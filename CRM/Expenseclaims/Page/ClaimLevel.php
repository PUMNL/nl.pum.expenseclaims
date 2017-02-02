<?php
/**
 * Page ClaimLevel to list all present claim leves
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 31 Jan 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_Page_ClaimLevel extends CRM_Core_Page {

  /**
   * Standard run function created when generating page with Civix
   *
   * @access public
   */
  function run() {
    $this->setPageConfiguration();
    $this->initializePager();
    $claimLevels = $this->getClaimLevels();
    $this->assign('claimLevels', $claimLevels);
    parent::run();
  }

  /**
   * Function to get the claim levels
   *
   * @return array $claimLevels
   * @access protected
   */
  protected function getClaimLevels() {
    $claimLevels = array();
    list($offset, $limit) = $this->_pager->getOffsetAndRowCount();
    $query = "SELECT * FROM pum_claim_level LIMIT %1, %2";
    $queryParams[1] = array($offset, 'Integer');
    $queryParams[2] = array($limit, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($dao->fetch()) {
      $row = array();
      try {
        $row['level'] = civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => 'pum_claim_level',
          'value' => $dao->level,
          'return' => 'label'));
      } catch (CiviCRM_API3_Exception $ex) {}
      if ($dao->max_amount == 999999999.99) {
        $row['max_amount'] = 'no max';
      } else {
        $row['max_amount'] = $dao->max_amount;
      }
      $row['valid_types'] = $this->getClaimLevelTypes($dao->id);
      $row['valid_main_activities'] = $this->getClaimLevelMainActivities($dao->id);
      try {
        $row['authorizing_level'] = civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => 'pum_claim_level',
          'value' => $dao->authorizing_level,
          'return' => 'label'));
      } catch (CiviCRM_API3_Exception $ex) {}
      $row['actions'] = $this->setRowActions($dao->id);
      $claimLevels[$dao->id] = $row;
    }
    return $claimLevels;
  }

  /**
   * Method to get all claim level types in a string field
   *
   * @param $claimLevelId
   * @return null
   * @access protected
   */
  protected function getClaimLevelTypes($claimLevelId) {
    $result = NULL;
    $types = array();
    $claimLevelType = new CRM_Expenseclaims_DAO_ClaimLevelType();
    $claimLevelType->claim_level_id = $claimLevelId;
    $claimLevelType->find();
    while ($claimLevelType->fetch()) {
      try {
        $types[] = civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => 'pum_claim_type',
          'value' => $claimLevelType->type_value,
          'return' => 'label'
        ));
        } catch (CiviCRM_API3_Exception $ex) {}
    }
    if (!empty($types)) {
      $result = implode("; ", $types);
    }
    return $result;
  }

  /**
   * Method to get all claim level main activities in a string field
   *
   * @param $claimLevelId
   * @return null
   * @access protected
   */
  protected function getClaimLevelMainActivities($claimLevelId) {
    $result = NULL;
    $mainActivities = array();
    $claimLevelMain = new CRM_Expenseclaims_DAO_ClaimLevelMain();
    $claimLevelMain->claim_level_id = $claimLevelId;
    $claimLevelMain->find();
    while ($claimLevelMain->fetch()) {
      try {
        $mainActivities[] = civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => 'case_type',
          'value' => $claimLevelMain->main_activity_type_id,
          'return' => 'label'
        ));
        } catch (CiviCRM_API3_Exception $ex) {}
    }
    if (!empty($mainActivities)) {
      $result = implode("; ", $mainActivities);
    }
    return $result;
  }

  /**
   * Function to set the row action urls and links for each row
   *
   * @param int $claimLevelId
   * @return array $actions
   * @access protected
   */
  protected function setRowActions($claimLevelId) {
    $actions = array();
    $editUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimlevel', 'action=update&id='.$claimLevelId, true);
    $deleteUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimlevel', 'action=delete&id='.$claimLevelId, true);
    $contactsUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/page/claimlevelcontact', 'reset=1&id='.$claimLevelId, true);
    $actions[] = '<a class="action-item" title="Contacts" href="'.$contactsUrl.'">Contacts</a>';
    $actions[] = '<a class="action-item" title="Edit" href="'.$editUrl.'">Edit</a>';
    $actions[] = '<a class="action-item" title="Delete" href="'.$deleteUrl.'">Delete</a>';
    return $actions;
  }

  /**
   * Function to set the page configuration
   *
   * @access protected
   */
  protected function setPageConfiguration() {
    CRM_Utils_System::setTitle(ts("PUM Senior Experts Expense Claim Authorization Levels"));
    $this->assign('addUrl', CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimlevel', 'action=add', true));
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/pumexpenseclaims/page/claimlevel', 'reset=1', true));
  }

  /**
   * Method to initialize pager
   *
   * @access protected
   */
  protected function initializePager() {
    $params           = array(
      'total' => CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM pum_claim_level"),
      'rowCount' => CRM_Utils_Pager::ROWCOUNT,
      'status' => ts('Expense Claim Levels %%StatusMessage%%'),
      'buttonBottom' => 'PagerBottomButton',
      'buttonTop' => 'PagerTopButton',
      'pageID' => $this->get(CRM_Utils_Pager::PAGE_ID),
    );
    $this->_pager = new CRM_Utils_Pager($params);
    $this->assign_by_ref('pager', $this->_pager);
  }

}
