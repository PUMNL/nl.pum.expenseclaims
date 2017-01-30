CREATE TABLE IF NOT EXISTS `pum_claim_level_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `claim_level_id` int(11) unsigned DEFAULT NULL,
  `type_value` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
