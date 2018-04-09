<?php
/**
 * Class BAO Claim Log
 *
 * @author Erik Hommel (CiviCooP)
 * @date 17 Feb 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_ClaimLog extends CRM_Expenseclaims_DAO_ClaimLog {

  /**
   * Function to get values
   *
   * @param array $params
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $claimLog = new CRM_Expenseclaims_BAO_ClaimLog();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $claimLog->$key = $value;
        }
      }
    }
    $claimLog->find();
    while ($claimLog->fetch()) {
      $row = array();
      self::storeValues($claimLog, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Function to add or update claim log
   *
   * @param array $params
   * @return array $result
   * @access public
   * @throws Exception when params is empty or not valid
   * @throws Exception if activity is not claim
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a claim log record in '.__METHOD__);
    }
    // claim_activity id and approval_contact_id are required when create mode (id is not present)
    if (!isset($params['id'])) {
      if (!isset($params['claim_activity_id']) || !isset($params['approval_contact_id'])) {
        throw new Exception('Parameters claim_activity_id and/or approval_contact_id is mandatory when adding a 
        claim log record in ' . __METHOD__);
      }
    }
    $claimLog = new CRM_Expenseclaims_BAO_ClaimLog();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $claimLog->$key = $value;
      }
    }
    $claimLog->save();
    self::storeValues($claimLog, $result);
    return $result;
  }

  /**
   * Method to delete all claim logs for the activity (parent activity of the type Claim)
   *
   * @param $activityId
   * @throws Exception when activityId is empty
   */
  public static function deleteWithActivityId($activityId) {
    if (empty($activityId)) {
      throw new Exception('activity id can not be empty when attempting to delete claim log records for an activity in '.__METHOD__);
    }
    $claimLog = new CRM_Expenseclaims_BAO_ClaimLog();
    $claimLog->claim_activity_id = $activityId;
    $claimLog->find();
    while ($claimLog->fetch()) {
      $claimLog->delete();
    }
  }
}