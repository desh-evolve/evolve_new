<?php

namespace App\Http\Controllers\message;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualBalanceFactory;
use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\Accrual\AccrualListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Message\MessageControlFactory;
use App\Models\Message\MessageRecipientFactory;
use App\Models\Message\MessageSenderFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditMessage extends Controller
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
        if ( !$permission->Check('message','enabled')
				OR !( $permission->Check('message','edit') OR $permission->Check('message','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		
		$mcf = new MessageControlFactory();
		$mrf = new MessageRecipientFactory();
		$msf = new MessageSenderFactory();

        $viewData['title'] = 'New Message';


		if ( $permission->Check('message','send_to_any') ) {
			$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE);
			$data['user_options'] = Misc::arrayDiffByKey( (array)$filter_user_id, $user_options );
			$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $user_options );
		} else {
			//Only allow sending to supervisors OR children.
			$hlf = new HierarchyListFactory();

			//FIXME: For supervisors, we may need to include supervisors at the same level
			// Also how to handle cases where there are no To: recipients to select from.

			//Get Parents
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID($current_company->getId(), $current_user->getId(), array(1010,1020,1030,1040,1100), FALSE, FALSE );
			//Debug::Arr( $request_parent_level_user_ids, 'Request Parent Level Ids', __FILE__, __LINE__, __METHOD__,10);

			//Get Children, in case the current user is a superior.
			$request_child_level_user_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId(), array(1010,1020,1030,1040,1100) );
			//Debug::Arr( $request_child_level_user_ids, 'Request Child Level Ids', __FILE__, __LINE__, __METHOD__,10);

			$request_user_ids = array_merge( (array)$request_parent_level_user_ids, (array)$request_child_level_user_ids );
			//Debug::Arr( $request_user_ids, 'User Ids', __FILE__, __LINE__, __METHOD__,10);

			$ulf = new UserListFactory();
			$ulf->getByIdAndCompanyId( $request_user_ids, $current_user->getCompany() );
			$user_options = UserListFactory::getArrayByListFactory( $ulf, TRUE, FALSE);

			//$data['user_options'] = Misc::arrayDiffByKey( (array)$filter_user_id, $user_options );
			$data['user_options'] = $user_options;
			$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $user_options );
		}


		$viewData['data'] = $data;
		$viewData['filter_user_options'] = $filter_user_options;
		$viewData['filter_user_id'] = $filter_user_id;
		$viewData['mcf'] = $mcf;

        return view('message/EditMessage', $viewData);

    }

	public function submit_message(){
		$mcf = new MessageControlFactory();
		
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$redirect = TRUE;
		//Make sure the only array entry isn't 0 => 0;
		if ( is_array($filter_user_id) AND count($filter_user_id) > 0 AND ( isset($filter_user_id[0]) AND $filter_user_id[0] != 0 ) ) {
			$mcf->StartTransaction();

			$mcf = new MessageControlFactory();
			$mcf->setFromUserId( $current_user->getId() );
			$mcf->setToUserId( $filter_user_id );
			$mcf->setObjectType( 5 );
			$mcf->setObject( $current_user->getId() );
			$mcf->setParent( 0 );
			$mcf->setSubject( $data['subject'] );
			$mcf->setBody( $data['body'] );

			if ( $mcf->isValid() ) {
				$mcf->Save();

				$mcf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( NULL, 'UserMessageList.php') );
			}
			$mcf->FailTransaction();
		} else {
			$mcf->Validator->isTrue(	'to',
									FALSE,
									_('Please select at least one recipient') );
		}
	}
}


?>