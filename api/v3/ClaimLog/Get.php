<?php

/**
 * ClaimLog.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_log_get_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['claim_activity_id'] = array(
    'name' => 'claim_activity_id',
    'title' => 'claim_activity_id',
    'type' => CRM_Utils_Type::T_INT,
  );
  $spec['approval_contact_id'] = array(
    'name' => 'approval_contact_id',
    'title' => 'approval_contact_id',
    'type' => CRM_Utils_Type::T_INT,
  );
  $spec['old_status_id'] = array(
    'name' => 'old_status_id',
    'title' => 'old_status_id',
    'type' => CRM_Utils_Type::T_STRING,
  );
  $spec['new_status_id'] = array(
    'name' => 'new_status_id',
    'title' => 'new_status_id',
    'type' => CRM_Utils_Type::T_STRING,
  );
  $spec['processed_date'] = array(
    'name' => 'processed_date',
    'title' => 'processed_date',
    'type' => CRM_Utils_Type::T_DATE,
  );
  $spec['is_approved'] = array(
    'name' => 'is_approved',
    'title' => 'is_approved',
    'type' => CRM_Utils_Type::T_INT,
  );
  $spec['is_rejected'] = array(
    'name' => 'is_rejected',
    'title' => 'is_rejected',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['is_payable'] = array(
    'name' => 'is_payable',
    'title' => 'is_payable',
    'type' => CRM_Utils_Type::T_INT
  );
}

/**
 * ClaimLog.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_log_get($params) {
  return civicrm_api3_create_success(CRM_Expenseclaims_BAO_ClaimLog::getValues($params), $params, 'ClaimLog', 'Get');
}

