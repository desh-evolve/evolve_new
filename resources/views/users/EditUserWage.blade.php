<x-app-layout :title="'Input Example'">
    {{-- <style>
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
    </style> --}}

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Wage List') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card"> <!-- Adjust width as needed -->
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{ isset($wage_data['id']) ? 'Edit' : 'Add' }} {{ $title }} </h4>
                    <a href="/user/wage" class="btn btn-primary">Employee Wage <i class="ri-arrow-right-line"></i></a>
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

                    <form method="POST" action="{{ isset($wage_data['id']) ? route('user.wage.save', $wage_data['id']) : route('user.wage.save') }}">
                        @csrf

                        <div class="px-4 py-2">

                            <div class="row mb-3">
                                <label class="form-label req mb-1 col-md-3">Employee</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" value="{{ $user_data->getFullName() ?? '' }}" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="wage_group_id" class="form-label req mb-1 col-md-3">Group</label>
                                <div class="col-md-9">
                                    <select name="wage_group_id" class="form-select w-50" id="wage_group_id">
                                        @foreach ($wage_data['wage_group_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($wage_data['wage_group_id']) && $wage_data['wage_group_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="type" class="form-label req mb-1 col-md-3">Type</label>
                                <div class="col-md-9">
                                    <select name="type" class="form-select w-50" id="type_id" onChange="showWeeklyTime('weekly_time'); getHourlyRateAqua(); ">
                                        @foreach ($wage_data['type_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($wage_data['type']) && $wage_data['type'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="wage" class="form-label req mb-1 col-md-3">Wage</label>
                                <div class="col-md-9">
                                    <div class="d-flex align-items-center gap-2 w-50">
                                        <span class="fs-5">{{ $wage_data['currency_symbol'] ?? '' }}</span>
                                        <input id="wage_val" size="15" type="text" class="form-control" name="wage" value="{{ $wage_data['wage'] ?? 0 }}" onchange="getHourlyRateAqua()">
                                        <span class="fs-5">{{ $wage_data['iso_code'] ?? '' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="budgetary_allowance" class="form-label req mb-1 col-md-3">Budgetary Allowance</label>
                                <div class="col-md-9">
                                    <div class="d-flex align-items-center gap-2 w-50">
                                        <span class="fs-5">{{ $wage_data['currency_symbol'] ?? '' }}</span>
                                        <input id="wage_val" size="15" type="text" class="form-control" name="budgetary_allowance" value="{{ $wage_data['budgetary_allowance'] ?? '' }}" readonly>
                                        <span class="fs-5">{{ $wage_data['iso_code'] ?? '' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div id="weekly_time" style="display:none">

                                <div class="row mb-3">
                                    <label for="weekly_time" class="form-label req mb-1 col-md-3">Average Time / Month</label>
                                    <div class="col-md-9">
                                        <div class="d-flex align-items-center gap-2">
                                            <input id="weekly_time_val" size="15" type="text" class="form-control w-50" name="weekly_time" value="{{ old('weekly_time', $wage_data['weekly_time'] ?? 0) }}" onchange="getHourlyRateAqua()">
                                            <span class="fs-6 text-muted">(ie: 240 hours / month)</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="hourly_rate" class="form-label req mb-1 col-md-3">Hourly Rate</label>
                                    <div class="col-md-9">
                                        <input id="hourly_rate" size="15" type="text" class="form-control w-50" name="hourly_rate" value="{{ $wage_data['hourly_rate'] ?? 0.00 }}">
                                    </div>
                                </div>

                            </div>


                            <div class="row mb-3">
                                <label for="effective_date" class="form-label req mb-1 col-md-3">Effective Date</label>
                                <div class="col-md-9 d-flex align-items-center gap-2">
                                    <input type="date" class="form-control w-50" id="effective_date" name="effective_date"
                                        value="{{ getdate_helper('date', $wage_data['effective_date'] ?? '' )}}"
                                    >
                                    @if(count($pay_period_boundary_date_options) > 0)
                                        &nbsp;&nbsp;{{ __('or') }}&nbsp;&nbsp;
                                        <select name="effective_date2" class="form-select w-50"
                                            onchange="if (this.value != '-1') { document.getElementById('effective_date').value = this.value }"
                                        >
                                            @foreach($pay_period_boundary_date_options as $value => $option)
                                                <option value="{{ $value }}" {{ $tmp_effective_date == $value ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="note" class="form-label req mb-1 col-md-3">Note</label>
                                <div class="col-md-9">
                                    <textarea rows="5" class="form-control w-50" name="note" placeholder="Enter Note">{{ $wage_data['note'] ?? '' }}</textarea>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="wage_id" value="{{ $wage_data['id'] ?? '' }}">
                            <input type="hidden" id="user_id_val" name="user_id" value="{{ $user_data->getId() }}">
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

    // function getHourlyRateAqua() {
    //     const typeId = document.getElementById('type_id').value;
    //     if (typeId != 10) {
    //         const wage = document.getElementById('wage_val').value;
    //         const time = document.getElementById('weekly_time_val').value;
    //         const userId = document.getElementById('user_id_val').value;

    //         // Replace with AJAX call or server interaction
    //         const hourlyRate = (parseFloat(wage) / parseFloat(time || 1)).toFixed(2);
    //         document.getElementById('hourly_rate').value = hourlyRate;
    //     }
    // }


    function getHourlyRateAqua() {
        const typeId = document.getElementById('type_id').value;
        if (typeId != 10) {
            let wage = document.getElementById('wage_val').value.trim();
            let time = document.getElementById('weekly_time_val').value.trim();

            // Convert to float or default to 0
            wage = parseFloat(wage);
            time = parseFloat(time);

            if (!isNaN(wage) && !isNaN(time) && time > 0) {
                const hourlyRate = (wage / time).toFixed(2);
                document.getElementById('hourly_rate').value = hourlyRate;
            } else {
                document.getElementById('hourly_rate').value = '0.00'; // Fallback value
            }
        }
    }


    document.addEventListener('DOMContentLoaded', function () {
        showWeeklyTime('weekly_time');
    });

</script>
</x-app-layout>

