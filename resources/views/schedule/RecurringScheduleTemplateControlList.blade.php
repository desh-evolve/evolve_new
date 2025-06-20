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
                        <form method="get" action="{$smarty.server.SCRIPT_NAME}">
                                <tr class="bg-primary text-white">
                                    <td>
                                        #
                                    </td>
                                    <td>
                                        Name
                                    </td>
                                    <td>
                                        Description
                                    </td>
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
                                        <td>
                                            {{$row['name']}}
                                        </td>
                                        <td>
                                            {{$row['description']}}
                                        </td>
                                        <td>
                                            @if ($permission->Check('recurring_schedule_template','edit') OR ($permission->Check('recurring_schedule_template','edit_own') AND $row.is_owner === TRUE))
                                                [ <a href="/schedule/edit_recurring_schedule_template?id={{$row['id']}}" >Edit</a> ]
                                            @endif
                                            @if ($permission->Check('recurring_schedule','view') OR ($permission->Check('recurring_schedule','view_own') ))
                                                [ <a href="/schedule/recurring_schedule_control_list?filter_template_id={{$row['id']}}">Recurring Schedules</a> ]
                                            @endif
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="ids[]" value="{{$row['id']}}">
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="tblActionRow" colspan="7">
                                        @if ($permission->Check('recurring_schedule_template','add'))
                                            <input type="submit" class="button" name="action:add" value="Add">
                                            <input type="submit" class="button" name="action:copy" value="Copy">
                                        @endif
                                        @if ($permission->Check('recurring_schedule_template','delete') OR $permission->Check('recurring_schedule_template','delete_own'))
                                            <input type="submit" class="button" name="action:delete" value="Delete" onClick="return confirmSubmit()">
                                        @endif
                                        @if ($permission->Check('recurring_schedule_template','undelete'))
                                            <input type="submit" class="button" name="action:undelete" value="UnDelete">
                                        @endif
                                    </td>
                                </tr>
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
</x-app-layout>