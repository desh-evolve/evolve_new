<?php

namespace App\Models\Core;

use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Company\BranchListFactory;
use App\Models\Company\CompanyFactory;
use App\Models\Leaves\AbsenceLeaveListFactory;
use App\Models\Leaves\AbsenceLeaveUserEntryRecordListFactory;
use App\Models\Leaves\AbsenceLeaveUserFactory;
use App\Models\Leaves\AbsenceLeaveUserListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Policy\AccrualPolicyMilestoneListFactory;
use App\Models\Schedule\ScheduleFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserWageFactory;
use App\Models\Users\UserWageListFactory;

class AJAX_Server {

	function getCurrentUserFullName() {
		global $current_user;

		return $current_user->getFullName();
	}

	function getCurrentCompanyName() {
		global $current_company;

		return $current_company->getName();
	}

	function getProvinceOptions( $country ) {
		Debug::Arr($country, 'aCountry: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_array($country) AND $country == '' ) {
			return FALSE;
		}

		if ( !is_array($country) ) {
			$country = array($country);
		}

		Debug::Arr($country, 'bCountry: ', __FILE__, __LINE__, __METHOD__, 10);

		$cf = new CompanyFactory();

		$province_arr = $cf->getOptions('province');

		$retarr = array();

		foreach( $country as $tmp_country ) {
			if ( isset($province_arr[strtoupper($tmp_country)]) ) {
				//Debug::Arr($province_arr[strtoupper($tmp_country)], 'Provinces Array', __FILE__, __LINE__, __METHOD__, 10);

				$retarr = array_merge( $retarr, $province_arr[strtoupper($tmp_country)] );
				//$retarr = array_merge( $retarr, Misc::prependArray( array( -10 => '--' ), $province_arr[strtoupper($tmp_country)] ) );
			}
		}

		if ( count($retarr) == 0 ) {
			$retarr = array('00' => '--');
		}

		return $retarr;
	}

