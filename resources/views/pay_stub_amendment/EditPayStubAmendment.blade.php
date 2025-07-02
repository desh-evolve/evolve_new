<x-app-layout :title="'Input Example'">

    <script	language="JavaScript">
        function showPercent() {
            if ( document.getElementById('type_id').value == 20 ) {
                document.getElementById('type_id-10').style.display = 'none';
        
                document.getElementById('type_id-20').className = '';
                document.getElementById('type_id-20').style.display = '';
            } else {
                document.getElementById('type_id-20').style.display = 'none';
        
                document.getElementById('type_id-10').className = '';
                document.getElementById('type_id-10').style.display = '';
            }
        }
        
        function calcAmount() {
            //Round rate and units to 2 decimals
            rate = document.getElementById('rate').value;
            units = document.getElementById('units').value;
        
            if ( ( document.getElementById('rate').value != '' && rate > 0 )
                    || ( document.getElementById('units').value != '' && units > 0 ) ) {
                document.getElementById('amount').disabled = true;
        
                amount = rate * units;
                document.getElementById('amount').value = MoneyFormat( amount );
            } else {
                document.getElementById('amount').disabled = false;
            }
        }
        
        var hwCallback = {
                getUserHourlyRate: function(result) {
                    document.getElementById('rate').value = result;
                    calcAmount();
                }
            }
        
        var remoteHW = new AJAX_Server(hwCallback);
        
        function getHourlyRate() {
            if ( document.getElementById('filter_user').options.length == 1 ) {
                user_id = document.getElementById('filter_user').options[0].value
                remoteHW.getUserHourlyRate( user_id, document.getElementById('effective_date').value);
            } else if ( document.getElementById('filter_user').options.length > 1) {
                document.getElementById('rate').value = '';
                alert('{/literal}Unable to obtain rate when multiple employees are selected.{literal}');
            } else {
                document.getElementById('rate').value = '';
                alert('{/literal}Unable to obtain rate when no employee is selected.{literal}');
            }
        }
    </script>

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
                                href="/payroll/pay_stub_amendment/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">

                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="post" action="{{route('payroll.pay_stub_amendment.add')}}">
                        @csrf
                        <div id="contentBoxTwoEdit">
            
                            @if (!$psaf->Validator->isValid())
                                {{-- {include file="form_errors.tpl" object="psaf"} --}}
                            @endif
            
                            <table class="table table-bordered">
            
                            <tr>
                                <th>
                                    Employee(s):
                                </th>
                                <td>
                                    <table class="table table-bordered">
                                        <tr class="bg-primary text-white">
                                            <td>
                                                UnSelected Employees
                                            </td>
                                            <td>
                                                <br>
                                            </td>
                                            <td>
                                                Selected Employees
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="cellRightEditTable" width="50%" align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_user'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_user'))">
                                                <br>
                                                <select name="pay_stub_amendment_data[src_user_id]" id="src_filter_user" style="width:90%;margin:5px 0 5px 0;" size="{{select_size([ 'array'=>$pay_stub_amendment_data['user_options']])}}" multiple>
                                                    {!! html_options([ 'options'=>$pay_stub_amendment_data['user_options']]) !!}
                                                </select>
                                            </td>
                                            <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                                <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{select_size([ 'array'=>$pay_stub_amendment_data['user_options']])}})"> <button type="button" class="btn btn-primary btn-sm"> >> </button> </a>
                                                <br>
                                                <br>
                                                <a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{select_size([ 'array'=>$pay_stub_amendment_data['user_options']])}})"> <button type="button" class="btn btn-primary btn-sm"> << </button> </a>
                                                <br>
                                            </td>
                                            <td class="cellRightEditTable" width="50%"  align="center">
                                                <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_user'))">
                                                <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_user'))">
                                                <br>
                                                <select name="pay_stub_amendment_data[filter_user_id][]" id="filter_user" style="width:90%;margin:5px 0 5px 0;" size="{{select_size([ 'array'=>$pay_stub_amendment_data['user_options']])}}" multiple>
                                                    {!! html_options([ 'options'=>$pay_stub_amendment_data['filter_user_options'], 'selected'=>$filter_user_id ?? '']) !!}
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            {{-- <tr>
                                <td class="{isvalid object="psaf" label="user" value="cellLeftEditTable"}">
                                    Employee:
                                </td>
                                <td class="cellRightEditTable">
                                    <select id="user_id" name="pay_stub_amendment_data[user_id]">
                                        {html_options options=$pay_stub_amendment_data.user_options selected=$pay_stub_amendment_data.user_id}
                                    </select>
                                </td>
                            </tr> --}}
                            <tr>
                                <th>
                                    Status:
                                </th>
                                <td class="cellRightEditTable">
                                    <select name="pay_stub_amendment_data[status_id]">
                                        {!! html_options([ 'options'=>$pay_stub_amendment_data['status_options'], 'selected'=>$pay_stub_amendment_data['status_id'] ?? '']) !!}
                                    </select>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Pay Stub Account:
                                </th>
                                <td class="cellRightEditTable">
                                    <select name="pay_stub_amendment_data[pay_stub_entry_name_id]">
                                        {!! html_options([ 'options'=>$pay_stub_amendment_data['pay_stub_entry_name_options'], 'selected'=>$pay_stub_amendment_data['pay_stub_entry_name_id'] ?? '']) !!}
                                    </select>
                                </td>
                            </tr>
            
                            <tr class="bg-primary text-white">
                                <td colspan="2">
                                    Amount
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Amount Type:
                                </th>
                                <td class="cellRightEditTable">
                                    <select name="pay_stub_amendment_data[type_id]" id="type_id" onChange="showPercent()">
                                        {!! html_options([ 'options'=>$pay_stub_amendment_data['type_options'], 'selected'=>$pay_stub_amendment_data['type_id'] ?? '']) !!}
                                    </select>
                                </td>
                            </tr>
            
                            <tbody id="type_id-10" >
            
                            <tr>
                                <th>
                                    Rate:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="15" name="pay_stub_amendment_data[rate]" id="rate" value="{{$pay_stub_amendment_data['rate'] ?? ''}}" onKeyUp="calcAmount()">
                                    <input type="button" name="getUserHourlyRate" value="Get Employee Rate" onclick="getHourlyRate(); return false;"/>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Units:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="15" name="pay_stub_amendment_data[units]" id="units" value="{{$pay_stub_amendment_data['units'] ?? ''}}" onKeyUp="calcAmount()">
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Amount:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="15" name="pay_stub_amendment_data[amount]" id="amount" value="{{$pay_stub_amendment_data['amount'] ?? ''}}">
                                </td>
                            </tr>
                            </tbody>
            
                            <tbody id="type_id-20" style="display:none" >
                            <tr>
                                <th>
                                    Percent:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="10" name="pay_stub_amendment_data[percent_amount]" value="{{$pay_stub_amendment_data['percent_amount'] ?? ''}}">%
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Percent Of:
                                </th>
                                <td class="cellRightEditTable">
                                    <select name="pay_stub_amendment_data[percent_amount_entry_name_id]">
                                        {!! html_options([ 'options'=>$pay_stub_amendment_data['percent_amount_entry_name_options'], 'selected'=>$pay_stub_amendment_data['percent_amount_entry_name_id'] ?? '']) !!}
                                    </select>
                                </td>
                            </tr>
            
                            </tbody>
            
                            <tr class="bg-primary text-white">
                                <td colspan="2">
                                    Options
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Description:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" size="50" name="pay_stub_amendment_data[description]" value="{{$pay_stub_amendment_data['description'] ?? ''}}">
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Effective Date:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="date" id="effective_date" name="pay_stub_amendment_data[effective_date]" value="{{getdate_helper('date', $pay_stub_amendment_data['effective_date'])}}">
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Year to Date (YTD) Adjustment:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="checkbox" class="checkbox" name="pay_stub_amendment_data[ytd_adjustment]" value="1" {{ (!empty($pay_stub_amendment_data['ytd_adjustment']) && $pay_stub_amendment_data['ytd_adjustment']) ? 'checked' : '' }} >
                                </td>
                            </tr>
            
                        </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="button" name="action" value="Submit" onClick="selectAll(document.getElementById('filter_user'))" {{ (!empty($pay_stub_amendment_data['status_id']) && $pay_stub_amendment_data['status_id'] == 55) ? 'disabled' : '' }} >
                        </div>
            
                        <input type="hidden" name="pay_stub_amendment_data[id]" value="{{$pay_stub_amendment_data['id'] ?? ''}}">
                        {{-- {* <input type="hidden" name="user_id" value="{$user_data->getId()}"> *} --}}
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

</x-app-layout>