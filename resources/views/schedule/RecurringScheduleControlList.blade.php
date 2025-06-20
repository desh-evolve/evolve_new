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

                    <table class="table table-bordered">
        
                        <form method="get" name="search_form" action="#">
                            {{-- {include file="list_tabs.tpl" section="header"} --}}
                            <tr id="adv_search" class="tblSearch" style="display: none;">
                                <td colspan="{{$total_columns}}" class="tblSearchMainRow">
                                    <table id="content_adv_search" class="table table-bordered" bgcolor="#7a9bbd">
                                        <tr>
                                            <td valign="top" width="50%">
                                                <table class="table table-bordered">
                                                    <tr id="tab_row_all" >
                                                        <th>
                                                            Employee Status:
                                                        </th>
                                                        <td class="cellRightEditTable">
                                                            <select id="filter_data_status_id" name="filter_data[status_id]">
                                                                {!! html_options([ 'options'=>$filter_data['status_options'], 'selected'=>$filter_data['status_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <th>
                                                            Template:
                                                        </th>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[template_id]">
                                                                {!! html_options([ 'options'=>$filter_data['template_options'], 'selected'=>$filter_data['template_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <th>
                                                            Employee:
                                                        </th>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[user_id]">
                                                                {!! html_options([ 'options'=>$filter_data['user_options'], 'selected'=>$filter_data['user_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td valign="top" width="50%">
                                                <table class="table table-bordered">
                                                    <tr id="tab_row_all">
                                                        <th>
                                                            Group:
                                                        </th>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[group_id]">
                                                                {!! html_options([ 'options'=>$filter_data['group_options'], 'selected'=>$filter_data['group_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <th>
                                                            Default Branch:
                                                        </th>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[default_branch_id]">
                                                                {!! html_options([ 'options'=>$filter_data['branch_options'], 'selected'=>$filter_data['default_branch_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <th>
                                                            Default Department:
                                                        </th>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[default_department_id]">
                                                                {!! html_options([ 'options'=>$filter_data['department_options'], 'selected'=>$filter_data['default_department_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_adv_search">
                                                        <th>
                                                            Title:
                                                        </th>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[title_id]">
                                                                {!! html_options([ 'options'=>$filter_data['title_options'], 'selected'=>$filter_data['title_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            {{-- {include file="list_tabs.tpl" section="saved_search"} --}}
                            {{-- {include file="list_tabs.tpl" section="global"} --}}
                        </form>
        
                        <form method="get" action="{$smarty.server.SCRIPT_NAME}">
                        <tr class="bg-primary text-white">
                            <td>
                                #
                            </td>
                            @foreach ($columns as $column_id => $column)
                                <td>
                                    {{ $column }}
                                </td>
                            @endforeach
        
                            <td>
                                Functions
                            </td>
                            <td>
                                <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                            </td>
                        </tr>
                        @foreach ($rows as $i => $row)
                            <tr class="">
                                <td>
                                    {{ $i+1 }}
                                </td>
                                @foreach ($columns as $key => $column)
                                    <td>
                                        @if ($key == 'start_date')
                                            {{getdate_helper('date', $row['start_date'])}}
                                        @elseif ($key == 'end_date')
                                            @if ($row['end_date'] == NULL)
                                                Never
                                            @else
                                                {{ getdate_helper('date', $row['end_date']) }}
                                            @endif
                                        @else
                                            {{$row[$key] ?? "--"}}
                                        @endif
                                    </td>
                                @endforeach
                                
                                <td>
                                    @if ($permission->Check('recurring_schedule','edit') OR ( $permission->Check('recurring_schedule','edit_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('recurring_schedule','edit_own') AND $row['is_owner'] === TRUE ))
                                        [ <a href="/schedule/edit_recurring_schedule?id={{$row['id']}}" >Edit</a> ]
                                    @endif
                                </td>
                                <td>
                                    <input type="checkbox" class="checkbox" name="ids[{{$row['id']}}][]" value="{{$row['user_id']}}">
                                </td>
                            </tr>
                        @endforeach
                        
                        <tr>
                            <td class="tblActionRow" colspan="{{$total_columns}}">
                                @if ($permission->Check('recurring_schedule','add'))
                                    <input type="submit" class="button" name="action:add" value="Add">
                                @endif
                                @if ($permission->Check('recurring_schedule','delete') OR $permission->Check('recurring_schedule','delete_own') OR $permission->Check('recurring_schedule','delete_child'))
                                    <input type="submit" class="button" name="action:delete" value="Delete" onClick="return confirmSubmit()">
                                @endif
                                @if ($permission->Check('recurring_schedule','undelete'))
                                    <input type="submit" class="button" name="action:undelete" value="UnDelete">
                                @endif
                            </td>
                        </tr>
                    </table>
                </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>