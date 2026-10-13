/** Accessible confirmations shared by all portals. */
(function () {
  'use strict';
  let active = false;
  window.AppDialog = {
    confirm(message, { title = 'Please confirm', confirmText = 'Continue', destructive = false } = {}) {
      if (active) return Promise.resolve(false);
      active = true;
      return new Promise(resolve => {
        const previousFocus = document.activeElement;
        const dialog = document.createElement('dialog');
        dialog.className = 'app-confirm-dialog';
        dialog.setAttribute('aria-labelledby', 'app-confirm-title');
        dialog.setAttribute('aria-describedby', 'app-confirm-message');
        dialog.innerHTML = '<form method="dialog"><button class="app-dialog-close" value="cancel" aria-label="Cancel">×</button><h2 id="app-confirm-title"></h2><p id="app-confirm-message"></p><div class="app-dialog-actions"><button value="cancel" autofocus>Cancel</button><button class="app-dialog-accept" value="confirm"></button></div></form>';
        dialog.querySelector('h2').textContent = title;
        dialog.querySelector('p').textContent = message;
        const ok = dialog.querySelector('.app-dialog-accept');
        ok.textContent = confirmText;
        if (destructive) ok.classList.add('destructive');
        dialog.addEventListener('close', () => {
          const accepted = dialog.returnValue === 'confirm';
          dialog.remove();
          active = false;
          if (previousFocus?.isConnected) previousFocus.focus();
          resolve(accepted);
        }, { once: true });
        document.body.appendChild(dialog);
        dialog.showModal();
      });
    }
  };
  // Confirm explicit form actions; automatic saves never generate a dialog.
  const approved = new WeakSet();
  document.addEventListener('click', async event => {
    const control = event.target.closest('button, a, [data-attachment]');
    if (!control || control.disabled || approved.has(control)) return;
    const label = control.textContent.trim();
    let message = '';
    if (control.matches('[data-attachment]')) {
      message = 'Open or download this attachment?';
    } else if (control.id === 'dm-done-btn') {
      message = 'Save this documentation?';
    } else if (control.matches('#fillBody button, #btn-save-draft') && /^Save(?:\s|$)/i.test(label)) {
      message = 'Save the current table changes?';
    } else if (control.matches('#fillBody .t-docs-btn')) {
      message = 'Open documentation and attachments for this table?';
    } else if (control.matches('a[download]') && control.closest('.dm-overlay, .modal-overlay, .modal, #mainContent')) {
      message = 'Download this attachment?';
    }
    if (!message) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    if (await AppDialog.confirm(message)) {
      if (!control.isConnected || control.disabled) return;
      approved.add(control);
      control.click();
      approved.delete(control);
    }
  }, true);
})();
