++ ./db_backups/tmp_schema.sql	2025-11-05 16:16:23.938040000 +0200
DROP TABLE IF EXISTS `vtiger_account_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vtiger_account_currency` (
  `account_currency` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`account_currencyid`)
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vtiger_account_currency_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vtiger_account_currency_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vtiger_bankaccount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vtiger_bankaccountcf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vtiger_bankaccountcf` (
  `bankaccountid` int NOT NULL,
  PRIMARY KEY (`bankaccountid`)
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vtiger_related_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vtiger_related_entity` (
  `related_entity` varchar(200) NOT NULL,
  `sortorderid` int DEFAULT NULL,
  `presence` int NOT NULL DEFAULT '1',
  `color` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`related_entityid`)
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vtiger_related_entity_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vtiger_related_entity_seq` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
