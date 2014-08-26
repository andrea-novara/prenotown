CREATE TABLE IF NOT EXISTS `#__prenotown_preferences` (
  `preference` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY(preference)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__users` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `username` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `password_2` VARCHAR(255) NULL,
  `usertype` VARCHAR(255) NULL,
  `block` INTEGER UNSIGNED NULL,
  `sendEmail` INTEGER UNSIGNED NULL,
  `gid` INTEGER UNSIGNED NULL,
  `registerDate` DATETIME NULL,
  `lastvisitDate` DATETIME NULL,
  `activation` VARCHAR(255) NULL,
  `params` TEXT NULL,
  PRIMARY KEY(`id`)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_user_complement` (
  `id` INTEGER UNSIGNED NOT NULL,
  `status` ENUM('user', 'operator', 'admin', 'superadmin') NOT NULL DEFAULT 'user',
  `social_security_number` VARCHAR(16) NOT NULL,
  `address` VARCHAR(255) NULL,
  `town` VARCHAR(255) NULL,
  `district` VARCHAR(255) NULL,
  `nationality` VARCHAR(255) NULL,
  `ZIP` INTEGER(5) UNSIGNED NULL,
  `session_id` CHAR(40),
  PRIMARY KEY(id),
  UNIQUE KEY(`social_security_number`),
  INDEX #__prenotown_user_complement_idx_01(id)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

-- DELETE FROM `#__prenotown_user_complement` WHERE `id` = '62' AND `status` = 'superadmin';
-- INSERT INTO `#__prenotown_user_complement` (`id`, `status`) VALUES ('62', 'superadmin');

CREATE TABLE IF NOT EXISTS `#__prenotown_user_groups` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY(id),
  INDEX #__prenotown_user_groups_idx_01(`id`)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

DELETE FROM `#__prenotown_user_groups` WHERE `id` = '1' AND `name` = 'All';
INSERT INTO `#__prenotown_user_groups` VALUES (1, 'All');

CREATE TABLE IF NOT EXISTS `#__prenotown_user_group_entries` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INTEGER UNSIGNED NOT NULL,
  `user_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(group_id, user_id),
  INDEX #__prenotown_user_group_entries_idx_01(`id`),
  INDEX #__prenotown_user_group_entries_idx_02(`group_id`),
  INDEX #__prenotown_user_group_entries_idx_03(`user_id`),
  FOREIGN KEY(`group_id`)
    REFERENCES #__prenotown_user_groups(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(user_id)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_cost_function` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `class` VARCHAR(255) NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`name`),
  UNIQUE KEY(`class`),
  INDEX #__prenotown_cost_function_idx_01(`id`)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

DELETE FROM `#__prenotown_cost_function` WHERE `name` = "Time cost function" AND `class` = "PrenotownTimeCostFunction";
INSERT INTO `#__prenotown_cost_function` (`name`, `class`) VALUES ("Time cost function", "PrenotownTimeCostFunction");

CREATE TABLE IF NOT EXISTS `#__prenotown_resource` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INTEGER UNSIGNED NOT NULL,
  `cost_function_id` INTEGER UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `notes` TEXT NULL,
  `deadline` INTEGER UNSIGNED NOT NULL,
  `max_advance` INTEGER UNSIGNED NOT NULL,
  `paying_period` INTEGER UNSIGNED NOT NULL,
  `approval_period` INTEGER UNSIGNED NOT NULL,
  `description` TEXT NULL,
  `enabled` BOOLEAN NOT NULL DEFAULT FALSE,
  `availability_enabled` BOOLEAN NOT NULL DEFAULT FALSE,
  `monday_begin` TIME NOT NULL,
  `monday_end` TIME NOT NULL,
  `tuesday_begin` TIME NOT NULL,
  `tuesday_end` TIME NOT NULL,
  `wednesday_begin` TIME NOT NULL,
  `wednesday_end` TIME NOT NULL,
  `thursday_begin` TIME NOT NULL,
  `thursday_end` TIME NOT NULL,
  `friday_begin` TIME NOT NULL,
  `friday_end` TIME NOT NULL,
  `saturday_begin` TIME NOT NULL,
  `saturday_end` TIME NOT NULL,
  `sunday_begin` TIME NOT NULL,
  `sunday_end` TIME NOT NULL,
  PRIMARY KEY(`id`),
  INDEX #__prenotown_resource_idx_01(`id`),
  INDEX #__prenotown_resource_idx_02(`admin_id`),
  INDEX #__prenotown_resource_idx_03(`cost_function_id`),
  FOREIGN KEY(`admin_id`)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE NO ACTION
      ON UPDATE CASCADE,
  FOREIGN KEY(`cost_function_id`)
    REFERENCES #__prenotown_cost_function(`id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_time_cost_function_profile` (
  `id` INTEGER UNSIGNED UNIQUE NOT NULL,
  `measure_unit_value` INTEGER UNSIGNED NOT NULL DEFAULT 1,
  `measure_unit_base` ENUM('seconds','minutes','hours','days','weeks') NOT NULL DEFAULT 'minutes',
  INDEX #__prenotown_time_cost_function_idx_01(`id`),
  FOREIGN KEY(`id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_time_cost_function_fee` (
  `id` INTEGER UNSIGNED UNIQUE NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `resource_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`name`, `resource_id`),
  INDEX #__prenotown_time_cost_function_idx_01(`id`),
  INDEX #__prenotown_time_cost_function_idx_02(`name`, `resource_id`),
  INDEX #__prenotown_time_cost_function_idx_03(`resource_id`),
  FOREIGN KEY(`resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_time_cost_function_fee_groups` (
  `id` INTEGER UNSIGNED UNIQUE NOT NULL AUTO_INCREMENT,
  `fee_id` INTEGER UNSIGNED NOT NULL,
  `group_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`fee_id`, `group_id`),
  INDEX #__prenotown_time_cost_function_fee_groups_idx_01(`fee_id`),
  INDEX #__prenotown_time_cost_function_fee_groups_idx_02(`fee_id`),
  INDEX #__prenotown_time_cost_function_fee_groups_idx_03(`group_id`),
  FOREIGN KEY(`fee_id`)
    REFERENCES #__prenotown_time_cost_function_fee(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`group_id`)
    REFERENCES #__prenotown_user_groups(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_time_cost_function_fee_rules` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `fee_id` INTEGER UNSIGNED NOT NULL,
  `upper_limit` TIME NOT NULL,
  `cost` FLOAT NOT NULL,
  PRIMARY KEY(`id`),
  INDEX #__prenotown_time_cost_function_fee_rules_idx_01(`id`),
  INDEX #__prenotown_time_cost_function_fee_rules_idx_02(`fee_id`),
  FOREIGN KEY(`fee_id`)
    REFERENCES #__prenotown_time_cost_function_fee(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_admin` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_admin` INTEGER UNSIGNED NOT NULL,
  `id_resource` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX #__prenotown_resource_admin_idx_01(`id`),
  INDEX #__prenotown_resource_admin_idx_02(`id_resource`),
  INDEX #__prenotown_resource_admin_idx_03(`id_admin`),
  FOREIGN KEY(`id_resource`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(id_admin)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_attachment` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_id` INTEGER UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `filename` VARCHAR(1024) NOT NULL,
  PRIMARY KEY(`id`),
  INDEX #__prenotown_resource_attachment_idx_01(`id`),
  INDEX #__prenotown_resource_attachment_idx_02(`resource_id`),
  FOREIGN KEY(`resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`name`),
  INDEX #__prwnotown_resource_groups_idx_01(`id`)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_group_entries` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_id` INTEGER UNSIGNED NOT NULL,
  `group_id` INT NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`resource_id`, `group_id`),
  INDEX #__prenotown_resource_group_entries_idx_01(`id`),
  INDEX #__prenotown_resource_group_entries_idx_02(`resource_id`),
  INDEX #__prenotown_resource_group_entries_idx_03(`group_id`),
  FOREIGN KEY(`group_id`)
    REFERENCES #__prenotown_resource_groups(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(resource_id)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_components` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `component_resource_id` INTEGER UNSIGNED NOT NULL,
  `composed_resource_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`component_resource_id`, `composed_resource_id`),
  INDEX #__prenotown_resource_components_idx_01(`id`),
  INDEX #__prenotown_resource_components_idx_02(`composed_resource_id`),
  INDEX #__prenotown_resource_components_idx_03(`component_resource_id`),
  FOREIGN KEY(`composed_resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`component_resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_dependencies` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `slave_resource_id` INTEGER UNSIGNED NOT NULL,
  `master_resource_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`slave_resource_id`, `master_resource_id`),
  INDEX #__prenotown_resource_dependencies_idx_01(`id`),
  INDEX #__prenotown_resource_dependencies_idx_02(`master_resource_id`),
  INDEX #__prenotown_resource_dependencies_idx_03(`slave_resource_id`),
  FOREIGN KEY(`master_resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`slave_resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_payments` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INTEGER UNSIGNED NOT NULL,
  `amount` FLOAT UNSIGNED NOT NULL,
  `method` ENUM('check', 'credit_card') NOT NULL,
  `check_number` VARCHAR(255) NULL,
  `checked_by` INTEGER UNSIGNED DEFAULT NULL,
  date DATETIME NOT NULL,
  PRIMARY KEY(`id`),
  INDEX #__prenotown_payments_idx_01(`id`),
  INDEX #__prenotown_payments_idx_02(`user_id`),
  FOREIGN KEY(`user_id`)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_superbooking` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_id` INTEGER UNSIGNED NOT NULL,
  `payment_id` INTEGER UNSIGNED NULL,
  `user_id` INTEGER UNSIGNED NOT NULL,
  `group_id` INTEGER UNSIGNED NULL,
  `operator_id` INTEGER UNSIGNED,
  `periodic` BOOLEAN NOT NULL DEFAULT FALSE,
  `periodicity` INTEGER(64) UNSIGNED NOT NULL DEFAULT 0,
  `begin` DATETIME NOT NULL,
  `end` DATETIME NOT NULL,
  `cost` FLOAT UNSIGNED NOT NULL DEFAULT 0,
  `approved` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`resource_id`, `periodic`, `periodicity`, `begin`, `end`),
  INDEX #__prenotown_booking_idx_01(`id`),
  INDEX #__prenotown_booking_idx_02(`resource_id`),
  INDEX #__prenotown_booking_idx_03(`user_id`),
  INDEX #__prenotown_booking_idx_04(`payment_id`),
  FOREIGN KEY(`resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`user_id`)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(`payment_id`)
    REFERENCES #__prenotown_payments(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_superbooking_exception` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INTEGER UNSIGNED NOT NULL,
  `exception_date` DATE NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY(`booking_id`, `exception_date`),
  INDEX #__prenotown_booking_exception_idx_01(`id`),
  INDEX #__prenotown_booking_exception_idx_02(`booking_id`),
  FOREIGN KEY(`booking_id`)
    REFERENCES #__prenotown_superbooking(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

