<x-app-modal-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>

<table style="width:100%; background:#7a9bbd" id="schedule_table">
<form method="get" name="schedule" action="/schedule/view_schedule_day">
		<tr class="bg-primary text-white" height="1">
		@foreach($calendar_array as $calendar)
			<td 
                @if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')) id="cursor-hand" onClick="schedule.editSchedule('','',{{$calendar['epoch']}})" @endif >
				{{$calendar['day_of_week']}}, {{$calendar['month_short_name']}} {{$calendar['day_of_month']}}
				@if( isset($holidays[$calendar['epoch']]))
					<br>
					({{$holidays[$calendar['epoch']]}})
				@endif
			</td>
		@endforeach
		</tr>

		<tr class="tblDataWhiteNH">
        @foreach ($calendar_array as $calendar)
			<td valign="top">
				<table width="100%">
                @foreach ($schedule_shifts[$calendar['date_stamp']] as $branch => $branches)
					@if( $branch != '--')
					<tr class="bg-primary text-white">
						<td>
							{{$branch}}
						</td>
					</tr>
					@endif
                    @foreach ($branches as $department => $departments)
						@if( $department != '--')
						<tr class="bg-primary text-white">
							<td>
								{{$department}}
							</td>
						</tr>
						@endif
                        @foreach ($departments as $shifts)
                        
							<tr class="">
								<td class="cellHL" 
                                    @if($permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts['is_owner'] === TRUE )) 
                                    id="cursor-hand" 
                                    onClick="schedule.editSchedule('{{$shifts['id']}}',{{$shifts['user_id']}},{{$calendar['epoch']}},{{$shifts['status_id']}},{{$shifts['start_time']}},{{$shifts['end_time']}},'{{$shifts['schedule_policy_id']}}','{{$shifts['absence_policy_id']}}')"@endif nowrap>
									@if( $shifts['start_time'])
										<b>{{$shifts['user_full_name']}}</b><br>
										@if( isset($shifts['id']) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts['is_owner'] === TRUE ) ))
											<a href="javascript:schedule.editSchedule({{$shifts['id']}},{{$shifts['user_id']}},{{$calendar['epoch']}})">
										@endif
										@if( $shifts['status_id'] == 20)<span color="red">{{$shifts['absence_policy'] ?? "N/A"}}
                                            </span>
                                        @else
                                            {{getdate_helper('time', $shifts['start_time'])}}-{{getdate_helper('time', $shifts['end_time'])}}
                                        @endif
										@if( isset($shifts['id']) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts['is_owner'] === TRUE ) ))
                                            </a>
                                        @endif
										@if( !isset($shifts['id']))[R]@endif
									@else
										<br>
									@endif
								</td>
							</tr>
						@endforeach
					@endforeach
				@endforeach
				</table>
			</td>
		@endforeach
		</tr>
		<tr class="bg-primary text-white" height="1">
        @foreach ($calendar_array as $calendar)
			<td @if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')) onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"@endif  nowrap>
				{{$schedule_shift_totals[$calendar['date_stamp']]['total_users'] ?? 0}} Employees -
				{{gettimeunit_helper($schedule_shift_totals[$calendar['date_stamp']]['total_time'], 0)}}
			</td>
		@endforeach
		</tr>
	</table>
</form>
</body>
</html>

</x-app-modal-layout>