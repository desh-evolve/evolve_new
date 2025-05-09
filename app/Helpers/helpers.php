<?php

use App\Models\Core\AuthorizationListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Hierarchy\HierarchyObjectTypeListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

if (!function_exists('gettimeunit_helper')) {
    function gettimeunit_helper($timeInSeconds, $default = false) {
        if (empty($timeInSeconds) || $timeInSeconds == 0 || $timeInSeconds == '00:00') {
            return $default;
        }

        // Ensure it's a number
        $seconds = (int) $timeInSeconds;

        // Convert to hh:mm format
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}


if (!function_exists('getdate_helper')) {
    function getdate_helper($type = 'date', $epoch, $default = false) {
        if(empty($epoch) || $epoch == 0){
            return $default;
        }

        if($type == 'date'){
            return date('Y-m-d', $epoch);
        }elseif($type == 'time'){
            return date('H:i', $epoch);
        }elseif($type == 'date_time' || $type == 'timestamp'){
            return date('Y-m-d H:i:s', $epoch);
        }else{
            return date('Y-m-d', $epoch);
        }
    }
}


if (!function_exists('embeddedauthorizationlist')){
    function embeddedauthorizationlist($object_type_id, $object_id)
    {
        $authorization_data = [];

        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $current_company = View::shared('current_company');

        $ulf = new UserListFactory();

        $hlf = new HierarchyListFactory();
        $hotlf = new HierarchyObjectTypeListFactory();

        $alf = new AuthorizationListFactory();
        $alf->setObjectType($object_type_id);
        $tmp_authorizing_obj = ( is_object( $alf->getObjectHandler() ) ) ? $alf->getObjectHandler()->getById( $object_id ) : FALSE;
        if ( is_object($tmp_authorizing_obj) ) {
            $authorizing_obj = $tmp_authorizing_obj->getCurrent();
        } else {
            return FALSE;
        }
        unset($alf);
        
        $user_id = $authorizing_obj->getUserObject()->getId();

        $alf = new AuthorizationListFactory();
        $alf->getByObjectTypeAndObjectId($object_type_id, $object_id);
        if ( $alf->getRecordCount() > 0 ) {
            foreach( $alf->rs as $authorization_obj) {
                $alf->data = (array)$authorization_obj;
                $authorization_obj = $alf;

                $authorization_data[] = array(
                    'id' => $authorization_obj->getId(),
                    'created_by_full_name' => $ulf->getById( $authorization_obj->getCreatedBy() )->getCurrent()->getFullName(),
                    'authorized' => $authorization_obj->getAuthorized(),
                    'created_date' => $authorization_obj->getCreatedDate(),
                    'created_by' => $authorization_obj->getCreatedBy(),
                    'updated_date' => $authorization_obj->getUpdatedDate(),
                    'updated_by' => $authorization_obj->getUpdatedBy(),
                    'deleted_date' => $authorization_obj->getDeletedDate(),
                    'deleted_by' => $authorization_obj->getDeletedBy()
                );
                $user_id = $authorization_obj->getCreatedBy();
            }
        }
        
        if ( isset($authorization_obj) AND $authorizing_obj->getStatus() == 30 ) {
            //If the object is still pending authorization, display who its waiting on...
            $hierarchy_id = $hotlf->getByCompanyIdAndObjectTypeId( $current_company->getId(), $object_type_id )->getCurrent()->getHierarchyControl();
            Debug::Text('Hierarchy ID: '. $hierarchy_id, __FILE__, __LINE__, __METHOD__,10);

            //Get Parents
            $parent_level_user_ids = $hlf->getParentLevelIdArrayByHierarchyControlIdAndUserId($hierarchy_id, $user_id );
            //Debug::Arr( $parent_level_user_ids, 'Parent Level Ids', __FILE__, __LINE__, __METHOD__,10);

            if ( $parent_level_user_ids !== FALSE AND count($parent_level_user_ids) > 0 ) {
                Debug::Text('Adding Pending Line: ', __FILE__, __LINE__, __METHOD__,10);

                foreach($parent_level_user_ids as $parent_user_id ) {
                    $created_by_full_name[] = $ulf->getById( $parent_user_id )->getCurrent()->getFullName();
                }

                $authorization_data[] = array(
                    'id' => NULL,
                    'created_by_full_name' => implode('<br>', $created_by_full_name),
                    'authorized' => NULL,
                    'created_date' => NULL,
                    'created_by' => NULL
                );

            }
        }

        $viewData['authorization_data'] = $authorization_data;
        
        return view('authorization/EmbeddedAuthorizationList');
    }
}

if (!function_exists('embeddedmessagelist')){
    function embeddedmessagelist($object_type_id, $object_id, $object_user_id = FALSE, $height = 250)
    {

        //urlbuilder script="../message/EmbeddedMessageList.php" values="object_type_id=10,object_id=$default_schedule_control_id" merge="FALSE"}
        $url = URLBuilder::getURL( array('object_type_id' => $object_type_id, 'object_id' => $object_id, 'object_user_id' => $object_user_id), Environment::getBaseURL().'/user/embedded_messages' );
        //$retval = '<iframe style="width:100%; height:'.$height.'px; border: 0px" id="MessageFactory" name="MessageFactory" src="'.$url.'#form_start"></iframe>';
        $retval = '<iframe style="width:100%; height:'.$height.'px; border: 5px" id="MessageFactory" name="MessageFactory" src="'.$url.'"></iframe>';

        return $retval;
    }
}