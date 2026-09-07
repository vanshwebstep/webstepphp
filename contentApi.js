// lib/contentApi.js
// Replace your existing contentApi.js with this file
// Connects your Next.js frontend to the PHP backend
// https://webstepdev.com/demo/webstepphp

const BASE_URL =
  process.env.NEXT_PUBLIC_API_URL || "https://webstepdev.com/demo/webstepphp";

/**
 * Fetch packages and tabs for the Packages component
 * Drop-in replacement - returns { tabs, packages } in same shape
 */
export async function fetchContent(type, fallback) {
  if (type !== 'packages') return fallback;

  try {
    const res = await fetch(`${BASE_URL}/api/packages.php`, {
      cache: 'no-store', // always fresh
    });
    if (!res.ok) throw new Error('API error');
    const data = await res.json();
    return {
      tabs:     data.tabs     || fallback.tabs,
      packages: data.packages || fallback.packages,
    };
  } catch (err) {
    console.warn('[contentApi] fetchContent failed, using fallback:', err.message);
    return fallback;
  }
}

/**
 * Fetch custom builder options for CustomizePackage component
 * Returns { websiteType, pages, design, features } arrays
 */
export async function fetchCustomOptions() {
  try {
    const res = await fetch(`${BASE_URL}/api/custom-options.php`, {
      cache: 'no-store',
    });
    if (!res.ok) throw new Error('API error');
    return res.json();
  } catch (err) {
    console.warn('[contentApi] fetchCustomOptions failed:', err.message);
    return null; // caller should use FALLBACK
  }
}

/**
 * Submit a custom package request
 * Called from CustomizePackage on final form submit
 */
export async function submitLead(payload) {
  const isCustom = payload.source === 'custom-package';
  const endpoint = isCustom ? '/api/submit-custom.php' : '/api/submit-inquiry.php';

  const res = await fetch(`${BASE_URL}${endpoint}`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });

  const data = await res.json();
  if (!data.success) throw new Error(data.error || 'Submission failed');
  return data;
}

/**
 * Submit a "Choose Plan" inquiry from the Packages page
 */
export async function submitPlanInquiry(payload) {
  const res = await fetch(`${BASE_URL}/api/submit-inquiry.php`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });
  const data = await res.json();
  if (!data.success) throw new Error(data.error || 'Submission failed');
  return data;
}
