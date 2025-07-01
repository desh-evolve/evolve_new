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

                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="POST" name="mass_schedule" action="{{ route('schedule.add_mass_schedule') }}">
                        @csrf

                        <div id="contentBoxTwoEdit">
                            {{-- @if (!$sf->Validator->isValid()) --}}
                                {{-- error list here --}}
                                {{-- {include file="form_errors.tpl" object="sf"} --}}
                            {{-- @endif --}}

                            <table class="table table-bordered">

                            <tr>
                                <th>
                                    Employee(s):
                                </th>
                                <td>
                                    <table class="table table-bordered">
                                        <tr class="bg-primary text-white">
                                            <td>
                                                UnSelected Employees
                                            </td>
                                            <td>
                                                <br>
                                            </td>
                                            <td>
                                                Selected Employees
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="cellRightEditTable" width="50%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_user'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_user'))">
                                                <br>
                                                <select name="src_user_id" id="src_filter_user" style="width:90%;margin:5px 0 5px 0;" size="{{select_size(['array'=>$user_options])}}" multiple>
                                                    {!! html_options(['options'=>$user_options]) !!}
                                                </select>
                                            </td>
                                            <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                                <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('filter_user'), document.getElementById('filter_user'), {{select_size( ['array'=>$user_options])}})"><i class="ri-arrow-right-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                                <br>
                                                <a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('src_filter_user'), {{select_size([ 'array'=>$filter_user_options])}})"><i class="ri-arrow-left-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                                <br>
                                                <br>
                                                <br>
                                                <a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
                                            </td>
                                            <td class="cellRightEditTable" width="50%"  align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_user'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_user'))">
                                                <br>
                                                <select name="filter_user_id[]" id="filter_user" style="width:90%;margin:5px 0 5px 0;" size="{{select_size([ 'array'=>$user_options])}}" multiple>
                                                    {!! html_options([ 'options'=>$filter_user_options, 'selected'=>$filter_user_id ?? '']) !!}
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Status:
                                </th>
                                <td class="cellRightEditTable">
                                    <select id="status_id" name="data[status_id]" onChange="showAbsencePolicy();">
                                        {!! html_options([ 'options'=>$data['status_options'], 'selected'=>$data['status_id'] ?? '']) !!}
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    In:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="8" id="start_time" name="data[start_time]" value="{{getdate_helper('time', $data['parsed_start_time'] ?? '')}}" onChange="getScheduleTotalTime();">
                                    ie: {{$current_user_prefs->getTimeFormatExample()}}
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Out:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="8" id="end_time" name="data[end_time]" value="{{getdate_helper('time', $data['parsed_end_time'] ?? '')}}" onChange="getScheduleTotalTime();">
                                    ie: {{$current_user_prefs->getTimeFormatExample()}}
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Total:
                                </th>
                                <td class="cellRightEditTable">
                                    <span id="total_time">
                                        {{gettimeunit_helper($data['total_time'] ?? '', true)}}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Start Date:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="date" id="start_date_stamp" name="data[start_date_stamp]" value="{{getdate_helper('date', $data['start_date_stamp'])}}">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    End Date:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="date"id="end_date_stamp" name="data[end_date_stamp]" value="{{getdate_helper('date', $data['end_date_stamp'])}}">
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Only These Day(s):
                                </th>
                                <td class="cellRightEditTable">
                                    <table width="1">
                                    <table width="280">
                                        <tr style="text-align:center; font-weight: bold">
                                            <td>
                                                Sun
                                            </td>
                                            <td>
                                                Mon
                                            </td>
                                            <td>
                                                Tue
                                            </td>
                                            <td>
                                                Wed
                                            </td>
                                            <td>
                                                Thu
                                            </td>
                                            <td>
                                                Fri
                                            </td>
                                            <td>
                                                Sat
                                            </td>
                                        </tr>
                                        <tr style="text-align:center;">
                                            <td >
                                                {{-- <input type="checkbox" class="checkbox" name="data[dow][0]" value="1" {{!empty($data['dow']) && $data['dow'][0] ? 'checked' : ''}} > --}}
                                                <input type="checkbox" class="checkbox" name="data[dow][0]" value="1" {{ isset($data['dow'][0]) && $data['dow'][0] ? 'checked' : '' }} >
                                            </td>
                                            <td >
                                                {{-- <input type="checkbox" class="checkbox" name="data[dow][1]" value="1" {{!empty($data['dow']) && $data['dow'][1] ? 'checked' : ''}} > --}}
                                                <input type="checkbox" class="checkbox" name="data[dow][1]" value="1" {{ isset($data['dow'][1]) && $data['dow'][1] ? 'checked' : '' }} >
                                            </td>
                                            <td >
                                                {{-- <input type="checkbox" class="checkbox" name="data[dow][2]" value="1" {{!empty($data['dow']) && $data['dow'][2] ? 'checked' : ''}} > --}}
                                                <input type="checkbox" class="checkbox" name="data[dow][2]" value="1" {{ isset($data['dow'][2]) && $data['dow'][2] ? 'checked' : '' }} >
                                            </td>
                                            <td >
                                                {{-- <input type="checkbox" class="checkbox" name="data[dow][3]" value="1" {{!empty($data['dow']) && $data['dow'][3] ? 'checked' : ''}} > --}}
                                                <input type="checkbox" class="checkbox" name="data[dow][3]" value="1" {{ isset($data['dow'][3]) && $data['dow'][3] ? 'checked' : '' }} >
                                            </td>
                                            <td >
                                                {{-- <input type="checkbox" class="checkbox" name="data[dow][4]" value="1" {{!empty($data['dow']) && $data['dow'][4] ? 'checked' : ''}} > --}}
                                                <input type="checkbox" class="checkbox" name="data[dow][4]" value="1" {{ isset($data['dow'][4]) && $data['dow'][4] ? 'checked' : '' }} >
                                            </td>
                                            <td >
                                                {{-- <input type="checkbox" class="checkbox" name="data[dow][5]" value="1" {{!empty($data['dow']) && $data['dow'][5] ? 'checked' : ''}} > --}}
                                                <input type="checkbox" class="checkbox" name="data[dow][5]" value="1" {{ isset($data['dow'][5]) && $data['dow'][5] ? 'checked' : '' }} >
                                            </td>
                                            <td >
                                                {{-- <input type="checkbox" class="checkbox" name="data[dow][6]" value="1" {{!empty($data['dow']) && $data['dow'][6] ? 'checked' : ''}} > --}}
                                                <input type="checkbox" class="checkbox" name="data[dow][6]" value="1" {{ isset($data['dow'][6]) && $data['dow'][6] ? 'checked' : '' }} >
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Schedule Policy:
                                </th>
                                <td class="cellRightEditTable">
                                    <select id="schedule_policy_id" name="data[schedule_policy_id]" onChange="getScheduleTotalTime();">
                                        {!! html_options([ 'options'=>$data['schedule_policy_options'], 'selected'=>$data['schedule_policy_id'] ?? '']) !!}
                                    </select>
                                </td>
                            </tr>

                            <tbody id="absence" style="display:none">
                            <tr>
                                <th>
                                    Absence Policy:
                                </th>
                                <td class="cellRightEditTable">
                                    <select id="absence_policy_id" name="data[absence_policy_id]">
                                        {!! html_options( ['options'=>$data['absence_policy_options'], 'selected'=>$data['absence_policy_id'] ?? '']) !!}
                                    </select>
                                </td>
                            </tr>
                            </tbody>

                            @if (count($data['branch_options']) > 1 OR $data['branch_id'] != 0)
                                <tr>
                                    <th>
                                        Branch:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select id="branch_id" name="data[branch_id]">
                                            {!! html_options([ 'options'=>$data['branch_options'], 'selected'=>$data['branch_id'] ?? '']) !!}
                                        </select>
                                    </td>
                                </tr>
                            @endif

                            @if (count($data['department_options']) > 1 OR $data['department_id'] != 0)
                                <tr>
                                    <th>
                                        Department:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select id="department_id" name="data[department_id]">
                                            {!! html_options([ 'options'=>$data['department_options'], 'selected'=>$data['department_id'] ?? '']) !!}
                                        </select>
                                    </td>
                                </tr>
                            @endif

                            {{-- job part removed --}}

                            <tr>
                                <th>
                                    Overwrite Existing Shifts:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="checkbox" class="checkbox" name="data[overwrite]" value="1" {{ !empty($data['overwrite']) && $data['overwrite'] ? 'checked' : ''}} >
                                </td>
                            </tr>
                        </table>
                        </div>

                        <div id="contentBoxFour">
                            {{-- <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))"> --}}
                            <input type="hidden" name="action" value="submit">
                            <input type="submit" class="btnSubmit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))">

                        </div>

                    </form>

                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script language="JavaScript">

        $(document).ready(function(){
            showAbsencePolicy();
            getScheduleTotalTime();
            // getJobManualId();
            // getJobItemManualId();
        })

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

    </script>
</x-app-layout>
