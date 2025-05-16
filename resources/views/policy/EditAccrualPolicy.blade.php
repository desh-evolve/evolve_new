<x-app-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>
    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form 
                        id="accrualPolicyForm"
                        method="POST"
                        action="{{ route('policy.accrual_policies.add') }}">
                        @csrf

                            <div id="contentBoxTwoEdit">
                                @if (!$apf->Validator->isValid() OR !$apmf->Validator->isValid())
                                    {{-- include error list here => file="form_errors.tpl" object="apf,apmf" --}}
                                @endif
                
                                <table class="table table-bordered">
                
                                <tr>
                                    <td class="fw-bold">
                                        Name:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="text" name="data[name]" value="{{$data['name'] ?? ''}}">
                                    </td>
                                </tr>
                
                                <tr>
                                    <td class="fw-bold">
                                        Type:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select id="type_id" name="data[type_id]" onChange="showType();">
                                            @foreach ($data['type_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}"
                                                    {{(!empty($data['type_id']) && $id == $data['type_id']) ? 'selected' : ''}}
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr>
                                    <td class="fw-bold">
                                        Display Balance on Pay Stub:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input 
                                            type="checkbox" 
                                            class="checkbox" 
                                            id="enable_pay_stub_balance_display" 
                                            name="data[enable_pay_stub_balance_display]" 
                                            value="1" 
                                            {{ (!empty($data['enable_pay_stub_balance_display']) && $data['enable_pay_stub_balance_display']) ? 'checked' : '' }}
                                        >
                                    </td>
                                </tr>
                
                                <tbody id="type_id-20" style="{{ !empty($data['type_id']) && $data['type_id'] == 10 ? 'display:none' : '' }}" >
                                <tr class="bg-primary text-white">
                                    <td colspan="2" >
                                        Frequency In Which To Apply Time to Employee Records
                                    </td>
                                </tr>
                
                                <tr id="apply_frequency" {{ !empty($data['type_id']) && $data['type_id'] == 20 ? 'display:none' : '' }} >
                                    <td class="fw-bold">
                                        Frequency:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select id="apply_frequency_id" name="data[apply_frequency_id]" onChange="showApplyFrequency()">
                                            @foreach ($data['apply_frequency_options'] as $id => $name)
                                                <option
                                                    value="{{$id}}"
                                                    {{(!empty($data['apply_frequency_id']) && $id == $data['apply_frequency_id']) ? 'selected' : ''}}
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr id="apply_frequency_hire_date_display" style="display:none">
                                    <td class="fw-bold">
                                        Employee's Appointment Date:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input 
                                            type="checkbox" 
                                            class="checkbox" 
                                            id="apply_frequency_hire_date" 
                                            name="data[apply_frequency_hire_date]" 
                                            onChange="showApplyFrequencyHireDate()" 
                                            value="1" 
                                            {{(!empty($data['apply_frequency_hire_date']) && $data['apply_frequency_hire_date']) ? 'checked' : ''}} 
                                        >
                                    </td>
                                </tr>
                
                
                                <tr id="apply_frequency_month" style="display:none">
                                    <td class="fw-bold">
                                        Month:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="data[apply_frequency_month]">
                                            @foreach ($data['month_options'] as $id => $name)
                                                <option
                                                    value="{{$id}}"
                                                    {{!empty($data['apply_frequency_month']) && $id == $data['apply_frequency_month'] ? 'selected' : ''}}
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr id="apply_frequency_day_of_month" style="display:none">
                                    <td class="fw-bold">
                                        Day Of Month:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="data[apply_frequency_day_of_month]">.
                                            @foreach ($data['day_of_month_options'] as $id => $name)
                                                <option
                                                    value="{{$id}}"
                                                    {{!empty($data['apply_frequency_day_of_month']) && $id == $data['apply_frequency_day_of_month'] ? 'selected' : ''}}
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr id="apply_frequency_day_of_week" style="display:none">
                                    <td class="fw-bold">
                                        Day Of Week:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select name="data[apply_frequency_day_of_week]">
                                            @foreach ($data['day_of_week_options'] as $id => $name)
                                                <option
                                                    value="{{$id}}"
                                                    {{!empty($data['apply_frequency_day_of_week']) && $id == $data['apply_frequency_day_of_week'] ? 'selected' : ''}}
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr>
                                    <td class="fw-bold">
                                        After Minimum Employed Days:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input size="6" type="text" name="data[minimum_employed_days]" value="{{$data['minimum_employed_days'] ?? ''}}">
                                    </td>
                                </tr>
                
                            @if (!empty($data['id']) AND $data['type_id'] == 20)

                                <tr class="bg-primary text-white">
                                    <td colspan="2" >
                                        Calculate Accruals Immediately For The Following Dates
                                    </td>
                                </tr>
                
                                <tr>
                                    <td class="fw-bold">
                                        Enable:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="checkbox" id="recalculate" class="checkbox" name="data[recalculate]" value="1" onChange="showRecalculateDate()">
                                    </td>
                                </tr>
                
                                <tr id="display_recalculate_start_date" style="display:none">
                                    <td class="fw-bold">
                                        Start Date:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input 
                                            type="date" 
                                            id="recalculate_start_date" 
                                            name="data[recalculate_start_date]" 
                                            value="{{ getdate_helper('date', $data['recalculate_start_date'] ?? '') }}"
                                        >
                                    </td>
                                </tr>
                
                                <tr id="display_recalculate_end_date" style="display:none">
                                    <td class="fw-bold">
                                        End Date:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input 
                                            type="date" 
                                            id="recalculate_end_date" 
                                            name="data[recalculate_end_date]" 
                                            value="{{ getdate_helper('date', $data['recalculate_end_date'] ?? '') }}"
                                        >
                                    </td>
                                </tr>
                                @endif
                
                                <tr class="bg-primary text-white">
                                    <td colspan="2" >
                                        Milestone Rollover Based On
                                    </td>
                                </tr>
                
                                <tr>
                                    <td class="fw-bold">
                                        Employee's Appointment Date:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input 
                                            type="checkbox" 
                                            class="checkbox" 
                                            id="milestone_rollover_hire_date" 
                                            name="data[milestone_rollover_hire_date]" 
                                            onChange="showMilestoneRolloverHireDate()" 
                                            value="1"
                                            {{ !empty($data['milestone_rollover_hire_date']) && $data['milestone_rollover_hire_date'] == TRUE ? 'checked' : '' }}
                                        >
                                    </td>
                                </tr>
                
                                <tr id="milestone_rollover_month" style="display:none">
                                    <td class="fw-bold">
                                        Month:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select id="" name="data[milestone_rollover_month]">
                                            @foreach ($data['month_options'] as $id => $name)
                                                <option
                                                    value="{{ $id }}"
                                                    {{ !empty($data['milestone_rollover_month']) && $id == $data['milestone_rollover_month'] ? 'selected' : '' }}
                                                >
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr id="milestone_rollover_day_of_month" style="display:none">
                                    <td class="fw-bold">
                                        Day Of Month:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <select id="" name="data[milestone_rollover_day_of_month]">
                                            @foreach ($data['day_of_month_options'] as $id => $name)
                                                <option
                                                    value="{{ $id }}"
                                                    {{ !empty($data['milestone_rollover_day_of_month']) && $id == $data['milestone_rollover_day_of_month'] ? 'selected' : '' }}
                                                >
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr class="bg-primary text-white">
                                    <td colspan="2" >
                                        Length Of Service Milestones
                                    </td>
                                </tr>
                
                                <tr>
                                    <td colspan="2">
                                        <table class="table table-bordered">
                                            <tr class="bg-primary text-white">
                                                <td>
                                                    Length Of Service
                                                </td>
                                                <td>
                                                    Accrual Rate/{{ $data['type_id'] == 20 ? 'Year' : 'Hour' }}
                                                    {{-- <span id="milestone_accrual_rate_label"></span> --}}
                                                </td>
                                                {{-- <td>
                                                    Accrual Total Minimum
                                                </td> --}}
                                                <td>
                                                    Accrual Total Maximum
                                                </td>
                                                <td>
                                                    Annual Maximum Rollover
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                                </td>
                                            </tr>
                                            
                                            @foreach ($data['milestone_rows'] as $milestone_row)
                                                <tr class="">
                                                    <td>
                                                        <input type="hidden" name="data[milestone_rows][{{$milestone_row['id']}}][id]" value="{{$milestone_row['id']}}">
                                                        After:
                                                        <input size="3" type="text" name="data[milestone_rows][{{$milestone_row['id']}}][length_of_service]" value="{{$milestone_row['length_of_service']}}">
                                                        <select id="" name="data[milestone_rows][{{$milestone_row['id']}}][length_of_service_unit_id]">
                                                            @foreach ($data['length_of_service_unit_options'] as $id => $name)
                                                                <option
                                                                    value="{{ $id }}"
                                                                    {{ !empty($milestone_row['length_of_service_unit_id']) && $id == $milestone_row['length_of_service_unit_id'] ? 'selected' : '' }}
                                                                >
                                                                    {{ $name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        @if ($data['type_id'] == 20)
                                                            <input 
                                                                size="5" 
                                                                type="text" 
                                                                name="data[milestone_rows][{{$milestone_row['id']}}][accrual_rate]" 
                                                                value="{{ gettimeunit_helper($milestone_row['accrual_rate'], '00:00') }}"
                                                            > ie: {{ $current_user_prefs->getTimeUnitFormatExample() }}
                                                        @else
                                                            <input 
                                                                size="5" 
                                                                type="text" 
                                                                name="data[milestone_rows][{{$milestone_row['id']}}][accrual_rate]" 
                                                                value="{{ $milestone_row['accrual_rate'] }}"
                                                            > ie: 0.0192
                                                        @endif
                                                    </td>
                                                    {{--
                                                    <td id="{isvalid object="apmf" label="minimumtime$milestone_row['id']" value="value"}">
                                                        <input size="5" type="text" name="data[milestone_rows][{$milestone_row['id']}][minimum_time]" value="{gettimeunit value=$milestone_row.minimum_time}"> ie: {$current_user_prefs->getTimeUnitFormatExample()}
                                                    </td>
                                                    --}}
                                                    <td>
                                                        <input 
                                                            size="5" 
                                                            type="text" 
                                                            name="data[milestone_rows][{{$milestone_row['id']}}][maximum_time]" 
                                                            value="{{ gettimeunit_helper($milestone_row['maximum_time'], '00:00') }}"
                                                        >  ie: {{$current_user_prefs->getTimeUnitFormatExample()}}
                                                    </td>
                                                    <td>
                                                        <input 
                                                            size="5" 
                                                            type="text" 
                                                            name="data[milestone_rows][{{$milestone_row['id']}}][rollover_time]" 
                                                            value="{{ gettimeunit_helper($milestone_row['rollover_time'], '00:00') }}"
                                                        >  ie: {{$current_user_prefs->getTimeUnitFormatExample()}}
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="checkbox" name="ids[]" value="{{ $milestone_row['id'] }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td class="tblActionRow text-end" colspan="5">
                                                    <input type="submit" name="action" value="Add Milestone">
                                                    <input type="submit" name="action" value="Delete">
                                                </td>
                                            </tr>
                
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            </div>
                
                            <div id="contentBoxFour" class="text-end">
                                <input type="submit" class="btnSubmit btn btn-sm btn-primary" name="action" value="Submit" onClick="return singleSubmitHandler(this)">
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
            showType( true ); 
            showApplyFrequency(); 
            showMilestoneRolloverHireDate();
        });

        function showType(onload) {
            var form = document.getElementById('accrualPolicyForm');

            if (document.getElementById('type_id').value == 20 || document.getElementById('type_id').value == 30) {
                if (onload != true) {
                    //Create submit button so PHP script can handle the rest.
                    let submit_button = document.createElement('input');
                    submit_button.type = 'hidden';
                    submit_button.name = 'action';
                    submit_button.value = 'change_type';
                    form.appendChild(submit_button);
                    form.submit();
                }

                if (document.getElementById('type_id').value == 30) {
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
