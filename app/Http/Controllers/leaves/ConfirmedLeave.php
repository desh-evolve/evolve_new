<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');



$smarty->assign('title', TTi18n::gettext($title = 'Confiremed Leave'));


extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data',
                                                                                                'filter_data'
												) ) );


//$lrlf = TTnew( 'LeaveRequestListFactory' );

if(!isset($filter_data)){
   $filter_data = array();
}

if ( isset($filter_data['start_date']) && $filter_data['start_date'] !='' ) {
	//$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
        
        // $from_date =  DateTime::createFromFormat('j-M-y', $filter_data['start_date']);
         $from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['start_date']);
          $filter_data['start_date'] =  $from_date->format('Y-m-d');
}

if ( isset($filter_data['end_date']) && $filter_data['end_date'] !='' ) {
	//$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
    
    // $from_date =  DateTime::createFromFormat('j-M-y', $filter_data['start_date']);
         $from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['end_date']);
          $filter_data['end_date'] =  $from_date->format('Y-m-d');
}

//echo $filter_data['start_date'];
//exit();
$msg = "";
$lrlf = new LeaveRequestListFactory();

$action = Misc::findSubmitButton();
switch ($action) {
    
    case 'export':
                  break;
    case 'search':
                    
                             

                $lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);


 //echo $current_user->getRecordCount();
                    $leave= array();
                    if($lrlf->getRecordCount() >0){
    
   
                        foreach($lrlf as $lrf_obj) {

                            $leave['id'] = $lrf_obj->getId();
                            $leave['user'] = $lrf_obj->getUserObject()->getFullName();
                            $leave['user_id'] = $lrf_obj->getUser();
                            $leave['leave_name'] = $lrf_obj->getAccuralPolicyObject()->getName();
                            $leave['start_date'] = $lrf_obj->getLeaveFrom();
                            $leave['end_date'] = $lrf_obj->getLeaveTo();
                            $leave['amount'] = $lrf_obj->getAmount();
                            $leave['is_hr_approved'] = $lrf_obj->getHrApproved();

                            $data['leaves'][] =$leave;
                        }
                    }
 
         break;
     default :
        
         

$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);


 //echo $current_user->getRecordCount();
$leave= array();
if($lrlf->getRecordCount() >0){
    
   
foreach($lrlf as $lrf_obj) {

    $leave['id'] = $lrf_obj->getId();
    $leave['user'] = $lrf_obj->getUserObject()->getFullName();
    $leave['user_id'] = $lrf_obj->getUser();
    $leave['leave_name'] = $lrf_obj->getAccuralPolicyObject()->getName();
    $leave['start_date'] = $lrf_obj->getLeaveFrom();
    $leave['end_date'] = $lrf_obj->getLeaveTo();
    $leave['amount'] = $lrf_obj->getAmount();
    $leave['is_hr_approved'] = $lrf_obj->getHrApproved();
    
    $data['leaves'][] =$leave;
}
}
 
   
}

 // if( $filter_data['export_type'] ==){
//                                 echo '<pre>'; print_r($rows[0]); echo '<pre>';  die;
//                                 Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__,10);
//                                
//                             //   $tsdr= TTnew( 'TimesheetDetailReport' );//new code         
//                                
//                                $output =$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);//new code                               
//                                                                                              
//                                if ( Debug::getVerbosity() < 11 ) {                                    
//                                    Misc::FileDownloadHeader('DailyAttendanceReport.pdf', 'application/pdf', strlen($output));
//                                   echo $output;
//                                    exit;                           
//                                }  
                          //  }


$rows = 1;
	if ( $action == 'export' ) {
			if ( isset($rows) ) {
				$pdf_created_date = time();

				//Page width: 205mm
				$pdf = new TTPDF('P','mm','Letter');
				$pdf->setMargins(10,5);
				$pdf->SetAutoPageBreak(FALSE);
				$pdf->SetFont('freeserif','',10);

				$border = 0;

				//echo '<pre>'; print_r($rows); die;
                                $lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);
				//Create PDF TimeSheet for each employee.
//				foreach( $rows as $user_data ) {
                                $pdf->AddPage();
					

					$adjust_x = 10;
					$adjust_y = 10;

					//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

					$pdf->SetFont('','B',32);
					$pdf->Cell(200,15, TTi18n::gettext('Confiremed Leave Report') , $border, 0, 'C');
					$pdf->Ln();
					$pdf->SetFont('','B',12);
					$pdf->Cell(200,5, $current_company->getName() , $border, 0, 'C');
					$pdf->Ln(10);

					//$pdf->Rect( $pdf->getX(), $pdf->getY()-2, 200, 25 );

					

					$pdf->SetFont('','',10);
					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										//'date_stamp' => 20,
										'dow' => 55,
										'min_punch_time_stamp' => 50,
										'max_punch_time_stamp' => 30,
										'worked_time' => 30,
										'regular_time' => 29,
										'over_time' => 24,
										'paid_time' => 24,
										'absence_time' => 29,
										);


