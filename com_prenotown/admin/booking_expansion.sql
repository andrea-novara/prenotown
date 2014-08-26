DELIMITER $

-- expand a booking into a temporary table which describes each segment
DROP PROCEDURE IF EXISTS jos_prenotown_expand_booking_profile $
CREATE PROCEDURE jos_prenotown_expand_booking_profile(begin DATETIME, end DATETIME, periodic BOOLEAN, periodicity INTEGER UNSIGNED, split_single_bookings BOOLEAN)
READS SQL DATA
BEGIN
	DECLARE length TIME DEFAULT "00:00:00";
	DECLARE length_seconds INTEGER UNSIGNED DEFAULT 0;
	DECLARE datepointer DATE;

	CALL jos_prenotown_initenv();

	DROP TABLE IF EXISTS `jos_prenotown_booking_expansion`;
	CREATE TEMPORARY TABLE `jos_prenotown_booking_expansion` (
		begin_date DATE NOT NULL,
		end_date DATE NOT NULL,
		begin_time TIME NOT NULL,
		end_time TIME NOT NULL,
		length INTEGER UNSIGNED NOT NULL,
		excepted BOOLEAN DEFAULT FALSE,
		cost FLOAT NOT NULL DEFAULT 0.0
	);

	IF periodic THEN
		SET datepointer = DATE(begin);
		WHILE datepointer <= DATE(end) DO
			IF jos_prenotown_day_bitmask(datepointer, datepointer) & periodicity THEN
				INSERT INTO `jos_prenotown_booking_expansion` VALUES (DATE(datepointer), DATE(datepointer), TIME(begin), TIME(end), TIME_TO_SEC(TIMEDIFF(TIME(end), TIME(begin))), FALSE, 0.0);
			END IF;
			SET datepointer = ADDDATE(datepointer, 1);
		END WHILE;
	ELSE
		IF DATE(begin) = DATE(end) THEN
			INSERT INTO `jos_prenotown_booking_expansion` VALUES (DATE(begin), DATE(end), TIME(begin), TIME(end), UNIX_TIMESTAMP(end) - UNIX_TIMESTAMP(begin), FALSE, 0.0);
		ELSEIF NOT split_single_bookings THEN
			INSERT INTO `jos_prenotown_booking_expansion` VALUES (DATE(begin), DATE(end), TIME(begin), TIME(end), UNIX_TIMESTAMP(end) - UNIX_TIMESTAMP(begin), FALSE, 0.0);
		ELSE
			INSERT INTO `jos_prenotown_booking_expansion` VALUES (DATE(begin), DATE(begin), TIME(begin), "23:59:59", TIME_TO_SEC("23:59:59") - TIME_TO_SEC(TIME(begin)), FALSE, 0.0);
			SET datepointer = ADDDATE(DATE(begin), 1);
			WHILE datepointer <= SUBDATE(DATE(end), 1) DO
				INSERT INTO `jos_prenotown_booking_expansion` VALUES (datepointer, datepointer, "00:00:00", "23:59:59", 60*60*24, FALSE, 0.0);
				SET datepointer = ADDDATE(datepointer, 1);
			END WHILE;
			INSERT INTO `jos_prenotown_booking_expansion` VALUES (DATE(end), DATE(end), "00:00:00", TIME(end), TIME_TO_SEC(TIME(end)), FALSE, 0.0);
		END IF;
	END IF;
END $

-- change the temporary table by trimming segments to reflect booking intervals
DROP PROCEDURE IF EXISTS jos_prenotown_expand_booking_apply_availability$
CREATE PROCEDURE jos_prenotown_expand_booking_apply_availability(resource_id INTEGER UNSIGNED)
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

	CALL jos_prenotown_initenv();

	SELECT availability_enabled, monday_begin, monday_end, tuesday_begin, tuesday_end, wednesday_begin, wednesday_end, thursday_begin, thursday_end, friday_begin, friday_end, saturday_begin, saturday_end, sunday_begin, sunday_end INTO doit, monday_begin_var, monday_end_var, tuesday_begin_var, tuesday_end_var, wednesday_begin_var, wednesday_end_var, thursday_begin_var, thursday_end_var, friday_begin_var, friday_end_var, saturday_begin_var, saturday_end_var, sunday_begin_var, sunday_end_var FROM jos_prenotown_resource WHERE id = resource_id;

	IF doit THEN
		UPDATE jos_prenotown_booking_expansion SET begin_time = monday_begin_var WHERE begin_time < monday_begin_var AND jos_prenotown_day_bitmask(begin_date, begin_date) & @monday;
		UPDATE jos_prenotown_booking_expansion SET end_time = monday_end_var WHERE end_time > monday_end_var AND jos_prenotown_day_bitmask(end_date, end_date) & @monday;
		UPDATE jos_prenotown_booking_expansion SET begin_time = tuesday_begin_var WHERE begin_time < tuesday_begin_var AND jos_prenotown_day_bitmask(begin_date, begin_date) & @tuesday;
		UPDATE jos_prenotown_booking_expansion SET end_time = tuesday_end_var WHERE end_time > tuesday_end_var AND jos_prenotown_day_bitmask(end_date, end_date) & @tuesday;
		UPDATE jos_prenotown_booking_expansion SET begin_time = wednesday_begin_var WHERE begin_time < wednesday_begin_var AND jos_prenotown_day_bitmask(begin_date, begin_date) & @wednesday;
		UPDATE jos_prenotown_booking_expansion SET end_time = wednesday_end_var WHERE end_time > wednesday_end_var AND jos_prenotown_day_bitmask(end_date, end_date) & @wednesday;
		UPDATE jos_prenotown_booking_expansion SET begin_time = thursday_begin_var WHERE begin_time < thursday_begin_var AND jos_prenotown_day_bitmask(begin_date, begin_date) & @thursday;
		UPDATE jos_prenotown_booking_expansion SET end_time = thursday_end_var WHERE end_time > thursday_end_var AND jos_prenotown_day_bitmask(end_date, end_date) & @thursday;
		UPDATE jos_prenotown_booking_expansion SET begin_time = friday_begin_var WHERE begin_time < friday_begin_var AND jos_prenotown_day_bitmask(begin_date, begin_date) & @friday;
		UPDATE jos_prenotown_booking_expansion SET end_time = friday_end_var WHERE end_time > friday_end_var AND jos_prenotown_day_bitmask(end_date, end_date) & @friday;
		UPDATE jos_prenotown_booking_expansion SET begin_time = saturday_begin_var WHERE begin_time < saturday_begin_var AND jos_prenotown_day_bitmask(begin_date, begin_date) & @saturday;
		UPDATE jos_prenotown_booking_expansion SET end_time = saturday_end_var WHERE end_time > saturday_end_var AND jos_prenotown_day_bitmask(end_date, end_date) & @saturday;
		UPDATE jos_prenotown_booking_expansion SET begin_time = sunday_begin_var WHERE begin_time < sunday_begin_var AND jos_prenotown_day_bitmask(begin_date, begin_date) & @sunday;
		UPDATE jos_prenotown_booking_expansion SET end_time = sunday_end_var WHERE end_time > sunday_end_var AND jos_prenotown_day_bitmask(end_date, end_date) & @sunday;

		UPDATE jos_prenotown_booking_expansion SET length = TIME_TO_SEC(TIMEDIFF(end_time, begin_time));
	END IF;
