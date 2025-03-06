<?php

namespace App\Models\Core;

use App\Models\Accrual\AccrualListFactory;
use App\Models\Company\AllowanceFactory;
use App\Models\Company\AllowanceListFactory;
use App\Models\Holiday\HolidayListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\PayStub\PayStubEntryAccountLinkListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\PayStub\PayStubEntryListFactory;
use App\Models\PayStub\PayStubFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\PayStub\PayStubMiddlePayFactory;
use App\Models\PayStub\PayStubMiddlePayListFactory;
use App\Models\PayStubAmendment\PayStubAmendmentListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Schedule\ScheduleListFactory;
use App\Models\Users\UserDeductionListFactory;
use App\Models\Users\UserGenericStatusFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserWageListFactory;
use DateTime;

class CalculatePayStub extends PayStubFactory {

	var $wage_obj = NULL;
	var $user_obj = NULL;
	var $user_wage_obj = NULL;
	var $pay_period_obj = NULL;
	var $pay_period_schedule_obj = NULL;
	var $payroll_deduction_obj = NULL;
	var $pay_stub_entry_account_link_obj = NULL;
	var $pay_stub_entry_accounts_type_obj = NULL;

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory(); 

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriod() {
		if ( isset($this->data['pay_period_id']) ) {
			return $this->data['pay_period_id'];
		}

		return FALSE;
	}
	function setPayPeriod($id) {
		$id = trim($id);

		$pplf = new PayPeriodListFactory();

		if (  $this->Validator->isResultSetWithRows(	'pay_period',
														$pplf->getByID($id),
														('Invalid Pay Period')
														) ) {
			$this->data['pay_period_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getEnableCorrection() {
		if ( isset($this->correction) ) {
			return $this->correction;
		}

		return FALSE;
	}
	function setEnableCorrection($bool) {
		$this->correction = (bool)$bool;

		return TRUE;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			//$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() > 0 ) {
				$this->user_obj = $ulf->getCurrent();

				return $this->user_obj;
			}

			return FALSE;
		}
	}

	function getPayStubEntryAccountLinkObject() {
		if ( is_object($this->pay_stub_entry_account_link_obj) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = new PayStubEntryAccountLinkListFactory();
			$pseallf->getByCompanyId( $this->getUserObject()->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();
				return $this->pay_stub_entry_account_link_obj;
			}

			return FALSE;
		}
	}

	function getPayPeriodObject() {
		if ( is_object($this->pay_period_obj) ) {
			return $this->pay_period_obj;
		} else {
			$pplf = new PayPeriodListFactory();
			//$this->pay_period_obj = $pplf->getById( $this->getPayPeriod() )->getCurrent();
			$pplf->getById( $this->getPayPeriod() );
			if ( $pplf->getRecordCount() > 0 ) {
				$this->pay_period_obj = $pplf->getCurrent();

				return $this->pay_period_obj;
			}

			return FALSE;
		}
	}

	function getPayPeriodScheduleObject() {
		if ( is_object($this->pay_period_schedule_obj) ) {
			return $this->pay_period_schedule_obj;
		} else {
			$ppslf = new PayPeriodScheduleListFactory();
			$this->pay_period_schedule_obj = $ppslf->getById( $this->getPayPeriodObject()->getPayPeriodSchedule() )->getCurrent();

			return $this->pay_period_schedule_obj;
		}
	}

	function getWageObject() {
		if ( is_object($this->wage_obj) ) {
			return $this->wage_obj;
		} else {

			$this->wage_obj = new Wage( $this->getUser(), $this->getPayPeriod() );

			return $this->wage_obj;
		}
	}

