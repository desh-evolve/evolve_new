<x-app-layout :title="'Recurring Holidays'">
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
        <h4 class="mb-sm-0">{{ __('Recurring Holidays') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-50"> <!-- Adjust width as needed -->
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Recurring Holidays {{ isset($data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="/recurring_holidays" class="btn btn-primary">Recurring Holidays List <i class="ri-arrow-right-line"></i></a>
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

                <form method="POST" action="{{ isset($data['id']) ? route('recurring_holidays.save', $data['id']) : route('recurring_holidays.save') }}">
                    @csrf

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="data[name]" value="{{ $data['name'] ?? '' }}" placeholder="Enter Holiday Name">
                    </div>

                    <div class="form-group">
                        <label for="special_day">Special Day</label>
                        <select id="special_day" name="data[special_day_id]" class="form-select" onChange="changeSpecialDay();">
                            @foreach ($data['special_day_options'] as $value => $label)
                                <option value="{{ $value }}" {{ isset($data['special_day_id']) && $data['special_day_id'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="type">
                        <div class="form-group">
                            <label for="type_id">Type</label>
                            <select id="type_id" name="data[type_id]" class="form-select" onChange="changeType();">
                                @foreach ($data['type_options'] as $value => $label)
                                    <option value="{{ $value }}" {{ isset($data['type_id']) && $data['type_id'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="dynamic-20" style="display:none">
                        <div class="form-group">
                            <label for="week_interval">Week Interval</label>
                            <select id="week_interval" name="data[week_interval]" class="form-select">
                                @foreach ($data['week_interval_options'] as $value => $label)
                                    <option value="{{ $value }}" {{ isset($data['week_interval']) && $data['week_interval'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="day_of_week_20">Day of the Week</label>
                            <select id="day_of_week_20" name="data[day_of_week_20]" class="form-select">
                                @foreach ($data['day_of_week_options'] as $value => $label)
                                    <option value="{{ $value }}" {{ isset($data['day_of_week']) && $data['day_of_week'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="dynamic-30" style="display:none">
                        <div class="form-group">
                            <label for="day_of_week_30">Day of the Week</label>
                            <select id="day_of_week_30" name="data[day_of_week_30]" class="form-select">
                                @foreach ($data['day_of_week_options'] as $value => $label)
                                    <option value="{{ $value }}" {{ isset($data['day_of_week']) && $data['day_of_week'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="pivot_day_direction">Pivot Day Direction</label>
                            <select id="pivot_day_direction" name="data[pivot_day_direction_id]" class="form-select">
                                @foreach ($data['pivot_day_direction_options'] as $value => $label)
                                    <option value="{{ $value }}" {{ isset($data['pivot_day_direction_id']) && $data['pivot_day_direction_id'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="static" style="display:none">
                        <div class="form-group">
                            <label for="day_of_month">Day of the Month</label>
                            <select id="day_of_month" name="data[day_of_month]" class="form-select">
                                @foreach ($data['day_of_month_options'] as $value => $label)
                                    <option value="{{ $value }}" {{ isset($data['day_of_month']) && $data['day_of_month'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="month" style="display:none">
                        <div class="form-group">
                            <label for="month_of_year">Month</label>
                            <select id="month_of_year" name="data[month]" class="form-select">
                                @foreach ($data['month_of_year_options'] as $value => $label)
                                    <option value="{{ $value }}" {{ isset($data['month']) && $data['month'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="always_week_day">Always On Week Day</label>
                        <select id="always_week_day" name="data[always_week_day_id]" class="form-select">
                            @foreach ($data['always_week_day_options'] as $value => $label)
                                <option value="{{ $value }}" {{ isset($data['always_week_day_id']) && $data['always_week_day_id'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group text-center">
                        <input type="hidden" name="data[id]" value="{{ $data['id'] ?? '' }}">
                        <button type="submit" class="btn btn-primary" name="action:submit">Submit</button>
                    </div>
                </form>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end center-container -->

    <script>
        function changeType() {
            const typeId = document.getElementById('type_id').value;
            
            // Hide all sections first
            document.getElementById('static').style.display = 'none';
            document.getElementById('dynamic-20').style.display = 'none';
            document.getElementById('dynamic-30').style.display = 'none';
            document.getElementById('month').style.display = 'none';

            if (typeId == '10') {
                // Show Static
                document.getElementById('static').style.display = 'block';
            } else if (typeId == '20') {
                // Show Dynamic 20
                document.getElementById('dynamic-20').style.display = 'block';
            } else if (typeId == '30') {
                // Show Dynamic 30 and Static and Month
                document.getElementById('dynamic-30').style.display = 'block';
                document.getElementById('static').style.display = 'block';
                document.getElementById('month').style.display = 'block';
            }
        }

        function changeSpecialDay() {
            const specialDay = document.getElementById('special_day').value;
            
            if (specialDay != '0') {
                // Hide all sections when special day is selected
                document.getElementById('type').style.display = 'none';
                document.getElementById('static').style.display = 'none';
                document.getElementById('dynamic-20').style.display = 'none';
                document.getElementById('dynamic-30').style.display = 'none';
                document.getElementById('month').style.display = 'none';
            } else {
                // Show type selector and relevant sections
                document.getElementById('type').style.display = 'block';
                changeType(); // This will show the appropriate sections based on type
            }
        }

        // Initialize the form on page load
        document.addEventListener('DOMContentLoaded', function() {
            changeSpecialDay();
        });
    </script>
</x-app-layout>