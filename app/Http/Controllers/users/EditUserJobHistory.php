<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserJobFactory;
use App\Models\Users\UserJobListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserJobHistory extends Controller
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

        // if ( !$permission->Check('wage','enabled')
        //         OR !( $permission->Check('wage','edit') OR $permission->Check('wage','edit_child') OR $permission->Check('wage','edit_own') OR $permission->Check('wage','add') ) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

    }


    public function index($id = null)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;
        $viewData['title'] = 'Employee Job History';

        $uwlf = new UserJobListFactory();
        $ulf = new UserListFactory();
        $ujf = new UserJobFactory();
        $tmp_effective_date = null;
        $user_id = request()->get('user_id') ?? $wage_data['user_id'] ?? null;

        //Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
        $hlf = new HierarchyListFactory();
        $permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );


        if ( isset($id) ) {

			$uwlf->getByIdAndCompanyId($id, $current_company->getId() );

            $job_history_data = [];

			foreach ($uwlf->rs as $wage) {
                $uwlf->data = (array)$wage;
                $wage = $uwlf;

				$user_obj = $ulf->getByIdAndCompanyId( $wage->getUser(), $current_company->getId() )->getCurrent();
                //print_r($user_obj);

				if ( is_object($user_obj) ) {
					$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
                    //echo $is_owner;
					$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );
                    //echo $is_owner;

					if ( $permission->Check('wage','edit')
							OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
							OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {

						$user_id = $wage->getUser();

						//Debug::Text('Labor Burden Hourly Rate: '. $wage->getLaborBurdenHourlyRate( $wage->getHourlyRate() ), __FILE__, __LINE__, __METHOD__,10);
						$job_history_data = array(

											'id' => $wage->getId(),
											'user_id' => $wage->getUser(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_branch_id' => $wage->getDefaultBranch(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_department_id' => $wage->getDefaultDepartment(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'title_id' => $wage->getTitle(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'first_worked_date' => $wage->getFirstWorkedDate(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'last_worked_date' => $wage->getLastWorkedDate(),
											'note' => $wage->getNote(),
											'created_date' => $wage->getCreatedDate(),
											'created_by' => $wage->getCreatedBy(),
											'updated_date' => $wage->getUpdatedDate(),
											'updated_by' => $wage->getUpdatedBy(),
											'deleted_date' => $wage->getDeletedDate(),
											'deleted_by' => $wage->getDeletedBy()
										);
                                                //print_r($wage_data);
					} else {
						$permission->Redirect( FALSE ); //Redirect
						exit;
					}
				}
			}
		} else {

            $ulf = new UserListFactory();
            $temp_default_branch_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getDefaultBranch();
            $temp_default_department_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getDefaultDepartment();
            $temp_title_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getTitle();

            //ARSP NOTE --> I MODIFIED THISC CODE
            $job_history_data = array( 'first_worked_date' => TTDate::getTime(), 'default_branch_id' => $temp_default_branch_id, 'default_department_id' => $temp_default_department_id, 'title_id' => $temp_title_id );

		}

        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		//Select box options;
		$blf = new BranchListFactory();
		$job_history_data['branch_options'] = $blf->getByCompanyIdArray( $current_company->getId() );

        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        //Select box options;
		$dlf = new DepartmentListFactory();
		$job_history_data['department_options'] = $dlf->getByCompanyIdArray( $current_company->getId() );

        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        //Select box options;
		$utlf = new UserTitleListFactory();
		$job_history_data['title_options'] = $utlf->getByCompanyIdArray( $current_company->getId() );

		$ulf = new UserListFactory();
		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		$user_data = $ulf->getCurrent();


		//Get pay period boundary dates for this user.
		//Include user hire date in the list.
		$pay_period_boundary_dates[TTDate::getDate('DATE', $user_data->getHireDate() )] = _('(Appointment Date)').' '. TTDate::getDate('DATE', $user_data->getHireDate() );
		$pay_period_boundary_dates = Misc::prependArray( array(-1 => _('(Choose Date)')), $pay_period_boundary_dates);


        $viewData['user_data'] = $user_data;

        $viewData['job_history_data'] = $job_history_data;
        $viewData['tmp_effective_date'] = $tmp_effective_date;
        $viewData['pay_period_boundary_date_options'] = $pay_period_boundary_dates;
        $viewData['ujf'] = $ujf;

        // dd($viewData);

        return view('users.editUserJobHistory', $viewData);

    }


    public function save(Request $request)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        $user_id = $request->input('user_id');
        $job_history_data = $request->all();


        $ulf = new UserListFactory();
        $ujf = new UserJobFactory();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ulf->getByIdAndCompanyId($user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

            $hlf = new HierarchyListFactory();
            $permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

            //ARSP NOTE --> I MODIFIED THIS CODE FOR THUNDER & NEON
            if ( isset($job_history_data) ) {
                if ( $job_history_data['first_worked_date'] != '' ) {
                    $job_history_data['first_worked_date'] = TTDate::parseDateTime($job_history_data['first_worked_date']);
                }
            }

            //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
            if ( isset($job_history_data) ) {
                if ( $job_history_data['last_worked_date'] != '' ) {
                    $job_history_data['last_worked_date'] = TTDate::parseDateTime($job_history_data['last_worked_date']);
                }
            }

			if ( $permission->Check('wage','edit')
					OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {

                $ujf->setFirstWorkedDate($job_history_data['first_worked_date']);
                $ujf->setLastWorkedDate($job_history_data['last_worked_date']);
				$ujf->setId($job_history_data['id']);
				$ujf->setUser($user_id);
                $ujf->setDefaultBranch($job_history_data['default_branch_id']);
                $ujf->setDefaultDepartment($job_history_data['default_department_id']);
                $ujf->setTitle($job_history_data['title_id']);
				$ujf->setNote( $job_history_data['note'] );

				if ( $ujf->isValid() ) {
					$ujf->Save();

                    return redirect()->to(URLBuilder::getURL(array('user_id' => $user_id) , '/user/jobhistory'))->with('success', 'Employee Job History saved successfully.');

				}
			} else {
				// If validation fails, return back with errors
                return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
			}
        }
    }

}
