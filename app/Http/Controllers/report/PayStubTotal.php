<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4600 $
 * $Id: PayStubSummary.php 4600 2011-04-28 21:35:12Z ipso $
 * $Date: 2011-04-28 14:35:12 -0700 (Thu, 28 Apr 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_pay_stub_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}
$smarty->assign('title', TTi18n::gettext($title = 'Pay Stub Summary Report'));  // See index.php


/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );

$static_columns = array(
							'-0800-epf_membership_no' => TTi18n::gettext('EPF  #'),
							'-0801-full_name' => TTi18n::gettext('Full Name'),
							'-0802-basic_for_epf' => TTi18n::gettext('Basic for EPF'),
							'-0900-first_name' => TTi18n::gettext('First Name'),
							'-0900-name_initial' => TTi18n::gettext('Name with Initials'),
							'-0901-middle_name' => TTi18n::gettext('Middle Name'),
							'-0902-middle_initial' => TTi18n::gettext('Middle Initial'),
							'-0903-last_name' => TTi18n::gettext('Last Name'),
							'-1000-full_name' => TTi18n::gettext('Full Name'),
							'-1002-employee_number' => TTi18n::gettext('Employee #'),
							'-1010-title' => TTi18n::gettext('Title'),
							'-1020-province' => TTi18n::gettext('Province/State'),
							'-1030-country' => TTi18n::gettext('Country'),
							'-1039-group' => TTi18n::gettext('Group'),
							'-1040-default_branch' => TTi18n::gettext('Default Branch'),
							'-1050-default_department' => TTi18n::gettext('Default Department'),
							'-1060-sin' => TTi18n::gettext('SIN/SSN'),
							'-1065-birth_date' => TTi18n::gettext('Birth Date'),
							'-1070-hire_date' => TTi18n::gettext('Appointment Date'),
							'-1080-since_hire_date' => TTi18n::gettext('Since Hired'),
							'-1085-termination_date' => TTi18n::gettext('Termination Date'),
							'-1086-institution' => TTi18n::gettext('Bank Institution'),
							'-1087-transit' => TTi18n::gettext('Bank Transit/Routing'),
							'-1089-account' => TTi18n::gettext('Bank Account'),
							'-1090-pay_period' => TTi18n::gettext('Pay Period'),
							'-1100-pay_stub_start_date' => TTi18n::gettext('Start Date'),
							'-1110-pay_stub_end_date' => TTi18n::gettext('End Date'),
							'-1120-pay_stub_transaction_date' => TTi18n::gettext('Transaction Date'),
							'-1130-currency' => TTi18n::gettext('Currency'),
							'-1131-current_currency' => TTi18n::gettext('Current Currency'),
							'-1132-epf_20_persent' => TTi18n::gettext('E.P.F - 20%'),
							);

$psealf = new PayStubEntryAccountListFactory();
$psen_columns = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,60,65), FALSE );

$columns = Misc::prependArray( $static_columns, $psen_columns);

$default_transaction_start_date = TTDate::getBeginMonthEpoch( time() );
$default_transaction_end_date = TTDate::getEndMonthEpoch( time() );

//Get all pay periods
$pplf = new PayPeriodListFactory();
$pplf->getPayPeriodsWithPayStubsByCompanyId( $current_company->getId() );
$pay_period_options = array();
if ( $pplf->getRecordCount() > 0 ) {
	$pp=0;
	foreach ($pplf as $pay_period_obj) {
		$pay_period_ids[] = $pay_period_obj->getId();
		$pay_period_end_dates[$pay_period_obj->getId()] = $pay_period_obj->getEndDate();

		if ( $pp == 0 ) {
			$default_transaction_start_date = $pay_period_obj->getEndDate();
			$default_transaction_end_date = $pay_period_obj->getTransactionDate()+86400;
		}
		$pp++;
	}
	$pplf = new PayPeriodListFactory();
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
}

