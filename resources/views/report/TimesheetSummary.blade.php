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

                    <form method="post" name="report" action="/report/timesheet_summary" target="_self">
                        @csrf
                        <input type="hidden" id="action" name="action" value="">
            
                        <div id="contentBoxTwoEdit">
            
                            {{-- @if (!$ugdf->Validator->isValid()) --}}
                                {{-- include error list here --}}
                                {{-- {include file="form_errors.tpl" object="ugdf"} --}}
                            {{-- @endif --}}
                            
                            <table class="table table-bordered">
            
                            <tr class="bg-primary text-white">
                                <td colspan="3">
                                    Saved Reports
                                </td>
                            </tr>
            
                            <tr class="bg-primary text-white">
                                <td colspan="3">
                                    Report Filter Criteria
                                </td>
                            </tr>
            
                            <tr>
                                <td class="cellReportRadioColumn" rowspan="2">
                                    <input type="radio" class="checkbox" id="date_type_date" name="filter_data[date_type]" value="date" onClick="showReportDateType();" {{ (empty($filter_data['date_type']) OR $filter_data['date_type'] == 'date') ? 'checked' : '' }} >
                                </td>
                                <td class="cellLeftEditTableHeader">
                                    Start Date:
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="date" id="start_date" name="filter_data[start_date]" value="{{getdate_helper('date', $filter_data['start_date'] ?? '')}}">
                                    ie: {{$current_user_prefs->getDateFormatExample()}}
                                </td>
                            </tr>
            
                            <tr>
                                <td class="cellLeftEditTableHeader">
                                    End Date:
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="date" id="end_date" name="filter_data[end_date]" value="{{getdate_helper('date', $filter_data['end_date'] ?? '')}}">
                                    ie: {{$current_user_prefs->getDateFormatExample()}}
                                </td>
                            </tr>
                            @php
                                //dd($filter_data);
                            @endphp
                            {!! html_report_filter(['filter_data'=>$filter_data, 'date_type'=>true, 'label'=>'pay_period', 'display_name'=>'Pay Period', 'display_plural_name'=>'Pay Periods']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'user_status', 'display_name'=>'Employee Status', 'display_plural_name'=>'Employee Statuses']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'group', 'display_name'=>'Group', 'display_plural_name'=>'Groups']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'branch', 'display_name'=>'Default Branch', 'display_plural_name'=>'Branches']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'department', 'display_name'=>'Default Department', 'display_plural_name'=>'Departments']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'punch_branch', 'display_name'=>'Punch Branch', 'display_plural_name'=>'Branches']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'punch_department', 'display_name'=>'Punch Department', 'display_plural_name'=>'Departments']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'user_title', 'display_name'=>'Employee Title', 'display_plural_name'=>'Titles']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'include_user', 'display_name'=>'Include Employees', 'display_plural_name'=>'Employees']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'exclude_user', 'display_name'=>'Exclude Employees', 'display_plural_name'=>'Employees']) !!}
                            {!! html_report_filter(['filter_data'=>$filter_data, 'label'=>'column', 'order'=>TRUE, 'display_name'=>'Columns', 'display_plural_name'=>'Columns']) !!}
            
                            {!! html_report_group(['filter_data'=>$filter_data]) !!}
                            {!! html_report_sort(['filter_data'=>$filter_data]) !!}
                                            
                            <tr onClick="showHelpEntry('sort')">
                                <td colspan="2" class="{isvalid object="uwf" label="type" value="cellLeftEditTableHeader"}">
                                    Export Format:
                                </td>
                                <td class="cellRightEditTable">
                                    <select id="columns" name="filter_data[export_type]">
                                            {{-- {!!html_options(['options'=>$filter_data['export_type_options'], 'selected'=>$filter_data['export_type']]) !!} --}}
                                    </select>
                                </td>
                            </tr>
                                            
                            </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input 
                                type="submit" 
                                name="BUTTON" 
                                value="Display Report" 
                                onclick="handleDisplayReportClick(this)"
                            >
                            {{-- <input type="submit" name="BUTTON" value="Export" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Export';"> --}}
                        </div>
            
                        </table>
                    </form>

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
            showReportDateType();
        })

        var report_criteria_elements = new Array(
                                            'filter_user_status',
                                            'filter_group',
                                            'filter_branch',
                                            'filter_department',
                                            'filter_punch_branch',
                                            'filter_punch_department',
                                            'filter_user_title',
                                            'filter_pay_period',
                                            'filter_include_user',
                                            'filter_exclude_user',
                                            'filter_column');
        
        var report_date_type_elements = new Array();
        report_date_type_elements['date_type_date'] = new Array('start_date', 'end_date');
        report_date_type_elements['date_type_pay_period'] = new Array('src_filter_pay_period', 'filter_pay_period');

        function showReportDateType() {
            for ( i in report_date_type_elements ) {
                if ( document.getElementById( i ) ) {
                    if ( document.getElementById( i ).checked == true ) {
                        class_name = '';
                    } else {
                        class_name = 'DisableFormElement';
                    }
        
                    for (var x=0; x < report_date_type_elements[i].length ; x++) {
                        document.getElementById( report_date_type_elements[i][x] ).className = class_name;
                    }
                }
            }
        }

        function handleDisplayReportClick(button) {
            selectAllReportCriteria();
            button.form.target = '_blank';
            
            const actionInput = document.getElementById('action');
            if (actionInput) {
                actionInput.value = 'Display Report'; // optional: set a value if needed
            }
        }
        
    </script>
</x-app-layout>