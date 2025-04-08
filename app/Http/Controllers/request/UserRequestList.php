<?php

namespace App\Http\Controllers\request;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class UserRequestList extends Controller
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
		/*
		if ( !$permission->Check('request','enabled')
				OR !( $permission->Check('request','view') OR $permission->Check('request','view_own') OR $permission->Check('request','view_child') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
		*/

		$viewData['title'] = 'Request List';
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;

		$filter_data = [];
		$rlf = new RequestListFactory(); 
		$rlf->getByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		
		/*
		if ( isset($filter_start_date) AND $filter_start_date != '' AND isset($filter_end_date) AND $filter_end_date != '') {
			$rlf->getByUserIdAndCompanyIdAndStartDateAndEndDate( $user_id, $current_company->getId(), $filter_start_date, $filter_end_date, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
		} else {
			$rlf->getByUserIDAndCompanyId( $user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
		}
		*/

		$status_options = $rlf->getOptions('status');
		$type_options = $rlf->getOptions('type');

		$requests = [];

		foreach ($rlf->rs as $r_obj) {
			$rlf->data = (array)$r_obj;
			$r_obj = $rlf;

			$requests[] = 	array(
								'id' => $r_obj->getId(),
								'user_date_id' => $r_obj->getUserDateID(),
								'date_stamp' => TTDate::strtotime($r_obj->getColumn('date_stamp')),
								'status_id' => $r_obj->getStatus(),
								'status' => $status_options[$r_obj->getStatus()],
								'type_id' => $r_obj->getType(),
								'type' => $type_options[$r_obj->getType()],
								'created_date' => $r_obj->getCreatedDate(),
								'deleted' => $r_obj->getDeleted()
							);

		}

		$viewData['requests'] = $requests;

		return view('request/UserRequestList', $viewData);
	}

	public function delete($id){
		$current_company = $this->currentCompany;
		$delete = TRUE;

		$rlf = new RequestListFactory();

		$rlf->getByIdAndCompanyId( $id, $current_company->getId() );
		
		foreach ($rlf->rs as $r_obj) {
			$rlf->data = (array)$r_obj;
			unset($rlf->data['date_stamp']);

			$r_obj = $rlf;
			$r_obj->setDeleted($delete);
			$res = $r_obj->Save();

			if($res){
				return response()->json(['success' => 'Request Deleted Successfully.']);
			}else{
				return response()->json(['error' => 'Request Deleted Failed.']);
			}
		}

		return redirect(URLBuilder::getURL( NULL, '/attendance/requests') );

	}
}


?>