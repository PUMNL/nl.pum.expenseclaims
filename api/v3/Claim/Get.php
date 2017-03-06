<?php

/**
 * Claim.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_claim_get_spec(&$spec) {
  $spec['activity_id'] = array(
    'name' => 'activity_id',
    'title' => 'activity_id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['expense_date'] = array(
    'name' => 'expense_date',
    'title' => 'expense_date',
    'type' => CRM_Utils_Type::T_DATE
  );
  $spec['claim_contact_id'] = array(
    'name' => 'claim_contact_id',
    'title' => 'claim_contact_id',
    'type' => CRM_Utils_Type::T_INT
  );
}

/**
 * Claim.Get API
 * this special api will get the activity of the type claim, its target contacts and its custom fields
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 March 2017
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_claim_get($params) {
  $result = array();
  // use expense date as activity date time if set
  if (isset($params['expense_date'])) {
    $params['activity_date_time'] = $params['expense_date'];
  }
  // use contact_id if set
  $config = CRM_Expenseclaims_Config::singleton();
  // first get activity or activities or return error
  $params['is_current_revision'] = 1;
  $params['is_test'] = 0;
  $params['is_deleted'] = 0;
  $params['activity_type_id'] = $config->getClaimActivityTypeId();
  try {
    $claims = civicrm_api3('Activity', 'Get', $params);
  } catch (CiviCRM_API3_Exception $ex) {
    return civicrm_api3_create_error('No activity of the type Claim found with the parameters used', array('params' => $params));
  }
  // then output claim and get targets and custom fields for each found activity
  foreach ($claims['values'] as $claim) {
    $result[$claim['id']] = $claim;
    getTargetContacts($claim['id'], $result);
    getCustomFieldData($claim['id'], $result);
  }
  return civicrm_api3_create_success($result, $params, 'Claim', 'Get');
}

/**
 * Function to get the claim target contacts
 *
 * @param $claimId
 * @param $result
 */
function getTargetContacts($claimId, &$result) {
  $result[$claimId]['claim_target_contacts'] = array();
  $config = CRM_Expenseclaims_Config::singleton();
  $sql = "SELECT contact_id FROM civicrm_activity_contact WHERE activity_id = %1 AND record_type_id = %2";
  $dao = CRM_Core_DAO::executeQuery($sql, array(
    1 => array($claimId, 'Integer'),
    2 => array($config->getTargetRecordTypeId(), 'Integer')
  ));
  while ($dao->fetch()) {
    $result[$claimId]['claim_target_contacts'][] = $dao->contact_id;
  }
}

/**
 * Function to get the claim status data
 *
 * @param $claimId
 * @param $result
 */
function getCustomFieldData($claimId, &$result) {
  // set custom field column names
  $config = CRM_Expenseclaims_Config::singleton();
  $claimStatusColumn = $config->getClaimStatusCustomField('column_name');
  $claimTypeColumn = $config->getClaimTypeCustomField('column_name');
  $claimLinkColumn = $config->getClaimLinkCustomField('column_name');
  $claimTotalAmountColumn = $config->getClaimTotalAmountCustomField('column_name');
  $claimDescriptionColumn = $config->getClaimDescriptionCustomField('column_name');

  $sql = "SELECT * FROM ".$config->getClaimInformationCustomGroup('table_name')." WHERE entity_id = %1";
  $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($claimId, 'Integer')));
  while ($dao->fetch()) {
    // claim status
    if (isset($dao->$claimStatusColumn)) {
      $result[$claimId]['claim_status_id'] = $dao->$claimStatusColumn;
      try {
        $result[$claimId]['claim_status'] = civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => 'pum_claim_status',
          'value' => $dao->$claimStatusColumn,
          'return' => 'label'));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    // claim type
    if (isset($dao->$claimTypeColumn)) {
      $result[$claimId]['claim_type_id'] = $dao->$claimTypeColumn;
      try {
        $result[$claimId]['claim_type'] = civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => 'pum_claim_type',
          'value' => $dao->$claimTypeColumn,
          'return' => 'label'));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    // claim link
    if (isset($dao->$claimLinkColumn)) {
      $result[$claimId]['claim_linked_to'] = $dao->$claimLinkColumn;
    }
    // claim total amount
    if (isset($dao->$claimTotalAmountColumn)) {
      $result[$claimId]['claim_total_amount'] = $dao->$claimTotalAmountColumn;
    }
    // claim description
    if (isset($dao->$claimDescriptionColumn)) {
      $result[$claimId]['claim_description'] = $dao->$claimDescriptionColumn;
    }
  }
}

