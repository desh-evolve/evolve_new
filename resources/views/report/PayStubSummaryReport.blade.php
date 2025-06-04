<x-app-modal-layout :title="'Pay Stub Summary Report'">
    <style>
        .main-content {
            margin-left: 0 !important;
        }

        .page-content {
            padding: 10px !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }

        .tblDataWhite {
            background-color: #ffffff;
        }

        .tblDataGrey {
            background-color: #f8f9fa;
        }

        .tblHeader {
            background-color: #007bff;
            color: #ffffff;
            font-weight: bold;
        }

        .tblPagingLeft {
            text-align: right;
            padding: 10px;
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
                    <form method="get" action="{{ request()->url() }}">
                        <table class="table table-bordered">
                            <tr>
                                <td class="tblPagingLeft" colspan="100" align="right">
                                    <a href="javascript:exportReport()"><i class="ri-file-excel-line" style="font-size:18px"></i></a>
                                </td>
                            </tr>

                            <tr class="tblHeader">
                                <td colspan="100">
                                    @if ($filter_data['date_type'] == 'pay_period_ids')
                                        {{ __('Pay Period(s):') }}
                                        @foreach ($filter_data['pay_period_ids'] as $index => $pay_period_id)
                                            {{ $pay_period_options[$pay_period_id] ?? '--' }}
                                            @if ($index === 0 && count($filter_data['pay_period_ids']) > 1), @endif
                                        @endforeach
                                    @else
                                        {{ __('From:') }} {{ getdate_helper('date', $filter_data['transaction_start_date'] ?? now()->timestamp) }}
                                        {{ __('To:') }} {{ getdate_helper('date', $filter_data['transaction_end_date'] ?? now()->timestamp) }}
                                    @endif
                                </td>
                            </tr>

                            <tr class="tblHeader">
                                <td>{{ __('#') }}</td>
                                @foreach ($columns as $column_key => $column_name)
                                    <td>{{ __($column_name) }}</td>
                                @endforeach
                            </tr>

                            @if (!empty($rows))
                                @foreach ($rows as $index => $row)
                                    <tr class="{{ $index % 2 == 0 ? 'tblDataWhite' : 'tblDataGrey' }}" @if ($loop->last) style="font-weight: bold;" @endif>
                                        <td>
                                            @if ($loop->last)
                                                <br>
                                            @else
                                                {{ $loop->iteration }}
                                            @endif
                                        </td>
                                        @foreach ($columns as $column_key => $column_name)
                                            <td>
                                                {{ $row[$column_key] ?? '--' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @else
                                <tr class="tblDataWhiteNH">
                                    <td colspan="{{ count($columns) + 1 }}">
                                        {{ __('No results match your filter criteria.') }}
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td class="tblHeader" colspan="{{ count($columns) + 1 }}" align="center">
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
</x-app-modal-layout>