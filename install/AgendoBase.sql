-- --------------------------------------------------------

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_user`, `admin_table`, `admin_permission`) VALUES
(1, 'xfields', 7),
(1, 'resource', 7),
(1, 'similarresources', 7),
(1, 'permissions', 7),
(1, 'comments',0),
(1, 'invoice',0),
(1, 'fields',0),
(1, 'announcement',0);



-- --------------------------------------------------------

--
-- Table structure for table `allowedips`
--
CREATE TABLE IF NOT EXISTS `allowedips` (
  `allowedips_id` int(11) NOT NULL AUTO_INCREMENT,
  `allowedips_iprange` varchar(45) NOT NULL,
  PRIMARY KEY (`allowedips_id`),
  UNIQUE KEY `allowedips_iprange_UNIQUE` (`allowedips_iprange`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `board`
--

CREATE TABLE IF NOT EXISTS `board` (
  `board_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table used with monitoring system only',
  `board_address` varchar(20) COLLATE utf8_bin NOT NULL,
  `board_type` varchar(20) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;


INSERT INTO `configParams` (`configParams_id`, `configParams_name`, `configParams_value`) VALUES
(12, 'AgendoVersion', '1.5'),
(13, 'bookingHour', '9');

-- --------------------------------------------------------
--
-- Table structure for table `entry`
--

