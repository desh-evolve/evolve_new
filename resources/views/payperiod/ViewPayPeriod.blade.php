<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('payroll.pay_periods_view.submit', $pay_period_data['id']) : route('payroll.pay_periods_view.submit', $pay_period_data['id']) }}">
                        @csrf

                        @if (!$ppf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif

                        <table class="table">
                            <tr>
                                <th>Status</th>
                                <td>
                                    <select 
                                        id="status_id" 
                                        class="" 
                                        name="pay_period_data[status_id]" 
                                    >
                                        @foreach ($status_options as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($pay_period_data['status_id']) && $id == $pay_period_data['status_id'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Start Date</th>
                                <td>{{$pay_period_data['start_date']}}</td>
                            </tr>
                            <tr>
                                <th>End Date</th>
                                <td>{{$pay_period_data['end_date']}}</td>
                            </tr>
                            <tr>
                                <th>Transaction Date</th>
                                <td>{{$pay_period_data['transaction_date']}}</td>
                            </tr>
                            <tr>
                                <th>Total Punches</th>
                                <td>{{$pay_period_data['total_punches']}}</td>
                            </tr>
                            <tr>
                                <th>Pending Requests</th>
                                <td class="text-white {{ $pay_period_data['pending_requests'] > 0 ? 'bg-red' : 'bg-success'}}">
                                    {{$pay_period_data['pending_requests']}}
                                </td>
                            </tr>
                            <tr>
                                <th>Exceptions</th>
                                <td class="d-flex">
                                    <h6>{{$exceptions['low']}}</h6>
                                    &nbsp;/&nbsp; <h6 class="text-info">{{$exceptions['med']}}</h6>
                                    &nbsp;/&nbsp; <h6 class="text-warning">{{$exceptions['high']}}</h6>
                                    &nbsp;/&nbsp; <h6 class="text-danger">{{$exceptions['critical']}}</h6>
                                </td>
                            </tr>

                            @if ($pay_period_data['status_id'] != 20)
                                <tr>
                                    <th>Action</th>
                                    <td>                  
                                        <a 
                                            href="/payroll/pay_periods_view/generate_paystubs/{{$pay_period_data['id']}}" 
                                            class="btn btn-light btn-sm" 
                                        >Generate Final Pay</a>
                                        <a 
                                            href="/payroll/pay_periods_view/import/{{$pay_period_data['id']}}" 
                                            class="btn btn-light btn-sm" 
                                            onclick="return confirm('This will import employee attendance data from other pay periods into this pay period. Are you sure you want to continue?')"
                                        >Import Data</a>

                                        <a 
                                            href="/payroll/pay_periods_view/delete_data/{{$pay_period_data['id']}}" 
                                            class="btn btn-light btn-sm" 
                                            onclick="return confirm('This will delete all attendance data assigned to this pay period. Are you sure you want to continue?')"
                                        >Delete Data</a>

                                    </td>
                                </tr>
                            @endif

                        </table>


                        <div class="d-flex justify-content-end">
                            <input type="submit" class="btn btn-primary btn-sm" name="action:submit" value="Submit" onclick="return (document.getElementById('status_id').value == 20) ? confirm('Once a Pay Period is closed it cannot be re-opened, and Pay Stubs cannot be modified. Are you sure you want to close this Pay Period now?') : true;">

                        </div>
                
                        <input type="hidden" name="pay_period_data[pay_period_id]" value="{{$pay_period_data['id']}}">

                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    
</x-app-layout>
