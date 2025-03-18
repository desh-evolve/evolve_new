<?php

namespace App\Models\Core;

use App\Models\Hierarchy\HierarchyListFactory;

class Permission {
	function getPermissions( $user_id, $company_id ) {

		$plf = new PermissionListFactory();

		$cache_id = 'permission_all'.$user_id.$company_id;
		$perm_arr = $plf->getCache($cache_id);
		//Debug::Arr($perm_arr, 'Cached Perm Arr:', __FILE__, __LINE__, __METHOD__,9);
		if ( $perm_arr === FALSE ) {
			$plf->getAllPermissionsByCompanyIdAndUserId( $company_id, $user_id );
			if ( $plf->getRecordCount() > 0 ) {
				//Debug::Text('Found Permissions in DB!', __FILE__, __LINE__, __METHOD__,9);
				$perm_arr['_system']['last_updated_date'] = NULL;
				foreach($plf->rs as $p_obj) {
					$plf->data = (array)$p_obj;
					//Debug::Text('Perm -  Section: '. $p_obj->getSection(), __FILE__, __LINE__, __METHOD__,9);
					if ( $plf->getUpdatedDate() > $perm_arr['_system']['last_updated_date'] ) {
						$perm_arr['_system']['last_updated_date'] =  $plf->getUpdatedDate();
					}
					$perm_arr[$plf->getSection()][$plf->getName()] = $plf->getValue();
				}
				//Last iteration, grab the permission level.
				$perm_arr['_system']['level'] =  $plf->getColumn('level');

				$plf->saveCache($perm_arr,$cache_id);

				return $perm_arr;
			}
		}

		return $perm_arr;
	}

	function Check($section, $name, $user_id = NULL, $company_id = NULL) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		//Debug::Text('Permission Check - Section: '. $section .' Name: '. $name .' User ID: '. $user_id .' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__,9);
		$permission_arr = $this->getPermissions( $user_id, $company_id );

		if ( isset($permission_arr[$section][$name]) ) {
			//Debug::Text('Permission is Set!', __FILE__, __LINE__, __METHOD__,9);
			$result = $permission_arr[$section][$name];
		} else {
			//Debug::Text('Permission is NOT Set!', __FILE__, __LINE__, __METHOD__,9);
			$result = FALSE;
		}

		return $result;
	}

	function getLevel( $user_id = NULL, $company_id = NULL ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		$permission_arr = $this->getPermissions( $user_id, $company_id );

		if ( isset($permission_arr['_system']['level']) ) {
			return $permission_arr['_system']['level'];
		}

		return 1; //Lowest level.
	}

	function Redirect($result) {
		if ( $result !== TRUE ) {
			Redirect::Page( URLBuilder::getURL( NULL, Environment::getBaseURL().'/permission/PermissionDenied.php') );
		}

		return TRUE;
	}

	function PermissionDenied( $result = FALSE, $description = 'Permission Denied' ) {
		if ( $result !== TRUE ) {
			Debug::Text('Permission Denied! Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
			return APIFactory::returnHandler( FALSE, 'PERMISSION', $description );
		}

		return TRUE;
	}

	function Query($section, $name, $user_id = NULL, $company_id = NULL) {
		Debug::Text('Permission Query!' , __FILE__, __LINE__, __METHOD__,9);
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		$plf = new PermissionListFactory();

		return $plf->getBySectionAndNameAndUserIdAndCompanyId($section, $name, $user_id, $company_id)->getCurrent();
	}

	//Checks if the row_object_id is created by the current user
	function isOwner( $object_created_by, $object_assigned_to = NULL, $current_user_id = NULL ) {
		if ( $current_user_id == NULL OR $current_user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$current_user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( ($object_created_by != '' AND $object_created_by == $current_user_id)
				OR ($object_assigned_to != '' AND $object_assigned_to == $current_user_id) ) {
			return TRUE;
		}

		return FALSE;
	}

	//Checks if the row_object_id is in the src_object_list array,
	function isChild( $row_object_id, $src_object_list, $current_user_id = NULL ) {
		if ( !is_numeric($row_object_id) ) {
			return FALSE;
		}

		if ( $current_user_id == NULL OR $current_user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$current_user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}
		//Can never be a child of themselves, so remove the current user from the child list.
		if ( $row_object_id == $current_user_id ) {
			return FALSE;
		}

		if ( !is_array($src_object_list) AND $src_object_list != '' ) {
			$src_object_list = array( $src_object_list );
		}

		if ( is_array($src_object_list) AND in_array( $row_object_id, $src_object_list ) ) {
			return TRUE;
		}

		return FALSE;
	}

	function getPermissionHierarchyChildren( $company_id, $user_id ) {
		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $company_id, $user_id, 100 );
		//Debug::Arr($permission_children_ids, 'Permission Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		return $permission_children_ids;
	}

	function getPermissionChildren($section, $name, $user_id = NULL, $company_id = NULL) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		$permission_children_ids = $this->getPermissionHierarchyChildren( $company_id, $user_id );
		if ( $this->Check( $section, $name ) == FALSE ) {
			if ( $this->Check( $section, $name.'_child') ) {
				$retarr = $permission_children_ids;
			}
			//Why are we including the current user in the "child" list, if they can view their own records.
			//This essentially makes edit_child permissions include edit_own as well. Which for the editing punches
			//there may be cases where they can edit subordinates but not themselves.
			//Because in the SQL query, we restrict to just the child_ids.
			//Its different view view_own/view_child as compared to edit_own/edit_child.
			//  So we need to include the current user if they can only view their own, but exclude the current user when doing is_child checks above.
			//Another way we could handle this is to return an array of children and owner separately, then in SQL queries combine them together.
			if ( $this->Check( $section, $name.'_own') ) {
				$retarr[] = $user_id;
			}
		} else {
			$retarr = NULL;
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return NULL;
	}

	function getLastUpdatedDate( $user_id = NULL, $company_id = NULL ) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( isset($current_user) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		//Debug::Text('Permission Check - Section: '. $section .' Name: '. $name .' User ID: '. $user_id .' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__,9);
		$permission_arr = $this->getPermissions( $user_id, $company_id );

		if ( isset($permission_arr['_system']['last_updated_date']) ) {
			return $permission_arr['_system']['last_updated_date'];
		}

		return FALSE;
	}
        
        
        
               
        
        
        function getPermissionFilterData($section, $name, $user_id = NULL, $company_id = NULL) {
		//Use Cache_Lite class once we need performance.
		if ( $user_id == NULL OR $user_id == '') {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		if ( $company_id == NULL OR $company_id == '') {
			global $current_company;
			$company_id = $current_company->getId();
		}

		/*
			permission_children_ids
			permission_current_user_id
			permission_is_child = 1
			permission_is_own = 1
		*/

		$retarr['permission_current_user_id'] = $user_id;
		if ( $this->Check( $section, $name ) == FALSE ) {
			if ( $this->Check( $section, $name.'_child') ) {
				$retarr['permission_is_child'] = TRUE;
			}
			if ( $this->Check( $section, $name.'_own') ) {
				$retarr['permission_is_own'] = TRUE; //Return user_id so we can match that specifically
			}
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return array();
	}

}
?>
