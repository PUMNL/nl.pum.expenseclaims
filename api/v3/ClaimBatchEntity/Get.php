<?php

/**
 * ClaimBatchEntity.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_batch_entity_get_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['batch_id'] = array(
    'name' => 'batch_id',
    'title' => 'batch_id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['entity_id'] = array(
    'name' => 'entity_id',
    'title' => 'entity_id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['entity_table'] = array(
    'name' => 'entity_table',
    'title' => 'entity_table',
    'type' => CRM_Utils_Type::T_STRING
  );
}

/**
 * ClaimBatchEntity.Get API
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 March 2017
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_batch_entity_get($params) {
  return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimBatchEntity::getValues($params), $params, 'ClaimBatchEntity', 'Get');
}
