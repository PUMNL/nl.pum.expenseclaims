<?php
use CRM_Expenseclaims_ExtensionUtil as E;

/**
 * Currency.Get API specification (optional)
 *
 * API to get currency parameters from civicrm_currency table
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_currency_get_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 0
  );
  $spec['name'] = array(
    'name' => 'name',
    'title' => 'name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0
  );
}

/**
 * Currency.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_currency_get($params) {
  $returnValues = array();
  if(!empty($params['id']) && is_int((int)$params['id'])) {
    $q_currency = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_currency WHERE id = %1", array(1 => array($params['id'], 'Integer')));

    while($q_currency->fetch()){
      $returnValues[] = array(
        'id' => $q_currency->id,
        'name' => $q_currency->name,
        'symbol' => $q_currency->symbol,
        'numeric_code' => $q_currency->numeric_code,
        'full_name' => $q_currency->full_name
      );
    }
  } else if (!empty($params['name']) && is_string((string)$params['name'])) {
    $q_currency = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_currency WHERE name = %1", array(1 => array($params['name'], 'String')));

    while($q_currency->fetch()){
      $returnValues[] = array(
        'id' => $q_currency->id,
        'name' => $q_currency->name,
        'symbol' => $q_currency->symbol,
        'numeric_code' => $q_currency->numeric_code,
        'full_name' => $q_currency->full_name
      );
    }
  } else {
    return civicrm_api3_create_success(array(), $params, 'Currency', 'Get');
  }
  return civicrm_api3_create_success($returnValues, $params, 'Currency', 'Get');
}
