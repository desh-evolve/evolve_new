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
use App\Models\Message\MessageControlListFactory;
use App\Models\Message\MessageFactory;
use App\Models\Message\MessageRecipientListFactory;
use App\Models\Message\MessageSenderListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class CurrencyList extends Controller
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
				OR !( $permission->Check('message','view') OR $permission->Check('message','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Message List';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'filter_folder_id',
				'ids',
			) 
		) );
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}
		
		$mcf = new MessageFactory();		

		$mclf = new MessageControlListFactory();

		$folder_options = $mclf->getOptions('folder');

		Debug::text('Filter Folder ID: '. $filter_folder_id, __FILE__, __LINE__, __METHOD__,9);
		if ( !isset($filter_folder_id) OR !in_array($filter_folder_id, array_keys($folder_options) ) ) {
			Debug::text('Invalid Folder, using default ', __FILE__, __LINE__, __METHOD__,9);
			$filter_folder_id = 10;
		}

		//Make sure folder and sort columns stays as we switch pages.
		URLBuilder::setURL(NULL, array('filter_folder_id' => $filter_folder_id, 'sort_column' => $sort_column, 'sort_order' => $sort_order) );

		$mclf->getByCompanyIdAndUserIdAndFolder( $current_user->getCompany(), $current_user->getId(), $filter_folder_id, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($mclf);
		if ( $mclf->getRecordCount() > 0 ) {
			$object_name_options = $mclf->getOptions('object_name');

			foreach ($mclf->rs as $message) {
				$mclf->data = (array)$message;
				$message = $mclf;

				//Get user info
				$user_id = NULL;
				$user_full_name = NULL;
				if ( $filter_folder_id == 10 ) { //Inbox
					$user_id = $message->getColumn('from_user_id');
					$user_full_name = Misc::getFullName( $message->getColumn('from_first_name'), $message->getColumn('from_middle_name'), $message->getColumn('from_last_name') );
				} else { //Sent
					$user_id = $message->getColumn('to_user_id');
					$user_full_name = Misc::getFullName( $message->getColumn('to_first_name'), $message->getColumn('to_middle_name'), $message->getColumn('to_last_name') );
				}

				$messages[] = array(
									'id' => $message->getId(),
									'parent_id' => $message->getParent(),
									'object_type_id' => $message->getObjectType(),
									'object_type' => Option::getByKey($message->getObjectType(), $object_name_options ),
									'object_id' => $message->getObject(),
									//'priority' => $message->getPriority(),
									'status_id' => $message->getStatus(),
									//'require_ack' => $message->getRequireAck(),
									//'ack_date' => $message->getAckDate(),
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

		
		$viewData['messages'] = $messages;
		$viewData['require_ack'] = $require_ack;
		$viewData['show_ack_column'] = $show_ack_column;
		
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;

		$viewData['paging_data'] = $pager->getPageVariables();
		
		$viewData['mf'] = $mf;
		$viewData['folder_options'] = $folder_options;
		$viewData['filter_folder_id'] = $filter_folder_id;

        return view('message/UserMessageList', $viewData);

    }

	public function new_message(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditMessage.php', FALSE) );
	}

	public function delete(){
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'filter_folder_id',
				'ids',
			) 
		) );
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}
		
		$mcf = new MessageFactory();

		
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		if ( is_array($ids) AND count($ids) > 0 AND ( $permission->Check('message','delete') OR $permission->Check('message','delete_own') ) ) {
			$mcf->StartTransaction();

			Debug::text('Filter Folder ID: '. $filter_folder_id, __FILE__, __LINE__, __METHOD__,9);
			if ( $filter_folder_id == 10 ) { //Inbox
				$mrlf = new MessageRecipientListFactory();
				$mrlf->getByCompanyIdAndUserIdAndMessageSenderId( $current_company->getId(), $current_user->getId(), $ids );
				foreach ($mrlf->rs as $m_obj) {
					$mrlf->data = (array)$m_obj;
					$m_obj = $mrlf;
					$m_obj->setDeleted($delete);
					$m_obj->Save();
				}
			} else { //Sent
				$mslf = new MessageSenderListFactory();
				$mslf->getByCompanyIdAndUserIdAndId( $current_company->getId(), $current_user->getId(), $ids );
				foreach ($mslf->rs as $m_obj) {
					$mrlf->data = (array)$m_obj;
					$m_obj = $mrlf;
					$m_obj->setDeleted($delete);
					$m_obj->Save();
				}
			}
			//$mcf->FailTransaction();
			$mcf->CommitTransaction();

		}

		Redirect::Page( URLBuilder::getURL( array('filter_folder_id' => $filter_folder_id ), 'UserMessageList.php') );
	}
}




?>