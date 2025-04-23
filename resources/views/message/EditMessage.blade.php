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

                    <form method="post" name="edit_message" action="{$smarty.server.SCRIPT_NAME}">
                        <div id="contentBoxTwoEdit">
                            @if (!$mcf->Validator->isValid())
                                {{-- add error list here --}}
                            @endif
            
                            <table class="editTable">
            
                                @if ($permission->Check('message','send_to_any') OR $permission->Check('message','send_to_child'))
                                    <tr>
                                        <th class="cellLeftEditTableHeader" nowrap>
                                            <b>To:</b>
                                        </th>
                
                                        <td>
                                            <div class="col-md-12">
                                                <x-general.multiselect-php 
                                                    title="Employees" 
                                                    :data="$data['user_options']" 
                                                    :selected="!empty($data['filter_user_id']) ? array_values($data['filter_user_id']) : []" 
                                                    :name="'filter_user_id[]'"
                                                    id="userSelector"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <th style="width: 20%;">
                                            To:
                                        </th>
                                        <td colspan="3">
                                            <select name="filter_user_id[]" id="filter_user">
                                                @foreach ($data['user_options'] as $id => $name)
                                                    <option value="{{$id}}">{{$name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endif
                
                                <tr>
                                    <th style="width: 20%;">
                                        Subject:
                                    </th>
                                    <td colspan="3">
                                        <input type="text" size="45" name="data[subject]" value="{{!empty($data['subject']) ?? $data['subject'] : $default_subject}}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Body:
                                    </th>
                                    <td colspan="3">
                                        <textarea rows="5" cols="50" name="data[body]">{{$data['body']}}</textarea>
                                    </td>
                                </tr>
                                <tr class="tblHeader">
                                    <td colspan="100">
                                        <input class="btn btn-primary" type="submit" name="action:Submit_Message" value="Submit Message" onClick="selectAll(document.getElementById('filter_user'))">
                                    </td>
                                </tr>
            
                            </table>
                        </div>
            
                        <input type="hidden" name="data[id]" value="{{$id}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script	language=JavaScript>
        $(document).ready(function(){
            filterUserCount();
        })

        function filterUserCount() {
            total = countSelect(document.getElementById('filter_user'));
            writeLayer('filter_user_count', total);
        }
    </script>
</x-app-layout>