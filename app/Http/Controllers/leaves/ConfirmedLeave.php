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
use App\Models\Core\TTPDF;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use DateTime;
use Illuminate\Support\Facades\View;
use TCPDF;
// TimesheetDetailReport
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

	// public function index()
	// {
	//     $current_user = $this->currentUser;
	//     $viewData['title'] = 'Confiremed Leave';

	// 	if(!isset($filter_data)){
	// 	   $filter_data = array();
	// 	}

	// 	if ( isset($filter_data['start_date']) && $filter_data['start_date'] !='' ) {
	// 		$from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['start_date']);
	// 		$filter_data['start_date'] =  $from_date->format('Y-m-d');
	// 	}

	// 	if ( isset($filter_data['end_date']) && $filter_data['end_date'] !='' ) {
	// 		$from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['end_date']);
	// 		$filter_data['end_date'] =  $from_date->format('Y-m-d');
	// 	}

	// 	$lrlf = new LeaveRequestListFactory();

	// 	$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);
	//    	$leaves = [];

	//    	if($lrlf->getRecordCount() >0){

	// 		foreach($lrlf->rs as $lrf_obj) {
	// 			$lrlf->data = (array)$lrf_obj;
	// 			$lrf_obj = $lrlf;

	//             $leaves [] = array(
	//                 'id' => $lrf_obj->getId(),
	//                 'user' => $lrf_obj->getUserObject()->getFullName(),
	//                 'user_id' => $lrf_obj->getUser(),
	//                 'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
	//                 'start_date' => $lrf_obj->getLeaveFrom(),
	//                 'end_date' => $lrf_obj->getLeaveTo(),
	//                 'amount' => $lrf_obj->getAmount(),
	//                 'is_hr_approved' => $lrf_obj->getHrApproved()
	//             );
	// 		}
	//    	}

	//     $viewData['leaves'] = $leaves;
	//     // dd($viewData);

	//     return view('leaves/ConfirmedLeave', $viewData);
	// }


	public function index(Request $request)
	{
		$viewData['title'] = 'Confirmed Leave';
		$current_user = $this->currentUser;

		// Handle filter input
		$filter_data = $request->input('filter_data', []);
		$filter_user_id = $request->input('user_id', null);

		if ($filter_user_id === null) {
			$filter_user_id = $current_user->getId();
		}

		// Convert date formats if present
		if (!empty($filter_data['start_date'])) {
			$from_date = DateTime::createFromFormat('Y-m-d', $filter_data['start_date']);
			$filter_data['start_date'] = $from_date ? $from_date->format('Y-m-d') : null;
		}

		if (!empty($filter_data['end_date'])) {
			$from_date = DateTime::createFromFormat('Y-m-d', $filter_data['end_date']);
			$filter_data['end_date'] = $from_date ? $from_date->format('Y-m-d') : null;
		}

		// Get confirmed leaves
		$lrlf = new LeaveRequestListFactory();
		$lrlf->getAllConfirmedLeave($filter_user_id, $filter_data);
		$leaves = [];

		if ($lrlf->getRecordCount() > 0) {
			foreach ($lrlf->rs as $lrf_obj) {
				$lrlf->data = (array) $lrf_obj;
				$lrf_obj = $lrlf;

				$user_id = $lrf_obj->getUser();

				$ulf = new UserListFactory();
				$user_obj = $ulf->getById($user_id)->getCurrent();

				$leaves[] = [
					'id' => $lrf_obj->getId(),
					'user' => $user_obj->getFullName(),
					'user_id' => $user_id,
					'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
					'start_date' => $lrf_obj->getLeaveFrom(),
					'end_date' => $lrf_obj->getLeaveTo(),
					'amount' => $lrf_obj->getAmount(),
					'is_hr_approved' => $lrf_obj->getHrApproved(),
				];
			}
		}

		// Load employee options
		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria($current_user->getId(), []);
		$user_options = ['' => '--Select Employee--'] + UserListFactory::getArrayByListFactory($ulf, false, true);

		$viewData['user_options'] = $user_options;
		$viewData['filter_user_id'] = $filter_user_id;
		$viewData['filter_data'] = $filter_data;
		$viewData['leaves'] = $leaves;
		// dd($viewData);

		return view('leaves.ConfirmedLeave', $viewData);
	}



	// public function export(Request $request)
    // {
    //     // Handle filter input
	// 	//   <button type="button"  onclick="window.location.href='{{ url('/attendance/leaves/confirmed_leave/export') }}'" class="btn btn-secondary">Filler Export</button>
                           
	// 	$viewData['title'] = 'Confirmed Leave';
	// 	$current_user = $this->currentUser;

    //     $filter_data = $request->input('filter_data', []);
    //     $filter_user_id = $request->input('user_id', null);

    //     if ($filter_user_id === null) {
    //         $filter_user_id = $this->currentUser->getId();
    //     }

    //     // Convert date formats if present
    //     if (!empty($filter_data['start_date'])) {
    //         $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['start_date']);
    //         $filter_data['start_date'] = $from_date ? $from_date->format('Y-m-d') : null;
    //     }

    //     if (!empty($filter_data['end_date'])) {
    //         $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['end_date']);
    //         $filter_data['end_date'] = $from_date ? $from_date->format('Y-m-d') : null;
    //     }

    //     // Fetch confirmed leaves
    //     $lrlf = new LeaveRequestListFactory();
    //     $lrlf->getAllConfirmedLeave($filter_user_id, $filter_data);


    //     if ($lrlf->getRecordCount() > 0) {
    //         // Initialize TCPDF
    //         $pdf = new TCPDF('P', 'mm', 'Letter', true, 'UTF-8', false);
    //         $pdf->SetMargins(10, 5, 10);
    //         $pdf->SetAutoPageBreak(false);
    //         $pdf->SetFont('freeserif', '', 10);

    //         $border = 0;
    //         $adjust_x = 10;
    //         $adjust_y = 10;

    //         // Add a page
    //         $pdf->AddPage();

    //         // Header
    //         $pdf->SetFont('', 'B', 32);
    //         $pdf->Cell(200, 15, __('Confirmed Leave Report'), $border, 0, 'C');
    //         $pdf->Ln();
    //         $pdf->SetFont('', 'B', 12);
    //         $pdf->Cell(200, 5, $this->currentCompany->name, $border, 0, 'C');
    //         $pdf->Ln(10);

    //         // Column widths
    //         $column_widths = [
    //             'line' => 5,
    //             'dow' => 55,
    //             'min_punch_time_stamp' => 50,
    //             'max_punch_time_stamp' => 30,
    //             'worked_time' => 30,
    //             'over_time' => 24,
    //         ];

    //         // Table header
    //         $line_h = 6;
    //         $pdf->Ln();
    //         $pdf->SetFont('', 'B', 10);
    //         $pdf->setFillColor(220, 220, 220);
    //         $pdf->MultiCell($column_widths['line'], $line_h, '#', 1, 'C', 1, 0);
    //         $pdf->MultiCell($column_widths['dow'], $line_h, __('Employee'), 1, 'C', 1, 0);
    //         $pdf->MultiCell($column_widths['min_punch_time_stamp'], $line_h, __('Leave Type'), 1, 'C', 1, 0);
    //         $pdf->MultiCell($column_widths['max_punch_time_stamp'], $line_h, __('Leave Start Date'), 1, 'C', 1, 0);
    //         $pdf->MultiCell($column_widths['worked_time'], $line_h, __('Leave End Date'), 1, 'C', 1, 0);
    //         $pdf->MultiCell($column_widths['over_time'], $line_h, __('No Days'), 1, 'C', 1, 0);
    //         $pdf->Ln();

    //         // Table data
    //         $k = 1;
    //         foreach ($lrlf as $lrf_obj) {
    //             $lrlf->data = (array) $lrf_obj;
    //             $lrf_obj = $lrlf;

    //             $user_id = $lrf_obj->getUser();
    //             $ulf = new UserListFactory();
    //             $user_obj = $ulf->getById($user_id)->getCurrent();

    //             $pdf->SetFont('', '', 10);
    //             $pdf->Cell($column_widths['line'], 6, $k, 1, 0, 'C', 1);
    //             $pdf->Cell($column_widths['dow'], 6, $user_obj->getFullName(), 1, 0, 'C', 1);
    //             $pdf->Cell($column_widths['min_punch_time_stamp'], 6, $lrf_obj->getAccuralPolicyObject()->getName(), 1, 0, 'C', 1);
    //             $pdf->Cell($column_widths['max_punch_time_stamp'], 6, $lrf_obj->getLeaveFrom(), 1, 0, 'C', 1);
    //             $pdf->Cell($column_widths['worked_time'], 6, $lrf_obj->getLeaveTo(), 1, 0, 'C', 1);
    //             $pdf->Cell($column_widths['over_time'], 6, $lrf_obj->getAmount(), 1, 0, 'C', 1);
    //             $pdf->Ln();
    //             $k++;
    //         }

    //         // Output PDF
    //         $output = $pdf->Output('', 'S');

    //         return response($output)
    //             ->header('Content-Type', 'application/pdf')
    //             ->header('Content-Disposition', 'attachment; filename="timesheet.pdf"')
    //             ->header('Content-Length', strlen($output));
    //     }

    //     // Return error response if no data
    //     return response()->json([
    //         'error' => __('ERROR: Leave Confirm Report not Available')
    //     ], 404);
    // }

	public function export(Request $request)
    {
        // Handle filter input
        $filter_data = $request->input('filter_data', []);
        $filter_user_id = $request->input('user_id', null);

        if ($filter_user_id === null) {
            $filter_user_id = $this->currentUser->getId();
        }

        // Convert date formats if present
        if (!empty($filter_data['start_date'])) {
            $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['start_date']);
            $filter_data['start_date'] = $from_date ? $from_date->format('Y-m-d') : null;
        }

        if (!empty($filter_data['end_date'])) {
            $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['end_date']);
            $filter_data['end_date'] = $from_date ? $from_date->format('Y-m-d') : null;
        }

        // Fetch confirmed leaves
        $lrlf = new LeaveRequestListFactory();
        $lrlf->getAllConfirmedLeave($filter_user_id, $filter_data);

        if ($lrlf->getRecordCount() > 0) {
            // Initialize TCPDF
            $pdf = new TCPDF('P', 'mm', 'Letter', true, 'UTF-8', false);
            $pdf->SetMargins(10, 5, 10);
            $pdf->SetAutoPageBreak(false);
            $pdf->SetFont('freeserif', '', 10);

            $border = 0;

            // Add a page
            $pdf->AddPage();

            // Header
            $pdf->SetFont('', 'B', 32);
            $pdf->Cell(200, 15, __('Confirmed Leave Report'), $border, 0, 'C');
            $pdf->Ln();
            $pdf->SetFont('', 'B', 12);
            $pdf->Cell(200, 5, $this->currentCompany->name ?? 'Company Name', $border, 0, 'C');
            $pdf->Ln(10);

            // Column widths
            $column_widths = [
                'line' => 5,
                'dow' => 55,
                'min_punch_time_stamp' => 50,
                'max_punch_time_stamp' => 30,
                'worked_time' => 30,
                'over_time' => 24,
            ];

            // Table header
            $line_h = 6;
            $pdf->Ln();
            $pdf->SetFont('', 'B', 10);
            $pdf->setFillColor(220, 220, 220);
            $pdf->MultiCell($column_widths['line'], $line_h, '#', 1, 'C', 1, 0);
            $pdf->MultiCell($column_widths['dow'], $line_h, __('Employee'), 1, 'C', 1, 0);
            $pdf->MultiCell($column_widths['min_punch_time_stamp'], $line_h, __('Leave Type'), 1, 'C', 1, 0);
            $pdf->MultiCell($column_widths['max_punch_time_stamp'], $line_h, __('Leave Start Date'), 1, 'C', 1, 0);
            $pdf->MultiCell($column_widths['worked_time'], $line_h, __('Leave End Date'), 1, 'C', 1, 0);
            $pdf->MultiCell($column_widths['over_time'], $line_h, __('No Days'), 1, 'C', 1, 0);
            $pdf->Ln();

            // Table data
            $k = 1;
			
            foreach ($lrlf->rs as $lrf_obj) {
                $lrlf->data = (array) $lrf_obj;
                $lrf_obj = $lrlf;

                $user_id = $lrf_obj->getUser();
                $ulf = new UserListFactory();
                $user_obj = $ulf->getById($user_id)->getCurrent();

                $pdf->SetFont('', '', 10);
                $pdf->Cell($column_widths['line'], 6, $k, 1, 0, 'C', 1);
                $pdf->Cell($column_widths['dow'], 6, $user_obj->getFullName(), 1, 0, 'C', 1);
                $pdf->Cell($column_widths['min_punch_time_stamp'], 6, $lrf_obj->getAccuralPolicyObject()->getName(), 1, 0, 'C', 1);
                $pdf->Cell($column_widths['max_punch_time_stamp'], 6, $lrf_obj->getLeaveFrom(), 1, 0, 'C', 1);
                $pdf->Cell($column_widths['worked_time'], 6, $lrf_obj->getLeaveTo(), 1, 0, 'C', 1);
                $pdf->Cell($column_widths['over_time'], 6, $lrf_obj->getAmount(), 1, 0, 'C', 1);
                $pdf->Ln();
                $k++;
            }

            // Output PDF
            $output = $pdf->Output('', 'S');

            return response($output)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="timesheet.pdf"')
                ->header('Content-Length', strlen($output));
        }

        // Return error response if no data
        return response()->json([
            'error' => __('ERROR: Leave Confirm Report not Available')
        ], 404);
    }


	// public function search()
	// {
	//     $viewData['title'] = 'Confiremed Leave';
	// 	$current_user = $this->currentUser;

	//     if(!isset($filter_data)){
	//         $filter_data = array();
	//     }

	//     if ( isset($filter_data['start_date']) && $filter_data['start_date'] !='' ) {
	//         //$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);

	//             // $from_date =  DateTime::createFromFormat('j-M-y', $filter_data['start_date']);
	//             // $from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['start_date']);
	//             $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['start_date']);
	//             $filter_data['start_date'] =  $from_date->format('Y-m-d');
	//     }

	//     if ( isset($filter_data['end_date']) && $filter_data['end_date'] !='' ) {
	//         //$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);

	//         // $from_date =  DateTime::createFromFormat('j-M-y', $filter_data['start_date']);
	//             // $from_date =  DateTime::createFromFormat('d/m/Y', $filter_data['end_date']);
	//             $from_date = DateTime::createFromFormat('Y-m-d', $filter_data['end_date']);
	//             $filter_data['end_date'] =  $from_date->format('Y-m-d');
	//     }


	// 	$lrlf = new LeaveRequestListFactory();

	// 	$lrlf->getAllConfirmedLeave($current_user->getId(),$filter_data);

	// 	//echo $current_user->getRecordCount();
	// 	$leaves = [];

	// 	if($lrlf->getRecordCount() >0){

	// 		foreach($lrlf->rs as $lrf_obj) {
	// 			$lrlf->data = (array)$lrf_obj;
	// 			$lrf_obj = $lrlf;

	//             $leaves [] = array(
	//                 'id' => $lrf_obj->getId(),
	//                 'user' => $lrf_obj->getUserObject()->getFullName(),
	//                 'user_id' => $lrf_obj->getUser(),
	//                 'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
	//                 'start_date' => $lrf_obj->getLeaveFrom(),
	//                 'end_date' => $lrf_obj->getLeaveTo(),
	//                 'amount' => $lrf_obj->getAmount(),
	//                 'is_hr_approved' => $lrf_obj->getHrApproved()
	//             );

	// 		}
	// 	}

	//     $viewData['leaves'] = $leaves;
	//     dd($viewData);

	//     return view('leaves/ConfirmedLeave', $viewData);

	// }


	public function search(Request $request)
	{
		$viewData['title'] = 'Confirmed Leave';
		$current_user = $this->currentUser;

		// Get filters
		$filter_data = $request->input('filter_data', []);
		$filter_user_id = $request->input('user_id', null);
		// Add user_id to filter data
		if (!empty($filter_user_id)) {
			$filter_data['user_id'] = $filter_user_id;
		}

		// Format start date
		if (!empty($filter_data['start_date'])) {
			$from_date = DateTime::createFromFormat('Y-m-d', $filter_data['start_date']);
			$filter_data['start_date'] = $from_date ? $from_date->format('Y-m-d') : null;
		}

		// Format end date
		if (!empty($filter_data['end_date'])) {
			$from_date = DateTime::createFromFormat('Y-m-d', $filter_data['end_date']);
			$filter_data['end_date'] = $from_date ? $from_date->format('Y-m-d') : null;
		}

		// Call leave search with full filters
		$lrlf = new LeaveRequestListFactory();
		$lrlf->getAllConfirmedLeave($filter_user_id, $filter_data);  // Pass user + date

		// Prepare leave data
		$leaves = [];

		if ($lrlf->getRecordCount() > 0) {
			foreach ($lrlf->rs as $lrf_obj) {
				$lrlf->data = (array)$lrf_obj;
				$lrf_obj = $lrlf;

				$leaves[] = [
					'id' => $lrf_obj->getId(),
					'user' => $lrf_obj->getUserObject()->getFullName(),
					'user_id' => $lrf_obj->getUser(),
					'leave_name' => $lrf_obj->getAccuralPolicyObject()->getName(),
					'start_date' => $lrf_obj->getLeaveFrom(),
					'end_date' => $lrf_obj->getLeaveTo(),
					'amount' => $lrf_obj->getAmount(),
					'is_hr_approved' => $lrf_obj->getHrApproved(),
				];
			}
		}
		// Load dropdown options
		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria($current_user->getId(), []);
		$user_options = ['' => '--Select Employee--'] + UserListFactory::getArrayByListFactory($ulf, false, true);

		$viewData['user_options'] = $user_options;
		$viewData['filter_user_id'] = $filter_user_id;
		$viewData['filter_data'] = $filter_data;
		$viewData['leaves'] = $leaves;

		return view('leaves.ConfirmedLeave', $viewData);
	}



	function returnBetweenDates($startDate, $endDate)
	{
		$startStamp = strtotime($startDate);
		$endStamp   = strtotime($endDate);

		if ($endStamp > $startStamp) {
			while ($endStamp >= $startStamp) {
				$dateArr[] = date('Y-m-d', $startStamp);
				$startStamp = strtotime(' +1 day ', $startStamp);
			}
			return $dateArr;
		} else {
			return $startDate;
		}
	}


	public function delete($id)
	{
		if (empty($id)) {
			return response()->json(['error' => 'No Leave Selected.'], 400);
		}

		$delete = TRUE;

		$lrlf = new LeaveRequestListFactory();
		$lrlf->getById($id);

		foreach ($lrlf->rs as $leave_obj) {
			$lrlf->data = (array)$leave_obj;
			$leave_obj = $lrlf;

			$leave_obj->setDeleted($delete);
			if ($leave_obj->isValid()) {
				$res = $leave_obj->Save();

				if ($res) {
					return response()->json(['success' => 'Leave Deleted Successfully.']);
				} else {
					return response()->json(['error' => 'Leave Deleted Failed.']);
				}
			}
		}
		Redirect::Page(URLBuilder::getURL(NULL, 'ConfirmedLeave'));
	}
}
