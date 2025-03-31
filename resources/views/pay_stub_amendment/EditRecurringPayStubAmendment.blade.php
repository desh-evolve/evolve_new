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
                                href="/payroll/recurring_pay_stub_amendment/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('payroll.recurring_pay_stub_amendment.submit', $data['id']) : route('payroll.recurring_pay_stub_amendment.submit') }}">
                        @csrf

                        @if (!$rpsaf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif

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
                            <label for="status_id">Name</label>
                            <input
                                type="text" 
                                size="20" 
                                class="form-control"
                                name="pay_stub_amendment_data[name]"
                                value="{{$pay_stub_amendment_data['name'] ?? ''}}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="status_id">Description</label>
                            <input
                                type="text" 
                                size="50" 
                                class="form-control"
                                name="pay_stub_amendment_data[description]"
                                value="{{$pay_stub_amendment_data['description'] ?? ''}}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="frequency_id">Frequency</label>
                            <select 
                                id="frequency_id" 
                                class="form-select" 
                                name="pay_stub_amendment_data[frequency_id]" 
                            >
                                @foreach ($pay_stub_amendment_data['frequency_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($pay_stub_amendment_data['frequency_id']) && $id == $pay_stub_amendment_data['frequency_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input 
                                type="date" 
                                class="form-control"
                                id="start_date" 
                                name="pay_stub_amendment_data[start_date]" 
                                value="{{$pay_stub_amendment_data['start_date'] ?? date('Y-m-d')}}">
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input 
                                type="date" 
                                class="form-control"
                                id="end_date" 
                                name="pay_stub_amendment_data[end_date]" 
                                value="{{$pay_stub_amendment_data['end_date'] ?? ''}}">
                        </div>


                        <div class="mt-3 mb-3">
                            <label for="user_ids">Employees</label>
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

                        <p class="bg-primary text-white mt-4">Pay Stub Amendment</p>

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

                        <div class="form-group">
                            <label for="type_id">Amount Type</label>
                            <select 
                                id="type_id" 
                                class="form-select" 
                                name="pay_stub_amendment_data[type_id]" 
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

                        <div id="type_id-10" >
                            <div class="form-group">
                                <label for="rate">Rate</label>
                                <input 
                                    type="text" 
                                    class="form-control"
                                    size="15" 
                                    id="rate" 
                                    name="pay_stub_amendment_data[rate]" 
                                    value="{{$pay_stub_amendment_data['rate'] ?? ''}}"
                                    onKeyUp="calcAmount()"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="units">Units</label>
                                <input 
                                    type="text" 
                                    class="form-control"
                                    size="15" 
                                    id="units" 
                                    name="pay_stub_amendment_data[units]" 
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
                                    id="amount" 
                                    name="pay_stub_amendment_data[amount]" 
                                    value="{{$pay_stub_amendment_data['amount'] ?? ''}}"
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
                                    id="percent_amount" 
                                    name="pay_stub_amendment_data[percent_amount]" 
                                    value="{{$pay_stub_amendment_data['percent_amount'] ?? ''}}"
                                >%
                            </div>

                            <div class="form-group">
                                <label for="percent_amount_entry_name_id">Percent Of</label>
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

                        <div class="form-group">
                            <label for="ps_amendment_description">Description</label>
                            <input 
                                type="text" 
                                class="form-control"
                                size="50" 
                                id="ps_amendment_description" 
                                name="pay_stub_amendment_data[ps_amendment_description]" 
                                value="{{$pay_stub_amendment_data['ps_amendment_description'] ?? ''}}"
                            >
                        </div>

                        <div id="contentBoxFour">
                            <input type="submit" class="btn btn-primary btn-sm" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))" >
                            <a href="/payroll/recurring_pay_stub_amendment/recalculate/{{$pay_stub_amendment_data['id'] ?? ''}}" class="btn btn-primary btn-sm" name="action:Recalculate">Recalculate</a>
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
            filterUserCount();
        })

        function filterUserCount() {
            total = countSelect(document.getElementById('filter_user'));
            writeLayer('filter_user_count', total);
        }
        
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
        
        /*
        function calcAmount() {
            if ( document.getElementById('rate').value != '' || document.getElementById('units').value != '' ) {
                document.getElementById('amount').disabled = true;
            } else {
                document.getElementById('amount').disabled = false;
            }
        
            if ( document.getElementById('rate').value != '' && document.getElementById('units').value != '' ) {
                amount = document.getElementById('rate').value * document.getElementById('units').value;
                document.getElementById('amount').value = amount.toFixed(2);
            }
        }
        */
        
        function calcAmount() {
            //Round rate and units to 2 decimals
            rate = MoneyFormat( document.getElementById('rate').value );
            units = MoneyFormat( document.getElementById('units').value );
        
            if ( ( document.getElementById('rate').value != '' && rate > 0 )
                    || ( document.getElementById('units').value != '' && units > 0 ) ) {
                document.getElementById('amount').disabled = true;
        
                amount = rate * units;
                document.getElementById('amount').value = amount.toFixed(2);
            } else {
                document.getElementById('amount').disabled = false;
            }
        }
        
        function MoneyFormat( val ) {
            if ( val != '' ) {
                return parseFloat( val ).toFixed(2);
            }
        
            return '';
        }
        
    </script>
</x-app-layout>
