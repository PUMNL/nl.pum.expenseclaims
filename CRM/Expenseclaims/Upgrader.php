<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Expenseclaims_Upgrader extends CRM_Expenseclaims_Upgrader_Base {

  /**
   * Create required table(s) on install
   *
   */
  public function install() {
    $this->executeSqlFile('sql/create_pum_claim_batch.sql');
    $this->executeSqlFile('sql/create_pum_claim_batch_entity.sql');
    $this->executeSqlFile('sql/create_pum_claim_line.sql');
    $this->executeSqlFile('sql/create_pum_claim_log.sql');
    $this->executeSqlFile('sql/create_pum_claim_level.sql');
    $this->executeSqlFile('sql/create_pum_claim_level_main.sql');
    $this->executeSqlFile('sql/create_pum_claim_level_type.sql');
    $this->executeSqlFile('sql/create_pum_claim_level_contact.sql');
    $this->executeSqlFile('sql/create_pum_claim_line_log.sql');
    $configItems = CRM_Expenseclaims_ConfigItems_ConfigItems::singleton();
    // change custom fields in existing custom group claiminformation
    $configItems->install();
    CRM_Expenseclaims_ConfigItems_ConfigItems::changeCustomClaimInformation();
  }

  /**
   * Remove created option groups and tables on uninstall
   */
  public function uninstall() {
    $configItems = CRM_Expenseclaims_ConfigItems_ConfigItems::singleton();
    $configItems->uninstall();
    $this->executeSqlFile('sql/drop_tables.sql');
  }

  /**
   * Upgrade 1001 update custom fields (in some cases this will not have been called with install)
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1001() {
    CRM_Expenseclaims_ConfigItems_ConfigItems::changeCustomClaimInformation();
    return TRUE;
  }

  /**
   * Upgrade 1002 add pum_claim_line_change_log table
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1002() {
    $this->executeSqlFile('sql/create_pum_claim_line_log.sql');
    return TRUE;
  }

  /**
   * Main activity is considerd not to be very user friendly for a claim role
   * Use expert instead
   * @return TRUE
   **/

  public function upgrade_1003() {
    CRM_Expenseclaims_ConfigItems_ConfigItems::changeMainActivityLabel();
    return TRUE;
  }

  public function upgrade_1004() {
    $this->executeSqlFile('sql/upgrade_1004.sql');
    return TRUE;
  }
}
