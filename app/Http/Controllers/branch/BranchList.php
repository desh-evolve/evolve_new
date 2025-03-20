<?php

namespace App\Http\Controllers\branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use App\Models\Core\Environment;
use App\Models\Core\BreadCrumb;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class BranchList extends Controller
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

    public function index()
    {
        $current_company = $this->company;
        $current_user_prefs = $this->userPrefs;

        // // Permission check
        // if (!$this->permission->Check('branch', 'enabled') || 
        //     !($this->permission->Check('branch', 'view') || $this->permission->Check('branch', 'view_own'))) {
        //     return $this->permission->Redirect(false);
        // }

        extract(FormVariables::GetVariables([
            'action',
            'page',
            'sort_column',
            'sort_order',
            'ids'
        ]));

        URLBuilder::setURL($_SERVER['SCRIPT_NAME'], [
            'sort_column' => $sort_column,
            'sort_order' => $sort_order,
            'page' => $page
        ]);

        $sort_array = $sort_column != '' ? [$sort_column => $sort_order] : null;

        Debug::Arr($ids, 'Selected Objects', __FILE__, __LINE__, __METHOD__, 10);

        BreadCrumb::setCrumb('Branch List');

        $blf = new BranchListFactory();
        $blf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage() ?? null, $page, null, $sort_array);
        $pager = new Pager($blf);

        $branches = [];
        foreach ($blf->rs as $branch) {
            $blf->data = (array)$branch;
            // print_r($blf->data);
            // exit;
            $branches[] = [
                'id' =>  $blf->GetId(),
                'status_id' => $blf->getStatus(),
                'manual_id' => $blf->getManualID(),
                'name' => $blf->getName(),
                'city' => $blf->getCity(),
                'province' => $blf->getProvince(),
                'map_url' => $blf->getMapURL(),
                'deleted' => $blf->getDeleted(),
                'branch_short_id' => $blf->getBranchShortID() // Added for Thunder & Neon
            ];
        }

        $data = [
            'title' => 'Branch List',
            'branches' => $branches,
            'sort_column' => $sort_array['sort_column'] ?? '',
            'sort_order' => $sort_array['sort_order'] ?? '',
            'paging_data' => $pager->getPageVariables()
        ];

        return view('branch.BranchList', $data);
    }

    public function add()
    {
        Redirect::Page(URLBuilder::getURL(NULL, '/branch/add'));
    }

    public function delete($id)
    {
        $current_company = $this->company;

        if (empty($id)) {
            return response()->json(['error' => 'No branches selected.'], 400);
        }

        $blf = new BranchListFactory();
        $branch = $blf->getByIdAndCompanyId($id, $current_company->getId());

        foreach ($branch->rs as $b_obj) {
            $branch->data = (array)$b_obj; // Added because branch data is null and it gives an error

            $branch->setDeleted(true); // Set deleted flag to true

            if ($branch->isValid()) {
                $res = $branch->Save();

                if ($res) {
                    return response()->json(['success' => 'Branch deleted successfully.']);
                } else {
                    return response()->json(['error' => 'Branch deletion failed.']);
                }
            }
        }
    }
}
