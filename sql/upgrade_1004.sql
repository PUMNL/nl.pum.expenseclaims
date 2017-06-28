ALTER TABLE pum.pum_claim_log
  ADD acting_approval_contact_id INT(11) unsigned DEFAULT NULL AFTER approval_contact_id;