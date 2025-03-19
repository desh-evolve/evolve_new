<?php

namespace App\Models\Core;

use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\PayStub\PayStubEntryAccountLinkListFactory;
use App\Models\PayStubAmendment\PayStubAmendmentListFactory;
use App\Models\Users\UserDeductionListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserWageFactory;
use App\Models\Users\UserWageListFactory;

class Wage {
	var $user_id = NULL;
	var $pay_period_id = NULL;
	var $advance = FALSE;

	var $user_date_total_arr = NULL;
        var $user_date_total_arr_mid_pay = NULL;

	var $user_obj = NULL;
	var $user_tax_obj = NULL;
	var $user_wage_obj = NULL;
	var $user_pay_period_total_obj = NULL;
	var $pay_stub_entry_account_link_obj = NULL;

	var $pay_period_obj = NULL;
	var $pay_period_schedule_obj = NULL;

	var $labor_standard_obj = NULL;
	var $holiday_obj = NULL;

	function __construct($user_id, $pay_period_id) {
		$this->user_id = $user_id;
		$this->pay_period_id = $pay_period_id;

		return TRUE;
	}

	function getUser() {
		return $this->user_id;
	}

	function getPayPeriod() {
		return $this->pay_period_id;
	}

	function getAdvance() {
		if ( isset($this->advance) ) {
			return $this->advance;
		}

		return FALSE;
	}
	function setAdvance($bool) {
		$this->advance = $bool;

		return TRUE;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
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

	function getUserTaxObject() {
		if ( is_object($this->user_tax_obj) ) {
			return $this->user_tax_obj;
		} else {

			$utlf = new UserTaxListFactory();
			$utlf->getByUserId( $this->getUser() );
			if ( $utlf->getRecordCount() > 0 ) {
				$this->user_tax_obj = $utlf->getCurrent();

				return $this->user_tax_obj;
			}

			return FALSE;
		}
	}

	function getUserWageObject( $user_wage_id ) {
		if ( isset($this->user_wage_obj[$user_wage_id])
			AND is_object($this->user_wage_obj[$user_wage_id]) ) {
			return $this->user_wage_obj[$user_wage_id];
		} else {
			$uwlf = new UserWageListFactory();

			$uwlf->getByID( $user_wage_id );
			if ( $uwlf->getRecordCount() > 0 ) {
				$this->user_wage_obj[$user_wage_id] = $uwlf->getCurrent();

				return $this->user_wage_obj[$user_wage_id];
			}

			return FALSE;
		}
	}

	function getPayPeriodObject() {
		if ( is_object($this->pay_period_obj) ) {
			return $this->pay_period_obj;
		} else {
			$pplf = new PayPeriodListFactory();

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

	function getLaborStandardObject() {
		if ( is_object($this->labor_standard_obj) ) {
			return $this->labor_standard_obj;
		} else {
			$ls = new LaborStandard();
			$ls->getByCountryAndProvince( $this->getUserObject()->getCountry() , $this->getUserObject()->getProvince() );
			$this->labor_standard_obj = $ls;

			return $this->labor_standard_obj;
		}
	}

	static function getRemittanceDueDate($transaction_epoch, $avg_monthly_remittance) {
		Debug::text('Transaction Date: '. TTDate::getDate('DATE+TIME', $transaction_epoch) , __FILE__, __LINE__, __METHOD__,10);

		if ( $avg_monthly_remittance < 15000 ) {
			Debug::text('Regular Monthly' , __FILE__, __LINE__, __METHOD__,10);
			//15th of the month FOLLOWING transaction_epoch.
			$due_date = mktime(0,0,0,date('n',$transaction_epoch)+1,15,date('y',$transaction_epoch) );
		} elseif ( $avg_monthly_remittance >= 15000 AND $avg_monthly_remittance < 49999.99 ) {
			Debug::text('Accelerated Threshold 1' , __FILE__, __LINE__, __METHOD__,10);
			/*
			Amounts you deduct or withhold from remuneration paid in the first 15 days of the month
			are due by the 25th of the same month. Amounts you withhold from the 16th to the end of
			the month are due by the 10th day of the following month.
			*/
			if ( date('j', $transaction_epoch) <= 15 ) {
				$due_date = mktime(0,0,0,date('n',$transaction_epoch),25,date('y',$transaction_epoch) );
			} else {
				$due_date = mktime(0,0,0,date('n',$transaction_epoch)+1,10,date('y',$transaction_epoch) );
			}
		} elseif ( $avg_monthly_remittance > 50000) {
			Debug::text('Accelerated Threshold 2' , __FILE__, __LINE__, __METHOD__,10);
			/*
			Amounts you deduct or withhold from remuneration you pay any time during the month are due by the third working day (not counting Saturdays, Sundays, or holidays) after the end of the following periods:

			* from the 1st through the 7th day of the month;
			* from the 8th through the 14th day of the month;
			* from the 15th through the 21st day of the month; and
			* from the 22nd through the last day of the month.
			*/
			return FALSE;
		}

		return $due_date;
	}

	function getHourlyRate( $user_wage_id ) {
		if ( is_object($this->getUserWageObject( $user_wage_id ) ) ) {
			return $this->getUserWageObject( $user_wage_id )->getHourlyRate( FALSE, FALSE, $this->getPayPeriodObject()->getStartDate() );
		}

		return 0;
	}

	function getWage($seconds, $rate ) {
		if ( $seconds == '' OR empty($seconds) ) {
			return 0;
		}

		if ( $rate == '' OR empty($rate) ) {
			return 0;
		}

		return bcmul( TTDate::getHours( $seconds ), $rate );
	}

	function getMaximumPayPeriodWage( $user_wage_id ) {
		Debug::text('Absolute Maximum Pay Period NO Advance: User Wage ID: '. $user_wage_id .'  Annual Wage: '. $this->getUserWageObject( $user_wage_id )->getAnnualWage() .' Annual Pay Periods: '. $this->getPayPeriodScheduleObject()->getAnnualPayPeriods(), __FILE__, __LINE__, __METHOD__,10);
		$maximum_pay_period_wage = bcdiv( $this->getUserWageObject( $user_wage_id )->getAnnualWage(), $this->getPayPeriodScheduleObject()->getAnnualPayPeriods() );
		Debug::text('Absolute Maximum Pay Period Wage: '. $maximum_pay_period_wage, __FILE__, __LINE__, __METHOD__,10);

		return $maximum_pay_period_wage;
	}

	function getPayStubAmendmentEarnings() {
		//Get pay stub amendments here.
		$psalf = new PayStubAmendmentListFactory();

		if ( $this->getAdvance() == TRUE ) {
			//For advances, any PS amendment effective BEFORE the advance end date is considered in full.
			//Any AFTER the advance end date, is considered half.

			//$pay_period_end_date = $this->getPayPeriodObject()->getAdvanceEndDate();
			$advance_pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getAdvanceEndDate() );
			Debug::text('Pay Stub Amendment Advance Earnings: '. $advance_pos_sum , __FILE__, __LINE__, __METHOD__,10);

			$full_pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, TRUE, $this->getPayPeriodObject()->getAdvanceEndDate(), $this->getPayPeriodObject()->getEndDate() );
			Debug::text('Pay Stub Amendment Full Earnings: '. $full_pos_sum , __FILE__, __LINE__, __METHOD__,10);
			//Take the full amount of PS amendments BEFORE the advance end date, and half of any AFTER the advance end date.
			//$pos_sum = $advance_pos_sum + ($full_pos_sum / 2);
			$pos_sum = bcadd( $advance_pos_sum, bcdiv( $full_pos_sum, 2 ) );
		} else {
			//$pay_period_end_date =
			$pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );
		}
		//$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndTaxExemptAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, FALSE, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() )*-1;

		Debug::text('Pay Stub Amendment Total Earnings: '. $pos_sum , __FILE__, __LINE__, __METHOD__,10);

		return $pos_sum;
	}

