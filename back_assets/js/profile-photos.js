/**
 * Profile Photo Handling
 * This script ensures profile photos are loaded correctly and fallbacks are provided
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fix broken images
    const profileImages = document.querySelectorAll('.avatar img');
    const defaultPhoto = document.getElementById('userphotoonboarding2')?.getAttribute('onerror')?.match(/'([^']+)'/)?.[1] || '/back_assets/img/users/profile_photo/default_photo.jpg';
    
    profileImages.forEach(function(img) {
        // Set default onerror handler if not already set
        if (!img.hasAttribute('onerror')) {
            img.onerror = function() {
                this.onerror = null;
                this.src = defaultPhoto;
            };
        }
        
        // Check if image is already broken (won't trigger onerror if already in DOM)
        if (img.complete && (img.naturalWidth === 0 || img.naturalHeight === 0)) {
            img.src = defaultPhoto;
        }
    });
    
    // Add image preview for profile photo upload
    const profilePhotoInput = document.getElementById('profile_photo');
    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', function() {
            const fileSize = this.files[0]?.size / 1024 / 1024 || 0; // Size in MB
            
            if (fileSize > 0 && fileSize <= 2) {
                // Create or get preview element
                let preview = document.getElementById('profile_preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'profile_preview';
                    preview.style.width = '100px';
                    preview.style.height = '100px';
                    preview.style.borderRadius = '50%';
                    preview.style.marginTop = '10px';
                    preview.style.objectFit = 'cover';
                    this.parentNode.appendChild(preview);
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            } else if (fileSize > 2) {
                alert('File is too large. Maximum size is 2MB.');
                this.value = '';
                const preview = document.getElementById('profile_preview');
                if (preview) {
                    preview.style.display = 'none';
                }
            }
        });
    }
}); 