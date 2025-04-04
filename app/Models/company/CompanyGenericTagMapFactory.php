<?php
namespace App\Models\Company;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Policy\PolicyGroupListFactory;
use App\Models\Users\UserListFactory;

class CompanyGenericTagMapFactory extends Factory {
	protected $table = 'company_generic_tag_map';
	protected $pk_sequence_name = 'company_generic_tag_map_id_seq'; //PK Sequence name

	protected $tag_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$cgtf = new CompanyGenericTagFactory();
				$retval = $cgtf->getOptions( $name );
				break;
		}

		return $retval;
	}

	function getTagObject() {
		if ( is_object($this->tag_obj) ) {
			return $this->tag_obj;
		} else {
			$cgtlf = new CompanyGenericTagListFactory();
			$this->tag_obj = $cgtlf->getById( $this->getTagID() )->getCurrent();

			return $this->tag_obj;
		}
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return $this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											('Object Type is invalid'),
											$this->getOptions('object_type')) ) {

			$this->data['object_type_id'] = $type;

			return FALSE;
		}

		return FALSE;
	}

	function getObjectID() {
		if ( isset($this->data['object_id']) ) {
			return $this->data['object_id'];
		}

		return FALSE;
	}
	function setObjectID($id) {
		$id = trim($id);

		$pglf = new PolicyGroupListFactory();

		if ( $this->Validator->isNumeric(	'object_id',
										$id,
										('Object ID is invalid')
										) ) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTagID() {
		if ( isset($this->data['tag_id']) ) {
			return $this->data['tag_id'];
		}

		return FALSE;
	}
	function setTagID($id) {
		$id = trim($id);

		if ( $this->Validator->isNumeric(	'tag_id',
										$id,
										('Tag ID is invalid')
										) ) {
			$this->data['tag_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	static function setTags( $company_id, $object_type_id, $object_id, $tags ) {
		Debug::text('Setting Tags: Company: '. $company_id .' Object Type: '. $object_type_id .' Object: '. $object_type_id .' Tags: '. $tags, __FILE__, __LINE__, __METHOD__, 10);

		if ( $object_id > 0 ) {
			//Parse tags
			$parsed_tags = CompanyGenericTagFactory::parseTags( $tags );
			if ( is_array($parsed_tags) ) {

				$existing_tags = CompanyGenericTagFactory::getOrCreateTags( $company_id, $object_type_id, $parsed_tags );
				$existing_tag_ids = array_values( (array)$existing_tags );
				//Debug::Arr($existing_tags, 'Existing Tags: ', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($existing_tag_ids, 'Existing Tag IDs: ', __FILE__, __LINE__, __METHOD__, 10);

				//Get list of mapped Tag IDs that need to be deleted.
				if ( isset($parsed_tags['delete']) ) {
					foreach( $parsed_tags['delete'] as $del_tag ) {
						$del_tag = strtolower($del_tag);
						if ( isset($existing_tags[$del_tag]) AND $existing_tags[$del_tag] > 0 ) {
							$del_tag_ids[] = $existing_tags[$del_tag];
						}
					}
				}

				//If needed, delete mappings first.
				$cgtmlf = new CompanyGenericTagMapListFactory();
				$cgtmlf->getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id );

				$tmp_ids = array();
				foreach ( $cgtmlf->rs as $obj ) {
					$cgtmlf->data = (array)$obj;
					$id = $cgtmlf->getTagID();
					Debug::text('Object Type ID: '. $object_type_id .' Object ID: '. $cgtmlf->getObjectID() .' Tag ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					if ( isset($del_tag_ids) AND in_array($id, $del_tag_ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$cgtmlf->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $cgtmlf);
				//Debug::Arr($tmp_ids, 'TMP Ids: ', __FILE__, __LINE__, __METHOD__, 10);

				//Add new tags.
				if ( isset($parsed_tags['add']) ) {
					foreach( $parsed_tags['add'] as $add_tag ) {
						$add_tag = strtolower($add_tag);
						if ( isset($existing_tags[$add_tag]) AND $existing_tags[$add_tag] > 0 AND !in_array($existing_tags[$add_tag], $tmp_ids) ) {
							$cgtmf = new CompanyGenericTagMapFactory();
							$cgtmf->setObjectType( $object_type_id );
							$cgtmf->setObjectID( $object_id );
							$cgtmf->setTagID( $existing_tags[strtolower($add_tag)] );
							if ( $cgtmf->isValid() ) {
								$cgtmf->Save();
							}
						}
					}
				}
			}
		} else {
			Debug::Text('Object ID not set, skipping tags!', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
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
		$retval = FALSE;
		if ( $this->getObjectType() > 0 ) {
			//Get Tag name.
			$description = ('Tag');
			if ( is_object( $this->getTagObject() ) ) {
				$description .= ': '. $this->getTagObject()->getName();
			}

			switch( $this->getObjectType() ) {
/*
										100 => 'company',
										110 => 'branch',
										120 => 'department',
										130 => 'stations',
										140 => 'hierarchy',
										150 => 'request',
										160 => 'message',
										170 => 'policy_group',

										200 => 'users',
										210 => 'user_wage',
										220 => 'user_title',

										300 => 'pay_stub_amendment',

										400 => 'schedule',
										410 => 'recurring_schedule_template',

										500 => 'report',
										510 => 'report_schedule',

										600 => 'job',
										610 => 'job_item',

										700 => 'document',

										800 => 'client',
										810 => 'client_contact',
										820 => 'client_payment',

										900 => 'product',
										910 => 'invoice',

*/
				case 100:
					$lf = new CompanyListFactory();
					$lf->getById( $this->getObjectId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = ' - '.('Company').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' TagID: '. $this->getTagID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'company' );
					break;
				case 200:
					$lf = new UserListFactory();
					$lf->getById( $this->getObjectId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description .= ' - '.('Employee').': '. $lf->getCurrent()->getFullName();
					}

					Debug::text('Action: '. $log_action .' TagID: '. $this->getTagID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'users' );
					break;
			}
		}

		return $retval;
	}

}
?>
