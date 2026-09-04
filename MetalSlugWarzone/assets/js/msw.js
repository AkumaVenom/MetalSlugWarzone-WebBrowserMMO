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
      if(d>0)setTimeout(tick,1000);
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
      if(facing==='left'&&image.dataset.spriteLeft)image.src=image.dataset.spriteLeft;
      if(facing==='right'&&image.dataset.spriteRight)image.src=image.dataset.spriteRight;
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
            if(pair){pair.image.remove();pair.label.remove();}
            const image=document.createElement('img');
            image.alt='';image.className='map-avatar other'+(wantsLink?' bot-avatar':'');image.dataset.remoteAvatar='1';image.dataset.remoteId=key;
            const label=document.createElement(wantsLink?'a':'span');
            label.className='map-label'+(wantsLink?' bot-label':'');label.dataset.remoteAvatar='1';label.dataset.remoteId=key;
            world.append(image,label);
            pair={image,label,isBot:wantsLink};
            presenceNodes.set(key,pair);
          }
          const {image,label}=pair;
          image.dataset.spriteLeft=player.sprite_l||player.sprite;image.dataset.spriteRight=player.sprite_r||player.sprite;image.dataset.mirrorLeft=String(player.mirror_left||0);applyFacingSprite(image,player.facing||'right');
          image.style.left=player.x+'px';image.style.top=player.y+'px';
          label.style.left=player.x+'px';label.style.top=player.y+'px';
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
          pair.image.remove();pair.label.remove();presenceNodes.delete(key);
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
