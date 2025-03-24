<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Company\WageGroupFactory;
use App\Models\Company\WageGroupListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class EditWageGroup extends Controller
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

		/*
        if ( !$permission->Check('wage','enabled')
				OR !( $permission->Check('wage','edit') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
	}

	public function index($id = null)
	{

		$current_company = $this->currentCompany;
		if (isset($id)) {

			$wglf = new WageGroupListFactory();

			$wglf->GetByIdAndCompanyId($id, $current_company->getId());
			$wage_group = $wglf->rs ?? [];
			if ($wage_group) {
				foreach ($wage_group as $g_obj) {
					//Debug::Arr($title_obj,'Title Object', __FILE__, __LINE__, __METHOD__,10);

					$data = array(
						'id' => $g_obj->id,
						'name' => $g_obj->name,
						'created_date' => $g_obj->created_date,
						'created_by' => $g_obj->created_by,
						'updated_date' => $g_obj->updated_date,
						'updated_by' => $g_obj->updated_by,
						'deleted_date' => $g_obj->deleted_date,
						'deleted_by' => $g_obj->deleted_by
					);
				}
			}
		} else {
			$data = array(
				'name' => ''
			);
		}

		$viewData = [
            'title' => $id ? 'Edit Wage Group' : 'Add Wage Group',
            'data' => $data,
        ];

		return view('company/EditWageGroup', $viewData);
	}

	public function save(Request $request, $id = null)
	{
		$current_company = $this->currentCompany;
		// $group_data = $request->data;

		$data = $request->all();
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

		$wgf = new WageGroupFactory();

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);
		$wgf->setId($id ?? null);
		$wgf->setCompany($current_company->getId());
		$wgf->setName($data['name'] ?? '');

		if ($wgf->isValid()) {
		// 	dd('sss');
			$wgf->Save();
			return redirect()->to(URLBuilder::getURL(null, '/wage_group'))->with('success', 'Wage Group saved successfully.');
		}
		// else{
		// 	dd('ccc');
		return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
		// }
	}
}
