CREATE TABLE IF NOT EXISTS `pum_claim_line` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) unsigned DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `expense_type` varchar(512) DEFAULT NULL,
  `currency_id` int(10) unsigned DEFAULT NULL,
  `currency_amount` decimal(11,2) DEFAULT NULL,
  `euro_amount` decimal(11,2) DEFAULT NULL,
  `exchange_rate` decimal(11,2) DEFAULT NULL,
  `description` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
