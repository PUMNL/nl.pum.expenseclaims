/* Update claim level cfo should have higher claim level then cpo */
UPDATE `civicrm_option_value` SET `value` = '3', `weight` = '3' WHERE `option_group_id` = (SELECT id FROM `civicrm_option_group` WHERE `name` = 'pum_claim_level') AND `name` = 'cpo';
UPDATE `civicrm_option_value` SET `value` = '4', `weight` = '4' WHERE `option_group_id` = (SELECT id FROM `civicrm_option_group` WHERE `name` = 'pum_claim_level') AND `name` = 'cfo';
/* now move authorizing level */
/* first move to temporary group to prevent conflicts */
UPDATE `pum_claim_level` SET `authorizing_level` = '4' WHERE `level` = '2';
UPDATE `pum_claim_level` SET `level` = '9993' WHERE `level` = '4';
UPDATE `pum_claim_level` SET `level` = '9994' WHERE `level` = '3';
/* now move authorizing level to the right authorizing level */
UPDATE `pum_claim_level` SET `level` = '3' WHERE `level` = '9993';
UPDATE `pum_claim_level` SET `level` = '4' WHERE `level` = '9994';

/* now move claim level valid main activities to the correct claim level
 first move to temporary group to prevent conflicts
UPDATE `pum_claim_level_main` SET `claim_level_id` = '9993' WHERE `claim_level_id` = '2'
UPDATE `pum_claim_level_main` SET `claim_level_id` = '9992' WHERE `claim_level_id` = '3'
 now move valid main activities to the right authorizing level
UPDATE `pum_claim_level_main` SET `claim_level_id` = '2' WHERE `claim_level_id` = '9992'
UPDATE `pum_claim_level_main` SET `claim_level_id` = '3' WHERE `claim_level_id` = '9993'

 now move contacts to the correct claim level
UPDATE `pum_claim_level_contact` SET `claim_level_id` = '9991' WHERE `claim_level_id` = '4'
UPDATE `pum_claim_level_contact` SET `claim_level_id` = '9994' WHERE `claim_level_id` = '1'
*/