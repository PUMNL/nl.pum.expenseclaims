<?php
/**
 * Page My Claims to list all claims that require my action
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 20 Feb 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_Page_MyClaims extends CRM_Core_Page {
  private $_userContactId = NULL;

  /**
   * Standard run function created when generating page with Civix
   *
   * @access public
   */
  function run() {
    $this->setPageConfiguration();
    $this->initializePager();
    $myClaims = $this->getMyClaims();
    $this->assign('myClaims', $myClaims);
    parent::run();
  }

  /**
   * Function to get my claims
   *
   * @return array $myClaims
   * @access protected
   */
  protected function getMyClaims() {
    $myClaims = array();
    $config = CRM_Expenseclaims_Config::singleton();
    list($offset, $limit) = $this->_pager->getOffsetAndRowCount();
    $query = "
SELECT pclog.claim_activity_id 
,      cac.contact_id AS claim_submitted_by 
,      cact.activity_date_time AS claim_submitted_date 
,      pcc.{$config->getClaimLinkCustomField('column_name')} AS claim_link 
,      pcc.{$config->getClaimTotalAmountCustomField('column_name')} AS claim_total_amount
,      pcc.{$config->getClaimDescriptionCustomField('column_name')} AS claim_description
,      pcc.{$config->getClaimStatusCustomField('column_name')}     AS claim_status_id
,      pcc.{$config->getClaimTypeCustomField('column_name')} AS claim_type_id
,      csov.label AS claim_status
,      ctov.label AS claim_type
FROM pum_claim_log pclog
LEFT JOIN civicrm_activity cact ON pclog.claim_activity_id = cact.id
LEFT JOIN {$config->getClaimInformationCustomGroup('table_name')} pcc ON cact.id = pcc.entity_id
LEFT JOIN civicrm_activity_contact cac ON cact.id = cac.activity_id AND cac.record_type_id = %1 
LEFT JOIN civicrm_option_value csov ON pcc.{$config->getClaimStatusCustomField('column_name')} = csov.value AND csov.option_group_id = %2
LEFT JOIN civicrm_option_value ctov ON pcc.{$config->getClaimTypeCustomField('column_name')} = ctov.value AND ctov.option_group_id = %3
WHERE pclog.approval_contact_id = %4 AND pclog.processed_date IS NULL  LIMIT %6, %7";
    $queryParams = array(
      1 => array($config->getTargetRecordTypeId(), 'Integer'),
      2 => array($config->getClaimStatusOptionGroup('id'), 'Integer'),
      3 => array($config->getClaimTypeOptionGroup('id'), 'Integer'),
      4 => array($this->_userContactId, 'Integer'),
      //5 => array(1, 'Integer'),
      6 => array($offset, 'Integer'),
      7 => array($limit, 'Integer')
    );
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($dao->fetch()) {
      $row = array();
      $row['type'] = $dao->claim_type;
      $row['submitted_by'] = CRM_Threepeas_Utils::getContactName($dao->claim_submitted_by);
      $row['submitted_date'] = $dao->claim_submitted_date;
      if ($dao->claim_type_id == 'project') {
        $row['link'] = $this->getLinkCaseSubject($dao->claim_link);
        $row['link_url'] = $this->getLinkUrl($dao->claim_link);
      } else {
        $row['link'] = $dao->claim_link;
        $row['link_url'] = NULL;
      }
      $row['total_amount'] = $dao->claim_total_amount;
      $row['status'] = $dao->claim_status;
      $row['description'] = $dao->claim_description;
      $row['actions'] = $this->setRowActions($dao->claim_activity_id);
      $myClaims[$dao->claim_activity_id] = $row;
    }
    return $myClaims;
  }

  /**
   * Method to get the URL for Case View Summary
   *
   * @param $caseId
   * @return string
   */
  private function getLinkUrl($caseId) {
    if (method_exists('CRM_Threepeas_Utils', 'getCaseClientId')) {
      $caseClientId = CRM_Threepeas_Utils::getCaseClientId($caseId);
    } else {
      $sql = "SELECT contact_id FROM civicrm_case_contact WHERE case_id = %1";
      $caseClientId = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($caseId, 'Integer')));
    }
    return CRM_Utils_System::url('civicrm/contact/view/case', 'reset=1&action=view&id='.$caseId.'&cid='
      .$caseClientId, TRUE);
  }
  /**
   * Method to get the case subject for a main activity claim link
   *
   * @param $claimLinkCaseId
   * @return string
   */
  private function getLinkCaseSubject($claimLinkCaseId) {
    $link = 'Main Activity: ';
    if (!is_numeric($claimLinkCaseId)) {
      $link .= ' (could not find main activity with ID '.$claimLinkCaseId.')';
    }
    try {
      $link .= civicrm_api3('Case', 'getvalue', array(
        'id' => $claimLinkCaseId,
        'return' => 'subject'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      $link .= ' (could not find main activity with ID '.$claimLinkCaseId.')';
    }
    return $link;
  }

  /**
   * Function to set the row action urls and links for each row
   *
   * @param int $claimId
   * @return array $actions
   * @access protected
   */
  protected function setRowActions($claimId) {
    $actions = array();
    $manageUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claim', 'action=update&id='.$claimId, true);
    $actions[] = '<a class="action-item" title="Manage" href="'.$manageUrl.'">Manage</a>';
    return $actions;
  }

  /**
   * Function to set the page configuration
   *
   * @access protected
   */
  protected function setPageConfiguration() {
    CRM_Utils_System::setTitle(ts("PUM Senior Experts Expense My Claims"));
    $session = CRM_Core_Session::singleton();
    $this->_userContactId = $session->get('userID');
    $session->pushUserContext(CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1', true));
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
