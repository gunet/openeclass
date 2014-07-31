-- this table is required in eclass db

DROP TABLE IF EXISTS `user_sso`;
CREATE TABLE `user_sso` (
  `username` varchar(255) NOT NULL COLLATE utf8_bin,
  `token` varchar(255) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  PRIMARY KEY (`username`, `token`, `session_id`)
) DEFAULT CHARSET=utf8;
