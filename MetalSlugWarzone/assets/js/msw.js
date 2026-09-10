'use strict';
(()=>{
  const $=(s,p=document)=>p.querySelector(s);
  const $$=(s,p=document)=>[...p.querySelectorAll(s)];

  $$('[data-countdown]').forEach(el=>{
    const end=Date.parse(el.dataset.countdown);
    const tick=()=>{
      const d=Math.max(0,end-Date.now());
      const h=Math.floor(d/36e5),m=Math.floor((d%36e5)/6e4),s=Math.floor((d%6e4)/1e3);
      el.textContent=`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
      if(d>0){setTimeout(tick,1000);return;}
      const resultUrl=el.dataset.autoResultUrl;
      if(resultUrl&&el.dataset.autoResultTriggered!=='1'){
        el.dataset.autoResultTriggered='1';
        el.textContent='RESOLVING…';
        window.setTimeout(()=>{location.href=resultUrl;},700);
      }
    };
    tick();
  });

  $$('[data-dispatch-form]').forEach(form=>{
    const limit=Math.max(1,parseInt(form.dataset.slots||'1',10));
    const boxes=$$('input[type="checkbox"][name="units[]"]',form);
    const count=$('[data-selected-count]',form);
    const submit=$('[data-dispatch-submit]',form);
    const help=$('[data-dispatch-help]',form);
    const sync=()=>{
      const selected=boxes.filter(box=>box.checked);
      if(count)count.textContent=String(selected.length);
      boxes.forEach(box=>{box.disabled=!box.checked&&selected.length>=limit;});
      if(submit)submit.disabled=selected.length!==limit;
      if(help)help.textContent=selected.length===limit?'Team ready for deployment.':`Select ${limit-selected.length} more unit${limit-selected.length===1?'':'s'}.`;
    };
    boxes.forEach(box=>box.addEventListener('change',sync));
    sync();
  });

  const stage=$('[data-map-stage]');
  if(stage){
    const world=$('.map-world',stage);
    const me=$('[data-local-avatar]',stage);
    const myLabel=$('[data-local-label]',stage);
    const status=$('[data-movement-status]');
    const csrf=stage.dataset.csrf;
    const moveUrl=stage.dataset.moveUrl;
    const presenceUrl=stage.dataset.presenceUrl;
    const minInterval=Math.max(90,parseInt(stage.dataset.moveInterval||'110',10));
    let moving=false;
    let queuedDirection=null;
    let lastSentAt=0;

    const centerOn=(x,y,smooth=false)=>{
      if(!world)return;
      const left=Math.max(0,world.offsetLeft+x-stage.clientWidth/2);
      const top=Math.max(0,world.offsetTop+y-stage.clientHeight/2);
      stage.scrollTo({left,top,behavior:smooth?'smooth':'auto'});
    };
    const setStatus=(text,kind='')=>{
      if(!status)return;
      status.textContent=text;
      status.dataset.kind=kind;
    };
    const applyFacingSprite=(image,facing)=>{
      if(!image)return;
      image.dataset.facing=facing||'right';
      // Movement has four headings; the authored character art has two. Match
      // map_presence.php: up/down use the right sprite, including first sight.
      const sprite=facing==='left'
        ?(image.dataset.spriteLeft||image.dataset.spriteRight)
        :(image.dataset.spriteRight||image.dataset.spriteLeft);
      if(sprite&&(image.getAttribute('src')!==sprite||(image.complete&&image.naturalWidth===0)))image.src=sprite;
    };
    if(me){applyFacingSprite(me,me.dataset.facing||'right');centerOn(parseInt(me.style.left||'0',10),parseInt(me.style.top||'0',10));}

    const delay=ms=>new Promise(resolve=>setTimeout(resolve,ms));
    const flushMove=async()=>{
      if(moving||!queuedDirection)return;
      const direction=queuedDirection;
      queuedDirection=null;
      moving=true;
      const wait=Math.max(0,minInterval-(performance.now()-lastSentAt));
      if(wait>0)await delay(wait);
      lastSentAt=performance.now();
      setStatus(`Moving ${direction.toUpperCase()}…`,'active');
      try{
        const body=new URLSearchParams({csrf,direction});
        const response=await fetch(moveUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'fetch'},body,cache:'no-store'});
        let payload={};
        try{payload=await response.json();}catch(_error){}
        if(payload.battle){queuedDirection=null;location.href=payload.battle;return;}
        if(response.status===429){
          queuedDirection=queuedDirection||direction;
          const retry=Math.max(30,parseInt(payload.retry_ms||String(minInterval),10));
          await delay(retry);
          return;
        }
        if(!response.ok)throw new Error(payload.error||'Movement failed');
        if(me){me.style.left=payload.x+'px';me.style.top=payload.y+'px';applyFacingSprite(me,payload.facing||direction);}
        if(myLabel){myLabel.style.left=payload.x+'px';myLabel.style.top=payload.y+'px';}
        if(payload.blocked){
          centerOn(payload.x,payload.y,false);
          const reason=String(payload.reason||'solid terrain').replace(/_/g,' ');
          setStatus(`Blocked · ${reason}`,'blocked');
        }else{
          centerOn(payload.x,payload.y,true);
          setStatus('Ready · WASD / Arrow Keys','ready');
        }
      }catch(error){
        setStatus(error?.message||'Movement temporarily unavailable','error');
        console.warn('[MSW movement]',error?.message||error);
      }finally{
        moving=false;
        if(queuedDirection)flushMove();
      }
    };
    const requestMove=direction=>{
      queuedDirection=direction;
      flushMove();
    };

    $$('[data-move]').forEach(button=>button.addEventListener('click',()=>requestMove(button.dataset.move)));
    document.addEventListener('keydown',event=>{
      if(event.ctrlKey||event.altKey||event.metaKey)return;
      if(/INPUT|TEXTAREA|SELECT/.test(event.target?.tagName||'')||event.target?.isContentEditable)return;
      const map={ArrowUp:'up',KeyW:'up',ArrowDown:'down',KeyS:'down',ArrowLeft:'left',KeyA:'left',ArrowRight:'right',KeyD:'right'};
      const direction=map[event.code];
      if(direction){event.preventDefault();requestMove(direction);}
    },{passive:false});

    const presenceNodes=new Map();
    const presence=async()=>{
      try{
        const response=await fetch(presenceUrl,{cache:'no-store'});
        if(response.redirected){location.href=response.url;return;}
        if(!response.ok)throw new Error('Presence unavailable');
        const payload=await response.json();
        const seen=new Set();
        for(const player of payload.players||[]){
          const key=String(player.id||player.name);
          seen.add(key);
          let pair=presenceNodes.get(key);
          const wantsLink=!!player.is_bot;
          if(!pair||pair.isBot!==wantsLink){
            if(pair)pair.actor.remove();
            const actor=document.createElement('div');
            actor.className='map-actor';actor.dataset.remoteActor='1';actor.dataset.remoteId=key;
            const image=document.createElement('img');
            image.alt='';image.className='map-avatar other'+(wantsLink?' bot-avatar':'');image.dataset.remoteAvatar='1';image.dataset.remoteId=key;
            const label=document.createElement(wantsLink?'a':'span');
            label.className='map-label'+(wantsLink?' bot-label':'');label.dataset.remoteAvatar='1';label.dataset.remoteId=key;
            // One positioned parent owns the sprite and marker. Movement, hiding
            // and removal apply to this parent so the two can never drift apart.
            const syncVisibility=()=>{
              const ready=image.complete&&image.naturalWidth>0;
              actor.hidden=!ready;
            };
            actor.hidden=true;
            image.addEventListener('load',syncVisibility);
            image.addEventListener('error',syncVisibility);
            actor.append(image,label);world.append(actor);
            pair={actor,image,label,isBot:wantsLink,syncVisibility};
            presenceNodes.set(key,pair);
          }
          const {actor,image,label}=pair;
          image.dataset.spriteLeft=player.sprite_l||player.sprite;image.dataset.spriteRight=player.sprite_r||player.sprite;image.dataset.mirrorLeft=String(player.mirror_left||0);applyFacingSprite(image,player.facing||'right');
          pair.syncVisibility();
          actor.style.left=player.x+'px';actor.style.top=player.y+'px';
          const fullLabel=player.name+' · '+(wantsLink?'AI COMMANDER · ':'')+player.grade;
          if(wantsLink){
            // Keep autonomous presence readable at 1,000-population scale: a compact
            // AI pill at rest, with the complete identity revealed by CSS when the
            // pill or its adjacent operative sprite is hovered/focused. The full
            // label remains accessible and is refreshed without rebuilding the node.
            label.textContent='';
            label.dataset.fullLabel=fullLabel;
            label.setAttribute('aria-label',fullLabel);
            label.href=player.profile_url||'#';
            label.title=player.activity||'Autonomous commander';
            image.removeAttribute('title');
          }else{
            label.textContent=fullLabel;
            label.removeAttribute('data-full-label');
            label.removeAttribute('aria-label');
            label.removeAttribute('title');
            image.removeAttribute('title');
          }
        }
        for(const [key,pair] of presenceNodes){
          if(seen.has(key))continue;
          pair.actor.remove();presenceNodes.delete(key);
        }
      }catch(_error){}
      window.setTimeout(presence,3000);
    };
    presence();
  }
})();

(()=>{
  const watch=document.querySelector('[data-pvp-watch]');
  if(!watch)return;
  const currentVersion=parseInt(watch.dataset.pvpVersion||'0',10);
  const url=watch.dataset.pvpStateUrl;
  const poll=async()=>{
    try{const response=await fetch(url,{cache:'no-store'});if(response.ok){const state=await response.json();if(parseInt(state.version||'0',10)!==currentVersion)location.reload();}}catch(_error){}
    window.setTimeout(poll,2200);
  };
  window.setTimeout(poll,2200);
})();

(()=>{
  const stage=document.querySelector('[data-base-stage]');
  if(!stage)return;
  const world=stage.querySelector('.mother-base-world');
  const me=stage.querySelector('[data-base-local-avatar]');
  const myLabel=stage.querySelector('[data-base-local-label]');
  const status=document.querySelector('[data-base-movement-status]');
  const csrf=stage.dataset.csrf;
  const ownerId=stage.dataset.ownerId;
  const moveUrl=stage.dataset.moveUrl;
  const presenceUrl=stage.dataset.presenceUrl;
  const minInterval=Math.max(90,parseInt(stage.dataset.moveInterval||'110',10));
  let moving=false,queuedDirection=null,lastSentAt=0;
  const delay=ms=>new Promise(resolve=>setTimeout(resolve,ms));
  const centerOn=(x,y,smooth=false)=>{
    if(!world)return;
    const left=Math.max(0,world.offsetLeft+x-stage.clientWidth/2);
    const top=Math.max(0,world.offsetTop+y-stage.clientHeight/2);
    stage.scrollTo({left,top,behavior:smooth?'smooth':'auto'});
  };
  const setStatus=(text,kind='')=>{if(status){status.textContent=text;status.dataset.kind=kind;}};
  const applyPlayerFacing=(image,facing)=>{
    if(!image)return;
    image.dataset.facing=facing||'right';
    if(facing==='left'&&image.dataset.spriteLeft)image.src=image.dataset.spriteLeft;
    if(facing==='right'&&image.dataset.spriteRight)image.src=image.dataset.spriteRight;
  };
  const applyVisitorFacing=(node,facing)=>{
    node.dataset.facing=facing||'right';
    const image=node.querySelector('img');if(!image)return;
    if(facing==='left'&&node.dataset.spriteLeft)image.src=node.dataset.spriteLeft;
    if(facing==='right'&&node.dataset.spriteRight)image.src=node.dataset.spriteRight;
    image.style.transform=(facing==='left'&&node.dataset.mirrorLeft==='1')?'scaleX(-1)':'';
  };
  if(me){applyPlayerFacing(me,me.dataset.facing||'right');centerOn(parseInt(me.style.left||'0',10),parseInt(me.style.top||'0',10));}

  const flushMove=async()=>{
    if(moving||!queuedDirection)return;
    const direction=queuedDirection;queuedDirection=null;moving=true;
    const wait=Math.max(0,minInterval-(performance.now()-lastSentAt));if(wait>0)await delay(wait);lastSentAt=performance.now();
    setStatus(`Moving ${direction.toUpperCase()}…`,'active');
    try{
      const body=new URLSearchParams({csrf,direction,owner_id:ownerId});
      const response=await fetch(moveUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'fetch'},body,cache:'no-store'});
      let payload={};try{payload=await response.json();}catch(_error){}
      if(payload.reload){location.href=payload.reload;return;}
      if(response.status===429){queuedDirection=queuedDirection||direction;await delay(Math.max(30,parseInt(payload.retry_ms||String(minInterval),10)));return;}
      if(!response.ok)throw new Error(payload.error||'Mother Base movement failed');
      if(me){me.style.left=payload.x+'px';me.style.top=payload.y+'px';applyPlayerFacing(me,payload.facing||direction);}
      if(myLabel){myLabel.style.left=payload.x+'px';myLabel.style.top=payload.y+'px';}
      centerOn(payload.x,payload.y,!payload.blocked);
      if(payload.blocked)setStatus(`Blocked · ${String(payload.reason||'solid structure').replace(/_/g,' ')}`,'blocked');else setStatus('Ready · WASD / Arrow Keys','ready');
    }catch(error){setStatus(error?.message||'Movement temporarily unavailable','error');}
    finally{moving=false;if(queuedDirection)flushMove();}
  };
  const requestMove=direction=>{queuedDirection=direction;flushMove();};
  document.querySelectorAll('[data-base-move]').forEach(button=>button.addEventListener('click',()=>requestMove(button.dataset.baseMove)));
  document.addEventListener('keydown',event=>{
    if(event.ctrlKey||event.altKey||event.metaKey)return;
    if(/INPUT|TEXTAREA|SELECT/.test(event.target?.tagName||'')||event.target?.isContentEditable)return;
    const keys={ArrowUp:'up',KeyW:'up',ArrowDown:'down',KeyS:'down',ArrowLeft:'left',KeyA:'left',ArrowRight:'right',KeyD:'right'};
    const direction=keys[event.code];if(direction){event.preventDefault();requestMove(direction);}
  },{passive:false});

  const updateStaff=staff=>{
    const seen=new Set();
    for(const entity of staff||[]){
      const key=String(entity.id);seen.add(key);
      let node=world.querySelector(`[data-base-npc="${key}"]`);
      if(!node){node=document.createElement('div');node.dataset.baseNpc=key;world.append(node);}
      node.className='base-entity '+(entity.vehicle?'base-vehicle':'base-staff');
      node.dataset.mobile=String(entity.mobile||0);node.dataset.facing=entity.facing||'right';
      let image=node.querySelector('img');if(!image){image=document.createElement('img');image.alt='';node.append(image);}image.src=entity.sprite;
      image.style.transform=(entity.facing==='left'&&!entity.vehicle)?'scaleX(-1)':'';
      let label=node.querySelector('span');if(!label){label=document.createElement('span');node.append(label);}label.textContent=`${entity.name} · ${entity.grade} · ${entity.assignment_name}`;
      node.style.left=entity.x+'px';node.style.top=entity.y+'px';
    }
    world.querySelectorAll('[data-base-npc]').forEach(node=>{if(!seen.has(node.dataset.baseNpc))node.remove();});
  };
  const updateVisitors=visitors=>{
    const seen=new Set();
    for(const visitor of visitors||[]){
      const key=String(visitor.id);seen.add(key);
      let node=world.querySelector(`[data-base-visitor="${key}"]`);
      if(!node){node=document.createElement('div');node.className='base-entity base-visitor';node.dataset.baseVisitor=key;world.append(node);}
      node.dataset.spriteLeft=visitor.sprite_l||visitor.sprite;node.dataset.spriteRight=visitor.sprite_r||visitor.sprite;node.dataset.mirrorLeft=String(visitor.mirror_left||0);
      let image=node.querySelector('img');if(!image){image=document.createElement('img');image.alt='';node.append(image);}image.src=visitor.sprite;
      let label=node.querySelector('span');if(!label){label=document.createElement('span');node.append(label);}label.textContent=`${visitor.name} · ${visitor.grade}`;
      node.style.left=visitor.x+'px';node.style.top=visitor.y+'px';applyVisitorFacing(node,visitor.facing||'right');
    }
    world.querySelectorAll('[data-base-visitor]').forEach(node=>{if(!seen.has(node.dataset.baseVisitor))node.remove();});
  };
  const poll=async()=>{
    try{
      const response=await fetch(presenceUrl,{cache:'no-store'});let payload={};try{payload=await response.json();}catch(_error){}
      if(payload.reload){location.href=payload.reload;return;}
      if(response.status===403){setStatus('Base access revoked','error');return;}
      if(response.ok){updateStaff(payload.staff);updateVisitors(payload.visitors);}
    }catch(_error){}
    window.setTimeout(poll,2500);
  };
  window.setTimeout(poll,1200);
})();

(()=>{
  const viewport=document.querySelector('[data-fob-world-viewport]');
  if(viewport){
    const x=parseInt(viewport.dataset.ownX||'0',10),y=parseInt(viewport.dataset.ownY||'0',10);
    const center=()=>{
      viewport.scrollLeft=Math.max(0,x-viewport.clientWidth/2);
      viewport.scrollTop=Math.max(0,y-viewport.clientHeight/2);
    };
    requestAnimationFrame(center);
  }

  document.querySelectorAll('[data-fob-dispatch-form]').forEach(form=>{
    const min=Math.max(1,parseInt(form.dataset.min||'2',10));
    const max=Math.max(min,parseInt(form.dataset.max||'4',10));
    const boxes=[...form.querySelectorAll('input[type="checkbox"][name="units[]"]')];
    const count=form.querySelector('[data-fob-selected-count]');
    const submit=form.querySelector('[data-fob-dispatch-submit]');
    const sync=()=>{
      const selected=boxes.filter(box=>box.checked);
      if(count)count.textContent=String(selected.length);
      boxes.forEach(box=>{box.disabled=!box.checked&&selected.length>=max;});
      if(submit)submit.disabled=selected.length<min||selected.length>max;
    };
    boxes.forEach(box=>box.addEventListener('change',sync));
    sync();
  });
})();

(()=>{
  document.querySelectorAll('[data-command-target-select]').forEach(select=>{
    const form=select.closest('form');
    const world=form?form.querySelector('[data-command-world-input]'):null;
    const sync=()=>{
      const option=select.selectedOptions&&select.selectedOptions[0];
      if(world&&option)world.value=option.dataset.world||'';
    };
    select.addEventListener('change',sync);sync();
  });

  document.querySelectorAll('form[data-breaks-protection="1"]').forEach(form=>{
    form.addEventListener('submit',event=>{
      if(form.dataset.protectionConfirmed==='1')return;
      const ok=window.confirm('OFFENSIVE ACTION WARNING\n\nYour FOB is currently protected. Successfully committing this invasion or retaliation will immediately remove the remaining protection cooldown. Continue?');
      if(!ok){event.preventDefault();return;}
      form.dataset.protectionConfirmed='1';
    });
  });
})();

// v0.8.1 corrected automatic operations battle playback. The authoritative
// result is already committed by PHP/MySQL; this layer only plays the battle film.
(()=>{
  const reduced=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.querySelectorAll('[data-auto-battle]').forEach(shell=>{
    let model={};
    try{model=JSON.parse(shell.dataset.model||'{}');}catch(_error){return;}
    const stage=shell.querySelector('[data-auto-battle-stage]');
    const status=shell.querySelector('[data-auto-battle-status]');
    const eventText=shell.querySelector('[data-auto-battle-event]');
    const result=shell.querySelector('[data-auto-battle-result]');
    const log=shell.querySelector('[data-auto-battle-log]');
    const replay=shell.querySelector('[data-auto-battle-replay]');
    const skip=shell.querySelector('[data-auto-battle-skip]');
    const events=Array.isArray(model.events)?model.events:[];
    let runToken=0;

    const wait=ms=>new Promise(resolve=>window.setTimeout(resolve,ms));
    const team=side=>Array.isArray(model[side])?model[side]:[];
    const unit=(side,index)=>shell.querySelector(`[data-auto-unit="${side}-${index}"]`);
    const hpBar=(side,index)=>shell.querySelector(`[data-auto-unit-hp="${side}-${index}"]`);
    const hpCopy=(side,index)=>shell.querySelector(`[data-auto-hp-current="${side}-${index}"]`);
    const maxHp=(side,index)=>Math.max(1,Number(team(side)[index]?.max_hp)||1);
    const initialHp=(side,index)=>Math.max(0,Math.min(maxHp(side,index),Number(team(side)[index]?.hp)||maxHp(side,index)));

    const setHp=(side,index,value)=>{
      const maximum=maxHp(side,index);
      const hp=Math.max(0,Math.min(maximum,Math.round(Number(value)||0)));
      const pct=Math.max(0,Math.min(100,(hp/maximum)*100));
      const bar=hpBar(side,index);if(bar)bar.style.width=pct+'%';
      const copy=hpCopy(side,index);if(copy)copy.textContent=String(hp);
      const card=unit(side,index);if(card){
        card.classList.toggle('is-ko',hp<=0);
        card.setAttribute('aria-label',`${team(side)[index]?.name||'Unit'} HP ${hp} of ${maximum}`);
      }
    };
    const setIntegrity=(side,value)=>{
      const pct=Math.max(0,Math.min(100,Math.round(Number(value)||0)));
      const copy=shell.querySelector(`[data-auto-integrity="${side}"]`);
      const bar=shell.querySelector(`[data-auto-force-hp="${side}"]`);
      if(copy)copy.textContent=pct+'%';
      if(bar)bar.style.width=pct+'%';
    };
    const clearFx=()=>shell.querySelectorAll('.auto-battle-fighter').forEach(node=>node.classList.remove('is-firing','is-hit','is-shielded'));
    const reset=()=>{
      clearFx();
      ['left','right'].forEach(side=>team(side).forEach((_u,index)=>setHp(side,index,initialHp(side,index))));
      setIntegrity('left',100);setIntegrity('right',100);
      if(status)status.textContent='STANDBY';
      if(eventText)eventText.textContent='CONTACT';
      if(log)log.textContent='';
      if(result)result.classList.remove('is-visible');
      if(stage)stage.dataset.phase='standby';
    };
    const pushLog=text=>{
      if(!log||!text)return;
      const line=document.createElement('div');
      line.textContent=text;
      log.append(line);
      log.scrollTop=log.scrollHeight;
    };
    const applyEvent=evt=>{
      clearFx();
      if(evt.left_integrity!==undefined)setIntegrity('left',evt.left_integrity);
      if(evt.right_integrity!==undefined)setIntegrity('right',evt.right_integrity);
      if(Array.isArray(evt.left_hp))evt.left_hp.forEach((hp,i)=>setHp('left',i,hp));
      if(Array.isArray(evt.right_hp))evt.right_hp.forEach((hp,i)=>setHp('right',i,hp));
      if(evt.target_index!==undefined&&Number(evt.target_index)>=0&&evt.target&&evt.hp_after!==undefined){
        setHp(String(evt.target),Number(evt.target_index),evt.hp_after);
      }
      if(evt.actor&&evt.actor!=='none'&&Number(evt.actor_index)>=0){
        const actor=unit(String(evt.actor),Number(evt.actor_index));if(actor)actor.classList.add('is-firing');
      }
      if(evt.target&&evt.target!=='none'&&Number(evt.target_index)>=0){
        const target=unit(String(evt.target),Number(evt.target_index));if(target)target.classList.add('is-hit');
      }
      if(evt.type==='shield')shell.querySelectorAll('[data-auto-force="right"] .auto-battle-fighter').forEach(node=>node.classList.add('is-shielded'));
      if(stage)stage.dataset.phase=String(evt.type||'exchange');
      if(eventText)eventText.textContent=String(evt.log||'Combat exchange resolved.');
      if(status){
        status.textContent=evt.type==='contact'?'CONTACT':evt.type==='knockout'?'TARGET DOWN':evt.type==='decisive'?'BATTLE COMPLETE':evt.type==='shield'?'SHIELD BLOCK':evt.type==='withdraw'?'WITHDRAWAL':'ENGAGING';
      }
      pushLog(String(evt.log||''));
    };
    const finish=()=>{
      clearFx();
      if(status)status.textContent=model.result_label||'COMPLETE';
      if(result)result.classList.add('is-visible');
      if(stage)stage.dataset.phase='complete';
      if(replay)replay.disabled=false;
      if(skip)skip.disabled=true;
    };
    const applyAll=()=>{reset();events.forEach(applyEvent);finish();};
    const play=async()=>{
      const token=++runToken;
      reset();
      if(replay)replay.disabled=true;if(skip)skip.disabled=false;
      if(reduced){applyAll();return;}
      await wait(550);if(token!==runToken)return;
      for(const evt of events){
        applyEvent(evt);
        const pause=evt.type==='contact'?1000:evt.type==='knockout'?950:evt.type==='decisive'?1200:evt.type==='shield'?1250:820;
        await wait(pause);
        if(token!==runToken)return;
      }
      finish();
    };
    if(replay)replay.addEventListener('click',play);
    if(skip)skip.addEventListener('click',()=>{runToken++;applyAll();});
    // Script is cache-busted per release in ui.php, so every result page receives
    // this autoplay code even if the browser had an older msw.js cached.
    window.setTimeout(play,300);
  });
})();

// v0.6.0 supplied-art visual integration. Presentation only: the selected operative is
// mirrored into the current page hero and non-critical command surfaces reveal gently.
(()=>{
  const reduced=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.documentElement.classList.add('motion-ready');

  const hero=document.querySelector('.hero');
  const source=document.querySelector('.commander-chip img');
  if(hero&&source&&!hero.querySelector('.hero-operative')){
    const figure=document.createElement('span');
    figure.className='hero-operative';
    figure.setAttribute('aria-hidden','true');
    const image=document.createElement('img');
    image.src=source.currentSrc||source.src;
    image.alt='';
    figure.append(image);
    hero.append(figure);
  }

  const nodes=[...document.querySelectorAll('.panel,.stat,.feature,.map-card,.fob-command-status,.fob-command-target,.fob-operation-row,.fob-retaliation-card')];
  nodes.forEach(node=>node.classList.add('ui-reveal'));
  if(reduced||!('IntersectionObserver' in window)){
    nodes.forEach(node=>node.classList.add('is-visible'));
    return;
  }
  const observer=new IntersectionObserver(entries=>{
    for(const entry of entries){
      if(!entry.isIntersecting)continue;
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    }
  },{rootMargin:'0px 0px -24px 0px',threshold:.04});
  nodes.forEach(node=>observer.observe(node));
})();
