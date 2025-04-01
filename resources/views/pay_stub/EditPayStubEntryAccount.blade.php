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
                        action="{{ isset($data['id']) ? route('payroll.paystub_accounts.submit', $data['id']) : route('payroll.paystub_accounts.submit') }}">
                        @csrf

                        @if (!$pseaf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif

                        <table class="table">
                            <tr class="form-group">
                                <th for="status_id">Status</th>
                                <td>
                                    <select 
                                        id="status_id" 
                                        class="" 
                                        name="data[status_id]" 
                                    >
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
    
                            <tr class="form-group">
                                <th for="type_id">Type</th>
                                <td>
                                    <select 
                                        id="type_id" 
                                        class="" 
                                        name="data[type_id]" 
                                    >
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
                                <td><input type="text" size="30" name="data[name]" value="{{$data['name'] ?? ''}}"></td>
                            </tr>
                            <tr>
                                <th>Order</th>
                                <td><input type="text" size="6" id="ps_order" name="data[order]" value="{{$data['order'] ?? ''}}"></td>
                            </tr>

                            <tr id="accrual" style="display:none">
                                <th>Accrual</th>
                                <td>
                                    <select 
                                        id="accrual_id" 
                                        class="" 
                                        name="data[accrual_id]" 
                                    >
                                        @foreach ($data['accrual_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($data['accrual_id']) && $id == $data['accrual_id'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th>Debit Account</th>
                                <td><input type="text" size="40" name="data[debit_account]" value="{{$data['debit_account'] ?? ''}}"></td>
                            </tr>

                            <tr>
                                <th>Credit Account</th>
                                <td><input type="text" size="40" name="data[credit_account]" value="{{$data['credit_account'] ?? ''}}"></td>
                            </tr>
                        </table>
                        
                        <div class="d-flex justify-content-end">
                            <input type="submit" class="btn btn-primary btn-sm" name="action:submit" value="Submit">
                        </div>

                        <input type="hidden" name="data[id]" value="{{$data['id'] ?? ''}}">

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
            showType();
        })

        function showType() {
            if ( document.getElementById('type_id').value == 50 ) {
                document.getElementById('accrual').style.display = 'none';
            } else {
                document.getElementById('accrual').className = '';
                document.getElementById('accrual').style.display = '';
            }
        
            getNextPayStubAccountOrderByTypeId();
        }
        
        var hwCallback = {
            getNextPayStubAccountOrderByTypeId: function(result) {
                if ( result != false ) {
                    document.getElementById('ps_order').value = result;
                }
            }
        }
        
        var remoteHW = new AJAX_Server(hwCallback);
        
        function getNextPayStubAccountOrderByTypeId() {
            remoteHW.getNextPayStubAccountOrderByTypeId( document.getElementById('type_idsv').value );
        }
        </script>
</x-app-layout>