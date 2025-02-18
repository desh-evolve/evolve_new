<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 3351 $
 * $Id: MessageFactory.class.php 3351 2010-02-18 17:22:09Z ipso $
 * $Date: 2010-02-18 09:22:09 -0800 (Thu, 18 Feb 2010) $
 */

/**
 * @package Module_Message
 */
class MessageSenderFactory extends Factory {
	protected $table = 'message_sender';
	protected $pk_sequence_name = 'message_sender_id_seq'; //PK Sequence name
	protected $obj_handler = NULL;

	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid Employee')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getParent() {
		if ( isset($this->data['parent_id']) ) {
			return $this->data['parent_id'];
		}

		return FALSE;
	}
	function setParent($id) {
		$id = trim($id);

		if ( empty($id) ) {
			$id = 0;
		}

		$mslf = TTnew( 'MessageSenderListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'parent',
															$mslf->getByID($id),
															TTi18n::gettext('Parent is invalid')
															) ) {
			$this->data['parent_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getMessageControl() {
		if ( isset($this->data['message_control_id']) ) {
			return $this->data['message_control_id'];
		}

		return FALSE;
	}
	function setMessageControl($id) {
		$id = trim($id);

		$mclf = TTnew( 'MessageControlListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'message_control_id',
													$mclf->getByID($id),
													TTi18n::gettext('Message Control is invalid')
													) ) {
			$this->data['message_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function postSave() {
		return TRUE;
	}
}
?>
