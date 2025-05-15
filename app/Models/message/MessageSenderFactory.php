<?php

namespace App\Models\Message;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;
use App\Models\Users\UserListFactory;

class MessageSenderFactory extends Factory {
	protected $table = 'message_sender';
	protected $pk_sequence_name = 'message_sender_id_seq'; //PK Sequence name
	protected $obj_handler = NULL;

	function getUser() {
		return $this->data['user_id'];
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

		$mslf = new MessageSenderListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'parent',
															$mslf->getByID($id),
															('Parent is invalid')
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

		$mclf = new MessageControlListFactory();

		if ( $this->Validator->isResultSetWithRows(	'message_control_id',
													$mclf->getByID($id),
													('Message Control is invalid')
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
