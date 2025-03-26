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
                        action="{{ isset($data['id']) ? route('policy.rounding_policies.submit', $data['id']) : route('policy.rounding_policies.submit') }}">
                        @csrf

                        @if (!$ripf->Validator->isValid())
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
                            <label for="type_id">Punch Type</label>
                            <select 
                                id="type_id" 
                                class="form-select" 
                                name="data[punch_type_id]" 
                            >
                                @foreach ($data['punch_type_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['punch_type_id']) && $id == $data['punch_type_id'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                    
                        <div class="form-group">
                            <label for="round_type_id">Round Type</label>
                            <select 
                                id="round_type_id" 
                                class="form-select" 
                                name="data[round_type_id]" 
                            >
                                @foreach ($data['round_type_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['round_type_id']) && $id == $data['round_type_id'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="interval">Interval (format hh:mm (eg => 2:15))</label>
                            <input 
                                type="text" 
                                size="6"
                                class="form-control" 
                                name="data[interval]" 
                                value="{{ gmdate('H:i', $data['interval'] ?? '00:15') }}"
                                placeholder="format hh:mm (eg => 02:15)"
                            >
                        </div>

                        <div class="form-group">
                            <label for="grace">Grace Period (format hh:mm (eg => 2:15))</label>
                            <input 
                                type="text" 
                                size="6"
                                class="form-control" 
                                name="data[grace]" 
                                value="{{ gmdate('H:i', $data['grace'] ?? '00:00') }}"
                                placeholder="format hh:mm (eg => 02:15)"
                            >
                        </div>

                        <div class="form-group">
                            <label for="strict">Strict Schedule</label>
                            <input 
                                type="checkbox" 
                                class="checkbox" 
                                id="strict" 
                                name="data[strict]" 
                                value="1" {{ ( !empty($data['strict']) && $data['strict'] == TRUE) && 'checked' }}>
                            Employee can't work more than scheduled time
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
