<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\Company\CompanyFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\TTDate;
use App\Models\Core\UserDateListFactory;
use App\Models\Core\UserDateTotalFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Holiday\HolidayListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserWageFactory;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\View;

class EmployeeLateProcess extends Controller
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

        /*
        if ( !$permission->Check('user','employee_excel_upload') ) {
            $permission->Redirect( FALSE ); //Redirect
        }
        */
    }

    public function index() {

        $viewData['title'] = 'Employee Late Process';

        extract	(FormVariables::GetVariables(
            array	(
                'action',
                'id',
                'company_data',
                'pay_period_ids'
            ) 
        ) );

        $pplf->getByCompanyIdAndStatusForHrProcess($current_company->getId(), 10);

        $data = array();

        if($pplf->getRecordCount() > 0){
            
            foreach($pplf->rs as $ppf){
                $pplf->data = (array)$ppf;
                $ppf = $pplf;

                $temp = array();
                $temp['id'] = $ppf->getId();
                $temp['start_date'] =TTDate::getDate('Y-m-d', $ppf->getStartDate());
                $temp['end_date'] = TTDate::getDate('Y-m-d',$ppf->getEndDate());
                
                $data[] = $temp;
                
            }
            
        }

        $smarty->assign_by_ref('data', $data);

        $smarty->assign_by_ref('cf', $cf);

        $smarty->display('company/EmployeeLateProcess.tpl');

        return view('accrual/ViewUserAccrualList', $viewData);

    }

    public function submit(){}

    public function process_late(){
        $cf = new CompanyFactory();

        $ulf = new UserListFactory();
        $uf = new UserFactory();
        //ARSP  EDIT --> ADDD NEW CODE FOR SALARY (WAGE)
        $uwf = new UserWageFactory();

        $hlf = new HierarchyListFactory();

        $pplf = new PayPeriodListFactory();

        Debug::Text('Process Late ', __FILE__, __LINE__, __METHOD__,10);
            
        $ulf = new UserListFactory();
        $ulf->getByCompanyId($current_company->getId());
        
        $plf = new PunchListFactory(); 
        $udlf = new UserDateListFactory();
        $pplf = new PayPeriodListFactory();
        
        
        if($ulf->getRecordCount() >0){
            
            $user_count = $ulf->getRecordCount();
            $percentage = 0;
            $current = 0;
            
            foreach ($ulf->rs as $user_obj){
                $ulf->data = (array)$user_obj;
                $user_obj = $ulf;
                
                $late_date_arry = array();
                
                $current++;
                
                foreach($pay_period_ids as $pp_id){
                    
                    $pplf->getById($pp_id);
                    
                    if($pplf->getRecordCount() > 0){
                        
                        $ppf = $pplf->getCurrent();
                        
                        $udlf = new UserDateListFactory();
                        
                        $udlf->getByUserIdAndPayPeriodID($user_obj->getId(), $ppf->getId());
                        
                        if($udlf->getRecordCount() > 0){
                            
                            foreach($udlf->rs as $udf){
                                $udlf->data = (array)$udf;
                                $udf = $udlf;
                                
                                $udf->getId();
                                $plf->getByUserDateIdAndStatusId($udf->getId(), 10);
                                
                                if($plf->getRecordCount() > 0){
                                    
                                    $pf = $plf->getCurrent();
                                    
                                    $checking_date = $pf->getTimeStamp();
                                    
                                    //echo $checking_date;
                                    
                                    $punch_date = new DateTime(); 
                                    $punch_date->setTimestamp($checking_date);
                                    //$punch_date = dateTime::createFromFormat("Y-m-d H:i:s", $checking_date);
                                    $time_punch = $punch_date->format('H:i:s');
                                    $date_punch =  $punch_date->format('Y-m-d');
                                    
                                    $cut_off_time  = DateTime::createFromFormat("Y-m-d H:i:s",  $date_punch.' 08:15:00');
                                    
                                    if($checking_date > $cut_off_time->getTimestamp()){
                                        
                                        $hlf = new HolidayListFactory();
                                        $hlf->getByPolicyGroupUserIdAndDate($user_obj->getId(),$punch_date->format('Y-m-d'));
                                        
                                        if($hlf->getRecordCount()< 1){
                                            
                                                $udlf_step2 = new UserDateListFactory();
                                                $udlf_step2->getByUserIdAndDate($user_obj->getId(), $punch_date->format('Y-m-d'));

                                                    $user_date_id_step2 = 0;
                                                    if ( $udlf_step2->getRecordCount() == 1 ) {
                                                        $user_date_id_step2 = $udlf_step2->getCurrent()->getId();
                                                    }

                                                    $udtlf_s2 = new  UserDateTotalListFactory();
                                                    $udtlf_s2->getByUserDateIdAndStatusAndType($user_date_id_step2, 30, 10);
                                                            
                                                    if ( $udtlf_s2->getRecordCount() < 1 ) {
                                            
                                                        $late_date_arry[]= $punch_date;
                                                    }
                                        }
                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                        
                $number_of_late = count($late_date_arry);
                
                
                //   echo $user_obj->getId().' '.$number_of_late.'<br>';
                $sequence = 0;
                $not_in_sequence = 0;
                $is_in_seq = FALSE;
                $number_of_leave = 0;
                $month_last_date = NULL;
                
                for($i=0;$number_of_late>$i;$i++){
                    
                    $sequence++;
                    
                    $date = new DateTime($late_date_arry[$i]->format('Y-m-d'));
                    $month_last_date = $date->modify('last day of this month');    
                    
                    $hlf = new HolidayListFactory();
                    $hlf->getByPolicyGroupUserIdAndDate($user_obj->getId(),$late_date_arry[$i]->format('Y-m-d'));
                            
                    if($hlf->getRecordCount() > 0){
                        
                    } else{
                        $today =  new DateTime($late_date_arry[$i]->format('Y-m-d'));
                        
                        $day_name = $today->format("l");
                        
                        
                        $tomorrow_date = new DateTime($late_date_arry[$i]->format('Y-m-d'));
                        
                        if($day_name == 'Friday'){
                            $tomorrow_date->add(new DateInterval("P3D"));
                        } else{
                                $tomorrow_date->add(new DateInterval("P1D"));
                        }
                        
                        $hlf = new HolidayListFactory();
                        $hlf->getByPolicyGroupUserIdAndDate($user_obj->getId(),$tomorrow_date->format('Y-m-d'));
                        
                    
                        while($hlf->getRecordCount() > 0){
                            
                            $tomorrow_date->add(new DateInterval("P1D"));
                            
                            $hlf->getByPolicyGroupUserIdAndDate($user_obj->getId(),$tomorrow_date->format('Y-m-d'));
                            
                        }
                        
                        $tomorrow_string = $tomorrow_date;
                        
                        $next_date_string = "";
                        if($number_of_late > ($i+1)){
                            $next_date_string =  new DateTime($late_date_arry[$i+1]->format('Y-m-d'));
                        
                            
                            if($tomorrow_string->getTimestamp() == $next_date_string->getTimestamp()){
                                    $sequence++;
                                // echo $tomorrow_string.'<br>';
                                    
                                        $udlf_step1 = new UserDateListFactory();
                                        $udlf_step1->getByUserIdAndDate($user_obj->getId(), $next_date_string->format('Y-m-d'));
                                        
                                        $user_date_id_step1 = 0;
                                        if ( $udlf_step1->getRecordCount() == 1 ) {
                                $user_date_id_step1 = $udlf_step1->getCurrent()->getId();
                                        }
                        
                                        $udtlf_s1 = new  UserDateTotalListFactory();
                                        $udtlf_s1->getByUserDateIdAndStatusAndType($user_date_id_step1, 30, 10);
                                        
                                        if ( $udtlf_s1->getRecordCount() > 0 ) {
                                            
                                            $sequence--;
                                        // $not_in_sequence++;
                                        } else{
                                                        
                                            $next_plus_date = $next_date_string;
                                            $day_plus_name = $next_plus_date->format("l");

                                            if($day_plus_name == 'Friday'){
                                                $next_plus_date->add(new DateInterval("P3D"));
                                            }
                                            else{
                                                $next_plus_date->add(new DateInterval("P1D"));
                                                }

                                    
                                    
                                            $hlf = new HolidayListFactory();
                                            $hlf->getByPolicyGroupUserIdAndDate($user_obj->getId(),$next_plus_date->format('Y-m-d'));
                            
                        
                                            while($hlf->getRecordCount() > 0){

                                                $next_plus_date->add(new DateInterval("P1D"));

                                                $hlf->getByPolicyGroupUserIdAndDate($user_obj->getId(),$next_plus_date->format('Y-m-d'));


                                                }
                            

                                            $next_plus_date_string = $next_plus_date;
                                                $next_next_date_string = "";

                                            if($number_of_late > ($i+2)){
                                                        $next_next_date_string =  new DateTime($late_date_arry[$i+2]->format('Y-m-d'));


                                                        if($next_plus_date_string->getTimestamp() == $next_next_date_string->getTimestamp()){


                                                            // $not_in_sequence = 0;
                                                            $sequence++;
                                                            
                                                            
                                                            $udlf_step2 = new UserDateListFactory();
                                                            $udlf_step2->getByUserIdAndDate($user_obj->getId(), $next_next_date_string->format('Y-m-d'));

                                                            $user_date_id_step2 = 0;
                                                            if ( $udlf_step2->getRecordCount() == 1 ) {
                                                                    $user_date_id_step2 = $udlf_step2->getCurrent()->getId();
                                                            }

                                                            $udtlf_s2 = new  UserDateTotalListFactory();
                                                            $udtlf_s2->getByUserDateIdAndStatusAndType($user_date_id_step2, 30, 10);
                                                            
                                                                if ( $udtlf_s2->getRecordCount() > 0 ) {

                                                                    $sequence = 0;
                                                                    //  $not_in_sequence++;
                                                                }
                                                                else{

                                                                if(($sequence%3) == 0 && $sequence >2){

                                                                // $number_of_leave = $number_of_leave + 0.5;

                            


                                                                $ablf = new AccrualBalanceListFactory();
                                                                $ablf->getByUserIdAndAccrualPolicyId($user_obj->getId(),4);

                                                                    if( $ablf->getRecordCount() > 0){
                                                                    $abf = $ablf->getCurrent();

                                                                        $balance = $abf->getBalance();

                                                                        $amount_taken = ((0.4*10) * (28800/8));
                                                                        
                                                                        $amount_taken = -1 * abs($amount_taken);

                                                                        $current_balance = $balance -  $amount_taken;
                                                                        $abf->setBalance($current_balance);
                                                                        $abf->save();
                                                                        
                                                                        $udlf_f = new UserDateListFactory();
                                                                        $udlf_f->getByUserIdAndDate($user_obj->getId(), $late_date_arry[$i+2]->format('Y-m-d'));

                                                                            $user_date_id = 0;
                                                                            if ( $udlf_f->getRecordCount() == 1 ) {
                                                                                $user_date_id = $udlf_f->getCurrent()->getId();
                                                                            }

                                                                            $udt_obj = new UserDateTotalFactory(); 

                                                                            $udt_obj->setUserDateID($user_date_id);
                                                                            $udt_obj->setTotalTime($amount_taken);
                                                                            $udt_obj->setStatus(30);
                                                                            $udt_obj->setType(10);
                                                                            $udt_obj->setAbsencePolicyID(4);
                                                                            $udt_obj->setBranch($user_obj->getDefaultBranch());
                                                                            $udt_obj->setDepartment($user_obj->getDefaultDepartment());
                                                                            $udt_obj->setActualTotalTime($amount_taken);

                                                                            if ( $udt_obj->isValid() ){

                                                                                $udt_obj->Save();  
                                                                            }


                                                                        $af = new AccrualFactory();

                                                                        $af->setAccrualPolicyID(4);
                                                                        $af->setUser($user_obj->getId());
                                                                        $af->setType(20);
                                                                        $timestamp = $next_next_date_string->getTimestamp();
                                                                        $af->setTimeStamp($timestamp);

                                                                        $amount_taken = -1 * abs($amount_taken);
                                                                        $af->setAmount($amount_taken);
                                                                        $af->getEnableCalcBalance(TRUE);
                                                                        $af->save();






                                                                    }

                                                                $i= $i +2;
                                                                $sequence = 0;
                                                            }
                                                                }

                                                        }
                                                        else{
                                                            $not_in_sequence++;
                                                            $sequence = 0;
                                                        }
                                            }
                                            
                                        }// end of leavecheck
                                        
                                    
                                        
                            }
                            else{

                                $not_in_sequence++;
                                    $sequence = 0;
                            }
                        
                            }
                        
                        
                    }  
                    
                } // end of for loop
                        
                
                                $not_sequence_leave_count = $not_in_sequence /5;
                                    
                                        for($a=0;$a < $not_sequence_leave_count;$a++){
                                            
                                            
                                            $udlf = new UserDateListFactory();
                                            $udlf->getByUserIdAndDate($user_obj->getId(), $month_last_date->format('Y-m-d'));

                                            $user_date_id = 0;
                                            if ( $udlf->getRecordCount() == 1 ) {
                                                $user_date_id = $udlf->getCurrent()->getId();
                                            }
                        
                                            $ablf = new AccrualBalanceListFactory();
                                            $ablf->getByUserIdAndAccrualPolicyId($user_obj->getId(),4);

                                            if( $ablf->getRecordCount() > 0){
                                                $abf = $ablf->getCurrent();

                                                $balance = $abf->getBalance();

                                                $amount_taken = ((0.4*10) * (28800/8));
                                                
                                                // $amount_taken = -1 * abs($amount_taken);

                                                $current_balance = $balance -  $amount_taken;
                                                $abf->setBalance($current_balance);
                                                $abf->save();

                                                    $udt_obj = new UserDateTotalFactory();

                                                    $udt_obj->setUserDateID($user_date_id);
                                                    $udt_obj->setTotalTime($amount_taken);
                                                    $udt_obj->setStatus(30);
                                                    $udt_obj->setType(10);
                                                    $udt_obj->setAbsencePolicyID(4);
                                                    $udt_obj->setBranch($user_obj->getDefaultBranch());
                                                    $udt_obj->setDepartment($user_obj->getDefaultDepartment());
                                                    $udt_obj->setActualTotalTime($amount_taken);

                                                    if ( $udt_obj->isValid() ){

                                                        $udt_obj->Save();  
                                                    }


                                                $af = new AccrualFactory();

                                                $af->setAccrualPolicyID(4);
                                                $af->setUser($user_obj->getId());
                                                $af->setType(20);
                                                $timestamp = $month_last_date->getTimestamp();
                                                $af->setTimeStamp($timestamp);

                                                $amount_taken = -1 * abs($amount_taken);
                                                $af->setAmount($amount_taken);
                                                $af->getEnableCalcBalance(TRUE);
                                                $af->save();

                                            }

                                            
                                        }
                                        
                        $ppf->setIsHrProcess(1);
            
                        // $ppf->save();
                        
                    }//end pay periods IF
                    
                    
                }// end pay periods FOREACH
                
            
            }
            
        }
    

    }
}

?>