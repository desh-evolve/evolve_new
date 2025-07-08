<x-app-layout :title="'Employee TimeSheet Report'">
    <style>
        td, th {
            padding: 5px !important;
        }
        .tblList {
            width: 100%;
        }
        .tblPagingLeft {
            text-align: right;
        }
        .editTable {
            width: 100%;
        }
        .cellLeftEditTable {
            text-align: right;
            padding-right: 10px;
        }
        .cellRightEditTable {
            text-align: left;
        }
        .tblDataWhite {
            background-color: #ffffff;
        }
        .tblDataGrey {
            background-color: #f5f5f5;
        }
        .tblDataWhiteNH {
            background-color: #ffffff;
            text-align: center;
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
                    <form method="get" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                        <table class="table table-bordered tblList">
                            <thead>
                                <tr>
                                    <td class="tblPagingLeft" colspan="100" align="right">
                                        <a href="javascript:exportReport()"><img src="{{ env('IMAGES_URL', '/images') }}/excel_icon.gif"></a>
                                    </td>
                                </tr>
                                <tr class="bg-primary text-white">
                                    <th colspan="100">
                                        @if ($filter_data['date_type'] == 'pay_period_ids')
                                            {{ __('Pay Period(s):') }}
                                            @foreach ($filter_data['pay_period_ids'] as $index => $pay_period_id)
                                                {{ $pay_period_options[$pay_period_id] ?? '' }}{{ $loop->first && !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        @else
                                            {{ __('From:') }} {{ getdate_helper('date', $filter_data['start_date'] ?? null) }}
                                            {{ __('To:') }} {{ getdate_helper('date', $filter_data['end_date'] ?? null) }}
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!empty($rows))
                                    @foreach ($rows as $row)
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr class="bg-primary text-white">
                                                    <th align="left" colspan="100">
                                                        <table class="editTable">
                                                            <tr>
                                                                <td>
                                                                    <table class="editTable">
                                                                        <tr>
                                                                            <td class="cellLeftEditTable">
                                                                                {{ __('Employee:') }}
                                                                            </td>
                                                                            <td class="cellRightEditTable">
                                                                                {{ $row['full_name'] }} (#{{ $row['employee_number'] }})
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table class="editTable">
                                                                        <tr>
                                                                            <td class="cellLeftEditTable">
                                                                                {{ __('Pay Period:') }}
                                                                            </td>
                                                                            <td class="cellRightEditTable">
                                                                                {{ $row['pay_period'] }} ({{ __('Verified:') }} {{ $row['verified_time_sheet'] }})
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </th>
                                                </tr>
                                                <tr class="bg-primary text-white">
                                                    <th>{{ __('#') }}</th>
                                                    @foreach ($columns as $column)
                                                        <th>{{ $column }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($row['data'] as $index => $sub_row)
                                                    <tr class="{{ $index % 2 == 0 ? 'tblDataWhite' : 'tblDataGrey' }}" @if ($index == count($row['data']) - 1) style="font-weight: bold;" @endif>
                                                        <td>
                                                            @if ($index == count($row['data']) - 1)
                                                                <br>
                                                            @else
                                                                {{ $index + 1 }}
                                                            @endif
                                                        </td>
                                                        @foreach ($columns as $key => $column)
                                                            <td>
                                                                @if ($key == 'actual_time_diff_wage')
                                                                    @if (!empty($sub_row[$key]))
                                                                        ${{ $sub_row[$key] ?? '--' }}
                                                                    @else
                                                                        {{ $sub_row[$key] ?? '--' }}
                                                                    @endif
                                                                @else
                                                                    {{ $sub_row[$key] ?? '--' }}
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                                <tr><td><br></td></tr>
                                            </tbody>
                                        </table>
                                    @endforeach
                                @else
                                    <tr class="tblDataWhiteNH">
                                        <td colspan="100">
                                            {{ __('No results match your filter criteria.') }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="bg-primary text-white" colspan="100" align="center">
                                        {{ __('Generated:') }} {{ getdate_helper('date_time', $generated_time ?? time()) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end col -->
    </div>
</x-app-layout>