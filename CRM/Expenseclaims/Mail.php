<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL
 */

/**
 * Class CRM_Expenseclaims_Mail
 *
 * Use this class for sending an email.
 * This class stores the claims in a static variable and the claim
 * can then be used in the token functionality.
 */
class CRM_Expenseclaims_Mail  {

  protected $currentClaim;

  /**
   * @var CRM_Expenseclaims_Mail
   */
  protected static $singleton;

  /**
   * @return CRM_Expenseclaims_Mail
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Expenseclaims_Mail();
    }
    return self::$singleton;
  }

  public function sendEmail($claim, $contact_id, $template_id) {
    $this->currentClaim = $claim;

    //mail claim approved
    $params_email = array(
      'contact_id' => $contact_id,
      'template_id' => $template_id,
    );
    try {
      civicrm_api3('Email', 'send', $params_email);
    } catch (Exception $e) {
      // Do nothing
    }
    // reset the current claim
    $this->currentClaim = null;
  }

  /**
   * @return mixed
   */
  public function getCurrentClaim() {
    return $this->currentClaim;
  }

}