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
                            @if ($permission->Check('recurring_schedule_template','add'))
                                <a type="button" href="/schedule/recurring_schedule_template_control/add"
                                    class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                    id="add_new_btn">Add New <i class="ri-add-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>

                <div class="card-body">

                    <form method="post" action="{{ route('schedule.recurring_schedule_template_control_list') }}">
                        @csrf
                        <table class="table table-striped table-bordered">
                            <thead class="bg-primary text-white">
                                <th>#</th>
                                <th>Name </th>
                                <th>Description</th>
                                <th>Functions</th>
                                <td>
                                    <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                </td>
                            </thead>
                            <tbody id="table_body">
                                @foreach ($rows as $i => $row)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{$row['name']}}</td>
                                        <td>{{$row['description']}}</td>
                                        <td>
                                            <div class="d-flex gap-3">
                                                @if ($permission->Check('recurring_schedule_template','edit') OR ($permission->Check('recurring_schedule_template','edit_own') AND $row.is_owner === TRUE))
                                                 <a href="{{ route('schedule.edit_recurring_schedule_template.edit', ['id' => $row['id']]) }}" class="text-decoration-underline">[ Edit ]</a>
                                                @endif
                                                @if ($permission->Check('recurring_schedule','view') OR ($permission->Check('recurring_schedule','view_own') ))
                                                 <a href="/schedule/recurring_schedule_control_list?filter_template_id={{$row['id']}}" class="text-decoration-underline">[ Recurring Schedules ]</a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="ids[]" value="{{$row['id']}}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tr>
                                <td class="tblActionRow" colspan="15">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($permission->Check('recurring_schedule_template','add'))
                                            <input type="submit" class="btn btn-primary" name="action" value="Copy">
                                        @endif
                                        @if ($permission->Check('recurring_schedule_template','delete') OR $permission->Check('recurring_schedule_template','delete_own'))
                                            <input type="submit" class="btn btn-danger" name="action" value="Delete" onClick="return confirmSubmit()">
                                        @endif
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const tableBody = document.getElementById("table_body");
        if (tableBody && tableBody.children.length === 0) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td colspan="7" class="text-center text-danger font-weight-bold">No Recurring Schedule.</td>
            `;
            tableBody.appendChild(row);
        }
    </script>

</x-app-layout>
