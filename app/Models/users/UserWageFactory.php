<?php

namespace App\Models\Users;

use App\Models\Core\CurrencyFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class UserWageFactory extends Factory {
	protected $table = 'user_wage';
	protected $pk_sequence_name = 'user_wage_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $labor_standard_obj = NULL;
	var $holiday_obj = NULL;
	var $wage_group_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
											10 	=> ('Hourly'),
											12	=> ('Salary (Weekly)'),
											13	=> ('Salary (Bi-Weekly)'),
											15	=> ('Salary (Monthly)'),
											20	=> ('Salary (Annual)'),
//											30	=> ('Min. Wage + Bonus (Salary)')
									);
				break;
			case 'columns':
				$retval = array(

										'-1010-first_name' => ('First Name'),
										'-1020-last_name' => ('Last Name'),

										'-1030-wage_group' => ('Wage Group'),
										'-1040-type' => ('Type'),
										'-1050-wage' => ('Wage'),
										'-1060-effective_date' => ('Effective Date'),

										'-1070-hourly_rate' => ('Hourly Rate'),
										'-1070-labor_burden_percent' => ('Labor Burden Percent'),
										'-1080-weekly_time' => ('Average Time/Week'),

										'-1090-title' => ('Title'),
										'-1099-user_group' => ('Group'),
										'-1100-default_branch' => ('Branch'),
										'-1110-default_department' => ('Department'),

										'-1290-note' => ('Note'),

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
								'first_name',
								'last_name',
								'wage_group',
								'type',
								'wage',
								'effective_date',
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
											'first_name' => FALSE,
											'last_name' => FALSE,
											'wage_group_id' => 'WageGroup',
											'wage_group' => FALSE,
											'type_id' => 'Type',
											'type' => FALSE,
											'currency_symbol' => FALSE,
											'wage' => 'Wage',
											'hourly_rate' => 'HourlyRate',
											'weekly_time' => 'WeeklyTime',
											'labor_burden_percent' => 'LaborBurdenPercent',
											'effective_date' => 'EffectiveDate',
											'note' => 'Note',

											'default_branch' => FALSE,
											'default_department' => FALSE,
											'user_group' => FALSE,
											'title' => FALSE,

											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}

	function getWageGroupObject() {
		if ( is_object($this->wage_group_obj) ) {
			return $this->wage_group_obj;
		} else {

			$wglf = new UserWageListFactory();
			$wglf->getById( $this->getWageGroup() );

			if ( $wglf->getRecordCount() == 1 ) {
				$this->wage_group_obj = $wglf->getCurrent();

				return $this->wage_group_obj;
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

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('Invalid Employee')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getWageGroup() {
		if ( isset($this->data['wage_group_id']) ) {
			return $this->data['wage_group_id'];
		}

		return FALSE;
	}
	function setWageGroup($id) {
		$id = trim($id);

		Debug::Text('Wage Group ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$wglf = new UserWageListFactory();

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'wage_group',
														$wglf->getByID($id),
														('Group is invalid')
													) ) {

			$this->data['wage_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getWage() {
		if ( isset($this->data['wage']) ) {
			return $this->data['wage'];
		}

		return FALSE;
	}


	function setWage($wage) {
		$wage = trim($wage);

		//Pull out only digits and periods.
		$wage = $this->Validator->stripNonFloat($wage);

		if (
				$this->Validator->isNotNull('wage',
											$wage,
											('Please specify a wage'))
				AND
				$this->Validator->isFloat(	'wage',
											$wage,
											('Incorrect Wage'))
				AND
				$this->Validator->isLength(	'wage',
											$wage,
											('Wage has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal(	'wage',
											$wage,
											('Wage has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'wage',
											$wage,
											('Wage has too many digits after the decimal'),
											0,
											4)
				) {

			$this->data['wage'] = $wage;

			return TRUE;
		}

		return FALSE;
	}




       function getBudgetoryAllowance() {
		if ( isset($this->data['budgetary_allowance']) ) {
			return $this->data['budgetary_allowance'];
		}

		return FALSE;
	}


	function setBudgetoryAllowance($budgetary_allowance) {
		$wage = trim($budgetary_allowance);

		//Pull out only digits and periods.
		$wage = $this->Validator->stripNonFloat($wage);

		if (
				$this->Validator->isNotNull('budgetary_allowance',
											$wage,
											('Please specify a Budgetory Allowance'))
				AND
				$this->Validator->isFloat(	'budgetary_allowance',
											$wage,
											('Incorrect Budgetory Allowance'))
				AND
				$this->Validator->isLength(	'budgetary_allowance',
											$wage,
											('Budgetory Allowance has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal(	'budgetary_allowance',
											$wage,
											('Budgetory Allowance has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'budgetary_allowance',
											$wage,
											('Budgetory Allowance has too many digits after the decimal'),
											0,
											4)
				) {

			$this->data['budgetary_allowance'] = $wage;

			return TRUE;
		}

		return FALSE;
	}

	function getHourlyRate() {
		if ( isset($this->data['hourly_rate']) ) {
			return $this->data['hourly_rate'];
		}

		return FALSE;
	}
	function setHourlyRate($rate) {
		$rate = trim($rate);

		//Pull out only digits and periods.
		$rate = $this->Validator->stripNonFloat($rate);

		if ( $rate == '' OR empty($rate) ) {
			$rate = NULL;
		}

		if ( $rate == NULL
				OR
				$this->Validator->isFloat(	'hourly_rate',
											$rate,
											('Incorrect Hourly Rate')) ) {

			$this->data['hourly_rate'] = $rate;

			return TRUE;
		}

		return FALSE;
	}

	function getWeeklyTime() {
		if ( isset($this->data['weekly_time']) ) {
			//Debug::Text('Weekly Time: '. $this->data['weekly_time'], __FILE__, __LINE__, __METHOD__,10);

			return $this->data['weekly_time'];
		}

		return FALSE;

	}
	function setWeeklyTime($value) {
		//$value = $value;

		if (	$value == NULL
				OR
				$this->Validator->isNumeric(	'weekly_time',
											$value,
											('Incorrect Weekly Time')) ) {

			$this->data['weekly_time'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLaborBurdenPercent() {
		if ( isset($this->data['labor_burden_percent']) ) {
			return $this->data['labor_burden_percent'];
		}

		return FALSE;
	}
	function setLaborBurdenPercent($value) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if (	$this->Validator->isFloat(	'labor_burden_percent',
											$value,
											('Incorrect Labor Burden Percent')) ) {

			$this->data['labor_burden_percent'] = $value;

			return TRUE;
		}

		return FALSE;
	}


	function isValidEffectiveDate($epoch) {
		//Check to see if this is the first default wage entry, or if we are editing the first record.
		if ( $this->getWageGroup() != 0 ) { //If we aren't the default wage group, return valid always.
			return TRUE;
		}

		$must_validate = FALSE;

		$uwlf = new UserWageListFactory();
		$uwlf->getByUserIdAndGroupIDAndBeforeDate( $this->getUser(), 0, $epoch );
		//Debug::text(' Total Rows: '. $uwlf->getRecordCount() .' User: '. $this->getUser() .' Epoch: '. $epoch , __FILE__, __LINE__, __METHOD__,10);

		if ( $uwlf->getRecordCount() <= 1 ) {
			//If it returns one row, we need to check to see if the returned row is the current record.
			if ( $uwlf->getRecordCount() == 0 ) {
				$must_validate = TRUE;
			} elseif ( $uwlf->getRecordCount() == 1 AND $this->isNew() == FALSE ) {
				//Check to see if we are editing the current record.
				if ( is_object( $uwlf->getCurrent() ) AND $this->getId() == $uwlf->getCurrent()->getId() ) {
					$must_validate = TRUE;
				} else {
					$must_validate = FALSE;
				}
			}
		}

		if ( $must_validate == TRUE ) {
			if ( is_object( $this->getUserObject() ) AND $this->getUserObject()->getHireDate() != '' ) {
				//User has hire date, make sure its before or equal to the first wage effective date.
				if ( $epoch <= $this->getUserObject()->getHireDate() ) {
					return TRUE;
				} else {
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	function getEffectiveDate( $raw = FALSE ) {
		if ( isset($this->data['effective_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['effective_date'];
			} else {
				return TTDate::strtotime( $this->data['effective_date'] );
			}
		}

		return FALSE;
	}
	function setEffectiveDate($epoch) {
		$epoch = TTDate::getBeginDayEpoch( trim($epoch) );

		Debug::Text('Effective Date: '. TTDate::getDate('DATE+TIME', $epoch ) , __FILE__, __LINE__, __METHOD__,10);

		if 	(	$this->Validator->isDate(		'effective_date',
												$epoch,
												('Incorrect Effective Date'))
			) {

			$this->data['effective_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}

		return FALSE;
	}
	function setNote($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'note',
														$value,
														('Note is too long'),
														1,
														2048)
			) {

			$this->data['note'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getLaborBurdenHourlyRate( $rate ) {
		$retval = bcmul( $rate, bcadd( bcdiv( $this->getLaborBurdenPercent(), 100 ), 1) );

		return Misc::MoneyFormat($retval, FALSE);
	}

	function getBaseCurrencyHourlyRate( $rate ) {
		if ( $rate == '' ) {
			return FALSE;
		}

		$clf = new CurrencyListFactory();
		$clf->getByCompanyIdAndBase( $this->getUserObject()->getCompany(), TRUE );
		if ( $clf->getRecordCount() > 0 ) {
			$base_currency_obj = $clf->getCurrent();

			//If current currency is the base currency, just return the rate.
			if ( $base_currency_obj->getId() == $this->getUserObject()->getCurrency() ) {
				return $rate;
			} else {
				//Debug::text(' Base Currency Rate: '. $base_currency_obj->getConversionRate() .' Hourly Rate: '. $rate , __FILE__, __LINE__, __METHOD__,10);
				return CurrencyFactory::convertCurrency( $this->getUserObject()->getCurrency(), $base_currency_obj->getId(), $rate );
			}
		}

		return FALSE;
	}

	function getAnnualWage() {
		$annual_wage = 0;

		//Debug::text(' Type: '. $this->getType() .' Wage: '. $this->getWage() , __FILE__, __LINE__, __METHOD__,10);
		switch ( $this->getType() ) {
			case 10: //Hourly
				//Hourly wage type, can't have an annual wage.
				$annual_wage = 0;
				break;
			case 12: //Salary (Weekly)
				$annual_wage = bcmul( $this->getWage(), 52 );
				break;
			case 13: //Salary (Bi-Weekly)
				$annual_wage = bcmul( $this->getWage(), 26 );
				break;
			case 15: //Salary (Monthly)
				$annual_wage = bcmul( $this->getWage(), 12 );
				break;
			case 20: //Salary (Annual)
				$annual_wage = $this->getWage();
				break;
		}

		return $annual_wage;
	}

	function calcHourlyRate( $epoch = FALSE, $accurate_calculation = FALSE ) {
		$hourly_wage = 0;

		if ( $this->getType() == 10 ) {
			$hourly_wage = $this->getWage();
                //added for aqua fresh 2018/04/18 by Thilini - 15->type monthly
                } else if($this->getType() == 15){
                    $hourly_wage = $this->getMonthlyHourlyRate( $this->getWage(), $epoch, $accurate_calculation );
		} else {
                    $hourly_wage = $this->getMonthlyHourlyRate( $this->getAnnualWage(), $epoch, $accurate_calculation );

		}

		return Misc::MoneyFormat($hourly_wage, FALSE);
	}

        //Added By Thilini 2018/04/18 hourly rate with Basic + Budgetary Allowance
	function getMonthlyHourlyRate($monthly_wage, $epoch = FALSE, $accurate_calculation = FALSE ) {
            //echo 'here::'.$this->getUser();

		if ( $epoch == FALSE ) {
			$epoch = TTDate::getTime();
		}

		if( $monthly_wage == '' ) {
			return FALSE;
		}
                $budgetary_allowance = 0;
                $udlf = new UserDeductionListFactory();
                $udlf->getByUserIdAndCompanyDeductionId($this->getUser(), 3);
                if($udlf->getRecordCount()>0){
                    foreach ($udlf->rs as $udlf_obj){
						$udlf->data = (array)$udlf_obj;
						$udlf_obj = $udlf;
                        $budgetary_allowance = $udlf_obj->getUserValue1();
                    }
                }

                $average_monthly_hours = $this->getWeeklyTime()/3600;

                $full_wage = $monthly_wage + $budgetary_allowance;

                if ( $average_monthly_hours == 0 ) {
                    //No default schedule, can't pay them.
                    $hourly_wage = 0;
                } else {
                    $hourly_wage = bcdiv( $full_wage, $average_monthly_hours );
                }

//		if ( $accurate_calculation == TRUE ) {
//			Debug::text('EPOCH: '. $epoch , __FILE__, __LINE__, __METHOD__,10);
//
//			$annual_week_days = TTDate::getAnnualWeekDays( $epoch );
//			Debug::text('Annual Week Days: '. $annual_week_days , __FILE__, __LINE__, __METHOD__,10);
//
//			//Calculate weeks from adjusted annual weekdays
//			//We could use just 52 weeks in a year, but that isn't as accurate.
//			$annual_work_weeks = bcdiv( $annual_week_days, 5);
//			Debug::text('Adjusted annual work weeks : '. $annual_work_weeks , __FILE__, __LINE__, __METHOD__,10);
//		} else {
//			$annual_work_weeks = 52;
//		}

//		$average_weekly_hours = TTDate::getHours( $this->getWeeklyTime() );
		//Debug::text('Average Weekly Hours: '. $average_weekly_hours , __FILE__, __LINE__, __METHOD__,10);

//		if ( $average_weekly_hours == 0 ) {
//			//No default schedule, can't pay them.
//			$hourly_wage = 0;
//		} else {
//			//Divide by average hours/day from default schedule?
//			$hours_per_year = bcmul($annual_work_weeks, $average_weekly_hours);
//			if ( $hours_per_year > 0 ) {
//				$hourly_wage = bcdiv( $monthly_wage, $hours_per_year );
//			}
//			unset($hours_per_year);
//		}
		//Debug::text('User Wage: '. $this->getWage() , __FILE__, __LINE__, __METHOD__,10);
		//Debug::text('Annual Hourly Rate: '. $hourly_wage , __FILE__, __LINE__, __METHOD__,10);

		return $hourly_wage;
	}

        function getAnnualHourlyRate( $annual_wage, $epoch = FALSE, $accurate_calculation = FALSE ) {
		if ( $epoch == FALSE ) {
			$epoch = TTDate::getTime();
		}

		if( $annual_wage == '' ) {
			return FALSE;
		}

		if ( $accurate_calculation == TRUE ) {
			Debug::text('EPOCH: '. $epoch , __FILE__, __LINE__, __METHOD__,10);

			$annual_week_days = TTDate::getAnnualWeekDays( $epoch );
			Debug::text('Annual Week Days: '. $annual_week_days , __FILE__, __LINE__, __METHOD__,10);

			//Calculate weeks from adjusted annual weekdays
			//We could use just 52 weeks in a year, but that isn't as accurate.
			$annual_work_weeks = bcdiv( $annual_week_days, 5);
			Debug::text('Adjusted annual work weeks : '. $annual_work_weeks , __FILE__, __LINE__, __METHOD__,10);
		} else {
			$annual_work_weeks = 52;
		}

		$average_weekly_hours = TTDate::getHours( $this->getWeeklyTime() );
		//Debug::text('Average Weekly Hours: '. $average_weekly_hours , __FILE__, __LINE__, __METHOD__,10);

		if ( $average_weekly_hours == 0 ) {
			//No default schedule, can't pay them.
			$hourly_wage = 0;
		} else {
			//Divide by average hours/day from default schedule?
			$hours_per_year = bcmul($annual_work_weeks, $average_weekly_hours);
			if ( $hours_per_year > 0 ) {
				$hourly_wage = bcdiv( $annual_wage, $hours_per_year );
			}
			unset($hours_per_year);
		}
		//Debug::text('User Wage: '. $this->getWage() , __FILE__, __LINE__, __METHOD__,10);
		//Debug::text('Annual Hourly Rate: '. $hourly_wage , __FILE__, __LINE__, __METHOD__,10);

		return $hourly_wage;
	}


	static function proRateSalary($salary, $wage_effective_date, $prev_wage_effective_date, $pp_start_date, $pp_end_date, $termination_date ) {
		$prev_wage_effective_date = (int)$prev_wage_effective_date;

		if ( $wage_effective_date < $pp_start_date ) {
			$wage_effective_date = $pp_start_date;
		}

                /* commented by thusitha request by Mr Printoe 2018-03-09 */

		//$total_pay_period_days = ceil( TTDate::getDayDifference( $pp_start_date, $pp_end_date) );

                $total_pay_period_days = 30;

               // $total_pay_period_days = 30;

		if ( $prev_wage_effective_date == 0 ) {
			//ProRate salary to termination date if its in the middle of a pay period.
			if ( $termination_date != '' AND $termination_date > 0 AND $termination_date < $pp_end_date ) {
				Debug::text(' Setting PP end date to Termination Date: '. TTDate::GetDate('DATE', $termination_date) , __FILE__, __LINE__, __METHOD__,10);
				$pp_end_date = $termination_date;

                                $total_pay_period_days = 30;
			}

			Debug::text(' Using Pay Period End Date: '. TTDate::GetDate('DATE', $pp_end_date) , __FILE__, __LINE__, __METHOD__,10);
			 $total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date, $pp_end_date) );
		} else {

			Debug::text(' Using Prev Effective Date: '. TTDate::GetDate('DATE', $prev_wage_effective_date ) , __FILE__, __LINE__, __METHOD__,10);
			$total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date, $prev_wage_effective_date ) );
		}

		Debug::text('Salary: '. $salary .' Total Pay Period Days: '. $total_pay_period_days .' Wage Effective Days: '. $total_wage_effective_days , __FILE__, __LINE__, __METHOD__,10);

                if($total_pay_period_days > $total_wage_effective_days){

                    $total_pay_period_days = 30;
                }
                else{
                    $total_wage_effective_days = 30;
                }


		 $pro_rate_salary = bcmul( $salary, bcdiv($total_wage_effective_days, $total_pay_period_days) );
 //echo bcdiv($total_wage_effective_days, $total_pay_period_days);exit;
		Debug::text('Pro Rate Salary: '. $pro_rate_salary, __FILE__, __LINE__, __METHOD__,10);
		return $pro_rate_salary;
	}


        static function proRateBudgetory($salary, $wage_effective_date, $prev_wage_effective_date, $pp_start_date, $pp_end_date, $termination_date ) {
		$prev_wage_effective_date = (int)$prev_wage_effective_date;

		if ( $wage_effective_date < $pp_start_date ) {
			$wage_effective_date = $pp_start_date;
		}

                /* commented by thusitha request by Mr Printoe 2018-03-09 */

		//$total_pay_period_days = ceil( TTDate::getDayDifference( $pp_start_date, $pp_end_date) );

                $total_pay_period_days = 30;

               // $total_pay_period_days = 30;

		if ( $prev_wage_effective_date == 0 ) {
			//ProRate salary to termination date if its in the middle of a pay period.
			if ( $termination_date != '' AND $termination_date > 0 AND $termination_date < $pp_end_date ) {
				Debug::text(' Setting PP end date to Termination Date: '. TTDate::GetDate('DATE', $termination_date) , __FILE__, __LINE__, __METHOD__,10);
				$pp_end_date = $termination_date;

                                $total_pay_period_days = 30;
			}

			Debug::text(' Using Pay Period End Date: '. TTDate::GetDate('DATE', $pp_end_date) , __FILE__, __LINE__, __METHOD__,10);
			$total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date, $pp_end_date) );
		} else {
			Debug::text(' Using Prev Effective Date: '. TTDate::GetDate('DATE', $prev_wage_effective_date ) , __FILE__, __LINE__, __METHOD__,10);
			$total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date, $prev_wage_effective_date ) );
		}

		Debug::text('Salary: '. $salary .' Total Pay Period Days: '. $total_pay_period_days .' Wage Effective Days: '. $total_wage_effective_days , __FILE__, __LINE__, __METHOD__,10);

                if($total_pay_period_days > $total_wage_effective_days){

                    $total_pay_period_days = 30;
                }
                //echo $wage_effective_date.' '.$pp_end_date;exit;
		//$pro_rate_salary = $salary * ($total_wage_effective_days / $total_pay_period_days);
		$pro_rate_salary = bcmul( $salary, bcdiv($total_wage_effective_days, $total_pay_period_days) );

		Debug::text('Pro Rate Salary: '. $pro_rate_salary, __FILE__, __LINE__, __METHOD__,10);
		return $pro_rate_salary;
	}



        static function proReversRateBudgetory($salary, $wage_effective_date, $prev_wage_effective_date, $pp_start_date, $pp_end_date, $termination_date ) {
		$prev_wage_effective_date = (int)$prev_wage_effective_date;

		if ( $wage_effective_date < $pp_start_date ) {
			$wage_effective_date = $pp_start_date;
		}

                /* commented by thusitha request by Mr Printoe 2018-03-09 */

		//$total_pay_period_days = ceil( TTDate::getDayDifference( $pp_start_date, $pp_end_date) );

                $total_pay_period_days = 30;

               // $total_pay_period_days = 30;

		if ( $prev_wage_effective_date == 0 ) {
			//ProRate salary to termination date if its in the middle of a pay period.
			if ( $termination_date != '' AND $termination_date > 0 AND $termination_date < $pp_end_date ) {
				Debug::text(' Setting PP end date to Termination Date: '. TTDate::GetDate('DATE', $termination_date) , __FILE__, __LINE__, __METHOD__,10);
				$pp_end_date = $termination_date;

                                $total_pay_period_days = 30;
			}

			Debug::text(' Using Pay Period End Date: '. TTDate::GetDate('DATE', $pp_end_date) , __FILE__, __LINE__, __METHOD__,10);
			$total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date, $pp_end_date) );
		} else {
			Debug::text(' Using Prev Effective Date: '. TTDate::GetDate('DATE', $prev_wage_effective_date ) , __FILE__, __LINE__, __METHOD__,10);
			$total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date, $prev_wage_effective_date ) );
		}

		Debug::text('Salary: '. $salary .' Total Pay Period Days: '. $total_pay_period_days .' Wage Effective Days: '. $total_wage_effective_days , __FILE__, __LINE__, __METHOD__,10);

                if($total_pay_period_days > $total_wage_effective_days){

                    $total_pay_period_days = 30;
                }
                //echo $wage_effective_date.' '.$pp_end_date;exit;
		//$pro_rate_salary = $salary * ($total_wage_effective_days / $total_pay_period_days);
		$pro_rate_salary = bcmul( $total_pay_period_days, bcdiv($salary, $total_wage_effective_days) );

		Debug::text('Pro Rate Salary: '. $pro_rate_salary, __FILE__, __LINE__, __METHOD__,10);
		return $pro_rate_salary;
	}



	static function getWageFromArray( $date, $wage_arr ) {
		if ( !is_array($wage_arr) ) {
			return FALSE;
		}

		if ( $date == '' ) {
			return FALSE;
		}

		//Debug::Arr($wage_arr, 'Wage Array: ', __FILE__, __LINE__, __METHOD__,10);

		foreach( $wage_arr as $effective_date => $wage ) {
			if ( $effective_date <= $date ) {
				Debug::Text('Effective Date: '. TTDate::getDate('DATE+TIME', $effective_date) .' Is Less Than: '. TTDate::getDate('DATE+TIME', $date)  , __FILE__, __LINE__, __METHOD__,10);
				return $wage;
			}
		}

		return FALSE;
	}

	//Takes the employees
	static function calculateLaborBurdenPercent( $company_id, $user_id ) {
		if ( $company_id == '' ) {
			return FALSE;
		}
		if ( $user_id == '' ) {
			return FALSE;
		}

		$end_epoch = TTDate::getTime();
		$start_epoch = TTDate::getTime()-(86400*180); //6mths

		$retval = FALSE;

		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyID( $company_id );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pself = new PayStubEntryListFactory();
			$total_gross = $pself->getAmountSumByUserIdAndEntryNameIdAndStartDateAndEndDate($user_id, $pseallf->getCurrent()->getTotalGross(), $start_epoch, $end_epoch );
			$total_employer_deductions = $pself->getAmountSumByUserIdAndEntryNameIdAndStartDateAndEndDate($user_id, $pseallf->getCurrent()->getTotalEmployerDeduction(), $start_epoch, $end_epoch );

			if ( isset($total_employer_deductions['amount']) AND isset($total_gross['amount']) ) {
				$retval = bcmul( bcdiv( $total_employer_deductions['amount'], $total_gross['amount']), 100, 2);
			}
		}

		return $retval;
	}

	function preSave() {
		if ( $this->getType() == 10 ) { //Hourly
			$this->setWeeklyTime( NULL );
			$this->setHourlyRate( $this->getWage() ); //Match hourly rate to wage.
		}

		return TRUE;
	}

	function Validate() {
		if ( $this->getDeleted() == FALSE ) {
				$this->Validator->isTrue(		'effective_date',
												$this->isValidEffectiveDate( $this->getEffectiveDate() ),
												('An employees first wage entry must be effective on or before the employees Appointment Date'));
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getId().$this->getUser() ); //Used in some reports.

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'effective_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'wage_group':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'currency':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'currency_symbol':
							$data[$variable] = TTi18n::getCurrencySymbol( $this->getColumn( 'iso_code' ) );
							break;
						case 'effective_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, ('Employee Wage'), NULL, $this->getTable(), $this );
	}
}
?>
