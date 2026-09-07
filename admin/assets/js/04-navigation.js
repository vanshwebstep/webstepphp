// ─── Navigation ───────────────────────────────────────────────────────────────
function showPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  event?.currentTarget?.classList.add('active');
  currentPage = name;
  loadPage(name);
}

function loadPage(name) {
  const loaders = {
    dashboard:            loadDashboard,
    tabs:                 loadTabs,
    packages:              loadPackages,
    portfolio:              loadPortfolio,
    'portfolio-categories': loadPortfolioCategories,
    blogs:                loadBlogs,
    'case-studies':       loadCaseStudies,
    'service-pages':      loadServicePages,
    'seo':                loadSeoSettings,
    'custom-options':     loadCustomOptions,
    'custom-submissions': () => loadSubs('custom', currentSubFilter),
    inquiries:            loadInquiries,
  };
  loaders[name]?.();
}