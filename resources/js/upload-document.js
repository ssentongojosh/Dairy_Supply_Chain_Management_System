document.addEventListener('DOMContentLoaded', () => {
  // Set up drag and drop for National ID
  setupDragAndDrop(
    'nationalIdDropZone', 
    'national_id_input', 
    'nationalIdFileDisplay'
  );
  
  // Set up drag and drop for URSB Certificate
  setupDragAndDrop(
    'ursbCertificateDropZone', 
    'ursb_certificate_input', 
    'ursbCertificateFileDisplay'
  );
  
  function setupDragAndDrop(dropZoneId, inputId, displayId) {
    const dropZone = document.getElementById(dropZoneId);
    const fileInput = document.getElementById(inputId);
    const fileNameDisplay = document.getElementById(displayId);
    
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
      if (file) handleFile(file, fileInput, fileNameDisplay);
    });

    // handle manual input
    fileInput.addEventListener('change', e => {
      if (e.target.files.length) {
        handleFile(e.target.files[0], fileInput, fileNameDisplay);
      } else {
        fileNameDisplay.textContent = '';
      }
    });
  }

  function handleFile(file, fileInput, fileNameDisplay) {
    if (file.type === 'application/pdf') {
      // stage the file in the input
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;

      fileNameDisplay.textContent = `Selected file: ${file.name}`;
      fileNameDisplay.classList.remove('text-danger');
      fileNameDisplay.classList.add('text-muted');
    } else {
      fileNameDisplay.textContent = 'Invalid file type. Please select a PDF.';
      fileNameDisplay.classList.add('text-danger');
      fileInput.value = '';
    }
  }
});