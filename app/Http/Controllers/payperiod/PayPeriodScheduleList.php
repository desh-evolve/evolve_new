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

class CurrencyList extends Controller
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

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids',
			) 
		) );
		
		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array (
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);		

		$ppslf = new PayPeriodScheduleListFactory();

		$ppslf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, array($sort_column => $sort_order) );

		$pager = new Pager($ppslf);

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
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('payperiod/PayPeriodScheduleList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL(NULL, 'EditPayPeriodSchedule.php', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;

		$delete = TRUE;

		$ppslf = new PayPeriodScheduleListFactory();

		foreach ($ids as $id) {
			$ppslf->GetByIdAndCompanyId($id, $current_company->getId() );
			foreach ($ppslf->rs as $pay_period_schedule) {
				$ppslf->data = (array)$pay_period_schedule;
				$pay_period_schedule = $ppslf;
				
				$pay_period_schedule->setDeleted($delete);
				$pay_period_schedule->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'PayPeriodScheduleList.php') );
	}
}


?>