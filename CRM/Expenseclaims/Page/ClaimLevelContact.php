<?php
/**
 * Page ClaimLevelContact to list all present claim level contacts
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 2 Feb 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_Page_ClaimLevelContact extends CRM_Core_Page {

  private $_claimLevelId = NULL;

  /**
   * Standard run function created when generating page with Civix
   *
   * @access public
   */
  function run() {
    $this->setPageConfiguration();
    $this->initializePager();
    $claimLevelContacts = $this->getClaimLevelContacts();
    $this->assign('claimLevelContacts', $claimLevelContacts);
    parent::run();
  }

  /**
   * Function to get the claim level contacts
   *
   * @return array $claimLevelContacts
   * @access protected
   */
  protected function getClaimLevelContacts() {
    $claimLevelContacts = array();
    list($offset, $limit) = $this->_pager->getOffsetAndRowCount();
    $query = "SELECT * FROM pum_claim_level_contact WHERE claim_level_id = %1 LIMIT %2, %3";
    $queryParams[1] = array($this->_claimLevelId, 'Integer');
    $queryParams[2] = array($offset, 'Integer');
    $queryParams[3] = array($limit, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($dao->fetch()) {
      $row = array();
      try {
        $row['contact_name'] = civicrm_api3('Contact', 'getvalue', array(
          'id' => $dao->contact_id,
          'return' => 'display_name'));
      } catch (CiviCRM_API3_Exception $ex) {}
      $deleteUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimlevelcontact', 'action=delete&id='.$dao->id, true);
      $row['actions'] = array('<a class="action-item" title="Delete" href="'.$deleteUrl.'">Delete</a>');
      $claimLevelContacts[$dao->id] = $row;
    }
    return $claimLevelContacts;
  }

  /**
   * Function to set the page configuration
   *
   * @access protected
   */
  protected function setPageConfiguration() {
    $this->_claimLevelId = CRM_Utils_Request::retrieve('id', 'Integer');
    try {
      $claimLevelLevel = civicrm_api3('ClaimLevel', 'getvalue', array('id' => $this->_claimLevelId, 'return' => 'level'));
      $levelLabel = civicrm_api3('OptionValue', 'getvalue', array('option_group_id' => 'pum_claim_level', 'value' => $claimLevelLevel, 'return' => 'label'));
      $this->assign('pageHeader', ts("Contact for Expense Claim Level")." ".$levelLabel);
    } catch (CiviCRM_API3_Exception $ex) {}
    CRM_Utils_System::setTitle("PUM Senior Experts Expense Claim Level Contacts");
    $this->assign('addUrl', CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimlevelcontact', 'action=add&id='.$this->_claimLevelId, true));
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/pumexpenseclaims/page/claimlevelcontact', 'reset=1&id='.$this->_claimLevelId, true));
  }

  /**
   * Method to initialize pager
   *
   * @access protected
   */
  protected function initializePager() {
    $params           = array(
      'total' => CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM pum_claim_level_contact WHERE claim_level_id = %1",
        array(1 => array($this->_claimLevelId, 'Integer'))),
      'rowCount' => CRM_Utils_Pager::ROWCOUNT,
      'status' => ts('Expense Claim Level Contacts %%StatusMessage%%'),
      'buttonBottom' => 'PagerBottomButton',
      'buttonTop' => 'PagerTopButton',
      'pageID' => $this->get(CRM_Utils_Pager::PAGE_ID),
    );
    $this->_pager = new CRM_Utils_Pager($params);
    $this->assign_by_ref('pager', $this->_pager);
  }
}
