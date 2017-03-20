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
    $this->executeSqlFile('sql/create_pum_claim_line.sql');
    $this->executeSqlFile('sql/create_pum_claim_log.sql');
    $this->executeSqlFile('sql/create_pum_claim_level.sql');
    $this->executeSqlFile('sql/create_pum_claim_level_main.sql');
    $this->executeSqlFile('sql/create_pum_claim_level_type.sql');
    $this->executeSqlFile('sql/create_pum_claim_level_contact.sql');
  }

  /**
   * Once installed, make sure all config items are created
   */
  public function postInstall() {
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
}
