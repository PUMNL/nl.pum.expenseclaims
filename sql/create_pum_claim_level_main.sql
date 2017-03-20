CREATE TABLE IF NOT EXISTS `pum_claim_level_main` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `claim_level_id` int(11) unsigned DEFAULT NULL,
  `main_activity_type_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
  COLLATE = utf8_general_ci;