// ════════════════════════════════════════════
// UPLOAD_PHOTO — profile photo upload / crop / removal
// Depends on: core.js (openModal, closeModal, toast)
// ════════════════════════════════════════════

const DEFAULT_AVATAR = '/assets/img/default-avatar.svg';
const PHOTO_BASE     = '/';  // prepended to relative paths from server

// ── CUSTOM CONFIRM MODAL ──
function showConfirm(title, message, onConfirm) {
  document.getElementById('confirmTitle').textContent = title;
  document.getElementById('confirmMsg').textContent = message;
  const okBtn = document.getElementById('confirmOkBtn');
  const newBtn = okBtn.cloneNode(true);
  okBtn.parentNode.replaceChild(newBtn, okBtn);
  newBtn.addEventListener('click', () => {
    closeModal('modalConfirm');
    onConfirm();
  });
  openModal('modalConfirm');
}

// ── CROP STATE ──
let cropImage     = null;
let cropZoomVal   = 1;
let cropOffsetX   = 0;
let cropOffsetY   = 0;
let cropDragging  = false;
let cropDragStart = { x: 0, y: 0 };

// ── FILE SELECTED → open crop modal ──
function onPhotoSelected(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();
  reader.onload = function (e) {
    cropImage = new Image();
    cropImage.onload = function () {
      cropZoomVal = 1;
      cropOffsetX = 0;
      cropOffsetY = 0;
      const zoomEl = document.getElementById('cropZoom');
      if (zoomEl) zoomEl.value = 1;
      drawCrop();
      closeModal('modalProfile');
      openModal('modalCrop');
    };
    cropImage.src = e.target.result;
  };
  reader.readAsDataURL(file);
  input.value = '';
}

// ── DRAW CROP CANVAS ──
function drawCrop() {
  const canvas = document.getElementById('cropCanvas');
  if (!canvas || !cropImage) return;
  const ctx  = canvas.getContext('2d');
  const size = canvas.width;
  ctx.clearRect(0, 0, size, size);

  const imgRatio = cropImage.width / cropImage.height;
  let baseW, baseH;
  if (imgRatio > 1) { baseH = size; baseW = size * imgRatio; }
  else              { baseW = size; baseH = size / imgRatio; }

  const w = baseW * cropZoomVal;
  const h = baseH * cropZoomVal;
  const x = (size - w) / 2 + cropOffsetX;
  const y = (size - h) / 2 + cropOffsetY;
  ctx.drawImage(cropImage, x, y, w, h);
}

// ── CONFIRM CROP → upload blob ──
async function confirmCrop() {
  const canvas = document.getElementById('cropCanvas');
  if (!canvas) return;
  canvas.toBlob(async function (blob) {
    if (!blob) { toast('❌ Failed to process image'); return; }
    closeModal('modalCrop');
    await uploadCroppedPhoto(blob);
    openModal('modalProfile');
  }, 'image/jpeg', 0.92);
}

async function uploadCroppedPhoto(blob) {
  const formData = new FormData();
  formData.append('photo', blob, 'profile.jpg');
  try {
    const res  = await fetch('/api/pta/profile/upload-photo', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      const fullSrc = '/' + data.photo;
      setAvatarSrc(fullSrc);
      showToast('Profile photo updated!');
    } else {
      showToast('Failed: ' + (data.message || 'Upload failed'));
    }
  } catch (err) {
    showToast('Upload failed. Please try again.');
  }
}

// ── REMOVE PHOTO ──
function confirmRemovePhoto(e) {
  e.preventDefault();
  showConfirm('🗑 Remove Photo', 'Remove your profile photo? This will reset it to the default avatar.', removePhoto);
}

async function removePhoto() {
  try {
    const res  = await fetch('/api/pta/profile/remove-photo', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
      setAvatarSrc(DEFAULT_AVATAR);
      showToast('Profile photo removed');
    } else {
      showToast('Failed: ' + (data.message || 'Failed to remove photo'));
    }
  } catch {
    showToast('Failed to remove photo');
  }
}

// ── SET AVATAR EVERYWHERE — no innerHTML swap, no reload needed ──
function setAvatarSrc(src) {
    const isDefault = !src || src === DEFAULT_AVATAR;
    const finalSrc = isDefault ? DEFAULT_AVATAR : src;

    // 1. Modal preview circle
    const previewImg = document.getElementById("profileAvatarImg");
    if (previewImg) {
        previewImg.src = finalSrc;
    }

    // 2. Header topbar avatar
    document.querySelectorAll('.hdr-user-avatar').forEach(img => {
        img.src = finalSrc;
    });

    // 3. Settings page avatar preview
    const settingsImg = document.getElementById("avatarPreview");
    if (settingsImg) {
        settingsImg.src = finalSrc;
        settingsImg.style.display = 'block';
    }

    // 4. Toggle "Remove photo" link
    const removeLink = document.getElementById("removePhotoLink");
    if (removeLink) {
        removeLink.classList.toggle("hidden", isDefault);
    }
}

// ── DRAG & ZOOM ON CROP CANVAS ──
document.addEventListener('DOMContentLoaded', function () {
  const zoom = document.getElementById('cropZoom');
  const wrap = document.getElementById('cropCanvasWrap');
  if (!zoom || !wrap) return;

  zoom.addEventListener('input', function () {
    cropZoomVal = parseFloat(this.value);
    drawCrop();
  });

  wrap.addEventListener('mousedown', function (e) {
    cropDragging  = true;
    cropDragStart = { x: e.clientX - cropOffsetX, y: e.clientY - cropOffsetY };
    wrap.style.cursor = 'grabbing';
  });
  window.addEventListener('mousemove', function (e) {
    if (!cropDragging) return;
    cropOffsetX = e.clientX - cropDragStart.x;
    cropOffsetY = e.clientY - cropDragStart.y;
    drawCrop();
  });
  window.addEventListener('mouseup', function () {
    cropDragging = false;
    if (wrap) wrap.style.cursor = 'grab';
  });

  wrap.addEventListener('touchstart', function (e) {
    const t = e.touches[0];
    cropDragging  = true;
    cropDragStart = { x: t.clientX - cropOffsetX, y: t.clientY - cropOffsetY };
  }, { passive: true });
  window.addEventListener('touchmove', function (e) {
    if (!cropDragging) return;
    const t = e.touches[0];
    cropOffsetX = t.clientX - cropDragStart.x;
    cropOffsetY = t.clientY - cropDragStart.y;
    drawCrop();
  }, { passive: true });
  window.addEventListener('touchend', function () {
    cropDragging = false;
  });
});
