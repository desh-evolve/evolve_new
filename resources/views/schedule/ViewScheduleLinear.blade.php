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

<table class="table table-bordered" id="schedule_table" border="0">
<form method="get" name="schedule" action="/schedule/view_schedule_linear">
		<tr>
            {{-- {* This ensures that all hour columns are divided in to 4 columns *} --}}
			<td colspan="2"></td>
            @foreach ($header_hours as $header_hour)
				<td width="{{$column_widths}}%"></td>
				<td width="{{$column_widths}}%"></td>
				<td width="{{$column_widths}}%"></td>
				<td width="{{$column_widths}}%"></td>
			@endforeach
		</tr>

        @foreach ($calendar_array as $calendar)
			<tr class="bg-primary text-white">
				<td colspan="{{$total_span_columns}}" 
                    @if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child'))
                    id="cursor-hand" onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"
                    @endif >
					{{getdate_helper('date', $calendar['epoch'])}}

					@if( isset($holidays[$calendar['epoch']]))
						<br>
						({{$holidays[$calendar['epoch']]}})
					@endif
				</td>
			</tr>

			@if (!empty($schedule_shifts[$calendar['date_stamp']]))
				@foreach ($schedule_shifts[$calendar['date_stamp']] as $branch => $branches)
					@if( $branch != '--')
					<tr class="bg-primary text-white">
						<td colspan="{{$total_span_columns}}" 
							@if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child'))
							id="cursor-hand" onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"
							@endif >
							{{$branch}}
						</td>
					</tr>
					@endif
					@foreach ($branches as $department => $departments)
							@if( $department != '--')
							<tr class="bg-primary text-white">
								<td colspan="{{$total_span_columns}}" id="cursor-hand" 
									@if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child'))onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"
									@endif>
									{{$department}}
								</td>
							</tr>
							@endif

							<tr class="bg-primary text-white" id="cursor-hand" 
								@if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child'))
								onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"
								@endif>
								<td>
									Employee
								</td>
								@foreach ($header_hours as $header_hour)
									<td class="bg-primary text-white" colspan="4" nowrap>
										{{getdate_helper('time', $header_hour['hour'])}}
									</td>
								@endforeach
							</tr>

							@foreach ($schedule_shifts[$calendar['date_stamp']][$branch][$department] as $shifts)
								<tr>
									@foreach ($shifts as $shift)
										@if($loop->first)
										
										<td class="" 
										@if( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shift['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shift['is_owner'] === TRUE )) 
										id="cursor-hand" onClick="schedule.editSchedule('',{{$shift['user_id']}},{{$calendar['epoch']}})"
										@endif nowrap>
											<b>{{$shift['user_full_name']}}</b>
										</td>
										@endif
										@if( !empty($shift['off_duty']) && $shift['off_duty'] > 0)
											<td colspan="{{$shift['off_duty']}}"></td>
										@endif
										@if( !empty($shift['off_duty']) && $shift['on_duty'] > 0)
											<td class="" colspan="{{$shift['on_duty']}}" 
												@if( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shift['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shift['is_owner'] === TRUE ) )
													id="cursor-hand" onClick="schedule.editSchedule('{{$shift['id']}}',{{$shift['user_id']}},{{$calendar['epoch']}},{{$shift['status_id']}},{{$shift['start_time']}},{{$shift['end_time']}},'{{$shift['schedule_policy_id']}}','{{$shift['absence_policy_id']}}')"
												@endif nowrap>
												@if( isset($shift['id']) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shift['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shift['is_owner'] === TRUE ) ))
													<a href="javascript:schedule.editSchedule({{$shift['id']}},{{$shift['user_id']}},{{$calendar['epoch']}})">
												@endif

												@if( $shift['status_id'] == 20)
												<span color="red">{{$shift['absence_policy'] ?? "N/A"}}</span>
												@else
												@if( $shift['span_day'] == TRUE)
													@if( $shift['span_day_split'] == TRUE)
													{{getdate_helper('time', $shift['start_time'])}}-@else-{{getdate_helper('time', $shift['end_time'])}}
													@endif
												@else
												{{getdate_helper('time', $shift['start_time'])}}-{{getdate_helper('time', $shift['end_time'])}}
												@endif
												@endif
												@if( isset($shift['id']) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shift['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shift['is_owner'] === TRUE ) ))
												</a>
												@endif
												@if( !isset($shift['id']))[R]@endif
												<br>
											</td>
										@endif
									@endforeach
								</tr>
							@endforeach
					@endforeach
				@endforeach
			@endif

			<tr class="bg-primary text-white">
				<td id="cursor-hand" colspan="{{$total_span_columns}}" 
                @if( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child'))
                onClick="schedule.editSchedule('','',{{$calendar['epoch']}})"
                @endif nowrap>
					@if (!empty($schedule_shift_totals[$calendar['date_stamp']]))
						{{$schedule_shift_totals[$calendar['date_stamp']]['total_users'] ?? 0}} Employees -
						{{gettimeunit_helper($schedule_shift_totals[$calendar['date_stamp']]['total_time'], 0) }}
					@endif
				</td>
			</tr>

			<tr>
				<td bgcolor="#000000" colspan="{{$total_span_columns}}"></td>
			</td>
		@endforeach
	</table>
</form>
<script language="JavaScript">changeHeight(document.getElementById('body'), parent.document.getElementById('schedule_layer'), 'screen_size' ); 
    @if( $do != '') parent.location.hash = 'schedule'; @endif
</script>

</body>
</html>

</x-app-modal-layout>