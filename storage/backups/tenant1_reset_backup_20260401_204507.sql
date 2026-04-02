-- Backup tenant 1 generated at 2026-04-01T20:45:07
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `alertas`
--
-- WHERE:  empresa_id=1

LOCK TABLES `alertas` WRITE;
/*!40000 ALTER TABLE `alertas` DISABLE KEYS */;
/*!40000 ALTER TABLE `alertas` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `auditorias`
--
-- WHERE:  empresa_id=1

LOCK TABLES `auditorias` WRITE;
/*!40000 ALTER TABLE `auditorias` DISABLE KEYS */;
INSERT INTO `auditorias` VALUES (1,1,1,'LOGIN_SUCESSO','usuarios',1,'{\"ip\":\"127.0.0.1\"}','2026-03-31 11:08:43'),(2,1,1,'LOGOUT_SUCESSO','usuarios',1,'{\"ip\":\"127.0.0.1\"}','2026-03-31 11:13:26'),(3,1,1,'LOGIN_SUCESSO','usuarios',1,'{\"ip\":\"127.0.0.1\"}','2026-03-31 11:13:40'),(4,1,2,'LOGIN_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-03-31 11:22:22'),(5,1,2,'LOGIN_SENHA_INVALIDA','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-03-31 11:38:09'),(6,1,1,'LOGOUT_SUCESSO','usuarios',1,'{\"ip\":\"127.0.0.1\"}','2026-03-31 12:01:28'),(7,1,1,'LOGIN_SUCESSO','usuarios',1,'{\"ip\":\"127.0.0.1\"}','2026-03-31 12:01:33'),(8,1,1,'PERFIL_MONITORAMENTO_CRIADO','perfis_monitoramento',1,'{\"nome\":\"Paula\",\"frequencia_alerta\":\"DIARIO\",\"ativo\":1}','2026-03-31 12:05:18'),(9,1,1,'PALAVRA_CHAVE_CRIADA','palavras_chave',1,'{\"perfil_monitoramento_id\":1,\"termo\":\"alimentos\",\"peso\":1}','2026-03-31 12:06:04'),(10,1,1,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 12:06:25'),(11,1,1,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 12:06:28'),(12,1,1,'LOGOUT_SUCESSO','usuarios',1,'{\"ip\":\"127.0.0.1\"}','2026-03-31 12:13:28'),(13,1,2,'LOGIN_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-03-31 12:15:09'),(14,1,2,'FONTE_COLETA_STATUS_ALTERADO','fontes_coleta',3,'{\"ativa\":1}','2026-03-31 12:38:11'),(15,1,2,'FONTE_COLETA_STATUS_ALTERADO','fontes_coleta',2,'{\"ativa\":1}','2026-03-31 12:38:13'),(16,1,1,'COLETA_EXECUTADA_MANUAL','coletas_execucao',1,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 3 | Inseridos: 3 | Atualizados: 0 | Duplicados: 0 | Erros: 0\"}','2026-03-31 13:01:59'),(17,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',2,'{\"fonte_id\":3,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 40 | Atualizados: 0 | Duplicados: 0 | Erros: 0\"}','2026-03-31 13:26:53'),(18,1,2,'COLETA_EXECUTADA_MANUAL','coletas_execucao',3,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 50 | Inseridos: 47 | Atualizados: 0 | Duplicados: 3 | Erros: 0\"}','2026-03-31 13:27:20'),(19,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',4,'{\"fonte_id\":2,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 40 | Atualizados: 0 | Duplicados: 0 | Erros: 0\"}','2026-03-31 13:27:36'),(20,1,2,'COLETA_EXECUTADA_MANUAL','coletas_execucao',5,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 50 | Inseridos: 0 | Atualizados: 0 | Duplicados: 50 | Erros: 0\"}','2026-03-31 13:29:24'),(21,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',6,'{\"fonte_id\":3,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 0 | Duplicados: 40 | Erros: 0\"}','2026-03-31 13:31:13'),(22,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":50,\"total_analisados\":50,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:23:53'),(23,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:24:06'),(24,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:24:26'),(25,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:24:29'),(26,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:24:32'),(27,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:26:13'),(28,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:26:45'),(29,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:27:14'),(30,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:27:15'),(31,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:27:30'),(32,1,1,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:29:44'),(33,1,1,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:32:02'),(34,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:35:58'),(35,1,2,'PALAVRA_CHAVE_CRIADA','palavras_chave',2,'{\"perfil_monitoramento_id\":1,\"termo\":\"servi?os\",\"peso\":1}','2026-03-31 14:36:52'),(36,1,2,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 14:38:03'),(37,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:38:10'),(38,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:38:29'),(39,1,2,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 14:43:00'),(40,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:43:09'),(41,1,2,'COLETA_EXECUTADA_MANUAL','coletas_execucao',7,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 50 | Inseridos: 0 | Atualizados: 0 | Duplicados: 50 | Erros: 0\"}','2026-03-31 14:43:40'),(42,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',8,'{\"fonte_id\":2,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 0 | Duplicados: 40 | Erros: 0\"}','2026-03-31 14:43:49'),(43,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',9,'{\"fonte_id\":3,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 0 | Duplicados: 40 | Erros: 0\"}','2026-03-31 14:43:53'),(44,1,2,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 14:45:30'),(45,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:45:35'),(46,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:46:10'),(47,1,2,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 14:46:57'),(48,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 14:47:03'),(49,1,2,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 14:47:41'),(50,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":34,\"total_atualizadas\":0}','2026-03-31 14:47:46'),(51,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 14:48:38'),(52,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 15:06:16'),(53,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 15:06:48'),(54,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 15:07:03'),(55,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 17:03:19'),(56,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',10,'{\"fonte_id\":2,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 40 | Duplicados: 0 | Erros: 0\"}','2026-03-31 17:04:19'),(57,1,2,'PALAVRA_CHAVE_EXCLUIDA','palavras_chave',2,'{\"perfil_monitoramento_id\":1,\"termo\":\"servi?os\"}','2026-03-31 17:08:16'),(58,1,2,'PALAVRA_CHAVE_EXCLUIDA','palavras_chave',1,'{\"perfil_monitoramento_id\":1,\"termo\":\"alimentos\"}','2026-03-31 17:08:18'),(59,1,2,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 17:08:43'),(60,1,2,'LOGOUT_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-03-31 17:09:01'),(61,1,2,'LOGIN_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-03-31 18:53:14'),(62,1,2,'COLETA_EXECUTADA_MANUAL','coletas_execucao',11,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 50 | Inseridos: 0 | Atualizados: 0 | Duplicados: 50 | Erros: 0\"}','2026-03-31 18:56:19'),(63,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',12,'{\"fonte_id\":2,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 0 | Duplicados: 40 | Erros: 0\"}','2026-03-31 18:56:42'),(64,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',13,'{\"fonte_id\":3,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 40 | Duplicados: 0 | Erros: 0\"}','2026-03-31 18:56:47'),(65,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":1,\"total_editais\":130,\"total_analisados\":0,\"total_compativeis\":0,\"total_geradas\":0,\"total_atualizadas\":0}','2026-03-31 18:57:27'),(66,1,2,'PERFIL_MONITORAMENTO_CRIADO','perfis_monitoramento',2,'{\"nome\":\"Paula\",\"frequencia_alerta\":\"DIARIO\",\"ativo\":1}','2026-03-31 20:08:37'),(67,1,2,'PALAVRA_CHAVE_CRIADA','palavras_chave',3,'{\"perfil_monitoramento_id\":1,\"termo\":\"servi?os\",\"peso\":2}','2026-03-31 20:08:57'),(68,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":2,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 20:09:08'),(69,1,2,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',1,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Fernanda\",\"frequencia_alerta\":\"DIARIO\"}','2026-03-31 20:09:37'),(70,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',1,'{\"correspondencia_id\":34,\"status_acompanhamento\":\"EM_ANALISE\",\"acao\":\"ATUALIZADO\"}','2026-03-31 20:19:32'),(71,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',1,'{\"correspondencia_id\":34,\"status_acompanhamento\":\"EM_ANALISE\",\"acao\":\"ATUALIZADO\"}','2026-03-31 20:20:07'),(72,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',2,'{\"correspondencia_id\":1,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"CRIADO\"}','2026-03-31 20:24:58'),(73,1,2,'PIPELINE_TAREFA_REMOVIDA','favorito_tarefas',8,'{\"favorito_id\":2}','2026-03-31 20:26:49'),(74,1,2,'PIPELINE_STATUS_ATUALIZADO','favoritos',2,'{\"status_anterior\":\"PROPOSTA\",\"status_novo\":\"DESCARTADO\"}','2026-03-31 20:27:10'),(75,1,2,'PIPELINE_STATUS_ATUALIZADO','favoritos',2,'{\"status_anterior\":\"DESCARTADO\",\"status_novo\":\"FAVORITO\"}','2026-03-31 20:27:20'),(76,1,2,'PIPELINE_TAREFA_CRIADA','favorito_tarefas',9,'{\"favorito_id\":2,\"titulo\":\"Total por g?nero\",\"data_limite\":\"2026-03-31\"}','2026-03-31 20:28:56'),(77,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',3,'{\"correspondencia_id\":2,\"status_acompanhamento\":\"ENCERRADO\",\"acao\":\"CRIADO\"}','2026-03-31 20:29:56'),(78,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',3,'{\"correspondencia_id\":2,\"status_acompanhamento\":\"DESCARTADO\",\"acao\":\"ATUALIZADO\"}','2026-03-31 20:30:01'),(79,1,2,'PIPELINE_STATUS_ATUALIZADO','favoritos',2,'{\"status_anterior\":\"FAVORITO\",\"status_novo\":\"EM_ANALISE\"}','2026-03-31 20:32:28'),(80,1,2,'PIPELINE_STATUS_ATUALIZADO','favoritos',3,'{\"status_anterior\":\"DESCARTADO\",\"status_novo\":\"PROPOSTA\"}','2026-03-31 20:32:33'),(81,1,2,'PIPELINE_TAREFA_STATUS_ATUALIZADO','favorito_tarefas',9,'{\"favorito_id\":2,\"status_anterior\":\"PENDENTE\",\"status_novo\":\"EM_ANDAMENTO\"}','2026-03-31 20:32:41'),(82,1,2,'PIPELINE_TAREFA_STATUS_ATUALIZADO','favorito_tarefas',5,'{\"favorito_id\":2,\"status_anterior\":\"PENDENTE\",\"status_novo\":\"PENDENTE\"}','2026-03-31 20:32:50'),(83,1,2,'PIPELINE_TAREFA_STATUS_ATUALIZADO','favorito_tarefas',5,'{\"favorito_id\":2,\"status_anterior\":\"PENDENTE\",\"status_novo\":\"CONCLUIDA\"}','2026-03-31 20:32:54'),(84,1,2,'PIPELINE_TAREFA_STATUS_ATUALIZADO','favorito_tarefas',6,'{\"favorito_id\":2,\"status_anterior\":\"PENDENTE\",\"status_novo\":\"CONCLUIDA\"}','2026-03-31 20:33:06'),(98,1,2,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"CRIADA\"}','2026-03-31 21:07:58'),(99,1,2,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"ATUALIZADA\"}','2026-03-31 21:08:43'),(100,1,2,'PROPOSTA_ATUALIZADA','propostas_execucao',2,'{\"status\":\"APROVADA\"}','2026-03-31 21:09:30'),(104,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',8,'{\"correspondencia_id\":3,\"status_acompanhamento\":\"FAVORITO\",\"acao\":\"CRIADO\"}','2026-03-31 21:26:47'),(105,1,2,'PIPELINE_STATUS_ATUALIZADO','favoritos',2,'{\"status_anterior\":\"EM_ANALISE\",\"status_novo\":\"ENCERRADO\"}','2026-03-31 21:33:22'),(106,1,2,'PROPOSTA_SUBMISSAO_REGISTRADA','proposta_submissoes',2,'{\"proposta_id\":2,\"status_proposta_novo\":\"ENVIADA\"}','2026-03-31 21:36:06'),(107,1,2,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"ATUALIZADA\"}','2026-03-31 21:36:32'),(108,1,2,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',2,'{\"proposta_id\":2,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-03-31 21:36:49'),(109,1,2,'PROPOSTA_APROVACAO_DECIDIDA','proposta_aprovacoes',2,'{\"proposta_id\":2,\"decisao\":\"APROVADA\",\"status_proposta_novo\":\"APROVADA\"}','2026-03-31 21:37:00'),(110,1,2,'PROPOSTA_ATUALIZADA','propostas_execucao',2,'{\"status\":\"APROVADA\"}','2026-03-31 21:37:19'),(111,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":2,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 21:39:40'),(112,1,2,'PERFIL_MONITORAMENTO_CRIADO','perfis_monitoramento',3,'{\"nome\":\"Paula Fernanda\",\"frequencia_alerta\":\"SEMANAL\",\"ativo\":1}','2026-03-31 21:40:59'),(113,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 21:41:22'),(114,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 21:41:29'),(115,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 21:41:38'),(116,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',9,'{\"correspondencia_id\":4,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"CRIADO\"}','2026-03-31 21:47:02'),(117,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',9,'{\"correspondencia_id\":4,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"ATUALIZADO\"}','2026-03-31 21:47:21'),(118,1,2,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"ATUALIZADA\"}','2026-03-31 21:48:54'),(119,1,2,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',3,'{\"proposta_id\":2,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-03-31 21:49:29'),(120,1,2,'PROPOSTA_APROVACAO_DECIDIDA','proposta_aprovacoes',3,'{\"proposta_id\":2,\"decisao\":\"APROVADA\",\"status_proposta_novo\":\"APROVADA\"}','2026-03-31 21:49:54'),(121,1,2,'PROPOSTA_SUBMISSAO_REGISTRADA','proposta_submissoes',3,'{\"proposta_id\":2,\"status_proposta_novo\":\"ENVIADA\"}','2026-03-31 21:50:58'),(122,1,2,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"ATUALIZADA\"}','2026-03-31 21:51:37'),(123,1,2,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',4,'{\"proposta_id\":2,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-03-31 21:52:01'),(124,1,2,'PROPOSTA_APROVACAO_DECIDIDA','proposta_aprovacoes',4,'{\"proposta_id\":2,\"decisao\":\"APROVADA\",\"status_proposta_novo\":\"APROVADA\"}','2026-03-31 21:52:13'),(125,1,2,'PROPOSTA_SUBMISSAO_REGISTRADA','proposta_submissoes',4,'{\"proposta_id\":2,\"status_proposta_novo\":\"ENVIADA\"}','2026-03-31 21:52:29'),(128,1,2,'PROPOSTA_RESULTADO_REGISTRADO','proposta_resultados',3,'{\"proposta_id\":2,\"situacao\":\"EM_JULGAMENTO\",\"favorito_id\":2}','2026-03-31 22:04:00'),(129,1,2,'PROPOSTA_RESULTADO_REGISTRADO','proposta_resultados',4,'{\"proposta_id\":2,\"situacao\":\"VENCEDORA\",\"favorito_id\":2}','2026-03-31 22:04:36'),(130,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 22:14:47'),(131,1,2,'COLETA_EXECUTADA_MANUAL','coletas_execucao',14,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 50 | Inseridos: 0 | Atualizados: 0 | Duplicados: 50 | Erros: 0\"}','2026-03-31 22:15:37'),(132,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',15,'{\"fonte_id\":2,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 0 | Duplicados: 40 | Erros: 0\"}','2026-03-31 22:15:44'),(133,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',16,'{\"fonte_id\":3,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 0 | Duplicados: 40 | Erros: 0\"}','2026-03-31 22:15:47'),(134,1,2,'COLETA_EXECUTADA_MANUAL','coletas_execucao',17,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 50 | Inseridos: 0 | Atualizados: 0 | Duplicados: 50 | Erros: 0\"}','2026-03-31 22:15:52'),(135,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 22:47:27'),(136,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":130,\"total_analisados\":130,\"total_compativeis\":34,\"total_geradas\":0,\"total_atualizadas\":34}','2026-03-31 22:47:44'),(137,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',2,'{\"correspondencia_id\":1,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"ATUALIZADO\"}','2026-03-31 22:50:24'),(138,1,2,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"ATUALIZADA\"}','2026-03-31 22:53:27'),(139,1,2,'LOGOUT_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-03-31 22:56:54'),(140,1,2,'LOGIN_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-04-01 09:57:37'),(141,1,2,'COLETA_EXECUTADA_MANUAL','coletas_execucao',18,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 50 | Inseridos: 50 | Atualizados: 0 | Duplicados: 0 | Erros: 0\"}','2026-04-01 09:57:54'),(142,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',19,'{\"fonte_id\":2,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 40 | Atualizados: 0 | Duplicados: 0 | Erros: 0\"}','2026-04-01 09:58:01'),(143,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',20,'{\"fonte_id\":3,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 40 | Atualizados: 0 | Duplicados: 0 | Erros: 0\"}','2026-04-01 09:58:05'),(144,1,2,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":260,\"total_analisados\":260,\"total_compativeis\":68,\"total_geradas\":34,\"total_atualizadas\":34}','2026-04-01 09:58:22'),(145,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',10,'{\"correspondencia_id\":68,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"CRIADO\"}','2026-04-01 09:59:17'),(146,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',2,'{\"correspondencia_id\":1,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"ATUALIZADO\"}','2026-04-01 10:00:33'),(147,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',2,'{\"correspondencia_id\":1,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"ATUALIZADO\"}','2026-04-01 10:01:50'),(148,1,2,'PIPELINE_TAREFA_STATUS_ATUALIZADO','favorito_tarefas',7,'{\"favorito_id\":2,\"status_anterior\":\"PENDENTE\",\"status_novo\":\"EM_ANDAMENTO\"}','2026-04-01 10:02:40'),(149,1,2,'PIPELINE_TAREFA_STATUS_ATUALIZADO','favorito_tarefas',7,'{\"favorito_id\":2,\"status_anterior\":\"EM_ANDAMENTO\",\"status_novo\":\"CONCLUIDA\"}','2026-04-01 10:02:51'),(150,1,2,'PIPELINE_TAREFA_STATUS_ATUALIZADO','favorito_tarefas',9,'{\"favorito_id\":2,\"status_anterior\":\"EM_ANDAMENTO\",\"status_novo\":\"CONCLUIDA\"}','2026-04-01 10:02:55'),(151,1,2,'PIPELINE_TAREFA_STATUS_ATUALIZADO','favorito_tarefas',11,'{\"favorito_id\":2,\"status_anterior\":\"PENDENTE\",\"status_novo\":\"CONCLUIDA\"}','2026-04-01 10:03:01'),(152,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',11,'{\"correspondencia_id\":35,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"CRIADO\"}','2026-04-01 10:04:11'),(153,1,2,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',11,'{\"correspondencia_id\":35,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"ATUALIZADO\"}','2026-04-01 10:04:26'),(154,1,2,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',3,'{\"favorito_id\":11,\"acao\":\"CRIADA\"}','2026-04-01 10:04:36'),(155,1,2,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',5,'{\"proposta_id\":3,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-04-01 10:05:09'),(156,1,2,'PROPOSTA_APROVACAO_DECIDIDA','proposta_aprovacoes',5,'{\"proposta_id\":3,\"decisao\":\"APROVADA\",\"status_proposta_novo\":\"APROVADA\"}','2026-04-01 10:05:25'),(157,1,2,'PROPOSTA_SUBMISSAO_REGISTRADA','proposta_submissoes',5,'{\"proposta_id\":3,\"status_proposta_novo\":\"ENVIADA\"}','2026-04-01 10:05:45'),(158,1,2,'PROPOSTA_RESULTADO_REGISTRADO','proposta_resultados',5,'{\"proposta_id\":3,\"situacao\":\"VENCEDORA\",\"favorito_id\":11}','2026-04-01 10:06:20'),(159,1,2,'COLETA_EXECUTADA_MANUAL','coletas_execucao',21,'{\"fonte_id\":1,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 50 | Inseridos: 0 | Atualizados: 0 | Duplicados: 50 | Erros: 0\"}','2026-04-01 12:09:49'),(160,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',22,'{\"fonte_id\":2,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 0 | Duplicados: 40 | Erros: 0\"}','2026-04-01 12:09:54'),(161,1,2,'COLETA_SIMULADA_EXECUTADA','coletas_execucao',23,'{\"fonte_id\":3,\"status\":\"SUCESSO\",\"resumo\":\"Lidos: 40 | Inseridos: 0 | Atualizados: 0 | Duplicados: 40 | Erros: 0\"}','2026-04-01 12:09:59'),(162,1,2,'LOGOUT_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-04-01 12:12:58'),(163,1,2,'LOGIN_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-04-01 12:13:08'),(164,1,2,'LOGOUT_SUCESSO','usuarios',2,'{\"ip\":\"127.0.0.1\"}','2026-04-01 12:13:14'),(165,1,1,'LOGIN_SUCESSO','usuarios',1,'{\"ip\":\"127.0.0.1\"}','2026-04-01 12:13:18'),(166,1,1,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":260,\"total_analisados\":260,\"total_compativeis\":68,\"total_geradas\":0,\"total_atualizadas\":68}','2026-04-01 13:35:14'),(167,1,1,'PERFIL_MONITORAMENTO_ATUALIZADO','perfis_monitoramento',2,'{\"nome_antigo\":\"Paula\",\"nome_novo\":\"Paula\",\"frequencia_alerta\":\"DIARIO\"}','2026-04-01 13:36:11'),(168,1,1,'PALAVRA_CHAVE_CRIADA','palavras_chave',4,'{\"perfil_monitoramento_id\":2,\"termo\":\"engenharia\",\"peso\":2}','2026-04-01 13:36:32'),(169,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"ATUALIZADA\"}','2026-04-01 13:47:44'),(170,1,1,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',6,'{\"proposta_id\":2,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-04-01 13:47:53'),(171,1,1,'PROPOSTA_APROVACAO_DECIDIDA','proposta_aprovacoes',6,'{\"proposta_id\":2,\"decisao\":\"APROVADA\",\"status_proposta_novo\":\"APROVADA\"}','2026-04-01 13:47:59'),(172,1,1,'PROPOSTA_SUBMISSAO_REGISTRADA','proposta_submissoes',6,'{\"proposta_id\":2,\"status_proposta_novo\":\"ENVIADA\"}','2026-04-01 13:48:11'),(173,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',2,'{\"correspondencia_id\":1,\"status_acompanhamento\":\"ENCERRADO\",\"acao\":\"ATUALIZADO\"}','2026-04-01 19:50:19'),(174,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',12,'{\"correspondencia_id\":37,\"status_acompanhamento\":\"EM_ANALISE\",\"acao\":\"CRIADO\"}','2026-04-01 19:50:27'),(175,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',3,'{\"correspondencia_id\":2,\"status_acompanhamento\":\"ENCERRADO\",\"acao\":\"ATUALIZADO\"}','2026-04-01 19:50:44'),(176,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',12,'{\"correspondencia_id\":37,\"status_acompanhamento\":\"ENCERRADO\",\"acao\":\"ATUALIZADO\"}','2026-04-01 19:50:50'),(177,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',8,'{\"correspondencia_id\":3,\"status_acompanhamento\":\"FAVORITO\",\"acao\":\"ATUALIZADO\"}','2026-04-01 19:50:52'),(178,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',8,'{\"correspondencia_id\":3,\"status_acompanhamento\":\"ENCERRADO\",\"acao\":\"ATUALIZADO\"}','2026-04-01 19:50:56'),(179,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',9,'{\"correspondencia_id\":4,\"status_acompanhamento\":\"ENCERRADO\",\"acao\":\"ATUALIZADO\"}','2026-04-01 19:51:00'),(180,1,1,'PIPELINE_STATUS_ATUALIZADO','favoritos',10,'{\"status_anterior\":\"PROPOSTA\",\"status_novo\":\"ENCERRADO\"}','2026-04-01 20:00:32'),(181,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"ATUALIZADA\"}','2026-04-01 20:06:05'),(182,1,1,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',7,'{\"proposta_id\":2,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-04-01 20:06:19'),(183,1,1,'PROPOSTA_APROVACAO_DECIDIDA','proposta_aprovacoes',7,'{\"proposta_id\":2,\"decisao\":\"APROVADA\",\"status_proposta_novo\":\"APROVADA\"}','2026-04-01 20:06:26'),(184,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',2,'{\"favorito_id\":2,\"acao\":\"ATUALIZADA\"}','2026-04-01 20:06:51'),(185,1,1,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',8,'{\"proposta_id\":2,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-04-01 20:07:01'),(186,1,1,'PROPOSTA_APROVACAO_DECIDIDA','proposta_aprovacoes',8,'{\"proposta_id\":2,\"decisao\":\"APROVADA\",\"status_proposta_novo\":\"APROVADA\"}','2026-04-01 20:07:23'),(187,1,1,'PROPOSTA_SUBMISSAO_REGISTRADA','proposta_submissoes',7,'{\"proposta_id\":2,\"status_proposta_novo\":\"ENVIADA\"}','2026-04-01 20:09:03'),(188,1,1,'CORRESPONDENCIAS_PROCESSADAS','correspondencias',NULL,'{\"total_perfis\":3,\"total_editais\":260,\"total_analisados\":520,\"total_compativeis\":68,\"total_geradas\":0,\"total_atualizadas\":68}','2026-04-01 20:27:44'),(189,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',13,'{\"correspondencia_id\":10,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"CRIADO\"}','2026-04-01 20:27:57'),(190,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',4,'{\"favorito_id\":13,\"acao\":\"CRIADA\"}','2026-04-01 20:28:26'),(191,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',4,'{\"favorito_id\":13,\"acao\":\"ATUALIZADA\"}','2026-04-01 20:28:49'),(192,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',4,'{\"favorito_id\":13,\"acao\":\"ATUALIZADA\"}','2026-04-01 20:29:32'),(193,1,1,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',9,'{\"proposta_id\":4,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-04-01 20:29:41'),(194,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',4,'{\"favorito_id\":13,\"acao\":\"ATUALIZADA\"}','2026-04-01 20:31:52'),(195,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',4,'{\"favorito_id\":13,\"acao\":\"ATUALIZADA\"}','2026-04-01 20:36:48'),(196,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',4,'{\"favorito_id\":13,\"acao\":\"ATUALIZADA\"}','2026-04-01 20:36:52'),(197,1,1,'DECISAO_OPORTUNIDADE_REGISTRADA','favoritos',9,'{\"correspondencia_id\":4,\"status_acompanhamento\":\"PROPOSTA\",\"acao\":\"ATUALIZADO\"}','2026-04-01 20:37:32'),(198,1,1,'PIPELINE_STATUS_ATUALIZADO','favoritos',9,'{\"status_anterior\":\"PROPOSTA\",\"status_novo\":\"EM_ANALISE\"}','2026-04-01 20:37:57'),(199,1,1,'PROPOSTA_RASCUNHO_GERADA','propostas_execucao',5,'{\"favorito_id\":9,\"acao\":\"CRIADA\"}','2026-04-01 20:38:03'),(200,1,1,'PROPOSTA_APROVACAO_SOLICITADA','proposta_aprovacoes',10,'{\"proposta_id\":5,\"status_proposta_novo\":\"EM_REVISAO\"}','2026-04-01 20:38:14'),(201,1,1,'PROPOSTA_APROVACAO_DECIDIDA','proposta_aprovacoes',10,'{\"proposta_id\":5,\"decisao\":\"APROVADA\",\"status_proposta_novo\":\"APROVADA\"}','2026-04-01 20:38:32'),(202,1,1,'PROPOSTA_SUBMISSAO_REGISTRADA','proposta_submissoes',8,'{\"proposta_id\":5,\"status_proposta_novo\":\"ENVIADA\"}','2026-04-01 20:38:48'),(203,1,1,'PROPOSTA_RESULTADO_REGISTRADO','proposta_resultados',6,'{\"proposta_id\":5,\"situacao\":\"EM_JULGAMENTO\",\"favorito_id\":9}','2026-04-01 20:38:57');
/*!40000 ALTER TABLE `auditorias` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `correspondencias`
--
-- WHERE:  empresa_id=1

LOCK TABLES `correspondencias` WRITE;
/*!40000 ALTER TABLE `correspondencias` DISABLE KEYS */;
INSERT INTO `correspondencias` VALUES (1,90,1,1,14.45,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(2,89,1,1,14.44,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(3,87,1,1,14.43,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(4,86,1,1,14.42,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(5,84,1,1,14.41,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(6,83,1,1,14.40,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(7,81,1,1,14.39,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(8,80,1,1,14.38,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(9,78,1,1,14.37,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(10,77,1,1,14.36,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(11,75,1,1,14.35,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(12,74,1,1,14.34,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(13,72,1,1,14.32,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(14,71,1,1,14.32,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(15,69,1,1,14.30,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(16,68,1,1,14.30,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(17,66,1,1,14.28,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(18,65,1,1,14.28,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(19,63,1,1,14.26,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(20,62,1,1,14.25,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(21,60,1,1,14.24,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(22,59,1,1,14.23,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(23,57,1,1,14.22,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(24,56,1,1,14.21,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(25,54,1,1,14.20,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(26,53,1,1,14.19,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(27,51,1,1,14.18,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(28,50,1,1,14.17,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(29,48,1,1,14.16,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(30,47,1,1,14.15,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(31,45,1,1,14.14,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(32,44,1,1,14.13,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(33,2,1,1,14.11,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(34,1,1,1,14.11,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(35,180,1,1,14.45,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(36,179,1,1,14.44,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(37,177,1,1,14.43,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(38,176,1,1,14.42,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(39,174,1,1,14.41,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(40,173,1,1,14.40,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(41,171,1,1,14.39,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(42,170,1,1,14.38,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(43,168,1,1,14.37,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(44,167,1,1,14.36,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(45,165,1,1,14.35,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(46,164,1,1,14.34,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(47,162,1,1,14.32,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(48,161,1,1,14.32,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(49,159,1,1,14.30,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(50,158,1,1,14.30,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(51,156,1,1,14.28,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(52,155,1,1,14.28,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(53,153,1,1,14.26,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(54,152,1,1,14.25,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(55,150,1,1,14.24,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(56,149,1,1,14.23,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(57,147,1,1,14.22,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(58,146,1,1,14.21,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(59,144,1,1,14.20,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(60,143,1,1,14.19,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(61,141,1,1,14.18,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(62,140,1,1,14.17,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(63,138,1,1,14.16,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(64,137,1,1,14.15,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(65,135,1,1,14.14,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(66,134,1,1,14.13,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(67,132,1,1,14.11,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44'),(68,131,1,1,14.11,'BAIXA','{\"filtros\":\"compativel\",\"palavras_encontradas\":[\"servi?os\"],\"detalhes\":[{\"termo\":\"servi?os\",\"peso\":2,\"ocorrencias\":1,\"incremento\":14}]}',NULL,'2026-04-01 20:27:44');
/*!40000 ALTER TABLE `correspondencias` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `exportacoes`
--
-- WHERE:  empresa_id=1

LOCK TABLES `exportacoes` WRITE;
/*!40000 ALTER TABLE `exportacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `exportacoes` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `favoritos`
--
-- WHERE:  empresa_id=1

LOCK TABLES `favoritos` WRITE;
/*!40000 ALTER TABLE `favoritos` DISABLE KEYS */;
INSERT INTO `favoritos` VALUES (13,1,77,'PROPOSTA',NULL,'2026-04-01 20:27:57',NULL),(9,1,86,'EM_ANALISE',NULL,'2026-03-31 21:47:02','2026-04-01 20:37:57'),(8,1,87,'ENCERRADO',NULL,'2026-03-31 21:26:47','2026-04-01 19:50:56'),(3,1,89,'ENCERRADO',NULL,'2026-03-31 20:29:56','2026-04-01 19:50:44'),(2,1,90,'ENCERRADO',NULL,'2026-03-31 20:24:58','2026-04-01 19:50:18'),(10,1,131,'ENCERRADO',NULL,'2026-04-01 09:59:17','2026-04-01 20:00:32'),(12,1,177,'ENCERRADO',NULL,'2026-04-01 19:50:27','2026-04-01 19:50:50'),(11,1,180,'ENCERRADO','[Resultado proposta] VENCEDORA em 01/04/2026.','2026-04-01 10:04:11','2026-04-01 10:06:20');
/*!40000 ALTER TABLE `favoritos` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `favorito_status_historico`
--
-- WHERE:  empresa_id=1

LOCK TABLES `favorito_status_historico` WRITE;
/*!40000 ALTER TABLE `favorito_status_historico` DISABLE KEYS */;
INSERT INTO `favorito_status_historico` VALUES (9,8,1,NULL,'FAVORITO',2,'decisao_oportunidade','2026-03-31 21:26:47'),(10,2,1,'EM_ANALISE','ENCERRADO',2,'pipeline_status','2026-03-31 21:33:22'),(11,9,1,NULL,'PROPOSTA',2,'decisao_oportunidade','2026-03-31 21:47:02'),(13,2,1,'ENCERRADO','PROPOSTA',2,'decisao_oportunidade','2026-03-31 22:50:24'),(14,10,1,NULL,'PROPOSTA',2,'decisao_oportunidade','2026-04-01 09:59:17'),(15,11,1,NULL,'PROPOSTA',2,'decisao_oportunidade','2026-04-01 10:04:11'),(16,11,1,'PROPOSTA','ENCERRADO',2,'proposta_resultado','2026-04-01 10:06:20'),(17,2,1,'PROPOSTA','ENCERRADO',1,'decisao_oportunidade','2026-04-01 19:50:18'),(18,12,1,NULL,'EM_ANALISE',1,'decisao_oportunidade','2026-04-01 19:50:27'),(19,3,1,'PROPOSTA','ENCERRADO',1,'decisao_oportunidade','2026-04-01 19:50:44'),(20,12,1,'EM_ANALISE','ENCERRADO',1,'decisao_oportunidade','2026-04-01 19:50:50'),(21,8,1,'FAVORITO','ENCERRADO',1,'decisao_oportunidade','2026-04-01 19:50:56'),(22,9,1,'PROPOSTA','ENCERRADO',1,'decisao_oportunidade','2026-04-01 19:51:00'),(23,10,1,'PROPOSTA','ENCERRADO',1,'pipeline_status','2026-04-01 20:00:32'),(24,13,1,NULL,'PROPOSTA',1,'decisao_oportunidade','2026-04-01 20:27:57'),(25,9,1,'ENCERRADO','PROPOSTA',1,'decisao_oportunidade','2026-04-01 20:37:32'),(26,9,1,'PROPOSTA','EM_ANALISE',1,'pipeline_status','2026-04-01 20:37:57');
/*!40000 ALTER TABLE `favorito_status_historico` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `favorito_tarefas`
--
-- WHERE:  empresa_id=1

LOCK TABLES `favorito_tarefas` WRITE;
/*!40000 ALTER TABLE `favorito_tarefas` DISABLE KEYS */;
INSERT INTO `favorito_tarefas` VALUES (5,2,1,'Conferir aderencia tecnica e documental do edital',NULL,NULL,NULL,'2026-04-02','CONCLUIDA',1,'2026-03-31 20:32:54','2026-03-31 20:24:58','2026-03-31 20:32:54'),(6,2,1,'Definir estrategia comercial e margem da proposta',NULL,NULL,NULL,'2026-04-04','CONCLUIDA',2,'2026-03-31 20:33:06','2026-03-31 20:24:58','2026-03-31 20:33:06'),(7,2,1,'Produzir proposta e anexar documentos obrigatorios',NULL,NULL,NULL,'2026-04-05','CONCLUIDA',3,'2026-04-01 10:02:51','2026-03-31 20:24:58','2026-04-01 10:02:51'),(9,2,1,'Total por g?nero','teste','paula fernanda lima',NULL,'2026-03-31','CONCLUIDA',4,'2026-04-01 10:02:55','2026-03-31 20:28:56','2026-04-01 10:02:55'),(11,2,1,'Revisar e protocolar submissao final',NULL,NULL,NULL,'2026-04-07','CONCLUIDA',5,'2026-04-01 10:03:01','2026-03-31 20:32:28','2026-04-01 10:03:01'),(12,3,1,'Conferir aderencia tecnica e documental do edital',NULL,NULL,NULL,'2026-04-03','PENDENTE',1,NULL,'2026-03-31 20:32:33',NULL),(13,3,1,'Definir estrategia comercial e margem da proposta',NULL,NULL,NULL,'2026-04-05','PENDENTE',2,NULL,'2026-03-31 20:32:33',NULL),(14,3,1,'Produzir proposta e anexar documentos obrigatorios',NULL,NULL,NULL,'2026-04-08','PENDENTE',3,NULL,'2026-03-31 20:32:33',NULL),(15,3,1,'Revisar e protocolar submissao final',NULL,NULL,NULL,'2026-04-11','PENDENTE',4,NULL,'2026-03-31 20:32:33',NULL),(35,9,1,'Conferir aderencia tecnica e documental do edital',NULL,NULL,NULL,'2026-04-02','PENDENTE',1,NULL,'2026-03-31 21:47:02',NULL),(36,9,1,'Definir estrategia comercial e margem da proposta',NULL,NULL,NULL,'2026-04-04','PENDENTE',2,NULL,'2026-03-31 21:47:02',NULL),(37,9,1,'Produzir proposta e anexar documentos obrigatorios',NULL,NULL,NULL,'2026-04-06','PENDENTE',3,NULL,'2026-03-31 21:47:02',NULL),(38,9,1,'Revisar e protocolar submissao final',NULL,NULL,NULL,'2026-04-08','PENDENTE',4,NULL,'2026-03-31 21:47:02',NULL),(39,10,1,'Conferir aderencia tecnica e documental do edital',NULL,NULL,NULL,'2026-04-03','PENDENTE',1,NULL,'2026-04-01 09:59:17',NULL),(40,10,1,'Definir estrategia comercial e margem da proposta',NULL,NULL,NULL,'2026-04-05','PENDENTE',2,NULL,'2026-04-01 09:59:17',NULL),(41,10,1,'Produzir proposta e anexar documentos obrigatorios',NULL,NULL,NULL,'2026-04-07','PENDENTE',3,NULL,'2026-04-01 09:59:17',NULL),(42,10,1,'Revisar e protocolar submissao final',NULL,NULL,NULL,'2026-04-09','PENDENTE',4,NULL,'2026-04-01 09:59:17',NULL),(43,11,1,'Conferir aderencia tecnica e documental do edital',NULL,NULL,NULL,'2026-04-03','PENDENTE',1,NULL,'2026-04-01 10:04:11',NULL),(44,11,1,'Definir estrategia comercial e margem da proposta',NULL,NULL,NULL,'2026-04-05','PENDENTE',2,NULL,'2026-04-01 10:04:11',NULL),(45,11,1,'Produzir proposta e anexar documentos obrigatorios',NULL,NULL,NULL,'2026-04-06','PENDENTE',3,NULL,'2026-04-01 10:04:11',NULL),(46,11,1,'Revisar e protocolar submissao final',NULL,NULL,NULL,'2026-04-08','PENDENTE',4,NULL,'2026-04-01 10:04:11',NULL),(47,12,1,'Conferir aderencia tecnica e documental do edital',NULL,NULL,NULL,'2026-04-03','PENDENTE',1,NULL,'2026-04-01 19:50:27',NULL),(48,12,1,'Definir estrategia comercial e margem da proposta',NULL,NULL,NULL,'2026-04-06','PENDENTE',2,NULL,'2026-04-01 19:50:27',NULL),(49,12,1,'Produzir proposta e anexar documentos obrigatorios',NULL,NULL,NULL,'2026-04-08','PENDENTE',3,NULL,'2026-04-01 19:50:27',NULL),(50,12,1,'Revisar e protocolar submissao final',NULL,NULL,NULL,'2026-04-10','PENDENTE',4,NULL,'2026-04-01 19:50:27',NULL),(51,13,1,'Conferir aderencia tecnica e documental do edital',NULL,NULL,NULL,'2026-04-03','PENDENTE',1,NULL,'2026-04-01 20:27:57',NULL),(52,13,1,'Definir estrategia comercial e margem da proposta',NULL,NULL,NULL,'2026-04-05','PENDENTE',2,NULL,'2026-04-01 20:27:57',NULL),(53,13,1,'Produzir proposta e anexar documentos obrigatorios',NULL,NULL,NULL,'2026-04-07','PENDENTE',3,NULL,'2026-04-01 20:27:57',NULL),(54,13,1,'Revisar e protocolar submissao final',NULL,NULL,NULL,'2026-04-09','PENDENTE',4,NULL,'2026-04-01 20:27:57',NULL);
/*!40000 ALTER TABLE `favorito_tarefas` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `palavras_chave`
--
-- WHERE:  empresa_id=1

LOCK TABLES `palavras_chave` WRITE;
/*!40000 ALTER TABLE `palavras_chave` DISABLE KEYS */;
INSERT INTO `palavras_chave` VALUES (3,1,1,'servi?os',2,'servi?os',1,'2026-03-31 20:08:57',NULL),(4,1,2,'engenharia',2,NULL,1,'2026-04-01 13:36:32',NULL);
/*!40000 ALTER TABLE `palavras_chave` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `perfis_monitoramento`
--
-- WHERE:  empresa_id=1

LOCK TABLES `perfis_monitoramento` WRITE;
/*!40000 ALTER TABLE `perfis_monitoramento` DISABLE KEYS */;
INSERT INTO `perfis_monitoramento` VALUES (1,1,'Fernanda','[\"PA\",\"SP\",\"CE\"]','[\"preg?o eletr?nico\",\"concorrencia\"]','[\"?rg?o publico\"]',1000.00,1000000.00,'DIARIO',1,'2026-03-31 12:05:18','2026-03-31 20:09:37'),(2,1,'Paula','[\"PA\",\"SP\",\"CE\"]','[\"preg?o eletr?nico\",\"ampla concorr?ncia\"]','[\"prefeitura\",\"governo do estado\"]',1000.00,500000.00,'DIARIO',1,'2026-03-31 20:08:37','2026-04-01 13:36:11'),(3,1,'Paula Fernanda','[\"CE\",\"PA\",\"SP\",\"RJ\"]','[\"concorr?ncia\"]','[\"prefeitura\"]',1000.00,500000.00,'SEMANAL',1,'2026-03-31 21:40:59',NULL);
/*!40000 ALTER TABLE `perfis_monitoramento` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `propostas_execucao`
--
-- WHERE:  empresa_id=1

LOCK TABLES `propostas_execucao` WRITE;
/*!40000 ALTER TABLE `propostas_execucao` DISABLE KEYS */;
INSERT INTO `propostas_execucao` VALUES (2,2,1,'ENVIADA','Proposta Tecnica e Comercial - Edital PNCP/2026/0050 - Orgao Publico Exemplo 50','Esta proposta foi preparada para o edital PNCP/2026/0050 do Orgao Publico Exemplo 50, modalidade CONCORRENCIA. O score atual de aderencia e 14,45, indicando potencial competitivo para submissao. Prazo de referencia: 2026-04-07 18:00:00.','Estrategia sugerida: foco em mitigar riscos de baixa aderencia e decidir go/no-go com criterio financeiro. Estruturar revisoes internas em marcos curtos ate 07/04/2026 e definir responsaveis por cada bloco da submissao.','Escopo preliminar da proposta:\r\n- Atendimento integral aos requisitos do edital PNCP/2026/0050.\r\n- Estrutura de execucao adequada ao orgao Orgao Publico Exemplo 50.\r\n- Planejamento de entrega e suporte para a UF CE.','Diferenciais propostos:\r\n- Governanca de projeto com checkpoints formais.\r\n- Rastreabilidade documental e plano de conformidade.\r\n- Equipe alocada com responsaveis definidos por etapa.','Cronograma macro baseado no checklist:\r\n- Conferir aderencia tecnica e documental do edital (prazo: 2026-04-02)\r\n- Definir estrategia comercial e margem da proposta (prazo: 2026-04-04)\r\n- Produzir proposta e anexar documentos obrigatorios (prazo: 2026-04-05)\r\n- Total por g?nero (prazo: 2026-03-31)\r\n- Revisar e protocolar submissao final (prazo: 2026-04-07)','Risco principal monitorado: compressao de prazo ate 07/04/2026. Mitigar com revisoes antecipadas e controle de pendencias documentais.',112500.00,'Rascunho gerado automaticamente pelo assistente. Ajuste conforme estrategia comercial.',1,2,1,'2026-03-31 21:07:58','2026-04-01 20:09:03'),(3,11,1,'ENVIADA','Proposta Tecnica e Comercial - Edital PNCP/2026/0050 - Orgao Publico Exemplo 50','Esta proposta foi preparada para o edital PNCP/2026/0050 do Orgao Publico Exemplo 50, modalidade CONCORRENCIA. O score atual de aderencia e 14,45, indicando potencial competitivo para submissao. Prazo de referencia: 2026-04-08 18:00:00.','Estrategia sugerida: foco em mitigar riscos de baixa aderencia e decidir go/no-go com criterio financeiro. Estruturar revisoes internas em marcos curtos ate 08/04/2026 e definir responsaveis por cada bloco da submissao.','Escopo preliminar da proposta:\r\n- Atendimento integral aos requisitos do edital PNCP/2026/0050.\r\n- Estrutura de execucao adequada ao orgao Orgao Publico Exemplo 50.\r\n- Planejamento de entrega e suporte para a UF CE.','Diferenciais propostos:\r\n- Governanca de projeto com checkpoints formais.\r\n- Rastreabilidade documental e plano de conformidade.\r\n- Equipe alocada com responsaveis definidos por etapa.','Cronograma macro baseado no checklist:\r\n- Conferir aderencia tecnica e documental do edital (prazo: 2026-04-03)\r\n- Definir estrategia comercial e margem da proposta (prazo: 2026-04-05)\r\n- Produzir proposta e anexar documentos obrigatorios (prazo: 2026-04-06)\r\n- Revisar e protocolar submissao final (prazo: 2026-04-08)','Risco principal monitorado: compressao de prazo ate 08/04/2026. Mitigar com revisoes antecipadas e controle de pendencias documentais.',112500.00,'Rascunho gerado automaticamente pelo assistente. Ajuste conforme estrategia comercial.',1,2,2,'2026-04-01 10:04:36','2026-04-01 10:05:45'),(4,13,1,'RASCUNHO','Proposta Tecnica e Comercial - Edital PNCP/2026/0037 - Orgao Publico Exemplo 37','Esta proposta foi preparada para o edital PNCP/2026/0037 do Orgao Publico Exemplo 37, modalidade CONCORRENCIA. O score atual de aderencia e 14,36, indicando potencial competitivo para submissao. Prazo de referencia: 2026-04-09 18:00:00.','Estrategia sugerida: foco em mitigar riscos de baixa aderencia e decidir go/no-go com criterio financeiro. Estruturar revisoes internas em marcos curtos ate 09/04/2026 e definir responsaveis por cada bloco da submissao.','Escopo preliminar da proposta:\r\n- Atendimento integral aos requisitos do edital PNCP/2026/0037.\r\n- Estrutura de execucao adequada ao orgao Orgao Publico Exemplo 37.\r\n- Planejamento de entrega e suporte para a UF SP.','Diferenciais propostos:\r\n- Governanca de projeto com checkpoints formais.\r\n- Rastreabilidade documental e plano de conformidade.\r\n- Equipe alocada com responsaveis definidos por etapa.','Cronograma macro baseado no checklist:\r\n- Conferir aderencia tecnica e documental do edital (prazo: 2026-04-03)\r\n- Definir estrategia comercial e margem da proposta (prazo: 2026-04-05)\r\n- Produzir proposta e anexar documentos obrigatorios (prazo: 2026-04-07)\r\n- Revisar e protocolar submissao final (prazo: 2026-04-09)','Risco principal monitorado: compressao de prazo ate 09/04/2026. Mitigar com revisoes antecipadas e controle de pendencias documentais.',89750.00,'Rascunho gerado automaticamente pelo assistente. Ajuste conforme estrategia comercial.',1,1,1,'2026-04-01 20:28:26','2026-04-01 20:36:52'),(5,9,1,'ENVIADA','Proposta Tecnica e Comercial - Edital PNCP/2026/0046 - Orgao Publico Exemplo 46','Esta proposta foi preparada para o edital PNCP/2026/0046 do Orgao Publico Exemplo 46, modalidade CONCORRENCIA. O score atual de aderencia e 14,42, indicando potencial competitivo para submissao. Prazo de referencia: 2026-04-08 18:00:00.','Estrategia sugerida: foco em mitigar riscos de baixa aderencia e decidir go/no-go com criterio financeiro. Estruturar revisoes internas em marcos curtos ate 08/04/2026 e definir responsaveis por cada bloco da submissao.','Escopo preliminar da proposta:\r\n- Atendimento integral aos requisitos do edital PNCP/2026/0046.\r\n- Estrutura de execucao adequada ao orgao Orgao Publico Exemplo 46.\r\n- Planejamento de entrega e suporte para a UF CE.','Diferenciais propostos:\r\n- Governanca de projeto com checkpoints formais.\r\n- Rastreabilidade documental e plano de conformidade.\r\n- Equipe alocada com responsaveis definidos por etapa.','Cronograma macro baseado no checklist:\r\n- Conferir aderencia tecnica e documental do edital (prazo: 2026-04-02)\r\n- Definir estrategia comercial e margem da proposta (prazo: 2026-04-04)\r\n- Produzir proposta e anexar documentos obrigatorios (prazo: 2026-04-06)\r\n- Revisar e protocolar submissao final (prazo: 2026-04-08)','Risco principal monitorado: compressao de prazo ate 08/04/2026. Mitigar com revisoes antecipadas e controle de pendencias documentais.',105500.00,'Rascunho gerado automaticamente pelo assistente. Ajuste conforme estrategia comercial.',1,1,1,'2026-04-01 20:38:03','2026-04-01 20:38:48');
/*!40000 ALTER TABLE `propostas_execucao` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `proposta_aprovacoes`
--
-- WHERE:  empresa_id=1

LOCK TABLES `proposta_aprovacoes` WRITE;
/*!40000 ALTER TABLE `proposta_aprovacoes` DISABLE KEYS */;
INSERT INTO `proposta_aprovacoes` VALUES (2,2,1,'APROVADA',NULL,2,'2026-03-31 21:36:49',2,'2026-03-31 21:37:00','aprovado','2026-03-31 21:36:49','2026-03-31 21:37:00'),(3,2,1,'APROVADA','proposta revisada ....',2,'2026-03-31 21:49:29',2,'2026-03-31 21:49:54','aprovado','2026-03-31 21:49:29','2026-03-31 21:49:54'),(4,2,1,'APROVADA','pronta para valida??o comercial',2,'2026-03-31 21:52:01',2,'2026-03-31 21:52:13','aprovado','2026-03-31 21:52:01','2026-03-31 21:52:13'),(5,3,1,'APROVADA','proposta validada pela equipe t?cnica',2,'2026-04-01 10:05:09',2,'2026-04-01 10:05:25','APROVADO','2026-04-01 10:05:09','2026-04-01 10:05:25'),(6,2,1,'APROVADA','proposta aceita',1,'2026-04-01 13:47:53',1,'2026-04-01 13:47:59','aprovado','2026-04-01 13:47:53','2026-04-01 13:47:59'),(7,2,1,'APROVADA','proposta teste',1,'2026-04-01 20:06:19',1,'2026-04-01 20:06:26','aprovado','2026-04-01 20:06:19','2026-04-01 20:06:26'),(8,2,1,'APROVADA','proposta teste',1,'2026-04-01 20:07:01',1,'2026-04-01 20:07:23','aprovado','2026-04-01 20:07:01','2026-04-01 20:07:23'),(9,4,1,'PENDENTE','proposta teste',1,'2026-04-01 20:29:41',NULL,NULL,NULL,'2026-04-01 20:29:41',NULL),(10,5,1,'APROVADA','solicitar aprova??o',1,'2026-04-01 20:38:14',1,'2026-04-01 20:38:32','registara decis?o aprovado','2026-04-01 20:38:14','2026-04-01 20:38:32');
/*!40000 ALTER TABLE `proposta_aprovacoes` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `proposta_submissoes`
--
-- WHERE:  empresa_id=1

LOCK TABLES `proposta_submissoes` WRITE;
/*!40000 ALTER TABLE `proposta_submissoes` DISABLE KEYS */;
INSERT INTO `proposta_submissoes` VALUES (2,2,1,2,'PORTAL',NULL,'2026-03-31 21:34:00',112500.00,NULL,NULL,'2026-03-31 21:36:06'),(3,2,1,2,'PORTAL',NULL,'2026-03-31 21:49:00',112500.00,NULL,'teste','2026-03-31 21:50:58'),(4,2,1,2,'PORTAL',NULL,'2026-03-31 21:52:00',112500.00,NULL,'aprovado','2026-03-31 21:52:29'),(5,3,1,2,'EMAIL','001','2026-04-01 10:05:00',112500.00,NULL,'APROVADO','2026-04-01 10:05:45'),(6,2,1,1,'EMAIL',NULL,'2026-04-01 13:47:00',112500.00,NULL,'teste aprovado','2026-04-01 13:48:11'),(7,2,1,1,'PORTAL',NULL,'2026-04-01 20:08:00',112500.00,NULL,NULL,'2026-04-01 20:09:03'),(8,5,1,1,'PORTAL',NULL,'2026-04-01 20:38:00',105500.00,NULL,NULL,'2026-04-01 20:38:48');
/*!40000 ALTER TABLE `proposta_submissoes` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `proposta_resultados`
--
-- WHERE:  empresa_id=1

LOCK TABLES `proposta_resultados` WRITE;
/*!40000 ALTER TABLE `proposta_resultados` DISABLE KEYS */;
INSERT INTO `proposta_resultados` VALUES (3,2,1,2,'EM_JULGAMENTO','2026-03-31 22:03:00',NULL,NULL,'teste',NULL,'teste','2026-03-31 22:04:00'),(4,2,1,2,'VENCEDORA','2026-03-31 22:04:00',NULL,NULL,'aprovado',NULL,'aprovado','2026-03-31 22:04:36'),(5,3,1,2,'VENCEDORA','2026-04-01 10:05:00',NULL,NULL,'APROVADO',NULL,'APROVADO','2026-04-01 10:06:20'),(6,5,1,1,'EM_JULGAMENTO','2026-04-01 20:38:00',NULL,NULL,NULL,NULL,NULL,'2026-04-01 20:38:57');
/*!40000 ALTER TABLE `proposta_resultados` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `proposta_alerta_notificacoes`
--
-- WHERE:  empresa_id=1

LOCK TABLES `proposta_alerta_notificacoes` WRITE;
/*!40000 ALTER TABLE `proposta_alerta_notificacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposta_alerta_notificacoes` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `proposta_alerta_playbooks`
--
-- WHERE:  empresa_id=1

LOCK TABLES `proposta_alerta_playbooks` WRITE;
/*!40000 ALTER TABLE `proposta_alerta_playbooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposta_alerta_playbooks` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `proposta_alerta_playbook_tarefas`
--
-- WHERE:  empresa_id=1

LOCK TABLES `proposta_alerta_playbook_tarefas` WRITE;
/*!40000 ALTER TABLE `proposta_alerta_playbook_tarefas` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposta_alerta_playbook_tarefas` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `proposta_alerta_playbook_eventos`
--
-- WHERE:  empresa_id=1

LOCK TABLES `proposta_alerta_playbook_eventos` WRITE;
/*!40000 ALTER TABLE `proposta_alerta_playbook_eventos` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposta_alerta_playbook_eventos` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `proposta_alerta_aprendizado_regras`
--
-- WHERE:  empresa_id=1

LOCK TABLES `proposta_alerta_aprendizado_regras` WRITE;
/*!40000 ALTER TABLE `proposta_alerta_aprendizado_regras` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposta_alerta_aprendizado_regras` ENABLE KEYS */;
UNLOCK TABLES;
-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u696029111_editais
-- ------------------------------------------------------
-- Server version	11.4.9-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `tokens_recuperacao_senha`
--
-- WHERE:  usuario_id IN (SELECT id FROM usuarios WHERE empresa_id=1)