CREATE TABLE IF NOT EXISTS `entry` (
  `entry_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `entry_user` int(11) NOT NULL,
  `entry_datetime` datetime NOT NULL,
  `entry_slots` smallint(6) NOT NULL,
  `entry_assistance` tinyint(4) NOT NULL DEFAULT '0',
  `entry_repeat` int(11) NOT NULL,
  `entry_status` tinyint(4) NOT NULL,
  `entry_resource` int(11) NOT NULL,
  `entry_action` datetime NOT NULL,
  `entry_comments` tinytext COLLATE utf8_bin,
  PRIMARY KEY (`entry_id`),
  KEY `entry_user` (`entry_user`),
  KEY `entry_resource` (`entry_resource`),
  KEY `entry_repeat` (`entry_repeat`),
  KEY `entry_status` (`entry_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1288 ;

-- --------------------------------------------------------

--
-- Table structure for table `equip`
--

CREATE TABLE IF NOT EXISTS `equip` (
  `equip_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table used with monitoring system only',
  `equip_resourceid` int(11) NOT NULL,
  `equip_boardID` int(11) NOT NULL,
  `equip_para` int(11) NOT NULL,
  `equip_min` int(11) NOT NULL,
  `equip_max` int(11) NOT NULL,
  `equip_user` int(11) NOT NULL,
  `equip_desc` varchar(32) COLLATE utf8_bin NOT NULL,
  `equip_alarm_period` int(4) NOT NULL COMMENT 'minute',
  `equip_calibration` varchar(300) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`equip_id`),
  KEY `equip_boardID` (`equip_boardID`),
  KEY `equip_para` (`equip_para`),
  KEY `equip_resourceid` (`equip_resourceid`),
  KEY `equip_user` (`equip_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='monitored equipment' AUTO_INCREMENT=1 ;


--
-- Table structure for table `interfacerooms`
--

CREATE TABLE IF NOT EXISTS `interfacerooms` (
  `interfacerooms_id` int(11) NOT NULL AUTO_INCREMENT,
  `interfacerooms_name` varchar(45) NOT NULL,
  PRIMARY KEY (`interfacerooms_id`),
  UNIQUE KEY `interfacerooms_name_UNIQUE` (`interfacerooms_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Rooms that have resources that use a custom interface' AUTO_INCREMENT=1 ;-- --------------------------------------------------------

--
-- Dumping data for table `mask`
--


INSERT INTO `mask` (`mask_table`, `mask_name`, `mask_pic`) VALUES
('xfields', 'Fields configuration', NULL),
('resource', 'Resources', NULL),
('similarresources', 'Similar resources', NULL),
('permissions', 'Resource permissions', NULL),
('comments','Resource comments', NULL),
('invoice','Invoicing 2011', NULL),
('fields','Entry fields', NULL),
('board','Monitoring boards',NULL),
('entry','Resource sessions',NULL),
('equip','Monitoring fields',NULL),
('measure','Monitoring values',NULL),
('media','Media support',NULL),
('parameter','Monitoring parameters',NULL),
('permlevel','Permissions level',NULL),
('repetition','Auxiliary table',NULL),
('resinterface','Resource interfaces',NULL),
('resourcetype','Resource types',NULL),
('resstatus','Resource status',NULL),
('status','Reservation status',NULL),
('xfieldsinputtype','Field types',NULL),
('xfieldsval','Resource field values',NULL),
('announcement','Announcements', NULL);	


-- --------------------------------------------------------

--
-- Table structure for table `measure`
--

CREATE TABLE IF NOT EXISTS `measure` (
  `measure_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table used with monitoring system only',
  `measure_equip` int(11) NOT NULL,
  `measure_value` int(11) NOT NULL,
  `measure_date` datetime NOT NULL,
  PRIMARY KEY (`measure_id`),
  KEY `measure_equip` (`measure_equip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `measure`
--


-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE IF NOT EXISTS `media` (
  `media_id` int(11) NOT NULL AUTO_INCREMENT,
  `media_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `media_description` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `media_link` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`media_id`),
  UNIQUE KEY `media_name` (`media_name`,`media_description`,`media_link`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`media_name`, `media_description`, `media_link`) VALUES
('Recover Password', 'Shows how to recover a user''s password', 'http://www.youtube.com/embed/Fmo1EbcXPPE'),
('Entry Confirmation', 'Shows how to confirm entries', 'http://www.youtube.com/embed/C-lr4GyOusA'),
('New Permission', 'Shows how to give a user permission for a certain resource', 'http://www.youtube.com/embed/jBTVRI77w1g'),
('Adding an entry', 'Shows how to add entries', 'http://www.youtube.com/embed/MCnDTe8Edc8'),
('Access to specific tables', 'Shows how to give a user access to a table in Datumo', 'http://www.youtube.com/embed/f2U0XILdlVg'),
('Add resource pic', 'Allows the user to add or change the image of a resource', 'http://www.youtube.com/embed/XIDWTNjnQ9w'),
('Give permission', 'Gives a user permission to access a resource', 'http://www.youtube.com/embed/tCkNzMJw-uE'),
('Add, update and delete', 'Allows a user to add, update and delete entries on a table', 'http://www.youtube.com/embed/C4ApJWMJ-QM'),
('Changing Agendo settings ', 'How to change Agendo configuration settings: Institutes name, email settings, etc...', 'http://www.youtube.com/embed/l_perH4VlX8'),
('Resource announcements', 'Shows how to place announcements on a resource', 'http://www.youtube.com/embed/79E64hENOJs'),
('How to create resource fields ', 'Creating specific resource fields with in Agendo', 'http://www.youtube.com/embed/J_z7UIKHHW4'),
('How to change password', 'Changing password in Agendo', 'http://www.youtube.com/embed/6VVsQU-s0iU'),
('How to create a resource', 'Creating a new resource in Agendo resource scheduler', 'http://www.youtube.com/embed/wdOUev8EIPw'),
('Resource fields', 'How to create resource specific fields', 'http://www.youtube.com/watch?v=xdgfzPI_pjQ&feature=player_profilepage');


-- --------------------------------------------------------

--
-- Table structure for table `parameter`
--

CREATE TABLE IF NOT EXISTS `parameter` (
  `parameter_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table used with monitoring system only',
  `parameter_type` varchar(10) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`parameter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `permissions_id` int(11) NOT NULL AUTO_INCREMENT,
  `permissions_user` int(11) NOT NULL,
  `permissions_resource` int(11) NOT NULL,
  `permissions_level` tinyint(4) NOT NULL,
  `permissions_training` int(11) NOT NULL,
  PRIMARY KEY (`permissions_id`),
  KEY `permissions_user` (`permissions_user`),
  KEY `permissions_resource` (`permissions_resource`),
  KEY `permissions_level` (`permissions_level`),
  KEY `permissions_training` (`permissions_training`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `permlevel`
--

CREATE TABLE IF NOT EXISTS `permlevel` (
  `permlevel_id` tinyint(4) NOT NULL,
  `permlevel_desc` varchar(128) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`permlevel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `permlevel`
--

INSERT INTO `permlevel` (`permlevel_id`, `permlevel_desc`) VALUES
(0, 'No permission'),
(1, 'Regular reservation'),
(3, 'Add ahead'),
(5, 'Add Back'),
(7, 'Add Back+Ahead'),
(9, 'Extra reservation');


-- --------------------------------------------------------

--
-- Estrutura da tabela `pics`
--

CREATE TABLE IF NOT EXISTS `pics` (
  `pics_id` int(11) NOT NULL AUTO_INCREMENT,
  `pics_resource` int(11) NOT NULL,
  `pics_path` varchar(30) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`pics_id`),
  KEY `pics_resource` (`pics_resource`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;


--
-- Dumping data for table `pics`
--

INSERT INTO `pics` (`pics_id` ,`pics_resource` ,`pics_path`) VALUES
(NULL , '1', 'resource1.png');

-- --------------------------------------------------------

--
-- Table structure for table `price`
--

CREATE TABLE IF NOT EXISTS `price` (
  `price_id` int(11) NOT NULL AUTO_INCREMENT,
  `price_value` int(11) NOT NULL,
  `price_resource` int(11) DEFAULT NULL,
  `price_type` int(11) DEFAULT NULL,
  PRIMARY KEY (`price_id`),
  KEY `price_equip` (`price_resource`),
  KEY `price_type` (`price_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pricetype`
--

CREATE TABLE IF NOT EXISTS `pricetype` (
  `pricetype_id` int(11) NOT NULL AUTO_INCREMENT,
  `pricetype_name` varchar(100) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`pricetype_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;

--
-- Dumping data for table `pricetype`
--

INSERT INTO `pricetype` (`pricetype_id`, `pricetype_name`) VALUES
(1, 'Internal'),
(2, 'Academic'),
(3, 'Campus'),
(4, 'Comercial');

--
-- Table structure for table `repetition`
--

CREATE TABLE IF NOT EXISTS `repetition` (
  `repetition_id` int(11) NOT NULL AUTO_INCREMENT,
  `repetition_code` varchar(20) COLLATE utf8_bin NOT NULL,
  KEY `repetition_id` (`repetition_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `resinterface`
--

CREATE TABLE IF NOT EXISTS `resinterface` (
  `resinterface_id` int(11) NOT NULL AUTO_INCREMENT,
  `resinterface_resource` int(11) NOT NULL,
  `resinterface_phpfile` varchar(200) COLLATE utf8_bin NOT NULL,
  `resinterface_room` int(11) DEFAULT NULL,
  PRIMARY KEY (`resinterface_id`),
  KEY `resinterface_resource` (`resinterface_resource`),
  KEY `resinterface_room` (`resinterface_room`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Contains custom interfaces for a specific resource' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `resource`
--

CREATE TABLE IF NOT EXISTS `resource` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_name` varchar(32) COLLATE utf8_bin NOT NULL,
  `resource_type` smallint(6) NOT NULL,
  `resource_status` tinyint(4) NOT NULL,
  `resource_maxdays` smallint(6) NOT NULL COMMENT 'In days, sets the maximum number of days a user can reserve ahead.',
  `resource_starttime` smallint(6) NOT NULL COMMENT 'Hour, Starting time of day for reservations.',
  `resource_stoptime` smallint(6) NOT NULL COMMENT 'Hour, Above this time reservations are no longer available.',
  `resource_resp` int(11) NOT NULL,
  `resource_wikilink` varchar(128) COLLATE utf8_bin DEFAULT NULL,
  `resource_price` smallint(6) NOT NULL,
  `resource_resolution` smallint(6) NOT NULL COMMENT 'In minutes, sets the time duration in minutes of  each slot.',
  `resource_maxslots` tinyint(4) NOT NULL COMMENT 'In slots, Maximum time of usage per user per day.',
  `resource_confIP` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '0.0.0.0' COMMENT 'IP address of computer to be used to confirm reservation.',
  `resource_confirmtol` smallint(6) NOT NULL COMMENT 'In slots, Number of time slots of tolerance allowed before and after reservation time to confirm presence or equipment usage.',
  `resource_delhour` int(11) NOT NULL COMMENT 'In hours, minimum time before an entry starts.to delete it.',
  `resource_color` int(11) NOT NULL,
  `resource_maxhoursweek` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`resource_id`),
  KEY `resource_type` (`resource_type`),
  KEY `resource_status` (`resource_status`),
  KEY `resource_id` (`resource_id`),
  KEY `resource_resp` (`resource_resp`),
  KEY `resource_color` (`resource_color`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;

--
-- Dumping data for table `resource`
--

INSERT INTO `resource` (`resource_id`, `resource_name`, `resource_type`, `resource_status`, `resource_maxdays`, `resource_starttime`, `resource_stoptime`, `resource_resp`, `resource_wikilink`, `resource_price`, `resource_resolution`, `resource_maxslots`, `resource_confIP`, `resource_confirmtol`, `resource_delhour`, `resource_color`, `resource_maxhoursweek`) VALUES
(1, 'Demo Resource', 1, 1, 7, 7, 22, 1, '', 1, 30, 8, '0.0.0.0', 0, 0, 5, 0);
-- --------------------------------------------------------

--
-- Table structure for table `resourcetype`
--

CREATE TABLE IF NOT EXISTS `resourcetype` (
  `resourcetype_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `resourcetype_name` varchar(64) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`resourcetype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=11;

--
-- Dumping data for table `resourcetype`
--

INSERT INTO `resourcetype` (`resourcetype_id`, `resourcetype_name`) VALUES
(1, 'Microscopes - Optical Sectioning'),
(2, 'Microscopes - Wide fields'),
(3, 'Flow Cytometry - Cell sorters'),
(4, 'Real Time PCRs'),
(5, 'Flow Cytometry - Analyzers'),
(6, 'Phys-Chem Measurements'),
(7, 'Stereoscopes'),
(8, 'Histology'),
(9, 'Computers'),
(10, 'Environment Control');

-- --------------------------------------------------------

--
-- Table structure for table `resstatus`
--

CREATE TABLE IF NOT EXISTS `resstatus` (
  `resstatus_id` tinyint(4) NOT NULL,
  `resstatus_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`resstatus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `resstatus`
--

INSERT INTO `resstatus` (`resstatus_id`, `resstatus_name`) VALUES
(0, 'Inactive'),
(1, 'Regular reservation'),
(2, 'Invisible'),
(3, 'Pre-reservation with user confirmation'),
(4, 'Pre-reservation with admin confirmation'),
(5, 'Quick Scheduling');

-- --------------------------------------------------------

--
-- Table structure for table `similarresources`
--

CREATE TABLE IF NOT EXISTS `similarresources` (
  `similarresources_id` int(11) NOT NULL AUTO_INCREMENT,
  `similarresources_resource` int(11) NOT NULL,
  `similarresources_similar` int(11) NOT NULL,
  PRIMARY KEY (`similarresources_id`),
  KEY `similarresources_resource` (`similarresources_resource`),
  KEY `similarresources_similar` (`similarresources_similar`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE IF NOT EXISTS `status` (
  `status_id` tinyint(4) NOT NULL,
  `status_name` varchar(16) NOT NULL,
  PRIMARY KEY (`status_id`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_id`, `status_name`) VALUES
(1, 'Regular'),
(2, 'Pre-reserve'),
(3, 'Deleted'),
(4, 'Monitor'),
(5, 'In use');

-- --------------------------------------------------------

--
-- Table structure for table `xfields`
--

CREATE TABLE IF NOT EXISTS `xfields` (
  `xfields_id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `xfields_name` varchar(15) COLLATE utf8_bin NOT NULL,
  `xfields_label` varchar(32) COLLATE utf8_bin NOT NULL,
  `xfields_type` int(11) NOT NULL,
  `xfields_resource` int(11) NOT NULL,
  PRIMARY KEY (`xfields_id`),
  KEY `xfields_type` (`xfields_type`),
  KEY `xfields_resource` (`xfields_resource`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `xfieldsinputtype`
--

CREATE TABLE IF NOT EXISTS `xfieldsinputtype` (
  `xfieldsinputtype_id` int(11) NOT NULL AUTO_INCREMENT,
  `xfieldsinputtype_type` varchar(45) NOT NULL,
  PRIMARY KEY (`xfieldsinputtype_id`),
  UNIQUE KEY `xfieldsinputtype_type_UNIQUE` (`xfieldsinputtype_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `xfieldsinputtype`
--

INSERT INTO `xfieldsinputtype` (`xfieldsinputtype_id`, `xfieldsinputtype_type`) VALUES
(1, 'TextBox'),
(2, 'CheckBoxSinglePick'),
(3, 'CheckBoxMultiPick');

-- --------------------------------------------------------

--
-- Table structure for table `xfieldsval`
--

CREATE TABLE IF NOT EXISTS `xfieldsval` (
  `xfieldsval_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `xfieldsval_entry` bigint(20) NOT NULL,
  `xfieldsval_field` tinyint(4) NOT NULL,
  `xfieldsval_value` varchar(32) NOT NULL,
  PRIMARY KEY (`xfieldsval_id`),
  KEY `xfieldval_entry` (`xfieldsval_entry`,`xfieldsval_field`,`xfieldsval_value`),
  KEY `xfieldval_fieldid` (`xfieldsval_field`),
  KEY `xfieldsval_id` (`xfieldsval_id`),
  KEY `xfieldsval_id_2` (`xfieldsval_id`),
  KEY `xfieldsval_entry` (`xfieldsval_entry`),
  KEY `xfieldvals_field` (`xfieldsval_field`),
  KEY `xfieldsval_field` (`xfieldsval_field`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- views ------------------------------------------------

CREATE VIEW comments AS (select entry_id AS comments_id, user_login AS
comments_user, resource_name AS comments_resource, entry_datetime AS
comments_datetime, entry_comments AS comments_comments from user join
resource join entry where ((entry_resource = resource_id) and (user_id =
entry_user) and (entry_comments<>  '')) order by entry_datetime desc);

CREATE VIEW invoice AS (select entry_id AS invoice_id, user_login AS
invoice_user, department_name AS invoice_department, resource_name AS
invoice_resource, (sum((entry_slots * resource_resolution)) / 60) AS
invoice_hours,((sum((entry_slots * resource_resolution)) / 60) *
resource_price) AS invoice_price from (((entry join user) join resource)
join department) where ((user_dep = department_id) and (entry_resource =
resource_id) and (entry_user = user_id) and (entry_datetime>
'2011-01-01 00:00:00')) group by user_login, resource_name);

CREATE VIEW fields AS (select entry_id AS
fields_id, user_login AS
fields_user, resource_name AS
fields_resource, entry_datetime AS
fields_datetime, xfields_name AS
fields_fields, xfieldsval_value AS
fields_value from entry join user join resource
 join xfields join xfieldsval where ((entry_resource =
resource_id) and (entry_user =
user_id) and
(xfields_resource =
resource_id) and
(xfieldsval_field =
xfields_id) and
(xfieldsval_entry = entry_id)) order by entry_id
desc);