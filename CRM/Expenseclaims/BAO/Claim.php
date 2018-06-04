<?php
/**
 * Class BAO Claim (specific activity type)
 *
 * @author Erik Hommel (CiviCooP)
 * @date 17 Feb 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_Claim {

  private $_caseErrorStatusId = NULL;
  private $_countryCoordinatorLinkLabel = NULL;
  private $_hbfLinkLabel = NULL;
  private $_sectorCoordinatorLinkLabel = NULL;
  private $_recruitmentLinkLabel = NULL;
  private $_programmeManagerLinkLabel = NULL;
  private $_aspectAdvisorsLinkLabel = NULL;
  private $_countryCoordinatorLinkValue = NULL;
  private $_hbfLinkValue = NULL;
  private $_sectorCoordinatorLinkValue = NULL;
  private $_recruitmentLinkValue = NULL;
  private $_programmeManagerLinkValue = NULL;
  private $_aspectAdvisorsLinkValue = NULL;
  private $_newClaim = array();


  /**
   * CRM_Expenseclaims_BAO_Claim constructor.
   */
  public function __construct()   {
    try {
      $this->_caseErrorStatusId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'case_status',
        'name' => 'Error',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
    $this->setLinkValuesAndLabels();
  }

  /**
   * Method to set the link values and labels
   */
  private function setLinkValuesAndLabels() {
    $config = CRM_Expenseclaims_Config::singleton();
    $this->_aspectAdvisorsLinkValue = '7164';
    $this->_countryCoordinatorLinkValue = '7160';
    $this->_hbfLinkValue = '7162';
    $this->_programmeManagerLinkValue = '7165';
    $this->_recruitmentLinkValue = '7163';
    $this->_sectorCoordinatorLinkValue = '7161';
    try {
      $this->_aspectAdvisorsLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_aspectAdvisorsLinkValue,
        'return' => 'label'
      ));
      $this->_countryCoordinatorLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_countryCoordinatorLinkValue,
        'return' => 'label'
      ));
      $this->_hbfLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_hbfLinkValue,
        'return' => 'label'
      ));
      $this->_programmeManagerLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_programmeManagerLinkValue,
        'return' => 'label'
      ));
      $this->_recruitmentLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_recruitmentLinkValue,
        'return' => 'label'
      ));
      $this->_sectorCoordinatorLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_sectorCoordinatorLinkValue,
        'return' => 'label'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  public static function isInNonOpenBatch($claimId){
     $config = CRM_Expenseclaims_Config::singleton();
     $sql = "SELECT 1 FROM pum_claim_batch_entity pbe
     JOIN pum_claim_batch pcb ON pcb.id = pbe.batch_id AND pbe.entity_table='civicrm_activity'
     WHERE pbe.entity_id = %1 AND pcb.batch_status_id != %2
     ";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array($claimId, 'Integer'),
      2 => array($config->getOpenBatchStatusId(), 'Integer')));
    if ($dao->fetch()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Method to get claim with id
   * @param $claimId
   * @return bool|Object
   */
  public function getWithId($claimId) {
    $config = CRM_Expenseclaims_Config::singleton();
    $sql = "SELECT act.activity_date_time AS claim_submitted_date, cac.contact_id AS claim_submitted_by,
      cust.{$config->getClaimLinkCustomField('column_name')} AS claim_link,
      cust.{$config->getClaimTotalAmountCustomField('column_name')} AS claim_total_amount,
      cust.{$config->getClaimDescriptionCustomField('column_name')} AS claim_description
      FROM civicrm_activity act
      LEFT JOIN civicrm_activity_contact cac ON act.id = cac.activity_id AND cac.record_type_id = %1
      LEFT JOIN {$config->getClaimInformationCustomGroup('table_name')} cust ON act.id = cust.entity_id
      WHERE act.id = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array($config->getTargetRecordTypeId(), 'Integer'),
      2 => array($claimId, 'Integer')));
    if ($dao->fetch()) {
      return $dao;
    } else {
      return FALSE;
    }
  }

  public function failsDryRunApprove($claimId, $contactId){

    if (!empty($claimId) || !empty($contactId)) {
      // get my role and then my level
        try {
          $myRole = CRM_Expenseclaims_Utils::getMyRole($claimId, $contactId);
          $myLevel = civicrm_api3('ClaimLevel', 'getsingle', array('level' => $myRole));
          $this->preApprovalValidityChecks($claimId, $contactId, $myLevel);
          return FALSE;
        } catch (Exception $ex) {
          return 'Could not pass validation before approval of claim, problem : ' . $ex->getMessage();
        }

    } else {
      return FALSE;
    }
  }

  /**
   * Method to approve a claim, resulting in either next step or final approval
   *
   * @param $claimId
   * @param $contactId
   * @param $actingContactId - Should normally always be current user
   */
  public function approve($claimId, $contactId,$actingContactId) {
    $authorized = FALSE;
    $session = CRM_Core_Session::singleton();
    $currentUser = $session->get('userID');

    if (!empty($claimId) && !empty($currentUser)) {
      // get my role and then my level
      $myRole = CRM_Expenseclaims_Utils::getMyRole($claimId, $currentUser);

      if ($myRole) {
        $myLevel = civicrm_api3('ClaimLevel', 'getsingle', array('level' => $myRole));

        // first check if I can actually approve
        try {
          $this->preApprovalValidityChecks($claimId, $actingContactId, $myLevel);
        } catch (Exception $ex) {
          throw new Exception('Could not pass validation before approval of claim, problem : '.$ex->getMessage());
        }

        //Get approval contact and check if user has same authorization or higher authorization level
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
        $latest_approval_contact = max($ids);

        $params_claim = array(
          'version' => 3,
          'sequential' => 1,
          'id' => $claimId,
        );
        $claim = civicrm_api('Claim', 'getsingle', $params_claim);

        if(CRM_Expenseclaims_Utils::checkHasAuthorization('',$currentUser, $latest_approval_contact) == TRUE) {
          $authorized = TRUE;
        }

        if ($authorized == TRUE) {
          // if my limit is 999999999.99 then final approval
          if ($myLevel['max_amount'] == 999999999.99) {
            $this->finalApprove($claimId, $contactId,$actingContactId);
          } else {

            // if the claim total amount is less than my max amount, final approve else next step
            $totalAmount = $this->getTotalAmount($claimId);

            if ($totalAmount <= $myLevel['max_amount']) {
              $this->finalApprove($claimId, $contactId,$actingContactId);
            } else {
              $this->nextStep($claimId, $contactId, $actingContactId, $myLevel['authorizing_level']);
            }
          }
        } else {
          throw new Exception('Sorry, you are not authorized to approve this claim, please contact someone with a higher authorization or assign this claim to someone with a higher authorization level.');
        }
      } else {
         throw new Exception('Undefined role - cannot Approve');
      }
    } else {
      throw new Exception('claimId or contactId empty when trying to approve claim in '.__METHOD__.', contact your system administrator');
    }
  }

  /**
   * Method to reject a claim
   *
   * @param $claimId
   * @param $contactId
   */
  public function reject($claimId, $contactId,$actingContactId){
    $authorized = FALSE;
    $session = CRM_Core_Session::singleton();
    $currentUser = $session->get('userID');

    if (!empty($claimId) && !empty($currentUser)) {
      $myRole = CRM_Expenseclaims_Utils::getMyRole($claimId, $currentUser);

      if ($myRole) {
        $myLevel = civicrm_api3('ClaimLevel', 'getsingle', array('level' => $myRole));

        // first check if I can actually approve
        try {
          $this->preApprovalValidityChecks($claimId, $actingContactId, $myLevel);
        } catch (Exception $ex) {
          throw new Exception('Could not pass validation before approval of claim, problem : '.$ex->getMessage());
        }

        //Get approval contact and check if user has same authorization or higher authorization level
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
        $latest_approval_contact = max($ids);

        $params_claim = array(
          'version' => 3,
          'sequential' => 1,
          'id' => $claimId,
        );
        $claim = civicrm_api('Claim', 'getsingle', $params_claim);

        if(CRM_Expenseclaims_Utils::checkHasAuthorization('',$currentUser, $latest_approval_contact) == TRUE) {
          $authorized = TRUE;
        }

        if ($authorized == TRUE) {
          //Set status of activity
          $config = CRM_Expenseclaims_Config::singleton();
          $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET '.$config->getClaimStatusCustomField('column_name')
            .' = %1 WHERE entity_id = %2';
          CRM_Core_DAO::executeQuery($sql, array(
            1 => array($config->getRejectedClaimStatusValue(), 'String'),
            2 => array($claimId, 'Integer')));

          try {
            // now update claim log line for this rejection
            $claimLog = civicrm_api3('ClaimLog', 'getsingle', array(
              'claim_activity_id' => $claimId,
              'approval_contact_id' => $contactId));
            civicrm_api3('ClaimLog', 'create', array(
              'id' => $claimLog['id'],
              'new_status_id' => $config->getRejectedClaimStatusValue(),
              'is_payable' => 0,
              'is_approved' => 0,
              'is_rejected' => 1,
              'acting_approval_contact_id' => $actingContactId,
              'processed_date' => date('Y-m-d')));
          } catch (CiviCRM_API3_Exception $ex) {
            throw new Exception('Unable to update status to rejected in '.__METHOD__.', contact your system administrator');
          }

          try {
            //Get mail template
            $params_template = array('msg_title' => 'claim_rejected');
            $result_template = civicrm_api3('MessageTemplate', 'get', $params_template);

            //Get contact id of the user who the claim submitted
            $params_contactofclaim = array(
              'version' => 3,
              'sequential' => 1,
              'id' => $claimId,
            );
            $contactOfClaim = civicrm_api3('Claim', 'get', $params_contactofclaim);
            $ids = array();
            $contactIdOfClaim = '';
            if (is_array($contactOfClaim['values'])) {
              foreach($contactOfClaim['values'] as $value) {
                if(!empty($value['source_contact_id'])) {
                  $contactIdOfClaim = $value['source_contact_id'];
                }
              }
            }

            //Send E-mail to contact
            if($result_template['is_error'] == 0 && !empty($contactIdOfClaim)) {
              //mail claim rejected
              $params_email = array(
                'version' => 3,
                'sequential' => 1,
                'contact_id' => $contactIdOfClaim,
                'template_id' => $result_template['id'],
              );
              $result_email = civicrm_api3('Email', 'send', $params_email);
            }
          } catch (CiviCRM_API3_Exception $ex) {
            throw new Exception('Unable to send e-mail to contact in '.__METHOD__.', contact your system administrator');
          }
        } else {
          throw new Exception('Sorry, you are not authorized to reject this claim, please contact someone with a higher authorization or assign this claim to someone with a higher authorization level.');
        }
      } else {
        throw new Exception('Undefined role - cannot Approve');
      }
    } else {
      throw new Exception('claimId or currentUser empty when trying to final reject claim in '.__METHOD__.', contact your system administrator');
    }
  }

  /**
   * Method to assign a claim to another user
   *
   * @param $claimId
   * @param $currentContactId
   * @param $assigneeContactId
   */
  public function assignToOtherUser($claimId, $currentContactId){
    $claimsAssignToUserURL = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimassigntouser', 'reset=1&claim_id='.$claimId.'&approver_id='.$currentContactId, TRUE);
    CRM_Utils_System::redirect($claimsAssignToUserURL);
  }

  public function sendBackForCorrection($claimId, $currentContactId) {
    if (empty($claimId) || empty($currentContactId)) {
      throw new Exception('ClaimId, or currentContactId empty when trying to send claim back for correction in '.__METHOD__
        .', contact your system administrator');
    }
    $config = CRM_Expenseclaims_Config::singleton();

    $claim_status_waitingcorrection = $config->getWaitingForCorrectionClaimStatusValue();

    if (is_int((int)$claimId) && is_int((int)$claim_status)) {
      $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET pum_claim_status = %1 WHERE entity_id = %2 ORDER BY id DESC LIMIT 1';
      $result = CRM_Core_DAO::executeQuery($sql, array(
        1 => array((int)$claim_status_waitingcorrection, 'Integer'),
        2 => array((int)$claimId, 'Integer')
      ));
      //
      $sql = 'UPDATE pum_claim_log SET new_status_id = %1, is_approved = %2, is_rejected = %3, is_payable = %4, processed_date = %5 WHERE claim_activity_id = %6 ORDER BY id DESC LIMIT 1';
      $result = CRM_Core_DAO::executeQuery($sql, array(
        1 => array((int)$claim_status_waitingcorrection, 'Integer'),
        2 => array((int)0, 'Integer'),
        3 => array((int)0, 'Integer'),
        4 => array((int)0, 'Integer'),
        5 => array(date('YmdHis'),'String'),
        6 => array((int)$claimId, 'Integer')
      ));
    } else {
      throw new Exception('Unable to send claim back for correction');
    }
  }

  /**
   * Method to determine what the next step should be and processing that in the database
   *
   * @param $claimId Claim ID
   * @param $contactId
   * @param $actingContactId Current logged in user Contact ID
   * @param $authorizingLevel
   * @throws Exception when one of the params is empty
   */
  private function nextStep($claimId, $contactId, $actingContactId, $authorizingLevel) {
    if (empty($claimId) || empty($contactId) || empty($authorizingLevel)) {
      throw new Exception('ClaimId, ContactId or AuthorizingLevel empty when trying to determine next claim approval step in '.__METHOD__
        .', contact your system administrator');
    }
    $config = CRM_Expenseclaims_Config::singleton();
    // first complete current log record if there is one or create new one
    try {
      $claimLog = civicrm_api3('ClaimLog', 'getsingle', array(
        'claim_activity_id' => $claimId,
        'approval_contact_id' => $contactId)
      );
      $claimLogCreate = civicrm_api3('ClaimLog', 'create', array(
        'id' => $claimLog['id'],
        'acting_approval_contact_id' => $actingContactId,
        'new_status_id' => $config->getInitiallyApprovedClaimStatusValue(),
        'is_payable' => 0,
        'is_approved' => 1,
        'is_rejected' => 0,
        'processed_date' => date('Y-m-d')
      ));

      if($claimLogCreate['is_error'] == 1) {
        CRM_Core_Error::debug_log_message('Unable to create claim log entry for claim ID: '.$claimId.', contact id: '.$contactId.' authorizing level: '.$authorizingLevel.', unable to find next level contact id', TRUE);
      }

      // now set next log record for the authorizing level contact
      $nextContactId = CRM_Expenseclaims_BAO_ClaimLevel::getNextLevelContactId($claimId, $authorizingLevel);
      if (!empty($nextContactId) && $nextContactId != FALSE) {
        civicrm_api3('ClaimLog', 'create', array(
          'claim_activity_id' => $claimId,
          'approval_contact_id' => $nextContactId,
          'old_status_id' => $config->getInitiallyApprovedClaimStatusValue(),
          'is_approved' => 0,
          'is_payable' => 0,
          'is_rejected' => 0
        ));
      } else {
        CRM_Core_Error::debug_log_message('Unable to set new claim status for claim ID: '.$claimId.', contact id: '.$contactId.' authorizing level: '.$authorizingLevel.', unable to find next level contact id: '.$nextContactId, TRUE);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      CRM_Core_Error::debug_log_message('Failed to complete nextStep for claim ID: '.$claimId.', contact id: '.$contactId.' authorizing level: '.$authorizingLevel.', unable to find next level contact id', TRUE);
    }

    try{
      // finally update claim status to initialy approved
      $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET '.$config->getClaimStatusCustomField('column_name')
        .' = %1 WHERE entity_id = %2';
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($config->getInitiallyApprovedClaimStatusValue(), 'String'),
        2 => array($claimId, 'Integer')));
    } catch (Exception $ex) {
      CRM_Core_Error::debug_log_message('nextStep() failed for claim ID: '.$claimId.', contact id: '.$contactId.' authorizing level: '.$authorizingLevel.' unable to update claim status', TRUE);
    }
  }

  /**
   * Method to process final approval
   *
   * @param $claimId
   * @param $contactId
   * @throws Exception when claim id or contact id empty
   */
  private function finalApprove($claimId, $contactId,$actingContactId) {
    if (empty($claimId) || empty($contactId)) {
      throw new Exception('ClaimId or ContactId empty when trying to final approve claim in '.__METHOD__.', contact your system administrator');
    }

    $config = CRM_Expenseclaims_Config::singleton();

    //Set status of activity
    try {
      $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET '.$config->getClaimStatusCustomField('column_name')
        .' = %1 WHERE entity_id = %2';
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array((int)$config->getApprovedClaimStatusValue(), 'Integer'),
        2 => array((int)$claimId, 'Integer')));
    } catch (Exception $ex) {
      CRM_Core_Error::debug_log_message('finalApprove() failed for claim ID: '.$claimId.', contact id: '.$contactId.' unable to update claim status', TRUE);
    }

    // now update claim log line for this approval
    try {
      $claimLog = civicrm_api3('ClaimLog', 'get', array(
        'claim_activity_id' => $claimId,
        'approval_contact_id' => $contactId));
      $last_claim_log_id = max(array_keys($claimLog['values']));

      if(empty($actingContactId)) {
        $actingContactId = $contactId;
      }
      try {
        $sql = 'UPDATE pum_claim_log SET is_approved = %1, is_rejected = %2, acting_approval_contact_id = %3, is_payable = %4, new_status_id = %5, processed_date = %6 WHERE id = %7 AND claim_activity_id = %8';
        $result = CRM_Core_DAO::executeQuery($sql, array(
          1 => array((int)1, 'Integer'),
          2 => array((int)0, 'Integer'),
          3 => array((int)$actingContactId, 'Integer'),
          4 => array((int)1, 'Integer'),
          5 => array((int)$config->getApprovedClaimStatusValue(),'Integer'),
          6 => array(date('YmdHis'),'String'),
          7 => array((int)$last_claim_log_id,'Integer'),
          8 => array((int)$claimId,'Integer')
        ));
      } catch (Exception $ex) {
        CRM_Core_Error::debug_log_message('finalApprove() failed for claim ID: '.$claimId.', contact id: '.$contactId.' unable to update claim status', TRUE);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      CRM_Core_Error::debug_log_message('finalApprove() failed for claim ID: '.$claimId.', contact id: '.$contactId.' unable to update claim log line '.$ex->getMessage(), TRUE);
    }

    try {
      //Get mail template
      $params_template = array('msg_title' => 'claim_approved');
      $result_template = civicrm_api3('MessageTemplate', 'get', $params_template);

      //Get contact id of the user who the claim submitted
      $params_contactofclaim = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $claimId,
      );
      $contactOfClaim = civicrm_api3('Claim', 'get', $params_contactofclaim);
      $ids = array();
      $contactIdOfClaim = '';
      if (is_array($contactOfClaim['values'])) {
        foreach($contactOfClaim['values'] as $value) {
          if(!empty($value['source_contact_id'])) {
            $contactIdOfClaim = $value['source_contact_id'];
          }
        }
      }

      if($result_template['is_error'] == 0 && !empty($contactIdOfClaim)) {
        //mail claim approved
        $params_email = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $contactIdOfClaim,
          'template_id' => $result_template['id'],
        );
        $result_email = civicrm_api3('Email', 'send', $params_email);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      CRM_Core_Error::debug_log_message('Failed to send approval message for claim ID: '.$claimId.', contact id: '.$contactId.' '.$ex->getMessage(), TRUE);
    }
  }

  /**
   * Method to get the possible claim links for a contact
   * - all main activities where the contact has a role
   * - based on being a project officer, cc, sc, project manager, cfo, ceo, cpo
   *
   * @param int $contactId
   * @return array|bool
   */
  public function getMyLinks($contactId) {
    if (empty($contactId)) {
      return FALSE;
    }
    $config = CRM_Expenseclaims_Config::singleton();
    $countryCoordinatorRelationshipTypeId = $config->getCountryCoordinatorRelationshipTypeId();
    $grantCoordinatorRelationshipTypeId = $config->getGrantCoordinatorRelationshipTypeId();
    $recruitmentTeamRelationshipTypeId = $config->getRecruitmentTeamRelationshipTypeId();
    $sectorCoordinatorRelationshipTypeId = $config->getSectorCoordinatorRelationshipTypeId();

    $result = array();
    // get all case ids and relationship type that are not deleted where the contact has (had) a role
    $caseSql = "SELECT cc.id AS case_id, cc.subject AS case_subject, rel.relationship_type_id
      FROM civicrm_relationship rel LEFT JOIN civicrm_case cc ON rel.case_id = cc.id
      WHERE rel.contact_id_b = %1 AND cc.is_deleted != %2 AND cc.status_id != %3";
    $cases = CRM_Core_DAO::executeQuery($caseSql, array(
      1 => array($contactId, 'Integer'),
      2 => array(1, 'Integer'),
      3 => array($this->_caseErrorStatusId, 'Integer')));
    while ($cases->fetch()) {
      $result['case_id-'.$cases->case_id] = 'Main Activity '.$cases->case_subject;
      // add additional options based on case relationship
      switch ($cases->relationship_type_id) {
        case $countryCoordinatorRelationshipTypeId:
          if (!array_key_exists($this->_countryCoordinatorLinkValue, $result)) {
            $result[$this->_countryCoordinatorLinkValue] = $this->_countryCoordinatorLinkLabel;
          }
          break;
        case $grantCoordinatorRelationshipTypeId:
          if (!array_key_exists($this->_hbfLinkValue, $result)) {
            $result[$this->_hbfLinkValue] = $this->_hbfLinkLabel;
          }
          break;
        case $recruitmentTeamRelationshipTypeId:
          if (!array_key_exists($this->_recruitmentLinkValue, $result)) {
            $result[$this->_recruitmentLinkValue] = $this->_recruitmentLinkLabel;
          }
          break;
        case $sectorCoordinatorRelationshipTypeId:
          if (!array_key_exists($this->_sectorCoordinatorLinkValue, $result)) {
            $result[$this->_sectorCoordinatorLinkValue] = $this->_sectorCoordinatorLinkLabel;
          }
        }
    }
    // add generic options
    if (!array_key_exists($this->_aspectAdvisorsLinkValue, $result)) {
      $result[$this->_aspectAdvisorsLinkValue] = $this->_aspectAdvisorsLinkLabel;
    }
    // add roles based on group membership programmeManagers
    $groupSql = 'SELECT COUNT(*) FROM civicrm_group_contact WHERE contact_id = %1 AND group_id = %2';
    $groupCount = CRM_Core_DAO::singleValueQuery($groupSql, array(
      1 => array($contactId, 'Integer'),
      2 => array($config->getProgrammeManagerGroupId(), 'Integer')
    ));
    if ($groupCount > 0) {
      if (!array_key_exists($this->_programmeManagerLinkValue, $result)) {
        $result[$this->_programmeManagerLinkValue] = $this->_programmeManagerLinkLabel;
      }
    }
    return $result;
  }

  /**
   * Method to update claim
   *
   * @param $params
   * @throws Exception when no claim_id in params
   */
  public function update($params) {
    if (!isset($params['claim_id'])) {
      throw new Exception('Mandatory parameter claim_id missing in array $params in '.__METHOD__.', contact your system administrator');
    }
    if (CRM_Expenseclaims_BAO_Claim::isInNonOpenBatch($params['claim_id'])){
       throw new Exception('Cannot update a claim that is part of a Non Open Batch');
    }

    $config = CRM_Expenseclaims_Config::singleton();
    $clauses = array();
    $clausesParams = array();
    $index = 0;
    // if claim_description has to be updated
    if (isset($params['claim_description'])) {
      $index++;
      $clauses[] = $config->getClaimDescriptionCustomField('column_name').' = %'.$index;
      //max length of description field is 255 characters
      $clausesParams[$index] = array(substr($params['claim_description'],0,255), 'String');
    }
    // if claim_link has to be updated
    if (isset($params['claim_link'])) {
      $index++;
      $clauses[] = $config->getClaimLinkCustomField('column_name').' = %'.$index;
      $clausesParams[$index] = array($params['claim_link'], 'String');
    }
    // if claim type has to be updated
    if (isset($params['claim_type'])) {
      $index++;
      $clauses[] = $config->getClaimTypeCustomField('column_name').' = %'.$index;
      $clausesParams[$index] = array($params['claim_type'], 'String');
    }
    $index++;
    $sql = "UPDATE ".$config->getClaimInformationCustomGroup('table_name')." SET ".implode(',', $clauses)." WHERE entity_id = %".$index;
    $clausesParams[$index] = array($params['claim_id'], 'Integer');
    CRM_Core_DAO::executeQuery($sql, $clausesParams);
  }

  /**
   * Method to update the total amount of the claim with the euro amounts of all claim lines
   *
   * @param $claimId
   */
  public function updateTotalAmount($claimId) {
    if (!empty($claimId)) {
      $totalAmount = 0;
      $claimLines = civicrm_api3('ClaimLine', 'get', array('activity_id' => $claimId));
      foreach ($claimLines['values'] as $claimLineId => $claimLine) {
        $totalAmount = $totalAmount + $claimLine['euro_amount'];
      }
      $config = CRM_Expenseclaims_Config::singleton();
      $totalAmount = round($totalAmount, 2);
      $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET '.
        $config->getClaimTotalAmountCustomField('column_name').' = %1 WHERE entity_id = %2';
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($totalAmount, 'Money'),
        2 => array($claimId, 'Integer')
        ));
    }
    return;
  }

  /**
   * Method to return a case id from the claim link field
   *
   * @param $claimId
   * @return bool|string
   */
  public function getProjectClaimCaseId($claimId) {
    if (!empty($claimId)) {
      $config = CRM_Expenseclaims_Config::singleton();
      $sql = 'SELECT ' . $config->getClaimLinkCustomField('column_name') . ' FROM ' . $config->getClaimInformationCustomGroup('table_name')
        . ' WHERE entity_id = %1 AND ' . $config->getClaimTypeCustomField('column_name') . ' = %2';
      $claimLink = CRM_Core_DAO::singleValueQuery($sql, array(
        1 => array($claimId, 'Integer'),
        2 => array('project', 'String')));
      if ($claimLink) {
        return $claimLink;
      }
    }
    return FALSE;
  }

  /**
   * Method to get total amount of a claim
   *
   * @param $claimId
   * @return bool|string
   */
  public function getTotalAmount($claimId) {
    if (empty($claimId)) {
      return FALSE;
    }
    $config = CRM_Expenseclaims_Config::singleton();
    $sql = 'SELECT '.$config->getClaimTotalAmountCustomField('column_name').' FROM '.$config->getClaimInformationCustomGroup('table_name')
      .' WHERE entity_id = %1';
    $totalAmount = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($claimId, 'Integer')));
    if ($totalAmount) {
      return $totalAmount;
    }
    return FALSE;
  }

  /**
   * Method to create a new claim :
   * - add activity with custom data
   * - add claim log entry for the correct approval contact id
   *
   * @param array $params
   * @return bool
   */
  public function createNew($params) {
    $config = CRM_Expenseclaims_Config::singleton();
    $params['activity_type_id'] = $config->getClaimActivityTypeId();
    // create activity
    $activityParams = array(
      'activity_type_id' => $config->getClaimActivityTypeId(),
      'activity_date_time' => date('Y-m-d', strtotime($params['expense_date'])),
      'status_id' => $config->getCompletedActivityStatusId(),
      'target_contact_id' => $params['claim_contact_id'],
      'subject' => 'Claim entered on website'
    );
    if(key_exists('source_contact_id',$params)){
      $activityParams['source_contact_id'] = $params['source_contact_id'];
    }
    try {
      $activity = civicrm_api3('Activity', 'create', $activityParams);
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
    // then add custom data
    $this->_newClaim = $activity['values'][$activity['id']];
    $this->createCustomData($params);
    $this->linkActivityToClaim();
    // finally determine who needs to approve claim and create claim log entry
    return $this->_newClaim;
  }

  /**
   * Method to find approval contact and set claim log for new claim
   */
  public function createFirstStep($params) {
    $sqlParams = array(1 => array($params['id'], 'Integer'));
    $countProcessed = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM pum_claim_log WHERE claim_activity_id = %1", $sqlParams);
    $config = CRM_Expenseclaims_Config::singleton();

    $tx = new CRM_Core_Transaction();

    if (!empty($countProcessed) && $countProcessed > 0) {
      try {
        //Update status in custom fields table
        $sql1 = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET pum_claim_status = %1 WHERE entity_id = %2 ORDER BY id DESC LIMIT 1';
        $dao = CRM_Core_DAO::executeQuery($sql1, array(
          1 => array((int)$config->getWaitingForApprovalClaimStatusValue(), 'Integer'),
          2 => array($params['id'], 'Integer')
        ));

        //Update status in claim log: Only the last entry!! (desc limit 1)
        $sql2 = 'UPDATE pum_claim_log SET old_status_id = new_status_id, new_status_id = %1, processed_date = %3 WHERE claim_activity_id = %2 ORDER BY id DESC LIMIT 1';
        $dao = CRM_Core_DAO::executeQuery($sql2, array(
          1 => array((int)$config->getWaitingForApprovalClaimStatusValue(), 'Integer'),
          2 => array($params['id'], 'Integer'),
          3 => array(NULL, 'Date')
        ));

        //Update status on activity, status on activity is not used on new claims,
        //but to prevent the activity to show up in 'my activities' the activity is set to status completed
        $sql3 = 'UPDATE civicrm_activity SET status_id = %1 WHERE id = %2 ORDER BY id DESC LIMIT 1';
        $dao = CRM_Core_DAO::executeQuery($sql3, array(
          1 => array((int)$config->getCompletedActivityStatusId(), 'Integer'),
          2 => array($params['id'], 'Integer')
        ));

        return TRUE;
      } catch (Exception $ex) {
        $tx->rollBack();
        CRM_Core_Error::debug_log_message('Unable to set claim status 1 for claim ID: '.$params['id'], TRUE);
        return FALSE;
      }
    } else {
      // find approval contact based on claim link
      $approvalContactId = $this->findFirstApprovalContact($params);
      try {
        //Update status in custom fields table
        $sql1 = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET pum_claim_status = %1 WHERE entity_id = %2 ORDER BY id DESC LIMIT 1';
        $dao = CRM_Core_DAO::executeQuery($sql1, array(
          1 => array((int)$config->getWaitingForApprovalClaimStatusValue(), 'Integer'),
          2 => array($params['id'], 'Integer')
        ));
      } catch (Exception $ex) {
        $tx->rollBack();
        CRM_Core_Error::debug_log_message('Unable to set claim status 2 for claim ID: '.$params['id'], TRUE);
        return FALSE;
      }

      if ($approvalContactId) {
        //Create claim log entry
        civicrm_api3('ClaimLog', 'create', array(
          'claim_activity_id' => $params['id'],
          'approval_contact_id' => $approvalContactId,
          'new_status_id' => $config->getWaitingForApprovalClaimStatusValue(),
          'old_status_id' => $config->getWaitingForApprovalClaimStatusValue(),
          'processed_date' => NULL,
          'is_approved' => 0,
          'is_payable' => 0,
          'is_rejected' => 0
        ));

        try {
          //Update status on activity, status on activity is not used on new claims,
          //but to prevent the activity to show up in 'my activities' the activity is set to status completed
          $sql = 'UPDATE civicrm_activity SET status_id = %1 WHERE id = %2 ORDER BY id DESC LIMIT 1';
          $dao = CRM_Core_DAO::executeQuery($sql, array(
            1 => array((int)$config->getCompletedActivityStatusId(), 'Integer'),
            2 => array((int)$params['id'], 'Integer')
          ));
        } catch (Exception $ex) {
          $tx->rollBack();
          CRM_Core_Error::debug_log_message('Unable to set claim status 3 for claim ID: '.$params['id'], TRUE);
          return FALSE;
        }
        return TRUE;
      } else {
        $errorTxt = array();
        foreach ($this->_newClaim as $key => $value) {
          $errorTxt[] = 'parameter '.$key.' and value '.$value;
        }
        throw new Exception('Could not create a claim because no approval contact for the claim could be identified in '.__METHOD__
          .' with values '.implode('; ', $errorTxt));
      }
    }
  }

  /**
   * Method to determine the first approval contact based on claim type
   *
   * @return bool|int
   */
  public function findFirstApprovalContact($params) {

    /* fyi interest the 7 series is of the old organisation setup
       the 3 series is the new organisation setup
       in the export the grouping field is used in case the organisation
       setup changes again */

    switch ($params['claim_type']) {
      // if claim type is 7162 or 7165 approval by CFO
      // 7162 is Hans Blankerd Fonds
      case "7162":
        $config =  CRM_Expenseclaims_Config::singleton();
        return $config->getPumCfo();
        break;
      // 7165 is Programma manager
      case "7165":
        $config =  CRM_Expenseclaims_Config::singleton();
        return $config->getPumCfo();
        break;
      // 7161 is Sector Coordinator
      case "7161":
        $config =  CRM_Expenseclaims_Config::singleton();
        return $config->getPumCpo();
        break;
      // 7160 is Country Coordinator
      case "7160":
        $config =  CRM_Expenseclaims_Config::singleton();
        return $config->getPumCfo();
        break;
      // 7163 is Recruitment (RCT)
      case "7163":
        $config = CRM_Expenseclaims_Config::singleton();
        return $config->getPumCpo();
        break;
      // 7164 is Other Roles
      case "7164":
        $config = CRM_Expenseclaims_Config::singleton();
        return $config->getPumCfo();
        break;
      // 3101 is Region Coordinator
      case "3101":
        $config = CRM_Expenseclaims_Config::singleton();
        return $config->getPumCfo();
        break;
      // 3201 is Theme Coordinator
      case "3201":
        $config = CRM_Expenseclaims_Config::singleton();
        return $config->getPumCpo();
        break;
      // if project, check my role (if SC then approval by CPO) else approval based on levels
      case "project":
        if (CRM_Expenseclaims_Utils::isClaimEnteredBySC($params['id'], $params['claim_link']) == TRUE) {
          $config = CRM_Expenseclaims_Config::singleton();
          return $config->getPumCpo();
        }
        else {
          if (CRM_Expenseclaims_Utils::isCTMorPDVCase($params['claim_link'])) {
            $config = CRM_Expenseclaims_Config::singleton();
            return $config->getPumCfo();
          }
          else {
            return $this->findFirstApprovalProjectContact($params['claim_link']);
          }
        }
        break;
      default:
        return FALSE;
        break;
    }
  }

  /**
   * Method to find out who needs to approve the new claim for a main activity
   * - always fall back to cfo if no other contact found
   * - always use project officer as first approval step
   *
   * @return mixed
   */
  private function findFirstApprovalProjectContact($claim_link) {
    // in case of doubt go to CFO
    $config = CRM_Threepeas_CaseRelationConfig::singleton();
    $contactId = $config->getPumCfo();
    $config = CRM_Expenseclaims_Config::singleton();
    // get project officer for case
    $relation = civicrm_api3('Relationship', 'get', array(
      'relationship_type_id' => $config->getProjectOfficerRelationshipTypeId(),
      'case_id' => $claim_link,
      'options' => array('limit' => 1)
    ));
    if (!empty($relation['values'][$relation['id']]['contact_id_b'])) {
      $contactId = $relation['values'][$relation['id']]['contact_id_b'];
    }
    return $contactId;
  }

  /**
   * Function to insert custom record for claim activity and save in $this->_newClaim
   *
   * @param $params
   */
  private function createCustomData($params) {
    $config = CRM_Expenseclaims_Config::singleton();
    $sqlClauses = array('entity_id = %1');
    $sqlParams[1] = array($this->_newClaim['id'], 'Integer');
    $sqlClauses[] = $config->getClaimStatusCustomField('column_name').' = %2';
    $sqlParams[2] = array($config->getNotSubmittedClaimStatusValue(), 'String');
    $index = 2;
    if (isset($params['claim_type'])) {
      $index++;
      $sqlClauses[] = $config->getClaimTypeCustomField('column_name').' = %'.$index;
      $sqlParams[$index] = array($params['claim_type'], 'String');
      $this->_newClaim['claim_type'] = $params['claim_type'];
    }
    if (isset($params['claim_link'])) {
      $index++;
      $sqlClauses[] = $config->getClaimLinkCustomField('column_name').' = %'.$index;
      $sqlParams[$index] = array($params['claim_link'], 'String');
      $this->_newClaim['claim_link'] = $params['claim_link'];
    }
    if (isset($params['claim_total_amount'])) {
      $index++;
      $sqlClauses[] = $config->getClaimTotalAmountCustomField('column_name').' = %'.$index;
      $sqlParams[$index] = array($params['claim_total_amount'], 'Money');
      $this->_newClaim['claim_total_amount'] = $params['claim_total_amount'];
    }
    if (isset($params['claim_description'])) {
      $index++;
      $sqlClauses[] = $config->getClaimDescriptionCustomField('column_name').' = %'.$index;
      $sqlParams[$index] = array($params['claim_description'], 'String');
      $this->_newClaim['claim_description'] = $params['claim_description'];
    }
    $sql = 'INSERT INTO '.$config->getClaimInformationCustomGroup('table_name').' SET '.implode(', ', $sqlClauses);
    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  private function linkActivityToClaim(){

    $id=$this->_newClaim['id'];
    civicrm_api3('activity','create',array(
      'id' => $id,
      'details' => "<a href='/civicrm/pumexpenseclaims/form/claim?action=view&id=$id'>View Claim</a>"
    ));
  }

  /**
   * Method to process buildForm hook:
   * - hide activity_date_time with jQuery in template in update mode
   *
   * @param $formName
   * @param $form
   */
  public static function buildForm($formName, &$form) {
    if($formName == 'CRM_Activity_Form_ActivityLinks') {
      /* remove the Create Claim Option form the activity menu
         by removing it from the smarty template
         https://civicoop.plan.io/issues/1091
      */
      $activityTypes = $form->get_template_vars('activityTypes');
      $claimKey = array_search('Claim', $activityTypes);
      if(isset($claimKey)){
        unset($activityTypes[$claimKey]);
      }
      $form->assign('activityTypes',$activityTypes);
    }
    if ($formName == 'CRM_Activity_Form_Activity') {
      if (isset($form->_activityTypeName) && $form->_activityTypeName == 'Claim') {
        CRM_Core_Region::instance('page-body')->add(array('template' => 'CRM/Expenseclaims/ClaimActivityDateTime.tpl'));
      }
    }
    if($formName == 'CRM_Expenseclaims_Form_ClaimAssignToUser') {
      $valuesToArray = self::retrieveValuesFromURL($form->controller->_entryURL);

      if (!empty($valuesToArray['approverid'])) {
        $defaults = array('claim_assign_contacts' => $valuesToArray['approverid']);
        $form->setDefaults($defaults);
      }
    }
  }

  /**
   * Method to check in advance if approval can take place
   *
   * @param $claimId
   * @param $contactId
   * @param $claimLevel
   * @return bool
   * @throws Exception when validity check not passed
   */
  private function preApprovalValidityChecks($claimId, $contactId, $claimLevel) {
    $config = CRM_Expenseclaims_Config::singleton();
    // first check if the contact actually is allowed for the claim level TO change claime level screen
    $sql = "SELECT COUNT(*) FROM pum_claim_level_contact WHERE claim_level_id = %1 AND contact_id = %2";
    $count = CRM_Core_DAO::singleValueQuery($sql, array(
      1 => array($claimLevel['id'], 'Integer'),
      2 => array($contactId, 'Integer')
    ));

    // then if the claim is a main activity claim:
    try {
      $claim = civicrm_api3('Claim', 'getsingle', array('id' => $claimId));
      if ($claim['claim_type_id'] == 'project') {
        // check if there is a case for the claim
        try {
          $caseCount = civicrm_api3('Case', 'getcount', array('id' => $claim['claim_linked_to']));
          if ($caseCount != 1) {
            throw new Exception(ts('Could not find a single case with ID '.$claim['claim_linked_to'].' for claim id '.$claimId));
          }
        } catch (CiviCRM_API3_Exception $ex) {
          throw new Exception(ts('Could not find case with ID '.$claim['claim_linked_to'].' for claim id'.$claimId));
        }
        // check there is a customer for the case
        $caseCustomerId = CRM_Threepeas_Utils::getCaseClientId($claim['claim_linked_to']);
        if (empty($caseCustomerId)) {
          throw new Exception(ts('Could not find a customer for case ID '.$claim['claim_linked_to'].' and claim ID '.$claimId));
        }
        // check there is a country for the customer
        $countryId = CRM_Expenseclaims_Utils::getCountryForClaimCustomer($claimId);
        if (!$countryId) {
          throw new Exception(ts('Could not find a country for customer ID '.$caseCustomerId.' of case ID '.$claim['claim_linked_to'].' linked to claim '.$claimId));
        }
        // check if the country has a project officer
        $projectOfficerId = CRM_Expenseclaims_Utils::getProjectOfficerForCountry($countryId);
        if (!$projectOfficerId) {
          throw new Exception(ts('There is no project officer for the country for customer ID '.$caseCustomerId.' linked to claim ID '.$claimId));
        }
      }
      // if the max amount of my level is not enough
      if ($claim['claim_total_amount'] > $claimLevel['max_amount']) {
        // check if CFO exists if that is next level
        if ($claimLevel['authorizing_level'] == $config->getCfoLevelId()) {
          if (!$config->getPumCfo()) {
            throw new Exception(ts('No contact found with authorization level CFO'));
          }
        }
        // check Senior Project Officer exists, is on the country and has correct claim level
        if (!CRM_Expenseclaims_Utils::getSeniorProjectOfficerForCountry($countryId)) {
          throw new Exception(ts('Could not find a valid Senior Project Officer with the correct authorization level for country '
            .$countryId.' with customer '.$caseCustomerId.' linked to claim ID '.$claimId));
        }
      }
    } catch(CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not find the claim with id '.$claimId.' in the database! Contact your system administrator'));
    }
  }

  /**
   * Function to delete a claim by id
   *
   * @param int $claimId
   * @throws Exception when claimId is empty
   */
  public static function deleteWithId($claimId) {
    if (empty($claimId)) {
      throw new Exception(ts('claim id can not be empty when attempting to delete a claim in '.__METHOD__));
    } else {
      try {
        //Remove all claim line logs
        $claimLineLog = new CRM_Expenseclaims_BAO_ClaimLineLog();
        $claimLineLog->deleteWithActivityId($claimId);
      } catch (Exception $e) {
        return 10;
      }

      try {
        //Remove all claim lines
        $claimLines = new CRM_Expenseclaims_BAO_ClaimLine();
        $claimLines->deleteWithActivityId($claimId);
      } catch (Exception $e) {
        return 20;
      }

      try {
        //Remove all from claim log
        $claimLog = new CRM_Expenseclaims_BAO_ClaimLog();
        $claimLog->deleteWithActivityId($claimId);
      } catch (Exception $e) {
        return 30;
      }

      $remove_claim_params = array(
        'version' => 3,
        'sequential' => 1,
        'id' => (int)$claimId,
      );
      $remove_claim = civicrm_api('Activity', 'delete', $remove_claim_params);

      if($remove_claim['is_error']==0 && $remove_claim['count'] == 1) {
        return TRUE;
      } else {
        return FALSE;
      }
    }
  }

  /**
   * Method to retrieve values from the URL
   *
   * @param array $urlParams
   * @return
   */
  private function retrieveValuesFromURL($entryURL) {
    $queryStr = parse_url($entryURL, PHP_URL_QUERY);
    $queryStrStripped = str_replace('amp;','&',$queryStr);
    parse_str($queryStrStripped, $urlParams);

    return $urlParams;
  }
}