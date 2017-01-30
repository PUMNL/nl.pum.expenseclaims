<?php
/**
 * Class BAO Claim Level
 *
 * @author Erik Hommel (CiviCooP)
 * @date 30 Jan 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_ClaimLevel extends CRM_Expenseclaims_DAO_ClaimLevel {

  /**
   * Function to get values
   *
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $claimLevel = new CRM_Expenseclaims_BAO_ClaimLevel();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $claimLevel->$key = $value;
        }
      }
    }
    $claimLevel->find();
    while ($claimLevel->fetch()) {
      $row = array();
      self::storeValues($claimLevel, $row);
      // now add claim level types and main activitities
      $row['level_types'] = $claimLevel->getLevelTypes($claimLevel->id);
      $row['level_main_activities'] = $claimLevel->getLevelMainActivities($claimLevel->id);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Method to get all the claim level types for a level (the valid types for that level)
   *
   * @param $claimLevelId
   * @return array
   */
  public function getLevelMainActivities($claimLevelId) {
    $result = array();
    $claimLevelMain = new CRM_Expenseclaims_DAO_ClaimLevelMain();
    $claimLevelMain->claim_level_id = $claimLevelId;
    $claimLevelMain->find();
    while ($claimLevelMain->fetch()) {
      $result[] = $claimLevelMain->main_activity_type_id;
    }
    return $result;
  }

  /**
   * Method to get all the claim level types for a level (the valid types for that level)
   *
   * @param $claimLevelId
   * @return array
   */
  public function getLevelTypes($claimLevelId) {
    $result = array();
    $claimLevelType = new CRM_Expenseclaims_DAO_ClaimLevelType();
    $claimLevelType->claim_level_id = $claimLevelId;
    $claimLevelType->find();
    while ($claimLevelType->fetch()) {
      $result[] = $claimLevelType->type_value;
    }
    return $result;
  }

  /**
   * Function to add or update claim level (with types and main activities)
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
      throw new Exception('Params can not be empty when adding or updating a claim level in '.__METHOD__);
    }
    $claimLevel = new CRM_Expenseclaims_BAO_ClaimLevel();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $claimLevel->$key = $value;
      }
    }
    $claimLevel->save();
    self::storeValues($claimLine, $result);
    // now add or update the types and main activities for the level
    $claimLevel->addTypes($params['types']);
    $claimLevel->addMainActivities($params['main_activities']);
    return $result;
  }

  /**
   * Function to delete a claim level (with types and main activities) by id
   *
   * @param int $claimLevelId
   * @throws Exception when claimLevelId is empty
   */
  public static function deleteWithId($claimLevelId) {
    if (empty($claimLevelId)) {
      throw new Exception('claim level id can not be empty when attempting to delete a claim level in '.__METHOD__);
    }
    $claimLevel = new CRM_Expenseclaims_BAO_ClaimLevel();
    // first delete all types
    $claimLevelType = new CRM_Expenseclaims_DAO_ClaimLevelType();
    $claimLevelType->claim_level_id = $claimLevelId;
    $claimLevelType->find();
    while ($claimLevelType->fetch()) {
      $claimLevelType->delete();
    }
    $claimLevelMain = new CRM_Expenseclaims_DAO_ClaimLevelMain();
    $claimLevelMain->claim_level_id = $claimLevelId;
    $claimLevelMain->find();
    while ($claimLevelMain->fetch()) {
      $claimLevelMain->delete();
    }
    // finally delete the claim level
    $claimLevel->id = $claimLevelId;
    $claimLevel->delete();
  }
}