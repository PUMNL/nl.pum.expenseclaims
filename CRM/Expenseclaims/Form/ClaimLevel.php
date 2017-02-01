<?php

/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 31 Jan 2017
 * @license AGPL-3.0
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Expenseclaims_Form_ClaimLevel extends CRM_Core_Form {

  protected $_claimLevelId = NULL;
  protected $_claimLevel = array();

  /**
   * Method to build the QuickForm
   */
  public function buildQuickForm() {
    // add form elements
    $this->add('hidden', 'claim_level_id');
    $this->add('select', 'level', ts('Level'), $this->getClaimLevelLevels(), true);
    $this->add('text', 'max_amount', ts('Max Amount'), array('size' => 14), true);
    $this->add('select', 'valid_types', ts('Valid Types'), $this->getValidTypes(), true,
      array('id' => 'valid_types', 'multiple' => 'multiple','class' => 'crm-select2'));
    $this->add('select', 'valid_main_activities', ts('Valid Main Activities'), $this->getValidMainActivities(), true,
      array('id' => 'valid_main_activities', 'multiple' => 'multiple','class' => 'crm-select2'));
    $this->add('select', 'authorizing_level', ts('Authorizing Level'), $this->getClaimLevelLevels());
    // add buttons
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));

    parent::buildQuickForm();
  }

  /**
   * Method to get available claim levels
   *
   * @return array
   */
  private function getClaimLevelLevels() {
    $result = array();
    $result[0] = '- select -';
    try {
      $claimLevels = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'pum_claim_level',
        'is_active' => 1,
        'options' => array('sort' => 'label', 'limit' => 0)));
      foreach ($claimLevels['values'] as $claimLevelId => $claimLevel) {
        $result[$claimLevel['value']] = $claimLevel['label'];
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $this->removeOwnLevelFromList($result);
    }
    return $result;
  }

  /**
   * Method to remove level being edited from select list
   *
   * @param $result
   */
  private function removeOwnLevelFromList(&$result) {
    if (!empty($this->_claimLevel['level'])) {
      $claimLevelLevel = $this->_claimLevel['level'];
      unset($result[$claimLevelLevel]);
    }
  }

  /**
   * Method to get available claim types
   *
   * @return array
   */
  private function getValidTypes() {
    $result = array();
    try {
      $claimTypes = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'pum_claim_type',
        'is_active' => 1,
        'options' => array('sort' => 'label', 'limit' => 0)));
      foreach ($claimTypes['values'] as $claimTypeId => $claimType) {
        $result[$claimType['value']] = $claimType['label'];
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    return $result;
  }

  /**
   * Method to get the valid main activities
   *
   * @return mixed
   */
  private function getValidMainActivities() {
    $config = CRM_Expenseclaims_Config::singleton();
    return $config->getValidMainActivities();
  }

  /**
   * Method to process results from the form
   */
  public function postProcess() {
    $this->_claimLevelId = $this->_submitValues['claim_level_id'];
    if ($this->_action != CRM_Core_Action::VIEW) {
      $this->saveClaimLevel($this->_submitValues);
    }
    parent::postProcess();
  }

  /**
   * Method to save the claim level
   *
   * @param $values
   */
  private function saveClaimLevel($values) {
    if (!empty($values)) {
      $this->_claimLevel = civicrm_api3('ClaimLevel', 'create', $values);
    }
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $this->_claimLevelId = CRM_Utils_Request::retrieve('id', 'Integer');
    if ($this->_action != CRM_Core_Action::ADD && $this->_claimLevelId) {
      $this->_claimLevel = civicrm_api3('ClaimLevel', 'Getsingle', array('id' => $this->_claimLevelId));
    }
    if ($this->_action == CRM_Core_Action::DELETE) {
      $this->deleteClaimLevelAndReturn();
    }
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $actionHeader = "Add Expense Claim Level";
        break;
      case CRM_Core_Action::UPDATE:
        $actionHeader = "Edit Expense Claim Level";
        break;
      default:
        $actionHeader = 'Expense Claim Level';
        break;
    }
    CRM_Utils_System::setTitle(ts('PUM Senior Experts Expense Claim Level'));
    $this->assign('actionHeader', $actionHeader);
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['claim_level_id'] = $this->_claimLevelId;
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $defaults['level'] = $this->_claimLevel['level'];
      $defaults['max_amount'] = $this->_claimLevel['max_amount'];
      $defaults['valid_types'] = $this->_claimLevel['level_types'];
      $defaults['valid_main_activities'] = $this->_claimLevel['level_main_activities'];
      $defaults['authorizing_level'] = $this->_claimLevel['authorizing_level'];
    }
    return $defaults;
  }

  /**
   * Method to delete claim level
   *
   */
  protected function deleteClaimLevelAndReturn() {
    civicrm_api3('ClaimLevel', 'Delete', array('id' => $this->_claimLevelId));
    $session = CRM_Core_Session::singleton();
    $session->setStatus(ts('Deleted Claim Level').' '.$this->_claimLevel['label'].' '.ts('from the database'),
      'Deleted Claim Level', 'success');
    CRM_Utils_System::redirect($session->readUserContext());
  }

  /**
   * Overridden parent method to set validation rules
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Expenseclaims_Form_ClaimLevel', 'validateLabel'));
    $this->addFormRule(array('CRM_Expenseclaims_Form_ClaimLevel', 'validateAuthorizingLevel'));
    $this->addFormRule(array('CRM_Epxenseclaims_Form_ClaimLevel', 'validateMaxAmount'));
  }

  /**
   * Method to validate authorizing level
   *
   * @param $fields
   * @return bool|array
   */
  public static function validateAuthorizingLevel($fields) {
    if (isset($fields['authorizing_level'])) {
      if (empty($fields['authorizing_level']) && $fields['max_amount'] != 'no max') {
        $errors['authorizing_level'] = ts('Authorizing Level can only be empty if max amount is set to no max');
        return $errors;
      }
    }
    return TRUE;
  }
  /**
   * Method to validate max amount
   *
   * @param $fields
   * @return bool|array
   */
  public static function validateMaxAmount($fields) {
    // empty value is already validated as max_amount is a required field
    if (isset($fields['max_amount']) && !empty($fields['max_amount'])) {
      // value can only contain numbers or 'no max'
      if ($fields['max_amount'] != "no max") {
        if (!is_numeric($fields['max_amount'])) {
          $errors['max_amount'] = ts('Max Amount can only contain numbers or the value no max');
          return $errors;
        }
        if ($fields['max_amount'] < 0) {
          $errors['max_amount'] = ts('Max Amount can only contain positive values');
          return $errors;
        }
      }
      $count = civicrm_api3('ClaimLevel', 'getcount', array('max_amount' => $fields['max_amount']));
      if ($count > 0) {
        $errors['level'] = ts('MAx amount is already used in another claim level. A certain max amount can only exist once');
        return $errors;
      }
    }
    return TRUE;
  }

  /**
   * Method to validate that label does not have a claim level yet
   *
   * @param $fields
   * @return array|bool
   */
  public static function validateLabel($fields) {
    if (isset($fields['level']) && !empty($fields['level'])) {
      $count = civicrm_api3('ClaimLevel', 'getcount', array('level' => $fields['level']));
      if ($count > 0) {
        $errors['level'] = ts('Level is already used in another claim level. A certain level can only exist once');
        return $errors;
      }
    }
    return TRUE;
  }
}
