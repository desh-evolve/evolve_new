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
                    
                    @if (isset($data_saved) && $data_saved == true)
                        <div class="alert alert-success material-shadow" role="alert">
                            <strong> Data Saved Successfully! </strong>
                        </div>
                    @endif
                    
                    <form method="POST"
                        action="{{ route('payroll.paystub_account_link.submit') }}">
                        @csrf

                        @if (!$psealf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif

                        <table class="table">
                            <tr>
                                <th>Total Gross</th>
                                <td>
                                    <select 
                                        id="total_gross" 
                                        class="" 
                                        name="data[total_gross]" 
                                    >
                                        @foreach ($data['total_account_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['total_gross']) && $id == $data['total_gross'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Employee Deduction</th>
                                <td>
                                    <select 
                                        id="total_employee_deduction" 
                                        class="" 
                                        name="data[total_employee_deduction]" 
                                    >
                                        @foreach ($data['total_account_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['total_employee_deduction']) && $id == $data['total_employee_deduction'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Employer Deduction</th>
                                <td>
                                    <select 
                                        id="total_employer_deduction" 
                                        class="" 
                                        name="data[total_employer_deduction]" 
                                    >
                                        @foreach ($data['total_account_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['total_employer_deduction']) && $id == $data['total_employer_deduction'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Net Pay</th>
                                <td>
                                    <select 
                                        id="total_net_pay" 
                                        class="" 
                                        name="data[total_net_pay]" 
                                    >
                                        @foreach ($data['total_account_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['total_net_pay']) && $id == $data['total_net_pay'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Regular Time Earnings</th>
                                <td>
                                    <select 
                                        id="regular_time" 
                                        class=""
                                        name="data[regular_time]" 
                                    >
                                        @foreach ($data['earning_account_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['regular_time']) && $id == $data['regular_time'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>


                        </table>

                        <div class="d-flex justify-content-end">
                            <input type="submit" class="btn btn-primary btn-sm" name="action:submit" value="Submit">
                        </div>

                        <input type="hidden" name="data[id]" value="{{$data['id'] ?? ''}}">

                    </form>

                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>