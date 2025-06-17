@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;
  $containerNav = $containerNav ?? 'container-fluid';
  $navbarDetached = ($navbarDetached ?? '');

@endphp

  <!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
  <nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
    @endif
    @if(isset($navbarDetached) && $navbarDetached == '')
      <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{$containerNav}}">
          @endif

          <!--  Brand demo (display only for navbar-full and hide on below xl) -->
          @if(isset($navbarFull))
            <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
              <a href="{{url('/')}}" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20])</span>
                <span class="app-brand-text demo menu-text fw-semibold ms-1">{{config('variables.templateName')}}</span>
              </a>
              <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                <i class="ri-close-fill align-middle"></i>
              </a>
            </div>
          @endif

          <!-- ! Not required for layout-without-menu -->
          @if(!isset($navbarHideToggle))
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="ri-menu-fill ri-24px"></i>
              </a>
            </div>
          @endif

          <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

            <!-- Search -->
            <div class="navbar-nav align-items-center">
              <div class="nav-item navbar-search-wrapper mb-0">
                <div class="navbar-search">
                  <div class="input-group input-group-merge">
                    <span class="input-group-text" id="basic-addon-search31">
                      <i class="ri-search-line ri-22px" id="searchIcon"></i>
                      <i class="ri-loader-4-line ri-22px d-none animate-spin" id="searchLoader"></i>
                    </span>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Search..."
                      aria-label="Search"
                      aria-describedby="basic-addon-search31"
                      id="navbarSearch"
                      autocomplete="off"
                    >
                    <span class="input-group-text d-none d-sm-flex">
                      <kbd class="navbar-search-suggestion">
                        <span class="text-muted">⌘</span>
                        <span class="text-muted">K</span>
                      </kbd>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-md-auto">

              <!-- Language -->
              <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <i class="icon-base ri ri-translate-2 icon-22px"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item active waves-effect" href="#" data-language="en" data-text-direction="ltr">
                      <span>English</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item waves-effect" href="#" data-language="fr" data-text-direction="ltr">
                      <span>French</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item waves-effect" href="#" data-language="ar" data-text-direction="rtl">
                      <span>Arabic</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item waves-effect" href="#" data-language="de" data-text-direction="ltr">
                      <span>German</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ Language -->

              <!-- Style Switcher -->
              <li class="nav-item dropdown me-sm-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill waves-effect" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Toggle theme (light)" aria-expanded="false">
                  <i class="ri-sun-line icon-base ri icon-22px theme-icon-active"></i>
                  <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                  <li>
                    <button type="button" class="dropdown-item align-items-center waves-effect active" data-bs-theme-value="light" aria-pressed="true">
                      <span> <i class="icon-base ri ri-sun-line icon-md me-3" data-icon="sun-line"></i>Light</span>
                    </button>
                  </li>
                  <li>
                    <button type="button" class="dropdown-item align-items-center waves-effect" data-bs-theme-value="dark" aria-pressed="false">
                      <span> <i class="icon-base ri ri-moon-clear-line icon-md me-3" data-icon="moon-clear-line"></i>Dark</span>
                    </button>
                  </li>
                  <li>
                    <button type="button" class="dropdown-item align-items-center waves-effect" data-bs-theme-value="system" aria-pressed="false">
                      <span> <i class="icon-base ri ri-computer-line icon-md me-3" data-icon="computer-line"></i>System</span>
                    </button>
                  </li>
                </ul>
              </li>
              <!-- / Style Switcher-->

              <!-- Quick links  -->
              <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-sm-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill waves-effect" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                  <i class="icon-base ri ri-star-smile-line icon-22px"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-0">
                  <div class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-2 my-50">
                      <h6 class="mb-0 me-auto">Shortcuts</h6>
                      <a href="javascript:void(0)" class="dropdown-shortcuts-add btn btn-text-secondary rounded-pill btn-icon waves-effect" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Add shortcuts" data-bs-original-title="Add shortcuts">
                        <i class="icon-base ri ri-add-line icon-20px text-heading"></i>
                      </a>
                    </div>
                  </div>
                  <div class="dropdown-shortcuts-list scrollable-container">
                    <div class="row row-bordered overflow-visible g-0">
                      <div class="dropdown-shortcuts-item col">
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                          <i class="icon-base ri ri-calendar-line icon-26px text-heading"></i>
                        </span>
                        <a href="{{url('/app/calendar')}}" class="stretched-link">Calendar</a>
                        <small>Appointments</small>
                      </div>
                      <div class="dropdown-shortcuts-item col">
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                          <i class="icon-base ri ri-wechat-line icon-26px text-heading"></i>
                        </span>
                        <a href="{{url('/app/chat')}}" class="stretched-link">Chat</a>
                        <small>Team Communication</small>
                      </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                      <div class="dropdown-shortcuts-item col">
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                          <i class="icon-base ri ri-user-line icon-26px text-heading"></i>
                        </span>
                        <a href="{{url('/users')}}" class="stretched-link">User Management</a>
                        <small>Manage Users</small>
                      </div>
                      <div class="dropdown-shortcuts-item col">
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                          <i class="icon-base ri ri-computer-line icon-26px text-heading"></i>
                        </span>
                        <a href="{{url('/roles')}}" class="stretched-link">Role Management</a>
                        <small>Permissions</small>
                      </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                      <div class="dropdown-shortcuts-item col">
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                          <i class="icon-base ri ri-pie-chart-2-line icon-26px text-heading"></i>
                        </span>
                        <a href="{{url('/')}}" class="stretched-link">Dashboard</a>
                        <small>Analytics</small>
                      </div>
                      <div class="dropdown-shortcuts-item col">
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                          <i class="icon-base ri ri-settings-4-line icon-26px text-heading"></i>
                        </span>
                        <a href="{{url('/settings')}}" class="stretched-link">Settings</a>
                        <small>Account Settings</small>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
              <!-- Quick links -->

              <!-- Notification -->
              <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill waves-effect" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                  <span class="position-relative">
                    <i class="icon-base ri ri-notification-2-line icon-22px"></i>
                    <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                  </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-0">
                  <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                      <h6 class="mb-0 me-auto">Notifications</h6>
                      <div class="d-flex align-items-center h6 mb-0">
                        <span class="badge bg-label-primary rounded-pill me-2">3 New</span>
                        <a href="javascript:void(0)" class="dropdown-notifications-all p-2" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Mark all as read" data-bs-original-title="Mark all as read">
                          <i class="icon-base ri ri-mail-open-line text-heading"></i>
                        </a>
                      </div>
                    </div>
                  </li>
                  <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item list-group-item-action dropdown-notifications-item waves-effect">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="icon-base ri ri-check-line"></i>
                              </span>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="small mb-50">Welcome to DSCMS! 🎉</h6>
                            <small class="mb-1 d-block text-body">Your account has been successfully created</small>
                            <small class="text-body-secondary">Just now</small>
                          </div>
                          <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"> <span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"> <span class="icon-base ri ri-close-line"></span></a>
                          </div>
                        </div>
                      </li>
                      <li class="list-group-item list-group-item-action dropdown-notifications-item waves-effect">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="icon-base ri ri-message-3-line"></i>
                              </span>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="small mb-50">New message received 💬</h6>
                            <small class="mb-1 d-block text-body">You have a new message in chat</small>
                            <small class="text-body-secondary">5 min ago</small>
                          </div>
                          <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"> <span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"> <span class="icon-base ri ri-close-line"></span></a>
                          </div>
                        </div>
                      </li>
                      <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read waves-effect">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="icon-base ri ri-settings-4-line"></i>
                              </span>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="small mb-50">System maintenance scheduled</h6>
                            <small class="mb-1 d-block text-body">Maintenance window: Tonight 2-4 AM</small>
                            <small class="text-body-secondary">1 hour ago</small>
                          </div>
                          <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a href="javascript:void(0)" class="dropdown-notifications-read"> <span class="badge badge-dot"></span></a>
                            <a href="javascript:void(0)" class="dropdown-notifications-archive"> <span class="icon-base ri ri-close-line"></span></a>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </li>
                  <li class="border-top">
                    <div class="d-grid p-4">
                      <a class="btn btn-primary btn-sm d-flex h-px-34 waves-effect waves-light" href="javascript:void(0);">
                        <small class="align-middle">View all notifications</small>
                      </a>
                    </div>
                  </li>
                </ul>
              </li>
              <!--/ Notification -->

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
                  <li>
                    <a class="dropdown-item waves-effect" href="javascript:void(0);">
                      <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-2">
                          <div class="avatar avatar-online">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="w-px-40 h-auto rounded-circle">
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          @auth
                            <h6 class="mb-0 small">{{ Auth::user()->name }}</h6>
                            <small class="text-body-secondary">{{ ucfirst(strtolower(Auth::user()->role->value)) }}</small>
                          @else
                            <h6 class="mb-0 small">Guest</h6>
                            <small class="text-body-secondary">Not logged in</small>
                          @endauth
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item waves-effect" href="{{url('pages/account-settings-account')}}">
                      <i class="icon-base ri ri-user-3-line icon-22px me-2"></i>
                      <span class="align-middle">My Profile</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item waves-effect" href="javascript:void(0);">
                      <i class='icon-base ri ri-settings-4-line icon-22px me-2'></i>
                      <span class="align-middle">Settings</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item waves-effect" href="javascript:void(0);">
                      <span class="d-flex align-items-center align-middle">
                        <i class="flex-shrink-0 icon-base ri ri-file-text-line icon-22px me-2"></i>
                        <span class="flex-grow-1 align-middle">Billing</span>
                        <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger h-px-20 d-flex align-items-center justify-content-center">4</span>
                      </span>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider my-1"></div>
                  </li>
                  <li>
                    <div class="d-grid px-4 pt-2 pb-1">
                      @auth
                        <form method="POST" action="{{ route('logout') }}">
                          @csrf
                          <button type="submit" class="btn btn-danger d-flex w-100 border-0 waves-effect waves-light">
                            <small class="align-middle">Logout</small>
                            <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
                          </button>
                        </form>
                      @else
                        <a class="btn btn-primary d-flex waves-effect waves-light" href="{{ route('login') }}">
                          <small class="align-middle">Login</small>
                          <i class="icon-base ri ri-login-box-r-line ms-2 icon-16px"></i>
                        </a>
                      @endauth
                    </div>
                  </li>
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>

          @if(!isset($navbarDetached))
        </div>
        @endif
      </nav>
      <!-- / Navbar -->