	function getPayStubAmendmentDeductions() {
		//Get pay stub amendments here.
		$psalf = new PayStubAmendmentListFactory();

		if ( $this->getAdvance() == TRUE ) {
			//For advances, any PS amendment effective BEFORE the advance end date is considered in full.
			//Any AFTER the advance end date, is considered half.

			//$pay_period_end_date = $this->getPayPeriodObject()->getAdvanceEndDate();
			$advance_neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getAdvanceEndDate() );
			Debug::text('Pay Stub Amendment Advance Deductions: '. $advance_neg_sum , __FILE__, __LINE__, __METHOD__,10);

			$full_neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, TRUE, $this->getPayPeriodObject()->getAdvanceEndDate(), $this->getPayPeriodObject()->getEndDate() );
			Debug::text('Pay Stub Amendment Full Deductions: '. $full_neg_sum , __FILE__, __LINE__, __METHOD__,10);
			//Take the full amount of PS amendments BEFORE the advance end date, and half of any AFTER the advance end date.
			//$neg_sum = $advance_neg_sum + ($full_neg_sum / 2);
			$neg_sum = bcadd( $advance_neg_sum, bcdiv( $full_neg_sum, 2 ) );
		} else {
			//$pay_period_end_date =
			$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );
		}
		//$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndTaxExemptAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, FALSE, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() )*-1;


		Debug::text('Pay Stub Amendment Total Deductions: '. $neg_sum , __FILE__, __LINE__, __METHOD__,10);

		return bcmul( $neg_sum, -1 );
	}

	function getRawGrossWage() {
		$wage = 0;

		$udt_arr = $this->getUserDateTotalArray();
		if ( isset($udt_arr['entries']) AND count($udt_arr['entries']) > 0 ) {
			foreach( $udt_arr['entries'] as $udt ) {
				if ( isset($udt['amount']) ) {
					$wage += $udt['amount'];
				}
			}
		}

		Debug::text('Raw Gross Wage: '. $wage , __FILE__, __LINE__, __METHOD__,10);
		return $wage;
	}

	function getGrossWage() {

		$wage = $this->getRawGrossWage();

		Debug::text('Gross Wage (NOT incl amendments) $'. $wage , __FILE__, __LINE__, __METHOD__,10);

		return $wage;
	}

	function getUserDateTotalArray() {
		if ( isset($this->user_date_total_arr) ) {
			return $this->user_date_total_arr;
		}

		//If the user date total array isn't set, set it now, and return its value.
		return $this->setUserDateTotalArray();
		//return FALSE;
	}

	function setUserDateTotalArray() {
	//            echo '<pre>';
	//            echo '<br>user :: '.$this->getUser();
	//            echo '<br>pay period:: '.$this->getPayPeriod();
	//            echo '<br>pay period end :: '.date('Y-m-d',$this->getPayPeriodObject()->getEndDate());
		//Loop through unique UserDateTotal rows... Adding entries to pay stubs.
		$udtlf = new UserDateTotalListFactory();
		$udtlf->getByUserIdAndPayPeriodIdAndEndDate( $this->getUser(), $this->getPayPeriod(), $this->getPayPeriodObject()->getEndDate() );

		$dock_absence_time = 0;
		$paid_absence_time = 0;
		$dock_absence_amount = 0;
		$paid_absence_amount = 0;
		$prev_wage_effective_date = 0;
		if ( $udtlf->getRecordCount() > 0 ) {
                    
			foreach( $udtlf->rs as $udt_obj ) {
				$udtlf->data = (array)$udt_obj;
				$udt_obj = $udtlf;
				//                            echo '<br>------------';
				//                            echo '<br>type:: '.$udt_obj->getType();
				//                            echo '<br>status:: '.$udt_obj->getStatus();
				//                            echo '<br>name:: '.$udt_obj->getAbsencePolicyObjectAqua()->getName();
				//                            echo '<br>adsence policy:: '.$udt_obj->getAbsencePolicyID();
											
				//                            print_r($udt_obj->getAbsencePolicyObjectAqua()->data);
				//                            echo '====================';
				//                            print_r($udt_obj->getAbsencePolicyObject()->data);
                            
                            
				Debug::text('User Total Row... Type: '. $udt_obj->getType() .' OverTime Policy ID: '. $udt_obj->getOverTimePolicyID() .' User Wage ID: '. $udt_obj->getColumn('user_wage_id') , __FILE__, __LINE__, __METHOD__,10);

				if ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 20 ) { //Regular Time
					Debug::text('User Total Row... Regular Time: '. $udt_obj->getTotalTime()  , __FILE__, __LINE__, __METHOD__,10);

					//Check if they are a salary user...
					//Use WORKED time to calculate regular time. Not just regular time.
					if ( is_object($this->getUserWageObject( $udt_obj->getColumn('user_wage_id') ))
							AND $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getType() != 10 ) {
						//Salary
						Debug::text('Strict Salary Wage: Reduce Regular Pay By: Dock Time: '. $dock_absence_time .' and Paid Absence: '. $paid_absence_time, __FILE__, __LINE__, __METHOD__,10);

						if ( isset($dock_absence_amount_arr[$udt_obj->getColumn('user_wage_id')]) ) {
							$dock_absence_wage = $dock_absence_amount_arr[$udt_obj->getColumn('user_wage_id')];
						} else {
							$dock_absence_wage = 0;
						}
						if ( isset($reduce_salary_absence_amount_arr[$udt_obj->getColumn('user_wage_id')]) ) {
							$paid_absence_wage = $reduce_salary_absence_amount_arr[$udt_obj->getColumn('user_wage_id')];
						} else {
							$paid_absence_wage = 0;
						}
						Debug::text('Wage ID: '. $udt_obj->getColumn('user_wage_id') .' Dock Absence Wage: '. $dock_absence_wage .' Paid Absence Wage: '. $paid_absence_wage, __FILE__, __LINE__, __METHOD__,10);
                                                
                                                
                                                
                                                /*
                                                  $plf = new PayStubListFactory();
                                                  $plf->getByPayperiodsIdAndUserIdForPayroll($this->getPayPeriod(), $this->getUser());
                                                
                                                   if($plf->getRecordCount() > 0){
                                                       
                                                    $budgetary_allowance = 0;
                                                    $udlf = new UserDeductionListFactory();
                                                    $udlf->getByUserIdAndCompanyDeductionId($this->getUser(), 3);
                                                    if($udlf->getRecordCount()>0){
                                                        foreach ($udlf as $udlf_obj1){
                                                           $budgetary_allowance = $udlf_obj1->getUserValue1();
                                                           $budgetary_allowance =   UserWageFactory::proReversRateBudgetory( $budgetary_allowance , $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getTerminationDate() );
                                                       
                                                           $udlf_obj1->setUserValue1($budgetary_allowance);
                                                           
                                                            if($udlf_obj1->isValid()){
                                                                  $udlf_obj1->save();
                                                              }
                                                          
                                                        }
                                                    }
                                                   }
                                                   */
                                              $turmination_date = $this->getUserObject()->getTerminationDate();
                                                
                                              if(isset($turmination_date) && !empty($turmination_date))
                                              {
                                                $budgetary_allowance = 0;
                                                
                                                $uwlf = new UserWageListFactory();
                                                $uwlf->getByUserId($this->getUser());
                                                 if($uwlf->getRecordCount()>0){
                                                     
                                                     $uw_obj = $uwlf->getCurrent();
                                                     
                                                     $budgetary_allowance = $uw_obj->getBudgetoryAllowance();
                                                     $budgetary_allowance =   UserWageFactory::proRateBudgetory( $budgetary_allowance , $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getTerminationDate() );
                                                              
                                                     $udlf = new UserDeductionListFactory();
                                                     $udlf->getByUserIdAndCompanyDeductionId($this->getUser(), 3);
                                                     
                                                     if($udlf->getRecordCount()>0){
                                                         
                                                         $udlf_obj = $udlf->getCurrent();
                                                         
                                                         $udlf_obj->setUserValue1($budgetary_allowance);
                                                              
                                                              if($udlf_obj->isValid()){
                                                                  $udlf_obj->save();
                                                              }
                                                     }
                                                     
                                                 }
                                                
                                             }
                                                 /*  
                                                 $budgetary_allowance = 0;
                                                    $udlf = new UserDeductionListFactory();
                                                    $udlf->getByUserIdAndCompanyDeductionId($this->getUser(), 3);
                                                    if($udlf->getRecordCount()>0){
                                                        foreach ($udlf as $udlf_obj){
                                                            $budgetary_allowance = $udlf_obj->getUserValue1();
                                                            
                                                              $budgetary_allowance =   UserWageFactory::proRateBudgetory( $budgetary_allowance , $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getTerminationDate() );
                                                              $udlf_obj->setUserValue1($budgetary_allowance);
                                                              
                                                              if($udlf_obj->isValid()){
                                                                  $udlf_obj->save();
                                                              }
                                                             
                                                             
                                                        }
                                                    }
					*/
						$maximum_wage_salary = UserWageFactory::proRateSalary( $this->getMaximumPayPeriodWage( $udt_obj->getColumn('user_wage_id') ) , $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getTerminationDate() );
                                                 //  $budgetary_allowance =   UserWageFactory::proRateBudgetory( $budgetary_allowance , $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getTerminationDate() );
                                               // $maximum_wage_salary = bcadd( $maximum_wage_salary, $budgetary_allowance ) ;
                                                
						$prev_wage_effective_date = $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getEffectiveDate();

						//$amount = bcsub( $maximum_wage_salary, bcadd( $dock_absence_wage, $paid_absence_wage ) );
                                                
                                                $amount = $maximum_wage_salary;
                                                
						$total_time = $udt_obj->getTotalTime(); //Dont minus dock/paid absence time. Because its already not included.
						$rate = NULL;
						$pay_stub_entry = $this->getPayStubEntryAccountLinkObject()->getRegularTime();

						unset($dock_absence_wage, $paid_absence_wage);
					} else {
						//Hourly
						Debug::text('Hourly or Hourly + Bonus Wage', __FILE__, __LINE__, __METHOD__,10);
						$pay_stub_entry = $this->getPayStubEntryAccountLinkObject()->getRegularTime();
						$total_time = $udt_obj->getTotalTime();
						$rate = $this->getHourlyRate( $udt_obj->getColumn('user_wage_id') );
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
					}
					Debug::text('aPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
				} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 30 ) { //Overtime
					Debug::text('User Total Row... Overtime Time: '. $udt_obj->getTotalTime() , __FILE__, __LINE__, __METHOD__,10);

					//Get overtime policy info. Allow negative rates so they withdraw from pay stub accounts.
					if ( $udt_obj->getOverTimePolicyObject()->getRate() != 0 ) {
							Debug::text('Paid Overtime Time Policy... Rate: '. $udt_obj->getOverTimePolicyObject()->getRate(), __FILE__, __LINE__, __METHOD__,10);
						$pay_stub_entry = $udt_obj->getOverTimePolicyObject()->getPayStubEntryAccountId();
						$total_time = $udt_obj->getTotalTime();
						$rate = bcmul( $this->getHourlyRate( $udt_obj->getColumn('over_time_policy_wage_id') ), $udt_obj->getOverTimePolicyObject()->getRate() );
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
						Debug::text('bPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__,10);
					
                                        } else {
						Debug::text('NOT Paid Overtime Time Policy: ', __FILE__, __LINE__, __METHOD__,10);
					}

				} elseif ( $udt_obj->getStatus() == 30 AND $udt_obj->getType() == 10) { //Absence
					//                                    echo '<br>Absence';
					Debug::text('User Total Row... Absence Time: '. $udt_obj->getTotalTime()  , __FILE__, __LINE__, __METHOD__,10);
					//                                        echo '<br> policy  type::'.$udt_obj->getAbsencePolicyObjectAqua()->getType();
					if ( is_object( $udt_obj->getAbsencePolicyObjectAqua() )
							AND ( $udt_obj->getAbsencePolicyObjectAqua()->getType() == 10 OR $udt_obj->getAbsencePolicyObjectAqua()->getType() == 12 )
							AND $udt_obj->getAbsencePolicyObject()->getPayStubEntryAccountID() != '') { //Paid
						Debug::text('Paid Absence Time: '. $udt_obj->getTotalTime() , __FILE__, __LINE__, __METHOD__,10);

						$pay_stub_entry = (int)$udt_obj->getAbsencePolicyObjectAqua()->getPayStubEntryAccountID();
						$total_time = $udt_obj->getTotalTime();
						$rate = bcmul( $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') ), $udt_obj->getAbsencePolicyObjectAqua()->getRate() );
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
						//$rate = $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') );
						//$amount = $this->getWage( $udt_obj->getTotalTime(), $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') ) );

						//Debug::text('Paid Absence Info: '. $udt_obj->getTotalTime() , __FILE__, __LINE__, __METHOD__,10);
						Debug::text('cPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__,10);

						$paid_absence_time = bcadd($paid_absence_time, $udt_obj->getTotalTime() );
						$paid_absence_amount = bcadd( $paid_absence_amount, $amount);

						//Make sure we add the amount below. Incase there are two or more
						//entries for a paid absence in the same user_wage_id on one pay stub.
						if ( !isset($paid_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')]) ) {
							$paid_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = 0;
						}
						$paid_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = bcadd($paid_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')], $amount );

						//Some paid absences are over and above employees salary, so we need to track them separately.
						//So we only reduce the salary of the amount of regular paid absences, not "Paid (Above Salary)" absences.
						if ( !isset($reduce_salary_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')]) ) {
							$reduce_salary_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = 0;
						}
						if ( $udt_obj->getAbsencePolicyObjectAqua()->getType() == 10 ) {
							$reduce_salary_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = bcadd($reduce_salary_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')], $amount );
						}

					} elseif ( is_object($udt_obj->getAbsencePolicyObjectAqua()) AND $udt_obj->getAbsencePolicyObjectAqua()->getType() == 30 ) { //30
					//                                            echo '<br> Dock';
						$dock_absence_time = bcadd( $dock_absence_time, $udt_obj->getTotalTime() );
					//                                                echo '<br> Dock ab time::'.$dock_absence_time;
					//                                                echo '<br> policy wage::'.$udt_obj->getColumn('absence_policy_wage_id');
					//                                                echo '<br> H wage::'.$this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') );
					//                                                echo '<br> rate bcmul::'.$udt_obj->getAbsencePolicyObjectAqua();
						$rate = bcmul( $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') ), $udt_obj->getAbsencePolicyObjectAqua()->getRate() );
                                                
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
						//$amount = $this->getWage( $udt_obj->getTotalTime(), $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') ) );
						$dock_absence_amount = bcadd( $dock_absence_amount, $amount );
						$dock_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = $amount;
						Debug::text('DOCK Absence Time.. Adding: '. $udt_obj->getTotalTime() .' Total: '. $dock_absence_time .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__,10);
						unset($rate);
					}
				} elseif (  $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 40 ) { //Premium
					Debug::text('User Total Row... Premium Time: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);

					//Get premium policy info.
					if ( is_object( $udt_obj->getPremiumPolicyObject() ) AND $udt_obj->getPremiumPolicyObject()->getRate() != 0 ) {
						Debug::text('Paid Premium Time Policy... Rate: '. $udt_obj->getPremiumPolicyObject()->getRate(), __FILE__, __LINE__, __METHOD__,10);

						switch ( $udt_obj->getPremiumPolicyObject()->getPayType() ) {
							case 10: //Pay Factor
								//Since they are already paid for this time with regular or OT, minus 1 from the rate
								$rate = bcmul( $this->getHourlyRate( $udt_obj->getColumn('premium_policy_wage_id') ), bcsub( $udt_obj->getPremiumPolicyObject()->getRate(), 1 ) );
								break;
							case 20: //Pay Plus Premium
								$rate = $udt_obj->getPremiumPolicyObject()->getRate();
								break;
							case 30: //Flat Hourly Rate
								//Get the difference between the employees current wage and the premium wage.
								$rate = bcsub( $udt_obj->getPremiumPolicyObject()->getRate(), $this->getHourlyRate( $udt_obj->getColumn('premium_policy_wage_id') ) );
								break;
						}

						$pay_stub_entry = $udt_obj->getPremiumPolicyObject()->getPayStubEntryAccountId();
						$total_time = $udt_obj->getTotalTime();
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
						Debug::text('dPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__,10);
					} else {
						Debug::text('NOT Paid Premium Time Policy: ', __FILE__, __LINE__, __METHOD__,10);
					}

				}

				if ( isset($pay_stub_entry) AND $pay_stub_entry != '' ) {
					Debug::text('zPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
					$ret_arr['entries'][] = array(	'user_wage_id' => $udt_obj->getColumn('user_wage_id'),
                                                            'pay_stub_entry' => $pay_stub_entry,
                                                            'total_time' =>  $total_time,
                                                            'amount' => $amount,
                                                            'rate' => $rate
                                                    );
				}
				unset($pay_stub_entry, $amount, $total_time, $rate);
			}
                        
                         // Director salary process without attendance
                       
                          
                        if($this->getUserObject()->getTitle()==2){
                          
                            $uwf = new UserWageListFactory();
                            $uwf->getWageByUserIdAndPayPeriodEndDate($this->getUser(), $this->getPayPeriodObject()->getEndDate());
                            
                            $amount = $uwf->getCurrent()->getWage();
                            $total_time = 240*3600; //Dont minus dock/paid absence time. Because its already not included.
			    $rate = NULL;
			    $pay_stub_entry = $this->getPayStubEntryAccountLinkObject()->getRegularTime();
                            
                            $ret_arr['entries'][] = array(	'user_wage_id' => $udt_obj->getColumn('user_wage_id'),
                                                            'pay_stub_entry' => $pay_stub_entry,
                                                            'total_time' =>  $total_time,
                                                            'amount' => $amount,
                                                            'rate' => $rate
                                                    );
                            
                            
                            unset($pay_stub_entry, $amount, $total_time, $rate);
                            
                        }
                      
		} else {
			Debug::text('NO UserDate Total entries found.', __FILE__, __LINE__, __METHOD__,10);
                        
                       
                        
                        
		}

		$ret_arr['other']['paid_absence_time'] = $paid_absence_time;
		$ret_arr['other']['dock_absence_time'] = $dock_absence_time;

		$ret_arr['other']['paid_absence_amount'] = $paid_absence_amount;
		$ret_arr['other']['dock_absence_amount'] = $dock_absence_amount;
                

		if ( isset($ret_arr) ) {
			Debug::Arr($ret_arr, 'UserDateTotal Array', __FILE__, __LINE__, __METHOD__,10);
			return $this->user_date_total_arr = $ret_arr;
		}

		return FALSE;
	}
        
        
        
        //////////////////////////////////add-by-thusitha--//////////////////////////
        
        
        
	function getUserDateTotalArrayMiddlePay($check_date) {
		if ( isset($this->$user_date_total_arr_mid_pay) ) {
			return $this->$user_date_total_arr_mid_pay;
		}

		//If the user date total array isn't set, set it now, and return its value.
		return $this->setUserDateTotalArrayMiddlePay($check_date);
		//return FALSE;
	}

	function setUserDateTotalArrayMiddlePay($check_date) {

		//Loop through unique UserDateTotal rows... Adding entries to pay stubs. $this->getPayPeriodObject()->getEndDate()
		$udtlf = new UserDateTotalListFactory();
		$udtlf->getByUserIdAndPayPeriodIdAndEndDate( $this->getUser(), $this->getPayPeriod(), $check_date );

		$dock_absence_time = 0;
		$paid_absence_time = 0;
		$dock_absence_amount = 0;
		$paid_absence_amount = 0;
		$prev_wage_effective_date = 0;
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach( $udtlf->rs as $udt_obj ) {
				$udtlf->data = (array)$udt_obj;
				$udt_obj = $udtlf;
				Debug::text('User Total Row... Type: '. $udt_obj->getType() .' OverTime Policy ID: '. $udt_obj->getOverTimePolicyID() .' User Wage ID: '. $udt_obj->getColumn('user_wage_id') , __FILE__, __LINE__, __METHOD__,10);

				if ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 20 ) { //Regular Time
					Debug::text('User Total Row... Regular Time: '. $udt_obj->getTotalTime()  , __FILE__, __LINE__, __METHOD__,10);

					//Check if they are a salary user...
					//Use WORKED time to calculate regular time. Not just regular time.
					if ( is_object($this->getUserWageObject( $udt_obj->getColumn('user_wage_id') ))
							AND $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getType() != 10 ) {
						//Salary
						Debug::text('Strict Salary Wage: Reduce Regular Pay By: Dock Time: '. $dock_absence_time .' and Paid Absence: '. $paid_absence_time, __FILE__, __LINE__, __METHOD__,10);

						if ( isset($dock_absence_amount_arr[$udt_obj->getColumn('user_wage_id')]) ) {
							$dock_absence_wage = $dock_absence_amount_arr[$udt_obj->getColumn('user_wage_id')];
						} else {
							$dock_absence_wage = 0;
						}
						if ( isset($reduce_salary_absence_amount_arr[$udt_obj->getColumn('user_wage_id')]) ) {
							$paid_absence_wage = $reduce_salary_absence_amount_arr[$udt_obj->getColumn('user_wage_id')];
						} else {
							$paid_absence_wage = 0;
						}
						Debug::text('Wage ID: '. $udt_obj->getColumn('user_wage_id') .' Dock Absence Wage: '. $dock_absence_wage .' Paid Absence Wage: '. $paid_absence_wage, __FILE__, __LINE__, __METHOD__,10);

						$maximum_wage_salary = UserWageFactory::proRateSalary( $this->getMaximumPayPeriodWage( $udt_obj->getColumn('user_wage_id') ) , $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getTerminationDate() );

						$prev_wage_effective_date = $this->getUserWageObject( $udt_obj->getColumn('user_wage_id') )->getEffectiveDate();

						$amount = bcsub( $maximum_wage_salary, bcadd( $dock_absence_wage, $paid_absence_wage ) );
                                                
                                                
						$total_time = $udt_obj->getTotalTime(); //Dont minus dock/paid absence time. Because its already not included.
						$rate = NULL;
						$pay_stub_entry = $this->getPayStubEntryAccountLinkObject()->getRegularTime();

						unset($dock_absence_wage, $paid_absence_wage);
					} else {
						//Hourly
						Debug::text('Hourly or Hourly + Bonus Wage', __FILE__, __LINE__, __METHOD__,10);
						$pay_stub_entry = $this->getPayStubEntryAccountLinkObject()->getRegularTime();
						$total_time = $udt_obj->getTotalTime();
						$rate = $this->getHourlyRate( $udt_obj->getColumn('user_wage_id') );
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
					}
					Debug::text('aPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
				} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 30 ) { //Overtime
					Debug::text('User Total Row... Overtime Time: '. $udt_obj->getTotalTime() , __FILE__, __LINE__, __METHOD__,10);

					//Get overtime policy info. Allow negative rates so they withdraw from pay stub accounts.
					if ( $udt_obj->getOverTimePolicyObject()->getRate() != 0 ) {
						Debug::text('Paid Overtime Time Policy... Rate: '. $udt_obj->getOverTimePolicyObject()->getRate(), __FILE__, __LINE__, __METHOD__,10);
						//$pay_stub_entry = $udt_obj->getOverTimePolicyObject()->getPayStubEntryAccountId();
                                                $pay_stub_entry ='';
                                                
						$total_time = $udt_obj->getTotalTime();
						$rate = bcmul( $this->getHourlyRate( $udt_obj->getColumn('over_time_policy_wage_id') ), $udt_obj->getOverTimePolicyObject()->getRate() );
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
						Debug::text('bPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__,10);
					} else {
						Debug::text('NOT Paid Overtime Time Policy: ', __FILE__, __LINE__, __METHOD__,10);
					}

				} elseif ( $udt_obj->getStatus() == 30 AND $udt_obj->getType() == 10) { //Absence
					Debug::text('User Total Row... Absence Time: '. $udt_obj->getTotalTime()  , __FILE__, __LINE__, __METHOD__,10);

					if ( is_object( $udt_obj->getAbsencePolicyObject() )
							AND ( $udt_obj->getAbsencePolicyObject()->getType() == 10 OR $udt_obj->getAbsencePolicyObject()->getType() == 12 )
							AND $udt_obj->getAbsencePolicyObject()->getPayStubEntryAccountID() != '') { //Paid
						Debug::text('Paid Absence Time: '. $udt_obj->getTotalTime() , __FILE__, __LINE__, __METHOD__,10);

						$pay_stub_entry = (int)$udt_obj->getAbsencePolicyObject()->getPayStubEntryAccountID();
						$total_time = $udt_obj->getTotalTime();
						$rate = bcmul( $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') ), $udt_obj->getAbsencePolicyObject()->getRate() );
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
						//$rate = $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') );
						//$amount = $this->getWage( $udt_obj->getTotalTime(), $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') ) );

						//Debug::text('Paid Absence Info: '. $udt_obj->getTotalTime() , __FILE__, __LINE__, __METHOD__,10);
						Debug::text('cPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__,10);

						$paid_absence_time = bcadd($paid_absence_time, $udt_obj->getTotalTime() );
						$paid_absence_amount = bcadd( $paid_absence_amount, $amount);

						//Make sure we add the amount below. Incase there are two or more
						//entries for a paid absence in the same user_wage_id on one pay stub.
						if ( !isset($paid_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')]) ) {
							$paid_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = 0;
						}
						$paid_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = bcadd($paid_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')], $amount );

						//Some paid absences are over and above employees salary, so we need to track them separately.
						//So we only reduce the salary of the amount of regular paid absences, not "Paid (Above Salary)" absences.
						if ( !isset($reduce_salary_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')]) ) {
							$reduce_salary_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = 0;
						}
						if ( $udt_obj->getAbsencePolicyObject()->getType() == 10 ) {
							$reduce_salary_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = bcadd($reduce_salary_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')], $amount );
						}

					} elseif ( is_object($udt_obj->getAbsencePolicyObject()) AND $udt_obj->getAbsencePolicyObject()->getType() == 30 ) {
                                            echo '<br>Dock';
						$dock_absence_time = bcadd( $dock_absence_time, $udt_obj->getTotalTime() );
						$rate = bcmul( $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') ), $udt_obj->getAbsencePolicyObject()->getRate() );
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
						//$amount = $this->getWage( $udt_obj->getTotalTime(), $this->getHourlyRate( $udt_obj->getColumn('absence_policy_wage_id') ) );
						$dock_absence_amount = bcadd( $dock_absence_amount, $amount );
						$dock_absence_amount_arr[$udt_obj->getColumn('absence_policy_wage_id')] = $amount;
						Debug::text('DOCK Absence Time.. Adding: '. $udt_obj->getTotalTime() .' Total: '. $dock_absence_time .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__,10);
						unset($rate);
					}
				} elseif (  $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 40 ) { //Premium
					Debug::text('User Total Row... Premium Time: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);

					//Get premium policy info.
					if ( is_object( $udt_obj->getPremiumPolicyObject() ) AND $udt_obj->getPremiumPolicyObject()->getRate() != 0 ) {
						Debug::text('Paid Premium Time Policy... Rate: '. $udt_obj->getPremiumPolicyObject()->getRate(), __FILE__, __LINE__, __METHOD__,10);

						switch ( $udt_obj->getPremiumPolicyObject()->getPayType() ) {
							case 10: //Pay Factor
								//Since they are already paid for this time with regular or OT, minus 1 from the rate
								$rate = bcmul( $this->getHourlyRate( $udt_obj->getColumn('premium_policy_wage_id') ), bcsub( $udt_obj->getPremiumPolicyObject()->getRate(), 1 ) );
								break;
							case 20: //Pay Plus Premium
								$rate = $udt_obj->getPremiumPolicyObject()->getRate();
								break;
							case 30: //Flat Hourly Rate
								//Get the difference between the employees current wage and the premium wage.
								$rate = bcsub( $udt_obj->getPremiumPolicyObject()->getRate(), $this->getHourlyRate( $udt_obj->getColumn('premium_policy_wage_id') ) );
								break;
						}

						//$pay_stub_entry = $udt_obj->getPremiumPolicyObject()->getPayStubEntryAccountId();
                                                
                                                $pay_stub_entry = '';
						$total_time = $udt_obj->getTotalTime();
						$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
						Debug::text('dPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__,10);
					} else {
						Debug::text('NOT Paid Premium Time Policy: ', __FILE__, __LINE__, __METHOD__,10);
					}

				}

				if ( isset($pay_stub_entry) AND $pay_stub_entry != '' ) {
					Debug::text('zPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
                                        
                                       
					$ret_arr['entries'][] = array(	'user_wage_id' => $udt_obj->getColumn('user_wage_id'),
													'pay_stub_entry' => $pay_stub_entry,
													'total_time' =>  $total_time,
													'amount' => $amount,
													'rate' => $rate
												);
                                       
				}
				unset($pay_stub_entry, $amount, $total_time, $rate);
			}
		} else {
			Debug::text('NO UserDate Total entries found.', __FILE__, __LINE__, __METHOD__,10);
		}

		$ret_arr['other']['paid_absence_time'] = $paid_absence_time;
		$ret_arr['other']['dock_absence_time'] = $dock_absence_time;

		$ret_arr['other']['paid_absence_amount'] = $paid_absence_amount;
		$ret_arr['other']['dock_absence_amount'] = $dock_absence_amount;

		if ( isset($ret_arr) ) {
			Debug::Arr($ret_arr, 'UserDateTotal Array', __FILE__, __LINE__, __METHOD__,10);
			return $this->user_date_total_arr = $ret_arr;
		}

		return FALSE;
	}
        
        
}
?>