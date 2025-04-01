<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Core\TTi18n;
use App\Models\Core\BreadCrumb;
use App\Models\Core\Debug;
use App\Models\Core\FastTree;
use App\Models\users\UserGroupListFactory;

class UserGroupList extends Controller
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

	public function index(Request $request)
	{
		$current_company = $this->currentCompany;
		$current_user_prefs = $this->userPrefs;
		// Get user groups by company ID
		$uglf = new UserGroupListFactory();
		$nodes = FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'HTML');


		// Default case - show list
		$uglf = new UserGroupListFactory();
		$nodes = FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'HTML');

		$viewData = [
			'title' => 'User Groups',
			'rows' => $nodes ?? [],
			'sort_column' => request('sort_column', ''),
			'sort_order' => request('sort_order', ''),
			'permission' => $this->permission
		];

		return view('users.UserGroupList', $viewData);
	}

	public function add()
	{
		return Redirect::Page(URLBuilder::getURL(null, 'EditUserGroup.php', false));
	}

	// public function delete($ids, $delete)
	// {
	// 	$current_company = $this->currentCompany;
	// 	$uglf = new UserGroupListFactory();

	// 	foreach ($ids as $id) {
	// 		$uglf->getById($id);
	// 		foreach ($uglf as $obj) {
	// 			$obj->setDeleted($delete);
	// 			$obj->Save();
	// 		}
	// 	}

	// 	return Redirect::Page(URLBuilder::getURL(null, 'UserGroupList.php'));
	// }

	public function delete($id, $delete = true)
	{
		$current_company = $this->currentCompany;

		if (empty($id)) {
			return response()->json(['error' => 'No Group selected.'], 400);
		}

		$utlf = new UserGroupListFactory();

			$user_group = $utlf->GetByIdAndCompanyId($id, $current_company->getId());

			foreach ($user_group->rs as $g_obj) {
				$user_group->data = (array)$g_obj; // added bcz currency data is null and it gives an error
				
				$user_group->setDeleted(true); // Set deleted flag to true
	
				if ($user_group->isValid()) {
					$res = $user_group->Save();
					
					if($res){
						return response()->json(['success' => 'Group deleted successfully.']);
					}else{
						return response()->json(['error' => 'Group deleted failed.']);
					}
				}
			}

		return response()->json(['success' => 'Operation completed successfully.']);
	}

}
