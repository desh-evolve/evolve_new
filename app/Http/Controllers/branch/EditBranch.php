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

    public function index($id = null)
    {
        $current_company = $this->company;

        // Permission check
        if (!$this->permission->Check('branch', 'enabled') ||
            !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))) {
            return $this->permission->Redirect(false);
        }

        $data = [];

        if ($id) {
            // Edit mode: Fetch existing bank account data
            $balf = $this->branchBankAccountListFactory->getById($id);

            $bank_account = $balf->rs ?? [];
            if ($bank_account) {
                foreach ($bank_account as $b_obj) {
                    $data = [
                        'id' => $b_obj->id,
                        'default_branch_id' => $b_obj->default_branch_id,
                        'institution' => $b_obj->institution,
                        'transit' => $b_obj->transit,
                        'account' => $b_obj->account,
                        'bank_name' => $b_obj->bank_name,
                        'bank_branch' => $b_obj->bank_branch,
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
                'default_branch_id' => null,
                'institution' => null,
                'transit' => null,
                'account' => null,
                'bank_name' => null,
                'bank_branch' => null,
            ];
        }

        // Fetch branch name for display
        $company_branch_name = null;
        if (!empty($data['default_branch_id'])) {
            $blf = $this->branchListFactory->getById($data['default_branch_id']);
            $company_branch_name = $blf->getCurrent()->getName();
        }

        $viewData = [
            'title' => $id ? 'Edit Bank Account' : 'Add Bank Account',
            'data' => $data,
            'company_branch_name' => $company_branch_name,
        ];

        return view('branch.EditBankAccount', $viewData);
    }

    public function save(Request $request, $id = null)
    {
        $current_company = $this->company;

        // Permission check
        if (!$this->permission->Check('branch', 'enabled') ||
            !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))) {
            return $this->permission->Redirect(false);
        }

        $data = $request->all();
        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

        $this->branchBankAccountFactory->setId($id ?? null); // Use $id if editing, otherwise null for add
        $this->branchBankAccountFactory->setDefaultBranch($data['default_branch_id'] ?? null);
        $this->branchBankAccountFactory->setInstitution($data['institution'] ?? null);
        $this->branchBankAccountFactory->setTransit($data['transit'] ?? null);
        $this->branchBankAccountFactory->setAccount($data['account'] ?? null);
        $this->branchBankAccountFactory->setBankName($data['bank_name'] ?? null);
        $this->branchBankAccountFactory->setBankBranch($data['bank_branch'] ?? null);

        if ($this->branchBankAccountFactory->isValid()) {
            $this->branchBankAccountFactory->Save();
            return redirect()->to(URLBuilder::getURL(null, '/branch/BranchList.php'))->with('success', 'Bank account saved successfully.');
        } else {
            // If validation fails, return back with errors
            return redirect()->back()->withErrors(['error' => 'Invalid bank account data.'])->withInput();
        }
    }
}