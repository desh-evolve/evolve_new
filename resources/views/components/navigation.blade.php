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

                    <!-- Attendance -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#attendance" data-bs-toggle="collapse" role="button"
                            aria-expanded="false">
                            <i class="ri-bar-chart-line"></i> <span>Attendance</span>
                        </a>
                        <div class="collapse menu-dropdown" id="attendance">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="#" class="nav-link">My Timesheet</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Punches</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Mass Punch</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Requests</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Apply Leaves</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Leaves Cover View</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Leaves Supervisor Approval</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Leaves Confirmation Report</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Accruals</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Pay Slips</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Schedule -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#schedule" data-bs-toggle="collapse" role="button"
                            aria-expanded="false">
                            <i class="ri-bar-chart-line"></i> <span>Schedule</span>
                        </a>
                        <div class="collapse menu-dropdown" id="schedule">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="#" class="nav-link">My Schedule</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Scheduled Shifts</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Mass Schedule</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Recurring Schedule</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Recurring Schedule Templates</a></li>
                            </ul>
                        </div>
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

                    <!-- Company -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#company" data-bs-toggle="collapse" role="button"
                            aria-expanded="false">
                            <i class="ri-bar-chart-line"></i> <span>Company</span>
                        </a>
                        <div class="collapse menu-dropdown" id="company">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="#" class="nav-link">Company Information</a></li>
                                <li class="nav-item">
                                    <a href="{{ route('user_title.index') }}" 
                                        class="nav-link {{ request()->routeIs('user_title.index') ? 'active' : '' }}">Designations
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('user_group.index') }}" 
                                        class="nav-link {{ request()->routeIs('user_group.index') ? 'active' : '' }}">Employee Titles
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('currency.index') }}" 
                                        class="nav-link {{ request()->routeIs('currency.index') ? 'active' : '' }}">Currencies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('branch.index') }}" 
                                        class="nav-link {{ request()->routeIs('branch.index') ? 'active' : '' }}">Locations
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('department.index') }}" 
                                        class="nav-link {{ request()->routeIs('department.index') ? 'active' : '' }}">Departments
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('wage_group.index') }}" 
                                        class="nav-link {{ request()->routeIs('wage_group.index') ? 'active' : '' }}">Secondary Wage Groups
                                    </a>
                                </li>
                                <li class="nav-item"><a href="#" class="nav-link">Stations</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Permission Groups</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">New Hire Defaults</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Hierarchy</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Company Bank Information</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Recurring Holidays</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Other Fields</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Policies -->
                    @php
                        $checkPolicylNav = request()->routeIs('policy.*');
                    @endphp
                    <li class="nav-item">
                        <a 
                            class="nav-link menu-link {{ $checkPolicylNav ? 'active' : '' }}" href="#policy"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $checkPolicylNav ? 'true' : 'false' }}"
                            aria-controls="policy">
                            <i class="ri-file-list-line"></i> <span>Policies</span>
                        </a>
                        <div class="collapse menu-dropdown" id="policy">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('policy.policy_groups') }}" 
                                        class="nav-link {{ request()->routeIs('policy.policy_groups') ? 'active' : '' }}">Policy Groups
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.schedule_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.schedule_policies') ? 'active' : '' }}">Schedule Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.rounding_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.rounding_policies') ? 'active' : '' }}">Rounding Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.meal_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.meal_policies') ? 'active' : '' }}">Meal Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.break_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.break_policies') ? 'active' : '' }}">Break Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.accrual_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.accrual_policies') ? 'active' : '' }}">Accrual Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.overtime_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.overtime_policies') ? 'active' : '' }}">Overtime Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.premium_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.premium_policies') ? 'active' : '' }}">Premium Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.absence_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.absence_policies') ? 'active' : '' }}">Absence Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.exception_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.exception_policies') ? 'active' : '' }}">Exception Policies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('policy.holiday_policies') }}" 
                                        class="nav-link {{ request()->routeIs('policy.holiday_policies') ? 'active' : '' }}">Holiday Policies
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Payroll -->
                    @php
                        $checkPayrollNav = request()->routeIs('payroll.*');
                    @endphp
                    <li class="nav-item">
                        <a 
                            class="nav-link menu-link {{ $checkPayrollNav ? 'active' : '' }}" href="#payroll"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $checkPayrollNav ? 'true' : 'false' }}"
                            aria-controls="payroll">
                            <i class="ri-bar-chart-line"></i> <span>Payroll</span>
                        </a>
                        <div class="collapse menu-dropdown" id="payroll">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('payroll.payroll_processing') }}" 
                                        class="nav-link {{ request()->routeIs('payroll.payroll_processing') ? 'active' : '' }}">End of Pay Period
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('payroll.pay_stub_amendment') }}" 
                                        class="nav-link {{ request()->routeIs('payroll.pay_stub_amendment') ? 'active' : '' }}">Pay Stub Amendments
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('payroll.recurring_pay_stub_amendment') }}" 
                                        class="nav-link {{ request()->routeIs('payroll.recurring_pay_stub_amendment') ? 'active' : '' }}">Recurring PS Amendments
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('payroll.pay_period_schedules') }}" 
                                        class="nav-link {{ request()->routeIs('payroll.pay_period_schedules') ? 'active' : '' }}">Pay Period Schedules
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('payroll.paystub_accounts') }}" 
                                        class="nav-link {{ request()->routeIs('payroll.paystub_accounts') ? 'active' : '' }}">Pay Stub Accounts
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('payroll.company_deductions') }}" 
                                        class="nav-link {{ request()->routeIs('payroll.company_deductions') ? 'active' : '' }}">Taxes/Deductions/Earnings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('payroll.paystub_account_link') }}" 
                                        class="nav-link {{ request()->routeIs('payroll.paystub_account_link') ? 'active' : '' }}">Pay Stub Account Linking
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Employees -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#employee" data-bs-toggle="collapse" role="button"
                            aria-expanded="false">
                            <i class="ri-bar-chart-line"></i> <span>Employees</span>
                        </a>
                        <div class="collapse menu-dropdown" id="employee">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="#" class="nav-link">Messages</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Contact Information</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Preferences</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Bank Information</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Admin -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#admin" data-bs-toggle="collapse" role="button"
                            aria-expanded="false">
                            <i class="ri-bar-chart-line"></i> <span>Admin</span>
                        </a>
                        <div class="collapse menu-dropdown" id="admin">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="#" class="nav-link">Employee Administration</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Census Infortion</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">December Bonus Calculation</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Attendance Bonus Calculation</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Gratuity Calculation</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Database Backup</a></li>
                                <li class="nav-item"><a href="#" class="nav-link">Authorization</a></li>
                            </ul>
                        </div>
                    </li>

                    <!--
                    <li class="nav-item"><a href="#" class="nav-link">Permission Groups</a></li>
                    -->

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
