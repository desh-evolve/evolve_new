<x-app-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>
    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="/payroll/paystub_accounts/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="POST" name="edit_permission" action="/permission_control/add">
                    @csrf
                        <div id="contentBoxTwoEdit">
                            @if (!$pcf->Validator->isValid())
                                {{-- {include file="form_errors.tpl" object="pcf"} --}}
                                {{-- add error list --}}
                            @endif
            
                            <table class="table table-bordered">
                
                                <tr>
                                    <th>
                                        Name:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" name="data[name]" value="{{$data['name'] ?? ''}}">
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Description:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" name="data[description]" value="{{$data['description'] ?? ''}}">
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Level:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select name="data[level]">
                                            {!! html_options([ 'options'=>$data['level_options'], 'selected'=>$data['level'] ?? '']) !!}
                                        </select>
                                        (Higher levels can only assign employees to lower levels)
                                    </td>
                                </tr>
                
                                <tr>
                                    <td colspan="2">
                                        <table class="tblList">
                                            <tr class="bg-primary text-white">
                                                <td colspan="6">
                                                    Permission Presets:
                                                        <select id="preset" name="data[preset]">
                                                            {!! html_options([ 'options'=>$preset_options]) !!}
                                                        </select>
                                                        @if( $product_edition == 20)
                                                            <input type="checkbox" name="data[preset_flags][job]" value="1"> Job Tracking
                                                            <input type="checkbox" name="data[preset_flags][invoice]" value="1"> Invoicing
                                                            <input type="checkbox" name="data[preset_flags][document]" value="1"> Documents
                                                        @endif
                                                    <input type="submit" class="button" name="action" value="Apply Preset" onClick="selectAll(document.getElementById('filter_user')); return singleSubmitHandler(this)">
                                                </td>
                                            </tr>
                
                                            <tr class="bg-primary text-white">
                                                <td colspan="6">
                                                    Display Permissions:
                                                        <select id="group" name="group_id" onChange="submitModifiedForm('group', '', document.edit_permission);">
                                                            {!! html_options([ 'options'=>$section_group_options, 'selected'=>$group_id ?? '']) !!}
                                                        </select>
                                                        <input type="hidden" id="old_group" value="{{$group_id ?? ''}}">
                                                </td>
                                            </tr>
                
                                            @php
                                                $first = true;
                                                $iteration = 1;
                                                $total = count($permission_data);
                                            @endphp

                                            @foreach ($permission_data as $index => $section)
                                                @if (!isset($ignore_permissions[$section['name']]) || $ignore_permissions[$section['name']] !== 'ALL')
                                                    @if ($first)
                                                        <tr class="bg-primary text-white">
                                                            <td colspan="6">
                                                                <a name="top">Table of Contents</a>
                                                            </td>
                                                        </tr>

                                                        @php $row_class = 'tblDataWhiteNH'; @endphp
                                                        <tr class="{{ $row_class }}">
                                                        @php $first = false; @endphp
                                                    @endif

                                                    <td colspan="2">
                                                        <a href="#{{ $section['name'] }}">{{ $section['display_name'] }}</a>
                                                    </td>

                                                    @if ($iteration % 3 == 0)
                                                        @php $row_class = $row_class == 'tblDataWhiteNH' ? 'tblDataGreyNH' : 'tblDataWhiteNH'; @endphp
                                                        </tr>
                                                        <tr class="{{ $row_class }}">
                                                    @endif

                                                    @if ($loop->last)
                                                        <td colspan="2">
                                                            <a href="#employees">Employee List</a>
                                                        </td>

                                                        @if (($total + 1) % 3 != 0)
                                                            <td colspan="6">
                                                                <br>
                                                            </td>
                                                        @endif
                                                        </tr>
                                                    @endif
                                                @endif

                                                @if ($loop->last)
                                                    <tr>
                                                        <td colspan="6">
                                                            <br>
                                                        </td>
                                                    </tr>
                                                @endif

                                                @php $iteration++; @endphp
                                            @endforeach


                                            {{-- Permissions Table --}}
                                            @foreach ($permission_data as $section)
                                                @if (!isset($ignore_permissions[$section['name']]) || $ignore_permissions[$section['name']] !== 'ALL')
                                                    <tr class="bg-primary text-white">
                                                        <td>
                                                            [ <a href="#top">Top</a> ] [ <a href="#employees">Bottom</a> ]
                                                        </td>
                                                        <td colspan="2">
                                                            <a name="{{ $section['name'] }}">{{ $section['display_name'] }}</a>
                                                        </td>
                                                        <td>Allow</td>
                                                        <td>Deny</td>
                                                    </tr>

                                                    @foreach ($section['permissions'] as $perm)
                                                        @php
                                                            $show = false;

                                                            if (!isset($ignore_permissions[$section['name']])) {
                                                                $show = true;
                                                            } elseif (
                                                                is_array($ignore_permissions[$section['name']]) &&
                                                                (
                                                                    (in_array($perm['name'], $ignore_permissions[$section['name']]) && $permission->Check('company', 'edit')) ||
                                                                    (!in_array($perm['name'], $ignore_permissions[$section['name']]))
                                                                )
                                                            ) {
                                                                $show = true;
                                                            }

                                                            $row_class = isset($row_class) && $row_class === 'tblDataWhiteNH' ? 'tblDataGreyNH' : 'tblDataWhiteNH';
                                                        @endphp

                                                        @if ($show)
                                                            <tr class="{{ $row_class }}">
                                                                <td colspan="3" class="cellLeftBlueEditTable">
                                                                    {{ $perm['display_name'] }}: {{ $ignore_permissions[$section['name']][$perm['name']] ?? '' }}
                                                                </td>
                                                                <td>
                                                                    <input type="radio" name="data[permissions][{{ $section['name'] }}][{{ $perm['name'] }}]" value="1" @if ($perm['result'] === true) checked @endif>
                                                                </td>
                                                                <td>
                                                                    <input type="radio" name="data[permissions][{{ $section['name'] }}][{{ $perm['name'] }}]" value="0" @if ($perm['result'] !== true) checked @endif>
                                                                </td>
                                                                <input type="hidden" name="old_data[permissions][{{ $section['name'] }}][{{ $perm['name'] }}]" value="{{ $perm['result'] === true ? '1' : '0' }}">
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach

                                        </table>
                                        <a name="employees"></a>
                                    </td>
                                </tr>
                
                                <tbody id="filter_employees_on" style="display:none" >
                                <tr>
                                    <th nowrap>
                                        <b>Employees:</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><i class="ri-arrow-down-double-fill arrow-icon" style="vertical-align: middle" ></i></a>
                                    </th>
                                    <td colspan="3">
                                        <table class="table table-bordered">
                                        <tr class="bg-primary text-white">
                                            <th>
                                                UnAssigned Employees
                                            </th>
                                            <th>
                                                <br>
                                            </th>
                                            <th>
                                                Assigned Employees
                                            </th>
                                        </tr>
                                        <tr>
                                            <td class="cellRightEditTable" width="49%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_user'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_user'))">
                                                <br>
                                                <select name="src_user_id[]" id="src_filter_user" style="width:200px;margin:5px 0 5px 0;" size="{!! select_size([ 'array'=>$data['user_options']]) !!}" multiple>
                                                    {!! html_options([ 'options'=>$data['user_options']]) !!}
                                                </select>
                                            </td>
                                            <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                                <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {!! select_size([ 'array'=>$data['user_options']]) !!})"><i class="ri-arrow-right-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                                <br>
                                                <a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {!! select_size([ 'array'=>$data['user_options']]) !!})"><i class="ri-arrow-left-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                                <br>
                                            </td>
                                            <td class="cellRightEditTable" width="49%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_user'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_user'))">
                                                <br>
                                                <select name="data[user_ids][]" id="filter_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_user_options}" multiple>
                                                    {!! html_options([ 'options'=>$filter_user_options, 'selected'=>$data['user_options']]) !!}
                                                </select>
                                            </td>
                                        </tr>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                                <tbody id="filter_employees_off">
                                <tr>
                                    <th nowrap>
                                        <b>Employees:</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {!! select_size([ 'array'=>$data['user_options']]) !!})"><i class="ri-arrow-up-double-fill arrow-icon" style="vertical-align: middle" ></i></a>
                                    </th>
                                    <td class="cellRightEditTable" colspan="100">
                                        <span id="filter_user_count">0</span> Employees Currently Selected, Click the arrow to modify.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action" value="Submit" onClick="selectAll(document.getElementById('src_filter_user')); selectAll(document.getElementById('filter_user')); return singleSubmitHandler(this)">
                        </div>
            
                        <input type="hidden" name="data[id]" value="{{$data['id'] ?? ''}}">
                        <input type="hidden" name="id" value="{{$data['id'] ?? ''}}">
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <script	language=JavaScript>
        $(document).ready(function(){
            filterUserCount(); 
            uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user'));
        })

        function filterUserCount() {
            total = countSelect(document.getElementById('filter_user'));
            writeLayer('filter_user_count', total);
        }
    </script>
</x-app-layout>