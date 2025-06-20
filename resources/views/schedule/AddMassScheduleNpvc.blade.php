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

                    <form method="post" name="mass_schedule" action="{$smarty.server.SCRIPT_NAME}">
                        <div id="contentBoxTwoEdit">
                            @if (!$sf->Validator->isValid())
                                {{-- error list here --}}
                                {{-- {include file="form_errors.tpl" object="sf"} --}}
                            @endif
            
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
                                                <select name="src_user_id" id="src_filter_user" style="width:90%;margin:5px 0 5px 0;" size="{{select_size([ 'array'=>$user_options])}}" multiple>
                                                    {{html_options([ 'options'=>$user_options])}}
                                                </select>
                                            </td>
                                            <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                                <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('filter_user'), document.getElementById('filter_user'), {{select_size([ 'array'=>$user_options])}})"><i class="ri-arrow-right-double-fill arrow-icon" style="vertical-align: middle"></i></a>
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
                                                    {{html_options([ 'options'=>$filter_user_options, 'selected'=>$filter_user_id])}}
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
                                        {{html_options([ 'options'=>$data['status_options'], 'selected'=>$data['status_id']])}}
                                    </select>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Rosters:
                                </th>
                                <td class="cellRightEditTable">
                                    <table border="1">
                                                                <tr>
                                                                    <th></th>
                                                                    <th><p>Shifts</p></th>
                                                                    <th><p>Start Date</p></th>
                                                                    <th><p>End Date</p></th>
                                                                    <th><p>Days Recurring</p></th>
                                                                    <th><p>Days Gap</p></th>
                                                                </tr>
            
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" class="checkbox" name="data[shifts][0]" value="1" {{$data['shifts'][0] ? 'checked' : ''}} >
                                                                    </td> 
                                                                    <td>
                                                                        <p>Morning Shift: </p>
                                                                    </td> 
                                                                    <td>
                                                                        <input type="date" id="start_date_0" name="data[start_date][0]" value="{{getdate_helper('date', $data['start_date_0'])}}">
                                                                    </td> 
                                                                    <td>
                                                                        <input type="date" id="end_date_0" name="data[end_date][0]" value="{{getdate_helper('date', $data['end_date_0'])}}">
                                                                    </td> 
                                                                    <td>
                                                                        <input type="text" size="8" id="days_rec_0" name="data[days_rec_0]" value="{{$data['days_rec_0']}}" onChange="">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" size="8" id="days_gap_0" name="data[days_gap_0]" value="{{$data['days_gap_0']}}" onChange="">
                                                                    </td>
                                                                </tr>
            
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" class="checkbox" name="data[shifts][1]" value="1" {{$data['shifts'][1] ? 'checked' : ''}} >
                                                                    </td>
                                                                    <td>
                                                                        <p>Evening Shift: </p>
                                                                    </td> 
                                                                    <td>
                                                                        <input type="date" id="start_date_1" name="data[start_date][1]" value="{{getdate_helper('date', $data['start_date_1'])}}">
                                                                    </td> 
                                                                    <td>
                                                                        <input type="date" id="end_date_1" name="data[end_date][1]" value="{{getdate_helper('date', $data['end_date_1'])}}">
                                                                    </td> 
                                                                    <td>
                                                                        <input type="text" size="8" id="days_rec_1" name="data[days_rec_1]" value="{{$data['days_rec_1']}}" onChange="">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" size="8" id="days_gap_1" name="data[days_gap_1]" value="{{$data['days_gap_1']}}" onChange="">
                                                                    </td>
                                                                </tr>
            
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" class="checkbox" name="data[shifts][2]" value="1" {{ $data['shifts'][2] ? 'checked' : '' }} >
                                                                    </td>
                                                                    <td>
                                                                        <p>Night  Shift: </p>
                                                                    </td> 
                                                                    <td>
                                                                        <input type="date" id="start_date_2" name="data[start_date][2]" value="{{getdate_helper('date', $data['start_date_2'])}}">
                                                                    </td> 
                                                                    <td>
                                                                        <input type="data" id="end_date_2" name="data[end_date][2]" value="{{getdate_helper('date', $data['end_date_2'])}}">
                                                                    </td> 
                                                                    <td>
                                                                        <input type="text" size="8" id="days_rec_2" name="data[days_rec_2]" value="{{$data['days_rec_2']}}" onChange="">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" size="8" id="days_gap_2" name="data[days_gap_2]" value="{{$data['days_gap_2']}}" onChange="">
                                                                    </td> 
                                                                </tr>
            
                                                                <!-- <tr>
                                                                    <td>
                                                                        <input type="checkbox" class="checkbox" name="data[shifts][3]" value="1" {if $data.shifts.3 == TRUE}checked{/if}>
                                                                    </td>
                                                                    <td>
                                                                        <p>Week Off: </p>
                                                                    </td> 
                                                                    <td>
                                                                        <input type="text" size="15" id="start_date_3" name="data[start_date][3]" value="{getdate type="DATE" epoch=$data.start_date_3}">
                                                                        <img src="{$BASE_URL}/images/cal.gif" id="cal_start_date_3" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date_3', 'cal_start_date_3', false);">
                                                                    </td> 
                                                                    <td>
                                                                        <input type="text" size="15" id="end_date_3" name="data[end_date][3]" value="{getdate type="DATE" epoch=$data.end_date_3}">
                                                                        <img src="{$BASE_URL}/images/cal.gif" id="cal_end_date_3" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date_3', 'cal_end_date_3', false);">
                                                                    </td> 
                                                                    <td>
                                                                        <input type="text" size="8" id="days_rec_3" name="data[days_rec_3]" value="{$data.days_rec_3}" onChange="">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" size="8" id="days_gap_3" name="data[days_gap_3]" value="{$data.days_gap_3}" onChange="">
                                                                    </td> 
                                                                </tr> -->
                                                            </table>
                                </td>
                            </tr>
            
                            
             
             
            
                             
            
                            <tr>
                                <th>
                                    Schedule Policy:
                                </th>
                                <td class="cellRightEditTable">
                                    <select id="schedule_policy_id" name="data[schedule_policy_id]" onChange="getScheduleTotalTime();">
                                        {{html_options([ 'options'=>$data['schedule_policy_options'], 'selected'=>$data['schedule_policy_id']])}}
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
                                        {{html_options([ 'options'=>$data['absence_policy_options'], 'selected'=>$data['absence_policy_id']])}}
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
                                            {{html_options([ 'options'=>$data['branch_options'], 'selected'=>$data['branch_id']])}}
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
                                            {{html_options([ 'options'=>$data['department_options'], 'selected'=>$data['department_id']])}}
                                        </select>
                                    </td>
                                </tr>
                            @endif
            
                            <tr>
                                <th>
                                    Overwrite Existing Shifts:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="checkbox" class="checkbox" name="data[overwrite]" value="1" {{$data['overwrite'] ? 'checked' : ''}} >
                                </td>
                            </tr>
                        </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))">
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
            showAbsencePolicy(); getScheduleTotalTime(); getJobManualId(); getJobItemManualId();
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