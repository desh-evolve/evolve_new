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
                        action="{{ isset($data['id']) ? route('policy.accrual_policies.submit', $data['id']) : route('policy.accrual_policies.submit') }}">
                        @csrf

                        @if (!$apf->Validator->isValid())
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
                                placeholder="Enter Accrual Policy Name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="type_id">Type</label>
                            <select 
                                id="type_id" 
                                class="form-select" 
                                name="data[type_id]" 
                                onChange="showType()"
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
                            <label for="enable_pay_stub_balance_display">Name</label>
                            <input 
                                type="checkbox" 
                                class="checkbox" 
                                id="enable_pay_stub_balance_display" 
                                name="data[enable_pay_stub_balance_display]" 
                                value="1" {{ ( !empty($data['enable_pay_stub_balance_display']) && $data['enable_pay_stub_balance_display'] == TRUE) && 'checked' }}>
                        </div>
        

                        <div id="type_id-20" style="{{ $data['type_id'] == 10 ? 'display:none' : '' }}">
                            
                            <div class="bg-primary text-white">Frequency In Which To Apply Time to Employee Records</div>
        
                            <div class="form-group" id="apply_frequency" style="{{$data['type_id'] != 20 ? 'display:none' : ''}}">
                                <label for="apply_frequency_id">Frequency</label>
                                <select 
                                    id="apply_frequency_id" 
                                    class="form-select" 
                                    name="data[apply_frequency_id]" 
                                    onChange="showApplyFrequency()"
                                >
                                    @foreach ($data['apply_frequency_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['apply_frequency_id']) && $id == $data['apply_frequency_id'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group" id="apply_frequency_hire_date_display" style="display:none">
                                <label for="apply_frequency_hire_date">Employee's Appointment Date</label>
                                <input 
                                    type="checkbox" 
                                    class="checkbox" 
                                    id="apply_frequency_hire_date" 
                                    name="data[apply_frequency_hire_date]" 
                                    onChange="showApplyFrequencyHireDate()"
                                    value="1" {{ $data['apply_frequency_hire_date'] == TRUE && 'checked' }}>
                            </div>
                            
                            <div class="form-group" id="apply_frequency_month" style="display:none">
                                <label for="apply_frequency_month">Month</label>
                                <select 
                                    id="apply_frequency_month" 
                                    class="form-select" 
                                    name="data[apply_frequency_month]" 
                                >
                                    @foreach ($data['month_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['apply_frequency_month']) && $id == $data['apply_frequency_month'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group" id="apply_frequency_day_of_month" style="display:none">
                                <label for="apply_frequency_day_of_month">Day Of Month</label>
                                <select 
                                    id="apply_frequency_day_of_month" 
                                    class="form-select" 
                                    name="data[apply_frequency_day_of_month]" 
                                >
                                    @foreach ($data['day_of_month_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['apply_frequency_day_of_month']) && $id == $data['apply_frequency_day_of_month'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>
        
                            
                            <div class="form-group" id="apply_frequency_day_of_week" style="display:none">
                                <label for="apply_frequency_day_of_week">Day Of Month</label>
                                <select 
                                    id="apply_frequency_day_of_week" 
                                    class="form-select" 
                                    name="data[apply_frequency_day_of_week]" 
                                >
                                    @foreach ($data['day_of_week_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['apply_frequency_day_of_week']) && $id == $data['apply_frequency_day_of_week'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>
        
        
                            <div class="form-group" id="minimum_employed_days" style="display:none">
                                <label for="minimum_employed_days">After Minimum Employed Days</label>
                                <input
                                    class="form-control"
                                    size="6" 
                                    type="text" 
                                    name="data[minimum_employed_days]" 
                                    value="{{ $data['minimum_employed_days'] ?? '0' }}"
                                />
                            </div>
        
                            @if (!empty($data['id']) AND $data['id'] AND $data['type_id'] == 20)
                                <div class="bg-primary text-white">Calculate Accruals Immediately For The Following Dates</div>
            
                                <div class="form-group">
                                    <label for="recalculate">Enable</label>
                                    <input 
                                        type="checkbox" 
                                        id="recalculate" 
                                        class="checkbox" 
                                        name="data[recalculate]" 
                                        value="1" 
                                        onChange="showRecalculateDate()">
                                </div>

                                <div class="form-group" id="display_recalculate_start_date" style="display:none">
                                    <label for="recalculate_start_date">Start Date</label>
                                    <input
                                        class="form-control"
                                        size="15" 
                                        id="recalculate_start_date"
                                        type="date" 
                                        name="data[recalculate_start_date]" 
                                        value="{{ !empty($data['recalculate_start_date']) ? date('Y-m-d', $data['recalculate_start_date']) : '' }}"
                                    />
                                </div>

                                <div class="form-group" id="display_recalculate_end_date" style="display:none">
                                    <label for="recalculate_end_date">End Date</label>
                                    <input
                                        class="form-control"
                                        size="15" 
                                        id="recalculate_end_date"
                                        type="date" 
                                        name="data[recalculate_end_date]" 
                                        value="{{ !empty($data['recalculate_end_date']) ? date('Y-m-d', $data['recalculate_end_date']) : '' }}"
                                    />
                                </div>

                            @endif
        
                            <div class="bg-primary text-white">Milestone Rollover Based On</div>

                            <div class="form-group">
                                <label for="milestone_rollover_hire_date">Employee's Appointment Date</label>
                                <input
                                    size="15" 
                                    id="milestone_rollover_hire_date"
                                    type="checkbox" 
                                    class="checkbox"
                                    name="data[milestone_rollover_hire_date]" 
                                    onChange="showMilestoneRolloverHireDate()"
                                    value="1" {{(!empty($data['milestone_rollover_hire_date']) AND $data['milestone_rollover_hire_date'] == TRUE) && 'checked' }}
                                />
                            </div>

                            <div class="form-group" id="milestone_rollover_month" style="display:none">
                                <label for="milestone_rollover_month">Month</label>
                                <select 
                                    id="milestone_rollover_month" 
                                    class="form-select" 
                                    name="data[milestone_rollover_month]" 
                                >
                                    @foreach ($data['month_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['milestone_rollover_month']) && $id == $data['milestone_rollover_month'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" id="milestone_rollover_day_of_month" style="display:none">
                                <label for="milestone_rollover_day_of_month">Month</label>
                                <select 
                                    id="milestone_rollover_day_of_month" 
                                    class="form-select" 
                                    name="data[milestone_rollover_day_of_month]" 
                                >
                                    @foreach ($data['day_of_month_options'] as $id => $name )
                                    <option 
                                        value="{{$id}}"
                                        @if(!empty($data['milestone_rollover_day_of_month']) && $id == $data['milestone_rollover_day_of_month'])
                                            selected
                                        @endif
                                    >{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="bg-primary text-white">Length Of Service Milestones</div>
                            
                            <div>
                                <td colspan="2">
                                    <table class="tblList">
                                        <thead class="bg-primary text-white">
                                            <td>Length Of Service</td>
                                            <td>Accrual Rate /{{ $data['type_id'] == 20 ? 'Year' : 'Hour' }}
                                                {{-- <span id="milestone_accrual_rate_label"></span> --}}
                                            </td>
                                            {{-- <td>Accrual Total Minimum </td> --}}
                                            <td>Accrual Total Maximum </td>
                                            <td>Annual Maximum Rollover </td>
                                            <td><input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/></td>
                                        </thead>
                                        @foreach ($data['milestone_rows'] as $milestone_row)
                                            <tr>
                                                <td label="length_of_service{{$milestone_row['id']}}" value="value">
                                                    <input type="hidden" name="data[milestone_rows][{{$milestone_row['id']}}][id]" value="{{$milestone_row['id']}}">
                                                    After: 
                                                    <input size="3" type="text" name="data[milestone_rows][{{$milestone_row['id']}}][length_of_service]" value="{{$milestone_row['length_of_service']}}">

                                                    <select 
                                                        id="milestone_rollover_day_of_month" 
                                                        class="form-select" 
                                                        name="data[milestone_rollover_day_of_month]" 
                                                    >
                                                        @foreach ($data['length_of_service_unit_options'] as $id => $name )
                                                        <option 
                                                            value="{{$id}}"
                                                            @if(!empty($data['milestone_rollover_day_of_month']) && $id == $milestone_row['length_of_service_unit_id'])
                                                                selected
                                                            @endif
                                                        >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td label="accrual_rate{{$milestone_row['id']}}" value="value">
                                                    @if ($data['type_id'] == 20)
                                                        <input size="5" type="text" 
                                                            name="data[milestone_rows][{{ $milestone_row['id'] }}][accrual_rate]" 
                                                            value="{{ gmdate('H:i', (int)$milestone_row['accrual_rate']) }}">
                                                        ie: {{ $current_user_prefs->getTimeUnitFormatExample() }}
                                                    @else
                                                        <input size="5" type="text" 
                                                            name="data[milestone_rows][{{ $milestone_row['id'] }}][accrual_rate]" 
                                                            value="{{ number_format($milestone_row['accrual_rate'], 4) }}">
                                                        ie: 0.0192
                                                    @endif
                                                </td>

                                                <td label="maximumtime{{ $milestone_row['id'] }}" value="value">
                                                    <input size="5" type="text" 
                                                           name="data[milestone_rows][{{ $milestone_row['id'] }}][maximum_time]" 
                                                           value="{{ gmdate('H:i', (int)$milestone_row['maximum_time']) }}">
                                                    ie: {{ $current_user_prefs->getTimeUnitFormatExample() }}
                                                </td>
                                                
                                                <td label="rollovertime{{ $milestone_row['id'] }}" value="value">
                                                    <input size="5" type="text" 
                                                           name="data[milestone_rows][{{ $milestone_row['id'] }}][rollover_time]" 
                                                           value="{{ gmdate('H:i', (int)$milestone_row['rollover_time']) }}">
                                                    ie: {{ $current_user_prefs->getTimeUnitFormatExample() }}
                                                </td>
                                                
                                                <td>
                                                    <input type="checkbox" class="checkbox" name="ids[]" value="{{ $milestone_row['id'] }}">
                                                </td>
                                                
                                            </tr>
                                        @endforeach
                                        <div>
                                            <td class="tblActionRow" colspan="5">
                                                <input type="submit" name="action:add_milestone" value="Add Milestone ">
                                                <input type="submit" name="action:delete" value="Delete ">
                                            </td>
                                        </div>
        
                                    </table>
                                </td>
                            </div>
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

    <script	language=JavaScript>

        document.addEventListener("DOMContentLoaded", function () {
            showType( true ); 
            showApplyFrequency(); 
            showMilestoneRolloverHireDate();
        });

        function showType( onload ) {
            if ( document.getElementById('type_id').value == 20 || document.getElementById('type_id').value == 30 ) {
                if ( onload != true ) {
                    //Create submit button so PHP script can handle the rest.
                    submit_button = document.createElement('input');
                    submit_button.type = 'hidden';
                    submit_button.name = 'action:change_type';
                    submit_button.value = 'Submit';
                    document.forms[0].appendChild( submit_button );
        
                    //document.forms[0].submit();
                }
        
                if ( document.getElementById('type_id').value == 30 ) {
                    document.getElementById('apply_frequency_id').value = 10;
                    document.getElementById('apply_frequency').className = 'none';
                    document.getElementById('apply_frequency').style.display = 'none';
                }
        
            } else {
                document.getElementById('type_id-20').style.display = 'none';
                document.getElementById('frequency').style.display = 'none';
            }
        }
        
        function showApplyFrequency() {
            document.getElementById('apply_frequency_month').style.display = 'none';
            document.getElementById('apply_frequency_day_of_month').style.display = 'none';
            document.getElementById('apply_frequency_day_of_week').style.display = 'none';
            document.getElementById('apply_frequency_hire_date_display').style.display = 'none';
        
            if ( document.getElementById('apply_frequency_id').value == 10 ) {
            } else if (document.getElementById('apply_frequency_id').value == 20) {
                document.getElementById('apply_frequency_hire_date_display').className = '';
                document.getElementById('apply_frequency_hire_date_display').style.display = '';
        
                //document.getElementById('apply_frequency_month').className = '';
                //document.getElementById('apply_frequency_month').style.display = '';
        
                //document.getElementById('apply_frequency_day_of_month').className = '';
                //document.getElementById('apply_frequency_day_of_month').style.display = '';
            } else if (document.getElementById('apply_frequency_id').value == 30) {
                document.getElementById('apply_frequency_day_of_month').className = '';
                document.getElementById('apply_frequency_day_of_month').style.display = '';
            }  else if (document.getElementById('apply_frequency_id').value == 40) {
                document.getElementById('apply_frequency_day_of_week').className = '';
                document.getElementById('apply_frequency_day_of_week').style.display = '';
            }
        
            showApplyFrequencyHireDate();
        }
        
        function showApplyFrequencyHireDate() {
            if ( document.getElementById('apply_frequency_id').value == 20 && document.getElementById('apply_frequency_hire_date').checked == true ) {
                document.getElementById('apply_frequency_month').style.display = 'none';
                document.getElementById('apply_frequency_day_of_month').style.display = 'none';
                document.getElementById('apply_frequency_day_of_week').style.display = 'none';
            } else if ( document.getElementById('apply_frequency_id').value == 20 && document.getElementById('apply_frequency_hire_date').checked == false )  {
                document.getElementById('apply_frequency_month').className = '';
                document.getElementById('apply_frequency_month').style.display = '';
        
                document.getElementById('apply_frequency_day_of_month').className = '';
                document.getElementById('apply_frequency_day_of_month').style.display = '';
            }
        }
        
        function showMilestoneRolloverHireDate() {
            if ( document.getElementById('milestone_rollover_hire_date').checked == true ) {
                document.getElementById('milestone_rollover_month').style.display = 'none';
                document.getElementById('milestone_rollover_day_of_month').style.display = 'none';
            } else {
                document.getElementById('milestone_rollover_month').className = '';
                document.getElementById('milestone_rollover_month').style.display = '';
        
                document.getElementById('milestone_rollover_day_of_month').className = '';
                document.getElementById('milestone_rollover_day_of_month').style.display = '';
            }
        }
        
        function showRecalculateDate() {
            if ( document.getElementById('recalculate').checked == true ) {
                document.getElementById('display_recalculate_start_date').className = '';
                document.getElementById('display_recalculate_start_date').style.display = '';
        
                document.getElementById('display_recalculate_end_date').className = '';
                document.getElementById('display_recalculate_end_date').style.display = '';
            } else {
        
                document.getElementById('display_recalculate_start_date').style.display = 'none';
                document.getElementById('display_recalculate_start_date').style.display = 'none';
        
                document.getElementById('display_recalculate_end_date').style.display = 'none';
                document.getElementById('display_recalculate_end_date').style.display = 'none';
            }
        }
        
        </script>
</x-app-layout>
