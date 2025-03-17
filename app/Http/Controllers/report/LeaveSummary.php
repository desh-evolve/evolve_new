<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/misc/arr_multisort.class.php');
require_once(Environment::getBasePath() .'classes/php_excel/PHPExcel.php');

extract	(FormVariables::GetVariables(
    array	(
                    'action',
                    'generic_data',
                    'filter_data'
                    ) ) );


if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_accrual_balance_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Leave  Summary Report')); // See index.php



if ( isset($filter_data['print_timesheet']) AND $filter_data['print_timesheet'] >= 1 ) {
	if ( !$permission->Check('punch','enabled')
			OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_own') OR $permission->Check('punch','view_child'))
			) {
		$permission->Redirect( FALSE ); //Redirect
	}
} else {
	if ( !$permission->Check('report','enabled')
			OR !$permission->Check('report','view_timesheet_summary') ) {
		$permission->Redirect( FALSE ); //Redirect
	}
}


URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
                    array(
                            'filter_data' => $filter_data
							//'sort_column' => $sort_column,
							//'sort_order' => $sort_order,
                            ) 
              );


$static_columns = array(			'-1000-full_name' => TTi18n::gettext('Full Name'),
									'-1002-employee_number' => TTi18n::gettext('Employee #'),
									'-1010-title' => TTi18n::gettext('Title'),
									'-1020-province' => TTi18n::gettext('Province/State'),
									'-1030-country' => TTi18n::gettext('Country'),
									'-1039-group' => TTi18n::gettext('Group'),
									'-1040-default_branch' => TTi18n::gettext('Default Branch'),
									'-1050-default_department' => TTi18n::gettext('Default Department'),
									);


$columns = array(
				'-1060-total_balance' => TTi18n::gettext('Total Balance'),
				);

$columns = Misc::prependArray( $static_columns, $columns);



$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = Misc::findSubmitButton();

//print_r($action);

