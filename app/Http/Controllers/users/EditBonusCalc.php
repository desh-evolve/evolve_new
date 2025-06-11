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
use App\Models\Users\BonusDecemberFactory;
use App\Models\Users\BonusDecemberListFactory;
use Illuminate\Support\Facades\View;

class EditBonusCalc extends Controller
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

		$viewData['title'] = 'Bonus';


		extract(FormVariables::GetVariables(
			array(
				'action',
				'id',
				'view',
				'data'
			)
		));


		if (isset($data)) {
			if (isset($data['start_date'])) {
				$data['start_date'] = TTDate::parseDateTime($data['start_date']);
			}
			if (isset($data['end_date'])) {
				$data['end_date'] = TTDate::parseDateTime($data['end_date']);
			}
		}

		$bdf = new BonusDecemberFactory();

		//==================================================================================
		$action = '';
		if (isset($_POST['action'])) {
			$action = trim($_POST['action']);
		} elseif (isset($_GET['action'])) {
			$action = trim($_GET['action']);
		}
		$action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
		//==================================================================================
		switch ($action) {
			case 'submit':
				//Debug::setVerbosity(11);
				Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

				$bdf->StartTransaction();

				if ($data['id'] == '') {
					$bdf->setCompany($current_company->getId());
				} else {
					$bdf->setId($data['id']);
				}

				$bdf->setStartDate($data['start_date']);
				$bdf->setEndDate($data['end_date'] + 59);
				$bdf->setYNumber($data['y_number']);

				if ($bdf->isValid()) {
					$bdf->Save();

					$bdf->CommitTransaction();
					Redirect::Page(URLBuilder::getURL(NULL, '/users/bonus_calc'));
					break;
				}

				$bdf->FailTransaction();

			case 'generate_december_bonuses':

				Debug::Text('Generate Bonus!', __FILE__, __LINE__, __METHOD__, 10);

				return redirect()->to('/progress_bar_control?' . http_build_query([
						'action' => 'generate_december_bonuses',
						'filter_user_id' => $data['id'],
						'next_page' => url('/users/bonus_list') . '?dec_bo_id=' . $data['id'] . '&action=view'
					]));

				break;

			default:
				if (isset($id)) {

					$bdlf = new BonusDecemberListFactory();
					$bdlf->getByIdAndCompanyId($id, $current_company->getId());

					foreach ($bdlf->rs as $bd_obj) {
						$bdlf->data = (array)$bd_obj;
						$bd_obj = $bdlf;

						$data = array(
							'id' => $bd_obj->getId(),
							'company_id' => $bd_obj->getCompany(),
							'start_date' => $bd_obj->getStartDate(),
							'end_date' => $bd_obj->getEndDate(),
							'y_number' => $bd_obj->getYNumber(),
							'deleted' => $bd_obj->getDeleted(),
							'created_date' => $bd_obj->getCreatedDate(),
							'created_by' => $bd_obj->getCreatedBy(),
							'updated_date' => $bd_obj->getUpdatedDate(),
							'updated_by' => $bd_obj->getUpdatedBy(),
							'deleted_date' => $bd_obj->getDeletedDate(),
							'deleted_by' => $bd_obj->getDeletedBy()
						);
					}
				} elseif ($action != 'submit') {
					$data = array(
						'id' => '',
						'start_date' => '',
						'end_date' => '',
						'y_number' => ''

					);
				}
				$viewData['data'] = $data;

				break;
		}

		$viewData['bdf'] = $bdf;
		$viewData['view'] = $view;
		return view('users/EditBonusCalc', $viewData);
	}
}
