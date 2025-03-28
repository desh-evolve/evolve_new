<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Leaves\LeaveRequestListFactory;
use Illuminate\Support\Facades\View;

class ApprovedHrBy extends Controller
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

    public function index() {

        $viewData['title'] = 'Employee Leaves Supervisor Aprooval';
        
        //$lrlf = new LeaveRequestListFactory();
        $data = array();
        //$msg = "";
        $lrlf = new LeaveRequestListFactory();
        
        $lrlf->getByHrEmployeeId($current_user->getId());
        
        
         //echo $current_user->getRecordCount();
        $leave= array();
        if($lrlf->getRecordCount() >0){
            foreach($lrlf->rs as $lrf_obj) {
                $lrlf->data = (array)$lrf_obj;
                $lrf_obj = $lrlf;
        
                $leave['id'] = $lrf_obj->getId();
                $leave['user'] = $lrf_obj->getUserObject()->getFullName();
                $leave['user_id'] = $lrf_obj->getUser();
                $leave['leave_name'] = $lrf_obj->getAccuralPolicyObject()->getName();
                $leave['start_date'] = $lrf_obj->getLeaveFrom();
                $leave['end_date'] = $lrf_obj->getLeaveTo();
                $leave['amount'] = $lrf_obj->getAmount();
                $leave['is_hr_approved'] = $lrf_obj->getHrApproved();
                
                $data['leaves'][] =$leave;
            }
        }
         
        $data['msg'] = $msg;    
                
        $viewData['data'] = $data;

        return view('leaves/ApprovedHrBy', $viewData);

    }
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