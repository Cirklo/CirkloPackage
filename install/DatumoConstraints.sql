--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`admin_user`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`admin_permission`) REFERENCES `access` (`access_id`);

 
--
-- Limitadores para a tabela `department`
--
ALTER TABLE `department`
  ADD CONSTRAINT `department_ibfk_1` FOREIGN KEY (`department_inst`) REFERENCES `institute` (`institute_id`),
  ADD CONSTRAINT `department_ibfk_2` FOREIGN KEY (`department_manager`) REFERENCES `user` (`user_id`);

  
--
-- Limitadores para a tabela `institute`
--
ALTER TABLE `institute`
  ADD CONSTRAINT `institute_ibfk_1` FOREIGN KEY (`institute_country`) REFERENCES `country` (`country_id`);

--
-- Limitadores para a tabela `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`menu_plugin`) REFERENCES `plugin` (`plugin_id`);

--
-- Limitadores para a tabela `param`
--
ALTER TABLE `param`
  ADD CONSTRAINT `param_ibfk_1` FOREIGN KEY (`param_report`) REFERENCES `report` (`report_id`);

--
-- Limitadores para a tabela `pub`
--
ALTER TABLE `pub`
  ADD CONSTRAINT `pub_ibfk_1` FOREIGN KEY (`pub_target`) REFERENCES `pubpages` (`pubpages_id`);
  

--
-- Limitadores para a tabela `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`report_user`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`report_conf`) REFERENCES `confidentiality` (`confidentiality_id`);

--
-- Limitadores para a tabela `reprop`
--
ALTER TABLE `reprop`
  ADD CONSTRAINT `reprop_ibfk_1` FOREIGN KEY (`reprop_report`) REFERENCES `report` (`report_id`);

--
-- Limitadores para a tabela `resaccess`
--
ALTER TABLE `resaccess`
  ADD CONSTRAINT `resaccess_ibfk_1` FOREIGN KEY (`resaccess_user`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;


--
-- Limitadores para a tabela `restree`
--
ALTER TABLE `restree`
  ADD CONSTRAINT `restree_ibfk_1` FOREIGN KEY (`restree_user`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `restree_ibfk_2` FOREIGN KEY (`restree_name`) REFERENCES `treeview` (`treeview_id`),
  ADD CONSTRAINT `restree_ibfk_3` FOREIGN KEY (`restree_access`) REFERENCES `access` (`access_id`);

--
-- Limitadores para a tabela `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`user_level`) REFERENCES `level` (`level_id`),
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`user_dep`) REFERENCES `department` (`department_id`),
  ADD CONSTRAINT `user_ibfk_3` FOREIGN KEY (`user_alert`) REFERENCES `alert` (`alert_id`);



