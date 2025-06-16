<?php

namespace App\Http\Controllers\schedule;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Policy\SchedulePolicyListFactory;
use App\Models\Schedule\RecurringScheduleTemplateControlFactory;
use App\Models\Schedule\RecurringScheduleTemplateControlListFactory;
use App\Models\Schedule\RecurringScheduleTemplateFactory;
use App\Models\Schedule\RecurringScheduleTemplateListFactory;
use Illuminate\Support\Facades\View;

class EditRecurringScheduleTemplate extends Controller
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
		$permission = $this->permission;
		$current_user = $this->currentUser;
		$current_company = $this->currentCompany;
		$current_user_prefs = $this->userPrefs;
				
		if ( !$permission->Check('recurring_schedule_template','enabled')
				OR !( $permission->Check('recurring_schedule_template','edit') OR $permission->Check('recurring_schedule_template','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Edit Recurring Schedule Template';

		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
												array	(
														'action',
														'id',
														'ids',
														'data',
														'week_rows'
														) ) );

		if ( isset($week_rows)) {
			foreach( $week_rows as $week_row_id => $week_row ) {
				Debug::Text('Start Time: '. $week_row['start_time'] , __FILE__, __LINE__, __METHOD__,10);

				if ( isset($week_row['start_time']) AND $week_row['start_time'] != '') {
					$week_rows[$week_row_id]['start_time'] = TTDate::strtotime($week_row['start_time']);
				}
				if ( isset($week_row['end_time']) AND $week_row['end_time'] != '' ) {
					$week_rows[$week_row_id]['end_time'] = TTDate::strtotime($week_row['end_time']);
				}
			}
		}

		$rstcf = new RecurringScheduleTemplateControlFactory(); 
		$rstf = new RecurringScheduleTemplateFactory();

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
			case 'submit':
				Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
				//Debug::setVerbosity(11);
				$redirect=0;

				$rstcf->StartTransaction();

				$rstcf->setId( $data['id'] );
				$rstcf->setCompany( $current_company->getId() );
				$rstcf->setName( $data['name'] );
				$rstcf->setDescription( $data['description'] );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();

					Debug::Text('aRecurring Schedule Template Control ID: '. $rstc_id , __FILE__, __LINE__, __METHOD__,10);

					if ( $rstc_id === TRUE ) {
						$rstc_id = $data['id'];
					}

					Debug::Text('bRecurring Schedule Template Control ID: '. $rstc_id , __FILE__, __LINE__, __METHOD__,10);

					//Save each weeks data.
					if ( count($week_rows) > 0 ) {
						foreach( $week_rows as $week_row_id => $week_row ) {
							Debug::Text('Row ID: '. $week_row_id .' Week: '. $week_row['week'] , __FILE__, __LINE__, __METHOD__,10);

							if ( $week_row['week'] != '' AND $week_row['week'] > 0 ) {
								if ( $week_row_id > 0 ) {
									$rstf->setID( $week_row_id );
								}
								$rstf->setRecurringScheduleTemplateControl( $rstc_id );
								$rstf->setWeek( $week_row['week'] );

								if ( isset($week_row['sun']) ) {
									$rstf->setSun( TRUE );
								} else {
									$rstf->setSun( FALSE );
								}

								if ( isset($week_row['mon']) ) {
									$rstf->setMon( TRUE );
								} else {
									$rstf->setMon( FALSE );
								}

								if ( isset($week_row['tue']) ) {
									$rstf->setTue( TRUE );
								} else {
									$rstf->setTue( FALSE );
								}

								if ( isset($week_row['wed']) ) {
									$rstf->setWed( TRUE );
								} else {
									$rstf->setWed( FALSE );
								}

								if ( isset($week_row['thu']) ) {
									$rstf->setThu( TRUE );
								} else {
									$rstf->setThu( FALSE );
								}

								if ( isset($week_row['fri']) ) {
									$rstf->setFri( TRUE );
								} else {
									$rstf->setFri( FALSE );
								}

								if ( isset($week_row['sat']) ) {
									$rstf->setSat( TRUE );
								} else {
									$rstf->setSat( FALSE );
								}

								if ( isset($week_row['sun']) ) {
									$rstf->setSun( TRUE );
								} else {
									$rstf->setSun( FALSE );
								}

								$rstf->setStartTime( $week_row['start_time'] );
								$rstf->setEndTime( $week_row['end_time'] );

								$rstf->setSchedulePolicyID( $week_row['schedule_policy_id'] );
								$rstf->setBranch( $week_row['branch_id'] );
								$rstf->setDepartment( $week_row['department_id'] );

								if ( isset($week_row['job_id']) ) {
									$rstf->setJob( $week_row['job_id'] );
								}

								if ( isset($week_row['job_item_id']) ) {
									$rstf->setJobItem( $week_row['job_item_id'] );
								}

								if ( $rstf->isValid() ) {
									Debug::Text('Saving Week Row ID: '. $week_row_id, __FILE__, __LINE__, __METHOD__,10);
									$rstf->Save();
								} else {
									$redirect++;
								}
							} else {
								//Delete week
								if ( $week_row_id > 0 ) {
									$rstf->setID( $week_row_id );
									$rstf->setDeleted(TRUE);
									$rstf->Save();
								} else {
									unset($week_row[$week_row_id]);
								}
							}
						}
					}

					if ( $redirect == 0 ) {
						$rstcf->CommitTransaction();
						//$rstcf->FailTransaction();

						Redirect::Page( URLBuilder::getURL( NULL, '/schedule/recurring_schedule_template_control_list') );

						break;

					}
				}
				$rstcf->FailTransaction();
			case 'delete':
				if ( count($ids) > 0) {
					foreach ($ids as $rst_id) {
						if ( $rst_id > 0 ) {
							Debug::Text('Deleting Week Row ID: '. $rst_id, __FILE__, __LINE__, __METHOD__,10);

							$rstlf = new RecurringScheduleTemplateListFactory(); 
							$rstlf->getById( $rst_id );

							if ( $rstlf->getRecordCount() == 1 ) {
								foreach($rstlf->rs as $rst_obj ) {
									$rstlf->data = (array)$rst_obj;
									$rst_obj = $rstlf;

									$rst_obj->setDeleted( TRUE );
									if ( $rst_obj->isValid() ) {
										$rst_obj->Save();
									}
								}
							}
						}
						unset($week_rows[$rst_id]);

					}
					unset($rst_id);
				}

				//Redirect::Page( URLBuilder::getURL( array('id' => $data['id']), 'EditRecurringScheduleTemplate.php') );

				//break;
			default:
				if ( isset($id) ) {

					$rstlf = new RecurringScheduleTemplateListFactory();
					$rstclf = new RecurringScheduleTemplateControlListFactory();
					$rstclf->getByIdAndCompanyId( $id, $current_company->getID() );

					foreach ($rstclf as $rstc_obj) {
						$rstclf->data = (array)$rstc_obj;
						$rstc_obj = $rstclf;
						//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

						$data = array(
											'id' => $rstc_obj->getId(),
											'name' => $rstc_obj->getName(),
											'description' => $rstc_obj->getDescription(),
											'created_date' => $rstc_obj->getCreatedDate(),
											'created_by' => $rstc_obj->getCreatedBy(),
											'updated_date' => $rstc_obj->getUpdatedDate(),
											'updated_by' => $rstc_obj->getUpdatedBy(),
											'deleted_date' => $rstc_obj->getDeletedDate(),
											'deleted_by' => $rstc_obj->getDeletedBy()
										);

						//Get week data
						$rstlf->getByRecurringScheduleTemplateControlId( $rstc_obj->getId() );
						if ( $rstlf->getRecordCount() > 0 ) {
							foreach( $rstlf as $rst_obj) {
								$rstlf->data = (array)$rst_obj;
								$rst_obj = $rstlf;
								$week_rows[$rst_obj->getId()] = array(
													'id' => $rst_obj->getId(),
													'week' => $rst_obj->getWeek(),
													'sun' => $rst_obj->getSun(),
													'mon' => $rst_obj->getMon(),
													'tue' => $rst_obj->getTue(),
													'wed' => $rst_obj->getWed(),
													'thu' => $rst_obj->getThu(),
													'fri' => $rst_obj->getFri(),
													'sat' => $rst_obj->getSat(),
													'start_time' => $rst_obj->getStartTime(),
													'end_time' => $rst_obj->getEndTime(),
													'total_time' => $rst_obj->getTotalTime(),
													'schedule_policy_id' => $rst_obj->getSchedulePolicyID(),
													'branch_id' => $rst_obj->getBranch(),
													'department_id' => $rst_obj->getDepartment(),
													'job_id' => $rst_obj->getJob(),
													'job_item_id' => $rst_obj->getJobItem()
													);
							}
						} else {
							$week_rows[-1] = array(
											'id' => -1,
											'week' => 1
											);

						}

					}
				} elseif ( $action == 'add_week' ) {
					Debug::Text('Adding Blank Week', __FILE__, __LINE__, __METHOD__,10);
					if ( !isset($week_rows) OR ( isset($week_rows) AND !is_array( $week_rows ) ) ) {
						//If they delete all weeks and try to add a new one.
						$week_rows[0] = array(
										'id' => -1,
										'week' => 0,
										'mon' => TRUE,
										'tue' => TRUE,
										'wed' => TRUE,
										'thu' => TRUE,
										'fri' => TRUE,
										'start_time' => strtotime('08:00'),
										'end_time' => strtotime('17:00'),
										'branch_id' => -1,
										'department_id' => -1,
										'schedule_policy_id' => 0,
										);

						$row_keys = array_keys($week_rows);
						sort($row_keys);

						$next_blank_id = 0;
						$lowest_id = 0;
					} else {
						$row_keys = array_keys($week_rows);
						sort($row_keys);

						Debug::Text('Lowest ID: '. $row_keys[0], __FILE__, __LINE__, __METHOD__,10);
						$lowest_id = $row_keys[0];
						if ( $lowest_id < 0 ) {
							$next_blank_id = $lowest_id-1;
						} else {
							$next_blank_id = -1;
						}
					}

					Debug::Text('Next Blank ID: '. $next_blank_id, __FILE__, __LINE__, __METHOD__,10);

					//Find next week
					$last_new_week = $week_rows[$row_keys[0]]['week'];
					$last_saved_week = $week_rows[array_pop($row_keys)]['week'];
					Debug::Text('Last New Week: '. $last_new_week .' Last Saved Week: '. $last_saved_week, __FILE__, __LINE__, __METHOD__,10);
					if ( $last_new_week > $last_saved_week) {
						$last_week = $last_new_week;
					} else {
						$last_week = $last_saved_week;
					}
					Debug::Text('Last Week: '. $last_week, __FILE__, __LINE__, __METHOD__,10);

					$next_total_time = 0;
					if ( count($week_rows) > 0 ) {
						foreach( $week_rows as $week_row_id => $week_row ) {
							if ( $week_row['week'] != '' AND $week_row['week'] > 0 ) {
								Debug::Text('Row ID: '. $week_row_id .' Week: '. $week_row['week'] .' Schedule Policy ID: '. $week_row['schedule_policy_id'], __FILE__, __LINE__, __METHOD__,10);

								$rstf = new RecurringScheduleTemplateFactory();
								$rstf->setStartTime( $week_row['start_time'] );
								$rstf->setEndTime( $week_row['end_time'] );

								$rstf->setSchedulePolicyID( $week_row['schedule_policy_id'] );
								$rstf->preSave();
								$week_rows[$week_row_id]['total_time'] = $rstf->getTotalTime();
								if ( $week_row_id == $lowest_id ) {
									$next_total_time = $week_rows[$week_row_id]['total_time'];
								}
							}

						}
					}

					$week_rows[$next_blank_id] = array(
									'id' => $next_blank_id,
									'week' => $last_week+1,
									'start_time' => $week_rows[$lowest_id]['start_time'],
									'end_time' => $week_rows[$lowest_id]['end_time'],
									'total_time' => $next_total_time,
									'schedule_policy_id' => $week_rows[$lowest_id]['schedule_policy_id'],
									'branch_id' => $week_rows[$lowest_id]['branch_id'],
									'department_id' => $week_rows[$lowest_id]['department_id'],
									'mon' => @$week_rows[$lowest_id]['mon'],
									'tue' => @$week_rows[$lowest_id]['tue'],
									'wed' => @$week_rows[$lowest_id]['wed'],
									'thu' => @$week_rows[$lowest_id]['thu'],
									'fri' => @$week_rows[$lowest_id]['fri'],
									'sat' => @$week_rows[$lowest_id]['sat'],
									'sun' => @$week_rows[$lowest_id]['sun'],
									);
				} elseif ( $action != 'submit' AND $action != 'delete' ) {
					$week_rows[-1] = array(
									'id' => -1,
									'week' => 1,
									'mon' => TRUE,
									'tue' => TRUE,
									'wed' => TRUE,
									'thu' => TRUE,
									'fri' => TRUE,
									'start_time' => strtotime('08:00'),
									'end_time' => strtotime('17:00'),
									'total_time' => (9*3600),
									'branch_id' => -1,
									'department_id' => -1,
									'schedule_policy_id' => 0,
									);
				}

				$prepend_array_option = array( 0 => '--', -1 => _('-- Default --') );

				//Select box options;
				$splf = new SchedulePolicyListFactory();
				$data['schedule_options'] = $splf->getByCompanyIdArray( $current_company->getId() );

				$blf = new BranchListFactory();
				$blf->getByCompanyId( $current_company->getId() );
				$data['branch_options'] = Misc::prependArray( $prepend_array_option,  $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
				//$data['branch_options']  = Misc::prependArray( array( -1 => '-- Default --' ), $blf->getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );

				$dlf = new DepartmentListFactory();
				$dlf->getByCompanyId( $current_company->getId() );
				$data['department_options'] = Misc::prependArray( $prepend_array_option,  $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
				//$data['department_options'] = $dlf->getByCompanyIdArray( $current_company->getId() );

				/*
				if ( $current_company->getProductEdition() == 20 ) {
					$jlf = new JobListFactory();
					$data['job_options'] = $jlf->getByCompanyIdAndStatusArray( $current_company->getId(), array(10,20,30,40), TRUE );

					$jilf = new JobItemListFactory();
					$data['job_item_options'] = $jilf->getByCompanyIdArray( $current_company->getId(), TRUE );
				}
				*/

				//var_dump($week_rows);
				$smarty->assign_by_ref('data', $data);
				$smarty->assign_by_ref('week_rows', $week_rows);

				break;
		}

		$smarty->assign_by_ref('rstcf', $rstcf);
		$smarty->assign_by_ref('rstf', $rstf);

		$smarty->display('schedule/EditRecurringScheduleTemplate.tpl');
	}
}

?>