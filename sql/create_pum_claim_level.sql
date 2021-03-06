CREATE TABLE IF NOT EXISTS `pum_claim_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(80) DEFAULT NULL,
  `max_amount` decimal(11,2) DEFAULT NULL,
  `authorizing_level` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;

