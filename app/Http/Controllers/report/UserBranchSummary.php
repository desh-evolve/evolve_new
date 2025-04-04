<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: UserBranchSummary.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_branch_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Branch Summary')); // See index.php
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
												'pay_period_id',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'pay_period_id' => $pay_period_id,
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

switch ($action) {
	case 'Submit':
	default:

		$pplf = new PayPeriodListFactory();
		$slf = new ShiftListFactory();

		if (!isset($pay_period_id) ) {
			Debug::text(' Pay Period ID NOT SET: '. $pay_period_id , __FILE__, __LINE__, __METHOD__,10);
			$pay_period_id = $pplf->getByCompanyId( $current_company->getId(), 1, 2, NULL, array('start_date' => 'desc') )->getCurrent()->getId();
		}
		Debug::text(' Pay Period ID: '. $pay_period_id , __FILE__, __LINE__, __METHOD__,10);

		$psenlf = new PayStubEntryNameListFactory();
		$ulf = new UserListFactory();
		$blf = new BranchListFactory();

		//Get all pay stubs for this pay period
		$pslf = new PayStubListFactory();
		$pslf->getByPayPeriodId( $pay_period_id, NULL, array('advance' => '= \'f\'') );

		$pager = new Pager($pslf);

		$entry_name_ids = array(10,22);
		foreach($pslf as $pay_stub_obj) {
			Debug::text(' Pay Stub ID: '. $pay_stub_obj->getId() , __FILE__, __LINE__, __METHOD__,10);

			$pself = new PayStubEntryListFactory();
			//Order is very important here. We want the "last" entries to go last, as they should
			//have the most up to date YTD values.
			$pself->getByPayStubId( $pay_stub_obj->getId() );

			$entries = NULL;

			foreach ($pself as $pay_stub_entry_obj) {
				$pay_stub_entry_name_obj = $psenlf->getById( $pay_stub_entry_obj->getPayStubEntryNameId() ) ->getCurrent();

				if ( in_array( $pay_stub_entry_obj->getPayStubEntryNameId(), $entry_name_ids ) ) {
					Debug::text(' Found valid entry name ID: '. $pay_stub_entry_name_obj->getName() .' Amount: '. $pay_stub_entry_obj->getAmount() , __FILE__, __LINE__, __METHOD__,10);

					if (  isset($show_ytd) AND $show_ytd == 1 ) {
						$amount = $pay_stub_entry_obj->getYTDAmount();
					} else {
						$amount = $pay_stub_entry_obj->getAmount();
					}

					if ( isset($show_ytd) AND $show_ytd == 1 ) {
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
					Debug::text(' INVALID entry name ID: '. $pay_stub_entry_obj->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);
				}
			}
			unset($prev_entries);



			if ( $entries !== NULL ) {
				//Do this so pay periods with both advanc, and full pay stubs only show the full pay stub.
				$pay_stub_rows[$pay_stub_obj->getUser()] = array(
							'user_id' => $pay_stub_obj->getUser(),
							'entries' => $entries
							);
			}
		}

		$total_time = 0;

		//Get shift total times for each user/branch
		$slf->getUserBranchTotalTimeByPayPeriodId( $pay_period_id );
		foreach($slf as $user_total_time_obj) {
			//Debug::text(' User ID: '. $user_total_time_obj->getColumn('user_id') .' Branch ID: '. $user_total_time_obj->getColumn('branch_id') .' Total Time: '.$user_total_time_obj->getColumn('branch_total_time') , __FILE__, __LINE__, __METHOD__,10);
			if ( isset($totals['users'][$user_total_time_obj->getColumn('user_id')])) {
				$totals['users'][$user_total_time_obj->getColumn('user_id')] += $user_total_time_obj->getColumn('branch_total_time');
			} else {
				$totals['users'][$user_total_time_obj->getColumn('user_id')] = $user_total_time_obj->getColumn('branch_total_time');
			}

			if ( isset($totals['branches'][$user_total_time_obj->getColumn('branch_id')]) ) {
				$totals['branches'][$user_total_time_obj->getColumn('branch_id')] += $user_total_time_obj->getColumn('branch_total_time');
			} else {
				$totals['branches'][$user_total_time_obj->getColumn('branch_id')] = $user_total_time_obj->getColumn('branch_total_time');
			}

			if ( isset($totals['branches']['total']) ) {
				$totals['branches']['total'] += $user_total_time_obj->getColumn('branch_total_time');
			} else {
				$totals['branches']['total'] = $user_total_time_obj->getColumn('branch_total_time');
			}

			$branch_ids[] = $user_total_time_obj->getColumn('branch_id');
		}
		if ( isset($branch_ids) ) {
			$branch_ids = array_unique($branch_ids);
		}
		//var_dump($totals);

		foreach($slf as $user_total_time_obj) {
			$user_obj = $ulf->getById( $user_total_time_obj->getColumn('user_id') )->getCurrent();
			Debug::text(' User Name: '. $user_obj->getFullName() , __FILE__, __LINE__, __METHOD__,10);

			$user_percent = $user_total_time_obj->getColumn('branch_total_time') / $totals['users'][$user_total_time_obj->getColumn('user_id')];

			if ( isset($pay_stub_rows[$user_total_time_obj->getColumn('user_id')]) ) {
				$user_gross_pay = $pay_stub_rows[$user_total_time_obj->getColumn('user_id')]['entries']['gross_pay'] * $user_percent;
			} else {
				$user_gross_pay = 0;
			}

			$user_entries[$user_total_time_obj->getColumn('branch_id')][] = array(
								'user_id' => $user_total_time_obj->getColumn('user_id'),
								'branch_id' => $user_total_time_obj->getColumn('branch_id'),
								'full_name' => $user_obj->getFullName(),
								'total_time' => $user_total_time_obj->getColumn('branch_total_time'),
								'percent' => $user_percent,
								'percent_display' => round( ($user_percent*100), 2),
								'gross_pay' => number_format($user_gross_pay, 2, '.','')
							);
			unset($user_percent, $user_gross_pay);
		}

		if ( isset($branch_ids) ) {
			foreach($branch_ids as $branch_id) {
				Debug::text(' Branch Done! Branch ID: '. $branch_id, __FILE__, __LINE__, __METHOD__,10);
				$branch_obj = $blf->getById( $branch_id )->getCurrent();
				$branch_percent = $totals['branches'][$branch_id] / $totals['branches']['total'];

				$user_totals = Misc::ArrayAssocSum($user_entries[$branch_id], NULL, 2);

				$user_entries[$branch_id][] = array(
										'full_name' => 'Total',
										'total_time' => $totals['branches'][$branch_id],
										'percent' => $branch_percent*100,
										'percent_display' => round( ($branch_percent*100),2),
										'gross_pay' => number_format($user_totals['gross_pay'], 2, '.','')
										);

				$rows[] = array(
																		'id' => $branch_id,
																		'name' => $branch_obj->getName(),
																		'percent' => $branch_percent,
																		'percent_display' => round( ($branch_percent*100),2),
																		'users' => $user_entries[$branch_id]
																					);

				unset($branch_obj, $branch_percent, $user_totals);

			}
		}
		unset($branch_ids);
		//var_dump($rows);
/*
		if ( isset($tmp_rows) ) {
			foreach($tmp_rows as $row) {
				$rows[] = $row;
			}
			$rows = Sort::Multisort($rows, 'last_name');
			//var_dump($rows);

			$total_entries = Misc::ArrayAssocSum($rows, 'entries', 2);

			$rows[] = array(
							'full_name' => 'Total',
							'entries' => $total_entries
							);
		}
*/
		$smarty->assign_by_ref('rows', $rows );

		$pplf->getByCompanyId( $current_company->getId() );
		foreach ($pplf as $pay_period_obj) {
			$pay_period_ids[] = $pay_period_obj->getId();
		}

		$pplf = new PayPeriodListFactory();
		$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));

		$smarty->assign_by_ref('pay_period_options', $pay_period_options);
		$smarty->assign_by_ref('pay_period_id', $pay_period_id);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('report/UserBranchSummary.tpl');
?>