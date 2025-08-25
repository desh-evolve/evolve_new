<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Administration') }}</h4>
    </x-slot>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/admin/userlist/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1">
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    {{-- --------------------------------------------------------------------------- --}}
                    <div class="table-responsive">
                        <table id="userlist_table" class="table nowrap align-middle" style="width:100%">
                            {{-- <table id="example" class="table table-bordered table-striped align-middle dt-responsive" style="width:100%"> --}}
                            <thead class="bg-primary text-white">
                                <th>#</th>
                                <th>Employee #</th>
                                <th>Status</th>
                                <th>User Name</th>
                                <th>Phone ID</th>
                                <th>iButton</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Last Name</th>
                                <th>NIC</th>
                                <th>Title</th>
                                <th>Group</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>Sex</th>
                                <th>Address 1</th>
                                <th>Address 2</th>
                                <th>City</th>
                                <th>Province/State</th>
                                <th>Country</th>
                                <th>Postal Code</th>
                                <th>Work Phone</th>
                                <th>Home Phone</th>
                                <th>Mobile Phone</th>
                                <th>Fax Phone</th>
                                <th>Home Email</th>
                                <th>Work Email</th>
                                <th>Birth Date</th>
                                <th>Appointment Date</th>
                                <th>Termination Date</th>
                                <th>SIN/SSN</th>
                                <th>Functions <br>
                                    <!-- Added Employee and Payroll buttons -->
                                    <button class="btn btn-sm btn-light function-type active"
                                        data-type="employee">Employee</button>
                                    <button class="btn btn-sm btn-primary function-type"
                                        data-type="payroll">Payroll</button>
                                </th>
                            </thead>
                            @foreach ($users as $index => $user)
                                <tr data-user-id="{{ $user['id'] }}">
                                    <td>{{ $index + 1 }} </td>
                                    <td>{{ $user['employee_number'] }}</td>

                                    <td>{{ $user['status'] }}</td>
                                    <td>{{ $user['user_name'] }}</td>
                                    <td>{{ $user['phone_id'] }}</td>
                                    <td>{{ $user['ibutton_id'] }}</td>
                                    <td>{{ $user['first_name'] }}</td>
                                    <td>{{ $user['middle_name'] }}</td>
                                    <td>{{ $user['last_name'] }}</td>

                                    <td>{{ $user['nic'] }}</td>
                                    <td>{{ $user['title'] }}</td>
                                    <td>{{ $user['user_group'] }}</td>
                                    <td>{{ $user['default_branch'] }}</td>
                                    <td>{{ $user['default_department'] }}</td>
                                    <td>{{ $user['sex'] }}</td>
                                    <td>{{ $user['address1'] }}</td>
                                    <td>{{ $user['address2'] }}</td>
                                    <td>{{ $user['city'] }}</td>
                                    <td>{{ $user['province'] }}</td>
                                    <td>{{ $user['country'] }}</td>
                                    <td>{{ $user['postal_code'] }}</td>
                                    <td>{{ $user['work_phone'] }}</td>
                                    <td>{{ $user['home_phone'] }}</td>
                                    <td>{{ $user['mobile_phone'] }}</td>
                                    <td>{{ $user['fax_phone'] }}</td>
                                    <td>{{ $user['home_email'] }}</td>
                                    <td>{{ $user['work_email'] }}</td>
                                    <td>{{ $user['birth_date'] }}</td>
                                    <td>{{ $user['hire_date'] }}</td>
                                    <td>{{ $user['termination_date'] }}</td>
                                    <td>{{ $user['sin'] }}</td>

                                    <td class="function-buttons">
                                        <a class="btn btn-secondary btn-sm"
                                            href="{{ route('admin.userlist.add', ['id' => $user['id']]) }}">
                                            Edit
                                        </a>
                                        <a class="btn btn-info btn-sm"
                                            href="/user/preference?user_id={{ $user['id'] }}">
                                            Prefs
                                        </a>
                                        <a class="btn btn-warning btn-sm"
                                            href="/user/jobhistory?user_id={{ $user['id'] }}">
                                            Job History
                                        </a>

                                        {{-- <a class="btn btn-warning  btn-sm"
                                            href="/admin/userlist/kpi/{{ $user['id'] }}">
                                            KPI
                                        </a> --}}

                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="commonDeleteFunction('/admin/userlist/delete/{{ $user['id'] }}', 'User', this)">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // $(document).ready(function() {
        //     function initTable() {
        //         new DataTable("#userlist_table", {
        //             scrollX: !0,
        //             dom: "Bfrtip",
        //             buttons: ["copy", "csv", "excel", "print", "pdf"],
        //             //fixedHeader: !0
        //         })
        //     }

        //     initTable();

        //     // Added: Update Functions column buttons based on type
        //     $('.function-type').on('click', function() {
        //         var type = $(this).data('type');
        //         $('.function-type').removeClass('active').removeClass('btn-light').addClass('btn-primary');
        //         $(this).addClass('active').addClass('btn-light');

        //         $('#userlist_table tbody tr').each(function() {
        //             var $row = $(this);
        //             var userId = $row.data('user-id');
        //             var $functionCell = $row.find('.function-buttons');

        //             if (type === 'employee') {
        //                 $functionCell.html(`
        //                     <a class="btn btn-secondary btn-sm" href="/admin/userlist/add?id=${userId}">Edit</a>
        //                     <a class="btn btn-info btn-sm" href="/user/preference?user_id=${userId}">Prefs</a>
        //                     <a class="btn btn-warning btn-sm" href="/user/jobhistory?user_id=${userId}">Job History</a>
        //                     <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/admin/userlist/delete/${userId}', 'User', this)">Delete</button>
        //                 `);
        //             } else if (type === 'payroll') {
        //                 $functionCell.html(`
        //                     <a class="btn btn-primary btn-sm" href="/user/wage?user_id=${userId}">Wage</a>
        //                     <a class="btn btn-success btn-sm" href="/user/tax?user_id=${userId}">Tax</a>
        //                     <a class="btn btn-info btn-sm" href="/payroll/pay_stub_amendment?filter_user_id=${userId}">PS Amendments</a>
        //                     <a class="btn btn-warning btn-sm" href="/bank_account/user/${userId}">Bank</a>
        //                 `);
        //             }
        //         });
        //     });

        //     // Added: Initialize with Employee buttons
        //     $('.function-type[data-type="employee"]').trigger('click');
        // });


        // new
        $(document).ready(function() {
            let currentMode = 'employee'; // default

            const table = new DataTable("#userlist_table", {
                scrollX: true,
                dom: "Bfrtip",
                buttons: ["copy", "csv", "excel", "print", "pdf"]
            });

            // Function to update function buttons per row
            function updateFunctionButtons(type) {
                $('#userlist_table tbody tr').each(function() {
                    const $row = $(this);
                    const userId = $row.data('user-id');
                    const $functionCell = $row.find('.function-buttons');

                    if (!$functionCell.length) return;

                    if (type === 'employee') {
                        $functionCell.html(`
                            <a class="btn btn-secondary btn-sm" href="/admin/userlist/add?id=${userId}">Edit</a>
                            <a class="btn btn-info btn-sm" href="/user/preference?user_id=${userId}">Prefs</a>
                            <a class="btn btn-warning btn-sm" href="/user/jobhistory?user_id=${userId}">Job History</a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/admin/userlist/delete/${userId}', 'User', this)">Delete</button>
                        `);
                    } else if (type === 'payroll') {
                        $functionCell.html(`
                            <a class="btn btn-primary btn-sm" href="/user/wage?user_id=${userId}">Wage</a>
                            <a class="btn btn-success btn-sm" href="/user/tax?user_id=${userId}">Tax</a>
                            <a class="btn btn-info btn-sm" href="/payroll/pay_stub_amendment?filter_user_id=${userId}">PS Amendments</a>
                            <a class="btn btn-warning btn-sm" href="/bank_account/user/${userId}">Bank</a>
                        `);
                    }
                });
            }

            // Button click handler
            $('.function-type').on('click', function() {
                currentMode = $(this).data('type');

                // toggle styles
                $('.function-type').removeClass('active btn-light').addClass('btn-primary');
                $(this).addClass('active btn-light').removeClass('btn-primary');

                updateFunctionButtons(currentMode);
            });

            // Hook into DataTables redraw event
            table.on('draw', function() {
                updateFunctionButtons(currentMode);
            });

            // Initialize with Employee buttons
            $('.function-type[data-type="employee"]').trigger('click');
        });


        var loading = false;
        var hwCallback = {
            getProvinceOptions: function(result) {
                if (result != false) {
                    province_obj = document.getElementById('province');
                    selected_province = document.getElementById('selected_province').value;

                    populateSelectBox(province_obj, result, selected_province);
                }
                loading = false;
            }
        }


        function showProvince() {
            if (document.getElementById('selected_tab').value != '') {
                country = document.getElementById('country').value;
                remoteHW.getProvinceOptions(country);
            }
        }

    </script>
</x-app-layout>
