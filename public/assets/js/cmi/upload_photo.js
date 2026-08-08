/* ═══════════════════════════════════════════
   UPLOAD PHOTO — profile avatar selection,
   cropping (canvas-based) and upload/removal.
   Used by c_header.php (modalProfile, modalCrop)
   and c_sidebar.php (sidebar avatar).
════════════════════════════════════════════ */

let _cropImage   = null;
let _cropZoom    = 1;
let _cropOffsetX = 0;
let _cropOffsetY = 0;
let _cropDragging = false;
let _cropDragStart = {x:0, y:0};

const CROP_SIZE = 260; // matches #cropCanvas width/height

// ── STEP 1: file selected from <input id="profileAvatarInput"> ──
function onPhotoSelected(input){
  const file = input.files && input.files[0];
  if(!file) return;

  if(!file.type.startsWith('image/')){
    toast('⚠️ Please select an image file');
    input.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function(e){
    _cropImage = new Image();
    _cropImage.onload = function(){
      _cropZoom = 1;
      _cropOffsetX = 0;
      _cropOffsetY = 0;
      document.getElementById('cropZoom').value = 1;
      drawCrop();
      closeModal('modalProfile');
      openModal('modalCrop');
    };
    _cropImage.src = e.target.result;
  };
  reader.readAsDataURL(file);

  // reset input so selecting the same file again still fires onchange
  input.value = '';
}

// ── CROP CANVAS RENDERING ──
function drawCrop(){
  const canvas = document.getElementById('cropCanvas');
  if(!canvas || !_cropImage) return;
  const ctx = canvas.getContext('2d');

  ctx.clearRect(0, 0, CROP_SIZE, CROP_SIZE);

  const baseScale = Math.max(CROP_SIZE / _cropImage.width, CROP_SIZE / _cropImage.height);
  const scale = baseScale * _cropZoom;

  const w = _cropImage.width * scale;
  const h = _cropImage.height * scale;

  const x = (CROP_SIZE - w) / 2 + _cropOffsetX;
  const y = (CROP_SIZE - h) / 2 + _cropOffsetY;

  ctx.drawImage(_cropImage, x, y, w, h);
}

// ── ZOOM SLIDER ──
document.addEventListener('input', function(e){
  if(e.target && e.target.id === 'cropZoom'){
    _cropZoom = parseFloat(e.target.value);
    drawCrop();
  }
});

// ── DRAG TO REPOSITION ──
document.addEventListener('mousedown', function(e){
  if(e.target && e.target.id === 'cropCanvas'){
    _cropDragging = true;
    _cropDragStart = {x: e.clientX - _cropOffsetX, y: e.clientY - _cropOffsetY};
    e.target.style.cursor = 'grabbing';
  }
});

document.addEventListener('mousemove', function(e){
  if(_cropDragging){
    _cropOffsetX = e.clientX - _cropDragStart.x;
    _cropOffsetY = e.clientY - _cropDragStart.y;
    drawCrop();
  }
});

document.addEventListener('mouseup', function(){
  if(_cropDragging){
    _cropDragging = false;
    const canvas = document.getElementById('cropCanvas');
    if(canvas) canvas.style.cursor = 'grab';
  }
});

// ── STEP 2: confirm crop, upload to server ──
function confirmCrop(){
  const canvas = document.getElementById('cropCanvas');
  if(!canvas) return;

  canvas.toBlob(function(blob){
    const formData = new FormData();
    formData.append('avatar', blob, 'avatar.png');

    fetch('/api/cmi/profile/upload-photo', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if(data.success && data.photo){
        const newSrc = '/' + data.photo + '?t=' + Date.now();

        const profileImg = document.getElementById('profileAvatarImg');
        if(profileImg) profileImg.src = newSrc;

        // Update topbar avatar
        document.querySelectorAll('.hdr-user-avatar').forEach(img => img.src = newSrc);

        const removeLink = document.getElementById('removePhotoLink');
        if(removeLink) removeLink.classList.remove('hidden');

        closeModal('modalCrop');
        showToast('Profile photo updated');
      } else {
        showToast((data.message || 'Upload failed'));
      }
    })
    .catch(() => showToast('Upload failed. Please try again.'));
  }, 'image/png');
}

// ── REMOVE PHOTO ──
function confirmRemovePhoto(event){
  event.preventDefault();

  showConfirm('Remove Photo', 'Are you sure you want to remove your profile photo?', function(){
    fetch('/api/cmi/profile/remove-photo', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: 'remove'})
    })
    .then(res => res.json())
    .then(data => {
      if(data.success){
        const defaultSrc = '/assets/img/default-avatar.svg';
        const profileImg = document.getElementById('profileAvatarImg');
        const sidebarImg = document.getElementById('sidebarAvatarImg');

        if(profileImg) profileImg.src = defaultSrc;
        if(sidebarImg) sidebarImg.src = defaultSrc;

        const removeLink = document.getElementById('removePhotoLink');
        if(removeLink) removeLink.classList.add('hidden');

        toast('✅ Profile photo removed');
      } else {
        toast('⚠️ ' + (data.message || 'Failed to remove photo'));
      }
    })
    .catch(() => toast('⚠️ Failed to remove photo. Please try again.'));
  });
}
