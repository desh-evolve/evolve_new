<?php

namespace App\Http\Controllers\pay_stub_amendment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\PayStub\PayStubEntryAccountLinkListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\PayStubAmendment\RecurringPayStubAmendmentFactory;
use App\Models\PayStubAmendment\RecurringPayStubAmendmentListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditRecurringPayStubAmendment extends Controller
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

    public function index($id = null) {
        /*
        if ( !$permission->Check('recurring_pay_stub_amendment','enabled')
				OR !( $permission->Check('recurring_pay_stub_amendment','edit') OR $permission->Check('recurring_pay_stub_amendment','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Edit Recurring Pay Stub Amendment';
		$current_company = $this->currentCompany;

		if ( isset($pay_stub_amendment_data) ) {
			if ( $pay_stub_amendment_data['start_date'] != '' ) {
				$pay_stub_amendment_data['start_date'] = TTDate::parseDateTime($pay_stub_amendment_data['start_date']);
			}
			if ( $pay_stub_amendment_data['end_date'] != '' ) {
				$pay_stub_amendment_data['end_date'] = TTDate::parseDateTime($pay_stub_amendment_data['end_date']);
			}
		
		}
		
		$rpsaf = new RecurringPayStubAmendmentFactory();

		if ( isset($id) ) {
			$rpsalf = new RecurringPayStubAmendmentListFactory();

			//$uwlf->GetByUserIdAndCompanyId($current_user->getId(), $current_company->getId() );
			$rpsalf->GetById($id);

			foreach ($rpsalf->rs as $recurring_pay_stub_amendment) {
				$rpsalf->data = (array)$recurring_pay_stub_amendment;
				$recurring_pay_stub_amendment = $rpsalf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				//$user_id = $recurring_pay_stub_amendment->getUser();
				$amount_type_id = 10;
				if ( $recurring_pay_stub_amendment->getPercentAmount() > 0 ) {
					$amount_type_id = 20;
				}

				$pay_stub_amendment_data = array(
					'id' => $recurring_pay_stub_amendment->getId(),
					'company_id' => $recurring_pay_stub_amendment->getCompany(),

					'status_id'	=> $recurring_pay_stub_amendment->getStatus(),

					'name' => $recurring_pay_stub_amendment->getName(),
					'description' => $recurring_pay_stub_amendment->getDescription(),

					'start_date' => date('Y-m-d', $recurring_pay_stub_amendment->getStartDate()),
					'end_date' => date('Y-m-d', $recurring_pay_stub_amendment->getEndDate()),
					'frequency_id' => $recurring_pay_stub_amendment->getFrequency(),

					'user_ids' => $recurring_pay_stub_amendment->getUser(),

					'type_id' => $recurring_pay_stub_amendment->getType(),
					'pay_stub_entry_name_id' => $recurring_pay_stub_amendment->getPayStubEntryNameId(),

					'amount_type_id' => $amount_type_id,

					'rate' => $recurring_pay_stub_amendment->getRate(),
					'units' => $recurring_pay_stub_amendment->getUnits(),
					'amount' => $recurring_pay_stub_amendment->getAmount(),

					'percent_amount' => $recurring_pay_stub_amendment->getPercentAmount(),
					'percent_amount_entry_name_id' => $recurring_pay_stub_amendment->getPercentAmountEntryNameId(),

					'ps_amendment_description' => $recurring_pay_stub_amendment->getPayStubAmendmentDescription(),

					'created_date' => $recurring_pay_stub_amendment->getCreatedDate(),
					'created_by' => $recurring_pay_stub_amendment->getCreatedBy(),
					'updated_date' => $recurring_pay_stub_amendment->getUpdatedDate(),
					'updated_by' => $recurring_pay_stub_amendment->getUpdatedBy(),
					'deleted_date' => $recurring_pay_stub_amendment->getDeletedDate(),
					'deleted_by' => $recurring_pay_stub_amendment->getDeletedBy()
				);
			}
		} else {
			if ( empty($pay_stub_amendment_data['start_date']) ) {
				$pay_stub_amendment_data['start_date'] = date('Y-m-d', TTDate::getTime());
			}
		}
		
		//Select box options;
		$status_options_filter = array(50,60);
		/*
		if ( isset($recurring_pay_stub_amendment) AND $recurring_pay_stub_amendment->getStatus() == 55 ) {
			$status_options_filter = array(55);
		} elseif ( isset($recurring_pay_stub_amendment) AND $recurring_pay_stub_amendment->getStatus() == 52 ) {
			$status_options_filter = array(52);
		}
		*/
		$status_options = Option::getByArray( $status_options_filter, $rpsaf->getOptions('status') );
		$pay_stub_amendment_data['status_options'] = $status_options;

		$frequency_options = $rpsaf->getOptions('frequency');
		$pay_stub_amendment_data['frequency_options'] = $frequency_options;

		$percent_amount_options = $rpsaf->getOptions('percent_amount');
		$pay_stub_amendment_data['percent_amount_options'] = $percent_amount_options;

		$pay_stub_amendment_data['type_options'] = $rpsaf->getOptions('type');

		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $current_company->getId() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$net_pay_psea_id = $pseal_obj = $pseallf->getCurrent()->getTotalNetPay();
		}

		//$psenlf = new PayStubEntryNameListFactory();

		$psealf = new PayStubEntryAccountListFactory();
		$pay_stub_amendment_data['pay_stub_entry_name_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50,60,65) );
		$pay_stub_amendment_data['percent_amount_entry_name_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,60,65) );
		if ( isset($net_pay_psea_id) ) {
			unset($pay_stub_amendment_data['percent_amount_entry_name_options'][$net_pay_psea_id]);
		}

		
		$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		$user_options = Misc::prependArray( array( -1 => _('-- ALL --')), $user_options );
		$pay_stub_amendment_data['user_options'] = $user_options;
		
		$viewData['pay_stub_amendment_data'] = $pay_stub_amendment_data;
		$viewData['rpsaf'] = $rpsaf;

        return view('pay_stub_amendment/EditRecurringPayStubAmendment', $viewData);

    }

	public function recalculate($pay_stub_amendment_id){
		if(empty($pay_stub_amendment_id)){
			return false;
		}

		//Debug::setVerbosity(11);
		$rpsalf = new RecurringPayStubAmendmentListFactory();
		$rpsalf->getById( $pay_stub_amendment_id );
		if ( $rpsalf->getRecordCount() > 0 ) {
			$rpsa_obj = $rpsalf->getCurrent();
			$rpsa_obj->createPayStubAmendments();
		}

		return redirect(URLBuilder::getURL( NULL, '/payroll/recurring_pay_stub_amendment'));

	}

	public function submit(Request $request){
		$rpsaf = new RecurringPayStubAmendmentFactory();
		$current_company = $this->currentCompany;

		$pay_stub_amendment_data = $request->pay_stub_amendment_data;

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rpsaf->setId($pay_stub_amendment_data['id']);
		$rpsaf->setCompany( $current_company->getId() );

		$rpsaf->setStatus( $pay_stub_amendment_data['status_id'] );

		$rpsaf->setName( $pay_stub_amendment_data['name'] );
		$rpsaf->setDescription( $pay_stub_amendment_data['description'] );

		$rpsaf->setStartDate( TTDate::parseDateTime($pay_stub_amendment_data['start_date']) );
		if ( $pay_stub_amendment_data['end_date'] != '' ) {
			$rpsaf->setEndDate( TTDate::parseDateTime($pay_stub_amendment_data['end_date']) );
		}
		$rpsaf->setFrequency( $pay_stub_amendment_data['frequency_id'] );

		$rpsaf->setPayStubEntryNameId($pay_stub_amendment_data['pay_stub_entry_name_id']);

		$rpsaf->setType( $pay_stub_amendment_data['type_id'] );

		if ( $pay_stub_amendment_data['type_id'] == 10 ) {
			Debug::Text('Fixed Amount!', __FILE__, __LINE__, __METHOD__,10);
			$rpsaf->setRate($pay_stub_amendment_data['rate']);
			$rpsaf->setUnits($pay_stub_amendment_data['units']);
			if ( isset($pay_stub_amendment_data['amount']) ) {
				$rpsaf->setAmount($pay_stub_amendment_data['amount']);
			}
		} else {
			Debug::Text('Percent Amount!', __FILE__, __LINE__, __METHOD__,10);
			$rpsaf->setPercentAmount($pay_stub_amendment_data['percent_amount']);
			$rpsaf->setPercentAmountEntryNameID($pay_stub_amendment_data['percent_amount_entry_name_id']);
		}

		$rpsaf->setPayStubAmendmentDescription($pay_stub_amendment_data['ps_amendment_description']);

		if ( $rpsaf->isValid() ) {
			$rpsaf->Save(FALSE);

			if ( isset($pay_stub_amendment_data['user_ids']) ) {
				$rpsaf->setUser( $pay_stub_amendment_data['user_ids'] );
			} else {
				$rpsaf->setUser( array() );
			}

			$rpsaf->Save();

			return redirect(URLBuilder::getURL( NULL, '/payroll/recurring_pay_stub_amendment'));

		}
	}

}


?>