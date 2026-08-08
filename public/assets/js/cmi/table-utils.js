/**
 * table-utils.js — CMI Shared Table Utilities
 * Loaded before all table scripts in fillup.php.
 */

window.CMIUtils = {

  /**
   * Filters out rows where all meaningful fields are empty.
   * @param {Array} rows - array of row objects
   * @param {Array} fields - field names to check for content
   * @returns {Array} filtered rows
   */
  filterEmptyRows(rows, fields) {
    return rows.filter(row =>
      fields.some(field => String(row[field] || '').trim() !== '')
    );
  },

  /**
   * Checks if meta object has any meaningful content.
   * @param {Object} meta - meta object (excluding 'images')
   * @returns {boolean}
   */
  metaHasContent(meta) {
    return Object.entries(meta)
      .filter(([key]) => key !== 'images')
      .some(([, val]) => String(val || '').trim() !== '');
  },

  /**
   * Checks whether a table, as a whole, has any meaningful content —
   * either in its rows or in its meta fields (e.g. optional notes/remarks).
   * Use this before allowing a Save Draft / Mark as Complete action.
   * @param {Array} rows - array of row objects
   * @param {Array} fields - field names to check for content in each row
   * @param {Object} [meta] - optional meta object (excluding 'images')
   * @returns {boolean} true if there's at least one non-empty field anywhere
   */
  hasContent(rows, fields, meta) {
    const rowsHaveContent = this.filterEmptyRows(rows, fields).length > 0;
    const metaHasContent  = meta ? this.metaHasContent(meta) : false;
    return rowsHaveContent || metaHasContent;
  },

  /**
   * Guard to call right before saving (draft or complete). Shows a toast
   * and returns false if the table is completely empty, so the caller
   * can bail out before hitting the API.
   *
   * Usage:
   *   if (!CMIUtils.guardEmptySave(rows, fields, meta)) return;
   *   // ...proceed to fetch(API_SAVE, ...)
   *
   * @param {Array} rows
   * @param {Array} fields
   * @param {Object} [meta]
   * @param {string} [message] - optional custom toast message
   * @returns {boolean} true if OK to proceed, false if save should be blocked
   */
  guardEmptySave(rows, fields, meta, message) {
    if (this.hasContent(rows, fields, meta)) return true;

    const msg = message || '⚠️ Wala kang nilagay. Hindi maisasave kung walang data.';
    if (typeof toast === 'function') {
      toast(msg);
    } else {
      alert(msg);
    }
    return false;
  },

};
