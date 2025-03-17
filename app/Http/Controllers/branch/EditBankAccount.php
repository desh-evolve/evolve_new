<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5391 $
 * $Id: EditBankAccount.php 5391 2011-10-25 19:53:15Z ipso $
 * $Date: 2011-10-25 12:53:15 -0700 (Tue, 25 Oct 2011) $
 */


/*******************************************************************************
 * ARSP NOTE --> I ADDE THIS CODE FOR THUNDER AND NEON
 * I COPIED THIS CODE FROM evolvepayroll\interface\bank_account\EditBankAccount.php
 * 
 *******************************************************************************/


require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = 'Edit Bank Account')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'company_id',
												'bank_data',
												'data_saved',
                                                                                                'branch_id_new',//ARSP NOTE --> I ADDED THIS NEW CODE FOR THUNDER & NEON. THIS VALUE FROM URL QUERY STRING
                                                                                                'id',//ARSP NOTE --> I ADDED THIS NEW CODE FOR THUNDER & NEON. THIS VALUE FROM URL QUERY STRING
                                                                                                'branch_id_saved'//ARSP NOTE --> I ADDED THIS NEW CODE FOR THUNDER & NEON. THIS VALUE FROM URL QUERY STRING                                                                                                
												) ) );


if ( !$permission->Check('branch','enabled')
		OR !( $permission->Check('branch','edit') OR $permission->Check('branch','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}


//$baf = TTnew( 'BankAccountFactory' );
$baf = TTnew( 'BranchBankAccountFactory' );


$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':

            
                $baf->setId( $bank_data['id'] );
                //$baf->setDefaultBranch($branch_id_new);//$baf->setDefaultBranch( $bank_data['branch_id'] );
                
                /** 
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                if($branch_id_saved == '' OR $branch_id_saved == NULL)
                {
                    $baf->setDefaultBranch($branch_id_new);//$baf->setDefaultBranch( $bank_data['branch_id'] );
                }
                else 
                {
                    $baf->setDefaultBranch($branch_id_saved);//$baf->setDefaultBranch( $bank_data['branch_id'] );
                }



		if ( isset($bank_data['institution']) ) {
			$baf->setInstitution( $bank_data['institution'] );
		}
		$baf->setTransit( $bank_data['transit'] );
		$baf->setAccount( $bank_data['account'] );
                $baf->setBankName( $bank_data['bank_name'] );//ARSP EDIT --> I ADD NEW CODE FOR SET BANK NAME
		$baf->setBankBranch( $bank_data['bank_branch'] );//ARSP EDIT --> I ADD NEW CODE FOR SET BANK NAME

		if ( $baf->isValid() ) {
			$baf->Save();

			Redirect::Page( URLBuilder::getURL( $redirect_arr, Environment::getBaseURL().'/branch/BranchList.php') );

			break;
		} else {
			Debug::Text('Invalid bank data...', __FILE__, __LINE__, __METHOD__,10);
		}
	default:
		$balf = TTnew( 'BranchBankAccountListFactory' );
		//$ulf = TTnew( 'UserListFactory' );

                $balf->getById($id);                
                
		if ( !isset($action) ) {
			BreadCrumb::setCrumb($title);

			foreach ($balf as $bank_account) {
				//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

				$bank_data = array(
									'id' => $bank_account->getId(),
                                                                        'default_branch_id' => $bank_account->getDefaultBranch(),
									'country' => strtolower($country),
									'institution' => $bank_account->getInstitution(),
									'transit' => $bank_account->getTransit(),                                                                        
                                                                        //'account' => $bank_account->getSecureAccount(),//ARSP EDIT --> I Hide THIS CODE REASON- WE CANT SEE ORIGINAL ACCOUNT NUMBER
									'account' => $bank_account->getAccount(),//ARSP EDIT --> I ADD NEW CODE SHOW ONLY ORIGINAL ACCOUNT NUMBER
                                                                        'bank_name' => $bank_account->getBankName(),//ARSP EDIT --> I ADD NEW CODE FOR BANK NAME
                                                                        'bank_branch' => $bank_account->getBankBranch(),//ARSP EDIT --> I ADD NEW CODE FOR BANK BRANCH NAME
									'created_date' => $bank_account->getCreatedDate(),
									'created_by' => $bank_account->getCreatedBy(),
									'updated_date' => $bank_account->getUpdatedDate(),
									'updated_by' => $bank_account->getUpdatedBy(),
									'deleted_date' => $bank_account->getDeletedDate(),
									'deleted_by' => $bank_account->getDeletedBy()
								);
			}
		}
                
                $smarty->assign_by_ref('branch_id_new', $branch_id_new );
                $smarty->assign_by_ref('branch_id_saved', $bank_data['default_branch_id'] );
                
                //Add New
                if($bank_data['default_branch_id'] != '' OR $bank_data['default_branch_id'] != NULL)
                {
                    $blf = TTnew( 'BranchListFactory' );
                    $company_branch_name = $blf->getById( $bank_data['default_branch_id'] )->getCurrent()->getName();                
                    $smarty->assign_by_ref('company_branch_name', $company_branch_name);                    
                }
                
                //Edid Old
                if(isset($branch_id_new))
                {
                    $blf = TTnew( 'BranchListFactory' );
                    $company_branch_name = $blf->getById( $branch_id_new )->getCurrent()->getName();                
                    $smarty->assign_by_ref('company_branch_name', $company_branch_name);                    
                }               
		//var_dump($bank_data);
		$smarty->assign_by_ref('bank_data', $bank_data);
		$smarty->assign_by_ref('data_saved', $data_saved);

		break;            
            
            
            
            
}

$smarty->assign_by_ref('baf', $baf);
//$smarty->assign_by_ref('current_time', TTDate::getDate('TIME') );

$smarty->display('branch/EditBankAccount.tpl');
?>
