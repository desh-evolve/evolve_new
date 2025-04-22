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

                    <div id="rowContentInner">
                        <form method="post" name="frmleavesearch" action="#">
                            <div id="contentBoxTwoEdit">
                                <table id="content_adv_search" class="editTable" bgcolor="#7a9bbd">
                                    <tr>
                                        <td valign="top" width="50%">
                                            <table class="editTable">
                                                <tr id="tab_row_all" >
                                                    <td class="cellLeftEditTable">
                                                        Start Date:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        <input type="date" id="start_date" name="filter_data[start_date]" value="">
                                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                                    </td>
                                                </tr>
                                                <tr id="tab_row_all">
                                                    <td class="cellLeftEditTable">
                                                        End Date:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        <input type="text" id="end_date" name="filter_data[end_date]" value="">
                                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                                    </td>
                                                </tr>
                                                <tr id="tab_row_all">
                                                    <td class="cellLeftEditTable">
                                                        Employee:
                                                    </td>
                                                    <td class="cellRightEditTable">
                                                        <select name="filter_data[user_id]">
                                                            @foreach ($data['user_options'] as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($data['user_id']) && $id == $data['user_id'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td valign="top" width="50%">
                                            
                                        </td>
                                    </tr>
                                    <td class="tblActionRow" colspan="1">
                                        <input type="submit" name="action:search" value="Search">
                                        <input type="submit" name="action:export" value="Filter Export"> 
                                    </td>                                 
                                </table>                                                                             
                            </div>                                                                   
                        </form>
                        <form method="post" name="frmleavesearch" action="#">
                            <td class="tblActionRow" colspan="1">
                                <input type="submit" name="action:export" value="export"> 
                            </td>
                        </form>
                    </div>
                    <div id="rowContentInner">  
                        <form method="post" name="frmleave" action="#">
                            <div id="contentBoxTwoEdit">                
                                <table class="tblList">
                                    @if (isset($data['msg']) &&  $data['msg'] !='')
                                        <tr class="tblDataWarning">
                                            <td colspan="100" valign="center">
                                                <br>
                                                <b>{$data.msg}</b>
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
                                            
                                            <td class="cellRightEditTable">{{$row['user']}}</td>
                                            <td class="cellRightEditTable">{{$row['leave_name']}}</td>
                                            <td class="cellRightEditTable">{{$row['start_date']}}</td>
                                            <td class="cellRightEditTable">{{$row['end_date']}}</td>
                                            <td class="cellRightEditTable">{{$row['amount']}}</td>
                                    
                                            <td class="cellRightEditTable"><a href="" onclick="javascript:viewNumberLeave({{$row['id']}});">Leave</a>&emsp;<a href="" onclick="javascript:viewLeave({{$row['id']}});">View</a>&emsp;<a href="" onclick="javascript:deleteLeaveConfiremed({{$row['id']}});">Delete</a></td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            <input type="hidden" id="id" name="data[id]" value="{{$data['id']}}">
                        </form>
                    </div>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script>
        function checkInput(){
             
           var ddl = document.getElementById("leave_type");
           var selectedValue = ddl.options[ddl.selectedIndex].value;
           if (selectedValue == 0)
           {
             alert("Please select a Leave type");
             return(false);
           }
           
           var ddl_2 = document.getElementById("method_type");
           var selectedValue2 = ddl_2.options[ddl_2.selectedIndex].value;
           if (selectedValue2 == 0)
           {
             alert("Please select a Leave Methord");
             return(false);
           }
           
            if( document.getElementById("leave_start_date").value == ""){
                
                alert("Please select leave from date");
                return(false);
            }else if( document.getElementById("leave_end_date").value == ""){
                
                alert("Please select leave to date");
                return(false);
            }
            else{
                   return(true);
            }
             
        }     
              
        var hwCallback = {
            deleteLeave: function(result) {
                if ( result == false ) {
                    result = '0';
                } 
            }
        }
                 
        var remoteHW = new AJAX_Server(hwCallback);
        
        function deleteLeaveConfiremed(id) {
            var confim =  deleteLeave(id);
            if(confim){
                
                remoteHW.deleteLeave(id); 
                document.location.reload();
            }
        }
        
    </script>
</x-app-layout>