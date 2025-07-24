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
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class PayPeriodScheduleList extends Controller
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
				OR !( $permission->Check('pay_period_schedule','view') OR $permission->Check('pay_period_schedule','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Pay Period Schedule List';
		$current_company = $this->currentCompany;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getByCompanyId($current_company->getId());

		foreach ($ppslf->rs as $pay_period_schedule) {
			$ppslf->data = (array)$pay_period_schedule;
			$pay_period_schedule = $ppslf;

			$pay_period_schedules[] = array(
				'id' => $pay_period_schedule->getId(),
				'company_id' => $pay_period_schedule->getCompany(),
				'name' => $pay_period_schedule->getName(),
				'description' => $pay_period_schedule->getDescription(),
				'type' => Option::getByKey($pay_period_schedule->getType(), $pay_period_schedule->getOptions('type') ),
				/*
				'anchor_date' => TTDate::getDate( 'DATE', $pay_period_schedule->getAnchorDate() ),
				'primary_date' => TTDate::getDate( 'DATE', $pay_period_schedule->getPrimaryDate() ),
				'primary_transaction_date' => TTDate::getDate( 'DATE', $pay_period_schedule->getPrimaryTransactionDate() ),
				'secondary_date' => TTDate::getDate( 'DATE', $pay_period_schedule->getSecondaryDate() ),
				'secondary_transaction_date' => TTDate::getDate( 'DATE', $pay_period_schedule->getSecondaryTransactionDate() ),
				*/
				'deleted' => $pay_period_schedule->getDeleted()
			);

		}

		$viewData['pay_period_schedules'] = $pay_period_schedules;

        return view('payperiod/PayPeriodScheduleList', $viewData);

    }

	public function delete($id){
		$current_company = $this->currentCompany;

		$delete = TRUE;

		$ppslf = new PayPeriodScheduleListFactory();

		$ppslf->GetByIdAndCompanyId($id, $current_company->getId() );
		foreach ($ppslf->rs as $pay_period_schedule) {
			$ppslf->data = (array)$pay_period_schedule;
			$pay_period_schedule = $ppslf;

			$pay_period_schedule->setDeleted($delete);
			$res = $pay_period_schedule->Save();

            if($res){
                return response()->json(['success' => 'recurring_pay_stub_amendment deleted successfully.']);
            }else{
                return response()->json(['error' => 'recurring_pay_stub_amendment deleted failed.']);
            }
		}

		return redirect(URLBuilder::getURL(NULL, '/payroll/pay_period_schedules'));
	}
}


?>
