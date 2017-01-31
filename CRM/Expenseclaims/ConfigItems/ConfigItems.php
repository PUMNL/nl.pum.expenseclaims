<?php
/**
 * Class following Singleton pattern o create or update configuration items from
 * JSON files in resources folder
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Jan 2017
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_ConfigItems_ConfigItems {
  private static $_singleton;
  protected $_resourcesPath;
  protected $_customDataDir;

  /**
   * CRM_CExpenseclaims_ConfigItems constructor.
   */
  function __construct() {
    $settings = civicrm_api3('Setting', 'Getsingle', array());
    $resourcesPath = $settings['extensionsDir'].'/nl.pum.expenseclaims/resources/';
    if (!is_dir($resourcesPath) || !file_exists($resourcesPath)) {
      throw new Exception(ts('Could not find the folder '.$resourcesPath
        .' which is required for extension nl.pum.expenseclaims in '.__METHOD__
        .'.It does not exist or is not a folder, contact your system administrator'));
    }
    $this->_resourcesPath = $resourcesPath;
  }

  /**
   * Method to install config items
   */
  public function install() {
    $this->installOptionGroups();
  }

  /**
   * Method to remove config items on uninstall of extension
   */
  public function uninstall() {
    $this->uninstallOptionGroups();
  }

  /**
   * Method to remove option groups
   *
   * @throws Exception
   */
  private function uninstallOptionGroups() {
    $jsonFile = $this->_resourcesPath.'option_groups.json';
    if (file_exists($jsonFile)) {
      $optionGroupsJson = file_get_contents($jsonFile);
      $optionGroups = json_decode($optionGroupsJson, true);
      foreach ($optionGroups as $name => $optionGroupParams) {
        $optionGroup = new CRM_Expenseclaims_ConfigItems_OptionGroup();
        $optionGroup->uninstall($optionGroupParams);
      }
    }
  }

  /**
   * Singleton method
   *
   * @return CRM_Expenseclaims_ConfigItems_ConfigItems
   * @access public
   * @static
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Expenseclaims_ConfigItems_ConfigItems();
    }
    return self::$_singleton;
  }

  /**
   * Method to create option groups
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function installOptionGroups() {
    $jsonFile = $this->_resourcesPath.'option_groups.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load option_groups configuration file for extension,
      contact your system administrator!'));
    }
    $optionGroupsJson = file_get_contents($jsonFile);
    $optionGroups = json_decode($optionGroupsJson, true);
    foreach ($optionGroups as $name => $optionGroupParams) {
      $optionGroup = new CRM_Expenseclaims_ConfigItems_OptionGroup();
      $optionGroup->create($optionGroupParams);
    }
  }
}