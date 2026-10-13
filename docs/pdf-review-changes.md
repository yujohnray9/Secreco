# PDF review changes

Implemented in the local project from Secreco_____FLORIE MAE.pdf.

- Approved CVAARRD logo applied to welcome, login, portal headers, report preview and exports. PhilRice welcome logo resized for reliable loading.
- Header X keeps the sign-out confirmation, logs out, and returns to the welcome page.
- Added styled confirmations for table/documentation saves, spreadsheet download/import, and attachment upload/open/delete. Existing notification deletion confirmation retained.
- Spreadsheet templates provide ten blank rows per category (or ten rows for ungrouped tables), including separate Type and Level columns for Table 6.
- Category Add Row buttons appear beside their sections, including after import.
- Empty format-title metadata no longer replaces built-in layouts, preserving Table 1's grouped header. Unchanged format definitions survive editing; stale year requests cannot change another year's lock.
- Table 2b uses category headings; Table 4 includes Duration; Table 11 hides Implementing Agency while retaining legacy saved values; Table 14 accepts participant descriptions without inventing numeric totals; Table 16 groups awards by level.
- Descriptive fields support multiple lines, including Word export line breaks.
- PTA submission editing uses shared field definitions, preserves row identity after deletion, and keeps checkbox/status values consistent.
- Additional CMI representatives can register and be approved for the same institution. Email uniqueness remains enforced.
- Submission heading follows the selected year. Report menu renamed Consolidated Reports; institution dropdown uses the complete master list.
- Added navigation padding to prevent the scroll-to-top control from covering the final table link.
- Changed asset URLs are versioned to refresh browser caches.

## Validation

- All 24 built-in forms rendered in a headless browser; template row counts and section buttons checked.
- All 24 forms imported and saved two sample multiline spreadsheet rows using mocked requests (no live database writes).
- PTA editor regression checks passed for Tables 2b, 4, 10, 11, 14 and 16, including row deletion/category preservation and retired data.
- Five PHP tests passed (12 assertions), including year-specific locking and multiple representative approval/reactivation, using an in-memory database.
- JavaScript syntax, changed PHP controller syntax and git whitespace checks passed.
- Production asset build was attempted but unavailable because Vite is not installed in this checkout. The changed portal scripts are served directly from public assets.

## Login background completed

Applied public/assets/img/forbg.jpg as the full-page login background with a 58% dark green overlay for a darker background. Branding uses white text; the login and registration cards remain solid white.
