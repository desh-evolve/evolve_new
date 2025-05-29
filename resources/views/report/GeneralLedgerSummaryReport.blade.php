<x-app-modal-layout :title="'General Ledger Summary Report'">
    <style>
        .main-content {
            margin-left: 0 !important;
        }

        .page-content {
            padding: 10px !important;
        }

        .tblDataWhite {
            background-color: #ffffff;
        }

        .tblDataGrey {
            background-color: #f9f9f9;
        }

        .tblHeader {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .tblPagingLeft {
            text-align: right;
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
                        @csrf
                        <table class="table table-bordered">
                            <tr>
                                <td class="tblPagingLeft" colspan="14" align="right">
                                    <a href="javascript:void(0)" onclick="exportReport()"><i class="fas fa-file-excel"></i></a>
                                </td>
                            </tr>

                            <tr class="bg-primary text-white">
                                <td colspan="14">
                                    {{ __('Pay Period(s):') }}
                                    @foreach ($filter_data['pay_period_ids'] as $index => $pay_period_id)
                                        {{ $pay_period_options[$pay_period_id] ?? '--' }}
                                        @if ($index < count($filter_data['pay_period_ids']) - 1)
                                            , 
                                        @endif
                                    @endforeach
                                </td>
                            </tr>

                            <tr class="tblHeader">
                                <td>{{ __('#') }}</td>
                                <td>{{ __('Date') }}</td>
                                <td>{{ __('Source') }}</td>
                                <td>{{ __('Comment') }}</td>
                                <td>{{ __('Account') }}</td>
                                <td>{{ __('Debit') }}</td>
                                <td>{{ __('Credit') }}</td>
                            </tr>

                            @if (!empty($rows))
                                @foreach ($rows as $index => $row)
                                    @php
                                        $row_class = $index % 2 == 0 ? 'tblDataWhite' : 'tblDataGrey';
                                        $debit_count = count($row['records']['debit'] ?? []);
                                        $credit_count = count($row['records']['credit'] ?? []);
                                        $total_count = count($row['records']['total'] ?? []);
                                        $rowspan = $debit_count + $credit_count + $total_count;
                                    @endphp
                                    <tr class="{{ $row_class }}">
                                        <td rowspan="{{ $rowspan }}">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ getdate_helper('date', $row['transaction_date']) }}
                                        </td>
                                        <td>
                                            {{ $row['source'] ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $row['comment'] }}
                                        </td>
                                        <td colspan="3">
                                            <br>
                                        </td>
                                    </tr>
                                    @if (!empty($row['records']['debit']))
                                        @foreach ($row['records']['debit'] as $record)
                                            <tr class="{{ $row_class }}">
                                                <td colspan="3">
                                                    <br>
                                                </td>
                                                <td>
                                                    {{ $record['account'] }}
                                                </td>
                                                <td align="right">
                                                    {{ $record['amount'] }}
                                                </td>
                                                <td align="right">
                                                    -
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (!empty($row['records']['credit']))
                                        @foreach ($row['records']['credit'] as $record)
                                            <tr class="{{ $row_class }}">
                                                <td colspan="3">
                                                    <br>
                                                </td>
                                                <td>
                                                    {{ $record['account'] }}
                                                </td>
                                                <td align="right">
                                                    -
                                                </td>
                                                <td align="right">
                                                    {{ $record['amount'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (!empty($row['records']['total']))
                                        @foreach ($row['records']['total'] as $record)
                                            <tr class="{{ $row_class }}" style="font-weight: bold; background: {{ $record['total_diff'] != 0 ? 'red' : 'green' }};">
                                                <td align="right" colspan="4">
                                                    {{ __('Total:') }}<br>
                                                </td>
                                                <td align="right">
                                                    {{ __('Difference:') }} {{ $record['total_diff'] }}
                                                </td>
                                                <td align="right">
                                                    {{ $record['total_debits'] }}
                                                </td>
                                                <td align="right">
                                                    {{ $record['total_credits'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @else
                                <tr class="tblDataWhiteNH">
                                    <td colspan="14">
                                        {{ __('No results match your filter criteria.') }}
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td class="bg-primary text-white" colspan="14" align="center">
                                    {{ __('Generated:') }} {{ getdate_helper('date_time', $generated_time) }}
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" name="filter_data" value="{{ json_encode($filter_data) }}">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportReport() {
            // Update the form action to trigger export
            document.forms[0].action = "{{ route('report.employee_nopay_count_report') }}?action=Export";
            document.forms[0].submit();
        }
    </script>
</x-app-modal-layout>