// ─── Case Studies ──────────────────────────────────────────────────────────────
async function loadCaseStudies() {
  const r = await api('/admin/case-studies.php');
  allCases = r.data || [];
  const tbody = document.querySelector('#case-table tbody');
  tbody.innerHTML = allCases.map(c => `
    <tr>
      <td><strong>${esc(c.title)}</strong><br><small style="color:var(--slate-400)">${esc(c.slug)}</small></td>
      <td><span class="tag" style="background:#ede9fe;color:#7c3aed;">${esc(c.category)}</span></td>
      <td>${esc(c.client || '-')}</td>
      <td>${+c.featured ? '<span class="tag tag-popular">Featured</span>' : '-'}</td>
      <td>${esc(c.year || '-')}</td>
      <td><span class="tag ${+c.is_active ? 'tag-active' : 'tag-inactive'}">${+c.is_active ? 'Active' : 'Hidden'}</span></td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-secondary" onclick="editCaseStudy('${actionJson(c)}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteCaseStudy(${c.id})">Delete</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="7" style="text-align:center;color:var(--slate-400);padding:20px;">No case studies yet</td></tr>';
}

function metricLines(metrics = []) {
  return metrics.map(m => `${m.label || ''}: ${m.val || m.value || ''}`.trim()).join('\n');
}

function openCaseModal(data = null) {
  document.getElementById('case-id').value = data?.id || '';
  document.getElementById('case-title').value = data?.title || '';
  document.getElementById('case-slug').value = data?.slug || '';
  document.getElementById('case-tagline').value = data?.tagline || '';
  document.getElementById('case-category').value = data?.category || '';
  document.getElementById('case-client').value = data?.client || '';
  document.getElementById('case-logo').value = data?.logo || '';
  document.getElementById('case-image').value = data?.image || '';
const caseImg = document.getElementById('case-image-preview-img');
if (data?.image) { caseImg.src = data.image; caseImg.style.display = 'inline-block'; }
else { caseImg.style.display = 'none'; }
document.getElementById('case-image-file').value = '';
  document.getElementById('case-duration').value = data?.duration || '';
  document.getElementById('case-year').value = data?.year || new Date().getFullYear();
  document.getElementById('case-featured').value = data?.featured ? 1 : 0;
  setMultiSelectValues('case-tags', data?.tags || []);
  setMultiSelectValues('case-tech', data?.tech || []);
  document.getElementById('case-challenge').value = data?.challenge || '';
  document.getElementById('case-solution').value = data?.solution || '';
  document.getElementById('case-result').value = data?.result || '';
  document.getElementById('case-metrics').value = metricLines(data?.metrics || []);
  document.getElementById('case-deliverables').value = (data?.deliverables || []).join(', ');
  document.getElementById('case-testimonial-quote').value = data?.testimonial_quote || '';
  document.getElementById('case-testimonial-author').value = data?.testimonial_author || '';
  document.getElementById('case-testimonial-role').value = data?.testimonial_role || '';
  document.getElementById('case-testimonial-avatar').value = data?.testimonial_avatar || '';
  document.getElementById('case-color').value = data?.color || '#6366f1';
  document.getElementById('case-accent').value = data?.accent || '#e0e7ff';
  document.getElementById('case-light-text').value = data?.light_text || '#4338ca';
  document.getElementById('case-dark-bg').value = data?.dark_bg || '#4338ca';
  document.getElementById('case-sort').value = data?.sort_order || 0;
  document.getElementById('case-active').value = data?.is_active !== undefined ? data.is_active : 1;
  document.getElementById('case-modal-title').textContent = data ? 'Edit Case Study' : 'New Case Study';
  document.getElementById('case-modal').classList.add('open');
}

function editCaseStudy(encoded) { openCaseModal(readActionJson(encoded)); }

async function saveCaseStudy() {
  const id = document.getElementById('case-id').value;
  const body = {
    title: document.getElementById('case-title').value,
    slug: document.getElementById('case-slug').value,
    tagline: document.getElementById('case-tagline').value,
    category: document.getElementById('case-category').value,
    client: document.getElementById('case-client').value,
    logo: document.getElementById('case-logo').value,
    image: document.getElementById('case-image').value,
    duration: document.getElementById('case-duration').value,
    year: document.getElementById('case-year').value,
    featured: +document.getElementById('case-featured').value,
    tags: getMultiSelectValues('case-tags'),
    tech: getMultiSelectValues('case-tech'),
    challenge: document.getElementById('case-challenge').value,
    solution: document.getElementById('case-solution').value,
    result: document.getElementById('case-result').value,
    metrics: linesToMetrics(document.getElementById('case-metrics').value),
    deliverables: csvToArray(document.getElementById('case-deliverables').value),
    testimonial_quote: document.getElementById('case-testimonial-quote').value,
    testimonial_author: document.getElementById('case-testimonial-author').value,
    testimonial_role: document.getElementById('case-testimonial-role').value,
    testimonial_avatar: document.getElementById('case-testimonial-avatar').value,
    color: document.getElementById('case-color').value,
    accent: document.getElementById('case-accent').value,
    light_text: document.getElementById('case-light-text').value,
    dark_bg: document.getElementById('case-dark-bg').value,
    sort_order: +document.getElementById('case-sort').value,
    is_active: +document.getElementById('case-active').value,
  };
  const r = id
    ? await api('/admin/case-studies.php?id=' + id, 'PUT', body)
    : await api('/admin/case-studies.php', 'POST', body);
  if (r.success) { toast(r.message); closeModal('case-modal'); loadCaseStudies(); }
  else toast(r.error, 'error');
}

async function deleteCaseStudy(id) {
  if (!confirm('Delete this case study?')) return;
  const r = await api('/admin/case-studies.php?id=' + id, 'DELETE');
  if (r.success) { toast('Case study deleted'); loadCaseStudies(); }
  else toast(r.error, 'error');
}
function uploadCaseImage(input) {
  uploadImage(input, 'case-studies', 'case-image', 'case-image-preview-img', 'case-image-uploading');
}