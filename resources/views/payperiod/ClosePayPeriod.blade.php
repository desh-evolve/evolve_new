<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __($title) }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            
            <table class="table table-striped">
                <tr>
                    <td colspan="10">
                        <br>
                    </td>
                </tr>
                @foreach ($pay_periods as $index => $pay_period)
                    @if ($index === 0)
                        <tr class="bg-primary text-white">
                            <td colspan="8">
                                {{ __('Step 1: Confirm all requests are authorized, and exceptions are handled.') }}
                            </td>
                        </tr>

                        <tr class="bg-primary text-white">
                            <td>{{ __('Name') }}</td>
                            <td>{{ __('Type') }}</td>
                            <td colspan="2">{{ __('Pending Requests') }}</td>
                            <td colspan="1">
                                {{ __('Exceptions') }}<br>
                                {{ __('Low') }} / {{ __('Medium') }} / {{ __('High') }} / {{ __('Critical') }}
                            </td>
                            <td colspan="1">
                                {{ __('Verified TimeSheets') }}<br>
                                {{ __('Pending') }} / {{ __('Verified') }} / {{ __('Total') }}
                            </td>
                            <td colspan="2">{{ __('Functions') }}</td>
                        </tr>
                    @endif

                    @php
                        $row_class = $pay_period['deleted'] ? 'tblDataDeleted' : ($index % 2 == 0 ? 'bg-white' : 'bg-dark-subtle');
                    @endphp

                    <tr class="{{ $row_class }}">
                        <td>{{ $pay_period['name'] }}</td>
                        <td>{{ $pay_period['type'] }}</td>
                        <td colspan="2">
                            <table width="100%" align="center">
                                <tr>
                                    <td width="20" style="background: {{ $pay_period['pending_requests'] > 0 ? 'red' : 'green' }};"><br></td>
                                    <td align="center"><b>{{ $pay_period['pending_requests'] }}</b></td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="1">
                            <table width="100%" align="center">
                                <tr>
                                    <td width="20" style="background: {{ $pay_period['critical_severity_exceptions'] > 0 ? 'red' : 'green' }};"><br></td>
                                    <td align="center">
                                        <b>
                                            {{ $pay_period['low_severity_exceptions'] }} /
                                            <font color="blue">{{ $pay_period['med_severity_exceptions'] }}</font> /
                                            <font color="orange">{{ $pay_period['high_severity_exceptions'] }}</font> /
                                            <font color="red">{{ $pay_period['critical_severity_exceptions'] }}</font>
                                        </b>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="1">
                            <table width="100%" align="center">
                                <tr>
                                    <td width="20" style="background: 
                                        {{ $pay_period['verified_time_sheets'] >= $pay_period['total_worked_users'] ? 'green' : 
                                        (($pay_period['verified_time_sheets'] + $pay_period['pending_time_sheets']) >= $pay_period['total_worked_users'] ? 'yellow' : 'red') }};"><br>
                                    </td>
                                    <td align="center">
                                        <b>{{ $pay_period['pending_time_sheets'] }} / {{ $pay_period['verified_time_sheets'] }} / {{ $pay_period['total_worked_users'] }}</b>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="2">
                            <span style="white-space: nowrap;">[ <a href="../punch/UserExceptionList.php">{{ __('Exceptions') }}</a> ]</span>
                            <span style="white-space: nowrap;">[ <a href="../authorization/AuthorizationList.php">{{ __('Requests') }}</a> ]</span>
                            <span style="white-space: nowrap;">[ <a href="../report/TimesheetSummary.php">{{ __('Verifications') }}</a> ]</span>
                        </td>
                    </tr>
                @endforeach

                <form method="get" action="{{ request()->server('SCRIPT_NAME') }}">
                    <tr>
                        <td class="tblPagingLeft" colspan="10" align="right">
                            <br>
                        </td>
                    </tr>
                
                    @if (!$open_pay_periods)
                        <tr class="bg-primary text-white">
                            <td colspan="8">
                                @if ($total_pay_periods == 0)
                                    {{ __('There are no Pay Periods past their end date yet.') }}
                                @else
                                    {{ __('All pay periods are currently closed.') }}
                                @endif
                            </td>
                        </tr>
                    @else
                        @foreach ($pay_periods as $index => $pay_period)
                            @if ($loop->first)
                                <tr class="bg-primary text-white">
                                    <td colspan="8">
                                        {{ __('Step 2: Lock Pay Period to prevent changes.') }}
                                    </td>
                                </tr>
                
                                <tr class="bg-primary text-white">
                                    <td>{{ __('Name') }}</td>
                                    <td>{{ __('Type') }}</td>
                                    <td>{{ __('Status') }}</td>
                                    <td>{{ __('Start') }}</td>
                                    <td>{{ __('End') }}</td>
                                    <td>{{ __('Transaction') }}</td>
                                    <td>{{ __('Functions') }}</td>
                                    <td>
                                        <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
                                    </td>
                                </tr>
                            @endif
                            
                            @php
                                $row_class = ($index % 2 == 0) ? 'bg-white' : 'bg-dark-subtle';
                                if ($pay_period['deleted']) {
                                    $row_class = 'tblDataDeleted';
                                }
                            @endphp
                
                            <tr class="{{ $row_class }}">
                                <td>{{ $pay_period['name'] }}</td>
                                <td>{{ $pay_period['type'] }}</td>
                                <td>{{ $pay_period['status'] }}</td>
                                <td>{{ $pay_period['start_date'] }}</td>
                                <td>{{ $pay_period['end_date'] }}</td>
                                <td>{{ $pay_period['transaction_date'] }}</td>
                                <td>
                                    @if ($pay_period['id'])
                                        [ <a href="ViewPayPeriod.php">{{ __('View') }}</a> ]
                                    @endif
                                </td>
                                <td>
                                    <input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{{ $pay_period['id'] }}" checked>
                                </td>
                            </tr>
                        @endforeach
                
                        <tr class="bg-primary text-white">
                            <td colspan="6">
                                <br>
                            </td>
                            <td colspan="2" align="center">
                                <input type="button" name="action:lock" value="{{ __('Lock') }}">
                                <input type="button" name="action:unlock" value="{{ __('UnLock') }}">
                            </td>
                        </tr>
                    @endif
                </form>

                <tr>
                    <td colspan="10">
                        <br>
                    </td>
                </tr>


                @foreach ($pay_periods as $index => $pay_period)
                    @if ($index === 0)
                        <tr class="bg-primary text-white">
                            <td colspan="8">
                                {{ __('Step 3: Submit all Pay Stub Amendments.') }}
                            </td>
                        </tr>
                
                        <tr class="bg-primary text-white">
                            <td>{{ __('Name') }}</td>
                            <td>{{ __('Type') }}</td>
                            <td colspan="4">{{ __('Pay Stub Amendments') }}</td>
                            <td colspan="2">{{ __('Functions') }}</td>
                        </tr>
                    @endif
                
                    @php
                        $row_class = $pay_period['deleted'] ? 'tblDataDeleted' : ($index % 2 === 0 ? 'bg-white' : 'bg-dark-subtle');
                    @endphp
                
                    <tr class="{{ $row_class }}">
                        <td>{{ $pay_period['name'] }}</td>
                        <td>{{ $pay_period['type'] }}</td>
                        <td colspan="4">{{ $pay_period['total_ps_amendments'] }}</td>
                        <td colspan="2">
                            [ <a href="../pay_stub_amendment/PayStubAmendmentList.php">{{ __('View') }}</a> ]
                        </td>
                    </tr>
                @endforeach
            

                <tr>
                    <td colspan="10">
                        <br>
                    </td>
                </tr>


                <form method="get" action="{{ request()->server('SCRIPT_NAME') }}">
                    @foreach ($pay_periods as $index => $pay_period)
                        @if ($index === 0)
                            <tr class="bg-primary text-white">
                                <td colspan="8">
                                    {{ __('Step 4: Generate and Review Pay Stubs.') }}
                                </td>
                            </tr>
                
                            <tr class="bg-primary text-white">
                                <td>{{ __('Name') }}</td>
                                <td>{{ __('Type') }}</td>
                                <td colspan="4">{{ __('Pay Stubs') }}</td>
                                <td>{{ __('Functions') }}</td>
                                <td>
                                    <input type="checkbox" class="checkbox" name="select_all" onclick="CheckAll(this)" checked />
                                </td>
                            </tr>
                        @endif
                
                        @php
                            $row_class = $pay_period['deleted'] ? 'tblDataDeleted' : ($index % 2 === 0 ? 'bg-white' : 'bg-dark-subtle');
                        @endphp
                
                        <tr class="{{ $row_class }}">
                            <td>{{ $pay_period['name'] }}</td>
                            <td>{{ $pay_period['type'] }}</td>
                            <td colspan="4">{{ $pay_period['total_pay_stubs'] }}</td>
                            <td>
                                @if ($pay_period['id'])
                                    @php $pay_period_id = $pay_period['id']; @endphp
                                    [ <a href="../pay_stub/PayStubList.php">{{ __('View') }}</a> ]
                                    [ <a href="../report/PayStubSummary.php">{{ __('Summary') }}</a> ]
                                @endif
                            </td>
                            <td>
                                <input type="checkbox" class="checkbox" name="pay_stub_pay_period_ids[]" value="{{ $pay_period['id'] }}" checked>
                            </td>
                        </tr>
                    @endforeach
                
                    <tr class="bg-primary text-white">
                        <td colspan="6"><br></td>
                        <td colspan="2" align="center">
                            <input type="button" name="action:generate_pay_stubs" value="{{ __('Generate Final Pay') }}">
                        </td>
                    </tr>
                </form>
                

                <tr>
                    <td colspan="10">
                        <br>
                    </td>
                </tr>

                <form method="get" action="{{ request()->server('SCRIPT_NAME') }}">
                    @foreach ($pay_periods as $index => $pay_period)
                        @if ($index === 0)
                            <tr class="bg-primary text-white">
                                <td colspan="8">
                                    {{ __('Step 5: Transfer Funds or Write Checks.') }}
                                </td>
                            </tr>
                
                            <tr class="bg-primary text-white">
                                <td>{{ __('Name') }}</td>
                                <td>{{ __('Type') }}</td>
                                <td>{{ __('Status') }}</td>
                                <td>{{ __('Start') }}</td>
                                <td>{{ __('End') }}</td>
                                <td>{{ __('Transaction') }}</td>
                                <td>{{ __('Functions') }}</td>
                                <td>
                                    <input type="checkbox" class="checkbox" name="select_all" onclick="CheckAll(this)" checked />
                                </td>
                            </tr>
                        @endif
                
                        @php
                            $row_class = $pay_period['deleted'] ? 'tblDataDeleted' : ($index % 2 === 0 ? 'bg-white' : 'bg-dark-subtle');
                        @endphp
                
                        <tr class="{{ $row_class }}">
                            <td>{{ $pay_period['name'] }}</td>
                            <td>{{ $pay_period['type'] }}</td>
                            <td>{{ $pay_period['status'] }}</td>
                            <td>{{ $pay_period['start_date'] }}</td>
                            <td>{{ $pay_period['end_date'] }}</td>
                            <td>{{ $pay_period['transaction_date'] }}</td>
                            <td>
                                @if ($pay_period['id'])
                                    @php $pay_period_id = $pay_period['id']; @endphp
                                    [ <a href="../pay_stub/PayStubList.php">{{ __('View') }}</a> ]
                                    [ <a href="../report/PayStubSummary.php">{{ __('Summary') }}</a> ]
                                @endif
                            </td>
                            <td>
                                <input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{{ $pay_period['id'] }}" checked>
                            </td>
                        </tr>
                    @endforeach
                </form>
                
                <tr>
                    <td colspan="10">
                        <br>
                    </td>
                </tr>

                <form method="get" action="{{ request()->server('SCRIPT_NAME') }}">
                    @if (count($pay_periods) > 0)
                        @foreach ($pay_periods as $index => $pay_period)
                            @if ($index === 0)
                                <tr class="bg-primary text-white">
                                    <td colspan="8">
                                        {{ __('Step 6: Close Pay Period.') }}
                                    </td>
                                </tr>
                
                                <tr class="bg-primary text-white">
                                    <td>{{ __('Name') }}</td>
                                    <td>{{ __('Type') }}</td>
                                    <td>{{ __('Status') }}</td>
                                    <td>{{ __('Start') }}</td>
                                    <td>{{ __('End') }}</td>
                                    <td>{{ __('Transaction') }}</td>
                                    <td>{{ __('Functions') }}</td>
                                    <td>
                                        <input type="checkbox" class="checkbox" name="select_all" onclick="CheckAll(this)" checked />
                                    </td>
                                </tr>
                            @endif
                
                            @php
                                $row_class = $pay_period['deleted'] ? 'tblDataDeleted' : ($index % 2 === 0 ? 'bg-white' : 'bg-dark-subtle');
                            @endphp
                
                            <tr class="{{ $row_class }}">
                                <td>{{ $pay_period['name'] }}</td>
                                <td>{{ $pay_period['type'] }}</td>
                                <td>{{ $pay_period['status'] }}</td>
                                <td>{{ $pay_period['start_date'] }}</td>
                                <td>{{ $pay_period['end_date'] }}</td>
                                <td>{{ $pay_period['transaction_date'] }}</td>
                                <td>
                                    @if ($pay_period['id'])
                                        [ <a href="ViewPayPeriod.php">{{ __('View') }}</a> ]
                                    @endif
                                </td>
                                <td>
                                    <input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{{ $pay_period['id'] }}" checked>
                                </td>
                            </tr>
                        @endforeach
                
                        <tr class="bg-primary text-white">
                            <td colspan="6"><br></td>
                            <td colspan="2" align="center">
                                <input type="button" name="action:close" value="{{ __('Close') }}">
                            </td>
                        </tr>
                    @endif
                </form>
                

            </table>
            
        </div>
    </div>

    <script>
    //check here
    // if not working here just use php
    $(document).ready(function () {
        // Listen for the action button click
        $(document).on('click', 'input[name^="action:"]', function () {
            // Get the action type from the button's name
            let actionType = $(this).attr('name').split(':')[1] || ''; // Extract action (lock or unlock)

            // Show confirmation dialog before proceeding
            let confirmationMessage = '';

            // Determine confirmation message based on action
            if (actionType === 'lock') {
                confirmationMessage = 'Are you sure you want to lock the pay periods?';
            } else if (actionType === 'unlock') {
                confirmationMessage = 'Are you sure you want to unlock the pay periods?';
            } else {
                confirmationMessage = 'Are you sure you want to take this action?';
            }

            // Ask for user confirmation
            if (confirm(confirmationMessage)) {
                // If confirmed, proceed with fetch request
                fetch('/payroll_action', {
                    method: 'POST', // POST method
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // CSRF token
                    },
                    body: JSON.stringify({
                        action: actionType, // action type (lock/unlock)
                        pay_period_ids: [1, 2, 3] // Example pay period IDs, replace with actual
                    })
                })
                .then(response => response.json()) // Parse JSON response
                .then(data => {
                    // Handle the success response from the PHP function
                    alert('Action successfully completed');
                    console.log(data); // You can check the response from the server here
                })
                .catch(error => {
                    // Handle any errors during the fetch request
                    alert('An error occurred: ' + error);
                    console.error(error);
                });
            } else {
                // If user cancels the action
                console.log('Action was canceled by the user');
            }
        });
    });

    </script>
</x-app-layout>