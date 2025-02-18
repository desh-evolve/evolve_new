<?php

/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 2095 $
 * $Id: Sort.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * Description of EmployeeDetailReport
 *
 * @author Thusitha
 */
class EmployeeDetailReport extends Report {

        function __construct() {
                $this->title = TTi18n::getText('TimeSheet Detail Report');
                $this->file_name = 'timesheet_detail_report';

                parent::__construct();

                return TRUE;
        }
        
        
                protected function _checkPermissions( $user_id, $company_id ) {
            if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
                            AND $this->getPermissionObject()->Check('report','view_timesheet_summary', $user_id, $company_id ) ) { //Piggyback on timesheet summary permissions.
                    return TRUE;
            } else {
                    //Debug::Text('Regular employee viewing their own timesheet...', __FILE__, __LINE__, __METHOD__,10);
                    //Regular employee printing timesheet for themselves. Force specific config options.
                    //Get current pay period from config, then overwrite it with
                    $filter_config = $this->getFilterConfig();
                    if ( isset($filter_config['time_period']['pay_period_id']) ) {
                            $pay_period_id = $filter_config['time_period']['pay_period_id'];
                    } else {
                            $pay_period_id = 0;
                    }
                    $this->setFilterConfig( array( 'include_user_id' => array($user_id), 'time_period' => array( 'time_period' => 'custom_pay_period', 'pay_period_id' => $pay_period_id ) ) );

                    return TRUE;
            }

            return FALSE;
        }
}
