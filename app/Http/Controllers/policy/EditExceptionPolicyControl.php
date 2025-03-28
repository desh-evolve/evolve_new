<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Policy\ExceptionPolicyControlFactory;
use App\Models\Policy\ExceptionPolicyControlListFactory;
use App\Models\Policy\ExceptionPolicyFactory;
use App\Models\Policy\ExceptionPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditExceptionPolicyControl extends Controller
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
        if ( !$permission->Check('exception_policy','enabled')
				OR !( $permission->Check('exception_policy','edit') OR $permission->Check('exception_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		} 
        */

		$viewData['title'] = isset($id) ? 'Edit Exception Policy' : 'Add Exception Policy';
		$current_company = $this->currentCompany;

		if ( isset($data['exceptions'])) {
			foreach( $data['exceptions'] as $code => $exception ) {
		
				if ( isset($exception['grace']) AND $exception['grace'] != '') {
					Debug::Text('Grace: '. $exception['grace'] , __FILE__, __LINE__, __METHOD__,10);
					$data['exceptions'][$code]['grace'] = TTDate::parseTimeUnit( $exception['grace'] );
				}
				if ( isset($exception['watch_window']) AND $exception['watch_window'] != '') {
					$data['exceptions'][$code]['watch_window'] = TTDate::parseTimeUnit( $exception['watch_window'] );
				}
			}
		}
		
		$epf = new ExceptionPolicyFactory();
		$epcf = new ExceptionPolicyControlFactory();

		$type_options = $epf->getTypeOptions( $current_company->getProductEdition() );

		if ( isset($id) AND $id != '' ) {
			$epclf = new ExceptionPolicyControlListFactory();
			$epclf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($epclf->rs as $epc_obj) {
				$epclf->data = (array)$epc_obj;
				$epc_obj = $epclf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$eplf = new ExceptionPolicyListFactory();
				$eplf->getByExceptionPolicyControlID( $id );
				if ( $eplf->getRecordCount() > 0 ) {
					foreach( $eplf->rs as $ep_obj ) {
						$eplf->data = (array)$ep_obj;
						$ep_obj = $eplf;
						
						if ( isset($type_options[$ep_obj->getType()]) ) {
							$ep_objs[$ep_obj->getType()] = $ep_obj;
						} else {
							//Delete exceptions that aren't part of the product.
							Debug::Text('Deleting exception outside product edition: '. $ep_obj->getID(), __FILE__, __LINE__, __METHOD__,10);

							$ep_obj->setDeleted(TRUE);
							if ( $ep_obj->isValid() ) {
								$ep_obj->Save();
							}
						}
					}
				}

				$exceptions = array();
				if ( isset($type_options) AND is_array($type_options) AND count($type_options) > 0 ) {
					foreach( $type_options as $exception_type => $exception_name ) {
						if ( isset($ep_objs[$exception_type]) ) {
							$ep_obj = $ep_objs[$exception_type];

							$exceptions[$ep_obj->getType()] = array(
																	'id' => $ep_obj->getId(),
																	'active' => $ep_obj->getActive(),
																	'type_id' => $ep_obj->getType(),
																	'name' => Option::getByKey( $ep_obj->getType(), $type_options ),
																	'severity_id' => $ep_obj->getSeverity(),
																	'email_notification_id' => $ep_obj->getEmailNotification(),
																	'demerit' => $ep_obj->getDemerit(),
																	'grace' => (int)$ep_obj->getGrace(),
																	'is_enabled_grace' => $ep_obj->isEnabledGrace( $ep_obj->getType() ),
																	'watch_window' => (int)$ep_obj->getWatchWindow(),
																	'is_enabled_watch_window' => $ep_obj->isEnabledWatchWindow( $ep_obj->getType() )
																	);
						}
					}
					unset($exception_name);
				}
				//var_dump($type_options, $ep_objs,$exceptions);
				
				//Populate default values.
				$default_exceptions = $epf->getExceptionTypeDefaultValues( array_keys($exceptions), $current_company->getProductEdition() );
				$exceptions = array_merge( $exceptions, $default_exceptions );

				$data = array(
									'id' => $epc_obj->getId(),
									'name' => $epc_obj->getName(),
									'exceptions' => $exceptions,
									'created_date' => $epc_obj->getCreatedDate(),
									'created_by' => $epc_obj->getCreatedBy(),
									'updated_date' => $epc_obj->getUpdatedDate(),
									'updated_by' => $epc_obj->getUpdatedBy(),
									'deleted_date' => $epc_obj->getDeletedDate(),
									'deleted_by' => $epc_obj->getDeletedBy()
								);
			}
		} else {
			//Populate default values.
			$exceptions = $epf->getExceptionTypeDefaultValues( NULL, $current_company->getProductEdition() );

			$data = array( 'exceptions' => $exceptions );
		}
		//print_r($data);

		//Select box options;
		$data['severity_options'] = $epf->getOptions('severity');
		$data['email_notification_options'] = $epf->getOptions('email_notification');
		
		$viewData['data'] = $data;
		$viewData['epf'] = $epf;
		$viewData['epcf'] = $epcf;
        return view('policy/EditExceptionPolicyControl', $viewData);

    }

	public function submit(Request $request){
		$epf = new ExceptionPolicyFactory();
		$epcf = new ExceptionPolicyControlFactory();

		$data = $request->data;
		$current_company = $this->currentCompany;

		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$epcf->setId( $data['id'] );
		$epcf->setCompany( $current_company->getId() );
		$epcf->setName( $data['name'] );

		if ( $epcf->isValid() ) {
			$epc_id = $epcf->Save();

			Debug::Text('aException Policy Control ID: '. $epc_id , __FILE__, __LINE__, __METHOD__,10);

			if ( $epc_id === TRUE ) {
				$epc_id = $data['id'];
			}

			Debug::Text('bException Policy Control ID: '. $epc_id , __FILE__, __LINE__, __METHOD__,10);

			if ( count($data['exceptions']) > 0 ) {
				foreach ($data['exceptions'] as $code => $exception_data) {
					Debug::Text('Looping Code: '. $code .' ID: '. $exception_data['id'], __FILE__, __LINE__, __METHOD__,10);

					if ( $exception_data['id'] != '' AND $exception_data['id'] > 0 ) {
						$epf->setId( $exception_data['id'] );
					}
					$epf->setExceptionPolicyControl( $epc_id );
					if ( isset($exception_data['active'])  ) {
						$epf->setActive( TRUE );
					} else {
						$epf->setActive( FALSE );
					}
					$epf->setType( $code );
					$epf->setSeverity( $exception_data['severity_id'] );
					$epf->setEmailNotification( $exception_data['email_notification_id'] );
					if ( isset($exception_data['demerit']) AND $exception_data['demerit'] != '') {
						$epf->setDemerit( $exception_data['demerit'] );
					}
					if ( isset($exception_data['grace']) AND $exception_data['grace'] != '' ) {
						$epf->setGrace( $exception_data['grace'] );
					}
					if ( isset($exception_data['watch_window']) AND $exception_data['watch_window'] != '' ) {
						$epf->setWatchWindow( $exception_data['watch_window'] );
					}
					if ( $epf->isValid() ) {
						$epf->Save();
					}
				}
			}

			Redirect::Page( URLBuilder::getURL( NULL, 'ExceptionPolicyControlList') );
		}

	}
}

?>