//					if ( isset($user_data['data']) AND is_array($user_data['data']) ) {
//						if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' )  {
//							//Fill in any missing days, only if they select by pay period.
//							$pplf = TTnew( 'PayPeriodListFactory' );
//							$pplf->getById( $user_data['pay_period_id'] );
//							if ( $pplf->getRecordCount() == 1 ) {
//								$pp_obj = $pplf->getCurrent();
//
//								for( $d=TTDate::getBeginDayEpoch($pp_obj->getStartDate()); $d <= $pp_obj->getEndDate(); $d+=86400) {
//									if ( Misc::inArrayByKeyAndValue($user_data['data'], 'date_stamp', TTDate::getBeginDayEpoch($d) ) == FALSE ) {
//										$user_data['data'][] = array(
//																'date_stamp' => TTDate::getBeginDayEpoch($d),
//																'min_punch_time' => NULL,
//																'max_punch_time' => NULL,
//																'worked_time' => NULL,
//																'regular_time' => NULL,
//																'over_time' => NULL,
//																'paid_time' => NULL,
//																'absence_time' => NULL
//															);
//
//									}
//								}
//							}
//						}
//						$user_data['data'] = Sort::Multisort( $user_data['data'], 'date_stamp', NULL, 'ASC' );
//                                                
//                                                    
//
//						$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time','leave_type' ), 0 );
//						$totals = array();
//						$totals = Misc::preSetArrayValues( $totals, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
//
//
//						/*echo '<pre>';
//						print_r($user_data['data']); */
//
						$i=1;
						$x=1;
						$y=1;
                                                $k=1;
						$max_i = count($user_data['data']);
                                                
                                                $line_h = 6;
								$cell_h_min = $cell_h_max = $line_h * 2;
                                                $pdf->Ln();
								$pdf->SetFont('','B',10);
								$pdf->setFillColor(220,220,220);
								$pdf->MultiCell( $column_widths['line'], $line_h, '#' , 1, 'C', 1, 0);
								//$pdf->MultiCell( $column_widths['date_stamp'], $line_h, TTi18n::gettext('Date') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['dow'], $line_h, TTi18n::gettext('Employee') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['min_punch_time_stamp'], $line_h, TTi18n::gettext('Leave Type') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['max_punch_time_stamp'], $line_h, TTi18n::gettext('Leave start date') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['worked_time'], $line_h, TTi18n::gettext('Leave End Date') , 1, 'C', 1, 0);
								/*$pdf->MultiCell( $column_widths['regular_time'], $line_h, TTi18n::gettext('Regular Time') , 1, 'C', 1, 0);*/
								$pdf->MultiCell( $column_widths['over_time'], $line_h, TTi18n::gettext('No Days') , 1, 'C', 1, 0);
								//$pdf->MultiCell( $column_widths['paid_time'], $line_h, TTi18n::gettext('Paid Time') , 1, 'C', 1, 0);
                                                             //   $pdf->MultiCell( $column_widths['absence_time'], $line_h, TTi18n::gettext('Remarks') , 1, 'C', 1, 0);
								//$pdf->MultiCell( $column_widths['absence_time'], $line_h, TTi18n::gettext('Absence Time') , 1, 'C', 1, 0);
								$pdf->Ln();
