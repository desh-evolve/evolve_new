<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('payroll.pay_period_schedules.submit', $data['id']) : route('payroll.pay_period_schedules.submit') }}">
                        @csrf

                        @if (!$ppsf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <label>Name</label>
                            <input 
                                class="form-control"
                                type="text" 
                                name="pay_period_schedule_data[name]" 
                                value="{{$pay_period_schedule_data['name'] ?? ''}}"
                            >
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input 
                                class="form-control"
                                type="text" 
                                size="40" 
                                name="pay_period_schedule_data[description]" 
                                value="{{$pay_period_schedule_data['description'] ?? ''}}"
                            >
                        </div>
                        <div class="form-group">
                            <label for="start_week_day_id">Overtime Week</label>
                            <select 
                                id="start_week_day_id" 
                                class="form-select" 
                                name="pay_period_schedule_data[start_week_day_id]" 
                            >
                                @foreach ($pay_period_schedule_data['start_week_day_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_period_schedule_data['start_week_day_id']) && $id == $pay_period_schedule_data['start_week_day_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="time_zone">Time Zone</label>
                            <select 
                                id="time_zone" 
                                class="form-select" 
                                name="pay_period_schedule_data[time_zone]" 
                            >
                                @foreach ($pay_period_schedule_data['time_zone_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_period_schedule_data['time_zone']) && $id == $pay_period_schedule_data['time_zone'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($pay_period_schedule_data['day_start_time'] != '')
                            <div class="form-group">
                                <label>Pay Period Daily Start Time</label>
                                <input 
                                    class="form-control"
                                    type="text" 
                                    size="7" 
                                    id="daily_start_time"
                                    name="pay_period_schedule_data[day_start_time]" 
                                    value="{{$pay_period_schedule_data['day_start_time']  ?? ''}}"
                                    onChange="changeDailyStartTime()"
                                > hh:mm (2:15) (Hours from Midnight)
                            </div>
                        @else
                        <input 
                            type="hidden" 
                            id="daily_start_time" 
                            name="pay_period_schedule_data[day_start_time]" 
                            value="{{$pay_period_schedule_data['day_start_time'] ?? ''}}"
                        >
                        @endif

                        <div class="form-group">
                            <label>Minimum Time-Off Between Shifts</label>
                            <input class="form-control" type="text" size="7" name="pay_period_schedule_data[new_day_trigger_time]" value="{{$pay_period_schedule_data['new_day_trigger_time'] ?? ''}}"> (hh:mm (2:15)) (Only for shifts that span midnight)
                        </div>

                        <div class="form-group">
                            <label>Maximum Shift Time</label>
                            <input class="form-control" type="text" size="7" name="pay_period_schedule_data[maximum_shift_time]" value="{{$pay_period_schedule_data['maximum_shift_time'] ?? ''}}"> hh:mm (2:15)
                        </div>

                        <div class="form-group">
                            <label for="shift_assigned_day_id">Assign Shifts To</label>
                            <select 
                                id="shift_assigned_day_id" 
                                class="form-select" 
                                name="pay_period_schedule_data[shift_assigned_day_id]" 
                            >
                                @foreach ($pay_period_schedule_data['shift_assigned_day_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_period_schedule_data['shift_assigned_day_id']) && $id == $pay_period_schedule_data['shift_assigned_day_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bg-primary text-white mt-4 text-center">TimeSheet Verification</div>

                        <div class="form-group">
                            <label for="timesheet_verify_type_id">TimeSheet Verification</label>
                            <select 
                                id="timesheet_verify_type_id" 
                                class="form-select" 
                                name="pay_period_schedule_data[timesheet_verify_type_id]" 
                            >
                                @foreach ($pay_period_schedule_data['timesheet_verify_type_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_period_schedule_data['timesheet_verify_type_id']) && $id == $pay_period_schedule_data['timesheet_verify_type_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="timesheet_verify" style="display:none">
                            <div class="form-group">
                                <label>Verification Window Starts</label>
                                <input 
                                    class="form-control" 
                                    type="text" 
                                    size="3" 
                                    name="pay_period_schedule_data[timesheet_verify_before_end_date]" 
                                    value="{{$pay_period_schedule_data['timesheet_verify_before_end_date'] ?? ''}}"
                                >Day(s) (<b>Before Pay Period End Date</b>)
                            </div>
                            <div class="form-group">
                                <label>Verification Window Ends</label>
                                <input 
                                    class="form-control" 
                                    type="text" 
                                    size="3" 
                                    name="pay_period_schedule_data[timesheet_verify_before_transaction_date]" 
                                    value="{{$pay_period_schedule_data['timesheet_verify_before_transaction_date'] ?? ''}}"
                                >Day(s) (<b>Before Pay Period Transaction Date</b>)
                            </div>
                        </div>
                        
                        <div class="bg-primary text-white mt-4 text-center">Pay Period Dates</div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <select 
                                id="type" 
                                class="form-select" 
                                name="pay_period_schedule_data[type]" 
                            >
                                @foreach ($pay_period_schedule_data['type_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_period_schedule_data['type']) && $id == $pay_period_schedule_data['type'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="type_id-5" style="display:none">
                            <div class="form-group">
                                <label>Annual Pay Periods</label>
                                <input 
                                    class="form-control" 
                                    type="text" 
                                    size="7" 
                                    id="annual_pay_periods" 
                                    name="pay_period_schedule_data[annual_pay_periods]" 
                                    value="{{$pay_period_schedule_data['annual_pay_periods'] ?? ''}}"
                                >
                            </div>
                        </div>

                        <div id="type_id-10" style="display:none">
                            <div class="form-group">
                                <label for="start_day_of_week">Pay Period Starts On</label>
                                <select 
                                    id="start_day_of_week" 
                                    class="form-select" 
                                    name="pay_period_schedule_data[start_day_of_week]" 
                                >
                                    @foreach ($pay_period_schedule_data['day_of_week_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($pay_period_schedule_data['start_day_of_week']) && $id == $pay_period_schedule_data['start_day_of_week'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                                at <b><span id="start_day_of_week_start_time">00:00</span></b>
                            </div>

                            <div class="form-group">
                                <label for="transaction_date">Transaction Date</label>
                                <select 
                                    id="transaction_date" 
                                    class="form-select" 
                                    name="pay_period_schedule_data[transaction_date]" 
                                >
                                    @foreach ($pay_period_schedule_data['transaction_date_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($pay_period_schedule_data['transaction_date']) && $id == $pay_period_schedule_data['transaction_date'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                                (days after end of pay period)
                            </div>
                        </div>

                        <div id="type_id-50" style="display:none">
                            <div class="bg-primary text-white mt-4 text-center">Primary</div>

                            <div class="form-group">
                                <label for="primary_day_of_month">Pay Period Start Day Of Month</label>
                                <select 
                                    id="primary_day_of_month" 
                                    class="form-select" 
                                    name="pay_period_schedule_data[primary_day_of_month]" 
                                >
                                    @foreach ($pay_period_schedule_data['day_of_month_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($pay_period_schedule_data['primary_day_of_month']) && $id == $pay_period_schedule_data['primary_day_of_month'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>at <b><span id="primary_day_of_month_start_time">00:00</span></b>
                            </div>
                            
                            <div class="form-group">
                                <label for="primary_transaction_day_of_month">Transaction Day Of Month</label>
                                <select 
                                    id="primary_transaction_day_of_month" 
                                    class="form-select" 
                                    name="pay_period_schedule_data[primary_transaction_day_of_month]" 
                                >
                                    @foreach ($pay_period_schedule_data['day_of_month_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($pay_period_schedule_data['primary_transaction_day_of_month']) && $id == $pay_period_schedule_data['primary_transaction_day_of_month'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div id="type_id-30" style="display:none">
                            <div class="bg-primary text-white mt-4 text-center">Secondary</div>

                            <div class="form-group">
                                <label for="secondary_day_of_month">Pay Period Start Day Of Month</label>
                                <select 
                                    id="secondary_day_of_month" 
                                    class="form-select" 
                                    name="pay_period_schedule_data[secondary_day_of_month]" 
                                >
                                    @foreach ($pay_period_schedule_data['day_of_month_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($pay_period_schedule_data['secondary_day_of_month']) && $id == $pay_period_schedule_data['secondary_day_of_month'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>at <b><span id="secondary_day_of_month_start_time">00:00</span></b>
                            </div>

                            <div class="form-group">
                                <label for="secondary_transaction_day_of_month">Transaction Day Of Month</label>
                                <select 
                                    id="secondary_transaction_day_of_month" 
                                    class="form-select" 
                                    name="pay_period_schedule_data[secondary_transaction_day_of_month]" 
                                >
                                    @foreach ($pay_period_schedule_data['day_of_month_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($pay_period_schedule_data['secondary_transaction_day_of_month']) && $id == $pay_period_schedule_data['secondary_transaction_day_of_month'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="transaction_date_bd" style="display:none">
                            <div class="form-group">
                                <label for="transaction_date_bd">Transaction Always on Business Day</label>
                                <select 
                                    id="transaction_date_bd" 
                                    class="form-select" 
                                    name="pay_period_schedule_data[transaction_date_bd]" 
                                >
                                    @foreach ($pay_period_schedule_data['transaction_date_bd_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($pay_period_schedule_data['transaction_date_bd']) && $id == $pay_period_schedule_data['transaction_date_bd'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if (empty($pay_period_schedule_data['id']))
                            <div id="display_anchor_date" style="display:none">
                                <div class="form-group">
                                    <label>Create Initial Pay Periods From</label>
                                    <input 
                                        class="form-control" 
                                        type="date" 
                                        id="anchor_date" 
                                        name="pay_period_schedule_data[anchor_date]" 
                                        value="{{$pay_period_schedule_data['anchor_date'] ?? ''}}"
                                    >
                                </div>
                            </div>
                        @endif
                        
                        <div class="mt-3 mb-3">
                            <label for="user_ids">Employees</label>
                            <div class="col-md-12">
                                <x-general.multiselect-php 
                                    title="Employees" 
                                    :data="$pay_period_schedule_data['user_options']" 
                                    :selected="!empty($pay_period_schedule_data['user_ids']) ? array_values($pay_period_schedule_data['user_ids']) : []" 
                                    :name="'pay_period_schedule_data[user_ids][]'"
                                    id="userSelector"
                                />
                            </div>
                        </div>

                        <div id="contentBoxFour">
                            <input type="submit" class="btn btn-primary btn-sm" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))" >
                        </div>

                        <input type="hidden" name="pay_period_schedule_data[id]" value="{{$pay_period_schedule_data['id'] ?? ''}}">

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
            showTimeSheetVerificationType(); 
            showType(); 
            changeDailyStartTime(); 
            filterUserCount();
        })

        function filterUserCount() {
            total = countSelect(document.getElementById('filter_user'));
            writeLayer('filter_user_count', total);
        }
        
        function showType() {
            hideObject('transaction_date_bd');
            hideObject('display_anchor_date');
            hideObject('type_id-5');
            hideObject('type_id-10');
            hideObject('type_id-30');
            hideObject('type_id-50');
        
            //alert('Type ID: '+ document.getElementById('type_id').value );
            if ( document.getElementById('type_id').value == 5 ) {
                showObject('type_id-5');
            } else if ( document.getElementById('type_id').value == 10 || document.getElementById('type_id').value == 20 ) {
                showObject('type_id-10');
        
                showObject('transaction_date_bd');
                showObject('display_anchor_date');
            } else if ( document.getElementById('type_id').value == 30 ) {
                showObject('type_id-30');
                showObject('type_id-50');
        
                showObject('transaction_date_bd');
                showObject('display_anchor_date');
        
            } else if ( document.getElementById('type_id').value == 50 ) {
                showObject('type_id-50');
        
                showObject('transaction_date_bd');
                showObject('display_anchor_date');
            }
        }
        
        function showTimeSheetVerificationType() {
            hideObject('timesheet_verify');
        
            //alert('Type ID: '+ document.getElementById('type_id').value );
            if ( document.getElementById('timesheet_verify_type_id').value > 10 ) {
                showObject('timesheet_verify');
            }
        }
        
        function changeDailyStartTime() {
            daily_start_time = document.getElementById('daily_start_time').value
            document.getElementById('start_day_of_week_start_time').innerHTML = daily_start_time;
            document.getElementById('primary_day_of_month_start_time').innerHTML = daily_start_time;
            document.getElementById('secondary_day_of_month_start_time').innerHTML = daily_start_time;
        
        }
    </script>
</x-app-layout>
