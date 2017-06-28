<?php
/**
 * Class DAO Claim Log
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 17 Feb 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_DAO_ClaimLog extends CRM_Core_DAO {
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  static $_export = null;
  /**
   * empty definition for virtual function
   */
  static function getTableName() {
    return 'pum_claim_log';
  }
  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields() {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ) ,
        'claim_activity_id' => array(
          'name' => 'claim_activity_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ),
        'approval_contact_id' => array(
          'name' => 'approval_contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ),
        'acting_approval_contact_id' => array(
          'name' => 'acting_approval_contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ),
        'processed_date' => array(
          'name' => 'processed_date',
          'type' => CRM_Utils_Type::T_DATE
        ),
        'is_approved' => array(
          'name' => 'is_approved',
          'type' => CRM_Utils_Type::T_INT
        ),
        'is_rejected' => array(
          'name' => 'is_rejected',
          'type' => CRM_Utils_Type::T_INT
        ),
        'is_payable' => array(
          'name' => 'is_payable',
          'type' => CRM_Utils_Type::T_INT
        ),
        'old_status_id' => array(
          'name' => 'old_status_id',
          'type' => CRM_Utils_Type::T_STRING
        ),
        'new_status_id' => array(
          'name' => 'new_status_id',
          'type' => CRM_Utils_Type::T_STRING
        )
      );
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the array key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys() {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id' => 'id',
        'claim_activity_id' => 'claim_activity_id',
        'approval_contact_id' => 'approval_contact_id',
        'acting_approval_contact_id' => 'acting_approval_contact_id',
        'processed_date' => 'processed_date',
        'is_approved' => 'is_approved',
        'is_rejected' => 'is_rejected',
        'is_payable' => 'is_payable',
        'old_status_id' => 'old_status_id',
        'new_status_id' => 'new_status_id'
      );
    }
    return self::$_fieldKeys;
  }
  /**
   * returns the list of fields that can be exported
   *
   * @access public
   * return array
   * @static
   */
  static function &export($prefix = false)
  {
    if (!(self::$_export)) {
      self::$_export = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (CRM_Utils_Array::value('export', $field)) {
          if ($prefix) {
            self::$_export['activity'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}