//                                                                
//                                                       
//                                                    
//                                                     
////						foreach( $user_data['data'] as $data) {
                                                    foreach($lrlf as $lrf_obj) {
////                                                    
////                                                    $pdf->AddPage();
////							//Show Header
////							if ( $i == 1 OR $x == 1 ) {
////								if ( $x == 1 ) {
////									//$pdf->Ln();
////								}
////
////
////								
////							}
////                                                        
////                                                        
////                                                        
////
////							$data = Misc::preSetArrayValues( $data, array('date_stamp', 'min_punch_time_stamp', 'max_punch_time_stamp', 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), '--' );
////
////							if ( $x % 2 == 0 ) {
////								$pdf->setFillColor(220,220,220);
////							} else {
////								$pdf->setFillColor(255,255,255);
////							}
////
////							if ( $data['date_stamp'] !== '' ) {
////
////								$hlf = TTnew('HolidayListFactory');
////								$hlf->getByPolicyGroupUserIdAndDate($user_data['user_id'], date('Y-m-d', $data['date_stamp']));
////								$hday_obj_arr = $hlf->getCurrent()->data;
////                                                                
////                                                                 $ch_date = new DateTime();
////                                                        $ch_date->setTimestamp($data['date_stamp']);
////                                                        $leave_date = $ch_date->format('Y-m-d');
////                                                        
////                                                        $lrlf = new LeaveRequestListFactory();
////                                                        $lrlf->checkUserHasLeaveForDay($user_id, $leave_date);
////                                                    
////                                                      
////                                                        
////                                                        if($lrlf->getRecordCount()>0){
////                                                            
////                                                        
////                                                            
////                                                           $lrf = $lrlf->getCurrent();
////                                                           $leave_method = $lrf->getOptions('leave_method');
////                                                           
////                                                           $data['leave_type']= $leave_method[$lrf->getLeaveMethod()];
////                                                            
////                                                        }
////                                                        else{
////                                                              $data['leave_type']='';
////                                                        }
////
////								if ( !empty($hday_obj_arr) ) {
////									$pdf->setFillColor(210,180,140);
////								}
////
								$pdf->SetFont('','',10);
								$pdf->Cell( $column_widths['line'], 6, $k , 1, 0, 'C', 1);
								//$pdf->Cell( $column_widths['date_stamp'], 6, TTDate::getDate('DATE', $lrf_obj->getId() ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['dow'], 6, $lrf_obj->getUserObject()->getFullName() , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['min_punch_time_stamp'], 6, $lrf_obj->getAccuralPolicyObject()->getName(), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['max_punch_time_stamp'], 6, $lrf_obj->getLeaveFrom(), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['worked_time'], 6, $lrf_obj->getLeaveTo(), 1, 0, 'C', 1);
								/*$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $data['regular_time'] ), 1, 0, 'C', 1);*/
								$pdf->Cell( $column_widths['over_time'], 6,  $lrf_obj->getAmount(), 1, 0, 'C', 1);
								//$pdf->Cell( $column_widths['paid_time'], 4,  TTDate::getTimeUnit( $data['paid_time'] ), 1, 0, 'C', 1);
								//$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $data['absence_time'] ), 1, 0, 'C', 1);
                                                              //  $pdf->Cell( $column_widths['absence_time'], 6, , 1, 0, 'C', 1);
								$pdf->Ln();
////$k++;
////								unset($hday_obj_arr);
////							}
////
////							$totals['worked_time'] += $data['worked_time'];
////							$totals['paid_time'] += $data['paid_time'];
////							$totals['absence_time'] += $data['absence_time'];
////							$totals['regular_time'] += $data['regular_time'];
////							$totals['over_time'] += $data['over_time'];
////
////							$week_totals['worked_time'] += $data['worked_time'];
////							$week_totals['paid_time'] += $data['paid_time'];
////							$week_totals['absence_time'] += $data['absence_time'];
////							$week_totals['regular_time'] += $data['regular_time'];
////							$week_totals['over_time'] += $data['over_time'];
////
////							if ( $x % 7 == 0 OR $i == $max_i ) {
////								//Show Week Total.
////								$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['min_punch_time_stamp']+$column_widths['max_punch_time_stamp'];
////								//$pdf->SetFont('','B',10);
////								//$pdf->Cell( $total_cell_width, 6, TTi18n::gettext('Week Total:').' ', 0, 0, 'R', 0);
////								//$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $week_totals['worked_time'] ) , 0, 0, 'C', 0);
////								/*$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $week_totals['regular_time'] ), 0, 0, 'C', 0);*/
////								//$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $week_totals['over_time'] ), 0, 0, 'C', 0);
////								//$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $week_totals['paid_time'] ), 0, 0, 'C', 0);
////								//$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $week_totals['absence_time'] ), 0, 0, 'C', 0);
////								//$pdf->Ln(2);
////
////								unset($week_totals);
////								$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
////
////								$x=0;
////								$y++;
////
////								//Force page break every 3 weeks.
////								if ( $y == 6 AND $i !== $max_i ) {
////									$pdf->AddPage();
////								}
////							}
////
////
////							$i++;
////							$x++;
////						}
//						unset($data);
//					}

					//die;

//					if ( isset($totals) AND is_array($totals) ) {
//						//Display overall totals.
//						$pdf->Ln(3);
//						$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['min_punch_time_stamp'];
//						//$pdf->SetFont('','B',10);
//						//$pdf->Cell( $total_cell_width, 6, '' , 0, 0, 'R', 0);
//						//$pdf->Cell( $column_widths['max_punch_time_stamp'], 6, TTi18n::gettext('Overall Total:').' ', 'T', 0, 'R', 0);
//						//$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $totals['worked_time'] ) , 'T', 0, 'C', 0);
//						/*$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $totals['regular_time'] ), 'T', 0, 'C', 0);*/
//						//$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $totals['over_time'] ), 'T', 0, 'C', 0);
//						//$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $totals['paid_time'] ), 'T', 0, 'C', 0);
//						//$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $totals['absence_time'] ), 'T', 0, 'C', 0);
//						$pdf->Ln();
//						unset($totals);
//					}
                                      /*
					$pdf->SetFont('','',10);
					$pdf->setFillColor(255,255,255);
					$pdf->Ln();

					//Signature lines
					$pdf->MultiCell(200,5, TTi18n::gettext('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '.TTi18n::gettext('worked during the designated period.'), $border, 'L');
					$pdf->Ln(5);

					$border = 0;
					$pdf->Cell(40,5, TTi18n::gettext('Employee Signature:'), $border, 0, 'L');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');
					$pdf->Cell(40,5, TTi18n::gettext('Supervisor Signature:'), $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(40,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['first_name'] .' '. $user_data['last_name'] , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, TTi18n::gettext('(print name)'), $border, 0, 'C');

					if ( $user_data['verified_time_sheet_date'] != FALSE ) {
						$pdf->Ln();
						$pdf->SetFont('','B',10);
						$pdf->Cell(200,5, TTi18n::gettext('TimeSheet electronically signed by').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '. TTi18n::gettext('on') .' '. TTDate::getDate('DATE+TIME', $user_data['verified_time_sheet_date'] ), $border, 0, 'C');
						$pdf->SetFont('','',10);
					}


					//Add generated date/time at the bottom.
					$pdf->SetFont('','I',8);
					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(245, $adjust_y) );
					$pdf->Cell(200,5, TTi18n::gettext('Generated:') .' '. TTDate::getDate('DATE+TIME', $pdf_created_date ), $border, 0, 'C');
                                 */
 
				}

				$output = $pdf->Output('','S');
			}

			if ( isset($output) AND $output !== FALSE AND Debug::getVerbosity() < 11 ) {
				Misc::FileDownloadHeader('timesheet.pdf', 'application/pdf', strlen($output));
				echo $output;
				exit;
			} else {
				//Debug::Display();
				echo TTi18n::gettext('ERROR: Leave Comfirm Report not Available') . "<br>\n";
				exit;
			}

		} 

$ulf = TTnew( 'UserListFactory' );

$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

$data = Misc::prependArray( isset($all_array_option) ? $all_array_option : [] , UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );


             
$pager = new Pager($lrlf);

$pager = isset($pager) ? $pager : [];

$msg = isset($data['msg']) ? $data['msg'] : [] ;

$smarty->assign_by_ref('data', $data); 
$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );
        
$smarty->display('leaves/ConfirmedLeave.tpl');



function returnBetweenDates( $startDate, $endDate ){
    $startStamp = strtotime(  $startDate );
    $endStamp   = strtotime(  $endDate );

    if( $endStamp > $startStamp ){
        while( $endStamp >= $startStamp ){

            $dateArr[] = date( 'Y-m-d', $startStamp );

            $startStamp = strtotime( ' +1 day ', $startStamp );

        }
        return $dateArr;    
    }else{
        return $startDate;
    }

}