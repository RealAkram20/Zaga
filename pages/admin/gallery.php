<?php
$admin_page = 'gallery';
$page_title = 'Gallery';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">

      <div class="admin-page-header">
        <div>
          <h1>Image Gallery</h1>
          <p>Manage images across your site</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button class="admin-btn admin-btn-primary" onclick="document.getElementById('galleryUploadInput').click()">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span class="btn-label">Upload</span>
          </button>
          <input type="file" id="galleryUploadInput" multiple accept="image/*" style="display:none;" onchange="uploadGalleryFiles(this.files)">
        </div>
      </div>

      <!-- Toolbar -->
      <div class="admin-card" style="margin-bottom:var(--spacing-lg);padding:var(--spacing-base) var(--spacing-lg);">
        <div style="display:flex;gap:var(--spacing-base);align-items:center;flex-wrap:wrap;">
          <input type="text" id="gallerySearch" placeholder="Search images..." class="admin-input" style="flex:1;min-width:150px;max-width:300px;" oninput="filterGallery()">
          <select id="galleryDirFilter" class="admin-input" style="width:auto;" onchange="filterGallery()">
            <option value="all">All Folders</option>
            <option value="uploads">Uploads</option>
            <option value="images">Images</option>
          </select>
          <span id="galleryCount" style="color:var(--color-text-muted);font-size:var(--font-sm);margin-left:auto;"></span>
        </div>
      </div>

      <!-- Drop zone -->
      <div id="galleryDropZone" class="gallery-dropzone" style="display:none;">
        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--color-primary);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p>Drop images here to upload</p>
      </div>

      <!-- Gallery grid -->
      <div id="galleryGrid" class="gallery-grid">
        <div style="grid-column:1/-1;text-align:center;padding:var(--spacing-3xl);color:var(--color-text-muted);">Loading images...</div>
      </div>

    </div>

<style>
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: var(--spacing-base);
}

.gallery-item {
  background: var(--color-bg-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  overflow: hidden;
  position: relative;
  transition: box-shadow var(--transition-fast);
}

.gallery-item:hover {
  box-shadow: var(--shadow-md);
}

.gallery-item-img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  display: block;
  background: var(--color-bg-subtle);
}

.gallery-item-info {
  padding: 8px 10px;
}

.gallery-item-name {
  font-size: var(--font-xs);
  font-weight: var(--font-weight-medium);
  color: var(--color-text-dark);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 2px;
}

.gallery-item-meta {
  font-size: 10px;
  color: var(--color-text-muted);
  display: flex;
  justify-content: space-between;
}

.gallery-item-actions {
  position: absolute;
  top: 6px;
  right: 6px;
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity var(--transition-fast);
}

.gallery-item:hover .gallery-item-actions {
  opacity: 1;
}

.gallery-action-btn {
  width: 28px;
  height: 28px;
  border: none;
  border-radius: var(--radius-base);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  background: rgba(255,255,255,0.92);
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
  color: var(--color-text-base);
  transition: background var(--transition-fast);
}

.gallery-action-btn:hover {
  background: #fff;
}

.gallery-action-btn.danger:hover {
  color: var(--color-danger);
}

.gallery-dropzone {
  border: 2px dashed var(--color-primary);
  border-radius: var(--radius-lg);
  padding: var(--spacing-3xl);
  text-align: center;
  background: var(--color-primary-light);
  margin-bottom: var(--spacing-lg);
}

.gallery-dropzone p {
  margin-top: var(--spacing-sm);
  color: var(--color-primary);
  font-weight: var(--font-weight-semibold);
}

/* Copy path tooltip */
.gallery-item-path {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(17,24,39,0.95);
  color: #fff;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 11px;
  white-space: nowrap;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.15s;
  z-index: 10;
}

@media (max-width: 768px) {
  .gallery-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: var(--spacing-sm);
  }

  .gallery-item-img {
    height: 90px;
  }

  .gallery-item-actions {
    opacity: 1;
  }

  .gallery-item-info {
    padding: 6px 8px;
  }
}

@media (max-width: 480px) {
  .gallery-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-sm);
  }

  .gallery-item-img {
    height: 80px;
  }

  .gallery-action-btn {
    width: 24px;
    height: 24px;
  }
}
</style>

<script>
var GALLERY_API = '<?php echo SITE_URL; ?>/api/gallery.php';
var allGalleryFiles = [];

document.addEventListener('DOMContentLoaded', function() {
    loadGallery();
    setupDragDrop();
});

async function loadGallery() {
    try {
        var res = await fetch(GALLERY_API + '?action=list');
        var json = await res.json();
        if (json.success) {
            allGalleryFiles = json.data;
            filterGallery();
        }
    } catch (err) {
        document.getElementById('galleryGrid').innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--color-text-muted);">Failed to load images</div>';
    }
}

