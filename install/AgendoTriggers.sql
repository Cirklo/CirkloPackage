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