// Simplified auth/navigation script — initializes #authNav reliably on every page
(function(){
  'use strict';

  function escapeHtml(s){ return String(s||'').replace(/[&<>"'']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]; }); }

  function makeProfileHtml(u){
    const username = escapeHtml((u.username || '').toUpperCase());
    const img = u.profile_image || 'img/avatars/default.png';
    return `
      <div class="profile-menu">
        <div style="display:flex;align-items:center;gap:8px;">
          <button class="profile-btn" aria-expanded="false" onclick="(function(e,btn){e.stopPropagation(); const d=btn.parentElement.parentElement.querySelector('.profile-dropdown'); const exp=btn.getAttribute('aria-expanded')==='true'; btn.setAttribute('aria-expanded', exp? 'false':'true'); d.style.display = exp? 'none':'flex'; })(event,this)">
            <img src="${img}" class="profile-btn-avatar" alt="${username}">
            <span class="profile-btn-name">${username}</span>
          </button>
          <button class="profile-go-btn" id="auth-profile-btn" title="PERFIL">PERFIL</button>
          <button class="logout-icon-btn" id="auth-logout-btn" title="LOGOUT"><img src="img/logout.png" class="logout-icon-img" alt="LOGOUT"></button>
        </div>
        <div class="profile-dropdown" role="menu" style="display:none;flex-direction:column;">
          <a href="profile.html">IR AO PERFIL</a>
          <a href="#" id="auth-logout-link">LOGOUT</a>
        </div>
      </div>
    `;
  }

  async function fetchUser(){
    try{
      const r = await fetch('php/get_user_info.php', { credentials: 'include' });
      if (!r.ok) return null;
      const j = await r.json();
      if (j && j.username) return j;
      return null;
    } catch(e){ return null; }
  }

  async function logout(){
    try{
      await fetch('php/login.php', { method: 'POST', credentials: 'include', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action: 'logout' }) });
    }catch(e){}
    window.location.reload();
  }

  async function init(){
    const authNav = document.getElementById('authNav');
    if (!authNav) return;

    const user = await fetchUser();
    if (!user) {
      authNav.innerHTML = '<a href="login.html" class="w3-bar-item w3-button w3-padding-large">LOGIN | REGISTO</a>';
      return;
    }

    authNav.innerHTML = makeProfileHtml(user);

    const logoutLink = authNav.querySelector('#auth-logout-link');
    if (logoutLink) logoutLink.addEventListener('click', function(e){ e.preventDefault(); logout(); });
    const logoutBtn = authNav.querySelector('#auth-logout-btn');
    if (logoutBtn) logoutBtn.addEventListener('click', function(e){ e.preventDefault(); logout(); });
    const profileGoBtn = authNav.querySelector('#auth-profile-btn');
    if (profileGoBtn) profileGoBtn.addEventListener('click', function(e){ e.preventDefault(); window.location.href = 'profile.html'; });

    // inject minimal CSS for logout icon and profile-go if not present
    if (!document.getElementById('auth-inline-styles')){
      const style = document.createElement('style');
      style.id = 'auth-inline-styles';
      style.textContent = '\n.profile-btn{display:flex;align-items:center;gap:10px;padding:6px 12px;border-radius:24px;background-color:rgba(2,8,2,0.9);border:1px solid #1a331a;color:#2ecc71;cursor:pointer;font-weight:700;text-transform:uppercase;}\n.profile-btn-avatar{width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #2ecc71;}\n.profile-btn-name{font-size:14px;text-transform:uppercase;}\n.profile-go-btn{background:transparent;border:1px solid rgba(255,255,255,0.04);color:#2ecc71;padding:6px 8px;border-radius:6px;cursor:pointer;font-weight:700;text-transform:uppercase;}\n.profile-go-btn:hover{background:rgba(46,204,113,0.03);}\n.profile-dropdown{position:absolute;right:0;top:calc(100% + 8px);background:#020802;border:1px solid #1a331a;padding:6px;border-radius:6px;display:none;flex-direction:column;min-width:150px;z-index:50;}\n.profile-dropdown a{color:#2ecc71;padding:8px 10px;text-decoration:none;font-weight:600;text-transform:uppercase;}\n.profile-dropdown a:hover{background:rgba(46,204,113,0.06);}\n.logout-icon-btn{background:transparent;border:1px solid rgba(255,255,255,0.06);padding:4px;border-radius:6px;cursor:pointer;text-transform:uppercase;}\n.logout-icon-img{width:18px;height:18px;display:block;}\n.logout-icon-btn:hover{background:rgba(255,0,0,0.04);}\n';
      document.head.appendChild(style);
    }

    // close dropdown on outside click
    document.addEventListener('click', function(){
      const dd = authNav.querySelector('.profile-dropdown');
      const btn = authNav.querySelector('.profile-btn');
      if (dd && btn) { dd.style.display = 'none'; btn.setAttribute('aria-expanded','false'); }
    });
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

  // Expose for manual refresh if needed
  window.auth_nav_refresh = init;
})();
