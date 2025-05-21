<x-app-modal-layout :title="'Employee Nopay Count Report'">
    <style>
        .main-content {
            margin-left: 0 !important;
        }

        .page-content {
            padding: 10px !important;
        }
    </style>
    <div class="">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>
                </div>

                <div class="card-body">
                    <form method="get" action="{{ route('report.employee_nopay_count_report') }}">
                        <table class="table table-bordered">
                            <tr>
                                <td class="tblPagingLeft" colspan="100" align="right">
                                    <br>
                                    {{-- Export button, uncomment if needed --}}
                                    {{-- <a href="javascript:exportReport()"><i class="ri-file-excel-line" style="font-size=18px"></i></a> --}}
                                </td>
                            </tr>

                            @if (!empty($rows))
                                @foreach ($rows as $row)
                                    <tr class="bg-primary text-white">
                                        <td colspan="2">
                                            {{ $row['full_name'] }} ({{ $row['employee_number'] }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%" valign="top">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td class="fw-bold">Employee:</td>
                                                    <td class="cellRightEditTable">{{ $row['full_name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Employee Number:</td>
                                                    <td class="cellRightEditTable">{{ $row['employee_number'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Pay Period:</td>
                                                    <td class="cellRightEditTable">{{ $row['pay_period'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Branch:</td>
                                                    <td class="cellRightEditTable">{{ $row['default_branch'] ?? '--' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Department:</td>
                                                    <td class="cellRightEditTable">
                                                        {{ $row['default_department'] ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Verified Timesheet:</td>
                                                    <td class="cellRightEditTable">{{ $row['verified_time_sheet'] }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td valign="top">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td class="fw-bold">Province:</td>
                                                    <td class="cellRightEditTable">{{ $row['province'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Country:</td>
                                                    <td class="cellRightEditTable">{{ $row['country'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Group:</td>
                                                    <td class="cellRightEditTable">{{ $row['group'] ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Title:</td>
                                                    <td class="cellRightEditTable">{{ $row['title'] ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Verified Timesheet Date:</td>
                                                    <td class="cellRightEditTable">
                                                        {{ $row['verified_time_sheet_date'] ? getdate_helper('date', $row['verified_time_sheet_date']) : '--' }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>

                                    {{-- Time Data Table --}}
                                    @if (!empty($row['data']))
                                        <tr>
                                            <td colspan="2">
                                                <table class="table table-bordered">
                                                    <tr class="bg-primary text-white">
                                                        <td colspan="{{ count($columns) }}">Time Details</td>
                                                    </tr>
                                                    <tr class="bg-primary text-white">
                                                        @foreach ($columns as $column_key => $column_name)
                                                            <td>{{ __($column_name) }}</td>
                                                        @endforeach
                                                    </tr>
                                                    @foreach ($row['data'] as $data_row)
                                                        <tr>
                                                            @foreach ($columns as $column_key => $column_name)
                                                                <td>
                                                                    @if ($column_key == 'date_stamp')
                                                                        {{ $data_row[$column_key] ?? '--' }}
                                                                    @elseif (in_array($column_key, ['worked_time', 'paid_time', 'regular_time']))
                                                                        {{ gettimeunit_helper($data_row[$column_key] ?? 0, 0) }}
                                                                    @else
                                                                        {{ $data_row[$column_key] ?? '--' }}
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="bg-primary text-white">
                                            <td colspan="2">No Time Data Available</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td colspan="2"><br><br></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="tblDataWhiteNH">
                                    <td colspan="100">No results match your filter criteria.</td>
                                </tr>
                            @endif

                            <tr>
                                <td class="bg-primary text-white" colspan="100" align="center">
                                    Generated: {{ getdate_helper('date_time', $generated_time) }}
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-modal-layout>
