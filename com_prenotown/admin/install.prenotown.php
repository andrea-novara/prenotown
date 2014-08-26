<?php
	global $table_prefix, $handle, $queries;

	require_once(JPATH_BASE."/../configuration.php");
	
	$table_prefix = "";
	$handle = NULL;
	$queries = Array();

	$queries[] = <<< EOF
CREATE TRIGGER `add_fee_base_rule` AFTER INSERT ON `#__prenotown_time_cost_function_fee`
FOR EACH ROW BEGIN
	INSERT INTO `#__prenotown_time_cost_function_fee_rules` (`fee_id`, `upper_limit`, `cost`) VALUES (NEW.id, '1:00:00', 1);
END;
EOF;

	$queries[] = <<<  EOF
CREATE TRIGGER `add_default_fee_to_resource` AFTER INSERT ON `#__prenotown_resource`
FOR EACH ROW BEGIN
	INSERT INTO `#__prenotown_time_cost_function_fee` (`name`, `resource_id`) VALUES ('Default', NEW.id);
END;
EOF;

	$queries[] = "DROP PROCEDURE IF EXISTS #__prenotown_initenv";
	$queries[] = <<< EOF
CREATE PROCEDURE #__prenotown_initenv()
BEGIN
	SET @monday    = 1;
	SET @tuesday   = 2;
	SET @wednesday = 4;
	SET @thursday  = 8;
	SET @friday    = 16;
	SET @saturday  = 32;
	SET @sunday    = 64;
END
EOF;

	$queries[] = "DROP FUNCTION IF EXISTS #__prenotown_day_bitmask";
	$queries[] = <<< EOF
CREATE FUNCTION #__prenotown_day_bitmask(begin_date DATE, end_date DATE)
RETURNS INTEGER(8)
READS SQL DATA
BEGIN
	DECLARE bitmask INTEGER DEFAULT 0;

	DECLARE begin_dow INTEGER DEFAULT 0;
	DECLARE end_dow INTEGER DEFAULT 0;
	DECLARE reverse BOOLEAN DEFAULT FALSE;
	DECLARE date_diff INTEGER DEFAULT 0;

	DECLARE i INTEGER DEFAULT 0;
	DECLARE stopcond INTEGER DEFAULT 0;

	SELECT WEEKDAY(begin_date) INTO begin_dow;
	SELECT WEEKDAY(end_date) INTO end_dow;

	IF end_date < begin_date THEN
		RETURN 0;
	ELSEIF begin_date = end_date THEN
		RETURN POW(2, begin_dow);
	ELSEIF DATE_ADD(begin_date, interval 7 day) <= end_date THEN
		RETURN 127;
	ELSEIF end_dow > begin_dow THEN
		SET reverse = FALSE;
		SET i = end_dow;
		SET stopcond = begin_dow;
	ELSE
		SET reverse = TRUE;
		SET i = begin_dow;
		SET stopcond = end_dow;
	END IF;

	WHILE i >= stopcond DO
		SET bitmask = bitmask + POW(2,i);
		SET i = i - 1;
	END WHILE;

	IF reverse = TRUE THEN
		SET bitmask = bitmask ^ 127 + POW(2, end_dow) + POW(2, begin_dow);
	END IF;

	RETURN bitmask;
END
EOF;

	$queries[] = "DROP FUNCTION IF EXISTS #__prenotown_booking_crosses_availability";
	$queries[] = <<< EOF
