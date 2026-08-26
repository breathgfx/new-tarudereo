// Content hydrator — reads content.json and fills every element marked
// with data-content="path.to.value". Falls back silently to whatever
// placeholder text already sits in the HTML if content.json can't be
// loaded (e.g. opened via file:// with no local server, or a bad edit).
//
// Path rules:
//   "site.foo.bar"   -> content.site.foo.bar        (shared across all pages)
//   "foo.bar"         -> content.pages[pageKey].foo.bar (this page only)
//
// The page key comes from data-content-page="..." on <body>.
//
// By default the resolved value replaces the element's textContent.
// Add data-content-attr="href" (or any attribute name) on an element to
// set that attribute instead — used for things like the report PDF link.
(() => {
  function resolvePath(obj, path) {
    return path.split('.').reduce((acc, key) => (acc == null ? undefined : acc[key]), obj);
  }

  function hydrate(content) {
    const pageKey = document.body.getAttribute('data-content-page');
    const pageData = pageKey ? content.pages && content.pages[pageKey] : undefined;

    document.querySelectorAll('[data-content]').forEach((el) => {
      const path = el.getAttribute('data-content');
      let value;
      if (path.startsWith('site.')) {
        value = resolvePath(content.site, path.slice(5));
      } else if (pageData) {
        value = resolvePath(pageData, path);
      }
      if (value === undefined || value === null) return; // leave existing placeholder text as-is

      const attr = el.getAttribute('data-content-attr');
      if (attr) {
        el.setAttribute(attr, value);
      } else {
        el.textContent = value;
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    fetch('content.json')
      .then((r) => (r.ok ? r.json() : Promise.reject(new Error('content.json ' + r.status))))
      .then(hydrate)
      .catch((err) => {
        // No content.json, or it failed to load/parse — pages already show
        // sensible fallback text, so this is a soft failure, not fatal.
        console.warn('content.js: could not load content.json, showing fallback text.', err);
      });
  });
})();
