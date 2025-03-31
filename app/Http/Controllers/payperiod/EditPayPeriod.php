<?php

namespace App\Http\Controllers\payperiod;

use App\Http\Controllers\Controller;
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
use App\Models\PayPeriod\PayPeriodFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Users\UserListFactory;
use DateTime;
use Illuminate\Support\Facades\View;

class EditPayPeriod extends Controller
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

    public function index($pay_period_schedule_id, $id = null) {
        /*
        if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = !empty($id) ? 'Edit Pay Period' : 'Add Pay Period';
		$current_company = $this->currentCompany;

		if ( isset($data) ) {
			if ( isset($data['start_date']) ) {
				$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
			}
			if ( isset($data['end_date']) ) {
				$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
			}
			if ( isset($data['transaction_date']) ) {
				$data['transaction_date'] = TTDate::parseDateTime( $data['transaction_date'] );
			}
			if ( isset($data['advance_end_date']) ) {
				$data['advance_end_date'] = TTDate::parseDateTime( $data['advance_end_date'] );
			}
			if ( isset($data['advance_transaction_date']) ) {
				$data['advance_transaction_date'] = TTDate::parseDateTime( $data['advance_transaction_date'] );
			}
		}
		
		$ppf = new PayPeriodFactory(); 

		if ( isset($id) ) {

			$pplf = new PayPeriodListFactory();
			$pplf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($pplf as $pp_obj) {
				$pplf->data = (array)$pp_obj;
				$pp_obj = $pplf;
//check here - date problem
				$data = array(
					'id' => $pp_obj->getId(),
					'company_id' => $pp_obj->getCompany(),
					'pay_period_schedule_id' => $pp_obj->getPayPeriodSchedule(),
					'pay_period_schedule_type_id' => $pp_obj->getPayPeriodScheduleObject()->getType(),
					'start_date' => (new DateTime())->setTimestamp($pp_obj->getStartDate())->format('Y-m-d\TH:i'),
					'end_date' => date('Y-m-d\TH:i', $pp_obj->getEndDate()),
					'transaction_date' => date('Y-m-d\TH:i', $pp_obj->getTransactionDate()),
					'advance_end_date' => date('Y-m-d\TH:i', $pp_obj->getAdvanceEndDate()),
					'advance_transaction_date' => date('Y-m-d\TH:i', $pp_obj->getAdvanceTransactionDate()),
					'deleted' => $pp_obj->getDeleted(),
					'created_date' => $pp_obj->getCreatedDate(),
					'created_by' => $pp_obj->getCreatedBy(),
					'updated_date' => $pp_obj->getUpdatedDate(),
					'updated_by' => $pp_obj->getUpdatedBy(),
					'deleted_date' => $pp_obj->getDeletedDate(),
					'deleted_by' => $pp_obj->getDeletedBy()
				);
			}
		} else {
			if ( isset($pay_period_schedule_id) AND $pay_period_schedule_id != '') {
				$ppslf = new PayPeriodScheduleListFactory();
				$ppslf->getByIdAndCompanyId( $pay_period_schedule_id, $current_company->getId() );
				if ( $ppslf->getRecordCount() > 0 ) {
					$data['pay_period_schedule_type_id'] = $ppslf->getCurrent()->getType();
				}

				$data['pay_period_schedule_id'] = $pay_period_schedule_id;

				//Get end date of previous pay period, and default the start date of the new pay period to that.
				$pplf = new PayPeriodListFactory();
				$pplf->getByPayPeriodScheduleId( $pay_period_schedule_id, 1, NULL, NULL, array('start_date' => 'desc') );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach( $pplf->rs as $pp_obj) {
						$pplf->data = (array)$pp_obj;
						$pp_obj = $pplf;
						
						$data['start_date'] = date('Y-m-d', $pp_obj->getEndDate()+1);
						$data['end_date'] = date('Y-m-d', $pp_obj->getEndDate()+86400);
					}
				}
			}
		}

		$viewData['data'] = $data;
		$viewData['ppf'] = $ppf;
        return view('payperiod/EditPayPeriod', $viewData);

    }

	public function submit(Request $request){
		$ppf = new PayPeriodFactory();		
		$data = $request->data;
		$current_company = $this->currentCompany;

		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ppf->StartTransaction();

		if ( $data['id'] == '' ) {
			$ppf->setCompany( $current_company->getId() );
			$ppf->setStatus(10); //Open
		} else {
			$ppf->setId($data['id']);
		}

		$ppf->setPayPeriodSchedule($data['pay_period_schedule_id']);
		if ( is_object( $ppf->getPayPeriodScheduleObject() ) ) {
			$ppf->getPayPeriodScheduleObject()->setPayPeriodTimeZone();
		}
		
		$ppf->setStartDate($data['start_date']);// date+time
		$ppf->setEndDate($data['end_date']+59); // date+time
		$ppf->setTransactionDate($data['transaction_date']+59);// date+time - 59 seconds added to the time 23:59 -> 23:59:59 (H:i => H:i:s)

		if ( isset($data['advance_end_date']) ) {
			$ppf->setAdvanceEndDate($data['advance_end_date']);
		}
		if ( isset($data['advance_transaction_date']) ) {
			$ppf->setAdvanceTransactionDate($data['advance_transaction_date']);
		}

		$ppf->setEnableImportData( TRUE ); //Import punches when creating new pay periods.

		if ( $ppf->isValid() ) {
			$ppf->Save();

			$ppf->CommitTransaction();
			return redirect(URLBuilder::getURL( array('id' => $data['pay_period_schedule_id'] ), '/payroll/pay_periods'));
		}

		$ppf->FailTransaction();
	}
}

?>