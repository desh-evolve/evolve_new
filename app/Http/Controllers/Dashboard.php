<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Company\CompanyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\ExceptionListFactory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\UserDateListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyLevelListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Leaves\AbsenceLeaveUserEntryRecordListFactory;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Message\MessageControlListFactory;
use App\Models\Punch\PunchControlListFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Schedule\ScheduleListFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class Dashboard extends Controller
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
        return view('dashboard');
    }

    public function userCount()
    {
        $ulf = new UserListFactory();
        $ulf->getAll();

        $count = 0;

        foreach ($ulf->rs as $u_obj) {
            $ulf->data = (array)$u_obj;
            $u_obj = $ulf;

            $count++;
        }

        return response()->json(['user_count' => $count]);
    }


    public function confirmedLeaveCount()
    {
        $lrlf = new LeaveRequestListFactory();

        // Pass null and empty array to ignore all filters
        $lrlf->getAllConfirmedLeave(0, []);

        // Get total number of confirmed leave records
        $count = $lrlf->getRecordCount();

        return response()->json(['confirmed_leave_count' => $count]);
    }


   public function getTodaysConfirmedLeavesCount()
    {
        $lrlf = new LeaveRequestListFactory();
        $today = date('Y-m-d');

        // Get all confirmed leaves (no date filter)
        $filter_data = []; // No restrictions
        $lrlf->getAllConfirmedLeave(0, $filter_data);

        $count = 0;

        if ($lrlf->getRecordCount() > 0) {
            foreach ($lrlf->rs as $leave) {
                if (
                    isset($leave->leave_from, $leave->leave_to)
                    && $leave->leave_to >= $today // must end today or in future
                    && $leave->leave_from <= $today // must have started already or today
                ) {
                    $count++;
                }
            }
        }

        return response()->json(['confirmed_leave_count' => $count]);
    }



    public function threeDaysAbsenteeism()
    {
        $current_company = $this->currentCompany;
		$permission = $this->permission;
        $threeDaysAbsence = [];

        if(!isset($filter_data)){
            $filter_data = array();
        }

        if ( $permission->Check('authorization','enabled')
			AND $permission->Check('authorization','view')
			AND $permission->Check('request','authorize') ) {


            $ulf1 = new UserListFactory();
            $uf1 = new UserFactory();
            $ulf1->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data);

            foreach ($ulf1->rs as $u_obj) {
                $ulf1->data = (array)$u_obj;
                $u_obj = $ulf1;

                $nofAbsence=0;

                for($d=0;$d<3;$d++){

                    $udlf = new UserDateListFactory();
                    // $check_date = date('Y-m-d',  mktime(0, 0, 0, date("m")  , date("d")-$d, date("Y")));
                    $check_date = strtotime("-{$d} days"); // returns a timestamp
                    $udlf->getByUserIdAndDate($u_obj->getId(), $check_date);
                    $udlf_obj = $udlf->getCurrent();
                    $user_date_id = $udlf_obj->getId();

                    $slf = new ScheduleListFactory();
                    $slf->getByUserDateId($user_date_id);
                    $slf_obj_arr = $slf->getCurrent()->data;

                    if(!empty($slf_obj_arr))
                    {
                        $pclf = new PunchControlListFactory();
                        $pclf->getByUserDateId($user_date_id); //par - user_date_id
                        $pc_obj_arr = $pclf->getCurrent()->data;

                        if(empty($pc_obj_arr))
                        {

                            $aluelf = new AbsenceLeaveUserEntryRecordListFactory();
                            $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                            $absLeave_obj_arr = $aluelf->getCurrent()->data;


                            /*if(!empty($absLeave_obj_arr)){
                                $leaveName = $absLeave_obj_arr['absence_name'];

                            }
                            else{
                                $leaveName = 'Unscheduled Absence.';
                            }*/

                            if(empty($absLeave_obj_arr)){
                                $nofAbsence += 1;

                            }
                        }

                    }

                }
                //die;

                if($nofAbsence>=3)
                {
                    $threeDaysAbsence[] = array(
                        'full_name' => $u_obj->getFullName(TRUE),
                        'default_branch' => BranchListFactory::getNameById($u_obj->getDefaultBranch()),
                        'default_department' => DepartmentListFactory::getNameById( $u_obj->getDefaultDepartment() ),

                    );
                }
            }
        }

        return response()->json(['data' => $threeDaysAbsence]);
    }


    public function recentMessges()
    {
		$current_user = $this->currentUser;
        $mclf = new MessageControlListFactory();
        $messages = [];

        $mclf->getByCompanyIdAndUserIdAndFolder( $current_user->getCompany(), $current_user->getId(), 10, 5, 1 );

        if ( $mclf->getRecordCount() > 0 ) {
            $object_name_options = $mclf->getOptions('object_name');

            foreach ($mclf->rs as $message) {
                $mclf->data = (array)$message;
			    $message = $mclf;

                //Get user info
                $user_id = $message->getColumn('from_user_id');
                $user_full_name = Misc::getFullName( $message->getColumn('from_first_name'), $message->getColumn('from_middle_name'), $message->getColumn('from_last_name') );

                $messages[] = array(
                                    'id' => $message->getId(),
                                    'parent_id' => $message->getParent(),
                                    'object_type_id' => $message->getObjectType(),
                                    'object_type' => Option::getByKey($message->getObjectType(), $object_name_options ),
                                    'object_id' => $message->getObject(),
                                    'status_id' => $message->getStatus(),
                                    'subject' => $message->getSubject(),
                                    'body' => $message->getBody(),

                                    'user_id' => $user_id,
                                    'user_full_name' =>  $user_full_name,
                                    'created_date' => $message->getCreatedDate(),
                                    'created_by' => $message->getCreatedBy(),
                                    'updated_date' => $message->getUpdatedDate(),
                                    'updated_by' => $message->getUpdatedBy(),
                                    'deleted_date' => $message->getDeletedDate(),
                                    'deleted_by' => $message->getDeletedBy()
                                );
            }
        }

         return response()->json(['data' => $messages]);
    }


    public function recentRequest()
    {
        $current_user = $this->currentUser;
        $current_company = $this->currentCompany;
        $rlf = new RequestListFactory();

        $requests = [];

        $rlf->getByUserIDAndCompanyId( $current_user->getId(), $current_company->getId(), 5, 1 );
        if ($rlf->getRecordCount() > 0 ) {
            $status_options = $rlf->getOptions('status');
            $type_options = $rlf->getOptions('type');

            foreach ($rlf->rs as $r_obj) {
                $rlf->data = (array)$r_obj;
			    $r_obj = $rlf;

                $requests[] = array(
                                    'id' => $r_obj->getId(),
                                    'user_date_id' => $r_obj->getUserDateID(),
                                    'date_stamp' => TTDate::strtotime($r_obj->getColumn('date_stamp')),
                                    'status_id' => $r_obj->getStatus(),
                                    'status' => Misc::TruncateString( $status_options[$r_obj->getStatus()], 15 ),
                                    'type_id' => $r_obj->getType(),
                                    'type' => $type_options[$r_obj->getType()],
                                    'created_date' => $r_obj->getCreatedDate(),
                                    'deleted' => $r_obj->getDeleted()
                                );
            }
        }
        return response()->json(['data' => $requests]);
    }


    public function pendingRequest()
    {
        $permission = $this->permission;
        $current_user = $this->currentUser;

        if ( $permission->Check('authorization','enabled')
		AND $permission->Check('authorization','view')
		AND $permission->Check('request','authorize') ) {

            $hllf = new HierarchyLevelListFactory();
            $request_levels = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), array(1010,1020,1030,1040,1100) );

            $pending_requests = [];

            $selected_levels['request'] = 1;

            if ( isset($selected_levels['request']) AND isset($request_levels[$selected_levels['request']]) ) {
                $request_selected_level = $request_levels[$selected_levels['request']];
                Debug::Text(' Switching Levels to Level: '. key($request_selected_level), __FILE__, __LINE__, __METHOD__,10);
            } elseif ( isset($request_levels[1]) ) {
                $request_selected_level = $request_levels[1];
            } else {
                Debug::Text( 'No Request Levels... Not in hierarchy?', __FILE__, __LINE__, __METHOD__,10);
                $request_selected_level = 0;
            }

            if ( is_array($request_selected_level) ) {

                $rlf = new RequestListFactory();
                $rlf->getByHierarchyLevelMapAndStatusAndNotAuthorized($request_selected_level, 30);

                $status_options = $rlf->getOptions('status');
                $type_options = $rlf->getOptions('type');

                foreach( $rlf->rs as $r_obj) {
                    $rlf->data = (array)$r_obj;
			        $r_obj = $rlf;

                    //Grab authorizations for this object.
                    $pending_requests[] = array(
                                            'id' => $r_obj->getId(),
                                            'user_date_id' => $r_obj->getId(),
                                            'user_id' => $r_obj->getUserDateObject()->getUser(),
                                            'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
                                            'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
                                            'type_id' => $r_obj->getType(),
                                            'type' => $type_options[$r_obj->getType()],
                                            'status_id' => $r_obj->getStatus(),
                                            'status' => $status_options[$r_obj->getStatus()]
                                        );
                }
            } else {
                Debug::Text( 'No hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
            }

            return response()->json(['data' => $pending_requests]);
            unset($pending_requests, $request_hierarchy_id, $request_user_id, $request_node_data, $request_current_level_user_ids, $request_parent_level_user_ids, $request_child_level_user_ids );
        }
    }


    public function exception()
    {
        $current_user = $this->currentUser;
        $exceptions = array();

        $elf = new ExceptionListFactory();
        $elf->getFlaggedExceptionsByUserIdAndPayPeriodStatus( $current_user->getId(), 10 );

        if ( $elf->getRecordCount() > 0 ) {

            foreach($elf->rs as $e_obj) {
                $elf->data = (array)$e_obj;
                $e_obj = $elf;

                $exceptions[$e_obj->getColumn('severity_id')] = $e_obj->getColumn('total');
            }
        }
        unset($elf, $e_obj);

        return response()->json(['data' => $exceptions]);

    }


    public function employmentConfirmationRequest()
    {
        $current_company = $this->currentCompany;
        $ulf1 = new UserListFactory();
        $uf1 = new UserFactory();

        if(!isset($filter_data)){
            $filter_data = array();
        }

        $ulf1->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data);
        $users1 = [];

            foreach ($ulf1->rs as $u_obj) {
                $ulf1->data = (array)$u_obj;
                $u_obj = $ulf1;
                    //$company_name = $clf->getById( $u_obj->getCompany() )->getCurrent()->getName();
                    //echo $u_obj;
                    //print_r($u_obj);
                    //exit('this is INTEX Object');
                    if($u_obj->getMonth() > 0 && $u_obj->getBasisOfEmployment() > 0 && $u_obj->getBasisOfEmployment() != 4 && $u_obj->getBasisOfEmployment() != 6)
                    {

                        $users1[] = array(
                                'id' => $u_obj->getId(),
                                'company_id' => $u_obj->getCompany(),
                                'employee_number' => $u_obj->getEmployeeNumber(),
        //									'status_id' => $u_obj->getStatus(),
        //									'status' => Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions('status') ),
        //									'user_name' => $u_obj->getUserName(),
        //									'phone_id' => $u_obj->getPhoneID(),
        //									'ibutton_id' => $u_obj->getIButtonID(),
        //
                                'full_name' => $u_obj->getFullName(TRUE),
        //									'first_name' => $u_obj->getFirstName(),
        //									'middle_name' => $u_obj->getMiddleName(),
        //									'last_name' => $u_obj->getLastName(),
        //
        //									'title' => Option::getByKey($u_obj->getTitle(), $title_options ),
        //									'user_group' => Option::getByKey($u_obj->getGroup(), $group_options ),
        //
        //									'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
        //									'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),
        //
        //									'sex_id' => $u_obj->getSex(),
        //									'sex' => Option::getByKey($u_obj->getSex(), $u_obj->getOptions('sex') ),
        //
        //									'address1' => $u_obj->getAddress1(),
        //									'address2' => $u_obj->getAddress2(),
        //									'city' => $u_obj->getCity(),
        //									'province' => $u_obj->getProvince(),
        //									'country' => $u_obj->getCountry(),
        //									'postal_code' => $u_obj->getPostalCode(),
        //									'work_phone' => $u_obj->getWorkPhone(),
        //									'home_phone' => $u_obj->getHomePhone(),
        //									'mobile_phone' => $u_obj->getMobilePhone(),
        //									'fax_phone' => $u_obj->getFaxPhone(),
        //									'home_email' => $u_obj->getHomeEmail(),
        //									'work_email' => $u_obj->getWorkEmail(),
        //									'birth_date' => TTDate::getDate('DATE', $u_obj->getBirthDate() ),
        //									'sin' => $u_obj->getSecureSIN(),

                                                                                //'hire_date' => TTDate::getDate('DATE', $u_obj->getHireDate() ),
                                    'hire_date' => $u_obj->getHireDate(),

                /* ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON  */
                                    'resign_date' => $u_obj->getResignDate(),

        //									'termination_date' => TTDate::getDate('DATE', $u_obj->getTerminationDate() ),
        //
        //									'map_url' => $u_obj->getMapURL(),
        //
        //									'is_owner' => $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() ),
        //									'is_child' => $permission->isChild( $u_obj->getId(), $permission_children_ids ),
        //									'deleted' => $u_obj->getDeleted(),
                        //ARSP NOT --> I HIDE THIS CODE FOR THUNDER & NEON   'probation'=>$u_obj->getProbation()
                                    'basis_of_employment' =>$u_obj->getBasisOfEmployment(),
                                    'month'=>$u_obj->getMonth()
                            );
                    }
        }
        //print_r($users1);
        //exit('this is INTEX Object');


        //print_r($uf->getWarningEmployees($users));
        $basis_of_employment_warning_employees  = $uf1->getWarningBasisOfEmployment($users1);
        //print_r($basis_of_employment_warning_employees);
        //exit('this is INTEX Object');

        return response()->json(['data' => $basis_of_employment_warning_employees]);

    }


    public function search(Request $request)
    {
        $current_company = $this->currentCompany;
        $ulf1 = new UserListFactory();
        $uf1 = new UserFactory();

        if(!isset($filter_data)){
            $filter_data = array();
        }

        // Get input from request
        $filter_data['start_date'] = $request->input('start_date');
        $filter_data['end_date'] = $request->input('end_date');
        $filter_data['basis_of_employment'] = $request->input('category');

        //echo '<pre>'; print_r($filter_data['basis_of_employment']); echo '<pre>';  die;
        $ulf1->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data);
        // echo '<pre>'; print_r($filter_data); echo '<pre>';  die;

        $users1 = [];
            foreach ($ulf1->rs as $u_obj) {
                $ulf1->data = (array)$u_obj;
                $u_obj = $ulf1;
                    //$company_name = $clf->getById( $u_obj->getCompany() )->getCurrent()->getName();
                    //echo $u_obj;
                    //print_r($u_obj);
                    //exit('this is INTEX Object');
                    if($u_obj->getMonth() > 0 && $u_obj->getBasisOfEmployment() > 0 && $u_obj->getBasisOfEmployment() != 4 && $u_obj->getBasisOfEmployment() != 6)
                    {

                        $users1[] = array(
                                'id' => $u_obj->getId(),
                                'company_id' => $u_obj->getCompany(),
                                'employee_number' => $u_obj->getEmployeeNumber(),
        //									'status_id' => $u_obj->getStatus(),
        //									'status' => Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions('status') ),
        //									'user_name' => $u_obj->getUserName(),
        //									'phone_id' => $u_obj->getPhoneID(),
        //									'ibutton_id' => $u_obj->getIButtonID(),
        //
                                'full_name' => $u_obj->getFullName(TRUE),
        //									'first_name' => $u_obj->getFirstName(),
        //									'middle_name' => $u_obj->getMiddleName(),
        //									'last_name' => $u_obj->getLastName(),
        //
        //									'title' => Option::getByKey($u_obj->getTitle(), $title_options ),
        //									'user_group' => Option::getByKey($u_obj->getGroup(), $group_options ),
        //
        //									'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
        //									'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),
        //
        //									'sex_id' => $u_obj->getSex(),
        //									'sex' => Option::getByKey($u_obj->getSex(), $u_obj->getOptions('sex') ),
        //
        //									'address1' => $u_obj->getAddress1(),
        //									'address2' => $u_obj->getAddress2(),
        //									'city' => $u_obj->getCity(),
        //									'province' => $u_obj->getProvince(),
        //									'country' => $u_obj->getCountry(),
        //									'postal_code' => $u_obj->getPostalCode(),
        //									'work_phone' => $u_obj->getWorkPhone(),
        //									'home_phone' => $u_obj->getHomePhone(),
        //									'mobile_phone' => $u_obj->getMobilePhone(),
        //									'fax_phone' => $u_obj->getFaxPhone(),
        //									'home_email' => $u_obj->getHomeEmail(),
        //									'work_email' => $u_obj->getWorkEmail(),
        //									'birth_date' => TTDate::getDate('DATE', $u_obj->getBirthDate() ),
        //									'sin' => $u_obj->getSecureSIN(),

                                                                                //'hire_date' => TTDate::getDate('DATE', $u_obj->getHireDate() ),
                                    'hire_date' => $u_obj->getHireDate(),

                                    /* ASRP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON  */
                                    'resign_date' => $u_obj->getResignDate(),

        //									'termination_date' => TTDate::getDate('DATE', $u_obj->getTerminationDate() ),
        //
        //									'map_url' => $u_obj->getMapURL(),
        //
        //									'is_owner' => $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() ),
        //									'is_child' => $permission->isChild( $u_obj->getId(), $permission_children_ids ),
        //									'deleted' => $u_obj->getDeleted(),
                                            //ARSP NOT --> I HIDE THIS CODE FOR THUNDER & NEON   'probation'=>$u_obj->getProbation()
                                    'basis_of_employment' =>$u_obj->getBasisOfEmployment(),
                                    'month'=>$u_obj->getMonth()
                            );
                    }
        }
        //print_r($users1);
        //exit('this is INTEX Object');


        //print_r($uf->getWarningEmployees($users));
        $basis_of_employment_warning_employees  = $uf1->getWarningBasisOfEmployment($users1);
        // print_r($basis_of_employment_warning_employees);
        //exit('this is INTEX Object');

        return response()->json(['data' => $basis_of_employment_warning_employees]);

    }




}
