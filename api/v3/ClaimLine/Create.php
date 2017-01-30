<?php

/**
 * ClaimLine.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_contact_segment_create_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['activity_id'] = array(
    'name' => 'activity_id',
    'title' => 'activity_id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  );
  $spec['expense_date'] = array(
    'name' => 'expense_date',
    'title' => 'expense_date',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 1,
  );
  $spec['currency_id'] = array(
    'name' => 'currency_id',
    'title' => 'currency_id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  );
  $spec['currency_amount'] = array(
    'name' => 'currency_amount',
    'title' => 'currency_amount',
    'type' => CRM_Utils_Type::T_MONEY,
    'api.required' => 1,
  );
  $spec['euro_amount'] = array(
    'name' => 'euro_amount',
    'title' => 'euro_amount',
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
 * ClaimLine.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_line_create($params) {
  return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimLine::add($params), $params, 'ClaimLine', 'Create');
}

