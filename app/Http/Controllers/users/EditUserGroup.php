<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\URLBuilder;
use App\Models\Core\FastTree;
use App\Models\users\UserGroupListFactory;
use App\Models\users\UserGroupFactory;
use Illuminate\Support\Facades\View;

class EditUserGroup extends Controller
{
	protected $permission;
	protected $company;
	protected $userPrefs;
	protected $userGroupFactory;
	protected $userGroupListFactory;

	public function __construct()
	{
		$basePath = Environment::getBasePath();
		require_once($basePath . '/app/Helpers/global.inc.php');
		require_once($basePath . '/app/Helpers/Interface.inc.php');

		$this->userPrefs = View::shared('current_user_prefs');
		$this->company = View::shared('current_company');
		$this->permission = View::shared('permission');
	}

	// public function index($id = null)
	// {
	// 	/*
	//     if (!$this->permission->Check('user', 'enabled')
	//         || !($this->permission->Check('user', 'edit') || $this->permission->Check('user', 'edit_own'))) {
	//         $this->permission->Redirect(FALSE);
	//     }
	//     */

	// 	$current_company = $this->company;

	// 	$ugf = new UserGroupFactory();

	// 	// Edit mode: Fetch existing user group data
	// 	$uglf = new UserGroupListFactory();

	// 	$nodes = FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'TEXT', TRUE);

	// 	// Alternatively, if the data is not in a collection, you can loop through and format it manually:
	// 	$parent_list_options = [];
	// 	foreach ($nodes as $node) {
	// 		$parent_list_options[$node->id] = $node->text;
	// 	}

	// 	if (isset($id) ) {





	// 		$uglf->getByIdAndCompanyId($id, $this->company);

	// 		$user_group = $uglf->rs ?? [];
	// 		if ($user_group) {
	// 			foreach ($user_group as $group_obj) {
	// 				$ft = new FastTree(); // Assuming FastTree doesn't need options here
	// 				$ft->setTree($this->company);
	// 				$parent_id = $ft->getParentID($group_obj->getId());

	// 				$data = [
	// 					'id' => $group_obj->getId(),
	// 					'parent_id' => $parent_id,
	// 					'previous_parent_id' => $parent_id,
	// 					'name' => $group_obj->getName(),
	// 					'created_date' => $group_obj->getCreatedDate(),
	// 					'created_by' => $group_obj->getCreatedBy(),
	// 					'updated_date' => $group_obj->getUpdatedDate(),
	// 					'updated_by' => $group_obj->getUpdatedBy(),
	// 					'deleted_date' => $group_obj->getDeletedDate(),
	// 					'deleted_by' => $group_obj->getDeletedBy()
	// 				];
	// 			}
	// 		}
	// 	} else {
	// 		// Add mode: Set default values
	// 		$data = [
	// 			'parent_id' => '',
	// 			'name' => ''
	// 		];
	// 	}

	// 	// Select box options for parent groups
	// 	$uglf = new UserGroupListFactory();
	// 	$nodes = FastTree::FormatArray($uglf->getByCompanyIdArray($this->company), 'TEXT', TRUE);
	// 	$parent_list_options = [];
	// 	foreach ($nodes as $node) {
	// 		$parent_list_options[$node['id']] = $node['text'];
	// 	}

	// 	$data['parent_list_options'] = $parent_list_options;

	// 	$viewData = [
	// 		'title' => $id ? 'Edit Employee Group' : 'Add Employee Group',
	// 		'data' => $data,
	// 	];

	// 	return view('users.EditUserGroup', $viewData);
	// }

	public function index($id = null)
	{
		$current_company = $this->company;
		$ugf = new UserGroupFactory();
		$uglf = new UserGroupListFactory();

		$nodes = FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'TEXT', TRUE);

		$parent_list_options = [];
		foreach ($nodes as $node) {
			   $cleanText = str_replace('|  &nbsp;', '', $node['text']);
			   $parent_list_options[$node['id']] = $cleanText;
		}
		$data = [];
		if ($id) {

			$ft = new FastTree();
			$ft->setTree($current_company->getID());
			$uglf->getById($id);
			// dd($uglf);
			foreach ($uglf->rs as $group_obj) {
				$uglf->data = (array)$group_obj;
				$group_obj = $uglf;
				$parent_id = $ft->getParentID($group_obj->getId());

				$data = [
					'id' => $group_obj->getId(),
					'previous_parent_id' => $parent_id,
					'parent_id' => $parent_id,
					'name' => $group_obj->getName(),
					'created_date' => $group_obj->getCreatedDate(),
					'created_by' => $group_obj->getCreatedBy(),
					'updated_date' => $group_obj->getUpdatedDate(),
					'updated_by' => $group_obj->getUpdatedBy(),
					'deleted_date' => $group_obj->getDeletedDate(),
					'deleted_by' => $group_obj->getDeletedBy()
				];
			}
		}
		$data['parent_list_options'] = $parent_list_options;
		$viewData = [
			'title' => $id ? 'Edit Employee Group' : 'Add Employee Group',
			'data' => $data
		];

		return view('users.EditUserGroup', $viewData);
	}

	public function submit(Request $request, $id = null)
	{
		$current_company = $this->company;

		$data = $request->all();
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

		$ugf = new UserGroupFactory();

		$ugf->setId($id ?? null);
		$ugf->setCompany($current_company->getId());
		$ugf->setPreviousParent($data['previous_parent_id'] ?? '');
		$ugf->setParent($data['parent_id'] ?? '');
		$ugf->setName($data['name'] ?? '');

		if ($ugf->isValid()) {
			
            $ugf->Save();
            // return redirect()->to(URLBuilder::getURL(null, '/user_group'))->with('success', 'User group saved successfully.');
        }
		// If validation fails, return back with errors
		return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
	}
}
