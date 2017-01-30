<?php
/**
 * Class BAO Claim Line
 *
 * @author Erik Hommel (CiviCooP)
 * @date 30 Jan 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_ClaimLine extends CRM_Expenseclaims_DAO_ClaimLine {

  /**
   * Function to get values
   *
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $claimLine = new CRM_Expenseclaims_BAO_ClaimLine();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $claimLine->$key = $value;
        }
      }
    }
    $claimLine->find();
    while ($claimLine->fetch()) {
      $row = array();
      self::storeValues($claimLine, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Function to add or update claim line
   *
   * @param array $params
   * @return array $result
   * @access public
   * @throws Exception when params is empty
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a claim line in '.__METHOD__);
    }
    $claimLine = new CRM_Expenseclaims_BAO_ClaimLine();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $claimLine->$key = $value;
      }
    }
    $claimLine->save();
    self::storeValues($claimLine, $result);
    return $result;
  }

  /**
   * Function to delete a claim line by id
   *
   * @param int $claimLineId
   * @throws Exception when claimLineId is empty
   */
  public static function deleteWithId($claimLineId) {
    if (empty($claimLineId)) {
      throw new Exception('claim line id can not be empty when attempting to delete a claim line in '.__METHOD__);
    }
    $claimLine = new CRM_Expenseclaims_BAO_ClaimLine();
    $claimLine->id = $claimLineId;
    $claimLine->delete();
  }

  /**
   * Method to delete all claim lines for the activity (parent activity of the type Claim)
   *
   * @param $activityId
   * @throws Exception when activityId is empty
   */
  public static function deleteWithActivityId($activityId) {
    if (empty($activityId)) {
      throw new Exception('activity id can not be empty when attempting to delete claim lines for an activity in '.__METHOD__);
    }
    $claimLine = new CRM_Expenseclaims_BAO_ClaimLine();
    $claimLine->activity_id = $activityId;
    $claimLine->find();
    while ($claimLine->fetch()) {
      $claimLine->delete();
    }
  }
}