	function getPayStubEntryAccountsTypeArray() {
		if ( is_array($this->pay_stub_entry_accounts_type_obj) ) {
			//Debug::text('Returning Cached data...' , __FILE__, __LINE__, __METHOD__,10);
			return $this->pay_stub_entry_accounts_type_obj;
		} else {
			$psealf = new PayStubEntryAccountListFactory();
			$this->pay_stub_entry_accounts_type_obj = $psealf->getByTypeArrayByCompanyIdAndStatusId( $this->getUserObject()->getCompany(), 10 );

			if ( is_array( $this->pay_stub_entry_accounts_type_obj ) ) {
				return $this->pay_stub_entry_accounts_type_obj;
			}

			Debug::text('Returning FALSE...' , __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}
	}

	function getDeductionObjectArrayForSorting( $obj ) {
		//$psealf = new PayStubEntryAccountListFactory();
		//$type_map_arr = $psealf->getByTypeArrayByCompanyIdAndStatusId( $this->getUserObject()->getCompany(), 10 );
		$type_map_arr = $this->getPayStubEntryAccountsTypeArray();

		//Debug::Arr($type_map_arr, 'PS Account Type Map Array: ', __FILE__, __LINE__, __METHOD__,10);

		if ( !is_object($obj) ) {
			return FALSE;
		}

		if ( get_class($obj) == 'UserDeductionListFactory' ) {
			if ( !is_object( $obj->getCompanyDeductionObject() ) ) {
				return FALSE;
			}

			if ( !is_object( $obj->getCompanyDeductionObject()->getPayStubEntryAccountObject() ) ) {
				Debug::text('Bad PS Entry Account(s) for Company Deduction. Skipping... ID: '. $obj->getCompanyDeductionObject()->getId(), __FILE__, __LINE__, __METHOD__,10);
				return FALSE;
			}
			//Debug::Arr($obj->getCompanyDeductionObject()->getIncludePayStubEntryAccount(), 'Include Accounts: ', __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr($obj->getCompanyDeductionObject()->getExcludePayStubEntryAccount(), 'Exclude Accounts: ', __FILE__, __LINE__, __METHOD__,10);

			$arr['type'] = get_class( $obj );
			$arr['obj_id'] = $obj->getId();
			$arr['id'] = substr($arr['type'],0,1).$obj->getId();
			$arr['name'] = $obj->getCompanyDeductionObject()->getName();
			$arr['order'] = $obj->getCompanyDeductionObject()->getPayStubEntryAccountObject()->getTypeCalculationOrder();
			$arr['obj'] = $obj;
			$arr['require_accounts'] = array();

			$include_accounts = $obj->getCompanyDeductionObject()->getIncludePayStubEntryAccount();
			if ( is_array($include_accounts) ) {
				foreach( $include_accounts as $include_account ) {
					if ( isset($type_map_arr[$include_account]) ) {
						foreach ($type_map_arr[$include_account] as $type_account ) {
							$arr['require_accounts'][] = $type_account;
						}
					} else {
						$arr['require_accounts'][] = $include_account;
					}
				}
			}
			unset($include_accounts, $include_account, $type_account);

			$exclude_accounts = $obj->getCompanyDeductionObject()->getExcludePayStubEntryAccount();
			if ( is_array($exclude_accounts) ) {
				foreach( $exclude_accounts as $exclude_account ) {
					if ( isset($type_map_arr[$exclude_account]) ) {
						foreach ($type_map_arr[$exclude_account] as $type_account ) {
							$arr['require_accounts'][] = $type_account;
						}
					} else {
						$arr['require_accounts'][] = $exclude_account;
					}
				}
			}
			unset($exclude_accounts, $exclude_account, $type_account);

			$arr['affect_accounts'] = $obj->getCompanyDeductionObject()->getPayStubEntryAccount();

			return $arr;
		} elseif ( get_class($obj) == 'PayStubAmendmentListFactory' ) {
			$arr['type'] = get_class( $obj );
			$arr['obj_id'] = $obj->getId();
			$arr['id'] = substr($arr['type'],0,1).$obj->getId();
			$arr['name'] = $obj->getDescription();
			$arr['order'] = $obj->getPayStubEntryNameObject()->getTypeCalculationOrder();
			$arr['obj'] = $obj;

			$arr['affect_accounts'] = $obj->getPayStubEntryNameId();

			if ( $obj->getType() == 10 ) { //Fixed
				$arr['require_accounts'][] = NULL;
			} else { //Percent
				$arr['require_accounts'][] = $obj->getPercentAmountEntryNameId();
			}

			return $arr;
		}

		return FALSE;
	}

	function getOrderedDeductionAndPSAmendment( $udlf, $psalf ) {
		global $profiler;

		$dependency_tree = new DependencyTree();

		$deduction_order_arr = array();
		if ( is_object($udlf) ) {
			//Loop over all User Deductions getting Include/Exclude and PS accounts.
			if ( $udlf->getRecordCount() > 0 ) {
				foreach ( $udlf as $ud_obj ) {
					Debug::text('User Deduction: ID: '. $ud_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					if ( $ud_obj->getCompanyDeductionObject()->getStatus() == 10 ) {
						//$deduction_order_arr = $this->calculateDeductionOrder( $deduction_order_arr, $ud_obj );
						$global_id = substr(get_class( $ud_obj ),0,1) . $ud_obj->getId();
						$deduction_order_arr[$global_id] = $this->getDeductionObjectArrayForSorting( $ud_obj );

						$dependency_tree->addNode( $global_id, $deduction_order_arr[$global_id]['require_accounts'], $deduction_order_arr[$global_id]['affect_accounts'], $deduction_order_arr[$global_id]['order']);
					} else {
						Debug::text('Company Deduction is DISABLED!', __FILE__, __LINE__, __METHOD__,10);
					}
				}
			}
		}
		unset($udlf, $ud_obj);

		if ( is_object( $psalf) ) {
			if ( $psalf->getRecordCount() > 0 ) {
				foreach ( $psalf as $psa_obj ) {
					Debug::text('PS Amendment ID: '. $psa_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					//$deduction_order_arr = $this->calculateDeductionOrder( $deduction_order_arr, $ud_obj );
					$global_id = substr(get_class( $psa_obj ),0,1) . $psa_obj->getId();
					$deduction_order_arr[$global_id] = $this->getDeductionObjectArrayForSorting( $psa_obj );

					$dependency_tree->addNode( $global_id, $deduction_order_arr[$global_id]['require_accounts'], $deduction_order_arr[$global_id]['affect_accounts'], $deduction_order_arr[$global_id]['order']);
				}
			}
		}
		unset($psalf, $psa_obj);

		$profiler->startTimer( "Calculate Dependency Tree");
		Debug::text('Calculate Dependency Tree: Start: '. TTDate::getDate('DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__,10);

		$sorted_deduction_ids = $dependency_tree->getAllNodesInOrder();

		Debug::text('Calculate Dependency Tree: End: '. TTDate::getDate('DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__,10);
		$profiler->stopTimer( "Calculate Dependency Tree");

		if ( is_array($sorted_deduction_ids) ) {
			foreach( $sorted_deduction_ids as $tmp => $deduction_id ) {
				$retarr[$deduction_id] = $deduction_order_arr[$deduction_id];
			}
		}

		//Debug::Arr($retarr, 'AFTER - Deduction Order Array: ', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	function calculate($epoch = NULL) {

		if ( $this->getUserObject() == FALSE OR $this->getUserObject()->getStatus() !== 10 ) {
			return FALSE;
		}

		$generic_queue_status_label = $this->getUserObject()->getFullName(TRUE).' - '. ('Pay Stub');

		if ( $epoch == NULL OR $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if (  $this->getPayPeriodObject() == FALSE ) {
			return FALSE;
		}

		Debug::text('bbUser Id: '. $this->getUser() .' Pay Period End Date: '. TTDate::getDate('DATE+TIME', $this->getPayPeriodObject()->getEndDate() ), __FILE__, __LINE__, __METHOD__,10);

		//echo '<pre>';

		$pay_stub = new PayStubFactory();
		$pay_stub->StartTransaction();

		$old_pay_stub_id = NULL;
		if ( $this->getEnableCorrection() == TRUE ) {
			Debug::text('Correction Enabled!', __FILE__, __LINE__, __METHOD__,10);
			$pay_stub->setTemp(TRUE);

			//Check for current pay stub ID so we can compare against it.
			$pslf = new PayStubListFactory();
			$pslf->getByUserIdAndPayPeriodId( $this->getUser(), $this->getPayPeriod() );
			if ( $pslf->getRecordCount() > 0 ) {
				$old_pay_stub_id = $pslf->getCurrent()->getId();
				Debug::text('Comparing Against Pay Stub ID: '. $old_pay_stub_id, __FILE__, __LINE__, __METHOD__,10);
			}
		}
		$pay_stub->setUser( $this->getUser() );
		$pay_stub->setPayPeriod( $this->getPayPeriod() );
		$pay_stub->setCurrency( $this->getUserObject()->getCurrency() );
		$pay_stub->setStatus('NEW');

		//Use User Termination Date instead of ROE.
		if ( $this->getUserObject()->getTerminationDate() != ''
				AND $this->getUserObject()->getTerminationDate() >= $this->getPayPeriodObject()->getStartDate()
				AND $this->getUserObject()->getTerminationDate() <= $this->getPayPeriodObject()->getEndDate() ) {
			Debug::text('User has been terminated in this pay period!', __FILE__, __LINE__, __METHOD__,10);

			$is_terminated = TRUE;
		} else {
			$is_terminated = FALSE;
		}

		if ( $is_terminated == TRUE ) {
			Debug::text('User is Terminated, assuming final pay, setting End Date to terminated date: '. TTDate::getDate('DATE+TIME', $this->getUserObject()->getTerminationDate() ), __FILE__, __LINE__, __METHOD__,10);

			$pay_stub->setStartDate( $pay_stub->getPayPeriodObject()->getStartDate() );
			$pay_stub->setEndDate( $this->getUserObject()->getTerminationDate() );

			//Use the PS generation date instead of terminated date...
			//Unlikely they would pay someone before the pay stub is generated.
			//Perhaps still use the pay period transaction date for this too?
			//Anything we set won't be correct for everyone. Maybe a later date is better though?
			//Perhaps add to the user factory under Termination Date a: "Final Transaction Date" for this purpose?
			//Use the end of the current date for the transaction date, as if the employee is terminated
			//on the same day they are generating the pay stub, the transaction date could be before the end date
			//as the end date is at 11:59PM

			//For now make sure that the transaction date for a terminated employee is never before their termination date.
			if ( TTDate::getEndDayEpoch( TTDate::getTime() ) < $this->getUserObject()->getTerminationDate() ) {
				$pay_stub->setTransactionDate( $this->getUserObject()->getTerminationDate() );
			} else {
				$pay_stub->setTransactionDate( TTDate::getEndDayEpoch( TTDate::getTime() ) );
			}

		} else {
			Debug::text('User Termination Date is NOT set, assuming normal pay.', __FILE__, __LINE__, __METHOD__,10);
			$pay_stub->setDefaultDates();
		}

		//This must go after setting advance
		if ( $this->getEnableCorrection() == FALSE AND $pay_stub->IsUniquePayStub() == FALSE ) {
			Debug::text('Pay Stub already exists', __FILE__, __LINE__, __METHOD__,10);
			$this->CommitTransaction();

			UserGenericStatusFactory::queueGenericStatus( $generic_queue_status_label, 20, ('Pay Stub for this employee already exists, skipping...'), NULL );

			return FALSE;
		}

		if ( $pay_stub->isValid() == TRUE ) {
			$pay_stub->Save(FALSE);
			$pay_stub->setStatus('Open');
		} else {
			Debug::text('Pay Stub isValid failed!', __FILE__, __LINE__, __METHOD__,10);

			UserGenericStatusFactory::queueGenericStatus( $generic_queue_status_label, 10, $pay_stub->Validator->getTextErrors(), NULL );

			$this->FailTransaction();
			$this->CommitTransaction();
			return FALSE;
		}

		$pay_stub->loadPreviousPayStub();

		$user_date_total_arr = $this->getWageObject()->getUserDateTotalArray();
                
		if ( isset($user_date_total_arr['entries']) AND is_array( $user_date_total_arr['entries'] ) ) {
			foreach( $user_date_total_arr['entries'] as $udt_arr ) {
				//Allow negative amounts so flat rate premium policies can reduce an employees wage if need be.
				if ( $udt_arr['amount'] != 0 ) {
					Debug::text('Adding Pay Stub Entry: '. $udt_arr['pay_stub_entry'] .' Amount: '. $udt_arr['amount'], __FILE__, __LINE__, __METHOD__,10);
					$pay_stub->addEntry( $udt_arr['pay_stub_entry'], $udt_arr['amount'], TTDate::getHours( $udt_arr['total_time'] ), $udt_arr['rate'] );
				} else {
					Debug::text('NOT Adding ($0 amount) Pay Stub Entry: '. $udt_arr['pay_stub_entry'] .' Amount: '. $udt_arr['amount'], __FILE__, __LINE__, __METHOD__,10);
				}
			}
		} else {
			//No Earnings, CHECK FOR PS AMENDMENTS next for earnings.
			Debug::text('NO TimeSheet EARNINGS ON PAY STUB... Checking for PS amendments', __FILE__, __LINE__, __METHOD__,10);
		}
                /////////////////////////////////////////Added by Thusitha start//////////////////////////////////////////////
                $pgplf = new PremiumPolicyListFactory();
               // $pslf = new PayStubListFactory();
                //echo $this->getUser();                exit();
                $pgplf->getByPolicyGroupUserId($this->getUser());
                
                if($pgplf->getRecordCount() > 0){
                    
                    foreach($pgplf as $ppf_obj){

                        //$ppf_obj = $pgplf->getCurrent();

                        $allf = new AllowanceListFactory();
                        $allf->getByUserIdAndPayperiodsId($this->getUser(), $this->getPayPeriod());
                      
                         if($allf->getRecordCount()>0){
                          
                             $amount = 0;
                             $alf_obj = $allf->getCurrent();
                             
                             if($ppf_obj->getId() == 1){
                                 
                                 $amount = ( $alf_obj->getWorkedDays() - $alf_obj->getLateDays())*120;
                             }
                             elseif ($ppf_obj->getId() == 3) {
                                 $amount = ( $alf_obj->getWorkedDays() - $alf_obj->getLateDays())*160;
                             }
                             elseif ($ppf_obj->getId() == 2) {
                                 
                                 $nopay_days = $alf_obj->getNopayDays();
                                 $full_day = $alf_obj->getFulldayLeaveDays();
                                 $half_day = $alf_obj->getHalfdayLeaveDays();
                                 
                                 $allowance = 3000;
                                 
                                 $amount =  $allowance - (($nopay_days*1000) + ($full_day*500)+ ($half_day*250));
                                 
                             }
                             
                         }
                             if($amount > 0){
                                 $pay_stub->addEntry( $ppf_obj->getPayStubEntryAccountId(), $amount, 2, 1 );
                             }
                         
                    
                    }    
                }
		/////////////////////////////////////////Added by Thusitha end//////////////////////////////////////////////
		//Get all PS amendments and Tax / Deductions so we can determine the proper order to calculate them in.
		$psalf = new PayStubAmendmentListFactory();
		$psalf->getByUserIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );

		//                echo '<br>';
		//                print_r($psalf->getRecordCount());
		$udlf = new UserDeductionListFactory();
		$udlf->getByCompanyIdAndUserId( $this->getUserObject()->getCompany(), $this->getUserObject()->getId() );

		//                echo '<br>';
		//                print_r($udlf->getRecordCount());
                
		$deduction_order_arr = $this->getOrderedDeductionAndPSAmendment( $udlf, $psalf );
               // print_r($deduction_order_arr); exit;
		if ( is_array($deduction_order_arr) AND count($deduction_order_arr) > 0 ) {
                    
                      $deduction_slary_advance = 0;
                      
			foreach($deduction_order_arr as $calculation_order => $data_arr ) {

				Debug::text('Found PS Amendment/Deduction: Type: '. $data_arr['type'] .' Name: '. $data_arr['name'] .' Order: '. $calculation_order, __FILE__, __LINE__, __METHOD__,10);

				if ( isset($data_arr['obj']) AND is_object($data_arr['obj']) ) {

					if ( $data_arr['type'] == 'UserDeductionListFactory' ) {

						$ud_obj = $data_arr['obj'];

						//Determine if this deduction is valid based on start/end dates.
						//Determine if this deduction is valid based on min/max length of service.
						//Determine if this deduction is valid based on min/max user age.
						if ( $ud_obj->getCompanyDeductionObject()->isActiveDate( $pay_stub->getPayPeriodObject()->getEndDate() ) == TRUE
								AND $ud_obj->getCompanyDeductionObject()->isActiveLengthOfService( $this->getUserObject(), $pay_stub->getPayPeriodObject()->getEndDate() ) == TRUE
								AND $ud_obj->getCompanyDeductionObject()->isActiveUserAge( $this->getUserObject(), $pay_stub->getPayPeriodObject()->getEndDate() ) == TRUE ) {

								$amount = $ud_obj->getDeductionAmount( $this->getUserObject()->getId(), $pay_stub, $this->getPayPeriodObject() );
								Debug::text('User Deduction: '. $ud_obj->getCompanyDeductionObject()->getName() .' Amount: '. $amount .' Calculation Order: '. $ud_obj->getCompanyDeductionObject()->getCalculationOrder(), __FILE__, __LINE__, __METHOD__,10);

                                                                if($ud_obj->getCompanyDeduction()==3){
                                                                    
                                                                    $wage_obj = $this->getWageObject();
                                                                    
                                                                    $date_now = new DateTime();
                                                                    
                                                                    $user_wage_list = new UserWageListFactory();
                                                                    $user_wage_list->getLastWageByUserIdAndDate($this->getUserObject()->getId(),$date_now->getTimestamp());
                                                                    
                                                                    if($user_wage_list->getRecordCount() > 0){
                                                                        
                                                                       $uw_obj =  $user_wage_list->getCurrent();
                                                                       
                                                                       $total_pay_period_days = ceil( TTDate::getDayDifference( $pay_stub->getPayPeriodObject()->getStartDate(), $pay_stub->getPayPeriodObject()->getEndDate()) );
                                                                      
                                                                        
                                                                        $wage_effective_date = new DateTime($uw_obj->getColumn('effective_date'));
                                                                        $prev_wage_effective_date = $pay_stub->getPayPeriodObject()->getEndDate();
                                                                        
                                                                        $total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date->getTimestamp(), $prev_wage_effective_date ) );
                                                                        
                                                                         
                                                                        
                                                                        if($total_pay_period_days > $total_wage_effective_days){
                                                                            
                                                                            $total_pay_period_days = 30;
                                                                            
                                                                           // if($this->getUserObject()->getId()==971){
                                                                            
                                                                            //echo $total_wage_effective_days.' '.$uw_obj->getColumn('effective_date').'<br>';
                                                                           // echo $total_pay_period_days.' ';
                                                                            
                                                                           $amount = abs(bcmul( $amount, bcdiv($total_wage_effective_days, $total_pay_period_days) ));
                                                                           
                                                                          // exit();
                                                                          //  }
                                                                            
                                                                        }
                                                                    }
                                                                  
                                                                }
                                                                
                                                                if($ud_obj->getCompanyDeduction()==10){//no pay
                                                                    $amount = $user_date_total_arr['other']['dock_absence_amount'];
                                                                }
                                                                
                                                                
                                                                $deduction_slary_advance++;
                                                                //}
								//Allow negative amounts, so they can reduce previously calculated deductions or something. getEmpBasisType()
                                                                // added by thusitha 2017/08/10
								if ( isset($amount) AND $amount != 0 ) {
                                                                   
                                                                       $pay_stub->addEntry( $ud_obj->getCompanyDeductionObject()->getPayStubEntryAccount(), $amount );
                                                                   
								} else {
									Debug::text('Amount is 0, skipping...', __FILE__, __LINE__, __METHOD__,10);
								}
						}
						unset($amount, $ud_obj);
					} elseif ( $data_arr['type'] == 'PayStubAmendmentListFactory' ) {
						$psa_obj = $data_arr['obj'];

						Debug::text('Found Pay Stub Amendment: ID: '. $psa_obj->getID() .' Entry Name ID: '. $psa_obj->getPayStubEntryNameId() .' Type: '. $psa_obj->getType() , __FILE__, __LINE__, __METHOD__,10);

						$amount = $psa_obj->getCalculatedAmount( $pay_stub );
                                                
                                               

						if ( isset($amount) AND $amount != 0 ) {
							Debug::text('Pay Stub Amendment Amount: '. $amount , __FILE__, __LINE__, __METHOD__,10);

							$pay_stub->addEntry( $psa_obj->getPayStubEntryNameId(), $amount, $psa_obj->getUnits(), $psa_obj->getRate(), $psa_obj->getDescription(), $psa_obj->getID(), NULL, NULL, $psa_obj->getYTDAdjustment() );

							//Keep in mind this causes pay stubs to be re-generated every time, as this modifies the updated time
							//to slightly more then the pay stub creation time.
							$psa_obj->setStatus('IN USE');
							$psa_obj->Save();

						} else {
							Debug::text('bPay Stub Amendment Amount is not set...', __FILE__, __LINE__, __METHOD__,10);
						}
						unset($amount, $psa_obj);

					}
				}

			}
		//                        die;
                        
                       

		}
		unset($deduction_order_arr, $calculation_order, $data_arr);

		$pay_stub_id = $pay_stub->getId();

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__,10);
			$pay_stub->Save();

			if ( $this->getEnableCorrection() == TRUE ) {
				if ( isset($old_pay_stub_id) ) {
					Debug::text('bCorrection Enabled - Doing Comparison here', __FILE__, __LINE__, __METHOD__,10);
					PayStubFactory::CalcDifferences( $pay_stub_id, $old_pay_stub_id );
				}

				//Delete newly created temp paystub.
				//This used to be in the above IF block that depended on $old_pay_stub_id
				//being set, however in cases where the old pay stub didn't exist
				//TimeTrex wouldn't delete these temporary pay stubs.
				//Moving this code outside that IF statement so it only depends on EnableCorrection()
				//to be TRUE should fix that issue.
				$pslf = new PayStubListFactory();
				$pslf->getById( $pay_stub_id );
				if ( $pslf->getRecordCount() > 0 ) {
					$tmp_ps_obj = $pslf->getCurrent();
					$tmp_ps_obj->setDeleted(TRUE);
					$tmp_ps_obj->Save();
					unset($tmp_ps_obj);
				}
			}

			$pay_stub->CommitTransaction();

			UserGenericStatusFactory::queueGenericStatus( $generic_queue_status_label, 30, NULL, NULL );

			return TRUE;
		}

		Debug::text('Pay Stub is NOT valid returning FALSE', __FILE__, __LINE__, __METHOD__,10);

		UserGenericStatusFactory::queueGenericStatus( $generic_queue_status_label, 10, $pay_stub->Validator->getTextErrors(), NULL );

		$pay_stub->FailTransaction(); //Reduce transaction count by one.
		//$pay_stub->FailTransaction(); //Reduce transaction count by one.

		$pay_stub->CommitTransaction();

		return FALSE;
	}
        
        
     ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        
	function calculateMid($epoch = NULL) {

		if ( $this->getUserObject() == FALSE OR $this->getUserObject()->getStatus() !== 10 ) {
			return FALSE;
		}

		$generic_queue_status_label = $this->getUserObject()->getFullName(TRUE).' - '. ('Pay Stub');

		if ( $epoch == NULL OR $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if (  $this->getPayPeriodObject() == FALSE ) {
			return FALSE;
		}

		Debug::text('bbUser Id: '. $this->getUser() .' Pay Period End Date: '. TTDate::getDate('DATE+TIME', $this->getPayPeriodObject()->getEndDate() ), __FILE__, __LINE__, __METHOD__,10);

		//echo '<pre>';

		$pay_stub = new PayStubFactory();
		$pay_stub->StartTransaction();

		$old_pay_stub_id = NULL;
		if ( $this->getEnableCorrection() == TRUE ) {
			Debug::text('Correction Enabled!', __FILE__, __LINE__, __METHOD__,10);
			$pay_stub->setTemp(TRUE);

			//Check for current pay stub ID so we can compare against it.
			$pslf = new PayStubListFactory();
			$pslf->getByUserIdAndPayPeriodId( $this->getUser(), $this->getPayPeriod() );
			if ( $pslf->getRecordCount() > 0 ) {
				$old_pay_stub_id = $pslf->getCurrent()->getId();
				Debug::text('Comparing Against Pay Stub ID: '. $old_pay_stub_id, __FILE__, __LINE__, __METHOD__,10);
			}
		}
		$pay_stub->setUser( $this->getUser() );
		$pay_stub->setPayPeriod( $this->getPayPeriod() );
		$pay_stub->setCurrency( $this->getUserObject()->getCurrency() );
		$pay_stub->setStatus('NEW');

		//Use User Termination Date instead of ROE.
		if ( $this->getUserObject()->getTerminationDate() != ''
				AND $this->getUserObject()->getTerminationDate() >= $this->getPayPeriodObject()->getStartDate()
				AND $this->getUserObject()->getTerminationDate() <= $this->getPayPeriodObject()->getEndDate() ) {
			Debug::text('User has been terminated in this pay period!', __FILE__, __LINE__, __METHOD__,10);

			$is_terminated = TRUE;
		} else {
			$is_terminated = FALSE;
		}

		if ( $is_terminated == TRUE ) {
			Debug::text('User is Terminated, assuming final pay, setting End Date to terminated date: '. TTDate::getDate('DATE+TIME', $this->getUserObject()->getTerminationDate() ), __FILE__, __LINE__, __METHOD__,10);

			$pay_stub->setStartDate( $pay_stub->getPayPeriodObject()->getStartDate() );
			$pay_stub->setEndDate( $this->getUserObject()->getTerminationDate() );

			//Use the PS generation date instead of terminated date...
			//Unlikely they would pay someone before the pay stub is generated.
			//Perhaps still use the pay period transaction date for this too?
			//Anything we set won't be correct for everyone. Maybe a later date is better though?
			//Perhaps add to the user factory under Termination Date a: "Final Transaction Date" for this purpose?
			//Use the end of the current date for the transaction date, as if the employee is terminated
			//on the same day they are generating the pay stub, the transaction date could be before the end date
			//as the end date is at 11:59PM

			//For now make sure that the transaction date for a terminated employee is never before their termination date.
			if ( TTDate::getEndDayEpoch( TTDate::getTime() ) < $this->getUserObject()->getTerminationDate() ) {
				$pay_stub->setTransactionDate( $this->getUserObject()->getTerminationDate() );
			} else {
				$pay_stub->setTransactionDate( TTDate::getEndDayEpoch( TTDate::getTime() ) );
			}

		} else {
			Debug::text('User Termination Date is NOT set, assuming normal pay.', __FILE__, __LINE__, __METHOD__,10);
			$pay_stub->setDefaultDates();
		}

		//This must go after setting advance
		if ( $this->getEnableCorrection() == FALSE AND $pay_stub->IsUniquePayStub() == FALSE ) {
			Debug::text('Pay Stub already exists', __FILE__, __LINE__, __METHOD__,10);
			$this->CommitTransaction();

			UserGenericStatusFactory::queueGenericStatus( $generic_queue_status_label, 20, ('Pay Stub for this employee already exists, skipping...'), NULL );

			return FALSE;
		}

		if ( $pay_stub->isValid() == TRUE ) {
			$pay_stub->Save(FALSE);
			$pay_stub->setStatus('Open');
		} else {
			Debug::text('Pay Stub isValid failed!', __FILE__, __LINE__, __METHOD__,10);

			UserGenericStatusFactory::queueGenericStatus( $generic_queue_status_label, 10, $pay_stub->Validator->getTextErrors(), NULL );

			$this->FailTransaction();
			$this->CommitTransaction();
			return FALSE;
		}

		$pay_stub->loadPreviousPayStub();
                
                $checking_date = $this->getPayPeriodObject()->getEndDate();
                
                
                $date = new DateTime();
                $date->setTimestamp($checking_date);
                
                $date_formated = $date->format('Y-m');
                
               //$date->add(new DateInterval('P25D'));
               $check_date = $date_formated.'-25';
                
               $date = new DateTime($check_date);
                $check_date = $date->format('Y-m-d');
               // echo $check_date;
                

		$user_date_total_arr = $this->getWageObject()->getUserDateTotalArrayMiddlePay($check_date);

		if ( isset($user_date_total_arr['entries']) AND is_array( $user_date_total_arr['entries'] ) ) {
			foreach( $user_date_total_arr['entries'] as $udt_arr ) {
				//Allow negative amounts so flat rate premium policies can reduce an employees wage if need be.
				if ( $udt_arr['amount'] != 0 ) {
					Debug::text('Adding Pay Stub Entry: '. $udt_arr['pay_stub_entry'] .' Amount: '. $udt_arr['amount'], __FILE__, __LINE__, __METHOD__,10);
					$pay_stub->addEntry( $udt_arr['pay_stub_entry'], $udt_arr['amount'], TTDate::getHours( $udt_arr['total_time'] ), $udt_arr['rate'] );
				} else {
					Debug::text('NOT Adding ($0 amount) Pay Stub Entry: '. $udt_arr['pay_stub_entry'] .' Amount: '. $udt_arr['amount'], __FILE__, __LINE__, __METHOD__,10);
				}
			}
		} else {
			//No Earnings, CHECK FOR PS AMENDMENTS next for earnings.
			Debug::text('NO TimeSheet EARNINGS ON PAY STUB... Checking for PS amendments', __FILE__, __LINE__, __METHOD__,10);
		}

		//Get all PS amendments and Tax / Deductions so we can determine the proper order to calculate them in.
		$psalf = new PayStubAmendmentListFactory();
		$psalf->getByUserIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );

		$udlf = new UserDeductionListFactory();
		$udlf->getByCompanyIdAndUserIdForMid( $this->getUserObject()->getCompany(), $this->getUserObject()->getId() );

		$deduction_order_arr = $this->getOrderedDeductionAndPSAmendment( $udlf, $psalf );

		if ( is_array($deduction_order_arr) AND count($deduction_order_arr) > 0 ) {
			foreach($deduction_order_arr as $calculation_order => $data_arr ) {

				Debug::text('Found PS Amendment/Deduction: Type: '. $data_arr['type'] .' Name: '. $data_arr['name'] .' Order: '. $calculation_order, __FILE__, __LINE__, __METHOD__,10);

				if ( isset($data_arr['obj']) AND is_object($data_arr['obj']) ) {

					if ( $data_arr['type'] == 'UserDeductionListFactory' ) {

						$ud_obj = $data_arr['obj'];

						//Determine if this deduction is valid based on start/end dates.
						//Determine if this deduction is valid based on min/max length of service.
						//Determine if this deduction is valid based on min/max user age.
						if ( $ud_obj->getCompanyDeductionObject()->isActiveDate( $pay_stub->getPayPeriodObject()->getEndDate() ) == TRUE
								AND $ud_obj->getCompanyDeductionObject()->isActiveLengthOfService( $this->getUserObject(), $pay_stub->getPayPeriodObject()->getEndDate() ) == TRUE
								AND $ud_obj->getCompanyDeductionObject()->isActiveUserAge( $this->getUserObject(), $pay_stub->getPayPeriodObject()->getEndDate() ) == TRUE ) {

								$amount = $ud_obj->getDeductionAmount( $this->getUserObject()->getId(), $pay_stub, $this->getPayPeriodObject() );
								Debug::text('User Deduction: '. $ud_obj->getCompanyDeductionObject()->getName() .' Amount: '. $amount .' Calculation Order: '. $ud_obj->getCompanyDeductionObject()->getCalculationOrder(), __FILE__, __LINE__, __METHOD__,10);

								//Allow negative amounts, so they can reduce previously calculated deductions or something.
								if ( isset($amount) AND $amount != 0 ) {
									//$pay_stub->addEntry( $ud_obj->getCompanyDeductionObject()->getPayStubEntryAccount(), $amount );
                                                                    
                                                                     if($ud_obj->getCompanyDeductionObject()->getBasisOfEmployment()==3){
                                                                       $pay_stub->addEntry( $ud_obj->getCompanyDeductionObject()->getPayStubEntryAccount(), $amount );
                                                                   }
                                                                   else if( $ud_obj->getCompanyDeductionObject()->getBasisOfEmployment()==1 && $this->getUserObject()->getEmpBasisType()== 1 ){
									$pay_stub->addEntry( $ud_obj->getCompanyDeductionObject()->getPayStubEntryAccount(), $amount );
                                                                   }
                                                                   else if( $ud_obj->getCompanyDeductionObject()->getBasisOfEmployment()==1 && $this->getUserObject()->getEmpBasisType()== 2 ){
									$pay_stub->addEntry( $ud_obj->getCompanyDeductionObject()->getPayStubEntryAccount(), $amount );
                                                                   }
                                                                   else if( $ud_obj->getCompanyDeductionObject()->getBasisOfEmployment()==2 && $this->getUserObject()->getEmpBasisType()== 3 ){
									$pay_stub->addEntry( $ud_obj->getCompanyDeductionObject()->getPayStubEntryAccount(), $amount );
                                                                   }
                                                                   else if( $ud_obj->getCompanyDeductionObject()->getBasisOfEmployment()==2 && $this->getUserObject()->getEmpBasisType()== 4 ){
									$pay_stub->addEntry( $ud_obj->getCompanyDeductionObject()->getPayStubEntryAccount(), $amount );
                                                                   }
                                                                   else{
                                                                      // $pay_stub->addEntry( $ud_obj->getCompanyDeductionObject()->getPayStubEntryAccount(), $amount );
                                                                   }
                                                                   
								} else {
									Debug::text('Amount is 0, skipping...', __FILE__, __LINE__, __METHOD__,10);
								}
						}
						unset($amount, $ud_obj);
					} elseif ( $data_arr['type'] == 'PayStubAmendmentListFactory' ) {
						$psa_obj = $data_arr['obj'];

						Debug::text('Found Pay Stub Amendment: ID: '. $psa_obj->getID() .' Entry Name ID: '. $psa_obj->getPayStubEntryNameId() .' Type: '. $psa_obj->getType() , __FILE__, __LINE__, __METHOD__,10);

						$amount = $psa_obj->getCalculatedAmount( $pay_stub );

						if ( isset($amount) AND $amount != 0 ) {
							Debug::text('Pay Stub Amendment Amount: '. $amount , __FILE__, __LINE__, __METHOD__,10);

							$pay_stub->addEntry( $psa_obj->getPayStubEntryNameId(), $amount, $psa_obj->getUnits(), $psa_obj->getRate(), $psa_obj->getDescription(), $psa_obj->getID(), NULL, NULL, $psa_obj->getYTDAdjustment() );

							//Keep in mind this causes pay stubs to be re-generated every time, as this modifies the updated time
							//to slightly more then the pay stub creation time.
							$psa_obj->setStatus('IN USE');
							$psa_obj->Save();

						} else {
							Debug::text('bPay Stub Amendment Amount is not set...', __FILE__, __LINE__, __METHOD__,10);
						}
						unset($amount, $psa_obj);

					}
				}

			}

		}
		unset($deduction_order_arr, $calculation_order, $data_arr);

		$pay_stub_id = $pay_stub->getId();

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntriesMiddle();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__,10);
			$pay_stub->Save();

			if ( $this->getEnableCorrection() == TRUE ) {
				if ( isset($old_pay_stub_id) ) {
					Debug::text('bCorrection Enabled - Doing Comparison here', __FILE__, __LINE__, __METHOD__,10);
					PayStubFactory::CalcDifferences( $pay_stub_id, $old_pay_stub_id );
				}

				//Delete newly created temp paystub.
				//This used to be in the above IF block that depended on $old_pay_stub_id
				//being set, however in cases where the old pay stub didn't exist
				//TimeTrex wouldn't delete these temporary pay stubs.
				//Moving this code outside that IF statement so it only depends on EnableCorrection()
				//to be TRUE should fix that issue.
				$pslf = new PayStubListFactory();
				$pslf->getById( $pay_stub_id );
				if ( $pslf->getRecordCount() > 0 ) {
					$tmp_ps_obj = $pslf->getCurrent();
					$tmp_ps_obj->setDeleted(TRUE);
					$tmp_ps_obj->Save();
					unset($tmp_ps_obj);
				}
			}

			$pay_stub->CommitTransaction();

			UserGenericStatusFactory::queueGenericStatus( $generic_queue_status_label, 30, NULL, NULL );

			return TRUE;
		}

		Debug::text('Pay Stub is NOT valid returning FALSE', __FILE__, __LINE__, __METHOD__,10);

		UserGenericStatusFactory::queueGenericStatus( $generic_queue_status_label, 10, $pay_stub->Validator->getTextErrors(), NULL );

		$pay_stub->FailTransaction(); //Reduce transaction count by one.
		//$pay_stub->FailTransaction(); //Reduce transaction count by one.

		$pay_stub->CommitTransaction();

		return FALSE;
	}
        
    function calculateTotalMid($epoch = NULL) {
            
		if ( $this->getUserObject() == FALSE OR $this->getUserObject()->getStatus() !== 10 ) {
			return FALSE;
		}
                
                if ( $epoch == NULL OR $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if (  $this->getPayPeriodObject() == FALSE ) {
			return FALSE;
		}
            
                $pslf = new PayStubListFactory();
                
                $hide_employer_rows = 0;
                
             
                
                $pslf->getByPayperiodsIdAndUserId($this->getPayPeriod(),$this->getUser());
                //$pslf->setPayStubTotalMidPay( $pslf, (bool)$hide_employer_rows );
                
               
                   
                if ( $pslf->getRecordCount() > 0 ) {
                    
                       
                    $prev_type = NULL;
                    
                    foreach ($pslf as $pay_stub_obj) {
                        
                         
                        
                        $psealf = new PayStubEntryAccountListFactory();
                        
                        //Get Pay Period information

			$pplf = new PayPeriodListFactory();

			$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();
                        
                        $pself = new PayStubEntryListFactory();

			$pself->getByPayStubId( $pay_stub_obj->getId() );
                        
                        foreach ($pself as $pay_stub_entry) {
                            
                           
                            $pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();
                            
                            
                            if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {

                                    $type = $pay_stub_entry_name_obj->getType();

                            }
                            
                            
                            if ( $type != 40 OR ( $type == 40 AND $pay_stub_entry->getAmount() != 0 ) ) {
                                
                                    if($pay_stub_entry->getPayStubEntryNameId() == 89){
                                            
                                       // $psmplf = new PayStubMiddlePayListFactory();
                                        
                                         $psmplf = new PayStubMiddlePayListFactory();
                                        
                                    
                                        $psmplf->getByPayPeriodsIdAndUserId($pay_period_obj->getId(),$this->getUserObject()->getId());
                                        
                                        if($psmplf->getRecordCount()>0){
                                            
                                            
                                          $psmpf = $psmplf->getCurrent();
                                           
                                          $psmpf->setPayPeriod($pay_period_obj->getId());
                                          $psmpf->setUser($this->getUserObject()->getId());
                                          $psmpf->setAmount($pay_stub_entry->getAmount());
                                          
                                          $psmpf->Save();
                                            
                                        }
                                        else{
                                         $psmpf = new PayStubMiddlePayFactory();
                                         
                                         
                                       // $psmpf = new PayStubMiddlePayFactory();                     
                                          $psmpf->setPayPeriod($pay_period_obj->getId());
                                          $psmpf->setUser($this->getUserObject()->getId());
                                          $psmpf->setAmount($pay_stub_entry->getAmount());
                                          
                                          $psmpf->Save();
                                        }
                                         
                                        
                                    }





					}
                                        
                            $prev_type = $pay_stub_entry_name_obj->getType(); 
                            
                            
                           
                                        
                        }// end of pay stub entry list foreach
                                
                    }
                    
                }
               
    }
        
        
	function calculateAllowance()
	{
		
		
		
		$udlf = new UserDateListFactory();
		
		$filter_data['pay_period_ids'] = array($this->getPayPeriod());
		$filter_data['include_user_ids'] = array($this->getUser());
		$filter_data['user_id'] = array($this->getUser());
		
		//echo $this->getUserObject()->getCompany(); 
		
		$udtlf = new UserDateTotalListFactory();
		$udtlf->getDayReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		
					
		$slf = new ScheduleListFactory();
		$slf->getSearchByCompanyIdAndArrayCriteria($this->getUserObject()->getCompany(),$filter_data);
		
		if ( $slf->getRecordCount() > 0 ) {
			foreach($slf as $s_obj) {
				$user_id = $s_obj->getColumn('user_id');
				$status_id = $s_obj->getColumn('status_id');
				$status = strtolower( Option::getByKey($status_id, $s_obj->getOptions('status') ) );
				$pay_period_id = $s_obj->getColumn('pay_period_id');
				$date_stamp = TTDate::strtotime( $s_obj->getColumn('date_stamp') );

				$schedule_rows[$pay_period_id][$user_id][$date_stamp][$status] = $s_obj->getColumn('total_time');  
				$schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'] = $s_obj->getColumn('start_time');  
				$schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'] = $s_obj->getColumn('end_time');  
				unset($user_id, $status_id, $status, $pay_period_id, $date_stamp);
			}
		}
		//echo '<pre>'; print_r($schedule_rows); echo'<pre>';
					
					
		foreach ($udtlf as $udt_obj ) {
			$user_id = $udt_obj->getColumn('id');
			$pay_period_id = $udt_obj->getColumn('pay_period_id');
			$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
							
			$status_id = $udt_obj->getColumn('status_id');
			$type_id = $udt_obj->getColumn('type_id');

			$tmp_rows[$pay_period_id][$user_id][$date_stamp]['min_punch_time_stamp'] = TTDate::strtotime( $udt_obj->getColumn('min_punch_time_stamp') );
			$tmp_rows[$pay_period_id][$user_id][$date_stamp]['max_punch_time_stamp'] = TTDate::strtotime( $udt_obj->getColumn('max_punch_time_stamp') );
							
			if ( ($status_id == 20 AND $type_id == 10 ) OR ($status_id == 10 AND $type_id == 100 ) ) {
				$tmp_rows[$pay_period_id][$user_id][$date_stamp]['worked_time'] += (int)$udt_obj->getColumn('total_time');
			}
		}
		
					
		$worked_days_no = 0;
		$late_days_no = 0;
		$nopay_days_no = 0;
		$full_day_leave_no = 0;
		$half_day_leave_no = 0;
		
		foreach($tmp_rows as $pp_id=>$user_data) {
			foreach ($user_data as $usr_id => $date_data) {
				foreach ($date_data as $date_stamp => $att_data) {

					$dt_stamp = new DateTime();
					$dt_stamp->setTimestamp($date_stamp);
					$current_date = $dt_stamp->format('Y-m-d');
					
					if((isset($schedule_rows[$pp_id][$usr_id][$date_stamp]['start_time']) && $schedule_rows[$pp_id][$usr_id][$date_stamp]['start_time'] !='' )&& (isset($att_data['min_punch_time_stamp'])&& $att_data['min_punch_time_stamp']!='')){
						
						$worked_days_no++;
						
						$late_time = TTDate::strtotime($schedule_rows[$pp_id][$usr_id][$date_stamp]['start_time']) - $att_data['min_punch_time_stamp'];
						
						if($late_time < 0){
						
							
							$alf_a = new AccrualListFactory();
		
							$alf_a->getByAccrualByUserIdAndTypeIdAndDate($usr_id,55,$current_date);
				
							if($alf_a->getRecordCount() > 0){

								// echo $late_time.'<br>';
							}
							else{
								$late_days_no++;
							}
						}
					}
					elseif((isset($schedule_rows[$pp_id][$usr_id][$date_stamp]['start_time']) && $schedule_rows[$pp_id][$usr_id][$date_stamp]['start_time'] !='' )&& (isset($att_data['min_punch_time_stamp'])&& $att_data['min_punch_time_stamp']=='') && (isset($att_data['max_punch_time_stamp'])&& $att_data['max_punch_time_stamp']=='')){
						
							$hlf = new HolidayListFactory();
						
						
							$hlf->getByPolicyGroupUserIdAndDate($usr_id, $current_date);
							$hday_obj_arr = $hlf->getCurrent()->data;

							if (!empty($hday_obj_arr)) {
								
							}
							else{
								
								
								
										$alf = new AccrualListFactory();
										$alf->getByAccrualByUserIdAndTypeIdAndDate($usr_id,55,$current_date);
										
										//  echo ' '.$date_stamp;
								
											if($alf->getRecordCount() > 0){
												
												$af_obj = $alf->getCurrent();
												// echo $late_time.'<br>'; getAmount()
												
											// echo ' '.$af_obj->getAmount();
												
												if($af_obj->getAmount()== -28800){
													
													$full_day_leave_no++;
												}
												elseif($af_obj->getAmount()==-14400){
													$half_day_leave_no++;
												}
											}
											else{
												
												// Used to calculate nopay days and place nopay on salary
												
												$ulf = new UserListFactory();
												$ulf->getById($usr_id);
												$user_obj = $ulf->getCurrent();
												
											// exclude directors from Nopay
											if($user_obj->getTitle()!= 2){ 
												
													$udlf = new UserDateListFactory();
													$udlf->getByUserIdAndDate($usr_id, $current_date);

													
													if($udlf->getRecordCount() >0){
														
													$ud_obj = $udlf->getCurrent();


													$udtlf = new UserDateTotalListFactory();
													$udtlf->getByUserDateId($ud_obj->getId());

													if($udtlf->getRecordCount() > 0){

														foreach($udtlf as $udt_obj){

															$udt_obj->setDeleted(TRUE);

															if( $udt_obj->isValid()){
																$udt_obj->Save(); 
															}
														}
													}

													
													if(($user_obj->getTerminationDate()!='' && $user_obj->getTerminationDate() >= $dt_stamp->getTimestamp() )|| $user_obj->getTerminationDate() == ''){
													
													//// if($user_obj->getId()==1015 && $dt_stamp->getTimestamp()==1531420200){ echo $user_obj->getTerminationDate().'  MM'; }
													$udt_obj1 = new UserDateTotalFactory();

													$udt_obj1->setUserDateID($ud_obj->getId());
													$udt_obj1->setStatus(10);
													$udt_obj1->setType(10);
													$udt_obj1->setTotalTime(0);

													if( $udt_obj1->isValid()){
																$udt_obj1->Save(); 
													}

													$udt_obj2 = new UserDateTotalFactory();


													$udt_obj2->setUserDateID($ud_obj->getId());
													$udt_obj2->setStatus(30);
													$udt_obj2->setType(10);
													$udt_obj2->setTotalTime(28800);
													$udt_obj2->setAbsencePolicyID(10);
													$udt_obj2->setDepartment($user_obj->getDefaultDepartment());
													$udt_obj2->setBranch($user_obj->getDefaultBranch());


													if( $udt_obj2->isValid()){
																$udt_obj2->Save(); 
													}

													unset($udt_obj1);
													unset($udt_obj2);
													unset($ulf);
													
													}
												}
											}
											
												$nopay_days_no++;
											}
							}
					}
					/*
					elseif((isset($schedule_rows[$pp_id][$usr_id][$date_stamp]['start_time']) && $schedule_rows[$pp_id][$usr_id][$date_stamp]['start_time'] !='' )&& (!isset($att_data['min_punch_time_stamp'])&& $att_data['min_punch_time_stamp']=='') && (isset($att_data['max_punch_time_stamp'])&& $att_data['max_punch_time_stamp']=='')){
						
							$hlf = new HolidayListFactory();
						
						
							$hlf->getByPolicyGroupUserIdAndDate($usr_id, $current_date);
							$hday_obj_arr = $hlf->getCurrent()->data;

							if (!empty($hday_obj_arr)) {
								
							}
							else{
								
								
								
										$alf = new AccrualListFactory();
										$alf->getByAccrualByUserIdAndTypeIdAndDate($usr_id,55,$current_date);
										
										//  echo ' '.$date_stamp;
								
											if($alf->getRecordCount() > 0){
												
												$af_obj = $alf->getCurrent();
												// echo $late_time.'<br>'; getAmount()
												
											// echo ' '.$af_obj->getAmount();
												
												if($af_obj->getAmount()== -28800){
													
													$full_day_leave_no++;
												}
												elseif($af_obj->getAmount()==-14400){
													$half_day_leave_no++;
												}
											}
											else{
												
												// Used to calculate nopay days and place nopay on salary
												
												$ulf = new UserListFactory();
												$ulf->getById($usr_id);
												$user_obj = $ulf->getCurrent();
												
											// exclude directors from Nopay
											if($user_obj->getTitle()!= 2){ 
												
													$udlf = new UserDateListFactory();
													$udlf->getByUserIdAndDate($usr_id, $current_date);

													
													if($udlf->getRecordCount() >0){
														
													$ud_obj = $udlf->getCurrent();


													$udtlf = new UserDateTotalListFactory();
													$udtlf->getByUserDateId($ud_obj->getId());

													if($udtlf->getRecordCount() > 0){

														foreach($udtlf as $udt_obj){

															$udt_obj->setDeleted(TRUE);

															if( $udt_obj->isValid()){
																$udt_obj->Save(); 
															}
														}
													}

													
													if(($user_obj->getTerminationDate()!='' && $user_obj->getTerminationDate() >= $dt_stamp->getTimestamp() )|| $user_obj->getTerminationDate() == ''){
													
													//// if($user_obj->getId()==1015 && $dt_stamp->getTimestamp()==1531420200){ echo $user_obj->getTerminationDate().'  MM'; }
													$udt_obj1 = new UserDateTotalFactory();

													$udt_obj1->setUserDateID($ud_obj->getId());
													$udt_obj1->setStatus(10);
													$udt_obj1->setType(10);
													$udt_obj1->setTotalTime(0);

													if( $udt_obj1->isValid()){
																$udt_obj1->Save(); 
													}

													$udt_obj2 = new UserDateTotalFactory();


													$udt_obj2->setUserDateID($ud_obj->getId());
													$udt_obj2->setStatus(30);
													$udt_obj2->setType(10);
													$udt_obj2->setTotalTime(28800);
													$udt_obj2->setAbsencePolicyID(10);
													$udt_obj2->setDepartment($user_obj->getDefaultDepartment());
													$udt_obj2->setBranch($user_obj->getDefaultBranch());


													if( $udt_obj2->isValid()){
																$udt_obj2->Save(); 
													}

													unset($udt_obj1);
													unset($udt_obj2);
													unset($ulf);
													
													}
												}
											}
											
												$nopay_days_no++;
											}
							}
					}
					*/
					elseif( (isset($att_data['min_punch_time_stamp'])&& $att_data['min_punch_time_stamp']!='')){
						
						// echo $att_data['worked_time'] ;
						
						// echo $schedule_rows[$pp_id][$usr_id][$date_stamp]['start_time'];
						if($att_data['worked_time'] >= 14400){
							
							// echo "gone";
							$worked_days_no++;
						}
						
					}
					
				
				}// end foreach datestamp
				
			}// end of user foreach
			
		}// end of payperiods  foreach
					
					// echo $this->getPayPeriod(); exit;
					
		$allf = new AllowanceListFactory();
		$allf->getByUserIdAndPayperiodsId($this->getUser(), $this->getPayPeriod());
		
		if($allf->getRecordCount() >0){
			
			$alf_obj = $allf->getCurrent();
			
			
				$alf_obj->setUser($this->getUser());
				$alf_obj->setPayPeriod($this->getPayPeriod());
				$alf_obj->setWorkedDays($worked_days_no);
				$alf_obj->setLateDays($late_days_no);
				$alf_obj->setNopayDays($nopay_days_no);
				$alf_obj->setFulldayLeaveDays($full_day_leave_no);
				$alf_obj->setHalfdayLeaveDays($half_day_leave_no);

				if($alf_obj->isValid()){
					$alf_obj->Save();
				}
			
		}else{
		
				$alf = new AllowanceFactory();

				$alf->setUser($this->getUser());
				$alf->setPayPeriod($this->getPayPeriod());
				$alf->setWorkedDays($worked_days_no);
				$alf->setLateDays($late_days_no);
				$alf->setNopayDays($nopay_days_no);
				$alf->setFulldayLeaveDays($full_day_leave_no);
				$alf->setHalfdayLeaveDays($half_day_leave_no);

				if($alf->isValid()){
					$alf->Save();
				}
		}
	}
	
	
	function removeTerminatePayStub(){
		
		$plf = new PayStubListFactory();
		$plf->getByPayperiodsIdAndUserId($this->getPayPeriod(), $this->getUser());
			
			if($plf->getRecordCount() > 0){
				
				$ps_obj = $plf->getCurrent();
					
				$ps_obj->setDeleted(TRUE);
				
				$ps_obj->save();
				
			}
		
		/*
		$ulf = new UserListFactory();
		//$ulf =  new PayStubMiddlePayFactory();
		
		$ulf->getTerminationByPayperiod($this->getPayPeriod());
		
		foreach ($ulf as $u_obj){
			
			$plf = new PayStubListFactory();
			$plf->getByPayperiodsIdAndUserId($this->getPayPeriod(), $u_obj->getId());
			
			if($plf->getRecordCount() > 0){
				
				$ps_obj = $plf->getCurrent();
					
				$ps_obj->setDelete(TRUE);
				
				$ps_obj->save();
				
			}
		}
			
			*/
	}
}
?>
