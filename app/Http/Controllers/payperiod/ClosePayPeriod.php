<?php

namespace App\Http\Controllers\payperiod;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use App\Models\Core\Environment;
use App\Models\Core\BreadCrumb;
use App\Models\Core\CurrencyFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\ExceptionListFactory;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\PayStubAmendment\PayStubAmendmentListFactory;
use App\Models\Request\RequestListFactory;
use Illuminate\Support\Facades\View;

class ClosePayPeriod extends Controller
{
	protected $permission;
    protected $company;
    protected $userPrefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');
    
        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');

		/*
		if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','view') OR $permission->Check('pay_period_schedule','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
		*/
    }

	public function index(){
		$current_company = $this->company;
        $current_user_prefs = $this->userPrefs;
		
		$viewData = [];

		$viewData['title'] = 'End of Pay Period';
		
		/* Get FORM variables */
		extract	(FormVariables::GetVariables(
			array	(
				'action',
				'page',
				'sort_column',
				'sort_order',
				'pay_period_ids',
				'pay_stub_pay_period_ids'
			)
		) );

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array(
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);

		Debug::Arr($pay_period_ids,'Selected Pay Periods', __FILE__, __LINE__, __METHOD__,10);

		//Step 1, get all open pay periods that have ended and are before the transaction date.
		$pplf = new PayPeriodListFactory();
		$ppslf = new PayPeriodScheduleListFactory();

		$open_pay_periods = FALSE;

		//$pplf->getByCompanyIdAndTransactionDate( $current_company->getId(), TTDate::getTime() );
		$pplf->getByCompanyIdAndStatus( $current_company->getId(), array(10,12,15) );

		if ( $pplf->getRecordCount() > 0 ) {
			foreach ($pplf->rs as $pay_period_obj) {

				$pplf->data = (array)$pay_period_obj;

				$pay_period_schedule = $ppslf->getById( $pplf->getPayPeriodSchedule() )->getCurrent();

				if ( $pay_period_schedule != FALSE) {
					
					$elf = new ExceptionListFactory(); 
					$elf->getSumExceptionsByPayPeriodIdAndBeforeDate($pplf->getId(), $pplf->getEndDate() );

					$low_severity_exceptions = 0;
					$med_severity_exceptions = 0;
					$high_severity_exceptions = 0;
					$critical_severity_exceptions = 0;
					
					if ( $elf->getRecordCount() > 0 ) {
						Debug::Text(' Found Exceptions: '. $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
						foreach($elf->rs as $e_obj ) {
							if ( $e_obj->severity_id == 10 ) {
								$low_severity_exceptions = $e_obj->count;
							}
							if ( $e_obj->severity_id == 20 ) {
								$med_severity_exceptions = $e_obj->count;
							}
							if ( $e_obj->severity_id == 25 ) {
								$high_severity_exceptions = $e_obj->count;
							}
							if ( $e_obj->severity_id == 30 ) {
								$critical_severity_exceptions = $e_obj->count;
							}
						}
					} else {
						Debug::Text(' No Exceptions!', __FILE__, __LINE__, __METHOD__,10);
					}

					//Get all pending requests
					$pending_requests = 0;
					$rlf = new RequestListFactory();
					$rlf->getSumByPayPeriodIdAndStatus( $pplf->getId(), 30 );
					if ( $rlf->getRecordCount() > 0 ) {
						$pending_requests = $rlf->getCurrent()->getColumn('total');
					}

					//Get PS Amendments.
					$psalf = new PayStubAmendmentListFactory();
					$psalf->getByUserIdAndAuthorizedAndStartDateAndEndDate( $pay_period_schedule->getUser(), TRUE, $pplf->getStartDate(), $pplf->getEndDate() );
					$total_ps_amendments = 0;
					if ( is_object($psalf) ) {
						$total_ps_amendments = $psalf->getRecordCount();
					}

					//Get verified timesheets
					$pptsvlf = new PayPeriodTimeSheetVerifyListFactory(); 
					$pptsvlf->getByPayPeriodIdAndCompanyId( $pplf->getId(), $current_company->getId() );
					$verified_time_sheets = 0;
					$pending_time_sheets = 0;
					if ( $pptsvlf->getRecordCount() > 0 ) {
						foreach( $pptsvlf as $pptsv_obj ) {
							if ( $pptsv_obj->getAuthorized() == TRUE ) {
								$verified_time_sheets++;
							} elseif (  $pptsv_obj->getStatus() == 30 OR $pptsv_obj->getStatus() == 45 ) {
								$pending_time_sheets++;
							}
						}
					}

					//Get total employees with time for this pay period.
					$udtlf = new UserDateTotalListFactory(); 
					$total_worked_users = $udtlf->getWorkedUsersByPayPeriodId( $pplf->getId() );

					//Count how many pay stubs for each pay period.
					$pslf = new PayStubListFactory();
					$total_pay_stubs = $pslf->getByPayPeriodId( $pplf->getId() )->getRecordCount();

					if ( $pplf->getStatus() != 20 ) {
						$open_pay_periods = TRUE;
					}

					$pay_periods[] = array(
						'id' => $pplf->getId(),
						'company_id' => $pplf->getCompany(),
						'pay_period_schedule_id' => $pplf->getPayPeriodSchedule(),
						'name' => $pay_period_schedule->getName(),
						'type' => Option::getByKey($pay_period_schedule->getType(), $pay_period_schedule->getOptions('type') ),
						'status' => Option::getByKey($pplf->getStatus(), $pplf->getOptions('status') ),
						'start_date' => TTDate::getDate( 'DATE+TIME', $pplf->getStartDate() ),
						'end_date' => TTDate::getDate( 'DATE+TIME', $pplf->getEndDate() ),
						'transaction_date' => TTDate::getDate( 'DATE+TIME', $pplf->getTransactionDate() ),
						'low_severity_exceptions' => $low_severity_exceptions,
						'med_severity_exceptions' => $med_severity_exceptions,
						'high_severity_exceptions' => $high_severity_exceptions,
						'critical_severity_exceptions' => $critical_severity_exceptions,
						'pending_requests' => $pending_requests,
						'verified_time_sheets' => $verified_time_sheets,
						'pending_time_sheets' => $pending_time_sheets,
						'total_worked_users' => $total_worked_users,
						'total_ps_amendments' => $total_ps_amendments,
						'total_pay_stubs' => $total_pay_stubs,
						'deleted' => $pplf->getDeleted()
					);
				}
				unset(	
					$total_shifts,
					$total_ps_amendments,
					$total_pay_stubs,
					$verified_time_sheets,
					$total_worked_users
				);

			}

		} else {
			Debug::Text('No pay periods pending transaction ', __FILE__, __LINE__, __METHOD__,10);
		}

		$viewData['open_pay_periods'] = $open_pay_periods;
		$viewData['pay_periods'] = $pay_periods;
		$total_pay_periods = count($pay_periods);
		$viewData['total_pay_periods'] = $total_pay_periods;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;

		return view('payperiod.ClosePayPeriod', $viewData);
	}

	// function for lock/unlock/close
	public function action(Request $request) {
		$action = strtolower($request->input('action'));// Get the action from request
		$pay_period_ids = $request->input('pay_period_ids', []); // Default to an empty array if not set
		
		$current_company = $this->company;
		$current_user_prefs = $this->userPrefs;
	
		// Debugging
		Debug::Text('Lock Selected Pay Periods... Action: ' . $action, __FILE__, __LINE__, __METHOD__, 10);
	
		$pplf = new PayPeriodListFactory();
	
		$pplf->StartTransaction();
	
		if (!empty($pay_period_ids)) {
			foreach ($pay_period_ids as $pay_period_id) {
				$pay_period_obj = $pplf->getById($pay_period_id)->getCurrent();
	
				if ($pay_period_obj && $pay_period_obj->getStatus() != 20) {
					switch ($action) {
						case 'close':
							$pay_period_obj->setStatus(20);
							break;
						case 'lock':
							$pay_period_obj->setStatus(12);
							break;
						default:
							$pay_period_obj->setStatus(10);
							break;
					}
					$pay_period_obj->Save();
				}
			}
		}
	
		$pplf->CommitTransaction();
		
		return redirect()->to(URLBuilder::getURL(NULL, '/payroll/payroll_processing'));
	}
	

	public function generate_pay_stubs(Request $request){
		$action = strtolower(str_replace(' ', '_', trim($request->input('action'))));// Get the action from request
		$pay_stub_pay_period_ids = $request->input('pay_stub_pay_period_ids', []); // Default to an empty array if not set

		Debug::Text('Generate Pay Stubs ', __FILE__, __LINE__, __METHOD__,10);

		return redirect()->route('progress_bar.generate_paystubs', [
			'action' => 'generate_paystubs',
			'pay_period_ids' => $pay_stub_pay_period_ids,
			'next_page' => 'payroll.payroll_processing'
		]);		
	}
}





?>