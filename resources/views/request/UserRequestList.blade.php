<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grequest-1">{{__($title)}}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <button 
                                onclick="editRequest();"
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="requestlist_table" class="table nowrap align-middle" style="width:100%">
                            <thead class="bg-primary text-white">
                                <th>#</th>
                                <th>Date </th>
                                <th>Status </th>
                                <th>Type </th>
                                <th>Functions </th>
                            </thead>
                            @foreach ($requests as $index => $request)
                                <tr>
                                    <td>{{ $index + 1 }} </td>
                                    <td>{{ getdate_helper('date', $request['date_stamp']) }}</td>
                                    <td>{{ $request['status'] }}</td>
                                    <td>{{ $request['type'] }}</td>
                                    <td>
                                        @if ($permission->Check('request','view') OR $permission->Check('request','view_own'))
                                             <button type="button" class="btn btn-info btn-sm" onclick="viewRequest({{$request['id']}})">View</button> 
                                        @endif
                                        @if ($permission->Check('request','delete'))
                                            <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/attendance/request/delete/{{ $request['id'] }}', 'Request', this)">Delete</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            function initTable(){
                new DataTable("#requestlist_table", { 
                    scrollX: !0,
                    dom: "Bfrtip",
                    buttons: ["copy", "csv", "excel", "print", "pdf"],
                    //fixedHeader: !0
                })
            }

            initTable();
        })

        function editRequest(requestID,userID) {
            try {
                eR=window.open('/attendance/request/add?id='+ encodeURI(requestID),"Request","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
            } catch (e) {
                //DN
            }
        }
        
        function viewRequest(requestID) {
            try {
                vR=window.open('/attendance/request/view?id='+ encodeURI(requestID),"Request_"+ requestID,"toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
            } catch (e) {
                //DN
            }
        }

    </script>

    
</x-app-layout>