<?php

namespace App\Models\Users;

use App\Models\Company\CompanyDeductionListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\PayStub\PayStubEntryAccountLinkListFactory;
use Illuminate\Support\Facades\DB;

class UserDeductionFactory extends Factory {
	protected $table = 'user_deduction';
	protected $pk_sequence_name = 'user_deduction_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $company_deduction_obj = NULL;
	var $pay_stub_entry_account_link_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-first_name' => ('First Name'),
										'-1020-last_name' => ('Last Name'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'manual_id',
								'name',
								'city',
								'province',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',
										'company_deduction_id' => 'CompanyDeduction',
										'first_name' => FALSE,
										'last_name' => FALSE,
										'user_value1' => 'UserValue1',
										'user_value2' => 'UserValue2',
										'user_value3' => 'UserValue3',
										'user_value4' => 'UserValue4',
										'user_value5' => 'UserValue5',
										'user_value6' => 'UserValue6',
										'user_value7' => 'UserValue7',
										'user_value8' => 'UserValue8',
										'user_value9' => 'UserValue9',
										'user_value10' => 'UserValue10',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
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

	function getCompanyDeductionObject() {
		if ( is_object($this->company_deduction_obj) ) {
			return $this->company_deduction_obj;
		} else {
			$cdlf = new CompanyDeductionListFactory();
			$this->company_deduction_obj = $cdlf->getById( $this->getCompanyDeduction() )->getCurrent();

			return $this->company_deduction_obj;
		}
	}

	function getPayStubEntryAccountLinkObject() {
		if ( is_object($this->pay_stub_entry_account_link_obj) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = new PayStubEntryAccountLinkListFactory();
			$pseallf->getByCompanyID( $this->getUserObject()->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();
				return $this->pay_stub_entry_account_link_obj;
			}

			return FALSE;
		}
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueCompanyDeduction($deduction_id) {
		$ph = array(
					':user_id' => (int)$this->getUser(),
					':deduction_id' => (int)$deduction_id,
					);

		$query = 'select id from '. $this->getTable() .' where user_id = :user_id AND company_deduction_id = :deduction_id AND deleted=0';
		$id = DB::select($query, $ph);

		if (empty($id) || $id === FALSE ) {
            $id = 0;
        }else{
            $id = current(get_object_vars($id[0]));
        }
		Debug::Arr($id,'Unique Company Deduction: '. $deduction_id, __FILE__, __LINE__, __METHOD__,10);

		if ( empty($id) || $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getCompanyDeduction() {
		if ( isset($this->data['company_deduction_id']) ) {
			return $this->data['company_deduction_id'];
		}

		return FALSE;
	}
	function setCompanyDeduction($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$cdlf = new CompanyDeductionListFactory();

		if (	(
					$id != 0
					OR
					$this->Validator->isResultSetWithRows(	'company_deduction',
															$cdlf->getByID($id),
															('Deduction is invalid')
														)
				)
					AND
					$this->Validator->isTrue(				'company_deduction',
															$this->isUniqueCompanyDeduction($id),
															('Deduction is already assigned to employee')
															)


													) {

			$this->data['company_deduction_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue1() {
		if ( isset($this->data['user_value1']) ) {
			return $this->data['user_value1'];
		}

		return FALSE;
	}
	function setUserValue1($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value1',
												$value,
												('User Value 1 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue2() {
		if ( isset($this->data['user_value2']) ) {
			return $this->data['user_value2'];
		}

		return FALSE;
	}
	function setUserValue2($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value2',
												$value,
												('User Value 2 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue3() {
		if ( isset($this->data['user_value3']) ) {
			return $this->data['user_value3'];
		}

		return FALSE;
	}
	function setUserValue3($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value3',
												$value,
												('User Value 3 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue4() {
		if ( isset($this->data['user_value4']) ) {
			return $this->data['user_value4'];
		}

		return FALSE;
	}
	function setUserValue4($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value4',
												$value,
												('User Value 4 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue5() {
		if ( isset($this->data['user_value5']) ) {
			return $this->data['user_value5'];
		}

		return FALSE;
	}
	function setUserValue5($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value5',
												$value,
												('User Value 5 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue6() {
		if ( isset($this->data['user_value6']) ) {
			return $this->data['user_value6'];
		}

		return FALSE;
	}
	function setUserValue6($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value6',
												$value,
												('User Value 6 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value6'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue7() {
		if ( isset($this->data['user_value7']) ) {
			return $this->data['user_value7'];
		}

		return FALSE;
	}
	function setUserValue7($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value7',
												$value,
												('User Value 7 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value7'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue8() {
		if ( isset($this->data['user_value8']) ) {
			return $this->data['user_value8'];
		}

		return FALSE;
	}
	function setUserValue8($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value8',
												$value,
												('User Value 8 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value8'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue9() {
		if ( isset($this->data['user_value9']) ) {
			return $this->data['user_value9'];
		}

		return FALSE;
	}
	function setUserValue9($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value9',
												$value,
												('User Value 9 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value9'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue10() {
		if ( isset($this->data['user_value10']) ) {
			return $this->data['user_value10'];
		}

		return FALSE;
	}
	function setUserValue10($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value10',
												$value,
												('User Value 10 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value10'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//function getDeductionAmount( $user_id, $pay_stub_id, $annual_pay_periods, $date = NULL ) {
	function getDeductionAmount( $user_id, $pay_stub_obj, $pay_period_obj ) {
		if ( $user_id == '' ) {
			Debug::Text('Missing User ID: ', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( !is_object($pay_stub_obj) ) {
			Debug::Text('Missing Pay Stub Object: ', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( !is_object($pay_period_obj) ) {
			Debug::Text('Missing Pay Period Object: ', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		//Calculates the deduction.
		$cd_obj = $this->getCompanyDeductionObject();

		$annual_pay_periods = $pay_period_obj->getPayPeriodScheduleObject()->getAnnualPayPeriods();

		if ( !is_object($cd_obj) ) {
			return FALSE;
		}

		require_once( Environment::getBasePath(). DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'payroll_deduction'. DIRECTORY_SEPARATOR .'PayrollDeduction.class.php');

		$retval = 0;

		Debug::Text('Company Deduction: ID: '. $cd_obj->getID() .' Name: '. $cd_obj->getName() .' Calculation ID: '. $cd_obj->getCalculation(), __FILE__, __LINE__, __METHOD__,10);
		switch ( $cd_obj->getCalculation() ) {
			case 10: //Basic Percent
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				$retval = bcmul($amount, bcdiv($percent, 100) );

				break;
			case 15: //Advanced Percent
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$wage_base = $cd_obj->getUserValue2();
				} else {
					$wage_base = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$exempt_amount = $cd_obj->getUserValue3();
				} else {
					$exempt_amount = $this->getUserValue3();
				}

				//Annual Wage Base is the maximum earnings that an employee can earn before they are no longer eligible for this deduction
				//Annual Deduction Amount

				Debug::Text('Percent: '. $percent .' Wage Base: '. $wage_base .' Exempt Amount: '. $exempt_amount, __FILE__, __LINE__, __METHOD__,10);

				if ( $percent != 0 ) {
					if ( $exempt_amount > 0 ) {
						$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), bcdiv( $exempt_amount, $annual_pay_periods ) );
						Debug::Text('Amount After Exemption: '. $amount, __FILE__, __LINE__, __METHOD__,10);
					} else {
						$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );
						Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
					}

					if ( $wage_base > 0 ) {
						//*NOTE: If the first pay stub in TimeTrex is near the end of the year, and the employee has already exceeded the wage base amount
						//the payroll admin needs to make sure they add a YTD Adjustment for each Include PS Accounts that this calculation is based on,
						//NOT the total amount they have paid for the resulting calculation, as that has no effect whatsoever.

						//getCalculationYTDAmount is the previous pay stub YTD amount, but it includes any YTD Adjustments in the current pay stub too.
						$ytd_amount = $cd_obj->getCalculationYTDAmount( $pay_stub_obj );
						Debug::Text('Wage Base is set: '. $wage_base .' Amount: '. $amount .' Current YTD: '. $ytd_amount , __FILE__, __LINE__, __METHOD__,10);

						//Possible calcations:
						//
						//Wage Base: 3000
						//Amount: 500 YTD: 0		= 500
						//Amount: 500 YTD: 2900		= 100
						//Amount: 500 YTD: 3100		= 0
						//Amount: 3500 YTD: 0		= 3000
						//AMount: 3500 YTD: 2900	= 100
						//Amount: 3500 YTD: 3100	= 0

						//Check to see if YTD is less than wage base.
						$remaining_wage_base = bcsub($wage_base, $ytd_amount);
						Debug::Text('Remaining Wage Base to be calculated: '. $remaining_wage_base, __FILE__, __LINE__, __METHOD__,10);
						if ( $remaining_wage_base > 0 ) {
							if ( $amount > $remaining_wage_base ) {
								$amount = $remaining_wage_base;
							}
						} else {
							$amount = 0; //Exceeded wage base, nothing to calculate.
						}
						unset($remaining_wage_base);
					} else {
						Debug::Text('Wage Base is NOT set: '. $wage_base, __FILE__, __LINE__, __METHOD__,10);
					}

					$retval = bcmul($amount, bcdiv($percent, 100) );
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 AND $retval < 0 ) {
					$retval = 0;
				}

				unset($amount, $ytd_amount, $percent, $wage_base);

				break;
			case 17: //Advanced Percent (Range Bracket)
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$min_wage = $cd_obj->getUserValue2();
				} else {
					$min_wage = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$max_wage = $cd_obj->getUserValue3();
				} else {
					$max_wage = $this->getUserValue3();
				}

				if ( $this->getUserValue4() == '' ) {
					$annual_deduction_amount = $cd_obj->getUserValue4();
				} else {
					$annual_deduction_amount = $this->getUserValue4();
				}

				if ( $this->getUserValue5() == '' ) {
					$annual_fixed_amount = $cd_obj->getUserValue5();
				} else {
					$annual_fixed_amount = $this->getUserValue5();
				}

				$min_wage = bcdiv( $min_wage, $annual_pay_periods);
				$max_wage = bcdiv( $max_wage, $annual_pay_periods);
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
				$annual_fixed_amount = bcdiv( $annual_fixed_amount, $annual_pay_periods );

				Debug::Text('Percent: '. $percent .' Min Wage: '. $min_wage .' Max Wage: '. $max_wage .' Annual Deduction: '. $annual_deduction_amount, __FILE__, __LINE__, __METHOD__,10);

				if ( $percent != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);

					if ( $amount >= $min_wage AND $amount <= $max_wage ) {
						$retval = bcadd( bcmul($amount, bcdiv($percent, 100) ), $annual_fixed_amount);
					}
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 AND $retval < 0 ) {
					$retval = 0;
				}

				unset($amount, $percent, $min_wage, $max_wage, $annual_deduction_amount, $annual_fixed_amount);

				break;
			case 18: //Advanced Percent (Tax Bracket)
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$wage_base = $cd_obj->getUserValue2();
				} else {
					$wage_base = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$exempt_amount = $cd_obj->getUserValue3();
				} else {
					$exempt_amount = $this->getUserValue3();
				}

				if ( $this->getUserValue4() == '' ) {
					$annual_deduction_amount = $cd_obj->getUserValue4();
				} else {
					$annual_deduction_amount = $this->getUserValue4();
				}

				Debug::Text('Percent: '. $percent .' Wage Base: '. $wage_base .' Exempt Amount: '. $exempt_amount, __FILE__, __LINE__, __METHOD__,10);

				if ( $percent != 0 ) {
					if ( $exempt_amount > 0 ) {
						$pp_exempt_amount = bcdiv( $exempt_amount, $annual_pay_periods );
					} else {
						$pp_exempt_amount = 0;
					}
					//Debug::Text('PP Exempt Amount: '. $pp_exempt_amount, __FILE__, __LINE__, __METHOD__,10);

					if ( $wage_base > 0 ) {
						$pp_wage_base_amount = bcdiv( $wage_base, $annual_pay_periods );
					} else {
						$pp_wage_base_amount = 0;
					}

					if ( $annual_deduction_amount > 0 ) {
						$pp_annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
					} else {
						$pp_annual_deduction_amount = 0;
					}

					//Debug::Text('PP Wage Base Base Amount: '. $pp_wage_base_amount, __FILE__, __LINE__, __METHOD__,10);
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $pp_annual_deduction_amount );

					//Debug::Text('Calculation Pay Stub Amount: '. $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), __FILE__, __LINE__, __METHOD__,10);
					if (  $pp_wage_base_amount > 0
							AND $amount > $pp_wage_base_amount ) {
						//Debug::Text('Exceeds Wage Base...'. $amount, __FILE__, __LINE__, __METHOD__,10);
						$amount = bcsub( $pp_wage_base_amount, $pp_exempt_amount );
					} else {
						//Debug::Text('Under Wage Base...'. $amount, __FILE__, __LINE__, __METHOD__,10);
						$amount = bcsub( $amount, $pp_exempt_amount );
					}
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);

					$retval = bcmul($amount, bcdiv($percent, 100) );
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 AND $retval < 0 ) {
					$retval = 0;
				}

				unset($amount, $percent, $wage_base, $pp_wage_base_amount, $pp_exempt_amount, $annual_deduction_amount, $pp_annual_deduction_amount);

				break;
			case 19: //Advanced Percent (Tax Bracket Alternate)
				/*
					This is designed to be used for single line item tax calculations, in that the formula looks like this,
					where only ONE bracket would be applied to the employee, NOT all:
					Wage between 0 - 10,000 calculate 10%
					Wage between 10,001 - 20,000 calculate 15% + $1000 (10% of 10,000 as per above)
					Wage between 20,001 - 30,000 calculate 20% + $2500 (10% of 10,000 as first bracket, and 15% of 10,000 as per 2nd bracket)
				*/
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$min_wage = $cd_obj->getUserValue2();
				} else {
					$min_wage = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$max_wage = $cd_obj->getUserValue3();
				} else {
					$max_wage = $this->getUserValue3();
				}

				if ( $this->getUserValue4() == '' ) {
					$annual_deduction_amount = $cd_obj->getUserValue4();
				} else {
					$annual_deduction_amount = $this->getUserValue4();
				}

				if ( $this->getUserValue5() == '' ) {
					$annual_fixed_amount = $cd_obj->getUserValue5();
				} else {
					$annual_fixed_amount = $this->getUserValue5();
				}

				$min_wage = bcdiv( $min_wage, $annual_pay_periods);
				$max_wage = bcdiv( $max_wage, $annual_pay_periods);
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
				$annual_fixed_amount = bcdiv( $annual_fixed_amount, $annual_pay_periods );

				Debug::Text('Percent: '. $percent .' Min Wage: '. $min_wage .' Max Wage: '. $max_wage .' Annual Deduction: '. $annual_deduction_amount, __FILE__, __LINE__, __METHOD__,10);

				if ( $percent != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);

					if ( $amount >= $min_wage AND $amount <= $max_wage ) {
						$retval = bcadd( bcmul( bcsub( $amount, $min_wage ) , bcdiv($percent, 100) ), $annual_fixed_amount);
					}
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 AND $retval < 0 ) {
					$retval = 0;
				}

				unset($amount, $percent, $min_wage, $max_wage, $annual_deduction_amount, $annual_fixed_amount);

				break;
			case 20: //Fixed amount
				if ( $this->getUserValue1() == FALSE ) {
					$amount = $cd_obj->getUserValue1();
				} else {
					$amount = $this->getUserValue1();
				}

				$retval = $amount;
				unset($amount);

				break;
			case 30: //Fixed Amount (Range Bracket)
				if ( $this->getUserValue1() == '' ) {
					$fixed_amount = $cd_obj->getUserValue1();
				} else {
					$fixed_amount = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$min_wage = $cd_obj->getUserValue2();
				} else {
					$min_wage = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$max_wage = $cd_obj->getUserValue3();
				} else {
					$max_wage = $this->getUserValue3();
				}

				if ( $this->getUserValue4() == '' ) {
					$annual_deduction_amount = $cd_obj->getUserValue4();
				} else {
					$annual_deduction_amount = $this->getUserValue4();
				}

				$min_wage = bcdiv( $min_wage, $annual_pay_periods);
				$max_wage = bcdiv( $max_wage, $annual_pay_periods);
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );

				Debug::Text('Amount: '. $fixed_amount .' Min Wage: '. $min_wage .' Max Wage: '. $max_wage .' Annual Deduction: '. $annual_deduction_amount, __FILE__, __LINE__, __METHOD__,10);

				if ( $fixed_amount != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);

					if ( $amount >= $min_wage AND $amount <= $max_wage ) {
						$retval = $fixed_amount;
					}
				} else {
					$retval = 0;
				}

				unset($fixed_amount, $amount, $percent, $min_wage, $max_wage, $annual_deduction_amount);

				break;
			case 52: //Fixed Amount (w/Limit)
				if ( $this->getUserValue1() == '' ) {
					$fixed_amount = $cd_obj->getUserValue1();
				} else {
					$fixed_amount = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$target_amount = $cd_obj->getUserValue2();
				} else {
					$target_amount = $this->getUserValue2();
				}

				Debug::Text('Amount: '. $fixed_amount .' Target Amount: '. $target_amount, __FILE__, __LINE__, __METHOD__,10);

				$retval = 0;
				if ( $fixed_amount != 0 ) {
					$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
					if ( $amount !== $target_amount ) {
						if ( abs($fixed_amount) < abs(bcsub($amount,$target_amount)) ) {
							//Use full fixed amount
							Debug::Text('Not within reach of target, use full fixed amount...', __FILE__, __LINE__, __METHOD__,10);
							$retval = $fixed_amount;
						} else {
							Debug::Text('Within reach of target, use partial fixed amount...', __FILE__, __LINE__, __METHOD__,10);
							//Use partial fixed_amount
							$retval = bcadd( abs($amount), $target_amount);
						}
					}
				}

				$retval = abs($retval);

				unset($fixed_amount, $tmp_amount, $amount, $min_limit, $max_limit);

				break;
			case 80: //US Earning Income Credit (EIC). Repealed as of 31-Dec-2010.
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__,10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__,10);

				$pd_obj = new PayrollDeduction( 'US', NULL );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				if ( is_object( $this->getUserObject() ) ) {
					$currency_id = $this->getUserObject()->getCurrency();
					$pd_obj->setUserCurrency( $currency_id );
					Debug::Text('User Currency ID: '. $currency_id, __FILE__, __LINE__, __METHOD__,10);
				}

				$pd_obj->setEICFilingStatus( $user_value1 );
				$pd_obj->setGrossPayPeriodIncome( $amount );

				//Allow negative value, infact it always should be.
				$retval = $pd_obj->getEIC();

				break;

			case 90: //Canada - CPP
				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__,10);

				$pd_obj = new PayrollDeduction( 'CA', NULL);
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				$pd_obj->setEnableCPPAndEIDeduction(TRUE);

				if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() != '' ) {
					Debug::Text('Found Employee CPP account link!: ', __FILE__, __LINE__, __METHOD__,10);

					$previous_ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
					$current_ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
					Debug::text('YTD CPP Contribution: Previous Amount: '. $previous_ytd_cpp_arr['ytd_amount'] .' Current Amount: '. $current_ytd_cpp_arr['amount'], __FILE__, __LINE__, __METHOD__,10);

					$pd_obj->setYearToDateCPPContribution( bcadd($previous_ytd_cpp_arr['ytd_amount'], $current_ytd_cpp_arr['ytd_amount'] ) );
					unset($previous_ytd_cpp_arr, $current_ytd_cpp_arr);
				}

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getEmployeeCPP();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 91: //Canada - EI
				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__,10);

				$pd_obj = new PayrollDeduction( 'CA', NULL);
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				$pd_obj->setEnableCPPAndEIDeduction(TRUE);

				if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() != '' ) {
					Debug::Text('Found Employee EI account link!: ', __FILE__, __LINE__, __METHOD__,10);

					$previous_ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
					$current_ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
					Debug::text('YTD EI Contribution: Previous Amount: '. $previous_ytd_ei_arr['ytd_amount'] .' Current Amount: '. $current_ytd_ei_arr['amount'], __FILE__, __LINE__, __METHOD__,10);

					$pd_obj->setYearToDateEIContribution( bcadd($previous_ytd_ei_arr['ytd_amount'], $current_ytd_ei_arr['ytd_amount'] ) );
					unset($previous_ytd_ei_arr, $current_ytd_ei_arr);
				}

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getEmployeeEI();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 100: //Federal Income Tax
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$user_value2 = $cd_obj->getUserValue2();
				} else {
					$user_value2 = $this->getUserValue2();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('UserValue2: '. $user_value2, __FILE__, __LINE__, __METHOD__,10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__,10);

				$pd_obj = new PayrollDeduction( $this->getCompanyDeductionObject()->getCountry(), NULL );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				if ( is_object( $this->getUserObject() ) ) {
					$currency_id = $this->getUserObject()->getCurrency();
					$pd_obj->setUserCurrency( $currency_id );
					Debug::Text('User Currency ID: '. $currency_id, __FILE__, __LINE__, __METHOD__,10);
				}

				if ( $this->getCompanyDeductionObject()->getCountry() == 'CA' ) {
					//CA
					$pd_obj->setFederalTotalClaimAmount( $user_value1 );

					$pd_obj->setEnableCPPAndEIDeduction(TRUE);

					//$pself = new PayStubEntryListFactory();
					if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() != '' ) {
						Debug::Text('Found Employee CPP account link!: ', __FILE__, __LINE__, __METHOD__,10);

						//Check to see if CPP was calculated on the CURRENT pay stub, if not assume they are CPP exempt.
						//Single this calculation formula doesn't know directly if the user was CPP exempt or not, we have to assume it by
						//the calculate CPP on the current pay stub. However if the CPP calculation is done AFTER this, it may mistakenly assume they are exempt.
						//Make sure we handle the maximum CPP contribution cases properly as well.
						$current_cpp = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
						if ( isset($current_cpp['amount']) AND $current_cpp['amount'] == 0 ) {
							Debug::Text('Current CPP: '. $current_cpp['amount'] .' Setting CPP exempt in Federal Income Tax calculation...', __FILE__, __LINE__, __METHOD__,10);
							$pd_obj->setCPPExempt( TRUE );
						}

						$ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );

						Debug::text('YTD CPP Contribution: '. $ytd_cpp_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__,10);

						$pd_obj->setYearToDateCPPContribution( $ytd_cpp_arr['ytd_amount'] );
						unset($ytd_cpp_arr, $current_cpp );
					}

					if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() != '' ) {
						Debug::Text('Found Employee EI account link!: ', __FILE__, __LINE__, __METHOD__,10);

						//See comment above regarding CPP exempt.
						$current_ei = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
						if ( isset($current_ei['amount']) AND $current_ei['amount'] == 0 ) {
							Debug::Text('Current EI: '. $current_ei['amount'] .' Setting EI exempt in Federal Income Tax calculation...', __FILE__, __LINE__, __METHOD__,10);
							$pd_obj->setEIExempt( TRUE );
						}

						$ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );

						Debug::text('YTD EI Contribution: '. $ytd_ei_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__,10);

						$pd_obj->setYearToDateEIContribution( $ytd_ei_arr['ytd_amount'] );
						unset($ytd_ei_arr, $current_ei);
					}
				} elseif ( $this->getCompanyDeductionObject()->getCountry() == 'US' ) {
					//US
					$pd_obj->setFederalFilingStatus( $user_value1 );
					$pd_obj->setFederalAllowance( $user_value2 );
				} elseif ( $this->getCompanyDeductionObject()->getCountry() == 'CR' ) {
					//CR
					$pd_obj->setFederalFilingStatus( $user_value1 ); //Single/Married
					$pd_obj->setFederalAllowance( $user_value2 );	 //Allownces/Children
				}

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getFederalPayPeriodDeductions();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 200: //Province Income Tax
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$user_value2 = $cd_obj->getUserValue2();
				} else {
					$user_value2 = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$user_value3 = $cd_obj->getUserValue3();
				} else {
					$user_value3 = $this->getUserValue3();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('UserValue2: '. $user_value2, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('UserValue3: '. $user_value3, __FILE__, __LINE__, __METHOD__,10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__,10);

				$pd_obj = new PayrollDeduction( $this->getCompanyDeductionObject()->getCountry(), $this->getCompanyDeductionObject()->getProvince() );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				$pd_obj->setGrossPayPeriodIncome( $amount );

				if ( $this->getCompanyDeductionObject()->getCountry() == 'CA' ) {
					Debug::Text('Canada Pay Period Deductions...', __FILE__, __LINE__, __METHOD__,10);
					$pd_obj->setProvincialTotalClaimAmount( $user_value1 );

					$pd_obj->setEnableCPPAndEIDeduction(TRUE);

					//$pself = new PayStubEntryListFactory();
					if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() != '' ) {
						Debug::Text('Found Employee CPP account link!: ', __FILE__, __LINE__, __METHOD__,10);

						//Check to see if CPP was calculated on the CURRENT pay stub, if not assume they are CPP exempt.
						//Single this calculation formula doesn't know directly if the user was CPP exempt or not, we have to assume it by
						//the calculate CPP on the current pay stub. However if the CPP calculation is done AFTER this, it may mistakenly assume they are exempt.
						//Make sure we handle the maximum CPP contribution cases properly as well.
						$current_cpp = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
						if ( isset($current_cpp['amount']) AND $current_cpp['amount'] == 0 ) {
							Debug::Text('Current CPP: '. $current_cpp['amount'] .' Setting CPP exempt in Provincial Income Tax calculation...', __FILE__, __LINE__, __METHOD__,10);
							$pd_obj->setCPPExempt( TRUE );
						}

						$ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );

						Debug::text('YTD CPP Contribution: '. $ytd_cpp_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__,10);

						$pd_obj->setYearToDateCPPContribution( $ytd_cpp_arr['ytd_amount'] );
						unset($ytd_cpp_arr, $current_cpp);
					}

					if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() != '' ) {
						Debug::Text('Found Employee EI account link!: ', __FILE__, __LINE__, __METHOD__,10);

						//See comment above regarding CPP exempt.
						$current_ei = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
						if ( isset($current_ei['amount']) AND $current_ei['amount'] == 0 ) {
							Debug::Text('Current EI: '. $current_ei['amount'] .' Setting EI exempt in Provincial Income Tax calculation...', __FILE__, __LINE__, __METHOD__,10);
							$pd_obj->setEIExempt( TRUE );
						}

						$ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );

						Debug::text('YTD EI Contribution: '. $ytd_ei_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__,10);

						$pd_obj->setYearToDateEIContribution( $ytd_ei_arr['ytd_amount'] );
						unset($ytd_ei_arr, $current_ei);
					}

					$retval = $pd_obj->getProvincialPayPeriodDeductions();
				} elseif ( $this->getCompanyDeductionObject()->getCountry() == 'US' ) {
					Debug::Text('US Pay Period Deductions...', __FILE__, __LINE__, __METHOD__,10);

					//Need to set Federal settings here.
					$udlf = new UserDeductionListFactory();
					$udlf->getByUserIdAndCountryID( $user_id, $this->getCompanyDeductionObject()->getCountry() );
					if ( $udlf->getRecordCount() > 0 ) {
						Debug::Text('Found Federal User Deduction...', __FILE__, __LINE__, __METHOD__,10);

						$tmp_ud_obj = $udlf->getCurrent();

						if ( $tmp_ud_obj->getUserValue1() == '' ) {
							$tmp_user_value1 = $tmp_ud_obj->getCompanyDeductionObject()->getUserValue1();
						} else {
							$tmp_user_value1 = $tmp_ud_obj->getUserValue1();
						}

						if ( $tmp_ud_obj->getUserValue2() == '' ) {
							$tmp_user_value2 = $tmp_ud_obj->getCompanyDeductionObject()->getUserValue2();
						} else {
							$tmp_user_value2 = $tmp_ud_obj->getUserValue2();
						}

						Debug::Text('TmpUserValue1: '. $tmp_user_value1, __FILE__, __LINE__, __METHOD__,10);
						Debug::Text('TmpUserValue2: '. $tmp_user_value2, __FILE__, __LINE__, __METHOD__,10);

						$pd_obj->setFederalFilingStatus( $tmp_user_value1 );
						$pd_obj->setFederalAllowance( $tmp_user_value2 );

						unset($tmp_ud_obj, $tmp_user_value1, $tmp_user_value1);
					}
					unset($udlf);

					$pd_obj->setStateFilingStatus( $user_value1 );
					$pd_obj->setStateAllowance( $user_value2 );

					$pd_obj->setUserValue1( $user_value1 );
					$pd_obj->setUserValue2( $user_value2 );
					$pd_obj->setUserValue3( $user_value3 );

					$retval = $pd_obj->getStatePayPeriodDeductions();
				}

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 300: //District Income Tax
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$user_value2 = $cd_obj->getUserValue2();
				} else {
					$user_value2 = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$user_value3 = $cd_obj->getUserValue3();
				} else {
					$user_value3 = $this->getUserValue3();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('UserValue2: '. $user_value2, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('UserValue3: '. $user_value3, __FILE__, __LINE__, __METHOD__,10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('District: '. $this->getCompanyDeductionObject()->getDistrict(), __FILE__, __LINE__, __METHOD__,10);

				$pd_obj = new PayrollDeduction( $this->getCompanyDeductionObject()->getCountry(), $this->getCompanyDeductionObject()->getProvince(), $this->getCompanyDeductionObject()->getDistrict() );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				$pd_obj->setDistrictFilingStatus( $user_value1 );
				$pd_obj->setDistrictAllowance( $user_value2 );

				$pd_obj->setUserValue1( $user_value1 );
				$pd_obj->setUserValue2( $user_value2 );
				$pd_obj->setUserValue3( $user_value3 );

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getDistrictPayPeriodDeductions();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
		}

		Debug::Text('Deduction Amount: '. $retval, __FILE__, __LINE__, __METHOD__,10);

		//Allow negative values, as some advanced tax bracket setups require this.
		if ( $retval < 0 ) {
			//Debug::Text('Deduction was negative, setting to 0...', __FILE__, __LINE__, __METHOD__,10);
			Debug::Text('Deduction was negative...', __FILE__, __LINE__, __METHOD__,10);
			//$retval = 0;
		}

		return $retval;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		$obj = $this->getUserObject();
		if ( is_object($obj) ) {
			return TTLog::addEntry( $this->getCompanyDeduction(), $log_action, ('Employee Deduction') .': '. $obj->getFullName(), NULL, $this->getTable(), $this );
		}
	}
}
?>
