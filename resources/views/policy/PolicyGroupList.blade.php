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
                                href="/policy/policy_groups/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Policy Group <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <div class="row">
                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <th>#</th>
                                <th>Name</th>
                                <th>Functions</th>
                            </thead>
                            @foreach ($policies as $index => $policy)
                                @php
                                    $rowClass = ($policy['deleted'] == true) ? 'tblDataDeleted' : (($index % 2 == 0) ? 'tblDataWhite' : 'tblDataGrey');
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>
                                        {{ $index + 1 }}
                                    </td>
                                    <td>
                                        {{ $policy['name'] }}
                                    </td>
                                    <td>
                                        <a href="{{ route('policy.policy_groups.add', ['id' => $policy['id']]) }}">Edit</a>
                                    </td>
                                </tr>
                            @endforeach

                        </table>
                    </div>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
