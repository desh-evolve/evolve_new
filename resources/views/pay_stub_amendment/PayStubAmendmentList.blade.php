<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/payroll/pay_stub_amendment/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1">
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
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Account</th>
                            <th>Effective Date</th>
                            <th>Amount</th>
                            <th>Rate</th>
                            <th>Units</th>
                            <th>Description</th>
                            <th>YTD</th>
                            <th>Functions</th>
                        </thead>
                        <tbody>
                            @if(count($pay_stub_amendments) > 0)
                                @foreach ($pay_stub_amendments as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }} </td>
                                        <td>{{ $row['first_name'] }}</td>
                                        <td>{{ $row['last_name'] }}</td>
                                        <td>{{ $row['status'] }}</td>
                                        <td>{{ $row['type'] }}</td>
                                        <td>{{ $row['pay_stub_account_name'] }}</td>
                                        <td>{{ $row['effective_date'] }}</td>
                                        <td>{{ $row['amount'] }}</td>
                                        <td>{{ $row['rate'] }}</td>
                                        <td>{{ $row['units'] }}</td>
                                        <td>{{ $row['description'] }}</td>
                                        <td>{{ $row['ytd_adjustment'] }}</td>
                                        <td>
                                            <a class="btn btn-secondary btn-sm" href="{{ route('payroll.pay_stub_amendment.add', ['id' => $row['id']]) }}">Edit</a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/payroll/pay_stub_amendment/delete/{{ $row['id'] }}', 'PayStub Amendment', this)">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="13" class="text-center py-4">
                                        <div class="alert alert-info mb-0" role="alert">
                                            <i class="ri-information-line me-2"></i> No pay stub amendments found. Click the "Add" button to create a new one.
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>


                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
