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

      $result = civicrm_api3('Claim','get',array('id'=> $claimId));
      $claim_link_to = $result['values'][$result['id']]['claim_linked_to'];
      $count = civicrm_api3('Relationship', 'getcount', array(
        'relationship_type_id' => $config->getProjectOfficerRelationshipTypeId(),
        'contact_id_b' => $contactId,
        'case_id' =>  $claim_link_to,
        'options' => array('limit' => 1)
      ));
      if ($count > 0) {
          return TRUE;
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
      $countryId = self::getCountryForClaimCustomer($claimId);
      if ($contactId == self::getSeniorProjectOfficerForCountry($countryId)) {
        return TRUE;
      }
    }
    else {
       return FALSE;
    }
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
      $config = CRM_Expenseclaims_Config::singleton();
      $pumContactCfo = $config->getPumCfo();
      if ($contactId == $config->getPumCfo()) {
        return TRUE;
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
      $config = CRM_Expenseclaims_Config::singleton();
      if ($contactId == $config->getPumCpo()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Method to calculate euro amount based on currency amount and exchange rate
   *
   * @param $currencyAmount
   * @param $currencyId
   * @return double
   * @throws Exception when currency amount or exchange rate empty
   */
  public static function calculateEuroAmount($currencyAmount, $currencyId) {
    if (empty($currencyAmount) || empty($currencyId)) {
      throw new Exception('Can not calculate a euro amount without currency amount or currency id, one of them empty in '
        .__METHOD__.', contact your system administrator');
    }
    // only if currency is not EURO
    $config = CRM_Expenseclaims_Config::singleton();
    if ($currencyId != $config->getEuroCurrencyId()) {

      try {
        $result = civicrm_api3('Currency', 'convert', array(
          'currency_id' => $currencyId,
          'amount' => $currencyAmount
        ));
        $euroAmount = $result['euro_amount'];
      } catch (CiviCRM_API3_Exception $ex) {
        $euroAmount = $currencyAmount;
      }
    } else {
      $euroAmount = $currencyAmount;
    }
    return $euroAmount;
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
      $result =  civicrm_api3('Relationship', 'getvalue', array(
        'relationship_type_id' => $config->getSeniorProjectOfficerRelationshipTypeId(),
        'contact_id_a' => $countryId,
        'is_active' => 1,
        'return' => 'contact_id_b'
      ));
      return $result;
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception("Could not find Senior Project Officer for Country $countryId");
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

  /**
   * Method to check if the claim was submitted by SC
   * - get activity target contact, assuming there is only one (limit 1)
   * - check if activity target contact is sector coordinator on the case of the claim (case_id in link)
   *
   * @param $claimId
   * @param $caseId
   * @return bool
   */
  public static function isClaimEnteredBySC($claimId, $caseId) {
    $config = CRM_Expenseclaims_Config::singleton();
    $sql = "SELECT contact_id FROM civicrm_activity_contact WHERE activity_id = %1 AND record_type_id = %2 LIMIT 1";
    $claimEnteredById = CRM_Core_DAO::singleValueQuery($sql, array(
      1 => array($claimId, 'Integer'),
      2 => array($config->getTargetRecordTypeId(), 'Integer')
    ));
    if ($claimEnteredById) {
      $count = civicrm_api3('Relationship', 'getcount', array(
        'case_id' => $caseId,
        'relationship_type_id' => $config->getSectorCoordinatorRelationshipTypeId(),
        'contact_id_b' => $claimEnteredById
      ));
      if ($count > 0) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Described in Smit Issue #80
   * A CTM Case or a PDV case must be dispatched directory to the CFO
   * @param $claimId
   */
  public static function isCTMorPDVCase($claimId){
    $sql = "SELECT case_type FROM civicrm_case_pum WHERE entity_id = %1";
    $claimType = CRM_Core_DAO::singleValueQuery($sql, array(
      1 => array($claimId, 'Integer'),
    ));
    if($claimType=='C'||$claimType=='P'){
      return TRUE;
    } else {
      return FALSE;
    }


  }

  /**
   * Method to check if a certain authorization record already exists
   *
   * @param $authorizationData
   * @return bool
   */
  public static function checkAuthorizationExists($authorizationData) {
    // need to check if there already is an authorization for the max amount with each selected valid type and each selected
    // main activity
    return FALSE;
  }

  /**
   * Method to check if a contact has a specified authorization level
   *
   * @param $claimLevelId
   * @param $contactId
   * @return bool
   */
  public static function checkHasAuthorization($authorizationLevellId, $contactId) {
    $sql = 'SELECT * FROM pum_claim_level_contact WHERE claim_level_id = %1';
    $contacts = CRM_Core_DAO::executeQuery($sql, array(1 => array($authorizationLevellId, 'Integer')));
    while ($contacts->fetch()) {
      if ($contacts->contact_id == $contactId) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Method to check if a contact has a specified authorization level
   *
   * @param $contactId
   * @return array
   */
  public static function  getClaimLinksForContact($contactId) {
    $travelCaseType = civicrm_api3('OptionValue', 'getvalue', array(
      'return' => 'value',
      'name' => 'TravelCase',
      'option_group_id' => 'case_type'
    ));
    $sql = "SELECT `case`.`id`, `case`.`subject` 
          FROM `civicrm_relationship` `relationship` 
          INNER JOIN `civicrm_case` `case` ON `case`.`id` = `relationship`.`case_id`
          JOIN  `civicrm_case_pum`  `pum_case`      ON `case`.`id` = `pum_case`.`entity_id` AND `pum_case`.`case_type` in ('A','B','C','P','R','S')
          WHERE (`relationship`.`contact_id_a` = %1 OR `relationship`.`contact_id_b` = %1) AND `case`.`case_type_id` NOT LIKE %2 
          ";
    $params[1] = array($contactId, 'Integer');
    $params[2] = array(
      '%' . CRM_Core_DAO::VALUE_SEPARATOR . $travelCaseType . CRM_Core_DAO::VALUE_SEPARATOR . '%',
      'String'
    );
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    $return = array();
    while ($dao->fetch()) {
      $return[$dao->id] = $dao->subject . ' (Case: ' . $dao->id . ')';
    }
    return $return;
  }

  public static function csvField($value){
    if(!empty($value)) {
      return '"' . str_replace('"','""',$value) . '"';
    } else {
      return '""';
    }
  }
}