<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Company Information') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0 title-form">Edit Company Details</h4>
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
                        action="{{ isset($company_data['id']) ? route('company.save', ['id' => $company_data['id']]) : route('company.save') }}"
                        id="company_form" enctype="multipart/form-data">
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
                                    <select class="form-select w-50" id="product_edition_id"
                                        name="data[product_edition]">
                                        @foreach ($company_data['product_edition_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['product_edition']) && $company_data['product_edition'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="name" class="form-label req mb-1 col-md-3">Company Full Name</label>
                                <div class="col-md-9">
                                    <input type="text" name="data[name]" class="form-control w-50" id="name"
                                        placeholder="Enter Company Full Name" value="{{ $company_data['name'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="short_name" class="form-label req mb-1 col-md-3">Company Short Name</label>
                                <div class="col-md-9">
                                    <input type="text" name="data[short_name]" class="form-control w-50"
                                        id="short_name" placeholder="Enter Company Short Name"
                                        value="{{ $company_data['short_name'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="industry_id" class="form-label req mb-1 col-md-3">Industry</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="industry_id" name="data[industry_id]">
                                        @foreach ($company_data['industry_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['industry']) && $company_data['industry'] == $value ? 'selected' : '' }}>
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
                                    <input type="text" name="data[business_number]" class="form-control w-50"
                                        id="business_number"
                                        placeholder="Enter Business / Employer Identification Number"
                                        value="{{ $company_data['business_number'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="address1" class="form-label req mb-1 col-md-3">Address (Line 1)</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="address1" name="data[address1]"
                                        placeholder="Enter Address" value="{{ $company_data['address1'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="address_2" class="form-label mb-1 col-md-3">Address (Line 2)</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="address_2"
                                        name="data[address_2]" placeholder="Enter Address"
                                        value="{{ $company_data['address2'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="country" class="form-label req mb-1 col-md-3">Country</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="country" name="data[country]">
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
                                    <select class="form-select w-50" id="province" name="data[province]">
                                        @foreach ($company_data['province_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($company_data['province']) && $company_data['province'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="city" class="form-label req mb-1 col-md-3">City</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="city" name="data[city]"
                                        placeholder="Enter City" value="{{ $company_data['city'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="postal_code" class="form-label req mb-1 col-md-3">Postal / ZIP
                                    Code</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="postal_code"
                                        name="data[postal_code]" placeholder="Enter Postal Code"
                                        value="{{ $company_data['postal_code'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="work_phone" class="form-label req mb-1 col-md-3">Phone</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="work_phone"
                                        name="data[work_phone]" placeholder="Enter Phone No"
                                        value="{{ $company_data['work_phone'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="fax_phone" class="form-label req mb-1 col-md-3">Fax</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="fax_phone"
                                        name="data[fax_phone]" placeholder="Enter Fax No"
                                        value="{{ $company_data['fax_phone'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="epf_number" class="form-label req mb-1 col-md-3">EPF Reg No</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="epf_number"
                                        name="data[epf_number]" placeholder="Enter EPF Reg No"
                                        value="{{ $company_data['epf_number'] ?? '' }}">
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="admin_contact" class="form-label req mb-1 col-md-3">Primary Support
                                    Contact</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="admin_contact" name="data[admin_contact]">
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
                                    <select class="form-select w-50" id="billing_contact"
                                        name="data[billing_contact]">
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
                                    <select class="form-select w-50" id="support_contact"
                                        name="data[support_contact]">
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
                                    <input type="text" class="form-control w-50" id="other_id1"
                                        name="data[other_id1]" placeholder="Enter" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id2" class="form-label mb-1 col-md-3">222</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id2"
                                        name="data[other_id2]" placeholder="Enter" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id3" class="form-label mb-1 col-md-3">333</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id3"
                                        name="data[other_id3]" placeholder="Enter" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id4" class="form-label mb-1 col-md-3">444</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id4"
                                        name="data[other_id4]" placeholder="Enter" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id5" class="form-label mb-1 col-md-3">555</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id5"
                                        name="data[other_id5]" placeholder="Enter" value="">
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
                                            name="data[originator_id]" placeholder="Enter Originator ID"
                                            value="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="data_center_id" class="form-label req mb-1 col-md-3">Data Center /
                                        Immediate Destination</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="data_center_id"
                                            name="data[data_center_id]" placeholder="Enter Data Center"
                                            value="">
                                    </div>
                                </div>

                            </div>

                        </div>


                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Company Settings
                            </h5>

                            <div class="px-4 py-2">

                                {{-- <div class="row mb-3">
                                    <label for="company_logo" class="form-label mb-1 col-md-3">Company Logo</label>
                                    <div class="col-md-9 d-flex align-items-center">
                                        <input type="file" class="form-control w-50 me-3" id="company_logo"
                                            name="company_logo" accept="image/*" onchange="previewLogo(event)">
                                        <img id="company_logo_i"
                                            src="{{ isset($company_data['company_logo']) ? $company_data['company_logo'] : '' }}"
                                            alt="Company Logo"
                                            style="max-width: 100px; border: 1px solid #ccc; display: {{ isset($company_data['company_logo']) && $company_data['company_logo'] ? 'block' : 'none' }}; padding: 2px; border-radius: 5px;" />
                                    </div>
                                    @error('company_logo')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div> --}}

                                <div class="row mb-3">
                                    <label for="company_logo" class="form-label mb-1 col-md-3">Company Logo</label>
                                    <div class="col-md-9 d-flex align-items-center">
                                        <input type="file" class="form-control w-50 me-3" id="company_logo"
                                            name="company_logo" accept="image/*" onchange="previewLogo(event)">
                                        <img id="company_logo_i" src="{{ $company_data['company_logo'] ?? '' }}"
                                            alt="Company Logo"
                                            style="max-width: 100px; border: 1px solid #ccc; {{ isset($company_data['company_logo']) && $company_data['company_logo'] ? 'display: block' : 'display: none' }}; padding: 2px; border-radius: 5px;" />
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
                                            name="data[enable_second_last_name]">
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
                                    <label for="ldap_authentication_type_id" class="form-label req mb-1 col-md-3">LDAP
                                        Authentication</label>
                                    <div class="col-md-9">
                                        <select class="form-select w-50" id="ldap_authentication_type_id"
                                            name="data[ldap_authentication_type_id]">
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

                            <div class="px-4 py-2" style="display: none;">
                                @if (!extension_loaded('ldap'))
                                    <div class="alert alert-danger mb-3">
                                        <strong>{{ __('ERROR') }}:</strong>
                                        {{ __('LDAP extension for PHP is not installed! LDAP authentication will not function.') }}
                                    </div>
                                @endif

                                <div class="row mb-3">
                                    <label for="ldap_host" class="form-label req mb-1 col-md-3">Server</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="ldap_host"
                                            name="data[ldap_host]"
                                            placeholder="Enter LDAP Server (e.g., ldap.example.com)"
                                            value="{{ old('data.ldap_host', $company_data['ldap_host'] ?? '') }}">
                                        <small class="form-text text-muted">
                                            (e.g., ldap.example.com or ldaps://ldap.example.com for SSL)
                                        </small>
                                        @error('data.ldap_host')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="ldap_port" class="form-label req mb-1 col-md-3">Port</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="ldap_port"
                                            name="data[ldap_port]" placeholder="Enter LDAP Port (e.g., 389)"
                                            value="{{ old('data.ldap_port', $company_data['ldap_port'] ?? '') }}">
                                        <small class="form-text text-muted">
                                            (e.g., 389 or 636 for SSL)
                                        </small>
                                        @error('data.ldap_port')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="ldap_bind_user_name" class="form-label req mb-1 col-md-3">Bind User
                                        Name</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="ldap_bind_user_name"
                                            name="data[ldap_bind_user_name]" placeholder="Enter Bind User Name"
                                            value="{{ old('data.ldap_bind_user_name', $company_data['ldap_bind_user_name'] ?? '') }}">
                                        <small class="form-text text-muted">
                                            (Used to search for the user, leave blank for anonymous binding)
                                        </small>
                                        @error('data.ldap_bind_user_name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="ldap_bind_password" class="form-label req mb-1 col-md-3">Bind
                                        Password</label>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control w-50" id="ldap_bind_password"
                                            name="data[ldap_bind_password]" placeholder="Enter Bind Password"
                                            value="{{ old('data.ldap_bind_password', $company_data['ldap_bind_password'] ?? '') }}">
                                        @error('data.ldap_bind_password')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="ldap_base_dn" class="form-label req mb-1 col-md-3">Base DN</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="ldap_base_dn"
                                            name="data[ldap_base_dn]"
                                            placeholder="Enter Base DN (e.g., ou=People,dc=example,dc=com)"
                                            value="{{ old('data.ldap_base_dn', $company_data['ldap_base_dn'] ?? '') }}">
                                        <small class="form-text text-muted">
                                            (e.g., ou=People,dc=example,dc=com)
                                        </small>
                                        @error('data.ldap_base_dn')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="ldap_bind_attribute" class="form-label req mb-1 col-md-3">Bind
                                        Attribute</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="ldap_bind_attribute"
                                            name="data[ldap_bind_attribute]"
                                            placeholder="Enter Bind Attribute (e.g., userPrincipalName)"
                                            value="{{ old('data.ldap_bind_attribute', $company_data['ldap_bind_attribute'] ?? '') }}">
                                        <small class="form-text text-muted">
                                            (For binding the LDAP user, e.g., AD/openLDAP: userPrincipalName, Mac OSX:
                                            uid)
                                        </small>
                                        @error('data.ldap_bind_attribute')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="ldap_user_filter" class="form-label req mb-1 col-md-3">User
                                        Filter</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="ldap_user_filter"
                                            name="data[ldap_user_filter]"
                                            placeholder="Enter User Filter (e.g., is_timetrex_user=1)"
                                            value="{{ old('data.ldap_user_filter', $company_data['ldap_user_filter'] ?? '') }}">
                                        <small class="form-text text-muted">
                                            (Additional filter parameters, e.g., is_timetrex_user=1)
                                        </small>
                                        @error('data.ldap_user_filter')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="ldap_login_attribute" class="form-label req mb-1 col-md-3">Login
                                        Attribute</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="ldap_login_attribute"
                                            name="data[ldap_login_attribute]"
                                            placeholder="Enter Login Attribute (e.g., sAMAccountName)"
                                            value="{{ old('data.ldap_login_attribute', $company_data['ldap_login_attribute'] ?? '') }}">
                                        <small class="form-text text-muted">
                                            (For searching the LDAP user, e.g., AD: sAMAccountName, openLDAP: dn, Mac
                                            OSX: dn)
                                        </small>
                                        @error('data.ldap_login_attribute')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                        </div>

                        <div class="form-group text-center mt-4">
                            <input type="hidden" name="id" value="{{ $company_data['id'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
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
            }
        }
    </script>
</x-app-layout>
