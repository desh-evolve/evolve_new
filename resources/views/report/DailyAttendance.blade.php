<x-app-layout :title="$title">
    <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .tblHeader {
            background-color: #f8f9fa;
            font-weight: bold;
            padding: 8px 12px;
            margin-bottom: 15px;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ $title }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-75">
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Daily Attendance Report Filters</h4>
                <a href="/reports" class="btn btn-primary">Report List <i class="ri-arrow-right-line"></i></a>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('report.daily_attendance.generate') }}" id="reportForm">
                    @csrf

                    <div class="tblHeader">
                        <h5 class="mb-0">Date Criteria</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Date Range</label>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="date_type_date" name="filter_data[date_type]" value="date" onclick="showReportDateType();" {{ ($filterData['date_type'] ?? 'date') == 'date' ? 'checked' : '' }}>
                                <label class="form-check-label" for="date_type_date">{{ __('Date Range') }}</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="date_type_pay_period" name="filter_data[date_type]" value="pay_period" onclick="showReportDateType();" {{ ($filterData['date_type'] ?? 'date') == 'pay_period' ? 'checked' : '' }}>
                                <label class="form-check-label" for="date_type_pay_period">{{ __('Pay Period') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="filter_data[start_date]" value="{{ isset($filterData['start_date']) ? date('Y-m-d', $filterData['start_date']) : '' }}">
                            
                            <label class="mt-2">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="filter_data[end_date]" value="{{ isset($filterData['end_date']) ? date('Y-m-d', $filterData['end_date']) : '' }}">
                            
                            <label class="mt-2">Pay Period</label>
                            <select name="filter_data[pay_period_ids][]" id="filter_pay_period" class="form-select select2" multiple>
                                @foreach ($data['pay_period_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['pay_period_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="tblHeader mt-4">
                        <h5 class="mb-0">Employee Criteria</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Employee Status</label>
                            <select name="filter_data[user_status_ids][]" class="form-select select2" multiple>
                                @foreach ($data['src_user_status_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['user_status_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Groups</label>
                            <select name="filter_data[group_ids][]" class="form-select select2" multiple>
                                @foreach ($data['src_group_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['group_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Default Branches</label>
                            <select name="filter_data[branch_ids][]" class="form-select select2" multiple>
                                @foreach ($data['src_branch_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['branch_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Default Departments</label>
                            <select name="filter_data[department_ids][]" class="form-select select2" multiple>
                                @foreach ($data['src_department_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['department_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Punch Branches</label>
                            <select name="filter_data[punch_branch_ids][]" class="form-select select2" multiple>
                                @foreach ($data['src_punch_branch_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['punch_branch_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Branch</label>
                            <select name="filter_data[punch_branch_ids][]" class="form-select select2" multiple>
                                @foreach ($data['src_punch_branch_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['punch_branch_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Punch Departments</label>
                            <select name="filter_data[punch_department_ids][]" class="form-select select2" multiple>
                                @foreach ($data['src_punch_department_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['punch_department_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Employee Titles</label>
                            <select name="filter_data[user_title_ids][]" class="form-select select2" multiple>
                                @foreach ($data['src_user_title_options'] as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, $filterData['user_title_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="tblHeader mt-4">
                            <h5 class="mb-0">Employee Selection</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Include Employees</label>
                                <select name="filter_data[include_user_ids][]" class="form-select select2" multiple>
                                    @foreach ($data['src_include_user_options'] as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, $filterData['include_user_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Exclude Employees</label>
                                <select name="filter_data[exclude_user_ids][]" class="form-select select2" multiple>
                                    @foreach ($data['src_exclude_user_options'] as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, $filterData['exclude_user_ids'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="tblHeader mt-4">
                            <h5 class="mb-0">Report Configuration</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Columns</label>
                                <select name="filter_data[column_ids][]" class="form-select select2" multiple required>
                                    @foreach ($data['src_column_options'] as $key => $label)
                                        <option value="{{ $key }}" {{ in_array($key, $filterData['column_ids'] ?? []) ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3 form-group">
                                <label>Primary Sort</label>
                                <select name="filter_data[primary_sort]" class="form-select">
                                    @foreach ($data['sort_options'] as $key => $label)
                                        <option value="{{ $key }}" {{ ($filterData['primary_sort'] ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        <option value="-{{ $key }}" {{ ($filterData['primary_sort'] ?? '') == '-'.$key ? 'selected' : '' }}>{{ $label }} (Desc)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Secondary Sort</label>
                                <select name="filter_data[secondary_sort]" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach ($data['sort_options'] as $key => $label)
                                        <option value="{{ $key }}" {{ ($filterData['secondary_sort'] ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        <option value="-{{ $key }}" {{ ($filterData['secondary_sort'] ?? '') == '-'.$key ? 'selected' : '' }}>{{ $label }} (Desc)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Export Format</label>
                                <select name="filter_data[export_type]" class="form-select">
                                    @foreach ($data['export_type_options'] as $key => $label)
                                        <option value="{{ $key }}" {{ ($filterData['export_type'] ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-center mt-4">
                        <button type="submit" name="action" value="display_report" class="btn btn-primary" formtarget="_blank">Display Report</button>
                        <button type="submit" name="action" value="display_timesheet" class="btn btn-primary" formtarget="_blank">Display TimeSheet</button>
                        <button type="submit" name="action" value="display_detailed_timesheet" class="btn btn-primary" formtarget="_blank">Display Detailed TimeSheet</button>
                        <button type="submit" name="action" value="export" class="btn btn-success">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select options',
                allowClear: true
            });

            function showReportDateType() {
                const dateTypeDate = document.getElementById('date_type_date').checked;
                document.getElementById('start_date').disabled = !dateTypeDate;
                document.getElementById('end_date').disabled = !dateTypeDate;
                document.getElementById('filter_pay_period').disabled = dateTypeDate;
            }

            $('#reportForm').on('submit', function(e) {
                if (!$('select[name="filter_data[column_ids][]"]').val()) {
                    e.preventDefault();
                    alert('Please select at least one column');
                    return false;
                }
            });

            showReportDateType();
        });
    </script>
    @endpush
</x-app-layout>