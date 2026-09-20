(() => {
  const sections = document.getElementById('sections');
  const addSection = document.getElementById('add-section');
  const sectionTemplate = document.getElementById('section-template');
  const itemTemplate = document.getElementById('item-template');
  if (!sections || !addSection || !sectionTemplate || !itemTemplate) return;

  let sequence = Date.now();
  const sectionKey = (section) => {
    const input = section.querySelector('[name^="sections["]');
    return input?.name.match(/^sections\[([^\]]+)\]/)?.[1] || String(sequence++);
  };
  const addItem = (section) => {
    const key = sectionKey(section);
    const html = itemTemplate.innerHTML.replaceAll('__SECTION__', key).replaceAll('__ITEM__', String(sequence++));
    section.querySelector('[data-items]').insertAdjacentHTML('beforeend', html);
  };
  const refreshLabels = () => sections.querySelectorAll('[data-section]').forEach((section, index) => {
    const label = section.querySelector('.section-number');
    if (label) label.textContent = `Section ${index + 1}`;
  });

  addSection.addEventListener('click', () => {
    const key = String(sequence++);
    sections.insertAdjacentHTML('beforeend', sectionTemplate.innerHTML.replaceAll('__SECTION__', key));
    refreshLabels();
  });
  sections.addEventListener('click', (event) => {
    const target = event.target.closest('button');
    if (!target) return;
    const section = target.closest('[data-section]');
    if (target.matches('[data-remove-section]')) section?.remove();
    if (target.matches('[data-add-item]') && section) addItem(section);
    if (target.matches('[data-remove-item]')) target.closest('[data-item]')?.remove();
    if (target.matches('[data-move-up]') && section?.previousElementSibling) section.parentNode.insertBefore(section, section.previousElementSibling);
    if (target.matches('[data-move-down]') && section?.nextElementSibling) section.parentNode.insertBefore(section.nextElementSibling, section);
    refreshLabels();
  });
  const title = document.getElementById('title');
  const slug = document.getElementById('slug');
  const preview = document.getElementById('slug-preview');
  let slugTouched = Boolean(slug?.value);
  slug?.addEventListener('input', () => { slugTouched = true; if (preview) preview.textContent = slug.value; });
  title?.addEventListener('input', () => {
    if (!slug || slug.readOnly || slugTouched) return;
    slug.value = title.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    if (preview) preview.textContent = slug.value;
  });
  refreshLabels();
})();
