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
                                href="/policy/absence_policies/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Absence Policy <i class="ri-add-line"></i>
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
                            <th>Type</th>
                            <th>Functions</th>
                        </thead>
                        @foreach ($policies as $index => $policy)
                            <tr>
                                <td>{{ $index + 1 }} </td>
                                <td>{{ $policy['name'] }}</td>
                                <td>{{ $policy['type'] }}</td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('policy.absence_policies.add', ['id' => $policy['id']]) }}">Edit</a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/policy/absence_policies/delete/{{ $policy['id'] }}', 'Absence Policy', this)">Delete</button>
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
