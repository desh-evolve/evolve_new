<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Tax / Deduction List') }}</h4>
    </x-slot>

    @if (!empty($data['add']) && $data['add'] != 1)
        @include('company.EditCompanyDeduction_js', $data)
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card"> <!-- Adjust width as needed -->
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{ isset($data['id']) ? 'Edit' : 'Add' }} {{ $title }} </h4>
                    <a href="/user/tax" class="btn btn-primary">Employee Tax / Deduction <i class="ri-arrow-right-line"></i></a>
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

                    <form method="POST" action="{{ isset($data['id']) ? route('user.tax.save', $data['id']) : route('user.tax.save') }}">
                        @csrf

                        <div class="px-4 py-2">


                            @if($company_deduction_id == '')
                                <div class="row mb-3">
                                    <label class="form-label req mb-1 col-md-3">Employee</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" value="{{ $data['user_full_name'] ?? '' }}" disabled>
                                    </div>
                                </div>
                            @endif

                            @if (isset($data['add']) && $data['add'] == 1)

                                <div class="row mb-3">
                                    <label for="deduction_ids" class="form-label mb-1 col-md-3">Add Deductions</label>
                                    <div class="col-md-9">
                                        <select name="deduction_ids[]" class="form-select w-50" id="deduction_id" multiple>
                                            @foreach ($data['deduction_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($data['deduction_ids']) && $data['deduction_ids'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="add" value="1">
                                    </div>
                                </div>

                            @else

                                <div class="row mb-3">
                                    <label for="status" class="form-label req mb-1 col-md-3">Status</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" name="status" value="{{ $data['status'] ?? '' }}" disabled>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="type" class="form-label req mb-1 col-md-3">Type</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="type" name="type" placeholder="Enter type" value="{{ $data['type'] ?? '' }}" disabled>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="name" class="form-label req mb-1 col-md-3">Name</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="name" name="name" placeholder="Enter name" value="{{ $data['name'] ?? '' }}" disabled>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="calculation" class="form-label req mb-1 col-md-3">Calculation</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="calculation" name="calculation" value="{{ $data['calculation'] ?? '' }}" disabled>
                                    </div>
                                </div>

                                {{-- @if($data['country'] != '')
                                    <div class="row mb-3">
                                        <label for="country" class="form-label req mb-1 col-md-3">Country</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control w-50" id="country" name="country" value="{{ $data['country'] ?? '' }}">
                                        </div>
                                    </div>
                                @endif --}}

                                @if(!empty($data['country']))
                                    <div class="row mb-3">
                                        <label for="country" class="form-label req mb-1 col-md-3">Country</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control w-50" id="country" name="country" value="{{ $data['country'] }}">
                                        </div>
                                    </div>
                                @endif


                                {{-- @if($data['province'] != '')
                                    <div onclick="showHelpEntry('province')" class="flex mb-2">
                                        <div class="{{ isvalid('udf', 'province', 'cellLeftEditTable') }}">
                                            {{ __('Province / State:') }}
                                        </div>
                                        <div class="cellRightEditTable">
                                            {{ $data['province'] }}
                                        </div>
                                    </div>
                                @endif --}}

                                @if(!empty($data['province']))
                                    <div class="row mb-3">
                                        <label for="province" class="form-label mb-1 col-md-3">Province</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control w-50" id="province" name="province" value="{{ $data['province'] }}">
                                        </div>
                                    </div>
                                @endif


                                @if(!empty($data['district']))
                                    <div onclick="showHelpEntry('district')" class="flex mb-2">
                                        <div class="{{ isvalid('udf', 'district', 'cellLeftEditTable') }}">
                                            {{ __('District / County:') }}
                                        </div>
                                        <div class="cellRightEditTable">
                                            @if($data['district_id'] === 'ALL' && !empty($data['default_user_value5']))
                                                {{ $data['default_user_value5'] }}
                                            @else
                                                {{ $data['district'] }}
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($company_deduction_id != '')
                                    @include('company.EditCompanyDeductionUserValues', ['page_type' => 'mass_user'])
                                @else
                                    @include('company.EditCompanyDeductionUserValues', ['page_type' => 'user'])
                                @endif

                            @endif

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" id="id" name="id" value="{{ $data['id'] ?? '' }}">
                            <input type="hidden" name="user_id" value="{{ $data['user_id'] ?? '' }}">
                            <input type="hidden" name="company_deduction_id" value="{{ $data['company_deduction_id'] ?? '' }}">
                            <input type="hidden" id="calculation_id" value="{{ $data['calculation_id'] ?? '' }}">
                            <input type="hidden" id="combined_calculation_id" value="{{ $data['combined_calculation_id'] ?? '' }}">
                            <input type="hidden" id="country_id" value="{{ $data['country_id'] ?? '' }}">
                            <input type="hidden" id="province_id" value="{{ $data['province_id'] ?? '' }}">

                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>

<script>

    function showWeeklyTime(objectID)
    {
        if (document.getElementById) {
            let typeId = document.getElementById('type_id').value;
            let weeklyTime = document.getElementById(objectID);

            if (weeklyTime.style.display === 'none') {
                if (typeId != 10) {
                    weeklyTime.className = '';
                    weeklyTime.style.display = '';
                }
            } else {
                if (typeId == 10) {
                    weeklyTime.style.display = 'none';
                }
            }
        }
    }

    function getHourlyRateAqua() {
        const typeId = document.getElementById('type_id').value;
        if (typeId != 10) {
            const wage = document.getElementById('wage_val').value;
            const time = document.getElementById('weekly_time_val').value;
            const userId = document.getElementById('user_id_val').value;

            // Replace with AJAX call or server interaction
            const hourlyRate = (parseFloat(wage) / parseFloat(time || 1)).toFixed(2);
            document.getElementById('hourly_rate').value = hourlyRate;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        showWeeklyTime('weekly_time');
    });

</script>
</x-app-layout>
