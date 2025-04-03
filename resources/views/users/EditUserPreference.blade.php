<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Preferences') }}</h4>
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


                    <form method="POST" action="{{ isset($pref_data['id']) ? route('user_preference.save', $pref_data['id']) : route('user_preference.save') }}">
                        @csrf


                        <div class="px-4 py-2">

                            {{-- @if ($incomplete == 1)
                                <div class="alert alert-warning">
                                    {{ __('Please define your personal preferences for a better :app experience.', ['app' => $APPLICATION_NAME]) }}
                                </div>
                            @endif --}}

                            <div class="row mb-3">
                                <label for="user_full_name" class="form-label req mb-1 col-md-3">Employee Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="user_full_name"
                                            name="user_full_name" placeholder="Enter Employee Name"
                                            value="{{ $pref_data['user_full_name'] ?? '' }}">
                                </div>
                            </div>

                            {{-- <div class="row mb-3">
                                    <label for="language" class="form-label req mb-1 col-md-3">Language</label>
                                    <div class="col-md-9">
                                        <select name="language" class="form-select w-50" id="language" onchange = "showDateFormat()">
                                            @foreach ($pref_data['language_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['language']) && $pref_data['language'] == $value ? 'selected' : '' }}>
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
                                            @foreach ($pref_data['date_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['date_format']) && $pref_data['date_format'] == $value ? 'selected' : '' }}>
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
                                            @foreach ($pref_data['language_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['language']) && $pref_data['language'] == $value ? 'selected' : '' }}>
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
                                            style="{{ isset($pref_data['language']) && $pref_data['language'] == 'en' ? '' : 'display:none;' }}">
                                            @foreach ($pref_data['date_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['date_format']) && $pref_data['date_format'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="other_date_format" class="form-select w-50" id="other_date_format"
                                            style="{{ isset($pref_data['language']) && $pref_data['language'] == 'en' ? 'display:none;' : '' }}">
                                            @foreach ($pref_data['other_date_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['other_date_format']) && $pref_data['other_date_format'] == $value ? 'selected' : '' }}>
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
                                            @foreach ($pref_data['time_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['time_format']) && $pref_data['time_format'] == $value ? 'selected' : '' }}>
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
                                            @foreach ($pref_data['time_unit_format_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['time_unit_format']) && $pref_data['time_unit_format'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="time_zone" class="form-label req mb-1 col-md-3">Time Zone</label>
                                    <div class="col-md-9 d-flex align-items-center gap-2">
                                        <select name="time_zone" class="form-select w-50" id="time_zone" onchange="getTimeZoneOffset()">
                                            @foreach ($pref_data['time_zone_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['time_zone']) && $pref_data['time_zone'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        (GMT <span id="time_zone_offset"></span>)
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="start_week_day" class="form-label req mb-1 col-md-3">Start Weeks on</label>
                                    <div class="col-md-9">
                                        <select name="start_week_day" class="form-select w-50" id="start_week_day">
                                            @foreach ($pref_data['start_week_day_options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($pref_data['start_week_day']) && $pref_data['start_week_day'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="items_per_page" class="form-label req mb-1 col-md-3">Rows per Page</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" id="items_per_page" name="items_per_page" placeholder="Enter Rows per Page" value="{{ $pref_data['items_per_page'] ?? '' }}">
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
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_exception" name="enable_email_notification_exception" value="1" {{ isset($pref_data['enable_email_notification_exception']) && $pref_data['enable_email_notification_exception'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="enable_email_notification_message" class="form-label req mb-1 col-md-3">Messages</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_message" name="enable_email_notification_message" value="1" {{ isset($pref_data['enable_email_notification_message']) && $pref_data['enable_email_notification_message'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="enable_email_notification_home" class="form-label req mb-1 col-md-3">Send Notifications to Home Email</label>
                                    <div class="col-md-9">
                                        <input type="checkbox" class="form-check-input" id="enable_email_notification_home" name="enable_email_notification_home" value="1" {{ isset($pref_data['enable_email_notification_home']) && $pref_data['enable_email_notification_home'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                            </div>

                        </div>


                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="user_preference_id" value="{{ $pref_data['id'] ?? '' }}">
                            <input type="hidden" name="user_id" value="{{ $pref_data['user_id'] ?? '' }}">
                            <input type="hidden" name="user_full_name" value="{{ $pref_data['user_full_name'] ?? '' }}">
                            {{-- <input type="hidden" name="incomplete" value="1"> --}}
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Pass time zone options from PHP to JavaScript
        var timeZoneOffsets = @json($pref_data['time_zone_options']);
        console.log('timezone', timeZoneOffsets);

        document.addEventListener("DOMContentLoaded", function () {
            getTimeZoneOffset(); // Fetch GMT offset on page load
        });

        // Function to get the offset based on the timezone selected
        function getTimeZoneOffset() {
            let timeZoneSelect = document.getElementById('time_zone');
            let selectedTimeZone = timeZoneSelect.value;

            if (timeZoneOffsets.hasOwnProperty(selectedTimeZone) && selectedTimeZone !== "-1") {
                // Use the Intl.DateTimeFormat API to get the offset dynamically based on the time zone
                let date = new Date();
                let options = { timeZone: selectedTimeZone, hour: '2-digit', minute: '2-digit' };
                let formatter = new Intl.DateTimeFormat([], options);

                // Get the offset in minutes and calculate hours and minutes
                let parts = formatter.formatToParts(date);
                let timeZoneOffset = parts.find(part => part.type === "timeZoneName").value;

                // Adjust the format to display GMT and the correct offset
                let offsetInSeconds = new Date().getTimezoneOffset() * -60; // This gives the offset in seconds
                let hours = Math.floor(Math.abs(offsetInSeconds) / 3600);
                let minutes = Math.abs((offsetInSeconds % 3600) / 60);
                let sign = offsetInSeconds >= 0 ? '+' : '-';

                // Format minutes to always be 2 digits
                let formattedOffset = `GMT ${sign}${hours}:${minutes.toString().padStart(2, '0')}`;
                document.getElementById('time_zone_offset').textContent = formattedOffset;
            } else {
                document.getElementById('time_zone_offset').textContent = "GMT Unknown";
            }
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

        // Function to populate the showDateFormat
        window.onload = function() {
            showDateFormat();
        };

     </script>

</x-app-layout>
