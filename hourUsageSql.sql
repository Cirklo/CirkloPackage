-- department default
-- project


DELIMITER //
create function happy_hour_duration(entry_start_date varchar(100), duration int, happyhour_starthour int, happyhour_endhour int) returns int deterministic
	BEGIN
		declare entry_start_minutes, startInterval, endInterval, discounted_duration int;
		
		set entry_start_minutes = hour(entry_start_date) * 60 + minute(entry_start_date);
		
		set startInterval = greatest(entry_start_minutes, (happyhour_starthour * 60));
		set endInterval = least(entry_start_minutes + duration, (happyhour_endhour * 60));
		set discounted_duration = endInterval - startInterval;
		
		if discounted_duration > 0 then
			return discounted_duration;
		end if;
		
		return 0;
	END
//
DELIMITER ;


DELIMITER //
create function entry_discount(entrydatetime varchar(100), entryslots int, resourceid int, departmentid int) returns int deterministic
	BEGIN
		declare cost int;
		
		select
			sum(
				happy_hour_duration(
					entrydatetime
					,entryslots * resource_resolution
					,happyhour_starthour
					,happyhour_endhour
				) * happyhour_discount * 0.01 * ifnull(price_value, 0)
			) into cost
		from
			happyhour join happyhour_assoc on happyhour_id = happyhour_assoc_happyhour
			join resource on happyhour_assoc_resource = resource_id
			join price on price_resource = resource_id
			join institute on price_type = institute_pricetype
			join department on institute_id = department_inst
			
		where
			resource_id = resourceid
			and department_id = departmentid
			and weekday(entrydatetime) between happyhour_startday and happyhour_endday;
				
		return ifnull(cost, 0);
	END
//
DELIMITER ;