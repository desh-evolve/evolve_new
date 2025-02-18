<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 2095 $
 * $Id: Sort.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Core
 */
class TimesheetDetailReport extends Report {

        function __construct() {
                $this->title = TTi18n::getText('TimeSheet Detail Report');
                $this->file_name = 'timesheet_detail_report';

                parent::__construct();

                return TRUE;
        }

        protected function _checkPermissions( $user_id, $company_id ) {
            if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
                            AND $this->getPermissionObject()->Check('report','view_timesheet_summary', $user_id, $company_id ) ) { //Piggyback on timesheet summary permissions.
                    return TRUE;
            } else {
                    //Debug::Text('Regular employee viewing their own timesheet...', __FILE__, __LINE__, __METHOD__,10);
                    //Regular employee printing timesheet for themselves. Force specific config options.
                    //Get current pay period from config, then overwrite it with
                    $filter_config = $this->getFilterConfig();
                    if ( isset($filter_config['time_period']['pay_period_id']) ) {
                            $pay_period_id = $filter_config['time_period']['pay_period_id'];
                    } else {
                            $pay_period_id = 0;
                    }
                    $this->setFilterConfig( array( 'include_user_id' => array($user_id), 'time_period' => array( 'time_period' => 'custom_pay_period', 'pay_period_id' => $pay_period_id ) ) );

                    return TRUE;
            }

            return FALSE;
        }

        protected function _getOptions( $name, $params = NULL ) {
                $retval = NULL;
                switch( $name ) {
                        case 'default_setup_fields':
                                $retval = array(
                                                                                'template',
                                                                                'time_period',
                                                                                'columns',
                                                           );
                                break;
                        case 'setup_fields':
                                $retval = array(
                                                                                //Static Columns - Aggregate functions can't be used on these.
                                                                                '-1000-template' => TTi18n::gettext('Template'),
                                                                                '-1010-time_period' => TTi18n::gettext('Time Period'),

                                                                                '-2010-user_status_id' => TTi18n::gettext('Employee Status'),
                                                                                '-2020-user_group_id' => TTi18n::gettext('Employee Group'),
                                                                                '-2030-user_title_id' => TTi18n::gettext('Employee Title'),
                                                                                '-2040-include_user_id' => TTi18n::gettext('Employee Include'),
                                                                                '-2050-exclude_user_id' => TTi18n::gettext('Employee Exclude'),
                                                                                '-2060-default_branch_id' => TTi18n::gettext('Default Branch'),
                                                                                '-2070-default_department_id' => TTi18n::gettext('Default Department'),
                                                                                '-2080-punch_branch_id' => TTi18n::gettext('Punch Branch'),
                                                                                '-2090-punch_department_id' => TTi18n::gettext('Punch Department'),

                                                                                '-5000-columns' => TTi18n::gettext('Display Columns'),
                                                                                '-5010-group' => TTi18n::gettext('Group By'),
                                                                                '-5020-sub_total' => TTi18n::gettext('SubTotal By'),
                                                                                '-5030-sort' => TTi18n::gettext('Sort By'),
                                                           );
                                break;
                        case 'time_period':
                                $retval = TTDate::getTimePeriodOptions();
                                break;
                        case 'date_columns':
                                $retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
                                break;
                        case 'static_columns':
                                $retval = array(
                                                                                //Static Columns - Aggregate functions can't be used on these.
                                                                                '-1000-first_name' => TTi18n::gettext('First Name'),
                                                                                '-1001-middle_name' => TTi18n::gettext('Middle Name'),
                                                                                '-1002-last_name' => TTi18n::gettext('Last Name'),
                                                                                '-1005-full_name' => TTi18n::gettext('Full Name'),
                                                                                '-1030-employee_number' => TTi18n::gettext('Employee #'),
                                                                                '-1040-status' => TTi18n::gettext('Status'),
                                                                                '-1050-title' => TTi18n::gettext('Title'),
                                                                                '-1060-province' => TTi18n::gettext('Province/State'),
                                                                                '-1070-country' => TTi18n::gettext('Country'),
                                                                                '-1080-user_group' => TTi18n::gettext('Group'),
                                                                                '-1090-default_branch' => TTi18n::gettext('Default Branch'),
                                                                                '-1100-default_department' => TTi18n::gettext('Default Department'),
                                                                                '-1110-currency' => TTi18n::gettext('Currency'),
                                                                                //'-1111-current_currency' => TTi18n::gettext('Current Currency'),

                                                                                //'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
                                                                                //'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

                                                                                '-1400-permission_control' => TTi18n::gettext('Permission Group'),
                                                                                '-1410-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
                                                                                '-1420-policy_group' => TTi18n::gettext('Policy Group'),

                                                                                //Handled in date_columns above.
                                                                                //'-1430-pay_period' => TTi18n::gettext('Pay Period'),

                                                                                '-1430-branch' => TTi18n::gettext('Branch'),
                                                                                '-1440-department' => TTi18n::gettext('Department'),

                                                                                '-1500-min_punch_time_stamp' => TTi18n::gettext('First In Punch'),
                                                                                '-1505-max_punch_time_stamp' => TTi18n::gettext('Last Out Punch'),

                                                                                '-1510-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
                                                                                '-1515-verified_time_sheet_date' => TTi18n::gettext('Verified TimeSheet Date'),
                                                           );

                                $retval = array_merge( $retval, $this->getOptions('date_columns') );
                                ksort($retval);
                                break;
                        case 'dynamic_columns':
                                $retval = array(
                                                                                //Dynamic - Aggregate functions can be used

                                                                                //Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
                                                                                //'-2010-hourly_rate' => TTi18n::gettext('Hourly Rate'),

                                                                                //'-2070-schedule_working' => TTi18n::gettext('Scheduled Time'),
                                                                                //'-2080-schedule_absence' => TTi18n::gettext('Scheduled Absence'),

                                                                                //'-2085-worked_days' => TTi18n::gettext('Worked Days'), //Doesn't work for this report.
                                                                                //'-2090-worked_time' => TTi18n::gettext('Worked Time'),
                                                                                //'-2100-actual_time' => TTi18n::gettext('Actual Time'),
                                                                                //'-2110-actual_time_diff' => TTi18n::gettext('Actual Time Difference'),
                                                                                //'-2130-paid_time' => TTi18n::gettext('Paid Time'),
                                                                                '-2290-regular_time' => TTi18n::gettext('Regular Time'),

                                                                                '-2500-gross_wage' => TTi18n::gettext('Gross Wage'),
                                                                                '-2530-regular_time_wage' => TTi18n::gettext('Regular Time - Wage'),
                                                                                //'-2540-actual_time_wage' => TTi18n::gettext('Actual Time Wage'),
                                                                                //'-2550-actual_time_diff_wage' => TTi18n::gettext('Actual Time Difference Wage'),

                                                                                '-2690-regular_time_hourly_rate' => TTi18n::gettext('Regular Time - Hourly Rate'),

                                                        );

                                $retval = array_merge( $retval, $this->getOptions('overtime_columns'), $this->getOptions('premium_columns'), $this->getOptions('absence_columns') );
                                ksort($retval);

                                break;
                        case 'overtime_columns':
                                //Get all Overtime policies.
                                $retval = array();
                                $otplf = TTnew( 'OverTimePolicyListFactory' );
                                $otplf->getByCompanyId( $this->getUserObject()->getCompany() );
                                if ( $otplf->getRecordCount() > 0 ) {
                                        foreach( $otplf as $otp_obj ) {
                                                $retval['-2291-over_time_policy-'.$otp_obj->getId()] = $otp_obj->getName();
                                                $retval['-2591-over_time_policy-'.$otp_obj->getId().'_wage'] = $otp_obj->getName() .' '. TTi18n::getText('- Wage');
                                                $retval['-2691-over_time_policy-'.$otp_obj->getId().'_hourly_rate'] = $otp_obj->getName() .' '. TTi18n::getText('- Hourly Rate');
                                        }
                                }
                                break;
                        case 'premium_columns':
                                $retval = array();
                                //Get all Premium policies.
                                $pplf = TTnew( 'PremiumPolicyListFactory' );
                                $pplf->getByCompanyId( $this->getUserObject()->getCompany() );
                                if ( $pplf->getRecordCount() > 0 ) {
                                        foreach( $pplf as $pp_obj ) {
                                                $retval['-2291-premium_policy-'.$pp_obj->getId()] = $pp_obj->getName();
                                                $retval['-2591-premium_policy-'.$pp_obj->getId().'_wage'] = $pp_obj->getName() .' '. TTi18n::getText('- Wage');
                                                $retval['-2691-premium_policy-'.$pp_obj->getId().'_hourly_rate'] = $pp_obj->getName() .' '. TTi18n::getText('- Hourly Rate');
                                        }
                                }
                                break;
                        case 'absence_columns':
                                $retval = array();
                                //Get all Absence Policies.
                                $aplf = TTnew( 'AbsencePolicyListFactory' );
                                $aplf->getByCompanyId( $this->getUserObject()->getCompany() );
                                if ( $aplf->getRecordCount() > 0 ) {
                                        foreach( $aplf as $ap_obj ) {
                                                $retval['-2291-absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
                                                if ( $ap_obj->getType() == 10 ) {
                                                        $retval['-2591-absence_policy-'.$ap_obj->getId().'_wage'] = $ap_obj->getName() .' '. TTi18n::getText('- Wage');
                                                        $retval['-2691-absence_policy-'.$ap_obj->getId().'_hourly_rate'] = $ap_obj->getName() .' '. TTi18n::getText('- Hourly Rate');
                                                }
                                        }
                                }
                                break;
                        case 'columns':
                                $retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns') );
                                break;
                        case 'column_format':
                                //Define formatting function for each column.
                                $columns = $this->getOptions('dynamic_columns');
                                if ( is_array($columns) ) {
                                        foreach($columns as $column => $name ) {
                                                if ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE ) {
                                                        $retval[$column] = 'currency';
                                                } elseif ( strpos($column, '_time') OR strpos($column, '_policy') ) {
                                                        $retval[$column] = 'time_unit';
                                                }
                                        }
                                }
                                $retval['verified_time_sheet_date'] = 'time_stamp';
                                break;
                        case 'aggregates':
                                $retval = array();
                                $dynamic_columns = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
                                if ( is_array($dynamic_columns ) ) {
                                        foreach( $dynamic_columns as $column ) {
                                                switch ( $column ) {
                                                        default:
                                                                if ( strpos($column, '_hourly_rate') !== FALSE ) {
                                                                        $retval[$column] = 'avg';
                                                                } else {
                                                                        $retval[$column] = 'sum';
                                                                }
                                                }
                                        }
                                }
                                $retval['verified_time_sheet'] = 'first';
                                $retval['verified_time_sheet_date'] = 'first';
                                break;
                        case 'templates':
                                $retval = array(

                                    '-1010-by_employee+regular' => TTi18n::gettext('Regular Time by Employee'),
                                    '-1020-by_employee+overtime' => TTi18n::gettext('Overtime by Employee'),
                                    '-1030-by_employee+premium' => TTi18n::gettext('Premium Time by Employee'),
                                    '-1040-by_employee+absence' => TTi18n::gettext('Absence Time by Employee'),
                                    '-1050-by_employee+regular+overtime+premium+absence' => TTi18n::gettext('All Time by Employee'),

                                    '-1060-by_employee+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Employee'),
                                    '-1070-by_employee+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Employee'),
                                    '-1080-by_employee+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Employee'),
                                    '-1090-by_employee+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Employee'),
                                    '-1100-by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Employee'),

                                    '-1110-by_date_by_full_name+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Date/Employee'),
                                    '-1120-by_date_by_full_name+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Date/Employee'),
                                    '-1130-by_date_by_full_name+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Date/Employee'),
                                    '-1140-by_date_by_full_name+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Date/Employee'),
                                    '-1150-by_date_by_full_name+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Date/Employee'),

                                    '-1160-by_full_name_by_date+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Employee/Date'),
                                    '-1170-by_full_name_by_date+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Employee/Date'),
                                    '-1180-by_full_name_by_date+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Employee/Date'),
                                    '-1190-by_full_name_by_date+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Employee/Date'),
                                    '-1200-by_full_name_by_date+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Employee/Date'),

                                    '-1210-by_branch+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch'),
                                    '-1220-by_branch+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Branch'),
                                    '-1230-by_branch+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Branch'),
                                    '-1240-by_branch+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Branch'),
                                    '-1250-by_branch+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Branch'),

                                    '-1260-by_department+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Department'),
                                    '-1270-by_department+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Department'),
                                    '-1280-by_department+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Department'),
                                    '-1290-by_department+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Department'),
                                    '-1300-by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Department'),

                                    '-1310-by_branch_by_department+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch/Department'),
                                    '-1320-by_branch_by_department+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Branch/Department'),
                                    '-1330-by_branch_by_department+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Branch/Department'),
                                    '-1340-by_branch_by_department+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Branch/Department'),
                                    '-1350-by_branch_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Branch/Department'),

                                    '-1360-by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period'),
                                    '-1370-by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period'),
                                    '-1380-by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period'),
                                    '-1390-by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period'),
                                    '-1400-by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period'),

                                    '-1410-by_pay_period_by_employee+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Employee'),
                                    '-1420-by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Employee'),
                                    '-1430-by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Employee'),
                                    '-1440-by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Employee'),
                                    '-1450-by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Employee'),

                                    '-1451-by_pay_period_by_date_stamp_by_employee+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Date/Employee'),
                                    '-1452-by_pay_period_by_date_stamp_by_employee+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Date/Employee'),
                                    '-1453-by_pay_period_by_date_stamp_by_employee+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Date/Employee'),
                                    '-1454-by_pay_period_by_date_stamp_by_employee+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Date/Employee'),
                                    '-1455-by_pay_period_by_date_stamp_by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Date/Employee'),

                                    '-1460-by_pay_period_by_branch+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Branch'),
                                    '-1470-by_pay_period_by_branch+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Branch'),
                                    '-1480-by_pay_period_by_branch+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Branch'),
                                    '-1490-by_pay_period_by_branch+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Branch'),
                                    '-1500-by_pay_period_by_branch+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Branch'),

                                    '-1510-by_pay_period_by_department+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Department'),
                                    '-1520-by_pay_period_by_department+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Department'),
                                    '-1530-by_pay_period_by_department+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Department'),
                                    '-1540-by_pay_period_by_department+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Department'),
                                    '-1550-by_pay_period_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Department'),

                                    '-1560-by_pay_period_by_branch_by_department+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Pay Period/Branch/Department'),
                                    '-1570-by_pay_period_by_branch_by_department+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Period/Branch/Department'),
                                    '-1580-by_pay_period_by_branch_by_department+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Period/Branch/Department'),
                                    '-1590-by_pay_period_by_branch_by_department+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Period/Branch/Department'),
                                    '-1600-by_pay_period_by_branch_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Period/Branch/Department'),

                                    '-1610-by_employee_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Employee/Pay Period'),
                                    '-1620-by_employee_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Employee/Pay Period'),
                                    '-1630-by_employee_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Employee/Pay Period'),
                                    '-1640-by_employee_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Employee/Pay Period'),
                                    '-1650-by_employee_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Employee/Pay Period'),

                                    '-1660-by_branch_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch/Pay Period'),
                                    '-1670-by_branch_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Branch/Pay Period'),
                                    '-1680-by_branch_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Branch/Pay Period'),
                                    '-1690-by_branch_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Branch/Pay Period'),
                                    '-1700-by_branch_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Branch/Pay Period'),

                                    '-1810-by_department_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Department/Pay Period'),
                                    '-1820-by_department_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Department/Pay Period'),
                                    '-1830-by_department_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Department/Pay Period'),
                                    '-1840-by_department_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Department/Pay Period'),
                                    '-1850-by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Pay Department/Pay Period'),

                                    '-1860-by_branch_by_department_by_pay_period+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Branch/Department/Pay Period'),
                                    '-1870-by_branch_by_department_by_pay_period+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Branch/Department/Pay Period'),
                                    '-1880-by_branch_by_department_by_pay_period+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Branch/Department/Pay Period'),
                                    '-1890-by_branch_by_department_by_pay_period+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Branch/Department/Pay Period'),
                                    '-1900-by_branch_by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Branch/Department/Pay Period'),

                                    '-1910-by_full_name_by_dow+regular+regular_wage' => TTi18n::gettext('Regular Time+Wage by Employee/Day of Week'),
                                    '-1920-by_full_name_by_dow+overtime+overtime_wage' => TTi18n::gettext('Overtime+Wage by Pay Employee/Day of Week'),
                                    '-1930-by_full_name_by_dow+premium+premium_wage' => TTi18n::gettext('Premium Time+Wage by Pay Employee/Day of Week'),
                                    '-1940-by_full_name_by_dow+absence+absence_wage' => TTi18n::gettext('Absence Time+Wage by Pay Employee/Day of Week'),
                                    '-1950-by_full_name_by_dow+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => TTi18n::gettext('All Time+Wage by Employee/Day of Week'),
               );

                                break;
                        case 'template_config':
                            $template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
                            if ( isset($template) AND $template != '' ) {
                                switch( $template ) {
                                            case 'specific_template_name':
                                                    //$retval['column'] = array();
                                                    //$retval['filter'] = array();
                                                    //$retval['group'] = array();
                                                    //$retval['sub_total'] = array();
                                                    //$retval['sort'] = array();
                                                    break;
                                            default:
                                                    Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
                                                    $retval['-1010-time_period']['time_period'] = 'last_pay_period';

                                                    //Parse template name, and use the keywords separated by '+' to determine settings.
                                                    $template_keywords = explode('+', $template );
                                                    if ( is_array($template_keywords) ) {
                                                            foreach( $template_keywords as $template_keyword ) {
                                                                    Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__,10);

                                                                    switch( $template_keyword ) {
                                                                            //Columns
                                                                            case 'regular':
                                                                                    $retval['columns'][] = 'regular_time';
                                                                                    break;
                                                                            case 'overtime':
                                                                            case 'premium':
                                                                            case 'absence':
                                                                                    $columns = Misc::trimSortPrefix( $this->getOptions( $template_keyword.'_columns') );
                                                                                    if ( is_array($columns) ) {
                                                                                            foreach( $columns as $column => $column_name ) {
                                                                                                    if ( strpos( $column, '_wage') === FALSE AND strpos( $column, '_hourly_rate') === FALSE ) {
                                                                                                            $retval['columns'][] = $column;
                                                                                                    }
                                                                                            }
                                                                                    }
                                                                                    break;

                                                                            case 'regular_wage':
                                                                                    $retval['columns'][] = 'regular_time_wage';
                                                                                    break;
                                                                            case 'overtime_wage':
                                                                            case 'premium_wage':
                                                                            case 'absence_wage':
                                                                                    $columns = Misc::trimSortPrefix( $this->getOptions( str_replace('_wage', '', $template_keyword).'_columns' ) );
                                                                                    if ( is_array($columns) ) {
                                                                                            foreach( $columns as $column => $column_name ) {
                                                                                                    if ( strpos( $column, '_wage') !== FALSE ) {
                                                                                                            $retval['columns'][] = $column;
                                                                                                    }
                                                                                            }
                                                                                    }
                                                                                    break;

                                                                            //Filter

                                                                            //Group By
                                                                            //SubTotal
                                                                            //Sort

                                                                            case 'by_employee':
                                                                                    $retval['columns'][] = 'first_name';
                                                                                    $retval['columns'][] = 'last_name';

                                                                                    $retval['group'][] = 'first_name';
                                                                                    $retval['group'][] = 'last_name';

                                                                                    $retval['sort'][] = array('last_name' => 'asc');
                                                                                    $retval['sort'][] = array('first_name' => 'asc');
                                                                                    break;
                                                                            case 'by_branch':
                                                                                    $retval['columns'][] = 'branch';

                                                                                    $retval['group'][] = 'branch';

                                                                                    $retval['sort'][] = array('branch' => 'asc');
                                                                                    break;
                                                                            case 'by_department':
                                                                                    $retval['columns'][] = 'department';

                                                                                    $retval['group'][] = 'department';

                                                                                    $retval['sort'][] = array('department' => 'asc');
                                                                                    break;
                                                                            case 'by_branch_by_department':
                                                                                    $retval['columns'][] = 'branch';
                                                                                    $retval['columns'][] = 'department';

                                                                                    $retval['group'][] = 'branch';
                                                                                    $retval['group'][] = 'department';

                                                                                    $retval['sub_total'][] = 'branch';

                                                                                    $retval['sort'][] = array('branch' => 'asc');
                                                                                    $retval['sort'][] = array('department' => 'asc');
                                                                                    break;
                                                                            case 'by_pay_period':
                                                                                    $retval['columns'][] = 'pay_period';

                                                                                    $retval['group'][] = 'pay_period';

                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    break;
                                                                            case 'by_pay_period_by_employee':
                                                                                    $retval['columns'][] = 'pay_period';
                                                                                    $retval['columns'][] = 'first_name';
                                                                                    $retval['columns'][] = 'last_name';

                                                                                    $retval['group'][] = 'pay_period';
                                                                                    $retval['group'][] = 'first_name';
                                                                                    $retval['group'][] = 'last_name';

                                                                                    $retval['sub_total'][] = 'pay_period';

                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    $retval['sort'][] = array('last_name' => 'asc');
                                                                                    $retval['sort'][] = array('first_name' => 'asc');
                                                                                    break;
                                                                            case 'by_pay_period_by_date_stamp_by_employee':
                                                                                    $retval['columns'][] = 'pay_period';
                                                                                    $retval['columns'][] = 'date_stamp';
                                                                                    $retval['columns'][] = 'first_name';
                                                                                    $retval['columns'][] = 'last_name';

                                                                                    $retval['group'][] = 'pay_period';
                                                                                    $retval['group'][] = 'date_stamp';
                                                                                    $retval['group'][] = 'first_name';
                                                                                    $retval['group'][] = 'last_name';

                                                                                    $retval['sub_total'][] = 'pay_period';
                                                                                    $retval['sub_total'][] = 'date_stamp';

                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    $retval['sort'][] = array('date_stamp' => 'asc');
                                                                                    $retval['sort'][] = array('last_name' => 'asc');
                                                                                    $retval['sort'][] = array('first_name' => 'asc');
                                                                                    break;
                                                                            case 'by_pay_period_by_branch':
                                                                                    $retval['columns'][] = 'pay_period';
                                                                                    $retval['columns'][] = 'branch';

                                                                                    $retval['group'][] = 'pay_period';
                                                                                    $retval['group'][] = 'branch';

                                                                                    $retval['sub_total'][] = 'pay_period';

                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    $retval['sort'][] = array('branch' => 'asc');
                                                                                    break;
                                                                            case 'by_pay_period_by_department':
                                                                                    $retval['columns'][] = 'pay_period';
                                                                                    $retval['columns'][] = 'department';

                                                                                    $retval['group'][] = 'pay_period';
                                                                                    $retval['group'][] = 'department';

                                                                                    $retval['sub_total'][] = 'pay_period';

                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    $retval['sort'][] = array('department' => 'asc');
                                                                                    break;
                                                                            case 'by_pay_period_by_branch_by_department':
                                                                                    $retval['columns'][] = 'pay_period';
                                                                                    $retval['columns'][] = 'branch';
                                                                                    $retval['columns'][] = 'department';

                                                                                    $retval['group'][] = 'pay_period';
                                                                                    $retval['group'][] = 'branch';
                                                                                    $retval['group'][] = 'department';

                                                                                    $retval['sub_total'][] = 'pay_period';
                                                                                    $retval['sub_total'][] = 'branch';

                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    $retval['sort'][] = array('branch' => 'asc');
                                                                                    $retval['sort'][] = array('department' => 'asc');
                                                                                    break;
                                                                            case 'by_employee_by_pay_period':
                                                                                    $retval['columns'][] = 'full_name';
                                                                                    $retval['columns'][] = 'pay_period';

                                                                                    $retval['group'][] = 'full_name';
                                                                                    $retval['group'][] = 'pay_period';

                                                                                    $retval['sub_total'][] = 'full_name';

                                                                                    $retval['sort'][] = array('full_name' => 'asc');
                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    break;
                                                                            case 'by_branch_by_pay_period':
                                                                                    $retval['columns'][] = 'branch';
                                                                                    $retval['columns'][] = 'pay_period';

                                                                                    $retval['group'][] = 'branch';
                                                                                    $retval['group'][] = 'pay_period';

                                                                                    $retval['sub_total'][] = 'branch';

                                                                                    $retval['sort'][] = array('branch' => 'asc');
                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    break;
                                                                            case 'by_department_by_pay_period':
                                                                                    $retval['columns'][] = 'department';
                                                                                    $retval['columns'][] = 'pay_period';

                                                                                    $retval['group'][] = 'department';
                                                                                    $retval['group'][] = 'pay_period';

                                                                                    $retval['sub_total'][] = 'department';

                                                                                    $retval['sort'][] = array('department' => 'asc');
                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    break;
                                                                            case 'by_branch_by_department_by_pay_period':
                                                                                    $retval['columns'][] = 'branch';
                                                                                    $retval['columns'][] = 'department';
                                                                                    $retval['columns'][] = 'pay_period';

                                                                                    $retval['group'][] = 'branch';
                                                                                    $retval['group'][] = 'department';
                                                                                    $retval['group'][] = 'pay_period';

                                                                                    $retval['sub_total'][] = 'branch';
                                                                                    $retval['sub_total'][] = 'department';

                                                                                    $retval['sort'][] = array('branch' => 'asc');
                                                                                    $retval['sort'][] = array('department' => 'asc');
                                                                                    $retval['sort'][] = array('pay_period' => 'asc');
                                                                                    break;
                                                                            case 'by_date_by_full_name':
                                                                                    $retval['columns'][] = 'date_stamp';
                                                                                    $retval['columns'][] = 'full_name';

                                                                                    $retval['group'][] = 'date_stamp';
                                                                                    $retval['group'][] = 'full_name';

                                                                                    $retval['sub_total'][] = 'date_stamp';

                                                                                    $retval['sort'][] = array('date_stamp' => 'asc');
                                                                                    $retval['sort'][] = array('full_name' => 'asc');
                                                                                    break;
                                                                            case 'by_full_name_by_date':
                                                                                    $retval['columns'][] = 'full_name';
                                                                                    $retval['columns'][] = 'date_stamp';

                                                                                    $retval['group'][] = 'full_name';
                                                                                    $retval['group'][] = 'date_stamp';

                                                                                    $retval['sub_total'][] = 'full_name';

                                                                                    $retval['sort'][] = array('full_name' => 'asc');
                                                                                    $retval['sort'][] = array('date_stamp' => 'asc');
                                                                                    break;
                                                                            case 'by_full_name_by_dow':
                                                                                    $retval['columns'][] = 'full_name';
                                                                                    $retval['columns'][] = 'date_dow';

                                                                                    $retval['group'][] = 'full_name';
                                                                                    $retval['group'][] = 'date_dow';

                                                                                    $retval['sub_total'][] = 'full_name';

                                                                                    $retval['sort'][] = array('full_name' => 'asc');
                                                                                    $retval['sort'][] = array('date_dow' => 'asc');
                                                                                    break;
                                                                    }
                                                            }
                                                    }
                                                    break;
                                    }
                            }

                                //Set the template dropdown as well.
                                $retval['-1000-template'] = $template;

                                //Add sort prefixes so Flex can maintain order.
                                if ( isset($retval['filter']) ) {
                                        $retval['-5000-filter'] = $retval['filter'];
                                        unset($retval['filter']);
                                }
                                if ( isset($retval['columns']) ) {
                                        $retval['-5010-columns'] = $retval['columns'];
                                        unset($retval['columns']);
                                }
                                if ( isset($retval['group']) ) {
                                        $retval['-5020-group'] = $retval['group'];
                                        unset($retval['group']);
                                }
                                if ( isset($retval['sub_total']) ) {
                                        $retval['-5030-sub_total'] = $retval['sub_total'];
                                        unset($retval['sub_total']);
                                }
                                if ( isset($retval['sort']) ) {
                                        $retval['-5040-sort'] = $retval['sort'];
                                        unset($retval['sort']);
                                }
                                Debug::Arr($retval, ' Template Config for: '. $template, __FILE__, __LINE__, __METHOD__,10);

                                break;
                        default:
                                //Call report parent class options function for options valid for all reports.
                                $retval = $this->__getOptions( $name );
                                break;
                }

                return $retval;
        }

        function getPolicyHourlyRates() {
                //Take into account wage groups!

                //Get all Overtime policies.
                $otplf = TTnew( 'OverTimePolicyListFactory' );
                $otplf->getByCompanyId( $this->getUserObject()->getCompany() );
                if ( $otplf->getRecordCount() > 0 ) {
                        foreach( $otplf as $otp_obj ) {
                                Debug::Text('Over Time Policy ID: '. $otp_obj->getId() .' Rate: '. $otp_obj->getRate() , __FILE__, __LINE__, __METHOD__,10);
                                $policy_rates['over_time_policy-'.$otp_obj->getId()] = $otp_obj;
                        }
                }

                //Get all Premium policies.
                $pplf = TTnew( 'PremiumPolicyListFactory' );
                $pplf->getByCompanyId( $this->getUserObject()->getCompany() );
                if ( $pplf->getRecordCount() > 0 ) {
                        foreach( $pplf as $pp_obj ) {
                                $policy_rates['premium_policy-'.$pp_obj->getId()] = $pp_obj;
                        }
                }

                //Get all Absence Policies.
                $aplf = TTnew( 'AbsencePolicyListFactory' );
                $aplf->getByCompanyId( $this->getUserObject()->getCompany() );
                if ( $aplf->getRecordCount() > 0 ) {
                        foreach( $aplf as $ap_obj ) {
                                if ( $ap_obj->getType() == 10 ) {
                                        $policy_rates['absence_policy-'.$ap_obj->getId()] = $ap_obj;
                                } else {
                                        $policy_rates['absence_policy-'.$ap_obj->getId()] = FALSE;
                                }
                        }
                }

                return $policy_rates;
        }

        //Get raw data for report
        function _getData( $format = NULL ) {
                $this->tmp_data = array('user_date_total' => array(), 'user' => array(), 'verified_timesheet' => array(), 'punch_rows' => array() );

                $columns = $this->getColumnConfig();
                $filter_data = $this->getFilterConfig();
                $policy_hourly_rates = $this->getPolicyHourlyRates();

                if ( $this->getPermissionObject()->Check('punch','view') == FALSE OR $this->getPermissionObject()->Check('wage','view') == FALSE ) {
                        $hlf = TTnew( 'HierarchyListFactory' );
                        $permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
                        Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
                } else {
                        //Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
                        $permission_children_ids = array();
                        $wage_permission_children_ids = array();
                }
                if ( $this->getPermissionObject()->Check('punch','view') == FALSE ) {
                        if ( $this->getPermissionObject()->Check('punch','view_child') == FALSE ) {
                                $permission_children_ids = array();
                        }
                        if ( $this->getPermissionObject()->Check('punch','view_own') ) {
                                $permission_children_ids[] = $this->getUserObject()->getID();
                        }

                        $filter_data['permission_children_ids'] = $permission_children_ids;
                }
                //Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
                if ( $this->getPermissionObject()->Check('wage','view') == TRUE ) {
                        $wage_permission_children_ids = TRUE;
                } elseif ( $this->getPermissionObject()->Check('wage','view') == FALSE ) {
                        if ( $this->getPermissionObject()->Check('wage','view_child') == FALSE ) {
                                $wage_permission_children_ids = array();
                        }
                        if ( $this->getPermissionObject()->Check('wage','view_own') ) {
                                $wage_permission_children_ids[] = $this->getUserObject()->getID();
                        }
                }
                //Debug::Text(' Permission Children: '. count($permission_children_ids) .' Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
                //Debug::Arr($permission_children_ids, 'Permission Children: '. count($permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
                //Debug::Arr($wage_permission_children_ids, 'Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);

                $pay_period_ids = array();

                $udtlf = TTnew( 'UserDateTotalListFactory' );
                $udtlf->getTimesheetDetailReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
                Debug::Text(' Total Rows: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
                $this->getProgressBarObject()->start( $this->getAMFMessageID(), $udtlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
                if ( $udtlf->getRecordCount() > 0 ) {
                        foreach ( $udtlf as $key => $udt_obj ) {
                                $pay_period_ids[$udt_obj->getColumn('pay_period_id')] = TRUE;

                                $user_id = $udt_obj->getColumn('user_id');

                                $date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
                                $status_id = $udt_obj->getColumn('status_id');
                                $type_id = $udt_obj->getColumn('type_id');

                                $branch = $udt_obj->getColumn('branch');
                                $department = $udt_obj->getColumn('department');

                                //Can we get rid of Worked and Paid time to simplify things? People have a hard time figuring out what these are anyways for reports.
                                //Paid time doesn't belong to a branch/department, so if we try to group by branch/department there will
                                //always be a blank line showing just the paid time. So if they don't want to display paid time, just exclude it completely.
                                $column = $udt_obj->getTimeCategory();
                                //if ( $column == 'paid_time' OR $column == 'worked_time' ) {
                                //Include worked time for the PDF timesheet.
                                if ( $column == 'paid_time' ) {
                                        $column = NULL;
                                }

                                //Debug::Text('Column: '. $column .' Total Time: '. $udt_obj->getColumn('total_time') .' Status: '. $status_id .' Type: '. $type_id .' Rate: '. $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__,10);
                                if ( ( isset($filter_data['include_no_data_users']) AND $filter_data['include_no_data_users'] == 1 )
                                                OR ( !isset($filter_data['include_no_data_users']) AND $date_stamp != '' AND $column != '' AND $udt_obj->getColumn('total_time') != 0 )  ) {

                                        $hourly_rate = 0;
                                        if ( $wage_permission_children_ids === TRUE OR in_array( $user_id, $wage_permission_children_ids) ) {
                                                $hourly_rate = $udt_obj->getColumn( 'hourly_rate' );
                                        }
                                        if ( isset($policy_hourly_rates[$column]) AND is_object($policy_hourly_rates[$column]) ) {
                                                $hourly_rate = $policy_hourly_rates[$column]->getHourlyRate( $hourly_rate );
                                        }

                                        if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]) ) {
                                                //Add time/wage and calculate average hourly rate.
                                                if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column]) ) {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
                                                } else {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
                                                }

                                                if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage']) ) {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'] += bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
                                                } else {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'] = bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
                                                }

                                                if ( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] > 0 ) {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_hourly_rate'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column], 3600) );
                                                } else {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_hourly_rate'] = $hourly_rate;
                                                }

                                                //Gross wage calculation must go here otherwise it gets doubled up.
                                                if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage']) ) {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] += $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'];
                                                } else {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] = $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'];
                                                }
                                        } else {
                                                $this->tmp_data['user_date_total'][$user_id][$date_stamp] = array(
                                                    'branch' => $branch,
                                                    'department' => $department,
                                                    'pay_period_start_date' => strtotime( $udt_obj->getColumn('pay_period_start_date') ),
                                                    'pay_period_end_date' => strtotime( $udt_obj->getColumn('pay_period_end_date') ),
                                                    'pay_period_transaction_date' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
                                                    'pay_period' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
                                                    'pay_period_id' => $udt_obj->getColumn('pay_period_id'),
                                                    'min_punch_time_stamp' => strtotime( $udt_obj->getColumn('min_punch_time_stamp') ),
                                                    'max_punch_time_stamp' => strtotime( $udt_obj->getColumn('max_punch_time_stamp') ),
                                                    $column => $udt_obj->getColumn('total_time'),
                                                    $column.'_hourly_rate' => $hourly_rate,
                                                    $column.'_wage' => bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate ),
                                                                                                                        );
                                        }

