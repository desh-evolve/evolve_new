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

                    <table class="table table-striped table-bordered">
                        <thead class="bg-primary text-white">
                            <th>#</th>
                            <th>Name </th>
                            <th>Description</th>
                            <th>Functions</th>
                        </thead>
                        <tbody id="table_body">
                            @foreach ($rows as $i => $row)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{$row['name']}}</td>
                                    <td>{{$row['description']}}</td>
                                    <td>
                                        @if ($permission->Check('recurring_schedule_template','edit') OR ($permission->Check('recurring_schedule_template','edit_own') AND $row.is_owner === TRUE))
                                            {{-- [ <a href="/schedule/edit_recurring_schedule_template?id={{$row['id']}}" >Edit</a> ] --}}
                                            <a href="{{ route('schedule.edit_recurring_schedule_template.edit', ['id' => $row['id']]) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        @endif
                                        @if ($permission->Check('recurring_schedule','view') OR ($permission->Check('recurring_schedule','view_own') ))
                                            <a href="/schedule/recurring_schedule_control_list?filter_template_id={{$row['id']}}" class="btn btn-warning btn-sm">Recurring Schedules</a>
                                            {{-- <a href="{{ route('schedule.edit_recurring_schedule.add', ['id' => $row['id']]) }}" class="btn btn-warning btn-sm">Recurring Schedules</a> --}}
                                        @endif

                                        @if ($permission->Check('recurring_schedule_template','delete') OR $permission->Check('recurring_schedule_template','delete_own'))
                                            <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/schedule/recurring_schedule_template_control/delete/{{ $row['id'] }}', 'Recurring Schedule', this)">
                                                Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tr>
                            <td class="tblActionRow" colspan="7">
                                @if ($permission->Check('recurring_schedule_template','add'))
                                    <input type="submit" class="button" name="action:add" value="Add">
                                    <input type="submit" class="button" name="action:copy" value="Copy">
                                @endif
                            </td>
                        </tr>
                    </table>
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
