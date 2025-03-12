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
        // var_dump('a');
        $current_company = $this->company;
        $current_user_prefs = $this->userPrefs;

        //if (!$this->permission->Check('currency', 'enabled') || 
        //    !($this->permission->Check('currency', 'view') || $this->permission->Check('currency', 'view_own'))) {
        //    return $this->permission->Redirect(false);
        //}

        //$smarty->assign('title', ('Currency List'));

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

        $action = Misc::findSubmitButton();

        switch ($action) {
            case 'update_rates':
                CurrencyFactory::updateCurrencyRates($current_company->getId());
                Redirect::Page(URLBuilder::getURL(NULL, '/currency'));
                break;

            case 'add':
                Redirect::Page(URLBuilder::getURL(NULL, '/currency/add'));
                break;

            case 'delete':
            case 'undelete':
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
                Redirect::Page(URLBuilder::getURL(NULL, '/currency'));
                break;

            default:
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

                $data = [
                    'title' => 'Currency List',
                    'currencies' => $currencies,
                    'base_currency' => $base_currency,
                    'sort_column' => $sort_array['sort_column'] ?? '',
                    'sort_order' => $sort_array['sort_order'] ?? '',
                    'paging_data' => $pager->getPageVariables()
                ];

                return view('currency.CurrencyList', $data);
                break;
        }
    }

    // public function delete($id)
    // {
    //     $current_company = $this->company;
    //     // dd($id);
    //     // print_r($request);
    //     // exit;

    //     // $ids = $request->input('ids', []); // Array of IDs to delete

    //     if (empty($id)) {
    //         return redirect()->back()->withErrors(['error' => 'No currencies selected.']);
    //     }

    //     $clf = new CurrencyListFactory();

    //     // foreach ($ids as $id) {
    //         $clf->getByIdAndCompanyId($id, $current_company->getId());
    //         foreach ($clf as $c_obj) {
    //             $c_obj->setDeleted(true); // Set deleted flag to true
    //             if ($c_obj->isValid()) {
    //                 $c_obj->Save();
    //             }
    //         }
    //     // }

    //     // Redirect with success message
    //     return redirect()->to(URLBuilder::getURL(null, '/currency'))->with('success', 'Currencies deleted successfully.');
    // }

    public function delete($id)
    {
        $current_company = $this->company;

        if (empty($id)) {
            return response()->json(['error' => 'No currencies selected.'], 400);
        }

        $clf = new CurrencyListFactory();
        $currency = $clf->getByIdAndCompanyId($id, $current_company->getId());
        
        foreach ($currency->rs as $c_obj) {
            $currency->setDeleted(true); // Set deleted flag to true
            if ($currency->isValid()) {
                $currency->Save();
            }
        }
        dd($currency->rs);

        return response()->json(['success' => 'Currency deleted successfully.']);
    }
}
