<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Leaves\LeaveRequestListFactory;
use Illuminate\Support\Facades\View;

class ApprovedCoveredBy extends Controller
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

        $current_user = $this->currentCompany;

        $viewData['title'] = 'Employee Leaves covered Aprooval';
        
        $lrlf = new LeaveRequestListFactory();
        $lrlf->getByCoveredEmployeeId($current_user->getId());
        
        $data = array();
        
        $leave= array();
        if($lrlf->getRecordCount() >0){
            
           
        foreach($lrlf->rs as $lrf_obj) {
            $lrlf->data = (array)$lrf_obj;
            $lrf_obj = $lrlf;
        
            $leave['id'] = $lrf_obj->getId();
            $leave['user'] = $lrf_obj->getUserObject()->getFullName();
            
            $methord = $lrf_obj->getOptions('leave_method');
            $leave['leave_method'] = $methord[$lrf_obj->getLeaveMethod()];
            $leave['start_date'] = $lrf_obj->getLeaveFrom();
            $leave['end_date'] = $lrf_obj->getLeaveTo();
            $leave['is_covered_approved'] = $lrf_obj->getCoveredApproved();
            
            $data['leaves'][] =$leave;
        }
        }
         
        $viewData['data'] = $data;

        return view('leaves/ApprovedCoveredBy', $viewData);

    }
}
