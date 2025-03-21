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
use App\Models\Core\FormVariables;
use Illuminate\Support\Facades\View;

class EditBankAccount extends Controller
{
    protected $permission;
    protected $currentUser;
    protected $currentCompany;
    protected $branchBankAccountFactory;
    protected $branchBankAccountListFactory;
    protected $branchListFactory;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');

        extract(FormVariables::GetVariables(
            array(
                'action',
                'company_id',
                'bank_data',
                'data_saved',
                'branch_id_new',
                'id',
                'branch_id_saved'
            )
        ));


        /*
        if ( !$permission->Check('branch','enabled')  OR !( $permission->Check('branch','edit') OR $permission->Check('branch','edit_own') ) ) {
            $permission->Redirect( FALSE ); //Redirect
        }
        */
    }

    public function index(Request $request,$id = null)
    {
        $viewData = ['title' => $id ? 'Edit Bank Account' : 'Add Bank Account'];
        if ($id) {
            $balf = new BranchBankAccountListFactory();
            $balf->getById($id);

            foreach ($balf->rs as $bank_account) {
                $balf->data = (array)$bank_account;
                $bank_account = $balf;
                //Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

                $bank_data = array(
                    'id' => $bank_account->getId(),
                    'default_branch_id' => $bank_account->getDefaultBranch(),
                    'country' => 1,
                    'institution' => $bank_account->getInstitution(),
                    'transit' => $bank_account->getTransit(),
                    //'account' => $bank_account->getSecureAccount(),//ARSP EDIT --> I Hide THIS CODE REASON- WE CANT SEE ORIGINAL ACCOUNT NUMBER
                    'account' => $bank_account->getAccount(), //ARSP EDIT --> I ADD NEW CODE SHOW ONLY ORIGINAL ACCOUNT NUMBER
                    'bank_name' => $bank_account->getBankName(), //ARSP EDIT --> I ADD NEW CODE FOR BANK NAME
                    'bank_branch' => $bank_account->getBankBranch(), //ARSP EDIT --> I ADD NEW CODE FOR BANK BRANCH NAME
                    'created_date' => $bank_account->getCreatedDate(),
                    'created_by' => $bank_account->getCreatedBy(),
                    'updated_date' => $bank_account->getUpdatedDate(),
                    'updated_by' => $bank_account->getUpdatedBy(),
                    'deleted_date' => $bank_account->getDeletedDate(),
                    'deleted_by' => $bank_account->getDeletedBy()
                );
            }

            $viewData['branch_id_saved'] = $bank_data['default_branch_id'];

            //Add New
            if ($bank_data['default_branch_id'] != '' or $bank_data['default_branch_id'] != NULL) {
                $blf = new BranchListFactory();
                $company_branch_name = $blf->getById($bank_data['default_branch_id'])->getCurrent()->getName();
                $viewData['company_branch_name'] = $company_branch_name;
            }
        } else {
            // Add mode: Set default values
            $bank_data = [
                'default_branch_id' => $request->branch_id,
                'institution' => null,
                'transit' => null,
                'account' => null,
                'bank_name' => null,
                'bank_branch' => null,
            ];
            
        }
        //Edit Old
        if (isset($branch_id_new)) {
            $blf = new BranchListFactory();
            $company_branch_name = $blf->getById($branch_id_new)->getCurrent()->getName();
            $viewData['company_branch_name'] = $company_branch_name;
        }

        $viewData['company_id'] = $this->currentCompany->getCurrent()->getId();
        $viewData['user_id'] = $this->currentUser->getCurrent()->getId();

        $viewData['bank_data'] = $bank_data;

        return view('branch.EditBankAccount', $viewData);
    }

    public function save(Request $request, $id = null)
    {
        // dd($request->all());
        $data = $request->all();
        $baf = new BranchBankAccountFactory();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

        $baf->setId($id);
        $baf->setDefaultBranch($data['default_branch_id'] ?? '');
        $baf->setInstitution('000');
        $baf->setTransit($data['transit'] ?? '');
        $baf->setAccount($data['account'] ?? '');
        $baf->setBankName($data['bank_name'] ?? '');
        $baf->setBankBranch($data['bank_branch'] ?? '');
        $baf->setCompany($data['company_id'] ?? '');

        if ($baf->isValid()) {
            $baf->Save();
            return redirect()->to(URLBuilder::getURL(null, '/branch'))->with('success', 'Bank account saved successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Invalid bank account data.'])->withInput();
        }

        
    }
}
