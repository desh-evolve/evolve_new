<?php

namespace App\Http\Controllers\users;
use App\Http\Controllers\Controller;

use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserGenericStatusListFactory;
use Illuminate\Support\Facades\View;

class UserGenericStatusList extends Controller
{
	protected $permission;
    protected $company;
    protected $userPrefs;
    protected $currentUser;
    protected $profiler;

	public function __construct() {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');
    
        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->profiler = View::shared('profiler');

		/* //check here
		if ( !$permission->Check('user','enabled')
				OR !( $permission->Check('user','view') OR $permission->Check('user','view_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}
		*/
	}

	public function index(){

		$current_user = $this->currentUser;
		$current_user_prefs = $this->userPrefs;
		$viewData = [];
		$rows = [];

		$viewData['title'] = 'Status Report';

		// Get FORM variables
		extract	(FormVariables::GetVariables(
			array	(
				'batch_id',
				'batch_title',
				'batch_next_page',
				'action',
				'page',
				'sort_column',
				'sort_order',
			) 
		) );

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array(
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page,
				'batch_id' => $batch_id,
				'batch_title' => $batch_title,
				'batch_next_page' => $batch_next_page
			) 
		);

		$sort_array = NULL;

		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		Debug::Text('Next Page: '. urldecode( $batch_next_page ) , __FILE__, __LINE__, __METHOD__,10);
		
		if ( $batch_id != '' ) {
			$ugslf = new UserGenericStatusListFactory();
			$ugslf->getByUserIdAndBatchId( $current_user->getId(), $batch_id,  $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

			Debug::Text('Record Count: '. $ugslf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

			//$pager = new Pager($ugslf);

			if ( $ugslf->getRecordCount() > 0 ) {
				$status_count_arr = $ugslf->getStatusCountArrayByUserIdAndBatchId( $current_user->getId(), $batch_id );

				foreach ($ugslf->rs as $ugs_obj) {
					$ugslf->data = (array)$ugs_obj;
					$rows[] = array(
						'id' => $ugslf->getId(),
						'user_id' => $ugslf->getUser(),
						'batch_id' => $ugslf->getBatchId(),
						'status_id' => $ugslf->getStatus(),
						'status' => Option::getByKey( $ugslf->getStatus(), $ugslf->getOptions('status') ),
						'label' => $ugslf->getLabel(),
						'description' => $ugslf->getDescription(),
						'link' => $ugslf->getLink(),
						'deleted' => $ugslf->getDeleted()
					);
				}

				//var_dump($rows);
				//var_dump($status_count_arr);
			}
		}

		print_r($rows);exit;

		$viewData['rows'] = $rows;	
		$viewData['status_count'] = $status_count_arr;	
		$viewData['batch_title'] = $batch_title;
		$viewData['batch_next_page'] = $batch_next_page;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		
		return view('users.UserGenericStatusList', $viewData);
	}

}


?>