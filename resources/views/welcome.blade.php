<!-- filepath: c:\xampp\htdocs\DSCMS\resources\views\welcome.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>DSCMS - Dairy Supply Chain Management System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CDN (temporary solution until your build process is fixed) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: "#F53003",
                            "primary-dark": "#F61500",
                            "neutral-50": "#FDFDFC",
                            "neutral-900": "#0a0a0a",
                        },
                        fontFamily: {
                            sans: ['Inter', 'system-ui', 'sans-serif'],
                            display: ['Instrument Sans', 'system-ui', 'sans-serif'],
                        }
                    },
                }
            }
        </script>

        <!-- Additional styles -->
        <style>
            /* Slideshow enhancements */
            .slideshow-container {
                position: relative;
                max-width: 1200px;
                margin: 0 auto;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .slide {
                display: none;
                width: 100%;
                height: 500px;
                background-size: cover;
                background-position: center;
                border-radius: 12px;
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .slide::before {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 250px;
                background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
            }

            .slide-active {
                display: block;
                animation: fadeIn 0.8s;
            }

            @keyframes fadeIn {
                from { opacity: 0.4; }
                to { opacity: 1; }
            }

            .slide-content {
                position: absolute;
                bottom: 0;
                width: 100%;
                padding: 30px;
                color: white;
                z-index: 2;
            }

            /* Navigation buttons */
            .prev, .next {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 24px;
                background-color: rgba(0, 0, 0, 0.4);
                border-radius: 50%;
                cursor: pointer;
                z-index: 10;
                transition: all 0.3s ease;
                opacity: 0.7;
            }

            .prev:hover, .next:hover {
                background-color: rgba(245, 48, 3, 0.8);
                opacity: 1;
            }

            .prev { left: 20px; }
            .next { right: 20px; }

            /* Dots */
            .dots-container {
                text-align: center;
                margin-top: 20px;
            }

            .dot {
                height: 12px;
                width: 12px;
                margin: 0 5px;
                background-color: #bbb;
                border-radius: 50%;
                display: inline-block;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .dot:hover {
                background-color: #999;
            }

            .dot-active {
                background-color: #F53003;
                transform: scale(1.2);
            }

            /* Card hover effects */
            .feature-card {
                transition: all 0.3s ease;
            }

            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }

            /* Custom button effects */
            .btn-primary {
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .btn-primary:after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 5px;
                height: 5px;
                background: rgba(255, 255, 255, 0.5);
                opacity: 0;
                border-radius: 100%;
                transform: scale(1, 1) translate(-50%);
                transform-origin: 50% 50%;
            }

            .btn-primary:hover:after {
                animation: ripple 1s ease-out;
            }

            @keyframes ripple {
                0% {
                    transform: scale(0, 0);
                    opacity: 0.5;
                }
                100% {
                    transform: scale(20, 20);
                    opacity: 0;
                }
            }
        </style>
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] font-sans flex flex-col min-h-screen">
        <!-- Header with logo and login buttons -->
        <header class="w-full px-6 py-5 lg:px-8 lg:py-6 flex items-center justify-between bg-white/80 dark:bg-black/30 backdrop-blur-sm sticky top-0 z-50 shadow-sm">
            <!-- Logo -->
            <div class="flex items-center">
                <span class="text-primary dark:text-primary-dark font-bold text-3xl font-display tracking-tight">DSCMS</span>
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 hidden sm:inline-block">Dairy Supply Chain Management System</span>
            </div>

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
                <div class="slide slide-active" style="background-image: url('https://images.unsplash.com/photo-1595514535215-6481440873e9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80')">
                    <div class="slide-content">
                        <h2 class="text-2xl font-bold mb-3">Milk Collection & Quality Testing</h2>
                        <p class="text-lg max-w-2xl">Ensuring high-quality raw milk through automated quality monitoring systems.</p>
                    </div>
                </div>

                <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1515282147087-92d2ebc6467c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80')">
                    <div class="slide-content">
                        <h2 class="text-2xl font-bold mb-3">Dairy Product Processing</h2>
                        <p class="text-lg max-w-2xl">Transforming raw milk into various dairy products with optimal resource allocation.</p>
                    </div>
                </div>

                <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1581953732364-6109dad1a8c9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80')">
                    <div class="slide-content">
                        <h2 class="text-2xl font-bold mb-3">Inventory Management</h2>
                        <p class="text-lg max-w-2xl">Smart inventory control with First Expired, First Out (FEFO) product management.</p>
                    </div>
                </div>

                <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1607004468138-e7e23ea26947?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80')">
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

        <!-- Slideshow JavaScript -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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
                    window.location.href = "{{ route('login') }}";
                });

                document.getElementById('registerUserBtn').addEventListener('click', function() {
                    window.location.href = "{{ route('login') }}";
                });
            });
        </script>
    </body>
</html>
