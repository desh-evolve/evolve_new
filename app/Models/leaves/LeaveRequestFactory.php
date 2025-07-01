<?php


namespace App\Models\Leaves;

use App\Models\Company\CompanyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;

class LeaveRequestFactory  extends Factory {
    //put your code here

    	protected $table = 'leave_request';
	    protected $pk_sequence_name = 'leave_request_id_seq'; //PK Sequence name

	    protected $company_obj = NULL;
        protected $user_obj = NULL;
        protected $leave_policy_obj = NULL;
        protected $designation_obj =  NULL;



        function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => ('Paid'),
										12 => ('Paid (Above Salary)'),
										20 => ('Unpaid'),
										30 => ('Dock'),
									);
				break;
			case 'leave_method': //Types that are considered paid.
				$retval = array(
										1 => ('Full Day Leave'),
										2 => ('Half day Leave'),
										3 => ('Short Leave'),

									);
				break;
                        case 'leave_status': //Types that are considered paid.
				$retval = array(
										10 => ('ACTIVE'),
										20 => ('COVER REJECTED'),
										30 => ('SUPERVISOR REJECTED'),
                                        40 => ('HR REJECTED'),

									);
				break;
                        case 'paid_type': //Types that are considered paid.
				$retval = array(10,12);
				break;

		}

		return $retval;
	}




        function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
											'id' => 'ID',
											'company_id' => 'Company',
											'type_id' => 'Type',
											'type' => FALSE,
											'name' => 'Name',
											'rate' => 'Rate',
											'wage_group_id' => 'WageGroup',
											'accrual_rate' => 'AccrualRate',
											'accrual_policy_id' => 'AccrualPolicyID',
											'accrual_policy' => FALSE,
											'pay_stub_entry_account_id' => 'PayStubEntryAccountId',
											'deleted' => 'Deleted',
											);
			return $variable_function_map;
		}



        function getCompanyObject() {
            if ( is_object($this->company_obj) ) {
                return $this->company_obj;
            } else {
                $clf = new CompanyListFactory();
                $this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

                return $this->company_obj;
            }
        }

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}

	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$clf = new CompanyListFactory();

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

    function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$aplf = new UserListFactory();
			$this->user_obj = $aplf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
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

			Debug::Text('User ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
			$ulf = new UserListFactory();

			if ( $this->Validator->isResultSetWithRows(	'user',
														$ulf->getByID($id),
														('User is invalid')
														) ) {

				$this->data['user_id'] = $id;

				return TRUE;
			}

			return FALSE;
		}


        function getDesignationObject() {
			if ( is_object($this->designation_obj) ) {
				return $this->designation_obj;
			} else {
				$aplf = new UserTitleListFactory();
				$this->designation_obj = $aplf->getById( $this->getDesignation() )->getCurrent();

				return $this->designation_obj;
			}
		}

       	function getDesignation() {
			if ( isset($this->data['designation_id']) ) {
				return $this->data['designation_id'];
			}

			return FALSE;
		}


     function setDesignation($id, $skip = false) {
		if(empty($id) && $skip){ //added by desh for leave request(2025-05-09)
			return true;
		}
		$id = trim($id);

		Debug::Text('User ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$ulf = new UserTitleListFactory();

		if ( $this->Validator->isResultSetWithRows(	'designation',
													$ulf->getByID($id),
													('Designation is invalid')
													) ) {

			$this->data['designation_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}



    function getAccuralPolicyObject() {
		if ( is_object($this->leave_policy_obj) ) {
			return $this->leave_policy_obj;
		} else {
			$aplf = new AccrualPolicyListFactory();
			$this->leave_policy_obj = $aplf->getById( $this->getAccuralPolicy() )->getCurrent();

			return $this->leave_policy_obj;
		}
	}


        function getAccuralPolicy(){

            if ( isset($this->data['accurals_policy_id']) ) {
				return $this->data['accurals_policy_id'];
			}

			return FALSE;
        }


    function setAccuralPolicy($id) {
		$id = trim($id);

		Debug::Text('Accrual ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$aplf = new AccrualPolicyListFactory();

		if ( $this->Validator->isResultSetWithRows(	'accrualpolicy',
													$aplf->getByID($id),
													('Accrual Policy is invalid')
													) ) {

			$this->data['accurals_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}



        function getAmount(){

            if ( isset($this->data['amount']) ) {
				return $this->data['amount'];
			}

			return FALSE;

        }


       	function setAmount($amount) {
			$amount = trim($amount);


						if(isset($amount)){
				$this->data['amount'] = $amount;

				return TRUE;
						}

			return FALSE;
		}



        function getLeaveFrom(){

            if ( isset($this->data['leave_from']) ) {
				return $this->data['leave_from'];
			}

			return FALSE;

        }


       function setLeaveFrom($leaveFrom) {
		$leaveFrom = trim($leaveFrom);


                    if(isset($leaveFrom)){
			$this->data['leave_from'] = $leaveFrom;

			return TRUE;
                    }

		return FALSE;
	}




        function getLeaveTo(){

            if ( isset($this->data['leave_to']) ) {
				return $this->data['leave_to'];
			}

			return FALSE;

        }


    function setLeaveTo($leaveTo) {
		$leaveTo = trim($leaveTo);


                    if(isset($leaveTo)){
			$this->data['leave_to'] = $leaveTo;

			return TRUE;
                    }

		return FALSE;
	}



        function getReason(){

            if ( isset($this->data['reason']) ) {
				return $this->data['reason'];
			}

			return FALSE;

        }


    function setReason($reason) {
		$reason = trim($reason);


                    if(isset($reason)){
			$this->data['reason'] = $reason;

			return TRUE;
                    }

		return FALSE;
	}





        function getAddressTelephone(){

            	if ( isset($this->data['address_telephone']) ) {
			return $this->data['address_telephone'];
		}

		return FALSE;

        }


    function setAddressTelephone($address) {
		$address = trim($address);


                    if(isset($address)){
			$this->data['address_telephone'] = $address;

			return TRUE;
                    }

		return FALSE;
	}



       function getLeaveTime(){

            	if ( isset($this->data['leave_time']) ) {
			return $this->data['leave_time'];
		}

		return FALSE;

        }


       function setLeaveTime($lvtime) {
		$lvtime = trim($lvtime);


                    if(isset($lvtime)){
			$this->data['leave_time'] = $lvtime;

			return TRUE;
                    }

		return FALSE;
	}





       function getLeaveEndTime(){

            	if ( isset($this->data['leave_end_time']) ) {
			return $this->data['leave_end_time'];
		}

		return FALSE;

        }


       function setLeaveEndTime($lvtime) {
		$lvtime = trim($lvtime);


                    if(isset($lvtime)){
			$this->data['leave_end_time'] = $lvtime;

			return TRUE;
                    }

		return FALSE;
	}



        function getCoveredBy(){

            	if ( isset($this->data['covered_by']) ) {
			return $this->data['covered_by'];
		}

		return FALSE;

        }


       function setCoveredBy($covered_by) {
		$covered_by = trim($covered_by);


                    if(isset($covered_by)){
			$this->data['covered_by'] = $covered_by;

			return TRUE;
                    }

		return FALSE;
	}





        function getSupervisorId(){

            	if ( isset($this->data['supervisor_id']) ) {
			return $this->data['supervisor_id'];
		}

		return FALSE;

        }


       function setSupervisorId($supervisor_id) {
		$supervisor_id = trim($supervisor_id);


                    if(isset($supervisor_id)){
			$this->data['supervisor_id'] = $supervisor_id;

			return TRUE;
                    }

		return FALSE;
	}


         function getCoveredApproved(){

            	if ( isset($this->data['is_covered_approved']) ) {
			return $this->data['is_covered_approved'];
		}

		return FALSE;

        }


       function setCoveredApproved($is_covered_approved) {
		$is_covered_approved = trim($is_covered_approved);


                    if(isset($is_covered_approved)){
			$this->data['is_covered_approved'] = $is_covered_approved;

			return TRUE;
                    }

		return FALSE;
	}



       function getSupervisorApproved(){

            if ( isset($this->data['is_supervisor_approved']) ) {
			return $this->data['is_supervisor_approved'];
		}

		return FALSE;

        }


       function setSupervisorApproved($is_supervisor_approved) {
		$is_supervisor_approved = trim($is_supervisor_approved);


                    if(isset($is_supervisor_approved)){
			$this->data['is_supervisor_approved'] = $is_supervisor_approved;

			return TRUE;
                    }

		return FALSE;
	}




       function getHrApproved(){

            	if ( isset($this->data['is_hr_approved']) ) {
			return $this->data['is_hr_approved'];
		}

		return FALSE;

        }


       function setHrApproved($is_hr_approved) {
		$is_hr_approved = trim($is_hr_approved);


                    if(isset($is_hr_approved)){
			$this->data['is_hr_approved'] = $is_hr_approved;

			return TRUE;
                    }

		return FALSE;
	}



       function getDeleted(){

            	if ( isset($this->data['deleted']) ) {
			return $this->data['deleted'];
		}

		return FALSE;

        }


       function setDeleted($deleted) {
		$deleted = trim($deleted);


                    if(isset($deleted)){
			$this->data['deleted'] = $deleted;

			return TRUE;
                    }

		return FALSE;
	}


        function getLeaveMethod(){

            	if ( isset($this->data['method']) ) {
			return $this->data['method'];
		}

		return FALSE;

        }


       function setLeaveMethod($method) {
		$method = trim($method);


                    if(isset($method)){
			$this->data['method'] = $method;

			return TRUE;
                    }

		return FALSE;
	}



       function getStatus(){

            	if ( isset($this->data['status']) ) {
			return $this->data['status'];
		}

		return FALSE;

        }


       function setStatus($status) {
		$status = trim($status);


                    if(isset($status)){
			$this->data['status'] = $status;

			return TRUE;
                    }

		return FALSE;
	}



        function getLeaveDates(){

            	if ( isset($this->data['leave_dates']) ) {
			return $this->data['leave_dates'];
		}

		return FALSE;

        }


       function setLeaveDates($leaveDates) {
		$leaveDates = trim($leaveDates);


                    if(isset($leaveDates)){
			$this->data['leave_dates'] = $leaveDates;

			return TRUE;
                    }

		return FALSE;
	}
}
