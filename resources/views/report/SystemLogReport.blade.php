<x-app-layout :title="$title">
    <style>
        th, td {
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

        .tblDataWhite {
            background-color: #ffffff;
        }

        .tblDataGrey {
            background-color: #f5f5f5;
        }

        .tblHeader {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .tblPagingLeft {
            text-align: right;
        }
    </style>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>
                    <div>
                        <a href="javascript:exportReport()" class="btn btn-sm btn-outline-primary">
                            <img src="{{ asset('images/excel_icon.gif') }}" alt="{{ __('Export to Excel') }}">
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="get" name="report" action="{{ url()->current() }}" target="_self">
                        @csrf
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <td class="tblPagingLeft" colspan="{{ count($columns) + 1 }}" align="right">
                                        <a href="javascript:exportReport()">
                                            <img src="{{ asset('images/excel_icon.gif') }}" alt="{{ __('Export to Excel') }}">
                                        </a>
                                    </td>
                                </tr>

                                <tr class="tblHeader">
                                    <td colspan="{{ count($columns) + 1 }}">
                                        @if ($filter_data['date_type'] === 'pay_period_ids')
                                            {{ __('Pay Period(s):') }}
                                            @foreach ($filter_data['pay_period_ids'] as $pay_period_id)
                                                {{ $pay_period_options[$pay_period_id] ?? '' }}
                                                @if (!$loop->last), @endif
                                            @endforeach
                                        @else
                                            {{ __('From:') }} {{ getdate_helper('DATE', $filter_data['start_date'] ?? null, true) }}
                                            {{ __('To:') }} {{ getdate_helper('DATE', $filter_data['end_date'] ?? null, true) }}
                                        @endif
                                    </td>
                                </tr>

                                <tr class="tblHeader">
                                    <td>{{ __('#') }}</td>
                                    @foreach ($columns as $column)
                                        <td>{{ $column }}</td>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($rows as $index => $row)
                                    <tr class="{{ $loop->iteration % 2 == 0 ? 'tblDataGrey' : 'tblDataWhite' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        @foreach ($columns as $key => $column)
                                            <td @if ($key === 'date') nowrap @endif>
                                                @if ($key === 'description')
                                                    {{ htmlspecialchars($row[$key] ?? '--') }}
                                                @else
                                                    {{ $row[$key] ?? '--' }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr class="tblDataWhite">
                                        <td colspan="{{ count($columns) + 1 }}">
                                            {{ __('No results match your filter criteria.') }}
                                        </td>
                                    </tr>
                                @endforelse

                                <tr>
                                    <td class="tblHeader" colspan="{{ count($columns) + 1 }}" align="center">
                                        {{ __('Generated:') }} {{ getdate_helper('DATE+TIME', $generated_time) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportReport() {
            document.getElementById('action').value = 'export';
            document.forms['report'].submit();
        }
    </script>
</x-app-layout>