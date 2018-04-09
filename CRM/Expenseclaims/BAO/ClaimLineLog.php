<?php
/**
 * Class BAO Claim Line Log
 *
 * @author Erik Hommel (CiviCooP)
 * @date 29 Mar 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_ClaimLineLog extends CRM_Expenseclaims_DAO_ClaimLineLog {

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
    $claimLineLog = new CRM_Expenseclaims_BAO_ClaimLineLog();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $claimLineLog->$key = $value;
        }
      }
    }
    $claimLineLog->find();
    while ($claimLineLog->fetch()) {
      $row = array();
      self::storeValues($claimLineLog, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Function to add or update claim line log
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
      throw new Exception('Params can not be empty when adding or updating a claim linelog record in '.__METHOD__);
    }
    // claim_line_id, changed_by_id and changed_date are required when create mode (id is not present)
    if (!isset($params['id'])) {
      if (!isset($params['claim_line_id']) || !isset($params['changed_by_id']) || !isset($params['changed_date'])) {
        throw new Exception('Parameters claim_line_id, changed_byId and/or changed_date are mandatory when adding a 
        claim line log record in ' . __METHOD__);
      }
    }
    $claimLineLog = new CRM_Expenseclaims_BAO_ClaimLineLog();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $claimLineLog->$key = $value;
      }
    }
    $claimLineLog->save();
    self::storeValues($claimLineLog, $result);
    return $result;
  }

  /**
   * Method to delete all claim logs for the activity (parent activity of the type Claim)
   *
   * @param $id
   * @throws Exception when activityId is empty
   */
  public static function deleteWithActivityId($id) {
    if (empty($id)) {
      throw new Exception('activity id can not be empty when attempting to delete claim log records for an activity in '.__METHOD__);
    }
    $claimLineLog = new CRM_Expenseclaims_BAO_ClaimLineLog();
    $claimLineLog->claim_activity_id = $id;
    $claimLineLog->find();
    while ($claimLineLog->fetch()) {
      $claimLineLog->delete();
    }
  }
}