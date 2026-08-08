/**
 * reports-export.js  —  SecReCo · PTA Portal
 * Word export preview + export trigger.
 * Updated: real logos from header fetched as base64, styled preview, confirm modal.
 */
'use strict';

// ── LOGO FETCHER ──────────────────────────────────────────────
/**
 * Fetches both header logos as base64 data URIs so they embed cleanly
 * in both the on-screen preview and the Word .doc file.
 * Returns a Promise<{ secreco: string, cvaarrd: string }>.
 */
async function fetchLogosAsBase64() {
  const toBase64 = async (src) => {
    try {
      const res  = await fetch(src);
      const blob = await res.blob();
      return await new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload  = () => resolve(reader.result); // data:image/...;base64,...
        reader.onerror = () => reject('');
        reader.readAsDataURL(blob);
      });
    } catch {
      return ''; // fallback: logo just won't show
    }
  };

  const [secreco, cvaarrd] = await Promise.all([
    toBase64('/assets/img/logo26.png'),
    toBase64('/assets/img/cvaarrd.png'),
  ]);

  return { secreco, cvaarrd };
}

/**
 * Builds <img> tags for both logos.
 * Uses base64 so they work offline and inside Word's embedded HTML.
 */
function logoImgs({ secreco, cvaarrd }, size = 52) {
  const img = (src, alt) => src
    ? `<img src="${src}" alt="${alt}" width="${size}" height="${size}" style="width:${size}px;height:${size}px;object-fit:contain;vertical-align:middle;display:block;"/>`
    : `<span style="display:inline-block;width:${size}px;height:${size}px;"></span>`;
  return { secreco: img(secreco, 'SecReCo'), cvaarrd: img(cvaarrd, 'CVAARRD') };
}

// ── EXPORT BUTTONS ────────────────────────────────────────────
function renderExportBtns() {
  const wrap = el('rptExportBtns');
  if (!wrap) return;
  wrap.innerHTML = `
    <button class="btn btn-sm btn-yellow" onclick="showWordPreview()">👁 Preview</button>
    <button class="btn btn-sm btn-primary" onclick="showExportConfirm()">⬇ Export</button>`;
}

// ── PREVIEW ───────────────────────────────────────────────────
async function showWordPreview() {
  const card = el('wordPreviewCard');
  el('wordPreviewContent').innerHTML = `<p style="text-align:center;color:#888;padding:24px">Loading preview…</p>`;
  if (card) card.style.display = 'block';

  const logos = await fetchLogosAsBase64();
  const src   = buildExportHTML({ forPreview: true, logos });
  el('wordPreviewContent').innerHTML = src;
  toast('📄 Preview loaded!');
}

function closeWordPreview() {
  const card = el('wordPreviewCard');
  if (card) card.style.display = 'none';
}

