ALTER TABLE `event_registrations`
  ADD COLUMN `receive_updates` tinyint(1) NOT NULL DEFAULT 1 AFTER `status`;
