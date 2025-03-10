<?php

namespace App\Models\PayPeriod;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class PayPeriodFactory extends Factory {
	protected $table = 'pay_period';
	protected $pk_sequence_name = 'pay_period_id_seq'; //PK Sequence name

	var $pay_period_schedule_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => ('OPEN'),
										12 => ('Locked - Pending Approval'), //Go to this state as soon as date2 is passed
										//15 => ('Locked - Pending Transaction'), //Go to this as soon as approved, or 48hrs before transaction date.
										20 => ('CLOSED'), //Once paid
										30 => ('Post Adjustment')
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => ('Type'),
										'-1020-status' => ('Status'),
										'-1030-pay_period_schedule' => ('Pay Period Schedule'),

										'-1040-start_date' => ('Start Date'),
										'-1050-end_date' => ('End Date'),
										'-1060-transaction_date' => ('Transaction Date'),

										'-1500-total_punches' => ('Punches'),
										'-1505-pending_requests' => ('Pending Requests'),
										'-1510-exceptions_critical' => ('Critical'),
										'-1510-exceptions_high' => ('High'),
										'-1512-exceptions_medium' => ('Medium'),
										'-1514-exceptions_low' => ('Low'),
										'-1520-verified_timesheets' => ('Verified'),
										'-1522-pending_timesheets' => ('Pending'),
										'-1524-total_timesheets' => ('Total'),
										'-1530-ps_amendments' => ('PS Amendments'),
										'-1540-pay_stubs' => ('Pay Stubs'),

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
								'pay_period_schedule',
								'type',
								'status',
								'start_date',
								'end_date',
								'transaction_date'
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'start_date',
								'end_date',
								'transaction_date',
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
											'company_id' => 'Company',
											'status_id' => 'Status',
											'status' => FALSE,
											'type_id' => FALSE,
											'type' => FALSE,
											'pay_period_schedule_id' => 'PayPeriodSchedule',
											'pay_period_schedule' => FALSE,
											'start_date' => 'StartDate',
											'end_date' => 'EndDate',
											'transaction_date' => 'TransactionDate',
											//'advance_transaction_date' => 'AdvanceTransactionDate',
											//'advance_transaction_date' => 'Primary',
											//'is_primary' => 'PayStubStatus',
											//'tainted' => 'Tainted',
											//'tainted_date' => 'TaintedDate',
											//'tainted_by' => 'TaintedBy',

											'total_punches' => 'TotalPunches',
											'pending_requests' => 'PendingRequests',
											'exceptions_critical' => 'Exceptions',
											'exceptions_high' => 'Exceptions',
											'exceptions_medium' => 'Exceptions',
											'exceptions_low' => 'Exceptions',
											'verified_timesheets' => 'TimeSheets',
											'pending_timesheets' => 'TimeSheets',
											'total_timesheets' => 'TimeSheets',
											'ps_amendments' => 'PayStubAmendments',
											'pay_stubs' => 'PayStubs',

											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getPayPeriodScheduleObject() {
		if ( is_object($this->pay_period_schedule_obj) ) {
			return $this->pay_period_schedule_obj;
		} else {
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			//$this->pay_period_schedule_obj = $ppslf->getById( $this->getPayPeriodSchedule() )->getCurrent();
			$ppslf->getById( $this->getPayPeriodSchedule() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$this->pay_period_schedule_obj = $ppslf->getCurrent();
				return $this->pay_period_schedule_obj;
			}

			return FALSE;
		}
	}

	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return $this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = (int)trim($status);

		$status_options = $this->getOptions('status');
		$validate_msg = ('Invalid Status');

		Debug::Text('Current Status: '. $this->getStatus() .' New Status: '. $status, __FILE__, __LINE__, __METHOD__,10);
		switch ( $this->getStatus() ) {
			case 20: //Closed
				$valid_statuses = array( 20, 30 );
				$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
				$validate_msg = ('Status can only be changed from Closed to Post Adjustment');
				break;
			case 30: //Post Adjustment
				$valid_statuses = array( 20, 30 );
				$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
				$validate_msg = ('Status can only be changed from Post Adjustment to Closed');
				break;
			default:
				break;
		}

