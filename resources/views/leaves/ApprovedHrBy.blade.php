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

                    <form method="post" name="frmleave" action="#">
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
                                        <th>Leave start date</th>
                                        <th>Leave End Date</th>
                                        <th>No Days</th>
                                    </thead>
                                </tr>
                                @foreach ($data['leaves'] as $row)
                                    <tr id="row">
                                        
                                        <td>{{$row['user']}}</td>
                                        <td>{{$row['leave_name']}}</td>
                                        <td>{{$row['start_date']}}</td>
                                        <td>{{$row['end_date']}}</td>
                                        <td>{{$row['amount']}}</td>
                                        <td>
                                            <a href="#" onclick="javascript:viewNumberLeave({{$row['id']}});">Leave</a>&emsp;
                                            <a href="#" onclick="javascript:viewLeave({{$row['id']}});">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                        
                            </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="">
                            <input type="submit" class="" name="action:rejected" value="Rejected" onClick="">
                        </div>
                
                        <input type="hidden" id="id" name="data[id]" value="{{$data['id']}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>