	function getProvinceDistrictOptions( $country, $province) {
		if ( $country == '' ) {
			return FALSE;
		}

		if ( $province == '' ) {
			return FALSE;
		}
		Debug::text('Country: '. $country .' Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);

		$cf = new CompanyFactory();

		$district_arr = $cf->getOptions('district');

		if ( isset($district_arr[strtoupper($country)][strtoupper($province)]) ) {
			Debug::Arr($district_arr[strtoupper($country)][strtoupper($province)], 'District Array', __FILE__, __LINE__, __METHOD__, 10);
			return $district_arr[strtoupper($country)][strtoupper($province)];
		}

		return array();
	}

	function getProvinceInvoiceDistrictOptions( $country, $province) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( !is_array($country) AND $country == '' ) {
			return FALSE;
		}

		if ( !is_array($province) AND $province == '' ) {
			return FALSE;
		}

		if ( !is_array($country) ) {
			$country = array($country);
		}

		if ( !is_array($province) ) {
			$province = array($province);
		}

		Debug::text('Country: '. $country .' Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);

		$idlf = new InvoiceDistrictListFactory();
		$idlf->getByCompanyIdAndProvinceAndCountry( $current_company->getId(), $province, $country);

		$district_arr = $idlf->getArrayByListFactory($idlf, FALSE);

		if ( is_array($district_arr) ) {
			Debug::Arr($district_arr, 'District Array', __FILE__, __LINE__, __METHOD__, 10);
			return $district_arr;
		}

		return array();
	}

	function getHourlyRate( $wage, $weekly_hours, $wage_type_id = 10 ) {
		if ( $wage == '' ) {
			return '0.00';
		}

		if ( $weekly_hours == '' ) {
			return '0.00';
		}

		if ( $wage_type_id == '' ) {
			return '0.00';
		}

		$uwf = new UserWageFactory();
		$uwf->setType( $wage_type_id );
		$uwf->setWage( $wage );
		$uwf->setWeeklyTime( TTDate::parseTimeUnit($weekly_hours) );
                
		$hourly_rate = $uwf->calcHourlyRate();

		return $hourly_rate;
	}
	
        /**
         * Added by Thilini  2018/04/18 for aqua fresh
         */
        
        function getHourlyRateAqua( $wage, $weekly_hours, $wage_type_id = 10, $user_id) {
		if ( $wage == '' ) {
			return '0.00';
		}

		if ( $weekly_hours == '' ) {
			return '0.00';
		}

		if ( $wage_type_id == '' ) {
			return '0.00';
		}

		$uwf = new UserWageFactory();
		$uwf->setType( $wage_type_id );
		$uwf->setWage( $wage );
		$uwf->setWeeklyTime( TTDate::parseTimeUnit($weekly_hours) );
                $uwf->setUser( $user_id );
		$hourly_rate = $uwf->calcHourlyRate();

		return $hourly_rate;
	}
	
	/**
	 * ARSP NOTE -->
	 * THIS CODE ADDED BY ME FOR THUNDER & NEON
	 */
	function getBranchShortId( $default_branch_id ) {

		if ( $default_branch_id == '' ) {
			return NULL;
		}
                
		$blf = new BranchListFactory();
		$branch_short_id= $blf->getBranchShortIdById($default_branch_id);
		return $branch_short_id;
	}	

        /**
         * ARSP NOTE -->
         * THIS CODE ADDED BY ME, FOR THUNDER & NEON
         */
        function getNextHighestEmployeeNumberByBranch( $default_branch_id ) {

		if ( $default_branch_id == 0 ) {
			return NULL;
		}
                
                $ulf = new UserListFactory();
                $ulf->getHighestEmployeeNumberOnlyByBranchId( $default_branch_id );                  
                
                if ( $ulf->getRecordCount() > 0 ) {
                    
                        if ( is_numeric( $ulf->getCurrent()->getEmployeeNumberOnly() ) == TRUE ) {
                                        $next_available_employee_number_only = $ulf->getCurrent()->getEmployeeNumberOnly()+1;
				} else {
					$next_available_employee_number_only = NULL;
				}
			} else {
                                $next_available_employee_number_only = 1;                            
			} 
                
                return $next_available_employee_number_only;
	}           
        
		

        /**
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */
        function searchJobSkills($searching_value)
        {
            
//            if ( $searching_value == '' ) {
//                    return NULL;
//            }


            $ulf = new UserListFactory();
            $array = $ulf->getAllJobSkillsUniqueOptions();
            
//            foreach ($ulf as $user)
//            {
//                $user_data = array('id' => $user->getJobSkills());
//            }
//            print_r($user_data);
//            exit();
            
            $array1 = array(
                            "ActionScript",
                            "AppleScript",
                            "Asp",
                            "BASIC",
                            "C",
                            "C++",
                            "Clojure",
                            "COBOL",
                            "ColdFusion",
                            "Erlang",
                            "Fortran",
                            "Groovy",
                            "Haskell",
                            "Java",
                            "JALSA",
                            "JavaScript",
                            "Lisp",
                            "Perl",
                            "PHP",
                            "Python",
                            "Ruby",
                            "Scala",
                            "Scheme"
                           );
            //echo "Hi this is ARSP test";
            //exit();
            return $array;
    
        }
		
	function getUserHourlyRate( $user_id, $date ) {
		Debug::text('User ID: '. $user_id .' Date: '. $date, __FILE__, __LINE__, __METHOD__, 10);
		if ( $user_id == '' ) {
			return '0.00';
		}

		if ( $date == '' ) {
			$date = TTDate::getTime();
		}

		$epoch = TTDate::parseDateTime($date);

		$uwlf = new UserWageListFactory();
		$uwlf->getByUserIdAndDate( $user_id, $epoch);
		if ( $uwlf->getRecordCount() > 0 ) {
			$hourly_rate = $uwlf->getCurrent()->getHourlyRate();

			return $hourly_rate;
		}

		return '0.00';
	}

	function getUserLaborBurdenPercent( $user_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return '0.00';
		}

		$retval = UserWageFactory::calculateLaborBurdenPercent( $current_company->getId(), $user_id );

		if ( $retval == '' ) {
			return '0.00';
		}

		return $retval;
	}