function filterGallery() {
    var search = document.getElementById('gallerySearch').value.toLowerCase();
    var dir = document.getElementById('galleryDirFilter').value;
    var filtered = allGalleryFiles.filter(function(f) {
        var nameMatch = f.name.toLowerCase().indexOf(search) !== -1;
        var dirMatch = dir === 'all' || f.dir === dir;
        return nameMatch && dirMatch;
    });
    document.getElementById('galleryCount').textContent = filtered.length + ' images';
    renderGallery(filtered);
}

function renderGallery(files) {
    var grid = document.getElementById('galleryGrid');
    if (files.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--color-text-muted);">No images found</div>';
        return;
    }

    grid.innerHTML = files.map(function(f) {
        var sizeStr = f.size < 1024 ? f.size + ' B' : f.size < 1048576 ? (f.size / 1024).toFixed(0) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB';
        var imgUrl = '<?php echo SITE_URL; ?>/' + f.path;
        return '<div class="gallery-item" title="' + escapeHtml(f.path) + '">' +
            '<div class="gallery-item-actions">' +
                '<button class="gallery-action-btn" title="Copy path" onclick="copyPath(\'' + escapeHtml(f.path) + '\', this)">' +
                    '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>' +
                '</button>' +
                '<button class="gallery-action-btn" title="Rename" onclick="renameFile(\'' + escapeHtml(f.path) + '\', \'' + escapeHtml(f.name) + '\')">' +
                    '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' +
                '</button>' +
                '<button class="gallery-action-btn danger" title="Delete" onclick="deleteFile(\'' + escapeHtml(f.path) + '\', \'' + escapeHtml(f.name) + '\')">' +
                    '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>' +
                '</button>' +
            '</div>' +
            '<img class="gallery-item-img" src="' + imgUrl + '" alt="' + escapeHtml(f.name) + '" loading="lazy" onerror="this.src=\'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%2250%25%22 x=%2250%25%22 text-anchor=%22middle%22 font-size=%2230%22>?</text></svg>\'">' +
            '<div class="gallery-item-info">' +
                '<div class="gallery-item-name">' + escapeHtml(f.name) + '</div>' +
                '<div class="gallery-item-meta"><span>' + f.dir + '</span><span>' + sizeStr + '</span></div>' +
            '</div>' +
        '</div>';
    }).join('');
}

function copyPath(path, btn) {
    navigator.clipboard.writeText(path).then(function() {
        showToast('Path copied: ' + path);
    });
}

function renameFile(path, oldName) {
    var baseName = oldName.replace(/\.[^.]+$/, '');
    showAppPrompt('Rename File', 'Enter new filename for "' + oldName + '":', baseName, function(newName) {
        if (newName === baseName) return;

        var fd = new FormData();
        fd.append('action', 'rename');
        fd.append('old_path', path);
        fd.append('new_name', newName);

        fetch(GALLERY_API, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json.success) {
                    showToast(json.message);
                    loadGallery();
                } else {
                    showToast(json.message || 'Rename failed', 'error');
                }
            })
            .catch(function(err) { showToast('Error: ' + err.message, 'error'); });
    });
}

function deleteFile(path, name) {
    showConfirmModal('Delete Image', 'Are you sure you want to delete "' + name + '"? This cannot be undone.', 'Delete', function() {
        var fd = new FormData();
        fd.append('action', 'delete');
        fd.append('path', path);

        fetch(GALLERY_API, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json.success) {
                    showToast(json.message);
                    loadGallery();
                } else {
                    showToast(json.message || 'Delete failed', 'error');
                }
            })
            .catch(function(err) { showToast('Error: ' + err.message, 'error'); });
    }, true);
}

async function uploadGalleryFiles(files) {
    if (!files || files.length === 0) return;

    var fd = new FormData();
    for (var i = 0; i < files.length; i++) {
        fd.append('files[]', files[i]);
    }
    fd.append('action', 'upload');

    showToast('Uploading ' + files.length + ' file(s)...');

    try {
        var res = await fetch(GALLERY_API, { method: 'POST', body: fd });
        var json = await res.json();
        showToast(json.message, json.success ? 'success' : 'error');
        if (json.success) loadGallery();
    } catch (err) {
        showToast('Upload error: ' + err.message, 'error');
    }

    // Reset the file input
    document.getElementById('galleryUploadInput').value = '';
}

function setupDragDrop() {
    var content = document.querySelector('.admin-content-inner');
    var dropZone = document.getElementById('galleryDropZone');
    var dragCounter = 0;

    content.addEventListener('dragenter', function(e) {
        e.preventDefault();
        dragCounter++;
        dropZone.style.display = 'block';
    });

    content.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dragCounter--;
        if (dragCounter <= 0) {
            dropZone.style.display = 'none';
            dragCounter = 0;
        }
    });

    content.addEventListener('dragover', function(e) {
        e.preventDefault();
    });

    content.addEventListener('drop', function(e) {
        e.preventDefault();
        dragCounter = 0;
        dropZone.style.display = 'none';
        if (e.dataTransfer.files.length > 0) {
            uploadGalleryFiles(e.dataTransfer.files);
        }
    });
}
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
