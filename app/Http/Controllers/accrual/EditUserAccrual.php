<?php

namespace App\Http\Controllers\accrual;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualFactory;
use App\Models\Accrual\AccrualListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditUserAccrual extends Controller
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

    public function index($id = null) {
        /*
        if ( !$permission->Check('accrual','enabled')
                OR !( $permission->Check('accrual','edit') OR $permission->Check('accrual','edit_own') OR $permission->Check('accrual','edit_child') ) ) {
            $permission->Redirect( FALSE ); //Redirect
        }
        */

        $viewData['title'] = $id ? 'Edit Accrual' : 'Add Accrual';

        if ( isset($id) ) { //edit

			$alf = new AccrualListFactory();
			$alf->getById($id);

			foreach ($alf->rs as $a_obj) {
                $alf->data = (array)$a_obj;
                $a_obj = $alf;

				$data = array(
                    'id' => $a_obj->getId(),
                    'user_id' => $a_obj->getUser(),
                    'accrual_policy_id' => $a_obj->getAccrualPolicyID(),
                    'type_id' => $a_obj->getType(),
                    'amount' => ($a_obj->getAmount()/8),
                    'time_stamp' => $a_obj->getTimeStamp(),
                    'user_date_total_id' => $a_obj->getUserDateTotalID(),
                    'created_date' => $a_obj->getCreatedDate(),
                    'created_by' => $a_obj->getCreatedBy(),
                    'updated_date' => $a_obj->getUpdatedDate(),
                    'updated_by' => $a_obj->getUpdatedBy(),
                    'deleted_date' => $a_obj->getDeletedDate(),
                    'deleted_by' => $a_obj->getDeletedBy()
                );
			}
		} else { //add
			if ( $user_id == '' ) {
				$user_id = $filter_user_id;
			}
			$data = array(
                'user_id' => $user_id,
                'accrual_policy_id' => $accrual_policy_id,
                'amount' => 0,
                'time_stamp' => TTDate::getTime()
			);
		}

		$aplf = new AccrualPolicyListFactory();
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );

		//Select box options;
		$data['type_options'] = $af->getOptions('user_type');
		$data['user_options'] = $user_options;
		$data['accrual_policy_options'] = $accrual_options;

        $viewData['data'] = $data;

        return view('accrual/EditUserAccrual', $viewData);
    }
    
    public function save(Request $request, $id = null){
        $data = $request->all();

        $af = new AccrualFactory();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
            
		$af->setId( $data['id'] );
		$af->setUser( $data['user_id'] );
		$af->setType( $data['type_id'] );
		$af->setAccrualPolicyID( $data['accrual_policy_id'] );
		$af->setAmount( $data['amount'] *8);
		$af->setTimeStamp( $data['time_stamp'] );
		$af->setEnableCalcBalance( TRUE );

		if ( $af->isValid() ) {
			$af->Save();
            return redirect()->to(URLBuilder::getURL(null, '/accrual'))->with('success', 'Accrual saved successfully.');
		}

    }
}
