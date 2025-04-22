<x-app-layout :title="'Input Example'">

    {*{include file="leaves/EditAbsenceLeaveUser.js.tpl"}*}
    @include('leaves/EditAbsenceLeaveUser_js')

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
                            @if (!$cdf->Validator->isValid())
                                {{-- add form errors list --}}
                            @endif
            
                            <table class="editTable">
                
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <select id="status_id" name="data[status_id]">
                                            @foreach ($data['status_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['status']) && $id == $data['status'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>Absence Policy:</th>
                                    <td>
                                        <select id="type_id" name="data[absence_policy_id]">
                                            @foreach ($data['absence_policy_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['absence_policy_id']) && $id == $data['absence_policy_id'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>Name:</th>
                                    <td>
                                        <input type="text" size="30" name="data[name]" value="{{$data['name']}}">
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>Leave Year:</th>
                                    <td>
                                        <input type="text" size="30" name="data[leave_date_year]" value="{{$data['leave_date_year']}}">
                                    </td>
                                </tr>
                
                                <tr class="bg-primary text-white">
                                    <td colspan="2" >
                                        Eligibility Criteria
                                    </td>
                                </tr>
                
                                {{-- 
                                    <tr>
                                        <th>Start Date:</th>
                                        <td>
                                            <input type="text" size="15" id="start_date" name="data[start_date]" value="{getdate type="DATE" epoch=$data.start_date}">
                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
                                            ie: {{$current_user_prefs->getDateFormatExample()}} <b>(Leave blank for no start date)</b>
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <th>End Date:</th>
                                        <td>
                                            <input type="text" size="15" id="end_date" name="data[end_date]" value="{getdate type="DATE" epoch=$data.end_date}">
                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
                                            ie: {{$current_user_prefs->getDateFormatExample()}} <b>(Leave blank for no end date)</b>
                                        </td>
                                    </tr>
                                --}}

                                <tr>
                                    <th>Basis of Employment:</th>
                                    <td>
                                        <select id="basis_employment" name="data[basis_employment]">
                                            @foreach ($data['basis_employment_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['basis_employment']) && $id == $data['basis_employment'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Applicable:</th>
                                    <td>
                                        <select id="type_id" name="data[leave_applicable]">
                                            @foreach ($data['leave_applicable_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['leave_applicable']) && $id == $data['leave_applicable'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Minimum Length Of Service:</th>
                                    <td>
                                        <input size="3" type="text" name="data[minimum_length_of_service]" value="{$data.minimum_length_of_service}">
                                        <select id="" name="data[minimum_length_of_service_unit_id]">
                                            @foreach ($data['length_of_service_unit_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['minimum_length_of_service_unit_id']) && $id == $data['minimum_length_of_service_unit_id'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>Maximum Length Of Service:</th>
                                    <td>
                                        <input size="3" type="text" name="data[maximum_length_of_service]" value="{$data.maximum_length_of_service}">
                                        <select id="" name="data[maximum_length_of_service_unit_id]">
                                            @foreach ($data['length_of_service_unit_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['maximum_length_of_service_unit_id']) && $id == $data['maximum_length_of_service_unit_id'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                {{--             
                                    <tr>
                                        <th>Minimum Employee Age:</th>
                                        <td>
                                            <input size="3" type="text" name="data[minimum_user_age]" value="{{$data['minimum_user_age']}}"> years
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <th>Maximum Employee Age:</th>
                                        <td>
                                            <input size="3" type="text" name="data[maximum_user_age]" value="{{$data['maximum_user_age']}}"> years
                                        </td>
                                    </tr>
                                --}}
                                
                                <tr class="bg-primary text-white">
                                    <td colspan="2" >
                                        Calculation Criteria
                                    </td>
                                </tr>
                        
                                <tr>
                                    <th>Number of leave :</th>
                                    <td>
                                        <input type="text" size="10" name="data[amount]" value="{{$data['amount']}}">
                                    </td>
                                </tr>
                                                
                                <tr>
                                    <th>Employees:</th>
                                    <td colspan="3">
                                        <div class="col-md-12">
                                            <x-general.multiselect-php 
                                                title="Employees" 
                                                :data="$data['user_options']" 
                                                :selected="!empty($data['user_ids']) ? array_values($data['user_ids']) : []" 
                                                :name="'data[user_ids][]'"
                                                id="userSelector"
                                            />
                                        </div>
                                    </td>
                                </tr>
                
                            </table>
                        </div>
                
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
                        </div>
                    
                        <input type="hidden" id="id" name="data[id]" value="{{$data['id']}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            showCalculation(); 
            filterIncludeCount(); 
            filterExcludeCount(); 
            filterUserCount();
        })
    </script>
</x-app-layout>