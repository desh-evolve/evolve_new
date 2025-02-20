<?php

namespace App\Models\Users;

use App\Models\Core\Factory;

class UserFactory extends Factory {

	protected $table = 'users';
	protected $pk_sequence_name = 'users_id_seq'; //PK Sequence name

	protected $tmp_data = NULL;
	protected $user_preference_obj = NULL;
	protected $user_tax_obj = NULL;
	protected $company_obj = NULL;
	protected $title_obj = NULL;
	protected $currency_obj = NULL;

	public $validate_only = FALSE; //Used by the API to ignore certain validation checks if we are doing validation only.

	protected $username_validator_regex = '/^[a-z0-9-_\.@]{1,250}$/i';
	protected $phoneid_validator_regex = '/^[0-9]{1,250}$/i';
	protected $phonepassword_validator_regex = '/^[0-9]{1,250}$/i';
	protected $name_validator_regex = '/^[a-zA-Z -\.\'|\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	//ARSP EDIT--> ADD NEW CODE FOR VALIDATE THE NIC 
        protected $nic_validator = '/^[0-9]{9,12}[0-9VvXx]$/';

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										//Add System users (for APIs and reseller admin accounts)
										//Add "New Hire" status for employees going through the onboarding process or newly imported employees.
										10 => TTi18n::gettext('Active'),
										12 => TTi18n::gettext('Leave - Illness/Injury'),
										14 => TTi18n::gettext('Leave - Maternity/Parental'),
										16 => TTi18n::gettext('Leave - Other'),
										20 => TTi18n::gettext('Terminated'),
									);
				break;
                            
                        case 'title':
				$retval = array(
										//Add System users (for APIs and reseller admin accounts)
										//Add "New Hire" status for employees going through the onboarding process or newly imported employees.
										10 => TTi18n::gettext('Mr'),
										20 => TTi18n::gettext('Mrs'),
										30 => TTi18n::gettext('Miss'),
										40 => TTi18n::gettext('Hon'),
										
									);
				break;
                           case 'religion':
				$retval = array(
										//Add System users (for APIs and reseller admin accounts)
										//Add "New Hire" status for employees going through the onboarding process or newly imported employees.
										10 => TTi18n::gettext('Buddhist'),
										20 => TTi18n::gettext('Christian'),
										30 => TTi18n::gettext('Tamil'),
										40 => TTi18n::gettext('Muslim'),
										
									);
				break;
			case 'sex':
				$retval = array(
										5 => TTi18n::gettext('Unspecified'),
										10 => TTi18n::gettext('Male'),
										20 => TTi18n::gettext('Female'),
									);
				break;
                        case 'marital':
				$retval = array(
										5 => TTi18n::gettext('Unspecified'),
										10 => TTi18n::gettext('Singale'),
										20 => TTi18n::gettext('Marrid'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1005-company' => TTi18n::gettext('Company'),
										'-1010-employee_number' => TTi18n::gettext('Employee #'),
										'-1020-status' => TTi18n::gettext('Status'),
										'-1030-user_name' => TTi18n::gettext('User Name'),
										'-1040-phone_id' => TTi18n::gettext('Quick Punch ID'),

										'-1060-first_name' => TTi18n::gettext('First Name'),
										'-1070-middle_name' => TTi18n::gettext('Middle Name'),
										'-1080-last_name' => TTi18n::gettext('Last Name'),

										'-1090-title' => TTi18n::gettext('Title'),
                         /*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/ '-1091-job_skills' => TTi18n::gettext('JobSkills'),
										'-1099-user_group' => TTi18n::gettext('Group'), //Update ImportUser class if sort order is changed for this.
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),
										'-1110-currency' => TTi18n::gettext('Currency'),

										'-1112-permission_control' => TTi18n::gettext('Permission Group'),
										'-1112-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1112-policy_group' => TTi18n::gettext('Policy Group'),

										'-1120-sex' => TTi18n::gettext('Sex'),

										'-1130-address1' => TTi18n::gettext('Address 1'),
										'-1140-address2' => TTi18n::gettext('Address 2'),
										
    /* ARSP ADD CODE---> */             '-1145-nic' => TTi18n::gettext('Nic'),

										'-1150-city' => TTi18n::gettext('City'),
										'-1160-province' => TTi18n::gettext('Province/State'),
										'-1170-country' => TTi18n::gettext('Country'),
										'-1180-postal_code' => TTi18n::gettext('Postal Code'),
										'-1190-work_phone' => TTi18n::gettext('Work Phone'),
										'-1191-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1200-home_phone' => TTi18n::gettext('Home Phone'),
										'-1210-mobile_phone' => TTi18n::gettext('Mobile Phone'),
										'-1220-fax_phone' => TTi18n::gettext('Fax Phone'),
										'-1230-home_email' => TTi18n::gettext('Home Email'),
										'-1240-work_email' => TTi18n::gettext('Work Email'),
										'-1250-birth_date' => TTi18n::gettext('Birth Date'),
										'-1260-hire_date' => TTi18n::gettext('Appointment Date'),
										'-1270-termination_date' => TTi18n::gettext('Termination Date'),
										'-1280-sin' => TTi18n::gettext('SIN/SSN'),
										'-1290-note' => TTi18n::gettext('Note'),
                         /*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/ '-1291-hire_note' => TTi18n::gettext('HireNote'),
                         /*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/ '-1292-termination_note' => TTi18n::gettext('TerminationNote'),                                    
										
										'-1300-tag' => TTi18n::gettext('Tags'),
										'-1400-hierarchy_control_display' => TTi18n::gettext('Hierarchy'),
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
								'status',
								'employee_number',
								'first_name',
								'last_name',
								'home_phone',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'user_name',
								'phone_id',
								'employee_number',
								'sin'
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								'country',
								'province',
								'postal_code'
								);
				break;
                        /** 
                         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                         */    
			case 'month':
				$retval = array(
										//Add System users (for APIs and reseller admin accounts)
										//Add "New Hire" status for employees going through the onboarding process or newly imported employees.
										1 => TTi18n::gettext('1'),
										2 => TTi18n::gettext('2'),
										3 => TTi18n::gettext('3'),
										4 => TTi18n::gettext('4'),
										5 => TTi18n::gettext('5'),
										6 => TTi18n::gettext('6'),
										7 => TTi18n::gettext('7'),
										8 => TTi18n::gettext('8'),
										9 => TTi18n::gettext('9'),
										10 => TTi18n::gettext('10'),
										11 => TTi18n::gettext('11'),
										12 => TTi18n::gettext('12'),
                                    
									);
				break;  

                       /** 
                         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                         */    
			case 'bond_period':
				$retval = array(
										//Add System users (for APIs and reseller admin accounts)
										//Add "New Hire" status for employees going through the onboarding process or newly imported employees.
                                        0 => TTi18n::gettext('--'),
										3 => TTi18n::gettext('3'),
										6 => TTi18n::gettext('6'),
										9 => TTi18n::gettext('9'),
										12 => TTi18n::gettext('12'),
										18 => TTi18n::gettext('18'),
										24 => TTi18n::gettext('24'),
										30 => TTi18n::gettext('30'),
										36 => TTi18n::gettext('36'),
                                    
									);
				break; 				

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data = null ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'company' => FALSE,
										'status_id' => 'Status',
										'status' => FALSE,
										'group_id' => 'Group',
										'user_group' => FALSE,
										'user_name' => 'UserName',
										'password' => 'Password',
										'phone_id' => 'PhoneId',
										'phone_password' => 'PhonePassword',
										'employee_number' => 'EmployeeNumber',
										'title_id' => 'Title',
										'title' => FALSE,
                          /*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/'job_skills' => 'JobSkills',
										'default_branch_id' => 'DefaultBranch',
										'default_branch' => FALSE,
										'default_department_id' => 'DefaultDepartment',
										'default_department' => FALSE,
										'permission_control_id' => 'PermissionControl',
										'permission_control' => FALSE,
										'pay_period_schedule_id' => 'PayPeriodSchedule',
										'pay_period_schedule' => FALSE,
										'policy_group_id' => 'PolicyGroup',
										'policy_group' => FALSE,
										'hierarchy_control' => 'HierarchyControl',
										'first_name' => 'FirstName',
										'middle_name' => 'MiddleName',
										'last_name' => 'LastName',
										'full_name' => 'FullName',
										'second_last_name' => 'SecondLastName',
										'sex_id' => 'Sex',
										'sex' => FALSE,
										'address1' => 'Address1',
										'address2' => 'Address2',
			/*ARSP EDIT----> */         'nic'=>'Nic',
										'city' => 'City',
										'country' => 'Country',
										'province' => 'Province',
										'postal_code' => 'PostalCode',
										'work_phone' => 'WorkPhone',
										'work_phone_ext' => 'WorkPhoneExt',
										'home_phone' => 'HomePhone',
										'mobile_phone' => 'MobilePhone',
										'fax_phone' => 'FaxPhone',
										'home_email' => 'HomeEmail',
										'work_email' => 'WorkEmail',
										'birth_date' => 'BirthDate',
										'hire_date' => 'HireDate',
										'termination_date' => 'TerminationDate',
										'currency_id' => 'Currency',
										'currency' => FALSE,
										'sin' => 'SIN',
										'other_id1' => 'OtherID1',
										'other_id2' => 'OtherID2',
										'other_id3' => 'OtherID3',
										'other_id4' => 'OtherID4',
										'other_id5' => 'OtherID5',
										'note' => 'Note',
                          /*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/'hire_note' => 'HireNote',
                          /*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/'termination_note' => 'TerminationNote',                    
										
										'password_reset_key' => 'PasswordResetKey',
										'password_reset_date' => 'PasswordResetDate',
										'tag' => 'Tag',
										'hierarchy_control_display' => FALSE,
										'deleted' => 'Deleted',
 										);
		return $variable_function_map;
	}
	
	
	//ARSP EDIT - > Add some code to remove already stored picture
    function cleanStoragePath( $user_id = NULL ) {
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$dir = $this->getStoragePath( $user_id ) . DIRECTORY_SEPARATOR;

		if ( $dir != '' ) {
			//Delete tmp files.
			foreach(glob($dir.'*') as $filename) {
				unlink($filename);
			}
		}

		return TRUE;
	}
	
	//ARSP EDIT - > get user image storage path
	function getStoragePath($user_id=null) {
		if ( $user_id == '' ) {
			$user_id = $this->getId();
                        
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		return Environment::getStorageBasePath().'user_image'.DIRECTORY_SEPARATOR.$user_id;
                
	}
	
	 //ARSP EDIT-> get user uploaded all files URL like--- >http://localhost/evolvepayroll/storage/user_file/ex.txt
     function getUserFilesUrl( ) {
	 
                // GET THE TOTAL FILES INSIDE THE FOLDER    
                //This Code Will give full path of the file----->   $file_array =  glob($this->getUserFileStoragePathTest( $this->getId() ).'/*');
                if ($handle = opendir($this->getUserFileStoragePathTest( $this->getId() ))) {
                    $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] =  Environment::getUserFileStorageBasePath().'user_file'. '/' .$this->getId().'/'.$entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
//                echo '<pre>';
//                echo "ARSP-->"; 
//                print_r($file_array);
//                echo '<pre>';
//                    print_r(glob($_SERVER['DOCUMENT_ROOT'].'/'.$file.'/*'));
//
//		//Test for both jpg and png
//		$base_name = $this->getUserFileStoragePath( $user_id ) . DIRECTORY_SEPARATOR .'User_file';
//		//echo $base_name;
//                //exit();
//                if ( file_exists( $base_name.'.docx') ) {
//			$logo_file_name = $base_name.'.docx';
//		} elseif ( file_exists( $base_name.'.txt') ) {
//			$logo_file_name = $base_name.'.txt';
//		}
//                  elseif ( file_exists( $base_name.'.pdf') ) {
//			$logo_file_name = $base_name.'.pdf';
//                }
//                  elseif ( file_exists( $base_name.'.doc') ) {
//			$logo_file_name = $base_name.'.doc';
//                }
//                  elseif ( file_exists( $base_name.'.jpg') ) {
//			$logo_file_name = $base_name.'.jpg';
//                }
//                  elseif ( file_exists( $base_name.'.png') ) {
//			$logo_file_name = $base_name.'.png';
//                }
//
//
//		//Debug::Text('Logo File Name: '. $logo_file_name .' Include Default: '. (int)$include_default_logo .' Primary Company Logo: '. (int)$primary_company_logo, __FILE__, __LINE__, __METHOD__,10);
//		return $logo_file_name;
                return $file_array;
	}
	
	
	 //ARSP EDIT - > get original storage path like --> C:\xampp\htdocs\evolvepayroll\storage\user_file\1
     function getUserFileStoragePathTest( $user_id = NULL ) {
	 
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}
                
                //echo Environment::getStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;

		return Environment::getStorageBasePath() .'user_file'. '/'. $user_id;
                //return Environment::getStorageBasePath() . DIRECTORY_SEPARATOR .'User_file'. DIRECTORY_SEPARATOR . $user_id;
                //ARSP ADD NEW CODE HERE --> getUserFileStorageBasePath()
                //return Environment::getUserFileStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
                //return 'storage\User_file'. DIRECTORY_SEPARATOR . $user_id;
	}
	
	
	   //ARSP EDIT - > get user files storage path
       function getUserFileStoragePath( $user_id = NULL ) {
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}
                
                //echo Environment::getUserFileStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;

		//return Environment::getStorageBasePath() . DIRECTORY_SEPARATOR .'User_file'. DIRECTORY_SEPARATOR . $user_id;
                //ARSP ADD NEW CODE HERE --> getUserFileStorageBasePath()
                return Environment::getStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
                //return 'storage\User_file'. DIRECTORY_SEPARATOR . $user_id;
	}
	
        
        
        
        
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserIdCopyUrl( ) {
                // GET THE TOTAL FILES INSIDE THE FOLDER    
                //This Code Will give full path of the file----->   $file_array =  glob($this->getUserFileStoragePathTest( $this->getId() ).'/*');
                if ($handle = opendir($this->getUserIdCopyStoragePath( $this->getId() ))) {
                    $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] =  Environment::getUserFileStorageBasePath().'user_id_copy'. '/' .$this->getId().'/'.$entry;
                    $x++;
                        }       
                    }
                //print_r($file_array);
                closedir($handle);
                }
                return $file_array;
            }
            
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
        	function getUserIdCopyStoragePath( $user_id = NULL ) {
                    
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}
                
                //echo Environment::getStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
		return Environment::getStorageBasePath() .'user_id_copy'. '/'. $user_id;
                }    
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserIdCopyFileName()
                {
                if ($handle = opendir($this->getUserIdCopyStoragePath( $this->getId() ))) {
                $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] = $entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
                return $file_array;
                    
                }                
        
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserBirthCertificateUrl( ) {
                // GET THE TOTAL FILES INSIDE THE FOLDER    
                //This Code Will give full path of the file----->   $file_array =  glob($this->getUserFileStoragePathTest( $this->getId() ).'/*');
                if ($handle = opendir($this->getUserBirthCertificateStoragePath( $this->getId() ))) {
                    $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] =  Environment::getUserFileStorageBasePath().'user_birth_certificate'. '/' .$this->getId().'/'.$entry;
                    $x++;
                        }       
                    }
                //print_r($file_array);
                closedir($handle);
                }
                return $file_array;
            }
            
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
        	function getUserBirthCertificateStoragePath( $user_id = NULL ) {
                    
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}
                
                //echo Environment::getStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
		return Environment::getStorageBasePath() .'user_birth_certificate'. '/'. $user_id;
                }    
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserBirthCertificateFileName()
                {
                if ($handle = opendir($this->getUserBirthCertificateStoragePath( $this->getId() ))) {
                $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] = $entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
                return $file_array;
                    
                }                  
                
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserGsLetterUrl( ) {
                // GET THE TOTAL FILES INSIDE THE FOLDER    
                //This Code Will give full path of the file----->   $file_array =  glob($this->getUserFileStoragePathTest( $this->getId() ).'/*');
                if ($handle = opendir($this->getUserGsLetterStoragePath( $this->getId() ))) {
                    $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] =  Environment::getUserFileStorageBasePath().'user_gs_letter'. '/' .$this->getId().'/'.$entry;
                    $x++;
                        }       
                    }
                //print_r($file_array);
                closedir($handle);
                }
                return $file_array;
            }
            
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
        	function getUserGsLetterStoragePath( $user_id = NULL ) {
                    
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}
                
                //echo Environment::getStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
		return Environment::getStorageBasePath() .'user_gs_letter'. '/'. $user_id;
                }    
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserGsLetterFileName()
                {
                if ($handle = opendir($this->getUserGsLetterStoragePath( $this->getId() ))) {
                $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] = $entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
                return $file_array;
                    
                }                  
                
                
                
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserPoliceReportUrl( ) {
                // GET THE TOTAL FILES INSIDE THE FOLDER    
                //This Code Will give full path of the file----->   $file_array =  glob($this->getUserFileStoragePathTest( $this->getId() ).'/*');
                if ($handle = opendir($this->getUserPoliceReportStoragePath( $this->getId() ))) {
                    $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] =  Environment::getUserFileStorageBasePath().'user_police_report'. '/' .$this->getId().'/'.$entry;
                    $x++;
                        }       
                    }
                //print_r($file_array);
                closedir($handle);
                }
                return $file_array;
            }
            
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
        	function getUserPoliceReportStoragePath( $user_id = NULL ) {
                    
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}
                
                //echo Environment::getStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
		return Environment::getStorageBasePath() .'user_police_report'. '/'. $user_id;
                }    
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserPoliceReportFileName()
                {
                if ($handle = opendir($this->getUserPoliceReportStoragePath( $this->getId() ))) {
                $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] = $entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
                return $file_array;
                    
                }   
                
        
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserNdaUrl( ) {
                // GET THE TOTAL FILES INSIDE THE FOLDER    
                //This Code Will give full path of the file----->   $file_array =  glob($this->getUserFileStoragePathTest( $this->getId() ).'/*');
                if ($handle = opendir($this->getUserNdaUrlStoragePath( $this->getId() ))) {
                    $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] =  Environment::getUserFileStorageBasePath().'user_nda'. '/' .$this->getId().'/'.$entry;
                    $x++;
                        }       
                    }
                //print_r($file_array);
                closedir($handle);
                }
                return $file_array;
            }
            
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
        	function getUserNdaUrlStoragePath( $user_id = NULL ) {
                    
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}
                
                //echo Environment::getStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
		return Environment::getStorageBasePath() .'user_nda'. '/'. $user_id;
                }    
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getUserNdaFileName()
                {
                if ($handle = opendir($this->getUserNdaUrlStoragePath( $this->getId() ))) {
                $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] = $entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
                return $file_array;
                    
                }   
                
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getBondUrl( ) {
                // GET THE TOTAL FILES INSIDE THE FOLDER    
                //This Code Will give full path of the file----->   $file_array =  glob($this->getUserFileStoragePathTest( $this->getId() ).'/*');
                if ($handle = opendir($this->getBondUrlStoragePath( $this->getId() ))) {
                    $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] =  Environment::getUserFileStorageBasePath().'user_bond'. '/' .$this->getId().'/'.$entry;
                    $x++;
                        }       
                    }
                //print_r($file_array);
                closedir($handle);
                }
                return $file_array;
            }
            
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
        	function getBondUrlStoragePath( $user_id = NULL ) {
                    
		if ( $user_id == '' ) {
			$user_id = $this->getId();
		}

		if ( $user_id == '' ) {
			return FALSE;
		}
                
                //echo Environment::getStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
		return Environment::getStorageBasePath() .'user_bond'. '/'. $user_id;
                }    
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getBondFileName()
                {
                if ($handle = opendir($this->getBondUrlStoragePath( $this->getId() ))) {
                $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] = $entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
                return $file_array;
                    
                } 
                        
        
        
        
        
		 //ARSP EDIT-> get user uploaded all files only file name
	    function getFileName()
                {
                if ($handle = opendir($this->getUserFileStoragePathTest( $this->getId() ))) {
                $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] = $entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
                return $file_array;
                    
                }
				
			                //FL EDIT --> ADD NEW CODE FOR GET PROBATION PERIOD 20160127
                function getEmpBasisType()
                {
                    if(isset($this->data['basis_of_employment'])){
                        return $this->data['basis_of_employment'];
                    }
                    
                    return FALSE;
                    
                }
                
              //FL EDIT --> ADD NEW CODE FOR GET PROBATION PERIOD 20160127
                function SetEmpBasisType($probation)
                {
                    $probation = trim($probation);
                    
                    if($probation != ''){
                        $this->data['basis_of_employment'] = $probation;
                        
                        return TRUE;
                    }
                    
                    return FALSE;
                
                }
                        
					
				
				                //ARSP EDIT --> ADD NEW CODE FOR GET PROBATION PERIOD 
                function getProbation()
                {
                    if(isset($this->data['probation'])){
                        return $this->data['probation'];
                    }
                    
                    return FALSE;
                    
                }
                
                ////ARSP EDIT --> ADD NEW CODE FOR SET PROBATION PERIOD 
                function setProbation($probation)
                {
                    $probation = trim($probation);
                    
                    if($probation != ''){
                        $this->data['probation'] = $probation;
                        
                        return TRUE;
                    }
                    
                    return FALSE;
                
                }
				
	
                
                /*
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function getBasisOfEmployment()
                {
                    if(isset($this->data['basis_of_employment'])){
                        return $this->data['basis_of_employment'];
                    }
                    
                    return FALSE;
                    
                }
                
                /*
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
                function setBasisOfEmployment($basis_of_employment)
                {
                    $basis_of_employment = trim($basis_of_employment);
                    
                    if($basis_of_employment != ''){
                        $this->data['basis_of_employment'] = $basis_of_employment;
                        
                        return TRUE;
                    }
                    
                    return FALSE;
                
                }                
				
				
                //ARSP EDIT --> ADD NEW CODE FOR GET WARNING MESSAGES FOR IF PROBATION PERIOD IS EXCEED 
                //WARNING WILL BE SHOW BEFOR THE END OF THE MONTH TO TILL CONFIRME ADMMIN
                function getWarning($hdate, $probation)
                {            
                    $hire_date = date('d-m-Y',$hdate); 
                    $now_date = date('d-m-Y');
                        
                    $end_date = new DateTime($hire_date );
                    $period = "+".($probation -1);
                    $end_date->modify($period.' month');// modify('+3 month')this function find the after the given time period of the date
                    //echo "Test";
                    
                    $real_end_date = new DateTime($hire_date );
                    $real_period = "+".$probation;
                    $real_end_date->modify($real_period.' month');
                    
                    
                    $start = new DateTime($end_date->format('d-m-Y')); // DD-MM-YYYY 
                    $end = new DateTime($now_date);
                    $warning ="";
                    if($start <=  $end )
                    {
                         $warning =  "WARNING: Confirmation Due on ".($real_end_date->format('d-m-Y')).".";
                        return  $warning;  
                        
                    }
                    else
                    {
                         return  $warning;                        
                    }        
                         
                }
                
                
                
                /**
                 * ARSP NOTE --> I CHANGED THIS ORIGINAL CODE FOR THUNDER & NEON
                 */
                //ARSP EDIT --> ADD NEW CODE FOR GET WARNING MESSAGES FOR IF PROBATION PERIOD IS EXCEED 
                //WARNING WILL BE SHOW BEFOR THE END OF THE MONTH TO TILL CONFIRME ADMMIN
                function getWarning1($hdate, $basis_of_employment_month, $basis_of_employment)
                {            
                    $hire_date = date('d-m-Y',$hdate); 
                    $now_date = date('d-m-Y');
                    

                    $real_end_date = new DateTime($hire_date );
                    $real_period = "+".$basis_of_employment_month;
                    $real_end_date->modify($real_period.' month');
                    
                    //echo "After the 3 months(real end date) =".$real_end_date->format('Y-m-d');
                    //echo "<p/>";
                    
                    $end_date = new DateTime($real_end_date->format('d-m-Y'));
                    $period = "-45";//before the 45 days want to show the warning message
                    $end_date->modify($period.' day');// modify('+3 month')this function find the after the given time period of the date
                    
                    //echo "Warning start date =".$end_date->format('Y-m-d');
                    
                    $start = new DateTime($end_date->format('d-m-Y')); // DD-MM-YYYY 
                    $end = new DateTime($now_date);
                    
                    $basis_of_employment_option = "";
                    switch ($basis_of_employment) {
                        case 1:$basis_of_employment_option = "Contract";
                            break;
                        case 2:$basis_of_employment_option = "Training";
                            break;
                        case 3:$basis_of_employment_option = "Permanent with Probation";
                            break;
                        case 5:$basis_of_employment_option = "Resign";
                            break;                        
                        default:
                            break;
                    }
                    $warning ="";
                    if($start <=  $end )
                    {
                        $warning =  "WARNING(".$basis_of_employment_option."): Confirmation Due on ".($real_end_date->format('d-m-Y')).".";
                        return  $warning;                       
                    }
                    else
                    {
                         return  $warning;                        
                    }        
                         
                }                  
                
                //ARSP EDIT --> ADD NEW CODE FOR GET PROBATION WARNING EMPLOYEES LIST  
                function getWarningEmployees($users)
                {                    
                    
                    foreach ($users as $u_obj) {

                                                                                $hire_date = date('d-m-Y',$u_obj['hire_date']); 
                                                                                $now_date = date('d-m-Y');
                                                                                $end_date = new DateTime($hire_date );
                                                                                $probation = $u_obj['probation'];
                                                                                $period = "+".($probation -1);
                                                                                $end_date->modify($period.' month');// modify('+3 month')this function find the after the given time period of the date
                                                                                
                                                                                $real_end_date = new DateTime($hire_date );
                                                                                $real_period = "+".$probation;
                                                                                $real_end_date->modify($real_period.' month');

                                                                                $start = new DateTime($end_date->format('d-m-Y')); // DD-MM-YYYY 
                                                                                $end = new DateTime($now_date);

                                                                                $warning ="";
                                                                                if($start <=  $end )
                                                                                {
                                                                                    array_push($u_obj,($real_end_date->format('d-m-Y'))); // addd another index in to the same array
                                                                                    $warning_employee[] = $u_obj;
                                                                                    
                                                                                    //$warning_employee[1] = $real_end_date->format('d-m-Y');

                                                                                }    
                    }            
                    return $warning_employee;             
                }				
				
				
                //ARSP EDIT --> ADD NEW CODE FOR GET PROBATION WARNING EMPLOYEES LIST  
                function getWarningBasisOfEmployment($users)
                {                    
                    
                    foreach ($users as $u_obj) {
                        
                        //print_r($u_obj);
                        //exit('This is Object Test');
                                                                                if($u_obj['resign_date'] != '' && $u_obj['basis_of_employment'] == 5)
                                                                                {
                                                                                    $hire_date = date('d-m-Y',$u_obj['resign_date']); 
                                                                                    $basis_of_employment_month = 3;
                                                                                    
                                                                                    
                                                                                    $now_date = date('d-m-Y');

                                                                                    //$basis_of_employment_month = $u_obj['month'];

                                                                                    $real_end_date = new DateTime($hire_date );
                                                                                    $real_period = "+".$basis_of_employment_month;
                                                                                    $real_end_date->modify($real_period.' month');

                                                                                    $end_date = new DateTime($real_end_date->format('d-m-Y'));                                                                                
                                                                                    $period = "-45";//before the 45 days want to show the warning message
                                                                                    $end_date->modify($period.' day');// modify('+3 month')this function find the after the given time period of the date

                                                                                    //$end_date = new DateTime($hire_date );
                                                                                    //$probation = $u_obj['probation'];                                                                                                                                                                
                                                                                    //$period = "+".($probation -1);
                                                                                    //$end_date->modify($period.' month');// modify('+3 month')this function find the after the given time period of the date


                                                                                    $start = new DateTime($end_date->format('d-m-Y')); // DD-MM-YYYY 
                                                                                    $end = new DateTime($now_date);

                                                                                    $basis_of_employment= $u_obj['basis_of_employment'];   
                                                                                    $basis_of_employment_option = "";
                                                                                    switch ($basis_of_employment) {
                                                                                        case 1:$basis_of_employment_option = "Contract";
                                                                                            break;
                                                                                        case 2:$basis_of_employment_option = "Training";
                                                                                            break;
                                                                                        case 3:$basis_of_employment_option = "Permanent with Probation";
                                                                                            break;
                                                                                        case 5:$basis_of_employment_option = "Resign";
                                                                                            break;                        
                                                                                        default:
                                                                                            break;
                                                                                    }                                                                               

                                                                                    $warning ="";
                                                                                    if($start <=  $end )
                                                                                    {
                                                                                        array_push($u_obj,($real_end_date->format('d-m-Y'))); // addd another index in to the same array
                                                                                        array_push($u_obj, $basis_of_employment_option); // addd another index in to the same array
                                                                                        $warning_employee[] = $u_obj;

                                                                                        //$warning_employee[1] = $real_end_date->format('d-m-Y');

                                                                                    }                                                                                      
                                                                                }
//                                                                                
                                                                                if($u_obj['basis_of_employment'] != 5)
                                                                                {
                                                                                    
                                                                                    $hire_date = date('d-m-Y',$u_obj['hire_date']); 
                                                                                    $basis_of_employment_month = $u_obj['month'];                                                                                   
                                                                                    
                                                                                    
                                                                                    $now_date = date('d-m-Y');

                                                                                    //$basis_of_employment_month = $u_obj['month'];

                                                                                    $real_end_date = new DateTime($hire_date );
                                                                                    $real_period = "+".$basis_of_employment_month;
                                                                                    $real_end_date->modify($real_period.' month');

                                                                                    $end_date = new DateTime($real_end_date->format('d-m-Y'));                                                                                
                                                                                    $period = "-45";//before the 45 days want to show the warning message
                                                                                    $end_date->modify($period.' day');// modify('+3 month')this function find the after the given time period of the date

                                                                                    //$end_date = new DateTime($hire_date );
                                                                                    //$probation = $u_obj['probation'];                                                                                                                                                                
                                                                                    //$period = "+".($probation -1);
                                                                                    //$end_date->modify($period.' month');// modify('+3 month')this function find the after the given time period of the date


                                                                                    $start = new DateTime($end_date->format('d-m-Y')); // DD-MM-YYYY 
                                                                                    $end = new DateTime($now_date);

                                                                                    $basis_of_employment= $u_obj['basis_of_employment'];   
                                                                                    $basis_of_employment_option = "";
                                                                                    switch ($basis_of_employment) {
                                                                                        case 1:$basis_of_employment_option = "Contract";
                                                                                            break;
                                                                                        case 2:$basis_of_employment_option = "Training";
                                                                                            break;
                                                                                        case 3:$basis_of_employment_option = "Permanent with Probation";
                                                                                            break;
                                                                                        case 5:$basis_of_employment_option = "Resign";
                                                                                            break;                        
                                                                                        default:
                                                                                            break;
                                                                                    }                                                                               

                                                                                    $warning ="";
                                                                                    if($start <=  $end )
                                                                                    {
                                                                                        array_push($u_obj,($real_end_date->format('d-m-Y'))); // addd another index in to the same array
                                                                                        array_push($u_obj, $basis_of_employment_option); // addd another index in to the same array
                                                                                        $warning_employee[] = $u_obj;

                                                                                        //$warning_employee[1] = $real_end_date->format('d-m-Y');

                                                                                    }                                                                                      
                                                                                }                                                                                
  
                    }            
                    return $warning_employee;             
                }                 
                                
                
				
                //ARSP EDIT --> ADD NEW CODE FOR GET WARNING MESSAGES FOR IF PROBATION PERIOD IS EXCEED 
                //WARNING WILL BE SHOW BEFOR THE END OF THE MONTH TO TILL CONFIRME ADMMIN
                function getWarning2($hdate, $bond_period)
                {            
                    $hire_date = date('d-m-Y',$hdate); 
                    $now_date = date('d-m-Y');
                        
                    $end_date = new DateTime($hire_date );
                    $period = "+".($bond_period -1);
                    $end_date->modify($period.' month');// modify('+3 month')this function find the after the given time period of the date
                    //echo "Test";
                    
                    $real_end_date = new DateTime($hire_date );
                    $real_period = "+".$bond_period;
                    $real_end_date->modify($real_period.' month');
                    
                    
                    $start = new DateTime($end_date->format('d-m-Y')); // DD-MM-YYYY 
                    $end = new DateTime($now_date);
                    $warning ="";
                    if($start <=  $end )
                    {
                         $warning =  "WARNING(BOND): Confirmation Due on ".($real_end_date->format('d-m-Y')).".";
                        return  $warning;  
                        
                    }
                    else
                    {
                         return  $warning;                        
                    }        
                         
                }  
				

				//ARSP EDIT-> get user Tempalte file URL
                function getUserTemplateUrl( ) {
                // GET THE TOTAL FILES INSIDE THE FOLDER    
                //This Code Will give full path of the file----->   $file_array =  glob($this->getUserFileStoragePathTest( $this->getId() ).'/*');
                if ($handle = opendir($this->getUserTemplateFileStoragePathTest( $this->getId() ))) {
                    $x=0;
                while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                //echo $entry."</br>";
                    $file_array[$x] =  Environment::getUserFileStorageBasePath().'user_template_file'. '/' .$this->getId().'/'.$entry;
                    $x++;
        }
    }
                //print_r($file_array);
                closedir($handle);
}
           return $file_array;
	}			
				
				
				
				//ARSP EDIT - > get user Tempalte file storage path
				function getUserTemplateFileStoragePathTest( $user_id = NULL ) {
			if ( $user_id == '' ) {
				$user_id = $this->getId();
			}
	
			if ( $user_id == '' ) {
				return FALSE;
			}         
	
			return Environment::getStorageBasePath() .'user_template_file'. '/'. $user_id;
		}
		
		
		
					//ARSP EDIT --> ADD NEW CODE FOR GET USER TEMPLATE FILES NAME ONLY
					function getTemplateName()
					{
					if ($handle = opendir($this->getUserTemplateFileStoragePathTest( $this->getId() ))) {
					$x=0;
					while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
					//echo $entry."</br>";
						$file_array[$x] = $entry;
						$x++;
			}
		}
					//print_r($file_array);
					closedir($handle);
	}
					return $file_array;
						
					}   
		
		
					
					//ARSP EDIT -> get User Template files storage path
					function getUserTemplateFileStoragePath($user_id = NULL)
					{
			if ( $user_id == '' ) {
				$user_id = $this->getId();
			}
	
			if ( $user_id == '' ) {
				return FALSE;
			}
					
					//echo Environment::getUserFileStorageBasePath().'user_file'. DIRECTORY_SEPARATOR .$user_id;
	
			//return Environment::getStorageBasePath() . DIRECTORY_SEPARATOR .'User_file'. DIRECTORY_SEPARATOR . $user_id;
					//ARSP ADD NEW CODE HERE --> getUserFileStorageBasePath()
					return Environment::getStorageBasePath().'user_template_file'. DIRECTORY_SEPARATOR .$user_id;
					//return 'storage\User_file'. DIRECTORY_SEPARATOR . $user_id;   
						
						
					}			
					
					
					
			
		
				
                //ARSP EDIT --> ADD NEW CODE FOR GET EPF REGISTRATION NO
                function getEpfRegistrationNo()
                {
                if ( isset($this->data['epf_registration_no']) ) {
			return $this->data['epf_registration_no'];
		}

		return FALSE;                    
                    
                }
                
                
                // ARSP EDIT --> ADD CODE FOR setEpfRegistrationNo
                function setEpfRegistrationNo($epf_registration_no) {
                    
                    $epf_registration_no = trim($epf_registration_no);
                        
                    if($epf_registration_no != '' OR $epf_registration_no == ''){
                        $this->data['epf_registration_no'] = $epf_registration_no;
                        
                        return TRUE;
                    }                    
                    
                    

                        return FALSE;

                }
                
                
                
                
                //ARSP EDIT --> ADD NEW CODE FOR GET EPF REGISTRATION NO
                function getEpfMembershipNo()
                {
                if ( isset($this->data['epf_membership_no']) ) {
			return $this->data['epf_membership_no'];
		}

		return FALSE;                    
                    
                }
                
                
                // ARSP EDIT --> ADD CODE FOR setEpfMembershipNo
                function setEpfMembershipNo($epf_membership_no) {
                    
                    $epf_membership_no = trim($epf_membership_no);
                    
                    if($epf_membership_no != '' OR $epf_membership_no == ''){
                        $this->data['epf_membership_no'] = $epf_membership_no;
                        
                        return TRUE;
                    }                    
                    
                    

                        return FALSE;   

                }     	
	
	
	

	function getUserPreferenceObject() {
		if ( is_object($this->user_preference_obj) ) {
			return $this->user_preference_obj;
		} else {
			$uplf = TTnew( 'UserPreferenceListFactory' );
			$this->user_preference_obj = $uplf->getByUserId( $this->getId() )->getCurrent();

			return $this->user_preference_obj;
		}
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
			$clf->getById( $this->getCompany() );
			if ( $clf->getRecordCount() == 1 ) {
				$this->company_obj = $clf->getCurrent();

				return $this->company_obj;
			}

			return FALSE;
		}
	}

	function getTitleObject() {
		if ( is_object($this->title_obj) ) {
			return $this->title_obj;
		} else {

			$utlf = TTnew( 'UserTitleListFactory' );
			$utlf->getById( $this->getTitle() );

			if ( $utlf->getRecordCount() == 1 ) {
				$this->title_obj = $utlf->getCurrent();

				return $this->title_obj;
			}

			return FALSE;
		}
	}

	function getCurrencyObject() {
		if ( is_object($this->currency_obj) ) {
			return $this->currency_obj;
		} else {
			$clf = TTnew( 'CurrencyListFactory' );

			$clf->getById( $this->getCurrency() );
			if ( $clf->getRecordCount() == 1 ) {
				$this->currency_obj = $clf->getCurrent();
				return $this->currency_obj;
			}
		}

		return FALSE;
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
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

			return TRUE;
		}

		return FALSE;
	}
        
        /*
         * ARSP --> I ADDED THIS CODE FOR THUNDER & NEON 
         */
	function getMonth() {
		if ( isset($this->data['month']) ) {
			return (int)$this->data['month'];
		}

		return FALSE;
	}
        
        /*
         * ARSP --> I ADDED THIS CODE FOR THUNDER & NEON 
         */        
	function setMonth($month) {
		$month = trim($month);

		$key = Option::getByValue($month, $this->getOptions('month') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'month',
											$month,
											TTi18n::gettext('Incorrect Month'),
											$this->getOptions('month')) ) {

			$this->data['month'] = $month;

			return TRUE;
		}

		return FALSE;
	}        

	function getGroup() {
		if ( isset($this->data['group_id']) ) {
			return $this->data['group_id'];
		}

		return FALSE;
	}
	function setGroup($id) {
		$id = (int)trim($id);

		$uglf = TTnew( 'UserGroupListFactory' );
		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'group',
														$uglf->getByID($id),
														TTi18n::gettext('Group is invalid')
													) ) {

			$this->data['group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPermissionLevel() {
		$permission = new Permission();
		return $permission->getLevel( $this->getID(), $this->getCompany() );
	}

	function getPermissionControl() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['permission_control_id']) ) {
			return $this->tmp_data['permission_control_id'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			$pclfb = TTnew( 'PermissionControlListFactory' );
			$pclfb->getByCompanyIdAndUserId( $this->getCompany(), $this->getID() );
			if ( $pclfb->getRecordCount() > 0 ) {
				return $pclfb->getCurrent()->getId();
			}
		}

		return FALSE;
	}
	function setPermissionControl($id) {
		$id = (int)trim($id);

		$pclf = TTnew( 'PermissionControlListFactory' );

		//Get currently logged in users permission level, so we can ensure they don't assign another user to a higher level.
		global $current_user;
		if ( isset($current_user) AND is_object($current_user) ) {
			$permission = new Permission();
			$current_user_permission_level = $permission->getLevel( $current_user->getId(), $current_user->getCompany() );

		} else {
			//If we can't find the current_user object, we need to allow any permission group to be assigned, in case
			//its being modified from raw factory calls.
			$current_user_permission_level = 100;
		}

		$modify_permissions = FALSE;
		if ( $current_user_permission_level >= $this->getPermissionLevel() ) {
			$modify_permissions = TRUE;
		}
		Debug::Text('Current User Permission Level: '. $current_user_permission_level, __FILE__, __LINE__, __METHOD__,10);

		//Don't allow permissions to be modified if the currently logged in user has a lower permission level.
		//As such if someone with a lower level is able to edit the user of higher level, they must not call this function at all, or use a blank value.
		if (	$id != ''
				AND
				$this->Validator->isResultSetWithRows(		'permission_control_id',
															$pclf->getByIDAndLevel($id, $current_user_permission_level),
															TTi18n::gettext('Permission Group is invalid')
															)
				AND
				$this->Validator->isTrue(		'permission_control_id',
												$modify_permissions,
												TTi18n::gettext('Insufficient access to modify permissions for this employee')
												)
				) {
			$this->tmp_data['permission_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriodSchedule() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['pay_period_schedule_id']) ) {
			return $this->tmp_data['pay_period_schedule_id'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			$ppslfb = TTnew( 'PayPeriodScheduleListFactory' );
			$ppslfb->getByUserId( $this->getID() );
			if ( $ppslfb->getRecordCount() > 0 ) {
				return $ppslfb->getCurrent()->getId();
			}
		}

		return FALSE;
	}
	function setPayPeriodSchedule($id) {
		$id = (int)trim($id);

		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'pay_period_schedule_id',
															$ppslf->getByID($id),
															TTi18n::gettext('Pay Period schedule is invalid')
															) ) {
			$this->tmp_data['pay_period_schedule_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPolicyGroup() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['policy_group_id']) ) {
			return $this->tmp_data['policy_group_id'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getByUserIds( $this->getID());
			if ( $pglf->getRecordCount() > 0 ) {
				return $pglf->getCurrent()->getId();
			}
		}

		return FALSE;
	}
	function setPolicyGroup($id) {
		$id = (int)trim($id);

		$pglf = TTnew( 'PolicyGroupListFactory' );

		if (	$id != ''
				AND
				(
					$id == 0
					OR $this->Validator->isResultSetWithRows(	'policy_group_id',
																$pglf->getByID($id),
																TTi18n::gettext('Policy Group is invalid')
																)
				)
				) {
			$this->tmp_data['policy_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//Display each hierarchy that the employee is assigned too.
	function getHierarchyControlDisplay() {
		$hclf = TTnew( 'HierarchyControlListFactory' );
		$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
		$data = $hclf->getArrayByListFactory( $hclf, FALSE, FALSE, TRUE );

		if ( is_array($data) ) {
			$retval = array();
			foreach( $data as $id => $name ) {
				$retval[] = $name;
			}

			sort($retval); //Maintain consistent order.

			return implode(',', $retval );
		}

		return FALSE;
	}

	function getHierarchyControl() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['hierarchy_control']) ) {
			return $this->tmp_data['hierarchy_control'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			$hclf = TTnew( 'HierarchyControlListFactory' );
			$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
			return $hclf->getArrayByListFactory( $hclf, FALSE, TRUE, FALSE );
		}

		return FALSE;
	}
	function setHierarchyControl($data) {
		if ( !is_array($data) ) {
			return FALSE;
		}

		//array passed in is hierarchy_object_type_id => hierarchy_control_id
		if ( is_array($data) ) {
			$hclf = TTnew( 'HierarchyControlListFactory' );
			//Debug::Arr($data,'Hierarchy Control Data: ', __FILE__, __LINE__, __METHOD__,10);

			foreach( $data as $hierarchy_object_type_id => $hierarchy_control_id ) {
				$hierarchy_control_id = Misc::trimSortPrefix( $hierarchy_control_id );

				if (	$hierarchy_control_id == 0
						OR
						$this->Validator->isResultSetWithRows(		'hierarchy_control_id',
																	$hclf->getByID($hierarchy_control_id),
																	TTi18n::gettext('Hierarchy is invalid')
																	) ) {
					$this->tmp_data['hierarchy_control'][$hierarchy_object_type_id] = $hierarchy_control_id;
				} else {
					return FALSE;
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueUserName($user_name) {
		$ph = array(
					'user_name' => trim(strtolower($user_name)),
					);

		$query = 'select id from '. $this->getTable() .' where user_name = ? AND deleted=0';
		$user_name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_name_id,'Unique User Name: '. $user_name, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_name_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getUserName() {
		if ( isset($this->data['user_name']) ) {
			return $this->data['user_name'];
		}

		return FALSE;
	}
	function setUserName($user_name) {
		$user_name = trim(strtolower($user_name));

		if 	(	$this->Validator->isRegEx(		'user_name',
												$user_name,
												TTi18n::gettext('Incorrect characters in user name'),
												$this->username_validator_regex)
					AND
						$this->Validator->isLength(		'user_name',
														$user_name,
														TTi18n::gettext('Incorrect user name length'),
														3,
														250)
					AND
						$this->Validator->isTrue(		'user_name',
														$this->isUniqueUserName($user_name),
														TTi18n::gettext('User name is already taken')
														)
			) {

			$this->data['user_name'] = $user_name;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordSalt() {
		global $config_vars;

		if ( isset($config_vars['other']['salt']) AND $config_vars['other']['salt'] != '' ) {
			$retval = $config_vars['other']['salt'];
		} else {
			$retval = 'ttsalt03198238';
		}

		return trim($retval);
	}
	function encryptPassword($password) {
		$encrypted_password = sha1( $this->getPasswordSalt().$password );

		return $encrypted_password;
	}
	function checkPassword($password, $check_password_policy = TRUE ) {
		global $config_vars;

		$password = html_entity_decode( $password );

		//Check if LDAP is enabled
		$ldap_authentication_type_id = 0;
		if ( DEMO_MODE != TRUE AND function_exists('ldap_connect') AND !isset($config_vars['other']['enable_ldap']) OR ( isset($config_vars['other']['enable_ldap']) AND $config_vars['other']['enable_ldap'] == TRUE ) ) {
			//Check company object to make sure LDAP is enabled.
			$c_obj = $this->getCompanyObject();
			if ( is_object($this->getCompanyObject()) ) {
				$ldap_authentication_type_id = $this->getCompanyObject()->getLDAPAuthenticationType();
				if ( $ldap_authentication_type_id > 0 ) {
					$ldap = new TTLDAP();
					$ldap->setHost( $this->getCompanyObject()->getLDAPHost() );
					$ldap->setPort( $this->getCompanyObject()->getLDAPPort() );
					$ldap->setBindUserName( $this->getCompanyObject()->getLDAPBindUserName() );
					$ldap->setBindPassword( $this->getCompanyObject()->getLDAPBindPassword() );
					$ldap->setBaseDN( $this->getCompanyObject()->getLDAPBaseDN() );
					$ldap->setBindAttribute( $this->getCompanyObject()->getLDAPBindAttribute() );
					$ldap->setUserFilter( $this->getCompanyObject()->getLDAPUserFilter() );
					$ldap->setLoginAttribute( $this->getCompanyObject()->getLDAPLoginAttribute() );
					if (  $ldap->authenticate( $this->getUserName(), $password ) === TRUE ) {
						return TRUE;
					} elseif ( $ldap_authentication_type_id == 1 ) {
						Debug::Text('LDAP authentication failed, falling back to local password...', __FILE__, __LINE__, __METHOD__,10);
						TTLog::addEntry( $this->getId(), 510, TTi18n::getText('LDAP Authentication failed, falling back to local password for username').': '. $this->getUserName() . TTi18n::getText('IP Address') .': '.$_SERVER['REMOTE_ADDR'], $this->getId(), $this->getTable() );
					}
					unset($ldap);
				} else {
					Debug::Text('LDAP authentication is not enabled...', __FILE__, __LINE__, __METHOD__,10);
				}
			}
		} else {
			Debug::Text('LDAP authentication disabled due to config or extension missing...', __FILE__, __LINE__, __METHOD__,10);
		}

		$password = $this->encryptPassword( trim(strtolower($password)) );

		//Don't check local TT passwords if LDAP Only authentication is enabled. Still accept override passwords though.
		if ( $ldap_authentication_type_id != 2 AND $password == $this->getPassword() ) {
			//If the passwords match, confirm that the password hasn't exceeded its maximum age.
			//Allow override passwords always.
			if ( $check_password_policy == TRUE AND $this->checkPasswordAge() == FALSE ) {
				Debug::Text('Password Policy: Password exceeds maximum age, denying access...', __FILE__, __LINE__, __METHOD__,10);
				return FALSE;
			} else {
				return TRUE; //Password accepted.
			}
		} elseif ( isset($config_vars['other']['override_password_prefix'])
						AND $config_vars['other']['override_password_prefix'] != '' ) {
			//Check override password
			if ( $password == $this->encryptPassword( trim( trim( strtolower($config_vars['other']['override_password_prefix']) ).substr($this->getUserName(),0,2) ) ) ) {
				TTLog::addEntry( $this->getId(), 510, TTi18n::getText('Override Password successful from IP Address').': '. $_SERVER['REMOTE_ADDR'], NULL, $this->getTable() );
				return TRUE;
			}
		}

		return FALSE;
	}
	function getPassword() {
		if ( isset($this->data['password']) ) {
			return $this->data['password'];
		}

		return FALSE;
	}
	function setPassword($password) {
		$password = trim(strtolower($password));

		if 	( 	$password != ''
				AND
				$this->Validator->isLength(		'password',
												$password,
												TTi18n::gettext('Incorrect password length'),
												4,
												64) ) {

			$update_password = TRUE;

			//When changing the password, we need to check if a Password Policy is defined.
			$c_obj = $this->getCompanyObject();
			if ( is_object( $c_obj ) AND $c_obj->getPasswordPolicyType() == 1 AND $this->getPermissionLevel() >= $c_obj->getPasswordMinimumPermissionLevel() AND $c_obj->getProductEdition() > 10 ) {
				Debug::Text('Password Policy: Minimum Length: '. $c_obj->getPasswordMinimumLength() .' Min. Strength: '. $c_obj->getPasswordMinimumStrength() .' ('.  Misc::getPasswordStrength( $password ) .') Age: '. $c_obj->getPasswordMinimumAge(), __FILE__, __LINE__, __METHOD__,10);

				if ( strlen( $password ) < $c_obj->getPasswordMinimumLength() ) {
					$update_password = FALSE;
					$this->Validator->isTrue(		'password',
													FALSE,
													TTi18n::gettext('Password is too short') );
				}

				if ( Misc::getPasswordStrength( $password ) <= $c_obj->getPasswordMinimumStrength() ) {
					$update_password = FALSE;
					$this->Validator->isTrue(		'password',
													FALSE,
													TTi18n::gettext('Password is too weak, add additional numbers or special characters') );
				}

				if ( $this->getPasswordUpdatedDate() != '' AND $this->getPasswordUpdatedDate() >= time()-($c_obj->getPasswordMinimumAge()*86400) ) {
					$update_password = FALSE;
					$this->Validator->isTrue(		'password',
													FALSE,
													TTi18n::gettext('Password must reach its minimum age before it can be changed again') );
				}

				if ( $this->getId() > 0 ) {
					$uilf = TTnew( 'UserIdentificationListFactory' );
					$uilf->getByUserIdAndTypeIdAndValue( $this->getId(), 5, $this->encryptPassword( $password ) );
					if ( $uilf->getRecordCount() > 0 ) {
						$update_password = FALSE;
						$this->Validator->isTrue(		'password',
														FALSE,
														TTi18n::gettext('Password has already been used in the past, please choose a new one') );
					}
					unset($uilf);
				}
			} else {
				//Debug::Text('Password Policy disabled or does not apply to this user.', __FILE__, __LINE__, __METHOD__,10);
			}

			if ( $update_password === TRUE ) {
				$this->data['password'] = $this->encryptPassword( $password );
				$this->setPasswordUpdatedDate( time() );
			}

			return TRUE;
		}

		return FALSE;
	}

	function checkPasswordAge() {
		$c_obj = $this->getCompanyObject();
		if ( is_object( $c_obj ) AND $c_obj->getPasswordPolicyType() == 1 AND $this->getPasswordUpdatedDate() > 0 AND $this->getPasswordUpdatedDate() < ( time()-($c_obj->getPasswordMaximumAge()*86400) ) AND $c_obj->getProductEdition() > 10 ) {
			Debug::Text('Password Policy: Password exceeds maximum age, denying access... Current Age: '. TTDate::getDays( (time()-$this->getPasswordUpdatedDate()) ) .' Maximum Age: '. $c_obj->getPasswordMaximumAge() .' days', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}
		return TRUE;
	}
	function getPasswordUpdatedDate() {
		if ( isset($this->data['password_updated_date']) ) {
			return $this->data['password_updated_date'];
		}

		return FALSE;
	}
	function setPasswordUpdatedDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'password_updated_date',
												$epoch,
												TTi18n::gettext('Password updated date is invalid')) ) {

			$this->data['password_updated_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function isUniquePhoneId($phone_id) {
		$ph = array(
					'phone_id' => $phone_id,
					);

		$query = 'select id from '. $this->getTable() .' where phone_id = ? and deleted = 0';
		$phone_id = $this->db->GetOne($query, $ph);
		Debug::Arr($phone_id,'Unique Phone ID:', __FILE__, __LINE__, __METHOD__,10);

		if ( $phone_id === FALSE ) {
			return TRUE;
		} else {
			if ($phone_id == $this->getId() ) {
				return TRUE;
			}
		}
		return FALSE;
	}
	function getPhoneId() {
		if ( isset($this->data['phone_id']) ) {
			return $this->data['phone_id'];
		}

		return FALSE;
	}
	function setPhoneId($phone_id) {
		$phone_id = trim($phone_id);

		if 	(
				$phone_id == ''
				OR
				(
					$this->Validator->isRegEx(		'phone_id',
													$phone_id,
													TTi18n::gettext('Quick Punch ID must be digits only'),
													$this->phoneid_validator_regex)
				AND
					$this->Validator->isLength(		'phone_id',
													$phone_id,
													TTi18n::gettext('Incorrect Quick Punch ID length'),
													4,
													8)
				AND
					$this->Validator->isTrue(		'phone_id',
													$this->isUniquePhoneId($phone_id),
													TTi18n::gettext('Quick Punch ID is already taken')
													)
				)
			) {

			$this->data['phone_id'] = $phone_id;

			return TRUE;
		}

		return FALSE;
	}

	function checkPhonePassword($password) {
		$password = trim($password);

		if ( $password == $this->getPhonePassword() ) {
			return TRUE;
		}

		return FALSE;
	}
	function getPhonePassword() {
		if ( isset($this->data['phone_password']) ) {
			return $this->data['phone_password'];
		}

		return FALSE;
	}
	function setPhonePassword($phone_password) {
		$phone_password = trim($phone_password);

		//Phone passwords are now displayed the administrators to make things easier.
		//NOTE: Phone passwords are used for passwords on the timeclock as well, and need to be able to be cleared sometimes.
		//Limit phone password to max of 9 digits so we don't overflow an integer on the timeclocks. (10 digits, but maxes out at 2billion)
		if 	(	$phone_password == ''
				OR (
				$this->Validator->isRegEx(		'phone_password',
												$phone_password,
												TTi18n::gettext('Quick Punch password must be digits only'),
												$this->phonepassword_validator_regex)
				AND
					$this->Validator->isLength(		'phone_password',
													$phone_password,
													TTi18n::gettext('Quick Punch password must be between 4 and 9 digits'),
													4,
													9) ) ) {

			$this->data['phone_password'] = $phone_password;

			return TRUE;
		}

		return FALSE;
	}

	//
	// MUST LEAVE iButton functions in until v3.0 of TimeTrex, so allow for upgrades.
	//
	function checkIButton($id) {
		$id = trim($id);

		$uilf = TTnew( 'UserIdentificationListFactory' );
		$uilf->getByUserIdAndTypeIdAndValue( $this->getId(), 10, $id );
		if ( $uilf->getRecordCount() == 1 ) {
			return TRUE;
		}

/*
		if ( $id == $this->getIButtonID() ) {
			return TRUE;
		}
*/
		return FALSE;
	}
	function isUniqueIButtonId($id) {
		$ph = array(
					'id' => $id,
					);

		$query = 'select id from '. $this->getTable() .' where ibutton_id = ? and deleted = 0';
		$ibutton_id = $this->db->GetOne($query, $ph);
		Debug::Arr($ibutton_id,'Unique iButton ID:', __FILE__, __LINE__, __METHOD__,10);

		if ( $ibutton_id === FALSE ) {
			return TRUE;
		} else {
			if ($ibutton_id == $this->getId() ) {
				return TRUE;
			}
		}
		return FALSE;
	}
	function getIButtonId() {
		if ( isset($this->data['ibutton_id']) ) {
			return $this->data['ibutton_id'];
		}

		return FALSE;
	}
	function setIButtonId($id) {
		$id = trim($id);

		if 	( $id == ''
				OR
				(
					$this->Validator->isLength(		'ibutton_id',
													$id,
													TTi18n::gettext('Incorrect iButton ID length'),
													14,
													64)
				AND
					$this->Validator->isTrue(		'ibutton_id',
													$this->isUniqueIButtonId($id),
													TTi18n::gettext('iButton ID is already taken')
													)
				)
			) {

			$this->data['ibutton_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//
	// MUST LEAVE Fingerprint functions in until v3.0 of TimeTrex, so allow for upgrades.
	//
	function getFingerPrint1() {
		if ( isset($this->data['finger_print_1']) ) {
			return $this->data['finger_print_1'];
		}

		return FALSE;
	}
	function setFingerPrint1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'finger_print_1',
														$value,
														TTi18n::gettext('Fingerprint 1 is too long'),
														1,
														32000)
			) {

			$this->data['finger_print_1'] = $value;

			$this->setFingerPrint1UpdatedDate( time() );
			return TRUE;
		}

		return FALSE;
	}
	function getFingerPrint1UpdatedDate() {
		if ( isset($this->data['finger_print_1_updated_date']) ) {
			return $this->data['finger_print_1_updated_date'];
		}
	}
	function setFingerPrint1UpdatedDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'finger_print_1_updated_date',
												$epoch,
												TTi18n::gettext('Finger print 1 updated date is invalid')) ) {

			$this->data['finger_print_1_updated_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getFingerPrint2() {
		if ( isset($this->data['finger_print_2']) ) {
			return $this->data['finger_print_2'];
		}

		return FALSE;
	}
	function setFingerPrint2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'finger_print_2',
														$value,
														TTi18n::gettext('Fingerprint 2 is too long'),
														1,
														32000)
			) {

			$this->data['finger_print_2'] = $value;

			$this->setFingerPrint2UpdatedDate( time() );
			return TRUE;
		}

		return FALSE;
	}
	function getFingerPrint2UpdatedDate() {
		if ( isset($this->data['finger_print_2_updated_date']) ) {
			return $this->data['finger_print_2_updated_date'];
		}
	}
	function setFingerPrint2UpdatedDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'finger_print_2_updated_date',
												$epoch,
												TTi18n::gettext('Finger print 2 updated date is invalid')) ) {

			$this->data['finger_print_2_updated_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getFingerPrint3() {
		if ( isset($this->data['finger_print_3']) ) {
			return $this->data['finger_print_3'];
		}

		return FALSE;
	}
	function setFingerPrint3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'finger_print_3',
														$value,
														TTi18n::gettext('Fingerprint 3 is too long'),
														1,
														32000)
			) {

			$this->data['finger_print_3'] = $value;

			$this->setFingerPrint3UpdatedDate( time() );
			return TRUE;
		}

		return FALSE;
	}
	function getFingerPrint3UpdatedDate() {
		if ( isset($this->data['finger_print_3_updated_date']) ) {
			return $this->data['finger_print_3_updated_date'];
		}
	}
	function setFingerPrint3UpdatedDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'finger_print_3_updated_date',
												$epoch,
												TTi18n::gettext('Finger print 3 updated date is invalid')) ) {

			$this->data['finger_print_3_updated_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}


	function getFingerPrint4() {
		if ( isset($this->data['finger_print_4']) ) {
			return $this->data['finger_print_4'];
		}

		return FALSE;
	}
	function setFingerPrint4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'finger_print_4',
														$value,
														TTi18n::gettext('Fingerprint 4 is too long'),
														1,
														32000)
			) {

			$this->data['finger_print_4'] = $value;

			$this->setFingerPrint4UpdatedDate( time() );
			return TRUE;
		}

		return FALSE;
	}
	function getFingerPrint4UpdatedDate() {
		if ( isset($this->data['finger_print_4_updated_date']) ) {
			return $this->data['finger_print_4_updated_date'];
		}
	}
	function setFingerPrint4UpdatedDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'finger_print_4_updated_date',
												$epoch,
												TTi18n::gettext('Finger print 4 updated date is invalid')) ) {

			$this->data['finger_print_4_updated_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	static function getNextAvailableEmployeeNumber( $company_id = NULL ) {
		global $current_company;

		if ( $company_id == '' ANd is_object($current_company) ) {
			$company_id = $current_company->getId();
		} elseif ( $company_id == '' AND isset($this) AND is_object($this) ) {
			$company_id = $this->getCompany();
		}

		$ulf = TTNew('UserListFactory');
		$ulf->getHighestEmployeeNumberByCompanyId( $company_id );
		if ( $ulf->getRecordCount() > 0 ) {
			Debug::Text('Highest Employee Number: '. $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__,10);
			if ( is_numeric( $ulf->getCurrent()->getEmployeeNumber() ) == TRUE ) {
				return $ulf->getCurrent()->getEmployeeNumber()+1;
			} else {
				Debug::Text('Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__,10);
				return NULL;
			}
		} else {
			return 1;
		}
	}

	function isUniqueEmployeeNumber($id) {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		if ( $id == 0 ) {
			return FALSE;
		}

		$ph = array(
					'manual_id' => $id,
					'company_id' =>  $this->getCompany(),
					);

		$query = 'select id from '. $this->getTable() .' where employee_number = ? AND company_id = ? AND deleted = 0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id,'Unique Employee Number: '. $id, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function checkEmployeeNumber($id) {
		$id = trim($id);

		//Use employee ID for now.
		//if ( $id == $this->getID() ) {
		if ( $id == $this->getEmployeeNumber() ) {
			return TRUE;
		}

		return FALSE;
	}
        /**
         * ARSP NOTE -->
         * I MODIFIED THIS ORIGINAL CODE FOR THUNDER AND NEON
         */
	function getEmployeeNumber() {
		if ( isset($this->data['employee_number']) AND $this->data['employee_number'] != '' ) {
			//return (int)$this->data['employee_number']; //ARSP NOTE --> I HIDE THIS CODE
                        return $this->data['employee_number'];
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE -->
         * I HIDE THIS ORIGINAL CODE FOR THUNDER AND NEON AND ADDED NEW EMPLOYEE NUMBER CODE
         * 
         */
        /*
	function setEmployeeNumber($value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );

		//Allow setting a blank employee number, so we can use Validate() to check employee number against the status_id
		//To allow terminated employees to have a blank employee number, but active ones always have a number.
		if (
				$value == ''
				OR (
					$this->Validator->isNumeric(	'employee_number',
													$value,
													TTi18n::gettext('Employee number must only be digits'))
					AND
					$this->Validator->isTrue(		'employee_number',
														$this->isUniqueEmployeeNumber($value),
														TTi18n::gettext('Employee number is already in use, please enter a different one'))
				)
												) {
			if ( $value != '' AND $value >= 0 ) {
				$value = (int)$value;
			}

			$this->data['employee_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}
        */
        
        
        
        /**
         * ARSP NOTE-->
         * I ADDED THIS CODE FOR THUNDER AND NEON
         * IN HERE DO NOT CHECK THE isUniqueEmployeeNumber() BCZ setEmployeeNumberOnly() FUNCTION ALREADY CHECKED
         */          
	function setEmployeeNumber($value) {
		//$value = $this->Validator->stripNonNumeric( trim($value) ); //ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON
                $value = trim($value);

		//Allow setting a blank employee number, so we can use Validate() to check employee number against the status_id
		//To allow terminated employees to have a blank employee number, but active ones always have a number.
		if (
//				$value == ''
//				OR (
//					$this->Validator->isNumeric(	'employee_number',
//													$value,
//													TTi18n::gettext('Employee number must only be digits'))
//					AND
//					$this->Validator->isTrue(		'employee_number',
//														$this->isUniqueEmployeeNumber($value),
//														TTi18n::gettext('Employee number is already in use, please enter a different one'))
//				)
                        $value != '' 
                        
												) {
//			if ( $value != '' AND $value >= 0 ) {
//				$value = (int)$value;
//			}

			$this->data['employee_number'] = $value;

			return TRUE;
		}

		return FALSE;
	}        
        
        
        
        /**
         * ARSP NOTE-->
         * I ADDED THIS CODE FOR THUNDER AND NEON
         */        
        function getEmployeeNumberOnly() {
		if ( isset($this->data['employee_number_only']) AND $this->data['employee_number_only'] != '' ) {
			return (int)$this->data['employee_number_only'];
		}

		return FALSE;
	}   
        
        /**
         * ARSP NOTE-->
         * I ADDED THIS CODE FOR THUNDER AND NEON
         */
        function setEmployeeNumberOnly($value, $default_branch_id) {
		$value = $this->Validator->stripNonNumeric( trim($value) );
                $default_branch_id = $this->Validator->stripNonNumeric( trim($default_branch_id) );
		//Allow setting a blank employee number, so we can use Validate() to check employee number against the status_id
		//To allow terminated employees to have a blank employee number, but active ones always have a number.
		if (
				$value == ''
				OR (
					$this->Validator->isNumeric(	'employee_number_only',
													$value,
													TTi18n::gettext('Employee number must only be digits'))
					AND
					$this->Validator->isTrue(		'employee_number_only',
														$this->isUniqueEmployeeNumberOnly($value, $default_branch_id),//ARSP NOTE --> I ADDED EXTRA FIELD FOR THUNDER & NEON
														TTi18n::gettext('Employee number is already in use, please enter a different one'))
				)
												) {
			if ( $value != '' AND $value >= 0 ) {
				$value = (int)$value;
			}

			$this->data['employee_number_only'] = $value;

			return TRUE;
		}

		return FALSE;
	}     
 
        /**
         * ARSP NOTE--> NOT USED
         * I ADDED THIS CODE FOR THUNDER AND NEON
         * I'M NOT USED THIS FUNCTION
         */        
        function isUniqueEmployeeNumberOnly_OLDFUNCTION($id, $default_branch_id) {
            

		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		if ( $id == 0 ) {
			return FALSE;
		}


		$ph = array(
					'manual_id' => $id,
					'company_id' =>  $this->getCompany(),                                        
					);

		$query = 'select id from '. $this->getTable() .' where employee_number_only = ? AND company_id = ?  AND deleted = 0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id,'Unique Employee Number Only: '. $id, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	} 
        
        
        /**
         * ARSP NOTE--> I ADDED THIS FUNCTION & MODIFIED SOME FIELD FOR 2ND TIME
         * I ADDED THIS CODE FOR THUNDER AND NEON
         */        
        function isUniqueEmployeeNumberOnly($id, $default_branch_id) {
            

		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		if ( $id == 0 ) {
			return FALSE;
		}


		$ph = array(
					'manual_id' => $id,
					'company_id' =>  $this->getCompany(),
                                        'default_branch_id' =>  $default_branch_id,
					);

		$query = 'select id from '. $this->getTable() .' where employee_number_only = ? AND company_id = ?  AND default_branch_id = ? AND deleted = 0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id,'Unique Employee Number Only: '. $id, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}     
	
	
	
	

        /**
         * ARSP NOTE-->
         * THIS ID IS FINGERPRINT MACHINE UNIQUE ID
         */        
        function getPunchMachineUserID() {
		if ( isset($this->data['punch_machine_user_id']) AND $this->data['punch_machine_user_id'] != '' ) {
			return (int)$this->data['punch_machine_user_id'];
		}

		return FALSE;
	}      
	

        /**
         * ARSP NOTE-->
         * THIS ID IS FINGERPRINT MACHINE UNIQUE ID
         */
        function setPunchMachineUserID($value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );
                //$default_branch_id = $this->Validator->stripNonNumeric( trim($default_branch_id) );

		//Allow setting a blank employee number, so we can use Validate() to check employee number against the status_id
		//To allow terminated employees to have a blank employee number, but active ones always have a number.
		if (
				$value == ''
				OR (
					$this->Validator->isNumeric(	'punch_machine_user_id',
													$value,
													TTi18n::gettext('Punch Machine User ID must only be digits'))
					AND
					$this->Validator->isTrue(		'punch_machine_user_id',
														$this->isPunchMachineUserID($value),//ARSP NOTE --> I ADDED EXTRA FIELD FOR THUNDER & NEON
														TTi18n::gettext('Punch Machine User ID is already in use, please enter a different one'))
				)
												) {
			if ( $value != '' AND $value >= 0 ) {
				$value = (int)$value;
			}

			$this->data['punch_machine_user_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}           
        
        /**
         * ARSP NOTE--> 
         * THIS ID IS FINGERPRINT MACHINE UNIQUE ID
         */        
        function isPunchMachineUserID($id) {
            

		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		if ( $id == 0 ) {
			return FALSE;
		}


		$ph = array(
					'punch_machine_user_id' => $id,
					'company_id' =>  $this->getCompany(),                                        
					);

		$query = 'select id from '. $this->getTable() .' where punch_machine_user_id = ? AND company_id = ? AND deleted = 0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id,'Unique punch_machine_user_id : '. $id, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}  
	

	//
	// MUST LEAVE RFID functions in until v3.0 of TimeTrex, so allow for upgrades.
	//
	function isUniqueRFID($id) {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$ph = array(
					'rf_id' => $id,
					'company_id' =>  $this->getCompany(),
					);

		$query = 'select id from '. $this->getTable() .' where rf_id = ? AND company_id = ? AND deleted = 0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id,'Unique RFID: '. $id, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_id === FALSE ) {
			return TRUE;
		} else {
			if ($user_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	
	
	
	function checkRFID($id) {
		$id = trim($id);

		$uilf = TTnew( 'UserIdentificationListFactory' );
		$uilf->getByUserIdAndTypeIdAndValue( $this->getId(), 40, $id );
		if ( $uilf->getRecordCount() == 1 ) {
			return TRUE;
		}
/*
		//Use employee ID for now.
		if ( $id == $this->getRFID() ) {
			return TRUE;
		}
*/
		return FALSE;
	}
	function getRFID() {
		if ( isset($this->data['rf_id']) ) {
			return (int)$this->data['rf_id'];
		}

		return FALSE;
	}
	function setRFID($value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );

		if (	$value == ''
				OR
				(
				$this->Validator->isNumeric(	'rf_id',
												$value,
												TTi18n::gettext('RFID must only be digits'))
				AND
					$this->Validator->isTrue(		'rf_id',
													$this->isUniqueRFID($value),
													TTi18n::gettext('RFID is already in use, please enter a different one'))
				) ) {
			$this->data['rf_id'] = $value;

			$this->setRFIDUpdatedDate( time() );
			return TRUE;
		}

		return FALSE;
	}
	function getRFIDUpdatedDate() {
		if ( isset($this->data['rf_id_updated_date']) ) {
			return $this->data['rf_id_updated_date'];
		}
	}
	function setRFIDUpdatedDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'rf_id_updated_date',
												$epoch,
												TTi18n::gettext('RFID updated date is invalid')) ) {

			$this->data['rf_id_updated_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getTitle() {
		if ( isset($this->data['title_id']) ) {
			return $this->data['title_id'];
		}

		return FALSE;
	}
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
	
	
        /*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDDR & NEON-
         */
		function getJobSkills() {
		if ( isset($this->data['job_skills']) ) {
			return $this->data['job_skills'];
		}

		return FALSE;
	}
        
        /*
         * ARSP NOTE --> I ADDED THIS COD EFOR THUNDER & NEON
         */
		function setJobSkills($value) {
		$value = trim($value);

		if ($value != '' ) {

			$this->data['job_skills'] = $value;

			return TRUE;
		}

		return FALSE;
	}   	

	function getDefaultBranch() {
		if ( isset($this->data['default_branch_id']) ) {
			return $this->data['default_branch_id'];
		}

		return FALSE;
	}
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

	function getDefaultDepartment() {
		if ( isset($this->data['default_department_id']) ) {
			return $this->data['default_department_id'];
		}

		return FALSE;
	}
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

	function getFullName($reverse = FALSE, $include_middle = TRUE ) {
		return Misc::getFullName($this->getFirstName(), $this->getMiddleInitial(), $this->getLastName(), $reverse, $include_middle);
	}
        
        function getFullNamefield()
        {
            if ( isset($this->data['full_name']) ) {
			return $this->data['full_name'];
		}

		return FALSE;
        }

	function getFirstName() {
		if ( isset($this->data['first_name']) ) {
			return $this->data['first_name'];
		}

		return FALSE;
	}
	function setFirstName($first_name) {
		$first_name = trim($first_name);

		if 	( /*	$this->Validator->isRegEx(		'first_name',
												$first_name,
												TTi18n::gettext('First name contains invalid characters'),
												$this->name_validator_regex)
				AND */
					$this->Validator->isLength(		'first_name',
													$first_name,
													TTi18n::gettext('First name is too short or too long'),
													0,
													50) ) {

			$this->data['first_name'] = $first_name;
			$this->setFirstNameMetaphone( $first_name );

			return TRUE;
		}

		return FALSE;
	}
        
        
        
        function setFullNameField($full_name) {
		$full_name = trim($full_name);

		if 	(	$this->Validator->isRegEx(		'full_name',
												$full_name,
												TTi18n::gettext('Full name contains invalid characters'),
												$this->name_validator_regex)
				 ) {

			$this->data['full_name'] = $full_name;
			

			return TRUE;
		}

		return FALSE;
	}
        
        
	function getFirstNameMetaphone() {
		if ( isset($this->data['first_name_metaphone']) ) {
			return $this->data['first_name_metaphone'];
		}

		return FALSE;
	}
	function setFirstNameMetaphone($first_name) {
		$first_name = metaphone( trim($first_name) );

		if 	(	$first_name != '' ) {

			$this->data['first_name_metaphone'] = $first_name;

			return TRUE;
		}

		return FALSE;
	}


        //FL ADDED ROSEN REQUIREMENTS 20160314
	function getNameInitial() {
            $fullname = ''; 
            if ( $this->getFirstName() != '' ) {
                $fullname .= $this->getFirstName().' ';
            }
            if ( $this->getMiddleName() != '' ) {
                $fullname .= $this->getMiddleName().' ';
            }
            if ( $this->getLastName() != '' ) {
                $fullname .= $this->getLastName();
            }
            
             $name_arr = explode(' ', $fullname);
             
            $name_initials = '';
            $i=0;
            if(!empty($name_arr)){
                foreach ($name_arr as $nm){
                    $i++;
                    if($i == count($name_arr)){
                        $name_initials .= $nm; 
                    }else{
                        $name_initials .= ucfirst($nm[0]).'. '; 
                    }
                }
            }else{
                return FALSE;
            }
                                                                                        
		return $name_initials;
	}
        
	function getMiddleInitial() {
		if ( $this->getMiddleName() != '' ) {
			$middle_name = $this->getMiddleName();
			return $middle_name[0];
		}

		return FALSE;
	}
	function getMiddleName() {
		if ( isset($this->data['middle_name']) ) {
			return $this->data['middle_name'];
		}

		return FALSE;
	}
        

        
	function setMiddleName($middle_name) {
		$middle_name = trim($middle_name);

		if 	(
				$middle_name == ''
				OR
				(
				$this->Validator->isRegEx(		'middle_name',
												$middle_name,
												TTi18n::gettext('Middle name contains invalid characters'),
												$this->name_validator_regex)
				AND
					$this->Validator->isLength(		'middle_name',
													$middle_name,
													TTi18n::gettext('Middle name is too short or too long'),
													1,
													50)
				)
			) {

			$this->data['middle_name'] = $middle_name;

			return TRUE;
		}


		return FALSE;
	}

	function getLastName() {
		if ( isset($this->data['last_name']) ) {
			return $this->data['last_name'];
		}

		return FALSE;
	}
	function setLastName($last_name) {
		$last_name = trim($last_name);

		if 	(/*	$this->Validator->isRegEx(		'last_name',
												$last_name,
												TTi18n::gettext('Last name contains invalid characters'),
												$this->name_validator_regex)
				AND */
					$this->Validator->isLength(		'last_name',
													$last_name,
													TTi18n::gettext('Last name is too short or too long'),
													2,
													50) ) {

			$this->data['last_name'] = $last_name;
			$this->setLastNameMetaphone( $last_name );

			return TRUE;
		}

		return FALSE;
	}
        
      function getCallingName() {
		if ( isset($this->data['calling_name']) ) {
			return $this->data['calling_name'];
		}

		return FALSE;
	}
        
        
        function setCallingName($calling_name)
        {
            $calling_name = trim($calling_name);

		if 	(
				$second_last_name == ''
				OR
				(
					$this->Validator->isRegEx(		'calling_name',
													$calling_name,
													TTi18n::gettext('Second last name contains invalid characters'),
													$this->name_validator_regex)
					
				)
			) {

			$this->data['calling_name'] = $calling_name;

			return TRUE;
		}

		return FALSE;
            
        }
        
        
        
          
             
      function getReligion() {
		if ( isset($this->data['religion']) ) {
			return $this->data['religion'];
		}

		return FALSE;
	}
        
        
        function setReligion($religion)
        {
            $religion = trim($religion);

		if 	(
				$this->Validator->inArrayKey(	'religion',
											$religion,
											TTi18n::gettext('Invalid religion'),
											$this->getOptions('religion') ) 
			) {

			$this->data['religion'] = $religion;

			return TRUE;
		}

		return FALSE;
            
        }
        
        
        
        
        
      function getNameWithInitials() {
		if ( isset($this->data['name_with_initials']) ) {
			return $this->data['name_with_initials'];
		}

		return FALSE;
	}
        
        
        function setNameWithInitials($name_with_initials)
        {
            $name_with_initials = trim($name_with_initials);

		if 	(
				$name_with_initials == ''
				OR
				(
					$this->Validator->isRegEx(		'name_with_initials',
													$name_with_initials,
													TTi18n::gettext('Second last name contains invalid characters'),
													$this->name_validator_regex)
					
				)
			) {

			$this->data['name_with_initials'] = $name_with_initials;

			return TRUE;
		}

		return FALSE;
            
        }
        
        
	function getLastNameMetaphone() {
		if ( isset($this->data['last_name_metaphone']) ) {
			return $this->data['last_name_metaphone'];
		}

		return FALSE;
	}
	function setLastNameMetaphone($last_name) {
		$last_name = metaphone( trim($last_name) );

		if 	( $last_name != '' ) {

			$this->data['last_name_metaphone'] = $last_name;

			return TRUE;
		}

		return FALSE;
	}

	function getSecondLastName() {
		if ( isset($this->data['second_last_name']) ) {
			return $this->data['second_last_name'];
		}

		return FALSE;
	}

	function setSecondLastName($second_last_name) {
		$last_name = trim($second_last_name);

		if 	(
				$second_last_name == ''
				OR
				(
					$this->Validator->isRegEx(		'second_last_name',
													$second_last_name,
													TTi18n::gettext('Second last name contains invalid characters'),
													$this->name_validator_regex)
					AND
						$this->Validator->isLength(		'second_last_name',
														$second_last_name,
														TTi18n::gettext('Second last name is too short or too long'),
														2,
														50)
				)
			) {

			$this->data['second_last_name'] = $second_last_name;

			return TRUE;
		}

		return FALSE;
	}

	function getSex() {
		if ( isset($this->data['sex_id']) ) {
			return $this->data['sex_id'];
		}

		return FALSE;
	}
	function setSex($sex) {
		$sex = trim($sex);

		if ( $this->Validator->inArrayKey(	'sex',
											$sex,
											TTi18n::gettext('Invalid gender'),
											$this->getOptions('sex') ) ) {

			$this->data['sex_id'] = $sex;

			return TRUE;
		}

		return FALSE;
	}
        
        
        
        
        
        
        
	function getNameTitle() {
		if ( isset($this->data['user_name_title_id']) ) {
			return $this->data['user_name_title_id'];
		}

		return FALSE;
	}
	function setNameTitle($title) {
		$title = trim($title);

		if ( $this->Validator->inArrayKey(	'user_name_title_id',
											$title,
											TTi18n::gettext('Title name invalied'),
											$this->getOptions('title') ) ) {

			$this->data['user_name_title_id'] = $title;

			return TRUE;
		}

		return FALSE;
	}
        
        
        
	function getMarital() {
		if ( isset($this->data['marital_id']) ) {
			return $this->data['marital_id'];
		}

		return FALSE;
	}
	function setMarital($marital) {
		$marital = trim($marital);

		if ( $this->Validator->inArrayKey(	'marital',
											$marital,
											TTi18n::gettext('Invalid marital status'),
											$this->getOptions('marital') ) ) {

			$this->data['marital_id'] = $marital;

			return TRUE;
		}

		return FALSE;
	}

	function getAddress1() {
		if ( isset($this->data['address1']) ) {
			return $this->data['address1'];
		}

		return FALSE;
	}
	function setAddress1($address1) {
		$address1 = trim($address1);

		if 	(
				$address1 == ''
				OR
				(
				$this->Validator->isRegEx(		'address1',
												$address1,
												TTi18n::gettext('Address1 contains invalid characters'),
												$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address1',
													$address1,
													TTi18n::gettext('Address1 is too short or too long'),
													2,
													250)
				)
				) {

			$this->data['address1'] = $address1;

			return TRUE;
		}

		return FALSE;
	}

	function getAddress2() {
		if ( isset($this->data['address2']) ) {
			return $this->data['address2'];
		}

		return FALSE;
	}
	function setAddress2($address2) {
		$address2 = trim($address2);

		if 	(	$address2 == ''
				OR
				(
					$this->Validator->isRegEx(		'address2',
													$address2,
													TTi18n::gettext('Address2 contains invalid characters'),
													$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address2',
													$address2,
													TTi18n::gettext('Address2 is too short or too long'),
													2,
													250) ) ) {

			$this->data['address2'] = $address2;

			return TRUE;
		}

		return FALSE;

	}
	
        
        
        
	function getAddress3() {
		if ( isset($this->data['address3']) ) {
			return $this->data['address3'];
		}

		return FALSE;
	}
	function setAddress3($address2) {
		$address2 = trim($address2);

		if 	(	$address2 == ''
				OR
				(
					$this->Validator->isRegEx(		'address3',
													$address2,
													TTi18n::gettext('Address2 contains invalid characters'),
													$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address3',
													$address2,
													TTi18n::gettext('Address2 is too short or too long'),
													2,
													250) ) ) {

			$this->data['address3'] = $address2;

			return TRUE;
		}

		return FALSE;

	}
	
	// ARSP EDIT --> ADD CODE FOR NIC
        function getNic() {
		if ( isset($this->data['nic']) ) {
			return $this->data['nic'];
		}

		return FALSE;
	}
        // ARSP EDIT --> ADD CODE FOR NIC
        function setNic($nic) {
		$nic = trim($nic);

		if 	($nic == ''
				OR
				(
					$this->Validator->isRegEx(		'nic',
													$nic,
													TTi18n::gettext('NIC contains invalid characters'),
													$this->nic_validator)
//				AND
//					$this->Validator->isLength(		'address2',
//													$address2,
//													TTi18n::gettext('Address2 is too short or too long'),
//													2,
//													250)
                                        ) ) {

			$this->data['nic'] = $nic;

			return TRUE;
		}

		return FALSE;

	}


	function getCity() {
		if ( isset($this->data['city']) ) {
			return $this->data['city'];
		}

		return FALSE;
	}
	function setCity($city) {
		$city = trim($city);

		if 	(
				$city == ''
				OR
				(
				$this->Validator->isRegEx(		'city',
												$city,
												TTi18n::gettext('City contains invalid characters'),
												$this->city_validator_regex)
				AND
					$this->Validator->isLength(		'city',
													$city,
													TTi18n::gettext('City name is too short or too long'),
													2,
													250)
				)
				) {

			$this->data['city'] = $city;

			return TRUE;
		}

		return FALSE;
	}

	function getCountry() {
		if ( isset($this->data['country']) ) {
			return $this->data['country'];
		}

		return FALSE;
	}
	function setCountry($country) {
		$country = trim($country);

		$cf = TTnew( 'CompanyFactory' );

		if ( $this->Validator->inArrayKey(		'country',
												$country,
												TTi18n::gettext('Invalid Country'),
												$cf->getOptions('country') ) ) {

			$this->data['country'] = $country;

			return TRUE;
		}

		return FALSE;
	}

	function getProvince() {
		if ( isset($this->data['province']) ) {
			return $this->data['province'];
		}

		return FALSE;
	}
	function setProvince($province) {
		$province = trim($province);

		//Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__,10);

		$cf = TTnew( 'CompanyFactory' );

		$options_arr = $cf->getOptions('province');
		if ( isset($options_arr[$this->getCountry()]) ) {
			$options = $options_arr[$this->getCountry()];
		} else {
			$options = array();
		}

		//If country isn't set yet, accept the value and re-validate on save.
		if ( $this->getCountry() == FALSE
				OR
				$this->Validator->inArrayKey(	'province',
												$province,
												TTi18n::gettext('Invalid Province/State'),
												$options ) ) {

			$this->data['province'] = $province;

			return TRUE;
		}

		return FALSE;
	}

	function getPostalCode() {
		if ( isset($this->data['postal_code']) ) {
			return $this->data['postal_code'];
		}

		return FALSE;
	}
	function setPostalCode($postal_code) {
		$postal_code = strtoupper( $this->Validator->stripSpaces($postal_code) );

		if 	(
				$postal_code == ''
				OR
				(
				$this->Validator->isPostalCode(		'postal_code',
													$postal_code,
													TTi18n::gettext('Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State'),
													$this->getCountry(), $this->getProvince() )
				AND
					$this->Validator->isLength(		'postal_code',
													$postal_code,
													TTi18n::gettext('Postal/ZIP Code is too short or too long'),
													1,
													10)
				)
				) {

			$this->data['postal_code'] = $postal_code;

			return TRUE;
		}

		return FALSE;
	}

	function getLongitude() {
		if ( isset($this->data['longitude']) ) {
			return (float)$this->data['longitude'];
		}

		return FALSE;
	}
	function setLongitude($value) {
		$value = trim((float)$value);

		if (	$value == 0
				OR
				$this->Validator->isFloat(	'longitude',
											$value,
											TTi18n::gettext('Longitude is invalid')
											) ) {
			$this->data['longitude'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLatitude() {
		if ( isset($this->data['latitude']) ) {
			return (float)$this->data['latitude'];
		}

		return FALSE;
	}
	function setLatitude($value) {
		$value = trim((float)$value);

		if (	$value == 0
				OR
				$this->Validator->isFloat(	'latitude',
											$value,
											TTi18n::gettext('Latitude is invalid')
											) ) {
			$this->data['latitude'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkPhone() {
		if ( isset($this->data['work_phone']) ) {
			return $this->data['work_phone'];
		}

		return FALSE;
	}
	function setWorkPhone($work_phone) {
		$work_phone = trim($work_phone);

		if 	(
				$work_phone == ''
				OR
				$this->Validator->isPhoneNumber(		'work_phone',
														$work_phone,
														TTi18n::gettext('Work phone number is invalid')) ) {

			$this->data['work_phone'] = $work_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkPhoneExt() {
		if ( isset($this->data['work_phone_ext']) ) {
			return $this->data['work_phone_ext'];
		}

		return FALSE;
	}
	function setWorkPhoneExt($work_phone_ext) {
		$work_phone_ext = $this->Validator->stripNonNumeric( trim($work_phone_ext) );

		if ( 	$work_phone_ext == ''
				OR $this->Validator->isLength(		'work_phone_ext',
													$work_phone_ext,
													TTi18n::gettext('Work phone number extension is too short or too long'),
													2,
													10) ) {

			$this->data['work_phone_ext'] = $work_phone_ext;

			return TRUE;
		}

		return FALSE;

	}

	function getHomePhone() {
		if ( isset($this->data['home_phone']) ) {
			return $this->data['home_phone'];
		}

		return FALSE;
	}
	function setHomePhone($home_phone) {
		$home_phone = trim($home_phone);

		if 	(	$home_phone == ''
				OR
				$this->Validator->isPhoneNumber(		'home_phone',
														$home_phone,
														TTi18n::gettext('Home phone number is invalid')) ) {

			$this->data['home_phone'] = $home_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getMobilePhone() {
		if ( isset($this->data['mobile_phone']) ) {
			return $this->data['mobile_phone'];
		}

		return FALSE;
	}
	function setMobilePhone($mobile_phone) {
		$mobile_phone = trim($mobile_phone);

		if 	(	$mobile_phone == ''
					OR $this->Validator->isPhoneNumber(	'mobile_phone',
															$mobile_phone,
															TTi18n::gettext('Mobile phone number is invalid')) ) {

			$this->data['mobile_phone'] = $mobile_phone;

			return TRUE;
		}

		return FALSE;
	}


        /*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */
	function getImmediateContactNo() {
		if ( isset($this->data['immediate_contact_no']) ) {
			return $this->data['immediate_contact_no'];
		}

		return FALSE;
	}
        
        /*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */        
	function setImmediateContactNo($phone) {
		$phone = trim($phone);

		if 	(
				$phone == ''
				OR
				$this->Validator->isPhoneNumber(		'immediate_contact_no',
														$phone,
														TTi18n::gettext('Immediate contact phone number is invalid')) ) {

			$this->data['immediate_contact_no'] = $phone;

			return TRUE;
		}

		return FALSE;
	}        
        
        /*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */ 
	function getImmediateContactPerson() {
		if ( isset($this->data['immediate_contact_person']) ) {
			return $this->data['immediate_contact_person'];
		}

		return FALSE;
	}
        
        /*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */     
	function setImmediateContactPerson($name) {
		$name = trim($name);

		if 	(
				$name == ''
				OR
				(
				$this->Validator->isRegEx(		'immediate_contact_person',
												$name,
												TTi18n::gettext('Immediate contact person contains invalid characters'),
												$this->name_validator_regex)
				AND
					$this->Validator->isLength(		'immediate_contact_person',
													$name,
													TTi18n::gettext('Immediate contact person is too short or too long'),
													1,
													50)
				)
			) {

			$this->data['immediate_contact_person'] = $name;

			return TRUE;
		}


		return FALSE;
	}
        
        
        /*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */ 
	function getBondPeriod() {
		if ( isset($this->data['bond_period']) ) {
			return $this->data['bond_period'];
		}

		return FALSE;
	}
        
        /*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */     
	function setBondPeriod($name) {
		$name = trim($name);

		if 	(
				$name == ''
				OR
				(
					$this->Validator->isLength(		'bond_period',
													$name,
													TTi18n::gettext('Bond Period is too short or too long'),
													1,
													50)
				)
			) {

			$this->data['bond_period'] = $name;

			return TRUE;
		}


		return FALSE;
	}    
        
	function getFaxPhone() {
		if ( isset($this->data['fax_phone']) ) {
			return $this->data['fax_phone'];
		}

		return FALSE;
	}
	function setFaxPhone($fax_phone) {
		$fax_phone = trim($fax_phone);

		if 	(	$fax_phone == ''
					OR $this->Validator->isPhoneNumber(	'fax_phone',
															$fax_phone,
															TTi18n::gettext('Fax phone number is invalid')) ) {

			$this->data['fax_phone'] = $fax_phone;

			return TRUE;
		}

		return FALSE;
	}

        
       function getPersonalEmail() {
		if ( isset($this->data['personal_email']) ) {
			return $this->data['personal_email'];
		}

		return FALSE;
	}
        
        
	function setPersonalEmail($personal_email) {
		$personal_email = trim($personal_email);

		$error_threshold = 7; //No DNS checks.
		if ( DEPLOYMENT_ON_DEMAND === TRUE ) {
			$error_threshold = 0; //DNS checks on email address.
		}
		if 	(	$personal_email == ''
					OR $this->Validator->isEmailAdvanced(	'personal_email',
													$personal_email,
													TTi18n::gettext('Personal Email address is invalid'),
													$error_threshold ) ) {

			$this->data['personal_email'] = $personal_email;

			return TRUE;
		}

		return FALSE;
	}
        
        
	function getHomeEmail() {
		if ( isset($this->data['home_email']) ) {
			return $this->data['home_email'];
		}

		return FALSE;
	}
	function setHomeEmail($home_email) {
		$home_email = trim($home_email);

		$error_threshold = 7; //No DNS checks.
		if ( DEPLOYMENT_ON_DEMAND === TRUE ) {
			$error_threshold = 0; //DNS checks on email address.
		}
		if 	(	$home_email == ''
					OR $this->Validator->isEmailAdvanced(	'home_email',
													$home_email,
													TTi18n::gettext('Home Email address is invalid'),
													$error_threshold ) ) {

			$this->data['home_email'] = $home_email;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkEmail() {
		if ( isset($this->data['work_email']) ) {
			return $this->data['work_email'];
		}

		return FALSE;
	}
	function setWorkEmail($work_email) {
		$work_email = trim($work_email);

		$error_threshold = 7; //No DNS checks.
		if ( DEPLOYMENT_ON_DEMAND === TRUE ) {
			$error_threshold = 0; //DNS checks on email address.
		}
		if 	(	$work_email == ''
					OR	$this->Validator->isEmailAdvanced(	'work_email',
													$work_email,
													TTi18n::gettext('Office email address is invalid'),
													$error_threshold) ) {

			$this->data['work_email'] = $work_email;

			return TRUE;
		}

		return FALSE;
	}

	function getAge() {
		return round( TTDate::getYearDifference( $this->getBirthDate(), TTDate::getTime() ),1 );
	}
        
        
        
	function getRetirementDate() {
		if ( isset($this->data['retirement_date']) ) {
			return $this->data['retirement_date'];
		}

		return FALSE;
	}
	function setRetirementDate($epoch) {
		if 	( $epoch !== FALSE AND $epoch !== '' )
				 {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['retirement_date'] = $epoch ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

        
        
        
        

	function getBirthDate() {
		if ( isset($this->data['birth_date']) ) {
			return $this->data['birth_date'];
		}

		return FALSE;
	}
	function setBirthDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'birth_date',
												$epoch,
												TTi18n::gettext('Birth date is invalid, try specifying the year with four digits.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['birth_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

	function getHireDate() {
		if ( isset($this->data['hire_date']) ) {
			return $this->data['hire_date'];
		}

		return FALSE;
	}
	function setHireDate($epoch) {
		//( $epoch !== FALSE AND $epoch == '' ) //Check for strict FALSE causes data from UserDefault to fail if its not set.
		if 	(	( $epoch == '' )
				OR
				$this->Validator->isDate(		'hire_date',
												$epoch,
												TTi18n::gettext('Appointment Date is invalid')) ) {

			//Use the beginning of the day epoch, so accrual policies that apply on the hired date still work.
			$this->data['hire_date'] = TTDate::getBeginDayEpoch( $epoch );

			return TRUE;
		}

		return FALSE;
	}

	function getTerminationDate() {
		if ( isset($this->data['termination_date']) ) {
			return $this->data['termination_date'];
		}

		return FALSE;
	}
	function setTerminationDate($epoch) {
		if 	(	( $epoch == '' )
				OR
				$this->Validator->isDate(		'termination_date',
												$epoch,
												TTi18n::gettext('Termination date is invalid')) ) {

			if ( $epoch == '' ) {
				$epoch = NULL; //Force to NULL if no termination date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
			}
			$this->data['termination_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

        
        
        function getConfiremedDate() {
		if ( isset($this->data['confirmed_date']) ) {
			return $this->data['confirmed_date'];
		}

		return FALSE;
	}
       
        /**
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */        
	function setConfiremedDate($epoch) {
		if 	(	( $epoch == '' )
				OR
				$this->Validator->isDate(		'confirmed_date',
												$epoch,
												TTi18n::gettext('Confirmed date is invalid')) ) {

			if ( $epoch == '' ) {
				$epoch = NULL; //Force to NULL if no termination date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
			}
			$this->data['confirmed_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}
        
        
        /**
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */
	function getResignDate() {
		if ( isset($this->data['resign_date']) ) {
			return $this->data['resign_date'];
		}

		return FALSE;
	}
       
        /**
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         */        
	function setResignDate($epoch) {
		if 	(	( $epoch == '' )
				OR
				$this->Validator->isDate(		'resign_date',
												$epoch,
												TTi18n::gettext('Resign date is invalid')) ) {

			if ( $epoch == '' ) {
				$epoch = NULL; //Force to NULL if no termination date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
			}
			$this->data['resign_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}
        
	function getCurrency() {
		if ( isset($this->data['currency_id']) ) {
			return $this->data['currency_id'];
		}

		return FALSE;
	}
	function setCurrency($id) {
		$id = trim($id);

		Debug::Text('Currency ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$culf = TTnew( 'CurrencyListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'currency_id',
														$culf->getByID($id),
														TTi18n::gettext('Invalid currency')
													) ) {

			$this->data['currency_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getSecureSIN( $sin = NULL ) {
		if ( $sin == '' ) {
			$sin = $this->getSIN();
		}
		if ( $sin != '' ) {
			//Grab the first 1, and last 3 digits.
			$first_four = substr( $sin, 0, 1 );
			$last_four = substr( $sin, -3 );

			$total = strlen($sin)-4;

			$retval = $first_four.str_repeat('X', $total).$last_four;

			return $retval;
		}

		return FALSE;
	}
	function getSIN() {
		if ( isset($this->data['sin']) ) {
			return $this->data['sin'];
		}

		return FALSE;
	}
	function setSIN($sin) {
		//If *'s are in the SIN number, skip setting it
		//This allows them to change other data without seeing the SIN number.
		if ( stripos( $sin, 'X') !== FALSE  ) {
			return FALSE;
		}

		$sin = $this->Validator->stripNonNumeric( trim($sin) );

		if 	(
				$sin == ''
				OR
				$this->Validator->isLength(		'sin',
												$sin,
												TTi18n::gettext('SIN is invalid'),
												6,
												20)
				) {

			$this->data['sin'] = $sin;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID1() {
		if ( isset($this->data['other_id1']) ) {
			return $this->data['other_id1'];
		}

		return FALSE;
	}
	function setOtherID1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id1',
											$value,
											TTi18n::gettext('Other ID 1 is invalid'),
											1,255) ) {

			$this->data['other_id1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID2() {
		if ( isset($this->data['other_id2']) ) {
			return $this->data['other_id2'];
		}

		return FALSE;
	}
	function setOtherID2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id2',
											$value,
											TTi18n::gettext('Other ID 2 is invalid'),
											1,255) ) {

			$this->data['other_id2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID3() {
		if ( isset($this->data['other_id3']) ) {
			return $this->data['other_id3'];
		}

		return FALSE;
	}
	function setOtherID3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id3',
											$value,
											TTi18n::gettext('Other ID 3 is invalid'),
											1,255) ) {

			$this->data['other_id3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID4() {
		if ( isset($this->data['other_id4']) ) {
			return $this->data['other_id4'];
		}

		return FALSE;
	}
	function setOtherID4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id4',
											$value,
											TTi18n::gettext('Other ID 4 is invalid'),
											1,255) ) {

			$this->data['other_id4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID5() {
		if ( isset($this->data['other_id5']) ) {
			return $this->data['other_id5'];
		}

		return FALSE;
	}
	function setOtherID5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id5',
											$value,
											TTi18n::gettext('Other ID 5 is invalid'),
											1,255) ) {

			$this->data['other_id5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}

		return FALSE;
	}
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
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDRER & NEON
         */
	function getHireNote() {
		if ( isset($this->data['hire_note']) ) {
			return $this->data['hire_note'];
		}

		return FALSE;
	}        
        /**
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDRER & NEON
         */        
	function setHireNote($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'hire_note',
														$value,
														TTi18n::gettext('Hire Note is too long'),
														1,
														2048)
			) {

			$this->data['hire_note'] = $value;

			return FALSE;
		}

		return FALSE;
	}     
        
        
        
        
        function getOfficeMobile() {
		if ( isset($this->data['office_mobile']) ) {
			return $this->data['office_mobile'];
		}

		return FALSE;
	}
	function setOfficeMobile($work_phone) {
		$work_phone = trim($work_phone);

		if 	(
				$work_phone == ''
				OR
				$this->Validator->isPhoneNumber(		'office_mobile',
														$work_phone,
														TTi18n::gettext('Office mobile number is invalid')) ) {

			$this->data['office_mobile'] = $work_phone;

			return TRUE;
		}

		return FALSE;
	}
        
        /**
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDRER & NEON
         */        
	function getTerminationNote() {
		if ( isset($this->data['termination_note']) ) {
			return $this->data['termination_note'];
		}

		return FALSE;
	}        
        /**
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDRER & NEON
         */        
	function setTerminationNote($value) {
		$value = trim($value);

		if (	$value == ''
				OR
						$this->Validator->isLength(		'termination_note',
														$value,
														TTi18n::gettext('Termination Note is too long'),
														1,
														2048)
			) {

			$this->data['termination_note'] = $value;

			return FALSE;
		}

		return FALSE;
	}  	

	function checkPasswordResetKey($key) {
		if ( $this->getPasswordResetDate() != ''
				AND $this->getPasswordResetDate() > (time() - 86400)
				AND $this->getPasswordResetKey() == $key ) {

			return TRUE;
		}

		return FALSE;
	}

	function sendPasswordResetEmail() {
		global $config_vars;

		if ( $this->getHomeEmail() != FALSE
				OR $this->getWorkEmail() != FALSE ) {

			if ( $this->getWorkEmail() != FALSE ) {
				$primary_email = $this->getWorkEmail();
				if ( $this->getHomeEmail() != FALSE ) {
					$secondary_email = $this->getHomeEmail();
				} else {
					$secondary_email = NULL;
				}
			} else {
				$primary_email = $this->getHomeEmail();
				$secondary_email = NULL;
			}

			$this->setPasswordResetKey( md5( uniqid() ) );
			$this->setPasswordResetDate( time() );
			$this->Save(FALSE);

			if ( $config_vars['other']['force_ssl'] == 1 ) {
				$protocol = 'https';
			} else {
				$protocol = 'http';
			}

			$subject = TTi18n::gettext('Password Reset requested at '). TTDate::getDate('DATE+TIME', time() ) .' '. TTi18n::gettext('from') .' '. $_SERVER['REMOTE_ADDR'];

			$body = '<html><body>';
			$body .= TTi18n::gettext('If you did not request your password to be reset, you may ignore this email.');
			$body .= '<br><br>';
			$body .= TTi18n::gettext('If you did request the password for') .' '. $this->getUserName() .' '. TTi18n::gettext('to be reset,');
			$body .= TTi18n::gettext('please') .' <a href="'.$protocol .'://'.Misc::getHostName().Environment::getBaseURL() .'ForgotPassword.php?action:password_reset=null&key='. $this->getPasswordResetKey().'">'. TTi18n::gettext('click here') .'</a>';
			$body .= '</body></html>';

			TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Employee Password Reset By').': '. $_SERVER['REMOTE_ADDR'] .' '. TTi18n::getText('Key').': '. $this->getPasswordResetKey(), NULL, $this->getTable() );

			$headers = array(
								'From'    => '"'. APPLICATION_NAME .' - '. TTi18n::gettext('Password Reset') .'"<DoNotReply@'. Misc::getHostName( FALSE ) .'>',
								'Subject' => $subject,
								'Cc'	  => $secondary_email,
							 );

			$mail = new TTMail();
			$mail->setTo( $primary_email );
			$mail->setHeaders( $headers );

			@$mail->getMIMEObject()->setHTMLBody($body);

			$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
			$retval = $mail->Send();

			return $retval;
		}

		return FALSE;
	}

	function getPasswordResetKey() {
		if ( isset($this->data['password_reset_key']) ) {
			return $this->data['password_reset_key'];
		}

		return FALSE;
	}
	function setPasswordResetKey($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'password_reset_key',
											$value,
											TTi18n::gettext('Password reset key is invalid'),
											1,255) ) {

			$this->data['password_reset_key'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordResetDate() {
		if ( isset($this->data['password_reset_date']) ) {
			return $this->data['password_reset_date'];
		}
	}
	function setPasswordResetDate($epoch) {
		if ( empty($epoch) ) {
			$epoch = NULL;
		}

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'password_reset_date',
												$epoch,
												TTi18n::gettext('Password reset date is invalid')) ) {

			$this->data['password_reset_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['tags']) ) {
			return $this->tmp_data['tags'];
		} elseif ( $this->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 200, $this->getID() );
		}

		return FALSE;
	}
	function setTag( $tags ) {
		$tags = trim($tags);

		//Save the tags in temporary memory to be committed in postSave()
		$this->tmp_data['tags'] = $tags;

		return TRUE;
	}

	function isInformationComplete() {
		//Make sure the users information is all complete.
		//No longer check for SIN, as employees can't change it anyways.
		//Don't check for postal code, as some countries don't have that.
		if ( $this->getAddress1() == ''
				OR $this->getCity() == ''
				OR $this->getHomePhone() == '' ) {
			Debug::text('User Information is NOT Complete: ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		Debug::text('User Information is Complete: ', __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

        /**
         * ARSP NOTE --> I HIDE THIS ORIGINAL CODE FOR THUNDER & NEON
         * 
         */
        /*
	function Validate() {
		//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.
		if ( $this->getUserName() == '' ) {
			$this->Validator->isTrue(		'user_name',
											FALSE,
											TTi18n::gettext('User name not specified'));
		}

		//Re-validate the province just in case the country was set AFTER the province.
		$this->setProvince( $this->getProvince() );

		if ( $this->getCompany() == FALSE ) {
			$this->Validator->isTrue(		'company',
											FALSE,
											TTi18n::gettext('Company is invalid'));
		}

		if ( $this->getCurrency() == FALSE ) {
			$this->Validator->isTrue(		'currency_id',
											FALSE,
											TTi18n::gettext('Invalid currency'));
		}

		//Need to require password on new employees as the database column is NOT NULL.
		//However when mass editing, no IDs are set so this always fails during the only validation phase.
		if ( $this->validate_only == FALSE AND $this->isNew() == TRUE AND ( $this->getPassword() == FALSE OR $this->getPassword() == '' ) ) {
			$this->Validator->isTrue(		'password',
											FALSE,
											TTi18n::gettext('Please specify a password'));
		}

		if ( $this->getEmployeeNumber() == FALSE AND $this->getStatus() == 10 ) {
			$this->Validator->isTrue(		'employee_number',
											FALSE,
											TTi18n::gettext('Employee number must be specified for ACTIVE employees') );
		}
																																												if ( $this->isNew() == TRUE ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); } }
		return TRUE;
	}         
         */
        
        
 
        /**
         * ARSP NOTE --> 
         * I MODIFIED THIS ORIGINAL CODE FOR THUNDER & NEON
         */        
        function Validate() {
		//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.
		if ( $this->getUserName() == '' ) {
			$this->Validator->isTrue(		'user_name',
											FALSE,
											TTi18n::gettext('User name not specified'));
		}

		//Re-validate the province just in case the country was set AFTER the province.
		$this->setProvince( $this->getProvince() );

		if ( $this->getCompany() == FALSE ) {
			$this->Validator->isTrue(		'company',
											FALSE,
											TTi18n::gettext('Company is invalid'));
		}

		if ( $this->getCurrency() == FALSE ) {
			$this->Validator->isTrue(		'currency_id',
											FALSE,
											TTi18n::gettext('Invalid currency'));
		}

		//Need to require password on new employees as the database column is NOT NULL.
		//However when mass editing, no IDs are set so this always fails during the only validation phase.
		if ( $this->validate_only == FALSE AND $this->isNew() == TRUE AND ( $this->getPassword() == FALSE OR $this->getPassword() == '' ) ) {
			$this->Validator->isTrue(		'password',
											FALSE,
											TTi18n::gettext('Please specify a password'));
		}

		if ( $this->getEmployeeNumberOnly() == FALSE AND $this->getStatus() == 10 ) {
			$this->Validator->isTrue(		'employee_number_only',
											FALSE,
											TTi18n::gettext('Employee number must be specified for ACTIVE employees') );
		}
                
                if ( $this->getDefaultBranch() == 0 AND $this->getStatus() == 10 ) {
			$this->Validator->isTrue(		'default_branch',
											FALSE,
											TTi18n::gettext('Default Branch must be specified for ACTIVE employees') );
		}                                
																																												if ( $this->isNew() == TRUE ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); } }
		return TRUE;
	}     

	function preSave() {
		if ( $this->getDefaultBranch() == FALSE ) {
			$this->setDefaultBranch(0);
		}
		if ( $this->getDefaultDepartment() == FALSE ) {
			$this->setDefaultDepartment(0);
		}

		if ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ); //Active
		}

		if ( $this->getSex() == FALSE ) {
			$this->setSex( 5 ); //UnSpecified
		}

		//Remember if this is a new user for postSave()
		if ( $this->isNew() ) {
			$this->is_new = TRUE;
		}

		return TRUE;
	}

	function postSave( $data_diff = NULL ) {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == FALSE AND $this->getPermissionControl() !== FALSE ) {
			Debug::text('Permission Group is set...', __FILE__, __LINE__, __METHOD__, 10);

			$pclf = TTnew( 'PermissionControlListFactory' );
			$pclf->getByCompanyIdAndUserID( $this->getCompany(), $this->getId() );
			if ( $pclf->getRecordCount() > 0 ) {
				Debug::text('Already assigned to a Permission Group...', __FILE__, __LINE__, __METHOD__, 10);

				$pc_obj = $pclf->getCurrent();

				if ( $pc_obj->getId() == $this->getPermissionControl() ) {
					$add_permission_control = FALSE;
				} else {
					Debug::text('Permission Group has changed...', __FILE__, __LINE__, __METHOD__, 10);

					//Remove user from current schedule.
					$pulf = TTnew( 'PermissionUserListFactory' );
					$pulf->getByPermissionControlIdAndUserID( $pc_obj->getId(), $this->getId() );
					Debug::text('Record Count: '. $pulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					if ( $pulf->getRecordCount() > 0 ) {
						foreach( $pulf as $pu_obj ) {
							Debug::text('Deleteing from Permission Group: '. $pu_obj->getPermissionControl(), __FILE__, __LINE__, __METHOD__, 10);
							$pu_obj->Delete();
						}
					}

					$add_permission_control = TRUE;
				}
			} else {
				Debug::text('NOT Already assigned to a Permission Group...', __FILE__, __LINE__, __METHOD__, 10);
				$add_permission_control = TRUE;
			}

			if ( $this->getPermissionControl() !== FALSE AND $add_permission_control == TRUE ) {
				Debug::text('Adding user to Permission Group...', __FILE__, __LINE__, __METHOD__, 10);

				//Add to new permission group
				$puf = TTnew( 'PermissionUserFactory' );
				$puf->setPermissionControl( $this->getPermissionControl() );
				$puf->setUser( $this->getID() );

				if ( $puf->isValid() ) {
					$puf->Save();

					//Clear permission class for this employee.
					$pf = TTnew( 'PermissionFactory' );
					$pf->clearCache( $this->getID(), $this->getCompany() );
				}
			}
			unset($add_permission_control);
		}

		if ( $this->getDeleted() == FALSE AND $this->getPayPeriodSchedule() !== FALSE ) {
			Debug::text('Pay Period Schedule is set: '. $this->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);

			$add_pay_period_schedule = FALSE;

			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			$ppslf->getByUserId( $this->getId() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$pps_obj = $ppslf->getCurrent();

				if ( $this->getPayPeriodSchedule() == $pps_obj->getId() ) {
					Debug::text('Already assigned to this Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10);
					$add_pay_period_schedule = FALSE;
				} else {
					Debug::text('Changing Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10);

					//Remove user from current schedule.
					$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
					$ppsulf->getByPayPeriodScheduleIdAndUserID( $pps_obj->getId(), $this->getId() );
					Debug::text('Record Count: '. $ppsulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					if ( $ppsulf->getRecordCount() > 0 ) {
						foreach( $ppsulf as $ppsu_obj ) {
							Debug::text('Deleteing from Pay Period Schedule: '. $ppsu_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);
							$ppsu_obj->Delete();
						}
					}
					$add_pay_period_schedule = TRUE;
				}
			} elseif ( $this->getPayPeriodSchedule() > 0 ) {
				Debug::text('Not assigned to ANY Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10);
				$add_pay_period_schedule = TRUE;
			}

			if ( $this->getPayPeriodSchedule() !== FALSE AND $add_pay_period_schedule == TRUE ) {
				//Add to new pay period schedule
				$ppsuf = TTnew( 'PayPeriodScheduleUserFactory' );
				$ppsuf->setPayPeriodSchedule( $this->getPayPeriodSchedule() );
				$ppsuf->setUser( $this->getID() );

				if ( $ppsuf->isValid() ) {
					$ppsuf->Save();
				}
			}
			unset($add_pay_period_schedule);
		}

		if ( $this->getDeleted() == FALSE AND $this->getPolicyGroup() !== FALSE ) {
			Debug::text('Policy Group is set...', __FILE__, __LINE__, __METHOD__, 10);

			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getByUserIds( $this->getId() );
			if ( $pglf->getRecordCount() > 0 ) {
				$pg_obj = $pglf->getCurrent();

				if ( $this->getPolicyGroup() == $pg_obj->getId() ) {
					Debug::text('Already assigned to this Policy Group...', __FILE__, __LINE__, __METHOD__, 10);
					$add_policy_group = FALSE;
				} else {
					Debug::text('Changing Policy Group...', __FILE__, __LINE__, __METHOD__, 10);

					//Remove user from current schedule.
					$pgulf = TTnew( 'PolicyGroupUserListFactory' );
					$pgulf->getByPolicyGroupIdAndUserId( $pg_obj->getId(), $this->getId() );
					Debug::text('Record Count: '. $pgulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					if ( $pgulf->getRecordCount() > 0 ) {
						foreach( $pgulf as $pgu_obj ) {
							Debug::text('Deleting from Policy Group: '. $pgu_obj->getPolicyGroup(), __FILE__, __LINE__, __METHOD__, 10);
							$pgu_obj->Delete();
						}
					}
					$add_policy_group = TRUE;
				}
			} else {
				Debug::text('Not assigned to ANY Policy Group...', __FILE__, __LINE__, __METHOD__, 10);
				$add_policy_group = TRUE;
			}

			if ( $this->getPolicyGroup() !== FALSE AND $add_policy_group == TRUE ) {
				//Add to new policy group
				$pguf = TTnew( 'PolicyGroupUserFactory' );
				$pguf->setPolicyGroup( $this->getPolicyGroup() );
				$pguf->setUser( $this->getID() );

				if ( $pguf->isValid() ) {
					$pguf->Save();
				}
			}
			unset($add_policy_group);
		}

		if ( $this->getDeleted() == FALSE AND $this->getHierarchyControl() !== FALSE ) {
			Debug::text('Hierarchies are set...', __FILE__, __LINE__, __METHOD__, 10);

			$hierarchy_control_data = array_unique( array_values( (array)$this->getHierarchyControl() ) );
			//Debug::Arr($hierarchy_control_data, 'Setting hierarchy control data...', __FILE__, __LINE__, __METHOD__, 10);

			if ( is_array( $hierarchy_control_data ) ) {
				$hclf = TTnew( 'HierarchyControlListFactory' );
				$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
				$existing_hierarchy_control_data = array_unique( array_values( (array)$hclf->getArrayByListFactory( $hclf, FALSE, TRUE, FALSE ) ) );
				//Debug::Arr($existing_hierarchy_control_data, 'Existing hierarchy control data...', __FILE__, __LINE__, __METHOD__, 10);

				$hierarchy_control_delete_diff = array_diff( $existing_hierarchy_control_data, $hierarchy_control_data );
				//Debug::Arr($hierarchy_control_delete_diff, 'Hierarchy control delete diff: ', __FILE__, __LINE__, __METHOD__, 10);

				//Remove user from existing hierarchy control
				if ( is_array($hierarchy_control_delete_diff) ) {
					foreach( $hierarchy_control_delete_diff as $hierarchy_control_id ) {
						if ( $hierarchy_control_id != 0 ) {
							$hulf = TTnew( 'HierarchyUserListFactory' );
							$hulf->getByHierarchyControlAndUserID( $hierarchy_control_id, $this->getID() );
							if ( $hulf->getRecordCount() > 0 ) {
								Debug::text('Deleting user from hierarchy control ID: '. $hierarchy_control_id, __FILE__, __LINE__, __METHOD__, 10);
								$hulf->getCurrent()->Delete();
							}
						}
					}
				}
				unset($hierarchy_control_delete_diff, $hulf, $hclf, $hierarchy_control_id);

				$hierarchy_control_add_diff = array_diff( $hierarchy_control_data, $existing_hierarchy_control_data  );
				//Debug::Arr($hierarchy_control_add_diff, 'Hierarchy control add diff: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( is_array($hierarchy_control_add_diff) ) {
					foreach( $hierarchy_control_add_diff as $hierarchy_control_id ) {
						Debug::text('Hierarchy data changed...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $hierarchy_control_id != 0 ) {
							$huf = TTnew( 'HierarchyUserFactory' );
							$huf->setHierarchyControl( $hierarchy_control_id );
							$huf->setUser( $this->getId() );
							if ( $huf->isValid() ) {
								Debug::text('Adding user to hierarchy control ID: '. $hierarchy_control_id, __FILE__, __LINE__, __METHOD__, 10);
								$huf->Save();
							}
						}
					}
				}
				unset($hierarchy_control_add, $huf, $hierarchy_control_id);
			}
		}

		if ( $this->getDeleted() == FALSE AND $this->getPasswordUpdatedDate() >= (time()-10) ) { //If the password was updated in the last 10 seconds.
			Debug::text('Password changed, saving it for historical purposes... Password: '. $this->getPassword(), __FILE__, __LINE__, __METHOD__, 10);

			$uif = TTnew( 'UserIdentificationFactory' );
			$uif->setUser( $this->getID() );
			$uif->setType( 5 ); //Password History
			$uif->setNumber( 0 );
			$uif->setValue( $this->getPassword() );
			if ( $uif->isValid() ) {
				$uif->Save();
			}
			unset($uif);
		}

		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 200, $this->getID(), $this->getTag() );

			if ( is_array($data_diff) AND ( isset($data_diff['address1']) OR isset($data_diff['address2']) OR isset($data_diff['city']) OR isset($data_diff['province']) OR isset($data_diff['country']) OR isset($data_diff['postal_code']) ) ) {
				//Run a separate custom query to clear the geocordinates. Do we really want to do this for so many objects though...
				Debug::text('Address has changed, clear geocordinates!', __FILE__, __LINE__, __METHOD__, 10);
				$query = 'UPDATE '. $this->getTable() .' SET longitude = NULL, latitude = NULL where id = ?';
				$this->db->Execute( $query, array( 'id' => $this->getID() ) );
			}
		}

		if ( isset($this->is_new) AND $this->is_new == TRUE ) {
			$udlf = TTnew( 'UserDefaultListFactory' );
			$udlf->getByCompanyId( $this->getCompany() );
			if ( $udlf->getRecordCount() > 0 ) {
				Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
				$udf_obj = $udlf->getCurrent();

				Debug::text('Inserting Default Deductions...', __FILE__, __LINE__, __METHOD__, 10);

				$company_deduction_ids = $udf_obj->getCompanyDeduction();
				if ( is_array($company_deduction_ids) AND count($company_deduction_ids) > 0 ) {
					foreach( $company_deduction_ids as $company_deduction_id ) {
						$udf = TTnew( 'UserDeductionFactory' );
						$udf->setUser( $this->getId() );
						$udf->setCompanyDeduction( $company_deduction_id );
						if ( $udf->isValid() ) {
							$udf->Save();
						}
					}
				}
				unset($company_deduction_ids, $company_deduction_id, $udf);

				Debug::text('Inserting Default Prefs...', __FILE__, __LINE__, __METHOD__, 10);
				$upf = TTnew( 'UserPreferenceFactory' );
				$upf->setUser( $this->getId() );
				$upf->setLanguage( $udf_obj->getLanguage() );
				$upf->setDateFormat( $udf_obj->getDateFormat() );
				$upf->setTimeFormat( $udf_obj->getTimeFormat() );
				$upf->setTimeUnitFormat( $udf_obj->getTimeUnitFormat() );

				$upf->setTimeZone( $upf->getLocationTimeZone( $this->getCountry(), $this->getProvince(), $this->getWorkPhone(), $this->getHomePhone(), $udf_obj->getTimeZone() ) );
				Debug::text('Time Zone: '. $upf->getTimeZone(), __FILE__, __LINE__, __METHOD__,9);

				$upf->setItemsPerPage( $udf_obj->getItemsPerPage() );
				$upf->setStartWeekDay( $udf_obj->getStartWeekDay() );
				$upf->setEnableEmailNotificationException( $udf_obj->getEnableEmailNotificationException() );
				$upf->setEnableEmailNotificationMessage( $udf_obj->getEnableEmailNotificationMessage() );
				$upf->setEnableEmailNotificationHome( $udf_obj->getEnableEmailNotificationHome() );

				if ( $upf->isValid() ) {
					$upf->Save();
				}
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			//Remove them from the authorization hierarchy, policy group, pay period schedule, stations, jobs, etc...
			//Delete any accruals for them as well.

			//Pay Period Schedule
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			$ppslf->getByUserId( $this->getId() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$pps_obj = $ppslf->getCurrent();

				//Remove user from current schedule.
				$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
				$ppsulf->getByPayPeriodScheduleIdAndUserID( $pps_obj->getId(), $this->getId() );
				Debug::text('Record Count: '. $ppsulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $ppsulf->getRecordCount() > 0 ) {
					foreach( $ppsulf as $ppsu_obj ) {
						Debug::text('Deleting from Pay Period Schedule: '. $ppsu_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);
						$ppsu_obj->Delete();
					}
				}
			}

			//Policy Group
			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getByUserIds( $this->getId() );
			if ( $pglf->getRecordCount() > 0 ) {
				$pg_obj = $pglf->getCurrent();

				$pgulf = TTnew( 'PolicyGroupUserListFactory' );
				$pgulf->getByPolicyGroupIdAndUserId( $pg_obj->getId(), $this->getId() );
				Debug::text('Record Count: '. $pgulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $pgulf->getRecordCount() > 0 ) {
					foreach( $pgulf as $pgu_obj ) {
						Debug::text('Deleteing from Policy Group: '. $pgu_obj->getPolicyGroup(), __FILE__, __LINE__, __METHOD__, 10);
						$pgu_obj->Delete();
					}
				}
			}

			//Hierarchy
			$hclf = TTnew( 'HierarchyControlListFactory' );
			$hclf->getByCompanyId( $this->getCompany() );
			if ( $hclf->getRecordCount() > 0 ) {
				foreach( $hclf as $hc_obj ) {
					$hf = TTnew( 'HierarchyListFactory' );
					$hf->setUser( $this->getID() );
					$hf->setHierarchyControl( $hc_obj->getId() );
					$hf->Delete();
				}
				$hf->removeCache( NULL, $hf->getTable(TRUE) ); //On delete we have to delete the entire group.
				unset($hf);
			}

			//Accrual balances
			$alf = TTnew( 'AccrualListFactory' );
			$alf->getByUserIdAndCompanyId( $this->getId(), $this->getCompany() );
			if ( $alf->getRecordCount()> 0 ) {
				foreach( $alf as $a_obj ) {
					$a_obj->setDeleted(TRUE);
					if ( $a_obj->isValid() ) {
						$a_obj->Save();
					}
				}
			}

			//Station employee critiera
			$siuf = TTnew( 'StationIncludeUserFactory' );
			$seuf = TTnew( 'StationExcludeUserFactory' );

			$query = 'delete from '. $siuf->getTable() .' where user_id = '. (int)$this->getId();
			$this->db->Execute($query);

			$query = 'delete from '. $seuf->getTable() .' where user_id = '. (int)$this->getId();
			$this->db->Execute($query);

			//Job employee criteria
			$cgmlf = TTnew( 'CompanyGenericMapListFactory' );
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), array(1040,1050), $this->getID() );
			if ( $cgmlf->getRecordCount() > 0 ) {
				foreach( $cgmlf as $cgm_obj ) {
					Debug::text('Deleteing from Company Generic Map: '. $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					$cgm_obj->Delete();
				}
			}
		}

		return TRUE;
	}

	function getMapURL() {
		return Misc::getMapURL( $this->getAddress1(), $this->getAddress2(), $this->getCity(), $this->getProvince(), $this->getPostalCode(), $this->getCountry() );
	}

	//Support setting created_by,updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'hire_date':
						case 'birth_date':
						case 'termination_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
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


	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		/*
		 $include_columns = array(
								'id' => TRUE,
								'company_id' => TRUE,
								...
								)

		*/

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'full_name':
							$data[$variable] = $this->getFullName(TRUE);
						case 'status':
						case 'sex':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'company':
						case 'title':
						case 'user_group':
						case 'currency':
						case 'default_branch':
						case 'default_department':
						case 'permission_control':
						case 'pay_period_schedule':
						case 'policy_group':
							$data[$variable] = $this->getColumn( $variable );
							break;
						//The below fields may be set if APISearch ListFactory is used to obtain the data originally,
						//but if it isn't, use the explicit function to get the data instead.
						case 'permission_control_id':
							$data[$variable] = $this->getColumn( $variable );
							if ( $data[$variable] == FALSE ) {
								$data[$variable] = $this->getPermissionControl();
							}
							break;
						case 'pay_period_schedule_id':
							$data[$variable] = $this->getColumn( $variable );
							if ( $data[$variable] == FALSE ) {
								$data[$variable] = $this->getPayPeriodSchedule();
							}
							break;
						case 'policy_group_id':
							$data[$variable] = $this->getColumn( $variable );
							if ( $data[$variable] == FALSE ) {
								$data[$variable] = $this->getPolicyGroup();
							}
							break;
						case 'hierarchy_control':
							$data[$variable] = $this->getHierarchyControl();
							break;
						case 'hierarchy_control_display':
							//These functions are slow to obtain, so make sure the column is requested explicitly before we include it.
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->getHierarchyControlDisplay();
							}
							break;
						case 'password': //Don't return password
							break;
						case 'sin':
							$data[$variable] = $this->getSecureSIN();
							break;
						case 'hire_date':
						case 'birth_date':
						case 'termination_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
				unset($function);
			}
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee').': '. $this->getFullName( FALSE, TRUE ) , NULL, $this->getTable(), $this );
	}
}
?>
