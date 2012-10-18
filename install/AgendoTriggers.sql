-- --------------------------------------------------------
--
-- Dumping data for functions
--
DELIMITER //
create function betweenXandY(myInt int, x int, y int) returns int deterministic
	BEGIN
		IF myInt < x THEN
			set myInt = x;
		END IF;
		IF myInt > y THEN
			set myInt = y;
		END IF;
		return myInt;
	END
//
DELIMITER ;

DELIMITER //
create function overlappingHH(startDay int, endDay int, startHour int, endHour int, resourceId int) returns int deterministic
	BEGIN
		return(
			SELECT
				count(`happyhour_assoc_happyhour`)
			FROM
				`happyhour` inner join `happyhour_assoc` on happyhour_id = happyhour_assoc_happyhour
			WHERE 
				happyhour_assoc_resource = resourceId
				and (
					startHour >= happyhour_starthour
					and startHour < happyhour_endhour
					or 
					endHour <= happyhour_endhour
					and endhour > happyhour_starthour
					or
					startHour <= happyhour_starthour
					and endhour >= happyhour_endhour
				)
				and (
					startDay >= happyhour_startday
					and startDay < happyhour_endday
					or 
					endDay <= happyhour_endday
					and endDay > happyhour_startday
					or
					startDay <= happyhour_startday
					and endDay >= happyhour_endday
				)
		);
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


--
-- Triggers `user`
--

DELIMITER //
CREATE TRIGGER `newuser` AFTER INSERT ON `user`
 FOR EACH ROW BEGIN
IF NEW.user_level=0 THEN
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'admin',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'department',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'institute',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'report',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'mask',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'treeview',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'restree',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'resaccess',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'user',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'resource',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'permissions',5);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'xfields',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'similarresources',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'announcement',7);
END IF;
IF new.user_level=1 THEN
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'user',5);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'department',5);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'institute',5);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'resource',5);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'permissions',5);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'xfields',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'announcement',7);
END IF;
IF new.user_level=2 THEN
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'user',1);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'permissions',0);
INSERT INTO resaccess (resaccess_user, resaccess_table, resaccess_column, resaccess_value) VALUES (new.user_id, 'user', 'user_id', new.user_id);
END IF;
END
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER `userupd` BEFORE UPDATE ON `user`
FOR EACH ROW BEGIN
IF OLD.user_level<>0 THEN
SET NEW.user_level=OLD.user_level;
END IF;
END
//
DELIMITER;

--
-- Triggers `project`
--
DELIMITER //
CREATE TRIGGER `projDiscPercIns` BEFORE INSERT ON `project`
	FOR EACH ROW BEGIN
		SET NEW.project_discount = betweenXandY(NEW.project_discount, 0, 100);
	END
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER `projDiscPercUpd` BEFORE UPDATE ON `project`
	FOR EACH ROW BEGIN
		SET NEW.project_discount = betweenXandY(NEW.project_discount, 0, 100);
	END
//
DELIMITER ;

--
-- Triggers `happyhour`
--
DELIMITER //
CREATE TRIGGER `hhIns` BEFORE INSERT ON `happyhour`
	FOR EACH ROW BEGIN
		SET NEW.happyhour_discount = betweenXandY(NEW.happyhour_discount, 0, 100);
		SET NEW.happyhour_starthour = betweenXandY(NEW.happyhour_starthour, 0, 23);
		SET NEW.happyhour_endhour = betweenXandY(NEW.happyhour_endhour, 1, 24);
		SET NEW.happyhour_startday = betweenXandY(NEW.happyhour_startday, 1, 7);
		
		if NEW.happyhour_endday is null then
			set NEW.happyhour_endday = NEW.happyhour_startday;
		end if;
		SET NEW.happyhour_endday = betweenXandY(NEW.happyhour_endday, 1, 7);
		
		if NEW.happyhour_endhour <= NEW.happyhour_starthour then
			set NEW.happyhour_endhour = null;
		end if;
	END
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER `hhUpd` BEFORE UPDATE ON `happyhour`
	FOR EACH ROW BEGIN
		SET NEW.happyhour_discount = betweenXandY(NEW.happyhour_discount, 0, 100);
		SET NEW.happyhour_starthour = betweenXandY(NEW.happyhour_starthour, 0, 23);
		SET NEW.happyhour_endhour = betweenXandY(NEW.happyhour_endhour, 1, 24);
		SET NEW.happyhour_startday = betweenXandY(NEW.happyhour_startday, 1, 7);
		
		if NEW.happyhour_endday is null then
			set NEW.happyhour_endday = NEW.happyhour_startday;
		end if;
		SET NEW.happyhour_endday = betweenXandY(NEW.happyhour_endday, 1, 7);
		
		if NEW.happyhour_endhour <= NEW.happyhour_starthour then
			set NEW.happyhour_endhour = null;
		end if;
	END
//
DELIMITER ;

--
-- Triggers `happyhour_assoc`
--
DELIMITER //
CREATE TRIGGER `hh_assoc_ins` BEFORE INSERT ON `happyhour_assoc`
	FOR EACH ROW BEGIN
		declare validChange int;
		select validChangeToHHassoc(NEW.happyhour_assoc_resource, NEW.happyhour_assoc_happyhour) into validChange;
		if validChange != 1 then
			set NEW.happyhour_assoc_happyhour = null;
		end if;
	END
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER `hh_assoc_upd` BEFORE UPDATE ON `happyhour_assoc`
	FOR EACH ROW BEGIN
		declare validChange int;
		select validChangeToHHassoc(NEW.happyhour_assoc_resource, NEW.happyhour_assoc_happyhour) into validChange;
		if validChange != 1 then
			set NEW.happyhour_assoc_happyhour = OLD.happyhour_assoc_happyhour;
		end if;
	END
//
DELIMITER ;