	function getJobOptions( $user_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		Debug::text('User ID: '. $user_id .' Company ID: '. $current_company->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$jlf = new JobListFactory();
		return $jlf->getByCompanyIdAndUserIdAndStatusArray( $current_company->getId(),  $user_id, array(10,20,30,40), TRUE );
	}

	function getJobItemOptions( $job_id, $include_disabled = TRUE ) {
		//Don't check for current company as this needs to work when we are not fully authenticated.
		/*
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}
		*/

		Debug::text('Job ID: '. $job_id .' Include Disabled: '. (int)$include_disabled, __FILE__, __LINE__, __METHOD__, 10);

		$jilf = new JobItemListFactory();
		//$jilf->getByCompanyIdAndJobId( $current_company->getId(), $job_id );
		$jilf->getByJobId( $job_id );
		$job_item_options = $jilf->getArrayByListFactory( $jilf, TRUE, $include_disabled );
		if ( $job_item_options != FALSE AND is_array($job_item_options) ) {
				return $job_item_options;
		}

		Debug::text('Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);

		$retarr = array( '00' => '--');

		return $retarr;
	}

	function getJobItemData( $job_item_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		Debug::text('Job Item ID: '. $job_item_id .' Company ID: '. $current_company->getId(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $job_item_id == '' ) {
			return FALSE;
		}

		if ( $current_company->getID() == '' ) {
			return FALSE;
		}

		$jilf = new JobItemListFactory();
		$jilf->getByIdAndCompanyId( $job_item_id, $current_company->getId() );
		if ( $jilf->getRecordCount() > 0 ) {
			foreach( $jilf as $item_obj ) {
				$retarr = array(
									'id' => $item_obj->getId(),
									'product_id' => $item_obj->getProduct(),
									'group_id' => $item_obj->getGroup(),
									'type_id' => $item_obj->getType(),
									'other_id1' => $item_obj->getOtherID1(),
									'other_id2' => $item_obj->getOtherID2(),
									'other_id3' => $item_obj->getOtherID3(),
									'other_id4' => $item_obj->getOtherID4(),
									'other_id5' => $item_obj->getOtherID5(),
									'manual_id' => $item_obj->getManualID(),
									'name' => $item_obj->getName(),
									'description' => $item_obj->getDescription(),
									'estimate_time' => $item_obj->getEstimateTime(),
									'estimate_time_display' => TTDate::getTimeUnit( $item_obj->getEstimateTime() ),
									'estimate_quantity' => $item_obj->getEstimateQuantity(),
									'estimate_bad_quantity' => $item_obj->getEstimateBadQuantity(),
									'bad_quantity_rate' => $item_obj->getBadQuantityRate(),
									'billable_rate' => $item_obj->getBillableRate(),
									'minimum_time' => $item_obj->getMinimumTime(),
									'minimum_time_display' => TTDate::getTimeUnit( $item_obj->getMinimumTime() ),
									'created_date' => $item_obj->getCreatedDate(),
									'created_by' => $item_obj->getCreatedBy(),
									'updated_date' => $item_obj->getUpdatedDate(),
									'updated_by' => $item_obj->getUpdatedBy(),
									'deleted_date' => $item_obj->getDeletedDate(),
									'deleted_by' => $item_obj->getDeletedBy()
								);

				Debug::text('Returning Data...', __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('Returning False...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getProductQuantityUnitPrice( $product_id, $quantity, $currency_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		$plf = new ProductListFactory();
		$plf->getByIdAndCompanyId($product_id, $current_company->getId() );
		if ( $plf->getRecordCount() > 0 ) {
			$p_obj = $plf->getCurrent();

			Debug::text('Product ID: '. $product_id .' Quantity: '. $quantity .' SRC Currency: '. $p_obj->getCurrency() .' DST Currency: '. $currency_id, __FILE__, __LINE__, __METHOD__, 10);

			return CurrencyFactory::convertCurrency( $p_obj->getCurrency(), $currency_id, $p_obj->getQuantityUnitPrice( $quantity ) );
		}

		Debug::text('Returning FALSE', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	function getProductLockData($product_id, $part_number = NULL, $product_name = NULL, $product_upc = NULL, $currency_id = NULL ) {
		return $this->getProductData($product_id, $part_number, $product_name, $product_upc, $currency_id );
	}
	function getProductData( $product_id, $part_number = NULL, $product_name = NULL, $product_upc = NULL, $currency_id = NULL ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		Debug::text('Product ID: '. $product_id .' Part Number: '. $part_number .' Product Name: '. $product_name .' UPC: '. $product_upc .' Company ID: '. $current_company->getId(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $product_id == '' AND $part_number == '' AND $product_name == '' AND $product_upc == '') {
			return FALSE;
		}

		if ( $current_company->getID() == '' ) {
			return FALSE;
		}

		$plf = new ProductListFactory();

		if ( $product_id != '' ) {
			$plf->getByIdAndCompanyId($product_id, $current_company->getId() );
		} elseif ( $part_number != '' ) {
			Debug::text('Getting by Part Number ', __FILE__, __LINE__, __METHOD__, 10);
			$plf->getByPartNumberAndCompanyId($part_number, $current_company->getId() );
		} elseif( $product_name != '' ) {
			Debug::text('Getting by Name ', __FILE__, __LINE__, __METHOD__, 10);
			$plf->getByNameAndCompanyId($product_name, $current_company->getId() );
		} elseif( $product_upc != '' ) {
			Debug::text('Getting by UPC ', __FILE__, __LINE__, __METHOD__, 10);
			$plf->getByUPCAndCompanyId($product_upc, $current_company->getId() );
		}

		if ( $plf->getRecordCount() > 0 ) {
			$p_obj = $plf->getCurrent();

			$retarr = array(
								'id' => $p_obj->getId(),
								'name' => $p_obj->getName(),
								'description' => $p_obj->getDescription(),
								'type_id' => $p_obj->getType(),
								'status_id' => $p_obj->getStatus(),
								'part_number' => $p_obj->getPartNumber(),

								'currency_id' => $p_obj->getCurrency(),
								'unit_cost' => $p_obj->getUnitCost(),
								'unit_price' => CurrencyFactory::convertCurrency( $p_obj->getCurrency(), $currency_id, $p_obj->getQuantityUnitPrice( 1 ) ),
								//'unit_price' => $p_obj->getUnitPrice(),

								'weight_unit_id' => $p_obj->getWeightUnit(),
								'weight' => $p_obj->getWeight(),

								'dimension_unit_id' => $p_obj->getDimensionUnit(),
								'length' => $p_obj->getLength(),
								'width' => $p_obj->getWidth(),
								'height' => $p_obj->getHeight(),

								'price_locked' => $p_obj->getPriceLocked(),
								'description_locked' => $p_obj->getDescriptionLocked(),
							);

			Debug::text('Returning Data...', __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}

		Debug::text('Returning False...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function convertRawInvoiceDataToTransactionArray( $data ) {
		$transaction_arr = FALSE;
		if ( is_array($data) ) {
			foreach( $data as $transaction_key => $transaction_data ) {
				if ( isset($transaction_data[0]) AND $transaction_data[0] == 10 ) {
					//Debug::Text('Transaction Product ID: '. $transaction_data[0] .' Unit Price: '. $transaction_data[2] .' Quantity: '. $transaction_data[3], __FILE__, __LINE__, __METHOD__, 10);
					$transaction_arr[] = array(
									'id' => NULL,
									'type_id' => 10,
									'product_id' => $transaction_data[1],
									'product_type_id' => $transaction_data[2],
									'unit_price' => $transaction_data[3],
									'quantity' => $transaction_data[4],
									'currency_id' => $transaction_data[5],
									'amount' => bcmul( $transaction_data[3], $transaction_data[4] )
									);
				} elseif ( isset($transaction_data[0]) AND $transaction_data[0] == 20 ) {
					$transaction_arr[] = array(
									'id' => NULL,
									'type_id' => 20,
									'status_id' => $transaction_data[1],
									'amount' => $transaction_data[2]
									);
				}
			}
		}

		return $transaction_arr;
	}
	function getInvoiceTotalData( $data, $invoice_data, $include_unconfirmed_transactions = FALSE ) {
		//Debug::Arr($data, 'Input Transaction Data...', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($invoice_data, 'Input Invoice Data...', __FILE__, __LINE__, __METHOD__, 10);

		$ilf = new InvoiceListFactory();

		$transaction_arr = FALSE;
		if ( is_array($data) ) {
			$transaction_arr = $this->convertRawInvoiceDataToTransactionArray( $data );
			//Debug::Arr($data, 'bInput Transaction Data...', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($transaction_arr) AND is_array($transaction_arr) ) {
				//Calc taxes first, add those in as transactions.
				if ( !isset($invoice_data[0]) ) {
					$invoice_data[0] = NULL;
				}
				if ( !isset($invoice_data[1]) ) {
					$invoice_data[1] = NULL;
				}

				$tmp_taxes_arr = $ilf->calcTaxes( $transaction_arr, $invoice_data[0], $invoice_data[1] );
				if ( is_array($tmp_taxes_arr) ) {
					foreach( $tmp_taxes_arr as $ptp_id => $ptp_data ) {
						$transaction_arr[] = array(
										'id' => NULL,
										'type_id' => 10,
										'product_id' => $ptp_data['product_id'],
										'product_name' => $ptp_data['product_name'],
										'product_type_id' => 50,
										'amount' => $ptp_data['amount']
										);
					}
				}
			}

			Debug::Text('aCalc Shipping... Currency ID: '. $invoice_data[3], __FILE__, __LINE__, __METHOD__, 10);
			if ( isset($transaction_arr) AND is_array($transaction_arr) ) {
				Debug::Text('bCalc Shipping... Shipping Policy ID: '. $invoice_data[2], __FILE__, __LINE__, __METHOD__, 10);
				$tmp_shipping_arr = $ilf->calcShipping( $transaction_arr, $invoice_data[1], $invoice_data[2], $invoice_data[3] );
				$tmp_shipping_arr['type_id'] = 10;
				$tmp_shipping_arr['product_type_id'] = 60;

				if ( isset($tmp_shipping_arr['amount']) AND $tmp_shipping_arr['amount'] > 0 ) {
					$transaction_arr[] = $tmp_shipping_arr;
				}
			}
		}

		$retval = $ilf->getTotalArray( $transaction_arr, $include_unconfirmed_transactions );

		Debug::Arr($retval, 'Invoice getTotalArray()', __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getShippingOptions( $data, $invoice_data ) {
		$if = new InvoiceFactory();

		$transaction_arr = FALSE;
		if ( is_array($data) ) {
			$transaction_arr = $this->convertRawInvoiceDataToTransactionArray( $data );

			$weight_and_dimensions_arr = $if->getWeightAndDimensions( $transaction_arr );
			$shipping_option_data = $if->getShippingOptionData( $invoice_data[1],  $weight_and_dimensions_arr, $invoice_data[3] );

			$shipping_options = $if->getShippingOptions( $shipping_option_data, $invoice_data[3], TRUE );
		}

		if ( isset($shipping_options) ) {
			//Debug::Arr($shipping_options, 'Shipping Options: ', __FILE__, __LINE__, __METHOD__, 10);
			return $shipping_options;
		}

		return FALSE;
	}

	function getCurrencyData( $currency_id ) {
		Debug::Text('Getting Currency Data for ID: '. $currency_id, __FILE__, __LINE__, __METHOD__, 10);

		$clf = new CurrencyListFactory();
		$clf->getById( $currency_id );
		if ( $clf->getRecordCount() > 0 ) {
			$c_obj = $clf->getCurrent();

			$retarr = array(
							'id' => $c_obj->getId(),
							'conversion_rate' => $c_obj->getConversionRate(),
							'iso_code' => $c_obj->getISOCode()
							);

			return $retarr;
		}

		return FALSE;
	}

	function convertCurrency( $src_currency_id, $dst_currency_id, $amount ) {
		return CurrencyFactory::convertCurrency( $src_currency_id, $dst_currency_id, $amount );
	}

	function getScheduleTotalTime( $start, $end, $schedule_policy_id ) {
		$sf = new ScheduleFactory();
		$sf->setStartTime( TTDate::parseDateTime($start) );
		$sf->setEndTime( TTDate::parseDateTime($end) );
		$sf->setSchedulePolicyId( $schedule_policy_id );
		$sf->preSave();

		return TTDate::getTimeUnit( $sf->getTotalTime() );
	}

	function getAbsencePolicyData( $absence_policy_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		$aplf = new AbsencePolicyListFactory();
		$aplf->getByIdAndCompanyId( $absence_policy_id, $current_company->getId() );
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();

			$ap_data = $ap_obj->getObjectAsArray();

			$aplf = new AccrualPolicyListFactory();
			$aplf->getByIdAndCompanyId( $ap_obj->getAccrualPolicyID(), $current_company->getId() );
			if ( $aplf->getRecordCount() > 0 ) {
				$ap_data['accrual_policy_name'] = $aplf->getCurrent()->getName();
			} else {
				$ap_data['accrual_policy_name'] = 'None';
			}

			return $ap_data;
		}

		return FALSE;
	}

	function getAbsencePolicyBalance( $absence_policy_id, $user_id ) {
            
          //  return $this->getLeaveBalance($absence_policy_id,$user_id);
            
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		$aplf = new AbsencePolicyListFactory();
		$aplf->getByIdAndCompanyId( $absence_policy_id, $current_company->getId() );
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();
			if ( $ap_obj->getAccrualPolicyID() != '' ) { 
				return $this->getAccrualBalance( $ap_obj->getAccrualPolicyID(), $user_id );
			}
		}

		return FALSE;
	}
        
        function getLeaveBalance( $absence_policy_id, $user_id, $date = '', $repAbsence = 1) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}
		if ($date == '') {
            $date = date('Y');
		}
                 
                 //get total leaves for particular date year 
                $alulf = new AbsenceLeaveUserListFactory();
                $alulf->getEmployeeTotalLeaves($absence_policy_id, $user_id, $date);
                $total_assigned_leaves = 0;
                if(count($alulf) > 0){
                    foreach($alulf as $alulf_obj){
                        $total_assigned_leaves = $total_assigned_leaves + $alulf_obj->getAmount();
                    }
                } 
              
                 $ablf = new AccrualBalanceListFactory();
                 $ablf->getByUserIdAndAccrualPolicyId($user_id,$absence_policy_id);
                
                //get used Leave for particular date year
                 $aluerlf = new AbsenceLeaveUserEntryRecordListFactory();
                 $aluerlf->getByAbsencePolicyIdAndUserId2($absence_policy_id,$user_id);
                 $total_used_leaves = 0;
                 if(count($aluerlf) > 0){
                     
                    $allf1 = new AbsenceLeaveListFactory();
                     foreach($aluerlf as $aluerlf_obj){
                         $amount = $aluerlf_obj->getAmount();
                         //$amount = $allf1->getRelatedAmountTime($aluerlf_obj->getAbsenceLeaveId());
                         $total_used_leaves = $total_used_leaves + $amount;
                    }
                 }
                 //return 'tot - '.$amount;
                 
                $total_balance_leave = $total_assigned_leaves - $total_used_leaves;
                $allf = new AbsenceLeaveListFactory();
                
                $allf->getAll();
                
                foreach ($allf as $allf_obj){
                    $absence_leave[$allf_obj->getId()] = $allf_obj;  
                }
                
               // return 'total_assigned_leaves - '.$total_assigned_leaves.'total_used_leaves - '.$total_used_leaves;

                $absence_bal['full_day'] = floor($total_balance_leave/$absence_leave[1]->getTimeSec());
                $absence_bal['half_day'] = floor(($total_balance_leave%$absence_leave[1]->getTimeSec())/($absence_leave[$absence_leave[2]->getRelatedLeaveId()]->getTimeSec()/$absence_leave[2]->getRelatedLeaveUnit()));
                $absence_bal['short_leave'] = floor(($total_balance_leave%$absence_leave[1]->getTimeSec())/($absence_leave[$absence_leave[3]->getRelatedLeaveId()]->getTimeSec()/$absence_leave[3]->getRelatedLeaveUnit()));

              
                if($absence_bal['full_day']<0)
                	{$absence_bal['full_day']=0;}

	            if($absence_bal['half_day']<0)
	                {$absence_bal['half_day']=0;}
	            
	            if($absence_bal['short_leave']<0)
	                {$absence_bal['short_leave']=0;}
                 
                 

                return $absence_leave[1]->getShortCode().' - '.$absence_bal['full_day'].'  |  '.$absence_leave[2]->getShortCode().' - '.$absence_bal['half_day'].' or '.$absence_leave[3]->getShortCode().' - '.$absence_bal['short_leave'];
                 
                
                
                
        }
//FL ADDED FOR GET LEAVE BALANCE 20160729
        /*
	function getLeaveBalance( $absence_policy_id, $user_id, $date = '', $repAbsence = 1) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}
		if ($date == '') {
            $date = date('Y');
		}
                 
                 //get total leaves for particular date year 
                $alulf = new AbsenceLeaveUserListFactory();
                $alulf->getEmployeeTotalLeaves($absence_policy_id, $user_id, $date);
                $total_assigned_leaves = 0;
                if(count($alulf) > 0){
                    foreach($alulf as $alulf_obj){
                        $total_assigned_leaves = $total_assigned_leaves + $alulf_obj->getAmount();
                    }
                } 

                
                //get used Leave for particular date year
                 $aluerlf = new AbsenceLeaveUserEntryRecordListFactory();
                 $aluerlf->getByAbsencePolicyIdAndUserId2($absence_policy_id,$user_id);
                 $total_used_leaves = 0;
                 if(count($aluerlf) > 0){
                     
                    $allf1 = new AbsenceLeaveListFactory();
                     foreach($aluerlf as $aluerlf_obj){
                         $amount = $aluerlf_obj->getAmount();
                         //$amount = $allf1->getRelatedAmountTime($aluerlf_obj->getAbsenceLeaveId());
                         $total_used_leaves = $total_used_leaves + $amount;
                    }
                 }
                 //return 'tot - '.$amount;
                 
                $total_balance_leave = $total_assigned_leaves - $total_used_leaves;
                $allf = new AbsenceLeaveListFactory();
                
                $allf->getAll();
                
                foreach ($allf as $allf_obj){
                    $absence_leave[$allf_obj->getId()] = $allf_obj;  
                }
                
               // return 'total_assigned_leaves - '.$total_assigned_leaves.'total_used_leaves - '.$total_used_leaves;

                $absence_bal['full_day'] = floor($total_balance_leave/$absence_leave[1]->getTimeSec());
                $absence_bal['half_day'] = floor(($total_balance_leave%$absence_leave[1]->getTimeSec())/($absence_leave[$absence_leave[2]->getRelatedLeaveId()]->getTimeSec()/$absence_leave[2]->getRelatedLeaveUnit()));
                $absence_bal['short_leave'] = floor(($total_balance_leave%$absence_leave[1]->getTimeSec())/($absence_leave[$absence_leave[3]->getRelatedLeaveId()]->getTimeSec()/$absence_leave[3]->getRelatedLeaveUnit()));

              
                if($absence_bal['full_day']<0)
                	{$absence_bal['full_day']=0;}

	            if($absence_bal['half_day']<0)
	                {$absence_bal['half_day']=0;}
	            
	            if($absence_bal['short_leave']<0)
	                {$absence_bal['short_leave']=0;}
                 
                 

                return $absence_leave[1]->getShortCode().' - '.$absence_bal['full_day'].'  |  '.$absence_leave[2]->getShortCode().' - '.$absence_bal['half_day'].' or '.$absence_leave[3]->getShortCode().' - '.$absence_bal['short_leave'];
                 
	}
*/
        
        
                //FL ADDED 20160717
	function getAbsenceLeaveMethod( $absence_policy_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}
               
		if($absence_policy_id==1){
                    return 28800;
                }
                elseif ($absence_policy_id==2) {
                    
                  return 14400;
                }
                 elseif ($absence_policy_id==3) {
                    
                  return 5400;
                }
		

		return FALSE;
	}
        
        
        //FL ADDED 20160717
	function getAbsenceLeave( $absence_policy_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}
               
		$aplf = new AbsenceLeaveListFactory();
		$aplf->getById( $absence_policy_id);
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();
			if ( $ap_obj->getTimeSec() != '' ) {
				return $ap_obj->getTimeSec();
			}
		}

		return FALSE;
	}

	function getAccrualBalance( $accrual_policy_id, $user_id ) {
		if ( $accrual_policy_id == '' ) {
			return FALSE;
		}
		if ( $user_id == '' ) {
			return FALSE;
		}
		$ablf = new AccrualBalanceListFactory();
        $apmlf = new AccrualPolicyMilestoneListFactory();
        $apf = new AccrualPolicyListFactory();
        $ulf = new UserListFactory();
        $allf = new AbsenceLeaveListFactory();
                
		$ablf->getByUserIdAndAccrualPolicyId($user_id, $accrual_policy_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
                    //FL CHANGED FOR AUTOMATICALLY ADD LEAVE AMOUNT IF NOT ADDED---
                    //LEAVE AMOUNT DECIDED ACCRUAL POLICY MILE STONES
                    //20160721
                        $prev_obj = NULL;
                        $apmlf->getByAccrualPolicyId($accrual_policy_id,NULL,array(Misc::trimSortPrefix('length_of_service') => 'asc'));
                        $ulf->getById($user_id);
                        $user = $ulf->getCurrent();
                        $userHireYear = date('Y',$user->getHireDate());

                        $apf->getById($accrual_policy_id);
                        $absencePolicy = $apf->getCurrent();
                        
                        if($absencePolicy->getApplyFrequencyHireDate()==0 && $userHireYear != date('Y')){
                            foreach ($apmlf as $ms_obj){
                                 $workDays = floor((strtotime(date('Y').'-'.$absencePolicy->getApplyFrequencyMonth().'-'.$absencePolicy->getApplyFrequencyDayOfMonth()) - $user->getHireDate())/(60*60*24));
//                                 $workDays = floor((time() - $user->getHireDate())/(60*60*24));
                                 if($ms_obj->getLengthOfServiceDays() >= $workDays){
                                     $accrual_balance = $ms_obj->getMaximumTime();
                                     break;
                                 }

                                 $prev_obj = $ms_obj;
                            }    
                        
                        }          
                         $accrual_balance = $prev_obj->getMaximumTime();
                         
                        //Add Intitials Accruals after check accrual policy milestones
                        $af = new AbsenceLeaveUserFactory();
//                        $af->setId(null);
                        $af->setUserId($user_id);
                        $af->setAbsenceLeaveId(1);
                        $af->setAbsencePolicyId(2); 
                        $af->setAmount( $accrual_balance );
                        $af->setLeaveDateYear( date('Y')); 

                        if ( $af->isValid() ) { 
                            $aa = 'okoko';
//                                $af->Save();
  
                        }else{ 
                            $aa = 'nojnonono';
                        } 

                      //  return $aa.'--'.$prev_obj->getLengthOfServiceDays().'->-'.$workDays.'--'.$prev_obj->getMaximumTime();
                        
		}
                
                $allf->getAll();
                foreach ($allf as $allf_obj){
                    $absence_leave[$allf_obj->getId()] = $allf_obj; 
//                    $absence[]
                }
                $absence['full_day'] = floor($accrual_balance/$absence_leave[1]->getTimeSec());
                $absence['half_day'] = floor(($accrual_balance%$absence_leave[1]->getTimeSec())/($absence_leave[$absence_leave[2]->getRelatedLeaveId()]->getTimeSec()/$absence_leave[2]->getRelatedLeaveUnit()));
                $absence['short_leave'] = floor(($accrual_balance%$absence_leave[1]->getTimeSec())/($absence_leave[$absence_leave[3]->getRelatedLeaveId()]->getTimeSec()/$absence_leave[3]->getRelatedLeaveUnit()));
//                $absence['short_leave'] = floor(($accrual_balance%$absence_leave[1]->getTimeSec())/$absence_leave[1]->getTimeSec());
		return $absence_leave[1]->getShortCode().' - '.$absence['full_day'].'  |  '.$absence_leave[2]->getShortCode().' - '.$absence['half_day'].' or '.$absence_leave[3]->getShortCode().' - '.$absence['short_leave'];
//		return TTDate::getTimeUnit($accrual_balance);
	}

	function getNextPayStubAccountOrderByTypeId( $type_id ) {
		global $current_company;

		Debug::Text('Type ID: '. $type_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( $type_id == '' ) {
			return FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getHighestOrderByCompanyIdAndTypeId( $current_company->getId(), $type_id );
		if ( $psealf->getRecordCount() > 0 ) {
			foreach( $psealf as $psea_obj ) {
				return ($psea_obj->getOrder()+1);
			}
		}

		return FALSE;
	}

	function strtotime($str) {
		return TTDate::strtotime($str);
	}

	function parseDateTime($str) {
		return TTDate::parseDateTime( $str );
	}

	function getDate( $format, $epoch ) {
		return TTDate::getDate( $format, $epoch);
	}

	function getBeginMonthEpoch( $epoch ) {
		return TTDate::getBeginMonthEpoch( $epoch );
	}

	function getTimeZoneOffset( $time_zone ) {
		TTDate::setTimeZone( $time_zone );
		return TTDate::getTimeZoneOffset();
	}

	function test($str) {
		sleep(2);
		return $str;
	}

	function vardump($arr) {
		Debug::Arr($arr, 'vardump!', __FILE__, __LINE__, __METHOD__, 10);

		foreach( $arr as $key => $value ) {
			Debug::text('Key: '. $key .' Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array($value)  ) {
				foreach( $value as $keyb => $valueb ) {
					Debug::text('bKey: '. $keyb .' bValue: '. $valueb, __FILE__, __LINE__, __METHOD__, 10);
				}

			}
		}

	}

}
?>
