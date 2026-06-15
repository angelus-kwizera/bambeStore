document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');

    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('admin-sidebar--open');
        });
    }

    const fileInput = document.getElementById('image_file');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePlaceholder');
    const previewWrap = document.getElementById('imagePreviewWrap');

    if (fileInput && preview) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.src = ev.target.result;
                    preview.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                    if (previewWrap) previewWrap.classList.remove('image-preview--empty');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
