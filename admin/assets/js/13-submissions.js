// ─── Submissions ──────────────────────────────────────────────────────────────
async function loadSubs(type = 'custom', status = '') {
  currentSubType = type;
  const r = await api(`/admin/submissions.php?type=${type}${status ? '&status=' + status : ''}`);
  allSubs = r.data || [];
  renderSubs();
}

function filterSubs(status, btn) {
  currentSubFilter = status;
  document.querySelectorAll('#page-custom-submissions .tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadSubs('custom', status);
}

function renderSubs() {
  const tbody = document.querySelector('#subs-table tbody');
  tbody.innerHTML = allSubs.map(s => {
    const pkg = `${s.website_type||'—'} | ${s.pages||'—'} | ${s.design||'—'}`;
    const isCustom = (s.source || 'custom-package') === 'custom-package';
    const displayPkg = isCustom ? pkg : (s.message || 'General website lead');
    const total = Number(s.estimated_total || 0);
    return `
      <tr>
        <td><strong>${s.name}</strong></td>
        <td><span class="tag" style="background:#f3e8ff;color:#7e22ce;">${s.source || 'custom-package'}</span></td>
        <td>${s.email}</td>
        <td>${s.phone || '—'}</td>
        <td style="font-size:.8rem;color:var(--slate-500);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${displayPkg}</td>
        <td><strong>${total > 0 ? '$' + total : '—'}</strong></td>
        <td><span class="tag tag-${s.status}">${s.status}</span></td>
        <td>${new Date(s.created_at).toLocaleDateString()}</td>
        <td style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-secondary" onclick="viewSub(${s.id}, 'custom')">View</button>
          <button class="btn btn-sm btn-danger" onclick="deleteSub(${s.id}, 'custom')">✕</button>
        </td>
      </tr>
    `;
  }).join('') || '<tr><td colspan="9" style="text-align:center;color:var(--slate-400);padding:20px;">No submissions yet</td></tr>';
}

async function loadInquiries() {
  const r = await api('/admin/submissions.php?type=inquiry');
  const tbody = document.querySelector('#inq-table tbody');
  tbody.innerHTML = (r.data||[]).map(s => `
    <tr>
      <td><strong>${s.name}</strong></td>
      <td>${s.email}</td>
      <td>${s.phone || '—'}</td>
      <td>${s.package_id || '—'}</td>
      <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${s.message || '—'}</td>
      <td><span class="tag tag-${s.status}">${s.status}</span></td>
      <td>${new Date(s.created_at).toLocaleDateString()}</td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-secondary" onclick="viewSub(${s.id},'inquiry')">View</button>
        <button class="btn btn-sm btn-danger" onclick="deleteSub(${s.id},'inquiry')">✕</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="8" style="text-align:center;color:var(--slate-400);padding:20px;">No inquiries yet</td></tr>';
}

async function viewSub(id, type) {
  currentSubId   = id;
  currentSubType = type;
  const list   = type === 'custom' ? allSubs : [];
  let sub = list.find(s => s.id == id);

  if (!sub) {
    const r = await api(`/admin/submissions.php?type=${type}&id=${id}`);
    sub = r.data?.[0];
  }
  if (!sub) return;

  document.getElementById('sub-status-sel').value = sub.status;

  let html = `<div class="detail-grid">
    <div class="detail-field"><label>Name</label><span>${sub.name}</span></div>
    <div class="detail-field"><label>Email</label><span>${sub.email}</span></div>
    <div class="detail-field"><label>Phone</label><span>${sub.phone||'—'}</span></div>
    <div class="detail-field"><label>Date</label><span>${new Date(sub.created_at).toLocaleString()}</span></div>
    <div class="detail-field"><label>Source</label><span>${sub.source||'custom-package'}</span></div>
  `;

  if (type === 'custom') {
    html += `
    <div class="detail-field"><label>Website Type</label><span>${sub.website_type||'—'}</span></div>
    <div class="detail-field"><label>Pages</label><span>${sub.pages||'—'}</span></div>
    <div class="detail-field"><label>Design</label><span>${sub.design||'—'}</span></div>
    <div class="detail-field"><label>Estimated Total</label><span><strong>$${sub.estimated_total}</strong></span></div>
    </div>`;
    const feats = sub.features || [];
    if (feats.length) {
      html += `<div style="margin-top:12px"><label style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--slate-500);">Extra Features</label>
      <div class="features-chips" style="margin-top:8px">${feats.map(f=>`<span class="chip">${f.title}</span>`).join('')}</div></div>`;
    }
  } else {
    html += `<div class="detail-field" style="grid-column:1/-1"><label>Message</label><span>${sub.message||'—'}</span></div></div>`;
  }

  if (sub.message && type === 'custom') {
    html += `<div class="detail-field" style="margin-top:12px"><label>Message</label><span>${sub.message}</span></div>`;
  }

  document.getElementById('sub-detail-body').innerHTML = html;
  document.getElementById('sub-detail-modal').classList.add('open');

  // Mark as viewed
  if (sub.status === 'new') {
    await api(`/admin/submissions.php?type=${type}&id=${id}`, 'PUT', { status: 'viewed' });
    if (type === 'custom') loadSubs('custom', currentSubFilter);
    else loadInquiries();
  }
}

async function updateSubStatus() {
  const status = document.getElementById('sub-status-sel').value;
  const r = await api(`/admin/submissions.php?type=${currentSubType}&id=${currentSubId}`, 'PUT', { status });
  if (r.success) {
    toast('Status updated');
    closeModal('sub-detail-modal');
    if (currentSubType === 'custom') loadSubs('custom', currentSubFilter);
    else loadInquiries();
  } else toast(r.error, 'error');
}

async function deleteSub(id, type) {
  if (!confirm('Delete this submission?')) return;
  const r = await api(`/admin/submissions.php?type=${type}&id=${id}`, 'DELETE');
  if (r.success) {
    toast('Deleted');
    if (type === 'custom') loadSubs('custom', currentSubFilter);
    else loadInquiries();
  } else toast(r.error, 'error');
}