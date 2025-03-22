<?php

namespace App\Http\Controllers\department;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentFactory;
use App\Models\Department\DepartmentListFactory;
use Illuminate\Support\Facades\View;

class EditDepartment extends Controller
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

    public function index($id = null) {

        /*
        if ( !$permission->Check('department','enabled')
				OR !( $permission->Check('department','view') OR $permission->Check('department','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		
        $viewData['title'] = $id ? 'Edit Department' : 'Add Department';


		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'department_data'
			) 
		) );

		if ( isset($id) ) {

			$dlf = new DepartmentListFactory();

			$dlf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($dlf->rs as $department) {
				$dlf->data = (array)$department;
				$department = $dlf;

				Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

				$department_data = array (
					'id' => $department->getId(),
					'company_name' => $current_company->getName(),
					'status' => $department->getStatus(),
					'name' => $department->getName(),
					'manual_id' => $department->getManualID(),
					'branch_list' => $department->getBranch(),
					'other_id1' => $department->getOtherID1(),
					'other_id2' => $department->getOtherID2(),
					'other_id3' => $department->getOtherID3(),
					'other_id4' => $department->getOtherID4(),
					'other_id5' => $department->getOtherID5(),
					'created_date' => $department->getCreatedDate(),
					'created_by' => $department->getCreatedBy(),
					'updated_date' => $department->getUpdatedDate(),
					'updated_by' => $department->getUpdatedBy(),
					'deleted_date' => $department->getDeletedDate(),
					'deleted_by' => $department->getDeletedBy()
				);
			}
		} elseif ( $action != 'submit' ) {
			$next_available_manual_id = DepartmentListFactory::getNextAvailableManualId( $current_company->getId() );

			$department_data = array(
							'next_available_manual_id' => $next_available_manual_id,
							);
		}

		//Select box options;
		$department_data['status_options'] = $df->getOptions('status');
		$blf = new BranchListFactory(); 
		$blf->getByCompanyId( $current_company->getId() );
		$department_data['branch_list_options'] = $blf->getArrayByListFactory( $blf, FALSE);

		//Get other field names
		$oflf = new OtherFieldListFactory(); 
		$department_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getID(), 5 );

		$smarty->assign_by_ref('department_data', $department_data);

		
		$smarty->assign_by_ref('df', $df);

		$smarty->display('department/EditDepartment.tpl');

        return view('accrual/ViewUserAccrualList', $viewData);

    }

	public function submit(Request $request){
		$current_company = $this->currentCompany();
		$department_data = $request->data;

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		$df = new DepartmentFactory();

		$df->setId($department_data['id']);
		$df->setCompany( $current_company->getId() );
		$df->setStatus($department_data['status']);
		$df->setName($department_data['name']);
		$df->setManualId($department_data['manual_id']);

		if ( isset($department_data['other_id1']) ) {
			$df->setOtherID1( $department_data['other_id1'] );
		}
		if ( isset($department_data['other_id2']) ) {
			$df->setOtherID2( $department_data['other_id2'] );
		}
		if ( isset($department_data['other_id3']) ) {
			$df->setOtherID3( $department_data['other_id3'] );
		}
		if ( isset($department_data['other_id4']) ) {
			$df->setOtherID4( $department_data['other_id4'] );
		}
		if ( isset($department_data['other_id5']) ) {
			$df->setOtherID5( $department_data['other_id5'] );
		}

		if ( $df->isValid() ) {
			$df->Save(FALSE);

			if ( isset($department_data['branch_list']) ){
				$df->setBranch( $department_data['branch_list'] );
				$df->Save(TRUE);
			}

			Redirect::Page( URLBuilder::getURL(NULL, 'DepartmentList.php') );
		}
	}
}

?>