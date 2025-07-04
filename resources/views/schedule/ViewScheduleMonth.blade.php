<x-app-modal-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
		.main-content {
            margin: 0;
        }
		.page-content {
			padding-top: 25px !important;
		}
    </style>


<script	language=JavaScript>

function showScheduleDay(epoch) {
	document.getElementById('filter_start_date').value=epoch;
	document.getElementById('day_schedule').style.width='100%';
	document.schedule.submit();
}

</script>
<table class="table table-bordered" id="schedule_table" height="100%">
	<form method="get" target="day_schedule" name="schedule" action="/schedule/view_schedule_month">
        @foreach ($calendar_array as $calendar)
			@if($loop->first)
				<tr class="bg-primary text-white">
					@foreach($calendar_column_headers as $column_header)
						<td width="10%">
							{{$column_header}}
						</td>
					@endforeach
					@if( $total_users > 1)
						<td width="30%" rowspan="100" valign="middle" height="100%">
							<iframe style="width:100%; height:100%; border: 5px" id="day_schedule" name="day_schedule" src="/blank.html"></iframe>
						</td>
					@endif
				</tr>
			@endif

			@if( $calendar['isNewWeek'] == TRUE)
				</tr>
				<tr>
			@endif
				@if( $total_users <= 1)
					<td 
                        @if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child'))
                            id="cursor-hand" onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"
                        @endif
							class="
                                @if ($calendar['day_of_month'] == NULL)
                                    bg-primary text-white
								@endif" valign="top">
						@if( $calendar['day_of_month'] != NULL)
						<table border="0" cellpadding="0" cellspacing="2" width="100%">
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
							@if( isset($holidays[$calendar['epoch']]))
							<tr>
								<td colspan="2" align="center">
									<b>{{$holidays[$calendar['epoch']]}}</b>
								</td>
							</tr>
							@endif

							@if (!empty($schedule_shifts[$calendar['date_stamp']]))
								@foreach ($schedule_shifts[$calendar['date_stamp']] as $shifts)
									@if( $shifts['start_time'])
										@if( $shifts['branch_id'] != 0)
										<tr>
											<td colspan="2" class="bg-primary text-white">
												{{$shifts['branch']}}
											</td>
										</tr>
										@endif
										@if( $shifts['department_id'] != 0)
										<tr>
											<td colspan="2" class="bg-primary text-white">
												{{$shifts['department']}}
											</td>
										</tr>
										@endif
										<tr>
											<td colspan="2" align="center" nowrap>
												@if( isset($shifts['id']) AND ( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')) )
													<a href="javascript:schedule.editSchedule({{$shifts['id']}},{{$shifts['user_id']}},{{$calendar['epoch']}})">
												@endif
												@if( $shifts['status_id'] == 20)
													<span color="red">{{$shifts['absence_policy'] ?? "N/A"}}</span>
												@else
													{{getdate_helper('time', $shifts['start_time'])}}-{{getdate_helper('time', $shifts['end_time'])}}
												@endif
												@if( isset($shifts['id']) AND ( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child') ) )</a>
												@else [R]@endif
											</td>
										<tr>
									@endif
								@endforeach
							@endif
						</table>
						@else
							<br>
						@endif
					</td>
				@else
					<td id="cursor-hand" onClick="showScheduleDay({{$calendar['epoch']}})"
							class="
                                @if( $calendar['day_of_month'] == NULL)}
                                cellHL
                                @else
                                    @if( $calendar['epoch'] == $current_epoch) 
                                    bg-warning text-white
                                    @else
                                    @endif
                                @endif"
							valign="top">
						@if ($calendar['day_of_month'] != NULL)
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
									<td colspan="2" align="right"> 
                                        {{-- {* Allow wraping for long holiday names *} --}}
										@if( isset($holidays[$calendar['epoch']]))
											<div align="center">
												<b>{{$holidays[$calendar['epoch']]}}</b>
											</div>
										@endif

										<span style="white-space:nowrap;">
										Absent: {{$schedule_shift_totals[$calendar['date_stamp']]['total_absent_users'] ?? 0}}<br>
										Scheduled: {{$schedule_shift_totals[$calendar['date_stamp']]['total_users'] ?? 0}}<br>
										Total: {{gettimeunit_helper($schedule_shift_totals[$calendar['date_stamp']]['total_time'], 0)}}
										</span>
									</td>
								</tr>
							</table>
						@else
							<br>
						@endif
					</td>
				@endif
		@endforeach
	</table>
<input type="hidden" id="filter_start_date" name="filter_data[start_date]" value="{{$filter_data['start_date']}}">
<input type="hidden" name="serialize_filter_data" value="{{$serialize_filter_data}}">

</form>
<script language="JavaScript">changeHeight(document.getElementById('body'), parent.document.getElementById('schedule_layer'), 'screen_size' ); 
    @if( $do != '')
    parent.location.hash = 'schedule';
    @endif
</script>
</body>
</html>

</x-app-modal-layout>