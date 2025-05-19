<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a
                                type="button"
                                href="/policy/policy_groups/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Policy Group <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">

                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="POST"
                        action="{{ isset($data['id']) ? route('policy.meal_policies.submit', $data['id']) : route('policy.meal_policies.submit') }}">
                        @csrf

                        @if (!$mpf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input
                                type="text"
                                class="form-control"
                                name="data[name]"
                                value="{{ $data['name'] ?? '' }}"
                                placeholder="Enter Meal Policy Name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <select
                                id="type_id"
                                class="form-select"
                                name="data[type_id]"
                                onChange="showType()"
                            >
                                @foreach ($data['type_options'] as $id => $name )
                                <option
                                    value="{{$id}}"
                                    @if(!empty($data['type_id']) && $id == $data['type_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="trigger_time">Active After (hh:mm (2:15))</label>
                            <input
                                type="text"
                                size="8"
                                class="form-control"
                                name="data[trigger_time]"
                                value="{{ $data['trigger_time'] ?? '0' }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="amount">
                                <span id="type_10_desc" class="{{ $data['type_id'] != 10 ? 'd-none' : '' }}">Deduction/Addition Time (hh:mm (2:15))</span>
                                <span id="type_20_desc" class="{{ $data['type_id'] != 20 ? 'd-none' : '' }}">Meal Time (hh:mm (2:15))</span>
                            </label>
                            <input
                                type="text"
                                size="8"
                                class="form-control"
                                name="data[amount]"
                                value="{{ $data['amount'] ?? '0' }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="auto_detect_type">Auto-Detect Meals By</label>
                            <select
                                id="auto_detect_type_id"
                                class="form-select"
                                name="data[auto_detect_type_id]"
                                onChange="showAutoDetectType()"
                            >
                                @foreach ($data['auto_detect_type_options'] as $id => $name )
                                <option
                                    value="{{$id}}"
                                    @if(!empty($data['auto_detect_type_id']) && $id == $data['auto_detect_type_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="auto_detect_type-10" style="display:none">
                            <div class="form-group">
                                <label for="start_window">Start Window (hh:mm (2:15))</label>
                                <input
                                    type="text"
                                    size="8"
                                    class="form-control"
                                    name="data[start_window]"
                                    value="{{ $data['start_window'] ?? '0' }}"
                                >
                                (After First Punch)
                            </div>

                            <div class="form-group">
                                <label for="window_length">Window Length (hh:mm (2:15))</label>
                                <input
                                    type="text"
                                    size="8"
                                    class="form-control"
                                    name="data[window_length]"
                                    value="{{ $data['window_length'] ?? '0' }}"
                                >
                            </div>

                        </div>

                        <div id="auto_detect_type-20" style="display:none">
                            <div class="form-group">
                                <label for="minimum_punch_time">Minimum Punch Time (hh:mm (2:15))</label>
                                <input
                                    type="text"
                                    size="8"
                                    class="form-control"
                                    name="data[minimum_punch_time]"
                                    value="{{ $data['minimum_punch_time'] ?? '0' }}"
                                >
                            </div>

                            <div class="form-group">
                                <label for="maximum_punch_time">Maximum Punch Time (hh:mm (2:15))</label>
                                <input
                                    type="text"
                                    size="8"
                                    class="form-control"
                                    name="data[maximum_punch_time]"
                                    value="{{ $data['maximum_punch_time'] ?? '0' }}"
                                >
                            </div>

                        </div>

                        <div id="include_lunch_punch_time" style="display:none">
                            <div class="form-group">
                                <label for="window_length">Include Any Punched Time for Lunch</label>
                                <input
                                    type="checkbox"
                                    class="checkbox"
                                    name="include_lunch_punch_time"
                                    value="1"
                                    {{ isset($data['include_lunch_punch_time']) && $data['include_lunch_punch_time'] == 1 ? 'checked' : '' }}
                                >
                            </div>

                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-primary btnSubmit" name="action:submit" value="Submit">
                        </div>

                        <input type="hidden" name="data[id]" value="{{!empty($data['id']) ? $data['id'] : ''}}">
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script	language=JavaScript>
        document.addEventListener("DOMContentLoaded", function () {
            showType();
            showAutoDetectType();
        })

        function showType() {
            type_id = document.getElementById('type_id').value;

            document.getElementById('type_10_desc').style.display = 'none';
            document.getElementById('type_20_desc').style.display = 'none';
            document.getElementById('include_lunch_punch_time').style.display = 'none';

            if ( type_id == 10 || type_id == 15 ) {
                document.getElementById('type_10_desc').className = '';
                document.getElementById('type_10_desc').style.display = '';

                document.getElementById('include_lunch_punch_time').className = '';
                document.getElementById('include_lunch_punch_time').style.display = '';
            } else {
                document.getElementById('type_20_desc').className = '';
                document.getElementById('type_20_desc').style.display = '';
            }
        }

        function showAutoDetectType() {
            auto_detect_type_id = document.getElementById('auto_detect_type_id').value;

            //document.getElementById('trigger_time').style.display = 'none';
            document.getElementById('auto_detect_type-10').style.display = 'none';
            document.getElementById('auto_detect_type-20').style.display = 'none';

            if ( auto_detect_type_id == 10 ) {
                document.getElementById('auto_detect_type-10').className = '';
                document.getElementById('auto_detect_type-10').style.display = '';

            } else {
                document.getElementById('auto_detect_type-20').className = '';
                document.getElementById('auto_detect_type-20').style.display = '';
            }
        }

    </script>
</x-app-layout>
