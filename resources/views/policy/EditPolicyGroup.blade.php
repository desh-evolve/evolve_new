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

                    @if (!$pgf->Validator->isValid())
                        <div class="alert alert-danger">
                            <ul>
                                <li>Error list</li>
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST"
                        action="{{ isset($data['id']) ? route('policy.policy_groups.submit', $data['id']) : route('policy.policy_groups.submit') }}">
                        @csrf

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="data[name]" 
                                value="{{ $data['name'] ?? '' }}"
                                placeholder="Enter Policy Group Name"
                            >
                        </div>
                        <div class="mt-3 mb-3">
                            <label for="user_ids">Employees</label>
                            <div class="col-md-12">
                                <x-general.multiselect-php 
                                    title="Employees" 
                                    :data="$data['user_options']" 
                                    :selected="!empty($data['user_ids']) ? array_values($data['user_ids']) : []" 
                                    :name="'data[user_ids][]'"
                                    id="userSelector"
                                />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="over_time_policy_ids">Overtime Policies</label>
                            <select 
                                id="over_time_policy_ids" 
                                class="form-select" 
                                name="data[over_time_policy_ids][]" 
                                multiple
                            >
                                @foreach ($data['over_time_policy_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['over_time_policy_ids']) && in_array($id, $data['over_time_policy_ids']))
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="round_interval_policy_ids">Rounding Policies</label>
                            <select 
                                id="round_interval_policy_ids" 
                                class="form-select" 
                                name="data[round_interval_policy_ids][]" 
                                multiple
                            >
                                @foreach ($data['round_interval_policy_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['round_interval_policy_ids']) && in_array($id, $data['round_interval_policy_ids']))
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="meal_policy_ids">Meal Policies</label>
                            <select 
                                id="meal_policy_ids" 
                                class="form-select" 
                                name="data[meal_policy_ids][]" 
                                multiple
                            >
                                @foreach ($data['meal_options'] as $id => $name )
                                    <option
                                        value="{{$id}}"
                                        @if(!empty($data['meal_policy_ids']) && in_array($id, $data['meal_policy_ids']))
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="break_policy_ids">Break Policies</label>
                            <select 
                                id="break_policy_ids" 
                                class="form-select" 
                                name="data[break_policy_ids][]" 
                                multiple
                            >
                                @foreach ($data['break_options'] as $id => $name )
                                    <option
                                        value="{{$id}}"
                                        @if(!empty($data['break_policy_ids']) && in_array($id, $data['break_policy_ids']))
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="accrual_policy_ids">Accrual Policies</label>
                            <select 
                                id="accrual_policy_ids" 
                                class="form-select" 
                                name="data[accrual_policy_ids][]" 
                                multiple
                            >
                                @foreach ($data['accrual_policy_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['accrual_policy_ids']) && in_array($id, $data['accrual_policy_ids']))
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="premium_policy_ids">Premium Policies</label>
                            <select 
                                id="premium_policy_ids" 
                                class="form-select" 
                                name="data[premium_policy_ids][]" 
                                multiple
                            >
                                @foreach ($data['premium_policy_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['premium_policy_ids']) && in_array($id, $data['premium_policy_ids']))
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="holiday_policy_ids">Holiday Policies</label>
                            <select 
                                id="holiday_policy_ids" 
                                class="form-select" 
                                name="data[holiday_policy_ids][]" 
                                multiple
                            >
                                @foreach ($data['holiday_policy_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['holiday_policy_ids']) && in_array($id, $data['holiday_policy_ids']))
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exception_policy_id">Exception Policy</label>
                            <select 
                                id="exception_policy_id" 
                                class="form-select" 
                                name="data[exception_policy_control_id]" 
                            >
                                @foreach ($data['exception_options'] as $id => $name )
                                <option 
                                    value="{{$id}}"
                                    @if(!empty($data['exception_policy_control_id']) && $id == $data['exception_policy_control_id'])
                                        selected
                                    @endif
                                >{{$name}}</option>
                                @endforeach
                            </select>
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
</x-app-layout>