switch ($action) {
    
    case 'export':
                  
        
                $start_dt = TTDate::parseDateTime($filter_data['start_date']);
                $start_dt = date("Y-m-d", $start_dt);
                    
                                //$pdf = new TimeReportHeaderFooter();
            $fileName = 'Leave Summery -' . $start_dt;


            $objPHPExcel = new PHPExcel();

            $objPHPExcel->getProperties()->setCreator("Me")->setLastModifiedBy("Me")->setTitle("Leave summery Sheet")->setSubject("Leave summery Sheet")->setDescription("Leave summery Sheet")->setKeywords("Excel Sheet")->setCategory("Me");

            $objPHPExcel->setActiveSheetIndex(0);
            
            
             
        	$utlf = new UserTitleListFactory();
		$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		$uglf = new UserGroupListFactory();
		$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

                $ulf = new UserListFactory();
                $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);

        if ($ulf->getRecordCount() > 0) {

            if (isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids') {
                unset($filter_data['start_date']);
                unset($filter_data['end_date']);
            } else {
                unset($filter_data['pay_period_ids']);
            }

            foreach ($ulf as $u_obj) {
                $filter_data['user_id'][] = $u_obj->getId();
            }

            $usercount = 0;
            
            $selected_date = '';

            foreach ($ulf as $u_obj) {

                $emploee_leave_array[$u_obj->getId()] = array();

                $filter_data['user_id'][] = $u_obj->getId();
                $user_id = $u_obj->getId();

                if ($filter_data['start_date'] == '') {
                    $leave['start_date'] = date("Y-m-d");
                } else {
                    $leave['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
                    $leave['start_date'] = date("Y-m-d", $leave['start_date']);
                }
                
                $selected_date = $leave['start_date'];

                $aplf = new AccrualPolicyListFactory();
                $aplf->getByCompanyIdAndTypeId($current_company->getId(), 20);

                $annual_entitle = 0;
                $annual_utilize = 0;
                $annual_balance = 0;
                $casual_entitle = 0;
                $casual_utilize = 0;
                $casual_balance = 0;
                
                foreach ($aplf as $ap_o) {

                    $ablf = new AccrualBalanceListFactory();
                    $ablf->getByUserIdAndCompanyIdandPolicyId($u_obj->getId(), $current_company->getId(), $ap_o->getId());
                    $ab_o = $ablf->getCurrent();
                                      
                    $alf = new AccrualListFactory();
                    $alf->getByCompanyIdAndUserIdAndAccrualPolicyIdAndStatus($current_company->getId(), $u_obj->getId(), $ap_o->getId(), 30);
                    $af = $alf->getCurrent();
                    
                    if(!$af->getTimeStamp()){
                        continue;
                    }                   
                    
                    $updated_date =  new DateTime(date('Y-m-d',$af->getTimeStamp()));
                    $selected  = new DateTime($selected_date);
                       
                    if($selected<$updated_date){
                        continue;
                    }
                    
                    $leave_name = $ap_o->getName();
                    
                    if ($leave_name == 'Annual Leave Accrual Policy') {
                        $annual_entitle = $af->getAmount();
                        $annual_utilize = $af->getAmount() - $ab_o->getBalance();
                        $annual_balance = $ab_o->getBalance();
                    }

                    if ($leave_name == 'Casual Leave Accrual Policy') {
                        $casual_entitle = $af->getAmount();
                        $casual_utilize = $af->getAmount() - $ab_o->getBalance();
                        $casual_balance = $ab_o->getBalance();
                    }
                }
                
                $leave['province'] = $u_obj->getProvince();
                $leave['country'] = $u_obj->getCountry();

                $leave['group'] = Option::getByKey($u_obj->getGroup(), $group_options, NULL);
                $leave['title'] = Option::getByKey($u_obj->getTitle(), $title_options, NULL);

                $leave['default_department'] = Option::getByKey($u_obj->getDefaultDepartment(), $department_options, NULL);

                $leave['data'][$user_id] = array(
                    'emp_id' => $u_obj->getPunchMachineUserID(),
                    'emp_name' => $u_obj->getFullNameById($user_id),
                    'department' => $u_obj->getDefaultDepartment(),
                    'designation' => $u_obj->getDefaultDepartment(),
                    'hire_date' => $u_obj->getHireDate(),
                    'annual_entitle' => $annual_entitle,
                    'annual_utilize' => $annual_utilize,
                    'annual_balance' => $annual_balance,
                    'casual_entitle' => $casual_entitle,
                    'casual_utilize' => $casual_utilize,
                    'casual_balance' => $casual_balance
                );

                $emploee_leave_array[$u_obj->getId()] = $leave;

                $usercount++;
                unset($leave);
            }


            if ($action == 'export') {

              
                

                $row_data_day_key = array();
                
                $objPHPExcel->getActiveSheet()
			->setCellValue('A1', 'Emp No')
			->setCellValue('B1', 'Name')
			->setCellValue('C1', 'Department')
			->setCellValue('D1', 'Designation')
			->setCellValue('E1', 'DOJ')
                
                	->setCellValue('G1', 'Annual Leave')
                        ->setCellValue('J1', 'Casual Leave');
                
                $objPHPExcel->getActiveSheet()
			->setCellValue('F2', 'Entitlement')
			->setCellValue('G2', 'Utilization')
			->setCellValue('H2', 'Balance')
                        
			->setCellValue('I2', 'Entitlement')
			->setCellValue('J2', 'Utilization')
                        ->setCellValue('K2', 'Balance');
                        
 

               
                $x = 0;
                $ii =4;

                foreach ($emploee_leave_array as $user => $data) {

                    $x++;
                    

                    foreach ($data['data'] as $leave_name => $leave_amount) {

                       

                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$ii, $leave_amount['emp_id']);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$ii, $leave_amount['emp_name']);
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$ii, $data['default_department']);
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$ii, $data['title']);
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$ii, date('Y-m-d', $leave_amount['hire_date']));
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$ii, (number_format(($leave_amount['annual_entitle'] / 28800), 2)));
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$ii, (number_format(($leave_amount['annual_utilize'] / 28800), 2)) );
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$ii, (number_format(($leave_amount['annual_balance'] / 28800), 2)) );
    
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$ii, (number_format(($leave_amount['casual_entitle'] / 28800), 2)));
                        $objPHPExcel->getActiveSheet()->setCellValue('J'.$ii, (number_format(($leave_amount['casual_utilize'] / 28800), 2)) );
                        $objPHPExcel->getActiveSheet()->setCellValue('K'.$ii, (number_format(($leave_amount['casual_balance'] / 28800), 2)) );
    

                        
                        
                    }
                     $ii++;
                }

                

               
            }
        }
       
            
            
            
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');

        
        
                    break;
    case 'display_report':
          
        
        	$utlf = new UserTitleListFactory();
		$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		$uglf = new UserGroupListFactory();
		$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

                $ulf = new UserListFactory();
                $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);

        if ($ulf->getRecordCount() > 0) {

            if (isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids') {
                unset($filter_data['start_date']);
                unset($filter_data['end_date']);
            } else {
                unset($filter_data['pay_period_ids']);
            }

            foreach ($ulf as $u_obj) {
                $filter_data['user_id'][] = $u_obj->getId();
            }

            $usercount = 0;
            
            $selected_date = '';

            foreach ($ulf as $u_obj) {

                $emploee_leave_array[$u_obj->getId()] = array();

                $filter_data['user_id'][] = $u_obj->getId();
                $user_id = $u_obj->getId();

                if ($filter_data['start_date'] == '') {
                    $leave['start_date'] = date("Y-m-d");
                } else {
                    $leave['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
                    $leave['start_date'] = date("Y-m-d", $leave['start_date']);
                }
                
                $selected_date = $leave['start_date'];

                $aplf = new AccrualPolicyListFactory();
                $aplf->getByCompanyIdAndTypeId($current_company->getId(), 20);

                $annual_entitle = 0;
                $annual_utilize = 0;
                $annual_balance = 0;
                $casual_entitle = 0;
                $casual_utilize = 0;
                $casual_balance = 0;
                
                foreach ($aplf as $ap_o) {

                    $ablf = new AccrualBalanceListFactory();
                    $ablf->getByUserIdAndCompanyIdandPolicyId($u_obj->getId(), $current_company->getId(), $ap_o->getId());
                    $ab_o = $ablf->getCurrent();
                                      
                    $alf = new AccrualListFactory();
                    $alf->getByCompanyIdAndUserIdAndAccrualPolicyIdAndStatus($current_company->getId(), $u_obj->getId(), $ap_o->getId(), 30);
                    $af = $alf->getCurrent();
                    
                    if(!$af->getTimeStamp()){
                        continue;
                    }                   
                    
                    $updated_date =  new DateTime(date('Y-m-d',$af->getTimeStamp()));
                    $selected  = new DateTime($selected_date);
                       
                    if($selected<$updated_date){
                        continue;
                    }
                    
                    $leave_name = $ap_o->getName();
                    
                    if ($leave_name == 'Annual Leave Accrual Policy') {
                        $annual_entitle = $af->getAmount();
                        $annual_utilize = $af->getAmount() - $ab_o->getBalance();
                        $annual_balance = $ab_o->getBalance();
                    }

                    if ($leave_name == 'Casual Leave Accrual Policy') {
                        $casual_entitle = $af->getAmount();
                        $casual_utilize = $af->getAmount() - $ab_o->getBalance();
                        $casual_balance = $ab_o->getBalance();
                    }
                }
                
                $leave['province'] = $u_obj->getProvince();
                $leave['country'] = $u_obj->getCountry();

                $leave['group'] = Option::getByKey($u_obj->getGroup(), $group_options, NULL);
                $leave['title'] = Option::getByKey($u_obj->getTitle(), $title_options, NULL);

                $leave['default_department'] = Option::getByKey($u_obj->getDefaultDepartment(), $department_options, NULL);

                $leave['data'][$user_id] = array(
                    'emp_id' => $u_obj->getPunchMachineUserID(),
                    'emp_name' => $u_obj->getFullNameById($user_id),
                    'department' => $u_obj->getDefaultDepartment(),
                    'designation' => $u_obj->getDefaultDepartment(),
                    'hire_date' => $u_obj->getHireDate(),
                    'annual_entitle' => $annual_entitle,
                    'annual_utilize' => $annual_utilize,
                    'annual_balance' => $annual_balance,
                    'casual_entitle' => $casual_entitle,
                    'casual_utilize' => $casual_utilize,
                    'casual_balance' => $casual_balance
                );

                $emploee_leave_array[$u_obj->getId()] = $leave;

                $usercount++;
                unset($leave);
            }


            if ($action == 'display_report') {

                $html = '';
                
                $_SESSION['header_data'] = array(
                    'image_path' => $current_company->getLogoFileName(),
                    'company_name' => $current_company->getName(),
                    'address1' => $current_company->getAddress1(),
                    'address2' => $current_company->getAddress2(),
                    'city' => $current_company->getCity(),
                    'province' => $current_company->getProvince(),
                    'postal_code' => $current_company->getPostalCode(),
                    'heading' => ' ',
                    'group_list' => '',
                    'department_list' => '',
                    'branch_list' => '',
//                    'remove_line'=> 'true',
                    'line_width' => 280,
                );

                $pdf = new TimeReportHeaderFooter();

                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 31, 20);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                $pdf->SetFont('', 'B', 6.5);

                // add a page
                $pdf->AddPage('L', 'mm', 'A4');

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                //Table border
                $pdf->setLineWidth(0.20);

                $row_data_day_key = array();
 
                $html = $html . '<table width="100%" border="0">';
                $html = $html . '<thead><tr align="left" valign="top">';
                $html = $html . '<td><font size="12"><strong>Employee Leave Summary Report - ' . $selected_date . '</strong></font></td>';
                $html = $html . '</tr>';
                $html = $html . '<tr align="right" valign="top">';
                $html = $html . '<td><strong>Tel :</strong> ' . $current_company->getWorkPhone() . ' <br /> <strong>Fax : </strong>' . $current_company->getFaxPhone() . '<br />  </td>';
                $html = $html . '</tr>';
                $html = $html . '<thead></table>';
                $html = $html . '<br/>';

                $html = $html . '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                                <thead><tr style="background-color:#CCCCCC;text-align:center;" >';

                $html = $html . '<td height="25" width = "6%">Emp No</td>';
                $html = $html . '<td width = "18%" align="center">Name</td>';
                $html = $html . '<td width = "10%" align="left">Department</td>';
                $html = $html . '<td width = "19%" align="left">Designation</td>';
                $html = $html . '<td width = "10%" align="left">DOJ</td>';
                $html = $html . '<td width = "21%" align="center" scope="col" colspan="3">Annual Leave</td>';
                $html = $html . '<td width = "21%" align="center" scope="col" colspan="3">Casual Leave</td>';
                $html = $html . '</tr>';

                $html = $html . '<tr style ="background-color:#CCCCCC;text-align:center;" nobr="true">';
                $html = $html . '<td width = "6%" height="25">&nbsp;</td>';
                $html = $html . '<td width = "18%" align="left">&nbsp;</td>';
                $html = $html . '<td width = "10%" align="left">&nbsp;</td>';
                $html = $html . '<td width = "19%" align="left">&nbsp;</td>';
                $html = $html . '<td width = "10%" align="left">&nbsp;</td>';
                $html = $html . '<td width = "7%" align="left">Entitlement</td>';
                $html = $html . '<td width = "7%" align="left">Utilization</td>';
                $html = $html . '<td width = "7%" align="left">Balance</td>';
                $html = $html . '<td width = "7%" align="left">Entitlement</td>';
                $html = $html . '<td width = "7%" align="left">Utilization</td>';
                $html = $html . '<td width = "7%" align="left">Balance</td>';
                $html = $html . '</tr></thead>';

                $html = $html . '<tbody>';

                $pdf->SetFont('', '', 9);
                $x = 0;

                foreach ($emploee_leave_array as $user => $data) {

                    $x++;

                    foreach ($data['data'] as $leave_name => $leave_amount) {

                        if ($x % 2 == 0) {
                            $html = $html . '<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                        } else {
                            $html = $html . '<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        }

                        $html = $html . '<td width = "6%" height="25">' . $leave_amount['emp_id'] . '</td>';
                        $html = $html . '<td width = "18%" align="left">' . $leave_amount['emp_name'] . '</td>';
                        $html = $html . '<td width = "10%" align="left">' . $data['default_department'] . '</td>';
                        $html = $html . '<td width = "19%" align="left">' . $data['title'] . '</td>';
                        $html = $html . '<td width = "10%" align="left">' . date('Y-m-d', $leave_amount['hire_date']) . '</td>';
                        $html = $html . '<td width = "7%" align="left">' . (number_format(($leave_amount['annual_entitle'] / 28800), 2)) . '</td>';
                        $html = $html . '<td width = "7%" align="left">' . (number_format(($leave_amount['annual_utilize'] / 28800), 2)) . '</td>';
                        $html = $html . '<td width = "7%" align="left">' . (number_format(($leave_amount['annual_balance'] / 28800), 2)) . '</td>';
                        $html = $html . '<td width = "7%" align="left">' . (number_format(($leave_amount['casual_entitle'] / 28800), 2)) . '</td>';
                        $html = $html . '<td width = "7%" align="left">' . (number_format(($leave_amount['casual_utilize'] / 28800), 2)) . '</td>';
                        $html = $html . '<td width = "7%" align="left">' . (number_format(($leave_amount['casual_balance'] / 28800), 2)) . '</td>';
                        $html = $html . '</tr>';
                    }

                }

                $html = $html . '</tbody>';
                $html = $html . '</table>';

                $pdf->writeHTML($html, true, false, true, false, '');

                unset($_SESSION['header_data']);

                $output = $pdf->Output('', 'S');

                if (isset($output) AND $output !== FALSE AND Debug::getVerbosity() < 11) {
                    Misc::FileDownloadHeader('LeaveSummary.pdf', 'application/pdf', strlen($output));
                    echo $output;
                    exit;
                } else {
                    echo TTi18n::gettext('ERROR: Employee Leave(s) not available!') . "<br>\n";
                    exit;
                }
            }
        }
       
        break;
       
    default:
         
            
		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);

			extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );
		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);
				//Default selections
				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );

				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.@array_shift(array_keys((array)$pay_period_options)) );
				$filter_data['start_date'] = $default_start_date;
				$filter_data['end_date'] = $default_end_date;
				$filter_data['group_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-date_stamp',
											'-1090-worked_time',
											'-1130-paid_time',
											'-1140-regular_time'
												) );

				$filter_data['primary_sort'] = '-1000-date_stamp';
				$filter_data['secondary_sort'] = '-1090-worked_time';
