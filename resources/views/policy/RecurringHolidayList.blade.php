<x-app-layout :title="'Input Example'">

    <div class="row">
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
                                href="/policy/recurring_holidays/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Recurring Holiday <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <table class="table table-striped table-bordered">
                        <thead class="bg-primary text-white">
                            <th>#</th>
                            <th>Name </th>
                            <th>Next Date</th>
                            <th>Functions</th>
                        </thead>
                        @foreach ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }} </td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['next_date'] }}</td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('policy.recurring_holidays.add', ['id' => $row['id']]) }}">Edit</a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/policy/recurring_holidays/delete/{{ $policy['id'] }}', 'Absence Policy', this)">Delete</button>
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
