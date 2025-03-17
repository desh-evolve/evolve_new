<x-app-layout :title="'Edit Branch'">
    <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        /* Flexbox to center content */
        .center-container {
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            min-height: 100vh; /* Full viewport height */
        }
    </style>

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Branches') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-50"> <!-- Adjust width as needed -->
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Branch {{ isset($branch_data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="/branch" class="btn btn-primary">Branches List <i class="ri-arrow-right-line"></i></a>
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

                <form method="POST" action="{{ isset($branch_data['id']) ? route('branch.save', $branch_data['id']) : route('branch.save') }}">
                    @csrf

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-select">
                            @foreach ($branch_data['status_options'] as $value => $label)
                                <option value="{{ $value }}" {{ isset($branch_data['status']) && $branch_data['status'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $branch_data['name'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="branch_short_id">Branch Short ID</label>
                        <input type="text" name="branch_short_id" id="branch_short_id" class="form-control" value="{{ $branch_data['branch_short_id'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="manual_id">Code</label>
                        <input type="text" name="manual_id" id="manual_id" class="form-control" value="{{ $branch_data['manual_id'] ?? $branch_data['next_available_manual_id'] }}">
                        @if ($branch_data['next_available_manual_id'] != '')
                            <small class="form-text text-muted">Next available code: {{ $branch_data['next_available_manual_id'] }}</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="address1">Address (Line 1)</label>
                        <input type="text" name="address1" id="address1" class="form-control" value="{{ $branch_data['address1'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="address2">Address (Line 2)</label>
                        <input type="text" name="address2" id="address2" class="form-control" value="{{ $branch_data['address2'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ $branch_data['city'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="country">Country</label>
                        <select name="country" id="country" class="form-select" onchange="showProvince()">
                            @foreach ($branch_data['country_options'] as $value => $label)
                                <option value="{{ $value }}" {{ isset($branch_data['country']) && $branch_data['country'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="province">Province / State</label>
                        <select name="province" id="province" class="form-select">
                            @foreach ($branch_data['province_options'] as $value => $label)
                                <option value="{{ $value }}" {{ isset($branch_data['province']) && $branch_data['province'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" id="selected_province" value="{{ $branch_data['province'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="postal_code">Postal / ZIP Code</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ $branch_data['postal_code'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="work_phone">Phone</label>
                        <input type="text" name="work_phone" id="work_phone" class="form-control" value="{{ $branch_data['work_phone'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="fax_phone">Fax</label>
                        <input type="text" name="fax_phone" id="fax_phone" class="form-control" value="{{ $branch_data['fax_phone'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="business_reg_no">Business Registration No</label>
                        <input type="text" name="business_reg_no" id="business_reg_no" class="form-control" value="{{ $branch_data['business_reg_no'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="tin_no">TIN No</label>
                        <input type="text" name="tin_no" id="tin_no" class="form-control" value="{{ $branch_data['tin_no'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="epf_no">EPF No</label>
                        <input type="text" name="epf_no" id="epf_no" class="form-control" value="{{ $branch_data['epf_no'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="etf_no">ETF No</label>
                        <input type="text" name="etf_no" id="etf_no" class="form-control" value="{{ $branch_data['etf_no'] ?? '' }}">
                    </div>

                    @if (isset($branch_data['other_field_names']['other_id1']))
                        <div class="form-group">
                            <label for="other_id1">{{ $branch_data['other_field_names']['other_id1'] }}</label>
                            <input type="text" name="other_id1" id="other_id1" class="form-control" value="{{ $branch_data['other_id1'] ?? '' }}">
                        </div>
                    @endif

                    @if (isset($branch_data['other_field_names']['other_id2']))
                        <div class="form-group">
                            <label for="other_id2">{{ $branch_data['other_field_names']['other_id2'] }}</label>
                            <input type="text" name="other_id2" id="other_id2" class="form-control" value="{{ $branch_data['other_id2'] ?? '' }}">
                        </div>
                    @endif

                    @if (isset($branch_data['other_field_names']['other_id3']))
                        <div class="form-group">
                            <label for="other_id3">{{ $branch_data['other_field_names']['other_id3'] }}</label>
                            <input type="text" name="other_id3" id="other_id3" class="form-control" value="{{ $branch_data['other_id3'] ?? '' }}">
                        </div>
                    @endif

                    @if (isset($branch_data['other_field_names']['other_id4']))
                        <div class="form-group">
                            <label for="other_id4">{{ $branch_data['other_field_names']['other_id4'] }}</label>
                            <input type="text" name="other_id4" id="other_id4" class="form-control" value="{{ $branch_data['other_id4'] ?? '' }}">
                        </div>
                    @endif

                    @if (isset($branch_data['other_field_names']['other_id5']))
                        <div class="form-group">
                            <label for="other_id5">{{ $branch_data['other_field_names']['other_id5'] }}</label>
                            <input type="text" name="other_id5" id="other_id5" class="form-control" value="{{ $branch_data['other_id5'] ?? '' }}">
                        </div>
                    @endif

                    <div class="form-group text-center">
                        <input type="hidden" name="id" value="{{ $branch_data['id'] ?? '' }}">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>

            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end center-container -->

    <script>
        function showProvince() {
            var country = document.getElementById('country').value;
            var selectedProvince = document.getElementById('selected_province').value;

            fetch(`/get-provinces?country=${country}`)
                .then(response => response.json())
                .then(data => {
                    var provinceSelect = document.getElementById('province');
                    provinceSelect.innerHTML = '';

                    data.forEach(province => {
                        var option = document.createElement('option');
                        option.value = province.value;
                        option.text = province.label;
                        if (province.value == selectedProvince) {
                            option.selected = true;
                        }
                        provinceSelect.appendChild(option);
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            showProvince();
        });
    </script>
</x-app-layout>