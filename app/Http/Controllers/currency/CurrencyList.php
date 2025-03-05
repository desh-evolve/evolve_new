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
    
       
        //$permission;
        //$current_company;
        //$current_user_prefs;
        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
    }

    public function index()
    {
        // if (!$this->permission->Check('currency', 'enabled') || 
        //     !($this->permission->Check('currency', 'view') || $this->permission->Check('currency', 'view_own'))) {
        //     return $this->permission->Redirect(false);
        // }

        
        //$smarty->assign('title', TTi18n::gettext('Currency List'));

        extract(FormVariables::GetVariables([
            'action', 'page', 'sort_column', 'sort_order', 'ids'
        ]));

        URLBuilder::setURL($_SERVER['SCRIPT_NAME'], [
            'sort_column' => $sort_column,
            'sort_order' => $sort_order,
            'page' => $page
        ]);

        $sort_array = $sort_column != '' ? [$sort_column => $sort_order] : null;
        
        Debug::Arr($ids, 'Selected Objects', __FILE__, __LINE__, __METHOD__, 10);
        
        $action = Misc::findSubmitButton();
        $this->handleAction($action, $ids, $sort_array, $page);
    }

    private function handleAction($action, $ids, $sort_array, $page)
    {
        $current_company = $this->company;
        
        switch ($action) {
            case 'update_rates':
                CurrencyFactory::updateCurrencyRates($current_company->getId());
                Redirect::Page(URLBuilder::getURL(NULL, 'CurrencyList.php'));
                break;

            case 'add':
                Redirect::Page(URLBuilder::getURL(NULL, 'EditCurrency.php'));
                break;

            case 'delete':
            case 'undelete':
                $this->deleteOrUndelete($action, $ids);
                Redirect::Page(URLBuilder::getURL(NULL, 'CurrencyList.php'));
                break;

            default:
                $this->showCurrencyList($sort_array, $page);
                break;
        }
    }

    private function deleteOrUndelete($action, $ids)
    {
        global $current_company;

        $delete = strtolower($action) == 'delete';
        $clf = new CurrencyListFactory();

        if (!empty($ids) && is_array($ids)) {
            foreach ($ids as $id) {
                $clf->getByIdAndCompanyId($id, $current_company->getId());
                foreach ($clf as $c_obj) {
                    $c_obj->setDeleted($delete);
                    if ($c_obj->isValid()) {
                        $c_obj->Save();
                    }
                }
            }
        }
    }

    private function showCurrencyList($sort_array, $page)
    {
        $current_user_prefs = $this->userPrefs;
        $current_company = $this->company;

        BreadCrumb::setCrumb('Currency List');
        $clf = new CurrencyListFactory();

        $clf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage() ?? null, $page, null, $sort_array);
        $pager = new Pager($clf);
        $iso_code_options = $clf->getISOCodesArray();
        
        $currencies = [];
        $base_currency = false;

        foreach ($clf as $c_obj) {
            if ($c_obj->getBase() === true) {
                $base_currency = true;
            }
            $currencies[] = [
                'id' => $c_obj->GetId(),
                'status_id' => $c_obj->getStatus(),
                'name' => $c_obj->getName(),
                'iso_code' => $c_obj->getISOCode(),
                'currency_name' => Option::getByKey($c_obj->getISOCode(), $iso_code_options),
                'conversion_rate' => $c_obj->getConversionRate(),
                'auto_update' => $c_obj->getAutoUpdate(),
                'is_base' => $c_obj->getBase(),
                'is_default' => $c_obj->getDefault(),
                'deleted' => $c_obj->getDeleted()
            ];
        }

        //$smarty->assign_by_ref('currencies', $currencies);
        //$smarty->assign_by_ref('base_currency', $base_currency);
        //$smarty->assign_by_ref('sort_column', $sort_array['sort_column'] ?? '');
        //$smarty->assign_by_ref('sort_order', $sort_array['sort_order'] ?? '');
        //$smarty->assign_by_ref('paging_data', $pager->getPageVariables());

        $data = [
            'currencies' => $currencies,
            'base_currency' => $base_currency,
            'sort_column' => $sort_array['sort_column'] ?? '',
            'sort_order' => $sort_array['sort_order'] ?? '',
            'paging_data' => $pager->getPageVariables()
        ];
        
        return view('currency/CurrencyList', $data);
    }

}
