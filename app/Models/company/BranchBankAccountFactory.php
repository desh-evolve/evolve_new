<?php

namespace App\Models\Company;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\core\Validator;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\Log;
use App\Models\Core\TTLog;
use Illuminate\Support\Facades\DB;

class BranchBankAccountFactory extends Factory {
	protected $table = 'branch_bank_account';
	protected $pk_sequence_name = 'branch_bank_account_id_seq'; //PK Sequence name
	protected $Validator;

	public function __construct() {
        $this->Validator = new Validator();
    }

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
					'-1010-first_name' => ('First Name'),
					'-1020-last_name' => ('Last Name'),

					'-1090-title' => ('Title'),
					'-1099-group' => ('Group'),
					'-1100-default_branch' => ('Branch'),
					'-1110-default_department' => ('Department'),

					'-5010-transit' => ('Transit/Routing'),
					'-5020-account' => ('Account'),
					'-5030-institution' => ('Institution'),

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
					'first_name',
					'last_name',
					'account',
					'institution',
				);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap($param = null) {
		$variable_function_map = array(
			'id' => 'ID',
			'company_id' => 'Company',
			'user_id' => 'User',
			'institution' => 'Institution',
			'transit' => 'Transit',
			'account' => 'Account',
			'deleted' => 'Deleted',
		);
		return $variable_function_map;
	}

	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = new CompanyListFactory();

		if ( $this->Validator->isResultSetWithRows( 'company', $clf->getByID($id), ('Company is invalid') ) ) {

			$this->data['company_id'] = $id;

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

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows( 'user', $ulf->getByID($id), ('Invalid User') ) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUnique() {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$ph = array(
					':company_id' =>  (int)$this->getCompany(),
					':user_id' => (int)$this->getUser(),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = :company_id AND user_id = :user_id AND deleted = 0';

        $id = DB::select($query, $ph);

        if ($id === FALSE ) {
            $id = 0;
        }else{
            $id = current(get_object_vars($id[0]));
        }

		Debug::Arr($id,'Unique ID: '. $id, __FILE__, __LINE__, __METHOD__,10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getInstitution() {
		if ( isset($this->data['institution']) ) {
			return $this->data['institution'];
		}

		return FALSE;
	}
	function setInstitution($value) {
		$value = trim($value);

		if (
				$this->Validator->isNumeric( 'institution', $value, ('Invalid institution number, must be digits only'))
				AND
				$this->Validator->isLength( 'institution', $value, ('Invalid institution number length'), 2, 3)
			) {

			$this->data['institution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getTransit() {
		if ( isset($this->data['transit']) ) {
			return $this->data['transit'];
		}

		return FALSE;
	}


        /*
         * ARSP EDIT--> THIS CODE USE TO ADD  THE BANK CODE NOT TRANSIT
         *
         */
	function setTransit($value) {
		$value = trim($value);

		if (
				$this->Validator->isNumeric( 'transit', $value, ('Invalid Bank Code, must be digits only '))
				AND
				$this->Validator->isLength( 'transit', $value, ('Invalid Bank Code length'), 2, 15)
			) {

			$this->data['transit'] = $value;

			return FALSE;
		}

		return FALSE;
	}



        /*
         * ARSP EDIT--> I ADD NEW CODE FOR GET BANK NAME
         *
         */
	function getBankName() {
		if ( isset($this->data['bank_name']) ) {
			return $this->data['bank_name'];
		}

		return FALSE;
	}


        /*
         * RSP EDIT--> I ADD NEW CODE FOR SET BANK NAME
         *
         */
	function setBankName($value) {
		$value = trim($value);

		if ( $this->Validator->isLength( 'bank_name', $value, ('Incorrect Bank Name length'), 1, 100) ) {

			$this->data['bank_name'] = $value;

			return FALSE;
		}

		return FALSE;
	}



        /*
         * ARSP EDIT--> I ADD NEW CODE FOR GET BANK BRANCH NAME
         *
         */
	function getBankBranch() {
		if ( isset($this->data['bank_branch']) ) {
			return $this->data['bank_branch'];
		}

		return FALSE;
	}


        /*
         * ARSP EDIT--> I ADD NEW CODE FOR SET BANK BRANCH NAME
         *
         */
	function setBankBranch($value) {
		$value = trim($value);

		if ( $this->Validator->isLength( 'bank_branch', $value, ('Incorrect Bank Branch Length'), 1, 100) ) {

			$this->data['bank_branch'] = $value;

			return FALSE;
		}

		return FALSE;
	}



        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         *
         */
	function setDefaultBranch($id) {
		$id = (int)trim($id);

		$blf = new BranchListFactory();
		if ( $id == 0 OR $this->Validator->isResultSetWithRows( 'default_branch', $blf->getByID($id), ('Invalid Default Branch') ) ) {

			$this->data['default_branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         *
         */
	function getDefaultBranch() {
		if ( isset($this->data['default_branch_id']) ) {
			return $this->data['default_branch_id'];
		}

		return FALSE;
	}

	function getSecureAccount( $value = NULL ) {
		if ( $value == '' ) {
			$value = $this->getAccount();
		}

		$account = str_replace( substr($value,2,3), 'XXX', $value );
		return $account;
	}
	function getAccount() {
		if ( isset($this->data['account']) ) {
			return $this->data['account'];
		}

		return FALSE;
	}
	function setAccount($value) {
		//If *'s are in the account number, skip setting it
		//This allows them to change other data without seeing the account number.
		if ( stripos( $value, 'X') !== FALSE  ) {
			return FALSE;
		}

		$value = $this->Validator->stripNonNumeric( trim($value) );
		if ( $this->Validator->isLength( 'account', $value, ('Invalid account number length'), 3, 20) ) {

			$this->data['account'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		
		echo 'getDeleted: ';
		print_r($this->getDeleted());
		echo '<br>';
		echo 'isUnique: ';
		print_r($this->isUnique());
		echo '<br>';
		exit;
		//Make sure this entry is unique.
		if ( $this->getDeleted() == FALSE AND $this->isUnique() == TRUE ) {
			$this->Validator->isTRUE( 'account', FALSE, ('Bank account already exists') );

			return FALSE;
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getUser() == FALSE ) {
			Debug::Text('Clearing User value, because this is strictly a company record', __FILE__, __LINE__, __METHOD__,10);
			//$this->setUser( 0 ); //COMPANY record.
		}

		//PGSQL has a NOT NULL constraint on Instituion number prior to schema v1014A.
		if ( $this->getInstitution() == FALSE ) {
			$this->setInstitution( '000' );
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
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
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'account':
							$data[$variable] = $this->getSecureAccount();
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
		if ( $this->getUser() == '' ) {
			$log_description = ('Company');
		} else {
			$log_description = ('Employee');
		}
		return TTLog::addEntry( $this->getId(), $log_action, ('Bank Account') .' - '. $log_description, NULL, $this->getTable(), $this );
	}

}
?>
