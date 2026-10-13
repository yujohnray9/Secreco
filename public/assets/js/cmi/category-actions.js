/** Keep each category's add control alongside its rows, including after import/reload. */
(function () {
  'use strict';
  function start() {
    const body = document.getElementById('fillBody');
    if (!body) return;
    const observer = new MutationObserver(refresh);
    function refresh() {
      observer.disconnect();
      try {
        const controls = [...body.querySelectorAll('button[onclick], .dyn-add-cat-btn')]
          .filter(button => !button.closest('tbody') && (/\.addRow\('/.test(button.getAttribute('onclick') || '') || button.matches('.dyn-add-cat-btn')));
        controls.forEach((source, index) => {
          const match = (source.getAttribute('onclick') || '').match(/\.addRow\('((?:\\.|[^'])*)'/);
          const category = source.dataset.cat || (match ? match[1].replace(/\\'/g, "'") : '');
          const rows = [...body.querySelectorAll('tbody > tr[data-cat], tbody > tr[data-scope]')]
            .filter(row => (row.dataset.scope || row.dataset.cat) === category);
          const last = rows[rows.length - 1];
          if (!last) return;
          const tbody = last.parentElement;
          const id = 'category-action-' + index;
          let footer = tbody.querySelector('#' + id);
          if (!footer) {
            footer = document.createElement('tr');
            footer.id = id;
            footer.className = 'category-actions';
            const cell = document.createElement('td');
            cell.colSpan = Math.max(...[...tbody.closest('table').rows].map(row => [...row.cells].reduce((n, c) => n + c.colSpan, 0)));
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm';
            button.addEventListener('click', () => source.click());
            cell.appendChild(button);
            footer.appendChild(cell);
          }
          if (last.nextElementSibling !== footer) last.after(footer);
          const button = footer.querySelector('button');
          if (button.textContent !== source.textContent) button.textContent = source.textContent;
          button.disabled = source.disabled;
          source.hidden = true;
          source.style.display = 'none';
        });
      } finally {
        observer.observe(body, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });
      }
    }
    refresh();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
