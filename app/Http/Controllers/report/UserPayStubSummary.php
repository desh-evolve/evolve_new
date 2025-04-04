<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: UserPayStubSummary.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_employee_pay_stub_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Employee Pay Stub Summary')); // See index.php
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
												'user_ids',
												'show_ytd'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );


switch ($action) {
	case 'Submit':
	default:


		$psenlf = new PayStubEntryNameListFactory();
		$ulf = new UserListFactory();

		if ( !isset($user_ids) OR $user_ids == '' OR $user_ids[0] == 0) {
			//$user_ids = array_keys( $ulf->getByCompanyIdArray( $current_company->getId() ) );
			$user_ids = array( $current_user->getId() );
		}

		//Get all pay stubs for this pay period
		$pslf = new PayStubListFactory();

		$pslf->getByUserIdAndCompanyId( $user_ids, $current_company->getId(), NULL, NULL, array('advance' => '= \'f\''), array('user_id' => 'asc', 'pay_period_id' => 'asc') );
		$pager = new Pager($pslf);

		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $current_company->getId() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();


			$entry_name_ids = array(	$pseal_obj->getTotalGross(),
										$pseal_obj->getMonthlyAdvance(),
										$pseal_obj->getTotalEmployeeDeduction(),
										$pseal_obj->getTotalEmployerDeduction(),
										$pseal_obj->getTotalNetPay(),
										$pseal_obj->getMonthlyAdvanceDeduction(),
										$pseal_obj->getMonthlyAdvanceDeduction(),
										$pseal_obj->getVacationAccrual()

									);

			//array(10,11,18,22,23,24,25);
		} else {
			$entry_name_ids = array();
		}
		//var_dump($entry_name_ids);

		$prev_user = NULL;
		$prev_entries = NULL;
		foreach($pslf as $pay_stub_obj) {
			//Debug::text(' Pay Stub ID: '. $pay_stub_obj->getId() , __FILE__, __LINE__, __METHOD__,10);

			$pself = new PayStubEntryListFactory();
			//Order is very important here. We want the "last" entries to go last, as they should
			//have the most up to date YTD values.
			$pself->getByPayStubId( $pay_stub_obj->getId() );

			$entries = NULL;

			foreach ($pself as $pay_stub_entry_obj) {

				$pay_stub_entry_name_obj = $psenlf->getById( $pay_stub_entry_obj->getPayStubEntryNameId() )->getCurrent();

				Debug::Text('Pay Stub Entry Account ID: '. $pay_stub_entry_obj->getPayStubEntryNameId(), __FILE__, __LINE__, __METHOD__,10);

				if ( in_array( $pay_stub_entry_obj->getPayStubEntryNameId(), $entry_name_ids ) ) {
					Debug::text(' Found valid entry name ID: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $pay_stub_entry_obj->getAmount() , __FILE__, __LINE__, __METHOD__,10);

					if (  $show_ytd == 1 ) {
						$amount = $pay_stub_entry_obj->getYTDAmount();
					} else {
						$amount = $pay_stub_entry_obj->getAmount();
					}

					if ( $show_ytd == 1 ) {
						$entries[$pay_stub_entry_name_obj->getName()] = $amount;
					} else {
						//When we're not showing YTD, we have to add up all the entries, as there
						//could be two or more of the same name.
						if ( isset($entries[$pay_stub_entry_name_obj->getName()]) ) {
							//Debug::text(' Adding amount: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $amount , __FILE__, __LINE__, __METHOD__,10);
							$entries[$pay_stub_entry_name_obj->getName()] += $amount;
							$entries[$pay_stub_entry_name_obj->getName()] = number_format($entries[$pay_stub_entry_name_obj->getName()], 2, '.','');
							//Debug::text(' Final amount: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $entries[$pay_stub_entry_name_obj->getName()] , __FILE__, __LINE__, __METHOD__,10);
						} else {
							//Debug::text(' Setting amount: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $amount , __FILE__, __LINE__, __METHOD__,10);
							$entries[$pay_stub_entry_name_obj->getName()] = $amount;
						}
					}

					unset($amount);
				} else {
					//Debug::text(' INVALID entry name ID: '. $pay_stub_entry_obj->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);
				}

			}

			$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();

			if ( $entries !== NULL ) {
				//Debug::text('Entries is not null', __FILE__, __LINE__, __METHOD__,10);

				if ( $prev_user != $user_obj->getId() ) {
					$prev_entries = NULL;
				}

				//Do this so pay periods with both advanc, and full pay stubs only show the full pay stub.
				$tmp_rows[] = array(
							'id' => $pay_stub_obj->getId(),
							'pay_period_id' => $pay_stub_obj->getPayPeriod(),
							'pay_period_name' => $pay_stub_obj->getPayPeriodObject()->getName(TRUE),
							'pay_period_transaction_date' => $pay_stub_obj->getPayPeriodObject()->getTransactionDate(),
							'user_id' => $pay_stub_obj->getUser(),
							'last_name' => $user_obj->getLastName(),
							'full_name' => $user_obj->getFullName(),
							'entries' => $entries,
							'prev_entries' => $prev_entries
							);

			}

			$prev_user = $user_obj->getId();
			$prev_entries = $entries;

		}

		$red_alert_deviation = 20; //Percent
		$yellow_alert_deviation = 10; //Percent


		if ( isset($tmp_rows) ) {
			foreach($tmp_rows as $row) {
				//Calc percent deviation here.
				foreach( $row['entries'] as $key => $value ) {
					if ( !isset($row['prev_entries'][$key])
							OR $row['prev_entries'] == NULL
							OR $value == $row['prev_entries'][$key]
						) {
						$row['percent_deviation'][$key] = array('deviation' => '100.00', 'alert' => FALSE) ;
					} else {
						$deviation = ($value / $row['prev_entries'][$key]) * 100;

						if ( $deviation >= ( 100 + $red_alert_deviation )
								OR $deviation <= ( 100 - $red_alert_deviation ) ) {
							$alert = 'red';
						} elseif ( $deviation >= ( 100 + $yellow_alert_deviation )
								OR $deviation <= ( 100 - $yellow_alert_deviation ) ) {
							$alert = 'yellow';
						} else {
							$alert = FALSE;
						}

						$row['percent_deviation'][$key] = array( 'deviation' => number_format( $deviation, 2, '.', ''), 'alert' => $alert );
						//$row['percent_deviation'][$key] = number_format( $deviation, 2, '.', '');
					}
					unset($deviation, $alert);
				}

				$rows[] = $row;
			}

			$rows = Sort::Multisort($rows, 'last_name', 'pay_period_transaction_date', 'DESC');
		}

		//Since the array order matters for this opertation, we have to do it last, after
		//all ordering as been done.
		if ( isset($rows ) ) {
			$i=0;
			$prev_user = NULL;
			foreach($rows as $row) {
				if ( $row['user_id'] != $prev_user ) {
					$rows[$i]['user_changed'] = TRUE;
				} else {
					$rows[$i]['user_changed'] = FALSE;
				}

				$prev_user = $row['user_id'];
				$i++;
			}

			$smarty->assign_by_ref('rows', $rows );
		}
		$user_options = $ulf->getByCompanyIdArray($current_company->getId(), TRUE);

		$smarty->assign_by_ref('user_options', $user_options);
		$smarty->assign_by_ref('user_ids', $user_ids);

		$smarty->assign_by_ref('show_ytd', $show_ytd);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('report/UserPayStubSummary.tpl');
?>