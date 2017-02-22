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
   * @throws Exception when params is empty or not valid
   * @throws Exception if activity is not claim
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a claim line in '.__METHOD__);
    }
    // activity id is required when create mode (id is not present)
    if (!isset($params['id'])) {
      if (!isset($params['activity_id'])) {
        throw new Exception('Parameter activity id is mandatory when adding a claim line in ' . __METHOD__);
      }
    }
    // validate that activity is indeed a claim activity
    $claimLine = new CRM_Expenseclaims_BAO_ClaimLine();
    if ($claimLine->validClaimActivity($params['activity_id']) == FALSE) {
      throw new Exception('You are trying to add a claim line to an activity that is not a Claim (activity_type_id) in '
        .__METHOD__.'. This is not allowed.');
    }
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $claimLine->$key = $value;
      }
    }
    $claimLine->save();
    // update total amount of claim whenever a claim line is added or updated
    $claim = new CRM_Expenseclaims_BAO_Claim();
    $claim->updateTotalAmount($claimLine->activity_id);
    self::storeValues($claimLine, $result);
    return $result;
  }

  /**
   * Method to check if activity is of type Claim
   *
   * @param $activityId
   * @return bool
   */
  private function validClaimActivity($activityId) {
    $activityTypeId = civicrm_api3('Activity', 'getvalue', array(
      'id' => $activityId,
      'return' => 'activity_type_id'
    ));
    $config = CRM_Expenseclaims_Config::singleton();
    if ($activityTypeId == $config->getClaimActivityTypeId()) {
      return TRUE;
    } else {
      return FALSE;
    }
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

  /**
   * Method to get a single claim line with id
   *
   * @param $claimLineId
   * @return array
   */
  public static function getWithId($claimLineId) {
    $result = array();
    if (!empty($claimLineId)) {
      $claimLine = new CRM_Expenseclaims_BAO_ClaimLine();
      $claimLine->id = $claimLineId;
      $claimLine->find();
      if ($claimLine->fetch()) {
        $result = array(
          'id' => $claimLine->id,
          'activity_id' =>  $claimLine->activity_id,
          'expense_date' => $claimLine->expense_date,
          'expense_type' => $claimLine->expense_type,
          'currency_id' => $claimLine->currency_id,
          'currency_amount' => $claimLine->currency_amount,
          'euro_amount' => $claimLine->euro_amount,
          'description' => $claimLine->description,
          'exchange_rate' => $claimLine->exchange_rate
        );
      }
    }
    return $result;
  }
}