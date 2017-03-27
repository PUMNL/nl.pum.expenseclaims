<?php
/**
 * Class BAO Claim Batch Entity
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 20 March 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_ClaimBatchEntity extends CRM_Expenseclaims_DAO_ClaimBatchEntity {

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
    $claimBatchEntity = new CRM_Expenseclaims_BAO_ClaimBatchEntity();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $claimBatchEntity->$key = $value;
        }
      }
    }
    $claimBatchEntity->find();
    while ($claimBatchEntity->fetch()) {
      $row = array();
      self::storeValues($claimBatchEntity, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Function to add or update claim batch
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
      throw new Exception('Params can not be empty when adding or updating a claim batch entity in '.__METHOD__);
    }
    $claimBatchEntity = new CRM_Expenseclaims_BAO_ClaimBatchEntity();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $claimBatchEntity->$key = $value;
      }
    }
    $claimBatchEntity->save();
    self::storeValues($claimBatchEntity, $result);
    return $result;
  }
}