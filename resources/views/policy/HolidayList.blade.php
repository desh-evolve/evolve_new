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
                                href="{{ route('policy.holidays.add', $holiday_policy_id) }}"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Holiday <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <table class="table table-striped table-bordered">
                        <thead class="bg-primary text-white">
                            <th>#</th>
                            <th>Date </th>
                            <th>Holiday</th>
                            <th>Functions</th>
                        </thead>
                        @foreach ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }} </td>
                                <td>{{ $row['date_stamp'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('policy.holidays.add', [$holiday_policy_id, $row['id']]) }}">Edit</a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/policy/holidays/delete/{{ $row['id'] }}', 'Holiday', this)">Delete</button>
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
