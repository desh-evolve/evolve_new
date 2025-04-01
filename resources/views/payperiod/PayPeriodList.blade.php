<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="/payroll/pay_periods/add/{{$id}}"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <table class="table table-striped table-bordered">
                        <thead class="bg-primary text-white">
                            <th>#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Transaction</th>
                            <th>Functions</th>
                        </thead>
                        @foreach ($pay_periods as $index => $pay_period)
                            <tr>
                                <td>{{ $index + 1 }} </td>
                                <td>{{ $pay_period['name'] }}</td>
                                <td>{{ $pay_period['type'] }}</td>
                                <td>{{ $pay_period['status'] }}</td>
                                <td>{{ $pay_period['start_date'] }}</td>
                                <td>{{ $pay_period['end_date'] }}</td>
                                <td>{{ $pay_period['transaction_date'] }}</td>
                                <td>
                                    @if (!empty($pay_period['id']))    
                                        <a class="btn btn-primary btn-sm" href="{{ route('payroll.pay_periods.view', $pay_period['id']) }}">View</a>
                                        <a class="btn btn-secondary btn-sm" href="{{ route('payroll.pay_periods.add', [ $id, $pay_period['id']]) }}">Edit</a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/payroll/pay_periods/delete/{{ $pay_period['id'] }}', 'Pay Period Schedule', this)">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
