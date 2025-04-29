<?php

namespace App\Http\Controllers\currency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use App\Models\Core\Environment;
use App\Models\Core\BreadCrumb;
use App\Models\Core\CurrencyFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class CurrencyList extends Controller
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
        /*
        if (!$this->permission->Check('currency', 'enabled') ||
            !($this->permission->Check('currency', 'view') || $this->permission->Check('currency', 'view_own'))) {
            return $this->permission->Redirect(false);
        }
        */

         // Initialize sort column and sort order with default values or from the request
        $sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'name'; // Default sort by 'name'
        $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'asc'; // Default sort order 'asc'
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // Default page is 1 if not set
        // Example: Define $ids, assuming we are getting selected ids from the query string or any other source
        $ids = isset($_GET['ids']) ? $_GET['ids'] : []; // Fetch selected IDs from URL or set empty array if not provided

        // Debugging (if you need to debug $ids)
        Debug::Arr($ids, 'Selected Objects', __FILE__, __LINE__, __METHOD__, 10);

        $current_company = $this->company;
        $current_user_prefs = $this->userPrefs;

        $viewData['title'] = 'Currency List';


        BreadCrumb::setCrumb('Currency List');

        $clf = new CurrencyListFactory();
        $clf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage() ?? null, $page, null, $sort_array);
        $pager = new Pager($clf);
        $iso_code_options = $clf->getISOCodesArray();

        $currencies = [];
        $base_currency = false;

        foreach ($clf->rs as $c_obj) {
            if ($c_obj->is_base) {
                $base_currency = true;
            }
            $currencies[] = [
                'id' => $c_obj->id,
                'status_id' => $c_obj->status_id,
                'name' => $c_obj->name,
                'iso_code' => $c_obj->iso_code,
                'currency_name' => Option::getByKey($c_obj->iso_code, $iso_code_options),
                'conversion_rate' => $c_obj->conversion_rate,
                'auto_update' => $c_obj->auto_update,
                'is_base' => $c_obj->is_base,
                'is_default' => $c_obj->is_default,
                'deleted' => $c_obj->deleted
            ];
        }

        $viewData = [
            'title' => 'Currency List',
            'currencies' => $currencies,
            'base_currency' => $base_currency,
            'sort_column' => $sort_array['sort_column'] ?? '',
            'sort_order' => $sort_array['sort_order'] ?? '',
            'paging_data' => $pager->getPageVariables()
        ];

        return view('currency.CurrencyList', $viewData);
    }

    public function update_rates(){
        $current_company = $this->company;
        CurrencyFactory::updateCurrencyRates($current_company->getId());
        Redirect::Page(URLBuilder::getURL(NULL, '/currency'));
    }

    public function add(){
        Redirect::Page(URLBuilder::getURL(NULL, '/currency/add'));
    }

    public function delete($id)
    {
        $current_company = $this->company;

        if (empty($id)) {
            return response()->json(['error' => 'No currencies selected.'], 400);
        }

        $clf = new CurrencyListFactory();
        $currency = $clf->getByIdAndCompanyId($id, $current_company->getId());

        foreach ($currency->rs as $c_obj) {
            $currency->data = (array)$c_obj; // added bcz currency data is null and it gives an error

            $currency->setDeleted(true); // Set deleted flag to true

            if ($currency->isValid()) {
                $res = $currency->Save();

                if($res){
                    return response()->json(['success' => 'Currency deleted successfully.']);
                }else{
                    return response()->json(['error' => 'Currency deleted failed.']);
                }
            }
        }

    }
}
