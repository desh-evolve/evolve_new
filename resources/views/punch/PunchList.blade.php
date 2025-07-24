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

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="/policy/policy_groups/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Policy Group <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <form method="get" action="/attendance/punchlist">
                            @csrf
                            <table id="punchlist_table" class="table nowrap align-middle" style="width:100%">
                                <thead class="bg-primary text-white">
                                    <th>#</th>
                                    <th>First Name </th>
                                    <th>Last Name </th>
                                    {{-- <th>Title </th> --}}
                                    {{-- <th>Group </th> --}}
                                    {{-- <th>Default Branch </th> --}}
                                    {{-- <th>Default Department </th> --}}
                                    {{-- <th>Branch </th> --}}
                                    {{-- <th>Department </th> --}}
                                    <th>Type </th>
                                    <th>Status </th>
                                    {{-- <th>Date </th> --}}
                                    <th>Time </th>
                                    <th>Functions </th>
                                    <th>
                                        <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                    </th>
                                </thead>
                                @foreach ($rows as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }} </td>
                                        <td>{{ $row['first_name'] }}</td>
                                        <td>{{ $row['last_name'] }}</td>
                                        {{-- <td>{{ $row['title'] }}</td> --}}
                                        {{-- <td>{{ $row['group'] }}</td> --}}
                                        {{-- <td>{{ $row['default_branch'] }}</td> --}}
                                        {{-- <td>{{ $row['default_department'] }}</td> --}}
                                        {{-- <td>{{ $row['branch'] }}</td> --}}
                                        {{-- <td>{{ $row['department'] }}</td> --}}
                                        <td>{{ $row['type_id'] }}</td>
                                        <td>{{ $row['status_id'] }}</td>
                                        {{-- <td>{{ $row['date_stamp'] }}</td> --}}
                                        <td>{{ $row['time_stamp'] }}</td>
                                        <td>
                                            <a class="btn btn-info btn-sm" href="/attendance/timesheet?filter_data[user_id]={{$row['user_id']}}&filter_data[date]={{$row['date_stamp']}}">View</a>
                                            <button class="btn btn-secondary btn-sm" onclick="editPunch({{$row['id']}},'','','','')">Edit</button>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="ids[]" value="{{$row['id']}}">
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="tblActionRow" colspan="{{$total_columns}}">
                                        @if( $permission->Check('punch','delete') OR $permission->Check('punch','delete_own') OR $permission->Check('punch','delete_child'))
                                            <input type="submit" name="action" value="Delete" onClick="return confirmSubmit()">
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            function initTable(){
                new DataTable("#punchlist_table", { 
                    scrollX: !0,
                    dom: "Bfrtip",
                    buttons: ["copy", "csv", "excel", "print", "pdf"],
                    //fixedHeader: !0
                })
            }

            initTable();

            @if(request()->get('refresh') == 'true')
                if (window.opener) {
                    console.log('refreshing..')
                    window.opener.location.reload();
                }
            @endif
        })

        function editPunch(punchID,punchControlId,userID,date,statusID) {
            try {
                eP=window.open('/attendance/punch/add?id='+encodeURI(punchID)+'&punch_control_id='+encodeURI(punchControlId)+'&user_id='+encodeURI(userID)+'&date_stamp='+encodeURI(date)+'&status_id='+encodeURI(statusID),"Edit_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=800,height=625,resizable=1");
                if (window.focus) {
                    eP.focus()
                }
            } catch (e) {
                //DN
            }
        }
    </script>
</x-app-layout>