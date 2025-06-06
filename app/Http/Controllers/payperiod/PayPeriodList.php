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
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class PayPeriodList extends Controller
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

    public function index($id) {
        /*
        if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','view') OR $permission->Check('pay_period_schedule','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$projected_pay_periods = [];
        $viewData['title'] = 'Pay Period List';	
		$current_user_prefs = $this->userPrefs;
		$current_company = $this->currentCompany;	

		$pplf = new PayPeriodListFactory(); 
		$ppslf = new PayPeriodScheduleListFactory();

		//$pplf->GetByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, array($sort_column => $sort_order) );
		//$pplf->GetByPayPeriodScheduleId($id, $current_user_prefs->getItemsPerPage(), $page, NULL, array($sort_column => $sort_order) );
		$pplf->getByPayPeriodScheduleId($id);

		if ( $pplf->getRecordCount() >= 1 ) {
			if ( is_numeric($projected_pay_periods) ) {
				$max_projected_pay_periods = $projected_pay_periods;
			} else {
				$max_projected_pay_periods = 1;
			}
		} else {
			$max_projected_pay_periods = 24;
		}

		Debug::Text('Projected Pay Periods: '. $max_projected_pay_periods, __FILE__, __LINE__, __METHOD__,10);

		//Now project in to the future X pay periods...
		$ppslf->getById($id);
		foreach ($ppslf->rs as $pay_period_schedule) {
			$ppslf->data = (array)$pay_period_schedule;
			$pay_period_schedule = $ppslf;

			if ( $pay_period_schedule->getType() != 5 ) {
				for ($i=0; $i < $max_projected_pay_periods;$i++) {
					if ($i == 0) {
						if ( !isset( $last_end_date ) ) {
							$last_end_date = NULL;
						}

						$pay_period_schedule->getNextPayPeriod( $last_end_date );
					} else {
						$pay_period_schedule->getNextPayPeriod( $pay_period_schedule->getNextEndDate() );
					}


					//$start_date = $pay_period_schedule->getNextStartDate();
					//$end_date = $pay_period_schedule->getNextEndDate();
					//$transaction_date = $pay_period_schedule->getNextTransactionDate();
					//echo "Start Date: $start_date<br>\n";

					$pay_periods[] = array(
						'id' => null,
						'company_id' => $pay_period_schedule->getCompany(),
						'pay_period_schedule_id' => $pay_period_schedule->getId(),
						'name' => $pay_period_schedule->getName(),
						'type' => Option::getByKey($pay_period_schedule->getType(), $pay_period_schedule->getOptions('type') ),
						'status' => 'N/A',
						'start_date' => TTDate::getDate( 'DATE+TIME', $pay_period_schedule->getNextStartDate() ),
						'end_date' => TTDate::getDate( 'DATE+TIME', $pay_period_schedule->getNextEndDate() ),
						'transaction_date' => TTDate::getDate( 'DATE+TIME', $pay_period_schedule->getNextTransactionDate() ),
						'deleted' => FALSE
					);

				}
			}
		}
		
		foreach ($pplf->rs as $pay_period) {
			$pplf->data = (array)$pay_period;
			$pay_period = $pplf;

			$pay_period_schedule = $ppslf->getById( $pay_period->getPayPeriodSchedule() )->getCurrent();
			//$pay_period_schedule = $ppslf->getCurrent();

			$pay_periods[] = array(
				'id' => $pay_period->getId(),
				'company_id' => $pay_period->getCompany(),
				'pay_period_schedule_id' => $pay_period->getPayPeriodSchedule(),
				'name' => $pay_period_schedule->getName(),
				'type' => Option::getByKey($pay_period_schedule->getType(), $pay_period_schedule->getOptions('type') ),
				'status' => Option::getByKey($pay_period->getStatus(), $pay_period->getOptions('status') ),
				'start_date' => TTDate::getDate( 'DATE+TIME', $pay_period->getStartDate() ),
				'end_date' => TTDate::getDate( 'DATE+TIME', $pay_period->getEndDate() ),
				'transaction_date' => TTDate::getDate( 'DATE+TIME', $pay_period->getTransactionDate() ),
				'deleted' => $pay_period->getDeleted()
			);

			$last_end_date = $pay_period->getEndDate();

		}
		unset($pay_period_schedule);

		$viewData['pay_periods'] = $pay_periods;
		$viewData['id'] = $id;
		
        return view('payperiod/PayPeriodList', $viewData);

    }

	public function delete($id, $pay_period_schedule_id){
		$pplf = new PayPeriodListFactory();
		$current_company = $this->currentCompany;
		$delete = TRUE;

		$pplf->StartTransaction();
		$pay_period_id = $id;

		$pplf->GetByIdAndCompanyId($pay_period_id, $current_company->getId() );
		foreach ($pplf->rs as $pay_period) {
			$pplf->data = (array)$pay_period;
			$pay_period = $pplf;

			$pay_period->setDeleted($delete);
			$pay_period->Save();
		}

		//$pplf->FailTransaction();
		$pplf->CommitTransaction();

		return redirect(URLBuilder::getURL( array('id' => $id), '/payroll/pay_periods/'.$pay_period_schedule_id) );
		Redirect::Page( );

	}

}


?>