<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('TESTSUITE_PATH', dirname(__FILE__) . '/../../vendor/simpletest/simpletest/');
define('TEST_PATH', dirname(__FILE__) . '/../');

require_once(TESTSUITE_PATH . 'autorun.php');
require_once('../index.php');
