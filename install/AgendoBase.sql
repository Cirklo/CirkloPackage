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
-- Table structure for table `blacklist`
--

CREATE TABLE IF NOT EXISTS `blacklist` (
  `blacklist_id` int(11) NOT NULL AUTO_INCREMENT,
  `blacklist_user` int(11) NOT NULL,
  PRIMARY KEY (`blacklist_id`),
  UNIQUE KEY `blacklist_user_2` (`blacklist_user`),
  KEY `blacklist_user` (`blacklist_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Table structure for table `board`
--

CREATE TABLE IF NOT EXISTS `board` (
  `board_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table used with monitoring system only',
  `board_address` varchar(20) COLLATE utf8_bin NOT NULL,
  `board_type` varchar(20) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;


INSERT INTO `configParams` (`configParams_name`, `configParams_value`) VALUES
('AgendoVersion', '1.5.6'),
('bookingHour', '9'),
('imapCheck', '0'),
('imapHost', ''),
('imapMailServer', ''),
('showAssiduity', '0');
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
  `entry_project` int(11) DEFAULT NULL,
  PRIMARY KEY (`entry_id`),
  KEY `entry_user` (`entry_user`),
  KEY `entry_resource` (`entry_resource`),
  KEY `entry_repeat` (`entry_repeat`),
  KEY `entry_status` (`entry_status`),
  KEY `entry_project` (`entry_project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

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
-- Table structure for table `happyhour`
--

CREATE TABLE IF NOT EXISTS `happyhour` (
  `happyhour_id` int(11) NOT NULL AUTO_INCREMENT,
  `happyhour_name` varchar(100) NOT NULL,
  `happyhour_discount` tinyint(3) NOT NULL,
  `happyhour_starthour` tinyint(2) NOT NULL,
  `happyhour_endhour` tinyint(2) NOT NULL,
  `happyhour_startday` int(1) NOT NULL,
  `happyhour_endday` int(1) DEFAULT NULL,
  PRIMARY KEY (`happyhour_id`),
  UNIQUE KEY `happyhour_name` (`happyhour_name`),
  KEY `happyhour_startday` (`happyhour_startday`),
  KEY `happyhour_endday` (`happyhour_endday`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `happyhour_assoc` (
  `happyhour_assoc_id` int(11) NOT NULL AUTO_INCREMENT,
  `happyhour_assoc_resource` int(11) NOT NULL,
  `happyhour_assoc_happyhour` int(11) NOT NULL,
  `happyhour_assoc_weekusage` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`happyhour_assoc_id`),
  KEY `happyhour_assoc_happyhour` (`happyhour_assoc_happyhour`),
  KEY `happyhour_assoc_weekusage` (`happyhour_assoc_weekusage`),
  KEY `happyhour_assoc_resource` (`happyhour_assoc_resource`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Table structure for table `item`
--

CREATE TABLE IF NOT EXISTS `item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `item_user` int(11) NOT NULL,
  `item_state` int(11) NOT NULL DEFAULT '0',
  `item_resource` int(11) NOT NULL,
  `item_project` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `item_user` (`item_user`),
  KEY `item_state` (`item_state`),
  KEY `item_resource` (`item_resource`),
  KEY `item_project` (`item_project`)
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Associates a item or items to an entry or entries' AUTO_INCREMENT=199 ;


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


-- --------------------------------------------------------
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
('resourcetype','Resource types',NULL),
('resstatus','Resource status',NULL),
('status','Reservation status',NULL),
('xfieldsinputtype','Field types',NULL),
('xfieldsval','Resource field values',NULL),
('announcement','Announcements', NULL),
('happyhour', 'Happy hour creation', NULL),
('happyhour_assoc', 'Happy hour association', NULL),
('project', 'Project creation', NULL),
('proj_dep_assoc', 'Project association', NULL),
('price', 'Resource prices', NULL),
('blacklist', 'Blacklist users', NULL);	


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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
('Resource fields', 'How to create resource specific fields', 'http://www.youtube.com/watch?v=xdgfzPI_pjQ&feature=player_profilepage'),
('Usage Report', 'Explains how to use the Usage Report feature', 'http://www.youtube.com/embed/GJazBXD7J1E'),
('Assiduity', 'Shows how to activate the feature and what info it displays', 'http://www.youtube.com/embed/xUYXSV9fPxU'),
('Mailing list', 'Shows how to a user can receive mail notifications from a specific resource when entries are updated or deleted', 'http://www.youtube.com/embed/NbfxDicb_wI'),
('Happy hour creation', 'Shows how to create a happy hour', 'http://www.youtube.com/embed/e6zNf4KlH-4'),
('Happy hour association', 'Shows how to associate a resource to a happy hour', 'http://www.youtube.com/embed/wbl2vDif61U'),
('Project creation', 'Shows how to create a project', 'http://www.youtube.com/embed/KAeaHu61jJ8'),
('Project association', 'Shows how to associate a project to a department', 'http://www.youtube.com/embed/c5__HiA6sww'),
('Project on weekview', 'Show how to use projects on the weekview screen', 'http://www.youtube.com/embed/wswWo0UC9Tw'),
('Blacklist users', 'Shows how to blacklist users preventing them from doing anything on agendo', 'http://www.youtube.com/embed/tyTB_CfE4kM');

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

CREATE TABLE IF NOT EXISTS `pending` (
  `pending_id` int(11) NOT NULL AUTO_INCREMENT,
  `pending_code` varchar(100) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`pending_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `permissions_id` int(11) NOT NULL AUTO_INCREMENT,
  `permissions_user` int(11) NOT NULL,
  `permissions_resource` int(11) NOT NULL,
  `permissions_level` tinyint(4) NOT NULL,
  `permissions_training` int(11) DEFAULT '0',
  `permissions_sendmail` int(11) DEFAULT NULL,
  PRIMARY KEY (`permissions_id`),
  UNIQUE KEY `permissions_user_2` (`permissions_user`,`permissions_resource`),
  KEY `permissions_user` (`permissions_user`),
  KEY `permissions_resource` (`permissions_resource`),
  KEY `permissions_level` (`permissions_level`),
  KEY `permissions_ibfk_7` (`permissions_training`),
  KEY `permissions_sendmail` (`permissions_sendmail`)
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
  KEY `price_resource` (`price_resource`),
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
-- Table structure for table `proj_dep_assoc`
--

CREATE TABLE IF NOT EXISTS `proj_dep_assoc` (
  `proj_dep_assoc_id` int(11) NOT NULL AUTO_INCREMENT,
  `proj_dep_assoc_project` int(11) NOT NULL,
  `proj_dep_assoc_department` int(11) NOT NULL,
  `proj_dep_assoc_visible` int(11) NOT NULL DEFAULT '1',
  `proj_dep_assoc_active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`proj_dep_assoc_id`),
  KEY `proj_dep_assoc_project` (`proj_dep_assoc_project`),
  KEY `proj_dep_assoc_department` (`proj_dep_assoc_department`),
  KEY `proj_dep_assoc_active` (`proj_dep_assoc_active`),
  KEY `proj_dep_assoc_visible` (`proj_dep_assoc_visible`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Table structure for table `project`
--

CREATE TABLE IF NOT EXISTS `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(100) NOT NULL,
  `project_account` varchar(100) DEFAULT NULL,
  `project_discount` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `project_account` (`project_account`,`project_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


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
  `resource_mac` varchar(17) COLLATE utf8_bin NOT NULL DEFAULT '0-0-0-0-0-0' COMMENT 'IP address of computer to be used to confirm reservation.',
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

INSERT INTO `resource` (`resource_id`, `resource_name`, `resource_type`, `resource_status`, `resource_maxdays`, `resource_starttime`, `resource_stoptime`, `resource_resp`, `resource_wikilink`, `resource_price`, `resource_resolution`, `resource_maxslots`, `resource_mac`, `resource_confirmtol`, `resource_delhour`, `resource_color`, `resource_maxhoursweek`) VALUES
(1, 'Demo Resource', 1, 1, 7, 7, 22, 1, '', 1, 30, 8, '0-0-0-0-0-0', 0, 0, 5, 0);
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
(5, 'Quick Scheduling'),
(6, 'Sequencing');

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

CREATE TABLE IF NOT EXISTS `weekday` (
  `weekday_id` int(11) NOT NULL,
  `weekday_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`weekday_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `weekday` (`weekday_id`, `weekday_name`) VALUES
(0, 'Monday'),
(1, 'Tuesday'),
(2, 'Wednesday'),
(3, 'Thursday'),
(4, 'Friday'),
(5, 'Saturday'),
(6, 'Sunday');


--
-- Table structure for table `xfields`
--

CREATE TABLE IF NOT EXISTS `xfields` (
  `xfields_id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `xfields_name` varchar(15) COLLATE utf8_bin NOT NULL,
  `xfields_label` varchar(32) COLLATE utf8_bin NOT NULL,
  `xfields_type` int(11) NOT NULL,
  `xfields_resource` int(11) NOT NULL,
  `xfields_placement` int(11) NOT NULL,
  PRIMARY KEY (`xfields_id`),
  UNIQUE KEY `xfields_label` (`xfields_label`),
  KEY `xfields_type` (`xfields_type`),
  KEY `xfields_resource` (`xfields_resource`),
  KEY `xfields_placement` (`xfields_placement`)
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `xfieldsinputtype`
--

INSERT INTO `xfieldsinputtype` (`xfieldsinputtype_id`, `xfieldsinputtype_type`) VALUES
(1, 'TextBox'),
(2, 'CheckBoxSinglePick'),
(3, 'CheckBoxMultiPick'),
(4, 'NumericOnlyInput'),
(5, 'EmptyAllowedText');


--
-- Table structure for table `xfieldsplacement`
--

CREATE TABLE IF NOT EXISTS `xfieldsplacement` (
  `xfieldsplacement_id` int(11) NOT NULL AUTO_INCREMENT,
  `xfieldsplacement_name` varchar(45) NOT NULL,
  PRIMARY KEY (`xfieldsplacement_id`),
  UNIQUE KEY `xfieldsplacement_name` (`xfieldsplacement_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Defines the xfieldsplacement, either on confirmation div or reservation' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `xfieldsplacement`
--

INSERT INTO `xfieldsplacement` (`xfieldsplacement_name`) VALUES 
('Reservation'),
('Confirmation');
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

-- ---------------

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