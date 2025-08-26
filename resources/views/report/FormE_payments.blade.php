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
                    <form method="post" name="report" action="{{ route('report.formE_payments') }}" target="_self">
                        @csrf
                        <input type="hidden" id="action" name="action" value="">

                        <div id="contentBoxTwoEdit">
                            @if (!$ugdf->Validator->isValid())
                                <div class="alert alert-danger">
                                    @foreach ($ugdf->Validator->getErrors() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
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

                                <tr>
                                    <td colspan="2" class="text-end">
                                        {{ __('Zone:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="filter_data[zone]" class="form-select">
                                            <option value="A">A</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" class="text-end">
                                        {{ __('Payment Mode:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="filter_data[pay_mode]" class="form-select">
                                            <option value="1">Cash</option>
                                            <option value="2">Cheque</option>
                                            <option value="3">Money Order</option>
                                            <option value="4">Direct Debit</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" class="text-end">
                                        {{ __('Payment Ref:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input name="filter_data[bank_code]" style="width: 80px;" type="text" value="{{ $filter_data['branch_code1'] ?? '' }}">&nbsp;-&nbsp;
                                        <input name="filter_data[branch_code]" style="width: 60px;" type="text" value="">&nbsp;-&nbsp;
                                        <input name="filter_data[account_number]" style="width: 160px;" type="text" value="{{ $filter_data['account1'] ?? '' }}">&nbsp;&nbsp;{{ __('(Bank Code - Branch Code - A/C Number)') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" class="text-end">
                                        {{ __('Date Of Payments:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="10" id="date_payments" name="filter_data[date_payments]" value="{{ $filter_data['date_payments1'] ?? '' }}">
題
                                        <img src="{{ $BASE_URL }}/images/cal.gif" id="cal_date_payments" width="16" height="16" border="0" alt="{{ __('Pick a date') }}" onmouseover="calendar_setup('date_payments', 'cal_date_payments', false);">
                                        {{ __('ie:') }} {{ $current_user_prefs->getDateFormatExample() }}
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" class="text-end">
                                        {{ __('D/O Code:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="10" id="district_office" name="filter_data[district_office]" value="{{ $filter_data['district_office'] ?? '' }}">
                                    </td>
                                </tr>

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

                                <tr>
                                    <td colspan="2" class="text-end">
                                        {{ __('Allow Submission Number Count:') }}
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="checkbox" name="filter_data[submission_number]" value="submission_num" class="form-check-input">
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div id="contentBoxFour" class="mt-3">
                            <button type="button"
                                class="btn btn-primary btn-sm"
                                id="export"
                                onclick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').value = 'Export'; this.form.submit();">
                                {{ __('Export') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
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
            'filter_exclude_user'
        ];
    </script>
</x-app-layout>