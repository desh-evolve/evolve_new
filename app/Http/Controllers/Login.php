<?php

namespace App\Http\Controllers;

use App\Models\Company\CompanyListFactory;
use App\Models\Core\Authentication;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\StationFactory;
use App\Models\Core\StationListFactory;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use App\Models\Core\Validator;
use Illuminate\Support\Facades\View;

class Login extends Controller
{
    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $authenticate=FALSE;
        
    }

    public function index()
    {
        extract	(FormVariables::GetVariables(
                array	(
                    'action',
                    'user_name',
                    'password',
                    'password_reset',
                    'language',
                ) 
            )
        );

        $validator = new Validator();

        View::share('user_name', $user_name);
        View::share('password', $password);
        //View::share('password_reset', $password_reset);

        View::share('validator', $validator);

        return view('login');
    }

    public function login(){
        $user_name = $_POST['user_name'];
        $password = $_POST['password'];

        $validator = new Validator();

        //Debug::setVerbosity( 11 );
        Debug::Text('User Name: '. $user_name, __FILE__, __LINE__, __METHOD__,10);
        $authentication = new Authentication();
        echo 'auth check';
        print_r($authentication->Login($user_name, $password));
        echo '<br>';
        exit;
        if ( $authentication->Login($user_name, $password) ) {
            echo '1<br>';
            $authentication->Check();
            $language = 'en';

            Debug::text('Login Language: '. $language, __FILE__, __LINE__, __METHOD__, 10);

            $clf = new CompanyListFactory();
            $clf->getByID( $authentication->getObject()->getCompany() );
            $current_company = $clf->getCurrent();
            unset($clf);

            $create_new_station = FALSE;
            //If this is a new station, insert it now.
            if ( isset( $_COOKIE['StationID'] ) ) {
                echo '2<br>';
                Debug::text('Station ID Cookie found! '. $_COOKIE['StationID'], __FILE__, __LINE__, __METHOD__, 10);

                $slf = new StationListFactory();
                $slf->getByStationIdandCompanyId( $_COOKIE['StationID'], $current_company->getId() );
                $current_station = $slf->getCurrent();
                unset($slf);

                if ( $current_station->isNew() ) {
                    echo '3<br>';
                    Debug::text('Station ID is NOT IN DB!! '. $_COOKIE['StationID'], __FILE__, __LINE__, __METHOD__, 10);
                    $create_new_station = TRUE;
                }
            } else {
                echo '4<br>';
                $create_new_station = TRUE;
            }

            if ( $create_new_station == TRUE ) {
                echo '5<br>';
                //Insert new station
                $sf = new StationFactory();

                $sf->setCompany( $current_company->getId() );
                $sf->setStatus('ENABLED');
                $sf->setType('PC');
                $sf->setSource( $_SERVER['REMOTE_ADDR'] );
                $sf->setStation();
                $sf->setDescription( substr( $_SERVER['HTTP_USER_AGENT'], 0, 250) );
                if ( $sf->Save(FALSE) ) {
                    echo '6<br>';
                    $sf->setCookie();
                }
            }

            //Redirect::Page( URLBuilder::getURL( NULL, 'index.php' ) );
            Redirect::Page( URLBuilder::getURL( NULL, 'dashboard.php' ) );
        } else {
            echo '7<br>';
            $error_message = TTi18n::gettext('User Name or Password is incorrect');

            //Get company status from user_name, so we can display messages for ONHOLD/Cancelled accounts.
            $clf = new CompanyListFactory();
            $clf->getByUserName( $user_name );
            if ( $clf->getRecordCount() > 0 ) {
                echo '8<br>';
                $c_obj = $clf->getCurrent();
                if ( $c_obj->getStatus() == 20 ) {
                    echo '9<br>';
                    $error_message = TTi18n::gettext('Sorry, your company\'s account has been placed ON HOLD, please contact customer support immediately');
                } elseif ( $c_obj->getStatus() == 30 ) {
                    echo '10<br>';
                    $error_message = TTi18n::gettext('Sorry, your company\'s account has been CANCELLED, please contact customer support if you believe this is an error');
                }
            }

            $validator->isTrue('user_name',FALSE, $error_message );
        }

        print_r('hellow');
        exit;
    }

}