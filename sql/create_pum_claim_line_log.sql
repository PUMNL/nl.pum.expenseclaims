CREATE TABLE IF NOT EXISTS `pum_claim_line_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_line_id` INT(11) UNSIGNED DEFAULT NULL,
  `changed_by_id` INT(11) UNSIGNED DEFAULT NULL,
  `changed_date` DATETIME DEFAULT NULL,
  `change_reason` VARCHAR(256) DEFAULT NULL,
  `old_expense_date` DATE DEFAULT NULL,
  `new_expense_date` DATE DEFAULT NULL,
  `old_currency_id` INT(11) UNSIGNED DEFAULT NULL,
  `new_currency_id` INT(11) UNSIGNED DEFAULT NULL,
  `old_currency_amount` DECIMAL(11,2) DEFAULT NULL,
  `new_currency_amount` DECIMAL(11,2) DEFAULT NULL,
  `old_euro_amount` DECIMAL(11,2) DEFAULT NULL,
  `new_euro_amount` DECIMAL(11,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `FK_pum_claim_line_1_idx` (`claim_line_id`),
  CONSTRAINT `FK_pum_claim_line_1` FOREIGN KEY (`claim_line_id`) REFERENCES `pum_claim_line` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;
