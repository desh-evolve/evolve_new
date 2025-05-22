<?php

namespace App\Http\Controllers\message;

use App\Http\Controllers\Controller;
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
use App\Models\Message\MessageControlListFactory;
use App\Models\Message\MessageFactory;
use App\Models\Message\MessageRecipientListFactory;
use App\Models\Message\MessageSenderListFactory;
use Illuminate\Support\Facades\View;

class UserMessageList extends Controller
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

        /*
        if ( !$permission->Check('message','enabled')
				OR !( $permission->Check('message','view') OR $permission->Check('message','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
    }

    
    public function index()
    {
        $current_user = $this->currentUser;
        $current_user_prefs = $this->userPrefs;
        $viewData['title'] = 'Message List';


		$mf = new MessageFactory();
		$mclf = new MessageControlListFactory();

		$folder_options = $mclf->getOptions('folder');

        // Initialize variables to avoid undefined errors
        $filter_folder_id = request('filter_folder_id');
        $require_ack = false;
        $show_ack_column = false;
        $messages = [];

		Debug::text('Filter Folder ID: '. $filter_folder_id, __FILE__, __LINE__, __METHOD__,9);
		if ( !isset($filter_folder_id) OR !in_array($filter_folder_id, array_keys($folder_options) ) ) {
			Debug::text('Invalid Folder, using default ', __FILE__, __LINE__, __METHOD__,9);
			$filter_folder_id = 10;
		}

		//Make sure folder and sort columns stays as we switch pages.
		URLBuilder::setURL(NULL, array('filter_folder_id' => $filter_folder_id ) );

		$mclf->getByCompanyIdAndUserIdAndFolder( $current_user->getCompany(), $current_user->getId(), $filter_folder_id, $current_user_prefs->getItemsPerPage() );

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

        $messages = collect($messages)->sortByDesc('created_date')->values()->all();
		$viewData['messages'] = $messages;
		$viewData['require_ack'] = $require_ack;
		$viewData['show_ack_column'] = $show_ack_column;

		$viewData['mf'] = $mf;
		$viewData['folder_options'] = $folder_options;
		$viewData['filter_folder_id'] = $filter_folder_id;
        // dd($viewData);

        return view('message/UserMessageList', $viewData);

    }


	public function new_message(){
		// Redirect::Page( URLBuilder::getURL( NULL, 'EditMessage.php', FALSE) );
        return redirect()->to(URLBuilder::getURL( null , '/user/messages/edit', false));
	}



    public function delete(Request $request, $ids)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

		if (empty($ids)) {
			return response()->json(['error' => 'No message selected.'], 400);
		}

        $filter_folder_id = $request->query('filter_folder_id', 10);
		$mcf = new MessageFactory();

		if ( $permission->Check('message','delete') OR $permission->Check('message','delete_own') ) {
			$mcf->StartTransaction();

			Debug::text('Filter Folder ID: '. $filter_folder_id, __FILE__, __LINE__, __METHOD__,9);

            if ( $filter_folder_id == 10 ) { //Inbox

                $mrlf = new MessageRecipientListFactory();
				$inbox_message = $mrlf->getByCompanyIdAndUserIdAndMessageSenderId( $current_company->getId(), $current_user->getId(), $ids );

                foreach ($inbox_message->rs as $m_obj) {
					$inbox_message->data = (array)$m_obj;

                    $inbox_message->setDeleted(true); // Set deleted flag to true

                    if ($inbox_message->isValid()) {
                        $res = $inbox_message->Save();

                        if($res){
                            return response()->json(['success' => 'Message deleted successfully.']);
                        }else{
                            return response()->json(['error' => 'Message deleted failed.']);
                        }
                    }
				}
			} else { //Sent
				$mslf = new MessageSenderListFactory();

				$sent_message = $mslf->getByCompanyIdAndUserIdAndId( $current_company->getId(), $current_user->getId(), $ids );

				foreach ($sent_message->rs as $m_obj) {
					$sent_message->data = (array)$m_obj;

                    $sent_message->setDeleted(true); // Set deleted flag to true

                    if ($sent_message->isValid()) {
                        $res = $sent_message->Save();

                        if($res){
                            return response()->json(['success' => 'Message deleted successfully.']);
                        }else{
                            return response()->json(['error' => 'Message deleted failed.']);
                        }
                    }
				}
			}
			//$mcf->FailTransaction();
			$mcf->CommitTransaction();

		}

		return response()->json(['success' => 'Operation completed successfully.']);
	}


}

?>
