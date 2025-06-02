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

                    <table class="table table-bordered">
            
                        {{-- search functions removed. add later if needed --}}
            
                        <form method="get" action="/punch/user_exception">
                            <tr class="tblHeader">
                                <th>
                                    #
                                </th>
            
                                @foreach ($columns as $column_id => $column)
                                    <th>
                                        {{ $column }}
                                    </th>
                                @endforeach
            
                                <th>
                                    Functions
                                </th>
                                <th>
                                    <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                </th>
                            </tr>
                            @if (!empty($rows))
                                @foreach ($rows as $i => $row)
                                    <tr class="">
                                        <td>
                                            {{ $i++ }}
                                        </td>
                
                                        @foreach ($columns as $key => $column)
                                            <td style="background-color: {{ $key == 'severity' ? $row['exception_background_color'] : '' }}">
                                                @if ($key == 'exception_policy_type_id')
                                                    <span color="{{$row['exception_color']}}">
                                                        <b>{{$row[$key] ?? "--"}}</b>
                                                    </span>
                                                @else
                                                    @if ($key == 'severity')
                                                        <b>
                                                    @endif
                                                    {{$row[$key] ?? "--" }}
                                                    @if ($key == 'severity')
                                                        </b>
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach

                                        <td>
                                            @if ($permission->Check('punch','view') OR $permission->Check('punch','view_own'))
                                                [ <a href="/attendance/timesheet?filter_data[user_id]={{$row['user_id']}}&filter_data[date]={{$row['date_stamp_epoch']}}">View</a> ]
                                            @endif
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="ids[]" value="{{$request['id']}}">
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="tblHeader" colspan="{{$total_columns}}" align="center">
                                        No Exceptions Found
                                    </td>
                                </tr>
                            @endif
                        </form>
                    </table>
        
                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>