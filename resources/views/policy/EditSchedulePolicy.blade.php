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
                                href="/policy/schedule_policies/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Policy Group <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('policy.schedule_policies.submit', $data['id']) : route('policy.schedule_policies.submit') }}">
                        @csrf

                        @if (!$spf->Validator->isValid())
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
                                placeholder="Enter Schedule Policy Name"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="meal_policy_id">Meal Policy</label>
                            <select 
                                id="meal_policy_id" 
                                class="form-select" 
                                name="data[meal_policy_id]" 
                            >
                                @foreach ($data['meal_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['meal_policy_id']) && $id == $data['meal_policy_id'])
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
                            <label for="absence_policy_id">Undertime Absence Policy</label>
                            <select 
                                id="absence_policy_id" 
                                class="form-select" 
                                name="data[absence_policy_id]" 
                            >
                                @foreach ($data['absence_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['absence_policy_id']) &&  $id == $data['absence_policy_id'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="over_time_policy_id">Overtime Policy</label>
                            <select 
                                id="over_time_policy_id" 
                                class="form-select" 
                                name="data[over_time_policy_id]" 
                            >
                                @foreach ($data['over_time_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['over_time_policy_id']) && in_array($id, $data['over_time_policy_id']))
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_stop_window">Start / Stop Window (format hh:mm (eg => 2:15))</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="data[start_stop_window]" 
                                value="{{ gmdate('H:i', $data['start_stop_window'] ?? '01:00') }}"
                                placeholder="format hh:mm (eg => 2:15)"
                            >
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary btnSubmit" name="action:submit" value="Submit">
                        </div>
            
                        <input type="hidden" name="data[id]" value="{{!empty($data['id']) && $data['id']}}">

                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