CREATE FUNCTION #__prenotown_booking_crosses_availability(rid INTEGER UNSIGNED, bitmask INTEGER UNSIGNED, begin_time TIME, end_time TIME)
RETURNS INTEGER
READS SQL DATA
BEGIN
	DECLARE avail_enab BOOLEAN DEFAULT FALSE;
	DECLARE day_begin TIME;
	DECLARE day_end TIME;
	DECLARE checkmask INTEGER(8) UNSIGNED DEFAULT 0;
	DECLARE result TINYINT SIGNED DEFAULT 0;

	CALL #__prenotown_initenv();

	SELECT availability_enabled INTO avail_enab FROM `#__prenotown_resource` WHERE `id` = rid;
	IF NOT avail_enab THEN
		RETURN 0;
	END IF;

	IF bitmask & @monday THEN
		SELECT monday_begin INTO day_begin FROM `#__prenotown_resource` WHERE `id` = rid;	
		SELECT monday_end INTO day_end FROM `#__prenotown_resource` WHERE `id` = rid;
		IF (TIME(begin_time) < TIME(day_begin)) || (TIME(end_time) > TIME(day_end)) THEN
			SET checkmask = checkmask | @monday;
		END IF;
	END IF;

	IF bitmask & @tuesday THEN
		SELECT tuesday_begin INTO day_begin FROM `#__prenotown_resource` WHERE `id` = rid;	
		SELECT tuesday_end INTO day_end FROM `#__prenotown_resource` WHERE `id` = rid;
		IF (TIME(begin_time) < TIME(day_begin)) || (TIME(end_time) > TIME(day_end)) THEN
			SET checkmask = checkmask | @tuesday;
		END IF;
	END IF;

	IF bitmask & @wednesday THEN
		SELECT wednesday_begin INTO day_begin FROM `#__prenotown_resource` WHERE `id` = rid;	
		SELECT wednesday_end INTO day_end FROM `#__prenotown_resource` WHERE `id` = rid;
		IF (TIME(begin_time) < TIME(day_begin)) || (TIME(end_time) > TIME(day_end)) THEN
			SET checkmask = checkmask | @wednesday;
		END IF;
	END IF;

	IF bitmask & @thursday THEN
		SELECT thursday_begin INTO day_begin FROM `#__prenotown_resource` WHERE `id` = rid;	
		SELECT thursday_end INTO day_end FROM `#__prenotown_resource` WHERE `id` = rid;
		IF (TIME(begin_time) < TIME(day_begin)) || (TIME(end_time) > TIME(day_end)) THEN
			SET checkmask = checkmask | @thursday;
		END IF;
	END IF;

	IF bitmask & @friday THEN
		SELECT friday_begin INTO day_begin FROM `#__prenotown_resource` WHERE `id` = rid;	
		SELECT friday_end INTO day_end FROM `#__prenotown_resource` WHERE `id` = rid;
		IF (TIME(begin_time) < TIME(day_begin)) || (TIME(end_time) > TIME(day_end)) THEN
			SET checkmask = checkmask | @friday;
		END IF;
	END IF;

	IF bitmask & @saturday THEN
		SELECT saturday_begin INTO day_begin FROM `#__prenotown_resource` WHERE `id` = rid;	
		SELECT saturday_end INTO day_end FROM `#__prenotown_resource` WHERE `id` = rid;
		IF (TIME(begin_time) < TIME(day_begin)) || (TIME(end_time) > TIME(day_end)) THEN
			SET checkmask = checkmask | @saturday;
		END IF;
	END IF;

	IF bitmask & @sunday THEN
		SELECT sunday_begin INTO day_begin FROM `#__prenotown_resource` WHERE `id` = rid;	
		SELECT sunday_end INTO day_end FROM `#__prenotown_resource` WHERE `id` = rid;
		IF (TIME(begin_time) < TIME(day_begin)) || (TIME(end_time) > TIME(day_end)) THEN
			SET checkmask = checkmask | @sunday;
		END IF;
	END IF;

	SET SQL_MODE='NO_UNSIGNED_SUBTRACTION';

	RETURN 0 - checkmask;
END
EOF;

	$queries[] = "DROP FUNCTION IF EXISTS #__prenotown_booking_crosses_availability_on_day_range";
	$queries[] = <<< EOF
CREATE FUNCTION #__prenotown_booking_crosses_availability_on_day_range(rid INTEGER UNSIGNED, begin_day DATE, end_day DATE, begin_time TIME, end_time TIME)
RETURNS INTEGER
READS SQL DATA
BEGIN
	DECLARE result BOOLEAN DEFAULT FALSE;
	DECLARE day_bitmask INTEGER UNSIGNED DEFAULT 0;

	SELECT #__prenotown_day_bitmask(begin_day, end_day) INTO day_bitmask;
	SELECT #__prenotown_booking_crosses_availability(rid, day_bitmask, begin_time, end_time) INTO result;

	RETURN result;
