@extends('layouts/contentNavbarLayout')

@section('title', 'Account settings - Account')

@section('page-script')
@vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2 gap-lg-0">
        <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="ri-group-line me-1_5"></i>Account</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('pages/account-settings-notifications')}}"><i class="ri-notification-4-line me-1_5"></i>Notifications</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('pages/account-settings-connections')}}"><i class="ri-link-m me-1_5"></i>Connections</a></li>
      </ul>
    </div>
    <div class="card mb-6">
      <!-- Account -->
      <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-6">
          <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}"
               alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
          <div class="button-wrapper">
            <label for="upload" class="btn btn-sm btn-primary me-3 mb-4" tabindex="0">
              <span class="d-none d-sm-block">Upload new photo</span>
              <i class="ri-upload-2-line d-block d-sm-none"></i>
              <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg, image/jpg, image/gif" />
            </label>
            <button type="button" class="btn btn-sm btn-outline-danger account-image-reset mb-4">
              <i class="ri-refresh-line d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Reset</span>
            </button>

            <div>Allowed JPG, GIF or PNG. Max size of 800K</div>
          </div>
        </div>
      </div>
      <div class="card-body pt-0">
        <form id="formAccountSettings" method="POST" action="{{ route('account.update') }}">
          @csrf
          <div class="row mt-1 g-5">
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input class="form-control" type="text" id="email" name="email"
                       value="{{ Auth::user()->email }}" placeholder="john.doe@example.com" />
                <label for="email">E-mail</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" class="form-control" id="organization" name="name"
                       value="{{ Auth::user()->name }}" />
                <label for="organization">Name/Business Name</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" class="form-control" id="address" name="address"
                       value="{{ Auth::user()->address }}" placeholder="Address" />
                <label for="address">Address</label>
              </div>
            </div>
          <div class="mt-6">
            <button type="submit" class="btn btn-primary me-3">Save changes</button>
            <button type="reset" class="btn btn-outline-secondary">Reset</button>
          </div>
        </form>
      </div>
      <!-- /Account -->
    </div>
    <div class="card">
      <h5 class="card-header">Delete Account</h5>
      <div class="card-body">
        <form id="formAccountDeactivation" method="POST" action="{{ route('account.deactivate') }}">
          @csrf
          <div class="form-check mb-6 ms-3">
            <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
            <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
          </div>
          <button type="submit" class="btn btn-danger deactivate-account" disabled="disabled">Deactivate Account</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable deactivate button based on checkbox
    const accountActivationCheckbox = document.getElementById('accountActivation');
    const deactivateButton = document.querySelector('.deactivate-account');

    accountActivationCheckbox.addEventListener('change', function() {
        deactivateButton.disabled = !this.checked;
    });

    // Handle avatar upload
    const uploadInput = document.getElementById('upload');
    const uploadedAvatar = document.getElementById('uploadedAvatar');
    const resetButton = document.querySelector('.account-image-reset');

    uploadInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];

            // Validate file size (800KB = 819200 bytes)
            if (file.size > 819200) {
                showModal('error', 'File Size Error', 'File size must be less than 800KB');
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showModal('error', 'Invalid File Type', 'Only JPG, PNG and GIF files are allowed');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Show loading state
            const originalSrc = uploadedAvatar.src;
            uploadedAvatar.style.opacity = '0.6';

            fetch('{{ route("account.upload-avatar") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    uploadedAvatar.src = data.avatar_url;
                    showToast('success', data.message);
                } else {
                    showToast('error', data.message || 'Upload failed');
                    uploadedAvatar.src = originalSrc;
                }
            })
            .catch(error => {
                showToast('error', 'An error occurred during upload');
                uploadedAvatar.src = originalSrc;
            })
            .finally(() => {
                uploadedAvatar.style.opacity = '1';
                uploadInput.value = '';
            });
        }
    });

    // Handle avatar reset
    resetButton.addEventListener('click', function() {
        showConfirmModal(
            'Reset Avatar',
            'Are you sure you want to reset your avatar to default?',
            'Reset',
            'btn-outline-danger',
            function() {
                fetch('{{ route("account.reset-avatar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        uploadedAvatar.src = '{{ asset("assets/img/avatars/1.png") }}';
                        showToast('success', data.message);
                    } else {
                        showToast('error', data.message || 'Reset failed');
                    }
                })
                .catch(error => {
                    showToast('error', 'An error occurred during reset');
                });
            }
        );
    });

    // Handle account settings form submission
    const accountForm = document.getElementById('formAccountSettings');
    accountForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;

        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                if (data.avatar_url) {
                    uploadedAvatar.src = data.avatar_url;
                }
            } else {
                if (data.errors) {
                    let errorMsg = '';
                    Object.values(data.errors).forEach(errors => {
                        errors.forEach(error => {
                            errorMsg += error + '\n';
                        });
                    });
                    showToast('error', errorMsg);
                } else {
                    showToast('error', data.message || 'Update failed');
                }
            }
        })
        .catch(error => {
            showToast('error', 'An error occurred during update');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    });

    // Handle account deactivation form submission
    const deactivationForm = document.getElementById('formAccountDeactivation');
    deactivationForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!accountActivationCheckbox.checked) {
            showModal('error', 'Confirmation Required', 'Please confirm account deactivation by checking the checkbox');
            return;
        }

        showConfirmModal(
            'Deactivate Account',
            'Are you absolutely sure you want to deactivate your account? This action cannot be undone and will permanently delete all your data.',
            'Deactivate Account',
            'btn-danger',
            function() {
                const formData = new FormData();
                formData.append('confirmation', '1');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const submitButton = deactivateButton;
                const originalText = submitButton.textContent;

                submitButton.disabled = true;
                submitButton.textContent = 'Deactivating...';

                fetch(deactivationForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', data.message);
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    } else {
                        showToast('error', data.message || 'Deactivation failed');
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                })
                .catch(error => {
                    showToast('error', 'An error occurred during deactivation');
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
            }
        );
    });

    // Toast notification function
    function showToast(type, message) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast && toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }

    // Custom modal function
    function showModal(type, title, message) {
        const iconClass = type === 'error' ? 'ri-error-warning-line text-danger' :
                         type === 'success' ? 'ri-check-line text-success' :
                         'ri-information-line text-info';

        const modalId = 'customModal_' + Date.now();
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="d-flex align-items-center">
                                <i class="${iconClass} me-2" style="font-size: 1.5rem;"></i>
                                <h5 class="modal-title mb-0">${title}</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById(modalId));

        // Clean up modal after it's hidden
        document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
            this.remove();
        });

        modal.show();
    }

    // Custom confirmation modal function
    function showConfirmModal(title, message, confirmText, confirmClass, onConfirm) {
        const modalId = 'confirmModal_' + Date.now();
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="d-flex align-items-center">
                                <i class="ri-question-line text-warning me-2" style="font-size: 1.5rem;"></i>
                                <h5 class="modal-title mb-0">${title}</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn ${confirmClass}" id="confirmBtn_${modalId}">${confirmText}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById(modalId));

        // Handle confirm button click
        document.getElementById(`confirmBtn_${modalId}`).addEventListener('click', function() {
            modal.hide();
            if (onConfirm && typeof onConfirm === 'function') {
                onConfirm();
            }
        });

        // Clean up modal after it's hidden
        document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
            this.remove();
        });

        modal.show();
    }
});
</script>

@endsection
