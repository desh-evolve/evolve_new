<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyListFactory;
use Illuminate\Http\Request;


use App\Models\Core\Environment;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;


class BirthdayReminders extends Controller
{
    protected $permission;
    protected $currentUser;
    protected $currentCompany;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');

    }


    public function index() {
		/*
        if ( $permission->Check('accrual','view') OR $permission->Check('accrual','view_child')) {
            $user_id = $user_id;
        } else {
            $user_id = $current_user->getId();
        }
        */

        $viewData['title'] = 'Todays Birthday';

		
		$ulf = new UserListFactory();
		$clf = new CompanyListFactory();

		$ulf->getByCompanyIdandBirthday(1);


		foreach ($ulf->rs as $u_obj) {
			$ulf->data = (array)$u_obj;
			$u_obj = $ulf;
    
			$company_name = $clf->getById( $u_obj->getCompany() )->getCurrent()->getName();

			$users[] = array(
						'id' => $u_obj->getId(),
						'company_id' => $u_obj->getCompany(),
						'employee_number' => $u_obj->getEmployeeNumber(),
						'status_id' => $u_obj->getStatus(),
						'status' => Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions('status') ),
						'user_name' => $u_obj->getUserName(),
						'phone_id' => $u_obj->getPhoneID(),
						'ibutton_id' => $u_obj->getIButtonID(),

						'full_name' => $u_obj->getFullName(TRUE),
						'first_name' => $u_obj->getFirstName(),
						'middle_name' => $u_obj->getMiddleName(),
						'last_name' => $u_obj->getLastName(),

						'nic' => $u_obj->getNic(),

						'title' => Option::getByKey($u_obj->getTitle(), $title_options ),
						'user_group' => Option::getByKey($u_obj->getGroup(), $group_options ),

						'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
						'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),

						'sex_id' => $u_obj->getSex(),
						'sex' => Option::getByKey($u_obj->getSex(), $u_obj->getOptions('sex') ),

						'address1' => $u_obj->getAddress1(),
						'address2' => $u_obj->getAddress2(),
						'city' => $u_obj->getCity(),
						'province' => $u_obj->getProvince(),
						'country' => $u_obj->getCountry(),
						'postal_code' => $u_obj->getPostalCode(),
						'work_phone' => $u_obj->getWorkPhone(),
						'home_phone' => $u_obj->getHomePhone(),
						'mobile_phone' => $u_obj->getMobilePhone(),
						'fax_phone' => $u_obj->getFaxPhone(),
						'home_email' => $u_obj->getHomeEmail(),
						'work_email' => $u_obj->getWorkEmail(),
						'birth_date' => TTDate::getDate('DATE', $u_obj->getBirthDate() ),
						'sin' => $u_obj->getSecureSIN(),
						'hire_date' => TTDate::getDate('DATE', $u_obj->getHireDate() ),
						'termination_date' => TTDate::getDate('DATE', $u_obj->getTerminationDate() ),

						'map_url' => $u_obj->getMapURL(),
						
						'is_owner' => $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() ),
						'is_child' => $permission->isChild( $u_obj->getId(), $permission_children_ids ),
						'deleted' => $u_obj->getDeleted(),
					);
		}

		$viewData['users'] = $users;

        return view('company/BirthdayReminders', $viewData);

    }

}
