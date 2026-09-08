'use strict';
(()=>{
  const script=document.currentScript;
  if(!script?.dataset.pulseUrl)return;
  const endpoint=script.dataset.pulseUrl,csrf=script.dataset.csrf;
  const rankings=document.querySelector('[data-world-rankings]');
  const incoming=document.querySelector('[data-world-incoming]');
  const liveStatus=document.querySelector('[data-world-live-status]');
  const connection=document.querySelector('[data-world-connection]');
  const connectionLabel=document.querySelector('[data-world-connection-label]');
  const completed=new Map();
  let timer=0,inFlight=false,stopped=false,failures=0,lastRanking=0,incomingSignature='';
  let clockOffset=Number(script.dataset.serverTime)-Date.now();
  const setConnection=(state,message)=>{
    if(connection){connection.dataset.state=state;connection.setAttribute('aria-label',message);connection.title=message;}
    if(connectionLabel)connectionLabel.textContent={online:'ONLINE',retrying:'RETRYING',reconnecting:'RECONNECTING',expired:'SIGN IN'}[state]||'CONNECTING';
    if(liveStatus&&message&&liveStatus.textContent!==message)liveStatus.textContent=message;
  };
  const node=(tag,text,cls)=>{
    const el=document.createElement(tag);
    if(text!==undefined)el.textContent=String(text);
    if(cls)el.className=cls;
    return el;
  };
  const localLink=(label,url,cls)=>{
    const a=node('a',label,cls);
    const parsed=new URL(url,location.href);
    if(parsed.origin===location.origin)a.href=parsed.href;
    return a;
  };
  const format=value=>Number(value).toLocaleString('en-US');
  const replaceRows=(container,fragment)=>{
    const focused=document.activeElement;
    const href=focused&&container.contains(focused)?focused.href:null;
    container.replaceChildren(fragment);
    if(href)[...container.querySelectorAll('a')].find(link=>link.href===href)?.focus({preventScroll:true});
  };
  const updateRankings=rows=>{
    const fragment=document.createDocumentFragment();
    rows.forEach((row,index)=>{
      const tr=node('tr'),name=node('td');
      const link=localLink(row.username,row.profile_url);link.style.color='#e8d59a';name.append(link);
      if(Number(row.is_bot)===1)name.append(document.createTextNode(' '),node('span','AI','ai-mark'));
      const grade=node('td');grade.append(node('span',row.base_grade,'grade'));
      tr.append(node('td',index+1),name,node('td',row.level),grade,node('td',format(row.base_power)));
      fragment.append(tr);
    });
    replaceRows(rankings,fragment);
  };
  const updateIncoming=data=>{
    (data.completed||[]).forEach(row=>completed.set(row.id,row));
    while(completed.size>16)completed.delete(completed.keys().next().value);
    const signature=JSON.stringify([data.incoming,[...completed.values()],data.incoming_count]);
    if(signature===incomingSignature){tickArrivals();return;}
    incomingSignature=signature;
    const fragment=document.createDocumentFragment();
    (data.incoming||[]).forEach(op=>{
      const row=node('article',undefined,'fob-operation-row');row.dataset.incomingId=String(op.id);
      const details=node('div'),heading=node('h3',op.attacker+' ');
      heading.append(node('span',op.grade));
      details.append(node('small','INBOUND #'+op.id+' · '+op.world),heading,node('p','Enemy staff team · estimated success '+(Number(op.chance)*100).toFixed(1)+'%'));
      const eta=node('div',undefined,'fob-operation-eta'),value=node('b');value.dataset.worldEta=String(op.finish_ms);
      eta.append(node('small','ETA'),value);row.append(details,eta);fragment.append(row);
    });
    for(const op of [...completed.values()].reverse()){
      const row=node('article',undefined,'fob-operation-row'),details=node('div'),result=node('div',undefined,'fob-operation-eta');
      details.append(node('small','DEFENSE REPORT #'+op.id),node('h3',op.attacker));
      const label={defender_win:'DEFENSE HELD',attacker_win:'FOB BREACHED',protected_abort:'BLOCKED BY SHIELD'}[op.result]||'RESOLVED';
      result.append(node('b',label));
      if(op.report_url)result.append(localLink('View Defense Report',op.report_url,'btn small secondary'));
      row.append(details,result);fragment.append(row);
    }
    if(!fragment.childNodes.length)fragment.append(node('div','No enemy staff strike teams are currently heading toward your FOB.','empty'));
    replaceRows(incoming,fragment);
    document.querySelectorAll('[data-world-incoming-count]').forEach(el=>{el.textContent=format(data.incoming_count)+(el.dataset.worldIncomingCount==='badge'?' DETECTED':'');});
    tickArrivals();
  };
  const tickArrivals=()=>{
    document.querySelectorAll('[data-world-eta]').forEach(el=>{
      const seconds=Math.max(0,Math.ceil((Number(el.dataset.worldEta)-Date.now()-clockOffset)/1000));
      if(!seconds){el.textContent='AWAITING REPORT…';return;}
      const values=[Math.floor(seconds/3600),Math.floor(seconds%3600/60),seconds%60];
      el.textContent=values.map(value=>String(value).padStart(2,'0')).join(':');
    });
  };
  const schedule=delay=>{clearTimeout(timer);if(!stopped)timer=window.setTimeout(pulse,delay);};
  const pulse=async()=>{
    if(inFlight||stopped)return;
    inFlight=true;
    let delay=2000;
    const controller=new AbortController(),timeout=window.setTimeout(()=>controller.abort(),10000);
    try{
      const view=incoming?'fob':(rankings&&Date.now()-lastRanking>=10000?'rankings':'');
      const watch=incoming?[...incoming.querySelectorAll('[data-incoming-id]')].map(el=>el.dataset.incomingId).slice(0,16).join(','):'';
      const response=await fetch(endpoint,{method:'POST',credentials:'same-origin',cache:'no-store',signal:controller.signal,headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({csrf,view,watch})});
      if(response.status===401||response.status===419){
        stopped=true;setConnection('expired','Sign in again to resume live updates.');return;
      }
      if(!response.ok)throw new Error('World update unavailable');
      const data=await response.json();
      if(!Number.isFinite(Number(data.server_time_ms)))throw new Error('Invalid world response');
      clockOffset=Number(data.server_time_ms)-Date.now();
      delay=Math.max(1000,Math.min(10000,Number(data.next_poll_ms)||2000));failures=0;
      if(rankings&&Array.isArray(data.rankings)){updateRankings(data.rankings);lastRanking=Date.now();}
      if(incoming&&Array.isArray(data.incoming))updateIncoming(data);
      if(data.status==='retrying')setConnection('retrying','Some world updates are delayed. Retrying automatically…');
      else setConnection('online',rankings?'Live standings · Updated '+new Date(lastRanking+clockOffset).toLocaleTimeString():incoming?'Strike reports update automatically.':'Automatic world updates are active.');
    }catch(_){
      failures++;delay=Math.min(30000,2000*Math.pow(2,Math.min(failures,4)));
      setConnection('reconnecting','Connection interrupted. Reconnecting automatically…');
    }finally{
      // Background tabs keep the world moving at a lower request rate. A
      // suspended/closed browser catches up from persisted deadlines on return.
      clearTimeout(timeout);inFlight=false;schedule(document.hidden?Math.max(15000,delay):delay);
    }
  };
  document.addEventListener('visibilitychange',()=>{if(!document.hidden&&!inFlight)schedule(0);});
  window.addEventListener('online',()=>{if(!inFlight)schedule(0);});
  window.addEventListener('pagehide',()=>{stopped=true;clearTimeout(timer);});
  window.addEventListener('pageshow',event=>{if(event.persisted){stopped=false;schedule(0);}});
  if(incoming){tickArrivals();window.setInterval(tickArrivals,1000);}
  schedule(250);
})();
