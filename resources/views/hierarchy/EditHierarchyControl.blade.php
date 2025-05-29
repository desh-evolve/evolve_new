<x-app-layout :title="'Input Example'">
    <style>
        th, td{
            padding: 5px !important;
        }

        .arrow-icon{
            background-color: green;
            color: white;
            font-size: 16px;
            border-radius: 50%;
            padding: 5px;
            align-items: center;
            margin: 2px;
        }
    </style>
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

                    <form method="post" action="{{ route('company.hierarchy.add') }}">
                        @csrf
                        <div id="contentBoxTwoEdit">
                            @if (!$hcf->Validator->isValid() OR !$hlf->Validator->isValid())
                                {{-- {include file="form_errors.tpl" object="hcf,hlf"} --}}
                                {{-- error list here --}}
                            @endif
            
                            <table class="table table-bordered">
            
                            <tr>
                                <td>
                                    Name:
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="text" name="hierarchy_control_data[name]" value="{{$hierarchy_control_data['name'] ?? ''}}">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    Description:
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="text" name="hierarchy_control_data[description]" value="{{$hierarchy_control_data['description'] ?? ''}}">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    Objects:
                                    <Br>
                                    (Select one or more)
                                </td>
                                <td class="cellRightEditTable">
                                    <select name="hierarchy_control_data[object_type_ids][]" multiple>
                                        {!! html_options([ 'options'=>$hierarchy_control_data['object_type_options'], 'selected'=>$hierarchy_control_data['object_type_ids'] ?? []]) !!}
                                    </select>
                                </td>
                            </tr>
            
                            <tbody id="filter_employees_on" style="display:none" >
                            <tr>
                                <td nowrap>
                                    <b>Subordinates:</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><i class="ri-arrow-down-double-fill arrow-icon" style="vertical-align: middle" ></i></a>
                                </td>
                                <td colspan="3">
                                    <table class="table table-bordered">
                                    <tr class="bg-primary text-white">
                                        <td>
                                            UnAssigned Employees
                                        </td>
                                        <td>
                                            <br>
                                        </td>
                                        <td>
                                            Assigned Employees
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="cellRightEditTable" width="49%" align="center">
                                            <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_user'))">
                                            <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_user'))">
                                            <br>
                                            <select name="src_user_id" id="src_filter_user" style="width:90%;margin:5px 0 5px 0;" size="{{select_size([ 'array'=>$hierarchy_control_data['user_options']])}}" multiple>
                                                {!! html_options([ 'options'=>$hierarchy_control_data['user_options']]) !!}
                                            </select>
                                        </td>
                                        <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                            <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{select_size([ 'array'=>$hierarchy_control_data['user_options']])}})"><i class="ri-arrow-right-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                            <br>
                                            <a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{select_size([ 'array'=>$hierarchy_control_data['user_options']])}})"><i class="ri-arrow-left-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                            <br>
                                            <br>
                                            <br>
                                            <a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
                                        </td>
                                        <td class="cellRightEditTable" width="49%" align="center">
                                            <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_user'))">
                                            <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_user'))">
                                            <br>
                                            <select name="hierarchy_control_data[user_ids][]" id="filter_user" style="width:90%;margin:5px 0 5px 0;" size="{{select_size(['array'=>$filter_user_options])}}" multiple>
                                                {!! html_options([ 'options'=>$filter_user_options, 'selected'=>$hierarchy_control_data['user_ids'] ?? []]) !!}
                                            </select>
                                        </td>
                                    </tr>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                            <tbody id="filter_employees_off">
                            <tr>
                                <td nowrap>
                                    <b>Subordinates:</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{select_size(['array'=>$hierarchy_control_data['user_options']])}})"><i class="ri-arrow-up-double-fill arrow-icon" style="vertical-align: middle" ></i></a>
                                </td>
                                <td class="cellRightEditTable" colspan="100">
                                    <span id="filter_user_count">0</span> Employees Currently Selected, Click the arrow to modify.
                                </td>
                            </tr>
                            </tbody>
                            <tr>
                              <td colspan="3">
                                <table class="tblList">
                                    <tr class="bg-primary text-white">
                                        <td colspan="3">
                                            <b>NOTE:</b> Level one denotes the top or last level of the hierarchy and employees at the same level share responsibilities.
                                        </td>
                                    </tr>
                                    <tr class="bg-primary text-white">
                                        <td width="50%">
                                            Level
                                        </td>
                                        <td width="50%">
                                            Superiors
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                        </td>
                                    </tr>
                                    @foreach ($hierarchy_level_data as $hierarchy_level)
                                      <tr class="">
                                        <td>
                                            <input type="hidden" name="hierarchy_level_data[{{$hierarchy_level['id']}}][id]" value="{{$hierarchy_level['id']}}">
                                            @if ($hierarchy_level['level'] > 1)
                                                {{ str_repeat('      ', $hierarchy_level['level'] - 1) }}
                                            @endif
                                            <input type="text" size="4" name="hierarchy_level_data[{{$hierarchy_level['id']}}][level]" value="{{$hierarchy_level['level']}}">
                                        </td>
                                        <td>
                                            @if ($hierarchy_level['level'] > 1)
                                                {{ str_repeat('      ', $hierarchy_level['level'] - 1) }}
                                            @endif
                                          <select id="hierarchy_level-{{$hierarchy_level['id']}}" name="hierarchy_level_data[{{$hierarchy_level['id']}}][user_id]">
                                              {!! html_options(['options'=>$hierarchy_control_data['level_user_options'], 'selected'=>$hierarchy_level['user_id'] ?? []]) !!}
                                          </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="ids[]" value="{{$hierarchy_level['id']}}">
                                        </td>
                                      </tr>
                                    @endforeach
                                </table>
                              </td>
                            </tr>
            
                            <tr>
                                <td class="tblActionRow text-end" colspan="3">
                                    <input type="submit" name="action" value="Submit" onClick="selectAll(document.getElementById('filter_user'))">
                                    <input type="submit" name="action" value="Add Level" onClick="selectAll(document.getElementById('filter_user'))">
                                    <input type="submit" name="action" value="Delete Level" onClick="selectAll(document.getElementById('filter_user'))">
                                </td>
                            </tr>

                        </table>
                        </div>
                        {{-- <div id="contentBoxFour">
                        </div> --}}
                        <input type="hidden" name="hierarchy_control_data[id]" value="{{$hierarchy_control_data['id'] ?? ''}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script	language=JavaScript>
        function filterUserCount() {
            total = countSelect(document.getElementById('filter_user'));
            writeLayer('filter_user_count', total);
        }
    </script>
</x-app-layout>
