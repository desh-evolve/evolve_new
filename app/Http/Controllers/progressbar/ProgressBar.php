<?php

namespace App\Http\Controllers\progressbar;
use App\Http\Controllers\Controller;
use App\Models\Core\CalculatePayStub;
use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateListFactory;
use App\Models\PayPeriod\PayPeriodFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleUserListFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\Users\UserGenericStatusFactory;
use Illuminate\Support\Facades\View;

class ProgressBar extends Controller
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

		
		// Get FORM variables
		extract	(FormVariables::GetVariables(
			array(
				'action',
				'december_bonus_id',
				'next_page',
				'pay_period_ids',
				'filter_user_id',
				'pay_stub_ids',
				'data',
			)
		) );

		$dec_bo_id =$filter_user_id;
		$att_bo_id = $filter_user_id;
							
		Debug::text('Next Page: '. $next_page, __FILE__, __LINE__, __METHOD__,10);

    }

	public function InitProgressBar( $increment = 1 ) {
		if ($this->progress < 100) {
            $this->progress += $increment;
        }
	}

	//check here
	public function index(){

		$this->InitProgressBar( 10 );

		for($i=0; $i < 11; $i++) {

			if ( $i % 2 == 0 ) {
				sleep(1);
			}

		}

		return view('components.general.progress-bar', ['progress' => $this->progress]);
	}

	// function for recalculate_company & recalculate_employee
	public function recalculate_employee(){
		Debug::text('Recalculating Employee Timesheet: User ID: '. $filter_user_id .' Pay Period ID: '. $pay_period_ids, __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		//Make sure pay period is not CLOSED.
		//We can re-calc on locked though.
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getById( $pay_period_ids );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_obj = $pplf->getCurrent();

			if ( $pp_obj->getStatus() != 20 ) {
				$udlf = TTnew( 'UserDateListFactory' );
				if ( $action == 'recalculate_company' ) {
					TTLog::addEntry( $current_company->getId(), TTi18n::gettext('Notice'), TTi18n::gettext(' Recalculating Company TimeSheet'), $current_user->getId(), 'user_date_total' );
					$udlf->getByCompanyIdAndPayPeriodID( $current_company->getId(), $pay_period_ids );
				} else {
					TTLog::addEntry( $filter_user_id, TTi18n::gettext('Notice'), TTi18n::gettext(' Recalculating Employee TimeSheet'), $current_user->getId(), 'user_date_total' );
					$udlf->getByUserIdAndPayPeriodID( $filter_user_id, $pay_period_ids );
				}

				if ( $udlf->getRecordCount() > 0 ) {
					InitProgressBar();
					$progress_bar->setValue(0);
					$progress_bar->display();


					Debug::text('Found days to re-calculate: '.$udlf->getRecordCount() , __FILE__, __LINE__, __METHOD__, 10);



					$x=1;
					foreach($udlf as $ud_obj ) {
						//Debug::text($x .' / '. $udlf->getRecordCount() .' - User Date Id: '. $ud_obj->getId() .' Date: '.$ud_obj->getDateStamp(TRUE) .' User ID: '. $ud_obj->getUser() , __FILE__, __LINE__, __METHOD__, 10);

						$udlf->StartTransaction(); //If a transaction wraps the entire recalculation process, a deadlock is likely to occur for large batches.
						UserDateTotalFactory::reCalculateDay( $ud_obj->getId(), TRUE );
						$udlf->CommitTransaction();

						$progress_bar->setValue( Misc::calculatePercent( $x, $udlf->getRecordCount() ) );
						$progress_bar->display();

						$x++;
					}
				} else {
					Debug::text('No User Date rows to calculate!', __FILE__, __LINE__, __METHOD__, 10);
				}

			} else {
				Debug::text('Pay Period is CLOSED: ', __FILE__, __LINE__, __METHOD__, 10);
			}
		}
	}

	public function generate_paystubs(Request $request){
		$action = strtolower($request->input('action'));// Get the action from request
		$pay_period_ids = $request->input('pay_period_ids', []); // Default to an empty array if not set
		$next_page = $request->input('next_page', []); // Default to an empty array if not set
		$permission = $this->permission;
		$current_user = $this->currentUser;
		$current_company = $this->company;
		$profiler = $this->profiler;
		//Debug::setVerbosity(11);

		Debug::Text('Generate Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);
		/*
		if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}
		*/
		if ( !is_array($pay_period_ids) ) {
			$pay_period_ids = array($pay_period_ids);
		}
		print_r('Generate Pay Stubs: ');

		TTLog::addEntry( $current_company->getId(), TTi18n::gettext('Notice'), TTi18n::gettext('Recalculating Company Pay Stubs for Pay Periods:').' '. implode(',', $pay_period_ids) , $current_user->getId(), 'pay_stub' );

		$init_progress_bar = TRUE;
		foreach($pay_period_ids as $pay_period_id) {
			Debug::text('Pay Period ID: '. $pay_period_id, __FILE__, __LINE__, __METHOD__,10);

			$pplf = new PayPeriodListFactory();
			$pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() );
			
			$epoch = TTDate::getTime();

			foreach ($pplf->rs as $pay_period_obj) {
				$pplf->data = (array)$pay_period_obj;
				
				Debug::text('Pay Period Schedule ID: '. $pplf->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__,10);

				//Grab all users for pay period
				$ppsulf = new PayPeriodScheduleUserListFactory();
				$ppsulf->getByPayPeriodScheduleId( $pplf->getPayPeriodSchedule() );
				
				$total_pay_stubs = $ppsulf->getRecordCount();
				//echo "Total Pay Stubs: $total_pay_stubs - ". ceil(100 / $total_pay_stubs) ."<Br>\n";

				if ( $init_progress_bar == TRUE ) {
					//InitProgressBar( ceil(100 / $total_pay_stubs) );
					$this->InitProgressBar();
					$init_progress_bar = FALSE;
				}
				//$progress_bar->setValue(0);
				//$progress_bar->display();

				//Delete existing pay stub. Make sure we only
				//delete pay stubs that are the same as what we're creating.
				$pslf = new PayStubListFactory(); 
				$pslf->getByPayPeriodId( $pplf->getId() );

				foreach ( $pslf->rs as $pay_stub_obj ) {
					$pslf->data = (array)$pay_stub_obj;

					Debug::text('Existing Pay Stub: '. $pslf->getId(), __FILE__, __LINE__, __METHOD__,10);

					//Check PS End Date to match with PP End Date
					//So if an ROE was generated, it won't get deleted when they generate all other Pay Stubs
					//later on.
					if ( $pslf->getStatus() <= 25
							AND $pslf->getTainted() === FALSE
							AND $pslf->getEndDate() == $pplf->getEndDate() ) {
						Debug::text('Pay stub matched advance flag, deleting: '. $pslf->getId(), __FILE__, __LINE__, __METHOD__,10);
						$pslf->setDeleted(TRUE);
						$pslf->Save();
					} else {
						Debug::text('Pay stub does not need regenerating, or it is LOCKED!', __FILE__, __LINE__, __METHOD__,10);
					}
				}
				
				$i=1;
				foreach ($ppsulf->rs as $pay_period_schdule_user_obj) {
					$ppsulf->data = (array)$pay_period_schdule_user_obj;

					Debug::text('Pay Period User ID: '. $ppsulf->getUser(), __FILE__, __LINE__, __METHOD__,10);
					Debug::text('Total Pay Stubs: '. $total_pay_stubs .' - '. ceil( 1 / (100 / $total_pay_stubs) ) , __FILE__, __LINE__, __METHOD__,10);

					$profiler->startTimer( 'Calculating Pay Stub' );
					//Calc paystubs.
					$cps = new CalculatePayStub();

					$cps->setUser( $ppsulf->getUser() );
					$cps->setPayPeriod( $pplf->getId() );

					//=================================================================
					// calculation functions
					//=================================================================
                    $cps->removeTerminatePayStub();
                    $cps->calculateAllowance();
					$cps->calculate();
					//=================================================================
					unset($cps);
					$profiler->stopTimer( 'Calculating Pay Stub' );

					
					//$progress_bar->setValue( Misc::calculatePercent( $i, $total_pay_stubs ) );
					//$progress_bar->display();

					$i++;
				}
				echo 'hi';exit;
				unset($ppsulf);

				$ugsf = TTnew( 'UserGenericStatusFactory' );
				$ugsf->setUser( $current_user->getId() );
				$ugsf->setBatchID( $ugsf->getNextBatchId() );
				$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
				$ugsf->saveQueue();
				$next_page = URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Generating Pay Stubs', 'batch_next_page' => $next_page), '../users/UserGenericStatusList.php');

				unset($ugsf);
			}
		}
	}

	public function generate_paymiddle(){
		Debug::Text('Generate Pay Middle!', __FILE__, __LINE__, __METHOD__,10);

			if ( !$permission->Check('pay_period_schedule','enabled')
					OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

				$permission->Redirect( FALSE ); //Redirect
			}

			if ( !is_array($pay_period_ids) ) {
				$pay_period_ids = array($pay_period_ids);
			}
                
                
                $init_progress_bar = TRUE;
			foreach($pay_period_ids as $pay_period_id) {
				Debug::text('Pay Period ID: '. $pay_period_id, __FILE__, __LINE__, __METHOD__,10);

				$pplf = TTnew( 'PayPeriodListFactory' );
				$pplf->getByIdAndCompanyId($pay_period_id, $current_company->getId() );

				$epoch = TTDate::getTime();

				foreach ($pplf as $pay_period_obj) {
					Debug::text('Pay Period Schedule ID: '. $pay_period_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__,10);

					//Grab all users for pay period
					$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
					$ppsulf->getByPayPeriodScheduleId( $pay_period_obj->getPayPeriodSchedule() );

					$total_pay_stubs = $ppsulf->getRecordCount();
					//echo "Total Pay Stubs: $total_pay_stubs - ". ceil(100 / $total_pay_stubs) ."<Br>\n";

					if ( $init_progress_bar == TRUE ) {
						//InitProgressBar( ceil(100 / $total_pay_stubs) );
						InitProgressBar();
						$init_progress_bar = FALSE;
					}

					$progress_bar->setValue(0);
					$progress_bar->display();

					//Delete existing pay stub. Make sure we only
					//delete pay stubs that are the same as what we're creating.
					$pslf = TTnew( 'PayStubListFactory' );
					$pslf->getByPayPeriodId( $pay_period_obj->getId() );
					foreach ( $pslf as $pay_stub_obj ) {

						Debug::text('Existing Pay Stub: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__,10);

						//Check PS End Date to match with PP End Date
						//So if an ROE was generated, it won't get deleted when they generate all other Pay Stubs
						//later on.
						if ( $pay_stub_obj->getStatus() <= 25
								AND $pay_stub_obj->getTainted() === FALSE
								AND $pay_stub_obj->getEndDate() == $pay_period_obj->getEndDate() ) {
							Debug::text('Pay stub matched advance flag, deleting: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
							$pay_stub_obj->setDeleted(TRUE);
							$pay_stub_obj->Save();
						} else {
							Debug::text('Pay stub does not need regenerating, or it is LOCKED!', __FILE__, __LINE__, __METHOD__,10);
						}
					}

					$i=1;
					foreach ($ppsulf as $pay_period_schdule_user_obj) {
						Debug::text('Pay Period User ID: '. $pay_period_schdule_user_obj->getUser(), __FILE__, __LINE__, __METHOD__,10);
						Debug::text('Total Pay Stubs: '. $total_pay_stubs .' - '. ceil( 1 / (100 / $total_pay_stubs) ) , __FILE__, __LINE__, __METHOD__,10);

						$profiler->startTimer( 'Calculating Pay Stub' );
						//Calc paystubs.
						$cps = new CalculatePayStub();
						$cps->setUser( $pay_period_schdule_user_obj->getUser() );
						$cps->setPayPeriod( $pay_period_obj->getId() );
						$cps->calculateMid();
											$cps->calculateTotalMid();
						unset($cps);
						$profiler->stopTimer( 'Calculating Pay Stub' );

						$progress_bar->setValue( Misc::calculatePercent( $i, $total_pay_stubs ) );
						$progress_bar->display();

						$i++;
					}
					unset($ppsulf);

					$ugsf = TTnew( 'UserGenericStatusFactory' );
					$ugsf->setUser( $current_user->getId() );
					$ugsf->setBatchID( $ugsf->getNextBatchId() );
					$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
					$ugsf->saveQueue();
					$next_page = URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Generating Mid Pay', 'batch_next_page' => $next_page), '../users/UserGenericStatusList.php');

					unset($ugsf);
				}
			}
	}

	public function recalculate_paystub_ytd(){
		//Debug::setVerbosity(11);

		Debug::Text('Recalculating Pay Stub YTDs!', __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Pay Stub ID: '. $pay_stub_ids, __FILE__, __LINE__, __METHOD__,10);

		$init_progress_bar = TRUE;

		//Just need the pay_stub_id of the modified pay stub.
		$pslf = TTnew( 'PayStubListFactory' );

		$pslf->StartTransaction();
		if ( is_array($pay_stub_ids) ) {
			foreach( $pay_stub_ids as $pay_stub_id) {
				$pslf->getByIdAndCompanyIdAndIgnoreDeleted( $pay_stub_id, $current_company->getId() );

				if ( $pslf->getRecordCount() > 0 ) {

					$main_ps_obj = $pslf->getCurrent();

					//Get all pay stubs NEWER then this one.
					$pslf->getByUserIdAndStartDateAndEndDate( $main_ps_obj->getUser() , $main_ps_obj->getTransactionDate(), TTDate::getEndYearEpoch( $main_ps_obj->getTransactionDate() ) );
					$total_pay_stubs = $pslf->getRecordCount();
					if ( $total_pay_stubs > 0 ) {

						if ( $init_progress_bar == TRUE ) {
							InitProgressBar();
							$init_progress_bar = FALSE;
						}

						$progress_bar->setValue(0);
						$progress_bar->display();

						$x=1;
						foreach($pslf as $ps_obj ) {
							Debug::Text('ReCalculating Pay Stub ID: '. $ps_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
							$ps_obj->reCalculatePayStubYTD( $ps_obj->getId() );

							$progress_bar->setValue( Misc::calculatePercent( $x, $total_pay_stubs ) );
							$progress_bar->display();

							$x++;
						}

					} else {
						Debug::Text('No Newer Pay Stubs found!', __FILE__, __LINE__, __METHOD__,10);
					}
					unset($main_ps_obj);

				} else {
					Debug::Text('Pay Stub ID invalid!', __FILE__, __LINE__, __METHOD__,10);
				}
			}

		}

		//$pslf->FailTransaction();
		$pslf->CommitTransaction();

	}

	public function add_mass_punch(){
		if ( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 ) {
			//Debug::setVerbosity(11);

			$init_progress_bar = TRUE;

			if ( $init_progress_bar == TRUE ) {
				InitProgressBar();
				$init_progress_bar = FALSE;
			}

			$progress_bar->setValue(0);
			$progress_bar->display();

			//This will be slightly off depending on which days of the week they choose.
			$total_punches = count($filter_user_id) * TTDate::getDays($data['end_punch_full_time_stamp'] - $data['start_punch_full_time_stamp']);
			Debug::Text('Total Punches: '. $total_punches .' Users: '.  count($filter_user_id) .' Days: '.  TTDate::getDays($data['end_punch_full_time_stamp'] - $data['start_punch_full_time_stamp']), __FILE__, __LINE__, __METHOD__,10);

			$pcf = TTnew( 'PunchControlFactory' );
			$pf = TTnew( 'PunchFactory' );
			$ulf = TTnew( 'UserListFactory' );

			$pf->StartTransaction();

			TTLog::addEntry( $current_user->getId(), 500, 'Mass Punch: Time: '. TTDate::getDate('TIME', $data['start_punch_full_time_stamp']) .' Total Employees: '.  count($filter_user_id) .' Total Days: '. round( TTDate::getDays($data['end_punch_full_time_stamp'] - $data['start_punch_full_time_stamp']) ), $current_user->getId(), $pf->getTable() );

			$time_stamp = $data['start_punch_full_time_stamp'];

			$x=0;
			while ( $time_stamp <= $data['end_punch_full_time_stamp'] ) {
				if ( isset($data['dow'][TTDate::getDayOfWeek( $time_stamp )]) AND $data['dow'][TTDate::getDayOfWeek( $time_stamp )] == 1 ) {
					foreach( $filter_user_id as $user_id ) {

						$ulf->getByIdAndCompanyId($user_id,  $current_company->getId() );
						if ( $ulf->getRecordCount() == 1 ) {
							$user_obj = $ulf->getCurrent();
							$user_generic_status_label = $user_obj->getFullName(TRUE) .' @ '. TTDate::getDate('DATE+TIME', $time_stamp);
						} else {
							$user_obj = NULL;
							$user_generic_status_label = 'N/A @ '. TTDate::getDate('DATE+TIME', $time_stamp);
						}

						$pcf = TTnew( 'PunchControlFactory' );
						$pf = TTnew( 'PunchFactory' );

						Debug::Text('Punch Full Time Stamp: '. TTDate::getDate('DATE+TIME', $time_stamp), __FILE__, __LINE__, __METHOD__,10);

						//Set User before setTimeStamp so rounding can be done properly.
						$pf->setUser( $user_id );

						$pf->setType( $data['type_id'] );
						$pf->setStatus( $data['status_id'] );
						if ( isset($data['disable_rounding']) ) {
							$enable_rounding = FALSE;
						} else {
							$enable_rounding = TRUE;
						}
						$pf->setTimeStamp( $time_stamp, $enable_rounding );

						$pf->setPunchControlID( $pf->findPunchControlID() );

						$pf->setStation( $current_station->getID() );

						if ( $pf->isNew() ) {
							$pf->setActualTimeStamp( $time_stamp );
							$pf->setOriginalTimeStamp( $pf->getTimeStamp() );
						}

						if ( $pf->isValid() == TRUE ) {
							if ( $pf->Save( FALSE ) == TRUE ) {
								$pcf = TTnew( 'PunchControlFactory' );
								$pcf->setId( $pf->getPunchControlID() );
								$pcf->setPunchObject( $pf );

								if ( isset($data['branch_id']) ) {
									$pcf->setBranch( $data['branch_id'] );
								}
								if ( isset($data['department_id']) ) {
									$pcf->setDepartment( $data['department_id'] );
								}

								if ( isset($data['job_id']) ) {
									$pcf->setJob( $data['job_id'] );
								}
								if ( isset($data['job_item_id']) ) {
									$pcf->setJobItem( $data['job_item_id'] );
								}
								if ( isset($data['quantity']) ) {
									$pcf->setQuantity( $data['quantity'] );
								}
								if ( isset($data['bad_quantity']) ) {
									$pcf->setBadQuantity( $data['bad_quantity'] );
								}
								if ( isset($data['note']) ) {
									$pcf->setNote( $data['note'] );
								}

								if ( isset($data['other_id1']) ) {
									$pcf->setOtherID1( $data['other_id1'] );
								}
								if ( isset($data['other_id2']) ) {
									$pcf->setOtherID2( $data['other_id2'] );
								}
								if ( isset($data['other_id3']) ) {
									$pcf->setOtherID3( $data['other_id3'] );
								}
								if ( isset($data['other_id4']) ) {
									$pcf->setOtherID4( $data['other_id4'] );
								}
								if ( isset($data['other_id5']) ) {
									$pcf->setOtherID5( $data['other_id5'] );
								}

								$pcf->setEnableStrictJobValidation( TRUE );
								$pcf->setEnableCalcUserDateID( TRUE );
								$pcf->setEnableCalcTotalTime( TRUE );
								$pcf->setEnableCalcSystemTotalTime( TRUE );
								$pcf->setEnableCalcUserDateTotal( TRUE );
								$pcf->setEnableCalcException( TRUE );

								if ( $pcf->isValid() == TRUE ) {
									Debug::Text(' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__,10);

									if ( $pcf->Save( TRUE, TRUE ) == TRUE ) { //Force isNew() lookup.
										UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 30, NULL, NULL );
									} else {
										Debug::Text(' aFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
										$fail_transaction = TRUE;
										break;
									}
								} else {
									Debug::Text(' bFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
									UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $pcf->Validator->getTextErrors(), NULL );

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
							UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $pf->Validator->getTextErrors(), NULL );

							$fail_transaction = TRUE;
							break;
						}

						Debug::Text('Setting Percent: '. Misc::calculatePercent( $x, $total_punches ), __FILE__, __LINE__, __METHOD__,10);
						$progress_bar->setValue( Misc::calculatePercent( $x, $total_punches ) );
						$progress_bar->display();

						$x++;
					}
				} else {
					Debug::Text(' Skipping Day Of Week: ('. TTDate::getDayOfWeek( $time_stamp) .') '. TTDate::getDate('DATE+TIME', $time_stamp), __FILE__, __LINE__, __METHOD__,10);

					$x++;
				}
				$time_stamp = $time_stamp + 86400;
			}

			//$pf->FailTransaction();
			$pf->CommitTransaction();

			$ugsf = TTnew( 'UserGenericStatusFactory' );
			$ugsf->setUser( $current_user->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();

			$progress_bar->setValue( 100 );
			$progress_bar->display();

			$next_page = URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Mass Punch', 'batch_next_page' => urlencode( URLBuilder::getURL( array('pc_data' => $data, 'filter_user_id' => $filter_user_id ), '../punch/AddMassPunch.php' ) ) ), '../users/UserGenericStatusList.php');
		}
	}

	public function add_mass_schedule(){
		if ( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 ) {
			$init_progress_bar = TRUE;

			if ( $init_progress_bar == TRUE ) {
				InitProgressBar();
				$init_progress_bar = FALSE;
			}

			$progress_bar->setValue(0);
			$progress_bar->display();

			//This will be slightly off depending on which days of the week they choose.
			$total_shifts = count($filter_user_id) * TTDate::getDays($data['end_full_time_stamp'] - $data['start_full_time_stamp']);
			Debug::Text('Total Shifts: '. $total_shifts .' Users: '.  count($filter_user_id) .' Days: '.  TTDate::getDays($data['end_full_time_stamp'] - $data['start_full_time_stamp']), __FILE__, __LINE__, __METHOD__,10);

			$sf = TTnew( 'ScheduleFactory' );
			$ulf = TTnew( 'UserListFactory' );

			$sf->StartTransaction();

			TTLog::addEntry( $current_user->getId(), 500, 'Mass Schedule: Start Time: '. TTDate::getDate('TIME', $data['start_full_time_stamp']) .' End Time: '. TTDate::getDate('TIME', $data['end_full_time_stamp']) .' Total Employees: '.  count($filter_user_id) .' Total Days: '. round( TTDate::getDays($data['end_full_time_stamp'] - $data['start_full_time_stamp']) ), $current_user->getId(), $sf->getTable() );

			$time_stamp = $data['start_full_time_stamp'];

			$fail_transaction = FALSE;

			$x=0;
			while ( $time_stamp <= $data['end_full_time_stamp'] ) {
				if ( isset($data['dow'][TTDate::getDayOfWeek( $time_stamp )]) AND $data['dow'][TTDate::getDayOfWeek( $time_stamp )] == 1 ) {
					foreach( $filter_user_id as $user_id ) {
						if ( $data['start_time'] != '') {
							$start_time = strtotime( $data['start_time'], $time_stamp ) ;
						} else {
							$start_time = NULL;
						}
						if ( $data['end_time'] != '') {
							Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $time_stamp , __FILE__, __LINE__, __METHOD__,10);
							$end_time = strtotime( $data['end_time'], $time_stamp ) ;
							//Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);
						} else {
							$end_time = NULL;
						}

						//$user_date_id = UserDateFactory::findOrInsertUserDate($user_id, $time_stamp);
						Debug::Text('User ID: '. $user_id .' Date Stamp: '. TTDate::getDate('DATE', $time_stamp), __FILE__, __LINE__, __METHOD__,10);

						$conflicting_shifts = FALSE;
						if ( isset($data['overwrite']) AND $data['overwrite'] == 1 ) {
							Debug::Text('Overwriting Existing Shifts Enabled...', __FILE__, __LINE__, __METHOD__,10);
							$slf = TTnew( 'ScheduleListFactory' );
							//$slf->getConflictingByUserDateIdAndStartDateAndEndDate($user_date_id, $start_time, $end_time);
							$slf->getConflictingByUserIdAndStartDateAndEndDate( $user_id, $start_time, $end_time );
							if ( $slf->getRecordCount() > 0 ) {
								$conflicting_shifts = TRUE;
								Debug::Text('Found Conflicting Shift!!', __FILE__, __LINE__, __METHOD__,10);
								//Delete shifts.
								foreach( $slf as $s_obj ) {
									Debug::Text('Deleting Schedule Shift ID: '. $s_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
									$s_obj->setDeleted(TRUE);
									if ( $s_obj->isValid() ) {
										$s_obj->Save();
									}
								}
							} else {
								Debug::Text('NO Conflicting Shift found...', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						unset($slf, $s_obj);

						$ulf->getByIdAndCompanyId($user_id,  $current_company->getId() );
						if ( $ulf->getRecordCount() == 1 ) {
							$user_obj = $ulf->getCurrent();
							$user_generic_status_label = $user_obj->getFullName(TRUE) .' @ '. TTDate::getDate('DATE', $start_time) .': '. TTDate::getDate('TIME', $start_time) .' - '. TTDate::getDate('TIME', $end_time);
							if ( $conflicting_shifts == TRUE ) {
								$user_generic_status_label .= ' - '. TTi18n::gettext('DELETED CONFLICTING SHIFT');
							}
						} else {
							$user_obj = NULL;
							$user_generic_status_label = 'N/A @ '. TTDate::getDate('DATE', $start_time) .': '. TTDate::getDate('TIME', $start_time) .' - '. TTDate::getDate('TIME', $end_time);
						}

						//Re-initialize schedule factory here so we clear any errors preventing the next schedule from being inserted.
						$sf = TTnew( 'ScheduleFactory' );
						//$sf->setUserDateId( $user_date_id  );
						$sf->setUserDate($user_id, $time_stamp);
						$sf->setStatus( $data['status_id'] );
						$sf->setSchedulePolicyID( $data['schedule_policy_id'] );
						$sf->setAbsencePolicyID( $data['absence_policy_id'] );

						if ( isset($data['branch_id']) AND $data['branch_id'] == -1 ) {
							$sf->setBranch( $user_obj->getDefaultBranch() );
						} elseif ( isset($data['branch_id']) ) {
							$sf->setBranch( $data['branch_id'] );
						}
						if ( isset($data['department_id']) AND $data['department_id'] == -1 ) {
							$sf->setDepartment( $user_obj->getDefaultDepartment() );
						} elseif ( isset($data['department_id']) ) {
							$sf->setDepartment( $data['department_id'] );
						}

						if ( isset($data['job_id']) ) {
							$sf->setJob( $data['job_id'] );
						}

						if ( isset($data['job_item_id'] ) ) {
							$sf->setJobItem( $data['job_item_id'] );
						}

						$sf->setStartTime( $start_time );
						$sf->setEndTime( $end_time );

						if ( $sf->isValid() ) {
							$sf->setEnableReCalculateDay(TRUE);
							if ( $sf->Save() != TRUE ) {
								UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $sf->Validator->getTextErrors(), NULL );
								$fail_transaction = TRUE;
							} else {
								if ( $conflicting_shifts == TRUE ) {
									UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 20, NULL, NULL );
								} else {
									UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 30, NULL, NULL );
								}
							}
						} else {
							UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $sf->Validator->getTextErrors(), NULL );
							$fail_transaction = TRUE;
						}

						//Debug::Text('Setting Percent: '. Misc::calculatePercent( $x, $total_shifts ), __FILE__, __LINE__, __METHOD__,10);
						$progress_bar->setValue( Misc::calculatePercent( $x, $total_shifts ) );
						$progress_bar->display();

						$x++;
					}
				} else {
					Debug::Text(' Skipping Day Of Week: ('. TTDate::getDayOfWeek( $time_stamp) .') '. TTDate::getDate('DATE+TIME', $time_stamp), __FILE__, __LINE__, __METHOD__,10);

					$x++;
				}
				$time_stamp = $time_stamp + 86400;
			}

			//$sf->FailTransaction();
			$sf->CommitTransaction();

			$ugsf = TTnew( 'UserGenericStatusFactory' );
			$ugsf->setUser( $current_user->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();

			$progress_bar->setValue( 100 );
			$progress_bar->display();

			$next_page = URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Mass Schedule', 'batch_next_page' => urlencode( URLBuilder::getURL( array('data' => $data, 'filter_user_id' => $filter_user_id ), '../schedule/AddMassSchedule.php' ) ) ), '../users/UserGenericStatusList.php');
		}

	}

	public function add_mass_schedule_npvc(){
		if ( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 ) {

			$init_progress_bar = TRUE;

			if ( $init_progress_bar == TRUE ) {
				InitProgressBar();
				$init_progress_bar = FALSE;
			}

			$progress_bar->setValue(0);
			$progress_bar->display();
			//This will be slightly off depending on which days of the week they choose.
			$total_shifts = count($filter_user_id) * count($data['shifts']); 
						Debug::Text('Total Shifts: '. $total_shifts .' Users: '.  count($filter_user_id) .' Days: '.  TTDate::getDays($data['end_full_time_stamp'] - $data['start_full_time_stamp']), __FILE__, __LINE__, __METHOD__,10);

			$sf = TTnew( 'ScheduleFactory' );
			$ulf = TTnew( 'UserListFactory' );
						
			$sf->StartTransaction();

			TTLog::addEntry( $current_user->getId(), 500, 'Mass Schedule: Start Time: '. TTDate::getDate('TIME', $data['start_full_time_stamp']) .' End Time: '. TTDate::getDate('TIME', $data['end_full_time_stamp']) .' Total Employees: '.  count($filter_user_id) .' Total Days: '. round( TTDate::getDays($data['end_full_time_stamp'] - $data['start_full_time_stamp']) ), $current_user->getId(), $sf->getTable() );

				//			$time_stamp = $data['start_full_time_stamp'];

			$fail_transaction = FALSE;
						

						
						//NPVC   
							$shifts_array = array(); 
							$x =0;
				if(isset($data['shifts'])){
					foreach ($data['shifts'] as $shift_id =>$shift){
						$data_shifts = array(); 
						$data_shifts['status_id'] = $data['status_id'];
						if($shift_id == '0'){ //Shift A Time => 6:00 - 14:00 
							$data_shifts['start_time'] = '06:00';
							$data_shifts['end_time'] = '14:00'; 
						}
						if($shift_id == '1'){ //Shift B Time => 14:00 - 21:00 
								$data_shifts['start_time'] = '14:00';
								$data_shifts['end_time'] = '21:00';
						}
						if($shift_id == '2'){//Shift C Time => 21:00 - 06:00 
								$data_shifts['start_time'] = '21:00';
								$data_shifts['end_time'] = '06:00';
						}

								
								$data_shifts['start_date_stamp'] = TTDate::parseDateTime($data['start_date'][$shift_id]);
								$data_shifts['end_date_stamp'] = TTDate::parseDateTime($data['end_date'][$shift_id]);
								$data_shifts['dow'] = array(0=>1, 1=>1, 2=>1, 3=>1, 4=>1, 5=>1, 6=>1);
								
								$data_shifts['schedule_policy_id'] = $data['schedule_policy_id'];
								$data_shifts['absence_policy_id'] = $data['absence_policy_id'];
								$data_shifts['branch_id'] = $data['branch_id'];
								$data_shifts['department_id'] = $data['department_id'];
								$data_shifts['overwrite'] = $data['overwrite'];
								

								$date_dif_days = ($data_shifts['end_date_stamp'] - $data_shifts['start_date_stamp'])/(60*60*24);
								$rolling_length = $data['days_rec_'.$shift_id] + $data['days_gap_'.$shift_id];

								$date_unit = round($date_dif_days/$rolling_length);

			//                                echo (date('Y-m-d',$data_shifts['end_date_stamp'])); die;
								$day_count = 0;
								
							foreach( $filter_user_id as $user_id ) { 
								$roll_date = TTDate::parseDateTime($data['start_date'][$shift_id]);

								while ($roll_date <= TTDate::parseDateTime($data['end_date'][$shift_id])){
									
			//                                  echo "___".date('Y-m-d',$roll_date);
									for($i=0; $i<$rolling_length; $i++){
			//                                        
			//                                        
										if($i < $data['days_rec_'.$shift_id] && $roll_date <= TTDate::parseDateTime($data['end_date'][$shift_id])){
			//                                            echo '---'.date('Y-m-d',$roll_date);
											
											$data_shifts['start_date_stamp'] = $roll_date;
											$data_shifts['end_date_stamp'] = $roll_date;
											
											$data_shifts['start_full_time_stamp'] = TTDate::parseDateTime($data['start_date'][$shift_id].' '.$data_shifts['start_time']);
											$data_shifts['end_full_time_stamp'] = TTDate::parseDateTime($data['end_date'][$shift_id].' '.$data_shifts['end_time']);
											
											$data_shifts['parsed_start_time'] = TTDate::strtotime( $data_shifts['start_time'], $data_shifts['start_date_stamp'] ) ;
											$data_shifts['parsed_end_time'] = TTDate::strtotime( $data_shifts['end_time'], $data_shifts['start_date_stamp'] ) ;
															
											$shifts_array[] = $data_shifts;
			//                                            $data = $data_shifts;
											$time_stamp = $roll_date;
			//
			//                                            ///////////////////////////////////////////////
						if ( $data_shifts['start_time'] != '') {
							$start_time = strtotime( $data_shifts['start_time'], $time_stamp ) ;
						} else {
							$start_time = NULL;
						}
						if ( $data_shifts['end_time'] != '') {
							Debug::Text('End Time: '. $data_shifts['end_time'] .' Date Stamp: '. $time_stamp , __FILE__, __LINE__, __METHOD__,10);
							$end_time = strtotime( $data_shifts['end_time'], $time_stamp ) ;
							Debug::Text('bEnd Time: '. $data_shifts['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data_shifts['end_time']) , __FILE__, __LINE__, __METHOD__,10);
						} else {
							$end_time = NULL;
						}
			//
						//$user_date_id = UserDateFactory::findOrInsertUserDate($user_id, $time_stamp);
						Debug::Text('User ID: '. $user_id .' Date Stamp: '. TTDate::getDate('DATE', $time_stamp), __FILE__, __LINE__, __METHOD__,10);
			//
						$conflicting_shifts = FALSE;
						if ( isset($data_shifts['overwrite']) AND $data_shifts['overwrite'] == 1 ) {
							Debug::Text('Overwriting Existing Shifts Enabled...', __FILE__, __LINE__, __METHOD__,10);
							$slf = TTnew( 'ScheduleListFactory' );
							//$slf->getConflictingByUserDateIdAndStartDateAndEndDate($user_date_id, $start_time, $end_time);
							$slf->getConflictingByUserIdAndStartDateAndEndDate( $user_id, $start_time, $end_time );
							if ( $slf->getRecordCount() > 0 ) {
								$conflicting_shifts = TRUE;
								Debug::Text('Found Conflicting Shift!!', __FILE__, __LINE__, __METHOD__,10);
								//Delete shifts.
								foreach( $slf as $s_obj ) {
									Debug::Text('Deleting Schedule Shift ID: '. $s_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
									$s_obj->setDeleted(TRUE);
									if ( $s_obj->isValid() ) {
										$s_obj->Save();
									}
								}
							} else {
								Debug::Text('NO Conflicting Shift found...', __FILE__, __LINE__, __METHOD__,10);
							}
						}
			//                                                
						unset($slf, $s_obj);
			//
						$ulf->getByIdAndCompanyId($user_id,  $current_company->getId() );
						if ( $ulf->getRecordCount() == 1 ) {
							$user_obj = $ulf->getCurrent();
							$user_generic_status_label = $user_obj->getFullName(TRUE) .' @ '. TTDate::getDate('DATE', $start_time) .': '. TTDate::getDate('TIME', $start_time) .' - '. TTDate::getDate('TIME', $end_time);
							if ( $conflicting_shifts == TRUE ) {
								$user_generic_status_label .= ' - '. TTi18n::gettext('DELETED CONFLICTING SHIFT');
							}
						} else {
							$user_obj = NULL;
							$user_generic_status_label = 'N/A @ '. TTDate::getDate('DATE', $start_time) .': '. TTDate::getDate('TIME', $start_time) .' - '. TTDate::getDate('TIME', $end_time);
						}

						//Re-initialize schedule factory here so we clear any errors preventing the next schedule from being inserted.
						$sf = TTnew( 'ScheduleFactory' );
			//
						//$sf->setUserDateId( $user_date_id  );

						$sf->setUserDate($user_id, $roll_date); 
						$sf->setStatus( $data_shifts['status_id'] );
						$sf->setSchedulePolicyID( $data_shifts['schedule_policy_id'] );
						$sf->setAbsencePolicyID( $data_shifts['absence_policy_id'] );
						if ( isset($data_shifts['branch_id']) AND $data_shifts['branch_id'] == -1 ) {
							$sf->setBranch( $user_obj->getDefaultBranch() );
						} elseif ( isset($data_shifts['branch_id']) ) {
							$sf->setBranch( $data_shifts['branch_id'] );
						}
						if ( isset($data_shifts['department_id']) AND $data_shifts['department_id'] == -1 ) {
							$sf->setDepartment( $user_obj->getDefaultDepartment() );
						} elseif ( isset($data_shifts['department_id']) ) {
							$sf->setDepartment( $data_shifts['department_id'] );
						}

						if ( isset($data_shifts['job_id']) ) {
							$sf->setJob( $data_shifts['job_id'] );
						}

						if ( isset($data_shifts['job_item_id'] ) ) {
							$sf->setJobItem( $data_shifts['job_item_id'] );
						}

						$sf->setStartTime( $start_time );
						$sf->setEndTime( $end_time );
						if ( $sf->isValid() ) { 

			//                                                    echo "<pre>"; print_r(date('Y-m-d H:i:s',$sf->data['start_time']).'-->'.date('Y-m-d H:i:s',$sf->data	['end_time'])." -- 		"." USER:".$user_id." -- Date:".date('Y-m-d',$roll_date));  

							$sf->setEnableReCalculateDay(TRUE);
			//                                                    $sf->Save();
								


							if ( $sf->Save() != TRUE ) {
									UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $sf->Validator->getTextErrors(), NULL );
									$fail_transaction = TRUE;
							} else {
									if ( $conflicting_shifts == TRUE ) {
											UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 20, NULL, NULL );
									} else {
											UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 30, NULL, NULL );
									}
							}
						} else {
							UserGenericStatusFactory::queueGenericStatus( $user_generic_status_label, 10, $sf->Validator->getTextErrors(), NULL );
							$fail_transaction = TRUE;
						}

						
			//					}
			//			
										}
										$roll_date = strtotime('+1 day', $roll_date); 
			//			
									}
			//                                                          
			//                                        $roll_date = strtotime('+1 day', $roll_date);             
									$day_count = $day_count + $i;
								}   
								
								Debug::Text('Setting Percent: '. Misc::calculatePercent( $x, $total_shifts ), __FILE__, __LINE__, __METHOD__,10);
						$progress_bar->setValue( Misc::calculatePercent( $x, $total_shifts ) );
						$progress_bar->display();

						$x++;
							}
							
			//                                echo ($data_shifts['end_date_stamp'] - $data_shifts['start_date_stamp'])/(60*60*24); die;

							

					} 
				} 
						
				
						$data = array();

			//$sf->FailTransaction(); 
			$sf->CommitTransaction();

			$ugsf = TTnew( 'UserGenericStatusFactory' );
			$ugsf->setUser( $current_user->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();

			$progress_bar->setValue( 100 );
			$progress_bar->display(); 

			$next_page = URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Mass Schedule', 'batch_next_page' => urlencode( URLBuilder::getURL( array('data' => $data, 'filter_user_id' => $filter_user_id ), '../schedule/AddMassScheduleNpvc.php' ) ) ), '../users/UserGenericStatusList.php');

		}

	}

	public function recalculate_accrual_policy(){
		//Debug::setVerbosity(11);
		if ( isset($data['accrual_policy_id']) AND isset( $data['start_date'] ) AND isset( $data['end_date'] ) ) {
			if ( $data['start_date'] <= $data['end_date']) {
				$start_date = TTDate::getMiddleDayEpoch( $data['start_date'] );
				$end_date = TTDate::getMiddleDayEpoch( $data['end_date'] );
				$total_days = TTDate::getDays( ($end_date-$start_date) );
				$offset = (86400/2);

				$init_progress_bar = TRUE;

				if ( $init_progress_bar == TRUE ) {
					InitProgressBar();
					$init_progress_bar = FALSE;
				}

				$progress_bar->setValue(0);
				$progress_bar->display();

				$apf = TTnew( 'AccrualPolicyFactory' );
				$aplf = TTnew( 'AccrualPolicyListFactory' );

				$aplf->getByIdAndCompanyId( (int)$data['accrual_policy_id'], $current_company->getId() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						$aplf->StartTransaction();

						TTLog::addEntry( $current_user->getId(), 500, 'Recalculate Accrual Policy: '. $ap_obj->getName() .' Start Date: '. TTDate::getDate('TIME', $data['start_date']) .' End Date: '. TTDate::getDate('TIME', $data['end_date']) .' Total Days: '. round( $total_days ), $current_user->getId(), $ap_obj->getTable() );

						$x=0;
						for( $i=$start_date; $i < $end_date; $i+=(86400) ) {
							//$i = TTDate::getBeginDayEpoch( $i ); //This causes infinite loops during DST transitions.
							Debug::Text('Recalculating Accruals for Date: '. TTDate::getDate('DATE+TIME', TTDate::getBeginDayEpoch( $i ) ), __FILE__, __LINE__, __METHOD__,10);
							$ap_obj->addAccrualPolicyTime( TTDate::getBeginDayEpoch( $i ), $offset );

							$progress_bar->setValue( Misc::calculatePercent( $x, $total_days ) );
							$progress_bar->display();

							$x++;
						}

						//$aplf->FailTransaction();
						$aplf->CommitTransaction();
					}
				}

				$progress_bar->setValue( 100 );
				$progress_bar->display();
			}
		}
	}

	public function process_late_leave(){
		
		InitProgressBar( 0 );
		$progress_bar->setValue(0);
		$progress_bar->display();
		
		$ulf = new UserListFactory();
		$ulf->getByCompanyId($current_company->getId());
		
		$plf = new PunchListFactory();
		$udlf = new UserDateListFactory();
		$pplf = new PayPeriodListFactory();
		
		
		if($ulf->getRecordCount() >0){
			
			$user_count = $ulf->getRecordCount();
			$percentage = 0;
			$current = 0;
			
			foreach ($ulf as $user_obj){
				
				$late_date_arry = array();
				
				$current++;
				
				foreach($pay_period_ids as $pp_id){
					
					$pplf->getById($pp_id);
					
					if($pplf->getRecordCount() > 0){
						$ppf = new PayPeriodFactory();
						$ppf = $pplf->getCurrent();
						
						$udlf = new UserDateListFactory();

						$udlf->getByUserIdAndPayPeriodID($user_obj->getId(), $ppf->getId());
						
						if($udlf->getRecordCount() > 0){
							
							foreach($udlf as $udf){
								
								$udf->getId();
								$plf->getByUserDateIdAndStatusId($udf->getId(), 10);
								
								if($plf->getRecordCount() > 0){
									
									$pf = $plf->getCurrent();
									
									$checking_date = $pf->getTimeStamp();
									$punch_date = dateTime::createFromFormat("Y-m-d H:i:s", $checking_date);
									$time_punch = $punch_date->format('H:i:s');
									$date_punch =  $punch_date->format('Y-m-d');
									
									$cut_off_time  = dateTime::createFromFormat("Y-m-d H:i:s",  $date_punch.' 08:15:00');
									
									if($checking_date > $cut_off_time){
									   $late_date_arry[]= $punch_date;
									}
									
									
									
								}
								
								
								
								
							}
							
							
							
							
						}
						
						
					  //  $plf->getby
						
						
						
						
						
						$ppf->setIsHrProcess(1);
			
					   // $ppf->save();
						
					}
					
					
				}
				
				$number_of_late = count($late_date_arry);
				$sequence = 0;
				$not_in_sequence = 0;
				$is_in_seq = FALSE;
				$number_of_leave = 0;
			   
				for($i=0;$number_of_late<$i;$i++){
					
					$sequence++;
					
				   $first_date_of_month = $late_date_arry[$i]->$date->modify('first day of this month');
				   $firstday = $date->format('Y-m-d');
				   
				   $check_date_of_month= $late_date_arry[$i]->format('Y-m-d');
					
					if($firstday != $check_date_of_month){
						
						$today =  new DateTime($late_date_arry[$i]->format('Y-m-d'));
						
						
						$tomorrow = new DateTime($late_date_arry[$i]->format('Y-m-d'));
						$tomorrow->add(new DateInterval("P1D"))->format('Y-m-d');
						
						$next_date =  new DateTime($late_date_arry[$i+1]->format('Y-m-d'));
						
						if($tomorrow == $next_date){
							 $sequence++;
							 
							  $next_plus_date = $next_date->add(new DateInterval("P1D"))->format('Y-m-d');
							  $next_next_date =  new DateTime($late_date_arry[$i+2]->format('Y-m-d'));
							  
							  if($next_plus_date == $next_next_date){
								  
								  
								  $not_in_sequence = 0;
								  $sequence++;
								   
								   if(($sequence%3) == 0){
									   
									   $number_of_leave = $number_of_leave + 0.5;
										$i= $i +2;
								  
									   $sequence = 0;
								   }
								  
							  }
							  else{
								  $not_in_sequence++;
								  $sequence = 0;
							  }
							  
						}
						else{
					   
							$not_in_sequence++;
							 $sequence = 0;
						}
					  
						
						
						
					}
					else{
						
					}
				}
				
				
				  
						 
				
				
			   // $plf->getby
				
				$percentage = (int)(($current/$user_count)*100);
				$progress_bar->setValue($percentage);
		$progress_bar->display();
				
			}
			
		}

		
		$progress_bar->setValue( 100 );
		$progress_bar->display();
	
	}

	public function generate_december_bonuses(){

            
		InitProgressBar( 0 );
		$progress_bar->setValue(0);
		$progress_bar->display();
		
		$ulf = new UserListFactory();
		$ulf->getByCompanyId($current_company->getId());
		
		$uwlf = new UserWageListFactory();
		
		$bdlf = new BonusDecemberListFactory();
		$bdlf->getById($dec_bo_id);
	  
		
		$bdf_obj = $bdlf->getCurrent();
		
		$start_date = new DateTime();
		$start_date->setTimestamp($bdf_obj->getStartDate());
		
		$bonus_start_date = $start_date->format('Y-m-d');
		
		$end_date = new DateTime();
		$end_date->setTimestamp($bdf_obj->getEndDate());
		$bonus_end_date = $end_date->format('Y-m-d');
		
		
		 if($ulf->getRecordCount() >0){
			
			$user_count = $ulf->getRecordCount();
			$percentage = 0;
			$current = 0;
			
			foreach ($ulf as $user_obj){
				
				$current++;
				
				$hire_date = new DateTime();
				$hire_date->setTimestamp($user_obj->getHireDate());
				
				$diff =$hire_date->diff($end_date);
				
				$months = ($diff->format('%y') * 12) + $diff->format('%m');
				
				if($months > 12){ $months =12; }
				
				if($months>0){
					
						$uwlf->getByUserId($user_obj->getId());
						$uw_obj = $uwlf->getCurrent();

						$user_wage = $uw_obj->getWage();

						$uklf = new UserKpiListFactory();
						$uklf->getUserAccountByCompanyIdAndUserIdAndStartDateAndEndDate($user_obj->getId(),$bonus_start_date,$bonus_end_date);
						
						$ukf_obj = $uklf->getCurrent();
						
						$kpi_score = $ukf_obj->getTotalScore();
						if(empty($kpi_score)){ $kpi_score = 0;}
						
						$bonus_amount =   (($user_wage * ($months/12))*$kpi_score/100)* $bdf_obj->getYNumber();
						
						
						$bulf = new BonusDecemberUserListFactory();
						
						$bulf->getByUserIdAndBonusDecemberId($user_obj->getId(), $dec_bo_id);
						
					   
						//$bonus_user;
						
						if($bulf->getRecordCount()>0){
							 $bonus_user = $bulf->getCurrent();


								if(isset($dec_bo_id)){
								  $bonus_user->setBonusDecember($dec_bo_id);
								}

								$user_id = $user_obj->getId();
								if(isset($user_id)){
								   $bonus_user->setUser($user_obj->getId());
								}



								if(isset($user_wage)){
								  $bonus_user->setWage($user_wage);
								}

								if(isset($months)){
								  $bonus_user->setServicePeriods($months);
								}


								if(isset($kpi_score)){
								 $bonus_user->setKppMark($kpi_score);
								}

								if(isset($bonus_amount)){
								  $bonus_user->setBonusAmount($bonus_amount);
								}




								if($bonus_user->isValid()){
									$bonus_user->Save();
								}
						}
						else{
						
							$bonus_user = new BonusDecemberUserFactory();
							
							
						if(isset($dec_bo_id)){
						  $bonus_user->setBonusDecember($dec_bo_id);
						}
						
						$user_id = $user_obj->getId();
						if(isset($user_id)){
						   $bonus_user->setUser($user_obj->getId());
						}
						 
						 
					   
						if(isset($user_wage)){
						  $bonus_user->setWage($user_wage);
						}
						
						if(isset($months)){
						  $bonus_user->setServicePeriods($months);
						}
						
					  
						if(isset($kpi_score)){
						 $bonus_user->setKppMark($kpi_score);
						}
						
						if(isset($bonus_amount)){
						  $bonus_user->setBonusAmount($bonus_amount);
						}
						
						
						
						
						if($bonus_user->isValid()){
							$bonus_user->Save();
						}
					  }
						
						
						unset($bonus_user);
				}
				

				$percentage = (int)(($current/$user_count)*100);
				$progress_bar->setValue($percentage);
		$progress_bar->display();
				
			}
			
			
		 }
		
		$progress_bar->setValue( 100 );
		$progress_bar->display();
		
	}

	public function generate_attendance_bonuses(){

		InitProgressBar( 0 );
		$progress_bar->setValue(0);
		$progress_bar->display();
		
		$ulf = new UserListFactory();
		$ulf->getByCompanyId($current_company->getId());
		
		$uwlf = new UserWageListFactory();
		
		
		$ablf = new AttendanceBonusListFactory();
		$ablf->getById($att_bo_id);
		
		$abf_obj = $ablf->getCurrent();
		
		
		
		if($ulf->getRecordCount() >0){
			
			
			$user_count = $ulf->getRecordCount();
			$percentage = 0;
			$current = 0;
			
			
			foreach($ulf as $uf_obj){
				
				$current++;
			   
				
				$bdulf = new BonusDecemberUserListFactory();
				$bdulf->getByUserIdAndBonusDecemberId($uf_obj->getId(), $abf_obj->getBonusDecember());
				$bdf_obj = $bdulf->getCurrent();
				
				$december_bonus_amount=  $bdf_obj->getBonusAmount() ? $bdf_obj->getBonusAmount():0;
			   // echo $bdf_obj->getBonusAmount().' ';
				
				$start_date_timestamp = $abf_obj->getBonusDecemberObject()->getStartDate();
				$end_date_timestamp = $abf_obj->getBonusDecemberObject()->getEndDate();
				
				$st_time = new DateTime();
				$st_time->setTimestamp($start_date_timestamp);
				$start_date = $st_time->format('Y-m-d').' 00:00:00';
				
				$en_time = new DateTime();
				$en_time->setTimestamp($end_date_timestamp);
				$end_date = $en_time->format('Y-m-d').' 23:59:59';
				
				$alf_for_casual_70 = new AccrualListFactory();
				$alf_for_casual_70->getByAccrualPolicyIdAndStartDateAndEndDate($current_company->getId(), $uf_obj->getId(), 2, 70,$start_date,$end_date);
				
				$casual_initial_balance = $alf_for_casual_70->getCurrent()->getColumn('amount')? $alf_for_casual_70->getCurrent()->getColumn('amount') :0;
				
				$alf_for_casual_30 = new AccrualListFactory();
				$alf_for_casual_30->getByAccrualPolicyIdAndStartDateAndEndDate($current_company->getId(), $uf_obj->getId(), 2, 30,$start_date,$end_date);
				
				$casual_awarded = $alf_for_casual_30->getCurrent()->getColumn('amount') ? $alf_for_casual_30->getCurrent()->getColumn('amount'):0;
				
				$total_casual_granted = $casual_initial_balance + $casual_awarded;
				
				
				$alf_for_casual = new AccrualListFactory();
				$alf_for_casual->getByAccrualPolicyIdAndStartDateAndEndDate($current_company->getId(), $uf_obj->getId(), 2, 55,$start_date,$end_date);
				
				$casual_taken_leave = $alf_for_casual->getCurrent()->getColumn('amount');
				
				$casual_leave_balance = $total_casual_granted + $casual_taken_leave;
				
				
				$alf_for_anual_70 = new  AccrualListFactory();
				$alf_for_anual_70->getByAccrualPolicyIdAndStartDateAndEndDate($current_company->getId(), $uf_obj->getId(), 7, 70,$start_date,$end_date);
				
				$anual_initial_balance = $alf_for_anual_70->getCurrent()->getColumn('amount')? $alf_for_anual_70->getCurrent()->getColumn('amount') :0;
				
				
				$alf_for_anual_30 = new  AccrualListFactory();
				$alf_for_anual_30->getByAccrualPolicyIdAndStartDateAndEndDate($current_company->getId(), $uf_obj->getId(), 7, 30,$start_date,$end_date);
				
				$anual_awarded = $alf_for_anual_30->getCurrent()->getColumn('amount')? $alf_for_anual_30->getCurrent()->getColumn('amount') :0;
				
				$total_anual_leave_granted = $anual_initial_balance + $anual_awarded;
				
				
				$alf_for_anual_taken = new  AccrualListFactory();
				$alf_for_anual_taken->getByAccrualPolicyIdAndStartDateAndEndDate($current_company->getId(), $uf_obj->getId(), 7, 55,$start_date,$end_date);
				
				$anual_taken = $alf_for_anual_taken->getCurrent()->getColumn('amount')? $alf_for_anual_taken->getCurrent()->getColumn('amount') :0;
				
				
				$total_anual_leave_balance = $total_anual_leave_granted + $anual_taken;
				
				$total_leave_balance = (($total_anual_leave_balance + $casual_leave_balance)/28800);
				
				
				
				$abulf = new AttendanceBonusUserListFactory();
				$abulf->getByUserIdAndAttendanceBonusId($uf_obj->getId(), $att_bo_id);
				
				$udlf = new UserDateListFactory();
				
				$nopay_start_date =$st_time->format('Y-m-d');
				$nopay_end_date = $en_time->format('Y-m-d');
				
				$udlf->getTotalNopayTime($uf_obj->getId(), 10, $nopay_start_date, $nopay_end_date);
			   $nopay_time = $udlf->getCurrent()->getColumn('total_nopay_time')? $udlf->getCurrent()->getColumn('total_nopay_time') :0;
				
				
				$nopay_days = number_format($nopay_time/28800,2);
				
				if( $abulf->getRecordCount() > 0){
					
					 $abuf = $abulf->getCurrent();
					
					$abuf->setUser($uf_obj->getId());
					$abuf->setBonusAttendance($att_bo_id);
					$abuf->setLeaveBalance($total_leave_balance);
					$abuf->setNopay($nopay_days);
					
					
					$bonus_attendance_amount = ($december_bonus_amount * $total_leave_balance)/(12*((12-$nopay_days)/12));
					
					$abuf->setBonusAmount(number_format($bonus_attendance_amount,2));
					
					if($abuf->isValid()){
					   $abuf->save(); 
					}
					
				}
				else{
					
					$abuf = new AttendanceBonusUserFactory();
					
					$abuf->setUser($uf_obj->getId());
					$abuf->setBonusAttendance($att_bo_id);
					$abuf->setLeaveBalance($total_leave_balance);
					
					
					
					$abuf->setNopay($nopay_days);
					
					$total_leave_balance=$total_leave_balance ? $total_leave_balance:0;
					
					
					 $bonus_attendance_amount = ($december_bonus_amount * $total_leave_balance)/(12*((12-$nopay_days)/12));
					
					$abuf->setBonusAmount(number_format($bonus_attendance_amount,2));
					
					if($abuf->isValid()){
					   $abuf->save(); 
					}
					
				}
				
				
				
				$percentage = (int)(($current/$user_count)*100);
				$progress_bar->setValue($percentage);
		$progress_bar->display();
			}
			
		}
		
		
		
		
		
		
		$progress_bar->setValue( 100 );
		$progress_bar->display();
		
		
		
	}

}

?>


<?php
/*
sleep(2);

if ( Debug::getVerbosity() <= 10 ) {
	if (isset($next_page) AND $next_page != '') {
		?>
		<script type="text/javascript">parent.location.href='<?php echo $next_page;?>'</script>
		<?php
	}
}

if ( Debug::getVerbosity() > 10 ) {
	Debug::Display();
}
Debug::writeToLog();
*/
?>
