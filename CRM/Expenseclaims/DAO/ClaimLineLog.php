<?php
/**
 * Class DAO Claim Line Log (change history of claim lines)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 29 Mar 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_DAO_ClaimLineLog extends CRM_Core_DAO {
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
    return 'pum_claim_line_log';
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
        'claim_line_id' => array(
          'name' => 'claim_line_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ),
        'changed_by_id' => array(
          'name' => 'changed_by_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ),
        'changed_date' => array(
          'name' => 'changed_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
        ),
        'change_reason' => array(
          'name' => 'change_reason',
          'type' => CRM_Utils_Type::T_STRING
        ),
        'old_expense_date' => array(
          'name' => 'old_expense_date',
          'type' => CRM_Utils_Type::T_DATE
        ),
        'new_expense_date' => array(
          'name' => 'new_expense_date',
          'type' => CRM_Utils_Type::T_DATE
        ),
        'old_currency_id' => array(
          'name' => 'old_currency_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'new_currency_id' => array(
          'name' => 'new_currency_id',
          'type' => CRM_Utils_Type::T_INT
        ),
        'old_currency_amount' => array(
          'name' => 'old_currency_amount',
          'type' => CRM_Utils_Type::T_MONEY
        ),
        'new_currency_amount' => array(
          'name' => 'new_currency_amount',
          'type' => CRM_Utils_Type::T_MONEY
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
        'claim_line_id' => 'claim_line_id',
        'changed_by_id' => 'changed_by_id',
        'changed_date' => 'changed_date',
        'change_reason' => 'change_reason',
        'old_expense_date' => 'old_expense_date',
        'new_expense_date' => 'new_expense_date',
        'old_currency_id' => 'old_currency_id',
        'new_currency_id' => 'new_currency_id',
        'old_currency_amount' => 'old_currency_amount',
        'new_currency_amount' => 'new_currency_amount'
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