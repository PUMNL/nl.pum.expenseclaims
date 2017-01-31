<?php

/**
 * ClaimLevel.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_level_delete_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  );
}

/**
 * ClaimLevel.Delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_level_delete($params) {
  if (array_key_exists('id', $params)) {
    return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimLevel::deleteWithId($params['id']), $params, 'ClaimLevel', 'Delete');
  } else {
    throw new API_Exception('Id is a mandatory param when deleting a claim level', 'mandatory_id_missing', 0020);
  }
}

