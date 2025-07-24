<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use Illuminate\Support\Facades\View;
use App\Models\Users\AttendanceBonusUserListFactory;
use App\Models\Users\UserListFactory;

class AttendanceBonusList extends Controller
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

   public function index($att_bo_id = null)
   {
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

      $viewData['title'] = 'Bonus List';

      extract(FormVariables::GetVariables(['action', 'att_bo_id']));
      $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
      // dd($action, $att_bo_id);

      switch ($action) {
         case 'submit':
            break;

         default:
            $data = array();

            if (isset($att_bo_id)) {
               $abulf = new AttendanceBonusUserListFactory();
               $abulf->getByBonusAttendanceId($att_bo_id);
// dd($abulf);
               foreach ($abulf->rs as $bau_obj) {
                     $abulf->data = (array)$bau_obj;
                        $bau_obj = $abulf;
               //    $data[] = array(
               //       'id' => $bau_obj->getId(),
               //       'empno' => $bau_obj->getUserObject()->getEmployeeNumber(),
               //       'name' => $bau_obj->getUserObject()->getFullName(),
               //       'amount' => number_format($bau_obj->getBonusAmount(), 2),
               //    );

                  try {
                            $userObject = $bau_obj->getUserObject();

                            // Skip this record if user object is invalid
                            if (!$userObject || !is_object($userObject)) {
                                continue;
                            }

                            $data[] = [
                                'id' => $bau_obj->getId(),
                                'empno' => $userObject->getEmployeeNumber() ?? 'N/A',
                                'name' => $userObject->getFullName() ?? 'Unknown',
                                'amount' => number_format($bau_obj->getBonusAmount(), 2),
                            ];
                        } catch (\Exception $e) {
                            // Log the error and continue with next record
                            \Log::error("Error processing user bonus: " . $e->getMessage());
                            continue;
                        }
               }
            }

            $viewData['data'] = $data;
            // dd($viewData);
            break;
      }

      $viewData['user_options'] = UserListFactory::getByCompanyIdArray($current_company->getId(), FALSE);
      return view('users/AttendanceBonusList', $viewData);
   }
}
