<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('New Hire Defaults') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0 title-form">{{ $title }}</h4>
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
                                    <input type="text" class="form-control w-50" id="company_id"
                                            name="company" placeholder="Enter Company Name"
                                            value="">
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
                                        @foreach ($user_data['pay_period_schedule_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['pay_period_schedule_id']) && $user_data['pay_period_schedule_id'] == $value ? 'selected' : '' }}>
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
                                        @foreach ($user_data['policy_group_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['policy_group_id']) && $user_data['policy_group_id'] == $value ? 'selected' : '' }}>
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
                                        @foreach ($user_data['currency_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['currency_id']) && $user_data['currency_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="title_id" class="form-label req mb-1 col-md-3">Title</label>
                                <div class="col-md-9">
                                    <select name="title_id" class="form-select w-50" id="title_id">
                                        @foreach ($user_data['title_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['title_id']) && $user_data['title_id'] == $value ? 'selected' : '' }}>
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
                                           value="{{ $user_data['employee_number'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="hire_date" class="form-label req mb-1 col-md-3">Appointment Date</label>
                                <div class="col-md-9">
                                    <input type="Date" class="form-control w-50" id="hire_date" name="hire_date" value="{{ $user_data['hire_date'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="default_branch_id" class="form-label req mb-1 col-md-3">Default Branch</label>
                                <div class="col-md-9">
                                    <select name="default_branch_id" class="form-select w-50" id="default_branch_id">
                                        @foreach ($user_data['branch_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['default_branch_id']) && $user_data['default_branch_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="default_department_id" class="form-label req mb-1 col-md-3">Default Department</label>
                                <div class="col-md-9">
                                    <select name="default_department_id" class="form-select w-50" id="default_department_id">
                                        @foreach ($user_data['department_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($user_data['default_department_id']) && $user_data['default_department_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
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
                                        <input type="text" class="form-control w-50" id="city" name="city" placeholder="Enter City" value="{{ $user_data['city'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="country" class="form-label req mb-1 col-md-3">Country</label>
                                    <div class="col-md-9">
                                        <select name="country" class="form-select w-50" id="country" onchange="showProvince()">
                                            @foreach ($user_data['country_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['country']) && $user_data['country'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="province" class="form-label req mb-1 col-md-3">Province/State</label>
                                    <div class="col-md-9">
                                        <select name="province" class="form-select w-50" id="province">

                                        </select>
                                        <input type="hidden" id="selected_province" value="{{ $company_data['province'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="work_phone" class="form-label req mb-1 col-md-3">Work Phone</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="work_phone" name="work_phone" placeholder="Enter Phone No" value="{{ $user_data['work_phone'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="work_phone_ext" class="form-label req mb-1 col-md-3">Ext</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="work_phone_ext" name="work_phone_ext" placeholder="Enter Ext" value="{{ $user_data['work_phone_ext'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="work_email" class="form-label req mb-1 col-md-3">Email</label>
                                    <div class="col-md-9">
                                        <input type="email" class="form-control w-50" id="work_email" name="work_email" placeholder="Enter Email" value="{{ $user_data['work_email'] ?? '' }}">
                                    </div>
                                </div>

                            </div>

                        </div>


                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Employee Preferences
                            </h5>

                            <div class="px-4 py-2">

                                {{-- <div class="row mb-3">
                                    <label for="language" class="form-label req mb-1 col-md-3">Language</label>
                                    <div class="col-md-9">
                                        <select name="language" class="form-select w-50" id="language" onchange = "showDateFormat()">
                                            @foreach ($user_data['language_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['language']) && $user_data['language'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="date_format" class="form-label req mb-1 col-md-3">Date Format</label>
                                    <div class="col-md-9">
                                        <select name="date_format" class="form-select w-50" id="date_format">
                                            @foreach ($user_data['date_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['date_format']) && $user_data['date_format'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div> --}}

                                <div class="row mb-3">
                                    <label for="language" class="form-label req mb-1 col-md-3">Language</label>
                                    <div class="col-md-9">
                                        <select name="language" class="form-select w-50" id="language" onchange="showDateFormat()">
                                            @foreach ($user_data['language_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['language']) && $user_data['language'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="date_format" class="form-label req mb-1 col-md-3">Date Format</label>
                                    <div class="col-md-9">
                                        <select name="date_format" class="form-select w-50" id="date_format"
                                            style="{{ isset($user_data['language']) && $user_data['language'] == 'en' ? '' : 'display:none;' }}">
                                            @foreach ($user_data['date_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['date_format']) && $user_data['date_format'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="other_date_format" class="form-select w-50" id="other_date_format"
                                            style="{{ isset($user_data['language']) && $user_data['language'] == 'en' ? 'display:none;' : '' }}">
                                            @foreach ($user_data['other_date_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['other_date_format']) && $user_data['other_date_format'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="time_format" class="form-label req mb-1 col-md-3">Time Format</label>
                                    <div class="col-md-9">
                                        <select name="time_format" class="form-select w-50" id="time_format">
                                            @foreach ($user_data['time_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['time_format']) && $user_data['time_format'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="time_unit_format" class="form-label req mb-1 col-md-3">Time Units</label>
                                    <div class="col-md-9">
                                        <select name="time_unit_format" class="form-select w-50" id="time_unit_format">
                                            @foreach ($user_data['time_unit_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['time_unit_format']) && $user_data['time_unit_format'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="time_zone" class="form-label req mb-1 col-md-3">Time Zone</label>
                                    <div class="col-md-9">
                                        <select name="time_zone" class="form-select w-50" id="time_zone">
                                            @foreach ($user_data['time_zone_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['time_zone']) && $user_data['time_zone'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="start_week_day" class="form-label req mb-1 col-md-3">Start Weeks on</label>
                                    <div class="col-md-9">
                                        <select name="start_week_day" class="form-select w-50" id="start_week_day">
                                            @foreach ($user_data['start_week_day_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['start_week_day']) && $user_data['start_week_day'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="items_per_page" class="form-label req mb-1 col-md-3">Rows per Page</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="items_per_page" name="items_per_page" placeholder="Enter Rows per Page" value="{{ $user_data['items_per_page'] ?? '' }}">
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
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_exception" name="enable_email_notification_exception" value="1" {{ isset($user_data['enable_email_notification_exception']) && $user_data['enable_email_notification_exception'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="enable_email_notification_message" class="form-label req mb-1 col-md-3">Messages</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_message" name="enable_email_notification_message" value="1" {{ isset($user_data['enable_email_notification_message']) && $user_data['enable_email_notification_message'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="enable_email_notification_home" class="form-label req mb-1 col-md-3">Send Notifications to Home Email</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_home" name="enable_email_notification_home" value="1" {{ isset($user_data['enable_email_notification_home']) && $user_data['enable_email_notification_home'] ? 'checked' : '' }}>
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
                                            @foreach ($user_data['company_deduction_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($user_data['company_deduction_ids']) && $user_data['company_deduction_ids'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="new_user_default_id" value="{{ $user_data['id'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('company_id').value = "{{ $user_data['company'] ?? '' }}";


       // Assuming `province_options` is passed from the backend like below
       var provinceOptions = @json($user_data['province_options']);

        function showProvince() {
            var country = document.getElementById('country').value; // Get selected country
            var provinceDropdown = document.getElementById('province'); // Get province dropdown
            var selectedProvince = document.getElementById('selected_province').value; // Get the selected province value

            // Clear the current province options
            provinceDropdown.innerHTML = '';

            if (country in provinceOptions) {
                // If provinces are available for the selected country
                var provinces = provinceOptions[country];

                // Loop through and add each province option
                for (var provinceCode in provinces) {
                    var option = document.createElement('option');
                    option.value = provinceCode;
                    option.text = provinces[provinceCode];

                    // If the province is already selected, set it as selected
                    if (provinceCode == selectedProvince) {
                        option.selected = true;
                    }

                    provinceDropdown.appendChild(option);
                }
            }
        }


        function populateSelectBox(selectObj, options, selectedValue) {
            selectObj.innerHTML = ""; // Clear current options
            options.forEach(function(option) {
                let opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.label;
                if (option.value === selectedValue) {
                    opt.selected = true;
                }
                selectObj.appendChild(opt);
            });
        }


        function showDateFormat() {
            var lang = document.getElementById('language').value;

            if (lang === 'en') {
                document.getElementById('date_format').style.display = '';
                document.getElementById('other_date_format').style.display = 'none';
            } else {
                document.getElementById('other_date_format').style.display = '';
                document.getElementById('date_format').style.display = 'none';
            }
        }

        // Function to populate the province dropdown when the page loads
        window.onload = function() {
            // Trigger showProvince on page load to set the initial provinces
            showProvince();
            showDateFormat();

            // Set the initial selected country in the dropdown
            var selectedCountry = document.getElementById('country').value;
            if (selectedCountry) {
                showProvince(); // Update province dropdown based on selected country
            }
        };

    </script>

</x-app-layout>
