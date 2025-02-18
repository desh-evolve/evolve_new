<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5002 $
 * $Id: UserGenericStatusFactory.class.php 5002 2011-07-19 19:51:13Z ipso $
 * $Date: 2011-07-19 12:51:13 -0700 (Tue, 19 Jul 2011) $
 */

/**
 * @package Module_Users
 */
class UserGenericStatusFactory extends Factory {
	protected $table = 'user_generic_status';
	protected $pk_sequence_name = 'user_generic_status_id_seq'; //PK Sequence name
	protected $batch_sequence_name = 'user_generic_status_batch_id_seq'; //PK Sequence name

	protected $batch_id = NULL;
	protected $queue = NULL;
	static protected $static_queue = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('Failed'),
										20 => TTi18n::gettext('Warning'),
										//25 => TTi18n::gettext('Notice'), //Friendly than a warning.
										30 => TTi18n::gettext('Success'),
									);
				break;

		}

		return $retval;
	}


	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getNextBatchID() {
		$this->batch_id = $this->db->GenID( $this->batch_sequence_name );

		return $this->batch_id;
	}
	function getBatchID() {
		if ( isset($this->data['batch_id']) ) {
			return $this->data['batch_id'];
		}

		return FALSE;
	}
	function setBatchID($val) {
		$val = trim($val);
		if (	$this->Validator->isNumeric(	'batch_id',
												$val,
												TTi18n::gettext('Invalid Batch ID') )
						) {

			$this->data['batch_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return FALSE;
		}

		return FALSE;
	}

	function getLabel() {
		if ( isset($this->data['label']) ) {
			return $this->data['label'];
		}

		return FALSE;
	}
	function setLabel($val) {
		$val = trim($val);
		if (	$this->Validator->isLength(	'label',
											$val,
											TTi18n::gettext('Invalid label'),
											1,1024)
						) {

			$this->data['label'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($val) {
		$val = trim($val);
		if (	$val == ''
				OR
				$this->Validator->isLength(	'description',
											$val,
											TTi18n::gettext('Invalid description'),
											1,1024)
						) {

			$this->data['description'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getLink() {
		if ( isset($this->data['link']) ) {
			return $this->data['link'];
		}

		return FALSE;
	}
	function setLink($val) {
		$val = trim($val);
		if (	$val == ''
				OR
				$this->Validator->isLength(	'link',
											$val,
											TTi18n::gettext('Invalid link'),
											1,1024)
						) {

			$this->data['link'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	//Static Queue functions
	static function getStaticQueue() {
		return self::$static_queue;
	}
	static function clearStaticQueue() {
		self::$static_queue = NULL;

		return TRUE;
	}
	static function queueGenericStatus($label, $status, $description = NULL, $link = NULL ) {
		Debug::Text('Add Generic Status row to queue... Label: '. $label .' Status: '. $status, __FILE__, __LINE__, __METHOD__,10);
		$arr = array(
					'label' => $label,
					'status' => $status,
					'description' => $description,
					'link' => $link
					);

		self::$static_queue[] = $arr;

		return TRUE;
	}


	//Non-Static Queue functions
	function setQueue( $queue ) {
		$this->queue = $queue;

		UserGenericStatusFactory::clearStaticQueue();

		return TRUE;
	}

	function saveQueue() {
		if ( is_array($this->queue) ) {
			Debug::Arr($this->queue, 'Generic Status Queue', __FILE__, __LINE__, __METHOD__,10);
			foreach( $this->queue as $key => $queue_data ) {

				$ugsf = TTnew( 'UserGenericStatusFactory' );
				$ugsf->setUser( $this->getUser() );
				if ( $this->getBatchId() !== FALSE ) {
					$ugsf->setBatchID( $this->getBatchID() );
				} else {
					$this->setBatchId( $this->getNextBatchId() );
				}

				$ugsf->setLabel( $queue_data['label'] );
				$ugsf->setStatus( $queue_data['status'] );
				$ugsf->setDescription( $queue_data['description'] );
				$ugsf->setLink( $queue_data['link'] );

				if ( $ugsf->isValid() ) {
					$ugsf->Save();

					unset($this->queue[$key]);
				}
			}

			return TRUE;
		}

		Debug::Text('Generic Status Queue Empty', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	/*
	function addGenericStatus($label, $status, $description = NULL, $link = NULL ) {
		$this->setLabel( $label );
		$this->setStatus( $status );
		$this->setDescription( $description );
		$this->setLink( $link );

		$batch_id = $this->getBatchId();
		$user_id = $this->getUser();

		if ( $this->isValid() ) {
			$this->Save();

			$this->setBatchId( $batch_id );
			$this->setUser( $user_id );

			return TRUE;
		}

		return FALSE;
	}
	*/

	function preSave() {
		return TRUE;
	}
}
?>