END$

-- expand a booking into a temporary table, applies availability ranges and insert existing exceptions
DROP PROCEDURE IF EXISTS jos_prenotown_expand_booking$
CREATE PROCEDURE jos_prenotown_expand_booking(bid INTEGER UNSIGNED, OUT cost FLOAT)
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

	-- init the environment
	CALL jos_prenotown_initenv();
	
	-- select booking profile if exists
	SELECT COUNT(*) INTO exist FROM jos_prenotown_superbooking WHERE id = bid;
	IF exist THEN
		SELECT begin, end, periodic, periodicity, resource_id, group_id INTO begin_var, end_var, periodic_var, periodicity_var, rid, gid FROM jos_prenotown_superbooking WHERE id = bid;

		SELECT availability_enabled INTO use_availability FROM jos_prenotown_resource WHERE id = rid;

		-- expand booking profile
		CALL jos_prenotown_expand_booking_profile(begin_var, end_var, periodic_var, periodicity_var, use_availability);

		-- apply exceptions to periodic bookings
		IF periodic_var THEN
			UPDATE jos_prenotown_booking_expansion SET excepted = TRUE WHERE begin_date IN (SELECT exception_date FROM jos_prenotown_superbooking_exception WHERE booking_id = bid);
		END IF;

		-- apply availability from the resource
		CALL jos_prenotown_expand_booking_apply_availability(rid);

		-- return booking cost using selected fee
		SELECT jos_prenotown_time_cost_function_fee.id INTO fid FROM jos_prenotown_time_cost_function_fee JOIN jos_prenotown_time_cost_function_fee_groups ON jos_prenotown_time_cost_function_fee_groups.fee_id = jos_prenotown_time_cost_function_fee.id WHERE resource_id = rid AND group_id = gid;

		IF fid IS NULL THEN
			SELECT jos_prenotown_time_cost_function_fee.id INTO fid FROM jos_prenotown_time_cost_function_fee JOIN jos_prenotown_time_cost_function_fee_groups ON jos_prenotown_time_cost_function_fee_groups.fee_id = jos_prenotown_time_cost_function_fee.id WHERE resource_id = rid AND group_id = 1;
			IF fid IS NULL THEN
				SET cost = 0.0;
			ELSE
				SELECT jos_prenotown_get_cost(fid) INTO cost;
			END IF;
		ELSE
			SELECT jos_prenotown_get_cost(fid) INTO cost;
		END IF;
	END IF;
END$

-- calculate the cost of a segment, given in seconds (lengh argument)
DROP FUNCTION IF EXISTS jos_prenotown_apply_cost_per_day$
CREATE FUNCTION jos_prenotown_apply_cost_per_day(length INTEGER UNSIGNED, fid INTEGER UNSIGNED)
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

	DECLARE c CURSOR FOR SELECT TIME_TO_SEC(upper_limit), cost FROM jos_prenotown_time_cost_function_fee_rules WHERE fee_id = fid ORDER BY upper_limit ASC;

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
END$

-- calculate the cost of all the segments composing a booking
DROP FUNCTION IF EXISTS jos_prenotown_get_cost$
CREATE FUNCTION jos_prenotown_get_cost(fid INTEGER UNSIGNED)
RETURNS FLOAT
READS SQL DATA
BEGIN
	UPDATE jos_prenotown_booking_expansion SET cost = round(jos_prenotown_apply_cost_per_day(length, fid), 2);
	SELECT SUM(cost) INTO @total_cost FROM jos_prenotown_booking_expansion WHERE NOT excepted;

	RETURN @total_cost;
END$

DELIMITER ;
