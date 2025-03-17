<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: ViewUserAccrualList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('accrual','enabled')
		OR !( $permission->Check('accrual','view') OR $permission->Check('accrual','view_own') OR $permission->Check('accrual','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}


$smarty->assign('title', TTi18n::gettext($title = 'Accrual List')); // See index.php
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
												'user_id',
												'accrual_policy_id',
												'ids',
												) ) );

if ( $permission->Check('accrual','view') OR $permission->Check('accrual','view_child')) {
	$user_id = $user_id;
} else {
	$user_id = $current_user->getId();
}

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'user_id' => $user_id,
													'accrual_policy_id' => $accrual_policy_id,
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditUserAccrual.php') );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$alf = TTnew( 'AccrualListFactory' );

		$alf->StartTransaction();
		foreach ($ids as $id) {

			$alf->getById( $id );
			foreach ($alf as $a_obj) {
				//Allow user to delete AccrualPolicy entries, but not Banked/Used entries.
				if ( $a_obj->getUserDateTotalID() == FALSE ) {
					$a_obj->setEnableCalcBalance(FALSE);
					$a_obj->setDeleted($delete);
					if ( $a_obj->isValid() ) {
						$a_obj->Save();
					}
				}
			}
		}

		AccrualBalanceFactory::calcBalance( $user_id, $accrual_policy_id );

		$alf->CommitTransaction();

		Redirect::Page( URLBuilder::getURL( NULL, 'ViewUserAccrualList.php') );

		break;

	default:
		$alf = TTnew( 'AccrualListFactory' );
		$alf->getByCompanyIdAndUserIdAndAccrualPolicyID( $current_company->getId(), $user_id, $accrual_policy_id, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array);

		$pager = new Pager($alf);

		foreach ($alf as $a_obj) {

			$date_stamp = $a_obj->getColumn('date_stamp');
			if ( $date_stamp != '' ) {
				$date_stamp = TTDate::strtotime($date_stamp);
			}
			$accruals[] = array(
								'id' => $a_obj->getId(),
								'user_id' => $a_obj->getUser(),
								'accrual_policy_id' => $a_obj->getAccrualPolicyId(),
								'type_id' => $a_obj->getType(),
								'type' => Option::getByKey( $a_obj->getType(), $a_obj->getOptions('type') ),
								'user_date_total_id' => $a_obj->getUserDateTotalId(),
								'user_date_total_date_stamp' => $date_stamp,
								'time_stamp' => $a_obj->getTimeStamp(),
								'amount' => $a_obj->getAmount()/(8 * 3600),
								'system_type' => $a_obj->isSystemType(),
								'deleted' => $a_obj->getDeleted()
							);

			

		}
		$smarty->assign_by_ref('accruals', $accruals);

		$ulf = TTnew( 'UserListFactory' );
		$user_obj = $ulf->getById( $user_id )->getCurrent();

		$aplf = TTnew( 'AccrualPolicyListFactory' );
		$accrual_policy_obj = $aplf->getById( $accrual_policy_id )->getCurrent();

		$smarty->assign_by_ref('user_id', $user_id);
		$smarty->assign_by_ref('user_full_name', $user_obj->getFullName() );
		$smarty->assign_by_ref('accrual_policy_id', $accrual_policy_id);
		$smarty->assign_by_ref('accrual_policy', $accrual_policy_obj->getName() );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('accrual/ViewUserAccrualList.tpl');
?>