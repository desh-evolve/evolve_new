<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Holiday\RecurringHolidayFactory;
use App\Models\Holiday\RecurringHolidayListFactory;
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

		$rhlf = new RecurringHolidayListFactory();
		$rhlf->getByCompanyId( $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

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

		$viewData['rows'] = $rows;

        return view('policy/RecurringHolidayList', $viewData);

    }

	public function add_presets(){
		RecurringHolidayFactory::addPresets( $current_company->getId(), $current_company->getCountry() );

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringHolidayList') );
	}

	public function delete($id){
		if (empty($id)) {
            return response()->json(['error' => 'No Recurring Holiday Selected.'], 400);
        }

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$rhlf = new RecurringHolidayListFactory();
		$rhlf->getByIdAndCompanyId($id, $current_company->getId() );

		foreach ($rhlf->rs as $rh_obj) {
			$rhlf->data = (array)$rh_obj;
			$rh_obj = $rhlf;

			$rh_obj->setDeleted($delete);
			if ( $rh_obj->isValid() ) {
				$res = $rh_obj->Save();

				if($res){
					return response()->json(['success' => 'Recurring Holiday Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Recurring Holiday Deleted Failed.']);
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringHolidayList') );

	}

}

?>