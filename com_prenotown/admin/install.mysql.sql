CREATE TABLE IF NOT EXISTS `#__prenotown_preferences` (
  `preference` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (preference)
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
  PRIMARY KEY (`id`)
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
  PRIMARY KEY (id),
  UNIQUE KEY (`social_security_number`)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

DELETE FROM `#__prenotown_user_complement` WHERE `id` = '62' AND `status` = 'superadmin';
INSERT INTO `#__prenotown_user_complement` (`id`, `status`) VALUES ('62', 'superadmin');

CREATE TABLE IF NOT EXISTS `#__prenotown_user_groups` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

INSERT INTO `#__prenotown_user_groups` VALUES (1, 'All');
INSERT INTO `#__prenotown_user_groups` VALUES (2, 'Resource unavailability');
INSERT INTO `#__prenotown_user_groups` VALUES (100, 'Last reserved group');

CREATE TABLE IF NOT EXISTS `#__prenotown_user_group_entries` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INTEGER UNSIGNED NOT NULL,
  `user_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (group_id, user_id),
  CONSTRAINT fk_prenotown_user_group_entries_group_id
  FOREIGN KEY (group_id)
    REFERENCES #__prenotown_user_groups(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_user_group_entries_user_id
  FOREIGN KEY (user_id)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_cost_function` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `class` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`),
  UNIQUE KEY (`class`)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

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
  `enabled` INTEGER UNSIGNED NOT NULL DEFAULT FALSE,
  `availability_enabled` INTEGER UNSIGNED NOT NULL DEFAULT FALSE,
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
  PRIMARY KEY (`id`),
  INDEX #__prenotown_resource_idx_02(`admin_id`),
  INDEX #__prenotown_resource_idx_03(`cost_function_id`),
  CONSTRAINT fk_prenotown_cost_function_admin_id
  FOREIGN KEY (`admin_id`)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE RESTRICT
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_cost_function_cost_function_id
  FOREIGN KEY (`cost_function_id`)
    REFERENCES #__prenotown_cost_function(`id`)
      ON DELETE RESTRICT
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_time_cost_function_profile` (
  `id` INTEGER UNSIGNED UNIQUE NOT NULL,
  `measure_unit_value` INTEGER UNSIGNED NOT NULL DEFAULT 1,
  `measure_unit_base` ENUM('seconds','minutes','hours','days','weeks') NOT NULL DEFAULT 'minutes',
  CONSTRAINT fk_prenotown_time_cost_function_profile_id
  FOREIGN KEY (`id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_time_cost_function_fee` (
  `id` INTEGER UNSIGNED UNIQUE NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `resource_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`, `resource_id`),
  INDEX #__prenotown_time_cost_function_idx_02(`name`, `resource_id`),
  INDEX #__prenotown_time_cost_function_idx_03(`resource_id`),
  CONSTRAINT fk_prenotown_time_cost_function_fee_resource_id
  FOREIGN KEY (`resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_time_cost_function_fee_groups` (
  `id` INTEGER UNSIGNED UNIQUE NOT NULL AUTO_INCREMENT,
  `fee_id` INTEGER UNSIGNED NOT NULL,
  `group_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`fee_id`, `group_id`),
  INDEX #__prenotown_time_cost_function_fee_groups_idx_01(`fee_id`),
  INDEX #__prenotown_time_cost_function_fee_groups_idx_02(`group_id`),
  CONSTRAINT fk_prenotown_time_cost_function_fee_groups_fee_id
  FOREIGN KEY (`fee_id`)
    REFERENCES #__prenotown_time_cost_function_fee(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_time_cost_function_fee_groups_group_id
  FOREIGN KEY (`group_id`)
    REFERENCES #__prenotown_user_groups(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_time_cost_function_fee_rules` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `fee_id` INTEGER UNSIGNED NOT NULL,
  `upper_limit` TIME NOT NULL,
  `cost` FLOAT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX #__prenotown_time_cost_function_fee_rules_idx_02(`fee_id`),
  CONSTRAINT fk_prenotown_time_cost_function_fee_rules_fee_id
  FOREIGN KEY (`fee_id`)
    REFERENCES #__prenotown_time_cost_function_fee(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_admin` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_admin` INTEGER UNSIGNED NOT NULL,
  `id_resource` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX #__prenotown_resource_admin_idx_02(`id_resource`),
  INDEX #__prenotown_resource_admin_idx_03(`id_admin`),
  CONSTRAINT fk_prenotown_resource_admin_resource_id
  FOREIGN KEY (`id_resource`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_resource_admin_id_admin
  FOREIGN KEY (id_admin)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_attachment` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_id` INTEGER UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `filename` VARCHAR(1024) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX #__prenotown_resource_attachment_idx_02(`resource_id`),
  CONSTRAINT fk_prenotown_resource_attachment_resource_id
  FOREIGN KEY (`resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_groups` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_group_entries` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_id` INTEGER UNSIGNED NOT NULL,
  `group_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`resource_id`, `group_id`),
  INDEX #__prenotown_resource_group_entries_idx_02(`resource_id`),
  INDEX #__prenotown_resource_group_entries_idx_03(`group_id`),
  CONSTRAINT fk_prenotown_resource_group_entries_group_id
  FOREIGN KEY (`group_id`)
    REFERENCES #__prenotown_resource_groups(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_resource_group_entries_resource_id
  FOREIGN KEY (resource_id)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_components` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `component_resource_id` INTEGER UNSIGNED NOT NULL,
  `composed_resource_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`component_resource_id`, `composed_resource_id`),
  INDEX #__prenotown_resource_components_idx_02(`composed_resource_id`),
  INDEX #__prenotown_resource_components_idx_03(`component_resource_id`),
  CONSTRAINT fk_prenotown_resource_components_composed_resource_id
  FOREIGN KEY (`composed_resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_resource_components_component_resource_id
  FOREIGN KEY (`component_resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_resource_dependencies` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `slave_resource_id` INTEGER UNSIGNED NOT NULL,
  `master_resource_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`slave_resource_id`, `master_resource_id`),
  INDEX #__prenotown_resource_dependencies_idx_02(`master_resource_id`),
  INDEX #__prenotown_resource_dependencies_idx_03(`slave_resource_id`),
  CONSTRAINT fk_prenotown_resource_dependencies_master_resource_id
  FOREIGN KEY (`master_resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_resource_dependencies_slave_resource_id
  FOREIGN KEY (`slave_resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_payments` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INTEGER UNSIGNED NOT NULL,
  `amount` FLOAT UNSIGNED NOT NULL,
  `method` ENUM('check', 'credit_card', 'pos') NOT NULL,
  `check_number` VARCHAR(255) NULL,
  `checked_by` INTEGER UNSIGNED DEFAULT NULL,
  date DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX #__prenotown_payments_idx_02(`user_id`),
  CONSTRAINT fk_prenotown_payments_user_id
  FOREIGN KEY (`user_id`)
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
  `periodic` INTEGER UNSIGNED NOT NULL DEFAULT FALSE,
  `periodicity` INTEGER(64) UNSIGNED NOT NULL DEFAULT 0,
  `begin` DATETIME NOT NULL,
  `end` DATETIME NOT NULL,
  `cost` FLOAT UNSIGNED NOT NULL DEFAULT 0,
  `approved` INTEGER UNSIGNED NOT NULL DEFAULT FALSE,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`resource_id`, `periodic`, `periodicity`, `begin`, `end`),
  INDEX #__prenotown_booking_idx_02(`resource_id`),
  INDEX #__prenotown_booking_idx_03(`user_id`),
  INDEX #__prenotown_booking_idx_04(`payment_id`),
  CONSTRAINT fk_prenotown_superbooking_resource_id
  FOREIGN KEY (`resource_id`)
    REFERENCES #__prenotown_resource(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_superbooking_user_id
  FOREIGN KEY (`user_id`)
    REFERENCES #__prenotown_user_complement(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_superbooking_payment_id
  FOREIGN KEY (`payment_id`)
    REFERENCES #__prenotown_payments(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_prenotown_superbooking_group_id
  FOREIGN KEY (`group_id`)
    REFERENCES #__prenotown_user_groups(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__prenotown_superbooking_exception` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INTEGER UNSIGNED NOT NULL,
  `exception_date` DATE NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`booking_id`, `exception_date`),
  INDEX #__prenotown_booking_exception_idx_02(`booking_id`),
  CONSTRAINT fk_prenotown_superbooking_exception_booking_id
  FOREIGN KEY (`booking_id`)
    REFERENCES #__prenotown_superbooking(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB CHARACTER SET `utf8` COLLATE `utf8_general_ci`;
