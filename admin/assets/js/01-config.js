// ─────────────────────────────────────────────────────────────────────────────
const BASE = 'http://localhost/webstepphp'; // CHANGE TO YOUR LOCAL/LIVE SERVER PATH

let currentPage       = 'dashboard';
let allOpts           = [];
let currentOptCat     = 'websiteType';
let allSubs           = [];
let currentSubFilter  = '';
let currentSubId      = null;
let currentSubType    = 'custom';
let allPortfolio      = [];
let allCases          = [];
let allBlogs           = [];
let quillEditor        = null;