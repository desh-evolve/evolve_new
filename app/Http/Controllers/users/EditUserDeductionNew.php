<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyDeductionFactory;
use App\Models\Company\CompanyDeductionListFactory;
use App\Models\Company\CompanyFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Option;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserDeductionFactory;
use App\Models\Users\UserDeductionListFactory;
use App\Models\Users\UserListFactory;
use Faker\Provider\ar_EG\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserDeductionNew extends Controller
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

        // if ( !$permission->Check('user_tax_deduction','enabled')
        //         OR !( $permission->Check('user_tax_deduction','edit') OR $permission->Check('user_tax_deduction','edit_own') OR $permission->Check('user_tax_deduction','add') ) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

    }


    public function index(Request $request, $id = null, $company_deduction_id = null)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        $viewData['title'] = 'Employee Tax / Deduction';

        $cf = new CompanyFactory();
        $cdf = new CompanyDeductionFactory();
        $udf = new UserDeductionFactory();

        $total_amount = 0;
        $data = [];

		if ( isset($company_deduction_id) AND $company_deduction_id != '' ) {
			Debug::Text('Mass User Deduction Edit!', __FILE__, __LINE__, __METHOD__,10);

			//Get all employees assigned to this company deduction.
			$cdlf = new CompanyDeductionListFactory();
			$cdlf->getByCompanyIdAndId( $current_company->getId(), $company_deduction_id );

			Debug::Text('Company Deduction Records: '. $cdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

            if ( $cdlf->getRecordCount() > 0 ) {

				foreach( $cdlf->rs as $cd_obj ) {
                    $cdlf->data = (array)$cd_obj;
                    $cd_obj = $cdlf;

					$province_options = $cf->getOptions('province', $cd_obj->getCountry() );
					$tmp_district_options = $cf->getOptions('district', $cd_obj->getCountry() );

					$district_options = array();

					if ( isset($tmp_district_options[$cd_obj->getProvince()]) ) {
						$district_options = $tmp_district_options[$cd_obj->getProvince()];
					}
					unset($tmp_district_options);

                    $user_id = $cd_obj->getUser();

					if ( !isset($data['users']) ) {
						$data['users'] = NULL;
					}



					$data = array(
									'id' => $cd_obj->getId(),
									'company_id' => $cd_obj->getCompany(),

									'status_id' => $cd_obj->getStatus(),
									'status' => Option::getByKey( $cd_obj->getStatus(), $cd_obj->getOptions('status') ),

									'type_id' => $cd_obj->getType(),
									'type' => Option::getByKey( $cd_obj->getType(), $cd_obj->getOptions('type') ),

									'name' => $cd_obj->getName(),

									'combined_calculation_id' => $cd_obj->getCombinedCalculationId(),
									'calculation_id' => $cd_obj->getCalculation(),
									'calculation' => Option::getByKey( $cd_obj->getCalculation(), $cd_obj->getOptions('calculation') ),

									'country_id' => $cd_obj->getCountry(),
									'country' => Option::getByKey( $cd_obj->getCountry(), $cd_obj->getOptions('country') ),

									'province_id' => $cd_obj->getProvince(),
									'province' => Option::getByKey( $cd_obj->getProvince(), $province_options ),

									'district_id' => $cd_obj->getDistrict(),
									'district' => Option::getByKey( $cd_obj->getDistrict(), $district_options ),

									'company_value1' => $cd_obj->getCompanyValue1(),
									'company_value2' => $cd_obj->getCompanyValue2(),

									'default_user_value1' => $cd_obj->getUserValue1(),
									'default_user_value2' => $cd_obj->getUserValue2(),
									'default_user_value3' => $cd_obj->getUserValue3(),
									'default_user_value4' => $cd_obj->getUserValue4(),
									'default_user_value5' => $cd_obj->getUserValue5(),
									'default_user_value6' => $cd_obj->getUserValue6(),
									'default_user_value7' => $cd_obj->getUserValue7(),
									'default_user_value8' => $cd_obj->getUserValue8(),
									'default_user_value9' => $cd_obj->getUserValue9(),
									'default_user_value10' => $cd_obj->getUserValue10(),

									'users' => $data['users'],
								);



						$user_ids = $cd_obj->getUser();

						Debug::Text('Assigned Users: '. count($user_ids), __FILE__, __LINE__, __METHOD__,10);
						if ( is_array($user_ids) AND count($user_ids) > 0 ) {
							//Get User deduction data for each user.
							$udlf = new UserDeductionListFactory();
							$udlf->getByUserIdAndCompanyDeductionId( $user_ids, $cd_obj->getId() );

							if ( $udlf->getRecordCount() > 0 ) {
								//Get deduction data for each user.
								//When ever we add/subtract users to/from a company dedution, the user deduction rows are handled then.
								//So we don't need to worry about new users at all here.
								foreach( $udlf->rs as $ud_obj ) {
                                    $udlf->data = (array)$cd_obj;
                                    $cd_obj = $udlf;

									//Use Company Deduction values as default.
									if ( $ud_obj->getUserValue1() === FALSE ) {
										$user_value1 = $cd_obj->getUserValue1();
									} else {
										$user_value1 = $ud_obj->getUserValue1();
									}
									if ( $ud_obj->getUserValue2() === FALSE ) {
										$user_value2 = $cd_obj->getUserValue2();
									} else {
										$user_value2 = $ud_obj->getUserValue2();
									}
									if ( $ud_obj->getUserValue3() === FALSE ) {
										$user_value3 = $cd_obj->getUserValue3();
									} else {
										$user_value3 = $ud_obj->getUserValue3();
									}
									if ( $ud_obj->getUserValue4() === FALSE ) {
										$user_value4 = $cd_obj->getUserValue4();
									} else {
										$user_value4 = $ud_obj->getUserValue4();
									}
									if ( $ud_obj->getUserValue5() === FALSE ) {
										$user_value5 = $cd_obj->getUserValue5();
									} else {
										$user_value5 = $ud_obj->getUserValue5();
									}


									$data['users'][$ud_obj->getUser()] = array(
														'id' => $ud_obj->getId(),
														'user_id' => $ud_obj->getUser(),
														'user_full_name' => $ud_obj->getUserObject()->getFullName(TRUE),
                                                        /*ARSP ADD THIS NEW CODE FOR GET THE EMPOYEE NUMBER */
                                                        'employee_number'=> $ud_obj->getUserObject()->getEmployeeNumber(),

														'user_value1' => $user_value1,
														'user_value2' => $user_value2,
														'user_value3' => $user_value3,
														'user_value4' => $user_value4,
														'user_value5' => $user_value5,
														'user_value6' => $ud_obj->getUserValue6(),
														'user_value7' => $ud_obj->getUserValue7(),
														'user_value8' => $ud_obj->getUserValue8(),
														'user_value9' => $ud_obj->getUserValue9(),
														'user_value10' => $ud_obj->getUserValue10(),
														);

                                                        //ARSP EDIT--> ADD NEW CODE FOR GET THE TOTAL VALUE OF THE INCREMENT OR DEDUCTION
                                                        $total_amount  = $total_amount + (float)$data['users'][$ud_obj->getUser()]['user_value1'];


								}
							}
						}
				}
			}


			// print_r($data);
		} else {
			if ( isset($id) ) {
				Debug::Text('ID Passed', __FILE__, __LINE__, __METHOD__,10);

				//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
				$hlf = new HierarchyListFactory();
				$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

				$udlf = new UserDeductionListFactory();
				$udlf->getByCompanyIdAndId( $current_company->getID(), $id );

				foreach ($udlf->rs as $ud_obj) {
                    $udlf->data = (array)$ud_obj;
                    $ud_obj = $udlf;

                    $ulf = new UserListFactory();
					$user_obj = $ulf->getByIdAndCompanyId( $ud_obj->getUser(), $current_company->getId() )->getCurrent();

                    if ( is_object($user_obj) ) {
						$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
						$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

						if ( $permission->Check('user_tax_deduction','edit')
								OR ( $permission->Check('user_tax_deduction','edit_own') AND $is_owner === TRUE )
								OR ( $permission->Check('user_tax_deduction','edit_child') AND $is_child === TRUE ) ) {

							//Get Company Deduction info
							$cd_obj = $ud_obj->getCompanyDeductionObject();

							$province_options = $cf->getOptions('province', $cd_obj->getCountry() );
							$tmp_district_options = $cf->getOptions('district', $cd_obj->getCountry() );
							$district_options = array();
							if ( isset($tmp_district_options[$cd_obj->getProvince()]) ) {
								$district_options = $tmp_district_options[$cd_obj->getProvince()];
							}
							unset($tmp_district_options);

							//Use Company Deduction values as default.
							if ( $ud_obj->getUserValue1() === FALSE ) {
								$user_value1 = $cd_obj->getUserValue1();
							} else {
								$user_value1 = $ud_obj->getUserValue1();
							}
							if ( $ud_obj->getUserValue2() === FALSE ) {
								$user_value2 = $cd_obj->getUserValue2();
							} else {
								$user_value2 = $ud_obj->getUserValue2();
							}
							if ( $ud_obj->getUserValue3() === FALSE ) {
								$user_value3 = $cd_obj->getUserValue3();
							} else {
								$user_value3 = $ud_obj->getUserValue3();
							}
							if ( $ud_obj->getUserValue4() === FALSE ) {
								$user_value4 = $cd_obj->getUserValue4();
							} else {
								$user_value4 = $ud_obj->getUserValue4();
							}
							if ( $ud_obj->getUserValue5() === FALSE ) {
								$user_value5 = $cd_obj->getUserValue5();
							} else {
								$user_value5 = $ud_obj->getUserValue5();
							}

							$data = array(
												'id' => $ud_obj->getId(),
												'user_id' => $ud_obj->getUser(),
												'company_id' => $cd_obj->getCompany(),

                                                'company_deduction_id' => $ud_obj->getCompanyDeduction(),

												'status_id' => $cd_obj->getStatus(),
												'status' => Option::getByKey( $cd_obj->getStatus(), $cd_obj->getOptions('status') ),

												'type_id' => $cd_obj->getType(),
												'type' => Option::getByKey( $cd_obj->getType(), $cd_obj->getOptions('type') ),

												'name' => $cd_obj->getName(),

												'combined_calculation_id' => $cd_obj->getCombinedCalculationId(),
												'calculation_id' => $cd_obj->getCalculation(),
												'calculation' => Option::getByKey( $cd_obj->getCalculation(), $cd_obj->getOptions('calculation') ),

												'country_id' => $cd_obj->getCountry(),
												'country' => Option::getByKey( $cd_obj->getCountry(), $cd_obj->getOptions('country') ),

												'province_id' => $cd_obj->getProvince(),
												'province' => Option::getByKey( $cd_obj->getProvince(), $province_options ),

												'district_id' => $cd_obj->getDistrict(),
												'district' => Option::getByKey( $cd_obj->getDistrict(), $district_options ),

												'company_value1' => $cd_obj->getCompanyValue1(),
												'company_value2' => $cd_obj->getCompanyValue2(),

												'user_value1' => $user_value1,
												'user_value2' => $user_value2,
												'user_value3' => $user_value3,
												'user_value4' => $user_value4,
												'user_value5' => $user_value5,
												'user_value6' => $ud_obj->getUserValue6(),
												'user_value7' => $ud_obj->getUserValue7(),
												'user_value8' => $ud_obj->getUserValue8(),
												'user_value9' => $ud_obj->getUserValue9(),
												'user_value10' => $ud_obj->getUserValue10(),

												'default_user_value1' => $cd_obj->getUserValue1(),
												'default_user_value2' => $cd_obj->getUserValue2(),
												'default_user_value3' => $cd_obj->getUserValue3(),
												'default_user_value4' => $cd_obj->getUserValue4(),
												'default_user_value5' => $cd_obj->getUserValue5(),
												'default_user_value6' => $cd_obj->getUserValue6(),
												'default_user_value7' => $cd_obj->getUserValue7(),
												'default_user_value8' => $cd_obj->getUserValue8(),
												'default_user_value9' => $cd_obj->getUserValue9(),
												'default_user_value10' => $cd_obj->getUserValue10(),

												'created_date' => $ud_obj->getCreatedDate(),
												'created_by' => $ud_obj->getCreatedBy(),
												'updated_date' => $ud_obj->getUpdatedDate(),
												'updated_by' => $ud_obj->getUpdatedBy(),
												'deleted_date' => $ud_obj->getDeletedDate(),
												'deleted_by' => $ud_obj->getDeletedBy()
								);
						} else {
							$permission->Redirect( FALSE ); //Redirect

						}
					}
				}
			} else {
				Debug::Text('Adding... ', __FILE__, __LINE__, __METHOD__,10);

                // Get user_id from request when adding
                $user_id = $request->input('user_id');

				//Adding User Deductions...
				$data['add'] = 1;
				$data['user_id'] = $user_id;


				//Get all Company Deductions for drop down box.
				$cdlf = new CompanyDeductionListFactory();
				$data['deduction_options'] = $cdlf->getByCompanyIdAndStatusIdArray( $current_company->getId(), 10, FALSE);

				$udlf = new UserDeductionListFactory();
				$udlf->getByCompanyIdAndUserId( $current_company->getId(), $user_id );
				if ($udlf->getRecordCount() > 0 ) {
					//Remove deductions from select box that are already assigned to user.
					$deduction_ids = array_keys($data['deduction_options']);
					foreach( $udlf->rs as $ud_obj) {
                        $udlf->data = (array)$ud_obj;
                        $ud_obj = $udlf;

						if ( in_array( $ud_obj->getCompanyDeduction(), $deduction_ids ) ) {
							unset($data['deduction_options'][$ud_obj->getCompanyDeduction()]);
						}
					}
				}
			}

			//Get user full name
			$ulf = new UserListFactory();
			$ulf->getByIdAndCompanyId( $data['user_id'], $current_company->getId() );
			if ( $ulf->getRecordCount() > 0 ) {
				$data['user_full_name'] = $ulf->getCurrent()->getFullName();
			}
		}

		//Select box options;
		$data['us_eic_filing_status_options'] = $cdf->getOptions('us_eic_filing_status');
		$data['federal_filing_status_options'] = $cdf->getOptions('federal_filing_status');
		$data['state_filing_status_options'] = $cdf->getOptions('state_filing_status');
		$data['state_ga_filing_status_options'] = $cdf->getOptions('state_ga_filing_status');
		$data['state_nj_filing_status_options'] = $cdf->getOptions('state_nj_filing_status');
		$data['state_nc_filing_status_options'] = $cdf->getOptions('state_nc_filing_status');
		$data['state_ma_filing_status_options'] = $cdf->getOptions('state_ma_filing_status');
		$data['state_al_filing_status_options'] = $cdf->getOptions('state_al_filing_status');
		$data['state_ct_filing_status_options'] = $cdf->getOptions('state_ct_filing_status');
		$data['state_wv_filing_status_options'] = $cdf->getOptions('state_wv_filing_status');
		$data['state_me_filing_status_options'] = $cdf->getOptions('state_me_filing_status');
		$data['state_de_filing_status_options'] = $cdf->getOptions('state_de_filing_status');
		$data['state_dc_filing_status_options'] = $cdf->getOptions('state_dc_filing_status');
		$data['state_la_filing_status_options'] = $cdf->getOptions('state_la_filing_status');

		$data['js_arrays'] = $cdf->getJavaScriptArrays();

        $viewData['data'] = $data;
        $viewData['total_amount'] = $total_amount;

        $company_deduction_id = $data['company_deduction_id'] ?? $company_deduction_id;
        $viewData['company_deduction_id'] = $company_deduction_id;

        $user_id = $data['user_id'] ?? $user_id;
        $viewData['user_id'] = $user_id;
        $viewData['udf'] = $udf;

        // dd($viewData);

        return view('users.EditUserDeduction', $viewData);

    }


    public function save(Request $request)
    {
        $user_id = $request->input('user_id');
        $company_deduction_id = $request->input('company_deduction_id', '');
        $user_data = $request->all();
        $data = $request->all();
        // dd($request->all());

        $udf = new UserDeductionFactory();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		$udf->StartTransaction();

		if ( $company_deduction_id != '' ) {
			//Debug::setVerbosity(11);
			Debug::Text('Mass User Update', __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr($data, 'All User Data', __FILE__, __LINE__, __METHOD__,10);

			$redirect = 0;

			if ( isset($data['users']) AND is_array($data['users']) AND count($data['users']) > 0 ) {

                foreach( $data['users'] as  $user_id => $user_data ) {
					Debug::Text('Editing Deductions for User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
					//Debug::Arr($user_data, 'Specific User Data', __FILE__, __LINE__, __METHOD__,10);
					if ( isset($user_data['id']) AND $user_data['id'] > 0 ) {
						$udf->setId( $user_data['id'] );
					}
					$udf->setUser( $user_data['user_id'] );

					if ( isset($user_data['user_value1']) ) {
						$udf->setUserValue1( $user_data['user_value1'] );
					}
					if ( isset($user_data['user_value2']) ) {
						$udf->setUserValue2( $user_data['user_value2'] );
					}
					if ( isset($user_data['user_value3']) ) {
						$udf->setUserValue3( $user_data['user_value3'] );
					}
					if ( isset($user_data['user_value4']) ) {
						$udf->setUserValue4( $user_data['user_value4'] );
					}
					if ( isset($user_data['user_value5']) ) {
						$udf->setUserValue5( $user_data['user_value5'] );
					}
					if ( isset($user_data['user_value6']) ) {
						$udf->setUserValue6( $user_data['user_value6'] );
					}
					if ( isset($user_data['user_value7']) ) {
						$udf->setUserValue7( $user_data['user_value7'] );
					}
					if ( isset($user_data['user_value8']) ) {
						$udf->setUserValue8( $user_data['user_value8'] );
					}
					if ( isset($user_data['user_value9']) ) {
						$udf->setUserValue9( $user_data['user_value9'] );
					}
					if ( isset($user_data['user_value10']) ) {
						$udf->setUserValue10( $user_data['user_value10'] );
					}

					if ( $udf->isValid() ) {
						$udf->Save();
					} else {
						$redirect++;
					}
				}

				if ( $redirect == 0 ) {
					$udf->CommitTransaction();

					// Redirect::Page( URLBuilder::getURL( NULL, '../company/CompanyDeductionList.php') );
                    return redirect()->to(URLBuilder::getURL( NULL , '/user/tax'))->with('success', 'Employee Tax / Deduction saved successfully.');

				}
			}
		} else {
			if ( isset($data['add']) AND $data['add'] == 1 ) {
				Debug::Text('Adding Deductions', __FILE__, __LINE__, __METHOD__,10);

                if ( isset($data['deduction_ids']) AND count($data['deduction_ids']) > 0 ) {
					foreach( $data['deduction_ids'] as $deduction_id ) {
						$udf = new UserDeductionFactory();
						$udf->setUser( $data['user_id'] );
						$udf->setCompanyDeduction( $deduction_id );
						if ( $udf->isValid() ) {
							$udf->Save();
						}
					}
				}

				$udf->CommitTransaction();

				// Redirect::Page( URLBuilder::getURL( array('user_id' => $data['user_id'], 'saved_search_id' => $saved_search_id ), 'UserDeductionList.php') );
                return redirect()->to(URLBuilder::getURL(array('user_id' => $user_id) , '/user/tax'))->with('success', 'Employee Tax / Deduction saved successfully.');

			} else {

				Debug::Text('Editing Deductions', __FILE__, __LINE__, __METHOD__,10);

				$udf->setId( $data['id'] );
				$udf->setUser( $data['user_id'] );

				if ( isset($data['user_value1']) ) {
					$udf->setUserValue1( $data['user_value1'] );
				}
				if ( isset($data['user_value2']) ) {
					$udf->setUserValue2( $data['user_value2'] );
				}
				if ( isset($data['user_value3']) ) {
					$udf->setUserValue3( $data['user_value3'] );
				}
				if ( isset($data['user_value4']) ) {
					$udf->setUserValue4( $data['user_value4'] );
				}
				if ( isset($data['user_value5']) ) {
					$udf->setUserValue5( $data['user_value5'] );
				}
				if ( isset($data['user_value6']) ) {
					$udf->setUserValue6( $data['user_value6'] );
				}
				if ( isset($data['user_value7']) ) {
					$udf->setUserValue7( $data['user_value7'] );
				}
				if ( isset($data['user_value8']) ) {
					$udf->setUserValue8( $data['user_value8'] );
				}
				if ( isset($data['user_value9']) ) {
					$udf->setUserValue9( $data['user_value9'] );
				}
				if ( isset($data['user_value10']) ) {
					$udf->setUserValue10( $data['user_value10'] );
				}

				if ( $udf->isValid() ) {
					$udf->Save();

					$udf->CommitTransaction();

					// Redirect::Page( URLBuilder::getURL( array('user_id' => $data['user_id'], 'saved_search_id' => $saved_search_id ), 'UserDeductionList.php') );
                    return redirect()->to(URLBuilder::getURL(array('user_id' => $user_id) , '/user/tax'))->with('success', 'Employee Tax / Deduction saved successfully.');

				}
			}
		}
		$udf->FailTransaction();
    }


}
