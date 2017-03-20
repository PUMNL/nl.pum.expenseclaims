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
  $spec['claim_type'] = array(
    'name' => 'claim_type',
    'title' => 'claim_type',
    'type' => CRM_Utils_Type::T_STRING,
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
  $claim = new CRM_Expenseclaims_BAO_Claim();
  $result = $claim->createNew($params);
  if ($result == FALSE) {
    return civicrm_api3_create_error('Could not create claim activity in '.__METHOD__, array('params' => $params));
  } else {
    return civicrm_api3_create_success($result, $params, 'Claim', 'Create');
  }
}

