<x-app-layout :title="'Pay Stub Summary Report'">
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

                    <form method="post" name="report" action="{{ route('report.payroll_report') }}" target="_self">
                        @csrf
                        <input type="hidden" id="action" name="action" value="">

                        <div id="contentBoxTwoEdit">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <table class="table table-bordered">
                                <tr class="bg-primary text-white">
                                    <td colspan="3">
                                        {{ __('Saved Reports') }}
                                    </td>
                                </tr>

                                {!! html_report_save(['generic_data' => $generic_data, 'object' => 'ugdf']) !!}

                                <tr class="bg-primary text-white">
                                    <td colspan="3">
                                        {{ __('Report Filter Criteria') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="align-middle" rowspan="2" style="width: 50px;">
                                        <input type="radio" class="checkbox" id="date_type_transaction_date"
                                            name="filter_data[date_type]" value="transaction_date"
                                            onclick="showReportDateType();"
                                            {{ ($filter_data['date_type'] ?? 'transaction_date') == 'transaction_date' ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-end" style="width: 200px;">
                                        {{ __('Transaction Start Date:') }}
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm" id="start_date"
                                            name="filter_data[transaction_start_date]"
                                            value="{{ isset($filter_data['transaction_start_date']) ? date('Y-m-d', $filter_data['transaction_start_date']) : '' }}">
                                        <small>{{ __('ie:') }}
                                            {{ $current_user_prefs->getDateFormatExample() ?? 'YYYY-MM-DD' }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-end">
                                        {{ __('Transaction End Date:') }}
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm" id="end_date"
                                            name="filter_data[transaction_end_date]"
                                            value="{{ isset($filter_data['transaction_end_date']) ? date('Y-m-d', $filter_data['transaction_end_date']) : '' }}">
                                        <small>{{ __('ie:') }}
                                            {{ $current_user_prefs->getDateFormatExample() ?? 'YYYY-MM-DD' }}</small>
                                    </td>
                                </tr>

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'pay_period',
                                    'display_name' => 'Pay Period',
                                    'display_plural_name' => 'Pay Periods',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'user_status',
                                    'display_name' => 'Employee Status',
                                    'display_plural_name' => 'Employee Statuses',
                                ]) !!}

                                {{-- {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'user_status',
                                    'display_name' => 'Employee Status',
                                    'display_plural_name' => 'Employee Statuses',
                                ]) !!} --}}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'group',
                                    'display_name' => 'Group',
                                    'display_plural_name' => 'Groups',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'branch',
                                    'display_name' => 'Default Branch',
                                    'display_plural_name' => 'Branches',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'department',
                                    'display_name' => 'Default Department',
                                    'display_plural_name' => 'Departments',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'user_title',
                                    'display_name' => 'Employee Title',
                                    'display_plural_name' => 'Titles',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'include_user',
                                    'display_name' => 'Include Employees',
                                    'display_plural_name' => 'Employees',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'exclude_user',
                                    'display_name' => 'Exclude Employees',
                                    'display_plural_name' => 'Employees',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'currency',
                                    'display_name' => 'Currency',
                                    'display_plural_name' => 'Currencies',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'column',
                                    'display_name' => 'Columns',
                                    'display_plural_name' => 'Columns',
                                    'order' => true,
                                ]) !!}

                                <tr onclick="showHelpEntry('group_by')">
                                    <td colspan="2" class="text-end">
                                        {{ __('Group By:') }}
                                    </td>
                                    <td>
                                        <select id="primary_group_by" name="filter_data[primary_group_by]"
                                            class="form-select form-select-sm">
                                            @foreach ($filter_data['group_by_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ ($filter_data['primary_group_by'] ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                {!! html_report_sort(['filter_data' => $filter_data]) !!}

                                <tr onclick="showHelpEntry('sort')">
                                    <td colspan="2" class="text-end">
                                        {{ __('Export Format:') }}
                                    </td>
                                    <td>
                                        <select id="export_type" name="filter_data[export_type]"
                                            class="form-select form-select-sm">
                                            @foreach ($filter_data['export_type_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ ($filter_data['export_type'] ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr onclick="showHelpEntry('hide_employer_rows')">
                                    <td colspan="2" class="text-end">
                                        {{ __('Hide Employer Contributions:') }}
                                    </td>
                                    <td>
                                        <input type="checkbox" class="checkbox" name="filter_data[hide_employer_rows]"
                                            value="1"
                                            {{ $filter_data['hide_employer_rows'] ?? false ? 'checked' : '' }}>
                                        <small>{{ __('(Pay stub only)') }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div id="contentBoxFour" class="mt-3">
                            <input class="btn btn-primary btn-sm" type="button" id="display_report" name="action"
                                value="{{ __('Display Report') }}"
                                onclick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').value = 'Display Report'; this.form.submit();">
                            <input class="btn btn-primary btn-sm" type="button" id="view_pay_stubs" name="action"
                                value="{{ __('View Pay Stubs') }}"
                                onclick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').value = 'View Pay Stubs'; this.form.submit();">
                            <input class="btn btn-primary btn-sm" type="button" id="export_report" name="action"
                                value="{{ __('Export') }}"
                                onclick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').value = 'Export'; this.form.submit();">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

  <script language="JavaScript">
        $(document).ready(function() {
            countAllReportCriteria();
        });

            var report_criteria_elements = [
                'filter_user_status',
                'filter_group',
                'filter_branch',
                'filter_department',
                'filter_user_title',
                'filter_pay_period',
                'filter_include_user',
                'filter_exclude_user',
                'filter_currency',
                'filter_column'
            ];

        </script>
</x-app-layout>
