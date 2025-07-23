<x-app-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>
    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                </div>

                <div class="card-body">

                    <form method="POST" name="Schedule" action="{{ route('schedule.edit_schedule') }}">
                        @csrf

                        <div id="contentBoxTwoEdit">
                            @if (!$sf->Validator->isValid())
                                {{-- error list --}}
                                {{-- {include file="form_errors.tpl" object="sf"} --}}
                            @endif

                            <table class="table table-bordered">
                            {{-- @if ($data['pay_period_is_locked'] == TRUE)
                                <tr class="tblDataError">
                                    <td colspan="2">
                                        <b>NOTICE:</b> This pay period is currently locked, modifications are not permitted.
                                    </td>
                                </tr>
                            @endif --}}

                                <tr>
                                    <th>Employee: </th>
                                    <td class="cellRightEditTable">
                                        <select id="user_id" name="data[user_id]">
                                            @foreach($data['user_options'] as $key => $value)
                                                <option value="{{ $key }}" @if($key == $data['user_id']) selected @endif>{{ $value }}</option>
                                            @endforeach
                                        </select>

                                {{-- {*
                                        {if $data.user_id == ''}
                                        {else}
                                            {$data.user_full_name}
                                            <input type="hidden" id="user_id" name="data[user_id]" value="{$data.user_id}">
                                            <input type="hidden" name="data[user_full_name]" value="{$data.user_full_name}">
                                        {/if}
                                *} --}}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Status: </th>
                                    <td class="cellRightEditTable">
                                        <select id="status_id" name="data[status_id]" onChange="showAbsencePolicy();">
                                            @foreach($data['status_options'] as $key => $value)
                                                <option value="{{ $key }}" @if($key == $data['status_id']) selected @endif>{{ $value }}</option>
                                            @endforeach
                                        </select>

                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Date:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="date" id="date" name="data[date_stamp]"
                                            value="{{ $data['date_stamp'] ? date('Y-m-d', $data['date_stamp']) : date('Y-m-d') }}">

                                        ie: {{ $current_user_prefs->getDateFormatExample() }}
                                    </td>
                                </tr>


                                <tbody id="repeat" style="display:none">
                                <tr>
                                    <th>Repeat Schedule for: </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="3" id="time_stamp" name="data[repeat]" value="0"> day(s) after above date.
                                    </td>
                                </tr>
                                </tbody>

                                <tr>
                                    <th>In: </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="8" id="start_time" name="data[start_time]" value="{{ getdate_helper('time', $data['parsed_start_time'] ?? '' )}}" onChange="getScheduleTotalTime();">
                                        ie: {{$current_user_prefs->getTimeFormatExample()}}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Out: </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="8" id="end_time" name="data[end_time]" value="{{ getdate_helper('time', $data['parsed_end_time'] ?? '' )}}" onChange="getScheduleTotalTime();">
                                        ie: {{$current_user_prefs->getTimeFormatExample()}}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Total: </th>
                                    <td class="cellRightEditTable">
                                        <span id="total_time">
                                            {{ gettimeunit_helper($data['total_time'], true) }}
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Schedule Policy: </th>
                                    <td class="cellRightEditTable">
                                        <select id="schedule_policy_id" name="data[schedule_policy_id]" onChange="getScheduleTotalTime();">
                                            @foreach($data['schedule_policy_options'] as $key => $value)
                                                <option value="{{ $key }}" @if($key == $data['schedule_policy_id']) selected @endif>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tbody id="absence" style="display:none">
                                    <tr>
                                        <th>Absence Policy: </th>
                                        <td class="cellRightEditTable">
                                            <select id="absence_policy_id" name="data[absence_policy_id]" onChange="getAbsencePolicyBalance();">
                                                @foreach($data['absence_policy_options'] as $key => $value)
                                                    <option value="{{ $key }}" @if($key == $data['absence_policy_id']) selected @endif>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                            <br>
                                            Accrual Policy: <span id="accrual_policy_name">None</span><br>
                                            Available Balance: <span id="accrual_policy_balance">N/A</span><br>
                                        </td>
                                    </tr>
                                </tbody>

                                <!-- Branch Dropdown -->
                                <tr>
                                    <th>Branch:</th>
                                    <td class="cellRightEditTable">
                                        <select id="branch_id" name="data[branch_id]">
                                            @foreach($data['branch_options'] as $key => $value)
                                                <option value="{{ $key }}" @if((int)$key === (int)$data['branch_id']) selected @endif>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <!-- Department Dropdown -->
                                <tr>
                                    <th>Department:</th>
                                    <td class="cellRightEditTable">
                                        <select id="department_id" name="data[department_id]">
                                            @foreach($data['department_options'] as $key => $value)
                                                <option value="{{ $key }}" @if((int)$key === (int)$data['department_id']) selected @endif>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>


                            {{-- job part removed --}}

                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <input type="hidden" name="data[id]" value="{{$data['id'] ?? ''}}">
                            <input type="submit" class="btn btn-primary" name="action" value="Submit" @if ($data['pay_period_is_locked']) disabled @endif
                                onClick="return singleSubmitHandler(this)">

                            {{-- DELETE Button with Form --}}
                            @if ($data['id'] != '' &&
                            ($permission->Check('schedule','delete') ||
                            ($permission->Check('schedule','delete_child') && $data['is_child'] === TRUE) ||
                            ($permission->Check('schedule','delete_own') && $data['is_owner'] === TRUE))
                            )

                            <button type="button" class="btn btn-danger" @if ($data['pay_period_is_locked']) disabled @endif onclick="commonDeleteFunction('/schedule/edit_schedule/delete/{{ $data['id'] }}', 'Schedule', this)">
                                Delete
                            </button>
                            @endif

                        </div>
                    </form>

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script language="JavaScript">
        $(document).ready(function(){
            fixHeight(); showAbsencePolicy(); getAbsencePolicyBalance(); getScheduleTotalTime(); TIMETREX.punch.getJobManualId(); TIMETREX.punch.getJobItemManualId(); TIMETREX.punch.showJob(); TIMETREX.punch.showJobItem();
        })

        var jmido={js_array values=$data.job_manual_id_options name="jmido" assoc=true}
        var jimido={js_array values=$data.job_item_manual_id_options name="jimido" assoc=true}

        {literal}
        function fixHeight() {
            resizeWindowToFit(document.getElementById('body'), 'height', 100);
        }

        function showAbsencePolicy() {
            status_obj = document.getElementById('status_id');
            absence_obj = document.getElementById('absence');
            if ( status_obj[status_obj.selectedIndex].value == 10 ) {
                absence_obj.className = '';
                absence_obj.style.display = 'none';
            } else {
                absence_obj.className = '';
                absence_obj.style.display = '';
            }

            fixHeight();
        }

        function getAbsencePolicyBalance() {
            document.getElementById('accrual_policy_name').innerHTML = 'None';
            document.getElementById('accrual_policy_balance').innerHTML = 'N/A';

            if ( document.getElementById('absence_policy_id').value != 0 ) {
                remoteHW.getAbsencePolicyBalance( document.getElementById('absence_policy_id').value, document.getElementById('user_id').value);
                remoteHW.getAbsencePolicyData( document.getElementById('absence_policy_id').value );
            }
        }

        var loading = false;
        var hwCallback = {
            getScheduleTotalTime: function(result) {
                if ( result != false ) {
                    //alert('aWeek Row: '+ week_row);
                    document.getElementById('total_time').innerHTML = result;
                }
            },
            getJobOptions: function(result) {
                if ( result != false ) {
                    TIMETREX.punch.getJobOptionsCallBack( result );
                }
                loading = false;
            },
            getJobItemOptions: function(result) {
                if ( result != false ) {
                    TIMETREX.punch.getJobItemOptionsCallBack( result );
                }
                loading = false;
            },
            getAbsencePolicyBalance: function(result) {
                if ( result == false ) {
                    result = 'N/A';
                }
                document.getElementById('accrual_policy_balance').innerHTML = result;
            },
            getAbsencePolicyData: function(result) {
                if ( result == false ) {
                    result = 'None';
                } else {
                    result = result.accrual_policy_name;
                }
                document.getElementById('accrual_policy_name').innerHTML = result;
            }
        }

        var remoteHW = new AJAX_Server(hwCallback);

        function getScheduleTotalTime() {
            start_time = document.getElementById('start_time').value;
            end_time = document.getElementById('end_time').value;
            schedule_policy_obj = document.getElementById('schedule_policy_id');
            schedule_policy_id = schedule_policy_obj[schedule_policy_obj.selectedIndex].value;


            if ( start_time != '' && end_time != '' ) {
                remoteHW.getScheduleTotalTime( start_time, end_time, schedule_policy_id );
            }
        }
        {/literal}
    </script>
</x-app-layout>
