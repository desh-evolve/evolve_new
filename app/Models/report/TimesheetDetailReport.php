<?php

namespace App\Models\Report;

class TimesheetDetailReport extends Report {

    function __construct() {
        $this->title = ('TimeSheet Detail Report');
        $this->file_name = 'timesheet_detail_report';

        parent::__construct();

        return TRUE;
    }

    protected function _checkPermissions($user_id, $company_id) {
        if ($this->getPermissionObject()->Check('report', 'enabled', $user_id, $company_id)
                AND $this->getPermissionObject()->Check('report', 'view_timesheet_summary', $user_id, $company_id)) { //Piggyback on timesheet summary permissions.
            return TRUE;
        } else {
            //Debug::Text('Regular employee viewing their own timesheet...', __FILE__, __LINE__, __METHOD__,10);
            //Regular employee printing timesheet for themselves. Force specific config options.
            //Get current pay period from config, then overwrite it with
            $filter_config = $this->getFilterConfig();
            if (isset($filter_config['time_period']['pay_period_id'])) {
                $pay_period_id = $filter_config['time_period']['pay_period_id'];
            } else {
                $pay_period_id = 0;
            }
            $this->setFilterConfig(array('include_user_id' => array($user_id), 'time_period' => array('time_period' => 'custom_pay_period', 'pay_period_id' => $pay_period_id)));

            return TRUE;
        }

        return FALSE;
    }

    protected function _getOptions($name, $params = NULL) {
        $retval = NULL;
        switch ($name) {
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
                    '-1000-template' => ('Template'),
                    '-1010-time_period' => ('Time Period'),
                    '-2010-user_status_id' => ('Employee Status'),
                    '-2020-user_group_id' => ('Employee Group'),
                    '-2030-user_title_id' => ('Employee Title'),
                    '-2040-include_user_id' => ('Employee Include'),
                    '-2050-exclude_user_id' => ('Employee Exclude'),
                    '-2060-default_branch_id' => ('Default Branch'),
                    '-2070-default_department_id' => ('Default Department'),
                    '-2080-punch_branch_id' => ('Punch Branch'),
                    '-2090-punch_department_id' => ('Punch Department'),
                    '-5000-columns' => ('Display Columns'),
                    '-5010-group' => ('Group By'),
                    '-5020-sub_total' => ('SubTotal By'),
                    '-5030-sort' => ('Sort By'),
                );
                break;
            case 'time_period':
                $retval = TTDate::getTimePeriodOptions();
                break;
            case 'date_columns':
                $retval = TTDate::getReportDateOptions(NULL, ('Date'), 13, TRUE);
                break;
            case 'static_columns':
                $retval = array(
                    //Static Columns - Aggregate functions can't be used on these.
                    '-1000-first_name' => ('First Name'),
                    '-1001-middle_name' => ('Middle Name'),
                    '-1002-last_name' => ('Last Name'),
                    '-1005-full_name' => ('Full Name'),
                    '-1030-employee_number' => ('Employee #'),
                    '-1040-status' => ('Status'),
                    '-1050-title' => ('Title'),
                    '-1060-province' => ('Province/State'),
                    '-1070-country' => ('Country'),
                    '-1080-user_group' => ('Group'),
                    '-1090-default_branch' => ('Default Branch'),
                    '-1100-default_department' => ('Default Department'),
                    '-1110-currency' => ('Currency'),
                    //'-1111-current_currency' => ('Current Currency'),
                    //'-1110-verified_time_sheet' => ('Verified TimeSheet'),
                    //'-1120-pending_request' => ('Pending Requests'),
                    '-1400-permission_control' => ('Permission Group'),
                    '-1410-pay_period_schedule' => ('Pay Period Schedule'),
                    '-1420-policy_group' => ('Policy Group'),
                    //Handled in date_columns above.
                    //'-1430-pay_period' => ('Pay Period'),
                    '-1430-branch' => ('Branch'),
                    '-1440-department' => ('Department'),
                    '-1500-min_punch_time_stamp' => ('First In Punch'),
                    '-1505-max_punch_time_stamp' => ('Last Out Punch'),
                    '-1510-verified_time_sheet' => ('Verified TimeSheet'),
                    '-1515-verified_time_sheet_date' => ('Verified TimeSheet Date'),
                );

                $retval = array_merge($retval, $this->getOptions('date_columns'));
                ksort($retval);
                break;
            case 'dynamic_columns':
                $retval = array(
                    //Dynamic - Aggregate functions can be used
                    //Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
                    //'-2010-hourly_rate' => ('Hourly Rate'),
                    //'-2070-schedule_working' => ('Scheduled Time'),
                    //'-2080-schedule_absence' => ('Scheduled Absence'),
                    //'-2085-worked_days' => ('Worked Days'), //Doesn't work for this report.
                    //'-2090-worked_time' => ('Worked Time'),
                    //'-2100-actual_time' => ('Actual Time'),
                    //'-2110-actual_time_diff' => ('Actual Time Difference'),
                    //'-2130-paid_time' => ('Paid Time'),
                    '-2290-regular_time' => ('Regular Time'),
                    '-2500-gross_wage' => ('Gross Wage'),
                    '-2530-regular_time_wage' => ('Regular Time - Wage'),
                    //'-2540-actual_time_wage' => ('Actual Time Wage'),
                    //'-2550-actual_time_diff_wage' => ('Actual Time Difference Wage'),
                    '-2690-regular_time_hourly_rate' => ('Regular Time - Hourly Rate'),
                );

                $retval = array_merge($retval, $this->getOptions('overtime_columns'), $this->getOptions('premium_columns'), $this->getOptions('absence_columns'));
                ksort($retval);

                break;
            case 'overtime_columns':
                //Get all Overtime policies.
                $retval = array();
                $otplf = TTnew('OverTimePolicyListFactory');
                $otplf->getByCompanyId($this->getUserObject()->getCompany());
                if ($otplf->getRecordCount() > 0) {
                    foreach ($otplf as $otp_obj) {
                        $retval['-2291-over_time_policy-' . $otp_obj->getId()] = $otp_obj->getName();
                        $retval['-2591-over_time_policy-' . $otp_obj->getId() . '_wage'] = $otp_obj->getName() . ' ' . ('- Wage');
                        $retval['-2691-over_time_policy-' . $otp_obj->getId() . '_hourly_rate'] = $otp_obj->getName() . ' ' . ('- Hourly Rate');
                    }
                }
                break;
            case 'premium_columns':
                $retval = array();
                //Get all Premium policies.
                $pplf = TTnew('PremiumPolicyListFactory');
                $pplf->getByCompanyId($this->getUserObject()->getCompany());
                if ($pplf->getRecordCount() > 0) {
                    foreach ($pplf as $pp_obj) {
                        $retval['-2291-premium_policy-' . $pp_obj->getId()] = $pp_obj->getName();
                        $retval['-2591-premium_policy-' . $pp_obj->getId() . '_wage'] = $pp_obj->getName() . ' ' . ('- Wage');
                        $retval['-2691-premium_policy-' . $pp_obj->getId() . '_hourly_rate'] = $pp_obj->getName() . ' ' . ('- Hourly Rate');
                    }
                }
                break;
            case 'absence_columns':
                $retval = array();
                //Get all Absence Policies.
                $aplf = TTnew('AbsencePolicyListFactory');
                $aplf->getByCompanyId($this->getUserObject()->getCompany());
                if ($aplf->getRecordCount() > 0) {
                    foreach ($aplf as $ap_obj) {
                        $retval['-2291-absence_policy-' . $ap_obj->getId()] = $ap_obj->getName();
                        if ($ap_obj->getType() == 10) {
                            $retval['-2591-absence_policy-' . $ap_obj->getId() . '_wage'] = $ap_obj->getName() . ' ' . ('- Wage');
                            $retval['-2691-absence_policy-' . $ap_obj->getId() . '_hourly_rate'] = $ap_obj->getName() . ' ' . ('- Hourly Rate');
                        }
                    }
                }
                break;
            case 'columns':
                $retval = array_merge($this->getOptions('static_columns'), $this->getOptions('dynamic_columns'));
                break;
            case 'column_format':
                //Define formatting function for each column.
                $columns = $this->getOptions('dynamic_columns');
                if (is_array($columns)) {
                    foreach ($columns as $column => $name) {
                        if (strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE) {
                            $retval[$column] = 'currency';
                        } elseif (strpos($column, '_time') OR strpos($column, '_policy')) {
                            $retval[$column] = 'time_unit';
                        }
                    }
                }
                $retval['verified_time_sheet_date'] = 'time_stamp';
                break;
            case 'aggregates':
                $retval = array();
                $dynamic_columns = array_keys(Misc::trimSortPrefix($this->getOptions('dynamic_columns')));
                if (is_array($dynamic_columns)) {
                    foreach ($dynamic_columns as $column) {
                        switch ($column) {
                            default:
                                if (strpos($column, '_hourly_rate') !== FALSE) {
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
                    '-1010-by_employee+regular' => ('Regular Time by Employee'),
                    '-1020-by_employee+overtime' => ('Overtime by Employee'),
                    '-1030-by_employee+premium' => ('Premium Time by Employee'),
                    '-1040-by_employee+absence' => ('Absence Time by Employee'),
                    '-1050-by_employee+regular+overtime+premium+absence' => ('All Time by Employee'),
                    '-1060-by_employee+regular+regular_wage' => ('Regular Time+Wage by Employee'),
                    '-1070-by_employee+overtime+overtime_wage' => ('Overtime+Wage by Employee'),
                    '-1080-by_employee+premium+premium_wage' => ('Premium Time+Wage by Employee'),
                    '-1090-by_employee+absence+absence_wage' => ('Absence Time+Wage by Employee'),
                    '-1100-by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Employee'),
                    '-1110-by_date_by_full_name+regular+regular_wage' => ('Regular Time+Wage by Date/Employee'),
                    '-1120-by_date_by_full_name+overtime+overtime_wage' => ('Overtime+Wage by Date/Employee'),
                    '-1130-by_date_by_full_name+premium+premium_wage' => ('Premium Time+Wage by Date/Employee'),
                    '-1140-by_date_by_full_name+absence+absence_wage' => ('Absence Time+Wage by Date/Employee'),
                    '-1150-by_date_by_full_name+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Date/Employee'),
                    '-1160-by_full_name_by_date+regular+regular_wage' => ('Regular Time+Wage by Employee/Date'),
                    '-1170-by_full_name_by_date+overtime+overtime_wage' => ('Overtime+Wage by Employee/Date'),
                    '-1180-by_full_name_by_date+premium+premium_wage' => ('Premium Time+Wage by Employee/Date'),
                    '-1190-by_full_name_by_date+absence+absence_wage' => ('Absence Time+Wage by Employee/Date'),
                    '-1200-by_full_name_by_date+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Employee/Date'),
                    '-1210-by_branch+regular+regular_wage' => ('Regular Time+Wage by Branch'),
                    '-1220-by_branch+overtime+overtime_wage' => ('Overtime+Wage by Branch'),
                    '-1230-by_branch+premium+premium_wage' => ('Premium Time+Wage by Branch'),
                    '-1240-by_branch+absence+absence_wage' => ('Absence Time+Wage by Branch'),
                    '-1250-by_branch+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Branch'),
                    '-1260-by_department+regular+regular_wage' => ('Regular Time+Wage by Department'),
                    '-1270-by_department+overtime+overtime_wage' => ('Overtime+Wage by Department'),
                    '-1280-by_department+premium+premium_wage' => ('Premium Time+Wage by Department'),
                    '-1290-by_department+absence+absence_wage' => ('Absence Time+Wage by Department'),
                    '-1300-by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Department'),
                    '-1310-by_branch_by_department+regular+regular_wage' => ('Regular Time+Wage by Branch/Department'),
                    '-1320-by_branch_by_department+overtime+overtime_wage' => ('Overtime+Wage by Branch/Department'),
                    '-1330-by_branch_by_department+premium+premium_wage' => ('Premium Time+Wage by Branch/Department'),
                    '-1340-by_branch_by_department+absence+absence_wage' => ('Absence Time+Wage by Branch/Department'),
                    '-1350-by_branch_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Branch/Department'),
                    '-1360-by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Pay Period'),
                    '-1370-by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Pay Period'),
                    '-1380-by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Pay Period'),
                    '-1390-by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Pay Period'),
                    '-1400-by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period'),
                    '-1410-by_pay_period_by_employee+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Employee'),
                    '-1420-by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Employee'),
                    '-1430-by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Employee'),
                    '-1440-by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Employee'),
                    '-1450-by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Employee'),
                    '-1451-by_pay_period_by_date_stamp_by_employee+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Date/Employee'),
                    '-1452-by_pay_period_by_date_stamp_by_employee+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Date/Employee'),
                    '-1453-by_pay_period_by_date_stamp_by_employee+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Date/Employee'),
                    '-1454-by_pay_period_by_date_stamp_by_employee+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Date/Employee'),
                    '-1455-by_pay_period_by_date_stamp_by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Date/Employee'),
                    '-1460-by_pay_period_by_branch+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Branch'),
                    '-1470-by_pay_period_by_branch+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Branch'),
                    '-1480-by_pay_period_by_branch+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Branch'),
                    '-1490-by_pay_period_by_branch+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Branch'),
                    '-1500-by_pay_period_by_branch+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Branch'),
                    '-1510-by_pay_period_by_department+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Department'),
                    '-1520-by_pay_period_by_department+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Department'),
                    '-1530-by_pay_period_by_department+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Department'),
                    '-1540-by_pay_period_by_department+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Department'),
                    '-1550-by_pay_period_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Department'),
                    '-1560-by_pay_period_by_branch_by_department+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Branch/Department'),
                    '-1570-by_pay_period_by_branch_by_department+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Branch/Department'),
                    '-1580-by_pay_period_by_branch_by_department+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Branch/Department'),
                    '-1590-by_pay_period_by_branch_by_department+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Branch/Department'),
                    '-1600-by_pay_period_by_branch_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Branch/Department'),
                    '-1610-by_employee_by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Employee/Pay Period'),
                    '-1620-by_employee_by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Employee/Pay Period'),
                    '-1630-by_employee_by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Employee/Pay Period'),
                    '-1640-by_employee_by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Employee/Pay Period'),
                    '-1650-by_employee_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Employee/Pay Period'),
                    '-1660-by_branch_by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Branch/Pay Period'),
                    '-1670-by_branch_by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Branch/Pay Period'),
                    '-1680-by_branch_by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Branch/Pay Period'),
                    '-1690-by_branch_by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Branch/Pay Period'),
                    '-1700-by_branch_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Branch/Pay Period'),
                    '-1810-by_department_by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Department/Pay Period'),
                    '-1820-by_department_by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Pay Department/Pay Period'),
                    '-1830-by_department_by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Pay Department/Pay Period'),
                    '-1840-by_department_by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Pay Department/Pay Period'),
                    '-1850-by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Department/Pay Period'),
                    '-1860-by_branch_by_department_by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Branch/Department/Pay Period'),
                    '-1870-by_branch_by_department_by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Pay Branch/Department/Pay Period'),
                    '-1880-by_branch_by_department_by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Pay Branch/Department/Pay Period'),
                    '-1890-by_branch_by_department_by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Pay Branch/Department/Pay Period'),
                    '-1900-by_branch_by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Branch/Department/Pay Period'),
                    '-1910-by_full_name_by_dow+regular+regular_wage' => ('Regular Time+Wage by Employee/Day of Week'),
                    '-1920-by_full_name_by_dow+overtime+overtime_wage' => ('Overtime+Wage by Pay Employee/Day of Week'),
                    '-1930-by_full_name_by_dow+premium+premium_wage' => ('Premium Time+Wage by Pay Employee/Day of Week'),
                    '-1940-by_full_name_by_dow+absence+absence_wage' => ('Absence Time+Wage by Pay Employee/Day of Week'),
                    '-1950-by_full_name_by_dow+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Employee/Day of Week'),
                );

                break;
            case 'template_config':
                $template = strtolower(Misc::trimSortPrefix($params['template']));
                if (isset($template) AND $template != '') {
                    switch ($template) {
                        case 'specific_template_name':
                            //$retval['column'] = array();
                            //$retval['filter'] = array();
                            //$retval['group'] = array();
                            //$retval['sub_total'] = array();
                            //$retval['sort'] = array();
                            break;
                        default:
                            Debug::Text(' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10);
                            $retval['-1010-time_period']['time_period'] = 'last_pay_period';

                            //Parse template name, and use the keywords separated by '+' to determine settings.
                            $template_keywords = explode('+', $template);
                            if (is_array($template_keywords)) {
                                foreach ($template_keywords as $template_keyword) {
                                    Debug::Text(' Keyword: ' . $template_keyword, __FILE__, __LINE__, __METHOD__, 10);

                                    switch ($template_keyword) {
                                        //Columns
                                        case 'regular':
                                            $retval['columns'][] = 'regular_time';
                                            break;
                                        case 'overtime':
                                        case 'premium':
                                        case 'absence':
                                            $columns = Misc::trimSortPrefix($this->getOptions($template_keyword . '_columns'));
                                            if (is_array($columns)) {
                                                foreach ($columns as $column => $column_name) {
                                                    if (strpos($column, '_wage') === FALSE AND strpos($column, '_hourly_rate') === FALSE) {
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
                                            $columns = Misc::trimSortPrefix($this->getOptions(str_replace('_wage', '', $template_keyword) . '_columns'));
                                            if (is_array($columns)) {
                                                foreach ($columns as $column => $column_name) {
                                                    if (strpos($column, '_wage') !== FALSE) {
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
                if (isset($retval['filter'])) {
                    $retval['-5000-filter'] = $retval['filter'];
                    unset($retval['filter']);
                }
                if (isset($retval['columns'])) {
                    $retval['-5010-columns'] = $retval['columns'];
                    unset($retval['columns']);
                }
                if (isset($retval['group'])) {
                    $retval['-5020-group'] = $retval['group'];
                    unset($retval['group']);
                }
                if (isset($retval['sub_total'])) {
                    $retval['-5030-sub_total'] = $retval['sub_total'];
                    unset($retval['sub_total']);
                }
                if (isset($retval['sort'])) {
                    $retval['-5040-sort'] = $retval['sort'];
                    unset($retval['sort']);
                }
                Debug::Arr($retval, ' Template Config for: ' . $template, __FILE__, __LINE__, __METHOD__, 10);

                break;
            default:
                //Call report parent class options function for options valid for all reports.
                $retval = $this->__getOptions($name);
                break;
        }

        return $retval;
    }

    function getPolicyHourlyRates() {
        //Take into account wage groups!
        //Get all Overtime policies.
        $otplf = TTnew('OverTimePolicyListFactory');
        $otplf->getByCompanyId($this->getUserObject()->getCompany());
        if ($otplf->getRecordCount() > 0) {
            foreach ($otplf as $otp_obj) {
                Debug::Text('Over Time Policy ID: ' . $otp_obj->getId() . ' Rate: ' . $otp_obj->getRate(), __FILE__, __LINE__, __METHOD__, 10);
                $policy_rates['over_time_policy-' . $otp_obj->getId()] = $otp_obj;
            }
        }

        //Get all Premium policies.
        $pplf = TTnew('PremiumPolicyListFactory');
        $pplf->getByCompanyId($this->getUserObject()->getCompany());
        if ($pplf->getRecordCount() > 0) {
            foreach ($pplf as $pp_obj) {
                $policy_rates['premium_policy-' . $pp_obj->getId()] = $pp_obj;
            }
        }

        //Get all Absence Policies.
        $aplf = TTnew('AbsencePolicyListFactory');
        $aplf->getByCompanyId($this->getUserObject()->getCompany());
        if ($aplf->getRecordCount() > 0) {
            foreach ($aplf as $ap_obj) {
                if ($ap_obj->getType() == 10) {
                    $policy_rates['absence_policy-' . $ap_obj->getId()] = $ap_obj;
                } else {
                    $policy_rates['absence_policy-' . $ap_obj->getId()] = FALSE;
                }
            }
        }

        return $policy_rates;
    }

    //Get raw data for report
    function _getData($format = NULL) {
        $this->tmp_data = array('user_date_total' => array(), 'user' => array(), 'verified_timesheet' => array(), 'punch_rows' => array());

        $columns = $this->getColumnConfig();
        $filter_data = $this->getFilterConfig();
        $policy_hourly_rates = $this->getPolicyHourlyRates();

        if ($this->getPermissionObject()->Check('punch', 'view') == FALSE OR $this->getPermissionObject()->Check('wage', 'view') == FALSE) {
            $hlf = TTnew('HierarchyListFactory');
            $permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID($this->getUserObject()->getCompany(), $this->getUserObject()->getID());
            Debug::Arr($permission_children_ids, 'Permission Children Ids:', __FILE__, __LINE__, __METHOD__, 10);
        } else {
            //Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
            $permission_children_ids = array();
            $wage_permission_children_ids = array();
        }
        if ($this->getPermissionObject()->Check('punch', 'view') == FALSE) {
            if ($this->getPermissionObject()->Check('punch', 'view_child') == FALSE) {
                $permission_children_ids = array();
            }
            if ($this->getPermissionObject()->Check('punch', 'view_own')) {
                $permission_children_ids[] = $this->getUserObject()->getID();
            }

            $filter_data['permission_children_ids'] = $permission_children_ids;
        }
        //Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
        if ($this->getPermissionObject()->Check('wage', 'view') == TRUE) {
            $wage_permission_children_ids = TRUE;
        } elseif ($this->getPermissionObject()->Check('wage', 'view') == FALSE) {
            if ($this->getPermissionObject()->Check('wage', 'view_child') == FALSE) {
                $wage_permission_children_ids = array();
            }
            if ($this->getPermissionObject()->Check('wage', 'view_own')) {
                $wage_permission_children_ids[] = $this->getUserObject()->getID();
            }
        }
        //Debug::Text(' Permission Children: '. count($permission_children_ids) .' Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
        //Debug::Arr($permission_children_ids, 'Permission Children: '. count($permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
        //Debug::Arr($wage_permission_children_ids, 'Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);

        $pay_period_ids = array();

        $udtlf = TTnew('UserDateTotalListFactory');
        $udtlf->getTimesheetDetailReportByCompanyIdAndArrayCriteria($this->getUserObject()->getCompany(), $filter_data);
        Debug::Text(' Total Rows: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
        $this->getProgressBarObject()->start($this->getAMFMessageID(), $udtlf->getRecordCount(), NULL, ('Retrieving Data...'));
        if ($udtlf->getRecordCount() > 0) {
            foreach ($udtlf as $key => $udt_obj) {
                $pay_period_ids[$udt_obj->getColumn('pay_period_id')] = TRUE;

                $user_id = $udt_obj->getColumn('user_id');

                $date_stamp = TTDate::strtotime($udt_obj->getColumn('date_stamp'));
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
                if ($column == 'paid_time') {
                    $column = NULL;
                }

                //Debug::Text('Column: '. $column .' Total Time: '. $udt_obj->getColumn('total_time') .' Status: '. $status_id .' Type: '. $type_id .' Rate: '. $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__,10);
                if (( isset($filter_data['include_no_data_users']) AND $filter_data['include_no_data_users'] == 1 )
                        OR ( !isset($filter_data['include_no_data_users']) AND $date_stamp != '' AND $column != '' AND $udt_obj->getColumn('total_time') != 0 )) {

                    $hourly_rate = 0;
                    if ($wage_permission_children_ids === TRUE OR in_array($user_id, $wage_permission_children_ids)) {
                        $hourly_rate = $udt_obj->getColumn('hourly_rate');
                    }
                    if (isset($policy_hourly_rates[$column]) AND is_object($policy_hourly_rates[$column])) {
                        $hourly_rate = $policy_hourly_rates[$column]->getHourlyRate($hourly_rate);
                    }

                    if (isset($this->tmp_data['user_date_total'][$user_id][$date_stamp])) {
                        //Add time/wage and calculate average hourly rate.
                        if (isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column])) {
                            $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
                        } else {
                            $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
                        }

                        if (isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column . '_wage'])) {
                            $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column . '_wage'] += bcmul(bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate);
                        } else {
                            $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column . '_wage'] = bcmul(bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate);
                        }

                        if ($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] > 0) {
                            $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column . '_hourly_rate'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column . '_wage'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column], 3600));
                        } else {
                            $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column . '_hourly_rate'] = $hourly_rate;
                        }

                        //Gross wage calculation must go here otherwise it gets doubled up.
                        if (isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'])) {
                            $this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] += $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column . '_wage'];
                        } else {
                            $this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] = $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column . '_wage'];
                        }
                    } else {
                        $this->tmp_data['user_date_total'][$user_id][$date_stamp] = array(
                            'branch' => $branch,
                            'department' => $department,
                            'pay_period_start_date' => strtotime($udt_obj->getColumn('pay_period_start_date')),
                            'pay_period_end_date' => strtotime($udt_obj->getColumn('pay_period_end_date')),
                            'pay_period_transaction_date' => strtotime($udt_obj->getColumn('pay_period_transaction_date')),
                            'pay_period' => strtotime($udt_obj->getColumn('pay_period_transaction_date')),
                            'pay_period_id' => $udt_obj->getColumn('pay_period_id'),
                            'min_punch_time_stamp' => strtotime($udt_obj->getColumn('min_punch_time_stamp')),
                            'max_punch_time_stamp' => strtotime($udt_obj->getColumn('max_punch_time_stamp')),
                            $column => $udt_obj->getColumn('total_time'),
                            $column . '_hourly_rate' => $hourly_rate,
                            $column . '_wage' => bcmul(bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate),
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

                $this->getProgressBarObject()->set($this->getAMFMessageID(), $key);
            }
        }
        //Debug::Arr($this->tmp_data['user_date_total'], 'User Date Total Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
        //Get user data for joining.
        $ulf = TTnew('UserListFactory');
        $ulf->getAPISearchByCompanyIdAndArrayCriteria($this->getUserObject()->getCompany(), $filter_data);
        Debug::Text(' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
        $this->getProgressBarObject()->start($this->getAMFMessageID(), $ulf->getRecordCount(), NULL, ('Retrieving Data...'));
        foreach ($ulf as $key => $u_obj) {
            $this->tmp_data['user'][$u_obj->getId()] = (array) $u_obj->getObjectAsArray($this->getColumnConfig());

            $this->form_data[$u_obj->getId()] = (array) $u_obj->getObjectAsArray();

            $this->getProgressBarObject()->set($this->getAMFMessageID(), $key);
        }
        //Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
        //Debug::Arr($this->form_data, 'zUser Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
        //Get verified timesheets for all pay periods considered in report.
        $pay_period_ids = array_keys($pay_period_ids);
        if (isset($pay_period_ids) AND count($pay_period_ids) > 0) {
            $pptsvlf = TTnew('PayPeriodTimeSheetVerifyListFactory');
            $pptsvlf->getByPayPeriodIdAndCompanyId($pay_period_ids, $this->getUserObject()->getCompany());
            if ($pptsvlf->getRecordCount() > 0) {
                foreach ($pptsvlf as $pptsv_obj) {
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
        $this->getProgressBarObject()->start($this->getAMFMessageID(), count($this->tmp_data['user_date_total']), NULL, ('Pre-Processing Data...'));

        //Merge time data with user data
        $key = 0;
        if (isset($this->tmp_data['user_date_total'])) {
            foreach ($this->tmp_data['user_date_total'] as $user_id => $level_1) {
                if (isset($this->tmp_data['user'][$user_id])) {
                    foreach ($level_1 as $date_stamp => $row) {
                        //foreach( $level_2 as $branch => $level_3 ) {
                        //foreach( $level_3 as $department => $row ) {
                        $date_columns = TTDate::getReportDates(NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']));
                        $processed_data = array(
                            //'branch' => $branch,
                            //'department' => $department,
                            //'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
                            'min_punch_time_stamp' => TTDate::getDate('TIME', $row['min_punch_time_stamp']),
                            'max_punch_time_stamp' => TTDate::getDate('TIME', $row['max_punch_time_stamp'])
                        );

                        if (isset($this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']])) {
                            $processed_data['verified_time_sheet'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status'];
                            $processed_data['verified_time_sheet_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['created_date'];
                        } else {
                            $processed_data['verified_time_sheet'] = ('No');
                            $processed_data['verified_time_sheet_date'] = FALSE;
                        }

                        $this->data[] = array_merge($this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data);

                        $this->form_data[$user_id]['data'][] = array_merge($row, $date_columns, $processed_data);

                        $this->getProgressBarObject()->set($this->getAMFMessageID(), $key);
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

    function timesheetHeader($user_data) {
        $margins = $this->pdf->getMargins();
        $current_company = $this->getUserObject()->getCompanyObject();

        $border = 0;

        $total_width = $this->pdf->getPageWidth() - $margins['left'] - $margins['right'];

        $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(24));
        $this->pdf->Cell($total_width, 10, ('Employee TimeSheet'), $border, 0, 'C');
        $this->pdf->Ln();
        $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(12));
        $this->pdf->Cell($total_width, 5, $current_company->getName(), $border, 0, 'C');
        $this->pdf->Ln(5);

        //Generated Date/User top right.
        $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(6));
        $this->pdf->setY(($this->pdf->getY() - $this->_pdf_fontSize(6)));
        $this->pdf->setX($this->pdf->getPageWidth() - $margins['right'] - 50);
        $this->pdf->Cell(50, $this->_pdf_fontSize(3), ('Generated') . ': ' . TTDate::getDate('DATE+TIME', time()), 0, 0, 'R', 0, '', 1);
        $this->pdf->Ln();
        $this->pdf->setX($this->pdf->getPageWidth() - $margins['right'] - 50);
        $this->pdf->Cell(50, $this->_pdf_fontSize(3), ('Generated For') . ': ' . $this->getUserObject()->getFullName(), 0, 0, 'R', 0, '', 1);
        $this->pdf->Ln($this->_pdf_fontSize(5));

        $this->pdf->Rect($this->pdf->getX(), $this->pdf->getY() - 2, $total_width, 14);

        $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(12));
        $this->pdf->Cell(30, 5, ('Employee') . ':', $border, 0, 'R');
        $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(12));
        $this->pdf->Cell(70 + (($total_width - 200) / 2), 5, $user_data['first_name'] . ' ' . $user_data['last_name'] . ' (#' . $user_data['employee_number'] . ')', $border, 0, 'L');

        $this->pdf->SetFont('', '', 12);
        $this->pdf->Cell(40, 5, ('Title') . ':', $border, 0, 'R');
        $this->pdf->SetFont('', 'B', 12);
        $this->pdf->Cell(60 + (($total_width - 200) / 2), 5, $user_data['title'], $border, 0, 'L');
        $this->pdf->Ln();

        $this->pdf->SetFont('', '', 12);
        $this->pdf->Cell(30, 5, ('Branch') . ':', $border, 0, 'R');
        $this->pdf->Cell(70 + (($total_width - 200) / 2), 5, $user_data['default_branch'], $border, 0, 'L');
        $this->pdf->Cell(40, 5, ('Department') . ':', $border, 0, 'R');
        $this->pdf->Cell(60 + (($total_width - 200) / 2), 5, $user_data['default_department'], $border, 0, 'L');
        //$this->pdf->Ln();
        //$this->pdf->Cell(30,5, ('Group:') , $border, 0, 'R');
        //$this->pdf->Cell(70,5, $user_data['group'], $border, 0, 'L');
        //$this->pdf->Cell(40,5, ('Department:') , $border, 0, 'R');
        //$this->pdf->Cell(60,5, $user_data['default_department'], $border, 0, 'L');
        $this->pdf->Ln(5);

        $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10));
        $this->pdf->Ln();

        return TRUE;
    }

    function timesheetPayPeriodHeader($user_data, $data) {
        $line_h = 5;

        $margins = $this->pdf->getMargins();
        $total_width = $this->pdf->getPageWidth() - $margins['left'] - $margins['right'];

        $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(10));
        $this->pdf->setFillColor(220, 220, 220);
        if (isset($data['verified_time_sheet']) AND $data['verified_time_sheet'] != FALSE) {
            $this->pdf->Cell(75, $line_h, ('Pay Period') . ':' . $data['pay_period']['display'], 1, 0, 'L', 1);
            $this->pdf->Cell($total_width - 75, $line_h, ('Electronically signed by') . ' ' . $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . ('on') . ' ' . TTDate::getDate('DATE+TIME', $data['verified_time_sheet_date']), 1, 0, 'R', 1);
        } else {
            $this->pdf->Cell($total_width, $line_h, ('Pay Period') . ':' . $data['pay_period']['display'], 1, 0, 'L', 1);
        }

        $this->pdf->Ln();

        unset($this->timesheet_week_totals);
        $this->timesheet_week_totals = Misc::preSetArrayValues(NULL, array('worked_time', 'absence_time', 'regular_time', 'over_time'), 0);

        return TRUE;
    }

    function timesheetWeekHeader($column_widths) {
        $line_h = 10;

        $margins = $this->pdf->getMargins();
        $total_width = $this->pdf->getPageWidth() - $margins['left'] - $margins['right'];

        $buffer = ($total_width - 200) / 10;

        $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(10));
        $this->pdf->setFillColor(220, 220, 220);
        $this->pdf->MultiCell($column_widths['line'] + $buffer, $line_h, '#', 1, 'C', 1, 0);
        $this->pdf->MultiCell($column_widths['date_stamp'] + $buffer, $line_h, ('Date'), 1, 'C', 1, 0);
        $this->pdf->MultiCell($column_widths['dow'] + $buffer, $line_h, ('DoW'), 1, 'C', 1, 0);
        $this->pdf->MultiCell($column_widths['in_punch_time_stamp'] + $buffer, $line_h, ('In'), 1, 'C', 1, 0);
        $this->pdf->MultiCell($column_widths['out_punch_time_stamp'] + $buffer, $line_h, ('Out'), 1, 'C', 1, 0);
        $this->pdf->MultiCell($column_widths['worked_time'] + $buffer, $line_h, ('Worked Time'), 1, 'C', 1, 0);
        $this->pdf->MultiCell($column_widths['regular_time'] + $buffer, $line_h, ('Regular Time'), 1, 'C', 1, 0);
        $this->pdf->MultiCell($column_widths['over_time'] + $buffer, $line_h, ('Over Time'), 1, 'C', 1, 0);
        $this->pdf->MultiCell($column_widths['absence_time'] + $buffer, $line_h, ('Absence Time'), 1, 'C', 1, 0);
        $this->pdf->Ln();

        return TRUE;
    }

    function timesheetDayRow($format, $column_widths, $user_data, $data, $prev_data, $max_i) {
        $margins = $this->pdf->getMargins();
        $total_width = $this->pdf->getPageWidth() - $margins['left'] - $margins['right'];

        $buffer = ($total_width - 200) / 10;

        //Handle page break.
        $page_break_height = 25;
        if ($this->counter_i == 1 OR $this->counter_x == 1) {
            if ($this->counter_i == 1) {
                $page_break_height += 5;
            }
            $page_break_height += 5;
        }
        $this->timesheetCheckPageBreak($page_break_height, TRUE);

        Debug::Text('Pay Period Changed: Current: ' . $data['pay_period_id'] . ' Prev: ' . $prev_data['pay_period_id'], __FILE__, __LINE__, __METHOD__, 10);
        if ($prev_data !== FALSE AND $data['pay_period_id'] != $prev_data['pay_period_id']) {

            //Only display week total if we are in the middle of a week when the pay period ends, not at the end of the week.
            if ($this->counter_x != 1) {
                $this->timesheetWeekTotal($column_widths, $this->timesheet_week_totals);
                $this->counter_x++;
            }

            $this->timesheetPayPeriodHeader($user_data, $data);
        }

        //Show Header
        if ($this->counter_i == 1 OR $this->counter_x == 1) {
            Debug::Text('aFirst Row: Header', __FILE__, __LINE__, __METHOD__, 10);

            if ($this->counter_i == 1) {
                $this->timesheetPayPeriodHeader($user_data, $data);
            }

            $this->timesheetWeekHeader($column_widths);
        }

        if ($this->counter_x % 2 == 0) {
            $this->pdf->setFillColor(220, 220, 220);
        } else {
            $this->pdf->setFillColor(255, 255, 255);
        }

        if ($data['time_stamp'] !== '') {
            $default_line_h = 4;
            $line_h = $default_line_h;

            $total_rows_arr = array();

            //Find out how many punches fall on this day, so we can change row height to fit.
            $total_punch_rows = 1;

            if (isset($user_data['punch_rows'][$data['pay_period_id']][$data['time_stamp']])) {
                Debug::Text('Punch Data Row: ' . $this->counter_x, __FILE__, __LINE__, __METHOD__, 10);

                $day_punch_data = $user_data['punch_rows'][$data['pay_period_id']][$data['time_stamp']];
                $total_punch_rows = count($day_punch_data);
            } else {
                Debug::Text('NO Punch Data Row: ' . $this->counter_x, __FILE__, __LINE__, __METHOD__, 10);
            }

            $total_rows_arr[] = $total_punch_rows;

            $total_over_time_rows = 1;
            if ($data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy'])) {
                $total_over_time_rows = count($data['categorized_time']['over_time_policy']);
            }
            $total_rows_arr[] = $total_over_time_rows;

            $total_absence_rows = 1;
            if ($data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy'])) {
                $total_absence_rows = count($data['categorized_time']['absence_policy']);
            }
            $total_rows_arr[] = $total_absence_rows;

            rsort($total_rows_arr);
            $max_rows = $total_rows_arr[0];
            $line_h = ( $format == 'pdf_timesheet_detail' ) ? $default_line_h * $max_rows : $default_line_h;

            $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9));
            $this->pdf->Cell($column_widths['line'] + $buffer, $line_h, $this->counter_x, 1, 0, 'C', 1);
            $this->pdf->Cell($column_widths['date_stamp'] + $buffer, $line_h, TTDate::getDate('DATE', $data['time_stamp']), 1, 0, 'C', 1);
            $this->pdf->Cell($column_widths['dow'] + $buffer, $line_h, date('D', $data['time_stamp']), 1, 0, 'C', 1);

            $pre_punch_x = $this->pdf->getX();
            $pre_punch_y = $this->pdf->getY();

            //Print Punches
            if ($format == 'pdf_timesheet_detail' AND isset($day_punch_data)) {
                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8));

                $n = 0;
                foreach ($day_punch_data as $punch_control_id => $punch_data) {
                    if (!isset($punch_data[10]['time_stamp'])) {
                        $punch_data[10]['time_stamp'] = NULL;
                        $punch_data[10]['type_code'] = NULL;
                    }
                    if (!isset($punch_data[20]['time_stamp'])) {
                        $punch_data[20]['time_stamp'] = NULL;
                        $punch_data[20]['type_code'] = NULL;
                    }

                    if ($n > 0) {
                        $this->pdf->setXY($pre_punch_x, $punch_y + $default_line_h);
                    }

                    $this->pdf->Cell($column_widths['in_punch_time_stamp'] + $buffer, $line_h / $total_punch_rows, TTDate::getDate('TIME', $punch_data[10]['time_stamp']) . ' ' . $punch_data[10]['type_code'], 1, 0, 'C', 1);
                    $this->pdf->Cell($column_widths['out_punch_time_stamp'] + $buffer, $line_h / $total_punch_rows, TTDate::getDate('TIME', $punch_data[20]['time_stamp']) . ' ' . $punch_data[20]['type_code'], 1, 0, 'C', 1);

                    $punch_x = $this->pdf->getX();
                    $punch_y = $this->pdf->getY();

                    $n++;
                }

                $this->pdf->setXY($punch_x, $pre_punch_y);

                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9));
            } else {
                $this->pdf->Cell($column_widths['in_punch_time_stamp'] + $buffer, $line_h, $data['min_punch_time_stamp'], 1, 0, 'C', 1);
                $this->pdf->Cell($column_widths['out_punch_time_stamp'] + $buffer, $line_h, $data['max_punch_time_stamp'], 1, 0, 'C', 1);
            }

            $this->pdf->Cell($column_widths['worked_time'] + $buffer, $line_h, TTDate::getTimeUnit($data['worked_time']), 1, 0, 'C', 1);
            $this->pdf->Cell($column_widths['regular_time'] + $buffer, $line_h, TTDate::getTimeUnit($data['regular_time']), 1, 0, 'C', 1);

            if ($data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy'])) {
                $pre_over_time_x = $this->pdf->getX();
                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8));

                //Count how many absence policy rows there are.
                $over_time_policy_total_rows = count($data['categorized_time']['over_time_policy']);
                foreach ($data['categorized_time']['over_time_policy'] as $policy_id => $value) {
                    $this->pdf->Cell($column_widths['over_time'] + $buffer, $line_h / $total_over_time_rows, $otp_columns['over_time_policy-' . $policy_id] . ': ' . TTDate::getTimeUnit($value), 1, 0, 'C', 1);
                    $this->pdf->setXY($pre_over_time_x, $this->pdf->getY() + ($line_h / $total_over_time_rows));

                    $over_time_x = $this->pdf->getX();
                }
                $this->pdf->setXY($over_time_x + $column_widths['over_time'], $pre_punch_y);

                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9));
            } else {
                $this->pdf->Cell($column_widths['over_time'] + $buffer, $line_h, TTDate::getTimeUnit($data['over_time']), 1, 0, 'C', 1);
            }

            if ($data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy'])) {
                $pre_absence_time_x = $this->pdf->getX();
                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8));

                //Count how many absence policy rows there are.
                $absence_policy_total_rows = count($data['categorized_time']['absence_policy']);
                foreach ($data['categorized_time']['absence_policy'] as $policy_id => $value) {
                    $this->pdf->Cell($column_widths['absence_time'] + $buffer, $line_h / $total_absence_rows, $ap_columns['absence_policy-' . $policy_id] . ': ' . TTDate::getTimeUnit($value), 1, 0, 'C', 1);
                    $this->pdf->setXY($pre_absence_time_x, $this->pdf->getY() + ($line_h / $total_absence_rows));
                }

                $this->pdf->setY($this->pdf->getY() - ($line_h / $total_absence_rows));

                $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(9));
            } else {
                $this->pdf->Cell($column_widths['absence_time'] + $buffer, $line_h, TTDate::getTimeUnit($data['absence_time']), 1, 0, 'C', 1);
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

        Debug::Text('Row: ' . $this->counter_x, __FILE__, __LINE__, __METHOD__, 10);
        if ($this->counter_x % 7 == 0 OR $this->counter_i == $max_i) {
            $this->timesheetWeekTotal($column_widths, $this->timesheet_week_totals);

            unset($this->timesheet_week_totals);
            $this->timesheet_week_totals = Misc::preSetArrayValues(NULL, array('worked_time', 'absence_time', 'regular_time', 'over_time'), 0);
        }

        $this->counter_i++;
        $this->counter_x++;

        return TRUE;
    }

    function timesheetWeekTotal($column_widths, $week_totals) {
        Debug::Text('Week Total: Row: ' . $this->counter_x, __FILE__, __LINE__, __METHOD__, 10);

        $margins = $this->pdf->getMargins();
        $total_width = $this->pdf->getPageWidth() - $margins['left'] - $margins['right'];

        $buffer = ($total_width - 200) / 10;

        //Show Week Total.
        $total_cell_width = $column_widths['line'] + $column_widths['date_stamp'] + $column_widths['dow'] + $column_widths['in_punch_time_stamp'] + $column_widths['out_punch_time_stamp'] + ($buffer * 5);
        $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(9));
        $this->pdf->Cell($total_cell_width, 6, ('Week Total') . ': ', 0, 0, 'R', 0);
        $this->pdf->Cell($column_widths['worked_time'] + $buffer, 6, TTDate::getTimeUnit($week_totals['worked_time']), 0, 0, 'C', 0);
        $this->pdf->Cell($column_widths['regular_time'] + $buffer, 6, TTDate::getTimeUnit($week_totals['regular_time']), 0, 0, 'C', 0);
        $this->pdf->Cell($column_widths['over_time'] + $buffer, 6, TTDate::getTimeUnit($week_totals['over_time']), 0, 0, 'C', 0);
        $this->pdf->Cell($column_widths['absence_time'] + $buffer, 6, TTDate::getTimeUnit($week_totals['absence_time']), 0, 0, 'C', 0);
        $this->pdf->Ln(); //1

        $this->counter_x = 0; //Reset to 0, as the counter increases to 1 immediately after.
        $this->counter_y++;

        return TRUE;
    }

    function timesheetTotal($column_widths, $totals) {
        $margins = $this->pdf->getMargins();
        $total_width = $this->pdf->getPageWidth() - $margins['left'] - $margins['right'];

        $buffer = ($total_width - 200) / 10;

        $total_cell_width = $column_widths['line'] + $column_widths['date_stamp'] + $column_widths['dow'] + $column_widths['in_punch_time_stamp'] + ($buffer * 4);
        $this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize(9));
        $this->pdf->Cell($total_cell_width, 6, '', 0, 0, 'R', 0);
        $this->pdf->Cell($column_widths['out_punch_time_stamp'] + $buffer, 6, ('Overall Total') . ': ', 'T', 0, 'R', 0);
        $this->pdf->Cell($column_widths['worked_time'] + $buffer, 6, TTDate::getTimeUnit($totals['worked_time']), 'T', 0, 'C', 0);
        $this->pdf->Cell($column_widths['regular_time'] + $buffer, 6, TTDate::getTimeUnit($totals['regular_time']), 'T', 0, 'C', 0);
        $this->pdf->Cell($column_widths['over_time'] + $buffer, 6, TTDate::getTimeUnit($totals['over_time']), 'T', 0, 'C', 0);
        $this->pdf->Cell($column_widths['absence_time'] + $buffer, 6, TTDate::getTimeUnit($totals['absence_time']), 'T', 0, 'C', 0);
        $this->pdf->Ln();

        return TRUE;
    }

    function timesheetSignature($user_data) {
        $border = 0;

        $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10));
        $this->pdf->setFillColor(255, 255, 255);
        $this->pdf->Ln(1);

        $margins = $this->pdf->getMargins();
        $total_width = $this->pdf->getPageWidth() - $margins['left'] - $margins['right'];

        $buffer = ($total_width - 200) / 4;

        //Signature lines
        $this->pdf->MultiCell($total_width, 5, ('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that') . ' ' . $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . ('worked during the designated period.'), $border, 'L');
        $this->pdf->Ln(5); //5

        $this->pdf->Cell(40 + $buffer, 5, ('Employee Signature') . ':', $border, 0, 'L');
        $this->pdf->Cell(60 + $buffer, 5, '_____________________________', $border, 0, 'C');
        $this->pdf->Cell(40 + $buffer, 5, ('Supervisor Signature') . ':', $border, 0, 'R');
        $this->pdf->Cell(60 + $buffer, 5, '_____________________________', $border, 0, 'C');

        $this->pdf->Ln();
        $this->pdf->Cell(40 + $buffer, 5, '', $border, 0, 'R');
        $this->pdf->Cell(60 + $buffer, 5, $user_data['first_name'] . ' ' . $user_data['last_name'], $border, 0, 'C');

        $this->pdf->Ln();
        $this->pdf->Cell(140 + ($buffer * 3), 5, '', $border, 0, 'R');
        $this->pdf->Cell(60 + $buffer, 5, '_____________________________', $border, 0, 'C');

        $this->pdf->Ln();
        $this->pdf->Cell(140 + ($buffer * 3), 5, '', $border, 0, 'R');
        $this->pdf->Cell(60 + $buffer, 5, ('(print name)'), $border, 0, 'C');

        return TRUE;
    }

    //function timesheetFooter( $pdf_created_date, $adjust_x, $adjust_y ) {
    function timesheetFooter() {
        $margins = $this->pdf->getMargins();

        $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8));

        //Save x,y and restore after footer is set.
        $x = $this->pdf->getX();
        $y = $this->pdf->getY();

        //Jump to end of page.
        $this->pdf->setY($this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 10);

        $this->pdf->Cell(($this->pdf->getPageWidth() - $margins['right']), $this->_pdf_fontSize(5), ('Page') . ' ' . $this->pdf->PageNo() . ' of ' . $this->pdf->getAliasNbPages(), 0, 0, 'C', 0);
        $this->pdf->Ln();

        $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(6));
        $this->pdf->Cell(($this->pdf->getPageWidth() - $margins['right']), $this->_pdf_fontSize(5), ('Report Generated By') . ' ' . APPLICATION_NAME . ' v' . APPLICATION_VERSION, 0, 0, 'C', 0);

        $this->pdf->setX($x);
        $this->pdf->setY($y);
        return TRUE;
    }

    function timesheetCheckPageBreak($height, $add_page = TRUE) {
        $margins = $this->pdf->getMargins();

        if (($this->pdf->getY() + $height) > ($this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 10)) {
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

    function _outputPDFTimesheet($format) {
        Debug::Text(' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10);

        $border = 0;

        $current_company = $this->getUserObject()->getCompanyObject();
        if (!is_object($current_company)) {
            Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__, 10);
            return FALSE;
        }

        $pdf_created_date = time();

        $adjust_x = 10;
        $adjust_y = 10;

        //Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__,10);
        if (isset($this->form_data) AND count($this->form_data) > 0) {

            $this->pdf = new TTPDF($this->config['other']['page_orientation'], 'mm', $this->config['other']['page_format']);

            $this->pdf->SetCreator(APPLICATION_NAME);
            $this->pdf->SetAuthor(APPLICATION_NAME);
            $this->pdf->SetTitle($this->title);
            $this->pdf->SetSubject(APPLICATION_NAME . ' ' . ('Report'));

            $this->pdf->setMargins($this->config['other']['left_margin'], $this->config['other']['top_margin'], $this->config['other']['right_margin']);
            //Debug::Arr($this->config['other'], 'Margins: ', __FILE__, __LINE__, __METHOD__,10);

            $this->pdf->SetAutoPageBreak(FALSE);

            $this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(10));

            //Debug::Arr($this->form_data, 'zabUser Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

            $filter_data = $this->getFilterConfig();

            $plf = TTnew('PunchListFactory');
            $plf->getSearchByCompanyIdAndArrayCriteria($this->getUserObject()->getCompany(), $filter_data);
            if ($plf->getRecordCount() > 0) {
                foreach ($plf as $p_obj) {
                    $this->form_data[$p_obj->getColumn('user_id')]['punch_rows'][$p_obj->getColumn('pay_period_id')][TTDate::strtotime($p_obj->getColumn('date_stamp'))][$p_obj->getPunchControlID()][$p_obj->getStatus()] = array('status_id' => $p_obj->getStatus(), 'type_id' => $p_obj->getType(), 'type_code' => $p_obj->getTypeCode(), 'time_stamp' => $p_obj->getTimeStamp());
                }
            }
            unset($plf, $p_obj);

            foreach ($this->form_data as $user_data) {
                $this->pdf->AddPage($this->config['other']['page_orientation'], 'Letter');

                $this->timesheetHeader($user_data);

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


                if (isset($user_data['data']) AND is_array($user_data['data'])) {
                    $user_data['data'] = Sort::arrayMultiSort($user_data['data'], array('time_stamp' => SORT_ASC));

                    $this->timesheet_week_totals = Misc::preSetArrayValues(NULL, array('worked_time', 'absence_time', 'regular_time', 'over_time'), 0);
                    $this->timesheet_totals = array();
                    $this->timesheet_totals = Misc::preSetArrayValues($this->timesheet_totals, array('worked_time', 'absence_time', 'regular_time', 'over_time'), 0);

                    $this->counter_i = 1; //Overall row counter.
                    $this->counter_x = 1; //Row counter, starts over each week.
                    $this->counter_y = 1; //Week counter.
                    $max_i = count($user_data['data']);
                    $prev_data = FALSE;
                    foreach ($user_data['data'] as $data) {
                        //Debug::Arr($data, 'Data: i: '. $this->counter_i .' x: '. $this->counter_x, __FILE__, __LINE__, __METHOD__,10);
                        $data = Misc::preSetArrayValues($data, array('time_stamp', 'in_punch_time_stamp', 'out_punch_time_stamp', 'worked_time', 'absence_time', 'regular_time', 'over_time'), '--');

                        $row_date_gap = ($prev_data !== FALSE ) ? (TTDate::getMiddleDayEpoch($data['time_stamp']) - TTDate::getMiddleDayEpoch($prev_data['time_stamp'])) : 0; //Take into account DST by using mid-day epochs.
                        Debug::Text('Row Gap: ' . $row_date_gap, __FILE__, __LINE__, __METHOD__, 10);
                        if ($prev_data !== FALSE AND $row_date_gap > (86400)) {
                            Debug::Text('FOUND GAP IN DAYS!', __FILE__, __LINE__, __METHOD__, 10);

                            for ($d = TTDate::getBeginDayEpoch($prev_data['time_stamp']) + 86400; $d < $data['time_stamp']; $d += 86400) {
                                $blank_row_time_stamp = TTDate::getBeginDayEpoch($d);
                                Debug::Text('Blank row timestamp: ' . TTDate::getDate('DATE+TIME', $blank_row_time_stamp) . ' Pay Period Start Date: ' . TTDate::getDate('DATE+TIME', $prev_data['pay_period_start_date']), __FILE__, __LINE__, __METHOD__, 10);
                                if ($blank_row_time_stamp >= $prev_data['pay_period_end_date']) {
                                    Debug::Text('aBlank row timestamp: ' . TTDate::getDate('DATE+TIME', $blank_row_time_stamp) . ' Pay Period Start Date: ' . TTDate::getDate('DATE+TIME', $prev_data['pay_period_start_date']), __FILE__, __LINE__, __METHOD__, 10);
                                    $pay_period_id = $data['pay_period_id'];
                                    $pay_period_start_date = $data['pay_period_start_date'];
                                    $pay_period_end_date = $data['pay_period_end_date'];
                                    $pay_period = $data['pay_period'];
                                } else {
                                    Debug::Text('bBlank row timestamp: ' . TTDate::getDate('DATE+TIME', $blank_row_time_stamp) . ' Pay Period Start Date: ' . TTDate::getDate('DATE+TIME', $prev_data['pay_period_start_date']), __FILE__, __LINE__, __METHOD__, 10);
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
                                $this->timesheetDayRow($format, $column_widths, $user_data, $blank_row_data, $prev_data, $max_i); //Prev data is actually the current data for a blank row.

                                unset($blank_row_time_stamp, $pay_period_id, $pay_period_start_date, $pay_period_end_date, $pay_period);
                            }
                            $prev_data = $blank_row_data; //Make sure we set the last blank_row as the prev_data going forward.
                            unset($blank_row_data);
                        }
                        $this->timesheetDayRow($format, $column_widths, $user_data, $data, $prev_data, $max_i);

                        $prev_data = $data;
                    }

                    if (isset($this->timesheet_totals) AND is_array($this->timesheet_totals)) {
                        //Display overall totals.
                        $this->timesheetTotal($column_widths, $this->timesheet_totals);
                        unset($totals);
                    }

                    unset($data);
                }

                $this->timesheetSignature($user_data);

                $this->timesheetFooter($pdf_created_date, $adjust_x, $adjust_y);
            }

            $output = $this->pdf->Output('', 'S');

            return $output;
        }

        Debug::Text('No data to return...', __FILE__, __LINE__, __METHOD__, 10);
        return FALSE;
    }

    function _output($format = NULL) {
        if ($format == 'pdf_timesheet' OR $format == 'pdf_timesheet_print'
                OR $format == 'pdf_timesheet_detail' OR $format == 'pdf_timesheet_detail_print') {
            return $this->_outputPDFTimesheet($format);
        } else {
            return parent::_output($format);
        }
    }

    //FL ADDED FOR LATE DETAIL REPORT (National PVC) 20160517
    function OTDetailReport($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }



        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        /* while( $current <= $last ) {

          $dates[] = date('d', $current);
          $current = strtotime('+1 day', $current);
          } */


        $date = date('d', $pay_period_start);
        $current = strtotime('+1 day', $current);

//            echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Daily OT Report',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'payperiod_end_date' => date('Y-M-d', $pay_period_start),
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 52, 23);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $pdf->AddPage('P', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            $pdf->SetFont('', 'B', 10);

            $html = '';
            //Header
            // create some HTML content
            $html = $html . '<br><br><br><table border="0" cellspacing="0" cellpadding="0" width="100%">';
            $html = $html . '<tr style="background-color:#CCCCCC;text-align:center;">';
            $html = $html . '<td> ' . date('Y/M/d', $pay_period_start) . ' </td>';
            $html = $html . '</tr>';


            $html = $html . '<thead><tr style="background-color:#CCCCCC;text-align:center;" height="25">';
            $html = $html . '<td width="20%">EPF Np.</td>';
            $html = $html . '<td width="50%">Employee Name</td>';
            $html = $html . '<td width="30%">OT</td>';
            $html = $html . '</tr></thead>';

            $html = $html . '<tbody>';

            $pdf->SetFont('', '', 10);

            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows);

            $x = 1;
            $dayArray = 0;


            foreach ($rows as $row) {


                //create Array by date day
                $row_data_day_key = array();
                $tot_ot_hours = $tot_ot_hours_in_sec = 0;
                foreach ($row['data'] as $row1) {

                    //echo '<pre>'; //print_r($row1);


                    if ($row1['date_stamp'] != '') {
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d', strtotime($row_dt));
                        $row_data_day_key[$dat_day] = $row1;

                        //get total time calculation

                        if (isset($row1['over_time'])) {
                            $ot_hm = explode(':', $row1['over_time']);
                            //                            var_dump($ot_hm); die;
                            $ot_in_sec = $ot_hm[0] * 60 * 60 + $ot_hm[1] * 60;
                            $tot_ot_hours_in_sec = $tot_ot_hours_in_sec + $ot_in_sec;
                        }
                        //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                    } else {
                        $tot_ot_hours_data = $row1['over_time'];
                    }
                }


                if (isset($row_data_day_key[$date]['over_time'])) {
                    if ($x % 2 == 0) {
                        $html = $html . '<tr bgcolor="#EEEEEE" nobr="true">';
                    } else {
                        $html = $html . '<tr bgcolor="WHITE" nobr="true">';
                    }
                    /* $html = $html.'<td>
                      <table>
                      <tr style ="text-align:left">
                      <td width="20%">'.$row['employee_number'].'</td>
                      <td width="80%">'.$row['first_name'].' '.$row['last_name'].'</td>
                      </tr>
                      <tr style ="text-align:left ">
                      <td>Total OT</td>
                      <td>'.$tot_ot_hours_data.'</td>
                      </tr>
                      </table>
                      </td>'; */

                    $html = $html . '<td style ="text-align:center;" width="20%">' . $row['employee_number'] . '</td>
                                      <td style ="text-align:left;" width="50%">' . $row['first_name'] . ' ' . $row['last_name'] . '</td>
                                         ';
                    if (isset($row_data_day_key[$date]['over_time'])) {
                        $html = $html . '<td  style ="text-align:center;" width="30%">' . $row_data_day_key[$date]['over_time'] . '</td>';
                    } else {
                        $html = $html . '<td style ="text-align:left;"  width="30%">--</td>';
                    }

                    $x++;

                    $html = $html . '</tr>';

                    unset($row_data_day_key);
                }
            }

            $html = $html . '</tbody>';
            //$html = $html.'</tbody>';
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

            $html = $html . '</table>'; //echo $html; 
            //            
            //die;
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  

            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    function DailyAbsenceReport($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {

        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }

        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        //echo '<pre>'; print_r($data); echo '<pre>';  die;
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        while ($current <= $last) {
            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";

        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Head Count & Absence Report',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'payperiod_end_date' => date('Y-M-d', $pay_period_start),
                'line_width' => 258,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 50, 23);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $pdf->AddPage('L', 'mm', 'A4');


            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '<br><br><br><table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
            $html = $html . '<td height="17" width = "5%">#</td>';
            $html = $html . '<td width="10%">Emp. No.</td>';
            $html = $html . '<td width="55%">Emp. Name</td>';
            // $html = $html.'<td width="8%">Present</td>';
            $html = $html . '<td width="16%">Absence</td>';
            //  $html = $html.'<td width="20%">Status</td>';
            $html = $html . '</tr></thead>';

            $pdf->SetFont('', '', 8);

            $x = 1;
            $nof_emp = 0;

            $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

            $html = $html . '<tbody>';

            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows);


            foreach ($rows as $row) {



                $udlf = TTnew('UserDateListFactory');
                $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', $pay_period_start));
                $udlf_obj = $udlf->getCurrent();
                $user_date_id = $udlf_obj->getId();

                $slf = TTnew('ScheduleListFactory');
                $slf->getByUserDateId($user_date_id);
                $slf_obj_arr = $slf->getCurrent()->data;

                // if(!empty($slf_obj_arr))
                // {
                $pclf = TTnew('PunchControlListFactory');
                $pclf->getByUserDateId($user_date_id); //par - user_date_id
                $pc_obj_arr = $pclf->getCurrent()->data;

                $present_mark = '';
                $absence_mark = '';
                $leaveStaus = '';


                // var_dump($pc_obj_arr);
                // exit();
                // if(empty($pc_obj_arr))

                if (empty($pc_obj_arr)) {
                    //$present_mark = '&#x2713;';

                    $absence_mark = 'AB';

                    if ($row['employee_number'] != 99999) {
                        if ($x % 2 == 0) {
                            $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        } else {
                            $html = $html . '<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                        }

                        $html = $html . '<td  width = "5%" height="25">' . $x . '</td>';
                        $html = $html . '<td width = "10%">' . $row['employee_number'] . '</td>';
                        $html = $html . '<td width = "55%" align="left">' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
                        // $html = $html.'<td width = "8%" align="center">'.$present_mark.'</td>';
                        $html = $html . '<td width = "16%" align="center">' . $absence_mark . '</td>';
                        // $html = $html.'<td width = "20%" align="center">'.$leaveStaus.'</td>';
                        $html = $html . '</tr>';

                        $nof_emp++;
                        $x++;
                    }
                } else {
                    // $absence_mark = '&#x2717;';
                    $present_mark = '*';

                    $leaveStaus = '';

                    $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                    $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                    $absLeave_obj_arr = $aluelf->getCurrent()->data;


                    if (!empty($absLeave_obj_arr)) {
                        $leaveStaus = $absLeave_obj_arr['absence_name'];
                    } else {
                        $leaveStaus = 'Unscheduled Absence.';
                    }
                }

                //}                     
                // }
            }//die;


            $html = $html . '</tbody>';
            $html = $html . '</table>';

            $html = $html . '
                                <table width="943" border="0">
                                    <tr><td align="center"></td></tr>
                                    <tr><td align="Left">Total No of Employees : ' . $nof_emp . '</td></tr>
                                    <tr><td align="Left">Date : ' . date('Y-M-d', $pay_period_start) . '</td></tr>
                                    <tr><td align="Left"></td></tr>
                                </table>';


            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  

            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    //FL ADDED FOR Daily Late (National PVC) 20160517
    //FL ADDED FOR Daily Late (National PVC) 20160517
    function DailyLateReport($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                //      $br_strng = implode(', ', $branch_list);
            }

            $br_strng = $blf->getNameById($br_id);
            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

//                echo '<pre>'; print_r($data); echo '<pre>';  die;
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        while ($current <= $last) {
            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";

        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Late Attendance And Early Departures',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'payperiod_end_date' => date('Y-M-d', $pay_period_start),
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 55, 23);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.30);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '<br><br><br><table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead><tr style="background-color:#CCCCCC;text-align:center;" >';
//                $html = $html.'<td width = "3%">#</td>';
            $html = $html . '<td height="17" width = "5%">#</td>';
            $html = $html . '<td width = "10%">Emp. No.</td>';
            $html = $html . '<td align="left" width = "35%">Emp. Name</td>';
            // $html = $html.'<td width = "5%">Shift</td>';
            $html = $html . '<td width = "10%"> In</td>';
            // $html = $html.'<td width = "5%">Break In</td>';
            // $html = $html.'<td width = "5%">Break Out</td>';
            $html = $html . '<td width = "10%">Out</td>';
            //$html = $html.'<td width = "10%">Hours Worked</td>';
            $html = $html . '<td width = "10%">Late Attendance</td>';
            $html = $html . '<td width = "10%">Early departure</td>';
            //  $html = $html.'<td width = "5%">OT</td>';
            //  $html = $html.'<td width = "5%">Loss</td>';
            //  $html = $html.'<td width = "5%">Status</td>';
            //$html = $html.'<td width = "5%">Covered</td>'; 

            $pdf->SetFont('', 'B', 7);


            $html = $html . '</tr></thead>';

            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows);

            $pdf->SetFont('', '', 7);

            $x = 1;
            $dayArray = 0;
            $tot_work_hours = $tot_work_hours_in_sec = 0;

            $html = $html . '<tbody>';

            $late_emp = 0;

            foreach ($rows as $row) {

                $EmpDateStatus = $this->getReportStatusByUserIdAndDate($row['user_id'], date('Y-m-d', $pay_period_start));
//                    echo'<pre>'; print_r($EmpDateStatus); die;  

                $udlf = TTnew('UserDateListFactory');

                $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', $pay_period_start));
                $udlf_obj = $udlf->getCurrent();
                $user_date_id = $udlf_obj->getId();

                $elf = TTnew('ExceptionListFactory');
                $elf->getByUserDateId($user_date_id);
                foreach ($elf as $elf_obj) {
                    if ($elf_obj->getExceptionPolicyId() == '5') {
                        $status2 = 'ED'; //Early Departure
                        $all_status['status2_all'] .= ' ED';
                    }
                    if ($elf_obj->getExceptionPolicyId() == '4') {
                        $status2 = 'LP'; //Late Presents
                        $all_status['status2_all'] .= ' LP';
                    }
                    if ($elf_obj->getExceptionPolicyId() == '12' || $elf_obj->getExceptionPolicyId() == '13') {
                        $status2 = 'MIS'; //Missed Punch
                        $all_status['status2_all'] .= ' MIS';
                    }
                    if ($elf_obj->getExceptionPolicyId() == '1') {
                        $status2 = 'A'; //Unscheduled absent
                        $all_status['status2_all'] .= ' A';
                    }
                }

                $udtlf = TTnew('UserDateTotalListFactory');


                //create Array by date day
                $row_data_day_key = array();
                $tot_ot_hours = $tot_ot_hours_in_sec = 0;
                foreach ($row['data'] as $row1) {
                    $ot_in_sec = $work_in_sec = 0;
                    if ($row1['date_stamp'] != '') {
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d', strtotime($row_dt));
                        $row_data_day_key[$dat_day] = $row1;

                        $late = '';
                        if ($row_data_day_key[$dat_day]['min_punch_time_stamp'] != '' && $row_data_day_key[$dat_day]['shedule_start_time'] != '') {
                            $lateSec = strtotime($row_data_day_key[$dat_day]['shedule_start_time']) - strtotime($row_data_day_key[$dat_day]['min_punch_time_stamp']);
                            //echo '<br>lateSec....'.$lateSec;
                            if ($lateSec < 0) {
                                //$lossSec = $lossSec + abs($lateSec);
                                $late = gmdate("H:i", abs($lateSec));
                                $status_str .= 'LP ';
                            }
                        }


                        if ($late != '') {
                            //get total time calculation 
                            if (isset($row1['over_time']) && $row1['over_time'] != '') {
                                $ot_hm = explode(':', $row1['over_time']);
                                $ot_in_sec = $ot_hm[0] * 60 * 60 + $ot_hm[1] * 60;
                                $tot_ot_hours_in_sec = $tot_ot_hours_in_sec + $ot_in_sec;
                            }

                            if (isset($row1['worked_time']) && $row1['worked_time'] != '') {

                                $work_hm = explode(':', $row1['worked_time']);
                                $work_in_sec = $work_hm[0] * 60 * 60 + $work_hm[1] * 60;
                                $tot_work_hours_in_sec = $tot_work_hours_in_sec + $work_in_sec;
                            }
                        }
                    }
                }
//                $tot_ot_hours = gmdate("H:i", $tot_ot_hours_in_sec);
                $tot_ot_hours = floor($tot_ot_hours_in_sec / 3600) . ':' . floor($tot_ot_hours_in_sec / 60 % 60);

                //                $tot_work_hours = gmdate("H:i", $tot_work_hours_in_sec);
                $tot_work_hours = floor($tot_work_hours_in_sec / 3600) . ':' . floor($tot_work_hours_in_sec / 60 % 60);

                //echo '<pre>'; print_r($dates); die;


                $late = '';
                foreach ($dates as $date) {
                    
                    $row_date = str_replace('/', '-', $row_data_day_key[$date]['date_stamp']);
                   // echo $row_dt;
                    
//echo $date.$row['employee_number'].'<br><pre>';print_r($row_data_day_key);exit;



                    if ($row_data_day_key[$date]['min_punch_time_stamp'] != '' && $row_data_day_key[$date]['shedule_start_time'] != '') {
                        $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - strtotime($row_data_day_key[$date]['min_punch_time_stamp']);
                        //  echo $row_data_day_key[$date]['shedule_start_time'].'lateSec....'.$lateSec.'  '.$row_data_day_key[$date]['min_punch_time_stamp'].'<br>';
                        if ($lateSec < 0) {
                            //$lossSec = $lossSec + abs($lateSec);
                            $late = gmdate("H:i", abs($lateSec));

                            $status_str .= 'LP ';
                        }
                    }
                }


                if ($late != '') {
                    $late_emp++;

                    if ($x % 2 == 0) {
                        $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                    } else {
                        $html = $html . '<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                    }


                    foreach ($dates as $date) {
                        $lossSec = 0;
                        //                        echo '<pre>'; print_r($row_data_day_key); echo '<pre>'; die;
                        //STATUS COULUMN VALUE SETUP
                        $status_str = '';
                        $status_str .= ($row_data_day_key[$date]['min_punch_time_stamp'] == '' && $row_data_day_key[$date]['max_punch_time_stamp'] == '') ? 'A ' : 'P ';

                    $hlf = TTnew('HolidayListFactory');
                    $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($row_date)));
                    $hday_obj_arr = $hlf->getCurrent()->data;

                    if (empty($hday_obj_arr)) {
                        $status1 = 'HLD';
                   

                        if ($row_data_day_key[$date]['min_punch_time_stamp'] != '' && $row_data_day_key[$date]['shedule_start_time'] != '') {
                            $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - strtotime($row_data_day_key[$date]['min_punch_time_stamp']);
                            if ($lateSec < 0) {
                                $lossSec = $lossSec + abs($lateSec);
                                $late = gmdate("H:i", abs($lateSec));
                                $status_str .= 'LP ';
                            }
                        }

                        $early = '';
                        if ($row_data_day_key[$date]['max_punch_time_stamp'] != '' && $row_data_day_key[$date]['shedule_end_time'] != '') {
                            $earlySec = strtotime($row_data_day_key[$date]['shedule_end_time']) - strtotime($row_data_day_key[$date]['max_punch_time_stamp']);

                            if ($earlySec > 0) {
                                $lossSec = $lossSec + abs($earlySec);
                                $early = gmdate("H:i", abs($earlySec));
                                $status_str .= 'ED ';
                            }
                        }
                        
                        
                        $loss = gmdate("H:i", abs($lossSec));

                        $status_str .= (($row_data_day_key[$date]['min_punch_time_stamp'] == "" || $row_data_day_key[$date]['max_punch_time_stamp'] == "") && !($row_data_day_key[$date]['min_punch_time_stamp'] == "" && $row_data_day_key[$date]['max_punch_time_stamp'] == "")) ? 'MIS ' : '';

                        //Columns Setup
                        $shift_column = 'O';
                        $shift_in = ($row_data_day_key[$date]['min_punch_time_stamp'] == '') ? '' : $row_data_day_key[$date]['min_punch_time_stamp'];
                        $shift_out = ($row_data_day_key[$date]['max_punch_time_stamp'] == '') ? '' : $row_data_day_key[$date]['max_punch_time_stamp'];
                        $break_in = '';
                        $break_out = '';
                        $covered = '';

                        $html = $html . '<td height="25" width = "5%">' . $x . '</td>';
                        $html = $html . '<td width = "10%">' . $row['employee_number'] . '</td>';
                        $html = $html . '<td align="left" width = "35%">' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
                        // $html = $html.'<td width = "5%">'.$shift_column.'</td>'; 
                        $html = $html . '<td width = "10%">' . $shift_in . '</td>';
                        //  $html = $html.'<td width = "5%">'.$break_in.'</td>'; 
                        // $html = $html.'<td width = "5%">'.$break_out.'</td>'; 
                        $html = $html . '<td width = "10%">' . $shift_out . '</td>';
                        //$html = $html.'<td width = "10%">'.$row_data_day_key[$date]['worked_time'].'</td>'; 
                        $html = $html . '<td width = "10%">' . $late . '</td>';
                        $html = $html . '<td width = "10%">' . $early . '</td>';
                        //  $html = $html.'<td width = "5%">'.$tot_ot_hours.'</td>'; 
                        //  $html = $html.'<td width = "5%">'.$loss.'</td>'; 
                        //  $html = $html.'<td width = "5%">'.$EmpDateStatus['status1'].' '.$EmpDateStatus['status2_all'].'</td>'; 
                        //$html = $html.'<td width = "5%">'.$covered.'</td>'; 
                      }
                      else{
                          $late_emp--;
                      }
                    }

                    $x++;

                    $html = $html . '</tr>';
                }
            }
            /*
              $html=  $html.'<tr>'
              . '<td colspan="6"><b>Total</b></td>'
              . '<td align="center" ><b>'.$tot_work_hours.'</b></td>'
              . '<td colspan="3" align="center" ><b></b></td>'
              . '</tr>';

             */

            $html = $html . '<tbody>';

            $html = $html . '</table>';

            $html = $html . '
                                <table width="943" border="0">
                                    <tr><td align="center"></td></tr>
                                    <tr><td align="Left">Total No of Employees : ' . $late_emp . '</td></tr>
                                    <tr><td align="Left">Date : ' . date('Y-M-d', $pay_period_start) . '</td></tr>
                                    <tr><td align="Left"></td></tr>
                                </table>';
            /*
              $html = $html.  '
              <table width="943" border="1">
              <tr>
              <td align="center">P- Present / A - Absenteism / LP - Late Presents / MIS - Miss Punch / POH - Present On Holiday / HLD - Holiday / WO - Week Off / SL - Short Leave </td>
              </tr>
              </table>';
             */

            //die;
            // echo $html;
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  

            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    //---Monthly Leave Taken
    function MonthlyLeaveTakenReport($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }
            $br_strng = $blf->getNameById($br_id);
            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));
        $start_date_year = date('Y', $pay_period_start);
        //echo '<br><pre>';
        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }

        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $new_rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Monthly Leave Detail & Summery Report - Taken',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 280,
                'footer_FDHDSL' => 'FDHDSL',
            );

            $pdf = TTnew('TimeReportHeaderFooter');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 50, 23);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';

            $html = $html . '</tr>';

            $pdf->SetFont('', 'B', 6.5);


            $row_data_day_key = array();
            $j1 = 0;

            $html = '<br/><br/><br/><table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
            $html = $html . '<td height="17" width = "5%">#</td>';
            $html = $html . '<td width = "10%">Emp. No.</td>';
            $html = $html . '<td width = "25%">Emp. Name</td>';
            $html = $html . '<td width = "15%">Annual Leave</td>';
            $html = $html . '<td width = "15%">Casual Leave</td>';
            $html = $html . '<td width = "15%">Medical Leave</td>';
            $html = $html . '<td width = "15%">Short Leave</td>';
            $html = $html . '</tr></thead>';

            $html = $html . '<tbody>';

            $pdf->SetFont('', '', 8);


            foreach ($new_rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            // Sort the data with volume descending, edition ascending
            // Add $data as the last parameter, to sort by the common key
            array_multisort($employee_number, SORT_ASC, $new_rows);

            $x = 1;
            foreach ($new_rows as $data_vl) {

                //Get all accrual policies.
                $ulf = TTnew('UserListFactory');
                $aplf = TTnew('AbsencePolicyListFactory');
                $aplf->getByCompanyId($current_company->getId());
                if ($aplf->getRecordCount() > 0) {
                    foreach ($aplf as $ap_obj) {
                        $ap_columns['absence_policy-' . $ap_obj->getId()] = $ap_obj->getName();
                    }

                    $columns = array_merge($columns, $ap_columns);
                }


                $ablf = TTnew('AccrualBalanceListFactory');
                $ablf->getByUserIdAndCompanyId($data_vl['user_id'], $current_company->getId());

                $total_balance_leave_all = array('full_day' => 0, 'half_day' => 0, 'short_leave' => 0);


                foreach ($columns as $column_abs => $column_abs_vl) {
                    //foreach ($filter_data['column_ids'] as $column_abs ){

                    $$absence_policy_id = '';
                    $absence_policy_id_array = array('1', '2', '3'); //Annual/casual/Sick leave IDs
                    $colAbs_arr = explode('-', $column_abs);

                    //&& in_array($colAbs_arr[1], $absence_policy_id_array)
                    if ($colAbs_arr[0] == 'absence_policy' && in_array($colAbs_arr[1], $absence_policy_id_array)) {
                        $absence_policy_id = $colAbs_arr[1];

                        $udlf = TTnew('UserDateListFactory');
                        $total_used_leaves = 0;
                        for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {

                            $udlf->getByUserIdAndDate($data_vl['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                            $udlf_obj = $udlf->getCurrent();

                            //get used Leave for particular date year
                            $aluerlf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                            //$aluerlf->getByAbsencePolicyIdAndUserId2($absence_policy_id,$row['user_id']);
                            $aluerlf->getgetAbsenceLeaveIdByAbsencePolicyIdAndUserIdUserDateId($absence_policy_id, $data_vl['user_id'], $udlf_obj->getId());

                            if (count($aluerlf) > 0) {
                                $allf1 = TTnew('AbsenceLeaveListFactory');
                                foreach ($aluerlf as $aluerlf_obj) {

                                    $leave_taken[$column_abs][$aluerlf_obj->getAbsenceLeaveId()] += 1;
                                }
                            }

                            //$total_balance_leave = $total_assigned_leaves - $total_used_leaves;
                        }

                        $allf = TTnew('AbsenceLeaveListFactory');

                        $allf->getAll();

                        foreach ($allf as $allf_obj) {
                            $absence_leave[$allf_obj->getId()] = $allf_obj;
                        }



                        if (empty($leave_taken[$column_abs][1])) {
                            $leave_taken[$column_abs][1] = '0';
                        }
                        if (empty($leave_taken[$column_abs][2])) {
                            $leave_taken[$column_abs][2] = '0';
                        }
                        if (empty($leave_taken[$column_abs][3])) {
                            $leave_taken[$column_abs][3] = '0';
                        }

                        $taken[$column_abs] = $absence_leave[1]->getShortCode() .
                                ' - ' . $leave_taken[$column_abs][1] .
                                ' | ' . $absence_leave[2]->getShortCode() .
                                ' - ' . $leave_taken[$column_abs][2];

                        /* .
                          ' | '.$absence_leave[3]->getShortCode().
                          ' - '.$leave_taken[$column_abs][3]; */

                        $taken['short_leave'] = $leave_taken[$column_abs][3];
                    }
                }

                $user_obj = $ulf->getById($data_vl['user_id'])->getCurrent();


                if ($x % 2 == 0) {
                    $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                } else {
                    $html = $html . '<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                }


                $html = $html . '<td  width = "5%" height="25">' . $x . '</td>';
                $html = $html . '<td  width = "10%">' . $user_obj->getEmployeeNumber() . '</td>';
                $html = $html . '<td  width = "25%" align="left">' . $user_obj->getFirstName() . ' ' . $user_obj->getLastName() . '</td>';
                $html = $html . '<td width = "15%" align="center">' . $taken['absence_policy-1'] . '</td>';
                $html = $html . '<td width = "15%" align="center">' . $taken['absence_policy-2'] . '</td>';
                $html = $html . '<td width = "15%" align="center">' . $taken['absence_policy-3'] . '</td>';
                $html = $html . '<td width = "15%" align="center">' . $taken['short_leave'] . '</td>';
                $html = $html . '</tr>';

                $x++;

                unset($leave_taken);
            }

            //echo '<pre>'; print_r($leave_taken);
            //die;
            //echo '<pre>'; print_r($rows); die;
            $html = $html . '</tbody>';
            $html = $html . '';
            $html = $html . '</table>';

            //  echo $html;
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    //--MOnthly Leave Taken
    //---Monthly Leave Balance
    function MonthlyLeavebalanceReport($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }
            $br_strng = $blf->getNameById($br_id);
            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }


            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }


        if ($dep_strng == '') {
            $dep_strng = 'All';
        }


        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));
        $start_date_year = date('Y', $pay_period_start);

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }

        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $new_rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Monthly Leave Detail & Summery Report - Balance',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 50, 23);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';

            $html = $html . '</tr>';

            $pdf->SetFont('', 'B', 6.5);


            $row_data_day_key = array();
            $j1 = 0;

            $html = '<br/><br/><br/><table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
            $html = $html . '<td height="17" width = "5%">#</td>';
            $html = $html . '<td width = "10%">Emp. No.</td>';
            $html = $html . '<td width = "25%">Emp. Name</td>';
            $html = $html . '<td width = "20%">Annual Leave</td>';
            $html = $html . '<td width = "20%">Casual Leave</td>';
            $html = $html . '<td width = "20%">Medical Leave</td>';
            $html = $html . '</tr></thead>';

            $html = $html . '<tbody>';

            $pdf->SetFont('', '', 8);

            foreach ($new_rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $new_rows);


            $x = 1;
            foreach ($new_rows as $data_vl) {

                //Get all accrual policies.
                $ulf = TTnew('UserListFactory');
                $aplf = TTnew('AbsencePolicyListFactory');
                $aplf->getByCompanyId($current_company->getId());
                if ($aplf->getRecordCount() > 0) {
                    foreach ($aplf as $ap_obj) {
                        $ap_columns['absence_policy-' . $ap_obj->getId()] = $ap_obj->getName();
                    }

                    $columns = array_merge($columns, $ap_columns);
                }


                $ablf = TTnew('AccrualBalanceListFactory');
                $ablf->getByUserIdAndCompanyId($data_vl['user_id'], $current_company->getId());

                $total_balance_leave_all = array('full_day' => 0, 'half_day' => 0, 'short_leave' => 0);

                foreach ($columns as $column_abs => $column_abs_vl) {
                    //foreach ($filter_data['column_ids'] as $column_abs ){

                    $$absence_policy_id = '';
                    $absence_policy_id_array = array('1', '2', '3');
                    $colAbs_arr = explode('-', $column_abs);

                    //&& in_array($colAbs_arr[1], $absence_policy_id_array)
                    if ($colAbs_arr[0] == 'absence_policy' && in_array($colAbs_arr[1], $absence_policy_id_array)) {
                        $absence_policy_id = $colAbs_arr[1];

                        //get total leaves for particular date year 
                        $alulf = TTnew('AbsenceLeaveUserListFactory');

                        $alulf->getEmployeeTotalLeaves($absence_policy_id, $data_vl['user_id'], $start_date_year);
                        $total_assigned_leaves = 0;


                        if (count($alulf) > 0) {

                            foreach ($alulf as $alulf_obj) {
                                $total_assigned_leaves = $total_assigned_leaves + $alulf_obj->getAmount();
                            }
                            $total_assigned_leaves_indays[$column_abs] = $total_assigned_leaves / (60 * 60 * 8);
                        }


                        $udlf = TTnew('UserDateListFactory');
                        $total_used_leaves = 0;
                        for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {

                            $udlf->getByUserIdAndDate($data_vl['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                            $udlf_obj = $udlf->getCurrent();

                            //get used Leave for particular date year
                            $aluerlf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                            //$aluerlf->getByAbsencePolicyIdAndUserId2($absence_policy_id,$row['user_id']);
                            $aluerlf->getgetAbsenceLeaveIdByAbsencePolicyIdAndUserIdUserDateId($absence_policy_id, $data_vl['user_id'], $udlf_obj->getId());

                            if (count($aluerlf) > 0) {
                                $allf1 = TTnew('AbsenceLeaveListFactory');
                                foreach ($aluerlf as $aluerlf_obj) {
                                    $leave_taken[$column_abs][$aluerlf_obj->getAbsenceLeaveId()] += 1;
                                }
                            }
                        }

                        //echo '<pre>'; print_r($leave_taken);


                        $allf = TTnew('AbsenceLeaveListFactory');
                        $allf->getAll();
                        foreach ($allf as $allf_obj) {
                            $absence_leave[$allf_obj->getId()] = $allf_obj;
                        }


                        if (empty($leave_taken[$column_abs][1])) {
                            $leave_taken[$column_abs][1] = '0';
                        }
                        if (empty($leave_taken[$column_abs][2])) {
                            $leave_taken[$column_abs][2] = '0';
                        }
                        if (empty($leave_taken[$column_abs][3])) {
                            $leave_taken[$column_abs][3] = '0';
                        }


                        $full_days_from_half_days = 0;
                        if ($leave_taken[$column_abs][2] > 0) {
                            //If odd number of halfdays taken, display balance halfday which is 1 
                            if ($leave_taken[$column_abs][2] % 2 == 1) {
                                $leave_balance[$column_abs][2] = 1;
                                $full_days_from_half_days = (int) ($leave_taken[$column_abs][2] / 2) + 1;
                            } else {
                                //If even number of halfdays taken, consider 2 halfdays as one fullday
                                $full_days_from_half_days = (int) ($leave_taken[$column_abs][2] / 2);
                            }
                        }
                        $leave_balance[$column_abs][1] = $total_assigned_leaves_indays[$column_abs] - ($leave_taken[$column_abs][1] + $full_days_from_half_days);


                        if (empty($leave_balance[$column_abs][1]) || $leave_balance[$column_abs][1] < 0) {
                            $leave_balance[$column_abs][1] = '0';
                        }
                        if (empty($leave_balance[$column_abs][2]) || $leave_balance[$column_abs][2] < 0) {
                            $leave_balance[$column_abs][2] = '0';
                        }
                        if (empty($leave_balance[$column_abs][3]) || $leave_balance[$column_abs][3] < 0) {
                            $leave_balance[$column_abs][3] = '0';
                        }



                        $balance[$column_abs] = $absence_leave[1]->getShortCode() .
                                ' - ' . $leave_balance[$column_abs][1] .
                                ' | ' . $absence_leave[2]->getShortCode() .
                                ' - ' . $leave_balance[$column_abs][2];
                    }
                }
                //echo '<pre>'; print_r($total_assigned_leaves_indays); print_r($leave_balance);print_r($leave_taken);
                $user_obj = $ulf->getById($data_vl['user_id'])->getCurrent();


                if ($x % 2 == 0) {
                    $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                } else {
                    $html = $html . '<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                }


                $html = $html . '<td  width = "5%" height="25">' . $x . '</td>';
                $html = $html . '<td  width = "10%">' . $user_obj->getEmployeeNumber() . '</td>';
                $html = $html . '<td  width = "25%" align="left">' . $user_obj->getFirstName() . ' ' . $user_obj->getLastName() . '</td>';
                $html = $html . '<td width = "20%" align="left">' . $balance['absence_policy-1'] . '</td>';
                $html = $html . '<td width = "20%" align="left">' . $balance['absence_policy-2'] . '</td>';
                $html = $html . '<td width = "20%" align="left">' . $balance['absence_policy-3'] . '</td>';
                $html = $html . '</tr>';

                $x++;

                unset($leave_taken);
                unset($leave_balance);
                //unset();
            }
            //print_r($total_assigned_leaves_indays);
            //die;
            //echo '<pre>'; print_r($rows);
            $html = $html . '</tbody>';
            $html = $html . '</table>';

            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    //--MOnthly Leave Balance
    //---Start MissingPunchReport
           //---Start MissingPunchReport
    function MissingPunchReport($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
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

                     $br_strng =  $blf->getNameById($br_id); //eranda add code dynamic header data report

                        if($br_strng == null)
                        {
                                $company_name = $current_company->getName();
                                $addrss1 = $current_company->getAddress1();
                                $address2 = $current_company->getAddress2();
                                $city = $current_company->getCity();
                                $postalcode = $current_company->getPostalCode();
                        }else
                        {
                                $company_name = $blf->getNameById($br_id);
                                $addrss1 = $blf->getAddress1ById($br_id);
                                $address2 = $blf->getAddress2ById($br_id);
                                $city = $blf->getCityById($br_id);
                                $postalcode = $blf->getPostCodeById($br_id);

                        }
                    //    echo "<pre>"; print_r($blf->getNameById($br_id)); die;
                    $uglf = TTnew( 'UserGroupListFactory' ); 
                    if($fh_key == 'group_ids'){
                        foreach ($filter_header as $gr_id) {   
                            $group_list[] = $uglf->getNameById($gr_id); 
                        }
                        $gr_strng = implode(', ', $group_list);
                    }
                                
                } 
                if($dep_strng==''){$dep_strng='All';}

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

                $list_start_date = date('d',$pay_period_start);
                $list_end_date = date('d',$pay_period_end);

/*
                while( $current <= $last ) {

                    $dates[] = date('d', $current); 
                    $current = strtotime('+1 day', $current);
                    
                   
                }
                $dates[] = date('d', $current); 
                
                */
                
               $j = 0;
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);             
                $dates[$j]['date_actual'] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }
            
                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date_actual'] = date('Y-m-d', $current); 
                
         //  echo '<pre>'; print_r($dates); echo '<pre>'; die;
            
            
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
                                                  'payperiod_end_date'   => date('Y-M-d',$pay_period_start).' - '.date('Y-M-d',$pay_period_end),
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $company_name,
                                                  'address1'     => $addrss1,
                                                  'address2'     => $address2,
                                                  'city'         => $city,
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $postalcode,
                                                  'heading'  => 'Missing Punch Report', 
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
                $pdf->SetMargins(PDF_MARGIN_LEFT, 46, 23);
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
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(50, $adjust_y) );
                                           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '';


                /*foreach ($data as $key => $row) {
                    $volume[$key]  = $row['volume'];
                    $edition[$key] = $row['edition'];
                }

                // Sort the data with volume descending, edition ascending
                // Add $data as the last parameter, to sort by the common key
                array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data);*/

                //Sort array by employee_number
                foreach ($rows as $key => $row) {
                    $employee_number[$key]  = $row['employee_number'];
                }

                array_multisort($employee_number, SORT_ASC, $rows);/**/

                
                $pdf->SetFont('', 'B',6.5); 
                
                
                $row_data_day_key = array(); 
                $j1 = 0;
                foreach ($rows as $row){

                    if(!empty($row['data']))
                    {

                        $html_new = $html_new.'<br/><table border="0" cellspacing="0" cellpadding="0" width="100%">';
                        /*$html_new = $html_new.'<tr style="background-color:#CCCCCC;text-align:center;" >';
                        $html_new = $html_new.'<td width="40"> </td>';
                        $html_new = $html_new.'<td  width="80"> </td>';
                        $html_new = $html_new.'<td colspan="6"> </td>';
                        $html_new = $html_new.'<td colspan="3">Present</td>';
                        $html_new = $html_new.'<td colspan="2">Absent</td>';
                        $html_new = $html_new.'<td colspan="2">Leaves</td>';
                        $html_new = $html_new.'<td colspan="2">W. Offs</td>';
                        $html_new = $html_new.'<td colspan="2">Holidays</td>';
                        $html_new = $html_new.'<td colspan="2">OT</td>';
                        $html_new = $html_new.'<td colspan="12"></td>';
                        $html_new = $html_new.'</tr>';*/

                        $present_days = 0; $absent_days = 0; $leave_days = 0; $week_off = 0; $holidays = 0;

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
                                
                            }
                       }

                        
                       // $nof_presence=0; $nof_absence=0; $nof_leaves=0; $nof_weekoffs=0; $nof_holidays=0; $nof_ot=0;

                        $day_row = ''; $shift_id_row = ''; $shift_in_row = ''; $shift_out_row = ''; $late_row = ''; $early_row= ''; $status1_row = ''; $status2_row = '';

                        $earlySec = $lateSec =0;

                       // for($i1=$list_start_date; $i1<=$list_end_date; $i1++){
                        
                        
                        foreach($dates as $i1 ){
                            
                            //---Get Total values
                            $status1 = '';
                            
                            $date_month = date('m-Y',strtotime($i1['date_actual'])); 

                            /*$lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                            $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);*/
                            
                            $udlf = TTnew('UserDateListFactory');
                            $pclf = TTnew('PunchControlListFactory');
                             
                            $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1['day'].'-'.$date_month)));
                            $udlf_obj = $udlf->getCurrent();
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;

                            
                            //if punch exists
                            /*if(!empty($pc_obj_arr)){
                                //$status1 = 'P';  
                                //check late come and early departure
                                //$elf = TTnew('ExceptionListFactory');
                                /*$elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                                $ex_obj_arr = $elf->getCurrent()->data;
                                 if(!empty($ex_obj_arr)){
                                    $status1 = 'LP';
                                }
                            }else{
                                 //$status1 = 'WO'; 
                                 
                                 //$aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
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
                            }*/
                            

                            //$hlf = TTnew('HolidayListFactory');
                            /*$hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1.'-'.$date_month)));
                            $hday_obj_arr = $hlf->getCurrent()->data;

                            if(!empty($hday_obj_arr)){
                            $status1 = 'HLD';  
                            }
                            $tot_array[$status1] += 1;*/
                            //---End Get Total values


                            //---Day row value
                            $day_row = $day_row.'<td>'.$i1['day'].'</td>'; 


                            //---Shift ID row value
                            //$udlf = TTnew('UserDateListFactory');
                            $slf = TTnew('ScheduleListFactory');
                            
                            //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            //$udlf_obj = $udlf->getCurrent();
                            
                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;
                             
                            $schedule_name_arr = explode('-', $sp_obj_arr['shedule_policy_name']); 
                            $status_id = $schedule_name_arr[1]; 
                            $shift_id_row = $shift_id_row.'<td>'.$status_id.'</td>';


                            //---Shift In row value
                            $shift_in_row = $shift_in_row.'<td>'.$row_data_day_key[ $i1['day']]['min_punch_time_stamp'].'</td>'; 



                            //---Shift Out row value
                            $shift_out_row = $shift_out_row.'<td>'.$row_data_day_key[$i1['day']]['max_punch_time_stamp'].'</td>'; 


                            //---Late row value
                            //$udlf = TTnew('UserDateListFactory');
                            //$slf = TTnew('ScheduleListFactory');
                            
                            //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            //$udlf_obj = $udlf->getCurrent();
                            
                            //$slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            //$sp_obj_arr = $slf->getCurrent()->data;

                            /*$late = '';
                            if(!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] != ''){
                                $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                                if($lateSec < 0 ){ 
                                    $late = gmdate("H:i", abs($lateSec))    ; 
                                }
                            } 
                            $late_row = $late_row.'<td>'.$late.'</td>'; */


                            //---Early row value
                            //$udlf = TTnew('UserDateListFactory');
                            //$slf = TTnew('ScheduleListFactory');
                            
                            //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            //$udlf_obj = $udlf->getCurrent();
                            
                            //$slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            //$sp_obj_arr = $slf->getCurrent()->data;

                            /*$early = '';
                            if(!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] != ''){
                                $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                                if($earlySec > 0 ){ 
                                    $early = gmdate("H:i", abs($earlySec)); 
                                }
                            }  
                            $early_row = $early_row.'<td>'.$early.'</td>';*/



                            //---Status 1 row value
                            $status1 = '';
                            $lateSec = strtotime($row_data_day_key[$i1['day']]['shedule_start_time']) - strtotime($row_data_day_key[ $i1['day']]['min_punch_time_stamp']);
                            $earlySec = strtotime($row_data_day_key[ $i1['day']]['shedule_end_time']) - strtotime($row_data_day_key[$i1['day']]['max_punch_time_stamp']);
                            
                            //$udlf = TTnew('UserDateListFactory');
                            //$pclf = TTnew('PunchControlListFactory');
                            $elf = TTnew('ExceptionListFactory'); //--Add code eranda

                            //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            //$udlf_obj = $udlf->getCurrent();
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;

                            $elf->getByUserDateId($udlf_obj->getId());
                            $elf_obj = $elf->getCurrent();

                            //if punch exists
                            if(!empty($pc_obj_arr)){
                                
                                $status1 = 'P';  

                                    if(!empty($elf_obj->data)) {
                                            //  if($epclf_obj->getExceptionPolicyControlID()) {
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
                                
                                 //Check user leaves
                                 $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                                 $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                                 $absLeave_obj_arr = $aluelf->getCurrent()->data;
                                 if(!empty($absLeave_obj_arr)){
                                     $leaveName_arr = explode(' ',$absLeave_obj_arr['absence_name']);
                                     $status1 = substr($leaveName_arr[0], 0, 1).substr($leaveName_arr[1], 0, 1);  
                                 }
                                 else
                                 {
                                    //Check Holidays
                                     $hlf = TTnew('HolidayListFactory');
                                     $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1['day'].'-'.$date_month)));
                                     $hday_obj_arr = $hlf->getCurrent()->data;
                                     
                                     if(!empty($hday_obj_arr)){
                                        $status1 = 'HLD';  
                                     }
                                     else
                                     {
                                        //Schedule shifts
                                        //$slf = TTnew('ScheduleListFactory');
                                        $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                        $sp_obj_arr = $slf->getCurrent()->data;

                                        if(!empty($sp_obj_arr))
                                        {
                                            $status1 = 'A'; 
                                        }

                                     }

                                 }

                            }
                          //  $status1_row = $status1_row.'<td>'.$status1.'</td>';
                            //---End Status 1 row value  


                            //---Status 2 row value
                            $status2_row = $status2_row.'<td>'.date('D',  strtotime($i1['date_actual'])).'</td>';
                            unset($row_data_day_key[$i1['day']]); 

                        }
                   
                       
                        /*$udtlf = TTnew( 'UserDateTotalListFactory' );
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
                        $nof_absence =  $nof_days_for_month - ($nof_presence+$nof_weekoffs+$nof_holidays+$nof_leaves);*/
                  

                        $html_new = $html_new.'<tr style ="text-align:center" bgcolor="#CCCCCC" nobr="true">';
                        $html_new = $html_new.'<td align="left" width="40">'.$row['employee_number'].'</td>'; 
                        $html_new = $html_new.'<td colspan="'.((count($dates))+1).'" >'.$row['first_name'].' '.$row['last_name'].'</td>'; 
                        /*$html_new = $html_new.'<td colspan="6"> </td>';
                        $html_new = $html_new.'<td colspan="3">'.$nof_presence.'</td>';
                        $html_new = $html_new.'<td colspan="2">'.$nof_absence.'</td>';
                        $html_new = $html_new.'<td colspan="2">'.$nof_leaves.'</td>';
                        $html_new = $html_new.'<td colspan="2">'.$nof_weekoffs.'</td>';
                        $html_new = $html_new.'<td colspan="2">'.$nof_holidays.'</td>';
                        $html_new = $html_new.'<td colspan="2">'.$nof_ot.'</td>';
                        $html_new = $html_new.'<td colspan="12"></td>';*/
                        $html_new = $html_new.'</tr>'; 

                        //-------Day
                        $html_new = $html_new.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        $html_new = $html_new.'<td align="left">Date</td>'; 
                        $html_new = $html_new.$day_row;
                        $html_new = $html_new.'</tr>';
                        //$html_new = $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="'.($nof_days_for_month+2).'"></td></tr>';

                        //-------Shift ID
                       // $html_new =  $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                       // $html_new = $html_new.'<td align="left">Shift ID</td>'; 
                       // $html_new = $html_new.$shift_id_row; 
                       // $html_new = $html_new.'</tr>';

                        //-------Shift In
                        $html_new =  $html_new.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        $html_new = $html_new.'<td align="left">In</td>'; 
                        $html_new = $html_new.$shift_in_row; 
                        $html_new = $html_new.'</tr>';

                        //-------Shift Out
                        $html_new=  $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                        $html_new = $html_new.'<td align="left">Out</td>'; 
                        $html_new = $html_new.$shift_out_row; 
                        $html_new = $html_new.'</tr>';
                       // $html_new = $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="'.($nof_days_for_month+2).'"></td></tr>';

                        //-------Status 1
                       // $html_new=  $html_new.'<tr  style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        //$html_new = $html_new.'<td align="left" >Status 1</td>'; 
                        //$html_new = $html_new.$status1_row; 
                        //$html_new = $html_new.'</tr>';

                        //-------Status 2
                        $html_new=  $html_new.'<tr  style ="text-align:center" bgcolor="white" nobr="true">';
                        $html_new = $html_new.'<td align="left">Day</td>'; 
                        $html_new = $html_new.$status2_row; 
                        $html_new = $html_new.'</tr>';
                        $html_new = $html_new.'<tr style ="text-align:center;" bgcolor="white" nobr="true"><td style ="padding-bottom: 5px;" colspan="'.(($list_end_date-$list_start_date)+2).'"><hr/></td></tr>'; 

                        $html_new=  $html_new.'</table>';



                        $j1++;

                        if($j1%4 == 0){
                            $html_new .= '<br pagebreak="true" />'; 
                        }

                    }
                    
                }
                        
                // output the HTML content
                $pdf->writeHTML($html_new, true, false, true, false, '');
                        
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

        
    function MissingPunchReportExcelExport($data, $columns = NULL, $filter_data=NULL, $current_user, $current_company)
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

                     $br_strng =  $blf->getNameById($br_id); //eranda add code dynamic header data report

                        if($br_strng == null)
                        {
                                $company_name = $current_company->getName();
                                $addrss1 = $current_company->getAddress1();
                                $address2 = $current_company->getAddress2();
                                $city = $current_company->getCity();
                                $postalcode = $current_company->getPostalCode();
                        }else
                        {
                                $company_name = $blf->getNameById($br_id);
                                $addrss1 = $blf->getAddress1ById($br_id);
                                $address2 = $blf->getAddress2ById($br_id);
                                $city = $blf->getCityById($br_id);
                                $postalcode = $blf->getPostCodeById($br_id);

                        }
                    //    echo "<pre>"; print_r($blf->getNameById($br_id)); die;
                    $uglf = TTnew( 'UserGroupListFactory' ); 
                    if($fh_key == 'group_ids'){
                        foreach ($filter_header as $gr_id) {   
                            $group_list[] = $uglf->getNameById($gr_id); 
                        }
                        $gr_strng = implode(', ', $group_list);
                    }
                                
                } 
                if($dep_strng==''){$dep_strng='All';}

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

                $list_start_date = date('d',$pay_period_start);
                $list_end_date = date('d',$pay_period_end);

                
                $fileName = 'Missing Punch -' . $date_month;


                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Me")->setLastModifiedBy("Me")->setTitle("Missing Punch Sheet")->setSubject("Missing Punch Sheet")->setDescription("Missing Punch Sheet")->setKeywords("Excel Sheet")->setCategory("Me");

                $objPHPExcel->setActiveSheetIndex(0);
/*
                while( $current <= $last ) {

                    $dates[] = date('d', $current); 
                    $current = strtotime('+1 day', $current);
                    
                   
                }
                $dates[] = date('d', $current); 
                
                */
                
               $j = 0;
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);             
                $dates[$j]['date_actual'] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }
            
                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date_actual'] = date('Y-m-d', $current); 
                
         //  echo '<pre>'; print_r($dates); echo '<pre>'; die;
            
            
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
               
              

                foreach ($rows as $key => $row) {
                    $employee_number[$key]  = $row['employee_number'];
                }

                array_multisort($employee_number, SORT_ASC, $rows);/**/

                
                
                
                $row_data_day_key = array(); 
                $j1 = 0;
                
                $user_gap = 0;
                $total_gap=0;
            
                foreach ($rows as $row){

                    if(!empty($row['data']))
                    {

                        $html_new = $html_new.'<br/><table border="0" cellspacing="0" cellpadding="0" width="100%">';
                        

                        $present_days = 0; $absent_days = 0; $leave_days = 0; $week_off = 0; $holidays = 0;

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
                                
                            }
                       }

                        
                       // $nof_presence=0; $nof_absence=0; $nof_leaves=0; $nof_weekoffs=0; $nof_holidays=0; $nof_ot=0;

                        $day_row = ''; $shift_id_row = ''; $shift_in_row = ''; $shift_out_row = ''; $late_row = ''; $early_row= ''; $status1_row = ''; $status2_row = '';

                        $earlySec = $lateSec =0;

                       // for($i1=$list_start_date; $i1<=$list_end_date; $i1++){
                      
                       $array_cell = array('B','C','D','E','F','G','H','I','J','K','L', 'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF' ,'AG');
                
                        $aa=0;
                        foreach($dates as $i1 ){
                            
                            //---Get Total values
                            $status1 = '';

                            /*$lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                            $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);*/
                            
                            $udlf = TTnew('UserDateListFactory');
                            $pclf = TTnew('PunchControlListFactory');
                             
                            $udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1['day'].'-'.$date_month)));
                            $udlf_obj = $udlf->getCurrent();
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;

                          
                            
                             $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +4), $i1['day']);



                            $slf = TTnew('ScheduleListFactory');
                            

                            
                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;
                             
                            $schedule_name_arr = explode('-', $sp_obj_arr['shedule_policy_name']); 
                            $status_id = $schedule_name_arr[1]; 
                            
                          //  $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +7), $status_id);

                            

                            if(isset($row_data_day_key[ $i1['day']]['min_punch_time_stamp']) && $row_data_day_key[ $i1['day']]['min_punch_time_stamp']!=''){
                           
                               $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +5), $row_data_day_key[ $i1['day']]['min_punch_time_stamp']);
                            
                            }



                            //---Shift Out row value
                                if(isset($row_data_day_key[ $i1['day']]['max_punch_time_stamp']) && $row_data_day_key[ $i1['day']]['max_punch_time_stamp']!=''){
                                    
                                    $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +6), $row_data_day_key[$i1['day']]['max_punch_time_stamp']);

                                }
                           



                            //---Status 1 row value
                            $status1 = '';
                            $lateSec = strtotime($row_data_day_key[$i1['day']]['shedule_start_time']) - strtotime($row_data_day_key[ $i1['day']]['min_punch_time_stamp']);
                            $earlySec = strtotime($row_data_day_key[ $i1['day']]['shedule_end_time']) - strtotime($row_data_day_key[$i1['day']]['max_punch_time_stamp']);
                            
               
                            $elf = TTnew('ExceptionListFactory'); //--Add code eranda

                            //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                            //$udlf_obj = $udlf->getCurrent();
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;

                            $elf->getByUserDateId($udlf_obj->getId());
                            $elf_obj = $elf->getCurrent();

                            //if punch exists
                            if(!empty($pc_obj_arr)){
                                
                                $status1 = 'P';  

                                    if(!empty($elf_obj->data)) {
                                            //  if($epclf_obj->getExceptionPolicyControlID()) {
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
                                
                                 //Check user leaves
                                 $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                                 $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                                 $absLeave_obj_arr = $aluelf->getCurrent()->data;
                                 if(!empty($absLeave_obj_arr)){
                                     $leaveName_arr = explode(' ',$absLeave_obj_arr['absence_name']);
                                     $status1 = substr($leaveName_arr[0], 0, 1).substr($leaveName_arr[1], 0, 1);  
                                 }
                                 else
                                 {
                                    //Check Holidays
                                     $hlf = TTnew('HolidayListFactory');
                                     $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1['day'].'-'.$date_month)));
                                     $hday_obj_arr = $hlf->getCurrent()->data;
                                     
                                     if(!empty($hday_obj_arr)){
                                        $status1 = 'HLD';  
                                     }
                                     else
                                     {
                                        //Schedule shifts
                                        //$slf = TTnew('ScheduleListFactory');
                                        $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                        $sp_obj_arr = $slf->getCurrent()->data;

                                        if(!empty($sp_obj_arr))
                                        {
                                            $status1 = 'A'; 
                                        }

                                     }

                                 }

                            }
                          //  $status1_row = $status1_row.'<td>'.$status1.'</td>';
                            //---End Status 1 row value  


                            //---Status 2 row value
              
                           
                            $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +7), date('D',  strtotime($i1['day'].'-'.$date_month)));
                           
                            $aa++;
                        }
                   
                      
                        
                        $objPHPExcel->getActiveSheet()
                             ->setCellValue('A'.($user_gap +1), $row['employee_number'])
                             ->setCellValue('A'.($user_gap +2), $row['first_name'].'  '.$row['last_name'])
                             ->setCellValue('A'.($user_gap +4), 'Day')
                             ->setCellValue('A'.($user_gap +5), 'In')
                             ->setCellValue('A'.($user_gap +6), 'Out')
                             ->setCellValue('A'.($user_gap +7), 'Status');

                     
                       
                        $user_gap +=12;                
                        $j1++;



                    }
                    
                }
                        
               //
                
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
                
            }

        }

        
    function MissingPunchReport1($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {

        echo '<pre>';
        print_r($data);
        die;
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }
            $br_strng = $blf->getNameById($br_id);
            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        $list_start_date = date('d', $pay_period_start);
        $list_end_date = date('d', $pay_period_end);

        $list_count = ($list_end_date - $list_start_date) + 1;

        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }

        //echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Missing Punch Report',
                'group_list' => $gr_strng,
                //'department_list'  => $dep_strng, 
                'branch_list' => $br_strng,
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

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
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';


            $pdf->SetFont('', 'B', 6.5);

            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows);

            $row_data_day_key = array();
            $j1 = 0;
            foreach ($rows as $row) {

                if (!empty($row['data'])) {

                    $html = $html . '<table border="0" cellspacing="0" cellpadding="3" width="100%">';

                    /* $html = $html.'<tr style="background-color:#CCCCCC;text-align:center;" >';
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

                      $html = $html.'</tr>'; */
                    $present_days = 0;
                    $absent_days = 0;
                    $leave_days = 0;
                    $week_off = 0;
                    $holidays = 0;


                    foreach ($row['data'] as $row1) {

                        if ($row1['date_stamp'] != '') {
                            $row_dt = str_replace('/', '-', $row1['date_stamp']);

                            $dat_day = date('d', strtotime($row_dt));
                            //echo '<br><pre>'.$dat_day;
                            $row_data_day_key[$dat_day] = $row1;

                            //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                        } else {
                            $tot_ot_hours_data = $row1['over_time'];
                            $tot_worked_actual_hours_data = $row1['actual_time'];
                            $tot_worked_hours_data = explode(':', $row1['worked_time']);
                            $tot_worked_sec_data = ($tot_worked_hours_data[0] * 60 * 60) + ($tot_worked_hours_data[1] * 60);
                            //                            
                        }
                    }

                    //echo '<pre>'; print_r($row); 
                    //row1
                    $nof_presence = 0;
                    $nof_absence = 0;
                    $nof_leaves = 0;
                    $nof_weekoffs = 0;
                    $nof_holidays = 0;
                    $nof_ot = 0;

                    $date_diff = $list_end_date - $list_start_date;

                    for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {

                        //echo '<pre>';
                        //print_r($row_data_day_key[sprintf("%02d", $i1)]);
                        // $status1 = '';

                        $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                        $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                        $udlf = TTnew('UserDateListFactory');
                        $pclf = TTnew('PunchControlListFactory');

                        //                            
                        $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                        $udlf_obj = $udlf->getCurrent();

                        $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                        $pc_obj_arr = $pclf->getCurrent()->data;
                        //                         echo '<pre>'; print_r($pc_obj_arr); die;
                        //if punch exists
                        if (!empty($pc_obj_arr)) {
                            $status1 = 'P';
                            //check late come and early departure
                            $elf = TTnew('ExceptionListFactory');
                            $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                            $ex_obj_arr = $elf->getCurrent()->data;
                            if (!empty($ex_obj_arr)) {
                                $status1 = 'LP';
                            }
                        } else {
                            $status1 = 'WO';

                            $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                            $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                            $absLeave_obj_arr = $aluelf->getCurrent()->data;
                            if (!empty($absLeave_obj_arr)) {
                                $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                                $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);

                                if ($status1 != 'WO') {
                                    //$tot_array['L'][]=$i1;
                                    if ($absLeave_obj_arr['absence_leave_id'] == 2) {
                                        $tot_array['L'] += 0.5;
                                        $tot_array['P'] += 0.5;
                                    } else {
                                        $tot_array['L'] += 1;
                                    }
                                }
                            }
                        }


                        $hlf = TTnew('HolidayListFactory');
                        $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                        $hday_obj_arr = $hlf->getCurrent()->data;

                        if (!empty($hday_obj_arr)) {
                            $status1 = 'HLD';
                        }


                        // $tot_array[$status1][]=$i1;
                        $tot_array[$status1] += 1;
                    }

                    $udtlf = TTnew('UserDateTotalListFactory');
                    $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate($current_company->getId(), $row['user_id'], 10, date('Y-m-d', $pay_period_start), date('Y-m-d', $pay_period_end));
                    if ($udtlf->getRecordCount() > 0) {
                        foreach ($udtlf as $udt_obj) {
                            if ($udt_obj->getOverTimePolicyID() != 0) {
                                $tot_array['OT'] += 1;
                            }
                        }
                    }

                    if (isset($tot_array['P'])) {
                        $nof_presence += $tot_array['P'];
                    }

                    if (isset($tot_array['LP'])) {
                        $nof_presence += $tot_array['LP'];
                    }

                    if (isset($tot_array['WO'])) {
                        $nof_weekoffs = $tot_array['WO'];
                    }

                    if (isset($tot_array['HLD'])) {
                        $nof_holidays = $tot_array['HLD'];
                    }

                    if (isset($tot_array['L'])) {
                        $nof_leaves = $tot_array['L'];
                    }

                    if (isset($tot_array['OT'])) {
                        $nof_ot = $tot_array['OT'];
                    }


                    unset($tot_array);
                    $nof_absence = $nof_days_for_month - ($nof_presence + $nof_weekoffs + $nof_holidays + $nof_leaves);


                    /* $html = $html.'<tr style ="text-align:center"  bgcolor="#EEEEEE" nobr="true">';
                      $html = $html.'<td align="left" colspan="'.($list_count+2).'">EPF No: '.$row['employee_number'].' <br/> Emp Name: '.$row['first_name'].' '.$row['last_name'].' <br/>  Department: '.$row['default_department'].'</td>';
                      $html = $html.'<td colspan="2">'.$nof_absence.'</td>';
                      $html = $html.'<td colspan="2">'.$nof_leaves.'</td>';
                      $html = $html.'<td colspan="2">'.$nof_weekoffs.'</td>';
                      $html = $html.'<td colspan="2">'.$nof_holidays.'</td>';
                      $html = $html.'<td colspan="2">'.$nof_ot.'</td>';
                      $html = $html.'<td colspan="12"></td>';
                      $html = $html.'</tr>'; */

                    $html = $html . '<tr style ="text-align:left" bgcolor="#EEEEEE" nobr="true"><td colspan="' . ($list_count + 2) . '">EPF No: ' . $row['employee_number'] . '</td></tr>';

                    $html = $html . '<tr style ="text-align:left" bgcolor="#EEEEEE" nobr="true"><td colspan="' . ($list_count + 2) . '">Employee Name: ' . $row['first_name'] . ' ' . $row['last_name'] . ' </td></tr>';

                    $html = $html . '<tr style ="text-align:left" bgcolor="#EEEEEE" nobr="true"><td colspan="' . ($list_count + 2) . '"> Department: ' . $row['default_department'] . '</td></tr>';

                    //$html = $html.'<tr style ="text-align:left" bgcolor="#EEEEEE" nobr="true"><td colspan="'.($list_count+2).'"></td></tr>'; 
                    //row2
                    $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                    $html = $html . '<td align="left"  width="60">Day</td>';
                    $html = $html . '<td></td>';
                    for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {
                        $html = $html . '<td width="45">' . $i1 . '</td>';
                    }
                    $html = $html . '</tr>';


                    $status1 = '';
                    //row3
                    $html = $html . '<tr style ="text-align:center"  bgcolor="#EEEEEE" nobr="true">';
                    $html = $html . '<td align="left">Shift In</td>';
                    $html = $html . '<td></td>';
                    for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {
                        $html = $html . '<td>' . $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] . '</td>';
                    }
                    $html = $html . '</tr>';

                    //row4
                    $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                    $html = $html . '<td align="left">Shift Out</td>';
                    $html = $html . '<td></td>';
                    for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {
                        $html = $html . '<td>' . $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] . '</td>';
                    }
                    $html = $html . '</tr>';

                    $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="#EEEEEE" nobr="true">';
                    $html = $html . '<td align="left" >Status 1</td>';
                    $html = $html . '<td></td>';
                    for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {

                        if ($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] == '' && $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] != '') {
                            $status1 = 'MIS'; //'MIS-IN';
                        } elseif ($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] == '' && $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] != '') {
                            $status1 = 'MIS'; //'MIS-OUT';
                        } else {
                            $status1 = 'P';
                        }

                        unset($row_data_day_key[sprintf("%02d", $i1)]);

                        $html = $html . '<td>' . $status1 . '</td>';
                    }
                    $html = $html . '</tr>';

                    $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                    $html = $html . '<td align="left">Status 2</td>';
                    $html = $html . '<td></td>';
                    for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {
                        $html = $html . '<td>' . date('D', strtotime($i1 . '-' . $date_month)) . '</td>';
                    }
                    $html = $html . '</tr>';



                    $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="' . ($list_count + 2) . '"><br/><hr/><br/></td></tr>';

                    $html = $html . '</table>';

                    $j1++;

                    if ($j1 % 3 == 0) {
                        $html .= '<br pagebreak="true" />';
                    }
                }
            }

            //die;   
            //echo $html;    
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    //---End MissingPunchReport


    function DailyAttendanceDetailed1($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }
            $br_strng = $blf->getNameById($br_id);
            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        $list_start_date = date('d', $pay_period_start);
        $list_end_date = date('d', $pay_period_end);

        $list_count = ($list_end_date - $list_start_date) + 1;

        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }

        //echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Daily Attendance Report',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

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
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';


            // foreach ($data as $key => $row) {
            //     $volume[$key]  = $row['volume'];
            //     $edition[$key] = $row['edition'];
            // }
            // Sort the data with volume descending, edition ascending
            // Add $data as the last parameter, to sort by the common key
            //array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data);
            //Sort array by employee_number
            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows);

            //echo '<pre>'; print_r($rows); die;

            $pdf->SetFont('', 'B', 6.5);


            $row_data_day_key = array();
            $j1 = 0;
            foreach ($rows as $row) {



                $html = $html . '<table border="0" cellspacing="0" cellpadding="0" width="100%">';


                $present_days = 0;
                $absent_days = 0;
                $leave_days = 0;
                $week_off = 0;
                $holidays = 0;

                //echo '<pre>'; print_r($row); die;

                foreach ($row['data'] as $row1) {

                    if ($row1['date_stamp'] != '') {
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d', strtotime($row_dt));
                        //echo '<br><pre>'.$dat_day;
                        $row_data_day_key[$dat_day] = $row1;

                        //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                    } else {
                        $tot_ot_hours_data = $row1['over_time'];
                        $tot_worked_actual_hours_data = $row1['actual_time'];
                        $tot_worked_hours_data = explode(':', $row1['worked_time']);
                        $tot_worked_sec_data = ($tot_worked_hours_data[0] * 60 * 60) + ($tot_worked_hours_data[1] * 60);
//                            
                    }
                }



                //row1
                $nof_presence = 0;
                $nof_absence = 0;
                $nof_leaves = 0;
                $nof_weekoffs = 0;
                $nof_holidays = 0;
                $nof_ot = 0;

                $date_diff = $list_end_date - $list_start_date;

                for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {

                    //echo '<pre>';
                    //print_r($row_data_day_key[sprintf("%02d", $i1)]);

                    $status1 = '';

                    $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                    $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                    $udlf = TTnew('UserDateListFactory');
                    $pclf = TTnew('PunchControlListFactory');

//                            
                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $udlf_obj = $udlf->getCurrent();

                    $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $pc_obj_arr = $pclf->getCurrent()->data;
                    //echo '<pre>'; print_r($pc_obj_arr); die;
                    //if punch exists
                    if (!empty($pc_obj_arr)) {
                        $status1 = 'P';
                        //check late come and early departure
                        $elf = TTnew('ExceptionListFactory');
                        $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                        $ex_obj_arr = $elf->getCurrent()->data;
                        if (!empty($ex_obj_arr)) {
                            $status1 = 'LP';
                        }
                    } else {
                        $status1 = 'WO';

                        $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                        $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                        $absLeave_obj_arr = $aluelf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                            $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                            $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);

                            if ($status1 != 'WO') {
                                //$tot_array['L'][]=$i1;
                                if ($absLeave_obj_arr['absence_leave_id'] == 2) {
                                    $tot_array['L'] += 0.5;
                                    $tot_array['P'] += 0.5;
                                } else {
                                    $tot_array['L'] += 1;
                                }
                            }
                        }
                    }


                    $hlf = TTnew('HolidayListFactory');
                    $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $hday_obj_arr = $hlf->getCurrent()->data;

                    if (!empty($hday_obj_arr)) {
                        $status1 = 'HLD';
                    }


                    // $tot_array[$status1][]=$i1;
                    $tot_array[$status1] += 1;
                }

                $udtlf = TTnew('UserDateTotalListFactory');
                $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate($current_company->getId(), $row['user_id'], 10, date('Y-m-d', $pay_period_start), date('Y-m-d', $pay_period_end));
                if ($udtlf->getRecordCount() > 0) {
                    foreach ($udtlf as $udt_obj) {
                        if ($udt_obj->getOverTimePolicyID() != 0) {
                            $tot_array['OT'] += 1;
                        }
                    }
                }

                if (isset($tot_array['P'])) {
                    $nof_presence += $tot_array['P'];
                }

                if (isset($tot_array['LP'])) {
                    $nof_presence += $tot_array['LP'];
                }

                if (isset($tot_array['WO'])) {
                    $nof_weekoffs = $tot_array['WO'];
                }

                if (isset($tot_array['HLD'])) {
                    $nof_holidays = $tot_array['HLD'];
                }

                if (isset($tot_array['L'])) {
                    $nof_leaves = $tot_array['L'];
                }

                if (isset($tot_array['OT'])) {
                    $nof_ot = $tot_array['OT'];
                }


                unset($tot_array);
                $nof_absence = $nof_days_for_month - ($nof_presence + $nof_weekoffs + $nof_holidays + $nof_leaves);




                $html = $html . '<tr style ="text-align:center"  bgcolor="#CCCCCC" nobr="true">';
                $html = $html . '<td align="left"  width="40">' . $row['employee_number'] . '</td>';
                $html = $html . '<td colspan="' . ($list_count + 1) . '"  width="25%">' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';

                $html = $html . '</tr>';


                //row2
                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Day</td>';
                $html = $html . '<td></td>';
                for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {
                    $html = $html . '<td width="28">' . $i1 . '</td>';
                }
                $html = $html . '</tr>';



                //row3
                $html = $html . '<tr style ="text-align:center"  bgcolor="#EEEEEE" nobr="true">';
                $html = $html . '<td align="left">Shift In</td>';
                $html = $html . '<td></td>';

                $plf = TTnew('PunchListFactory');

                $udlf->getByUserIdAndStartDateAndEndDate($row['user_id'], $pay_period_start, $pay_period_end);
                $udlf_obj = $udlf->getCurrent();

                $plf->getByUserDateId($udlf_obj->getId()); //par - user_date_id


                for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {

                    //$plf_obj_arr = $plf->getCurrent()->data;
                    //echo '<pre>'; print_r($plf); die;
                    // echo '<br><br><br>date.....'.date('Y-m-d',  strtotime($i1.'-'.$date_month));

                    $minpunchtime = '';
                    foreach ($plf as $plf_obj) {
                        //echo  $plf_obj->getStatus();
                        //echo '<pre>'; print_r($plf_obj);

                        if ($plf_obj->getTimeStamp() != '' && date('Y-m-d', $plf_obj->getTimeStamp()) == date('Y-m-d', strtotime($i1 . '-' . $date_month)) && $plf_obj->getStatus() == '10') {
                            $minpunchtime .= date('H:i', $plf_obj->getTimeStamp()) . ' ';
                        }
                    }

                    $html = $html . '<td>' . $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] . '</td>';
                    //$html = $html.'<td>'.$minpunchtime.'</td>'; 
                }
                $html = $html . '</tr>';

                //row4
                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Shift Out</td>';
                $html = $html . '<td></td>';
                for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {

                    $maxpunchtime = '';
                    foreach ($plf as $plf_obj) {
                        //echo  $plf_obj->getStatus();
                        //echo '<pre>'; print_r($plf_obj);

                        if ($plf_obj->getTimeStamp() != '' && date('Y-m-d', $plf_obj->getTimeStamp()) == date('Y-m-d', strtotime($i1 . '-' . $date_month)) && $plf_obj->getStatus() == '20') {
                            $maxpunchtime .= date('H:i', $plf_obj->getTimeStamp()) . ' ';
                        }
                    }

                    $html = $html . '<td>' . $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] . '</td>';
                    //$html = $html.'<td>'.$maxpunchtime.'</td>'; 
                }
                $html = $html . '</tr>';

                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="34"></td></tr>';
                //row5
                $html = $html . '<tr  style =" height:50px; text-align:center"  bgcolor="#EEEEEE" nobr="true">';
                $html = $html . '<td align="left">Late</td>';
                $html = $html . '<td></td>';

                for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {

                    $udlf = TTnew('UserDateListFactory');
                    $slf = TTnew('ScheduleListFactory');

                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $udlf_obj = $udlf->getCurrent();

                    $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $sp_obj_arr = $slf->getCurrent()->data;
                    $late = '';
                    if (!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] != '') {
                        $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                        if ($lateSec < 0) {
                            $late = gmdate("H:i", abs($lateSec));
                        }
                    }
                    $html = $html . '<td>' . $late . '</td>';
                }
                $html = $html . '</tr>';

                //row6
                $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Early</td>';
                $html = $html . '<td></td>';
                for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {

                    $udlf = TTnew('UserDateListFactory');
                    $slf = TTnew('ScheduleListFactory');

                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $udlf_obj = $udlf->getCurrent();

                    $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $sp_obj_arr = $slf->getCurrent()->data;
                    $early = '';
                    if (!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] != '') {
                        $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                        if ($earlySec > 0) {
                            $early = gmdate("H:i", abs($earlySec));
                        }
                    }
                    $html = $html . '<td>' . $early . '</td>';
                }
                $html = $html . '</tr>';


                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="34"></td></tr>';

                //row7
                $html = $html . '<tr  style =" height:50px; text-align:center"  bgcolor="#EEEEEE" nobr="true">';
                $html = $html . '<td align="left" >Status 1</td>';
                $html = $html . '<td></td>';
                $earlySec = $lateSec = 0;
                for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {
                    $status1 = '';
                    $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                    $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                    $udlf = TTnew('UserDateListFactory');
                    $pclf = TTnew('PunchControlListFactory');
                    $elf = TTnew('ExceptionListFactory'); //--Add code eranda
//                            
                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $udlf_obj = $udlf->getCurrent();

                    $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $pc_obj_arr = $pclf->getCurrent()->data;
//                            echo '<pre>'; print_r($pc_obj_arr); die;
                    $elf->getByUserDateId($udlf_obj->getId());
                    $elf_obj = $elf->getCurrent();

                    //if punch exists
                    if (!empty($pc_obj_arr)) {
                        $status1 = 'P';


                        //check late come and early departure

                        if (!empty($elf_obj->data)) {
                            //  if($epclf_obj->getExceptionPolicyControlID()) {
                            foreach ($elf as $elf_obj) {
                                if ($elf_obj->getExceptionPolicyID() == '29' || $elf_obj->getExceptionPolicyID() == '5') {
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
                    } else {
                        $status1 = 'WO';

                        $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                        $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                        $absLeave_obj_arr = $aluelf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                            $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                            $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                        }
                        //echo '<pre><br>'.date('Y-m-d',  strtotime($i1.'-'.$date_month)).$udlf_obj->getId(); print_r($absLeave_obj_arr); 
                    }


                    $hlf = TTnew('HolidayListFactory');
                    $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $hday_obj_arr = $hlf->getCurrent()->data;

                    if (!empty($hday_obj_arr)) {
                        $status1 = 'HLD';
                    }



                    $html = $html . '<td>' . $status1 . '</td>';
                }
                //die;
                $html = $html . '</tr>';

                //row8
                $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Status 2</td>';
                $html = $html . '<td></td>';
                for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {
                    $html = $html . '<td>' . date('D', strtotime($i1 . '-' . $date_month)) . '</td>';
                    unset($row_data_day_key[sprintf("%02d", $i1)]);
                }
                $html = $html . '</tr>';

                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="' . ($list_count + 2) . '"><br/><hr/></td></tr>';

                $html = $html . '</table>';
                $j1++;

                if ($j1 % 3 == 0) {
                    $html .= '<br pagebreak="true" />';
                }
            }


            //echo $html; 
            //die;      
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    function DailyAttendanceDetailed($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {

        // echo '<pre>'; print_r($filter_data); die;
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            $br_strng = $blf->getNameById($br_id);
            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }


            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            //$pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['start_date'];
            //$pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        $list_start_date = date('d', $pay_period_start);
        $list_end_date = date('d', $pay_period_end);

        $list_count = ($list_end_date - $list_start_date) + 1;

        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }

        //echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'payperiod_end_date' => date('Y-M-d', $pay_period_start),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Daily Attendance Report',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 258,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 55, 30);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';

            //echo '<pre>'; print_r($rows); die;
            //Sort array by employee_number
            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows); /**/



            $pdf->SetFont('', 'B', 10);


            $row_data_day_key = array();
            $j1 = 0;
            $html = $html . '<br/><br/><br/><table border="0" cellspacing="0" cellpadding="0" width="100%">';

            $html = $html . '<thead><tr bgcolor="#CCCCCC" nobr="true">';
            $html = $html . '<td  height="25" align="left" width="10%">EPF No.</td>';
            $html = $html . '<td align="left" width="35%">Employee Name</td>';
            $html = $html . '<td align="center" width="10%"> In</td>';
            $html = $html . '<td align="center" width="10%"> Out</td>';
            $html = $html . '<td align="center" width="10%">Late Attendance</td>';
            $html = $html . '<td align="center" width="10%">Early Departures</td>';
            $html = $html . '<td align="center" width="10%">Status</td>';
            //$html = $html.'<td align="center">Status 2</td>';
            $html = $html . '</tr></thead>';

            $html = $html . '<tbody>';

            $pdf->SetFont('', '', 10);

            foreach ($rows as $row) {

                foreach ($row['data'] as $row1) {

                    if ($row1['date_stamp'] != '') {
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d', strtotime($row_dt));
                        //echo '<br><pre>'.$dat_day;
                        $row_data_day_key[$dat_day] = $row1;

                        //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                    } else {
                        $tot_ot_hours_data = $row1['over_time'];
                        $tot_worked_actual_hours_data = $row1['actual_time'];
                        $tot_worked_hours_data = explode(':', $row1['worked_time']);
                        $tot_worked_sec_data = ($tot_worked_hours_data[0] * 60 * 60) + ($tot_worked_hours_data[1] * 60);
                        //                            
                    }
                }


                if ($j1 % 2 == 0) {
                    $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                } else {
                    $html = $html . '<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                }


                //$html = $html.'<tr style ="text-align:center" nobr="true" >';
                $html = $html . '<td height="25" align="left"  width="10%">' . $row['employee_number'] . '</td>';
                $html = $html . '<td align="left"  width="35%" >' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
                $html = $html . '<td align="center" width="10%">' . $row_data_day_key[sprintf("%02d", $list_start_date)]['min_punch_time_stamp'] . '</td>';
                $html = $html . '<td align="center" width="10%">' . $row_data_day_key[sprintf("%02d", $list_start_date)]['max_punch_time_stamp'] . '</td>';

                $udlf = TTnew('UserDateListFactory');
                $slf = TTnew('ScheduleListFactory');

                $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($list_start_date . '-' . $date_month)));
                $udlf_obj = $udlf->getCurrent();

                $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                $sp_obj_arr = $slf->getCurrent()->data;
                $late = '';
                if (!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $list_start_date)]['min_punch_time_stamp'] != '') {
                    $lateSec = strtotime($row_data_day_key[sprintf("%02d", $list_start_date)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $list_start_date)]['min_punch_time_stamp']);
                    if ($lateSec < 0) {
                        $late = gmdate("H:i", abs($lateSec));
                    }
                }
                $html = $html . '<td align="center" width="10%">' . $late . '</td>';

                $udlf = TTnew('UserDateListFactory');
                $slf = TTnew('ScheduleListFactory');

                $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($list_start_date . '-' . $date_month)));
                $udlf_obj = $udlf->getCurrent();

                $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                $sp_obj_arr = $slf->getCurrent()->data;
                $early = '';
                if (!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $list_start_date)]['max_punch_time_stamp'] != '') {
                    $earlySec = strtotime($row_data_day_key[sprintf("%02d", $list_start_date)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $list_start_date)]['max_punch_time_stamp']);

                    if ($earlySec > 0) {
                        $early = gmdate("H:i", abs($earlySec));
                    }
                }
                $html = $html . '<td align="center" width="10%">' . $early . '</td>';


                $status1 = '';
                $lateSec = strtotime($row_data_day_key[sprintf("%02d", $list_start_date)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $list_start_date)]['min_punch_time_stamp']);
                $earlySec = strtotime($row_data_day_key[sprintf("%02d", $list_start_date)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $list_start_date)]['max_punch_time_stamp']);

                $udlf = TTnew('UserDateListFactory');
                $pclf = TTnew('PunchControlListFactory');
                $elf = TTnew('ExceptionListFactory'); //--Add code eranda
//                            
                $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($list_start_date . '-' . $date_month)));
                $udlf_obj = $udlf->getCurrent();

                $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                $pc_obj_arr = $pclf->getCurrent()->data;
//                            echo '<pre>'; print_r($pc_obj_arr); die;
                $elf->getByUserDateId($udlf_obj->getId());
                $elf_obj = $elf->getCurrent();

                //if punch exists
                if (!empty($pc_obj_arr)) {
                    $status1 = 'P';

                    if (!empty($elf_obj->data)) {

                        foreach ($elf as $elf_obj) {
                            if ($elf_obj->getExceptionPolicyID() == '29' || $elf_obj->getExceptionPolicyID() == '5') {
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
                } else {
                    /* $status1 = 'WO'; 

                      $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                      $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                      $absLeave_obj_arr = $aluelf->getCurrent()->data;
                      if(!empty($absLeave_obj_arr)){
                      $leaveName_arr = explode(' ',$absLeave_obj_arr['absence_name']);
                      $status1 = substr($leaveName_arr[0], 0, 1).substr($leaveName_arr[1], 0, 1);
                      } */

                    $status1 = 'WO';

                    //Check user leaves
                    $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                    $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                    $absLeave_obj_arr = $aluelf->getCurrent()->data;
                    if (!empty($absLeave_obj_arr)) {
                        $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                        $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                    } else {
                        //Check Holidays
                        $hlf = TTnew('HolidayListFactory');
                        $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                        $hday_obj_arr = $hlf->getCurrent()->data;

                        if (!empty($hday_obj_arr)) {
                            $status1 = 'HLD';
                        } else {
                            //Schedule shifts
                            $slf = TTnew('ScheduleListFactory');
                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;

                            if (!empty($sp_obj_arr)) {
                                $status1 = 'A';
                            } else {
                                $date_name = date('l', strtotime($current));

                                if ($date_name == 'Saturday' || $date_name == 'Sunday') {
                                    
                                } else {
                                    $status1 = 'AB';
                                }
                            }
                        }
                    }
                }


                /* $hlf = TTnew('HolidayListFactory');
                  $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($list_start_date.'-'.$date_month)));
                  $hday_obj_arr = $hlf->getCurrent()->data;

                  if(!empty($hday_obj_arr)){
                  $status1 = 'HLD';
                  } */

                $html = $html . '<td align="center">' . $status1 . '</td>';
                //$html = $html.'<td>'.date('D',  strtotime($list_start_date.'-'.$date_month)).'</td>';

                unset($row_data_day_key[sprintf("%02d", $list_start_date)]);

                //$html = $html.'<td align="left">'.$list_start_date.'</td>';
                $html = $html . '</tr>';

                $j1++;

                /* if($j1%3 == 0){
                  $html .= '<br pagebreak="true" />';
                  } */
            }
            $html = $html . '<tbody>';
            $html = $html . '</table>';

            $html = $html . '<table style="margin-top:5%">';
            $html = $html . '<tr>';
            $html = $html . '<td width="12%">Total Count</td>';
            $html = $html . '<td width="5%">'.count($rows).'</td>';
            $html = $html . '</tr>';
            $html = $html . '</table>';
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    function MonthlyAttendanceDetailed($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }
            //    echo "<pre>"; print_r($blf->getNameById($br_id)); die;
            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        $list_start_date = date('d', $pay_period_start);
        $list_end_date = date('d', $pay_period_end);


        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }
       // $dates[] = date('d', $current);

     //  echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Attendance Report 2',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

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
            $pdf->AddPage('l', 'mm', 'A2');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(50, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';


            /* foreach ($data as $key => $row) {
              $volume[$key]  = $row['volume'];
              $edition[$key] = $row['edition'];
              }

              // Sort the data with volume descending, edition ascending
              // Add $data as the last parameter, to sort by the common key
              array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data); */

            //Sort array by employee_number
            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows); /**/


            $pdf->SetFont('', 'B', 6.5);


            $row_data_day_key = array();
            $j1 = 0;

            $no_of_emp = 0;
            $no_of_department_present = 0;
            $no_of_department_leaves = 0;
            $no_of_department_short_leaves = 0;
            $no_of_department_nopay = 0;
            $no_of_department_ot = 0;
            $no_of_department_late = 0;
            $no_of_department_early = 0;

            foreach ($rows as $row) {

                $html_new = $html_new . '<br/><br/><table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td align="left">' . $row['employee_number'] . '</td></tr>'
                        . '<tr><td>' . $row['first_name'] . ' ' . $row['last_name'] . '</td></tr></table><table border="1" cellspacing="0" cellpadding="0" width="100%">';


                $present_days = 0;
                $absent_days = 0;
                $leave_days = 0;
                $week_off = 0;
                $holidays = 0;

                foreach ($row['data'] as $row1) {
                    
                  //  print_r($row['data']); exit;


                    if ($row1['date_stamp'] != '') {
                        //echo $row1['date_stamp'];
                        //exit();
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d', strtotime($row_dt));
                        //echo '<br><pre>'.$dat_day;
                        $row_data_day_key[$dat_day] = $row1;

                        //    $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                    } else {
                        $tot_ot_hours_data = $row1['over_time'];
                        $tot_worked_actual_hours_data = $row1['actual_time'];
                        $tot_worked_hours_data = explode(':', $row1['worked_time']);
                        $tot_worked_sec_data = ($tot_worked_hours_data[0] * 60 * 60) + ($tot_worked_hours_data[1] * 60);
                    }
                }



                $nof_presence = 0;
                $nof_absence = 0;
                $nof_leaves = 0;
                $nof_weekoffs = 0;
                $nof_holidays = 0;
                $nof_ot = 0;
                $no_of_late_attendance = 0;
                $no_of_early_dep = 0;
                $no_of_short_leaves = 0;
                $nopay = 0;

                $day_row = '';
                $shift_id_row = '';
                $shift_in_row = '';
                $shift_out_row = '';
                $late_row = '';
                $early_row = '';
                $status1_row = '';
                $status2_row = '';
                $nof_half_days = 0;
                $working_hours_row = '';

                $earlySec = $lateSec = 0;

                //  for($i1=$list_start_date; $i1<=$list_end_date; $i1++){
              //  foreach ($row_data_day_key as $i1 => $row_data) {
                
               // $date_check_start = new DateTime();
                //$date_check_start->setTimestamp($pay_period_start);
                //$date_stamp= $date_check_start->format('Y-m-d');
                
                $Resign_date_stamp = 0;
                
                
                foreach($dates as $date){

                    
                    $row_data = $row_data_day_key[$date];
                
                // echo '<pre>';print_r($row_data_day_key);exit;
                    //---Get Total values date_stamp
                    $status1 = '';



                    $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - strtotime($row_data_day_key[$date]['min_punch_time_stamp']);
                    $earlySec = strtotime($row_data_day_key[$date]['shedule_end_time']) - strtotime($row_data_day_key[$date]['max_punch_time_stamp']);

                    $udlf = TTnew('UserDateListFactory');
                    $pclf = TTnew('PunchControlListFactory');

                    //$date_stamp =date('Y-m-d',  strtotime($row_data['date_stamp']));
                    // $Hr_date = new DateTime();
                    
                    if(isset($row_data['date_stamp'])){
                        $Hr_date = DateTime::createFromFormat('d/m/Y', $row_data['date_stamp']);
                        $date_stamp = $Hr_date->format('Y-m-d');
                        
                        $udlf->getByUserIdAndDate($row['user_id'], $date_stamp);
                        $udlf_obj = $udlf->getCurrent();

                        $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                        $pc_obj_arr = $pclf->getCurrent()->data;
                        
                    }
                    else{
                        //$current = strtotime('+1 day', $date_stamp);
                        
                        if(isset($date_stamp)){
                            $date_check = new DateTime($date_stamp);
                            $date_check->add(new DateInterval('P1D'));
                            $date_stamp= $date_check->format('Y-m-d');
                            $Resign_date_stamp =$date_check->getTimestamp();
                        }
                        else{
                            $date_check_start = new DateTime();
                            $date_check_start->setTimestamp($pay_period_start);
                            $date_stamp= $date_check_start->format('Y-m-d');
                            
                            $Resign_date_stamp = $pay_period_start;
                        }
                        //echo $date_stamp;exit;
                    }



                    // echo $date_stamp.' '.$row_data['date_stamp'].'<br>';





                    //if punch exists
                    if (!empty($pc_obj_arr)) {
                        
                        $status1 = 'P';
                        //check late come and early departure
                        $elf = TTnew('ExceptionListFactory');
                        $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                        $ex_obj_arr = $elf->getCurrent()->data;
                        if (!empty($ex_obj_arr)) {
                          //  $status1 = 'LP';
                        }
                       
                        $alf = new AccrualListFactory();
                        
                        
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$date_stamp);
                        $absLeave_obj_arr = $alf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                            
                         
                            if ($absLeave_obj_arr['accrual_policy_id'] == 8 && $absLeave_obj_arr['type_id']==55) {//Full day leave
                                
                                  
                                     $tot_array['SH'] = $tot_array['SH']  + 1;
                                    // $status1 = 'SH';
                                    // echo $date_stamp.'<br>'.$tot_array['SH']; 
                            }
                            else{
                                    if($absLeave_obj_arr['amount']== -14400){
                                         $tot_array['L'] += 0.5;
                                         $tot_array['P'] -= 0.5;
                                    }else{ 
                                       // $tot_array['L'] += abs($absLeave_obj_arr['amount']/28800);
                                       // $status1 = 'LV';
                                    }
                                // echo $date_stamp.'<br>';
                            }
                                    
                        }
                        
                    } else {
                        $status1 = 'WO';

                        $alf = new AccrualListFactory();
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$date_stamp);
                        
                        $absLeave_obj_arr = $alf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                           // $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                           // $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                                      //  print_r($absLeave_obj_arr);exit;

                            
                                //$tot_array['L'][]=$i1;
                                if ($absLeave_obj_arr['accrual_policy_id'] != 8 && $absLeave_obj_arr['type_id']==55) {//Half day leave 
                                   
                                    
                                    if($absLeave_obj_arr['amount']== -14400){
                                        // $tot_array['L'] += 0.5;
                                        // $tot_array['P'] -= 0.5;
                                          $status1 = 'HL';
                                    }
                                    elseif($absLeave_obj_arr['amount']== -28800){
                                        $tot_array['L'] += 1;
                                    }
                                    else{
                                        $tot_array['L'] += abs($absLeave_obj_arr['amount']/28800);
                                    }
                                    
                                  
                                } else if ($absLeave_obj_arr['accrual_policy_id'] == 8) {//Full day leave
                                    // $tot_array['SH'] += 1;
                                    // echo 'bbbb';
                                    // $status1 = 'SH';
                                } else if ($absLeave_obj_arr['accrual_policy_id'] == 3) {//Short leave
                                   // $tot_array['SH'] += 1;
                                }
                            
                        }
                        
                        
                    }
////////////////////////////////////////////////////////////////////////////////////////////////////////

                    $hlf = TTnew('HolidayListFactory');
                    $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                    $hday_obj_arr = $hlf->getCurrent()->data;

                    if (!empty($hday_obj_arr) && empty($pc_obj_arr)) {
                        $status1 = 'HLD';
                    }
                    $tot_array[$status1] += 1;
                    //---End Get Total values
                    //---Day row value
                    $day_row = $day_row . '<td>' . $date . '</td>';


                    //---Shift ID row value
                    //$udlf = TTnew('UserDateListFactory');
                    $slf = TTnew('ScheduleListFactory');

                    //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                    //$udlf_obj = $udlf->getCurrent();
                   if(isset($udlf_obj)){                     
                    $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $sp_obj_arr = $slf->getCurrent()->data;
                    
                   }

                    $schedule_name_arr = explode('-', $sp_obj_arr['shedule_policy_name']);
                    $status_id = $schedule_name_arr[1];
                    // $shift_id_row = $shift_id_row.'<td>'.$status_id.'</td>';
                    //---Shift In row value
                    $shift_in_row = $shift_in_row . '<td>' . TTDate::getDate('TIME',$row_data_day_key[$date]['min_punch_time_stamp']) . '</td>';


                    //---Shift Out row value
                    $shift_out_row = $shift_out_row . '<td>' . TTDate::getDate('TIME',$row_data_day_key[$date]['max_punch_time_stamp']) . '</td>';


                    $work_hours = $row_data_day_key[$date]['min_punch_time_stamp'] - $row_data_day_key[$date]['max_punch_time_stamp'];
                    /*
                      if($work_hours <= 0 || $work_hours == strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']) ){
                      $work_hours=0;
                      }
                     */

                    if ($work_hours < 0) {
                        $work_hours = gmdate("H:i", abs($work_hours));
                    } else {
                        $work_hours = 0;
                    }

                    /* $dt_hours = '';
                      if($work_hours < 0){
                      $Hr_date = new DateTime();
                      $Hr_date->setTimestamp($work_hours);
                      $dt_hours = $Hr_date->format('H:i');
                      }
                      else{
                      $dt_hours = '';
                      } */

                    $working_hours_row = $working_hours_row . '<td>' . $work_hours . '</td>';

                    //---Late row value
                    //$udlf = TTnew('UserDateListFactory');
                    //$slf = TTnew('ScheduleListFactory');
                    //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                    //$udlf_obj = $udlf->getCurrent();
                    //$slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    //$sp_obj_arr = $slf->getCurrent()->data;

                    $late = '';


                    if (!empty($sp_obj_arr) && $row_data_day_key[$date]['min_punch_time_stamp'] != '') {

                        $hlf = TTnew('HolidayListFactory');
                        $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                        $hday_obj_arr = $hlf->getCurrent()->data;
                        // echo '<pre>';print_r($row_data_day_key);
                        // exit();
                        
                        if(empty($hday_obj_arr)){
                        $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - $row_data_day_key[$date]['min_punch_time_stamp'];
                                   
                        if ($lateSec < 0) {
                            
                            
                            $alf = new AccrualListFactory();
                            
                            $day_check =$row_data_day_key[$date]['date_stamp'];
                            
                            $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                            $ph_date = $ch_date->format('Y-m-d');
                          
                            
                             $alf->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$ph_date);
                             
                             if($alf->getRecordCount() > 0){
                                
                                 $a_obj =  $alf->getCurrent();
                                 
                                 if($a_obj->getAccrualPolicyID()==8)
                                 {
                                    // $tot_array['SH']+=1;
                                    // 
                                     
                                 }
                                 
                                
                            
                             }
                             else{
                                  //$totEarly = $totEarly + abs($lateSec);
                                 $late = gmdate("H:i", abs($lateSec));
                                 $tot_array['MLA'] +=1;
                                 
                             }
                             
                           // $late = gmdate("H:i", abs($lateSec));

                           // $tot_array['MLA'] += 1;
                        }
                        
                        }
                        
                    }
                    $late_row = $late_row . '<td>' . $late . '</td>';


                    //---Early row value
                    //$udlf = TTnew('UserDateListFactory');
                    //$slf = TTnew('ScheduleListFactory');
                    //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                    //$udlf_obj = $udlf->getCurrent();
                    //$slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    //$sp_obj_arr = $slf->getCurrent()->data;

                    $early = '';
                    if (!empty($sp_obj_arr) && $row_data_day_key[$date]['max_punch_time_stamp'] != '') {
                        
                                  $hlf = TTnew('HolidayListFactory');
                        $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                        $hday_obj_arr = $hlf->getCurrent()->data;
                        //  echo '<pre>';print_r($row_data_day_key);'<pre>';
                        //  exit();
                        
                        if(empty($hday_obj_arr)){ 
                        $earlySec = strtotime($row_data_day_key[$date]['shedule_end_time']) - $row_data_day_key[$date]['max_punch_time_stamp'];

                        if ($earlySec > 0) {
                            
                            $alf = new AccrualListFactory();
                            
                            $day_check =$row_data_day_key[$date]['date_stamp'];
                            
                            $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                            $ph_date = $ch_date->format('Y-m-d');
                          
                            
                             $alf->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$ph_date);
                             
                             if($alf->getRecordCount() > 0){
                                /*
                                 $a_obj =  $alf->getCurrent();
                                 
                                 if($a_obj->getAccrualPolicyID()== 8)
                                 {
                                     $tot_array['SH']+=1;
                                     
                                     
                                 }
                                 */
                                
                            
                             }
                             else{
                                  //$totEarly = $totEarly + abs($lateSec);
                                   $early = gmdate("H:i", abs($earlySec));
                                   $tot_array['ELD'] += 1;
                                 
                             }
                          
                        }
                        }
                    }
                    $early_row = $early_row . '<td>' . $early . '</td>';



                    //---Status 1 row value
                    //$status1 = '';
                   // $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - strtotime($row_data_day_key[$date]['min_punch_time_stamp']);
                   // $earlySec = strtotime($row_data_day_key[$date]['shedule_end_time']) - strtotime($row_data_day_key[$date]['max_punch_time_stamp']);

                         
                  
                    
                    $udlf = TTnew('UserDateListFactory');
                    $pclf = TTnew('PunchControlListFactory');
                    $elf = TTnew('ExceptionListFactory'); //--Add code eranda
                    
                    $udlf->getByUserIdAndDate($row['user_id'],$date_stamp);
                    $udlf_obj = $udlf->getCurrent();

                    $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $pc_obj_arr = $pclf->getCurrent()->data;
                  // exit;
                    if(isset($udlf_obj)){
                      $elf->getByUserDateId($udlf_obj->getId());
                      $elf_obj = $elf->getCurrent();
                    }

                    //if punch exists
                    if (!empty($pc_obj_arr) && isset($pc_obj_arr)) {

                        $status1 = 'P';
                       // $tot_array['P']++;
                        
                       
                        $alf_a = new AccrualListFactory();
                        
                        $alf_a->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$date_stamp);
                             //exit;
                          if($alf_a->getRecordCount() > 0){
                               
                             // $status1 ='LV';
                             // $nof_leaves++;
                              
                                 $absLeave_obj_arr = $alf_a->getCurrent()->data;
                                    if (!empty($absLeave_obj_arr)) {
                                       // $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                                       // $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                                                  //  print_r($absLeave_obj_arr);exit;


                                            //$tot_array['L'][]=$i1;
                                            if ($absLeave_obj_arr['accrual_policy_id'] != 8 && $absLeave_obj_arr['type_id']==55) {//Half day leave 


                                                if($absLeave_obj_arr['amount']== -14400){
                                                    // $tot_array['L'] += 0.5;
                                                    // $tot_array['P'] -= 0.5;
                                                      $status1 = 'HL';
                                                      
                                                     // print_r($tot_array['L'] );exit;
                                                }
                                                

                                               
                                            } else if ($absLeave_obj_arr['accrual_policy_id'] == 8) {//Full day leave
                                                // $tot_array['SH'] += 1;
                                                // echo 'bbbb';
                                                 $status1 = 'SH';
                                            } else if ($absLeave_obj_arr['accrual_policy_id'] == 3) {//Short leave
                                                $tot_array['SH'] += 1;
                                            }

                                    }
                          }

                        if (!empty($elf_obj->data)) {
                            //  if($epclf_obj->getExceptionPolicyControlID()) {
                            foreach ($elf as $elf_obj) {
                                if ($elf_obj->getExceptionPolicyID() == '29' || $elf_obj->getExceptionPolicyID() == '5') {
                                  //  $status1 = 'ED'; //Early Departure
                                }
                                if ($elf_obj->getExceptionPolicyID() == '28' || $elf_obj->getExceptionPolicyID() == '4') {
                                  //  $status1 = 'LP'; //Late Presents
                                }
                                if ($elf_obj->getExceptionPolicyID() == '36' || $elf_obj->getExceptionPolicyId() == '37' || $elf_obj->getExceptionPolicyID() == '12' || $elf_obj->getExceptionPolicyID() == '13') {
                                   // $status1 = 'MIS'; //Missed Punch
                                }
                                if ($elf_obj->getExceptionPolicyID() == '25' || $elf_obj->getExceptionPolicyID() == '1') {
                                   // $status1 = 'P'; //Unscheduled absent
                                }
                            }
                        }
                        
                        /*
                            $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                            $hday_obj_arr = $hlf->getCurrent()->data;

                            if (!empty($hday_obj_arr)) {
                                $status1 = 'POH';
                            }
                        */
                        
                    } else {
                        $status1 = 'A';
                        
                        $alf_a = new AccrualListFactory();
                        
                        $alf_a->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$date_stamp);
                             //exit;
                          if($alf_a->getRecordCount() > 0){
                               
                             // $status1 ='LV';
                             // $nof_leaves++;
                              
                                 $absLeave_obj_arr = $alf_a->getCurrent()->data;
                                    if (!empty($absLeave_obj_arr)) {
                                       // $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                                       // $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                                                  //  print_r($absLeave_obj_arr);exit;


                                            //$tot_array['L'][]=$i1;
                                            if ($absLeave_obj_arr['accrual_policy_id'] != 8 && $absLeave_obj_arr['type_id']==55) {//Half day leave 


                                                if($absLeave_obj_arr['amount']== -14400){
                                                     $tot_array['L'] += 0.5;
                                                     $tot_array['P'] -= 0.5;
                                                     $status1 = 'HL';
                                                }
                                                elseif($absLeave_obj_arr['amount']== -28800){
                                                   // $tot_array['L'] += 1;
                                                    $status1 = 'LV';
                                                }
                                                else{
                                                    $tot_array['L'] += abs($absLeave_obj_arr['amount']/28800);
                                                    $status1 = 'LV';
                                                }

                                                
                                            } else{//Full day leave
                                                // $tot_array['SH'] += 1;
                                                // echo 'bbbb';
                                                $ulf = new UserListFactory();
                                                $ulf->getById($row['user_id']);
                                                $uf_obj = $ulf->getCurrent();
                                                
                                               
                                                if(($uf_obj->getTerminationDate() > $Resign_date_stamp)||$uf_obj->getTerminationDate()==''){
                                                  $nopay++;
                                                  $status1 = 'NP';
                                                }
                                            }

                                    }
                          }
                          else{
                              
                              $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                              $hday_obj_arr = $hlf->getCurrent()->data;

                            if (!empty($hday_obj_arr)) {
                                $status1 = 'HLD';
                            }
                            else{
                                
                                $slf_o = TTnew('ScheduleListFactory');
                                
                                if(isset($udlf_obj)){
                                 $slf_o->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                 $sp_obj_arr_a = $slf_o->getCurrent()->data;
                                }
                                
                                
                                $i = 0;
                                 if (!empty($sp_obj_arr_a) && isset($sp_obj_arr_a)) {
                                     
                                    
                                       
                                               $ulf = new UserListFactory();
                                                $ulf->getById($row['user_id']);
                                                $uf_obj = $ulf->getCurrent();
                                                
                                               
                                                if(($uf_obj->getTerminationDate() > $Resign_date_stamp)||$uf_obj->getTerminationDate()==''){
                                                  $nopay++;
                                                  $status1 = 'NP';
                                                }
                                                else{
                                                    $status1 = '';
                                                }
                                     
                                 }
                                 else{
                                     
                                              
                                               $status1 = 'WO';
                                 }
                                 
                                
                                
                                        unset($sp_obj_arr_a);
                            }
                            
                          }
                          
                        
                        
                    }
                    $status1_row = $status1_row . '<td>' . $status1 . '</td>';
                    //---End Status 1 row value  
                    //---Status 2 row value
                    $status2_row = $status2_row . '<td>' . date('D', strtotime($date_stamp)) . '</td>';
                    unset($row_data_day_key[$date]);
                    unset($pc_obj_arr);
                    unset($udlf_obj);
                }
                // end of loop for date array
                
                unset($date_stamp);
//exit;
                $privious_date_stamp='';
                
                $udtlf = TTnew('UserDateTotalListFactory');
                $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate($current_company->getId(), $row['user_id'], 10, date('Y-m-d', $pay_period_start), date('Y-m-d', $pay_period_end));
                if ($udtlf->getRecordCount() > 0) {
                    foreach ($udtlf as $udt_obj) {
                        if ($udt_obj->getOverTimePolicyID() != 0 && $udt_obj->getOverTimePolicyID() != 3 && $udt_obj->getColumn('user_date_stamp') !='' ) {
                            
                           
                            
                            if($privious_date_stamp!=$udt_obj->getColumn('user_date_stamp')){
                                
                                  $privious_date_stamp = $udt_obj->getColumn('user_date_stamp');
                                  $tot_array['OT'] += 1;
                                  
                            }
                        }
                    }
                }

                if (isset($tot_array['P'])) {
                    $nof_presence += $tot_array['P'];
                }

                if (isset($tot_array['LP'])) {
                    $nof_presence += $tot_array['LP'];
                }

                if (isset($tot_array['WO'])) {
                    $nof_weekoffs = $tot_array['WO'];
                }

                if (isset($tot_array['HLD'])) {
                    $nof_holidays = $tot_array['HLD'];
                }

                if (isset($tot_array['L'])) {
                    $nof_leaves = $tot_array['L'];
                }

                if (isset($tot_array['OT'])) {
                    $nof_ot = $tot_array['OT'];
                }

                if (isset($tot_array['H'])) {
                    $nof_half_days = $tot_array['H'];
                }

                if (isset($tot_array['MLA'])) {
                    $no_of_late_attendance = $tot_array['MLA'];
                }

                if (isset($tot_array['ELD'])) {
                    $no_of_early_dep = $tot_array['ELD'];
                }
                if (isset($tot_array['SH'])) {
                    $no_of_short_leaves = $tot_array['SH'];
                }
                if (isset($tot_array['NP'])) {
                    $nopay = $tot_array['NP'];
                }
//print_r($tot_array);
//exit;
                unset($tot_array);
                $nof_absence = $nof_days_for_month - ($nof_presence + $nof_weekoffs + $nof_holidays + $nof_leaves);
                //uncommented - rush
                /*
                  $html_new = $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                  $html_new = $html_new.'<td align="left">'.$row['employee_number'].'</td>';
                  $html_new = $html_new.'<td>'.$row['first_name'].' '.$row['last_name'].'</td>';
                  $html_new = $html_new.'<td colspan="6"> </td>';

                  $html_new = $html_new.'<td colspan="3">'.$nof_presence.'</td>';//correct
                  $html_new = $html_new.'<td colspan="2">'.$nof_leaves.'</td>';//leaves - done halfdays+fulldays
                  $html_new = $html_new.'<td colspan="2">'.$no_of_short_leaves.'</td>';//short leave - done
                  $html_new = $html_new.'<td colspan="2">'.$nopay.'</td>';//no pay - need to check
                  $html_new = $html_new.'<td colspan="2">'.$no_of_late_attendance.'</td>';//late attendance - done
                  $html_new = $html_new.'<td colspan="2">'.$no_of_early_dep.'</td>';//early departure - done
                  $html_new = $html_new.'<td colspan="2">'.$nof_ot.'</td>';//correct
                  $html_new = $html_new.'<td colspan="10"></td>';
                  $html_new = $html_new.'<td colspan="10"></td>';
                  //end


                  $html_new = $html_new.'</tr>';

                 */

                $day_row = $day_row . '<td colspan="2"></td><td colspan="1">Count</td>';
                //-------Day
                $html_new = $html_new . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                $html_new = $html_new . '<td align="left" colspan="3">Date</td>';
                //$html_new = $html_new.'<td></td>';
                $html_new = $html_new . $day_row;
                $html_new = $html_new . '</tr>';
                //$html_new = $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="'.($nof_days_for_month+2).'"></td></tr>';
                //-------Shift ID
                /*
                  $html_new =  $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                  $html_new = $html_new.'<td align="left">Shift ID</td>';
                  $html_new = $html_new.'<td></td>';
                  $html_new = $html_new.$shift_id_row;
                  $html_new = $html_new.'</tr>';

                 */

                //-------Status 2
                $no_of_department_present = $no_of_department_present + $nof_presence;
                $status2_row = $status2_row . '<td colspan="2">Present</td><td colspan="1">' . $nof_presence . '</td>';

                $html_new = $html_new . '<tr  style ="text-align:center" bgcolor="white" nobr="true">';
                $html_new = $html_new . '<td align="left" colspan="3">Day</td>';
                //$html_new = $html_new.'<td></td>';
                $html_new = $html_new . $status2_row;
                $html_new = $html_new . '</tr>';
                //$html_new = $html_new.'<tr style ="text-align:center;" bgcolor="white" nobr="true"><td style ="padding-bottom: 5px;" colspan="'.($nof_days_for_month+2).'"><hr/></td></tr>'; 
                //-------Shift In 
                $no_of_department_leaves = $no_of_department_leaves + $nof_leaves;
                $shift_in_row = $shift_in_row . '<td colspan="2">Leave</td><td colspan="2">' . $nof_leaves . '</td>';

                $html_new = $html_new . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                $html_new = $html_new . '<td align="left" colspan="3">In</td>';
                //$html_new = $html_new.'<td></td>';
                $html_new = $html_new . $shift_in_row;
                $html_new = $html_new . '</tr>';

                //-------Shift Out
                $no_of_department_short_leaves = $no_of_department_short_leaves + $no_of_short_leaves;
                $shift_out_row = $shift_out_row . '<td colspan="2">Short L</td><td colspan="2">' . $no_of_short_leaves . '</td>';

                $html_new = $html_new . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html_new = $html_new . '<td align="left" colspan="3">Out</td>';
                // $html_new = $html_new.'<td></td>';
                $html_new = $html_new . $shift_out_row;
                $html_new = $html_new . '</tr>';


                //-------Shift Out
                $no_of_department_nopay = $no_of_department_nopay + $nopay;
                $working_hours_row = $working_hours_row . '<td colspan="2">No Pay</td><td colspan="2">' . $nopay . '</td>';

                $html_new = $html_new . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html_new = $html_new . '<td align="left" colspan="3">Working Hours</td>';
                //$html_new = $html_new.'<td></td>';
                $html_new = $html_new . $working_hours_row;
                $html_new = $html_new . '</tr>';

                // $html_new = $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="'.($nof_days_for_month+2).'"></td></tr>';
                //-------Status 1
                $no_of_department_ot = $no_of_department_ot + $nof_ot;

                $status1_row = $status1_row . '<td colspan="2">OT</td><td colspan="2">' . $nof_ot . '</td>';

                $html_new = $html_new . '<tr  style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                $html_new = $html_new . '<td align="left" colspan="3">Status </td>';
                //$html_new = $html_new.'<td></td>';
                $html_new = $html_new . $status1_row;
                $html_new = $html_new . '</tr>';

                //-------Late

                $no_of_department_late = $no_of_department_late + $no_of_late_attendance;
                $late_row = $late_row . '<td colspan="2">Late</td><td colspan="2">' . $no_of_late_attendance . '</td>';

                $html_new = $html_new . '<tr  style =" text-align:center" bgcolor="#EEEEEE" nobr="true">';
                $html_new = $html_new . '<td colspan="3" align="left">Late Attendance (minute)</td>';
                //$html_new = $html_new.'<td align="left"></td>'; 
                $html_new = $html_new . $late_row;
                $html_new = $html_new . '</tr>';

                //-------Early

                $no_of_department_early = $no_of_department_early + $no_of_early_dep;
                $early_row = $early_row . '<td colspan="2">Early</td><td colspan="2">' . $no_of_early_dep . '</td>';

                $html_new = $html_new . '<tr  style ="text-align:center" bgcolor="white" nobr="true">';
                $html_new = $html_new . '<td colspan="3" align="left">Early Departure (minute)</td>';
                //$html_new = $html_new.'<td align="left"></td>'; 
                $html_new = $html_new . $early_row;
                $html_new = $html_new . '</tr>';
                //$html_new = $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="'.($nof_days_for_month+2).'"></td></tr>';




                $html_new = $html_new . '</table>';




                $j1++;

                $no_of_emp = $j1;

                if ($j1 % 3 == 0 && $j1 != count($rows)) {
                    $html_new .= '<br pagebreak="true" />';
                }
            }

            $html_new = $html_new . '<br><br><br><b>Department</b>';
            $html_new = $html_new . '<br><br>';
            $html_new = $html_new . '<table border="1" width="50%" >';


            $html_new = $html_new . '<tr>';

            $html_new = $html_new . '<td style ="text-align:center" >No of Emp</td>';
            $html_new = $html_new . '<td style ="text-align:center" >Present</td>';
            $html_new = $html_new . '<td style ="text-align:center" >Leave</td>';
            $html_new = $html_new . '<td style ="text-align:center" >Short L</td>';
            $html_new = $html_new . '<td style ="text-align:center" >No Pay</td>';
            $html_new = $html_new . '<td style ="text-align:center" >OT</td>';
            $html_new = $html_new . '<td style ="text-align:center" >Late</td>';
            $html_new = $html_new . '<td style ="text-align:center" >Early</td>';
            // $html_new=  $html_new.'</td>';

            $html_new = $html_new . '</tr>';


            $html_new = $html_new . '<tr>';

            $html_new = $html_new . '<td style ="text-align:center" >' . $no_of_emp . '</td>';
            $html_new = $html_new . '<td style ="text-align:center" >' . $no_of_department_present . '</td>';
            $html_new = $html_new . '<td style ="text-align:center" >' . $no_of_department_leaves . '</td>';
            $html_new = $html_new . '<td style ="text-align:center" >' . $no_of_department_short_leaves . '</td>';
            $html_new = $html_new . '<td style ="text-align:center" >' . $no_of_department_nopay . '</td>';
            $html_new = $html_new . '<td style ="text-align:center" >' . $no_of_department_ot . '</td>';
            $html_new = $html_new . '<td style ="text-align:center" >' . $no_of_department_late . '</td>';
            $html_new = $html_new . '<td style ="text-align:center" >' . $no_of_department_early . '</td>';
            // $html_new=  $html_new.'</td>';

            $html_new = $html_new . '</tr>';


            $html_new = $html_new . '</table>';
            /*
              $html_new=  $html_new.'<br/><br/>Total Count : '.count($rows);
              $html_new=  $html_new.'<br/><br/><u>Parameters </u>';
              $html_new=  $html_new.'<br/>Time wise (Starting Date & End Date) ';
              $html_new=  $html_new.'<br/>Location wise ';
              $html_new=  $html_new.'<br/>EPF No ';
              $html_new=  $html_new.'<br/>Individual';
              $html_new=  $html_new.'<br/>Department wise';

              $html_new=  $html_new.'<br/><br/><u>Note </u>';
              $html_new=  $html_new.'<br/>individual employee can view their attendance only. ';
              $html_new=  $html_new.'<br/>Admin can access entire attendance. ';
             */

            // output the HTML content
            $pdf->writeHTML($html_new, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');

            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    
    // Added by Thusitha
    
       function MonthlyAttendanceDetailedExcelExport($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company)                                                     {
        
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }
            //    echo "<pre>"; print_r($blf->getNameById($br_id)); die;
            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        $list_start_date = date('d', $pay_period_start);
        $list_end_date = date('d', $pay_period_end);


        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }
        $dates[] = date('d', $current);

      //  echo '<pre>'; print_r($dates); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Attendance Report 2',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 280,
            );

            //$pdf = TTnew('TimeReportHeaderFooter');
            $fileName = 'Monthly Attendance -' . date('Y F', $pay_period_end);

            // set default header data
            //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
            $objPHPExcel = new PHPExcel();

 
            $objPHPExcel->getProperties()->setCreator("Me")->setLastModifiedBy("Me")->setTitle("My Excel Sheet")->setSubject("My Excel Sheet")->setDescription("Excel Sheet")->setKeywords("Excel Sheet")->setCategory("Me");

      
            $objPHPExcel->setActiveSheetIndex(0);



            $j = 0;
            /* foreach ($data as $key => $row) {
              $volume[$key]  = $row['volume'];
              $edition[$key] = $row['edition'];
              }

              // Sort the data with volume descending, edition ascending
              // Add $data as the last parameter, to sort by the common key
              array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data); */
            //Sort array by employee_number
            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows); /**/


            //$pdf->SetFont('', 'B', 6.5);
            
            $row_data_day_key = array();
            $j1 = 0;

            $no_of_emp = 0;
            $no_of_department_present = 0;
            $no_of_department_leaves = 0;
            $no_of_department_short_leaves = 0;
            $no_of_department_nopay = 0;
            $no_of_department_ot = 0;
            $no_of_department_late = 0;
            $no_of_department_early = 0;

            $user_gap = 0;
            $total_gap=0;
            
            foreach ($rows as $row) {

              

                $present_days = 0;
                $absent_days = 0;
                $leave_days = 0;
                $week_off = 0;
                $holidays = 0;
                
                

                foreach ($row['data'] as $row1) {
                    
                  //  print_r($row['data']); exit;


                    if ($row1['date_stamp'] != '') {
                        //echo $row1['date_stamp'];
                        //exit();
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d', strtotime($row_dt));
                        //echo '<br><pre>'.$dat_day;
                        $row_data_day_key[$dat_day] = $row1;

                        //    $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                    } else {
                        $tot_ot_hours_data = $row1['over_time'];
                        $tot_worked_actual_hours_data = $row1['actual_time'];
                        $tot_worked_hours_data = explode(':', $row1['worked_time']);
                        $tot_worked_sec_data = ($tot_worked_hours_data[0] * 60 * 60) + ($tot_worked_hours_data[1] * 60);
                    }
                }



                $nof_presence = 0;
                $nof_absence = 0;
                $nof_leaves = 0;
                $nof_weekoffs = 0;
                $nof_holidays = 0;
                $nof_ot = 0;
                $no_of_late_attendance = 0;
                $no_of_early_dep = 0;
                $no_of_short_leaves = 0;
                $nopay = 0;

                $day_row = '';
                $shift_id_row = '';
                $shift_in_row = '';
                $shift_out_row = '';
                $late_row = '';
                $early_row = '';
                $status1_row = '';
                $status2_row = '';
                $nof_half_days = 0;
                $working_hours_row = '';

                $earlySec = $lateSec = 0;
                
                $day_exc = '';
                $status2_exc = '';
                $shift_in_exc = '';
                $shift_out_exc = '';
                $working_hours_exc = '';
                $status1_exc = '';
                $late_exc  = '';
                $early_exc  = '';

                //  for($i1=$list_start_date; $i1<=$list_end_date; $i1++){
              //  foreach ($row_data_day_key as $i1 => $row_data) {
                
               // $date_check_start = new DateTime();
                //$date_check_start->setTimestamp($pay_period_start);
                //$date_stamp= $date_check_start->format('Y-m-d');
                $array_cell = array('B','C','D','E','F','G','H','I','J','K','L', 'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF' ,'AG');
                
                $aa = 0;
                
                foreach($dates as $date){

                    
                    $row_data = $row_data_day_key[$date];
                
                // echo '<pre>'.print_r($row_data_day_key);exit;
                    //---Get Total values date_stamp
                    $status1 = '';



                    $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - strtotime($row_data_day_key[$date]['min_punch_time_stamp']);
                    $earlySec = strtotime($row_data_day_key[$date]['shedule_end_time']) - strtotime($row_data_day_key[$date]['max_punch_time_stamp']);

                    $udlf = TTnew('UserDateListFactory');
                    $pclf = TTnew('PunchControlListFactory');

                    //$date_stamp =date('Y-m-d',  strtotime($row_data['date_stamp']));
                    // $Hr_date = new DateTime();
                    
                    if(isset($row_data['date_stamp'])){
                        $Hr_date = DateTime::createFromFormat('d/m/Y', $row_data['date_stamp']);
                        $date_stamp = $Hr_date->format('Y-m-d');
                        
                        $udlf->getByUserIdAndDate($row['user_id'], $date_stamp);
                        $udlf_obj = $udlf->getCurrent();

                        $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                        $pc_obj_arr = $pclf->getCurrent()->data;
                    }
                    else{
                        //$current = strtotime('+1 day', $date_stamp);
                        
                        if(isset($date_stamp)){
                            $date_check = new DateTime($date_stamp);
                            $date_check->add(new DateInterval('P1D'));
                            $date_stamp= $date_check->format('Y-m-d');
                        }
                        else{
                            $date_check_start = new DateTime();
                            $date_check_start->setTimestamp($pay_period_start);
                            $date_stamp= $date_check_start->format('Y-m-d');
                        }
                        //echo $date_stamp;exit;
                    }



                    // echo $date_stamp.' '.$row_data['date_stamp'].'<br>';





                    //if punch exists
                    if (!empty($pc_obj_arr)) {
                        $status1 = 'P';
                        //check late come and early departure
                        $elf = TTnew('ExceptionListFactory');
                        $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                        $ex_obj_arr = $elf->getCurrent()->data;
                        if (!empty($ex_obj_arr)) {
                            $status1 = 'LP';
                        }
                       
                        $alf = new AccrualListFactory();
                        
                        
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$date_stamp);
                        $absLeave_obj_arr = $alf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                            
                         
                            if ($absLeave_obj_arr['accrual_policy_id'] == 8 && $absLeave_obj_arr['type_id']==55) {//Full day leave
                                
                                  
                                     //$tot_array['SH'] = $tot_array['SH']  + 1;
                                     $status1 = 'SH';
                                    // echo $date_stamp.'<br>'.$tot_array['SH']; 
                            }
                            else{
                                    if($absLeave_obj_arr['amount']== -14400){
                                         $tot_array['L'] += 0.5;
                                         $tot_array['P'] -= 0.5;
                                    }else{ 
                                        $tot_array['L'] += abs($absLeave_obj_arr['amount']/28800);
                                        $status1 = 'LV';
                                    }
                                // echo $date_stamp.'<br>';
                            }
                                    
                        }
                        
                    } else {
                        $status1 = 'WO';

                        $alf = new AccrualListFactory();
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$date_stamp);
                        
                        $absLeave_obj_arr = $alf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                           // $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                           // $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                                      //  print_r($absLeave_obj_arr);exit;

                            
                                //$tot_array['L'][]=$i1;
                                if ($absLeave_obj_arr['accrual_policy_id'] != 8 && $absLeave_obj_arr['type_id']==55) {//Half day leave 
                                   // $tot_array['L'] += 0.5;
                                   // $tot_array['P'] += 0.5;
                                   // $tot_array['H'] += 1;
                                    
                                    //$nof_leaves += 0.5;
                                    //$nof_presence += 0.5;
                                    
                                    if($absLeave_obj_arr['amount']== -14400){
                                         $tot_array['L'] += 0.5;
                                         $tot_array['P'] -= 0.5;
                                    }
                                    elseif($absLeave_obj_arr['amount']== -28800){
                                        $tot_array['L'] += 1;
                                    }
                                    else{
                                        $tot_array['L'] += abs($absLeave_obj_arr['amount']/28800);
                                    }
                                    
                                    $status1 = 'HL';
                                } else if ($absLeave_obj_arr['accrual_policy_id'] == 8) {//Full day leave
                                    // $tot_array['SH'] += 1;
                                    // echo 'bbbb';
                                    // $status1 = 'SH';
                                } else if ($absLeave_obj_arr['accrual_policy_id'] == 3) {//Short leave
                                   // $tot_array['SH'] += 1;
                                }
                            
                        }
                    }


                    $hlf = TTnew('HolidayListFactory');
                    $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                    $hday_obj_arr = $hlf->getCurrent()->data;

                    if (!empty($hday_obj_arr) && empty($pc_obj_arr)) {
                        $status1 = 'HLD';
                    }
                    $tot_array[$status1] += 1;
                    //---End Get Total values
                    //---Day row value
                    $day_row = $day_row . '<td>' . $date . '</td>';
                   // $day_exc = $day_exc .' ' . $date . ' ';
                    
                    $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +4), $date); 
                    

                    


                    //---Shift ID row value
                    //$udlf = TTnew('UserDateListFactory');
                    $slf = TTnew('ScheduleListFactory');

                    //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                    //$udlf_obj = $udlf->getCurrent();
                   if(isset($udlf_obj)){                     
                    $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $sp_obj_arr = $slf->getCurrent()->data;
                    
                   }

                    $schedule_name_arr = explode('-', $sp_obj_arr['shedule_policy_name']);
                    $status_id = $schedule_name_arr[1];
                    // $shift_id_row = $shift_id_row.'<td>'.$status_id.'</td>';
                    //---Shift In row value
                    //$shift_in_row = $shift_in_row . '<td>' . $row_data_day_key[$date]['min_punch_time_stamp'] . '</td>';
                   // $shift_in_exc = $shift_in_exc . ' ' . $row_data_day_key[$date]['min_punch_time_stamp'] . ' ';
                    
                    $in_turm ='';
                    if(isset($row_data_day_key[$date]['min_punch_time_stamp']) && $row_data_day_key[$date]['min_punch_time_stamp']!=''){
                        $in_turm = TTDate::getDate('TIME',$row_data_day_key[$date]['min_punch_time_stamp']);
                    }
                    
                    
                    
                    $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +6), $in_turm);

                    //---Shift Out row value
                    
                    $out_turm ='';
                    if(isset($row_data_day_key[$date]['max_punch_time_stamp']) && $row_data_day_key[$date]['max_punch_time_stamp']!=''){
                        $out_turm = TTDate::getDate('TIME',$row_data_day_key[$date]['max_punch_time_stamp']);
                        
                     
                    }
                    
                    //$shift_out_row = $shift_out_row . '<td>' . $row_data_day_key[$date]['max_punch_time_stamp'] . '</td>';
                    //$shift_out_exc = $shift_out_exc . ' ' . $row_data_day_key[$date]['max_punch_time_stamp'] . ' ';

                   $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +7), $out_turm);

                    $work_hours = $row_data_day_key[$date]['min_punch_time_stamp'] - $row_data_day_key[$date]['max_punch_time_stamp'];
                   

                    if ($work_hours < 0) {
                        $work_hours = gmdate("H:i", abs($work_hours));
                    } else {
                        $work_hours = 0;
                    }

         
                    
                    $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +8), $work_hours);

                  

                    $late = '';


                    if (!empty($sp_obj_arr) && $row_data_day_key[$date]['min_punch_time_stamp'] != '') {

                        //  print_r($row_data_day_key);
                        //  exit();
                        
                                   $hlf = TTnew('HolidayListFactory');
                        $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                        $hday_obj_arr = $hlf->getCurrent()->data;
                        //  print_r($row_data_day_key);
                        //  exit();
                        
                        if(empty($hday_obj_arr)){
                        $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - $row_data_day_key[$date]['min_punch_time_stamp'];
                                   
                        if ($lateSec < 0) {
                            
                            
                            $alf = new AccrualListFactory();
                            
                            $day_check =$row_data_day_key[$date]['date_stamp'];
                            
                            $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                            $ph_date = $ch_date->format('Y-m-d');
                          
                            
                             $alf->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$ph_date);
                             
                             if($alf->getRecordCount() > 0){
                                
                                 $a_obj =  $alf->getCurrent();
                                 
                                 if($a_obj->getAccrualPolicyID()==8)
                                 {
                                    // $tot_array['SH']+=1;
                                    // 
                                     
                                 }
                                 
                                
                            
                             }
                             else{
                                  //$totEarly = $totEarly + abs($lateSec);
                                 $late = gmdate("H:i", abs($lateSec));
                                 $tot_array['MLA'] +=1;
                                 
                             }
                             
                           // $late = gmdate("H:i", abs($lateSec));

                           // $tot_array['MLA'] += 1;
                        }
                        
                        
                        }
                    }
                   
                    
                    $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +10), $late);


                    //---Early row value
                    //$udlf = TTnew('UserDateListFactory');
                    //$slf = TTnew('ScheduleListFactory');
                    //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                    //$udlf_obj = $udlf->getCurrent();
                    //$slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    //$sp_obj_arr = $slf->getCurrent()->data;

                    $early = '';
                    if (!empty($sp_obj_arr) && $row_data_day_key[$date]['max_punch_time_stamp'] != '') {
                      
                        $hlf = TTnew('HolidayListFactory');
                        $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                        $hday_obj_arr = $hlf->getCurrent()->data;
                        //  print_r($row_data_day_key);
                        //  exit();
                        
                        if(empty($hday_obj_arr)){
                        
                        $earlySec = strtotime($row_data_day_key[$date]['shedule_end_time']) - $row_data_day_key[$date]['max_punch_time_stamp'];

                        if ($earlySec > 0) {
                            
                            $alf = new AccrualListFactory();
                            
                            $day_check =$row_data_day_key[$date]['date_stamp'];
                            
                            $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                            $ph_date = $ch_date->format('Y-m-d');
                          
                            
                             $alf->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$ph_date);
                             
                             if($alf->getRecordCount() > 0){
                                /*
                                 $a_obj =  $alf->getCurrent();
                                 
                                 if($a_obj->getAccrualPolicyID()== 8)
                                 {
                                     $tot_array['SH']+=1;
                                     
                                     
                                 }
                                 */
                                
                            
                             }
                             else{
                                  //$totEarly = $totEarly + abs($lateSec);
                                   $early = gmdate("H:i", abs($earlySec));
                                   $tot_array['ELD'] += 1;
                                 
                             }
                          
                        }
                    }
                    }
                    
                    $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +11), $early);


                    //---Status 1 row value
                    //$status1 = '';
                   // $lateSec = strtotime($row_data_day_key[$date]['shedule_start_time']) - strtotime($row_data_day_key[$date]['min_punch_time_stamp']);
                   // $earlySec = strtotime($row_data_day_key[$date]['shedule_end_time']) - strtotime($row_data_day_key[$date]['max_punch_time_stamp']);

                    //$udlf = TTnew('UserDateListFactory');
                    //$pclf = TTnew('PunchControlListFactory');
                    $elf = TTnew('ExceptionListFactory'); //--Add code eranda
                    //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                    //$udlf_obj = $udlf->getCurrent();

                   // $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $pc_obj_arr = $pclf->getCurrent()->data;
                   
                    if(isset($udlf_obj)){
                    $elf->getByUserDateId($udlf_obj->getId());
                    $elf_obj = $elf->getCurrent();
                    }

                    //if punch exists
                    if (!empty($pc_obj_arr) && isset($pc_obj_arr)) {

                        $status1 = 'P';

                        if (!empty($elf_obj->data)) {
                            //  if($epclf_obj->getExceptionPolicyControlID()) {
                            foreach ($elf as $elf_obj) {
                                if ($elf_obj->getExceptionPolicyID() == '29' || $elf_obj->getExceptionPolicyID() == '5') {
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
                        
                        /*
                            $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                            $hday_obj_arr = $hlf->getCurrent()->data;

                            if (!empty($hday_obj_arr)) {
                                $status1 = 'POH';
                            }
                        */
                        
                    } else {
                        $status1 = 'A';
                        
                         $alf_a = new AccrualListFactory();
                        
                        $alf_a->getByAccrualByUserIdAndTypeIdAndDate($row['user_id'],55,$date_stamp);
                             
                          if($alf_a->getRecordCount() > 0){
                               
                               $absLeave_obj_arr = $alf_a->getCurrent()->data;
                                    if (!empty($absLeave_obj_arr)) {
                                       // $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                                       // $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                                                  //  print_r($absLeave_obj_arr);exit;


                                            //$tot_array['L'][]=$i1;
                                            if ($absLeave_obj_arr['accrual_policy_id'] != 8 && $absLeave_obj_arr['type_id']==55) {//Half day leave 


                                                if($absLeave_obj_arr['amount']== -14400){
                                                     $tot_array['L'] += 0.5;
                                                     $tot_array['P'] -= 0.5;
                                                     $status1 = 'HL';
                                                }
                                                elseif($absLeave_obj_arr['amount']== -28800){
                                                   // $tot_array['L'] += 1;
                                                    $status1 = 'LV';
                                                }
                                                else{
                                                    $tot_array['L'] += abs($absLeave_obj_arr['amount']/28800);
                                                    $status1 = 'LV';
                                                }

                                                
                                            } else{//Full day leave
                                                // $tot_array['SH'] += 1;
                                                // echo 'bbbb';
                                                $ulf = new UserListFactory();
                                                $ulf->getById($row['user_id']);
                                                $uf_obj = $ulf->getCurrent();
                                                
                                               
                                                if(($uf_obj->getTerminationDate() > $Resign_date_stamp)||$uf_obj->getTerminationDate()==''){
                                                  $nopay++;
                                                  $status1 = 'NP';
                                                }
                                                else{
                                                    $status1 = '';
                                                }
                                            }

                                    }
                          }
                          else{
                              
                              $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], $date_stamp);
                              $hday_obj_arr = $hlf->getCurrent()->data;

                            if (!empty($hday_obj_arr)) {
                                $status1 = 'HLD';
                            }
                            else{
                                
                                $slf_o = TTnew('ScheduleListFactory');
                                
                                if(isset($udlf_obj)){
                                 $slf_o->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                 $sp_obj_arr_a = $slf_o->getCurrent()->data;
                                }
                                
                                
                                $i = 0;
                                 if (!empty($sp_obj_arr_a) && isset($sp_obj_arr_a)) {
                                     
                                    
                                      $ulf = new UserListFactory();
                                                $ulf->getById($row['user_id']);
                                                $uf_obj = $ulf->getCurrent();
                                                
                                               
                                                if(($uf_obj->getTerminationDate() > $Resign_date_stamp)||$uf_obj->getTerminationDate()==''){
                                                  $nopay++;
                                                  $status1 = 'NP';
                                                }
                                                else{
                                                    $status1 = '';
                                                }
                                     
                                                
                                               
                                               
                                     
                                 }
                                 else{
                                     
                                                $ulf = new UserListFactory();
                                                $ulf->getById($row['user_id']);
                                                $uf_obj = $ulf->getCurrent();
                                                
                                               
                                                if(($uf_obj->getTerminationDate() > $Resign_date_stamp)||$uf_obj->getTerminationDate()==''){
                                                  $nopay++;
                                                  $status1 = '';
                                                }
                                                else{
                                                   $status1 = 'WO';
                                                }
                                               
                                 }
                                 
                                
                                
                                        unset($sp_obj_arr_a);
                            }
                          }
                          
                        
                        
                    }
                    
                    $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +9), $status1);
                    
                    //---End Status 1 row value  
                    //---Status 2 row value
                    
                    
                    $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +5), date('D', strtotime($date_stamp)));
                    unset($row_data_day_key[$date]);
                    unset($pc_obj_arr);
                    unset($udlf_obj);
                    
                    $aa++;
                }
                                
                // end of loop for date array
                
                unset($date_stamp);
//exit;
                $privious_date_stamp ='';
                $udtlf = TTnew('UserDateTotalListFactory');
                $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate($current_company->getId(), $row['user_id'], 10, date('Y-m-d', $pay_period_start), date('Y-m-d', $pay_period_end));
                if ($udtlf->getRecordCount() > 0) {
                    foreach ($udtlf as $udt_obj) {
                        if ($udt_obj->getOverTimePolicyID() != 0 && $udt_obj->getOverTimePolicyID() != 3 && $udt_obj->getColumn('user_date_stamp') !='' ) {
                            
                           
                            
                            if($privious_date_stamp!=$udt_obj->getColumn('user_date_stamp')){
                                
                                  $privious_date_stamp = $udt_obj->getColumn('user_date_stamp');
                                  $tot_array['OT'] += 1;
                                  
                            }
                        }
                    }
                }

                if (isset($tot_array['P'])) {
                    $nof_presence += $tot_array['P'];
                }

                if (isset($tot_array['LP'])) {
                    $nof_presence += $tot_array['LP'];
                }

                if (isset($tot_array['WO'])) {
                    $nof_weekoffs = $tot_array['WO'];
                }

                if (isset($tot_array['HLD'])) {
                    $nof_holidays = $tot_array['HLD'];
                }

                if (isset($tot_array['L'])) {
                    $nof_leaves = $tot_array['L'];
                }

                if (isset($tot_array['OT'])) {
                    $nof_ot = $tot_array['OT'];
                }

                if (isset($tot_array['H'])) {
                    $nof_half_days = $tot_array['H'];
                }

                if (isset($tot_array['MLA'])) {
                    $no_of_late_attendance = $tot_array['MLA'];
                }

                if (isset($tot_array['ELD'])) {
                    $no_of_early_dep = $tot_array['ELD'];
                }
                if (isset($tot_array['SH'])) {
                    $no_of_short_leaves = $tot_array['SH'];
                }
                if (isset($tot_array['NP'])) {
                    $nopay = $tot_array['NP'];
                }
//print_r($tot_array);
//exit;
                unset($tot_array);
                $nof_absence = $nof_days_for_month - ($nof_presence + $nof_weekoffs + $nof_holidays + $nof_leaves);
                //uncommented - rush
                /*
                  $html_new = $html_new.'<tr style ="text-align:center" bgcolor="white" nobr="true">';
                  $html_new = $html_new.'<td align="left">'.$row['employee_number'].'</td>';
                  $html_new = $html_new.'<td>'.$row['first_name'].' '.$row['last_name'].'</td>';
                  $html_new = $html_new.'<td colspan="6"> </td>';

                  $html_new = $html_new.'<td colspan="3">'.$nof_presence.'</td>';//correct
                  $html_new = $html_new.'<td colspan="2">'.$nof_leaves.'</td>';//leaves - done halfdays+fulldays
                  $html_new = $html_new.'<td colspan="2">'.$no_of_short_leaves.'</td>';//short leave - done
                  $html_new = $html_new.'<td colspan="2">'.$nopay.'</td>';//no pay - need to check
                  $html_new = $html_new.'<td colspan="2">'.$no_of_late_attendance.'</td>';//late attendance - done
                  $html_new = $html_new.'<td colspan="2">'.$no_of_early_dep.'</td>';//early departure - done
                  $html_new = $html_new.'<td colspan="2">'.$nof_ot.'</td>';//correct
                  $html_new = $html_new.'<td colspan="10"></td>';
                  $html_new = $html_new.'<td colspan="10"></td>';
                  //end


                  $html_new = $html_new.'</tr>';

                 */
                //$day_row = $day_row . '<td colspan="2"></td><td colspan="1">Count</td>';
                //$ii = $count +1;
            	//$objPHPExcel->getActiveSheet()->setCellValue('CB'.$ii, $day_exc)
                //$ii = $count +2;
            	//$objPHPExcel->getActiveSheet()->setCellValue('CB'.$ii, $row['employee_number']);
                $objPHPExcel->getActiveSheet()
                         
                ->setCellValue('A'.($user_gap +1), $row['employee_number'])        
                ->setCellValue('A'.($user_gap +2), $row['first_name'].'  '.$row['last_name'])
                ->setCellValue('A'.($user_gap +4), 'Date')
                //->setCellValue('B'.($user_gap +4), $day_exc)
                ->setCellValue('A'.($user_gap +5), 'Day')
                //->setCellValue('B'.($user_gap +5), $status2_exc)
                ->setCellValue('A'.($user_gap +6), 'In')
                //->setCellValue('B'.($user_gap +6), $shift_in_exc)
                ->setCellValue('A'.($user_gap +7), 'Out')
                //->setCellValue('B'.($user_gap +7), $shift_out_exc)
                ->setCellValue('A'.($user_gap +8), 'Working Hours')
                //->setCellValue('B'.($user_gap +8), $working_hours_exc)
                ->setCellValue('A'.($user_gap +9), 'Status')
                //->setCellValue('B'.($user_gap +9), $status1_exc)
                ->setCellValue('A'.($user_gap +10), 'Late Attendance(minute)')
                //->setCellValue('B'.($user_gap +10), $late_exc)
                ->setCellValue('A'.($user_gap +11), 'Early Departure(minute)');
                //->setCellValue('B'.($user_gap +11), $early_exc);
               




                $j1++;

                $no_of_emp = $j1;

               
                
                
                 $user_gap += 15;
                 $total_gap+=15;
            }
            
            if($no_of_emp > 1){
            $objPHPExcel->getActiveSheet()
 
            ->setCellValue('A'.($total_gap), 'No of Emp')
            ->setCellValue('B'.($total_gap), 'Present')
            ->setCellValue('C'.($total_gap), 'Leave')
            ->setCellValue('D'.($total_gap), 'Short L')
            ->setCellValue('E'.($total_gap), 'No Pay')
            ->setCellValue('F'.($total_gap), 'OT')
            ->setCellValue('G'.($total_gap), 'Late')
            ->setCellValue('H'.($total_gap), 'Early');


            $objPHPExcel->getActiveSheet()->setCellValue('A'.($total_gap+1), $no_of_emp)
            ->setCellValue('B'.($total_gap+1), $no_of_department_present)
            ->setCellValue('C'.($total_gap+1), $no_of_department_leaves)
            ->setCellValue('D'.($total_gap+1), $no_of_department_short_leaves)
            ->setCellValue('E'.($total_gap+1), $no_of_department_nopay)
            ->setCellValue('F'.($total_gap+1), $no_of_department_ot)
            ->setCellValue('G'.($total_gap+1), $no_of_department_late)
            ->setCellValue('H'.($total_gap+1), $no_of_department_early);
            }
           
   

            //return FALSE;
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        }
    }  
    
    
    
    function EmployeeAttendanceSummery($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }
            //    echo "<pre>"; print_r($blf->getNameById($br_id)); die;
            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        $list_start_date = date('d', $pay_period_start);
        $list_end_date = date('d', $pay_period_end);


        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }

        //echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                // 'payperiod_end_date'   => date('Y-M',$pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Employee Attendance Summery',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

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
            $pdf->AddPage('l', 'mm', 'A2');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(50, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';


            /* foreach ($data as $key => $row) {
              $volume[$key]  = $row['volume'];
              $edition[$key] = $row['edition'];
              }

              // Sort the data with volume descending, edition ascending
              // Add $data as the last parameter, to sort by the common key
              array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data); */

            //Sort array by employee_number
            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows); /**/


            $pdf->SetFont('', 'B', 6.5);


            $row_data_day_key = array();
            $j1 = 0;



            $no_of_emp = 0;

            // echo '<pre>'; print_r($rows); echo '<pre>'; die;
            $html = '';

            foreach ($rows as $emp_row) {
                //  echo '<pre>'; print_r($emp_row); echo '<pre>'; die;


                $html = $html . '<br><br><table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td align="left">' . $emp_row['employee_number'] . '</td></tr>'
                        . '<tr><td>' . $emp_row['first_name'] . ' ' . $emp_row['last_name'] . '</td></tr></table>';
                $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="100%">
                        <thead style="background-color:#CCCCCC;text-align:center;"><tr style="background-color:#CCCCCC;text-align:center;" >';
//                $html = $html.'<td width = "3%">#</td>';
                $html = $html . '<td height="17" width = "5%" style="text-align:center;">Month</td>';
                $html = $html . '<td width = "12%" style="text-align:center;">Working Hours</td>';
                $html = $html . '<td width = "12%" style="text-align:center;">Late arrival (minutes)</td>';
                $html = $html . '<td  width = "12%" style="text-align:center;">Early Departure (minutes) </td>';

                $html = $html . '<td  width = "8%" style="text-align:center;">Present</td>';
                $html = $html . '<td  width = "8%" style="text-align:center;">Leave</td>';
                $html = $html . '<td  width = "8%" style="text-align:center;">Short L</td>';
                $html = $html . '<td  width = "8%" style="text-align:center;">No Pay</td>';
                $html = $html . '<td  width = "8%" style="text-align:center;">OT</td>';
                $html = $html . '<td  width = "8%" style="text-align:center;">Late</td>';
                $html = $html . '<td  width = "8%" style="text-align:center;">Early</td>';

                $html = $html . '</tr>';
                $html = $html . '</thead>';

                $month_report = '';
                $no_of_pay_periods_present = 0;
                $no_of_pay_periods_leaves = 0;
                $no_of_pay_periods_short_leaves = 0;
                $no_of_pay_periods_nopay = 0;
                $no_of_pay_periods_ot = 0;
                $no_of_pay_periods_late = 0;
                $no_of_pay_periods_early = 0;
                $total_late_time = 0;
                $total_early_time = 0;
                $total_work_time = 0;
                $work_times = array();

                foreach ($emp_row['data'] as $pay_periods_id => $attendance) {

                    //   echo '<pre>'; print_r($attendance_data); echo '<pre>'; die;
                    // $attendance_data =array();

                    foreach ($attendance as $key => $row) {
                        if ($row['date_stamp'] != '') {

                            $attendance_date[] = $row['date_stamp'];
                            $attendance_data[] = $row;
                        }
                    }

                    array_multisort($attendance_date, SORT_ASC, $attendance_data); /**/
                    // echo '<pre>'; print_r($attendance_data); echo '<pre>'; die;

                    foreach ($attendance_data as $day_attendance) {

                        if ($day_attendance['date_stamp'] != '') {

                            //  echo '<pre>'; print_r($day_attendance); echo '<pre>'; die;

                            $work_times[]=$day_attendance['worked_time'];
                            $udlf = TTnew('UserDateListFactory');
                            $pclf = TTnew('PunchControlListFactory');

                            $day = strtotime($day_attendance['date_stamp']);
                            // $Hr_date = new DateTime();
                            //$Hr_date->setTimestamp($day);
                            $Hr_date = DateTime::createFromFormat('d/m/Y', $day_attendance['date_stamp']);
                            $date_stamp = $Hr_date->format('Y-m-d');
                            // $month_report=  $Hr_date->format('Y-M');





                            $udlf->getByUserIdAndDate($emp_row['user_id'], $date_stamp);
                            $udlf_obj = $udlf->getCurrent();

                            if(isset($pc_obj_arr)){
                                unset($pc_obj_arr);
                            }
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;

                          

                            $slf = TTnew('ScheduleListFactory');

                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;

                            $schedule_name_arr = explode('-', $sp_obj_arr['shedule_policy_name']);
                            $status_id = $schedule_name_arr[1];

                            if (!empty($pc_obj_arr)) {

                                // echo $date_stamp.' '.$day_attendance['date_stamp'].'<br>';

                                $no_of_pay_periods_present +=  1;
                                
                                
                                
                                
                                

                                if (isset($day_attendance['over_time']) && $day_attendance['over_time'] != '') {
                                    $no_of_pay_periods_ot = $no_of_pay_periods_ot + 1;
                                }

                                $work_hours = 0;
                                $work_hours = strtotime($day_attendance['min_punch_time_stamp']) - strtotime($day_attendance['max_punch_time_stamp']);
                                
                                
                        $alf = new AccrualListFactory();
                        
                        
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$date_stamp);
                        $absLeave_obj_arr = $alf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                            
                         
                            if ($absLeave_obj_arr['accrual_policy_id'] == 8 && $absLeave_obj_arr['type_id']==55) {//Full day leave
                                
                                  $no_of_pay_periods_short_leaves = $no_of_pay_periods_short_leaves + 1;
                                     //$tot_array['SH'] = $tot_array['SH']  + 1;
                                    
                                    // echo $date_stamp.'<br>'.$tot_array['SH']; 
                            }
                            else{
                                    if($absLeave_obj_arr['amount']== -14400){
                                         $no_of_pay_periods_leaves += 0.5;
                                         $no_of_pay_periods_present -= 0.5;
                                    }else{ 
                                        $no_of_pay_periods_leaves += abs($absLeave_obj_arr['amount']/28800);
                                        
                                    }
                                // echo $date_stamp.'<br>';
                            }
                                    
                        }
/*
                                if ($work_hours < 0) {

                                    $total_work_time = $total_work_time + $work_hours;
                                    $work_times[] = gmdate("H:i:s", abs($work_hours));
                                } else {
                                    $work_hours = 0;
                                }
 
 */
                                //echo gmdate("d", abs($total_work_time)).'<br>';

                                if (!empty($sp_obj_arr) && $day_attendance['min_punch_time_stamp'] != "") {
                                    $lateSec = strtotime($day_attendance['shedule_start_time']) - strtotime($day_attendance['min_punch_time_stamp']);
                                    if ($lateSec < 0) {
                                        
                                        $alf = new AccrualListFactory();
                                        $day_check =$day_attendance['date_stamp'];
                                        $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                                        $ph_date = $ch_date->format('Y-m-d');
                                        
                                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$ph_date);
                                        
                                        if($alf->getRecordCount() <1){
                                           $total_late_time = $total_late_time + $lateSec;
                                           $no_of_pay_periods_late = $no_of_pay_periods_late + 1;
                                        }
                                    }
                                }


                                $earlySec = strtotime($day_attendance['shedule_end_time']) - strtotime($day_attendance['max_punch_time_stamp']);

                                $early = '';
                                if (!empty($sp_obj_arr) && $day_attendance['max_punch_time_stamp'] != '') {
                                    $earlySec = strtotime($day_attendance['shedule_end_time']) - strtotime($day_attendance['max_punch_time_stamp']);

                                    if ($earlySec > 0) {

                                                                                $alf = new AccrualListFactory();
                                        $day_check =$day_attendance['date_stamp'];
                                        $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                                        $ph_date = $ch_date->format('Y-m-d');
                                        
                                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$ph_date);
                                        
                                        if($alf->getRecordCount() <1){
                                             $no_of_pay_periods_early += 1;
                                            $total_early_time = $total_early_time + $earlySec;
                                        }
                                    }
                                }
                            } else {

                                $hlf = new HolidayListFactory();

                                $alf = new AccrualListFactory();
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$date_stamp);
                        
                        $absLeave_obj_arr = $alf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                           // $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                           // $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                                      //  print_r($absLeave_obj_arr);exit;

                            
                                //$tot_array['L'][]=$i1;
                                if ($absLeave_obj_arr['accrual_policy_id'] != 8 && $absLeave_obj_arr['type_id']==55) {//Half day leave 
                                   
                                    
                                    if($absLeave_obj_arr['amount']== -14400){
                                         $no_of_pay_periods_leaves += 0.5;
                                         $no_of_pay_periods_present -= 0.5;
                                    }
                                    elseif($absLeave_obj_arr['amount']== -28800){
                                           $no_of_pay_periods_leaves += 1;
                                    }
                                    else{
                                          $no_of_pay_periods_leaves  += abs($absLeave_obj_arr['amount']/28800);
                                    }
                                    
                                    $status1 = 'HL';
                                } else if ($absLeave_obj_arr['accrual_policy_id'] == 8) {//Full day leave
                                    // $tot_array['SH'] += 1;
                                    // echo 'bbbb';
                                    // $status1 = 'SH';
                                } else if ($absLeave_obj_arr['accrual_policy_id'] == 3) {//Short leave
                                   // $tot_array['SH'] += 1;
                                }
                            
                        }
                        
                        

                               /* 
                                $lrlf = new LeaveRequestListFactory();
                                $lrlf->checkUserHasLeaveForDay($emp_row['user_id'], $date_stamp);

                                if ($lrlf->getRecordCount() > 0) {

                                    $lrf_obj = $lrlf->getCurrent();

                                    if ($lrf_obj->getLeaveMethod() == 1) {
                                        $no_of_pay_periods_leaves = $no_of_pay_periods_leaves + 1;
                                    } else if ($lrf_obj->getLeaveMethod() == 2) {

                                        $no_of_pay_periods_leaves = $no_of_pay_periods_leaves + 0.5;
                                    } else if ($lrf_obj->getLeaveMethod() == 3) {

                                        $no_of_pay_periods_short_leaves = $no_of_pay_periods_short_leaves + 1;
                                    }
                                } else {

                                    $no_of_pay_periods_nopay = $no_of_pay_periods_nopay + 1;
                                }
                                
                                */
                            }
                        }// end of daystamp if
                    }

                    //  echo $month_report.' '.$no_of_pay_periods_present.'<br>';

                    $counter = new times_counter($work_times);
                    // echo $counter->get_total_time();

                    $pplf = new PayPeriodListFactory();
                    $pplf->getById($pay_periods_id);
                    $pay_period_obj = $pplf->getCurrent();

                    // echo $pay_period_obj->getEndDate();
                    $Hr_date = new DateTime();
                    $Hr_date->setTimestamp($pay_period_obj->getEndDate());
                    $month_report = $Hr_date->format('Y-M');

                    $total_late_time = gmdate("H:i", abs($total_late_time));

                    $total_early_time = gmdate("H:i", abs($total_early_time));

                    $total_work_time = gmdate("H:i", abs($total_work_time));


                    $html = $html . '<tr style="text-align:center;" >';
                    $html = $html . '<td height="17" width = "5%">' . $month_report . '</td>';
                    $html = $html . '<td width = "12%">' . $counter->get_total_time() . '</td>';
                    $html = $html . '<td  width = "12%">' . $total_late_time . '</td>';
                    $html = $html . '<td  width = "12%">' . $total_early_time . '</td>';
                    $html = $html . '<td  width = "8%">' . $no_of_pay_periods_present . '</td>';
                    $html = $html . '<td  width = "8%">' . $no_of_pay_periods_leaves . '</td>';
                    $html = $html . '<td  width = "8%">' . $no_of_pay_periods_short_leaves . '</td>';
                    $html = $html . '<td  width = "8%">' . $no_of_pay_periods_nopay . '</td>';
                    $html = $html . '<td  width = "8%">' . $no_of_pay_periods_ot . '</td>';
                    $html = $html . '<td  width = "8%">' . $no_of_pay_periods_late . '</td>';
                    $html = $html . '<td  width = "8%">' . $no_of_pay_periods_early . '</td>';

                    $html = $html . '</tr>';


                    unset($attendance_data, $attendance_date);
                    $month_report = '';
                    $no_of_pay_periods_present = 0;
                    $no_of_pay_periods_leaves = 0;
                    $no_of_pay_periods_short_leaves = 0;
                    $no_of_pay_periods_ot = 0;
                    $no_of_pay_periods_nopay = 0;
                    $no_of_pay_periods_late = 0;
                    $no_of_pay_periods_early = 0;
                    $total_work_time = 0;
                    $total_late_time = 0;
                    $total_early_time = 0;
                }

                $html = $html . '</table>';
            }

            //  exit;
            // output the HTML content 
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');

            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    
   function EmployeeAttendanceSummeryExcel($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }
            //    echo "<pre>"; print_r($blf->getNameById($br_id)); die;
            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        $list_start_date = date('d', $pay_period_start);
        $list_end_date = date('d', $pay_period_end);


        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }

        //echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';


            /* foreach ($data as $key => $row) {
              $volume[$key]  = $row['volume'];
              $edition[$key] = $row['edition'];
              }

              // Sort the data with volume descending, edition ascending
              // Add $data as the last parameter, to sort by the common key
              array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data); */

            //Sort array by employee_number
            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows); /**/


           
            $fileName = 'Employee Attendance Summery-' . date('Y F', $pay_period_end);

            // set default header data
            //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
            $objPHPExcel = new PHPExcel();

 
            $objPHPExcel->getProperties()->setCreator("Me")->setLastModifiedBy("Me")->setTitle("Employee Attendance Summery")->setSubject("Employee Attendance Summery")->setDescription("Excel Sheet")->setKeywords("Excel Sheet")->setCategory("Me");

      
            $objPHPExcel->setActiveSheetIndex(0);
            

            $row_data_day_key = array();
            $j1 = 0;



            $no_of_emp = 0;

            // echo '<pre>'; print_r($rows); echo '<pre>'; die;
            $html = '';
            
            
            $array_cell = array('B','C','D','E','F','G','H','I','J','K','L', 'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF' ,'AG');
                
            $aa = 0;

            foreach ($rows as $emp_row) {
                
                $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +2), $emp_row['employee_number']);
                $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +3), $emp_row['first_name'] . ' ' . $emp_row['last_name']);
                
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +4), 'Month');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+1].($user_gap +4), 'Working Hours');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+2].($user_gap +4), 'Late arrival (minutes)');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+3].($user_gap +4), 'Early Departure (minutes) ');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+4].($user_gap +4), 'Present');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+5].($user_gap +4), 'Leave');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+6].($user_gap +4), 'Short Leave');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+7].($user_gap +4), 'No Pay');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+8].($user_gap +4), 'OT');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+9].($user_gap +4), 'Late');
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+10].($user_gap +4), 'Early');


                $month_report = '';
                $no_of_pay_periods_present = 0;
                $no_of_pay_periods_leaves = 0;
                $no_of_pay_periods_short_leaves = 0;
                $no_of_pay_periods_nopay = 0;
                $no_of_pay_periods_ot = 0;
                $no_of_pay_periods_late = 0;
                $no_of_pay_periods_early = 0;
                $total_late_time = 0;
                $total_early_time = 0;
                $total_work_time = 0;
                $work_times = array();

                foreach ($emp_row['data'] as $pay_periods_id => $attendance) {

                 
                    foreach ($attendance as $key => $row) {
                        if ($row['date_stamp'] != '') {

                            $attendance_date[] = $row['date_stamp'];
                            $attendance_data[] = $row;
                        }
                    }

                    array_multisort($attendance_date, SORT_ASC, $attendance_data); /**/
                   
                    foreach ($attendance_data as $day_attendance) {

                        if ($day_attendance['date_stamp'] != '') {

                            

                            $work_times[]=$day_attendance['worked_time'];
                            $udlf = TTnew('UserDateListFactory');
                            $pclf = TTnew('PunchControlListFactory');

                            $day = strtotime($day_attendance['date_stamp']);
                           
                            $Hr_date = DateTime::createFromFormat('d/m/Y', $day_attendance['date_stamp']);
                            $date_stamp = $Hr_date->format('Y-m-d');
                            

                            $udlf->getByUserIdAndDate($emp_row['user_id'], $date_stamp);
                            $udlf_obj = $udlf->getCurrent();

                            if(isset($pc_obj_arr)){
                                unset($pc_obj_arr);
                            }
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;

                          

                            $slf = TTnew('ScheduleListFactory');

                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;

                            $schedule_name_arr = explode('-', $sp_obj_arr['shedule_policy_name']);
                            $status_id = $schedule_name_arr[1];

                            if (!empty($pc_obj_arr)) {

                                // echo $date_stamp.' '.$day_attendance['date_stamp'].'<br>';

                                $no_of_pay_periods_present +=  1;
                                

                                if (isset($day_attendance['over_time']) && $day_attendance['over_time'] != '') {
                                    $no_of_pay_periods_ot = $no_of_pay_periods_ot + 1;
                                }

                                $work_hours = 0;
                                $work_hours = strtotime($day_attendance['min_punch_time_stamp']) - strtotime($day_attendance['max_punch_time_stamp']);
                                
                                
                        $alf = new AccrualListFactory();
                        
                        
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$date_stamp);
                        $absLeave_obj_arr = $alf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                            
                         
                            if ($absLeave_obj_arr['accrual_policy_id'] == 8 && $absLeave_obj_arr['type_id']==55) {//Full day leave
                                
                                  $no_of_pay_periods_short_leaves = $no_of_pay_periods_short_leaves + 1;
                                    
                            }
                            else{
                                    if($absLeave_obj_arr['amount']== -14400){
                                         $no_of_pay_periods_leaves += 0.5;
                                         $no_of_pay_periods_present -= 0.5;
                                    }else{ 
                                        $no_of_pay_periods_leaves += abs($absLeave_obj_arr['amount']/28800);
                                        
                                    }
                                // echo $date_stamp.'<br>';
                            }
                                    
                        }


                                if (!empty($sp_obj_arr) && $day_attendance['min_punch_time_stamp'] != "") {
                                    $lateSec = strtotime($day_attendance['shedule_start_time']) - strtotime($day_attendance['min_punch_time_stamp']);
                                    if ($lateSec < 0) {
                                        
                                        $alf = new AccrualListFactory();
                                        $day_check =$day_attendance['date_stamp'];
                                        $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                                        $ph_date = $ch_date->format('Y-m-d');
                                        
                                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$ph_date);
                                        
                                        if($alf->getRecordCount() <1){
                                           $total_late_time = $total_late_time + $lateSec;
                                           $no_of_pay_periods_late = $no_of_pay_periods_late + 1;
                                        }
                                    }
                                }


                                $earlySec = strtotime($day_attendance['shedule_end_time']) - strtotime($day_attendance['max_punch_time_stamp']);

                                $early = '';
                                if (!empty($sp_obj_arr) && $day_attendance['max_punch_time_stamp'] != '') {
                                    $earlySec = strtotime($day_attendance['shedule_end_time']) - strtotime($day_attendance['max_punch_time_stamp']);

                                    if ($earlySec > 0) {

                                        $alf = new AccrualListFactory();
                                        $day_check =$day_attendance['date_stamp'];
                                        $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                                        $ph_date = $ch_date->format('Y-m-d');
                                        
                                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$ph_date);
                                        
                                        if($alf->getRecordCount() <1){
                                            $no_of_pay_periods_early += 1;
                                            $total_early_time = $total_early_time + $earlySec;
                                        }
                                    }
                                }
                            } else {

                                $hlf = new HolidayListFactory();

                                $alf = new AccrualListFactory();
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$date_stamp);
                        
                        $absLeave_obj_arr = $alf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                           
                                if ($absLeave_obj_arr['accrual_policy_id'] != 8 && $absLeave_obj_arr['type_id']==55) {//Half day leave 
                                   
                                    
                                    if($absLeave_obj_arr['amount']== -14400){
                                         $no_of_pay_periods_leaves += 0.5;
                                         $no_of_pay_periods_present -= 0.5;
                                    }
                                    elseif($absLeave_obj_arr['amount']== -28800){
                                           $no_of_pay_periods_leaves += 1;
                                    }
                                    else{
                                          $no_of_pay_periods_leaves  += abs($absLeave_obj_arr['amount']/28800);
                                    }
                                    
                                    $status1 = 'HL';
                                } else if ($absLeave_obj_arr['accrual_policy_id'] == 8) {//Full day leave
                                    // $tot_array['SH'] += 1;
                                    // echo 'bbbb';
                                    // $status1 = 'SH';
                                } else if ($absLeave_obj_arr['accrual_policy_id'] == 3) {//Short leave
                                   // $tot_array['SH'] += 1;
                                }    
                        }
                                                
                            }
                        }// end of daystamp if
                    }

                    //  echo $month_report.' '.$no_of_pay_periods_present.'<br>';

                    $counter = new times_counter($work_times);
                    // echo $counter->get_total_time();

                    $pplf = new PayPeriodListFactory();
                    $pplf->getById($pay_periods_id);
                    $pay_period_obj = $pplf->getCurrent();

                    // echo $pay_period_obj->getEndDate();
                    $Hr_date = new DateTime();
                    $Hr_date->setTimestamp($pay_period_obj->getEndDate());
                    $month_report = $Hr_date->format('Y-M');

                    $total_late_time = gmdate("H:i", abs($total_late_time));

                    $total_early_time = gmdate("H:i", abs($total_early_time));

                    $total_work_time = gmdate("H:i", abs($total_work_time));


                    
                    
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa].($user_gap +5), $month_report);
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+1].($user_gap +5), $counter->get_total_time());
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+2].($user_gap +5), $total_late_time);
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+3].($user_gap +5), $total_early_time );
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+4].($user_gap +5), $no_of_pay_periods_present);
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+5].($user_gap +5), $no_of_pay_periods_leaves );
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+6].($user_gap +5), $no_of_pay_periods_short_leaves);
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+7].($user_gap +5), $no_of_pay_periods_nopay);
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+8].($user_gap +5), $no_of_pay_periods_ot);
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+9].($user_gap +5), $no_of_pay_periods_late );
                 $objPHPExcel->getActiveSheet()->setCellValue($array_cell[$aa+10].($user_gap +5), $no_of_pay_periods_early);



                    unset($attendance_data, $attendance_date);
                    $month_report = '';
                    $no_of_pay_periods_present = 0;
                    $no_of_pay_periods_leaves = 0;
                    $no_of_pay_periods_short_leaves = 0;
                    $no_of_pay_periods_ot = 0;
                    $no_of_pay_periods_nopay = 0;
                    $no_of_pay_periods_late = 0;
                    $no_of_pay_periods_early = 0;
                    $total_work_time = 0;
                    $total_late_time = 0;
                    $total_early_time = 0;
                    
                    
                }
                
                
                

                $user_gap+=8;
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');

            return FALSE;
        }
    }

    
    
    function DepartmentAttendanceDetailed($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }
            //    echo "<pre>"; print_r($blf->getNameById($br_id)); die;
            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
         $count_pp= count($filter_data['pay_period_ids']);                               
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][$count_pp-1])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }

/*
        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        $list_start_date = date('d', $pay_period_start);
        $list_end_date = date('d', $pay_period_end);


        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }
*/
        //echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                // 'payperiod_end_date'   => date('Y-M',$pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Department Attendance Report',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

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
            $pdf->AddPage('l', 'mm', 'A2');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(50, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '';


            /* foreach ($data as $key => $row) {
              $volume[$key]  = $row['volume'];
              $edition[$key] = $row['edition'];
              }

              // Sort the data with volume descending, edition ascending
              // Add $data as the last parameter, to sort by the common key
              array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data); */

            //Sort array by employee_number
            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows); /**/


            $pdf->SetFont('', 'B', 6.5);


            $row_data_day_key = array();
            $j1 = 0;



            $html_new = '';
            // $html ='<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td align="left">'.$row['employee_number'].'</td></tr>'
            //             . '<tr><td>'.$row['first_name'].' '.$row['last_name'].'</td></tr></table>';
            $html = $html . '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead><tr style="background-color:#CCCCCC;text-align:center;" >';
//                $html = $html.'<td width = "3%">#</td>';
            $html = $html . '<td height="17" width = "6%">Month</td>';
            $html = $html . '<td width = "10%">Late arrival (minutes)</td>';
            $html = $html . '<td align="left" width = "10%">Early Departure (minutes) </td>';
            $html = $html . '<td align="left" width = "10%">Leave</td>';
            $html = $html . '<td align="left" width = "10%">Short L</td>';
            $html = $html . '<td align="left" width = "10%">No Pay</td>';
            $html = $html . '<td align="left" width = "10%">OT</td>';
            $html = $html . '<td align="left" width = "10%">Late</td>';
            $html = $html . '<td align="left" width = "10%">Early</td>';

            $html = $html . '</tr>';
            $html = $html . '</thead>';




           // echo '<pre>';print_r($rows);exit;
            
            foreach($filter_data['pay_period_ids'] as $pp_id){
                
                
                $no_of_emp = 0;
                $no_of_department_present = 0;
                $no_of_department_leaves = 0;
                $no_of_department_short_leaves = 0;
                $no_of_department_nopay = 0;
                $no_of_department_ot = 0;
                $no_of_department_late = 0;
                $no_of_department_early = 0;
                $total_late_time = 0;
                $total_early_time = 0;
            
                
                 $html = $html .'<tr>';
                
                 $pp_month_year = $pplf->getById($pp_id)->getCurrent()->getEndDate();
                 $date_month_y = date('M-y', $pp_month_year);
                 
                $html = $html . '<td height="17" width = "6%">'.$date_month_y.'</td>';
                
                foreach ($rows as $emp_row) {
                   
                    
                    $pay_periods_data = $emp_row['data'][$pp_id];
                   // if($emp_row['user_id'] ==798){ echo '<pre>';print_r($pay_periods_data); exit;}
                    
                    foreach($pay_periods_data as $pp_date){
                        
                        
                        if($pp_date['date_stamp'] !=''){
                            
                            $udlf = TTnew('UserDateListFactory');
                            $pclf = TTnew('PunchControlListFactory');
                            
                            $day = strtotime($pp_date['date_stamp']);

                            $Hr_date = DateTime::createFromFormat('d/m/Y', $pp_date['date_stamp']);
                            $date_stamp = $Hr_date->format('Y-m-d');
                            





                            $udlf->getByUserIdAndDate($emp_row['user_id'], $date_stamp);
                            $udlf_obj = $udlf->getCurrent();

                            if(isset($pc_obj_arr)){
                                unset($pc_obj_arr);
                            }
                            
                            $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $pc_obj_arr = $pclf->getCurrent()->data;

                          

                            $slf = TTnew('ScheduleListFactory');

                            $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                            $sp_obj_arr = $slf->getCurrent()->data;
                            
                            
                              if (!empty($pc_obj_arr)) {
                                  
                                  $no_of_department_present +=1;
                                  
                                  
                                if (isset($pp_date['over_time']) && $pp_date['over_time'] != '') {
                                    
                                    if($pp_date['over_time_policy_id']!=3){
                                       $no_of_department_ot = $no_of_department_ot + 1;
                                    }
                                }

                                  
                                  
                                  
                                  $alf = new AccrualListFactory();
                        


                                $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$date_stamp);
                                $absLeave_obj_arr = $alf->getCurrent()->data;
                               // if($emp_row['user_id'] ==798){  echo $date_stamp.'<br>';}
                                if (!empty($absLeave_obj_arr)) {

                                    

                                    if ($absLeave_obj_arr['accrual_policy_id'] == 8 && $absLeave_obj_arr['type_id']==55) {//Full day leave

                                          $no_of_department_short_leaves = $no_of_department_short_leaves + 1;
                                             
                                          
                                    }
                                    else{
                                        
                                            if($absLeave_obj_arr['amount']== -14400){
                                                  $no_of_department_leaves += 0.5;
                                                 //$no_of_pay_periods_present -= 0.5;
                                            }
                                            else if($absLeave_obj_arr['amount'] == -28800){
                                                  $no_of_department_leaves += 1;
                                                 //$no_of_pay_periods_present -= 0.5;
                                            }
                                            else{ 
                                                $no_of_pay_periods_leaves += abs($absLeave_obj_arr['amount']/28800);

                                            }
                                        // echo $date_stamp.'<br>';
                                    }

                                }
                                  
                                  
                                  
                                if (!empty($sp_obj_arr) && $pp_date['min_punch_time_stamp'] != "") {
                                    $lateSec = strtotime($pp_date['shedule_start_time']) - strtotime($pp_date['min_punch_time_stamp']);
                                    if ($lateSec < 0) {
                                        
                                        $alf = new AccrualListFactory();
                                        $day_check =$pp_date['date_stamp'];
                                        $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                                        $ph_date = $ch_date->format('Y-m-d');
                                        
                                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$ph_date);
                                        
                                        if($alf->getRecordCount() <1){
                                           $no_of_department_late += 1;
                                           $total_late_time +=$lateSec;
                                           
                                        }
                                    }
                                }


                                $earlySec = strtotime($pp_date['shedule_end_time']) - strtotime($pp_date['max_punch_time_stamp']);

                                $early = '';
                                if (!empty($sp_obj_arr) && $pp_date['max_punch_time_stamp'] != '') {
                                    $earlySec = strtotime($pp_date['shedule_end_time']) - strtotime($pp_date['max_punch_time_stamp']);

                                    if ($earlySec > 0) {

                                        $alf = new AccrualListFactory();
                                        $day_check =$pp_date['date_stamp'];
                                        $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                                        $ph_date = $ch_date->format('Y-m-d');
                                        
                                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$ph_date);
                                        
                                        if($alf->getRecordCount() <1){
                                             
                                            $no_of_department_early += 1;
                                            $total_early_time += $earlySec;
                                        }
                                    }
                                }
                                  
                                  
                              }
                              else{
                                 
                                  
                                 if(!empty($sp_obj_arr) && isset($sp_obj_arr) ){ 
                                        $alf = new AccrualListFactory();

                                        $alf->getByAccrualByUserIdAndTypeIdAndDate($emp_row['user_id'],55,$date_stamp);
                                        $absLeave_obj_arr = $alf->getCurrent()->data;

                                        if (!empty($absLeave_obj_arr)) {





                                                    if($absLeave_obj_arr['amount'] == -28800){
                                                          $no_of_department_leaves += 1;
                                                         //$no_of_pay_periods_present -= 0.5;
                                                    }
                                                    else{ 
                                                        $no_of_pay_periods_leaves += abs($absLeave_obj_arr['amount']/28800);

                                                    }
                                                // echo $date_stamp.'<br>';


                                        }  
                                        else{
                                            
                                            $hlf = TTnew('HolidayListFactory');
                                            $hlf->getByPolicyGroupUserIdAndDate($emp_row['user_id'], $date_stamp);
                                            $hday_obj_arr = $hlf->getCurrent()->data;
                                            
                                            if(empty($hday_obj_arr)){
                                                
                                              if($emp_row['user_id']!=940){
                                                $no_of_department_nopay++;
                                              }
                                           // echo $emp_row['user_id'].' '.$date_stamp.'<br>';
                                            }
                                        }
                                 }


                              }
                            
                            
                            
                            
                        }
                        
                    }
                    

                }
                
                $total_late_time  = gmdate("H:i", abs($total_late_time ));
                
                $html = $html . '<td height="17" width = "10%">'.$total_late_time .'</td>';
                
                
                $total_early_time = gmdate("H:i", abs($total_early_time));
                
                $html = $html . '<td height="17" width = "10%">'.$total_early_time.'</td>';
                
                $html = $html . '<td height="17" width = "10%">'.$no_of_department_leaves.'</td>';
                $html = $html . '<td height="17" width = "10%">'. $no_of_department_short_leaves.'</td>';
                $html = $html . '<td height="17" width = "10%">'.$no_of_department_nopay.'</td>';
                $html = $html . '<td height="17" width = "10%">'. $no_of_department_ot.'</td>';
                $html = $html . '<td height="17" width = "10%">'.$no_of_department_late.'</td>';
                $html = $html . '<td height="17" width = "10%">'. $no_of_department_early.'</td>';
                
                
               $html = $html .'</tr>';
            }



            $html = $html . '</table>';


            // output the HTML content 
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');

            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

//Rush
    //FL ADDED FOR Monthly Attendance (National PVC) 20160524
    function MonthlyAttendanceDetailed_old($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

        while ($current <= $last) {

            $dates[] = date('d', $current);
            $current = strtotime('+1 day', $current);
        }

        //echo '<pre>'; print_r($data); echo '<pre>'; die;


        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;
            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Monthly Attendance Report',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

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
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


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

            $html = $html . '</tr>';

            /* foreach ($data as $key => $row) {
              $volume[$key]  = $row['volume'];
              $edition[$key] = $row['edition'];
              }

              // Sort the data with volume descending, edition ascending
              // Add $data as the last parameter, to sort by the common key
              array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data); */

            //Sort array by employee_number
            /* foreach ($rows as $key => $row) {
              $employee_number[$key]  = $row['employee_number'];
              }

              array_multisort($employee_number, SORT_ASC, $rows); */

            //echo '<pre>'; print_r($rows); die;

            $pdf->SetFont('', 'B', 6.5);


            $row_data_day_key = array();
            $j1 = 0;
            foreach ($rows as $row) {



                $html = $html . '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                    <tr style="background-color:#CCCCCC;text-align:center;" >';
//                $html = $html.'<td width = "3%">#</td>';
                $html = $html . '<td width="40"> </td>';
                $html = $html . '<td  width="80"> </td>';
                $html = $html . '<td colspan="6"> </td>';
                $html = $html . '<td colspan="3">Present</td>';
                $html = $html . '<td colspan="2">Absent</td>';
                $html = $html . '<td colspan="2">Leaves</td>';
                $html = $html . '<td colspan="2">W. Offs</td>';
                $html = $html . '<td colspan="2">Holidays</td>';
                $html = $html . '<td colspan="2">OT</td>';
                $html = $html . '<td colspan="12"></td>';

                $html = $html . '</tr>';
                $present_days = 0;
                $absent_days = 0;
                $leave_days = 0;
                $week_off = 0;
                $holidays = 0;

                //echo '<pre>'; print_r($row); die;

                foreach ($row['data'] as $row1) {

                    if ($row1['date_stamp'] != '') {
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d', strtotime($row_dt));
                        //echo '<br><pre>'.$dat_day;
                        $row_data_day_key[$dat_day] = $row1;

                        //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                    } else {
                        $tot_ot_hours_data = $row1['over_time'];
                        $tot_worked_actual_hours_data = $row1['actual_time'];
                        $tot_worked_hours_data = explode(':', $row1['worked_time']);
                        $tot_worked_sec_data = ($tot_worked_hours_data[0] * 60 * 60) + ($tot_worked_hours_data[1] * 60);
//                            
                    }
                }



                //row1
                $nof_presence = 0;
                $nof_absence = 0;
                $nof_leaves = 0;
                $nof_weekoffs = 0;
                $nof_holidays = 0;
                $nof_ot = 0;
                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {

                    //echo '<pre>';
                    //print_r($row_data_day_key[sprintf("%02d", $i1)]);

                    $status1 = '';

                    $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                    $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                    $udlf = TTnew('UserDateListFactory');
                    $pclf = TTnew('PunchControlListFactory');

//                            
                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $udlf_obj = $udlf->getCurrent();

                    $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $pc_obj_arr = $pclf->getCurrent()->data;
//                         echo '<pre>'; print_r($pc_obj_arr); die;
                    //if punch exists
                    if (!empty($pc_obj_arr)) {
                        $status1 = 'P';
                        //check late come and early departure
                        $elf = TTnew('ExceptionListFactory');
                        $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                        $ex_obj_arr = $elf->getCurrent()->data;
                        if (!empty($ex_obj_arr)) {
                            $status1 = 'LP';
                        }
                    } else {
                        $status1 = 'WO';

                        $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                        $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                        $absLeave_obj_arr = $aluelf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                            $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                            $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);

                            if ($status1 != 'WO') {
                                //$tot_array['L'][]=$i1;
                                if ($absLeave_obj_arr['absence_leave_id'] == 2) {
                                    $tot_array['L'] += 0.5;
                                    $tot_array['P'] += 0.5;
                                } else {
                                    $tot_array['L'] += 1;
                                }
                            }
                        }
                    }


                    $hlf = TTnew('HolidayListFactory');
                    $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $hday_obj_arr = $hlf->getCurrent()->data;

                    if (!empty($hday_obj_arr)) {
                        $status1 = 'HLD';
                    }


                    // $tot_array[$status1][]=$i1;
                    $tot_array[$status1] += 1;
                }

                $udtlf = TTnew('UserDateTotalListFactory');
                $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate($current_company->getId(), $row['user_id'], 10, date('Y-m-d', $pay_period_start), date('Y-m-d', $pay_period_end));
                if ($udtlf->getRecordCount() > 0) {
                    foreach ($udtlf as $udt_obj) {
                        if ($udt_obj->getOverTimePolicyID() != 0) {
                            $tot_array['OT'] += 1;
                        }
                    }
                }

                if (isset($tot_array['P'])) {
                    $nof_presence += $tot_array['P'];
                }

                if (isset($tot_array['LP'])) {
                    $nof_presence += $tot_array['LP'];
                }

                if (isset($tot_array['WO'])) {
                    $nof_weekoffs = $tot_array['WO'];
                }

                if (isset($tot_array['HLD'])) {
                    $nof_holidays = $tot_array['HLD'];
                }

                if (isset($tot_array['L'])) {
                    $nof_leaves = $tot_array['L'];
                }

                if (isset($tot_array['OT'])) {
                    $nof_ot = $tot_array['OT'];
                }


                unset($tot_array);
                $nof_absence = $nof_days_for_month - ($nof_presence + $nof_weekoffs + $nof_holidays + $nof_leaves);




                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">' . $row['employee_number'] . '</td>';
                $html = $html . '<td>' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
                $html = $html . '<td colspan="6"> </td>';
                $html = $html . '<td colspan="3">' . $nof_presence . '</td>';
                $html = $html . '<td colspan="2">' . $nof_absence . '</td>';
                $html = $html . '<td colspan="2">' . $nof_leaves . '</td>';
                $html = $html . '<td colspan="2">' . $nof_weekoffs . '</td>';
                $html = $html . '<td colspan="2">' . $nof_holidays . '</td>';
                $html = $html . '<td colspan="2">' . $nof_ot . '</td>';
                $html = $html . '<td colspan="12"></td>';
                $html = $html . '</tr>';

                //echo '<pre>'; print_r($row['data']); echo '<pre>'; die;  
                //row2
                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Day</td>';
                $html = $html . '<td></td>';
                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {
                    $html = $html . '<td>' . $i1 . '</td>';
                }
                $html = $html . '</tr>'; /**/

                //row3
                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Shift ID</td>';
                $html = $html . '<td></td>';
                $status_id = '-';
                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {
                    $udlf = TTnew('UserDateListFactory');
                    $slf = TTnew('ScheduleListFactory');

                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
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
                    $html = $html . '<td>' . $status_id . '</td>';
                }
                $html = $html . '</tr>'; /**/

                //row3
                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Shift In</td>';
                $html = $html . '<td></td>';
                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {
                    $html = $html . '<td>' . $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] . '</td>';
                }
                $html = $html . '</tr>';

                //row4
                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Shift Out</td>';
                $html = $html . '<td></td>';
                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {
                    $html = $html . '<td>' . $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] . '</td>';
                }
                $html = $html . '</tr>';

                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="34"></td></tr>'; /**/

                //row5
                $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Late</td>';
                $html = $html . '<td></td>';

                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {

                    $udlf = TTnew('UserDateListFactory');
                    $slf = TTnew('ScheduleListFactory');

                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $udlf_obj = $udlf->getCurrent();

                    $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $sp_obj_arr = $slf->getCurrent()->data;
                    $late = '';
                    if (!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] != '') {
                        $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                        if ($lateSec < 0) {
                            $late = gmdate("H:i", abs($lateSec));
                        }
                    }
                    $html = $html . '<td>' . $late . '</td>';
                }
                $html = $html . '</tr>'; /* */

                //row6
                $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Early</td>';
                $html = $html . '<td></td>';
                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {

                    $udlf = TTnew('UserDateListFactory');
                    $slf = TTnew('ScheduleListFactory');

                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $udlf_obj = $udlf->getCurrent();

                    $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $sp_obj_arr = $slf->getCurrent()->data;
                    $early = '';
                    if (!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] != '') {
                        $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                        if ($earlySec > 0) {
                            $early = gmdate("H:i", abs($earlySec));
                        }
                    }
                    $html = $html . '<td>' . $early . '</td>';
                }
                $html = $html . '</tr>';


                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="34"></td></tr>'; /**/

                //row7
                $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left" >Status 1</td>';
                $html = $html . '<td></td>';
                $earlySec = $lateSec = 0;
                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {
                    $status1 = '';
                    $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                    $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                    $udlf = TTnew('UserDateListFactory');
                    $pclf = TTnew('PunchControlListFactory');
                    $elf = TTnew('ExceptionListFactory'); //--Add code eranda

                    $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $udlf_obj = $udlf->getCurrent();

                    $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                    $pc_obj_arr = $pclf->getCurrent()->data;
                    //echo '<pre>'; print_r($pc_obj_arr); die;
                    $elf->getByUserDateId($udlf_obj->getId());
                    $elf_obj = $elf->getCurrent();

                    //if punch exists
                    if (!empty($pc_obj_arr)) {

                        $status1 = 'P';


                        //check late come and early departure
                        /*       $elf = TTnew('ExceptionListFactory');
                          $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                          $ex_obj_arr = $elf->getCurrent()->data;
                          if(!empty($ex_obj_arr)){
                          $status1 = 'LP';
                          } */
                        if (!empty($elf_obj->data)) {
                            //	if($epclf_obj->getExceptionPolicyControlID()) {
                            foreach ($elf as $elf_obj) {
                                if ($elf_obj->getExceptionPolicyID() == '29' || $elf_obj->getExceptionPolicyID() == '5') {
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
                    } else {
                        $status1 = 'WO';

                        $aluelf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
                        $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                        $absLeave_obj_arr = $aluelf->getCurrent()->data;
                        if (!empty($absLeave_obj_arr)) {
                            $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                            $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                        }
                        //echo '<pre><br>'.date('Y-m-d',  strtotime($i1.'-'.$date_month)).$udlf_obj->getId(); print_r($absLeave_obj_arr); 
                    }


                    $hlf = TTnew('HolidayListFactory');
                    $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                    $hday_obj_arr = $hlf->getCurrent()->data;

                    if (!empty($hday_obj_arr)) {
                        $status1 = 'HLD';
                    }



                    $html = $html . '<td>' . $status1 . '</td>';
                }
                //die;
                $html = $html . '</tr>'; /**/

                //row8
                $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Status 2</td>';
                $html = $html . '<td></td>';
                for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {
                    $html = $html . '<td>' . date('D', strtotime($i1 . '-' . $date_month)) . '</td>';
                    unset($row_data_day_key[sprintf("%02d", $i1)]);
                }
                $html = $html . '</tr>';

                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="34"><br/><hr/></td></tr>';

                $html = $html . '</table>';
                $j1++;

                if ($j1 % 3 == 0) {
                    $html .= '<br pagebreak="true" />';
                }
            }

            //echo $html; die;      
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  
            // Debug::setVerbosity(11); 
            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    //FL ADDED FOR Monthly Late (National PVC) 20160524
    function MonthlyLateDetailed($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }
            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }

        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;

            $j = 0;
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $dates[$j]['date_actual'] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }
            
                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $dates[$j]['date_actual'] = date('Y-m-d', $current);
         
         

        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data; //echo '<pre>'; print_r($rows);

            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Monthly Late Report',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 50, 23);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '<br><br><br><table border="0" cellspacing="0" cellpadding="0" width="100%"> ';

            $pdf->SetFont('', 'B', 7);

            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows);

            $j1 = 0;
            $row_data_day_key = array();
            foreach ($rows as $row) {
                foreach ($row['data'] as $row1) {
                    if ($row1['date_stamp'] != '') {
                        $row_dt = str_replace('/', '-', $row1['date_stamp']);

                        $dat_day = date('d', strtotime($row_dt));
                        $row_data_day_key[$dat_day] = $row1;

                        //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                    } else {
                        $tot_ot_hours_data = $row1['over_time'];
                        $tot_worked_actual_hours_data = $row1['actual_time'];
                        $tot_worked_hours_data = explode(':', $row1['worked_time']);
                        $tot_worked_sec_data = ($tot_worked_hours_data[0] * 60 * 60) + ($tot_worked_hours_data[1] * 60);
//                            
                    }
                }

                $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                $html = $html . '<td align="left" width="4%">' . $row['employee_number'] . '</td>';
                $html = $html . '<td align="left" width="8%">' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
                $html = $html . '<td colspan="' . $nof_days_for_month . '"></td>';
                $html = $html . '<td >Total</td>';
                $html = $html . '</tr>';

                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Day</td>';
                $html = $html . '<td></td>';
                foreach($dates as $i1){
                    $html = $html . '<td>' . $i1['day'] . '</td>';
                }
                $html = $html . '</tr>';

                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="' . ($nof_days_for_month + 3) . '"></td></tr>';

                $html = $html . '<tr  style =" height:50px; text-align:center" bgcolor="#EEEEEE" nobr="true">';
                $html = $html . '<td align="left">Late</td>';
                $html = $html . '<td></td>';

             //   echo "<pre>"; print_r($row_data_day_key); die;
                $TotlateSec = 0;
               // for ($i1 = 1; $i1 <= $nof_days_for_month; $i1++) {
                foreach($dates as $date){

                    //echo '<br>b4late'.$late;

                    $late = '';
                    $lateSec = '';
                    if ($row_data_day_key[$date['day']]['min_punch_time_stamp'] != '' && $row_data_day_key[$date['day']]['shedule_start_time'] != '') {

                        $lateSec = strtotime($row_data_day_key[$date['day']]['shedule_start_time']) - strtotime($row_data_day_key[$date['day']]['min_punch_time_stamp']);

                        /* if($lateSec < 0 ){ 
                          //echo '<br>late... '.;
                          $late = gmdate("H:i", abs($lateSec));

                          echo '<br><br>minpunch strtotime...'.strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']).
                          ' <br>minpunch date... '.date("H:i", (strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']))).
                          '<br>minpunch ...  '.$row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'];

                          $TotlateSec = $TotlateSec + abs($lateSec) ;
                          } */

                        /* echo '<br><br>employee_number....'.$row['employee_number'].'<br>day'.$i1.'<br>minpunch strtotime...'.strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']).
                          ' <br>minpunch date... '.date("H:i", (strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']))).
                          '<br>shedule_start_time ...  '.$row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']; */

                        if ($lateSec < 0) {
                            $late = gmdate("H:i", abs($lateSec));

                            $TotlateSec = $TotlateSec + abs($lateSec);
                        }
                    } else {
                        $late = '';
                    }

                    $html = $html . '<td>' . $late . '</td>';

                    $status2_row = $status2_row . '<td>' . date('D', strtotime($date['date_actual'])) . '</td>';
                    // echo '<br>afterlate'.$late;
                }


                $Totlate_hours = intval($TotlateSec / 3600);
                $Totlate_minutes = intval(($TotlateSec % 3600) / 60);
                $Totlate_seconds = ($TotlateSec % 3600) % 60;

                $html = $html . '<td><b>' . $Totlate_hours . ':' . $Totlate_minutes . '</b></td>';
                $html = $html . '</tr>';


                $html = $html . '<tr style =" height:50px; text-align:center" bgcolor="white" nobr="true">';
                $html = $html . '<td align="left">Status</td>';
                $html = $html . '<td></td>';
                $html = $html . $status2_row;
                $html = $html . '<td></td>';
                $html = $html . '</tr>';
                $status2_row = '';


                $html = $html . '<tr style ="text-align:center" bgcolor="white" nobr="true"><td colspan="' . ($nof_days_for_month + 3) . '"><br/><hr/></td></tr>';

                $j1++;

                if ($j1 % 5 == 0) {
                    $html .= '<br pagebreak="true" />';
                }
            }



            $html = $html . '</table>';


            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  

            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    //FL ADDED FOR EMPLOYEE TIME SHEET REPORT (National PVC) 20160601
     function EmployeeTimeSheet($data1, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {

        $total_worked_hours = new DateTime('00:00');
        $total_work_hr = 0;


        $total_worked_hours_add = clone $total_worked_hours;


        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                //   $br_strng = implode(', ', $branch_list);
            }
            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        /* $_SESSION['header_data'] = array( 
          'image_path'   => $current_company->getLogoFileName(),
          'company_name' => $company_name,
          'address1'     => $addrss1,
          'address2'     => $address2,
          'city'         => $city,
          'province'     => $current_company->getProvince(),
          'postal_code'  => $postalcode,
          'heading'  => 'Employee Time Sheet - ',
          'line_width'  => 185,

          ); */


        $_SESSION['header_data'] = array(
            'image_path' => $current_company->getLogoFileName(),
            'company_name' => $company_name,
            'address1' => $addrss1,
            'address2' => $address2,
            'city' => $city,
            'province' => $current_company->getProvince(),
            'postal_code' => $postalcode,
            'heading' => 'Attendance Report - ' . date('Y F', $pay_period_end),
            'group_list' => $gr_strng,
            'department_list' => $dep_strng,
            'branch_list' => $br_strng,
            'payperiod_end_date' => date('Y-M', $pay_period_end),
            'line_width' => 185,);

        $pdf = TTnew('TimeReportHeaderFooter');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, 23);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // add a page
        $pdf->AddPage('p', 'mm', 'A4');

        //Table border
        $pdf->setLineWidth(0.20);

        //set table position
        $adjust_x = 12;

        $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(30, $adjust_y));


        //TABLE CODE HERE

        $pdf->SetFont('', 'B', 7);
//            $data = $data;
        $html = '';
        $j = 0;

        foreach ($data1 as $key => $row) {
            $employee_number[$key] = $row['employee_number'];
        }

        array_multisort($employee_number, SORT_ASC, $data1);


        foreach ($data1 as $data) {
            $data['tot_data'] = $data['data'][count($data['data']) - 1];
            array_pop($data['data']); //delete tot of data array 
//           echo '<pre>';     print_r( $data ); echo '<pre>'; die;
            $pplf = TTnew('PayPeriodListFactory');
            if (isset($filter_data['pay_period_ids'][0])) {
                $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
            } else {
                $pay_period_start = $filter_data['start_date'];
                $pay_period_end = $filter_data['end_date'];
            }

            $dates = array();
            $current = $pay_period_start;
            $last = $pay_period_end;
            $j = 0;
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $dates[$j]['date_actual'] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }
            
                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $dates[$j]['date_actual'] = date('Y-m-d', $current);

            $present_days = 0;
            $absent_days = 0;
            $week_off = 0;
            $holidays = 0;
            $row_data_day_key = array();
            foreach ($data['data'] as $row1) {
                if ($row1['date_stamp'] != '') {
                    $row_dt = str_replace('/', '-', $row1['date_stamp']);

                    $dat_day = date('d', strtotime($row_dt));
                    
                    $row_data_day_key[$dat_day] = $row1;
                }
            }


            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";


            if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
                $rows = $data;
                //echo '<pre>'; print_r($rows); echo '<pre>'; die;

                if ($ignore_last_row === TRUE) {
                    $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }



                $html = $html . '<table width="100%">';
                $html = $html . '<tr align="right" valign="top">';
                $html = $html . '<td><strong>EPF No :</strong> ' . $rows['employee_number'] . ' <br /> <strong>Name : </strong>' . $rows['full_name'] . '<br /> <strong>Department : </strong>' . $rows['default_department'] . '<br /> </td>';
                $html = $html . '</tr>';

                $html = $html . '</table>';
                //Header
                // create some HTML content
                $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="105%">
                        <tr style="background-color:#CCCCCC;text-align:center; padding:5px;" >';
//                $html = $html.'<td width = "3%">#</td>';
                $html = $html . '<td width="15%"><table><tr><td></td></tr><tr><td>Date</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="13%"><table><tr><td></td></tr><tr><td>First In</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="13%"><table><tr><td></td></tr><tr><td>Last Out</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="15%"><table><tr><td></td></tr><tr><td>Worked Hrs</td></tr><tr><td></td></tr></table> </td>';
                // $html = $html.'<td width="9%"><table><tr><td></td></tr><tr><td>OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="15%" colspan="2"><table><tr><td colspan="2"></td></tr><tr><td colspan="2">Status</td></tr><tr><td>1</td><td>2</td></tr></table> </td>';
                $html = $html . '<td width="15%"><table><tr><td></td></tr><tr><td>Late Arrival (Minute)</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="15%"><table><tr><td></td></tr><tr><td>Early Departure (Minute)</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '</tr>';

                $pdf->SetFont('', '', 8);

                $totLate = $totEarly = 0;

//print_r($dates) ; die;
                //             
                $nof_presence = 0;
                $nof_late = 0;
                $nof_no_pay = 0;
                $nof_early = 0;
                $nof_short_leave=0;
                $nof_leave = 0;
                $nof_ot_days = 0;
                
                
                foreach ($dates as $date) {
                  // echo '<pre>'; print_r($row_data_day_key); echo '<pre>'; die;

                    $dateStamp = '';
                    if ($row_data_day_key[$date['day']]['date_stamp'] != '') {
                        $dateStamp = DateTime::createFromFormat('d/m/Y', $row_data_day_key[$date['day']]['date_stamp'])->format('Y-m-d');
                    }
                    
                    // exclude policy id 3
                    if (isset($row_data_day_key[$date['day']]['over_time']) && $row_data_day_key[$date['day']]['over_time'] != '' && $row_data_day_key[$date['day']]['over_time_policy_id'] != 3) {
                       
                         $nof_ot_days++;
                    }

                    //unset($EmpDateStatus); 

                    $EmpDateStatus = $this->getReportStatusByUserIdAndDate($rows['user_id'], $dateStamp);
                    //echo'<pre>'; print_r( $row_data_day_key);   die;

                    $status1 = $status2 = '';
                    $earlySec = $lateSec = 0;
                    if (($row_data_day_key[$date['day']]['min_punch_time_stamp'] != '' && $row_data_day_key[$date['day']]['min_punch_time_stamp'] != "") &&
                            ($row_data_day_key[$date['day']]['shedule_start_time'] != "" && $row_data_day_key[$date['day']]['shedule_end_time'] != "")) {
                        
                       $hlf = TTnew('HolidayListFactory');
                        $hlf->getByPolicyGroupUserIdAndDate($rows['user_id'], $dateStamp);
                        $hday_obj_arr = $hlf->getCurrent()->data;
                        //  print_r($row_data_day_key);
                        //  exit();
                        
                        if(empty($hday_obj_arr)){
                            
                        if($row_data_day_key[$date['day']]['min_punch_time_stamp'] !=''){
                           $lateSec = strtotime($row_data_day_key[$date['day']]['shedule_start_time']) - $row_data_day_key[$date['day']]['min_punch_time_stamp'];
                        }
                        
                        if($row_data_day_key[$date['day']]['max_punch_time_stamp'] !=''){
                           $earlySec = strtotime($row_data_day_key[$date['day']]['shedule_end_time']) - $row_data_day_key[$date['day']]['max_punch_time_stamp'];
                        }
                        // print_r($row_data_day_key[$date['day']]);
                         //   exit();
                        if ($earlySec > 0) {
                            
                           
                            $alf = new AccrualListFactory();
                            
                            $day_check =$row_data_day_key[$date['day']]['date_stamp'];
                            
                            $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                            $ph_date = $ch_date->format('Y-m-d');
                          
                            
                             $alf->getByAccrualByUserIdAndTypeIdAndDate($rows['user_id'],55,$ph_date);
                            
                             
                            $absLeave_obj_arr = $alf->getCurrent()->data;
                            if (!empty($absLeave_obj_arr)) {
                            
                         
                            if ($absLeave_obj_arr['accrual_policy_id'] == 8 && $absLeave_obj_arr['type_id']==55) {//Full day leave
                                
                                  
                                    $nof_short_leave++;
                                     $EmpDateStatus['status2']= 'SL';
                            }
                            else{
                                    if($absLeave_obj_arr['amount']== -14400){
                                         $nof_leave += 0.5;
                                         $nof_presence -= 0.5;
                                    }else{ 
                                        $nof_leave += abs($absLeave_obj_arr['amount']/28800);
                                        $status1 = 'LV';
                                        
                                          //$nof_leave++;
                                    }
                                // echo $date_stamp.'<br>';
                            }
                                    
                            }
                            else{
                             $totEarly = $totEarly + abs($earlySec);
                                 $early = gmdate("H:i", abs($earlySec));
                                 $nof_early++;
                            }
                           
                            
                            
                        }
                        
                        
                        if ($lateSec < 0) {
                            
                             $alf = new AccrualListFactory();
                            
                            $day_check =$row_data_day_key[$date['day']]['date_stamp'];
                            
                            $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                            
                            $ph_date = $ch_date->format('Y-m-d');
                          
                            
                             $alf->getByAccrualByUserIdAndTypeIdAndDate($rows['user_id'],55,$ph_date);
                             
                               $absLeave_obj_arr_late = $alf->getCurrent()->data;
                                    if (!empty($absLeave_obj_arr_late)) {


                                        if ($absLeave_obj_arr_late['accrual_policy_id'] == 8 && $absLeave_obj_arr_late['type_id']==55) {//Full day leave


                                                $nof_short_leave++;
                                                 $EmpDateStatus['status2']= 'SL';
                                        }
                                        else{
                                                if($absLeave_obj_arr_late['amount']== -14400){
                                                     $nof_leave += 0.5;
                                                     $nof_presence -= 0.5;
                                                }else{ 
                                                    $nof_leave += abs($absLeave_obj_arr_late['amount']/28800);
                                                    $status1 = 'LV';

                                                      //$nof_leave++;
                                                }
                                            // echo $date_stamp.'<br>';
                                        }
                                    }
                                else{
                                    $totLate = $totLate + abs($lateSec);
                                    $late = gmdate("H:i", abs($lateSec));
                                    $nof_late++;
                                 
                             }
                             
                             unset($absLeave_obj_arr_late);
                             /*
                               if($alf->getRecordCount() > 0){
                                
                                 $a_obj =  $alf->getCurrent();
                                 
                                 if($a_obj->getAccrualPolicyID()==8)
                                 {
                                     $nof_short_leave++;
                                     $EmpDateStatus['status2']= 'SL';
                                 }
                                 else{
                                     $nof_leave++;
                                 }
                                
                            
                             }
                             else{
                                  $totLate = $totLate + abs($lateSec);
                            $late = gmdate("H:i", abs($lateSec));
                            $nof_late++;
                                 
                             }
                             */
                            
                        }
                        
                       }
                        $status1 = 'P';
                        $status2 = 'P';
                    } else {
                        $day = explode(' ', $date['date']);
                        if ($day[1] == 'Sun') {
                            if ($row_data_day_key[$date['day']]['worked_time'] != "") {
                                $status1 = 'POW';
                                $status2 = 'POW';
                                $nof_presence++;
                            } else {
                                $status1 = 'WO';
                                $status2 = 'WO';
                            }
                        }
                        else if ($day[1] == 'Sat') {
                            if ($row_data_day_key[$date['day']]['worked_time'] != "") {
                                $status1 = 'POW';
                                $status2 = 'POW';
                                $nof_presence++;
                            } else {
                                $status1 = 'WO';
                                $status2 = 'WO';
                            }
                        }else {
                            $status1 = 'AB';
                            $status2 = 'AB';
                        }
                    }
                    //echo'<pre>'; print_r($row_data_day_key); echo'<pre>'; die; 

                    if ($EmpDateStatus['status1'] == 'P') {
                        $nof_presence++;
                    }

                    $datetime1 = new DateTime();
                    $datetime1->setTimestamp($row_data_day_key[$date['day']]['min_punch_time_stamp']);
                    
                    $datetime2 = new DateTime();
                    $datetime2->setTimestamp($row_data_day_key[$date['day']]['max_punch_time_stamp']);
                    $interval = $datetime1->diff($datetime2);
                    //$total_worked_hours->add($interval);
                    $total_work_hr = $total_work_hr + $interval;
                    $date_int = $interval->format("%H:%I");

                    if ($row_data_day_key[$date['day']]['min_punch_time_stamp'] == '' || $row_data_day_key[$date['day']]['max_punch_time_stamp'] == '') {
                        $date_int = '';
                    }


                    $day_2 = explode(' ', $date['date']);

                    if ( ($day_2[1] == 'Sun' || $day_2[1] == 'Sat') && $row_data_day_key[$date['day']]['worked_time'] == "") {
                        $EmpDateStatus['status1'] = 'WO';
                    }
                    
                    if($EmpDateStatus['status1'] == 'A'){
                       
                        $alf = new AccrualListFactory();
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($rows['user_id'],55,$date['date_actual']);
                        
                                 $absLeave_obj_arr_leave = $alf->getCurrent()->data;
                                    if (!empty($absLeave_obj_arr_leave)) {


                                        if ($absLeave_obj_arr_leave['accrual_policy_id'] == 8 && $absLeave_obj_arr_leave['type_id']==55) {//Full day leave


                                                $nof_short_leave++;
                                                 $EmpDateStatus['status2']= 'SL';
                                        }
                                        else{
                                                if($absLeave_obj_arr_leave['amount']== -14400){
                                                     $nof_leave += 0.5;
                                                     $nof_no_pay += 0.5;
                                                     $EmpDateStatus['status2'] ='HLV';
                                                }else{ 
                                                    $nof_leave += abs($absLeave_obj_arr_leave['amount']/28800);
                                                    $EmpDateStatus['status2'] ='LV';

                                                      //$nof_leave++;
                                                }
                                            // echo $date_stamp.'<br>';
                                        }
                                    }
                                    
                                    unset($absLeave_obj_arr_leave);
          
                        
                        
                    }
                    elseif($EmpDateStatus['status1'] == 'P'){
                        
                        
                        $alf = new AccrualListFactory();
                        
                        $alf->getByAccrualByUserIdAndTypeIdAndDate($rows['user_id'],55,$date['date_actual']);
                        $absLeave_obj_arr_precent = $alf->getCurrent()->data;
                                    if (!empty($absLeave_obj_arr_precent)) {


                                        if ($absLeave_obj_arr_precent['accrual_policy_id'] == 8 && $absLeave_obj_arr_precent['type_id']==55) {//Full day leave


                                                //$nof_short_leave++;
                                                // $EmpDateStatus['status2']= 'SL';
                                        }
                                        else{
                                                if($absLeave_obj_arr_precent['amount']== -14400){
                                                    // $nof_leave += 0.5;
                                                    // $nof_presence -= 0.5;
                                                     $EmpDateStatus['status2'] ='HLV';
                                                }else{ 
                                                    $nof_leave += abs($absLeave_obj_arr_precent['amount']/28800);
                                                    $EmpDateStatus['status2'] ='LV';

                                                      //$nof_leave++;
                                                }
                                            // echo $date_stamp.'<br>';
                                        }
                                    }
                                    
                                    unset($absLeave_obj_arr_precent);
                        
                        
                    }


                    //  print_r($row_data_day_key)   ;           $datetime2              
                    $html = $html . '<tr align="center"  style="padding-top:25px;text-align:center;" valign="top">';
                    $html = $html . '<td  height="20" style="padding-top:25px;font-size:30px;text-align:center;" align="left"> ' . $date['date'] . '</td>';
                   // $html = $html . '<td  height="20"  style="font-size:30px;text-align:center;" >' . $row_data_day_key[$date['day']]['min_punch_time_stamp'] . '</td>';
                    if($row_data_day_key[$date['day']]['min_punch_time_stamp'] !=""){
                      $html = $html . '<td  height="20"  style="font-size:30px;text-align:center;" >' . $datetime1->format("H:i") . '</td>';
                    }
                    else{
                        $html = $html . '<td  height="20"  style="font-size:30px;text-align:center;" > </td>';
                    }
                    
                    
                   if($row_data_day_key[$date['day']]['max_punch_time_stamp'] !=""){
                      $html = $html . '<td  height="20" style="font-size:30px;text-align:center;">' . $datetime2->format("H:i") . '</td>';
                   }
                   else{
                       $html = $html . '<td  height="20"  style="font-size:30px;text-align:center;" > </td>';
                   }
                    // $html=  $html.'<td>'.$row_data_day_key[$date['day']]['worked_time'].'</td>'; 
                    $html = $html . '<td  height="20" style="font-size:30px;text-align:center;">' . $date_int . '</td>';
                    // $html=  $html.'<td>'.$row_data_day_key[$date['day']]['over_time'].'</td>';            
                    $html = $html . '<td  height="20" style="font-size:30px;text-align:center;">' . $EmpDateStatus['status1'] . '</td>';
                    $html = $html . '<td  height="20" style="font-size:30px;text-align:center;">' . $EmpDateStatus['status2'] . '</td>';
                    $html = $html . '<td height="20" style="font-size:30px;text-align:center;">' . $late . '</td>';
                    $html = $html . '<td height="20" style="font-size:30px;text-align:center;">' . $early . '</td>';
                    // $html=  $html.'<td></td>';        
                    // $html=  $html.'<td></td>';        
                    $html = $html . '</tr>';

                    $early = "";
                    $late = '';
                    $leave_arr[$EmpDateStatus['status2']] += 1;
                }

                //echo'<pre>'; print_r($row_data_day_key); die;

                $html = $html . '<tr>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>Total</b></td>';
                $html = $html . '<td></td>';
                $html = $html . '<td></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $rows['tot_data']['worked_time'] . '</td>';
                $html = $html . '<td></td>';
                $html = $html . '<td></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . gmdate("H:i", $totLate) . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . gmdate("H:i", $totEarly) . '</td>';
                $html = $html . '</tr>';

                $html = $html . '<tr>';
                $html = $html . '<td style="font-size:30px;text-align:center;"></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>Present</b></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>Leave</b></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>Short L</b></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>No Pay</b></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>OT</b></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>Late</b></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>Early</b></td>';
                $html = $html . '</tr>';

                $html = $html . '<tr>';
                $html = $html . '<td style="font-size:30px;text-align:center;"><b>Count</b></td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $nof_presence . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">'.$nof_leave.'</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">'.$nof_short_leave.' </td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">'.$nof_no_pay.'</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">'.$nof_ot_days.'</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $nof_late . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $nof_early . '</td>';
                $html = $html . '</tr>';


                $html = $html . '<tr>';
                $html = $html . '<td colspan="9">';

                $html = $html . '<table border="1">';

                /*
                  $html=  $html.'<tr>';
                  $html=  $html.'<td width="15%">No Of Days Worked: </td>';
                  $html=  $html.'<td width="05%"></td>';
                  $html=  $html.'<td>'.$nof_presence.'</td>';
                  $html=  $html.'<td></td>';
                  $html=  $html.'<td>Late / Early Hours :</td>';
                  $html=  $html.'<td>'.gmdate("H:i", ($totLate+$totEarly)).'</td>';
                  $html=  $html.'<td rowspan="4"></td>';
                  $html=  $html.'</tr>';
                 */
                /*
                  $html=  $html.'<tr>';
                  $html=  $html.'<td width="15%">Total Work Hrs: </td>';
                  $html=  $html.'<td></td>';

                  // $tot = $total_worked_hours_add->diff($total_worked_hours)->format("%H:%I");
                  $html=  $html.'<td>'.$rows['tot_data']['worked_time'].'</td>';
                  $html=  $html.'<td>'. $tot.'</td>';

                  $html=  $html.'<td></td>';
                  $html=  $html.'<td>No Pay Days :</td>';
                  $html=  $html.'<td>0.00</td>';
                  //$html=  $html.'<td></td>';
                  $html=  $html.'</tr>';
                 */
                /*
                  $html=  $html.'<tr>';
                  //$html=  $html.'<td colspan="6">&nbsp;</td>';
                  $html=  $html.'</tr>';
                 */
                $otplf = TTnew('OverTimePolicyListFactory');
                $allOtAccount = $otplf->getAll();
                if (count($allOtAccount) > 0) {
                    // $html=  $html.'<tr><td colspan="6">';
                    // $html=  $html. '<table border="0">';
                    foreach ($allOtAccount as $OtAccount) {
                        if (isset($rows['tot_data']['over_time_policy-' . $OtAccount->getId()])) {
                            /* $html=  $html.'<tr>';
                              $html=  $html.'<td width="15%">'.$OtAccount->getName().': </td>';
                              $html=  $html.'<td colspan="4">'.$rows['tot_data']['over_time_policy-'.$OtAccount->getId()].' Hrs. @ Rs...................Per Hours Rs...............</td>';
                              //$html=  $html.'<td></td>';
                              //$html=  $html.'<td></td>';
                              $html=  $html.'</tr>';

                             */
                        }
                    }

                    /* $html=  $html.'<tr>';
                      $html=  $html.'<td width="15%">Total OT Hours: </td>';
                      $html=  $html.'<td colspan="5">'.$rows['tot_data']['over_time'].' Total OT Amount Rs.......................................</td>';
                      //$html=  $html.'<td></td>';
                      //$html=  $html.'<td></td>';
                      $html=  $html.'</tr>';



                      $html=  $html.'</table>';
                     */

                    // $html=  $html.'</td>';
                }

                if (count($leave_arr) > 0) {
                    /*
                      $html=  $html.'<td valign="top"><table border="0">';
                      $html=  $html.'<tr>';
                      $html=  $html.'<td colspan="2">Leave Taken</td>';
                      $html=  $html.'</tr>';

                      $html=  $html.'<tr>';
                      $html=  $html.'<td>AL</td>';
                      $html=  $html.'<td>';
                      if(isset($leave_arr['AL']))
                      {
                      $html=  $html.$leave_arr['AL'];
                      }
                      else
                      {
                      $html=  $html.'0';
                      }
                      $html=  $html.'</td>';
                      $html=  $html.'</tr>';

                      $html=  $html.'<tr>';
                      $html=  $html.'<td>CL</td>';
                      $html=  $html.'<td>';
                      if(isset($leave_arr['CL']))
                      {
                      $html=  $html.$leave_arr['CL'];
                      }
                      else
                      {
                      $html=  $html.'0';
                      }
                      $html=  $html.'</td>';
                      $html=  $html.'</tr>';

                      $html=  $html.'<tr>';
                      $html=  $html.'<td>SL</td>';
                      $html=  $html.'<td>';
                      if(isset($leave_arr['SL']))
                      {
                      $html=  $html.$leave_arr['SL'];
                      }
                      else
                      {
                      $html=  $html.'0';
                      }
                      $html=  $html.'</td>';
                      $html=  $html.'</tr>';
                      $html=  $html.'</table></td>';
                     */

                    // print_r($leave_arr);
                } else {
                    //echo 'sss';
                }


                // $html=  $html.'</tr>';


                unset($leave_arr);


                $html = $html . '</table>';


                $html = $html . '</td>';
                $html = $html . '</tr>';
                $html = $html . '</table>';

                /*
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
                  $html= $html.'<td>P - Present / A - Absenrteism(No Pay) / LP - Late Present /ED - Early Departure / MIS - Miss Punch / POW - Present on Week Off / POH - Present On Holiday / HLD - Holiday / WO - Week Off /  CL - Casual Leave / AL - Annual Leave / SL - Sick Leave </td>';
                  $html=  $html.'</tr>';
                  $html=  $html.'</table>';

                 */
                
                $html = $html . '<br pagebreak="true" />';

                $j++;
            }
        }

        // print_r($EmpDateStatus['leave']);
        //die;
        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        unset($_SESSION['header_data']);

        //Close and output PDF document
        //$pdf->Output('example_006.pdf', 'I');
        $output = $pdf->Output('', 'S');

        //exit;  

        if (isset($output)) {
            return $output;
        }

        return FALSE;
    }

    //FL ADDED FOR EMPLOYEE TIME SHEET REPORT (National PVC) 20160601
    function EmployeeOverPocketSheet($data1, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {

        $total_worked_hours = new DateTime('00:00');
        $total_work_hr = 0;


        $total_worked_hours_add = clone $total_worked_hours;
        //echo '<pre>'; print_r($data1); die;

        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                //   $br_strng = implode(', ', $branch_list);
            }
            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        /* $_SESSION['header_data'] = array( 
          'image_path'   => $current_company->getLogoFileName(),
          'company_name' => $company_name,
          'address1'     => $addrss1,
          'address2'     => $address2,
          'city'         => $city,
          'province'     => $current_company->getProvince(),
          'postal_code'  => $postalcode,
          'heading'  => 'Employee Time Sheet - ',
          'line_width'  => 185,

          ); */


        $_SESSION['header_data'] = array(
            'image_path' => $current_company->getLogoFileName(),
            'company_name' => $company_name,
            'address1' => $addrss1,
            'address2' => $address2,
            'city' => $city,
            'province' => $current_company->getProvince(),
            'postal_code' => $postalcode,
            'heading' => 'Employee OP Report - ' . date('Y F', $pay_period_end),
            'group_list' => $gr_strng,
            'department_list' => $dep_strng,
            'branch_list' => $br_strng,
            'payperiod_end_date' => date('Y-M', $pay_period_end),
            'line_width' => 185,);

        $pdf = TTnew('TimeReportHeaderFooter');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, 23);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // add a page
        $pdf->AddPage('p', 'mm', 'A4');

        //Table border
        $pdf->setLineWidth(0.15);

        //set table position
        $adjust_x = 12;

        $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(30, $adjust_y));


        //TABLE CODE HERE

        $pdf->SetFont('', 'B', 7);
//            $data = $data;
        $html = '';
        $j = 0;

        foreach ($data1 as $key => $row) {
            $employee_number[$key] = $row['employee_number'];
        }

        array_multisort($employee_number, SORT_ASC, $data1);

        $page_last = 0;
        foreach ($data1 as $data) {
            $data['tot_data'] = $data['data'][count($data['data']) - 1];
            array_pop($data['data']); //delete tot of data array 
//            echo '<pre>';     print_r( $data ); echo '<pre>'; die;
            $pplf = TTnew('PayPeriodListFactory');
            if (isset($filter_data['pay_period_ids'][0])) {
                $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
            } else {
                $pay_period_start = $filter_data['start_date'];
                $pay_period_end = $filter_data['end_date'];
            }

            $dates = array();
            $current = $pay_period_start;
            $last = $pay_period_end;
            $j = 0;
            while ($current <= $last) {

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
            foreach ($data['data'] as $row1) {
                if ($row1['date_stamp'] != '') {
                    $row_dt = str_replace('/', '-', $row1['date_stamp']);

                    $dat_day = date('d', strtotime($row_dt));
                    $row_data_day_key[$dat_day] = $row1;
                }
            }


            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";


            if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
                $rows = $data;
                //echo '<pre>'; print_r($rows); echo '<pre>'; die;

                if ($ignore_last_row === TRUE) {
                    $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }



                $html = $html . '<table width="100%">';
                $html = $html . '<tr align="right" valign="top">';
                $html = $html . '<td><strong>EPF No :</strong> ' . $rows['employee_number'] . ' <br /> <strong>Name : </strong>' . $rows['full_name'] . '<br /> <strong>Department : </strong>' . $rows['default_department'] . '<br /> </td>';
                $html = $html . '</tr>';

                $html = $html . '</table>';
                //Header
                // create some HTML content
                $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="105%">
                        <tr style="background-color:#CCCCCC;text-align:center; padding:5px;" >';
//                $html = $html.'<td width = "3%">#</td>';
                $html = $html . '<td width="16%"><table><tr><td></td></tr><tr><td>Date</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="16%"><table><tr><td></td></tr><tr><td> In</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="16%"><table><tr><td></td></tr><tr><td> Out</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="16%"><table><tr><td></td></tr><tr><td>Worked Hrs</td></tr><tr><td></td></tr></table> </td>';
                // $html = $html.'<td width="9%"><table><tr><td></td></tr><tr><td>OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="16%" colspan=""><table><tr><td colspan="2"></td></tr><tr><td colspan="2">OP Nomal</td></tr></table> </td>';
                $html = $html . '<td width="16%"><table><tr><td></td></tr><tr><td>OP Holidays</td></tr><tr><td></td></tr></table> </td>';
                // $html = $html.'<td width="18%"><table><tr><td></td></tr><tr><td>Remarks</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '</tr>';

                $pdf->SetFont('', '', 8);

                $totLate = $totEarly = 0;

//print_r($dates) ; die;
                //             
                $nof_presence = 0;
                $date_intv_day = new DateInterval('PT0S');
                $op_day_time = '00:00';

                foreach ($dates as $date) {
                    //echo $row_data_day_key[$date['day']]['date_stamp'] ; die;

                    $dateStamp = '';
                    if ($row_data_day_key[$date['day']]['date_stamp'] != '') {
                        $dateStamp = DateTime::createFromFormat('d/m/Y', $row_data_day_key[$date['day']]['date_stamp'])->format('Y-m-d');
                    }

                    //unset($EmpDateStatus);

                    $EmpDateStatus = $this->getReportStatusByUserIdAndDate($rows['user_id'], $dateStamp);
                    //echo'<pre>'; print_r( $EmpDateStatus);   die;

                    $status1 = $status2 = '';
                    $earlySec = $lateSec = 0;
                    if (($row_data_day_key[$date['day']]['min_punch_time_stamp'] != '' && $row_data_day_key[$date['day']]['min_punch_time_stamp'] != "") &&
                            ($row_data_day_key[$date['day']]['shedule_start_time'] != "" && $row_data_day_key[$date['day']]['shedule_end_time'] != "")) {
                        $lateSec = strtotime($row_data_day_key[$date['day']]['shedule_start_time']) - strtotime($row_data_day_key[$date['day']]['min_punch_time_stamp']);
                        $earlySec = strtotime($row_data_day_key[$date['day']]['shedule_end_time']) - strtotime($row_data_day_key[$date['day']]['max_punch_time_stamp']);

                        if ($earlySec > 0) {
                            $totEarly = $totEarly + abs($earlySec);
                        }
                        if ($lateSec < 0) {
                            $totLate = $totLate + abs($lateSec);
                        }
                        $status1 = 'P';
                        $status2 = 'P';
                    } else {
                        $day = explode(' ', $date['date']);
                        if ($day[1] == 'Sun') {
                            if ($row_data_day_key[$date['day']]['worked_time'] != "") {
                                $status1 = 'POW';
                                $status2 = 'POW';
                                $nof_presence++;
                            } else {
                                $status1 = 'WO';
                                $status2 = 'WO';
                            }
                        } else {
                            $status1 = 'A';
                            $status2 = 'A';
                        }
                    }
                    //echo'<pre>'; print_r($row_data_day_key); echo'<pre>'; die; 

                    if ($EmpDateStatus['status1'] == 'P') {
                        $nof_presence++;
                    }

                    $datetime1 = new DateTime($row_data_day_key[$date['day']]['min_punch_time_stamp']);
                    $datetime2 = new DateTime($row_data_day_key[$date['day']]['max_punch_time_stamp']);
                    $interval = $datetime1->diff($datetime2);
                    //$total_worked_hours->add($interval);
                    $total_work_hr = $total_work_hr + $interval;
                    $date_int = $interval->format("%H:%I");

                    if ($row_data_day_key[$date['day']]['min_punch_time_stamp'] == '' || $row_data_day_key[$date['day']]['max_punch_time_stamp'] == '') {
                        $date_int = '';
                    }


                    $day_2 = explode(' ', $date['date']);

                    if ($day_2[1] == 'Sun' || $day_2[1] == 'Sat') {
                        $EmpDateStatus['status1'] = 'WO';
                    }


                    //  print_r($row_data_day_key)   ;                         
                    $html = $html . '<tr align="center"  style="padding-top:25px;text-align:center;" valign="top">';
                    $html = $html . '<td  height="15" style="padding-top:25px;font-size:38px;text-align:center;" align="left"> ' . $date['date'] . '</td>';
                    $html = $html . '<td  height="15"  style="font-size:38px;text-align:center;" >' . $row_data_day_key[$date['day']]['min_punch_time_stamp'] . '</td>';
                    $html = $html . '<td  height="15" style="font-size:38px;text-align:center;">' . $row_data_day_key[$date['day']]['max_punch_time_stamp'] . '</td>';
                    // $html=  $html.'<td>'.$row_data_day_key[$date['day']]['worked_time'].'</td>'; 
                    $html = $html . '<td  height="15" style="font-size:38px;text-align:center;">' . $date_int . '</td>';

                    $hlf = TTnew('HolidayListFactory');

                    $day_var = explode(' ', $date['date']);

                    $date_holidays = DateTime::createFromFormat('d/m/Y', $day_var[0])->format('Y-m-d');

                    // $date = new DateTime($day_var[0]);

                    $hlf->getByPolicyGroupUserIdAndDate($rows['user_id'], $date_holidays);
                    $hlf_obj = $hlf->getCurrent();

                    // $date_intv_day->add('PT'.$date['day']]['op_time'].'S');
                    $op_day_time = $this->sumarHoras($op_day_time, $row_data_day_key[$date['day']]['op_time']);


                    //$op_day_time += strtotime($row_data_day_key[$date['day']]['op_time']);

                    if (!empty($hlf_obj->data)) {
                        $html = $html . '<td  height="15" style="font-size:38px;text-align:center;"></td>';
                        $html = $html . '<td  height="15" style="font-size:38px;text-align:center;">' . $row_data_day_key[$date['day']]['op_time'] . '</td>';
                    } else {

                        $day_3 = explode(' ', $date['date']);
                        if ($day_2[1] == 'Sun' || $day_2[1] == 'Sat') {
                            $html = $html . '<td  height="15" style="font-size:38px;text-align:center;">' . '</td>';
                            $html = $html . '<td  height="15" style="font-size:38px;text-align:center;">' . $row_data_day_key[$date['day']]['op_time'] . '</td>';
                        } else {



                            $html = $html . '<td  height="15" style="font-size:38px;text-align:center;">' . $row_data_day_key[$date['day']]['op_time'] . '</td>';
                            $html = $html . '<td  height="15" style="font-size:38px;text-align:center;">' . '</td>';
                        }
                    }
                    //$html=  $html.'<td  height="20" style="font-size:38px;text-align:center;">'.$EmpDateStatus['status1'].'</td>';        
                    $html = $html . '';
                    $html = $html . '<td></td>';
                    // $html=  $html.'<td></td>';        
                    // $html=  $html.'<td></td>';        
                    $html = $html . '</tr>';

                    $leave_arr[$EmpDateStatus['status2']] += 1;
                }

                //echo'<pre>'; print_r($row_data_day_key); die;
                // $html=  $html.'<tr>'; 
                // $html=  $html.'<td colspan="9"></td>'; 
                // $html=  $html.'</tr>'; 
                $html = $html . '<tr>';
                $html = $html . '<td colspan="10">';

                $html = $html . '<table border="0">';

                /*
                  $html=  $html.'<tr>';
                  $html=  $html.'<td width="15%">No Of Days Worked: </td>';
                  $html=  $html.'<td width="05%"></td>';
                  $html=  $html.'<td>'.$nof_presence.'</td>';
                  $html=  $html.'<td></td>';
                  $html=  $html.'<td>Late / Early Hours :</td>';
                  $html=  $html.'<td>'.gmdate("H:i", ($totLate+$totEarly)).'</td>';
                  $html=  $html.'<td rowspan="4"></td>';
                  $html=  $html.'</tr>';
                 */
                /*
                  $html=  $html.'<tr>';
                  $html=  $html.'<td width="15%">Total Work Hrs: </td>';
                  $html=  $html.'<td></td>';

                  // $tot = $total_worked_hours_add->diff($total_worked_hours)->format("%H:%I");
                  $html=  $html.'<td>'.$rows['tot_data']['worked_time'].'</td>';
                  $html=  $html.'<td>'. $tot.'</td>';

                  $html=  $html.'<td></td>';
                  $html=  $html.'<td>No Pay Days :</td>';
                  $html=  $html.'<td>0.00</td>';
                  //$html=  $html.'<td></td>';
                  $html=  $html.'</tr>';
                 */
                /*
                  $html=  $html.'<tr>';
                  //$html=  $html.'<td colspan="6">&nbsp;</td>';
                  $html=  $html.'</tr>';
                 */
                $otplf = TTnew('OverTimePolicyListFactory');
                $allOtAccount = $otplf->getAll();
                if (count($allOtAccount) > 0) {
                    // $html=  $html.'<tr><td colspan="6">';
                    $html = $html . '<table border="0">';
                    foreach ($allOtAccount as $OtAccount) {
                        if (isset($rows['tot_data']['over_time_policy-' . $OtAccount->getId()])) {
                            /* $html=  $html.'<tr>';
                              $html=  $html.'<td width="15%">'.$OtAccount->getName().': </td>';
                              $html=  $html.'<td colspan="4">'.$rows['tot_data']['over_time_policy-'.$OtAccount->getId()].' Hrs. @ Rs...................Per Hours Rs...............</td>';
                              //$html=  $html.'<td></td>';
                              //$html=  $html.'<td></td>';
                              $html=  $html.'</tr>';

                             */
                        }
                    }


                    $html = $html . '<tr>';
                    $html = $html . '<td width="87%" style="font-size:38px;">Total OP Hours: </td>';
                    $html = $html . '<td colspan="5" style="font-size:38px;">' . $op_day_time . '</td>';
                    $html = $html . '<td>' . '</td>';
                    //$html=  $html.'<td></td>';
                    $html = $html . '</tr>';



                    $html = $html . '</table>';


                    // $html=  $html.'</td>';
                }

                if (count($leave_arr) > 0) {
                    /*
                      $html=  $html.'<td valign="top"><table border="0">';
                      $html=  $html.'<tr>';
                      $html=  $html.'<td colspan="2">Leave Taken</td>';
                      $html=  $html.'</tr>';

                      $html=  $html.'<tr>';
                      $html=  $html.'<td>AL</td>';
                      $html=  $html.'<td>';
                      if(isset($leave_arr['AL']))
                      {
                      $html=  $html.$leave_arr['AL'];
                      }
                      else
                      {
                      $html=  $html.'0';
                      }
                      $html=  $html.'</td>';
                      $html=  $html.'</tr>';

                      $html=  $html.'<tr>';
                      $html=  $html.'<td>CL</td>';
                      $html=  $html.'<td>';
                      if(isset($leave_arr['CL']))
                      {
                      $html=  $html.$leave_arr['CL'];
                      }
                      else
                      {
                      $html=  $html.'0';
                      }
                      $html=  $html.'</td>';
                      $html=  $html.'</tr>';

                      $html=  $html.'<tr>';
                      $html=  $html.'<td>SL</td>';
                      $html=  $html.'<td>';
                      if(isset($leave_arr['SL']))
                      {
                      $html=  $html.$leave_arr['SL'];
                      }
                      else
                      {
                      $html=  $html.'0';
                      }
                      $html=  $html.'</td>';
                      $html=  $html.'</tr>';
                      $html=  $html.'</table></td>';
                     */

                    // print_r($leave_arr);
                } else {
                    //echo 'sss';
                }


                // $html=  $html.'</tr>';


                unset($leave_arr);


                $html = $html . '</table>';


                $html = $html . '</td>';
                $html = $html . '</tr>';
                $html = $html . '</table>';

                $html = $html . '<table>';
                $html = $html . '<tr><td colspan="4"></td></tr>';
                $html = $html . '<tr><td colspan="4"></td></tr>';
                $html = $html . '<tr><td colspan="4"></td></tr>';

                $html = $html . '<tr align="center">';
                $html = $html . "<td >Employee's Signature </td>";
                $html = $html . '<td height="20">.........................</td>';
                $html = $html . '<td height="20"></td>';

                $html = $html . '</tr>';


                $html = $html . '<table>';
                $html = $html . '<tr><td colspan="4"></td></tr>';


                $html = $html . '<tr align="center">';
                $html = $html . '<td height="50" width="33%">Checked by </td>';
                $html = $html . '<td height="50" width="33%">Recommended by </td>';
                $html = $html . '<td height="50" width="33%">Approved by</td>';

                $html = $html . '</tr>';

                $html = $html . '<tr align="center" >';
                $html = $html . '<td >.............................................. </td>';
                $html = $html . '<td >.............................................. </td>';
                $html = $html . '<td >.............................................. </td>';

                $html = $html . '</tr>';
                $html = $html . '</tr>';
                $html = $html . '<tr align="center">';
                $html = $html . '<td> </td>';
                $html = $html . '<td>Immediate Superior </td>';
                $html = $html . '<td>Head of the Department </td>';

                $html = $html . '</tr>';


                // $html=  $html.'<tr><td colspan="4"></td></tr>';
                $html = $html . '</table>';

                /*
                  $html=  $html.'<table width="105%" border="1">';
                  $html=  $html.'<tr align="center">';
                  $html= $html.'<td>P - Present / A - Absenrteism(No Pay) / LP - Late Present /ED - Early Departure / MIS - Miss Punch / POW - Present on Week Off / POH - Present On Holiday / HLD - Holiday / WO - Week Off /  CL - Casual Leave / AL - Annual Leave / SL - Sick Leave </td>';
                  $html=  $html.'</tr>';
                  $html=  $html.'</table>';

                 */

                if (count($data1) > ($page_last + 1)) {
                    $html = $html . '<br pagebreak="true" />';
                }
                $page_last++;
                $j++;
            }
        }

        // print_r($EmpDateStatus['leave']);
        //die;
        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');



        unset($_SESSION['header_data']);

        //Close and output PDF document
        //$pdf->Output('example_006.pdf', 'I');
        $output = $pdf->Output('', 'S');

        //exit;  

        if (isset($output)) {
            return $output;
        }

        return FALSE;
    }

    function sumarHoras($acumuladoTime, $nuevoTime) {

        //Se esperan parametros así:
        //$acumuladoTime="02:45";
        //$nuevoTime="04:36";
        //echo "Hora acumulada: $acumuladoTime"."<br>";
        //echo "Nuevo tiempo acumulado: $nuevoTime"."<br>";

        /* Tiempo acumulado */
        $myArrayAcumuladoTime = explode(":", $acumuladoTime);

        $hrsAcumuladoTime = $myArrayAcumuladoTime[0];
        $minsAcumuladoTime = $myArrayAcumuladoTime[1];

        /* Nuevo Time */
        $myArrayNewTime = explode(":", $nuevoTime);

        $hraNewTime = $myArrayNewTime[0];
        $minNewTime = $myArrayNewTime[1];

        /* Calculo */
        $sumHrs = $hrsAcumuladoTime + $hraNewTime;
        $sumMins = $minsAcumuladoTime + $minNewTime;

        /* Si se pasan los MINUTOS */
        if ($sumMins > 59) {
            /* Quitamos hora para dejarlo en minutos y se la sumamos a la de horas */
            $sumMins -= 60;
            $sumHrs += 1;
        }

        // echo "Total hrs agregadas: $sumHrs:$sumMins"."<br>";
        return "$sumHrs:$sumMins";
    }

    //FL ADDED FOR EMPLOYEE TIME SHEET REPORT (National PVC) 20160816
    function EmployeeLeaveBalance($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data;

            //echo '<pre>'; print_r($data); die;

            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $current_company->getName(),
                'address1' => $current_company->getAddress1(),
                'address2' => $current_company->getAddress2(),
                'city' => $current_company->getCity(),
                'province' => $current_company->getProvince(),
                'postal_code' => $current_company->getPostalCode(),
                'heading' => 'Leave Balance Summary',
                'line_width' => 185,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

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
            $pdf->AddPage('p', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE

            $pdf->SetFont('', 'B', 7);

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
            /* $html = $html.'<table border="1" cellspacing="0" cellpadding="0" width="105%">
              <tr style="background-color:#CCCCCC;text-align:center; padding:5px;" >'; */
//                $html = $html.'<td width = "3%">#</td>';
            //echo '<pre>';                print_r($columns); 
            //echo '<pre>'; print_r($rows); die;
            /* foreach ($columns as $column){
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

              } */


            $html = '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <thead>
                        <tr style="background-color:#CCCCCC;text-align:center;" >';
            $html = $html . '<td height="17" width = "5%">#</td>';
            $html = $html . '<td width = "10%">Emp. No.</td>';
            $html = $html . '<td width = "45%">Emp. Name</td>';
            $html = $html . '<td width = "25%">Total Balance</td>';
            $html = $html . '</tr></thead>';

            $html = $html . '<tbody>';


            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows);

            $pdf->SetFont('', '', 8);

            $x = 1;
            foreach ($rows as $row) {

                if ($x % 2 == 0) {
                    $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                } else {
                    $html = $html . '<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                }
                $html = $html . '<td  width = "5%" height="25">' . $x . '</td>';
                $html = $html . '<td width = "10%">' . $row['employee_number'] . '</td>';
                $html = $html . '<td width = "45%" align="left">' . $row['full_name'] . '</td>';
                $html = $html . '<td width = "25%" align="left">' . $row['total_balance'] . '</td>';
                $html = $html . '</tr>';

                $x++;
            }

            $html = $html . '</tbody>';
            $html = $html . '</table>';

            echo $html;

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
            $output = $pdf->Output('', 'S');

            //exit;  

            if (isset($output)) {
                return $output;
            }

            return FALSE;
        }
    }

    //FL ADDED FOR GET REPORT STATUS 20160819. 
    function getReportStatusByUserIdAndDate($user_id, $date) {
        $status1 = $status2 = '';
        $all_status = array('status1_all' => '', 'status2_all' => '', 'status1' => '', 'status2' => '');
        $udlf = TTnew('UserDateListFactory');

        $udlf->getByUserIdAndDate($user_id, $date);
        $udlf_obj = $udlf->getCurrent();
        $user_date_id = $udlf_obj->getId();



        //echo '<br>user_date_id....'.$user_date_id.'..date..'.$date;

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

        $date_name = date('l', strtotime($date));

        // echo $date; 

        if (!empty($plf_obj->data)) {
            $status1 = 'P'; //Present
            $all_status['status1_all'] .= ' P';

            if (empty($slf_obj->data)) {
                $status2 = 'POW'; //Present On Week Off
                $all_status['status2_all'] .= ' POW';
            }
        } else {
            if (!empty($slf_obj->data)) {
                $status1 = 'A'; //Absent
                $all_status['status1_all'] .= ' A';
            } else {

                $hlf = TTnew('HolidayListFactory');
                $hlf->getByPolicyGroupUserIdAndDate($user_id, $date);
                $hlf_obj = $hlf->getCurrent();

                if (!empty($hlf_obj->data)) {

                    $status1 = 'HLD'; //Absent Week Off
                    $all_status['status1_all'] .= ' HLD';
                } else {


                    $status1 = 'A'; //Absent Week Off
                    $all_status['status1_all'] .= ' AB';
                }
            }
        }

        if (!empty($elf_obj->data)) {
            //	if($epclf_obj->getExceptionPolicyControlID()) {
            foreach ($elf as $elf_obj) {
                if ($elf_obj->getExceptionPolicyID() == '29' || $elf_obj->getExceptionPolicyID() == '5') {
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
        if (!empty($hlf_obj->data)) {
            $status1 = 'HLD'; //Holiday
            $all_status['status1_all'] .= ' HLD';
            if (!empty($plf_obj->data)) {
                $status2 = 'POH'; //Present on Holiday
                $all_status['status2_all'] .= ' POH';
            }
        }
     
/*
        $aluerlf = TTnew('AbsenceLeaveUserEntryRecordListFactory');
        $aluerlf->getAbsencePolicyByUserDateId($user_date_id);
        $aluerlf_obj = $aluerlf->getCurrent();
        if (!empty($aluerlf_obj->data)) {
            $leaveName_arr = explode(' ', $aluerlf_obj->data['absence_name']);
            //$status2 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1); //Leave Type
            $all_status['status2'] .= ' ' . substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
        }
*/

        $all_status['status1'] = $status1;
        $all_status['status2'] = $status2;
        //echo '<pre>'; print_r($all_status); //die;
        return $all_status;
    }

    //Added by Thilini 2018-20-27 for aqua fresh
    function OverTimeSummaryMonthSheetEmployee($data1, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $total_worked_hours = new DateTime('00:00');
        $total_work_hr = 0;

        $total_worked_hours_add = clone $total_worked_hours;

        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );
        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
            }
            $br_strng = $blf->getNameById($br_id);

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $_SESSION['header_data'] = array(
            'image_path' => $current_company->getLogoFileName(),
            'company_name' => $company_name,
            'address1' => $addrss1,
            'address2' => $address2,
            'city' => $city,
            'province' => $current_company->getProvince(),
            'postal_code' => $postalcode,
            'heading' => ' ',
            'group_list' => $gr_strng,
            'department_list' => $dep_strng,
            'branch_list' => $br_strng,
            'payperiod_end_date' => date('Y-M', $pay_period_end),
            'line_width' => 185,);

        $pdf = TTnew('TimeReportHeaderFooter');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, 23);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // add a page
        $pdf->AddPage('p', 'mm', 'A4');

        //Table border
        $pdf->setLineWidth(0.15);

        //set table position
        // $adjust_x = 12;         
        // $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(30, $adjust_y) );
        $pdf->SetFont('', 'B', 8);
        $html = '';
        $j = 0;

        foreach ($data1 as $key => $row) {
            $employee_number[$key] = $row['employee_number'];
        }

        array_multisort($employee_number, SORT_ASC, $data1);
        $row_count = 0;

        $start_year = date('Y', $filter_data['start_date']);
        $end_year = date('Y', $filter_data['end_date']);

        $start_month = date('m', $filter_data['start_date']);
        $end_month = date('m', $filter_data['end_date']);

        if ($start_month == 1) {
            $month_count = intval($end_month);
        } else {
            $month_count = ($end_month - $start_month) + 1;
        }

        //if not selected pay period in the interface
        if (!isset($filter_data['pay_period_ids'][0])) {
            $all_employees = array_values(array_unique($employee_number));

            $current_array_count = count($data1);
            $correct_array_count = count($all_employees) * $month_count;
            if ($current_array_count != $correct_array_count) {
                for ($a = 0; $a < count($all_employees); $a++) {
                    for ($x = 0; $x < count($data1); $x++) {
                        if ($data1[$x]['employee_number'] == $all_employees[$a]) {
                            $num_arr [$a] = $x;
                        }
                    }
                }
                for ($a = 0; $a < count($all_employees); $a++) {
                    $num_arr2 [$a] = $num_arr [$a] - $num_arr [$a - 1];
                }
                $num_arr2 [0] = $num_arr2 [0] + 1;
            }
            for ($x = 0; $x < count($data1); $x++) {
                for ($a = 0; $a < count($all_employees); $a++) {
                    if ($current_array_count != $correct_array_count) {
                        $month_count = $num_arr2 [$a];
                    }
                    for ($z = 1; $z < $month_count; $z++) {
                        if ($data1[$x + $z]['data'] != array()) {
                            $data1[$x]['data'] = array_merge($data1[$x]['data'], $data1[$x + $z]['data']);
                        }
                    }
                    $array1 [] = $data1[$x];
                    $x = $x + $month_count;
                }
            }
            $data1 = array();
            $data1 = $array1;
        }
        $page_last = 0;


        $count = 0;

        foreach ($data1 as $data) {
            
            $pplf = TTnew('PayPeriodListFactory');
            if (isset($filter_data['pay_period_ids'][0])) {
                $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
            } else {
                $pay_period_start = $filter_data['start_date'];
                $pay_period_end = $filter_data['end_date'];
            }

            $dates = array();
            $current = $pay_period_start;
            $last = $pay_period_end;
            $j = 0;
            $months = array();
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['month'] = date('m', $current);
                $dates[$j]['year'] = date('Y', $current);
                $months[$j] = date('Y-m', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $dates[$j]['date_get'] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }
            $month_array = array_values(array_unique($months));

            $row_data_day_key = array();
            foreach ($data['data'] as $row1) {
                if ($row1['date_stamp'] != '') {
                    $row_dt = str_replace('/', '-', $row1['date_stamp']);

                    $dat_day = date('Y-m-d', strtotime($row_dt));
                    $row_data_day_key[$dat_day] = $row1;
                }
            }

           // echo '<pre>';print_r($row_data_day_key);exit;
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";

            if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
                $rows = $data;
                
                if ($ignore_last_row === TRUE) {
                    $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }

                $start_date = date('d \of F Y', $pay_period_start);
                $end_date = date('d \of F Y', $pay_period_end);

                $html = $html . '<table width="100%" border="0">';
                $html = $html . '<tr align="left" valign="top">';
                $html = $html . '<td><font size="15"><strong>OT Summary - From ' . $start_date . ' To ' . $end_date . '</strong></font></td>';
                $html = $html . '</tr>';
                $html = $html . '</table>';
                $html = $html . '<br/>';

                $html = $html . '<table width="100%">';
                $html = $html . '<tr align="right" valign="top">';
                $html = $html . '<td><strong>EPF No :</strong> ' . $rows['employee_number'] . ' <br /> <strong>Name : </strong>' . $rows['full_name'] . '<br /> <strong>Department : </strong>' . $rows['default_department'] . '<br /> </td>';
                $html = $html . '</tr>';

                $html = $html . '</table>';
                //Header
                // create some HTML content
                $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="105%"><tr style="background-color:#CCCCCC;text-align:center; padding:5px;" >';
                $html = $html . '<td width="15%"><table><tr><td></td></tr><tr><td>Month</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Total Working Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Weekday OT</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Weekday Amount</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Holiday OT</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Holiday Amount</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Total OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Total OT Amount</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '</tr>';

                $pdf->SetFont('', '', 8);

                $sum_daily_ot_mins = 0;
                $sum_holiday_ot_mins = 0;
                $month_total_worked_time = 0;
                $total_mon_daily_ot = 0;
                $total_mon_holiday_ot = 0;

                
                foreach ($month_array as $month) {
                   
                    $month_data = explode("-", $month);
                    $actual_hrs = 0;
                    $actual_mins = 0;
                    $total_mon_daily_ot_mins = 0;
                    $total_mon_holiday_ot_mins = 0;
                    $total_mon_daily_ot = 0;
                    $total_mon_holiday_ot = 0;
                    $month_sum_daily_ot_wage = 0;
                    $month_sum_holiday_ot_wage = 0;
                    $sum_mon_array = array ();
                foreach ($dates as $date) {
                        if ($date['year'] == $month_data[0] && $date['month'] == $month_data[1]) {
                      
                        if(!empty($row_data_day_key[$date['date_get']])){
                          array_push($sum_mon_array,$this->convertTimeToMinFormat($row_data_day_key[$date['date_get']]['worked_time']));
                        }
                            $holiday_ot_wage = 0;
                            $policy_array = Array();
                            $searchword = 'over_time_policy-';
                            $matches = array();
                            foreach ($row_data_day_key[$date['date_get']] as $k => $v) {
                                if (preg_match("/\b$searchword\b/i", $k)) {
                                    $matches[] = $k;
                                }
                            }
                            $otplf = TTnew('OverTimePolicyListFactory');
                            for ($z = 0; $z < count($matches); $z++) {
                                $policy_array_temp = explode("-", $matches[$z]);
                                $policy_array[$z]['overtime_policy_id'] = $policy_array_temp[1];
                                $otplf_obj_holiday = $otplf->getById($policy_array_temp[1]);
                                if ($otplf_obj_holiday->getRecordCount() > 0) {
                                    foreach ($otplf_obj_holiday as $ot_policy_data) {
                                        $trigger_time = $ot_policy_data->getTriggerTime();
                                        $overtime_rate = $ot_policy_data->getRate();
                                    }
                                }
                                $policy_array[$z]['trigger_time'] = $trigger_time;
                                $policy_array[$z]['overtime_rate'] = $overtime_rate;
                            }

                            usort($policy_array, function($a, $b) {
                                return $a['overtime_policy_id'] - $b['overtime_policy_id'];
                            });

                            $datetime1 = new DateTime($row_data_day_key[$date['date_get']]['min_punch_time_stamp']);
                            $datetime2 = new DateTime($row_data_day_key[$date['date_get']]['max_punch_time_stamp']);
                            $interval = $datetime1->diff($datetime2);
                            $total_work_hr = $total_work_hr + $interval;
                            $date_int = $interval->format("%H:%I");

                            if ($row_data_day_key[$date['date_get']]['min_punch_time_stamp'] == '' || $row_data_day_key[$date['date_get']]['max_punch_time_stamp'] == '') {
                                $date_int = 0;
                            }
                            if ($date_int == 0) {
                                $actual_hrs += 0;
                                $actual_mins += 0;
                            } else {
                                $actual_time_temp = explode(":", $date_int);
                                $actual_hrs += intval($actual_time_temp[0]);
                                $actual_mins += intval($actual_time_temp[1]);
                            }

                            $hlf = TTnew('HolidayListFactory');
                            $day_var = explode(' ', $date['date']);
//                            print_r($day_var); die;
                            $date_holidays = DateTime::createFromFormat('d/m/Y', $day_var[0])->format('Y-m-d');

                            $hlf->getByPolicyGroupUserIdAndDate($rows['user_id'], $date_holidays);

                            $hlf_obj = $hlf->getCurrent();
                            if (!empty($hlf_obj->data)) {
                                $daily_ot = 0;
                                $holiday_ot = $row_data_day_key[$date['date_get']]['over_time'];
                            } else {
                                if($day_var[1] == 'Sun' || $day_var[1] == 'Sat' && $row_data_day_key[$date['date_get']]['over_time_policy_id']!=3){
                                    $daily_ot = 0;
                                    $holiday_ot = $row_data_day_key[$date['date_get']]['over_time'];
                                } else {
                                    $daily_ot = $row_data_day_key[$date['date_get']]['over_time'];
                                    $holiday_ot = 0;
                                }
                                
                                
                            }
                            $daily_ot_temp = explode(":", $daily_ot);
                            $daily_ot_mins = (intval($daily_ot_temp[0]) * 60) + intval($daily_ot_temp[1]);

                            $holiday_ot_temp = explode(":", $holiday_ot);
                            $holiday_ot_mins = (intval($holiday_ot_temp[0]) * 60) + intval($holiday_ot_temp[1]);

                            $total_mon_daily_ot_mins += $daily_ot_mins;
                            $total_mon_holiday_ot_mins += $holiday_ot_mins;

                            //user's hourly wage 
                            $hourly_wage = $row_data_day_key[$date['date_get']]['hourly_wage'];

                            if (isset($row_data_day_key[$date['date_get']]['over_time_policy_id'])) {

                                $otplf_obj = $otplf->getById($row_data_day_key[$date['date_get']]['over_time_policy_id']);
                                if ($otplf_obj->getRecordCount() > 0) {
                                    foreach ($otplf_obj as $ot_policy_data) {
                                        $overtime_rate = $ot_policy_data->getRate();
                                    }
                                }
                            }
                            if ($date['date_get'] == $hlf_obj->data['date_stamp'] || $day_var[1] == 'Sun' || $day_var[1] == 'Sat') {
                                $holiday_ot_temp = explode(":", $holiday_ot);
                                $holiday_ot_mins = (intval($holiday_ot_temp[0]) * 60) + intval($holiday_ot_temp[1]);

                                if (count($policy_array) > 1) {
                                    for ($y = 0; $y < count($policy_array); $y++) {
                                        if ($y != count($policy_array) - 1) {
                                            $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                            $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                            $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                        } else {
                                            $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                            $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                        }

                                        if ($holiday_ot_mins < $trigger_time) {
                                            $holiday_ot_wage = $holiday_ot_mins * (($hourly_wage * $overtime_rate2 ) / 60);
                                            break;
                                        } else {
                                            $holiday_ot_wage = ($trigger_time * (($hourly_wage * $overtime_rate2 ) / 60)) + (($holiday_ot_mins - $trigger_time) * (($hourly_wage * $overtime_rate3) / 60));
                                            break;
                                        }
                                    }
                                } else if (count($policy_array) == 1) {
                                    $overtime_rate = $policy_array[0]['overtime_rate'];
                                    $holiday_ot_wage = $holiday_ot_mins * (($hourly_wage * $overtime_rate ) / 60);
                                }


                                $month_sum_holiday_ot_wage += $holiday_ot_wage;
                            } else {
                                //normal working day
                                $daily_ot_temp = explode(":", $daily_ot);
                                $daily_ot_mins = (intval($daily_ot_temp[0]) * 60) + intval($daily_ot_temp[1]);
                                $daily_ot_wage = $daily_ot_mins * (($hourly_wage * $overtime_rate) / 60);
                                $month_sum_daily_ot_wage += $daily_ot_wage;
                            }
                        }
                        
                    }
                    $total_min = array_sum($sum_mon_array);
                    //total worked hrs
                    $additional_hrs = intval($actual_mins / 60);
                    $additional_mins = $actual_mins % 60;
                    $total_hours = $actual_hrs + $additional_hrs;
                    $month_total_worked_time = $total_hours . ":" . $additional_mins;

                    //total daily ot hrs
                    $total_mon_daily_ot = $this->convertMinutesToHourFormat($total_mon_daily_ot_mins);

                    //total holiday ot hrs
                    $total_mon_holiday_ot = $this->convertMinutesToHourFormat($total_mon_holiday_ot_mins);

                    //total ot hrs
                    $total_ot_mins = $total_mon_daily_ot_mins + $total_mon_holiday_ot_mins;
                    $total_ot_mins_hrs = $this->convertMinutesToHourFormat($total_ot_mins);

                    //total ot wage
                    $total_ot_wage = number_format((float) $month_sum_daily_ot_wage, 2, '.', '') + number_format((float) $month_sum_holiday_ot_wage, 2, '.', '');

                    $html = $html . '<tr align="center" height="17"  style="padding-top:25px;text-align:center;" valign="top">';
                    $html = $html . '<td style="padding-top:25px;font-size:32px;text-align:center;" align="center">' . date('F Y', strtotime($month)) . '</td>';
                    $html = $html . '<td style="padding-top:25px;font-size:32px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($total_min) . '</td>';
                    $html = $html . '<td style="font-size:30px;text-align:center;" >' . $total_mon_daily_ot . '</td>';
                    $html = $html . '<td style="font-size:30px;text-align:center;">' . number_format((float) $month_sum_daily_ot_wage, 2, '.', '') . '</td>';
                    $html = $html . '<td style="font-size:30px;text-align:center;">' . $total_mon_holiday_ot . '</td>';
                    $html = $html . '<td style="font-size:30px;text-align:center;">' . number_format((float) $month_sum_holiday_ot_wage, 2, '.', '') . '</td>';
                    $html = $html . '<td style="font-size:30px;text-align:center;">' . $total_ot_mins_hrs . '</td>';
                    $html = $html . '<td style="font-size:30px;text-align:center;">' . number_format((float) $total_ot_wage, 2, '.', '') . '</td>';

                    $html = $html . '</tr>';
                }
                $html = $html . '<tr>';
                $html = $html . '<td colspan="9">';
                $html = $html . '<br/>';
                $html = $html . '<label> Count :</label>' . count($month_array);
                $html = $html . '<table border="1">';

                $html = $html . '</table>';

                $html = $html . '</td>';
                $html = $html . '</tr>';
                $html = $html . '</table>';

                if (count($data1) > ($page_last + 1)) {
                    $html = $html . '<br pagebreak="true" />';
                }
                $page_last++;
                $j++;
            }

            $count++;
        }
        $html = $html . '</table>';

        $html = $html . '<br/>';

        $pdf->writeHTML($html, true, false, true, false, '');
        unset($_SESSION['header_data']);
        $output = $pdf->Output('', 'S');

        if (isset($output)) {
            return $output;
        }

        return FALSE;
    }

    //Added by Thilini 2018-03-13 for aqua fresh
    function OverTimeSummaryMonthSheetDept($data1, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {

        $total_worked_hours = new DateTime('00:00');
        $total_work_hr = 0;

        $total_worked_hours_add = clone $total_worked_hours;

        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );
        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
            }
            $br_strng = $blf->getNameById($br_id);

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $_SESSION['header_data'] = array(
            'image_path' => $current_company->getLogoFileName(),
            'company_name' => $company_name,
            'address1' => $addrss1,
            'address2' => $address2,
            'city' => $city,
            'province' => $current_company->getProvince(),
            'postal_code' => $postalcode,
            'heading' => ' ',
            'group_list' => $gr_strng,
            'department_list' => $dep_strng,
            'branch_list' => $br_strng,
            'payperiod_end_date' => date('Y-M', $pay_period_end),
            'line_width' => 180,);

        $pdf = TTnew('TimeReportHeaderFooter');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, 23);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // add a page
        $pdf->AddPage('p', 'mm', 'A4');

        //Table border
        $pdf->setLineWidth(0.15);

        //set table position
        // $adjust_x = 12;         
        // $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(30, $adjust_y) );
        $pdf->SetFont('', '', 8);
        $html = '';
        $j = 0;

        foreach ($data1 as $key => $row) {
            $employee_number[$key] = $row['employee_number'];
        }

        array_multisort($employee_number, SORT_ASC, $data1);
        $row_count = 0;

        $start_year = date('Y', $filter_data['start_date']);
        $end_year = date('Y', $filter_data['end_date']);

        $start_month = date('m', $filter_data['start_date']);
        $end_month = date('m', $filter_data['end_date']);

        if ($start_month == 1) {
            $month_count = intval($end_month);
        } else {
            $month_count = ($end_month - $start_month) + 1;
        }

        //if not selected pay period in the interface
        if (!isset($filter_data['pay_period_ids'][0])) {
            $all_employees = array_values(array_unique($employee_number));

            $current_array_count = count($data1);
            $correct_array_count = count($all_employees) * $month_count;
            if ($current_array_count != $correct_array_count) {
                for ($a = 0; $a < count($all_employees); $a++) {
                    for ($x = 0; $x < count($data1); $x++) {
                        if ($data1[$x]['employee_number'] == $all_employees[$a]) {
                            $num_arr [$a] = $x;
                        }
                    }
                }
                for ($a = 0; $a < count($all_employees); $a++) {
                    $num_arr2 [$a] = $num_arr [$a] - $num_arr [$a - 1];
                }
                $num_arr2 [0] = $num_arr2 [0] + 1;
            }
            for ($x = 0; $x < count($data1); $x++) {
                for ($a = 0; $a < count($all_employees); $a++) {
                    if ($current_array_count != $correct_array_count) {
                        $month_count = $num_arr2 [$a];
                    }
                    for ($z = 1; $z < $month_count; $z++) {
                        if ($data1[$x + $z]['data'] != array()) {
                            $data1[$x]['data'] = array_merge($data1[$x]['data'], $data1[$x + $z]['data']);
                        }
                    }
                    $array1 [] = $data1[$x];
                    $x = $x + $month_count;
                }
            }
            $data1 = array();
            $data1 = $array1;
        }
        $page_last = 0;

        $count = 0;

        for ($z = 0; $z < count($data1); $z++) {
            $departments[] = $data1[$z]['default_department'];
        }
        $departments_array = array_values(array_unique($departments));

        $start_date_formatted = date('d \of F Y', $pay_period_start);
        $end_date_formatted = date('d \of F Y', $pay_period_end);

        $html = $html . '<table width="100%" border="0">';
        $html = $html . '<tr align="left" valign="top">';
        $html = $html . '<td><font size="15"><strong>OT Summary - From ' . $start_date_formatted . ' To ' . $end_date_formatted . '</strong></font></td>';
        $html = $html . '</tr>';
        $html = $html . '</table>';
        $html = $html . '<br/>';

        foreach ($departments_array as $department) {
            $html = $html . '<table width="100%" border="0">';
            $html = $html . '<td><font size="10"><strong>Department: ' . $department . '</strong></font></td>';
            $html = $html . '</table>';
            $html = $html . '<br/>';

            $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="105%"><tr style="background-color:#CCCCCC;text-align:center; padding:5px;" >';
            $html = $html . '<td width="15%"><table><tr><td></td></tr><tr><td>Month</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="14%"><table><tr><td></td></tr><tr><td>Weekday OT</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="14%"><table><tr><td></td></tr><tr><td>Weekday Amount</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="14%"><table><tr><td></td></tr><tr><td>Holiday OT</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="14%"><table><tr><td></td></tr><tr><td>Holiday Amount</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="14%"><table><tr><td></td></tr><tr><td>Total OT Hrs</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="15%"><table><tr><td></td></tr><tr><td>Total OT Amount</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '</tr>';

            $pplf = TTnew('PayPeriodListFactory');
            if (isset($filter_data['pay_period_ids'][0])) {
                $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
            } else {
                $pay_period_start = $filter_data['start_date'];
                $pay_period_end = $filter_data['end_date'];
            }

            $dates = array();
            $current = $pay_period_start;
            $last = $pay_period_end;
            $j = 0;
            $months = array();
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['month'] = date('m', $current);
                $dates[$j]['year'] = date('Y', $current);
                $months[$j] = date('Y-m', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $dates[$j]['date_get'] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }
            $month_array = array_values(array_unique($months));

            $total_mon_holiday_ot = 0;
            $final_dept_mon_daily_ot_wage = 0;
            $final_dept_mon_holiday_ot_wage = 0;
            foreach ($month_array as $month) {
                $month_sum_daily_ot_wage_dept = 0;
                $month_sum_holiday_ot_wage_dept = 0;
                $total_mon_holiday_ot_dept = 0;
                $total_mon_daily_ot_dept = 0;
                $total_ot_mins = 0;
                $total_ot_wage_dept = 0;
                foreach ($data1 as $data) {
                    if ($data['default_department'] == $department) {

                        $data['tot_data'] = $data['data'][count($data['data']) - 1];
                        array_pop($data['data']); //delete tot of data array 
                        $row_data_day_key = array();
                        foreach ($data['data'] as $row1) {
                            if ($row1['date_stamp'] != '') {
                                $row_dt = str_replace('/', '-', $row1['date_stamp']);

                                $dat_day = date('Y-m-d', strtotime($row_dt));
                                $row_data_day_key[$dat_day] = $row1;
                            }
                        }


                        $ignore_last_row = TRUE;
                        $include_header = TRUE;
                        $eol = "\n";
                        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
                            $rows = $data;

                            if ($ignore_last_row === TRUE) {
                                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                            }

                            $start_date = date('d \of F Y', $pay_period_start);
                            $end_date = date('d \of F Y', $pay_period_end);

                            $sum_daily_ot_mins = 0;
                            $sum_holiday_ot_mins = 0;

                            $month_total_worked_time = 0;
                            $total_mon_daily_ot_mins = 0;
                            $total_mon_holiday_ot_mins = 0;
                            $month_data = explode("-", $month);
                            $actual_hrs = 0;
                            $actual_mins = 0;
                            $month_sum_holiday_ot_wage = 0;
                            $month_sum_daily_ot_wage = 0;
                            foreach ($dates as $date) {
                                
                                if ($date['year'] == $month_data[0] && $date['month'] == $month_data[1]) {
                                    $holiday_ot_wage = 0;
                                    $policy_array = Array();
                                    $searchword = 'over_time_policy-';
                                    $matches = array();
                                    foreach ($row_data_day_key[$date['date_get']] as $k => $v) {
                                        if (preg_match("/\b$searchword\b/i", $k)) {
                                            $matches[] = $k;
                                        }
                                    }
                                    $otplf = TTnew('OverTimePolicyListFactory');
                                    for ($z = 0; $z < count($matches); $z++) {
                                        $policy_array_temp = explode("-", $matches[$z]);
                                        $policy_array[$z]['overtime_policy_id'] = $policy_array_temp[1];
                                        $otplf_obj_holiday = $otplf->getById($policy_array_temp[1]);
                                        if ($otplf_obj_holiday->getRecordCount() > 0) {
                                            foreach ($otplf_obj_holiday as $ot_policy_data) {
                                                $trigger_time = $ot_policy_data->getTriggerTime();
                                                $overtime_rate = $ot_policy_data->getRate();
                                            }
                                        }
                                        $policy_array[$z]['trigger_time'] = $trigger_time;
                                        $policy_array[$z]['overtime_rate'] = $overtime_rate;
                                    }

                                    usort($policy_array, function($a, $b) {
                                        return $a['overtime_policy_id'] - $b['overtime_policy_id'];
                                    });
                                    $datetime1 = new DateTime($row_data_day_key[$date['date_get']]['min_punch_time_stamp']);
                                    $datetime2 = new DateTime($row_data_day_key[$date['date_get']]['max_punch_time_stamp']);
                                    $interval = $datetime1->diff($datetime2);
                                    $total_work_hr = $total_work_hr + $interval;
                                    $date_int = $interval->format("%H:%I");

                                    if ($row_data_day_key[$date['date_get']]['min_punch_time_stamp'] == '' || $row_data_day_key[$date['date_get']]['max_punch_time_stamp'] == '') {
                                        $date_int = 0;
                                    }
                                    if ($date_int == 0) {
                                        $actual_hrs += 0;
                                        $actual_mins += 0;
                                    } else {
                                        $actual_time_temp = explode(":", $date_int);
                                        $actual_hrs += intval($actual_time_temp[0]);
                                        $actual_mins += intval($actual_time_temp[1]);
                                    }

                                    $hlf = TTnew('HolidayListFactory');
                                    $day_var = explode(' ', $date['date']);
                                    $date_holidays = DateTime::createFromFormat('d/m/Y', $day_var[0])->format('Y-m-d');

                                    $hlf->getByPolicyGroupUserIdAndDate($rows['user_id'], $date_holidays);

                                    $hlf_obj = $hlf->getCurrent();
                                    if (!empty($hlf_obj->data)) {
                                        $daily_ot = 0;
                                        $holiday_ot = $row_data_day_key[$date['date_get']]['over_time'];
                                    } else {
                                        if($day_var[1] == 'Sun' || $day_var[1] == 'Sat'){
                                            $daily_ot = 0;
                                            $holiday_ot = $row_data_day_key[$date['date_get']]['over_time'];
                                        } else {
                                            $daily_ot = $row_data_day_key[$date['date_get']]['over_time'];
                                            $holiday_ot = 0;
                                        }
                                    }
                                    $daily_ot_temp = explode(":", $daily_ot);
                                    $daily_ot_mins = (intval($daily_ot_temp[0]) * 60) + intval($daily_ot_temp[1]);
                                    

                                    $holiday_ot_temp = explode(":", $holiday_ot);
                                    $holiday_ot_mins = (intval($holiday_ot_temp[0]) * 60) + intval($holiday_ot_temp[1]);

                                    $total_mon_daily_ot_mins += $daily_ot_mins;
                                    $total_mon_holiday_ot_mins += $holiday_ot_mins;


                                    //user's hourly wage 
                                    $hourly_wage = $row_data_day_key[$date['date_get']]['hourly_wage'];

                                    if (isset($row_data_day_key[$date['date_get']]['over_time_policy_id'])) {

                                        $otplf_obj = $otplf->getById($row_data_day_key[$date['date_get']]['over_time_policy_id']);
                                        if ($otplf_obj->getRecordCount() > 0) {
                                            foreach ($otplf_obj as $ot_policy_data) {
                                                $overtime_rate = $ot_policy_data->getRate();
                                            }
                                        }
                                    }

                                    if ($date['date_get'] == $hlf_obj->data['date_stamp'] || $day_var[1] == 'Sun' || $day_var[1] == 'Sat') {
                                        //if a holiday
                                        $holiday_ot_temp = explode(":", $holiday_ot);
                                        $holiday_ot_mins = (intval($holiday_ot_temp[0]) * 60) + intval($holiday_ot_temp[1]);

                                        if (count($policy_array) > 1) {
                                            for ($y = 0; $y < count($policy_array); $y++) {

                                                if ($y != count($policy_array) - 1) {
                                                    $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                                    $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                                } else {
                                                    $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                                }
                                                if ($holiday_ot_mins < $trigger_time) {
                                                    $holiday_ot_wage = $holiday_ot_mins * (($hourly_wage * $overtime_rate2) / 60 );
                                                    break;
                                                } else {
                                                    $holiday_ot_wage = ($trigger_time * (($hourly_wage * $overtime_rate2) / 60 )) + (($holiday_ot_mins - $trigger_time) * (($hourly_wage * $overtime_rate3) / 60));
                                                    break;
                                                }
                                            }
                                        } else if (count($policy_array) == 1) {
                                            $overtime_rate = $policy_array[0]['overtime_rate'];
                                            $holiday_ot_wage = $holiday_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
                                        }

                                        $month_sum_holiday_ot_wage += $holiday_ot_wage;
                                    } else {
                                        //normal working day
                                        
                                        $daily_ot_temp = explode(":", $daily_ot);
                                        $daily_ot_mins = (intval($daily_ot_temp[0]) * 60) + intval($daily_ot_temp[1]);
                                        $daily_ot_wage = $daily_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
                                        $month_sum_daily_ot_wage += $daily_ot_wage;
                                        
                                    }
                                }
                            }

                            //total daily ot hrs in department
                            $total_mon_daily_ot_dept += $total_mon_daily_ot_mins;

                            //total holiday ot hrs
                            $total_mon_holiday_ot_dept += $total_mon_holiday_ot_mins;

                            //total ot hrs
                            $total_ot_mins += $total_mon_daily_ot_mins + $total_mon_holiday_ot_mins;
                            //$total_ot_mins_hrs = $this->convertMinutesToHourFormat($total_ot_mins);
                            //total daily ot wage in dept
                            $month_sum_daily_ot_wage_dept += $month_sum_daily_ot_wage;

                            //total holiday ot wage in dept

                            $month_sum_holiday_ot_wage_dept += $month_sum_holiday_ot_wage;

                            //total ot wage in dept
                            $total_ot_wage_dept += $month_sum_daily_ot_wage + $month_sum_holiday_ot_wage;
                        }
                    }
                }

                $final_dept_mon_daily_ot = $this->convertMinutesToHourFormat($total_mon_daily_ot_dept);
                $final_dept_mon_holiday_ot = $this->convertMinutesToHourFormat($total_mon_holiday_ot_dept);
                $final_dept_mon_daily_ot_wage = number_format((float) $month_sum_daily_ot_wage_dept, 2, '.', '');
                $final_dept_mon_holiday_ot_wage = number_format((float) $month_sum_holiday_ot_wage_dept, 2, '.', '');
                $final_total_ot = $this->convertMinutesToHourFormat($total_ot_mins);
                $final_total_ot_wage = number_format((float) $total_ot_wage_dept, 2, '.', '');

                $html = $html . '<tr align="center" height="17"  style="padding-top:25px;text-align:center;" valign="top">';
                $html = $html . '<td style="padding-top:25px;font-size:32px;text-align:center;" align="center">' . date('F Y', strtotime($month)) . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;" >' . $final_dept_mon_daily_ot . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $final_dept_mon_daily_ot_wage . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $final_dept_mon_holiday_ot . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $final_dept_mon_holiday_ot_wage . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $final_total_ot . '</td>';
                $html = $html . '<td style="font-size:30px;text-align:center;">' . $final_total_ot_wage . '</td>';

                $html = $html . '</tr>';
            }
            $html = $html . '</table>';
            $html = $html . '<label> Count :</label>' . count($month_array);
            $html = $html . '<br/>';
            $html = $html . '<br/>';
        }

        $html = $html . '</table>';

        $html = $html . '<br/>';

        $pdf->writeHTML($html, true, false, true, false, '');
        unset($_SESSION['header_data']);
        $output = $pdf->Output('', 'S');

        if (isset($output)) {
            return $output;
        }

        return FALSE;
    }

    //Added by Thilini 2018-20-26 for aqua fresh
    function OverTimeSummarySheet($data1, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $total_worked_hours = new DateTime('00:00');
        $total_work_hr = 0;

       // print_r($data1);exit;
        $total_worked_hours_add = clone $total_worked_hours;

        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );
        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
            }
            $br_strng = $blf->getNameById($br_id);

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $_SESSION['header_data'] = array(
            'image_path' => $current_company->getLogoFileName(),
            'company_name' => $company_name,
            'address1' => $addrss1,
            'address2' => $address2,
            'city' => $city,
            'province' => $current_company->getProvince(),
            'postal_code' => $postalcode,
            'heading' => 'OT Summary Report - ' . date('Y F', $pay_period_end),
            'group_list' => $gr_strng,
            'department_list' => $dep_strng,
            'branch_list' => $br_strng,
            'payperiod_end_date' => date('Y-M', $pay_period_end),
            'line_width' => 275,);

        $pdf = TTnew('TimeReportHeaderFooter');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 50, 23);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // add a page
        $pdf->AddPage('l', 'mm', 'A2');

        //Table border
        $pdf->setLineWidth(0.15);

        //set table position
        $adjust_x = 12;

        // $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(30, $adjust_y) );
        $pdf->SetFont('', '', 6.5);
        $html = '';
        $j = 0;

        foreach ($data1 as $key => $row) {
            $employee_number[$key] = $row['employee_number'];
        }

        array_multisort($employee_number, SORT_ASC, $data1);

        $page_last = 0;
        $html = $html . '<br/><br/>';
        $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="110%"><tr style="background-color:#CCCCCC;text-align:center; padding:1px;" >';
        $html = $html . '<td width="2.5%"><table><tr><td></td></tr><tr><td>Emp No</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="8%"><table><tr><td></td></tr><tr><td>Name</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="7%"><table><tr><td></td></tr><tr><td> Department</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="7%"><table><tr><td></td></tr><tr><td> Designation</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Worked Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Weekday OT</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Weekday Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Sunday Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Sunday Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Sunday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Sunday OT(Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Saturday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Saturday OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Poya Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Poya Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Poya OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Poya OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Salary Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total OT Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '</tr></table>';

        $count = 0;
        $data_no = 1;
        $page_no = 1;
        $max_data_rows = 9;
        $last_page_data_no = 0;
       
        foreach ($data1 as $data) {
            $data['tot_data'] = $data['data'][count($data['data']) - 1];
            array_pop($data['data']);
            $daily_ot_wage = 0;
            $dates = array();
            $current = $pay_period_start;
            $last = $pay_period_end;
            $j = 0;
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $dates[$j]['date_get'] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }
            $row_data_day_key = array();
            foreach ($data['data'] as $row1) {
                if ($row1['date_stamp'] != '') {
                    $row_dt = str_replace('/', '-', $row1['date_stamp']);

                    $dat_day = date('d', strtotime($row_dt));
                    $row_data_day_key[$dat_day] = $row1;
                }
            }
            
            $total_sun_ot_mins = 0;
            $total_sat_ot_mins = 0;
            $total_satu_ot_mins = 0;
            $total_poya_ot_mins = 0;
            $total_daily_ot_mins = 0;
            
            $sum_daily_ot_wage = 0;
            $sum_sun_ot_wage = 0;
            $sum_sat_ot_wage = 0;
            $sum_satu_ot_wage = 0;
            $sum_poya_ot_wage = 0;
            
            $total_poya_salary_mins = 0;
            $total_sun_salary_mins = 0;  
            $total_sat_salary_mins = 0;
            $total_satu_salary_mins = 0;
            
            $sum_poya_salary_wage = 0;
            $sum_sun_salary_wage = 0;
            $sum_sat_salary_wage = 0;
            $sum_satu_salary_wage = 0;        
           
            $actual_hrs = 0;
            $actual_mins = 0;
                    
            foreach ($dates as $date) {
//                echo '<pre><br> Date::' . $date['day'];
//                print_r($data);
                $poya_ot_wage = 0;
                $poya_salary = 0;
                $sat_ot_wage = 0;
                $sat_salary = 0;
                $sun_ot_wage = 0;
                $sun_salary = 0;
                $satu_ot_wage = 0;
                $satu_salary = 0;
                
                $poya_ot_display_min = 0;
                $sun_ot_display_min = 0; 
                $sat_ot_display_min = 0;
                $satu_ot_display_min = 0;
                
                $poya_salary_display_min = 0;
                $sun_salary_display_min = 0;   
                $sat_salary_display_min = 0;      
                $satu_salary_display_min =  0;
                        
                $policy_array = Array();
                $searchword = 'over_time_policy-';
                $matches = array();
                foreach ($row_data_day_key[$date['day']] as $k => $v) {
                    if (preg_match("/\b$searchword\b/i", $k)) {
                        $matches[] = $k;
                    }
                }
                $otplf = TTnew('OverTimePolicyListFactory');
                for ($z = 0; $z < count($matches); $z++) {
                    $policy_array_temp = explode("-", $matches[$z]);
                    $policy_array[$z]['overtime_policy_id'] = $policy_array_temp[1];
                    $otplf_obj_holiday = $otplf->getById($policy_array_temp[1]);
                    if ($otplf_obj_holiday->getRecordCount() > 0) {
                        foreach ($otplf_obj_holiday as $ot_policy_data) {
                            $trigger_time = $ot_policy_data->getTriggerTime();
                            $overtime_rate = $ot_policy_data->getRate();
                            $max_time = $ot_policy_data->getMaxTime();
                        }
                    }
                    $policy_array[$z]['trigger_time'] = $trigger_time;
                    $policy_array[$z]['overtime_rate'] = $overtime_rate;
                    $policy_array[$z]['max_time'] = $max_time;
                }
                usort($policy_array, function($a, $b) {
                    return $a['overtime_policy_id'] - $b['overtime_policy_id'];
                });
                
                $dateStamp = '';
                if ($row_data_day_key[$date['day']]['date_stamp'] != '') {
                    $dateStamp = DateTime::createFromFormat('d/m/Y', $row_data_day_key[$date['day']]['date_stamp'])->format('Y-m-d');
                }

                $EmpDateStatus = $this->getReportStatusByUserIdAndDate($data['user_id'], $dateStamp);

                $datetime1 = new DateTime($row_data_day_key[$date['day']]['min_punch_time_stamp']);
                $datetime2 = new DateTime($row_data_day_key[$date['day']]['max_punch_time_stamp']);
                $interval = $datetime1->diff($datetime2);
                $total_work_hr = $total_work_hr + $interval;
                $date_int = $interval->format("%H:%I");
                if ($row_data_day_key[$date['day']]['min_punch_time_stamp'] == '' || $row_data_day_key[$date['day']]['max_punch_time_stamp'] == '') {
                    $date_int = 0;
                }
                if ($date_int == 0) {
                    $actual_hrs += 0;
                    $actual_mins += 0;
                } else {
                    $actual_time_temp = explode(":", $date_int);
                    $actual_hrs += intval($actual_time_temp[0]);
                    $actual_mins += intval($actual_time_temp[1]);
                }

                $day_2 = explode(' ', $date['date']);
                
                //user's hourly wage 
                $hourly_wage = $row_data_day_key[$date['day']]['hourly_wage'];
                
                if (isset($row_data_day_key[$date['day']]['over_time_policy_id'])) {
                    $otplf_obj = $otplf->getById($row_data_day_key[$date['day']]['over_time_policy_id']);
                    if ($otplf_obj->getRecordCount() > 0) {
                        foreach ($otplf_obj as $ot_policy_data) {
                            $overtime_rate = $ot_policy_data->getRate();
                        }
                    }
                }

                $hlf = TTnew('HolidayListFactory');

                $date_holidays = DateTime::createFromFormat('d/m/Y', $day_2[0])->format('Y-m-d');

                $hlf->getByPolicyGroupUserIdAndDate($data['user_id'], $date_holidays);
                $hlf_obj = $hlf->getCurrent();
                $holiday_policy_array = $hlf_obj->data;
                if (!empty($holiday_policy_array)) {
                    if($holiday_policy_array['holiday_policy_id'] == '1'){
                        //poya holiday
                        $daily_ot = 0;
                        $statutory_ot = 0;
                        $sunday_ot = 0 ;
                        $saturday_ot = 0;
                        $poya_ot = $row_data_day_key[$date['day']]['over_time'];
                        $poya_ot_temp = explode(":", $poya_ot);
                        $poya_ot_mins = (intval($poya_ot_temp[0]) * 60) + intval($poya_ot_temp[1]);
//                        echo '<br>poya min::'.$poya_ot_mins;
//                        print_r($policy_array);
                        if(count($policy_array) > 1){
                            for ($y = 0; $y < count($policy_array); $y++) {
                                if ($y != count($policy_array) - 1) {
                                    $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                    $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                } else {
                                    
                                    $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                }
                                
                                if ($poya_ot_mins < $trigger_time) {
                                    $poya_salary_display_min = $poya_ot_mins;
                                    $poya_salary = $poya_ot_mins * (($hourly_wage * $overtime_rate2) / 60 );
                                    break;
                                } else {
                                    $poya_salary_display_min = $trigger_time;
                                    $poya_ot_display_min = $poya_ot_mins - $trigger_time;
                                    $poya_salary = ($trigger_time * (($hourly_wage * $overtime_rate2) / 60 ));
                                    $poya_ot_wage = ($poya_ot_display_min * (($hourly_wage * $overtime_rate3) / 60));
                                    break;
                                }
                                
                            }                       
                        } else if(count($policy_array) == 1) {
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $poya_salary_display_min = $poya_ot_mins;
                            $poya_salary = $poya_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
                        }
//                        echo '<br>p_salary_mins::'.$poya_salary_display_min;
//                        echo '<br>p_ot_mins::'.$poya_ot_display_min;
//                        echo '<br>p_ot_wage::'.$poya_ot_wage;
//                        echo '<br>p_salary_wage::'.$poya_salary;
                        
                        $total_poya_salary_mins += $poya_salary_display_min;
                        $total_poya_ot_mins += $poya_ot_display_min;
                        $sum_poya_ot_wage += $poya_ot_wage;
                        $sum_poya_salary_wage += $poya_salary;
                        
//                        echo '<br>s_salary_mins::'.$total_poya_salary_mins;
//                        echo '<br>s_ot_mins::'.$total_poya_ot_mins;
//                        echo '<br>s_ot_wage::'.$sum_poya_ot_wage;
//                        echo '<br>s_salary_wage::'.$sum_poya_salary_wage;
                        
                    } else if ($holiday_policy_array['holiday_policy_id'] == '2'){
                        //satutory holiday
                        $daily_ot = 0;
                        $saturday_ot = 0;
                        $sunday_ot = 0 ;
                        $poya_ot = 0;
                        $statutory_ot = $row_data_day_key[$date['day']]['over_time'];
                        $statutory_ot_temp = explode(":", $statutory_ot);
                        $statutory_ot_mins = (intval($statutory_ot_temp[0]) * 60) + intval($statutory_ot_temp[1]);
//                        echo '<br>statutory min::'.$statutory_ot_mins;
                         if(count($policy_array) > 1){
                            for ($y = 0; $y < count($policy_array); $y++) {
                                if ($y != count($policy_array) - 1) {
                                    $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                    $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                } else {
                                    $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                }
                                
                                if ($statutory_ot_mins < $trigger_time) {
                                    $satu_salary_display_min = $statutory_ot_mins;
                                    $satu_salary = $statutory_ot_mins * (($hourly_wage * $overtime_rate2) / 60 );
                                    break;
                                } else {
                                    $satu_salary_display_min = $trigger_time;
                                    $satu_ot_display_min = $statutory_ot_mins - $trigger_time;
                                    $satu_salary = ($trigger_time * (($hourly_wage * $overtime_rate2) / 60 ));
                                    $satu_ot_wage = ($satu_ot_display_min * (($hourly_wage * $overtime_rate3) / 60));
                                    break;
                                }
                                
                            }                       
                        } else if(count($policy_array) == 1) {
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $satu_salary_display_min = $statutory_ot_mins;
                            $satu_salary = $statutory_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
                        }
                        $total_satu_salary_mins += $satu_salary_display_min;
                        $total_satu_ot_mins += $satu_ot_display_min;
                        $sum_satu_ot_wage += $satu_ot_wage;
                        $sum_satu_salary_wage += $satu_salary;
                    }
                } else {
                    if($day_2[1] == 'Sun'){
                        //sunday holiday 
                        
                        $daily_ot = 0;
                        $saturday_ot = 0;
                        $statutory_ot = 0 ;
                        $poya_ot = 0;
                        $sunday_ot = $row_data_day_key[$date['day']]['over_time'];
                        $sunday_ot_temp = explode(":", $sunday_ot);
                        $sunday_ot_mins = (intval($sunday_ot_temp[0]) * 60) + intval($sunday_ot_temp[1]);
//                        echo '<br>sunday min::'.$sunday_ot_mins;
                        if(count($policy_array) > 1){
                            for ($y = 0; $y < count($policy_array); $y++) {
                                if ($y != count($policy_array) - 1) {
                                    $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                    $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                } else {
                                    $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                }
                                
                                if ($sunday_ot_mins < $trigger_time) {
                                    $sun_salary_display_min = $sunday_ot_mins;
                                    $sun_salary = $sunday_ot_mins * (($hourly_wage * $overtime_rate2) / 60 );
                                    break;
                                } else {
                                    $sun_salary_display_min = $trigger_time;
                                    $sun_ot_display_min = $sunday_ot_mins - $trigger_time;
                                    $sun_salary = ($trigger_time * (($hourly_wage * $overtime_rate2) / 60 ));
                                    $sun_ot_wage = ($sun_ot_display_min * (($hourly_wage * $overtime_rate3) / 60));
                                    break;
                                }
                                
                            }                       
                        } else if(count($policy_array) == 1) {
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $sun_salary_display_min = $sunday_ot_mins;
                            $sun_salary = $sun_salary_display_min * (($hourly_wage * $overtime_rate) / 60 );
                        }
                        
//                        echo '<br>sun_salary_mins::'.$sun_salary_display_min;
//                        echo '<br>sun_ot_mins::'.$sun_ot_display_min;
//                        echo '<br>sun_ot_wage::'.$sun_ot_wage;
//                        echo '<br>sun_salary_wage::'.$sun_salary;
                        
                        $total_sun_salary_mins += $sun_salary_display_min;
                        $total_sun_ot_mins += $sun_ot_display_min;
                        $sum_sun_ot_wage += $sun_ot_wage;
                        $sum_sun_salary_wage += $sun_salary;
                        
//                        echo '<br>total_sun_salary_mins::'.$total_sun_salary_mins;
//                        echo '<br>total_sun_ot_mins::'.$total_sun_ot_mins;
//                        echo '<br>sum_sun_ot_wage::'.$sum_sun_ot_wage;
//                        echo '<br>sum_sun_salary_wage::'.$sum_sun_salary_wage;
                        
                    } else if($day_2[1] == 'Sat') {
                        //saturday ot
                        $daily_ot = 0;
                        $sunday_ot = 0;
                        $statutory_ot = 0 ;
                        $poya_ot = 0;
                        $saturday_ot = $row_data_day_key[$date['day']]['over_time'];
                        $saturday_ot_temp = explode(":", $saturday_ot);
                        $saturday_ot_mins = (intval($saturday_ot_temp[0]) * 60) + intval($saturday_ot_temp[1]);
//                         echo '<br>saturday min::'.$saturday_ot_mins;
                        
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $sat_ot_display_min = $saturday_ot_mins;
                            $sat_ot_wage = $saturday_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
                        
                        $total_sat_ot_mins += $sat_ot_display_min;
                        $sum_sat_ot_wage += $sat_ot_wage;
                    } else {
                        //daily ot
                        $sunday_ot = 0;
                        $statutory_ot = 0 ;
                        $poya_ot = 0;
                        $saturday_ot = 0;
                        $daily_ot = $row_data_day_key[$date['day']]['over_time'];
                        
                        $daily_ot_temp = explode(":", $daily_ot);
                        $daily_ot_mins = (intval($daily_ot_temp[0]) * 60) + intval($daily_ot_temp[1]);
//                         echo '<br>daily min::'.$daily_ot_mins;
                        $daily_ot_wage = $daily_ot_mins * (($hourly_wage * $overtime_rate) / 60);
                        $total_daily_ot_mins += $daily_ot_mins;
                        $sum_daily_ot_wage += $daily_ot_wage;
                    }
                    
                }
            }
//            die;
           //total worked hrs
            $additional_hrs = intval($actual_mins / 60);
            $additional_mins = $actual_mins % 60;
            $total_hours = $actual_hrs + $additional_hrs;
            if(strlen($total_hours) < 2){
                $total_hours = "0" . $total_hours;
            }
            if(strlen($additional_mins) < 2){
                $additional_mins = "0".$additional_mins;
            }
            $total_worked_time = $total_hours . ":" . $additional_mins;
            
            if(isset($data['tot_data']['worked_time']) ){
                $final_worked_time = $data['tot_data']['worked_time'];
            }  else {
                 $final_worked_time = '00:00';
            }

            $total_salary_hrs = $total_sun_salary_mins + $total_poya_salary_mins + $total_satu_salary_mins;
            $total_salary_wage = $sum_sun_salary_wage + $sum_poya_salary_wage +$sum_satu_salary_wage;
                    
            $total_ot_hours = $total_daily_ot_mins + $total_sun_ot_mins  + $total_sat_ot_mins  + $total_poya_ot_mins  + $total_satu_ot_mins;
            $sum_all_ot_wage = $sum_daily_ot_wage +  $sum_sun_ot_wage  + $sum_sat_ot_wage  + $sum_poya_ot_wage  + $sum_satu_ot_wage;

            $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="110%"><tr align="center" height="15"  style="padding-top:25px;text-align:center;" valign="top">';
            $html = $html . '<td width="2.5%"><table><tr><td></td></tr><tr><td>' . $data['employee_number'] . '</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="8%"><table><tr><td></td></tr><tr><td>' . $data['full_name'] . '</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="7%"><table><tr><td></td></tr><tr><td> ' . $data['default_department'] . '</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="7%"><table><tr><td></td></tr><tr><td> ' . $data['title'] . '</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>' . $final_worked_time . '</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>' . $this->convertMinutesToHourFormat($total_daily_ot_mins) . '</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>' . number_format((float) $sum_daily_ot_wage, 2, '.', '') . '</td></tr><tr><td></td></tr></table> </td>';

            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.$this->convertMinutesToHourFormat($total_sun_salary_mins).'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>'.number_format((float) $sum_sun_salary_wage, 2, '.', '').'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.$this->convertMinutesToHourFormat($total_sun_ot_mins).'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>'.number_format((float) $sum_sun_ot_wage, 2, '.', '').'</td></tr><tr><td></td></tr></table> </td>';

            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.$this->convertMinutesToHourFormat($total_sat_ot_mins).'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.number_format((float) $sum_sat_ot_wage, 2, '.', '').'</td></tr><tr><td></td></tr></table> </td>';

            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.$this->convertMinutesToHourFormat($total_poya_salary_mins).'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>'.number_format((float) $sum_poya_salary_wage, 2, '.', '').'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.$this->convertMinutesToHourFormat($total_poya_ot_mins).'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>'.number_format((float) $sum_poya_ot_wage, 2, '.', '').'</td></tr><tr><td></td></tr></table> </td>';

            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.$this->convertMinutesToHourFormat($total_satu_salary_mins).'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.number_format((float) $sum_satu_salary_wage, 2, '.', '').'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.$this->convertMinutesToHourFormat($total_satu_ot_mins).'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>'.number_format((float) $sum_satu_ot_wage, 2, '.', '').'</td></tr><tr><td></td></tr></table> </td>';

            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>'.$this->convertMinutesToHourFormat($total_salary_hrs).'</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>' . number_format((float) $total_salary_wage, 2, '.', '') . '</td></tr><tr><td></td></tr></table> </td>';
            
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>' . $this->convertMinutesToHourFormat($total_ot_hours) . '</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>' . number_format((float) $sum_all_ot_wage, 2, '.', '') . '</td></tr><tr><td></td></tr></table> </td>';
            $html = $html . '</tr></table>';

            $dept_sum_daily_ot += $total_daily_ot_mins;
            $dept_sum_weekday_amount += $sum_daily_ot_wage;
            
            $dept_sun_sal_hrs += $total_sun_salary_mins;
            $dept_sun_sal_amount += $sum_sun_salary_wage;
            $dept_sun_ot_hrs += $total_sun_ot_mins;
            $dept_sun_ot_amount += $sum_sun_ot_wage;
            
            $dept_sat_ot_hrs += $total_sat_ot_mins;
            $dept_sat_ot_amount += $sum_sat_ot_wage;
            
            $dept_poya_sal_hrs += $total_poya_salary_mins;
            $dept_poya_sal_amount += $sum_poya_salary_wage;
            $dept_poya_ot_hrs += $total_poya_ot_mins;
            $dept_poya_ot_amount += $sum_poya_ot_wage;
            
            $dept_satu_sal_hrs += $total_satu_salary_mins;
            $dept_satu_sal_amount += $sum_satu_salary_wage;
            $dept_satu_ot_hrs += $total_satu_ot_mins;
            $dept_satu_ot_amount += $sum_satu_ot_wage;

//            echo '<pre>count data::'.count($data1);
//            echo '<pre>page no::'.$page_no;
//            echo '<pre>max_data_rows::'.$max_data_rows;
//            echo '<pre>calculation::'.(count($data1)/$max_data_rows);
//            echo '<br>round::'.ceil((count($data1)/$max_data_rows));
//            echo '<pre>Data No::'.$data_no;
//            echo '<pre>exceed max for page ::'.($page_no*$max_data_rows);
//            echo '<br>-----------------------------------';
            if(ceil(count($data1)/$max_data_rows) == $page_no){ // if the last page
//                $page_no++;
//                $html = $html . '<label> Last Page </label>';
                $last_page_data_no++;
//                echo '<pre>in last page ::';
            } else if($data_no == ($page_no*$max_data_rows)) {
                $html = $html . '<br pagebreak="true" />';
                $html = $html . '<br/><br/>';
                $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="110%"><tr style="background-color:#CCCCCC;text-align:center; padding:1px;" >';
                $html = $html . '<td width="2.5%"><table><tr><td></td></tr><tr><td>Emp No</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="8%"><table><tr><td></td></tr><tr><td>Name</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="7%"><table><tr><td></td></tr><tr><td> Department</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="7%"><table><tr><td></td></tr><tr><td> Designation</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Worked Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Weekday OT</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Weekday Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Sunday Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Sunday Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Sunday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Sunday OT(Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Saturday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Saturday OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Poya Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Poya Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Poya OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Poya OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Salary Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total OT Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '</tr></table>';
                $page_no++;
            } 
           $data_no++;
           $count++; 

        } 
      
        $overoll_salary_mins = $dept_sun_sal_hrs + $dept_poya_sal_hrs + $dept_satu_sal_hrs;
        $overoll_salary_amount = $dept_sun_sal_amount + $dept_poya_sal_amount + $dept_satu_sal_amount;
        
        $overoll_ot_mins = $dept_sum_daily_ot + $dept_sun_ot_hrs  +$dept_sat_ot_hrs  +$dept_poya_ot_hrs  + $dept_satu_ot_hrs;
        $overall_ot_amount = $dept_sum_weekday_amount  + $dept_sun_ot_amount +$dept_sat_ot_amount + $dept_poya_ot_amount +$dept_satu_ot_amount;
        
        $html = $html . '</table>';
//        print_r($last_page_data_no);  
        if($last_page_data_no > 5){
//            echo 'here';
            $html = $html . '<br pagebreak="true" />';
        } 
//        die;
        $html = $html . '<br/>';
        $html = $html . '<label> Count :</label>' . $count;

        $html = $html . '<br/><br/>';

        $html = $html . '<label> Month : </label>' . date('Y F', $pay_period_end) . '<br/>';
        $html = $html . '<label> Department : </label>' . $dep_strng;
        $html = $html . '<br/><br/>';
        
        $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="105%"><tr style="background-color:#CCCCCC;text-align:center;" >';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Weekday OT</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Weekday Amount</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Sunday Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Sunday Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Sunday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Sunday OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
       
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Saturday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Saturday OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Poya Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Poya Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Poya OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Poya OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Statutory Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Statutory Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Statutory OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Statutory OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="6%"><table><tr><td></td></tr><tr><td>Total Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="6%"><table><tr><td></td></tr><tr><td>Salary Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="6%"><table><tr><td></td></tr><tr><td>Total OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="6%"><table><tr><td></td></tr><tr><td>OT Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '</tr>';

        $html = $html . '<tr align="center" style="padding-top:25px;text-align:center;" valign="top">';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;height:25px;" align="center">' . $this->convertMinutesToHourFormat($dept_sum_daily_ot) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_sum_weekday_amount, 2, '.', '') . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_sun_sal_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_sun_sal_amount, 2, '.', '') . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_sun_ot_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_sun_ot_amount, 2, '.', '') . '</td>';
        
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_sat_ot_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_sat_ot_amount, 2, '.', '') . '</td>';
        
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_poya_sal_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_poya_sal_amount, 2, '.', '') . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_poya_ot_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_poya_ot_amount, 2, '.', '') . '</td>';
        
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_satu_sal_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_satu_sal_amount, 2, '.', '') . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_satu_ot_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_satu_ot_amount, 2, '.', '') . '</td>';
              
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($overoll_salary_mins) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $overoll_salary_amount, 2, '.', '') . '</td>';
        
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($overoll_ot_mins) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $overall_ot_amount, 2, '.', '') . '</td>';
        $html = $html . '</tr>';

        $html = $html . '</table>';


        $pdf->writeHTML($html, true, false, true, false, '');
        unset($_SESSION['header_data']);
        $output = $pdf->Output('', 'S');

        if (isset($output)) {
            return $output;
        }

        return FALSE;
    }

    //edit Thilini 2018-20-23 for aqua fresh
    function EmployeeOverTimeSheet($data1, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $total_worked_hours = new DateTime('00:00');
        $total_work_hr = 0;

        $total_worked_hours_add = clone $total_worked_hours;

        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
            }
            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }

        $_SESSION['header_data'] = array(
            'image_path' => $current_company->getLogoFileName(),
            'company_name' => $company_name,
            'address1' => $addrss1,
            'address2' => $address2,
            'city' => $city,
            'province' => $current_company->getProvince(),
            'postal_code' => $postalcode,
            'heading' => 'Employee OT Report - ' . date('Y F', $pay_period_end),
            'group_list' => $gr_strng,
            'department_list' => $dep_strng,
            'branch_list' => $br_strng,
            'payperiod_end_date' => date('Y-M', $pay_period_end),
            'line_width' => 185,);

        $pdf = TTnew('TimeReportHeaderFooter');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, 23);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // add a page
        $pdf->AddPage('p', 'mm', 'A4');

        //Table border
        $pdf->setLineWidth(0.15);

        //set table position
        $adjust_x = 12;

        $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(30, $adjust_y));

        //TABLE CODE HERE

        $pdf->SetFont('', 'B', 7);
        $html = '';
        $j = 0;

        foreach ($data1 as $key => $row) {
            $employee_number[$key] = $row['employee_number'];
        }

        array_multisort($employee_number, SORT_ASC, $data1);

        $page_last = 0;
        foreach ($data1 as $data) {


            $data['tot_data'] = $data['data'][count($data['data']) - 1];
            array_pop($data['data']); //delete tot of data array 
            $pplf = TTnew('PayPeriodListFactory');
            if (isset($filter_data['pay_period_ids'][0])) {
                $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
            } else {
                $pay_period_start = $filter_data['start_date'];
                $pay_period_end = $filter_data['end_date'];
            }

            $dates = array();
            $current = $pay_period_start;
            $last = $pay_period_end;
            $j = 0;
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date_get'] = date('Y-m-d', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }

            $present_days = 0;
            $absent_days = 0;
            $week_off = 0;
            $holidays = 0;
            $row_data_day_key = array();
            foreach ($data['data'] as $row1) {
                if ($row1['date_stamp'] != '') {
                    $row_dt = str_replace('/', '-', $row1['date_stamp']);

                    $dat_day = date('d', strtotime($row_dt));
                    $row_data_day_key[$dat_day] = $row1;
                }
            }


            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";


            if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
                $rows = $data;

                if ($ignore_last_row === TRUE) {
                    $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }



                $html = $html . '<table width="100%">';
                $html = $html . '<tr align="right" valign="top">';
                $html = $html . '<td><strong>EPF No :</strong> ' . $rows['employee_number'] . ' <br /> <strong>Name : </strong>' . $rows['full_name'] . '<br /> <strong>Department : </strong>' . $rows['default_department'] . '<br /> </td>';
                $html = $html . '</tr>';

                $html = $html . '</table>';
                //Header
                // create some HTML content
                $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="105%">
                        <tr style="background-color:#CCCCCC;text-align:center; padding:5px;" >';
//                $html = $html.'<td width = "3%">#</td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Date</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="12%"><table><tr><td></td></tr><tr><td>Day</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="11%"><table><tr><td></td></tr><tr><td> In</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="11%"><table><tr><td></td></tr><tr><td> Out</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="11%"><table><tr><td></td></tr><tr><td>Worked Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="11%"><table><tr><td></td></tr><tr><td>Weekday OT</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="11%"><table><tr><td></td></tr><tr><td>Holiday OT</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="11%"><table><tr><td></td></tr><tr><td>Total OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="11%"><table><tr><td></td></tr><tr><td>Lieu Leave</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '</tr>';

                $pdf->SetFont('', '', 8);

                $totLate = $totEarly = 0;

                $nof_presence = 0;
                $sum_daily_ot = 0;
                $sum_holiday_ot = 0;

                foreach ($dates as $date) {
                    $daily_ot_wage = 0;
//                    echo '<pre><br> date::'.$date['date'];

                    $holiday_ot_wage = 0;
                    $policy_array = Array();
                    $searchword = 'over_time_policy-';
                    $matches = array();
                    foreach ($row_data_day_key[$date['day']] as $k => $v) {
                        if (preg_match("/\b$searchword\b/i", $k)) {
                            $matches[] = $k;
                        }
                    }
                    $otplf = TTnew('OverTimePolicyListFactory');
                    for ($z = 0; $z < count($matches); $z++) {
                        $policy_array_temp = explode("-", $matches[$z]);
                        $policy_array[$z]['overtime_policy_id'] = $policy_array_temp[1];
                        $otplf_obj_holiday = $otplf->getById($policy_array_temp[1]);
                        if ($otplf_obj_holiday->getRecordCount() > 0) {
                            foreach ($otplf_obj_holiday as $ot_policy_data) {
                                $trigger_time = $ot_policy_data->getTriggerTime();
                                $overtime_rate = $ot_policy_data->getRate();
                            }
                        }
                        $policy_array[$z]['trigger_time'] = $trigger_time;
                        $policy_array[$z]['overtime_rate'] = $overtime_rate;
                    }
                    usort($policy_array, function($a, $b) {
                        return $a['overtime_policy_id'] - $b['overtime_policy_id'];
                    });
                    $datetime1 = new DateTime($row_data_day_key[$date['day']]['min_punch_time_stamp']);
                    $datetime2 = new DateTime($row_data_day_key[$date['day']]['max_punch_time_stamp']);
                    $interval = $datetime1->diff($datetime2);
                    $total_work_hr = $total_work_hr + $interval;
                    $date_int = $interval->format("%H:%I");

                    if ($row_data_day_key[$date['day']]['min_punch_time_stamp'] == '' || $row_data_day_key[$date['day']]['max_punch_time_stamp'] == '') {
                        $date_int = '';
                    }

                    $day_2 = explode(' ', $date['date']);

                    if ($day_2[1] == 'Sun' || $day_2[1] == 'Sat') {
                        $EmpDateStatus['status1'] = 'WO';
                    }

                    $date_temp = explode('/', $day_2[0]);

                    $hlf = TTnew('HolidayListFactory');

                    $date_holidays = DateTime::createFromFormat('d/m/Y', $day_2[0])->format('Y-m-d');

                    $hlf->getByPolicyGroupUserIdAndDate($rows['user_id'], $date_holidays);
                    $hlf_obj = $hlf->getCurrent();
                    if (!empty($hlf_obj->data)) {
//                        echo '<br>holiday';
                        $daily_ot = "";
                        $holiday_ot = $row_data_day_key[$date['day']]['over_time'];
                    } else {
                        if($day_2[1] == 'Sun' || $day_2[1] == 'Sat'){
//                            echo '<br>sun or sat';
                            $daily_ot = "";
                            $holiday_ot = $row_data_day_key[$date['day']]['over_time'];
                        } else {
//                            echo '<br>daily';
                            $daily_ot = $row_data_day_key[$date['day']]['over_time'];
                            $holiday_ot = "";
                        }
                        
                    }
                    
//                    echo '<br>daily ot::'.$daily_ot;
//                    echo '<br>holiday ot::'.$holiday_ot;
//                    
                    //user's hourly wage 
                    $hourly_wage = $row_data_day_key[$date['day']]['hourly_wage'];

//                    echo '<br>hourly_wage::'.$hourly_wage;
                    
//                    print_r($policy_array);
                    
                    if (isset($row_data_day_key[$date['day']]['over_time_policy_id'])) {

                        $otplf_obj = $otplf->getById($row_data_day_key[$date['day']]['over_time_policy_id']);
                        if ($otplf_obj->getRecordCount() > 0) {
                            foreach ($otplf_obj as $ot_policy_data) {
                                $overtime_rate = $ot_policy_data->getRate();
                            }
                        }
                    }
                    if ($date['date_get'] == $hlf_obj->data['date_stamp'] || $day_2[1] == 'Sun' || $day_2[1] == 'Sat') {
//                        echo '<br> holiday ::'.$date['day'];
                        //if a holiday
                        $holiday_ot_temp = explode(":", $holiday_ot);
                        $holiday_ot_mins = (intval($holiday_ot_temp[0]) * 60) + intval($holiday_ot_temp[1]);

                        
                        if (count($policy_array) > 1) {
                            for ($y = 0; $y < count($policy_array); $y++) {

                                if ($y != count($policy_array) - 1) {
                                    $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                    $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                } else {
                                    $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                }

                                if ($holiday_ot_mins < $trigger_time) {
                                    $holiday_ot_wage = $holiday_ot_mins * (($hourly_wage * $overtime_rate2) / 60 );
                                    break;
                                } else {
                                    $holiday_ot_wage = ($trigger_time * (($hourly_wage * $overtime_rate2) / 60 )) + (($holiday_ot_mins - $trigger_time) * (($hourly_wage * $overtime_rate3) / 60));
                                    break;
                                }
                            }
//                            echo '<br> holiday ot wage  ::'.$holiday_ot_wage;
                        } else if (count($policy_array) == 1) {
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $holiday_ot_wage = $holiday_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
//                            echo '<br> holiday ot wage  ::'.$holiday_ot_wage;
                        }
                    } else {
                        //normal working day
                        $daily_ot_temp = explode(":", $daily_ot);
                        $daily_ot_mins = (intval($daily_ot_temp[0]) * 60) + intval($daily_ot_temp[1]);
                        $daily_ot_wage = $daily_ot_mins * (($hourly_wage * $overtime_rate) / 60);

//                        echo '<br> daily ot wage  ::'.$daily_ot_wage;
                    }

                    $html = $html . '<tr align="center"  style="padding-top:25px;text-align:center;" valign="top">';
                    $html = $html . '<td  height="17" style="padding-top:25px;font-size:32px;text-align:center;" align="center"> ' . $date_temp[0] . '</td>';
                    $html = $html . '<td  height="17" style="padding-top:25px;font-size:32px;text-align:center;" align="center"> ' . $day_2[1] . '</td>';
                    $html = $html . '<td  height="17"  style="font-size:30px;text-align:center;" >' . $row_data_day_key[$date['day']]['min_punch_time_stamp'] . '</td>';
                    $html = $html . '<td  height="17" style="font-size:30px;text-align:center;">' . $row_data_day_key[$date['day']]['max_punch_time_stamp'] . '</td>';
                    $html = $html . '<td  height="17" style="font-size:30px;text-align:center;">' . $date_int . '</td>';
                    $html = $html . '<td  height="17" style="font-size:30px;text-align:center;">' . $daily_ot . '</td>';
                    $html = $html . '<td  height="17" style="font-size:30px;text-align:center;">' . $holiday_ot . '</td>';
                    $html = $html . '<td  height="17" style="font-size:30px;text-align:center;">' . $row_data_day_key[$date['day']]['over_time'] . '</td>';
                    $html = $html . '<td  height="17" style="font-size:30px;text-align:center;"></td>';
                    $html = $html . '</tr>';

//                    echo '<br> here daily ot::'.$daily_ot_wage;
                    $sum_daily_ot += $daily_ot_wage;
                    $sum_holiday_ot += $holiday_ot_wage;

//                    echo '<br> sum_daily_ot  ::'.$sum_daily_ot;
//                    echo '<br> sum_holiday_ot  ::'.$sum_holiday_ot;
                    $total_cost = ($sum_daily_ot + $sum_holiday_ot);
//                    echo '<br> total_cost  ::'.$total_cost;
                }
//                die;
                $html = $html . '<tr>';
                $html = $html . '<td colspan="9">';

                $html = $html . '<table border="1">';

                $otplf = TTnew('OverTimePolicyListFactory');

                $html = $html . '<table border="1">';

                $html = $html . '<tr>';
                $html = $html . '<td  width="23.7%" height="20" style="font-size:32px; vertical-align:middle;" colspan = "2">Total OT Hours: </td>';
                $html = $html . '<td width="11%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="11%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="10.7%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="11%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="10.7%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="11%" colspan="8" style="font-size:32px; vertical-align:middle;text-align:center;">' . $rows['tot_data']['over_time'] . '</td>';
                $html = $html . '<td width="11%" bgcolor="#CCCCCC"></td>';
                $html = $html . '</tr>';

                $html = $html . '<tr>';
                $html = $html . '<td  width="23.7%" height="20" style="font-size:32px; vertical-align:middle;" colspan = "2">Total Cost: </td>';
                $html = $html . '<td width="11%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="11%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="10.7%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="11%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="10.7%" bgcolor="#CCCCCC"></td>';
                $html = $html . '<td width="11%" colspan="8" style="font-size:32px; vertical-align:middle;text-align:center;">' . number_format((float) $total_cost, 2, '.', '') . '</td>';
                $html = $html . '<td width="11%" bgcolor="#CCCCCC"></td>';
                $html = $html . '</tr>';

                $html = $html . '</table>';



                $html = $html . '</table>';

                $html = $html . '</td>';
                $html = $html . '</tr>';
                $html = $html . '</table>';

                $html = $html . '<table>';
                $html = $html . '<tr><td colspan="4"></td></tr>';
                $html = $html . '<tr><td colspan="4"></td></tr>';
                $html = $html . '<tr><td colspan="4"></td></tr>';

                $html = $html . '<tr align="center">';
                $html = $html . "<td >Employee's Signature </td>";
                $html = $html . '<td height="20">.........................</td>';
                $html = $html . '<td height="20"></td>';

                $html = $html . '</tr>';

                $html = $html . '<table>';
                $html = $html . '<tr><td colspan="4"></td></tr>';

                $html = $html . '<tr align="center">';
                $html = $html . '<td height="50" width="33%">Checked by </td>';
                $html = $html . '<td height="50" width="33%">Recommended by </td>';
                $html = $html . '<td height="50" width="33%">Approved by</td>';

                $html = $html . '</tr>';

                $html = $html . '<tr align="center" >';
                $html = $html . '<td >.............................................. </td>';
                $html = $html . '<td >.............................................. </td>';
                $html = $html . '<td >.............................................. </td>';

                $html = $html . '</tr>';
                $html = $html . '</tr>';
                $html = $html . '<tr align="center">';
                $html = $html . '<td> </td>';
                $html = $html . '<td>Immediate Superior </td>';
                $html = $html . '<td>Head of the Department </td>';

                $html = $html . '</tr>';

                $html = $html . '</table>';

                if (count($data1) > ($page_last + 1)) {
                    $html = $html . '<br pagebreak="true" />';
                }
                $page_last++;
                $j++;
            }
        }
//        die;
        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        unset($_SESSION['header_data']);

        $output = $pdf->Output('', 'S');


        if (isset($output)) {
            return $output;
        }

        return FALSE;
    }
    
    
        //Added by Thilini 2018-20-26 for aqua fresh
    function OverTimeSummarySheetExcelExport($data1, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
        $total_worked_hours = new DateTime('00:00');
        $total_work_hr = 0;

        $total_worked_hours_add = clone $total_worked_hours;

        $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );
        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
            }
            $br_strng = $blf->getNameById($br_id);

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }
        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }


        $_SESSION['header_data'] = array(
            'image_path' => $current_company->getLogoFileName(),
            'company_name' => $company_name,
            'address1' => $addrss1,
            'address2' => $address2,
            'city' => $city,
            'province' => $current_company->getProvince(),
            'postal_code' => $postalcode,
            'heading' => 'OT Summary Report - ' . date('Y F', $pay_period_end),
            'group_list' => $gr_strng,
            'department_list' => $dep_strng,
            'branch_list' => $br_strng,
            'payperiod_end_date' => date('Y-M', $pay_period_end),
            'line_width' => 275,);
        
        $fileName = 'OT Summary Report - ' . date('Y F', $pay_period_end);
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Me")->setLastModifiedBy("Me")->setTitle("My Excel Sheet")->setSubject("My Excel Sheet")->setDescription("Excel Sheet")->setKeywords("Excel Sheet")->setCategory("Me");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

       
        $j = 0;

        foreach ($data1 as $key => $row) {
            $employee_number[$key] = $row['employee_number'];
        }

        array_multisort($employee_number, SORT_ASC, $data1);
        
        
        // Add column headers
        $objPHPExcel->getActiveSheet()
			->setCellValue('A1', 'Emp No')
			->setCellValue('B1', 'Name')
			->setCellValue('C1', 'Department')
			->setCellValue('D1', 'Designation')
			->setCellValue('E1', 'Total Worked Hrs')
                
                	->setCellValue('F1', 'Weekday OT')
                        ->setCellValue('G1', 'Weekday Amount (Rs.)')
                        ->setCellValue('H1', 'Sunday Salary Hrs')
                        ->setCellValue('I1', 'Sunday Salary (Rs.)')
                        ->setCellValue('J1', 'Sunday OT Hrs')
                
                        ->setCellValue('K1', 'Sunday OT(Rs.)')
                        ->setCellValue('L1', 'Saturday OT Hrs')
                        ->setCellValue('M1', 'Saturday OT (Rs.)')
                        ->setCellValue('N1', 'Poya Salary Hrs')
                        ->setCellValue('O1', 'Poya Salary (Rs.)')
                
                        ->setCellValue('P1', 'Poya OT Hrs')
                        ->setCellValue('Q1', 'Poya OT (Rs.)')
                        ->setCellValue('R1', 'Statutory Salary Hrs')
                        ->setCellValue('S1', 'Statutory Salary (Rs.)')
                        ->setCellValue('T1', 'Statutory OT Hrs')
                
                        ->setCellValue('U1', 'Statutory OT (Rs.)')
                        ->setCellValue('V1', 'Total Salary Hrs')
                        ->setCellValue('W1', 'Total Salary Amount (Rs.)')
                        ->setCellValue('X1', 'Total OT Hrs')
                        ->setCellValue('Y1', 'Total OT Amount (Rs.)')
			;

        $page_last = 0;

        
        $html = $html . '</tr></table>';

        $count = 0;
        $data_no = 1;
        $page_no = 1;
        $max_data_rows = 9;
        $last_page_data_no = 0;
        foreach ($data1 as $data) {
            $data['tot_data'] = $data['data'][count($data['data']) - 1];
            array_pop($data['data']);
            $daily_ot_wage = 0;
            $dates = array();
            $current = $pay_period_start;
            $last = $pay_period_end;
            $j = 0;
            while ($current <= $last) {

                $dates[$j]['day'] = date('d', $current);
                $dates[$j]['date'] = date('d/m/Y D', $current);
                $dates[$j]['date_get'] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
                $j++;
            }
            $row_data_day_key = array();
            foreach ($data['data'] as $row1) {
                if ($row1['date_stamp'] != '') {
                    $row_dt = str_replace('/', '-', $row1['date_stamp']);

                    $dat_day = date('d', strtotime($row_dt));
                    $row_data_day_key[$dat_day] = $row1;
                }
            }
            
            $total_sun_ot_mins = 0;
            $total_sat_ot_mins = 0;
            $total_satu_ot_mins = 0;
            $total_poya_ot_mins = 0;
            $total_daily_ot_mins = 0;
            
            $sum_daily_ot_wage = 0;
            $sum_sun_ot_wage = 0;
            $sum_sat_ot_wage = 0;
            $sum_satu_ot_wage = 0;
            $sum_poya_ot_wage = 0;
            
            $total_poya_salary_mins = 0;
            $total_sun_salary_mins = 0;  
            $total_sat_salary_mins = 0;
            $total_satu_salary_mins = 0;
            
            $sum_poya_salary_wage = 0;
            $sum_sun_salary_wage = 0;
            $sum_sat_salary_wage = 0;
            $sum_satu_salary_wage = 0;        
           
            $actual_hrs = 0;
            $actual_mins = 0;
                    
            foreach ($dates as $date) {
//                echo '<pre><br> Date::' . $date['day'];
//                print_r($data);
                $poya_ot_wage = 0;
                $poya_salary = 0;
                $sat_ot_wage = 0;
                $sat_salary = 0;
                $sun_ot_wage = 0;
                $sun_salary = 0;
                $satu_ot_wage = 0;
                $satu_salary = 0;
                
                $poya_ot_display_min = 0;
                $sun_ot_display_min = 0; 
                $sat_ot_display_min = 0;
                $satu_ot_display_min = 0;
                
                $poya_salary_display_min = 0;
                $sun_salary_display_min = 0;   
                $sat_salary_display_min = 0;      
                $satu_salary_display_min =  0;
                        
                $policy_array = Array();
                $searchword = 'over_time_policy-';
                $matches = array();
                foreach ($row_data_day_key[$date['day']] as $k => $v) {
                    if (preg_match("/\b$searchword\b/i", $k)) {
                        $matches[] = $k;
                    }
                }
                $otplf = TTnew('OverTimePolicyListFactory');
                for ($z = 0; $z < count($matches); $z++) {
                    $policy_array_temp = explode("-", $matches[$z]);
                    $policy_array[$z]['overtime_policy_id'] = $policy_array_temp[1];
                    $otplf_obj_holiday = $otplf->getById($policy_array_temp[1]);
                    if ($otplf_obj_holiday->getRecordCount() > 0) {
                        foreach ($otplf_obj_holiday as $ot_policy_data) {
                            $trigger_time = $ot_policy_data->getTriggerTime();
                            $overtime_rate = $ot_policy_data->getRate();
                            $max_time = $ot_policy_data->getMaxTime();
                        }
                    }
                    $policy_array[$z]['trigger_time'] = $trigger_time;
                    $policy_array[$z]['overtime_rate'] = $overtime_rate;
                    $policy_array[$z]['max_time'] = $max_time;
                }
                usort($policy_array, function($a, $b) {
                    return $a['overtime_policy_id'] - $b['overtime_policy_id'];
                });
                
                $dateStamp = '';
                if ($row_data_day_key[$date['day']]['date_stamp'] != '') {
                    $dateStamp = DateTime::createFromFormat('d/m/Y', $row_data_day_key[$date['day']]['date_stamp'])->format('Y-m-d');
                }

                $EmpDateStatus = $this->getReportStatusByUserIdAndDate($data['user_id'], $dateStamp);

                $datetime1 = new DateTime($row_data_day_key[$date['day']]['min_punch_time_stamp']);
                $datetime2 = new DateTime($row_data_day_key[$date['day']]['max_punch_time_stamp']);
                $interval = $datetime1->diff($datetime2);
                $total_work_hr = $total_work_hr + $interval;
                $date_int = $interval->format("%H:%I");
                if ($row_data_day_key[$date['day']]['min_punch_time_stamp'] == '' || $row_data_day_key[$date['day']]['max_punch_time_stamp'] == '') {
                    $date_int = 0;
                }
                if ($date_int == 0) {
                    $actual_hrs += 0;
                    $actual_mins += 0;
                } else {
                    $actual_time_temp = explode(":", $date_int);
                    $actual_hrs += intval($actual_time_temp[0]);
                    $actual_mins += intval($actual_time_temp[1]);
                }

                $day_2 = explode(' ', $date['date']);
                
                //user's hourly wage 
                $hourly_wage = $row_data_day_key[$date['day']]['hourly_wage'];
                
                if (isset($row_data_day_key[$date['day']]['over_time_policy_id'])) {
                    $otplf_obj = $otplf->getById($row_data_day_key[$date['day']]['over_time_policy_id']);
                    if ($otplf_obj->getRecordCount() > 0) {
                        foreach ($otplf_obj as $ot_policy_data) {
                            $overtime_rate = $ot_policy_data->getRate();
                        }
                    }
                }

                $hlf = TTnew('HolidayListFactory');

                $date_holidays = DateTime::createFromFormat('d/m/Y', $day_2[0])->format('Y-m-d');

                $hlf->getByPolicyGroupUserIdAndDate($data['user_id'], $date_holidays);
                $hlf_obj = $hlf->getCurrent();
                $holiday_policy_array = $hlf_obj->data;
                if (!empty($holiday_policy_array)) {
                    if($holiday_policy_array['holiday_policy_id'] == '1'){
                        //poya holiday
                        $daily_ot = 0;
                        $statutory_ot = 0;
                        $sunday_ot = 0 ;
                        $saturday_ot = 0;
                        $poya_ot = $row_data_day_key[$date['day']]['over_time'];
                        $poya_ot_temp = explode(":", $poya_ot);
                        $poya_ot_mins = (intval($poya_ot_temp[0]) * 60) + intval($poya_ot_temp[1]);
//                        echo '<br>poya min::'.$poya_ot_mins;
//                        print_r($policy_array);
                        if(count($policy_array) > 1){
                            for ($y = 0; $y < count($policy_array); $y++) {
                                if ($y != count($policy_array) - 1) {
                                    $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                    $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                } else {
                                    
                                    $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                }
                                
                                if ($poya_ot_mins < $trigger_time) {
                                    $poya_salary_display_min = $poya_ot_mins;
                                    $poya_salary = $poya_ot_mins * (($hourly_wage * $overtime_rate2) / 60 );
                                    break;
                                } else {
                                    $poya_salary_display_min = $trigger_time;
                                    $poya_ot_display_min = $poya_ot_mins - $trigger_time;
                                    $poya_salary = ($trigger_time * (($hourly_wage * $overtime_rate2) / 60 ));
                                    $poya_ot_wage = ($poya_ot_display_min * (($hourly_wage * $overtime_rate3) / 60));
                                    break;
                                }
                                
                            }                       
                        } else if(count($policy_array) == 1) {
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $poya_salary_display_min = $poya_ot_mins;
                            $poya_salary = $poya_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
                        }
//                        echo '<br>p_salary_mins::'.$poya_salary_display_min;
//                        echo '<br>p_ot_mins::'.$poya_ot_display_min;
//                        echo '<br>p_ot_wage::'.$poya_ot_wage;
//                        echo '<br>p_salary_wage::'.$poya_salary;
                        
                        $total_poya_salary_mins += $poya_salary_display_min;
                        $total_poya_ot_mins += $poya_ot_display_min;
                        $sum_poya_ot_wage += $poya_ot_wage;
                        $sum_poya_salary_wage += $poya_salary;
                        
//                        echo '<br>s_salary_mins::'.$total_poya_salary_mins;
//                        echo '<br>s_ot_mins::'.$total_poya_ot_mins;
//                        echo '<br>s_ot_wage::'.$sum_poya_ot_wage;
//                        echo '<br>s_salary_wage::'.$sum_poya_salary_wage;
                        
                    } else if ($holiday_policy_array['holiday_policy_id'] == '2'){
                        //satutory holiday
                        $daily_ot = 0;
                        $saturday_ot = 0;
                        $sunday_ot = 0 ;
                        $poya_ot = 0;
                        $statutory_ot = $row_data_day_key[$date['day']]['over_time'];
                        $statutory_ot_temp = explode(":", $statutory_ot);
                        $statutory_ot_mins = (intval($statutory_ot_temp[0]) * 60) + intval($statutory_ot_temp[1]);
//                        echo '<br>statutory min::'.$statutory_ot_mins;
                         if(count($policy_array) > 1){
                            for ($y = 0; $y < count($policy_array); $y++) {
                                if ($y != count($policy_array) - 1) {
                                    $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                    $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                } else {
                                    $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                }
                                
                                if ($statutory_ot_mins < $trigger_time) {
                                    $satu_salary_display_min = $statutory_ot_mins;
                                    $satu_salary = $statutory_ot_mins * (($hourly_wage * $overtime_rate2) / 60 );
                                    break;
                                } else {
                                    $satu_salary_display_min = $trigger_time;
                                    $satu_ot_display_min = $statutory_ot_mins - $trigger_time;
                                    $satu_salary = ($trigger_time * (($hourly_wage * $overtime_rate2) / 60 ));
                                    $satu_ot_wage = ($satu_ot_display_min * (($hourly_wage * $overtime_rate3) / 60));
                                    break;
                                }
                                
                            }                       
                        } else if(count($policy_array) == 1) {
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $satu_salary_display_min = $statutory_ot_mins;
                            $satu_salary = $statutory_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
                        }
                        $total_satu_salary_mins += $satu_salary_display_min;
                        $total_satu_ot_mins += $satu_ot_display_min;
                        $sum_satu_ot_wage += $satu_ot_wage;
                        $sum_satu_salary_wage += $satu_salary;
                    }
                } else {
                    if($day_2[1] == 'Sun'){
                        //sunday holiday 
                        
                        $daily_ot = 0;
                        $saturday_ot = 0;
                        $statutory_ot = 0 ;
                        $poya_ot = 0;
                        $sunday_ot = $row_data_day_key[$date['day']]['over_time'];
                        $sunday_ot_temp = explode(":", $sunday_ot);
                        $sunday_ot_mins = (intval($sunday_ot_temp[0]) * 60) + intval($sunday_ot_temp[1]);
//                        echo '<br>sunday min::'.$sunday_ot_mins;
                        if(count($policy_array) > 1){
                            for ($y = 0; $y < count($policy_array); $y++) {
                                if ($y != count($policy_array) - 1) {
                                    $trigger_time = $policy_array[$y + 1]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                    $overtime_rate3 = $policy_array[$y + 1]['overtime_rate'];
                                } else {
                                    $trigger_time = $policy_array[$y]['trigger_time'] / 60;
                                    $overtime_rate2 = $policy_array[$y]['overtime_rate'];
                                }
                                
                                if ($sunday_ot_mins < $trigger_time) {
                                    $sun_salary_display_min = $sunday_ot_mins;
                                    $sun_salary = $sunday_ot_mins * (($hourly_wage * $overtime_rate2) / 60 );
                                    break;
                                } else {
                                    $sun_salary_display_min = $trigger_time;
                                    $sun_ot_display_min = $sunday_ot_mins - $trigger_time;
                                    $sun_salary = ($trigger_time * (($hourly_wage * $overtime_rate2) / 60 ));
                                    $sun_ot_wage = ($sun_ot_display_min * (($hourly_wage * $overtime_rate3) / 60));
                                    break;
                                }
                                
                            }                       
                        } else if(count($policy_array) == 1) {
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $sun_salary_display_min = $sunday_ot_mins;
                            $sun_salary = $sun_salary_display_min * (($hourly_wage * $overtime_rate) / 60 );
                        }
                        
//                        echo '<br>sun_salary_mins::'.$sun_salary_display_min;
//                        echo '<br>sun_ot_mins::'.$sun_ot_display_min;
//                        echo '<br>sun_ot_wage::'.$sun_ot_wage;
//                        echo '<br>sun_salary_wage::'.$sun_salary;
                        
                        $total_sun_salary_mins += $sun_salary_display_min;
                        $total_sun_ot_mins += $sun_ot_display_min;
                        $sum_sun_ot_wage += $sun_ot_wage;
                        $sum_sun_salary_wage += $sun_salary;
                        
//                        echo '<br>total_sun_salary_mins::'.$total_sun_salary_mins;
//                        echo '<br>total_sun_ot_mins::'.$total_sun_ot_mins;
//                        echo '<br>sum_sun_ot_wage::'.$sum_sun_ot_wage;
//                        echo '<br>sum_sun_salary_wage::'.$sum_sun_salary_wage;
                        
                    } else if($day_2[1] == 'Sat') {
                        //saturday ot
                        $daily_ot = 0;
                        $sunday_ot = 0;
                        $statutory_ot = 0 ;
                        $poya_ot = 0;
                        $saturday_ot = $row_data_day_key[$date['day']]['over_time'];
                        $saturday_ot_temp = explode(":", $saturday_ot);
                        $saturday_ot_mins = (intval($saturday_ot_temp[0]) * 60) + intval($saturday_ot_temp[1]);
//                         echo '<br>saturday min::'.$saturday_ot_mins;
                        
                            $overtime_rate = $policy_array[0]['overtime_rate'];
                            $sat_ot_display_min = $saturday_ot_mins;
                            $sat_ot_wage = $saturday_ot_mins * (($hourly_wage * $overtime_rate) / 60 );
                        
                        $total_sat_ot_mins += $sat_ot_display_min;
                        $sum_sat_ot_wage += $sat_ot_wage;
                    } else {
                        //daily ot
                        $sunday_ot = 0;
                        $statutory_ot = 0 ;
                        $poya_ot = 0;
                        $saturday_ot = 0;
                        $daily_ot = $row_data_day_key[$date['day']]['over_time'];
                        
                        $daily_ot_temp = explode(":", $daily_ot);
                        $daily_ot_mins = (intval($daily_ot_temp[0]) * 60) + intval($daily_ot_temp[1]);
//                         echo '<br>daily min::'.$daily_ot_mins;
                        $daily_ot_wage = $daily_ot_mins * (($hourly_wage * $overtime_rate) / 60);
                        $total_daily_ot_mins += $daily_ot_mins;
                        $sum_daily_ot_wage += $daily_ot_wage;
                    }
                    
                }
            }
//            die;
           //total worked hrs
            $additional_hrs = intval($actual_mins / 60);
            $additional_mins = $actual_mins % 60;
            $total_hours = $actual_hrs + $additional_hrs;
            if(strlen($total_hours) < 2){
                $total_hours = "0" . $total_hours;
            }
            if(strlen($additional_mins) < 2){
                $additional_mins = "0".$additional_mins;
            }
            $total_worked_time = $total_hours . ":" . $additional_mins;
            
            if(isset($data['tot_data']['worked_time']) ){
                $final_worked_time = $data['tot_data']['worked_time'];
            }  else {
                 $final_worked_time = '00:00';
            }

            $total_salary_hrs = $total_sun_salary_mins + $total_poya_salary_mins + $total_satu_salary_mins;
            $total_salary_wage = $sum_sun_salary_wage + $sum_poya_salary_wage +$sum_satu_salary_wage;
                    
            $total_ot_hours = $total_daily_ot_mins + $total_sun_ot_mins  + $total_sat_ot_mins  + $total_poya_ot_mins  + $total_satu_ot_mins;
            $sum_all_ot_wage = $sum_daily_ot_wage +  $sum_sun_ot_wage  + $sum_sat_ot_wage  + $sum_poya_ot_wage  + $sum_satu_ot_wage;
            
            $ii = $count +2;
            	$objPHPExcel->getActiveSheet()->setCellValue('A'.$ii, $data['employee_number']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$ii, $data['full_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$ii, $data['default_department']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$ii, $data['title']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$ii, $final_worked_time );
                
                
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$ii, $this->convertMinutesToHourFormat($total_daily_ot_mins));
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$ii, number_format((float) $sum_daily_ot_wage, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$ii, $this->convertMinutesToHourFormat($total_sun_salary_mins));
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$ii, number_format((float) $sum_sun_salary_wage, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$ii, $this->convertMinutesToHourFormat($total_sun_ot_mins) );
                
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$ii, number_format((float) $sum_sun_ot_wage, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$ii, $this->convertMinutesToHourFormat($total_sat_ot_mins));
                $objPHPExcel->getActiveSheet()->setCellValue('M'.$ii, number_format((float) $sum_sat_ot_wage, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$ii, $this->convertMinutesToHourFormat($total_poya_salary_mins));
                $objPHPExcel->getActiveSheet()->setCellValue('O'.$ii, number_format((float) $sum_poya_salary_wage, 2, '.', '') );
                
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$ii, $this->convertMinutesToHourFormat($total_poya_ot_mins));
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.$ii, number_format((float) $sum_poya_ot_wage, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('R'.$ii, $this->convertMinutesToHourFormat($total_satu_salary_mins));
                $objPHPExcel->getActiveSheet()->setCellValue('S'.$ii, number_format((float) $sum_satu_salary_wage, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('T'.$ii, $this->convertMinutesToHourFormat($total_satu_ot_mins) );
                
                $objPHPExcel->getActiveSheet()->setCellValue('U'.$ii, number_format((float) $sum_satu_ot_wage, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('V'.$ii, $this->convertMinutesToHourFormat($total_salary_hrs));
                $objPHPExcel->getActiveSheet()->setCellValue('W'.$ii, number_format((float) $total_salary_wage, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('X'.$ii, $this->convertMinutesToHourFormat($total_ot_hours));
                $objPHPExcel->getActiveSheet()->setCellValue('Y'.$ii, number_format((float) $sum_all_ot_wage, 2, '.', '') );



            $dept_sum_daily_ot += $total_daily_ot_mins;
            $dept_sum_weekday_amount += $sum_daily_ot_wage;
            
            $dept_sun_sal_hrs += $total_sun_salary_mins;
            $dept_sun_sal_amount += $sum_sun_salary_wage;
            $dept_sun_ot_hrs += $total_sun_ot_mins;
            $dept_sun_ot_amount += $sum_sun_ot_wage;
            
            $dept_sat_ot_hrs += $total_sat_ot_mins;
            $dept_sat_ot_amount += $sum_sat_ot_wage;
            
            $dept_poya_sal_hrs += $total_poya_salary_mins;
            $dept_poya_sal_amount += $sum_poya_salary_wage;
            $dept_poya_ot_hrs += $total_poya_ot_mins;
            $dept_poya_ot_amount += $sum_poya_ot_wage;
            
            $dept_satu_sal_hrs += $total_satu_salary_mins;
            $dept_satu_sal_amount += $sum_satu_salary_wage;
            $dept_satu_ot_hrs += $total_satu_ot_mins;
            $dept_satu_ot_amount += $sum_satu_ot_wage;

//            echo '<pre>count data::'.count($data1);
//            echo '<pre>page no::'.$page_no;
//            echo '<pre>max_data_rows::'.$max_data_rows;
//            echo '<pre>calculation::'.(count($data1)/$max_data_rows);
//            echo '<br>round::'.ceil((count($data1)/$max_data_rows));
//            echo '<pre>Data No::'.$data_no;
//            echo '<pre>exceed max for page ::'.($page_no*$max_data_rows);
//            echo '<br>-----------------------------------';
            if(ceil(count($data1)/$max_data_rows) == $page_no){ // if the last page
//                $page_no++;
//                $html = $html . '<label> Last Page </label>';
                $last_page_data_no++;
//                echo '<pre>in last page ::';
            } else if($data_no == ($page_no*$max_data_rows)) {
                $html = $html . '<br pagebreak="true" />';
                $html = $html . '<br/><br/>';
                $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="110%"><tr style="background-color:#CCCCCC;text-align:center; padding:1px;" >';
                $html = $html . '<td width="2.5%"><table><tr><td></td></tr><tr><td>Emp No</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="8%"><table><tr><td></td></tr><tr><td>Name</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="7%"><table><tr><td></td></tr><tr><td> Department</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="7%"><table><tr><td></td></tr><tr><td> Designation</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Worked Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Weekday OT</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Weekday Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Sunday Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Sunday Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Sunday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Sunday OT(Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Saturday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Saturday OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Poya Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Poya Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Poya OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Poya OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.2%"><table><tr><td></td></tr><tr><td>Statutory OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total Salary Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total OT Hrs</td></tr><tr><td></td></tr></table> </td>';
                $html = $html . '<td width="3.5%"><table><tr><td></td></tr><tr><td>Total OT Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';

                $html = $html . '</tr></table>';
                $page_no++;
            } 
           $data_no++;
           $count++; 

        } // end of data row
      
        $overoll_salary_mins = $dept_sun_sal_hrs + $dept_poya_sal_hrs + $dept_satu_sal_hrs;
        $overoll_salary_amount = $dept_sun_sal_amount + $dept_poya_sal_amount + $dept_satu_sal_amount;
        
        $overoll_ot_mins = $dept_sum_daily_ot + $dept_sun_ot_hrs  +$dept_sat_ot_hrs  +$dept_poya_ot_hrs  + $dept_satu_ot_hrs;
        $overall_ot_amount = $dept_sum_weekday_amount  + $dept_sun_ot_amount +$dept_sat_ot_amount + $dept_poya_ot_amount +$dept_satu_ot_amount;
        
        $html = $html . '</table>';
//        print_r($last_page_data_no);  
        if($last_page_data_no > 5){
//            echo 'here';
            $html = $html . '<br pagebreak="true" />';
        } 
//        die;
        $html = $html . '<br/>';
        $html = $html . '<label> Count :</label>' . $count;

        $html = $html . '<br/><br/>';

        $html = $html . '<label> Month : </label>' . date('Y F', $pay_period_end) . '<br/>';
        $html = $html . '<label> Department : </label>' . $dep_strng;
        $html = $html . '<br/><br/>';
        
        $html = $html . '<table border="1" cellspacing="0" cellpadding="0" width="105%"><tr style="background-color:#CCCCCC;text-align:center;" >';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Weekday OT</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Weekday Amount</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Sunday Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Sunday Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Sunday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Sunday OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
       
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Saturday OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Saturday OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Poya Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Poya Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Poya OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Poya OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Statutory Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="4%"><table><tr><td></td></tr><tr><td>Statutory Salary (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Statutory OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="5%"><table><tr><td></td></tr><tr><td>Statutory OT (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="6%"><table><tr><td></td></tr><tr><td>Total Salary Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="6%"><table><tr><td></td></tr><tr><td>Salary Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        
        $html = $html . '<td width="6%"><table><tr><td></td></tr><tr><td>Total OT Hrs</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '<td width="6%"><table><tr><td></td></tr><tr><td>OT Amount (Rs.)</td></tr><tr><td></td></tr></table> </td>';
        $html = $html . '</tr>';

        $html = $html . '<tr align="center" style="padding-top:25px;text-align:center;" valign="top">';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;height:25px;" align="center">' . $this->convertMinutesToHourFormat($dept_sum_daily_ot) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_sum_weekday_amount, 2, '.', '') . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_sun_sal_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_sun_sal_amount, 2, '.', '') . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_sun_ot_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_sun_ot_amount, 2, '.', '') . '</td>';
        
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_sat_ot_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_sat_ot_amount, 2, '.', '') . '</td>';
        
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_poya_sal_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_poya_sal_amount, 2, '.', '') . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_poya_ot_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_poya_ot_amount, 2, '.', '') . '</td>';
        
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_satu_sal_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_satu_sal_amount, 2, '.', '') . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($dept_satu_ot_hrs) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $dept_satu_ot_amount, 2, '.', '') . '</td>';
              
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($overoll_salary_mins) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $overoll_salary_amount, 2, '.', '') . '</td>';
        
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . $this->convertMinutesToHourFormat($overoll_ot_mins) . '</td>';
        $html = $html . '<td style="padding-top:25px;font-size:25px;text-align:center;" align="center">' . number_format((float) $overall_ot_amount, 2, '.', '') . '</td>';
        $html = $html . '</tr>';

        $html = $html . '</table>';


       // $pdf->writeHTML($html, true, false, true, false, '');
       // unset($_SESSION['header_data']);
       // $output = $pdf->Output('', 'S');
        
        $objPHPExcel->getActiveSheet()->setTitle($fileName);
        
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        
    }

  function LieuLeaveAndDutyLeaveReport($data, $columns = NULL, $filter_data = NULL, $current_user, $current_company) {
            
      //echo '<pre>';print_r($data);exit;
        
                $filter_header_data = array(
            'group_ids' => $filter_data['group_ids'],
            'branch_ids' => $filter_data['branch_ids'],
            'department_ids' => $filter_data['department_ids'],
            'pay_period_ids' => $filter_data['pay_period_ids']
        );

            
                
        foreach ($filter_header_data as $fh_key => $filter_header) {
            $dlf = TTnew('DepartmentListFactory');
            if ($fh_key == 'department_ids') {
                foreach ($filter_header as $dep_id) {
                    $department_list[] = $dlf->getNameById($dep_id);
                }
                $dep_strng = implode(', ', $department_list);
            }

            $blf = TTnew('BranchListFactory');
            if ($fh_key == 'branch_ids') {
                foreach ($filter_header as $br_id) {
                    $branch_list[] = $blf->getNameById($br_id);
                }
                $br_strng = implode(', ', $branch_list);
            }
            $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

            if ($br_strng == null) {
                $company_name = $current_company->getName();
                $addrss1 = $current_company->getAddress1();
                $address2 = $current_company->getAddress2();
                $city = $current_company->getCity();
                $postalcode = $current_company->getPostalCode();
            } else {
                $company_name = $blf->getNameById($br_id);
                $addrss1 = $blf->getAddress1ById($br_id);
                $address2 = $blf->getAddress2ById($br_id);
                $city = $blf->getCityById($br_id);
                $postalcode = $blf->getPostCodeById($br_id);
            }

            $uglf = TTnew('UserGroupListFactory');
            if ($fh_key == 'group_ids') {
                foreach ($filter_header as $gr_id) {
                    $group_list[] = $uglf->getNameById($gr_id);
                }
                $gr_strng = implode(', ', $group_list);
            }
        }
        if ($dep_strng == '') {
            $dep_strng = 'All';
        }

        $pplf = TTnew('PayPeriodListFactory');
        if (isset($filter_data['pay_period_ids'][0])) {
            $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
            $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
        } else {
            $pay_period_start = $filter_data['start_date'];
            $pay_period_end = $filter_data['end_date'];
        }

        $date_month = date('m-Y', $pay_period_start);
        $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

        $dates = array();
        $current = $pay_period_start;
        $last = $pay_period_end;



        $ignore_last_row = TRUE;
        $include_header = TRUE;
        $eol = "\n";


        if (is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0) {
            $rows = $data; //echo '<pre>'; print_r($rows);

            if ($ignore_last_row === TRUE) {
                $last_row = array_pop($data); //ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
            }

            $_SESSION['header_data'] = array(
                'image_path' => $current_company->getLogoFileName(),
                'company_name' => $company_name,
                'address1' => $addrss1,
                'address2' => $address2,
                'city' => $city,
                'province' => $current_company->getProvince(),
                'postal_code' => $postalcode,
                'heading' => 'Lieu Leave and Duty to Lieu Leave Report',
                'group_list' => $gr_strng,
                'department_list' => $dep_strng,
                'branch_list' => $br_strng,
                'payperiod_end_date' => date('Y-M', $pay_period_end),
                'line_width' => 280,
            );

            $pdf = TTnew('TimeReportHeaderFooter');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 50, 23);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $pdf->AddPage('l', 'mm', 'A4');

            //Table border
            $pdf->setLineWidth(0.20);

            //set table position
            $adjust_x = 19;

            $pdf->setXY(Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y));


            //TABLE CODE HERE
            //Header
            // create some HTML content
            $html = '<br><br><br><table border="0" cellspacing="0" cellpadding="0" width="100%"> ';
            
             $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
             $html = $html . '<td align="center" width="10%">EMP NO</td>';
             $html = $html . '<td align="center" width="40%">EMPLOYEE NAME</td>';
             $html = $html . '<td align="center" width="20%">LIEU LEAVE</td>';
             $html = $html . '<td align="center" width="20%">DUE TO LIEU LEAVE</td>';
             
             $html = $html . '</tr>';
             

            $pdf->SetFont('', 'B', 7);

            foreach ($rows as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $rows);
        
       
            foreach ($rows as $row) {
             
             
            

            $pdf->SetFont('', 'B', 8);
            
            foreach($row['data']as $dates){
                
                if(isset($dates['absence_type']) && $dates['absence_type'] !='' && $dates['date_stamp']!='' ){
                
                    $html = $html . '<tr style ="text-align:center" bgcolor="" nobr="true">';
                    $html = $html . '<td align="left" width="10%">' . $row['employee_number'] . '</td>';
                    $html = $html . '<td align="left" width="40%">' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
                    
                    if($dates['absence_type']==7){
                     $html = $html . '<td width="20%">'.$dates['date_stamp'].'</td>';
                      $html = $html . '<td width="20%"></td>';
                    }
              
                    if($dates['absence_type']==9){
                      $html = $html . '<td width="20%"></td>';
                      $html = $html . '<td width="20%">'.$dates['date_stamp'].'</td>';
                    }
              
                    
                    $html = $html . '</tr>';
               
                    
                }    
                
                   
            }
             
             
         }
         
                $html = $html . '</table>';

            
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            unset($_SESSION['header_data']);

            //Close and output PDF document
            //$pdf->Output('example_006.pdf', 'I');
            $output = $pdf->Output('', 'S');

            //exit;  

            if (isset($output)) {
                return $output;
            }
        }
            return FALSE;
        
    }

    
    
    
    function convertMinutesToHourFormat($mins) {
        $hours = floor($mins / 60);
        $minutes = ($mins % 60);
        if (strlen($hours) < 2) {
            $hours = "0" . $hours;
        }
        if (strlen($minutes) < 2) {
            $minutes = "0" . $minutes;
        }
        return $hours . ":" . $minutes;
    }
    
    function convertTimeToMinFormat($time_string) {
        $time_temp = explode(":", $time_string);
        $h_to_min = intval($time_temp[0])* 60;
        $min = intval($time_temp[1]);
        
        $total_min = $h_to_min + $min;
        return $total_min;
    }

}

class times_counter {

    private $hou = 0;
    private $min = 0;
    private $sec = 0;
    private $totaltime = '00:00:00';

    public function __construct($times) {

        if (is_array($times)) {

            $length = sizeof($times);

            for ($x = 0; $x <= $length; $x++) {
                $split = explode(":", @$times[$x]);
                $this->hou += @$split[0];
                $this->min += @$split[1];
                $this->sec += @$split[2];
            }

            $seconds = $this->sec % 60;
            $minutes = $this->sec / 60;
            $minutes = (integer) $minutes;
            $minutes += $this->min;
            $hours = $minutes / 60;
            $minutes = $minutes % 60;
            $hours = (integer) $hours;
            $hours += $this->hou;
            //$days = $hours/24;
            //$hours += ($days *24)+$hours;
            $this->totaltime = $hours . ":" . $minutes;
        }
    }

    public function get_total_time() {
        return $this->totaltime;
    }

}

?>
