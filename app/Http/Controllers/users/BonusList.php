<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\BonusDecemberFactory;
use App\Models\Users\BonusDecemberListFactory;
use App\Models\Users\BonusDecemberUserListFactory;
use Illuminate\Support\Facades\View;
use App\Models\Users\UserListFactory;

class BonusList extends Controller
{
    protected $permission;
    protected $currentUser;
    protected $currentCompany;
    protected $userPrefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');
        $this->userPrefs = View::shared('current_user_prefs');
    }

    public function index($dec_bo_id = null)
    {
       extract(FormVariables::GetVariables(['action', 'dec_bo_id']));
        $permission = $this->permission;
        $current_user = $this->currentUser;
        $current_company = $this->currentCompany;
        $current_user_prefs = $this->userPrefs;

        if (
            !$permission->Check('company', 'enabled')
            or !($permission->Check('company', 'view') or $permission->Check('company', 'view_own') or $permission->Check('company', 'view_child'))
        ) {
            $permission->Redirect(FALSE); //Redirect
        }

        $viewData['title'] = 'Bonus List ';

      extract(FormVariables::GetVariables(['action', 'dec_bo_id']));
$action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
        //==================================================================================
        $action = '';
        if (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
        //==================================================================================
        switch ($action) {

            case 'submit':

                break;
            default:

                $data = array();

                if (isset($dec_bo_id)) {
                    $bdulf = new BonusDecemberUserListFactory();
                    $bdulf->getByBonusDecemberId($dec_bo_id);
                    $data = []; // Initialize the array

                    foreach ($bdulf->rs as $bdu_obj) {
                        $bdulf->data = (array)$bdu_obj;
                        $bdu_obj = $bdulf;

                        try {
                            $userObject = $bdu_obj->getUserObject();

                            // Skip this record if user object is invalid
                            if (!$userObject || !is_object($userObject)) {
                                continue;
                            }

                            $data[] = [
                                'id' => $bdu_obj->getId(),
                                'empno' => $userObject->getEmployeeNumber() ?? 'N/A',
                                'name' => $userObject->getFullName() ?? 'Unknown',
                                'amount' => number_format($bdu_obj->getBonusAmount(), 2),
                            ];
                        } catch (\Exception $e) {
                            // Log the error and continue with next record
                            \Log::error("Error processing user bonus: " . $e->getMessage());
                            continue;
                        }
                    }
                }
                $viewData['data'] = $data;

                break;
        }

        $viewData['user_options'] = UserListFactory::getByCompanyIdArray($current_company->getId(), FALSE);
        return view('users/BonusList', $viewData);
    }
}
