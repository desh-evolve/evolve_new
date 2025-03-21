<?php

namespace App\Http\Controllers\hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyControlFactory;
use App\Models\Hierarchy\HierarchyControlListFactory;
use App\Models\Hierarchy\HierarchyLevelFactory;
use App\Models\Hierarchy\HierarchyLevelListFactory;
use App\Models\Hierarchy\HierarchyObjectTypeListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditHierarchyControl extends Controller
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

        /*
        if ( !$permission->Check('hierarchy','enabled')
				OR !( $permission->Check('hierarchy','edit') OR $permission->Check('hierarchy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
    }

    public function index() {

        $viewData['title'] = 'Edit Hierarchy List';
		$hcf = new HierarchyControlFactory(); 
		$hlf = new HierarchyLevelFactory();

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'ids',
				'hierarchy_control_id',
				'hierarchy_control_data',
				'hierarchy_level_data'
			) 
		) );

		if ( isset($hierarchy_control_id) ) {

			$hclf = new HierarchyControlListFactory();
			$hclf->getByIdAndCompanyId($hierarchy_control_id, $current_company->getId() );

			foreach ($hclf->rs as $hierarchy_control) {
				$hclf->data = (array)$hierarchy_control;
				$hierarchy_control = $hclf;

				$hierarchy_control_data = array(
					'id' => $hierarchy_control->getId(),
					'name' => $hierarchy_control->getName(),
					'description' => $hierarchy_control->getDescription(),
					'object_type_ids' => $hierarchy_control->getObjectType(),
					'user_ids' => $hierarchy_control->getUser(),
					'created_date' => $hierarchy_control->getCreatedDate(),
					'created_by' => $hierarchy_control->getCreatedBy(),
					'updated_date' => $hierarchy_control->getUpdatedDate(),
					'updated_by' => $hierarchy_control->getUpdatedBy(),
					'deleted_date' => $hierarchy_control->getDeletedDate(),
					'deleted_by' => $hierarchy_control->getDeletedBy()
				);
			}

			$hllf = new HierarchyLevelListFactory(); 
			$hllf->getByHierarchyControlId( $hierarchy_control_id );
			if ( $hllf->getRecordCount() > 0 ) {
				foreach( $hllf->rs as $hl_obj ) {
					$hllf->data = (array)$hl_obj;
					$hl_obj = $hllf;

					$hierarchy_level_data[] = array(
						'id' => $hl_obj->getId(),
						'level' => $hl_obj->getLevel(),
						'user_id' => $hl_obj->getUser(),
					);
				}
			} else {
				$hierarchy_level_data[-1] = array(
					'id' => -1,
					'level' => 1,
				);
			}
		} elseif ( $action == 'add_level' ) {
			Debug::Text('Adding Blank Level', __FILE__, __LINE__, __METHOD__,10);
			if ( !isset($hierarchy_level_data) OR ( isset($hierarchy_level_data) AND !is_array( $hierarchy_level_data ) ) ) {
				//If they delete all weeks and try to add a new one.
				$hierarchy_level_data[0] = array(
					'id' => -1,
					'level' => 0,
				);

				$row_keys = array_keys($hierarchy_level_data);
				sort($row_keys);

				$next_blank_id = 0;
				$lowest_id = 0;
			} else {
				$row_keys = array_keys($hierarchy_level_data);
				sort($row_keys);

				Debug::Text('Lowest ID: '. $row_keys[0], __FILE__, __LINE__, __METHOD__,10);
				$lowest_id = $row_keys[0];
				if ( $lowest_id < 0 ) {
					$next_blank_id = $lowest_id-1;
				} else {
					$next_blank_id = -1;
				}
			}

			Debug::Text('Next Blank ID: '. $next_blank_id, __FILE__, __LINE__, __METHOD__,10);

			//Find next level
			$last_new_level = $hierarchy_level_data[$row_keys[0]]['level'];
			$last_saved_level = $hierarchy_level_data[array_pop($row_keys)]['level'];
			Debug::Text('Last New level: '. $last_new_level .' Last Saved level: '. $last_saved_level, __FILE__, __LINE__, __METHOD__,10);
			if ( $last_new_level > $last_saved_level) {
				$last_level = $last_new_level;
			} else {
				$last_level = $last_saved_level;
			}
			Debug::Text('Last level: '. $last_level, __FILE__, __LINE__, __METHOD__,10);

			$hierarchy_level_data[$next_blank_id] = array(
							'id' => $next_blank_id,
							'level' => $last_level+1,
							);
		} elseif ( $action != 'submit' AND $action != 'delete_level' ) {
			//New hierarchy.

			$hierarchy_level_data[-1] = array(
											  'id' => -1,
											  'level' => 1,
											  );
		}

		$prepend_array_option = array( 0 => _('-- Please Choose --') );

		$ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), FALSE, TRUE );

		//Select box options;
		$hotlf = new HierarchyObjectTypeListFactory();
		$hierarchy_control_data['user_options'] = $user_options;
		$hierarchy_control_data['level_user_options'] = Misc::prependArray( $prepend_array_option, $user_options);
		$hierarchy_control_data['object_type_options'] = $hotlf->getOptions('object_type');

		if ( isset($hierarchy_control_data['user_ids']) AND is_array($hierarchy_control_data['user_ids']) ) {
			$tmp_user_options = $user_options;
			foreach( $hierarchy_control_data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}

		$viewData['filter_user_options'] = $filter_user_options;
		$viewData['hierarchy_control_data'] = $hierarchy_control_data;
		$viewData['hierarchy_level_data'] = $hierarchy_level_data;
		$viewData['hcf'] = $hcf;
		$viewData['hlf'] = $hlf;
		
        return view('hierarchy/EditHierarchyControl', $viewData);

    }

	public function submit(){
		$hcf = new HierarchyControlFactory();
		$hlf = new HierarchyLevelFactory();

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'ids',
				'hierarchy_control_id',
				'hierarchy_control_data',
				'hierarchy_level_data'
			) 
		) );

		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$redirect=0;

		$hcf->StartTransaction();

		//Since this class has sub-classes, when creating a new row make sure we have the ID set.
		if ( isset($hierarchy_control_data['id']) AND $hierarchy_control_data['id'] > 0 ) {
			$hcf->setId( $hierarchy_control_data['id'] );
		} else {
			$hcf->setID( $hcf->getNextInsertId() );
		}
		Debug::Text('Hierarchy Control ID: '. $hcf->getID() , __FILE__, __LINE__, __METHOD__,10);

		$hcf->setCompany( $current_company->getId() );

		$hcf->setName($hierarchy_control_data['name']);
		$hcf->setDescription($hierarchy_control_data['description']);

		if ( isset($hierarchy_control_data['object_type_ids']) ) {
			$hcf->setObjectType( $hierarchy_control_data['object_type_ids'] );
		} else {
			$hcf->setObjectType( FALSE );
		}

		if ( isset($hierarchy_control_data['user_ids'] ) ) {
			$hcf->setUser( $hierarchy_control_data['user_ids'] );
		} else {
			$hcf->setUser( array() );
		}

		if ( count($hierarchy_level_data) > 0 ) {
			//ReMap levels
			$hierarchy_level_map = $hlf->ReMapHierarchyLevels( $hierarchy_level_data );
			Debug::Arr($hierarchy_level_map, 'Hierarchy Level Map: ', __FILE__, __LINE__, __METHOD__,10);

			foreach( $hierarchy_level_data as $hierarchy_level_id => $hierarchy_level ) {
				Debug::Text('Row ID: '. $hierarchy_level_id .' Level: '. $hierarchy_level['level'] , __FILE__, __LINE__, __METHOD__,10);

				if ( $hierarchy_level['level'] != '' AND $hierarchy_level['level'] >= 0 AND isset($hierarchy_level_map[$hierarchy_level['level']]) ) {
					if ( $hierarchy_level_id > 0 ) {
						$hlf->setID( $hierarchy_level_id );
					}

					$hlf->setHierarchyControl( $hcf->getID() );
					$hlf->setLevel( $hierarchy_level_map[$hierarchy_level['level']] );
					$hlf->setUser( $hierarchy_level['user_id'] );

					if ( $hlf->isValid() ) {
						Debug::Text('Saving Level Row ID: '. $hierarchy_level_id, __FILE__, __LINE__, __METHOD__,10);
						$hlf->Save();
					} else {
						$redirect++;
					}
				} else {
					//Delete level
					if ( $hierarchy_level_id > 0 ) {
						$hlf->setID( $hierarchy_level_id );
						$hlf->setDeleted(TRUE);
						$hlf->Save();
					} else {
						unset($hierarchy_level_data[$hierarchy_level_id]);
					}
				}
			}
		}

		if ( $redirect == 0 AND $hcf->isValid() ) {
			$hcf->Save( TRUE, TRUE );
			$hcf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( array(), 'HierarchyControlList.php') );
		}
		$hcf->FailTransaction();
	}

	public function delete_level(){
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'ids',
				'hierarchy_control_id',
				'hierarchy_control_data',
				'hierarchy_level_data'
			) 
		) );

		if ( count($ids) > 0) {
			foreach ($ids as $hl_id) {
				if ( $hl_id > 0 ) {
					Debug::Text('Deleting level Row ID: '. $hl_id, __FILE__, __LINE__, __METHOD__,10);

					$hllf = new HierarchyLevelListFactory();
					$hllf->getById( $hl_id );
					if ( $hllf->getRecordCount() == 1 ) {
						foreach($hllf->rs as $hl_obj ) {
							$hllf->data = (array)$hl_obj;
							$hl_obj = $hllf;

							$hl_obj->setDeleted( TRUE );
							if ( $hl_obj->isValid() ) {
								$hl_obj->Save();
							}
						}
					}
				}
				unset($hierarchy_level_data[$hl_id]);

			}
			unset($hl_id);
		}
	}
}


?>