		if ( $this->Validator->inArrayKey(	'status_id',
											$status,
											$validate_msg,
											$status_options ) ) {

			$this->data['status_id'] = $status;

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriodSchedule() {
		if ( isset($this->data['pay_period_schedule_id']) ) {
			return $this->data['pay_period_schedule_id'];
		}

		return FALSE;
	}
	function setPayPeriodSchedule($id) {
		$id = trim($id);

		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'pay_period_schedule',
															$ppslf->getByID($id),
															('Incorrect Pay Period Schedule')
															) ) {
			$this->data['pay_period_schedule_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isValidStartDate($epoch) {
		if ( $this->isNew() ) {
			$id = 0;
		} else {
			$id = $this->getId();
		}

		$ph = array(
					'pay_period_schedule_id' => (int)$this->getPayPeriodSchedule(),
					'start_date' => $this->db->BindTimeStamp($epoch),
					'end_date' => $this->db->BindTimeStamp($epoch),
					'id' => (int)$id,
					);

		//Used to have LIMIT 1 at the end, but GetOne() should do that for us.
		$query = 'select id from '. $this->getTable() .'
					where	pay_period_schedule_id = ?
						AND start_date <= ?
						AND end_date >= ?
						AND deleted=0
						AND id != ?
					';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id,'Pay Period ID of conflicting pay period: '. $epoch, __FILE__, __LINE__, __METHOD__,10);

		if ( $id === FALSE ) {
			Debug::Text('aReturning TRUE!', __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				Debug::Text('bReturning TRUE!', __FILE__, __LINE__, __METHOD__,10);
				return TRUE;
			}
		}

		Debug::Text('Returning FALSE!', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function getPayPeriodDates( $filter_start_date = NULL, $filter_end_date = NULL, $include_pay_period_id = FALSE ) {
		//Debug::Text('Start Date: '. TTDate::getDate('DATE', $this->getStartDate()) .' End Date: '. TTDate::getDate('DATE', $this->getEndDate()) .' Filter: Start: '. TTDate::getDate('DATE', $filter_start_date ) .' End: '. TTDate::getDate('DATE', $filter_end_date), __FILE__, __LINE__, __METHOD__,10);
		if ( $this->getStartDate() > 0 AND $this->getEndDate() > 0 ) {

			for( $i = (int)$this->getStartDate(); $i <= (int)$this->getEndDate(); $i += 93600) {
				$i = TTDate::getBeginDayEpoch($i);

				if ( ( $filter_start_date == '' OR $filter_start_date <= $i )
						AND ( $filter_end_date == '' OR $filter_end_date >= $i ) ) {
					if ( $include_pay_period_id == TRUE ) {
						$retarr[TTDate::getAPIDate('DATE', $i)] = $this->getID();
					} else {
						$retarr[] = TTDate::getAPIDate('DATE', $i);
					}
				} else {
					Debug::Text('Filter didnt match!', __FILE__, __LINE__, __METHOD__,10);
				}
			}

			//Debug::Arr($retarr, 'Pay Period Dates: ', __FILE__, __LINE__, __METHOD__,10);

			return $retarr;
		}

		return FALSE;
	}

	function getStartDate( $raw = FALSE ) {
		if ( isset($this->data['start_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['start_date'];
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $this->data['start_date'] );
			}
		}

		return FALSE;
	}
	function setStartDate($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'start_date',
												$epoch,
												('Incorrect start date'))
				AND
				$this->Validator->isTrue(		'start_date',
												$this->isValidStartDate($epoch),
												('Conflicting start date'))

			) {

			//$this->data['start_date'] = $epoch;
			$this->data['start_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndDate( $raw = FALSE ) {
		if ( isset($this->data['end_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['end_date'];
			} else {
				return TTDate::strtotime( $this->data['end_date'] );
			}
		}

		return FALSE;
	}
	function setEndDate($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'end_date',
												$epoch,
												('Incorrect end date')) ) {

			//$this->data['end_date'] = $epoch;
			$this->data['end_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getTransactionDate( $raw = FALSE ) {
		if ( isset($this->data['transaction_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['transaction_date'];
			} else {
				return TTDate::strtotime( $this->data['transaction_date'] );
			}
		}

		return FALSE;
	}
	function setTransactionDate($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'transaction_date',
												$epoch,
												('Incorrect transaction date')) ) {

			$this->data['transaction_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getAdvanceEndDate( $raw = FALSE ) {
		if ( isset($this->data['advance_end_date']) ) {
			return TTDate::strtotime($this->data['advance_end_date']);
		}

		return FALSE;
	}
	function setAdvanceEndDate($epoch) {
		$epoch = trim($epoch);

		if 	(	$epoch == FALSE
				OR
				$this->Validator->isDate(		'advance_end_date',
												$epoch,
												('Incorrect advance end date')) ) {

			$this->data['advance_end_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getAdvanceTransactionDate() {
		if ( isset($this->data['advance_transaction_date']) ) {
			return TTDate::strtotime($this->data['advance_transaction_date']);
			/*
			if ( (int)$this->data['advance_transaction_date'] == 0 ) {
				return strtotime( $this->data['advance_transaction_date'] );
			} else {
				return $this->data['advance_transaction_date'];
			}
			*/
		}

		return FALSE;
	}
	function setAdvanceTransactionDate($epoch) {
		$epoch = trim($epoch);

		if 	(	$epoch == FALSE
				OR
				$this->Validator->isDate(		'advance_transaction_date',
												$epoch,
												('Incorrect advance transaction date')) ) {

			$this->data['advance_transaction_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getPrimary() {
		return $this->fromBool( $this->data['is_primary'] );
	}
	function setPrimary($bool) {
		$this->data['is_primary'] = $this->toBool($bool);

		return true;
	}

	function setPayStubStatus($status) {
		Debug::text('setPayStubStatus: '. $status, __FILE__, __LINE__, __METHOD__, 10);

		$this->StartTransaction();

		$pslf = TTnew( 'PayStubListFactory' );
		$pslf->getByPayPeriodId( $this->getId() );
		foreach($pslf as $pay_stub) {
			//Only change status of advance pay stubs if we're in the advance part of the pay period.
			//What if the person is too late, set status anyways?
			if ( $pay_stub->getStatus() != $status
					/*
					AND (
							(
							$this->getPayPeriodScheduleObject()->getType() == 40
								AND TTDate::getTime() < $this->getAdvanceTransactionDate()
								AND $pay_stub->getAdvance() == TRUE
							)
						OR
							$pay_stub->getAdvance() == FALSE
						)
					*/
				) {

				Debug::text('Changing Status of Pay Stub ID: '. $pay_stub->getId(), __FILE__, __LINE__, __METHOD__, 10);
				$pay_stub->setStatus($status);
				$pay_stub->save();
			}
		}

		$this->CommitTransaction();

		return TRUE;
	}

	function getTainted() {
		return $this->fromBool( $this->data['tainted'] );
	}
	function setTainted($bool) {
		$this->data['tainted'] = $this->toBool($bool);

		return true;
	}

	function getTaintedDate() {
		if ( isset($this->data['tainted_date']) ) {
			return $this->data['tainted_date'];
		}

		return FALSE;
	}
	function setTaintedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'tainted_date',
												$epoch,
												('Incorrect tainted date') ) ) {

			$this->data['tainted_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}
	function getTaintedBy() {
		if ( isset($this->data['tainted_by']) ) {
			return $this->data['tainted_by'];
		}

		return FALSE;
	}
	function setTaintedBy($id = NULL) {
		$id = trim($id);

		if ( empty($id) ) {
			global $current_user;

			if ( is_object($current_user) ) {
				$id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'tainted_by',
													$ulf->getByID($id),
													('Incorrect tainted employee')
													) ) {

			$this->data['tainted_by'] = $id;

			return TRUE;
		}

		return FALSE;
	}

        
    function getIsHrProcess() {
		if ( isset($this->data['is_hr_process']) ) {
			return $this->data['is_hr_process'];
		}

		return FALSE;
	}
        
        
    function setIsHrProcess($hrProcess= NULL) {
                        
           $hrProcess = (int)trim($hrProcess);
           
		if ( isset($hrProcess) ) {
                    
			$this->data['is_hr_process'] = $hrProcess;

			return TRUE;
		}

		return FALSE;
	}
        
        
	function getTimeSheetVerifyType() {
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			return $this->getPayPeriodScheduleObject()->getTimeSheetVerifyType();
		}

		return FALSE;
	}
	function getTimeSheetVerifyWindowStartDate() {
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			return (int)$this->getEndDate()-( $this->getPayPeriodScheduleObject()->getTimeSheetVerifyBeforeEndDate()*86400 );
		}

		return $this->getEndDate();
	}
	function getTimeSheetVerifyWindowEndDate() {
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			return (int)$this->getTransactionDate()-( $this->getPayPeriodScheduleObject()->getTimeSheetVerifyBeforeTransactionDate()*86400 );
		}

		return $this->getTransactionDate();
	}

	function getIsLocked() {
		if ( $this->getStatus() == 10 OR $this->getStatus() == 30 OR $this->isNew() == TRUE ) {
			return FALSE;
		}

		return TRUE;
	}

	function getName($include_schedule_name = FALSE) {
		$schedule_name = NULL;
		if ( $include_schedule_name == TRUE ) {
			$schedule_name = '('. $this->getPayPeriodScheduleObject()->getName() .') ';
		}

		$retval = $schedule_name . TTDate::getDate('DATE', $this->getStartDate() ).' -> '. TTDate::getDate('DATE', $this->getEndDate() );

		return $retval;
	}

	function getEnableImportData() {
		if ( isset($this->import_data) ) {
			return $this->import_data;
		}

		return FALSE;
	}
	function setEnableImportData($bool) {
		$this->import_data = $bool;

		return TRUE;
	}

	//Imports only data not assigned to other pay periods
	function importOrphanedData() {
		$pps_obj = $this->getPayPeriodScheduleObject();

		if ( is_object( $pps_obj ) ) {
			//Get all users assigned to this pp schedule
			$udlf = TTnew( 'UserDateListFactory' );
			$udlf->getByUserIdAndStartDateAndEndDateAndEmptyPayPeriod( $pps_obj->getUser(), $this->getStartDate(), $this->getEndDate() );
			Debug::text(' Pay Period ID: '. $this->getId() .' Pay Period orphaned User Date Rows: '. $udlf->getRecordCount() .' Start Date: '. TTDate::getDate('DATE+TIME', $this->getStartDate() ) .' End Date: '. TTDate::getDate('DATE+TIME', $this->getEndDate() ), __FILE__, __LINE__, __METHOD__,10);
			if ( $udlf->getRecordCount() > 0 ) {
				$udlf->StartTransaction();
				foreach( $udlf as $ud_obj ) {
					$ud_obj->setPayPeriod( $this->getId() );
					if ( $ud_obj->isValid() ) {
						$ud_obj->Save();
					}
				}
				$ud_obj->CommitTransaction();
			}

			return TRUE;
		}

		return FALSE;
	}

	//Imports all data from other pay periods into this one.
	function importData() {
		$pps_obj = $this->getPayPeriodScheduleObject();

		$udlf = TTnew( 'UserDateListFactory' );

		$udlf->StartTransaction();
		$udlf->getByUserIdAndStartDateAndEndDate($pps_obj->getUser(), $this->getStartDate(), $this->getEndDate() );
		Debug::text(' Pay Period ID: '. $this->getId() .' Pay Period User Date Rows: '. $udlf->getRecordCount() .' Start Date: '. TTDate::getDate('DATE+TIME', $this->getStartDate() ) .' End Date: '. TTDate::getDate('DATE+TIME', $this->getEndDate() ), __FILE__, __LINE__, __METHOD__,10);
		foreach($udlf as $ud_obj) {
			$new_pay_period_id = $ud_obj->findPayPeriod();
			Debug::Text('Current Pay Period: '. $ud_obj->getPayPeriod() .' ('.$this->getID() .') New PayPeriod ID: '. $new_pay_period_id , __FILE__, __LINE__, __METHOD__,10);

			//Make sure original pay period isn't already closed
			if ( $this->getID() == $new_pay_period_id
					AND $new_pay_period_id != $ud_obj->getPayPeriod()
					AND $ud_obj->getPayPeriodObject()->getStatus() != 20) {
				$ud_obj->setPayPeriod( $new_pay_period_id );
				if ( $ud_obj->isValid() ) {
					$ud_obj->Save();
				}
			} else {
				Debug::Text('Pay Period is already closed, OR Current Pay Period DOES NOT match New PayPeriod ID OR no changes are needed!!', __FILE__, __LINE__, __METHOD__,10);
			}
			unset($new_pay_period_id);
		}
		//$udlf->FailTransaction();
		$udlf->CommitTransaction();

		return TRUE;
	}

	//Delete all data assigned to this pay period.
	function deleteData() {
		Debug::text('Deleting all data from pay period: '. $this->getID(), __FILE__, __LINE__, __METHOD__, 10);
		//Remove all user_date rows, punch control, punches, and schedules
		$udlf = TTnew( 'UserDateListFactory' );
		$udlf->getByPayPeriodID( $this->getId() );
		Debug::text(' Pay Period ID: '. $this->getId() .' Pay Period User Date Rows: '. $udlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		if ( $udlf->getRecordCount() > 0 ) {
				$udlf->StartTransaction();
				Debug::text('Delete User Date Rows: '. $udlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				foreach($udlf as $ud_obj ) {
					$ud_obj->setDeleted(TRUE);
					$ud_obj->Save();
				}
				$ud_obj->CommitTransaction();
		} else {
				Debug::text('No User Date Rows to Delete', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	function getPendingRequests() {
		if ( $this->getCompany() != '' AND $this->isNew() == FALSE ) {
			//Get all pending requests
			$rlf = TTnew( 'RequestListFactory' );
			$rlf->getSumByCompanyIDAndPayPeriodIdAndStatus( $this->getCompany(), $this->getID(), 30 );
			if ( $rlf->getRecordCount() == 1 ) {
				return $rlf->getCurrent()->getColumn('total');
			}

			return 0;
		}

		return FALSE;
	}

	function getExceptions() {
		$retarr = array(
						'exceptions_low' => 0,
						'exceptions_medium' => 0,
						'exceptions_high' => 0,
						'exceptions_critical' => 0,
						);

		$elf = TTnew( 'ExceptionListFactory' );
		$elf->getSumExceptionsByPayPeriodIdAndBeforeDate( $this->getID(), $this->getEndDate() );
		if ( $elf->getRecordCount() > 0 ) {
			//Debug::Text(' Found Exceptions: '. $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			foreach($elf as $e_obj ) {
				if ( $e_obj->getColumn('severity_id') == 10 ) {
					$retarr['exceptions_low'] = $e_obj->getColumn('count');
				}
				if ( $e_obj->getColumn('severity_id') == 20 ) {
					$retarr['exceptions_medium'] = $e_obj->getColumn('count');
				}
				if ( $e_obj->getColumn('severity_id') == 25 ) {
					$retarr['exceptions_high']= $e_obj->getColumn('count');
				}
				if ( $e_obj->getColumn('severity_id') == 30 ) {
					$retarr['exceptions_critical']= $e_obj->getColumn('count');
				}
			}
		} else {
			//Debug::Text(' No Exceptions!', __FILE__, __LINE__, __METHOD__,10);
		}

		return $retarr;
	}

	function getTotalPunches() {
		//Count how many punches are in this pay period.
		$plf = TTnew( 'PunchListFactory' );
		$retval = $plf->getByPayPeriodId( $this->getID() )->getRecordCount();
		Debug::Text(' Total Punches: '. $retval, __FILE__, __LINE__, __METHOD__,10);
		return $retval;
	}

	function getTimeSheets() {
		$retarr = array(
						'verified_timesheets' => 0,
						'pending_timesheets' => 0,
						'total_timesheets' => 0,
						);

		//Get verified timesheets
		$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
		$pptsvlf->getByPayPeriodIdAndCompanyId( $this->getID(), $this->getCompany() );
		if ( $pptsvlf->getRecordCount() > 0 ) {
			foreach( $pptsvlf as $pptsv_obj ) {
				if ( $pptsv_obj->getAuthorized() == TRUE ) {
					$retarr['verified_timesheets']++;
				} elseif (  $pptsv_obj->getStatus() == 30 OR $pptsv_obj->getStatus() == 45 ) {
					$retarr['pending_timesheets']++;
				}
			}
		}

		//Get total employees with time for this pay period.
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$retarr['total_timesheets'] = $udtlf->getWorkedUsersByPayPeriodId( $this->getID() );

		return $retarr;
	}

	function getPayStubAmendments() {
		//Get PS Amendments.
		$psalf = TTnew( 'PayStubAmendmentListFactory' );
		$psalf->getByCompanyIdAndAuthorizedAndStartDateAndEndDate( $this->getCompany(), TRUE, $this->getStartDate(), $this->getEndDate() );
		$total_ps_amendments = 0;
		if ( is_object($psalf) ) {
			$total_ps_amendments = $psalf->getRecordCount();
		}

		Debug::Text(' Total PS Amendments: '. $total_ps_amendments, __FILE__, __LINE__, __METHOD__,10);
		return $total_ps_amendments;
	}

	function getPayStubs() {
		//Count how many pay stubs for each pay period.
		$pslf = TTnew( 'PayStubListFactory' );
		$total_pay_stubs = $pslf->getByPayPeriodId( $this->getId() )->getRecordCount();
		//Debug::Text(' Total Pay Stubs: '. $total_pay_stubs, __FILE__, __LINE__, __METHOD__,10);
		return $total_pay_stubs;
	}

	function Validate() {
		//Make sure there aren't conflicting pay periods.
		//Start date checks that...

		//Make sure End Date is after Start Date, and transaction date is the same or after End Date.
		Debug::text('Start Date: '. $this->getStartDate() .' End Date: '. $this->getEndDate(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getStartDate() != '' AND $this->getEndDate() != '' AND $this->getEndDate() <= $this->getStartDate() ) {
			$this->Validator->isTrue(		'end_date',
											FALSE,
											('Conflicting end date'));
		}

		if ( $this->getEndDate() != '' AND $this->getTransactionDate() != '' AND $this->getTransactionDate() < $this->getEndDate() ) {
			$this->Validator->isTrue(		'transaction_date',
											FALSE,
											('Conflicting transaction date'));
		}

		if ( ( $this->getStatus() == 20 OR $this->getStatus() == 30 ) AND $this->getEndDate() > 0 AND TTDate::getBeginDayEpoch( time() ) <= $this->getEndDate() ) {
			$this->Validator->isTrue(		'status_id',
											FALSE,
											('Invalid status, unable to lock or close pay periods before their end date.'));

		}

		//Make sure if this a monthly+advanc pay period, advance dates are set.
        $ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$ppslf->getById( $this->getPayPeriodSchedule() );
		if ( $ppslf->getRecordCount() == 1 AND is_object($ppslf) ) {
			Debug::text('Pay Period Type is NOT Monthly + Advance... Advance End Date: '. $this->getAdvanceEndDate(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->getAdvanceEndDate() != '' ) {
				$this->Validator->isTrue(		'advance_end_date',
												FALSE,
												('Advance end date is set') );
			}

			if ( $this->getAdvanceTransactionDate() != '' ) {
				$this->Validator->isTrue(		'advance_transaction_date',
												FALSE,
												('Advance transaction date is set') );
			}
		} elseif ( $this->getPayPeriodSchedule() == '' ) { // Used to be != for some reason?
			Debug::text('Pay Period Schedule not found: '. $this->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);
			$this->Validator->isTrue(		'pay_period_schedule_id',
											FALSE,
											('Incorrect Pay Period Schedule') );
		}

		return TRUE;
	}

	function preSave() {
		$this->StartTransaction();

		if ( $this->getStatus() == 30 ) {
			$this->setTainted(TRUE);
		}

		//Only update these when we are setting the pay period to Post-Adjustment status.
		if ( $this->getStatus() == 30 AND $this->getTainted() == TRUE ) {
			$this->setTaintedBy();
			$this->setTaintedDate();
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == TRUE ) {
			Debug::text('Delete TRUE: ', __FILE__, __LINE__, __METHOD__, 10);
			//Unassign user_date rows from this pay period, no need to delete this data anymore as it can be easily done otherways and users don't realize
			//how much data will actually be deleted.
			$udf = TTnew( 'UserDateFactory' );

			$query = 'update '. $udf->getTable() .' set pay_period_id = 0 where pay_period_id = '. (int)$this->getId();
			DB::select($query);
		} else {
			if ( $this->getStatus() == 20 ) { //Closed
				//Mark pay stubs as PAID once the pay period is closed?
				TTLog::addEntry( $this->getId(), 20,  ('Setting Pay Period to Closed'), NULL, $this->getTable() );
				$this->setPayStubStatus(40);
			} elseif ( $this->getStatus() == 30 ) {
				TTLog::addEntry( $this->getId(), 20,  ('Setting Pay Period to Post-Adjustment'), NULL, $this->getTable() );
			}

			if ( $this->getEnableImportData() == TRUE ) {
				$this->importOrphanedData();
			}
		}

		$this->CommitTransaction();

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'start_date':
						case 'end_date':
						case 'transaction_date':
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

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
	        $ppsf = TTnew( 'PayPeriodScheduleFactory' );

			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'type':
							//Make sure type_id is set first.
							$data[$variable] = Option::getByKey( $this->getColumn('type_id'), $ppsf->getOptions( $variable ) );
							break;
						case 'type_id':
						case 'pay_period_schedule':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'start_date':
						case 'end_date':
						case 'transaction_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() );
							}
							break;
						case 'total_punches':
						case 'pending_requests':
						case 'ps_amendments':
						case 'pay_stubs':
							//These functions are slow to obtain, so make sure the column is requested explicitly before we include it.
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->$function();
							}
							break;
						case 'exceptions_critical':
						case 'exceptions_high':
						case 'exceptions_medium':
						case 'exceptions_low':
							//These functions are slow to obtain, so make sure the column is requested explicitly before we include it.
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								if ( !isset($exceptions_arr) ) {
									$exceptions_arr = $this->getExceptions();
								}

								$data[$variable] = $exceptions_arr[$variable];
							}
							break;
						case 'verified_timesheets':
						case 'pending_timesheets':
						case 'total_timesheets':
							//These functions are slow to obtain, so make sure the column is requested explicitly before we include it.
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								if ( !isset($timesheet_arr) ) {
									$timesheet_arr = $this->getTimeSheets();
								}

								$data[$variable] = $timesheet_arr[$variable];
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  ('Pay Period') .' - '. ('Start Date') .': '. TTDate::getDate('DATE+TIME', $this->getStartDate() ) .' '. ('End Date') .': '. TTDate::getDate('DATE+TIME', $this->getEndDate() ) .' '. ('Transaction Date') .': '. TTDate::getDate('DATE+TIME', $this->getTransactionDate() ), NULL, $this->getTable(), $this );
	}
}
?>
