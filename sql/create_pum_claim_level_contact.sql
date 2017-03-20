CREATE TABLE IF NOT EXISTS `pum_claim_level_contact` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_level_id` int(11) UNSIGNED DEFAULT NULL,
  `contact_id` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;
