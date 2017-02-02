<?php

/**
 * ClaimLevel.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_level_get_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['level'] = array(
    'name' => 'level',
    'title' => 'level',
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['max_amount'] = array(
    'name' => 'max_amount',
    'title' => 'max_amount',
    'type' => CRM_Utils_Type::T_MONEY,
  );
  $spec['authorizing_level'] = array(
    'name' => 'authorizing_level',
    'title' => 'authorizing_level',
    'type' => CRM_Utils_Type::T_STRING,
  );
}

/**
 * ClaimLevel.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_level_get($params) {
  return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimLevel::getValues($params), $params, 'ClaimLevel', 'Get');
}

