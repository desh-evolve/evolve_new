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
                <h4 class="card-title mb-0 flex-grow-1">Employee Detail Report Filters</h4>
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

                
                <form method="GEt" action="{{ route('employee_detail.report') }}" id="reportForm">
                     @csrf

                    <div class="tblHeader">
                        <h5 class="mb-0">Employee Criteria</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Employee Status</label>
                            <select name="filter_data[user_status_ids][]" class="form-select select2" multiple>
                                @foreach ($data['user_status_options'] as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ in_array($value, $data['selected_user_status_options'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Groups</label>
                            <select name="filter_data[group_ids][]" class="form-select select2" multiple>
                                @foreach ($data['group_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array($value, $data['selected_group_options'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Branches</label>
                            <select name="filter_data[branch_ids][]" class="form-select select2" multiple>
                                @foreach ($data['branch_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array($value, $data['selected_branch_options'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Departments</label>
                            <select name="filter_data[department_ids][]" class="form-select select2" multiple>
                                @foreach ($data['department_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array($value, $data['selected_department_options'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Titles</label>
                            <select name="filter_data[user_title_ids][]" class="form-select select2" multiple>
                                @foreach ($data['title_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array($value, $data['selected_title_options'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="tblHeader mt-4">
                        <h5 class="mb-0">Employee Selection</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Include Employees</label>
                            <select name="filter_data[include_user_ids][]" class="form-select select2" multiple>
                                @foreach ($data['include_user_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array($value, $data['selected_include_user_options'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Exclude Employees</label>
                            <select name="filter_data[exclude_user_ids][]" class="form-select select2" multiple>
                                @foreach ($data['exclude_user_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array($value, $data['selected_exclude_user_options'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
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
                                @foreach ($data['column_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ in_array($value, $data['selected_column_options'] ?? []) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Sort By</label>
                            <select name="filter_data[primary_sort]" class="form-select">
                                @foreach ($data['sort_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ ($data['filter_data']['primary_sort'] ?? '') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Sort Direction</label>
                            <select name="filter_data[sort_direction]" class="form-select">
                                @foreach ($data['sort_direction_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ ($data['filter_data']['sort_direction'] ?? '') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group text-center mt-4">
                        <button type="submit" name="action" value="display_report" class="btn btn-primary">Display Report</button>
                        <button type="submit" name="action" value="export" class="btn btn-success">Export to CSV</button>
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

            $('#reportForm').on('submit', function(e) {
                if (!$('select[name="filter_data[column_ids][]"]').val()) {
                    e.preventDefault();
                    alert('Please select at least one column');
                    return false;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>