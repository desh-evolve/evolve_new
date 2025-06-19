<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Users\UserListFactory;
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

        $current_user = $this->currentUser;

        $viewData['title'] = 'Employee Leaves covered Aprooval';

        $lrlf = new LeaveRequestListFactory();
        $lrlf->getByCoveredEmployeeId($current_user->getId());

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
                    'leave_method' => $methord[$lrf_obj->getLeaveMethod()],
                    'start_date' => $lrf_obj->getLeaveFrom(),
                    'end_date' => $lrf_obj->getLeaveTo(),
                    'is_covered_approved' => $lrf_obj->getCoveredApproved(),

                );

            }
        }

        $viewData['leaves'] = $leaves;
        // dd($viewData);

        return view('leaves/ApprovedCoveredBy', $viewData);

    }


}
