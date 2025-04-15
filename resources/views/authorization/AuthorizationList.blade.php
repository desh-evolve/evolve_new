<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- ----------------------------------------- --}}

                    

                        <table class="tblList">
            
                            @if ($permission->Check('request','authorize'))
                            
                                {{-- Missed Punch: request_punch --}}
                                @if (is_array($hierarchy_levels['request_punch']))
                                    <tr class="tblHeader">
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
                                    <tr class="tblHeader">
                                        <td colspan="5">
                                            Pending Requests: Punch Adjustment
            
                                            [ {foreach from=$hierarchy_levels.request_punch_adjust key=request_level_display item=request_level name=request_levels}
                                                {if $selected_level_arr.request_punch_adjust == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_punch_adjust]=$request_level_display" merge="FALSE"}">Level {$request_level_display}</a>{if $selected_level_arr.request_punch_adjust == $request_level}</span>{/if}
                                                {if !$smarty.foreach.request_levels.last}
                                                    |
                                                {/if}
                                            {/foreach} ]
            
                                        </td>
                                    </tr>
                                    {foreach from=$requests.request_punch_adjust item=request_punch_adjust name=request_punch_adjust}
                                        {if $smarty.foreach.request_punch_adjust.first}
                                        <tr class="tblHeader">
                                            <td>
                                                {capture assign=label}Employee{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                {capture assign=label}Request Date{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                {capture assign=label}Submitted Date{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                Functions
                                            </td>
                                        </tr>
                                        {/if}
                                        {cycle assign=row_class values="tblDataWhite,tblDataGrey"}
                                        <tr class="{$row_class}">
                                            <td>
                                                {$request_punch_adjust.user_full_name}
                                            </td>
                                            <td>
                                                {getdate type="DATE" epoch=$request_punch_adjust.date_stamp}
                                            </td>
                                            <td>
                                                {getdate type="DATE" epoch=$request_punch_adjust.created_date}
                                            </td>
                                            <td>
                                                {assign var="request_punch_adjust_id" value=$request_punch_adjust.id}
                                                {assign var="selected_level" value=$selected_levels.request_punch_adjust}
                                                <a href="javascript:viewRequest({$request_punch_adjust_id},{$selected_level|default:0})">View</a>
                                            </td>
                                        </tr>
                                    {foreachelse}
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    {/foreach}
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                @endif

                                {{-- Missed Punch: request_absence --}}
                                {if is_array($hierarchy_levels.request_absence)}
                                    <tr class="tblHeader">
                                        <td colspan="5">
                                            Pending Requests: Absence
                                            [ {foreach from=$hierarchy_levels.request_absence key=request_level_display item=request_level name=request_levels}
                                                {if $selected_level_arr.request_absence == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_absence]=$request_level_display" merge="FALSE"}">Level {$request_level_display}</a>{if $selected_level_arr.request_absence == $request_level}</span>{/if}
                                                {if !$smarty.foreach.request_levels.last}
                                                    |
                                                {/if}
                                            {/foreach} ]
                                        </td>
                                    </tr>
                                    {foreach from=$requests.request_absence item=request_absence name=request_absence}
                                        {if $smarty.foreach.request_absence.first}
                                        <tr class="tblHeader">
                                            <td>
                                                {capture assign=label}Employee{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                {capture assign=label}Request Date{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                {capture assign=label}Submitted Date{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                Functions
                                            </td>
                                        </tr>
                                        {/if}
                                        {cycle assign=row_class values="tblDataWhite,tblDataGrey"}
                                        <tr class="{$row_class}">
                                            <td>
                                                {$request_absence.user_full_name}
                                            </td>
                                            <td>
                                                {getdate type="DATE" epoch=$request_absence.date_stamp}
                                            </td>
                                            <td>
                                                {getdate type="DATE" epoch=$request_absence.created_date}
                                            </td>
                                            <td>
                                                {assign var="request_absence_id" value=$request_absence.id}
                                                {assign var="selected_level" value=$selected_levels.request_absence}
                                                <a href="javascript:viewRequest({$request_absence_id},{$selected_level|default:0})">View</a>
                                            </td>
                                        </tr>
                                    {foreachelse}
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    {/foreach}
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                {/if}

                                {{-- Missed Punch: request_schedule --}}
                                {if is_array($hierarchy_levels.request_schedule)}
                                    <tr class="tblHeader">
                                        <td colspan="5">
                                            Pending Requests: Schedule Adjustment
                                            [ {foreach from=$hierarchy_levels.request_schedule key=request_level_display item=request_level name=request_levels}
                                                {if $selected_level_arr.request_schedule == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_schedule]=$request_level_display" merge="FALSE"}">Level {$request_level_display}</a>{if $selected_level_arr.request_schedule == $request_level}</span>{/if}
                                                {if !$smarty.foreach.request_levels.last}
                                                    |
                                                {/if}
                                            {/foreach} ]
                                        </td>
                                    </tr>
            
                                    {foreach from=$requests.request_schedule item=request_schedule name=request_schedule}
                                        {if $smarty.foreach.request_schedule.first}
                                        <tr class="tblHeader">
                                            <td>
                                                {capture assign=label}Employee{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                {capture assign=label}Request Date{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                {capture assign=label}Submitted Date{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                Functions
                                            </td>
                                        </tr>
                                        {/if}
                                        {cycle assign=row_class values="tblDataWhite,tblDataGrey"}
                                        <tr class="{$row_class}">
                                            <td>
                                                {$request_schedule.user_full_name}
                                            </td>
                                            <td>
                                                {getdate type="DATE" epoch=$request_schedule.date_stamp}
                                            </td>
                                            <td>
                                                {getdate type="DATE" epoch=$request_schedule.created_date}
                                            </td>
                                            <td>
                                                {assign var="request_schedule_id" value=$request_schedule.id}
                                                {assign var="selected_level" value=$selected_levels.request_schedule}
                                                <a href="javascript:viewRequest({$request_schedule_id},{$selected_level|default:0})">View</a>
                                            </td>
                                        </tr>
                                    {foreachelse}
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    {/foreach}
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                {/if}

                                {{-- Missed Punch: request_other --}}

                                {if is_array($hierarchy_levels.request_other)}
                                    <tr class="tblHeader">
                                        <td colspan="5">
                                            Pending Requests: Other
                                            [ {foreach from=$hierarchy_levels.request_other key=request_level_display item=request_level name=request_levels}
                                                {if $selected_level_arr.request_other == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_other]=$request_level_display" merge="FALSE"}">Level {$request_level_display}</a>{if $selected_level_arr.request_other == $request_level}</span>{/if}
                                                {if !$smarty.foreach.request_levels.last}
                                                    |
                                                {/if}
                                            {/foreach} ]
                                        </td>
                                    </tr>
            
                                    {foreach from=$requests.request_other item=request_other name=request_other}
                                        {if $smarty.foreach.request_other.first}
                                        <tr class="tblHeader">
                                            <td>
                                                {capture assign=label}Employee{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                {capture assign=label}Request Date{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                {capture assign=label}Submitted Date{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                Functions
                                            </td>
                                        </tr>
                                        {/if}
                                        {cycle assign=row_class values="tblDataWhite,tblDataGrey"}
                                        <tr class="{$row_class}">
                                            <td>
                                                {$request_other.user_full_name}
                                            </td>
                                            <td>
                                                {getdate type="DATE" epoch=$request_other.date_stamp}
                                            </td>
                                            <td>
                                                {getdate type="DATE" epoch=$request_other.created_date}
                                            </td>
                                            <td>
                                                {assign var="request_other_id" value=$request_other.id}
                                                {assign var="selected_level" value=$selected_levels.request_other}
                                                <a href="javascript:viewRequest({$request_other_id},{$selected_level|default:0})">View</a>
                                            </td>
                                        </tr>
                                    {foreachelse}
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 Requests found.
                                            </td>
                                        </tr>
                                    {/foreach}
            
                                    <tr>
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    </tr>
                                {/if}

                            @endif

                            @if ($permission->Check('punch','authorize'))
                                {if is_array($hierarchy_levels.timesheet)}
                                    <tr class="tblHeader">
                                        <td colspan="5">
                                            Pending TimeSheets
                                            [ {foreach from=$hierarchy_levels.timesheet key=timesheet_level_display item=timesheet_level name=timesheet_levels}
                                                {if $selected_level_arr.timesheet == $timesheet_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[timesheet]=$timesheet_level_display" merge="FALSE"}">Level {$timesheet_level_display}</a>{if $selected_level_arr.timesheet == $timesheet_level}</span>{/if}
                                                {if !$smarty.foreach.timesheet_levels.last}
                                                    |
                                                {/if}
                                            {/foreach} ]
                                        </td>
                                    </tr>
                                    {foreach from=$timesheets item=timesheet name=timesheets}
                                        {if $smarty.foreach.timesheets.first}
                                        <tr class="tblHeader">
                                            <td>
                                                {capture assign=label}Employee{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td colspan="2">
                                                {capture assign=label}Pay Period{/capture}
                                                {include file="column_sort.tpl" label=$label sort_column="start_date" current_column="$sort_column" current_order="$sort_order"}
                                            </td>
                                            <td>
                                                Functions
                                            </td>
                                        </tr>
                                        {/if}
                                        {cycle assign=row_class values="tblDataWhite,tblDataGrey"}
                                        <tr class="{$row_class}">
                                            <td>
                                                {$timesheet.user_full_name}
                                            </td>
                                            <td colspan="2">
                                                {getdate type="DATE" epoch=$timesheet.pay_period_start_date} - {getdate type="DATE" epoch=$timesheet.pay_period_end_date}
                                            </td>
                                            <td>
                                                {assign var="timesheet_id" value=$timesheet.id}
                                                {assign var="selected_level" value=$selected_levels.timesheet}
                                                <a href="javascript:viewTimeSheetVerification({$timesheet_id},{$selected_level|default:0})">View</a>
                                            </td>
                                        </tr>
                                    {foreachelse}
                                        <tr class="tblDataWhite">
                                            <td colspan="5">
                                                0 TimeSheets found.
                                            </td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            @endif
            
                            @if (!is_array($hierarchy_levels.request_punch)
                                    AND !is_array($hierarchy_levels.request_punch_adjust)
                                    AND !is_array($hierarchy_levels.request_absence)
                                    AND !is_array($hierarchy_levels.request_schedule)
                                    AND !is_array($hierarchy_levels.request_other)
                                    AND !is_array($hierarchy_levels.timesheet))
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