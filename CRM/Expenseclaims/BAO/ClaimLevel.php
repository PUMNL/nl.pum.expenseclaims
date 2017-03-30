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
      $row['valid_types'] = $claimLevel->getLevelTypes($claimLevel->id);
      $row['valid_main_activities'] = $claimLevel->getLevelMainActivities($claimLevel->id);
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
    self::storeValues($claimLevel, $result);
    // now add or update the types and main activities for the level
    $claimLevel->addValidTypes($claimLevel->id, $params['valid_types']);
    if (isset($params['valid_main_activities'])) {
      $claimLevel->addValidMainActivities($claimLevel->id, $params['valid_main_activities']);
    }
    return $result;
  }

  /**
   * Method to first delete the existing set of claim level types for the claim level and then
   * save the new set
   *
   * @param $claimLevelId
   * @param $validTypes
   */
  private function addValidTypes($claimLevelId, $validTypes) {
    // first delete existing set
    $oldClaimLevelType = new CRM_Expenseclaims_DAO_ClaimLevelType();
    $oldClaimLevelType->claim_level_id = $claimLevelId;
    $oldClaimLevelType->find();
    while ($oldClaimLevelType->fetch()) {
      $oldClaimLevelType->delete();
    }
    // then save new set
    if (!empty($validTypes)) {
      foreach ($validTypes as $validType) {
        $claimLevelType = new CRM_Expenseclaims_DAO_ClaimLevelType();
        $claimLevelType->claim_level_id = $claimLevelId;
        $claimLevelType->type_value = $validType;
        $claimLevelType->save();
      }
    }
  }

  /**
   * Method to first delete the existing set of claim level main activities for the claim level and then
   * save the new set
   *
   * @param $claimLevelId
   * @param $validMainActivities
   */
  private function addValidMainActivities($claimLevelId, $validMainActivities) {
    // first delete existing set
    $oldClaimLevelMain = new CRM_Expenseclaims_DAO_ClaimLevelMain();
    $oldClaimLevelMain->claim_level_id = $claimLevelId;
    $oldClaimLevelMain->find();
    while ($oldClaimLevelMain->fetch()) {
      $oldClaimLevelMain->delete();
    }
    // then save new set
    if (!empty($validMainActivities)) {
      foreach ($validMainActivities as $validMainActivity) {
        $claimLevelMain = new CRM_Expenseclaims_DAO_ClaimLevelMain();
        $claimLevelMain->claim_level_id = $claimLevelId;
        $claimLevelMain->main_activity_type_id = $validMainActivity;
        $claimLevelMain->save();
      }
    }
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
    // first delete all types and main activities
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

  /**
   * Method to get the contact for the next level authorization
   *
   * @param int $claimId
   * @param string $authorizingLevel
   * @return bool|int
   */
  public static function getNextLevelContactId($claimId, $authorizingLevel) {
    if (empty($claimId) || empty($authorizingLevel)) {
      return FALSE;
    }
    // check authorizing level
    switch ($authorizingLevel) {
      // if cfo, return cfo contact
      case 'cfo':
        $config = CRM_Threepeas_CaseRelationConfig::singleton();
        return $config->getPumCfo();
        break;
      // if cpo, return cpo contact
      case 'cpo':
        $config = CRM_Expenseclaims_Config::singleton();
        return $config->getPumCpo();
        break;
      // if senior project officer, get country for claim and then relevant senior project officer
      case 'senior_project_officer':
        $countryId = CRM_Expenseclaims_Utils::getCountryForClaimCustomer($claimId);
        if ($countryId) {
          return CRM_Expenseclaims_Utils::getSeniorProjectOfficerForCountry($countryId);
        }
        break;
      // if project officer, get country for claim and then relevant project officer
      case 'project_officer':
        $countryId = CRM_Expenseclaims_Utils::getCountryForClaimCustomer($claimId);
        if ($countryId) {
          return CRM_Expenseclaims_Utils::getProjectOfficerForCountry($countryId);
        }
        break;
    }
    return FALSE;
  }
}