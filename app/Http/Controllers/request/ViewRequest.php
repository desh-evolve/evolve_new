<?php

namespace App\Http\Controllers\request;

use App\Http\Controllers\Controller;
use App\Models\Core\AuthorizationFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyLevelListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Request\RequestFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class ViewRequest extends Controller
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

		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;
		$current_user_prefs = $this->userPrefs;

		if ( !$permission->Check('request','enabled')
				OR !( $permission->Check('request','edit')
						OR $permission->Check('request','edit_own')
						) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Request List';
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;

		$viewData['title'] = 'View Request';

		// Get FORM variables
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'hierarchy_type_id',
				'request_id',
				'request_queue_ids',
				'selected_level'
			) 
		) );

		if ( isset($request_queue_ids) ) {
			$request_queue_ids = unserialize( base64_decode( urldecode($request_queue_ids) ) );
			Debug::Arr($request_queue_ids, ' Input Request Queue IDs '. $action, __FILE__, __LINE__, __METHOD__,10);
		}
		if ( isset($data) ) {
			$data['date_stamp'] = TTDate::parseDateTime($data['date_stamp']);
		}

		$rf = new RequestFactory(); 

		$action = $_POST['action'] ?? '';
		$action = !empty($action) ? strtolower($action) : '';

		switch ($action) {
			case 'pass':
				if ( count($request_queue_ids) > 1 ) {
					//Remove the authorized/declined request from the stack.
					array_shift($request_queue_ids);
					Redirect::Page( URLBuilder::getURL( array('id' => $request_queue_ids[0], 'selected_level' => $selected_level, 'request_queue_ids' => base64_encode( serialize($request_queue_ids) ) ), 'ViewRequest.php') );
				} else {
					Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
				}
				break;
			case 'decline':
			case 'authorize':
				//Debug::setVerbosity(11);
				Debug::text(' Authorizing Request: Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
				if ( !empty($request_id) ) {
					Debug::text(' Authorizing Request ID: '. $request_id, __FILE__, __LINE__, __METHOD__,10);

					$af = new AuthorizationFactory();
					$af->setObjectType( $hierarchy_type_id );
					$af->setObject( $request_id );

					if ( $action == 'authorize' ) {
						Debug::text(' Approving Authorization: ', __FILE__, __LINE__, __METHOD__,10);
						$af->setAuthorized(TRUE);
					} else {
						Debug::text(' Declining Authorization: ', __FILE__, __LINE__, __METHOD__,10);
						$af->setAuthorized(FALSE);
					}

					if ( $af->isValid() ) {
						$af->Save();

						if ( count($request_queue_ids) > 1 ) {
							//Remove the authorized/declined request from the stack.
							array_shift($request_queue_ids);
							Redirect::Page( URLBuilder::getURL( array('id' => $request_queue_ids[0], 'selected_level' => $selected_level, 'request_queue_ids' => base64_encode( serialize($request_queue_ids) ) ), 'ViewRequest.php') );
							break;
						}
					}
				}
				Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
				break;
			default:
				if ( (int)$id > 0 ) {
					Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

					$rlf = new RequestListFactory();
					$rlf->getByIDAndCompanyID( $id, $current_company->getId() );
					if ( $rlf->getRecordCount() == 1 ) {
						foreach ($rlf->rs as $r_obj) {
							$rlf->data = (array)$r_obj;
							$r_obj = $rlf;

							//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
							$hierarchy_type_id = $r_obj->getHierarchyTypeID();
							$type_id = $r_obj->getType();

							$data = array(
												'id' => $r_obj->getId(),
												'user_date_id' => $r_obj->getId(),
												'user_id' => $r_obj->getUserDateObject()->getUser(),
												'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
												'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
												'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
												'type_id' => $r_obj->getType(),
												'hierarchy_type_id' => $r_obj->getHierarchyTypeID(),
												'status_id' => $r_obj->getStatus(),
												'authorized' => $r_obj->getAuthorized(),
												'created_date' => $r_obj->getCreatedDate(),
												'created_by' => $r_obj->getCreatedBy(),
												'updated_date' => $r_obj->getUpdatedDate(),
												'updated_by' => $r_obj->getUpdatedBy(),
												'deleted_date' => $r_obj->getDeletedDate(),
												'deleted_by' => $r_obj->getDeletedBy()
											);
						}

						//Get Next Request to authorize:
						if ( $permission->Check('request','authorize')
								AND $selected_level != NULL
								AND count($request_queue_ids) <= 1 ) {

							Debug::Text('Get Request Queue: ', __FILE__, __LINE__, __METHOD__,10);

							$ulf = new UserListFactory();
							$hlf = new HierarchyListFactory();
							$hllf = new HierarchyLevelListFactory();

							$request_levels = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), $hierarchy_type_id );
							//Debug::Arr( $request_levels, 'Request Levels', __FILE__, __LINE__, __METHOD__,10);

							if ( isset($selected_level) AND isset($request_levels[$selected_level]) ) {
								$request_selected_level = $request_levels[$selected_level];
								Debug::Text(' Switching Levels to Level: '. key($request_selected_level), __FILE__, __LINE__, __METHOD__,10);
							} elseif ( isset($request_levels[1]) ) {
								$request_selected_level = $request_levels[1];
							} else {
								Debug::Text( 'No Request Levels... Not in hierarchy?', __FILE__, __LINE__, __METHOD__,10);
								$request_selected_level = 0;
							}

							if ( is_array($request_selected_level) ) {
								Debug::Text( 'Hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
								$rlf = new RequestListFactory();
								//$rlf->getByHierarchyLevelMapAndStatusAndNotAuthorized( $request_selected_level, 30 );
								$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($request_selected_level, $type_id, 30 );

								//Get all IDs that need authorizing.
								//Only do 25 at a time, then grab more.
								$i=0;
								$start=FALSE;
								foreach( $rlf->rs as $r_obj) {
									$rlf->data = (array)$r_obj;
									$r_obj = $rlf;

									if ( $id == $r_obj->getId() ) {
										$start = TRUE;
									}

									if ( $start == TRUE ) {
										$request_queue_ids[] = $r_obj->getId();
									}

									if ( $i > 25 ) {
										break;
									}
									$i++;
								}

								if ( isset($request_queue_ids) ) {
									$request_queue_ids = array_unique($request_queue_ids);
								}
							} else {
								Debug::Text( 'No hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
							}
						}
					}
				}

				//Select box options;
				$data['status_options'] = $rf->getOptions('status');
				$data['type_options'] = $rf->getOptions('type');

				if ( isset($request_queue_ids) ) {
					Debug::Arr($request_queue_ids, ' Output Request Queue IDs '. $action, __FILE__, __LINE__, __METHOD__,10);
					$viewData['request_queue_ids'] = urlencode( base64_encode( serialize($request_queue_ids) ) ) ;
				}else{
					$viewData['request_queue_ids'] = '';
				}

				$viewData['selected_level'] = $selected_level;
				$viewData['data'] = $data;

				break;
		}

		$viewData['rf'] = $rf;
		//dd($viewData);
		return view('request/ViewRequest', $viewData);

	}
}




?>