<x-app-modal-layout :title="'Input Example'">
    <style>
        .main-content{
            margin-left: 0 !important;
        }
        .page-content{
            padding: 10px !important;
        }
        th, td{
            padding: 2px !important;
        }
    </style>
    <div class="">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- ---------------------------------------------- --}}

                    <form method="post" name="wage" action="/attendance/punch/edit_user_absence">
                        <div id="contentBoxTwoEdit">
                            @if (!$udtf->Validator->isValid())
                                {{-- error list here --}}
                            @endif
            
                            <table class="table table-bordered">
            
                                <tr>
                                    <th>
                                        Employee:
                                    </th>
                                    <td>
                                        {{$udt_data['user_full_name']}}
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        <a href="javascript:toggleRowObject('advance');toggleIcon(document.getElementById('advance_img'))">
                                            <i id="advance_img" class="ri-arrow-down-double-line" style="vertical-align: middle;"></i>
                                        </a> Date:
                                    </th>
                                    <td>
                                        {{ getdate_helper('date', $udt_data['date_stamp']) }}
                                    </td>
                                </tr>
                
                                <tbody id="advance" style="display:none">
                                <tr>
                                    <th>
                                        Repeat Absence for:
                                    </th>
                                    <td>
                                        <input type="text" size="3" id="time_stamp" name="udt_data[repeat]" value="{{$udt_data['repeat'] ?? 0}}"> day(s) after above date.
                                    </td>
                                </tr>
                                </tbody>
                
                                <tr>
                                    <th>
                                        Time:
                                    </th>
                                    <td>           
                                        <select id="leave_total_time" name="udt_data[absence_leave_id]" onChange="UpdateTotalLeaveTime();">
                                            @foreach ($udt_data['leave_day_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}"
                                                    {{(isset($udt_data['absence_leave_id']) && $udt_data['absence_leave_id'] == $id) ? 'selected' : ''}}
                                                >
                                                {{$name}}
                                                </option>
                                            @endforeach
                                        </select>
                                    
                                        <input  type="text" id="total_time_text" size="8" name="udt_data[total_time]" value="{{ gettimeunit_helper($udt_data['total_time'], '00:00') }}">
                                        ie: {{$current_user_prefs->getTimeUnitFormatExample()}}
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Type:
                                    </th>
                                    <td>
                                        <select id="absence_policy_id" name="udt_data[absence_policy_id]" onChange="getAbsencePolicyBalance();">
                                            @foreach ($udt_data['absence_policy_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}"
                                                    {{(isset($udt_data['absence_policy_id']) && $udt_data['absence_policy_id'] == $id) ? 'selected' : ''}}
                                                >
                                                {{$name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        <br>
                                        Accrual Policy: <span id="accrual_policy_name">None</span><br>
                                        Available Balance: <span id="accrual_policy_balance">N/A</span><br>
                                        <input type="hidden" name="udt_data[old_absence_policy_id]" value="{$udt_data.absence_policy_id}">
                                    </td>
                                </tr>
                
                                @if ($permission->Check('absence','edit_branch'))
                                    <tr>
                                        <th>
                                            Branch:
                                        </th>
                                        <td>
                                            <select id="branch_id" name="udt_data[branch_id]">
                                                @foreach ($udt_data['branch_options'] as $id => $name)
                                                    <option 
                                                        value="{{$id}}"
                                                        {{(isset($udt_data['branch_id']) && $udt_data['branch_id'] == $id) ? 'selected' : ''}}
                                                    >
                                                    {{$name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endif
                                
                                @if ($permission->Check('absence','edit_department'))
                                    <tr>
                                        <th>
                                            Department:
                                        </th>
                                        <td>
                                            <select id="department_id" name="udt_data[department_id]">
                                                @foreach ($udt_data['department_options'] as $id => $name)
                                                    <option 
                                                        value="{{$id}}"
                                                        {{(isset($udt_data['department_id']) && $udt_data['department_id'] == $id) ? 'selected' : ''}}
                                                    >
                                                    {{$name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr>
                                    <th>
                                        Override:
                                    </th>
                                    <td>
                                        <input type="checkbox" class="checkbox" name="udt_data[override]" value="1" {{$udt_data['override'] == TRUE ? 'checked' : ''}} >
                                    </td>
                                </tr>
            
                            </table>
                        </div>
                
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit btn btn-primary btn-sm" name="action:submit"  onClick="return singleSubmitHandler(this)">
                            @if (!empty($udt_data['id']) AND ( $permission->Check('absence','delete') OR $permission->Check('absence','delete_own') OR $permission->Check('absence','delete_child') ))
                                <input type="submit" class="btnDelete1" name="action:delete"   onClick="return singleSubmitHandler(this)">
                            @endif
                        </div>
                
                        <input type="hidden" name="udt_data[id]" value="{{$udt_data['id'] ?? ''}}">
                        <input type="hidden" name="udt_data[user_id]" value="{{$udt_data['user_id']}}">
                        <input type="hidden" name="udt_data[user_full_name]" value="{{$udt_data['user_full_name']}}">
                        <input type="hidden" name="udt_data[date_stamp]" value="{{$udt_data['date_stamp']}}">
                        <input type="hidden" name="udt_data[user_date_id]" value="{{$udt_data['user_date_id']}}">
                    </form>

                    {{-- ---------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            fixHeight();  
            //TIMETREX.punch.getJobManualId(); 
            //TIMETREX.punch.getJobItemManualId(); 
            //TIMETREX.punch.showJobItem( true ); 
            getAbsencePolicyBalance()
        })

        function fixHeight() {
            resizeWindowToFit(document.getElementById('body'), 'height', 45);
        }

        var hwCallback = {
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
                        getAbsenceLeave: function(result) {
                    if ( result == false ) {
                        result = '0';
                    } 
                                
                                var h = Math.floor(result / 3600);
                                var m = Math.floor(result % 3600 / 60); 
                                document.getElementById('total_time_text').value = ("0" + h).slice(-2)+':'+("0" + m).slice(-2);  
                    
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

        function getAbsencePolicyBalance() {
            document.getElementById('accrual_policy_name').innerHTML = 'None';
            document.getElementById('accrual_policy_balance').innerHTML = 'N/A';

            if ( document.getElementById('absence_policy_id').value != 0 ) {
                remoteHW.getAbsencePolicyBalance( document.getElementById('absence_policy_id').value, {/literal}{$udt_data.user_id}{literal});
                remoteHW.getAbsencePolicyData( document.getElementById('absence_policy_id').value );
            }
        }
        

        //FL HARDCODED FOR LEAVE SYSTEM 20160715
        function UpdateTotalLeaveTime() {  
            var selectedLeaveId = document.getElementById('leave_total_time').value;
            remoteHW.getAbsenceLeave(selectedLeaveId);  
        }

        function toggleIcon(icon) {
            if (icon.classList.contains('ri-arrow-down-double-line')) {
                icon.classList.remove('ri-arrow-down-double-line');
                icon.classList.add('ri-arrow-up-double-line');
            } else {
                icon.classList.remove('ri-arrow-up-double-line');
                icon.classList.add('ri-arrow-down-double-line');
            }
        }
    </script>

</x-app-modal-layout>