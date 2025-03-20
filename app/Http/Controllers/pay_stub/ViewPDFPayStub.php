<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: ViewPDFPayStub.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('pay_stub','enabled')
		OR !( $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Pay Stub')); // See index.php
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
												'hide_employer_rows',
												'id',
												'ids'
												) ) );

switch ($action) {
	default:
		if ( isset($id) AND !isset($ids) ) {
			$ids = array($id);
		}

		if ( count($ids) > 0 ) {
			$pslf = new PayStubListFactory();
			if ( $permission->Check('pay_stub','view') ) {
				$pslf->getByCompanyIdAndId( $current_company->getId(), $ids);
			} else {
				$pslf->getByUserIdAndId( $current_user->getId(), $ids);
				$hide_employer_rows = TRUE;
			}

			$output = $pslf->getPayStub( $pslf, (bool)$hide_employer_rows );

			if ( Debug::getVerbosity() < 11 ) {
				//$output = $pdf->Output('','S');
				Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($output));
				echo $output;
			}
		}

		break;
}
if ( Debug::getVerbosity() == 11 ) {
	Debug::Display();
}
?>