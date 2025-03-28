<?php

namespace App\Http\Controllers\payperiod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\ExceptionListFactory;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\PayPeriod\PayPeriodFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class ViewPayPeriod extends Controller
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
        /*
        if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'View Pay Period';

		$ppf = new PayPeriodFactory(); 

		if ( isset($pay_period_id) ) {

			$status_options = $ppf->getOptions('status');

			$pplf = new PayPeriodListFactory();
			$pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() );

			foreach ($pplf->rs as $pay_period_obj) {
				$pplf->data = (array)$pay_period_obj;
				$pay_period_obj = $pplf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$pay_period_data = array(
					'id' => $pay_period_obj->getId(),
					'company_id' => $pay_period_obj->getCompany(),
					'pay_period_schedule_id' => $pay_period_obj->getPayPeriodSchedule(),
					'pay_period_schedule_type' => $pay_period_obj->getPayPeriodScheduleObject()->getType(),
					'status_id' => $pay_period_obj->getStatus(),
					'status' => $status_options[$pay_period_obj->getStatus()],
					'start_date' => $pay_period_obj->getStartDate(),
					'end_date' => $pay_period_obj->getEndDate(),
					'transaction_date' => $pay_period_obj->getTransactionDate(),
					'is_primary' => $pay_period_obj->getPrimary(),

					'deleted' => $pay_period_obj->getDeleted(),
					'tainted' => $pay_period_obj->getTainted(),
					'tainted_date' => $pay_period_obj->getTaintedDate(),
					'tainted_by' => $pay_period_obj->getTaintedBy(),
					'created_date' => $pay_period_obj->getCreatedDate(),
					'created_by' => $pay_period_obj->getCreatedBy(),
					'updated_date' => $pay_period_obj->getUpdatedDate(),
					'updated_by' => $pay_period_obj->getUpdatedBy(),
					'deleted_date' => $pay_period_obj->getDeletedDate(),
					'deleted_by' => $pay_period_obj->getDeletedBy()
				);
			}
			Debug::Text('Current Pay Period Status: '. $pay_period_obj->getStatus(), __FILE__, __LINE__, __METHOD__,10);

			$status_options = $pay_period_obj->getOptions('status');

			if ( $pay_period_obj->getStatus() == 20
					OR $pay_period_obj->getStatus() == 30 ) {
				//Once pay period is closed, do not allow it to re-open.
				$status_filter_arr = array(20,30);
			} else {
				//Only allow to close pay period if AFTER end date.
				if ( TTDate::getTime() >= $pay_period_obj->getEndDate() ) {
					$status_filter_arr = array(10,12,$pay_period_obj->getStatus(), 20);
				} else {
					$status_filter_arr = array(10,12,$pay_period_obj->getStatus() );
				}
			}

			$status_options = Option::getByArray( $status_filter_arr, $status_options);

			$smarty->assign_by_ref('status_options', $status_options);

			$elf = new ExceptionListFactory(); 
			$elf->getSumExceptionsByPayPeriodIdAndBeforeDate($pay_period_obj->getId(), $pay_period_obj->getEndDate() );
			$exceptions = array(
								'low' => 0,
								'med' => 0,
								'high' => 0,
								'critical' => 0,
								);
			if ( $elf->getRecordCount() > 0 ) {
				Debug::Text(' Found Exceptions: '. $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
				foreach($elf->rs as $e_obj ) {
					$elf->data = (array)$e_obj;
					$e_obj = $elf;

					if ( $e_obj->getColumn('severity_id') == 10 ) {
						$exceptions['low'] = $e_obj->getColumn('count');
					}
					if ( $e_obj->getColumn('severity_id') == 20 ) {
						$exceptions['med'] = $e_obj->getColumn('count');
					}
					if ( $e_obj->getColumn('severity_id') == 25 ) {
						$exceptions['high'] = $e_obj->getColumn('count');
					}
					if ( $e_obj->getColumn('severity_id') == 30 ) {
						$exceptions['critical'] = $e_obj->getColumn('count');
					}

				}
			} else {
				Debug::Text(' No Exceptions!', __FILE__, __LINE__, __METHOD__,10);
			}

			//Get all pending requests
			$pending_requests = 0;
			$rlf = new RequestListFactory();
			$rlf->getSumByPayPeriodIdAndStatus( $pay_period_obj->getId(), 30 );
			if ( $rlf->getRecordCount() > 0 ) {
				$pending_requests = $rlf->getCurrent()->getColumn('total');
			}
			$pay_period_data['pending_requests'] = $pending_requests;

			//Count how many punches are in this pay period.
			$plf = new PunchListFactory();
			$pay_period_data['total_punches'] = $plf->getByPayPeriodId( $pay_period_id )->getRecordCount();
			Debug::Text(' Total Punches: '. $pay_period_data['total_punches'], __FILE__, __LINE__, __METHOD__,10);
		}

		$viewData['exceptions'] = $exceptions;
		$viewData['pay_period_data'] = $pay_period_data;
		$viewData['current_epoch'] = TTDate::getTime();
		$viewData['ppf'] = $ppf;
        return view('payperiod/ViewPayPeriod', $viewData);

    }

	public function submit(){
		$pplf = new PayPeriodListFactory();
		$pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() );
		foreach ($pplf->rs as $pay_period_obj) {
			$pplf->data = (array)$pay_period_obj;
			$pay_period_obj = $pplf;

			$pay_period_obj->setStatus( $status_id );
			$pay_period_obj->save();
		}

		Redirect::Page( URLBuilder::getURL( array('pay_period_id' => $pay_period_id), 'ViewPayPeriod.php') );

	}

	public function generate_paystubs(){
		Debug::Text('Generate Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

		Redirect::Page( URLBuilder::getURL( array('action' => 'generate_paystubs', 'pay_period_ids' => $pay_period_id, 'next_page' => URLBuilder::getURL( array('filter_pay_period_id' => $pay_period_id ), '../pay_stub/PayStubList.php') ), '../progress_bar/ProgressBarControl.php') );
	}

	public function generate_midpay(){
		Debug::Text('Generate Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

		Redirect::Page( URLBuilder::getURL( array('action' => 'generate_paymiddle', 'pay_period_ids' => $pay_period_id, 'next_page' => URLBuilder::getURL( array('filter_pay_period_id' => $pay_period_id ), '../pay_stub/PayStubList.php') ), '../progress_bar/ProgressBarControl.php') );
	}

	public function import(){
		//Imports already created shifts in to this pay period, from another pay period.
		//Get all users assigned to this pay period schedule.
		$pplf = new PayPeriodListFactory();
		$pay_period_obj = $pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() )->getCurrent();

		$pay_period_obj->importData();

		Redirect::Page( URLBuilder::getURL( array('pay_period_id' => $pay_period_id), 'ViewPayPeriod.php') );
	}

	public function delete_data(){
		//Deletes all data assigned to this pay period.
		//Get all users assigned to this pay period schedule.
		$pplf = new PayPeriodListFactory();
		$pay_period_obj = $pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() )->getCurrent();

		$pay_period_obj->deleteData();

		Redirect::Page( URLBuilder::getURL( array('pay_period_id' => $pay_period_id), 'ViewPayPeriod.php') );
	}

}

?>