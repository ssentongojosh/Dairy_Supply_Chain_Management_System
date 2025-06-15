<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Application - DSCM</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #000; /* Black background */
            color: #fff; /* White text */
        }

        .form-label {
            color: #fff;
        }

        .card {
            background-color: #111; /* Slightly lighter black for contrast */
            border: 1px solid #444;
        }

        .card-header {
            background-color: #111;
            color: #fff;
            border-bottom: 1px solid #444;
        }

        .btn-success {
            background-color: green;
            border-color: green;
        }

        .btn-success:hover {
            background-color: #0a0;
            border-color: #080;
        }

        .form-control,
        .form-select {
            background-color: #222;
            color: #fff;
            border: 1px solid #555;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #222;
            color: #fff;
            border-color: green;
            box-shadow: 0 0 0 0.25rem rgba(0, 128, 0, 0.25);
        }

        .alert-danger {
            background-color: #330000;
            border-color: red;
            color: red;
        }

        .logo-text {
            font-size: 2rem;
            font-weight: bold;
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="logo-text">DSCM</div>

    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow">
                <div class="card-header text-center">
                    <h3>Vendor Application Form</h3>
                </div>

                <div class="card-body">
                    <!-- Display validation errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('vendor.register') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="vendor_type" class="form-label">Vendor Type</label>
                            <select class="form-select" name="vendor_type" id="vendor_type" required>
                                <option value="" disabled selected>Select vendor type</option>
                                <option value="retailer">Retailer</option>
                                <option value="wholesaler">Wholesaler</option>
                                <option value="supplier">Supplier</option>
                                <option value="factory">Factory</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Register as Vendor</button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>


