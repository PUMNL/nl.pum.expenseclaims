<?php
/**
 * Class DAO Claim Line
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Jan 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_DAO_ClaimLine extends CRM_Core_DAO {
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
    return 'pum_claim_line';
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
        'activity_id' => array(
          'name' => 'activity_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ) ,
        'expense_date' => array(
          'name' => 'expense_date',
          'type' => CRM_Utils_Type::T_DATE,
        ),
        'expense_type' => array(
          'name' => 'expense_type',
          'type' => CRM_Utils_Type::T_STRING,
        ),
        'currency_id' => array(
          'name' => 'currency_id',
          'type' => CRM_Utils_Type::T_INT,
        ) ,
        'currency_amount' => array(
          'name' => 'currency_amount',
          'type' => CRM_Utils_Type::T_MONEY,
        ),
        'euro_amount' => array(
          'name' => 'euro_amount',
          'type' => CRM_Utils_Type::T_MONEY,
        ),
        'distance_km' => array(
          'name' => 'distance_km',
          'type' => CRM_Utils_Type::T_INT,
        ),
        'exchange_rate' => array(
          'name' => 'exchange_rate',
          'type' => CRM_Utils_Type::T_MONEY,
        ),
        'description' => array(
          'name' => 'description',
          'type' => CRM_Utils_Type::T_STRING,
        ),
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
        'activity_id' => 'activity_id',
        'expense_date' => 'expense_date',
        'expense_type' => 'expense_type',
        'currency_id' => 'currency_id',
        'currency_amount' => 'currency_amount',
        'euro_amount' => 'euro_amount',
        'distance_km' => 'distance_km',
        'exchange_rate' => 'exchange_rate',
        'description' => 'description'
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