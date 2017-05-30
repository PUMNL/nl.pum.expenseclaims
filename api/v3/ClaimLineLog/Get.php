<?php

/**
 * ClaimLineLog.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_line_log_get_spec(&$spec) {
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
    'type' => CRM_Utils_Type::T_DATE
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
  $spec['old_euro_amount'] = array(
    'name' => 'old_euro_amount',
    'title' => 'old_euro_amount',
    'type' => CRM_Utils_Type::T_MONEY
  );
  $spec['new_euro_amount'] = array(
    'name' => 'new_euro_amount',
    'title' => 'new_euro_amount',
    'type' => CRM_Utils_Type::T_MONEY
  );
}

/**
 * ClaimLineLog.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_line_log_get($params) {
  $result = CRM_Expenseclaims_BAO_ClaimLineLog::getValues($params);
  foreach ($result as $resultId => $claimLineLog) {
    if (isset($claimLineLog['old_currency_id'])) {
      $sql = "SELECT name, full_name FROM civicrm_currency WHERE id = %1";
      $currency = CRM_Core_DAO::executeQuery($sql, array(1 => array($claimLineLog['old_currency_id'], 'Integer')));
      if ($currency->fetch()) {
        $result[$resultId]['old_currency'] = $currency->name . ' (' . $currency->full_name . ')';
      }
    }
    if (isset($claimLineLog['new_currency_id'])) {
      $sql = "SELECT name, full_name FROM civicrm_currency WHERE id = %1";
      $currency = CRM_Core_DAO::executeQuery($sql, array(1 => array($claimLineLog['new_currency_id'], 'Integer')));
      if ($currency->fetch()) {
        $result[$resultId]['new_currency'] = $currency->name . ' (' . $currency->full_name . ')';
      }
    }
    if (isset($claimLineLog['changed_by_id'])) {
      try {
        $result[$resultId]['changed_by'] = civicrm_api3('Contact', 'getvalue', array(
          'id' => $claimLineLog['changed_by_id'],
          'return' => 'display_name'
        ));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
  }
  return civicrm_api3_create_success($result, $params, 'ClaimLineLog', 'Get');
}

