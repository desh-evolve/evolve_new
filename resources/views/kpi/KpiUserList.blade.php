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

                    <form method="get" name="userwage" action="#">
                        <table class="table table-bordered">
                            <tr class="tblHeader">
                                <td colspan="10">
                                    Employee:
                                    <a href="javascript: submitModifiedForm('filter_user', 'prev', document.userwage);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
                                    <select name="user_id" id="filter_user" onChange="submitModifiedForm('filter_user', '', document.userwage);">
                                        @foreach ($user_id as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($user_id) && $id == $user_id)
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="old_filter_user" value="{$user_id}">
                                    <a href="javascript: submitModifiedForm('filter_user', 'next', document.userwage);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </td>
                            </tr>
            
                            
                            <tr class="tblHeader">
                                <th>Title</th>
                                <th> Start Date</th>
                                <th>End Date</th>
                                <th>Review Date</th>
                                <th>Functions</th>
                            </tr>
                            @foreach ($kpi_history as $kpi)
                                <tr>
                                    <td>
                                        {{$kpi['title_id']}}
                                    </td>
                                    <td>
                                        {{$kpi['start_date']}}
                                    </td>
                                    <td>
                                        {{$kpi['end_date']}}
                                    </td>
                                    <td>
                                        {{$kpi['review_date']}}
                                    </td>         
                                    <td>
                                        @if ($permission->Check('wage','edit') OR ( $permission->Check('wage','edit_child') AND $kpi['is_child'] === TRUE ) OR ( $permission->Check('wage','edit_own') AND $kpi['is_owner'] === TRUE ))
                                            [ <a href="#">Edit</a> ]
                                        @endif

                                        @if ($permission->Check('wage','delete') OR ( $permission->Check('wage','delete_child') AND $kpi['is_child'] === TRUE ) OR ( $permission->Check('wage','delete_own') AND $kpi['is_owner'] === TRUE ))
                                            [ <a href="#">Delete</a> ]
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <input type="hidden" name="sort_column" value="{{$sort_column}}">
                            <input type="hidden" name="sort_order" value="{{$sort_order}}">
                            <input type="hidden" name="saved_search_id" value="{{$saved_search_id}}">
                            <input type="hidden" name="page" value="{{$paging_data['current_page']}}">
                        </table>
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>