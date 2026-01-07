/**
 * Paste Image Upload Handler
 * Handles paste events for image upload functionality
 */

class PasteImageUploader {
    constructor(uploadUrl, csrfToken) {
        this.uploadUrl = uploadUrl;
        this.csrfToken = csrfToken;
        this.activeElement = null;
        this.init();
    }

    init() {
        // Listen to paste events globally
        document.addEventListener('paste', (e) => this.handlePaste(e));
        
        // Track which textarea or input is focused
        document.addEventListener('focusin', (e) => {
            if (e.target.matches('textarea, input[type="text"], [contenteditable="true"]')) {
                this.activeElement = e.target;
            }
        });
    }

    handlePaste(event) {
        const items = (event.clipboardData || event.originalEvent.clipboardData).items;
        
        for (let item of items) {
            if (item.type.indexOf('image') !== -1) {
                event.preventDefault();
                
                const blob = item.getAsFile();
                this.uploadImage(blob);
                
                break;
            }
        }
    }

    uploadImage(blob) {
        // Show loading indicator
        this.showLoading();

        // Convert blob to base64
        const reader = new FileReader();
        reader.onloadend = () => {
            const base64data = reader.result;
            
            // Send to server
            fetch(this.uploadUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    image: base64data
                })
            })
            .then(response => response.json())
            .then(data => {
                this.hideLoading();
                
                if (data.success) {
                    this.handleSuccess(data);
                } else {
                    this.handleError(data.message || 'Upload gagal');
                }
            })
            .catch(error => {
                this.hideLoading();
                this.handleError('Terjadi kesalahan saat upload: ' + error.message);
            });
        };
        
        reader.readAsDataURL(blob);
    }

    handleSuccess(data) {
        // Show success message
        this.showNotification('Gambar berhasil diupload!', 'success');
        
        // Find the closest container to the active element
        if (this.activeElement) {
            // Strategy 1: Try to find pilihan-item or pernyataan-item first (for answer choices and statements)
            let container = this.activeElement.closest('.pilihan-item, .pernyataan-item');
            let previewContainer = null;
            
            if (container) {
                // For pilihan-item or pernyataan-item, find preview in upload-container
                const uploadContainer = container.querySelector('.upload-container');
                if (uploadContainer) {
                    previewContainer = uploadContainer.querySelector('[id^="preview-"]');
                }
            }
            
            // Strategy 2: If not found, try form-group or parent container (for questions)
            if (!previewContainer) {
                container = this.activeElement.closest('.form-group, .soal-content, .sub-soal-item');
                if (container) {
                    previewContainer = container.querySelector('[id^="preview-"]');
                }
            }
            
            // Strategy 3: Try to find nearest preview container by traversing siblings
            if (!previewContainer) {
                const parent = this.activeElement.parentElement;
                if (parent) {
                    // Look in siblings or next elements
                    const uploadContainer = parent.querySelector('.upload-container') || 
                                          parent.nextElementSibling?.classList.contains('upload-container') ? 
                                          parent.nextElementSibling : null;
                    
                    if (uploadContainer) {
                        previewContainer = uploadContainer.querySelector('[id^="preview-"]');
                    }
                }
            }
            
            if (previewContainer) {
                // Show the preview
                this.showPreview(previewContainer, data.url, data.path);
            } else {
                console.warn('Preview container not found. Active element:', this.activeElement);
                this.showNotification('Preview container tidak ditemukan. Gambar tersimpan tapi preview tidak tampil.', 'error');
            }
        }
    }

    showPreview(container, url, path) {
        // Don't change innerHTML, instead update existing elements
        const existingPreview = container.querySelector('.image-preview-container');
        
        if (existingPreview) {
            // Update existing preview
            const img = existingPreview.querySelector('img');
            if (img) {
                img.src = url;
                img.onclick = function() { 
                    if (typeof openImageZoom === 'function') openImageZoom(url); 
                };
            }
        } else {
            // Create new preview
            container.innerHTML = `
                <div class="image-preview-container">
                    <img src="${url}" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px; cursor: pointer;" onclick="if(typeof openImageZoom === 'function') openImageZoom('${url}')">
                    <button type="button" class="remove-image-btn" onclick="removePreview('${container.id}', '${path}')">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            `;
        }
        
        container.style.display = 'block';

        // NEW APPROACH: Store image path as data attribute on preview container
        // This is more reliable than dynamic hidden inputs
        const inputName = this.getInputNameFromPreviewId(container.id);
        container.setAttribute('data-image-path', path);
        container.setAttribute('data-input-name', inputName);
        
        console.log('✓ Image path stored in data attribute');
        console.log('  Preview ID:', container.id);
        console.log('  Input name:', inputName);
        console.log('  Path:', path);
    }

    getInputNameFromPreviewId(previewId) {
        // Convert preview-soal-123 to gambar_soal_123
        // Convert preview-a-123 to gambar_pilihan_123_a
        // Convert preview-pernyataan-1-2 to gambar_pernyataan_1_2
        const parts = previewId.replace('preview-', '').split('-');
        
        if (parts[0] === 'soal') {
            return 'gambar_soal_' + parts[1];
        } else if (parts[0] === 'pembahasan') {
            return 'gambar_pembahasan_' + parts[1];
        } else if (parts[0] === 'pernyataan') {
            // Format: preview-pernyataan-1-2 -> gambar_pernyataan_1_2
            return 'gambar_pernyataan_' + parts[1] + '_' + parts[2];
        } else {
            // pilihan: a, b, c, d, etc
            return 'gambar_pilihan_' + parts[1] + '_' + parts[0];
        }
    }

    showLoading() {
        // Create or show loading overlay
        let loadingDiv = document.getElementById('paste-upload-loading');
        
        if (!loadingDiv) {
            loadingDiv = document.createElement('div');
            loadingDiv.id = 'paste-upload-loading';
            loadingDiv.innerHTML = `
                <div class="paste-loading-overlay">
                    <div class="paste-loading-content">
                        <div class="paste-loading-spinner"></div>
                        <p>Mengupload gambar...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingDiv);
            
            // Add styles
            const style = document.createElement('style');
            style.textContent = `
                .paste-loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                }
                .paste-loading-content {
                    background: white;
                    padding: 30px;
                    border-radius: 12px;
                    text-align: center;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
                }
                .paste-loading-spinner {
                    width: 40px;
                    height: 40px;
                    margin: 0 auto 15px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #702637;
                    border-radius: 50%;
                    animation: paste-spin 1s linear infinite;
                }
                @keyframes paste-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
        
        loadingDiv.style.display = 'block';
    }

    hideLoading() {
        const loadingDiv = document.getElementById('paste-upload-loading');
        if (loadingDiv) {
            loadingDiv.style.display = 'none';
        }
    }

    showNotification(message, type = 'success') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `paste-notification paste-notification-${type}`;
        notification.innerHTML = `
            <div class="paste-notification-content">
                <span class="material-symbols-outlined">
                    ${type === 'success' ? 'check_circle' : 'error'}
                </span>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Add styles if not exists
        if (!document.getElementById('paste-notification-styles')) {
            const style = document.createElement('style');
            style.id = 'paste-notification-styles';
            style.textContent = `
                .paste-notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    z-index: 10000;
                    animation: slideIn 0.3s ease-out;
                }
                .paste-notification-success {
                    background: #10b981;
                    color: white;
                }
                .paste-notification-error {
                    background: #ef4444;
                    color: white;
                }
                .paste-notification-content {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                @keyframes slideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideIn 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    handleError(message) {
        this.showNotification(message, 'error');
        console.error('Paste upload error:', message);
    }
}

// Function to remove preview (can be called from inline onclick)
function removePreview(previewId, imagePath) {
    const previewContainer = document.getElementById(previewId);
    if (previewContainer) {
        previewContainer.style.display = 'none';
        previewContainer.innerHTML = '';
        
        // Clear hidden input - check pilihan-item first, then form-group
        const parentContainer = previewContainer.closest('.pilihan-item, .form-group, .soal-content, .sub-soal-item, .upload-container');
        if (parentContainer) {
            const hiddenInput = parentContainer.querySelector('input[type="hidden"][name*="gambar"]');
            if (hiddenInput) {
                hiddenInput.value = '';
            }
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the create/edit soal page
    if (document.querySelector('.soal-form-container') || document.querySelector('form[action*="soal"]')) {
        const uploadUrl = document.querySelector('meta[name="paste-upload-url"]')?.content || '/soal/upload-paste-image';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (csrfToken) {
            window.pasteImageUploader = new PasteImageUploader(uploadUrl, csrfToken);
            console.log('Paste image upload initialized. You can now paste images directly!');
        }
    }
});
