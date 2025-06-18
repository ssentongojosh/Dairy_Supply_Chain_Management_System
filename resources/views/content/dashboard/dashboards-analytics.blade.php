@extends('layouts/contentNavbarLayout')

@section('title', 'DSCMS - Analytics Dashboard')

@push('page-styles')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
<!-- DataTables Bootstrap 5 styling -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<style>
    .dt-paging {
        max-width: 100%;
        overflow-x: auto;
    }
    .dt-paging .pagination {
        margin-bottom: 0;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    .dt-paging .page-link {
        white-space: nowrap;
        padding: .375rem .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dt-paging .page-item {
        margin-bottom: 5px;
    }
    .dt-paging .page-item.active .page-link {
        background-color: #696cff;
        border-color: #696cff;
    }
    .dt-paging .page-item .page-link:focus {
        box-shadow: none;
    }
    .dt-paging .icon-base {
        font-size: 1.2rem;
    }
</style>
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Dashboard /</span> Admin
    </h4>

    {{-- Stats Cards --}}
    <div class="row g-6 mb-6">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-1">Users</p>
                            <div class="d-flex align-items-center">
                                {{-- Dynamically load total users --}}
                                <h4 class="mb-1 me-2">{{ $totalUsers ?? 'N/A' }}</h4>
                                <p class="text-success mb-1">(100%)</p>
                            </div>
                            <small class="mb-0">Total Users</small>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-primary rounded">
                                <div class="icon-base ri ri-group-line icon-26px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-1">Verified Users</p>
                            <div class="d-flex align-items-center">
                                {{-- Dynamically load verified users --}}
                                <h4 class="mb-1 me-2">{{ $verifiedUsers ?? 'N/A' }}</h4>
                                <p class="text-success mb-1">(+95%)</p>
                            </div>
                            <small class="mb-0">Recent analytics</small>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-danger rounded">
                                <div class="icon-base ri ri-user-add-line icon-26px scaleX-n1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-1">Duplicate Users</p>
                            <div class="d-flex align-items-center">
                                {{-- Dynamically load duplicate users --}}
                                <h4 class="mb-1 me-2">{{ $duplicateUsers ?? 'N/A' }}</h4>
                                <p class="text-danger mb-1">(0%)</p>
                            </div>
                            <small class="mb-0">Recent analytics</small>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-success rounded">
                                <div class="icon-base ri ri-user-follow-line icon-26px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-1">
                            <p class="text-heading mb-1">Verification Pending</p>
                            <div class="d-flex align-items-center">
                                {{-- Dynamically load pending verification users --}}
                                <h4 class="mb-1 me-2">{{ $pendingVerification ?? 'N/A' }}</h4>
                                <p class="text-success mb-1">(+6%)</p>
                            </div>
                            <small class="mb-0">Recent analytics</small>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-warning rounded">
                                <div class="icon-base ri ri-user-search-line icon-26px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Users List Table --}}
    <div class="card">
        <div class="card-header border-bottom">
            <h6 class="card-title mb-0">Filters</h6>
        </div>
        <div class="card-datatable">
            <div id="DataTables_Table_0_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                <div class="row m-3 my-0 justify-content-end">
                    <div class="d-md-flex align-items-center gap-md-3 flex-wrap justify-content-md-between justify-content-center">
                        <div class="dt-length mb-3 mb-md-0">
                            <select name="DataTables_Table_0_length" aria-controls="users-table" class="form-select form-select-sm" id="dt-length-0">
                                <option value="7">7</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="70">70</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div class="dt-search mb-3 mb-md-0">
                            <input type="search" class="form-control form-control-sm" id="dt-search-0" placeholder="Search users" aria-controls="users-table">
                        </div>
                        <div class="dt-buttons btn-group flex-wrap d-md-flex gap-4 mb-md-0 mb-3 justify-content-center">
                            <div class="btn-group">
                                <button id="staticExportBtn" type="button" class="btn btn-label-secondary me-2">
                                  <span><i class="icon-base ri ri-upload-2-line me-2 icon-sm"></i>Export</span>
                                </button>
                            </div>
                            <button class="btn add-new btn-primary" tabindex="0" aria-controls="DataTables_Table_0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
                                <span><i class="icon-base ri ri-add-line icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Add New User</span></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="dt-layout-table">
                    <div class="d-md-flex align-items-center dt-layout-full">
                        <div id="DataTables_Table_0_processing" class="dt-processing card" role="status" style="display: none;"><div><div></div><div></div><div></div></div></div>
                    </div>
                    <div class="table-responsive">
                        <table id="users-table" class="table table-striped table-bordered dt-responsive nowrap align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="control dt-orderable-none"></th>
                                    <th>Id</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th class="text-center">Role</th>
                                    <th class="text-center">Verified</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loop through users data passed from controller --}}
                                @foreach($users as $user)
                                <tr>
                                    <td class="control"></td>
                                    <td>{{ $user->id }}</td>
                                    <td class="sorting_1">
                                        <div class="d-flex justify-content-start align-items-center user-name">
                                            <div class="avatar-wrapper">
                                                <div class="avatar avatar-sm me-4">
                                                    {{-- Display user initial or image --}}
                                                    <span class="avatar-initial rounded-circle bg-label-{{ ['primary', 'success', 'info', 'warning', 'danger'][array_rand(['primary', 'success', 'info', 'warning', 'danger'])] }}">{{ substr($user->name, 0, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('users.show', $user->id) }}" class="text-truncate text-heading">
                                                    <span class="fw-medium">{{ $user->name }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="user-email">{{ $user->email }}</span></td>
                                    <td class="text-center"><span class="user-role">{{ ucfirst($user->role->value) }}</span></td>
                                    <td class="text-center">
                                        @if($user->verified)
                                            <i class="icon-base ri fs-4 ri-shield-check-line text-success"></i>
                                        @else
                                            <i class="icon-base ri fs-4 ri-shield-line text-danger"></i>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center gap-4 justify-content-end">
                                            <button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="{{ $user->id }}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
                                                <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record"
                                                data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                                                data-url="{{ route('users.destroy', $user->id) }}"
                                                data-name="{{ $user->name }}">
                                                <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
                                            </button>
                                            <button class="btn btn-icon btn-text-secondary btn-sm rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="icon-base ri ri-more-2-line icon-22px"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end m-0">
                                                <a href="{{ route('users.show', $user->id) }}" class="dropdown-item">View</a>
                                                <a href="javascript:;" class="dropdown-item">Suspend</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
                <div class="row mx-3 justify-content-between">
                    <div class="col-12 d-flex justify-content-end">
                         <div class="dt-paging mt-3">
                            {{ $users->links('pagination.custom') }} {{-- Custom pagination view --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Offcanvas to add new user --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 h-100">
            <form class="add-new-user pt-0 fv-plugins-bootstrap5 fv-plugins-framework" id="addNewUserForm" novalidate="novalidate">
                @csrf {{-- CSRF token for Laravel forms --}}
                <input type="hidden" name="id" id="user_id">
                <div class="form-floating form-floating-outline mb-5 form-control-validation fv-plugins-icon-container">
                    <input type="text" class="form-control" id="add-user-fullname" placeholder="John Doe" name="name" aria-label="John Doe">
                    <label for="add-user-fullname">Full Name</label>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="form-floating form-floating-outline mb-5 form-control-validation fv-plugins-icon-container">
                    <input type="text" id="add-user-email" class="form-control" placeholder="john.doe@example.com" aria-label="john.doe@example.com" name="email">
                    <label for="add-user-email">Email</label>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="form-floating form-floating-outline mb-5 form-control-validation fv-plugins-icon-container">
                    <input type="text" id="add-user-contact" class="form-control phone-mask" placeholder="+1 (609) 988-44-11" aria-label="john.doe@example.com" name="userContact">
                    <label for="add-user-contact">Contact</label>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="form-floating form-floating-outline mb-5 form-control-validation fv-plugins-icon-container">
                    <input type="text" id="add-user-company" class="form-control" placeholder="Web Developer" aria-label="jdoe1" name="company">
                    <label for="add-user-company">Company</label>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                {{-- <div class="form-floating form-floating-outline mb-5 form-floating-select2">
                    <div class="position-relative">
                        <select id="country" class="select2 form-select select2-hidden-accessible" data-select2-id="country" tabindex="-1" aria-hidden="true" name="country">
                            <option value="" data-select2-id="2">Select</option>
                            <option value="Australia">Australia</option>
                            <option value="Bangladesh">Bangladesh</option>
                            <option value="Belarus">Belarus</option>
                            <option value="Brazil">Brazil</option>
                            <option value="Canada">Canada</option>
                            <option value="China">China</option>
                            <option value="France">France</option>
                            <option value="Germany">Germany</option>
                            <option value="India">India</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="Israel">Israel</option>
                            <option value="Italy">Italy</option>
                            <option value="Japan">Japan</option>
                            <option value="Korea">Korea, Republic of</option>
                            <option value="Mexico">Mexico</option>
                            <option value="Philippines">Philippines</option>
                            <option value="Russia">Russian Federation</option>
                            <option value="South Africa">South Africa</option>
                            <option value="Thailand">Thailand</option>
                            <option value="Turkey">Turkey</option>
                            <option value="Ukraine">Ukraine</option>
                            <option value="United Arab Emirates">United Arab Emirates</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                        </select>
                    </div>
                    <label for="country">Country</label>
                </div> --}}
                <div class="form-floating form-floating-outline mb-5">
                    <select id="user-role" class="form-select" name="role">
                        <option value="subscriber">Plant Manager</option>
                        <option value="editor">Inspector</option>
                        <option value="maintainer">Driver</option>
                        <option value="author">Plant Worker</option>
                        <option value="admin">Admin</option>
                        {{-- Add other specific roles for your dairy system --}}

                        <option value="sales_manager">Sales Manager</option>
                    </select>
                    <label for="user-role">User Role</label>
                </div>
                {{-- <div class="form-floating form-floating-outline mb-5">
                    <select id="user-plan" class="form-select" name="plan">
                        <option value="basic">Basic</option>
                        <option value="enterprise">Enterprise</option>
                        <option value="company">Company</option>
                        <option value="team">Team</option>
                    </select>
                    <label for="user-plan">Select Plan</label>
                </div> --}}
                <div class="form-check mb-5">
                    <input class="form-check-input" type="checkbox" value="1" id="add-user-verified" name="verified">
                    <label class="form-check-label" for="add-user-verified">
                        Verified
                    </label>
                </div>
                <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit waves-effect waves-light">Submit</button>
                <button type="reset" class="btn btn-outline-danger waves-effect" data-bs-dismiss="offcanvas">Cancel</button>
                <input type="hidden">
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete <strong id="deleteUserName"></strong>?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteUser">Delete</button>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection

@push('page-scripts')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.js',
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.js',
        'resources/assets/js/dashboards-analytics.js'])
<script>
    // Handle pagination links to work with DataTables
    $(document).ready(function() {
        // Listen for pagination clicks and update the table
        $(document).on('click', '.dt-paging .pagination a', function(e) {
            e.preventDefault();

            // Get the URL from the pagination link
            let url = $(this).attr('href');

            // Reload the page with the new URL
            window.location.href = url;
        });

        // Ensure the pagination container is properly styled
        $('.dt-paging').css('max-width', '100%');

        // If the pagination is wider than its container, add scrolling
        if ($('.dt-paging').width() > $('.col-12').width()) {
            $('.dt-paging').css('overflow-x', 'auto');
        }

        // Listen for window resize
        $(window).on('resize', function() {
            // Reset width to recalculate
            $('.dt-paging').css('width', '');

            // If the pagination is wider than its container, add scrolling
            if ($('.dt-paging').width() > $('.col-12').width()) {
                $('.dt-paging').css('overflow-x', 'auto');
            }
        });
    });
</script>
@endpush
