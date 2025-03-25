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

                    <form method="POST"
                        action="{{ isset($company_data['id']) ? route('company.save', ['id' => $company_data['id']]) : route('company.save') }}"
                        id="company_form" enctype="multipart/form-data">
                        @csrf

                        <div class="px-4 py-2">

                            <div class="row mb-3">
                                <label for="product_edition_id" class="form-label req mb-1 col-md-3">Product Edition</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="product_edition_id" name="product_edition_id">
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
                                    <input type="text" class="form-control w-50" id="name" placeholder="Enter Company Full Name" value="{{ $company_data['name'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="short_name" class="form-label req mb-1 col-md-3">Company Short Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="short_name" placeholder="Enter Company Short Name" value="{{ $company_data['short_name'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="industry_id" class="form-label req mb-1 col-md-3">Industry</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="industry_id">
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
                                <label for="short_name" class="form-label req mb-1 col-md-3">Business / Employer Identification Number</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="short_name" placeholder="Enter Business / Employer Identification Number" value="{{ $company_data['business_number'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="address1" class="form-label req mb-1 col-md-3">Address (Line 1)</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="address1" placeholder="Enter Address" value="{{ $company_data['address1'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="address_2" class="form-label mb-1 col-md-3">Address (Line 2)</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="address_2" placeholder="Enter Address" value="{{ $company_data['address2'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="country" class="form-label req mb-1 col-md-3">Country</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="country">
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
                                    <select class="form-select w-50" id="province">
                                        @foreach ($company_data['province'] as $value => $label)
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
                                    <input type="text" class="form-control w-50" id="city" placeholder="Enter City" value="{{ $company_data['city'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="postal_code" class="form-label req mb-1 col-md-3">Postal / ZIP Code</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="postal_code" placeholder="Enter Postal Code" value="{{ $company_data['postal_code'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="work_phone" class="form-label req mb-1 col-md-3">Phone</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="work_phone" placeholder="Enter Phone No" value="{{ $company_data['work_phone'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="fax_phone" class="form-label req mb-1 col-md-3">Fax</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="fax_phone" placeholder="Enter Fax No" value="{{ $company_data['fax_phone'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="epf_number" class="form-label req mb-1 col-md-3">EPF Reg No</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="epf_number" placeholder="Enter EPF Reg No" value="{{ $company_data['epf_number'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="admin_contact" class="form-label req mb-1 col-md-3">Administrative Contact</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="admin_contact">
                                        <option value="">Select Contact</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="billing_contact" class="form-label req mb-1 col-md-3">Billing Contact</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="billing_contact">
                                        <option value="">Select Contact</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="support_contact" class="form-label req mb-1 col-md-3">Primary Support Contact</label>
                                <div class="col-md-9">
                                    <select class="form-select w-50" id="support_contact">
                                        <option value="">Select Contact</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id1" class="form-label mb-1 col-md-3">111</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id1" placeholder="Enter" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id2" class="form-label mb-1 col-md-3">222</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id2" placeholder="Enter" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id3" class="form-label mb-1 col-md-3">333</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id3" placeholder="Enter" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id4" class="form-label mb-1 col-md-3">444</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id4" placeholder="Enter" value="">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id5" class="form-label mb-1 col-md-3">555</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id5" placeholder="Enter" value="">
                                </div>
                            </div>

                        </div>

                        <div>
                            <h5 class="bg-primary text-white p-1 mb-3">
                                Direct Deposit (EFT)
                            </h5>

                            <div class="px-4 py-2">

                                <div class="row mb-3">
                                    <label for="originator_id" class="form-label req mb-1 col-md-3">Originator ID / Immediate Origin</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="originator_id" placeholder="Enter Originator ID" value="">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="data_center_id" class="form-label req mb-1 col-md-3">Data Center / Immediate Destination</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="data_center_id" placeholder="Enter Data Center" value="">
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
                                <div class="row mb-3">
                                    <label for="company_logo" class="form-label mb-1 col-md-3">Company Logo</label>
                                    <div class="col-md-9 d-flex align-items-center">
                                        <input type="file" class="form-control w-50 me-3" id="company_logo" accept="image/*" onchange="previewLogo(event)">
                                        <img id="company_logo_i" src="" alt="Company Logo"
                                            style="max-width: 100px; border: 1px solid #ccc; display: none; padding: 2px; border-radius: 5px;" />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="enable_second_last_name" class="form-label req mb-1 col-md-3">Enable Second Surname</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_second_last_name">
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
                                    <label for="ldap_authentication_type_id" class="form-label mb-1 col-md-3">LDAP Authentication</label>
                                    <div class="col-md-9">
                                        <select class="form-select w-50" id="ldap_authentication_type_id">
                                            <option value="">Disabled</option>
                                            <option value="">Disabled</option>
                                            <option value="">Disabled</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" id="premium_policy_id" value="" />
                            <button type="button" id="form_submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
