<?php

namespace App\Models\Users;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class UserKpiFactory extends Factory {
	protected $table = 'user_kpi';
	protected $pk_sequence_name = ' user_kpi_id_seq'; //PK Sequence name

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
                                    
                                    						'-1200-start_date' => TTi18n::gettext('Start Date'),//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
										'-1210-end_date' => TTi18n::gettext('End Date'),//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                                                                                '-1210-review_date' => TTi18n::gettext('Review Date'),//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON


										//'-5010-transit' => TTi18n::gettext('Transit/Routing'),
										//'-5020-account' => TTi18n::gettext('Account'),
										//'-5030-institution' => TTi18n::gettext('Institution'),
                                    
                                                                                //'-1290-note' => TTi18n::gettext('Note'),//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON

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
								'start_date',//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
								'end_name',//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
								'review_date',//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
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
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',

										'deleted' => 'Deleted',
                    
                                                                                'first_date' => 'FirstDate',
                                                                                'last_date' => 'LastDate',
                                                                                'review_date' => 'ReviewDate',
                                                                                'scorea1' => 'ScoreA1',
                    
                    
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
 
        
	function getStartDate( $raw = FALSE ) {
		if ( isset($this->data['start_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['start_date'];
			} else {
				return TTDate::strtotime( $this->data['start_date'] );
			}
		}

		return FALSE;
	}
	function setStartDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ){
			$epoch = NULL;
		}

