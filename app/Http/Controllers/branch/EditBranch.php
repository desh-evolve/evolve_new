<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\Environment;
use App\Models\company\BranchFactory;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class EditBranch extends Controller
{
    protected $permission;
    protected $company;
    protected $userPrefs;
    protected $branchFactory;
    protected $branchListFactory;

    public function __construct(BranchFactory $branchFactory, BranchListFactory $branchListFactory)
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->branchFactory = $branchFactory;
        $this->branchListFactory = $branchListFactory;

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');
    }

    public function index($id = null)
    {
        $current_company = $this->company;

        // Permission check
        /*
        if (!$this->permission->Check('branch', 'enabled')
            || !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))) {
            return $this->permission->Redirect(false);
        }
        */

        $data = [];

        if ($id) {
            // Edit mode: Fetch existing branch data
            $blf = $this->branchListFactory->getByIdAndCompanyId($id, $current_company->getId());

            $branch = $blf->rs ?? [];
            if ($branch) {
                // print_r($branch);
                // exit;
                foreach ($branch as $b_obj) {
                    $data = [
                        'id' => $b_obj->id,
                        'status' => $b_obj->status_id,
                        'name' => $b_obj->name,
                        'manual_id' => $b_obj->manual_id,
                        'branch_short_id' => $b_obj->branch_short_id, // Added for Thunder & Neon
                        'epf_no' => $b_obj->epf_no, // Added for Thunder & Neon
                        'etf_no' => $b_obj->etf_no, // Added for Thunder & Neon
                        'tin_no' => $b_obj->tin_no, // Added for Thunder & Neon
                        'business_reg_no' => $b_obj->business_reg_no, // Added for Thunder & Neon
                        'address1' => $b_obj->address1,
                        'address2' => $b_obj->address2,
                        'city' => $b_obj->city,
                        'country' => $b_obj->country,
                        'province' => $b_obj->province,
                        'postal_code' => $b_obj->postal_code,
                        'work_phone' => $b_obj->work_phone,
                        'fax_phone' => $b_obj->fax_phone,
                        'other_id1' => $b_obj->other_id1,
                        'other_id2' => $b_obj->other_id2,
                        'other_id3' => $b_obj->other_id3,
                        'other_id4' => $b_obj->other_id4,
                        'other_id5' => $b_obj->other_id5,
                        'created_date' => $b_obj->created_date,
                        'created_by' => $b_obj->created_by,
                        'updated_date' => $b_obj->updated_date,
                        'updated_by' => $b_obj->updated_by,
                        'deleted_date' => $b_obj->deleted_date,
                        'deleted_by' => $b_obj->deleted_by,
                    ];
                }
            }
        } else {
            // Add mode: Set default values
            $data = [
                'country' => $current_company->getCountry(),
                'province' => $current_company->getProvince(),
                'next_available_manual_id' => $this->branchListFactory->getNextAvailableManualId($current_company->getId()),
            ];
        }

        // Select box options
        $data['status_options'] = $this->branchFactory->getOptions('status');
        $data['country_options'] = $this->branchFactory->getOptions('country');
        $data['province_options'] = $this->branchFactory->getOptions('province', $data['country']);

        // Get other field names
        $oflf = new OtherFieldListFactory();
        $data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray($current_company->getId(), 4);

        $viewData = [
            'title' => $id ? 'Edit Branch' : 'Add Branch',
            'data' => $data,
        ];

        return view('branch.EditBranch', $viewData);
    }

    public function save(Request $request, $id = null)
    {
        $current_company = $this->company;

        // Permission check
        /*
        if (!$this->permission->Check('branch', 'enabled')
            || !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))) {
            return $this->permission->Redirect(false);
        }
        */

        $data = $request->all();
        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

        $this->branchFactory->setId($id ?? null); // Use $id if editing, otherwise null for add
        $this->branchFactory->setCompany($current_company->getId());
        $this->branchFactory->setStatus($data['status'] ?? '');
        $this->branchFactory->setName($data['name'] ?? '');
        $this->branchFactory->setManualId($data['manual_id'] ?? '');
        $this->branchFactory->setBranchShortID($data['branch_short_id'] ?? ''); // Added for Thunder & Neon
        $this->branchFactory->setEpfNo($data['epf_no'] ?? ''); // Added for Thunder & Neon
        $this->branchFactory->setEtfNo($data['etf_no'] ?? ''); // Added for Thunder & Neon
        $this->branchFactory->setTinNo($data['tin_no'] ?? ''); // Added for Thunder & Neon
        $this->branchFactory->setBusinessRegNo($data['business_reg_no'] ?? ''); // Added for Thunder & Neon
        $this->branchFactory->setAddress1($data['address1'] ?? '');
        $this->branchFactory->setAddress2($data['address2'] ?? '');
        $this->branchFactory->setCity($data['city'] ?? '');
        $this->branchFactory->setCountry($data['country'] ?? '');
        $this->branchFactory->setProvince($data['province'] ?? '');
        $this->branchFactory->setPostalCode($data['postal_code'] ?? '');
        $this->branchFactory->setWorkPhone($data['work_phone'] ?? '');
        $this->branchFactory->setFaxPhone($data['fax_phone'] ?? '');
        $this->branchFactory->setOtherID1($data['other_id1'] ?? '');
        $this->branchFactory->setOtherID2($data['other_id2'] ?? '');
        $this->branchFactory->setOtherID3($data['other_id3'] ?? '');
        $this->branchFactory->setOtherID4($data['other_id4'] ?? '');
        $this->branchFactory->setOtherID5($data['other_id5'] ?? '');

        if ($this->branchFactory->isValid()) {
            $this->branchFactory->Save();
            return redirect()->to(URLBuilder::getURL(null, '/branch'))->with('success', 'Branch saved successfully.');
        }

        // If validation fails, return back with errors
        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
    }
}