DELIMITER //
create function entry_cost(entry_date date, number_of_slots int, resource_resolution int, price int, happyhour_id int) returns int deterministic
	BEGIN
		declare weekday, hh_start_hour, hh_end_hour, hh_start_day, hh_end_day int;
		
		set weekday = dayofweek(entry_date);
		if 
		
		return 0;
	END
//
DELIMITER ;


DELIMITER //
create function entry_cost(entry_date date, number_of_slots int, resource_resolution int, price int, happyhour_id int) returns int deterministic
	BEGIN
		declare weekday, hh_start_hour, hh_end_hour, hh_start_day, hh_end_day int;
		set weekday = dayofweek(entry_date);
		
		select 
		from 
			happyhour_assoc join happyhour on happyhour_id = happyhour_assoc_happyhour
			join resource on happyhour_assoc_resource = resource_id
		where 
			resource_id = entry_resource
			and weekday between happyhour_startday and happyhour_endday
		
		return 0;
	END
//
DELIMITER ;



DELIMITER //
create function validChangeToHHassoc(newRes int, newHH int) returns int deterministic
	BEGIN
		declare overlapped, startday, endday, starthour, endhour, resStartHour, resEndHour, returnValue INT;
		
		-- check if overlapping
		select
			happyhour_startday, happyhour_endday, happyhour_starthour, happyhour_endhour
		into
			startday, endday, starthour, endhour
		from
			happyhour
		where
			happyhour_id = newHH
		;
		
		set overlapped = overlappingHH(startday, endday, starthour, endhour, newRes);
		if overlapped > 0 then
			return 0;
		end if;
		
		-- check if the new resource "supports" the HH start and end time
		select
			resource_starttime, resource_stoptime
		into
			resStartHour, resEndHour
		from
			resource
		where
			resource_id = newRes
		;
		
		if starthour >= resEndHour || endhour <= resStartHour then
			return 0;
		end if;
		
		return 1;
	END
//
DELIMITER ;