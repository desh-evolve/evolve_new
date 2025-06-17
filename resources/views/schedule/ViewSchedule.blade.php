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

                    <table class="table table-bordered">
                        {{-- {*
                        If cases where people need to select many employees, the GET URL length can be exceeded. The problem though is
                        if we use POST, when editing schedules they can't refresh the page automatically without being prompted to re-submit form data.
                        *} --}}
                        <form method="get" name="schedule" id="schedule_form">
                            <input type="hidden" id="tmp_action" name="do" value="">
            
                                <div id="contentBoxTwoEdit">
                                    {{-- @if (!$ugdf->Validator->isValid()) --}}
                                        {{-- error list --}}
                                        {{-- {include file="form_errors.tpl" object="ugdf"} --}}
                                    {{-- @endif --}}
            
                                    <table class="table table-bordered">
            
                                    <tr class="bg-primary text-white">
                                        <td colspan="3">
                                            Schedule Filter Criteria
                                        </td>
                                    </tr>
                                    @if ($permission->Check('schedule','view') OR $permission->Check('schedule','view_child'))
                                        {!! html_report_filter([ 'filter_data'=>$filter_data, 'label'=>'group', 'display_name'=>'Group', 'display_plural_name'=>'Groups']) !!}
                
                                        {!! html_report_filter([ 'filter_data'=>$filter_data, 'label'=>'default_branch', 'display_name'=> 'Branch', 'display_plural_name'=>'Branches']) !!}
                
                                        {!! html_report_filter([ 'filter_data'=>$filter_data, 'label'=>'default_department', 'display_name'=> 'Department', 'display_plural_name'=>'Departments']) !!}
                
                                        {!! html_report_filter([ 'filter_data'=>$filter_data, 'label'=>'schedule_branch', 'display_name'=> 'Branch', 'display_plural_name'=>'Branches']) !!}
                
                                        {!! html_report_filter([ 'filter_data'=>$filter_data, 'label'=>'schedule_department', 'display_name'=> 'Department', 'display_plural_name'=>'Departments']) !!}
                
                                        {!! html_report_filter([ 'filter_data'=>$filter_data, 'label'=>'user_title', 'display_name'=> 'Title', 'display_plural_name'=>'Titles']) !!}
                
                                        {!! html_report_filter([ 'filter_data'=>$filter_data, 'label'=>'include_user', 'display_name'=> 'Employees', 'display_plural_name'=>'Employees']) !!}
                
                                        {!! html_report_filter([ 'filter_data'=>$filter_data, 'label'=>'exclude_user', 'display_name'=> 'Employees', 'display_plural_name'=>'Employees']) !!}
                                    @endif
                                        <tr>
                                            <td class="cellLeftEditTableHeader" width="10%" colspan="2" nowrap>
                                                <b>Start Date:</b>
                                            </td>
                                            <td class="cellRightEditTable">
                                                <input type="date" id="start_date" name="filter_data[start_date]" value="{{ getdate_helper('date', $filter_data['start_date']) }}" >
                                                <b>Show:</b>
                                                <select name="filter_data[show_days]">
                                                    {!! html_options([ 'options'=>$filter_data['show_days_options'], 'selected'=>$filter_data['show_days']]) !!}
                                                </select>
                                            </td>
                                        </tr>
            
                                        <tr>
                                            <td class="cellLeftEditTableHeader" colspan="2" nowrap>
                                                View:
                                            </td>
                                            <td class="cellRightEditTable">
                                                <select name="filter_data[view_type_id]" id="filter_view_type">
                                                    {!! html_options([ 'options'=>$filter_data['view_type_options'], 'selected'=>$filter_data['view_type_id'] ?? '']) !!}
                                                </select>
                                            </td>
                                        </tr>
            
                                        <tr>
                                            <td class="bg-primary text-white" colspan="3">
                                                <a name="schedule"></a>
                                                <input type="submit" name="do:view_schedule" value="View Schedule" onClick="ViewTypeTarget(document.getElementById('filter_view_type')); selectAllReportCriteria();">
                                                <input type="submit" name="do:print_schedule" value="Print Schedule" onClick="ViewTypeTarget('do:print_schedule'); selectAllReportCriteria();">
                                                @if ($permission->Check('schedule','view') OR $permission->Check('schedule','view_child'))
                                                    Group Schedule: <input type="checkbox" name="filter_data[group_schedule]" value="1">
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
            
                            <tr>
                                <td colspan="10">
                                    <iframe style="width:100%; height:0px; border: 5px" id="schedule_layer" name="Schedule" src="{$BASE_URL}/blank.html"></iframe>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script	language=JavaScript>
        $(document).ready(function(){
            countAllReportCriteria();
        })
        {literal}
        var report_criteria_elements = new Array(
                                            'filter_group',
                                            'filter_default_branch',
                                            'filter_default_department',
                                            'filter_schedule_branch',
                                            'filter_schedule_department',
                                            'filter_user_title',
                                            'filter_include_user',
                                            'filter_exclude_user' );
        
        function ViewTypeTarget(obj) {
            if ( typeof obj !== 'undefined' ) {
                if ( obj.value == 10 ) { //Month
                    action = '{/literal}{$BASE_URL}{literal}/schedule/ViewScheduleMonth.php';
                } else if ( obj.value == 20 ) { //Week
                    action = '{/literal}{$BASE_URL}{literal}/schedule/ViewScheduleWeek.php';
                } else if ( obj.value == 30 ) { //Day
                    action = '{/literal}{$BASE_URL}{literal}/schedule/ViewScheduleLinear.php';
                } else if ( obj == 'do:print_schedule' ) {
                    action = '{/literal}{$BASE_URL}{literal}/schedule/ViewSchedule.php';
                } else {
                    action = '{/literal}{$BASE_URL}{literal}/schedule/ViewSchedule.php';
                }
            } else {
                action = '{/literal}{$BASE_URL}{literal}/schedule/ViewSchedule.php';
            }
        
            //alert('aValue: '+ obj.value +' Action:'+ action);
            document.getElementById('schedule_form').action = action;
        
            if ( obj == 'do:print_schedule' ) {
                document.getElementById('schedule_form').target = '';
            } else {
                document.getElementById('schedule_form').target = 'Schedule';
                document.getElementById('schedule_layer').src = action;
            }
        
            //alert('bSrc:'+document.getElementById('schedule_layer').src);
        }
        
        {/literal}
    </script>
</x-app-layout>