<x-app-layout :title="$title">
    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                        <h5 class="mt-2">Company: {{ $company_name }} | Generated: {{ date('Y-m-d H:i:s', $generated_time) }}</h5>
                    </div>
                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="{{ route('employee_detail.index') }}"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1">
                                Back to Filters <i class="ri-arrow-left-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="report_table" class="table nowrap align-middle" style="width:100%">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Employee #</th>
                                    <th>Status</th>
                                    <th>User Name</th>
                                    <th>Phone ID</th>
                                    <th>iButton</th>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Last Name</th>
                                    <th>SIN/SSN</th>
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
                                    <th>Currency</th>
                                    <th>Wage Type</th>
                                    <th>Wage</th>
                                    <th>Effective Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row['employee_number'] ?? '' }}</td>
                                        <td>{{ $row['status'] ?? '' }}</td>
                                        <td>{{ $row['user_name'] ?? '' }}</td>
                                        <td>{{ $row['phone_id'] ?? '' }}</td>
                                        <td>{{ $row['ibutton_id'] ?? '' }}</td>
                                        <td>{{ $row['first_name'] ?? '' }}</td>
                                        <td>{{ $row['middle_name'] ?? '' }}</td>
                                        <td>{{ $row['last_name'] ?? '' }}</td>
                                        <td>{{ $row['sin'] ?? '' }}</td>
                                        <td>{{ $row['title'] ?? '' }}</td>
                                        <td>{{ $row['group'] ?? '' }}</td>
                                        <td>{{ $row['default_branch'] ?? '' }}</td>
                                        <td>{{ $row['default_department'] ?? '' }}</td>
                                        <td>{{ $row['sex'] ?? '' }}</td>
                                        <td>{{ $row['address1'] ?? '' }}</td>
                                        <td>{{ $row['address2'] ?? '' }}</td>
                                        <td>{{ $row['city'] ?? '' }}</td>
                                        <td>{{ $row['province'] ?? '' }}</td>
                                        <td>{{ $row['country'] ?? '' }}</td>
                                        <td>{{ $row['postal_code'] ?? '' }}</td>
                                        <td>{{ $row['work_phone'] ?? '' }}</td>
                                        <td>{{ $row['home_phone'] ?? '' }}</td>
                                        <td>{{ $row['mobile_phone'] ?? '' }}</td>
                                        <td>{{ $row['fax_phone'] ?? '' }}</td>
                                        <td>{{ $row['home_email'] ?? '' }}</td>
                                        <td>{{ $row['work_email'] ?? '' }}</td>
                                        <td>{{ $row['birth_date'] ?? '' }}</td>
                                        <td>{{ $row['hire_date'] ?? '' }}</td>
                                        <td>{{ $row['termination_date'] ?? '' }}</td>
                                        <td>{{ $row['currency'] ?? '' }}</td>
                                        <td>{{ $row['wage_type'] ?? '' }}</td>
                                        <td>{{ $row['wage'] ?? '' }}</td>
                                        <td>{{ $row['effective_date'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                function initTable() {
                    new DataTable("#report_table", {
                        scrollX: true,
                        dom: "Bfrtip",
                        buttons: ["copy", "csv", "excel", "print", "pdf"],
                        //fixedHeader: true
                    });
                }

                initTable();
            });
        </script>
    @endpush
</x-app-layout>