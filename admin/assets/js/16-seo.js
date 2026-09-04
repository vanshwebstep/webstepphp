// ─── SEO Settings ─────────────────────────────────────────────────────────────
async function loadSeoSettings() {
  const r = await api('/admin/seo.php');
  const seo = r.data || [];
  const tbody = document.querySelector('#seo-table tbody');
  tbody.innerHTML = seo.map(s => `
    <tr>
      <td><strong>${s.page_url}</strong></td>
      <td>${s.title || '<span style="color:#aaa">No Title</span>'}</td>
      <td>${s.updated_at ? new Date(s.updated_at).toLocaleDateString() : '-'}</td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-secondary" onclick="editSeoSetting('${actionJson(s)}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteSeoSetting(${s.id})">x</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--slate-400);padding:20px;">No SEO settings found</td></tr>';
}

function openSeoModal(data = null) {
  document.getElementById('seo-id').value = data?.id || '';
  document.getElementById('seo-page-url').value = data?.page_url || '';
  document.getElementById('seo-title').value = data?.title || '';
  document.getElementById('seo-description').value = data?.description || '';
  document.getElementById('seo-keywords').value = data?.keywords || '';
  document.getElementById('seo-modal-title').textContent = data ? 'Edit SEO Settings' : 'New SEO Settings';
  document.getElementById('seo-modal').classList.add('open');
}

function editSeoSetting(encoded) { openSeoModal(readActionJson(encoded)); }

async function saveSeoSetting() {
  const id = document.getElementById('seo-id').value;
  const body = {
    page_url: document.getElementById('seo-page-url').value,
    title: document.getElementById('seo-title').value,
    description: document.getElementById('seo-description').value,
    keywords: document.getElementById('seo-keywords').value,
  };
  
  if(!body.page_url) {
    toast('Page URL is required', 'error');
    return;
  }

  const r = id
    ? await api('/admin/seo.php?id=' + id, 'PUT', body)
    : await api('/admin/seo.php', 'POST', body);
    
  if (r.success) { 
    toast(r.message); 
    closeModal('seo-modal'); 
    loadSeoSettings(); 
  } else {
    toast(r.error, 'error');
  }
}

async function deleteSeoSetting(id) {
  if (!confirm('Delete these SEO settings?')) return;
  const r = await api('/admin/seo.php?id=' + id, 'DELETE');
  if (r.success) { toast('SEO setting deleted'); loadSeoSettings(); }
  else toast(r.error, 'error');
}
