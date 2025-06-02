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

                        <tr>
                            <td colspan="10">
                                <br>
                            </td>
                        </tr>
                        @foreach ($pay_periods as $pay_period)
                            @if ($loop->first)
                                <tr class="bg-primary text-white">
                                    <td colspan="8">
                                        Step 1: Confirm all requests are authorized, and exceptions are handled.
                                    </td>
                                </tr>
    
                                <tr class="bg-primary text-white">
                                    <td>
                                        Name
                                    </td>
                                    <td>
                                        Type
                                    </td>
                                    <td colspan="2">
                                        Pending Requests
                                    </td>
                                    <td colspan="1">
                                        Exceptions<br>
                                        Low / Medium / High / Critical
                                    </td>
                                    <td colspan="1">
                                        Verified TimeSheets<br>
                                        Pending / Verified / Total
                                    </td>
                                    <td colspan="2">
                                        Functions
                                    </td>
                                </tr>
                            @endif
                        
                            <tr class="">
                                <td>
                                    {{$pay_period['name']}}
                                </td>
                                <td>
                                    {{$pay_period['type']}}
                                </td>
                                <td colspan="2">
                                    <table width="100%" align="center" >
                                        <tr>
                                            <td width="20" style="background: {{ $pay_period['pending_requests'] > 0 ? 'red' : 'green' }};">
                                                <br>
                                            </td>
                                            <td align="center">
                                                <b>
                                                {{$pay_period['pending_requests']}}
                                                </b>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
    
                                <td colspan="1">
                                    <table width="100%" align="center" >
                                        <tr>
                                            <td width="20" style="background: {{ $pay_period['critical_severity_exceptions'] > 0 ? 'red' : 'green' }};">
                                                <br>
                                            </td>
                                            <td align="center">
                                                <b>
                                                {{$pay_period['low_severity_exceptions']}}
                                                / <span color="blue">{{$pay_period['med_severity_exceptions']}}</span>
                                                / <span color="orange">{{$pay_period['high_severity_exceptions']}}</span>
                                                / <span color="red">{{$pay_period['critical_severity_exceptions']}}</span>
                                                </b>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
    
                                <td colspan="1">
                                    <table width="100%" align="center" >
                                        <tr>
                                            <td width="20" style="background: {{ $pay_period['verified_time_sheets'] >= $pay_period['total_worked_users'] ? 'green' : (($pay_period['verified_time_sheets'] + $pay_period['pending_time_sheets']) >= $pay_period['total_worked_users'] ? 'yellow' : 'red') }};">
                                                <br>
                                            </td>
                                            <td align="center">
                                                <b>
                                                {{$pay_period['pending_time_sheets']}}
                                                / {{$pay_period['verified_time_sheets']}}
                                                / {{$pay_period['total_worked_users']}}
                                                </b>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
    
                                <td colspan="2">
                                    <span style="white-space: nowrap;">[ <a href="/punch/user_exception">Exceptions</a> ]</span>
                                    <span style="white-space: nowrap;">[ <a href="/authorization/authorization_list">Requests</a> ]</span>
                                    <span style="white-space: nowrap;">[ <a href="../report/TimesheetSummary.php" values="filter_data[pay_period_ids][0]=${{$pay_period['id']}},filter_data[columns][99]=verified_time_sheet,filter_data[primary_sort]=verified_time_sheet">Verifications</a> ]</span>
                                </td>
                            </tr>
                        @endforeach
    
                        <form method="get" action="/payroll/payroll_processing">
                            <tr>
                                <td class="tblPagingLeft" colspan="10" align="right">
                                    <br>
                                </td>
                            </tr>
            
                            @if ($open_pay_periods == FALSE)
                                <tr class="bg-primary text-white">
                                    <td colspan="8">
                                        @if ($total_pay_periods == 0)
                                            There are no Pay Periods past their end date yet.
                                        @else
                                            All pay periods are currently closed.
                                        @endif
                                    </td>
                                </tr>
                            @else
                                @foreach ($pay_periods as $pay_period)
                                    @if ($loop->first)
                                        <tr class="bg-primary text-white">
                                            <td colspan="8">
                                                Step 2: Lock Pay Period to prevent changes.
                                            </td>
                                        </tr>
                                        <tr class="bg-primary text-white">
                                            <td>
                                                Name
                                            </td>
                                            <td>
                                                Type
                                            </td>
                                            <td>
                                                Status
                                            </td>
                                            <td>
                                                Start
                                            </td>
                                            <td>
                                                End
                                            </td>
                                            <td>
                                                Transaction
                                            </td>
                                            <td>
                                                Functions
                                            </td>
                                            <td>
                                                <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="">
                                        <td>
                                            {{$pay_period['name']}}
                                        </td>
                                        <td>
                                            {{$pay_period['type']}}
                                        </td>
                                        <td>
                                            {{$pay_period['status']}}
                                        </td>
                                        <td>
                                            {{$pay_period['start_date']}}
                                        </td>
                                        <td>
                                            {{$pay_period['end_date']}}
                                        </td>
                                        <td>
                                            {{$pay_period['transaction_date']}}
                                        </td>
                                        <td>
                                            @if ($pay_period['id'])
                                                [ <a href="ViewPayPeriod.php?pay_period_id={{$pay_period['id']}}">View</a> ]
                                            @endif
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{{$pay_period['id']}}" checked>
                                        </td>
                                    </tr>
                                @endforeach
                                
                                <tr class="bg-primary text-white">
                                    <td colspan="6">
                                        <br>
                                    </td>
                                    <td colspan="2" align="center">
                                        <input type="submit" name="action:lock" value="Lock">
                                        <input type="submit" name="action:unlock" value="UnLock">
                                    </td>
                                </tr>
                                
                                </form>
    
                                <tr>
                                    <td colspan="10">
                                        <br>
                                    </td>
                                </tr>
            
                                @foreach ($pay_periods as $pay_period)
                                    @if ($loop->first)
                                        <tr class="bg-primary text-white">
                                            <td colspan="8">
                                                Step 3: Submit all Pay Stub Amendments.
                                            </td>
                                        </tr>
            
                                        <tr class="bg-primary text-white">
                                            <td>
                                                Name
                                            </td>
                                            <td>
                                                Type
                                            </td>
                                            <td colspan="4">
                                                Pay Stub Amendments
                                            </td>
                                            <td colspan="2">
                                                Functions
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="">
                                        <td>
                                            {{$pay_period['name']}}
                                        </td>
                                        <td>
                                            {{$pay_period['type']}}
                                        </td>
                                        <td colspan="4">
                                            {{$pay_period['total_ps_amendments']}}
                                        </td>
                                        <td colspan="2">
                                            [ <a href="../pay_stub_amendment/PayStubAmendmentList.php">View</a> ]
                                        </td>
                                    </tr>
                                @endforeach
            
                                <tr>
                                    <td colspan="10">
                                        <br>
                                    </td>
                                </tr>

                                <form method="get" action="/payroll/payroll_processing">
                                    @foreach ($pay_periods as $pay_period)
                                        @if ($loop->first)
                                            <tr class="bg-primary text-white">
                                                <td colspan="8">
                                                    Step 4: Generate and Review Pay Stubs.
                                                </td>
                                            </tr>
                
                                            <tr class="bg-primary text-white">
                                                <td>
                                                    Name
                                                </td>
                                                <td>
                                                    Type
                                                </td>
                                                <td colspan="4">
                                                    Pay Stubs
                                                </td>
                                                <td>
                                                    Functions
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
                                                </td>
                                            </tr>
                                        @endif
                                        
                                        <tr class="">
                                            <td>
                                                {{$pay_period['name']}}
                                            </td>
                                            <td>
                                                {{$pay_period['type']}}
                                            </td>
                                            <td colspan="4">
                                                {{$pay_period['total_pay_stubs']}}
                                            </td>
                                            <td>
                                                @if ($pay_period['id'])
                                                    [ <a href="../pay_stub/PayStubList.php?filter_pay_period_id={{$pay_period['id']}}">View</a> ]
                                                    [ <a href="../report/PayStubSummary.php?pay_period_id={{$pay_period['id']}}">Summary</a> ]
                                                @endif
                                            </td>
                                            <td>
                                                <input type="checkbox" class="checkbox" name="pay_stub_pay_period_ids[]" value="{{$pay_period['id']}}" checked>
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    <tr class="bg-primary text-white">
                                        <td colspan="6">
                                            <br>
                                        </td>
                                        <td colspan="2" align="center">
                                                                        
                                            <input type="submit" name="action:generate_pay_stubs" value="Generate Final Pay">
                                        </td>
                                    </tr>
                                </form>
            
                                <tr>
                                    <td colspan="10">
                                        <br>
                                    </td>
                                </tr>

                                <form method="get" action="/payroll/payroll_processing">
                                    @foreach ($pay_periods as $pay_period)
                                        @if ($loop->first)
                                            <tr class="bg-primary text-white">
                                                <td colspan="8">
                                                    Step 5: Transfer Funds or Write Checks.
                                                </td>
                                            </tr>
                
                                            <tr class="bg-primary text-white">
                                                <td>
                                                    Name
                                                </td>
                                                <td>
                                                    Type
                                                </td>
                                                <td>
                                                    Status
                                                </td>
                                                <td>
                                                    Start
                                                </td>
                                                <td>
                                                    End
                                                </td>
                                                <td>
                                                    Transaction
                                                </td>
                                                <td>
                                                    Functions
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="">
                                            <td>
                                                {{$pay_period['name']}}
                                            </td>
                                            <td>
                                                {{$pay_period['type']}}
                                            </td>
                                            <td>
                                                {{$pay_period['status']}}
                                            </td>
                                            <td>
                                                {{$pay_period['start_date']}}
                                            </td>
                                            <td>
                                                {{$pay_period['end_date']}}
                                            </td>
                                            <td>
                                                {{$pay_period['transaction_date']}}
                                            </td>
                                            <td>
                                                @if ($pay_period['id'])
                                                    {assign var="pay_period_id" value=$pay_period.id}
                                                    [ <a href="../pay_stub/PayStubList.php?filter_pay_period_id={{$pay_period['id']}}">View</a> ]
                                                    [ <a href="../report/PayStubSummary.php?pay_period_id={{$pay_period['id']}}">Summary</a> ]
                                                @endif
                                            </td>
                                            <td>
                                                <input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{{$pay_period['id']}}" checked>
                                            </td>
                                        </tr>
                                    @endforeach
                                </form>

                                <tr>
                                    <td colspan="10">
                                        <br>
                                    </td>
                                </tr>

                                <form method="get" action="/payroll/payroll_processing">
                                    @foreach ($pay_periods as $pay_period)
                                        @if ($loop->first)
                                            <tr class="bg-primary text-white">
                                                <td colspan="8">
                                                    Step 6: Close Pay Period.
                                                </td>
                                            </tr>
                
                                            <tr class="bg-primary text-white">
                                                <td>
                                                    Name
                                                </td>
                                                <td>
                                                    Type
                                                </td>
                                                <td>
                                                    Status
                                                </td>
                                                <td>
                                                    Start
                                                </td>
                                                <td>
                                                    End
                                                </td>
                                                <td>
                                                    Transaction
                                                </td>
                                                <td>
                                                    Functions
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
                                                </td>
                                            </tr>
                                        @endif

                                        <tr class="">
                                            <td>
                                                {{$pay_period['name']}}
                                            </td>
                                            <td>
                                                {{$pay_period['type']}}
                                            </td>
                                            <td>
                                                {{$pay_period['status']}}
                                            </td>
                                            <td>
                                                {{$pay_period['start_date']}}
                                            </td>
                                            <td>
                                                {{$pay_period['end_date']}}
                                            </td>
                                            <td>
                                                {{$pay_period['transaction_date']}}
                                            </td>
                                            <td>
                                                @if ($pay_period['id'])
                                                    {assign var="pay_period_id" value=}
                                                    [ <a href="ViewPayPeriod.php?pay_period_id={{$pay_period['id']}}">View</a> ]
                                                @endif
                                            </td>
                                            <td>
                                                <input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{{$pay_period['id']}}" checked>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-primary text-white">
                                        <td colspan="6">
                                            <br>
                                        </td>
                                        <td colspan="2" align="center">
                                            <input type="submit" name="action:close" value="Close">
                                        </td>
                                    </tr>
                                </form> 
                        @endif
                    </table>
                    <br>
        
                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>