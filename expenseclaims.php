<?php

require_once 'expenseclaims.civix.php';

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function expenseclaims_civicrm_buildForm($formName, &$form) {
  CRM_Expenseclaims_BAO_Claim::buildForm($formName, $form);
}

/**
 * Implements hook_civicrm_permission
 *
 * @param $permissions
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function expenseclaims_civicrm_permission(&$permissions) {
  $prefix = ts('CiviCRM PUM Senior Experts Expense Claims') . ': ';
  $permissions['pum expense claims'] = $prefix . ts('pum expense claims');
}


/**
 * Implementation of hook civicrm_navigationMenu
 * to create claims menu
 *
 * @param array $params
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function expenseclaims_civicrm_navigationMenu( &$params ) {
  if (!class_exists('CRM_Expenseclaims_Config')) {
    require_once('CRM/Expenseclaims/Config.php');
  }
  $config = CRM_Expenseclaims_Config::singleton();
  // get custom search for claim batch search and process
  try {
    $batchSearchId = civicrm_api3('OptionValue', 'getvalue', array(
      'option_group_id' => 'custom_search',
      'name' => 'CRM_Expenseclaims_Form_Search_FindBatch',
      'return' => 'value'
    ));
  } catch (CiviCRM_API3_Exception $ex) {}
  $maxKey = (max(array_keys($params)));
  $params[$maxKey+1] = array (
    'attributes' => array (
      'label'      => 'Claims',
      'name'       => 'pum_expense_claims',
      'url'        => null,
      //'permission' => 'access CiviCRM',
      'operator'   => null,
      'separator'  => null,
      'parentID'   => null,
      'navID'      => $maxKey+1,
      'active'     => 1
    ),
    'child' =>  array (
      '1' => array (
        'attributes' => array (
          'label'      => 'My Claims',
          'name'       => 'pum_expense_claims_my_claims',
          'url'        => CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1', true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 1,
          'active'     => 1
        ),
        'child' => null
      ),
      '2' => array (
        'attributes' => array (
          'label'      => 'Claim Batches',
          'name'       => 'pum_expense_claim_batches',
          'url'        => CRM_Utils_System::url('civicrm/contact/search/custom', 'reset=1&csid='.$batchSearchId, true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 2,
          'active'     => 1
        ),
        'child' => null
      ),
      '3' => array (
        'attributes' => array (
          'label'      => 'Claim Authorization',
          'name'       => 'pum_expense_claim_authorization',
          'url'        => CRM_Utils_System::url('civicrm/pumexpenseclaims/page/claimlevel', 'reset=1', true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 3,
          'active'     => 1
        ),
        'child' => null
      ),
      '4' => array (
        'attributes' => array (
          'label'      => 'Claim Levels',
          'name'       => 'pum_expense_claims_levels',
          'url'        => CRM_Utils_System::url('civicrm/admin/optionValue', 'reset=1&gid='.$config->getClaimLevelOptionGroup('id'), true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 4,
          'active'     => 1
        ),
        'child' => null
      ),
      '5' => array (
        'attributes' => array (
          'label'      => 'Claim Status',
          'name'       => 'pum_expense_claims_status',
          'url'        => CRM_Utils_System::url('civicrm/admin/optionValue', 'reset=1&gid='.$config->getClaimStatusOptionGroup('id'), true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 5,
          'active'     => 1
        ),
        'child' => null
      ),
      '6' => array (
        'attributes' => array (
          'label'      => 'Claim Types',
          'name'       => 'pum_expense_claims_types',
          'url'        => CRM_Utils_System::url('civicrm/admin/optionValue', 'reset=1&gid='.$config->getClaimTypeOptionGroup('id'), true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 6,
          'active'     => 1
        ),
        'child' => null
      ),
      '7' => array (
        'attributes' => array (
          'label'      => 'Claim Line Types',
          'name'       => 'pum_expense_claims_line_types',
          'url'        => CRM_Utils_System::url('civicrm/admin/optionValue', 'reset=1&gid='.$config->getClaimLineTypeOptionGroup('id'), true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 7,
          'active'     => 1
        ),
        'child' => null
      ),
      '8' => array (
        'attributes' => array (
          'label'      => 'Claim Batch Status',
          'name'       => 'pum_expense_claims_batch_status',
          'url'        => CRM_Utils_System::url('civicrm/admin/optionValue', 'reset=1&gid='.$config->getBatchStatusOptionGroup('id'), true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 8,
          'active'     => 1
        ),
        'child' => null
      ),
      '9' => array (
        'attributes' => array (
          'label'      => 'Others Claims',
          'name'       => 'pum_expense_claims_other_peoples_claims',
          'url'        => CRM_Utils_System::url('civicrm/pumexpenseclaims/otherpeoplesclaims', 'reset=1', true),
          'operator'   => null,
          'separator'  => 0,
          'parentID'   => $maxKey+1,
          'navID'      => 8,
          'active'     => 1
        ),
        'child' => null
      ),
      ));
}




/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function expenseclaims_civicrm_config(&$config) {
  _expenseclaims_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function expenseclaims_civicrm_xmlMenu(&$files) {
  _expenseclaims_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function expenseclaims_civicrm_install() {
  _expenseclaims_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function expenseclaims_civicrm_postInstall() {
  _expenseclaims_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function expenseclaims_civicrm_uninstall() {
  _expenseclaims_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function expenseclaims_civicrm_enable() {
  _expenseclaims_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function expenseclaims_civicrm_disable() {
  _expenseclaims_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function expenseclaims_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _expenseclaims_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function expenseclaims_civicrm_managed(&$entities) {
  _expenseclaims_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function expenseclaims_civicrm_caseTypes(&$caseTypes) {
  _expenseclaims_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function expenseclaims_civicrm_angularModules(&$angularModules) {
  _expenseclaims_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function expenseclaims_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _expenseclaims_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function expenseclaims_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function expenseclaims_civicrm_navigationMenu(&$menu) {
  _expenseclaims_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'nl.pum.expenseclaims')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _expenseclaims_civix_navigationMenu($menu);
} // */

function expenseclaims_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions)
{
  $permissions['claim_line_log']['get'] = array('access CiviCRM');
  $permissions['claim_batch']['create'] = array('access CiviCRM');
}
