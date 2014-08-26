DROP PROCEDURE	IF EXISTS #__prenotown_booking_on_day;
DROP PROCEDURE	IF EXISTS #__prenotown_booking_on_day_range;
DROP PROCEDURE	IF EXISTS #__prenotown_initenv;
DROP FUNCTION	IF EXISTS #__prenotown_day_bitmask;
DROP FUNCTION	IF EXISTS #__prenotown_booking_crosses_availability;
DROP FUNCTION	IF EXISTS #__prenotown_booking_crosses_availability_on_day_range;
DROP FUNCTION	IF EXISTS #__prenotown_single_booking_overlapping;
DROP FUNCTION	IF EXISTS #__prenotown_periodic_booking_overlapping;
DROP FUNCTION	IF EXISTS #__prenotown_booking_overlapping;

DROP TABLE	IF EXISTS #__prenotown_time_cost_function_profile;
DROP TABLE	IF EXISTS #__prenotown_time_cost_function_fee_groups;
DROP TABLE	IF EXISTS #__prenotown_time_cost_function_fee_rules;
DROP TABLE	IF EXISTS #__prenotown_time_cost_function_fee;

DROP TABLE	IF EXISTS #__prenotown_user_group_entries;
DROP TABLE	IF EXISTS #__prenotown_user_groups;

DROP TABLE	IF EXISTS #__prenotown_resource_admin;
DROP TABLE	IF EXISTS #__prenotown_resource_attachment;
DROP TABLE	IF EXISTS #__prenotown_resource_group_entries;
DROP TABLE	IF EXISTS #__prenotown_resource_groups;
DROP TABLE	IF EXISTS #__prenotown_resource_components;
DROP TABLE	IF EXISTS #__prenotown_resource_dependencies;

DROP TABLE	IF EXISTS #__prenotown_superbooking_exception;
DROP TABLE	IF EXISTS #__prenotown_superbooking;
DROP TABLE	IF EXISTS #__prenotown_payments;

DROP TABLE	IF EXISTS #__prenotown_resource;
DROP TABLE	IF EXISTS #__prenotown_cost_function;

DROP TABLE	IF EXISTS #__prenotown_user_complement;

DROP TABLE	IF EXISTS #__prenotown_preferences;
