<x-app-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>
    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
                        <div id="contentBoxTwoEdit">
                            @if (!$rscf->Validator->isValid())
                                {{-- error list --}}
                                {{-- {include file="form_errors.tpl" object="rscf"} --}}
                            @endif
            
                            <table class="table table-bordered">
            
                            <tr>
                                <th>
                                    Template:
                                </th>
                                <td class="cellRightEditTable">
                                    <select id="template_id" name="data[template_id][]" multiple>
                                        {{html_options([ 'options'=>$data['template_options'], 'selected'=>$data['template_id']])}}
                                    </select>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Start Week:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="4" name="data[start_week]" value="{{$data['start_week']}}">
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Start Date:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="15" id="start_date" name="data[start_date]" value="{{getdate_helper('date', $data['start_date'])}}">
                                    ie: {{$current_user_prefs->getDateFormatExample()}}
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    End Date:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="15" id="end_date" name="data[end_date]" value="{{getdate_helper('date', $data['end_date'])}}">
                                    ie: {{$current_user_prefs->getDateFormatExample()}} <b>(Leave blank for no end date)</b>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Auto-Pilot:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="checkbox" class="checkbox" name="data[auto_fill]" value="1" {{ $data['auto_fill'] ? 'checked' : '' }} >
                                </td>
                            </tr>
            
                            <tbody id="filter_employees_on" style="display:none" >
                            <tr>
                                <td nowrap>
                                    <b>Employees:</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><i class="ri-arrow-down-double-fill arrow-icon" style="vertical-align: middle" ></i></a>
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
                                            <select name="src_user_id" id="src_filter_user" style="width:200px;margin:5px 0 5px 0;" size="{{select_size([ 'array'=>$data['user_options']])}}" multiple>
                                                {{html_options([ 'options'=>$data['user_options']])}}
                                            </select>
                                        </td>
                                        <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                            <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{select_size([ 'array'=>$data['user_options']])}})"><i class="ri-arrow-right-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                            <br>
                                            <a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{select_size( 'array'=>$data['user_options'])}})"><i class="ri-arrow-left-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                            <br>
                                            <br>
                                            <br>
                                            <a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
                                        </td>
                                        <td class="cellRightEditTable" width="49%" align="center">
                                            <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_user'))">
                                            <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_user'))">
                                            <br>
                                            <select name="data[user_ids][]" id="filter_user" style="width:200px;margin:5px 0 5px 0;" size="{{select_size([ 'array'=>$filter_user_options])}}" multiple>
                                                {{html_options([ 'options'=>$filter_user_options, 'selected'=>$data['user_ids']])}}
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
                                    <b>Employees:</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{select_size([ 'array'=>$data['user_options']])}})"><i class="ri-arrow-up-double-fill arrow-icon" style="vertical-align: middle" ></i></a>
                                </td>
                                <td class="cellRightEditTable" colspan="100">
                                    <span id="filter_user_count">0</span> Employees Currently Selected, Click the arrow to modify.
                                </td>
                            </tr>
                            </tbody>
            
                        </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))">
                        </div>
            
                        <input type="hidden" name="data[id]" value="{{$data['id']}}">
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
        })

        function filterUserCount() {
            total = countSelect(document.getElementById('filter_user'));
            writeLayer('filter_user_count', total);
        }
    </script>
</x-app-layout>