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
                                value="1" {{ $data['enable_pay_stub_balance_display'] == TRUE && 'checked' }}>
                        </div>
        

                        <div id="type_id-20" style="{{ $data['type_id'] == 10 ? 'display:none' : '' }}">
                            
                            <div>Frequency In Which To Apply Time to Employee Records</div>
        {{-- check here --}}
                            <div id="apply_frequency" {if $data.type_id != 20}style="display:none"{/if} onClick="showHelpEntry('apply_frequency')">
                                <td class="{isvalid object="apf" label="apply_frequency_id" value="cellLeftEditTable"}">
                                    {t}Frequency:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <select id="apply_frequency_id" name="data[apply_frequency_id]" onChange="showApplyFrequency()">
                                        {html_options options=$data.apply_frequency_options selected=$data.apply_frequency_id}
                                    </select>
                                </td>
                            </div>
        
                            <div id="apply_frequency_hire_date_display" style="display:none" onClick="showHelpEntry('apply_frequency_hire_date')">
                                <td class="{isvalid object="apf" label="apply_frequency_hire_date" value="cellLeftEditTable"}">
                                    {t}Employee's Appointment Date:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="checkbox" class="checkbox" id="apply_frequency_hire_date" name="data[apply_frequency_hire_date]" onChange="showApplyFrequencyHireDate()" value="1" {if $data.apply_frequency_hire_date == TRUE}checked{/if}>
                                </td>
                            </div>
        
        
                            <div id="apply_frequency_month" style="display:none" onClick="showHelpEntry('apply_frequency_month')">
                                <td class="{isvalid object="apf" label="apply_frequency_month" value="cellLeftEditTable"}">
                                    {t}Month:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <select name="data[apply_frequency_month]">
                                        {html_options options=$data.month_options selected=$data.apply_frequency_month}
                                    </select>
                                </td>
                            </div>
        
                            <div id="apply_frequency_day_of_month" style="display:none" onClick="showHelpEntry('apply_frequency_day_of_month')">
                                <td class="{isvalid object="apf" label="apply_frequency_day_of_month" value="cellLeftEditTable"}">
                                    {t}Day Of Month:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <select name="data[apply_frequency_day_of_month]">
                                        {html_options options=$data.day_of_month_options selected=$data.apply_frequency_day_of_month}
                                    </select>
                                </td>
                            </div>
        
                            <div id="apply_frequency_day_of_week" style="display:none" onClick="showHelpEntry('apply_frequency_day_of_week')">
                                <td class="{isvalid object="apf" label="apply_frequency_day_of_week" value="cellLeftEditTable"}">
                                    {t}Day Of Week:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <select name="data[apply_frequency_day_of_week]">
                                        {html_options options=$data.day_of_week_options selected=$data.apply_frequency_day_of_week}
                                    </select>
                                </td>
                            </div>
        
                            <div onClick="showHelpEntry('minimum_employed_days')">
                                <td class="{isvalid object="apf" label="minimum_employed_days" value="cellLeftEditTable"}">
                                    {t}After Minimum Employed Days:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <input size="6" type="text" name="data[minimum_employed_days]" value="{$data.minimum_employed_days}">
                                </td>
                            </div>
        
                            {if $data.id != '' AND $data.type_id == 20}
                            <div class="tblHeader">
                                <td colspan="2" >
                                    {t}Calculate Accruals Immediately For The Following Dates{/t}
                                </td>
                            </div>
        
                            <div onClick="showHelpEntry('recalculate')">
                                <td class="{isvalid object="apf" label="recalculate" value="cellLeftEditTable"}">
                                    {t}Enable:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="checkbox" id="recalculate" class="checkbox" name="data[recalculate]" value="1" onChange="showRecalculateDate()">
                                </td>
                            </div>
        
                            <div id="display_recalculate_start_date" style="display:none" onClick="showHelpEntry('recalculate_start_date')">
                                <td class="{isvalid object="apf" label="recalculate_start_date" value="cellLeftEditTable"}">
                                    {t}Start Date:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="text" size="15" id="recalculate_start_date" onFocus="showHelpEntry('recalculate_start_date')" name="data[recalculate_start_date]" value="{getdate type="DATE" epoch=$data.recalculate_start_date}">
                                    <img src="{$BASE_URL}/images/cal.gif" id="cal_recalculate_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('recalculate_start_date', 'cal_recalculate_start_date', false);">
                                </td>
                            </div>
        
                            <div id="display_recalculate_end_date" style="display:none" onClick="showHelpEntry('recalculate_end_date')">
                                <td class="{isvalid object="apf" label="recalculate_end_date" value="cellLeftEditTable"}">
                                    {t}End Date:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="text" size="15" id="recalculate_end_date" onFocus="showHelpEntry('recalculate_end_date')" name="data[recalculate_end_date]" value="{getdate type="DATE" epoch=$data.recalculate_end_date}">
                                    <img src="{$BASE_URL}/images/cal.gif" id="cal_recalculate_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('recalculate_end_date', 'cal_recalculate_end_date', false);">
                                </td>
                            </div>
                            {/if}
        
                            <div class="tblHeader">
                                <td colspan="2" >
                                    {t}Milestone Rollover Based On{/t}
                                </td>
                            </div>
        
                            <div onClick="showHelpEntry('milestone_rollover_hire_date')">
                                <td class="{isvalid object="apf" label="milestone_rollover_hire_date" value="cellLeftEditTable"}">
                                    {t}Employee's Appointment Date:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="checkbox" class="checkbox" id="milestone_rollover_hire_date" name="data[milestone_rollover_hire_date]" onChange="showMilestoneRolloverHireDate()" value="1"{if $data.milestone_rollover_hire_date == TRUE}checked{/if}>
                                </td>
                            </div>
        
                            <div id="milestone_rollover_month" style="display:none" onClick="showHelpEntry('type')">
                                <td class="{isvalid object="apf" label="milestone_rollover_month" value="cellLeftEditTable"}">
                                    {t}Month:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <select id="" name="data[milestone_rollover_month]">
                                        {html_options options=$data.month_options selected=$data.milestone_rollover_month}
                                    </select>
                                </td>
                            </div>
                            <div id="milestone_rollover_day_of_month" style="display:none" onClick="showHelpEntry('type')">
                                <td class="{isvalid object="apf" label="milestone_rollover_day_of_month" value="cellLeftEditTable"}">
                                    {t}Day Of Month:{/t}
                                </td>
                                <td class="cellRightEditTable">
                                    <select id="" name="data[milestone_rollover_day_of_month]">
                                        {html_options options=$data.day_of_month_options selected=$data.milestone_rollover_day_of_month}
                                    </select>
                                </td>
                            </div>
        
                            <div class="tblHeader">
                                <td colspan="2" >
                                    {t}Length Of Service Milestones{/t}
                                </td>
                            </div>
        
                            <div>
                                <td colspan="2">
                                    <table class="tblList">
                                        <div class="tblHeader">
                                            <td>
                                                {t}Length Of Service{/t}
                                            </td>
                                            <td>
                                                {t}Accrual Rate{/t}/{if $data.type_id == 20}Year{else}Hour{/if}
                                                {*
                                                <span id="milestone_accrual_rate_label"></span>
                                                *}
                                            </td>
                                            {*
                                            <td>
                                                {t}Accrual Total Minimum{/t}
                                            </td>
                                            *}
                                            <td>
                                                {t}Accrual Total Maximum{/t}
                                            </td>
                                            <td>
                                                {t}Annual Maximum Rollover{/t}
                                            </td>
                                            <td>
                                                <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                            </td>
                                        </div>
                                        {foreach name="milestones" from=$data.milestone_rows item=milestone_row}
                                            {assign var="milestone_row_id" value=$milestone_row.id}
                                            {cycle assign=row_class values="tblDataWhite,tblDataGrey"}
                                            <div class="{$row_class}">
                                                <td id="{isvalid object="apmf" label="length_of_service$milestone_row_id" value="value"}">
                                                    <input type="hidden" name="data[milestone_rows][{$milestone_row.id}][id]" value="{$milestone_row.id}">
                                                    {t}After:{/t}
                                                    <input size="3" type="text" name="data[milestone_rows][{$milestone_row.id}][length_of_service]" value="{$milestone_row.length_of_service}">
                                                    <select id="" name="data[milestone_rows][{$milestone_row.id}][length_of_service_unit_id]">
                                                        {html_options options=$data.length_of_service_unit_options selected=$milestone_row.length_of_service_unit_id}
                                                    </select>
                                                </td>
                                                <td id="{isvalid object="apmf" label="accrual_rate$milestone_row_id" value="value"}">
                                                    {if $data.type_id == 20}
                                                        <input size="5" type="text" name="data[milestone_rows][{$milestone_row.id}][accrual_rate]" value="{gettimeunit value=$milestone_row.accrual_rate}"> ie: {$current_user_prefs->getTimeUnitFormatExample()}
                                                    {else}
                                                        <input size="5" type="text" name="data[milestone_rows][{$milestone_row.id}][accrual_rate]" value="{$milestone_row.accrual_rate|string_format:"%01.4f"}"> ie: 0.0192
                                                    {/if}
                                                </td>
                                                {*
                                                <td id="{isvalid object="apmf" label="minimumtime$milestone_row_id" value="value"}">
                                                    <input size="5" type="text" name="data[milestone_rows][{$milestone_row.id}][minimum_time]" value="{gettimeunit value=$milestone_row.minimum_time}"> ie: {$current_user_prefs->getTimeUnitFormatExample()}
                                                </td>
                                                *}
                                                <td id="{isvalid object="apmf" label="maximumtime$milestone_row_id" value="value"}">
                                                    <input size="5" type="text" name="data[milestone_rows][{$milestone_row.id}][maximum_time]" value="{gettimeunit value=$milestone_row.maximum_time}">  ie: {$current_user_prefs->getTimeUnitFormatExample()}
                                                </td>
                                                <td id="{isvalid object="apmf" label="rollovertime$milestone_row_id" value="value"}">
                                                    <input size="5" type="text" name="data[milestone_rows][{$milestone_row.id}][rollover_time]" value="{gettimeunit value=$milestone_row.rollover_time}">  ie: {$current_user_prefs->getTimeUnitFormatExample()}
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="checkbox" name="ids[]" value="{$milestone_row.id}">
                                                </td>
                                            </div>
                                        {/foreach}
                                        <div>
                                            <td class="tblActionRow" colspan="5">
                                                <input type="submit" name="action:add_milestone" value="{t}Add Milestone{/t}">
                                                <input type="submit" name="action:delete" value="{t}Delete{/t}">
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
        
                    document.forms[0].submit();
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