if ( isset($filter_data['transaction_start_date']) ) {
	$filter_data['transaction_start_date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime($filter_data['transaction_start_date']) );
}

if ( isset($filter_data['transaction_end_date']) ) {
	$filter_data['transaction_end_date'] = TTDate::getEndDayEpoch( TTDate::parseDateTime($filter_data['transaction_end_date']) );
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'currency_ids', 'pay_period_ids', 'column_ids' ), array() );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('pay_stub','view') == FALSE ) {
	$hlf = new HierarchyListFactory();
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('pay_stub','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('pay_stub','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'view_pay_stubs':
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);

		Debug::Text('Submit! Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'aFilter Data', __FILE__, __LINE__, __METHOD__,10);

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
                
                $pplf = new PayPeriodListFactory();
                $pplf->getByCompanyIdAndEndDate($current_company->getId(),$filter_data['transaction_end_date']);
                
                if($pplf->getRecordCount() > 0){
                    
                    $pp_obj = $pplf->getCurrent();
                    
                   
                    
                      $pslf = new PayStubListFactory();
                      $pslf->getByCompanyIdAndPayPeriodId($current_company->getId(), $pp_obj->getId());
                      
                      if($pslf->getRecordCount() >0){
                          
                          $ps_obj = $pslf->getCurrent();
                          
                          $pself = new PayStubEntryListFactory();
                         $sum_data = $pself->getSumByPayStubIdAndType($ps_obj->getId(),30);
                         
                         print_r($sum_data);
                         
                         exit();
                          
                          
                      }
                      
                }
                
              
                
                $cdlf = new CompanyDeductionListFactory();
                $cdlf->getByCompanyIdAndStatusId($current_company->getId(),30);
                
                if($cdlf->getRecordCount() >0){
                    
                    foreach ($cdlf as $cdf_obj){
                        
                        
                        
                    }
                }
                
	
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);
	default:
		BreadCrumb::setCrumb($title);

		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);
			extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );
		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);
				//Default selections
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				//$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys((array)$pay_period_options)) );
				$filter_data['transaction_start_date'] = $default_transaction_start_date;
				$filter_data['transaction_end_date'] = $default_transaction_end_date;
				$filter_data['group_ids'] = array( -1 );
				$filter_data['currency_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );
				//$filter_data['primary_group_by'] = '-1000-full_name';

				$default_columns = array( 0 => '-0800-epf_membership_no' ,
                                                          1 => '-1000-full_name', 
                                                          2 => '49', 
                                                          3 => '45', 
                                                          4 => '-0802-basic_for_epf', 
                                                          5 => '9', 
                                                          6 => '20', 
                                                          7 => '8', 
                                                          8 => '29', 
                                                          9 => '12', 
                                                          10 => '13', 
                                                          11 => '10', 
                                                          12 => '-1132-epf_20_persent', 
                                                          13 => '18', 
                                                          14 => '75' );

				$pseallf = new PayStubEntryAccountLinkListFactory();
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();

//					$default_linked_columns = array(
//												$pseal_obj->getTotalGross(),
//												$pseal_obj->getTotalNetPay(),
//												$pseal_obj->getTotalEmployeeDeduction(),
//												$pseal_obj->getTotalEmployerDeduction() );
				} else {
					$default_linked_columns = array();
				}

				$filter_data['column_ids'] = Misc::prependArray( $default_columns, $default_linked_columns );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1120-pay_stub_transaction_date';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'currency_ids', 'column_ids' ), NULL );

		$ulf = new UserListFactory();
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

		//Get include employee list.
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );

		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		$uglf = new UserGroupListFactory();
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		//Get employee titles
		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get pay periods
		//$pplf = new PayPeriodListFactory();
		//$pplf->getPayPeriodsWithPayStubsByCompanyId( $current_company->getId() );
		$pay_period_options = Misc::prependArray( $all_array_option, $pplf->getArrayByListFactory( $pplf, FALSE, TRUE ) );
		$filter_data['src_pay_period_options'] = Misc::arrayDiffByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );
		$filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );

		//Get currencies
		$crlf = new CurrencyListFactory();
		$crlf->getByCompanyId( $current_company->getId() );
		$currency_options = Misc::prependArray( $all_array_option, $crlf->getArrayByListFactory( $crlf, FALSE, TRUE ) );
		$filter_data['src_currency_options'] = Misc::arrayDiffByKey( (array)$filter_data['currency_ids'], $currency_options );
		$filter_data['selected_currency_options'] = Misc::arrayIntersectByKey( (array)$filter_data['currency_ids'], $currency_options );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$filter_data['group_by_options'] = Misc::prependArray( array('0' => TTi18n::gettext('No Grouping')), $static_columns );

		$psf = new PayStubFactory();
		//ARSP EDIT --> ADD Some New code('pdf' => TTi18n::gettext('PDF (PDF)')) ) for new 'pdf' dropdown list from export type  
		$filter_data['export_type_options'] = Misc::prependArray( array( 'csv' => TTi18n::gettext('CSV (Excel)'), 'pdfp' => TTi18n::gettext('PDF (PORTRAIT)'), 'pdfl' => TTi18n::gettext('PDF (LANDSCAPE)'), 'formc' => TTi18n::gettext('Form C (PDF)'), 'payslip3' => TTi18n::gettext('3 Payslip/Page (PDF)'), 'payslip4' => TTi18n::gettext('4 Payslip/Page (Landscape PDF)')), Misc::trimSortPrefix( $psf->getOptions('export_type') ) );
		//$filter_data['export_type_options'] = Misc::prependArray( array( 'csv' => TTi18n::gettext('CSV (Excel)') ), Misc::trimSortPrefix( $psf->getOptions('export_type') ) );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/PayStubTotal.tpl');

		break;
}
?>