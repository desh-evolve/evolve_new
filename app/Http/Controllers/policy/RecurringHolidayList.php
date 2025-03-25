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
use App\Models\Core\Sort;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Holiday\RecurringHolidayFactory;
use App\Models\Holiday\RecurringHolidayListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class RecurringHolidayList extends Controller
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

        $viewData['title'] = 'Recurring Holiday List';
		$current_company = $this->currentCompany;


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
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		$rhlf = new RecurringHolidayListFactory();
		$rhlf->getByCompanyId( $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );


		$pager = new Pager($rhlf);

		//$type_options = $aplf->getOptions('type');

		foreach ($rhlf->rs as $rh_obj) {
			$rhlf->data = (array)$rh_obj;
			$rh_obj = $rhlf;

			$rows[] = array(
				'id' => $rh_obj->getId(),
				'name' => $rh_obj->getName(),
				'next_date' => $rh_obj->getNextDate( time() ),
				'deleted' => $rh_obj->getDeleted()
			);
		}
		
		//Special sorting since next_date is calculated outside of the DB.
		if ( $sort_column == 'next_date' ) {
			Debug::Text('Sort By Date!', __FILE__, __LINE__, __METHOD__,10);
			$rows = Sort::Multisort($rows, $sort_column, NULL, $sort_order);
		}

		$viewData['rows'] = $rows;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('policy/RecurringHolidayList', $viewData);

    }

	public function add_presets(){
		RecurringHolidayFactory::addPresets( $current_company->getId(), $current_company->getCountry() );

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringHolidayList') );
	}

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditRecurringHoliday', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;
		$delete = TRUE;

		$rhlf = new RecurringHolidayListFactory();

		foreach ($ids as $id) {
			$rhlf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($rhlf->rs as $rh_obj) {
				$rhlf->data = (array)$rh_obj;
				$rh_obj = $rhlf;

				$rh_obj->setDeleted($delete);
				if ( $rh_obj->isValid() ) {
					$rh_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringHolidayList') );

	}

}

?>