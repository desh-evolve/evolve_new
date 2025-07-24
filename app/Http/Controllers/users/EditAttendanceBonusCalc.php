<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Users\AttendanceBonusFactory;
use App\Models\Users\AttendanceBonusListFactory;
use App\Models\Users\BonusDecemberListFactory;
use Illuminate\Support\Facades\View;

class EditAttendanceBonusCalc extends Controller
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
        // dd('add');
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

        $viewData['title'] = 'Bonus';

        extract(FormVariables::GetVariables(
            array(
                'action',
                'id',
                'view',
                'data'
            )
        ));

        $abf = new AttendanceBonusFactory();
        //==================================================================================
        $action = '';
        if (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';

        // dd($action);

        switch ($action) {
            case 'submit':
                // dd($action);
                $abf->StartTransaction();

                if ($data['id'] == '') {
                    $abf->setCompany($current_company->getId());
                } else {
                    $abf->setId($data['id']);
                }

                $abf->setYear($data['year']);
                $abf->setBonusDecember($data['bonus_december_id']);

                if ($abf->isValid()) {
                    $abf->Save();
                    $abf->CommitTransaction();
                    Redirect::Page(URLBuilder::getURL(NULL, '/users/attendance_bonus_calc'));
                    break;
                }

                $abf->FailTransaction();
                break;

            case 'generate_attendance_bonuses':
                // dd($action);
                Debug::Text('Generate Bonus!', __FILE__, __LINE__, __METHOD__, 10);

                return redirect()->to('/progress_bar_control?' . http_build_query([
                    'action' => 'generate_attendance_bonuses',
                    'filter_user_id' => $data['id'],
                    'next_page' => url('/users/attendance_bonus_list') . '?att_bo_id=' . $data['id'] . '&action=view'
                ]));

                break;

            default:
                if (isset($id)) {
                    // dd($id);
                    $ablf = new AttendanceBonusListFactory();
                    $ablf->GetByIdAndCompanyId($id, $current_company->getId());

                    if ($ablf->getRecordCount() > 0) {
                        foreach ($ablf->rs as $abf_obj) {
                            $ablf->data = (array)$abf_obj;
                            $abf_obj = $ablf;
                            // $abf_obj = $ablf->getCurrent();
                            $data = array(
                                'id' => $abf_obj->getId(),
                                'year' => $abf_obj->getYear(),
                                'company_id' => $abf_obj->getCompany(),
                                'bonus_december_id' => $abf_obj->getBonusDecember(),
                            );
                        }
                    }
                } elseif ($action != 'submit') {
					$data = array(
						'id' => '',
						'year' => '',
						'company_id' => '',
						'bonus_december_id' => ''

					);
				}

                $viewData['data'] = $data;
                break;
        }

        $bdlf = new BonusDecemberListFactory();
        $viewData['bonus_december_options'] = $bdlf->getByCompanyIdArray($current_company->getId());
        $viewData['abf'] = $abf;
        $viewData['view'] = $view;

        return view('users/EditAttendanceBonusCalc', $viewData);
    }
}
