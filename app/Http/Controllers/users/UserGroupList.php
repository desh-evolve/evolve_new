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

	public function delete($id)
	{
		$current_company = $this->currentCompany;

		try {
			if (empty($id)) {
				return response()->json([
					'success' => false,
					'error' => 'No User group selected.'
				], 400);
			}

			$uglf = new UserGroupListFactory();
			$uglf->getByIdAndCompanyId($id, $current_company->getId());

			if (empty($uglf->rs)) {
				return response()->json([
					'success' => false,
					'error' => 'User group not found.'
				], 404);
			}

			$success = false;
			foreach ($uglf->rs as $user_group) {
				$uglf->data = (array)$user_group;
				$user_group = $uglf;

				$user_group->setDeleted(true);
				$res = $user_group->Save();

				if ($res) {
					$success = true;
				}
			}

			return response()->json([
				'success' => $success,
				'message' => $success ? 'User group deleted successfully.' : 'User group deletion failed.'
			]);
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'error' => 'Server error: ' . $e->getMessage()
			], 500);
		}
	}
}
