<?php

namespace App\Http\Controllers\permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\PermissionControlFactory;
use App\Models\Core\PermissionControlListFactory;
use App\Models\Core\PermissionFactory;
use App\Models\Core\PermissionListFactory;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditPermissionControl extends Controller
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
        if ( !$permission->Check('permission','enabled')
				OR !( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Edit Permission Group';

		$pcf = new PermissionControlFactory();

		$pf = new PermissionFactory();
		$plf = new PermissionListFactory();

		if ( isset($id) ) {

			$pclf = new PermissionControlListFactory();

			$pclf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($pclf->rs as $pc_obj) {
				$pclf->data = (array)$pc_obj;
				$pc_obj = $pclf;

				$data = array(
									'id' => $pc_obj->getId(),
									'name' => $pc_obj->getName(),
									'description' => $pc_obj->getDescription(),
									'level' => $pc_obj->getLevel(),
									'user_ids' => $pc_obj->getUser(),
									'created_date' => $pc_obj->getCreatedDate(),
									'created_by' => $pc_obj->getCreatedBy(),
									'updated_date' => $pc_obj->getUpdatedDate(),
									'updated_by' => $pc_obj->getUpdatedBy(),
									'deleted_date' => $pc_obj->getDeletedDate(),
									'deleted_by' => $pc_obj->getDeletedBy()
								);
			}

			//$plf->getAllPermissionsByCompanyIdAndPermissionControlId($company_id, $id);
			$plf->getByCompanyIdAndPermissionControlId( $current_company->getId(), $id );
			if ( $plf->getRecordCount() > 0 ) {
				Debug::Text('Found Current Permissions!', __FILE__, __LINE__, __METHOD__,10);
				foreach($plf->rs as $p_obj) {
					$plf->data = (array)$p_obj;
					$p_obj = $plf;

					foreach($plf->rs as $p_obj) {
						$plf->data = (array)$p_obj;
						$p_obj = $plf;
						$current_permissions[$p_obj->getSection()][$p_obj->getName()] = $p_obj;
					}
				}
			}
			//print_r($current_permissions);

		}

		$section_groups = Misc::prependArray( array( -1 => _('-- None --')), $pf->getOptions('section_group') );
		$section_group_map = $pf->getOptions('section_group_map');
		$sections = $pf->getOptions('section');
		$names = $pf->getOptions('name');

		//Trim out ignored sections
		foreach( $section_groups as $section_group_key => $section_group_value ) {
			if ( $pf->isIgnore( $section_group_key, NULL, $current_company->getProductEdition() ) == TRUE ) {
				unset($section_groups[$section_group_key]);
			}
		}
		unset($section_group_key, $section_group_value);

		if ( !isset($group_id) OR !isset($section_groups[$group_id]) ) {
			$group_id = 0; //None
			//$group_id = 'all'; //None
		}
		Debug::Text('Group ID: '. $group_id, __FILE__, __LINE__, __METHOD__,10);

		foreach ($names as $section => $permission_arr) {
			if (
					( $pf->isIgnore( $section, NULL, $current_company->getProductEdition() ) == FALSE )
					AND
					( ( $group_id == 'all' AND $group_id !== 0 ) OR ( isset($section_group_map[$group_id]) AND in_array($section,$section_group_map[$group_id]) ) )
					) {

				foreach($permission_arr as $name => $display_name) {

						if ( isset($current_permissions[$section][$name]) ) {
							$permission_result_obj = $current_permissions[$section][$name];

							Debug::Text(' Permission Check Section: '. $section .' - Name: '. $name .' - Get Permission Control: '. $permission_result_obj->getPermissionControl(), __FILE__, __LINE__, __METHOD__,10);
							$permission_result = $permission_result_obj->getValue();

							$permissions[] = array('name' => $name, 'display_name' => $display_name, 'result' => $permission_result);
						} elseif ( $pf->isIgnore( $section, $name, $current_company->getProductEdition() ) == FALSE ) {
							$permissions[] = array('name' => $name, 'display_name' => $display_name, 'result' => NULL );
						}
				}

				//If you get a index error just below here, you forgot to
				//enter the section name in PayStubFactory.class.php
				$permission_data[] = array(
											'name' => $section,
											'display_name' => $sections[$section],
											'permissions' => $permissions
											);

				unset($permissions);
			}

		}

		//var_dump($permission_data);
		$preset_options = Misc::prependArray( array( -1 => _('--')), $pf->getOptions('preset') );


		$data['level_options'] = $pcf->getOptions('level');

		$data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE);

		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}

		$viewData['filter_user_options'] = $filter_user_options ;
		$viewData['data'] = $data ;

		$viewData['preset_options'] = $preset_options ;
		$viewData['section_group_options'] = $section_groups ;
		$viewData['user_options'] = $user_options;
		$viewData['permission_data'] = $permission_data;
		$viewData['ignore_permissions'] = $ignore_permissions;
		$viewData['id'] = $id;
		$viewData['group_id'] = $group_id;
		$viewData['product_edition'] = $current_company->getProductEdition();

		$viewData['pcf'] = $pcf;
        return view('permission/EditPermissionControl', $viewData);

    }

	public function apply_preset(Request $request){
		$pcf = new PermissionControlFactory();

		$data = $request->data;
		$current_company = $this->currentCompany;

		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$pf = new PermissionFactory();
		$pcf->StartTransaction();

		$pcf->setId( $data['id'] );
		$pcf->setCompany( $current_company->getId() );

		$pcf->setName($data['name']);
		$pcf->setDescription($data['description']);
		$pcf->setLevel($data['level']);

		//Check to make sure the currently logged in user is NEVER in the unassigned
		//user list. This prevents an administrator from accidently un-assigning themselves
		//from a group and losing all permissions.
		if ( in_array( $current_user->getId(), (array)$src_user_id) ) {
			//Check to see if current user is assigned to another permission group.
			$current_user_failed = FALSE;

			$pclf = new PermissionControlListFactory();
			$pclf->getByCompanyIdAndUserId( $current_company->getId(), $current_user->getId() );
			if ( $pclf->getRecordCount() == 0 ) {
				$current_user_failed = TRUE;
			} else {
				foreach( $pclf->rs as $pc_obj ) {
					$pclf->data = (array)$pc_obj;
					$pc_obj = $pclf;

					if ( $pc_obj->getId() == $data['id'] ) {
						$current_user_failed = TRUE;
					}
				}

			}
			unset($pclf, $pc_obj);

			if ( $current_user_failed == TRUE ) {
				$pcf->Validator->isTrue( 'user',
										FALSE,
										_('You can not unassign yourself from a permission group, assign yourself to a new group instead') );
			}
		}

		if ( $pcf->isValid() ) {
			$pcf_id = $pcf->Save(FALSE);

			Debug::Text('aPermission Control ID: '. $pcf_id , __FILE__, __LINE__, __METHOD__,10);

			if ( $pcf_id === TRUE ) {
				$pcf_id = $data['id'];
			}

			if ( DEMO_MODE == FALSE ) {
				if ( isset($data['user_ids']) ){
					$pcf->setUser( $data['user_ids'] );
				} else {
					$pcf->setUser( array() );
				}

				//Don't Delete all previous permissions, do that in the Permission class.
				if ( isset($data['permissions']) AND is_array($data['permissions']) AND count($data['permissions']) > 0 ) {
					$pcf->setPermission( $data['permissions'], $old_data['permissions']);
				}
			}

			if ( $pcf->isValid() ) {
				$pcf->Save(TRUE);

				if ( DEMO_MODE == FALSE ) {
					if ( $action == 'apply_preset' ) {
						Debug::Text('Attempting to apply preset...', __FILE__, __LINE__, __METHOD__,10);

						if ( !isset($data['preset_flags']) ) {
							$data['preset_flags'] = array();
						}

						if ( $pcf_id != '' AND isset($data['preset']) ) {
							Debug::Text('Applying Preset!', __FILE__, __LINE__, __METHOD__,10);
							$pf = new PermissionFactory();
							$pf->applyPreset($pcf_id, $data['preset'], $data['preset_flags']);
						}
					}
				}
				//$pcf->FailTransaction();
				$pcf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( array(), 'PermissionControlList.php') );
			}
		}

		$pcf->FailTransaction();
	}

}


?>