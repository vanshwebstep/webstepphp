// ─── Portfolio Categories ──────────────────────────────────────────────────
let allPfCategories = [];

async function loadPortfolioCategories() {
  const r = await api('/admin/portfolio-categories.php');
  allPfCategories = r.data || [];
  const tbody = document.querySelector('#pf-cat-table tbody');
  tbody.innerHTML = allPfCategories.map(c => `
    <tr>
      <td><strong>${esc(c.name)}</strong></td>
      <td>${c.sort_order}</td>
      <td><span class="tag ${+c.is_active ? 'tag-active' : 'tag-inactive'}">${+c.is_active ? 'Active' : 'Inactive'}</span></td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-secondary" onclick="editPfCat('${actionJson(c)}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deletePfCat(${c.id})">Delete</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--slate-400);padding:20px;">No categories yet</td></tr>';
}

function openPfCatModal(data = null) {
  document.getElementById('pf-cat-id').value     = data?.id || '';
  document.getElementById('pf-cat-name').value   = data?.name || '';
  document.getElementById('pf-cat-sort').value   = data?.sort_order || 0;
  document.getElementById('pf-cat-active').value = data?.is_active !== undefined ? data.is_active : 1;
  document.getElementById('pf-cat-modal-title').textContent = data ? 'Edit Category' : 'Add Category';
  document.getElementById('pf-cat-modal').classList.add('open');
}
function editPfCat(encoded) { openPfCatModal(readActionJson(encoded)); }

async function savePfCat() {
  const id = document.getElementById('pf-cat-id').value;
  const body = {
    name: document.getElementById('pf-cat-name').value,
    sort_order: +document.getElementById('pf-cat-sort').value,
    is_active: +document.getElementById('pf-cat-active').value,
  };
  const r = id
    ? await api('/admin/portfolio-categories.php?id=' + id, 'PUT', body)
    : await api('/admin/portfolio-categories.php', 'POST', body);
  if (r.success) { toast(r.message); closeModal('pf-cat-modal'); loadPortfolioCategories(); }
  else toast(r.error, 'error');
}

async function deletePfCat(id) {
  if (!confirm('Delete this category?')) return;
  const r = await api('/admin/portfolio-categories.php?id=' + id, 'DELETE');
  if (r.success) { toast('Category deleted'); loadPortfolioCategories(); }
  else toast(r.error, 'error');
}