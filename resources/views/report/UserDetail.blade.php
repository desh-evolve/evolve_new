<x-app-layout :title="'Input Example'">
    <style>
        th, td{
            padding: 5px !important;
        }

        .arrow-icon{
            background-color: green;
            color: white;
            font-size: 16px;
            border-radius: 50%;
            padding: 5px;
            align-items: center;
            margin: 2px;
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
                    
                    {{-- ----------------------------------------- --}}

                    <form method="post" name="report" action="{{ route('report.user_detail') }}" target="_self">
                        @csrf
                        <input type="hidden" id="action" name="action" value="">
            
                        <div id="contentBoxTwoEdit">
            
                            @if (!$ugdf->Validator->isValid())
                                {{-- form errors list here --}}
                            @endif
            
                            <table class="table table-bordered">
            
                                <tr class="bg-primary text-white">
                                    <td colspan="3">
                                        Report Filter Criteria
                                    </td>
                                </tr>
                
                                <tr>
                                    <td colspan="2" class="text-end">
                                        Start Date:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="date" id="start_date" name="filter_data[start_date]" value="{{getdate_helper('date', $filter_data['start_date'] ?? strtotime('first day of this month') )}}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </td>
                                </tr>
                
                                <tr>
                                    <td colspan="2" class="text-end">
                                        End Date:
                                    </td>
                                    <td class="cellRightEditTable">
                                        <input type="date" id="end_date" name="filter_data[end_date]" value="{{getdate_helper('date', $filter_data['end_date'] ?? strtotime('last day of this month') )}}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </td>
                                </tr>
            
                                {!!
                                    html_report_filter([
                                        'filter_data' => $filter_data ,
                                        'label' => 'user_status' ,
                                        'display_name' => 'Employee Status',
                                        'display_plural_name' => 'Employee Statuses'
                                    ])
                                !!}
                                
                                {!!
                                    html_report_filter([
                                        'filter_data' => $filter_data ,
                                        'label' => 'group' ,
                                        'display_name' => 'Group',
                                        'display_plural_name' => 'Groups'
                                    ])
                                !!}
                                
                                {!!
                                    html_report_filter([
                                        'filter_data' => $filter_data ,
                                        'label' => 'branch' ,
                                        'display_name' => 'Default Branch',
                                        'display_plural_name' => 'Branches'
                                    ])
                                !!}
                                
                                {!!
                                    html_report_filter([
                                        'filter_data' => $filter_data ,
                                        'label' => 'department' ,
                                        'display_name' => 'Default Department',
                                        'display_plural_name' => 'Departments'
                                    ])
                                !!}
                                
                                {!!
                                    html_report_filter([
                                        'filter_data' => $filter_data ,
                                        'label' => 'user_title' ,
                                        'display_name' => 'Employee Title',
                                        'display_plural_name' => 'Titles'
                                    ])
                                !!}
                                
                                {!!
                                    html_report_filter([
                                        'filter_data' => $filter_data ,
                                        'label' => 'include_user' ,
                                        'display_name' => 'Include Employees',
                                        'display_plural_name' => 'Employees'
                                    ])
                                !!}
                                
                                {!!
                                    html_report_filter([
                                        'filter_data' => $filter_data ,
                                        'label' => 'exclude_user' ,
                                        'display_name' => 'Exclude Employees',
                                        'display_plural_name' => 'Employees'
                                    ])
                                !!}
                                
                                {!!
                                    html_report_filter([
                                        'filter_data' => $filter_data ,
                                        'label' => 'column' ,
                                        'order'=> TRUE, 
                                        'display_name' => 'Columns',
                                        'display_plural_name' => 'Columns'
                                    ])
                                !!}
                                
                                {!!
                                    html_report_sort([
                                        'filter_data' => $filter_data
                                    ])
                                !!}
                            </table>
                        </div>

                        <div id="contentBoxFour">
                            <input class="btn btn-primary btn-sm" type="button" id="display_report" name="action" value="Display Report" onClick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').value = 'Display Report'; this.form.submit();">
                        </div>
                        
                    </form>

                    {{-- ---------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script	language=JavaScript>
        $(document).ready(function(){
            countAllReportCriteria();
        })
        var report_criteria_elements =  new Array(
            'filter_user_status',
            'filter_group',
            'filter_branch',
            'filter_department',
            'filter_user_title',
            'filter_include_user',
            'filter_exclude_user',
            'filter_column'
        );
    </script>
</x-app-layout>