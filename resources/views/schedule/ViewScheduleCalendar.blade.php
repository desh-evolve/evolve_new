<x-app-modal-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>

    <script	language=JavaScript>
    function editSchedule(scheduleID,userID,date) {
        try {
            eS=window.open('schedule/edit_schedule?id='+ encodeURI(scheduleID) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date),"Edit_Schedule","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
        } catch (e) {
            //DN
        }
    }
    </script>
            <table class="table table-bordered">
                    <tr>
                        <td class="tblPagingLeft" colspan="7" align="right">
                            <br>
                        </td>
                    </tr>
    
                    @foreach ($calendar_array as $calendar)
                        @if ($loop->first)
                            <tr class="bg-primary text-white">
                                @if( $calendar['start_day_of_week'] == 0)
                                    <td width="14%">
                                        Sunday
                                    </td>
                                @endif
                                <td width="14%">
                                    Monday
                                </td>
                                <td width="14%">
                                    Tuesday
                                </td>
                                <td width="14%">
                                    Wednesday
                                </td>
                                <td width="14%">
                                    Thursday
                                </td>
                                <td width="14%">
                                    Friday
                                </td>
                                <td width="14%">
                                    Saturday
                                </td>
                                @if( $calendar['start_day_of_week'] == 1)
                                    <td width="14%">
                                        Sunday
                                    </td>
                                @endif
                            </tr>
                        @endif
    
    
                        @if ($calendar['isNewWeek'] == TRUE) 
                            @if ($loop->first)
                                </td>
                            @endif
                            <tr>
                        @endif
    
                            <td 
                                @if ($permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own')) 
                                    onClick="editSchedule('',{{$filter_user_id}},{{$calendar['epoch']}})"
                                @endif
                                    class="
                                        @if ($calendar['day_of_month'] == NULL)
                                            bg-primary text-white
                                        @endif
                                    " valign="top">
                                @if( $calendar['day_of_month'] != NULL)
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="left" width="50%" >
                                            @if( $calendar['isNewMonth'] == TRUE) 
                                                <b>{{$calendar['month_name']}}</b>
                                            @endif
                                            <br>
                                        </td>
                                        <td align="right" width="50%">
                                            <b>{{$calendar['day_of_month']}}</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" nowrap>
                                            @if( isset($holidays[$calendar['epoch']]))
                                                <div align="center">
                                                    <b>{{$holidays[$calendar['epoch']]}}</b>
                                                </div>
                                            @endif
    
                                            @foreach ($schedule_shifts[$calendar['epoch']] as $shifts)
                                                @if ($shifts['start_time'])
                                                    @if (isset($shifts['id']) AND ( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') )) 
                                                        <a href="javascript:editSchedule({{$shifts['id']}},{{$filter_user_id}},{{$calendar['epoch']}})">
                                                    @endif
                                                    @if( $shifts['status_id'] == 20)<span color="red">@endif
                                                    {{getdate_helper('time', $shifts['start_time'])}}-{{getdate_helper('time', $shifts['end_time'])}}<br>
                                                    @if( $shifts['status_id'] == 20)</span>@endif
                                                    @if (isset($shifts['id']) AND ( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') )) 
                                                        </a>
                                                    @endif
                                                @else
                                                    <br>
                                                @endif
    
                                            @endforeach
                                            <br>
                                        </td>
                                    </tr>
                                </table>
                                @else
                                    <br>
                                @endif
                            </td>
                        @if ($loop->last)
                            </td>
                        @endif
                    @endforeach
    
                    <tr class="bg-primary text-white">
                        <td colspan="7">
                            Schedule is subject to change without notice.
                        </td>
                    </tr>
                    <tr>
                      <td class="tblPagingLeft" colspan="7" align="right">
                          <br>
                      </td>
                    </tr>
                </table>
</x-app-modal-layout>