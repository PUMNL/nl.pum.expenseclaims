<?php

/**
 * ClaimLine.Delete API specification (optional)
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
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  );
}

/**
 * ClaimLine.Delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_line_delete($params) {
  if (array_key_exists('id', $params)) {
    return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimLine::deleteWithId($params['id']), $params, 'ClaimLine', 'Delete');
  } else {
    throw new API_Exception('Id is a mandatory param when deleting a claim line', 'mandatory_id_missing', 0020);
  }
}

