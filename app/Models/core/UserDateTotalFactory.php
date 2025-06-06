<?php

namespace App\Models\Core;

use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Policy\BreakPolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Punch\PunchControlListFactory;
use App\Models\Holiday\HolidayListFactory;
use App\Models\Policy\PolicyGroupListFactory;
use App\Models\Punch\PunchControlFactory;
use App\Models\Schedule\ScheduleListFactory;
use App\Models\Users\UserFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Policy\ExceptionPolicyFactory;
use App\Models\Policy\HolidayPolicyListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Company\BranchListFactory;
use App\Models\Department\DepartmentListFactory;
use DateInterval;
use DateTime;

class UserDateTotalFactory extends Factory {

    protected $table = 'user_date_total';
    protected $pk_sequence_name = 'user_date_total_id_seq'; //PK Sequence name
    protected $user_date_obj = NULL;
    protected $punch_control_obj = NULL;
    protected $overtime_policy_obj = NULL;
    protected $premium_policy_obj = NULL;
    protected $absence_policy_obj = NULL;
    protected $meal_policy_obj = NULL;
    protected $break_policy_obj = NULL;
    protected $job_obj = NULL;
    protected $job_item_obj = NULL;
    protected $calc_system_total_time = FALSE;
    static $calc_future_week = FALSE; //Used for BiWeekly overtime policies to schedule future week recalculating.

    function _getFactoryOptions($name) {

        $retval = NULL;
        switch ($name) {
            case 'status':
                $retval = array(
                    10 => ('System'),
                    20 => ('Worked'),
                    30 => ('Absence')
                );
                break;
            case 'type':
                $retval = array(
                    10 => ('Total'),
                    20 => ('Regular'),
                    30 => ('Overtime'),
                    40 => ('Premium'),
                    100 => ('Lunch'),
                    110 => ('Break')
                );
                break;
            case 'status_type':
                $retval = array(
                    10 => array(10, 20, 30, 40, 100, 110),
                    20 => array(10),
                    30 => array(10),
                );
                break;
            case 'columns':
                $retval = array(
                    '-1000-first_name' => ('First Name'),
                    '-1002-last_name' => ('Last Name'),
                    '-1005-user_status' => ('Employee Status'),
                    '-1010-title' => ('Title'),
                    '-1039-group' => ('Group'),
                    '-1040-default_branch' => ('Default Branch'),
                    '-1050-default_department' => ('Default Department'),
                    '-1160-branch' => ('Branch'),
                    '-1170-department' => ('Department'),
                    '-1200-type' => ('Type'),
                    '-1202-status' => ('Status'),
                    '-1210-date_stamp' => ('Date'),
                    '-1290-total_time' => ('Time'),
                    '-2000-created_by' => ('Created By'),
                    '-2010-created_date' => ('Created Date'),
                    '-2020-updated_by' => ('Updated By'),
                    '-2030-updated_date' => ('Updated Date'),
                );
                break;
            case 'list_columns':
                $retval = Misc::arrayIntersectByKey($this->getOptions('default_display_columns'), Misc::trimSortPrefix($this->getOptions('columns')));
                break;
            case 'default_display_columns': //Columns that are displayed by default.
                $retval = array(
                    'status',
                    'time_stamp',
                );
                break;
            case 'unique_columns': //Columns that are unique, and disabled for mass editing.
                $retval = array(
                );
                break;
            case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
                $retval = array(
                );
                break;
        }

        return $retval;
    }

    function _getVariableToFunctionMap( $data ) {
        $variable_function_map = array(
            'id' => 'ID',
            'user_id' => FALSE,
            'user_date_id' => 'UserDateID',
            'over_time_policy_id' => 'OverTimePolicyID',
            'over_time_policy' => FALSE,
            'premium_policy_id' => 'PremiumPolicyID',
            'premium_policy' => FALSE,
            'absence_policy_id' => 'AbsencePolicyID',
            'absence_policy' => FALSE,
            'absence_policy_type_id' => FALSE,
            'meal_policy_id' => 'MealPolicyID',
            'meal_policy' => FALSE,
            'break_policy_id' => 'BreakPolicyID',
            'break_policy' => FALSE,
            'punch_control_id' => 'PunchControlID',
            'status_id' => 'Status',
            'status' => FALSE,
            'type_id' => 'Type',
            'type' => FALSE,
            'branch_id' => 'Branch',
            'branch' => FALSE,
            'department_id' => 'Department',
            'department' => FALSE,
            'job_id' => 'Job',
            'job' => FALSE,
            'job_item_id' => 'JobItem',
            'job_item' => FALSE,
            'quantity' => 'Quantity',
            'bad_quantity' => 'BadQuantity',
            'start_time_stamp' => 'StartTimeStamp',
            'end_time_stamp' => 'EndTimeStamp',
            'total_time' => 'TotalTime',
            'actual_total_time' => 'ActualTotalTime',
            'name' => FALSE,
            'override' => 'Override',
            'first_name' => FALSE,
            'last_name' => FALSE,
            'user_status_id' => FALSE,
            'user_status' => FALSE,
            'group_id' => FALSE,
            'group' => FALSE,
            'title_id' => FALSE,
            'title' => FALSE,
            'default_branch_id' => FALSE,
            'default_branch' => FALSE,
            'default_department_id' => FALSE,
            'default_department' => FALSE,
            'date_stamp' => FALSE,
            'pay_period_id' => FALSE,
            'deleted' => 'Deleted',
        );
        return $variable_function_map;
    }

    function getUserDateObject() {
        if (is_object($this->user_date_obj)) {
            return $this->user_date_obj;
        } else {
            $udlf = new UserDateListFactory();
            $udlf->getById($this->getUserDateID());
            if ($udlf->getRecordCount() > 0) {
                $this->user_date_obj = $udlf->getCurrent();
            }

            return $this->user_date_obj;
        }
    }

    function getPunchControlObject() {
        if (is_object($this->punch_control_obj)) {
            return $this->punch_control_obj;
        } else {
            $pclf = new PunchControlListFactory();
            $pclf->getById($this->getPunchControlID());
            if ($pclf->getRecordCount() > 0) {
                $this->punch_control_obj = $pclf->getCurrent();
            }

            return $this->punch_control_obj;
        }
    }

    function getOverTimePolicyObject() {
        if (is_object($this->overtime_policy_obj)) {
            return $this->overtime_policy_obj;
        } else {
            $otplf = new OverTimePolicyListFactory();
            $otplf->getById($this->getOverTimePolicyID());
            if ($otplf->getRecordCount() > 0) {
                $this->overtime_policy_obj = $otplf->getCurrent();
            }

            return $this->overtime_policy_obj;
        }
    }

    function getPremiumPolicyObject() {
        if (is_object($this->premium_policy_obj)) {
            return $this->premium_policy_obj;
        } else {
            $pplf = new PremiumPolicyListFactory();
            $pplf->getById($this->getPremiumPolicyID());
            if ($pplf->getRecordCount() > 0) {
                $this->premium_policy_obj = $pplf->getCurrent();
            }

            return $this->premium_policy_obj;
        }
    }

    function getAbsencePolicyObject() {
        if (is_object($this->absence_policy_obj)) {
            return $this->absence_policy_obj;
        } else {
            $aplf = new AccrualPolicyListFactory();
            $aplf->getById($this->getAbsencePolicyID());
            if ($aplf->getRecordCount() > 0) {
                $this->absence_policy_obj = $aplf->getCurrent();
            }

            return $this->absence_policy_obj;
        }
    }
    
    function getAbsencePolicyObjectAqua() {
        if (is_object($this->absence_policy_obj)) {
            return $this->absence_policy_obj;
        } else {
            $aplf = new AbsencePolicyListFactory();
            $aplf->getById($this->getAbsencePolicyID());
            if ($aplf->getRecordCount() > 0) {
                $this->absence_policy_obj = $aplf->getCurrent();
            }

            return $this->absence_policy_obj;
        }
    }

    function getMealPolicyObject() {
        if (is_object($this->meal_policy_obj)) {
            return $this->meal_policy_obj;
        } else {
            $mplf = new MealPolicyListFactory();
            $mplf->getById($this->getMealPolicyID());
            if ($mplf->getRecordCount() > 0) {
                $this->meal_policy_obj = $mplf->getCurrent();
                return $this->meal_policy_obj;
            }

            return FALSE;
        }
    }

    function getBreakPolicyObject() {
        if (is_object($this->break_policy_obj)) {
            return $this->break_policy_obj;
        } else {
            $bplf = new BreakPolicyListFactory();
            $bplf->getById($this->getBreakPolicyID());
            if ($bplf->getRecordCount() > 0) {
                $this->break_policy_obj = $bplf->getCurrent();
                return $this->break_policy_obj;
            }

            return FALSE;
        }
    }

    function getJobObject() {
        if (is_object($this->job_obj)) {
            return $this->job_obj;
        } else {
            $jlf = new JobListFactory();
            $jlf->getById($this->getJob());
            if ($jlf->getRecordCount() > 0) {
                $this->job_obj = $jlf->getCurrent();
                return $this->job_obj;
            }

            return FALSE;
        }
    }

    function getJobItemObject() {
        if (is_object($this->job_item_obj)) {
            return $this->job_item_obj;
        } else {
            $jilf = new JobItemListFactory();
            $jilf->getById($this->getJobItem());
            if ($jilf->getRecordCount() > 0) {
                $this->job_item_obj = $jilf->getCurrent();
                return $this->job_item_obj;
            }

            return FALSE;
        }
    }

    function setUserDate($user_id, $date) {
        $user_date_id = UserDateFactory::findOrInsertUserDate($user_id, $date);
        Debug::text(' User Date ID: ' . $user_date_id, __FILE__, __LINE__, __METHOD__, 10);
        if ($user_date_id != '') {
            $this->setUserDateID($user_date_id);
            return TRUE;
        }
        Debug::text(' No User Date ID found', __FILE__, __LINE__, __METHOD__, 10);

        return FALSE;
    }

    function getUserDateID() {
        if (isset($this->data['user_date_id'])) {
            return $this->data['user_date_id'];
        }

        return FALSE;
    }

