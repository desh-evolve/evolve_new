<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ $title ?? 'Status Report' }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ $batch_title }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            @if ($batch_next_page != '') [ <a href="{{ $batch_next_page }}">{{ __('Continue') }}</a> ] @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">

<!----------------------------------------------------------------------------------------------------------------------->

<div id="rowContent">
	<div id="rowContentInner">
		<form method="get" action="#">
			<table class="table table-striped" id="user_tbl">
				<thead>
					<tr class="">
						<td colspan="3">
							<div class="d-flex justify-content-center">
								<h5 style="color:red;">{{ __('Failed:') }}</h5> <h5>{{ $status_count['status'][10]['total'] }}/{{ $status_count['total'] }} ({{ $status_count['status'][10]['percent'] }}%)</h5>
								&nbsp;&nbsp;&nbsp;&nbsp;<h5 style="color:blue;">{{ __('Warning:') }}</h5> <h5>{{ $status_count['status'][20]['total'] }}/{{ $status_count['total'] }} ({{ $status_count['status'][20]['percent'] }}%)</h5>
								&nbsp;&nbsp;&nbsp;&nbsp;<h5 style="color:green;">{{ __('Success:') }}</h5> <h5>{{ $status_count['status'][30]['total'] }}/{{ $status_count['total'] }} ({{ $status_count['status'][30]['percent'] }}%)</h5>
							</div>
						</td>
					</tr>
					<tr class="bg-primary text-white">
						<th>#</th>
						<th>{{ __('Label') }}</th>
						<th>{{ __('Status') }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($rows as $index => $row)
						@php
							$row_class = $index % 2 == 0 ? 'table-light' : 'table-secondary';
							if ($row['deleted']) {
								$row_class = 'table-danger';
							}
						@endphp
						<tr class="{{ $row_class }}">
							<td @if ($row['description'] != '') rowspan="2" @endif>{{ $loop->iteration }}</td>
							<td>{{ $row['label'] }}</td>
							<td @if ($row['description'] != '') rowspan="2" @endif>
								<span class="badge bg-{{ $row['status_id'] == 10 ? 'danger' : ($row['status_id'] == 20 ? 'primary' : 'success') }}">
									{{ $row['status'] }}
								</span>
							</td>
						</tr>
						@if ($row['description'] != '')
							<tr class="{{ $row_class }}">
								<td colspan="1">{{ nl2br(e($row['description'])) }}</td>
							</tr>
						@endif
					@endforeach
				</tbody>
			</table>
		</form>
	</div>
</div>

<!----------------------------------------------------------------------------------------------------------------------->


				</div>
			</div>
		</div>
	</div>
</x-app-layout>






