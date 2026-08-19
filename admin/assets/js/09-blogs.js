// ─── Blogs ────────────────────────────────────────────────────────────────────
async function loadBlogs() {
  const r = await api('/admin/blogs.php');
  allBlogs = r.data || [];
  const tbody = document.querySelector('#blog-table tbody');
  tbody.innerHTML = allBlogs.map(b => `
    <tr>
      <td><img src="${esc(b.image || '')}" style="width:70px;height:50px;object-fit:cover;border-radius:8px;"></td>
      <td><strong>${esc(b.title)}</strong></td>
      <td>${esc(b.category || '-')}</td>
      <td>${esc(b.author || '-')}</td>
      <td>${esc(b.published_date || '-')}</td>
      <td><span class="tag ${+b.featured ? 'tag-popular' : ''}">${+b.featured ? 'Yes' : 'No'}</span></td>
      <td><span class="tag ${+b.is_active ? 'tag-active' : 'tag-inactive'}">${+b.is_active ? 'Active' : 'Hidden'}</span></td>
      <td style="display:flex;gap:6px">
        <button class="btn btn-sm btn-secondary" onclick="editBlog('${actionJson(b)}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteBlog(${b.id})">Delete</button>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="8" style="text-align:center;padding:20px;">No Blogs Found</td></tr>`;
}

function openBlogModal(data = null) {
  document.getElementById('blog-id').value = data?.id || '';
  document.getElementById('blog-title').value = data?.title || '';
  document.getElementById('blog-slug').value = data?.slug || '';
  document.getElementById('blog-category').value = data?.category || '';
  document.getElementById('blog-author').value = data?.author || '';
  document.getElementById('blog-author-avatar').value = data?.author_avatar || '';
  document.getElementById('blog-author-bio').value = data?.author_bio || '';
  document.getElementById('blog-image').value = data?.image || '';
  document.getElementById('blog-excerpt').value = data?.excerpt || '';

  initQuillEditor();
  quillEditor.root.innerHTML = data?.content || '';

  document.getElementById('blog-published-date').value = data?.published_date || '';
  document.getElementById('blog-read-time').value = data?.read_time || '';
  document.getElementById('blog-views').value = data?.views || 0;
  document.getElementById('blog-likes').value = data?.likes || 0;
  document.getElementById('blog-featured').value = data?.featured || 0;
  document.getElementById('blog-sort').value = data?.sort_order || 0;
  document.getElementById('blog-active').value = data?.is_active ?? 1;
  document.getElementById('blog-seo-title').value = data?.seo_title || '';
  document.getElementById('blog-seo-description').value = data?.seo_description || '';
  document.getElementById('blog-tags').value = (data?.tags || []).join(',');

  document.getElementById('blog-modal-title').innerHTML = data ? 'Edit Blog' : 'New Blog';
  document.getElementById('blog-modal').classList.add('open');
}

function editBlog(data) {
  openBlogModal(readActionJson(data));
}

async function saveBlog() {
  const id = document.getElementById('blog-id').value;
  const body = {
    title: document.getElementById('blog-title').value,
    slug: document.getElementById('blog-slug').value,
    category: document.getElementById('blog-category').value,
    author: document.getElementById('blog-author').value,
    author_avatar: document.getElementById('blog-author-avatar').value,
    author_bio: document.getElementById('blog-author-bio').value,
    image: document.getElementById('blog-image').value,
    excerpt: document.getElementById('blog-excerpt').value,
    content: quillEditor.root.innerHTML,
    tags: document.getElementById('blog-tags').value.split(',').map(t => t.trim()).filter(Boolean),
    published_date: document.getElementById('blog-published-date').value,
    read_time: document.getElementById('blog-read-time').value,
    views: +document.getElementById('blog-views').value,
    likes: +document.getElementById('blog-likes').value,
    featured: +document.getElementById('blog-featured').value,
    sort_order: +document.getElementById('blog-sort').value,
    is_active: +document.getElementById('blog-active').value,
    seo_title: document.getElementById('blog-seo-title').value,
    seo_description: document.getElementById('blog-seo-description').value
  };

  const r = id
    ? await api('/admin/blogs.php?id=' + id, 'PUT', body)
    : await api('/admin/blogs.php', 'POST', body);

  if (r.success) {
    toast(r.message);
    closeModal('blog-modal');
    loadBlogs();
  } else {
    toast(r.error, 'error');
  }
}

async function deleteBlog(id) {
  if (!confirm('Delete this blog?')) return;
  const r = await api('/admin/blogs.php?id=' + id, 'DELETE');
  if (r.success) {
    toast('Blog deleted');
    loadBlogs();
  } else {
    toast(r.error, 'error');
  }
}