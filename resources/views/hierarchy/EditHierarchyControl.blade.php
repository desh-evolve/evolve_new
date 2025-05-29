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

                    <form method="POST"
                        action="{{ route('company.hierarchy.add') }}"
                        id="hierarchy_form" enctype="multipart/form-data">
                        @csrf

                        <div id="contentBoxTwoEdit">

                            @if (!$hcf->Validator->isValid())
                                <div class="alert alert-danger">
                                    <ul>
                                        <li>Error list</li>
                                    </ul>
                                </div>
                            @endif

                            <table class="editTable">

                            <tr>
                                <th>
                                    Name:
                                </th>
                                <td>
                                    <input type="text" name="hierarchy_control_data[name]" value="{{$hierarchy_control_data['name'] ?? '' }}">
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Description:
                                </th>
                                <td>
                                    <input type="text" name="hierarchy_control_data[description]" value="{{$hierarchy_control_data['description'] ?? '' }}">
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Objects:
                                    <Br>
                                    (Select one or more)
                                </th>
                                <td>
                                    <select name="hierarchy_control_data[object_type_ids][]" multiple >
                                        @foreach ($hierarchy_control_data['object_type_options'] as $id => $name )
                                        <option value="{{$id}}"
                                            @if(!empty($hierarchy_control_data['object_type_ids']) && in_array($id,$hierarchy_control_data['object_type_ids']))
                                                selected
                                            @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td class="{isvalid object="ppsf" label="user" value="cellLeftEditTable"}" nowrap>
                                    <b>Subordinates:</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
                                </td>
                                <td colspan="3">
                                    <div class="col-md-12">
                                        <x-general.multiselect-php
                                            title="Employees"
                                            :data="$hierarchy_control_data['user_options']"
                                            :selected="!empty($hierarchy_control_data['user_ids']) ? array_values($hierarchy_control_data['user_ids']) : []"
                                            :name="'hierarchy_control_data[user_ids][]'"
                                            id="userSelector"
                                        />
                                    </div>
                                </td>
                            </tr>



                            {{-- check here
                            <tbody id="filter_employees_off">
                            <tr>
                                <td class="{isvalid object="ppsf" label="user" value="cellLeftEditTable"}" nowrap>
                                    <b>Subordinates:</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$hierarchy_control_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
                                </td>
                                <td colspan="100">
                                    <span id="filter_user_count">0</span> Employees Currently Selected, Click the arrow to modify.
                                </td>
                            </tr>
                            </tbody>
                            <tr>
                              <td colspan="3">
                                <table class="tblList">
                                    <tr class="tblHeader">
                                        <td colspan="3">
                                            <b>NOTE:</b> Level one denotes the top or last level of the hierarchy and employees at the same level share responsibilities.
                                        </td>
                                    </tr>
                                    <tr class="tblHeader">
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
                                    {foreach name="level" from=$hierarchy_level_data item=hierarchy_level}
                                      {assign var="hierarchy_level_id" value=$hierarchy_level.id}
                                      {cycle assign=row_class values="tblDataWhite,tblDataGrey"}

                                      <tr class="{$row_class}">
                                        <td>
                                            <input type="hidden" name="hierarchy_level_data[{$hierarchy_level.id}][id]" value="{$hierarchy_level.id}">
                                            {if $hierarchy_level.level > 1}
                                                {'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:$hierarchy_level.level-1}
                                            {/if}
                                            <input type="text" size="4" name="hierarchy_level_data[{$hierarchy_level.id}][level]" value="{$hierarchy_level.level}">
                                        </td>
                                        <td>
                                            {if $hierarchy_level.level > 1}
                                                {'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:$hierarchy_level.level-1}
                                            {/if}
                                          <select id="hierarchy_level-{$hierarchy_level.id}" name="hierarchy_level_data[{$hierarchy_level.id}][user_id]">
                                              {html_options options=$hierarchy_control_data.level_user_options selected=$hierarchy_level.user_id}
                                          </select>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="ids[]" value="{$hierarchy_level.id}">
                                        </td>
                                      </tr>
                                    {/foreach}
                                </table>
                              </td>
                            </tr>
                            --}}


                            <tr>
                                <td class="tblActionRow" colspan="3">
                                    <input type="submit" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))">
                                    <input type="submit" name="action:add_level" value="Add Level" onClick="selectAll(document.getElementById('filter_user'))">
                                    <input type="submit" name="action:delete_level" value="Delete Level" onClick="selectAll(document.getElementById('filter_user'))">
                                </td>
                            </tr>

                        </table>
                        </div>
                        <input type="hidden" name="hierarchy_control_data[id]" value="{{$hierarchy_control_data['id'] ?? '' }}">
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
