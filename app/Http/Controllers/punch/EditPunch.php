<?php

namespace App\Http\Controllers\punch;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\Redirect;
use App\Models\Core\StationListFactory;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Punch\PunchControlFactory;
use App\Models\Punch\PunchControlListFactory;
use App\Models\Punch\PunchFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;


class EditPunch extends Controller
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
		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit')
						OR $permission->Check('punch','edit_own')
						OR $permission->Check('punch','edit_child')
						) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
		*/

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'punch_control_id',
				'user_id',
				'date_stamp',
				'status_id',
				'pc_data'
			)
		));
		
		$viewData['title'] = !empty($id) ? 'Edit Punch' : 'Add Punch';
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;
		$current_user_prefs = $this->userPrefs;

		$pcf = new PunchControlFactory(); 
		$pf = new PunchFactory();
		$ulf = new UserListFactory();


		if ( !empty($id) ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$pclf = new PunchControlListFactory();
			$pclf->getByPunchId( $id );

			foreach ($pclf->rs as $pc_obj) {
				$pclf->data = (array)$pc_obj;
				$pc_obj = $pclf;

				//Get punches
				$plf = new PunchListFactory();
				$plf->getById( $id );

				if ( $plf->getRecordCount() > 0 ) {
					$p_obj = $plf->getCurrent();
				} else {
					$punch_data = NULL;
				}

				//Get Station data.
				$station_data = FALSE;
				$slf = new StationListFactory();
				if ( $p_obj->getStation() != FALSE ) {
					$slf->getById( $p_obj->getStation() );
					if ( $slf->getRecordCount() > 0 ) {
						$s_obj = $slf->getCurrent();

						$station_data = array(
											'id' => $s_obj->getId(),
											'type_id' => $s_obj->getType(),
											'type' => Option::getByKey($s_obj->getType(), $s_obj->getOptions('type') ),
											'station_id' => $s_obj->getStation(),
											'source' => $s_obj->getSource(),
											'description' => Misc::TruncateString( $s_obj->getDescription(), 20 )
										);
					}
				}

				$pc_data = array(
									'id' => $pc_obj->getId(),
									'user_date_id' => $pc_obj->getUserDateId(),
									'user_id' => $pc_obj->getUserDateObject()->getUser(),
									'user_full_name' => $pc_obj->getUserDateObject()->getUserObject()->getFullName(),
									'pay_period_id' => $pc_obj->getUserDateObject()->getPayPeriod(),
									//This causes punches that span 24hrs to not be edited correct
									//the date being the date of the first punch, not the last if we're
									//editing the last.
									//'date_stamp' => $pc_obj->getUserDateObject()->getDateStamp(),
									'branch_id' => $pc_obj->getBranch(),
									'department_id' => $pc_obj->getDepartment(),
									'job_id' => $pc_obj->getJob(),
									'job_item_id' => $pc_obj->getJobItem(),
									'quantity' => (float)$pc_obj->getQuantity(),
									'bad_quantity' => (float)$pc_obj->getBadQuantity(),
									'note' => $pc_obj->getNote(),

									'other_id1' => $pc_obj->getOtherID1(),
									'other_id2' => $pc_obj->getOtherID2(),
									'other_id3' => $pc_obj->getOtherID3(),
									'other_id4' => $pc_obj->getOtherID4(),
									'other_id5' => $pc_obj->getOtherID5(),

									//Punch Data
									'punch_id' => $p_obj->getId(),
									'status_id' => $p_obj->getStatus(),
									'type_id' => $p_obj->getType(),
									'station_id' => $p_obj->getStation(),
									'station_data' => $station_data,
									'time_stamp' => $p_obj->getTimeStamp(),
									//Use this so the date is always insync with the time.
									'date_stamp' => $p_obj->getTimeStamp(),
									'original_time_stamp' => $p_obj->getOriginalTimeStamp(),
									'actual_time_stamp' => $p_obj->getActualTimeStamp(),
									'longitude' => $p_obj->getLongitude(),
									'latitude' => $p_obj->getLatitude(),

									'created_date' => $p_obj->getCreatedDate(),
									'created_by' => $p_obj->getCreatedBy(),
									'created_by_name' => (string)$ulf->getFullNameById( $p_obj->getCreatedBy() ),
									'updated_date' => $p_obj->getUpdatedDate(),
									'updated_by' => $p_obj->getUpdatedBy(),
									'updated_by_name' => (string)$ulf->getFullNameById( $p_obj->getUpdatedBy() ),
									'deleted_date' => $p_obj->getDeletedDate(),
									'deleted_by' => $p_obj->getDeletedBy()
								);
			}
		} else {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			//UserID has to be set at minimum
			if ( !empty($punch_control_id) ) {
				Debug::Text(' Punch Control ID was passed: '. $punch_control_id, __FILE__, __LINE__, __METHOD__,10);

				//Get previous punch, and default timestamp to that.
				$plf = new PunchListFactory();
				$plf->getPreviousPunchByPunchControlID( $punch_control_id );
				if ( $plf->getRecordCount() > 0 ) {
					$prev_punch_obj = $plf->getCurrent();
					$time_stamp = $prev_punch_obj->getTimeStamp()+3600;
					$date_stamp = $prev_punch_obj->getTimeStamp(); //Match date with previous punch as well, incase a new day hasnt been triggered yet.
				} else {
					$time_stamp = TTDate::getTime();
					$date_stamp = NULL;
				}

				$pclf = new PunchControlListFactory();
				$pclf->getById( $punch_control_id );
				if ( $pclf->getRecordCount() > 0 ) {
					$pc_obj = $pclf->getCurrent();

					if ( $date_stamp == NULL ) {
						$date_stamp = $pc_obj->getUserDateObject()->getDateStamp();
					}

					$pc_data = array (
						'id' => $pc_obj->getId(),
						'user_id' => $pc_obj->getUserDateObject()->getUser(),
						'user_full_name' => $pc_obj->getUserDateObject()->getUserObject()->getFullName(),
						'date_stamp' => $date_stamp,
						'user_date_id' => $pc_obj->getUserDateObject()->getId(),
						'time_stamp' => $time_stamp,
						'branch_id' => $pc_obj->getBranch(),
						'department_id' => $pc_obj->getDepartment(),
						'job_id' => $pc_obj->getJob(),
						'job_item_id' => $pc_obj->getJobItem(),
						'quantity' => (float)$pc_obj->getQuantity(),
						'bad_quantity' => (float)$pc_obj->getBadQuantity(),
						'note' => $pc_obj->getNote(),

						'other_id1' => $pc_obj->getOtherID1(),
						'other_id2' => $pc_obj->getOtherID2(),
						'other_id3' => $pc_obj->getOtherID3(),
						'other_id4' => $pc_obj->getOtherID4(),
						'other_id5' => $pc_obj->getOtherID5(),

						'status_id' => $status_id
					);
				}
			} elseif ( !empty($user_id) ) {
				Debug::Text(' User ID was passed: '. $user_id .' Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__,10);
				
				//Don't guess too much. If they click a day to add a punch. Make sure that punch is on that day.
				if ( isset($date_stamp) AND !empty($date_stamp) ) {
					$time_stamp = $date_stamp + (3600*12); //Noon
				} else {
					$time_stamp = TTDate::getBeginDayEpoch( TTDate::getTime() ) + (3600*12); //Noon
				}

				$ulf = new UserListFactory();
				$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
				if ( $ulf->getRecordCount() > 0 ) {
					$user_obj = $ulf->getCurrent();

					$pc_data = array (
						'user_id' => $user_obj->getId(),
						'user_full_name' => $user_obj->getFullName(),
						'date_stamp' => $date_stamp,
						'time_stamp' => $time_stamp,
						'status_id' => $status_id,
						'branch_id' => $user_obj->getDefaultBranch(),
						'department_id' => $user_obj->getDefaultDepartment(),
						'quantity' => 0,
						'bad_quantity' => 0
					);
				}

				unset($time_stamp, $plf);
			}
		}

		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		//Select box options;
		$pc_data['status_options'] = $pf->getOptions('status');
		$pc_data['type_options'] = $pf->getOptions('type');
		$pc_data['branch_options'] = $branch_options;
		$pc_data['department_options'] = $department_options;

		//Get other field names
		$oflf = new OtherFieldListFactory();
		$pc_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getId(), 15 );

		$viewData['pc_data'] = $pc_data;
		$viewData['pcf'] = $pcf;
		$viewData['pf'] = $pf;
		$viewData['current_user_prefs'] = $current_user_prefs;
		//dd($viewData);
		return view('punch/EditPunch', $viewData);
	}

	public function delete($punch_id){
		//Debug::setVerbosity(11);
		if (empty($punch_id)) {
            return response()->json(['error' => 'No Punch Selected.'], 400);
        }

		$plf = new PunchListFactory();
		$plf->getById( $punch_id );
		if ( $plf->getRecordCount() > 0 ) {
			foreach($plf->rs as $p_obj) {
				$plf->data = (array)$p_obj;
				$p_obj = $plf;

				$p_obj->setUser( $p_obj->getPunchControlObject()->getUserDateObject()->getUser() );
				$p_obj->setDeleted(TRUE);

				//These aren't doing anything because they aren't acting on the PunchControl object?
				$p_obj->setEnableCalcTotalTime( TRUE );
				$p_obj->setEnableCalcSystemTotalTime( TRUE );
				$p_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
				$p_obj->setEnableCalcUserDateTotal( TRUE );
				$p_obj->setEnableCalcException( TRUE );
				$res = $p_obj->Save();
				if($res){
					//return redirect(URLBuilder::getURL( array('refresh' => TRUE ), 'close_window'));
					return response()->json(['success' => 'Punch Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Punch Deleted Failed.']);
				}
			}
		}

		

	}

	public function submit(Request $request){

		$pcf = new PunchControlFactory(); 
		$pf = new PunchFactory();

		$pc_data = $request->pc_data;

		$punch_full_time_stamp = NULL;
		if ( isset($pc_data) ) {
			if ( !empty($pc_data['date_stamp']) AND !empty($pc_data['time_stamp']) ) {
				$punch_full_time_stamp = TTDate::parseDateTime($pc_data['date_stamp'].' '.$pc_data['time_stamp']);
				$pc_data['punch_full_time_stamp'] = $punch_full_time_stamp;
				$pc_data['time_stamp'] = $punch_full_time_stamp;
			} else {
				$pc_data['punch_full_time_stamp'] = NULL;
			}

			if ( !empty($pc_data['date_stamp']) ) {
				$pc_data['date_stamp'] = TTDate::parseDateTime($pc_data['date_stamp']);
			}
		}

		//dd($pc_data); //check here

		$fail_transaction=FALSE;

		$pf->StartTransaction();

		//Limit it to 31 days, just in case someone makes an error entering the dates or something.
		if ( $pc_data['repeat'] > 31 ) {
			$pc_data['repeat'] = 31;
		}
		Debug::Text('Repeating Punch For: '. $pc_data['repeat'] .' Days', __FILE__, __LINE__, __METHOD__,10);

		for( $i=0; $i <= (int)$pc_data['repeat']; $i++ ) {
			$pf = new PunchFactory();

			Debug::Text('Punch Repeat: '. $i, __FILE__, __LINE__, __METHOD__,10);
			if ( $i == 0 ) {
				$time_stamp = $punch_full_time_stamp;
			} else {
				$time_stamp = $punch_full_time_stamp + (86400 * $i);
			}

			Debug::Text('Punch Full Time Stamp: '. date('r', $time_stamp) .'('.$time_stamp.')', __FILE__, __LINE__, __METHOD__,10);

			//Set User before setTimeStamp so rounding can be done properly.
			$pf->setUser( $pc_data['user_id'] );

			if ( $i == 0 ) {
				$pf->setId( $pc_data['punch_id'] );
			}
			if ( isset($data['transfer']) ) {
				$pf->setTransfer( TRUE );
			}

			$pf->setType( $pc_data['type_id'] );
			$pf->setStatus( $pc_data['status_id'] );
			if ( isset($pc_data['disable_rounding']) ) {
				$enable_rounding = FALSE;
			} else {
				$enable_rounding = TRUE;
			}

			
			$pf->setTimeStamp( $time_stamp, $enable_rounding );

			if ( $i == 0 AND isset( $pc_data['id'] ) AND $pc_data['id']  != '' ) {
				Debug::Text('Using existing Punch Control ID: '. $pc_data['id'], __FILE__, __LINE__, __METHOD__,10);
				$pf->setPunchControlID( $pc_data['id'] );
			} else {
				Debug::Text('Finding Punch Control ID: '. $pc_data['id'], __FILE__, __LINE__, __METHOD__,10);
				$pf->setPunchControlID( $pf->findPunchControlID() );
			}

			if ( $pf->isNew() ) {
				$pf->setActualTimeStamp( $time_stamp );
				$pf->setOriginalTimeStamp( $pf->getTimeStamp() );
			}
			if ( $pf->isValid() == TRUE ) {
				
				if ( $pf->Save( FALSE )) {

					$pcf = new PunchControlFactory();
					$pcf->setPunchObject( $pf );
					$pcf->setId( $pf->getPunchControlID() );

					if ( $i == 0 AND $pc_data['user_date_id'] != '' ) {
						//This is important when editing a punch, without it there can be issues calculating exceptions
						//because if a specific punch was modified that caused the day to change, smartReCalculate
						//may only be able to recalculate a single day, instead of both.
						$pcf->setUserDateID( $pc_data['user_date_id'] );
					}

					if ( isset($pc_data['branch_id']) ) {
						$pcf->setBranch( $pc_data['branch_id'] );
					}
					if ( isset($pc_data['department_id']) ) {
						$pcf->setDepartment( $pc_data['department_id'] );
					}
					if ( isset($pc_data['job_id']) ) {
						$pcf->setJob( $pc_data['job_id'] ?? 0 );
					}
					if ( isset($pc_data['job_item_id']) ) {
						$pcf->setJobItem( $pc_data['job_item_id'] ?? 0  );
					}
					if ( isset($pc_data['quantity']) ) {
						$pcf->setQuantity( $pc_data['quantity'] ?? 0  );
					}
					if ( isset($pc_data['bad_quantity']) ) {
						$pcf->setBadQuantity( $pc_data['bad_quantity'] ?? 0  );
					}
					if ( isset($pc_data['note']) ) {
						$pcf->setNote( $pc_data['note'] );
					}

					if ( isset($pc_data['other_id1']) ) {
						$pcf->setOtherID1( $pc_data['other_id1'] );
					}
					if ( isset($pc_data['other_id2']) ) {
						$pcf->setOtherID2( $pc_data['other_id2'] );
					}
					if ( isset($pc_data['other_id3']) ) {
						$pcf->setOtherID3( $pc_data['other_id3'] );
					}
					if ( isset($pc_data['other_id4']) ) {
						$pcf->setOtherID4( $pc_data['other_id4'] );
					}
					if ( isset($pc_data['other_id5']) ) {
						$pcf->setOtherID5( $pc_data['other_id5'] );
					}

					$pcf->setEnableStrictJobValidation( TRUE ); 
					$pcf->setEnableCalcUserDateID( TRUE );
					$pcf->setEnableCalcTotalTime( TRUE );
					$pcf->setEnableCalcSystemTotalTime( TRUE );
					$pcf->setEnableCalcWeeklySystemTotalTime( TRUE );
					$pcf->setEnableCalcUserDateTotal( TRUE );
					$pcf->setEnableCalcException( TRUE );
					
					if ( $pcf->isValid() == TRUE ) {
						Debug::Text(' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__,10);

						if ( $pcf->Save( TRUE, TRUE ) != TRUE ) { //Force isNew() lookup.
							Debug::Text(' aFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
							$fail_transaction = TRUE;
							break;
						}
					} else {
						Debug::Text(' bFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
						$fail_transaction = TRUE;
						break;
					}
				} else {
					Debug::Text(' cFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
					$fail_transaction = TRUE;
					break;
				}
			} else {
				Debug::Text(' dFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
				$fail_transaction = TRUE;
				break;
			}
		}

		if ( $fail_transaction == FALSE ) {
			//$pf->FailTransaction();
			$pf->CommitTransaction();

			return redirect(URLBuilder::getURL( array('refresh' => TRUE ), 'close_window'));
		} else {
			$pf->FailTransaction();
		}
	}

}


?>