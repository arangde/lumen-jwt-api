DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_field` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `settings_setting_field(191)_index` (`setting_field`(191))
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `settings` */

insert  into `settings`(`id`,`setting_field`,`value`,`created_at`,`updated_at`,`name`) values 
(1,'project_title','Membership management system','2018-07-24 06:59:15','2018-07-24 06:59:15','Project Title'),
(17,'direct_bonus_income','500','2018-07-31 12:31:24','2018-07-31 12:31:24','Direct bonus income when a member is recommended by new one.'),
(18,'point_rate','1','2018-07-31 12:57:56','2018-07-31 12:57:56','Rate of the additional points of when a member adds an income. (percentage in number)'),
(19,'recurring_income','330','2018-07-31 13:31:55','2018-07-31 13:31:55','Recurring incomes'),
(20,'recurring_income_rate','1','2018-08-01 11:18:00','2018-08-01 11:18:00','Rate of the additional incomes of recurring periods. (percentage in number)'),
(21,'recurring_periods','12','2018-08-01 18:20:37','2018-08-01 18:20:37','Total recurring periods'),
(22,'recommends_number1','5','2018-08-01 11:43:36','2018-08-01 19:11:27','The first number of recommended members'),
(23,'recommends_rate1','2','2018-08-01 11:45:06','2018-08-01 11:45:06','The first rate of recommended members (percentage in number)'),
(24,'recommends_number2','10','2018-08-01 11:45:32','2018-08-01 11:45:32','The second number of recommended members'),
(25,'recommends_rate2','4','2018-08-01 11:46:01','2018-08-01 11:46:01','The second rate of recommended members (percentage in number)'),
(27,'recommends_number3','15','2018-08-21 20:14:45','2018-08-21 20:14:45','The third number of recommended members'),
(28,'recommends_rate3','6','2018-08-21 20:15:39','2018-08-21 20:15:39','The third rate of recommended members (percentage in number)'),
(29,'recommends_number4','20','2018-08-21 20:16:20','2018-08-21 20:16:20','The fourth number of recommended members'),
(30,'recommends_rate4','8','2018-08-21 20:16:50','2018-08-21 20:16:50','The fourth rate of recommended members (percentage in number)'),
(31,'recommends_number5','25','2018-08-21 20:17:18','2018-08-21 20:17:18','The fifth number of recommended members'),
(32,'recommends_rate5','10','2018-08-21 20:17:50','2018-08-21 20:17:50','The fifth rate of recommended members (percentage in number)'),
(33,'recommends_number6','30','2018-08-21 20:18:20','2018-08-21 20:18:20','The sixth number of recommended members'),
(34,'recommends_rate6','15','2018-08-21 20:19:06','2018-08-21 20:19:06','The sixth rate of recommended members (percentage in number)');
