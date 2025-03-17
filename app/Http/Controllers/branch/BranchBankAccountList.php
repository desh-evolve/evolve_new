<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4822 $
 * $Id: BranchList.php 4822 2011-06-12 02:25:33Z ipso $
 * $Date: 2011-06-11 19:25:33 -0700 (Sat, 11 Jun 2011) $
 */


/*******************************************************************************
 * ARSP NOTE --> I ADDE THIS CODE FOR THUNDER AND NEON
 * I COPIED THIS CODE FROM thunder\interface\branch\BranchList.php
 *******************************************************************************/


require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('branch','enabled')
		OR !( $permission->Check('branch','view') OR $permission->Check('branch','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Bank Account List') ); // See index.php
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
                                                                                                'id',//ARSP NOTE --> I ADDED THIS NEW CODE FOR THUNDER & NEON. THIS VALUE FROM URL QUERY STRING
                                                                                                'branch_id_new',//ARSP NOTE --> I ADDED THIS NEW CODE FOR THUNDER & NEON. THIS VALUE FROM URL QUERY STRING. THIS VARIABLE WE MUST SET FROM FROM HIDDEN FIELD
                                                                                                'ids'//ARSP NOTE --> I ADDED THIS NEW CODE FOR THUNDER & NEON. THIS VALUE FROM URL QUERY STRING
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page,
                                                                                                        'id' => $id
												) );

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':
                Redirect::Page( URLBuilder::getURL(array('branch_id_new' => $branch_id_new), 'EditBankAccount.php',FALSE));

		break;
	case 'delete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$bbalf = new BranchBankAccountListFactory();

		if ( isset($ids) AND is_array($ids) ) {
			foreach ($ids as $id1) {
				$bbalf->getById($id1);
				foreach ($bbalf as $branch) {
					$branch->setDeleted($delete);
					$branch->Save();
				}
			}
		}

                Redirect::Page( URLBuilder::getURL( $redirect_arr, 'BranchList.php') );

		break;   
	default:
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$bbalf = new BranchBankAccountListFactory();
		$bbalf->getByBranchId($id, $current_user_prefs->getItemsPerPage(),$page, NULL, $sort_array );

		$pager = new Pager($bbalf);

		$branches = array();
		if ( $bbalf->getRecordCount() > 0 ) {
			foreach ($bbalf as $branch) {
				$branches[] = array(
									'id' => $branch->GetId(),
									'transit' => $branch->getTransit(),
									'bank_name' => $branch->getBankName(),
									'bank_branch' => $branch->getBankBranch(),
									'account' => $branch->getAccount(),
									//'deleted' => $branch->getDeleted()
								);
			}
		}
                
                $blf = new BranchListFactory();
                $company_branch_name = $blf->getById( $id )->getCurrent()->getName();                
                $smarty->assign_by_ref('company_branch_name', $company_branch_name);

		$smarty->assign_by_ref('branches', $branches);
                
                
                $smarty->assign_by_ref('branch_id_new', $id );//ARSP NOTE --> IADDED NEW CODE FOR THUNDER & NEON
                
		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
                
		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;      
            
}
$smarty->display('branch/BranchBankAccountList.tpl');
?>