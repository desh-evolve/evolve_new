
<?php



require_once('../../includes/global.inc.php');


if(isset($_POST['post_data']))
{    
    //un serialize
    $post_data = unserialize(stripslashes($_POST['post_data'])); 

    if($post_data != NULL)
    {
        //Set Payperiod Schedule Maximum Shift Time -- >evolvepayroll/interface/payperiod/EditPayPeriodSchedule.php
        //If this  ( Maximum Shift Time < Total Time Of day) It will be error so we need to set maximum value.
        //we need to update the Maximum Shift time from  "pay_period_schedule" Table ( column name = maximum_shift_time)
        
        //ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON 2014.01.27
        //$maximum_shift_time = 57600; //16 hours 16*60*60
        
        //ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON 2014.01.27
        //if(updateMaximumShiftTime($maximum_shift_time))
        //{
            $added_local_server_Id = array();
            foreach ($post_data as $value)
            {
                $id = $value['id'];
                $employee_id = $value['employee_id'];
                $punchtime = $value['punchtime'];
                $inandout = $value['inandout'];
                $in_date_time = $value['in_date_time'];
                $in_date_time = $value['in_date_time'];
                //$accessdb_id = $value['accessdb_id'];
                //$accessdb_record_id = $value['accessdb_record_id'];
                //$flag_remote_server = $value['flag_remote_server'];            

                $explode = explode(" ",$punchtime);
                $date_stamp = $explode[0];
                $time_stamp = $explode[1];            
                $repeat ='0';
                $type_id = '10';
                $note = '';
                $punch_id = '';          
                
                
                if(checkPayPeriodDate($date_stamp))
                {                    
                    //get payroll user id
                    $user_id = getUserId($employee_id);                
                    if($user_id != NULL)
                    {
                        //Check this user assigned or not in "pay period schedule" 
                        $pay_period_schedule_user = chechPayPeriodScheduleUser($user_id);
                        if($pay_period_schedule_user != NULL)
                        {
                            //Check Flag
                            $check_flag = checkRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout);
                            if($check_flag == TRUE)
                            {
                                $branch_id = getBranchId($user_id);
                                $department_id = getDepartmentId($user_id); 
                                //$user_full_name = $this->getUserFullName($employee_id);

                                //in
                                if($inandout == 1)
                                {
                                    $pc_data = array();
                                    $pc_data['time_stamp'] = $time_stamp;
                                    $pc_data['date_stamp'] = $date_stamp;
                                    $pc_data['repeat'] = $repeat;        
                                    $pc_data['type_id'] = $type_id;        
                                    $pc_data['status_id'] = '10';// in = 10       
                                    $pc_data['branch_id'] = $branch_id;       
                                    $pc_data['department_id'] = $department_id;       
                                    $pc_data['note'] =  $note;
                                    $pc_data['punch_id'] =  $punch_id;      
                                    $pc_data['id'] =  '';     
                                    $pc_data['user_id'] = $user_id;
                                    $pc_data['user_date_id'] = '';
                                    //$pc_data['user_full_name'] = 

                                   // print_r($pc_data);
                                   // exit;

                                    $addPunch = addLocalServerPunchdata($pc_data);
                                    //sleep(3);
                                    //echo "TEST 1 :".$addPunch;
                                    //echo "</br>";
                                    //var_dump($addPunch);
                                    
                                    if($addPunch)
                                    {
                                        //insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout, 1);
                                        if(insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout, 1))
                                        {
                                            $added_local_server_Id[] =$id;
                                        }
                                    }
                                    else
                                    {
                                        //sleep(6);
                                        //echo "ERROR IN DATA ADDED !!!<br>";
                                        //insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout, -1);
                                        if(insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout, -1))
                                        {
                                            $added_local_server_Id[] =$id;
                                        }
                                        break;
                                    }                   
                                }

                                //out
                                if($inandout == 0)
                                {                                   
                                    
                                    $user_date_id  = getUserDateId($user_id, $date_stamp); 
                                    $punch_control_id = getPunchControlId($in_date_time, $user_date_id);
                                    
                                    //echo "USer Date ID = ".$user_date_id;
                                    //echo "</p>Punch Control ID = ".$punch_control_id;
                                    //exit();

                                    if($user_date_id != NULL && $punch_control_id != NULL )
                                    {
                                        $pc_data = array();
                                        $pc_data['time_stamp'] = $time_stamp;
                                        $pc_data['date_stamp'] = $date_stamp;
                                        $pc_data['repeat'] = $repeat;        
                                        $pc_data['type_id'] = $type_id;        
                                        $pc_data['status_id'] = '20';//out =20       
                                        $pc_data['branch_id'] = $branch_id;       
                                        $pc_data['department_id'] = $department_id;       
                                        $pc_data['note'] =  $note;
                                        $pc_data['punch_id'] =  $punch_id;      
                                        $pc_data['id'] =  $punch_control_id;     
                                        $pc_data['user_id'] = $user_id;
                                        $pc_data['user_date_id'] = $user_date_id;
                                        //$pc_data['user_full_name'] =    

                                        $addPunch = addLocalServerPunchdata($pc_data);
                                        //sleep(3);
                                        //echo "TEST 2 :".$addPunch;
                                        //echo "</br>";
                                        //var_dump($addPunch);
                                        if($addPunch)
                                        {
                                            //insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout);
                                            if(insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout, 1))
                                            {
                                                $added_local_server_Id[] = $id;
                                            }
                                        }
                                        else
                                        {
                                            //sleep(6);
                                            //echo "ERROR IN DATA ADDED !!!<br>";
                                            //insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout, -1);
                                            if(insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout, -1))
                                            {
                                                $added_local_server_Id[] =$id;
                                            }
                                            break;
                                        }                         
                                    }           
                                }            
                            }
                            else
                            {
                                $added_local_server_Id[] =$id;
                            }                        
                        }
                    }                    
                    
                }
                
                
                
                
                $id = '';
                $employee_id = '';
                $punchtime = '';
                $inandout = '';
                $in_date_time = '';                
                
                
                

            }
            
            //echo "**************************** ARSP FINISHED *******************************";
        //}

        //count($punch_control_id);
       // exit();
        //print_r($added_local_server_Id);
