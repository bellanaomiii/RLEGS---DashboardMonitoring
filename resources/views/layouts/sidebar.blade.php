<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Sidebar RLEGS</title>
        <link rel="stylesheet" href="{{ asset('sidebar/sidebarpage.css') }}">
        <link href="https://cdn.lineicons.com/5.0/lineicons.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <style>
            /* Styling untuk gambar avatar agar konsisten ukurannya */
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

            /* Style untuk logo dan user profile */
            .logo-avatar {
                margin-left: 1px;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <aside id="sidebar">
                <div class="d-flex">
                    <button id="toggle-btn" type="button">
                        <div class="avatar-container logo-avatar">
                            <img src="{{ asset('img/logo-outline.png') }}" alt="Logo">
                        </div>
                    </button>
                    <div class="sidebar-logo mt-4" style="margin-left: -18px;">
                        <a href="#">RLEGS</a>
                    </div>
                </div>
                <ul class="sidebar-nav">
                    <li class="sidebar-item">
                        <a href="{{ route('revenue.data') }}" class="sidebar-link">
                            <i class="lni lni-dashboard-square-1"></i><span>Data Revenue</span>
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
                        <a href="#" class="sidebar-link">
                            <i class="lni lni-user-multiple-4"></i><span>Top LOP</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link">
                            <i class="lni lni-chromecast"></i><span>Aosodomoro</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link">
                            <i class="lni lni-target-user"></i><span>EDK</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link">
                            <i class="lni lni-gear-1"></i><span>Settings</span>
                        </a>
                    </li>
                </ul>
                <div class="sidebar-footer">
                    <a href="{{ route('logout') }}" class="sidebar-link">
                        <i class="lni lni-exit"></i><span>Logout</span>
                    </a>
                </div>
            </aside>

            <div class="main p-0">
                <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                            <ul class="navbar-nav align-items-center">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="lni lni-bell-1 fs-4 mt-2"></i>
                                    </a>
                                </li>

                                <!-- Dropdown User dengan avatar yang di-crop dengan benar -->
                                <li class="nav-item dropdown ms-1">
                                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <div class="avatar-container">
                                            @if(Auth::user()->profile_image)
                                                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="{{ Auth::user()->name }}">
                                            @else
                                                <img src="{{ asset('img/profile.png') }}" alt="Default Profile">
                                            @endif
                                        </div>
                                        <span class="ms-2">{{ Auth::user()->name }}</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                                {{ __('Profile') }}
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item" href="#">Settings</a></li>
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
            </div>


        </div>
        <script src="sidebar/script.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

        <!-- Tambahan script untuk perbaikan dropdown yang tidak berfungsi -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fix untuk dropdown toggle yang tidak berfungsi di beberapa halaman
            var dropdownToggle = document.querySelectorAll('.dropdown-toggle');

            // Pastikan dropdown bekerja di semua halaman
            dropdownToggle.forEach(function(dropdown) {
                dropdown.addEventListener('click', function(e) {
                    // Hanya menjalankan manual jika built-in Bootstrap tidak bekerja
                    if (!this.nextElementSibling.classList.contains('show')) {
                        var dropdownMenu = this.nextElementSibling;

                        // Coba toggle dengan class manual
                        setTimeout(function() {
                            if (!dropdownMenu.classList.contains('show')) {
                                // Tutup dropdown lain yang mungkin terbuka
                                document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                                    menu.classList.remove('show');
                                });

                                // Tampilkan dropdown ini
                                dropdownMenu.classList.add('show');
                            }
                        }, 50);
                    }
                });
            });

            // Tutup dropdown saat klik di luar
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.nav-item.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                        menu.classList.remove('show');
                    });
                }
            });

            // Pastikan sidebar toggle juga berfungsi dengan baik
            const sidebarToggle = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                });
            }
        });
        </script>
    </body>
</html>