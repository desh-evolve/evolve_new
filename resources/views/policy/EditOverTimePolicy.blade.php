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
                                href="/policy/policy_groups/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Policy Group <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('policy.overtime_policies.submit', $data['id']) : route('policy.overtime_policies.submit') }}">
                        @csrf

                        @if (!$otpf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="data[name]" 
                                value="{{ $data['name'] ?? '' }}"
                                placeholder="Enter Overtime Policy Name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <select 
                                id="type_id" 
                                class="form-select" 
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
                        </div>
        
                        <div class="form-group">
                            <label for="trigger_time">Active After (hh:mm (2:15))</label>
                            <input 
                                type="text" 
                                size="8"
                                class="form-control" 
                                name="data[trigger_time]" 
                                value="{{ $data['trigger_time'] ?? '00:00' }}"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="max_time">Max Time (hh:mm (2:15))</label>
                            <input 
                                type="text" 
                                size="8"
                                class="form-control" 
                                name="data[max_time]" 
                                value="{{ $data['max_time'] ?? '00:00' }}"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="rate">Rate</label>
                            <input 
                                type="text" 
                                size="8"
                                class="form-control" 
                                name="data[rate]" 
                                value="{{ $data['rate'] ?? '1.00' }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="wage_group">Wage Group</label>
                            <select 
                                id="wage_group" 
                                class="form-select" 
                                name="data[wage_group_id]" 
                            >
                                @foreach ($data['wage_group_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['wage_group_id']) && $id == $data['wage_group_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="pay_stub_entry_name">Pay Stub Account</label>
                            <select 
                                id="pay_stub_entry_name" 
                                class="form-select" 
                                name="data[pay_stub_entry_account_id]" 
                            >
                                @foreach ($data['pay_stub_entry_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['pay_stub_entry_account_id']) && $id == $data['pay_stub_entry_account_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="accrual_policy_id">Accrual Policy</label>
                            <select 
                                id="accrual_policy_id" 
                                class="form-select" 
                                name="data[accrual_policy_id]" 
                                onChange="showAccrualRate()"
                            >
                                @foreach ($data['accrual_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['accrual_policy_id']) && $id == $data['accrual_policy_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div id="accrual_rate" style="display:none">
                            <div class="form-group">
                                <label for="accrual_rate">Accrual Rate</label>
                                <input 
                                    type="text" 
                                    size="8"
                                    class="form-control" 
                                    name="data[accrual_rate]" 
                                    value="{{ $data['accrual_rate'] ?? '1.00' }}"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-primary btnSubmit" name="action:submit" value="Submit">
                        </div>
            
                        <input type="hidden" name="data[id]" value="{{!empty($data['id']) ? $data['id'] : ''}}">
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
            showAccrualRate();
        })

        function showAccrualRate() {
            accrual_policy_id = document.getElementById('accrual_policy_id').value;
        
            if ( accrual_policy_id == 0 ) {
                document.getElementById('accrual_rate').style.display = 'none';
            } else {
                document.getElementById('accrual_rate').className = '';
                document.getElementById('accrual_rate').style.display = '';
            }
        }
    </script>
</x-app-layout>
