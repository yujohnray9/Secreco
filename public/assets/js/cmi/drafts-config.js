/**
 * drafts-config.js — CMI My Drafts
 * API endpoints, page URLs, and status display configuration.
 *
 * Exposed as: window.DraftsConfig
 */

(function () {
  'use strict';

  window.DraftsConfig = {

    API_STATUSES    : '/api/cmi/tables/statuses',
    API_LOAD        : '/api/cmi/tables/load',
    FILLUP_URL      : '/dashboard/cmi/fillup',
    SUBMISSIONS_URL : '/dashboard/cmi/submissions',

    // Per-status display: badge label, icon, CSS class, action button label + class
    STATUS_CFG : {
      'done'        : { label: 'Complete',    icon: '✅', cls: 'badge-green',  action: 'View',     btnCls: 'btn-sm' },
      'submitted'   : { label: 'Submitted',   icon: '📤', cls: 'badge-green',  action: 'View',     btnCls: 'btn-sm' },
      'accepted'    : { label: 'Accepted',    icon: '✅', cls: 'badge-green',  action: 'View',     btnCls: 'btn-sm' },
      'returned'    : { label: 'Returned',    icon: '↩️', cls: 'badge-purple', action: 'Revise',   btnCls: 'btn-sm btn-primary' },
      'draft'       : { label: 'Draft',       icon: '⏳', cls: 'badge-yellow', action: 'Continue', btnCls: 'btn-sm btn-primary' },
      'not-started' : { label: 'Not Started', icon: '⚪', cls: 'badge-gray',   action: 'Start',    btnCls: 'btn-sm btn-outline' },
      'error'       : { label: 'Correction',  icon: '🔴', cls: 'badge-red',    action: 'Fix',      btnCls: 'btn-sm btn-danger' },
    },

  };

})();
