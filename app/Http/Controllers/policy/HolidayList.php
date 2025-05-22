<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Holiday\HolidayListFactory;
use Carbon\Carbon;
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

    public function index($id = null) {
        /*
        if ( !$permission->Check('holiday_policy','enabled')
				OR !( $permission->Check('holiday_policy','view') OR $permission->Check('holiday_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		if(empty($id)){
			dd('No Holiday Policy ID');
		}

        $viewData['title'] = 'Holiday List';
		$current_company = $this->currentCompany;

		$hlf = new HolidayListFactory();

		$hlf->getByCompanyIdAndHolidayPolicyId( $current_company->getId(), $id );

        $rows = [];

		if ( $hlf->getRecordCount() > 0 ) {
			foreach ($hlf->rs as $h_obj) {
				$hlf->data = (array)$h_obj;
				$h_obj = $hlf;

				$rows[] = array(
					'id' => $h_obj->getId(),
					'date_stamp' => date('Y-m-d', $h_obj->getDateStamp()),
					'name' => $h_obj->getName(),
					'deleted' => $h_obj->getDeleted()
				);

			}
		}

		$viewData['holiday_policy_id'] = $id;
		$viewData['rows'] = $rows;

        return view('policy/HolidayList', $viewData);

    }

	public function delete($id, $holiday_policy_id){
		if (empty($id)) {
            return response()->json(['error' => 'No Holiday Selected.'], 400);
        }

		$delete = TRUE;

		$hlf = new HolidayListFactory();
		$hlf->getById($id );

		foreach ($hlf->rs as $h_obj) {
			$hlf->data = (array)$h_obj;
			$h_obj = $hlf;

			$h_obj->setDeleted($delete);
			if ( $h_obj->isValid() ) {
				$res = $h_obj->Save();

				if($res){
					return response()->json(['success' => 'Holiday Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Holiday Deleted Failed.']);
				}
			}
		}
		return redirect(URLBuilder::getURL( array('id' => $holiday_policy_id ), 'HolidayList'));

	}

}


?>
