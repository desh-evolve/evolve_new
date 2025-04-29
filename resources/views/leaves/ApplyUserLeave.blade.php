<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="post" id="frmleave" name="frmleave" action="#">
                        <div id="contentBoxTwoEdit">

                            @if (!$user->Validator->isValid())
                                {{-- validator errors here --}}
                            @endif
                            <table class="table table-bordered">
                                <tr>
                                    @if (isset($data['msg']) &&  $data['msg'] !='')
                                        <tr class="tblDataWarning">
                                            <td colspan="100" valign="center">
                                                <br>
                                                <b>{{$data['msg']}}</b>
                                                <br>&nbsp;
                                            </td>
                                        </tr>
                                    @endif

                                    <table class="editTable">
                                        <tr>
                                            <th>Name:</th>
                                            <td>
                                                <input type="text" size="30" name="data[name]" value="{{$data['name']}}" readonly="readonly">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Designation:</th>
                                            <td>
                                                <input type="text" size="30" name="data[title]" value="{{$data['title']}}" readonly="readonly">
                                                <input type="hidden" size="30" name="data[title_id]" value="{{$data['title_id']}}" >
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Leave Type:</th>
                                            <td>
                                                <select id="data[leave_type]" name="data[leave_type]">
                                                    @foreach ($data['leave_options'] as $id => $name )
                                                    <option 
                                                        value="{{$id}}"
                                                        @if(!empty($data['leave_type']) && $id == $data['leave_type'])
                                                            selected
                                                        @endif
                                                    >{{$name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Leave Methord:</th>
                                            <td>
                                                <select id="data[method_type]" name="data[method_type]" onChange="UpdateTotalLeaveTime();">
                                                    @foreach ($data['method_options'] as $id => $name )
                                                    <option 
                                                        value="{{$id}}"
                                                        @if(!empty($data['method_type']) && $id == $data['method_type'])
                                                            selected
                                                        @endif
                                                    >{{$name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Number Of Datys:</th>
                                            <td>
                                                <input type="text" size="30"  name="data[no_days]" id="no_days" value="{{$data['no_days']}}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                Leave Dates:
                                            </th>
                                            <td>
                                                <div id="mdp-demo"></div>
                                                <input type="text" size="30" id="altField" name="data[leave_start_date]" value="">
                                                ie: {{$current_user_prefs->getDateFormatExample()}}
                                            </td>
                                        </tr>      
                                        <tr id="rwtime" style="" >
                                            <th>
                                                Start Time:
                                            </th>
                                            <td>
                                                <input id="appt-time" disabled="disabled" type="time" name="data[appt-time]" value="{{$data['appt_time']}}">
                                            </td>
                                        </tr>       
                                        <tr id="rwendtime" style="" >
                                            <th>
                                                End Time:
                                            </th>
                                            <td>
                                                <input id="end-time" disabled="disabled" type="time" name="data[end-time]" value="{{$data['end_time']}}">
                                            </td>
                                        </tr>             
                                        <tr>
                                            <th>
                                                Reason:
                                            </th>
                                            <td>
                                                <textarea rows="5" cols="45" name="data[reason]" id="reason"> {{$data['reason'] ?? ''}} </textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                Address/ Tel. No While On Leave:
                                            </th>
                                            <td>
                                                <input type="text" size="30" name="data[address_tel]" value="{{$data['address_tel']}}">
                                            </td>
                                        </tr>
                                                        
                                        <tr>
                                            <th>
                                                Agreed to Cover Duties :
                                            </th>
                                            <td>
                                                <select id="data[cover_duty]"  name="data[cover_duty]">
                                                    @foreach ($data['users_cover_options'] as $id => $name )
                                                    <option 
                                                        value="{{$id}}"
                                                        @if(!empty($data['cover_duty']) && $id == $data['cover_duty'])
                                                            selected
                                                        @endif
                                                    >{{$name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                                        
                                        <tr>
                                            <th>
                                                Supervisor  :
                                            </th>
                                            <td>
                                                <select id="data[supervisor]" name="data[supervisor]">
                                                    @foreach ($data['users_cover_options'] as $id => $name )
                                                    <option 
                                                        value="{{$id}}"
                                                        @if(!empty($data['supervisor']) && $id == $data['supervisor'])
                                                            selected
                                                        @endif
                                                    >{{$name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <th></th>
                                            <td>
                                                <input type="submit" class="btnSubmit" id="btnSubmit" name="action:submit" value="Submit" onClick="">
                                            </td>
                                        </tr>
                        
                                    </table>
                                                    
                                    <table class="tblList">
                                        <tr id="row">
                                            <thead id="row">
                                                <th></th>
                                                @foreach ($header_leave as $row)
                                                    <th>{{$row['name']}}</th>
                                                @endforeach
                                            </thead>
                                        </tr>
                                        <tr id="row">
                                            <td class="">Leave Entitlement</td>
                                            @foreach ($total_asign_leave as $row)
                                                <td>{{$row['asign']}}</td>
                                            @endforeach
                                        </tr>
                                        <tr id="row">
                                            <td class="">Leave Taken</td>
                                            @foreach ($total_taken_leave as $row)
                                                <td>{{$row['taken']}}</td>
                                            @endforeach
                                        </tr>
                                        <tr id="row">
                                            <td >Balance</td>
                                            @foreach ($total_balance_leave as $row)
                                                <td>{{$row['balance']}}</td>
                                            @endforeach
                                        </tr>
                                    </table>
                                </tr>    
                            </table>
                        </div>
                                                    
                        <div>
                            <table class="tblList">
                                    <tr id="row">
                                        <th>Name</th>
                                        <th>Leave Type</th>
                                        <th>Amount</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                    </tr>
                                    @foreach ($leave_request as $row)
                                        <tr id="row">
                                            <td>{{$row['name']}}</td>
                                            <td>{{$row['leave_type']}}</td>
                                            <td>{{$row['amount']}}</td>
                                            <td>{{$row['from']}}</td>
                                            <td>{{$row['to']}}</td>
                                            <td>{{$row['status']}}</td>
                                        </tr>
                                    @endforeach
                            </table>                            
                        </div>
                        <input type="hidden" id="id" name="data[id]" value="{{$data['id']}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script	language=JavaScript>
    
        var hwCallback = {
          
            getAbsenceLeaveMethod: function(result) {
                if ( result == false ) {
                    result = '0';
                } 
                              
                var h = Math.floor(result / 3600);
                var m = Math.floor(result % 3600 / 60);
                
                if(Math.floor(result ) == 28800){
                    document.getElementById('no_days').value = '1';
                }
                else if(Math.floor(result ) == 14400){
                    document.getElementById('no_days').value = '0.5';
                }
                else if(Math.floor(result ) == 5400){
                    document.getElementById('no_days').value = '1';
                }
    
            },
              
        }
              
              
              
        var remoteHW = new AJAX_Server(hwCallback);
      
        function UpdateTotalLeaveTime() {
            var selectedLeaveId = document.getElementById('data[method_type]').value;
            remoteHW.getAbsenceLeaveMethod(selectedLeaveId);
            
            var ele = document.getElementById("appt-time");
            var ele_end = document.getElementById("end-time");
            
            if(selectedLeaveId == 3){
                // ele.style.display = "block";
                ele.disabled = false;
                ele_end.disabled = false;
                //ele.readonly = false;
            }
            else{
                ele.disabled = true;
                ele_end.disabled = true;
                //ele.readonly = true;
            }
        }
      
        window.onload = function() {
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            // history.replaceState("", "", "/the/result/page");
            }
        }
      
        $( document ).ready(function() {
            $('#mdp-demo').multiDatesPicker({
            //minDate: 0, // today
            //maxDate: 30, // +30 days from today 
                dateFormat: "yy-mm-dd",
                altField: '#altField',
                    onSelect: function() {
                        var $this = $(this);
                        var e = document.getElementById("no_days");
                        
                        var eMethod  = document.getElementById("data[method_type]");
                        var strMethod = eMethod.options[eMethod.selectedIndex].value;
                
                    if(strMethod ==0){
                            
                            alert("Please select leave method");
                            for(var i = 0; i < this.multiDatesPicker.dates.picked.length; i++)
                    return this.multiDatesPicker.dates.picked.splice(i, 1).pop();
                    }
                    else if(strMethod ==1){
                        e.value = this.multiDatesPicker.dates.picked.length;
                    }
                    else if(strMethod ==2 || strMethod ==3){
                        
                        if(this.multiDatesPicker.dates.picked.length >0){
                        var pick_date = this.multiDatesPicker.dates.picked[0];
                        
                        for(var i = 0; i < this.multiDatesPicker.dates.picked.length; i++)
                            return this.multiDatesPicker.dates.picked.splice(i, 1).pop();
                                            
                            this.multiDatesPicker.dates.picked.push(pick_date);
                    }
                    }
                        
                    }
            });
            
            
            $( "#no_days" ).keydown(function(event ) {
                
                var e = document.getElementById("data[method_type]");
                var strMethod = e.options[e.selectedIndex].value;
                
                var et = document.getElementById("data[leave_type]");
                var strType = et.options[et.selectedIndex].value;
                
                var submit = document.getElementById("btnSubmit");
                
                if(strType == 0){
                    
                    submit.disabled = true;
                    alert("Please select leave type");
                }
                else if(strMethod == 0){
                    
                    submit.disabled = true;
                    alert("Please select leave method");
                }
                else if(strMethod == 1){
                    submit.disabled = false;
                    var num = event.value;
                    var amt = parseFloat(this.value);
                    if(isNaN(amt)) {
                    $(this).val('');
                    }
                    else{
                    $(this).val( amt.toFixed(0));
                    }
                    //$(this).val("");
                    //$(this).val(num);
                }
                
                
            });
            
            
            $( "#frmleave" ).submit(function( event ) {
                
                
                
                
                var e = document.getElementById("data[method_type]");
                var strMethod = e.options[e.selectedIndex].value;
                
                var et = document.getElementById("data[leave_type]");
                var strType = et.options[et.selectedIndex].value;
                
                
                var ecover = document.getElementById("data[cover_duty]");
                var streCover = ecover.options[ecover.selectedIndex].value;
                
                var esupevisor = document.getElementById("data[supervisor]");
                var strSupervisor = esupevisor.options[esupevisor.selectedIndex].value;
                
                var noDays = document.getElementById("no_days");
                var selectDays = document.getElementById("altField");
                var reason = $("#reason").val().trim().length;
                
                
                if(strType == 0){
                    
                    
                    alert("Please select leave type");
                    event.preventDefault();
                }
                else if(strMethod == 0){
                    
                    alert("Please select leave method");
                    event.preventDefault();
                }
                else if(noDays.value === ''){
                    alert("Number Of Days Empty");
                    event.preventDefault();
                }
                else if(selectDays.value === ''){
                    alert("Please select date");
                    event.preventDefault();
                }
                else if(reason < 1){
                    
                    alert("Reason is Empty");
                    event.preventDefault();
                }
                else if(streCover == 0){
                    
                    alert("Please select leave cover duty");
                    event.preventDefault();
                }
                else if(strSupervisor == 0){
                    
                    alert("Please select leave supervisor");
                    event.preventDefault();
                }
                
                    //$( "#btnSubmit" ).prop("disabled",true);
                
                
                
                
            });
        });
      
    </script>
</x-app-layout>