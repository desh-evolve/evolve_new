<x-app-layout :title="$title">
    <style>
        th,
        td {
            padding: 6px !important;
        }

        .arrow-icon {
            background-color: green;
            color: white;
            font-size: 16px;
            border-radius: 50%;
            padding: 5px;
            align-items: center;
            margin: 2px;
        }
    </style>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>
                </div>

                <div class="card-body">
                    @if ($permission->Check('pay_stub', 'view') || $permission->Check('pay_stub', 'view_child'))
                    {{-- <form method="post" name="pay_stubs" action="{{ route('attendance.paystubs') }}" target="_self">
                        @csrf --}}
                    <form method="post" name="report" action="{{ route('attendance.paystubs') }}"
                        target="_self">
                            @csrf
                        {{-- <input type="hidden" id="action" name="action" value="">
                        <input type="hidden" name="sort_column" value="{{ $sort_column ?? '' }}">
                        <input type="hidden" name="sort_order" value="{{ $sort_order ?? '' }}">
                        <input type="hidden" name="page" value="{{ $filter_data['page'] ?? 1 }}">
                        <input type="hidden" name="saved_search_id" value="{{ $saved_search_id ?? '' }}">

                        <div id="contentBoxTwoEdit">
                            <table class="table table-bordered">
                                <tr class="bg-primary text-white">
                                    <td colspan="3">
                                        {{ __('Saved Reports') }}
                                    </td>
                                </tr>

                                {!! html_report_save(['generic_data' => $filter_data['saved_search_options'] ?? [], 'object' => 'pay_stubs']) !!}

                                <tr class="bg-primary text-white">
                                    <td colspan="3">
                                        {{ __('Pay Stub Filter Criteria') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('Employee Status:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select id="filter_data_status_id" name="filter_data[status_id]" class="form-select">
                                            @foreach ($filter_data['status_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['status_id']) && $filter_data['status_id'] == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('Pay Period:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="filter_data[pay_period_id]" class="form-select">
                                            @foreach ($filter_data['pay_period_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['pay_period_id']) && $filter_data['pay_period_id'] == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('Employee:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="filter_data[user_id]" class="form-select">
                                            @foreach ($filter_data['user_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['user_id']) && $filter_data['user_id'] == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('Group:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="filter_data[group_id]" class="form-select">
                                            @foreach ($filter_data['group_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['group_id']) && $filter_data['group_id'] == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('Default Branch:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="filter_data[default_branch_id]" class="form-select">
                                            @foreach ($filter_data['branch_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['default amplifiers_branch_id']) && $filter_data['default_branch_id'] == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('Default Department:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="filter_data[default_department_id]" class="form-select">
                                            @foreach ($filter_data['department_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['default_department_id']) && $filter_data['default_department_id'] == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('Title:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="filter_data[title_id]" class="form-select">
                                            @foreach ($filter_data['title_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['title_id']) && $filter_data['title_id'] == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('Export Format:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select id="export_type" name="export_type" class="form-select">
                                            @foreach ($export_type_options ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['export_type']) && $filter_data['export_type'] == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div> --}}

                        <div id="contentBoxFour" class="mt-3">
                            @if ($permission->Check('pay_stub', 'view') || $permission->Check('pay_stub', 'view_own') || $permission->Check('pay_stub', 'view_child'))
                                <button type="button" class="btn btn-primary btn-sm" id="view"
                                    onclick="document.getElementById('action').value = 'view'; this.form.submit();">
                                    {{ __('View') }}
                                </button>
                            @endif

                            @if ($permission->Check('pay_stub', 'edit') || $permission->Check('pay_stub', 'edit_child'))
                                <button type="button" class="btn btn-primary btn-sm" id="mark_paid"
                                    onclick="document.getElementById('action').value = 'mark_paid'; this.form.submit();">
                                    {{ __('Mark Paid') }}
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" id="mark_unpaid"
                                    onclick="document.getElementById('action').value = 'mark_unpaid'; this.form.submit();">
                                    {{ __('Mark UnPaid') }}
                                </button>
                            @endif

                            @if ($permission->Check('pay_stub', 'delete') || $permission->Check('pay_stub', 'delete_child'))
                                <button type="button" class="btn btn-danger btn-sm" id="delete"
                                    onclick="if (confirm('{{ __('Are you sure you want to delete?') }}')) { document.getElementById('action').value = 'delete'; this.form.submit(); }">
                                    {{ __('Delete') }}
                                </button>
                            @endif

                            @if ($permission->Check('pay_stub', 'undelete'))
                                <button type="button" class="btn btn-primary btn-sm" id="undelete"
                                    onclick="document.getElementById('action').value = 'undelete'; this.form.submit();">
                                    {{ __('UnDelete') }}
                                </button>
                            @endif

                            @if ($permission->Check('pay_stub', 'view') || $permission->Check('pay_stub', 'view_child'))
                                <button type="button" class="btn btn-primary btn-sm" id="export"
                                    onclick="document.getElementById('action').value = 'export'; this.form.submit();">
                                    {{ __('Export') }}
                                </button>
                            @endif
                        </div>
                    </form>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th>{{ __('#') }}</th>
                                    @foreach ($columns as $column_id => $column)
                                        <th>
                                            <a href="{{ route('attendance.paystubs', ['sort_column' => $column_id, 'sort_order' => ($sort_column == $column_id && $sort_order == 'asc') ? 'desc' : 'asc', 'saved_search_id' => $saved_search_id]) }}">
                                                {{ __($column) }}
                                            </a>
                                        </th>
                                    @endforeach
                                    <th>{{ __('Functions') }}</th>
                                    <th>
                                        <input type="checkbox" class="form-check-input" name="select_all"
                                            onclick="CheckAll(this); @if ($permission->Check('pay_stub', 'view')) document.getElementById('hide_employer').checked = document.getElementById('hide_employer').checked; @endif">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pay_stubs as $index => $pay_stub)
                                    <tr class="{{ $pay_stub['deleted'] ? 'table-danger' : ($index % 2 == 0 ? 'table-light' : 'table-secondary') }}">
                                        <td>{{ $index + 1 }}</td>
                                        @foreach ($columns as $key => $column)
                                            <td>{{ $pay_stub[$key] ?? '--' }}</td>
                                        @endforeach
                                        <td>
                                            @if ($permission->Check('pay_stub', 'view') || ($permission->Check('pay_stub', 'view_child') && $pay_stub['is_child']) || ($permission->Check('pay_stub', 'view_own') && $pay_stub['is_owner']))
                                                <a href="{{ route('attendance.paystubs', ['action' => 'view', 'id' => $pay_stub['id']]) }}"
                                                   class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                                            @endif
                                            @if (in_array($pay_stub['status_id'], [10, 25]) && ($permission->Check('pay_stub', 'edit') || ($permission->Check('pay_stub', 'edit_child') && $pay_stub['is_child']) || ($permission->Check('pay_stub', 'edit_own') && $pay_stub['is_owner'])))
                                                <a href="{{ route('attendance.paystubs', ['id' => $pay_stub['id'], 'filter_pay_period_id' => $filter_data['pay_period_id'] ?? '']) }}"
                                                   class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="checkbox" class="form-check-input" name="ids[]"
                                                   value="{{ $pay_stub['id'] }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- <div class="d-flex justify-content-end">
                        {{ $pay_stubs->links() }}
                    </div> --}}

                    @if ($permission->Check('pay_stub', 'view') || $permission->Check('pay_stub', 'view_child'))
                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="hide_employer"
                                   name="hide_employer_rows" value="1"
                                   {{ isset($filter_data['hide_employer_rows']) && $filter_data['hide_employer_rows'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="hide_employer">
                                {{ __('Hide Employer Contributions') }}
                            </label>
                        </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            countAllReportCriteria();
        });

        var report_criteria_elements = [
            'filter_data_status_id',
            'filter_data_pay_period_id',
            'filter_data_user_id',
            'filter_data_group_id',
            'filter_data_default_branch_id',
            'filter_data_default_department_id',
            'filter_data_title_id'
        ];

        function CheckAll(checkbox) {
            var checkboxes = document.getElementsByName('ids[]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = checkbox.checked;
            }
        }

        function countAllReportCriteria() {
            report_criteria_elements.forEach(function(id) {
                var element = document.getElementById(id);
                if (element) {
                    // Add any specific handling for report criteria if needed
                }
            });
        }
    </script>
</x-app-layout>