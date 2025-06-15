document.addEventListener('DOMContentLoaded', () => {
  const dropZone        = document.getElementById('dropZone');
  const fileInput       = document.getElementById('business_document_input');
  const fileNameDisplay = document.getElementById('fileNameDisplay');
  if (!dropZone || !fileInput || !fileNameDisplay) return;

  // click to open file‐picker
  dropZone.addEventListener('click', () => fileInput.click());

  // highlight on drag
  ['dragenter','dragover'].forEach(evt =>
    dropZone.addEventListener(evt, e => {
      e.preventDefault(); e.stopPropagation();
      dropZone.classList.add('dragover');
    })
  );
  ['dragleave','drop','dragend'].forEach(evt =>
    dropZone.addEventListener(evt, e => {
      e.preventDefault(); e.stopPropagation();
      dropZone.classList.remove('dragover');
    })
  );

  // handle drop
  dropZone.addEventListener('drop', e => {
    const [file] = e.dataTransfer.files;
    if (file) handleFile(file);
  });

  // handle manual input
  fileInput.addEventListener('change', e => {
    if (e.target.files.length) {
      handleFile(e.target.files[0]);
    } else {
      fileNameDisplay.textContent = '';
    }
  });

  function handleFile(file) {
    if (file.type === 'application/pdf') {
      // stage the file in the input
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;

      fileNameDisplay.textContent        = `Selected file: ${file.name}`;
      fileNameDisplay.classList.remove('text-danger');
      fileNameDisplay.classList.add('text-muted');
    } else {
      fileNameDisplay.textContent        = 'Invalid file type. Please select a PDF.';
      fileNameDisplay.classList.add('text-danger');
      fileInput.value                    = '';
    }
  }
});