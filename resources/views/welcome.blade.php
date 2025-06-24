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
      background: #0d2244;
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
      background: #091c37;
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

    /* Add AOS base styles if not using CDN for CSS, or to override */
    /* [data-aos] { transition-property: transform, opacity; } */

    .slideshow-container {
      position: relative;
      max-width: 100%; /* Or your desired max-width */
      margin: auto;
      overflow: hidden; /* Important for slide transitions */
      border-radius: 0.75rem; /* Equivalent to rounded-xl */
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-lg */
      height: 450px; /* Set height on container if slides are absolute */
    }

    .slide {
      width: 100%;
      height: 100%; /* Make slide fill the container height */
      background-size: cover;
      background-position: center;
      position: absolute;
      top: 0;
      left: 0;
      opacity: 0;
      visibility: hidden;
      /* Transition for fade-out: opacity animates, then visibility changes */
      transition: opacity 0.7s ease-in-out, visibility 0s linear 0.7s;
      z-index: 0; /* Default z-index for inactive slides */
    }

    .slide-active {
      opacity: 1;
      visibility: visible;
      /* Transition for fade-in: opacity animates. Visibility changes instantly (no delay needed here) */
      transition: opacity 0.7s ease-in-out;
      z-index: 1; /* Active slide on top */
    }
  </style>
  <!-- AOS CSS -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>

<body class="bg-gradient-to-br from-purple-50 via-indigo-50 to-sky-100 dark:bg-gradient-to-br dark:from-slate-900 dark:via-indigo-950 dark:to-purple-950 text-[#1b1b18] dark:text-[#e5e5e5] font-sans flex flex-col min-h-screen transition-colors duration-300">
<!-- Header with logo and login buttons -->
<header class="w-full px-6 py-5 lg:px-8 lg:py-6 flex items-center justify-between bg-white/80 dark:bg-[#0d1b2a]/30 backdrop-blur-sm sticky top-0 z-50 shadow-sm">
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
          <a href="{{ route('dashboard') }}"
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
  <div class="max-w-4xl mx-auto bg-white dark:bg-[#0d1b2a] p-8 md:p-10 rounded-xl shadow-lg">
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
      <div class="bg-gray-50 dark:bg-[#0d1b2a] p-7 rounded-lg shadow-sm feature-card transition-all duration-300 ease-in-out hover:scale-[1.02] hover:shadow-xl" data-aos="fade-right" data-aos-delay="100">
        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6 mr-2 text-primary dark:text-primary-dark">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
          </svg>
          Key Features
        </h3>
        <ul class="space-y-3 text-gray-700 dark:text-gray-300">
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Product tracking and supply chain monitoring</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Inventory management and order processing</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Role-specific dashboards and scheduled reporting</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Machine learning-driven analytics and demand prediction</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Vendor validation with identity verification</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Workforce distribution and optimization</span>
          </li>
        </ul>
      </div>

      <div class="bg-gray-50 dark:bg-[#0d1b2a] p-7 rounded-lg shadow-sm feature-card transition-all duration-300 ease-in-out hover:scale-[1.02] hover:shadow-xl" data-aos="fade-left" data-aos-delay="200">
        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6 mr-2 text-primary dark:text-primary-dark">
            <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
          </svg>
          User Roles
        </h3>
        <ul class="space-y-3 text-gray-700 dark:text-gray-300">
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            <span><strong>Suppliers:</strong> Dairy farmers and raw material providers</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V3A2.25 2.25 0 0019.5 0H4.5A2.25 2.25 0 002.25 3z" /></svg>
            <span><strong>Factory:</strong> Processing plants and manufacturers</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
            <span><strong>Wholesalers:</strong> Bulk product distributors</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5A2.25 2.25 0 0011.25 11.25H6.75A2.25 2.25 0 004.5 13.5V21M6.75 6.75h.75v.75h-.75v-.75zM6.75 9.75h.75v.75h-.75v-.75zM6.75 12.75h.75v.75h-.75v-.75zM9.75 6.75h.75v.75h-.75v-.75zM9.75 9.75h.75v.75h-.75v-.75zM9.75 12.75h.75v.75h-.75v-.75zM12.75 6.75h.75v.75h-.75v-.75zM12.75 9.75h.75v.75h-.75v-.75zM12.75 12.75h.75v.75h-.75v-.75zM15.75 6.75h.75v.75h-.75v-.75zM15.75 9.75h.75v.75h-.75v-.75zM15.75 12.75h.75v.75h-.75v-.75z" /></svg>
            <span><strong>Retailers:</strong> End-point sellers to consumers</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 mt-1 text-primary dark:text-primary-dark flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
            <span><strong>Administrators:</strong> System managers and supervisors</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="flex flex-col md:flex-row gap-8 justify-between">
      <!-- Prospective Vendor Section -->
      <div class="bg-emerald-50 dark:bg-[#1D2A20] p-7 rounded-lg border border-emerald-100 dark:border-[#8c57ff] flex-1 feature-card transition-all duration-300 ease-in-out hover:scale-[1.02] hover:shadow-xl" data-aos="zoom-in-up" data-aos-delay="300">
        <h2 class="text-xl font-semibold text-emerald-800 dark:text-emerald-400 mb-4">Prospective Dairy Suppliers</h2>
        <p class="text-slate-700 dark:text-slate-300 mb-6 leading-relaxed">
          Are you a dairy farmer, ingredient provider, or packaging supplier interested in partnering with us?
        </p>
        <button id="applyVendorBtn" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-md transition-colors duration-200 shadow-sm hover:shadow-md btn-primary">
          Apply as a Vendor
        </button>
      </div>

      <!-- General New User Registration Section -->
      <div class="bg-gray-50 dark:bg-[#0d1b2a] p-7 rounded-lg border border-gray-200 dark:border-[#8c57ff] flex-1 feature-card transition-all duration-300 ease-in-out hover:scale-[1.02] hover:shadow-xl" data-aos="zoom-in-up" data-aos-delay="400">
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
<footer class="bg-white dark:bg-[#0d1b2a] py-8 mt-16 border-t border-gray-200 dark:border-[#8c57ff]" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400">
    <div class="flex items-center justify-center mb-4">
      <span class="text-primary dark:text-primary-dark font-bold text-xl font-display">DSCMS</span>
    </div>
    <p>© 2025 DSCMS - Dairy Supply Chain Management System. All rights reserved.</p>
  </div>
</footer>

<!-- JavaScript -->
<!-- AOS JS -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    AOS.init({
      duration: 700, // values from 0 to 3000, with step 50ms
      easing: 'ease-in-out', // default easing for AOS animations
      once: true, // whether animation should happen only once - while scrolling down
      mirror: false, // whether elements should animate out while scrolling past them
    });

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

    // Ensure the first slide is active on load by calling showSlide
    if (totalSlides > 0) {
      showSlide(currentSlide); // currentSlide is already 0
    }

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
      const newIndex = (n + totalSlides) % totalSlides;

      // Remove active class from all slides
      slides.forEach((slide, index) => {
        if (index === newIndex) {
          slide.classList.add('slide-active');
        } else {
          slide.classList.remove('slide-active');
        }
      });

      // Remove active state from all dots and add to current
      dots.forEach((dot, index) => {
        if (index === newIndex) {
          dot.classList.add('dot-active');
        } else {
          dot.classList.remove('dot-active');
        }
      });

      currentSlide = newIndex;
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
