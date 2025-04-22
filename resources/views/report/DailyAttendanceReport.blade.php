<x-app-layout :title="$title">
    <style>
        .tblList {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .tblHeader {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .tblDataWhite {
            background-color: #ffffff;
        }

        .tblDataGrey {
            background-color: #f2f2f2;
        }

        .tblPagingLeft {
            text-align: right;
            padding: 10px;
        }

        .editTable {
            width: auto;
            display: inline-table;
            margin-right: 20px;
        }

        .cellLeftEditTable,
        .cellRightEditTable {
            padding: 5px 10px;
        }

        .cellLeftEditTable {
            font-weight: bold;
        }

        .tblList td,
        .tblList th {
            border: 1px solid #dee2e6;
            padding: 8px;
            vertical-align: middle;
        }

        .tblList .bold {
            font-weight: bold;
        }

        .textTitle {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .textTitleSub {
            font-weight: bold;
        }
    </style>

    <div id="rowContent">
        <div id="titleTab">
            <div class="textTitle"><span class="textTitleSub">{{ $title }}</span></div>
        </div>
        <div id="rowContentInner">
            <table class="tblList">
                <thead>
                    <tr>
                        <td class="tblPagingLeft" colspan="100" align="right">
                            <a href="#" onclick="exportReport()" title="Export to Excel">
                                <i class="ri-file-excel-2-line" style="font-size: 24px;"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="tblHeader">
                        <td colspan="100">
                            @if ($filter_data['date_type'] === 'pay_period_ids')
                                {{ __('Pay Period(s):') }}
                                @foreach ($filter_data['pay_period_ids'] as $index => $pay_period_id)
                                    {{ $pay_period_options[$pay_period_id] ?? '--' }}
                                    @if ($index < count($filter_data['pay_period_ids']) - 1)
                                        ,
                                    @endif
                                @endforeach
                            @else
                                {{ __('From:') }}
                                {{ !empty($filter_data['start_date']) ? \Carbon\Carbon::parse($filter_data['start_date'])->format('Y-m-d') : '--' }}
                                {{ __('To:') }}
                                {{ !empty($filter_data['end_date']) ? \Carbon\Carbon::parse($filter_data['end_date'])->format('Y-m-d') : '--' }}
                            @endif
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td colspan="100">
                                <table width="100%">
                                    <thead>
                                        <tr class="tblHeader">
                                            <td align="left" colspan="100">
                                                <table width="100%">
                                                    <tr>
                                                        <td>
                                                            <table class="editTable">
                                                                <tr>
                                                                    <td class="cellLeftEditTable">
                                                                        {{ __('Employee') }}:
                                                                    </td>
                                                                    <td class="cellRightEditTable">
                                                                        {{ $row['full_name'] ?? '--' }}
                                                                        (#{{ $row['employee_number'] ?? '--' }})
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td>
                                                            <table class="editTable">
                                                                <tr>
                                                                    <td class="cellLeftEditTable">
                                                                        {{ __('Pay Period') }}:
                                                                    </td>
                                                                    <td class="cellRightEditTable">
                                                                        {{ $row['pay_period'] ?? '--' }}
                                                                        ({{ __('Verified') }}:
                                                                        {{ $row['verified_time_sheet'] ?? '--' }})
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        @php
                                        $fixedColumns = [
                                            'date_stamp' => 'Date',
                                            'absence_time' => 'Absence Time',
                                            'absence_policy-4' => 'Absence Policy',
                                            'categorized_time' => 'Worked Time',
                                            'schedule_working' => 'Scheduled Work',
                                            'schedule_absence' => 'Scheduled Absence',
                                            'min_punch_time_stamp' => 'First Punch',
                                            'max_punch_time_stamp' => 'Last Punch'
                                        ];
                                    @endphp
                                           <tr>
                                            <th>#</th> <!-- Row number column -->
                                            @foreach ($fixedColumns as $column => $label)
                                                <th>{{ $label }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                              
                                    <tbody>
                                        @foreach ($row['data'] as $subRowIndex => $subRow)
                                            <tr class="{{ $subRowIndex % 2 == 0 ? 'tblDataWhite' : 'tblDataGrey' }} @if ($subRowIndex == count($row['data']) - 1) bold @endif">
                                                <td>{{ $subRowIndex + 1 }}</td>
                                                
                                                @foreach ($fixedColumns as $column => $label)
                                                    <td>
                                                        @if ($column === 'date_stamp')
                                                            @if (!empty($subRow[$column]) && $subRow[$column] !== false)
                                                                {{ \Carbon\Carbon::parse($subRow[$column])->format('Y-m-d') }}
                                                            @else
                                                                --
                                                            @endif
                                                        @elseif (in_array($column, ['min_punch_time_stamp', 'max_punch_time_stamp']))
                                                            @if (!empty($subRow[$column]) && $subRow[$column] !== false)
                                                                {{ $subRow[$column] }}
                                                            @else
                                                                --
                                                            @endif
                                                        @else
                                                            {{ $subRow[$column] !== false ? ($subRow[$column] ?? '--') : '--' }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @empty
                        <tr class="tblDataWhite">
                            <td colspan="100">
                                {{ __('No results match your filter criteria.') }}
                            </td>
                        </tr>
                    @endforelse
                    <tr>
                        <td class="tblHeader" colspan="100" align="center">
                            {{ __('Generated:') }}
                            {{ !empty($generated_time) ? \Carbon\Carbon::createFromTimestamp($generated_time)->format('Y-m-d H:i:s') : '--' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function exportReport() {
            // Placeholder for Excel export functionality
            alert('Export to Excel functionality to be implemented.');
            // Implement actual export logic, e.g., redirect to an export route
            // window.location.href = '{{ route('report.daily_attendance.generate') }}';
        }
    </script>
</x-app-layout>
