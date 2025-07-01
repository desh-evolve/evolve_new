<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Schedule') }}</h4>
    </x-slot>
    {{-- <style>
        td, th{
            padding: 5px !important;
        }
    </style> --}}
    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            @if ($permission->Check('recurring_schedule','add'))
                                <a type="button" href="/schedule/recurring_schedule_control/add"
                                    class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                    id="add_new_btn">Add New <i class="ri-add-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>

                <div class="card-body">

                    {{-- --------------------------------------------------------------------------- --}}
                    <table>
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
                    </table>

                    <table class="table table-bordered table-striped">

                        <thead class="bg-primary text-white">
                            <th>#</th>
                            @foreach ($columns as $column_id => $column)
                                <th>{{ $column }}</th>
                            @endforeach
                            <th>Functions</th>
                        </thead>

                        <tbody id="table_body">
                            @foreach ($rows as $i => $row)
                                <tr class="">
                                    <td>{{ $i+1 }}</td>
                                    @foreach ($columns as $key => $column)
                                        <td>
                                            @if ($key == 'start_date')
                                                {{getdate_helper('date', $row['start_date'])}}
                                            @elseif ($key == 'end_date')
                                                @if ($row['end_date'] == NULL)
                                                    <span class="text-danger"><i class="ri-close-circle-line fs-17 align-middle"></i> Never</span>
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
                                            <a href="{{ route('schedule.edit_recurring_schedule.add', ['id' => $row['id']]) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        @endif

                                        @if ($permission->Check('recurring_schedule','delete') OR $permission->Check('recurring_schedule','delete_own') OR $permission->Check('recurring_schedule','delete_child'))
                                            <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/schedule/recurring_schedule_control/delete/{{ $row['id'] }}', 'Schedule', this)">
                                                Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>


                    </table>

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script>
        const tableBody = document.getElementById("table_body");
        if (tableBody && tableBody.children.length === 0) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td colspan="10" class="text-center text-danger font-weight-bold">No Recurring Schedule.</td>
            `;
            tableBody.appendChild(row);
        }
    </script>
</x-app-layout>
