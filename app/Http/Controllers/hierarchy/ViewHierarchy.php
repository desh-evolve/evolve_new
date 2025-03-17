<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: ViewHierarchy.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('hierarchy','enabled')
		OR !( $permission->Check('hierarchy','view') OR $permission->Check('hierarchy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'View Hierarchy')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'hierarchy_id',
												'id'
												) ) );

switch ($action) {
	default:
		if ( isset($id) ) {

			$hlf = new HierarchyListFactory();

			$tmp_id = $id;
			$i=0;
			do {
				Debug::Text(' Iteration...', __FILE__, __LINE__, __METHOD__,10);
				$parents = $hlf->getParentLevelIdArrayByHierarchyControlIdAndUserId( $hierarchy_id, $tmp_id);

				$level = $hlf->getFastTreeObject()->getLevel( $tmp_id )-1;

				if ( is_array($parents) AND count($parents) > 0 ) {
					$parent_users = array();
					foreach($parents as $user_id) {
						//Get user information
						$ulf = new UserListFactory();
						$ulf->getById( $user_id );
						$user = $ulf->getCurrent();
						unset($ulf);

						$parent_users[] = array( 'name' => $user->getFullName() );
						unset($user);
					}

					$parent_groups[] = array( 'users' => $parent_users, 'level' => $level );
					unset($parent_users);
				}

				if ( isset($parents[0]) ) {
					$tmp_id = $parents[0];
				}
				
				$i++;
			} while ( is_array($parents) AND count($parents) > 0 AND $i < 100 );
		}

		$smarty->assign_by_ref('parent_groups', $parent_groups);

		break;
}
$smarty->display('hierarchy/ViewHierarchy.tpl');
?>