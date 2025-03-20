<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Core\BreadCrumb;
use App\Models\Core\Debug;
use App\Models\Core\Pager;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use App\Models\Company\BranchBankAccountListFactory;
use App\Models\Company\BranchListFactory;
use Illuminate\Support\Facades\View;

class BranchBankAccountList extends Controller
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

	/**
	 * Default index method to handle the main logic.
	 */
	public function index(Request $request)
	{
		$current_company = $this->company;
		$current_user_prefs = $this->userPrefs;
		// // Permission check
		// if (!$this->permission->Check('branch', 'enabled') ||
		//     !($this->permission->Check('branch', 'view') || $this->permission->Check('branch', 'view_own'))) {
		//     return $this->permission->Redirect(false);
		// }

		// Extract form variables
		extract(FormVariables::GetVariables([
			'action',
			'page',
			'sort_column',
			'sort_order',
			'id',
			'branch_id_new',
			'ids'
		]));

		URLBuilder::setURL($request->getScriptName(), [
			'sort_column' => $sort_column,
			'sort_order' => $sort_order,
			'page' => $page,
		]);
		$id = $request->id;

		Debug::Arr($ids, 'Selected Objects', __FILE__, __LINE__, __METHOD__, 10);

		// Handle default logic
		$sort_array = $sort_column != '' ? [Misc::trimSortPrefix($sort_column) => $sort_order] : NULL;

		$bbalf = new BranchBankAccountListFactory();
		// dd($current_user_prefs);
		$bbalf->getByBranchId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array);

		$pager = new Pager($bbalf);

		$bankAccounts = [];
		// if ($bbalf->getRecordCount() > 0) {
		foreach ($bbalf->rs as $bank) {
			$bbalf->data = (array)$bank;
			// print_r($blf->rs);
			// exit;
			$bankAccounts[] = [
				'id' => $bbalf->GetId(),
				'transit' => $bbalf->getTransit(),
				'bank_name' => $bbalf->getBankName(),
				'bank_branch' => $bbalf->getBankBranch(),
				'account' => $bbalf->getAccount(),
			];
		}
		// }

		$blf = new BranchListFactory();
		$blf->getById($id);
		$company_branch_name = $blf->getById($id)->getCurrent()->getName();
		View::share('company_branch_name', $company_branch_name);

		$data = [
			'title' => TTi18n::gettext('Bank Account List'),
			'bankAccounts' => $bankAccounts,
			'branch_id_new' => $id,
			'sort_column' => $sort_column,
			'sort_order' => $sort_order,
			'paging_data' => $pager->getPageVariables()
		];
		return view('branch.BranchBankAccountList', $data);
	}

	/**
	 * Handle the "add" action.
	 */
	public function add(Request $request)
	{
		// Permission check
		if (
			!$this->permission->Check('branch', 'enabled') ||
			!($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))
		) {
			return $this->permission->Redirect(false);
		}

		// Extract form variables
		extract(FormVariables::GetVariables([
			'branch_id_new'
		]));

		return Redirect::to(URLBuilder::getURL(['branch_id_new' => $branch_id_new], 'EditBankAccount.php', FALSE));
	}

	/**
	 * Handle the "delete" action.
	 */
	public function delete(Request $request)
	{
		// Permission check
		if (
			!$this->permission->Check('branch', 'enabled') ||
			!($this->permission->Check('branch', 'delete') || $this->permission->Check('branch', 'delete_own'))
		) {
			return $this->permission->Redirect(false);
		}

		// Extract form variables
		extract(FormVariables::GetVariables([
			'ids'
		]));

		$delete = true; // Assume delete action
		$bbalf = new BranchBankAccountListFactory();

		if (isset($ids) && is_array($ids)) {
			foreach ($ids as $id1) {
				$bbalf->getById($id1);
				foreach ($bbalf as $branch) {
					$branch->setDeleted($delete);
					$branch->Save();
				}
			}
		}

		return Redirect::to(URLBuilder::getURL([], 'BranchList.php'));
	}
}
