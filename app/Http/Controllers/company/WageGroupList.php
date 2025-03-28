<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Company\WageGroupListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class WageGroupList extends Controller
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
				OR !( $permission->Check('wage','view') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

        */
	}

	public function index()
    {
        $current_company = $this->currentCompany;
        $current_user_prefs = $this->userPrefs;
        $viewData['title'] = 'Wage Group List';

        // Set URL parameters for pagination and sorting
        URLBuilder::setURL(
            $_SERVER['SCRIPT_NAME'],
            array(
                'sort_column' => $sort_column,
                'sort_order' => $sort_order,
                'page' => $page
            )
        );

        // Prepare sorting array
        $sort_array = null;
        if ($sort_column != '') { // Fixed: Removed extra parenthesis
            $sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
        }

        // Fetch wage groups
        $wglf = new WageGroupListFactory();
        $wglf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, null, $sort_array);

        // Map wage group data
        $groups = array_map(function ($group_obj) {
            return [
                'id' => $group_obj->id,
                'name' => $group_obj->name,
                'deleted' => $group_obj->deleted,
            ];
        }, $wglf->rs);

        // Set up pagination
        $pager = new Pager($wglf);

        // Prepare view data
        $viewData['groups'] = $groups;
        $viewData['sort_column'] = $sort_column;
        $viewData['sort_order'] = $sort_order;
        $viewData['paging_data'] = $pager->getPageVariables();

        // Return the view
        return view('company.WageGroupList', $viewData);
    }

	public function add()
	{
		Redirect::Page(URLBuilder::getURL(NULL, 'EditWageGroup.php'));
	}

	public function delete($id)
    {
        $current_company = $this->currentCompany;

        if (empty($id)) {
            return response()->json(['error' => 'No wage group selected.'], 400);
        }

        $wglf = new WageGroupListFactory();
        $wage_group = $wglf->getByIdAndCompanyId($id, $current_company->getId());

        foreach ($wage_group->rs as $w_obj) {
            $wage_group->data = (array)$w_obj; // added bcz currency data is null and it gives an error

            $wage_group->setDeleted(true); // Set deleted flag to true

            if ($wage_group->isValid()) {
                $res = $wage_group->Save();

                if($res){
                    return response()->json(['success' => 'Wage Group deleted successfully.']);
                }else{
                    return response()->json(['error' => 'Wage Group deleted failed.']);
                }
            }
        }

    }
}