END
EOF;

	$queries[] = "DROP FUNCTION IF EXISTS #__prenotown_single_booking_overlapping";
	$queries[] = <<< EOF
CREATE FUNCTION #__prenotown_single_booking_overlapping(rid INTEGER UNSIGNED, booking_begin DATETIME, booking_end DATETIME)
RETURNS INTEGER
READS SQL DATA
BEGIN
	DECLARE check_date DATE;
	DECLARE result INTEGER DEFAULT 0;

	SELECT id INTO result FROM #__prenotown_superbooking
	WHERE #__prenotown_superbooking.resource_id = rid
		AND periodic = FALSE
		AND ((
			booking_begin <= #__prenotown_superbooking.end
			AND booking_end > #__prenotown_superbooking.begin
			AND booking_end <= #__prenotown_superbooking.end
		) OR (
			booking_end >= #__prenotown_superbooking.end
			AND booking_begin < #__prenotown_superbooking.end
		))
	LIMIT 1;
	
	IF result THEN
		RETURN result;
	END IF;

	SET result = 0;

	SET check_date = date(booking_begin);
	WHILE check_date <= date(booking_end) DO
		SET result = 0;

		SELECT `#__prenotown_superbooking`.`id`
		INTO result
		FROM `#__prenotown_superbooking`
		WHERE `resource_id` = rid
			AND DATE(booking_begin) <= DATE(#__prenotown_superbooking.end)
			AND DATE(booking_end) >= DATE(#__prenotown_superbooking.begin)
			AND TIME(booking_begin) < TIME(#__prenotown_superbooking.end)
			AND TIME(booking_end) > TIME(#__prenotown_superbooking.begin)
			AND `periodic` = TRUE
			AND `periodicity` & #__prenotown_day_bitmask(check_date, check_date)
			AND check_date NOT IN (
				SELECT exception_date
				FROM `#__prenotown_superbooking_exception`
				WHERE booking_id = `#__prenotown_superbooking`.`id`
			)
		LIMIT 1;

		IF result THEN
			RETURN result;
		END IF;

		SET check_date = DATE_ADD(check_date, INTERVAL 1 DAY);
	END WHILE;

	SET result = FALSE;
	SET result = #__prenotown_booking_crosses_availability_on_day_range(rid, DATE(booking_begin), DATE(booking_end), TIME(booking_begin), TIME(booking_end));

	RETURN result;
END
EOF;

	$queries[] = "DROP FUNCTION IF EXISTS #__prenotown_periodic_booking_overlapping";
	$queries[] = <<< EOF
CREATE FUNCTION #__prenotown_periodic_booking_overlapping(rid INTEGER UNSIGNED, booking_begin DATETIME, booking_end DATETIME, has_periodicity INTEGER UNSIGNED)
RETURNS INTEGER
READS SQL DATA
BEGIN
	DECLARE check_date DATE;
	DECLARE result INTEGER DEFAULT 0;
	DECLARE bitmask INTEGER DEFAULT 0;
	DECLARE is_periodic INTEGER DEFAULT 0;
	DECLARE begin_date DATE;
	DECLARE end_date DATE;

	SELECT `#__prenotown_superbooking`.`id`
	INTO result
	FROM `#__prenotown_superbooking`
	WHERE `resource_id` = rid
		AND `#__prenotown_superbooking`.`periodic` = 1
		AND `#__prenotown_superbooking`.`periodicity` & has_periodicity
		AND DATE(booking_begin) <= DATE(#__prenotown_superbooking.end)
		AND DATE(booking_end) >= DATE(#__prenotown_superbooking.begin)
		AND TIME(booking_begin) < TIME(#__prenotown_superbooking.end)
		AND TIME(booking_end) > TIME(#__prenotown_superbooking.begin)
	LIMIT 1;

	IF result THEN
		RETURN result;
	END IF;

	SET result = #__prenotown_booking_crosses_availability(rid, has_periodicity, TIME(booking_begin), TIME(booking_end));
	RETURN -1 * result;
END
EOF;

	$queries[] = "DROP FUNCTION IF EXISTS #__prenotown_booking_overlapping";
	$queries[] = <<< EOF
CREATE FUNCTION #__prenotown_booking_overlapping(rid INTEGER UNSIGNED, booking_begin DATETIME, booking_end DATETIME, has_periodicity INTEGER)
RETURNS INTEGER
READS SQL DATA
BEGIN
	DECLARE result INTEGER DEFAULT 0;

	IF booking_end < booking_begin THEN RETURN TRUE; END IF;

	IF has_periodicity > 0 THEN
		SELECT #__prenotown_periodic_booking_overlapping(rid, booking_begin, booking_end, has_periodicity) INTO result;
	ELSE
		SELECT #__prenotown_single_booking_overlapping(rid, booking_begin, booking_end) INTO result;
	END IF;

	RETURN result;
END
EOF;

	$queries[] = "DROP PROCEDURE IF EXISTS #__prenotown_booking_on_day_range";
	$queries[] = <<< EOF
CREATE PROCEDURE #__prenotown_booking_on_day_range(rid INTEGER UNSIGNED, begin_date DATE, end_date DATE)
BEGIN
	SELECT `id`, `payment_id`, `user_id`,
		DATE(`begin`) AS begin_date,
		TIME(`begin`) AS begin_time,
		HOUR(`begin`) * 60 + minute(`begin`) AS begin,
		DATE(`end`) AS end_date,
		TIME(`end`) AS end_time,
		HOUR(`end`) * 60 + minute(`begin`) AS end,
		periodic
	FROM `#__prenotown_superbooking`
	WHERE `resource_id` = rid
		AND date(end_date) >= date(`begin`)
		AND date(begin_date) <= date(`end`)
		AND (periodic = 0 OR #__prenotown_day_bitmask(begin_date, end_date) & periodicity)
	ORDER BY begin;
END
EOF;

	$queries[] = "DROP PROCEDURE IF EXISTS #__prenotown_booking_on_day";
	$queries[] = <<< EOF
CREATE PROCEDURE #__prenotown_booking_on_day(rid INTEGER UNSIGNED, day DATE)
BEGIN
	CALL #__prenotown_booking_on_day_range(rid, day, day);
END
EOF;

	$queries[] = "DROP PROCEDURE IF EXISTS #__prenotown_expand_booking_profile";
	$queries[] = <<< EOF
CREATE PROCEDURE #__prenotown_expand_booking_profile(begin DATETIME, end DATETIME, periodic BOOLEAN, periodicity INTEGER UNSIGNED, split_single_bookings BOOLEAN, reset BOOLEAN)
READS SQL DATA
BEGIN
	DECLARE length TIME DEFAULT "00:00:00";
	DECLARE length_seconds INTEGER UNSIGNED DEFAULT 0;
	DECLARE datepointer DATE;

	CALL #__prenotown_initenv();

	IF reset THEN
		DROP TABLE IF EXISTS `#__prenotown_booking_expansion`;
	END IF;

	CREATE TEMPORARY TABLE IF NOT EXISTS `#__prenotown_booking_expansion` (
		booking_id INTEGER UNSIGNED NOT NULL DEFAULT 0,
		resource_id INTEGER UNSIGNED NOT NULL DEFAULT 0,
		resource_name VARCHAR(255) NOT NULL DEFAULT "",
		user_name VARCHAR(255) NOT NULL DEFAULT "",
		group_name VARCHAR(255) NOT NULL DEFAULT "",

		begin_date DATE NOT NULL,
		begin_date_sec INTEGER UNSIGNED,

		end_date DATE NOT NULL,
		end_date_sec INTEGER UNSIGNED,

		begin_time TIME NOT NULL,
		begin_time_sec INTEGER UNSIGNED,

		end_time TIME NOT NULL,
		end_time_sec INTEGER UNSIGNED,

		length INTEGER UNSIGNED NOT NULL,
		excepted BOOLEAN DEFAULT FALSE,
		cost FLOAT NOT NULL DEFAULT 0.0,

  		INDEX #__prenotown_expansion_idx1(`begin_date`),
  		INDEX #__prenotown_expansion_idx2(`end_date`),
  		INDEX #__prenotown_expansion_idx3(`resource_id`),
  		INDEX #__prenotown_expansion_idx4(`booking_id`)
	);

	IF periodic THEN
		SET datepointer = DATE(begin);
		WHILE datepointer <= DATE(end) DO
			IF #__prenotown_day_bitmask(datepointer, datepointer) & periodicity THEN
				INSERT INTO `#__prenotown_booking_expansion` (begin_date, end_date, begin_time, end_time, length) VALUES (DATE(datepointer), DATE(datepointer), TIME(begin), TIME(end), TIME_TO_SEC(TIMEDIFF(TIME(end), TIME(begin))));
			END IF;
			SET datepointer = ADDDATE(datepointer, 1);
		END WHILE;
	ELSE
		IF DATE(begin) = DATE(end) THEN
			INSERT INTO `#__prenotown_booking_expansion` (begin_date, end_date, begin_time, end_time, length) VALUES (DATE(begin), DATE(end), TIME(begin), TIME(end), UNIX_TIMESTAMP(end) - UNIX_TIMESTAMP(begin));
		ELSEIF NOT split_single_bookings THEN
			INSERT INTO `#__prenotown_booking_expansion` (begin_date, end_date, begin_time, end_time, length) VALUES (DATE(begin), DATE(end), TIME(begin), TIME(end), UNIX_TIMESTAMP(end) - UNIX_TIMESTAMP(begin));
		ELSE
			INSERT INTO `#__prenotown_booking_expansion` (begin_date, end_date, begin_time, end_time, length) VALUES (DATE(begin), DATE(begin), TIME(begin), "23:59:59", TIME_TO_SEC("23:59:59") - TIME_TO_SEC(TIME(begin)));
			SET datepointer = ADDDATE(DATE(begin), 1);
			WHILE datepointer <= SUBDATE(DATE(end), 1) DO
				INSERT INTO `#__prenotown_booking_expansion` (begin_date, end_date, begin_time, end_time, length) VALUES (datepointer, datepointer, "00:00:00", "23:59:59", 60*60*24);
				SET datepointer = ADDDATE(datepointer, 1);
			END WHILE;
			INSERT INTO `#__prenotown_booking_expansion` (begin_date, end_date, begin_time, end_time, length) VALUES (DATE(end), DATE(end), "00:00:00", TIME(end), TIME_TO_SEC(TIME(end)));
		END IF;
	END IF;
END
EOF;

	$queries[] = "DROP PROCEDURE IF EXISTS #__prenotown_expand_booking_apply_availability";
	$queries[] = <<< EOF
CREATE PROCEDURE #__prenotown_expand_booking_apply_availability(rid INTEGER UNSIGNED)
READS SQL DATA
BEGIN
	DECLARE doit BOOLEAN;
	DECLARE monday_begin_var TIME;
	DECLARE monday_end_var TIME;
	DECLARE tuesday_begin_var TIME;
	DECLARE tuesday_end_var TIME;
	DECLARE wednesday_begin_var TIME;
	DECLARE wednesday_end_var TIME;
	DECLARE thursday_begin_var TIME;
	DECLARE thursday_end_var TIME;
	DECLARE friday_begin_var TIME;
	DECLARE friday_end_var TIME;
	DECLARE saturday_begin_var TIME;
	DECLARE saturday_end_var TIME;
	DECLARE sunday_begin_var TIME;
	DECLARE sunday_end_var TIME;

	CALL #__prenotown_initenv();

	SELECT availability_enabled, monday_begin, monday_end, tuesday_begin, tuesday_end, wednesday_begin, wednesday_end, thursday_begin, thursday_end, friday_begin, friday_end, saturday_begin, saturday_end, sunday_begin, sunday_end INTO doit, monday_begin_var, monday_end_var, tuesday_begin_var, tuesday_end_var, wednesday_begin_var, wednesday_end_var, thursday_begin_var, thursday_end_var, friday_begin_var, friday_end_var, saturday_begin_var, saturday_end_var, sunday_begin_var, sunday_end_var FROM #__prenotown_resource WHERE id = rid;

	IF doit THEN
		UPDATE #__prenotown_booking_expansion SET begin_time = monday_begin_var WHERE begin_time < monday_begin_var AND #__prenotown_day_bitmask(begin_date, begin_date) & @monday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET end_time = monday_end_var WHERE end_time > monday_end_var AND #__prenotown_day_bitmask(end_date, end_date) & @monday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET begin_time = tuesday_begin_var WHERE begin_time < tuesday_begin_var AND #__prenotown_day_bitmask(begin_date, begin_date) & @tuesday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET end_time = tuesday_end_var WHERE end_time > tuesday_end_var AND #__prenotown_day_bitmask(end_date, end_date) & @tuesday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET begin_time = wednesday_begin_var WHERE begin_time < wednesday_begin_var AND #__prenotown_day_bitmask(begin_date, begin_date) & @wednesday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET end_time = wednesday_end_var WHERE end_time > wednesday_end_var AND #__prenotown_day_bitmask(end_date, end_date) & @wednesday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET begin_time = thursday_begin_var WHERE begin_time < thursday_begin_var AND #__prenotown_day_bitmask(begin_date, begin_date) & @thursday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET end_time = thursday_end_var WHERE end_time > thursday_end_var AND #__prenotown_day_bitmask(end_date, end_date) & @thursday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET begin_time = friday_begin_var WHERE begin_time < friday_begin_var AND #__prenotown_day_bitmask(begin_date, begin_date) & @friday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET end_time = friday_end_var WHERE end_time > friday_end_var AND #__prenotown_day_bitmask(end_date, end_date) & @friday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET begin_time = saturday_begin_var WHERE begin_time < saturday_begin_var AND #__prenotown_day_bitmask(begin_date, begin_date) & @saturday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET end_time = saturday_end_var WHERE end_time > saturday_end_var AND #__prenotown_day_bitmask(end_date, end_date) & @saturday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET begin_time = sunday_begin_var WHERE begin_time < sunday_begin_var AND #__prenotown_day_bitmask(begin_date, begin_date) & @sunday AND resource_id = rid;
		UPDATE #__prenotown_booking_expansion SET end_time = sunday_end_var WHERE end_time > sunday_end_var AND #__prenotown_day_bitmask(end_date, end_date) & @sunday AND resource_id = rid;

		UPDATE #__prenotown_booking_expansion SET length = TIME_TO_SEC(TIMEDIFF(end_time, begin_time));
	END IF;

	CALL #__prenotown_expand_booking_apply_unavailability();
END
EOF;

	$queries[] = "DROP PROCEDURE IF EXISTS #__prenotown_apply_unavailability";
	$queries[] = <<< EOF
CREATE PROCEDURE `#__prenotown_expand_booking_apply_unavailability`()
READS SQL DATA
BEGIN   
	UPDATE #__prenotown_booking_expansion SET excepted = 2 WHERE (SELECT 1 AS excepted FROM #__prenotown_superbooking WHERE DATE(begin) <= end_date AND DATE(end) >= begin_date AND group_id = 2 AND #__prenotown_booking_expansion.resource_id = #__prenotown_superbooking.resource_id);
END
EOF;

	$queries[] = "DROP PROCEDURE IF EXISTS #__prenotown_expand_booking";
	$queries[] = <<< EOF
CREATE PROCEDURE #__prenotown_expand_booking(bid INTEGER UNSIGNED, OUT cost FLOAT, reset BOOLEAN)
READS SQL DATA
BEGIN
	DECLARE begin_var DATETIME;
	DECLARE end_var DATETIME;
	DECLARE periodic_var BOOLEAN;
	DECLARE periodicity_var INTEGER UNSIGNED;
	DECLARE rid INTEGER UNSIGNED;	-- resource id
	DECLARE gid INTEGER UNSIGNED;	-- group id
	DECLARE fid INTEGER UNSIGNED;	-- fee id
	DECLARE use_availability BOOLEAN;
	DECLARE exist INTEGER UNSIGNED;
	DECLARE user_name_var VARCHAR(255);
	DECLARE resource_name_var VARCHAR(255);
	DECLARE group_name_var VARCHAR(255);

	-- init the environment
	CALL #__prenotown_initenv();
	
	-- select booking profile if exists
	SELECT COUNT(*) INTO exist FROM #__prenotown_superbooking WHERE id = bid;
	IF exist THEN
		SELECT begin, end, periodic, periodicity, resource_id, #__prenotown_resource.name, group_id, #__prenotown_user_groups.name, #__users.name INTO begin_var, end_var, periodic_var, periodicity_var, rid, resource_name_var, gid, group_name_var, user_name_var FROM #__prenotown_superbooking JOIN #__users on #__users.id = #__prenotown_superbooking.user_id JOIN #__prenotown_user_groups ON #__prenotown_user_groups.id = #__prenotown_superbooking.group_id JOIN #__prenotown_resource ON #__prenotown_resource.id = #__prenotown_superbooking.resource_id WHERE #__prenotown_superbooking.id = bid;

		SELECT availability_enabled INTO use_availability FROM #__prenotown_resource WHERE id = rid;

		-- expand booking profile
		CALL #__prenotown_expand_booking_profile(begin_var, end_var, periodic_var, periodicity_var, use_availability, reset);

		-- save booking id and resource id and other info
		UPDATE #__prenotown_booking_expansion SET user_name = user_name_var WHERE booking_id = 0;
		UPDATE #__prenotown_booking_expansion SET group_name = group_name_var WHERE booking_id = 0;
		UPDATE #__prenotown_booking_expansion SET resource_name = resource_name_var WHERE booking_id = 0;
		UPDATE #__prenotown_booking_expansion SET booking_id = bid WHERE booking_id = 0;
		UPDATE #__prenotown_booking_expansion SET resource_id = rid WHERE resource_id = 0;

		-- apply exceptions to periodic bookings
		IF periodic_var THEN
			UPDATE #__prenotown_booking_expansion SET excepted = TRUE WHERE booking_id = bid AND begin_date IN (SELECT exception_date FROM #__prenotown_superbooking_exception WHERE booking_id = bid);
		END IF;

		-- apply availability from the resource
		CALL #__prenotown_expand_booking_apply_availability(rid);

		-- return booking cost using selected fee
		SELECT #__prenotown_time_cost_function_fee.id INTO fid FROM #__prenotown_time_cost_function_fee JOIN #__prenotown_time_cost_function_fee_groups ON #__prenotown_time_cost_function_fee_groups.fee_id = #__prenotown_time_cost_function_fee.id WHERE resource_id = rid AND group_id = gid;

		IF fid IS NULL THEN
			SELECT #__prenotown_time_cost_function_fee.id INTO fid FROM #__prenotown_time_cost_function_fee JOIN #__prenotown_time_cost_function_fee_groups ON #__prenotown_time_cost_function_fee_groups.fee_id = #__prenotown_time_cost_function_fee.id WHERE resource_id = rid AND group_id = 1;
			IF fid IS NULL THEN
				SET cost = 0.0;
			ELSE
				SELECT #__prenotown_get_cost(fid, bid) INTO cost;
			END IF;
		ELSE
			SELECT #__prenotown_get_cost(fid, bid) INTO cost;
		END IF;
	END IF;
END
EOF;

	$queries[] = "DROP FUNCTION IF EXISTS #__prenotown_apply_cost_per_day";
	$queries[] = <<< EOF
CREATE FUNCTION #__prenotown_apply_cost_per_day(length INTEGER UNSIGNED, fid INTEGER UNSIGNED)
RETURNS FLOAT
READS SQL DATA
BEGIN
	DECLARE total_cost FLOAT;
	DECLARE cost_var FLOAT DEFAULT 0.0;
	DECLARE amount FLOAT DEFAULT 0.0;
	DECLARE iteration_amount FLOAT DEFAULT 0.0;
	DECLARE upper_limit_var INTEGER UNSIGNED DEFAULT 0;
	DECLARE previous_limit_var INTEGER UNSIGNED;
	DECLARE last_rule_reached INTEGER DEFAULT 0;

	DECLARE c CURSOR FOR SELECT TIME_TO_SEC(upper_limit), cost FROM #__prenotown_time_cost_function_fee_rules WHERE fee_id = fid ORDER BY upper_limit ASC;

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET last_rule_reached = 1;

	SET amount = length;
	SET total_cost = 0.0;
	SET upper_limit_var = 0;

	OPEN c;
	REPEAT
		SET previous_limit_var = upper_limit_var;
		FETCH c INTO upper_limit_var, cost_var;

		IF last_rule_reached THEN
			SET total_cost = total_cost + cost_var * (amount / iteration_amount);
		ELSE
			SET iteration_amount = upper_limit_var - previous_limit_var;
			IF amount > iteration_amount THEN
				SET total_cost = total_cost + cost_var;
				SET amount = amount - iteration_amount;
			ELSE
				SET total_cost = total_cost + cost_var * (amount / iteration_amount);
				SET last_rule_reached = 1;
			END IF;
		END IF;
	UNTIL last_rule_reached END REPEAT;
	CLOSE c;

	RETURN total_cost;
END
EOF;

	$queries[] = "DROP FUNCTION IF EXISTS #__prenotown_get_cost";
	$queries[] = <<< EOF
CREATE FUNCTION #__prenotown_get_cost(fid INTEGER UNSIGNED, bid INTEGER UNSIGNED)
RETURNS FLOAT
READS SQL DATA
BEGIN
	UPDATE #__prenotown_booking_expansion SET cost = round(#__prenotown_apply_cost_per_day(length, fid), 2) WHERE booking_id = bid;
	SELECT SUM(cost) INTO @total_cost FROM #__prenotown_booking_expansion WHERE NOT excepted;

	RETURN @total_cost;
END
EOF;

	$queries[] = "DROP PROCEDURE IF EXISTS #__prenotown_expand_booking_for_resource";
	$queries[] = <<< EOF
CREATE PROCEDURE #__prenotown_expand_booking_for_resource(rid INTEGER UNSIGNED)
READS SQL DATA
BEGIN
	DECLARE cost INTEGER UNSIGNED;
	DECLARE bid INTEGER UNSIGNED;
	DECLARE last_rule_reached INTEGER UNSIGNED DEFAULT 0;

	DECLARE c CURSOR FOR SELECT id FROM #__prenotown_superbooking WHERE resource_id = rid;

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET last_rule_reached = 1;

	DROP TABLE IF EXISTS #__prenotown_booking_expansion;

	OPEN c;
	REPEAT
		FETCH c INTO bid;
		IF bid THEN
			CALL #__prenotown_expand_booking(bid, cost, 0);
		END IF;
	UNTIL last_rule_reached END REPEAT;
	CLOSE c;
END
EOF;

	function do_query($query) {
		global $table_prefix, $handle, $queries;

		// replace table prefix in queries
		$query = preg_replace("/#__/", $table_prefix, $query);

		// execute the query
		$result = mysql_query($query, $handle);

		// report
		if (!$result) {
			echo JText::_("Error executing query") . " [$query] [" . mysql_error($handle) . "]<br/>";
		//} else {
			//echo JText::_("Query executed correctly") . " [$query]<br/>";
		}
		return $result;
	}

	function com_install() {
		global $table_prefix, $handle, $queries;

		$jconfig = new JConfig();

		// connect to mysql
		$handle = mysql_connect($jconfig->host, $jconfig->user, $jconfig->password);

		// select database
		mysql_select_db($jconfig->db, $handle);

		// get table prefix from config
		$table_prefix = $jconfig->dbprefix;

		// process all the queries
		foreach ($queries as $query) {
			if (!do_query($query)) {
				return;
			}
		}
		echo JText::_("Installation successful");
	}
?>
