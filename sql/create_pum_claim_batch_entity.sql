CREATE TABLE IF NOT EXISTS `pum_claim_batch_entity` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_table` VARCHAR(128) DEFAULT NULL,
  `entity_id` INT(10) UNSIGNED DEFAULT NULL,
  `batch_id` INT(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `FK_pum_claim_batch_1_idx` (`batch_id`),
  CONSTRAINT `FK_pum_claim_batch_1` FOREIGN KEY (`batch_id`) REFERENCES `pum_claim_batch` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;
