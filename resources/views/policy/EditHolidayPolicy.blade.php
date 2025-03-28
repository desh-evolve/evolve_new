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
                        action="{{ isset($data['id']) ? route('policy.holiday_policies.submit', $data['id']) : route('policy.holiday_policies.submit') }}">
                        @csrf

                        @if (!$hpf->Validator->isValid())
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
                                placeholder="Enter Holiday Policy Name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="type_id">Type</label>
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
                            <label for="default_schedule_status_id">Default Schedule Status</label>
                            <select 
                                id="default_schedule_status_id" 
                                class="form-select" 
                                name="data[default_schedule_status_id]" 
                            >
                                @foreach ($data['schedule_status_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['default_schedule_status_id']) && $id == $data['default_schedule_status_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

				        <div class="bg-primary text-white mt-4">Holiday Eligibility </div>

                        <div class="form-group">
                            <label for="minimum_employed_days">Minimum Employed Days</label>
                            <input 
                                type="text" 
                                size="6"
                                class="form-control" 
                                name="data[minimum_employed_days]" 
                                value="{{ $data['minimum_employed_days'] ?? '0' }}"
                            >
                        </div>
                        
                        <div id="type_id-20" style="{{ $data['type_id'] != 20 ? 'display:none' : '' }}">

                            <div class="form-group">
                                <label for="minimum_worked_days">Employee Must Work at Least</label>
                                <input class="form-control" size="3" type="text" name="data[minimum_worked_days]" value="{{$data['minimum_worked_days'] ?? '0'}}">
                                of the
                                <input class="form-control" size="3" type="text" name="data[minimum_worked_period_days]" value="{{$data['minimum_worked_period_days'] ?? '0'}}">
                                <select 
                                    id="worked_scheduled_days" 
                                    class="form-select" 
                                    name="data[worked_scheduled_days]" 
                                >
                                    @foreach ($data['scheduled_day_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['worked_scheduled_days']) && $id == $data['worked_scheduled_days'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                                prior to the holiday
                            </div>

                            <div class="form-group">
                                <label for="minimum_worked_after_days">Employee Must Work at Least</label>
                                <input class="form-control" size="3" type="text" name="data[minimum_worked_after_days]" value="{{$data['minimum_worked_after_days'] ?? '0'}}">
                                of the
                                <input class="form-control" size="3" type="text" name="data[minimum_worked_after_period_days]" value="{{$data['minimum_worked_after_period_days'] ?? '0'}}">
                                <select 
                                    id="worked_after_scheduled_days" 
                                    class="form-select" 
                                    name="data[worked_after_scheduled_days]" 
                                >
                                    @foreach ($data['scheduled_day_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['worked_after_scheduled_days']) && $id == $data['worked_after_scheduled_days'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                                following the holiday
                            </div>

                        </div>

				        <div class="bg-primary text-white mt-4">Holiday Time Calculation</div>

                        <div id="type_id-30" style="{{ $data['type_id'] != 30 ? 'display:none' : '' }}">
                            <div class="form-group">
                                <label for="average_time_days">Total Time over</label>
                                <input 
                                    type="text" 
                                    size="3"
                                    class="form-control" 
                                    name="data[average_time_days]" 
                                    value="{{ $data['average_time_days'] ?? '0' }}"
                                >days
                            </div>

                            <div class="form-group">
                                <label for="average_time_days">Average Time over</label>
                                Worked Days Only
                                <input 
                                    type="checkbox" 
                                    class="checkbox" 
                                    id="average_time_worked_days" 
                                    name="data[average_time_worked_days]" 
                                    value="1" onclick="showAverageDays()" 
                                    {{ (!empty( $data['average_time_worked_days']) && $data['average_time_worked_days'] == TRUE) ? 'checked' : '' }} 
                                >
                                or
                                <input class="form-control" size="3" type="text" id="average_days" name="data[average_days]" value="{{$data['average_days']}}"> 
                                days
                            </div>

                            <div class="form-group">
                                <label for="minimum_time">Minimum Time (hh:mm (2:15))</label>
                                <input 
                                    type="text" 
                                    size="8"
                                    class="form-control" 
                                    name="data[minimum_time]" 
                                    value="{{ $data['minimum_time'] ?? '00:00' }}"
                                >days
                            </div>

                            <div class="form-group">
                                <label for="maximum_time">Maximum Time (hh:mm (2:15))</label>
                                <input 
                                    type="text" 
                                    size="8"
                                    class="form-control" 
                                    name="data[maximum_time]" 
                                    value="{{ $data['maximum_time'] ?? '00:00' }}"
                                >days
                            </div>

                            <div class="form-group">
                                <label for="force_over_time_policy">Always Apply Over Time/Premium Policies</label>
                                <input 
                                    type="checkbox" 
                                    class="checkbox"
                                    name="data[force_over_time_policy]" 
                                    value="1"
                                    {{(!empty($data['force_over_time_policy']) && $data['force_over_time_policy'] == TRUE) ? 'checked' : '' }}
                                >Even if they are not eligible for holiday pay
                            </div>

                            <div class="form-group">
                                <label for="include_over_time">Include Over Time in Average</label>
                                <input 
                                    type="checkbox" 
                                    class="checkbox"
                                    name="data[include_over_time]" 
                                    value="1"
                                    {{( !empty($data['include_over_time']) && $data['include_over_time'] == TRUE) ? 'checked' : '' }}
                                >
                            </div>

                            <div class="form-group">
                                <label for="include_paid_absence_time">Include Paid Absence Time in Average</label>
                                <input 
                                    type="checkbox" 
                                    class="checkbox"
                                    name="data[include_paid_absence_time]" 
                                    value="1"
                                    {{ (!empty($data['include_paid_absence_time']) && $data['include_paid_absence_time'] == TRUE) ? 'checked' : '' }}
                                >
                            </div>

                            <div class="form-group">
                                <label for="round_interval_policy_id">Rounding Policy</label>
                                <select 
                                    id="round_interval_policy_id" 
                                    class="form-select" 
                                    name="data[round_interval_policy_id]" 
                                >
                                    @foreach ($data['round_interval_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['round_interval_policy_id']) && $id == $data['round_interval_policy_id'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div id="type_id-10_and_20" style="{{ $data['type_id'] == 30 ? 'display: none' : '' }}">
                            <div class="form-group">
                                <label for="minimum_time">Holiday Time (hh:mm (2:15))</label>
                                <input 
                                    id="type_id-10_and_20_minimum_time" 
                                    size="8"
                                    class="form-control"
                                    name="data[minimum_time]" 
                                    value="{{$data['minimum_time'] ?? ''}}"
                                    {{ $data['minimum_time'] == TRUE ? 'checked' : '' }}
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="absence_policy_id">Absence Policy</label>
                            <select 
                                id="absence_policy_id" 
                                class="form-select" 
                                name="data[absence_policy_id]" 
                            >
                                @foreach ($data['absence_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['absence_policy_id']) && $id == $data['absence_policy_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

				        <div class="bg-primary text-white mt-4">Recurring Holidays</div>

                        <div class="form-group">
                            <label for="recurring_holiday_id">Recurring Holidays</label>
                            <select 
                                id="recurring_holiday_id" 
                                class="form-select" 
                                name="data[recurring_holiday_ids][]" 
                                multiple
                            >
                                @foreach ($data['recurring_holiday_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['recurring_holiday_ids']) && in_array($id,$data['recurring_holiday_ids']))
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
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
            showAverageDays();
        })

        function showType() {
            document.getElementById('type_id-10_and_20').style.display = 'none';
            document.getElementById('type_id-10_and_20_minimum_time').disabled = true;
            document.getElementById('type_id-20').style.display = 'none';
            document.getElementById('type_id-30').style.display = 'none';
        
            if ( document.getElementById('type_id').value == 10 ) {
                document.getElementById('type_id-10_and_20_minimum_time').disabled = false;
                document.getElementById('type_id-10_and_20').className = '';
                document.getElementById('type_id-10_and_20').style.display = '';
            } else if (document.getElementById('type_id').value == 20) {
                document.getElementById('type_id-10_and_20_minimum_time').disabled = false;
                document.getElementById('type_id-10_and_20').className = '';
                document.getElementById('type_id-10_and_20').style.display = '';
        
                document.getElementById('type_id-20').className = '';
                document.getElementById('type_id-20').style.display = '';
            } else if (document.getElementById('type_id').value == 30) {
                document.getElementById('type_id-20').className = '';
                document.getElementById('type_id-20').style.display = '';
        
                document.getElementById('type_id-30').className = '';
                document.getElementById('type_id-30').style.display = '';
            }
        }
        
        function showAverageDays() {
            document.getElementById('average_days').disabled = false;
            if ( document.getElementById('average_time_worked_days').checked == true ) {
                document.getElementById('average_days').disabled = true;
                document.getElementById('average_days').value = 0;
            }
        }
    </script>
</x-app-layout>
