(() => {
  const config = JSON.parse(document.getElementById('blog-editor-config').textContent);
  const status = document.getElementById('editor-status');
  const save = document.getElementById('save-blog');
  let pendingUploads = 0;
  const editor = Jodit.make('#content', {
    height: 580,
    toolbarAdaptive: false,
    toolbarSticky: false,
    buttons: ['undo', 'redo', '|', 'paragraph', 'bold', 'italic', 'underline', 'strikethrough', '|', 'ul', 'ol', '|', 'align', 'outdent', 'indent', '|', 'link', 'image', 'table', 'hr', '|', 'brush', 'fontsize', 'superscript', 'subscript', '|', 'eraser', 'fullsize', 'preview'],
    defaultActionOnPaste: 'insert_as_html',
    askBeforePasteHTML: false,
    askBeforePasteFromWord: false,
    uploader: {
      url: config.uploadUrl,
      headers: { 'X-CSRF-TOKEN': config.csrf, 'Accept': 'application/json' },
      insertImageAsBase64URI: false,
      imagesExtensions: ['jpg', 'jpeg', 'png', 'webp'],
      beforeUpload() { pendingUploads++; save.disabled = true; status.textContent = 'Uploading image…'; return true; },
      isSuccess: response => response.success === true,
      getMessage: response => response.message || 'Image upload failed. Please try again.',
      process: response => response.data,
      defaultHandlerSuccess(data) {
        const target = this.j || this;
        target.s.restore();
        data.files.forEach(url => target.s.insertImage(url));
        status.textContent = 'Image uploaded. Add alternative text using image properties.';
      },
      error(error) { pendingUploads = 0; save.disabled = false; status.textContent = error.message || 'Image upload failed. Please try again.'; },
      defaultHandlerError(error) { pendingUploads = 0; save.disabled = false; status.textContent = error.message || 'Image upload failed. Please try again.'; },
    },
  });
  window.blogEditor = editor;
  status.textContent = 'Editor ready. Tables, internal links, images and formatting are available above.';
  editor.events.on('filesWereUploaded', () => { pendingUploads = Math.max(0, pendingUploads - 1); save.disabled = pendingUploads > 0; });
  document.getElementById('blog-form').addEventListener('submit', event => {
    if (pendingUploads) { event.preventDefault(); status.textContent = 'Please wait for the image upload to finish.'; return; }
    const content = editor.value;
    const plain = new DOMParser().parseFromString(content, 'text/html').body.textContent.trim();
    if (!plain) { event.preventDefault(); status.textContent = 'Please write some article content before saving.'; editor.s.focus(); return; }
    document.getElementById('content').value = content;
    save.disabled = true;
    save.textContent = 'Saving…';
  });
  const title = document.getElementById('title');
  const slug = document.getElementById('slug');
  let slugEdited = !!slug.value;
  slug.addEventListener('input', () => { slugEdited = !!slug.value; updatePreview(); });
  title.addEventListener('input', () => { if (!slugEdited) slug.value = title.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); updatePreview(); });
  function updatePreview() {
    document.getElementById('slug-preview').textContent = slug.value;
    document.getElementById('seo-preview-slug').textContent = slug.value;
    document.getElementById('seo-preview-title').textContent = document.getElementById('meta_title').value || title.value || 'Your blog title';
    document.getElementById('seo-preview-description').textContent = document.getElementById('meta_description').value || document.getElementById('excerpt').value || 'Your article description will appear here.';
    document.querySelectorAll('[data-counter]').forEach(input => { document.getElementById(input.id + '-count').textContent = input.value.length; });
  }
  ['meta_title', 'meta_description', 'excerpt'].forEach(id => document.getElementById(id).addEventListener('input', updatePreview));
  updatePreview();
  let previewUrl;
  document.getElementById('image').addEventListener('change', event => {
    const file = event.target.files[0];
    if (!file) return;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    const image = document.getElementById('image-preview');
    image.src = previewUrl;
    image.classList.remove('hidden');
  });
})();
