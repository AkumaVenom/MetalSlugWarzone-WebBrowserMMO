/* Metal Slug Warzone v0.9.0. Presentation only: never submits gameplay orders. */
(() => {
  'use strict';
  const root = document.querySelector('[data-msw-audio]');
  if (!root) return;
  let cfg;
  try { cfg = JSON.parse(root.dataset.mswAudio); } catch (_) { return; }
  const memoryStorage = () => { const values=new Map(); return {getItem:key=>values.get(key)||null,setItem:(key,value)=>values.set(key,String(value)),removeItem:key=>values.delete(key)}; };
  const safeStorage = name => { try { return window[name] || memoryStorage(); } catch (_) { return memoryStorage(); } };
  const localStorage=safeStorage('localStorage'), sessionStorage=safeStorage('sessionStorage');
  const tracks = cfg.tracks || {}, effects = cfg.effects || {}, context = cfg.context || {};
  const clamp = (v, min, max) => Math.min(max, Math.max(min, Number(v) || 0));
  const read = (storage, key, fallback) => { try { return JSON.parse(storage.getItem(key)) || fallback; } catch (_) { return fallback; } };
  const write = (storage, key, value) => { try { storage.setItem(key, JSON.stringify(value)); return true; } catch (_) { return false; } };
  const remove = (storage, key) => { try { storage.removeItem(key); } catch (_) {} };
  // Base path and account both scope caches: separate installs/accounts never share settings.
  const scope = 'msw:audio:v1:' + new URL(cfg.endpoint, location.href).pathname + ':' + cfg.accountId;
  const cacheKey = scope + ':session', leaseKey = scope + ':owner', syncKey = scope + ':settings';
  const tab = Date.now().toString(36) + Math.random().toString(36).slice(2);
  let saved = read(sessionStorage, cacheKey, {});
  if (Date.now() - (saved.at || 0) > 300000) saved = {};
  let state = Object.assign({muted:false, musicVolume:0.55, effectsVolume:0.75, settingsRevision:0, positions:{}, positionRevisions:{}}, cfg.state);
  state.positions = Object.assign({}, state.positions);
  state.positionRevisions = Object.assign({}, state.positionRevisions);
  const pendingPositions = new Map();
  for(const [id,seconds] of Object.entries(saved.positions || {})){
    if(tracks[id] && (saved.positionRevisions?.[id] || 0) >= (state.positionRevisions[id] || 0)){
      state.positions[id]=seconds;
      state.positionRevisions[id]=saved.positionRevisions?.[id] || 0;
      if(saved.pendingPositions?.includes(id))pendingPositions.set(id,seconds);
    }
  }
  let dirtySettings = false, settingsGeneration = 0, inflight = false, saveTimer = 0;
  if (!cfg.accountId) Object.assign(state, read(localStorage, syncKey, {}));
  else if (saved.pending && saved.pending.revision === state.settingsRevision) {
    Object.assign(state, saved.pending.values); dirtySettings = true;
  }
  let music = new Audio(), track = '', loop = true, fallback = '', pendingSeek = null;
  let paused = !!saved.paused, blocked = false, mediaError = '', effectError = false, owns = false;
  let audioContext = null, musicGain = null, effectsGain = null, musicSource = null;
  const buffers = new Map(), voices = new Set(), timers = new Set();
  let generation = 0, readyToSeek = false, playingPromise = null;
  let seen = read(sessionStorage, scope + ':events', []), transient = null;
  const q = name => root.querySelector('[data-audio-' + name + ']');
  const panel = q('panel'), toggle = q('toggle'), mute = q('mute'), mutePanel = q('mute-panel');
  const musicSlider = q('music'), effectsSlider = q('effects'), seek = q('seek');
  let saveLabel = !cfg.accountId ? 'Guest settings saved on this device' : cfg.available ? 'Account settings loaded' : 'Account saving unavailable · run Update / Repair';
  const time = seconds => { seconds = Math.max(0, Math.floor(seconds || 0)); return Math.floor(seconds/60) + ':' + String(seconds%60).padStart(2,'0'); };
  function cache() {
    write(sessionStorage, cacheKey, {at:Date.now(),positions:state.positions,positionRevisions:state.positionRevisions,pendingPositions:[...pendingPositions.keys()],paused,transient,
      pending:dirtySettings?{revision:state.settingsRevision,values:{muted:state.muted,musicVolume:state.musicVolume,effectsVolume:state.effectsVolume}}:null});
  }
  function applyVolumes() {
    music.muted = state.muted;
    music.volume = musicGain ? 1 : state.musicVolume;
    if (musicGain) musicGain.gain.value = state.muted ? 0 : state.musicVolume;
    if (effectsGain) effectsGain.gain.value = state.muted ? 0 : state.effectsVolume;
    if(!audioContext)voices.forEach(v=>{v.volume=state.muted?0:state.effectsVolume;});
  }
  function render() {
    root.dataset.muted = String(state.muted);
    const label = state.muted ? 'Unmute sound' : 'Mute all sound';
    for (const button of [mute, mutePanel]) { button.textContent = state.muted ? 'Unmute' : 'Mute all'; button.setAttribute('aria-label',label); button.setAttribute('aria-pressed',String(state.muted)); }
    q('badge').textContent = state.muted ? 'MUTED' : blocked ? 'TAP TO START' : !owns ? 'STANDBY' : paused ? 'PAUSED' : 'ON';
    q('status').textContent = state.muted ? 'All sound muted' : mediaError || (blocked ? 'Click Enable sound to start playback' : !owns ? 'Audio follows the active game tab' : paused ? 'Music paused · effects active' : 'Sound enabled');
    q('track').textContent = (tracks[track] || {}).title || 'Warzone audio';
    q('play').textContent = blocked ? 'Enable sound' : paused ? 'Play music' : 'Pause music';
    q('save').textContent = saveLabel + (effectError ? ' · An effect could not load' : '');
    if (document.activeElement !== musicSlider) musicSlider.value = String(Math.round(state.musicVolume*100));
    if (document.activeElement !== effectsSlider) effectsSlider.value = String(Math.round(state.effectsVolume*100));
    q('music-value').textContent = Math.round(state.musicVolume*100) + '%';
    q('effects-value').textContent = Math.round(state.effectsVolume*100) + '%';
    const duration = Number.isFinite(music.duration) ? music.duration : ((tracks[track]||{}).duration || 0);
    seek.max = String(Math.max(1,duration)); seek.disabled = !readyToSeek || !duration;
    if (document.activeElement !== seek) seek.value = String(music.currentTime || 0);
    q('time').textContent = time(music.currentTime) + ' / ' + time(duration);
  }
  function initContext() {
    if (audioContext) return;
    const Constructor = window.AudioContext || window.webkitAudioContext;
    if (!Constructor) return;
    try {
      audioContext = new Constructor(); musicGain = audioContext.createGain(); effectsGain = audioContext.createGain();
      musicSource = audioContext.createMediaElementSource(music);
      musicSource.connect(musicGain); musicGain.connect(audioContext.destination); effectsGain.connect(audioContext.destination); applyVolumes();
    } catch (_) { audioContext = null; musicGain = null; effectsGain = null; }
  }
  function stopEffects() {
    generation++; timers.forEach(clearTimeout); timers.clear();
    voices.forEach(v => { try { v.stop ? v.stop() : v.pause(); } catch (_) {} }); voices.clear();
  }
  function checkpoint() {
    // Never replace a restored offset with the media element's initial zero.
    if (track && readyToSeek && Number.isFinite(music.currentTime)) {
      const seconds=Math.round(music.currentTime*1000)/1000;
      if(owns && state.positions[track] !== seconds)pendingPositions.set(track,seconds);
      if(owns)state.positions[track]=seconds;
    }
    cache();
  }
  function lease(force = false) {
    if (document.hidden) return false;
    const owner = read(localStorage, leaseKey, {});
    if (force || !owner.id || owner.id === tab || owner.until < Date.now()) {
      const stored = write(localStorage, leaseKey, {id:tab,until:Date.now()+4000});
      return !stored || read(localStorage, leaseKey, {}).id === tab;
    }
    return false;
  }
  function release() {
    checkpoint(); music.pause(); stopEffects(); owns = false;
    if (read(localStorage, leaseKey, {}).id === tab) remove(localStorage, leaseKey);
  }
  function play() {
    if (!track || state.muted || paused || !owns || document.hidden) return;
    if (playingPromise || !music.paused) return;
    const requestedTrack = track;
    playingPromise = music.play();
    if (!playingPromise || !playingPromise.then) { playingPromise = null; return; }
    playingPromise.then(() => {
      if (requestedTrack !== track) return;
      blocked = !!audioContext && audioContext.state !== 'running'; mediaError = ''; render();
    }).catch(error => {
      if (requestedTrack !== track || error.name === 'AbortError') return;
      if (error.name === 'NotAllowedError') blocked = true;
      else mediaError = 'Track unavailable · use Retry audio';
      render();
    }).finally(() => { playingPromise = null; });
  }
  function selectTrack(id, options = {}) {
    if (!tracks[id]) id = context.track && tracks[context.track] ? context.track : 'mother_base';
    if (!tracks[id]) return;
    if (track === id && !options.restart && !options.reload) { loop = options.loop !== false; music.loop = loop; fallback = options.fallback || ''; play(); return; }
    checkpoint(); if(track && cfg.accountId && owns)save(); music.pause(); playingPromise = null;
    track = id; loop = options.loop !== false; fallback = options.fallback || '';
    readyToSeek = false; mediaError = ''; pendingSeek = options.restart ? 0 : clamp(state.positions[id] || 0,0,86400);
    music.src = tracks[id].url; music.loop = loop; music.preload = 'metadata'; applyVolumes();
    music.load(); render(); play();
  }
  music.addEventListener('loadedmetadata', () => {
    if (pendingSeek !== null) {
      const duration = music.duration;
      const offset = Number.isFinite(duration) && duration > 0 ? (loop ? pendingSeek % duration : Math.min(pendingSeek,Math.max(0,duration-0.05))) : pendingSeek;
      try { music.currentTime = offset; pendingSeek = null; readyToSeek = true; } catch (_) {}
    }
    render();
  });
  music.addEventListener('timeupdate', () => { checkpoint(); render(); });
  music.addEventListener('ended', () => {
    state.positions[track] = 0; pendingPositions.set(track,0); readyToSeek=false; transient = null; cache();
    if (!loop) selectTrack(fallback || context.fallback || context.track || 'mother_base');
  });
  music.addEventListener('error', () => { mediaError = 'Track unavailable · use Retry audio'; render(); });
  async function effect(sound, epoch) {
    if (!effects[sound] || state.muted || state.effectsVolume <= 0 || !owns || document.hidden || epoch !== generation) return;
    if (voices.size >= 8) return;
    initContext();
    if (audioContext) {
      if (audioContext.state !== 'running') { blocked = true; render(); return; }
      try {
        if (!buffers.has(sound)) buffers.set(sound, fetch(effects[sound].url,{credentials:'same-origin'}).then(r => { if(!r.ok) throw new Error('Audio asset'); return r.arrayBuffer(); }).then(data => audioContext.decodeAudioData(data)));
        const buffer = await buffers.get(sound);
        if (state.muted || state.effectsVolume <= 0 || !owns || document.hidden || epoch !== generation || voices.size >= 8) return;
        const voice = audioContext.createBufferSource(); voice.buffer = buffer; voice.connect(effectsGain); voices.add(voice);
        voice.onended = () => { voices.delete(voice); voice.disconnect(); }; voice.start();
      } catch (_) { buffers.delete(sound); effectError = true; render(); }
    } else {
      const voice = new Audio(effects[sound].url); voice.volume = state.effectsVolume; voices.add(voice);
      voice.onended = voice.onerror = () => voices.delete(voice);
      const result = voice.play(); if(result && result.catch) result.catch(() => voices.delete(voice));
    }
  }
  function event(detail) {
    if (!detail || typeof detail.id !== 'string' || seen.includes(detail.id)) return;
    if(detail.cancelPending)stopEffects();
    seen.push(detail.id); seen = seen.slice(-180); write(sessionStorage, scope + ':events', seen);
    if (detail.music && tracks[detail.music]) {
      transient = detail.loop === false ? {event:detail.id,track:detail.music,fallback:detail.fallback || context.track,page:location.pathname+location.search} : null;
      selectTrack(detail.music,{loop:detail.loop !== false,fallback:detail.fallback,restart:detail.restart !== false}); cache();
    }
    // Blocked/muted historical effects must never burst later after an unlock.
    if (state.muted || blocked || document.hidden || !owns) return;
    const epoch = generation;
    (Array.isArray(detail.cues) ? detail.cues : []).slice(0,16).forEach(cue => {
      const delay = clamp(cue.delay || 0,0,8000);
      const timer = setTimeout(() => { timers.delete(timer); effect(cue.sound,epoch); },delay); timers.add(timer);
    });
  }
  async function save(unloading = false) {
    checkpoint();
    if (!cfg.accountId) {
      write(localStorage,syncKey,{muted:state.muted,musicVolume:state.musicVolume,effectsVolume:state.effectsVolume});
      dirtySettings = false; cache(); render(); return;
    }
    if (!cfg.available || (inflight && !unloading)) return;
    const payload = {csrf:cfg.csrf};
    const nextPosition=pendingPositions.entries().next().value;
    const positionId=nextPosition ? nextPosition[0] : '';
    const positionSeconds=nextPosition ? nextPosition[1] : 0;
    if (owns && positionId) payload.position = {trackId:positionId,seconds:positionSeconds,revision:state.positionRevisions[positionId] || 0};
    if (dirtySettings) payload.settings = {muted:state.muted,musicVolume:state.musicVolume,effectsVolume:state.effectsVolume,revision:state.settingsRevision};
    if (!payload.settings && !payload.position) return;
    const body = JSON.stringify(payload), sentGeneration = settingsGeneration;
    if (unloading) {
      // Flush each touched track; settings travel once. Per-track CAS protects
      // racing pages and repeated pagehide/visibility/navigation checkpoints.
      const packets=[payload];
      if(owns)for(const [id,seconds] of pendingPositions){
        if(id!==positionId)packets.push({csrf:cfg.csrf,position:{trackId:id,seconds,revision:state.positionRevisions[id]||0}});
      }
      for(const packet of packets){
        const bytes=JSON.stringify(packet);
        if(navigator.sendBeacon){try{if(navigator.sendBeacon(cfg.endpoint,new Blob([bytes],{type:'application/json'})))continue;}catch(_){}}
        fetch(cfg.endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:bytes,keepalive:true}).catch(()=>{});
      }
      return;
    }
    inflight = true;
    let accepted=false;
    const abort = new AbortController(), timeout = setTimeout(()=>abort.abort(),8000);
    try {
      const response = await fetch(cfg.endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body,signal:abort.signal});
      const result = await response.json();
      if(response.status===409 && result.error==='position_conflict' && result.state){
        state.positionRevisions[positionId]=result.state.positionRevisions[positionId] || 0;
        pendingPositions.delete(positionId);
        saveLabel='Latest playback checkpoint retained';
      } else if (response.status === 409 && result.state) {
        state.settingsRevision = result.state.settingsRevision;
        // A newer saved preference wins over stale navigation snapshots; a new live edit can retry.
        if (settingsGeneration === sentGeneration) {
          Object.assign(state,{muted:result.state.muted,musicVolume:result.state.musicVolume,effectsVolume:result.state.effectsVolume});
          dirtySettings = false; applyVolumes(); if(state.muted){music.pause();stopEffects();}else play();
          saveLabel = 'Latest account settings loaded';
        }
      } else if (!response.ok || !result.ok) throw new Error(result.error || 'save_failed');
      else {
        accepted=true;
        if (payload.position) {
          state.positionRevisions[positionId]=result.state.positionRevisions[positionId] || 0;
          if(result.positionAccepted===false || pendingPositions.get(positionId)===positionSeconds)pendingPositions.delete(positionId);
        }
        if (payload.settings) {
          state.settingsRevision = result.state.settingsRevision;
          if (settingsGeneration === sentGeneration) dirtySettings = false;
          write(localStorage,syncKey,{muted:result.state.muted,musicVolume:result.state.musicVolume,effectsVolume:result.state.effectsVolume,settingsRevision:result.state.settingsRevision});
        }
        saveLabel = dirtySettings ? 'Saving account settings…' : 'Saved to your account';
      }
    } catch (_) { saveLabel = 'Save pending · reconnect to sync'; }
    finally { clearTimeout(timeout); inflight = false; cache(); render(); }
    if ((dirtySettings && settingsGeneration !== sentGeneration) || (accepted && [...pendingPositions.keys()].some(id=>id!==positionId))) scheduleSave();
  }
  function scheduleSave() { clearTimeout(saveTimer); saveTimer = setTimeout(()=>save(),250); }
  function changed() {
    dirtySettings = true; settingsGeneration++; saveLabel = cfg.accountId ? 'Saving account settings…' : 'Guest settings saved on this device';
    applyVolumes(); cache(); render(); scheduleSave();
  }
  function unlock() {
    if (state.muted || document.hidden) return;
    owns = lease(true); initContext();
    if (audioContext && audioContext.state !== 'running') audioContext.resume().then(()=>{blocked=false;play();render();}).catch(()=>{blocked=true;render();});
    blocked = false; play(); render();
  }
  function muteClick() { checkpoint(); state.muted = !state.muted; if(state.muted){music.pause();stopEffects();}else unlock(); changed(); }
  mute.addEventListener('click',muteClick); mutePanel.addEventListener('click',muteClick);
  toggle.addEventListener('click', () => { panel.hidden = !panel.hidden; toggle.setAttribute('aria-expanded',String(!panel.hidden)); if(!panel.hidden) q('close').focus(); });
  q('close').addEventListener('click', () => { panel.hidden = true; toggle.setAttribute('aria-expanded','false'); toggle.focus(); });
  root.addEventListener('keydown', e => { if(e.key === 'Escape' && !panel.hidden){panel.hidden=true;toggle.setAttribute('aria-expanded','false');toggle.focus();} });
  q('play').addEventListener('click', () => { if(blocked || state.muted){state.muted=false;paused=false;changed();}else paused=!paused; if(paused){checkpoint();music.pause();}else unlock();cache();render(); });
  musicSlider.addEventListener('input', () => { state.musicVolume=clamp(musicSlider.value,0,100)/100; changed(); });
  effectsSlider.addEventListener('input', () => { state.effectsVolume=clamp(effectsSlider.value,0,100)/100; if(state.effectsVolume===0)stopEffects(); changed(); });
  seek.addEventListener('change', () => { if(readyToSeek){music.currentTime=clamp(seek.value,0,Number.isFinite(music.duration)?music.duration:86400);checkpoint();scheduleSave();render();} });
  q('test').addEventListener('click', () => { unlock(); const epoch=generation; if(audioContext) audioContext.resume().then(()=>effect('rifle',epoch)).catch(()=>{});else effect('rifle',epoch); });
  q('retry').addEventListener('click', () => { effectError=false; checkpoint(); selectTrack(track,{loop,fallback,reload:true}); unlock(); save(); });
  document.addEventListener('pointerdown',e=>{if(!e.target.closest('[data-audio-mute],[data-audio-mute-panel]'))unlock();},{passive:true});
  document.addEventListener('keydown',e=>{if(!e.ctrlKey&&!e.metaKey&&!e.altKey)unlock();});
  document.addEventListener('submit',()=>{save(true);},{capture:true});
  document.addEventListener('click',e=>{if(e.target.closest('a[href]'))save(true);},{capture:true});
  window.addEventListener('msw:audio-event',e=>event(e.detail));
  window.addEventListener('pagehide',()=>{save(true);release();});
  window.addEventListener('pageshow',e=>{if(e.persisted){owns=lease(true);play();render();}});
  document.addEventListener('visibilitychange',()=>{if(document.hidden){save(true);release();}else{owns=lease();play();}render();});
  window.addEventListener('focus',()=>{owns=lease(true);play();render();});
  window.addEventListener('online',()=>save());
  window.addEventListener('storage',e=>{
    if(e.key===leaseKey && read(localStorage,leaseKey,{}).id!==tab && owns){save(true);checkpoint();music.pause();stopEffects();owns=false;render();}
    if(e.key===syncKey && !dirtySettings){const incoming=read(localStorage,syncKey,{});if(!cfg.accountId||incoming.settingsRevision>state.settingsRevision){Object.assign(state,incoming);applyVolumes();if(state.muted){music.pause();stopEffects();}else play();cache();render();}}
  });
  setInterval(()=>{const was=owns;owns=lease();if(!owns&&was){checkpoint();music.pause();stopEffects();}else if(owns&&!was)play();render();},1500);
  setInterval(()=>save(),12000);
  owns=lease();
  let initial=context.track || 'mother_base', options={loop:context.loop!==false,fallback:context.fallback};
  const oldTransient=saved.transient;
  if(oldTransient && oldTransient.page===location.pathname+location.search && tracks[oldTransient.track]){
    transient=oldTransient;initial=oldTransient.track;options={loop:false,fallback:oldTransient.fallback};
  }
  selectTrack(initial,options);
  if(context.event)event(context.event);
  document.querySelectorAll('[data-msw-audio-event]').forEach(el=>{try{event(JSON.parse(el.dataset.mswAudioEvent));}catch(_){}});
  if(dirtySettings)scheduleSave();
  render();
})();
