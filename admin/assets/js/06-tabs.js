// ─── Tabs ─────────────────────────────────────────────────────────────────────
async function loadTabs() {
  const r = await api('/admin/tabs.php');
  const tbody = document.querySelector('#tabs-table tbody');
  tbody.innerHTML = (r.data||[]).map(t => `
    <tr>
      <td><strong>${t.name}</strong></td>
      <td>${t.sort_order}</td>
      <td><span class="tag ${t.is_active ? 'tag-active' : 'tag-inactive'}">${t.is_active ? 'Active' : 'Inactive'}</span></td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-secondary" onclick='editTab(${JSON.stringify(t)})'>Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteTab(${t.id})">Delete</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--slate-400);padding:20px;">No categories yet</td></tr>';
}

function openTabModal(data = null) {
  document.getElementById('tab-id').value    = data?.id || '';
  document.getElementById('tab-name').value  = data?.name || '';
  document.getElementById('tab-sort').value  = data?.sort_order || 0;
  document.getElementById('tab-active').value= data?.is_active !== undefined ? data.is_active : 1;
  document.getElementById('tab-modal-title').textContent = data ? 'Edit Category' : 'Add Category';
  document.getElementById('tab-modal').classList.add('open');
}
function editTab(t) { openTabModal(t); }

async function saveTab() {
  const id   = document.getElementById('tab-id').value;
  const body = {
    name: document.getElementById('tab-name').value,
    sort_order: +document.getElementById('tab-sort').value,
    is_active: +document.getElementById('tab-active').value,
  };
  const r = id
    ? await api('/admin/tabs.php?id=' + id, 'PUT', body)
    : await api('/admin/tabs.php', 'POST', body);
  if (r.success) { toast(r.message); closeModal('tab-modal'); loadTabs(); }
  else toast(r.error, 'error');
}

async function deleteTab(id) {
  if (!confirm('Delete this category?')) return;
  const r = await api('/admin/tabs.php?id=' + id, 'DELETE');
  if (r.success) { toast('Category deleted'); loadTabs(); }
  else toast(r.error, 'error');
}