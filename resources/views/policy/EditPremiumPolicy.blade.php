<x-app-layout :title="'Input Example'">

    <style>
        th, td{
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
                    
                    <form 
                        method="POST" 
                        name="wage" 
                        action="{{ route('policy.premium_policies.add') }}">
                        @csrf

                            <div id="contentBoxTwoEdit">
                                @if (!$ppf->Validator->isValid())
                                    {{-- {include file="form_errors.tpl" object="ppf"} --}}
                                    {{-- error list here --}}
                                @endif
                
                                <table class="table table-bordered">
                    
                                    <tr>
                                        <td>
                                            Name:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <input type="text" name="data[name]" value="{{$data['name'] ?? ''}}">
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <td>
                                            Type:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <select id="type_id" name="data[type_id]" onChange="showType()">
                                                @foreach ($data['type_options'] as $id => $name)
                                                    <option
                                                        value="{{$id}}"
                                                        {{ !empty($data['type_id']) && $id == $data['type_id'] ? 'selected' : '' }}
                                                    >
                                                        {{$name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                    
                                    <tbody id="type_date_time" style="display:none" >
                                        <tr>
                                            <td colspan="2" class="bg-primary text-white">
                                                Date/Time Criteria
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Start Date:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input 
                                                    type="date" 
                                                    id="start_date" 
                                                    name="data[start_date]" 
                                                    value="{{ getdate_helper('date', $data['start_date'] ?? '') }}"
                                                >
                                                ie: {{$current_user_prefs->getDateFormatExample()}} <b>(Leave blank for no start date)</b>
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                End Date:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input 
                                                    type="date" 
                                                    id="end_date" 
                                                    name="data[end_date]" 
                                                    value="{{ getdate_helper('date', $data['end_date'] ?? '') }}"
                                                >
                                                ie: {{$current_user_prefs->getDateFormatExample()}} <b>(Leave blank for no end date)</b>
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td class="{isvalid object="ppf" label="start_time" value="cellLeftEditTable"}">
                                                Start Time:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input 
                                                    type="text" 
                                                    size="10" 
                                                    name="data[start_time]" 
                                                    value="{{ getdate_helper('date', $data['start_time']) }}"
                                                >
                                                ie: {{$current_user_prefs->getTimeFormatExample()}} <b>(Leave blank for no start time)</b>
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                End Time:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input 
                                                    type="text" 
                                                    size="10" 
                                                    name="data[end_time]" 
                                                    value="{{ getdate_helper('date', $data['end_time']) }}"
                                                >
                                                ie: {{$current_user_prefs->getTimeFormatExample()}} <b>(Leave blank for no end time)</b>
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Include Partial Punches:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input 
                                                    type="checkbox" 
                                                    class="checkbox" 
                                                    name="data[include_partial_punch]" 
                                                    value="1" 
                                                    {{ !empty($data['include_partial_punch']) && $data['include_partial_punch'] ? 'checked' : '' }}
                                                >
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Active After Daily (Regular) Hours:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input 
                                                type="text" 
                                                size="8" 
                                                name="data[daily_trigger_time]" 
                                                value="{{ gettimeunit_helper($data['daily_trigger_time'], '00:00') }}"
                                            > {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Active After Weekly (Regular) Hours:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input 
                                                    type="text" 
                                                    size="8" 
                                                    name="data[weekly_trigger_time]" 
                                                    value="{{ gettimeunit_helper($data['weekly_trigger_time'], '00:00') }}"
                                                > {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Effective Days:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <table width="280">
                                                    <tr align="center" style="font-weight: bold">
                                                        <td>
                                                            Sun
                                                        </td>
                                                        <td>
                                                            Mon
                                                        </td>
                                                        <td>
                                                            Tue
                                                        </td>
                                                        <td>
                                                            Wed
                                                        </td>
                                                        <td>
                                                            Thu
                                                        </td>
                                                        <td>
                                                            Fri
                                                        </td>
                                                        <td>
                                                            Sat
                                                        </td>
                                                    </tr>
                                                    <tr align="center">
                                                        <td>
                                                            <input type="checkbox" class="checkbox" name="data[sun]" value="1" {{ $data['sun'] ? 'checked' : '' }} >
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" class="checkbox" name="data[mon]" value="1" {{ $data['mon'] ? 'checked' : '' }} >
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" class="checkbox" name="data[tue]" value="1" {{ $data['tue'] ? 'checked' : '' }} >
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" class="checkbox" name="data[wed]" value="1" {{ $data['wed'] ? 'checked' : '' }} >
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" class="checkbox" name="data[thu]" value="1" {{ $data['thu'] ? 'checked' : '' }} >
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" class="checkbox" name="data[fri]" value="1" {{ $data['fri'] ? 'checked' : '' }} >
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" class="checkbox" name="data[sat]" value="1" {{ $data['sat'] ? 'checked' : '' }} >
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                    
                                    <tbody id="type_differential" style="display:none" >
                                        <tr>
                                            <td colspan="2" class="bg-primary text-white">
                                                Differential Criteria
                                            </td>
                                        </tr>
                    
                                        <tbody id="filter_branch_on" style="display:none" >
                                            <tr>
                                                <td nowrap>
                                                    <b>Branches:</b><a href="javascript:toggleRowObject('filter_branch_on');toggleRowObject('filter_branch_off');filterCountSelect( 'filter_branch' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
                                                </td>
                                                <td colspan="3">
                                                    <table class="table table-bordered">
                                                    <tr class="bg-primary text-white">
                                                        <td colspan="3">
                                                            Selection Type:
                                                            <select id="type_id" name="data[branch_selection_type_id]">
                                                                @foreach ($data['branch_selection_type_options'] as $id => $name)
                                                                    <option
                                                                        value="{{ $id }}"
                                                                        {{ !empty($data['branch_selection_type_id']) && $id == $data['branch_selection_type_id'] ? 'selected' : '' }}
                                                                    >
                                                                        {{ $name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            Exclude Default Branch:
                                                            <input 
                                                                type="checkbox" 
                                                                class="checkbox" 
                                                                name="data[exclude_default_branch]" 
                                                                value="1" 
                                                                {{ !empty($data['exclude_default_branch']) && $data['exclude_default_branch'] ? 'checked' : '' }} 
                                                            >
                                                        </td>
                                                    </td>
                                                    <tr class="bg-primary text-white">
                                                        <td>
                                                            UnSelected Branches
                                                        </td>
                                                        <td>
                                                            <br>
                                                        </td>
                                                        <td>
                                                            Selected Branches
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="cellRightEditTable" width="49%" align="center">
                                                            <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_branch'))">
                                                            <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_branch'))">
                                                            <br>
                                                            <select 
                                                                name="src_branch_id" 
                                                                id="src_filter_branch" 
                                                                style="width:200px;margin:5px 0 5px 0;" 
                                                                size="{{select_size([ 'array'=>$data['src_branch_options'], 'array2'=>$data['selected_branch_options']])}}" 
                                                                multiple
                                                            >
                                                                {!!html_options(['options'=>$data['src_branch_options']])!!}
                                                            </select>
                                                        </td>
                                                        <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                                            <a href="javascript:moveItem(document.getElementById('src_filter_branch'), document.getElementById('filter_branch')); uniqueSelect(document.getElementById('filter_branch')); sortSelect(document.getElementById('filter_branch'));resizeSelect(document.getElementById('src_filter_branch'), document.getElementById('filter_branch'), {{select_size(['array'=>$data['src_branch_options'], 'array2'=>$data['selected_branch_options']])}})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
                                                            <br>
                                                            <a href="javascript:moveItem(document.getElementById('filter_branch'), document.getElementById('src_filter_branch')); uniqueSelect(document.getElementById('src_filter_branch')); sortSelect(document.getElementById('src_filter_branch'));resizeSelect(document.getElementById('src_filter_branch'), document.getElementById('filter_branch'), {{select_size(['array'=>$data['src_branch_options'], 'array2'=>$data['selected_branch_options']])}})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
                                                        </td>
                                                        <td class="cellRightEditTable" width="49%" align="center">
                                                            <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_branch'))">
                                                            <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_branch'))">
                                                            <br>
                                                            <select name="data[branch_ids][]" id="filter_branch" style="width:200px;margin:5px 0 5px 0;" size="{{select_size([ 'array'=>$data['src_branch_options'], 'array2'=>$data['selected_branch_options']])}}" multiple>
                                                                {!!html_options(['options'=>$data['selected_branch_options'], 'selected'=>$data['branch_ids']])!!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tbody id="filter_branch_off" style="display:none">
                                            <tr>
                                                <td nowrap>
                                                    <b>Branches:</b><a href="javascript:toggleRowObject('filter_branch_on');toggleRowObject('filter_branch_off');uniqueSelect(document.getElementById('filter_branch'), document.getElementById('src_filter_branch')); sortSelect(document.getElementById('filter_branch'));resizeSelect(document.getElementById('src_filter_branch'), document.getElementById('filter_branch'), {{select_size(['array'=>$data['branch_options'] ?? []])}})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
                                                </td>
                                                <td class="cellRightEditTable" colspan="100">
                                                    <span id="filter_branch_count">0</span> Branches Currently Selected, Click the arrow to modify.
                                                </td>
                                            </tr>
                                        </tbody>
                    
                                        <tbody id="filter_department_on" style="display:none" >
                                            <tr>
                                                <td nowrap>
                                                    <b>Departments:</b><a href="javascript:toggleRowObject('filter_department_on');toggleRowObject('filter_department_off');filterCountSelect( 'filter_department' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
                                                </td>
                                                <td colspan="3">
                                                    <table class="table table-bordered">
                                                    <tr class="bg-primary text-white">
                                                        <td colspan="3">
                                                            Selection Type:
                                                            <select id="type_id" name="data[department_selection_type_id]">
                                                                {!!html_options(['options'=>$data['department_selection_type_options'], 'selected'=>$data['department_selection_type_id'] ?? ''])!!}
                                                            </select>
                                                            Exclude Default Department:
                                                            <input 
                                                                type="checkbox" 
                                                                class="checkbox" 
                                                                name="data[exclude_default_department]" 
                                                                value="1"
                                                                {{ !empty($data['exclude_default_department']) && $data['exclude_default_department'] ? 'checked' : '' }}
                                                            >
                                                        </td>
                                                    </td>
                                                    <tr class="bg-primary text-white">
                                                        <td>
                                                            UnSelected Departments
                                                        </td>
                                                        <td>
                                                            <br>
                                                        </td>
                                                        <td>
                                                            Selected Departments
                                                        </td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td class="cellRightEditTable" width="49%" align="center">
                                                            <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_department'))">
                                                            <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_department'))">
                                                            <br>
                                                            <select name="src_department_id" id="src_filter_department" style="width:200px;margin:5px 0 5px 0;" size="{{select_size(['array'=>$data['src_department_options'], 'array2'=>$data['selected_department_options']])}}" multiple>
                                                                {!!html_options(['options'=>$data['src_department_options']])!!}
                                                            </select>
                                                        </td>
                                                        <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                                            <a href="javascript:moveItem(document.getElementById('src_filter_department'), document.getElementById('filter_department')); uniqueSelect(document.getElementById('filter_department')); sortSelect(document.getElementById('filter_department'));resizeSelect(document.getElementById('src_filter_department'), document.getElementById('filter_department'), {{select_size(['array'=>$data['src_department_options'], 'array2'=>$data['selected_department_options']])}})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
                                                            <br>
                                                            <a href="javascript:moveItem(document.getElementById('filter_department'), document.getElementById('src_filter_department')); uniqueSelect(document.getElementById('src_filter_department')); sortSelect(document.getElementById('src_filter_department'));resizeSelect(document.getElementById('src_filter_department'), document.getElementById('filter_department'), {{select_size(['array'=>$data['src_department_options'], 'array2'=>$data['selected_department_options']])}})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
                                                        </td>
                                                        <td class="cellRightEditTable" width="49%" align="center">
                                                            <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_department'))">
                                                            <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_department'))">
                                                            <br>
                                                            <select name="data[department_ids][]" id="filter_department" style="width:200px;margin:5px 0 5px 0;" size="{{select_size(['array'=>$data['src_department_options'], 'array2'=>$data['selected_department_options']])}}" multiple>
                                                                {!!html_options(['options'=>$data['selected_department_options'], 'selected'=>$data['department_ids']])!!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tbody id="filter_department_off" style="display:none">
                                            <tr>
                                                <td nowrap>
                                                    <b>Departments:</b><a href="javascript:toggleRowObject('filter_department_on');toggleRowObject('filter_department_off');uniqueSelect(document.getElementById('filter_department'), document.getElementById('src_filter_department')); sortSelect(document.getElementById('filter_department'));resizeSelect(document.getElementById('src_filter_department'), document.getElementById('filter_department'), {{select_size(['array'=>$data['department_options'] ?? [] ])}})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
                                                </td>
                                                <td class="cellRightEditTable" colspan="100">
                                                    <span id="filter_department_count">0</span> Departments Currently Selected, Click the arrow to modify.
                                                </td>
                                            </tr>
                                        </tbody>
                    
                                    </tbody>
                    
                                    <tbody id="type_meal_break" style="display:none" >
                                        <tr>
                                            <td colspan="2" class="bg-primary text-white">
                                                Meal/Break Criteria
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Active After Daily Hours:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input type="text" size="8" name="data[daily_trigger_time2]" value="{{gettimeunit_helper($data['daily_trigger_time'])}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Maximum Time Without A Break:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input type="text" size="8" name="data[maximum_no_break_time]" value="{{gettimeunit_helper($data['maximum_no_break_time'])}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Minimum Time Recognized As Break:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input type="text" size="8" name="data[minimum_break_time]" value="{{gettimeunit_helper($data['minimum_break_time'])}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            </td>
                                        </tr>
                                    <tbody>
                    
                                    <tbody id="type_callback" style="display:none" >
                                        <tr>
                                            <td colspan="2" class="bg-primary text-white">
                                                Callback Criteria
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Minimum Time Between Shifts:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input type="text" size="8" name="data[minimum_time_between_shift]" value="{{gettimeunit_helper($data['minimum_time_between_shift'])}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                First Shift Must Be At Least:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input type="text" size="8" name="data[minimum_first_shift_time]" value="{{gettimeunit_helper($data['minimum_first_shift_time'])}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            </td>
                                        </tr>
                                    <tbody>
                    
                                    <tbody id="type_minimum_shift_time" style="display:none" >
                                        <tr>
                                            <td colspan="2" class="bg-primary text-white">
                                                Minimum Shift Time Criteria
                                            </td>
                                        </tr>
                        
                                        <tr>
                                            <td>
                                                Minimum Shift Time:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input type="text" size="8" name="data[minimum_shift_time]" value="{{gettimeunit_helper($data['minimum_shift_time'])}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            </td>
                                        </tr>
                                    <tbody>
                    
                                    <tr>
                                        <td colspan="2" class="bg-primary text-white">
                                            Hours/Pay Criteria
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <td>
                                            Minimum Time:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <input size="8" type="text" name="data[minimum_time]" value="{{gettimeunit_helper($data['minimum_time'])}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            <b>(Use 0 for no minimum)</b>
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <td class="{isvalid object="hpf" label="maximum_time" value="cellLeftEditTable"}">
                                            Maximum Time:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <input size="8" type="text" name="data[maximum_time]" value="{{gettimeunit_helper($data['maximum_time'])}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                            <b>(Use 0 for no maximum)</b>
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <td>
                                            Include Meal Policy in Calculation:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <input 
                                                type="checkbox" 
                                                class="checkbox" 
                                                name="data[include_meal_policy]" 
                                                value="1" 
                                                {{ !empty($data['include_meal_policy']) && $data['include_meal_policy'] ? 'checked' : '' }}
                                            >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Include Break Policy in Calculation:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <input 
                                                type="checkbox" 
                                                class="checkbox" 
                                                name="data[include_break_policy]"
                                                value="1" 
                                                {{ !empty($data['include_break_policy']) && $data['include_break_policy'] ? 'checked' : '' }}
                                            >
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <td>
                                            Pay Type:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <select id="pay_type_id" name="data[pay_type_id]" onChange="showPayType()">
                                                {!!html_options(['options'=>$data['pay_type_options'], 'selected'=>$data['pay_type_id'] ?? ''])!!}
                                            </select>
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <td>
                                            <span id="pay_type_10_desc" style="display:none">Rate</span><span id="pay_type_20_desc" style="display:none">Premium</span><span id="pay_type_30_desc" style="display:none">Hourly Rate</span>:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <input type="text" size="8" name="data[rate]" value="{{$data['rate']}}"> (ie: <span id="pay_type_10_help" style="display:none">1.5 for time and a half</span><span id="pay_type_20_help" style="display:none">0.75 for 75 cents/hr</span><span id="pay_type_30_help" style="display:none">10.00/hr</span>)
                                        </td>
                                    </tr>
                    
                                    <tbody id="wage_group_desc" style="display:none">
                                        <tr>
                                            <td>
                                                Wage Group:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <select id="wage_group" name="data[wage_group_id]">
                                                    {!!html_options(['options'=>$data['wage_group_options'], 'selected'=>$data['wage_group_id'] ?? ''])!!}
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                    
                                    <tr>
                                        <td>
                                            Pay Stub Account:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <select id="pay_stub_entry_name" name="data[pay_stub_entry_account_id]">
                                                {!!html_options(['options'=>$data['pay_stub_entry_options'], 'selected'=>$data['pay_stub_entry_account_id'] ?? ''])!!}
                                            </select>
                                        </td>
                                    </tr>
                    
                                    <tr>
                                        <td>
                                            Accrual Policy:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <select id="accrual_policy_id" name="data[accrual_policy_id]" onChange="showAccrualRate()">
                                                {!!html_options(['options'=>$data['accrual_options'], 'selected'=>$data['accrual_policy_id'] ?? ''])!!}
                                            </select>
                                        </td>
                                    </tr>
                    
                                    <tbody id="accrual_rate" style="display:none">
                                        <tr>
                                            <td>
                                                Accrual Rate:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input type="text" size="8" name="data[accrual_rate]" value="{{$data['accrual_rate']}}">
                                            </td>
                                        </tr>
                                    </tbody>
                    
                                </table>
                            </div>
                
                            <div id="contentBoxFour">
                                <input type="submit" class="btnSubmit" name="action" value="Submit" onClick="selectAllReportCriteria(); return singleSubmitHandler(this)">
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

    <script>
        $(document).ready(function () {
            showType();
            showPayType();
            showAccrualRate();
            countAllReportCriteria();
        });

        var report_criteria_elements = new Array(
            'filter_branch',
            'filter_department',
            'filter_job_group',
            'filter_job',
            'filter_job_item_group',
            'filter_job_item'
        );

        function showType() {
            let type_id = document.getElementById('type_id').value;

            hideObject('type_date_time');
            hideObject('type_differential');
            hideObject('type_meal_break');
            hideObject('type_callback');
            hideObject('type_minimum_shift_time');

            hideObject('filter_branch_on');
            hideObject('filter_branch_off');
            hideObject('filter_department_on');
            hideObject('filter_department_off');
            hideObject('filter_job_group_on');
            hideObject('filter_job_group_off');
            hideObject('filter_job_on');
            hideObject('filter_job_off');
            hideObject('filter_job_item_group_on');
            hideObject('filter_job_item_group_off');
            hideObject('filter_job_item_on');
            hideObject('filter_job_item_off');

            if ( type_id == 10 ) {
                showObject('type_date_time');
            } else if ( type_id == 20 ) {
                showObject('type_differential');

                //Handle sub-rows
                showObject('filter_branch_off');
                showObject('filter_department_off');

                showObject('filter_job_group_off');
                showObject('filter_job_off');
                showObject('filter_job_item_group_off');
                showObject('filter_job_item_off');
            } else if ( type_id == 30 ) {
                showObject('type_meal_break');
            } else if ( type_id == 40 ) {
                showObject('type_callback');
            } else if ( type_id == 50 ) {
                showObject('type_minimum_shift_time');
            } else if ( type_id == 100 ) {
                showObject('type_date_time');
                showObject('type_differential');

                //Handle sub-rows
                showObject('filter_branch_off');
                showObject('filter_department_off');

                showObject('filter_job_group_off');
                showObject('filter_job_off');
                showObject('filter_job_item_group_off');
                showObject('filter_job_item_off');
            }
        }

        function showPayType() {
            let pay_type_id = document.getElementById('pay_type_id').value;

            document.getElementById('pay_type_10_desc').style.display = 'none';
            document.getElementById('pay_type_20_desc').style.display = 'none';
            document.getElementById('pay_type_30_desc').style.display = 'none';

            document.getElementById('pay_type_10_help').style.display = 'none';
            document.getElementById('pay_type_20_help').style.display = 'none';
            document.getElementById('pay_type_30_help').style.display = 'none';

            document.getElementById('wage_group_desc').style.display = 'none';

            if ( pay_type_id == 10 ) {
                document.getElementById('pay_type_10_desc').className = '';
                document.getElementById('pay_type_10_desc').style.display = '';

                document.getElementById('pay_type_10_help').className = '';
                document.getElementById('pay_type_10_help').style.display = '';

                document.getElementById('wage_group_desc').className = '';
                document.getElementById('wage_group_desc').style.display = '';
            } else if ( pay_type_id == 20 ) {
                document.getElementById('pay_type_20_desc').className = '';
                document.getElementById('pay_type_20_desc').style.display = '';

                document.getElementById('pay_type_20_help').className = '';
                document.getElementById('pay_type_20_help').style.display = '';
            } else if ( pay_type_id == 30 ) {
                document.getElementById('pay_type_30_desc').className = '';
                document.getElementById('pay_type_30_desc').style.display = '';

                document.getElementById('pay_type_30_help').className = '';
                document.getElementById('pay_type_30_help').style.display = '';

                document.getElementById('wage_group_desc').className = '';
                document.getElementById('wage_group_desc').style.display = '';
            }
        }
        function showAccrualRate() {
            let accrual_policy_id = document.getElementById('accrual_policy_id').value;

            if ( accrual_policy_id == 0 ) {
                document.getElementById('accrual_rate').style.display = 'none';
            } else {
                document.getElementById('accrual_rate').className = '';
                document.getElementById('accrual_rate').style.display = '';
            }
        }


    </script>
</x-app-layout>