/*
                                        if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]) ) {
                                                //Add time/wage and calculate average hourly rate.
                                                if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column]) ) {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
                                                } else {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
                                                }

                                                if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage']) ) {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'] += bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
                                                } else {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'] = bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
                                                }

                                                if ( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] > 0 ) {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_hourly_rate'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column], 3600) );
                                                } else {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_hourly_rate'] = $hourly_rate;
                                                }

                                                //Gross wage calculation must go here otherwise it gets doubled up.
                                                if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage']) ) {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] += $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'];
                                                } else {
                                                        $this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] = $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'];
                                                }
                                        } else {
                                                $this->tmp_data['user_date_total'][$user_id][$date_stamp] = array(
                                            'pay_period_start_date' => strtotime( $udt_obj->getColumn('pay_period_start_date') ),
                                            'pay_period_end_date' => strtotime( $udt_obj->getColumn('pay_period_end_date') ),
                                            'pay_period_transaction_date' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
                                            'pay_period' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
                                            'pay_period_id' => $udt_obj->getColumn('pay_period_id'),
                                           'min_punch_time_stamp' => strtotime( $udt_obj->getColumn('min_punch_time_stamp') ),
                                            'max_punch_time_stamp' => strtotime( $udt_obj->getColumn('max_punch_time_stamp') ),
                                            $column => $udt_obj->getColumn('total_time'),
                                            $column.'_hourly_rate' => $hourly_rate,
                                            $column.'_wage' => bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate ),
                                                                                                                        );
                                        }
*/
                                        unset($hourly_rate);
                                }

                                $this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
                        }
                }
                //Debug::Arr($this->tmp_data['user_date_total'], 'User Date Total Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

                //Get user data for joining.
                $ulf = TTnew( 'UserListFactory' );
                $ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
                Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
                $this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
                foreach ( $ulf as $key => $u_obj ) {
                        $this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnConfig() );

                        $this->form_data[$u_obj->getId()] = (array)$u_obj->getObjectAsArray();

                        $this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
                }
                //Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
                //Debug::Arr($this->form_data, 'zUser Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

                //Get verified timesheets for all pay periods considered in report.
                $pay_period_ids = array_keys( $pay_period_ids );
                if ( isset($pay_period_ids) AND count($pay_period_ids) > 0 ) {
                        $pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
                        $pptsvlf->getByPayPeriodIdAndCompanyId( $pay_period_ids, $this->getUserObject()->getCompany() );
                        if ( $pptsvlf->getRecordCount() > 0 ) {
                                foreach( $pptsvlf as $pptsv_obj ) {
                                        $this->tmp_data['verified_timesheet'][$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
                                            'status' => $pptsv_obj->getVerificationStatusShortDisplay(),
                                            'created_date' => $pptsv_obj->getCreatedDate(),
                                                                                                                                                                                                        );
                                }
                        }
                }

                return TRUE;
        }

        //PreProcess data such as calculating additional columns from raw data etc...
        function _preProcess() {
                $this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['user_date_total']), NULL, TTi18n::getText('Pre-Processing Data...') );

                //Merge time data with user data
                $key=0;
                if ( isset($this->tmp_data['user_date_total']) ) {
                        foreach( $this->tmp_data['user_date_total'] as $user_id => $level_1 ) {
                                if ( isset($this->tmp_data['user'][$user_id]) ) {
                                        foreach( $level_1 as $date_stamp => $row ) {
                                                //foreach( $level_2 as $branch => $level_3 ) {
                                                        //foreach( $level_3 as $department => $row ) {
                                                                $date_columns = TTDate::getReportDates( NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
                                                                $processed_data  = array(
                                                                  //'branch' => $branch,
                                                                //'department' => $department,
                                                                  //'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
                                                                  'min_punch_time_stamp' => TTDate::getDate('TIME', $row['min_punch_time_stamp']),
                                                                  'max_punch_time_stamp' => TTDate::getDate('TIME', $row['max_punch_time_stamp'])
                                    );

                                                                if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]) ) {
                                                                        $processed_data['verified_time_sheet'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status'];
                                                                        $processed_data['verified_time_sheet_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['created_date'];
                                                                } else {
                                                                        $processed_data['verified_time_sheet'] = TTi18n::getText('No');
                                                                        $processed_data['verified_time_sheet_date'] = FALSE;
                                                                }

                                                                $this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

                                                                $this->form_data[$user_id]['data'][] = array_merge( $row, $date_columns, $processed_data );

                                                                $this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
                                                                $key++;
                                                        //}
                                                //}
                                        }
                                }
                        }
                        unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
                }
                //Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

                return TRUE;
        }


        function timesheetHeader( $user_data ) {
                $margins = $this->pdf->getMargins();
                $current_company = $this->getUserObject()->getCompanyObject();

                $border = 0;

                $total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

                $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(24) );
                $this->pdf->Cell( $total_width,10, TTi18n::gettext('Employee TimeSheet') , $border, 0, 'C');
                $this->pdf->Ln();
                $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(12) );
                $this->pdf->Cell( $total_width,5, $current_company->getName() , $border, 0, 'C');
                $this->pdf->Ln(5);

                //Generated Date/User top right.
                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(6) );
                $this->pdf->setY( ($this->pdf->getY()-$this->_pdf_fontSize(6)) );
                $this->pdf->setX( $this->pdf->getPageWidth()-$margins['right']-50 );
                $this->pdf->Cell(50, $this->_pdf_fontSize(3), TTi18n::getText('Generated').': '. TTDate::getDate('DATE+TIME', time() ), 0, 0, 'R', 0, '', 1);
                $this->pdf->Ln();
                $this->pdf->setX( $this->pdf->getPageWidth()-$margins['right']-50 );
                $this->pdf->Cell(50, $this->_pdf_fontSize(3), TTi18n::getText('Generated For').': '. $this->getUserObject()->getFullName(), 0, 0, 'R', 0, '', 1);
                $this->pdf->Ln( $this->_pdf_fontSize( 5 ) );

                $this->pdf->Rect( $this->pdf->getX(), $this->pdf->getY()-2, $total_width, 14 );

                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(12) );
                $this->pdf->Cell(30,5, TTi18n::gettext('Employee').':' , $border, 0, 'R');
                $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(12) );
                $this->pdf->Cell(70+(($total_width-200)/2),5, $user_data['first_name'] .' '. $user_data['last_name'] .' (#'. $user_data['employee_number'] .')', $border, 0, 'L');

                $this->pdf->SetFont('','',12);
                $this->pdf->Cell(40,5, TTi18n::gettext('Title').':', $border, 0, 'R');
                $this->pdf->SetFont('','B',12);
                $this->pdf->Cell(60+(($total_width-200)/2),5, $user_data['title'], $border, 0, 'L');
                $this->pdf->Ln();

                $this->pdf->SetFont('','',12);
                $this->pdf->Cell(30,5, TTi18n::gettext('Branch').':' , $border, 0, 'R');
                $this->pdf->Cell(70+(($total_width-200)/2),5, $user_data['default_branch'], $border, 0, 'L');
                $this->pdf->Cell(40,5, TTi18n::gettext('Department').':' , $border, 0, 'R');
                $this->pdf->Cell(60+(($total_width-200)/2),5, $user_data['default_department'], $border, 0, 'L');
                //$this->pdf->Ln();

                //$this->pdf->Cell(30,5, TTi18n::gettext('Group:') , $border, 0, 'R');
                //$this->pdf->Cell(70,5, $user_data['group'], $border, 0, 'L');
                //$this->pdf->Cell(40,5, TTi18n::gettext('Department:') , $border, 0, 'R');
                //$this->pdf->Cell(60,5, $user_data['default_department'], $border, 0, 'L');
                $this->pdf->Ln(5);

                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10) );
                $this->pdf->Ln();

                return TRUE;
        }

        function timesheetPayPeriodHeader( $user_data, $data ) {
                $line_h = 5;

                $margins = $this->pdf->getMargins();
                $total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

                $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(10) );
                $this->pdf->setFillColor(220,220,220);
                if ( isset($data['verified_time_sheet']) AND $data['verified_time_sheet'] != FALSE ) {
                        $this->pdf->Cell( 75, $line_h, TTi18n::gettext('Pay Period').':'. $data['pay_period']['display'], 1, 0, 'L', 1);
                        $this->pdf->Cell( $total_width-75, $line_h, TTi18n::gettext('Electronically signed by') .' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '. TTi18n::gettext('on') .' '. TTDate::getDate('DATE+TIME', $data['verified_time_sheet_date']  ), 1, 0, 'R', 1);
                } else {
                        $this->pdf->Cell( $total_width, $line_h, TTi18n::gettext('Pay Period').':'. $data['pay_period']['display'], 1, 0, 'L', 1);
                }

                $this->pdf->Ln();

                unset($this->timesheet_week_totals);
                $this->timesheet_week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

                return TRUE;
        }

        function timesheetWeekHeader( $column_widths ) {
                $line_h = 10;

                $margins = $this->pdf->getMargins();
                $total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

                $buffer = ($total_width-200)/10;

                $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(10) );
                $this->pdf->setFillColor(220,220,220);
                $this->pdf->MultiCell( $column_widths['line']+$buffer, $line_h, '#' , 1, 'C', 1, 0);
                $this->pdf->MultiCell( $column_widths['date_stamp']+$buffer, $line_h, TTi18n::gettext('Date') , 1, 'C', 1, 0);
                $this->pdf->MultiCell( $column_widths['dow']+$buffer, $line_h, TTi18n::gettext('DoW') , 1, 'C', 1, 0);
                $this->pdf->MultiCell( $column_widths['in_punch_time_stamp']+$buffer, $line_h, TTi18n::gettext('In') , 1, 'C', 1, 0);
                $this->pdf->MultiCell( $column_widths['out_punch_time_stamp']+$buffer, $line_h, TTi18n::gettext('Out') , 1, 'C', 1, 0);
                $this->pdf->MultiCell( $column_widths['worked_time']+$buffer, $line_h, TTi18n::gettext('Worked Time') , 1, 'C', 1, 0);
                $this->pdf->MultiCell( $column_widths['regular_time']+$buffer, $line_h, TTi18n::gettext('Regular Time') , 1, 'C', 1, 0);
                $this->pdf->MultiCell( $column_widths['over_time']+$buffer, $line_h, TTi18n::gettext('Over Time') , 1, 'C', 1, 0);
                $this->pdf->MultiCell( $column_widths['absence_time']+$buffer, $line_h, TTi18n::gettext('Absence Time') , 1, 'C', 1, 0);
                $this->pdf->Ln();

                return TRUE;
        }

        function timesheetDayRow( $format, $column_widths, $user_data, $data, $prev_data, $max_i ) {
                $margins = $this->pdf->getMargins();
                $total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

                $buffer = ($total_width-200)/10;

                //Handle page break.
                $page_break_height = 25;
                if ( $this->counter_i == 1 OR $this->counter_x == 1 ) {
                        if ( $this->counter_i == 1 ) {
                                $page_break_height += 5;
                        }
                        $page_break_height += 5;
                }
                $this->timesheetCheckPageBreak( $page_break_height, TRUE );

                Debug::Text('Pay Period Changed: Current: '.  $data['pay_period_id'] .' Prev: '. $prev_data['pay_period_id'] , __FILE__, __LINE__, __METHOD__,10);
                if ( $prev_data !== FALSE AND $data['pay_period_id'] != $prev_data['pay_period_id'] ) {

                        //Only display week total if we are in the middle of a week when the pay period ends, not at the end of the week.
                        if ( $this->counter_x != 1 ) {
                                $this->timesheetWeekTotal( $column_widths, $this->timesheet_week_totals );
                                $this->counter_x++;
                        }

                        $this->timesheetPayPeriodHeader( $user_data, $data );
                }

                //Show Header
                if ( $this->counter_i == 1 OR $this->counter_x == 1 ) {
                        Debug::Text('aFirst Row: Header', __FILE__, __LINE__, __METHOD__,10);

                        if ( $this->counter_i == 1 ) {
                                $this->timesheetPayPeriodHeader( $user_data, $data );
                        }

                        $this->timesheetWeekHeader( $column_widths );
                }

                if ( $this->counter_x % 2 == 0 ) {
                        $this->pdf->setFillColor(220,220,220);
                } else {
                        $this->pdf->setFillColor(255,255,255);
                }

                if ( $data['time_stamp'] !== '' ) {
                        $default_line_h = 4;
                        $line_h = $default_line_h;

                        $total_rows_arr = array();

                        //Find out how many punches fall on this day, so we can change row height to fit.
                        $total_punch_rows = 1;

                        if ( isset($user_data['punch_rows'][$data['pay_period_id']][$data['time_stamp']]) ) {
                                Debug::Text('Punch Data Row: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);

                                $day_punch_data = $user_data['punch_rows'][$data['pay_period_id']][$data['time_stamp']];
                                $total_punch_rows = count($day_punch_data);
                        } else {
                                Debug::Text('NO Punch Data Row: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);
                        }

                        $total_rows_arr[] = $total_punch_rows;

                        $total_over_time_rows = 1;
                        if ( $data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy']) ) {
                                $total_over_time_rows = count($data['categorized_time']['over_time_policy']);
                        }
                        $total_rows_arr[] = $total_over_time_rows;

                        $total_absence_rows = 1;
                        if ( $data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy']) ) {
                                $total_absence_rows = count($data['categorized_time']['absence_policy']);
                        }
                        $total_rows_arr[] = $total_absence_rows;

                        rsort($total_rows_arr);
                        $max_rows = $total_rows_arr[0];
                        $line_h = ( $format == 'pdf_timesheet_detail' ) ? $default_line_h*$max_rows : $default_line_h;

                        $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9) );
                        $this->pdf->Cell( $column_widths['line']+$buffer, $line_h, $this->counter_x , 1, 0, 'C', 1);
                        $this->pdf->Cell( $column_widths['date_stamp']+$buffer, $line_h, TTDate::getDate('DATE', $data['time_stamp'] ), 1, 0, 'C', 1);
                        $this->pdf->Cell( $column_widths['dow']+$buffer, $line_h, date('D', $data['time_stamp']) , 1, 0, 'C', 1);

                        $pre_punch_x = $this->pdf->getX();
                        $pre_punch_y = $this->pdf->getY();

                        //Print Punches
                        if ( $format == 'pdf_timesheet_detail' AND isset($day_punch_data) ) {
                                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

                                $n=0;
                                foreach( $day_punch_data as $punch_control_id => $punch_data ) {
                                        if ( !isset($punch_data[10]['time_stamp']) ) {
                                                $punch_data[10]['time_stamp'] = NULL;
                                                $punch_data[10]['type_code'] = NULL;
                                        }
                                        if ( !isset($punch_data[20]['time_stamp']) ) {
                                                $punch_data[20]['time_stamp'] = NULL;
                                                $punch_data[20]['type_code'] = NULL;
                                        }

                                        if ( $n > 0 ) {
                                                $this->pdf->setXY( $pre_punch_x, $punch_y+$default_line_h);
                                        }

                                        $this->pdf->Cell( $column_widths['in_punch_time_stamp']+$buffer, $line_h/$total_punch_rows, TTDate::getDate('TIME', $punch_data[10]['time_stamp'] ) .' '. $punch_data[10]['type_code'], 1, 0, 'C', 1);
                                        $this->pdf->Cell( $column_widths['out_punch_time_stamp']+$buffer, $line_h/$total_punch_rows, TTDate::getDate('TIME', $punch_data[20]['time_stamp'] ) .' '. $punch_data[20]['type_code'], 1, 0, 'C', 1);

                                        $punch_x = $this->pdf->getX();
                                        $punch_y = $this->pdf->getY();

                                        $n++;
                                }

                                $this->pdf->setXY( $punch_x, $pre_punch_y);

                                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9) );
                        } else {
                                $this->pdf->Cell( $column_widths['in_punch_time_stamp']+$buffer, $line_h, $data['min_punch_time_stamp'], 1, 0, 'C', 1);
                                $this->pdf->Cell( $column_widths['out_punch_time_stamp']+$buffer, $line_h, $data['max_punch_time_stamp'], 1, 0, 'C', 1);
                        }

                        $this->pdf->Cell( $column_widths['worked_time']+$buffer , $line_h, TTDate::getTimeUnit( $data['worked_time'] ) , 1, 0, 'C', 1);
                        $this->pdf->Cell( $column_widths['regular_time']+$buffer, $line_h, TTDate::getTimeUnit( $data['regular_time'] ), 1, 0, 'C', 1);

                        if ( $data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy']) ) {
                                $pre_over_time_x = $this->pdf->getX();
                                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

                                //Count how many absence policy rows there are.
                                $over_time_policy_total_rows = count($data['categorized_time']['over_time_policy']);
                                foreach( $data['categorized_time']['over_time_policy'] as $policy_id => $value ) {
                                        $this->pdf->Cell( $column_widths['over_time']+$buffer, $line_h/$total_over_time_rows, $otp_columns['over_time_policy-'.$policy_id].': '.TTDate::getTimeUnit( $value ), 1, 0, 'C', 1);
                                        $this->pdf->setXY( $pre_over_time_x, $this->pdf->getY()+($line_h/$total_over_time_rows) );

                                        $over_time_x = $this->pdf->getX();
                                }
                                $this->pdf->setXY( $over_time_x+$column_widths['over_time'], $pre_punch_y);

                                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9) );
                        } else {
                                $this->pdf->Cell( $column_widths['over_time']+$buffer, $line_h, TTDate::getTimeUnit( $data['over_time'] ), 1, 0, 'C', 1);
                        }

                        if ( $data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy']) ) {
                                $pre_absence_time_x = $this->pdf->getX();
                                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

                                //Count how many absence policy rows there are.
                                $absence_policy_total_rows = count($data['categorized_time']['absence_policy']);
                                foreach( $data['categorized_time']['absence_policy'] as $policy_id => $value ) {
                                        $this->pdf->Cell( $column_widths['absence_time']+$buffer, $line_h/$total_absence_rows, $ap_columns['absence_policy-'.$policy_id].': '.TTDate::getTimeUnit( $value ), 1, 0, 'C', 1);
                                        $this->pdf->setXY( $pre_absence_time_x, $this->pdf->getY()+($line_h/$total_absence_rows));
                                }

                                $this->pdf->setY( $this->pdf->getY()-($line_h/$total_absence_rows));

                                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9) );
                        } else {
                                $this->pdf->Cell( $column_widths['absence_time']+$buffer, $line_h, TTDate::getTimeUnit( $data['absence_time'] ), 1, 0, 'C', 1);
                        }

                        $this->pdf->Ln();

                        unset($day_punch_data);
                }

                $this->timesheet_totals['worked_time'] += $data['worked_time'];
                $this->timesheet_totals['absence_time'] += $data['absence_time'];
                $this->timesheet_totals['regular_time'] += $data['regular_time'];
                $this->timesheet_totals['over_time'] += $data['over_time'];

                $this->timesheet_week_totals['worked_time'] += $data['worked_time'];
                $this->timesheet_week_totals['absence_time'] += $data['absence_time'];
                $this->timesheet_week_totals['regular_time'] += $data['regular_time'];
                $this->timesheet_week_totals['over_time'] += $data['over_time'];

                Debug::Text('Row: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);
                if ( $this->counter_x % 7 == 0 OR $this->counter_i == $max_i ) {
                        $this->timesheetWeekTotal( $column_widths, $this->timesheet_week_totals );

                        unset($this->timesheet_week_totals);
                        $this->timesheet_week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
                }

                $this->counter_i++;
                $this->counter_x++;

                return TRUE;
        }

        function timesheetWeekTotal( $column_widths, $week_totals ) {
                Debug::Text('Week Total: Row: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);

                $margins = $this->pdf->getMargins();
                $total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

                $buffer = ($total_width-200)/10;

                //Show Week Total.
                $total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['in_punch_time_stamp']+$column_widths['out_punch_time_stamp']+($buffer*5);
                $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(9) );
                $this->pdf->Cell( $total_cell_width, 6, TTi18n::gettext('Week Total').': ', 0, 0, 'R', 0);
                $this->pdf->Cell( $column_widths['worked_time']+$buffer, 6, TTDate::getTimeUnit( $week_totals['worked_time'] ) , 0, 0, 'C', 0);
                $this->pdf->Cell( $column_widths['regular_time']+$buffer, 6, TTDate::getTimeUnit( $week_totals['regular_time'] ), 0, 0, 'C', 0);
                $this->pdf->Cell( $column_widths['over_time']+$buffer, 6, TTDate::getTimeUnit( $week_totals['over_time'] ), 0, 0, 'C', 0);
                $this->pdf->Cell( $column_widths['absence_time']+$buffer, 6, TTDate::getTimeUnit( $week_totals['absence_time'] ), 0, 0, 'C', 0);
                $this->pdf->Ln(); //1

                $this->counter_x=0; //Reset to 0, as the counter increases to 1 immediately after.
                $this->counter_y++;

                return TRUE;
        }

        function timesheetTotal( $column_widths, $totals ) {
                $margins = $this->pdf->getMargins();
                $total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

                $buffer = ($total_width-200)/10;

                $total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['in_punch_time_stamp']+($buffer*4);
                $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(9) );
                $this->pdf->Cell( $total_cell_width, 6, '' , 0, 0, 'R', 0);
                $this->pdf->Cell( $column_widths['out_punch_time_stamp']+$buffer, 6, TTi18n::gettext('Overall Total').': ', 'T', 0, 'R', 0);
                $this->pdf->Cell( $column_widths['worked_time']+$buffer, 6, TTDate::getTimeUnit( $totals['worked_time'] ) , 'T', 0, 'C', 0);
                $this->pdf->Cell( $column_widths['regular_time']+$buffer, 6, TTDate::getTimeUnit( $totals['regular_time'] ), 'T', 0, 'C', 0);
                $this->pdf->Cell( $column_widths['over_time']+$buffer, 6, TTDate::getTimeUnit( $totals['over_time'] ), 'T', 0, 'C', 0);
                $this->pdf->Cell( $column_widths['absence_time']+$buffer, 6, TTDate::getTimeUnit( $totals['absence_time'] ), 'T', 0, 'C', 0);
                $this->pdf->Ln();

                return TRUE;
        }

        function timesheetSignature( $user_data ) {
                $border = 0;

                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10) );
                $this->pdf->setFillColor(255,255,255);
                $this->pdf->Ln(1);

                $margins = $this->pdf->getMargins();
                $total_width = $this->pdf->getPageWidth()-$margins['left']-$margins['right'];

                $buffer = ($total_width-200)/4;

                //Signature lines
                $this->pdf->MultiCell($total_width,5, TTi18n::gettext('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '.TTi18n::gettext('worked during the designated period.'), $border, 'L');
                $this->pdf->Ln(5); //5

                $this->pdf->Cell(40+$buffer,5, TTi18n::gettext('Employee Signature').':', $border, 0, 'L');
                $this->pdf->Cell(60+$buffer,5, '_____________________________' , $border, 0, 'C');
                $this->pdf->Cell(40+$buffer,5, TTi18n::gettext('Supervisor Signature').':', $border, 0, 'R');
                $this->pdf->Cell(60+$buffer,5, '_____________________________' , $border, 0, 'C');

                $this->pdf->Ln();
                $this->pdf->Cell(40+$buffer,5, '', $border, 0, 'R');
                $this->pdf->Cell(60+$buffer,5, $user_data['first_name'] .' '. $user_data['last_name'] , $border, 0, 'C');

                $this->pdf->Ln();
                $this->pdf->Cell(140+($buffer*3),5, '', $border, 0, 'R');
                $this->pdf->Cell(60+$buffer,5, '_____________________________' , $border, 0, 'C');

                $this->pdf->Ln();
                $this->pdf->Cell(140+($buffer*3),5, '', $border, 0, 'R');
                $this->pdf->Cell(60+$buffer,5, TTi18n::gettext('(print name)'), $border, 0, 'C');

                return TRUE;
        }

        //function timesheetFooter( $pdf_created_date, $adjust_x, $adjust_y ) {
        function timesheetFooter() {
                $margins = $this->pdf->getMargins();

                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );

                //Save x,y and restore after footer is set.
                $x = $this->pdf->getX();
                $y = $this->pdf->getY();

                //Jump to end of page.
                $this->pdf->setY( $this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-10 );

                $this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']), $this->_pdf_fontSize(5), TTi18n::getText('Page').' '. $this->pdf->PageNo() .' of '. $this->pdf->getAliasNbPages(), 0, 0, 'C', 0 );
                $this->pdf->Ln();

                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(6) );
                $this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']), $this->_pdf_fontSize(5), TTi18n::gettext('Report Generated By').' '. APPLICATION_NAME .' v'. APPLICATION_VERSION, 0, 0, 'C', 0 );

                $this->pdf->setX( $x );
                $this->pdf->setY( $y );
                return TRUE;
        }

        function timesheetCheckPageBreak( $height, $add_page = TRUE ) {
                $margins = $this->pdf->getMargins();

                if ( ($this->pdf->getY()+$height) > ($this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-10) ) {
                        //Debug::Text('Detected Page Break needed...', __FILE__, __LINE__, __METHOD__,10);
                        $this->timesheetAddPage();

                        return TRUE;
                }
                return FALSE;
        }

        function timesheetAddPage() {
                $this->timesheetFooter();
                $this->pdf->AddPage();
                return TRUE;
        }


        function _outputPDFTimesheet( $format ) {
                Debug::Text(' Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

                $border = 0;

                $current_company = $this->getUserObject()->getCompanyObject();
                if ( !is_object($current_company) ) {
                        Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__,10);
                        return FALSE;
                }

                $pdf_created_date = time();

                $adjust_x = 10;
                $adjust_y = 10;

                //Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__,10);
                if ( isset($this->form_data) AND count($this->form_data) > 0 ) {

                        $this->pdf = new TTPDF( $this->config['other']['page_orientation'], 'mm', $this->config['other']['page_format'] );

                        $this->pdf->SetCreator( APPLICATION_NAME );
                        $this->pdf->SetAuthor( APPLICATION_NAME );
                        $this->pdf->SetTitle( $this->title );
                        $this->pdf->SetSubject( APPLICATION_NAME .' '. TTi18n::getText('Report') );

                        $this->pdf->setMargins( $this->config['other']['left_margin'], $this->config['other']['top_margin'], $this->config['other']['right_margin'] );
                        //Debug::Arr($this->config['other'], 'Margins: ', __FILE__, __LINE__, __METHOD__,10);

                        $this->pdf->SetAutoPageBreak(FALSE);

                        $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10) );

                        //Debug::Arr($this->form_data, 'zabUser Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

                        $filter_data = $this->getFilterConfig();

                        $plf = TTnew( 'PunchListFactory' );
                        $plf->getSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data);
                        if ( $plf->getRecordCount() > 0 ) {
                                foreach( $plf as $p_obj ) {
                                        $this->form_data[$p_obj->getColumn('user_id')]['punch_rows'][$p_obj->getColumn('pay_period_id')][TTDate::strtotime( $p_obj->getColumn('date_stamp'))][$p_obj->getPunchControlID()][$p_obj->getStatus()] = array( 'status_id' => $p_obj->getStatus(), 'type_id' => $p_obj->getType(), 'type_code' => $p_obj->getTypeCode(), 'time_stamp' => $p_obj->getTimeStamp() );
                                }
                        }
                        unset($plf,$p_obj);

                        foreach( $this->form_data as $user_data ) {
                                $this->pdf->AddPage( $this->config['other']['page_orientation'], 'Letter' );

                                $this->timesheetHeader( $user_data );

                                //Start displaying dates/times here. Start with header.
                                $column_widths = array(
                                                                        'line' => 5,
                                                                        'date_stamp' => 20,
                                                                        'dow' => 10,
                                                                        'in_punch_time_stamp' => 20,
                                                                        'out_punch_time_stamp' => 20,
                                                                        'worked_time' => 20,
                                                                        'regular_time' => 20,
                                                                        'over_time' => 40.6,
                                                                        'absence_time' => 45,
                                                                        );


                                if ( isset($user_data['data']) AND is_array($user_data['data']) ) {
                                        $user_data['data'] = Sort::arrayMultiSort( $user_data['data'],  array( 'time_stamp' => SORT_ASC ) );

                                        $this->timesheet_week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
                                        $this->timesheet_totals = array();
                                        $this->timesheet_totals = Misc::preSetArrayValues( $this->timesheet_totals, array( 'worked_time','absence_time', 'regular_time', 'over_time' ), 0 );

                                        $this->counter_i=1; //Overall row counter.
                                        $this->counter_x=1; //Row counter, starts over each week.
                                        $this->counter_y=1; //Week counter.
                                        $max_i = count($user_data['data']);
                                        $prev_data = FALSE;
                                        foreach( $user_data['data'] as $data ) {
                                                //Debug::Arr($data, 'Data: i: '. $this->counter_i .' x: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);
                                                $data = Misc::preSetArrayValues( $data, array('time_stamp', 'in_punch_time_stamp', 'out_punch_time_stamp', 'worked_time', 'absence_time', 'regular_time', 'over_time' ), '--' );

                                                $row_date_gap = ($prev_data !== FALSE ) ? (TTDate::getMiddleDayEpoch($data['time_stamp'])-TTDate::getMiddleDayEpoch($prev_data['time_stamp'])) : 0; //Take into account DST by using mid-day epochs.
                                                Debug::Text('Row Gap: '. $row_date_gap, __FILE__, __LINE__, __METHOD__,10);
                                                if ( $prev_data !== FALSE AND $row_date_gap > (86400) ) {
                                                        Debug::Text('FOUND GAP IN DAYS!', __FILE__, __LINE__, __METHOD__,10);

                                                        for( $d=TTDate::getBeginDayEpoch($prev_data['time_stamp'])+86400; $d < $data['time_stamp']; $d+=86400) {
                                                                $blank_row_time_stamp = TTDate::getBeginDayEpoch($d);
                                                                Debug::Text('Blank row timestamp: '. TTDate::getDate('DATE+TIME', $blank_row_time_stamp ) .' Pay Period Start Date: '. TTDate::getDate('DATE+TIME', $prev_data['pay_period_start_date'] ), __FILE__, __LINE__, __METHOD__,10);
                                                                if ( $blank_row_time_stamp >= $prev_data['pay_period_end_date'] ) {
                                                                        Debug::Text('aBlank row timestamp: '. TTDate::getDate('DATE+TIME', $blank_row_time_stamp ) .' Pay Period Start Date: '. TTDate::getDate('DATE+TIME', $prev_data['pay_period_start_date'] ), __FILE__, __LINE__, __METHOD__,10);
                                                                        $pay_period_id = $data['pay_period_id'];
                                                                        $pay_period_start_date = $data['pay_period_start_date'];
                                                                        $pay_period_end_date = $data['pay_period_end_date'];
                                                                        $pay_period = $data['pay_period'];
                                                                } else {
                                                                        Debug::Text('bBlank row timestamp: '. TTDate::getDate('DATE+TIME', $blank_row_time_stamp ) .' Pay Period Start Date: '. TTDate::getDate('DATE+TIME', $prev_data['pay_period_start_date'] ), __FILE__, __LINE__, __METHOD__,10);
                                                                        $pay_period_id = $prev_data['pay_period_id'];
                                                                        $pay_period_start_date = $prev_data['pay_period_start_date'];
                                                                        $pay_period_end_date = $prev_data['pay_period_end_date'];
                                                                        $pay_period = $prev_data['pay_period'];
                                                                }

                                                                $blank_row_data = array(
                                                                                 'pay_period_id' => $pay_period_id,
                                                                                 'pay_period_start_date' => $pay_period_start_date,
                                                                                 'pay_period_end_date' => $pay_period_end_date,
                                                                                  'pay_period' => $pay_period,
                                                                                  'time_stamp' => $blank_row_time_stamp,
                                                                                  'min_punch_time_stamp' => NULL,
                                                                                  'max_punch_time_stamp' => NULL,
                                                                                  'in_punch_time' => NULL,
                                                                                   'out_punch_time' => NULL,
                                                                                        'worked_time' => NULL,
                                                                                        'regular_time' => NULL,
                                                                                        'over_time' => NULL,
                                                                                        'absence_time' => NULL
                                                                                                                );

                                                                $max_i++;
                                                                $this->timesheetDayRow( $format, $column_widths, $user_data, $blank_row_data, $prev_data, $max_i ); //Prev data is actually the current data for a blank row.

                                                                unset( $blank_row_time_stamp, $pay_period_id, $pay_period_start_date, $pay_period_end_date, $pay_period);
                                                        }
                                                        $prev_data = $blank_row_data; //Make sure we set the last blank_row as the prev_data going forward.
                                                        unset($blank_row_data);
                                                }
                                                $this->timesheetDayRow( $format, $column_widths, $user_data, $data, $prev_data, $max_i );

                                                $prev_data = $data;
                                        }

                                        if ( isset($this->timesheet_totals) AND is_array($this->timesheet_totals) ) {
                                                //Display overall totals.
                                                $this->timesheetTotal( $column_widths, $this->timesheet_totals );
                                                unset($totals);
                                        }

                                        unset($data);
                                }

                                $this->timesheetSignature( $user_data );

                                $this->timesheetFooter( $pdf_created_date, $adjust_x, $adjust_y );
                        }

                        $output = $this->pdf->Output('','S');

                        return $output;

                }

                Debug::Text('No data to return...', __FILE__, __LINE__, __METHOD__,10);
                return FALSE;
        }

        function _output( $format = NULL ) {
                if ( $format == 'pdf_timesheet' OR $format == 'pdf_timesheet_print'
                                OR $format == 'pdf_timesheet_detail' OR $format == 'pdf_timesheet_detail_print' ) {
                        return $this->_outputPDFTimesheet( $format );
                } else {
                        return parent::_output( $format );
                }
        }

            //FL ADDED FOR LATE DETAIL REPORT (National PVC) 20160517
        function OTDetailReport($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {  
            $filter_header_data = array(
                                        'group_ids' => $filter_data['group_ids'],
                                        'branch_ids' => $filter_data['branch_ids'],
                                        'department_ids' => $filter_data['department_ids'],
                                        'pay_period_ids' => $filter_data['pay_period_ids']
                                    );
                                                                                
                foreach($filter_header_data as $fh_key=>$filter_header){
                    $dlf = TTnew( 'DepartmentListFactory' );
                    if($fh_key == 'department_ids'){
                        foreach ($filter_header as $dep_id) { 
                            $department_list[] = $dlf->getNameById($dep_id); 
                        }
                        $dep_strng = implode(', ', $department_list);
                    }
                                
                    $blf = TTnew( 'BranchListFactory' ); 
                    if($fh_key == 'branch_ids'){
                        foreach ($filter_header as $br_id) { 
                            $branch_list[] = $blf->getNameById($br_id); 
                        }
                        $br_strng = implode(', ', $branch_list);
                    }
                                                                                
                    $uglf = TTnew( 'UserGroupListFactory' ); 
                    if($fh_key == 'group_ids'){
                        foreach ($filter_header as $gr_id) {   
                            $group_list[] = $uglf->getNameById($gr_id); 
                        }
                        $gr_strng = implode(', ', $group_list);
                    }
                                
                }  
                
                $pplf = TTnew( 'PayPeriodListFactory' );
                if(isset($filter_data['pay_period_ids'][0])){                                                              
                    $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                    $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                }else{
                    $pay_period_start = $filter_data['start_date'];
                    $pay_period_end = $filter_data['end_date'];
                
                }
                
                $dates = array();
                $current = $pay_period_start;
                $last = $pay_period_end;

                while( $current <= $last ) {

                    $dates[] = date('d', $current); 
                    $current = strtotime('+1 day', $current);
                }
                
//            echo '<pre>'; print_r($data); echo '<pre>'; die;
            
            
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            
                                                                                
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $rows = $data;
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Daily / Monthly OT Report',
                                                  'group_list'  => $gr_strng, 
                                                  'department_list'  => $dep_strng, 
                                                  'branch_list'  => $br_strng, 
                                                  'payperiod_end_date'   => date('Y-M',$pay_period_end),
                                                  'line_width'  => 280, 
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('l','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
//                $html = $html.'<td width = "3%">#</td>';
                $html = $html.'<td width = "12%"> ('.date('Y/M/d',$pay_period_start).' to '.date('Y/M/d',$pay_period_end).') </td>';
                
                $pdf->SetFont('', 'B',5);    
                
                                                                                
                foreach( $dates as $column_name )
                {                    
                    $html = $html.'<td width = "3.1%" style ="text-align:center:justify;font-weight:bold;" >'.$column_name.'</td>';                      
                }
                $html=  $html.'</tr>';
                
                $pdf->SetFont('','',6);  
                                                                                
                $x=1;   
                $dayArray=0;
                foreach ($rows as $row){

                    //create Array by date day
                $row_data_day_key = array();
                $tot_ot_hours=$tot_ot_hours_in_sec=0;
                foreach($row['data'] as $row1){
                    if($row1['date_stamp'] != ''){
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d',  strtotime($row_dt)); 
                        $row_data_day_key[$dat_day] = $row1; 
                        
                        //get total time calculation
                        
                        if(isset($row1['over_time'])){
                            $ot_hm = explode(':', $row1['over_time']);
//                            var_dump($ot_hm); die;
                            $ot_in_sec =  $ot_hm[0]*60*60 + $ot_hm[1]*60;
                            $tot_ot_hours_in_sec = $tot_ot_hours_in_sec + $ot_in_sec;
                        }
//                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                } else{ 
                                        $tot_ot_hours_data = $row1['over_time'];

                }
                }
                $tot_ot_hours = gmdate("H:i", $tot_ot_hours_in_sec);
                    
//                    echo '<pre>'; print_r($row); echo '<pre>'; die;
                    if($x % 2 == 0) {
                        $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                    }
                    else{
                        $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                    }   
                    $html = $html.'<td>
                                    <table>
                                        <tr style ="text-align:left">
                                            <td width="35%">'.$row['employee_number'].'</td>
                                            <td width="65%">'.$row['first_name'].' '.$row['last_name'].'</td>
                                        </tr>
                                        <tr style ="text-align:left ">
                                            <td>Total OT</td>
                                            <td>'.$tot_ot_hours_data.'</td>
                                        </tr>
                                    </table>
                                    </td>'; 
                    
                    
                  
                    
//            echo '<pre>'; print_r($row_data_day_key); echo '<pre>'; die;
                    foreach( $dates as $date ){                    
                        
                        if(isset($row_data_day_key[$date]['over_time'])){
                            $html = $html.'<td style ="text-align:center:justify;" ><table><tr><td></td></tr> <tr><td>'.$row_data_day_key[$date]['over_time'].'</td></tr></table></td>';                      
                        }else{
                            $html = $html.'<td style ="text-align:center:justify;font-weight:bold;" ><table><tr><td></td></tr> <tr><td>--</td></tr></table></td>';                      
                        }
                    }
                    
                    $x++;
                    
                    $html=  $html.'</tr>';      
                }
                
//                foreach( $data as $rows ) 
//                {                    
//                        if($x % 2 == 0)
//                        {
//                            $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
//                        }
//                        else
//                        {
//                            $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
//                        }                    
//                    
//                    $html = $html.'<td>'.$x++.'</td>';
//                    
//                    foreach ($columns as $column_key => $column_name ) 
//                    {
//                        if ( isset($rows[$column_key])  && $rows[$column_key] != "")
//                        {
//                            $html = $html.'<td>'.$rows[$column_key].'</td>'; 
//                        }
//                        
//                        else
//                        {
//                            $html = $html.'<td>'.'--'.'</td>';
//                        }
//                    }
//                    $html =  $html.'</tr>';          
//                }
//                              
//                              
//                              //SUM ROW
//                $html=  $html.'<tr style ="background-color:#CCCCCC;text-align:justify;" >';
//                $html = $html.'<td width = "3%"></td>';       
//                                                      
//                foreach($columns as $column_key1=>$column_value)
//                {
//                    $checked=0;
//                    foreach( $last_row as $key=>$value)
//                    {
//                        if($key == $column_key1 && isset($value) != "")
//                        {
//                            $html = $html.'<td style ="center;text-align:center:justify;font-weight:bold;" >'.$value.'</td>'; 
//                            $checked=1;
//                        }
//                    }
//                    
//                    if($checked != 1)
//                    {
//                        $html = $html.'<td style ="text-align:center:justify;font-weight:bold;">--</td>';
//                    }                        
//                }
//                $html=  $html.'</tr>';                                        
                                
                                
                                
                                                        
                $html=  $html.'</table>';        
      
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
                                $output = $pdf->Output('','S');
                        
                //exit;  
                                
                                if ( isset($output) )
                                {
                                        return $output;                         
                                }
                                
                                return FALSE;              
                                
            }

        }


        function DailyAbsenceReport($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {  
            
            $filter_header_data = array(
                                        'group_ids' => $filter_data['group_ids'],
                                        'branch_ids' => $filter_data['branch_ids'],
                                        'department_ids' => $filter_data['department_ids'],
                                        'pay_period_ids' => $filter_data['pay_period_ids']
                                    );
                                                                                
                foreach($filter_header_data as $fh_key=>$filter_header){
                    $dlf = TTnew( 'DepartmentListFactory' );
                    if($fh_key == 'department_ids'){
                        foreach ($filter_header as $dep_id) { 
                            $department_list[] = $dlf->getNameById($dep_id); 
                        }
                        $dep_strng = implode(', ', $department_list);
                    }
                                
                    $blf = TTnew( 'BranchListFactory' ); 
                    if($fh_key == 'branch_ids'){
                        foreach ($filter_header as $br_id) { 
                            $branch_list[] = $blf->getNameById($br_id); 
                        }
                        $br_strng = implode(', ', $branch_list);
                    }
                                                                                
                    $uglf = TTnew( 'UserGroupListFactory' ); 
                    if($fh_key == 'group_ids'){
                        foreach ($filter_header as $gr_id) {   
                            $group_list[] = $uglf->getNameById($gr_id); 
                        }
                        $gr_strng = implode(', ', $group_list);
                    }
                                
                }  
                //echo '<pre>'; print_r($data); echo '<pre>';  die;
                $pplf = TTnew( 'PayPeriodListFactory' );
                if(isset($filter_data['pay_period_ids'][0])){                                                              
                    $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                    $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                }else{
                    $pay_period_start = $filter_data['start_date'];
                    $pay_period_end = $filter_data['end_date'];
                }
                
                $dates = array();
                $current = $pay_period_start;
                $last = $pay_period_end;

                while( $current <= $last ) {
                    $dates[] = date('d', $current); 
                    $current = strtotime('+1 day', $current);
                }
                                                                              
            
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
                                               
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $rows = $data;
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Daily Absence Report', 
                                                  'group_list'  => $gr_strng, 
                                                  'department_list'  => $dep_strng, 
                                                  'branch_list'  => $br_strng, 
                                                  'payperiod_end_date'   => date('Y-M-d',$pay_period_start),
                                                  'line_width'  => 185, 
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('p','mm','A4');

             
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                //Header
                // create some HTML content
                $html = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
                $html = $html.'<td height="17" width = "5%">#</td>';
                $html = $html.'<td width = "10%">Emp. No.</td>';
                $html = $html.'<td width = "45%">Emp. Name</td>';
                $html = $html.'<td width = "25%">Reasoan</td>';
                $html=  $html.'</tr></thead>';
     
                $pdf->SetFont('','',8);  
                                                                                
                $x=1; $nof_emp = 0;  
                
                $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m',$pay_period_start), date('Y',$pay_period_start));

                $html=  $html.'<tbody>';
                foreach ($rows as $row){

                    $udlf = TTnew('UserDateListFactory');
                    $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d', $pay_period_start));
                    $udlf_obj = $udlf->getCurrent();
                    $user_date_id = $udlf_obj->getId();

                    $slf = TTnew('ScheduleListFactory');
                    $slf->getByUserDateId($user_date_id);
                    $slf_obj_arr = $slf->getCurrent()->data;

                    if(!empty($slf_obj_arr))
                    {
                        $pclf = TTnew('PunchControlListFactory');
                        $pclf->getByUserDateId($user_date_id); //par - user_date_id
                        $pc_obj_arr = $pclf->getCurrent()->data;

                        if(empty($pc_obj_arr))
                        {

                             $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                             $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                             $absLeave_obj_arr = $aluelf->getCurrent()->data;


                             if(!empty($absLeave_obj_arr)){
                                 $leaveName = $absLeave_obj_arr['absence_name'];

                             }
                             else{
                                $leaveName = 'Unscheduled Absence.';
                             }

                             if($x % 2 == 0) {
                                $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                            }
                            else{
                                $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                            } 

                            $html = $html.'<td  width = "5%" height="25">'.$x.'</td>'; 
                            $html = $html.'<td width = "10%">'.$row['employee_number'].'</td>'; 
                            $html = $html.'<td width = "45%" align="left">'.$row['first_name'].' '.$row['last_name'].'</td>'; 
                            $html = $html.'<td width = "25%" align="left">'.$leaveName.'</td>';
                            $html=  $html.'</tr>';

                            $nof_emp++; $x++;
                        }                     

                    }

                    
                    
                }//die;
                 
                $html=  $html.'</tbody>';                                       
                $html=  $html.'</table>';        
                
                $html = $html.  '
                                <table width="943" border="0">
                                    <tr><td align="center"></td></tr>
                                    <tr><td align="Left">Total No of Employees : '.$nof_emp.'</td></tr>
                                    <tr><td align="Left">Date : '.date('Y-M-d',$pay_period_start).'</td></tr>
                                    <tr><td align="Left"></td></tr>
                                </table>'; 
                
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
                $output = $pdf->Output('','S');
        
                //exit;  
                
                if ( isset($output) )
                {
                    return $output;                         
                }
                
                return FALSE;              
                                
            }

        }



        
             //FL ADDED FOR Daily Late (National PVC) 20160517
        function DailyLateReport($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {  
            $filter_header_data = array(
                                        'group_ids' => $filter_data['group_ids'],
                                        'branch_ids' => $filter_data['branch_ids'],
                                        'department_ids' => $filter_data['department_ids'],
                                        'pay_period_ids' => $filter_data['pay_period_ids']
                                    );
                                                                                
                foreach($filter_header_data as $fh_key=>$filter_header){
                    $dlf = TTnew( 'DepartmentListFactory' );
                    if($fh_key == 'department_ids'){
                        foreach ($filter_header as $dep_id) { 
                            $department_list[] = $dlf->getNameById($dep_id); 
                        }
                        $dep_strng = implode(', ', $department_list);
                    }
                                
                    $blf = TTnew( 'BranchListFactory' ); 
                    if($fh_key == 'branch_ids'){
                        foreach ($filter_header as $br_id) { 
                            $branch_list[] = $blf->getNameById($br_id); 
                        }
                        $br_strng = implode(', ', $branch_list);
                    }
                                                                                
                    $uglf = TTnew( 'UserGroupListFactory' ); 
                    if($fh_key == 'group_ids'){
                        foreach ($filter_header as $gr_id) {   
                            $group_list[] = $uglf->getNameById($gr_id); 
                        }
                        $gr_strng = implode(', ', $group_list);
                    }
                                
                }  
//                echo '<pre>'; print_r($data); echo '<pre>';  die;
                $pplf = TTnew( 'PayPeriodListFactory' );
                if(isset($filter_data['pay_period_ids'][0])){                                                              
                    $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                    $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                }else{
                    $pay_period_start = $filter_data['start_date'];
                    $pay_period_end = $filter_data['end_date'];
                }
                
                $dates = array();
                $current = $pay_period_start;
                $last = $pay_period_end;

                while( $current <= $last ) {
                    $dates[] = date('d', $current); 
                    $current = strtotime('+1 day', $current);
                }
                                                                                
            
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
                                               
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $rows = $data;
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Daily Attendance / Late Report', 
                                                  'group_list'  => $gr_strng, 
                                                  'department_list'  => $dep_strng, 
                                                  'branch_list'  => $br_strng, 
                                                  'payperiod_end_date'   => date('Y-M-d',$pay_period_start),
                                                  'line_width'  => 280, 
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('l','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.30 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
//                $html = $html.'<td width = "3%">#</td>';
                $html = $html.'<td height="17" width = "5%">#</td>';
                $html = $html.'<td width = "10%">Emp. No.</td>';
                $html = $html.'<td align="left" width = "25%">Emp. Name</td>';
                $html = $html.'<td width = "5%">Shift</td>';
                $html = $html.'<td width = "5%">Shift In</td>';
                $html = $html.'<td width = "5%">Break In</td>';
                $html = $html.'<td width = "5%">Break Out</td>';
                $html = $html.'<td width = "5%">Shift Out</td>';
                $html = $html.'<td width = "5%">Hours Worked</td>';
                $html = $html.'<td width = "5%">Late</td>';
                $html = $html.'<td width = "10%">Early</td>';
                $html = $html.'<td width = "5%">OT</td>';
                $html = $html.'<td width = "5%">Loss</td>';
                $html = $html.'<td width = "5%">Status</td>';
                $html = $html.'<td width = "5%">Covered</td>'; 
                
                $pdf->SetFont('', 'B',7);    
                
                                                                                
                $html=  $html.'</tr>';
                
                $pdf->SetFont('','',7);  
                                                                                
                $x=1;   
                $dayArray=0;
                $tot_work_hours = $tot_work_hours_in_sec = 0;


                //echo '<pre>';print_r($rows);

                foreach ($rows as $row){
                    
                    $EmpDateStatus = $this->getReportStatusByUserIdAndDate($row['user_id'],date('Y-m-d', $pay_period_start));
//                    echo'<pre>'; print_r($EmpDateStatus); die;  
                    
                    $udlf = TTnew('UserDateListFactory');
            
                    $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d', $pay_period_start));
                    $udlf_obj = $udlf->getCurrent();
                    $user_date_id = $udlf_obj->getId();

                    $elf = TTnew('ExceptionListFactory');
                    $elf->getByUserDateId($user_date_id);
                    foreach ($elf as $elf_obj){
                        if($elf_obj->getExceptionPolicyId() == '5'){
                            $status2 = 'ED'; //Early Departure
                            $all_status['status2_all'] .= ' ED';
                        }
                        if($elf_obj->getExceptionPolicyId() == '4'){
                            $status2 = 'LP'; //Late Presents
                            $all_status['status2_all'] .= ' LP';
                        }
                        if($elf_obj->getExceptionPolicyId() == '12' || $elf_obj->getExceptionPolicyId() == '13'){
                            $status2 = 'MIS'; //Missed Punch
                            $all_status['status2_all'] .= ' MIS';
                        }
                        if($elf_obj->getExceptionPolicyId() == '1'){
                            $status2 = 'A'; //Unscheduled absent
                            $all_status['status2_all'] .= ' A';
                        }
                    }
                    
                    $udtlf = TTnew('UserDateTotalListFactory');
                    
                    
                    //create Array by date day
                    $row_data_day_key = array();
                    $tot_ot_hours=$tot_ot_hours_in_sec=0;
                    foreach($row['data'] as $row1){
                     $ot_in_sec = $work_in_sec = 0;
                     if($row1['date_stamp'] != ''){
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d',  strtotime($row_dt)); 
                        $row_data_day_key[$dat_day] = $row1; 
                          
//            echo '<pre>'; print_r($row1); echo '<pre>'; die;
                        //get total time calculation 
                        if(isset($row1['over_time']) && $row1['over_time']!=''){
                            $ot_hm = explode(':', $row1['over_time']); 
                            $ot_in_sec =  $ot_hm[0]*60*60 + $ot_hm[1]*60;
                            $tot_ot_hours_in_sec = $tot_ot_hours_in_sec + $ot_in_sec;
                        } 

                        if(isset($row1['worked_time']) && $row1['worked_time']!=''){
                            
                            $work_hm = explode(':', $row1['worked_time']); 
                            $work_in_sec =  $work_hm[0]*60*60 + $work_hm[1]*60;
                            $tot_work_hours_in_sec = $tot_work_hours_in_sec + $work_in_sec;
                        }                      
                    }
                }
//                $tot_ot_hours = gmdate("H:i", $tot_ot_hours_in_sec);
                $tot_ot_hours = floor($tot_ot_hours_in_sec / 3600).':'.floor($tot_ot_hours_in_sec / 60 % 60);
                
//                $tot_work_hours = gmdate("H:i", $tot_work_hours_in_sec);
                $tot_work_hours = floor($tot_work_hours_in_sec / 3600).':'.floor($tot_work_hours_in_sec / 60 % 60);
                    
                    if($x % 2 == 0) {
                        $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                    }
                    else{
                        $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                    }   
                                                                              
                                                                                
                    foreach( $dates as $date ){  
                        $lossSec =0;
//                        echo '<pre>'; print_r($row_data_day_key); echo '<pre>'; die;
                        //STATUS COULUMN VALUE SETUP
                        $status_str = ''; 
                        $status_str .= ($row_data_day_key[$date]['min_punch_time_stamp']=='' && $row_data_day_key[$date]['max_punch_time_stamp']=='')?'A ':'P ';
                         
                        
                        $late = '';
                        if($row_data_day_key[$date]['min_punch_time_stamp'] != '' && $row_data_day_key[$date]['shedule_start_time']!=''){
                            $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - strtotime($row_data_day_key[$date]['min_punch_time_stamp']);
                            if($lateSec < 0 ){
                                $lossSec = $lossSec + abs($lateSec);
                                $late = gmdate("H:i", abs($lateSec));
                                 $status_str .= 'LP ';
                            }
                        } 
                         $early = '';
                        if($row_data_day_key[$date]['max_punch_time_stamp'] != '' && $row_data_day_key[$date]['shedule_end_time']!=''){
                            $earlySec = strtotime($row_data_day_key[$date]['shedule_end_time']) - strtotime($row_data_day_key[$date]['max_punch_time_stamp']);
                          
                            if($earlySec > 0 ){
                                $lossSec = $lossSec +  abs($earlySec);
                                $early = gmdate("H:i", abs($earlySec));
                                 $status_str .= 'ED ';
                            }
                        }  
                        $loss =  gmdate("H:i", abs($lossSec));
                        
                        $status_str .= (($row_data_day_key[$date]['min_punch_time_stamp'] == "" || $row_data_day_key[$date]['max_punch_time_stamp'] == "") && !($row_data_day_key[$date]['min_punch_time_stamp'] == "" && $row_data_day_key[$date]['max_punch_time_stamp'] == ""))?'MIS ':'';
                        
                        //Columns Setup
                        $shift_column = 'O';
                        $shift_in = ($row_data_day_key[$date]['min_punch_time_stamp'] == '')?'':$row_data_day_key[$date]['min_punch_time_stamp'];
                        $shift_out = ($row_data_day_key[$date]['max_punch_time_stamp'] == '')?'':$row_data_day_key[$date]['max_punch_time_stamp'];
                        $break_in = '';
                        $break_out = '';
                        $covered = '';
                                
                        $html = $html.'<td height="25">'.$x.'</td>'; 
                        $html = $html.'<td>'.$row['employee_number'].'</td>'; 
                        $html = $html.'<td align="left">'.$row['first_name'].' '.$row['last_name'].'</td>'; 
                        $html = $html.'<td>'.$shift_column.'</td>'; 
                        $html = $html.'<td>'.$shift_in.'</td>'; 
                        $html = $html.'<td>'.$break_in.'</td>'; 
                        $html = $html.'<td>'.$break_out.'</td>'; 
                        $html = $html.'<td>'.$shift_out.'</td>'; 
                        $html = $html.'<td>'.$row_data_day_key[$date]['worked_time'].'</td>'; 
                        $html = $html.'<td>'.$late.'</td>'; 
                        $html = $html.'<td>'.$early.'</td>'; 
                        $html = $html.'<td>'.$tot_ot_hours.'</td>'; 
                        $html = $html.'<td>'.$loss.'</td>'; 
                        $html = $html.'<td>'.$EmpDateStatus['status1'].' '.$EmpDateStatus['status2_all'].'</td>'; 
                        $html = $html.'<td>'.$covered.'</td>'; 
                                                                                
                    }
                    
                    $x++;
                    
                    $html=  $html.'</tr>';              
                }
              
                    $html=  $html.'<tr>'
                                . '<td colspan="8"><b>Total</b></td>'
                                . '<td align="center" ><b>'.$tot_work_hours.'</b></td>'
                                . '<td colspan="2" align="center" ><b></b></td>'
                                . '<td align="center" ><b></b></td>'
                                . '</tr>';
                                                        
                $html=  $html.'</table>';        
                
                $html = $html.  '
                                <table width="943" border="0">
                                    <tr><td align="center"></td></tr>
                                    <tr><td align="Left">Total No of Employees : '.count($rows).'</td></tr>
                                    <tr><td align="Left">Date : '.date('Y-M-d',$pay_period_start).'</td></tr>
                                    <tr><td align="Left"></td></tr>
                                </table>'; 
                $html = $html.  '
                                <table width="943" border="1">
                                    <tr>
                                        <td align="center">P- Present / A - Absenteism / LP - Late Presents / MIS - Miss Punch / POH - Present On Holiday / HLD - Holiday / WO - Week Off / SL - Short Leave </td>
                                    </tr>
                                </table>';  
                                //die;
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
                                $output = $pdf->Output('','S');
                        
                //exit;  
                
                if ( isset($output) )
                {
                        return $output;                         
                }
                
                return FALSE;              
                                
            }

        }

        //---Monthly Leave Taken
        function MonthlyLeaveTakenReport($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {  
                $filter_header_data = array(
                                        'group_ids' => $filter_data['group_ids'],
                                        'branch_ids' => $filter_data['branch_ids'],
                                        'department_ids' => $filter_data['department_ids'],
                                        'pay_period_ids' => $filter_data['pay_period_ids']
                                    );
                                                                                
                foreach($filter_header_data as $fh_key=>$filter_header){
                    $dlf = TTnew( 'DepartmentListFactory' );
                    if($fh_key == 'department_ids'){
                        foreach ($filter_header as $dep_id) { 
                            $department_list[] = $dlf->getNameById($dep_id); 
                        }
                        $dep_strng = implode(', ', $department_list);
                    }
                                
                    $blf = TTnew( 'BranchListFactory' ); 
                    if($fh_key == 'branch_ids'){
                        foreach ($filter_header as $br_id) { 
                            $branch_list[] = $blf->getNameById($br_id); 
                        }
                        $br_strng = implode(', ', $branch_list);
                    }
                                                                                
                    $uglf = TTnew( 'UserGroupListFactory' ); 
                    if($fh_key == 'group_ids'){
                        foreach ($filter_header as $gr_id) {   
                            $group_list[] = $uglf->getNameById($gr_id); 
                        }
                        $gr_strng = implode(', ', $group_list);
                    }
                                
                }  
                $pplf = TTnew( 'PayPeriodListFactory' );
                if(isset($filter_data['pay_period_ids'][0])){                                                              
                    $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                    $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                }else{
                    $pay_period_start = $filter_data['start_date'];
                    $pay_period_end = $filter_data['end_date'];
                
                } 

                
                $date_month = date('m-Y',$pay_period_start); 
                $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m',$pay_period_start), date('Y',$pay_period_start));
                $start_date_year = date('Y',$pay_period_start); 
                //echo '<br><pre>';
                $dates = array();
                $current = $pay_period_start;
                $last = $pay_period_end;

                while( $current <= $last ) {

                    $dates[] = date('d', $current); 
                    $current = strtotime('+1 day', $current);
                }
            
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            
                                                                                
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $new_rows = $data;
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                $_SESSION['header_data'] = array( 
                                                  'payperiod_end_date'   => date('Y-M',$pay_period_end),
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Monthly Leave Taken Report', 
                                                  'group_list'  => $gr_strng, 
                                                  'department_list'  => $dep_strng, 
                                                  'branch_list'  => $br_strng, 
                                                  'line_width'  => 280, 
                                                  'footer_FDHDSL' => 'FDHDSL',
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('l','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '';
                
                $html = $html.'</tr>'; 
                
                $pdf->SetFont('', 'B',6.5); 
                
                
                $row_data_day_key = array(); 
                $j1 = 0;

                $html = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
                    $html = $html.'<td height="17" width = "5%">#</td>';
                    $html = $html.'<td width = "10%">Emp. No.</td>';
                    $html = $html.'<td width = "25%">Emp. Name</td>';
                    $html = $html.'<td width = "20%">Annual Leave</td>';
                    $html = $html.'<td width = "20%">Casual Leave</td>';
                    $html = $html.'<td width = "20%">Sick Leave</td>';
                    $html=  $html.'</tr></thead>';

                    $html=  $html.'<tbody>';

                    $pdf->SetFont('', '',8);

                $x = 1;
                foreach ($new_rows as $data_vl){

                    //Get all accrual policies.
                    $ulf = TTnew( 'UserListFactory' );
                    $aplf = TTnew( 'AbsencePolicyListFactory' );
                    $aplf->getByCompanyId($current_company->getId());
                    if ( $aplf->getRecordCount() > 0 ) {
                        foreach ($aplf as $ap_obj ) {
                            $ap_columns['absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
                        }

                        $columns = array_merge( $columns, $ap_columns);
                    }


                        $ablf = TTnew( 'AccrualBalanceListFactory' );
                        $ablf->getByUserIdAndCompanyId( $data_vl['user_id'], $current_company->getId() );

                        $total_balance_leave_all = array('full_day'=>0, 'half_day'=>0, 'short_leave'=>0);

                        foreach ($columns as $column_abs => $column_abs_vl){ 
                        //foreach ($filter_data['column_ids'] as $column_abs ){
                            
                            $$absence_policy_id = ''; 
                            $absence_policy_id_array = array('1', '2', '3'); //Annual/casual/Sick leave IDs
                            $colAbs_arr = explode('-', $column_abs);
                                 
                            //&& in_array($colAbs_arr[1], $absence_policy_id_array)
                           if($colAbs_arr[0] == 'absence_policy' && in_array($colAbs_arr[1], $absence_policy_id_array) ){
                                $absence_policy_id = $colAbs_arr[1];

                                $udlf = TTnew('UserDateListFactory');
                                $total_used_leaves = 0;
                                for($i1=1; $i1<=$nof_days_for_month; $i1++){
                                    
                                    $udlf->getByUserIdAndDate($data_vl['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                                    $udlf_obj = $udlf->getCurrent();

                                    //get used Leave for particular date year
                                     $aluerlf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                                     //$aluerlf->getByAbsencePolicyIdAndUserId2($absence_policy_id,$row['user_id']);
                                     $aluerlf->getgetAbsenceLeaveIdByAbsencePolicyIdAndUserIdUserDateId($absence_policy_id, $data_vl['user_id'], $udlf_obj->getId());

                                     if(count($aluerlf) > 0){ 
                                        $allf1 = TTnew('AbsenceLeaveListFactory');
                                         foreach($aluerlf as $aluerlf_obj){

                                            $leave_taken[$column_abs][$aluerlf_obj->getAbsenceLeaveId()] += 1;
                                        }

                                     }  

                                    //$total_balance_leave = $total_assigned_leaves - $total_used_leaves;
                                }

                                $allf = TTnew('AbsenceLeaveListFactory');

                                $allf->getAll(); 

                                foreach ($allf as $allf_obj){
                                    $absence_leave[$allf_obj->getId()] = $allf_obj;  
                                }


                                if(empty($leave_taken[$column_abs][1]))
                                    {$leave_taken[$column_abs][1] = '0';}
                                if(empty($leave_taken[$column_abs][2]))
                                    {$leave_taken[$column_abs][2] = '0';}
                                if(empty($leave_taken[$column_abs][3]))
                                    {$leave_taken[$column_abs][3] = '0';}

                                 $taken[$column_abs] =  $absence_leave[1]->getShortCode().
                                ' - '.$leave_taken[$column_abs][1].
                                ' | '.$absence_leave[2]->getShortCode().
                                ' - '.$leave_taken[$column_abs][2].
                                ' | '.$absence_leave[3]->getShortCode().
                                ' - '.$leave_taken[$column_abs][3];
                                } 

                            }       

                            $user_obj = $ulf->getById( $data_vl['user_id'] )->getCurrent();
                            

                            if($x % 2 == 0) {
                                $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                            }
                            else{
                                $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                            }


                            $html = $html.'<td  width = "5%" height="25">'.$x.'</td>'; 
                            $html = $html.'<td  width = "10%">'.$user_obj->getEmployeeNumber().'</td>'; 
                            $html = $html.'<td  width = "25%" align="left">'.$user_obj->getFirstName().' '.$user_obj->getLastName().'</td>';
                            $html = $html.'<td width = "20%" align="left">'.$taken['absence_policy-1'].'</td>';
                            $html = $html.'<td width = "20%" align="left">'.$taken['absence_policy-2'].'</td>';
                            $html = $html.'<td width = "20%" align="left">'.$taken['absence_policy-3'].'</td>';
                            $html=  $html.'</tr>';  
                            
                            $x++;                         
                                
                            unset($leave_taken);

                        } 

                        //echo '<pre>'; print_r($leave_taken);
                        //die;

                        //echo '<pre>'; print_r($rows); die;
                    $html=  $html.'</tbody>'; 
                    $html=  $html.'';                                      
                    $html=  $html.'</table>';
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']); 
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
                $output = $pdf->Output('','S');
                        
                //exit;  
               // Debug::setVerbosity(11); 
                if ( isset($output) )
                {
                    return $output;                         
                }
                
                return FALSE;              
                
            }

        }
        //--MOnthly Leave Taken

        //---Monthly Leave Balance
        function MonthlyLeavebalanceReport($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {  
                $filter_header_data = array(
                                        'group_ids' => $filter_data['group_ids'],
                                        'branch_ids' => $filter_data['branch_ids'],
                                        'department_ids' => $filter_data['department_ids'],
                                        'pay_period_ids' => $filter_data['pay_period_ids']
                                    );
                                                                                
                foreach($filter_header_data as $fh_key=>$filter_header){
                    $dlf = TTnew( 'DepartmentListFactory' );
                    if($fh_key == 'department_ids'){
                        foreach ($filter_header as $dep_id) { 
                            $department_list[] = $dlf->getNameById($dep_id); 
                        }
                        $dep_strng = implode(', ', $department_list);
                    }
                                
                    $blf = TTnew( 'BranchListFactory' ); 
                    if($fh_key == 'branch_ids'){
                        foreach ($filter_header as $br_id) { 
                            $branch_list[] = $blf->getNameById($br_id); 
                        }
                        $br_strng = implode(', ', $branch_list);
                    }
                                                                                
                    $uglf = TTnew( 'UserGroupListFactory' ); 
                    if($fh_key == 'group_ids'){
                        foreach ($filter_header as $gr_id) {   
                            $group_list[] = $uglf->getNameById($gr_id); 
                        }
                        $gr_strng = implode(', ', $group_list);
                    }
                                
                }  
                $pplf = TTnew( 'PayPeriodListFactory' );
                if(isset($filter_data['pay_period_ids'][0])){                                                              
                    $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                    $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                }else{
                    $pay_period_start = $filter_data['start_date'];
                    $pay_period_end = $filter_data['end_date'];
                
                } 

                
                $date_month = date('m-Y',$pay_period_start); 
                $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m',$pay_period_start), date('Y',$pay_period_start));
                $start_date_year = date('Y',$pay_period_start); 
                
                $dates = array();
                $current = $pay_period_start;
                $last = $pay_period_end;

                while( $current <= $last ) {

                    $dates[] = date('d', $current); 
                    $current = strtotime('+1 day', $current);
                }
            
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            
                                                                                
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $new_rows = $data;
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                $_SESSION['header_data'] = array( 
                                                  'payperiod_end_date'   => date('Y-M',$pay_period_end),
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Monthly Leave Balance Report', 
                                                  'group_list'  => $gr_strng, 
                                                  'department_list'  => $dep_strng, 
                                                  'branch_list'  => $br_strng, 
                                                  'line_width'  => 280, 
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('l','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '';
                
                $html = $html.'</tr>'; 
                
                $pdf->SetFont('', 'B',6.5); 
                
                
                $row_data_day_key = array(); 
                $j1 = 0;

                $html = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
                    $html = $html.'<td height="17" width = "5%">#</td>';
                    $html = $html.'<td width = "10%">Emp. No.</td>';
                    $html = $html.'<td width = "25%">Emp. Name</td>';
                    $html = $html.'<td width = "20%">Annual Leave</td>';
                    $html = $html.'<td width = "20%">Casual Leave</td>';
                    $html = $html.'<td width = "20%">Sick Leave</td>';
                    $html=  $html.'</tr></thead>';

                    $html=  $html.'<tbody>';

                    $pdf->SetFont('', '',8);

                $x = 1;
                foreach ($new_rows as $data_vl){

                    //Get all accrual policies.
                    $ulf = TTnew( 'UserListFactory' );
                    $aplf = TTnew( 'AbsencePolicyListFactory' );
                    $aplf->getByCompanyId($current_company->getId());
                    if ( $aplf->getRecordCount() > 0 ) {
                        foreach ($aplf as $ap_obj ) {
                            $ap_columns['absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
                        }

                        $columns = array_merge( $columns, $ap_columns);
                    }


                        $ablf = TTnew( 'AccrualBalanceListFactory' );
                        $ablf->getByUserIdAndCompanyId( $data_vl['user_id'], $current_company->getId() );

                        $total_balance_leave_all = array('full_day'=>0, 'half_day'=>0, 'short_leave'=>0);

                        foreach ($columns as $column_abs => $column_abs_vl){ 
                        //foreach ($filter_data['column_ids'] as $column_abs ){
                            
                            $$absence_policy_id = ''; $absence_policy_id_array = array('1', '2', '3');
                            $colAbs_arr = explode('-', $column_abs);
                                 
                            //&& in_array($colAbs_arr[1], $absence_policy_id_array)
                           if($colAbs_arr[0] == 'absence_policy' && in_array($colAbs_arr[1], $absence_policy_id_array) ){
                                $absence_policy_id = $colAbs_arr[1];

                                //get total leaves for particular date year 
                                $alulf = TTnew('AbsenceLeaveUserListFactory');
                                
                                $alulf->getEmployeeTotalLeaves($absence_policy_id, $data_vl['user_id'], $start_date_year);
                                $total_assigned_leaves = 0; 
                               

                                if(count($alulf) > 0){
                                    foreach($alulf as $alulf_obj){
                                        $total_assigned_leaves = $total_assigned_leaves + $alulf_obj->getAmount();
                                    } 
                                    $total_assigned_leaves_indays[$column_abs] = $total_assigned_leaves/(60*60*8);
                               }


                                $udlf = TTnew('UserDateListFactory');
                                $total_used_leaves = 0;
                                for($i1=1; $i1<=$nof_days_for_month; $i1++){
                                    
                                    $udlf->getByUserIdAndDate($data_vl['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                                    $udlf_obj = $udlf->getCurrent();

                                    //get used Leave for particular date year
                                     $aluerlf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                                     //$aluerlf->getByAbsencePolicyIdAndUserId2($absence_policy_id,$row['user_id']);
                                     $aluerlf->getgetAbsenceLeaveIdByAbsencePolicyIdAndUserIdUserDateId($absence_policy_id, $data_vl['user_id'], $udlf_obj->getId());

                                     if(count($aluerlf) > 0){ 
                                        $allf1 = TTnew('AbsenceLeaveListFactory');
                                         foreach($aluerlf as $aluerlf_obj){
                                            $leave_taken[$column_abs][$aluerlf_obj->getAbsenceLeaveId()] += 1;
                                        }

                                     }  

                                }


                                $allf = TTnew('AbsenceLeaveListFactory');
                                $allf->getAll(); 
                                foreach ($allf as $allf_obj){
                                    $absence_leave[$allf_obj->getId()] = $allf_obj;  
                                }


                                if(empty($leave_taken[$column_abs][1]))
                                {
                                    $leave_taken[$column_abs][1] = '0';
                                }
                                if(empty($leave_taken[$column_abs][2]))
                                {
                                    $leave_taken[$column_abs][2] = '0';
                                }
                                if(empty($leave_taken[$column_abs][3]))
                                {
                                    $leave_taken[$column_abs][3] = '0';
                                }


                                $full_days_from_half_days=0;
                                if($leave_taken[$column_abs][2]>0)
                                {
                                    //If odd number of halfdays taken, display balance halfday which is 1 
                                    if($leave_taken[$column_abs][2]%2==1)
                                    {
                                        $leave_balance[$column_abs][2] = 1;
                                    }
                                    else
                                    {
                                        //If even number of halfdays taken, consider 2 halfdays as one fullday
                                        $full_days_from_half_days = $leave_taken[$column_abs][2]/2;
                                    }
                                }
                                $leave_balance[$column_abs][1] = $total_assigned_leaves_indays[$column_abs] - ($leave_taken[$column_abs][1] + $full_days_from_half_days);


                                if(empty($leave_balance[$column_abs][1]))
                                {
                                    $leave_balance[$column_abs][1] = '0';
                                }
                                if(empty($leave_balance[$column_abs][2]))
                                {
                                    $leave_balance[$column_abs][2] = '0';
                                }
                                if(empty($leave_balance[$column_abs][3]))
                                {
                                    $leave_balance[$column_abs][3] = '0';
                                }

                                

                                 $balance[$column_abs] =  $absence_leave[1]->getShortCode().
                                ' - '.$leave_balance[$column_abs][1].
                                ' | '.$absence_leave[2]->getShortCode().
                                ' - '.$leave_balance[$column_abs][2];
                            } 

                        }       
                        //echo '<pre>'; print_r($total_assigned_leaves_indays); print_r($leave_balance);print_r($leave_taken);
                        $user_obj = $ulf->getById( $data_vl['user_id'] )->getCurrent();
                        

                        if($x % 2 == 0) {
                            $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        }
                        else{
                            $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                        }


                        $html = $html.'<td  width = "5%" height="25">'.$x.'</td>'; 
                        $html = $html.'<td  width = "10%">'.$user_obj->getEmployeeNumber().'</td>'; 
                        $html = $html.'<td  width = "25%" align="left">'.$user_obj->getFirstName().' '.$user_obj->getLastName().'</td>';
                        $html = $html.'<td width = "20%" align="left">'.$balance['absence_policy-1'].'</td>';
                        $html = $html.'<td width = "20%" align="left">'.$balance['absence_policy-2'].'</td>';
                        $html = $html.'<td width = "20%" align="left">'.$balance['absence_policy-3'].'</td>';
                        $html=  $html.'</tr>';  
                        
                        $x++;                         
                            
                      

                    } 
                    //print_r($total_assigned_leaves_indays);
                    //die;

                        //echo '<pre>'; print_r($rows);
                    $html=  $html.'</tbody>';                                       
                    $html=  $html.'</table>';
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']); 
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
                $output = $pdf->Output('','S');
                        
                //exit;  
               // Debug::setVerbosity(11); 
                if ( isset($output) )
                {
                    return $output;                         
                }
                
                return FALSE;              
                
            }

        }
        //--MOnthly Leave Balance




        
            //FL ADDED FOR Monthly Attendance (National PVC) 20160524
        function MonthlyAttendanceDetailed($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {  
                $filter_header_data = array(
                                        'group_ids' => $filter_data['group_ids'],
                                        'branch_ids' => $filter_data['branch_ids'],
                                        'department_ids' => $filter_data['department_ids'],
                                        'pay_period_ids' => $filter_data['pay_period_ids']
                                    );
                                                                                
                foreach($filter_header_data as $fh_key=>$filter_header){
                    $dlf = TTnew( 'DepartmentListFactory' );
                    if($fh_key == 'department_ids'){
                        foreach ($filter_header as $dep_id) { 
                            $department_list[] = $dlf->getNameById($dep_id); 
                        }
                        $dep_strng = implode(', ', $department_list);
                    }
                                
                    $blf = TTnew( 'BranchListFactory' ); 
                    if($fh_key == 'branch_ids'){
                        foreach ($filter_header as $br_id) { 
                            $branch_list[] = $blf->getNameById($br_id); 
                        }
                        $br_strng = implode(', ', $branch_list);
                    }
                                                                                
                    $uglf = TTnew( 'UserGroupListFactory' ); 
                    if($fh_key == 'group_ids'){
                        foreach ($filter_header as $gr_id) {   
                            $group_list[] = $uglf->getNameById($gr_id); 
                        }
                        $gr_strng = implode(', ', $group_list);
                    }
                                
                }  
                $pplf = TTnew( 'PayPeriodListFactory' );
                if(isset($filter_data['pay_period_ids'][0])){                                                              
                    $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                    $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                }else{
                    $pay_period_start = $filter_data['start_date'];
                    $pay_period_end = $filter_data['end_date'];
                
                } 

                
                $date_month = date('m-Y',$pay_period_start); 
                $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m',$pay_period_start), date('Y',$pay_period_start));
                
                $dates = array();
                $current = $pay_period_start;
                $last = $pay_period_end;

                while( $current <= $last ) {

                    $dates[] = date('d', $current); 
                    $current = strtotime('+1 day', $current);
                }
                
            //echo '<pre>'; print_r($data); echo '<pre>'; die;
            
            
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            
                                                                                
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $rows = $data;
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                $_SESSION['header_data'] = array( 
                                                  'payperiod_end_date'   => date('Y-M',$pay_period_end),
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Monthly Attendance Report', 
                                                  'group_list'  => $gr_strng, 
                                                  'department_list'  => $dep_strng, 
                                                  'branch_list'  => $br_strng, 
                                                  'line_width'  => 280, 
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('l','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '';
//                $html = $html.'<table border="0" cellspacing="0" cellpadding="0" width="100%">
//                        <tr style="background-color:#CCCCCC;text-align:center;" >';
////                $html = $html.'<td width = "3%">#</td>';
//                    $html = $html.'<td width="40"> </td>';
//                    $html = $html.'<td  width="80"> </td>';
//                    $html = $html.'<td colspan="6"> </td>';
//                    $html = $html.'<td colspan="3">Present</td>';
//                    $html = $html.'<td colspan="2">Absent</td>';
//                    $html = $html.'<td colspan="2">Leaves</td>';
//                    $html = $html.'<td colspan="2">W. Offs</td>';
//                    $html = $html.'<td colspan="2">Holidays</td>';
//                    $html = $html.'<td colspan="2">OT</td>';
//                    $html = $html.'<td colspan="12"></td>';
                
                $html = $html.'</tr>'; 
                
                $pdf->SetFont('', 'B',6.5); 
                
                
                $row_data_day_key = array(); 
                $j1 = 0;
                foreach ($rows as $row){

                    $html = $html.'<table border="0" cellspacing="0" cellpadding="0" width="100%">
                    <tr style="background-color:#CCCCCC;text-align:center;" >';
//                $html = $html.'<td width = "3%">#</td>';
                    $html = $html.'<td width="40"> </td>';
                    $html = $html.'<td  width="80"> </td>';
                    $html = $html.'<td colspan="6"> </td>';
                    $html = $html.'<td colspan="3">Present</td>';
                    $html = $html.'<td colspan="2">Absent</td>';
                    $html = $html.'<td colspan="2">Leaves</td>';
                    $html = $html.'<td colspan="2">W. Offs</td>';
                    $html = $html.'<td colspan="2">Holidays</td>';
                    $html = $html.'<td colspan="2">OT</td>';
                    $html = $html.'<td colspan="12"></td>';

                    $html = $html.'</tr>';
                    $present_days = 0; 
                    $absent_days = 0; 
                    $leave_days = 0; 
                    $week_off = 0; 
                    $holidays = 0;

                    //echo '<pre>'; print_r($row); die;
                    
                    foreach($row['data'] as $row1){
                        
                       if($row1['date_stamp'] != ''){
                           $row_dt = str_replace('/', '-', $row1['date_stamp']);

                           $dat_day = date('d',  strtotime($row_dt)); 
                           //echo '<br><pre>'.$dat_day;
                           $row_data_day_key[$dat_day] = $row1; 
                                                                                
   //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                    } else{ 
                            $tot_ot_hours_data = $row1['over_time'];
                            $tot_worked_actual_hours_data = $row1['actual_time'];
                            $tot_worked_hours_data = explode(':', $row1['worked_time']);
                            $tot_worked_sec_data = ($tot_worked_hours_data[0]*60*60)+ ($tot_worked_hours_data[1]*60);
//                            
                    }
                   } 


                   
                   //row1
                   $nof_presence=0; $nof_absence=0; $nof_leaves=0; $nof_weekoffs=0; $nof_holidays=0; $nof_ot=0; 
                   for($i1=1; $i1<=$nof_days_for_month; $i1++){ 

                        //echo '<pre>';
                        //print_r($row_data_day_key[sprintf("%02d", $i1)]);

                            $status1 = '';

                            $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                            $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);
                            
                            $udlf = TTnew('UserDateListFactory');
                            $pclf = TTnew('PunchControlListFactory');

//                            
                            $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            $udlf_obj = $udlf->getCurrent();
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;
//                         echo '<pre>'; print_r($pc_obj_arr); die;

                            
                            //if punch exists
                            if(!empty($pc_obj_arr)){
                                $status1 = 'P';  
                                //check late come and early departure
                                $elf = TTnew('ExceptionListFactory');
                                $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                                $ex_obj_arr = $elf->getCurrent()->data;
                                 if(!empty($ex_obj_arr)){
                                    $status1 = 'LP';
                                }
                            }else{
                                 $status1 = 'WO'; 
                                 
                                 $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                                 $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                                 $absLeave_obj_arr = $aluelf->getCurrent()->data;
                                 if(!empty($absLeave_obj_arr)){
                                     $leaveName_arr = explode(' ',$absLeave_obj_arr['absence_name']);
                                     $status1 = substr($leaveName_arr[0], 0, 1).substr($leaveName_arr[1], 0, 1);

                                     if($status1!='WO')
                                     {
                                        //$tot_array['L'][]=$i1;
                                        if($absLeave_obj_arr['absence_leave_id']==2)
                                        {
                                            $tot_array['L'] += 0.5;
                                            $tot_array['P'] += 0.5;
                                        }
                                        else
                                        {
                                            $tot_array['L'] += 1;
                                        }
                                        
                                      }

                                 }
                            }
                            

                             $hlf = TTnew('HolidayListFactory');
                             $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1.'-'.$date_month)));
                             $hday_obj_arr = $hlf->getCurrent()->data;
                             
                             if(!empty($hday_obj_arr)){
                                $status1 = 'HLD';  
                             }

                             
                            // $tot_array[$status1][]=$i1;
                             $tot_array[$status1] += 1;

                        }
                       
                        $udtlf = TTnew( 'UserDateTotalListFactory' );
                        $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $current_company->getId(), $row['user_id'], 10, date('Y-m-d',$pay_period_start), date('Y-m-d',$pay_period_end));
                         if ( $udtlf->getRecordCount() > 0 ) {
                            foreach($udtlf as $udt_obj) {
                                if($udt_obj->getOverTimePolicyID()!=0)
                                {
                                    $tot_array['OT'] += 1;
                                }
                            }
                        }

                        if(isset($tot_array['P']))
                        {
                            $nof_presence += $tot_array['P'];
                        }

                        if(isset($tot_array['LP']))
                        {
                            $nof_presence += $tot_array['LP'];
                        }

                        if(isset($tot_array['WO']))
                        {
                            $nof_weekoffs = $tot_array['WO']; 
                        }

                        if(isset($tot_array['HLD']))
                        {
                            $nof_holidays = $tot_array['HLD']; 
                        }

                        if(isset($tot_array['L']))
                        {
                            $nof_leaves = $tot_array['L']; 
                        }

                        if(isset($tot_array['OT']))
                        {
                            $nof_ot = $tot_array['OT']; 
                        }
                   

                        unset($tot_array);
                        $nof_absence =  $nof_days_for_month - ($nof_presence+$nof_weekoffs+$nof_holidays+$nof_leaves);


                  

                    $html =  $html.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                    $html = $html.'<td align="left">'.$row['employee_number'].'</td>'; 
                    $html = $html.'<td>'.$row['first_name'].' '.$row['last_name'].'</td>'; 
                    $html = $html.'<td colspan="6"> </td>';
                    $html = $html.'<td colspan="3">'.$nof_presence.'</td>';
                    $html = $html.'<td colspan="2">'.$nof_absence.'</td>';
                    $html = $html.'<td colspan="2">'.$nof_leaves.'</td>';
                    $html = $html.'<td colspan="2">'.$nof_weekoffs.'</td>';
                    $html = $html.'<td colspan="2">'.$nof_holidays.'</td>';
                    $html = $html.'<td colspan="2">'.$nof_ot.'</td>';
                    $html = $html.'<td colspan="12"></td>';
                    $html = $html.'</tr>'; 
                    
                    //echo '<pre>'; print_r($row['data']); echo '<pre>'; die;  

                   //row2
                    $html=  $html.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                    $html = $html.'<td align="left">Day</td>'; 
                    $html = $html.'<td></td>'; 
                    for($i1=1; $i1<=$nof_days_for_month; $i1++){
                        $html = $html.'<td>'.$i1.'</td>'; 
                    }
                    $html = $html.'</tr>'; /**/
                    
                   //row3
                   $html=  $html.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                        $html = $html.'<td align="left">Shift ID</td>'; 
                        $html = $html.'<td></td>'; 
                        $status_id = '-';
                        for($i1=1; $i1<=$nof_days_for_month; $i1++){
                            $udlf = TTnew('UserDateListFactory');
                            $slf = TTnew('ScheduleListFactory');
                            
                            $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            $udlf_obj = $udlf->getCurrent();
                            
                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;
//                          echo '<pre>'; print_r($sp_obj_arr['shedule_policy_name']); die;
                             
                            $schedule_name_arr = explode('-', $sp_obj_arr['shedule_policy_name']); 
                            $status_id = $schedule_name_arr[1]; 
//                            if(date('D',  strtotime($i1.'-'.$date_month))=='Sat'){
//                                $status_id = 'H';
//                            }
//                            if(date('D',  strtotime($i1.'-'.$date_month))=='Sun'){
//                                $status_id = 'W';
//                            }
                            $html = $html.'<td>'.$status_id.'</td>'; 
                        }
                    $html = $html.'</tr>'; /**/ 
                    
                   //row3
                    $html=  $html.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                        $html = $html.'<td align="left">Shift In</td>'; 
                        $html = $html.'<td></td>'; 
                        for($i1=1; $i1<=$nof_days_for_month; $i1++){
                            $html = $html.'<td>'.$row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'].'</td>'; 
                        }
                    $html = $html.'</tr>'; 
                    
                   //row4
                    $html=  $html.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                        $html = $html.'<td align="left">Shift Out</td>'; 
                        $html = $html.'<td></td>'; 
                        for($i1=1; $i1<=$nof_days_for_month; $i1++){
                            $html = $html.'<td>'.$row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'].'</td>'; 
                        }
                    $html = $html.'</tr>'; 
                    
                    $html = $html.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="34"></td></tr>'; /**/
                    
                   //row5
                   $html=  $html.'<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                        $html = $html.'<td align="left">Late</td>'; 
                        $html = $html.'<td></td>'; 
                        
                        for($i1=1; $i1<=$nof_days_for_month; $i1++){
                            
                            $udlf = TTnew('UserDateListFactory');
                            $slf = TTnew('ScheduleListFactory');
                            
                            $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            $udlf_obj = $udlf->getCurrent();
                            
                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;
                            $late = '';
                            if(!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] != ''){
                                $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                                if($lateSec < 0 ){ 
                                    $late = gmdate("H:i", abs($lateSec))    ; 
                                }
                            } 
                            $html = $html.'<td>'.$late.'</td>'; 
                        } 
                    $html = $html.'</tr>'; /* */
                    
                   //row6
                    $html=  $html.'<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                        $html = $html.'<td align="left">Early</td>'; 
                        $html = $html.'<td></td>'; 
                        for($i1=1; $i1<=$nof_days_for_month; $i1++){
                            
                            $udlf = TTnew('UserDateListFactory');
                            $slf = TTnew('ScheduleListFactory');
                            
                            $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            $udlf_obj = $udlf->getCurrent();
                            
                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;
                            $early = '';
                            if(!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] != ''){
                                $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                                if($earlySec > 0 ){ 
                                    $early = gmdate("H:i", abs($earlySec)); 
                                }
                            }  
                            $html = $html.'<td>'.$early.'</td>'; 
                        }
                    $html = $html.'</tr>'; 
                                                                                
                    
                    $html = $html.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="34"></td></tr>'; /**/
                    
                   //row7
                    $html=  $html.'<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                        $html = $html.'<td align="left" >Status 1</td>'; 
                        $html = $html.'<td></td>'; 
                        $earlySec = $lateSec =0;
                        for($i1=1; $i1<=$nof_days_for_month; $i1++){
                            $status1 = '';
                            $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                            $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);
                            
                            $udlf = TTnew('UserDateListFactory');
                            $pclf = TTnew('PunchControlListFactory');
                                $elf = TTnew('ExceptionListFactory'); //--Add code eranda
//                            
                            $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            $udlf_obj = $udlf->getCurrent();
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;
//                            echo '<pre>'; print_r($pc_obj_arr); die;
                                $elf->getByUserDateId($udlf_obj->getId());
                                $elf_obj = $elf->getCurrent();
                            
                            //if punch exists
                            if(!empty($pc_obj_arr)){
                                $status1 = 'P';  


                                //check late come and early departure
                         /*       $elf = TTnew('ExceptionListFactory');
                                $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                                $ex_obj_arr = $elf->getCurrent()->data;
                                 if(!empty($ex_obj_arr)){
                                    $status1 = 'LP';  
                                 } */
                                    if(!empty($elf_obj->data)) {
                                            //	if($epclf_obj->getExceptionPolicyControlID()) {
                                            foreach ($elf as $elf_obj) {
                                                    if ($elf_obj->getExceptionPolicyID() == '29'||$elf_obj->getExceptionPolicyID() == '5') {
                                                            $status1 = 'ED'; //Early Departure

                                                    }
                                                    if ($elf_obj->getExceptionPolicyID() == '28' || $elf_obj->getExceptionPolicyID() == '4') {
                                                            $status1 = 'LP'; //Late Presents

                                                    }
                                                    if ($elf_obj->getExceptionPolicyID() == '36' || $elf_obj->getExceptionPolicyId() == '37' || $elf_obj->getExceptionPolicyID() == '12' || $elf_obj->getExceptionPolicyID() == '13') {
                                                            $status1 = 'MIS'; //Missed Punch

                                                    }
                                                    if ($elf_obj->getExceptionPolicyID() == '25' || $elf_obj->getExceptionPolicyID() == '1') {
                                                            $status1 = 'P'; //Unscheduled absent

                                                    }
                                            }
                                    }
                            }else{
                                 $status1 = 'WO'; 
                                 
                                 $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                                 $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                                 $absLeave_obj_arr = $aluelf->getCurrent()->data;
                                 if(!empty($absLeave_obj_arr)){
                                     $leaveName_arr = explode(' ',$absLeave_obj_arr['absence_name']);
                                     $status1 = substr($leaveName_arr[0], 0, 1).substr($leaveName_arr[1], 0, 1);  
                                 }
                                //echo '<pre><br>'.date('Y-m-d',  strtotime($i1.'-'.$date_month)).$udlf_obj->getId(); print_r($absLeave_obj_arr); 

                            }
                            

                             $hlf = TTnew('HolidayListFactory');
                             $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1.'-'.$date_month)));
                             $hday_obj_arr = $hlf->getCurrent()->data;
                             
                             if(!empty($hday_obj_arr)){
                                $status1 = 'HLD';  
                             }

                            
                            
                            $html = $html.'<td>'.$status1.'</td>'; 
                        }
                        //die;
                    $html = $html.'</tr>';/**/ 
                    
                   //row8
                    $html=  $html.'<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                        $html = $html.'<td align="left">Status 2</td>'; 
                        $html = $html.'<td></td>'; 
                        for($i1=1; $i1<=$nof_days_for_month; $i1++){
                            $html = $html.'<td>'.date('D',  strtotime($i1.'-'.$date_month)).'</td>';
                            unset($row_data_day_key[sprintf("%02d", $i1)]); 
                        }
                    $html = $html.'</tr>'; 
                                                                                
                    $html = $html.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="34"><br/><hr/></td></tr>'; 

                    $html=  $html.'</table>';
                    $j1++;

                    if($j1%3 == 0){
                        $html .= '<br pagebreak="true" />'; 
                    }
                    
                }

                 //echo $html; die;      
      
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']); 
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
                $output = $pdf->Output('','S');
                        
                //exit;  
               // Debug::setVerbosity(11); 
                if ( isset($output) )
                {
                    return $output;                         
                }
                
                return FALSE;              
                
            }

        }
        
         //FL ADDED FOR Monthly Late (National PVC) 20160524
        function MonthlyLateDetailed($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {  
            $filter_header_data = array(
                'group_ids' => $filter_data['group_ids'],
                'branch_ids' => $filter_data['branch_ids'],
                'department_ids' => $filter_data['department_ids'],
                'pay_period_ids' => $filter_data['pay_period_ids']
            );
                                                                                
            foreach($filter_header_data as $fh_key=>$filter_header){
                $dlf = TTnew( 'DepartmentListFactory' );
                if($fh_key == 'department_ids'){
                    foreach ($filter_header as $dep_id) { 
                        $department_list[] = $dlf->getNameById($dep_id); 
                    }
                    $dep_strng = implode(', ', $department_list);
                }
                            
                $blf = TTnew( 'BranchListFactory' ); 
                if($fh_key == 'branch_ids'){
                    foreach ($filter_header as $br_id) { 
                        $branch_list[] = $blf->getNameById($br_id); 
                    }
                    $br_strng = implode(', ', $branch_list);
                }
                                                                            
                $uglf = TTnew( 'UserGroupListFactory' ); 
                if($fh_key == 'group_ids'){
                    foreach ($filter_header as $gr_id) {   
                        $group_list[] = $uglf->getNameById($gr_id); 
                    }
                    $gr_strng = implode(', ', $group_list);
                }
                            
            }

            $pplf = TTnew( 'PayPeriodListFactory' );
            if(isset($filter_data['pay_period_ids'][0])){                                                              
                $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
            }else{
                $pay_period_start = $filter_data['start_date'];
                $pay_period_end = $filter_data['end_date'];
            
            }
            
            $date_month = date('m-Y',$pay_period_start);
            $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m',$pay_period_start), date('Y',$pay_period_start)); 
            
            $dates = array();
            $current = $pay_period_start;
            $last = $pay_period_end;

            while( $current <= $last ) {

                $dates[] = date('d', $current); 
                $current = strtotime('+1 day', $current);
            }
                
           
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";

                                                                                
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $rows = $data; //echo '<pre>'; print_r($rows);

                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Monthly Late Report', 
                                                  'group_list'  => $gr_strng, 
                                                  'department_list'  => $dep_strng, 
                                                  'branch_list'  => $br_strng, 
                                                  'payperiod_end_date'   => date('Y-M',$pay_period_end),
                                                  'line_width'  => 280, 
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('l','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '<table border="0" cellspacing="0" cellpadding="0" width="100%"> '; 

                $pdf->SetFont('', 'B',7); 
                
                $row_data_day_key = array();
                foreach ($rows as $row){
                    foreach($row['data'] as $row1){
                       if($row1['date_stamp'] != ''){
                           $row_dt = str_replace('/', '-', $row1['date_stamp']);

                           $dat_day = date('d',  strtotime($row_dt)); 
                           $row_data_day_key[$dat_day] = $row1; 
                                                                                
   //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                        } else{ 
                            $tot_ot_hours_data = $row1['over_time'];
                            $tot_worked_actual_hours_data = $row1['actual_time'];
                            $tot_worked_hours_data = explode(':', $row1['worked_time']);
                            $tot_worked_sec_data = ($tot_worked_hours_data[0]*60*60)+ ($tot_worked_hours_data[1]*60);
//                            
                        }
                } 
                   
                    $html=  $html.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                    $html = $html.'<td align="left" width="4%">'.$row['employee_number'].'</td>'; 
                    $html = $html.'<td align="left" width="8%">'.$row['first_name'].' '.$row['last_name'].'</td>'; 
                    $html = $html.'<td colspan="'.$nof_days_for_month.'"></td>';
                    $html = $html.'<td >Total</td>';
                    $html = $html.'</tr>'; 
                    
                    $html=  $html.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                        $html = $html.'<td align="left">Day</td>'; 
                        $html = $html.'<td></td>'; 
                        for($i1=1; $i1<=$nof_days_for_month; $i1++){
                            $html = $html.'<td>'.$i1.'</td>'; 
                        }
                    $html = $html.'</tr>'; 
                    
                    $html = $html.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="'.($nof_days_for_month+3).'"></td></tr>'; 
                 
                    $html=  $html.'<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                    $html = $html.'<td align="left">Late</td>'; 
                    $html = $html.'<td></td>';

                    $TotlateSec = 0;
                    for($i1=1; $i1<=$nof_days_for_month; $i1++){
                        
                        $late = '';
                        if($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] != '' && $row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']!=''){

                            $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);

                            /*if($lateSec < 0 ){ 
                                //echo '<br>late... '.;
                                $late = gmdate("H:i", abs($lateSec)); 

                                echo '<br><br>minpunch strtotime...'.strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']).
                                ' <br>minpunch date... '.date("H:i", (strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']))).
                                '<br>minpunch ...  '.$row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']; 

                                $TotlateSec = $TotlateSec + abs($lateSec) ;
                            }*/

                                
                            $late = gmdate("H:i", abs($lateSec)); 

                            $TotlateSec = $TotlateSec + abs($lateSec) ;

                        } 
                        
                        $html = $html.'<td>'.$late.'</td>'; 
                    }

                    $Totlate_hours = intval($TotlateSec/3600);
                    $Totlate_minutes = intval(($TotlateSec%3600)/60);
                    $Totlate_seconds = ($TotlateSec%3600)%60;

                    $html = $html.'<td><b>'.$Totlate_hours.':'.$Totlate_minutes.'</b></td>';
                    $html = $html.'</tr>'; 
                     
                                                                                
                                                                                
                    $html = $html.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="'.($nof_days_for_month+3).'"><br/><hr/></td></tr>'; 
                }                                                          

                 
                                                                                
                $html=  $html.'</table>';        
      
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
                $output = $pdf->Output('','S');

                //exit;  

                if ( isset($output) )
                {
                    return $output;                         
                }

                return FALSE;              
                                
            }

        }

        
        //FL ADDED FOR EMPLOYEE TIME SHEET REPORT (National PVC) 20160601
        function EmployeeTimeSheet($data1, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {  
              $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Employee Time Sheet', 
                                                  'line_width'  => 185, 
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('p','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                $pdf->SetFont('', 'B',7);  
//            $data = $data;
                $html = '';
                $j = 0;
foreach ($data1 as  $data){
            $data['tot_data'] = $data['data'][count($data['data'])-1];
            array_pop($data['data']);//delete tot of data array 
//            echo '<pre>';     print_r( $data ); echo '<pre>'; die;
                $pplf = TTnew( 'PayPeriodListFactory' );
                if(isset($filter_data['pay_period_ids'][0])){                                                              
                    $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                    $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                }else{
                    $pay_period_start = $filter_data['start_date'];
                    $pay_period_end = $filter_data['end_date'];
                
                }
                
                $dates = array();
                $current = $pay_period_start;
                $last = $pay_period_end;
                $j=0;
                while( $current <= $last ) {

                    $dates[$j]['day'] = date('d', $current); 
                    $dates[$j]['date'] = date('d/m/Y D', $current); 
                    $current = strtotime('+1 day', $current);
                    $j++;
                }
                
                $present_days = 0; 
                    $absent_days = 0; 
                    $week_off = 0; 
                    $holidays = 0;
                    $row_data_day_key = array();
                    foreach($data['data'] as $row1){
                       if($row1['date_stamp'] != ''){
                           $row_dt = str_replace('/', '-', $row1['date_stamp']);

                           $dat_day = date('d',  strtotime($row_dt)); 
                           $row_data_day_key[$dat_day] = $row1;                             
                    } 
                   } 
            
            
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            
                                                                                
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $rows = $data;
    //            echo '<pre>'; print_r($rows); echo '<pre>'; die;

                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                
                
                $html=   $html.'<table width="105%">'; 
                $html=  $html.'<tr align="right">'; 
                $html= $html.'<td>Emp Name : '.$rows['full_name'].'</td>'; 
                $html=  $html.'</tr>'; 
                $html=  $html.'<tr align="right">'; 
                $html= $html.'<td>Emp No : '.$rows['employee_number'].'</td>'; 
                $html=  $html.'</tr>'; 
                $html=  $html.'<tr align="right">'; 
                $html= $html.'<td>Month : '.date('M Y',$pay_period_start).'</td>'; 
                $html=  $html.'</tr>'; 
                $html=  $html.'</table>';
                //Header
                // create some HTML content
                $html = $html.'<table border="1" cellspacing="0" cellpadding="0" width="105%">
                        <tr style="background-color:#CCCCCC;text-align:center; padding:5px;" >';
//                $html = $html.'<td width = "3%">#</td>';
                $html = $html.'<td width="15%"><table><tr><td></td></tr><tr><td>Date</td></tr><tr><td></td></tr></table> </td>';
                $html = $html.'<td width="9%"><table><tr><td></td></tr><tr><td>First In</td></tr><tr><td></td></tr></table> </td>';
                $html = $html.'<td width="9%"><table><tr><td></td></tr><tr><td>Last Out</td></tr><tr><td></td></tr></table> </td>';
                $html = $html.'<td width="9%"><table><tr><td></td></tr><tr><td>Worked Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html.'<td width="9%"><table><tr><td></td></tr><tr><td>OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html.'<td width="15%" colspan="2"><table><tr><td colspan="2"></td></tr><tr><td colspan="2">Status</td></tr><tr><td>1</td><td>2</td></tr></table> </td>';
                $html = $html.'<td width="16%"><table><tr><td></td></tr><tr><td>Purpose</td></tr><tr><td></td></tr></table> </td>';
                $html = $html.'<td width="18%"><table><tr><td></td></tr><tr><td>Remarks</td></tr><tr><td></td></tr></table> </td>';
                $html=  $html.'</tr>';        
                
                $pdf->SetFont('', '',8); 
                
               $totLate = $totEarly = 0;
                              
                foreach($dates as $date){
//echo $row_data_day_key[$date['day']]['date_stamp'] ; die;
                       $dateStamp = '';
                    if($row_data_day_key[$date['day']]['date_stamp'] != ''){
                        $dateStamp = DateTime::createFromFormat('d/m/Y', $row_data_day_key[$date['day']]['date_stamp'])->format('Y-m-d');
                    }   
                    $EmpDateStatus = $this->getReportStatusByUserIdAndDate($rows['user_id'],$dateStamp);
//                    echo'<pre>'; print_r( $EmpDateStatus);  

                    $status1 = $status2 = '';
                    $earlySec = $lateSec = 0;
                    if(($row_data_day_key[$date['day']]['min_punch_time_stamp'] != '' && $row_data_day_key[$date['day']]['min_punch_time_stamp']!="") && 
                            ($row_data_day_key[$date['day']]['shedule_start_time'] !="" && $row_data_day_key[$date['day']]['shedule_end_time'] !="")){
                        $lateSec = strtotime($row_data_day_key[$date['day']]['shedule_start_time']) - strtotime($row_data_day_key[$date['day']]['min_punch_time_stamp']); 
                        $earlySec = strtotime($row_data_day_key[$date['day']]['shedule_end_time']) - strtotime($row_data_day_key[$date['day']]['max_punch_time_stamp']); 
                        
                        if($earlySec>0){$totEarly = $totEarly + abs($earlySec); } 
                        if($lateSec<0){$totLate = $totLate + abs($lateSec); }  
                        $status1 = 'P';
                        $status2 = 'P';
                     }else{
                     $day = explode(' ', $date['date']);
                     if($day[1]=='Sun'){
                         if($row_data_day_key[$date['day']]['worked_time']!=""){
                            $status1 = 'POW';
                            $status2 = 'POW';
                         }else{
                            $status1 = 'WO';
                            $status2 = 'WO';
                         }
                     }else{
                         $status1 = 'A';
                         $status2 = 'A';
                     }

                    }
                    //echo'<pre>'; print_r($row_data_day_key); echo'<pre>'; die; 
                                                                      
                    $html=  $html.'<tr align="center">';        
                    $html=  $html.'<td style="padding:15px;" align="left">'.$date['date'].'</td>';        
                    $html=  $html.'<td>'.$row_data_day_key[$date['day']]['min_punch_time_stamp'].'</td>';        
                    $html=  $html.'<td>'.$row_data_day_key[$date['day']]['max_punch_time_stamp'].'</td>';        
                    $html=  $html.'<td>'.$row_data_day_key[$date['day']]['worked_time'].'</td>';            
                    $html=  $html.'<td>'.$row_data_day_key[$date['day']]['over_time'].'</td>';            
                    $html=  $html.'<td>'.$EmpDateStatus['status1'].'</td>';        
                    $html=  $html.'<td>'.$EmpDateStatus['status2'].'</td>';        
//                        $html=  $html.'<td>O</td>';           
                    $html=  $html.'<td></td>';        
                    $html=  $html.'<td></td>';        
                    $html=  $html.'</tr>';        
                } 

                $html=  $html.'<tr>'; 
                $html=  $html.'<td colspan="9"></td>'; 
                $html=  $html.'</tr>'; 
                $html=  $html.'<tr>'; 
                $html=  $html.'<td colspan="9">';
           
                $html=  $html.'<table>'; 
                        
                $html=  $html.'<tr>';
                $html=  $html.'<td width="15%">No Of Days Worked: </td>';
                $html=  $html.'<td width="05%"></td>';
                $html=  $html.'<td>'.count($row_data_day_key).'</td>';
                $html=  $html.'<td></td>';
                $html=  $html.'<td>Late / Early Hours :</td>';
                $html=  $html.'<td>'.gmdate("H:i", ($totLate+$totEarly)).'</td>';
                $html=  $html.'<td></td>';
                $html=  $html.'</tr>';
            
                $html=  $html.'<tr>';
                $html=  $html.'<td width="15%">Total Work Hrs: </td>';
                $html=  $html.'<td></td>';
                $html=  $html.'<td>'.$rows['tot_data']['worked_time'].'</td>';
                $html=  $html.'<td></td>';
                $html=  $html.'<td>No Pay Days :</td>';
                $html=  $html.'<td>0.00</td>';
                $html=  $html.'<td></td>';
                $html=  $html.'</tr>';
            
                 $otplf = TTnew( 'OverTimePolicyListFactory' );
                $allOtAccount = $otplf->getAll();
                foreach ($allOtAccount as $OtAccount){
                    if(isset($rows['tot_data']['over_time_policy-'.$OtAccount->getId()])){
                                $html=  $html.'<tr>';
                                $html=  $html.'<td width="15%">'.$OtAccount->getName().': </td>';
                                $html=  $html.'<td colspan="4">'.$rows['tot_data']['over_time_policy-'.$OtAccount->getId()].' Hrs. @ Rs...................Per Hours Rs...............</td>'; 
                                $html=  $html.'<td></td>';
                                $html=  $html.'<td></td>';
                                $html=  $html.'</tr>';

                        } 
                }
            
                $html=  $html.'<tr>';
                $html=  $html.'<td width="15%">Total OT Hours: </td>';
                $html=  $html.'<td colspan="4">'.$rows['tot_data']['over_time'].' Total OT Amount Rs.......................................</td>'; 
                $html=  $html.'<td></td>';
                $html=  $html.'<td></td>';
                $html=  $html.'</tr>';
                        
                $html=  $html.'</table>';

                $html= $html.'</td>'; 
                $html=  $html.'</tr>'; 
                $html=  $html.'</table>'; 
                                                                                
      
                $html=  $html.'<table>';
                $html=  $html.'<tr><td colspan="4"></td></tr>';
                $html=  $html.'<tr><td colspan="4"></td></tr>';
                $html=  $html.'<tr><td colspan="4"></td></tr>';
                $html=  $html.'<tr align="center">';
                $html= $html.'<td>.............................................. </td>';  
                $html= $html.'<td>.............................................. </td>';  
                $html= $html.'<td>.............................................. </td>';  
                $html= $html.'<td>.............................................. </td>';  
                $html=  $html.'</tr>';  
                $html=  $html.'<tr align="center">';
                $html= $html.'<td>Prepared by </td>'; 
                $html= $html.'<td>Checked by </td>'; 
                $html= $html.'<td>Approved by</td>'; 
                $html= $html.'<td>Date</td>'; 
                $html=  $html.'</tr>'; 
                
                $html=  $html.'<tr><td colspan="4"></td></tr>';
                $html=  $html.'</table>'; 
                        
                $html=  $html.'<table width="105%" border="1">'; 
                $html=  $html.'<tr align="center">'; 
                $html= $html.'<td>P - Present / A - Absenrteism(No Pay) / LP - Late Present /ED - Early Departure / MIS - Miss Punch / POW - Present on Week Off / POH - Present On Holiday / HLD - Holiday / WO - Week Off / SL - Short Leave / CL - Casual Leave / AL - Annual Leave / ML - Medical Leave </td>'; 
                $html=  $html.'</tr>'; 
                $html=  $html.'</table>';  
                $html = $html.'<br pagebreak="true" />';  

                $j++;
            }
            
                            }
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');
                    
            unset($_SESSION['header_data']);
                    
            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('','S');
                    
            //exit;  
                            
            if ( isset($output) )
            {
                    return $output;                         
            }
            
            return FALSE;              
        }

        
           //FL ADDED FOR EMPLOYEE TIME SHEET REPORT (National PVC) 20160816
        function EmployeeLeaveBalance($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
        {                                                                       
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            
                                                                                
            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                $rows = $data;

                //echo '<pre>'; print_r($data); die;

                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
                                                                                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                  'heading'  => 'Leave Balance Summary', 
                                                  'line_width'  => 185, 
                    
                                                );
                                                                                                
                $pdf = TTnew( 'TimeReportHeaderFooter' );                                                               
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('p','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;         
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                $pdf->SetFont('', 'B',7);    
                
//                $html=   '<table width="105%">'; 
//                $html=  $html.'<tr align="right">'; 
//                $html= $html.'<td>Emp No : '.$rows['employee_number'].'</td>'; 
//                $html=  $html.'</tr>'; 
//                $html=  $html.'<tr align="right">'; 
//                $html= $html.'<td>Month : '.date('M Y',$pay_period_start).'</td>'; 
//                $html=  $html.'</tr>'; 
//                $html=  $html.'</table>';
                //Header
                // create some HTML content
                /*$html = $html.'<table border="1" cellspacing="0" cellpadding="0" width="105%">
                        <tr style="background-color:#CCCCCC;text-align:center; padding:5px;" >';*/
//                $html = $html.'<td width = "3%">#</td>';
                //echo '<pre>';                print_r($columns); 
                //echo '<pre>'; print_r($rows); die;
                /*foreach ($columns as $column){
                      $html = $html.'<td><table><tr><td></td></tr><tr><td>'.$column.'</td></tr><tr><td></td></tr></table> </td>';
                }
                $html=  $html.'</tr>';  
                foreach($rows as $row){
                    $html=  $html.'<tr>';
                    foreach ($columns as $column_key=>$col1){
                        $html=  $html.'<td> &nbsp;'.$row[$column_key].'</td>';
//                        echo '<pre>';  print_r($column_key); die; 
                    }
                    $html=  $html.'</tr>';

                }*/


                $html = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
                $html = $html.'<td height="17" width = "5%">#</td>';
                $html = $html.'<td width = "10%">Emp. No.</td>';
                $html = $html.'<td width = "45%">Emp. Name</td>';
                $html = $html.'<td width = "25%">Total Balance</td>';
                $html=  $html.'</tr></thead>';

                $html=  $html.'<tbody>';

                $pdf->SetFont('', '',8);

                $x = 1;
                foreach($rows as $row){

                    if($x % 2 == 0) {
                        $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                    }
                    else{
                        $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                    }
                    $html = $html.'<td  width = "5%" height="25">'.$x.'</td>'; 
                    $html = $html.'<td width = "10%">'.$row['employee_number'].'</td>'; 
                    $html = $html.'<td width = "45%" align="left">'.$row['full_name'].'</td>'; 
                    $html = $html.'<td width = "25%" align="left">'.$row['total_balance'].'</td>';
                    $html=  $html.'</tr>';

                    $x++;

                } 

                $html=  $html.'</tbody>';                                       
                $html=  $html.'</table>';        

                      
                
               /*  
                        
                $html=  $html.'</table>';

                $html=  $html.'</td>'; 
                $html=  $html.'</tr>'; 
                $html=  $html.'</table>'; 
                $html=  $html.'<tr><td colspan="4"></td></tr>';
                $html=  $html.'</table>'; */
                                                                                
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
                $output = $pdf->Output('','S');
                        
                //exit;  
                
                if ( isset($output) )
                {
                        return $output;                         
                }
                
                return FALSE;              
                                
            }

        }
        
        //FL ADDED FOR GET REPORT STATUS 20160819. 
        function getReportStatusByUserIdAndDate($user_id, $date){
            $status1 = $status2 = '';
            $all_status = array('status1_all'=>'','status2_all'=>'','status1'=>'', 'status2'=>'');
            $udlf = TTnew('UserDateListFactory');
            
            $udlf->getByUserIdAndDate($user_id,$date);
            $udlf_obj = $udlf->getCurrent();
            $user_date_id = $udlf_obj->getId();
            
          /*  $elf = TTnew('ExceptionListFactory');
            $elf->getByUserDateId($user_date_id);
			*/
			$elf = TTnew('ExceptionListFactory');
            $elf->getByUserDateId($user_date_id);
            $elf_obj = $elf->getCurrent();
    /*        foreach ($elf as $elf_obj){
                if($elf_obj->getExceptionPolicyId() == '5'){
                    $status2 = 'ED'; //Early Departure
                    $all_status['status2_all'] .= ' ED';
                }
                if($elf_obj->getExceptionPolicyId() == '4'){
                    $status2 = 'LP'; //Late Presents
                    $all_status['status2_all'] .= ' LP';
                }
                if($elf_obj->getExceptionPolicyId() == '12' || $elf_obj->getExceptionPolicyId() == '13'){
                    $status2 = 'MIS'; //Missed Punch
                    $all_status['status2_all'] .= ' MIS';
                }
                if($elf_obj->getExceptionPolicyId() == '1'){
                    $status2 = 'A'; //Unscheduled absent
                    $all_status['status2_all'] .= ' A';
                }
            }
			*/
                   
                $plf = TTnew('PunchListFactory');
                $plf->getByUserDateId($user_date_id);
                $plf_obj = $plf->getCurrent();
                    
                $slf = TTnew('ScheduleListFactory');
                $slf->getByUserDateId($user_date_id);
                $slf_obj = $slf->getCurrent();
                    
                if(!empty($plf_obj->data)){
                    $status1 = 'P';//Present
                    $all_status['status1_all'] .= ' P';
                    
                    if(empty($slf_obj->data)){
                        $status2 = 'POW';//Present On Week Off
                        $all_status['status2_all'] .= ' POW'; 
                    }
                }else{                                                        
                    if(!empty($slf_obj->data)){
                        $status1 = 'A';//Absent
                        $all_status['status1_all'] .= ' A'; 
                    }else{
                        $status1 = 'WO';//Absent Week Off
                        $all_status['status1_all'] .= ' WO'; 
                    }
                }
				
				 if(!empty($elf_obj->data)) {
				//	if($epclf_obj->getExceptionPolicyControlID()) {
                            foreach ($elf as $elf_obj) {
                                if ($elf_obj->getExceptionPolicyID() == '29'||$elf_obj->getExceptionPolicyID() == '5') {
                                    $status2 = 'ED'; //Early Departure
                                    $all_status['status2_all'] .= ' ED';
                                }
                                if ($elf_obj->getExceptionPolicyID() == '28' || $elf_obj->getExceptionPolicyID() == '4') {
                                    $status2 = 'LP'; //Late Presents
                                    $all_status['status2_all'] .= ' LP';
                                }
                                if ($elf_obj->getExceptionPolicyID() == '36' || $elf_obj->getExceptionPolicyId() == '37' || $elf_obj->getExceptionPolicyID() == '12' || $elf_obj->getExceptionPolicyID() == '13') {
                                    $status2 = 'MIS'; //Missed Punch
                                    $all_status['status2_all'] .= ' MIS';
                                }
                                if ($elf_obj->getExceptionPolicyID() == '25' || $elf_obj->getExceptionPolicyID() == '1') {
                                    $status2 = 'P'; //Unscheduled absent
                                    $all_status['status2_all'] .= ' A';
                                }
                            }
                        }
                
                $hlf = TTnew('HolidayListFactory');
                $hlf->getByPolicyGroupUserIdAndDate($user_id, $date);
                $hlf_obj = $hlf->getCurrent();
                if(!empty($hlf_obj->data)){
                    $status1 = 'HLD';//Holiday
                    $all_status['status1_all'] .= ' HLD';
                    if(!empty($plf_obj->data)){
                        $status2 = 'POH';//Present on Holiday
                        $all_status['status2_all'] .= ' POH';
                    }
                }
                
                $aluerlf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                $aluerlf->getAbsencePolicyByUserDateId($user_date_id);
                $aluerlf_obj = $aluerlf->getCurrent();
                if(!empty($aluerlf_obj->data)){
                    $leaveName_arr = explode(' ', $aluerlf_obj->data['absence_name']);
                    $status2 = substr($leaveName_arr[0], 0, 1).substr($leaveName_arr[1], 0, 1);//Leave Type
                        $all_status['status2'] .= ' '.substr($leaveName_arr[0], 0, 1).substr($leaveName_arr[1], 0, 1);
                }
                
                
                $all_status['status1'] = $status1;
                $all_status['status2'] = $status2;
//                echo '<pre>'; print_r($all_status); die;
                return $all_status;
                                                                                
        }
        
}
?>
