<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Job History List') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card"> <!-- Adjust width as needed -->
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{ isset($job_history_data['id']) ? 'Edit' : 'Add' }} {{ $title }} </h4>
                    <a href="/user/jobhistory" class="btn btn-primary">Employee Jobhistory <i class="ri-arrow-right-line"></i></a>
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

                    <form method="POST" action="{{ isset($job_history_data['id']) ? route('user.jobhistory.save', $job_history_data['id']) : route('user.jobhistory.save') }}">
                        @csrf

                        <div class="px-4 py-2">

                            <div class="row mb-3">
                                <label class="form-label req mb-1 col-md-3">Employee</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" value="{{ $user_data->getFullName() ?? '' }}" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="default_branch_id" class="form-label req mb-1 col-md-3">Default Branch</label>
                                <div class="col-md-9">
                                    <select name="default_branch_id" class="form-select w-50" id="default_branch_id">
                                        @foreach ($job_history_data['branch_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($job_history_data['default_branch_id']) && $job_history_data['default_branch_id'] == $value ? 'selected' : '' }}>
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
                                        @foreach ($job_history_data['department_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($job_history_data['default_department_id']) && $job_history_data['default_department_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="title_id" class="form-label req mb-1 col-md-3">Employee Title</label>
                                <div class="col-md-9">
                                    <select name="title_id" class="form-select w-50" id="title_id">
                                        @foreach ($job_history_data['title_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($job_history_data['title_id']) && $job_history_data['title_id'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="first_worked_date" class="form-label req mb-1 col-md-3">First Day Worked</label>
                                <div class="col-md-9 d-flex align-items-center gap-2">
                                    <input type="date" class="form-control w-50" id="first_worked_date" name="first_worked_date"
                                        value="{{ getdate_helper('date', $job_history_data['first_worked_date'] ?? '' )}}"
                                    >

                                    @if(count($pay_period_boundary_date_options) > 0)
                                        &nbsp;&nbsp;{{ __('or') }}&nbsp;&nbsp;
                                        <select name="effective_date2" class="form-select w-50"
                                            onchange="if (this.value != '-1') { document.getElementById('first_worked_date').value = this.value }"
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
                                <label for="last_worked_date" class="form-label req mb-1 col-md-3">Last Day Worked</label>
                                <div class="col-md-9">
                                    <input type="date" class="form-control w-50" id="last_worked_date" name="last_worked_date"
                                        value="{{ getdate_helper('date', $job_history_data['last_worked_date'] ?? '' )}}"
                                    >
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="note" class="form-label req mb-1 col-md-3">Note</label>
                                <div class="col-md-9">
                                    <textarea rows="5" class="form-control w-50" name="note" placeholder="Enter Note">{{ $job_history_data['note'] ?? '' }}</textarea>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="jobhistory_id" value="{{ $job_history_data['id'] ?? '' }}">
                            <input type="hidden" name="user_id" value="{{ $user_data->getId() }}">
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
