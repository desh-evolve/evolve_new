<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');


$cps = new CalculatePayStub();

$cps->setUser(902);
$cps->setPayPeriod(59);
$cps->calculateAllowance();