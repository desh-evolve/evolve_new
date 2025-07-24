<?php


namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Redirect;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserDeductionFactory;
use App\Models\Users\UserDeductionListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;
// use App\Upload\FileUpload;
use fileupload as GlobalFileupload;

class ImportCsvFile extends Controller
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
        require_once($basePath . '/app/Upload/fileupload.class.php');
        // D:\Evolve\app\Upload\fileupload.class.php

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');
        $this->userPrefs = View::shared('current_user_prefs');
    }

    public function index()
    {
        $permission = $this->permission;
        $current_company = $this->currentCompany;

        if (
            !$permission->Check('user', 'enabled') ||
            !($permission->Check('user', 'view'))
        ) {
            $permission->Redirect(FALSE);
        }

        $viewData['title'] = TTi18n::gettext('Import Excel(CSV) File');

        extract(FormVariables::GetVariables(
            array(
                'action',
                'object_type',
                'object_id',
                'userfile'
            )
        ));

        $action = '';
        if (isset($_POST['action:submit'])) {
            $action = 'import';
        } elseif (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';

        switch ($action) {
            case 'import':
                Debug::Text('Import... Object Type: ' . $object_type, __FILE__, __LINE__, __METHOD__, 10);

                $upload = new GlobalFileupload();
                $temp_file_path = $_FILES['userfile']['tmp_name'];
                $original_file_path = $_FILES['userfile']['name'];
                $extension = strtolower(pathinfo($original_file_path, PATHINFO_EXTENSION));

                if ($extension == 'csv') {
                    $error_array = [];
                    $handle = fopen($temp_file_path, "r");
                    $x = 0;
                    $data = ['users' => [], 'id' => $object_id];
                    $company_deduction_id = $object_id;
dd($file_open);
                    while (($file_open = fgetcsv($handle, 1000, ",")) !== false) {
                        $emp_no = $file_open[0];
                        $string_deduction = $file_open[1];
                        $deduction = intval(str_replace(',', '', $string_deduction));

                        if ($x != 0) {
                            $ulf = new UserListFactory();
                            $ulf->getByEmployeeNumber($emp_no);
                            $check_user = $ulf->getRecordCount();

                            if ($check_user == 1) {
                                $user = $ulf->getCurrent();
                                $user_id = $user->getId();

                                $udlf = new UserDeductionListFactory();
                                $udlf->getByUserIdAndCompanyDeductionId($user_id, $object_id);
                                $check_user_deduction = $udlf->getRecordCount();

                                if ($check_user_deduction == 1) {
                                    $user_deduction = $udlf->getCurrent();
                                    $user_deduction_id = $user_deduction->getId();

                                    $data['users'][$user_id] = [
                                        'id' => $user_deduction_id,
                                        'user_id' => $user_id,
                                        'user_value1' => $deduction
                                    ];
                                } else {
                                    if (!empty($emp_no)) {
                                        $error_array['not_assign_user'][$emp_no] = $user->getFullName();
                                    }
                                }
                            } else {
                                if (!empty($emp_no) && $emp_no > 0) {
                                    $error_array['invalid_employee_no'][] = $emp_no;
                                }
                            }
                        }
                        $x++;
                    }
                    fclose($handle);

                    // Save function
                    $udf = new UserDeductionFactory();
                    $udf->StartTransaction();

                    if (!empty($company_deduction_id) && isset($data['users']) && is_array($data['users']) && count($data['users']) > 0) {
                        foreach ($data['users'] as $user_id => $user_data) {
                            Debug::Text('Editing Deductions for User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10);
                            if (isset($user_data['id']) && $user_data['id'] > 0) {
                                $udf->setId($user_data['id']);
                            }
                            $udf->setUser($user_data['user_id']);

                            if (isset($user_data['user_value1'])) {
                                $udf->setUserValue1($user_data['user_value1']);
                            }
                            // Add other user_value fields if needed
                            for ($i = 2; $i <= 10; $i++) {
                                if (isset($user_data['user_value' . $i])) {
                                    $udf->{'setUserValue' . $i}($user_data['user_value' . $i]);
                                }
                            }

                            if ($udf->isValid()) {
                                $udf->Save();
                            } else {
                                $redirect++;
                            }
                        }

                        if ($redirect == 0) {
                            $udf->CommitTransaction();
                        } else {
                            $udf->FailTransaction();
                        }
                    } else {
                        $udf->FailTransaction();
                    }

                    if (count($error_array) == 0) {
                        $viewData['success'] = TTi18n::gettext('Successfully Import.');
                    } else {
                        $viewData['error'] = TTi18n::gettext('Error !!');
                    }
                } else {
                    $viewData['error'] = TTi18n::gettext('Please Select CSV File Format eg:-example.csv.');
                    $error_array['invalid_format'] = TTi18n::gettext('Invalid File Format !! Please Select CSV File Format eg:-example.csv.');
                }

                $viewData['error_array'] = $error_array;
                break;

            default:
                $viewData['object_type'] = $object_type;
                $viewData['object_id'] = $object_id;
                $viewData['error_array'] = [];
                break;
        }

        $viewData['object_type'] = $object_type;
        $viewData['object_id'] = $object_id;

        return view('upload.ImportCsvFile', $viewData);
    }
}