<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: HierarchyList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
//$_COOKIE['SessionID'] = '07bc520b3e90835ab586f0c585c4dd8f';
//$_SERVER['REMOTE_ADDR'] = '192.168.1.100';
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('hierarchy','enabled')
		OR !( $permission->Check('hierarchy','view') OR $permission->Check('hierarchy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Hierarchy Tree')); // See index.php


/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'hierarchy_id',
												'ids'
												) ) );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( array('hierarchy_id' => $hierarchy_id) , 'EditHierarchy.php') );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		foreach ($ids as $id) {
			Debug::Text(' Deleting ID: '. $id , __FILE__, __LINE__, __METHOD__,10);

	        $hf = TTnew( 'HierarchyListFactory' );
			$hf->setUser( $id );
			$hf->setHierarchyControl( $hierarchy_id );
			$hf->Delete();
		}

		//FIXME: Get parent ID of each node we're deleting and clear the cache based on the hierarchy_id and it instead
		if ( isset($hf) AND is_object($hf) ) {
			$hf->removeCache( NULL, $hf->getTable(TRUE) ); //On delete we have to delete the entire group.
		}
		unset($hf);

		Redirect::Page( URLBuilder::getURL( array('hierarchy_id' => $hierarchy_id) , 'HierarchyList.php') );

		break;

	default:
		BreadCrumb::setCrumb($title);

		$hlf = TTnew( 'HierarchyListFactory' );
		//$nodes = $hlf->FormatArray( $hlf->getByHierarchyControlId( $hierarchy_id ), 'HTML' );
		//$nodes = FastTree::FormatArray( $hlf->getByHierarchyControlId( $hierarchy_id ), 'HTML' );
		$nodes = FastTree::FormatArray( $hlf->getByCompanyIdAndHierarchyControlId( $current_company->getId(), $hierarchy_id ), 'HTML' );


		//For some reason smarty prints out a blank row if nodes is false.
		if ( $nodes !== FALSE ) {
			$smarty->assign_by_ref('users', $nodes);
		}

		break;
}
$smarty->assign_by_ref('hierarchy_id', $hierarchy_id);

$smarty->display('hierarchy/HierarchyList.tpl');
?>