<?php

namespace App\Models\Core;

use App\Models\Company\CompanyGenericTagFactory;
use App\Models\Users\UserListFactory;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Throwable;

class Factory {
	public $data = array();
	public $old_data = array(); //Used for detailed audit log.

	protected $enable_system_log_detail = TRUE;

	protected $next_insert_id = NULL;
	protected $progress_bar_obj = NULL;
	protected $AMF_message_id = NULL;

	protected $db;
    protected $cache;
    public $Validator;

	protected $currentUser;
	protected $profiler;
	protected $userPrefs;
	protected $currentCompany;
	protected $permission;
	protected $configVars;

	function __construct() {
		$this->db = DB::connection();
        $this->cache = Cache::store();
		$this->Validator = new Validator();

        $this->currentUser = View::shared('current_user');
        $this->profiler = View::shared('profiler');
		$this->userPrefs = View::shared('current_user_prefs');
        $this->currentCompany = View::shared('current_company');
        $this->permission = View::shared('permission');
        $this->configVars = View::shared('config_vars');

		//Callback to the child constructor method.
		if ( method_exists($this,'childConstruct') ) {
			$this->childConstruct();
		}

		return TRUE;
	}

	/*
	 * Used for updating progress bar for API calls.
	 */
	function getAMFMessageID() {
		if ( $this->AMF_message_id != NULL ) {
			return $this->AMF_message_id;
		}
		return FALSE;
	}
	function setAMFMessageID( $id ) {
		if ( $id != '' ) {
			$this->AMF_message_id = $id;
			return TRUE;
		}

		return FALSE;
	}

