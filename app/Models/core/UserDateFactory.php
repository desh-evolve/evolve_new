<?php

namespace App\Models\Core;

use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\Punch\PunchControlListFactory;
use App\Models\Schedule\ScheduleListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserPreferenceListFactory;
use Illuminate\Support\Facades\Log;

class UserDateFactory extends Factory {
	protected $table = 'user_date';
	protected $pk_sequence_name = 'user_date_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $pay_period_obj = NULL;

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

	function getPayPeriodObject() {
		if ( is_object($this->pay_period_obj) ) {
			return $this->pay_period_obj;
		} else {
			$pplf = new PayPeriodListFactory();
			$this->pay_period_obj = $pplf->getById( $this->getPayPeriod() )->getCurrent();

			return $this->pay_period_obj;
		}
	}

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
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function findPayPeriod() {
		Debug::Text('Attempting to find Pay Period for Start Date: '. $this->getDateStamp() , __FILE__, __LINE__, __METHOD__,10);

		if ( $this->getDateStamp() > 0 AND $this->getUser() > 0 ) {
			//FIXME: With MySQL since it doesn't handle timezones very well I think we need to
			//get the timezone of the payperiod schedule for this user, and set the timezone to that
			//before we go searching for a pay period, otherwise the wrong payperiod might be returned.
			//This might happen when the MySQL server is in one timezone (ie: CST) and the pay period
			//schedule is set to another timezone (ie: PST)
			//This could severely slow down a lot of operations though, so make this specific to MySQL only.
			$pplf = new PayPeriodListFactory();
			$pplf->getByUserIdAndEndDate( $this->getUser(), $this->getDateStamp() );
			$pay_period = $pplf->getCurrent();

			Debug::Text('Pay Period Id: '. $pay_period->getId() , __FILE__, __LINE__, __METHOD__,10);

			if ( $pay_period->getId() !== FALSE ) {
				return $pay_period->getId();
			}

		}

		Debug::Text('Attempt failed: ', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}
	function getPayPeriod() {
		if ( isset($this->data['pay_period_id']) ) {
			return $this->data['pay_period_id'];
		}

		return FALSE;
	}
	function setPayPeriod($id = NULL) {
		$id = trim($id);

		if ( $id == NULL ) {
			$id = $this->findPayPeriod();
		}

		$pplf = new PayPeriodListFactory();

		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		if (
				$id == FALSE
				OR
				$this->Validator->isResultSetWithRows(	'pay_period',
														$pplf->getByID($id),
														TTi18n::gettext('Invalid Pay Period')
														) ) {
			$this->data['pay_period_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $this->data['date_stamp'] );
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'date_stamp',
												$epoch,
												TTi18n::gettext('Incorrect date'))
			) {

			if 	( $epoch > 0 ) {
				$this->data['date_stamp'] = $epoch;

				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date'));
			}


		}

		return FALSE;
	}

	static function findOrInsertUserDate($user_id, $date, $timezone = NULL ) {
		
		$date = TTDate::getMiddleDayEpoch( $date ); //Use mid day epoch so the timezone conversion across DST doesn't affect the date.

                
		if ( $timezone == NULL ) {
			//Find the employees preferred timezone, base the user date off that instead of the pay period timezone,
			//as it can be really confusing to the user if they punch in at 10AM on Sept 27th, but it records as Sept 26th because
			//the PP Schedule timezone is 12hrs different or something.
			$uplf = new UserPreferenceListFactory();
			$uplf->getByUserID( $user_id );
			if ( $uplf->getRecordCount() > 0 ) {
				$timezone = $uplf->getCurrent()->getTimeZone();
			}
		}
		$date = TTDate::convertTimeZone( $date, $timezone ); //die;
                echo $date;
		Debug::text(' Using TimeZone: '. $timezone .' Date: '. TTDate::getDate('DATE+TIME', $date) .'('.$date.')', __FILE__, __LINE__, __METHOD__,10);

		$udlf = new UserDateListFactory();
		$udlf->getByUserIdAndDate( $user_id, $date );
		if ( $udlf->getRecordCount() == 1 ) {
			$id = $udlf->getCurrent()->getId();
			Debug::text(' Found Already Existing User Date ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
			return $id;
		} elseif ( $udlf->getRecordCount() == 0 ) {
			Debug::text(' Inserting new UserDate row.', __FILE__, __LINE__, __METHOD__,10);

			//Insert new row
			$udf = new UserDateFactory();
			$udf->setUser( $user_id );
			$udf->setDateStamp( $date );
			$udf->setPayPeriod(); 

			if ( $udf->isValid() ) {
				return $udf->Save();
			} else {
				Debug::text(' INVALID user date row. Pay Period Locked?', __FILE__, __LINE__, __METHOD__,10);
			}
		} elseif ( $udlf->getRecordCount() > 1 ) {
			Debug::text(' More then 1 user date row was detected!!: '. $udlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		}

		Debug::text(' Cant find or insert User Date ID. User ID: '. $user_id .' Date: '. $date, __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	static function getUserDateID($user_id, $date) {
		$user_date_id = UserDateFactory::findOrInsertUserDate( $user_id, $date);
		Debug::text(' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);
		if ( $user_date_id != '' ) {
			return $user_date_id;
		}
		Debug::text(' No User Date ID found', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	//This function deletes all rows from other tables that require a user_date row.
	//We need to keep this in its own function so we can call it BEFORE
	//actually deleting the user_date row. As we need to have a unique
	//index on user_id,date_stamp so we never get duplicate rows, essentially making the deleted
	//column useless.
	static function deleteChildren( $user_date_id ) {
		if (  $user_date_id == '' ) {
			return FALSE;
		}

	}

	function isUnique() {
		if ( $this->getUser() == FALSE ) {
			return FALSE;
		}

		if ( $this->getDateStamp() == FALSE  ) {
			return FALSE;
		}

		$ph = array(
					'user_id' => $this->getUser(),
					'date_stamp' => $this->db->BindDate( $this->getDateStamp() ),
					);

		$query = 'select id from '. $this->getTable() .' where user_id = ? AND date_stamp = ? AND deleted=0';
		$user_date_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_date_id,'Unique User Date.', __FILE__, __LINE__, __METHOD__,10);

		if ( $user_date_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_date_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function Validate() {
		//Make sure pay period isn't locked!
		if ( $this->getPayPeriod() !== FALSE ) {
			if ( $this->getPayPeriodObject()->getIsLocked() == TRUE ) {
				$this->Validator->isTRUE(	'pay_period',
											FALSE,
											TTi18n::gettext('Pay Period is Currently Locked') );
			}
		}

		//Make sure this is a UNIQUE user_date row.
		$this->Validator->isTRUE(	'date_stamp',
									$this->isUnique(),
									TTi18n::gettext('Employee can not have duplicate entries on the same day') );


		//Make sure the date isn't BEFORE the first pay period.
		$pplf = new PayPeriodListFactory();
		$pplf->getByUserID( $this->getUser(), NULL, NULL, NULL, array('a.start_date' => 'asc') );
		if ( $pplf->getRecordCount() > 0 ) {
			$first_pp_obj = $pplf->getCurrent();
			if ( $this->getDateStamp() < $first_pp_obj->getStartDate() ) {
				$this->Validator->isTRUE(	'pay_period',
											FALSE,
											TTi18n::gettext('Pay Period Missing').'(b)' );
			}
		} else {
			$this->Validator->isTRUE(	'pay_period',
										FALSE,
										TTi18n::gettext('Pay Period Missing') );
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getDeleted() == TRUE ) {
			//Delete (for real) any already deleted rows in hopes to prevent a
			//unique index conflict across user_id,date_stamp,deleted
			$udlf = new UserDateListFactory();
			$udlf->deleteByUserIdAndDateAndDeleted( $this->getUser(), $this->getDateStamp(), TRUE );
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		//Debug::Text('Post Save... Deleted: '. (int)$this->getDeleted(), __FILE__, __LINE__, __METHOD__,10);

		//Delete punch control/schedules assigned to this.
		if ( $this->getDeleted() == TRUE ) {

			//Delete schedules assigned to this user date.
			//Turn off any re-calc's
			$slf = new ScheduleListFactory();
			$slf->getByUserDateID( $this->getId() );
			if ( $slf->getRecordCount() > 0 ) {
				foreach( $slf as $schedule_obj ) {
					$schedule_obj->setDeleted(TRUE);
					$schedule_obj->Save();
				}
			}

			$pclf = new PunchControlListFactory();
			$pclf->getByUserDateID( $this->getId() );
			if ( $pclf->getRecordCount() > 0 ) {
				foreach( $pclf as $pc_obj ) {
					$pc_obj->setDeleted(TRUE);
					$pc_obj->Save();
				}
			}

			//Delete exceptions
			$elf = new ExceptionListFactory();
			$elf->getByUserDateID( $this->getId() );
			if ( $elf->getRecordCount() > 0 ) {
				foreach( $elf as $e_obj ) {
					$e_obj->setDeleted(TRUE);
					$e_obj->Save();
				}
			}

			//Delete user_date_total rows too
			$udtlf = new UserDateTotalListFactory();
			$udtlf->getByUserDateID( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				foreach( $udtlf as $udt_obj ) {
					$udt_obj->setDeleted(TRUE);
					$udt_obj->Save();
				}
			}
		}

		return TRUE;
	}
}
?>
