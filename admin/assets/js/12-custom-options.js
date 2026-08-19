// ─── Custom Builder Options ──────────────────────────────────────────────────
async function loadCustomOptions() {
  const r = await api('/admin/custom-options.php');
  allOpts = r.data || [];
  renderOpts();
}

function filterOpts(cat, btn) {
  currentOptCat = cat;
  document.querySelectorAll('.tab-bar .tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderOpts();
}

function renderOpts() {
  const filtered = allOpts.filter(o => o.category === currentOptCat);
  const tbody = document.querySelector('#opt-table tbody');
  tbody.innerHTML = filtered.map(o => `
    <tr>
      <td><strong>${o.title}</strong></td>
      <td><code style="background:var(--slate-100);padding:2px 6px;border-radius:4px;font-size:.8rem">${o.category}</code></td>
      <td style="color:var(--slate-500);font-size:.85rem">${o.description || '—'}</td>
      <td><strong>$${o.price}</strong></td>
      <td><span class="tag ${o.is_active ? 'tag-active' : 'tag-inactive'}">${o.is_active ? 'Active' : 'Hidden'}</span></td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-secondary" onclick='editOpt(${JSON.stringify(o)})'>Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteOpt(${o.id})">Delete</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="6" style="text-align:center;color:var(--slate-400);padding:20px;">No options for this category</td></tr>';
}

function openOptModal(data = null) {
  document.getElementById('opt-id').value      = data?.id || '';
  document.getElementById('opt-category').value= data?.category || 'websiteType';
  document.getElementById('opt-oid').value     = data?.option_id || '';
  document.getElementById('opt-title').value   = data?.title || '';
  document.getElementById('opt-desc').value    = data?.description || '';
  document.getElementById('opt-icon').value    = data?.icon || '';
  document.getElementById('opt-price').value   = data?.price || 0;
  document.getElementById('opt-sort').value    = data?.sort_order || 0;
  document.getElementById('opt-active').value  = data?.is_active !== undefined ? data.is_active : 1;
  document.getElementById('opt-modal-title').textContent = data ? 'Edit Option' : 'Add Builder Option';
  document.getElementById('opt-modal').classList.add('open');
}
function editOpt(o) { openOptModal(o); }

async function saveOpt() {
  const id = document.getElementById('opt-id').value;
  const body = {
    category:    document.getElementById('opt-category').value,
    option_id:   document.getElementById('opt-oid').value,
    title:       document.getElementById('opt-title').value,
    description: document.getElementById('opt-desc').value,
    icon:        document.getElementById('opt-icon').value,
    price:       +document.getElementById('opt-price').value,
    sort_order:  +document.getElementById('opt-sort').value,
    is_active:   +document.getElementById('opt-active').value,
  };
  const r = id
    ? await api('/admin/custom-options.php?id=' + id, 'PUT', body)
    : await api('/admin/custom-options.php', 'POST', body);
  if (r.success) { toast(r.message); closeModal('opt-modal'); loadCustomOptions(); }
  else toast(r.error, 'error');
}

async function deleteOpt(id) {
  if (!confirm('Delete this option?')) return;
  const r = await api('/admin/custom-options.php?id=' + id, 'DELETE');
  if (r.success) { toast('Option deleted'); loadCustomOptions(); }
  else toast(r.error, 'error');
}