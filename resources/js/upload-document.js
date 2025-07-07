document.addEventListener('DOMContentLoaded', () => {
  let uploadedFiles = {
    nationalId: false,
    ursbCertificate: false
  };

  // Initialize animations
  initializeAnimations();
  
  // Set up drag and drop for National ID
  setupDragAndDrop(
    'nationalIdDropZone', 
    'national_id_input', 
    'nationalIdFileDisplay',
    'nationalIdCheck',
    'nationalId'
  );
  
  // Set up drag and drop for URSB Certificate
  setupDragAndDrop(
    'ursbCertificateDropZone', 
    'ursb_certificate_input', 
    'ursbCertificateFileDisplay',
    'ursbCertificateCheck',
    'ursbCertificate'
  );

  // Handle form submission with animations
  setupFormSubmission();

  function initializeAnimations() {
    // Add staggered animation to form steps
    const formSteps = document.querySelectorAll('.form-step');
    formSteps.forEach((step, index) => {
      step.style.animationDelay = `${(index + 1) * 0.1}s`;
    });

    // Add typing animation to textarea
    const textarea = document.getElementById('business_description');
    if (textarea) {
      textarea.addEventListener('input', () => {
        updateProgress();
        // Add subtle scale animation on typing
        textarea.style.transform = 'scale(1.01)';
        setTimeout(() => {
          textarea.style.transform = 'scale(1)';
        }, 150);
      });
    }
  }
  
  function setupDragAndDrop(dropZoneId, inputId, displayId, checkId, fileType) {
    const dropZone = document.getElementById(dropZoneId);
    const fileInput = document.getElementById(inputId);
    const fileNameDisplay = document.getElementById(displayId);
    const checkmark = document.getElementById(checkId);
    
    if (!dropZone || !fileInput || !fileNameDisplay) return;

    // Click to open file picker with animation
    dropZone.addEventListener('click', () => {
      // Add click animation
      dropZone.style.transform = 'scale(0.98)';
      setTimeout(() => {
        dropZone.style.transform = 'scale(1)';
      }, 150);
      fileInput.click();
    });

    // Enhanced drag animations
    ['dragenter','dragover'].forEach(evt =>
      dropZone.addEventListener(evt, e => {
        e.preventDefault(); 
        e.stopPropagation();
        dropZone.classList.add('dragover');
        // Stop the pulse animation when dragging
        const icon = dropZone.querySelector('i');
        if (icon) icon.classList.remove('icon-pulse');
      })
    );
    
    ['dragleave','drop','dragend'].forEach(evt =>
      dropZone.addEventListener(evt, e => {
        e.preventDefault(); 
        e.stopPropagation();
        dropZone.classList.remove('dragover');
        // Resume pulse animation
        const icon = dropZone.querySelector('i');
        if (icon && !uploadedFiles[fileType]) icon.classList.add('icon-pulse');
      })
    );

    // Handle drop with animation
    dropZone.addEventListener('drop', e => {
      const [file] = e.dataTransfer.files;
      if (file) handleFileWithAnimation(file, fileInput, fileNameDisplay, dropZone, checkmark, fileType);
    });

    // Handle manual input with animation
    fileInput.addEventListener('change', e => {
      if (e.target.files.length) {
        handleFileWithAnimation(e.target.files[0], fileInput, fileNameDisplay, dropZone, checkmark, fileType);
      } else {
        resetFileDisplay(fileNameDisplay, dropZone, checkmark, fileType);
      }
    });
  }

  function handleFileWithAnimation(file, fileInput, fileNameDisplay, dropZone, checkmark, fileType) {
    if (file.type === 'application/pdf') {
      // Show loading animation
      showLoadingAnimation(dropZone);
      
      // Simulate file processing delay for better UX
      setTimeout(() => {
        // Stage the file in the input
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;

        // Update display with success animation
        showSuccessAnimation(fileNameDisplay, dropZone, checkmark, file.name, fileType);
        
        // Update progress
        uploadedFiles[fileType] = true;
        updateProgress();
        
        hideLoadingAnimation(dropZone);
      }, 800); // Simulate processing time
      
    } else {
      // Show error animation
      showErrorAnimation(fileNameDisplay, dropZone, file.name);
      fileInput.value = '';
    }
  }

  function showLoadingAnimation(dropZone) {
    const overlay = dropZone.querySelector('.upload-overlay');
    const spinner = dropZone.querySelector('.upload-spinner');
    const icon = dropZone.querySelector('i');
    
    if (overlay) overlay.style.display = 'block';
    if (spinner) spinner.style.display = 'block';
    if (icon) icon.classList.remove('icon-pulse');
  }

  function hideLoadingAnimation(dropZone) {
    const overlay = dropZone.querySelector('.upload-overlay');
    const spinner = dropZone.querySelector('.upload-spinner');
    
    if (overlay) overlay.style.display = 'none';
    if (spinner) spinner.style.display = 'none';
  }

  function showSuccessAnimation(fileNameDisplay, dropZone, checkmark, fileName, fileType) {
    // Update file display
    fileNameDisplay.textContent = `✓ ${fileName}`;
    fileNameDisplay.classList.remove('text-danger');
    fileNameDisplay.classList.add('text-success', 'fw-semibold');
    
    // Add success class to drop zone
    dropZone.classList.add('file-uploaded');
    
    // Show checkmark with animation
    if (checkmark) {
      checkmark.classList.add('show');
    }
    
    // Stop pulse animation on icon
    const icon = dropZone.querySelector('i');
    if (icon) {
      icon.classList.remove('icon-pulse');
      icon.classList.add('text-success');
    }
    
    // Add success shake animation
    dropZone.style.animation = 'checkmarkScale 0.5s ease-in-out';
    setTimeout(() => {
      dropZone.style.animation = '';
    }, 500);
  }

  function showErrorAnimation(fileNameDisplay, dropZone, fileName) {
    fileNameDisplay.textContent = `✗ Invalid file type: ${fileName}. Please select a PDF.`;
    fileNameDisplay.classList.remove('text-success');
    fileNameDisplay.classList.add('text-danger', 'fw-semibold');
    
    // Add error shake animation
    dropZone.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
      dropZone.style.animation = '';
    }, 500);
  }

  function resetFileDisplay(fileNameDisplay, dropZone, checkmark, fileType) {
    fileNameDisplay.textContent = '';
    fileNameDisplay.className = 'mt-2 text-muted small';
    dropZone.classList.remove('file-uploaded');
    
    if (checkmark) {
      checkmark.classList.remove('show');
    }
    
    const icon = dropZone.querySelector('i');
    if (icon) {
      icon.classList.add('icon-pulse');
      icon.classList.remove('text-success');
    }
    
    uploadedFiles[fileType] = false;
    updateProgress();
  }

  function updateProgress() {
    const textarea = document.getElementById('business_description');
    const progressBar = document.getElementById('uploadProgress');
    const progressText = document.getElementById('progressText');
    
    if (!progressBar || !progressText) return;
    
    let progress = 0;
    
    // Check file uploads (60% of progress)
    if (uploadedFiles.nationalId) progress += 30;
    if (uploadedFiles.ursbCertificate) progress += 30;
    
    // Check business description (40% of progress)
    if (textarea && textarea.value.trim().length > 10) {
      const descriptionProgress = Math.min(40, (textarea.value.length / 100) * 40);
      progress += descriptionProgress;
    }
    
    // Animate progress bar
    progressBar.style.width = `${progress}%`;
    progressText.textContent = `${Math.round(progress)}% Complete`;
    
    // Add animation class when progress changes
    progressBar.classList.add('progress-bar-animated');
    setTimeout(() => {
      progressBar.classList.remove('progress-bar-animated');
    }, 1000);
    
    // Change color based on progress
    progressBar.className = 'progress-bar';
    if (progress < 30) {
      progressBar.classList.add('bg-danger');
    } else if (progress < 70) {
      progressBar.classList.add('bg-warning');
    } else {
      progressBar.classList.add('bg-success');
    }
  }

  function setupFormSubmission() {
    const form = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!form || !submitBtn) return;
    
    form.addEventListener('submit', (e) => {
      // Add loading state to button
      submitBtn.classList.add('btn-loading');
      submitBtn.disabled = true;
      
      // Add pulse animation to the entire form
      form.style.animation = 'pulse 2s infinite';
      
      // Show completion message (this will be overridden by the actual form submission)
      setTimeout(() => {
        // This is just for visual feedback - the actual form submission will redirect
        const card = document.querySelector('.card-body');
        if (card) {
          card.innerHTML = `
            <div class="text-center">
              <div class="checkmark show mb-3"></div>
              <h4 class="text-success">Documents Submitted Successfully!</h4>
              <p class="text-muted">Your documents are being processed for verification.</p>
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Processing...</span>
              </div>
            </div>
          `;
        }
      }, 1000);
    });
  }

  // Initialize progress on page load
  updateProgress();
});

// Add shake animation CSS
const shakeStyle = document.createElement('style');
shakeStyle.textContent = `
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
  }
`;
document.head.appendChild(shakeStyle);