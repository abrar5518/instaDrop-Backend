(() => {
  const sections = document.getElementById('sections');
  const addSection = document.getElementById('add-section');
  const sectionTemplate = document.getElementById('section-template');
  const itemTemplate = document.getElementById('item-template');
  if (!sections || !addSection || !sectionTemplate || !itemTemplate) return;
  let sequence = Date.now();
  const sectionKey = (section) => section.querySelector('[name^="sections["]')?.name.match(/^sections\[([^\]]+)\]/)?.[1] || String(sequence++);
  const refresh = () => sections.querySelectorAll('[data-section]').forEach((section, index) => { const label = section.querySelector('.section-number'); if (label) label.textContent = `Block ${index + 1}`; });
  addSection.addEventListener('click', () => { sections.insertAdjacentHTML('beforeend', sectionTemplate.innerHTML.replaceAll('__SECTION__', String(sequence++))); refresh(); });
  sections.addEventListener('click', (event) => {
    const target = event.target.closest('button'); if (!target) return;
    const section = target.closest('[data-section]');
    if (target.matches('[data-remove-section]')) section?.remove();
    if (target.matches('[data-add-item]') && section) section.querySelector('[data-items]').insertAdjacentHTML('beforeend', itemTemplate.innerHTML.replaceAll('__SECTION__', sectionKey(section)).replaceAll('__ITEM__', String(sequence++)));
    if (target.matches('[data-remove-item]')) target.closest('[data-item]')?.remove();
    if (target.matches('[data-move-up]') && section?.previousElementSibling) section.parentNode.insertBefore(section, section.previousElementSibling);
    if (target.matches('[data-move-down]') && section?.nextElementSibling) section.parentNode.insertBefore(section.nextElementSibling, section);
    refresh();
  });
  const title = document.getElementById('title'); const slug = document.getElementById('slug'); const preview = document.getElementById('slug-preview'); let touched = Boolean(slug?.value);
  slug?.addEventListener('input', () => { touched = true; if (preview) preview.textContent = slug.value; });
  title?.addEventListener('input', () => { if (!slug || touched) return; slug.value = title.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); if (preview) preview.textContent = slug.value; });
  refresh();
})();
