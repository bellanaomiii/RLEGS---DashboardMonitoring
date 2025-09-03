<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'RLEGS Dashboard')</title>

    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/5.0/lineicons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('sidebar/sidebarpage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        /* Avatar styling */
        .avatar-container {
            width: 35px;
            height: 35px;
            overflow: hidden;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .logo-avatar {
            margin-left: 1px;
        }

        .nav-item.dropdown .nav-link {
            display: flex;
            align-items: center;
        }

        .nav-link .avatar-container {
            display: inline-flex;
            vertical-align: middle;
        }

        /* Desktop Layout */
        @media (min-width: 769px) {
            .navbar {
                position: fixed !important;
                top: 0 !important;
                left: 80px !important;
                right: 0 !important;
                width: calc(100% - 80px) !important;
                z-index: 1020 !important;
                height: 60px !important;
                padding: 0 15px !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: flex-end !important;
                background: white !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
                border-bottom: 1px solid #e9ecef !important;
            }

            .navbar .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            #sidebar {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 80px !important;
                height: 100vh !important;
                background: #0e223e !important;
                z-index: 1030 !important;
            }

            .main {
                margin-left: 30px !important;
                padding-top: 60px !important;
                width: calc(100% - 80px) !important;
            }

            .content-wrapper {
                padding: 15px;
                padding-top: 15px;
                width: 100%;
            }
        }

        .content-wrapper {
            padding: 15px;
            padding-top: 65px;
            width: 100%;
        }

        /* ========== SIDEBAR MOBILE FIXES ========== */
        @media (max-width: 768px) {
            /* Hide sidebar by default but keep it accessible */
            #sidebar {
                position: fixed !important;
                top: 0 !important;
                left: -100% !important;
                width: 280px !important;
                height: 100vh !important;
                z-index: 1040 !important;
                background: #0e223e !important;
                transition: left 0.3s ease !important;
                box-shadow: 2px 0 15px rgba(0,0,0,0.1) !important;
                overflow-y: auto !important;
                border-right: 1px solid #1a365d !important;
                display: block !important;
                visibility: visible !important;
            }

            /* Show sidebar when active */
            #sidebar.show {
                left: 0 !important;
            }

            /* Sidebar header styling */
            #sidebar .d-flex {
                padding: 15px !important;
                background: #0e223e !important;
                border-bottom: 1px solid #1a365d !important;
                display: flex !important;
                align-items: center !important;
            }

            #sidebar .sidebar-logo {
                margin-left: 10px !important;
            }

            #sidebar .sidebar-logo a {
                color: white !important;
                font-weight: 600 !important;
                font-size: 18px !important;
                text-decoration: none !important;
            }

            /* Sidebar navigation */
            #sidebar .sidebar-nav {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            #sidebar .sidebar-item {
                margin: 0 !important;
            }

            #sidebar .sidebar-link {
                display: flex !important;
                align-items: center !important;
                padding: 15px 20px !important;
                color: white !important;
                text-decoration: none !important;
                transition: all 0.2s ease !important;
                border-bottom: 1px solid #1a365d !important;
                text-align: left !important;
                justify-content: flex-start !important;
                position: relative !important;
            }

            #sidebar .sidebar-link:hover {
                background: #1a365d !important;
                color: white !important;
            }

            #sidebar .sidebar-link i {
                margin-right: 12px !important;
                width: 20px !important;
                text-align: center !important;
                font-size: 16px !important;
                color: white !important;
                flex-shrink: 0 !important;
            }

            #sidebar .sidebar-link span {
                display: inline !important;
                color: white !important;
                text-align: left !important;
                flex-grow: 1 !important;
                font-weight: 400 !important;
            }

            /* Dropdown arrow styling for mobile */
            #sidebar .sidebar-link.has-dropdown::after {
                content: '\f107';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                position: absolute;
                right: 20px;
                transition: transform 0.2s ease;
                color: white;
            }

            #sidebar .sidebar-link.has-dropdown[aria-expanded="true"]::after {
                transform: rotate(180deg);
            }

            /* Dropdown styling */
            #sidebar .sidebar-dropdown {
                background: #1a365d !important;
                margin: 0 !important;
                padding: 0 !important;
                border-top: 1px solid #2d4a66 !important;
                display: none;
            }

            #sidebar .sidebar-dropdown.show {
                display: block !important;
            }

            #sidebar .sidebar-dropdown .sidebar-link {
                padding-left: 50px !important;
                border-bottom: 1px solid #2d4a66 !important;
                color: white !important;
                font-size: 14px !important;
            }

            #sidebar .sidebar-dropdown .sidebar-link:hover {
                background: #2d4a66 !important;
            }

            #sidebar .sidebar-dropdown .sidebar-link i {
                font-size: 14px !important;
                margin-right: 10px !important;
            }

            /* Footer styling */
            #sidebar .sidebar-footer {
                margin-top: auto !important;
                border-top: 1px solid #1a365d !important;
                background: #0e223e !important;
            }

            #sidebar .sidebar-footer .sidebar-link {
                border-bottom: none !important;
                color: white !important;
            }

            #sidebar .sidebar-footer .sidebar-link:hover {
                background: #1a365d !important;
            }

            /* Toggle button styling */
            #toggle-btn {
                background: none !important;
                border: none !important;
                padding: 5px !important;
            }

            /* Overlay for when sidebar is open */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0,0,0,0.5);
                z-index: 1035;
                display: none;
                backdrop-filter: blur(2px);
            }

            .sidebar-overlay.show {
                display: block;
            }

            /* Force wrapper to not accommodate sidebar */
            .wrapper {
                display: flex !important;
                padding-left: 0 !important;
                margin-left: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
                overflow-x: hidden !important;
            }

            /* Force main content to take full viewport width */
            .main {
                margin-left: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
                min-width: 100vw !important;
                flex: 1 !important;
                position: relative !important;
                padding-top: 10px !important;
            }

            /* Content wrapper full width */
            .content-wrapper {
                padding: 10px 8px !important;
                padding-top: 10px !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }

            /* Fixed navbar with hamburger */
            .navbar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
                z-index: 1030 !important;
                height: 60px !important;
                padding: 0 10px !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                background: white !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
                border-bottom: 1px solid #e9ecef !important;
            }

            /* Hamburger menu button */
            .mobile-menu-btn {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 40px !important;
                height: 40px !important;
                border: none !important;
                background: #0e223e !important;
                color: white !important;
                border-radius: 8px !important;
                font-size: 16px !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
            }

            .mobile-menu-btn:hover {
                background: #1a365d !important;
                transform: scale(1.05) !important;
            }

            /* Navbar content */
            .navbar .container-fluid {
                padding: 0 !important;
                width: 100% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }

            /* Left side with hamburger */
            .navbar-left {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }

            /* Right side with profile */
            .navbar-right {
                display: flex !important;
                align-items: center !important;
            }

            /* Profile dropdown mobile */
            .nav-item.dropdown .nav-link {
                padding: 6px 10px !important;
                border-radius: 20px !important;
                transition: background 0.2s ease !important;
                color: #2d3748 !important;
                text-decoration: none !important;
            }

            .nav-item.dropdown .nav-link:hover {
                background: #f8f9fa !important;
            }

            /* Avatar container mobile */
            .avatar-container {
                width: 32px !important;
                height: 32px !important;
                margin-right: 6px !important;
            }

            /* Profile name */
            .nav-link span {
                font-size: 14px !important;
                font-weight: 500 !important;
                color: #2d3748 !important;
            }

            /* Dropdown menu mobile */
            .dropdown-menu {
                right: 0 !important;
                left: auto !important;
                margin-top: 8px !important;
                border: none !important;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
                border-radius: 8px !important;
                min-width: 180px !important;
            }

            /* Brand/Logo mobile */
            .navbar-brand {
                font-size: 1.1rem !important;
                font-weight: 700 !important;
                color: #0e223e !important;
                text-decoration: none !important;
            }

            /* Body and HTML adjustments */
            body {
                overflow-x: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
                padding-top: 60px !important;
            }

            html {
                overflow-x: hidden !important;
            }

            /* Hide original navbar elements that might interfere */
            .navbar-toggler {
                display: none !important;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.0rem !important;
            }

            #sidebar {
                width: 260px !important;
            }

            .avatar-container {
                width: 30px !important;
                height: 30px !important;
            }

            .nav-link span {
                font-size: 13px !important;
            }
        }
    </style>

    @yield('styles')

    <!-- Core JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="d-flex">
                <button id="toggle-btn" type="button">
                    <img src="{{ asset('img/logo-outline.png') }}" class="avatar rounded-circle" alt="Logo" width="35" height="35" style="margin-left: 1px">
                </button>
                <div class="sidebar-logo">
                    <a href="#">RLEGS</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">
                        <i class="lni lni-dashboard-square-1"></i><span>Overview Data</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('revenue.index') }}" class="sidebar-link">
                        <i class="lni lni-file-pencil"></i><span>Data Revenue</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link has-dropdown collapsed" data-bs-toggle="collapse" data-bs-target="#performance" aria-expanded="false" aria-controls="performance">
                        <i class="lni lni-bar-chart-dollar"></i><span>Performansi</span>
                    </a>
                    <ul id="performance" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="{{ url('witel-perform') }}" class="sidebar-link">
                                <i class="lni lni-react"></i><span>Witel</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ url('leaderboardAM') }}" class="sidebar-link">
                                <i class="lni lni-hierarchy-1"></i><span>Leaderboard AM</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('monitoring-LOP') }}" class="sidebar-link">
                        <i class="lni lni-user-multiple-4"></i><span>Top LOP</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="{{ route('profile.edit') }}" class="sidebar-link">
                    <i class="lni lni-gear-1"></i><span>Settings</span>
                </a>
            </div>
            <div class="sidebar-footer">
                <a href="{{ route('logout') }}" class="sidebar-link">
                    <i class="lni lni-exit"></i><span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main p-0">
            <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                        <ul class="navbar-nav align-items-center">
                            <li class="nav-item dropdown ms-1">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="avatar-container me-2">
                                        @if(Auth::user()->profile_image)
                                            <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="{{ Auth::user()->name }}">
                                        @else
                                            <img src="{{ asset('img/profile.png') }}" alt="Default Profile">
                                        @endif
                                    </div>
                                    <span>{{ Auth::user()->name }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            {{ __('Settings') }}
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                {{ __('Log Out') }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            @yield('content')
        </div>
    </div>

    <!-- JavaScript -->
    <script src="{{ asset('sidebar/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

    <!-- Mobile Responsive JavaScript -->
    <script>
        // Mobile responsive enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Create mobile navbar structure
            createMobileNavbar();

            // Initialize sidebar dropdowns
            initializeSidebarDropdowns();

            // Handle screen orientation changes
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 100);
            });

            // Prevent horizontal scroll on mobile
            function preventHorizontalScroll() {
                if (window.innerWidth <= 768) {
                    document.body.style.overflowX = 'hidden';
                    const wrapper = document.querySelector('.wrapper');
                    if (wrapper) {
                        wrapper.style.overflowX = 'hidden';
                    }
                }
            }

            // Mobile touch improvements
            if ('ontouchstart' in window) {
                document.body.classList.add('touch-device');
            }

            // Run on load and resize
            preventHorizontalScroll();
            window.addEventListener('resize', function() {
                preventHorizontalScroll();
                if (window.innerWidth > 768) {
                    hideMobileElements();
                } else {
                    createMobileNavbar();
                    initializeSidebarDropdowns();
                }
            });
        });

        // Initialize sidebar dropdowns functionality
        function initializeSidebarDropdowns() {
            const dropdownLinks = document.querySelectorAll('#sidebar .sidebar-link.has-dropdown');

            dropdownLinks.forEach(function(link) {
                // Remove existing event listeners to avoid duplicates
                link.removeEventListener('click', handleDropdownClick);

                // Add new event listener
                link.addEventListener('click', handleDropdownClick);
            });
        }

        // Handle dropdown click
        function handleDropdownClick(e) {
            e.preventDefault();

            const link = e.currentTarget;
            const targetId = link.getAttribute('data-bs-target');
            const target = document.querySelector(targetId);

            if (target) {
                const isExpanded = link.getAttribute('aria-expanded') === 'true';

                // Close all other dropdowns in sidebar
                const allDropdowns = document.querySelectorAll('#sidebar .sidebar-dropdown');
                const allDropdownLinks = document.querySelectorAll('#sidebar .sidebar-link.has-dropdown');

                allDropdowns.forEach(function(dropdown) {
                    if (dropdown !== target) {
                        dropdown.classList.remove('show');
                        dropdown.style.display = 'none';
                    }
                });

                allDropdownLinks.forEach(function(dropdownLink) {
                    if (dropdownLink !== link) {
                        dropdownLink.setAttribute('aria-expanded', 'false');
                        dropdownLink.classList.add('collapsed');
                    }
                });

                // Toggle current dropdown
                if (isExpanded) {
                    // Close
                    target.classList.remove('show');
                    target.style.display = 'none';
                    link.setAttribute('aria-expanded', 'false');
                    link.classList.add('collapsed');
                } else {
                    // Open
                    target.classList.add('show');
                    target.style.display = 'block';
                    link.setAttribute('aria-expanded', 'true');
                    link.classList.remove('collapsed');
                }
            }
        }

        // Create mobile navbar structure
        function createMobileNavbar() {
            if (window.innerWidth <= 768) {
                let navbar = document.querySelector('.navbar');

                if (!navbar) {
                    navbar = document.createElement('nav');
                    navbar.className = 'navbar navbar-expand-lg navbar-light bg-white';
                    document.body.insertBefore(navbar, document.body.firstChild);
                }

                const containerFluid = navbar.querySelector('.container-fluid') || document.createElement('div');
                containerFluid.className = 'container-fluid';

                if (!navbar.contains(containerFluid)) {
                    navbar.appendChild(containerFluid);
                }

                // Clear existing content
                containerFluid.innerHTML = '';

                // Create left side (hamburger + brand)
                const navbarLeft = document.createElement('div');
                navbarLeft.className = 'navbar-left';

                // Hamburger button
                const hamburgerBtn = document.createElement('button');
                hamburgerBtn.className = 'mobile-menu-btn';
                hamburgerBtn.innerHTML = '<i class="fas fa-bars"></i>';
                hamburgerBtn.onclick = toggleSidebar;

                // Brand
                const brand = document.createElement('a');
                brand.className = 'navbar-brand';
                brand.href = '#';
                brand.textContent = 'RLEGS Dashboard';

                navbarLeft.appendChild(hamburgerBtn);
                navbarLeft.appendChild(brand);

                // Create right side (profile)
                const navbarRight = document.createElement('div');
                navbarRight.className = 'navbar-right';

                // Profile dropdown
                const profileDropdown = document.createElement('div');
                profileDropdown.className = 'nav-item dropdown';
                profileDropdown.innerHTML = `
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-container">
                            <img src="{{ asset('img/profile.png') }}" alt="Profile">
                        </div>
                        <span>{{ Auth::user()->name ?? 'Admin' }}</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                `;

                navbarRight.appendChild(profileDropdown);

                // Append to container
                containerFluid.appendChild(navbarLeft);
                containerFluid.appendChild(navbarRight);

                // Create overlay for sidebar
                createSidebarOverlay();
            }
        }

        // Create sidebar overlay
        function createSidebarOverlay() {
            let overlay = document.querySelector('.sidebar-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.onclick = closeSidebar;
                document.body.appendChild(overlay);
            }
        }

        // Toggle sidebar function
        function toggleSidebar() {
            const sidebar = document.querySelector('#sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (sidebar && overlay) {
                const isOpen = sidebar.classList.contains('show');

                if (isOpen) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }
        }

        // Open sidebar
        function openSidebar() {
            const sidebar = document.querySelector('#sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (sidebar && overlay) {
                sidebar.classList.add('show');
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';

                // Re-initialize dropdown functionality when sidebar opens
                initializeSidebarDropdowns();

                // Add click handlers to sidebar links for mobile
                const sidebarLinks = sidebar.querySelectorAll('.sidebar-link:not(.has-dropdown)');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            setTimeout(() => {
                                closeSidebar();
                            }, 200);
                        }
                    });
                });
            }
        }

        // Close sidebar
        function closeSidebar() {
            const sidebar = document.querySelector('#sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (sidebar && overlay) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        // Hide mobile elements on desktop
        function hideMobileElements() {
            const overlay = document.querySelector('.sidebar-overlay');
            if (overlay) {
                overlay.remove();
            }

            const sidebar = document.querySelector('#sidebar');
            if (sidebar) {
                sidebar.classList.remove('show');
            }

            document.body.style.overflow = '';
        }

        // Handle clicks outside sidebar to close it
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('#sidebar');
                const hamburger = document.querySelector('.mobile-menu-btn');

                if (sidebar && sidebar.classList.contains('show')) {
                    if (!sidebar.contains(event.target) && hamburger && !hamburger.contains(event.target)) {
                        closeSidebar();
                    }
                }
            }
        });

        // Handle swipe gestures for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', function(event) {
            touchStartX = event.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', function(event) {
            touchEndX = event.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (window.innerWidth <= 768) {
                const swipeDistance = touchEndX - touchStartX;
                const sidebar = document.querySelector('#sidebar');

                // Swipe right to open sidebar
                if (swipeDistance > 50 && touchStartX < 50) {
                    openSidebar();
                }

                // Swipe left to close sidebar
                if (swipeDistance < -50 && sidebar && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            }
        }

        // Adjust viewport for better mobile rendering
        function adjustViewportForMobile() {
            const viewport = document.querySelector('meta[name=viewport]');
            if (viewport && window.innerWidth <= 768) {
                viewport.setAttribute('content',
                    'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'
                );
            }
        }

        // Run viewport adjustment
        adjustViewportForMobile();
        window.addEventListener('resize', adjustViewportForMobile);

        // Accessibility improvements
        document.addEventListener('keydown', function(e) {
            // Close sidebar with Escape key
            if (e.key === 'Escape') {
                const sidebar = document.querySelector('#sidebar');
                if (sidebar && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            }

            // Handle Enter/Space for dropdown links
            if (e.key === 'Enter' || e.key === ' ') {
                const target = e.target;
                if (target.classList.contains('has-dropdown')) {
                    e.preventDefault();
                    handleDropdownClick(e);
                }
            }
        });

        // Enhanced Bootstrap dropdown initialization for mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // Fix for mobile dropdown menu positioning
            document.addEventListener('show.bs.dropdown', function (e) {
                const dropdown = e.target.closest('.dropdown');
                if (dropdown && window.innerWidth <= 768) {
                    setTimeout(function() {
                        const menu = dropdown.querySelector('.dropdown-menu');
                        if (menu) {
                            const rect = dropdown.getBoundingClientRect();
                            const viewportWidth = window.innerWidth;

                            if (rect.right + menu.offsetWidth > viewportWidth) {
                                menu.classList.add('dropdown-menu-end');
                            }
                        }
                    }, 10);
                }
            });
        });

        // Performance optimization for mobile
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                // Recalculate layout after resize
                if (window.innerWidth <= 768) {
                    createMobileNavbar();
                    initializeSidebarDropdowns();
                } else {
                    hideMobileElements();
                }
            }, 150);
        });

        // Focus management for sidebar
        function manageFocus() {
            const sidebar = document.querySelector('#sidebar');
            if (!sidebar) return;

            const firstFocusableElement = sidebar.querySelector('a, button');
            const lastFocusableElement = sidebar.querySelector('.sidebar-footer .sidebar-link:last-child');

            if (sidebar.classList.contains('show')) {
                if (firstFocusableElement) {
                    firstFocusableElement.focus();
                }

                // Trap focus within sidebar
                sidebar.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        if (e.shiftKey) {
                            if (document.activeElement === firstFocusableElement) {
                                if (lastFocusableElement) {
                                    lastFocusableElement.focus();
                                    e.preventDefault();
                                }
                            }
                        } else {
                            if (document.activeElement === lastFocusableElement) {
                                if (firstFocusableElement) {
                                    firstFocusableElement.focus();
                                    e.preventDefault();
                                }
                            }
                        }
                    }
                });
            }
        }

        // Smooth animations for sidebar transitions
        function addSmoothTransitions() {
            const sidebar = document.querySelector('#sidebar');
            if (sidebar && window.innerWidth <= 768) {
                sidebar.style.transition = 'left 0.3s cubic-bezier(0.4, 0, 0.2, 1)';

                // Add bounce effect when opening
                sidebar.addEventListener('transitionend', function() {
                    if (sidebar.classList.contains('show')) {
                        sidebar.style.transform = 'translateX(2px)';
                        setTimeout(() => {
                            sidebar.style.transform = 'translateX(0)';
                        }, 100);
                    }
                });
            }
        }

        // Initialize smooth transitions
        addSmoothTransitions();

        // Optimize for mobile performance
        function optimizeForMobile() {
            if (window.innerWidth <= 768) {
                // Reduce animation complexity on mobile
                document.body.classList.add('mobile-optimized');

                // Disable hover effects on touch devices
                if ('ontouchstart' in window) {
                    document.body.classList.add('touch-device');
                }

                // Optimize scrolling
                document.body.style.webkitOverflowScrolling = 'touch';
            }
        }

        optimizeForMobile();

        // Initialize everything when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initializeSidebarDropdowns();
                createMobileNavbar();
                addSmoothTransitions();
                manageFocus();
            });
        } else {
            initializeSidebarDropdowns();
            createMobileNavbar();
            addSmoothTransitions();
            manageFocus();
        }

        // Debug function for mobile testing
        function debugMobile() {
            console.log('Screen width:', window.innerWidth);
            console.log('Sidebar visible:', document.querySelector('#sidebar')?.classList.contains('show'));
            console.log('Mobile navbar created:', !!document.querySelector('.mobile-menu-btn'));
            console.log('Dropdowns initialized:', document.querySelectorAll('#sidebar .sidebar-link.has-dropdown').length);
        }

        // Expose debug function globally for testing
        window.debugMobile = debugMobile;

        // Clean up function
        function cleanup() {
            // Remove event listeners when not needed
            window.removeEventListener('resize', preventHorizontalScroll);
            document.removeEventListener('touchstart', function() {});
            document.removeEventListener('touchend', function() {});
        }

        // Service worker registration for offline functionality (optional)
        if ('serviceWorker' in navigator && window.innerWidth <= 768) {
            navigator.serviceWorker.register('/sw.js').catch(function(error) {
                console.log('ServiceWorker registration failed:', error);
            });
        }
    </script>

    @yield('scripts')
</body>
</html>