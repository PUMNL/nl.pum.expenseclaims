CREATE TABLE IF NOT EXISTS `pum_claim_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_activity_id` INT(11) UNSIGNED DEFAULT NULL,
  `approval_contact_id` INT(11) UNSIGNED DEFAULT NULL,
  `processed_date` DATE DEFAULT NULL,
  `is_approved` TINYINT(4) DEFAULT 0,
  `is_rejected` TINYINT(4) DEFAULT 0,
  `is_payable` TINYINT(4) DEFAULT 0,
  `old_status_id` VARCHAR(512) DEFAULT NULL,
  `new_status_id` VARCHAR(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
