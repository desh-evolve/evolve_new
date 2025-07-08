<?php

namespace App\Http\Controllers\station;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Station;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use App\Models\Core\Environment;
use App\Models\Core\BreadCrumb;
use App\Models\Core\StationFactory;
use App\Models\Core\StationListFactory;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class StationList extends Controller
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

    public function index() {

        $current_company = $this->company;
        $current_user_prefs = $this->userPrefs;

        // Get request variables
        $page = request('page', 1);
        $sort_column = request('sort_column', '');
        $sort_order = request('sort_order', '');
        $ids = request('ids', []);

        URLBuilder::setURL($_SERVER['SCRIPT_NAME'], [
            'sort_column' => $sort_column,
            'sort_order' => $sort_order,
            'page' => $page
        ]);

        $sort_array = $sort_column != '' ? [$sort_column => $sort_order] : null;

        Debug::Arr($ids, 'Selected Objects', __FILE__, __LINE__, __METHOD__, 10);

        BreadCrumb::setCrumb('Station List');
        // dd($current_user_prefs->getItemsPerPage());
        $slf = new StationListFactory();
        $slf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage() ?? null, $page, null, $sort_array);
        $pager = new Pager($slf);

        $stations = [];
        foreach ($slf->rs as $s_obj) {
            $slf->data = (array)$s_obj;
            $s_obj = $slf;
			// dd($s_obj);
            $stations[] = [
                'id' => $s_obj->id,
                'type' => Option::getByKey($s_obj->type_id, $slf->getOptions('type')),
                'status' => Option::getByKey($s_obj->status_id, $slf->getOptions('status')),
                'source' => $s_obj->source,
                'station' => $s_obj->station_id,
                'short_station' => Misc::TruncateString($s_obj->station_id, 15),
                'description' => Misc::TruncateString($s_obj->description, 30),
                'deleted' => $s_obj->deleted
            ];
        }

        $viewData = [
            'title' => 'Station List',
            'stations' => $stations,
            'sort_column' => $sort_column,
            'sort_order' => $sort_order,
            'paging_data' => $pager->getPageVariables()
        ];

        return view('station.StationList', $viewData);
    }

    public function add() {
        Redirect::Page(URLBuilder::getURL(NULL, '/station/add'));
    }

    public function delete($id) {
        $current_company = $this->company;

        if (empty($id)) {
            return response()->json(['error' => 'No stations selected.'], 400);
        }

        $slf = new StationListFactory();
        $station = $slf->getByIdAndCompanyId($id, $current_company->getId());

        foreach ($station->rs as $s_obj) {
            $station->data = (array)$s_obj;
            $station->setDeleted(true);

            if ($station->isValid()) {
                $res = $station->Save();

                if($res){
                    return response()->json(['success' => 'Station deleted successfully.']);
                }else{
                    return response()->json(['error' => 'Station deletion failed.']);
                }
            }
        }
    }
}
