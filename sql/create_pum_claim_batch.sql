CREATE TABLE IF NOT EXISTS `pum_claim_batch` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(256) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `batch_status_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_general_ci;
