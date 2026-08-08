// ═══ MANAGE FORMATS PAGE ═══

function switchYear(year, el){
  // toggle active tab
  document.querySelectorAll('.year-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');

  // toggle year content panels
  ['2024','2025','2026'].forEach(y => {
    const panel = document.getElementById('yearContent' + y);
    if (panel) panel.style.display = (y === year) ? 'block' : 'none';
  });
}
