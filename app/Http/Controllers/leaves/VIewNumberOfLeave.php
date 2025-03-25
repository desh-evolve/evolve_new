<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use Illuminate\Support\Facades\View;

class VIewNumberOfLeave extends Controller
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

    public function index() {

      /*
      if ( !$permission->Check('accrual','view')
          OR (  $permission->Check('accrual','view_own') ) ) {
        $permission->Redirect( FALSE ); //Redirect
      } 
      */
      
      $viewData['title'] = 'Apply Employee Leaves';

      $header_leave = array();
      $total_asign_leave = array();
      $total_taken_leave = array();
      $total_balance_leave = array();
      
      $alf = new AccrualListFactory();
      
      $lrlf = new LeaveRequestListFactory();

      $lrlf->getById($id);
      
        if($lrlf->getRecordCount() >0){
      
          $arf = $lrlf->getCurrent();
          
          $aplf = new AccrualPolicyListFactory();
          $aplf->getByCompanyIdAndTypeId($current_company->getId(),20);
          
          foreach($aplf->rs as $apf){
            $aplf->data = (array)$apf;
            $apf = $aplf;
          
              $alf->getByCompanyIdAndUserIdAndAccrualPolicyIdAndStatusForLeave($current_company->getId(),$arf->getUser(),$apf->getId(),30);
              
              $header_leave[]['name']=$apf->getName();
                      
              if($alf->getRecordCount() > 0) {
                $af= $alf->getCurrent();
                $total_asign_leave[]['asign'] =  $af->getAmount()/28800;
              } else {
                  $total_asign_leave[]['asign'] = 0;
              }
          
              $ttotal =  $alf->getSumByUserIdAndAccrualPolicyId($arf->getUser(),$apf->getId());
            
              if($alf->getRecordCount() > 0) {
                $af= $alf->getCurrent();
                $total_taken_leave[]['taken'] =   ($af->getAmount()/28800)-($ttotal/28800);
                $total_balance_leave[]['balance'] = ($ttotal/28800);
              } else {
                  $total_taken_leave[]['taken'] = 0;
                  $total_balance_leave[]['balance'] = 0;
              }
              
          } 
      
        }
      
        $viewData['total_asign_leave'] = $total_asign_leave;
        $viewData['total_taken_leave'] = $total_taken_leave;
        $viewData['total_balance_leave'] = $total_balance_leave;
        $viewData['header_leave'] = $header_leave;
        $viewData['data'] = $data;
        $viewData['user'] = $current_user;

        return view('leaves/VIewNumberOfLeave', $viewData);

    }
}


?>