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

                    <form method="get" action="/authorization/authorization_list">
                        <table class="table table-bordered">
            
                            @if ($permission->Check('request','authorize'))
                                {{-- Missed Punch: request_punch --}}
                                @if (is_array($hierarchy_levels['request_punch']))
                                    <tr class="bg-primary text-white">
                                        <td colspan="5">
                                            Pending Requests: Missed Punch
                                            [ 
                                                @foreach ($hierarchy_levels['request_punch'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_punch'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="/authorization/authorization_list?selected_levels[request_punch]={{$request_level_display}}">Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_punch'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                             ]
                                        </td>
                                    </tr>
                                    @if (!empty($requests['request_punch']))
                                        @foreach ($requests['request_punch'] as $request_punch)
                                            @if ($loop->first)
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Employee
                                                    </td>
                                                    <td>
                                                        Request Date
                                                    </td>
                                                    <td>
                                                        Submitted Date
                                                    </td>
                                                    <td>
                                                        Functions
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr class="">
                                                <td>
                                                    {{$request_punch['user_full_name']}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_punch['date_stamp'])}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_punch['created_date'])}}
                                                </td>
                                                <td>
                                                    <a href="javascript:viewRequest({{$request_punch['id']}},{{$selected_levels['request_punch'] ?? 0}})">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    @endif
                                    
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif
            
            
                                {{-- Missed Punch: request_punch_adjust --}}
                                @if (!empty($hierarchy_levels['request_punch_adjust']) && is_array($hierarchy_levels['request_punch_adjust']))
                                    <tr class="bg-primary text-white">
                                        <td colspan="5">
                                            Pending Requests: Punch Adjustment
            
                                            [ 
                                                @foreach ($hierarchy_levels['request_punch_adjust'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_punch_adjust'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="/authorization/authorization_list?selected_levels[request_punch_adjust]={{$request_level_display}}" >Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_punch_adjust'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                            ]
            
                                        </td>
                                    </tr>
                                    @if (!empty($requests['request_punch_adjust']))
                                        @foreach ($requests['request_punch_adjust'] as $request_punch_adjust)
                                            @if ($loop->first)
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Employee
                                                    </td>
                                                    <td>
                                                        Request Date
                                                    </td>
                                                    <td>
                                                        Submitted Date
                                                    </td>
                                                    <td>
                                                        Functions
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr class="">
                                                <td>
                                                    {{$request_punch_adjust['user_full_name']}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_punch_adjust['date_stamp'])}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_punch_adjust['created_date'])}}
                                                </td>
                                                <td>
                                                    <a href="javascript:viewRequest({{$request_punch_adjust['id']}},{{$selected_levels['request_punch_adjust'] ?? 0}})">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    @endif
                                    
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif
            
                                {{-- Missed Punch: request_absence --}}
                                @if (is_array($hierarchy_levels['request_absence']))
                                    <tr class="bg-primary text-white">
                                        <td colspan="5">
                                            Pending Requests: Absence
                                            [ 
                                                @foreach ($hierarchy_levels['request_absence'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_absence'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="/authorization/authorization_list?selected_levels[request_absence]={{$request_level_display}}">Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_absence'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                            ]
                                        </td>
                                    </tr>
                                    @if (!empty($requests['request_absence']))
                                        @foreach ($requests['request_absence'] as $request_absence)
                                            @if ($loop->first)
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Employee
                                                    </td>
                                                    <td>
                                                        Request Date
                                                    </td>
                                                    <td>
                                                        Submitted Date
                                                    </td>
                                                    <td>
                                                        Functions
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr class="">
                                                <td>
                                                    {{$request_absence['user_full_name']}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_absence['date_stamp'])}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_absence['created_date'])}}
                                                </td>
                                                <td>
                                                    <a href="javascript:viewRequest({{$request_absence['id']}},{{$selected_levels['request_absence'] ?? 0}})">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    @endif
                                    
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif
            
                                {{-- Missed Punch: request_schedule --}}
                                @if (is_array($hierarchy_levels['request_schedule']))
                                    <tr class="bg-primary text-white">
                                        <td colspan="5">
                                            Pending Requests: Schedule Adjustment
                                            [ 
                                                @foreach ($hierarchy_levels['request_schedule'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_schedule'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="/authorization/authorization_list?selected_levels[request_schedule]={{$request_level_display}}">Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_schedule'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                            ]
                                        </td>
                                    </tr>
                                    @if (!empty($requests['request_schedule']))
                                        @foreach ($requests['request_schedule'] as $request_schedule)
                                            @if ($loop->first)
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Employee
                                                    </td>
                                                    <td>
                                                        Request Date
                                                    </td>
                                                    <td>
                                                        Submitted Date
                                                    </td>
                                                    <td>
                                                        Functions
                                                    </td>
                                                </tr>
                                            @endif
                                            
                                            <tr class="">
                                                <td>
                                                    {{$request_schedule['user_full_name']}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_schedule['date_stamp'])}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_schedule['created_date'])}}
                                                </td>
                                                <td>
                                                    <a href="javascript:viewRequest({{$request_schedule['id']}},{{$selected_levels['request_schedule'] ?? 0}})">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    @endif
                                    
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif
            
                                {{-- Missed Punch: request_other --}}
                                @if (is_array($hierarchy_levels['request_other']))
                                    <tr class="bg-primary text-white">
                                        <td colspan="5">
                                            Pending Requests: Other
                                            [ 
                                                @foreach ($hierarchy_levels['request_other'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_other'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="/authorization/authorization_list?selected_levels[request_other]={{$request_level_display}}">Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_other'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                            ]
                                        </td>
                                    </tr>
            
                                    @if (!empty($requests['request_other']))
                                        @foreach ($requests['request_other'] as $request_other)
                                            @if ($loop->first)
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Employee
                                                    </td>
                                                    <td>
                                                        Request Date
                                                    </td>
                                                    <td>
                                                        Submitted Date
                                                    </td>
                                                    <td>
                                                        Functions
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr class="">
                                                <td>
                                                    {{$request_other['user_full_name']}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_other['date_stamp'])}}
                                                </td>
                                                <td>
                                                    {{getdate_helper('date', $request_other['created_date'])}}
                                                </td>
                                                <td>
                                                    <a href="javascript:viewRequest({{$request_other['id']}},{{$selected_levels['request_other'] ?? 0}})">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    @endif
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif
            
                            @endif
            
                            @if ($permission->Check('punch','authorize'))
                                @if (is_array($hierarchy_levels['timesheet']))
                                    <tr class="bg-primary text-white">
                                        <td colspan="5">
                                            Pending TimeSheets
                                            [   
                                                @foreach ($hierarchy_levels['timesheet'] as $timesheet_level_display => $timesheet_level)
                                                    @if ($selected_level_arr['timesheet'] == $timesheet_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="/authorization/authorization_list?selected_levels[timesheet]={{$timesheet_level_display}}">Level {{$timesheet_level_display}}</a>
                                                    @if ($selected_level_arr['timesheet'] == $timesheet_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                            ]
                                        </td>
                                    </tr>
                                    @if (!empty($timesheets))
                                        @foreach ($timesheets as $timesheet)
                                            @if ($loop->first)
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Employee
                                                    </td>
                                                    <td colspan="2">
                                                        Pay Period
                                                    </td>
                                                    <td>
                                                        Functions
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr class="">
                                                <td>
                                                    {{$timesheet['user_full_name']}}
                                                </td>
                                                <td colspan="2">
                                                    {{getdate_helper('date', $timesheet['pay_period_start_date'])}} - {{getdate_helper('date', $timesheet['pay_period_end_date'])}}
                                                </td>
                                                <td>
                                                    <a href="javascript:viewTimeSheetVerification({{$timesheet['id']}},{{$selected_levels['timesheet'] ?? 0}})">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 TimeSheets found.
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            @endif
            
                            @if (!is_array($hierarchy_levels['request_punch'])
                                    AND !is_array($hierarchy_levels['request_punch_adjust'])
                                    AND !is_array($hierarchy_levels['request_absence'])
                                    AND !is_array($hierarchy_levels['request_schedule'])
                                    AND !is_array($hierarchy_levels['request_other'])
                                    AND !is_array($hierarchy_levels['timesheet']))
                                <tr class="tblDataWhite">
                                    <td colspan="5">
                                        No hierarchies are defined, therefore there are no authorizations pending.
                                    </td>
                                </tr>
                            @endif
            
                        </table>
                    </form>
                            
                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>


    <script	language=JavaScript>

        function viewRequest(requestID,level) {
            try {
                window.open('/attendance/request/view?id='+ encodeURI(requestID) +'&selected_level='+ encodeURI(level),"Request_"+ requestID,"toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
            } catch (e) {
                //DN
            }
        }
        function viewTimeSheetVerification(timesheet_verify_id,level) {
            try {
                // check here - url is incorrect
                //window.open('{/literal}{$BASE_URL}{literal}timesheet/ViewTimeSheetVerification.php?id='+ encodeURI(timesheet_verify_id) +'&selected_level='+ encodeURI(level),"TimeSheet_"+ timesheet_verify_ID,"toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
                window.open('{/literal}{$BASE_URL}{literal}timesheet/ViewTimeSheetVerification.php?id='+ encodeURI(timesheet_verify_id) +'&selected_level='+ encodeURI(level),"TimeSheet_"+ timesheet_verify_id,"toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
            } catch (e) {
                //DN
            }
        }
        
        </script>
</x-app-layout>