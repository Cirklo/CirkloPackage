-- 2011-09-01
-- New Feature: Imap login
INSERT INTO `configparams` (`configParams_name`, `configParams_value`) VALUES
('imapCheck', '0'),
('imapHost', ''),
('imapMailServer', '');
UPDATE configParams SET configParams_value='1.5.2' WHERE configParams_name='AgendoVersion';

-- 2011-09-05
-- Added an interface for simplified login (usually for tablets)(changed)
DROP TABLE `resinterface`;
DROP TABLE `interfacerooms`;
UPDATE configParams SET configParams_value='1.5.3' WHERE configParams_name='AgendoVersion';


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
