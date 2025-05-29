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
                                href="/company/hierarchy/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="post" action="{{ route('company.hierarchy.list') }}">
                        @csrf
                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Objects</th>
                                    <th>Functions</th>
                                </tr>
                            </thead>
                            @foreach ($hierarchy_controls as $i => $hierarchy_control)
                                @php
                                    if($hierarchy_control['deleted']){
                                        $row_class = 'bg-warning text-white';
                                    }else{
                                        $row_class = '';
                                    }
                                @endphp
                                <tr class="{{$row_class}}">
                                    <td>
                                        {{ $i+1 }}
                                    </td>
                                    <td>
                                        {{$hierarchy_control['name']}}
                                    </td>
                                    <td>
                                        {{$hierarchy_control['description']}}
                                    </td>
                                    <td>
                                        @foreach ($hierarchy_control['object_types'] as $object_type)
                                            {{$object_type}}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        {{--
                                            @if ($permission->Check('hierarchy','view_own') OR $permission->Check('hierarchy','view'))
                                                [ <a href="{urlbuilder script="HierarchyList.php" values="hierarchy_id=$hierarchy_control['id']" merge="FALSE"}">View</a> ]
                                            @endif
                                        --}}
                                        @if ($permission->Check('hierarchy','edit_own') OR $permission->Check('hierarchy','edit'))
                                            [<a href="{{ route('company.hierarchy.add', ['hierarchy_control_id' => $hierarchy_control['id'], 'merge' => 'FALSE']) }}">Edit</a>]
                                        @endif
                                        @if ($permission->Check('hierarchy','delete'))
                                            <button type="submit" name="action" value="Delete" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/company/hierarchy/list/{{ $hierarchy_control['id'] }}', 'Hierarchy', this)">Delete</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>