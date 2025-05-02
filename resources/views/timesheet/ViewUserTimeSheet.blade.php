<x-app-layout :title="'Timesheet'">

    <style>
        td, th {
            padding: 4px !important;
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
                    
                    {{-- -------------------------------------------- --}}

                    <form name="timesheet" method="get" action="#">
                        <table class="table table-bordered">
                            <tr>
                                <td class="tblPagingLeft" colspan="7" align="right">
                                    <br>
                                </td>
                            </tr>
            
                            @if (( $permission->Check('punch','view') OR $permission->Check('punch','view_child') )
                                OR ( $permission->Check('punch','add') AND ( $permission->Check('punch','edit') OR $permission->Check('punch','edit_own') OR $permission->Check('punch','edit_child') ) )
                                OR ( $permission->Check('absence','add') AND ( $permission->Check('absence','edit') OR $permission->Check('absence','edit_child') OR $permission->Check('absence','edit_own') ) ))
                                <tr class="bg-primary text-white">
                                    <td colspan="8">
                                        @if ($permission->Check('punch','view') OR $permission->Check('punch','view_child'))
                                            <span style="float:left;">
                                                &nbsp;
                                                @if (count($group_options) > 2)
                                                    Group:
                                                    <select name="filter_data[group_ids]" id="filter_branch" onChange="this.form.submit()">
                                                        @foreach ($group_options as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($filter_data['group_ids']) && $id == $filter_data['group_ids'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                
                                                @if (count($branch_options) > 1)
                                                    Branch:
                                                    <select name="filter_data[branch_ids]" id="filter_branch" onChange="this.form.submit()">
                                                        @foreach ($branch_options as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($filter_data['branch_ids']) && $id == $filter_data['branch_ids'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                                
                                                @if (count($department_options) > 1)
                                                    Dept:
                                                    <select name="filter_data[department_ids]" id="filter_department" onChange="this.form.submit()">
                                                        @foreach ($department_options as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($filter_data['department_ids']) && $id == $filter_data['department_ids'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                                
                                                <span style="white-space: nowrap;">
                                                    Employee:
                                                    <select name="filter_data[user_id]" id="filter_user" onChange="this.form.submit()">
                                                        @foreach ($user_options as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($filter_data['user_id']) && $id = $filter_data['user_id'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                </span>
                                            </span>
                                        @endif
                
                                        @if (( $permission->Check('punch','add') AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))
                                                OR ( $permission->Check('absence','add') AND ( $permission->Check('absence','edit') OR ($permission->Check('absence','edit_child') AND $is_child === TRUE) OR ($permission->Check('absence','edit_own') AND $is_owner === TRUE ))))
                                            <span style="float: right">
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                Add:
                                                @if (( $permission->Check('punch','add') AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))))
                                                    <input type="BUTTON" class="button" name="action" value="Punch" onClick="editPunch('','',{{$filter_data['user_id']}},{{$filter_data['date']}})">
                                                @endif

                                                @if (( $permission->Check('absence','add') AND ( $permission->Check('absence','edit') OR ($permission->Check('absence','edit_child') AND $is_child === TRUE) OR ($permission->Check('absence','edit_own') AND $is_owner === TRUE ))))
                                                    <input type="BUTTON" class="button" name="action" value="Absence" onClick="editAbsence('',{{$filter_data['user_id']}},{{$filter_data['date']}})">
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
            
                            <tr class="bg-primary text-white">
                                <td colspan="8">
                                    Date:
                                    <a href="prev_pp=1" onClick="resetAction();">
                                        <i class="ri-arrow-left-double-fill text-white"></i>
                                    </a>
                                    <a href="prev_week=1" onClick="resetAction();">
                                        <i class="ri-arrow-left-s-line text-white"></i>
                                    </a>
                                    <input type="date" id="filter_date" name="filter_data[date]" value="{{getdate_helper('date', $filter_data['date'])}}" onChange="resetAction();this.form.submit()">
                                    <i class="ri-calendar-2-line text-white" alt="Pick a date" onMouseOver="calendar_setup('filter_date', 'cal_filter_date', false);" id="cal_filter_date" ></i>

                                    <a href="next_week=1" onClick="resetAction();">
                                        <i class="ri-arrow-right-s-line text-white"></i>
                                    </a>
                                    <a href="next_pp=1" onClick="resetAction();">
                                        <i class="ri-arrow-right-double-fill text-white"></i>
                                    </a>
                                </td>
                            </tr>
            
                            @if (!empty($pay_period_is_locked) && $pay_period_is_locked == TRUE)
                                <tr class="tblDataError">
                                    <td colspan="8">
                                        <b>NOTICE:</b> This pay period is currently {{(!empty($pay_period_status_id) && $pay_period_status_id == 20) ? 'closed' : 'locked'}}, modifications are not permitted.
                                    </td>
                                </tr>
                            @elseif (!empty($pay_period_status_id) && $pay_period_status_id == 30)
                                <tr class="tblDataWarning">
                                    <td colspan="8">
                                        <b>NOTICE:</b> This pay period is currently in the post adjustment state.
                                    </td>
                                </tr>
                            @endif
            
                            <tr class="bg-primary text-white">
                                @foreach ($calendar_array as $calendar)
                                    @if ($loop->first)
                                        <td>
                                            {{-- <a href="{$BASE_URL}/report/TimesheetDetail.php?action:display_report=1&filter_data[print_timesheet]=1&filter_data[user_id]={{$filter_data['user_id']}}&filter_data[pay_period_ids]={{$pay_period_id}}"> --}}
                                            <a href="#">
                                                <i class="ri-printer-line text-white" alt="Print Timesheet"></i>
                                            </a>
                                            {{-- </a>&nbsp;&nbsp; --}}
                                        </td>
                                    @endif
                                    <td width="12%" id="cursor-hand" {{$calendar['epoch'] == $filter_data['date'] ? 'background-color:#33CCFF' : ''}} onClick="changeDate('{{getdate_helper('date', $calendar['epoch'])}}')">
                                        {{$calendar['day_of_week']}}
                                        <br>
                                        {{$calendar['month_short_name']}} {{$calendar['day_of_month']}}
                                        @if (isset($holidays[$calendar['epoch']]))
                                            <br>
                                            ({{$holidays[$calendar['epoch']]}})
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
            
                            @foreach ($rows as $row_num => $row)
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$row['status']}}
                                    </td>
                                    @foreach ($row['data'] as $epoch => $day)
                                        <td 
                                            @if (empty($pay_period_locked_rows[$epoch])
                                                AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))
                                                class="cellHL" id="cursor-hand" 
                                            @endif
                                            @if (empty($pay_period_locked_rows[$epoch])
                                                AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))
                                                AND empty($day['time_stamp']))
                                                onClick="editPunch('','{{$day['punch_control_id']}}',{{$filter_data['user_id']}},{{$epoch}},{{$row['status_id']}});"
                                            @endif
                                            nowrap
                                        >
                                            <table align="center" border="0" width="100%">
                                                <tr>
                                                    <td width="25%" align="left">
                                                        @if ((isset($day['id']) && isset($punch_exceptions[$day['id']])) || 
                                                                (isset($day['punch_control_id']) && isset($punch_control_exceptions[$day['punch_control_id']])))
                                                            @php
                                                                if (isset($punch_exceptions[$day['id']])) {
                                                                    $exception_arr = $punch_exceptions[$day['id']];
                                                                } else {
                                                                    $exception_arr = $punch_control_exceptions[$day['punch_control_id']];
                                                                }
                                                            @endphp
                                                            @foreach ($exception_arr as $exception_id => $exception_data)
                                                                @if ($loop->first)
                                                                    <span style="float: left">
                                                                @endif
                                                                <p color="{{$exception_data['color']}}">
                                                                    <b>{{$exception_data['exception_policy_type_id']}}</b>
                                                                </p>
                                                                @if ($loop->last)
                                                                    </span>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td width="50%" align="center" nowrap>
                                                        @if (!empty($day['time_stamp']))
                                                            @if ($day['has_note'] == TRUE)
                                                                *
                                                            @endif
                                                            @if ((empty($pay_period_locked_rows[$epoch]) || $pay_period_locked_rows[$epoch] == FALSE) AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))
                                                                <a href="javascript:editPunch({{$day['id']}})">{{getdate_helper('time',$day['time_stamp'])}}</a>
                                                            @else
                                                                {{getdate_helper('time', $day['time_stamp'])}}
                                                            @endif
                                                        @else
                                                            <br>
                                                        @endif
                                                    </td>
                                                    <td width="25%" align="right">
                                                        {{$day['type_code'] ?? ''}}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
            
                            @foreach ($date_break_total_rows as $date_break_total_row)
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$date_break_total_row['name']}}
                                    </td>
                                    @foreach ($date_break_total_row['data'] as $date_break_total_epoch => $date_break_total_day)
                                        <td>
                                            {{ gettimeunit_helper($date_break_total_day['total_time'], '00:00') }} 
                                            @if ($date_break_total_day['total_breaks'] > 1)
                                                ({{$date_break_total_day['total_breaks']}})
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            
                            @foreach ($date_break_policy_total_rows as $date_break_policy_total_row)
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$date_break_policy_total_row['name']}}
                                    </td>
                                    @foreach ($date_break_policy_total_row['data'] as $date_break_policy_total_epoch => $date_break_policy_total_day)
                                        <td>
                                            @if (isset($date_break_policy_total_day['total_time']) && $date_break_policy_total_day['total_time'] < 0)
                                                <p color="red">
                                            @endif
                                            {{ gettimeunit_helper($date_break_policy_total_day['total_time_display'] ?? 0, '00:00') }}
                                            @if (isset($date_break_policy_total_day['total_time']) && $date_break_policy_total_day['total_time'] < 0)
                                                </p>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
            

                            @foreach ($date_meal_policy_total_rows as $date_meal_total_row)
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$date_meal_total_row['name']}}
                                    </td>
                                    @foreach ($date_meal_total_row['data'] as $date_meal_total_epoch => $date_meal_total_day)
                                        <td>
                                            @if ($date_meal_total_day['total_time'] < 0)
                                                <p color="red">
                                            @endif
                                            {{ gettimeunit_helper($date_meal_total_day['total_time_display'], '00:00') }}
                                            @if ($date_meal_total_day['total_time'] < 0)
                                                </p>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            
                            @if (isset($date_exception_total_rows))
                                <tr class="">
                                    @foreach ($date_exception_total_rows as $date_exception_total_row)
                                        @if ($loop->first)
                                            <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                                Exceptions
                                            </td>
                                        @endif
                                        <td>
                                            <b>
                                                @foreach ($date_exception_total_row as $date_exception_total_day)
                                                    <p color="{$date_exception_total_day.color}">
                                                        {{$date_exception_total_day['exception_policy_type_id']}}
                                                    </p>
                                                @endforeach
                                            </b>
                                        </td>
                                    @endforeach
                                </tr>
                            @endif


                            @if (isset($date_request_total_rows))
                                <tr class="">
                                    @foreach ($date_request_total_rows as $request_epoch => $date_request_total_row)
                                        @if ($loop->first)
                                            <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                                Pending Requests
                                            </td>
                                        @endif
                                        <td>
                                            @if (isset($date_request_total_row))
                                                @php
                                                    $filter_user_id = $filter_data['user_id'];
                                                @endphp
                                                <a href="{{ urlbuilder('../request/UserRequestList.php', [
                                                    'filter_user_id' => $filter_user_id,
                                                    'filter_start_date' => $request_epoch,
                                                    'filter_end_date' => $request_epoch
                                                ], false) }}">
                                                    Yes
                                                </a>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
            

                            @foreach ($date_total_rows as $date_total_row)
                                @if ($loop->first)
                                    <tr class="bg-primary text-white">
                                        <td colspan="8">
                                            Accumulated Time
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$date_total_row['name']}}
                                    </td>
                                    @foreach ($date_total_row['data'] as $date_total_epoch => $date_total_day)
                                        <td 
                                            @if ((isset($date_total_day['type_id']) && $date_total_day['type_id'] == 10) AND (empty($pay_period_locked_rows[$date_total_epoch]) || $pay_period_locked_rows[$date_total_epoch] == FALSE)
                                            AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))
                                                class="cellHL"
                                            @endif
                                        >
                                            @if ($date_total_row['type_and_policy_id'] == 100)
                                                @if (empty($pay_period_locked_rows[$date_total_epoch])
                                                    AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))
                                                    <a href="javascript:hourList('','{{$filter_data['user_id']}}','{{$date_total_epoch}}')">
                                                @endif
                                                @if (!empty($date_total_day['override']) && $date_total_day['override'] == TRUE)
                                                    *
                                                @endif
                                                {{ gettimeunit_helper($date_total_day['total_time'] ?? 0, '00:00') }}
                                                @if (empty($pay_period_locked_rows[$date_total_epoch])
                                                    AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))
                                                    </a>
                                                @endif
                                            @else
                                                {{gettimeunit_helper($date_total_day['total_time'] ?? 0, '00:00')}}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
            
                            @foreach ($date_branch_total_rows as $date_branch_total_row)
                                @if ($loop->first)
                                    <tr class="bg-primary text-white">
                                        <td colspan="100">
                                            Branch
                                        </td>
                                    </tr>
                                @endif
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$date_branch_total_row['name']}}
                                    </td>
                                    @foreach ($date_branch_total_row['data'] as $date_branch_total_day)
                                        <td>
                                            {{gettimeunit_helper($date_branch_total_day['total_time'], '00:00')}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @foreach ($date_department_total_rows as $date_department_total_row)
                                @if ($loop->first)
                                    <tr class="bg-primary text-white">
                                        <td colspan="100">
                                            Department
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$date_department_total_row['name']}}
                                    </td>
                                    @foreach ($date_department_total_row['data'] as $date_department_total_day)
                                        <td>
                                            {{gettimeunit_helper($date_department_total_day['total_time'], '00:00')}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            
                            @foreach ($date_job_total_rows as $date_job_total_row)
                                @if ($loop->first)
                                    <tr class="bg-primary text-white">
                                        <td colspan="100">
                                            Job
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        @if ($permission->Check('job','edit') AND $date_job_total_row['id'] > 0)
                                            <a href="../job/EditJob.php?id={$date_job_total_row.id}">{{$date_job_total_row['name']}}</a>
                                        @else
                                            {{$date_job_total_row['name']}}
                                        @endif
                                    </td>
                                    @foreach ($date_job_total_row['data'] as $date_job_total_day)
                                        <td>
                                            {{gettimeunit_helper($date_job_total_day['total_time'], '00:00')}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            
                            @foreach ($date_job_item_total_rows as $date_job_item_total_row)
                                @if ($loop->first)
                                    <tr class="bg-primary text-white">
                                        <td colspan="100">
                                            Task
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        @if ($permission->Check('job_item','edit') AND $date_job_item_total_row['id'] > 0)
                                            <a href="../job_item/EditJobItem.php?id={$date_job_item_total_row.id}">{{$date_job_item_total_row['name']}}</a>
                                        @else
                                            {{$date_job_item_total_row['name']}}
                                        @endif
                                    </td>
                                    @foreach ($date_job_item_total_row['data'] as $date_job_item_total_day)
                                        <td>
                                            {{gettimeunit_helper($date_job_item_total_day['total_time'], '00:00')}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            
                            @foreach ($date_premium_total_rows as $date_premium_total_row)
                                @if ($loop->first)
                                    <tr class="bg-primary text-white">
                                        <td colspan="100">
                                            Premium
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$date_premium_total_row['name']}}
                                    </td>
                                    @foreach ($date_premium_total_row['data'] as $date_premium_total_epoch => $date_premium_total_day)
                                        <td>
                                            {{gettimeunit_helper($date_premium_total_day['total_time'] ?? 0, '00:00')}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @foreach ($date_absence_total_rows as $date_absence_total_row)
                                @if ($loop->first)
                                    <tr class="bg-primary text-white">
                                        <td colspan="100">
                                            Absence
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr class="">
                                    <td class="bg-primary text-white" style="font-weight: bold; text-align: right">
                                        {{$date_absence_total_row['name']}}
                                    </td>
                                    @foreach ($date_absence_total_row.data as $date_absence_total_epoch => $date_absence_total_day)
                                        <td 
                                            @if ((empty($pay_period_locked_rows[$date_absence_total_epoch]) || $pay_period_locked_rows[$date_absence_total_epoch] == FALSE) AND ( $permission->Check('absence','edit') OR ($permission->Check('absence','edit_child') AND $is_child === TRUE) OR ($permission->Check('absence','edit_own') AND $is_owner === TRUE )))
                                                class="cellHL" id="cursor-hand"
                                            @endif
                                            @if ($date_absence_total_day['total_time'] == '')
                                                onClick="editAbsence('','{{$filter_data['user_id']}}', '{{$date_absence_total_epoch}}')"
                                            @endif
                                        >
                                            @if ((empty($pay_period_locked_rows[$date_absence_total_epoch]) || $pay_period_locked_rows[$date_absence_total_epoch] == FALSE) AND ( $permission->Check('absence','edit') OR ($permission->Check('absence','edit_child') AND $is_child === TRUE) OR ($permission->Check('absence','edit_own') AND $is_owner === TRUE )))
                                                <a href="javascript: editAbsence({{$date_absence_total_day['id']}});">
                                            @endif
                                            @if ($date_absence_total_day['override'] == TRUE)
                                                *
                                            @endif
                                            {{gettimeunit_helper($date_absence_total_day['total_time'], '00:00')}}
                                            @if ((empty($pay_period_locked_rows[$date_absence_total_epoch]) || $pay_period_locked_rows[$date_absence_total_epoch] == FALSE) AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))
                                                </a>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
            
            
                            <tr class="">
                                <td colspan="8">
                                    @if ($is_assigned_pay_period_schedule == TRUE)
                                        Pay Period:
                                        @if (!empty($pay_period_start_date))
                                            {{getdate_helper('date', $pay_period_start_date)}} to {{date('date', $pay_period_end_date)}}
                                        @else
                                            NONE
                                        @endif
                                    @else
                                        <b>Employee is not assigned to a Pay Period Schedule.</b>
                                    @endif
                                </td>
                            </tr>
            
                            <tr valign="top">
                                <td colspan="2">
                                    @if ($permission->Check('punch','verify_time_sheet') AND (!empty($pay_period_verify_type_id) && $pay_period_verify_type_id != 10))
                                        @if ($time_sheet_verify['previous_pay_period_verification_display'] == TRUE)
                                            <table class="table table-bordered">
                                                <tr class="tblDataWarning">
                                                    <td colspan="3">
                                                        <b>Previous pay period is not verified!</b>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
            
                                        @if ($pay_period_end_date != '')
                                            <table class="table table-bordered">
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Verification
                                                    </td>
                                                </tr>
                                                <tr class="tblDataWhiteNH">
                                                    <td @if($time_sheet_verify['verification_box_color'] != '') style="background-color: {{ $time_sheet_verify['verification_box_color'] }}" @endif>
                                                        {{$time_sheet_verify['verification_status_display']}}
                                                    </td>
                                                </tr>
                
                                                @if ($time_sheet_verify['display_verify_button'] == TRUE)
                                                    <tr class="tblDataWhiteNH">
                                                        <td colspan="2" @if($time_sheet_verify['verification_box_color'] != '') style="background-color: {{ $time_sheet_verify['verification_box_color'] }}" @endif>
                                                            <input type="SUBMIT" class="button" name="action:verify" value="Verify" onClick="return confirmSubmit('By pressing OK, I hereby certify that this timesheet for the pay period of {{getdate_helper('date', $pay_period_start_date)}} to {{getdate_helper('date', $pay_period_end_date)}} is accurate and correct.');">
                                                        </td>
                                                    </tr>
                                                @endif
                                            </table>
                                        @endif
                                    @endif
            
                                    <table class="table table-bordered">
                                        @foreach ($exception_legend as $exception_legend_row)
                                            @if ($loop->first)
                                                <tr class="bg-primary text-white">
                                                    <td colspan="2">
                                                        Exception Legend
                                                    </td>
                                                </tr>
            
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Code
                                                    </td>
                                                    <td>
                                                        Exception
                                                    </td>
                                                </tr>
                                            @endif
                                            
                                            <tr class="">
                                                <td>
                                                    <p color="{{$exception_legend_row['color']}}">
                                                        <b>{{$exception_legend_row['exception_policy_type_id']}}</b>
                                                    </p>
                                                </td>
                                                <td>
                                                    {{$exception_legend_row['name']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                                <td colspan="3">
            
                                    <table class="table table-bordered">
                                        <tr class="bg-primary text-white">
                                            <td colspan="2">
                                                Paid Time
                                            </td>
                                        </tr>
            
                                        <tr class="" nowrap>
                                            <td>
                                                Worked Time
                                            </td>
                                            <td>
                                                {{gettimeunit_helper($pay_period_worked_total_time, '00:00')}}
                                            </td>
                                        </tr>
                                        @if ($pay_period_paid_absence_total_time > 0)
                                            <tr class="" nowrap>
                                                <td>
                                                    Paid Absences
                                                </td>
                                                <td>
                                                    {{gettimeunit_helper($pay_period_paid_absence_total_time, '00:00')}}
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="" style="font-weight: bold;" nowrap>
                                            <td>
                                                Total Time
                                            </td>
                                            <td width="75">
                                                {{gettimeunit_helper(($pay_period_worked_total_time+$pay_period_paid_absence_total_time), '00:00')}}
                                            </td>
                                        </tr>
                                    </table>
            
                                    @if ($pay_period_dock_absence_total_time > 0)
                                        <table class="table table-bordered">
                                            <tr class="bg-primary text-white">
                                                <td colspan="2">
                                                    Docked Time
                                                </td>
                                            </tr>
            
                                            <tr class="" style="font-weight: bold;" nowrap>
                                                <td>
                                                    Docked Absences
                                                </td>
                                                <td>
                                                    {{gettimeunit_helper($pay_period_dock_absence_total_time, '00:00')}}
                                                </td>
                                            </tr>
                                        </table>
                                    @endif
                                </td>
                                <td colspan="3">
                                    <table class="table table-bordered">
                                        <tr class="bg-primary text-white">
                                            <td colspan="2">
                                                Accumulated Time
                                            </td>
                                        </tr>
            
                                        @foreach ($pay_period_total_rows as $pay_period_total_row)
                                            <tr class="">
                                                <td>
                                                    {{$pay_period_total_row['name']}}
                                                </td>
                                                <td>
                                                    {{gettimeunit_helper($pay_period_total_row['total_time'], '00:00')}}
                                                </td>
                                            </tr>
                                        @endforeach
            
                                        <tr class="" style="font-weight: bold;">
                                            <td>
                                                Total Time
                                            </td>
                                            <td>
                                                {{gettimeunit_helper(($pay_period_worked_total_time+$pay_period_paid_absence_total_time), '00:00')}}
                                            </td>
                                        </tr>
            
                                        @if ((empty($pay_period_is_locked) || $pay_period_is_locked != TRUE) AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) ))
                                            <tr>
                                                <td colspan="2" align="center">
                                                    <select name="action_option" id="select_action">
                                                        @foreach ($action_options as $id => $name)
                                                            <option value="{{$id}}">{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="SUBMIT" class="button" name="action:submit" value="Submit" onClick="return confirmAction();">
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
            
                                </td>
                            </tr>
                        </table>
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script	language=JavaScript>

        function editPunch(punchID,punchControlId,userID,date,statusID) {
            try {
                eP=window.open('/attendance/punch/add?id='+encodeURI(punchID)+'&punch_control_id='+encodeURI(punchControlId)+'&user_id='+encodeURI(userID)+'&date_stamp='+encodeURI(date)+'&status_id='+encodeURI(statusID),"Edit_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=800,height=625,resizable=1");
                if (window.focus) {
                    eP.focus()
                }
            } catch (e) {
                //DN
            }
        }
        function hourList(userDateID,userID,date) {
            try {
                hL=window.open('/attendance/punch/userdate_totals?user_date_id='+encodeURI(userDateID)+'&filter_user_id='+encodeURI(userID)+'&filter_date='+encodeURI(date),"Hours","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=1200,height=625,resizable=1");
                if (window.focus) {
                    hL.focus()
                }
            } catch (e) {
                //DN
            }
        }
        function editAbsence(absenceID,userID,date) {
            try {
                eA=window.open('/attendance/punch/edit_user_absence?id='+encodeURI(absenceID)+'&user_id='+encodeURI(userID)+'&date_stamp='+encodeURI(date),"Edit_Absence","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=800,height=625,resizable=1");
                if (window.focus) {
                    eA.focus()
                }
            } catch (e) {
                //DN
            }
        }
        function changeDate(date) {
            document.getElementById("filter_date").value = date;
            document.timesheet.submit();
        }
        
        function resetAction() {
            action_obj = document.getElementById('select_action');
        
            if ( action_obj != null && action_obj.value != 0 ) {
                action_obj[0].selected = true;
            }
        }
        
        function confirmAction() {
            action = document.getElementById('select_action').value;
        
            var recalculateCompany = "Are you sure you want to recalculate the timesheets of every employee?";
            var recalculatePayStub = "Are you sure you want to recalculate the pay stub of this employee?";
        
            if ( action == 'recalculate employee' ) {
                confirm_result = true;
            } else if( action == 'recalculate company' ) {
                confirm_result = confirmSubmit(recalculateCompany);
            } else if( action == 'recalculate pay stub' ) {
                confirm_result = confirmSubmit(recalculatePayStub);
            } else {
                confirm_result = true;
            }
        
            return confirm_result;
        }
    </script>
</x-app-layout>