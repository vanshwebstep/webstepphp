// ─── Quill Editor ───────────────────────────────────────────────────────────
function initQuillEditor() {
  if (quillEditor) return; // already initialized, don't re-create
  quillEditor = new Quill('#blog-content-editor', {
    theme: 'snow',
    placeholder: 'Write your blog content here...',
    modules: {
      toolbar: [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline'],
        ['blockquote'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
        ['clean']
      ]
    }
  });
}

// ─── Fetch helper ─────────────────────────────────────────────────────────────
async function api(path, method = 'GET', body = null) {
  const opts = { method, credentials: 'include', headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(BASE + path, opts);
  return res.json();
}

function esc(value = '') {
  return String(value ?? '').replace(/[&<>"']/g, ch => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[ch]));
}

function csvToArray(value = '') {
  return String(value || '').split(',').map(v => v.trim()).filter(Boolean);
}

function getMultiSelectValues(id) {
  return [...document.getElementById(id).selectedOptions].map(option => option.value);
}

function setMultiSelectValues(id, values = []) {
  const select = document.getElementById(id);
  const normalizedValues = values.map(value => String(value));
  normalizedValues.forEach(value => {
    if (![...select.options].some(option => option.value === value)) {
      select.add(new Option(value, value));
    }
  });
  [...select.options].forEach(option => {
    option.selected = normalizedValues.includes(option.value);
  });
}

function linesToMetrics(value = '') {
  return String(value || '').split('\n').map(line => {
    const [label, ...rest] = line.split(':');
    return { label: (label || '').trim(), val: rest.join(':').trim() };
  }).filter(m => m.label && m.val);
}

function actionJson(value) {
  return encodeURIComponent(JSON.stringify(value)).replace(/'/g, '%27');
}

function readActionJson(value) {
  return JSON.parse(decodeURIComponent(value));
}

// ─── Toast ───────────────────────────────────────────────────────────────────
function toast(msg, type = 'success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = `toast ${type} show`;
  setTimeout(() => el.className = 'toast', 2800);
}

// ─── Modal helpers ────────────────────────────────────────────────────────────
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

// ─── Generic Image Upload ──────────────────────────────────────────────────
async function uploadImage(input, folder, hiddenId, previewImgId, uploadingId) {
  const file = input.files[0];
  if (!file) return;

  document.getElementById(uploadingId).style.display = 'inline';
  document.getElementById(previewImgId).style.display = 'none';

  const fd = new FormData();
  fd.append('file', file);
  fd.append('folder', folder);

  try {
    const res = await fetch(BASE + '/admin/upload.php', {
      method: 'POST',
      credentials: 'include',
      body: fd,
    });
    const r = await res.json();
    document.getElementById(uploadingId).style.display = 'none';

    if (r.success) {
      document.getElementById(hiddenId).value = r.data.url;
      const img = document.getElementById(previewImgId);
      img.src = r.data.url;
      img.style.display = 'inline-block';
      toast(r.message);
    } else {
      toast(r.error, 'error');
    }
  } catch (e) {
    document.getElementById(uploadingId).style.display = 'none';
    toast('Upload failed', 'error');
  }
}