//        for($i=0;$i<count($added_local_server_Id);$i++)
//        {
//            $string_id = $added_local_server_Id[$i].' ';
//        }
        $string_added_local_server_id = implode(" ",$added_local_server_Id);
        echo $string_added_local_server_id;//Must echo here otherwise data not to be return
        return $string_added_local_server_id;
        //echo $hi = "</p>HI THIS MESSAGE FROM REMOTE A...</br>"; 
        //return $hi;
    }
}

/**
 * ARSP NOTE --> I ADDED THIS CODE FOR FIND ERROR COUNT
 * TABLE --> punch_remote_flag
 * FIELD --> flag = (-1)
 */
if(isset($_POST['error_flag_total_count']))
{    
    //un serialize
    $error_count = $_POST['error_flag_total_count']; 

    if($error_count != NULL)
    {
        $total_error_flag = getErrorFlagTotalCount();
        //var_dump($total_error_flag);
        echo $total_error_flag;
        exit();
        //echo "ARSP TEST FROM REMOTE SERVER";
        //return "test";
        
        print_r($error_count);
        exit();
    }    
}


function getBranchId($user_id)
{
    $query = "SELECT default_branch_id
              FROM users
              WHERE id = '$user_id' AND deleted = 0
             ";    
    
    if($result= mysql_query($query))
    {
        $num_rows = mysql_num_rows($result);
        
        if($num_rows == 1)
        {
            $row  = mysql_fetch_row($result);
            $branch_id = $row[0];
            
            return $branch_id;
        }
        
        else
        {
            return NULL;
        }
    }
    else
    {
        die("Mysql Query Error :".  mysql_error());
    }
}    
    
function getDepartmentId($user_id)
{
    $query = "SELECT default_department_id
              FROM users
              WHERE id = '$user_id' AND deleted = 0
             ";    
    
    if($result= mysql_query($query))
    {
        $num_rows = mysql_num_rows($result);
        
        if($num_rows == 1)
        {
            $row  = mysql_fetch_row($result);
            $department_id = $row[0];
            
            return $department_id;
        }
        
        else
        {
            return NULL;
        }
    }
    else
    {
        die("Mysql Query Error :".  mysql_error());
    }
}    
    
    



function getUserId($employee_id)
{
    $query = "SELECT id
              FROM users
              WHERE punch_machine_user_id = '$employee_id' AND deleted = 0 
             ";    
    
    if($result= mysql_query($query))
    {
        $num_rows = mysql_num_rows($result);
        
        if($num_rows == 1)
        {
            $row  = mysql_fetch_row($result);
            $user_id = $row[0];
            
            return $user_id;
        }
        
        else
        {
            return NULL;
        }

    }
    else
    {
        die("Mysql Query Error :".  mysql_error());
    }
}



require_once(Environment::getBasePath() .'includes/Interface.inc.php');


