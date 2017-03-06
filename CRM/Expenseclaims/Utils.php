<?php

/**
 * Class with generic extension helper methods
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 21 Feb 2017
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_Utils {

  /**
   * Method to check if contact is project officer
   * (has an active relationship of the type project officer with the country of the customer
   * of the main activity to which the claim is linked)
   *
   * @param $contactId
   * @param $claimId
   * @return bool
   */
  public static function isProjectOfficer($contactId, $claimId) {
    if (!empty($contactId) && !empty($claimId)) {
      $config = CRM_Expenseclaims_Config::singleton();
      $countryId = self::getCountryForClaimCustomer($claimId);
      if ($countryId) {
        $count = civicrm_api3('Relationship', 'getcount', array(
          'is_active' => 1,
          'relationship_type_id' => $config->getProjectOfficerRelationshipTypeId(),
          'contact_id_a' => $countryId,
          'contact_id_b' => $contactId
        ));
        if ($count > 0) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to get a country of a claim customer with claim id (only if claim is on project and link holds case_id)
   *
   * @param $claimId
   * @return bool|int
   */
  public static function getCountryForClaimCustomer($claimId) {
    if (empty($claimId)) {
      return FALSE;
    }
    // only makes sense if claim is project, then case can be retrieved
    $claim = new CRM_Expenseclaims_BAO_Claim();
    $caseId = $claim->getProjectClaimCaseId($claimId);
    if ($caseId) {
      // now get case client and country of case client
      $caseClientId = CRM_Threepeas_Utils::getCaseClientId($caseId);
      if ($caseClientId) {
        $countryId = CRM_Threepeas_BAO_PumCaseRelation::getCustomerCountry($caseClientId);
        if ($countryId) {
          return $countryId;
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to check if contact is senior project officer
   * (has an active relationship of the type senior project officer with the country of the customer
   * of the main activity to which the claim is linked)
   *
   * @param $contactId
   * @param $claimId
   * @return bool
   */
  public static function isSeniorProjectOfficer($contactId, $claimId) {
    if (!empty($contactId) && !empty($claimId)) {
      $config = CRM_Expenseclaims_Config::singleton();
      $countryId = self::getCountryForClaimCustomer($claimId);
      if ($countryId) {
        $count = civicrm_api3('Relationship', 'getcount', array(
          'is_active' => 1,
          'relationship_type_id' => $config->getSeniorProjectOfficerRelationshipTypeId(),
          'contact_id_a' => $countryId,
          'contact_id_b' => $contactId
        ));
        if ($count > 0) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to check if contact is CFO
   *
   * @param $contactId
   * @throws Exception when method getPumCfo not found
   * @return bool
   */
  public static function isCFO($contactId) {
    if (!empty($contactId)) {
      if (method_exists('CRM_Threepeas_CaseRelationConfig', 'getPumCfo')) {
        $config = CRM_Threepeas_CaseRelationConfig::singleton();
        if ($contactId == $config->getPumCfo()) {
          return TRUE;
        }
      } else {
        throw new Exception('Could not find the method getPumCfo in CRM_Threepeas_CaseRelationConfig which is required in '
          .__METHOD__.', contact your system administrator');
      }
    }
    return FALSE;
  }

  /**
   * Method to check if contact is CPO
   *
   * @param $contactId
   * @return bool
   * @throws Exception when method getPumCpo not found
   */
  public static function isCPO($contactId) {
    if (!empty($contactId)) {
      if (method_exists('CRM_Expenseclaims_Config', 'getPumCpo')) {
        $config = CRM_Expenseclaims_Config::singleton();
        if ($contactId == $config->getPumCpo()) {
          return TRUE;
        }
      } else {
        throw new Exception('Could not find the method getPumCpo in CRM_Expenseclaims_Config which is required in '
          .__METHOD__.', contact your system administrator');
      }
    }
    return FALSE;
  }

  /**
   * Method to calculate euro amount based on currency amount and exchange rate
   *
   * @param $currencyAmount
   * @param $exchangeRate
   * @return double
   * @throws Exception when currency amount or exchange rate empty
   */
  public static function calculateEuroAmount($currencyAmount, $exchangeRate) {
    if (empty($currencyAmount) || empty($exchangeRate)) {
      throw new Exception('Can not calculate a euro amount without currency amount or exchange rate, one of them empty in '
        .__METHOD__.', contact your system administrator');
    }
    return round(($currencyAmount * $exchangeRate), 2);
  }

  /**
   * Method to get my role (for a specific claim)
   *
   * @param $claimId
   * @param $contactId
   * @return bool|string
   */
  public static function getMyRole($claimId, $contactId) {
    $config = CRM_Expenseclaims_Config::singleton();
    if (self::isCFO($contactId) == TRUE) {
      try {
        return civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => $config->getClaimLevelOptionGroup('id'),
          'name' => 'cfo',
          'return' => 'value'
        ));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    if (self::isCPO($contactId) == TRUE) {
      try {
        return civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => $config->getClaimLevelOptionGroup('id'),
          'name' => 'cpo',
          'return' => 'value'
        ));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    if (self::isSeniorProjectOfficer($contactId, $claimId) == TRUE) {
      try {
        return civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => $config->getClaimLevelOptionGroup('id'),
          'name' => 'senior_project_officer',
          'return' => 'value'
        ));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    if (self::isProjectOfficer($contactId, $claimId) == TRUE) {
      try {
        return civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => $config->getClaimLevelOptionGroup('id'),
          'name' => 'project_officer',
          'return' => 'value'
        ));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    return FALSE;
  }

  /**
   * Method to find the senior project officer for a country
   *
   * @param int $countryId
   * @return bool|int
   */
  public static function getSeniorProjectOfficerForCountry($countryId) {
    if (empty($countryId)) {
      return FALSE;
    }
    $config = CRM_Expenseclaims_Config::singleton();
    try {
      return civicrm_api3('Relationship', 'getvalue', array(
        'relationship_type_id' => $config->getSeniorProjectOfficerRelationshipTypeId(),
        'contact_id_a' => $countryId,
        'is_active' => 1,
        'return' => 'contact_id_b'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to find the  project officer for a country
   *
   * @param int $countryId
   * @return bool|int
   */
  public static function getProjectOfficerForCountry($countryId) {
    if (empty($countryId)) {
      return FALSE;
    }
    $config = CRM_Expenseclaims_Config::singleton();
    try {
      $relationships = civicrm_api3('Relationship', 'get', array(
        'relationship_type_id' => $config->getProjectOfficerRelationshipTypeId(),
        'contact_id_a' => $countryId,
        'is_active' => 1,
        'return' => 'contact_id_b'
      ));
      // take the one that is not on a case
      foreach ($relationships['values'] as $relationship) {
        if (!isset($relationship['case_id'])) {
          return $relationship['contact_id_b'];
        }
      }
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }
}