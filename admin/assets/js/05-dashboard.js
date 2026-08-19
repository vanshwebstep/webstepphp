// ─── Dashboard ────────────────────────────────────────────────────────────────
async function loadDashboard() {
  const [pkgs, tabs, subs, inqs] = await Promise.all([
    api('/admin/packages.php'), api('/admin/tabs.php'),
    api('/admin/submissions.php?type=custom'), api('/admin/submissions.php?type=inquiry'),
  ]);
  const newCount = (subs.data||[]).filter(s=>s.status==='new').length + (inqs.data||[]).filter(s=>s.status==='new').length;
  document.getElementById('s-packages').textContent = (pkgs.data||[]).length;
  document.getElementById('s-tabs').textContent     = (tabs.data||[]).length;
  document.getElementById('s-custom').textContent   = (subs.data||[]).length;
  document.getElementById('s-inq').textContent      = (inqs.data||[]).length;
  document.getElementById('s-new').textContent      = newCount;
  document.getElementById('badge-custom').textContent = (subs.data||[]).filter(s=>s.status==='new').length;
  document.getElementById('badge-inq').textContent    = (inqs.data||[]).filter(s=>s.status==='new').length;

  const tbody = document.querySelector('#dash-custom-table tbody');
  tbody.innerHTML = (subs.data||[]).slice(0,8).map(s => `
    <tr>
      <td><strong>${s.name}</strong></td>
      <td>${s.email}</td>
      <td><strong>$${s.estimated_total}</strong></td>
      <td><span class="tag tag-${s.status}">${s.status}</span></td>
      <td>${new Date(s.created_at).toLocaleDateString()}</td>
    </tr>
  `).join('') || '<tr><td colspan="5" style="text-align:center;color:var(--slate-400);padding:20px;">No submissions yet</td></tr>';
}