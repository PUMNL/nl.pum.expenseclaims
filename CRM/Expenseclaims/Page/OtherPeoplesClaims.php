<?php

class CRM_Expenseclaims_Page_OtherPeoplesClaims extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(ts('Other Peoples Claims'));
    $this->assign('currentTime', date('Y-m-d H:i:s'));
    $this->assign('otherPeople',$this->otherPeople());
    parent::run();
  }
  private function otherPeople() {
    try {
      $sql = "SELECT lc.contact_id AS 'id', c.display_name FROM pum_claim_level_contact lc JOIN civicrm_contact c ON c.id = lc.contact_id";

      $otherPeople = array();
      $dao = CRM_Core_DAO::executeQuery($sql);

      while($dao->fetch()){
        $otherPerson = array();
        $otherPerson['id'] = $dao->id;
        $otherPerson['display_name'] = $dao->display_name;
        $otherPerson['contact_id'] = $dao->id;
        $manageUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1&approverid='.$dao->id, true);
        $otherPerson['action'] = '<a class="action-item" title="Manage" href="'.$manageUrl.'">Manage</a>';
        $otherPeople[$dao->id]=$otherPerson;
      }
      $this->assign('otherPeople',$otherPerson);
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message($e->getCode() & " | " & $e->getMessage() & " | " & $e->getTraceAsString(), FALSE);
    }
    return $otherPeople;
  }
}
