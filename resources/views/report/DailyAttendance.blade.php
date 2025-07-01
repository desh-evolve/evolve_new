<x-app-layout :title="'Report'">
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
                    <form method="post" name="report" action="{{ route('report.daily_attendance') }}" target="_self">
                        @csrf
                        <input type="hidden" id="action" name="action" value="">

                        <div id="contentBoxTwoEdit">
                            {{-- @if (!$ugdf->Validator->isValid())
                                <div class="alert alert-danger">
                                    @foreach ($ugdf->Validator->getErrors() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif --}}

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
                                    <td class="cellReportRadioColumn" rowspan="2">
                                        <input type="radio" class="form-check-input" id="date_type_date"
                                            name="filter_data[date_type]" value="date"
                                            onchange="showReportDateType();"
                                            {{ !isset($filter_data['date_type']) || $filter_data['date_type'] == 'date' ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-end">
                                        {{ __('Date:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="date" id="start_date" name="filter_data[start_date]"
                                            value="{{ getdate_helper('DATE', $filter_data['start_date'] ?? '') }}">
                                        <img src="{{ $BASE_URL }}/images/cal.gif" id="cal_start_date" width="16"
                                            height="16" border="0" alt="{{ __('Pick a date') }}"
                                            onmouseover="calendar_setup('start_date', 'cal_start_date', false);">
                                        {{ __('ie:') }} {{ $current_user_prefs->getDateFormatExample() }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-end">
                                        {{ __('End Date:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="date" id="end_date" name="filter_data[end_date]"
                                            value="{{ getdate_helper('DATE', $filter_data['end_date'] ?? '') }}">
                                        <img src="{{ $BASE_URL }}/images/cal.gif" id="cal_end_date" width="16"
                                            height="16" border="0" alt="{{ __('Pick a date') }}"
                                            onmouseover="calendar_setup('start_date', 'cal_end_date', false);">
                                        {{ __('ie:') }} {{ $current_user_prefs->getDateFormatExample() }}
                                    </td>
                                </tr>

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'date_type' => true,
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
                                    'label' => 'punch_branch',
                                    'display_name' => 'Punch Branch',
                                    'display_plural_name' => 'Branches',
                                ]) !!}

                                {!! html_report_filter([
                                    'filter_data' => $filter_data,
                                    'label' => 'punch_department',
                                    'display_name' => 'Punch Department',
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
                                    'label' => 'column',
                                    'order' => true,
                                    'display_name' => 'Columns',
                                    'display_plural_name' => 'Columns',
                                ]) !!}

                                {!! html_report_sort(['filter_data' => $filter_data]) !!}

                                <tr>
                                    <td colspan="2" class="text-end">
                                        {{ __('Export Format:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select id="export_type" name="filter_data[export_type]" class="form-select">
                                            @foreach ($filter_data['export_type_options'] ?? [] as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ isset($filter_data['export_type']) && $filter_data['export_type'] === $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div id="contentBoxFour" class="mt-3">
                            {{-- <button type="button"
                                class="btn btn-primary btn-sm {{ $hidden_elements['displayReport'] }}"
                                id="display_report"
                                onclick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').value = 'Display Report'; this.form.submit();">
                                {{ __('Display Report') }}
                            </button>
                             --}}
                            <button type="button"
                                class="btn btn-primary btn-sm {{ $hidden_elements['displayReport'] }}"
                                id="display_report"
                                onclick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').value = 'display_timesheet'; console.log('Action set to: ' + document.getElementById('action').value); this.form.submit();">
                                {{ __('Display Report 00') }}
                            </button>
                            <button type="button"
                                class="btn btn-primary btn-sm {{ $hidden_elements['displayTimeSheet'] }}"
                                id="display_timesheet"
                                onclick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').value = 'display_timesheet'; console.log('Action set to: ' + document.getElementById('action').value); this.form.submit();">
                                {{ __('Display TimeSheet') }}
                            </button>
                            <button type="button"
                                class="btn btn-primary btn-sm {{ $hidden_elements['displayDetailedTimeSheet'] }}"
                                id="display_detailed_timesheet"
                                onclick="selectAllReportCriteria(); document.getElementById('action').value = 'Display Detailed TimeSheet'; this.form.submit();">
                                {{ __('Display Detailed TimeSheet') }}
                            </button>
                            <button type="button"
                                class="btn btn-primary btn-sm {{ $hidden_elements['export'] }}"
                                id="export"
                                onclick="selectAllReportCriteria(); document.getElementById('action').value = 'Export'; this.form.submit();">
                                {{ __('Export') }}
                            </button>
                            {{-- <button type="button" class="btn btn-primary btn-sm {{ $hidden_elements['export'] }}"
                                id="export"
                                onclick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').value = 'Export'; this.form.submit();">
                                {{ __('Export') }}
                            </button> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            countAllReportCriteria();
            showReportDateType();
        });

        var report_criteria_elements = [
            'filter_user_status',
            'filter_group',
            'filter_branch',
            'filter_department',
            'filter_punch_branch',
            'filter_punch_department',
            'filter_user_title',
            'filter_pay_period',
            'filter_include_user',
            'filter_exclude_user',
            'filter_column'
        ];

        var report_date_type_elements = {
            'date_type_date': ['start_date', 'end_date'],
            'date_type_pay_period': ['src_filter_pay_period', 'filter_pay_period']
        };

        function showReportDateType() {
            for (var key in report_date_type_elements) {
                var element = document.getElementById(key);
                if (element) {
                    var className = element.checked ? '' : 'DisableFormElement';
                    report_date_type_elements[key].forEach(function(id) {
                        var field = document.getElementById(id);
                        if (field) {
                            field.className = className;
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>
