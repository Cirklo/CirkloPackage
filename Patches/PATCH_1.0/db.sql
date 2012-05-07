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
INSERT INTO `databasename`.`xfieldsinputtype` (`xfieldsinputtype_id`, `xfieldsinputtype_type`) VALUES ('5', 'EmptyAllowedText');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Associates a item or items to an entry or entries' AUTO_INCREMENT=199 ;

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

