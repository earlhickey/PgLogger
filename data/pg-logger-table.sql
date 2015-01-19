DROP TABLE IF EXISTS `application_log`;

CREATE TABLE `application_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_date` varchar(25) DEFAULT NULL,
  `priority` varchar(30) DEFAULT 'UNKNOWN',
  `event` text,
  `source` varchar(255) DEFAULT NULL,
  `uri` text,
  `ip` varchar(45) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
