<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\BonusDecemberListFactory;
use Illuminate\Support\Facades\View;
use App\Models\Users\UserListFactory;

class BonusCalc extends Controller
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

	public function index()
	{
		$permission = $this->permission;
		$current_user = $this->currentUser;
		$current_company = $this->currentCompany;
		$current_user_prefs = $this->userPrefs;

		if (
			!$permission->Check('company', 'enabled')
			or !($permission->Check('company', 'view') or $permission->Check('company', 'view_own') or $permission->Check('company', 'view_child'))
		) {
			$permission->Redirect(FALSE); //Redirect
		}

		$viewData['title'] = 'Bonus Calculation';

		extract(FormVariables::GetVariables(
			array(
				'action',
				'page',
				'sort_column',
				'sort_order',
				'filter_user_id',
				'ids',
			)
		));

		URLBuilder::setURL(
			$_SERVER['SCRIPT_NAME'],
			array(
				'filter_user_id' => $filter_user_id,
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			)
		);


		$sort_array = NULL;
		if ($sort_column != '') {
			$sort_array = array($sort_column => $sort_order);
		}

		Debug::Arr($ids, 'Selected Objects', __FILE__, __LINE__, __METHOD__, 10);

	   $action = '';
        if (isset($_POST['action:submit'])) {
            $action = 'submit'; // Normalize to 'submit' for action:submit
        } elseif (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';

		//==================================================================================
		switch ($action) {
			case 'add':
				Redirect::Page(URLBuilder::getURL(NULL, '/users/edit_bonus_calc'));
				break;

			default:

				$bdlf = new BonusDecemberListFactory();
				$bdlf->getByCompanyId($current_company->getId());
				$bonuses = array();

				foreach ($bdlf->rs as $bd_obj) {
					$bdlf->data = (array)$bd_obj;
					$bd_obj = $bdlf;

					$bonuses[] = array(
						'id' => $bd_obj->getId(),
						'company_id' => $bd_obj->getCompany(),
						'y_number' => $bd_obj->getYNumber(),
						'start_date' => $bd_obj->getStartDate(),
						'end_date' => $bd_obj->getEndDate(),
					);
				}

				$viewData['bonuses'] = $bonuses;
                // dd($viewData);
				break;
		}
		$viewData['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );

		return view('users/bonusCalc', $viewData);
	}

}
