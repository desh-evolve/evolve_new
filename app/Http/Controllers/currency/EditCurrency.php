<?php

namespace App\Http\Controllers\Currency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Core\CurrencyFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class EditCurrency extends Controller
{
    protected $permission;
    protected $company;
    protected $userPrefs;
    protected $currencyFactory;
    protected $currencyListFactory;

    public function __construct(CurrencyFactory $currencyFactory, CurrencyListFactory $currencyListFactory)
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->currencyFactory = $currencyFactory;
        $this->currencyListFactory = $currencyListFactory;

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');
    }

    public function index($id = null)
    {
        $current_company = $this->company;

        /*
        if (!$this->permission->Check('currency', 'enabled')
            || !($this->permission->Check('currency', 'edit') || $this->permission->Check('currency', 'edit_own'))) {
            $this->permission->Redirect(FALSE); // Redirect
        }
        */

        $data = [];

        if ($id) {
            // Edit mode: Fetch existing currency data
            $clf = $this->currencyListFactory->getByIdAndCompanyId($id, $current_company->getId());

            $currency = $clf->rs ?? [];
            if ($currency) {
                foreach ($currency as $c_obj) {
                    $data = [
                        'id' => $c_obj->id,
                        'status' => $c_obj->status_id,
                        'name' => $c_obj->name,
                        'iso_code' => $c_obj->iso_code,
                        'conversion_rate' => $c_obj->conversion_rate,
                        'auto_update' => $c_obj->auto_update,
                        'rate_modify_percent' => $c_obj->rate_modify_percent,
                        'actual_rate' => (float)$c_obj->actual_rate,
                        'actual_rate_updated_date' => $c_obj->actual_rate_updated_date,
                        'is_base' => $c_obj->is_base,
                        'is_default' => $c_obj->is_default,
                        'created_date' => $c_obj->created_date,
                        'created_by' => $c_obj->created_by,
                        'updated_date' => $c_obj->updated_date,
                        'updated_by' => $c_obj->updated_by,
                        'deleted_date' => $c_obj->deleted_date,
                        'deleted_by' => $c_obj->deleted_by,
                    ];
                }
            }
        } else {
            // Add mode: Set default values
            $data = [
                'conversion_rate' => '1.0000000000',
                'rate_modify_percent' => '1.0000000000',
            ];
        }

        // Select box options
        $data['status_options'] = $this->currencyFactory->getOptions('status');
        $data['iso_code_options'] = $this->currencyFactory->getISOCodesArray();

        $viewData = [
            'title' => $id ? 'Edit Currency' : 'Add Currency',
            'data' => $data,
        ];

        return view('currency.EditCurrency', $viewData);
    }

    public function save(Request $request, $id = null)
    {
        
        $current_company = $this->company;

        /*
        if (!$this->permission->Check('currency', 'enabled')
            || !($this->permission->Check('currency', 'edit') || $this->permission->Check('currency', 'edit_own'))) {
            $this->permission->Redirect(FALSE); // Redirect
        }
        */

        $data = $request->all();
        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);
        

        $this->currencyFactory->setId($id ?? null); // Use $id if editing, otherwise null for add
        $this->currencyFactory->setCompany($current_company->getId());
        $this->currencyFactory->setStatus($data['status_id'] ?? '');
        $this->currencyFactory->setName($data['name'] ?? '');
        $this->currencyFactory->setISOCode($data['iso_code'] ?? '');
        $this->currencyFactory->setConversionRate($data['conversion_rate'] ?? '');
        $this->currencyFactory->setAutoUpdate(isset($data['auto_update']) && $data['auto_update'] == 1);
        $this->currencyFactory->setBase(isset($data['is_base']) && $data['is_base'] == 1);
        $this->currencyFactory->setDefault(isset($data['is_default']) && $data['is_default'] == 1);
        $this->currencyFactory->setRateModifyPercent($data['rate_modify_percent'] ?? '');
        
        if ($this->currencyFactory->isValid()) {
            $this->currencyFactory->Save();
            return redirect()->to(URLBuilder::getURL(null, '/currency'))->with('success', 'Currency saved successfully.');
        }

        // If validation fails, return back with errors
        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
    }
}