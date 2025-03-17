<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: UserAccrualBalanceList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('accrual','enabled')
		OR !( $permission->Check('accrual','view') OR $permission->Check('accrual','view_own') OR $permission->Check('accrual','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity( 11 );

$smarty->assign('title', TTi18n::gettext($title = 'Census Information')); // See index.php
BreadCrumb::setCrumb($title);
/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'filter_user_id',
												'ids',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_user_id' => $filter_user_id,
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':
		Redirect::Page( URLBuilder::getURL( NULL, 'editCensus.php') );
		break;
	default:
		$ucilf = new UserCensusInformationListFactory();
		$ulf = new UserListFactory();

		if ( $permission->Check('user','view') OR $permission->Check('user','view_child') ) {
			if ( isset($filter_user_id) ) {
				$user_id = $filter_user_id;
			} else {
				$user_id = $current_user->getId();
				$filter_user_id = $current_user->getId();
			}
		} else {
			$filter_user_id = $user_id = $current_user->getId();
		}

		$filter_data = NULL;

		//Get user object
		$ulf->getByIdAndCompanyID( $user_id, $current_company->getId() );
		if (  $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

			$ucilf->getByUserIdAndCompanyId( $user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

			$pager = new Pager($ablf);

			

			foreach ($ucilf as $ucif_obj) {
                            
                           
                           
				$censuses[] = array(
									'id' => $ucif_obj->getId(),
									'user_id' => $ucif_obj->getUser(),
                                                                        'dependant' => $ucif_obj->getDependant(),
                                                                        'name' => $ucif_obj->getName(),
                                                                        'relationship' => $ucif_obj->getRelationship(),
                                                                        'dob' => $ucif_obj->getBirthDate(),
                                                                        'nic' => $ucif_obj->getNic(),
                                                                        'gender' => $ucif_obj->getGender(),
									
								);
			}

			$smarty->assign_by_ref('censuses', $censuses);

			$hlf = new HierarchyListFactory();
			$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
			if ( $permission->Check('accrual','view') == FALSE ) {
				if ( $permission->Check('user','view_child') ) {
					$filter_data['permission_children_ids'] = $permission_children_ids;
				}
				if ( $permission->Check('user','view_own') ) {
					$filter_data['permission_children_ids'][] = $current_user->getId();
				}
			}

			$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
			$smarty->assign_by_ref('user_options', $user_options);

			$smarty->assign_by_ref('filter_user_id', $filter_user_id);
			$smarty->assign('is_owner', $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() ) );
			$smarty->assign('is_child', $permission->isChild( $user_obj->getId(), $permission_children_ids ) );

			$smarty->assign_by_ref('sort_column', $sort_column );
			$smarty->assign_by_ref('sort_order', $sort_order );

			$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );
		}

		break;
}
$smarty->display('users/censusinfo.tpl');
?>