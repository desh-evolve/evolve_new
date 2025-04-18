<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Misc;
use App\Models\Core\PermissionControlListFactory;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserPreferenceFactory;
use App\Models\Users\UserPreferenceListFactory;
use App\Models\Users\UserDefaultListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserPreference extends Controller
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

        // Permission check
        // if ( !$permission->Check('user_preference','enabled')
        //         OR !( $permission->Check('user_preference','edit') OR $permission->Check('user_preference','edit_child') OR $permission->Check('user_preference','edit_own') ) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

    }

    public function index($user_id = null)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        $viewData['title'] = 'Employee Preferences';

        $hlf = new HierarchyListFactory();
        // $permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId( $current_company->getId(), $current_user->getId() );
        // // Include current user in list.
        // if ( $permission->Check('user_preference','edit_own') ) {
        //     $permission_children_ids[] = $current_user->getId();
        // }

        // Default user selection
        if ( !isset($user_id) OR (isset($user_id) AND $user_id == '' ) ) {
			$user_id = $current_user->getId();
		}

        $ulf = new UserListFactory();
        $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();
		}

        // Fetch User Preferences
        $uplf = new UserPreferenceListFactory();
		$uplf->getByUserIDAndCompanyID( $user_id, $current_company->getId() );

        $pref_data = [];

        // Check if user object exists and preferences are returned
        if ( isset($user_obj) AND is_object( $user_obj) ) {

            // Check for permissions
            // $is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() );
            // $is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

            // if ( $permission->Check('user_preference','edit')
            //         OR ( $permission->Check('user_preference','edit_own') AND $is_owner === TRUE )
            //         OR ( $permission->Check('user_preference','edit_child') AND $is_child === TRUE ) ) {

                // If user preferences are found
                if ($uplf->getRecordCount() > 0) {
                    foreach ($uplf->rs as $user_preference) {
                        $uplf->data = (array)$user_preference;
                        $user_preference = $uplf;

                            $pref_data = array(
                                'id' => $user_preference->getId(),
                                'user_id' => $user_preference->getUser(),
                                'user_full_name' => $user_obj->getFullName(),
                                'language' =>  $user_preference->getLanguage(),
                                'date_format' => $user_preference->getDateFormat(),
                                'other_date_format'=> $user_preference->getDateFormat(),
                                'time_format' => $user_preference->getTimeFormat(),
                                'time_zone' => $user_preference->getTimeZone(),
                                'time_unit_format' => $user_preference->getTimeUnitFormat(),
                                'timesheet_view' => $user_preference->getTimeSheetView(),
                                'start_week_day' => $user_preference->getStartWeekDay(),
                                'items_per_page' => $user_preference->getItemsPerPage(),
                                'enable_email_notification_exception' => $user_preference->getEnableEmailNotificationException(),
                                'enable_email_notification_message' => $user_preference->getEnableEmailNotificationMessage(),
                                'enable_email_notification_home' => $user_preference->getEnableEmailNotificationHome(),
                                'created_date' => $user_preference->getCreatedDate(),
                                'created_by' => $user_preference->getCreatedBy(),
                                'updated_date' => $user_preference->getUpdatedDate(),
                                'updated_by' => $user_preference->getUpdatedBy(),
                                'deleted_date' => $user_preference->getDeletedDate(),
                                'deleted_by' => $user_preference->getDeletedBy()
                            );
                    }

                } else {
                    // Use default values if no preference exists
                    $udlf = new UserDefaultListFactory();
                    $udlf->getByCompanyId( $current_company->getId() );

                    if ( $udlf->getRecordCount() > 0 ) {
                        Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
                        $udf_obj = $udlf->getCurrent();

                        $pref_data = array(
                                        'user_id' => $user_obj->getId(),
                                        'user_full_name' => $user_obj->getFullName(),
                                        'language' =>  $udf_obj->getLanguage(),
                                        'date_format' => $udf_obj->getDateFormat(),
                                        'other_date_format' => $udf_obj->getDateFormat(),
                                        'time_format' => $udf_obj->getTimeFormat(),
                                        'time_zone' => $udf_obj->getTimeZone(),
                                        'time_unit_format' => $udf_obj->getTimeUnitFormat(),
                                        'start_week_day' => $udf_obj->getStartWeekDay(),
                                        'items_per_page' => $udf_obj->getItemsPerPage(),
                                        'enable_email_notification_exception' => $udf_obj->getEnableEmailNotificationException(),
                                        'enable_email_notification_message' => $udf_obj->getEnableEmailNotificationMessage(),
                                        'enable_email_notification_home' => $udf_obj->getEnableEmailNotificationHome(),
                                    );
                    } else {
                        $pref_data = array(
                                        'user_id' => $user_obj->getId(),
                                        'user_full_name' => $user_obj->getFullName(),
                                        'language' =>  'en',
                                        'time_unit_format' => 20, //Hours
                                        'items_per_page' => 25,
                                        'enable_email_notification_exception' => TRUE,
                                        'enable_email_notification_message' => TRUE,
                                        'enable_email_notification_home' => FALSE,
                                    );
                    }
                }
            // }
        }

        // Select box options
        $upf = new UserPreferenceFactory();
        $pref_data['language_options'] = ['en' => 'English']; // Hardcoded
		// $pref_data['language_options'] = TTi18n::getLanguageArray();
		$pref_data['date_format_options'] = $upf->getOptions('date_format');
		$pref_data['other_date_format_options'] = $upf->getOptions('other_date_format');

		$pref_data['time_format_options'] = $upf->getOptions('time_format');
		$pref_data['time_unit_format_options'] = $upf->getOptions('time_unit_format');
		$pref_data['timesheet_view_options'] = $upf->getOptions('timesheet_view');
		$pref_data['start_week_day_options'] = $upf->getOptions('start_week_day');

		$timezone_options = Misc::prependArray( array(-1 => '---'), $upf->getOptions('time_zone') );
		$pref_data['time_zone_options'] = $timezone_options;

        $viewData['pref_data'] = $pref_data;
        $viewData['upf'] = $upf;

        // dd($timezone_options);
        // dd($viewData);

        return view('users.editUserPreference', $viewData);
    }


    public function save(Request $request)
    {
        $upf = new UserPreferenceFactory();
        $current_user = $this->currentUser;
        $pref_data = $request->all();
        // dd($request->all());

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		if ( $pref_data['id'] != '' ) {
			$upf->setId( $pref_data['id'] );
		}

		if ( isset($pref_data['user_id']) AND $pref_data['user_id'] != '' ) {
			$upf->setUser( $pref_data['user_id'] );
		} else {
			$upf->setUser( $current_user->getId() );
		}

		$upf->setLanguage( $pref_data['language'] );
		if ( $pref_data['language'] == 'en' ) {
			$upf->setDateFormat( $pref_data['date_format'] );
		} else {
			$upf->setDateFormat( $pref_data['other_date_format'] );
		}

		$upf->setTimeFormat( $pref_data['time_format']);
		$upf->setTimeUnitFormat( $pref_data['time_unit_format'] );
		$upf->setTimeZone( $pref_data['time_zone'] );
		//$upf->setTimeSheetView( $pref_data['timesheet_view'] );
		$upf->setStartWeekDay( $pref_data['start_week_day'] );
		$upf->setItemsPerPage( $pref_data['items_per_page'] );

		if ( isset($pref_data['enable_email_notification_exception']) ) {
			$upf->setEnableEmailNotificationException( TRUE );
		} else {
			$upf->setEnableEmailNotificationException( FALSE );
		}

		if ( isset($pref_data['enable_email_notification_message']) ) {
			$upf->setEnableEmailNotificationMessage( TRUE );
		} else {
			$upf->setEnableEmailNotificationMessage( FALSE );
		}

		if ( isset($pref_data['enable_email_notification_home']) ) {
			$upf->setEnableEmailNotificationHome( TRUE );
		} else {
			$upf->setEnableEmailNotificationHome( FALSE );
		}

		if ( $upf->isValid() ) {
			$upf->Save( FALSE );

			if ( $current_user->getId() == $upf->getUser() ) {
				TTi18n::setLocaleCookie( $pref_data['language'].'_'.$current_user->getCountry() );
			}

            return redirect()->to(URLBuilder::getURL(array('user_id' => $pref_data['user_id'], 'data_saved' => 1), '/user/preference'))->with('success', 'User Preference saved successfully.');
        }

        // If validation fails, return back with errors
        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();

    }


}

