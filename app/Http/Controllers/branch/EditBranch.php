<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\URLBuilder;
use App\Models\Company\BranchBankAccountFactory;
use App\Models\Company\BranchBankAccountListFactory;
use App\Models\Company\BranchListFactory;
use App\Models\Company\BranchFactory;
use App\Models\Company\CompanyFactory;
use Illuminate\Support\Facades\View;

class EditBranch extends Controller
{
    protected $permission;
    protected $company;
    protected $userPrefs;
    protected $branchBankAccountFactory;
    protected $branchBankAccountListFactory;
    protected $branchListFactory;

    public function __construct(
        BranchBankAccountFactory $branchBankAccountFactory,
        BranchBankAccountListFactory $branchBankAccountListFactory,
        BranchListFactory $branchListFactory
    ) {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');

        $this->branchBankAccountFactory = $branchBankAccountFactory;
        $this->branchBankAccountListFactory = $branchBankAccountListFactory;
        $this->branchListFactory = $branchListFactory;
    }

    // public function index($id = null)
    // {
    //     $current_company = $this->company;

    //     // // Permission check
    //     // if (!$this->permission->Check('branch', 'enabled') ||
    //     //     !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))) {
    //     //     return $this->permission->Redirect(false);
    //     // }

    //     $data = [];

    //     if ($id) {
    //         // Edit mode: Fetch existing bank account data
    //         $balf = $this->branchBankAccountListFactory->getById($id);

    //         $bank_account = $balf->rs ?? [];
    //         if ($bank_account) {
    //             foreach ($bank_account as $b_obj) {
    //                 $data = [
    //                     'id' => $b_obj->id,
    //                     'default_branch_id' => $b_obj->default_branch_id,
    //                     'institution' => $b_obj->institution,
    //                     'transit' => $b_obj->transit,
    //                     'account' => $b_obj->account,
    //                     'bank_name' => $b_obj->bank_name,
    //                     'bank_branch' => $b_obj->bank_branch,
    //                     'created_date' => $b_obj->created_date,
    //                     'created_by' => $b_obj->created_by,
    //                     'updated_date' => $b_obj->updated_date,
    //                     'updated_by' => $b_obj->updated_by,
    //                     'deleted_date' => $b_obj->deleted_date,
    //                     'deleted_by' => $b_obj->deleted_by,
    //                 ];
    //             }
    //         }
    //     } else {
    //         // Add mode: Set default values
    //         $data = [
    //             'default_branch_id' => null,
    //             'institution' => null,
    //             'transit' => null,
    //             'account' => null,
    //             'bank_name' => null,
    //             'bank_branch' => null,
    //         ];
    //     }

    //     // Fetch branch name for display
    //     $company_branch_name = null;
    //     if (!empty($data['default_branch_id'])) {
    //         $blf = $this->branchListFactory->getById($data['default_branch_id']);
    //         $company_branch_name = $blf->getCurrent()->getName();
    //     }

    //     //Select box options;
    //     $bf = new BranchFactory();

    // 	// $bf->setCountry($data['country']);
    //     $data['status_options'] = $bf->getOptions('status');
    //     $data['manual_id'] = $bf->setManualId('manual_id');
    //     $data['manual_id'] = $bf->setManualId('manual_id');
    //     $data['next_available_manual_id'] = BranchListFactory::getNextAvailableManualId( $current_company->getId() );

    //     $cf = new CompanyFactory();
    // 	$data['country_options'] = $cf->getOptions('country');
    // 	$data['province_options'] = $cf->getOptions('province');

    // 	// $bf->setManualId($data['manual_id']);

    //     $viewData = [
    //         'title' => $id ? 'Edit Bank Account' : 'Add Bank Account',
    //         'data' => $data,
    //         'company_branch_name' => $company_branch_name,
    //     ];

    //     return view('branch.EditBranch', $viewData);
    // }

    // public function save(Request $request, $id = null)
    // {
    //     $current_company = $this->company;

    //     // Permission check
    //     if (
    //         !$this->permission->Check('branch', 'enabled') ||
    //         !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))
    //     ) {
    //         return $this->permission->Redirect(false);
    //     }

    //     $data = $request->all();
    //     Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

    //     $this->branchBankAccountFactory->setId($id ?? null); // Use $id if editing, otherwise null for add
    //     $this->branchBankAccountFactory->setDefaultBranch($data['default_branch_id'] ?? null);
    //     $this->branchBankAccountFactory->setInstitution($data['institution'] ?? null);
    //     $this->branchBankAccountFactory->setTransit($data['transit'] ?? null);
    //     $this->branchBankAccountFactory->setAccount($data['account'] ?? null);
    //     $this->branchBankAccountFactory->setBankName($data['bank_name'] ?? null);
    //     $this->branchBankAccountFactory->setBankBranch($data['bank_branch'] ?? null);

    //     if ($this->branchBankAccountFactory->isValid()) {
    //         $this->branchBankAccountFactory->Save();
    //         return redirect()->to(URLBuilder::getURL(null, '/branch/BranchList.php'))->with('success', 'Bank account saved successfully.');
    //     } else {
    //         // If validation fails, return back with errors
    //         return redirect()->back()->withErrors(['error' => 'Invalid bank account data.'])->withInput();
    //     }
    // }

