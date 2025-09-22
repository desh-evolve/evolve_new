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
                @php
                    $checkAttendancelNav = request()->routeIs('attendance.*');
                @endphp
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#attendance" data-bs-toggle="collapse" role="button"
                        aria-expanded="false">
                        <i class="ri-calendar-check-fill"></i> <span>Attendance</span>
                    </a>
                    <div class="collapse menu-dropdown" id="attendance">
                        <ul class="nav nav-sm flex-column">

                            <li class="nav-item">
                                <a href="{{ route('attendance.timesheet') }}"
                                    class="nav-link {{ request()->routeIs('attendance.timesheet') ? 'active' : '' }}">My Timesheet
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.punchlist') }}"
                                    class="nav-link {{ request()->routeIs('attendance.punchlist') ? 'active' : '' }}">Punches
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.masspunch.add') }}"
                                    class="nav-link {{ request()->routeIs('attendance.masspunch.add') ? 'active' : '' }}">Mass Punch
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('attendance.requests') }}"
                                    class="nav-link {{ request()->routeIs('attendance.requests') ? 'active' : '' }}">Requests
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.apply_leaves') }}"
                                    class="nav-link {{ request()->routeIs('attendance.apply_leaves') ? 'active' : '' }}">Apply Leaves
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.leaves.covered_aprooval') }}"
                                    class="nav-link {{ request()->routeIs('attendance.leaves.covered_aprooval') ? 'active' : '' }}">Leaves Cover View
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.leaves.supervise_aprooval') }}"
                                    class="nav-link {{ request()->routeIs('attendance.leaves.supervise_aprooval') ? 'active' : '' }}">Leaves Supervisor Approval
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.leaves.confirmed_leave') }}"
                                    class="nav-link {{ request()->routeIs('attendance.leaves.confirmed_leave') ? 'active' : '' }}">Leaves Confirmation Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.accruals') }}"
                                    class="nav-link {{ request()->routeIs('attendance.accruals') ? 'active' : '' }}">Accruals
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.paystubs') }}"
                                    class="nav-link {{ request()->routeIs('attendance.paystubs') ? 'active' : '' }}">Pay Slips
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Schedule -->
                @php
                    $checkAttendancelNav = request()->routeIs('schedule.*');
                @endphp
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#schedule" data-bs-toggle="collapse" role="button"
                        aria-expanded="false">
                        <i class="ri-calendar-line"></i> <span>Schedule</span>
                    </a>
                    <div class="collapse menu-dropdown" id="schedule">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('schedule.view_schedule') }}"
                                    class="nav-link {{ request()->routeIs('schedule.view_schedule') ? 'active' : '' }}">My Schedule
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('schedule.schedule_list') }}"
                                    class="nav-link {{ request()->routeIs('schedule.schedule_list') ? 'active' : '' }}">Scheduled Shifts
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('schedule.add_mass_schedule') }}"
                                    class="nav-link {{ request()->routeIs('schedule.add_mass_schedule') ? 'active' : '' }}">Mass Schedule
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('schedule.recurring_schedule_control_list') }}"
                                    class="nav-link {{ request()->routeIs('schedule.recurring_schedule_control_list') ? 'active' : '' }}">Recurring Schedule
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('schedule.recurring_schedule_template_control_list') }}"
                                    class="nav-link {{ request()->routeIs('schedule.recurring_schedule_template_control_list') ? 'active' : '' }}">Recurring Schedule Templates
                                </a>
                            </li>
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

                            <li class="nav-item">
                                <a href="{{ route('employee_detail.index') }}"
                                    class="nav-link {{ request()->routeIs('employee_detail.index') ? 'active' : '' }}">Employee Detail Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.employee_timesheet_report') }}"
                                    class="nav-link {{ request()->routeIs('report.employee_timesheet_report') ? 'active' : '' }}">Attendance Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.daily_attendance') }}"
                                    class="nav-link {{ request()->routeIs('report.daily_attendance') ? 'active' : '' }}">Daily Attendance Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.department_attendance_report') }}"
                                    class="nav-link {{ request()->routeIs('report.department_attendance_report') ? 'active' : '' }}">Department Attendance Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.daily_Absence_report') }}"
                                    class="nav-link {{ request()->routeIs('report.daily_Absence_report') ? 'active' : '' }}">Head Count & Absence Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.employee_nopay_count_report') }}"
                                    class="nav-link {{ request()->routeIs('report.employee_nopay_count_report') ? 'active' : '' }}">Employee Nopay Count Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.missing_punch_report') }}"
                                    class="nav-link {{ request()->routeIs('report.missing_punch_report') ? 'active' : '' }}">Missing Punch Report
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.formE_payments') }}"
                                    class="nav-link {{ request()->routeIs('report.formE_payments') ? 'active' : '' }}">EPF Payments Details
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.bank_transfer_summary') }}"
                                    class="nav-link {{ request()->routeIs('report.bank_transfer_summary') ? 'active' : '' }}">Bank Transfer Summary
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.employee_time_overtime_report_monthly') }}"
                                    class="nav-link {{ request()->routeIs('report.employee_time_overtime_report_monthly') ? 'active' : '' }}">Employee OT Report - Monthly
                                </a>
                            </li>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.payroll_report') }}"
                                    class="nav-link {{ request()->routeIs('report.payroll_report') ? 'active' : '' }}">Payroll Report
                                </a>
                            </li>
                            {{-- <li class="nav-item"><a href="#" class="nav-link">Employee Report</a></li> --}}
                            <li class="nav-item"><a href="#" class="nav-link">EPF Report</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Company -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#company" data-bs-toggle="collapse" role="button"
                        aria-expanded="false">
                        <i class="ri-building-line"></i> <span>Company</span>
                    </a>
                    <div class="collapse menu-dropdown" id="company">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('company.index') }}"
                                    class="nav-link {{ request()->routeIs('company.index') ? 'active' : '' }}">
                                    Company Information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('user_title.index') }}"
                                    class="nav-link {{ request()->routeIs('user_title.index') ? 'active' : '' }}">Designations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('user_group.index') }}"
                                    class="nav-link {{ request()->routeIs('user_group.index') ? 'active' : '' }}">Employee
                                    Titles
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
                                    class="nav-link {{ request()->routeIs('wage_group.index') ? 'active' : '' }}">Secondary
                                    Wage Groups
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('station.index') }}"
                                    class="nav-link {{ request()->routeIs('station.index') ? 'active' : '' }}">Stations
                                </a>
                            </li>
                </li>
                <li class="nav-item">
                    <a href="{{ route('permission_control.index') }}"
                        class="nav-link {{ request()->routeIs('permission_control.index') ? 'active' : '' }}">Permission
                        Groups
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('new_hire_defaults.index') }}"
                        class="nav-link {{ request()->routeIs('new_hire_defaults.index') ? 'active' : '' }}">
                        New Hire Defaults
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('company.hierarchy.list') }}"
                        class="nav-link {{ request()->routeIs('company.hierarchy.list') ? 'active' : '' }}">Hierarchy
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('bank_account.company', ['company_id' => $current_company->getId()]) }}"
                       class="nav-link {{ request()->routeIs('bank_account.company') ? 'active' : '' }}">
                       Company Bank Information
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('recurring_holidays.index') }}"
                        class="nav-link {{ request()->routeIs('recurring_holidays.index') ? 'active' : '' }}">Recurring
                        Holidays
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('company.other_field.index') }}"
                        class="nav-link {{ request()->routeIs('company.other_field.index') ? 'active' : '' }}">
                        Other Fields
                    </a>
                </li>
            </ul>
        </div>
        </li>

        <!-- Policies -->
        @php
            $checkPolicylNav = request()->routeIs('policy.*');
        @endphp
        <li class="nav-item">
            <a class="nav-link menu-link {{ $checkPolicylNav ? 'active' : '' }}" href="#policy"
                data-bs-toggle="collapse" role="button" aria-expanded="{{ $checkPolicylNav ? 'true' : 'false' }}"
                aria-controls="policy">
                <i class="ri-file-text-line"></i> <span>Policies</span>
            </a>
            <div class="collapse menu-dropdown" id="policy">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('policy.policy_groups') }}"
                            class="nav-link {{ request()->routeIs('policy.policy_groups') ? 'active' : '' }}">Policy
                            Groups
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.schedule_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.schedule_policies') ? 'active' : '' }}">Schedule
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.rounding_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.rounding_policies') ? 'active' : '' }}">Rounding
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.meal_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.meal_policies') ? 'active' : '' }}">Meal
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.break_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.break_policies') ? 'active' : '' }}">Break
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.accrual_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.accrual_policies') ? 'active' : '' }}">Accrual
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.overtime_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.overtime_policies') ? 'active' : '' }}">Overtime
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.premium_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.premium_policies') ? 'active' : '' }}">Premium
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.absence_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.absence_policies') ? 'active' : '' }}">Absence
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.exception_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.exception_policies') ? 'active' : '' }}">Exception
                            Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('policy.holiday_policies') }}"
                            class="nav-link {{ request()->routeIs('policy.holiday_policies') ? 'active' : '' }}">Holiday
                            Policies
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
            <a class="nav-link menu-link {{ $checkPayrollNav ? 'active' : '' }}" href="#payroll"
                data-bs-toggle="collapse" role="button" aria-expanded="{{ $checkPayrollNav ? 'true' : 'false' }}"
                aria-controls="payroll">
                <i class="ri-wallet-line"></i> <span>Payroll</span>
            </a>
            <div class="collapse menu-dropdown" id="payroll">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('payroll.payroll_processing') }}"
                            class="nav-link {{ request()->routeIs('payroll.payroll_processing') ? 'active' : '' }}">End
                            of Pay Period
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('payroll.pay_stub_amendment') }}"
                            class="nav-link {{ request()->routeIs('payroll.pay_stub_amendment') ? 'active' : '' }}">Pay
                            Stub Amendments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('payroll.recurring_pay_stub_amendment') }}"
                            class="nav-link {{ request()->routeIs('payroll.recurring_pay_stub_amendment') ? 'active' : '' }}">Recurring
                            PS Amendments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('payroll.pay_period_schedules') }}"
                            class="nav-link {{ request()->routeIs('payroll.pay_period_schedules') ? 'active' : '' }}">Pay
                            Period Schedules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('payroll.paystub_accounts') }}"
                            class="nav-link {{ request()->routeIs('payroll.paystub_accounts') ? 'active' : '' }}">Pay
                            Stub Accounts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('payroll.company_deductions') }}"
                            class="nav-link {{ request()->routeIs('payroll.company_deductions') ? 'active' : '' }}">Taxes/Deductions/Earnings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('payroll.paystub_account_link') }}"
                            class="nav-link {{ request()->routeIs('payroll.paystub_account_link') ? 'active' : '' }}">Pay
                            Stub Account Linking
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Employees -->
        <li class="nav-item">
            <a class="nav-link menu-link" href="#employee" data-bs-toggle="collapse" role="button"
                aria-expanded="false">
                <i class="ri-group-line"></i> <span>Employees</span>
            </a>
            <div class="collapse menu-dropdown" id="employee">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('user.messages.index') }}"
                            class="nav-link {{ request()->routeIs('user.messages.index') ? 'active' : '' }}">
                            Messages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('user_preference.index') }}"
                            class="nav-link  {{ request()->routeIs('user_preference.index') ? 'active' : '' }}">
                            Preferences
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a href="{{ route('user.web_password.index') }}"
                            class="nav-link {{ request()->routeIs('user.web_password.index') ? 'active' : '' }}">
                            Web Password
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('user.quick_punch_password.index') }}"
                            class="nav-link {{ request()->routeIs('user.quick_punch_password.index') ? 'active' : '' }}">
                            Quick Punch Password
                        </a>
                    </li> --}}

                    <li class="nav-item">
                        <a href="{{ route('bank_account.user', ['user_id' => $current_user->getId()]) }}"
                            class="nav-link {{ request()->routeIs('bank_account.user') ? 'active' : '' }}">
                            Bank Information
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Admin -->
        @php
            $checkAdminNav = request()->routeIs('admin.*');
        @endphp
        <li class="nav-item">
            <a class="nav-link menu-link {{ $checkAdminNav ? 'active' : '' }}" href="#admin"
                data-bs-toggle="collapse" role="button" aria-expanded="{{ $checkAdminNav ? 'true' : 'false' }}"
                aria-controls="admin">
                <i class="ri-shield-line"></i> <span>Admin</span>
            </a>
            <div class="collapse menu-dropdown" id="admin">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('admin.userlist') }}"
                            class="nav-link {{ request()->routeIs('admin.userlist') ? 'active' : '' }}">Employee
                            Administration
                        </a>
                    </li>
                    <li class="nav-item"><a href="#" class="nav-link">Census Infortion</a></li>

                    <li class="nav-item">
                        <a href="{{ route('users.bonus_calc') }}"
                            class="nav-link {{ request()->routeIs('users.bonus_calc') ? 'active' : '' }}">December Bonus Calculation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('users.attendance_bonus_calc') }}"
                            class="nav-link {{ request()->routeIs('users.attendance_bonus_calc') ? 'active' : '' }}">Attendance Bonus Calculation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('users.gratuity_calc') }}"
                            class="nav-link {{ request()->routeIs('users.gratuity_calc') ? 'active' : '' }}">Gratuity Calculation
                        </a>
                    </li>

                   <li class="nav-item"><a href="#" class="nav-link">Database Backup</a></li>
                    <li class="nav-item">
                        <a href="{{ route('authorization.authorization_list') }}"
                            class="nav-link {{ request()->routeIs('authorization.authorization_list') ? 'active' : '' }}">Authorization
                        </a>
                    </li>

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
