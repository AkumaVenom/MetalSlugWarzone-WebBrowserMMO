'use strict';
(()=>{
  const status=document.querySelector('[data-setup-world-state]');
  if(!status)return;
  let stopped=false,timer=0;
  const poll=async()=>{
    const controller=new AbortController(),timeout=setTimeout(()=>controller.abort(),5000);
    try{
      const url=new URL(location.href);url.search='?world_status=1';
      const response=await fetch(url,{cache:'no-store',credentials:'same-origin',signal:controller.signal});
      if(!response.ok)throw new Error('Status unavailable');
      const data=await response.json();
      if(typeof data.message!=='string'||typeof data.state!=='string')throw new Error('Invalid status');
      status.dataset.setupWorldState=data.state;
      if(status.textContent!==data.message)status.textContent=data.message;
      status.style.color=data.state==='running'?'#a5e3b3':['error','missing','retrying'].includes(data.state)?'#e6a092':'#e7c886';
    }catch(_){status.textContent='Cannot check background status. Reconnecting automatically…';}
    finally{clearTimeout(timeout);if(!stopped)timer=setTimeout(poll,3000);}
  };
  window.addEventListener('pagehide',()=>{stopped=true;clearTimeout(timer);});
  window.addEventListener('pageshow',event=>{if(event.persisted){stopped=false;poll();}});
  poll();
})();
