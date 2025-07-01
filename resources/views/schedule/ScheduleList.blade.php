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

                        @if ($permission->Check('punch','view') OR $permission->Check('punch','view_child'))
                        <form method="get" name="search_form" action="{$smarty.server.SCRIPT_NAME}">
                            {{-- {include file="list_tabs.tpl" section="header"} --}}
                            <tr id="adv_search" class="tblSearch" style="display: none;">
                                <td colspan="{{$total_columns}}" class="tblSearchMainRow">
                                    <table id="content_adv_search" class="table table-bordered" bgcolor="#7a9bbd">
                                        <tr>
                                            <td valign="top" width="50%">
                                                <table class="table table-bordered">
                                                    <tr id="tab_row_all">
                                                        <td class="cellLeftEditTable">
                                                            Status:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[status_id]">
                                                                {!! html_options([ 'options'=>$filter_data['schedule_status_options'], 'selected'=>$filter_data['status_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <td class="cellLeftEditTable">
                                                            Pay Period:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[pay_period_ids]">
                                                                {!! html_options([ 'options'=>$filter_data['pay_period_options'], 'selected'=>$filter_data['pay_period_ids'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <td class="cellLeftEditTable">
                                                            Employee:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[id]">
                                                                {!! html_options([ 'options'=>$filter_data['user_options'], 'selected'=>$filter_data['id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all" >
                                                        <td class="cellLeftEditTable">
                                                            Employee Status:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select id="filter_data_status_id" name="filter_data[user_status_id]">
                                                                {!! html_options([ 'options'=>$filter_data['status_options'], 'selected'=>$filter_data['user_status_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_adv_search">
                                                        <td class="cellLeftEditTable">
                                                            Title:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[title_id]">
                                                                {!! html_options([ 'options'=>$filter_data['title_options'], 'selected'=>$filter_data['title_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>

                                                            {{-- {*
                                                    <tr id="tab_row_adv_search">
                                                        <td class="cellLeftEditTable">
                                                            Severity:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[severity_id]">
                                                                {html_options options=$filter_data.severity_options selected=$filter_data.severity_id}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_adv_search">
                                                        <td class="cellLeftEditTable">
                                                            Exception:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[exception_policy_type_id]">
                                                                {html_options options=$filter_data.type_options selected=$filter_data.exception_policy_type_id}
                                                            </select>
                                                        </td>
                                                    </tr>
                                            *} --}}
                                                </table>
                                            </td>
                                            <td valign="top" width="50%">
                                                <table class="table table-bordered">
                                                    <tr id="tab_row_all">
                                                        <td class="cellLeftEditTable">
                                                            Group:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[group_id]">
                                                                {!! html_options([ 'options'=>$filter_data['group_options'], 'selected'=>$filter_data['group_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <td class="cellLeftEditTable">
                                                            Default Branch:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[default_branch_id]">
                                                                {!! html_options([ 'options'=>$filter_data['branch_options'], 'selected'=>$filter_data['default_branch_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <td class="cellLeftEditTable">
                                                            Default Department:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[default_department_id]">
                                                                {!! html_options([ 'options'=>$filter_data['department_options'], 'selected'=>$filter_data['default_department_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_all">
                                                        <td class="cellLeftEditTable">
                                                            Schedule Policy:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[schedule_policy_id]">
                                                                {!! html_options([ 'options'=>$filter_data['schedule_policy_options'], 'selected'=>$filter_data['schedule_policy_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_adv_search">
                                                        <td class="cellLeftEditTable">
                                                            Schedule Branch:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[schedule_branch_id]">
                                                                {!! html_options([ 'options'=>$filter_data['branch_options'], 'selected'=>$filter_data['schedule_branch_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr id="tab_row_adv_search">
                                                        <td class="cellLeftEditTable">
                                                            Schedule Department:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <select name="filter_data[schedule_department_id]">
                                                                {!! html_options([ 'options'=>$filter_data['department_options'], 'selected'=>$filter_data['schedule_department_id'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    {{-- {*
                                                    <tr id="tab_row_adv_search">
                                                        <td class="cellLeftEditTable">
                                                            Show Pre-Mature:
                                                        </td>
                                                        <td class="cellRightEditTable">
                                                            <input type="checkbox" class="checkbox" name="filter_data[pre_mature]" value="1" {if $filter_data.pre_mature == 1}checked{/if}>
                                                        </td>
                                                    </tr>
                                    *} --}}
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            {{-- {include file="list_tabs.tpl" section="saved_search"}
                            {include file="list_tabs.tpl" section="global"} --}}
                        </form>
                        @endif

                        <form method="POST" action="{{ route('schedule.schedule_list') }}">
                            <table class="table table-striped table-bordered">

                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>#</th>
                                        @foreach ($columns as $column_id => $column)
                                            <th>{{ $column }}</th>
                                        @endforeach
                                        <th>Functions</th>
                                    </tr>
                                </thead>

                                <tbody id="table_body">
                                    @if (!empty($rows))
                                        @foreach ($rows as $i => $row)
                                            <tr class="">
                                                <td>
                                                    {{ $i+1 }}
                                                </td>

                                                @foreach ($columns as $key => $column)
                                                    <td
                                                        @if ($key == 'severity')
                                                            @if ($row['severity_id'] == 20)
                                                                id="yellow"
                                                            @elseif ($row['severity_id'] == 30)
                                                                id="error"
                                                            @endif
                                                        @endif
                                                    >
                                                        @if ($key == 'exception_policy_type_id')
                                                            <span style="color: {{ $row['exception_color'] }}">
                                                                <strong>{{ $row[$key] ?? '--' }}</strong>
                                                            </span>
                                                        @else
                                                            @if ($key == 'severity')<strong>@endif
                                                                {{ $row[$key] ?? '--' }}
                                                            @if ($key == 'severity')</strong>@endif
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td>
                                                    @if ($permission->Check('schedule','view') OR ( $permission->Check('schedule','view_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('schedule','view_own') AND $row['is_owner'] === TRUE ))
                                                        <a href="/schedule/view_schedule?filter_data[include_user_ids][]={{$row['user_id']}}&filter_data[start_date]={{$row['start_time']}}" class="btn btn-secondary btn-sm">View</a>
                                                    @endif

                                                    @if ($permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $row['is_owner'] === TRUE ))
                                                        <a href="/schedule/edit_schedule?id={{$row['id']}}" class="btn btn-warning btn-sm">Edit</a>
                                                    @endif

                                                    @if ($permission->Check('schedule','delete') OR $permission->Check('schedule','delete_own') OR $permission->Check('schedule','delete_child'))
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/schedule/delete/{{ $row['id'] }}', 'Schedule', this)">
                                                            Delete
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="bg-primary text-white" colspan="{{$total_columns}}">
                                                No Scheduled Shifts Found
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                        </form>
                    </div>

                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script	language=JavaScript>

        function editPunch(punchID,punchControlId,userID,date,statusID) {
            try {
                eP=window.open('/punch/edit_punch?id='+ encodeURI(punchID) +'&punch_control_id='+ encodeURI(punchControlId) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date) +'&status_id='+ encodeURI(statusID),"Edit_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=600,height=470,resizable=1");
                if (window.focus) {
                    eP.focus()
                }
            } catch (e) {
                //DN
            }
        }

    </script>
</x-app-layout>
