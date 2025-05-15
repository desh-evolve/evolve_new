<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h3 class="mb-sm-0 text-uppercase fw-bold">{{ __('Dashboard') }}</h3>

        <!--
        <div class="page-title-right">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                <li class="breadcrumb-item active">Basic Elements</li>
            </ol>
        </div>
        -->
    </x-slot>


    <style>
     .header{
            position:sticky;
            top: 0 ;
        }
    </style>


    <div class="row">

        {{-- <div class="col-lg-12">
            <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
                <strong>Locations</strong> list is empty. Click here to add new locations.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div> --}}

        <!-- Left Side -->
        <div class="col-lg-9">
            <div class="row">
                {{-- pie chart --}}

                <div class="col-xl-4">
                    <div class="card" style="height: 510px">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Employee Attendance</h4>
                        </div>
                        <div class="card-body">

                            <div id="store-visits-source" class="apex-charts" dir="ltr"></div>

                            <div class="table-responsive mt-5">
                                <table class="table table-borderless table-sm table-centered align-middle table-nowrap mb-0">
                                    <tbody class="border-0">
                                        <tr>
                                            <td>
                                                <h4 class="text-truncate fs-14 fs-medium mb-0">
                                                    <i class="ri-stop-fill align-middle fs-18 text-success me-2"></i>Attendance
                                                </h4>
                                            </td>
                                            <td>
                                                <p class="text-muted mb-0">
                                                    <i data-feather="users" class="me-2 icon-sm"></i>
                                                    <span class="employee-count-pie">25</span>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h4 class="text-truncate fs-14 fs-medium mb-0">
                                                    <i class="ri-stop-fill align-middle fs-18 text-danger me-2"></i>Approved Leaves
                                                </h4>
                                            </td>
                                            <td>
                                                <p class="text-muted mb-0">
                                                    <i data-feather="external-link" class="me-2 icon-sm"></i>
                                                    <span class="leaves-count-pie">50</span>
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>


                {{-- == --}}
                <div class="col-xl-8">
                    <div class="row">
                        <!-- Employee Count -->
                        <div class="col-md-6">
                            <div class="card card-animate" style="height: 125px;">
                                <div class="card-body bg-success">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-semibold text-white mb-0 fs-5">Employees</p>
                                            <h2 class="mt-4 text-white fs-1 fw-semibold"><span class="employee-count">50</span></h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white rounded-circle fs-2">
                                                    <i data-feather="users" class="text-info"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approved Leave Count -->
                        <div class="col-md-6">
                            <div class="card card-animate" style="height: 125px;">
                                <div class="card-body bg-danger">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-semibold text-white mb-0 fs-5">Approved Leaves</p>
                                            <h2 class="mt-4 text-white fs-1 fw-semibold"><span class="leaves-count">25</span></h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-white rounded-circle fs-2">
                                                    <i data-feather="external-link" class="text-info"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Messages -->
                    <div class="card" style="height: 360px">
                        <div class="card-header align-items-center d-flex ">
                            <h4 class="card-title mb-1 flex-grow-1">Recent Messages</h4>
                        </div>
                        <div>
                            <div data-simplebar style="height: 250px;" class="pt-3">
                                <div class="table-card ps-3">
                                    <table class="table table-borderless table-centered align-middle table-nowrap mb-5">
                                        <thead class="text-muted table-light" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>
                                                <th class="header" scope="col">#</th>
                                                <th class="header" scope="col">From</th>
                                                <th class="header" scope="col">Type</th>
                                                <th class="header" scope="col">Subject</th>
                                                <th class="header" scope="col">Date</th>
                                            </tr>
                                        </thead>

                                        <tbody id="newMessage_table">
                                            <tr>
                                                <td>1</td>
                                                <td>John Doe</td>
                                                <td>Info</td>
                                                <td>Welcome to the platform</td>
                                                <td>May 15, 2025</td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>Jane Smith</td>
                                                <td>Alert</td>
                                                <td>Policy Update</td>
                                                <td>May 14, 2025</td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>System</td>
                                                <td>Reminder</td>
                                                <td>Timesheet Due</td>
                                                <td>May 13, 2025</td>
                                            </tr>
                                        </tbody>


                                    </table>
                                </div>
                            </div>

                            <div class="mt-2 mb-3 text-center">
                                <a href="/user/messages" class="text-info text-decoration-underline fs-6">View More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <!-- New Request Table -->
                <div class="col-xl-6">
                    <div class="card" style="height: 355px">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-1 pt-2 flex-grow-1">Recent Request</h4>
                        </div>
                        <div>
                            <div data-simplebar style="height: 240px;" class="pt-3">
                                <div class="table-card ps-3">
                                    <table class="table table-borderless table-centered align-middle table-nowrap mb-5">
                                        <thead class="text-muted table-light" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Employee</th>
                                                <th scope="col">Type</th>
                                                <th scope="col">Date</th>
                                            </tr>
                                        </thead>

                                        <tbody id="request_table_body">

                                            <tr>
                                                <td colspan="4" class="text-center">No Recent Requests..</td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            {{-- view button --}}
                            <div class="mt-2 mb-3 text-center">
                                <a href="#" class="text-info text-decoration-underline fs-6">View More</a>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- New Leave Request Table -->
                <div class="col-xl-6">
                    <div class="card" style="height: 355px">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-1 pt-2 flex-grow-1">New Leave Request</h4>
                        </div>
                        <div>
                            <div data-simplebar style="height: 240px;" class="pt-3">
                                <div class="table-card ps-3">
                                    <table class="table table-borderless table-centered align-middle table-nowrap mb-5">
                                        <thead class="text-muted table-light" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Leave Type</th>
                                                <th scope="col">Amount</th>
                                                <th scope="col">Start Date</th>
                                                <th scope="col">End Date</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>

                                        <tbody id="leave_request_table_body">
                                            <tr>
                                                <td>1</td>
                                                <td>Ali Khan</td>
                                                <td>Annual Leave</td>
                                                <td>5 Days</td>
                                                <td>May 20, 2025</td>
                                                <td>May 24, 2025</td>
                                                <td><span class="badge bg-warning">Pending</span></td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>Fatima Noor</td>
                                                <td>Sick Leave</td>
                                                <td>2 Days</td>
                                                <td>May 18, 2025</td>
                                                <td>May 19, 2025</td>
                                                <td><span class="badge bg-success">Approved</span></td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>Ahmed Raza</td>
                                                <td>Casual Leave</td>
                                                <td>1 Day</td>
                                                <td>May 17, 2025</td>
                                                <td>May 17, 2025</td>
                                                <td><span class="badge bg-danger">Rejected</span></td>
                                            </tr>

                                             <tr>
                                                <td>4</td>
                                                <td>Ahmed Raza</td>
                                                <td>Casual Leave</td>
                                                <td>1 Day</td>
                                                <td>May 17, 2025</td>
                                                <td>May 17, 2025</td>
                                                <td><span class="badge bg-danger">Rejected</span></td>
                                            </tr>

                                             <tr>
                                                <td>5</td>
                                                <td>Ahmed Raza</td>
                                                <td>Casual Leave</td>
                                                <td>1 Day</td>
                                                <td>May 17, 2025</td>
                                                <td>May 17, 2025</td>
                                                <td><span class="badge bg-danger">Rejected</span></td>
                                            </tr>

                                        </tbody>

                                    </table>
                                </div>
                            </div>
                             {{-- view button --}}
                             <div class="mt-2 mb-3 text-center">
                                <a href="#" class="text-info text-decoration-underline fs-6">View More</a>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>


        <!-- Right Side -->
        <div class="col-lg-3 d-flex flex-column">
            <div>
                <div class="card rounded-0 h-100">
                    <div class="card-body p-0">
                        <div class="p-3 bg-primary">
                            <h6 class="text-white mb-2 mt-1 text-uppercase fw-semibold fs-5">Recent Activity</h6>
                        </div>
                        <div data-simplebar style="height: 414px;" class="p-3 pt-0">

                            <!-- Activity Timeline -->
                            <div class="acitivity-timeline acitivity-main mt-4">
                                <div class="acitivity-item d-flex mb-4">
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 lh-base">Purchase by James Price</h6>
                                        <p class="text-muted mb-1">Product noise evolve smartwatch</p>
                                        <small class="mb-0 text-muted">02:14 PM Today</small>
                                    </div>
                                </div>

                                <hr>

                                <div class="acitivity-item d-flex mb-4">
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 lh-base">Purchase by James Price</h6>
                                        <p class="text-muted mb-1">Product noise evolve smartwatch</p>
                                        <small class="mb-0 text-muted">02:14 PM Today</small>
                                    </div>
                                </div>

                                <hr>

                                <div class="acitivity-item d-flex mb-4">
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 lh-base">Purchase by James Price</h6>
                                        <p class="text-muted mb-1">Product noise evolve smartwatch</p>
                                        <small class="mb-0 text-muted">02:14 PM Today</small>
                                    </div>
                                </div>

                                <hr>

                                <div class="acitivity-item d-flex mb-4">
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 lh-base">Purchase by James Price</h6>
                                        <p class="text-muted mb-1">Product noise evolve smartwatch</p>
                                        <small class="mb-0 text-muted">02:14 PM Today</small>
                                    </div>
                                </div>

                                <hr>
                            </div>
                        </div>


                        <div class="p-0 mt-2">
                            <div class="p-3 bg-primary">
                                <h6 class="text-white mb-1 mt-1 text-uppercase fw-semibold fs-5">Exceptions</h6>
                            </div>

                            <div class="p-3">
                                <ol class="ps-3 text-muted">
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Mobile & Accessories <span class="float-end">(10,294)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Desktop <span class="float-end">(6,256)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Electronics <span class="float-end">(3,479)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Home & Furniture <span class="float-end">(2,275)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Grocery <span class="float-end">(1,950)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Fashion <span class="float-end">(1,582)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Appliances <span class="float-end">(1,037)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Beauty, Toys & More <span class="float-end">(924)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Food & Drinks <span class="float-end">(701)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Toys & Games <span class="float-end">(239)</span></a>
                                    </li>
                                </ol>
                            </div>

                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Basis of Employment Confirmation Request Table -->
        <div class="col-xl-6">
            <div class="card" style="height: 355px">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-1 pt-2 flex-grow-1">Basis of Employment Confirmation Request</h4>
                </div>
                <div>
                    <div data-simplebar style="height: 240px;" class="pt-3">
                        <div class="table-card ps-3">
                            <table class="table table-borderless table-centered align-middle table-nowrap mb-5">
                                <thead class="text-muted table-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Employee Number</th>
                                        <th scope="col">Employee Name</th>
                                        <th scope="col">Confirmation Due</th>
                                        <th scope="col">Hire/Resign Date</th>
                                        <th scope="col">Type</th>
                                    </tr>
                                </thead>

                                <tbody id="table_body">
                                    <tr>
                                        <td>1</td>
                                        <td>EMP-00123</td>
                                        <td>John Doe</td>
                                        <td>May 30, 2025</td>
                                        <td>Jan 01, 2024</td>
                                        <td>Permanent</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>EMP-00456</td>
                                        <td>Jane Smith</td>
                                        <td>Jun 15, 2025</td>
                                        <td>Mar 15, 2024</td>
                                        <td>Contract</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>EMP-00789</td>
                                        <td>Ali Khan</td>
                                        <td>Jul 01, 2025</td>
                                        <td>Apr 20, 2024</td>
                                        <td>Probation</td>
                                    </tr>
                                </tbody>


                            </table>
                        </div>
                    </div>
                        {{-- view button --}}
                        <div class="mt-2 mb-3 text-center">
                        <a href="#" class="text-info text-decoration-underline fs-6">View More</a>
                    </div>
                </div>

            </div>

        </div>

        <!-- 3 Days Absenteeism Table -->
        <div class="col-xl-6">
            <div class="card" style="height: 355px">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-1 pt-2 flex-grow-1">3 Days Absenteeism</h4>
                </div>
                <div>
                    <div data-simplebar style="height: 240px;" class="pt-3">
                        <div class="table-card ps-3">
                            <table class="table table-borderless table-centered align-middle table-nowrap mb-5">
                                <thead class="text-muted table-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Employee</th>
                                        <th scope="col">Branch</th>
                                        <th scope="col">Department</th>
                                    </tr>
                                </thead>

                                <tbody id="absenteeism_table_body">
                                    <tr>
                                        <td colspan="4" class="text-center">No Recent Data..</td>
                                    </tr>
                                </tbody>

                            </table>
                        </div>
                    </div>
                        {{-- view button --}}
                        <div class="mt-2 mb-3 text-center">
                        <a href="#" class="text-info text-decoration-underline fs-6">View More</a>
                    </div>
                </div>

            </div>

        </div>

    </div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            chart: {
                type: 'pie',
                height: 500
            },
            labels: ['Attendance', 'Approved Leaves'],
            series: [25, 50], // Dummy values
            colors: ['#00AE98', '#e15d44'],
            legend: {
                position: 'bottom'
            }
        };

        var chart = new ApexCharts(document.querySelector("#store-visits-source"), options);
        chart.render();
    });
</script>

</x-app-layout>
