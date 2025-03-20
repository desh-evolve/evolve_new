<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: CompanyDeductionList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('company_tax_deduction','enabled')
		OR !( $permission->Check('company_tax_deduction','view') OR $permission->Check('company_tax_deduction','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Tax / Deduction List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'ids',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
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
	case 'add_presets':
		//Debug::setVerbosity(11);
		CompanyDeductionFactory::addPresets( $current_company->getId() );

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditCompanyDeduction.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$cdlf = new CompanyDeductionListFactory();

		foreach ($ids as $id) {
			$cdlf->getByCompanyIdAndId($current_company->getId(), $id );
			foreach ($cdlf as $cd_obj) {
				$cd_obj->setDeleted($delete);
				if ( $cd_obj->isValid() ) {
					$cd_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );

		break;
	case 'copy':
		$cdlf = new CompanyDeductionListFactory();

		foreach ($ids as $id) {
			$cdlf->getByCompanyIdAndId($current_company->getId(), $id );
			foreach ($cdlf as $cd_obj) {
				$tmp_cd_obj = clone $cd_obj;

				$tmp_cd_obj->setId( FALSE );
				$tmp_cd_obj->setName( Misc::generateCopyName( $cd_obj->getName() )  );
				if ( $tmp_cd_obj->isValid() ) {
					$tmp_cd_obj->Save( FALSE );

					$tmp_cd_obj->setIncludePayStubEntryAccount( $cd_obj->getIncludePayStubEntryAccount() );
					$tmp_cd_obj->setExcludePayStubEntryAccount( $cd_obj->getExcludePayStubEntryAccount() );
					$tmp_cd_obj->setUser( $cd_obj->getUser() );

					if ( $tmp_cd_obj->isValid() ) {
						$tmp_cd_obj->Save();
					}
				}
			}
		}
		unset($tmp_cd_obj, $cd_obj);

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );

		break;
	default:
		BreadCrumb::setCrumb($title);

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$cdlf = new CompanyDeductionListFactory();
		$cdlf->getByCompanyId( $current_company->getId(), NULL, $sort_array );

		$pager = new Pager($cdlf);

		$status_options = $cdlf->getOptions('status');
		$type_options = $cdlf->getOptions('type');
		$calculation_options = $cdlf->getOptions('calculation');

		foreach ($cdlf as $cd_obj) {

			$rows[] = array(
								'id' => $cd_obj->getId(),
								'status_id' => $cd_obj->getStatus(),
								'status' => $status_options[$cd_obj->getStatus()],
								'type_id' => $cd_obj->getType(),
								'type' => $type_options[$cd_obj->getType()],
								'calculation_id' => $cd_obj->getCalculation(),
								'calculation' => $calculation_options[$cd_obj->getCalculation()],
								'calculation_order' => $cd_obj->getCalculationOrder(),
								'name' => $cd_obj->getName(),
								'deleted' => $cd_obj->getDeleted()
							);
		}
                
                // print_r($rows);
               // exit();
                
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('company/CompanyDeductionList.tpl');
?>