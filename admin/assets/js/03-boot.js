// ─── Auth ─────────────────────────────────────────────────────────────────────
async function doLogin() {
  const username = document.getElementById('login-user').value;
  const password = document.getElementById('login-pass').value;
  const r = await api('/admin/auth.php?action=login', 'POST', { username, password });
  if (r.success) {
    document.getElementById('login-screen').style.display = 'none';
    document.getElementById('app').style.display = 'block';
    initApp();
  } else {
    const err = document.getElementById('login-err');
    err.textContent = r.error || 'Login failed';
    err.style.display = 'block';
  }
}

async function doLogout() {
  await api('/admin/auth.php?action=logout');
  location.reload();
}

document.getElementById('login-pass').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

// ─── Init ─────────────────────────────────────────────────────────────────────
function initApp() { loadDashboard(); }

// ─── Check existing session on page load/refresh ──────────────────────────────
async function checkSession() {
  try {
    const r = await api('/admin/auth.php?action=check');
    if (r.success) {
      document.getElementById('app').style.display = 'block';
      initApp();
    } else {
      document.getElementById('login-screen').style.display = 'flex';
    }
  } catch (e) {
    document.getElementById('login-screen').style.display = 'flex';
  } finally {
    const loader = document.getElementById('boot-loader');
    if (loader) loader.remove();
  }
}

checkSession();