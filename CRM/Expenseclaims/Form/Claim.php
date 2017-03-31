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
  protected $_claimLinkList = array();
  protected $_claim = array();

  /**
   * Method to build the QuickForm
   */
  public function buildQuickForm() {
    // add form elements
    $this->add('hidden', 'claim_id');
    $this->add('select', 'claim_link', ts('Link'), $this->_claimLinkList, true);
    $this->add('text', 'claim_submitted_by', ts('Claimed By'));
    $this->add('text', 'claim_submitted_date', ts('Date Submitted'));
    $this->add('text', 'claim_description', ts('Description'), true);
    $this->add('text', 'claim_total_amount', ts('Total Amount'), true);
    // add buttons
    $this->addButtons(array(
      array('type' => 'submit', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'next', 'name' => ts('Save and Approve')),
      array('type' => 'cancel', 'name' => ts('Cancel'))));

    $this->addClaimLines();
    parent::buildQuickForm();
  }

  /**
   * Method to get the claim lines of the claim and put them on the form
   */
  private function addClaimLines() {
    $result = array();
    $config = CRM_Expenseclaims_Config::singleton();
    $claimLines = CRM_Expenseclaims_BAO_ClaimLine::getValues(array('activity_id' => $this->_claimId));
    foreach ($claimLines as $claimLineId => $claimLine) {
      $result[$claimLineId] = array();
      if (isset($claimLine['expense_date'])) {
        $result[$claimLineId]['date'] = $claimLine['expense_date'];
      }
      if (isset($claimLine['expense_type'])) {
        try {
          $result[$claimLineId]['type'] = civicrm_api3('OptionValue', 'getvalue', array(
            'option_group_id' => $config->getClaimLineTypeOptionGroup('id'),
            'value' => $claimLine['expense_type'],
            'return' => 'label'
          ));
        } catch (CiviCRM_API3_Exception $ex) {}
      }
      if (isset($claimLine['currency_id'])) {
        $sql = 'SELECT name FROM civicrm_currency WHERE id = %1';
        $result[$claimLineId]['currency'] = CRM_Core_DAO::singleValueQuery($sql,
          array(1 => array($claimLine['currency_id'], 'Integer')));
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
      if (!empty($result[$claimLineId])) {
        $editUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimline', 'action=update&id='.$claimLineId, true);
        $result[$claimLineId]['actions'][] = '<a class="action-item" title="Edit" href="'.$editUrl.'">Edit</a>';
      }
    }
    $this->assign('claimLines', $result);
  }

  /**
   * Method to process results from the form
   */
  public function postProcess() {
    if (isset($this->_submitValues['claim_id'])) {
      $this->_claimId = $this->_submitValues['claim_id'];
    }
    $this->saveClaim();
    parent::postProcess();
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
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
      $index = $this->_elementIndex['claim_link'];
      foreach ($this->_elements[$index]->_options as $optionId => $option) {
        if ($option['text'] == $this->_claim->claim_link) {
          $defaults['claim_link'] = (string) $option['attr']['value'];
        }
      }
    }
    return $defaults;
  }

  /**
   * Method to save the claim
   *
   */
  private function saveClaim() {
    if (!empty($this->_submitValues)) {
      if (isset($this->_submitValues['claim_description'])) {
        $claimParams['claim_description'] = $this->_submitValues['claim_description'];
      }
      if (isset($this->_submitValues['claim_link'])) {
        $claimParams['claim_link'] = $this->_claimLinkList[$this->_submitValues['claim_link']];
      }
      $claimParams['claim_id'] = $this->_claimId;
      // if save or save and approve, save the claim
      if (isset($this->_submitValues['_qf_Claim_submit']) || isset($this->_submitValues['_qf_Claim_next'])) {
        $claim = new CRM_Expenseclaims_BAO_Claim();
        $claim->update($claimParams);
        // if save and approve, also change claim status to approval
        if (isset($this->_submitValues['_qf_Claim_next'])) {
          $session = CRM_Core_Session::singleton();
          $claim->approve($this->_claimId, $session->get('userID'));
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
    } else {
      if (isset($values['id'])) {
        $this->_claimId = $values['id'];
      }
    }
    // action enable means approve claim!
    if ($this->_action == CRM_Core_Action::ENABLE) {
      $claim = new CRM_Expenseclaims_BAO_Claim();
      $claim->approve($this->_claimId, $session->get('userID'));
      CRM_Core_Session::setStatus('Claim '.$this->_claimId.' approved', 'Claim Approved', 'success');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1', TRUE));
    }
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $claim = new CRM_Expenseclaims_BAO_Claim();
      $this->_claim = $claim->getWithId($this->_claimId);
    }
    CRM_Utils_System::setTitle(ts('PUM Senior Experts Expense Manage Claim'));
    $this->_claimLinkList = $this->getClaimLinkList($session->get('userID'));
  }

  /**
   * Method to get the link list for the user
   *
   * @param $userId
   * @return array|bool
   */
  private function getClaimLinkLIst($userId) {
    $claim = new CRM_Expenseclaims_BAO_Claim();
    return $claim->getMyLinks($userId);
  }
}
