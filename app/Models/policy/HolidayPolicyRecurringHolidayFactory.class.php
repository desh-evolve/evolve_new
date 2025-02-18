<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: HolidayPolicyRecurringHolidayFactory.class.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */

/**
 * @package Module_Policy
 */
class HolidayPolicyRecurringHolidayFactory extends Factory {
	protected $table = 'holiday_policy_recurring_holiday';
	protected $pk_sequence_name = 'holiday_policy_recurring_holiday_id_seq'; //PK Sequence name

	protected $recurring_holiday_obj = NULL;

	function getRecurringHolidayObject() {
		if ( is_object($this->recurring_holiday_obj) ) {
			return $this->recurring_holiday_obj;
		} else {
			$lf = TTnew( 'RecurringHolidayListFactory' );
			$lf->getById( $this->getRecurringHoliday() );
			if ( $lf->getRecordCount() == 1 ) {
				$this->recurring_holiday_obj = $lf->getCurrent();
				return $this->recurring_holiday_obj;
			}

			return FALSE;
		}
	}

	function getHolidayPolicy() {
		if ( isset($this->data['holiday_policy_id']) ) {
			return $this->data['holiday_policy_id'];
		}

		return FALSE;
	}
	function setHolidayPolicy($id) {
		$id = trim($id);

		$hplf = TTnew( 'HolidayPolicyListFactory' );

		if (
			  $this->Validator->isNumeric(	'holiday_policy',
											$id,
											TTi18n::gettext('Holiday Policy is invalid')

			/*
			  $this->Validator->isResultSetWithRows(	'holiday_policy',
													$hplf->getByID($id),
													TTi18n::gettext('Holiday Policy is invalid')
			 */
															) ) {
			$this->data['holiday_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getRecurringHoliday() {
		if ( isset($this->data['recurring_holiday_id']) ) {
			return $this->data['recurring_holiday_id'];
		}
	}
	function setRecurringHoliday($id) {
		$id = trim($id);

		$rhlf = TTnew( 'RecurringHolidayListFactory' );

		if ( $id != 0
				AND $this->Validator->isResultSetWithRows(	'recurring_holiday',
															$rhlf->getByID($id),
															TTi18n::gettext('Selected Recurring Holiday is invalid')
															)
			) {

			$this->data['recurring_holiday_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {
		return FALSE;
	}

	function getCreatedDate() {
		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		return FALSE;
	}
	function getCreatedBy() {
		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		return FALSE;
	}

	function getUpdatedDate() {
		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		return FALSE;
	}
	function getUpdatedBy() {
		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		return FALSE;
	}


	function getDeletedDate() {
		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {
		return FALSE;
	}
	function getDeletedBy() {
		return FALSE;
	}
	function setDeletedBy($id = NULL) {
		return FALSE;
	}

	function addLog( $log_action ) {
		$obj = $this->getRecurringHolidayObject();
		if ( is_object($obj) ) {
			return TTLog::addEntry( $this->getHolidayPolicy(), $log_action,  TTi18n::getText('Recurring Holiday').': '. $obj->getName(), NULL, $this->getTable() );
		}
	}
}
?>
