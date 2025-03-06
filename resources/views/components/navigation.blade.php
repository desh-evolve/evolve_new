<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" height="auto">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm-light.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-light.png') }}" alt="" height="auto">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>

                <!-- Main Navigation -->
                <ul class="navbar-nav" id="navbar-nav">

                    <li class="menu-title"><span>Menu</span></li>

                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#reports" data-bs-toggle="collapse" role="button"
                            aria-expanded="false">
                            <i class="ri-bar-chart-line"></i> <span>Reports</span>
                        </a>
                        <div class="collapse menu-dropdown" id="reports">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="#" class="nav-link">EPF Report</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Employee Report</a></li>
                            </ul>
                        </div>
                    </li>

                    
                    <li class="nav-item"><a href="#" class="nav-link">Permission Groups</a></li>

                    <!-- Logout -->
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="nav-link menu-link" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="ri-logout-box-line"></i> <span data-key="t-logout">Log Out</span>
                            </a>
                        </form>
                    </li>

                </ul>
            </div>
            <!-- Sidebar -->
        </div>
    </div>
</div>

<div class="sidebar-background"></div>
</div>
<!-- ========== End App Menu ========== -->
