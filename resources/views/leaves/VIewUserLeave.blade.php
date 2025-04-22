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

                    <form method="post" name="wage" action="#">
                        <div id="contentBoxTwoEdit">
                            @if (!$user->Validator->isValid())
                                {{-- form errors list --}}
                            @endif    
                                                    
                            <table class="editTable">
                                                    
                
                                <tr>
                                    <th>
                                        Name:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="30" name="data[name]" value="{{$data['name']}}" readonly="readonly">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Designation:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="30" name="data[title]" value="{{$data['title']}}" readonly="readonly">
                                        <input type="hidden" size="30" name="data[title_id]" value="{{$data['title_id']}}" >
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Leave Type  :
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select id="status_id" name="data[leave_type]" readonly="readonly" disabled="true">
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
                                    <th>
                                        Leave Methord  :
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select id="data[method_type]" name="data[method_type]" readonly="readonly" onChange="UpdateTotalLeaveTime();">
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
                                    <th>
                                        Number Of Datys:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="30" name="data[no_days]" value="{{$data['no_days']}}" readonly="readonly">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Leave From:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <div id="mdp-demo"></div>
                                        <input type="text" size="15" id="leave_start_date" name="data[leave_start_date]" value="{{$data['leave_start_date']}}" readonly="readonly">
                                                                        
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Leave To:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="15" id="leave_end_date" name="data[leave_end_date]" value="{{$data['leave_end_date']}}" readonly="readonly">
                                                                        
                                    </td>
                                </tr>
                                                
                                <tr id="rwtime" style="" disabled="disabled">
                                    <th>
                                        Start Time:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input id="appt-time" type="time" name="data[appt-time]" value="{{$data['appt_time']}}">
                                                                        
                                    </td>
                                </tr>
                                                
                                <tr id="rwendtime" style="" >
                                    <th>
                                        End Time:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input id="end-time" disabled="disabled" type="time" name="data[end-time]" value="{{$data['end_time']}}">
                                                                        
                                    </td>
                                </tr>
                                                
                                                
                                                
                                <tr>
                                    <th>
                                        Reason:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <textarea rows="5" cols="45" name="data[reason]" readonly="readonly"> {{$data['reason']}} </textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Address/ Tel. No While On Leave:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" size="30" name="data[address_tel]" value="{{$data['address_tel']}}" readonly="readonly">
                                    </td>
                                </tr>
                                                
                                <tr>
                                    <th>
                                        Agreed to Cover Duties :
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select id="status_id" name="data[cover_duty]"  readonly="readonly" disabled="true">
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
                                        Supervised By :
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select id="status_id" name="data[cover_duty]"  readonly="readonly" disabled="true">
                                            @foreach ($data['users_cover_options'] as $id => $name )
                                            <option 
                                                value="{{$id}}"
                                                @if(!empty($data['supervised_by']) && $id == $data['supervised_by'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                            </table>

                        </div>
                
                        
                        <input type="hidden" id="id" name="data[id]" value="{{$data['id']}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script>
       $(document).ready(function() {
           $('#mdp-demo').multiDatesPicker({
                //minDate: 0, // today
                //maxDate: 30, // +30 days from today 
                addDates: [{/literal}{$data.leave_dates}{literal}],
                numberOfMonths: [1,3],
                dateFormat: "yy-mm-dd",
                altField: '#altField',
           });
       });
    </script>
</x-app-layout>