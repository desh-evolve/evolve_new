<?php

namespace App\Http\Controllers\timesheet;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\CalculatePayStub;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\ExceptionListFactory;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\TTLog;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateListFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Holiday\HolidayListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\BreakPolicyListFactory;
use App\Models\Policy\ExceptionPolicyFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class ViewUserTimeSheet extends Controller
{
    protected $permission;
    protected $currentUser;
    protected $currentCompany;
    protected $userPrefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');
        $this->userPrefs = View::shared('current_user_prefs');

    }

    public function index(){

        $permission = $this->permission;
        $current_user = $this->currentUser;
        $current_company = $this->currentCompany;
        $current_user_prefs = $this->userPrefs;

        if ( !$permission->Check('punch','enabled')
                OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_own') OR $permission->Check('punch','view_child')) ) {
            $permission->Redirect( FALSE ); //Redirect
        }

        function TimeSheetFormatArrayByDate($input_arr, $type_arr, $calendar_array, $name_key, $id_key = NULL ) {
            //Debug::text('Group Array While Loop: ', __FILE__, __LINE__, __METHOD__,10);
            //Debug::Arr($input_arr, 'Input Array: ', __FILE__, __LINE__, __METHOD__,10);
            //Debug::Arr($type_arr, 'Type Array: ', __FILE__, __LINE__, __METHOD__,10);
            $x=0;
            $stop = FALSE;
            $max_no_punch_count = count($calendar_array);
            while ( $stop == FALSE ) {
                if ( isset($type_arr[$x]) ) {
                    $type_id = $type_arr[$x];
                } else {
                    $type_id = NULL;
                }

                //Debug::text('===========================================================', __FILE__, __LINE__, __METHOD__,10);
                //Debug::text('While Loop: '. $x .' Max No Punch Count: '. $max_no_punch_count .' Type ID: '. $type_id .' ... ', __FILE__, __LINE__, __METHOD__,10);

                $no_punch_count=0;

                foreach( $calendar_array as $cal_arr ) {
                    //Debug::text('Calendar Day: '. $cal_arr['day_of_month'] .' Epoch: '.$cal_arr['epoch'] , __FILE__, __LINE__, __METHOD__,10);

                    if ( isset($input_arr[$cal_arr['epoch']][$type_id]) ) {
                        //Debug::text('Found Punch for Day: '. $cal_arr['day_of_month'] , __FILE__, __LINE__, __METHOD__,10);
                        $total_arr = $input_arr[$cal_arr['epoch']][$type_id];

                        unset($input_arr[$cal_arr['epoch']][$type_id]);

                        if ( $total_arr[$name_key] == '' ) {
                            $total_rows[$x]['name'] = _('N/A');
                        } else {
                            $total_rows[$x]['name'] = $total_arr[$name_key];
                        }
                        $total_rows[$x]['type_and_policy_id'] = $type_id;
                        if ( $id_key != '' ) {
                            $total_rows[$x]['id'] = $total_arr[$id_key];
                        }
                    } else {
                        //Debug::text('NO Punch found for Day: '. $cal_arr['day_of_month'] .' No Punch Count: '. $no_punch_count, __FILE__, __LINE__, __METHOD__,10);
                        $total_arr = NULL;
                        $no_punch_count++;
                    }

                    $total_rows[$x]['data'][$cal_arr['epoch']] = $total_arr;
                }

                //Debug::text('No Punch Count: '. $no_punch_count .' Max: '. $max_no_punch_count, __FILE__, __LINE__, __METHOD__,10);
                if ( $x == 100 OR $no_punch_count == $max_no_punch_count ) {
                    //Debug::text('Stopping Loop at: '. $x, __FILE__, __LINE__, __METHOD__,10);
                    //Clear last row, as its blank;
                    array_pop($total_rows);

                    $stop = TRUE;
                }
                $x++;
            }
            //var_dump($total_rows);

            return $total_rows;
        }

        $viewData['title'] = 'My Timesheet';

        //Get FORM variables
        
        extract	(FormVariables::GetVariables(
            array (
                'action',
                'action_option',
                'filter_data',
                'prev_week',
                'next_week',
                'prev_pp',
                'next_pp'
            ) 
        ) );

        //Load default filter settings, if other filter settings aren't set.
        $ugdlf = new UserGenericDataListFactory(); 

        $ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'], TRUE );
        if ( $ugdlf->getRecordCount() > 0 ) {
            Debug::Text('Found Default Filter!', __FILE__, __LINE__, __METHOD__,10);

            $ugd_obj = $ugdlf->getCurrent();
            $generic_data['id'] = $ugd_obj->getId();

            if ( !isset($filter_data['user_id']) AND $filter_data == NULL ) {
                $filter_data = $ugd_obj->getData();
            }
        }

        if ( !isset($filter_data['user_id']) ) {
            $filter_data['user_id'] = NULL;
        }
        if ( !isset($filter_data['date']) ) {
            $filter_data['date'] = NULL;
        }
        if ( !isset($filter_data['group_ids']) ) {
            $filter_data['group_ids'] = -1;
        }
        if ( !isset($filter_data['branch_ids']) ) {
            $filter_data['branch_ids'] = -1;
        }
        if ( !isset($filter_data['department_ids']) ) {
            // $filter_data['department_ids'] = -1;
        }

        $uglf = new UserGroupListFactory();
        $group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );

        $blf = new BranchListFactory(); 
        $blf->getByCompanyId( $current_company->getId() );
        $branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );

        $dlf = new DepartmentListFactory();
        $dlf->getByCompanyId( $current_company->getId() );
        $department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );

        $ulf = new UserListFactory(); 

        $hlf = new HierarchyListFactory(); 
        $permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
        //Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
        if ( $permission->Check('punch','view') == FALSE ) {
            if ( $permission->Check('punch','view_child') ) {
                $filter_data['permission_children_ids'] = $permission_children_ids;
            }
            if ( $permission->Check('punch','view_own') ) {
                $filter_data['permission_children_ids'][] = $current_user->getId();
            }
        }
        $ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
        if ( $ulf->getRecordCount() == 0 ) {
            //If the user selects a branch/department without any users assigned to it, default to just themselves?
            $ulf->getById( $current_user->getId() );
        }
        $user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
        //Keep these from being stored in the database or showing up on the URL, causing excessive URL length.
        unset($filter_data['permission_children_ids']);

        if ( $permission->Check('punch','view') OR $permission->Check('punch','view_child') ) {
            Debug::text('Viewing all users timesheet', __FILE__, __LINE__, __METHOD__,10);
            if ( $filter_data['user_id'] != '' ) {
                if ( isset( $user_options[$filter_data['user_id']]) ) {
                    $user_id = $filter_data['user_id'];
                } elseif ( is_array($user_options) ) {
                    //Use first user in list
                    $user_ids = array_keys($user_options);
                    $user_id = $user_ids[0];
                    unset($user_ids);
                }
            }
        }

        if ( !isset($user_id) ) {
            $user_id = $current_user->getId();
            $filter_data['branch_ids'] = -1;
            $filter_data['department_ids'] = -1;
        }

        $filter_data['user_id'] = $user_id;

        //Get User Object
        $ulf->getByIdAndCompanyId( $user_id, $current_user->getCompany() );
        if ( $ulf->getRecordCount() > 0 ) {
            $user_obj = $ulf->getCurrent();
        }

        if ( $filter_data['date'] != '' ) { 
            $filter_data['date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime( $filter_data['date'] ) );
        }

        if ( isset($prev_week) ) {
            $filter_data['date'] = TTDate::getBeginDayEpoch( $filter_data['date']-((86400*7)-7200) ); //DST
        } elseif ( isset($next_week) ) {
            $filter_data['date'] = TTDate::getBeginDayEpoch( $filter_data['date']+((86400*7)+7200) ); //DST
        }

        //Get current PP info
        if ( isset($prev_pp) OR isset($next_pp) ) {
            $pplf = new PayPeriodListFactory();
            $pplf->getByUserIdAndEndDate( $user_id, $filter_data['date'] );
            if ( $pplf->getRecordCount() > 0 ) {
                //Debug::setVerbosity(11);
                $pay_period_obj = $pplf->getCurrent();
                $pay_period_total_days = $pay_period_obj->getEndDate() - $pay_period_obj->getStartDate();
                Debug::text('Pay Period Total Days: '. $pay_period_total_days, __FILE__, __LINE__, __METHOD__,10);

                if ( isset($prev_pp) ) {
                    $filter_data['date'] = TTDate::getBeginDayEpoch( ($filter_data['date'] - $pay_period_total_days) - 86400 );
                } elseif ( isset($next_pp) ) {
                    $filter_data['date'] = TTDate::getBeginDayEpoch( ($filter_data['date'] + $pay_period_total_days) + 86400 );
                }
                unset($pay_period_total_days);
            } else {
                Debug::text('Skipping Two Weeks...', __FILE__, __LINE__, __METHOD__,10);
                //Just do two weeks at a time
                if ( isset($prev_pp) ) {
                    $filter_data['date'] = TTDate::getBeginDayEpoch( $filter_data['date']-(86400*14) );
                } elseif ( isset($next_pp) ) {
                    $filter_data['date'] = TTDate::getBeginDayEpoch( $filter_data['date']+(86400*14) );
                }
            }
        }

        if ( $filter_data['date'] == '' OR $filter_data['date'] <= 0 ) {
            $filter_data['date'] = TTDate::getBeginDayEpoch( TTDate::getTime() );
        }

        //Save current filter settings, so when we come back to the timesheet they are loaded.
        $ugdf = new UserGenericDataFactory(); 
        if ( isset($generic_data) AND $generic_data['id'] != '' AND $generic_data['id'] != 0 ) {
            Debug::text('Passed ID: ', __FILE__, __LINE__, __METHOD__,10);
            $ugdf->setID( $generic_data['id'] );
        }

        $ugdf->setCompany( $current_company->getId() );
        $ugdf->setUser( $current_user->getId() );
        $ugdf->setScript( $_SERVER['SCRIPT_NAME'] );
        $ugdf->setName( 'Default' ); //This has to go after company,user and script are already set.
        $ugdf->setData( $filter_data );
        $ugdf->setDefault( TRUE );

        if ( $ugdf->isValid() ) {
            $ugf_id = $ugdf->Save();

            if ( $ugf_id !== TRUE ) {
                $generic_data['id']	= $ugf_id;
            }
            unset($generic_data['name']);
        }

        //Get pay period info from filter date.
        $udlf = new UserDateListFactory();
        $udlf->getByUserIdAndDate( $filter_data['user_id'], $filter_data['date'] );
        if ( $udlf->getRecordCount() > 0 ) {
            $pay_period_id = $udlf->getCurrent()->getPayPeriod();
        } else {
            Debug::text('bPay Period Lookup: ', __FILE__, __LINE__, __METHOD__,10);
            //Slower method, find another user date in
            //FIXME: If they change pay period schedules for an employee, and the employee
            //doesn't have user_date rows for weekends, it won't know which pay period they belong to.
            //This will guess they belong to the new pay period schedule.
            //Only real fix I see for this is to make sure we have a user_date row for EVERY day, for EVERY
            //user, regardless if they work or not.
            $pplf = new PayPeriodListFactory();
            $pplf->getByUserIdAndEndDate( $user_id, $filter_data['date'] );
            if ( $pplf->getRecordCount() > 0 ) {
                $pay_period_obj = $pplf->getCurrent();
                $pay_period_id = $pay_period_obj->getId();
            } else {
                $pay_period_id = FALSE;
            }
        }
        $pplf = new PayPeriodListFactory();
        $pplf->getById( $pay_period_id );
        if ( $pplf->getRecordCount() > 0 ) {
            $pay_period_obj = $pplf->getCurrent();
        }
        Debug::text('Pay Period ID: '. $pay_period_id, __FILE__, __LINE__, __METHOD__,10);

        URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
                                                    array(
                                                            'filter_data' => $filter_data,
                                                        ) );

        $action = Misc::findSubmitButton();
        Debug::Text('Action: '. $action .' Action Option: '. $action_option, __FILE__, __LINE__, __METHOD__,10);
        //If submit is pressed, use $action_option from the dropdown box as the action instead.
        if ( $action == 'submit' AND $action_option != '' ) {
            $action = $action_option;
        }
        switch ($action) {
            case 'authorize':
            case 'decline':
            case 'verify':
                //Debug::setVerbosity(11);
                Debug::text('Verifying Pay Period TimeSheet ', __FILE__, __LINE__, __METHOD__,10);

                $pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
                $pptsvlf->StartTransaction();
                $pptsvlf->getByPayPeriodIdAndUserId( $pay_period_obj->getId(), $user_id );
                if ( $pptsvlf->getRecordCount() == 0 ) {
                    Debug::text('Timesheet NOT verified by employee yet.', __FILE__, __LINE__, __METHOD__,10);
                    $pptsvf = new PayPeriodTimeSheetVerifyFactory(); 
                } else {
                    Debug::text('Timesheet re-verified by employee, or superior...', __FILE__, __LINE__, __METHOD__,10);
                    $pptsvf = $pptsvlf->getCurrent();
                }

                $pptsvf->setCurrentUser( $current_user->getId() );
                $pptsvf->setUser( $user_id );
                $pptsvf->setPayPeriod( $pay_period_id );

                if ( $pptsvf->isValid() ) {
                    $pptsvf->Save();
                }
                //$pptsvlf->FailTransaction();
                $pptsvlf->CommitTransaction();

                Redirect::Page( URLBuilder::getURL( NULL, '/attendance/timesheet') );

                break;
            case 'recalculate_pay_stub':
                //Debug::setVerbosity(11);

                //Find out if a pay stub is already generated for the pay period we are currently in.
                //If it is, delete it so we can start from fresh
                $pplf = new PayPeriodListFactory();
                $pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() );

                $pslf = new PayStubListFactory();
                $pslf->getByUserIdAndPayPeriodId( $filter_data['user_id'], $pay_period_id );

                foreach ($pslf->rs as $pay_stub) {
                    $pslf->data = (array)$pay_stub;
                    $pay_stub = $pslf;
                    
                    Debug::Text('Found Pay Stub ID: '. $pay_stub->getId(), __FILE__, __LINE__, __METHOD__,10);
                    //Do not delete PAID pay stubs!
                    if ( $pay_stub->getStatus() <= 25
                            AND $pay_stub->getTainted() === FALSE
                            AND $pay_stub->getEndDate() == $pay_period_obj->getEndDate() ) {
                        Debug::Text('Last Pay Stub Exists: '. $pay_stub->getId(), __FILE__, __LINE__, __METHOD__,10);
                        $pay_stub->setDeleted(TRUE);
                        $pay_stub->Save();
                    } else {
                        Debug::Text('aNot Deleting Pay Stub: '. $pay_stub->getId(), __FILE__, __LINE__, __METHOD__,10);
                    }
                }

                TTLog::addEntry( $filter_data['user_id'], 'Notice', _('Calculating Employee Pay Stub for Pay Period:').' '. $pay_period_id, $current_user->getID(), 'pay_stub' );

                //FIXME: Make sure user isn't already in-active! Otherwise pay stub won't generate.
                Debug::Text('Calculating Pay Stub...', __FILE__, __LINE__, __METHOD__,10);

                $profiler->startTimer( "Calculating Pay Stub");

                $cps = new CalculatePayStub();
                $cps->setUser( $filter_data['user_id'] );
                $cps->setPayPeriod( $pay_period_id );
                $cps->calculate();

                $profiler->stopTimer( "Calculating Pay Stub");
                Debug::Text('Done Calculating Pay Stub', __FILE__, __LINE__, __METHOD__,10);

                ////Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $filter_data['user_id'], 'filter_date' => $filter_date ), '/attendance/timesheet') );
                Redirect::Page( URLBuilder::getURL( array('filter_pay_period_id' => $pay_period_id, 'filter_user_id' => $filter_data['user_id'] ), '../pay_stub/PayStubList.php') );

                break;
            case 'calculate_adjustment':
                //Debug::setVerbosity(11);

                TTLog::addEntry( $filter_data['user_id'], 'Notice', _('Calculating Employee Pay Stub Adjustment for Pay Period:').' '. $pay_period_id, $current_user->getID(), 'pay_stub' );

                //FIXME: Make sure user isn't already in-active! Otherwise pay stub won't generate.
                Debug::Text('Calculating Pay Stub...', __FILE__, __LINE__, __METHOD__,10);
                $cps = new CalculatePayStub();
                $cps->setEnableCorrection(TRUE);
                $cps->setUser( $filter_data['user_id'] );
                $cps->setPayPeriod( $pay_period_id );
                $cps->calculate();
                Debug::Text('Done Calculating Pay Stub', __FILE__, __LINE__, __METHOD__,10);

                //Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $filter_data['user_id'], 'filter_date' => $filter_date ), '/attendance/timesheet') );
                Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $filter_data['user_id'] ), '../pay_stub_amendment/PayStubAmendmentList.php') );

                break;
            case 'recalculate_company':
                Debug::Text('Recalculating company timesheet!', __FILE__, __LINE__, __METHOD__,10);

                //Redirect::Page( URLBuilder::getURL( array('action' => 'recalculate_company', 'pay_period_ids' => $pay_period_id, 'next_page' => urlencode( URLBuilder::getURL( array('filter_date' => $filter_date ), '/attendance/timesheet') ) ), '/progress_bar_control') );
                Redirect::Page( URLBuilder::getURL( array('action' => 'recalculate_company', 'pay_period_ids' => $pay_period_id, 'next_page' => urlencode( URLBuilder::getURL( NULL, '/attendance/timesheet') ) ), '/progress_bar_control'), FALSE );

                break;
            case 'recalculate_employee':
                Debug::Text('Recalculating employee timesheet!', __FILE__, __LINE__, __METHOD__,10);

                Redirect::Page( URLBuilder::getURL( array('action' => 'recalculate_employee', 'pay_period_ids' => $pay_period_id, 'filter_user_id' => $filter_data['user_id'], 'next_page' => urlencode( URLBuilder::getURL( NULL, '/attendance/timesheet') ) ), '/progress_bar_control'), FALSE );

                break;
            case 'recalculate_mid_pay':
                Debug::Text('Recalculating Mid Pay timesheet!', __FILE__, __LINE__, __METHOD__,10);

                Redirect::Page( URLBuilder::getURL( array('action' => 'generate_paymiddle', 'pay_period_ids' => $pay_period_id, 'filter_user_id' => $filter_data['user_id'], 'next_page' => urlencode( URLBuilder::getURL( NULL, '/attendance/timesheet') ) ), '/progress_bar_control'), FALSE );

                break;
            case 'submit':
            default:
                //Debug::setVerbosity(11);

                $date_break_total_rows = [];
                $date_break_policy_total_rows = [];
                $date_meal_policy_total_rows = [];
                $date_branch_total_rows = [];
                $date_department_total_rows = [];
                $date_job_total_rows = [];
                $date_job_item_total_rows = [];
                $date_premium_total_rows = [];
                $date_absence_total_rows = [];
                $punch_control_exceptions = [];
                $date_exception_total_rows = [];
                $date_request_total_rows = [];
                $unique_exceptions = [];
                $pay_period_total_rows = [];
                $pay_period_locked_rows = [];
                $time_sheet_verify = [];

                Debug::Text('Default Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

                $start_date = TTDate::getBeginWeekEpoch( $filter_data['date'], $current_user_prefs->getStartWeekDay() );
                $end_date = TTDate::getEndWeekEpoch( $filter_data['date'], $current_user_prefs->getStartWeekDay() );

                Debug::Text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);
                $calendar_array = TTDate::getCalendarArray( $start_date, $end_date, $current_user_prefs->getStartWeekDay() );
                //var_dump($calendar_array);

                //Get all punches, put in array by date epoch.
                $plf = new PunchListFactory(); 
                $plf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $current_company->getId(), $user_id, $start_date, $end_date);
                if ( $plf->getRecordCount() > 0 ) {
                    foreach($plf->rs as $punch_obj) {
                        $plf->data = (array)$punch_obj;
                        $punch_obj = $plf;

                        $user_date_stamp = TTDate::strtotime( $punch_obj->getColumn('user_date_stamp') );

                        if ( $punch_obj->getColumn('note') != '' ) {
                            $has_note = TRUE;
                        } else {
                            $has_note = FALSE;
                        }
                        $punches[$user_date_stamp][] = array(
                            'date_stamp' => $punch_obj->getColumn('user_date_stamp'),
                            'id' => $punch_obj->getId(),
                            'punch_control_id' => $punch_obj->getPunchControlId(),
                            'time_stamp' => $punch_obj->getTimeStamp(),
                            'status_id' => $punch_obj->getStatus(),
                            'type_id' => $punch_obj->getType(),
                            'type_code' => $punch_obj->getTypeCode(),
                            'has_note' => $has_note,
                        );


                        //Total up meal and break total time for each day.
                        if ( $punch_obj->getType() != 10 ) {

                            if ( $punch_obj->getStatus() == 20 ) {
                                $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['prev'] = $punch_obj->getTimeStamp();
                            } elseif ( isset($tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['prev']) ) {
                                if ( !isset($tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_time']) ) {
                                    $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_time'] = 0;
                                }

                                $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_time'] = bcadd( $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_time'], bcsub( $punch_obj->getTimeStamp(), $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['prev']) );

                                if ( !isset($tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_breaks']) ) {
                                    $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_breaks'] = 0;
                                }
                                $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_breaks']++;

                                if ( $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_time'] > 0 ) {
                                    if (  $punch_obj->getType() == 20 ) {
                                        $break_name = _('Lunch Time');
                                    } else {
                                        $break_name = _('Break Time');
                                    }

                                    $date_break_totals[$user_date_stamp][$punch_obj->getType()] = array(
                                        'break_name' => $break_name,
                                        'total_time' => $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_time'],
                                        'total_breaks' => $tmp_date_break_totals[$user_date_stamp][$punch_obj->getType()]['total_breaks'],
                                    );
                                    unset($break_name);
                                }

                            }

                            $date_total_break_ids[] = (int)$punch_obj->getType();
                        }
                    }
                }
                unset($tmp_date_break_totals);
                //Debug::Arr( $punches, 'Punches: ', __FILE__, __LINE__, __METHOD__,10);
                //Debug::Arr( $tmp_date_break_totals, 'Break Totals: ', __FILE__, __LINE__, __METHOD__,10);

                //Process meal/break total time so it can be properly formatted on the timesheet.
                
                if ( isset($date_break_totals) ) {
                    $date_total_break_ids = array_unique($date_total_break_ids);
                    rsort($date_total_break_ids); //Put break time first, then lunch.

                    $date_break_total_rows = TimeSheetFormatArrayByDate( $date_break_totals, $date_total_break_ids, $calendar_array, 'break_name');
                    //Debug::Arr( $date_break_total_rows, 'Break Total Rows: ', __FILE__, __LINE__, __METHOD__,10);
                }
                unset($date_total_break_ids, $date_break_totals);

                $x=0;
                $stop = FALSE;
                $max_no_punch_count = count($calendar_array)*2;
                $punch_day_counter=array();
                $last_punch_control_id=array();
                $no_punch_count=0;
                $max_punch_day_counter=0;
                while ( $stop == FALSE ) {
                    if ($x % 2 == 0) {
                        $status = 10; //In
                        $status_name = _('In');
                    } else {
                        $status = 20; //Out
                        $status_name = _('Out');
                    }

                    //Debug::text('----------------------------------------', __FILE__, __LINE__, __METHOD__,10);
                    //Debug::text('While Loop: '. $x .' Max No Punch Count: '. $max_no_punch_count .' Status: '. $status, __FILE__, __LINE__, __METHOD__,10);

                    foreach( $calendar_array as $cal_arr ) {
                        $cal_day_epoch = $cal_arr['epoch'];
                        if ( !isset($punch_day_counter[$cal_day_epoch]) ) {
                            $punch_day_counter[$cal_day_epoch] = 0;
                        }
                        if ( !isset($last_punch_control_id[$cal_day_epoch]) ) {
                            $last_punch_control_id[$cal_day_epoch] = 0;
                        }

                        //Debug::text($x .'Calendar Day: '. $cal_arr['day_of_month'] .' Punch Day Counter: '. $punch_day_counter[$cal_day_epoch], __FILE__, __LINE__, __METHOD__,10);

                        if ( isset($punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]])
                                AND $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]['status_id'] == $status
                                AND $status == 10 ) {
                            //Debug::text('Status: 10 Found Punch for Day: '. $cal_arr['day_of_month'] , __FILE__, __LINE__, __METHOD__,10);
                            $punch_arr = $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]];

                            $last_punch_control_id[$cal_day_epoch] = $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]['punch_control_id'];

                            $punch_day_counter[$cal_day_epoch]++;

                            $no_punch_count=0;
                        } elseif ( isset($punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]])
                                    AND $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]['status_id'] == $status
                                    AND $status == 20 ) {
                            //Debug::text($x .'Status: 20 Found Punch for Day: '. $cal_arr['day_of_month'] , __FILE__, __LINE__, __METHOD__,10);

                            //Make sure the previous IN status punch_control_id matches this one.
                            //Or that it is null.
                            //Debug::text($x .'Last Punch Control ID: '. $last_punch_control_id[$cal_day_epoch], __FILE__, __LINE__, __METHOD__,10);
                            if ( isset($punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]-1])
                                    AND ( $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]-1]['punch_control_id'] == $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]['punch_control_id']
                                            OR $last_punch_control_id[$cal_day_epoch] == NULL ) ) {
                                //Debug::text('Status: 20 -- Punch Control ID DOES match that of In Status! ', __FILE__, __LINE__, __METHOD__,10);
                                $punch_arr = $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]];

                                $last_punch_control_id[$cal_day_epoch] = $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]['punch_control_id'];

                                $punch_day_counter[$cal_day_epoch]++;
                            } else {
                                //Check to see if the In punch even exists first?
                                if ( !isset($punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]-1] ) ) {
                                    //Debug::text('Status: 20 -- In Punch does not exist! ', __FILE__, __LINE__, __METHOD__,10);
                                    $punch_arr = $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]];

                                    $last_punch_control_id[$cal_day_epoch] = $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]['punch_control_id'];

                                    $punch_day_counter[$cal_day_epoch]++;
                                } else {
                                    //Debug::text('Status: 20 -- Punch Control ID DOES NOT match that of In Status! ', __FILE__, __LINE__, __METHOD__,10);
                                    $punch_arr = array('punch_control_id' => $last_punch_control_id[$cal_day_epoch]);

                                    $last_punch_control_id[$cal_day_epoch] = NULL;
                                }
                            }

                            $no_punch_count=0;
                        } else {
                            //Debug::text($x .': NO Punch found for Day: '. $cal_arr['day_of_month'] .' Status: '. $status .' Day Counter: '. $punch_day_counter[$cal_day_epoch] .' No Punch Count: '. $no_punch_count, __FILE__, __LINE__, __METHOD__,10);

                            $tmp_punch_control_id = NULL;
                            if ( $status == 10 ) {
                                if ( isset($punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]) ) {
                                    //Debug::text('aFound Possible Punch Control ID: '.$punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]['punch_control_id'], __FILE__, __LINE__, __METHOD__,10);
                                    $tmp_punch_control_id = $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]]['punch_control_id'];
                                    $no_punch_count=0;
                                } else {
                                    //Debug::text('aDID NOT Find Possible Punch Control ID: ', __FILE__, __LINE__, __METHOD__,10);
                                    //$last_punch_control_id[$cal_day_epoch] = NULL;
                                }
                            } else {
                                //Check for counter-1 for punch control id
                                if ( isset($punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]-1])
                                        AND $last_punch_control_id[$cal_day_epoch] != NULL ) {
                                    //Debug::text('bFound Possible Punch Control ID: '.$punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]-1]['punch_control_id'], __FILE__, __LINE__, __METHOD__,10);
                                    $tmp_punch_control_id = $punches[$cal_day_epoch][$punch_day_counter[$cal_day_epoch]-1]['punch_control_id'];
                                    $no_punch_count=0;
                                }
                            }
                            $last_punch_control_id[$cal_day_epoch] = NULL;

                            $punch_arr = array('punch_control_id' => $tmp_punch_control_id);

                            $no_punch_count++;
                        }


                        $rows[$x]['data'][$cal_arr['epoch']] = $punch_arr;
                        $rows[$x]['status_id'] = $status;
                        $rows[$x]['status'] = $status_name;
                        $rows[$x]['background'] = $x % 2;

                        if ( $punch_day_counter[$cal_day_epoch] > $max_punch_day_counter) {
                            //Debug::text('Updating Max Day Punch Counter: '. $punch_day_counter[$cal_day_epoch], __FILE__, __LINE__, __METHOD__,10);
                            $max_punch_day_counter = $punch_day_counter[$cal_day_epoch];
                        }
                    }

                    //Debug::text('No Punch Count: '. $no_punch_count .' Max: '. $max_no_punch_count, __FILE__, __LINE__, __METHOD__,10);
                    //Only pop off the last row if the rows aren't in pairs. Because if there is only ONE in punch at the first day of the week
                    //and no other punches, the Out row doesn't show otherwise.
                    if ( $x == 100 OR $no_punch_count >= $max_no_punch_count ) {
                        //Debug::text('Stopping Loop at: '. $x, __FILE__, __LINE__, __METHOD__,10);

                        //Made this >= 2 so it doesn't show 3 rows if the first day of the week only has the IN punch.
                        //It was > 2.
                        if ( $x >= 2 ) {
                            if ( $x % 2 == 0) {
                                //Clear last 1 rows, as its blank;
                                //Debug::text('Popping Off Last Row: '. $x, __FILE__, __LINE__, __METHOD__,10);
                                array_pop($rows);
                            } else {
                                //Debug::text('Popping Off Last TWO Row: '. $x, __FILE__, __LINE__, __METHOD__,10);
                                array_pop($rows);
                                array_pop($rows);
                            }
                        }

                        $stop = TRUE;
                    }
                    $x++;
                }
                unset($punches);
                

                //Get date total rows.
                $udtlf = new UserDateTotalListFactory();

                $mplf = new MealPolicyListFactory();
                $meal_policy_options = $mplf->getByCompanyIdArray( $current_company->getId() );
                unset($mplf);

                $udtlf->getByCompanyIDAndUserIdAndStatusAndTypeAndStartDateAndEndDate( $current_company->getId(), $user_id, 10, 100, $start_date, $end_date);
                if ( $udtlf->getRecordCount() > 0 ) {
                    foreach($udtlf->rs as $udt_obj) {
                        $udtlf->data = (array)$udt_obj;
                        $udt_obj = $udtlf;

                        $user_date_stamp = TTDate::strtotime( $udt_obj->getColumn('user_date_stamp') );

                        if ( $udt_obj->getMealPolicyID() !== FALSE AND isset($meal_policy_options[$udt_obj->getmealPolicyID()]) ) {
                            $meal_policy = $meal_policy_options[$udt_obj->getmealPolicyID()];
                        } else {
                            $meal_policy = _('No Meal Policy');
                        }

                        $date_meal_totals[$user_date_stamp][] = array(
                                                        'date_stamp' => $udt_obj->getColumn('user_date_stamp'),
                                                        'id' => $udt_obj->getId(),
                                                        'user_date_id' => $udt_obj->getUserDateId(),
                                                        'status_id' => $udt_obj->getStatus(),
                                                        'type_id' => $udt_obj->getType(),
                                                        'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
                                                        'meal_policy_id' => $udt_obj->getmealPolicyID(),
                                                        'meal_policy' => $meal_policy,
                                                        'department_id' => $udt_obj->getDepartment(),
                                                        'total_time' => $udt_obj->getTotalTime(),
                                                        'total_time_display' => abs($udt_obj->getTotalTime()),
                                                        //'name' => $udt_obj->getName(),
                                                        'override' => $udt_obj->getOverride()
                                                        );

                        $date_meal_total_policy_ids[] = (int)$udt_obj->getMealPolicyID();
                        $date_total_meal_ids[] = (int)$udt_obj->getMealPolicyID();
                    }
                }

                if ( isset($date_meal_totals) ) {
                    foreach( $date_meal_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_meal_total_group[$user_date_stamp][$date_data['meal_policy_id']]) ) {
                                $prev_total_time = $date_meal_total_group[$user_date_stamp][$date_data['meal_policy_id']]['total_time'];
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_meal_total_group[$user_date_stamp][$date_data['meal_policy_id']] = $date_data;
                        }
                    }

                    $date_total_meal_ids = array_unique($date_total_meal_ids);
                    sort($date_total_meal_ids);

                    $date_meal_policy_total_rows = TimeSheetFormatArrayByDate( $date_meal_total_group, $date_total_meal_ids, $calendar_array, 'meal_policy');
                    //var_dump($date_meal_policy_total_rows);
                }


                $bplf = new BreakPolicyListFactory(); 
                $break_policy_options = $bplf->getByCompanyIdArray( $current_company->getId() );
                unset($bplf);

                $udtlf->getByCompanyIDAndUserIdAndStatusAndTypeAndStartDateAndEndDate( $current_company->getId(), $user_id, 10, 110, $start_date, $end_date);
                if ( $udtlf->getRecordCount() > 0 ) {
                    foreach($udtlf->rs as $udt_obj) {
                        $udtlf->data = (array)$udt_obj;
                        $udt_obj = $udtlf;

                        $user_date_stamp = TTDate::strtotime( $udt_obj->getColumn('user_date_stamp') );

                        if ( $udt_obj->getBreakPolicyID() !== FALSE AND isset($break_policy_options[$udt_obj->getBreakPolicyID()]) ) {
                            $break_policy = $break_policy_options[$udt_obj->getBreakPolicyID()];
                        } else {
                            $break_policy = _('No Break Policy');
                        }

                        $date_break_policy_totals[$user_date_stamp][] = array(
                                                        'date_stamp' => $udt_obj->getColumn('user_date_stamp'),
                                                        'id' => $udt_obj->getId(),
                                                        'user_date_id' => $udt_obj->getUserDateId(),
                                                        'status_id' => $udt_obj->getStatus(),
                                                        'type_id' => $udt_obj->getType(),
                                                        'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
                                                        'break_policy_id' => $udt_obj->getbreakPolicyID(),
                                                        'break_policy' => $break_policy,
                                                        'department_id' => $udt_obj->getDepartment(),
                                                        'total_time' => $udt_obj->getTotalTime(),
                                                        'total_time_display' => abs($udt_obj->getTotalTime()),
                                                        //'name' => $udt_obj->getName(),
                                                        'override' => $udt_obj->getOverride()
                                                        );

                        $date_break_policy_total_policy_ids[] = (int)$udt_obj->getBreakPolicyID();
                        $date_total_break_policy_ids[] = (int)$udt_obj->getBreakPolicyID();
                    }
                }

                if ( isset($date_break_policy_totals) ) {
                    foreach( $date_break_policy_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_break_policy_total_group[$user_date_stamp][$date_data['break_policy_id']]) ) {
                                $prev_total_time = $date_break_policy_total_group[$user_date_stamp][$date_data['break_policy_id']]['total_time'];
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_break_policy_total_group[$user_date_stamp][$date_data['break_policy_id']] = $date_data;
                        }
                    }

                    $date_total_break_policy_ids = array_unique($date_total_break_policy_ids);
                    sort($date_total_break_policy_ids);

                    $date_break_policy_total_rows = TimeSheetFormatArrayByDate( $date_break_policy_total_group, $date_total_break_policy_ids, $calendar_array, 'break_policy');
                }

                //Get only system totals.
                $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $current_company->getId(), $user_id, 10, $start_date, $end_date);
                
                if ( $udtlf->getRecordCount() > 0 ) {
                    foreach($udtlf->rs as $udt_obj) {
                        $udtlf->data = (array)$udt_obj;
                        $udt_obj = $udtlf;

                        $user_date_stamp = TTDate::strtotime( $udt_obj->getColumn('user_date_stamp') );

                        $type_and_policy_id = $udt_obj->getType().(int)$udt_obj->getOverTimePolicyID();

                        $date_totals[$user_date_stamp][] = array(
                                                        'date_stamp' => $udt_obj->getColumn('user_date_stamp'),
                                                        'id' => $udt_obj->getId(),
                                                        'user_date_id' => $udt_obj->getUserDateId(),
                                                        'status_id' => $udt_obj->getStatus(),
                                                        'type_id' => $udt_obj->getType(),
                                                        'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
                                                        'type_and_policy_id' => $type_and_policy_id,
                                                        'branch_id' => (int)$udt_obj->getBranch(),
                                                        'department_id' => $udt_obj->getDepartment(),
                                                        'total_time' => $udt_obj->getTotalTime(),
                                                        'name' => $udt_obj->getName(),
                                                        //Override only shows for SYSTEM override columns...
                                                        //FIXME: Need to check Worked overrides too.
                                                        'tmp_override' => $udt_obj->getOverride()
                                                        );

                        $date_total_type_ids[$type_and_policy_id] = NULL;
                        //$date_total_type_ids[] = $type_and_policy_id;
                    }
                } else {
                    $date_totals[$start_date][] =   array(
                                                        'date_stamp' => $start_date,
                                                        'type_and_policy_id' => 100,
                                                        'total_time' => 0,
                                                        'name' => _('Total Time'),
                                                        'tmp_override' => FALSE
                                                    );
                    $date_total_type_ids[100] = NULL;
                }
                //echo '<pre>'; print_r($date_totals);die;

                if ( isset($date_totals) ) {
                    //Group Date Totals
                    foreach( $date_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_total_group[$user_date_stamp][$date_data['type_and_policy_id']]) ) {
                                $prev_total_time = $date_total_group[$user_date_stamp][$date_data['type_and_policy_id']]['total_time'];
                            }

                            if ( $date_data['tmp_override'] == TRUE AND isset($date_total_group[$user_date_stamp][100]) ) {
                                $date_total_group[$user_date_stamp][100]['override'] = TRUE;
                            }else{
                                $date_total_group[$user_date_stamp][100]['override'] = FALSE;
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_total_group[$user_date_stamp][$date_data['type_and_policy_id']] = $date_data;
                        }
                    }

                    //We want to keep the order of the SQL query, so use this method instead.
                    if ( isset($date_total_type_ids) ) {
                        $date_total_type_ids = array_keys($date_total_type_ids);
                        sort($date_total_type_ids); //Keep Total, then Regular first.
                    }

                    $date_total_rows = TimeSheetFormatArrayByDate( $date_total_group, $date_total_type_ids, $calendar_array, 'name');
                }
                //

                /*


                    Get Branch/Department Totals


                */

                /*
                $job_options = array();
                $job_item_options = array();
                if ( $current_company->getProductEdition() == 20 ) {
                    $jlf = new JobListFactory();
                    $job_options = $jlf->getByCompanyIdArray( $current_company->getId(), FALSE );

                    $jilf = new JobItemListFactory();
                    $job_item_options = $jilf->getByCompanyIdArray( $current_company->getId(), FALSE );
                }
                */

                $udtlf = new UserDateTotalListFactory();
                //Get only worked/paid absence totals.
                $udtlf->getPaidTimeByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $current_company->getId(), $user_id, array(10,30) , $start_date, $end_date);
                if ( $udtlf->getRecordCount() > 0 ) {
                    foreach($udtlf->rs as $udt_obj) {
                        $udtlf->data = (array)$udt_obj;
                        $udt_obj = $udtlf;

                        $user_date_stamp = TTDate::strtotime( $udt_obj->getColumn('user_date_stamp') );

                        if ( $udt_obj->getBranch() != 0 AND isset($branch_options[$udt_obj->getBranch()]) ) {
                            $branch = $branch_options[$udt_obj->getBranch()];
                        } else {
                            $branch = _('No Branch');
                        }

                        if ( $udt_obj->getDepartment() != 0 AND isset($department_options[$udt_obj->getDepartment()]) ) {
                            $department = $department_options[$udt_obj->getDepartment()];
                        } else {
                            $department = _('No Department');
                        }

                        if ( $udt_obj->getJob() != FALSE AND isset($job_options[$udt_obj->getJob()]) ) {
                            $job = $job_options[$udt_obj->getJob()];
                        } else {
                            $job = _('No Job');
                        }

                        if ( $udt_obj->getJobItem() != FALSE AND isset($job_item_options[$udt_obj->getJobItem()]) ) {
                            $job_item = $job_item_options[$udt_obj->getJobItem()];
                        } else {
                            $job_item = _('No Task');
                        }

                        $date_worked_totals[$user_date_stamp][] = array(
                                                        'date_stamp' => $udt_obj->getColumn('user_date_stamp'),
                                                        'id' => $udt_obj->getId(),
                                                        'user_date_id' => $udt_obj->getUserDateId(),
                                                        'status_id' => $udt_obj->getStatus(),
                                                        'type_id' => $udt_obj->getType(),
                                                        'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
                                                        'branch_id' => (int)$udt_obj->getBranch(),
                                                        'branch' => $branch,
                                                        'department_id' => $udt_obj->getDepartment(),
                                                        'department' => $department,
                                                        'job_id' => $udt_obj->getJob(),
                                                        'job' => $job,
                                                        'job_item_id' => $udt_obj->getJobItem(),
                                                        'job_item' => $job_item,
                                                        'total_time' => $udt_obj->getTotalTime(),
                                                        'name' => $udt_obj->getName()
                                                        );

                        $date_worked_total_branch_ids[] = (int)$udt_obj->getBranch();
                        $date_worked_total_department_ids[] = (int)$udt_obj->getDepartment();
                        $date_worked_total_job_ids[] = (int)$udt_obj->getJob();
                        $date_worked_total_job_item_ids[] = (int)$udt_obj->getJobItem();
                    }
                }
                //var_dump($date_worked_totals);

                if ( isset($date_worked_totals) ) {
                    //Branch Rows
                    foreach( $date_worked_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_branch_total_group[$user_date_stamp][$date_data['branch_id']]) ) {
                                $prev_total_time = $date_branch_total_group[$user_date_stamp][$date_data['branch_id']]['total_time'];
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_branch_total_group[$user_date_stamp][$date_data['branch_id']] = $date_data;
                        }
                    }
                    unset($prev_total_time, $date_rows, $date_data);
                    //var_dump($date_worked_totals);

                    $date_worked_total_branch_ids = array_unique($date_worked_total_branch_ids);
                    sort($date_worked_total_branch_ids);

                    if ( $date_worked_total_branch_ids[0] != FALSE OR count($date_worked_total_branch_ids) > 1 ) {
                        Debug::text('Formatting Branch Array By Date: ', __FILE__, __LINE__, __METHOD__,10);
                        $date_branch_total_rows = TimeSheetFormatArrayByDate( $date_branch_total_group, $date_worked_total_branch_ids, $calendar_array, 'branch');
                    }

                    //Deparment rows
                    foreach( $date_worked_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_department_total_group[$user_date_stamp][$date_data['department_id']]) ) {
                                $prev_total_time = $date_department_total_group[$user_date_stamp][$date_data['department_id']]['total_time'];
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_department_total_group[$user_date_stamp][$date_data['department_id']] = $date_data;
                        }
                    }
                    unset($prev_total_time, $date_rows, $date_data);
                    //var_dump($date_department_total_group);

                    $date_worked_total_department_ids = array_unique($date_worked_total_department_ids);
                    sort($date_worked_total_department_ids);

                    if ( $date_worked_total_department_ids[0] != FALSE OR count($date_worked_total_department_ids) > 1 ) {
                        $date_department_total_rows = TimeSheetFormatArrayByDate( $date_department_total_group, $date_worked_total_department_ids, $calendar_array, 'department');
                    }


                    //Job rows
                    foreach( $date_worked_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_job_total_group[$user_date_stamp][$date_data['job_id']]) ) {
                                $prev_total_time = $date_job_total_group[$user_date_stamp][$date_data['job_id']]['total_time'];
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_job_total_group[$user_date_stamp][$date_data['job_id']] = $date_data;
                        }
                    }
                    unset($prev_total_time, $date_rows, $date_data);
                    //var_dump($date_department_total_group);

                    $date_worked_total_job_ids = array_unique($date_worked_total_job_ids);
                    sort($date_worked_total_job_ids);

                    if ( $date_worked_total_job_ids[0] != FALSE OR count($date_worked_total_job_ids) > 1 ) {
                        $date_job_total_rows = TimeSheetFormatArrayByDate( $date_job_total_group, $date_worked_total_job_ids, $calendar_array, 'job', 'job_id');
                    }

                    //Job Item rows
                    foreach( $date_worked_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_job_item_total_group[$user_date_stamp][$date_data['job_item_id']]) ) {
                                $prev_total_time = $date_job_item_total_group[$user_date_stamp][$date_data['job_item_id']]['total_time'];
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_job_item_total_group[$user_date_stamp][$date_data['job_item_id']] = $date_data;
                        }
                    }
                    unset($prev_total_time, $date_rows, $date_data);
                    //var_dump($date_department_total_group);

                    $date_worked_total_job_item_ids = array_unique($date_worked_total_job_item_ids);
                    sort($date_worked_total_job_item_ids);

                    if ( $date_worked_total_job_item_ids[0] != FALSE OR count($date_worked_total_job_item_ids) > 1 ) {
                        $date_job_item_total_rows = TimeSheetFormatArrayByDate( $date_job_item_total_group, $date_worked_total_job_item_ids, $calendar_array, 'job_item', 'job_item_id');
                    }

                }

                /*


                    Get Premium Time


                */
                $pplf_b = new PremiumPolicyListFactory();
                $premium_policy_options = $pplf_b->getByCompanyIdArray( $current_company->getId() );
                unset($pplf_b);

                $udtlf = new UserDateTotalListFactory();
                //Get only worked totals.
                $udtlf->getByCompanyIDAndUserIdAndStatusAndTypeAndStartDateAndEndDate( $current_company->getId(), $user_id, 10, 40, $start_date, $end_date);
                if ( $udtlf->getRecordCount() > 0 ) {
                    foreach($udtlf->rs as $udt_obj) {
                        $udtlf->data = (array)$udt_obj;
                        $udt_obj = $udtlf;

                        $user_date_stamp = TTDate::strtotime( $udt_obj->getColumn('user_date_stamp') );

                        if ( $udt_obj->getPremiumPolicyID() !== FALSE AND isset($premium_policy_options[$udt_obj->getPremiumPolicyID()]) ) {
                            $premium_policy = $premium_policy_options[$udt_obj->getPremiumPolicyID()];
                        } else {
                            $premium_policy = _('No Policy');
                        }

                        $date_premium_totals[$user_date_stamp][] = array(
                                                        'date_stamp' => $udt_obj->getColumn('user_date_stamp'),
                                                        'id' => $udt_obj->getId(),
                                                        'user_date_id' => $udt_obj->getUserDateId(),
                                                        'status_id' => $udt_obj->getStatus(),
                                                        'type_id' => $udt_obj->getType(),
                                                        'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
                                                        'premium_policy_id' => $udt_obj->getPremiumPolicyID(),
                                                        'premium_policy' => $premium_policy,
                                                        'department_id' => $udt_obj->getDepartment(),
                                                        'total_time' => $udt_obj->getTotalTime(),
                                                        //'name' => $udt_obj->getName(),
                                                        'override' => $udt_obj->getOverride()
                                                        );

                        $date_premium_total_policy_ids[] = (int)$udt_obj->getPremiumPolicyID();
                        $date_total_premium_ids[] = (int)$udt_obj->getPremiumPolicyID();
                    }
                }

                if ( isset($date_premium_totals) ) {
                    foreach( $date_premium_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_premium_total_group[$user_date_stamp][$date_data['premium_policy_id']]) ) {
                                $prev_total_time = $date_premium_total_group[$user_date_stamp][$date_data['premium_policy_id']]['total_time'];
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_premium_total_group[$user_date_stamp][$date_data['premium_policy_id']] = $date_data;
                        }
                    }

                    $date_total_premium_ids = array_unique($date_total_premium_ids);
                    sort($date_total_premium_ids);

                    $date_premium_total_rows = TimeSheetFormatArrayByDate( $date_premium_total_group, $date_total_premium_ids, $calendar_array, 'premium_policy');
                    //var_dump($date_premium_total_rows);
                }


                /*


                    Get absences


                */
                //$aplf = new AccrualPolicyListFactory();
                //$absence_policy_options = $aplf->getByCompanyIdArray( $current_company->getId() );
                        $aplf = new AbsencePolicyListFactory(); 
                $absence_policy_options = $aplf->getByCompanyIdArray( $current_company->getId() );

                        
                $udtlf = new UserDateTotalListFactory();
                //Get only worked totals.
                $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $current_company->getId(), $user_id, 30, $start_date, $end_date);
                if ( $udtlf->getRecordCount() > 0 ) {
                    foreach($udtlf->rs as $udt_obj) {
                        $udtlf->data = (array)$udt_obj;
                        $udt_obj = $udtlf;

                        $user_date_stamp = TTDate::strtotime( $udt_obj->getColumn('user_date_stamp') );

                        if ( $udt_obj->getAbsencePolicyID() !== FALSE ) {
                            $absence_policy = $absence_policy_options[$udt_obj->getAbsencePolicyID()];
                        } else {
                            $absence_policy = _('No Policy');
                        }
                        /*              
                        if ( $udt_obj->getBranch() !== FALSE ) {
                            $branch = $branch_options[$udt_obj->getBranch()];
                        } else {
                            $branch = 'No Branch';
                        }
                        */

                        $date_absence_totals[$user_date_stamp][] = array(
                                                        'date_stamp' => $udt_obj->getColumn('user_date_stamp'),
                                                        'id' => $udt_obj->getId(),
                                                        'user_date_id' => $udt_obj->getUserDateId(),
                                                        'status_id' => $udt_obj->getStatus(),
                                                        'type_id' => $udt_obj->getType(),
                                                        'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
                                                        'absence_policy_id' => $udt_obj->getAbsencePolicyID(),
                                                        'absence_policy' => $absence_policy,
                                                        //'branch_id' => (int)$udt_obj->getBranch(),
                                                        //'branch' => $branch,
                                                        'department_id' => $udt_obj->getDepartment(),
                                                        'total_time' => $udt_obj->getTotalTime(),
                                                        'name' => $udt_obj->getName(),
                                                        'override' => $udt_obj->getOverride()
                                                        );

                        $date_absence_total_policy_ids[] = (int)$udt_obj->getAbsencePolicyID();
                        $date_total_absence_ids[] = (int)$udt_obj->getAbsencePolicyID();
                    }
                } 
                //                die;

                if ( isset($date_absence_totals) ) {
                    foreach( $date_absence_totals as $user_date_stamp => $date_rows ) {
                        foreach($date_rows as $date_data) {
                            $prev_total_time = 0;
                            if ( isset($date_absence_total_group[$user_date_stamp][$date_data['absence_policy_id']]) ) {
                                $prev_total_time = $date_absence_total_group[$user_date_stamp][$date_data['absence_policy_id']]['total_time'];
                            }

                            $date_data['total_time'] = $date_data['total_time'] + $prev_total_time;
                            $date_absence_total_group[$user_date_stamp][$date_data['absence_policy_id']] = $date_data;
                        }
                    }

                    $date_total_absence_ids = array_unique($date_total_absence_ids);
                    sort($date_total_absence_ids);

                    $date_absence_total_rows = TimeSheetFormatArrayByDate( $date_absence_total_group, $date_total_absence_ids, $calendar_array, 'absence_policy');
                    //var_dump($date_absence_total_rows);
                }

                /*


                    Get Exceptions


                */
                $elf = new ExceptionListFactory(); 
                $elf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $current_company->getID(), $user_id, $start_date, $end_date);
                $punch_exceptions = array();
                if ( $elf->getRecordCount() > 0 ) {
                    Debug::text('Found exceptions!: ', __FILE__, __LINE__, __METHOD__,10);

                    foreach( $elf->rs as $e_obj ) {
                        $elf->data = (array)$e_obj;
                        $e_obj = $elf;

                        $user_date_stamp = TTDate::strtotime( $e_obj->getColumn('user_date_stamp') );


                        $exception_data_arr = array(
                                                    'type_id' => $e_obj->getType(),
                                                    'severity_id' => $e_obj->getColumn('severity_id'),
                                                    'exception_policy_type_id' => $e_obj->getColumn('exception_policy_type_id'),
                                                    'color' => $e_obj->getColor(),
                                                );

                        if ( $e_obj->getPunchId() != '' ) {
                            $punch_exceptions[$e_obj->getPunchId()][] = $exception_data_arr;
                        }
                        if ( $e_obj->getPunchId() == '' AND $e_obj->getPunchControlId() != '' ) {
                            $punch_control_exceptions[$e_obj->getPunchControlId()][] = $exception_data_arr;
                        }

                        $date_exceptions[$user_date_stamp][] = $exception_data_arr;
                        if ( !isset($unique_exceptions[$e_obj->getColumn('exception_policy_type_id')])
                                OR ( $unique_exceptions[$e_obj->getColumn('exception_policy_type_id')]['severity_id'] < $exception_data_arr['severity_id']) ) {
                            $unique_exceptions[$e_obj->getColumn('exception_policy_type_id')] = $exception_data_arr;
                        }
                    }
                    
                    unset($exception_data_arr);
                }

                if ( isset($date_exceptions) ) {
                    foreach( $calendar_array as $cal_arr ) {
                        if ( isset($date_exceptions[$cal_arr['epoch']])) {
                            $exception_data = $date_exceptions[$cal_arr['epoch']];
                        } else {
                            $exception_data = NULL;
                        }

                        $date_exception_total_rows[] = $exception_data;
                    }
                }


                //Get exception names for legend.
                if ( isset($unique_exceptions) ) {
                    $epf = new ExceptionPolicyFactory();
                    $exception_options = $epf->getOptions('type');
                    foreach( $unique_exceptions as $unique_exception ) {
                        $unique_exceptions[$unique_exception['exception_policy_type_id']]['name'] = $exception_options[$unique_exception['exception_policy_type_id']];
                    }

                    sort($unique_exceptions);
                }

                /*


                    Get Pending Requests


                */
                $rlf = new RequestListFactory(); 
                $rlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $current_company->getID(), $user_id, 30, $start_date, $end_date);
                if ( $rlf->getRecordCount() > 0 ) {
                    Debug::text('Found Requests!!: ', __FILE__, __LINE__, __METHOD__,10);

                    foreach( $rlf->rs as $r_obj ) {
                        $rlf->data = (array)$r_obj;
                        $r_obj = $rlf;

                        $user_date_stamp = TTDate::strtotime( $r_obj->getColumn('date_stamp') );


                        $request_data_arr = array(
                                                                        'id' => $r_obj->getId()
                                                                    );

                        $date_requests[$user_date_stamp][] = $request_data_arr;
                    }
                }

                if ( isset($date_requests) ) {
                    foreach( $calendar_array as $cal_arr ) {
                        if ( isset($date_requests[$cal_arr['epoch']])) {
                            $request_data = $date_requests[$cal_arr['epoch']];
                        } else {
                            $request_data = NULL;
                        }

                        $date_request_total_rows[$cal_arr['epoch']] = $request_data;
                    }
                }

                /*

                    Get Holidays

                */

                $hlf = new HolidayListFactory();
                $holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_id, $start_date, $end_date );
                //var_dump($holiday_array);

                /*

                    Get pay period locked days

                */
                if ( isset($pay_period_obj) AND is_object($pay_period_obj) ) {
                    foreach( $calendar_array as $cal_arr ) {
                        if ( $cal_arr['epoch'] >= $pay_period_obj->getStartDate()
                                AND $cal_arr['epoch'] <= $pay_period_obj->getEndDate() ) {
                            //Debug::text('Current Pay Period: '. TTDate::getDate('DATE+TIME', $cal_arr['epoch'] ), __FILE__, __LINE__, __METHOD__,10);
                            $pay_period_locked_rows[$cal_arr['epoch']] = $pay_period_obj->getIsLocked();
                        } else {
                            //Debug::text('Diff Pay Period...', __FILE__, __LINE__, __METHOD__,10);
                            //FIXME: Add some caching here perhaps?
                            $pplf->getByUserIdAndEndDate( $user_id, $cal_arr['epoch'] );
                            if ( $pplf->getRecordCount() > 0 ) {
                                $tmp_pay_period_obj = $pplf->getCurrent();
                                $pay_period_locked_rows[$cal_arr['epoch']] = $tmp_pay_period_obj->getIsLocked();
                            } else {
                                //Debug::text('  Did not Found rows...', __FILE__, __LINE__, __METHOD__,10);
                                //Allow them to edit payperiods in future.
                                $pay_period_locked_rows[$cal_arr['epoch']] = FALSE;
                            }
                        }

                    }
                    unset($tmp_pay_period_obj);
                }
                //var_dump();

                /*

                    Get TimeSheet verification

                */
                if ( isset($pay_period_obj) AND is_object($pay_period_obj) ) {
                    $is_timesheet_superior = FALSE;
                    $pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
                    $pptsvlf->getByPayPeriodIdAndUserId( $pay_period_obj->getId(), $user_id );

                    if ( $pptsvlf->getRecordCount() > 0 ) {
                        $pptsv_obj = $pptsvlf->getCurrent();
                        $pptsv_obj->setCurrentUser( $current_user->getId() );
                    } else {
                        $pptsv_obj = $pptsvlf;
                        $pptsv_obj->setCurrentUser( $current_user->getId() );
                        $pptsv_obj->setUser( $user_id );
                        $pptsv_obj->setPayPeriod( $pay_period_obj->getId() );
                        //$pptsv_obj->setStatus( 45 ); //Pending Verification
                    }

                    $time_sheet_verify = array(
                                            'id' => $pptsv_obj->getId(),
                                            'user_verified' => $pptsv_obj->getUserVerified(),
                                            'user_verified_date' => $pptsv_obj->getUserVerifiedDate(),
                                            'status_id' => $pptsv_obj->getStatus(),
                                            'status' => Option::getByKey( $pptsv_obj->getStatus(), $pptsv_obj->getOptions('status') ),
                                            'pay_period_id' => $pptsv_obj->getPayPeriod(),
                                            'user_id' => $pptsv_obj->getUser(),
                                            'authorized' => $pptsv_obj->getAuthorized(),
                                            'authorized_users' => $pptsv_obj->getAuthorizedUsers(),
                                            'is_hierarchy_superior' => $pptsv_obj->isHierarchySuperior(),
                                            'display_verify_button' => $pptsv_obj->displayVerifyButton(),
                                            'verification_box_color' => $pptsv_obj->getVerificationBoxColor(),
                                            'verification_status_display' => $pptsv_obj->getVerificationStatusDisplay(),
                                            'previous_pay_period_verification_display' => $pptsv_obj->displayPreviousPayPeriodVerificationNotice(),
                                            'created_date' => $pptsv_obj->getCreatedDate(),
                                            'created_by' => $pptsv_obj->getCreatedBy(),
                                            'updated_date' => $pptsv_obj->getUpdatedDate(),
                                            'updated_by' => $pptsv_obj->getUpdatedBy(),
                                            'deleted_date' => $pptsv_obj->getDeletedDate(),
                                            'deleted_by' => $pptsv_obj->getDeletedBy()
                                            );
                }

                //Get pay period totals
                //Sum all Worked Hours
                //Sum all Paid Absences
                //Sum all Dock Absences
                //Sum all Regular/OverTime hours
                $udtlf = new UserDateTotalListFactory();
                $worked_total_time = (int)$udtlf->getWorkedTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id );
                Debug::text('Worked Total Time: '. $worked_total_time, __FILE__, __LINE__, __METHOD__,10);

                $paid_absence_total_time = $udtlf->getPaidAbsenceTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id );
                Debug::text('Paid Absence Total Time: '. $paid_absence_total_time, __FILE__, __LINE__, __METHOD__,10);

                $dock_absence_total_time = $udtlf->getDockAbsenceTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id );
                Debug::text('Dock Absence Total Time: '. $dock_absence_total_time, __FILE__, __LINE__, __METHOD__,10);

                $udtlf->getRegularAndOverTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id );
                if ( $udtlf->getRecordCount() > 0 ) {
                    //Get overtime policy names
                    $otplf = new OverTimePolicyListFactory(); 
                    $over_time_policy_options = $otplf->getByCompanyIdArray( $current_company->getId(), FALSE );

                    foreach($udtlf->rs as $udt_obj ) {
                        $udtlf->data = (array)$udt_obj;
                        $udt_obj = $udtlf;
                        
                        Debug::text('Type ID: '. $udt_obj->getColumn('type_id') .' OverTime Policy ID: '. $udt_obj->getColumn('over_time_policy_id') .' Total Time: '. $udt_obj->getColumn('total_time'), __FILE__, __LINE__, __METHOD__,10);

                        if ( $udt_obj->getColumn('type_id') == 20 ) {
                            $name = _('Regular Time');
                        } else {
                            if ( isset($over_time_policy_options[$udt_obj->getColumn('over_time_policy_id')]) ) {
                                $name = $over_time_policy_options[$udt_obj->getColumn('over_time_policy_id')];
                            } else {
                                $name = _('N/A');
                            }
                        }

                        if ( $udt_obj->getColumn('type_id') == 20 ) {
                            $total_time = $udt_obj->getColumn('total_time') + $paid_absence_total_time;
                        } else {
                            $total_time = $udt_obj->getColumn('total_time');
                        }

                        $pay_period_total_rows[] = array( 'name' => $name, 'total_time' => $total_time );
                    }
                    //var_dump($pay_period_total_rows);
                }

                //echo '<pre>---------------------------------<br><br><br><br><br><br><br><br><br><br>';
                //print_r($punch_control_exceptions);
                //print_r($punch_exceptions);
                //echo '<pre>';
                    // print_r($rows);
                        //exit();
                        
                $viewData['calendar_array'] = $calendar_array;
                $viewData['rows'] = $rows;
                $viewData['date_break_total_rows'] = $date_break_total_rows;
                $viewData['date_break_policy_total_rows'] = $date_break_policy_total_rows;
                $viewData['date_meal_policy_total_rows'] = $date_meal_policy_total_rows;
                $viewData['date_total_rows'] = $date_total_rows;
                $viewData['date_branch_total_rows'] = $date_branch_total_rows;
                $viewData['date_department_total_rows'] = $date_department_total_rows;
                $viewData['date_job_total_rows'] = $date_job_total_rows;
                $viewData['date_job_item_total_rows'] = $date_job_item_total_rows;
                $viewData['date_premium_total_rows'] = $date_premium_total_rows;
                $viewData['date_absence_total_rows'] = $date_absence_total_rows;
                $viewData['punch_exceptions'] = $punch_exceptions ;
                $viewData['punch_control_exceptions'] = $punch_control_exceptions ;
                $viewData['date_exception_total_rows'] = $date_exception_total_rows;
                $viewData['date_request_total_rows'] = $date_request_total_rows;
                $viewData['exception_legend'] = $unique_exceptions;
                $viewData['pay_period_total_rows'] = $pay_period_total_rows;
                $viewData['holidays'] = $holiday_array;
                $viewData['pay_period_locked_rows'] = $pay_period_locked_rows;
                $viewData['pay_period_worked_total_time'] = $worked_total_time;
                $viewData['pay_period_paid_absence_total_time'] = $paid_absence_total_time;
                $viewData['pay_period_dock_absence_total_time'] = $dock_absence_total_time;
                $viewData['time_sheet_verify'] = $time_sheet_verify;

                $is_assigned_pay_period_schedule = FALSE;
                if ( isset($pay_period_obj) AND is_object($pay_period_obj) ) {
                    Debug::text('Pay Period Object Found!', __FILE__, __LINE__, __METHOD__,10);

                    $is_assigned_pay_period_schedule = TRUE;

                    //Don't use assign_by_ref for these as that seems to trigger a fatal error in PHP v5.0.4
                    $viewData['pay_period_id'] = $pay_period_obj->getId();
                    $viewData['pay_period_start_date'] = $pay_period_obj->getStartDate();
                    $viewData['pay_period_end_date'] = $pay_period_obj->getEndDate();
                    $viewData['pay_period_verify_type_id'] = $pay_period_obj->getTimeSheetVerifyType();
                    $viewData['pay_period_verify_window_start_date'] = $pay_period_obj->getTimeSheetVerifyWindowStartDate();
                    $viewData['pay_period_verify_window_end_date'] = $pay_period_obj->getTimeSheetVerifyWindowEndDate();
                    $viewData['pay_period_transaction_date'] = $pay_period_obj->getTransactionDate();
                    $viewData['pay_period_is_locked'] = $pay_period_obj->getIsLocked();
                    $viewData['pay_period_status_id'] = $pay_period_obj->getStatus();

                } else {
                    Debug::text('Pay Period Object NOT Found!', __FILE__, __LINE__, __METHOD__,10);
                    //Check to see if employee is even assigned to pay period schedule.
                    $ppslf = new PayPeriodScheduleListFactory();
                    $ppslf->getByCompanyIdAndUserId( $current_company->getId(), $user_id );
                    if ( $ppslf->getRecordCount() > 0 ) {
                        Debug::text('Pay Period Schedule Found!', __FILE__, __LINE__, __METHOD__,10);
                        $is_assigned_pay_period_schedule = TRUE;
                    }
                }
                $viewData['is_assigned_pay_period_schedule'] = $is_assigned_pay_period_schedule;

                $action_options = array(
                                    '0' => _('-- Select Action --'),
                                    'recalculate_employee' => 	_('Recalculate Employee'),
                                    'recalculate_company' => 	_('Recalculate Company') );

                if ( $permission->Check('pay_period_schedule','enabled') AND ( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {
                    $action_options['recalculate_mid_pay'] = _('Recalculate Mid Pay');
                                $action_options['recalculate_pay_stub'] = _('Recalculate FInal Pay');
                }

                if ( isset($pay_period_obj) AND is_object($pay_period_obj) AND $pay_period_obj->getStatus() == 30 ) {
                    //Add a spacer so its less likely for someone to accidently hit "Recalc Pay Stub"
                    //instead of calculate adjustment.
                    $action_options['-1'] = '---';
                    $action_options['calculate_adjustment']  = _('Calculate PS Adjustment');
                }

                $all_array_option = array('-1' => _('-- All --'));
                $viewData['group_options'] = Misc::prependArray( $all_array_option, $group_options ) ;
                $viewData['branch_options'] = Misc::prependArray( $all_array_option, $branch_options ) ;
                $viewData['department_options'] = Misc::prependArray( $all_array_option, $department_options ) ;
                $viewData['user_options'] = $user_options;

                $viewData['is_owner'] = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() ) ;
                $viewData['is_child'] = $permission->isChild( $user_obj->getId(), $permission_children_ids ) ;

                $viewData['action_options'] = $action_options ;
                $viewData['filter_data'] = $filter_data ;
                $viewData['user_obj'] = $user_obj;
                $viewData['start_date'] = $start_date;
                $viewData['end_date'] = $end_date;
                $viewData['current_time'] = TTDate::getTime() ;

                break;
        }
        //dd($viewData);
        return view('timesheet/ViewUserTimeSheet', $viewData);
    }

}


?>