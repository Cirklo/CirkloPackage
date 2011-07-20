--
-- Estrutura da tabela `access`
--

CREATE TABLE IF NOT EXISTS `access` (
  `access_id` int(11) NOT NULL,
  `access_permission` varchar(50) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`access_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Extraindo dados da tabela `access`
--

INSERT INTO `access` (`access_id`, `access_permission`) VALUES
(0, 'View'),
(1, 'Update'),
(2, 'Delete'),
(3, 'Update, Delete'),
(4, 'Add'),
(5, 'Add, Update'),
(6, 'Add, Delete'),
(7, 'Add, Update, Delete');

-- --------------------------------------------------------

--
-- Estrutura da tabela `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user` int(11) NOT NULL,
  `admin_table` varchar(20) CHARACTER SET latin1 NOT NULL,
  `admin_permission` int(11) NOT NULL,
  PRIMARY KEY (`admin_id`),
  KEY `admin_user` (`admin_user`),
  KEY `admin_permission` (`admin_permission`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='With this table you can give access to specific tables' AUTO_INCREMENT=1 ;

--
-- Extraindo dados da tabela `admin`
--

INSERT INTO `admin` (`admin_user`, `admin_table`, `admin_permission`) VALUES
(1, 'user', 5),
(1, 'admin', 7),
(1, 'department', 5),
(1, 'institute',5),
(1, 'report', 7),
(1, 'mask', 7),
(1, 'treeview', 7),
(1, 'restree', 7),
(1, 'resaccess', 7);

-- --------------------------------------------------------

--
-- Estrutura da tabela `alert`
--

CREATE TABLE IF NOT EXISTS `alert` (
  `alert_id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_name` varchar(45) NOT NULL,
  PRIMARY KEY (`alert_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Extraindo dados da tabela `alert`
--

INSERT INTO `alert` (`alert_id`, `alert_name`) VALUES
(1, 'alert by email'),
(2, 'alert by sms');

-- --------------------------------------------------------

--
-- Estrutura da tabela `announcement`
--

CREATE TABLE IF NOT EXISTS `announcement` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
  `announcement_title` varchar(50) COLLATE utf8_bin NOT NULL,
  `announcement_message` text COLLATE utf8_bin DEFAULT NULL,
  `announcement_object` int(11) NOT NULL COMMENT 'Main object of the announcement.',
  `announcement_date` date NOT NULL COMMENT 'Announcement date.',
  `announcement_end_date` date DEFAULT NULL COMMENT 'The announcement will no longer be available after the end date.',
  PRIMARY KEY (`announcement_id`),
  KEY `announcement_object` (`announcement_object`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `bool`
--

CREATE TABLE IF NOT EXISTS `bool` (
  `bool_id` int(11) NOT NULL,
  `bool_type` varchar(6) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`bool_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Extraindo dados da tabela `bool`
--

INSERT INTO `bool` (`bool_id`, `bool_type`) VALUES
(0, 'FALSE'),
(1, 'TRUE');


-- --------------------------------------------------------

--
-- Table structure for table `color`
--

CREATE TABLE IF NOT EXISTS `color` (
  `color_id` int(11) NOT NULL,
  `color_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `color_code` varchar(6) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `color`
--

INSERT INTO `color` (`color_id`, `color_name`, `color_code`) VALUES
(0, 'indian red', 'B0171F'),
(1, 'crimson', 'DC143C'),
(2, 'lightpink', 'FFB6C1'),
(3, 'lightpink 1', 'FFAEB9'),
(4, 'lightpink 2', 'EEA2AD'),
(5, 'lightpink 3', 'CD8C95'),
(6, 'lightpink 4', '8B5F65'),
(7, 'pink', 'FFC0CB'),
(8, 'pink 1', 'FFB5C5'),
(9, 'pink 2', 'EEA9B8'),
(10, 'pink 3', 'CD919E'),
(11, 'pink 4', '8B636C'),
(12, 'palevioletred', 'DB7093'),
(13, 'palevioletred 1', 'FF82AB'),
(14, 'palevioletred 2', 'EE799F'),
(15, 'palevioletred 3', 'CD6889'),
(16, 'palevioletred 4', '8B475D'),
(17, 'lavenderblush 1 (lavenderblush)', 'FFF0F5'),
(18, 'lavenderblush 2', 'EEE0E5'),
(19, 'lavenderblush 3', 'CDC1C5'),
(20, 'lavenderblush 4', '8B8386'),
(21, 'violetred 1', 'FF3E96'),
(22, 'violetred 2', 'EE3A8C'),
(23, 'violetred 3', 'CD3278'),
(24, 'violetred 4', '8B2252'),
(25, 'hotpink', 'FF69B4'),
(26, 'hotpink 1', 'FF6EB4'),
(27, 'hotpink 2', 'EE6AA7'),
(28, 'hotpink 3', 'CD6090'),
(29, 'hotpink 4', '8B3A62'),
(30, 'raspberry', '872657'),
(31, 'deeppink 1 (deeppink)', 'FF1493'),
(32, 'deeppink 2', 'EE1289'),
(33, 'deeppink 3', 'CD1076'),
(34, 'deeppink 4', '8B0A50'),
(35, 'maroon 1', 'FF34B3'),
(36, 'maroon 2', 'EE30A7'),
(37, 'maroon 3', 'CD2990'),
(38, 'maroon 4', '8B1C62'),
(39, 'mediumvioletred', 'C71585'),
(40, 'violetred', 'D02090'),
(41, 'orchid', 'DA70D6'),
(42, 'orchid 1', 'FF83FA'),
(43, 'orchid 2', 'EE7AE9'),
(44, 'orchid 3', 'CD69C9'),
(45, 'orchid 4', '8B4789'),
(46, 'thistle', 'D8BFD8'),
(47, 'thistle 1', 'FFE1FF'),
(48, 'thistle 2', 'EED2EE'),
(49, 'thistle 3', 'CDB5CD'),
(50, 'thistle 4', '8B7B8B'),
(51, 'plum 1', 'FFBBFF'),
(52, 'plum 2', 'EEAEEE'),
(53, 'plum 3', 'CD96CD'),
(54, 'plum 4', '8B668B'),
(55, 'plum', 'DDA0DD'),
(56, 'violet', 'EE82EE'),
(57, 'magenta (fuchsia*)', 'FF00FF'),
(58, 'magenta 2', 'EE00EE'),
(59, 'magenta 3', 'CD00CD'),
(60, 'magenta 4 (darkmagenta)', '8B008B'),
(61, 'purple*', '800080'),
(62, 'mediumorchid', 'BA55D3'),
(63, 'mediumorchid 1', 'E066FF'),
(64, 'mediumorchid 2', 'D15FEE'),
(65, 'mediumorchid 3', 'B452CD'),
(66, 'mediumorchid 4', '7A378B'),
(67, 'darkviolet', '9400D3'),
(68, 'darkorchid', '9932CC'),
(69, 'darkorchid 1', 'BF3EFF'),
(70, 'darkorchid 2', 'B23AEE'),
(71, 'darkorchid 3', '9A32CD'),
(72, 'darkorchid 4', '68228B'),
(73, 'indigo', '4B0082'),
(74, 'blueviolet', '8A2BE2'),
(75, 'purple 1', '9B30FF'),
(76, 'purple 2', '912CEE'),
(77, 'purple 3', '7D26CD'),
(78, 'purple 4', '551A8B'),
(79, 'mediumpurple', '9370DB'),
(80, 'mediumpurple 1', 'AB82FF'),
(81, 'mediumpurple 2', '9F79EE'),
(82, 'mediumpurple 3', '8968CD'),
(83, 'mediumpurple 4', '5D478B'),
(84, 'darkslateblue', '483D8B'),
(85, 'lightslateblue', '8470FF'),
(86, 'mediumslateblue', '7B68EE'),
(87, 'slateblue', '6A5ACD'),
(88, 'slateblue 1', '836FFF'),
(89, 'slateblue 2', '7A67EE'),
(90, 'slateblue 3', '6959CD'),
(91, 'slateblue 4', '473C8B'),
(92, 'ghostwhite', 'F8F8FF'),
(93, 'lavender', 'E6E6FA'),
(94, 'blue*', '0000FF'),
(95, 'blue 2', '0000EE'),
(96, 'blue 3 (mediumblue)', '0000CD'),
(97, 'blue 4 (darkblue)', '00008B'),
(98, 'navy*', '80'),
(99, 'midnightblue', '191970'),
(100, 'cobalt', '3D59AB'),
(101, 'royalblue', '41690'),
(102, 'royalblue 1', '4876FF'),
(103, 'royalblue 2', '436EEE'),
(104, 'royalblue 3', '3A5FCD'),
(105, 'royalblue 4', '27408B'),
(106, 'cornflowerblue', '6495ED'),
(107, 'lightsteelblue', 'B0C4DE'),
(108, 'lightsteelblue 1', 'CAE1FF'),
(109, 'lightsteelblue 2', 'BCD2EE'),
(110, 'lightsteelblue 3', 'A2B5CD'),
(111, 'lightsteelblue 4', '6E7B8B'),
(112, 'lightslategray', '778899'),
(113, 'slategray', '708090'),
(114, 'slategray 1', 'C6E2FF'),
(115, 'slategray 2', 'B9D3EE'),
(116, 'slategray 3', '9FB6CD'),
(117, 'slategray 4', '6C7B8B'),
(118, 'dodgerblue 1 (dodgerblue)', '1E90FF'),
(119, 'dodgerblue 2', '1C86EE'),
(120, 'dodgerblue 3', '1874CD'),
(121, 'dodgerblue 4', '104E8B'),
(122, 'aliceblue', 'F0F8FF'),
(123, 'steelblue', '4682B4'),
(124, 'steelblue 1', '63B8FF'),
(125, 'steelblue 2', '5CACEE'),
(126, 'steelblue 3', '4F94CD'),
(127, 'steelblue 4', '36648B'),
(128, 'lightskyblue', '87CEFA'),
(129, 'lightskyblue 1', 'B0E2FF'),
(130, 'lightskyblue 2', 'A4D3EE'),
(131, 'lightskyblue 3', '8DB6CD'),
(132, 'lightskyblue 4', '607B8B'),
(133, 'skyblue 1', '87CEFF'),
(134, 'skyblue 2', '7EC0EE'),
(135, 'skyblue 3', '6CA6CD'),
(136, 'skyblue 4', '4A708B'),
(137, 'skyblue', '87CEEB'),
(138, 'deepskyblue 1 (deepskyblue)', '00BFFF'),
(139, 'deepskyblue 2', '00B2EE'),
(140, 'deepskyblue 3', '009ACD'),
(141, 'deepskyblue 4', '00688B'),
(142, 'peacock', '33A1C9'),
(143, 'lightblue', 'ADD8E6'),
(144, 'lightblue 1', 'BFEFFF'),
(145, 'lightblue 2', 'B2DFEE'),
(146, 'lightblue 3', '9AC0CD'),
(147, 'lightblue 4', '68838B'),
(148, 'powderblue', 'B0E0E6'),
(149, 'cadetblue 1', '98F5FF'),
(150, 'cadetblue 2', '8EE5EE'),
(151, 'cadetblue 3', '7AC5CD'),
(152, 'cadetblue 4', '53868B'),
(153, 'turquoise 1', '00F5FF'),
(154, 'turquoise 2', '00E5EE'),
(155, 'turquoise 3', '00C5CD'),
(156, 'turquoise 4', '00868B'),
(157, 'cadetblue', '5F9EA0'),
(158, 'darkturquoise', '00CED1'),
(159, 'azure 1 (azure)', 'F0FFFF'),
(160, 'azure 2', 'E0EEEE'),
(161, 'azure 3', 'C1CDCD'),
(162, 'azure 4', '838B8B'),
(163, 'lightcyan 1 (lightcyan)', 'E0FFFF'),
(164, 'lightcyan 2', 'D1EEEE'),
(165, 'lightcyan 3', 'B4CDCD'),
(166, 'lightcyan 4', '7A8B8B'),
(167, 'paleturquoise 1', 'BBFFFF'),
(168, 'paleturquoise 2 (paleturquoise)', 'AEEEEE'),
(169, 'paleturquoise 3', '96CDCD'),
(170, 'paleturquoise 4', '668B8B'),
(171, 'darkslategray', '2F4F4F'),
(172, 'darkslategray 1', '97FFFF'),
(173, 'darkslategray 2', '8DEEEE'),
(174, 'darkslategray 3', '79CDCD'),
(175, 'darkslategray 4', '528B8B'),
(176, 'cyan / aqua*', '00FFFF'),
(177, 'cyan 2', '00EEEE'),
(178, 'cyan 3', '00CDCD'),
(179, 'cyan 4 (darkcyan)', '008B8B'),
(180, 'teal*', '8080'),
(181, 'mediumturquoise', '48D1CC'),
(182, 'lightseagreen', '20B2AA'),
(183, 'manganeseblue', '03A89E'),
(184, 'turquoise', '40E0D0'),
(185, 'coldgrey', '808A87'),
(186, 'turquoiseblue', '00C78C'),
(187, 'aquamarine 1 (aquamarine)', '7FFFD4'),
(188, 'aquamarine 2', '76EEC6'),
(189, 'aquamarine 3 (mediumaquamarine)', '66CDAA'),
(190, 'aquamarine 4', '458B74'),
(191, 'mediumspringgreen', '00FA9A'),
(192, 'mintcream', 'F5FFFA'),
(193, 'springgreen', '00FF7F'),
(194, 'springgreen 1', '00EE76'),
(195, 'springgreen 2', '00CD66'),
(196, 'springgreen 3', '008B45'),
(197, 'mediumseagreen', '3CB371'),
(198, 'seagreen 1', '54FF9F'),
(199, 'seagreen 2', '4EEE94'),
(200, 'seagreen 3', '43CD80'),
(201, 'seagreen 4 (seagreen)', '2E8B57'),
(202, 'emeraldgreen', '00C957'),
(203, 'mint', 'BDFCC9'),
(204, 'cobaltgreen', '3D9140'),
(205, 'honeydew 1 (honeydew)', 'F0FFF0'),
(206, 'honeydew 2', 'E0EEE0'),
(207, 'honeydew 3', 'C1CDC1'),
(208, 'honeydew 4', '838B83'),
(209, 'darkseagreen', '8FBC8F'),
(210, 'darkseagreen 1', 'C1FFC1'),
(211, 'darkseagreen 2', 'B4EEB4'),
(212, 'darkseagreen 3', '9BCD9B'),
(213, 'darkseagreen 4', '698B69'),
(214, 'palegreen', '98FB98'),
(215, 'palegreen 1', '9AFF9A'),
(216, 'palegreen 2 (lightgreen)', '90EE90'),
(217, 'palegreen 3', '7CCD7C'),
(218, 'palegreen 4', '548B54'),
(219, 'limegreen', '32CD32'),
(220, 'forestgreen', '228B22'),
(221, 'green 1 (lime*)', '00FF00'),
(222, 'green 2', '00EE00'),
(223, 'green 3', '00CD00'),
(224, 'green 4', '008B00'),
(225, 'green*', '8000'),
(226, 'darkgreen', '6400'),
(227, 'sapgreen', '308014'),
(228, 'lawngreen', '7CFC00'),
(229, 'chartreuse 1 (chartreuse)', '7FFF00'),
(230, 'chartreuse 2', '76EE00'),
(231, 'chartreuse 3', '66CD00'),
(232, 'chartreuse 4', '458B00'),
(233, 'greenyellow', 'ADFF2F'),
(234, 'darkolivegreen 1', 'CAFF70'),
(235, 'darkolivegreen 2', 'BCEE68'),
(236, 'darkolivegreen 3', 'A2CD5A'),
(237, 'darkolivegreen 4', '6E8B3D'),
(238, 'darkolivegreen', '556B2F'),
(239, 'olivedrab', '6B8E23'),
(240, 'olivedrab 1', 'C0FF3E'),
(241, 'olivedrab 2', 'B3EE3A'),
(242, 'olivedrab 3 (yellowgreen)', '9ACD32'),
(243, 'olivedrab 4', '698B22'),
(244, 'ivory 1 (ivory)', 'FFFFF0'),
(245, 'ivory 2', 'EEEEE0'),
(246, 'ivory 3', 'CDCDC1'),
(247, 'ivory 4', '8B8B83'),
(248, 'beige', 'F5F5DC'),
(249, 'lightyellow 1 (lightyellow)', 'FFFFE0'),
(250, 'lightyellow 2', 'EEEED1'),
(251, 'lightyellow 3', 'CDCDB4'),
(252, 'lightyellow 4', '8B8B7A'),
(253, 'lightgoldenrodyellow', 'FAFAD2'),
(254, 'yellow 1 (yellow*)', 'FFFF00'),
(255, 'yellow 2', 'EEEE00'),
(256, 'yellow 3', 'CDCD00'),
(257, 'yellow 4', '8B8B00'),
(258, 'warmgrey', '808069'),
(259, 'olive*', '808000'),
(260, 'darkkhaki', 'BDB76B'),
(261, 'khaki 1', 'FFF68F'),
(262, 'khaki 2', 'EEE685'),
(263, 'khaki 3', 'CDC673'),
(264, 'khaki 4', '8B864E'),
(265, 'khaki', 'F0E68C'),
(266, 'palegoldenrod', 'EEE8AA'),
(267, 'lemonchiffon 1 (lemonchiffon)', 'FFFACD'),
(268, 'lemonchiffon 2', 'EEE9BF'),
(269, 'lemonchiffon 3', 'CDC9A5'),
(270, 'lemonchiffon 4', '8B8970'),
(271, 'lightgoldenrod 1', 'FFEC8B'),
(272, 'lightgoldenrod 2', 'EEDC82'),
(273, 'lightgoldenrod 3', 'CDBE70'),
(274, 'lightgoldenrod 4', '8B814C'),
(275, 'banana', 'E3CF57'),
(276, 'gold 1 (gold)', 'FFD700'),
(277, 'gold 2', 'EEC900'),
(278, 'gold 3', 'CDAD00'),
(279, 'gold 4', '8B7500'),
(280, 'cornsilk 1 (cornsilk)', 'FFF8DC'),
(281, 'cornsilk 2', 'EEE8CD'),
(282, 'cornsilk 3', 'CDC8B1'),
(283, 'cornsilk 4', '8B8878'),
(284, 'goldenrod', 'DAA520'),
(285, 'goldenrod 1', 'FFC125'),
(286, 'goldenrod 2', 'EEB422'),
(287, 'goldenrod 3', 'CD9B1D'),
(288, 'goldenrod 4', '8B6914'),
(289, 'darkgoldenrod', 'B8860B'),
(290, 'darkgoldenrod 1', 'FFB90F'),
(291, 'darkgoldenrod 2', 'EEAD0E'),
(292, 'darkgoldenrod 3', 'CD950C'),
(293, 'darkgoldenrod 4', '8B6508'),
(294, 'orange 1 (orange)', 'FFA500'),
(295, 'orange 2', 'EE9A00'),
(296, 'orange 3', 'CD8500'),
(297, 'orange 4', '8B5A00'),
(298, 'floralwhite', 'FFFAF0'),
(299, 'oldlace', 'FDF5E6'),
(300, 'wheat', 'F5DEB3'),
(301, 'wheat 1', 'FFE7BA'),
(302, 'wheat 2', 'EED8AE'),
(303, 'wheat 3', 'CDBA96'),
(304, 'wheat 4', '8B7E66'),
(305, 'moccasin', 'FFE4B5'),
(306, 'papayawhip', 'FFEFD5'),
(307, 'blanchedalmond', 'FFEBCD'),
(308, 'navajowhite 1 (navajowhite)', 'FFDEAD'),
(309, 'navajowhite 2', 'EECFA1'),
(310, 'navajowhite 3', 'CDB38B'),
(311, 'navajowhite 4', '8B795E'),
(312, 'eggshell', 'FCE6C9'),
(313, 'tan', 'D2B48C'),
(314, 'brick', '9C661F'),
(315, 'cadmiumyellow', 'FF9912'),
(316, 'antiquewhite', 'FAEBD7'),
(317, 'antiquewhite 1', 'FFEFDB'),
(318, 'antiquewhite 2', 'EEDFCC'),
(319, 'antiquewhite 3', 'CDC0B0'),
(320, 'antiquewhite 4', '8B8378'),
(321, 'burlywood', 'DEB887'),
(322, 'burlywood 1', 'FFD39B'),
(323, 'burlywood 2', 'EEC591'),
(324, 'burlywood 3', 'CDAA7D'),
(325, 'burlywood 4', '8B7355'),
(326, 'bisque 1 (bisque)', 'FFE4C4'),
(327, 'bisque 2', 'EED5B7'),
(328, 'bisque 3', 'CDB79E'),
(329, 'bisque 4', '8B7D6B'),
(330, 'melon', 'E3A869'),
(331, 'carrot', 'ED9121'),
(332, 'darkorange', 'FF8C00'),
(333, 'darkorange 1', 'FF7F00'),
(334, 'darkorange 2', 'EE7600'),
(335, 'darkorange 3', 'CD6600'),
(336, 'darkorange 4', '8B4500'),
(337, 'orange', 'FF8000'),
(338, 'tan 1', 'FFA54F'),
(339, 'tan 2', 'EE9A49'),
(340, 'tan 3 (peru)', 'CD853F'),
(341, 'tan 4', '8B5A2B'),
(342, 'linen', 'FAF0E6'),
(343, 'peachpuff 1 (peachpuff)', 'FFDAB9'),
(344, 'peachpuff 2', 'EECBAD'),
(345, 'peachpuff 3', 'CDAF95'),
(346, 'peachpuff 4', '8B7765'),
(347, 'seashell 1 (seashell)', 'FFF5EE'),
(348, 'seashell 2', 'EEE5DE'),
(349, 'seashell 3', 'CDC5BF'),
(350, 'seashell 4', '8B8682'),
(351, 'sandybrown', 'F4A460'),
(352, 'rawsienna', 'C76114'),
(353, 'chocolate', 'D2691E'),
(354, 'chocolate 1', 'FF7F24'),
(355, 'chocolate 2', 'EE7621'),
(356, 'chocolate 3', 'CD661D'),
(357, 'chocolate 4 (saddlebrown)', '8B4513'),
(358, 'ivoryblack', '292421'),
(359, 'flesh', 'FF7D40'),
(360, 'cadmiumorange', 'FF6103'),
(361, 'burntsienna', '8A360F'),
(362, 'sienna', 'A0522D'),
(363, 'sienna 1', 'FF8247'),
(364, 'sienna 2', 'EE7942'),
(365, 'sienna 3', 'CD6839'),
(366, 'sienna 4', '8B4726'),
(367, 'lightsalmon 1 (lightsalmon)', 'FFA07A'),
(368, 'lightsalmon 2', 'EE9572'),
(369, 'lightsalmon 3', 'CD8162'),
(370, 'lightsalmon 4', '8B5742'),
(371, 'coral', 'FF7F50'),
(372, 'orangered 1 (orangered)', 'FF4500'),
(373, 'orangered 2', 'EE4000'),
(374, 'orangered 3', 'CD3700'),
(375, 'orangered 4', '8B2500'),
(376, 'sepia', '5E2612'),
(377, 'darksalmon', 'E9967A'),
(378, 'salmon 1', 'FF8C69'),
(379, 'salmon 2', 'EE8262'),
(380, 'salmon 3', 'CD7054'),
(381, 'salmon 4', '8B4C39'),
(382, 'coral 1', 'FF7256'),
(383, 'coral 2', 'EE6A50'),
(384, 'coral 3', 'CD5B45'),
(385, 'coral 4', '8B3E2F'),
(386, 'burntumber', '8A3324'),
(387, 'tomato 1 (tomato)', 'FF6347'),
(388, 'tomato 2', 'EE5C42'),
(389, 'tomato 3', 'CD4F39'),
(390, 'tomato 4', '8B3626'),
(391, 'salmon', 'FA8072'),
(392, 'mistyrose 1 (mistyrose)', 'FFE4E1'),
(393, 'mistyrose 2', 'EED5D2'),
(394, 'mistyrose 3', 'CDB7B5'),
(395, 'mistyrose 4', '8B7D7B'),
(396, 'snow 1 (snow)', 'FFFAFA'),
(397, 'snow 2', 'EEE9E9'),
(398, 'snow 3', 'CDC9C9'),
(399, 'snow 4', '8B8989'),
(400, 'rosybrown', 'BC8F8F'),
(401, 'rosybrown 1', 'FFC1C1'),
(402, 'rosybrown 2', 'EEB4B4'),
(403, 'rosybrown 3', 'CD9B9B'),
(404, 'rosybrown 4', '8B6969'),
(405, 'lightcoral', 'F08080'),
(406, 'indianred', 'CD5C5C'),
(407, 'indianred 1', 'FF6A6A'),
(408, 'indianred 2', 'EE6363'),
(409, 'indianred 4', '8B3A3A'),
(410, 'indianred 3', 'CD5555'),
(411, 'brown', 'A52A2A'),
(412, 'brown 1', 'FF4040'),
(413, 'brown 2', 'EE3B3B'),
(414, 'brown 3', 'CD3333'),
(415, 'brown 4', '8B2323'),
(416, 'firebrick', 'B22222'),
(417, 'firebrick 1', 'FF3030'),
(418, 'firebrick 2', 'EE2C2C'),
(419, 'firebrick 3', 'CD2626'),
(420, 'firebrick 4', '8B1A1A'),
(421, 'red 1 (red*)', 'FF0000'),
(422, 'red 2', 'EE0000'),
(423, 'red 3', 'CD0000'),
(424, 'red 4 (darkred)', '8B0000'),
(425, 'maroon*', '800000'),
(426, 'sgi beet', '8E388E'),
(427, 'sgi slateblue', '7171C6'),
(428, 'sgi lightblue', '7D9EC0'),
(429, 'sgi teal', '388E8E'),
(430, 'sgi chartreuse', '71C671'),
(431, 'sgi olivedrab', '8E8E38'),
(432, 'sgi brightgray', 'C5C1AA'),
(433, 'sgi salmon', 'C67171'),
(434, 'sgi darkgray', '555555'),
(435, 'sgi gray 12', '1E1E1E'),
(436, 'sgi gray 16', '282828'),
(437, 'sgi gray 32', '515151'),
(438, 'sgi gray 36', '5B5B5B'),
(439, 'sgi gray 52', '848484'),
(440, 'sgi gray 56', '8E8E8E'),
(441, 'sgi lightgray', 'AAAAAA'),
(442, 'sgi gray 72', 'B7B7B7'),
(443, 'sgi gray 76', 'C1C1C1'),
(444, 'sgi gray 92', 'EAEAEA'),
(445, 'sgi gray 96', 'F4F4F4'),
(446, 'white*', 'FFFFFF'),
(447, 'white smoke (gray 96)', 'F5F5F5'),
(448, 'gainsboro', 'DCDCDC'),
(449, 'lightgrey', 'D3D3D3'),
(450, 'silver*', 'C0C0C0'),
(451, 'darkgray', 'A9A9A9'),
(452, 'gray*', '808080'),
(453, 'dimgray (gray 42)', '696969'),
(454, 'black*', '0'),
(455, 'gray 99', 'FCFCFC'),
(456, 'gray 98', 'FAFAFA'),
(457, 'gray 97', 'F7F7F7'),
(458, 'white smoke (gray 96)', 'F5F5F5'),
(459, 'gray 95', 'F2F2F2'),
(460, 'gray 94', 'F0F0F0'),
(461, 'gray 93', 'EDEDED'),
(462, 'gray 92', 'EBEBEB'),
(463, 'gray 91', 'E8E8E8'),
(464, 'gray 90', 'E5E5E5'),
(465, 'gray 89', 'E3E3E3'),
(466, 'gray 88', 'E0E0E0'),
(467, 'gray 87', 'DEDEDE'),
(468, 'gray 86', 'DBDBDB'),
(469, 'gray 85', 'D9D9D9'),
(470, 'gray 84', 'D6D6D6'),
(471, 'gray 83', 'D4D4D4'),
(472, 'gray 82', 'D1D1D1'),
(473, 'gray 81', 'CFCFCF'),
(474, 'gray 80', 'CCCCCC'),
(475, 'gray 79', 'C9C9C9'),
(476, 'gray 78', 'C7C7C7'),
(477, 'gray 77', 'C4C4C4'),
(478, 'gray 76', 'C2C2C2'),
(479, 'gray 75', 'BFBFBF'),
(480, 'gray 74', 'BDBDBD'),
(481, 'gray 73', 'BABABA'),
(482, 'gray 72', 'B8B8B8'),
(483, 'gray 71', 'B5B5B5'),
(484, 'gray 70', 'B3B3B3'),
(485, 'gray 69', 'B0B0B0'),
(486, 'gray 68', 'ADADAD'),
(487, 'gray 67', 'ABABAB'),
(488, 'gray 66', 'A8A8A8'),
(489, 'gray 65', 'A6A6A6'),
(490, 'gray 64', 'A3A3A3'),
(491, 'gray 63', 'A1A1A1'),
(492, 'gray 62', '9E9E9E'),
(493, 'gray 61', '9C9C9C'),
(494, 'gray 60', '999999'),
(495, 'gray 59', '969696'),
(496, 'gray 58', '949494'),
(497, 'gray 57', '919191'),
(498, 'gray 56', '8F8F8F'),
(499, 'gray 55', '8C8C8C'),
(500, 'gray 54', '8A8A8A'),
(501, 'gray 53', '878787'),
(502, 'gray 52', '858585'),
(503, 'gray 51', '828282'),
(504, 'gray 50', '7F7F7F'),
(505, 'gray 49', '7D7D7D'),
(506, 'gray 48', '7A7A7A'),
(507, 'gray 47', '787878'),
(508, 'gray 46', '757575'),
(509, 'gray 45', '737373'),
(510, 'gray 44', '707070'),
(511, 'gray 43', '6E6E6E'),
(512, 'gray 42', '6B6B6B'),
(513, 'dimgray (gray 42)', '696969'),
(514, 'gray 40', '666666'),
(515, 'gray 39', '636363'),
(516, 'gray 38', '616161'),
(517, 'gray 37', '5E5E5E'),
(518, 'gray 36', '5C5C5C'),
(519, 'gray 35', '595959'),
(520, 'gray 34', '575757'),
(521, 'gray 33', '545454'),
(522, 'gray 32', '525252'),
(523, 'gray 31', '4F4F4F'),
(524, 'gray 30', '4D4D4D'),
(525, 'gray 29', '4A4A4A'),
(526, 'gray 28', '474747'),
(527, 'gray 27', '454545'),
(528, 'gray 26', '424242'),
(529, 'gray 25', '404040'),
(530, 'gray 24', '3D3D3D'),
(531, 'gray 23', '3B3B3B'),
(532, 'gray 22', '383838'),
(533, 'gray 21', '363636'),
(534, 'gray 20', '333333'),
(535, 'gray 19', '303030'),
(536, 'gray 18', '2E2E2E'),
(537, 'gray 17', '2B2B2B'),
(538, 'gray 16', '292929'),
(539, 'gray 15', '262626'),
(540, 'gray 14', '242424'),
(541, 'gray 13', '212121'),
(542, 'gray 12', '1F1F1F'),
(543, 'gray 11', '1C1C1C'),
(544, 'gray 10', '1A1A1A'),
(545, 'gray 9', '171717'),
(546, 'gray 8', '141414'),
(547, 'gray 7', '121212'),
(548, 'gray 6', '0F0F0F'),
(549, 'gray 5', '0D0D0D'),
(550, 'gray 4', '0A0A0A'),
(551, 'gray 3', '80808'),
(552, 'gray 2', '50505'),
(553, 'gray 1', '30303');

-- --------------------------------------------------------

--
-- Estrutura da tabela `confidentiality`
--

CREATE TABLE IF NOT EXISTS `confidentiality` (
  `confidentiality_id` int(11) NOT NULL AUTO_INCREMENT,
  `confidentiality_name` varchar(10) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`confidentiality_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Extraindo dados da tabela `confidentiality`
--

INSERT INTO `confidentiality` (`confidentiality_id`, `confidentiality_name`) VALUES
(1, 'Public'),
(2, 'Private'),
(3, 'Undefined');


-- --------------------------------------------------------

--
-- Estrutura da tabela `configparams`
--

CREATE TABLE IF NOT EXISTS `configParams` (
  `configParams_id` int(11) NOT NULL,
  `configParams_name` varchar(45) NOT NULL,
  `configParams_value` varchar(45) NOT NULL,
  PRIMARY KEY (`configParams_id`),
  UNIQUE KEY `configParams_name_UNIQUE` (`configParams_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Agendo main configuration 0 False, 1 True';


INSERT INTO `configParams` (`configParams_id`, `configParams_name`, `configParams_value`) VALUES
(11, 'DatumoVersion', '2.1');

-- --------------------------------------------------------

--
-- Estrutura da tabela `country`
--

CREATE TABLE IF NOT EXISTS `country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `country_tel_id` char(3) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=240 ;

--
-- Extraindo dados da tabela `country`
--

INSERT INTO `country` (`country_id`, `country_name`, `country_tel_id`) VALUES
(1, 'Not Specified', '0'),
(2, 'Albania', '355'),
(3, 'Algeria', '213'),
(4, 'American Samoa', '684'),
(5, 'Andorra', '376'),
(6, 'Angola', '244'),
(7, 'Anguilla', '264'),
(8, 'Antarctica', '672'),
(9, 'Antigua', '268'),
(10, 'Argentina', '54'),
(11, 'Armenia', '374'),
(12, 'Aruba', '297'),
(13, 'Ascension Island', '247'),
(14, 'Australia', '61'),
(15, 'Austria', '43'),
(16, 'Austria', '43'),
(17, 'Azberbaijan', '994'),
(18, 'Bahamas', '242'),
(19, 'Bahrain', '973'),
(20, 'Bangladesh', '880'),
(21, 'Barbados', '246'),
(22, 'Barbuda', '268'),
(23, 'Belarus', '375'),
(24, 'Belgium', '32'),
(25, 'Belize', '501'),
(26, 'Benin', '229'),
(27, 'Bermuda', '441'),
(28, 'Bhutan', '975'),
(29, 'Bolivia', '591'),
(30, 'Bosnia', '387'),
(31, 'Botswana', '267'),
(32, 'Brazil', '55'),
(33, 'British Virgin Islands', '284'),
(34, 'Brunei', '673'),
(35, 'Bulgaria', '359'),
(36, 'Burkina Faso', '226'),
(37, 'Burma (Myanmar)', '95'),
(38, 'Burundi', '257'),
(39, 'Cambodia', '855'),
(40, 'Cameroon', '237'),
(41, 'Canada', '1'),
(42, 'Cape Verde Islands', '238'),
(43, 'Cayman Islands', '345'),
(44, 'Central African Rep.', '236'),
(45, 'Chad', '235'),
(46, 'Chile', '56'),
(47, 'China', '86'),
(48, 'Christmas Island', '61'),
(49, 'Cocos Islands', '61'),
(50, 'Colombia', '57'),
(51, 'Comoros', '269'),
(52, 'Congo', '242'),
(53, 'Congo (Dem.Rep.)', '243'),
(54, 'Cook Islands', '682'),
(55, 'Costa Rica', '506'),
(56, 'Croatia', '385'),
(57, 'Cuba', '53'),
(58, 'Cyprus', '357'),
(59, 'Czech Republic', '420'),
(60, 'Denmark', '45'),
(61, 'Diego Garcia', '246'),
(62, 'Djibouti', '253'),
(63, 'Dominica', '767'),
(64, 'Dominican Rep.', '809'),
(65, 'Ecuador', '593'),
(66, 'Egypt', '20'),
(67, 'El Salvador', '503'),
(68, 'Equatorial Guinea', '240'),
(69, 'Eritrea', '291'),
(70, 'Estonia', '372'),
(71, 'Ethiopia', '251'),
(72, 'Faeroe Islands', '298'),
(73, 'Falkland Islands', '500'),
(74, 'Fiji Islands', '679'),
(75, 'Finland', '358'),
(76, 'France', '33'),
(77, 'French Antilles', '596'),
(78, 'French Guiana', '594'),
(79, 'French Polynesia', '689'),
(80, 'Gabon', '241'),
(81, 'Gambia', '220'),
(82, 'Georgia', '995'),
(83, 'Germany', '49'),
(84, 'Ghana', '233'),
(85, 'Gibraltar', '350'),
(86, 'Greece', '30'),
(87, 'Greenland', '299'),
(88, 'Grenada', '473'),
(89, 'Guadeloupe', '590'),
(90, 'Guam', '671'),
(91, 'Guantanamo Bay', '539'),
(92, 'Guatemala', '502'),
(93, 'Guinea', '224'),
(94, 'Guinea Bissau', '245'),
(95, 'Guyana', '592'),
(96, 'Haiti', '509'),
(97, 'Honduras', '504'),
(98, 'Hong Kong', '852'),
(99, 'Hungary', '36'),
(100, 'Iceland', '354'),
(101, 'India', '91'),
(102, 'Indonesia', '62'),
(103, 'Iran', '98'),
(104, 'Iraq', '964'),
(105, 'Ireland', '353'),
(106, 'Israel', '972'),
(107, 'Italy', '39'),
(108, 'Ivory Coast', '225'),
(109, 'Jamaica', '876'),
(110, 'Japan', '81'),
(111, 'Jordan', '962'),
(112, 'Kazakhstan', '7'),
(113, 'Kenya', '254'),
(114, 'Kiribati', '686'),
(115, 'Korea (North)', '850'),
(116, 'Korea (South)', '82'),
(117, 'Kuwait', '965'),
(118, 'Kyrgyzstan', '996'),
(119, 'Laos', '856'),
(120, 'Latvia', '371'),
(121, 'Lebanon', '961'),
(122, 'Lesotho', '266'),
(123, 'Liberia', '231'),
(124, 'Libya', '218'),
(125, 'Liechtenstein', '423'),
(126, 'Lithuania', '370'),
(127, 'Luxembourg', '352'),
(128, 'Macau', '853'),
(129, 'Macedonia', '389'),
(130, 'Madagascar', '261'),
(131, 'Malawi', '265'),
(132, 'Malaysia', '60'),
(133, 'Maldives', '960'),
(134, 'Mali', '223'),
(135, 'Malta', '356'),
(136, 'Mariana Islands', '670'),
(137, 'Marshall Islands', '692'),
(138, 'Martinique', '596'),
(139, 'Mauritania', '222'),
(140, 'Mauritius', '230'),
(141, 'Mayotte Islands', '269'),
(142, 'Mexico', '52'),
(143, 'Micronesia', '691'),
(144, 'Midway Island', '808'),
(145, 'Moldova', '373'),
(146, 'Monaco', '377'),
(147, 'Mongolia', '976'),
(148, 'Montserrat', '664'),
(149, 'Morocco', '212'),
(150, 'Mozambique', '258'),
(151, 'Myanmar (Burma)', '95'),
(152, 'Namibia', '264'),
(153, 'Nauru', '674'),
(154, 'Nepal', '977'),
(155, 'Netherlands', '31'),
(156, 'Netherlands Antilles', '599'),
(157, 'Nevis', '869'),
(158, 'New Caledonia', '687'),
(159, 'New Zealand', '64'),
(160, 'Nicaragua', '505'),
(161, 'Niger', '227'),
(162, 'Nigeria', '234'),
(163, 'Niue', '683'),
(164, 'Norfolk Island', '672'),
(165, 'Norway', '47'),
(166, 'Oman', '968'),
(167, 'Pakistan', '92'),
(168, 'Palau', '680'),
(169, 'Palestine', '970'),
(170, 'Panama', '507'),
(171, 'Papua New Guinea', '675'),
(172, 'Paraguay', '595'),
(173, 'Peru', '51'),
(174, 'Philippines', '63'),
(175, 'Poland', '48'),
(176, 'Portugal', '351'),
(177, 'Puerto Rico', '787'),
(178, 'Qatar', '974'),
(179, 'Reunion Island', '262'),
(180, 'Romania', '40'),
(181, 'Russia', '7'),
(182, 'Rwanda', '250'),
(183, 'San Marino', '378'),
(184, 'Sao Tome & Principe', '239'),
(185, 'Saudi Arabia', '966'),
(186, 'Senegal', '221'),
(187, 'Serbia', '381'),
(188, 'Seychelles', '248'),
(189, 'Sierra Leone', '232'),
(190, 'Singapore', '65'),
(191, 'Slovakia', '421'),
(192, 'Slovenia', '386'),
(193, 'Solomon Islands', '677'),
(194, 'Somalia', '252'),
(195, 'South Africa', '27'),
(196, 'Spain', '34'),
(197, 'Sri Lanka', '94'),
(198, 'St. Helena', '290'),
(199, 'St. Kitts', '869'),
(200, 'St. Lucia', '758'),
(201, 'St. Perre & Miquelon', '508'),
(202, 'St. Vincent', '784'),
(203, 'Sudan', '249'),
(204, 'Suriname', '597'),
(205, 'Swaziland', '268'),
(206, 'Sweden', '46'),
(207, 'Switzerland', '41'),
(208, 'Syria', '963'),
(209, 'Taiwan', '886'),
(210, 'Tajikistan', '992'),
(211, 'Tanzania', '255'),
(212, 'Thailand', '66'),
(213, 'Togo', '228'),
(214, 'Tonga', '676'),
(215, 'Trinidad & Tobago', '868'),
(216, 'Tunisia', '216'),
(217, 'Turkey', '90'),
(218, 'Turkmenistan', '993'),
(219, 'Turks & Caicos', '649'),
(220, 'Tuvalu', '688'),
(221, 'Uganda', '256'),
(222, 'Ukraine', '380'),
(223, 'United Arab Emirates', '971'),
(224, 'United Kingdom (UK)', '44'),
(225, 'United States (USA)', '1'),
(226, 'Uruguay', '598'),
(227, 'US Virgin Islands', '1'),
(228, 'Uzbekistan', '998'),
(229, 'Vanuatu', '678'),
(230, 'Vatican City', '39'),
(231, 'Venezuela', '58'),
(232, 'Vietnam', '84'),
(233, 'Wake Island', '808'),
(234, 'Wallis & Futuna', '681'),
(235, 'Western Samoa', '685'),
(236, 'Yemen', '967'),
(237, 'Yugoslavia', '381'),
(238, 'Zambia', '260'),
(239, 'Zimbabwe', '263');

-- --------------------------------------------------------

--
-- Estrutura da tabela `department`
--

CREATE TABLE IF NOT EXISTS `department` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `department_inst` smallint(6) NOT NULL,
  `department_manager` int(11) NOT NULL,
  PRIMARY KEY (`department_id`),
  KEY `department_inst` (`department_inst`),
  KEY `department_manager` (`department_manager`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

--
-- Estrutura da tabela `institute`
--

CREATE TABLE IF NOT EXISTS `institute` (
  `institute_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `institute_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `institute_address` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `institute_phone` int(11) DEFAULT NULL,
  `institute_country` int(11) NOT NULL,
  `institute_vat` int(11) DEFAULT NULL,
  PRIMARY KEY (`institute_id`),
  KEY `institute_country` (`institute_country`),
  KEY `institute_country_2` (`institute_country`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

--
-- Estrutura da tabela `level`
--

CREATE TABLE IF NOT EXISTS `level` (
  `level_id` int(11) NOT NULL,
  `level_name` varchar(15) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Extraindo dados da tabela `level`
--

INSERT INTO `level` (`level_id`, `level_name`) VALUES
(0, 'Administrator'),
(1, 'Manager'),
(2, 'Regular User');

-- --------------------------------------------------------

--
-- Estrutura da tabela `mask`
--

CREATE TABLE IF NOT EXISTS `mask` (
  `mask_id` int(11) NOT NULL AUTO_INCREMENT,
  `mask_table` varchar(20) COLLATE utf8_bin NOT NULL,
  `mask_name` varchar(30) COLLATE utf8_bin NOT NULL,
  `mask_pic` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`mask_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=10 ;

--
-- Extraindo dados da tabela `mask`
--

INSERT INTO `mask` (`mask_table`, `mask_name`, `mask_pic`) VALUES
('access', 'Table actions', NULL),
('admin', 'User configuration', NULL),
('mask', 'Table masks', NULL),
('report', 'Reports', NULL),
('treeview', 'Treeview configuration', NULL),
('restree', 'Treeview permissions', NULL),
('user', 'Registered users', ''),
('department', 'Departments', NULL),
('institute', 'Institutes', NULL),
('alert', 'Alert type', NULL),
('allowedips', 'Allowed IPs', NULL),
('bool', 'Boolean', NULL),
('color', 'Color list', NULL),
('confidentiality', 'Confidentiality', NULL),
('configParams', 'Settings', NULL),
('country', 'Countries', NULL),
('level', 'User level', NULL),
('menu', 'Menu', NULL),
('module', 'Datumo modules', NULL),
('param', 'Report parameters', NULL),
('pics', 'Resource pictures', NULL),
('plugin', 'Datumo plugins', NULL),
('pub', 'Publicity', NULL),
('pubpages', 'Publicity pages', NULL),
('pubref', 'Publicity reference', NULL),
('reprop', 'Report properties', NULL),
('search', 'Quick search settings', NULL),
('resaccess', 'Field restrictions', NULL);


-- --------------------------------------------------------

--
-- Estrutura da tabela `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(20) COLLATE utf8_bin NOT NULL,
  `menu_description` varchar(50) COLLATE utf8_bin NOT NULL,
  `menu_plugin` int(11) NOT NULL,
  `menu_url` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`menu_id`),
  KEY `menu_plugin` (`menu_plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `menu_name`, `menu_description`, `menu_plugin`, `menu_url`) VALUES
(1, 'My Calendar', 'Personal calendar', 1, 'mycalendar.php'),
(2, 'Image upload', 'Resource image uploader', 1, 'resupload.php'),
(3, 'Reservations', 'Return to reservation system', 1, '../pathMarker/index.php'),
(4, 'Make cookie', 'Set up resource for local confirmation', 1, '../pathMarker/admin/cookie.php'),
(5, 'Mailing', 'Mailing list tool', 1, 'mailing.php');


-- --------------------------------------------------------

--
-- Estrutura da tabela `module`
--

CREATE TABLE IF NOT EXISTS `module` (
  `module_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_table` varchar(20) COLLATE utf8_bin NOT NULL,
  `module_name` varchar(20) COLLATE utf8_bin NOT NULL,
  `module_desc` varchar(30) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

--
-- Extraindo dados da tabela `module`
--

INSERT INTO `module` (`module_id`, `module_table`, `module_name`, `module_desc`) VALUES
(1, 'admin', 'tables', 'Available tables'),
(2, 'mask', 'tables', 'Available tables');

-- --------------------------------------------------------

--
-- Estrutura da tabela `param`
--

CREATE TABLE IF NOT EXISTS `param` (
  `param_id` int(11) NOT NULL AUTO_INCREMENT,
  `param_report` int(11) NOT NULL,
  `param_field` varchar(25) COLLATE utf8_bin NOT NULL,
  `param_name` varchar(25) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`param_id`),
  KEY `param_report` (`param_report`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;


-- --------------------------------------------------------

--
-- Estrutura da tabela `plugin`
--

CREATE TABLE IF NOT EXISTS `plugin` (
  `plugin_id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(20) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `plugin`
--

INSERT INTO `plugin` (`plugin_id`, `plugin_name`) VALUES
(1, 'calendar');


-- --------------------------------------------------------

--
-- Estrutura da tabela `pub`
--

CREATE TABLE IF NOT EXISTS `pub` (
  `pub_id` int(11) NOT NULL AUTO_INCREMENT,
  `pub_company` int(11) NOT NULL,
  `pub_target` int(11) NOT NULL,
  `pub_image` varchar(100) COLLATE utf8_bin NOT NULL,
  `pub_outlink` varchar(100) COLLATE utf8_bin NOT NULL,
  `pub_clicks` int(11) DEFAULT '0',
  `pub_time` int(11) DEFAULT '0',
  `pub_pageViews` int(11) DEFAULT '0',
  PRIMARY KEY (`pub_id`),
  KEY `pub_target` (`pub_target`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;


-- --------------------------------------------------------

--
-- Estrutura da tabela `pubpages`
--

CREATE TABLE IF NOT EXISTS `pubpages` (
  `pubpages_id` int(11) NOT NULL AUTO_INCREMENT,
  `pubpages_name` varchar(20) COLLATE utf8_bin NOT NULL,
  `pubpages_position` varchar(5) COLLATE utf8_bin NOT NULL,
  `pubpages_width` varchar(10) COLLATE utf8_bin NOT NULL,
  `pubpages_height` varchar(10) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`pubpages_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estrutura da tabela `pubref`
--

CREATE TABLE IF NOT EXISTS `pubref` (
  `pubref_id` int(11) NOT NULL AUTO_INCREMENT,
  `pubref_pub` int(11) NOT NULL,
  `pubref_reference` smallint(6) NOT NULL,
  PRIMARY KEY (`pubref_id`),
  KEY `pubref_pub` (`pubref_pub`),
  KEY `pubref_reference` (`pubref_reference`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estrutura da tabela `report`
--

CREATE TABLE IF NOT EXISTS `report` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_name` varchar(20) COLLATE utf8_bin NOT NULL,
  `report_description` varchar(150) COLLATE utf8_bin NOT NULL,
  `report_query` text COLLATE utf8_bin NOT NULL,
  `report_user` int(11) NOT NULL,
  `report_conf` int(11) NOT NULL,
  PRIMARY KEY (`report_id`),
  KEY `report_user` (`report_user`),
  KEY `report_conf` (`report_conf`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `reprop`
--

CREATE TABLE IF NOT EXISTS `reprop` (
  `reprop_id` int(11) NOT NULL AUTO_INCREMENT,
  `reprop_report` int(11) NOT NULL,
  `reprop_attribute` varchar(25) COLLATE utf8_bin NOT NULL,
  `reprop_mask` varchar(25) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`reprop_id`),
  KEY `reprop_report` (`reprop_report`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `resaccess`
--

CREATE TABLE IF NOT EXISTS `resaccess` (
  `resaccess_id` int(11) NOT NULL AUTO_INCREMENT,
  `resaccess_user` int(11) NOT NULL,
  `resaccess_table` varchar(30) NOT NULL,
  `resaccess_column` varchar(30) NOT NULL,
  `resaccess_value` varchar(30) NOT NULL,
  PRIMARY KEY (`resaccess_id`),
  KEY `resaccess_user` (`resaccess_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `restree`
--

CREATE TABLE IF NOT EXISTS `restree` (
  `restree_id` int(11) NOT NULL AUTO_INCREMENT,
  `restree_user` int(11) NOT NULL,
  `restree_name` int(11) NOT NULL,
  `restree_access` int(11) NOT NULL,
  PRIMARY KEY (`restree_id`),
  KEY `restree_user` (`restree_user`),
  KEY `restree_name` (`restree_name`),
  KEY `restree_access` (`restree_access`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `search`
--

CREATE TABLE IF NOT EXISTS `search` (
  `search_id` int(11) NOT NULL AUTO_INCREMENT,
  `search_table` varchar(20) COLLATE utf8_bin NOT NULL,
  `search_query` varchar(1000) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`search_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `treeview`
--

CREATE TABLE IF NOT EXISTS `treeview` (
  `treeview_id` int(11) NOT NULL AUTO_INCREMENT,
  `treeview_name` varchar(20) COLLATE utf8_bin NOT NULL,
  `treeview_description` varchar(70) COLLATE utf8_bin NOT NULL,
  `treeview_table1` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `treeview_table2` varchar(30) COLLATE utf8_bin NOT NULL,
  `treeview_table3` varchar(30) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`treeview_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;


-- --------------------------------------------------------

--
-- Estrutura da tabela `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_login` varchar(32) COLLATE utf8_bin NOT NULL,
  `user_passwd` varchar(64) COLLATE utf8_bin NOT NULL COMMENT 'pwd',
  `user_level` int(11) NOT NULL,
  `user_firstname` varchar(16) COLLATE utf8_bin NOT NULL,
  `user_lastname` varchar(16) COLLATE utf8_bin NOT NULL,
  `user_dep` int(11) NOT NULL,
  `user_phone` varchar(32) COLLATE utf8_bin NOT NULL,
  `user_phonext` varchar(8) COLLATE utf8_bin DEFAULT NULL,
  `user_mobile` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  `user_email` varchar(64) COLLATE utf8_bin NOT NULL,
  `user_alert` int(11) NOT NULL COMMENT '1 - Alert by email<br />2 - Alert by SMS',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_login` (`user_login`),
  KEY `user_dep` (`user_dep`),
  KEY `user_alert` (`user_alert`),
  KEY `user_level` (`user_level`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users registered. You can change your personal data here' AUTO_INCREMENT=1 ;
