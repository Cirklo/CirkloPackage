DELIMITER //
create function entry_cost_aux(entry_start_date varchar(100), duration int, happyhour_starthour int, happyhour_endhour int) returns int deterministic
	BEGIN
		declare entry_start_minutes, diff1, diff2, discounted_duration int;
		
		select hour(entry_start_date) * 60 + minute(entry_start_date) into entry_start_minutes;
		set diff1 = (happyhour_endhour * 60) - entry_start_minutes;
		set diff2 = entry_start_minutes + duration - (happyhour_starthour * 60);
		set discounted_duration = least(diff1, diff2, duration);
		if discounted_duration > 0 then
			return discounted_duration;
		end if;
		
		return 0;
	END
//
DELIMITER ;


DELIMITER //
create function entry_cost(entrydatetime varchar(100), entryslots int, resourceid int, departmentid int) returns int deterministic
	BEGIN
		declare cost int;
		set cost = 0;
		
		select
			sum(
				entry_cost_aux(
					entrydatetime
					,entryslots * resource_resolution
					,happyhour_starthour
					,happyhour_endhour
				) * happyhour_discount * 0.01 * price_value
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
			and dayofweek(entrydatetime) between happyhour_startday and happyhour_endday;
		
		return cost;
	END
//
DELIMITER ;
