<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">MyApp</a>
            <div class="collapse navbar-collapse">
                @php
                    // Get user role string (supporting backed enums or string role)
                    $role = auth()->user()->role instanceof \BackedEnum ? auth()->user()->role->value : auth()->user()->role;
                    $dashboardRoute = $role . '.dashboard';
                @endphp

                <ul class="navbar-nav ms-auto">
                    @if (Route::has($dashboardRoute))
                        <li class="nav-item">
                            <a href="{{ route($dashboardRoute) }}" class="nav-link">Dashboard</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
