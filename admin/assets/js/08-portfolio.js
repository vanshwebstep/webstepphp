// ─── Portfolio ───────────────────────────────────────────────────────────────
async function loadPortfolio() {
  const r = await api('/admin/portfolio.php');
  allPortfolio = r.data || [];
  const tbody = document.querySelector('#portfolio-table tbody');
  tbody.innerHTML = allPortfolio.map(p => `
    <tr>
      <td><strong>${esc(p.title)}</strong></td>
      <td>${esc(p.category || '-')}</td>
      <td style="max-width:220px">
        <div style="display:flex;flex-wrap:wrap;gap:4px;">
          ${(p.tags || []).map(t => `<span class="chip">${esc(t)}</span>`).join('')}
        </div>
      </td>
      <td style="max-width:220px">
        <div style="display:flex;flex-wrap:wrap;gap:4px;">
          ${(p.tech || []).map(t => `<span class="chip" style="background:#ecfeff;color:#0e7490;">${esc(t)}</span>`).join('')}
        </div>
      </td>
      <td>${esc(p.year || '-')}</td>
      <td><span class="tag ${+p.is_active ? 'tag-active' : 'tag-inactive'}">${+p.is_active ? 'Active' : 'Hidden'}</span></td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-secondary" onclick="editPortfolio('${actionJson(p)}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deletePortfolio(${p.id})">Delete</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="7" style="text-align:center;color:var(--slate-400);padding:20px;">No portfolio projects yet</td></tr>';
}

async function openPortfolioModal(data = null) {
  const sel = document.getElementById('portfolio-tags');

  try {
    const cats = await api('/admin/portfolio-categories.php');
    sel.innerHTML = (cats.data || []).map(c => `<option value="${esc(c.name)}">${esc(c.name)}</option>`).join('');
  } catch (err) {
    console.error('Failed to load portfolio categories:', err);
    sel.innerHTML = '<option disabled>⚠ Failed to load categories</option>';
    toast('Could not load categories — check admin/portfolio-categories.php', 'error');
  }

  document.getElementById('portfolio-id').value = data?.id || '';
  document.getElementById('portfolio-title').value = data?.title || '';
  document.getElementById('portfolio-label').value = data?.category || '';
  document.getElementById('portfolio-image').value = data?.image || '';
  const img = document.getElementById('portfolio-image-preview-img');
  if (data?.image) { img.src = data.image; img.style.display = 'inline-block'; }
  else { img.style.display = 'none'; }
  document.getElementById('portfolio-image-file').value = '';
  document.getElementById('portfolio-desc').value = data?.description || '';
  setMultiSelectValues('portfolio-tags', data?.tags || []);
  setMultiSelectValues('portfolio-tech', data?.tech || []);
  document.getElementById('portfolio-year').value = data?.year || new Date().getFullYear();
  document.getElementById('portfolio-sort').value = data?.sort_order || 0;
  document.getElementById('portfolio-active').value = data?.is_active !== undefined ? data.is_active : 1;
  document.getElementById('portfolio-modal-title').textContent = data ? 'Edit Portfolio Project' : 'New Portfolio Project';
  document.getElementById('portfolio-modal').classList.add('open');
}

function editPortfolio(encoded) { openPortfolioModal(readActionJson(encoded)); }

async function savePortfolio() {
  const id = document.getElementById('portfolio-id').value;
  const body = {
    title: document.getElementById('portfolio-title').value,
    category: document.getElementById('portfolio-label').value,
    image: document.getElementById('portfolio-image').value,
    description: document.getElementById('portfolio-desc').value,
    tags: getMultiSelectValues('portfolio-tags'),
    tech: getMultiSelectValues('portfolio-tech'),
    year: document.getElementById('portfolio-year').value,
    sort_order: +document.getElementById('portfolio-sort').value,
    is_active: +document.getElementById('portfolio-active').value,
  };
  const r = id
    ? await api('/admin/portfolio.php?id=' + id, 'PUT', body)
    : await api('/admin/portfolio.php', 'POST', body);
  if (r.success) { toast(r.message); closeModal('portfolio-modal'); loadPortfolio(); }
  else toast(r.error, 'error');
}
function uploadPortfolioImage(input) {
  uploadImage(input, 'portfolio', 'portfolio-image', 'portfolio-image-preview-img', 'portfolio-image-uploading');
}
async function deletePortfolio(id) {
  if (!confirm('Delete this portfolio project?')) return;
  const r = await api('/admin/portfolio.php?id=' + id, 'DELETE');
  if (r.success) { toast('Portfolio project deleted'); loadPortfolio(); }
  else toast(r.error, 'error');
}