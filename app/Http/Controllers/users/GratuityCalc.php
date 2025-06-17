<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;
use App\Models\Users\AttendanceBonusListFactory;
use App\Models\Users\UserListFactory;

class GratuityCalc extends Controller
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

    public function index()
    {
        $permission = $this->permission;
        $current_company = $this->currentCompany;

        if (
            !$permission->Check('company', 'enabled')
            || !($permission->Check('company', 'view') || $permission->Check('company', 'view_own') || $permission->Check('company', 'view_child'))
        ) {
            $permission->Redirect(FALSE); // Redirect
        }

        $viewData['title'] = 'Gratuity Calculation';

        extract(FormVariables::GetVariables(
            array(
                'action',
                'page',
                'sort_column',
                'sort_order',
                'filter_user_id',
                'ids',
            )
        ));

        URLBuilder::setURL(
            $_SERVER['SCRIPT_NAME'],
            array(
                'filter_user_id' => $filter_user_id,
                'sort_column' => $sort_column,
                'sort_order' => $sort_order,
                'page' => $page
            )
        );

        $action = Misc::findSubmitButton();

        switch ($action) {
            case 'add':
                Redirect::Page(URLBuilder::getURL(NULL, '/users/edit_attendance_bonus_calc'));
                break;

            default:
                $ablf = new AttendanceBonusListFactory();
                $ablf->getByCompanyId($current_company->getId());
                $bonuses = array();
                foreach ($ablf->rs as $ab_obj) {
                    $ablf->data = (array)$ab_obj;
					$ab_obj = $ablf;
                    $bonuses[] = array(
                        'id' => $ab_obj->getId(),
                        'company' => $ab_obj->getCompanyObject()->getName(),
                        'year' => $ab_obj->getYear(),
                    );
                }

                $viewData['bonuses'] = $bonuses;
                break;
        }

		$viewData['user_options'] = UserListFactory::getByCompanyIdArray($current_company->getId(), FALSE);

        return view('users.GratuityCalc', $viewData);
    }
}