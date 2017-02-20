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
    // add buttons
    $this->addButtons(array(
      array('type' => 'save', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'next', 'name' => ts('Save and Approve')),
      array('type' => 'cancel', 'name' => ts('Cancel'))));

    parent::buildQuickForm();
  }

  /**
   * Method to process results from the form
   */
  public function postProcess() {
    $this->_claimId = $this->_submitValues['claim_id'];
    if ($this->_action != CRM_Core_Action::VIEW) {
      $this->saveClaimLevel($this->_submitValues);
    }
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
   * @param $values
   */
  private function saveClaim($values) {
    if (!empty($values)) {
    }
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $this->_claimId = CRM_Utils_Request::retrieve('id', 'Integer');
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $claim = new CRM_Expenseclaims_BAO_Claim();
      $this->_claim = $claim->getWithId($this->_claimId);
    }
    CRM_Utils_System::setTitle(ts('PUM Senior Experts Expense Manage Claim'));
    $session = CRM_Core_Session::singleton();
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
