<?php

/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 2 Feb 2017
 * @license AGPL-3.0
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Expenseclaims_Form_ClaimLevelContact extends CRM_Core_Form {

  protected $_claimLevelId = NULL;
  private $_contacts = array();

  /**
   * Method to build the QuickForm
   */
  public function buildQuickForm() {
    // add form elements
    $this->add('hidden', 'claim_level_id');
    $this->add('select', 'claim_level_contacts', ts('New Contact(s) for this Claim Level'), $this->_contacts, true,
      array('id' => 'claim_level_contacts', 'multiple' => 'multiple','class' => 'crm-select2'));
    // add buttons
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));

    parent::buildQuickForm();
  }

  /**
   * Method to get available contacts (employees of PUM)
   * (with CRM_Core_DAO for performance reasons)
   *
   * @return array
   */
  private function getContacts() {
    $this->_contacts = array();
    if (empty($this->_claimLevelId) && isset($this->_submitValues['claim_level_id'])) {
      $this->_claimLevelId = $this->_submitValues['claim_level_id'];
    }
    $sql = 'SELECT id, sort_name FROM civicrm_contact WHERE contact_type = %1 AND employer_id = %2
      AND id NOT IN(SELECT DISTINCT(contact_id) FROM pum_claim_level_contact WHERE claim_level_id = %3) ORDER BY sort_name';
    $sqlParams = array(
      1 => array('Individual', 'String'),
      2 => array(1, 'Integer'),
      3 => array($this->_claimLevelId, 'Integer')
    );
    $contact = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    while ($contact->fetch()) {
      $this->_contacts[$contact->id] = $contact->sort_name;
    }
  }

  /**
   * Method to process results from the form
   */
  public function postProcess() {
    $this->_claimLevelId = $this->_submitValues['claim_level_id'];
    if ($this->_action == CRM_Core_Action::ADD) {
      $this->addClaimLevelContacts($this->_submitValues);
    }
    parent::postProcess();
  }

  /**
   * Method to add the claim level contacts
   *
   * @param $values
   */
  private function addClaimLevelContacts($values) {
    if (isset($values['claim_level_contacts']) && isset($values['claim_level_id'])) {
      foreach ($values['claim_level_contacts'] as $contactId) {
        $claimLevelContact = new CRM_Expenseclaims_DAO_ClaimLevelContact();
        $claimLevelContact->claim_level_id = $values['claim_level_id'];
        $claimLevelContact->contact_id = $contactId;
        $claimLevelContact->save();
      }
    }
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $config = CRM_Expenseclaims_Config::singleton();
    // process delete first
    if ($this->_action == CRM_Core_Action::DELETE) {
      $this->deleteClaimLevelContactAndReturn();
    }
    $requestValues = CRM_Utils_Request::exportValues();
    if (isset($requestValues['id'])) {
      $this->_claimLevelId = $requestValues['id'];
    }
    $this->getContacts();
    CRM_Utils_System::setTitle(ts('PUM Senior Experts Add Contacts for Expense Claim Level'));
    try {
      $claimLevelLevel = civicrm_api3('ClaimLevel', 'getvalue', array('id' => $this->_claimLevelId, 'return' => 'level'));
      $levelLabel = civicrm_api3('OptionValue', 'getvalue', array('option_group_id' => $config->getClaimLevelOptionGroup('id'), 'value' => $claimLevelLevel, 'return' => 'label'));
      $this->assign('actionHeader', ts("Add Contacts for Level")." ".$levelLabel);
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to delete claim level contact
   */
  protected function deleteClaimLevelContactAndReturn() {
    $requestValues = CRM_Utils_Request::exportValues();
    if (isset($requestValues['id'])) {
      $claimLevelContact = new CRM_Expenseclaims_DAO_ClaimLevelContact();
      $claimLevelContact->id = $requestValues['id'];
      $claimLevelContact->find();
      $statusMsg = ts('Removed Claim Level Contact');
      if ($claimLevelContact->fetch()) {
        $statusMsg .= ' '.civicrm_api3('Contact', 'getvalue', array('id' => $claimLevelContact->contact_id, 'return' => 'display_name'));
        $claimLevelContact->delete();
      }
    }
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMsg, 'Removed Claim Level Contact', 'success');
    CRM_Utils_System::redirect($session->readUserContext());
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults['claim_level_id'] = $this->_claimLevelId;
    return $defaults;
  }
}
