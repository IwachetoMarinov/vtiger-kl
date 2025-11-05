--- ./db_backups/vtiger_gpm_schema_2025_11_05_1128.sql	2025-11-05 11:28:29.814886600 +0200
+++ ./db_backups/tmp_schema.sql	2025-11-05 15:41:57.534911700 +0200
@@ -163,6 +163,25 @@
   CONSTRAINT `fk_1_vtiger_account` FOREIGN KEY (`accountid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
 /*!40101 SET character_set_client = @saved_cs_client */;
+DROP TABLE IF EXISTS `vtiger_account_currency`;
+/*!40101 SET @saved_cs_client     = @@character_set_client */;
+/*!50503 SET character_set_client = utf8mb4 */;
+CREATE TABLE `vtiger_account_currency` (
+  `account_currencyid` int NOT NULL AUTO_INCREMENT,
+  `account_currency` varchar(200) NOT NULL,
+  `sortorderid` int DEFAULT NULL,
+  `presence` int NOT NULL DEFAULT '1',
+  `color` varchar(10) DEFAULT NULL,
+  PRIMARY KEY (`account_currencyid`)
+) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
+/*!40101 SET character_set_client = @saved_cs_client */;
+DROP TABLE IF EXISTS `vtiger_account_currency_seq`;
+/*!40101 SET @saved_cs_client     = @@character_set_client */;
+/*!50503 SET character_set_client = utf8mb4 */;
+CREATE TABLE `vtiger_account_currency_seq` (
+  `id` int NOT NULL
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+/*!40101 SET character_set_client = @saved_cs_client */;
 DROP TABLE IF EXISTS `vtiger_accountbillads`;
 /*!40101 SET @saved_cs_client     = @@character_set_client */;
 /*!50503 SET character_set_client = utf8mb4 */;
@@ -595,6 +614,41 @@
   PRIMARY KEY (`auditid`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
 /*!40101 SET character_set_client = @saved_cs_client */;
+DROP TABLE IF EXISTS `vtiger_bankaccount`;
+/*!40101 SET @saved_cs_client     = @@character_set_client */;
+/*!50503 SET character_set_client = utf8mb4 */;
+CREATE TABLE `vtiger_bankaccount` (
+  `bankaccountid` int NOT NULL,
+  `bank_name` varchar(100) DEFAULT NULL,
+  `bank_alias_name` varchar(100) DEFAULT NULL,
+  `bank_address` varchar(250) DEFAULT NULL,
+  `swift_code` varchar(16) DEFAULT NULL,
+  `bank_code` varchar(16) DEFAULT NULL,
+  `branch_code` varchar(16) DEFAULT NULL,
+  `account_no` varchar(100) DEFAULT NULL,
+  `account_currency` varchar(12) DEFAULT NULL,
+  `beneficiary_name` varchar(100) DEFAULT NULL,
+  `beneficiary_address` varchar(250) DEFAULT NULL,
+  `related_entity` varchar(12) DEFAULT NULL,
+  `erp_account_no` varchar(100) DEFAULT NULL,
+  `intermediary_bank` varchar(100) DEFAULT NULL,
+  `intermediary_swift_code` varchar(16) DEFAULT NULL,
+  `footer_company_name` varchar(256) DEFAULT NULL,
+  `footer_company_address` varchar(512) DEFAULT NULL,
+  `footer_company_phone` varchar(64) DEFAULT NULL,
+  `footer_company_fax` varchar(64) DEFAULT NULL,
+  `footer_company_website` varchar(128) DEFAULT NULL,
+  PRIMARY KEY (`bankaccountid`)
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
+/*!40101 SET character_set_client = @saved_cs_client */;
+DROP TABLE IF EXISTS `vtiger_bankaccountcf`;
+/*!40101 SET @saved_cs_client     = @@character_set_client */;
+/*!50503 SET character_set_client = utf8mb4 */;
+CREATE TABLE `vtiger_bankaccountcf` (
+  `bankaccountid` int NOT NULL,
+  PRIMARY KEY (`bankaccountid`)
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
+/*!40101 SET character_set_client = @saved_cs_client */;
 DROP TABLE IF EXISTS `vtiger_blocks`;
 /*!40101 SET @saved_cs_client     = @@character_set_client */;
 /*!50503 SET character_set_client = utf8mb4 */;
@@ -1955,7 +2009,7 @@
   PRIMARY KEY (`ruleid`),
   KEY `fk_1_vtiger_def_org_share` (`permission`),
   CONSTRAINT `fk_1_vtiger_def_org_share` FOREIGN KEY (`permission`) REFERENCES `vtiger_org_share_action_mapping` (`share_action_id`) ON DELETE CASCADE
-) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3;
+) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3;
 /*!40101 SET character_set_client = @saved_cs_client */;
 DROP TABLE IF EXISTS `vtiger_def_org_share_seq`;
 /*!40101 SET @saved_cs_client     = @@character_set_client */;
@@ -2424,7 +2478,7 @@
   KEY `field_block_idx` (`block`),
   KEY `field_displaytype_idx` (`displaytype`),
   CONSTRAINT `fk_1_vtiger_field` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
-) ENGINE=InnoDB AUTO_INCREMENT=1103 DEFAULT CHARSET=utf8mb3;
+) ENGINE=InnoDB AUTO_INCREMENT=1125 DEFAULT CHARSET=utf8mb3;
 /*!40101 SET character_set_client = @saved_cs_client */;
 DROP TABLE IF EXISTS `vtiger_field_seq`;
 /*!40101 SET @saved_cs_client     = @@character_set_client */;
@@ -5161,6 +5215,25 @@
   `id` int NOT NULL
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 /*!40101 SET character_set_client = @saved_cs_client */;
+DROP TABLE IF EXISTS `vtiger_related_entity`;
+/*!40101 SET @saved_cs_client     = @@character_set_client */;
+/*!50503 SET character_set_client = utf8mb4 */;
+CREATE TABLE `vtiger_related_entity` (
+  `related_entityid` int NOT NULL AUTO_INCREMENT,
+  `related_entity` varchar(200) NOT NULL,
+  `sortorderid` int DEFAULT NULL,
+  `presence` int NOT NULL DEFAULT '1',
+  `color` varchar(10) DEFAULT NULL,
+  PRIMARY KEY (`related_entityid`)
+) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
+/*!40101 SET character_set_client = @saved_cs_client */;
+DROP TABLE IF EXISTS `vtiger_related_entity_seq`;
+/*!40101 SET @saved_cs_client     = @@character_set_client */;
+/*!50503 SET character_set_client = utf8mb4 */;
+CREATE TABLE `vtiger_related_entity_seq` (
+  `id` int NOT NULL
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
+/*!40101 SET character_set_client = @saved_cs_client */;
 DROP TABLE IF EXISTS `vtiger_relatedlists`;
 /*!40101 SET @saved_cs_client     = @@character_set_client */;
 /*!50503 SET character_set_client = utf8mb4 */;
@@ -6880,7 +6953,7 @@
   `handler_class` varchar(64) NOT NULL,
   `ismodule` int NOT NULL,
   PRIMARY KEY (`id`)
-) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb3;
+) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3;
 /*!40101 SET character_set_client = @saved_cs_client */;
 DROP TABLE IF EXISTS `vtiger_ws_entity_fieldtype`;
 /*!40101 SET @saved_cs_client     = @@character_set_client */;
