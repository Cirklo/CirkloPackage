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
create function entry_discount(entrydatetime varchar(100), entryslots int, resourceid int, departmentid int, pricevalue int, resourceres int) returns int deterministic
	BEGIN
		declare cost, weekdaynumber int;
		set weekdaynumber := weekday(entrydatetime);
		
		select
			sum(
				happy_hour_duration(
					entrydatetime
					,entryslots * resourceres
					,happyhour_starthour
					,happyhour_endhour
				) * happyhour_discount * 0.01 * pricevalue
			) into cost
		from
			happyhour join happyhour_assoc on happyhour_id = happyhour_assoc_happyhour
		where
			happyhour_assoc_resource = resourceid
			and weekdaynumber between happyhour_startday and happyhour_endday;
				
		return ifnull(cost, 0);
	END
//
DELIMITER ;


DELIMITER //
create function countItems(entryid int, userid int) returns int deterministic
	BEGIN
		declare items int;
		
		select
			count(item_id) into items
		from 
			item_assoc join item on item_id = item_assoc_item
		where
			item_assoc_entry = entryid
			and item_user = userid;
				
		return items;
	END
//
DELIMITER ;

DELIMITER //
create function sequencingDiscount() returns int deterministic
	BEGIN
		declare discount int;
		
		set discount = 10;
		
		return discount;
	END
//
DELIMITER ;

	select 
		department_id,
		department_name,
		user_id,
		fullname,
		resource_id,
		resource_name,
		project_id,
		project_name,
		entry_datetime,
		entry_status,
		price_value,
		units,
		@discount := sequencingDiscount() as discount,
		@subtotal := units * price_value as subtotal,
		@subtotal - @discount as total
		from (
			select
				department_id,
				department_name,
				user_id,
				concat(user_firstname, ' ', user_lastname) as fullname,
				resource_id,
				resource_name,
				project_id,
				ifnull(project_name, 'No project') as project_name,
				entry_datetime,
				entry_status,
				ifnull(price_value, 0) as price_value,
				count(item_id) as units
			from 
				entry join item_assoc on item_assoc_entry = entry_id
				join resource on resource_id = entry_resource
				join item on item_id = item_assoc_item
				join user on user_id = item_user
				join department on department_id = user_dep
				join institute on institute_id = department_inst
				left join project on project_id = item_project
				left join price on (price_resource = resource_id and price_type = institute_pricetype)
			group by
				entry_datetime, user_id
		) as AuxSelect
		where
			entry_status in (1,2)
			
			
			
			
			
		$sql = "
			select
				SQL_CALC_FOUND_ROWS
				department_id,
				department_name,
				user_id,
				@fullname := concat(user_firstname, ' ', user_lastname) as fullname,
				resource_id,
				resource_name,
				project_id,
				ifnull(project_name, 'No project') as project_name,
				entry_datetime,
				@pricevalue := ifnull(price_value, 0) as price_value,
				@units := entry_slots * resource_resolution as units,
				@discount := entry_discount(entry_datetime, entry_slots, entry_resource, user_dep, @pricevalue, resource_resolution) as discount,
				@subtotal := @units * @pricevalue as subtotal,
				@subtotal - @discount as total
			from 
				".dbHelp::getSchemaName().".user join entry on user_id = entry_user
				join department on department_id = user_dep
				join institute on institute_id = department_inst
				join resource on resource_id = entry_resource
				left join price on (price_resource = entry_resource and price_type = institute_pricetype)
				left join project on project_id = entry_project
			where
				entry_status in (1,2)
				".$date_sql."
				".$search_sql."
				".$filter_sql."
			".$order_by_sql."
			".$limit."
		";
		