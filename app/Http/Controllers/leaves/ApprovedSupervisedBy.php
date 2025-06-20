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
use App\Models\Core\UserDateTotalFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Policy\OverTimePolicyFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Punch\PunchControlListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Users\UserListFactory;
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class ApprovedSupervisedBy extends Controller
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


    public function index()
    {
        $current_user = $this->currentUser;
        $viewData['title'] = 'Employee Leaves Supervisor Aprooval';
        $msg = "";

        $lrlf = new LeaveRequestListFactory();
        $lrlf->getBySupervisorEmployeeId($current_user->getId());

        $leaves = [];

        if($lrlf->getRecordCount() >0){

            foreach($lrlf->rs as $lrf_obj) {
                $lrlf->data = (array)$lrf_obj;
                $lrf_obj = $lrlf;

                $methord = $lrf_obj->getOptions('leave_method');
                $user_id = $lrf_obj->getUser();

                $ulf = new UserListFactory();
		        $user_obj = $ulf->getById($user_id)->getCurrent();


                $leaves [] = array(
                    'id' => $lrf_obj->getId(),
                    'user' => $user_obj->getFullName(),
                    'user_id' => $user_id,
                    'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
                    'leave_method' => $methord[$lrf_obj->getLeaveMethod()],
                    'start_date' => $lrf_obj->getLeaveFrom(),
                    'end_date' => $lrf_obj->getLeaveTo(),
                    'amount' => $lrf_obj->getAmount(),
                    'is_supervisor_approved' => $lrf_obj->getSupervisorApproved()
                );
            }
        }

        $data['msg'] = $msg;

        $viewData['leaves'] = $leaves;
        // dd($viewData);

        return view('leaves/ApprovedSupervisedBy', $viewData);

    }

    //new
    public function submit(Request $request)
    {
        $leaveRequests = $request->input('data.leave_request', []);

        if (empty($leaveRequests)) {
            return redirect()->back()->with('error', 'No leave selected.');
        }

        dd($request->all());

        $lrlf = new LeaveRequestListFactory();
        $msg = "";
        $user_date_id=0;

        foreach ($leaveRequests as $key => $val) {
            $lrlf->getById($key);

            if ($lrlf->getRecordCount() > 0) {
                $lrf = $lrlf->getCurrent();

                $ablf = new AccrualBalanceListFactory();
                $ablf->getByUserIdAndAccrualPolicyId($lrf->getUser(), $lrf->getAccuralPolicy());

                    if ($ablf->getRecordCount() > 0) {
                        $abf = $ablf->getCurrent();
                        $balance = $abf->getBalance();
                        $amount = $lrf->getAmount();
                        $amount_taken = 0;

                        // Calculate amount_taken based on leave method
                        if($lrf->getLeaveMethod() == 1){
                            $amount_taken = (($amount*8) * (28800/8));
                        }
                        elseif($lrf->getLeaveMethod()  == 2){

                            if($amount<1){
                                $amount_taken = (($amount*8) * (28800/8));
                            }
                            else{
                                $amount_taken = (($amount*8) * (28800/8));
                            }
                        }
                        elseif($lrf->getLeaveMethod()  == 3){

                            $start_date_stamp= TTDate::parseTimeUnit($lrf->getLeaveTime() );
                            $end_date_stamp= TTDate::parseTimeUnit($lrf->getLeaveEndTime()  );

                            $time_diff = $end_date_stamp - $start_date_stamp;

                            if($time_diff <3600){
                                $time_diff = 3600;
                            }


                            if($time_diff >7200){
                                $time_diff = 7200;
                            }

                            $amount_taken =$time_diff*0.8;
                        }

                        $amount_taken = -1 * abs($amount_taken);
                        $current_balance = $balance -  abs($amount_taken);
                        $abf->setBalance($current_balance);

                        $leaves =$lrf->getLeaveDates();
                        $date_array = explode(',', $leaves);

                        foreach ($date_array as $date) {

                            $af = new AccrualFactory();

                            $af->setAccrualPolicyID($lrf->getAccuralPolicy());
                            $af->setUser($lrf->getUser());
                            $af->setType(55);

                            $datestamp = new DateTime(trim($date) );
                            $timestamp = $datestamp->getTimestamp();
                            $af->setTimeStamp($timestamp);

                            if($lrf->getLeaveMethod() ==1){
                                $af->setAmount(-28800);
                                $amount_taken=-28800;
                            }else{
                                $af->setAmount($amount_taken);
                            }

                            $af->setLeaveRequestId($lrf->getId());
                            $af->setEnableCalcBalance(true);
                            if ($af->isValid()) {

                                $result = $af->save();

                                    if($lrf->getAccuralPolicy() == 9){

                                        $udlf_a = new UserDateListFactory();
                                        $udlf_a->getByUserIdAndDate($lrf->getUser(), $datestamp->format('Y-m-d'));

                                        if( $udlf_a->getRecordCount() > 0){

                                            $user_date_id_a = $udlf_a->getCurrent()->getId();

                                            if(isset($user_date_id_a) && $user_date_id_a >0){
                                                $ulf_a = new UserListFactory();
                                                $ulf_a->getById($user_id);

                                                $user_a = $ulf_a->getCurrent();

                                                $udtlf_obj = new UserDateTotalListFactory();
                                                $udtlf_obj->getByUserDateIdAndStatusAndType($user_date_id,30,10);

                                                if($udtlf_obj->getRecordCount() > 0){
                                                    $udt_old_obj = $udtlf_obj->getCurrent();
                                                    $udt_old_obj->setDeleted(1);
                                                    $udt_old_obj->save();

                                                }

                                                $udt_obj_total = new UserDateTotalFactory();


                                                $udt_obj_total->setUserDateID($user_date_id_a);
                                                $udt_obj_total->setTotalTime(-1*$amount_taken);
                                                $udt_obj_total->setStatus(10);
                                                $udt_obj_total->setType(10);
                                                //$udt_obj_total->setAbsencePolicyID(11);

                                                $udt_obj_total->setBranch($user_a->getDefaultBranch());
                                                $udt_obj_total->setDepartment($user_a->getDefaultDepartment());
                                                $udt_obj_total->setActualTotalTime($amount_taken);

                                                if ( $udt_obj_total->isValid() ){
                                                    $udt_obj_total->Save();

                                                    //  $af->setUserDateTotalID();
                                                }

                                                $udt_obj = new UserDateTotalFactory();

                                                $udt_obj->setUserDateID($user_date_id_a);
                                                $udt_obj->setTotalTime(-1*$amount_taken);
                                                $udt_obj->setStatus(30);
                                                $udt_obj->setType(10);
                                                $udt_obj->setAbsencePolicyID(11);
                                                $udt_obj->setOverride(TRUE);
                                                $udt_obj->setBranch($user_a->getDefaultBranch());
                                                $udt_obj->setDepartment($user_a->getDefaultDepartment());
                                                $udt_obj->setActualTotalTime($amount_taken);

                                                if ( $udt_obj->isValid() ){

                                                    $udt_obj->Save();

                                                    //  $af->setUserDateTotalID();
                                                }
                                            }
                                        }
                                    }
                            }
                        }

                        if($result){
                            // save accrual balance
                            // $abf->save();

                            // $leave_request_id =  $lrf->getId();
                            $leave_amount =   $lrf->getAmount();
                            $leave_methord =   $lrf->getLeaveMethod();
                            $leave_type =   $lrf->getAccuralPolicy();
                            $user_id = $lrf->getUser();
                            $from_date = $lrf->getLeaveFrom();
                            $to_date = $lrf->getLeaveTo();
                            $sueprvisor_id =$lrf->getSupervisorId();

                            // Approve leave
                            $lrf->setCoveredApproved(1);
                            $lrf->setSupervisorApproved(1);
                            $lrf->save();

                            Log::info("Saved leave ID {$key} as approved.");

                            if(($leave_methord == 1 && $leave_type == 12) || $leave_methord == 2 || $leave_methord == 3 ){

                                // Update UserDateTotal
                                $udlf = new UserDateListFactory();
                                $udlf->getByUserIdAndDate($user_id, $from_date);

                                if ($udlf->getRecordCount() == 1) {
                                    $user_date_id = $udlf->getCurrent()->getId();

                                if(isset($user_date_id) && $user_date_id > 0 ){
                                    $ulf = new UserListFactory();
                                    $ulf->getById($user_id);
                                    $user = $ulf->getCurrent();

                                    $udtlf = new UserDateTotalListFactory();
                                    $udtlf->getByUserDateId($user_date_id);

                                    // Delete existing records
                                    $udtlf_obj = new UserDateTotalListFactory();
                                    $udtlf_obj->getByUserDateIdAndStatusAndType($user_date_id, 30, 10);

                                    if ($udtlf_obj->getRecordCount() > 0) {
                                        $udt_old_obj = $udtlf_obj->getCurrent();
                                        $udt_old_obj->setDeleted(1);
                                        $udt_old_obj->save();
                                    }

                                    // Save new UserDateTotal
                                    $udt_obj = new UserDateTotalFactory();

                                    if($leave_methord == 1 && $leave_type = 9){
                                        $amount_taken = $amount_taken * -1;
                                        $leave_type =11;
                                        $udt_obj->setOverride(TRUE);
                                    }

                                    if($leave_methord == 1 && $leave_type = 12){
                                        $amount_taken = $amount_taken * -1;
                                        $leave_type =9;
                                        $udt_obj->setOverride(TRUE);
                                    }

                                    $udt_obj->setUserDateID($user_date_id);
                                    $udt_obj->setTotalTime($amount_taken);
                                    $udt_obj->setStatus(30);
                                    $udt_obj->setType(10);
                                    $udt_obj->setAbsencePolicyID($lrf->getAccuralPolicy());
                                    $udt_obj->setBranch($user->getDefaultBranch());
                                    $udt_obj->setDepartment($user->getDefaultDepartment());
                                    $udt_obj->setActualTotalTime($amount_taken);
                                    if ($udt_obj->isValid()) {
                                        $udt_obj->Save();
                                    }


                                    if($udtlf->getRecordCount() > 0){

                                        $udtlf->StartTransaction();
                                        $udt_data_old = [];

                                        foreach ($udtlf->rs as $udt_obj) {
                                            $udtlf->data = (array)$udt_obj;
                                            $udt_obj = $udtlf;

                                            // $udt_obj->setDeleted(TRUE);

                                            if ( $udt_obj->isValid() ) {


                                                $udt_data_old[] = array(
                                                    'id' => $udt_obj->getId(),
                                                    'user_date_id' => $udt_obj->getUserDateId(),
                                                    'date_stamp' => $udt_obj->getUserDateObject()->getDateStamp(),
                                                    'user_id' => $udt_obj->getUserDateObject()->getUser(),
                                                    'user_full_name' => $udt_obj->getUserDateObject()->getUserObject()->getFullName(),
                                                    'status_id' => $udt_obj->getStatus(),
                                                    'type_id' => $udt_obj->getType(),
                                                    'total_time' => $udt_obj->getTotalTime(),
                                                    'absence_policy_id' => $udt_obj->getAbsencePolicyID(),
                                                    'absence_leave_id' => $absence_leave_id,
                                                    'branch_id' => $udt_obj->getBranch(),
                                                    'department_id' => $udt_obj->getDepartment(),
                                                    'job_id' => $udt_obj->getJob(),
                                                    'job_item_id' => $udt_obj->getJobItem(),
                                                    'override' => $udt_obj->getOverride(),
                                                    'created_date' => $udt_obj->getCreatedDate(),
                                                    'created_by' => $udt_obj->getCreatedBy(),
                                                    'updated_date' => $udt_obj->getUpdatedDate(),
                                                    'updated_by' => $udt_obj->getUpdatedBy(),
                                                    'deleted_date' => $udt_obj->getDeletedDate(),
                                                    'deleted_by' => $udt_obj->getDeletedBy()
                                                );
                                                            //  $udt_obj->Save();
                                            }

                                        } // end foreach

                                        $pclf_o = new PunchControlListFactory();
                                        $pclf_o->getByUserDateId($user_date_id);

                                        if($pclf_o->getRecordCount() > 0){

                                            $pcf_o = $pclf_o->getCurrent();
                                            $plf = new PunchListFactory();
                                            $plf->getByPunchControlIdAndStatusId($pcf_o->getId(), 20);

                                            if($plf->getRecordCount() >0){

                                                $pf = $plf->getCurrent();
                                                //$pf = new PunchFactory();
                                                $out_time =$pf->getTimeStamp();

                                                $out_date_time = new DateTime();
                                                $out_date_time->setTimestamp($out_time);
                                                $out_date = $out_date_time->format('Y-m-d');
                                                $office_out_time_string = $out_date.' 16:45:00';

                                                $office_out_time = DateTime::createFromFormat('Y-m-d H:i:s', $office_out_time_string);

                                                $dateDiff  = $office_out_time->diff($out_date_time);
                                                $over_time_minutes = intval($dateDiff->format("%R%I"));
                                                $hours = $dateDiff->format("%R%H");

                                                $group_id =$ulf->getCurrent()->getGroup();
                                                $ulf->getCurrent()->getPolicyGroup();

                                                $over_time_total = $dateDiff->format("%R%H")*3600 + $over_time_minutes*60 ;

                                                if($over_time_total>0){
                                                    if ($group_id == 3) {

                                                        $udtlf_obj = new UserDateTotalListFactory();
                                                        $udtlf_obj->getByUserDateIdAndStatusAndType($user_date_id,10,40);

                                                        if($udtlf_obj->getRecordCount() > 0){
                                                            $udt_old_obj = $udtlf_obj->getCurrent();
                                                            $udt_old_obj->setDeleted(1);
                                                            $udt_old_obj->save();

                                                        }

                                                        $pre_policy = new PremiumPolicyListFactory();
                                                        $pre_policy->getByPolicyGroupUserId($user_id);
                                                        $ppf =  $pre_policy->getCurrent();
                                                        $premium_policy_id = $ppf->getId();


                                                        $udt_obj = new UserDateTotalFactory();
                                                        $udt_obj->setUserDateID($user_date_id);
                                                        $udt_obj->setTotalTime($over_time_total);
                                                        $udt_obj->setStatus(10);
                                                        $udt_obj->setType(40);
                                                        $udt_obj->setPremiumPolicyID($premium_policy_id);
                                                        $udt_obj->setBranch($user->getDefaultBranch());
                                                        $udt_obj->setDepartment($user->getDefaultDepartment());
                                                        $udt_obj->setActualTotalTime($over_time_total);

                                                        if ( $udt_obj->isValid() ){

                                                            $udt_obj->Save();
                                                        }
                                                    } elseif ($group_id == 4 || $group_id == 6) {
                                                        $oplf = new OverTimePolicyListFactory();
                                                        $oplf->getByPolicyGroupUserId($user_id);
                                                        $opf =  $oplf->getCurrent();
                                                        $overtime_policy_id = $opf->getId();

                                                        $udt_obj = new UserDateTotalFactory();

                                                        $udt_obj->setUserDateID($user_date_id);
                                                        $udt_obj->setTotalTime($over_time_total);
                                                        $udt_obj->setStatus(10);
                                                        $udt_obj->setType(30);
                                                        $udt_obj->setOverTimePolicyID($overtime_policy_id);
                                                        $udt_obj->setBranch($user->getDefaultBranch());
                                                        $udt_obj->setDepartment($user->getDefaultDepartment());
                                                        $udt_obj->setActualTotalTime($over_time_total);

                                                        if ( $udt_obj->isValid() ){

                                                            $udt_obj->Save();
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        foreach ($udt_data_old as $user_data){
                                            $udtlf = new UserDateTotalFactory();
                                        }

                                        $udtlf->CommitTransaction();

                                    }
                                }

                                }
                            }

                                $supervisors = new UserListFactory();
                                $supervisors->getById(trim($sueprvisor_id));
                                $supervisor_obj = $supervisors->getCurrent();

                                $employeeLF = new UserListFactory();
                                $employeeLF->getById(trim($user_id));
                                $employee_obj = $employeeLF->getCurrent();

                                $aplf = new AccrualPolicyListFactory();
                                $aplf->getById($leave_type);


                            } else {
                                $msg = "Invalid accruals or you don't have permission";
                            }

                        } else {
                            $msg = "Leave record not found.";
                        }
                    }
        }

        return redirect()->back()->with('success', 'Selected leaves have been submitted.');
    }

// only supervisor approved not calculation
// public function submit(Request $request)
// {
//     $leaveRequests = $request->input('data.leave_request', []);

//     if (empty($leaveRequests)) {
//         return redirect()->back()->with('error', 'No leave selected.');
//     }

//     foreach ($leaveRequests as $key => $val) {
//         $lrlf = new LeaveRequestListFactory();
//         $lrlf->getById($key);

//         if ($lrlf->getRecordCount() > 0) {
//             $lrf = $lrlf->getCurrent();

//             // Only update supervisor approval
//             $lrf->setSupervisorApproved(1);

//             if ($lrf->isValid()) {
//                 $lrf->save();
//                 Log::info("Leave ID {$key} approved by supervisor.");
//             }
//         } else {
//             Log::warning("Leave request ID {$key} not found.");
//         }
//     }

//     return redirect()->back()->with('success', 'Selected leaves have been approved by supervisor.');
// }



    // public function submit(Request $request)
    // {
    //     $leaveRequests = $request->input('data.leave_request', []);

    //     if (empty($leaveRequests)) {
    //         return redirect()->back()->with('error', 'No leave selected.');
    //     }

    //     $lrlf = new LeaveRequestListFactory();

    //       /*  foreach ($data['leave_request'] as $key => $val){


    //             $lrlf->getById($key);

    //             if($lrlf->getRecordCount() >0){
    //                 $lrf = $lrlf->getCurrent();
    //                 $lrf->setCoveredApproved(1);
    //                 $lrf->setSupervisorApproved(1);
    //                 $lrf->save();

    //             }
    //         }*/
    //         $user_date_id=0;

    //         foreach ( $leaveRequests as $key => $val){

    //             $lrlf->getById($key);

    //             if($lrlf->getRecordCount() >0){

    //                 $lrf = $lrlf->getCurrent();

    //                 // echo $lrf->getUser();
    //                 // print_r($lrf);

    //                 $ablf = new AccrualBalanceListFactory();
    //                 $ablf->getByUserIdAndAccrualPolicyId($lrf->getUser(),$lrf->getAccuralPolicy());

    //                 //  echo $ablf->getRecordCount();

    //                 // print_r($ablf);


    //                 if( $ablf->getRecordCount() > 0){

    //                         $abf = $ablf->getCurrent();
    //                         $balance = $abf->getBalance();
    //                         $amount = $lrf->getAmount();
    //                         $amount_taken =0;

    //                         if($lrf->getLeaveMethod() == 1){
    //                             $amount_taken = (($amount*8) * (28800/8));
    //                         }
    //                         elseif($lrf->getLeaveMethod()  == 2){

    //                             if($amount<1){
    //                                 $amount_taken = (($amount*8) * (28800/8));
    //                             }
    //                             else{
    //                                 $amount_taken = (($amount*8) * (28800/8));
    //                             }
    //                         }
    //                         elseif($lrf->getLeaveMethod()  == 3){

    //                             $start_date_stamp= TTDate::parseTimeUnit($lrf->getLeaveTime() );
    //                             $end_date_stamp= TTDate::parseTimeUnit($lrf->getLeaveEndTime()  );

    //                             $time_diff = $end_date_stamp - $start_date_stamp;

    //                             if($time_diff <3600){
    //                                 $time_diff = 3600;
    //                             }


    //                             if($time_diff >7200){
    //                                 $time_diff = 7200;
    //                             }

    //                             $amount_taken =$time_diff*0.8;
    //                         }



    //                         $amount_taken = -1 * abs($amount_taken);

    //                         $current_balance = $balance -  abs($amount_taken);
    //                         $abf->setBalance($current_balance);

    //                         $leaves =$lrf->getLeaveDates();

    //                         $date_array = explode(',', $leaves);
    //                         // $abf->save();
    //                         foreach($date_array as $date){

    //                                 $af = new AccrualFactory();

    //                                 $af->setAccrualPolicyID($lrf->getAccuralPolicy());
    //                                 $af->setUser($lrf->getUser());
    //                                 $af->setType(55);


    //                                 $datestamp = new DateTime(trim($date) );

    //                                 $timestamp = $datestamp->getTimestamp();
    //                                 $af->setTimeStamp($timestamp);

    //                                 if($lrf->getLeaveMethod() ==1){
    //                                     $af->setAmount(-28800);
    //                                     $amount_taken=-28800;
    //                                 }else{
    //                                     $af->setAmount($amount_taken);
    //                                 }


    //                                 $af->setLeaveRequestId($lrf->getId());


    //                                 $af->setEnableCalcBalance(TRUE);

    //                                 if ( $af->isValid() ) {


    //                                         //save accurals
    //                                     $result =   $af->save();


    //                                         if($lrf->getAccuralPolicy() == 9){

    //                                             $udlf_a = new UserDateListFactory();
    //                                             $udlf_a->getByUserIdAndDate($lrf->getUser(), $datestamp->format('Y-m-d'));

    //                                             if( $udlf_a->getRecordCount() > 0){

    //                                                 $user_date_id_a = $udlf_a->getCurrent()->getId();

    //                                                 if(isset($user_date_id_a) && $user_date_id_a >0){
    //                                                     $ulf_a = new UserListFactory();
    //                                                     $ulf_a->getById($user_id);

    //                                                     $user_a = $ulf_a->getCurrent();

    //                                                     $udtlf_obj = new UserDateTotalListFactory();
    //                                                     $udtlf_obj->getByUserDateIdAndStatusAndType($user_date_id,30,10);

    //                                                     if($udtlf_obj->getRecordCount() > 0){
    //                                                         $udt_old_obj = $udtlf_obj->getCurrent();
    //                                                         $udt_old_obj->setDeleted(1);
    //                                                         $udt_old_obj->save();

    //                                                     }

    //                                                     $udt_obj_total = new UserDateTotalFactory();


    //                                                     $udt_obj_total->setUserDateID($user_date_id_a);
    //                                                     $udt_obj_total->setTotalTime(-1*$amount_taken);
    //                                                     $udt_obj_total->setStatus(10);
    //                                                     $udt_obj_total->setType(10);
    //                                                     //$udt_obj_total->setAbsencePolicyID(11);

    //                                                     $udt_obj_total->setBranch($user_a->getDefaultBranch());
    //                                                     $udt_obj_total->setDepartment($user_a->getDefaultDepartment());
    //                                                     $udt_obj_total->setActualTotalTime($amount_taken);

    //                                                     if ( $udt_obj_total->isValid() ){
    //                                                         $udt_obj_total->Save();

    //                                                         //  $af->setUserDateTotalID();
    //                                                     }


    //                                                     $udt_obj = new UserDateTotalFactory();

    //                                                     $udt_obj->setUserDateID($user_date_id_a);
    //                                                     $udt_obj->setTotalTime(-1*$amount_taken);
    //                                                     $udt_obj->setStatus(30);
    //                                                     $udt_obj->setType(10);
    //                                                     $udt_obj->setAbsencePolicyID(11);
    //                                                     $udt_obj->setOverride(TRUE);
    //                                                     $udt_obj->setBranch($user_a->getDefaultBranch());
    //                                                     $udt_obj->setDepartment($user_a->getDefaultDepartment());
    //                                                     $udt_obj->setActualTotalTime($amount_taken);

    //                                                     if ( $udt_obj->isValid() ){

    //                                                         $udt_obj->Save();

    //                                                         //  $af->setUserDateTotalID();
    //                                                     }
    //                                                 }

    //                                             }
    //                                         }
    //                                 }
    //                         }

    //                         if($result){


    //                             // save accrual balance
    //                             // $abf->save();

    //                             // $leave_request_id =  $lrf->getId();
    //                             $leave_amount =   $lrf->getAmount();
    //                             $leave_methord =   $lrf->getLeaveMethod();
    //                             $leave_type =   $lrf->getAccuralPolicy();
    //                             $user_id = $lrf->getUser();
    //                             $from_date = $lrf->getLeaveFrom();
    //                             $to_date = $lrf->getLeaveTo();
    //                             $sueprvisor_id =$lrf->getSupervisorId();

    //                             $lrf->setCoveredApproved(1);
    //                             $lrf->setSupervisorApproved(1);
    //                             //  $lrf->setHrApproved(1);
    //                             $lrf->save();

    //                             if(($leave_methord == 1 && $leave_type == 12) || $leave_methord == 2 || $leave_methord == 3 ){

    //                                 // echo $leave_type;exit;

    //                                 $udlf = new UserDateListFactory();
    //                                 $udlf->getByUserIdAndDate($user_id, $from_date);

    //                                 if ( $udlf->getRecordCount() == 1 ) {
    //                                     $user_date_id = $udlf->getCurrent()->getId();

    //                                 if(isset($user_date_id) && $user_date_id > 0 ){

    //                                     $ulf = new UserListFactory();
    //                                     $ulf->getById($user_id);

    //                                     $user = $ulf->getCurrent();

    //                                     $udtlf = new UserDateTotalListFactory();
    //                                     $udtlf->getByUserDateId($user_date_id);

    //                                     $udtlf_obj = new UserDateTotalListFactory();
    //                                     $udtlf_obj->getByUserDateIdAndStatusAndType($user_date_id,30,10);

    //                                     if($udtlf_obj->getRecordCount() > 0){
    //                                         $udt_old_obj = $udtlf_obj->getCurrent();
    //                                         $udt_old_obj->setDeleted(1);
    //                                         $udt_old_obj->save();

    //                                     }

    //                                     $udt_obj = new UserDateTotalFactory();

    //                                     if($leave_methord == 1 && $leave_type = 9){
    //                                         $amount_taken = $amount_taken * -1;
    //                                         $leave_type =11;
    //                                         $udt_obj->setOverride(TRUE);
    //                                     }

    //                                     if($leave_methord == 1 && $leave_type = 12){
    //                                         $amount_taken = $amount_taken * -1;
    //                                         $leave_type =9;
    //                                         $udt_obj->setOverride(TRUE);
    //                                     }


    //                                     $udt_obj->setUserDateID($user_date_id);
    //                                     $udt_obj->setTotalTime($amount_taken);
    //                                     $udt_obj->setStatus(30);
    //                                     $udt_obj->setType(10);
    //                                     $udt_obj->setAbsencePolicyID($leave_type);
    //                                     $udt_obj->setBranch($user->getDefaultBranch());
    //                                     $udt_obj->setDepartment($user->getDefaultDepartment());
    //                                     $udt_obj->setActualTotalTime($amount_taken);

    //                                     if ( $udt_obj->isValid() ){
    //                                         $udt_obj->Save();

    //                                         //  $af->setUserDateTotalID();
    //                                     }

    //                                     if($udtlf->getRecordCount() > 0)
    //                                     {

    //                                         $udtlf->StartTransaction();
    //                                         $udt_data_old = array();
    //                                         foreach ($udtlf->rs as $udt_obj) {
    //                                             $udtlf->data = (array)$udt_obj;
    //                                             $udt_obj = $udtlf;

    //                                             // $udt_obj->setDeleted(TRUE);

    //                                             if ( $udt_obj->isValid() ) {


    //                                                 $udt_data_old[] = array(
    //                                                     'id' => $udt_obj->getId(),
    //                                                     'user_date_id' => $udt_obj->getUserDateId(),
    //                                                     'date_stamp' => $udt_obj->getUserDateObject()->getDateStamp(),
    //                                                     'user_id' => $udt_obj->getUserDateObject()->getUser(),
    //                                                     'user_full_name' => $udt_obj->getUserDateObject()->getUserObject()->getFullName(),
    //                                                     'status_id' => $udt_obj->getStatus(),
    //                                                     'type_id' => $udt_obj->getType(),
    //                                                     'total_time' => $udt_obj->getTotalTime(),
    //                                                     'absence_policy_id' => $udt_obj->getAbsencePolicyID(),
    //                                                     'absence_leave_id' => $absence_leave_id,
    //                                                     'branch_id' => $udt_obj->getBranch(),
    //                                                     'department_id' => $udt_obj->getDepartment(),
    //                                                     'job_id' => $udt_obj->getJob(),
    //                                                     'job_item_id' => $udt_obj->getJobItem(),
    //                                                     'override' => $udt_obj->getOverride(),
    //                                                     'created_date' => $udt_obj->getCreatedDate(),
    //                                                     'created_by' => $udt_obj->getCreatedBy(),
    //                                                     'updated_date' => $udt_obj->getUpdatedDate(),
    //                                                     'updated_by' => $udt_obj->getUpdatedBy(),
    //                                                     'deleted_date' => $udt_obj->getDeletedDate(),
    //                                                     'deleted_by' => $udt_obj->getDeletedBy()
    //                                                 );
    //                                                             //  $udt_obj->Save();
    //                                             }

    //                                         } // end foreach

    //                                         $pclf_o = new PunchControlListFactory();
    //                                         $pclf_o->getByUserDateId($user_date_id);
    //                                         if($pclf_o->getRecordCount() > 0){


    //                                             $pcf_o = $pclf_o->getCurrent();

    //                                             $plf = new PunchListFactory();
    //                                             $plf->getByPunchControlIdAndStatusId($pcf_o->getId(), 20);


    //                                             if($plf->getRecordCount() >0){

    //                                                 $pf = $plf->getCurrent();
    //                                                 //$pf = new PunchFactory();
    //                                                 $out_time =$pf->getTimeStamp();

    //                                                 $out_date_time = new DateTime();
    //                                                 $out_date_time->setTimestamp($out_time);
    //                                                 $out_date = $out_date_time->format('Y-m-d');
    //                                                 $office_out_time_string = $out_date.' 16:45:00';

    //                                                 $office_out_time = DateTime::createFromFormat('Y-m-d H:i:s', $office_out_time_string);

    //                                                 $dateDiff  = $office_out_time->diff($out_date_time);
    //                                                 $over_time_minutes = intval($dateDiff->format("%R%I"));
    //                                                 $hours = $dateDiff->format("%R%H");

    //                                                 $group_id =$ulf->getCurrent()->getGroup();
    //                                                 $ulf->getCurrent()->getPolicyGroup();


    //                                                 /*



    //                                                 if($hours < 1 && $hours > -1){

    //                                                         if(0 < $over_time_minutes and $over_time_minutes< 30){
    //                                                                     $ot_min = 0;
    //                                                         }elseif(30<= $over_time_minutes and $over_time_minutes< 45){
    //                                                             $ot_min = 30;
    //                                                         }elseif(45 <= $over_time_minutes and $over_time_minutes<= 59){
    //                                                             $ot_min = 45;
    //                                                         }


    //                                                         if($group_id==3){
    //                                                             $ot_min = 0;
    //                                                         }

    //                                                 }
    //                                                 else{

    //                                                         if(0 < $over_time_minutes and $over_time_minutes< 15){
    //                                                                     $ot_min = 0;
    //                                                         }elseif(15 <= $over_time_minutes and $over_time_minutes< 30){
    //                                                             $ot_min = 15;
    //                                                         }elseif(30<= $over_time_minutes and $over_time_minutes< 45){
    //                                                             $ot_min = 30;
    //                                                         }elseif(45 <= $over_time_minutes and $over_time_minutes<= 59){
    //                                                             $ot_min = 45;
    //                                                             // echo 'boom';
    //                                                         }

    //                                                     } // end of if
    //                                                     */

    //                                                     $over_time_total = $dateDiff->format("%R%H")*3600 + $over_time_minutes*60 ;

    //                                                     if($over_time_total>0){

    //                                                         /*
    //                                                         if($over_time_total >7200 && $group_id !=6){
    //                                                             $over_time_total=7200;
    //                                                         }
    //                                                         */
    //                                                         //  echo '<br>'.$over_time_total;



    //                                                             if($group_id == 3){

    //                                                             //  exit();

    //                                                                 $udtlf_obj = new UserDateTotalListFactory();
    //                                                                 $udtlf_obj->getByUserDateIdAndStatusAndType($user_date_id,10,40);

    //                                                                 if($udtlf_obj->getRecordCount() > 0){
    //                                                                     $udt_old_obj = $udtlf_obj->getCurrent();
    //                                                                     $udt_old_obj->setDeleted(1);
    //                                                                     $udt_old_obj->save();

    //                                                                 }


    //                                                                 $pre_policy = new PremiumPolicyListFactory();
    //                                                                 $pre_policy->getByPolicyGroupUserId($user_id);
    //                                                                 $ppf =  $pre_policy->getCurrent();
    //                                                                 $premium_policy_id = $ppf->getId();


    //                                                                 $udt_obj = new UserDateTotalFactory();
    //                                                                 $udt_obj->setUserDateID($user_date_id);
    //                                                                 $udt_obj->setTotalTime($over_time_total);
    //                                                                 $udt_obj->setStatus(10);
    //                                                                 $udt_obj->setType(40);
    //                                                                 $udt_obj->setPremiumPolicyID($premium_policy_id);
    //                                                                 $udt_obj->setBranch($user->getDefaultBranch());
    //                                                                 $udt_obj->setDepartment($user->getDefaultDepartment());
    //                                                                 $udt_obj->setActualTotalTime($over_time_total);

    //                                                                 if ( $udt_obj->isValid() ){

    //                                                                     $udt_obj->Save();
    //                                                                 }


    //                                                             }
    //                                                             elseif($group_id == 4 || $group_id == 6) {

    //                                                                 $oplf = new OverTimePolicyListFactory();
    //                                                                 $oplf->getByPolicyGroupUserId($user_id);
    //                                                                 $opf =  $oplf->getCurrent();
    //                                                                 $overtime_policy_id = $opf->getId();

    //                                                                 $udt_obj = new UserDateTotalFactory();

    //                                                                 $udt_obj->setUserDateID($user_date_id);
    //                                                                 $udt_obj->setTotalTime($over_time_total);
    //                                                                 $udt_obj->setStatus(10);
    //                                                                 $udt_obj->setType(30);
    //                                                                 $udt_obj->setOverTimePolicyID($overtime_policy_id);
    //                                                                 $udt_obj->setBranch($user->getDefaultBranch());
    //                                                                 $udt_obj->setDepartment($user->getDefaultDepartment());
    //                                                                 $udt_obj->setActualTotalTime($over_time_total);

    //                                                                 if ( $udt_obj->isValid() ){

    //                                                                     $udt_obj->Save();
    //                                                                 }

    //                                                             }

    //                                                     }
    //                                             }


    //                                         }

    //                                         foreach ($udt_data_old as $user_data){

    //                                             $udtlf = new UserDateTotalFactory();

    //                                         }

    //                                         $udtlf->CommitTransaction();
    //                                     }
    //                                 } // end

    //                                 }

    //                             }// end of if that leave method check


    //                                 $supervisors = new UserListFactory();
    //                                 $supervisors->getById(trim($sueprvisor_id));
    //                                 $supervisor_obj = $supervisors->getCurrent();


    //                                 $employeeLF = new UserListFactory();
    //                                 $employeeLF->getById(trim($user_id));
    //                                 $employee_obj = $employeeLF->getCurrent();


    //                                 // if ( $supervisor_obj->getWorkEmail() != FALSE ) {
    //                                 //             $supervisor_primary_email = $supervisor_obj->getWorkEmail();
    //                                 //             if ( $supervisor_obj->getHomeEmail() != FALSE ) {
    //                                 //                     $supervisor_secondary_email = $supervisor_obj->getHomeEmail();
    //                                 //             } else {
    //                                 //                     $supervisor_secondary_email = NULL;
    //                                 //             }
    //                                 // } else {
    //                                 //             $supervisor_primary_email = $supervisor_obj->getHomeEmail();
    //                                 //             $supervisor_secondary_email = NULL;
    //                                 // }


    //                                 // if ( $employee_obj->getWorkEmail() != FALSE ) {
    //                                 //             $employee_primary_email = $employee_obj->getWorkEmail();
    //                                 //             if ( $employee_obj->getHomeEmail() != FALSE ) {
    //                                 //                     $employee_secondary_email = $employee_obj->getHomeEmail();
    //                                 //             } else {
    //                                 //                     $employee_secondary_email = NULL;
    //                                 //             }
    //                                 // } else {
    //                                 //             $employee_primary_email = $employee_obj->getHomeEmail();
    //                                 //             $employee_secondary_email = NULL;
    //                                 // }

    //                                 //echo $user_id.'hko'. $employee_primary_email;exit;
    //                         $aplf = new AccrualPolicyListFactory();
    //                         $aplf->getById($leave_type);

    //                     //****************************************************************


    //                     // // Create the mail transport configuration
    //                     // $transport = Swift_MailTransport::newInstance();
    //                     // $transporter = Swift_SmtpTransport::newInstance($config_vars['mail']['smtp_host'], $config_vars['mail']['smtp_port'], 'ssl')
    //                     // ->setUsername($config_vars['mail']['smtp_username'])
    //                     // ->setPassword($config_vars['mail']['smtp_password']);



    //                     // // Create the message
    //                     // $message = Swift_Message::newInstance();
    //                     // $message->setTo(array(
    //                     //   $employee_primary_email=> $employee_obj->getFullName()
    //                     //   ));
    //                     // $message->setSubject("Your leave approved");
    //                     // $htmlContent .= '<p>Dear '.$employee_obj->getFullName().'</p>';
    //                     // $htmlContent .= '<div style="background: rgb(55,110,55); padding-bottom: 0.1px; padding-top: 0.1px;" align="center"><h2 style="color: #fff">Your leave has been approved.</h2></div>';
    //                     // $htmlContent .= '<br><br><table><tr><td>Emp No </td>'. "<td>".$employee_obj->getEmployeeNumber()."</td></tr>";
    //                     // $htmlContent .= '<tr><td>Name </td>'. "<td>".$employee_obj->getFirstName().' '.$employee_obj->getLastName(). "</td></tr>";
    //                     // $htmlContent .= '<tr><td>Leave Type </td>'. "<td>".  $aplf->getCurrent()->getName()."</td></tr>";
    //                     // $htmlContent .= '<tr><td>No. of days </td>'. "<td>".$leave_amount."</td></tr>";
    //                     // $htmlContent .= '<tr><td>From </td>'. "<td>".$from_date."</td></tr>";
    //                     // $htmlContent .= '<tr><td>To </td>'. "<td>".$to_date."</td></tr></table>";
    //                     // $htmlContent .= '<p><b><i><span style="font-family: Helvetica,sans-serif; color:#440062">HR Department</span></i></b></p>';
    //                     // $message->setBody($htmlContent,'text/html');
    //                     // $message->setFrom("careers@aquafresh.lk", "HRM");

    //                     // // Send the email
    //                     // $mailer = Swift_Mailer::newInstance($transporter);
    //                     // $mailer->send($message);


    //                         }
    //                         else{
    //                             $msg = "Invalied accurals or you don't have permission";
    //                         }
    //                 }else{

    //                     $msg = "you don't have assign leave";

    //                 }


    //             }
    //         }

    //     return redirect()->back()->with('success', 'Selected leaves have been Submited.');
    // }



    // public function rejected(Request $request)
    // {

    //     $leaveRequests = $request->input('data.leave_request', []);

    //     if (empty($leaveRequests)) {
    //         return redirect()->back()->with('error', 'No leave requests selected.');
    //     }

    //     $lrlf = new LeaveRequestListFactory();

    //     foreach ($_POST['data']['leave_request'] as $key => $val){

    //         $lrlf->getById($key);

    //         if($lrlf->getRecordCount() >0){
    //             $lrf = $lrlf->getCurrent();

    //             $leave_amount =   $lrf->getAmount();
    //             $leave_methord =   $lrf->getLeaveMethod();
    //             $leave_type =   $lrf->getAccuralPolicy();
    //             $user_id = $lrf->getUser();
    //             $from_date = $lrf->getLeaveFrom();
    //             $to_date = $lrf->getLeaveTo();
    //             $sueprvisor_id =$lrf->getSupervisorId();


    //             $lrf->setStatus(30);
    //             $lrf->save();

    //             $supervisors = new UserListFactory();
    //             $supervisors->getById(trim($sueprvisor_id));
    //             $supervisor_obj = $supervisors->getCurrent();


    //             $employeeLF = new UserListFactory();
    //             $employeeLF->getById(trim($user_id));
    //             $employee_obj = $employeeLF->getCurrent();


    //             if ( $supervisor_obj->getWorkEmail() != FALSE ) {
    //                 $supervisor_primary_email = $supervisor_obj->getWorkEmail();
    //                 if ( $supervisor_obj->getHomeEmail() != FALSE ) {
    //                         $supervisor_secondary_email = $supervisor_obj->getHomeEmail();
    //                 } else {
    //                         $supervisor_secondary_email = NULL;
    //                 }
    //             } else {
    //                         $supervisor_primary_email = $supervisor_obj->getHomeEmail();
    //                         $supervisor_secondary_email = NULL;
    //             }


    //             if ( $employee_obj->getWorkEmail() != FALSE ) {
    //                         $employee_primary_email = $employee_obj->getWorkEmail();
    //                         if ( $employee_obj->getHomeEmail() != FALSE ) {
    //                                 $employee_secondary_email = $employee_obj->getHomeEmail();
    //                         } else {
    //                                 $employee_secondary_email = NULL;
    //                         }
    //             } else {
    //                         $employee_primary_email = $employee_obj->getHomeEmail();
    //                         $employee_secondary_email = NULL;
    //             }


    //             $aplf = new AccrualPolicyListFactory();
    //             $aplf->getById($leave_type);

    //             //****************************************************************


    //             // Create the mail transport configuration
    //             // $transport = Swift_MailTransport::newInstance();
    //             // $transporter = Swift_SmtpTransport::newInstance($config_vars['mail']['smtp_host'], $config_vars['mail']['smtp_port'], 'ssl')
    //             // ->setUsername($config_vars['mail']['smtp_username'])
    //             // ->setPassword($config_vars['mail']['smtp_password']);



    //             // // Create the message
    //             // $message = Swift_Message::newInstance();
    //             // $message->setTo(array(
    //             //     $employee_primary_email=> $employee_obj->getFullName()
    //             //     ));
    //             // $message->setSubject("Your leave Rejected");
    //             // $htmlContent .= '<p>Dear '.$employee_obj->getFullName().'</p>';
    //             // $htmlContent .= '<div style="background: rgb(239,101,101); padding-bottom: 0.1px; padding-top: 0.1px;" align="center"><h2 style="color: #fff">Your leave has been Rejected.</h2></div>';
    //             // $htmlContent .= '<br><br><table><tr><td>Emp No </td>'. "<td>".$employee_obj->getEmployeeNumber()."</td></tr>";
    //             // $htmlContent .= '<tr><td>Name </td>'. "<td>".$employee_obj->getFirstName().' '.$employee_obj->getLastName(). "</td></tr>";
    //             // $htmlContent .= '<tr><td>Leave Type </td>'. "<td>".  $aplf->getCurrent()->getName()."</td></tr>";
    //             // $htmlContent .= '<tr><td>No. of days </td>'. "<td>".$leave_amount."</td></tr>";
    //             // $htmlContent .= '<tr><td>From </td>'. "<td>".$from_date."</td></tr>";
    //             // $htmlContent .= '<tr><td>To </td>'. "<td>".$to_date."</td></tr></table>";
    //             // $htmlContent .= '<p><b><i><span style="font-family: Helvetica,sans-serif; color:#440062">HR Department</span></i></b></p>';
    //             // $message->setBody($htmlContent,'text/html');
    //             // $message->setFrom("careers@aquafresh.lk", "HRM");

    //             // // Send the email
    //             // $mailer = Swift_Mailer::newInstance($transporter);
    //             // $mailer->send($message);

    //         }
    //     }
    // }


    public function rejected(Request $request)
    {
        $leaveRequests = $request->input('data.leave_request', []);

        if (empty($leaveRequests)) {
            return redirect()->back()->with('error', 'No leave selected.');
        }

        // dd($request->all());
        $lrlf = new LeaveRequestListFactory();

        foreach ($leaveRequests as $leaveId => $isChecked) {
            $lrlf->getById($leaveId);

            if ($lrlf->getRecordCount() > 0) {
                $lrf = $lrlf->getCurrent();

                $leave_amount =   $lrf->getAmount();
                $leave_methord =   $lrf->getLeaveMethod();
                $leave_type =   $lrf->getAccuralPolicy();
                $user_id = $lrf->getUser();
                $from_date = $lrf->getLeaveFrom();
                $to_date = $lrf->getLeaveTo();
                $sueprvisor_id =$lrf->getSupervisorId();
                // Set leave status to rejected (30)
                $lrf->setStatus(30);
                $lrf->save();

                $supervisors = new UserListFactory();
                $supervisors->getById(trim($sueprvisor_id));
                $supervisor_obj = $supervisors->getCurrent();

                $employeeLF = new UserListFactory();
                $employeeLF->getById(trim($user_id));
                $employee_obj = $employeeLF->getCurrent();

                $aplf = new AccrualPolicyListFactory();
                $aplf->getById($leave_type);
            }
        }

        return redirect()->back()->with('success', 'Selected leaves have been rejected.');
    }




    // public function bulkAction(Request $request)
    // {
    //     $action = $request->input('action'); // 'submit' or 'rejected'
    //     $leaveRequests = $request->input('data.leave_request', []);

    //     if (empty($leaveRequests)) {
    //         return redirect()->back()->with('error', 'No leaves selected.');
    //     }

    //     foreach ($leaveRequests as $leaveId => $isApproved) {

    //         $lrlf = new LeaveRequestListFactory();
    //         $lrlf->getById($leaveId);

    //         if ($lrlf->getRecordCount() > 0) {
    //             $lrf = $lrlf->getCurrent();

    //             if ($action === 'submit') {
    //                 $lrf->setStatus(20); // e.g., Approved
    //             } elseif ($action === 'rejected') {
    //                 $lrf->setStatus(30); // Rejected
    //                 $this->sendRejectionEmail($lrf); // Use Laravel Mail
    //             }

    //             $lrf->save();
    //         }
    //     }

    //     return redirect()->back()->with('success', 'Leave requests processed successfully.');
    // }






}


function returnBetweenDates( $startDate, $endDate ){
    $startStamp = strtotime(  $startDate );
    $endStamp   = strtotime(  $endDate );

    if( $endStamp > $startStamp ){
        while( $endStamp >= $startStamp ){

            $dateArr[] = date( 'Y-m-d', $startStamp );

            $startStamp = strtotime( ' +1 day ', $startStamp );

        }
        return $dateArr;
    }else{
        return $startDate;
    }

}
