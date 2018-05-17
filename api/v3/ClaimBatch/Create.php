<?php
/**
 * @author Klaas Eikelboom (CiviCooP) klaas.eikelboom@civicoop.org
 * @date  02 jul 2017
 * @license AGPL-3.0
 */

/**
 * ClaimBatch.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_batch_create_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['batch_status_id'] = array(
    'name' => 'batch_status_id',
    'title' => 'batch_status_id',
    'type' => CRM_Utils_Type::T_INT
  );
}

/**
 * ClaimBatch.Get API
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 March 2017
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_batch_create($params) {
  CRM_Expenseclaims_BAO_ClaimBatch::add($params);
  return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimBatch::getValues($params), $params, 'ClaimBatch', 'Create');
}
