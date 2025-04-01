<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('New Hire Defaults') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0 title-form">Add New Hire Defaults</h4>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif


                    <form method="POST"
                        action="{{ isset($user_data['id']) ? route('new_hire_defaults.save', $user_data['id']) : route('new_hire_defaults.save') }}"
                        id="companyFormID">
                        @csrf

                        <h5 class="bg-primary text-white p-1 mb-3">
                            Employee Identification
                        </h5>

                        <div class="px-4 py-2">

                            <div class="row mb-3">
                                <label for="company" class="form-label req mb-1 col-md-3">Company Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="company" name="company" placeholder="Enter Company Name" value="{{ $user_data['company'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="permission_control_id" class="form-label req mb-1 col-md-3">Permission Group</label>
                                <div class="col-md-9">
                                    <select name="permission_control_id" class="form-select w-50" id="permission_control_id">
                                        @foreach ($user_data['permission_control_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['permission_control_id']) && $user_data['permission_control_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="pay_period_schedule_id" class="form-label req mb-1 col-md-3">Pay Period Schedule</label>
                                <div class="col-md-9">
                                    <select name="pay_period_schedule_id" class="form-select w-50" id="pay_period_schedule_id">
                                        @foreach ($user_data['permission_control_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['permission_control_id']) && $user_data['permission_control_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="policy_group_id" class="form-label req mb-1 col-md-3">Policy Group</label>
                                <div class="col-md-9">
                                    <select name="policy_group_id" class="form-select w-50" id="policy_group_id">
                                        @foreach ($user_data['permission_control_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['permission_control_id']) && $user_data['permission_control_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="currency_id" class="form-label req mb-1 col-md-3">Currency</label>
                                <div class="col-md-9">
                                    <select name="currency_id" class="form-select w-50" id="currency_id">
                                        @foreach ($user_data['permission_control_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['permission_control_id']) && $user_data['permission_control_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="title" class="form-label req mb-1 col-md-3">Title</label>
                                <div class="col-md-9">
                                    <select name="title" class="form-select w-50" id="title">
                                        @foreach ($user_data['permission_control_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['permission_control_id']) && $user_data['permission_control_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="employee_number" class="form-label req mb-1 col-md-3">Employee Number</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="employee_number"
                                           name="employee_number" placeholder="Enter Employee Number"
                                           value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="hire_date" class="form-label req mb-1 col-md-3">Appointment Date</label>
                                <div class="col-md-9">
                                    <input type="Date" class="form-control w-50" id="hire_date" name="hire_date" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="default_branch_id" class="form-label req mb-1 col-md-3">Default Branch</label>
                                <div class="col-md-9">
                                    <select name="default_branch_id" class="form-select w-50" id="default_branch_id">

                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="default_department_id" class="form-label req mb-1 col-md-3">Default Department</label>
                                <div class="col-md-9">
                                    <select name="default_department_id" class="form-select w-50" id="default_department_id">

                                    </select>
                                </div>
                            </div>

                        </div>

                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Contact Information
                            </h5>

                            <div class="px-4 py-2">

                                <div class="row mb-3">
                                    <label for="city" class="form-label req mb-1 col-md-3">City</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="city" name="city" placeholder="Enter City" value="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="country" class="form-label req mb-1 col-md-3">Country</label>
                                    <div class="col-md-9">
                                        <select name="country" class="form-select w-50" id="country" onchange="showProvince()">

                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="province" class="form-label req mb-1 col-md-3">Province/State</label>
                                    <div class="col-md-9">
                                        <select name="province" class="form-select w-50" id="province">

                                        </select>
                                        <input type="hidden" id="selected_province" value="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="work_phone" class="form-label req mb-1 col-md-3">Work Phone</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="work_phone" name="work_phone" placeholder="Enter Phone No" value="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="work_phone_ext" class="form-label req mb-1 col-md-3">Ext</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="work_phone_ext" name="work_phone_ext" placeholder="Enter Ext" value="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="work_email" class="form-label req mb-1 col-md-3">Email</label>
                                    <div class="col-md-9">
                                        <input type="email" class="form-control w-50" id="work_email" name="work_email" placeholder="Enter Email" value="">
                                    </div>
                                </div>

                            </div>

                        </div>


                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Employee Preferences
                            </h5>

                            <div class="px-4 py-2">

                                <div class="row mb-3">
                                    <label for="language" class="form-label req mb-1 col-md-3">Language</label>
                                    <div class="col-md-9">
                                        <select name="language" class="form-select w-50" id="language">

                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="date_format" class="form-label req mb-1 col-md-3">Date Format</label>
                                    <div class="col-md-9">
                                        <select name="date_format" class="form-select w-50" id="date_format">

                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="time_format" class="form-label req mb-1 col-md-3">Time Format</label>
                                    <div class="col-md-9">
                                        <select name="time_format" class="form-select w-50" id="time_format">

                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="time_unit_format" class="form-label req mb-1 col-md-3">Time Units</label>
                                    <div class="col-md-9">
                                        <select name="time_unit_format" class="form-select w-50" id="time_unit_format">

                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="time_zone" class="form-label req mb-1 col-md-3">Time Zone</label>
                                    <div class="col-md-9">
                                        <select name="time_zone" class="form-select w-50" id="time_zone">

                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="start_week_day" class="form-label req mb-1 col-md-3">Start Weeks on</label>
                                    <div class="col-md-9">
                                        <select name="start_week_day" class="form-select w-50" id="start_week_day">

                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="items_per_page" class="form-label req mb-1 col-md-3">Rows per Page</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="items_per_page" name="items_per_page" placeholder="Enter Rows per Page" value="">
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Email Notifications
                            </h5>

                            <div class="px-4 py-2">

                                <div class="row mb-3">
                                    <label for="enable_email_notification_exception" class="form-label req mb-1 col-md-3">Exceptions</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_exception" name="enable_email_notification_exception" value="1">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="enable_email_notification_message" class="form-label req mb-1 col-md-3">Messages</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_message" name="enable_email_notification_message" value="1">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="enable_email_notification_home" class="form-label req mb-1 col-md-3">Send Notifications to Home Email</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_home" name="enable_email_notification_home" value="1">
                                    </div>
                                </div>

                            </div>

                        </div>


                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Employee Tax / Deductions
                            </h5>

                            <div class="px-4 py-2">

                                <div class="row mb-3">
                                    <label for="company_deduction_ids" class="form-label mb-1 col-md-3">Deductions</label>
                                    <div class="col-md-9">
                                        <select name="company_deduction_ids" class="form-select w-50" id="company_deduction_ids" multiple>

                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="new_user_default_id" value="">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
