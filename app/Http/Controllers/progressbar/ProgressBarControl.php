<?php

namespace App\Http\Controllers\progressbar;
use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class ProgressBarControl extends Controller
{
	protected $permission;
    protected $company;
    protected $userPrefs;
    protected $currentUser;
    protected $profiler;

	public $progress = 0;

    public function __construct() {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');
		//require_once('HTML/Progress.php');

		//Don't stop execution if user hits their stop button on their browser!
		ignore_user_abort(TRUE);

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->profiler = View::shared('profiler');

    }

	public function index(){
		$permission = $this->permission;

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'next_page',
				'pay_period_ids',
				'filter_user_id',
				'pay_stub_ids',
				'data',
			)
		));

		$action = strtolower($action);
		switch ($action) {
			case 'recalculate_company':
				Debug::Text('ProgressBarControl: Recalculating Company TimeSheet!', __FILE__, __LINE__, __METHOD__,10);

				if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit') OR $permission->Check('punch','edit_own') ) ) {

				$permission->Redirect( FALSE ); //Redirect
				}

				$comment = _('Recalculating Company TimeSheet...');

				break;
			case 'recalculate_employee':
				Debug::Text('ProgressBarControl: Recalculating Employee / Company TimeSheet!', __FILE__, __LINE__, __METHOD__,10);

				if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit') OR $permission->Check('punch','edit_own') ) ) {

				$permission->Redirect( FALSE ); //Redirect
				}

				$comment = _('Recalculating Employee TimeSheet...');

				break;
			case 'generate_paystubs':
				Debug::Text('Generate PayStubs!', __FILE__, __LINE__, __METHOD__,10);

				if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

				$permission->Redirect( FALSE ); //Redirect
				}

				$comment = _('Generating Pay Stubs...');

				break;
			case 'recalculate_paystub_ytd':
				Debug::Text('Re-Calculating PayStub YTD values!', __FILE__, __LINE__, __METHOD__,10);

				if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

				$permission->Redirect( FALSE ); //Redirect
				}

				$comment = _('Recalculating Pay Stub Year To Date (YTD) amounts...');

				break;
			case 'recalculate_accrual_policy':
				Debug::Text('Recalculate Accrual Policy!', __FILE__, __LINE__, __METHOD__,10);

				if ( !$permission->Check('accrual_policy','enabled')
				OR !( $permission->Check('accrual_policy','edit')
				OR $permission->Check('accrual_policy','edit_own')
				OR $permission->Check('accrual_policy','edit_child')
				) ) {
				$permission->Redirect( FALSE ); //Redirect
				}

				$comment = _('Recalculating Accrual Policy...');

				break;
			case 'add_mass_punch':
				Debug::Text('Add Mass Punch!', __FILE__, __LINE__, __METHOD__,10);

				if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit')
				OR $permission->Check('punch','edit_own')
				OR $permission->Check('punch','edit_child')
				) ) {
				$permission->Redirect( FALSE ); //Redirect
				}

				$comment = _('Adding Punches...');

				break;
			case 'add_mass_schedule':
				Debug::Text('Add Mass Schedule!', __FILE__, __LINE__, __METHOD__,10);

				if ( !$permission->Check('schedule','enabled')
				OR !( $permission->Check('schedule','edit')
				OR $permission->Check('schedule','edit_own')
				OR $permission->Check('schedule','edit_child')
				) ) {
				$permission->Redirect( FALSE ); //Redirect
				}

				$comment = _('Adding Schedule Shifts...');

				break;
			case 'add_mass_schedule_npvc':

				Debug::Text('Add Mass Schedule!', __FILE__, __LINE__, __METHOD__,10);

				if ( !$permission->Check('schedule','enabled')
				OR !( $permission->Check('schedule','edit')
				OR $permission->Check('schedule','edit_own')
				OR $permission->Check('schedule','edit_child')
				) ) {
				$permission->Redirect( FALSE ); //Redirect
				}

				$comment = _('Adding Schedule Shifts...');

				break;
			default:
				$comment = _('Test Progress Bar...');
				break;
		}

		/*
		This suffers from URLs that are too long, especially when coming from Mass Punch/Schedule.
		Offer a method to store the data in the user_generic_data table, and retreive it on the ProgressBar.php page, bypassing the URL completely.
		*/

		$url = URLBuilder::getURL( array('action' => $action, 'pay_period_ids' => $pay_period_ids, 'filter_user_id' => $filter_user_id, 'pay_stub_ids' => $pay_stub_ids, 'data' => $data, 'next_page' => urlencode($next_page) ), '/progress_bar');


		$viewData['comment'] = $comment;
		$viewData['url'] = $url;

		return view('progress_bar/ProgressBarControl', $viewData);


	}

}

?>
