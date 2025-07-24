<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualBalanceFactory;
use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\Accrual\AccrualListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Leaves\LeaveRequestFactory;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use DateTime;
use Illuminate\Support\Facades\View;

class ApplyUserLeave extends Controller
{
    protected $permission;
    protected $current_user;
    protected $current_company;
    protected $current_user_prefs;
    protected $config_vars;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->current_user = View::shared('current_user');
        $this->current_company = View::shared('current_company');
        $this->current_user_prefs = View::shared('current_user_prefs');

        $configFilePath = config('evolve.paths.base_url');  // Get base URL or config file path from config file

        if ($configFilePath) {
            $this->config_vars = config('evolve');  // Load config directly from Laravel config
        } else {
            echo "Config file does not exist!\n";
            exit;
        }

    }

    public function index()
    {
        $current_user = $this->current_user;
        $current_company = $this->current_company;
        /*
        if ( !$permission->Check('accrual','view')
                OR (  $permission->Check('accrual','view_own') ) ) {
            $permission->Redirect( FALSE ); //Redirect
        }
        */

        $viewData['title'] = 'Apply Employee Leaves';

        /*
        * Get FORM variables
        */
        extract	(FormVariables::GetVariables(
            array (
                'action',
                'id',
                'data',
                'filter_data'
            )
        ) );


        if ( isset($data)) {
            if ( !empty($data['start_date']) ) {
                //$data['leave_start_date'] = TTDate::parseDateTime( $data['leave_start_date'] );
            }
            if ( !empty($data['end_date']) ) {
                //$data['leave_end_date'] = TTDate::parseDateTime( $data['leave_end_date'] );
            }
        }

        //echo '<pre>'.print_r($data);exit;

        /*
        if ( isset($data['appt-time']) && $data['appt-time'] != '') {
                //$data['parsed_start_time'] = TTDate::strtotime( $data['appt-time'], $data['start_date_stamp'] ) ;
            echo $data['appt-time']; exit;
        }
        */

        $lrlf = new LeaveRequestListFactory();

        //===================================================================================
        $action = '';
        if (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
        //===================================================================================

        switch ($action) {
            case 'submit':
                Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
                //Debug::setVerbosity(11);

                //  $lrf = new LeaveRequestFactory();

                    //echo $data['leave_start_date'];
                    //exit();

                    $ablf = new AccrualBalanceListFactory();
                    $ablf->getByUserIdAndAccrualPolicyId($current_user->getId(),$data['leave_type']);

                    if( $ablf->getRecordCount() > 0){

                        $abf = $ablf->getCurrent();

                        $balance = $abf->getBalance();

                        // $amount = $data['no_days'];
                        $amount = isset($data['no_days']) ? floatval($data['no_days']) : 0;

                        $amount_taken =0;

                        if($data['method_type'] == 1){
                            $amount_taken = (($amount*8) * (28800/8));
                        }elseif($data['method_type'] == 2){

                            if($amount<1){
                                $amount_taken = (($amount*8) * (28800/8));
                            }else{
                                $amount_taken = (($amount*8) * (28800/8));
                            }
                        }elseif($data['method_type'] == 3){
                            $amount_taken = 4320;

                            //  $start_date_stamp = TTDate::parseDateTime( $data['leave_start_date'].' '.$data['appt-time'] );

                            //  $end_date_stamp = TTDate::parseDateTime( $data['leave_start_date'].' '.$data['end-time'] );

                            $start_date_stamp= TTDate::parseTimeUnit($data['appt-time'] );
                            $end_date_stamp= TTDate::parseTimeUnit($data['end-time'] );

                            $time_diff = $end_date_stamp - $start_date_stamp;

                            if($time_diff <=3600){
                                $time_diff = 3600;
                            }


                            if($time_diff >7200){
                                $time_diff = 7200;
                            }

                            $amount_taken =$time_diff*0.8;
                        }

                        $amount_taken = -1 * abs($amount_taken);

                        $current_amount = abs($amount_taken);

                        if($current_amount <= $balance ){

                            $date_sh_array = explode(',', $data['leave_start_date']);

                            $udtlf_s = new UserDateListFactory();
                            $udtlf_s->getByUserIdAndDate($current_user->getId(), $date_sh_array[0]);

                            $udf_obj = $udtlf_s->getCurrent();
                            $pp_id = $udf_obj->getPayPeriod();

                            $pplf = new PayPeriodListFactory();
                            $pplf->getById($pp_id);
                            $pp_obj = $pplf->getCurrent();

                            $lrlf_s = new LeaveRequestListFactory();

                            $pp_short_leave_count = 0;
                            $row = $lrlf_s->getPayperiodsShortLeaveCount($current_user->getId(), $data['leave_type'], $pp_obj->getStartDate(TRUE), $pp_obj->getEndDate(TRUE));

                            if (is_array($row) && isset($row['count'])) {
                                    $pp_short_leave_count = $row['count'];
                            }


                            $row = $lrlf_s->getPayperiodsShortLeaveCount($current_user->getId(), $data['leave_type'], $pp_obj->getStartDate(TRUE), $pp_obj->getEndDate(TRUE));

                            $pp_short_leave_count = isset($row['count']) ? (int)$row['count'] : 0;

                            if($pp_short_leave_count >= 2 && $data['leave_type'] == 8){
                                $msg = "You can apply only two short leaves";
                            }else{

                                $lrf = new LeaveRequestFactory;

                                $lrf->setCompany($current_company->getId());
                                $lrf->setUser($current_user->getId());
                                $lrf->setDesignation($data['title_id'], true);
                                $lrf->setAccuralPolicy($data['leave_type']);
                                $lrf->setLeaveMethod($data['method_type']);
                                $lrf->setAmount($data['no_days']);

                                if ( isset($data['appt-time']) && $data['appt-time'] != '') {
                                    $lrf->setLeaveTime($data['appt-time']);
                                }

                                if ( isset($data['end-time']) && $data['end-time'] != '') {
                                    $lrf->setLeaveEndTime($data['end-time']);
                                }

                                $date_array = array();
                                if(isset($data['leave_start_date'])){

                                    $lrf->setLeaveDates(trim($data['leave_start_date']));

                                    $date_array = explode(',', $data['leave_start_date']);

                                        $date_count = count($date_array);

                                        if($date_count == 1){

                                            $from_date =  DateTime::createFromFormat('Y-m-d', $date_array[$date_count-1]);
                                            $lrf->setLeaveFrom($from_date->format('Y-m-d'));

                                            $To_date =  DateTime::createFromFormat('Y-m-d', $date_array[$date_count-1]);
                                            $lrf->setLeaveTo($To_date->format('Y-m-d'));

                                        } elseif ($date_count > 1) {

                                            $from_date =  new DateTime($date_array[0]);

                                            $lrf->setLeaveFrom($from_date->format('Y-m-d'));

                                            $To_date =  new DateTime($date_array[$date_count-1]);

                                            $lrf->setLeaveTo($To_date->format('Y-m-d'));

                                        }
                                }

                                //  $from_date =  DateTime::createFromFormat('j-M-y', $data['leave_start_date']);

                                // $To_date =  DateTime::createFromFormat('j-M-y', $data['leave_end_date']);

                                $lrf->setReason($data['reason']);
                                $lrf->setAddressTelephone($data['address_tel']);
                                $lrf->setCoveredBy($data['cover_duty']);
                                $lrf->setSupervisorId($data['supervisor']);
                                $lrf->setCoveredApproved(1);
                                $lrf->setSupervisorApproved(0);
                                $lrf->setHrApproved(0);
                                $lrf->setDeleted(0);
                                $lrf->setStatus(10);

                                if($data['leave_type'] == 3){
                                    $lrf->setCoveredApproved(1);
                                }

                                if($data['leave_type'] == 14){
                                    $lrf->setCoveredApproved(1);
                                }

                                $lrlf_b = new LeaveRequestListFactory();
                                $lrlf_b->checkUserHasLeaveTypeForDay($current_user->getId(), $from_date->format('Y-m-d'), $data['leave_type']);

                                if($lrlf_b->getRecordCount()>0 && $data['leave_type'] == 8){
                                    $msg = "You have This leave for the day";
                                }else{
                                    $lrf->Save();

                                    $supervisors = new UserListFactory();
                                    $supervisors->getById(trim($data['supervisor']));
                                    $supervisor_obj = $supervisors->getCurrent();

                                    $employeeLF = new UserListFactory();
                                    $employeeLF->getById(trim($current_user->getId()));
                                    $employee_obj = $employeeLF->getCurrent();

                                    if ( $supervisor_obj->getWorkEmail() != FALSE ) {
                                        $supervisor_primary_email = $supervisor_obj->getWorkEmail();
                                        if ( $supervisor_obj->getHomeEmail() != FALSE ) {
                                                $supervisor_secondary_email = $supervisor_obj->getHomeEmail();
                                        } else {
                                                $supervisor_secondary_email = NULL;
                                        }
                                    } else {
                                        $supervisor_primary_email = $supervisor_obj->getHomeEmail();
                                        $supervisor_secondary_email = NULL;
                                    }

                                    if ( $employee_obj->getWorkEmail() != FALSE ) {
                                        $employee_primary_email = $employee_obj->getWorkEmail();
                                        if ( $employee_obj->getHomeEmail() != FALSE ) {
                                                $employee_secondary_email = $employee_obj->getHomeEmail();
                                        } else {
                                                $employee_secondary_email = NULL;
                                        }
                                    } else {
                                        $employee_primary_email = $employee_obj->getHomeEmail();
                                        $employee_secondary_email = NULL;
                                    }


                                    $aplf = new AccrualPolicyListFactory();
                                    $aplf->getById($data['leave_type']);

                                    //****************************************************************
                                    // check here - add send mail function

                                    /*
                                    // Create the mail transport configuration
                                    $transport = Swift_MailTransport::newInstance();
                                    $transporter = Swift_SmtpTransport::newInstance($config_vars['mail']['smtp_host'], $config_vars['mail']['smtp_port'], 'ssl')
                                    ->setUsername($config_vars['mail']['smtp_username'])
                                    ->setPassword($config_vars['mail']['smtp_password']);



                                    // Create the message
                                    $message = Swift_Message::newInstance();
                                    $message->setTo(array(
                                    $supervisor_primary_email=> $supervisor_obj->getFullName()
                                    ));
                                    $message->setSubject("New leave request by ". $employee_obj->getFullName());
                                    $htmlContent .= '<p>Dear '.$supervisor_obj->getFullName().'</p>';
                                    $htmlContent .= '<div style="background: rgb(55,110,55); padding-bottom: 0.1px; padding-top: 0.1px;" align="center"><h2 style="color: #fff">Below leave application is pending for your approval.</h2></div>';
                                    $htmlContent .= '<br><br><table><tr><td>Emp No </td>'. "<td>".$employee_obj->getEmployeeNumber()."</td></tr>";
                                    $htmlContent .= '<tr><td>Name </td>'. "<td>".$employee_obj->getFirstName().' '.$employee_obj->getLastName(). "</td></tr>";
                                    $htmlContent .= '<tr><td>Leave Type </td>'. "<td>".  $aplf->getCurrent()->getName()."</td></tr>";
                                    $htmlContent .= '<tr><td>No. of days </td>'. "<td>".$data['no_days']."</td></tr>";
                                    $htmlContent .= '<tr><td>From </td>'. "<td>".$from_date->format('Y-m-d')."</td></tr>";
                                    $htmlContent .= '<tr><td>To </td>'. "<td>".$To_date->format('Y-m-d')."</td></tr></table>";
                                    $htmlContent .= '<tr><td>Dates </td>'. "<td>".$data['leave_start_date']."</td></tr></table>";
                                    $htmlContent .= '<p><b><i><span style="font-family: Helvetica,sans-serif; color:#440062">HR Department</span></i></b></p>';
                                    $message->setBody($htmlContent,'text/html');
                                    $message->setFrom("careers@aquafresh.lk", "HRM");

                                    // Send the email
                                    $mailer = Swift_Mailer::newInstance($transporter);
                                    $mailer->send($message);
                                    */

                                    $msg = "You have successfully apply leave";

                                    //****************************************************************
                                }
                            }
                        }else{
                            $msg = "You don't have sufficent leave";
                        }
                    }else{
                            $msg = "You don't have this leave type";
                    }

                    // dd($msg);
                    return redirect()->route('attendance.apply_leaves')->with('success', 'Leave successfully applied');

                break;

            default:


                break;
        }

        //Select box options; getName() no_days
        $lrlf->getByUserIdAndCompanyId($current_user->getId(),$current_company->getId());

        $leave_request = array();

        if($lrlf->getRecordCount() > 0){

        foreach ($lrlf->rs as $lrf) {
            $lrlf->data = (array)$lrf;
            $lrf = $lrlf;

            $leave = array();

            $leave['name'] = $lrf->getUserObject()->getFullName();
            $leave['from'] = $lrf->getLeaveFrom();
            $leave['to'] = $lrf->getLeaveTo();
            $leave['amount'] = $lrf->getAmount();
            $leave['leave_type'] = $lrf->getAccuralPolicyObject()->getName();
            $leave['status'] = "Pending Aproovals";

            if($lrf->getCoveredApproved() && $lrf->getStatus()==10 ){
                $leave['status'] = "Pending for Authorization ";
            }

            if (!$lrf->getCoveredApproved() && $lrf->getStatus()==20) {
                $leave['status'] = "Cover Rejected";
            }

            if($lrf->getCoveredApproved() && $lrf->getSupervisorApproved() && $lrf->getStatus()==10 ){
                $leave['status'] = "Supervisor Approved";
            }

            if($lrf->getCoveredApproved() && !$lrf->getSupervisorApproved() && $lrf->getStatus()==30 ){
                    $leave['status'] = "Supervisor Rejected";
            }

            if($lrf->getCoveredApproved() && $lrf->getSupervisorApproved() && $lrf->getHrApproved() && $lrf->getStatus()==10){
                $leave['status'] = "HR Approved";
            }

            if($lrf->getCoveredApproved() && $lrf->getSupervisorApproved() && !$lrf->getHrApproved() && $lrf->getStatus()==40){
                $leave['status'] = "HR Rejected";
            }


            $leave_request[]= $leave;
        }

        }



        $alf = new AccrualListFactory();

        $aplf = new AccrualPolicyListFactory();
        $aplf->getByCompanyIdAndTypeId($current_company->getId(),20);

        $header_leave = array();
        $total_asign_leave = array();
        $total_taken_leave = array();
        $total_balance_leave = array();

        foreach($aplf->rs as $apf){
            $aplf->data = (array)$apf;
            $apf = $aplf;

            if($apf->getId() == 4 || $apf->getId() == 11){
                continue;
            }

            $alf->getByCompanyIdAndUserIdAndAccrualPolicyIdAndStatusForLeave($current_company->getId(),$current_user->getId(),$apf->getId(),30);

            $header_leave[]['name']=$apf->getName();

            if($alf->getRecordCount() > 0) {
                $af= $alf->getCurrent();
                $total_asign_leave[]['asign'] =  number_format($af->getAmount()/28800,2);
            } else {
                $total_asign_leave[]['asign'] = 0;
            }

            $ttotal =  $alf->getSumByUserIdAndAccrualPolicyId($current_user->getId(),$apf->getId());

            if($alf->getRecordCount() > 0) {
                $af= $alf->getCurrent();
                $total_taken_leave[]['taken'] = number_format(($af->getAmount()/28800)-($ttotal/28800),2);
                $total_balance_leave[]['balance'] =  number_format(($ttotal/28800),2);
            } else {
                $total_taken_leave[]['taken'] = 0;
                $total_balance_leave[]['balance'] = 0;
            }

        }

        $leave_options = array();
        foreach($aplf->rs as $apf){
            $aplf->data = (array)$apf;
            $apf = $aplf;

            $leave_options[$apf->getId()]=$apf->getName();
            $alf->getByCompanyIdAndUserIdAndAccrualPolicyIdAndStatus($current_company->getId(),$current_user->getId(),$apf->getId(),30);

        }
        $leave_options = Misc::prependArray( array( 0 => '-- Please Choose --' ), $leave_options );
        $data['leave_options'] = $leave_options;

        $method_options = $lrlf->getOptions('leave_method');

        $method_options = Misc::prependArray( array( 0 => '-- Please Choose --' ), $method_options );
        $data['method_options'] = $method_options;

        $ulf = new UserListFactory();
        //$filter_data['default_branch_id'] = $current_user->getDefaultBranch();
        $filter_data['exclude_id'] = 1;

        $ulf->getAPISearchByCompanyIdAndArrayCriteria( $current_company->getId(),$filter_data);
        //$ulf->getAll();

        $user_options = array();

        foreach($ulf->rs as $uf){
            $ulf->data = (array)$uf;
            $uf = $ulf;

            $user_options[$uf->getId()] = $uf->getPunchMachineUserID().'-'.$uf->getFullName() ;

        }

        $user_options = Misc::prependArray( array( 0 => '-- Please Choose --' ), $user_options );
        $data['users_cover_options'] = $user_options;
        //$data['users_cover_options'] = $ulf;

        $data['name'] =$current_user->getFullName();
        $data['title'] = $current_user->getTitleObject()->getName(); //if this gives you error that means you should add/update data on 'user_title' table in db (it had deleted values and it gave me an error)
        $data['title_id'] = $current_user->getTitleObject()->getId();

        $data['leave_start_date'] = '';
        //$data['reason']="";

        //$data['no_days'] = '';
        $viewData['total_asign_leave'] = $total_asign_leave;
        $viewData['total_taken_leave'] = $total_taken_leave;
        $viewData['total_balance_leave'] = $total_balance_leave;
        $viewData['header_leave'] = $header_leave;

        $data['msg'] = isset($msg) ? $msg : '' ;

        $viewData['leave_request'] = $leave_request;
        $viewData['data'] = $data;
        $viewData['user'] = $current_user;

        //$current_user->getId()

        // dd($viewData);
        return view('leaves/ApplyUserLeave', $viewData);

    }


}





?>
