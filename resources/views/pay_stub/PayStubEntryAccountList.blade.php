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
                                href="/payroll/paystub_accounts/add"
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
                            <th>Type</th>
                            <th>Name</th>
                            <th>Order</th>
                            <th>Debit Account</th>
                            <th>Credit Account</th>
                            <th>Functions</th>
                        </thead>
                        @foreach ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }} </td>
                                <td>{{ $row['type'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['ps_order'] }}</td>
                                <td>{{ !empty($row['debit_account']) ? $row['debit_account'] : '--' }}</td>
                                <td>{{ !empty($row['credit_account']) ? $row['credit_account'] : '--' }}</td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('payroll.paystub_accounts.add', ['id' => $row['id']]) }}">Edit</a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/payroll/paystub_accounts/delete/{{ $row['id'] }}', 'PayStub Entry Account', this)">Delete</button>
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
