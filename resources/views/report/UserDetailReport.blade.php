<x-app-modal-layout :title="'Input Example'">
    <style>
        .main-content{
            margin-left: 0 !important;
        }
        .page-content{
            padding: 10px !important;
        }
    </style>
    <div class="">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- ---------------------------------------------- --}}

                    <form method="get" action="{{ route('report.user_detail') }}">
                        <table class="table table-bordered">
                            <tr>
                                <td class="tblPagingLeft" colspan="100" align="right">
                                    <br>{{-- <a href="javascript: exportReport()"><i class="ri-file-excel-line" style="font-size=18px"></i></a> --}}
                                </td>
                            </tr>
                            
                            @if (!empty($rows))
                                @foreach ($rows as $row)
                                    <tr class="bg-primary text-white">
                                        <td colspan="2">
                                            {{$row['first_name']}} {{$row['last_name']}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%" valign="top">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td class="fw-bold">
                                                        Employee:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        {{$row['first_name']}} {{$row['last_name']}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">
                                                        City:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        {{$row['city']}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">
                                                        Province:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        {{$row['province']}}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td valign="top">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td class="fw-bold">
                                                        Title:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        {{$row['title'] ?? ''}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">
                                                        Hired Date:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        {{ getdate_helper('date',$row['hire_date'] ) }} ({{$row['hire_date_since']}} ago)
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">
                                                        Termination Date:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        {{ getdate_helper('date', $row['termination_date']) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">
                                                        Birth Date:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        {{ getdate_helper('date', $row['birth_date']) }} ({{$row['birth_date_since']}} yrs old)
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>

                                    {{-- wage history --}}

                                    @if (isset($columns['wage']))
                                        @if (!empty($row['user_wage_rows']))

                                            @foreach ($row['user_wage_rows'] as $user_wage)
                                                @if ($loop->first)
                                                    <tr>
                                                        <td colspan="2">
                                                            <table class="table table-bordered">
                                                                <tr class="bg-primary text-white">
                                                                    <td colspan="3">
                                                                        Wage History
                                                                    </td>
                                                                </tr>
                                                                <tr class="bg-primary text-white">
                                                                    <td>
                                                                        Type
                                                                    </td>
                                                                    <td>
                                                                        Wage
                                                                    </td>
                                                                    <td>
                                                                        Effective Date
                                                                    </td>
                                                                </tr>
                                                @endif
                                
                                                                <tr class="">
                                                                    <td>
                                                                        {{$user_wage['type']}}
                                                                    </td>
                                                                    <td>
                                                                        {{$user_wage['currency_symbol']}}{{$user_wage['wage']}}
                                                                    </td>
                                                                    <td>
                                                                        {{ getdate_helper('date', $user_wage['effective_date']) }} ({{$user_wage['effective_date_since']}} ago)
                                                                    </td>
                                                                </tr>
                        
                                                @if ($loop->last)
                                                            </table>
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                        @else
                                            <tr class="bg-primary text-white">
                                                <td colspan="2">
                                                    No Wage History
                                                </td>
                                            </td>
                                        @endif
                                    @endif
                                    
                                    {{-- Attendance History --}}
                                    @if (isset($columns['attendance']))
                                        @if (!empty($row['user_attendance_rows']))
                                            <tr>
                                                <td colspan="2">
                                                    <table class="table table-bordered">
                                                        <tr class="bg-primary text-white">
                                                            <td colspan="100">
                                                                Attendance History
                                                            </td>
                                                        </tr>
                                                        <tr class="bg-primary text-white">
                                                            <td rowspan="2">
                                                                Name
                                                            </td>
                                                            <td colspan="4">
                                                                Per Day
                                                            </td>
                                                            <td rowspan="100">
                                                                <br>
                                                            </td>
                                                            <td  colspan="4">
                                                                Per Week
                                                            </td>
                                                            <td rowspan="100">
                                                                <br>
                                                            </td>
                                                            <td  colspan="4">
                                                                Per Month
                                                            </td>
                                                        </tr>
                                                        <tr class="bg-primary text-white">
                                                            <td>
                                                                Min
                                                            </td>
                                                            <td>
                                                                Avg
                                                            </td>
                                                            <td>
                                                                Max
                                                            </td>
                                                            <td>
                                                                Days
                                                            </td>
                    
                                                            <td>
                                                                Min
                                                            </td>
                                                            <td>
                                                                Avg
                                                            </td>
                                                            <td>
                                                                Max
                                                            </td>
                                                            <td>
                                                                Weeks
                                                            </td>
                    
                                                            <td>
                                                                Min
                                                            </td>
                                                            <td>
                                                                Avg
                                                            </td>
                                                            <td>
                                                                Max
                                                            </td>
                                                            <td>
                                                                Months
                                                            </td>
                                                        </tr>
                                                        <tr class="">
                                                            <td class="fw-bold">
                                                                Days Worked:
                                                            </td>
                                                            <td colspan="4">
                                                                N/A
                                                            </td>
                                                            <td>
                                                                {{$row['user_attendance_rows']['days_worked']['week']['min']}}
                                                            </td>
                                                            <td>
                                                                {{$row['user_attendance_rows']['days_worked']['week']['avg']}}
                                                            </td>
                                                            <td>
                                                                {{$row['user_attendance_rows']['days_worked']['week']['max']}}
                                                            </td>
                                                            <td>
                                                                --
                                                            </td>
                    
                                                            <td>
                                                                {{$row['user_attendance_rows']['days_worked']['month']['min']}}
                                                            </td>
                                                            <td>
                                                                {{$row['user_attendance_rows']['days_worked']['month']['avg']}}
                                                            </td>
                                                            <td>
                                                                {{$row['user_attendance_rows']['days_worked']['month']['max']}}
                                                            </td>
                                                            <td>
                                                                --
                                                            </td>
                                                        </tr>
                    
                                                        <tr class="">
                                                            <td class="fw-bold">
                                                                Regular Time:
                                                            </td>
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['day']['min'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['day']['avg'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['day']['max'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{ $row['user_attendance_rows']['hours_worked']['regular'][0]['day']['date_units'] ?? 0 }}
                                                            </td>
                    
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['week']['min'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['week']['avg'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['week']['max'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{$row['user_attendance_rows']['hours_worked']['regular'][0]['week']['date_units'] ?? 0}}
                                                            </td>
                    
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['month']['min'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['month']['avg'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{ gettimeunit_helper($row['user_attendance_rows']['hours_worked']['regular'][0]['month']['max'], 0) }}
                                                            </td>
                                                            <td>
                                                                {{ $row['user_attendance_rows']['hours_worked']['regular'][0]['month']['date_units'] ?? 0 }}
                                                            </td>
                                                        </tr>
                    
                                                        @foreach ($row['user_attendance_rows']['hours_worked']['over_time'] as $attendance_over_time)
                                                            <tr class="">
                                                                <td class="fw-bold">
                                                                    {{$attendance_over_time['name']}}:
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['day']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['day']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['day']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_over_time['day']['date_units'] ?? 0}}
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['week']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['week']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['week']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_over_time['week']['date_units'] ?? 0}}
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['month']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['month']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_over_time['month']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_over_time['month']['date_units'] ?? 0}}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                    
                                                        @foreach ($row['user_attendance_rows']['hours_worked']['premium'] as $attendance_premium)
                                                            <tr class="">
                                                                <td class="fw-bold">
                                                                    {{$attendance_premium['name']}}:
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['day']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['day']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['day']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_premium['day']['date_units'] ?? 0}}
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['week']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['week']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['week']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_premium['week']['date_units'] ?? 0}}
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['month']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['month']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['month']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_premium['month']['date_units'] ?? 0}}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                    
                                                        @foreach ($row['user_attendance_rows']['hours_worked']['absence'] as $attendance_absence)
                                                            <tr class="">
                                                                <td class="fw-bold">
                                                                    {{$attendance_absence['name']}}:
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['day']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['day']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['day']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_absence['day']['date_units'] ?? 0}}
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['week']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['week']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['week']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_absence['week']['date_units'] ?? 0}}
                                                                </td>
                        
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['month']['min'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['month']['avg'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{ gettimeunit_helper($attendance_premium['month']['max'], 0) }}
                                                                </td>
                                                                <td>
                                                                    {{$attendance_absence['month']['date_units'] ?? 0}}
                                                                </td>
                                                            </tr>    
                                                        @endforeach
                                                    </table>
                                                </td>
                                            </tr>
                                        @else
                                            <tr class="bg-primary text-white">
                                                <td colspan="2">
                                                    No Attendance History
                                                </td>
                                            </td>
                                        @endif
                                    @endif

                                    {{-- Exception History --}}
                                    @if (isset($columns['exception']))
                                        @if (!empty($row['user_exception_rows']))
                                            <tr>
                                                <td colspan="2">
                                                    <table class="table table-bordered">
                                                        @foreach ($row['user_exception_rows'] as $exception)
                                                            @if ($loop->first)
                                                                <tr class="bg-primary text-white">
                                                                    <td colspan="100">
                                                                        Exception History
                                                                    </td>
                                                                </tr>
                                                                <tr class="bg-primary text-white">
                                                                    <td rowspan="2">
                                                                        Exception
                                                                    </td>
                                                                    <td rowspan="2">
                                                                        Total
                                                                    </td>
                                                                    <td rowspan="100">
                                                                        <br>
                                                                    </td>
                                                                    <td colspan="3">
                                                                        Per Week<br>
                                                                    </td>
                                                                    <td rowspan="100">
                                                                        <br>
                                                                    </td>
                                                                    <td colspan="3">
                                                                        Per Month<br>
                                                                    </td>
                                                                    <td rowspan="100">
                                                                        <br>
                                                                    </td>
                                                                    <td colspan="7">
                                                                        Day of Week<br>
                                                                    </td>
                                                                </tr>
                    
                                                                <tr class="bg-primary text-white">
                                                                    <td>
                                                                        Min
                                                                    </td>
                                                                    <td>
                                                                        Avg
                                                                    </td>
                                                                    <td>
                                                                        Max
                                                                    </td>
                    
                                                                    <td>
                                                                        Min
                                                                    </td>
                                                                    <td>
                                                                        Avg
                                                                    </td>
                                                                    <td>
                                                                        Max
                                                                    </td>
                    
                                                                    <td>
                                                                        Sun
                                                                    </td>
                                                                    <td>
                                                                        Mon
                                                                    </td>
                                                                    <td>
                                                                        Tue
                                                                    </td>
                                                                    <td>
                                                                        Wed
                                                                    </td>
                                                                    <td>
                                                                        Thu
                                                                    </td>
                                                                    <td>
                                                                        Fri
                                                                    </td>
                                                                    <td>
                                                                        Sat
                                                                    </td>
                                                                </tr>
                                                            @endif

                                                                <tr class="">
                                                                    <td class="fw-bold">
                                                                        {{$exception['week']['name']}}
                                                                    </td>
                                                                    <td>
                                                                        {{$exception['week']['total']}}
                                                                    </td>
                            
                                                                    <td>
                                                                        {{$exception['week']['min']}}
                                                                    </td>
                                                                    <td>
                                                                        {{$exception['week']['avg']}}
                                                                    </td>
                                                                    <td>
                                                                        {{$exception['week']['max']}}
                                                                    </td>
                            
                                                                    <td>
                                                                        {{$exception['month']['min']}}
                                                                    </td>
                                                                    <td>
                                                                        {{$exception['month']['avg']}}
                                                                    </td>
                                                                    <td>
                                                                        {{$exception['month']['max']}}
                                                                    </td>
                            
                                                                    <td id="{{$exception['dow']['max']['dow'] == 0 ? 'red' : ''}}">
                                                                        {{$exception['dow'][0] ?? 0}}
                                                                    </td>
                                                                    <td id="{{($exception[dow]['min']['dow'] == 1) ? 'green' : (($exception['dow']['max']['dow'] == 1) ? 'red' : '') }}" >
                                                                        {{$exception['dow'][1] ?? 0}}
                                                                    </td>
                                                                    <td id="{{($exception[dow]['min']['dow'] == 2) ? 'green' : (($exception['dow']['max']['dow'] == 2) ? 'red' : '') }}" >
                                                                        {{$exception['dow'][2] ?? 0}}
                                                                    </td>
                                                                    <td id="{{($exception[dow]['min']['dow'] == 3) ? 'green' : (($exception['dow']['max']['dow'] == 3) ? 'red' : '') }}" >
                                                                        {{$exception['dow'][3] ?? 0}}
                                                                    </td>
                                                                    <td id="{{($exception[dow]['min']['dow'] == 4) ? 'green' : (($exception['dow']['max']['dow'] == 4) ? 'red' : '') }}" >
                                                                        {{$exception['dow'][4] ?? 0}}
                                                                    </td>
                                                                    <td id="{{($exception[dow]['min']['dow'] == 5) ? 'green' : (($exception['dow']['max']['dow'] == 5) ? 'red' : '') }}" >
                                                                        {{$exception['dow'][5] ?? 0}}
                                                                    </td>
                                                                    <td id="{{($exception[dow]['min']['dow'] == 6) ? 'green' : (($exception['dow']['max']['dow'] == 6) ? 'red' : '') }}" >
                                                                        {{$exception['dow'][6] ?? 0}}
                                                                    </td>
                                                                </tr>
                                                        @endforeach
                                                    </table>
                                                </td>
                                            </tr>    
                                        @else
                                            <tr class="bg-primary text-white">
                                                <td colspan="100">
                                                    No Exception History
                                                </td>
                                            </tr>
                                        @endif
                                    @endif
                                    <tr>
                                        <td>
                                            <br>
                                            <br>
                                        </td>
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
                                    Generated: {{ getdate_helper('date_time', $generated_time) }}
                                </td>
                            </tr>
                        </table>
                    </form>

                    {{-- ---------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-modal-layout>