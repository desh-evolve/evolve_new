<x-app-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>
    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="get" action="/report/timesheet_summary">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <td class="tblPagingLeft" colspan="100" align="right">
                                    <a href="javascript: exportReport()"><img src="{$IMAGES_URL}/excel_icon.gif"></a>
                                </td>
                            </tr>
            
                            <tr class="bg-primary text-white">
                                <th colspan="100">
                                    @if ($filter_data['date_type'] == 'pay_period_ids')
                                        Pay Period(s):
                                        @foreach ($filter_data['pay_period_ids'] as $pay_period_id)
                                            {{$pay_period_options[$pay_period_id]}}
                                            @if ($loop->first && $loop->last), @endif
                                        @endforeach
                                    @else
                                        From: {{getdate_helper('date', $filter_data['start_date'])}} To: {{getdate_helper('date', $filter_data['end_date'])}}
                                    @endif
                                </th>
                            <tr>
            
                            <tr class="bg-primary text-white">
                                <td>
                                    #
                                </td>
                                @foreach ($columns as $column)
                                    <td>
                                        {{$column}}
                                    </td>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @if (!empty($rows))
                                @foreach ($rows as $k => $row)
                                    <tr class="" {if $smarty.foreach.rows.last}style="font-weight: bold;"{/if}>
                                        <td>
                                            @if ($loop->last)
                                                <br>
                                            @else
                                                {{ $k + 1 }}
                                            @endif
                                        </td>
                                        @foreach ($columns as $key => $column)
                                            <td>
                                                @if ($key == 'actual_time_diff_wage')
                                                    @if ($row[$key] != '' )
                                                        {{$row[$key] ?? "--"}}
                                                    @else
                                                        {{$row[$key] ?? "--"}}
                                                    @endif
                                                @else
                                                    {{$row[$key] ?? "--"}}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @else
                                <tr class="tblDataWhiteNH">
                                    <td colspan="100">
                                        No results match your filter criteria.
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="bg-primary text-white" colspan="100" align="center">
                                    Generated: {{getdate_helper('date_time', $generated_time)}}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>