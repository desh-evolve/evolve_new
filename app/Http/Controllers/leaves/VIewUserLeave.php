<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Leaves\LeaveRequestFactory;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class VIewUserLeave extends Controller
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

    public function index($id)
    {
        /*
        if ( !$permission->Check('accrual','view')
				OR (  $permission->Check('accrual','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $current_user = $this->currentUser;
        $current_company = $this->currentCompany;
        $viewData['title'] = 'View Employee Leave';

		$lrlf = new LeaveRequestListFactory();
		$lrlf->getById($id);

		if($lrlf->getRecordCount() >0){

			$lrf =  $lrlf->getCurrent();

			$aplf = new AccrualPolicyListFactory();
			$aplf->getByCompanyIdAndTypeId($current_company->getId(),20);

			$leave_options = array();
			foreach($aplf->rs as $apf){
                $aplf->data = (array)$apf;
                $apf = $aplf;

				$leave_options[$apf->getId()]=$apf->getName();
			}

			$leave_options = Misc::prependArray( array( 0 => _('-- Please Choose --') ), $leave_options );
			$data['leave_options'] = $leave_options;

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


			$leaves = $lrf->getLeaveDates();

			$date_array = explode(',', $leaves);
			$date_string = '';

			foreach($date_array as $date){
			$date_string .= "'".trim($date)."'," ;
			}

			$user_options = Misc::prependArray( array( 0 => _('-- Please Choose --') ), $user_options );
			$data['users_cover_options'] = $user_options;
			//$data['users_cover_options'] = $ulf;
			$data['name'] =$lrf->getUserObject()->getFullName();
            $data['title'] = $lrf->getDesignationObject()->getName();
            $data['title_id'] = $lrf->getDesignationObject()->getId();

			$data['leave_type'] = $lrf->getAccuralPolicyObject()->getId();

			$method_options = $lrf->getOptions('leave_method');
			$method_options = Misc::prependArray( array( 0 => _('-- Please Choose --') ), $method_options );

			$data['method_options'] = $method_options;
			$data['method_type'] = $lrf->getLeaveMethod();

			$data['no_days'] = $lrf->getAmount();
			$data['leave_start_date'] = $lrf->getLeaveFrom();
			$data['leave_end_date'] = $lrf->getLeaveTo();
			$data['reason']=$lrf->getReason();
			$data['address_tel']=$lrf->getAddressTelephone();
			$data['cover_duty']=$lrf->getCoveredBy();
			$data['supervised_by']=$lrf->getSupervisorId();
			$data['appt_time']=$lrf->getLeaveTime();
			$data['end_time']=$lrf->getLeaveEndTime();
			$data['leave_dates']=$date_string;
		}

		$viewData['data'] = $data;
		$viewData['user'] = $current_user;
        // dd($viewData);

        return view('leaves/VIewUserLeave', $viewData);

    }


}


?>
