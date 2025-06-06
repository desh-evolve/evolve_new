<?php

namespace App\Models\PayPeriod;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\DB;

class PayPeriodScheduleUserFactory extends Factory {
	protected $table = 'pay_period_schedule_user';
	protected $pk_sequence_name = 'pay_period_schedule_user_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function getPayPeriodSchedule() {
		return $this->data['pay_period_schedule_id'];
	}
	function setPayPeriodSchedule($id) {
		$id = trim($id);

		$ppslf = new PayPeriodScheduleListFactory();

		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'pay_period_schedule',
															$ppslf->getByID($id),
															('Pay Period Schedule is invalid')
															) ) {
			$this->data['pay_period_schedule_id'] = $id;

			return TRUE;
		}

		return FALSE;
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
	function isUniqueUser($id) {
		$ppslf = new PayPeriodScheduleListFactory();

		$ph = array(
					':id' => $id,
					);

		$query = 'select a.id from '. $this->getTable() .' as a, '. $ppslf->getTable() .' as b where a.pay_period_schedule_id = b.id AND a.user_id = :id AND b.deleted=0';
		$user_id = DB::select($query, $ph);

		if (empty($user_id) || $user_id == FALSE ) {
            $user_id = 0;
        }else{
            $user_id = current(get_object_vars($user_id[0]));
        }
		Debug::Arr($user_id,'Unique User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

		if ( empty($user_id) || $user_id === FALSE ) {
			return TRUE;
		}

		return FALSE;
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

		if ( $id != 0
				AND $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('Selected Employee is invalid')
															)
				AND	$this->Validator->isTrue(		'user',
													$this->isUniqueUser($id),
													('Selected Employee is already assigned to another Pay Period')
													)
			) {

			$this->data['user_id'] = $id;

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
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getPayPeriodSchedule(), $log_action, ('Employee').': '. $u_obj->getFullName( FALSE, TRUE ) , NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>
