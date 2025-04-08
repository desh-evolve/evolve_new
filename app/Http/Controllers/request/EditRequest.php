<?php

namespace App\Http\Controllers\request;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class EditRequest extends Controller
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

	}
}

if ( !$permission->Check('request','enabled')
		OR !( $permission->Check('request','edit')
				OR $permission->Check('request','edit_own')
				 ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Edit Request')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data) ) {
	$data['date_stamp'] = TTDate::parseDateTime($data['date_stamp']);
}

$rf = new RequestFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rf->StartTransaction();

		$rf->setId( $data['id'] );
		$rf->setUserDate( $data['user_id'], $data['date_stamp'] );

		$rf->setType( $data['type_id'] );
		$rf->setStatus( 30 );
		if ( $rf->isNew() ) {
			Debug::Text('Object is NEW!', __FILE__, __LINE__, __METHOD__,10);
			$rf->setMessage( $data['message'] );
		} else {
			Debug::Text('Object is NOT new!', __FILE__, __LINE__, __METHOD__,10);
		}

		if ( $rf->isValid() ) {
			$request_id = $rf->Save();

			$rf->CommitTransaction();
			//$rf->FailTransaction();

			//Redirect::Page( URLBuilder::getURL( array('refresh' => FALSE ), '../CloseWindow.php') );

			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );

		}
		$rf->FailTransaction();

	default:
		if ( (int)$id > 0 ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$rlf = new RequestListFactory();
			$rlf->getByIDAndCompanyID( $id, $current_company->getId() );

			foreach ($rlf as $r_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $r_obj->getId(),
									'user_date_id' => $r_obj->getId(),
									'user_id' => $r_obj->getUserDateObject()->getUser(),
									'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
									'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
									'type_id' => $r_obj->getType(),
									'status_id' => $r_obj->getStatus(),
									'created_date' => $r_obj->getCreatedDate(),
									'created_by' => $r_obj->getCreatedBy(),
									'updated_date' => $r_obj->getUpdatedDate(),
									'updated_by' => $r_obj->getUpdatedBy(),
									'deleted_date' => $r_obj->getDeletedDate(),
									'deleted_by' => $r_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);
			//UserID has to be set at minimum
			$data = array(
						'user_id' => $current_user->getId(),
						'user_full_name' => $current_user->getFullName(),
						'date_stamp' => TTDate::getTime()
					);
		} else {
			$data['user_full_name'] = $current_user->getFullName();
		}

		//Select box options;
		$data['status_options'] = $rf->getOptions('status');
		$data['type_options'] = $rf->getOptions('type');

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('rf', $rf);

$smarty->display('request/EditRequest.tpl');
?>