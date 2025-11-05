DROP TABLE IF EXISTS `vtiger_account_currency`;
CREATE TABLE `vtiger_account_currency` (
  `account_currencyid` int NOT NULL AUTO_INCREMENT,
  `account_currency` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`account_currencyid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
DROP TABLE IF EXISTS `vtiger_account_currency_seq`;
CREATE TABLE `vtiger_account_currency_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `vtiger_bankaccount`;
CREATE TABLE `vtiger_bankaccount` (
  `bankaccountid` int NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_alias_name` varchar(100) DEFAULT NULL,
  `bank_address` varchar(250) DEFAULT NULL,
  `swift_code` varchar(16) DEFAULT NULL,
  `bank_code` varchar(16) DEFAULT NULL,
  `branch_code` varchar(16) DEFAULT NULL,
  `account_no` varchar(100) DEFAULT NULL,
  `account_currency` varchar(12) DEFAULT NULL,
  `beneficiary_name` varchar(100) DEFAULT NULL,
  `beneficiary_address` varchar(250) DEFAULT NULL,
  `related_entity` varchar(12) DEFAULT NULL,
  `erp_account_no` varchar(100) DEFAULT NULL,
  `intermediary_bank` varchar(100) DEFAULT NULL,
  `intermediary_swift_code` varchar(16) DEFAULT NULL,
  `footer_company_name` varchar(256) DEFAULT NULL,
  `footer_company_address` varchar(512) DEFAULT NULL,
  `footer_company_phone` varchar(64) DEFAULT NULL,
  `footer_company_fax` varchar(64) DEFAULT NULL,
  `footer_company_website` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`bankaccountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
DROP TABLE IF EXISTS `vtiger_bankaccountcf`;
CREATE TABLE `vtiger_bankaccountcf` (
  `bankaccountid` int NOT NULL,
  PRIMARY KEY (`bankaccountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
DROP TABLE IF EXISTS `vtiger_related_entity`;
CREATE TABLE `vtiger_related_entity` (
  `related_entityid` int NOT NULL AUTO_INCREMENT,
  `related_entity` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`related_entityid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
DROP TABLE IF EXISTS `vtiger_related_entity_seq`;
CREATE TABLE `vtiger_related_entity_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
