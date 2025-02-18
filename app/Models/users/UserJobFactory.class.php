<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Payroll Services Copyright (C) 2003 - 2012 TimeTrex Payroll Services.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/
/*
 * $Revision: 4881 $
 * $Id: BankAccountFactory.class.php 4881 2011-06-25 23:00:54Z ipso $
 * $Date: 2011-06-25 16:00:54 -0700 (Sat, 25 Jun 2011) $
 */

/**
 * @package Module_Users
 */

/*******************************************************************************
 * 
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 * THIS CODE ADDED BY ME
 * CREATE USERES JOB HISTORY
 * 
 *******************************************************************************/

class UserJobFactory extends Factory {
	protected $table = 'user_job_history';
	protected $pk_sequence_name = 'user_job_history_id_seq'; //PK Sequence name

        /**
         * ASRP NOTE --> I MODIFIED THIS CODE FOR THUNDER & NEON
         */
	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(

										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),

										'-1090-title' => TTi18n::gettext('Title'),
										//'-1099-group' => TTi18n::gettext('Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

										//'-5010-transit' => TTi18n::gettext('Transit/Routing'),
										//'-5020-account' => TTi18n::gettext('Account'),
										//'-5030-institution' => TTi18n::gettext('Institution'),
                                    
                                                                                '-1290-note' => TTi18n::gettext('Note'),//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'first_worked_date',//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;

		}

		return $retval;
	}

        /**
         * ASRP NOTE --> I MODIFIED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */        
	function _getVariableToFunctionMap() {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',

										'deleted' => 'Deleted',
                    
                                                                                'first_worked_date' => 'FirstWorkedDate',
                                                                                'last_worked_date' => 'LastWorkedDate',
                                                                                'note' => 'Note',                    
                    
										'title_id' => 'Title',
										'title' => FALSE,
										'default_branch_id' => 'DefaultBranch',
										'default_branch' => FALSE,
										'default_department_id' => 'DefaultDepartment',
										'default_department' => FALSE,                                                                               
										);
		return $variable_function_map;
	}
        
        
        
    //-----------------------------ARSP NOTE --> NEW FUNCTION FOR THUNDER & NEON    
 
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setNote($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'note',
														$value,
														TTi18n::gettext('Note is too long'),
														1,
														2048)
			) {

			$this->data['note'] = $value;

			return FALSE;
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
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */        
	function setDefaultBranch($id) {
		$id = (int)trim($id);

		$blf = TTnew( 'BranchListFactory' );
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'default_branch',
														$blf->getByID($id),
														TTi18n::gettext('Invalid Default Branch')
													) ) {

			$this->data['default_branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}         
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */      
	function getTitle() {
		if ( isset($this->data['title_id']) ) {
			return $this->data['title_id'];
		}

		return FALSE;
	}
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */      
	function setTitle($id) {
		$id = (int)trim($id);

		$utlf = TTnew( 'UserTitleListFactory' );
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'title',
														$utlf->getByID($id),
														TTi18n::gettext('Title is invalid')
													) ) {

			$this->data['title_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}     
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getDefaultDepartment() {
		if ( isset($this->data['default_department_id']) ) {
			return $this->data['default_department_id'];
		}

		return FALSE;
	}
        
	/**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
        function setDefaultDepartment($id) {
		$id = (int)trim($id);

		$dlf = TTnew( 'DepartmentListFactory' );
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'default_department',
														$dlf->getByID($id),
														TTi18n::gettext('Invalid Default Department')
													) ) {

			$this->data['default_department_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getFirstWorkedDate( $raw = FALSE ) {
		if ( isset($this->data['first_worked_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['first_worked_date'];
			} else {
				return TTDate::strtotime( $this->data['first_worked_date'] );
			}
		}

		return FALSE;
	}        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function setFirstWorkedDate($epoch) {
            
            //echo 'This is setFirstWorkedDate = '.$epoch;
            //exit();
            
		//$epoch = TTDate::getBeginDayEpoch( trim($epoch) );
                $epoch = trim($epoch);

		//Debug::Text('Effective Date: '. TTDate::getDate('DATE+TIME', $epoch ) , __FILE__, __LINE__, __METHOD__,10);

		if 	(	$this->Validator->isDate(		'first_worked_date',
												$epoch,
												TTi18n::gettext('Incorrect First Worked Date'))
			) {

			//$this->data['first_worked_date'] = $epoch;

			//return TRUE;
                    
			if 	( $epoch > 0 ) {
				$this->data['first_worked_date'] = $epoch;

				return TRUE;
			} else {
				$this->Validator->isTRUE(		'first_worked_date',
												FALSE,
												TTi18n::gettext('Incorrect first worked date'));
			}                    
                    
		}

		return FALSE;
	}        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getLastWorkedDate( $raw = FALSE ) {
		if ( isset($this->data['last_worked_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['last_worked_date'];
			} else {
				return TTDate::strtotime( $this->data['last_worked_date'] );
			}
		}

		return FALSE;
	}
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function setLastWorkedDate($epoch) {
                $epoch = trim($epoch);

		//Debug::Text('Effective Date: '. TTDate::getDate('DATE+TIME', $epoch ) , __FILE__, __LINE__, __METHOD__,10);

		if 	(	$this->Validator->isDate(		'last_worked_date',
												$epoch,
												TTi18n::gettext('Incorrect Last Worked Date'))
			) {

			//$this->data['first_worked_date'] = $epoch;

			//return TRUE;
                    
			if 	( $epoch > 0 ) {
				$this->data['last_worked_date'] = $epoch;

				return TRUE;
			} else {
				$this->Validator->isTRUE(		'last_worked_date',
												FALSE,
												TTi18n::gettext('Incorrect last worked date'));
			}                    
                    
		}

		return FALSE;
	}          

    //-----------------------------ARSP NOTE --> NEW FUNCTION FOR THUNDER & NEON      

	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

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

		$ulf = TTnew( 'UserListFactory' );

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

	function isUnique() {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$ph = array(
					'company_id' =>  (int)$this->getCompany(),
					'user_id' => (int)$this->getUser(),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND user_id = ? AND deleted = 0';
		$id = $this->db->GetOne($query, $ph);
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


        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function Validate() {
		//Make sure this entry is unique.
                
                //ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON
//		if ( $this->getDeleted() == FALSE AND $this->isUnique() == TRUE ) {
//			$this->Validator->isTRUE(		'account',
//											FALSE,
//											TTi18n::gettext('Bank account already exists') );
//
//			return FALSE;
//		}
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( $this->getDefaultBranch() == 0 ) {
			$this->Validator->isTrue(		'default_branch',
											FALSE,
											TTi18n::gettext('Default Branch must be specified') );
		} 
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( $this->getDefaultDepartment() == 0 ) {
			$this->Validator->isTrue(		'default_department',
											FALSE,
											TTi18n::gettext('Default Department must be specified') );
		}   
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( $this->getTitle() == 0 ) {
			$this->Validator->isTrue(		'title',
											FALSE,
											TTi18n::gettext('Employee Title must be specified') );
		}        
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( $this->getLastWorkedDate() != '' AND $this->getFirstWorkedDate() != '' AND $this->getLastWorkedDate() < $this->getFirstWorkedDate() ) {
			$this->Validator->isTrue(		'last_worked_date',
											FALSE,
											TTi18n::gettext('Conflicting last worked date'));
		}                

		return TRUE;
	}

	function preSave() {
                //ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON            
//		if ( $this->getUser() == FALSE ) {
//			Debug::Text('Clearing User value, because this is strictly a company record', __FILE__, __LINE__, __METHOD__,10);
//			//$this->setUser( 0 ); //COMPANY record.
//		}


		//PGSQL has a NOT NULL constraint on Instituion number prior to schema v1014A.
//		if ( $this->getInstitution() == FALSE ) {
//			$this->setInstitution( '000' );
//		}

		return TRUE;
	}

        /**
         * ARSP NOTE --> I'M NOT MODIFIED THIS CODE.
         */
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

        /**
         * ARSP NOTE --> I'M NOT MODIFIED THIS CODE.
         */        
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
			$this->getCreatedAndUpdatedColumns( &$data, $include_columns );
		}

		return $data;
	}

        /**
         * ARSP NOTE --> I'M NOT MODIFIED THIS CODE.
         */          
	function addLog( $log_action ) {
		if ( $this->getUser() == '' ) {
			$log_description = TTi18n::getText('Company');
		} else {
			$log_description = TTi18n::getText('Employee');
		}
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Bank Account') .' - '. $log_description, NULL, $this->getTable(), $this );
	}

}
?>