// ── EXPORT CONFIRM MODAL ──────────────────────────────────────
async function showExportConfirm() {
  document.getElementById('exportConfirmOverlay')?.remove();

  const key   = getTableKey();
  const year  = getYear();
  const def   = TABLE_DEFS[key];
  const title = def?.label ?? key;
  const logos = await fetchLogosAsBase64();
  const imgs  = logoImgs(logos, 40);

  const overlay = document.createElement('div');
  overlay.id = 'exportConfirmOverlay';
  overlay.style.cssText = `
    position:fixed;inset:0;background:rgba(0,0,0,.45);
    display:flex;align-items:center;justify-content:center;z-index:9999;`;

  overlay.innerHTML = `
    <style>
      @keyframes ecSlideUp { from{transform:translateY(16px);opacity:0} to{transform:translateY(0);opacity:1} }
      #ecBox {
        background:#fff;border-radius:14px;padding:32px 36px;
        max-width:420px;width:90%;box-shadow:0 8px 40px rgba(0,0,0,.22);
        animation:ecSlideUp .2s ease;font-family:Calibri,Arial,sans-serif;text-align:center;
      }
      #ecBox .ec-logos { display:flex;justify-content:center;align-items:center;gap:12px;margin-bottom:14px; }
      #ecBox .ec-sep   { width:1px;height:36px;background:#ddd; }
      #ecBox h3 { margin:0 0 6px;font-size:1.1rem;color:#1b4d2e; }
      #ecBox p  { margin:0 0 22px;font-size:.88rem;color:#555;line-height:1.55; }
      #ecBox p strong { color:#1b4d2e; }
      #ecBox .ec-actions { display:flex;gap:10px;justify-content:center;margin-top:4px; }
      #ecBox .ec-btn { padding:9px 22px;border-radius:8px;font-size:.88rem;font-weight:600;cursor:pointer;border:none;transition:opacity .15s;flex:1; }
      #ecBox .ec-btn:hover  { opacity:.82; }
      #ecBox .ec-cancel  { background:#f0f0f0;color:#555;flex:0 0 auto;padding:9px 18px; }
      #ecBox .ec-pdf   { background:#1b4d2e;color:#fff; }
      #ecBox .ec-docx  { background:#2e6da4;color:#fff; }
    </style>
    <div id="ecBox">
      <div class="ec-logos">
        ${imgs.secreco}
        <div class="ec-sep"></div>
        ${imgs.cvaarrd}
      </div>
      <h3>Export Report</h3>
      <p>
        <strong>${esc(title)} — CY ${esc(year)}</strong>
      </p>
      <div class="ec-actions">
        <button class="ec-btn ec-pdf"  onclick="confirmExportPDF()">PDF</button>
        <button class="ec-btn ec-docx" onclick="confirmExportDOCX()">DOCX</button>
        <button class="ec-btn ec-cancel" onclick="closeExportConfirm()">Cancel</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeExportConfirm(); });

  // stash logos for confirmExport() to reuse without re-fetching
  overlay._logos = logos;
}

function closeExportConfirm() {
  document.getElementById('exportConfirmOverlay')?.remove();
}

function confirmExport() {
  const logos = document.getElementById('exportConfirmOverlay')?._logos ?? {};
  closeExportConfirm();
  exportToWord(logos);
}

function confirmExportPDF() {
  const logos = document.getElementById('exportConfirmOverlay')?._logos ?? {};
  closeExportConfirm();
  exportToPDF(logos);
}

async function confirmExportDOCX() {
  closeExportConfirm();
  await exportToDOCX();
}

// ── BUILD HTML ────────────────────────────────────────────────
function buildExportHTML({ forPreview, logos = {} }) {
  const key   = getTableKey();
  const year  = getYear();
  const def   = TABLE_DEFS[key];
  const title = def?.label ?? key;

  const origin   = window.location.origin;
  const rawTable = el('tableContainer')?.innerHTML ?? '';
  // Fix absolute paths + strip any rogue serif/monospace font-family overrides
  const tableHtml = rawTable
    .replace(/src="\//g, `src="${origin}/`)
    .replace(/font-family\s*:[^;"']*/gi, 'font-family:Calibri,Arial,sans-serif')
    .replace(/color\s*:\s*#(?:1b4d2e|3a7d44|1b5e20|2e7d32|388e3c)[^;"']*/gi, 'color:#1a1a1a');

  const generatedAt = new Date().toLocaleString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });

  // Logos: 72px — large enough to read clearly in Word
  const imgs = logoImgs(logos, 90);

  const wrapStyle = forPreview
    ? `background:#fff;border:1px solid #e0e0e0;border-radius:10px;
       padding:40px 48px;max-width:780px;margin:0 auto;font-size:12px;
       box-shadow:0 4px 24px rgba(0,0,0,.10);font-family:Calibri,Arial,sans-serif;`
    : '';

  return `
    <div style="${wrapStyle}">

      <!-- ══ HEADER: CVAARRD logo + title block ══ -->
      <table style="width:100%;border:none;border-collapse:collapse;margin-bottom:0;">
        <tr>
          <td style="border:none;padding:0 20pt 14pt 0;vertical-align:middle;width:100px;">
            ${imgs.cvaarrd}
          </td>
          <td style="border:none;padding:0 24pt 14pt;vertical-align:middle;width:3pt;">
            <div style="width:3pt;height:64pt;background:#1b4d2e;"></div>
          </td>
          <td style="border:none;padding:0 0 14pt;vertical-align:middle;">
            <p style="margin:0 0 6px;font-size:11pt;color:#1b4d2e;font-weight:700;font-family:Calibri,Arial,sans-serif;letter-spacing:.1px;line-height:1.3;">
              Cagayan Valley Agriculture, Aquatic &amp; Natural Resources Research &amp; Development Consortium
            </p>
            <p style="margin:0;font-size:9.5pt;color:#888;font-family:Calibri,Arial,sans-serif;letter-spacing:.2px;">
              Consolidated Annual Accomplishment Report &mdash; CY ${esc(year)}
            </p>
          </td>
        </tr>
      </table>

      <!-- Double rule: green + gold -->
      <div style="height:3pt;background:#1b4d2e;margin-bottom:2pt;border-radius:1pt;"></div>
      <div style="height:1.5pt;background:#c8a84b;margin-bottom:20pt;border-radius:1pt;"></div>

      <!-- ══ TABLE TITLE ══ -->
      <p style="text-align:center;font-weight:bold;font-size:13pt;
                margin:0 0 14pt;color:#1a1a1a;letter-spacing:.3px;
                font-family:'Book Antiqua','Palatino Linotype',Palatino,Georgia,serif;">${esc(title)}</p>

      <!-- ══ TABLE ══ -->
      ${tableHtml}

      <!-- ══ NOTE ══ -->
      <p style="font-size:8.5pt;font-style:italic;color:#aaa;margin-top:12pt;margin-bottom:0;
               font-family:Calibri,Arial,sans-serif;">
        Note: The Regional Consortium may prepare other tables for ease in data presentation.
      </p>

      <!-- ══ FOOTER ══ -->
      <div style="margin-top:14pt;padding-top:7pt;border-top:1pt solid #c8a84b;
                  display:flex;justify-content:space-between;align-items:center;
                  font-size:8pt;color:#bbb;">
        <span>Generated ${esc(generatedAt)}</span>
        <span style="font-weight:700;color:#1b4d2e;letter-spacing:.4px;font-size:8.5pt;
               font-family:Calibri,Arial,sans-serif;">
          SecReCo &middot; CVAARRD
        </span>
      </div>

    </div>`;
}

// ── PDF EXPORT ───────────────────────────────────────────────
function exportToPDF(logos = {}) {
  const key   = getTableKey();
  const year  = getYear();
  const def   = TABLE_DEFS[key];
  const title = def?.label ?? key;
  const body  = buildExportHTML({ forPreview: false, logos });

  const safeName = `${key}_${title.replace(/[^\w]+/g, '_')}_CY${year}`;
  const printWin = window.open('', '_blank');
  printWin.document.write(`<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title> </title><!-- blank title hides filename from browser print header -->
  <style>
    @page {
      size: A4;
      margin: 2.2cm;
      /* Suppress browser-added filename, URL, date, page number */
      @top-left   { content: ''; }
      @top-center { content: ''; }
      @top-right  { content: ''; }
      @bottom-left   { content: ''; }
      @bottom-center { content: ''; }
      @bottom-right  { content: ''; }
    }
    * { box-sizing: border-box; }
    body {
      font-family: Calibri, Arial, sans-serif;
      font-size: 11pt;
      color: #1a1a1a;
      line-height: 1.5;
      margin: 0;
      padding: 0;
    }
    table { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 10pt; }
    td { border: 0.5pt solid #999; padding: 5pt 7pt; font-size: 9.5pt; color: #1a1a1a; }
    th { border: 0.5pt solid #999; padding: 5pt 7pt; font-size: 9.5pt; background: #d6e8d6; color: #1b4d2e; font-weight: 700; }
    img { max-width: 100%; }
    .not-submitted { color: #888; font-style: italic; }
    .badge        { padding: 1pt 6pt; border-radius: 8pt; font-size: 8.5pt; }
    .badge-green  { background: #e6f4ea; color: #1b5e20; }
    .badge-yellow { background: #fff8e1; color: #8d6e00; }
    .badge-gray   { background: #eee; color: #666; }
    .badge-blue   { background: #e3f2fd; color: #0d47a1; }
    @media print {
      body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
    /* Hide the page while not printing — only visible in print preview/dialog */
    @media screen {
      body { visibility: hidden; }
    }
  </style>
</head>
<body>${body}</body>
</html>`);
  printWin.document.close();
  printWin.onload = () => {
    setTimeout(() => {
      // Replace about:blank in the URL bar so it won't show in the browser's print footer
      try { printWin.history.replaceState({}, ' ', ' '); } catch {}
      printWin.focus();
      printWin.print();
      // Close the blank tab after print dialog is handled
      setTimeout(() => printWin.close(), 1000);
    }, 500);
  };
  toast('🖨️ PDF print dialog opened!');
}

// ── DOCX EXPORT ───────────────────────────────────────────────
async function exportToDOCX(logos = {}) {
  // Load docx.js from CDN if not already loaded
  if (!window.docx) {
    await new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = 'https://unpkg.com/docx@7.8.2/build/index.js';
      s.onload  = resolve;
      s.onerror = reject;
      document.head.appendChild(s);
    });
  }

  const { Document, Packer, Paragraph, Table, TableRow, TableCell,
          TextRun, ImageRun, AlignmentType, BorderStyle,
          WidthType, ShadingType, Header, TableLayoutType,
          HeightRule } = window.docx;

  const key   = getTableKey();
  const year  = getYear();
  const def   = TABLE_DEFS[key];
  const title = def?.label ?? key;

  // ── fetch logo as ArrayBuffer ──
  const fetchBuf = async (src) => {
    try { return await (await fetch(src)).arrayBuffer(); } catch { return null; }
  };
  const logoBuf = await fetchBuf('/assets/img/cvaarrd.png');

  // ── collect table data from DOM ──
  const domTable = el('tableContainer')?.querySelector('table.merged');
  const rows = domTable ? [...domTable.querySelectorAll('tbody tr')] : [];
  const headRows = domTable ? [...domTable.querySelectorAll('thead tr')] : [];

  // ── colours ──
  const GREEN  = '1b4d2e';
  const LGREEN = 'd6e8d6';
  const GOLD   = 'c8a84b';
  const BLACK  = '1a1a1a';
  const GRAY   = '999999';

  const cellBorder = (color = GRAY) => ({
    top:    { style: BorderStyle.SINGLE, size: 4, color },
    bottom: { style: BorderStyle.SINGLE, size: 4, color },
    left:   { style: BorderStyle.SINGLE, size: 4, color },
    right:  { style: BorderStyle.SINGLE, size: 4, color },
  });

  const noBorder = {
    top:    { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' },
    bottom: { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' },
    left:   { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' },
    right:  { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' },
  };

  // ── build header rows ──
  const docxHeadRows = headRows.map(tr => {
    const cells = [...tr.querySelectorAll('th')].map(th => new TableCell({
      children: [new Paragraph({
        children: [new TextRun({ text: th.innerText.trim(), bold: true, color: GREEN, size: 18, font: { name: 'Calibri' } })],
        alignment: AlignmentType.CENTER,
      })],
      shading: { type: ShadingType.SOLID, color: LGREEN },
      borders: cellBorder(),
      columnSpan: +th.getAttribute('colspan') || 1,
      rowSpan:    +th.getAttribute('rowspan') || 1,
    }));
    return new TableRow({ children: cells, tableHeader: true });
  });

  // ── build body rows ──
  const docxBodyRows = rows.map(tr => {
    const cells = [...tr.querySelectorAll('td')].map(td => new TableCell({
      children: [new Paragraph({
        children: [new TextRun({ text: td.innerText.trim(), size: 18, color: BLACK, font: { name: 'Calibri' } })],
        alignment: AlignmentType.CENTER,
      })],
      borders: cellBorder(),
      columnSpan: +td.getAttribute('colspan') || 1,
    }));
    return new TableRow({ children: cells });
  });

  // ── build Documentation section ──
  const docSections = [];
  const cmiBlocks = el('tableContainer')?.querySelectorAll('.doc-body > div') ?? [];

  if (cmiBlocks.length) {
    docSections.push(new Paragraph({
      children: [new TextRun({ text: 'Documentation', bold: true, size: 24, color: BLACK, font: { name: 'Calibri' } })],
      spacing: { before: 240, after: 120 },
      border: { left: { style: BorderStyle.SINGLE, size: 12, color: GREEN } },
      indent: { left: 120 },
    }));

    for (const block of cmiBlocks) {
      const instName = block.querySelector('p')?.innerText?.trim() ?? '';
      const caption  = block.querySelector('p:last-of-type')?.innerText?.trim() ?? '';
      const imgEls   = [...block.querySelectorAll('img')];

      docSections.push(new Paragraph({
        children: [new TextRun({ text: instName, bold: true, size: 19, color: BLACK, font: { name: 'Calibri' } })],
        spacing: { before: 160, after: 60 },
      }));

      // fetch and embed each image
      const imgRuns = [];
      for (const imgEl of imgEls) {
        try {
          const buf = await fetchBuf(imgEl.src);
          if (buf) {
            // detect image type from src
            const imgType = imgEl.src.toLowerCase().includes('.jpg') || imgEl.src.toLowerCase().includes('.jpeg') ? 'jpg' : 'png';
            imgRuns.push(new ImageRun({
              data: new Uint8Array(buf),
              transformation: { width: 64, height: 64 },
              type: imgType,
            }));
            imgRuns.push(new TextRun({ text: '  ' })); // small gap between images
          }
        } catch {}
      }

      if (imgRuns.length) {
        docSections.push(new Paragraph({ children: imgRuns, spacing: { after: 60 } }));
      }

      const cleanCaption = caption.replace(/^['"]+|['"]+$/g, '').trim();
      if (cleanCaption && cleanCaption !== instName) {
        docSections.push(new Paragraph({
          children: [new TextRun({ text: `"${cleanCaption}"`, italics: true, size: 17, color: '555555', font: { name: 'Calibri' } })],
          spacing: { after: 120 },
        }));
      }
    }
  }

  // ── assemble document ──
  const docChildren = [
    // Title
    new Paragraph({
      children: [new TextRun({ text: title, bold: true, size: 26, color: BLACK, font: { name: 'Calibri' } })],
      alignment: AlignmentType.CENTER,
      spacing: { after: 200 },
    }),
    // Main table
    ...(docxHeadRows.length || docxBodyRows.length ? [new Table({
      rows: [...docxHeadRows, ...docxBodyRows],
      width: { size: 100, type: WidthType.PERCENTAGE },
      layout: TableLayoutType.FIXED,
    })] : []),
    new Paragraph({ children: [], spacing: { after: 160 } }),
    // Documentation
    ...docSections,
    // Note
    new Paragraph({
      children: [new TextRun({
        text: 'Note: The Regional Consortium may prepare other tables for ease in data presentation.',
        italics: true, size: 17, color: 'aaaaaa', font: { name: 'Calibri' },
      })],
      spacing: { before: 240, after: 120 },
    }),
  ];

  // ── header with logo + org name ──
  // Header: logo cell + divider + text cell in a borderless table
  const headerLogoCell = new TableCell({
    children: logoBuf ? [new Paragraph({
      children: [new ImageRun({ data: new Uint8Array(logoBuf), transformation: { width: 56, height: 56 }, type: 'png' })],
      alignment: AlignmentType.CENTER,
    })] : [new Paragraph({ children: [] })],
    borders: noBorder,
    width: { size: 900, type: WidthType.DXA },
    verticalAlign: 'center',
    margins: { right: 200 },
  });

  const headerDividerCell = new TableCell({
    children: [new Paragraph({ children: [] })],
    borders: {
      top: { style: BorderStyle.NONE, size: 0 },
      bottom: { style: BorderStyle.NONE, size: 0 },
      left: { style: BorderStyle.SINGLE, size: 12, color: GREEN },
      right: { style: BorderStyle.NONE, size: 0 },
    },
    width: { size: 160, type: WidthType.DXA },
    margins: { left: 160, right: 160 },
  });

  const headerTextCell = new TableCell({
    children: [
      new Paragraph({
        children: [new TextRun({ text: 'Cagayan Valley Agriculture, Aquatic & Natural Resources Research & Development Consortium', bold: true, color: GREEN, font: { name: 'Calibri' }, size: 19 })],
        spacing: { after: 40 },
      }),
      new Paragraph({
        children: [new TextRun({ text: `Consolidated Annual Accomplishment Report — CY ${year}`, color: '888888', font: { name: 'Calibri' }, size: 17 })],
      }),
    ],
    borders: noBorder,
    verticalAlign: 'center',
  });

  const headerTable = new Table({
    rows: [new TableRow({ children: [headerLogoCell, headerDividerCell, headerTextCell] })],
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: noBorder,
  });

  const headerChildren = [headerTable, new Paragraph({
    children: [],
    border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: GREEN } },
    spacing: { after: 80 },
  })];

  const doc = new Document({
    sections: [{
      properties: {
        page: {
          size: { width: 11906, height: 16838 }, // A4 in twips
          margin: { top: 1247, right: 1247, bottom: 1247, left: 1247 }, // ~2.2cm
        },
      },
      headers: { default: new Header({ children: headerChildren }) },
      children: docChildren,
    }],
  });

  const blob = await Packer.toBlob(doc);
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = `${key}_${title.replace(/[^\w]+/g, '_')}_CY${year}.docx`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
  toast('✅ DOCX downloaded successfully!');
}

// ── LEGACY .doc export (kept as fallback) ────────────────────
function exportToWord(logos = {}) {
  const key   = getTableKey();
  const year  = getYear();
  const def   = TABLE_DEFS[key];
  const title = def?.label ?? key;
  const body  = buildExportHTML({ forPreview: false, logos });

  const doc = `<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<title>${esc(title)}</title>
<!--[if gte mso 9]>
<xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom></w:WordDocument></xml>
<![endif]-->
<style>
  @page { size: 21cm 29.7cm; margin: 2.2cm; }
  *, body, p, span, div, li, td, th, h1, h2, h3, h4, h5, h6 {
    font-family: Calibri, Arial, sans-serif !important;
  }
  body { font-size: 11pt; color: #1a1a1a; line-height: 1.5; }
  table { width:100%; border-collapse:collapse; font-size:9.5pt; margin-bottom:10pt; }
  td { border:0.5pt solid #999; padding:5pt 7pt; font-size:9.5pt; color:#1a1a1a; }
  th { border:0.5pt solid #999; padding:5pt 7pt; font-size:9.5pt; background:#d6e8d6; color:#1b4d2e; font-weight:700; }
  .not-submitted { color:#888; font-style:italic; }
  .badge { padding:1pt 6pt; border-radius:8pt; font-size:8.5pt; }
  .badge-green  { background:#e6f4ea; color:#1b5e20; }
  .badge-yellow { background:#fff8e1; color:#8d6e00; }
  .badge-gray   { background:#eee; color:#666; }
  .badge-blue   { background:#e3f2fd; color:#0d47a1; }
</style>
</head>
<body>${body}</body>
</html>`;

  const blob = new Blob(['\ufeff', doc], { type: 'application/msword' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = `${key}_${title.replace(/[^\w]+/g, '_')}_CY${year}.doc`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
  toast('✅ Word file downloaded!');
}
