<x-app-layout :title="'Input Example'">

    @include('company/EditCompanyDeduction_js', $data)

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
                    
                    <form method="POST"
                        action="{{ !empty($data['id']) ? route('payroll.company_deductions.submit', $data['id']) : route('payroll.company_deductions.submit') }}">
                        @csrf

                        @if (!$cdf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif

                        <table class="table">
            
                            <tr>
                                <th>Status</th>
                                <td>
                                    <select id="status_id"  name="data[status_id]" >
                                        @foreach ($data['status_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['status_id']) && $id == $data['status_id'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
            
                            <tr>
                                <th>Type</th>
                                <td>
                                    <select id="type_id"  name="data[type_id]" >
                                        @foreach ($data['type_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['type_id']) && $id == $data['type_id'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
            
                            <tr>
                                <th>Name</th>
                                <td>
                                    <input type="text" size="30" name="data[name]" value="{{$data['name'] ?? ''}}">
                                </td>
                            </tr>
            
                            <tr class="bg-primary text-white">
                                <th colspan="2" >
                                    Eligibility Criteria
                                </th>
                            </tr>
            
                            <tr>
                                <th>Start Date</th>
                                <td>
                                    <input type="date" id="start_date" name="data[start_date]" value="{{$data['start_date'] ?? ''}}">
                                    <b>(Leave blank for no start date)</b>
                                </td>
                            </tr>
            
                            <tr>
                                <th>End Date</th>
                                <td>
                                    <input type="date" id="end_date" name="data[end_date]" value="{{$data['end_date'] ?? ''}}">
                                    <b>(Leave blank for no end date)</b>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Minimum Length Of Service:
                                </th>
                                <td>
                                    <input size="3" type="text" name="data[minimum_length_of_service]" value="{{$data['minimum_length_of_service']}}">

                                    <select id="minimum_length_of_service_unit_id"  name="data[minimum_length_of_service_unit_id]" >
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
                                <th>
                                    Maximum Length Of Service:
                                </th>
                                <td>
                                    <input size="3" type="text" name="data[maximum_length_of_service]" value="{{$data['maximum_length_of_service']}}">
                                    <select id="maximum_length_of_service_unit_id"  name="data[maximum_length_of_service_unit_id]" >
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
                            
                            <tr>
                                <th>Minimum Employee Age</th>
                                <td><input size="3" type="text" name="data[minimum_user_age]" value="{{$data['minimum_user_age']}}"> years </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Maximum Employee Age:
                                </th>
                                <td>
                                    <input size="3" type="text" name="data[maximum_user_age]" value="{{$data['maximum_user_age']}}"> years
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Basis of Employment:
                                </th>
                                <td>
                                    <input type="radio"  name="data[basis_of_employment]" value="1" {{$data['basis_of_employment'] =="1" ? 'checked' : ''}} > Contract <br />
                                    <input type="radio"  name="data[basis_of_employment]" value="2"  {{$data['basis_of_employment'] =="2" ? 'checked' : ''}} > Permanent <br />
                                    <input type="radio"  name="data[basis_of_employment]" value="3"  {{$data['basis_of_employment'] =="3" ? 'checked' : ''}} > All <br />
                                </td>
                            </tr>
            
                            <tr class="bg-primary text-white">
                                <th colspan="2" >
                                    Calculation Criteria
                                </th>
                            </tr>
            
                            <tr>
                                <th>
                                    Calculation
                                </th>
                                <td>
                                    @if (empty($data['id']) || $data['id'] == '')
                                        <select id="calculation_id"  name="data[calculation_id]" >
                                            @foreach ($data['calculation_options'] as $id => $name )
                                            <option 
                                                value="{{$id}}"
                                                @if(!empty($data['calculation_id']) && $id == $data['calculation_id'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{$data['calculation_options'][$data['calculation_id']]}}
                                        <input type="hidden" id="calculation_id" name="data[calculation_id]" value="{{$data['calculation_id']}}">
                                    @endif
                                    <input type="hidden" id="old_calculation_id" value="{{$data['calculation_id'] ?? ''}}">
                                </td>
                            </tr>
            
                            <tbody id="country" style="{{empty($data['country']) || ($data['country'] == NULL AND $data['country'] == FALSE) ? "display:none" : ''}}" >
                                <tr>
                                    <th>
                                        Country
                                    </th>
                                    <td>
                                        @if (empty($data['id']) || $data['id'] == '')
                                            <select id="country_id"  name="data[country]" >
                                                @foreach ($data['country_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['country']) && $id == $data['country'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            {{$data['country_options'][$data['country']]}}
                                            <input type="hidden" id="country_id" name="data[country]" value="{{$data['country']}}">
                                        @endif
                                        <input type="hidden" id="old_country" value="{{$data['country'] ?? ''}}">
                                    </td>
                                </tr>
                            </tbody>
            
                            <tbody id="province" style="{{(empty($data['calculation_id']) || ($data['calculation_id'] != 200 AND $data['calculation_id'] != 300)) ? "display:none" : ''}}" >
                                <tr>
                                    <th>
                                        Province / State
                                    </th>
                                    <td>
                                        @if (empty($data['id']) || $data['id'] == '')
                                            <select id="province_id"  name="data[province]" >
                                                {{-- @foreach ($data['province_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['province']) && $id == $data['province'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                                @endforeach --}}
                                            </select>
                                        @else
                                            {{$data['province_options'][$data['province']]}}
                                            <input type="hidden" id="province_id" name="data[province]" value="{{$data['province']}}">
                                        @endif
                                        <input type="hidden" id="old_province_id" value="{{$data['province'] ?? ''}}">
                                        <input type="hidden" id="selected_province" value="{{$data['province'] ?? ''}}">
                                    </td>
                                </tr>
                            </tbody>
            
                            <tbody id="district" style="{{(empty($data['calculation_id']) || $data['calculation_id'] != 300) ? "display: none" : ''}}" >
                                <tr>
                                    <th>
                                        District / County
                                    </th>
                                    <td>
                                        @if (empty($data['id']) || $data['id'] == '')
                                            <select id="district_id"  name="data[district]" >
                                                {{-- @foreach ($data['district_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($data['district']) && $id == $data['district'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                                @endforeach --}}
                                            </select>
                                        @else
                                            {{$data['district_options'][$data['district']]}}
                                            <input type="hidden" id="district_id" name="data[district]" value="{{$data['district']}}">
                                        @endif
                                        <input type="hidden" id="old_district_id" value="{{$data['district'] ?? ''}}">
                                        <input type="hidden" id="selected_district" value="{{$data['district'] ?? ''}}">
                                    </td>
                                </tr>
                            </tbody>
            
                            @include('company/EditCompanyDeductionUserValues')
            
                            <tr>
                                <th>
                                    Pay Stub Account
                                </th>
                                <td>
                                    <select id="pay_stub_entry_account_id"  name="data[pay_stub_entry_account_id]" >
                                        @foreach ($data['pay_stub_entry_account_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['pay_stub_entry_account_id']) && $id == $data['pay_stub_entry_account_id'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Calculation Order
                                </th>
                                <td>
                                    <input type="text" size="6" name="data[calculation_order]" value="{{$data['calculation_order'] ?? ''}}">
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Include Pay Stub Accounts
                                </th>
                                <td colspan="3">
                                    <div class="col-md-12">
                                        <x-general.multiselect-php 
                                            title="Include Pay Stub Account" 
                                            :data="$data['account_amount_type_options']" 
                                            :selected="!empty($data['include_account_amount_type_id']) ? array_values($data['include_account_amount_type_id']) : []" 
                                            :name="'data[include_pay_stub_entry_account_ids][]'"
                                            id="userSelector"
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Exclude Pay Stub Accounts
                                </th>
                                <td colspan="3">
                                    <div class="col-md-12">
                                        <x-general.multiselect-php 
                                            title="Exclude Pay Stub Account" 
                                            :data="$data['account_amount_type_options']" 
                                            :selected="!empty($data['exclude_account_amount_type_id']) ? array_values($data['exclude_account_amount_type_id']) : []" 
                                            :name="'data[exclude_pay_stub_entry_account_ids][]'"
                                            id="userSelector"
                                        />
                                    </div>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Employees
                                </th>
                                <td colspan="3">
                                    <div class="col-md-12">
                                        <x-general.multiselect-php 
                                            title="Pay Stub Account" 
                                            :data="$data['user_options']" 
                                            :selected="!empty($data['user_ids']) ? array_values($data['user_ids']) : []" 
                                            :name="'data[user_ids][]'"
                                            id="userSelector"
                                        />
                                    </div>
                                </td>
                            </tr>
            
                        </table>
            
                        <div class="d-flex justify-content-end">
                            <input type="submit" class="btn btn-primary btn-sm" name="action:submit" value="Submit">
                        </div>
            
                        <input type="hidden" id="id" name="data[id]" value="{{!empty($data['id']) ? $data['id'] : ''}}">
                    </form>


                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>