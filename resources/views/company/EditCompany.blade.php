<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Company Information') }}</h4>
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


                    {{-- <form method="POST"
                        action="{{ isset($company_data['id']) ? route('company.save', $company_data['id']) : route('company.save') }}" id="companyFormID">
                        @csrf --}}

                    <form method="POST"
                        action="{{ isset($company_data['id']) ? route('company.save', $company_data['id']) : route('company.save') }}"
                        id="company_form" enctype="multipart/form-data">
                        @csrf

                        <div class="px-4 py-2">

                            <div class="row mb-3">
                                <label for="product_edition_id" class="form-label req mb-1 col-md-3">Product
                                    Edition</label>
                                <div class="col-md-9">
                                    <select name="product_edition_id" class="form-select w-50" id="product_edition_id"
                                        onchange="setName(this)">
                                        @foreach ($company_data['product_edition_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['product_edition_id']) && $company_data['product_edition_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="name" class="form-label req mb-1 col-md-3">Company Full Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="name" name="name"
                                        placeholder="Enter Company Full Name" value="{{ $company_data['name'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="short_name" class="form-label req mb-1 col-md-3">Company Short Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="short_name" name="short_name"
                                        placeholder="Enter Company Short Name"
                                        value="{{ $company_data['short_name'] ?? '' }}">
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="industry_id" class="form-label req mb-1 col-md-3">Industry</label>
                                <div class="col-md-9">
                                    <select name="industry_id" class="form-select w-50" id="industry_id">
                                        @foreach ($company_data['industry_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['industry_id']) && $company_data['industry_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="business_number" class="form-label req mb-1 col-md-3">Business / Employer
                                    Identification Number</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="business_number"
                                        name="business_number"
                                        placeholder="Enter Business / Employer Identification Number"
                                        value="{{ $company_data['business_number'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="address1" class="form-label req mb-1 col-md-3">Address (Line 1)</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="address1" name="address1"
                                        placeholder="Enter Address" value="{{ $company_data['address1'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="address2" class="form-label mb-1 col-md-3">Address (Line 2)</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="address2" name="address2"
                                        placeholder="Enter Address" value="{{ $company_data['address2'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="country" class="form-label req mb-1 col-md-3">Country</label>
                                <div class="col-md-9">
                                    <select name="country" class="form-select w-50" id="country"
                                        onchange="showProvince()">
                                        @foreach ($company_data['country_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['country']) && $company_data['country'] == $value ? 'selected' : '' }}>
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
                                    <input type="hidden" id="selected_province"
                                        value="{{ $company_data['province'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="city" class="form-label req mb-1 col-md-3">City</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="city" name="city"
                                        placeholder="Enter City" value="{{ $company_data['city'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="postal_code" class="form-label req mb-1 col-md-3">Postal / ZIP
                                    Code</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="postal_code"
                                        name="postal_code" placeholder="Enter Postal Code"
                                        value="{{ $company_data['postal_code'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="work_phone" class="form-label req mb-1 col-md-3">Phone</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="work_phone"
                                        name="work_phone" placeholder="Enter Phone No"
                                        value="{{ $company_data['work_phone'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="fax_phone" class="form-label req mb-1 col-md-3">Fax</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="fax_phone" name="fax_phone"
                                        placeholder="Enter Fax No" value="{{ $company_data['fax_phone'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="epf_number" class="form-label req mb-1 col-md-3">EPF Reg No</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="epf_number"
                                        name="epf_number" placeholder="Enter EPF Reg No"
                                        value="{{ $company_data['epf_number'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="admin_contact" class="form-label req mb-1 col-md-3">Administrative
                                    Contact</label>
                                <div class="col-md-9">
                                    <select name="admin_contact" class="form-select w-50" id="admin_contact">
                                        @foreach ($company_data['user_list_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['admin_contact']) && $company_data['admin_contact'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="billing_contact" class="form-label req mb-1 col-md-3">Billing
                                    Contact</label>
                                <div class="col-md-9">
                                    <select name="billing_contact" class="form-select w-50" id="billing_contact">
                                        @foreach ($company_data['user_list_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['billing_contact']) && $company_data['billing_contact'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="support_contact" class="form-label req mb-1 col-md-3">Primary Support
                                    Contact</label>
                                <div class="col-md-9">
                                    <select name="support_contact" class="form-select w-50" id="support_contact">
                                        @foreach ($company_data['user_list_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['support_contact']) && $company_data['support_contact'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id1" class="form-label mb-1 col-md-3">111</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id1" name="other_id1"
                                        placeholder="Enter" value="{{ $company_data['other_id1'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id2" class="form-label mb-1 col-md-3">222</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id2" name="other_id2"
                                        placeholder="Enter" value="{{ $company_data['other_id2'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id3" class="form-label mb-1 col-md-3">333</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id3" name="other_id3"
                                        placeholder="Enter" value="{{ $company_data['other_id3'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id4" class="form-label mb-1 col-md-3">444</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id4" name="other_id4"
                                        placeholder="Enter" value="{{ $company_data['other_id4'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id5" class="form-label mb-1 col-md-3">555</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id5" name="other_id5"
                                        placeholder="Enter" value="{{ $company_data['other_id5'] ?? '' }}">
                                </div>
                            </div>

                        </div>

                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Direct Deposit (EFT)
                            </h5>

                            <div class="px-4 py-2">

                                <div class="row mb-3">
                                    <label for="originator_id" class="form-label req mb-1 col-md-3">Originator ID /
                                        Immediate Origin</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="originator_id"
                                            name="originator_id" placeholder="Enter Originator ID"
                                            value="{{ $company_data['originator_id'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="data_center_id" class="form-label req mb-1 col-md-3">Data Center /
                                        Immediate Destination</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="data_center_id"
                                            name="data_center_id" placeholder="Enter Data Center"
                                            value="{{ $company_data['data_center_id'] ?? '' }}">
                                    </div>
                                </div>

                            </div>

                        </div>


                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Company Settings
                            </h5>

                            <div class="px-4 py-2">
                                <!-- Logo Upload -->
                                <!-- Logo Upload Section -->
                                <div class="row mb-3">
                                    <label for="company_logo" class="form-label mb-1 col-md-3">Company Logo</label>
                                    <div class="col-md-9 d-flex align-items-center">
                                        <input type="file" class="form-control w-50 me-3" id="company_logo"
                                            name="company_logo" accept="image/*" onchange="previewLogo(event)">
                                        <img id="company_logo_i"
                                            src="{{ isset($company_data['id']) ? route('company.logo', $company_data['id']) : '' }}"
                                            alt="Company Logo"
                                            style="max-width: 100px; border: 1px solid #ccc; {{ isset($company_data['id']) ? 'display: block' : 'display: none' }}; padding: 2px; border-radius: 5px;" />
                                    </div>
                                    @error('company_logo')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row mb-3">
                                    <label for="enable_second_last_name" class="form-label req mb-1 col-md-3">Enable
                                        Second Surname</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_second_last_name"
                                            name="enable_second_last_name" value="1"
                                            {{ isset($company_data['enable_second_last_name']) && $company_data['enable_second_last_name'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                LDAP Authentication
                            </h5>

                            <div class="px-4 py-2">

                                <div class="row mb-3">
                                    <label for="ldap_authentication_type_id" class="form-label mb-1 col-md-3">LDAP
                                        Authentication</label>
                                    <div class="col-md-9">
                                        <select name="ldap_authentication_type_id" class="form-select w-50"
                                            id="ldap_authentication_type_id">
                                            @foreach ($company_data['ldap_authentication_type_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($company_data['ldap_authentication_type_id']) && $company_data['ldap_authentication_type_id'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="company_id"
                                value="{{ $company_data['id'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Assuming `province_options` is passed from the backend like below
        var provinceOptions = @json($company_data['province_options']);

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

        function previewLogo(event) {
            const input = event.target;
            const preview = document.getElementById('company_logo_i');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                // If no file selected, show current logo again
                @if (isset($company_data['id']))
                    preview.src = "{{ route('company.logo', $company_data['id']) }}";
                @endif
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


        function setName(field) {
            // Check if it's a field that influences the 'name' field
            let nameField = document.getElementById('name');

            if (field) {
                // Dynamically change the 'name' field value based on the selected field's value
                let selectedValue = field.options[field.selectedIndex].text; // Use the option's text
                if (selectedValue) {
                    nameField.value = selectedValue;
                }
            }
        }

        // Function to populate the province dropdown when the page loads
        window.onload = function() {
            // Trigger showProvince on page load to set the initial provinces
            showProvince();

            // Set the initial selected country in the dropdown
            var selectedCountry = document.getElementById('country').value;
            if (selectedCountry) {
                showProvince(); // Update province dropdown based on selected country
            }
        };
        
    </script>

</x-app-layout>
