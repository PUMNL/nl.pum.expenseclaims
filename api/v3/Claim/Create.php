<?php

/**
 * Claim.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_create_spec(&$spec) {
  $spec['expense_date'] = array(
    'name' => 'expense_date',
    'title' => 'expense_date',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 1,
  );
  $spec['claim_type_id'] = array(
    'name' => 'claim_type_id',
    'title' => 'claim_type_id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  );
  $spec['claim_contact_id'] = array(
    'name' => 'claim_contact_id',
    'title' => 'claim_contact_id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  );
  $spec['claim_link'] = array(
    'name' => 'claim_link',
    'title' => 'claim_link',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
  );
  $spec['claim_total_amount'] = array(
    'name' => 'claim_total_amount',
    'title' => 'claim_total_amount',
    'type' => CRM_Utils_Type::T_MONEY,
    'api.required' => 1,
  );
  $spec['description'] = array(
    'name' => 'description',
    'title' => 'description',
    'type' => CRM_Utils_Type::T_STRING
  );
}

/**
 * Claim.Create API
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 March 2017
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_create($params) {
  $result = array();
  $config = CRM_Expenseclaims_Config::singleton();
  $params['activity_type_id'] = $config->getClaimActivityTypeId();
  // create activity
  $activityParams = array(
    'activity_type_id' => $config->getClaimActivityTypeId(),
    'activity_date_time' => date('Y-m-d', strtotime($params['expense_date'])),
    'status_id' => $config->getScheduledActivityStatusId(),
    'target_contact_id' => $params['claim_contact_id'],
    'subject' => 'Claim entered on website'
  );
  try {
    $activity = civicrm_api3('Activity', 'create', $activityParams);
  } catch (CiviCRM_API3_Exception $ex) {
    civicrm_api3_create_error('Could not create claim activity in '.__METHOD__, array('params' => $params));
  }
  // then add custom data
  $result = $activity['values'][0];
  addCustomData($activity['id'], $params, $result);
  return civicrm_api3_create_success($result, $params, 'Claim', 'Create');
}

/**
 * Function to insert custom record for claim activity
 *
 * @param $activityId
 * @param $params
 * @param $result
 */
function addCustomData($activityId, $params, &$result) {
  $config = CRM_Expenseclaims_Config::singleton();
  $sqlClauses = array('entity_id = %1');
  $sqlParams[1] = array($activityId, 'Integer');
  $sqlClauses[] = $config->getClaimStatusCustomField('column_name').' = %2';
  $sqlParams[2] = array($config->getWaitingForApprovalClaimStatusValue(), 'String');
  $index = 2;
  if (isset($params['claim_type_id'])) {
    $index++;
    $sqlClauses[] = $config->getClaimTypeCustomField('column_name').' = %'.$index;
    $sqlParams[$index] = array($params['claim_type_id'], 'String');
  }
  if (isset($params['claim_link'])) {
    $index++;
    $sqlClauses[] = $config->getClaimLinkCustomField('column_name').' = %'.$index;
    $sqlParams[$index] = array($params['claim_link'], 'String');
  }
  if (isset($params['claim_total_amount'])) {
    $index++;
    $sqlClauses[] = $config->getClaimTotalAmountCustomField('column_name').' = %'.$index;
    $sqlParams[$index] = array($params['claim_total_amount'], 'Money');
  }
  if (isset($params['claim_description'])) {
    $index++;
    $sqlClauses[] = $config->getClaimDescriptionCustomField('column_name').' = %'.$index;
    $sqlParams[$index] = array($params['claim_description'], 'String');
  }
  $sql = 'INSERT INTO '.$config->getClaimInformationCustomGroup('table_name').' SET '.implode(', ', $sqlClauses);
  CRM_Core_DAO::executeQuery($sql, $sqlParams);
}

