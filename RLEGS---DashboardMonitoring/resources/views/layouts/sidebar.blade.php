<!-- sidebar-partial.blade.php (tanpa div.main) -->
<aside id="sidebar">
    <div class="d-flex">
        <button id="toggle-btn" type="button">
            <img src="{{ asset('img/logo-outline.png') }}" class="avatar rounded-circle logo-avatar" alt="Logo" width="35" height="35">
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

<!-- Navbar (tanpa div.main) -->
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

                <!-- Dropdown User -->
                <li class="nav-item dropdown ms-1">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
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