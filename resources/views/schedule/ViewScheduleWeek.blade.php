<x-app-modal-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
		.main-content {
            margin: 0 !important;
        }
		.page-content {
			padding-top: 25px !important;
		}
    </style>


<table class="table table-bordered" id="schedule_table">
<form method="get" name="schedule" action="/schedule/view_schedule_week">
		<tr class="bg-primary text-white" height="1">
        @foreach ($calendar_array as $calendar)
			<td 
                @if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child'))
                id="cursor-hand" onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"@endif>
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
					@if (!empty($schedule_shifts[$calendar['date_stamp']]))
						@foreach ($schedule_shifts[$calendar['date_stamp']] as $branch => $branches)
							@if( $branch != '--' AND $branch != '')
							<tr class="bg-primary text-white">
								<td>
									{{$branch}}
								</td>
							</tr>
							@endif
							@foreach ($branches as $department => $departments)
								@if( $department != '--' AND $department != '') 
								<tr class="bg-primary text-white">
									<td>
										{{$department}}
									</td>
								</tr>
								@endif
								@foreach ($departments as $shifts)
									<tr class="">
										<td class="cellHL" 
										@if( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts['is_owner'] === TRUE )) 
										id="cursor-hand" onClick="schedule.editSchedule('{{$shifts['user_id']}},{{$calendar['epoch']}},{{$shifts['status_id']}},{{$shifts['start_time']}},{{$shifts['end_time']}},'{{$shifts['schedule_policy_id']}}','{{$shifts['absence_policy_id']}}')"
										@endif nowrap>
											@if( $shifts['start_time'])
												<b>{{$shifts['user_full_name']}}</b><br>
												@if( isset($shifts['id']) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts['is_owner'] === TRUE ) ))
													<a href="javascript:schedule.editSchedule({{$shifts['id']}},{{$shifts['user_id']}},{{$calendar['epoch']}})">
												@endif
		
												@if( $shifts['status_id'] == 20)
												<span color="red">{{$shifts['absence_policy'] ?? "N/A"}}</span>
												@else
												{{getdate_helper('time', $shifts['start_time'])}}-{{getdate_helper('time', $shifts['end_time'])}}
												@endif
												@if( isset($shifts['id']) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts['is_owner'] === TRUE ) ))</a>@endif
												@if( !isset($shifts['id']))[R]@endif
											@else
												<br>
											@endif
										</td>
									</tr>
								@endforeach
							@endforeach
						@endforeach
					@endif
				</table>
			</td>
		@endforeach
		</tr>
		<tr class="bg-primary text-white" height="1">
        @foreach ($calendar_array as $calendar)
			<td id="cursor-hand" 
            @if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child'))
            onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"
            @endif nowrap>
				@if (!empty($schedule_shift_totals[$calendar['date_stamp']]))
					{{$schedule_shift_totals[$calendar['date_stamp']]['total_users'] ?? 0}} Employees -
					{{gettimeunit_helper($schedule_shift_totals[$calendar['date_stamp']]['total_time'], 0)}}
				@endif
			</td>
		@endforeach
		</tr>
	</table>
</form>
<script language="JavaScript">changeHeight(document.getElementById('body'), parent.document.getElementById('schedule_layer'), 'screen_size' ); 
    @if( $do != '')
        parent.location.hash = 'schedule';
    @endif
</script>

</body>
</html>


</x-app-modal-layout>