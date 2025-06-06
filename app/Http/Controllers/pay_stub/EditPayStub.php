<?php

namespace App\Http\Controllers\pay_stub;

use App\Http\Controllers\Controller;
use App\Models\Core\CurrencyListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\PayStub\PayStubEntryListFactory;
use App\Models\PayStub\PayStubFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditPayStub extends Controller
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
        if ( !$permission->Check('pay_stub','enabled')
				OR !( $permission->Check('pay_stub','edit') OR $permission->Check('pay_stub','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Edit Employee Pay Stub';

		if ( isset($data) ) {
			$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
			$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
			$data['transaction_date'] = TTDate::parseDateTime( $data['transaction_date'] );
		}
		$modified_entry = (int)$modified_entry;
		
		$psf = new PayStubFactory();
		

		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		if ( $id != '' AND $action != 'submit' ) {
			$psealf = new PayStubEntryAccountListFactory(); 
			$pslf = new PayStubListFactory();

			$pslf->getByCompanyIdAndId( $current_company->getId(), $id );
			if ( $pslf->getRecordCount() > 0 ) {
				foreach ($pslf->rs as $ps_obj) {
					$pslf->data = (array)$ps_obj;
					$ps_obj = $pslf;

					//Get pay stub entries.
					$pself = new PayStubEntryListFactory();
					$pself->getByPayStubId( $ps_obj->getId() );

					$prev_type = NULL;
					$description_subscript_counter = 1;
					$pay_stub_entries = NULL;
					$pay_stub_entry_descriptions = NULL;
					foreach ($pself->rs as $pay_stub_entry) {
						$pself->data = (array)$pay_stub_entry;
						$pay_stub_entry = $pself;

						$description_subscript = NULL;

						//$pay_stub_entry_name_obj = $psenlf->getById( $pay_stub_entry->getPayStubEntryNameId() ) ->getCurrent();
						$pay_stub_entry_account_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() ) ->getCurrent();

						if ( $prev_type == 40 OR $pay_stub_entry_account_obj->getType() != 40 ) {
							$type = $pay_stub_entry_account_obj->getType();
						}

						//var_dump( $pay_stub_entry->getDescription() );
						if ( $pay_stub_entry->getDescription() !== NULL
								AND $pay_stub_entry->getDescription() !== FALSE
								AND strlen($pay_stub_entry->getDescription()) > 0) {
							$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,
																	'description' => $pay_stub_entry->getDescription() );

							$description_subscript = $description_subscript_counter;

							$description_subscript_counter++;
						}

						$pay_stub_entries[$type][] = array(
													'id' => $pay_stub_entry->getId(),
													'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),
													'pay_stub_amendment_id' => $pay_stub_entry->getPayStubAmendment(),
													'tmp_type' => $type,
													'type' => $pay_stub_entry_account_obj->getType(),
													'name' => $pay_stub_entry_account_obj->getName(),
													'display_name' => __($pay_stub_entry_account_obj->getName()),
													'rate' => $pay_stub_entry->getRate(),
													'units' => $pay_stub_entry->getUnits(),
													'ytd_units' => $pay_stub_entry->getYTDUnits(),
													'amount' => $pay_stub_entry->getAmount(),
													'ytd_amount' => $pay_stub_entry->getYTDAmount(),

													'description' => $pay_stub_entry->getDescription(),
													'description_subscript' => $description_subscript,

													'created_date' => $pay_stub_entry->getCreatedDate(),
													'created_by' => $pay_stub_entry->getCreatedBy(),
													'updated_date' => $pay_stub_entry->getUpdatedDate(),
													'updated_by' => $pay_stub_entry->getUpdatedBy(),
													'deleted_date' => $pay_stub_entry->getDeletedDate(),
													'deleted_by' => $pay_stub_entry->getDeletedBy()
													);
						$prev_type = $pay_stub_entry_account_obj->getType();
					}
					//var_dump($pay_stub_entries);

					$data = array(
										'id' => $ps_obj->getId(),
										'display_id' => str_pad($ps_obj->getId(),12,0, STR_PAD_LEFT),
										'user_id' => $ps_obj->getUser(),
										'pay_period_id' => $ps_obj->getPayPeriod(),
										'currency_id' => $ps_obj->getCurrency(),
										'start_date' => $ps_obj->getStartDate(),
										'end_date' => $ps_obj->getEndDate(),
										'transaction_date' => $ps_obj->getTransactionDate(),
										//'advance' => $ps_obj->getAdvance(),
										'status_id' => $ps_obj->getStatus(),
										'entries' => $pay_stub_entries,
										'entry_descriptions' => $pay_stub_entry_descriptions,

										'created_date' => $ps_obj->getCreatedDate(),
										'created_by' => $ps_obj->getCreatedBy(),
										'updated_date' => $ps_obj->getUpdatedDate(),
										'updated_by' => $ps_obj->getUpdatedBy(),
										'deleted_date' => $ps_obj->getDeletedDate(),
										'deleted_by' => $ps_obj->getDeletedBy()
									);
					unset($pay_stub_entries, $pay_stub_entry_descriptions);

					//Get Pay Period information
					$pplf = new PayPeriodListFactory(); 
					$pay_period_obj = $pplf->getById( $ps_obj->getPayPeriod() )->getCurrent();

					//Get pay period numbers
					$ppslf = new PayPeriodScheduleListFactory();
					$pay_period_schedule_obj = $ppslf->getById( $pay_period_obj->getPayPeriodSchedule() )->getCurrent();


					$pay_period_data = array(
											//'advance' => $ps_obj->getAdvance(),
											'start_date' => TTDate::getDate('DATE', $pay_period_obj->getStartDate() ),
											'end_date' => TTDate::getDate('DATE',  $pay_period_obj->getEndDate() ),
											'transaction_date' => TTDate::getDate('DATE', $pay_period_obj->getTransactionDate() ),
											//'pay_period_number' => $pay_period_schedule_obj->getCurrentPayPeriodNumber( $pay_period_obj->getTransactionDate(), $pay_period_obj->getEndDate() ),
											'annual_pay_periods' => $pay_period_schedule_obj->getAnnualPayPeriods()
											);

					//Get User information
					$ulf = new UserListFactory();
					$user_obj = $ulf->getById( $ps_obj->getUser() )->getCurrent();
					$data['user_full_name'] = $user_obj->getFullName();

					//Get company information
					/*
					$clf = new CompanyListFactory();
					$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();
					*/
				}
			}
		}
		$pay_stub_status_options = $psf->getOptions('status');

		$data['pay_stub_status_options'] = Option::getByArray( array(25,40), $pay_stub_status_options);

		$culf = new CurrencyListFactory(); 
        $culf->getByCompanyId( $current_company->getId() );
		$data['currency_options'] = $culf->getArrayByListFactory( $culf, FALSE, TRUE );

		$viewData['data'] = $data;
		$viewData['pay_stub_id'] = $id;
		$viewData['filter_pay_period_id'] = $filter_pay_period_id;
		$viewData['modified_entry'] = $modified_entry;

		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;

		$viewData['psf'] = $psf;

        return view('pay_stub/EditPayStub', $viewData);

    }

	public function submit(){
		$psf = new PayStubFactory();

		//Debug::setVerbosity(11);

		/*

			Add pay_stub_amendment_id to the pay_stub_entry table, so we can link them back.
			Disable editing entries from pay stub amendments.

			Modified pay stub entries get deleted, and new ones are inserted? This will keep
			a history of edits?

		*/
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		if ( isset($id) ) {
			$pslf = new PayStubListFactory();

			$psf = $pslf->getByID( $id )->getCurrent();
			$psf->StartTransaction();

			$psf->setCurrency( $data['currency_id'] );
			$psf->setStartDate( $data['start_date'] );
			$psf->setEndDate( $data['end_date'] );
			$psf->setTransactionDate( $data['transaction_date'] );

			$psf->setStatus( $data['status_id'] );
			$psf->setTainted(TRUE); //So we know it was modified.

			if ( $modified_entry == 1 AND isset($data['entries']) ) {
				Debug::Text(' Found modified entries!', __FILE__, __LINE__, __METHOD__,10);

				//Load previous pay stub
				$psf->loadPreviousPayStub();

				//Delete all entries, so they can be re-added.
				$psf->deleteEntries( TRUE );

				//When editing pay stubs we can't re-process linked accruals.
				$psf->setEnableLinkedAccruals( FALSE );

				foreach($data['entries'] as $pay_stub_entry_type_id => $pay_stub_entry_arr ) {
					foreach($pay_stub_entry_arr as $pay_stub_entry_id => $pay_stub_entry ) {
						if ( $pay_stub_entry['type'] != 40 ) {
							Debug::Text('Pay Stub Entry ID: '. $pay_stub_entry_id , __FILE__, __LINE__, __METHOD__,10);
							Debug::Text(' Amount: '. $pay_stub_entry['amount'] , __FILE__, __LINE__, __METHOD__,10);

							$pself = new PayStubEntryListFactory();
							$pay_stub_entry_obj = $pself->getById( $pay_stub_entry_id )->getCurrent();

							if ( !isset($pay_stub_entry['units']) OR $pay_stub_entry['units'] == '' ) {
								$pay_stub_entry['units'] = 0;
							}
							if ( !isset($pay_stub_entry['rate']) OR $pay_stub_entry['rate'] == '' ) {
								$pay_stub_entry['rate'] = 0;
							}
							if ( !isset($pay_stub_entry['description']) OR $pay_stub_entry['description'] == '' ) {
								$pay_stub_entry['description'] = NULL;
							}
							if ( !isset($pay_stub_entry['pay_stub_amendment_id']) OR $pay_stub_entry['pay_stub_amendment_id'] == '' ) {
								$pay_stub_entry['pay_stub_amendment_id'] = NULL;
							}
							Debug::Text(' Pay Stub Amendment Id: '. $pay_stub_entry['pay_stub_amendment_id'], __FILE__, __LINE__, __METHOD__,10);

							$psf->addEntry( $pay_stub_entry_obj->getPayStubEntryNameId(), $pay_stub_entry['amount'], $pay_stub_entry['units'], $pay_stub_entry['rate'], $pay_stub_entry['description'], $pay_stub_entry['pay_stub_amendment_id'] );
						} else {
							Debug::Text(' Skipping Total Entry. ', __FILE__, __LINE__, __METHOD__,10);

						}
					}
				}
				unset($pay_stub_entry_id, $pay_stub_entry);

				$psf->setEnableCalcYTD( TRUE );
				$psf->setEnableProcessEntries( TRUE );
				$psf->processEntries();
			}

			Debug::Text(' Saving pay stub ', __FILE__, __LINE__, __METHOD__,10);
			//Can't check isValid here, because preSave hasn't been called.
			if ( $psf->isValid() ) {
				if ( $psf->Save() ) {
					//$psf->FailTransaction();

					$psf->CommitTransaction();

					//Redirect::Page( URLBuilder::getURL( array('action' => 'recalculate_paystub_ytd', 'pay_stub_ids' => array($id), 'next_page' => urlencode( URLBuilder::getURL( array('filter_pay_period_id' => $filter_pay_period_id ), '../pay_stub/PayStubList.php') ) ), '../progress_bar/ProgressBarControl.php') );
					Redirect::Page( URLBuilder::getURL( array('filter_pay_period_id' => $filter_pay_period_id ), '../pay_stub/PayStubList.php') );

				} else {
					$psf->FailTransaction();
				}
			}

		}
	}
}

?>