<x-app-layout :title="'Input Example'">

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
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
                        <div id="contentBoxTwoEdit">            
                            <table class="table table-bordered">
                                @if (isset($data['msg']) &&  $data['msg'] !='')
                                    <tr class="tblDataWarning">
                                        <td colspan="100" valign="center">
                                            <br>
                                            <b>{{$data['msg']}}</b>
                                            <br>&nbsp;
                                        </td>
                                    </tr>
                                @endif
                                    
                                <tr id="row">
                                    <thead id="row">
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>method</th>
                                        <th>Leave start date</th>
                                        <th>Leave End Date</th>
                                        <th>No Days</th>
                                        <th>Approve</th>
                                    </thead>
                                </tr>
                                @foreach ($data['leaves'] as $row)
                                    <tr id="row">
                                        <td class="cellRightEditTable">{{$row['user']}}</td>
                                        <td class="cellRightEditTable">{{$row['leave_name']}}</td>
                                        <td class="cellRightEditTable">{{$row['leave_method']}}</td>
                                        <td class="cellRightEditTable">{{$row['start_date']}}</td>
                                        <td class="cellRightEditTable">{{$row['end_date']}}</td>
                                        <td class="cellRightEditTable">{{$row['amount']}}</td>
                                        <td class="cellRightEditTable">
                                            <input type="checkbox" size="10" name="data[leave_request][{{$row['id']}}]" value="{{$row['is_supervisor_approved']}}" {{ $row['is_supervisor_approved'] ? 'checked' : '' }}>
                                        </td>
                                        <td class="cellRightEditTable">
                                            <a href="" onclick="javascript:viewNumberLeave({{$row['id']}});">Leave</a>&emsp;
                                            <a href="" onclick="javascript:viewLeave({{$row['id']}});">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                        
                            </table>                 
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
                            <input type="submit" class="" name="action:rejected" value="Rejected" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
                        </div>
                
                        <input type="hidden" id="id" name="data[id]" value="{{$data['id']}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>