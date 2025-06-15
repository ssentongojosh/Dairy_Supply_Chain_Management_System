<!-- filepath: c:\xampp\htdocs\DSCMS\resources\views\welcome.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DSCMS - Dairy Supply Chain Management System</title>

  <!-- Vite Assets -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Font definitions - can be moved to app.css if you prefer -->
  <style>
    @font-face {
      font-family: 'Inter';
      src: url('{{ asset('fonts/Inter-Regular.woff2') }}') format('woff2');
      font-weight: 400;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'Inter';
      src: url('{{ asset('fonts/Inter-Medium.woff2') }}') format('woff2');
      font-weight: 500;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'Inter';
      src: url('{{ asset('fonts/Inter-SemiBold.woff2') }}') format('woff2');
      font-weight: 600;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'Inter';
      src: url('{{ asset('fonts/Inter-Bold.woff2') }}') format('woff2');
      font-weight: 700;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'Instrument Sans';
      src: url('{{ asset('fonts/InstrumentSans-Regular.woff2') }}') format('woff2');
      font-weight: 400;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'Instrument Sans';
      src: url('{{ asset('fonts/InstrumentSans-Medium.woff2') }}') format('woff2');
      font-weight: 500;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'Instrument Sans';
      src: url('{{ asset('fonts/InstrumentSans-SemiBold.woff2') }}') format('woff2');
      font-weight: 600;
      font-style: normal;
      font-display: swap;
    }

    /* Theme toggle button styles */
    .theme-toggle {
      position: relative;
      width: 50px;
      height: 25px;
      background: #e5e7eb;
      border-radius: 25px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      border: none;
      outline: none;
    }

    .theme-toggle.dark {
      background: #374151;
    }

    .theme-toggle::before {
      content: '';
      position: absolute;
      top: 2px;
      left: 2px;
      width: 21px;
      height: 21px;
      background: white;
      border-radius: 50%;
      transition: transform 0.3s ease;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .theme-toggle.dark::before {
      transform: translateX(25px);
      background: #1f2937;
    }

    .theme-icon {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 12px;
      transition: opacity 0.3s ease;
    }

    .theme-icon.sun {
      left: 5px;
      color: #fbbf24;
    }

    .theme-icon.moon {
      right: 5px;
      color: #60a5fa;
      opacity: 0;
    }

    .theme-toggle.dark .theme-icon.sun {
      opacity: 0;
    }

    .theme-toggle.dark .theme-icon.moon {
      opacity: 1;
    }
  </style>
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#e5e5e5] font-sans flex flex-col min-h-screen transition-colors duration-300">
<!-- Header with logo and login buttons -->
<header class="w-full px-6 py-5 lg:px-8 lg:py-6 flex items-center justify-between bg-white/80 dark:bg-black/30 backdrop-blur-sm sticky top-0 z-50 shadow-sm">
  <!-- Logo -->
  <div class="flex items-center">
    <span class="text-primary dark:text-primary-dark font-bold text-3xl font-display tracking-tight">DSCMS</span>
    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 hidden sm:inline-block">Dairy Supply Chain Management System</span>
  </div>

  <!-- Theme Toggle and Login Button -->
  <div class="flex items-center space-x-4">
    <!-- Theme Toggle Button -->
    <button id="themeToggle" class="theme-toggle" aria-label="Toggle theme">
      <span class="theme-icon sun">☀️</span>
      <span class="theme-icon moon">🌙</span>
    </button>

    <!-- Login Button -->
    @if (Route::has('login'))
      <div class="flex items-center">
        @auth
          <a href="{{ url('/dashboard') }}"
             class="inline-block px-5 py-2 bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark hover:bg-primary/20 dark:hover:bg-primary-dark/30 font-medium rounded-md transition-all duration-200 ease-in-out">
            Dashboard
          </a>
        @else
          <a href="{{ route('login') }}"
             class="inline-block px-5 py-2 bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark hover:bg-primary/20 dark:hover:bg-primary-dark/30 font-medium rounded-md transition-all duration-200 ease-in-out">
            Log in
          </a>
        @endauth
      </div>
    @endif
  </div>
</header>

<!-- Main content -->
<main class="flex-grow px-6 py-8 lg:px-8 lg:py-12">
  <!-- Hero section -->
  <div class="text-center mb-12 max-w-3xl mx-auto">
    <h1 class="text-4xl md:text-5xl font-bold mb-4 bg-gradient-to-r from-primary to-primary-dark bg-clip-text text-transparent font-display">Dairy Supply Chain Management</h1>
    <p class="text-lg text-gray-700 dark:text-gray-300">Connecting dairy farmers, processors, distributors, and retailers in one unified platform</p>
  </div>

  <!-- Slideshow -->
  <div class="slideshow-container mb-16">
    <div class="slide slide-active" style="background-image: url('{{ asset('images/dairy1.jpg') }}')">
      <div class="slide-content">
        <h2 class="text-2xl font-bold mb-3">Milk Collection & Quality Testing</h2>
        <p class="text-lg max-w-2xl">Ensuring high-quality raw milk through automated quality monitoring systems.</p>
      </div>
    </div>

    <div class="slide" style="background-image: url('{{ asset('images/dairy2.jpg') }}')">
      <div class="slide-content">
        <h2 class="text-2xl font-bold mb-3">Dairy Product Processing</h2>
        <p class="text-lg max-w-2xl">Transforming raw milk into various dairy products with optimal resource allocation.</p>
      </div>
    </div>

    <div class="slide" style="background-image: url('{{ asset('images/dairy3.jpg') }}')">
      <div class="slide-content">
        <h2 class="text-2xl font-bold mb-3">Inventory Management</h2>
        <p class="text-lg max-w-2xl">Smart inventory control with First Expired, First Out (FEFO) product management.</p>
      </div>
    </div>

    <div class="slide" style="background-image: url('{{ asset('images/dairy4.jpg') }}')">
      <div class="slide-content">
        <h2 class="text-2xl font-bold mb-3">ML-Driven Analytics</h2>
        <p class="text-lg max-w-2xl">Demand prediction and customer segmentation using advanced machine learning algorithms.</p>
      </div>
    </div>

    <!-- Navigation arrows -->
    <div class="prev">&#10094;</div>
    <div class="next">&#10095;</div>

    <!-- Dots -->
    <div class="dots-container">
      <span class="dot dot-active" data-index="0"></span>
      <span class="dot" data-index="1"></span>
      <span class="dot" data-index="2"></span>
      <span class="dot" data-index="3"></span>
    </div>
  </div>

  <!-- System Description -->
  <div class="max-w-4xl mx-auto bg-white dark:bg-[#161615] p-8 md:p-10 rounded-xl shadow-lg">
    <h1 class="text-3xl font-bold mb-6 text-primary dark:text-primary-dark font-display">Dairy Supply Chain Management System</h1>

    <div class="mb-10">
      <h2 class="text-2xl font-semibold mb-4">System Overview</h2>
      <p class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed">
        The Dairy Supply Chain Management System (DSCMS) is designed to track and manage the movement of dairy products
        through the entire supply chain — from raw milk collection by dairy farmers, processing in factories,
        distribution to wholesalers, and finally to retailers.
      </p>
      <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
        Our system enhances operational transparency, streamlines logistics, supports data-driven decision making,
        and improves communication across the dairy supply chain.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
      <div class="bg-gray-50 dark:bg-[#1D1D1B] p-7 rounded-lg shadow-sm feature-card">
        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">Key Features</h3>
        <ul class="list-disc pl-5 space-y-2 text-gray-700 dark:text-gray-300">
          <li>Product tracking and supply chain monitoring</li>
          <li>Inventory management and order processing</li>
          <li>Role-specific dashboards and scheduled reporting</li>
          <li>Machine learning-driven analytics and demand prediction</li>
          <li>Vendor validation with identity verification</li>
          <li>Workforce distribution and optimization</li>
        </ul>
      </div>

      <div class="bg-gray-50 dark:bg-[#1D1D1B] p-7 rounded-lg shadow-sm feature-card">
        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">User Roles</h3>
        <ul class="list-disc pl-5 space-y-2 text-gray-700 dark:text-gray-300">
          <li><strong>Suppliers:</strong> Dairy farmers and raw material providers</li>
          <li><strong>Factory:</strong> Processing plants and manufacturers</li>
          <li><strong>Wholesalers:</strong> Bulk product distributors</li>
          <li><strong>Retailers:</strong> End-point sellers to consumers</li>
          <li><strong>Administrators:</strong> System managers and supervisors</li>
        </ul>
      </div>
    </div>

    <div class="flex flex-col md:flex-row gap-8 justify-between">
      <!-- Prospective Vendor Section -->
      <div class="bg-emerald-50 dark:bg-[#1D2A20] p-7 rounded-lg border border-emerald-100 dark:border-emerald-900 flex-1 feature-card">
        <h2 class="text-xl font-semibold text-emerald-800 dark:text-emerald-400 mb-4">Prospective Dairy Suppliers</h2>
        <p class="text-slate-700 dark:text-slate-300 mb-6 leading-relaxed">
          Are you a dairy farmer, ingredient provider, or packaging supplier interested in partnering with us?
        </p>
        <a href="{{route('vendor.apply')}}">
        <button id="applyVendorBtn" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-md transition-colors duration-200 shadow-sm hover:shadow-md btn-primary">
          Apply as a Vendor
        </button>
      </div>

      <!-- General New User Registration Section -->
      <div class="bg-gray-50 dark:bg-[#1D1D1B] p-7 rounded-lg border border-gray-200 dark:border-gray-800 flex-1 feature-card">
        <h2 class="text-xl font-semibold text-slate-700 dark:text-slate-300 mb-4">New Wholesalers or Retailers</h2>
        <p class="text-slate-700 dark:text-slate-300 mb-6 leading-relaxed">
          Join our network to streamline your dairy product procurement and distribution.
        </p>
        <button id="registerUserBtn" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-md transition-colors duration-200 shadow-sm hover:shadow-md btn-primary">
          Register as a New Customer
        </button>
      </div>
    </div>
  </div>
</main>

<!-- Footer -->
<footer class="bg-white dark:bg-[#161615] py-8 mt-16 border-t border-gray-200 dark:border-gray-800">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400">
    <div class="flex items-center justify-center mb-4">
      <span class="text-primary dark:text-primary-dark font-bold text-xl font-display">DSCMS</span>
    </div>
    <p>© 2025 DSCMS - Dairy Supply Chain Management System. All rights reserved.</p>
  </div>
</footer>

<!-- JavaScript -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle Functionality
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;

    // Check for saved theme preference or default to light mode
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
      html.classList.add('dark');
      themeToggle.classList.add('dark');
    }

    // Theme toggle event listener
    themeToggle.addEventListener('click', function() {
      html.classList.toggle('dark');
      themeToggle.classList.toggle('dark');

      // Save preference to localStorage
      if (html.classList.contains('dark')) {
        localStorage.setItem('theme', 'dark');
      } else {
        localStorage.setItem('theme', 'light');
      }
    });

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
      if (!localStorage.getItem('theme')) {
        if (e.matches) {
          html.classList.add('dark');
          themeToggle.classList.add('dark');
        } else {
          html.classList.remove('dark');
          themeToggle.classList.remove('dark');
        }
      }
    });

    // Slideshow Functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;

    // Next/previous controls
    document.querySelector('.next').addEventListener('click', () => {
      changeSlide(1);
    });

    document.querySelector('.prev').addEventListener('click', () => {
      changeSlide(-1);
    });

    // Dot controls
    dots.forEach(dot => {
      dot.addEventListener('click', function() {
        const slideIndex = parseInt(this.getAttribute('data-index'));
        showSlide(slideIndex);
      });
    });

    // Auto advance slides every 5 seconds
    setInterval(() => {
      changeSlide(1);
    }, 5000);

    function changeSlide(direction) {
      currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
      showSlide(currentSlide);
    }

    function showSlide(n) {
      // Hide all slides
      slides.forEach(slide => {
        slide.classList.remove('slide-active');
      });

      // Remove active state from all dots
      dots.forEach(dot => {
        dot.classList.remove('dot-active');
      });

      // Show the selected slide and dot
      slides[n].classList.add('slide-active');
      dots[n].classList.add('dot-active');

      currentSlide = n;
    }

    // Button event handlers
    document.getElementById('applyVendorBtn').addEventListener('click', function() {
      window.location.href = "{{ route('register') }}";
    });

    document.getElementById('registerUserBtn').addEventListener('click', function() {
      window.location.href = "{{ route('register') }}";
    });
  });
</script>
</body>
</html>
