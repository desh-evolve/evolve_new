<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('payroll.company_deductions.submit', $data['id']) : route('payroll.company_deductions.submit') }}">
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
            
                            <tr><th colspan="2" >Eligibility Criteria</th></tr>
            
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
            {{-- check here --}}
                            <tr>
                                <td class="{isvalid object="udf" label="minimum_user_age" value="cellLeftEditTable"}">
                                    Minimum Employee Age:
                                </td>
                                <td>
                                    <input size="3" type="text" name="data[minimum_user_age]" value="{$data.minimum_user_age}"> years
                                </td>
                            </tr>
            
                            <tr>
                                <td class="{isvalid object="udf" label="maximum_user_age" value="cellLeftEditTable"}">
                                    Maximum Employee Age:
                                </td>
                                <td>
                                    <input size="3" type="text" name="data[maximum_user_age]" value="{$data.maximum_user_age}"> years
                                </td>
                            </tr>
                            <tr>
                                <td class="{isvalid object="udf" label="maximum_user_age" value="cellLeftEditTable"}">
                                    Basis of Employment:
                                </td>
                                <td>
                                    <input type="radio"  name="data[basis_of_employment]" value="1"  {if $data.basis_of_employment =="1"}  checked="checked"  {/if}> Contract <br />
                                                            <input type="radio"  name="data[basis_of_employment]" value="2"  {if $data.basis_of_employment =="2"}  checked="checked"  {/if}> Permanent <br />
                                                            <input type="radio"  name="data[basis_of_employment]" value="3"  {if $data.basis_of_employment =="3"}  checked="checked"  {/if}> All <br />
                                </td>
                            </tr>
            
                            <tr class="tblHeader">
                                <td colspan="2" >
                                    Calculation Criteria
                                </td>
                            </tr>
            
                            <tr>
                                <td class="{isvalid object="cdf" label="calculation" value="cellLeftEditTable"}">
                                    Calculation:
                                </td>
                                <td>
                                    {if $data.id == ''}
                                    <select id="calculation_id" name="data[calculation_id]" onChange="showCalculation()">
                                        {html_options options=$data.calculation_options selected=$data.calculation_id}
                                    </select>
                                    {else}
                                        {$data.calculation_options[$data.calculation_id]}
                                        <input type="hidden" id="calculation_id" name="data[calculation_id]" value="{$data.calculation_id}">
                                    {/if}
                                    <input type="hidden" id="old_calculation_id" value="{$data.calculation_id}">
                                </td>
                            </tr>
            
                            <tbody id="country" {if $data.country == NULL AND $data.country == FALSE}style="display:none"{/if}>
                                <tr>
                                    <td class="{isvalid object="cdf" label="country" value="cellLeftEditTable"}">
                                        Country:
                                    </td>
                                    <td>
                                        {if $data.id == ''}
                                        <select id="country_id" name="data[country]" onChange="showCalculation('country')">
                                            {html_options options=$data.country_options selected=$data.country}
                                        </select>
                                        {else}
                                            {$data.country_options[$data.country]}
                                            <input type="hidden" id="country_id" name="data[country]" value="{$data.country}">
                                        {/if}
                                        <input type="hidden" id="old_country" value="{$data.country}">
                                    </td>
                                </tr>
                            </tbody>
            
                            <tbody id="province" {if $data.calculation_id != 200 AND $data.calculation_id != 300}style="display:none"{/if}>
                                <tr>
                                    <td class="{isvalid object="cdf" label="province" value="cellLeftEditTable"}">
                                        Province / State:
                                    </td>
                                    <td>
                                        {if $data.id == ''}
                                        <select id="province_id" name="data[province]" onChange="showCalculation('province')">
                                            {*{html_options options=$data.province_options selected=$data.province}*}
                                        </select>
                                        {else}
                                            {$data.province_options[$data.province]}
                                            <input type="hidden" id="province_id" name="data[province]" value="{$data.province}">
                                        {/if}
                                        <input type="hidden" id="old_province_id" value="{$data.province}">
                                        <input type="hidden" id="selected_province" value="{$data.province}">
                                    </td>
                                </tr>
                            </tbody>
            
                            <tbody id="district" {if $data.calculation_id != 300}style="display:none"{/if}>
                                <tr>
                                    <td class="{isvalid object="cdf" label="district" value="cellLeftEditTable"}">
                                        District / County:
                                    </td>
                                    <td>
                                        {if $data.id == ''}
                                            <select id="district_id" name="data[district]" onChange="showCalculation('district')">
                                            </select>
                                        {else}
                                            {$data.district_options[$data.district]}
                                            <input type="hidden" id="district_id" name="data[district]" value="{$data.district}">
                                        {/if}
                                        <input type="hidden" id="old_district_id" value="{$data.district}">
                                        <input type="hidden" id="selected_district" value="{$data.district}">
                                    </td>
                                </tr>
                            </tbody>
            
                            {include file="company/EditCompanyDeductionUserValues.tpl"}
            
                            <tr>
                                <td class="{isvalid object="cdf" label="pay_stub_entry_account" value="cellLeftEditTable"}">
                                    Pay Stub Account:
                                </td>
                                <td>
                                    <select name="data[pay_stub_entry_account_id]">
                                        {html_options options=$data.pay_stub_entry_account_options selected=$data.pay_stub_entry_account_id}
                                    </select>
                                </td>
                            </tr>
            
                            <tr>
                                <td class="{isvalid object="cdf" label="calcluorder" value="cellLeftEditTable"}">
                                    Calculation Order:
                                </td>
                                <td>
                                    <input type="text" size="6" name="data[calculation_order]" value="{$data.calculation_order}">
                                </td>
                            </tr>
            
                            <tbody id="filter_include_on" style="display:none" >
                                <tr>
                                    <td class="{isvalid object="cdf" label="include_pay_stub_entry_account" value="cellLeftEditTable"}" nowrap>
                                        <b>Include Pay Stub Accounts:</b><a href="javascript:toggleRowObject('filter_include_on');toggleRowObject('filter_include_off');filterIncludeCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
                                    </td>
                                    <td colspan="3">
                                        <table class="editTable">
                                        <tr class="tblHeader">
                                            <td colspan="3">
                                                Pay Stub Account Value:
                                                <select name="data[include_account_amount_type_id]">
                                                    {html_options options=$data.account_amount_type_options selected=$data.include_account_amount_type_id}
                                                </select>
                                            </td>
                                        </td>
                
                                        <tr class="tblHeader">
                                            <td>
                                                Pay Stub Accounts
                                            </td>
                                            <td>
                                                <br>
                                            </td>
                                            <td>
                                                Included Pay Stub Accounts
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="49%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_include'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_include'))">
                                                <br>
                                                <select name="src_include_id" id="src_filter_include" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.pay_stub_entry_account_options}" multiple>
                                                    {html_options options=$data.include_pay_stub_entry_account_options}
                                                </select>
                                            </td>
                                            <td style="vertical-align: middle;" width="1">
                                                <a href="javascript:moveItem(document.getElementById('src_filter_include'), document.getElementById('filter_include')); uniqueSelect(document.getElementById('filter_include')); sortSelect(document.getElementById('filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
                                                <br>
                                                <a href="javascript:moveItem(document.getElementById('filter_include'), document.getElementById('src_filter_include')); uniqueSelect(document.getElementById('src_filter_include')); sortSelect(document.getElementById('src_filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
                                            </td>
                                            <td width="49%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_include'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_include'))">
                                                <br>
                                                <select name="data[include_pay_stub_entry_account_ids][]" id="filter_include" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.pay_stub_entry_account_options}" multiple>
                                                    {html_options options=$filter_include_options selected=$data.include_pay_stub_entry_account_ids}
                                                </select>
                                            </td>
                                        </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="filter_include_off">
                                <tr>
                                    <td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
                                        <b>Include Pay Stub Accounts:</b><a href="javascript:toggleRowObject('filter_include_on');toggleRowObject('filter_include_off');uniqueSelect(document.getElementById('filter_include'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
                                    </td>
                                    <td colspan="100">
                                        <span id="filter_include_count">0</span> Included Pay Stub Accounts Currently Selected, Click the arrow to modify.
                                    </td>
                                </tr>
                            </tbody>
            
                            <tbody id="filter_exclude_on" style="display:none" >
                                <tr>
                                    <td class="{isvalid object="cdf" label="exclude_pay_stub_entry_account" value="cellLeftEditTable"}" nowrap>
                                        <b>Exclude Pay Stub Accounts:</b><a href="javascript:toggleRowObject('filter_exclude_on');toggleRowObject('filter_exclude_off');filterExcludeCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
                                    </td>
                                    <td colspan="3">
                                        <table class="editTable">
                                        <tr class="tblHeader">
                                            <td colspan="3">
                                                Pay Stub Account Value:
                                                <select name="data[exclude_account_amount_type_id]">
                                                    {html_options options=$data.account_amount_type_options selected=$data.exclude_account_amount_type_id}
                                                </select>
                                            </td>
                                        </td>
                
                                        <tr class="tblHeader">
                                            <td>
                                                Pay Stub Accounts
                                            </td>
                                            <td>
                                                <br>
                                            </td>
                                            <td>
                                                Excluded Pay Stub Accounts
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="49%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_exclude'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_exclude'))">
                                                <br>
                                                <select name="src_exclude_id" id="src_filter_exclude" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.pay_stub_entry_account_options}" multiple>
                                                    {html_options options=$data.exclude_pay_stub_entry_account_options}
                                                </select>
                                            </td>
                                            <td style="vertical-align: middle;" width="1">
                                                <a href="javascript:moveItem(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude')); uniqueSelect(document.getElementById('filter_exclude')); sortSelect(document.getElementById('filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
                                                <br>
                                                <a href="javascript:moveItem(document.getElementById('filter_exclude'), document.getElementById('src_filter_exclude')); uniqueSelect(document.getElementById('src_filter_exclude')); sortSelect(document.getElementById('src_filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
                                            </td>
                                            <td width="49%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_exclude'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_exclude'))">
                                                <br>
                                                <select name="data[exclude_pay_stub_entry_account_ids][]" id="filter_exclude" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.pay_stub_entry_account_options}" multiple>
                                                    {html_options options=$filter_exclude_options selected=$data.exclude_pay_stub_entry_account_ids}
                                                </select>
                                            </td>
                                        </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="filter_exclude_off">
                                <tr>
                                    <td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
                                        <b>Exclude Pay Stub Accounts:</b><a href="javascript:toggleRowObject('filter_exclude_on');toggleRowObject('filter_exclude_off');uniqueSelect(document.getElementById('filter_exclude'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
                                    </td>
                                    <td colspan="100">
                                        <span id="filter_exclude_count">0</span> Excluded Pay Stub Accounts Currently Selected, Click the arrow to modify.
                                    </td>
                                </tr>
                            </tbody>
            
                            <tbody id="filter_user_on" style="display:none" >
                                <tr>
                                    <td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
                                        <b>Employees:</b><a href="javascript:toggleRowObject('filter_user_on');toggleRowObject('filter_user_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
                                    </td>
                                    <td colspan="3">
                                        <table class="editTable">
                                        <tr class="tblHeader">
                                            <td>
                                                Unassigned Employees
                                            </td>
                                            <td>
                                                <br>
                                            </td>
                                            <td>
                                                Assigned Employees
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="49%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_user'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_user'))">
                                                <br>
                                                <select name="src_user_id" id="src_filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
                                                    {html_options options=$data.user_options}
                                                </select>
                                            </td>
                                            <td style="vertical-align: middle;" width="1">
                                                <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
                                                <br>
                                                <a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
                                                <br>
                                                <br>
                                                <br>
                                                <a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
                                            </td>
                                            <td width="49%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_user'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_user'))">
                                                <br>
                                                <select name="data[user_ids][]" id="filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
                                                    {html_options options=$filter_user_options selected=$data.user_ids}
                                                </select>
                                            </td>
                                        </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="filter_user_off">
                                <tr>
                                    <td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
                                        <b>Employees:</b><a href="javascript:toggleRowObject('filter_user_on');toggleRowObject('filter_user_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
                                    </td>
                                    <td colspan="100">
                                        <span id="filter_user_count">0</span> Employees Currently Selected, Click the arrow to modify.
                                    </td>
                                </tr>
                            </tbody>
            
                        </table>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit">
                        </div>
            
                        <input type="hidden" id="id" name="data[id]" value="{{$data['id']}}">
                    </form>


                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>