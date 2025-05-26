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
                                            <h2 class="mt-4 text-white fs-1 fw-semibold"><span class="employee-count">0</span></h2>
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
                                                <th class="header" scope="col">Subject</th>
                                                <th class="header" scope="col">Date</th>
                                            </tr>
                                        </thead>

                                        <tbody id="message_table_body">
                                            <tr>
                                                <td colspan="4" class="text-center">Loading...</td>
                                            </tr>
                                        </tbody>



                                    </table>
                                </div>
                            </div>

                            <div class="mt-2 mb-3 text-center">
                                <a href="{{ route('user.messages.index') }}" class="text-info text-decoration-underline fs-6">View More</a>
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
                        {{-- <div class="p-2 bg-primary">
                            <h6 class="text-white mb-2 mt-1 text-uppercase fw-semibold fs-5">Employee Pending Confirmation</h6>
                        </div> --}}
                        <div data-simplebar style="height: 292px;" class="p-3 pt-6 mt-4">

                            <form id="searchForm">
                                <div class="mb-2">
                                    <label for="startDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control form-control-sm" id="startDate">
                                </div>
                                <div class="mb-2">
                                    <label for="endDate" class="form-label">End Date</label>
                                    <input type="date" class="form-control form-control-sm" id="endDate">
                                </div>
                                <div class="mb-2">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select form-select-sm" id="category">
                                        <option value="1">Contract </option>
                                        <option value="2">Training </option>
                                        <option value="3">Permanent (With Probation) </option>
                                        <option value="4">Permanent (Confirmed) </option>
                                        <option value="5">Resign </option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-sm btn-warning w-100 me-1">Search</button>
                                </div>

                            </form>

                            <!-- Results container -->
                            <div id="resultsContainer" class="mt-4"></div>

                        </div>


                        {{-- current exception --}}
                        <div class="mb-0 pb-0">
                            <div class="p-2 bg-primary">
                                <h6 class="text-white mb-1 mt-1 text-uppercase fw-semibold fs-5">Current Exceptions</h6>
                            </div>

                            <div>
                                <table class="table table-sm align-middle mb-0 pb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center px-3 py-2">Severity</th>
                                            <th class="text-center px-3 py-2">Exceptions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="exceptions-table-body">
                                        <tr id="row-high">
                                            <td class="text-start px-4 py-1">High</td>
                                            <td class="text-center px-3 py-1">
                                                <a href="{{ route('dashboard.exception') }}" class="text-muted" id="high-value">(0)</a>
                                            </td>
                                        </tr>
                                        <tr id="row-medium">
                                            <td class="text-start px-4 py-1">Medium</td>
                                            <td class="text-center px-3 py-1">
                                                <a href="{{ route('dashboard.exception') }}" class="text-muted" id="medium-value">(0)</a>
                                            </td>
                                        </tr>
                                        <tr id="row-low">
                                            <td class="text-start px-4 py-1">Low</td>
                                            <td class="text-center px-3 py-1">
                                                <a href="{{ route('dashboard.exception') }}" class="text-muted" id="low-value">(0)</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">

        <!-- New Request Table -->
        <div class="col-xl-6">
            <div class="card" style="height: 315px">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-1 pt-1 flex-grow-1">Recent Request</h4>
                </div>
                <div>
                    <div data-simplebar style="height: 200px;" class="pt-3">
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
                                        <td colspan="4" class="text-center">Loading...</td>
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


        <!-- Pending Request Table -->
        <div class="col-xl-6">
            <div class="card" style="height: 315px">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-1 pt-1 flex-grow-1">Pending Requests</h4>
                </div>
                <div>
                    <div data-simplebar style="height: 200px;" class="pt-3">
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

                                <tbody id="pending_request_table_body">

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



    <div class="row">
        <!-- Basis of Employment Confirmation Request Table -->
        <div class="col-xl-6">
            <div class="card" style="height: 315px">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-1 pt-1 flex-grow-1">Basis of Employment Confirmation Request</h4>
                </div>
                <div>
                    <div data-simplebar style="height: 200px;" class="pt-3">
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

                                <tbody id="emp_confirmation_table_body">
                                   <tr>
                                        <td colspan="6" class="text-center">Loading...</td>
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
            <div class="card" style="height: 315px">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-1 pt-1 flex-grow-1">3 Days Absenteeism</h4>
                </div>
                <div>
                    <div data-simplebar style="height: 200px;" class="pt-3">
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
                                        <td colspan="4" class="text-center">Loading...</td>
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


        // dashboard user count
        fetch('{{ route('dashboard.user_count') }}')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.employee-count').textContent = data.user_count;
            })
            .catch(error => {
                console.error('Error fetching user count:', error);
                document.querySelector('.employee-count').textContent = 'N/A';
            });


        // 3 days absands details
        fetch('{{ route('dashboard.absenteeism') }}')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('absenteeism_table_body');
                tbody.innerHTML = ''; // Clear existing rows

                if (data.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center">No Recent Data..</td></tr>`;
                } else {
                    data.data.forEach((employee, index) => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${employee.full_name}</td>
                                <td>${employee.default_branch}</td>
                                <td>${employee.default_department}</td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching absentee data:', error);
                const tbody = document.getElementById('absenteeism_table_body');
                tbody.innerHTML = `<tr><td colspan="4" class="text-danger text-center">Failed to load data.</td></tr>`;
            });


        // recent messages
        fetch('{{ route('dashboard.recent_messages') }}')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('message_table_body');
                tbody.innerHTML = ''; // Clear existing rows

                if (data.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center">No Recent Messages..</td></tr>`;
                } else {
                    data.data.forEach((message, index) => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${message.user_full_name}</td>
                                <td>${message.subject}</td>
                                <td>${message.created_date}</td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching message data:', error);
                const tbody = document.getElementById('message_table_body');
                tbody.innerHTML = `<tr><td colspan="4" class="text-danger text-center">Failed to load data.</td></tr>`;
            });


        // recent request
        fetch('{{ route('dashboard.recent_request') }}')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('request_table_body');
                tbody.innerHTML = ''; // Clear existing rows

                if (data.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center">No Recent Request..</td></tr>`;
                } else {
                    data.data.forEach((request, index) => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${request.date_stamp}</td>
                                <td>${request.status}</td>
                                <td>${request.type}</td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching request data:', error);
                const tbody = document.getElementById('request_table_body');
                tbody.innerHTML = `<tr><td colspan="4" class="text-danger text-center">Failed to load data.</td></tr>`;
            });


        // pending request
        fetch('{{ route('dashboard.pending_request') }}')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('pending_request_table_body');
                tbody.innerHTML = ''; // Clear existing rows

                if (data.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center">No Pending Request..</td></tr>`;
                } else {
                    data.data.forEach((pendingRequests, index) => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${pendingRequests.user_full_name}</td>
                                <td>${pendingRequests.type}</td>
                                <td>${pendingRequests.date_stamp}</td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching pending request data:', error);
                const tbody = document.getElementById('pending_request_table_body');
                tbody.innerHTML = `<tr><td colspan="4" class="text-danger text-center">Failed to load data.</td></tr>`;
            });


        // Add this function above the fetch if not already present
        const formatDate = (epoch) => {
            if (!epoch || epoch === 0) return '-';
            const date = new Date(epoch * 1000);
            return date.toLocaleDateString();
        };

        fetch('{{ route('dashboard.employement_confirmation_request') }}')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('emp_confirmation_table_body');
                tbody.innerHTML = ''; // Clear existing rows

                if (data.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center">No Employment Confirmation Request..</td></tr>`;
                } else {
                    data.data.forEach((warning_employee1, index) => {

                        let relevantDate = '-';
                        if (warning_employee1.basis_of_employment == 5) {
                            relevantDate = formatDate(warning_employee1.resign_date);
                        } else {
                            relevantDate = formatDate(warning_employee1.hire_date);
                        }

                        tbody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${warning_employee1.employee_number}</td>
                                <td>${warning_employee1.full_name}</td>
                                <td>${warning_employee1['0'] || '-'}</td>
                                <td>${relevantDate}</td>
                                <td>${warning_employee1['1'] || '-'}</td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching employment confirmation request data:', error);
                const tbody = document.getElementById('emp_confirmation_table_body');
                tbody.innerHTML = `<tr><td colspan="6" class="text-danger text-center">Failed to load data.</td></tr>`;
            });


    });


    // Current Exception
    document.addEventListener("DOMContentLoaded", function () {
        fetch("{{ route('dashboard.exception') }}")
            .then(response => response.json())
            .then(result => {
                const data = result.data || {};

                // High
                const high = data[30] ?? 0;
                const highEl = document.querySelector('#high-exception a');
                highEl.querySelector('span').textContent = `(${high})`;
                if (high > 0) {
                    highEl.classList.remove('text-muted');
                    highEl.classList.add('text-danger', 'fw-bold');
                    highEl.style.backgroundColor = 'red';
                }

                // Medium
                const medium = data[20] ?? 0;
                const mediumEl = document.querySelector('#medium-exception a');
                mediumEl.querySelector('span').textContent = `(${medium})`;
                if (medium > 0) {
                    mediumEl.classList.remove('text-muted');
                    mediumEl.classList.add('text-warning', 'fw-bold');
                    mediumEl.style.backgroundColor = 'yellow';
                }

                // Low
                const low = data[10] ?? 0;
                const lowEl = document.querySelector('#low-exception a');
                lowEl.querySelector('span').textContent = `(${low})`;
            })
            .catch(err => {
                console.error('Error loading exceptions:', err);
            });

        });


    document.querySelector('.btn-warning').addEventListener('click', () => {
        const data = {
            start_date: document.getElementById('startDate').value,
            end_date: document.getElementById('endDate').value,
            category: document.getElementById('category').value
        };

        fetch('/dashboard/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(res => {
            let html = '<pre>' + JSON.stringify(res.data, null, 2) + '</pre>';
            document.getElementById('resultsContainer').innerHTML = html;
        })
        .catch(err => {
            console.error(err);
        });

    });



</script>
</x-app-layout>
