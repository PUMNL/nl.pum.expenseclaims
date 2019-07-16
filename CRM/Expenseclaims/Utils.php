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
   * Method to check if contact is a project officer
   *
   * @param $contactId
   * @param $claimId
   * @return bool
   */
  public static function isProjectOfficer($contactId, $claimId='') {
    $isProjectOfficer = FALSE;

    if (!empty($contactId)) {
      $group = civicrm_api('Group', 'get', array(
        'version' => 3,
        'sequential' => 1,
        'title' => 'Project Officers',
      ));

      $params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id' => $contactId,
      );
      $group_contact = civicrm_api('GroupContact', 'get', $params);

      foreach($group_contact['values'] as $key => $value) {
        foreach($value as $key2 => $value2) {
          if (($key2 == 'group_id') && ($value2 == $group['id'])) {
            $isProjectOfficer = TRUE;
          }
        }
      }
    }

    return $isProjectOfficer;
  }

  /**
   * CRM_Expenseclaims_Utils::hasRelationshipOnCase()
   *
   * Method to check if the contact on the claim has a relationship on the case linked to the claim
   *
   * @deprecated 1.3 Was part of CRM_Expenseclaims_Utils::isProjectOfficer(),
   *             but users with same authorization level should now be able to approve claim so separated as deprecated function
   * @param $contactId
   * @param $claimId
   * @return bool
   */
  public static function hasRelationshipOnCase($contactId,$claimId) {
    if (!empty($contactId) && !empty($claimId) && $isProjectOfficer == TRUE) {
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
    //Users with the same role should be able to approve
    $config = CRM_Expenseclaims_Config::singleton();

    if(!empty($contactId) && !empty($claimId)) {
      $contacts = self::getClaimLevelContacts($config->getSeniorProjectOfficerLevelId());

      if(array_key_exists($contactId,$contacts)) {
        return TRUE;
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
   * CRM_Expenseclaims_Form_ClaimLevelContact::userInClaimLevel()
   *
   * Method to check if user is in current claim level
   *
   * @param in $contactId
   * @param int $claimLevelId
   * @return bool
   */
  public static function userInClaimLevel($contactId,$claimLevelId) {
    $userInClaimLevelContacts = FALSE;

    if (!empty($contactId) && !empty($claimLevelId)) {
      $claimLevelContacts = self::getClaimLevelContacts($claimLevelId);
      $userInClaimLevelContacts = array_key_exists($contactId, $claimLevelContacts);
    }

    return $userInClaimLevelContacts;
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
      $result =  civicrm_api3('Relationship', 'get', array(
        'relationship_type_id' => $config->getSeniorProjectOfficerRelationshipTypeId(),
        'contact_id_a' => $countryId,
        'is_active' => 1
      ));

      $num_relationships = 0;

      if(is_array($result['values'])) {
        foreach($result['values'] as $key => $value) {
          if(!isset($result['values'][$key]['case_id'])) {
            //Relationship on country
            if(!empty($result['values'][$key]['contact_id_b'])) {
              $num_relationships++;
              $prof = $result['values'][$key]['contact_id_b'];
            }
          }
        }
      }

      if ($num_relationships > 1) {
        throw new Exception("More then one active Senior Project Officer for Country: ".$countryId.", please make sure there is only one active senior project officer for this country!");
      } else {
        if(!empty($prof)) {
          return $prof;
        } else {
          throw new Exception("Could not find Senior Project Officer for Country $countryId");
        }
      }
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
   * Method to check if the claim was submitted by CC
   * - get activity target contact, assuming there is only one (limit 1)
   * - check if activity target contact is sector coordinator on the case of the claim (case_id in link)
   *
   * @param $claimId
   * @param $caseId
   * @return bool
   */
  public static function isClaimEnteredByCC($claimId, $caseId) {
    $config = CRM_Expenseclaims_Config::singleton();
    $sql = "SELECT contact_id FROM civicrm_activity_contact WHERE activity_id = %1 AND record_type_id = %2 LIMIT 1";
    $claimEnteredById = CRM_Core_DAO::singleValueQuery($sql, array(
      1 => array($claimId, 'Integer'),
      2 => array($config->getTargetRecordTypeId(), 'Integer')
    ));
    if ($claimEnteredById) {
      $count = civicrm_api3('Relationship', 'getcount', array(
        'case_id' => $caseId,
        'relationship_type_id' => $config->getCountryCoordinatorRelationshipTypeId(),
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
   * If $approvalContactId has and higher authorization level then $contactId then this returns to FALSE
   * If $approvalContactId has the same or lower authorization level
   *
   * @todo check if reimplement of $authorizationLevellId is necessary
   *
   * @param $authorizationLevellId - Authorization level of current contact (optional)
   * @param $contactId - Current contact id
   * @param $approvalContactId - Approval contact assigned to the claim
   *
   * @return bool
   */
  public static function checkHasAuthorization($authorizationLevellId='', $contactId='', $approvalContactId='', $claimId) {
    $claimLevelsCurrentUser = array();
    $claimLevelsApprovalContact = array();

    $session = CRM_Core_Session::singleton();
    $currentUser = $session->get('userID');

    if (!empty($currentUser)) {
      $claimLevelsCurrentUser = CRM_Expenseclaims_Utils::getClaimLevelForContact($currentUser);
    }

    $latest_approval_contact = CRM_Expenseclaims_Utils::getLatestApprovalContact($claimId);
    if (!empty($latest_approval_contact)) {
      $claimLevelsApprovalContact = CRM_Expenseclaims_Utils::getClaimLevelForContact($latest_approval_contact);
    }

    if (!empty($claimLevelsCurrentUser) && !empty($claimLevelsApprovalContact)) {
      $max_cl_approvalcontact = max($claimLevelsApprovalContact);
      $max_cl_currentuser = max($claimLevelsCurrentUser);
      //If the highest number (==highest permission) of the current contact is less or equal to the highest permission of the approval contact, user has permission
      //So if approval_contact has a lower number then current user won't have permission
      if($max_cl_currentuser >= $max_cl_approvalcontact) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Method to get the claim level of a contact
   *
   * @param $claimLevelId
   * @param $contactId
   * @return bool
   */
  public static function getClaimLevelForContact($contactId) {
    try {
      $claimlevels = array();
      $sql = 'SELECT * FROM pum_claim_level_contact clc LEFT JOIN pum_claim_level cl ON clc.claim_level_id = cl.id WHERE contact_id = %1';
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array((int)$contactId, 'Integer')));
      $claimlevels = array();
      while ($dao->fetch()) {
        if ($dao->contact_id == $contactId) {
          $claimlevels[] = $dao->level;
        }
      }
      return $claimlevels;
    } catch(Exception $e) {
      return FALSE;
    }
  }

  public static function getClaimLevelContacts($claimLevelId) {
    $contacts = array();
    if(!is_int($claimLevelId)) {
      (int)$claimLevelId = (int)$claimLevelId;
    }
    if(is_int($claimLevelId)) {
      $query = "SELECT * FROM pum_claim_level_contact WHERE claim_level_id = %1";
      $dao = CRM_Core_DAO::executeQuery($query, array(1=>array($claimLevelId, 'Integer')));

      while ($dao->fetch()) {
        try {
          $contacts[$dao->contact_id] = civicrm_api3('Contact', 'getvalue', array(
            'id' => $dao->contact_id,
            'return' => 'display_name'));
        } catch (Exception $ex) {

        }
      }
    } else {
      CRM_Core_Session::debug_log_message('Unable to get contact for this claimLevelId, must be of type integer', TRUE);
    }
    return $contacts;
  }

  /**
   * Method to get the latest (current) approval contact id of the claim
   *
   * @param mixed $claimId
   * @return
   */
  public static function getLatestApprovalContact($claimId) {
    $params_approval_contact = array(
      'version' => 3,
      'sequential' => 1,
      'claim_activity_id' => $claimId,
      'return' => 'approval_contact_id'
    );
    $approval_contact = civicrm_api('ClaimLog', 'get', $params_approval_contact);
    $ids = array();
    foreach($approval_contact['values'] as $value) {
      $ids[$value['id']]=$value['approval_contact_id'];
    }

    $latest_id = max(array_keys($ids));
    $latest_approval_contact = $ids[$latest_id];

    return $latest_approval_contact;
  }

  /**
   * Method to check if a contact has a specified authorization level
   *
   * @param $contactId
   * @return array
   */
  public static function getClaimLinksForContact($contactId, $manageClaim=FALSE) {
    $travelCaseType = civicrm_api3('OptionValue', 'getvalue', array(
      'return' => 'value',
      'name' => 'TravelCase',
      'option_group_id' => 'case_type'
    ));
    $caseStatusRejected = civicrm_api3('OptionValue', 'getvalue', array(
      'return' => 'value',
      'name' => 'Rejected',
      'option_group_id' => 'case_status'
    ));
    $caseStatusDeclined = civicrm_api3('OptionValue', 'getvalue', array(
      'return' => 'value',
      'name' => 'Declined',
      'option_group_id' => 'case_status'
    ));
    $caseStatusError = civicrm_api3('OptionValue', 'getvalue', array(
      'return' => 'value',
      'name' => 'Error',
      'option_group_id' => 'case_status'
    ));
    $caseStatusCancelled = civicrm_api3('OptionValue', 'getvalue', array(
      'return' => 'value',
      'name' => 'Cancelled',
      'option_group_id' => 'case_status'
    ));
    $caseStatusCompleted = civicrm_api3('OptionValue', 'getvalue', array(
      'return' => 'value',
      'name' => 'Completed',
      'option_group_id' => 'case_status'
    ));

    //When an approval contact is in manage claim screen, case status should not be filtered,
    //otherwise when a claim which is submitted just before the case is set to completed, the claim cannot be approved, because it doesn't show up in the list,
    //and the same counts for cancelled projects where people have made costs, despite the fact that the project has been cancelled.
    if ($manageClaim == FALSE) {
      $sql = "SELECT `case`.`id`, `case`.`subject`
              FROM `civicrm_relationship` `relationship`
              INNER JOIN `civicrm_case` `case` ON `case`.`id` = `relationship`.`case_id`
              JOIN  `civicrm_case_pum`  `pum_case`      ON `case`.`id` = `pum_case`.`entity_id` AND `pum_case`.`case_type` in ('A','B','C','P','R','S','F')
              WHERE (`relationship`.`contact_id_a` = %1 OR `relationship`.`contact_id_b` = %1) AND (`case`.`case_type_id` NOT LIKE %2) AND
              (`case`.`status_id` != %3) AND (`case`.`status_id` != %4) AND (`case`.`status_id` != %5) AND (`case`.`status_id` != %6) AND (`case`.`status_id` != %7)
              AND `case`.`is_deleted` = '0'";

      $params[1] = array($contactId, 'Integer');
      $params[2] = array('%' . CRM_Core_DAO::VALUE_SEPARATOR . $travelCaseType . CRM_Core_DAO::VALUE_SEPARATOR . '%','String');
      $params[3] = array($caseStatusRejected,'String');
      $params[4] = array($caseStatusDeclined,'String');
      $params[5] = array($caseStatusError,'String');
      $params[6] = array($caseStatusCancelled,'String');
      $params[7] = array($caseStatusCompleted,'String');

      $dao = CRM_Core_DAO::executeQuery($sql, $params);
    } else {
      $sql = "SELECT `case`.`id`, `case`.`subject`
              FROM `civicrm_relationship` `relationship`
              INNER JOIN `civicrm_case` `case` ON `case`.`id` = `relationship`.`case_id`
              JOIN  `civicrm_case_pum`  `pum_case`      ON `case`.`id` = `pum_case`.`entity_id` AND `pum_case`.`case_type` in ('A','B','C','P','R','S','F')
              WHERE (`relationship`.`contact_id_a` = %1 OR `relationship`.`contact_id_b` = %1) AND (`case`.`case_type_id` NOT LIKE %2) AND
              (`case`.`status_id` != %3)
              AND `case`.`is_deleted` = '0'";

      $params[1] = array($contactId, 'Integer');
      $params[2] = array('%' . CRM_Core_DAO::VALUE_SEPARATOR . $travelCaseType . CRM_Core_DAO::VALUE_SEPARATOR . '%','String');
      $params[3] = array($caseStatusError,'String');

      $dao = CRM_Core_DAO::executeQuery($sql, $params);
    }

    $return = array();
    while ($dao->fetch()) {
      $return[$dao->id] = $dao->subject . ' (Case: ' . $dao->id . ')';
    }

    return $return;
  }

  public static function csvField($value){
    if(!empty($value)) {
      $value = str_replace(';','',$value);
      $value = str_replace(',','',$value);
      $value = str_replace('"','""',$value);
      return '"' . $value . '"';
    } else {
      return '""';
    }
  }
}