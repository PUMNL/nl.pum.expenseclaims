<?php

/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 20 Feb 2017
 * @license AGPL-3.0
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Expenseclaims_Form_Claim extends CRM_Core_Form {

  protected $_claimId = NULL;

  protected $_claimLinkList = [];

  protected $_claim = [];
  private $_approverId = NULL;

  /**
   * Method to build the QuickForm
   */
  public function buildQuickForm() {
    //Check permission
    if (($currentUser != $this->_approverId) && (CRM_Core_Permission::check(array(array('view others claims','manage others claims'))) == FALSE)) {
      CRM_Core_Session::setStatus('Sorry, you are not allowed to view/manage this claim', 'Claims', 'error');
      parent::buildQuickForm();
    } else {
      if ($currentUser == $this->_approverId) {
        $this->addFormElements();
      } else if(($currentUser != $this->_approverId) && (CRM_Core_Permission::check(array(array('view others claims','manage others claims'))) == TRUE)) {
        $this->addFormElements();
      }
    }

    parent::buildQuickForm();
  }

  private function addFormElements() {
    // add form elements
    $this->add('hidden', 'claim_id');
    $this->add('hidden', 'approverid',$this->_approverId);
    if (isset($this->_claim->claim_link)) {
      if($this->_action==CRM_CORE_Action::UPDATE) {
        $this->add('select', 'claim_link', ts('Link'), $this->_claimLinkList, TRUE);
      } else if ($this->_action==CRM_CORE_Action::VIEW){
        $this->assign('claimLinkDescription', $this->_claimLinkList[$this->_claim->claim_link]);
      }
    }
    $this->add('text', 'claim_submitted_by', ts('Claimed By'));
    $this->add('text', 'claim_submitted_date', ts('Date Submitted'));
    $this->add('textarea', 'claim_description', ts('Remark'), TRUE);
    $this->add('text', 'claim_total_amount', ts('Total Amount'), TRUE);
    if($this->_action==CRM_CORE_Action::UPDATE) {
      $this->addButtons([
        ['type' => 'submit', 'name' => ts('Save'), 'isDefault' => TRUE,],
        ['type' => 'next', 'name' => ts('Save and Approve')],
        [
          'type' => 'next',
          'subName' => 'reject',
          'name' => ts('Save and Reject'),
        ],
        [
          'type' => 'next',
          'subName' => 'assigntouser',
          'name' => ts('Assign to another user'),
        ],
        ['type' => 'cancel', 'name' => ts('Cancel')],
      ]);
    } else if ($this->_action==CRM_CORE_Action::VIEW) {
      $this->addButtons([
        ['type' => 'cancel', 'name' => ts('Cancel')],
      ]);
    }

    $this->addClaimLines();
    $this->addAttachements();
    $this->addAuditTrail();

    $session = CRM_Core_Session::singleton();
    if($session->get('userID')==$this->_approverId){
      $this->assign('werkbakje','Eigen Werkbakje');
    } else {
      $this->assign('werkbakje','Andermans Werkbakje');
    };

    if($this->_action==CRM_Core_Action::UPDATE) {
      if ($this->_approverId == $session->get('userID')) {
        $whoseClaims = 'myself';
      }
      else {
        $whoseClaims = civicrm_api3('contact', 'getvalue', [
          'id' => $this->_approverId,
          'return' => 'display_name'
        ]);
      }
      $this->assign('whoseClaims', $whoseClaims);
    }
  }
  /**
   * Method to get the claim lines of the claim and put them on the form
   */
  private function addClaimLines() {
    $result = [];
    $config = CRM_Expenseclaims_Config::singleton();
    $claimLines = CRM_Expenseclaims_BAO_ClaimLine::getValues(['activity_id' => $this->_claimId]);
    foreach ($claimLines as $claimLineId => $claimLine) {
      $result[$claimLineId] = [];
      if (isset($claimLine['expense_date'])) {
        $result[$claimLineId]['date'] = $claimLine['expense_date'];
      }
      if (isset($claimLine['expense_type'])) {
        try {
          $result[$claimLineId]['type'] = civicrm_api3('OptionValue', 'getvalue', [
            'option_group_id' => $config->getClaimLineTypeOptionGroup('id'),
            'value' => $claimLine['expense_type'],
            'return' => 'label',
          ]);
        } catch (CiviCRM_API3_Exception $ex) {
        }
      }
      if (isset($claimLine['currency_id'])) {
        $sql = 'SELECT name FROM civicrm_currency WHERE id = %1';
        $result[$claimLineId]['currency'] = CRM_Core_DAO::singleValueQuery($sql, [
          1 => [
            $claimLine['currency_id'],
            'Integer',
          ],
        ]);
      }
      if (isset($claimLine['currency_amount'])) {
        $result[$claimLineId]['currency_amount'] = $claimLine['currency_amount'];
      }
      if (isset($claimLine['euro_amount'])) {
        $result[$claimLineId]['euro_amount'] = $claimLine['euro_amount'];
      }
      if (isset($claimLine['exchange_rate'])) {
        $result[$claimLineId]['exchange_rate'] = $claimLine['exchange_rate'];
      }
      if (isset($claimLine['description'])) {
        $result[$claimLineId]['description'] = $claimLine['description'];
      }
      // add action item
      if (!empty($result[$claimLineId])&& $this->_action==CRM_CORE_Action::UPDATE) {
        $editUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimline', 'action=update&id=' . $claimLineId.'&approverid='.$this->_approverId, TRUE);
        $result[$claimLineId]['actions'][] = '<a class="action-item" title="Edit" href="' . $editUrl . '">Edit</a>';
      }
    }
    $this->assign('claimLines', $result);
  }

  private function addAttachements() {
    $result = [];
    $attachments = CRM_Core_BAO_File::getEntityFile('civicrm_activity', $this->_claimId);
    foreach ($attachments as $attachmentId => $attachment) {
      $result[$attachmentId] = $attachment['href'];
    }
    $this->assign('attachments', $result);
  }

  /**
   * create the auditTrail information
   */
  private function addAuditTrail() {

    $config = CRM_Expenseclaims_Config::singleton();

    $sql = "SELECT l.id
,               c.display_name approver
,               ac.display_name acting_approver
,               l.processed_date
,               l.is_approved
,               l.is_rejected
,               l.is_payable
,               csov.label AS old_status
,               nsov.label AS new_status
FROM            pum_claim_log l
LEFT JOIN civicrm_contact c ON (c.id = l.approval_contact_id)
LEFT JOIN civicrm_contact ac ON (ac.id = l.acting_approval_contact_id)
LEFT JOIN civicrm_option_value csov ON  (l.old_status_id = csov.value collate utf8_general_ci AND csov.option_group_id = %2)
LEFT JOIN civicrm_option_value nsov ON  (l.new_status_id = nsov.value collate utf8_general_ci AND nsov.option_group_id = %2)
where             l.claim_activity_id = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, [
      '1' => [$this->_claimId, 'Integer'],
      '2' => array($config->getClaimStatusOptionGroup('id'), 'Integer')
    ]);
    while ($dao->fetch()) {
      $id = $dao->id;
      $result[$id] = [];
      $result[$id]['approver'] = $dao->approver;
      $result[$id]['acting_approver'] = $dao->acting_approver;
      $result[$id]['processed_date'] = $dao->processed_date;
      $result[$id]['old_status'] = $dao->old_status;
      $result[$id]['new_status'] = $dao->new_status;
      $result[$id]['is_approved'] = $dao->is_approved;
      $result[$id]['is_rejected'] = $dao->is_rejected;
      $result[$id]['is_payable'] = $dao->is_payable;
    }
    $this->assign('claimLogs', $result);

  }


  public function addRules() {
    $this->addFormRule(['CRM_Expenseclaims_Form_Claim', 'formRule']);
  }

  public static function formRule($fields) {
    $errors = [];
    if (isset($fields['_qf_Claim_next'])) {
      $claim = new CRM_Expenseclaims_BAO_Claim();
      $dryRunError = $claim->failsDryRunApprove($fields['claim_id'], $fields['approverid']);
      if ($dryRunError) {
        $errors['_qf_default'] = $dryRunError;
      }
    }
    return $errors;
  }
  /**
   * Method to process results from the form
   */
  public function postProcess() {
    if (isset($this->_submitValues['claim_id'])) {
      $this->_claimId = $this->_submitValues['claim_id'];
    }
    if (isset($this->_submitValues['approverid'])) {
      $this->_approverId = $this->_submitValues['approverid'];
    }
    $this->saveClaim();
    $MyClaimsURL = CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1&approverid='.$this->_approverId, TRUE);
    CRM_Utils_System::redirect($MyClaimsURL);
    parent::postProcess();
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = [];
    $defaults['claim_id'] = $this->_claimId;
    if (isset($this->_claim->claim_submitted_by)) {
      $defaults['claim_submitted_by'] = CRM_Threepeas_Utils::getContactName($this->_claim->claim_submitted_by);
    }
    if (isset($this->_claim->claim_submitted_date)) {
      $defaults['claim_submitted_date'] = $this->_claim->claim_submitted_date;
    }
    if (isset($this->_claim->claim_description)) {
      $defaults['claim_description'] = $this->_claim->claim_description;
    }
    if (isset($this->_claim->claim_total_amount)) {
      $defaults['claim_total_amount'] = $this->_claim->claim_total_amount;
    }
    if (isset($this->_claim->claim_link)) {
      $defaults['claim_link'] = $this->_claim->claim_link;
    }
    /*
    if (isset($this->_claim->claim_link)) {
      $index = $this->_elementIndex['claim_link'];
      foreach ($this->_elements[$index]->_options as $optionId => $option) {
        if ($option['text'] == $this->_claim->claim_link) {
          $defaults['claim_link'] = (string) $option['attr']['value'];
        }
      }
    */
    return $defaults;
  }

  /**
   * Method to save the claim
   */
  private function saveClaim() {
    if (!empty($this->_submitValues)) {
      if (isset($this->_submitValues['claim_description'])) {
        $claimParams['claim_description'] = $this->_submitValues['claim_description'];
      }
      if (isset($this->_submitValues['claim_link'])) {
        $claimParams['claim_link'] = $this->_submitValues['claim_link'];
      }
      $claimParams['claim_id'] = $this->_claimId;
      // if save or save and approve, save the claim
      if (isset($this->_submitValues['_qf_Claim_submit']) || isset($this->_submitValues['_qf_Claim_next']) || isset($this->_submitValues['_qf_Claim_next_reject']) || isset($this->_submitValues['_qf_Claim_next_assigntouser'])) {
        $claim = new CRM_Expenseclaims_BAO_Claim();
        $claim->update($claimParams);
        // if save and approve, also change claim status to approval
        if (isset($this->_submitValues['_qf_Claim_next'])) {
          $session = CRM_Core_Session::singleton();
          $claim->approve($this->_claimId, $this->_approverId, $session->get('userID'));
        }

        if (isset($this->_submitValues['_qf_Claim_next_reject'])) {
          $session = CRM_Core_Session::singleton();
          $claim->reject($this->_claimId, $this->_approverId, $session->get('userID'));
        }

        if(isset($this->_submitValues['_qf_Claim_next_assigntouser'])) {
          $session = CRM_Core_Session::singleton();
          $claim->assignToOtherUser($this->_claimId, $session->get('userID'));
        }
      }
    }
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $session = CRM_Core_Session::singleton();
    $values = CRM_Utils_Request::exportValues();
    if (isset($values['claim_id'])) {
      $this->_claimId = $values['claim_id'];
    }
    else {
      if (isset($values['id'])) {
        $this->_claimId = $values['id'];
      }
    }
    if(isset($values['approverid'])){
      $this->_approverId = $values['approverid'];
    }
    // action enable means approve claim!
    if ($this->_action == CRM_Core_Action::ENABLE) {
      $claim = new CRM_Expenseclaims_BAO_Claim();
      try {
        $claim->approve($this->_claimId, $session->get('userID'));
        CRM_Core_Session::setStatus('Claim ' . $this->_claimId . ' approved', 'Claim Approved', 'success');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1', TRUE));
      } catch (Exception $ex) {
        CRM_Core_Session::setStatus($ex->getMessage(), 'Claim Approval Error', 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1', TRUE));
      }
    }
    if ($this->_action == CRM_Core_Action::UPDATE || $this->_action == CRM_Core_Action::VIEW) {
      $claim = new CRM_Expenseclaims_BAO_Claim();
      $this->_claim = $claim->getWithId($this->_claimId);
    }
    CRM_Utils_System::setTitle(ts('PUM Senior Experts Expense Manage Claim'));
    $this->_claimLinkList = CRM_Expenseclaims_Utils::getClaimLinksForContact($this->_claim->claim_submitted_by, TRUE);

    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'approverid='.$this->_approverId, true));
  }

}
