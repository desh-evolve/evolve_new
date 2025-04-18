<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="post" action="#">
                        <div id="contentBoxTwoEdit">

                            @if (!$hf->Validator->isValid())
                                <div class="alert alert-danger">
                                    <ul>
                                        <li>Error list</li>
                                    </ul>
                                </div>
                            @endif
            
                            <table class="editTable">
            
                            <tr>
                                <th>
                                    Parent:
                                </th>
                                <td colspan="3">
                                    <select 
                                        name="user_data[parent_id]" 
                                    >
                                        @foreach ($parent_list_options as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($selected_node['parent_id']) && $id == $selected_node['parent_id'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Shared:
                                </th>
                                <td colspan="3">
                                    <input type="checkbox" name="user_data[share]" value="1" {{$selected_node['shared'] == TRUE ? 'checked' : ''}}>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <b>Employees:</b>
                                </th>
            
                                <td colspan="3">
                                    <div class="col-md-12">
                                        <x-general.multiselect-php 
                                            title="Employees" 
                                            :data="$src_user_options" 
                                            :selected="!empty($selected_user_options) ? array_values($selected_user_options) : []" 
                                            :name="'user_data[user_id][]'"
                                            id="userSelector"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))">
                        </div>
            
                        <input type="hidden" name="id" value="{{$id}}">
                        <input type="hidden" name="old_id" value="{{$old_id}}">
                        <input type="hidden" name="hierarchy_id" value="{{$hierarchy_id}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>