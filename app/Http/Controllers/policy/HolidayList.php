<?php

namespace App\Http\Controllers\policy;

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
use App\Models\Holiday\HolidayListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class HolidayList extends Controller
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
        if ( !$permission->Check('holiday_policy','enabled')
				OR !( $permission->Check('holiday_policy','view') OR $permission->Check('holiday_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Holiday List';
		$current_company = $this->currentCompany;

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'holiday_policy_id',
				'id',
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
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		$hlf = new HolidayListFactory();
		$hlf->getByCompanyIdAndHolidayPolicyId( $current_company->getId(), $id );

		$pager = new Pager($hlf);
		
		if ( $hlf->getRecordCount() > 0 ) {
			foreach ($hlf->rs as $h_obj) {
				$hlf->data = (array)$h_obj;
				$h_obj = $hlf;

				$rows[] = array(
					'id' => $h_obj->getId(),
					'date_stamp' => $h_obj->getDateStamp(),
					'name' => $h_obj->getName(),
					'deleted' => $h_obj->getDeleted()
				);

			}
		}

		$viewData['holiday_policy_id'] = $id;
		$viewData['rows'] = $rows;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('policy/HolidayList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( array('holiday_policy_id' => $holiday_policy_id ), 'EditHoliday', FALSE) );
	}

	public function delete(){
		$delete = TRUE;

		$hlf = new HolidayListFactory();

		foreach ($ids as $id) {
			$hlf->getById($id );
			foreach ($hlf->rs as $h_obj) {
				$hlf->data = (array)$h_obj;
				$h_obj = $hlf;

				$h_obj->setDeleted($delete);
				if ( $h_obj->isValid() ) {
					$h_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( array('id' => $holiday_policy_id ), 'HolidayList') );

	}

}


?>