		if 	(
				$epoch == NULL
				OR
				$this->Validator->isDate(		'start_date',
												$epoch,
												TTi18n::gettext('Incorrect start date'))
			) {

			$this->data['start_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndDate( $raw = FALSE ) {
		if ( isset($this->data['end_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['end_date'];
			} else {
				return TTDate::strtotime( $this->data['end_date'] );
			}
		}

		return FALSE;
	}
	function setEndDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ){
			$epoch = NULL;
		}

		if 	(	$epoch == NULL
				OR
				$this->Validator->isDate(		'end_date',
												$epoch,
												TTi18n::gettext('Incorrect end date'))
			) {

			$this->data['end_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}        
        
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function checkScoreRange($value) {
		if ( $value >= 0 AND $value <= 100 ) {
			return TRUE;
		}

		return FALSE;
	}        
        
       //Score A 1-12
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA1() {
		if ( isset($this->data['scorea1']) ) {
			return $this->data['scorea1'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA1($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea1',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea1',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea1'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }        
        
                                                                                
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA2() {
		if ( isset($this->data['scorea2']) ) {
			return $this->data['scorea2'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA2($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea2',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea2',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea2'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA3() {
		if ( isset($this->data['scorea3']) ) {
			return $this->data['scorea3'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA3($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea3',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea3',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea3'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA4() {
		if ( isset($this->data['scorea4']) ) {
			return $this->data['scorea4'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA4($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea4',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea4',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea4'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA5() {
		if ( isset($this->data['scorea5']) ) {
			return $this->data['scorea5'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA5($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea5',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea5',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea5'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA6() {
		if ( isset($this->data['scorea6']) ) {
			return $this->data['scorea6'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA6($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea6',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea6',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea6'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA7() {
		if ( isset($this->data['scorea7']) ) {
			return $this->data['scorea7'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA7($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea7',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea7',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea7'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA8() {
		if ( isset($this->data['scorea8']) ) {
			return $this->data['scorea8'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA8($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea8',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea8',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea8'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA9() {
		if ( isset($this->data['scorea9']) ) {
			return $this->data['scorea9'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA9($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea9',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea9',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea9'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA10() {
		if ( isset($this->data['scorea10']) ) {
			return $this->data['scorea10'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA10($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea10',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea10',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea10'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA11() {
		if ( isset($this->data['scorea11']) ) {
			return $this->data['scorea11'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA11($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea11',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea11',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea11'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreA12() {
		if ( isset($this->data['scorea12']) ) {
			return $this->data['scorea12'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreA12($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorea12',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorea12',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorea12'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
        
                                                                                
        //Score B 1-6

        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreB1() {
		if ( isset($this->data['scoreb1']) ) {
			return $this->data['scoreb1'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreB1($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scoreb1',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scoreb1',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scoreb1'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }        
        
                                                                                
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreB2() {
		if ( isset($this->data['scoreb2']) ) {
			return $this->data['scoreb2'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreB2($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scoreb2',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scoreb2',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scoreb2'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreB3() {
		if ( isset($this->data['scoreb3']) ) {
			return $this->data['scoreb3'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreB3($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scoreb3',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scoreb3',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scoreb3'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreB4() {
		if ( isset($this->data['scoreb4']) ) {
			return $this->data['scoreb4'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreB4($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scoreb4',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scoreb4',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scoreb4'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreB5() {
		if ( isset($this->data['scoreb5']) ) {
			return $this->data['scoreb5'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreB5($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scoreb5',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scoreb5',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scoreb5'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreB6() {
		if ( isset($this->data['scoreb6']) ) {
			return $this->data['scoreb6'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreB6($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scoreb6',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scoreb6',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scoreb6'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }                                                                                 
                                                                                
                                                                                
        //Score C 1-6

        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreC1() {
		if ( isset($this->data['scorec1']) ) {
			return $this->data['scorec1'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreC1($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorec1',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorec1',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorec1'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }        
        
                                                                                
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreC2() {
		if ( isset($this->data['scorec2']) ) {
			return $this->data['scorec2'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreC2($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorec2',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorec2',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorec2'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreC3() {
		if ( isset($this->data['scorec3']) ) {
			return $this->data['scorec3'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreC3($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorec3',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorec3',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorec3'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreC4() {
		if ( isset($this->data['scorec4']) ) {
			return $this->data['scorec4'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreC4($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorec4',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorec4',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorec4'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreC5() {
		if ( isset($this->data['scorec5']) ) {
			return $this->data['scorec5'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreC5($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorec5',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorec5',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorec5'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreC6() {
		if ( isset($this->data['scorec6']) ) {
			return $this->data['scorec6'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreC6($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scorec6',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scorec6',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scorec6'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }        
                                                                                
                                                                                
                                                                                

        //Score D 1-6

        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreD1() {
		if ( isset($this->data['scored1']) ) {
			return $this->data['scored1'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreD1($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scored1',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scored1',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scored1'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }        
        
                                                                                
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreD2() {
		if ( isset($this->data['scored2']) ) {
			return $this->data['scored2'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreD2($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scored2',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scored2',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scored2'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreD3() {
		if ( isset($this->data['scored3']) ) {
			return $this->data['scored3'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreD3($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scored3',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scored3',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scored3'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreD4() {
		if ( isset($this->data['scored4']) ) {
			return $this->data['scored4'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreD4($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scored4',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scored4',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scored4'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreD5() {
		if ( isset($this->data['scored5']) ) {
			return $this->data['scored5'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreD5($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scored5',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scored5',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scored5'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                } 
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getScoreD6() {
		if ( isset($this->data['scored6']) ) {
			return $this->data['scored6'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setScoreD6($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'scored6',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'scored6',
														$this->checkScoreRange($value),
														TTi18n::gettext('Score range is 0-10')
													)                        
                        
                        ) {


			$this->data['scored6'] = $value;

			return TRUE;
		}

		return FALSE;
	
                
                                                                                }                                                                                  
                                                                                

                           
       //----------------Remarks------------------------------------------------
                                    
        //Remark A 1-12                                                                        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA1() {
		if ( isset($this->data['remarka1']) ) {
			return $this->data['remarka1'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka1',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka1'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA2() {
		if ( isset($this->data['remarka2']) ) {
			return $this->data['remarka2'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka2',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka2'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA3() {
		if ( isset($this->data['remarka3']) ) {
			return $this->data['remarka3'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka3',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka3'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA4() {
		if ( isset($this->data['remarka4']) ) {
			return $this->data['remarka4'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka4',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka4'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA5() {
		if ( isset($this->data['remarka5']) ) {
			return $this->data['remarka5'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka5',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka5'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA6() {
		if ( isset($this->data['remarka6']) ) {
			return $this->data['remarka6'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA6($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka6',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka6'] = $value;

			return FALSE;
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA7() {
		if ( isset($this->data['remarka7']) ) {
			return $this->data['remarka7'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA7($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka7',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka7'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA8() {
		if ( isset($this->data['remarka8']) ) {
			return $this->data['remarka8'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA8($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka8',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka8'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA9() {
		if ( isset($this->data['remarka9']) ) {
			return $this->data['remarka9'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA9($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka9',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka9'] = $value;

			return FALSE;
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA10() {
		if ( isset($this->data['remarka10']) ) {
			return $this->data['remarka10'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA10($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka10',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka10'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA11() {
		if ( isset($this->data['remarka11']) ) {
			return $this->data['remarka11'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA11($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka11',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka11'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkA12() {
		if ( isset($this->data['remarka12']) ) {
			return $this->data['remarka12'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkA12($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarka12',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarka12'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        //Remark B 1-6                                                                        
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkB1() {
		if ( isset($this->data['remarkb1']) ) {
			return $this->data['remarkb1'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkB1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkb1',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkb1'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkB2() {
		if ( isset($this->data['remarkb2']) ) {
			return $this->data['remarkb2'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkB2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkb2',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkb2'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkB3() {
		if ( isset($this->data['remarkb3']) ) {
			return $this->data['remarkb3'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkB3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkb3',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkb3'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkB4() {
		if ( isset($this->data['remarkb4']) ) {
			return $this->data['remarkb4'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkB4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkb4',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkb4'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkB5() {
		if ( isset($this->data['remarkb5']) ) {
			return $this->data['remarkb5'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkB5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkb5',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkb5'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkB6() {
		if ( isset($this->data['remarkb6']) ) {
			return $this->data['remarkb6'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkB6($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkb6',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkb6'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        
        //Remark C 1-6                                                                        
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkC1() {
		if ( isset($this->data['remarkc1']) ) {
			return $this->data['remarkc1'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkC1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkc1',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkc1'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkC2() {
		if ( isset($this->data['remarkc2']) ) {
			return $this->data['remarkc2'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkC2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkc2',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkc2'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkC3() {
		if ( isset($this->data['remarkc3']) ) {
			return $this->data['remarkc3'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkC3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkc3',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkc3'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkC4() {
		if ( isset($this->data['remarkc4']) ) {
			return $this->data['remarkc4'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON

         */          
	function setRemarkC4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkc4',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkc4'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkC5() {
		if ( isset($this->data['remarkc5']) ) {
			return $this->data['remarkc5'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkC5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkc5',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkc5'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkC6() {
		if ( isset($this->data['remarkc6']) ) {
			return $this->data['remarkc6'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkC6($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkc6',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkc6'] = $value;

			return FALSE;
		}

		return FALSE;
	}          
        
        
        //Remark D 1-6                                                                        
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkD1() {
		if ( isset($this->data['remarkd1']) ) {
			return $this->data['remarkd1'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkD1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkd1',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkd1'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkD2() {
		if ( isset($this->data['remarkd2']) ) {
			return $this->data['remarkd2'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkD2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkd2',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkd2'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkD3() {
		if ( isset($this->data['remarkd3']) ) {
			return $this->data['remarkd3'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkD3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkd3',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkd3'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkD4() {
		if ( isset($this->data['remarkd4']) ) {
			return $this->data['remarkd4'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON

         */          
	function setRemarkD4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkd4',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkd4'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkD5() {
		if ( isset($this->data['remarkd5']) ) {
			return $this->data['remarkd5'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkD5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkd5',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkd5'] = $value;

			return FALSE;
		}

		return FALSE;
	}  
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getRemarkD6() {
		if ( isset($this->data['remarkd6']) ) {
			return $this->data['remarkd6'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setRemarkD6($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'remarkd6',
														$value,
														TTi18n::gettext('Remark is too long'),
														1,
														2048)
			) {

			$this->data['remarkd6'] = $value;

			return FALSE;
		}

		return FALSE;
	}          
                                                                                
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getTotalScore() {
		if ( isset($this->data['total_score']) ) {
			return $this->data['total_score'];
		}

		return FALSE;
	}
        
        
        
        
        
        function getAvarageKeyPerfomance() {
		if ( isset($this->data['avg_key_peformance']) ) {
			return $this->data['avg_key_peformance'];
		}

		return FALSE;
	}
        
        
        function setAvarageKeyPerfomance($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'avg_key_peformance',
												$value,
												TTi18n::gettext('Invalid key performance')
										)
                        
                        AND 
				$this->Validator->isTrue(	'avg_key_peformance',
														$this->checkScoreRange($value),
														TTi18n::gettext('key performance range is 0-100')
													)                        
                        
                        ) {

//                        echo "Test 2 = ";
//                        echo $value;
//                        exit();
			$this->data['avg_key_peformance'] = $value;

			return TRUE;
		}

		return FALSE;
	
             }   
             
             
             
        function getTotalScoreGenaral() {
		if ( isset($this->data['total_score_genaral']) ) {
			return $this->data['total_score_genaral'];
		}

		return FALSE;
	}
             
         
        function setTotalScoreGenaral($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'total_score_genaral',
												$value,
												TTi18n::gettext('Invalid total genaral score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'total_score_genaral',
														$this->checkScoreRange($value),
														TTi18n::gettext('Total genaral score range is 0-100')
													)                        
                        
                        ) {

//                        echo "Test 2 = ";
//                        echo $value;
//                        exit();
			$this->data['total_score_genaral'] = $value;

			return TRUE;
		}

		return FALSE;
	
             }   
             
         
        /** avg_key_peformance
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getFeedback1() {
		if ( isset($this->data['feedback1']) ) {
			return $this->data['feedback1'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setFeedback1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'feedback1',
														$value,
														TTi18n::gettext('Feedback is too long'),
														1,
														2048)
			) {

			$this->data['feedback1'] = $value;

			return FALSE;
		}

		return FALSE;
	}          
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getFeedback2() {
		if ( isset($this->data['feedback2']) ) {
			return $this->data['feedback2'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setFeedback2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'feedback2',
														$value,
														TTi18n::gettext('Feedback is too long'),
														1,
														2048)
			) {

			$this->data['feedback2'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getFeedback3() {
		if ( isset($this->data['feedback3']) ) {
			return $this->data['feedback3'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setFeedback3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'feedback3',
														$value,
														TTi18n::gettext('Feedback is too long'),
														1,
														2048)
			) {

			$this->data['feedback3'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getFeedback4() {
		if ( isset($this->data['feedback4']) ) {
			return $this->data['feedback4'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setFeedback4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'feedback4',
														$value,
														TTi18n::gettext('Feedback is too long'),
														1,
														2048)
			) {

			$this->data['feedback4'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getFeedback5() {
		if ( isset($this->data['feedback5']) ) {
			return $this->data['feedback5'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setFeedback5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'feedback5',
														$value,
														TTi18n::gettext('Feedback is too long'),
														1,
														2048)
			) {

			$this->data['feedback5'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getFeedback6() {
		if ( isset($this->data['feedback6']) ) {
			return $this->data['feedback6'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setFeedback6($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'feedback6',
														$value,
														TTi18n::gettext('Feedback is too long'),
														1,
														2048)
			) {

			$this->data['feedback6'] = $value;

			return FALSE;
		}

		return FALSE;
	}   
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getFeedback7() {
		if ( isset($this->data['feedback7']) ) {
			return $this->data['feedback7'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setFeedback7($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'feedback7',
														$value,
														TTi18n::gettext('Feedback is too long'),
														1,
														2048)
			) {

			$this->data['feedback7'] = $value;

			return FALSE;
		}

		return FALSE;
	}           
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function getFeedback8() {
		if ( isset($this->data['feedback8']) ) {
			return $this->data['feedback8'];
		}

		return FALSE;
	}
        
        /**
         * ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * BELOW DETAILS ARE DATABASE  TABLE FIELDS
         */          
	function setFeedback8($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'feedback8',
														$value,
														TTi18n::gettext('Feedback is too long'),
														1,
														2048)
			) {

			$this->data['feedback8'] = $value;

			return FALSE;
		}

		return FALSE;
	}            
        
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */         
	function setTotalScore($value) {
		$value = trim($value);

		if ( $value == NULL OR $this->Validator->isNumeric(		'total_score',
												$value,
												TTi18n::gettext('Invalid Score')
										)
                        
                        AND 
				$this->Validator->isTrue(	'total_score',
														$this->checkScoreRange($value),
														TTi18n::gettext('Total Score range is 0-10')
													)                        
                        
                        ) {

//                        echo "Test 2 = ";
//                        echo $value;
//                        exit();
			$this->data['total_score'] = $value;

			return TRUE;
		}

		return FALSE;
	
             }                                 

        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function getReviewDate( $raw = FALSE ) {
		if ( isset($this->data['review_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['review_date'];
			} else {
				return TTDate::strtotime( $this->data['review_date'] );
			}
		}
		return FALSE;
	}
        
        /**
         * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
         * 
         */ 
	function setReviewDate($epoch) {
                $epoch = trim($epoch);

		//Debug::Text('Effective Date: '. TTDate::getDate('DATE+TIME', $epoch ) , __FILE__, __LINE__, __METHOD__,10);

		if 	(	$this->Validator->isDate(		'review_date',
												$epoch,
												TTi18n::gettext('Incorrect review date'))
			) {

			//$this->data['first_worked_date'] = $epoch;

			//return TRUE;
                    
			if 	( $epoch > 0 ) {
				$this->data['review_date'] = $epoch;

				return TRUE;
			} else {
				$this->Validator->isTRUE(		'review_date',
												FALSE,
												TTi18n::gettext('Incorrect review date'));
			}                    
                    
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
		if ( $this->getEndDate() != '' AND $this->getStartDate() != '' AND $this->getEndDate() < $this->getStartDate() ) {
			$this->Validator->isTrue(		'end_date',
											FALSE,
											TTi18n::gettext('Conflicting end date'));
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
         * ARSP NOTE --> I'M NOT MODIFIED THIS FUNCTION
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
         * ARSP NOTE --> I'M NOT MODIFIED THIS FUNCTION.
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

        /**
         * ARSP NOTE --> I'M NOT MODIFIED THIS FUNCTION.
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

