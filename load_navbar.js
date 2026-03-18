(function(){
  function insertNavbar(html){
    try{
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const nav = doc.body.firstElementChild;
      if(!nav) return;
      const existing = document.querySelector('.w3-top');
      if(existing) existing.replaceWith(nav);
      else document.body.insertAdjacentElement('afterbegin', nav);

      // ensure auth script is loaded, then refresh nav
      function ensureAuthAndRefresh(){
        if(window.auth_nav_refresh || window.auth_nav_refresh === undefined && window.auth_nav_refresh === undefined && window.auth_nav_refresh === undefined){ }
        if(typeof window.auth_nav_refresh === 'function'){
          try{ window.auth_nav_refresh(); }catch(e){}
        } else {
          // load auth.js then call refresh
          const s = document.createElement('script'); s.src = 'js/auth.js'; s.onload = function(){ try{ if(typeof window.auth_nav_refresh === 'function') window.auth_nav_refresh(); }catch(e){} }; document.body.appendChild(s);
        }
      }

      ensureAuthAndRefresh();
    }catch(e){ console.error('Failed to insert navbar', e); }
  }

  fetch('includes/navbar.html', {cache: 'no-store'})
    .then(r => { if(!r.ok) throw new Error('nav fetch failed'); return r.text(); })
    .then(insertNavbar)
    .catch(err => {
      console.error('Could not load shared navbar:', err);
    });
})();
