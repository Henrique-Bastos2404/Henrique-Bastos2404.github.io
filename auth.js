// Simplified auth/navigation script — initializes #authNav reliably on every page
(function(){
  'use strict';

  function escapeHtml(s){ return String(s||'').replace(/[&<>"'']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]; }); }

  function makeProfileHtml(u){
    const username = escapeHtml((u.username || '').toUpperCase());
    const img = u.profile_image || 'default-avatar.svg';
    return `
      <div class="profile-menu">
        <button class="profile-btn" id="auth-profile-btn" title="PERFIL">
          <span class="profile-btn-avatar-wrap">
            <img src="${img}" class="profile-btn-avatar" alt="${username}">
          </span>
          <span class="profile-btn-name">${username}</span>
        </button>
        <button class="profile-logout-btn" id="auth-logout-link" title="LOGOUT">LOGOUT</button>
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

    authNav.querySelectorAll('.profile-dropdown').forEach(function(el){ el.remove(); });
    authNav.querySelectorAll('.profile-btn').forEach(function(el){ el.removeAttribute('onclick'); });

    const user = await fetchUser();
    if (!user) {
      authNav.innerHTML = '<a href="login.html" class="w3-bar-item w3-button w3-padding-large">LOGIN | REGISTO</a>';
      return;
    }

    authNav.innerHTML = makeProfileHtml(user);

    const profileBtn = authNav.querySelector('#auth-profile-btn');
    const logoutLink = authNav.querySelector('#auth-logout-link');
    if (profileBtn) profileBtn.addEventListener('click', function(){ window.location.href = 'profile.html'; });
    if (logoutLink) logoutLink.addEventListener('click', function(e){ e.preventDefault(); logout(); });

    // inject shared navbar auth styles if not present
    if (!document.getElementById('auth-inline-styles')){
      const style = document.createElement('style');
      style.id = 'auth-inline-styles';
      style.textContent = '\n#authNav{display:flex !important;justify-content:flex-end !important;align-items:center !important;min-width:0 !important;}\
\n#authNav .profile-menu{position:relative !important;display:inline-flex !important;align-items:center !important;justify-content:flex-end !important;max-width:100% !important;width:auto !important;flex:0 0 auto !important;}\
\n#authNav .profile-btn{display:inline-flex !important;align-items:center !important;gap:12px !important;max-width:100% !important;padding:8px 16px !important;border-radius:999px !important;background-color:#020802 !important;border:1px solid #1a331a !important;color:#2ecc71 !important;cursor:pointer !important;font-weight:700 !important;text-transform:uppercase !important;line-height:1 !important;white-space:nowrap !important;box-shadow:none !important;}\
\n#authNav .profile-btn:hover{background-color:#061106 !important;}\
\n#authNav .profile-btn-avatar-wrap{width:42px !important;height:42px !important;display:flex !important;align-items:center !important;justify-content:center !important;flex:0 0 42px !important;border-radius:50% !important;overflow:hidden !important;background:#0b170b !important;border:2px solid #2ecc71 !important;}\
\n#authNav .profile-btn-avatar{width:100% !important;height:100% !important;display:block !important;object-fit:cover !important;border-radius:50% !important;}\
\n#authNav .profile-btn-name{display:block !important;max-width:140px !important;overflow:hidden !important;text-overflow:ellipsis !important;white-space:nowrap !important;font-size:14px !important;line-height:1.1 !important;text-transform:uppercase !important;color:#2ecc71 !important;}\
\n#authNav .profile-dropdown{display:none !important;}\
\n#authNav .profile-logout-btn{margin-left:10px !important;padding:8px 12px !important;border-radius:999px !important;background:transparent !important;border:1px solid rgba(255,102,102,0.22) !important;color:#ff8d8d !important;cursor:pointer !important;font-weight:700 !important;text-transform:uppercase !important;line-height:1 !important;}\
\n#authNav .profile-logout-btn:hover{background:rgba(255,102,102,0.08) !important;color:#ffd2d2 !important;}\
\n@media (max-width: 640px){#authNav .profile-btn{padding:6px 10px !important;gap:8px !important;}#authNav .profile-btn-avatar-wrap{width:36px !important;height:36px !important;flex-basis:36px !important;}#authNav .profile-btn-name{max-width:92px !important;font-size:12px !important;}#authNav .profile-logout-btn{padding:6px 10px !important;font-size:12px !important;}}\
\n';
      document.head.appendChild(style);
    }
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
  window.addEventListener('load', init);
  setTimeout(init, 150);
  setTimeout(init, 600);
  setTimeout(init, 1200);

  // Expose for manual refresh if needed
  window.auth_nav_refresh = init;
})();