/*
				$filter_data['column_ids'] = array(
											'date_stamp',
											'worked_time',
											'paid_time',
											'regular_time'
												);
*/

			}
		}
            
          $filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL);

		$ulf = new UserListFactory();
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

		//Get include employee list.
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		$uglf = new UserGroupListFactory();
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		$filter_data['src_punch_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_branch_ids'], $branch_options );
		$filter_data['selected_punch_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		$filter_data['src_punch_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_department_ids'], $department_options );
		$filter_data['selected_punch_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_department_ids'], $department_options );

		//Get employee titles
		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get pay periods
		$pplf = new PayPeriodListFactory();
		$pplf->getByCompanyId( $current_company->getId() );
		$pay_period_options = Misc::prependArray( $all_array_option, $pplf->getArrayByListFactory( $pplf, FALSE, TRUE ) );
		$filter_data['src_pay_period_options'] = Misc::arrayDiffByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );
		$filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
		unset($filter_data['sort_options']['effective_date']);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();
                
             
            //FL ADDED FOR HIDE BUTTON
                $hidden_elements = Misc::prependArray( array( 'displayReport' => 'hidden', 'displayTimeSheet' => 'hidden', 'displayDetailedTimeSheet' => 'hidden', 'export' => '') );
                
                $smarty->assign('hidden_elements',$hidden_elements); // See index.php
                
                
            
            $saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
	    $generic_data['saved_report_options'] = $saved_report_options;
	    $smarty->assign_by_ref('generic_data', $generic_data);
                
            
            $smarty->assign_by_ref('filter_data', $filter_data);

            $smarty->assign_by_ref('ugdf', $ugdf);
                
            $smarty->display('report/LeaveSummary.tpl');

	    break;
            
}