function addLocalServerPunchdata($pc_data )
{
    
    //echo "HI THIS MESSAGE FROM REMOTE B...</br>"; 
    Debug::Text('**********************', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('**********************', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('START addLocalServerPunchdata() FUNCTION FROM a.php !', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('**********************', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('**********************', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

    $punch_full_time_stamp = NULL;

    if ( isset($pc_data) ) {
            if ( $pc_data['date_stamp'] != '' AND $pc_data['time_stamp'] != '') {
                    $punch_full_time_stamp = TTDate::parseDateTime($pc_data['date_stamp'].' '.$pc_data['time_stamp']);
                    $pc_data['punch_full_time_stamp'] = $punch_full_time_stamp;
                    $pc_data['time_stamp'] = $punch_full_time_stamp;
            } else {
                    $pc_data['punch_full_time_stamp'] = NULL;
            }

            if ( $pc_data['date_stamp'] != '') {
                    $pc_data['date_stamp'] = TTDate::parseDateTime($pc_data['date_stamp']);
            }
    }

    $pcf = TTnew( 'PunchControlFactory' );
    $pf = TTnew( 'PunchFactory' );   


    Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

    $fail_transaction=FALSE;

    $pf->StartTransaction();

    //Limit it to 31 days, just in case someone makes an error entering the dates or something.
    if ( $pc_data['repeat'] > 31 ) {
            $pc_data['repeat'] = 31;
    }
    Debug::Text('Repeating Punch For: '. $pc_data['repeat'] .' Days', __FILE__, __LINE__, __METHOD__,10);

    for( $i=0; $i <= (int)$pc_data['repeat']; $i++ ) {

            $pf = TTnew( 'PunchFactory' );

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


                    if ( $pf->Save( FALSE ) == TRUE ) {
                            $pcf = TTnew( 'PunchControlFactory' );
                            $pcf->setId( $pf->getPunchControlID() );
                            $pcf->setPunchObject( $pf );

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
                                    $pcf->setJob( $pc_data['job_id'] );
                            }
                            if ( isset($pc_data['job_item_id']) ) {
                                    $pcf->setJobItem( $pc_data['job_item_id'] );
                            }
                            if ( isset($pc_data['quantity']) ) {
                                    $pcf->setQuantity( $pc_data['quantity'] );
                            }
                            if ( isset($pc_data['bad_quantity']) ) {
                                    $pcf->setBadQuantity( $pc_data['bad_quantity'] );
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
                                            //return FALSE;
                                            break;
                                    }
                            } else {
                                    Debug::Text(' bFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
                                    $fail_transaction = TRUE;
                                    //return FALSE;
                                    break;
                            }
                    } else {
                            Debug::Text(' cFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
                            $fail_transaction = TRUE;
                            //return FALSE;
                            break;
                    }
            } else {
                    Debug::Text(' dFail Transaction: ', __FILE__, __LINE__, __METHOD__,10);
                    $fail_transaction = TRUE;
                    //return FALSE;
                    break;
            }
    }
    
    Debug::Text('**************************', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('**************************', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('FINISHED ARSP FUNCTION', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('**************************', __FILE__, __LINE__, __METHOD__,10);
    Debug::Text('**************************', __FILE__, __LINE__, __METHOD__,10);
    
    if ( $fail_transaction == FALSE ) {
        //Debug::Text('FINISHED ARSP FUNCTION', __FILE__, __LINE__, __METHOD__,10);
            //$pf->FailTransaction();
            $pf->CommitTransaction();

            //Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );

            return TRUE;
            //break;
    } else {
            //Debug::Text('FINISHED ARSP FUNCTION', __FILE__, __LINE__, __METHOD__,10);
            $pf->FailTransaction();
            return FALSE;
    }


}



    function getUserDateId($user_id, $date_stamp)
    {      
        $query = "SELECT id 
                  FROM user_date
                  WHERE user_id = '$user_id' AND date_stamp = '$date_stamp' AND deleted = 0";
        
        if($result = mysql_query($query))
        {
            $num_rows = mysql_num_rows($result);
            if($num_rows == 1)
            {
                $row = mysql_fetch_row($result);                
                $user_date_id= $row[0];

                return $user_date_id;          
            }
            else
            {
                return NULL;
            }
        }
        
        else
        {
            die("Mysql Query Error :".mysql_error());
        }
    }
    
    function getPunchControlId($in_date_time, $user_date_id)
    {       
        $query = "SELECT pc.id AS punch_control_id 
                  FROM punch_control pc, punch pu
                  WHERE pc.id= pu.punch_control_id AND pc.user_date_id = '$user_date_id' AND pu.time_stamp = '$in_date_time' AND pc.deleted = 0
                 ";
        
        if($result = mysql_query($query))
        {
            $num_rows = mysql_num_rows($result);
            if($num_rows == 1)
            {
                $row = mysql_fetch_row($result);                
                $punch_control_id= $row[0];

                return $punch_control_id;          
            }
            else
            {
                return NULL;
            }
        }
        
        else
        {
            die("Mysql Query Error :".mysql_error());
        }        
    }    
    
    
    function insertRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout, $flag)
    {        
        $query = "INSERT INTO punch_remote_flag
                  (local_server_punch_id, local_server_employee_id, remote_server_user_id, punch_time, in_and_out, flag)
                  VALUES
                  ('$id', '$employee_id', '$user_id', '$punchtime', '$inandout', '$flag' ) ";
        
        if(mysql_query($query))
        {      
            return TRUE;             
        }
        else
        {
            die("Mysql Query Error :".mysql_error());
        }      
    }
    
    function checkRemoteFlag($id, $employee_id, $user_id, $punchtime, $inandout)
    {
        $query = "SELECT *
                  FROM punch_remote_flag
                  WHERE local_server_punch_id = '$id' AND local_server_employee_id = '$employee_id' AND remote_server_user_id = '$user_id' AND punch_time = '$punchtime' AND in_and_out = '$inandout' AND (flag = 1 OR flag = -1)
                  ";
        
        if($result = mysql_query($query))
        {
            if(mysql_num_rows($result) > 0)
            {
                return FALSE;             
            }
            else
            {
                return TRUE;
            }            
        }
        else
        {
            die("Mysql Query Error :".mysql_error());
        } 
                
        
    }
    
    
    
    function updateMaximumShiftTime($maximum_shift_time)
    {
        $select_query ="SELECT maximum_shift_time 
                        FROM pay_period_schedule 
                        WHERE deleted=0 AND maximum_shift_time < '$maximum_shift_time'";
        
        if($result = mysql_query($select_query))
        {
            if(mysql_num_rows($result) > 0)
            {
                $query = "UPDATE pay_period_schedule
                          SET  	maximum_shift_time ='$maximum_shift_time'
                          WHERE deleted = 0    
                         ";
                
                if(mysql_query($query))
                {      
                    return TRUE;             
                }
                else
                {
                    return FALSE;
                }
            }
            return TRUE;
            
        }

        else
        {
            return FALSE;
            //die("Mysql Query Error :".mysql_error());
        }      
    }
    
    
    
    function chechPayPeriodScheduleUser($user_id)
    {
        $query = "SELECT *
                  FROM pay_period_schedule_user
                  WHERE user_id = '$user_id'
                  ";
        
        if($result = mysql_query($query))
        {
            if(mysql_num_rows($result) > 0)
            {
                return TRUE;             
            }
            else
            {
                return FALSE;
            }            
        }
        else
        {
            return FALSE;
            //die("Mysql Query Error :".mysql_error());
        }         
        
    }    
    

    function checkPayPeriodDate($date_stamp)
    {        
        $max_query = "SELECT MAX(CAST(end_date AS date)) AS max_date
                     FROM pay_period
                     WHERE deleted=0 
                     "; 
        
        if($max_result = mysql_query($max_query))
        {
            $row = mysql_fetch_row($max_result);                
            $max_date = $row[0];
            
            if( (strtotime($date_stamp) - strtotime($max_date)) > 0)
            {
                return TRUE;
            }
            
            else
            {                
                $query = "SELECT status_id
                         FROM pay_period
                         WHERE CAST(start_date AS date) <= '$date_stamp' AND CAST(end_date AS date) >= '$date_stamp' AND deleted=0
                         ";          
                
                if($result = mysql_query($query))
                {
                    if(mysql_num_rows($result) > 0)
                    {
                        $row1 = mysql_fetch_row($result);   
                        $status_id = $row1[0];
                        
                        if($status_id == 10 || $status_id == 30)//Open Payperiod=10 , Post Adjustment = 30
                        {
                            return TRUE;      
                        }
                        else
                        {
                            return FALSE;
                        }                               
                    }
                    else
                    {
                        return FALSE;
                    }            
                }
                else
                {
                    return FALSE;
                    //die("Mysql Query Error :".mysql_error());
                }         
            }
        }

        else
        {
            return FALSE;
            //die("Mysql Query Error :".mysql_error());
        }
    }
    
    function getErrorFlagTotalCount()
    {
        $query = "SELECT *
                  FROM punch_remote_flag
                  WHERE flag = -1
                  ";
        
        if($result = mysql_query($query))
        {
            return mysql_num_rows($result);           
        }
        else
        {
            return NULL;
            //die("Mysql Query Error :".mysql_error());
        }          
    }


?>