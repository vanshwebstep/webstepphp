// ─── Packages ─────────────────────────────────────────────────────────────────
async function loadPackages() {
  const r = await api('/admin/packages.php');
  const tbody = document.querySelector('#pkg-table tbody');
  tbody.innerHTML = (r.data||[]).map(p => `
  <tr>
    <td><strong>${p.title}</strong></td>
    <td><span class="tag" style="background:#ede9fe;color:#7c3aed;">${p.category_name}</span></td>
    <td>${p.symbol}${p.price} <small style="color:var(--slate-400)">${p.price_desc}</small></td>
    <td>${p.isPopular ? '<span class="tag tag-popular">⭐ Popular</span>' : '—'}</td>
    <td><span class="tag ${p.is_active ? 'tag-active' : 'tag-inactive'}">${p.is_active ? 'Active' : 'Hidden'}</span></td>
    <td style="display:flex;gap:6px;">
      <button class="btn btn-sm btn-secondary" onclick='editPkg(${JSON.stringify(p)})'>Edit</button>
      <button class="btn btn-sm btn-danger" onclick="deletePkg(${p.id})">Delete</button>
    </td>
  </tr>
`).join('') || '<tr><td colspan="6" style="text-align:center;color:var(--slate-400);padding:20px;">No packages yet</td></tr>';
}

async function openPkgModal(data = null) {
  const tabs = await api('/admin/tabs.php');
  const sel = document.getElementById('pkg-category');
  sel.innerHTML = (tabs.data||[]).map(t => `<option value="${t.id}">${t.name}</option>`).join('');

  document.getElementById('pkg-id').value       = data?.id || '';
  document.getElementById('pkg-title').value    = data?.title || '';
  document.getElementById('pkg-desc').value     = data?.description || '';
  document.getElementById('pkg-symbol').value   = data?.symbol || '$';
  document.getElementById('pkg-price').value    = data?.price || 0;
  document.getElementById('pkg-pricedesc').value= data?.price_desc || '/project';
  document.getElementById('pkg-btn').value      = data?.btn_text || 'Choose Plan';
  document.getElementById('pkg-sort').value     = data?.sort_order || 0;
  document.getElementById('pkg-popular').value  = data?.isPopular ? 1 : 0;
  if (data?.category_id) sel.value = data.category_id;

  const fl = document.getElementById('features-list');
  fl.innerHTML = '';
  (data?.features || ['']).forEach(f => addFeatureRow(f));
  if (!data?.features?.length) addFeatureRow();

  document.getElementById('pkg-modal-title').textContent = data ? 'Edit Package' : 'New Package';
  document.getElementById('pkg-modal').classList.add('open');
}

function editPkg(p) { openPkgModal(p); }

function addFeatureRow(val = '') {
  const fl = document.getElementById('features-list');
  const div = document.createElement('div');
  div.className = 'feature-item';
  div.innerHTML = `
    <input class="feature-input" type="text" placeholder="Feature text..." value="${val}">
    <button class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
  `;
  fl.appendChild(div);
}

async function savePkg() {
  const id = document.getElementById('pkg-id').value;
  const features = [...document.querySelectorAll('.feature-input')]
    .map(i => i.value.trim()).filter(Boolean);

  const body = {
    title: document.getElementById('pkg-title').value,
    category_id: +document.getElementById('pkg-category').value,
    description: document.getElementById('pkg-desc').value,
    symbol: document.getElementById('pkg-symbol').value,
    price: +document.getElementById('pkg-price').value,
    price_desc: document.getElementById('pkg-pricedesc').value,
    btn_text: document.getElementById('pkg-btn').value,
    sort_order: +document.getElementById('pkg-sort').value,
    is_popular: +document.getElementById('pkg-popular').value,
    features,
  };

  const r = id
    ? await api('/admin/packages.php?id=' + id, 'PUT', body)
    : await api('/admin/packages.php', 'POST', body);

  if (r.success) { toast(r.message); closeModal('pkg-modal'); loadPackages(); }
  else toast(r.error, 'error');
}

async function deletePkg(id) {
  if (!confirm('Delete this package?')) return;
  const r = await api('/admin/packages.php?id=' + id, 'DELETE');
  if (r.success) { toast('Package deleted'); loadPackages(); }
  else toast(r.error, 'error');
}