-- 2011-09-01
-- New Feature: Imap login
INSERT INTO `configparams` (`configParams_name`, `configParams_value`) VALUES
('imapCheck', '0'),
('imapHost', ''),
('imapMailServer', '');
UPDATE configParams SET configParams_value='1.5.2' WHERE configParams_name='AgendoVersion';
--------------


-- 2011-09-05
-- Added an interface for simplified login (usually for tablets)(changed)
DROP TABLE `resinterface`;
DROP TABLE `interfacerooms`;
UPDATE configParams SET configParams_value='1.5.3' WHERE configParams_name='AgendoVersion';
--------------



-- 2011-09-12
-- xfieldstype table added to be able to configure xfields on the confirm menu as well as on reservation
CREATE TABLE IF NOT EXISTS `xfieldsplacement` (
  `xfieldsplacement_id` int(11) NOT NULL AUTO_INCREMENT,
  `xfieldsplacement_name` varchar(45) NOT NULL,
  PRIMARY KEY (`xfieldsplacement_id`),
  UNIQUE KEY `xfieldsplacement_name` (`xfieldsplacement_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Defines the xfieldsplacement, either on confirmation div or reservation' AUTO_INCREMENT=1 ;

INSERT INTO `xfieldsplacement` (`xfieldsplacement_name`) VALUES 
('Reservation'),
('Confirmation');

ALTER TABLE `xfields` ADD COLUMN `xfields_placement` INT NOT NULL  AFTER `xfields_resource`;
UPDATE xfields SET xfields_placement=1 WHERE xfields_placement=0;

ALTER TABLE `xfields` ADD INDEX ( `xfields_placement` );
ALTER TABLE `xfields` ADD FOREIGN KEY ( `xfields_placement` ) REFERENCES `xfieldsplacement` (
`xfieldsplacement_id`);

INSERT INTO `xfieldsinputtype` (`xfieldsinputtype_id`, `xfieldsinputtype_type`) VALUES
(4, 'NumericOnlyInput');

UPDATE configParams SET configParams_value='1.5.4' WHERE configParams_name='AgendoVersion';
--------------



-- 2011-10-11
-- to confirm a resource now you need to put the macaddres instead of the ip on the database, use the makeConfirmRes.php in admin for it
ALTER TABLE `resource` CHANGE `resource_confIP` `resource_mac` VARCHAR( 17 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0-0-0-0-0-0' COMMENT 'Macaddress of computer to be used to confirm reservation.'
--------------


-- 2011-12-12
-- sequencing type of resource
INSERT INTO `resstatus` (`resstatus_id`, `resstatus_name`) VALUES
(6, 'Sequencing');

UPDATE configParams SET configParams_value='1.5.5' WHERE configParams_name='AgendoVersion';
--------------


-- 2012-03-22
-- few changes to allow a financial report and a new type of xfield (an empty input text box)
ALTER TABLE `institute` ADD COLUMN `institute_pricetype` INT NOT NULL DEFAULT 1  AFTER `institute_vat` ;
ALTER TABLE `institute` ADD INDEX ( `institute_pricetype` ) ;
ALTER TABLE `institute` ADD FOREIGN KEY ( `institute_pricetype` ) REFERENCES `pricetype` (`pricetype_id`);
INSERT INTO `level` (`level_id`, `level_name`) VALUES ('3', 'Inactive');
INSERT INTO `xfieldsinputtype` (`xfieldsinputtype_id`, `xfieldsinputtype_type`) VALUES ('5', 'EmptyAllowedText');

UPDATE configParams SET configParams_value='1.5.6' WHERE configParams_name='AgendoVersion';
--------------

-- 2012-05-07
-- new type of resource, used for sequencing for example
INSERT INTO `resstatus` (`resstatus_id`, `resstatus_name`) VALUES
(6, 'Sequencing');


--
-- Table structure for table `item_state`
--

CREATE TABLE IF NOT EXISTS `item_state` (
  `item_state_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_state_name` varchar(45) NOT NULL,
  PRIMARY KEY (`item_state_id`),
  UNIQUE KEY `item_state_name_UNIQUE` (`item_state_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='The several states that a item may have are here' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `item_state`
--

INSERT INTO `item_state` (`item_state_id`, `item_state_name`) VALUES
(1, 'Available'),
(2, 'In use'),
(3, 'Used');


--
-- Table structure for table `item`
--

CREATE TABLE IF NOT EXISTS `item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `item_user` int(11) NOT NULL,
  `item_state` int(11) NOT NULL DEFAULT '0',
  `item_resource` int(11) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `item_user` (`item_user`),
  KEY `item_state` (`item_state`),
  KEY `item_resource` (`item_resource`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `item_assoc`
--

CREATE TABLE IF NOT EXISTS `item_assoc` (
  `item_assoc_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_assoc_entry` bigint(11) NOT NULL,
  `item_assoc_item` int(11) NOT NULL,
  PRIMARY KEY (`item_assoc_id`),
  KEY `item_assoc_item` (`item_assoc_item`),
  KEY `item_assoc_entry` (`item_assoc_entry`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Associates a item or items to an entry or entries' AUTO_INCREMENT=1 ;

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`item_user`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`item_state`) REFERENCES `item_state` (`item_state_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `item_ibfk_3` FOREIGN KEY (`item_resource`) REFERENCES `resource` (`resource_id`) ON UPDATE CASCADE;
  
--
-- Constraints for table `item_assoc`
--
ALTER TABLE `item_assoc`
  ADD CONSTRAINT `item_assoc_ibfk_1` FOREIGN KEY (`item_assoc_item`) REFERENCES `item` (`item_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `item_assoc_ibfk_2` FOREIGN KEY (`item_assoc_entry`) REFERENCES `entry` (`entry_id`) ON UPDATE CASCADE;
  
UPDATE configParams SET configParams_value='1.5.7' WHERE configParams_name='AgendoVersion';
--------------


-- 2012-06-04
-- new type of xfield, doesnt need to be filled
INSERT INTO `xfieldsinputtype` (`xfieldsinputtype_id`, `xfieldsinputtype_type`) VALUES
(5, 'EmptyAllowedText');

-- 2012-08-07
-- added the mailing list feat
ALTER TABLE permissions ADD permissions_sendmail tinyint(1);
-- not being unique was giving problems
ALTER TABLE permissions ADD UNIQUE (permissions_user, permissions_resource);

drop trigger if exists 'userupd';
DELIMITER //
CREATE TRIGGER `userupd` BEFORE UPDATE ON `user`
FOR EACH ROW BEGIN
IF NEW.user_level <> 3 AND OLD.user_level<>0 THEN
SET NEW.user_level=OLD.user_level;
END IF;
END
//
DELIMITER;

-- 2012-10-11
-- changed the report a lot, added triggers for additional stability for the report
-- added the assiduity feature
-- added the possibility to associate accounts/projects to users
-- --------------------------------------------------------
UPDATE configParams SET configParams_value='1.5.6' WHERE configParams_name='AgendoVersion';


--
-- Table structure for table `blacklist`
--

CREATE TABLE IF NOT EXISTS `blacklist` (
  `blacklist_id` int(11) NOT NULL AUTO_INCREMENT,
  `blacklist_user` int(11) NOT NULL,
  PRIMARY KEY (`blacklist_id`),
  UNIQUE KEY `blacklist_user_2` (`blacklist_user`),
  KEY `blacklist_user` (`blacklist_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Table structure for table `happyhour`
--
CREATE TABLE IF NOT EXISTS `happyhour` (
  `happyhour_id` int(11) NOT NULL AUTO_INCREMENT,
  `happyhour_name` varchar(100) NOT NULL,
  `happyhour_discount` tinyint(3) NOT NULL,
  `happyhour_starthour` tinyint(2) NOT NULL,
  `happyhour_endhour` tinyint(2) NOT NULL,
  `happyhour_startday` tinyint(1) NOT NULL,
  `happyhour_endday` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`happyhour_id`),
  UNIQUE KEY `happyhour_name` (`happyhour_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `happyhour_assoc`
--
CREATE TABLE IF NOT EXISTS `happyhour_assoc` (
  `happyhour_assoc_id` int(11) NOT NULL AUTO_INCREMENT,
  `happyhour_assoc_resource` int(11) NOT NULL,
  `happyhour_assoc_happyhour` int(11) NOT NULL,
  `happyhour_assoc_weekusage` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`happyhour_assoc_id`),
  KEY `happyhour_assoc_happyhour` (`happyhour_assoc_happyhour`),
  KEY `happyhour_assoc_weekusage` (`happyhour_assoc_weekusage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `project`
--
CREATE TABLE IF NOT EXISTS `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(100) NOT NULL,
  `project_account` int(11) NOT NULL,
  `project_discount` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `project_account` (`project_account`,`project_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `proj_dep_assoc`
--
CREATE TABLE IF NOT EXISTS `proj_dep_assoc` (
  `proj_dep_assoc_id` int(11) NOT NULL AUTO_INCREMENT,
  `proj_dep_assoc_project` int(11) NOT NULL,
  `proj_dep_assoc_department` int(11) NOT NULL,
  PRIMARY KEY (`proj_dep_assoc_id`),
  KEY `proj_dep_assoc_project` (`proj_dep_assoc_project`),
  KEY `proj_dep_assoc_department` (`proj_dep_assoc_department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `configParams` (`configParams_id`, `configParams_name`, `configParams_value`) VALUES (NULL, 'showAssiduity', '0');

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
				and startDay >= happyhour_startday
				and endDay <= happyhour_endday
				and startHour >= happyhour_starthour
				and endHour <= happyhour_endhour
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


-- --------------------------------------------------------
--
-- Constraints for table `blacklist`
--
ALTER TABLE `blacklist`
  ADD CONSTRAINT `blacklist_ibfk_1` FOREIGN KEY (`blacklist_user`) REFERENCES `user` (`user_id`);
  
--
-- Constraints for table `proj_dep_assoc`
--
ALTER TABLE `proj_dep_assoc`
  ADD CONSTRAINT `proj_dep_assoc_ibfk_1` FOREIGN KEY (`proj_dep_assoc_project`) REFERENCES `project` (`project_id`),
  ADD CONSTRAINT `proj_dep_assoc_ibfk_2` FOREIGN KEY (`proj_dep_assoc_department`) REFERENCES `department` (`department_id`);

--
-- Constraints for table `happyhour_assoc`
--
ALTER TABLE `happyhour_assoc`
  ADD CONSTRAINT `happyhour_assoc_ibfk_2` FOREIGN KEY (`happyhour_assoc_happyhour`) REFERENCES `happyhour` (`happyhour_id`),
  ADD CONSTRAINT `happyhour_assoc_ibfk_3` FOREIGN KEY (`happyhour_assoc_resource`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `happyhour_assoc_ibfk_4` FOREIGN KEY (`happyhour_assoc_weekusage`) REFERENCES `bool` (`bool_id`);
  
ALTER TABLE proj_dep_assoc ADD UNIQUE (proj_dep_assoc_project, proj_dep_assoc_department);
--
-- Constraints for table `entry`
--
ALTER TABLE `entry` ADD `entry_project` INT,
ADD INDEX ( `entry_project` );

ALTER TABLE `entry` ADD FOREIGN KEY ( `entry_project` ) REFERENCES `project` (
`project_id`
);

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions` ADD `permissions_project_default` INT,
ADD INDEX ( `permissions_project_default` );

ALTER TABLE `permissions` ADD FOREIGN KEY ( `permissions_project_default` ) REFERENCES `project` (
`project_id`
);

-- --------------------------------------------------------
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

drop trigger newuser;
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
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'happyhour',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'happyhour_assoc',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'project',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'proj_assoc',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'price',7);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'blacklist',7);
END IF;
IF new.user_level=1 THEN
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'user',5);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'department',5);
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'institute',5);
END IF;
IF new.user_level=2 THEN
INSERT INTO admin (admin_user, admin_table, admin_permission) VALUES (new.user_id,'user',1);
INSERT INTO resaccess (resaccess_user, resaccess_table, resaccess_column, resaccess_value) VALUES (new.user_id, 'user', 'user_id', new.user_id);
END IF;
END
//
DELIMITER ;

drop trigger userupd;
DELIMITER //
CREATE TRIGGER `userupd` BEFORE UPDATE ON `user`
 FOR EACH ROW BEGIN
IF OLD.user_level<>0 THEN
SET NEW.user_level=OLD.user_level;
END IF;
END
//
DELIMITER ;

INSERT INTO `mask` (`mask_id`, `mask_table`, `mask_name`, `mask_pic`) VALUES (NULL, 'happyhour', 'Happy hour creation', NULL);
INSERT INTO `mask` (`mask_id`, `mask_table`, `mask_name`, `mask_pic`) VALUES (NULL, 'happyhour_assoc', 'Happy hour association', NULL);
INSERT INTO `mask` (`mask_id`, `mask_table`, `mask_name`, `mask_pic`) VALUES (NULL, 'project', 'Project creation', NULL);
INSERT INTO `mask` (`mask_id`, `mask_table`, `mask_name`, `mask_pic`) VALUES (NULL, 'proj_dep_assoc', 'Project association', NULL);
INSERT INTO `mask` (`mask_id`, `mask_table`, `mask_name`, `mask_pic`) VALUES (NULL, 'price', 'Price association', NULL);
INSERT INTO `mask` (`mask_id`, `mask_table`, `mask_name`, `mask_pic`) VALUES (NULL, 'blacklist', 'Blacklist users', NULL);

INSERT INTO `configparams` (`configParams_id`, `configParams_name`, `configParams_value`) VALUES
(22, 'showAssiduity', '0');

INSERT INTO `media` (`media_id`, `media_name`, `media_description`, `media_link`) VALUES 
(NULL, 'Assiduity', 'Shows how to activate the feature and what info it displays', 'http://www.youtube.com/embed/xUYXSV9fPxU'),
(NULL, 'Mailing list', 'Shows how to a user can receive mail notifications from a specific resource when entries are updated or deleted', 'http://www.youtube.com/embed/NbfxDicb_wI'),
(NULL, 'Happy hour creation', 'Shows how to create a happy hour', 'http://www.youtube.com/embed/e6zNf4KlH-4'),
(NULL, 'Happy hour association', 'Shows how to associate a resource to a happy hour', 'http://www.youtube.com/embed/wbl2vDif61U'),
(NULL, 'Project creation', 'Shows how to create a project', 'http://www.youtube.com/embed/KAeaHu61jJ8'),
(NULL, 'Project association', 'Shows how to associate a project to a department', 'http://www.youtube.com/embed/c5__HiA6sww'),
(NULL, 'Project on weekview', 'Show how to use projects on the weekview screen', 'http://www.youtube.com/embed/wswWo0UC9Tw'),
(NULL, 'Blacklist users', 'Shows how to blacklist users preventing them from doing anything on agendo', 'http://www.youtube.com/embed/tyTB_CfE4kM');


