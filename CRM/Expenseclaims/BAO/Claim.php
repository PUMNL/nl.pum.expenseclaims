<?php
/**
 * Class BAO Claim (specific activity type)
 *
 * @author Erik Hommel (CiviCooP)
 * @date 17 Feb 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_Claim {

  /**
   * CRM_Expenseclaims_BAO_Claim constructor.
   */
  public function __construct()   {
  }

  /**
   * Method to get all claims where the contact should approve
   *
   * @param int $contactId
   * @return array $result
   */
  public function getMyClaims($contactId) {
    $result = array();
    return $result;
  }
  public function getExportableClaims() {

  }
  public function nextStepForClaim($claimId) {

  }


}