    function setUserDateID($id) {
        $id = trim($id);

        $udlf = new UserDateListFactory();

        if ($this->Validator->isResultSetWithRows('user_date', $udlf->getByID($id), ('Date/Time is incorrect, or pay period does not exist for this date')
                )) {
            $this->data['user_date_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getOverTimePolicyID() {
        if (isset($this->data['over_time_policy_id'])) {
            return $this->data['over_time_policy_id'];
        }

        return FALSE;
    }

    function setOverTimePolicyID($id) {
        $id = trim($id);

        $otplf = new OverTimePolicyListFactory();

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('over_time_policy_id', $otplf->getByID($id), ('Invalid Overtime Policy')
                )) {
            $this->data['over_time_policy_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getPremiumPolicyID() {
        if (isset($this->data['premium_policy_id'])) {
            return $this->data['premium_policy_id'];
        }

        return FALSE;
    }

    function setPremiumPolicyID($id) {
        $id = trim($id);

        $pplf = new PremiumPolicyListFactory();

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('premium_policy_id', $pplf->getByID($id), ('Invalid Premium Policy ID')
                )) {
            $this->data['premium_policy_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getAbsencePolicyID() {
        if (isset($this->data['absence_policy_id'])) {
            return $this->data['absence_policy_id'];
        }

        return FALSE;
    }

    function setAbsencePolicyID($id) {
        $id = trim($id);

        $aplf = new AccrualPolicyListFactory();

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        if (
                $id == 0
                OR
                $this->Validator->isResultSetWithRows('absence_policy_id', $aplf->getByID($id), ('Invalid Accural Policy ID')
                )) {
            $this->data['absence_policy_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getMealPolicyID() {
        if (isset($this->data['meal_policy_id'])) {
            return $this->data['meal_policy_id'];
        }

        return FALSE;
    }

    function setMealPolicyID($id) {
        $id = trim($id);

        $mplf = new MealPolicyListFactory();

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('meal_policy_id', $mplf->getByID($id), ('Invalid Meal Policy ID')
                )) {
            $this->data['meal_policy_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getBreakPolicyID() {
        if (isset($this->data['break_policy_id'])) {
            return $this->data['break_policy_id'];
        }

        return FALSE;
    }

    function setBreakPolicyID($id) {
        $id = trim($id);

        $bplf = new BreakPolicyListFactory();

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('break_policy_id', $bplf->getByID($id), ('Invalid Break Policy ID')
                )) {
            $this->data['break_policy_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getPunchControlID() {
        if (isset($this->data['punch_control_id'])) {
            return $this->data['punch_control_id'];
        }

        return FALSE;
    }

    function setPunchControlID($id) {
        $id = trim($id);

        $pclf = new PunchControlListFactory();

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('punch_control_id', $pclf->getByID($id), ('Invalid Punch Control ID')
                )) {
            $this->data['punch_control_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getStatus() {
        if (isset($this->data['status_id'])) {
            return $this->data['status_id'];
        }

        return FALSE;
    }

    function setStatus($status) {
        $status = trim($status);

        $key = Option::getByValue($status, $this->getOptions('status'));
        if ($key !== FALSE) {
            $status = $key;
        }

        if ($this->Validator->inArrayKey('status_id', $status, ('Incorrect Status'), $this->getOptions('status'))) {

            $this->data['status_id'] = $status;

            return FALSE;
        }

        return FALSE;
    }

    function getType() {
        if (isset($this->data['type_id'])) {
            return $this->data['type_id'];
        }

        return FALSE;
    }

    function setType($value) {
        $value = trim($value);

        $key = Option::getByValue($value, $this->getOptions('type'));
        if ($key !== FALSE) {
            $value = $key;
        }

        if ($this->Validator->inArrayKey('type_id', $value, ('Incorrect Type'), $this->getOptions('type'))) {

            $this->data['type_id'] = $value;

            return FALSE;
        }

        return FALSE;
    }

    function getTimeCategory() {
        if ($this->getStatus() == 10 AND $this->getType() == 10) {
            $column = 'paid_time';
        } elseif ($this->getStatus() == 10 AND $this->getType() == 20) {
            $column = 'regular_time';
        } elseif ($this->getStatus() == 10 AND $this->getType() == 30) {
            $column = 'over_time_policy-' . $this->getColumn('over_time_policy_id');
        } elseif ($this->getStatus() == 10 AND $this->getType() == 40) {
            $column = 'premium_policy-' . $this->getColumn('premium_policy_id');
        } elseif ($this->getStatus() == 30 AND $this->getType() == 10) {
            $column = 'absence_policy-' . $this->getColumn('absence_policy_id');
        } elseif (( $this->getStatus() == 20 AND $this->getType() == 10 ) OR ( $this->getStatus() == 10 AND $this->getType() == 100 )) {
            $column = 'worked_time';
        } else {
            $column = NULL;
        }

        return $column;
    }

    function getBranch() {
        if (isset($this->data['branch_id'])) {
            return $this->data['branch_id'];
        }

        return FALSE;
    }

    function setBranch($id) {
        $id = trim($id);

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        $blf = new BranchListFactory();

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('branch_id', $blf->getByID($id), ('Branch does not exist')
                )) {
            $this->data['branch_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getDepartment() {
        if (isset($this->data['department_id'])) {
            return $this->data['department_id'];
        }

        return FALSE;
    }

    function setDepartment($id) {
        $id = trim($id);

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        $dlf = new DepartmentListFactory(); 

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('department_id', $dlf->getByID($id), ('Department does not exist')
                )) {
            $this->data['department_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getJob() {
        if (isset($this->data['job_id'])) {
            return $this->data['job_id'];
        }

        return FALSE;
    }

    function setJob($id) {
        $id = trim($id);

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        if (getTTProductEdition() == TT_PRODUCT_PROFESSIONAL) {
            $jlf = new JobListFactory();
        }

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('job_id', $jlf->getByID($id), ('Job does not exist')
                )) {
            $this->data['job_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getJobItem() {
        if (isset($this->data['job_item_id'])) {
            return $this->data['job_item_id'];
        }

        return FALSE;
    }

    function setJobItem($id) {
        $id = trim($id);

        if ($id == FALSE OR $id == 0 OR $id == '') {
            $id = 0;
        }

        if (getTTProductEdition() == TT_PRODUCT_PROFESSIONAL) {
            $jilf = new JobItemListFactory();
        }

        if ($id == 0
                OR
                $this->Validator->isResultSetWithRows('job_item_id', $jilf->getByID($id), ('Job Item does not exist')
                )) {
            $this->data['job_item_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }

    function getQuantity() {
        if (isset($this->data['quantity'])) {
            return (float) $this->data['quantity'];
        }

        return FALSE;
    }

    function setQuantity($val) {
        $val = (float) $val;

        if ($val == FALSE OR $val == 0 OR $val == '') {
            $val = 0;
        }

        if ($val == 0
                OR
                $this->Validator->isFloat('quantity', $val, ('Incorrect quantity'))) {
            $this->data['quantity'] = $val;

            return TRUE;
        }

        return FALSE;
    }

    function getBadQuantity() {
        if (isset($this->data['bad_quantity'])) {
            return (float) $this->data['bad_quantity'];
        }

        return FALSE;
    }

    function setBadQuantity($val) {
        $val = (float) $val;

        if ($val == FALSE OR $val == 0 OR $val == '') {
            $val = 0;
        }


        if ($val == 0
                OR
                $this->Validator->isFloat('bad_quantity', $val, ('Incorrect bad quantity'))) {
            $this->data['bad_quantity'] = $val;

            return TRUE;
        }

        return FALSE;
    }

    function getStartTimeStamp($raw = FALSE) {
        if (isset($this->data['start_time_stamp'])) {
            if ($raw === TRUE) {
                return $this->data['start_time_stamp'];
            } else {
                //return $this->db->UnixTimeStamp( $this->data['start_date'] );
                //strtotime is MUCH faster than UnixTimeStamp
                //Must use ADODB for times pre-1970 though.
                return TTDate::strtotime($this->data['start_time_stamp']);
            }
        }

        return FALSE;
    }

    function setStartTimeStamp($epoch) {
        $epoch = trim($epoch);

        if ($epoch == ''
                OR
                $this->Validator->isDate('start_time_stamp', $epoch, ('Incorrect start time stamp'))
        ) {

            $this->data['start_time_stamp'] = $epoch;

            return TRUE;
        }

        return FALSE;
    }

    function getEndTimeStamp($raw = FALSE) {
        if (isset($this->data['end_time_stamp'])) {
            if ($raw === TRUE) {
                return $this->data['end_time_stamp'];
            } else {
                return TTDate::strtotime($this->data['end_time_stamp']);
            }
        }

        return FALSE;
    }

    function setEndTimeStamp($epoch) {
        $epoch = trim($epoch);

        if ($epoch == ''
                OR
                $this->Validator->isDate('end_time_stamp', $epoch, ('Incorrect end time stamp'))
        ) {

            $this->data['end_time_stamp'] = $epoch;

            return TRUE;
        }

        return FALSE;
    }

    function getTotalTime() {
        if (isset($this->data['total_time'])) {
            return (int) $this->data['total_time'];
        }
        return FALSE;
    }

    function setTotalTime($int) {
        $int = (int) $int;

        if ($this->Validator->isNumeric('total_time', $int, ('Incorrect total time'))) {
            $this->data['total_time'] = $int;

            return TRUE;
        }

        return FALSE;
    }

    function getActualTotalTime() {
        if (isset($this->data['actual_total_time'])) {
            return (int) $this->data['actual_total_time'];
        }
        return FALSE;
    }

    function setActualTotalTime($int) {
        $int = (int) $int;

        if ($this->Validator->isNumeric('actual_total_time', $int, ('Incorrect actual total time'))) {
            $this->data['actual_total_time'] = $int;

            return TRUE;
        }

        return FALSE;
    }

    function getOverride() {
        if (isset($this->data['override'])) {
            return $this->fromBool($this->data['override']);
        }
        return FALSE;
    }

    function setOverride($bool) {
        $this->data['override'] = $this->toBool($bool);

        return TRUE;
    }

    function getName() {
        switch ($this->getStatus() . $this->getType()) {
            case 1010:
                $name = ('Total Time');
                break;
            case 1020:
                $name = ('Regular Time');
                break;
            case 1030:
                if (is_object($this->getOverTimePolicyObject())) {
                    $name = $this->getOverTimePolicyObject()->getName();
                }
                break;
            case 1040:
                if (is_object($this->getPremiumPolicyObject())) {
                    $name = $this->getPremiumPolicyObject()->getName();
                }
                break;
            case 10100:
                if (is_object($this->getMealPolicyObject())) {
                    $name = $this->getMealPolicyObject()->getName();
                }
                break;
            case 10110:
                if (is_object($this->getBreakPolicyObject())) {
                    $name = $this->getBreakPolicyObject()->getName();
                }
                break;
            case 3010:
                if (is_object($this->getAbsencePolicyObject())) {
                    $name = $this->getAbsencePolicyObject()->getName();
                }
                break;
            default:
                $name = ('N/A');
                break;
        }

        if (isset($name)) {
            return $name;
        }

        return FALSE;
    }

    function getEnableCalcSystemTotalTime() {
        if (isset($this->calc_system_total_time)) {
            return $this->calc_system_total_time;
        }

        return FALSE;
    }

    function setEnableCalcSystemTotalTime($bool) {
        $this->calc_system_total_time = $bool;

        return TRUE;
    }

    function getEnableCalcWeeklySystemTotalTime() {
        if (isset($this->calc_weekly_system_total_time)) {
            return $this->calc_weekly_system_total_time;
        }

        return FALSE;
    }

    function setEnableCalcWeeklySystemTotalTime($bool) {
        $this->calc_weekly_system_total_time = $bool;

        return TRUE;
    }

    function getEnableCalcException() {
        if (isset($this->calc_exception)) {
            return $this->calc_exception;
        }

        return FALSE;
    }

    function setEnableCalcException($bool) {
        $this->calc_exception = $bool;

        return TRUE;
    }

    function getEnablePreMatureException() {
        if (isset($this->premature_exception)) {
            return $this->premature_exception;
        }

        return FALSE;
    }

    function setEnablePreMatureException($bool) {
        $this->premature_exception = $bool;

        return TRUE;
    }

    function getEnableCalcAccrualPolicy() {
        if (isset($this->calc_accrual_policy)) {
            return $this->calc_accrual_policy;
        }

        return FALSE;
    }

    function setEnableCalcAccrualPolicy($bool) {
        $this->calc_accrual_policy = $bool;

        return TRUE;
    }

    static function getEnableCalcFutureWeek() {
        if (isset(self::$calc_future_week)) {
            return self::$calc_future_week;
        }

        return FALSE;
    }

    static function setEnableCalcFutureWeek($bool) {
        self::$calc_future_week = $bool;

        return TRUE;
    }

    function getDailyTotalTime() {
        $udtlf = new UserDateTotalListFactory();

        $daily_total_time = $udtlf->getTotalSumByUserDateID($this->getUserDateID());
        Debug::text('Daily Total Time for Day: ' . $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

        return $daily_total_time;
    }

    function deleteSystemTotalTime() {
        //Delete everything that is not overrided.
        $udtlf = new UserDateTotalListFactory();
        $pcf = new PunchControlFactory();

        //Optimize for a direct delete query.
        if ($this->getUserDateID() > 0) {

            //Due to a MySQL gotcha: http://dev.mysql.com/doc/refman/5.0/en/subquery-errors.html
            //We need to wrap the subquery in a subquery of itself to hide it from MySQL
            //So it doesn't complain about updating a table and selecting from it at the same time.
            //MySQL v5.0.22 DOES NOT like this query, it takes 10+ seconds to run and seems to cause a deadlock.
            //Switch back to a select then a bulkDelete instead. Still fast enough I think.
            $udtlf->getByUserDateIdAndStatusAndOverrideAndMisMatchPunchControlUserDateId($this->getUserDateID(), array(10, 30), FALSE); //System totals
            $this->bulkDelete($this->getIDSByListFactory($udtlf));
        } else {
            Debug::text('NO System Total Records to delete...', __FILE__, __LINE__, __METHOD__, 10);
        }

        return TRUE;
    }

    function processTriggerTimeArray($trigger_time_arr, $weekly_total_time = 0) {
        if (is_array($trigger_time_arr) == FALSE OR count($trigger_time_arr) == 0) {
            return FALSE;
        }

        //Debug::Arr($trigger_time_arr, 'Source Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);
        //Create a duplicate trigger_time_arr that we can sort so we know the
        //first trigger time is always the first in the array.
        //We don't want to use this array in the loop though, because it throws off other ordering.
        $tmp_trigger_time_arr = Sort::multiSort($trigger_time_arr, 'trigger_time');

        //echo '<br>tmp_trigger_time_arr.....';print_r($tmp_trigger_time_arr);

        $first_trigger_time = $tmp_trigger_time_arr[0]['trigger_time']; //Get first trigger time.
        //Debug::Arr($tmp_trigger_time_arr, 'Trigger Time After Sort: ', __FILE__, __LINE__, __METHOD__, 10);
        Debug::text('Weekly Total Time: ' . (int) $weekly_total_time . ' First Trigger Time: ' . $first_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

        unset($tmp_trigger_time_arr);

        //Sort trigger_time array by calculation order before looping over it.
        //$trigger_time_arr = Sort::multiSort( $trigger_time_arr, 'calculation_order', 'trigger_time', 'asc', 'desc' );
        $trigger_time_arr = Sort::arrayMultiSort($trigger_time_arr, array('calculation_order' => SORT_ASC, 'trigger_time' => SORT_DESC, 'combined_rate' => SORT_DESC));

        //Debug::Arr($trigger_time_arr, 'Source Trigger Arr After Calculation Order Sort: ', __FILE__, __LINE__, __METHOD__, 10);
        //We need to calculate regular time as early as possible so we can adjust the trigger time
        //of weekly overtime policies and re-sort the array.
        $tmp_trigger_time_arr = array();

        foreach ($trigger_time_arr as $key => $trigger_time_data) {

            //echo '<br>trigger_time_data'. $trigger_time_data['over_time_policy_type_id'];

            if ($trigger_time_data['over_time_policy_type_id'] == 20 OR $trigger_time_data['over_time_policy_type_id'] == 30 OR $trigger_time_data['over_time_policy_type_id'] == 210) {
                /* if ( $trigger_time_data['over_time_policy_type_id'] == 20 OR $trigger_time_data['over_time_policy_type_id'] == 80 OR $trigger_time_data['over_time_policy_type_id'] == 210 ) { */

                if (is_numeric($weekly_total_time)
                        AND $weekly_total_time > 0
                        AND $weekly_total_time >= $trigger_time_data['trigger_time']) {
                    //Worked more then weekly trigger time already.
                    Debug::Text('Worked more then weekly trigger time...', __FILE__, __LINE__, __METHOD__, 10);

                    $tmp_trigger_time = 0;
                } else {
                    //Haven't worked more then the weekly trigger time yet.
                    $tmp_trigger_time = $trigger_time_data['trigger_time'] - $weekly_total_time;
                    Debug::Text('NOT Worked more then weekly trigger time... TMP Trigger Time: ' . $tmp_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

                    if (is_numeric($weekly_total_time)
                            AND $weekly_total_time > 0
                            AND $tmp_trigger_time > $first_trigger_time) {
                        Debug::Text('Using First Trigger Time: ' . $first_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        $tmp_trigger_time = $first_trigger_time;
                    }
                }

                $trigger_time_arr[$key]['trigger_time'] = $tmp_trigger_time;
            } else {
                Debug::Text('NOT weekly overtime policy...', __FILE__, __LINE__, __METHOD__, 10);

                $tmp_trigger_time = $trigger_time_data['trigger_time'];
            }

            Debug::Text('Trigger Time: ' . $tmp_trigger_time . ' Overtime Policy Id: ' . $trigger_time_data['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);

            if (!in_array($tmp_trigger_time, $tmp_trigger_time_arr)) {
                Debug::Text('Adding policy to final array... Trigger Time: ' . $tmp_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                $trigger_time_data['trigger_time'] = $tmp_trigger_time;
                $retval[] = $trigger_time_data;
            } else {
                Debug::Text('NOT Adding policy to final array...', __FILE__, __LINE__, __METHOD__, 10);
            }

            $tmp_trigger_time_arr[] = $trigger_time_arr[$key]['trigger_time'];
        }
        //die;
        unset($trigger_time_arr, $tmp_trigger_time_arr, $trigger_time_data);

        $retval = Sort::multiSort($retval, 'trigger_time');

        //echo '<br>brfore';
        //print_r($retval);
        //Debug::Arr($retval, 'Dest Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);
        //Loop through final array and remove policies with higher trigger times and lower rates.
        //The rate matters as we don't want one policy after 8hrs to have a lower rate than a policy after 0hrs. (ie: Holiday OT after 0hrs @ 2x and Daily OT after 8hrs @ 1.5x)
        //Are there any scenarios where an employee works more hours and gets a lesser rate?
        $prev_combined_rate = 0;
        foreach ($retval as $key => $policy_data) {
            if ($policy_data['combined_rate'] < $prev_combined_rate) {
                Debug::Text('Removing policy with higher trigger time and lower combined rate... Key: ' . $key, __FILE__, __LINE__, __METHOD__, 10);
                unset($retval[$key]);
            } else {
                $prev_combined_rate = $policy_data['combined_rate'];
            }
        }
        unset($key, $policy_data);
        $retval = array_values($retval); //Rekey the array so there are no gaps.
        //Debug::Arr($retval, 'zDest Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);
        //echo '<br>after';
        //print_r($retval);

        return $retval;
    }

    function calcOverTimePolicyTotalTime($udt_meal_policy_adjustment_arr, $udt_break_policy_adjustment_arr) {
        global $profiler;

        $profiler->startTimer('UserDateTotal::calcOverTimePolicyTotalTime() - Part 1');

        //echo '<pre>';
        //Debug::setVerbosity(11);
        //If this user is scheduled, get schedule overtime policy id.
        $schedule_total_time = 0;
        $schedule_over_time_policy_id = 0;
        $schedule_start_time = 0;
        $schedule_end_time = 0;


//                echo 'user';
//                print_r($this->getUserDateObject()->getUser());
//                getByCompanyId
        $slf = new ScheduleListFactory();

        $slf->getByUserDateIdAndStatusId($this->getUserDateID(), 10);

        $user_ids[0] = $this->getUserDateObject()->getUser();

        $pglf = new PolicyGroupListFactory();
        $pglf->getByCompanyIdAndUserId($this->getUserDateObject()->getUserObject()->getCompany(), $user_ids);

        foreach ($pglf->rs as $pglf_obj) {
            $pglf->data = (array) $pglf_obj;
            $pglf_obj = $pglf;
            $policy_group_id = $pglf_obj->getId();
        }

        if ($slf->getRecordCount() > 0) {

            //Check for schedule policy
            foreach ($slf->rs as $s_obj) {
                $slf->data = (array) $s_obj;
                $s_obj = $slf;
                Debug::text(' Schedule Total Time: ' . $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
                $schedule_total_time += $s_obj->getTotalTime();
                $schedule_start_time = $s_obj->getStartTime();
                $schedule_end_time = $s_obj->getEndTime();
//                echo 'schedule_total_time:' . $schedule_total_time . '<br>';
//                echo 'schedule_start_time: ' . $schedule_start_time . ' <br>';
//                echo 'schedule_end_time: ' . $schedule_end_time . ' <br>';

                if (is_object($s_obj->getSchedulePolicyObject()) AND $s_obj->getSchedulePolicyObject()->getOverTimePolicyID() != FALSE) {
                    $schedule_over_time_policy_id = $s_obj->getSchedulePolicyObject()->getOverTimePolicyID();
                    Debug::text('Found New Schedule Overtime Policies to apply: ' . $schedule_over_time_policy_id, __FILE__, __LINE__, __METHOD__, 10);
                }
            }
        } else {
            //If they are not scheduled, we use the PolicyGroup list to get a Over Schedule / No Schedule overtime policy.
            //We could check for an active recurring schedule, but there could be multiple, and which
            //one do we use?

            $date_punch = new DateTime();
            $date_punch->setTimestamp($this->getUserDateObject()->getDateStamp());
            $day_name = $date_punch->format("l");

//                        if($day_name == 'Saturday'){ $schedule_over_time_policy_id = 3; }
//                        else
            if ($day_name == 'Sunday') {
                $schedule_over_time_policy_id = 3;
            }

            //$schedule_over_time_policy_id = 2;
            // echo '<br>'.$this->getUserDateObject()->getDateStamp();
        }

        //Apply policies for OverTime hours
        $otplf = new OverTimePolicyListFactory();
        $otp_calculation_order = $otplf->getOptions('calculation_order');

        $otplf->getByPolicyGroupUserIdOrId($this->getUserDateObject()->getUser(), $policy_group_id);

        if ($otplf->getRecordCount() > 0) {

            Debug::text('Found Overtime Policies to apply.', __FILE__, __LINE__, __METHOD__, 10);

            //Get Pay Period Schedule info
            if (is_object($this->getUserDateObject()->getPayPeriodObject())
                    AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject())) {

                $start_week_day_id = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
            } else {
                $start_week_day_id = 0;
            }
            Debug::text('Start Week Day ID: ' . $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

            //Convert all OT policies to daily before applying.
            //For instance, 40+hrs/week policy if they are currently at 35hrs is a 5hr daily policy.
            //For weekly OT policies, they MUST include regular time + other WEEKLY over time rules.
            $udtlf = new UserDateTotalListFactory();

            $weekly_total = $udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id));

            Debug::text('Weekly Total: ' . (int) $weekly_total, __FILE__, __LINE__, __METHOD__, 10);

            //Daily policy always takes precedence, then Weekly, Bi-Weekly, Day Of Week etc...
            //So unless the next policy in the list has a lower trigger time then the previous policy
            //We ignore it.
            //ie: if Daily OT is after 8hrs, and Day Of Week is after 10. Day of week will be ignored.
            //	If Daily OT is after 8hrs, and Weekly is after 40, and they worked 35 up to yesterday,
            //	and 12 hrs today, from 5hrs to 8hrs will be weekly, then anything after that is daily.
            //FIXME: Take rate into account, so for example if we have a daily OT policy after 8hrs at 1.5x
            //  and a Holiday OT policy after 0hrs at 2.0x. If the employee works 10hrs on the holiday we want all 10hrs to be Holiday time.
            //  We shouldn't go back to a lesser rate of 1.5x for the Daily OT policy. However if we do this we also need to take into account accrual rates, as time could be banked.
            //  Combine Rate and Accrual rate to use for sorting, as some 2.0x rate overtime policies might accrual/bank it all (Rate: 0 Accrual Rate: 2.0), but it should still be considered a 2.0x rate.
            //  *The work around for this currently is to have multiple holiday policies that match the daily overtime policies so they take priority.

            $hlf = new HolidayListFactory();
            $hlf->getByPolicyGroupUserIdAndDate($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp());

            foreach ($hlf->rs as $hlf_obj) {
                $hlf->data = (array) $hlf_obj;
                $hlf_obj = $hlf;
                $holiday_policy_id = $hlf_obj->getHolidayPolicyID();
            }

            $tmp_trigger_time_arr = array();
            $npvc_daily = false;
            foreach ($otplf->rs as $otp_obj) {
                $otplf->data = (array) $otp_obj;
                $otp_obj = $otplf;

                Debug::text('&nbsp;&nbsp;Checking Against Policy: ' . $otp_obj->getName() . ' Trigger Time: ' . $otp_obj->getTriggerTime(), __FILE__, __LINE__, __METHOD__, 10);
                $trigger_time = NULL;
                    //                echo '<br>------------------';
                    //                echo '<br>type:::'.$otp_obj->getType();
                    //                echo '<br>datestamp::'.date('Y-m-d',$this->getUserDateObject()->getDateStamp());
                
                switch ($otp_obj->getType()) {

                    case 10: //Daily
                        //echo '<br>type is 10 case:: Daily';
                        $trigger_time = $otp_obj->getTriggerTime();
                        $max_time = $otp_obj->getMaxTime();
                        $npvc_daily = true;
                        //echo '<br>trigger_time::' . $trigger_time;
                        Debug::text(' Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        break;
                    case 20: //Weekly
                        $trigger_time = $otp_obj->getTriggerTime();
                        Debug::text(' Weekly Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        break;
                    case 30: //Bi-Weekly
                        //Convert biweekly into a weekly policy by taking the hours worked in the
                        //first of the two week period and reducing the trigger time by that amount.
                        //When does the bi-weekly cutoff start though? It must have a hard date that it can be based on so we don't count the same week twice.
                        //Try to synchronize it with the week of the first pay period? Just figure out if we are odd or even weeks.
                        //FIXME: Set flag that tells smartRecalculate to calculate the next week or not.
                        $week_modifier = 0; //0=Even, 1=Odd
                        if (is_object($this->getUserDateObject()->getPayPeriodObject())) {
                            $week_modifier = TTDate::getWeek($this->getUserDateObject()->getPayPeriodObject()->getStartDate(), $start_week_day_id) % 2;
                        }
                        $current_week_modifier = TTDate::getWeek($this->getUserDateObject()->getDateStamp(), $start_week_day_id) % 2;
                        Debug::text(' Current Week: ' . $current_week_modifier . ' Week Modifier: ' . $week_modifier, __FILE__, __LINE__, __METHOD__, 10);

                        $first_week_total = 0;
                        if ($current_week_modifier != $week_modifier) {
                            //$udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch() uses "< $epoch" so the current day is ignored, but in this
                            //case we want to include the last day of the week, so we need to add one day to this argument.
                            $first_week_total = $udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch($this->getUserDateObject()->getUser(), TTDate::getEndWeekEpoch(($this->getUserDateObject()->getDateStamp() - (86400 * 7)), $start_week_day_id) + 86400, TTDate::getBeginWeekEpoch(($this->getUserDateObject()->getDateStamp() - (86400 * 7)), $start_week_day_id));
                            Debug::text(' Week modifiers differ, calculate total time for the first week: ' . $first_week_total, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            UserDateTotalFactory::setEnableCalcFutureWeek(TRUE);
                        }

                        $trigger_time = ( $otp_obj->getTriggerTime() - $first_week_total );
                        if ($trigger_time < 0) {
                            $trigger_time = 0;
                        }
                        Debug::text('Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);

                        unset($first_week_total, $week_modifier, $current_week_modifier);
                        break;
                    case 40: //Sunday
                    //                        echo '<br><br>type is 40 case sunday';
                        if (date('w', $this->getUserDateObject()->getDateStamp()) == 0) {
                            $trigger_time = $otp_obj->getTriggerTime();
                            $max_time = $otp_obj->getMaxTime();
                            Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2;
                        }

                        break;
                    case 50: //Monday
                        if (date('w', $this->getUserDateObject()->getDateStamp()) == 1) {
                            $trigger_time = $otp_obj->getTriggerTime();
                            Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2;
                        }
                        break;
                    case 60: //Tuesday
                        if (date('w', $this->getUserDateObject()->getDateStamp()) == 2) {
                            $trigger_time = $otp_obj->getTriggerTime();
                            Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2;
                        }
                        break;
                    case 70: //Wed
                        if (date('w', $this->getUserDateObject()->getDateStamp()) == 3) {
                            $trigger_time = $otp_obj->getTriggerTime();
                            Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2;
                        }
                        break;
                    case 80: //Thu
                        if (date('w', $this->getUserDateObject()->getDateStamp()) == 4) {
                            $trigger_time = $otp_obj->getTriggerTime();
                            Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2;
                        }
                        break;
                    case 90: //Fri
                        if (date('w', $this->getUserDateObject()->getDateStamp()) == 5) {
                            $trigger_time = $otp_obj->getTriggerTime();
                            Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2;
                        }
                        break;
                    case 100: //Sat
                                //                        echo '<br> type 100 Saturday';
                        if (date('w', $this->getUserDateObject()->getDateStamp()) == 6) {
                             $hlf = new HolidayListFactory();
                             $hlf->getByPolicyGroupUserIdAndDate($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp());
                             if ($hlf->getRecordCount() > 0) {
                                            //                                 echo '<br> have a holi count';
                                 continue 2;
                             } else {
                                //                                 echo '<br> not have a holi count';
                                 $trigger_time = $otp_obj->getTriggerTime();
                             }
                            Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2;
                        }
                        break;
                    case 150: //2-day Consecutive
                    case 151: //3-day Consecutive
                    case 152: //4-day Consecutive
                    case 153: //5-day Consecutive
                    case 154: //6-day Consecutive
                    case 155: //7-day Consecutive
                        switch ($otp_obj->getType()) {
                            case 150:
                                $minimum_days_worked = 2;
                                break;
                            case 151:
                                $minimum_days_worked = 3;
                                break;
                            case 152:
                                $minimum_days_worked = 4;
                                break;
                            case 153:
                                $minimum_days_worked = 5;
                                break;
                            case 154:
                                $minimum_days_worked = 6;
                                break;
                            case 155:
                                $minimum_days_worked = 7;
                                break;
                        }

                        //Should these be reset on the week boundary or should any consecutive days worked apply? Or should we offer both options?
                        //We should probably break this out to just a general "consecutive days worked" and add a field to specify any number of days
                        //and a field to specify if its only per week, or any timeframe.
                        //Will probably want to include a flag to consider scheduled days only too.
                        $weekly_days_worked = $udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate($this->getUserDateObject()->getUser(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id), $this->getUserDateObject()->getDateStamp());

                        Debug::text(' Weekly Days Worked: ' . $weekly_days_worked . ' Minimum Required: ' . $minimum_days_worked, __FILE__, __LINE__, __METHOD__, 10);

                        if ($weekly_days_worked >= $minimum_days_worked) {
                            $trigger_time = $otp_obj->getTriggerTime();
                            Debug::text(' After Days Consecutive... Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            Debug::text(' NOT After Days Consecutive Worked...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2;
                        }
                        unset($weekly_days_worked, $minimum_days_worked);
                        break;
                    case 180: // Poya Holiday
                            //                        echo '<br>type is 180 case Poya holiday';

                        $hlf = new HolidayListFactory();

                        $hlf->getByPolicyGroupUserIdAndDate($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp());

                        if ($hlf->getRecordCount() > 0) {
                            $holiday_obj = $hlf->getCurrent();
                            Debug::text(' Found Holiday: ' . $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

                            if ($holiday_obj->getHolidayPolicyObject()->getForceOverTimePolicy() == TRUE
                                    OR $holiday_obj->isEligible($this->getUserDateObject()->getUser())) {

                                if ($holiday_obj->getHolidayPolicyID() == '1') {
                                    $trigger_time = $otp_obj->getTriggerTime();
                                    $max_time = $otp_obj->getMaxTime();

                                    Debug::text(' Is Eligible for Holiday: ' . $holiday_obj->getName() . ' Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                                } else {
                                    Debug::text(' Not Poya Holiday...', __FILE__, __LINE__, __METHOD__, 10);
                                    continue 2; //Skip to next policy
                                }
                            } else {
                                Debug::text(' Not Eligible for Holiday: ' . $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
                                continue 2; //Skip to next policy
                            }
                        } else {
                            Debug::text(' Not Poya Holiday...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2; //Skip to next policy
                        }
                        unset($hlf, $holiday_obj);

                        break;
                    case 190: // S Holiday
                        //                        echo '<br>type is 190 case S holiday';

                        $hlf = new HolidayListFactory();

                        $hlf->getByPolicyGroupUserIdAndDate($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp());


                        if ($hlf->getRecordCount() > 0) {

                            $holiday_obj = $hlf->getCurrent();

                            //$holiday_policy_id = $holiday_obj->getHolidayPolicyID();
                            Debug::text(' Found Holiday: ' . $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

                            if ($holiday_obj->getHolidayPolicyObject()->getForceOverTimePolicy() == TRUE
                                    OR $holiday_obj->isEligible($this->getUserDateObject()->getUser())) {

                                if ($holiday_obj->getHolidayPolicyID() == '2') {
                                    $trigger_time = $otp_obj->getTriggerTime();
                                    $max_time = $otp_obj->getMaxTime();

                                    Debug::text(' Is Eligible for Holiday: ' . $holiday_obj->getName() . ' Daily Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                                } else {
                                    Debug::text(' Not S Holiday...', __FILE__, __LINE__, __METHOD__, 10);
                                    continue 2; //Skip to next policy
                                }
                            } else {
                                Debug::text(' Not Eligible for Holiday: ' . $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
                                continue 2; //Skip to next policy
                            }
                        } else {
                            Debug::text(' Not Holiday...', __FILE__, __LINE__, __METHOD__, 10);
                            continue 2; //Skip to next policy
                        }
                        unset($hlf, $holiday_obj);

                        break;
                    case 200: //Over schedule (Daily) / No Schedule. Have trigger time extend the schedule time.
                        $trigger_time = $schedule_total_time + $otp_obj->getTriggerTime();


                        Debug::text(' Over Schedule/No Schedule Trigger Time: ' . $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                        break;
                    case 210: //Over Schedule (Weekly) / No Schedule
                        //Get schedule time for the entire week, and add the Active After time to that.
                        $schedule_weekly_total_time = $slf->getWeekWorkTimeSumByUserIDAndEpochAndStartWeekEpoch($this->getUserDateObject()->getUser(), TTDate::getEndWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id));
                        Debug::text('Schedule Weekly Total Time: ' . $schedule_weekly_total_time, __FILE__, __LINE__, __METHOD__, 10);

                        $trigger_time = $schedule_weekly_total_time + $otp_obj->getTriggerTime();


                        unset($schedule_weekly_total_time);
                        break;
                }


                if (is_numeric($trigger_time) AND $trigger_time < 0) {
                    $trigger_time = 0;
                }

                if (is_numeric($trigger_time)) {
                    $trigger_time_arr[] = array('calculation_order' => $otp_calculation_order[$otp_obj->getType()], 'trigger_time' => $trigger_time, 'max_time' => $max_time ?? 0, 'over_time_policy_id' => $otp_obj->getId(), 'over_time_policy_type_id' => $otp_obj->getType(), 'combined_rate' => ($otp_obj->getRate() + $otp_obj->getAccrualRate()));
                }



                unset($trigger_time);
            }

            if (isset($trigger_time_arr)) {
                $trigger_time_arr = $this->processTriggerTimeArray($trigger_time_arr, $weekly_total);
            }

        } else {
            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;No OverTime Policies found for this user.', __FILE__, __LINE__, __METHOD__, 10);
        }
        unset($otp_obj, $otplf);

        if (isset($trigger_time_arr)) {

            $total_daily_hours = 0;
            $total_daily_hours_used = 0;

            //get all worked total hours.
            $udtlf = new UserDateTotalListFactory();
            $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20);
            if ($udtlf->getRecordCount() > 0) {

                Debug::text('Found Total Hours to attempt to apply policy: Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                if ($trigger_time_arr[0]['trigger_time'] > 0) {
                    //No trigger time set at 0.
                    $enable_regular_hour_calculating = TRUE;
                } else {
                    $enable_regular_hour_calculating = FALSE;
                }

                $tmp_policy_total_time = NULL;
                foreach ($udtlf->rs as $udt_obj) {
                    $udtlf->data = (array) $udt_obj;
                    $udt_obj = $udtlf;
                    //Ignore incomplete punches
                    if ($udt_obj->getTotalTime() == 0) {
                        continue;
                    }

                    $udt_total_time = $udt_obj->getTotalTime();
                    if (isset($udt_meal_policy_adjustment_arr[$udt_obj->getId()])) {
                        $udt_total_time = bcadd($udt_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                    }
                    if (isset($udt_break_policy_adjustment_arr[$udt_obj->getId()])) {
                        $udt_total_time = bcadd($udt_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                    }


                    $total_daily_hours = bcadd($total_daily_hours, $udt_total_time);

                    //Loop through each trigger.
                    $i = 0;

                    Debug::text('Total Hour: ID: ' . $udt_obj->getId() . ' Status: ' . $udt_obj->getStatus() . ' Total Time: ' . $udt_obj->getTotalTime() . ' Total Daily Hours: ' . $total_daily_hours . ' Used Total Time: ' . $total_daily_hours_used . ' Branch ID: ' . $udt_obj->getBranch() . ' Department ID: ' . $udt_obj->getDepartment() . ' Job ID: ' . $udt_obj->getJob() . ' Job Item ID: ' . $udt_obj->getJobItem() . ' Quantity: ' . $udt_obj->getQuantity(), __FILE__, __LINE__, __METHOD__, 10);


                    foreach ($trigger_time_arr as $trigger_time_data) {


                        if (isset($trigger_time_arr[$i + 1]['trigger_time']) AND $total_daily_hours_used >= $trigger_time_arr[$i + 1]['trigger_time']) {

                            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; ' . $i . ': SKIPPING THIS TRIGGER TIME: ' . $trigger_time_data['trigger_time'], __FILE__, __LINE__, __METHOD__, 10);

                            $i++;
                            continue;
                        }

                        Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; ' . $i . ': Trigger Time Data: Trigger Time: ' . $trigger_time_data['trigger_time'] . ' ID: ' . $trigger_time_data['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);
                        Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; ' . $i . ': Used Total Time: ' . $total_daily_hours_used, __FILE__, __LINE__, __METHOD__, 10);

                        //Only consider Regular Time ONCE per user date total row.
                        if ($i == 0
                                AND $trigger_time_arr[$i]['trigger_time'] > 0
                                AND $total_daily_hours_used < $trigger_time_arr[$i]['trigger_time']) {


                            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; ' . $i . ': Trigger Time: ' . $trigger_time_arr[$i]['trigger_time'] . ' greater then 0, found Regular Time.', __FILE__, __LINE__, __METHOD__, 10);

                            if ($total_daily_hours > $trigger_time_arr[$i]['trigger_time']) {

                                $regular_total_time = $trigger_time_arr[$i]['trigger_time'] - $total_daily_hours_used;
                                $regular_quantity_percent = bcdiv($trigger_time_arr[$i]['trigger_time'], $udt_obj->getTotalTime());
                                $regular_quantity = round(bcmul($udt_obj->getQuantity(), $regular_quantity_percent), 2);
                                $regular_bad_quantity = round(bcmul($udt_obj->getBadQuantity(), $regular_quantity_percent), 2);
                            } else {
                                //$regular_total_time = $udt_obj->getTotalTime();
                                $regular_total_time = $udt_total_time;
                                $regular_quantity = $udt_obj->getQuantity();
                                $regular_bad_quantity = $udt_obj->getBadQuantity();
                            }

                            /* echo '<br><br>regular_total_time<br>'.$regular_total_time;
                              echo '<br>regular_quantity_percent<br>'.$regular_quantity_percent;
                              echo '<br>regular_quantity<br>'.$regular_quantity;
                              echo '<br>regular_bad_quantity<br>'.$regular_bad_quantity; */


                            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; ' . $i . ': Regular Total Time: ' . $regular_total_time . ' Regular Quantity: ' . $regular_quantity, __FILE__, __LINE__, __METHOD__, 10);

                            if (isset($user_data_total_compact_arr[20][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()])) {

                                Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; Adding to Compact Array: Branch: ' . (int) $udt_obj->getBranch() . ' Department: ' . (int) $udt_obj->getDepartment(), __FILE__, __LINE__, __METHOD__, 10);

                                $user_data_total_compact_arr[20][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['total_time'] += $regular_total_time;

                                $user_data_total_compact_arr[20][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['quantity'] += $regular_quantity;

                                $user_data_total_compact_arr[20][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['bad_quantity'] += $regular_bad_quantity;
                            } else {
                                Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; Initiating Compact Sub-Array: Branch: ' . (int) $udt_obj->getBranch() . ' Department: ' . (int) $udt_obj->getDepartment(), __FILE__, __LINE__, __METHOD__, 10);
                                $user_data_total_compact_arr[20][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()] = array('total_time' => $regular_total_time, 'quantity' => $regular_quantity, 'bad_quantity' => $regular_bad_quantity);
                            }
                            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; Compact Array Regular Total: ' . $user_data_total_compact_arr[20][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['total_time'], __FILE__, __LINE__, __METHOD__, 10);

                            $total_daily_hours_used += $regular_total_time;
                        }


                        Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; ' . $i . ': Daily Total Time: ' . $total_daily_hours . ' Trigger Time: ' . $trigger_time_arr[$i]['trigger_time'] . ' Used Total Time: ' . $total_daily_hours_used . ' Overtime Policy Type: ' . $trigger_time_arr[$i]['over_time_policy_type_id'], __FILE__, __LINE__, __METHOD__, 10);

                        if ($total_daily_hours > $trigger_time_arr[$i]['trigger_time']) {

                            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; ' . $i . ': Trigger Time: ' . $trigger_time_arr[$i]['trigger_time'] . ' greater then 0, found Over Time.', __FILE__, __LINE__, __METHOD__, 10);

                            if (isset($trigger_time_arr[$i + 1]['trigger_time'])) {

                                Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; ' . $i . ': Found trigger time after this one: ' . $trigger_time_arr[$i + 1]['trigger_time'], __FILE__, __LINE__, __METHOD__, 10);

                                $max_trigger_time = $trigger_time_arr[$i + 1]['trigger_time'] - $trigger_time_arr[$i]['trigger_time'];
                            } else {
                                $max_trigger_time = $trigger_time_arr[$i]['trigger_time'];
                            }
                            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; aMax Trigger Time ' . $max_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                            if (isset($trigger_time_arr[$i + 1]['trigger_time']) AND $total_daily_hours_used > $trigger_time_arr[$i]['trigger_time']) {
                                //$max_trigger_time = $max_trigger_time - ($total_daily_hours_used - $max_trigger_time);
                                $max_trigger_time = $max_trigger_time - ($total_daily_hours_used - $trigger_time_arr[$i]['trigger_time']);
                            }
                            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; bMax Trigger Time ' . $max_trigger_time, __FILE__, __LINE__, __METHOD__, 10);


                            //print_r($trigger_time_arr);
                            //Calculate overtime for NPVC after scheduled time
                            if ($npvc_daily) {//
                                //WITHOUT OT ROUND OFF
                                /* if($schedule_start_time>0 && ($trigger_time_arr[$i]['over_time_policy_type_id']==40 || $trigger_time_arr[$i]['over_time_policy_type_id']==180) )
                                  {
                                  $plf = new PunchListFactory();
                                  $plf->getByUserDateId($this->getUserDateID());
                                  foreach( $plf as $plf_obj ) {

                                  $inout_time[$plf_obj->getStatus()][] = $plf_obj->getTimeStamp();
                                  }

                                  //print_r($inout_time); die;

                                  $cal_start_time = min($inout_time[10]);
                                  $cal_end_time = max($inout_time[20]);

                                  //$schedule_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                  //echo '<br><br>schedule_start_time0...'.$schedule_start_time.'....'.date('Y-m-d H:i:s', $schedule_start_time);
                                  //echo '<br><br>start_start_time0...'.$cal_start_time.'....'.date('Y-m-d H:i:s', $cal_start_time);

                                  if($schedule_start_time>$cal_start_time)
                                  {
                                  $cal_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                  }
                                  else
                                  {
                                  $cal_start_time = date('Y-m-d H:i:s', $cal_start_time);
                                  }
                                  $cal_end_time = date('Y-m-d H:i:s', $cal_end_time);

                                  //End time - Start time
                                  $dteStart = new DateTime($cal_start_time);
                                  //$dteStart->add(new DateInterval('PT30M'));
                                  $dteEnd   = new DateTime($cal_end_time);
                                  $dteDiff  = $dteStart->diff($dteEnd);

                                  //echo '<br><br>date_diff...'.$dteDiff->format("%R%H").',,,,'.$date_diff = $dteDiff->format("%R%H:%I");


                                  $over_time_total = $dteDiff->format("%R%H")*3600+$dteDiff->format("%R%I")*60+$dteDiff->format("%R%S");

                                  //echo '<br>over_time_total....'.$over_time_total;
                                  }

                                  elseif($schedule_start_time>0)
                                  {
                                  $plf = new PunchListFactory();
                                  $plf->getByUserDateId($this->getUserDateID());
                                  foreach( $plf as $plf_obj ) {

                                  $inout_time[$plf_obj->getStatus()][] = $plf_obj->getTimeStamp();
                                  }

                                  //print_r($inout_time); die;

                                  $cal_start_time = min($inout_time[10]);
                                  $cal_end_time = max($inout_time[20]);

                                  //$schedule_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                  //echo '<br><br>schedule_start_time0...'.$schedule_start_time.'....'.date('Y-m-d H:i:s', $schedule_start_time);
                                  //echo '<br><br>start_start_time0...'.$cal_start_time.'....'.date('Y-m-d H:i:s', $cal_start_time);

                                  if($schedule_start_time>$cal_start_time)
                                  {
                                  $cal_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                  }
                                  else
                                  {
                                  $cal_start_time = date('Y-m-d H:i:s', $cal_start_time);
                                  }
                                  $cal_end_time = date('Y-m-d H:i:s', $cal_end_time);

                                  // echo '<br><br>start_start_time...'.$cal_start_time;
                                  // echo '<br><br>end_start_time...'.$cal_end_time;
                                  // echo '<br><br>schedule_start_time...'.date('Y-m-d H:i:s', $schedule_start_time);
                                  // echo '<br><br>schedule_total_time....'.gmdate("H:i:s", $schedule_total_time);

                                  //End time - (Start time + Schedule total time)

                                  //Add Start time and Schedule total time
                                  $date_added = new DateTime($cal_start_time);
                                  $date_added->add(new DateInterval('PT'.gmdate("H", $schedule_total_time).'H'.gmdate("i", $schedule_total_time).'M'.gmdate("s", $schedule_total_time).'S'));

                                  //echo $date_added->format('Y-m-d H:i:s');

                                  //Add another 30mins
                                  $date_added->add(new DateInterval('PT30M'));

                                  $strtotime_date_added = strtotime($date_added->format('Y-m-d H:i:s'));
                                  $strtotime_cal_end_time = strtotime($cal_end_time);


                                  // $dteStart = new DateTime($date_added->format('Y-m-d H:i:s'));
                                  // 			$dteEnd   = new DateTime($cal_end_time);

                                  // 			$dteDiff  = $dteStart->diff($dteEnd);

                                  // 			$over_time_total = $dteDiff->format("%R%H")*3600+$dteDiff->format("%R%I")*60+$dteDiff->format("%R%S");

                                  // echo '<br><br>strtotime_date_added...'.$strtotime_date_added;
                                  // echo '<br><br>strtotime_cal_end_time...'.$strtotime_cal_end_time;
                                  // echo '<br><br>strtotime_date_added...'.date('Y-m-d H:i:s',$strtotime_date_added);
                                  // echo '<br><br>strtotime_cal_end_time...'.date('Y-m-d H:i:s',$strtotime_cal_end_time);
                                  // die;

                                  //OT will be calculated only if work for more than or equal 30mins
                                  if($strtotime_date_added < $strtotime_cal_end_time)
                                  {
                                  //Add Start time and Schedule total time
                                  $date_shcdl_added = new DateTime($cal_start_time);
                                  $date_shcdl_added->add(new DateInterval('PT'.gmdate("H", $schedule_total_time).'H'.gmdate("i", $schedule_total_time).'M'.gmdate("s", $schedule_total_time).'S'));
                                  $dteStart = new DateTime($date_shcdl_added->format('Y-m-d H:i:s'));
                                  $dteEnd   = new DateTime($cal_end_time);

                                  $dteDiff  = $dteStart->diff($dteEnd);

                                  $over_time_total = $dteDiff->format("%R%H")*3600+$dteDiff->format("%R%I")*60+$dteDiff->format("%R%S");
                                  }
                                  else
                                  {
                                  $over_time_total =0;
                                  }

                                  //echo '<br><br>date_diff...'.$dteDiff->format("%R%H").',,,,'.$date_diff = $dteDiff->format("%R%H:%I");



                                  //echo '<br>over_time_total....'.$over_time_total;
                                  }
                                  elseif($schedule_total_time==0)
                                  {
                                  //echo '<br>schedule_total_time<0';

                                  //print_r($trigger_time_arr);

                                  $plf = new PunchListFactory();
                                  $plf->getByUserDateId($this->getUserDateID());
                                  foreach( $plf as $plf_obj ) {

                                  $inout_time[$plf_obj->getStatus()][] = $plf_obj->getTimeStamp();
                                  }

                                  //print_r($inout_time); die;

                                  $cal_start_time = min($inout_time[10]);
                                  $cal_end_time = max($inout_time[20]);

                                  //$schedule_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                  //echo '<br><br>schedule_start_time0...'.$schedule_start_time.'....'.date('Y-m-d H:i:s', $schedule_start_time);
                                  //echo '<br><br>start_start_time0...'.$cal_start_time.'....'.date('Y-m-d H:i:s', $cal_start_time);

                                  $cal_start_time = date('Y-m-d H:i:s', $cal_start_time);
                                  $cal_end_time = date('Y-m-d H:i:s', $cal_end_time);

                                  //End time - Start time
                                  $dteStart = new DateTime($cal_start_time);
                                  //$dteStart->add(new DateInterval('PT30M'));
                                  $dteEnd   = new DateTime($cal_end_time);
                                  $dteDiff  = $dteStart->diff($dteEnd);

                                  //echo '<br><br>date_diff...'.$dteDiff->format("%R%H").',,,,'.$date_diff = $dteDiff->format("%R%H:%I");

                                  $over_time_total = $dteDiff->format("%R%H")*3600+$dteDiff->format("%R%I")*60+$dteDiff->format("%R%S");

                                  } */



//                                echo 'schedule_start_time::'.$schedule_start_time;
                                //WITH OT ROUND OFF AND OUT AT SHIFT END TIME
                                if ($schedule_start_time > 0 && ($trigger_time_arr[$i]['over_time_policy_type_id'] == 40 || $trigger_time_arr[$i]['over_time_policy_type_id'] == 100 || $trigger_time_arr[$i]['over_time_policy_type_id'] == 180 || $trigger_time_arr[$i]['over_time_policy_type_id'] == 190)) {

                                    $plf = new PunchListFactory();
                                    $plf->getByUserDateId($this->getUserDateID());
                                    foreach ($plf->rs as $plf_obj) {
                                        $plf->data = (array) $plf_obj;
                                        $plf_obj = $plf;
                                        $inout_time[$plf_obj->getStatus()][] = $plf_obj->getTimeStamp();
                                    }

                                    //print_r($inout_time); die;

                                    $cal_start_time = min($inout_time[10]);
                                    $cal_end_time = max($inout_time[20]);

                                    //$schedule_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                    //echo '<br><br>schedule_start_time0...'.$schedule_start_time.'....'.date('Y-m-d H:i:s', $schedule_start_time);
                                    //echo '<br><br>start_start_time0...'.$cal_start_time.'....'.date('Y-m-d H:i:s', $cal_start_time);

                                    /* if($schedule_start_time>$cal_start_time)
                                      {
                                      $cal_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                      }
                                      else
                                      {
                                      $cal_start_time = date('Y-m-d H:i:s', $cal_start_time);
                                      } */

                                    $cal_start_time = date('Y-m-d H:i:s', $cal_start_time);

                                    $cal_end_time = date('Y-m-d H:i:s', $cal_end_time);

                                    //End time - Start time
                                    $dteStart = new DateTime($cal_start_time);
                                    //$dteStart->add(new DateInterval('PT30M'));
                                    $dteEnd = new DateTime($cal_end_time);
                                    $dteDiff = $dteStart->diff($dteEnd);


                                    //echo '<br><br>date_diff...'.$dteDiff->format("%R%H").',,,,'.$date_diff = $dteDiff->format("%R%H:%I");
                                    //$over_time_total = $dteDiff->format("%R%H")*3600 + $dteDiff->format("%R%I")*60 + $dteDiff->format("%R%S");
                                    //---------------danushka add this code for overtime round up calculation----------------
                                    $over_time_minutes = intval($dteDiff->format("%R%I"));
                                    $hours = $dteDiff->format("%R%H");

                                    /*  if ($hours < 1) {

                                      if (0 < $over_time_minutes and $over_time_minutes < 30) {
                                      $ot_min = 0;
                                      } elseif (30 <= $over_time_minutes and $over_time_minutes < 45) {
                                      $ot_min = 30;
                                      } elseif (45 <= $over_time_minutes and $over_time_minutes <= 59) {
                                      $ot_min = 45;
                                      }
                                      } else {

                                      if (0 < $over_time_minutes and $over_time_minutes < 15) {
                                      $ot_min = 0;
                                      } elseif (15 <= $over_time_minutes and $over_time_minutes < 30) {
                                      $ot_min = 15;
                                      } elseif (30 <= $over_time_minutes and $over_time_minutes < 45) {
                                      $ot_min = 30;
                                      } elseif (45 <= $over_time_minutes and $over_time_minutes <= 59) {
                                      $ot_min = 45;
                                      //echo 'boom';
                                      }
                                      } */

                                    $over_time_total_temp = $dteDiff->format("%R%H") * 3600 + $over_time_minutes * 60;
//                                    $over_time_total_temp = $dteDiff->format("%R%H") * 3600 + $ot_min * 60;
//                                    echo 'over_time_total_temp::'.$over_time_total_temp;
                                    //---------------danushka add this code for overtime round up calculation----------------


                                    $over_time_total = 0;
                                    if ($trigger_time_data['max_time'] > 0) {
                                        if ($over_time_total_temp < $trigger_time_data['max_time']) {
                                            $over_time_total = $over_time_total_temp;
                                        } else {
                                            $over_time_total = $trigger_time_data['max_time'];
                                        }
                                    } else {
                                        $over_time_total = $over_time_total_temp - $trigger_time_data['trigger_time'];
                                    }
                                } elseif ($schedule_start_time > 0) {

                                    $plf = new PunchListFactory();
                                    $plf->getByUserDateId($this->getUserDateID());
                                    foreach ($plf->rs as $plf_obj) {
                                        $plf->data = (array) $plf_obj;
                                        $plf_obj = $plf;
                                        $inout_time[$plf_obj->getStatus()][] = $plf_obj->getTimeStamp();
                                    }


                                    $cal_start_time = min($inout_time[10]);
                                    $cal_end_time = max($inout_time[20]);


                                    if ($schedule_start_time > $cal_start_time) {
                                        $cal_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                    } else {
                                        $cal_start_time = date('Y-m-d H:i:s', $cal_start_time);
                                    }

                                    $cal_end_time = date('Y-m-d H:i:s', $cal_end_time);

                                    //$schedule_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                    //echo '<br><br>schedule_start_time0...'.$schedule_start_time.'....'.date('Y-m-d H:i:s', $schedule_start_time);
                                    //echo '<br><br>start_start_time0...'.$cal_start_time.'....'.date('Y-m-d H:i:s', $cal_start_time);
                                    //------danushka commented this code for get the start time for ot calculation----
                                    /* if($schedule_start_time>$cal_start_time)
                                      {
                                      $cal_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                      }
                                      else
                                      {
                                      $cal_start_time = date('Y-m-d H:i:s', $cal_start_time);
                                      }
                                     */
                                    //------danushka commented this code for get the start time for ot calculation----
                                    //$cal_start_time = date('Y-m-d H:i:s', $cal_start_time);//----danuhska add this code for above modification
                                    //$cal_end_time = date('Y-m-d H:i:s', $schedule_end_time);


                                    /* echo '<br><br>start_start_time...'.$cal_start_time;
                                      echo '<br><br>end_start_time...'.$cal_end_time;
                                      echo '<br><br>schedule_start_time...'.date('Y-m-d H:i:s', $schedule_start_time);
                                      echo '<br><br>schedule_total_time....'.gmdate("H:i:s", $schedule_total_time); */

                                    //End time - (Start time + Schedule total time)
                                    //Add Start time and Schedule total time
                                    $date_added = new DateTime($cal_start_time);
                                    //$date_added = new DateTime($schedule_start_time);
                                    //$date_added->add(new DateInterval('PT'.gmdate("H", $schedule_total_time).'H'.gmdate("i", $schedule_total_time).'M'.gmdate("s", $schedule_total_time).'S'));
                                    //echo $date_added->format('Y-m-d H:i:s');
                                    //Add another 30mins
                                    $date_added->add(new DateInterval('PT30M'));

                                    $strtotime_date_added = strtotime($date_added->format('Y-m-d H:i:s'));
                                    $strtotime_cal_end_time = strtotime($cal_end_time);


                                    /* $dteStart = new DateTime($date_added->format('Y-m-d H:i:s'));
                                      $dteEnd   = new DateTime($cal_end_time);

                                      $dteDiff  = $dteStart->diff($dteEnd);

                                      $over_time_total = $dteDiff->format("%R%H")*3600+$dteDiff->format("%R%I")*60+$dteDiff->format("%R%S"); */

                                    /* echo '<br><br>strtotime_date_added...'.$strtotime_date_added;
                                      echo '<br><br>strtotime_cal_end_time...'.$strtotime_cal_end_time;
                                      echo '<br><br>strtotime_date_added...'.date('Y-m-d H:i:s',$strtotime_date_added);
                                      echo '<br><br>strtotime_cal_end_time...'.date('Y-m-d H:i:s',$strtotime_cal_end_time);
                                      die; */

//                                    echo '<br> strtotime_date_added' . date('Y-m-d H:i:s', $strtotime_date_added);
//                                    echo '<br> strtotime_cal_end_time' . date('Y-m-d H:i:s', $strtotime_cal_end_time);
                                    //OT will be calculated only if work for more than or equal 30mins	
                                    if ($strtotime_date_added < $strtotime_cal_end_time) {
                                        //Add Start time and Schedule total time
                                        //$date_shcdl_added = new DateTime($cal_start_time);
                                        //$date_shcdl_added = new DateTime(date('Y-m-d H:i:s', $schedule_end_time));
                                        //$date_shcdl_added->add(new DateInterval('PT'.gmdate("H", $schedule_total_time).'H'.gmdate("i", $schedule_total_time).'M'.gmdate("s", $schedule_total_time).'S'));
                                        //$dteStart = new DateTime($date_shcdl_added->format('Y-m-d H:i:s'));
                                        $dteStart = new DateTime(date('Y-m-d H:i:s', $schedule_end_time));
                                        $dteEnd = new DateTime($cal_end_time);

                                        $dteDiff = $dteStart->diff($dteEnd);

                                        //$over_time_total = $dteDiff->format("%R%H")*3600+$dteDiff->format("%R%I")*60+$dteDiff->format("%R%S");
                                        //---------------danushka add this code for overtime round up calculation----------------
                                        $over_time_minutes = intval($dteDiff->format("%R%I"));

                                        $hours = $dteDiff->format("%R%H");

                                        /* if ($hours < 1) {

                                          if (0 < $over_time_minutes and $over_time_minutes < 30) {
                                          $ot_min = 0;
                                          } elseif (30 <= $over_time_minutes and $over_time_minutes < 45) {
                                          $ot_min = 30;
                                          } elseif (45 <= $over_time_minutes and $over_time_minutes <= 59) {
                                          $ot_min = 45;
                                          }
                                          } else {

                                          if (0 < $over_time_minutes and $over_time_minutes < 15) {
                                          $ot_min = 0;
                                          } elseif (15 <= $over_time_minutes and $over_time_minutes < 30) {
                                          $ot_min = 15;
                                          } elseif (30 <= $over_time_minutes and $over_time_minutes < 45) {
                                          $ot_min = 30;
                                          } elseif (45 <= $over_time_minutes and $over_time_minutes <= 59) {
                                          $ot_min = 45;
                                          //echo 'boom';
                                          }
                                          } */

                                        // $over_time_total_temp = $dteDiff->format("%R%H") * 3600 + $ot_min * 60;
                                        $over_time_total_temp = $dteDiff->format("%R%H") * 3600 + $over_time_minutes * 60;
                                        //---------------danushka add this code for overtime round up calculation----------------
                                        $over_time_total = 0;
                                        if ($trigger_time_data['max_time'] > 0) {
                                            if ($over_time_total_temp < $trigger_time_data['max_time']) {
                                                $over_time_total = $over_time_total_temp;
                                            } else {
                                                $over_time_total = $trigger_time_data['max_time'];
                                            }
                                        } else {
                                            $over_time_total = $over_time_total_temp;
                                        }
                                    } else {
                                        $over_time_total = 0;
                                    }

                                    //echo '<br><br>date_diff...'.$dteDiff->format("%R%H").',,,,'.$date_diff = $dteDiff->format("%R%H:%I");
                                    //echo '<br>over_time_total....'.$over_time_total;
                                } elseif ($schedule_total_time == 0) {


                                    //echo '<br>schedule_total_time<0';
                                    //print_r($trigger_time_arr);

                                    $plf = new PunchListFactory();
                                    $plf->getByUserDateId($this->getUserDateID());
                                    foreach ($plf->rs as $plf_obj) {
                                        $plf->data = (array) $plf_obj;
                                        $plf_obj = $plf;
                                        $inout_time[$plf_obj->getStatus()][] = $plf_obj->getTimeStamp();
                                    }

                                    //print_r($inout_time); die;

                                    $cal_start_time = min($inout_time[10]);
                                    $cal_end_time = max($inout_time[20]);

                                    //$schedule_start_time = date('Y-m-d H:i:s', $schedule_start_time);
                                    //echo '<br><br>schedule_start_time0...'.$schedule_start_time.'....'.date('Y-m-d H:i:s', $schedule_start_time);
                                    //echo '<br><br>start_start_time0...'.$cal_start_time.'....'.date('Y-m-d H:i:s', $cal_start_time);

                                    $date_time = new DateTime();
                                    $date_time->setTimestamp($cal_start_time);

                                    $string_date = $date_time->format('Y-m-d') . ' 16:45:00';



                                    $date_created = DateTime::createFromFormat('Y-m-d H:i:s', $string_date);

                                    $day_name = $date_created->format("l");

                                    //  echo ' '.$date_created->format('Y-m-d H:i:s');
                                    // exit();

                                    $cal_start_time = date('Y-m-d H:i:s', $cal_start_time);
                                    $cal_end_time = date('Y-m-d H:i:s', $cal_end_time);

                                    $hlf = new HolidayListFactory();



                                    //End time - Start time
                                    $dteStart = new DateTime($cal_start_time);
                                    //$dteStart->add(new DateInterval('PT30M'));
                                    $dteEnd = new DateTime($cal_end_time);

                                    $week_end_ot = FALSE;

                                    if ($day_name == 'Saturday' || $day_name == 'Sunday') {
                                        $dteDiff = $dteStart->diff($dteEnd);
                                        $week_end_ot = TRUE;
                                    } else {
                                        $dteDiff = $date_created->diff($dteEnd);
                                    }

                                    //echo '<br><br>date_diff...'.$dteDiff->format("%R%H").',,,,'.$date_diff = $dteDiff->format("%R%H:%I");
                                    //$over_time_total = $dteDiff->format("%R%H")*3600+$dteDiff->format("%R%I")*60+$dteDiff->format("%R%S");
                                    //---------------danushka add this code for overtime round up calculation----------------
                                    $over_time_minutes = intval($dteDiff->format("%R%I"));
                                    $hours = $dteDiff->format("%R%H");


                                    /* if ($hours < 1) {

                                      if (0 < $over_time_minutes and $over_time_minutes < 30) {
                                      $ot_min = 0;
                                      } elseif (30 <= $over_time_minutes and $over_time_minutes < 45) {
                                      $ot_min = 30;
                                      } elseif (45 <= $over_time_minutes and $over_time_minutes <= 59) {
                                      $ot_min = 45;
                                      }
                                      } else {

                                      if (0 < $over_time_minutes and $over_time_minutes < 15) {
                                      $ot_min = 0;
                                      } elseif (15 <= $over_time_minutes and $over_time_minutes < 30) {
                                      $ot_min = 15;
                                      } elseif (30 <= $over_time_minutes and $over_time_minutes < 45) {
                                      $ot_min = 30;
                                      } elseif (45 <= $over_time_minutes and $over_time_minutes <= 59) {
                                      $ot_min = 45;
                                      //echo 'boom';
                                      }
                                      } */

                                    $over_time_total_temp = $dteDiff->format("%R%H") * 3600 + $over_time_minutes * 60;
                                    //$over_time_total_temp = $dteDiff->format("%R%H") * 3600 + $ot_min * 60;
                                    //---------------danushka add this code for overtime round up calculation----------------
                                    /*
                                      if(($over_time_total >7200) AND !$week_end_ot ){

                                      $over_time_total=7200;
                                      }
                                     */

                                    $over_time_total = 0;
                                    if ($trigger_time_data['max_time'] > 0) {
                                        if ($over_time_total_temp < $trigger_time_data['max_time']) {
                                            $over_time_total = $over_time_total_temp;
                                        } else {
                                            $over_time_total = $trigger_time_data['max_time'];
                                        }
                                    } else {
                                        $over_time_total = $over_time_total_temp - $trigger_time_data['trigger_time'];
                                    }
                                }



                                //If date difference is minus value set over time to 0
                                if ($over_time_total < 0) {
                                    $over_time_total = 0;
                                }
                            } else {

                                $over_time_total_temp = $total_daily_hours - $total_daily_hours_used;
                                //echo 'over_time_total2...'.$over_time_total;
                                //echo '<br><br>max_trigger_time...'.$max_trigger_time;
//                                if (isset($trigger_time_arr[$i + 1]['trigger_time'])
//                                        AND $max_trigger_time > 0
//                                        AND $over_time_total > $max_trigger_time) {
//                                    $over_time_total = $max_trigger_time;
//                                }

                                if ($trigger_time_data['max_time'] > 0) {
                                    if ($over_time_total_temp > $trigger_time_data['max_time']) {
                                        $over_time_total = $trigger_time_data['max_time'];
                                    } else {
                                        $over_time_total = $over_time_total_temp;
                                    }
                                } else {
                                    $over_time_total = $over_time_total_temp;
                                }
                            }

                            //echo '<br><br><br>Final Total over time...'.$over_time_total.'<br><br><br><br><br><br>';

                            if ($over_time_total > 0) {

                                $over_time_quantity_percent = bcdiv($over_time_total, $udt_obj->getTotalTime());

                                $over_time_quantity = round(bcmul($udt_obj->getQuantity(), $over_time_quantity_percent), 2);

                                $over_time_bad_quantity = round(bcmul($udt_obj->getBadQuantity(), $over_time_quantity_percent), 2);

                                Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; Inserting Hours (' . $over_time_total . ') for Policy ID: ' . $trigger_time_arr[$i]['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);

                                if (isset($user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()])) {

                                    Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; Adding to Compact Array: Policy ID: ' . $trigger_time_arr[$i]['over_time_policy_id'] . ' Branch: ' . (int) $udt_obj->getBranch() . ' Department: ' . (int) $udt_obj->getDepartment(), __FILE__, __LINE__, __METHOD__, 10);

                                    $user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['total_time'] += $over_time_total;

                                    $user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['quantity'] += $over_time_quantity;

                                    $user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['bad_quantity'] += $over_time_bad_quantity;
                                } else {
                                    Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; Initiating Compact Sub-Array: Policy ID: ' . $trigger_time_arr[$i]['over_time_policy_id'] . ' Branch: ' . (int) $udt_obj->getBranch() . ' Department: ' . (int) $udt_obj->getDepartment(), __FILE__, __LINE__, __METHOD__, 10);

                                    $user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()] = array('total_time' => $over_time_total, 'quantity' => $over_time_quantity, 'bad_quantity' => $over_time_bad_quantity);
                                }

                                //echo '<br>'.$over_time_total;

                                $total_daily_hours_used += $over_time_total;
                            } else {
                                Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; Over Time Total is 0: ' . $over_time_total, __FILE__, __LINE__, __METHOD__, 10);
                            }

                            unset($over_time_total, $over_time_quantity_percent, $over_time_quantity, $over_time_bad_quantity);
                        } else {
                            break;
                        }

                        $i++;
                    }

                    unset($udt_total_time);
                }
                unset($tmp_policy_total_time, $trigger_time_data, $trigger_time_arr);
            }
        }

        //echo '<pre><br><br>user_data_total_compact_arr...';print_r($user_data_total_compact_arr);


        $profiler->stopTimer('UserDateTotal::calcOverTimePolicyTotalTime() - Part 1');

        if (isset($user_data_total_compact_arr)) {
            return $user_data_total_compact_arr;
        }

        return FALSE;
    }

    //Take all punches for a given day, take into account the minimum time between shifts,
    //and return an array of shifts, with their start/end and total time calculated.
    function getShiftDataByUserDateID($user_date_id = NULL) {
        if ($user_date_id == '') {
            $user_date_id = $this->getUserDateObject()->getId();
        }

        $new_shift_trigger_time = 3600 * 4; //Default to 8hrs
        if (is_object($this->getUserDateObject()->getPayPeriodObject())
                AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject())) {
            $new_shift_trigger_time = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getNewDayTriggerTime();
        }

        $plf = new PunchListFactory();
        $plf->getByUserDateId($user_date_id);
        if ($plf->getRecordCount() > 0) {
            $shift = 0;
            $i = 0;
            foreach ($plf->rs as $p_obj) {
                $plf->data = (array) $p_obj;
                $p_obj = $plf;
                $total_time = $p_obj->getPunchControlObject()->getTotalTime();

                if ($total_time == 0) {
                    continue;
                }

                if ($i > 0 AND isset($shift_data[$shift]['last_out'])
                        AND $p_obj->getStatus() == 10) {
                    Debug::text('Checking for new shift...', __FILE__, __LINE__, __METHOD__, 10);
                    if (($p_obj->getTimeStamp() - $shift_data[$shift]['last_out']) > $new_shift_trigger_time) {
                        $shift++;
                    }
                }

                if (!isset($shift_data[$shift]['total_time'])) {
                    $shift_data[$shift]['total_time'] = 0;
                }

                $shift_data[$shift]['punches'][] = $p_obj->getTimeStamp();
                if (!isset($shift_data[$shift]['first_in']) AND $p_obj->getStatus() == 10) {
                    $shift_data[$shift]['first_in'] = $p_obj->getTimeStamp();
                } elseif ($p_obj->getStatus() == 20) {
                    $shift_data[$shift]['last_out'] = $p_obj->getTimeStamp();
                    $shift_data[$shift]['total_time'] += $total_time;
                }

                $i++;
            }

            if (isset($shift_data)) {
                return $shift_data;
            }
        }

        return FALSE;
    }

    function calcPremiumPolicyTotalTime($udt_meal_policy_adjustment_arr, $udt_break_policy_adjustment_arr, $daily_total_time = FALSE) {
        global $profiler;

        $profiler->startTimer('UserDateTotal::calcPremiumPolicyTotalTime() - Part 1');

        if (empty($daily_total_time) || $daily_total_time === FALSE) {
            $daily_total_time = $this->getDailyTotalTime();
        }

        $pplf = new PremiumPolicyListFactory();
        $pplf->getByPolicyGroupUserId($this->getUserDateObject()->getUser());
        if ($pplf->getRecordCount() > 0) {
            Debug::text('Found Premium Policies to apply.', __FILE__, __LINE__, __METHOD__, 10);

            foreach ($pplf->rs as $pp_obj) {
                $pplf->data = (array) $pp_obj;
                $pp_obj = $pplf;
                Debug::text('Found Premium Policy: ID: ' . $pp_obj->getId() . ' Type: ' . $pp_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);




                //FIXME: Support manually setting a premium policy through the Edit Hours page?
                //In those cases, just skip auto-calculating it and accept it?
                switch ($pp_obj->getType()) {
                    case 10: //Date/Time
                        Debug::text(' Date/Time Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

                        //Make sure this is a valid day
                        //Take into account shifts that span midnight though, where one half of the shift is eligilble for premium time.
                        //ie: Premium Policy starts 7AM to 7PM on Sat/Sun. Punches in at 9PM Friday and out at 9AM Sat, we need to check if both days are valid.
                        //FIXME: Handle shifts that are longer than 24hrs in length.
                        if ($pp_obj->isActive($this->getUserDateObject()->getDateStamp() - 86400, $this->getUserDateObject()->getDateStamp() + 86400)) {
                            Debug::text(' Premium Policy Is Active On OR Around This Day.', __FILE__, __LINE__, __METHOD__, 10);


                            $total_daily_time_used = 0;
                            $daily_trigger_time = 0;

                            $udtlf = new UserDateTotalListFactory();

                            if ($pp_obj->isHourRestricted() == TRUE) {



                                if ($pp_obj->getWeeklyTriggerTime() > 0) {
                                    //Get Pay Period Schedule info
                                    if (is_object($this->getUserDateObject()->getPayPeriodObject())
                                            AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject())) {
                                        $start_week_day_id = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
                                    } else {
                                        $start_week_day_id = 0;
                                    }
                                    Debug::text('Start Week Day ID: ' . $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

                                    $weekly_total_time = $udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id));
                                    if ($weekly_total_time > $pp_obj->getWeeklyTriggerTime()) {
                                        $daily_trigger_time = 0;
                                    } else {
                                        $daily_trigger_time = $pp_obj->getWeeklyTriggerTime() - $weekly_total_time;
                                    }
                                    Debug::text(' Weekly Trigger Time: ' . $daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                                }

                                if ($pp_obj->getDailyTriggerTime() > 0 AND $pp_obj->getDailyTriggerTime() > $daily_trigger_time) {
                                    $daily_trigger_time = $pp_obj->getDailyTriggerTime();
                                }
                            }


                            Debug::text(' Daily Trigger Time: ' . $daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

                            //Loop through all worked (status: 20) UserDateTotalRows
                            $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20);
                            $i = 1;
                            if ($udtlf->getRecordCount() > 0) {
                                Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                                $valid_user_date_total_ids = array();
                                foreach ($udtlf->rs as $udt_obj) {
                                    $udtlf->data = (array) $udt_obj;
                                    $udt_obj = $udtlf;
                                    Debug::text('UserDateTotal ID: ' . $udt_obj->getID() . ' Total Time: ' . $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);



                                    //Ignore incomplete punches
                                    if ($udt_obj->getTotalTime() == 0) {
                                        continue;
                                    }

                                    //How do we handle actual shifts for premium time?
                                    //So if premium policy starts at 1PM for shifts, to not
                                    //include employees who return from lunch at 1:30PM.
                                    //Create a function that takes all punches for a day, and returns
                                    //the first in and last out time for a given shift when taking
                                    //into account minimum time between shifts, as well as the total time for that shift.
                                    //We can then use that time for ActiveTime on premium policies, and determine if a
                                    //punch falls within the active time, then we add it to the total.
                                    if ($pp_obj->isTimeRestricted() == TRUE AND $udt_obj->getPunchControlID() != FALSE) {
                                        Debug::text('Time Restricted Premium Policy, lookup punches to get times.', __FILE__, __LINE__, __METHOD__, 10);

                                        if ($pp_obj->getIncludePartialPunch() == FALSE) {
                                            $shift_data = $this->getShiftDataByUserDateID($this->getUserDateID());
                                        }

                                        $plf = new PunchListFactory();
                                        $plf->getByPunchControlId($udt_obj->getPunchControlID());
                                        if ($plf->getRecordCount() > 0) {
                                            Debug::text('Found Punches: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
                                            foreach ($plf->rs as $punch_obj) {
                                                $plf->data = (array) $punch_obj;
                                                $punch_obj = $plf;
                                                if ($pp_obj->getIncludePartialPunch() == TRUE) {
                                                    //Debug::text('Including Partial Punches...', __FILE__, __LINE__, __METHOD__, 10);

                                                    if ($punch_obj->getStatus() == 10) {
                                                        $punch_times['in'] = $punch_obj->getTimeStamp();
                                                    } elseif ($punch_obj->getStatus() == 20) {
                                                        $punch_times['out'] = $punch_obj->getTimeStamp();
                                                    }
                                                } else {
                                                    if (isset($shift_data) AND is_array($shift_data)) {
                                                        foreach ($shift_data as $shift) {
                                                            if ($punch_obj->getTimeStamp() >= $shift['first_in']
                                                                    AND $punch_obj->getTimeStamp() <= $shift['last_out']) {
                                                                //Debug::Arr($shift,'Shift Data...', __FILE__, __LINE__, __METHOD__, 10);
                                                                Debug::text('Punch (' . TTDate::getDate('DATE+TIME', $punch_obj->getTimeStamp()) . ') inside shift time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                $punch_times['in'] = $shift['first_in'];
                                                                $punch_times['out'] = $shift['last_out'];
                                                                break;
                                                            } else {
                                                                Debug::text('Punch (' . TTDate::getDate('DATE+TIME', $punch_obj->getTimeStamp()) . ') outside shift time...', __FILE__, __LINE__, __METHOD__, 10);
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            if (isset($punch_times) AND count($punch_times) == 2
                                                    AND $pp_obj->isActiveDate($this->getUserDateObject()->getDateStamp()) == TRUE
                                                    AND $pp_obj->isActiveTime($punch_times['in'], $punch_times['out']) == TRUE) {
                                                //Debug::Arr($punch_times, 'Punch Times: ', __FILE__, __LINE__, __METHOD__, 10);
                                                $punch_total_time = $pp_obj->getPartialPunchTotalTime($punch_times['in'], $punch_times['out'], $udt_obj->getTotalTime());
                                                $valid_user_date_total_ids[] = $udt_obj->getID(); //Need to record punches that fall within the active time so we can properly handle break/meal adjustments.
                                                Debug::text('Valid Punch pair in active time, Partial Punch Total Time: ' . $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);
                                            } else {
                                                Debug::text('InValid Punch Pair or outside Active Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                $punch_total_time = 0;
                                            }
                                        }
                                    } elseif ($pp_obj->isActive($udt_obj->getUserDateObject()->getDateStamp()) == TRUE) {
                                        $punch_total_time = $udt_obj->getTotalTime();
                                        $valid_user_date_total_ids[] = $udt_obj->getID();
                                    } else {
                                        $punch_total_time = 0;
                                    }

                                    //Why is $tmp_punch_total_time not just $punch_total_time? Are the partial punches somehow separate from the meal/break calculation?
                                    //Yes, because tmp_punch_total_time is the DAILY total time used, whereas punch_total_time can be a partial shift. Without this the daily trigger time won't work.
                                    $tmp_punch_total_time = $udt_obj->getTotalTime();
                                    Debug::text('aPunch Total Time: ' . $punch_total_time . ' TMP Punch Total Time: ' . $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

                                    //When calculating meal/break policy adjustments, make sure they can be added to one another, in case there is a meal AND break
                                    //within the same shift, they both need to be included. Also make sure we double check the active date again.
                                    //Apply meal policy adjustment as early as possible.
                                    if ($pp_obj->getIncludeMealPolicy() == TRUE
                                            AND $pp_obj->isActiveDate($this->getUserDateObject()->getDateStamp()) == TRUE
                                            AND isset($udt_meal_policy_adjustment_arr[$udt_obj->getId()])
                                            AND in_array($udt_obj->getID(), $valid_user_date_total_ids)) {
                                        Debug::text(' Meal Policy Adjustment Found: ' . $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                        $punch_total_time = bcadd($punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                                        $tmp_punch_total_time = bcadd($tmp_punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                                    }
                                    Debug::text('bPunch Total Time: ' . $punch_total_time . ' TMP Punch Total Time: ' . $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

                                    //Apply break policy adjustment as early as possible.
                                    if ($pp_obj->getIncludeBreakPolicy() == TRUE
                                            AND $pp_obj->isActiveDate($this->getUserDateObject()->getDateStamp()) == TRUE
                                            AND isset($udt_break_policy_adjustment_arr[$udt_obj->getId()])
                                            AND in_array($udt_obj->getID(), $valid_user_date_total_ids)) {
                                        Debug::text(' Break Policy Adjustment Found: ' . $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                        $punch_total_time = bcadd($punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                                        $tmp_punch_total_time = bcadd($tmp_punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                                    }
                                    Debug::text('cPunch Total Time: ' . $punch_total_time . ' TMP Punch Total Time: ' . $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

                                    $total_daily_time_used += $tmp_punch_total_time;
                                    Debug::text('Daily Total Time Used: ' . $total_daily_time_used, __FILE__, __LINE__, __METHOD__, 10);





                                    //FIXME: Should the daily/weekly trigger time be >= instead of >.
                                    //That way if the policy is active after 7.5hrs, punch time of exactly 7.5hrs will still
                                    //activate the policy, rather then requiring 7.501hrs+
                                    if ($punch_total_time > 0 AND $total_daily_time_used > $daily_trigger_time) {
                                        Debug::text('Past Trigger Time!!', __FILE__, __LINE__, __METHOD__, 10);

                                        $total_daily_time_used = $total_daily_time_used - ($total_daily_time_used % 900);

                                        //Calculate how far past trigger time we are.
                                        $past_trigger_time = $total_daily_time_used - $daily_trigger_time;


                                        if ($punch_total_time > $past_trigger_time) {
                                            $punch_total_time = $past_trigger_time;
                                            Debug::text('Using Past Trigger Time as punch total time: ' . $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                                        } else {
                                            Debug::text('NOT Using Past Trigger Time as punch total time: ' . $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                                        }

                                        $total_time = $punch_total_time;




                                        if ($pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0) {
                                            $premium_policy_daily_total_time = (int) $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID($this->getUserDateID(), $pp_obj->getId());


                                            Debug::text(' Premium Policy Daily Total Time: ' . $premium_policy_daily_total_time . ' Minimum Time: ' . $pp_obj->getMinimumTime() . ' Maximum Time: ' . $pp_obj->getMaximumTime() . ' Total Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                            if ($pp_obj->getMinimumTime() > 0) {
                                                //FIXME: Split the minimum time up between all the punches somehow.
                                                //Apply the minimum time on the last punch, otherwise if there are two punch pairs of 15min each
                                                //and a 1hr minimum time, if the minimum time is applied to the first, it will be 1hr and 15min
                                                //for the day. If its applied to the last it will be just 1hr.
                                                //Min & Max time is based on the shift time, rather then per punch pair time.
                                                //FIXME: If there is a minimum time set to say 9hrs, and the punches go like this:
                                                // In: 7:00AM Out: 3:00:PM, Out: 3:30PM (missing 2nd In Punch), the minimum time won't be calculated due to the invalid punch pair.

                                                $time_premium = bcsub($premium_policy_daily_total_time, $total_time);

                                                if (($i == $udtlf->getRecordCount()) AND ( $time_premium > $pp_obj->getMinimumTime())) {
                                                    $total_time = bcsub($pp_obj->getMinimumTime(), $premium_policy_daily_total_time);
                                                }
                                            }

                                            Debug::text(' Total Time After Minimum is applied: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                            if ($pp_obj->getMaximumTime() > 0) {
                                                //Min & Max time is based on the shift time, rather then per punch pair time.

                                                $premium_time2 = bcadd($premium_policy_daily_total_time, $total_time);



                                                if ($premium_time2 > $pp_obj->getMaximumTime()) {
                                                    Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                    $total_time = bcsub($total_time, bcsub(bcadd($premium_policy_daily_total_time, $total_time), $pp_obj->getMaximumTime()));
                                                }
                                            }
                                        }

                                        if ($total_time < $pp_obj->getMinimumTime()) {
                                            $total_time = 0;
                                        }



                                        Debug::text(' Premium Punch Total Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                        if ($total_time > 0) {
                                            Debug::text(' Applying  Premium Time!: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                            $udtf = new UserDateTotalFactory();
                                            $udtf->setUserDateID($this->getUserDateID());
                                            $udtf->setStatus(10); //System
                                            $udtf->setType(40); //Premium
                                            $udtf->setPremiumPolicyId($pp_obj->getId());
                                            $udtf->setBranch($udt_obj->getBranch());
                                            $udtf->setDepartment($udt_obj->getDepartment());
                                            $udtf->setJob($udt_obj->getJob());
                                            $udtf->setJobItem($udt_obj->getJobItem());

                                            $udtf->setQuantity($udt_obj->getQuantity());
                                            $udtf->setBadQuantity($udt_obj->getBadQuantity());

                                            $udtf->setTotalTime($total_time);
                                            $udtf->setEnableCalcSystemTotalTime(FALSE);
                                            if ($udtf->isValid() == TRUE) {
                                                $udtf->Save();
                                            }
                                            unset($udtf);
                                        } else {
                                            Debug::text(' Premium Punch Total Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
                                        }
                                    } else {
                                        Debug::text('Not Past Trigger Time Yet or Punch Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
                                    }

                                    $i++;
                                }
                                unset($valid_user_date_total_ids);
                            }
                        }
                        break;
                    case 20: //Differential
                        Debug::text(' Differential Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

                        //Loop through all worked (status: 20) UserDateTotalRows
                        $udtlf = new UserDateTotalListFactory();
                        $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20);
                        if ($udtlf->getRecordCount() > 0) {
                            Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                            foreach ($udtlf->rs as $udt_obj) {
                                $udtlf->data = (array) $udt_obj;
                                $udt_obj = $udtlf;
                                //Ignore incomplete punches
                                if ($udt_obj->getTotalTime() == 0) {
                                    continue;
                                }

                                if (( $pp_obj->getBranchSelectionType() == 10
                                        AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
                                        OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
                                        AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) ) )

                                        OR ( $pp_obj->getBranchSelectionType() == 20
                                        AND in_array($udt_obj->getBranch(), (array) $pp_obj->getBranch()) )
                                        AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
                                        OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
                                        AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )

                                        OR ( $pp_obj->getBranchSelectionType() == 30
                                        AND ! in_array($udt_obj->getBranch(), (array) $pp_obj->getBranch()) )
                                        AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
                                        OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
                                        AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )
                                ) {
                                    Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: ' . $pp_obj->getBranchSelectionType() . ' Exclude Default Branch: ' . (int) $pp_obj->getExcludeDefaultBranch() . ' Default Branch: ' . $this->getUserDateObject()->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);

                                    if (( $pp_obj->getDepartmentSelectionType() == 10
                                            AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
                                            OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
                                            AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) ) )

                                            OR ( $pp_obj->getDepartmentSelectionType() == 20
                                            AND in_array($udt_obj->getDepartment(), (array) $pp_obj->getDepartment()) )
                                            AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
                                            OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
                                            AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )

                                            OR ( $pp_obj->getDepartmentSelectionType() == 30
                                            AND ! in_array($udt_obj->getDepartment(), (array) $pp_obj->getDepartment()) )
                                            AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
                                            OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
                                            AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )
                                    ) {
                                        Debug::text(' Shift Differential... Meets Department Criteria! Select Type: ' . $pp_obj->getDepartmentSelectionType() . ' Exclude Default Department: ' . (int) $pp_obj->getExcludeDefaultDepartment() . ' Default Department: ' . $this->getUserDateObject()->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);


                                        if ($pp_obj->getJobGroupSelectionType() == 10
                                                OR ( $pp_obj->getJobGroupSelectionType() == 20
                                                AND ( is_object($udt_obj->getJobObject()) AND in_array($udt_obj->getJobObject()->getGroup(), (array) $pp_obj->getJobGroup()) ) )
                                                OR ( $pp_obj->getJobGroupSelectionType() == 30
                                                AND ( is_object($udt_obj->getJobObject()) AND ! in_array($udt_obj->getJobObject()->getGroup(), (array) $pp_obj->getJobGroup()) ) )
                                        ) {
                                            Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: ' . $pp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

                                            if ($pp_obj->getJobSelectionType() == 10
                                                    OR ( $pp_obj->getJobSelectionType() == 20
                                                    AND in_array($udt_obj->getJob(), (array) $pp_obj->getJob()) )
                                                    OR ( $pp_obj->getJobSelectionType() == 30
                                                    AND ! in_array($udt_obj->getJob(), (array) $pp_obj->getJob()) )
                                            ) {
                                                Debug::text(' Shift Differential... Meets Job Criteria! Select Type: ' . $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

                                                if ($pp_obj->getJobItemGroupSelectionType() == 10
                                                        OR ( $pp_obj->getJobItemGroupSelectionType() == 20
                                                        AND ( is_object($udt_obj->getJobItemObject()) AND in_array($udt_obj->getJobItemObject()->getGroup(), (array) $pp_obj->getJobItemGroup()) ) )
                                                        OR ( $pp_obj->getJobItemGroupSelectionType() == 30
                                                        AND ( is_object($udt_obj->getJobItemObject()) AND ! in_array($udt_obj->getJobItemObject()->getGroup(), (array) $pp_obj->getJobItemGroup()) ) )
                                                ) {
                                                    Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: ' . $pp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

                                                    if ($pp_obj->getJobItemSelectionType() == 10
                                                            OR ( $pp_obj->getJobItemSelectionType() == 20
                                                            AND in_array($udt_obj->getJobItem(), (array) $pp_obj->getJobItem()) )
                                                            OR ( $pp_obj->getJobItemSelectionType() == 30
                                                            AND ! in_array($udt_obj->getJobItem(), (array) $pp_obj->getJobItem()) )
                                                    ) {
                                                        Debug::text(' Shift Differential... Meets Task Criteria! Select Type: ' . $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

                                                        $premium_policy_daily_total_time = 0;
                                                        $punch_total_time = $udt_obj->getTotalTime();
                                                        $total_time = 0;

                                                        //Apply meal policy adjustment BEFORE min/max times
                                                        if ($pp_obj->getIncludeMealPolicy() == TRUE AND isset($udt_meal_policy_adjustment_arr[$udt_obj->getId()])) {
                                                            Debug::text(' Meal Policy Adjustment Found: ' . $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                                            $punch_total_time = bcadd($punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                                                        }
                                                        if ($pp_obj->getIncludeBreakPolicy() == TRUE AND isset($udt_break_policy_adjustment_arr[$udt_obj->getId()])) {
                                                            Debug::text(' Break Policy Adjustment Found: ' . $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                                            $punch_total_time = bcadd($punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                                                        }

                                                        if ($pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0) {
                                                            $premium_policy_daily_total_time = $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID($this->getUserDateID(), $pp_obj->getId());
                                                            Debug::text(' Premium Policy Daily Total Time: ' . $premium_policy_daily_total_time . ' Minimum Time: ' . $pp_obj->getMinimumTime() . ' Maximum Time: ' . $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

                                                            if ($pp_obj->getMinimumTime() > 0) {
                                                                if ($daily_total_time < $pp_obj->getMinimumTime()) {
                                                                    //Split the minimum time up between all punches
                                                                    //We only get IN punches, so we don't need to divide $total_punches by 2.
                                                                    //This won't calculate the proper amount if punches aren't paired, but everything
                                                                    //is broken then anyways.
                                                                    $total_time = bcdiv($pp_obj->getMinimumTime(), $udtlf->getRecordCount());
                                                                    Debug::text(' Daily Total Time is less the Minimum, using: ' . $total_time . ' Total Punches: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
                                                                } else {
                                                                    Debug::text(' Daily Total is more then minimum...', __FILE__, __LINE__, __METHOD__, 10);
                                                                    $total_time = $punch_total_time;
                                                                }
                                                            } else {
                                                                $total_time = $punch_total_time;
                                                            }

                                                            Debug::text(' Total Time After Minimum is applied: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                                            if ($pp_obj->getMaximumTime() > 0) {
                                                                if ($total_time > $pp_obj->getMaximumTime()) {
                                                                    Debug::text(' aMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                    $total_time = $pp_obj->getMaximumTime();
                                                                } elseif (bcadd($premium_policy_daily_total_time, $total_time) > $pp_obj->getMaximumTime()) {
                                                                    Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                    $total_time = bcsub(bcadd($premium_policy_daily_total_time, $total_time), $pp_obj->getMaximumTime());
                                                                }
                                                            }
                                                        } else {
                                                            $total_time = $punch_total_time;
                                                        }

                                                        Debug::text(' Premium Punch Total Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                                        if ($total_time > 0) {
                                                            Debug::text(' Applying  Premium Time!: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                            $udtf = new UserDateTotalFactory();
                                                            $udtf->setUserDateID($this->getUserDateID());
                                                            $udtf->setStatus(10); //System
                                                            $udtf->setType(40); //Premium
                                                            $udtf->setPremiumPolicyId($pp_obj->getId());
                                                            $udtf->setBranch($udt_obj->getBranch());
                                                            $udtf->setDepartment($udt_obj->getDepartment());
                                                            $udtf->setJob($udt_obj->getJob());
                                                            $udtf->setJobItem($udt_obj->getJobItem());

                                                            $udtf->setQuantity($udt_obj->getQuantity());
                                                            $udtf->setBadQuantity($udt_obj->getBadQuantity());

                                                            $udtf->setTotalTime($total_time);
                                                            $udtf->setEnableCalcSystemTotalTime(FALSE);
                                                            if ($udtf->isValid() == TRUE) {
                                                                $udtf->Save();
                                                            }
                                                            unset($udtf);
                                                        } else {
                                                            Debug::text(' Premium Punch Total Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
                                                        }
                                                    } else {
                                                        Debug::text(' Shift Differential... DOES NOT Meet Task Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                                    }
                                                } else {
                                                    Debug::text(' Shift Differential... DOES NOT Meet Task Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                                }
                                            } else {
                                                Debug::text(' Shift Differential... DOES NOT Meet Job Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                            }
                                        } else {
                                            Debug::text(' Shift Differential... DOES NOT Meet Job Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                        }
                                    } else {
                                        Debug::text(' Shift Differential... DOES NOT Meet Department Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                    }
                                } else {
                                    Debug::text(' Shift Differential... DOES NOT Meet Branch Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                }
                            }
                        }
                        break;
                    case 30: //Meal/Break
                        Debug::text(' Meal/Break Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

                        if ($pp_obj->getDailyTriggerTime() == 0
                                OR ( $pp_obj->getDailyTriggerTime() > 0 AND $daily_total_time >= $pp_obj->getDailyTriggerTime() )) {
                            //Find maximum worked without a break.
                            $plf = new PunchListFactory();
                            $plf->getByUserDateId($this->getUserDateID()); //Get all punches for the day.
                            if ($plf->getRecordCount() > 0) {
                                Debug::text('Found Punches: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
                                foreach ($plf->rs as $p_obj) {
                                    $plf->data = (array) $p_obj;
                                    $p_obj = $plf;
                                    Debug::text('TimeStamp: ' . $p_obj->getTimeStamp() . ' Status: ' . $p_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
                                    $punch_pairs[$p_obj->getPunchControlID()][] = array(
                                        'status_id' => $p_obj->getStatus(),
                                        'punch_control_id' => $p_obj->getPunchControlID(),
                                        'time_stamp' => $p_obj->getTimeStamp()
                                    );
                                }

                                if (isset($punch_pairs)) {
                                    $prev_punch_timestamp = NULL;
                                    $maximum_time_worked_without_break = 0;

                                    foreach ($punch_pairs as $punch_pair) {
                                        if (count($punch_pair) > 1) {
                                            //Total Punch Time
                                            $total_punch_pair_time = $punch_pair[1]['time_stamp'] - $punch_pair[0]['time_stamp'];
                                            $maximum_time_worked_without_break += $total_punch_pair_time;
                                            Debug::text('Total Punch Pair Time: ' . $total_punch_pair_time . ' Maximum No Break Time: ' . $maximum_time_worked_without_break, __FILE__, __LINE__, __METHOD__, 10);

                                            if ($prev_punch_timestamp !== NULL) {
                                                $break_time = $punch_pair[0]['time_stamp'] - $prev_punch_timestamp;
                                                if ($break_time > $pp_obj->getMinimumBreakTime()) {
                                                    Debug::text('Exceeded Minimum Break Time: ' . $break_time . ' Minimum: ' . $pp_obj->getMinimumBreakTime(), __FILE__, __LINE__, __METHOD__, 10);
                                                    $maximum_time_worked_without_break = 0;
                                                }
                                            }

                                            if ($maximum_time_worked_without_break > $pp_obj->getMaximumNoBreakTime()) {
                                                Debug::text('Exceeded maximum no break time!', __FILE__, __LINE__, __METHOD__, 10);

                                                if ($pp_obj->getMaximumTime() > $pp_obj->getMinimumTime()) {
                                                    $total_time = $pp_obj->getMaximumTime();
                                                } else {
                                                    $total_time = $pp_obj->getMinimumTime();
                                                }

                                                if ($total_time > 0) {
                                                    Debug::text(' Applying Meal/Break Premium Time!: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                    //Get Punch Control obj.
                                                    $pclf = new PunchControlListFactory();
                                                    $pclf->getById($punch_pair[0]['punch_control_id']);
                                                    if ($pclf->getRecordCount() > 0) {
                                                        $pc_obj = $pclf->getCurrent();
                                                    }

                                                    $udtf = new UserDateTotalFactory();
                                                    $udtf->setUserDateID($this->getUserDateID());
                                                    $udtf->setStatus(10); //System
                                                    $udtf->setType(40); //Premium
                                                    $udtf->setPremiumPolicyId($pp_obj->getId());

                                                    if (isset($pc_obj) AND is_object($pc_obj)) {
                                                        $udtf->setBranch($pc_obj->getBranch());
                                                        $udtf->setDepartment($pc_obj->getDepartment());
                                                        $udtf->setJob($pc_obj->getJob());
                                                        $udtf->setJobItem($pc_obj->getJobItem());
                                                    }

                                                    $udtf->setTotalTime($total_time);
                                                    $udtf->setEnableCalcSystemTotalTime(FALSE);
                                                    if ($udtf->isValid() == TRUE) {
                                                        $udtf->Save();
                                                    }
                                                    unset($udtf);

                                                    break; //Stop looping through punches.
                                                }
                                            } else {
                                                Debug::text('Did not exceed maximum no break time yet...', __FILE__, __LINE__, __METHOD__, 10);
                                            }

                                            $prev_punch_timestamp = $punch_pair[1]['time_stamp'];
                                        } else {
                                            Debug::text('Found UnPaired Punch, Ignorning...', __FILE__, __LINE__, __METHOD__, 10);
                                        }
                                    }
                                    unset($plf, $punch_pairs, $punch_pair, $prev_punch_timestamp, $maximum_time_worked_without_break, $total_time);
                                }
                            }
                        } else {
                            Debug::text(' Not within Daily Total Time: ' . $daily_total_time . ' Trigger Time: ' . $pp_obj->getDailyTriggerTime(), __FILE__, __LINE__, __METHOD__, 10);
                        }
                        break;
                    case 40: //Callback
                        Debug::text(' Callback Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);
                        Debug::text(' Minimum Time Between Shifts: ' . $pp_obj->getMinimumTimeBetweenShift() . ' Minimum First Shift Time: ' . $pp_obj->getMinimumFirstShiftTime(), __FILE__, __LINE__, __METHOD__, 10);

                        $first_punch_epoch = FALSE;

                        $plf = new PunchListFactory();
                        $plf->getByUserDateId($this->getUserDateID()); //Get all punches for the day.
                        if ($plf->getRecordCount() > 0) {
                            Debug::text('Found Punches: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
                            $i = 0;
                            foreach ($plf->rs as $p_obj) {
                                $plf->data = (array) $p_obj;
                                $p_obj = $plf;
                                Debug::text('TimeStamp: ' . $p_obj->getTimeStamp() . ' Status: ' . $p_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
                                if ($i == 0) {
                                    $first_punch_epoch = $p_obj->getTimeStamp();
                                }
                                $punch_pairs[$p_obj->getPunchControlID()][] = array(
                                    'status_id' => $p_obj->getStatus(),
                                    'punch_control_id' => $p_obj->getPunchControlID(),
                                    'time_stamp' => $p_obj->getTimeStamp()
                                );
                                $i++;
                            }
                        }



                        //Debug::Arr($punch_pairs, ' Punch Pairs...', __FILE__, __LINE__, __METHOD__, 10);

                        $shift_data = FALSE;
                        if (is_object($this->getUserDateObject()->getPayPeriodObject())
                                AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject())) {
                            //This should return all shifts within the minimum time between shifts setting.
                            //We need to get all shifts within
                            $shift_data = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getShiftData(NULL, $this->getUserDateObject()->getUser(), $first_punch_epoch, NULL, NULL, ( $pp_obj->getMinimumTimeBetweenShift() + $pp_obj->getMinimumFirstShiftTime()));
                        } else {
                            Debug::text(' No Pay Period...', __FILE__, __LINE__, __METHOD__, 10);
                        }
                        //Debug::Arr($shift_data, ' Shift Data...', __FILE__, __LINE__, __METHOD__, 10);
                        //Only calculate if their are at least two shifts
                        if (count($shift_data) >= 2) {
                            Debug::text(' Found at least two shifts...', __FILE__, __LINE__, __METHOD__, 10);

                            //Loop through shifts backwards.
                            krsort($shift_data);

                            $prev_key = FALSE;
                            foreach ($shift_data as $key => $data) {
                                //Debug::Arr($data, ' Shift Data for Shift: '. $key, __FILE__, __LINE__, __METHOD__, 10);
                                //Check if previous shift is greater than minimum first shift time.
                                $prev_key = $key - 1;

                                if (isset($shift_data[$prev_key]) AND isset($shift_data[$prev_key]['total_time']) AND $shift_data[$prev_key]['total_time'] >= $pp_obj->getMinimumFirstShiftTime()) {
                                    Debug::text(' Previous shift exceeds minimum first shift time... Shift Total Time: ' . $shift_data[$prev_key]['total_time'], __FILE__, __LINE__, __METHOD__, 10);

                                    //Get last out time of the previous shift.
                                    if (isset($shift_data[$prev_key]['last_out'])) {
                                        $previous_shift_last_out_epoch = $shift_data[$prev_key]['last_out']['time_stamp'];
                                        $current_shift_cutoff = $previous_shift_last_out_epoch + $pp_obj->getMinimumTimeBetweenShift();
                                        Debug::text(' Previous Shift Last Out: ' . TTDate::getDate('DATE+TIME', $previous_shift_last_out_epoch) . '(' . $previous_shift_last_out_epoch . ') Current Shift Cutoff: ' . TTDate::getDate('DATE+TIME', $current_shift_cutoff) . '(' . $previous_shift_last_out_epoch . ')', __FILE__, __LINE__, __METHOD__, 10);

                                        //Loop through all worked (status: 20) UserDateTotalRows
                                        $udtlf = new UserDateTotalListFactory();
                                        $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20);
                                        if ($udtlf->getRecordCount() > 0) {
                                            Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                                            $x = 1;
                                            foreach ($udtlf->rs as $udt_obj) {
                                                $udtlf->data = (array) $udt_obj;
                                                $udt_obj = $udtlf;
                                                Debug::text('X: ' . $x . '/' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                                                //Ignore incomplete punches
                                                if ($udt_obj->getTotalTime() == 0) {
                                                    continue;
                                                }

                                                if ($udt_obj->getPunchControlID() > 0 AND isset($punch_pairs[$udt_obj->getPunchControlID()])) {
                                                    Debug::text(' Found valid Punch Control ID: ' . $udt_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
                                                    Debug::text(' First Punch: ' . TTDate::getDate('DATE+TIME', $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp']) . ' Last Punch: ' . TTDate::getDate('DATE+TIME', $punch_pairs[$udt_obj->getPunchControlID()][1]['time_stamp']), __FILE__, __LINE__, __METHOD__, 10);

                                                    $punch_total_time = 0;
                                                    $force_minimum_time_calculation = FALSE;
                                                    //Make sure OUT punch is before current_shift_cutoff
                                                    if (isset($punch_pairs[$udt_obj->getPunchControlID()][1]) AND $punch_pairs[$udt_obj->getPunchControlID()][1]['time_stamp'] <= $current_shift_cutoff) {
                                                        Debug::text(' Both punches are BEFORE the cutoff time...', __FILE__, __LINE__, __METHOD__, 10);
                                                        $punch_total_time = bcsub($punch_pairs[$udt_obj->getPunchControlID()][1]['time_stamp'], $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp']);
                                                    } elseif (isset($punch_pairs[$udt_obj->getPunchControlID()][0]) AND $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp'] <= $current_shift_cutoff) {
                                                        Debug::text(' Only IN punch is BEFORE the cutoff time...', __FILE__, __LINE__, __METHOD__, 10);
                                                        $punch_total_time = bcsub($current_shift_cutoff, $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp']);
                                                        $force_minimum_time_calculation = TRUE;
                                                    } else {
                                                        Debug::text(' Both punches are AFTER the cutoff time... Skipping...', __FILE__, __LINE__, __METHOD__, 10);
                                                        //continue;
                                                        $punch_total_time = 0;
                                                    }
                                                    Debug::text(' Punch Total Time: ' . $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                    //Apply meal policy adjustment BEFORE min/max times
                                                    if ($pp_obj->getIncludeMealPolicy() == TRUE AND isset($udt_meal_policy_adjustment_arr[$udt_obj->getId()])) {
                                                        Debug::text(' Meal Policy Adjustment Found: ' . $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                                        $punch_total_time = bcadd($punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                                                    }
                                                    if ($pp_obj->getIncludeBreakPolicy() == TRUE AND isset($udt_break_policy_adjustment_arr[$udt_obj->getId()])) {
                                                        Debug::text(' Break Policy Adjustment Found: ' . $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                                        $punch_total_time = bcadd($punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                                                    }

                                                    $premium_policy_daily_total_time = 0;
                                                    if ($pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0) {
                                                        $premium_policy_daily_total_time = $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID($this->getUserDateID(), $pp_obj->getId());
                                                        Debug::text('X: ' . $x . '/' . $udtlf->getRecordCount() . ' Premium Policy Daily Total Time: ' . $premium_policy_daily_total_time . ' Minimum Time: ' . $pp_obj->getMinimumTime() . ' Maximum Time: ' . $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

                                                        if ($pp_obj->getMinimumTime() > 0) {
                                                            //FIXME: Split the minimum time up between all the punches somehow.
                                                            //Apply the minimum time on the last punch, otherwise if there are two punch pairs of 15min each
                                                            //and a 1hr minimum time, if the minimum time is applied to the first, it will be 1hr and 15min
                                                            //for the day. If its applied to the last it will be just 1hr.
                                                            //Min & Max time is based on the shift time, rather then per punch pair time.
                                                            if (( $force_minimum_time_calculation == TRUE OR $x == $udtlf->getRecordCount() ) AND bcadd($premium_policy_daily_total_time, $punch_total_time) < $pp_obj->getMinimumTime()) {
                                                                $total_time = bcsub($pp_obj->getMinimumTime(), $premium_policy_daily_total_time);
                                                            } else {
                                                                $total_time = $punch_total_time;
                                                            }
                                                        } else {
                                                            $total_time = $punch_total_time;
                                                        }

                                                        Debug::text(' Total Time After Minimum is applied: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                        if ($pp_obj->getMaximumTime() > 0) {
                                                            //Min & Max time is based on the shift time, rather then per punch pair time.
                                                            if (bcadd($premium_policy_daily_total_time, $total_time) > $pp_obj->getMaximumTime()) {
                                                                Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                $total_time = bcsub($total_time, bcsub(bcadd($premium_policy_daily_total_time, $total_time), $pp_obj->getMaximumTime()));
                                                            }
                                                        }
                                                    } else {
                                                        $total_time = $punch_total_time;
                                                    }

                                                    Debug::text(' Total Punch Control Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                                    if ($total_time > 0) {
                                                        Debug::text(' Applying  Premium Time!: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                        $udtf = new UserDateTotalFactory();
                                                        $udtf->setUserDateID($this->getUserDateID());
                                                        $udtf->setStatus(10); //System
                                                        $udtf->setType(40); //Premium
                                                        $udtf->setPremiumPolicyId($pp_obj->getId());
                                                        $udtf->setBranch($udt_obj->getBranch());
                                                        $udtf->setDepartment($udt_obj->getDepartment());
                                                        $udtf->setJob($udt_obj->getJob());
                                                        $udtf->setJobItem($udt_obj->getJobItem());

                                                        $udtf->setQuantity($udt_obj->getQuantity());
                                                        $udtf->setBadQuantity($udt_obj->getBadQuantity());

                                                        $udtf->setTotalTime($total_time);
                                                        $udtf->setEnableCalcSystemTotalTime(FALSE);
                                                        if ($udtf->isValid() == TRUE) {
                                                            $udtf->Save();
                                                        }
                                                        unset($udtf);
                                                    }
                                                } else {
                                                    Debug::text(' Skipping invalid Punch Control ID: ' . $udt_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
                                                }

                                                $x++;
                                            }
                                        }
                                    }
                                    unset($previous_shift_last_out_epoch, $current_shift_cutoff, $udtlf);
                                } else {
                                    Debug::text(' Previous shift does not exist or does NOT exceed minimum first shift time... Key: ' . $prev_key, __FILE__, __LINE__, __METHOD__, 10);
                                }
                            }
                        } else {
                            Debug::text(' Didnt find two shifts, or the first shift wasnt long enough... Total Shifts: ' . count($shift_data), __FILE__, __LINE__, __METHOD__, 10);
                        }
                        unset($plf, $punch_pairs, $first_punch_epoch, $shift_data, $data);

                        break;
                    case 50: //Minimum shift time
                        Debug::text(' Minimum Shift Time Premium Policy... Minimum Shift Time: ' . $pp_obj->getMinimumShiftTime(), __FILE__, __LINE__, __METHOD__, 10);

                        //Loop through all worked (status: 20) UserDateTotalRows
                        //Change sort order to reverse timestamp and limit to only one, so we get the last punch pair.
                        $udtlf = new UserDateTotalListFactory();
                        $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20, 1, NULL, NULL, array('a.status_id' => 'desc', 'c.time_stamp' => 'desc', 'a.start_time_stamp' => 'desc'));
                        if ($udtlf->getRecordCount() > 0) {
                            Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                            $x = 1;
                            foreach ($udtlf->rs as $udt_obj) { //We only use last punch pair.
                                $udtlf->data = (array) $udt_obj;
                                $udt_obj = $udtlf;
                                $punch_obj = FALSE;
                                if ($udt_obj->getPunchControlID() > 0) {
                                    $plf = new PunchListFactory();
                                    $plf->getByPunchControlId($udt_obj->getPunchControlID());
                                    if ($plf->getRecordCount() > 0) {
                                        Debug::text('Found Punches: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
                                        foreach ($plf->rs as $punch_obj) {
                                            $plf->data = (array) $punch_obj;
                                            $punch_obj = $plf;
                                            break; //Get first punch_obj.
                                        }
                                    }
                                }

                                $shift_data = FALSE;
                                if (is_object($punch_obj)
                                        AND is_object($this->getUserDateObject()->getPayPeriodObject())
                                        AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject())) {
                                    //This should return all shifts within the minimum time between shifts setting.
                                    //We need to get all shifts within
                                    $shift_data = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getShiftData(NULL, $this->getUserDateObject()->getUser(), $punch_obj->getTimeStamp(), 'nearest');
                                } else {
                                    Debug::text(' No Pay Period...', __FILE__, __LINE__, __METHOD__, 10);
                                }
                                //Debug::Arr($shift_data, ' Shift Data...', __FILE__, __LINE__, __METHOD__, 10);
                                //Only calculate if their are at least two shifts
                                if (count($shift_data) >= 1 AND isset($shift_data[0])) {
                                    $total_time = 0;
                                    $punch_total_time = $shift_data[0]['total_time'];

                                    //Apply meal policy adjustment BEFORE min/max times
                                    if ($pp_obj->getIncludeMealPolicy() == TRUE AND isset($udt_meal_policy_adjustment_arr[$udt_obj->getId()])) {
                                        Debug::text(' Meal Policy Adjustment Found: ' . $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                        $punch_total_time = bcadd($punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                                    }
                                    if ($pp_obj->getIncludeBreakPolicy() == TRUE AND isset($udt_break_policy_adjustment_arr[$udt_obj->getId()])) {
                                        Debug::text(' Break Policy Adjustment Found: ' . $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                        $punch_total_time = bcadd($punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                                    }
                                    Debug::text(' Found at least one shift, total time: ' . $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

                                    if ($punch_total_time > $pp_obj->getMinimumShiftTime()) {
                                        Debug::text(' Shift exceeds minimum shift time...', __FILE__, __LINE__, __METHOD__, 10);
                                        break;
                                    } else {
                                        $punch_total_time = bcsub($pp_obj->getMinimumShiftTime(), $punch_total_time);
                                    }

                                    $premium_policy_daily_total_time = 0;
                                    if ($pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0) {
                                        $premium_policy_daily_total_time = $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID($this->getUserDateID(), $pp_obj->getId());
                                        Debug::text('X: ' . $x . '/' . $udtlf->getRecordCount() . ' Premium Policy Daily Total Time: ' . $premium_policy_daily_total_time . ' Minimum Time: ' . $pp_obj->getMinimumTime() . ' Maximum Time: ' . $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

                                        if ($pp_obj->getMinimumTime() > 0) {
                                            //FIXME: Split the minimum time up between all the punches somehow.
                                            //Apply the minimum time on the last punch, otherwise if there are two punch pairs of 15min each
                                            //and a 1hr minimum time, if the minimum time is applied to the first, it will be 1hr and 15min
                                            //for the day. If its applied to the last it will be just 1hr.
                                            //Min & Max time is based on the shift time, rather then per punch pair time.
                                            if ($x == $udtlf->getRecordCount() AND bcadd($premium_policy_daily_total_time, $punch_total_time) < $pp_obj->getMinimumTime()) {
                                                $total_time = bcsub($pp_obj->getMinimumTime(), $premium_policy_daily_total_time);
                                            } else {
                                                $total_time = $punch_total_time;
                                            }
                                        } else {
                                            $total_time = $punch_total_time;
                                        }

                                        Debug::text(' Total Time After Minimum is applied: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                        if ($pp_obj->getMaximumTime() > 0) {
                                            //Min & Max time is based on the shift time, rather then per punch pair time.
                                            if (bcadd($premium_policy_daily_total_time, $total_time) > $pp_obj->getMaximumTime()) {
                                                Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                $total_time = bcsub($total_time, bcsub(bcadd($premium_policy_daily_total_time, $total_time), $pp_obj->getMaximumTime()));
                                            }
                                        }
                                    } else {
                                        $total_time = $punch_total_time;
                                    }

                                    Debug::text(' Total Punch Control Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                    if ($total_time > 0) {
                                        Debug::text(' Applying  Premium Time!: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                        $udtf = new UserDateTotalFactory();
                                        $udtf->setUserDateID($this->getUserDateID());
                                        $udtf->setStatus(10); //System
                                        $udtf->setType(40); //Premium
                                        $udtf->setPremiumPolicyId($pp_obj->getId());
                                        $udtf->setBranch($udt_obj->getBranch());
                                        $udtf->setDepartment($udt_obj->getDepartment());
                                        $udtf->setJob($udt_obj->getJob());
                                        $udtf->setJobItem($udt_obj->getJobItem());

                                        $udtf->setQuantity($udt_obj->getQuantity());
                                        $udtf->setBadQuantity($udt_obj->getBadQuantity());

                                        $udtf->setTotalTime($total_time);
                                        $udtf->setEnableCalcSystemTotalTime(FALSE);
                                        if ($udtf->isValid() == TRUE) {
                                            $udtf->Save();
                                        }
                                        unset($udtf);
                                    }
                                } else {
                                    Debug::text(' Didnt find nearest shift...', __FILE__, __LINE__, __METHOD__, 10);
                                }
                            }
                        }
                        unset($udtlf, $punch_obj, $punch_total_time, $total_time, $shift_data);

                        break;
                    case 90: //Holiday
                        Debug::text(' Holiday Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

                        //Determine if the employee is eligible for holiday premium.
                        $hlf = new HolidayListFactory();
                        $hlf->getByPolicyGroupUserIdAndDate($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp());
                        if ($hlf->getRecordCount() > 0) {
                            $holiday_obj = $hlf->getCurrent();
                            Debug::text(' Found Holiday: ' . $holiday_obj->getName() . ' Date: ' . TTDate::getDate('DATE', $holiday_obj->getDateStamp()) . ' Current Date: ' . TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp()), __FILE__, __LINE__, __METHOD__, 10);

                            if ($holiday_obj->getDateStamp() == $this->getUserDateObject()->getDateStamp()) {
                                if ($holiday_obj->getHolidayPolicyObject()->getForceOverTimePolicy() == TRUE
                                        OR $holiday_obj->isEligible($this->getUserDateObject()->getUser())) {
                                    Debug::text(' User is Eligible for Holiday: ' . $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

                                    $udtlf = new UserDateTotalListFactory();
                                    $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20);
                                    if ($udtlf->getRecordCount() > 0) {
                                        Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                                        foreach ($udtlf->rs as $udt_obj) {
                                            $udtlf->data = (array) $udt_obj;
                                            $udt_obj = $udtlf;
                                            //Ignore incomplete punches
                                            if ($udt_obj->getTotalTime() == 0) {
                                                continue;
                                            }

                                            $premium_policy_daily_total_time = 0;
                                            $punch_total_time = $udt_obj->getTotalTime();
                                            $total_time = 0;

                                            //Apply meal policy adjustment BEFORE min/max times
                                            if ($pp_obj->getIncludeMealPolicy() == TRUE AND isset($udt_meal_policy_adjustment_arr[$udt_obj->getId()])) {
                                                Debug::text(' Meal Policy Adjustment Found: ' . $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                                $punch_total_time = bcadd($punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                                            }
                                            if ($pp_obj->getIncludeBreakPolicy() == TRUE AND isset($udt_break_policy_adjustment_arr[$udt_obj->getId()])) {
                                                Debug::text(' Break Policy Adjustment Found: ' . $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                                $punch_total_time = bcadd($punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                                            }

                                            if ($pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0) {
                                                $premium_policy_daily_total_time = $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID($this->getUserDateID(), $pp_obj->getId());
                                                Debug::text(' Premium Policy Daily Total Time: ' . $premium_policy_daily_total_time . ' Minimum Time: ' . $pp_obj->getMinimumTime() . ' Maximum Time: ' . $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

                                                if ($pp_obj->getMinimumTime() > 0) {
                                                    if ($daily_total_time < $pp_obj->getMinimumTime()) {
                                                        //Split the minimum time up between all punches
                                                        //We only get IN punches, so we don't need to divide $total_punches by 2.
                                                        //This won't calculate the proper amount if punches aren't paired, but everything
                                                        //is broken then anyways.
                                                        $total_time = bcdiv($pp_obj->getMinimumTime(), $udtlf->getRecordCount());
                                                        Debug::text(' Daily Total Time is less the Minimum, using: ' . $total_time . ' Total Punches: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
                                                    } else {
                                                        Debug::text(' Daily Total is more then minimum...', __FILE__, __LINE__, __METHOD__, 10);
                                                        $total_time = $punch_total_time;
                                                    }
                                                } else {
                                                    $total_time = $punch_total_time;
                                                }

                                                Debug::text(' Total Time After Minimum is applied: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                                if ($pp_obj->getMaximumTime() > 0) {
                                                    if ($total_time > $pp_obj->getMaximumTime()) {
                                                        Debug::text(' aMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                        $total_time = $pp_obj->getMaximumTime();
                                                    } elseif (bcadd($premium_policy_daily_total_time, $total_time) > $pp_obj->getMaximumTime()) {
                                                        Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                        $total_time = bcsub(bcadd($premium_policy_daily_total_time, $total_time), $pp_obj->getMaximumTime());
                                                    }
                                                }
                                            } else {
                                                $total_time = $punch_total_time;
                                            }

                                            Debug::text(' Premium Punch Total Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                            if ($total_time > 0) {
                                                Debug::text(' Applying  Premium Time!: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                $udtf = new UserDateTotalFactory();
                                                $udtf->setUserDateID($this->getUserDateID());
                                                $udtf->setStatus(10); //System
                                                $udtf->setType(40); //Premium
                                                $udtf->setPremiumPolicyId($pp_obj->getId());
                                                $udtf->setBranch($udt_obj->getBranch());
                                                $udtf->setDepartment($udt_obj->getDepartment());
                                                $udtf->setJob($udt_obj->getJob());
                                                $udtf->setJobItem($udt_obj->getJobItem());

                                                $udtf->setQuantity($udt_obj->getQuantity());
                                                $udtf->setBadQuantity($udt_obj->getBadQuantity());

                                                $udtf->setTotalTime($total_time);
                                                $udtf->setEnableCalcSystemTotalTime(FALSE);
                                                if ($udtf->isValid() == TRUE) {
                                                    $udtf->Save();
                                                }
                                                unset($udtf);
                                            } else {
                                                Debug::text(' Premium Punch Total Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
                                            }
                                        }
                                    }
                                }
                            } else {
                                Debug::text(' This date is not an actual holiday...', __FILE__, __LINE__, __METHOD__, 10);
                            }
                        }

                        break;
                    case 100: //Advanced
                        Debug::text(' Advanced Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

                        //Make sure this is a valid day
                        if ($pp_obj->isActive($this->getUserDateObject()->getDateStamp() - 86400, $this->getUserDateObject()->getDateStamp() + 86400)) {

                            Debug::text(' Premium Policy Is Active On This Day.', __FILE__, __LINE__, __METHOD__, 10);

                            $total_daily_time_used = 0;
                            $daily_trigger_time = 0;

                            $udtlf = new UserDateTotalListFactory();

                            if ($pp_obj->isHourRestricted() == TRUE) {
                                if ($pp_obj->getWeeklyTriggerTime() > 0) {
                                    //Get Pay Period Schedule info
                                    if (is_object($this->getUserDateObject()->getPayPeriodObject())
                                            AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject())) {
                                        $start_week_day_id = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
                                    } else {
                                        $start_week_day_id = 0;
                                    }
                                    Debug::text('Start Week Day ID: ' . $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

                                    $weekly_total_time = $udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id));
                                    if ($weekly_total_time > $pp_obj->getWeeklyTriggerTime()) {
                                        $daily_trigger_time = 0;
                                    } else {
                                        $daily_trigger_time = $pp_obj->getWeeklyTriggerTime() - $weekly_total_time;
                                    }
                                    Debug::text(' Weekly Trigger Time: ' . $daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                                }

                                if ($pp_obj->getDailyTriggerTime() > 0 AND $pp_obj->getDailyTriggerTime() > $daily_trigger_time) {
                                    $daily_trigger_time = $pp_obj->getDailyTriggerTime();
                                }
                            }
                            Debug::text(' Daily Trigger Time: ' . $daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

                            //Loop through all worked (status: 20) UserDateTotalRows
                            $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20);
                            $i = 1;
                            if ($udtlf->getRecordCount() > 0) {
                                Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                                foreach ($udtlf->rs as $udt_obj) {
                                    $udtlf->data = (array) $udt_obj;
                                    $udt_obj = $udtlf;
                                    //Ignore incomplete punches
                                    if ($udt_obj->getTotalTime() == 0) {
                                        continue;
                                    }

                                    //Check Shift Differential criteria before calculatating daily/weekly time which
                                    //is more resource intensive.
                                    if (( $pp_obj->getBranchSelectionType() == 10
                                            AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
                                            OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
                                            AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) ) )

                                            OR ( $pp_obj->getBranchSelectionType() == 20
                                            AND in_array($udt_obj->getBranch(), (array) $pp_obj->getBranch()) )
                                            AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
                                            OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
                                            AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )

                                            OR ( $pp_obj->getBranchSelectionType() == 30
                                            AND ! in_array($udt_obj->getBranch(), (array) $pp_obj->getBranch()) )
                                            AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
                                            OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
                                            AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )
                                    ) {
                                        Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: ' . $pp_obj->getBranchSelectionType() . ' Exclude Default Branch: ' . (int) $pp_obj->getExcludeDefaultBranch() . ' Default Branch: ' . $this->getUserDateObject()->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);

                                        if (( $pp_obj->getDepartmentSelectionType() == 10
                                                AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
                                                OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
                                                AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) ) )

                                                OR ( $pp_obj->getDepartmentSelectionType() == 20
                                                AND in_array($udt_obj->getDepartment(), (array) $pp_obj->getDepartment()) )
                                                AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
                                                OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
                                                AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )

                                                OR ( $pp_obj->getDepartmentSelectionType() == 30
                                                AND ! in_array($udt_obj->getDepartment(), (array) $pp_obj->getDepartment()) )
                                                AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
                                                OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
                                                AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )
                                        ) {
                                            Debug::text(' Shift Differential... Meets Department Criteria! Select Type: ' . $pp_obj->getDepartmentSelectionType() . ' Exclude Default Department: ' . (int) $pp_obj->getExcludeDefaultDepartment() . ' Default Department: ' . $this->getUserDateObject()->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);


                                            if ($pp_obj->getJobGroupSelectionType() == 10
                                                    OR ( $pp_obj->getJobGroupSelectionType() == 20
                                                    AND is_object($udt_obj->getJobObject())
                                                    AND in_array($udt_obj->getJobObject()->getGroup(), (array) $pp_obj->getJobGroup()) )
                                                    OR ( $pp_obj->getJobGroupSelectionType() == 30
                                                    AND is_object($udt_obj->getJobObject())
                                                    AND ! in_array($udt_obj->getJobObject()->getGroup(), (array) $pp_obj->getJobGroup()) )
                                            ) {
                                                Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: ' . $pp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

                                                if ($pp_obj->getJobSelectionType() == 10
                                                        OR ( $pp_obj->getJobSelectionType() == 20
                                                        AND in_array($udt_obj->getJob(), (array) $pp_obj->getJob()) )
                                                        OR ( $pp_obj->getJobSelectionType() == 30
                                                        AND ! in_array($udt_obj->getJob(), (array) $pp_obj->getJob()) )
                                                ) {
                                                    Debug::text(' Shift Differential... Meets Job Criteria! Select Type: ' . $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

                                                    if ($pp_obj->getJobItemGroupSelectionType() == 10
                                                            OR ( $pp_obj->getJobItemGroupSelectionType() == 20
                                                            AND is_object($udt_obj->getJobItemObject())
                                                            AND in_array($udt_obj->getJobItemObject()->getGroup(), (array) $pp_obj->getJobItemGroup()) )
                                                            OR ( $pp_obj->getJobItemGroupSelectionType() == 30
                                                            AND is_object($udt_obj->getJobItemObject())
                                                            AND ! in_array($udt_obj->getJobItemObject()->getGroup(), (array) $pp_obj->getJobItemGroup()) )
                                                    ) {
                                                        Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: ' . $pp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

                                                        if ($pp_obj->getJobItemSelectionType() == 10
                                                                OR ( $pp_obj->getJobItemSelectionType() == 20
                                                                AND in_array($udt_obj->getJobItem(), (array) $pp_obj->getJobItem()) )
                                                                OR ( $pp_obj->getJobItemSelectionType() == 30
                                                                AND ! in_array($udt_obj->getJobItem(), (array) $pp_obj->getJobItem()) )
                                                        ) {
                                                            Debug::text(' Shift Differential... Meets Task Criteria! Select Type: ' . $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

                                                            if ($pp_obj->isTimeRestricted() == TRUE AND $udt_obj->getPunchControlID() != FALSE) {
                                                                Debug::text('Time Restricted Premium Policy, lookup punches to get times.', __FILE__, __LINE__, __METHOD__, 10);

                                                                if ($pp_obj->getIncludePartialPunch() == FALSE) {
                                                                    $shift_data = $this->getShiftDataByUserDateID($this->getUserDateID());
                                                                }

                                                                $plf = new PunchListFactory();
                                                                $plf->getByPunchControlId($udt_obj->getPunchControlID());
                                                                if ($plf->getRecordCount() > 0) {
                                                                    Debug::text('Found Punches: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
                                                                    foreach ($plf->rs as $punch_obj) {
                                                                        $plf->data = (array) $punch_obj;
                                                                        $punch_obj = $plf;
                                                                        if ($pp_obj->getIncludePartialPunch() == TRUE) {
                                                                            //Debug::text('Including Partial Punches...', __FILE__, __LINE__, __METHOD__, 10);

                                                                            if ($punch_obj->getStatus() == 10) {
                                                                                $punch_times['in'] = $punch_obj->getTimeStamp();
                                                                            } elseif ($punch_obj->getStatus() == 20) {
                                                                                $punch_times['out'] = $punch_obj->getTimeStamp();
                                                                            }
                                                                        } else {
                                                                            if (isset($shift_data) AND is_array($shift_data)) {
                                                                                foreach ($shift_data as $shift) {
                                                                                    if ($punch_obj->getTimeStamp() >= $shift['first_in']
                                                                                            AND $punch_obj->getTimeStamp() <= $shift['last_out']) {
                                                                                        //Debug::Arr($shift,'Shift Data...', __FILE__, __LINE__, __METHOD__, 10);
                                                                                        Debug::text('Punch (' . TTDate::getDate('DATE+TIME', $punch_obj->getTimeStamp()) . ') inside shift time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                                        $punch_times['in'] = $shift['first_in'];
                                                                                        $punch_times['out'] = $shift['last_out'];
                                                                                        break;
                                                                                    } else {
                                                                                        Debug::text('Punch (' . TTDate::getDate('DATE+TIME', $punch_obj->getTimeStamp()) . ') outside shift time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if (isset($punch_times) AND count($punch_times) == 2
                                                                            AND $pp_obj->isActiveDate($this->getUserDateObject()->getDateStamp()) == TRUE
                                                                            AND $pp_obj->isActiveTime($punch_times['in'], $punch_times['out']) == TRUE) {
                                                                        //Debug::Arr($punch_times, 'Punch Times: ', __FILE__, __LINE__, __METHOD__, 10);
                                                                        $punch_total_time = $pp_obj->getPartialPunchTotalTime($punch_times['in'], $punch_times['out'], $udt_obj->getTotalTime());
                                                                        Debug::text('Valid Punch pair in active time, Partial Punch Total Time: ' . $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);
                                                                    } else {
                                                                        Debug::text('InValid Punch Pair or outside Active Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                        $punch_total_time = 0;
                                                                    }
                                                                }
                                                            } elseif ($pp_obj->isActive($udt_obj->getUserDateObject()->getDateStamp()) == TRUE) {
                                                                $punch_total_time = $udt_obj->getTotalTime();
                                                            } else {
                                                                $punch_total_time = 0;
                                                            }
                                                            $tmp_punch_total_time = $udt_obj->getTotalTime();
                                                            Debug::text('aPunch Total Time: ' . $punch_total_time . ' TMP Punch Total Time: ' . $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                            //Apply meal policy adjustment as early as possible.
                                                            if ($pp_obj->getIncludeMealPolicy() == TRUE AND isset($udt_meal_policy_adjustment_arr[$udt_obj->getId()])) {
                                                                Debug::text(' Meal Policy Adjustment Found: ' . $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                                                $punch_total_time = bcadd($punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                                                                $tmp_punch_total_time = bcadd($tmp_punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                                                            }
                                                            Debug::text('bPunch Total Time: ' . $punch_total_time . ' TMP Punch Total Time: ' . $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                            //Apply break policy adjustment as early as possible.
                                                            if ($pp_obj->getIncludeBreakPolicy() == TRUE AND isset($udt_break_policy_adjustment_arr[$udt_obj->getId()])) {
                                                                Debug::text(' Break Policy Adjustment Found: ' . $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
                                                                $punch_total_time = bcadd($punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                                                                $tmp_punch_total_time = bcadd($tmp_punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                                                            }
                                                            Debug::text('cPunch Total Time: ' . $punch_total_time . ' TMP Punch Total Time: ' . $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                            $total_daily_time_used += $tmp_punch_total_time;
                                                            Debug::text('Daily Total Time Used: ' . $total_daily_time_used, __FILE__, __LINE__, __METHOD__, 10);

                                                            if ($punch_total_time > 0 AND $total_daily_time_used > $daily_trigger_time) {
                                                                Debug::text('Past Trigger Time!!', __FILE__, __LINE__, __METHOD__, 10);

                                                                //Calculate how far past trigger time we are.
                                                                $past_trigger_time = $total_daily_time_used - $daily_trigger_time;
                                                                if ($punch_total_time > $past_trigger_time) {
                                                                    $punch_total_time = $past_trigger_time;
                                                                    Debug::text('Using Past Trigger Time as punch total time: ' . $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                                                                } else {
                                                                    Debug::text('NOT Using Past Trigger Time as punch total time: ' . $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
                                                                }

                                                                $total_time = $punch_total_time;
                                                                if ($pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0) {
                                                                    $premium_policy_daily_total_time = (int) $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID($this->getUserDateID(), $pp_obj->getId());
                                                                    Debug::text(' Premium Policy Daily Total Time: ' . $premium_policy_daily_total_time . ' Minimum Time: ' . $pp_obj->getMinimumTime() . ' Maximum Time: ' . $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

                                                                    if ($pp_obj->getMinimumTime() > 0) {
                                                                        //FIXME: Split the minimum time up between all the punches somehow.
                                                                        if ($i == $udtlf->getRecordCount() AND bcadd($premium_policy_daily_total_time, $total_time) < $pp_obj->getMinimumTime()) {
                                                                            $total_time = bcsub($pp_obj->getMinimumTime(), $premium_policy_daily_total_time);
                                                                        }
                                                                    }

                                                                    Debug::text(' Total Time After Minimum is applied: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                                                    if ($pp_obj->getMaximumTime() > 0) {
                                                                        if ($total_time > $pp_obj->getMaximumTime()) {
                                                                            Debug::text(' aMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                            $total_time = $pp_obj->getMaximumTime();
                                                                        } elseif (bcadd($premium_policy_daily_total_time, $total_time) > $pp_obj->getMaximumTime()) {
                                                                            Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
                                                                            //$total_time = bcsub( bcadd( $premium_policy_daily_total_time, $total_time ), $pp_obj->getMaximumTime() );
                                                                            $total_time = bcsub($total_time, bcsub(bcadd($premium_policy_daily_total_time, $total_time), $pp_obj->getMaximumTime()));
                                                                        }
                                                                    }
                                                                }

                                                                Debug::text(' Premium Punch Total Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);
                                                                if ($total_time > 0) {
                                                                    Debug::text(' Applying  Premium Time!: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10);

                                                                    $udtf = new UserDateTotalFactory();
                                                                    $udtf->setUserDateID($this->getUserDateID());
                                                                    $udtf->setStatus(10); //System
                                                                    $udtf->setType(40); //Premium
                                                                    $udtf->setPremiumPolicyId($pp_obj->getId());
                                                                    $udtf->setBranch($udt_obj->getBranch());
                                                                    $udtf->setDepartment($udt_obj->getDepartment());
                                                                    $udtf->setJob($udt_obj->getJob());
                                                                    $udtf->setJobItem($udt_obj->getJobItem());

                                                                    $udtf->setQuantity($udt_obj->getQuantity());
                                                                    $udtf->setBadQuantity($udt_obj->getBadQuantity());

                                                                    $udtf->setTotalTime($total_time);
                                                                    $udtf->setEnableCalcSystemTotalTime(FALSE);
                                                                    if ($udtf->isValid() == TRUE) {
                                                                        $udtf->Save();
                                                                    }
                                                                    unset($udtf);
                                                                } else {
                                                                    Debug::text(' Premium Punch Total Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
                                                                }
                                                            } else {
                                                                Debug::text('Not Past Trigger Time Yet or Punch Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
                                                            }
                                                        } else {
                                                            Debug::text(' Shift Differential... DOES NOT Meet Task Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                                        }
                                                    } else {
                                                        Debug::text(' Shift Differential... DOES NOT Meet Task Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                                    }
                                                } else {
                                                    Debug::text(' Shift Differential... DOES NOT Meet Job Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                                }
                                            } else {
                                                Debug::text(' Shift Differential... DOES NOT Meet Job Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                            }
                                        } else {
                                            Debug::text(' Shift Differential... DOES NOT Meet Department Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                        }
                                    } else {
                                        Debug::text(' Shift Differential... DOES NOT Meet Branch Criteria!', __FILE__, __LINE__, __METHOD__, 10);
                                    }

                                    $i++;
                                }
                            }
                        }
                        break;
                }
            }
        }

        $profiler->stopTimer('UserDateTotal::calcPremiumPolicyTotalTime() - Part 1');

        return TRUE;
    }

    function calcAbsencePolicyTotalTime() {
        //Don't do this, because it doubles up on paid time?
        //Only issue is if we want to add these hours to weekly OT hours or anything.
        //Does it double up on paid time, as it is paid time after all?

        /*
          Debug::text(' Adding Paid Absence Policy time to Regular Time: '. $this->getUserDateID(), __FILE__, __LINE__, __METHOD__,10);
          $udtlf = new UserDateTotalListFactory();
          $udtlf->getPaidAbsenceByUserDateID( $this->getUserDateID() );
          if ( $udtlf->getRecordCount() > 0 ) {
          foreach ($udtlf as $udt_obj) {
          Debug::text(' Found some Paid Absence Policy time entries: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);
          $udtf = new UserDateTotalFactory();
          $udtf->setUserDateID( $this->getUserDateID() );
          $udtf->setStatus( 10 ); //System
          $udtf->setType( 20 ); //Regular
          $udtf->setBranch( $udt_obj->getBranch() );
          $udtf->setDepartment( $udt_obj->getDepartment() );
          $udtf->setTotalTime( $udt_obj->getTotalTime() );
          $udtf->Save();
          }

          return TRUE;
          } else {
          Debug::text(' Found zero Paid Absence Policy time entries: '. $this->getUserDateID(), __FILE__, __LINE__, __METHOD__,10);
          }

          return FALSE;
         */

        return TRUE;
    }

    //Meal policy deduct/include time should be calculated on a percentage basis between all branches/departments/jobs/tasks
    //rounded to the nearest 60 seconds. This is the only way to keep things "fair"
    //as we can never know which individual branch/department/job/task to deduct/include the time for.
    //
	//Use the Worked Time UserTotal rows to calculate the adjustment for each worked time row.
    //Since we need this information BEFORE any compaction occurs.
    function calcUserTotalMealPolicyAdjustment($meal_policy_time) {
        if ($meal_policy_time == '' OR $meal_policy_time == 0) {
            return array();
        }
        Debug::text('Meal Policy Time: ' . $meal_policy_time, __FILE__, __LINE__, __METHOD__, 10);

        $day_total_time = 0;
        $retarr = array();

        $udtlf = new UserDateTotalListFactory();
        $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20);
        if ($udtlf->getRecordCount() > 0) {
            foreach ($udtlf->rs as $udt_obj) {
                $udtlf->data = (array) $udt_obj;
                $udt_obj = $udtlf;
                $udt_arr[$udt_obj->getId()] = $udt_obj->getTotalTime();

                $day_total_time = bcadd($day_total_time, $udt_obj->getTotalTime());
            }
            Debug::text('Day Total Time: ' . $day_total_time, __FILE__, __LINE__, __METHOD__, 10);

            if (is_array($udt_arr) AND $day_total_time > 0) {
                $remainder = 0;
                foreach ($udt_arr as $udt_id => $total_time) {
                    $udt_raw_meal_policy_time = bcmul(bcdiv($total_time, $day_total_time), $meal_policy_time);
                    if ($meal_policy_time > 0) {
                        $rounded_udt_raw_meal_policy_time = floor($udt_raw_meal_policy_time);
                        $remainder = bcadd($remainder, bcsub($udt_raw_meal_policy_time, $rounded_udt_raw_meal_policy_time));
                    } else {
                        $rounded_udt_raw_meal_policy_time = ceil($udt_raw_meal_policy_time);
                        $remainder = bcadd($remainder, bcsub($udt_raw_meal_policy_time, $rounded_udt_raw_meal_policy_time));
                    }
                    $retarr[$udt_id] = (int) $rounded_udt_raw_meal_policy_time;

                    Debug::text('UserDateTotal Row ID: ' . $udt_id . ' Raw Meal Policy Time: ' . $udt_raw_meal_policy_time . '(' . $rounded_udt_raw_meal_policy_time . ') Remainder: ' . $remainder, __FILE__, __LINE__, __METHOD__, 10);
                }

                //Add remainder rounded to the nearest second to the last row.
                if ($meal_policy_time > 0) {
                    $remainder = ceil($remainder);
                } else {
                    $remainder = floor($remainder);
                }
                $retarr[$udt_id] = (int) bcadd($retarr[$udt_id], $remainder);
            }
        } else {
            Debug::text('No UserDateTotal rows...', __FILE__, __LINE__, __METHOD__, 10);
        }

        return $retarr;
    }

    function calcMealPolicyTotalTime($meal_policy_obj = NULL) {
        //Debug::arr($meal_policy_obj, 'MealPolicyObject param:', __FILE__, __LINE__, __METHOD__, 10);
        //Get total worked time for the day.
        $udtlf = new UserDateTotalListFactory();
        $daily_total_time = $udtlf->getWorkedTimeSumByUserDateID($this->getUserDateID());

        if (is_object($meal_policy_obj) == FALSE) {
            //Lookup meal policy
            $mplf = new MealPolicyListFactory();
            //$mplf->getByPolicyGroupUserId( $this->getUserDateObject()->getUser() );
            $mplf->getByPolicyGroupUserIdAndDayTotalTime($this->getUserDateObject()->getUser(), $daily_total_time);
            if ($mplf->getRecordCount() > 0) {
                Debug::text('Found Meal Policy to apply.', __FILE__, __LINE__, __METHOD__, 10);
                $meal_policy_obj = $mplf->getCurrent();
            }
        }

        $meal_policy_time = 0;

        if (is_object($meal_policy_obj) AND $daily_total_time >= $meal_policy_obj->getTriggerTime()) {
            Debug::text('Meal Policy ID: ' . $meal_policy_obj->getId() . ' Type ID: ' . $meal_policy_obj->getType() . ' Amount: ' . $meal_policy_obj->getAmount() . ' Daily Total TIme: ' . $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

            //Get lunch total time.
            $lunch_total_time = 0;

            $plf = new PunchListFactory();
            $plf->getByUserDateIdAndTypeId($this->getUserDateId(), 20); //Only Lunch punches
            if ($plf->getRecordCount() > 0) {
                $pair = 0;
                $x = 0;
                $out_for_lunch = FALSE;
                foreach ($plf->rs as $p_obj) {
                    $plf->data = (array) $p_obj;
                    $p_obj = $plf;
                    if ($p_obj->getStatus() == 20 AND $p_obj->getType() == 20) {
                        $lunch_out_timestamp = $p_obj->getTimeStamp();
                        $out_for_lunch = TRUE;
                    } elseif ($out_for_lunch == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 20) {
                        $lunch_punch_arr[$pair][20] = $lunch_out_timestamp;
                        $lunch_punch_arr[$pair][10] = $p_obj->getTimeStamp();
                        $out_for_lunch = FALSE;
                        $pair++;
                        unset($lunch_out_timestamp);
                    } else {
                        $out_for_lunch = FALSE;
                    }

                    $x++;
                }

                if (isset($lunch_punch_arr)) {
                    foreach ($lunch_punch_arr as $punch_control_id => $time_stamp_arr) {
                        if (isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20])) {
                            $lunch_total_time = bcadd($lunch_total_time, bcsub($time_stamp_arr[10], $time_stamp_arr[20]));
                        } else {
                            Debug::text(' Lunch Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
                        }
                    }
                } else {
                    Debug::text(' No Lunch Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
                }
            }

            Debug::text(' Lunch Total Time: ' . $lunch_total_time, __FILE__, __LINE__, __METHOD__, 10);
            switch ($meal_policy_obj->getType()) {
                case 10: //Auto-Deduct
                    Debug::text(' Lunch AutoDeduct.', __FILE__, __LINE__, __METHOD__, 10);
                    if ($meal_policy_obj->getIncludeLunchPunchTime() == TRUE) {
                        $meal_policy_time = bcsub($meal_policy_obj->getAmount(), $lunch_total_time) * -1;
                        //If they take more then their alloted lunch, zero it out so time isn't added.
                        if ($meal_policy_time > 0) {
                            $meal_policy_time = 0;
                        }
                    } else {
                        $meal_policy_time = $meal_policy_obj->getAmount() * -1;
                    }
                    break;
                case 15: //Auto-Include
                    Debug::text(' Lunch AutoInclude.', __FILE__, __LINE__, __METHOD__, 10);
                    if ($meal_policy_obj->getIncludeLunchPunchTime() == TRUE) {
                        if ($lunch_total_time > $meal_policy_obj->getAmount()) {
                            $meal_policy_time = $meal_policy_obj->getAmount();
                        } else {
                            $meal_policy_time = $lunch_total_time;
                        }
                    } else {
                        $meal_policy_time = $meal_policy_obj->getAmount();
                    }
                    break;
            }

            Debug::text(' Meal Policy Total Time: ' . $meal_policy_time, __FILE__, __LINE__, __METHOD__, 10);

            if ($meal_policy_time != 0) {
                $udtf = new UserDateTotalFactory();
                $udtf->setUserDateID($this->getUserDateID());
                $udtf->setStatus(10); //System
                $udtf->setType(100); //Lunch
                $udtf->setMealPolicyId($meal_policy_obj->getId());
                $udtf->setBranch($this->getUserDateObject()->getUserObject()->getDefaultBranch());
                $udtf->setDepartment($this->getUserDateObject()->getUserObject()->getDefaultDepartment());

                $udtf->setTotalTime($meal_policy_time);
                $udtf->setEnableCalcSystemTotalTime(FALSE);
                if ($udtf->isValid() == TRUE) {
                    $udtf->Save();
                }
                unset($udtf);
            }
        } else {
            Debug::text(' No Meal Policy found, or not after meal policy trigger time yet...', __FILE__, __LINE__, __METHOD__, 10);
        }

        return $meal_policy_time;
    }

    //Break policy deduct/include time should be calculated on a percentage basis between all branches/departments/jobs/tasks
    //rounded to the nearest 60 seconds. This is the only way to keep things "fair"
    //as we can never know which individual branch/department/job/task to deduct/include the time for.
    //
	//Use the Worked Time UserTotal rows to calculate the adjustment for each worked time row.
    //Since we need this information BEFORE any compaction occurs.
    function calcUserTotalBreakPolicyAdjustment($break_policy_time) {
        if ($break_policy_time == '' OR $break_policy_time == 0) {
            return array();
        }
        Debug::text('Break Policy Time: ' . $break_policy_time, __FILE__, __LINE__, __METHOD__, 10);

        $day_total_time = 0;
        $retarr = array();

        $udtlf = new UserDateTotalListFactory();
        $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), 20);
        if ($udtlf->getRecordCount() > 0) {
            foreach ($udtlf->rs as $udt_obj) {
                $udtlf->data = (array) $udt_obj;
                $udt_obj = $udtlf;
                $udt_arr[$udt_obj->getId()] = $udt_obj->getTotalTime();

                $day_total_time = bcadd($day_total_time, $udt_obj->getTotalTime());
            }
            Debug::text('Day Total Time: ' . $day_total_time, __FILE__, __LINE__, __METHOD__, 10);

            if (is_array($udt_arr)) {
                $remainder = 0;
                foreach ($udt_arr as $udt_id => $total_time) {
                    $udt_raw_break_policy_time = bcmul(bcdiv($total_time, $day_total_time), $break_policy_time);
                    if ($break_policy_time > 0) {
                        $rounded_udt_raw_break_policy_time = floor($udt_raw_break_policy_time);
                        $remainder = bcadd($remainder, bcsub($udt_raw_break_policy_time, $rounded_udt_raw_break_policy_time));
                    } else {
                        $rounded_udt_raw_break_policy_time = ceil($udt_raw_break_policy_time);
                        $remainder = bcadd($remainder, bcsub($udt_raw_break_policy_time, $rounded_udt_raw_break_policy_time));
                    }
                    $retarr[$udt_id] = (int) $rounded_udt_raw_break_policy_time;

                    Debug::text('UserDateTotal Row ID: ' . $udt_id . ' Raw Break Policy Time: ' . $udt_raw_break_policy_time . '(' . $rounded_udt_raw_break_policy_time . ') Remainder: ' . $remainder, __FILE__, __LINE__, __METHOD__, 10);
                }

                //Add remainder rounded to the nearest second to the last row.
                if ($break_policy_time > 0) {
                    $remainder = ceil($remainder);
                } else {
                    $remainder = floor($remainder);
                }
                $retarr[$udt_id] = (int) bcadd($retarr[$udt_id], $remainder);
            }
        } else {
            Debug::text('No UserDateTotal rows...', __FILE__, __LINE__, __METHOD__, 10);
        }

        return $retarr;
    }

    function calcBreakPolicyTotalTime($break_policy_ids = NULL) {
        //Get total worked time for the day.
        $udtlf = new UserDateTotalListFactory();
        $daily_total_time = $udtlf->getWorkedTimeSumByUserDateID($this->getUserDateID());
        Debug::text('Daily Total Time: ' . $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

        $bplf = new BreakPolicyListFactory();
        if (is_array($break_policy_ids)) {
            $bplf->getByIdAndCompanyId($break_policy_ids, $this->getUserDateObject()->getUserObject()->getCompany());
        } else {
            //Lookup break policy
            $bplf->getByPolicyGroupUserIdAndDayTotalTime($this->getUserDateObject()->getUser(), $daily_total_time);
        }

        $break_policy_total_time = 0;

        if ($bplf->getRecordCount() > 0) {
            Debug::text('Found Break Policy(ies) to apply: ' . $bplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

            $break_total_time_arr = array();
            $break_overall_total_time = 0;

            $plf = new PunchListFactory();
            $plf->getByUserDateIdAndTypeId($this->getUserDateId(), 30); //Only Break punches
            if ($plf->getRecordCount() > 0) {
                $pair = 0;
                $x = 0;
                $out_for_break = FALSE;
                foreach ($plf->rs as $p_obj) {
                    $plf->data = (array) $p_obj;
                    $p_obj = $plf;
                    if ($p_obj->getStatus() == 20 AND $p_obj->getType() == 30) {
                        $break_out_timestamp = $p_obj->getTimeStamp();
                        $out_for_break = TRUE;
                    } elseif ($out_for_break == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 30) {
                        $break_punch_arr[$pair][20] = $break_out_timestamp;
                        $break_punch_arr[$pair][10] = $p_obj->getTimeStamp();
                        $out_for_break = FALSE;
                        $pair++;
                        unset($break_out_timestamp);
                    } else {
                        $out_for_break = FALSE;
                    }

                    $x++;
                }

                if (isset($break_punch_arr)) {
                    foreach ($break_punch_arr as $punch_control_id => $time_stamp_arr) {
                        if (isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20])) {
                            $break_overall_total_time = bcadd($break_overall_total_time, bcsub($time_stamp_arr[10], $time_stamp_arr[20]));
                            $break_total_time_arr[] = bcsub($time_stamp_arr[10], $time_stamp_arr[20]);
                        } else {
                            Debug::text(' Break Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
                        }
                    }
                } else {
                    Debug::text(' No Break Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
                }
            }
            //Debug::Arr($break_punch_arr, ' Break Punch Arr: ', __FILE__, __LINE__, __METHOD__, 10);
            //Debug::Arr($break_total_time_arr, ' Break Total Time Arr: ', __FILE__, __LINE__, __METHOD__, 10);
            Debug::text(' Break Overall Total Time: ' . $break_overall_total_time, __FILE__, __LINE__, __METHOD__, 10);

            $remaining_break_time = $break_overall_total_time;

            $i = 0;
            foreach ($bplf->rs as $break_policy_obj) {
                $bplf->data = (array) $break_policy_obj;
                $break_policy_obj = $bplf;
                $break_policy_time = 0;
                if (!isset($break_total_time_arr[$i])) {
                    $break_total_time_arr[$i] = 0; //Prevent PHP warnings.
                }

                //This is the time that can be considered for the break.
                if ($break_policy_obj->getIncludeMultipleBreaks() == TRUE) {
                    //If only one break policy is defined (say 30min auto-add after 0hrs w/include punch time)
                    //and the employee punches out for two breaks, one for 10mins and one for 15mins, only the first break will be added back in.
                    //Because TimeTrex tries to match each break to a specific break policy.
                    //getIncludeMultipleBreaks(): is the flag that ignores how many breaks there are in total,
                    //and just combines any breaks together that fall within the active after time.
                    //So it doesn't matter if the employee takes 1 break or 30, they are all combined into one after the active_after time.
                    //FIXME: Handle cases where one break policy includes multiples and another one does not. Currently the break time may be doubled up in this case.
                    $eligible_break_total_time = array_sum($break_total_time_arr);
                    Debug::text(' Including multiple breaks...', __FILE__, __LINE__, __METHOD__, 10);
                } else {
                    $eligible_break_total_time = $break_total_time_arr[$i];
                }

                Debug::text('Break Policy ID: ' . $break_policy_obj->getId() . ' Type ID: ' . $break_policy_obj->getType() . ' Break Total Time: ' . $eligible_break_total_time . ' Amount: ' . $break_policy_obj->getAmount() . ' Daily Total Time: ' . $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);
                switch ($break_policy_obj->getType()) {
                    case 10: //Auto-Deduct
                        Debug::text(' Break AutoDeduct...', __FILE__, __LINE__, __METHOD__, 10);
                        if ($break_policy_obj->getIncludeBreakPunchTime() == TRUE) {
                            $break_policy_time = bcsub($break_policy_obj->getAmount(), $eligible_break_total_time) * -1;
                            //If they take more then their alloted break, zero it out so time isn't added.
                            if ($break_policy_time > 0) {
                                $break_policy_time = 0;
                            }
                        } else {
                            $break_policy_time = $break_policy_obj->getAmount() * -1;
                        }
                        break;
                    case 15: //Auto-Include
                        Debug::text(' Break AutoAdd...', __FILE__, __LINE__, __METHOD__, 10);
                        if ($break_policy_obj->getIncludeBreakPunchTime() == TRUE) {
                            if ($eligible_break_total_time > $break_policy_obj->getAmount()) {
                                $break_policy_time = $break_policy_obj->getAmount();
                            } else {
                                $break_policy_time = $eligible_break_total_time;
                            }
                        } else {
                            $break_policy_time = $break_policy_obj->getAmount();
                        }
                        break;
                }

                if ($break_policy_obj->getIncludeBreakPunchTime() == TRUE AND $break_policy_time > $remaining_break_time) {
                    $break_policy_time = $remaining_break_time;
                }
                if ($break_policy_obj->getIncludeBreakPunchTime() == TRUE) { //Handle cases where some break policies include punch time, and others don't.
                    $remaining_break_time -= $break_policy_time;
                }

                Debug::text(' Break Policy Total Time: ' . $break_policy_time . ' Break Policy ID: ' . $break_policy_obj->getId() . ' Remaining Time: ' . $remaining_break_time, __FILE__, __LINE__, __METHOD__, 10);

                if ($break_policy_time != 0) {
                    $break_policy_total_time = bcadd($break_policy_total_time, $break_policy_time);

                    $udtf = new UserDateTotalFactory();
                    $udtf->setUserDateID($this->getUserDateID());
                    $udtf->setStatus(10); //System
                    $udtf->setType(110); //Break
                    $udtf->setBreakPolicyId($break_policy_obj->getId());
                    $udtf->setBranch($this->getUserDateObject()->getUserObject()->getDefaultBranch());
                    $udtf->setDepartment($this->getUserDateObject()->getUserObject()->getDefaultDepartment());

                    $udtf->setTotalTime($break_policy_time);
                    $udtf->setEnableCalcSystemTotalTime(FALSE);
                    if ($udtf->isValid() == TRUE) {
                        $udtf->Save();
                    }
                    unset($udtf);
                }

                Debug::text(' bBreak Policy Total Time: ' . $break_policy_time . ' Break Policy ID: ' . $break_policy_obj->getId() . ' Remaining Time: ' . $remaining_break_time, __FILE__, __LINE__, __METHOD__, 10);

                $i++;
            }
        } else {
            Debug::text(' No Break Policy found, or not after break policy trigger time yet...', __FILE__, __LINE__, __METHOD__, 10);
        }

        Debug::text(' Final Break Policy Total Time: ' . $break_policy_total_time, __FILE__, __LINE__, __METHOD__, 10);

        return $break_policy_total_time;
    }

    function calcAccrualPolicy() {
        //FIXME: There is a minor bug for hour based accruals that if a milestone has a maximum limit,
        //  and an employee recalculates there timesheet, and the limit is reached midweek, if its recalculated
        //  again, the days that get the accrual time won't always be in order because the accrual balance is deleted
        //  only for the day currently being calculated, so on Monday it will delete 1hr of accrual, but the balance will
        //  still include Tue,Wed,Thu and the limit may already be reached.
        //We still need to calculate accruals even if the total time is 0, because we may want to override a
        //policy to 0hrs, and if we skip entries with TotalTime() == 0, the accruals won't be updated.
        if ($this->getDeleted() == FALSE) {
            Debug::text('Calculating Accrual Policies... Total Time: ' . $this->getTotalTime() . ' Date: ' . TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp()), __FILE__, __LINE__, __METHOD__, 10);

            //Calculate accrual policies assigned to other overtime/premium/absence policies
            //Debug::text('ID: '. $this->getId() .' Overtime Policy ID: '. (int)$this->getOverTimePolicyID()  .' Premium Policy ID: '. (int)$this->getPremiumPolicyID() .' Absence Policy ID: '. (int)$this->getAbsencePolicyID(), __FILE__, __LINE__, __METHOD__, 10);
            //If overtime, premium or absence policy is an accrual, handle that now.
            if ($this->getOverTimePolicyID() != FALSE) {
                $accrual_policy_id = $this->getOverTimePolicyObject()->getAccrualPolicyID();
                Debug::text('Over Time Accrual Policy ID: ' . $accrual_policy_id, __FILE__, __LINE__, __METHOD__, 10);

                if ($accrual_policy_id > 0) {
                    Debug::text('Over Time Accrual Rate: ' . $this->getOverTimePolicyObject()->getAccrualRate() . ' Policy ID: ' . $this->getOverTimePolicyObject()->getAccrualPolicyID(), __FILE__, __LINE__, __METHOD__, 10);
                    $af = new AccrualFactory();
                    $af->setUser($this->getUserDateObject()->getUser());
                    $af->setAccrualPolicyID($accrual_policy_id);
                    $af->setTimeStamp($this->getUserDateObject()->getDateStamp());
                    $af->setUserDateTotalID($this->getID());

                    $accrual_amount = bcmul($this->getTotalTime(), $this->getOverTimePolicyObject()->getAccrualRate());
                    if ($accrual_amount > 0) {
                        $af->setType(10); //Banked
                    } else {
                        $af->setType(20); //Used
                    }
                    $af->setAmount($accrual_amount);
                    $af->setEnableCalcBalance(TRUE);
                    if ($af->isValid()) {
                        $af->Save();
                    }

                    unset($accrual_amount);
                } else {
                    Debug::text('Skipping Over Time Accrual Policy ID: ' . $accrual_policy_id, __FILE__, __LINE__, __METHOD__, 10);
                }
            }
            if ($this->getPremiumPolicyID() != FALSE) {
                $accrual_policy_id = $this->getPremiumPolicyObject()->getAccrualPolicyID();
                Debug::text('Premium Accrual Policy ID: ' . $accrual_policy_id, __FILE__, __LINE__, __METHOD__, 10);

                if ($accrual_policy_id > 0) {
                    $af = new AccrualFactory();
                    $af->setUser($this->getUserDateObject()->getUser());
                    $af->setAccrualPolicyID($accrual_policy_id);
                    $af->setTimeStamp($this->getUserDateObject()->getDateStamp());
                    $af->setUserDateTotalID($this->getID());

                    $accrual_amount = bcmul($this->getTotalTime(), $this->getPremiumPolicyObject()->getAccrualRate());
                    if ($accrual_amount > 0) {
                        $af->setType(10); //Banked
                    } else {
                        $af->setType(20); //Used
                    }
                    $af->setAmount($accrual_amount);
                    $af->setEnableCalcBalance(TRUE);
                    if ($af->isValid()) {
                        $af->Save();
                    }

                    unset($accrual_amount);
                }
            }
            if ($this->getAbsencePolicyID() != FALSE) {
                $accrual_policy_id = $this->getAbsencePolicyObject()->getId();
                Debug::text('Absence Accrual Policy ID: ' . $accrual_policy_id, __FILE__, __LINE__, __METHOD__, 10);

                if ($accrual_policy_id > 0) {
                    $af = new AccrualFactory();
                    $af->setUser($this->getUserDateObject()->getUser());
                    $af->setAccrualPolicyID($accrual_policy_id);
                    $af->setTimeStamp($this->getUserDateObject()->getDateStamp());
                    $af->setUserDateTotalID($this->getID());

                    //By default we withdraw from accrual policy, so if there is a negative rate, deposit instead.
                    //$accrual_amount = bcmul( $this->getTotalTime(), bcmul( $this->getAbsencePolicyObject()->getAccrualRate(), -1 ) );

                    $accrual_amount = $this->getTotalTime();

                    if ($accrual_amount > 0) {
                        $af->setType(10); //Banked
                    } else {
                        $af->setType(20); //Used
                    }
                    $af->setAmount($accrual_amount);

                    $af->setEnableCalcBalance(TRUE);
                    if ($af->isValid()) {
                        //$af->Save();
                    }
                }
            }
            unset($af, $accrual_policy_id);


            //Calculate any hour based accrual policies.
            //if ( $this->getType() == 10 AND $this->getStatus() == 10 ) {
            if ($this->getStatus() == 10 AND in_array($this->getType(), array(20, 30))) { //Calculate hour based accruals on regular/overtime only.
                $aplf = new AccrualPolicyListFactory();
                $aplf->getByPolicyGroupUserIdAndType($this->getUserDateObject()->getUser(), 30);
                if ($aplf->getRecordCount() > 0) {
                    Debug::text('Found Hour Based Accrual Policies to apply.', __FILE__, __LINE__, __METHOD__, 10);
                    foreach ($aplf->rs as $ap_obj) {
                        $aplf->data = (array) $ap_obj;
                        $ap_obj = $aplf;

                        if ($ap_obj->getMinimumEmployedDays() == 0
                                OR TTDate::getDays(($this->getUserDateObject()->getDateStamp() - $this->getUserDateObject()->getUserObject()->getHireDate())) >= $ap_obj->getMinimumEmployedDays()) {
                            Debug::Text('&nbsp;&nbsp;User has been employed long enough.', __FILE__, __LINE__, __METHOD__, 10);

                            $milestone_obj = $ap_obj->getActiveMilestoneObject($this->getUserDateObject()->getUserObject(), $this->getUserDateObject()->getDateStamp());
                            $accrual_balance = $ap_obj->getCurrentAccrualBalance($this->getUserDateObject()->getUserObject()->getId(), $ap_obj->getId());

                            //If Maximum time is set to 0, make that unlimited.
                            if (is_object($milestone_obj) AND ( $milestone_obj->getMaximumTime() == 0 OR $accrual_balance < $milestone_obj->getMaximumTime() )) {
                                $accrual_amount = $ap_obj->calcAccrualAmount($milestone_obj, $this->getTotalTime(), 0);

                                if ($accrual_amount > 0) {
                                    $new_accrual_balance = bcadd($accrual_balance, $accrual_amount);

                                    //If Maximum time is set to 0, make that unlimited.
                                    if ($milestone_obj->getMaximumTime() > 0 AND $new_accrual_balance > $milestone_obj->getMaximumTime()) {
                                        $accrual_amount = bcsub($milestone_obj->getMaximumTime(), $accrual_balance, 4);
                                    }
                                    Debug::Text('&nbsp;&nbsp; Min/Max Adjusted Accrual Amount: ' . $accrual_amount . ' Limits: Min: ' . $milestone_obj->getMinimumTime() . ' Max: ' . $milestone_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

                                    $af = new AccrualFactory();
                                    $af->setUser($this->getUserDateObject()->getUserObject()->getId());
                                    $af->setType(75); //Accrual Policy
                                    $af->setAccrualPolicyID($ap_obj->getId());
                                    $af->setUserDateTotalID($this->getID());
                                    $af->setAmount($accrual_amount);
                                    $af->setTimeStamp($this->getUserDateObject()->getDateStamp());
                                    $af->setEnableCalcBalance(TRUE);

                                    if ($af->isValid()) {
                                        $af->Save();
                                    }
                                    unset($accrual_amount, $accrual_balance, $new_accrual_balance);
                                } else {
                                    Debug::Text('&nbsp;&nbsp; Accrual Amount is 0...', __FILE__, __LINE__, __METHOD__, 10);
                                }
                            } else {
                                Debug::Text('&nbsp;&nbsp; Accrual Balance is outside Milestone Range. Or no milestone found. Skipping...', __FILE__, __LINE__, __METHOD__, 10);
                            }
                        } else {
                            Debug::Text('&nbsp;&nbsp;User has only been employed: ' . TTDate::getDays(($this->getUserDateObject()->getDateStamp() - $this->getUserDateObject()->getUserObject()->getHireDate())) . ' Days, not enough.', __FILE__, __LINE__, __METHOD__, 10);
                        }
                    }
                } else {
                    Debug::text('No Hour Based Accrual Policies to apply.', __FILE__, __LINE__, __METHOD__, 10);
                }
            } else {
                Debug::text('No worked time on this day or not proper type/status, skipping hour based accrual policies...', __FILE__, __LINE__, __METHOD__, 10);
            }
        }

        return TRUE;
    }

    function calcSystemTotalTime() {
        global $profiler;

        $profiler->startTimer('UserDateTotal::calcSystemTotalTime() - Part 1');

        if (is_object($this->getUserDateObject())
                AND is_object($this->getUserDateObject()->getPayPeriodObject())
                AND $this->getUserDateObject()->getPayPeriodObject()->getStatus() == 20) {
            Debug::text(' Pay Period is closed!', __FILE__, __LINE__, __METHOD__, 10);
            return FALSE;
        }

        //Take the worked hours, and calculate Total,Regular,Overtime,Premium hours from that.
        //This is where many of the policies will be applied
        //Such as any meal/overtime/premium policies.
        $return_value = FALSE;

        $udtlf = new UserDateTotalListFactory();

        $this->deleteSystemTotalTime();

        //We can't assign a dock absence to a given branch/dept automatically,
        //Because several punches with different branches could fall within a schedule punch pair.
        //Just total up entire day, and entire scheduled time to see if we're over/under
        //FIXME: Handle multiple schedules on a single day better.
        $schedule_total_time = 0;
        $meal_policy_obj = NULL;
        $slf = new ScheduleListFactory();

        $profiler->startTimer('UserDateTotal::calcSystemTotalTime() - Holiday');
        //Check for Holidays
        $holiday_time = 0;
        $hlf = new HolidayListFactory();
        $hlf->getByPolicyGroupUserIdAndDate($this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp());
        if ($hlf->getRecordCount() > 0) {
            $holiday_obj = $hlf->getCurrent();
            Debug::text(' Found Holiday: ' . $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

            if ($holiday_obj->isEligible($this->getUserDateObject()->getUser())) {
                Debug::text(' User is Eligible for Holiday: ' . $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

                $holiday_time = $holiday_obj->getHolidayTime($this->getUserDateObject()->getUser());
                Debug::text(' User average time for Holiday: ' . TTDate::getHours($holiday_time), __FILE__, __LINE__, __METHOD__, 10);

                if ($holiday_time > 0 AND $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyID() != FALSE) {
                    Debug::text(' Adding Holiday hours: ' . TTDate::getHours($holiday_time), __FILE__, __LINE__, __METHOD__, 10);
                    $udtf = new UserDateTotalFactory();
                    $udtf->setUserDateID($this->getUserDateID());
                    $udtf->setStatus(30); //Absence
                    $udtf->setType(10); //Total
                    $udtf->setBranch($this->getUserDateObject()->getUserObject()->getDefaultBranch());
                    $udtf->setDepartment($this->getUserDateObject()->getUserObject()->getDefaultDepartment());
                    $udtf->setAbsencePolicyID($holiday_obj->getHolidayPolicyObject()->getAbsencePolicyID());
                    $udtf->setTotalTime($holiday_time);
                    $udtf->setEnableCalcSystemTotalTime(FALSE);
                    if ($udtf->isValid()) {
                        $udtf->Save();
                    }
                }
            }

            $slf->getByUserDateIdAndStatusId($this->getUserDateID(), 20);
            $schedule_absence_total_time = 0;
            if ($slf->getRecordCount() > 0) {
                //Check for schedule policy
                foreach ($slf->rs as $s_obj) {
                    $slf->data = (array) $s_obj;
                    $s_obj = $slf;
                    Debug::text(' Schedule Absence Total Time: ' . $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);

                    $schedule_absence_total_time += $s_obj->getTotalTime();
                    if (is_object($s_obj->getSchedulePolicyObject()) AND $s_obj->getSchedulePolicyObject()->getAbsencePolicyID() > 0) {
                        $holiday_absence_policy_id = $s_obj->getSchedulePolicyObject()->getAbsencePolicyID();
                        Debug::text(' Found Absence Policy for docking: ' . $holiday_absence_policy_id, __FILE__, __LINE__, __METHOD__, 10);
                    } else {
                        Debug::text(' NO Absence Policy : ', __FILE__, __LINE__, __METHOD__, 10);
                    }
                }
            }

            $holiday_total_under_time = $schedule_absence_total_time - $holiday_time;
            if (isset($holiday_absence_policy_id) AND $holiday_total_under_time > 0) {
                Debug::text(' Schedule Under Time Case: ' . $holiday_total_under_time, __FILE__, __LINE__, __METHOD__, 10);
                $udtf = new UserDateTotalFactory();
                $udtf->setUserDateID($this->getUserDateID());
                $udtf->setStatus(30); //Absence
                $udtf->setType(10); //Total
                $udtf->setBranch($this->getUserDateObject()->getUserObject()->getDefaultBranch());
                $udtf->setDepartment($this->getUserDateObject()->getUserObject()->getDefaultDepartment());
                $udtf->setAbsencePolicyID($holiday_absence_policy_id);
                $udtf->setTotalTime($holiday_total_under_time);
                $udtf->setEnableCalcSystemTotalTime(FALSE);
                if ($udtf->isValid()) {
                    $udtf->Save();
                }
            }
            unset($holiday_total_under_time, $holiday_absence_policy_id, $schedule_absence_total_time);
        }
        $profiler->stopTimer('UserDateTotal::calcSystemTotalTime() - Holiday');

        //Do this after holiday policies have been applied, so if someone
        //schedules a holiday manually, we don't double up on the time.
        $slf->getByUserDateId($this->getUserDateID());
        if ($slf->getRecordCount() > 0) {
            //Check for schedule policy
            foreach ($slf->rs as $s_obj) {
                $slf->data = (array) $s_obj;
                $s_obj = $slf;
                Debug::text(' Schedule Total Time: ' . $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
                if ($s_obj->getStatus() == 20 AND $s_obj->getAbsencePolicyID() != '') {
                    Debug::text(' Scheduled Absence Found of Total Time: ' . $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);

                    //If a holiday policy is applied on this day, ignore the schedule so we don't duplicate it.
                    //We could take the difference, and use the greatest of the two,
                    //But I think that will just open the door for errors.
                    if (!isset($holiday_obj) OR ( $holiday_time == 0 AND is_object($holiday_obj) AND $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyID() != $s_obj->getAbsencePolicyID() )) {
                        $udtf = new UserDateTotalFactory();
                        $udtf->setUserDateID($this->getUserDateID());
                        $udtf->setStatus(30); //Absence
                        $udtf->setType(10); //Total
                        $udtf->setBranch($s_obj->getBranch());
                        $udtf->setDepartment($s_obj->getDepartment());
                        $udtf->setJob($s_obj->getJob());
                        $udtf->setJobItem($s_obj->getJobItem());
                        $udtf->setAbsencePolicyID($s_obj->getAbsencePolicyID());
                        $udtf->setTotalTime($s_obj->getTotalTime());
                        $udtf->setEnableCalcSystemTotalTime(FALSE);
                        if ($udtf->isValid()) {
                            $udtf->Save();
                        }
                    } else {
                        Debug::text(' Holiday Time Found, ignoring schedule!', __FILE__, __LINE__, __METHOD__, 10);
                    }
                } elseif ($s_obj->getStatus() == 10) {
                    $schedule_total_time += $s_obj->getTotalTime();
                    if (is_object($s_obj->getSchedulePolicyObject())) {
                        $schedule_absence_policy_id = $s_obj->getSchedulePolicyObject()->getAbsencePolicyID();
                        $meal_policy_obj = $s_obj->getSchedulePolicyObject()->getMealPolicyObject();
                        Debug::text(' Found Absence Policy for docking: ' . $schedule_absence_policy_id, __FILE__, __LINE__, __METHOD__, 10);
                    } else {
                        Debug::text(' NO Absence Policy : ', __FILE__, __LINE__, __METHOD__, 10);
                    }
                }
            }
        } else {
            Debug::text(' No Schedules found. ', __FILE__, __LINE__, __METHOD__, 10);
        }
        unset($s_obj);
        unset($holiday_time, $holiday_obj);

        //Handle Meal Policy time.
        //Do this after schedule meal policies have been looked up, as those override any policy group meal policies.
        $meal_policy_time = $this->calcMealPolicyTotalTime($meal_policy_obj);
        $udt_meal_policy_adjustment_arr = $this->calcUserTotalMealPolicyAdjustment($meal_policy_time);
        //Debug::Arr($udt_meal_policy_adjustment_arr, 'UserDateTotal Meal Policy Adjustment: ', __FILE__, __LINE__, __METHOD__,10);

        $break_policy_time = $this->calcBreakPolicyTotalTime();
        $udt_break_policy_adjustment_arr = $this->calcUserTotalBreakPolicyAdjustment($break_policy_time);
        //Debug::Arr($udt_break_policy_adjustment_arr, 'UserDateTotal Break Policy Adjustment: ', __FILE__, __LINE__, __METHOD__,10);

        $daily_total_time = $this->getDailyTotalTime();
        Debug::text(' Daily Total Time: ' . $daily_total_time . ' Schedule Total Time: ' . $schedule_total_time, __FILE__, __LINE__, __METHOD__, 10);

        //Check for overtime policies or undertime absence policies
        if ($daily_total_time > $schedule_total_time) {
            Debug::text(' Schedule Over Time Case: ', __FILE__, __LINE__, __METHOD__, 10);
        } elseif (isset($schedule_absence_policy_id) AND $schedule_absence_policy_id != '' AND $daily_total_time < $schedule_total_time) {
            $total_under_time = bcsub($schedule_total_time, $daily_total_time);

            if ($total_under_time > 0) {
                Debug::text(' Schedule Under Time Case: ' . $total_under_time . ' Absence Policy ID: ' . $schedule_absence_policy_id, __FILE__, __LINE__, __METHOD__, 10);
                $udtf = new UserDateTotalFactory();
                $udtf->setUserDateID($this->getUserDateID());
                $udtf->setStatus(30); //Absence
                $udtf->setType(10); //Total
                $udtf->setBranch($this->getUserDateObject()->getUserObject()->getDefaultBranch());
                $udtf->setDepartment($this->getUserDateObject()->getUserObject()->getDefaultDepartment());
                $udtf->setAbsencePolicyID($schedule_absence_policy_id);
                $udtf->setTotalTime($total_under_time);
                $udtf->setEnableCalcSystemTotalTime(FALSE);
                if ($udtf->isValid()) {
                    $udtf->Save();
                }
            } else {
                Debug::text(' Schedule Under Time is a negative value, skipping dock time: ' . $total_under_time . ' Absence Policy ID: ' . $schedule_absence_policy_id, __FILE__, __LINE__, __METHOD__, 10);
            }
        } else {
            Debug::text(' No Dock Absenses', __FILE__, __LINE__, __METHOD__, 10);
        }
        unset($schedule_absence_policy_id);

        //Do this AFTER the UnderTime absence policy is submitted.
        $recalc_daily_total_time = $this->calcAbsencePolicyTotalTime();

        if ($recalc_daily_total_time == TRUE) {
            //Total up all "worked" hours for the day again, this time include
            //Paid Absences.
            $daily_total_time = $this->getDailyTotalTime();
            //$daily_total_time = $udtlf->getTotalSumByUserDateID( $this->getUserDateID() );
            Debug::text('ReCalc Daily Total Time for Day: ' . $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);
        }

        $profiler->stopTimer('UserDateTotal::calcSystemTotalTime() - Part 1');

        $user_data_total_compact_arr = $this->calcOverTimePolicyTotalTime($udt_meal_policy_adjustment_arr, $udt_break_policy_adjustment_arr);
        //Debug::Arr($user_data_total_compact_arr, 'User Data Total Compact Array: ', __FILE__, __LINE__, __METHOD__, 10);
        //print_r($user_data_total_compact_arr);
        //exit();
        //Insert User Date Total rows for each compacted array entry.
        //The reason for compacting is to reduce the amount of rows as much as possible.
        if (is_array($user_data_total_compact_arr)) {
            $profiler->startTimer('UserDateTotal::calcSystemTotalTime() - Part 2');

            Debug::text('Compact Array Exists: ', __FILE__, __LINE__, __METHOD__, 10);

            foreach ($user_data_total_compact_arr as $type_id => $udt_arr) {
                Debug::text('Compact Array Entry: Type ID: ' . $type_id, __FILE__, __LINE__, __METHOD__, 10);

                if ($type_id == 20) {
                    //Regular Time
                    //Debug::text('Compact Array Entry: Branch ID: '. $udt_arr[' , __FILE__, __LINE__, __METHOD__, 10);
                    foreach ($udt_arr as $branch_id => $branch_arr) {
                        //foreach($branch_arr as $department_id => $total_time ) {
                        foreach ($branch_arr as $department_id => $department_arr) {
                            foreach ($department_arr as $job_id => $job_arr) {
                                foreach ($job_arr as $job_item_id => $data_arr) {

                                    Debug::text('Compact Array Entry: Regular Time - Branch ID: ' . $branch_id . ' Department ID: ' . $department_id . ' Job ID: ' . $job_id . ' Job Item ID: ' . $job_item_id . ' Total Time: ' . $data_arr['total_time'], __FILE__, __LINE__, __METHOD__, 10);
                                    $user_data_total_expanded[] = array(
                                        'type_id' => $type_id,
                                        'over_time_policy_id' => NULL,
                                        'branch_id' => $branch_id,
                                        'department_id' => $department_id,
                                        'job_id' => $job_id,
                                        'job_item_id' => $job_item_id,
                                        'total_time' => $data_arr['total_time'],
                                        'quantity' => $data_arr['quantity'],
                                        'bad_quantity' => $data_arr['bad_quantity']
                                    );
                                }
                            }
                        }
                    }
                } else {
                    //Overtime
                    //Overtime array is completely different then regular time array!
                    foreach ($udt_arr as $over_time_policy_id => $policy_arr) {
                        foreach ($policy_arr as $branch_id => $branch_arr) {
                            //foreach($branch_arr as $department_id => $total_time ) {
                            foreach ($branch_arr as $department_id => $department_arr) {
                                foreach ($department_arr as $job_id => $job_arr) {
                                    foreach ($job_arr as $job_item_id => $data_arr) {

                                        Debug::text('Compact Array Entry: Policy ID: ' . $over_time_policy_id . ' Branch ID: ' . $branch_id . ' Department ID: ' . $department_id . ' Job ID: ' . $job_id . ' Job Item ID: ' . $job_item_id . ' Total Time: ' . $data_arr['total_time'], __FILE__, __LINE__, __METHOD__, 10);
                                        $user_data_total_expanded[] = array(
                                            'type_id' => $type_id,
                                            'over_time_policy_id' => $over_time_policy_id,
                                            'branch_id' => $branch_id,
                                            'department_id' => $department_id,
                                            'job_id' => $job_id,
                                            'job_item_id' => $job_item_id,
                                            'total_time' => $data_arr['total_time'],
                                            'quantity' => $data_arr['quantity'],
                                            'bad_quantity' => $data_arr['bad_quantity']
                                        );
                                    }
                                }
                            }
                        }
                    }
                }

                unset($policy_arr, $branch_arr, $department_arr, $job_arr, $over_time_policy_id, $branch_id, $department_id, $job_id, $job_item_id, $data_arr);
            }
            $profiler->stopTimer('UserDateTotal::calcSystemTotalTime() - Part 2');

            //var_dump($user_data_total_expanded);
            //Do the actual inserts now.
            if (isset($user_data_total_expanded)) {
                foreach ($user_data_total_expanded as $data_arr) {
                    $profiler->startTimer('UserDateTotal::calcSystemTotalTime() - Part 2b');

                    Debug::text('Inserting from expanded array, Type ID: ' . $data_arr['type_id'], __FILE__, __LINE__, __METHOD__, 10);
                    $udtf = new UserDateTotalFactory();
                    $udtf->setUserDateID($this->getUserDateID());
                    $udtf->setStatus(10); //System
                    $udtf->setType($data_arr['type_id']);
                    if (isset($data_arr['over_time_policy_id'])) {
                        $udtf->setOverTimePolicyId($data_arr['over_time_policy_id']);
                    }

                    $udtf->setBranch($data_arr['branch_id']);
                    $udtf->setDepartment($data_arr['department_id']);
                    $udtf->setJob($data_arr['job_id']);
                    $udtf->setJobItem($data_arr['job_item_id']);

                    $udtf->setQuantity($data_arr['quantity']);
                    $udtf->setBadQuantity($data_arr['bad_quantity']);

                    $udtf->setTotalTime($data_arr['total_time']);
                    $udtf->setEnableCalcSystemTotalTime(FALSE);
                    if ($udtf->isValid()) {
                        $udtf->Save();
                    } else {
                        Debug::text('aINVALID UserDateTotal Entry!!: ', __FILE__, __LINE__, __METHOD__, 10);
                    }

                    $profiler->stopTimer('UserDateTotal::calcSystemTotalTime() - Part 2b');
                }
                unset($user_data_total_expanded);
            }
        } else {
            $profiler->startTimer('UserDateTotal::calcSystemTotalTime() - Part 3');

            //We need to break this out by branch, dept, job, task
            $udtlf = new UserDateTotalListFactory();

            //FIXME: Should Absence time be included as "regular time". We do this on
            //the timesheet view manually as of 12-Jan-06. If we included it in the
            //regular time system totals, we wouldn't have to do it manually.
            //$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), array(20,30) );
            $udtlf->getByUserDateIdAndStatus($this->getUserDateID(), array(20));
            if ($udtlf->getRecordCount() > 0) {
                Debug::text('Found Total Hours for just regular time: Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
                $user_date_regular_time_compact_arr = NULL;
                foreach ($udtlf->rs as $udt_obj) {
                    $udtlf->data = (array) $udt_obj;
                    $udt_obj = $udtlf;
                    //Create compact array, so we don't make as many system entries.
                    //Check if this is a paid absence or not.
                    if ($udt_obj->getStatus() == 20 AND $udt_obj->getTotalTime() > 0) {

                        $udt_total_time = $udt_obj->getTotalTime();
                        if (isset($udt_meal_policy_adjustment_arr[$udt_obj->getId()])) {
                            $udt_total_time = bcadd($udt_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()]);
                        }
                        if (isset($udt_break_policy_adjustment_arr[$udt_obj->getId()])) {
                            $udt_total_time = bcadd($udt_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()]);
                        }

                        if (isset($user_date_regular_time_compact_arr[(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()])) {
                            Debug::text('&nbsp;&nbsp;&nbsp;&nbsp; Adding to Compact Array: Regular Time -  Branch: ' . (int) $udt_obj->getBranch() . ' Department: ' . (int) $udt_obj->getDepartment(), __FILE__, __LINE__, __METHOD__, 10);
                            $user_date_regular_time_compact_arr[(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['total_time'] += $udt_total_time;
                            $user_date_regular_time_compact_arr[(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['quantity'] += $udt_obj->getQuantity();
                            $user_date_regular_time_compact_arr[(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()]['bad_quantity'] += $udt_obj->getBadQuantity();
                        } else {
                            $user_date_regular_time_compact_arr[(int) $udt_obj->getBranch()][(int) $udt_obj->getDepartment()][(int) $udt_obj->getJob()][(int) $udt_obj->getJobItem()] = array('total_time' => $udt_total_time, 'quantity' => $udt_obj->getQuantity(), 'bad_quantity' => $udt_obj->getBadQuantity());
                        }
                        unset($udt_total_time);
                    } else {
                        Debug::text('Total Time is 0!!: ' . $udt_obj->getTotalTime() . ' Or its an UNPAID absence: ' . $udt_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
                    }
                }

                if (isset($user_date_regular_time_compact_arr)) {
                    foreach ($user_date_regular_time_compact_arr as $branch_id => $branch_arr) {
                        //foreach($branch_arr as $department_id => $total_time ) {
                        foreach ($branch_arr as $department_id => $department_arr) {
                            foreach ($department_arr as $job_id => $job_arr) {
                                foreach ($job_arr as $job_item_id => $data_arr) {

                                    Debug::text('Compact Array Entry: bRegular Time - Branch ID: ' . $branch_id . ' Department ID: ' . $department_id . ' Job ID: ' . $job_id . ' Job Item ID: ' . $job_item_id . ' Total Time: ' . $data_arr['total_time'], __FILE__, __LINE__, __METHOD__, 10);

                                    $udtf = new UserDateTotalFactory();
                                    $udtf->setUserDateID($this->getUserDateID());
                                    $udtf->setStatus(10); //System
                                    $udtf->setType(20); //Regular

                                    $udtf->setBranch($branch_id);
                                    $udtf->setDepartment($department_id);

                                    $udtf->setJob($job_id);
                                    $udtf->setJobItem($job_item_id);

                                    $udtf->setQuantity($data_arr['quantity']);
                                    $udtf->setBadQuantity($data_arr['bad_quantity']);

                                    $udtf->setTotalTime($data_arr['total_time']);
                                    $udtf->setEnableCalcSystemTotalTime(FALSE);
                                    $udtf->Save();
                                }
                            }
                        }
                    }
                }
                unset($user_date_regular_time_compact_arr);
            }
        }

        //Handle Premium time.
        $this->calcPremiumPolicyTotalTime($udt_meal_policy_adjustment_arr, $udt_break_policy_adjustment_arr, $daily_total_time);

        //Total Hours
        $udtf = new UserDateTotalFactory();
        $udtf->setUserDateID($this->getUserDateID());
        $udtf->setStatus(10); //System
        $udtf->setType(10); //Total
        $udtf->setTotalTime($daily_total_time);
        $udtf->setEnableCalcSystemTotalTime(FALSE);
        if ($udtf->isValid()) {
            $return_value = $udtf->Save();
        } else {
            $return_value = FALSE;
        }

        $profiler->stopTimer('UserDateTotal::calcSystemTotalTime() - Part 3');

        if ($this->getEnableCalcException() == TRUE) {
            $epf = new ExceptionPolicyFactory;
            $epf->calcExceptions($this->getUserDateID(), $this->getEnablePreMatureException());
        }

        return $return_value;
    }

    function calcWeeklySystemTotalTime() {
        if ($this->getEnableCalcWeeklySystemTotalTime() == TRUE) {
            global $profiler;

            $profiler->startTimer('UserDateTotal::postSave() - reCalculateRange 1');

            //Get Pay Period Schedule info
            if (is_object($this->getUserDateObject()->getPayPeriodObject())
                    AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject())) {
                $start_week_day_id = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
            } else {
                $start_week_day_id = 0;
            }
            Debug::text('Start Week Day ID: ' . $start_week_day_id . ' Date Stamp: ' . TTDate::getDate('DATE+TIME', $this->getUserDateObject()->getDateStamp()), __FILE__, __LINE__, __METHOD__, 10);

            UserDateTotalFactory::reCalculateRange($this->getUserDateObject()->getUser(), ($this->getUserDateObject()->getDateStamp() + 86400), TTDate::getEndWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id));
            unset($start_week_day_id);

            $profiler->stopTimer('UserDateTotal::postSave() - reCalculateRange 1');
            return TRUE;
        }

        return FALSE;
    }

    function getHolidayUserDateIDs() {
        Debug::text('reCalculating Holiday...', __FILE__, __LINE__, __METHOD__, 10);

        //Get Holiday policies and determine how many days we need to look ahead/behind in order
        //to recalculate the holiday eligilibility/time.
        $holiday_before_days = 0;
        $holiday_after_days = 0;

        $hplf = new HolidayPolicyListFactory();
        $hplf->getByCompanyId($this->getUserDateObject()->getUserObject()->getCompany());
        if ($hplf->getRecordCount() > 0) {
            foreach ($hplf->rs as $hp_obj) {
                $hplf->data = (array) $hp_obj;
                $hp_obj = $hplf;

                if ($hp_obj->getMinimumWorkedPeriodDays() > $holiday_before_days) {
                    $holiday_before_days = $hp_obj->getMinimumWorkedPeriodDays();
                }
                if ($hp_obj->getAverageTimeDays() > $holiday_before_days) {
                    $holiday_before_days = $hp_obj->getAverageTimeDays();
                }
                if ($hp_obj->getMinimumWorkedAfterPeriodDays() > $holiday_after_days) {
                    $holiday_after_days = $hp_obj->getMinimumWorkedAfterPeriodDays();
                }
            }
        }
        Debug::text('Holiday Before Days: ' . $holiday_before_days . ' Holiday After Days: ' . $holiday_after_days, __FILE__, __LINE__, __METHOD__, 10);

        if ($holiday_before_days > 0 OR $holiday_after_days > 0) {
            $retarr = array();

            $search_start_date = TTDate::getBeginWeekEpoch(($this->getUserDateObject()->getDateStamp() - ($holiday_after_days * 86400)));
            $search_end_date = TTDate::getEndWeekEpoch(TTDate::getEndDayEpoch($this->getUserDateObject()->getDateStamp()) + ($holiday_before_days * 86400) + 3601);
            Debug::text('Holiday search start date: ' . TTDate::getDate('DATE', $search_start_date) . ' End date: ' . TTDate::getDate('DATE', $search_end_date) . ' Current Date: ' . TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp()), __FILE__, __LINE__, __METHOD__, 10);

            $hlf = new HolidayListFactory();
            //$hlf->getByPolicyGroupUserIdAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), TTDate::getEndWeekEpoch( $this->getUserDateObject()->getDateStamp() )+86400, TTDate::getEndDayEpoch()+($max_average_time_days*86400)+3601 );
            $hlf->getByPolicyGroupUserIdAndStartDateAndEndDate($this->getUserDateObject()->getUser(), $search_start_date, $search_end_date);
            if ($hlf->getRecordCount() > 0) {
                Debug::text('Found Holidays within range: ' . $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                $udlf = new UserDateListFactory();
                foreach ($hlf->rs as $h_obj) {
                    $hlf->data = (array) $h_obj;
                    $h_obj = $hlf;
                    Debug::text('ReCalculating Day due to Holiday: ' . TTDate::getDate('DATE', $h_obj->getDateStamp()), __FILE__, __LINE__, __METHOD__, 10);
                    $user_date_ids = $udlf->getArrayByListFactory($udlf->getByUserIdAndDate($this->getUserDateObject()->getUser(), $h_obj->getDateStamp()));
                    if (is_array($user_date_ids)) {
                        $retarr = array_merge($retarr, $user_date_ids);
                    }
                    unset($user_date_ids);
                }
            }
        }

        if (isset($retarr) AND is_array($retarr) AND count($retarr) > 0) {
            //Debug::Arr($retarr, 'Holiday UserDateIDs: ', __FILE__, __LINE__, __METHOD__, 10);
            return $retarr;
        }

        Debug::text('No Holidays within range...', __FILE__, __LINE__, __METHOD__, 10);
        return FALSE;
    }

    static function reCalculateDay($user_date_id, $enable_exception = FALSE, $enable_premature_exceptions = FALSE, $enable_future_exceptions = TRUE, $enable_holidays = FALSE) {
        Debug::text('Re-calculating User Date ID: ' . $user_date_id . ' Enable Exception: ' . (int) $enable_exception, __FILE__, __LINE__, __METHOD__, 10);
        $udtf = new UserDateTotalFactory();
        $udtf->setUserDateId($user_date_id);
        $udtf->calcSystemTotalTime();

        if ($enable_holidays == TRUE) {
            $holiday_user_date_ids = $udtf->getHolidayUserDateIDs();
            if (is_array($holiday_user_date_ids)) {
                foreach ($holiday_user_date_ids as $holiday_user_date_id) {
                    Debug::Text('reCalculating Holiday...', __FILE__, __LINE__, __METHOD__, 10);
                    if ($user_date_id != $holiday_user_date_id) { //Don't recalculate the same day twice.
                        UserDateTotalFactory::reCalculateDay($holiday_user_date_id, FALSE, FALSE, FALSE, FALSE);
                    }
                }
            }
            unset($holiday_user_date_ids, $holiday_user_date_id);
        }

        if (!isset(self::$calc_exception) AND $enable_exception == TRUE) {
            $epf = new ExceptionPolicyFactory;
            $epf->calcExceptions($user_date_id, $enable_premature_exceptions, $enable_future_exceptions);
        }

        return TRUE;
    }

    static function reCalculateRange($user_id, $start_date, $end_date) {
        Debug::text('Re-calculating Range for User: ' . $user_id . ' Start: ' . $start_date . ' End: ' . $end_date, __FILE__, __LINE__, __METHOD__, 10);

        $udlf = new UserDateListFactory();
        $udlf->getByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date);
        if ($udlf->getRecordCount() > 0) {
            Debug::text('Found days to re-calculate: ' . $udlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

            $udlf->StartTransaction();
            $x = 0;
            $x_max = $udlf->getRecordCount();
            foreach ($udlf->rs as $ud_obj) {
                $udlf->data = (array) $ud_obj;
                $ud_obj = $udlf;

                if ($x == $x_max) {
                    //At the end of each range, make sure we calculate holidays.
                    UserDateTotalFactory::reCalculateDay($ud_obj->getId(), FALSE, FALSE, FALSE, TRUE);
                } else {
                    UserDateTotalFactory::reCalculateDay($ud_obj->getId(), FALSE, FALSE, FALSE, FALSE);
                }

                $x++;
            }
            $udlf->CommitTransaction();

            return TRUE;
        }

        Debug::text('DID NOT find days to re-calculate: ', __FILE__, __LINE__, __METHOD__, 10);
        return FALSE;
    }

    static function smartReCalculate($user_id, $user_date_ids, $enable_exception = TRUE, $enable_premature_exceptions = FALSE, $enable_future_exceptions = TRUE) {
        if ($user_id == '') {
            return FALSE;
        }

        //Debug::Arr($user_date_ids, 'aUser Date IDs: ', __FILE__, __LINE__, __METHOD__, 10);
        if (!is_array($user_date_ids) AND is_numeric($user_date_ids) AND $user_date_ids > 0) {
            $user_date_ids = array($user_date_ids);
        }

        if (!is_array($user_date_ids)) {
            Debug::Text('Returning FALSE... User Date IDs not an array...', __FILE__, __LINE__, __METHOD__, 10);
            return FALSE;
        }

        $user_date_ids = array_unique($user_date_ids);
        //Debug::Arr($user_date_ids, 'bUser Date IDs: ', __FILE__, __LINE__, __METHOD__, 10);

        $start_week_day_id = 0;
        $ppslf = new PayPeriodScheduleListFactory();
        $ppslf->getByUserId($user_id);
        if ($ppslf->getRecordCount() == 1) {
            $pps_obj = $ppslf->getCurrent();
            $start_week_day_id = $pps_obj->getStartWeekDay();
        }
        Debug::text('Start Week Day ID: ' . $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

        //Get date stamps for all user_date_ids.
        $udlf = new UserDateListFactory();
        $udlf->getByIds($user_date_ids, NULL, array('date_stamp' => 'asc')); //Order by date asc
        if ($udlf->getRecordCount() > 0) {
            //Order them, and get the one or more sets of date ranges that need to be recalculated.
            //Need to consider re-calculating multiple weeks at once.

            $i = 0;
            foreach ($udlf->rs as $ud_obj) {
                $udlf->data = (array) $ud_obj;
                $ud_obj = $udlf;

                $start_week_epoch = TTDate::getBeginWeekEpoch($ud_obj->getDateStamp(), $start_week_day_id);
                $end_week_epoch = TTDate::getEndWeekEpoch($ud_obj->getDateStamp(), $start_week_day_id);

                Debug::text('Current Date: ' . TTDate::getDate('DATE', $ud_obj->getDateStamp()) . ' Start Week: ' . TTDate::getDate('DATE', $start_week_epoch) . ' End Week: ' . TTDate::getDate('DATE', $end_week_epoch), __FILE__, __LINE__, __METHOD__, 10);

                if ($i == 0) {
                    $range_arr[$start_week_epoch] = array('start_date' => $ud_obj->getDateStamp(), 'end_date' => $end_week_epoch);
                } else {
                    //Loop through each range extending it if needed.
                    foreach ($range_arr as $tmp_start_week_epoch => $tmp_range) {
                        if ($ud_obj->getDateStamp() >= $tmp_range['start_date'] AND $ud_obj->getDateStamp() <= $tmp_range['end_date']) {
                            //Date falls within already existing range
                            continue;
                        } elseif ($ud_obj->getDateStamp() < $tmp_range['start_date'] AND $ud_obj->getDateStamp() >= $tmp_start_week_epoch) {
                            //Date falls within the same week, but before the current start date.
                            $range_arr[$tmp_start_week_epoch]['start_date'] = $ud_obj->getDateStamp();
                            Debug::text('Pushing Start Date back...', __FILE__, __LINE__, __METHOD__, 10);
                        } else {
                            //Outside current range. Check to make sure it isn't within another range.
                            if (isset($range_arr[$start_week_epoch])) {
                                //Within another existing week, check to see if we need to extend it.
                                if ($ud_obj->getDateStamp() < $range_arr[$start_week_epoch]['start_date']) {
                                    Debug::text('bPushing Start Date back...', __FILE__, __LINE__, __METHOD__, 10);
                                    $range_arr[$start_week_epoch]['start_date'] = $ud_obj->getDateStamp();
                                }
                            } else {
                                //Not within another existing week
                                Debug::text('Adding new range...', __FILE__, __LINE__, __METHOD__, 10);
                                $range_arr[$start_week_epoch] = array('start_date' => $ud_obj->getDateStamp(), 'end_date' => $end_week_epoch);
                            }
                        }
                    }
                    unset($tmp_range, $tmp_start_week_epoch);
                }

                $i++;
            }
            unset($start_week_epoch, $end_week_epoch, $udlf, $ud_obj);

            if (is_array($range_arr)) {
                ksort($range_arr); //Sort range by start week, so recalculating goes in date order.
                //Debug::Arr($range_arr, 'Range Array: ', __FILE__, __LINE__, __METHOD__, 10);
                foreach ($range_arr as $week_range) {
                    $udlf = new UserDateListFactory();
                    $udlf->getByUserIdAndStartDateAndEndDate($user_id, $week_range['start_date'], $week_range['end_date']);
                    if ($udlf->getRecordCount() > 0) {
                        Debug::text('Found days to re-calculate: ' . $udlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

                        $udlf->StartTransaction();

                        $z = 1;
                        $z_max = $udlf->getRecordCount();
                        foreach ($udlf->rs as $ud_obj) {
                            $udlf->data = (array) $ud_obj;
                            $ud_obj = $udlf;
                            //We only need to re-calculate exceptions on the exact days specified by user_date_ids.
                            //This was the case before we Over Weekly Time/Over Scheduled Weekly Time exceptions,
                            //Now we have to enable calculating exceptions for the entire week.
                            Debug::text('Re-calculating day with exceptions: ' . $ud_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
                            if ($z == $z_max) {
                                //Enable recalculating holidays at the end of each week.
                                UserDateTotalFactory::reCalculateDay($ud_obj->getId(), $enable_exception, $enable_premature_exceptions, $enable_future_exceptions, TRUE);
                            } else {
                                UserDateTotalFactory::reCalculateDay($ud_obj->getId(), $enable_exception, $enable_premature_exceptions, $enable_future_exceptions);
                            }

                            $z++;
                        }
                        $udlf->CommitTransaction();
                    }
                }

                //Use the last date to base the future week calculation on. Make sure we don't unset $week_range['end_date']
                //When BiWeekly overtime policies are calculated, it sets getEnableCalcFutureWeek() to TRUE.
                if (isset($week_range['end_date']) AND UserDateTotalFactory::getEnableCalcFutureWeek() == TRUE) {
                    $future_week_date = $week_range['end_date'] + (86400 * 7);
                    Debug::text('Found Biweekly overtime policy, calculate one week into the future: ' . TTDate::getDate('DATE', $future_week_date), __FILE__, __LINE__, __METHOD__, 10);
                    UserDateTotalFactory::reCalculateRange($user_id, TTDate::getBeginWeekEpoch($future_week_date, $start_week_day_id), TTDate::getEndWeekEpoch($future_week_date, $start_week_day_id));
                    UserDateTotalFactory::setEnableCalcFutureWeek(FALSE); //Return to FALSE so future weeks aren't calculate for other users.
                    unset($future_week_date);
                }

                return TRUE;
            }
        }

        Debug::text('Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);

        return FALSE;
    }

    function Validate() {
        //Make sure status/type combinations are correct.
        if (!in_array($this->getType(), $this->getOptions('status_type', $this->getStatus()))) {
            Debug::text('Type doesnt match status: Type: ' . $this->getType() . ' Status: ' . $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
            $this->Validator->isTRUE('type', FALSE, ('Incorrect Type'));
        }

        //Check to make sure if this is an absence row, the absence policy is actually set.
        if ($this->getStatus() == 30 AND $this->getAbsencePolicyID() == FALSE) {
            $this->Validator->isTRUE('absence_policy_id', FALSE, ('Invalid Absence Policy'));
        }

        //Check to make sure if this is an overtime row, the overtime policy is actually set.
        if ($this->getStatus() == 10 AND $this->getType() == 30 AND $this->getOverTimePolicyID() == FALSE) {
            $this->Validator->isTRUE('over_time_policy_id', FALSE, ('Invalid Overtime Policy'));
        }

        //Check to make sure if this is an premium row, the premium policy is actually set.
        if ($this->getStatus() == 10 AND $this->getType() == 40 AND $this->getPremiumPolicyID() == FALSE) {
            $this->Validator->isTRUE('premium_policy_id', FALSE, ('Invalid Premium Policy'));
        }

        //Check to make sure if this is an meal row, the meal policy is actually set.
        if ($this->getStatus() == 10 AND $this->getType() == 100 AND $this->getMealPolicyID() == FALSE) {
            $this->Validator->isTRUE('meal_policy_id', FALSE, ('Invalid Meal Policy'));
        }

        //Make sure that we aren't trying to overwrite an already overridden entry made by the user for some special purpose.
        if ($this->getDeleted() == FALSE
                AND $this->isNew() == TRUE
                AND in_array($this->getStatus(), array(10, 20, 30))) {

            Debug::text('Checking over already existing overridden entries ... User Date ID: ' . $this->getUserDateID() . ' Status ID: ' . $this->getStatus() . ' Type ID: ' . $this->getType(), __FILE__, __LINE__, __METHOD__, 10);

            $udtlf = new UserDateTotalListFactory();

            if ($this->getStatus() == 20 AND $this->getPunchControlID() > 0) {
                $udtlf->getByUserDateIdAndStatusAndTypeAndPunchControlIdAndOverride($this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getPunchControlID(), TRUE);
            } elseif ($this->getStatus() == 30) {
                $udtlf->getByUserDateIdAndStatusAndTypeAndAbsencePolicyIDAndOverride($this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getAbsencePolicyID(), TRUE);
            } elseif ($this->getStatus() == 10 AND $this->getType() == 30) {
                $udtlf->getByUserDateIdAndStatusAndTypeAndOvertimePolicyIDAndOverride($this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getOverTimePolicyID(), TRUE);
            } elseif ($this->getStatus() == 10 AND $this->getType() == 40) {
                $udtlf->getByUserDateIdAndStatusAndTypeAndPremiumPolicyIDAndOverride($this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getPremiumPolicyID(), TRUE);
            } elseif ($this->getStatus() == 10 AND $this->getType() == 100) {
                $udtlf->getByUserDateIdAndStatusAndTypeAndMealPolicyIDAndOverride($this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getMealPolicyID(), TRUE);
            } elseif ($this->getStatus() == 10 AND ( $this->getType() == 10 OR ( $this->getType() == 20 AND $this->getPunchControlID() > 0 ) )) {
                $udtlf->getByUserDateIdAndStatusAndTypeAndPunchControlIdAndOverride($this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getPunchControlID(), TRUE);
            }

            Debug::text('Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
            if ($udtlf->getRecordCount() > 0) {
                Debug::text('Found an overridden row... NOT SAVING: ' . $udtlf->getCurrent()->getId(), __FILE__, __LINE__, __METHOD__, 10);
                $this->Validator->isTRUE('absence_policy_id', FALSE, ('Similar entry already exists, not overriding'));
            }
        }

        return TRUE;
    }

    function preSave() {
        if ($this->getPunchControlID() === FALSE) {
            $this->setPunchControlID(0);
        }

        if ($this->getOverTimePolicyID() === FALSE) {
            $this->setOverTimePolicyId(0);
        }

        if ($this->getAbsencePolicyID() === FALSE) {
            $this->setAbsencePolicyID(0);
        }

        if ($this->getPremiumPolicyID() === FALSE) {
            $this->setPremiumPolicyId(0);
        }

        if ($this->getMealPolicyID() === FALSE) {
            $this->setMealPolicyId(0);
        }

        if ($this->getBranch() === FALSE) {
            $this->setBranch(0);
        }

        if ($this->getDepartment() === FALSE) {
            $this->setDepartment(0);
        }

        if ($this->getJob() === FALSE) {
            $this->setJob(0);
        }

        if ($this->getJobItem() === FALSE) {
            $this->setJobItem(0);
        }

        if ($this->getQuantity() === FALSE) {
            $this->setQuantity(0);
        }

        if ($this->getBadQuantity() === FALSE) {
            $this->setBadQuantity(0);
        }

        return TRUE;
    }

    function postSave() {
        if ($this->getEnableCalcSystemTotalTime() == TRUE) {
            Debug::text('Calc System Total Time Enabled: ', __FILE__, __LINE__, __METHOD__, 10);
            $this->calcSystemTotalTime();
        } else {
            Debug::text('Calc System Total Time Disabled: ', __FILE__, __LINE__, __METHOD__, 10);
        }

        if ($this->getDeleted() == FALSE) {
            //Handle accruals here, instead of in calcSystemTime as that is too early in the process and user_date_total ID's don't exist yet.
            $this->calcAccrualPolicy();

          //  AccrualFactory::deleteOrphans($this->getUserDateObject()->getUser());
        }

        return TRUE;
    }

    //Takes UserDateTotal rows, and calculate the accumlated time sections
    static function calcAccumulatedTime($data) {
        if (is_array($data) and count($data) > 0) {
            //Keep track of item ids for each section type so we can decide later on if we can eliminate unneeded data.
            $section_ids = array('branch' => array(), 'department' => array(), 'job' => array(), 'job_item' => array());

            //Sort data by date_stamp at the top, so it works for multiple days at a time.
            //Keep a running total of all days, mainly for 'weekly total" purposes.
            foreach ($data as $key => $row) {
                //Skip rows with a 0 total_time.
                if ($row['total_time'] == 0) {
                    continue;
                }
                $combined_type_id_status_id = $row['type_id'] . $row['status_id'];

                switch ($combined_type_id_status_id) {
                    //Section: Accumulated Time:
                    //  Includes: Total Time, Regular Time, Overtime, Meal Policy Time, Break Policy Time.
                    case 1010: //Type_ID= 10, Status_ID= 10 - Total Time row.
                        if (!isset($retval[$row['date_stamp']]['accumulated_time']['total'])) {
                            $retval[$row['date_stamp']]['accumulated_time']['total'] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval[$row['date_stamp']]['accumulated_time']['total']['total_time'] += $row['total_time'];

                        if (!isset($retval['total']['accumulated_time']['total'])) {
                            $retval['total']['accumulated_time']['total'] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval['total']['accumulated_time']['total']['total_time'] += $row['total_time'];
                        break;
                    case 2010: //Type_ID= 20, Status_ID= 10 - Regular Time row.
                        if (!isset($retval[$row['date_stamp']]['accumulated_time']['regular'])) {
                            $retval[$row['date_stamp']]['accumulated_time']['regular'] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval[$row['date_stamp']]['accumulated_time']['regular']['total_time'] += $row['total_time'];

                        if (!isset($retval['total']['accumulated_time']['regular'])) {
                            $retval['total']['accumulated_time']['regular'] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval['total']['accumulated_time']['regular']['total_time'] += $row['total_time'];
                        break;
                    case 3010: //Type_ID= 30, Status_ID= 10 - Over Time row.
                        if (!isset($retval[$row['date_stamp']]['accumulated_time']['over_time_' . $row['over_time_policy_id']])) {
                            $retval[$row['date_stamp']]['accumulated_time']['over_time_' . $row['over_time_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval[$row['date_stamp']]['accumulated_time']['over_time_' . $row['over_time_policy_id']]['total_time'] += $row['total_time'];

                        if (!isset($retval['total']['accumulated_time']['over_time_' . $row['over_time_policy_id']])) {
                            $retval['total']['accumulated_time']['over_time_' . $row['over_time_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval['total']['accumulated_time']['over_time_' . $row['over_time_policy_id']]['total_time'] += $row['total_time'];
                        break;

                    case 10010: //Type_ID= 100, Status_ID= 10 - Meal Policy Row.
                        //Daily Total
                        if (!isset($retval[$row['date_stamp']]['accumulated_time']['meal_time_' . $row['meal_policy_id']])) {
                            $retval[$row['date_stamp']]['accumulated_time']['meal_time_' . $row['meal_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval[$row['date_stamp']]['accumulated_time']['meal_time_' . $row['meal_policy_id']]['total_time'] += $row['total_time'];

                        if (!isset($retval['total']['accumulated_time']['meal_time_' . $row['meal_policy_id']])) {
                            $retval['total']['accumulated_time']['meal_time_' . $row['meal_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval['total']['accumulated_time']['meal_time_' . $row['meal_policy_id']]['total_time'] += $row['total_time'];
                        break;
                    case 11010: //Type_ID= 110, Status_ID= 10 - Break Policy Row.
                        //Daily Total
                        if (!isset($retval[$row['date_stamp']]['accumulated_time']['break_time_' . $row['break_policy_id']])) {
                            $retval[$row['date_stamp']]['accumulated_time']['break_time_' . $row['break_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval[$row['date_stamp']]['accumulated_time']['break_time_' . $row['break_policy_id']]['total_time'] += $row['total_time'];

                        if (!isset($retval['total']['accumulated_time']['break_time_' . $row['break_policy_id']])) {
                            $retval['total']['accumulated_time']['break_time_' . $row['break_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval['total']['accumulated_time']['break_time_' . $row['break_policy_id']]['total_time'] += $row['total_time'];
                        break;

                    //Section: Premium Time:
                    //  Includes: All Premium Time
                    case 4010: //Type_ID= 40, Status_ID= 10 - Premium Policy Row.
                        //Daily Total
                        if (!isset($retval[$row['date_stamp']]['premium_time']['premium_' . $row['premium_policy_id']])) {
                            $retval[$row['date_stamp']]['premium_time']['premium_' . $row['premium_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval[$row['date_stamp']]['premium_time']['premium_' . $row['premium_policy_id']]['total_time'] += $row['total_time'];

                        if (!isset($retval['total']['premium_time']['premium_' . $row['premium_policy_id']])) {
                            $retval['total']['premium_time']['premium_' . $row['premium_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval['total']['premium_time']['premium_' . $row['premium_policy_id']]['total_time'] += $row['total_time'];
                        break;

                    //Section: Absence Time:
                    //  Includes: All Absence Time
                    case 1030: //Type_ID= 10, Status_ID= 30 - Absence Policy Row.
                        //Daily Total
                        if (!isset($retval[$row['date_stamp']]['absence_time']['absence_' . $row['absence_policy_id']])) {
                            $retval[$row['date_stamp']]['absence_time']['absence_' . $row['absence_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval[$row['date_stamp']]['absence_time']['absence_' . $row['absence_policy_id']]['total_time'] += $row['total_time'];

                        if (!isset($retval['total']['absence_time']['absence_' . $row['absence_policy_id']])) {
                            $retval['total']['absence_time']['absence_' . $row['absence_policy_id']] = array('label' => $row['name'], 'total_time' => 0);
                        }
                        $retval['total']['absence_time']['absence_' . $row['absence_policy_id']]['total_time'] += $row['total_time'];
                        break;
                }

                //Section: Accumulated Time by Branch,Department,Job,Task
                if (in_array($row['type_id'], array(20, 30)) AND in_array($row['status_id'], array(10))) {
                    //Branch
                    $branch_name = $row['branch'];
                    if ($branch_name == '') {
                        $branch_name = ('No Branch');
                    }
                    if (!isset($retval[$row['date_stamp']]['branch_time']['branch_' . $row['branch_id']])) {
                        $retval[$row['date_stamp']]['branch_time']['branch_' . $row['branch_id']] = array('label' => $branch_name, 'total_time' => 0);
                    }
                    $retval[$row['date_stamp']]['branch_time']['branch_' . $row['branch_id']]['total_time'] += $row['total_time'];
                    $section_ids['branch'][] = (int) $row['branch_id'];

                    //Department
                    $department_name = $row['department'];
                    if ($department_name == '') {
                        $department_name = ('No Department');
                    }
                    if (!isset($retval[$row['date_stamp']]['department_time']['department_' . $row['department_id']])) {
                        $retval[$row['date_stamp']]['department_time']['department_' . $row['department_id']] = array('label' => $department_name, 'total_time' => 0);
                    }
                    $retval[$row['date_stamp']]['department_time']['department_' . $row['department_id']]['total_time'] += $row['total_time'];
                    $section_ids['department'][] = (int) $row['department_id'];

                    //Job
                    $job_name = $row['job'];
                    if ($job_name == '') {
                        $job_name = ('No Job');
                    }
                    if (!isset($retval[$row['date_stamp']]['job_time']['job_' . $row['job_id']])) {
                        $retval[$row['date_stamp']]['job_time']['job_' . $row['job_id']] = array('label' => $job_name, 'total_time' => 0);
                    }
                    $retval[$row['date_stamp']]['job_time']['job_' . $row['job_id']]['total_time'] += $row['total_time'];
                    $section_ids['job'][] = (int) $row['job_id'];

                    //Job Item/Task
                    $job_item_name = $row['job_item'];
                    if ($job_item_name == '') {
                        $job_item_name = ('No Task');
                    }
                    if (!isset($retval[$row['date_stamp']]['job_item_time']['job_item_' . $row['job_item_id']])) {
                        $retval[$row['date_stamp']]['job_item_time']['job_item_' . $row['job_item_id']] = array('label' => $job_item_name, 'total_time' => 0);
                    }
                    $retval[$row['date_stamp']]['job_item_time']['job_item_' . $row['job_item_id']]['total_time'] += $row['total_time'];
                    $section_ids['job_item'][] = (int) $row['job_item_id'];

                    //Debug::text('ID: '. $row['id'] .' User Date ID: '. $row['date_stamp'] .' Total Time: '. $row['total_time'] .' Branch: '. $branch_name .' Job: '. $job_name, __FILE__, __LINE__, __METHOD__, 10);
                }
            }

            if (isset($retval)) {
                //Remove any unneeded data, such as "No Branch" for all dates in the range
                foreach ($section_ids as $section => $ids) {
                    $ids = array_unique($ids);
                    sort($ids);
                    if (isset($ids[0]) AND $ids[0] == 0 AND count($ids) == 1) {
                        foreach ($retval as $date_stamp => $day_data) {
                            unset($retval[$date_stamp][$section . '_time']);
                        }
                    }
                }

                return $retval;
            }
        }

        return FALSE;
    }

    function setObjectFromArray($data) {
        if (is_array($data)) {

            //We need to set the UserDate as soon as possible.
            if (isset($data['user_id']) AND $data['user_id'] != ''
                    AND isset($data['date_stamp']) AND $data['date_stamp'] != '') {
                Debug::text('Setting User Date ID based on User ID:' . $data['user_id'] . ' Date Stamp: ' . $data['date_stamp'], __FILE__, __LINE__, __METHOD__, 10);
                $this->setUserDate($data['user_id'], TTDate::parseDateTime($data['date_stamp']));
            } elseif (isset($data['user_date_id']) AND $data['user_date_id'] > 0) {
                Debug::text(' Setting UserDateID: ' . $data['user_date_id'], __FILE__, __LINE__, __METHOD__, 10);
                $this->setUserDateID($data['user_date_id']);
            } else {
                Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__, 10);
            }

            $variable_function_map = $this->getVariableToFunctionMap();
            foreach ($variable_function_map as $key => $function) {
                if (isset($data[$key])) {

                    $function = 'set' . $function;
                    switch ($key) {
                        case 'user_date_id': //Ignore user_date_id, as we already set it above.
                            break;
                        default:
                            if (method_exists($this, $function)) {
                                $this->$function($data[$key]);
                            }
                            break;
                    }
                }
            }

            $this->setCreatedAndUpdatedColumns($data);

            return TRUE;
        }

        return FALSE;
    }

    function getObjectAsArray($include_columns = NULL, $permission_children_ids = FALSE) {
        $uf = new UserFactory();

        $variable_function_map = $this->getVariableToFunctionMap();
        if (is_array($variable_function_map)) {
            foreach ($variable_function_map as $variable => $function_stub) {
                if ($include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE )) {

                    $function = 'get' . $function_stub;
                    switch ($variable) {
                        case 'user_id':
                        case 'first_name':
                        case 'last_name':
                        case 'user_status_id':
                        case 'group_id':
                        case 'group':
                        case 'title_id':
                        case 'title':
                        case 'default_branch_id':
                        case 'default_branch':
                        case 'default_department_id':
                        case 'default_department':
                        case 'pay_period_id':
                        case 'branch':
                        case 'department':
                        case 'over_time_policy':
                        case 'absence_policy':
                        case 'absence_policy_type_id':
                        case 'premium_policy':
                        case 'meal_policy':
                        case 'break_policy':
                        case 'job':
                        case 'job_item':
                            $data[$variable] = $this->getColumn($variable);
                            break;
                        case 'status':
                        case 'type':
                            $function = 'get' . $variable;
                            if (method_exists($this, $function)) {
                                $data[$variable] = Option::getByKey($this->$function(), $this->getOptions($variable));
                            }
                            break;
                        case 'user_status':
                            $data[$variable] = Option::getByKey((int) $this->getColumn('user_status_id'), $uf->getOptions('status'));
                            break;
                        case 'date_stamp':
                            $data[$variable] = TTDate::getAPIDate('DATE', TTDate::strtotime($this->getColumn('date_stamp')));
                            break;
                        case 'start_time_stamp':
                            $data[$variable] = TTDate::getAPIDate('DATE+TIME', $this->$function()); //Include both date+time
                            break;
                        case 'end_time_stamp':
                            $data[$variable] = TTDate::getAPIDate('DATE+TIME', $this->$function()); //Include both date+time
                            break;
                        case 'name':
                            $data[$variable] = $this->getName();
                            break;
                        default:
                            if (method_exists($this, $function)) {
                                $data[$variable] = $this->$function();
                            }
                            break;
                    }
                }
            }
            $this->getPermissionColumns($data, $this->getColumn('user_id'), $this->getCreatedBy(), $permission_children_ids, $include_columns);
            $this->getCreatedAndUpdatedColumns($data, $include_columns);
        }

        return $data;
    }

    function addLog($log_action) {
        if ($this->getOverride() == TRUE AND $this->getStatus() == 30) { //Absence
            return TTLog::addEntry($this->getId(), $log_action, ('Absence') . ' - ' . ('Date') . ': ' . TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp()) . ' ' . ('Total Time') . ': ' . TTDate::getTimeUnit($this->getTotalTime()), NULL, $this->getTable(), $this);
        }
    }

}

?>