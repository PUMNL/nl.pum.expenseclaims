<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_Tokens {

  protected $token_name;
  protected $token_label;

  public function __construct($token_name, $token_label) {
    $this->token_name = $token_name;
    $this->token_label = $token_label;
  }

  public function tokens(&$tokens) {
    $t = array();
    $t[$this->token_name.'.claimnr'] = $this->token_label.' '.ts('claimnr.');
    $t[$this->token_name.'.claim_amount'] = $this->token_label.' '.ts('amount.');
    $tokens[$this->token_name] = $t;
  }

  public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    $this->datetime = time();
    if ($this->checkToken($tokens, 'claimnr')) {
      $this->claimNrToken($values, $cids, 'claimnr');
    }
    if ($this->checkToken($tokens, 'claim_amount')) {
      $this->claimAmountToken($values, $cids, 'claim_amount');
    }
  }

  private function claimNrToken(&$values, $cids, $token) {
    $claimEmailer = CRM_Expenseclaims_Mail::singleton();
    $currentClaim = $claimEmailer->getCurrentClaim();
    $value = $currentClaim['id'];
    if(is_array($cids)) {
      foreach($cids as $cid) {
        $values[$cid][$this->token_name.'.'.$token] = $value;
      }
    } else {
      $values[$this->token_name.'.'.$token] = $value;
    }
  }

  private function claimAmountToken(&$values, $cids, $token) {
    $claimEmailer = CRM_Expenseclaims_Mail::singleton();
    $currentClaim = $claimEmailer->getCurrentClaim();
    $value = $currentClaim['claim_total_amount'];
    $value = CRM_Utils_Money::format($value);
    if(is_array($cids)) {
      foreach($cids as $cid) {
        $values[$cid][$this->token_name.'.'.$token] = $value;
      }
    } else {
      $values[$this->token_name.'.'.$token] = $value;
    }
  }

  /**
   * Returns true when token in set in the tokens array
   *
   * @param $tokens
   * @param $token
   * @return bool
   */
  protected function checkToken($tokens, $token) {
    if (!empty($tokens[$this->token_name])) {
      if (in_array($token, $tokens[$this->token_name])) {
        return true;
      } elseif (array_key_exists($token, $tokens[$this->token_name])) {
        return true;
      }
    }
    return false;
  }

}