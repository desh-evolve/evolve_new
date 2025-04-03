<x-app-layout :title="'Edit Station'">
    <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .tblHeader {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .checkbox {
            margin-left: 0;
        }
    </style>

    <script>
        function showType() {
            const type_id = document.getElementById('type_id').value;

            document.getElementById('timeclock').style.display = 'none';
            document.getElementById('type_id-100').style.display = 'none';
            document.getElementById('type_id-150').style.display = 'none';

            if (type_id == 100 || type_id == 120 || type_id == 200) {
                document.getElementById('timeclock').style.display = '';
                document.getElementById('type_id-100').style.display = '';
            } else if (type_id == 150) {
                document.getElementById('timeclock').style.display = '';
                document.getElementById('type_id-150').style.display = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            showType(); // Initialize on page load
        });
    </script>

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Stations') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-75"> <!-- Wider card to accommodate more fields -->
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Station {{ isset($data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="/stations" class="btn btn-primary">Station List <i class="ri-arrow-right-line"></i></a>
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

                @if (session('time_clock_command_result'))
                    <div class="alert alert-info">
                        {{ session('time_clock_command_result') }}
                    </div>
                @endif

                <form method="POST"
                    action="{{ isset($data['id']) ? route('station.save', $data['id']) : route('station.save') }}">
                    @csrf
                 

                    {{-- <div class="form-group">
                        <label for="status">Status</label>
                        <select name="data[status]" id="status" class="form-select">
                            @foreach ($data['status_options'] as $value => $label)
                                <option value="{{ $value }}" {{ $data['status'] == $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div> --}}

                    <div class="form-group">
                        <label for="status_id">Status</label>
                        <select name="data[status]" id="status_id" class="form-select">
                            @foreach ($data['status_options'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ isset($data['status']) && $data['status'] == $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="type_id">Type</label>
                        <select name="data[type]" id="type_id" class="form-select" onChange="showType()">
                            @foreach ($data['type_options'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ (!isset($data['type']) && $loop->first) || (isset($data['type']) && $data['type'] == $value) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="station">Station ID</label>
                        <input type="text" name="data[station]" id="station" class="form-control"
                            value="{{ $data['station'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="source">Source</label>
                        <input type="text" name="data[source]" id="source" class="form-control"
                            value="{{ $data['source'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" name="data[description]" id="description" class="form-control"
                            value="{{ $data['description'] ?? '' }}">
                    </div>

                    <!-- Default Punch Settings Section -->
                    <div class="tblHeader mt-4 mb-2 p-2">
                        <h5 class="mb-0">Default Punch Settings</h5>
                    </div>

                    <div class="form-group">
                        <label for="branch_id">Branch</label>
                        <select name="data[branch_id]" id="branch_id" class="form-select">
                            @foreach ($data['branch_options'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ (!isset($data['branch_id']) && $loop->first) || (isset($data['branch_id']) && $data['branch_id'] == $value) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select name="data[department_id]" id="department_id" class="form-select">
                            @foreach ($data['department_options'] as $value => $label)
                                {{-- <option value="{{ $value }}"
                                    {{ $data['department_id'] == $value ? 'selected' : '' }}>{{ $label }}
                                </option> --}}
                                <option value="{{ $value }}"
                                    {{ (!isset($data['department_id']) && $loop->first) || (isset($data['department_id']) && $data['department_id'] == $value) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($current_company->getProductEdition() == 20)
                        @if (count($data['job_options']) > 1)
                            <div class="form-group">
                                <label for="job_id">Job</label>
                                <select name="data[job_id]" id="job_id" class="form-select"
                                    onChange="TIMETREX.punch.showJobItem(false)">
                                    @foreach ($data['job_options'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ $data['job_id'] == $value ? 'selected' : '' }}>{{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if (count($data['job_item_options']) > 1)
                            <div class="form-group">
                                <label for="job_item_id">Task</label>
                                <select name="data[job_item_id]" id="job_item_id" class="form-select"></select>
                                <input type="hidden" id="selected_job_item" value="{{ $data['job_item_id'] ?? '' }}">
                            </div>
                        @endif
                    @endif

                    <!-- Time Clock Configuration (shown conditionally) -->
                    <div id="timeclock" style="display:none">
                        <div class="tblHeader mt-4 mb-2 p-2">
                            <h5 class="mb-0">Time Clock Configuration</h5>
                        </div>

                        <div class="form-group">
                            <label for="password">Password/COMM Key</label>
                            <input type="text" name="data[password]" id="password" class="form-control"
                                value="{{ $data['password'] ?? '' }}">
                        </div>

                        <div class="form-group">
                            <label for="port">Port</label>
                            <input type="text" name="data[port]" id="port" class="form-control"
                                value="{{ $data['port'] ?? '' }}">
                        </div>

                        <div class="form-group">
                            <label for="time_zone_id">Force Time Zone</label>
                            <select name="data[time_zone_id]" id="time_zone_id" class="form-select">
                                @foreach ($data['time_zone_options'] as $value => $label)
                                    {{-- <option value="{{ $value }}"
                                    
                                        {{ $data['time_zone_id'] == $value ? 'selected' : '' }}>{{ $label }}
                                    </option> --}}
                                    <option value="{{ $value }}"
                                        {{ (!isset($data['time_zone_id']) && $loop->first) || (isset($data['time_zone_id']) && $data['time_zone_id'] == $value) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" name="data[enable_auto_punch_status]"
                                id="enable_auto_punch_status" class="form-check-input" value="1"
                                {{ $data['enable_auto_punch_status'] ?? false ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_auto_punch_status">Enable Automatic Punch
                                Status</label>
                        </div>

                        <div class="form-group">
                            <label for="mode_flag">Configuration Modes</label>
                            <select name="data[mode_flag][]" id="mode_flag" class="form-select" multiple>
                                @foreach ($data['mode_flag_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array($value, $data['mode_flag'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- @if ($data['id'] != '' && in_array($data['type'], [100, 120, 200])) --}}
                        @if (isset($data['id']) && $data['id'] != '' && isset($data['type']) && in_array($data['type'], [100, 120, 200]))
                            <div class="form-group">
                                <label for="time_clock_command">Manual Command</label>
                                <div class="input-group">
                                    <select name="data[time_clock_command]" id="time_clock_command"
                                        class="form-select">
                                        @foreach ($data['time_clock_command_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $data['time_clock_command'] == $value ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" name="action" value="time_clock_command"
                                        class="btn btn-secondary">Run Now</button>
                                </div>
                            </div>
                        @endif

                        <!-- Time Clock Synchronization -->
                        <div class="tblHeader mt-4 mb-2 p-2">
                            <h5 class="mb-0">Time Clock Synchronization</h5>
                        </div>

                        <div id="type_id-100" style="display:none">


                            <div class="form-group">
                                <label for="100_poll_frequency">Download Frequency</label>
                                <select name="data[poll_frequency]" id="100_poll_frequency" class="form-select"
                                    onChange="document.getElementById('150_poll_frequency').value = this.value">
                                    @foreach ($data['poll_frequency_options'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ isset($data['poll_frequency']) && $data['poll_frequency'] == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Last Download:
                                    {{ isset($data['last_poll_date']) && $data['last_poll_date'] ? date('Y-m-d H:i', $data['last_poll_date']) : 'Never' }}
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="push_frequency">Full Upload Frequency</label>
                                <select name="data[push_frequency]" id="push_frequency" class="form-select">
                                    @foreach ($data['push_frequency_options'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ $data['push_frequency'] == $value ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Last Upload:
                                    {{ isset($data['last_push_date']) ? date('Y-m-d H:i', $data['last_push_date']) : 'Never' }}</small>
                            </div>

                            <div class="form-group">
                                <label for="partial_push_frequency">Partial Upload Frequency</label>
                                <select name="data[partial_push_frequency]" id="partial_push_frequency"
                                    class="form-select">
                                    @foreach ($data['push_frequency_options'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ $data['partial_push_frequency'] == $value ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Last Upload:
                                    {{ isset($data['last_partial_push_date']) ? date('Y-m-d H:i', $data['last_partial_push_date']) : 'Never' }}</small>
                            </div>
                        </div>

                        <div id="type_id-150" style="display:none">
                            <div class="form-group">
                                <label for="150_poll_frequency">Synchronize Frequency</label>
                                <select name="data[poll_frequency]" id="150_poll_frequency" class="form-select"
                                    onChange="document.getElementById('100_poll_frequency').value = this.value">
                                    @foreach ($data['poll_frequency_options'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ $data['poll_frequency'] == $value ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Last Downloaded Punch</label>
                            <div class="form-control-plaintext">
                                {{ isset($data['last_punch_time_stamp']) ? date('Y-m-d H:i', $data['last_punch_time_stamp']) : 'Never' }}
                            </div>
                        </div>
                    </div>
                    <div class="tblHeader mt-4 mb-2 p-2">
                        <h5 class="mb-0">Employee Criteria</h5>
                    </div>
                    <div class="mt-3 mb-3">
                        <label for="user_ids">Employee Groups</label>
                        <div class="col-md-12">
                            <x-general.multiselect-php title="Employee Groups" :data="$data['src_group_options']" :selected="!empty($data['group_ids']) ? array_values($data['group_ids']) : []"
                                :name="'data[group_ids][]'" id="policySelector" />
                        </div>
                    </div>

                    <div class="mt-3 mb-3">
                        <label for="user_ids">Branches</label>
                        <div class="col-md-12">
                            <x-general.multiselect-php title="Branches" :data="$data['branch_options']" :selected="!empty($data['branch_ids']) ? array_values($data['branch_ids']) : []"
                                :name="'data[branch_ids][]'" id="policySelector" />
                        </div>
                    </div>
                    <div class="mt-3 mb-3">
                        <label for="user_ids">Departments</label>
                        <div class="col-md-12">
                            <x-general.multiselect-php title="Departments" :data="$data['department_options']" :selected="!empty($data['department_ids']) ? array_values($data['department_ids']) : []"
                                :name="'data[department_ids][]'" id="policySelector" />
                        </div>
                    </div>
                    <div class="mt-3 mb-3">
                        <label for="user_ids">Include Employees</label>
                        <div class="col-md-12">
                            <x-general.multiselect-php title="Include Employees" :data="$data['src_include_user_options']" :selected="!empty($data['include_user_ids']) ? array_values($data['include_user_ids']) : []"
                                :name="'data[include_user_ids][]'" id="policySelector" />
                        </div>
                    </div>
                    <div class="mt-3 mb-3">
                        <label for="user_ids">Exclude Employees</label>
                        <div class="col-md-12">
                            <x-general.multiselect-php title="Exclude Employees" :data="$data['src_exclude_user_options']" :selected="!empty($data['exclude_user_ids']) ? array_values($data['exclude_user_ids']) : []"
                                :name="'data[exclude_user_ids][]'" id="policySelector" />
                        </div>
                    </div>
                    {{-- @php
                        $data['group_selection_type_id'] = $data['group_selection_type_id'] ?? '20';
                        $data['branch_selection_type_id'] = $data['branch_selection_type_id'] ?? '20';
                        $data['department_selection_type_id'] = $data['department_selection_type_id'] ?? '20';
                    @endphp --}}
                    <div class="form-group text-center mt-4">
                        <input type="hidden" name="data[group_selection_type_id]" value="{{ $data['group_selection_type_id'] ?? '20' }}">
                        <input type="hidden" name="data[branch_selection_type_id]" value="{{ $data['branch_selection_type_id'] ?? '20' }}">
                        <input type="hidden" name="data[department_selection_type_id]" value="{{ $data['department_selection_type_id'] ?? '20' }}">
                        <input type="hidden" name="data[id]" value="{{ $data['id'] ?? '' }}">
                        <button type="submit" name="action" value="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end center-container -->
</x-app-layout>
