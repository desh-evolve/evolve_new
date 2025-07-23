<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">

                            @if ($permission->Check('accrual', 'add') || $permission->Check('accrual', 'add_child'))
                                <button type="button" class="btn btn-primary"
                                    onclick="window.location.href=
                                '{{ route('user_accruals.add') }}?action=add&user_id={{ $user_id ?? '' }}&filter_user_id={{ $filter_user_id ?? ($user_id ?? '') }}&accrual_policy_id={{ $accrual_policy_id ?? '' }}&data='">
                                    {{ __('Add') }} <i class="ri-add-line"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-stripped" style="width:100%">
                            <thead class="bg-primary text-white">
                                <th>#</th>
                                <th>Type </th>
                                <th>Amount </th>
                                <th>Date </th>
                                <th>Functions </th>
                            </thead>
                            @foreach ($accruals as $index => $accrual)
                                {{-- <pre>{{ print_r($accrual, true) }}</pre> --}}
                                <tr>
                                    <td>{{ $index + 1 }} </td>
                                    <td>{{ $accrual['type'] }}</td>
                                    <td>{{ $accrual['amount'] }}</td>
                                    {{-- <td>{{ $accrual['time_stamp'] }}</td> --}}
                                    <td>
                                        @if (!empty($accrual['user_date_total_date_stamp']))
                                            {{ $accrual['user_date_total_date_stamp'] }}
                                        @else
                                            {{ $accrual['time_stamp'] }}
                                        @endif
                                    </td>
                                    <td>
                                        {{-- @if ($accrual['user_date_total_id'] == '' and $accrual['system_type'] == false)
                                            @if ($permission->Check('accrual', 'edit') or $permission->Check('accrual', 'edit_child'))
                                               <button type="button" class="btn btn-primary btn-sm"
                                            onclick="window.location.href='/user_accruals/add/{{ $accrual['id'] ?? '' }}'">
                                            {{ __('Edit') }}
                                        </button>
                                            @endif
                                        @endif
                                        <a class="btn btn-info btn-sm" href="#">View</a> --}}

                                        {{-- timesheet part  --}}
                                        @if ($permission->Check('accrual','view') OR $permission->Check('accrual','view_own'))
                                                [ <a href="/attendance/timesheet?filter_data[user_id]={{$accrual['user_id']}}&filter_data[date]={{$accrual['time_stamp']}}">View</a> ]
                                        @endif

                                        {{-- <a class="btn btn-info btn-sm" href="#">View</a>  --}}

                                        @if ($permission->Check('accrual', 'edit') || $permission->Check('accrual', 'edit_child'))
                                            <button type="button" class="btn btn-primary btn-sm"
                                                onclick="window.location.href='{{ route('user_accruals.add', ['id' => $accrual['id'] ?? null]) }}?action=edit&id={{ $accrual['id'] ?? '' }}&user_id={{ $user_id ?? '' }}&filter_user_id={{ $filter_user_id ?? ($user_id ?? '') }}&accrual_policy_id={{ $accrual_policy_id ?? '' }}&data='">
                                                {{ __('Edit') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        {{-- Hidden Inputs --}}
                        <input type="hidden" name="sort_column" value="{{ $sort_column ?? '' }}">
                        <input type="hidden" name="sort_order" value="{{ $sort_order ?? '' }}">
                        <input type="hidden" name="page" value="{{ $paging_data['current_page'] ?? 1 }}">
                        <input type="hidden" name="user_id" value="{{ $user_id }}">
                        <input type="hidden" name="accrual_policy_id" value="{{ $accrual_policy_id }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script></script>
</x-app-layout>
