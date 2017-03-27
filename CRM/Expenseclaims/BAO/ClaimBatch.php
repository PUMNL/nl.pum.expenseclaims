<?php
/**
 * Class BAO Claim Batch
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 7 March 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_ClaimBatch extends CRM_Expenseclaims_DAO_ClaimBatch {

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
    $claimBatch = new CRM_Expenseclaims_BAO_ClaimBatch();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $claimBatch->$key = $value;
        }
      }
    }
    $claimBatch->find();
    while ($claimBatch->fetch()) {
      $row = array();
      self::storeValues($claimBatch, $row);
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
      throw new Exception('Params can not be empty when adding or updating a claim batch in '.__METHOD__);
    }
    $claimBatch = new CRM_Expenseclaims_BAO_ClaimBatch();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $claimBatch->$key = $value;
      }
    }
    $claimBatch->save();
    self::storeValues($claimBatch, $result);
    return $result;
  }
}