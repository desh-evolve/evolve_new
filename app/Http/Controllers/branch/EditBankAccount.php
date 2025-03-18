<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Core\BreadCrumb;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use App\Models\Company\BranchBankAccountFactory;
use App\Models\Company\BranchBankAccountListFactory;
use App\Models\Company\BranchListFactory;
use Illuminate\Support\Facades\View;

class EditBankAccount extends Controller
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

    public function index(Request $request, $id = null)
    {
        // Permission check
        if (!$this->permission->Check('branch', 'enabled') ||
            !($this->permission->Check('branch', 'edit') || $this->permission->Check('branch', 'edit_own'))) {
            return $this->permission->Redirect(false);
        }

        // Extract form variables
        extract(FormVariables::GetVariables([
            'action',
            'company_id',
            'bank_data',
            'data_saved',
            'branch_id_new',
            'branch_id_saved'
        ]));

        $baf = new BranchBankAccountFactory();

        $action = Misc::findSubmitButton();
        switch ($action) {
            case 'submit':
                $baf->setId($bank_data['id'] ?? null);

                // Set default branch ID
                if (empty($branch_id_saved)) {
                    $baf->setDefaultBranch($branch_id_new);
                } else {
                    $baf->setDefaultBranch($branch_id_saved);
                }

                // Set bank account data
                if (isset($bank_data['institution'])) {
                    $baf->setInstitution($bank_data['institution']);
                }
                $baf->setTransit($bank_data['transit']);
                $baf->setAccount($bank_data['account']);
                $baf->setBankName($bank_data['bank_name']);
                $baf->setBankBranch($bank_data['bank_branch']);

                // Validate and save
                if ($baf->isValid()) {
                    $baf->Save();
                    return Redirect::to(URLBuilder::getURL($redirect_arr, Environment::getBaseURL() . '/branch/BranchList.php'));
                } else {
                    Debug::Text('Invalid bank data...', __FILE__, __LINE__, __METHOD__, 10);
                }
                break;

            default:
                $balf = new BranchBankAccountListFactory();
                $balf->getById($id);

                if (!isset($action)) {
                    BreadCrumb::setCrumb('Edit Bank Account');

                    foreach ($balf as $bank_account) {
                        $bank_data = [
                            'id' => $bank_account->getId(),
                            'default_branch_id' => $bank_account->getDefaultBranch(),
                            'institution' => $bank_account->getInstitution(),
                            'transit' => $bank_account->getTransit(),
                            'account' => $bank_account->getAccount(),
                            'bank_name' => $bank_account->getBankName(),
                            'bank_branch' => $bank_account->getBankBranch(),
                            'created_date' => $bank_account->getCreatedDate(),
                            'created_by' => $bank_account->getCreatedBy(),
                            'updated_date' => $bank_account->getUpdatedDate(),
                            'updated_by' => $bank_account->getUpdatedBy(),
                            'deleted_date' => $bank_account->getDeletedDate(),
                            'deleted_by' => $bank_account->getDeletedBy(),
                        ];
                    }
                }

                // Fetch branch name for display
                $company_branch_name = null;
                if (!empty($bank_data['default_branch_id'])) {
                    $blf = new BranchListFactory();
                    $company_branch_name = $blf->getById($bank_data['default_branch_id'])->getCurrent()->getName();
                } elseif (isset($branch_id_new)) {
                    $blf = new BranchListFactory();
                    $company_branch_name = $blf->getById($branch_id_new)->getCurrent()->getName();
                }

                $data = [
                    'title' => TTi18n::gettext('Edit Bank Account'),
                    'bank_data' => $bank_data,
                    'branch_id_new' => $branch_id_new,
                    'branch_id_saved' => $bank_data['default_branch_id'] ?? null,
                    'company_branch_name' => $company_branch_name,
                    'data_saved' => $data_saved,
                ];

                return view('branch.EditBankAccount', $data);
        }
    }
}