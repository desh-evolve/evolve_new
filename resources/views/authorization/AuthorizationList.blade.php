<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                    
                    {{-- ----------------------------------------- --}}

                    

                        <table class="tblList">
            
                            @if ($permission->Check('request','authorize'))
                            
                                {{-- Missed Punch: request_punch --}}
                                @if (is_array($hierarchy_levels['request_punch']))
                                    <tr>
                                        <th colspan="5">
                                            Pending Requests: Missed Punch
                                            [ 
                                                @foreach ($hierarchy_levels['request_punch'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_punch'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="{{ url('AuthorizationList.php') . '?selected_levels[request_punch]=' . urlencode($request_level_display) }}">Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_punch'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                             ]
                                        </th>
                                    </tr>

                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Request Date</th>
                                        <th>Submitted Date</th>
                                        <th>Functions</th>
                                    </tr>

                                    @foreach ($requests['request_punch'] as $index => $request_punch)
                                        <tr>
                                            <td>
                                                {{$request_punch['user_full_name']}}
                                            </td>
                                            <td>
                                                {{$request_punch['date_stamp']}}
                                            </td>
                                            <td>
                                                {{$request_punch['created_date']}}
                                            </td>
                                            <td>
                                                <a href="javascript:viewRequest({{$request_punch.id}},{{$selected_levels.request_punch ?? 0}})">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif

                                {{-- Missed Punch: request_punch_adjust --}}
                                @if (is_array($hierarchy_levels['request_punch_adjust']))
                                    <tr>
                                        <th colspan="5">
                                            Pending Requests: Punch Adjustment
                                            [
                                                @foreach ($hierarchy_levels['request_punch_adjust'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_punch_adjust'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="#">Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_punch_adjust'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                            ]
                                        </th>
                                    </tr>

                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Request Date</th>
                                        <th>Submitted Date</th>
                                        <th>Functions</th>
                                    </tr>

                                    @foreach ($requests['request_punch_adjust'] as $request_punch_adjust)
                                        <tr>
                                            <td>
                                                {{$request_punch_adjust['user_full_name']}}
                                            </td>
                                            <td>
                                                {{$request_punch_adjust['date_stamp']}}
                                            </td>
                                            <td>
                                                {{$request_punch_adjust['created_date']}}
                                            </td>
                                            <td>
                                                <a href="javascript:viewRequest({{$request_punch_adjust['id']}},{{$selected_levels['request_punch_adjust'] ?? 0}})">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif

                                {{-- Missed Punch: request_absence --}}
                                @if (is_array($hierarchy_levels['request_absence']))
                                    <tr>
                                        <th colspan="5">
                                            Pending Requests: Absence
                                            [ 
                                                @foreach ($hierarchy_levels['request_absence']  as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_absence'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="#">Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_absence'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                            ]
                                        </th>
                                    </tr>

                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Request Date</th>
                                        <th>Submitted Date</th>
                                        <th>Functions</th>
                                    </tr>
                                    
                                    @foreach ($requests['request_absence'] as $request_absence)
                                        <tr>
                                            <td>
                                                {{$request_absence['user_full_name']}}
                                            </td>
                                            <td>
                                                {{$request_absence['date_stamp']}}
                                            </td>
                                            <td>
                                                {{$request_absence['created_date']}}
                                            </td>
                                            <td>
                                                <a href="javascript:viewRequest({{$$request_absence['id']}},{{$selected_levels['request_absence'] ?? 0}})">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif
                                

                                {{-- Missed Punch: request_schedule --}}
                                @if (is_array($hierarchy_levels['request_schedule']))
                                    <tr>
                                        <th colspan="5">
                                            Pending Requests: Schedule Adjustment
                                            [ 
                                                @foreach ($hierarchy_levels['request_schedule'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_schedule'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="#">Level {{$request_level_display}}</a>
                                                    @if ($selected_level_arr['request_schedule'] == $request_level)
                                                        </span>
                                                    @endif
                                                    @if (!$loop->last)
                                                        |
                                                    @endif
                                                @endforeach
                                            ]
                                        </th>
                                    </tr>

                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Request Date</th>
                                        <th>Submitted Date</th>
                                        <th>Functions</th>
                                    </tr>

                                    @foreach ($requests['request_schedule'] as $request_schedule)
                                        <tr>
                                            <td>
                                                {{$request_schedule['user_full_name']}}
                                            </td>
                                            <td>
                                                {{$request_schedule['date_stamp']}}
                                            </td>
                                            <td>
                                                {{$request_schedule['created_date']}}
                                            </td>
                                            <td>
                                                <a href="javascript:viewRequest({{$request_schedule['id']}},{{$selected_levels['request_schedule'] ?? 0}})">View</a>
                                            </td>
                                        </tr>
                                    @endforeach    

                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif
            

                                {{-- Missed Punch: request_other --}}
                                @if (is_array($hierarchy_levels['request_other']))
                                    <tr class="tblHeader">
                                        <td colspan="5">
                                            Pending Requests: Other
                                            [ 
                                                @foreach ($hierarchy_levels['request_other'] as $request_level_display => $request_level)
                                                    @if ($selected_level_arr['request_other'] == $request_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="#">Level {{$request_level_display}}</a>
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

                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Request Date</th>
                                        <th>Submitted Date</th>
                                        <th>Functions</th>
                                    </tr>

                                    @foreach ($requests['request_other'] as $request_other)
                                        <tr>
                                            <td>
                                                {{$request_other['user_full_name']}}
                                            </td>
                                            <td>
                                                {{$request_other['date_stamp']}}
                                            </td>
                                            <td>
                                                {{$request_other['created_date']}}
                                            </td>
                                            <td>
                                                <a href="javascript:viewRequest({{$request_other['id']}},{{$selected_levels['request_other'] ?? 0}})">View</a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif

                            @endif

                            @if ($permission->Check('punch','authorize'))
                                @if (is_array($hierarchy_levels['timesheet']))
                                    <tr class="tblHeader">
                                        <td colspan="5">
                                            Pending TimeSheets
                                            [ 
                                                @foreach ($hierarchy_levels['timesheet'] as $timesheet_level_display => $timesheet_level)
                                                    @if ($selected_level_arr['timesheet'] == $timesheet_level)
                                                        <span style="background-color:#33CCFF">
                                                    @endif
                                                    <a href="#">Level {{$timesheet_level_display}}</a>
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

                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th colspan="2">Pay Period</th>
                                        <th>Functions</th>
                                    </tr>

                                    @foreach ($timesheets as $timesheet)
                                        <tr>
                                            <td>
                                                {{$timesheet['user_full_name']}}
                                            </td>
                                            <td colspan="2">
                                                {{$timesheet['pay_period_start_date']}} - {{$timesheet['pay_period_end_date']}}
                                            </td>
                                            <td>
                                                <a href="javascript:viewTimeSheetVerification({{$timesheet['id']}},{{$selected_levels['timesheet'] ?? 0}})">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
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
                    
                    {{-- ----------------------------------------- --}}
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewRequest(requestID, level) {
            try {
                let baseUrl = "{{ url('request/view_request') }}";
                let url = baseUrl + '?id=' + encodeURIComponent(requestID) + '&selected_level=' + encodeURIComponent(level);
                window.open(url, "Request_" + requestID, "toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
            } catch (e) {
                //DN
            }
        }
    
        function viewTimeSheetVerification(timesheet_verify_id, level) {
            try {
                let baseUrl = "{{ url('timesheet/view_timesheet_verification') }}";
                let url = baseUrl + '?id=' + encodeURIComponent(timesheet_verify_id) + '&selected_level=' + encodeURIComponent(level);
                window.open(url, "TimeSheet_" + timesheet_verify_id, "toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
            } catch (e) {
                //DN
            }
        }
    
        // Example usage with jQuery trigger
        $(document).on('click', '.view-request-btn', function () {
            let requestID = $(this).data('id');
            let level = $(this).data('level');
            viewRequest(requestID, level);
        });
    
        $(document).on('click', '.view-timesheet-btn', function () {
            let timesheetID = $(this).data('id');
            let level = $(this).data('level');
            viewTimeSheetVerification(timesheetID, level);
        });
    </script>
    
</x-app-layout>