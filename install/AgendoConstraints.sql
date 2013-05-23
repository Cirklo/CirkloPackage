--
-- Constraints for dumped tables
--

--
-- Constraints for table `allowedips`
--
ALTER TABLE `allowedips`
  ADD CONSTRAINT `allowedips_ibfk_1` FOREIGN KEY (`allowedips_institute`) REFERENCES `institute` (`institute_id`) ON DELETE CASCADE ON UPDATE CASCADE;


--
-- Constraints for table `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `announcement_ibfk_1` FOREIGN KEY (`announcement_object`) REFERENCES `resource` (`resource_id`);

  
--
-- Constraints for table `board`
--
ALTER TABLE `board`
  ADD CONSTRAINT `board_ibfk_1` FOREIGN KEY (`board_parent`) REFERENCES `board` (`board_id`);


--
-- Constraints for table `blacklist`
--
ALTER TABLE `blacklist`
  ADD CONSTRAINT `blacklist_ibfk_1` FOREIGN KEY (`blacklist_user`) REFERENCES `user` (`user_id`);
  
  
ALTER TABLE `department` ADD `department_default` INT NULL ,
ADD INDEX ( `department_default` ) ;


ALTER TABLE `department` ADD FOREIGN KEY ( `department_default` ) REFERENCES `project` (
`project_id`
); 


--
-- Constraints for table `entry`
--
ALTER TABLE `entry`
  ADD CONSTRAINT `entry_ibfk_10` FOREIGN KEY (`entry_user`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `entry_ibfk_11` FOREIGN KEY (`entry_repeat`) REFERENCES `repetition` (`repetition_id`),
  ADD CONSTRAINT `entry_ibfk_12` FOREIGN KEY (`entry_status`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `entry_ibfk_13` FOREIGN KEY (`entry_resource`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `entry_ibfk_14` FOREIGN KEY (`entry_project`) REFERENCES `project` (`project_id`);

--
-- Constraints for table `equip`
--
ALTER TABLE `equip`
  ADD CONSTRAINT `equip_ibfk_14` FOREIGN KEY (`equip_resourceid`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `equip_ibfk_15` FOREIGN KEY (`equip_boardID`) REFERENCES `board` (`board_id`),
  ADD CONSTRAINT `equip_ibfk_16` FOREIGN KEY (`equip_para`) REFERENCES `parameter` (`parameter_id`),
  ADD CONSTRAINT `equip_ibfk_17` FOREIGN KEY (`equip_user`) REFERENCES `user` (`user_id`);

--
-- Limitadores para a tabela `institute`
--
ALTER TABLE `institute`
  ADD CONSTRAINT `institute_ibfk_2` FOREIGN KEY (`institute_pricetype`) REFERENCES `pricetype` (`pricetype_id`);

  
--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`item_user`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`item_state`) REFERENCES `item_state` (`item_state_id`),
  ADD CONSTRAINT `item_ibfk_3` FOREIGN KEY (`item_resource`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `item_ibfk_4` FOREIGN KEY (`item_project`) REFERENCES `project` (`project_id`);

  
--
-- Constraints for table `item_assoc`
--
ALTER TABLE `item_assoc`
  ADD CONSTRAINT `item_assoc_ibfk_1` FOREIGN KEY (`item_assoc_item`) REFERENCES `item` (`item_id`),
  ADD CONSTRAINT `item_assoc_ibfk_2` FOREIGN KEY (`item_assoc_entry`) REFERENCES `entry` (`entry_id`);


ALTER TABLE `machine`
  ADD CONSTRAINT `machine_ibfk_1` FOREIGN KEY (`machine_resource`) REFERENCES `resource` (`resource_id`);

--
-- Constraints for table `measure`
--
ALTER TABLE `measure`
  ADD CONSTRAINT `measure_ibfk_1` FOREIGN KEY (`measure_equip`) REFERENCES `equip` (`equip_id`);

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_4` FOREIGN KEY (`permissions_user`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permissions_ibfk_5` FOREIGN KEY (`permissions_resource`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `permissions_ibfk_6` FOREIGN KEY (`permissions_level`) REFERENCES `permlevel` (`permlevel_id`),
  ADD CONSTRAINT `permissions_ibfk_7` FOREIGN KEY (`permissions_training`) REFERENCES `bool` (`bool_id`),
  ADD CONSTRAINT `permissions_ibfk_8` FOREIGN KEY (`permissions_sendmail`) REFERENCES `bool` (`bool_id`);
  
ALTER TABLE permissions add unique (`permissions_user`, `permissions_resource`);

--
-- constraints for table `pics`
--
ALTER TABLE `pics`
  ADD CONSTRAINT `pics_ibfk_1` FOREIGN KEY (`pics_resource`) REFERENCES `resource` (`resource_id`);


--
-- Constraints for table `price`
--
ALTER TABLE `price`
  ADD CONSTRAINT `price_ibfk_6` FOREIGN KEY (`price_resource`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `price_ibfk_7` FOREIGN KEY (`price_type`) REFERENCES `pricetype` (`pricetype_id`);


--
-- Constraints for table `proj_dep_assoc`
--
  
ALTER TABLE `proj_dep_assoc`
  ADD CONSTRAINT `proj_dep_assoc_ibfk_1` FOREIGN KEY (`proj_dep_assoc_project`) REFERENCES `project` (`project_id`),
  ADD CONSTRAINT `proj_dep_assoc_ibfk_2` FOREIGN KEY (`proj_dep_assoc_department`) REFERENCES `department` (`department_id`),
  ADD CONSTRAINT `proj_dep_assoc_ibfk_3` FOREIGN KEY (`proj_dep_assoc_active`) REFERENCES `bool` (`bool_id`),
  ADD CONSTRAINT `proj_dep_assoc_ibfk_4` FOREIGN KEY (`proj_dep_assoc_visible`) REFERENCES `bool` (`bool_id`);
  
ALTER TABLE proj_dep_assoc ADD UNIQUE (proj_dep_assoc_project, proj_dep_assoc_department);
--
-- Constraints for table `happyhour_assoc`
--
ALTER TABLE `happyhour_assoc`
  ADD CONSTRAINT `happyhour_assoc_ibfk_2` FOREIGN KEY (`happyhour_assoc_happyhour`) REFERENCES `happyhour` (`happyhour_id`),
  ADD CONSTRAINT `happyhour_assoc_ibfk_3` FOREIGN KEY (`happyhour_assoc_resource`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `happyhour_assoc_ibfk_4` FOREIGN KEY (`happyhour_assoc_weekusage`) REFERENCES `bool` (`bool_id`);
  
  
--
-- Limitadores para a tabela `pubref`
--
ALTER TABLE `pubref`
  ADD CONSTRAINT `pubref_ibfk_2` FOREIGN KEY (`pubref_reference`) REFERENCES `resourcetype` (`resourcetype_id`),
  ADD CONSTRAINT `pubref_ibfk_1` FOREIGN KEY (`pubref_pub`) REFERENCES `pub` (`pub_id`);

--
-- Constraints for table `resource`
--
ALTER TABLE `resource`
  ADD CONSTRAINT `resource_ibfk_10` FOREIGN KEY (`resource_resp`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `resource_ibfk_11` FOREIGN KEY (`resource_color`) REFERENCES `color` (`color_id`),
  ADD CONSTRAINT `resource_ibfk_12` FOREIGN KEY (`resource_type`) REFERENCES `resourcetype` (`resourcetype_id`),
  ADD CONSTRAINT `resource_ibfk_13` FOREIGN KEY (`resource_computer`) REFERENCES `computer` (`computer_id`),
  ADD CONSTRAINT `resource_ibfk_9` FOREIGN KEY (`resource_status`) REFERENCES `resstatus` (`resstatus_id`);

--
-- Constraints for table `similarresources`
--
ALTER TABLE `similarresources`
  ADD CONSTRAINT `similarresources_ibfk_1` FOREIGN KEY (`similarresources_resource`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `similarresources_ibfk_2` FOREIGN KEY (`similarresources_similar`) REFERENCES `resource` (`resource_id`);


--
-- Constraints for table `xfields`
--
ALTER TABLE `xfields`
  ADD CONSTRAINT `xfields_ibfk_1` FOREIGN KEY (`xfields_type`) REFERENCES `xfieldsinputtype` (`xfieldsinputtype_id`),
  ADD CONSTRAINT `xfields_ibfk_2` FOREIGN KEY (`xfields_resource`) REFERENCES `resource` (`resource_id`),
  ADD CONSTRAINT `xfields_ibfk_3` FOREIGN KEY (`xfields_placement`) REFERENCES `xfieldsplacement` (`xfieldsplacement_id`);
  
--
-- Constraints for table `xfieldsval`
--
ALTER TABLE `xfieldsval`
  ADD CONSTRAINT `xfieldsval_ibfk_2` FOREIGN KEY (`xfieldsval_field`) REFERENCES `xfields` (`xfields_id`),
  ADD CONSTRAINT `xfieldsval_ibfk_3` FOREIGN KEY (`xfieldsval_entry`) REFERENCES `entry` (`entry_id`) ON DELETE CASCADE;

  
ALTER TABLE `happyhour`
  ADD CONSTRAINT `happyhour_ibfk_2` FOREIGN KEY (`happyhour_endday`) REFERENCES `weekday` (`weekday_id`),
  ADD CONSTRAINT `happyhour_ibfk_1` FOREIGN KEY (`happyhour_startday`) REFERENCES `weekday` (`weekday_id`);



