(() => {
  let sequence = Date.now();
  const setup = (containerId, addId, templateId, itemSelector, numberSelector) => {
    const container = document.getElementById(containerId); const add = document.getElementById(addId); const template = document.getElementById(templateId); if (!container || !add || !template) return;
    const refresh = () => container.querySelectorAll(itemSelector).forEach((item, index) => { const label = item.querySelector(numberSelector); if (label) label.textContent = `${containerId === 'regions' ? 'Region' : 'Section'} ${index + 1}`; });
    add.addEventListener('click', () => { container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(sequence++))); refresh(); });
    container.addEventListener('click', (event) => { const button = event.target.closest('button'); if (!button) return; const item = button.closest(itemSelector); if (button.matches('[data-remove]')) item?.remove(); if (button.matches('[data-up]') && item?.previousElementSibling) item.parentNode.insertBefore(item, item.previousElementSibling); if (button.matches('[data-down]') && item?.nextElementSibling) item.parentNode.insertBefore(item.nextElementSibling, item); refresh(); });
    refresh();
  };
  setup('regions', 'add-region', 'region-template', '[data-region]', '.region-number');
  setup('coverage-sections', 'add-coverage-section', 'coverage-section-template', '[data-coverage-section]', '.coverage-number');
})();
