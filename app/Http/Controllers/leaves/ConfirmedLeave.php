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
use App\Models\Core\TTPDF;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use DateTime;
use Illuminate\Support\Facades\View;

class ConfirmedLeave extends Controller
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

    // public function index()
    // {
    //     $current_user = $this->currentUser;
    //     $viewData['title'] = 'Confiremed Leave';

	// 	if(!isset($filter_data)){
	// 	   $filter_data = array();
	// 	}

	// 	if ( isset($filter_data['start_date']) && $filter_data['start_date'] !='' ) {
	// 		$from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['start_date']);
	// 		$filter_data['start_date'] =  $from_date->format('Y-m-d');
	// 	}

	// 	if ( isset($filter_data['end_date']) && $filter_data['end_date'] !='' ) {
	// 		$from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['end_date']);
	// 		$filter_data['end_date'] =  $from_date->format('Y-m-d');
	// 	}

	// 	$lrlf = new LeaveRequestListFactory();

	// 	$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);
	//    	$leaves = [];

	//    	if($lrlf->getRecordCount() >0){

	// 		foreach($lrlf->rs as $lrf_obj) {
	// 			$lrlf->data = (array)$lrf_obj;
	// 			$lrf_obj = $lrlf;

    //             $leaves [] = array(
    //                 'id' => $lrf_obj->getId(),
    //                 'user' => $lrf_obj->getUserObject()->getFullName(),
    //                 'user_id' => $lrf_obj->getUser(),
    //                 'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
    //                 'start_date' => $lrf_obj->getLeaveFrom(),
    //                 'end_date' => $lrf_obj->getLeaveTo(),
    //                 'amount' => $lrf_obj->getAmount(),
    //                 'is_hr_approved' => $lrf_obj->getHrApproved()
    //             );
	// 		}
	//    	}

	//     $viewData['leaves'] = $leaves;
    //     // dd($viewData);

    //     return view('leaves/ConfirmedLeave', $viewData);
	// }


    public function index(Request $request)
    {
        $viewData['title'] = 'Confirmed Leave';
        $current_user = $this->currentUser;

        // Handle filter input
        $filter_data = $request->input('filter_data', []);
        $filter_user_id = $request->input('user_id', null);

        if ($filter_user_id === null) {
            $filter_user_id = $current_user->getId();
        }

        // Convert date formats if present
        if (!empty($filter_data['start_date'])) {
            $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['start_date']);
            $filter_data['start_date'] = $from_date ? $from_date->format('Y-m-d') : null;
        }

        if (!empty($filter_data['end_date'])) {
            $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['end_date']);
            $filter_data['end_date'] = $from_date ? $from_date->format('Y-m-d') : null;
        }

        // Get confirmed leaves
        $lrlf = new LeaveRequestListFactory();
        $lrlf->getAllConfirmedLeave($filter_user_id, $filter_data);
        $leaves = [];

        if ($lrlf->getRecordCount() > 0) {
            foreach ($lrlf->rs as $lrf_obj) {
                $lrlf->data = (array) $lrf_obj;
                $lrf_obj = $lrlf;

                $leaves[] = [
                    'id' => $lrf_obj->getId(),
                    'user' => $lrf_obj->getUserObject()->getFullName(),
                    'user_id' => $lrf_obj->getUser(),
                    'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
                    'start_date' => $lrf_obj->getLeaveFrom(),
                    'end_date' => $lrf_obj->getLeaveTo(),
                    'amount' => $lrf_obj->getAmount(),
                    'is_hr_approved' => $lrf_obj->getHrApproved(),
                ];
            }
        }

        // Load employee options
        $ulf = new UserListFactory();
        $ulf->getSearchByCompanyIdAndArrayCriteria($current_user->getId(), []);
        $user_options = ['' => '--Select Employee--'] + UserListFactory::getArrayByListFactory($ulf, false, true);

        $viewData['user_options'] = $user_options;
        $viewData['filter_user_id'] = $filter_user_id;
        $viewData['filter_data'] = $filter_data;
        $viewData['leaves'] = $leaves;
        // dd($viewData);

        return view('leaves.ConfirmedLeave', $viewData);
    }



	public function export(){}


	// public function search()
    // {
    //     $viewData['title'] = 'Confiremed Leave';
	// 	$current_user = $this->currentUser;

    //     if(!isset($filter_data)){
    //         $filter_data = array();
    //     }

    //     if ( isset($filter_data['start_date']) && $filter_data['start_date'] !='' ) {
    //         //$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);

    //             // $from_date =  DateTime::createFromFormat('j-M-y', $filter_data['start_date']);
    //             // $from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['start_date']);
    //             $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['start_date']);
    //             $filter_data['start_date'] =  $from_date->format('Y-m-d');
    //     }

    //     if ( isset($filter_data['end_date']) && $filter_data['end_date'] !='' ) {
    //         //$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);

    //         // $from_date =  DateTime::createFromFormat('j-M-y', $filter_data['start_date']);
    //             // $from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['end_date']);
    //             $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['end_date']);
    //             $filter_data['end_date'] =  $from_date->format('Y-m-d');
    //     }


	// 	$lrlf = new LeaveRequestListFactory();

	// 	$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);

	// 	//echo $current_user->getRecordCount();
	// 	$leaves = [];

	// 	if($lrlf->getRecordCount() >0){

	// 		foreach($lrlf->rs as $lrf_obj) {
	// 			$lrlf->data = (array)$lrf_obj;
	// 			$lrf_obj = $lrlf;

    //             $leaves [] = array(
    //                 'id' => $lrf_obj->getId(),
    //                 'user' => $lrf_obj->getUserObject()->getFullName(),
    //                 'user_id' => $lrf_obj->getUser(),
    //                 'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
    //                 'start_date' => $lrf_obj->getLeaveFrom(),
    //                 'end_date' => $lrf_obj->getLeaveTo(),
    //                 'amount' => $lrf_obj->getAmount(),
    //                 'is_hr_approved' => $lrf_obj->getHrApproved()
    //             );

	// 		}
	// 	}

    //     $viewData['leaves'] = $leaves;
    //     dd($viewData);

    //     return view('leaves/ConfirmedLeave', $viewData);

	// }


    public function search(Request $request)
    {
        $viewData['title'] = 'Confirmed Leave';
        $current_user = $this->currentUser;

        // Get filters
        $filter_data = $request->input('filter_data', []);
        $filter_user_id = $request->input('user_id', null);

        // Add user_id to filter data
        if (!empty($filter_user_id)) {
            $filter_data['user_id'] = $filter_user_id;
        }

        // Format start date
        if (!empty($filter_data['start_date'])) {
            $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['start_date']);
            $filter_data['start_date'] = $from_date ? $from_date->format('Y-m-d') : null;
        }

        // Format end date
        if (!empty($filter_data['end_date'])) {
            $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['end_date']);
            $filter_data['end_date'] = $from_date ? $from_date->format('Y-m-d') : null;
        }

        // Call leave search with full filters
        $lrlf = new LeaveRequestListFactory();
        $lrlf->getAllConfirmedLeave($filter_user_id, $filter_data);  // Pass user + date

        // Prepare leave data
        $leaves = [];

        if ($lrlf->getRecordCount() > 0) {
            foreach ($lrlf->rs as $lrf_obj) {
                $lrlf->data = (array)$lrf_obj;
                $lrf_obj = $lrlf;

                $leaves[] = [
                    'id' => $lrf_obj->getId(),
                    'user' => $lrf_obj->getUserObject()->getFullName(),
                    'user_id' => $lrf_obj->getUser(),
                    'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
                    'start_date' => $lrf_obj->getLeaveFrom(),
                    'end_date' => $lrf_obj->getLeaveTo(),
                    'amount' => $lrf_obj->getAmount(),
                    'is_hr_approved' => $lrf_obj->getHrApproved(),
                ];
            }
        }

        // Load dropdown options
        $ulf = new UserListFactory();
        $ulf->getSearchByCompanyIdAndArrayCriteria($current_user->getId(), []);
        $user_options = ['' => '--Select Employee--'] + UserListFactory::getArrayByListFactory($ulf, false, true);

        $viewData['user_options'] = $user_options;
        $viewData['filter_user_id'] = $filter_user_id;
        $viewData['filter_data'] = $filter_data;
        $viewData['leaves'] = $leaves;

        return view('leaves.ConfirmedLeave', $viewData);
    }



	function returnBetweenDates( $startDate, $endDate )
    {
		$startStamp = strtotime( $startDate );
		$endStamp   = strtotime( $endDate );

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


    public function delete($id)
    {
		if (empty($id)) {
            return response()->json(['error' => 'No Leave Selected.'], 400);
        }

		$delete = TRUE;

        $lrlf = new LeaveRequestListFactory();
		$lrlf->getById($id );

		foreach ($lrlf->rs as $leave_obj) {
			$lrlf->data = (array)$leave_obj;
			$leave_obj = $lrlf;

			$leave_obj->setDeleted($delete);
			if ( $leave_obj->isValid() ) {
				$res = $leave_obj->Save();

				if($res){
					return response()->json(['success' => 'Leave Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Leave Deleted Failed.']);
				}
			}
		}
        Redirect::Page( URLBuilder::getURL( NULL, 'ConfirmedLeave') );

	}






}
