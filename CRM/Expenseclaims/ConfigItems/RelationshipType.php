<?php
/**
 * Class for RelationshipType configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 Feb 2017
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_ConfigItems_RelationshipType {
  /**
   * CRM_Expenseclaims_ConfigItems_RelationshipType constructor.
   */
  public function __construct() {
    $this->_apiParams = array();
  }

  /**
   * Method to validate params passed to create
   *
   * @param $params
   * @throws Exception when required param not found
   */
  public function validateCreateParams($params) {
    if (empty($params['name_a_b']) || empty($params['name_b_a'])) {
      throw new Exception("Missing mandatory parameter 'name_a_b' and/or 'name_b_a' in " .__METHOD__. ".");
    }
    $this->_apiParams = $params;
  }

  /**
   * Function to find an existing entity based on the entity's parameters.
   *
   * If no existing entity is found, an empty array is returned.
   * This default implementation searches on the name, but you can override it.
   *
   * @param array $params
   * @return array
   * @access public
   * @static
   */
  public function getExisting(array $params) {
    try {
      return civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => $params['name_a_b']));
    } catch (CiviCRM_API3_Exception $ex) {
      return [];
    }
  }

  /**
   * Method to create or update option group
   *
   * @param $params
   * @return array
   * @throws Exception when error in API Option Group Create
   */
  public function create($params) {
    $this->validateCreateParams($params);
    $existing = $this->getExisting($this->_apiParams);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    $this->_apiParams['is_active'] = 1;
    CRM_Core_Error::debug('api params', $this->_apiParams);
    try {
      civicrm_api3('RelationshipType', 'Create', $this->_apiParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not create or update relationship type with name_a_b '
          .$this->_apiParams['name_a_b'].', error from API RelationshipType Create: ') . $ex->getMessage());
    }
  }

  /**
   * Method to remove relationship types on uninstall
   *
   * @param $params
   */
  public function uninstall($params) {
    try {
      $relationshipTypeId = civicrm_api3('RelationshipType'. 'getvalue', array(
        'name_a_b' => $params['name_a_b'],
        'name_b_a' => $params['name_b_a'],
        'return' => 'id'
      ));
      // only if no active relationships with type
      $count = civicrm_api3('Relationship', 'getcount', array(
        'relationship_type_id' => $relationshipTypeId));
      if ($count == 0) {
        civicrm_api3('RelationshipType', 'delete', array('id' => $relationshipTypeId));
      }
    } catch (CiviCRM_API3_Exception $ex) {}
  }
}