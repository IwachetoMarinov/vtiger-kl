-- Generated migration: apply LOCAL schema additions onto FRESH schema
-- Local schema: vtiger_gpm_schema_only.sql
-- Fresh schema: vtiger_gpm_dump.sql

SET FOREIGN_KEY_CHECKS=0;

-- Missing tables (create)

-- TABLE vtiger_account_currency
CREATE TABLE `vtiger_account_currency` (
  `account_currencyid` int NOT NULL AUTO_INCREMENT,
  `account_currency` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`account_currencyid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_account_currency_seq
CREATE TABLE `vtiger_account_currency_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_api_clients
CREATE TABLE `vtiger_api_clients` (
  `client_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_secret_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_api_refresh_tokens
CREATE TABLE `vtiger_api_refresh_tokens` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `client_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_asset
CREATE TABLE `vtiger_asset` (
  `assetid` int NOT NULL AUTO_INCREMENT,
  `asset` varchar(200) NOT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `picklist_valueid` int NOT NULL DEFAULT '0',
  `sortorderid` int DEFAULT '0',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`assetid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_asset_seq
CREATE TABLE `vtiger_asset_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_bankaccount
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

-- TABLE vtiger_bankaccountcf
CREATE TABLE `vtiger_bankaccountcf` (
  `bankaccountid` int NOT NULL,
  `cf_1149` varchar(255) DEFAULT '',
  `cf_1151` varchar(100) DEFAULT '',
  PRIMARY KEY (`bankaccountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_certificate_status
CREATE TABLE `vtiger_certificate_status` (
  `certificate_statusid` int NOT NULL AUTO_INCREMENT,
  `certificate_status` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`certificate_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_certificate_status_seq
CREATE TABLE `vtiger_certificate_status_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_1064
CREATE TABLE `vtiger_cf_1064` (
  `cf_1064id` int NOT NULL AUTO_INCREMENT,
  `cf_1064` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_1064id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_1064_seq
CREATE TABLE `vtiger_cf_1064_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_1132
CREATE TABLE `vtiger_cf_1132` (
  `cf_1132id` int NOT NULL AUTO_INCREMENT,
  `cf_1132` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_1132id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_1132_seq
CREATE TABLE `vtiger_cf_1132_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_853
CREATE TABLE `vtiger_cf_853` (
  `cf_853id` int NOT NULL AUTO_INCREMENT,
  `cf_853` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_853id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_853_seq
CREATE TABLE `vtiger_cf_853_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_855
CREATE TABLE `vtiger_cf_855` (
  `cf_855id` int NOT NULL AUTO_INCREMENT,
  `cf_855` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_855id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_855_seq
CREATE TABLE `vtiger_cf_855_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_873
CREATE TABLE `vtiger_cf_873` (
  `cf_873id` int NOT NULL AUTO_INCREMENT,
  `cf_873` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_873id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_873_seq
CREATE TABLE `vtiger_cf_873_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_896
CREATE TABLE `vtiger_cf_896` (
  `cf_896id` int NOT NULL AUTO_INCREMENT,
  `cf_896` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_896id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_896_seq
CREATE TABLE `vtiger_cf_896_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_900
CREATE TABLE `vtiger_cf_900` (
  `cf_900id` int NOT NULL AUTO_INCREMENT,
  `cf_900` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_900id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_900_seq
CREATE TABLE `vtiger_cf_900_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_906
CREATE TABLE `vtiger_cf_906` (
  `cf_906id` int NOT NULL AUTO_INCREMENT,
  `cf_906` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_906id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_906_seq
CREATE TABLE `vtiger_cf_906_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_924
CREATE TABLE `vtiger_cf_924` (
  `cf_924id` int NOT NULL AUTO_INCREMENT,
  `cf_924` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_924id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_924_seq
CREATE TABLE `vtiger_cf_924_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_927
CREATE TABLE `vtiger_cf_927` (
  `cf_927id` int NOT NULL AUTO_INCREMENT,
  `cf_927` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_927id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_927_seq
CREATE TABLE `vtiger_cf_927_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_cf_947
CREATE TABLE `vtiger_cf_947` (
  `cf_947id` int NOT NULL AUTO_INCREMENT,
  `cf_947` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`cf_947id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_cf_947_seq
CREATE TABLE `vtiger_cf_947_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_google_map
CREATE TABLE `vtiger_google_map` (
  `module` varchar(255) DEFAULT NULL,
  `parameter_name` varchar(255) DEFAULT NULL,
  `parameter_field` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpm_labels
CREATE TABLE `vtiger_gpm_labels` (
  `gpm_labelsid` int NOT NULL AUTO_INCREMENT,
  `gpm_labels` varchar(200) NOT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `picklist_valueid` int NOT NULL DEFAULT '0',
  `sortorderid` int DEFAULT '0',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`gpm_labelsid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpm_labels_seq
CREATE TABLE `vtiger_gpm_labels_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_gpm_metal_type
CREATE TABLE `vtiger_gpm_metal_type` (
  `gpm_metal_typeid` int NOT NULL AUTO_INCREMENT,
  `gpm_metal_type` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`gpm_metal_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpm_metal_type_seq
CREATE TABLE `vtiger_gpm_metal_type_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_gpm_order_location
CREATE TABLE `vtiger_gpm_order_location` (
  `gpm_order_locationid` int NOT NULL AUTO_INCREMENT,
  `gpm_order_location` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`gpm_order_locationid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpm_order_location_seq
CREATE TABLE `vtiger_gpm_order_location_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_gpm_order_type
CREATE TABLE `vtiger_gpm_order_type` (
  `gpm_order_typeid` int NOT NULL AUTO_INCREMENT,
  `gpm_order_type` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`gpm_order_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpm_order_type_seq
CREATE TABLE `vtiger_gpm_order_type_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_gpmcompany
CREATE TABLE `vtiger_gpmcompany` (
  `gpmcompanyid` int NOT NULL,
  `company_name` varchar(256) DEFAULT NULL,
  `company_orosoft_code` varchar(256) DEFAULT NULL,
  `company_gst_no` varchar(256) DEFAULT NULL,
  `company_address` varchar(512) DEFAULT NULL,
  `company_phone` varchar(64) DEFAULT NULL,
  `company_fax` varchar(64) DEFAULT NULL,
  `company_website` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`gpmcompanyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpmcompany_bankaccount_rel
CREATE TABLE `vtiger_gpmcompany_bankaccount_rel` (
  `gpmcompanyid` int NOT NULL,
  `bankaccountid` int NOT NULL,
  PRIMARY KEY (`gpmcompanyid`,`bankaccountid`),
  KEY `bankaccountid` (`bankaccountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpmcompanycf
CREATE TABLE `vtiger_gpmcompanycf` (
  `gpmcompanyid` int NOT NULL,
  `cf_1101` varchar(255) DEFAULT '',
  PRIMARY KEY (`gpmcompanyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpmcryptotrx
CREATE TABLE `vtiger_gpmcryptotrx` (
  `gpmcryptotrxid` int NOT NULL AUTO_INCREMENT,
  `contact_id` int DEFAULT NULL,
  `wallet_address` varchar(100) DEFAULT NULL,
  `asset` varchar(255) DEFAULT NULL,
  `trx_date` date DEFAULT NULL,
  PRIMARY KEY (`gpmcryptotrxid`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpmcryptotrxcf
CREATE TABLE `vtiger_gpmcryptotrxcf` (
  `gpmcryptotrxid` int NOT NULL,
  PRIMARY KEY (`gpmcryptotrxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpmintent
CREATE TABLE `vtiger_gpmintent` (
  `gpmintentid` int NOT NULL,
  `intent_no` varchar(64) DEFAULT NULL,
  `contact_id` int DEFAULT NULL,
  `contact_erp_no` varchar(32) DEFAULT NULL,
  `gpm_metal_type` varchar(64) DEFAULT NULL,
  `intent_status` varchar(128) DEFAULT NULL,
  `indicative_spot_price` decimal(12,4) DEFAULT NULL,
  `gpm_order_type` varchar(128) DEFAULT NULL,
  `remark` varchar(500) DEFAULT NULL,
  `gpm_order_location` varchar(128) DEFAULT NULL,
  `gpm_order_other_location` varchar(128) DEFAULT NULL,
  `gpm_labels` varchar(256) DEFAULT NULL,
  `package_currency` varchar(12) DEFAULT NULL,
  `indicative_fx_spot` varchar(255) DEFAULT NULL,
  `package_price` decimal(24,2) DEFAULT NULL,
  `package_price_usd` decimal(24,2) DEFAULT NULL,
  `trade_date` date DEFAULT NULL,
  `document_no` varchar(64) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `special_instructions` varchar(500) DEFAULT NULL,
  `spot_price` varchar(255) DEFAULT NULL,
  `fx_spot_price` varchar(255) DEFAULT NULL,
  `total_oz` decimal(12,8) DEFAULT NULL,
  `total_amount` decimal(24,4) DEFAULT NULL,
  `total_foreign_amount` decimal(24,4) DEFAULT NULL,
  `chk_trade_order` varchar(5) DEFAULT NULL,
  `chk_collection_request` varchar(5) DEFAULT NULL,
  `chk_tc_invoice_to_client` varchar(5) DEFAULT NULL,
  `chk_collection_acknowledgement` varchar(5) DEFAULT NULL,
  `lead_id` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  PRIMARY KEY (`gpmintentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpmintent_line
CREATE TABLE `vtiger_gpmintent_line` (
  `gpmintentid` int NOT NULL,
  `gpmmetalid` int DEFAULT NULL,
  `remark` varchar(512) DEFAULT NULL,
  `qty` decimal(12,8) DEFAULT NULL,
  `fine_oz` decimal(12,8) DEFAULT NULL,
  `premium_or_discount` decimal(5,2) DEFAULT NULL,
  `premium_or_discount_usd` decimal(12,4) DEFAULT NULL,
  `value_usd` decimal(24,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_gpmintentcf
CREATE TABLE `vtiger_gpmintentcf` (
  `gpmintentid` int NOT NULL,
  `cf_1132` varchar(255) DEFAULT '',
  PRIMARY KEY (`gpmintentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_holdingcertificate
CREATE TABLE `vtiger_holdingcertificate` (
  `holdingcertificateid` int NOT NULL,
  `guid` varchar(128) DEFAULT NULL,
  `contact_id` int DEFAULT NULL,
  `notes_id` int DEFAULT NULL,
  `certificate_hash` varchar(128) DEFAULT NULL,
  `verify_url` varchar(256) DEFAULT NULL,
  `certificate_status` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`holdingcertificateid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_holdingcertificatecf
CREATE TABLE `vtiger_holdingcertificatecf` (
  `holdingcertificateid` int NOT NULL,
  PRIMARY KEY (`holdingcertificateid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_intent_status
CREATE TABLE `vtiger_intent_status` (
  `intent_statusid` int NOT NULL AUTO_INCREMENT,
  `intent_status` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`intent_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_intent_status_seq
CREATE TABLE `vtiger_intent_status_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_masforex
CREATE TABLE `vtiger_masforex` (
  `masforexid` int NOT NULL,
  `price_date` date DEFAULT NULL,
  `usd_sgd` decimal(8,4) DEFAULT NULL,
  `eur_sgd` decimal(8,4) DEFAULT NULL,
  `cad_sgd` decimal(8,4) DEFAULT NULL,
  `chf_sgd` decimal(8,4) DEFAULT NULL,
  `hkd_sgd` decimal(8,4) DEFAULT NULL,
  `myr_sgd` decimal(8,4) DEFAULT NULL,
  PRIMARY KEY (`masforexid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_masforexcf
CREATE TABLE `vtiger_masforexcf` (
  `masforexid` int NOT NULL,
  PRIMARY KEY (`masforexid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_metal_type
CREATE TABLE `vtiger_metal_type` (
  `metal_typeid` int NOT NULL AUTO_INCREMENT,
  `metal_type` varchar(200) NOT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `picklist_valueid` int NOT NULL DEFAULT '0',
  `sortorderid` int DEFAULT '0',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`metal_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_metal_type_seq
CREATE TABLE `vtiger_metal_type_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_metalprice
CREATE TABLE `vtiger_metalprice` (
  `metalpriceid` int NOT NULL,
  `type_of_metal` varchar(100) DEFAULT NULL,
  `price_date` date DEFAULT NULL,
  `am_rate` decimal(13,7) DEFAULT NULL,
  `pm_rate` decimal(13,7) DEFAULT NULL,
  PRIMARY KEY (`metalpriceid`),
  UNIQUE KEY `unique_metalprice_idx` (`price_date`,`type_of_metal`),
  KEY `metaprice_date_idx` (`type_of_metal`,`price_date` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_metalprice_user_field
CREATE TABLE `vtiger_metalprice_user_field` (
  `recordid` int NOT NULL,
  `userid` int NOT NULL,
  `starred` varchar(100) DEFAULT NULL,
  KEY `fk_metalpriceid_vtiger_metalprice_user_field` (`recordid`),
  CONSTRAINT `fk_metalpriceid_vtiger_metalprice_user_field` FOREIGN KEY (`recordid`) REFERENCES `vtiger_metalprice` (`metalpriceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_metalpricecf
CREATE TABLE `vtiger_metalpricecf` (
  `metalpriceid` int NOT NULL,
  PRIMARY KEY (`metalpriceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_metals
CREATE TABLE `vtiger_metals` (
  `metalsid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `fineoz` decimal(10,2) DEFAULT NULL,
  `createdtime` datetime DEFAULT NULL,
  `metal_type` varchar(50) DEFAULT NULL,
  `assigned_user_id` int DEFAULT NULL,
  PRIMARY KEY (`metalsid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_metalscf
CREATE TABLE `vtiger_metalscf` (
  `metalsid` int NOT NULL,
  `cf_894` date DEFAULT NULL,
  PRIMARY KEY (`metalsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_modtracker_basic_seq
CREATE TABLE `vtiger_modtracker_basic_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_package_currency
CREATE TABLE `vtiger_package_currency` (
  `package_currencyid` int NOT NULL AUTO_INCREMENT,
  `package_currency` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`package_currencyid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_package_currency_seq
CREATE TABLE `vtiger_package_currency_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_related_entity
CREATE TABLE `vtiger_related_entity` (
  `related_entityid` int NOT NULL AUTO_INCREMENT,
  `related_entity` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`related_entityid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_related_entity_seq
CREATE TABLE `vtiger_related_entity_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE vtiger_type_of_metal
CREATE TABLE `vtiger_type_of_metal` (
  `type_of_metalid` int NOT NULL AUTO_INCREMENT,
  `type_of_metal` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`type_of_metalid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3;

-- TABLE vtiger_type_of_metal_seq
CREATE TABLE `vtiger_type_of_metal_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Missing columns (add)
ALTER TABLE `vtiger_activity` ADD COLUMN `assigned_by` int DEFAULT NULL;
ALTER TABLE `vtiger_assetscf` ADD COLUMN `cf_873` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_assetscf` ADD COLUMN `cf_875` varchar(200) DEFAULT '';
ALTER TABLE `vtiger_assetscf` ADD COLUMN `cf_877` date DEFAULT NULL;
ALTER TABLE `vtiger_contactdetails` ADD COLUMN `introducer_id` int DEFAULT NULL;
ALTER TABLE `vtiger_contactdetails` ADD COLUMN `company_id` int DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_896` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_898` varchar(200) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_900` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_902` text;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_904` varchar(200) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_906` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_908` varchar(200) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_910` varchar(50) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_912` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_914` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_916` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_918` varchar(200) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_920` varchar(200) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_922` text;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_924` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_927` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_929` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_931` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_933` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_935` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_937` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_939` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_941` text;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_943` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_945` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_947` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_951` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_955` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_959` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_963` varchar(200) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_967` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_971` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_973` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_975` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_977` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_979` date DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_981` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_983` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_985` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_987` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_989` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_991` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_993` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_995` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_997` decimal(5,2) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_999` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1001` varchar(24) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1003` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1005` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1007` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1009` varchar(200) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1011` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1013` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1015` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1017` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1019` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1021` text;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1064` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1066` decimal(29,5) DEFAULT NULL;
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1070` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1072` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_contactscf` ADD COLUMN `cf_1157` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_leaddetails` ADD COLUMN `company_id` int DEFAULT NULL;
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_853` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_855` varchar(255) DEFAULT '';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_857` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_859` varchar(155) DEFAULT '';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_861` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_863` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_865` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_867` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_869` varchar(3) DEFAULT '0';
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_871` text;
ALTER TABLE `vtiger_leadscf` ADD COLUMN `cf_1153` varchar(200) DEFAULT '';

SET FOREIGN_KEY_CHECKS=1;
