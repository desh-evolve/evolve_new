<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use Illuminate\Support\Facades\View;
use App\Models\Users\AttendanceBonusListFactory;
use App\Models\Users\UserDeductionListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;

class UserDeductionList extends Controller
{
    protected $permission;
    protected $current_user;
    protected $current_company;
    protected $current_user_prefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->current_user = View::shared('current_user');
        $this->current_company = View::shared('current_company');
        $this->current_user_prefs = View::shared('current_user_prefs');
    }

    public function index(Request $request)
    {
        $permission = $this->permission;
        $current_user = $this->current_user;
        $current_company = $this->current_company;
        $current_user_prefs = $this->current_user_prefs;

		if ( !$permission->Check('user_tax_deduction','enabled')
				OR !( $permission->Check('user_tax_deduction','view') OR $permission->Check('user_tax_deduction','view_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect

		}

		$viewData['title'] = 'Employee Tax / Deduction List';

		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
												array	(
														'action',
														'page',
														'sort_column',
														'sort_order',
														'saved_search_id',
														'user_id',
														'ids',
														) ) );

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
													array(
															'sort_column' => $sort_column,
															'sort_order' => $sort_order,
															'page' => $page
														) );

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

		$ulf = new UserListFactory();

        $user_id = $request->input('user_id', $current_user->getId());
        $filter_user_id = $user_id;

		//===================================================================================
        $action = '';
        if (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
        //===================================================================================

		switch ($action) {
			case 'add':

				Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id, 'saved_search_id' => $saved_search_id ), '/user/tax/add', FALSE) );

				break;
			case 'delete' OR 'undelete':
				if ( strtolower($action) == 'delete' ) {
					$delete = TRUE;
				} else {
					$delete = FALSE;
				}

				$udlf = new UserDeductionListFactory();

				if ( isset($ids) AND is_array($ids) ) {
					foreach ($ids as $id) {
						$udlf->getByCompanyIdAndId($current_company->getId(), $id, $current_company->getId() );
						foreach ($udlf->rs as $ud_obj) {
							$udlf->data = (array)$ud_obj;
							$ud_obj = $udlf;

							$ud_obj->setDeleted($delete);
							if ( $ud_obj->isValid() ) {
								$ud_obj->Save();
							}
						}
					}
				}

				Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id ), '/user/tax') );

				break;
			default:

				//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
				$hlf = new HierarchyListFactory();
				$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

				$udlf = new UserDeductionListFactory();
				$udlf->getByCompanyIdAndUserId( $current_company->getId(), $user_id );

				$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );

				$rows = [];
				if ( $ulf->getRecordCount() > 0 ) {
					$user_obj = $ulf->getCurrent();

					if ( is_object($user_obj) ) {
						$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
						$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

						if ( $permission->Check('user_tax_deduction','view')
								OR ( $permission->Check('user_tax_deduction','view_own') AND $is_owner === TRUE )
								OR ( $permission->Check('user_tax_deduction','view_child') AND $is_child === TRUE ) ) {

							foreach ($udlf->rs as $ud_obj) {
								$udlf->data = (array)$ud_obj;
								$ud_obj = $udlf;

								$cd_obj = $ud_obj->getCompanyDeductionObject();

								$rows[] = array(
									'id' => $ud_obj->getId(),
									'status_id' => $cd_obj->getStatus(),
									'user_id' => $ud_obj->getUser(),
									'name' => $cd_obj->getName(),
									'type_id' => $cd_obj->getType(),
									'type' => Option::getByKey( $cd_obj->getType(), $cd_obj->getOptions('type') ),
									'calculation' => Option::getByKey( $cd_obj->getCalculation(), $cd_obj->getOptions('calculation') ),
									'is_owner' => $is_owner,
									'is_child' => $is_child,
									'deleted' => $ud_obj->getDeleted()
								);
							}
						}
					}
				}

				$viewData['rows'] = $rows;
				$viewData['user_id'] = $user_id;

				$ulf = new UserListFactory();

				$filter_data = NULL;
				$ugdf = new UserGenericDataFactory();
				//extract( $ugdf->getSearchFormData( $saved_search_id, NULL ) );

				if ( $permission->Check('user_tax_deduction','view') == FALSE ) {
					if ( $permission->Check('user_tax_deduction','view_child') ) {
						$filter_data['permission_children_ids'] = $permission_children_ids;
					}
					if ( $permission->Check('user_tax_deduction','view_own') ) {
						$filter_data['permission_children_ids'][] = $current_user->getId();
					}
				}
				$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
				$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

				$viewData['user_options'] = $user_options;

                $viewData['is_child'] = $is_child;
                $viewData['is_owner'] = $is_owner;
				$viewData['sort_column'] = $sort_column ;
				$viewData['sort_order'] = $sort_order ;
				$viewData['saved_search_id'] = $saved_search_id ;

                // dd($viewData);
				break;
		}

		return view('users/UserDeductionList', $viewData);
	}

}
?>
