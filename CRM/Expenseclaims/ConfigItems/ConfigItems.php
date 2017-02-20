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
    $this->installRelationshipTypes();
  }

  /**
   * Method to remove config items on uninstall of extension
   */
  public function uninstall() {
    $this->uninstallOptionGroups();
    $this->uninstallRelationshipTypes();
  }

  /**
   * Method to remove relationship types
   */
  private function uninstallRelationshipTypes() {
    $jsonFile = $this->_resourcesPath.'relationship_types.json';
    if (file_exists($jsonFile)) {
      $relationshipTypesJson = file_get_contents($jsonFile);
      $relationshipTypes = json_decode($relationshipTypesJson, true);
      foreach ($relationshipTypes as $name => $relationshipTypeParams) {
        $relationshipType = new CRM_Expenseclaims_ConfigItems_RelationshipType();
        $relationshipType->uninstall($relationshipTypeParams);
      }
    }
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

  /**
   * Method to create relationship types
   *
   * @throws Exception when resource file not found
   * @access protected
   */
  protected function installRelationshipTypes() {
    $jsonFile = $this->_resourcesPath.'relationship_types.json';
    if (!file_exists($jsonFile)) {
      throw new Exception(ts('Could not load relationship_types configuration file for extension,
      contact your system administrator!'));
    }
    $relationshipTypesJson = file_get_contents($jsonFile);
    $relationshipTypes = json_decode($relationshipTypesJson, true);
    foreach ($relationshipTypes as $name => $relationshipTypeParams) {
      $relationshipType = new CRM_Expenseclaims_ConfigItems_RelationshipType();
      $relationshipType->create($relationshipTypeParams);
    }
  }

  /**
   * Method to change the custom fields in the claim information custom group (which should already exist)
   */
  public static function changeCustomClaimInformation() {
    // check if custom group claim information exists, create if not
    $claimInformationCustomGroup = self::getClaimInformationCustomGroup();
    // switch deprecated custom fields to disabled
    $disabled = array('Currency', 'Pay_Receive');
    foreach ($disabled as $disableName) {
      try {
        $disabledId = civicrm_api3('CustomField', 'getvalue', array(
          'custom_group_id' => $claimInformationCustomGroup['id'],
          'name' => $disableName,
          'return' => 'id'));
        civicrm_api3('CustomField', 'create', array('id' => $disabledId, 'is_active' => 0));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    // check fields that have to remain
    self::checkRemainingClaimCustomFields($claimInformationCustomGroup['id']);
    // create new ones
    self::createNewClaimCustomFields($claimInformationCustomGroup['id']);
  }

  /**
   * Method to check if the custom fields of claim information that have to remain exist and update, and create if they do not
   *
   * @param $customGroupId
   */
  private static function checkRemainingClaimCustomFields($customGroupId) {
    // custom field to hold claim link
    try {
      $claimLinkCustomField = civicrm_api3('CustomField', 'getsingle', array(
        'custom_group_id' => $customGroupId,
        'name' => 'PUM_Projectnumber_Referencenumber'));
      civicrm_api3('CustomField', 'create', array(
        'id' => $claimLinkCustomField['id'],
        'label' => 'Claim Linked to',
        'is_active' => 1,
        'is_view' => 1));
    } catch (CiviCRM_API3_Exception $ex) {
      $claimTypeParams = array(
        'custom_group_id' => $customGroupId,
        'name' => 'PUM_Projectnumber_Referencenumber',
        'label' => 'Claim Linked to',
        'data_type' => 'String',
        'html_type' => 'Text',
        'column_name' => 'pum_projectnumber_referencenumbe_387',
        'is_active' => 1,
        'is_view' => 1
      );
      civicrm_api3('CustomField', 'create', $claimTypeParams);
    }
    // custom field for total amount
    try {
      $claimLinkCustomField = civicrm_api3('CustomField', 'getsingle', array(
        'custom_group_id' => $customGroupId,
        'name' => 'Total_Expenses'));
      civicrm_api3('CustomField', 'create', array(
        'id' => $claimLinkCustomField['id'],
        'label' => 'Total Amount',
        'is_active' => 1,
        'is_view' => 1));
    } catch (CiviCRM_API3_Exception $ex) {
      $claimTypeParams = array(
        'custom_group_id' => $customGroupId,
        'name' => 'Total_Expenses',
        'label' => 'Total Amount',
        'data_type' => 'Money',
        'html_type' => 'Text',
        'column_name' => 'total_expenses_389',
        'is_active' => 1,
        'is_view' => 1
      );
      civicrm_api3('CustomField', 'create', $claimTypeParams);
    }
    // custom field for description
    try {
      $claimLinkCustomField = civicrm_api3('CustomField', 'getsingle', array(
        'custom_group_id' => $customGroupId,
        'name' => 'Description'));
      civicrm_api3('CustomField', 'create', array(
        'id' => $claimLinkCustomField['id'],
        'is_active' => 1,
        'is_view' => 1));
    } catch (CiviCRM_API3_Exception $ex) {
      $claimTypeParams = array(
        'custom_group_id' => $customGroupId,
        'name' => 'Description',
        'label' => 'Description',
        'data_type' => 'String',
        'html_type' => 'Text',
        'column_name' => 'description_391',
        'is_active' => 1,
        'is_view' => 1
      );
      civicrm_api3('CustomField', 'create', $claimTypeParams);
    }
  }

  /**
   * Method to create new claim information custom fields
   *
   * @param $customGroupId
   */
  private static function createNewClaimCustomFields($customGroupId) {
    $config = CRM_Expenseclaims_Config::singleton();
    // new custom field type
    try {
      civicrm_api3('CustomField', 'getsingle', array('custom_group_id' => $customGroupId, 'name' => 'pum_claim_type'));
    } catch (CiviCRM_API3_Exception $ex) {
      $claimTypeParams = array(
        'custom_group_id' => $customGroupId,
        'name' => 'pum_claim_type',
        'label' => 'Claim Type',
        'data_type' => 'String',
        'html_type' => 'Select',
        'column_name' => 'pum_claim_type',
        'is_active' => 1,
        'is_view' => 1,
        'weight' => -500
      );
      $claimType = civicrm_api3('CustomField', 'create', $claimTypeParams);
      // now clean up option group that was f***ed up by api
      self::cleanOptionGroupForCustomField($claimType['id'], $config->getClaimTypeOptionGroup('id'));
    }
    // new custom field status id
    try {
      civicrm_api3('CustomField', 'getsingle', array('custom_group_id' => $customGroupId, 'name' => 'pum_claim_status'));
    } catch (CiviCRM_API3_Exception $ex) {
      $claimStatusParams = array(
        'custom_group_id' => $customGroupId,
        'name' => 'pum_claim_status',
        'label' => 'Claim Status',
        'data_type' => 'String',
        'html_type' => 'Select',
        'column_name' => 'pum_claim_status',
        'is_active' => 1,
        'is_view' => 1,
        'weight' => -510
      );
      $claimStatus = civicrm_api3('CustomField', 'create', $claimStatusParams);
      self::cleanOptionGroupForCustomField($claimStatus['id'], $config->getClaimStatusOptionGroup('id'));
    }
  }

  /**
   * Method to revert core error in custom field create api with option groups
   *
   * @param $customFieldId
   * @param $optionGroupId
   */
  private static function cleanOptionGroupForCustomField($customFieldId, $optionGroupId) {
    // first get custom field to find option group that was falsely created
    $customFieldOptionGroupId = civicrm_api3('CustomField', 'getvalue', array('id' => $customFieldId, 'return' => 'option_group_id'));
    // now delete option group
    civicrm_api3('OptionGroup', 'delete', array('id' => $customFieldOptionGroupId));
    // then link valid option group
    $sql = "UPDATE civicrm_custom_field SET option_group_id = %1 WHERE id = %2";
    CRM_Core_DAO::executeQuery($sql, array(
      1 => array($optionGroupId, 'Integer'),
      2 => array($customFieldId, 'Integer')
    ));
  }

  /**
   * Method to get or create data on custom group Claim Information
   *
   * @return array
   * @throws Exception
   */
  public static function getClaimInformationCustomGroup() {
    $config = CRM_Expenseclaims_Config::singleton();
    try {
      return civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Claiminformation'));
    } catch (CiviCRM_API3_Exception $ex) {
      $params = array(
        'name' => 'Claiminformation',
        'title' => 'Claiminformation',
        'extends' => 'Activity',
        'extends_entity_column_value' => $config->getClaimActivityTypeId(),
        'style' => 'Inline',
        'table_name' => 'civicrm_value_claiminformation_70');
      try {
        $created = civicrm_api3('CustomGroup', 'create', $params);
        return $created['values'];
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not find or create custom group with name Claiminformation in '.__METHOD__
          .', contact your ssystem administrator. Error from API CustomGroup Create: '.$ex->getMessage());
      }
    }
  }
}