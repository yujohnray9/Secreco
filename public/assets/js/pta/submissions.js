// ═══ SUBMISSIONS PAGE ═══

function filterSubs(){
  const search = (document.getElementById('subSearch')?.value || '').toLowerCase().trim();
  const section = document.getElementById('subSectionFilter')?.value || '';
  const status = document.getElementById('subStatusFilter')?.value || '';

  const rows = document.querySelectorAll('#subTable tbody tr');
  let visibleCount = 0;

  rows.forEach(row => {
    const name = row.dataset.name || '';
    const rSection = row.dataset.section || '';
    const rStatus = row.dataset.status || '';

    const matchesSearch = !search || name.includes(search);
    const matchesSection = !section || rSection === section;
    const matchesStatus = !status || rStatus === status;

    const visible = matchesSearch && matchesSection && matchesStatus;
    row.style.display = visible ? '' : 'none';
    if (visible) visibleCount++;
  });

  const empty = document.getElementById('subEmpty');
  if (empty) empty.style.display = visibleCount === 0 ? 'block' : 'none';
}

(function () {
  'use strict';

  /* ── State ── */
  var _attachments = [];   // { dataUrl, name }
  var _tableNo     = '';
  var _tableTitle  = '';

  /* Default columns per table type — extend as needed */
  var TABLE_COLS = {
    default: ['Date', 'Agency', 'New', 'Ongoing', 'Completed', 'Terminated'],
  };

  function colsFor(tableNo) {
    return TABLE_COLS[tableNo] || TABLE_COLS['default'];
  }

  /* ══════════════════════════════════════
     VIEW MODAL
  ══════════════════════════════════════ */

  /* Badge HTML helper (mirrors PHP $stBadge) */
  function statusBadge(s) {
    if (s === 'Submitted')      return '<span class="badge badge-green">&#x2705; Submitted</span>';
    if (s === 'For Correction') return '<span class="badge badge-red">&#x1F534; For Correction</span>';
    if (s === 'Re-submitted')   return '<span class="badge badge-yellow">&#x23F3; Re-submitted</span>';
    if (s === 'Consolidated')   return '<span class="badge" style="background:#e8f5e9;color:#2e7d32">Consolidated</span>';
    return '<span>' + s + '</span>';
  }

  window.openViewModal = async function (subId, cmiUserId, inst, status) {
    document.getElementById('viewModalInst').textContent   = inst;
    document.getElementById('viewModalStatus').textContent = 'Status: ' + status;
    document.getElementById('viewModalBody').innerHTML     = '<div style="text-align:center;padding:30px;color:var(--text-muted)">Loading data...</div>';
    document.getElementById('modalViewTable').style.display = 'flex';
    document.getElementById('modalViewTable').classList.add('open');
  
    try {
      const year = new Date().getFullYear();
      const res = await fetch(`/api/pta/submissions/get_pta_submissions.php?year=${year}`);
      const json = await res.json();
      if (!json.ok) throw new Error();
  
      const userTables = json.rows.filter(r => r.cmi_user_id === cmiUserId);
      if (!userTables.length) {
        document.getElementById('viewModalBody').innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted)">No completed tables found for this submission.</div>';
        return;
      }
  
      let html = '<div style="display:flex;flex-direction:column;gap:12px">';
      userTables.forEach(t => {
        let rowsHtml = '';
        if (t.rows && t.rows.length) {
          const keys = Object.keys(t.rows[0] || {});
          rowsHtml = `<table class="dt" style="margin-top:8px">
            <thead><tr>${keys.map(k => `<th>${k}</th>`).join('')}</tr></thead>
            <tbody>
              ${t.rows.map(r => `<tr>${keys.map(k => `<td>${r[k]||''}</td>`).join('')}</tr>`).join('')}
            </tbody>
          </table>`;
        }
        
        let docsHtml = '';
        if (t.docs && t.docs.length) {
          docsHtml = '<div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">' + 
            t.docs.map(d => `<a href="/${d.file_path}" target="_blank" style="display:block;width:60px;height:60px;border-radius:4px;border:1px solid #ddd;overflow:hidden"><img src="/${d.file_path}" style="width:100%;height:100%;object-fit:cover" title="${d.caption||''}"></a>`).join('') +
            '</div>';
        }
  
        html += `<div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px">
          <div style="font-weight:600;color:#111">${t.table_no} <span style="font-size:11px;color:#6b7280;font-weight:400;margin-left:8px">Updated: ${t.updated_at}</span></div>
          ${rowsHtml}
          ${docsHtml}
        </div>`;
      });
      html += '</div>';
      document.getElementById('viewModalBody').innerHTML = html;
  
    } catch (e) {
      document.getElementById('viewModalBody').innerHTML = '<div style="text-align:center;padding:30px;color:#e53935">Failed to load submission data.</div>';
    }
  };

  window.closeViewModal = function () {
    var overlay = document.getElementById('modalViewTable');
    overlay.classList.remove('open');
    setTimeout(function () { overlay.style.display = 'none'; }, 200);
  };

  /* Backdrop click closes View modal */
  document.getElementById('modalViewTable').addEventListener('click', function (e) {
    if (e.target === this) closeViewModal();
  });

  /* ══════════════════════════════════════
     EDIT MODAL
  ══════════════════════════════════════ */
  /* ── Open / close ── */
  window.openEditModal = function (tableNo, inst) {
    _tableNo    = tableNo;
    _tableTitle = inst || '';
    _attachments = [];

    document.getElementById('editModalTableNo').textContent    = tableNo;
    document.getElementById('editModalTableTitle').textContent = inst || '';
    document.getElementById('editCaption').value               = '';

    renderAttachments();
    renderDataTable(tableNo);

    var overlay = document.getElementById('modalEditTable');
    overlay.style.display = 'flex';
    requestAnimationFrame(function () {
      overlay.classList.add('modal-visible');
    });
  };

  window.closeEditModal = function () {
    var overlay = document.getElementById('modalEditTable');
    overlay.classList.remove('modal-visible');
    setTimeout(function () { overlay.style.display = 'none'; }, 200);
  };

  /* ── Attachments ── */
  window.ptaHandleAttach = function (input) {
    var files = Array.from(input.files);
    files.forEach(function (file) {
      var reader = new FileReader();
      reader.onload = function (e) {
        _attachments.push({ dataUrl: e.target.result, name: file.name });
        renderAttachments();
      };
      reader.readAsDataURL(file);
    });
    input.value = '';
  };

  function renderAttachments() {
    var row = document.getElementById('editAttachRow');
    row.innerHTML = '';
    _attachments.forEach(function (att, idx) {
      var thumb = document.createElement('div');
      thumb.className = 'attach-thumb';
      thumb.innerHTML =
        '<img src="' + att.dataUrl + '" alt="' + att.name + '" onclick="ptaOpenLightbox(' + idx + ')"/>' +
        '<button class="attach-remove" type="button" onclick="ptaRemoveAttach(' + idx + ')" title="Remove">×</button>';
      row.appendChild(thumb);
    });
  }

  window.ptaRemoveAttach = function (idx) {
    _attachments.splice(idx, 1);
    renderAttachments();
  };

  /* ── Lightbox ── */
  window.ptaOpenLightbox = function (idx) {
    var att = _attachments[idx];
    if (!att) return;
    document.getElementById('lightboxImg').src           = att.dataUrl;
    document.getElementById('lightboxCaption').textContent = att.name;
    var lb = document.getElementById('modalLightbox');
    lb.style.display = 'flex';
    requestAnimationFrame(function () { lb.classList.add('modal-visible'); });
  };

  window.closeLightbox = function () {
    var lb = document.getElementById('modalLightbox');
    lb.classList.remove('modal-visible');
    setTimeout(function () { lb.style.display = 'none'; }, 200);
  };

  /* ── Data table ── */
  function renderDataTable(tableNo) {
    var cols = colsFor(tableNo);
    var head = document.getElementById('editDataHead');
    var body = document.getElementById('editDataBody');

    /* Header */
    head.innerHTML = cols.map(function (c) {
      return '<th>' + c + '</th>';
    }).join('') + '<th></th>';

    /* One starter row */
    body.innerHTML = '';
    ptaAddRow();
  }

  window.ptaAddRow = function () {
    var cols   = colsFor(_tableNo);
    var body   = document.getElementById('editDataBody');
    var tr     = document.createElement('tr');
    var cells  = cols.map(function (col, i) {
      var type = (i >= 2) ? 'number' : 'text';
      return '<td><input type="' + type + '" placeholder="' + col + '"/></td>';
    }).join('');
    tr.innerHTML = cells +
      '<td><button class="row-del-btn" type="button" onclick="ptaRemoveRow(this)" title="Remove row">×</button></td>';
    body.appendChild(tr);
  };

  window.ptaRemoveRow = function (btn) {
    var row = btn.closest('tr');
    var body = document.getElementById('editDataBody');
    if (body.rows.length > 1) {
      row.remove();
    } else {
      /* Clear instead of deleting last row */
      row.querySelectorAll('input').forEach(function (inp) { inp.value = ''; });
    }
  };

  /* ── Save ── */
  window.ptaSaveEdit = function () {
    var caption = document.getElementById('editCaption').value.trim();
    var rows    = [];
    var cols    = colsFor(_tableNo);
    document.querySelectorAll('#editDataBody tr').forEach(function (tr) {
      var inputs = tr.querySelectorAll('input');
      var row = {};
      inputs.forEach(function (inp, i) {
        if (i < cols.length) row[cols[i]] = inp.value;
      });
      rows.push(row);
    });

    console.log('Saving', _tableNo, {
      caption: caption,
      attachments: _attachments.map(function (a) { return a.name; }),
      rows: rows
    });

    if (typeof toast === 'function') toast('✅ ' + _tableNo + ' updated successfully.');
    closeEditModal();
  };

  /* ── Close on backdrop click ── */
  document.getElementById('modalEditTable').addEventListener('click', function (e) {
    if (e.target === this) closeEditModal();
  });
  document.getElementById('modalLightbox').addEventListener('click', function (e) {
    if (e.target === this) closeLightbox();
  });

}());

document.addEventListener('DOMContentLoaded', filterSubs);

