<?php

define(STATUS_SUCCESS, 1);

// Init
require_once dirname(__FILE__).'/../../../../config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration('webservices', 'dev', true);
sfContext::createInstance($configuration);

print 'Fetch Current Mappings'. "\n";
$currentMappings = csTestMappingsHelper::getMappings();

print 'Apply Offet'. "\n";
$newMappings = csTestMappingsHelper::applyInOffset($currentMappings);

print 'Register Msisdn 0 ' .$newMappings->getActiveMsisdn(0). "\t";
$returnStatus = myVasTestHelper::registerUser($newMappings->getActiveMsisdn(0), $newMappings->getActiveNickname(0));

if ($returnStatus == STATUS_SUCCESS) {
print 'Done'. "\n";
}
else {
print 'Failed'. "\n";    
}

print 'Register Msisdn 1 ' .$newMappings->getActiveMsisdn(1). "\t";
$returnStatus = myVasTestHelper::registerUser($newMappings->getActiveMsisdn(1), $newMappings->getActiveNickname(1));

if ($returnStatus == STATUS_SUCCESS) {
print 'Done'. "\n";
}
else {
print 'Failed'. "\n";    
}

print 'Register Msisdn 2 ' .$newMappings->getActiveMsisdn(2). "\t";
$returnStatus = myVasTestHelper::registerUser($newMappings->getActiveMsisdn(2), $newMappings->getActiveNickname(2));

if ($returnStatus == STATUS_SUCCESS) {
print 'Done'. "\n";
}
else {
print 'Failed'. "\n";    
}

print 'Register Msisdn 3 ' .$newMappings->getActiveMsisdn(3). "\t";
$returnStatus = myVasTestHelper::registerUser($newMappings->getActiveMsisdn(3), $newMappings->getActiveNickname(3));

if ($returnStatus == STATUS_SUCCESS) {
print 'Done'. "\n";
}
else {
print 'Failed'. "\n";    
}

print 'Register Msisdn 4 ' .$newMappings->getActiveMsisdn(4). "\t";
$returnStatus = myVasTestHelper::registerUser($newMappings->getActiveMsisdn(4), $newMappings->getActiveNickname(4));

if ($returnStatus == STATUS_SUCCESS) {
print 'Done'. "\n";
}
else {
print 'Failed'. "\n";    
}

print 'Register Msisdn 5 ' .$newMappings->getActiveMsisdn(5). "\t";
$returnStatus = myVasTestHelper::registerUser($newMappings->getActiveMsisdn(5), $newMappings->getActiveNickname(5));

if ($returnStatus == STATUS_SUCCESS) {
print 'Done'. "\n";
}
else {
print 'Failed'. "\n";    
}