    public function index($id = null)
    {
        $current_company = $this->company;

        // Permission check (commented out for now)
        // if (!$this->permission->Check('branch', 'enabled') ||
        //     !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))) {
        //     return $this->permission->Redirect(false);
        // }

        $data = [];

        if ($id) {
            // Edit mode: Fetch existing branch data
            $blf = $this->branchListFactory->getById($id);
            $branch = $blf->rs ?? [];
            // dd($branch);
            if ($branch) {
                foreach ($branch as $b_obj) {
                    $data = [
                        'id' => $b_obj->id,
                        'status' => $b_obj->status_id,
                        'manual_id' => $b_obj->manual_id,
                        'branch_short_id' => $b_obj->branch_short_id,
                        'epf_no' => $b_obj->epf_no,
                        'etf_no' => $b_obj->etf_no,
                        'tin_no' => $b_obj->tin_no,
                        'business_reg_no' => $b_obj->business_reg_no,
                        'name' => $b_obj->name,
                        'address1' => $b_obj->address1,
                        'address2' => $b_obj->address2,
                        'city' => $b_obj->city,
                        'province' => $b_obj->province,
                        'country' => $b_obj->country,
                        'postal_code' => $b_obj->postal_code,
                        'work_phone' => $b_obj->work_phone,
                        'fax_phone' => $b_obj->fax_phone,
                        'other_id1' => $b_obj->other_id1,
                        'other_id2' => $b_obj->other_id2,
                        'other_id3' => $b_obj->other_id3,
                        'other_id4' => $b_obj->other_id4,
                        'other_id5' => $b_obj->other_id5,
                    ];
                }
            }
            
            // dd($data);
        } else {
            // Add mode: Set default values
            $data = [
                'country' => $current_company->getCountry(),
                'province' => $current_company->getProvince(),
                'next_available_manual_id' => BranchListFactory::getNextAvailableManualId($current_company->getId()),
            ];
        }

        // Fetch options for select boxes
        $bf = new BranchFactory();
        $data['status_options'] = $bf->getOptions('status');

        $cf = new CompanyFactory();
        $data['country_options'] = $cf->getOptions('country');
        $data['province_options'] = $cf->getOptions('province', $data['country'] ?? null);

        // Prepare view data
        $viewData = [
            'title' => $id ? 'Edit Branch' : 'Add Branch',
            'data' => $data,
        ];
        // dd($viewData);
        return view('branch.EditBranch', $viewData);
    }

    public function save(Request $request, $id = null)
    {
        $current_company = $this->company;

        // // Permission check
        // if (
        //     !$this->permission->Check('branch', 'enabled') ||
        //     !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))
        // ) {
        //     return $this->permission->Redirect(false);
        // }

        // Validate input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'status_id' => 'required|string',
            'manual_id' => 'nullable|string',
            'branch_short_id' => 'nullable|string',
            'epf_no' => 'nullable|string',
            'etf_no' => 'nullable|string',
            'tin_no' => 'nullable|string',
            'business_reg_no' => 'nullable|string',
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            // 'province' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'work_phone' => 'nullable|string',
            'fax_phone' => 'nullable|string',
            'other_id1' => 'nullable|string',
            'other_id2' => 'nullable|string',
            'other_id3' => 'nullable|string',
            'other_id4' => 'nullable|string',
            'other_id5' => 'nullable|string',
        ]);

        // Set branch data
        $bf = new BranchFactory();
        $bf->setId($id ?? null); // Use $id if editing, otherwise null for add
        $bf->setCompany($current_company->getId());
        $bf->setStatus($validatedData['status_id']);
        $bf->setName($validatedData['name']);
        $bf->setManualId($validatedData['manual_id'] ?? null);
        $bf->setBranchShortId($validatedData['branch_short_id'] ?? null);
        $bf->setEpfNo($validatedData['epf_no'] ?? null);
        $bf->setEtfNo($validatedData['etf_no'] ?? null);
        $bf->setTinNo($validatedData['tin_no'] ?? null);
        $bf->setBusinessRegNo($validatedData['business_reg_no'] ?? null);
        $bf->setAddress1($validatedData['address1'] ?? null);
        $bf->setAddress2($validatedData['address2'] ?? null);
        $bf->setCity($validatedData['city'] ?? null);
        $bf->setCountry($validatedData['country'] ?? null);
        $bf->setProvince($validatedData['province'] ?? '00');
        $bf->setPostalCode($validatedData['postal_code'] ?? null);
        $bf->setWorkPhone($validatedData['work_phone'] ?? null);
        $bf->setFaxPhone($validatedData['fax_phone'] ?? null);
        $bf->setOtherId1($validatedData['other_id1'] ?? null);
        $bf->setOtherId2($validatedData['other_id2'] ?? null);
        $bf->setOtherId3($validatedData['other_id3'] ?? null);
        $bf->setOtherId4($validatedData['other_id4'] ?? null);
        $bf->setOtherId5($validatedData['other_id5'] ?? null);

        if ($bf->isValid()) {
            $bf->Save();
            return redirect()->route('branch.index')->with('success', 'Branch saved successfully.');
        } else {
            // // If validation fails, return back with errors
            return redirect()->back()->withErrors(['error' => 'Invalid branch data.'])->withInput();
        }
    }
    public function delete($id)
    {
        $current_company = $this->company;

        // Permission check
        if (!$this->permission->Check('branch', 'enabled') || !$this->permission->Check('branch', 'delete')) {
            return $this->permission->Redirect(false);
        }

        $bf = new BranchFactory();
        $bf->setId($id);
        $bf->setDeleted(true); // Assuming your factory supports soft deletes

        if ($bf->isValid()) {
            $bf->Save();
            return redirect()->route('branch.index')->with('success', 'Branch deleted successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to delete branch.']);
        }
    }
}
