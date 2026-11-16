-- popcabdb: the one column the site needs that your tables don't have yet.
-- Run once: phpMyAdmin -> select popcabdb -> SQL tab -> paste -> Go.

ALTER TABLE `users`
  ADD COLUMN `user_role` ENUM('customer','admin') NOT NULL DEFAULT 'customer';

-- Every existing and new account is a 'customer'. To make someone an admin, register
-- them on the site first, then run (with their real username):
--
--   UPDATE `users` SET `user_role` = 'admin' WHERE `username` = 'your_username';
--
-- (They need to log out and back in for the change to take effect.)
