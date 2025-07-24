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
                                href="/payroll/recurring_pay_stub_amendment/add"
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
                            <th>Description</th>
                            <th>Status</th>
                            <th>Frequency</th>
                            <th>Type</th>
                            <th>Functions</th>
                        </thead>
                        @foreach ($recurring_pay_stub_amendments as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }} </td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['description'] }}</td>
                                <td>{{ $row['status'] }}</td>
                                <td>{{ $row['frequency'] }}</td>
                                <td>{{ $row['pay_stub_entry_name'] }}</td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="{{ route('payroll.pay_stub_amendment', ['recurring_ps_amendment_id' => $row['id']]) }}">PS Amendments</a>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('payroll.recurring_pay_stub_amendment.add', ['id' => $row['id']]) }}">Edit</a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/payroll/recurring_pay_stub_amendment/delete/{{ $row['id'] }}', 'PayStub Amendment', this)">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    {{-- --------------------------------------------------------------------------- --}}

                </div>

            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
