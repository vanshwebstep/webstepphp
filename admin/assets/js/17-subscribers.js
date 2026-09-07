// Subscribers Logic
let allSubscribers = [];

async function loadSubscribers() {
  try {
    const res = await api('/admin/subscribers.php');
    allSubscribers = res.data || [];
    renderSubscribers();
    document.getElementById('badge-subs').textContent = allSubscribers.length;
  } catch(e) {
    console.error(e);
  }
}

function renderSubscribers() {
  const tbody = document.querySelector('#subs-subscribers-table tbody');
  if(!tbody) return;
  tbody.innerHTML = '';
  
  if(allSubscribers.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No subscribers yet.</td></tr>';
    return;
  }

  allSubscribers.forEach(sub => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>#${sub.id}</td>
      <td style="font-weight:600;">${sub.email}</td>
      <td><span class="badge" style="background:#e0e7ff;color:#4338ca;">${sub.status}</span></td>
      <td>${sub.source || 'unknown'}</td>
      <td>${new Date(sub.created_at).toLocaleDateString()}</td>
      <td>
        <button class="btn btn-sm" style="background:#dc2626;color:white;" onclick="deleteSubscriber(${sub.id})">Delete</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

async function deleteSubscriber(id) {
  if(!confirm('Are you sure you want to delete this subscriber?')) return;
  try {
    await api(`/admin/subscribers.php?id=${id}`, 'DELETE');
    toast('Subscriber deleted');
    loadSubscribers();
  } catch(e) {
    toast(e.message, 'error');
  }
}

// Automatically load subscribers when the dashboard initializes or when the page is shown
document.addEventListener('DOMContentLoaded', () => {
  // If boot loader is gone, we can load
  setTimeout(() => loadSubscribers(), 1000);
});
