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

if ( !$permission->Check('leaves','enabled')
		OR !( $permission->Check('leaves','view') OR $permission->Check('leaves','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Leave management')); // See index.php

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

		Redirect::Page( URLBuilder::getURL( NULL, 'AbsenceLeaveUserList.php') );
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditAbsenceLeaveUser.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$alulf = TTnew( 'AbsenceLeaveUserListFactory' ); 
		foreach ($ids as $id) {
			$alulf->getById($id);
			foreach ($alulf as $cd_obj) {
				$cd_obj->setDeleted($delete);
				if ( $cd_obj->isValid() ) {
					$cd_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'AbsenceLeaveUserList.php') );

		break;
	case 'copy':
		$cdlf = TTnew( 'CompanyDeductionListFactory' );

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

		$cdlf = TTnew( 'AbsenceLeaveUserListFactory' );
                
                $aplf = TTnew( 'AbsencePolicyListFactory' );
		$cdlf->getAll();

		$pager = new Pager($cdlf);
                
//		$status_options = $cdlf->getOptions('status');
//		$type_options = $cdlf->getOptions('type');
//		$calculation_options = $cdlf->getOptions('calculation');
                
		foreach ($cdlf as $cd_obj) {
                        $aplf->getById($cd_obj->getAbsencePolicyId());
			$rows[] = array(
								'id' => $cd_obj->getId(),
								'status_id' => $cd_obj->getStatus(),
								'status' => $cd_obj->getName(),
								'type_id' => $cd_obj->getName(),
								'type' => $aplf->getCurrent()->getName(),
								'year' => $cd_obj->getLeaveDateYear(),
								'calculation' => $cd_obj->getName(),
								'calculation_order' => $cd_obj->getName(),
								'name' => $cd_obj->getName(),
								'deleted' => $cd_obj->getDeleted()
							);
		}
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('leaves/AbsenceLeaveUserList.tpl');
?>