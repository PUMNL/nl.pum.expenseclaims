<?php
/**
 * ClaimBatchEntity.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_batch_entity_delete_spec(&$spec) {
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
    'type' => CRM_Utils_Type::T_STRING,
  );
}
/**
 * ClaimBatchEntity.Delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_batch_entity_delete($params) {
  if (array_key_exists('id', $params)) {
    return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimBatchEntity::deleteWithId($params['id']), $params, 'ClaimBatchEntity', 'Delete');
  } else if (array_key_exists('batch_id')&&array_key_exists('entity_id')&&array_key_exists('entity_table')){
    return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimBatchEntity::deleteWithUK(
      $params['batch_id'],
      $params['entity_id'],
      $params['entity_table']
    ),$params, 'ClaimBatchEntity', 'Delete');

  } else
  {
    throw new API_Exception('Id is a mandatory param when deleting a claim level', 'mandatory_id_missing', 0020);
  }
}