<!-- Navbar Search Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('navbarSearch');
    const searchIcon = document.getElementById('searchIcon');
    const searchLoader = document.getElementById('searchLoader');

    // Search categories for better organization
    const searchCategories = {
        navigation: {
            name: 'Navigation',
            icon: 'ri-navigation-line',
            color: 'primary'
        },
        users: {
            name: 'Users',
            icon: 'ri-user-line',
            color: 'info'
        },
        settings: {
            name: 'Settings',
            icon: 'ri-settings-line',
            color: 'warning'
        }
    };

    // Keyboard shortcut (Ctrl + K or Cmd + K) to focus search
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }

        // Escape to clear search and close results
        if (e.key === 'Escape' && searchInput) {
            searchInput.blur();
            searchInput.value = '';
            removeSearchResults();
        }
    });

    // Search functionality
    if (searchInput) {
        let searchTimeout;

        // Focus and blur effects
        searchInput.addEventListener('focus', function() {
            this.closest('.navbar-search').classList.add('focused');
        });

        searchInput.addEventListener('blur', function() {
            this.closest('.navbar-search').classList.remove('focused');
            // Delay hiding results to allow clicking on them
            setTimeout(() => {
                removeSearchResults();
            }, 200);
        });

        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            if (query.length === 0) {
                removeSearchResults();
                hideLoader();
                return;
            }

            if (query.length >= 2) {
                showLoader();
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = e.target.value.trim();
                if (query.length >= 2) {
                    showLoader();
                    performSearch(query);
                }
            }

            // Arrow key navigation in search results
            const results = document.querySelectorAll('#searchResults .search-result-item');
            if (results.length > 0) {
                const current = document.querySelector('#searchResults .search-result-item.active');
                let currentIndex = current ? Array.from(results).indexOf(current) : -1;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentIndex = (currentIndex + 1) % results.length;
                    setActiveResult(results, currentIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentIndex = currentIndex <= 0 ? results.length - 1 : currentIndex - 1;
                    setActiveResult(results, currentIndex);
                } else if (e.key === 'Enter' && current) {
                    e.preventDefault();
                    current.click();
                }
            }
        });
    }

    function showLoader() {
        if (searchIcon) searchIcon.classList.add('d-none');
        if (searchLoader) {
            searchLoader.classList.remove('d-none');
            searchLoader.classList.add('animate-spin');
        }
    }

    function hideLoader() {
        if (searchIcon) searchIcon.classList.remove('d-none');
        if (searchLoader) {
            searchLoader.classList.add('d-none');
            searchLoader.classList.remove('animate-spin');
        }
    }

    function performSearch(query) {
        // Simulate API delay
        setTimeout(() => {
            hideLoader();

            // Enhanced search logic
            let results = [];

            // Search in navigation menu items
            const menuItems = document.querySelectorAll('.menu-link');
            menuItems.forEach(item => {
                const text = item.textContent.toLowerCase().trim();
                const href = item.getAttribute('href') || '#';

                if (text.includes(query.toLowerCase()) && text !== '') {
                    results.push({
                        title: item.textContent.trim(),
                        url: href,
                        category: 'navigation',
                        icon: 'ri-navigation-line',
                        description: 'Navigation item'
                    });
                }
            });

            // Search in quick links
            const quickLinks = document.querySelectorAll('.dropdown-shortcuts-item a');
            quickLinks.forEach(link => {
                const text = link.textContent.toLowerCase().trim();
                const href = link.getAttribute('href') || '#';

                if (text.includes(query.toLowerCase()) && text !== '') {
                    results.push({
                        title: link.textContent.trim(),
                        url: href,
                        category: 'navigation',
                        icon: 'ri-star-line',
                        description: 'Quick access'
                    });
                }
            });

            // Add some mock data for demonstration
            if (query.toLowerCase().includes('user')) {
                results.push({
                    title: 'User Management',
                    url: '/users',
                    category: 'users',
                    icon: 'ri-user-line',
                    description: 'Manage system users'
                });
            }

            if (query.toLowerCase().includes('setting')) {
                results.push({
                    title: 'System Settings',
                    url: '/settings',
                    category: 'settings',
                    icon: 'ri-settings-line',
                    description: 'Configure system settings'
                });
            }

            // Remove duplicates
            results = results.filter((item, index, self) =>
                index === self.findIndex(t => t.title === item.title && t.url === item.url)
            );

            if (results.length > 0) {
                showSearchResults(results, query);
            } else {
                showNoResults(query);
            }
        }, 500);
    }

    function showSearchResults(results, query) {
        removeSearchResults();

        const resultsContainer = document.createElement('div');
        resultsContainer.id = 'searchResults';
        resultsContainer.className = 'position-absolute bg-white border rounded-3 shadow-lg mt-2 w-100 search-results-container';
        resultsContainer.style.cssText = 'top: 100%; left: 0; z-index: 1050; max-height: 400px; overflow-y: auto; min-width: 350px;';

        // Header
        const header = document.createElement('div');
        header.className = 'px-3 py-2 border-bottom bg-light rounded-top';
        header.innerHTML = `
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-medium">Search Results</small>
                <span class="badge bg-primary rounded-pill">${results.length}</span>
            </div>
        `;
        resultsContainer.appendChild(header);

        const resultsList = document.createElement('div');
        resultsList.className = 'p-2';

        // Group results by category
        const groupedResults = {};
        results.forEach(result => {
            if (!groupedResults[result.category]) {
                groupedResults[result.category] = [];
            }
            groupedResults[result.category].push(result);
        });

        Object.keys(groupedResults).forEach(category => {
            const categoryResults = groupedResults[category];

            // Category header (if more than one category)
            if (Object.keys(groupedResults).length > 1) {
                const categoryHeader = document.createElement('div');
                categoryHeader.className = 'px-2 py-1 mt-2 first:mt-0';
                categoryHeader.innerHTML = `
                    <small class="text-muted fw-medium text-uppercase">
                        <i class="${searchCategories[category]?.icon || 'ri-folder-line'} me-1"></i>
                        ${searchCategories[category]?.name || category}
                    </small>
                `;
                resultsList.appendChild(categoryHeader);
            }

            categoryResults.slice(0, 5).forEach((result, index) => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item p-2 rounded-2 cursor-pointer';
                resultItem.style.cssText = 'transition: all 0.2s ease;';

                resultItem.innerHTML = `
                    <a href="${result.url}" class="text-decoration-none text-dark d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded-circle bg-label-${searchCategories[category]?.color || 'primary'}">
                                    <i class="${result.icon} ri-18px"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">${highlightQuery(result.title, query)}</div>
                            <small class="text-muted">${result.description || 'Navigate to page'}</small>
                        </div>
                        <div class="flex-shrink-0 ms-2">
                            <i class="ri-arrow-right-line ri-16px text-muted"></i>
                        </div>
                    </a>
                `;

                // Hover effects
                resultItem.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = 'var(--bs-gray-100)';
                    this.classList.add('active');
                    // Remove active from others
                    document.querySelectorAll('#searchResults .search-result-item.active').forEach(item => {
                        if (item !== this) item.classList.remove('active');
                    });
                });

                resultItem.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });

                resultsList.appendChild(resultItem);
            });
        });

        resultsContainer.appendChild(resultsList);

        // Footer with total results
        if (results.length > 5) {
            const footer = document.createElement('div');
            footer.className = 'px-3 py-2 border-top bg-light text-center rounded-bottom';
            footer.innerHTML = `
                <small class="text-muted">Showing 5 of ${results.length} results</small>
            `;
            resultsContainer.appendChild(footer);
        }

        // Position relative to search wrapper
        const searchWrapper = document.querySelector('.navbar-search-wrapper');
        if (searchWrapper) {
            searchWrapper.style.position = 'relative';
            searchWrapper.appendChild(resultsContainer);
        }
    }

    function showNoResults(query) {
        removeSearchResults();

        const resultsContainer = document.createElement('div');
        resultsContainer.id = 'searchResults';
        resultsContainer.className = 'position-absolute bg-white border rounded-3 shadow-lg mt-2 w-100';
        resultsContainer.style.cssText = 'top: 100%; left: 0; z-index: 1050; min-width: 300px;';

        resultsContainer.innerHTML = `
            <div class="p-4 text-center">
                <i class="ri-search-line mb-2 d-block text-muted" style="font-size: 2rem;"></i>
                <h6 class="mb-1">No results found</h6>
                <small class="text-muted">Try searching for something else</small>
                <div class="mt-3">
                    <small class="text-muted">Searched for: <mark class="bg-warning bg-opacity-25 border-0 rounded px-1">${query}</mark></small>
                </div>
            </div>
        `;

        const searchWrapper = document.querySelector('.navbar-search-wrapper');
        if (searchWrapper) {
            searchWrapper.style.position = 'relative';
            searchWrapper.appendChild(resultsContainer);
        }
    }

    function highlightQuery(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark class="bg-warning bg-opacity-25 border-0 rounded px-1">$1</mark>');
    }

    function setActiveResult(results, index) {
        results.forEach((result, i) => {
            if (i === index) {
                result.classList.add('active');
                result.style.backgroundColor = 'var(--bs-gray-100)';
                result.scrollIntoView({ block: 'nearest' });
            } else {
                result.classList.remove('active');
                result.style.backgroundColor = '';
            }
        });
    }

    function removeSearchResults() {
        const existingResults = document.getElementById('searchResults');
        if (existingResults) {
            existingResults.remove();
        }
    }

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar-search-wrapper')) {
            removeSearchResults();
        }
    });
});
</script>

<!-- Custom CSS for search enhancements -->
<style>
.navbar-search {
    min-width: 240px;
}

.navbar-search .input-group {
    transition: all 0.15s ease-in-out;
}

.navbar-search.focused .input-group {
    box-shadow: 0 0 0 0.2rem rgb(13 110 253 / 25%);
    border-radius: 6px;
}

.navbar-search .form-control:focus {
    border-color: transparent;
    box-shadow: none;
}

.navbar-search-suggestion {
    background: transparent;
    border: 1px solid var(--bs-border-color);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.75rem;
    line-height: 1;
}

.navbar-search-suggestion span {
    font-size: 10px;
}

.search-results-container {
    animation: searchFadeIn 0.2s ease-out;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

@keyframes searchFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.search-result-item {
    transition: all 0.2s ease;
}

.search-result-item:hover {
    background-color: var(--bs-gray-100) !important;
}

.cursor-pointer {
    cursor: pointer;
}

.first\:mt-0:first-child {
    margin-top: 0 !important;
}

@media (max-width: 576px) {
    .navbar-search {
        min-width: 180px;
    }
}
</style>
