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
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="get" action="#">
                        <table class="table table-bordered">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Objects</th>
                                <th>Functions</th>
                            </tr>
                            @foreach ($hierarchy_controls as $i => $hierarchy_control)
                                <tr>
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
                                        @if ($permission->Check('hierarchy','edit_own') OR $permission->Check('hierarchy','edit'))
                                            [ <a href="#">Edit</a> ]
                                        @endif
                                        @if ($permission->Check('hierarchy','delete'))
                                            [ <a href="#">Delete</a> ]
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            <input type="hidden" name="sort_column" value="{{$sort_column}}">
                            <input type="hidden" name="sort_order" value="{{$sort_order}}">
                            <input type="hidden" name="page" value="{{$paging_data['current_page']}}">
                        </table>
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>