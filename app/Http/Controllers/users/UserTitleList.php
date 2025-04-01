<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users\UserTitle;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use App\Models\Core\Environment;
use App\Models\Core\BreadCrumb;
use App\Models\users\UserTitleFactory;
use App\Models\users\UserTitleListFactory;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class UserTitleList extends Controller
{
	protected $permission;
	protected $company;
	protected $userPrefs;

	public function __construct()
	{
		$basePath = Environment::getBasePath();
		require_once($basePath . '/app/Helpers/global.inc.php');
		require_once($basePath . '/app/Helpers/Interface.inc.php');

		$this->userPrefs = View::shared('current_user_prefs');
		$this->company = View::shared('current_company');
		$this->permission = View::shared('permission');
	}

	public function index(Request $request)
	{
		$current_company = $this->company;
		$current_user_prefs = $this->userPrefs;

		$viewData['title'] = __('Employee Title List');

		$current_company = $this->company;
		$current_user_prefs = $this->userPrefs;

		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage());
		// dd($utlf);

		$pager = new Pager($utlf);
		$titles = [];

		foreach ($utlf->rs as $title_obj) {
			$titles[] = [
				'id' => $title_obj->id,
				'name' => $title_obj->name,
				'deleted' => $title_obj->deleted
			];
		}

		BreadCrumb::setCrumb('Employee Title List');

		$viewData = [
			'title' => __('Employee Title List'),
			'titles' => $titles,
			'paging_data' => $pager->getPageVariables()
		];

		return view('users.UserTitleList', $viewData);
	}

	// protected function defaultAction($sort_column, $sort_order, $page) {}

	public function add()
	{
		return Redirect::Page(URLBuilder::getURL(null, '/user_titles/add'));
	}

	public function delete($id, $delete = true)
	{
		$current_company = $this->company;

		if (empty($id)) {
			return response()->json(['error' => 'No titles selected.'], 400);
		}

		$utlf = new UserTitleListFactory();

			$titles = $utlf->GetByIdAndCompanyId($id, $current_company->getId());

			foreach ($titles->rs as $title_obj) {
				$titles->data = (array)$title_obj; // added bcz currency data is null and it gives an error
				
				$titles->setDeleted(true); // Set deleted flag to true
	
				if ($titles->isValid()) {
					$res = $titles->Save();
					
					if($res){
						return response()->json(['success' => 'Titles deleted successfully.']);
					}else{
						return response()->json(['error' => 'Titles deleted failed.']);
					}
				}
			}

		return response()->json(['success' => 'Operation completed successfully.']);
	}
}
