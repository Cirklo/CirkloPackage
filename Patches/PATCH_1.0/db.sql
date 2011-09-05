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
