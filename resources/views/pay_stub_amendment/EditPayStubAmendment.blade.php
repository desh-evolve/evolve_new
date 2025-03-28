<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
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

                    <form method="POST"
                        action="{{ isset($data['id']) ? route('payroll.pay_stub_amendment.submit', $data['id']) : route('payroll.pay_stub_amendment.submit') }}">
                        @csrf

                        @if (!$psaf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif
                        
                        <div class="mt-3 mb-3">
                            <label for="user_ids">Employee(s)</label>
                            <div class="col-md-12">
                                <x-general.multiselect-php 
                                    title="Employees" 
                                    :data="$pay_stub_amendment_data['user_options']" 
                                    :selected="!empty($pay_stub_amendment_data['user_ids']) ? array_values($pay_stub_amendment_data['user_ids']) : []" 
                                    :name="'pay_stub_amendment_data[user_ids][]'"
                                    id="userSelector"
                                />
                            </div>
                        </div>

                        {{-- <div class="form-group">
                            <label for="user_id">Employee</label>
                            <select 
                                id="user_id" 
                                class="form-select" 
                                name="pay_stub_amendment_data[user_id]" 
                            >
                                @foreach ($pay_stub_amendment_data['user_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_stub_amendment_data['user_id']) && $id == $pay_stub_amendment_data['user_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div> --}}

                        <div class="form-group">
                            <label for="status_id">Status</label>
                            <select 
                                id="status_id" 
                                class="form-select" 
                                name="pay_stub_amendment_data[status_id]" 
                            >
                                @foreach ($pay_stub_amendment_data['status_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_stub_amendment_data['status_id']) && $id == $pay_stub_amendment_data['status_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="pay_stub_entry_name_id">Pay Stub Account</label>
                            <select 
                                id="pay_stub_entry_name_id" 
                                class="form-select" 
                                name="pay_stub_amendment_data[pay_stub_entry_name_id]" 
                            >
                                @foreach ($pay_stub_amendment_data['pay_stub_entry_name_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_stub_amendment_data['pay_stub_entry_name_id']) && $id == $pay_stub_amendment_data['pay_stub_entry_name_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bg-primary text-white mt-4">Amount</div>

                        <div class="form-group">
                            <label for="type_id">Amount Type</label>
                            <select 
                                id="type_id" 
                                class="form-select" 
                                name="pay_stub_amendment_data[type_id]" 
                                onChange="showPercent()"
                            >
                                @foreach ($pay_stub_amendment_data['type_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_stub_amendment_data['type_id']) && $id == $pay_stub_amendment_data['type_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="type_id-10">
                            <div class="form-group">
                                <label for="rate">Rate</label>
                                <input 
                                    type="text"
                                    class="form-control"
                                    size="15" 
                                    name="pay_stub_amendment_data[rate]" 
                                    id="rate" 
                                    value="{{$pay_stub_amendment_data['rate'] ?? ''}}" 
                                    onKeyUp="calcAmount()"
                                >
                                <input type="button" name="getUserHourlyRate" value="Get Employee Rate" onclick="getHourlyRate(); return false;"/>
                            </div>

                            <div class="form-group">
                                <label for="units">Units</label>
                                <input 
                                    type="text" 
                                    class="form-control"
                                    size="15" 
                                    name="pay_stub_amendment_data[units]" 
                                    id="units" 
                                    value="{{$pay_stub_amendment_data['units'] ?? ''}}" 
                                    onKeyUp="calcAmount()"
                                >
                            </div>

                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input 
                                    type="text" 
                                    class="form-control"
                                    size="15" 
                                    name="pay_stub_amendment_data[amount]" 
                                    id="amount" 
                                    value="{{$pay_stub_amendment_data['amount'] ?? '0'}}"
                                >
                            </div>

                        </div>

                        <div id="type_id-20" style="display:none">

                            <div class="form-group">
                                <label for="percent_amount">Percent</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    size="10" 
                                    name="pay_stub_amendment_data[percent_amount]" 
                                    value="{{$pay_stub_amendment_data['percent_amount'] ?? '0'}}"
                                >%
                            </div>

                            <div class="form-group">
                                <label for="percent_amount_entry_name_id">Percent</label>
                                <select 
                                    id="percent_amount_entry_name_id" 
                                    class="form-select" 
                                    name="pay_stub_amendment_data[percent_amount_entry_name_id]" 
                                >
                                    @foreach ($pay_stub_amendment_data['percent_amount_entry_name_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($pay_stub_amendment_data['percent_amount_entry_name_id']) && $id == $pay_stub_amendment_data['percent_amount_entry_name_id'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="bg-primary text-white mt-4">Options</div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                size="50" 
                                name="pay_stub_amendment_data[description]" 
                                value="{{$pay_stub_amendment_data['description'] ?? ''}}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="effective_date">Effective Date</label>
                            <input 
                                type="date" 
                                class="form-control" 
                                name="pay_stub_amendment_data[effective_date]" 
                                value="{{$pay_stub_amendment_data['effective_date'] ?? date('Y-m-d')}}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="ytd_adjustment">Year to Date (YTD) Adjustment:</label>
                            <input 
                                type="checkbox" 
                                class="checkbox" 
                                name="pay_stub_amendment_data[ytd_adjustment]" 
                                value="1" {{(!empty($pay_stub_amendment_data['ytd_adjustment']) && $pay_stub_amendment_data['ytd_adjustment'] == TRUE) ? 'checked' : ''}}
                            >
                        </div>

                        <div id="contentBoxFour">
                            <input type="submit" class="btn btn-primary btn-sm" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))" {{(!empty($pay_stub_amendment_data['status_id']) && $pay_stub_amendment_data['status_id'] == 55) ? 'disabled' : ''}}>
                        </div>

                        <input type="hidden" name="pay_stub_amendment_data[id]" value="{{$pay_stub_amendment_data['id'] ?? ''}}">

                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script	language=JavaScript>

        document.addEventListener("DOMContentLoaded", function () {
            showPercent(); 
            calcAmount();
        })

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
</x-app-layout>