	function setProgressBarObject( $obj ) {
		if  ( is_object( $obj ) ) {
			$this->progress_bar_obj = $obj;
			return TRUE;
		}

		return FALSE;
	}
	function getProgressBarObject() {
		if  ( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	/*
	 * Cache functions
	 */
	public function getCache($cache_id) {
		// Ensure Cache ID is formatted correctly
		$cache_id = str_replace(':', '_', $cache_id);
		$cacheKey = $this->getTable(true) . '_' . $cache_id;

		return Cache::get($cacheKey, false); // Return cached data or false if not found
	}

	public function saveCache($data, $cache_id) {
		// Ensure Cache ID is formatted correctly
		$cache_id = str_replace(':', '_', $cache_id);
		$cacheKey = $this->getTable(true) . '_' . $cache_id;

		// Save to Laravel Cache for 2 hours
		$success = Cache::put($cacheKey, $data, now()->addHours(2));

		if (!$success) {
			Log::warning("WARNING: Unable to write cache file. Cache ID: $cache_id | Table: " . $this->getTable(true));
		}

		return $success;
	}

	public function removeCache($cache_id = null) {
		if ($cache_id) {
			$cache_id = str_replace(':', '_', $cache_id);
			$cacheKey = $this->getTable(true) . '_' . $cache_id;

			Cache::forget($cacheKey); // Remove specific cache entry
			return true;
		}

		return false;
	}

	public function setCacheLifeTime($seconds) {
		// Laravel doesn't support dynamic cache lifetime setting per request.
		// You need to modify the expiration time inside `saveCache()` instead.
		return false;
	}


	function getTable($strip_quotes = FALSE) {

		if ( isset($this->table) ) {
			if ( $strip_quotes == TRUE ) {
				return str_replace('"','', $this->table );
			} else {
				return $this->table;
			}
		}

		return FALSE;
	}

	//Generic function get any data from the data array.
	//Used mainly for the reports that return grouped queries and such.
	function getColumn( $column ) {

		if ( isset($this->data[$column]) ) {
			return $this->data[$column];
		}

		return FALSE;
	}

	//Print primary columns from object.
	function __toString() {
		if ( method_exists( $this, 'getObjectAsArray' ) ) {
			$columns = Misc::trimSortPrefix( $this->getOptions('columns') );
			$data = $this->getObjectAsArray( $columns );

			if ( is_array($columns) AND is_array($data) ) {
				$retarr = array();
				foreach( $columns as $column => $name ) {
					if ( isset($data[$column]) ) {
						$retarr[] = $name .': '. $data[$column];
					}
				}

				if ( count($retarr) > 0 ) {
					return implode( "\n", $retarr );
				}
			}
		}

		return FALSE;
	}

	function toBool($value) {
		$value = strtolower(trim($value));

		if ($value === TRUE OR $value == 1 OR $value == 't') {
			//return 't';
			return 1;
		} else {
			//return 'f';
			return 0;
		}
	}

	function fromBool($value) {
		$value = strtolower(trim($value));

		//if ($value == 't') {
		if ($value == 1 OR $value == 't' ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	//Determines if the data is new data, or updated data.
	public function isNew($force_lookup = false) {
		// Check if the model has an ID (i.e., it's an existing record)
		if (empty($this->getId())) {
			// New Data (no ID set)
			return true;
		} elseif ($force_lookup === true) {
			// Check if the record exists in the database
			$exists = DB::table($this->getTable())->where('id', $this->getId())->exists();

			if (!$exists) {
				// ID does not exist in the database, treat as new
				return true;
			}
		}
		// Not new data (the record exists in the DB)
		return false;
	}


	//Determines if we were called by a save function or not.
	//This is useful for determining if we are just validating or actually saving data. Problem is its too late to throw any new validation errors.
	function isSave() {
		$stack = debug_backtrace();

		if ( is_array($stack) ) {
			//Loop through and if we find a Save function call return TRUE.
			//Not sure if this will work in some more complex cases though.
			foreach( $stack as $data ) {
				if ( $data['function'] == 'Save' ) {
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	//Returns the calling function name
	function getCallerFunction() {
		$stack = debug_backtrace();
		if ( isset($stack[1]) ) {
			return $statc[1]['function'];
		}

		return FALSE;
	}

	function getLabelId() {
		//Gets the ID used in validator labels. If no ID, uses "-1";
		if ( $this->getId() == FALSE ) {
			return '-1';
		}

		return $this->getId();
	}

	function getId() {
		if ( isset($this->data['id']) AND $this->data['id'] != NULL) {
			return $this->data['id'];
		}

		return FALSE;
	}
	function setId($id) {
		/*
		if ($id != NULL) {
			//$this->data['id'] = (int)$id;
			$this->data['id'] = $id; //Allow ID to be set as FALSE. Essentially making a new entry.
		}
		*/
		if ( is_numeric( $id ) OR is_bool( $id ) ) {
			$this->data['id'] = $id; //Allow ID to be set as FALSE. Essentially making a new entry.
			return TRUE;
		}

		return FALSE;
	}

	function getEnableSystemLogDetail() {
		if ( isset($this->enable_system_log_detail) ) {
			return $this->enable_system_log_detail;
		}

		return FALSE;
	}
	function setEnableSystemLogDetail($bool) {
		$this->enable_system_log_detail = (bool)$bool;

		return true;
	}

	function getDeleted() {
		if ( isset($this->data['deleted']) ) {
			return $this->fromBool( $this->data['deleted'] );
		}

		return FALSE;
	}
	function setDeleted($bool) {
		$value = (bool)$bool;

		//Handle Postgres's boolean values.
		if ($value === TRUE) {
			//Only set this one we're deleting
			$this->setDeletedDate();
			$this->setDeletedBy();
		}

		$this->data['deleted'] = $this->toBool($value);

		return true;
	}

	function getCreatedDate() {
		if ( isset($this->data['created_date']) ) {
			return (int)$this->data['created_date'];
		}

		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ( $epoch == NULL OR $epoch == '' OR $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'created_date',
												$epoch,
												('Incorrect Date')) ) {

			$this->data['created_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}
	function getCreatedBy() {
		if ( isset($this->data['created_by']) ) {
			return (int)$this->data['created_by'];
		}

		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		$id = (int)trim($id);

		if ( empty($id) ) {
			$current_user = $this->currentUser;

			if ( is_object($current_user) ) {
				$id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = new UserListFactory();
		if ( $this->Validator->isResultSetWithRows(	'created_by',
													$ulf->getByID($id),
													('Incorrect User')
													) ) {

			$this->data['created_by'] = $id;

			return TRUE;
		}

		return FALSE;
		*/

		$this->data['created_by'] = (int)$id;

		return TRUE;
	}

	function getUpdatedDate() {
		if ( isset($this->data['updated_date']) ) {
			return (int)$this->data['updated_date'];
		}

		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ( $epoch == NULL OR $epoch == '' OR $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'updated_date',
												$epoch,
												('Incorrect Date')) ) {

			$this->data['updated_date'] = $epoch;

			//return TRUE;
			//Return the value so we can use it in getUpdateSQL
			return $epoch;
		}

		return FALSE;

	}
	function getUpdatedBy() {
		if ( isset($this->data['updated_by']) ) {
			return (int)$this->data['updated_by'];
		}

		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		$id = (int)trim($id);

		if ( empty($id) ) {
			$current_user = $this->currentUser;

			if ( is_object($current_user) ) {
				$id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = new UserListFactory();
		if ( $this->Validator->isResultSetWithRows(	'updated_by',
													$ulf->getByID($id),
													('Incorrect User')
													) ) {
			$this->data['updated_by'] = $id;

			//return TRUE;
			return $id;
		}

		return FALSE;
		*/

		$this->data['updated_by'] = $id;

		return $id;
	}


	function getDeletedDate() {
		if ( isset($this->data['deleted_date']) ) {
			return $this->data['deleted_date'];
		}

		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ( $epoch == NULL OR $epoch == '' OR $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'deleted_date',
												$epoch,
												('Incorrect Date')) ) {

			$this->data['deleted_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}
	function getDeletedBy() {
		if ( isset($this->data['deleted_by']) ) {
			return $this->data['deleted_by'];
		}

		return FALSE;
	}
	function setDeletedBy($id = NULL) {
		$id = trim($id);

		if ( empty($id) ) {
			$current_user = $this->currentUser;

			if ( is_object($current_user) ) {
				$id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		$ulf = new UserListFactory();

		if ( $this->Validator->isResultSetWithRows(	'updated_by',
													$ulf->getByID($id),
													('Incorrect User')
													) ) {

			$this->data['deleted_by'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function setCreatedAndUpdatedColumns( $data ) {
		Debug::text(' Set created/updated columns...', __FILE__, __LINE__, __METHOD__,10);
		//Update array in-place.
		if ( isset($data['created_by']) AND is_numeric($data['created_by']) AND $data['created_by'] > 0 ) {
			$this->setCreatedBy( $data['created_by'] );
		}
		if ( isset($data['created_by_id']) AND is_numeric($data['created_by_id']) AND $data['created_by_id'] > 0 ) {
			$this->setCreatedBy( $data['created_by_id'] );
		}
		if ( isset($data['created_date']) AND $data['created_date'] != FALSE AND $data['created_date'] != '' ) {
			$this->setCreatedDate( TTDate::parseDateTime( $data['created_date'] ) );
		}

		if ( isset($data['updated_by']) AND is_numeric($data['updated_by']) AND $data['updated_by'] > 0 ) {
			$this->setUpdatedBy( $data['updated_by'] );
		}
		if ( isset($data['updated_by_id']) AND is_numeric($data['updated_by_id']) AND $data['updated_by_id'] > 0 ) {
			$this->setUpdatedBy( $data['updated_by_id'] );
		}
		if ( isset($data['updated_date']) AND $data['updated_date'] != FALSE AND $data['updated_date'] != '' ) {
			$this->setUpdatedDate( TTDate::parseDateTime( $data['updated_date'] ) );
		}

		return TRUE;
	}
	function getCreatedAndUpdatedColumns( $data, $include_columns = NULL ) {
		//Update array in-place.
		if ( $include_columns == NULL OR ( isset($include_columns['created_by_id']) AND $include_columns['created_by_id'] == TRUE) ) {
			$data['created_by_id'] = $this->getCreatedBy();
		}
		if ( $include_columns == NULL OR ( isset($include_columns['created_by']) AND $include_columns['created_by'] == TRUE) ) {
			$data['created_by'] = Misc::getFullName( $this->getColumn('created_by_first_name'), $this->getColumn('created_by_middle_name'), $this->getColumn('created_by_last_name') );
		}
		if ( $include_columns == NULL OR ( isset($include_columns['created_date']) AND $include_columns['created_date'] == TRUE) ) {
			$data['created_date'] = TTDate::getAPIDate( 'DATE+TIME', $this->getCreatedDate() );
		}
		if ( $include_columns == NULL OR ( isset($include_columns['updated_by_id']) AND $include_columns['updated_by_id'] == TRUE) ) {
			$data['updated_by_id'] = $this->getUpdatedBy();
		}
		if ( $include_columns == NULL OR ( isset($include_columns['updated_by']) AND $include_columns['updated_by'] == TRUE) ) {
			$data['updated_by'] = Misc::getFullName( $this->getColumn('updated_by_first_name'), $this->getColumn('updated_by_middle_name'), $this->getColumn('updated_by_last_name') );
		}
		if ( $include_columns == NULL OR ( isset($include_columns['updated_date']) AND $include_columns['updated_date'] == TRUE) ) {
			$data['updated_date'] = TTDate::getAPIDate( 'DATE+TIME', $this->getUpdatedDate() );
		}

		return TRUE;
	}

	function getPermissionColumns( $data, $object_user_id, $created_by_id, $permission_children_ids = NULL, $include_columns = NULL ) {
		$permission = new Permission();

		if( $include_columns == NULL OR ( isset($include_columns['is_owner']) AND $include_columns['is_owner'] == TRUE) ) {
			$data['is_owner'] = $permission->isOwner( $created_by_id, $object_user_id );
		}

		if ( $include_columns == NULL OR ( isset($include_columns['is_child']) AND $include_columns['is_child'] == TRUE) ) {
			if ( is_array($permission_children_ids) ) {
				//ObjectID should always be a user_id.
				$data['is_child'] = $permission->isChild( $object_user_id, $permission_children_ids );
			} else {
				$data['is_child'] = FALSE;
			}
		}

		return TRUE;
	}

	function getOptions($name, $parent = NULL) {
		if ( $parent == NULL OR $parent == '') {
			return $this->_getFactoryOptions( $name );
		} else {
			$retval = $this->_getFactoryOptions( $name );
			if ( isset($retval[$parent]) ){
				return $retval[$parent];
			}
		}

		return FALSE;
	}
	protected function _getFactoryOptions( $name ) {
		return FALSE;
	}

	function getVariableToFunctionMap( $data = NULL ) {
		return $this->_getVariableToFunctionMap( $data );
	}
	protected function _getVariableToFunctionMap( $data ) {
		return FALSE;
	}

	function getRecordCount() {

		if (isset($this->rs) && is_array($this->rs)) {
			return count($this->rs);
		}

		return FALSE;
	}

	function getCurrentRow( $offset = 1 ) {
		if ( isset($this->rs) AND isset($this->rs->_currentRow) ) {
			return $this->rs->_currentRow+(int)$offset;
		}

		return FALSE;
	}

	private function getRecordSetColumnList($rs) {
		if (is_object($rs)) {
			for ($i=0, $max=$rs->FieldCount(); $i < $max; $i++) {
				$field = $rs->FetchField($i);
				$fields[] = $field->name;
			}

			return $fields;
		}

		return FALSE;
	}

	protected function getListSQL($array, $ph = null)
	{
		// Ensure it's an array
		if (!is_array($array)) {
			$array = explode(',', (string) $array); // Convert comma-separated string to array
		}

		// Trim values and filter out empty ones
		$array = array_filter(array_map('trim', $array));

		return implode(',', $array);
	}

	function getDateRangeSQL( $str, $column, $use_epoch = TRUE ) {

		if ( $str == '' ) {
			return FALSE;
		}

		if ( $column == '' ) {
			return FALSE;
		}

		$operators = array(
						   '>',
						   '<',
						   '>=',
						   '<=',
						   '=',
						   );

		$operations = FALSE;

		//Parse input, separate any subqueries first.
		$split_str = explode( '&', $str, 2 ); //Limit sub-queries
		if ( is_array($split_str) ) {
			foreach( $split_str as $tmp_str ) {
				$tmp_str = trim($tmp_str);

				$date = (int)TTDate::parseDateTime( str_replace( $operators, '', $tmp_str ) );
				//Debug::text(' Parsed Date: '. $tmp_str .' To: '. TTDate::getDate('DATE+TIME', $date) .' ('. $date .')', __FILE__, __LINE__, __METHOD__,10);

				if ( $date != 0 ) {
					preg_match('/^>=|>|<=|</i', $tmp_str, $operator );

					//Debug::Arr($operator, ' Operator: ', __FILE__, __LINE__, __METHOD__,10);
					if ( isset($operator[0]) AND in_array( $operator[0], $operators) ) {
						if ( $operator[0] == '<=' ) {
							$date = TTDate::getEndDayEpoch( $date );
						} elseif ( $operator[0] == '>' ) {
							$date = TTDate::getEndDayEpoch( $date );
						}

						$operations[] = $column .' '. $operator[0] .' '. $date;
					} else {
						//Debug::text(' No operator specified... Using a 24hr period', __FILE__, __LINE__, __METHOD__,10);
						$operations[] = $column .' >= '. TTDate::getBeginDayEpoch( $date );
						$operations[] = $column .' <= '. TTDate::getEndDayEpoch( $date );
					}
				}
			}
		}

		//Debug::Arr($operations, ' Operations: ', __FILE__, __LINE__, __METHOD__,10);
		if ( is_array($operations) ) {
			$retval = ' ( '. implode(' AND ', $operations ) .' )';
			Debug::text(' Query parts: '. $retval, __FILE__, __LINE__, __METHOD__,10);

			return $retval;
		}

		return FALSE;
	}

	///SQL where clause Syntax:
	//  * or % as wildcard.
	//  "<query>" as exact match, no default wildcard and no metaphone

	//Handles '*' and '%' as wildcards, defaults to wildcard on the end always.
	//If no wildcard is to be added, the last character should be |
	protected function handleSQLSyntax( $arg ) {
		$arg = str_replace('*', '%', trim($arg) );

		if ( strpos($arg, '%') === FALSE AND ( strpos( $arg, '|') === FALSE AND strpos( $arg, '"') === FALSE ) ) {
			$arg .= '%';
		}

		return $this->stripSQLSyntax( $arg );
	}

	protected function stripSQLSyntax( $arg ) {
		return str_replace( array('"'), '', $arg); //Strip syntax characters out.
	}

	protected function getWhereClauseSQL( $columns, $args, $type, &$ph, $query_stub = NULL, $and = TRUE ) {
		Debug::Text('Type: '. $type .' Query Stub: '. $query_stub .' AND: '. (int)$and, __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($columns, 'Columns: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($args, 'Args: ', __FILE__, __LINE__, __METHOD__,10);
		switch( strtolower($type) ) {
			case 'text':
				if ( isset($args) AND !is_array($args) AND trim($args) != '' ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = 'lower('. $columns .') LIKE ?';
					}

					$ph[] = $this->handleSQLSyntax( strtolower($args) );
					$retval = $query_stub;
				}
				break;
			case 'text_metaphone':
				if ( isset($args) AND !is_array($args) AND trim($args) != '' ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = '( lower('. $columns .') LIKE ? OR '. $columns .'_metaphone LIKE ? )';
					}

					$ph[] = $this->handleSQLSyntax( strtolower($args) );
					if ( strpos($args, '"') !== FALSE ) { //ignores metaphone search.
						$ph[] = '';
					} else {
						$ph[] = $this->handleSQLSyntax( metaphone( $args ) );
					}
					$retval = $query_stub;
				}
				break;
			case 'text_list':
			case 'lower_text_list':
			case 'upper_text_list':
				if ( !is_array($args) ) {
					$args = (array)$args;
				}

				if ( $type == 'upper_text_list' OR $type == 'lower_text_list' ) {
					if ( $type == 'upper_text_list' ) {
						$text_case = CASE_UPPER;
					} else {
						$text_case = CASE_LOWER;
					}
					$args = array_flip( array_change_key_case( array_flip( $args ), $text_case ) );
				}

				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) AND !in_array( '00', $args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' in (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph), $query_stub );
				}

				break;
			case 'province':
				if ( !is_array($args) ) {
					$args = (array)$args;
				}

				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) AND !in_array( '00', $args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' in (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph), $query_stub );
				}
				break;
			case 'phone':
				if ( isset($args) AND !is_array($args) AND trim($args) != '' ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = "( replace( replace( replace( replace( replace( replace( ". $columns .", ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '') LIKE ? OR ". $columns ." LIKE ? )";
					}

					$ph[] = $ph[] = $this->handleSQLSyntax( preg_replace('/[^0-9\%\*\"]/','', strtolower($args) ) ); //Need the same value twice for the query stub.
					$retval = $query_stub;
				}
				break;
			case 'numeric':
				if ( !is_array($args) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' AND !is_array($columns) ) {
						if ( $args === NULL ) {
							$query_stub = $columns .' is NULL';
						} else {
							$args = $this->Validator->stripNonNumeric( $args );
							if ( is_numeric( $args ) ) {
								$ph[] = $args;
								$query_stub = $columns .' = ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'numeric_list':
				if ( !is_array($args) ) {
					$args = (array)$args;
				}
				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' in (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph), $query_stub );
				}
				break;
			case 'not_numeric_list':
				if ( !is_array($args) ) {
					$args = (array)$args;
				}
				if ( isset($args) AND isset($args[0]) AND !in_array( -1, $args) ) {
					if ( $query_stub == '' AND !is_array($columns) ) {
						$query_stub = $columns .' not in (?)';
					}
					$retval = str_replace('?', $this->getListSQL($args, $ph), $query_stub );
				}
				break;
			case 'tag':
				//We need company_id and object_type_id passed in.
				if ( isset($args['company_id']) AND isset($args['object_type_id']) AND isset($args['tag']) ) {
					//Parse the tags search syntax to determine ANY, AND, OR searches.
					$parsed_tags = CompanyGenericTagFactory::parseTags($args['tag']);
					Debug::Arr($parsed_tags, 'Parsed Tags: ', __FILE__, __LINE__, __METHOD__,10);
					if ( is_array($parsed_tags) ) {
						$retval = '';
						if ( isset($parsed_tags['add']) AND count($parsed_tags['add']) > 0 ) {
							$retval .= ' EXISTS 	(
														select 1
														from company_generic_tag_map as cgtm
														INNER JOIN company_generic_tag as cgt ON (cgtm.tag_id = cgt.id)
														WHERE cgt.company_id = '. (int)$args['company_id'] .'
															AND cgtm.object_type_id = '. (int)$args['object_type_id'] .'
															AND a.id = cgtm.object_id
															AND ( lower(cgt.name) in (\''. implode('\',\'', $parsed_tags['add'] ) .'\') )
														group by cgtm.object_id
														HAVING COUNT(*) = '. count( $parsed_tags['add'] ) .'
													)';

							if ( isset($parsed_tags['delete']) AND count($parsed_tags['delete']) > 0 ) {
								$retval .= ' AND ';
							}
						}

						if ( isset($parsed_tags['delete']) AND count($parsed_tags['delete']) > 0 ) {
							$retval .= ' NOT EXISTS 	(
														select 1
														from company_generic_tag_map as cgtm
														INNER JOIN company_generic_tag as cgt ON (cgtm.tag_id = cgt.id)
														WHERE cgt.company_id = '. (int)$args['company_id'] .'
															AND cgtm.object_type_id = '. (int)$args['object_type_id'] .'
															AND a.id = cgtm.object_id
															AND ( lower(cgt.name) in (\''. implode('\',\'', $parsed_tags['delete'] ) .'\') )
														group by cgtm.object_id
														HAVING COUNT(*) = '. count( $parsed_tags['delete'] ) .'
													)';
						}
					}
				}

				if ( !isset($retval) ) {
					$retval = '';
				}
				break;
			default:
				Debug::Text('Invalid type: '. $type, __FILE__, __LINE__, __METHOD__,10);
				break;
		}

		if ( isset($retval) ) {
			$and_sql = NULL;
			if ( $and == TRUE ) {
				$and_sql = 'AND ';
			}

			Debug::Arr($ph, 'Query Stub: '. $retval, __FILE__, __LINE__, __METHOD__,10);
			return ' '.$and_sql.$retval.' '; //Wrap each query stub in spaces.
		}

		return NULL;
	}

	//Parses out the exact column name, without any aliases, or = signs in it.
	private function parseColumnName($column) {
		$column = trim($column);

		if ( strstr($column, '=') ) {
			$tmp_column = explode('=', $column);
			$retval = trim($tmp_column[0]);
			unset($tmp_column);
		} else {
			$retval = $column;
		}

		if ( strstr($retval, '.') ) {
			$tmp_column = explode('.', $retval);
			$retval = $tmp_column[1];
			unset($tmp_column);
		}
		//Debug::Text('Column: '. $column .' RetVal: '. $retval, __FILE__, __LINE__, __METHOD__,10);

		return $retval;
	}

	/* //original code - commented by desh(2025-03-26)
	protected function getWhereSQL($array, $append_where = FALSE) {
		//Make this a multi-dimensional array, the first entry
		//is the WHERE clauses with '?' for placeholders, the second is
		//the array to replace the placeholders with.
		if (is_array($array) ) {
			$rs = $this->getEmptyRecordSet();
			$fields = $this->getRecordSetColumnList($rs);
			foreach ($array as $orig_column => $expression) {
				$orig_column = trim($orig_column);
				$column = $this->parseColumnName( $orig_column );

				$expression = trim($expression);

				if ( in_array($column, $fields) ) {
					$sql_chunks[] = $orig_column.' '.$expression;
				}
			}

			if ( isset($sql_chunks) ) {
				$sql = implode(',', $sql_chunks);

				if ($append_where == TRUE) {
					return ' where '.$sql;
				} else {
					return ' AND '.$sql;
				}
			}
		}

		return FALSE;
	}
	*/

	protected function getWhereSQL($array, $append_where = false) {
		if (!is_array($array) || empty($array)) {
			return ''; // Return empty string if input is not a valid array
		}

		$sql_chunks = [];

		foreach ($array as $column => $value) {
			$column = trim($column);
			$value = trim($value);

			// Ensure proper SQL escaping (use prepared statements in real-world applications)
			if (is_numeric($value)) {
				$sql_chunks[] = "`$column` $value"; // No quotes for numeric values
			} else {
				$sql_chunks[] = "`$column` " . addslashes($value); // No quotes around values
			}
		}

		$sql = implode(' AND ', $sql_chunks);

		if ($append_where) {
			return ' WHERE ' . $sql;
		} else {
			return ' AND ' . $sql;
		}
	}


	protected function getColumnsFromAliases( $columns, $aliases ) {
		// Columns is the original column array.
		//
		// Aliases is an array of search => replace key/value pairs.
		//
		// This is used so the frontend can sort by the column name (ie: type) and it can be converted to type_id for the SQL query.
		if ( is_array($columns) AND is_array( $aliases ) ) {
			$columns = $this->convertFlexArray( $columns );

			//Debug::Arr($columns, 'Columns before: ', __FILE__, __LINE__, __METHOD__,10);

			foreach( $columns as $column => $sort_order ) {
				if ( isset($aliases[$column]) AND !isset($columns[$aliases[$column]]) ) {
					$retarr[$aliases[$column]] = $sort_order;
				} else {
					$retarr[$column] = $sort_order;
				}

			}
			//Debug::Arr($retarr, 'Columns after: ', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return $columns;
	}

	protected function convertFlexArray( $array ) {
		//Flex doesn't appear to be consistent on the order the fields are placed into an assoc array, so
		//handle this type of array too:
		// array(
		//		0 => array('first_name' => 'asc')
		//		1 => array('last_name' => 'desc')
		//		)

		if ( isset($array[0]) AND is_array($array[0]) ) {
			Debug::text('Found Flex Sort Array, converting to proper format...', __FILE__, __LINE__, __METHOD__,10);

			//Debug::Arr($array, 'Before conversion...', __FILE__, __LINE__, __METHOD__,10);

			$new_arr = array();
			foreach( $array as $tmp_order => $tmp_arr ) {
				if ( is_array($tmp_arr) ) {
					foreach( $tmp_arr as $tmp_column => $tmp_order ) {
						$new_arr[$tmp_column] = $tmp_order;
					}
				}
			}
			$array = $new_arr;
			unset($tmp_key, $tmp_arr, $tmp_order, $tmp_column, $new_arr);
			//Debug::Arr($array, 'Converted format...', __FILE__, __LINE__, __METHOD__,10);
		}

		return $array;
	}
/*
	protected function getSortSQL($array, $strict = TRUE, $additional_fields = NULL)
	{
		if (is_array($array)) {
			$array = $this->convertFlexArray($array);

			$alt_order_options = [1 => 'asc', -1 => 'desc'];
			$order_options = ['asc', 'desc'];

			$rs = $this->getEmptyRecordSet();
			$fields = $this->getRecordSetColumnList($rs);
			$sql_chunks = [];
			if (is_array($additional_fields)) {
				foreach($additional_fields as $orig_column => $order){
					$column = $this->parseColumnName($orig_column);
					$order = trim($order);

					$sql_chunks[] = $orig_column . ' ' . $order;
				}
			}

			foreach ($array as $orig_column => $order) {
				$orig_column = trim($orig_column);
				$column = $this->parseColumnName($orig_column);
				$order = trim($order);

				if (is_numeric($order) && isset($alt_order_options[$order])) {
					$order = $alt_order_options[$order];
				}

				if ($strict == false || (
					(is_array($fields) && (in_array($column, $fields) || in_array($orig_column, $fields))) &&
					in_array(strtolower($order), $order_options)
				)) {
					// Check for any illegal semicolons in the column or order
					if ($strict == true || (strpos($orig_column, ';') === false && strpos($order, ';') === false)) {
						// Add to the SQL chunks for ORDER BY clause
						$sql_chunks[] = $orig_column . ' ' . $order;
					} else {
						Debug::text('ERROR: Found ";" in SQL order string: ' . $orig_column . ' Order: ' . $order, __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::text('Invalid Sort Column/Order: ' . $column . ' Order: ' . $order, __FILE__, __LINE__, __METHOD__, 10);
				}

			}

			if (isset($sql_chunks)) {
				$sql = implode(',', $sql_chunks);
				dd($sql);
				return ' order by ' . $sql;
			}
		}

		return FALSE;
	}
*/

	protected function getSortSQL($array, $strict = true, $additional_fields = null)
	{
		if (!is_array($array)) {
			return false;
		}

		$array = $this->convertFlexArray($array);
		$alt_order_options = [1 => 'asc', -1 => 'desc'];
		$order_options = ['asc', 'desc'];
		$rs = $this->getEmptyRecordSet();
		$fields = $this->getRecordSetColumnList($rs);
		$sql_chunks = [];

		if (is_array($additional_fields)) {
			foreach ($additional_fields as $orig_column => $order) {
				$sql_chunks[] = $orig_column . ' ' . trim($order);
			}
		}

		foreach ($array as $orig_column => $order) {
			$orig_column = trim($orig_column);
			$column = $this->parseColumnName($orig_column);
			$order = trim($order);

			if (is_numeric($order) && isset($alt_order_options[$order])) {
				$order = $alt_order_options[$order];
			}

			if (
				!$strict || (
					is_array($fields) && (in_array($column, $fields) || in_array($orig_column, $fields)) &&
					in_array(strtolower($order), $order_options)
				)
			) {
				if (!$strict || (strpos($orig_column, ';') === false && strpos($order, ';') === false)) {
					$sql_chunks[] = "$orig_column $order";
				} else {
					Debug::text("ERROR: Found ';' in SQL order string: $orig_column Order: $order", __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::text("Invalid Sort Column/Order: $column Order: $order", __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		if (!empty($sql_chunks)) {
			return ' ORDER BY ' . implode(',', $sql_chunks);
		}

		return false;
	}




	public function getColumnList() {
		if ( is_array($this->data) AND count($this->data) > 0) {
			$column_list = array_keys($this->data);

			//Don't set updated_date when deleting records, we use deleted_date/deleted_by for that.
			if ( $this->getDeleted() == FALSE AND $this->setUpdatedDate() !== FALSE ) {
				$column_list[] = 'updated_date';
			}
			if ( $this->getDeleted() == FALSE AND $this->setUpdatedBy() !== FALSE ) {
				$column_list[] = 'updated_by';
			}

			$column_list = array_unique($column_list);

			//Debug::Arr($this->data,'aColumn List', __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr($column_list,'bColumn List', __FILE__, __LINE__, __METHOD__,10);

			return $column_list;
		}

		return FALSE;
	}

	public function getEmptyRecordSet($id = NULL) {
		global $profiler, $config_vars;

		$profiler  = new Profiler();
		$profiler->startTimer( 'getEmptyRecordSet()' );

		if ($id == NULL) {
			$id = -1;
		}

		$id = (int)$id;

		//Possible errors can happen if $this->data[<invalid_column>] is passed, like what happens with APIPunch when attempting to delete a punch.
		//Why are we not using '*' for all empty record set queries? Will using * cause more fields to be updated then necessary?
		//Yes, it will, as well the updated_by/updated_date fields aren't controllable by getColumnList() then either.
		//Therefore any ListFactory queries used to potentially delete data should only include columns from its own table,
		//Or collect the IDs and use bulkDelete instead.
		$column_list = $this->getColumnList();
		if ( is_array($column_list) ) {
			//Implode columns.
			$column_str = implode(',', $column_list);
		} else {
			$column_str = '*'; //Get empty RS with all columns.
		}

		try {
			$query = 'select '. $column_str .' from '. $this->table .' where id = '. $id;

			if ( $id == -1 AND isset($config_vars['cache']['enable']) AND $config_vars['cache']['enable'] == TRUE ) {

				/*
				//Try to use Cache Lite instead of ADODB, to avoid cache write errors from causing a transaction rollback. It should be faster too.
				//However I think there is some issues with storing the record set, as ADODB goes to great lengths to avoid straight serialize/unserialize.
				$cache_id = 'empty_rs_'. $this->table .'_'. $id;
				$rs = $this->getCache($cache_id);
				if ( $rs === FALSE ) {
					$rs = DB::select($query);
					$this->saveCache($rs,$cache_id);
				}
				*/
				$save_error_handlers = $this->db->IgnoreErrors(); //Prevent a cache write error from causing a transaction rollback.
				try {
					$rs = $this->db->CacheExecute(604800, $query);
				} catch (Throwable $e) {
					if ( $e->getCode() == -32000 OR $e->getCode() == -32001 ) { //Cache write error/cache file lock error.
						//Likely a cache write error occurred, fall back to non-cached query and log this error.
						Debug::Text('ERROR: Unable to write cache file, likely due to permissions or locking! Code: '. $e->getCode() .' Msg: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__,10);
					}

					//Execute non-cached query
					try {
    					$rs = DB::select($query);
					} catch (Throwable $e) {
						throw new DBError($e);
					}
				}
				$this->db->IgnoreErrors( $save_error_handlers ); //Prevent a cache write error from causing a transaction rollback.
			} else {
				//$rs = DB::select($query);
				$rs = DB::select($query);
			}
		} catch (Throwable $e) {
			throw new DBError($e);
		}

		$profiler->stopTimer( 'getEmptyRecordSet()' );
		return $rs;
	}

	private function getUpdateQuery($data = NULL) {
		//Debug::text('Update' , __FILE__, __LINE__, __METHOD__,9);

		//
		// If the table has timestamp columns without timezone set
		// this function will think the data has changed, and update it.
		// PayStubFactory() had this issue.
		//

		//Debug::arr($this->data,'Data Arr', __FILE__, __LINE__, __METHOD__,10);

		//Add new columns to record set.
		//Check to make sure the columns exist in the table first though
		//Classes like station don't have updated_date, so we need to take that in to account.
		try {
			$rs = $this->getEmptyRecordSet( $this->getId() );
			$this->old_data = $rs->fields; //Store old data in memory for detailed audit log.
		} catch (Throwable $e) {
			throw new DBError($e);
		}
		if (!$rs) {
			Debug::text('No Record Found! (ID: '. $this->getID() .' Insert instead?' , __FILE__, __LINE__, __METHOD__,9);
			//Throw exception?
		}

		//Debug::Arr($rs->fields, 'RecordSet: ', __FILE__, __LINE__, __METHOD__, 9);
		//Debug::Arr($this->data, 'Data Array: ', __FILE__, __LINE__, __METHOD__, 9);
		//Debug::Arr( array_diff_assoc($rs->fields, $this->data), 'Data Array: ', __FILE__, __LINE__, __METHOD__, 9);

		//If no columns changed, this will be FALSE.
		$query = $this->db->GetUpdateSQL($rs, $this->data);

		//No updates are fine. We still want to run postsave() etc...
		if (empty($query) || $query === FALSE) {
			$query = TRUE;
		} else {
			Debug::text('Data changed, set updated date: ', __FILE__, __LINE__, __METHOD__, 9);
		}

		//Debug::text('Update Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		return $query;
	}

	private function getInsertQuery($data = NULL) {
		Debug::text('Insert' , __FILE__, __LINE__, __METHOD__,9);

		//Debug::arr($this->data,'Data Arr', __FILE__, __LINE__, __METHOD__, 10);

		try {
			$rs = $this->getEmptyRecordSet();
		} catch (Throwable $e) {
			throw new DBError($e);
		}

		//Use table name instead of recordset, especially when using CacheLite for caching empty recordsets.
		//$query = $this->db->GetInsertSQL($rs, $this->data);
		$query = $this->db->GetInsertSQL($this->getTable(), $this->data);

               // echo $query;
               // exit();

		//Debug::text('Insert Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		return $query;
	}

	public function startTransaction()
    {
        Log::debug('StartTransaction: Starting database transaction.');
        DB::beginTransaction();
    }

    public function failTransaction()
    {
        Log::debug('FailTransaction: Rolling back database transaction.');
        DB::rollBack();
    }

    public function commitTransaction()
    {
        Log::debug('CommitTransaction: Committing database transaction.');
        DB::commit();
    }

	//Call class specific validation function just before saving.
	function isValid() {
		if ( method_exists($this,'Validate') ) {
			Debug::text('Calling Validate()' , __FILE__, __LINE__, __METHOD__,10);
			$this->Validate();
		}
        return $this->Validator->isValid();
	}

	function getNextInsertId() {
		// Implement your logic to get next insert ID
        return DB::table($this->getTable())->max('id') + 1;
	}

	public function Save($reset_data = TRUE, $force_lookup = FALSE) {
		DB::beginTransaction();
		try {
			// Validate the model before saving (if not deleted)
			if (!$this->getDeleted() && !$this->isValid()) {
				throw new \Exception('Invalid Data, not saving.');
			}
			// Get the table name dynamically
			$table = $this->getTable();

			// Determine if we're inserting a new record or updating an existing one
			if ($this->isNew($force_lookup)) {
				//Insert
				$time = TTDate::getTime();

				if ( empty($this->getCreatedDate()) ) {
					$this->setCreatedDate($time);
				}

				if ( empty($this->getCreatedBy()) ) {
					$this->setCreatedBy();
				}

				//Set updated date at the same time, so we can easily select last
				//updated, or last created records.
				$this->setUpdatedDate($time);
				$this->setUpdatedBy();

				unset($time);

				// Perform the insert and get the insert ID
				$insert_id = DB::table($table)->insertGetId($this->data);
				Debug::text('Insert ID: '. $insert_id , __FILE__, __LINE__, __METHOD__, 9);

				// Set the insert ID in the model
				$this->setId($insert_id);

				// Return the ID of the newly created record
				$retval = (int)$insert_id;
				$log_action = 10; // 'Add'
				// echo 'check error: ';
			} else {
				Debug::text(' Updating...' , __FILE__, __LINE__, __METHOD__,10);

				// Perform the update
				DB::table($table)
					->where('id', $this->getId())
					->update($this->data);

				// Return true to indicate success
				$retval = true;
				$log_action = $this->getDeleted() ? 30 : 20; // 'Delete' or 'Edit'
			}

			/*
			if ( method_exists($this,'addLog') ) {
				//In some cases, like deleting users, this function will fail because the user is deleted before they are removed from other
				//tables like PayPeriodSchedule, so addLog() can't get the user information.
				$this->addLog( $log_action );
			}
			*/

			// Clear the data if requested
			if ($reset_data) {
				$this->clearData();
			}

			// Commit the transaction
			DB::commit();

			return $retval;
		} catch (\Exception $e) {
			// Roll back the transaction on error
			DB::rollBack();
			Log::error('Save failed: ' . $e->getMessage());
			print_r($e->getMessage());exit;
			throw new \Exception('Save failed.');
		}

	}

	function Delete() {
		Debug::text('Delete: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 9);

		if ( $this->getId() !== FALSE ) {
			$ph = array(
						':id' => $this->getId(),
						);

			$query = 'DELETE FROM '. $this->getTable() .' WHERE id = :id';

			try {
				DB::delete($query, $ph);

				if ( method_exists($this,'addLog') ) {
					//In some cases, like deleting users, this function will fail because the user is deleted before they are removed from other
					//tables like PayPeriodSchedule, so addLog() can't get the user information.
					$this->addLog( 31 );
				}

			} catch (Throwable $e) {
				throw new DBError($e);
			}

			return TRUE;
		}

		return FALSE;
	}

	function getIDSByListFactory( $lf ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach( $lf->rs as $lf_obj ) {
			$lf->data = (array)$lf_obj;
			$retarr[] = $lf->getID();
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	function bulkDelete( $ids ) {
		//Debug::text('Delete: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 9);

		//Make SURE you get the right table when calling this.
		if ( is_array($ids) AND count($ids) > 0 ) {
			$ph = array();

			$query = 'DELETE FROM '. $this->getTable() .' WHERE id in ('. $this->getListSQL( $ids, $ph ) .')';
			Debug::text('Bulk Delete Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

			try {
				DB::delete($query, $ph);
			} catch (Throwable $e) {
				throw new DBError($e);
			}

			return TRUE;
		}

		return FALSE;
	}

	function clearData() {
		$this->data = array();
		$this->tmp_data = array();
		$this->next_insert_id = NULL;

		return TRUE;
	}

	final function getIterator() {
		return new FactoryListIterator($this);
	}

	//Grabs the current object
	final function getCurrent() {
		return $this->getIterator()->current();
	}

	public static function checkTableExists($table_name)
	{
		return Schema::hasTable($table_name);
	}

	//===========================================================================
	// added by desh(2025-03-18)
	//===========================================================================
	public function getCurrentUser(){
		return $this->currentUser;
	}
	public function getProfiler(){
		return $this->profiler;
	}
	public function getUserPrefs(){
		return $this->userPrefs;
	}
	public function getCurrentCompany(){
		return $this->currentCompany;
	}
	public function getPermission(){
		return $this->permission;
	}
	public function getConfigVars(){
		return $this->configVars;
	}

	static function convertToSeconds($time) {
		// Validate the format of the time (hh:mm)
		if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
			throw new Exception("Invalid time format. Expected hh:mm (e.g., 05:00).");
		}

		list($hours, $minutes) = explode(':', $time);
		return ($hours * 3600) + ($minutes * 60);
	}

	static function convertToHoursAndMinutes($seconds) {
		$hours = floor($seconds / 3600);  // Get the total hours
		$minutes = floor(($seconds % 3600) / 60);  // Get the remaining minutes

		return sprintf("%02d:%02d", $hours, $minutes);  // Return in hh:mm format
	}

	//===========================================================================

}
?>
