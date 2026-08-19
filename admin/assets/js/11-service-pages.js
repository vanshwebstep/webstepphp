// ─── Service Pages ───────────────────────────────────────────────────────────
async function loadServicePages() {
  const r = await api('/admin/service-pages.php');
  const pages = r.data || [];
  const tbody = document.querySelector('#service-pages-table tbody');
  tbody.innerHTML = pages.map(p => `
    <tr>
      <td><strong>${p.title}</strong></td>
      <td><code>${p.slug}</code></td>
      <td><span class="tag ${p.is_active ? 'tag-active' : 'tag-inactive'}">${p.is_active ? 'Active' : 'Hidden'}</span></td>
      <td>${p.updated_at ? new Date(p.updated_at).toLocaleDateString() : '-'}</td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-secondary" onclick="editServicePage('${actionJson(p)}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteServicePage(${p.id})">x</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="5" style="text-align:center;color:var(--slate-400);padding:20px;">No service pages yet</td></tr>';
}

function openServicePageModal(data = null) {
  document.getElementById('service-page-id').value = data?.id || '';
  document.getElementById('service-page-title').value = data?.title || '';
  document.getElementById('service-page-slug').value = data?.slug || '';
  document.getElementById('service-page-sort').value = data?.sort_order || 0;
  document.getElementById('service-page-active').value = data?.is_active !== undefined ? data.is_active : 1;
  document.getElementById('service-page-content').value = JSON.stringify(data?.content || {}, null, 2);
  document.getElementById('service-page-modal-title').textContent = data ? 'Edit Service Page' : 'New Service Page';
  document.getElementById('service-page-modal').classList.add('open');
}

function editServicePage(encoded) { openServicePageModal(readActionJson(encoded)); }

async function saveServicePage() {
  const id = document.getElementById('service-page-id').value;
  let content;
  try {
    content = JSON.parse(document.getElementById('service-page-content').value || '{}');
  } catch (error) {
    toast('Content JSON is invalid', 'error');
    return;
  }
  const body = {
    title: document.getElementById('service-page-title').value,
    slug: document.getElementById('service-page-slug').value,
    sort_order: +document.getElementById('service-page-sort').value,
    is_active: +document.getElementById('service-page-active').value,
    content,
  };
  const r = id
    ? await api('/admin/service-pages.php?id=' + id, 'PUT', body)
    : await api('/admin/service-pages.php', 'POST', body);
  if (r.success) { toast(r.message); closeModal('service-page-modal'); loadServicePages(); }
  else toast(r.error, 'error');
}

async function deleteServicePage(id) {
  if (!confirm('Delete this service page?')) return;
  const r = await api('/admin/service-pages.php?id=' + id, 'DELETE');
  if (r.success) { toast('Service page deleted'); loadServicePages(); }
  else toast(r.error, 'error');
}