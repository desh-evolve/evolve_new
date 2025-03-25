<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualBalanceFactory;
use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\Accrual\AccrualListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use DateTime;
use Illuminate\Support\Facades\View;

class ConfirmedLeave extends Controller
{
    protected $permission;
    protected $currentUser;
    protected $currentCompany;
    protected $userPrefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');
        $this->userPrefs = View::shared('current_user_prefs');
    }

    public function index() {

        $viewData['title'] = 'Confiremed Leave';
		
		if(!isset($filter_data)){
		   $filter_data = array();
		}
		
		if ( isset($filter_data['start_date']) && $filter_data['start_date'] !='' ) {
			$from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['start_date']);
			$filter_data['start_date'] =  $from_date->format('Y-m-d');
		}
		
		if ( isset($filter_data['end_date']) && $filter_data['end_date'] !='' ) {
			$from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['end_date']);
			$filter_data['end_date'] =  $from_date->format('Y-m-d');
		}
		
		$msg = "";
		$lrlf = new LeaveRequestListFactory();
		
		$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);

	   	$leave= array();
	   	if($lrlf->getRecordCount() >0){
			foreach($lrlf->rs as $lrf_obj) {
				$lrlf->data = (array)$lrf_obj;
				$lrf_obj = $lrlf;

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
	   
						$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);
		
						$pdf->AddPage();
						   
	   
						$adjust_x = 10;
						$adjust_y = 10;
	
						$pdf->SetFont('','B',32);
						$pdf->Cell(200,15, _('Confiremed Leave Report') , $border, 0, 'C');
						$pdf->Ln();
						$pdf->SetFont('','B',12);
						$pdf->Cell(200,5, $current_company->getName() , $border, 0, 'C');
						$pdf->Ln(10);
	   
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
						$pdf->MultiCell( $column_widths['dow'], $line_h, _('Employee') , 1, 'C', 1, 0);
						$pdf->MultiCell( $column_widths['min_punch_time_stamp'], $line_h, _('Leave Type') , 1, 'C', 1, 0);
						$pdf->MultiCell( $column_widths['max_punch_time_stamp'], $line_h, _('Leave start date') , 1, 'C', 1, 0);
						$pdf->MultiCell( $column_widths['worked_time'], $line_h, _('Leave End Date') , 1, 'C', 1, 0);
						$pdf->MultiCell( $column_widths['over_time'], $line_h, _('No Days') , 1, 'C', 1, 0);

						$pdf->Ln();
						foreach($lrlf->rs as $lrf_obj) {
							$lrlf->data = (array)$lrf_obj;
							$lrf_obj = $lrlf;

							$pdf->SetFont('','',10);
							$pdf->Cell( $column_widths['line'], 6, $k , 1, 0, 'C', 1);
							//$pdf->Cell( $column_widths['date_stamp'], 6, TTDate::getDate('DATE', $lrf_obj->getId() ), 1, 0, 'C', 1);
							$pdf->Cell( $column_widths['dow'], 6, $lrf_obj->getUserObject()->getFullName() , 1, 0, 'C', 1);
							$pdf->Cell( $column_widths['min_punch_time_stamp'], 6, $lrf_obj->getAccuralPolicyObject()->getName(), 1, 0, 'C', 1);
							$pdf->Cell( $column_widths['max_punch_time_stamp'], 6, $lrf_obj->getLeaveFrom(), 1, 0, 'C', 1);
							$pdf->Cell( $column_widths['worked_time'], 6, $lrf_obj->getLeaveTo(), 1, 0, 'C', 1);
							/*$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $data['regular_time'] ), 1, 0, 'C', 1);*/
							$pdf->Cell( $column_widths['over_time'], 6,  $lrf_obj->getAmount(), 1, 0, 'C', 1);
							
							$pdf->Ln();
						}
	   
					   $output = $pdf->Output('','S');
				   }
	   
				   if ( isset($output) AND $output !== FALSE AND Debug::getVerbosity() < 11 ) {
					   Misc::FileDownloadHeader('timesheet.pdf', 'application/pdf', strlen($output));
					   echo $output;
					   exit;
				   } else {
					   //Debug::Display();
					   echo _('ERROR: Leave Comfirm Report not Available') . "<br>\n";
					   exit;
				   }
	   
			   } 
	   
	   	$ulf = new UserListFactory();
			   
	   	$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			   
	   	$data = Misc::prependArray( isset($all_array_option) ? $all_array_option : [] , 	UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );
	   
	   
					
	   	$pager = new Pager($lrlf);
			   
	   	$pager = isset($pager) ? $pager : [];
			   
	   	$msg = isset($data['msg']) ? $data['msg'] : [] ;
	   
		$viewData['data'] = $data;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('leaves/ConfirmedLeave', $viewData);
	}
    

	public function export(){}

	public function search(){
		$current_user = $this->currentUser;
		
		$msg = "";
		$lrlf = new LeaveRequestListFactory();

		$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);

		//echo $current_user->getRecordCount();
		$leave= array();
		if($lrlf->getRecordCount() >0){


			foreach($lrlf->rs as $lrf_obj) {
				$lrlf->data = (array)$lrf_obj;
				$lrf_obj = $lrlf;

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

}