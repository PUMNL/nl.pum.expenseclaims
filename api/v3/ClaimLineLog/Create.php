<?php

/**
 * ClaimLineLog.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_line_log_create_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['claim_line_id'] = array(
    'name' => 'claim_line_id',
    'title' => 'claim_line_id',
    'type' => CRM_Utils_Type::T_INT,
  );
  $spec['changed_by_id'] = array(
    'name' => 'changed_by_id',
    'title' => 'changed_by_id',
    'type' => CRM_Utils_Type::T_INT,
  );
  $spec['changed_date'] = array(
    'name' => 'changed_date',
    'title' => 'changed_date',
    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
  );
  $spec['change_reason'] = array(
    'name' => 'change_reason',
    'title' => 'change_reason',
    'type' => CRM_Utils_Type::T_STRING,
  );
  $spec['old_expense_date'] = array(
    'name' => 'old_expense_date',
    'title' => 'old_expense_date',
    'type' => CRM_Utils_Type::T_DATE,
  );
  $spec['new_expense_date'] = array(
    'name' => 'new_expense_date',
    'title' => 'new_expense_date',
    'type' => CRM_Utils_Type::T_DATE,
  );
  $spec['old_currency_id'] = array(
    'name' => 'old_currency_id',
    'title' => 'old_currency_id',
    'type' => CRM_Utils_Type::T_INT,
  );
  $spec['new_currency_id'] = array(
    'name' => 'new_currency_id',
    'title' => 'new_currency_id',
    'type' => CRM_Utils_Type::T_INT,
  );
  $spec['old_currency_amount'] = array(
    'name' => 'old_currency_amount',
    'title' => 'old_currency_amount',
    'type' => CRM_Utils_Type::T_MONEY
  );
  $spec['new_currency_amount'] = array(
    'name' => 'new_currency_amount',
    'title' => 'new_currency_amount',
    'type' => CRM_Utils_Type::T_MONEY
  );
}

/**
 * ClaimLineLog.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_line_log_create($params) {
  // check required params when id is not present and we are creating a new claim log record
  if (!isset($params['id'])) {
    $mandatories = array('claim_line_id', 'changed_by_id', 'changed_date', 'change_reason');
    foreach ($mandatories as $mandatory) {
      if (!isset($params[$mandatory]) || empty($params[$mandatory])) {
        return civicrm_api3_create_error('Mandatory parameter '.$mandatory.'missing', array('params' => $params));
      }
    }
  }
  return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimLineLog::add($params), $params, 'ClaimLineLog', 'Create');
}

