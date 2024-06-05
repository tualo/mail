DELIMITER;
SET FOREIGN_KEY_CHECKS=0;
INSERT INTO `ds` (`allowform`,`class_name`,`combined`,`default_pagesize`,`displayfield`,`existsreal`,`listselectionmodel`,`listviewbaseclass`,`modelbaseclass`,`phpexporter`,`phpexporterfilename`,`searchfield`,`showactionbtn`,`sortfield`,`table_name`,`title`) VALUES ('1','Unklassifiziert','0','1000','id','1','cellmodel','Tualo.DataSets.ListView','Tualo.DataSets.model.Basic','XlsxWriter','mail_config {DATE} {TIME}','smtp_host','1','id','mail_config','Mail-Configuration') ON DUPLICATE KEY UPDATE `allowform`=values(`allowform`),`class_name`=values(`class_name`),`combined`=values(`combined`),`default_pagesize`=values(`default_pagesize`),`displayfield`=values(`displayfield`),`existsreal`=values(`existsreal`),`listselectionmodel`=values(`listselectionmodel`),`listviewbaseclass`=values(`listviewbaseclass`),`modelbaseclass`=values(`modelbaseclass`),`phpexporter`=values(`phpexporter`),`phpexporterfilename`=values(`phpexporterfilename`),`searchfield`=values(`searchfield`),`showactionbtn`=values(`showactionbtn`),`sortfield`=values(`sortfield`),`table_name`=values(`table_name`),`title`=values(`title`); 
INSERT IGNORE INTO `ds_column` (`character_maximum_length`,`character_set_name`,`column_key`,`column_name`,`column_type`,`data_type`,`existsreal`,`fieldtype`,`is_nullable`,`is_primary`,`privileges`,`table_name`,`writeable`) VALUES ('36','utf8mb3','PRI','id','varchar(36)','varchar','1','','NO','1','select,insert,update,references','mail_config','1') ; 
INSERT IGNORE INTO `ds_column` (`character_maximum_length`,`character_set_name`,`column_key`,`column_name`,`column_type`,`data_type`,`existsreal`,`fieldtype`,`is_nullable`,`privileges`,`table_name`,`writeable`) VALUES ('255','utf8mb3','','smtp_auth','varchar(255)','varchar','1','','NO','select,insert,update,references','mail_config','1') ; 
INSERT IGNORE INTO `ds_column` (`character_maximum_length`,`character_set_name`,`column_key`,`column_name`,`column_type`,`data_type`,`existsreal`,`fieldtype`,`is_nullable`,`privileges`,`table_name`,`writeable`) VALUES ('255','utf8mb3','','smtp_host','varchar(255)','varchar','1','','NO','select,insert,update,references','mail_config','1') ; 
INSERT IGNORE INTO `ds_column` (`column_key`,`column_name`,`column_type`,`data_type`,`existsreal`,`fieldtype`,`is_nullable`,`numeric_precision`,`numeric_scale`,`privileges`,`table_name`,`writeable`) VALUES ('','smtp_no_autotls','tinyint(4)','tinyint','1','','YES','3','0','select,insert,update,references','mail_config','1') ; 
INSERT IGNORE INTO `ds_column` (`column_key`,`column_name`,`column_type`,`data_type`,`existsreal`,`fieldtype`,`is_nullable`,`numeric_precision`,`numeric_scale`,`privileges`,`table_name`,`writeable`) VALUES ('','smtp_no_certcheck','tinyint(4)','tinyint','1','','YES','3','0','select,insert,update,references','mail_config','1') ; 
INSERT IGNORE INTO `ds_column` (`character_maximum_length`,`character_set_name`,`column_key`,`column_name`,`column_type`,`data_type`,`existsreal`,`fieldtype`,`is_nullable`,`privileges`,`table_name`,`writeable`) VALUES ('255','utf8mb3','','smtp_pass','varchar(255)','varchar','1','','NO','select,insert,update,references','mail_config','1') ; 
INSERT IGNORE INTO `ds_column` (`character_maximum_length`,`character_set_name`,`column_key`,`column_name`,`column_type`,`data_type`,`existsreal`,`fieldtype`,`is_nullable`,`privileges`,`table_name`,`writeable`) VALUES ('255','utf8mb3','','smtp_secure','varchar(255)','varchar','1','','NO','select,insert,update,references','mail_config','1') ; 
INSERT IGNORE INTO `ds_column` (`character_maximum_length`,`character_set_name`,`column_key`,`column_name`,`column_type`,`data_type`,`existsreal`,`fieldtype`,`is_nullable`,`privileges`,`table_name`,`writeable`) VALUES ('255','utf8mb3','','smtp_user','varchar(255)','varchar','1','','NO','select,insert,update,references','mail_config','1') ; 
INSERT IGNORE INTO `ds_column_list_label` (`active`,`column_name`,`editor`,`filterstore`,`flex`,`grouped`,`hidden`,`label`,`language`,`listfiltertype`,`position`,`renderer`,`summaryrenderer`,`summarytype`,`table_name`,`xtype`) VALUES ('1','id','','','1.00','0','0','ID','DE','','0','','','','mail_config','gridcolumn') ; 
INSERT IGNORE INTO `ds_column_list_label` (`active`,`column_name`,`editor`,`filterstore`,`flex`,`grouped`,`hidden`,`label`,`language`,`listfiltertype`,`position`,`renderer`,`summaryrenderer`,`summarytype`,`table_name`,`xtype`) VALUES ('1','smtp_auth','','','1.00','0','0','Authentication','DE','','5','','','','mail_config','gridcolumn') ; 
INSERT IGNORE INTO `ds_column_list_label` (`active`,`column_name`,`editor`,`filterstore`,`flex`,`grouped`,`hidden`,`label`,`language`,`listfiltertype`,`position`,`renderer`,`summaryrenderer`,`summarytype`,`table_name`,`xtype`) VALUES ('1','smtp_host','','','1.00','0','0','Host','DE','','1','','','','mail_config','gridcolumn') ; 
INSERT IGNORE INTO `ds_column_list_label` (`active`,`column_name`,`editor`,`filterstore`,`flex`,`grouped`,`hidden`,`label`,`language`,`listfiltertype`,`position`,`renderer`,`summaryrenderer`,`summarytype`,`table_name`,`xtype`) VALUES ('1','smtp_no_autotls','','','1.00','0','0','NO-Auto-TLS','DE','','7','','','','mail_config','gridcolumn') ; 
INSERT IGNORE INTO `ds_column_list_label` (`active`,`column_name`,`editor`,`filterstore`,`flex`,`grouped`,`hidden`,`label`,`language`,`listfiltertype`,`position`,`renderer`,`summaryrenderer`,`summarytype`,`table_name`,`xtype`) VALUES ('1','smtp_no_certcheck','','','1.00','0','0','Cert-Check','DE','','6','','','','mail_config','gridcolumn') ; 
INSERT IGNORE INTO `ds_column_list_label` (`active`,`column_name`,`editor`,`filterstore`,`flex`,`grouped`,`hidden`,`label`,`language`,`listfiltertype`,`position`,`renderer`,`summaryrenderer`,`summarytype`,`table_name`,`xtype`) VALUES ('1','smtp_pass','','','1.00','0','0','Password','DE','','3','','','','mail_config','gridcolumn') ; 
INSERT IGNORE INTO `ds_column_list_label` (`active`,`column_name`,`editor`,`filterstore`,`flex`,`grouped`,`hidden`,`label`,`language`,`listfiltertype`,`position`,`renderer`,`summaryrenderer`,`summarytype`,`table_name`,`xtype`) VALUES ('1','smtp_secure','','','1.00','0','0','Secure','DE','','4','','','','mail_config','gridcolumn') ; 
INSERT IGNORE INTO `ds_column_list_label` (`active`,`column_name`,`editor`,`filterstore`,`flex`,`grouped`,`hidden`,`label`,`language`,`listfiltertype`,`position`,`renderer`,`summaryrenderer`,`summarytype`,`table_name`,`xtype`) VALUES ('1','smtp_user','','','1.00','0','0','User','DE','','2','','','','mail_config','gridcolumn') ; 
INSERT IGNORE INTO `ds_column_form_label` (`active`,`allowempty`,`column_name`,`field_path`,`flex`,`hidden`,`label`,`language`,`position`,`table_name`,`xtype`) VALUES ('1','1','id','Allgemein/Angaben','1.00','0','ID','DE','0','mail_config','textfield') ; 
INSERT IGNORE INTO `ds_column_form_label` (`active`,`allowempty`,`column_name`,`field_path`,`flex`,`hidden`,`label`,`language`,`position`,`table_name`,`xtype`) VALUES ('1','1','smtp_auth','Allgemein/SMTP','1.00','0','Authentication','DE','5','mail_config','textfield') ; 
INSERT IGNORE INTO `ds_column_form_label` (`active`,`allowempty`,`column_name`,`field_path`,`flex`,`hidden`,`label`,`language`,`position`,`table_name`,`xtype`) VALUES ('1','0','smtp_host','Allgemein/SMTP','1.00','0','Host','DE','1','mail_config','textfield') ; 
INSERT IGNORE INTO `ds_column_form_label` (`active`,`allowempty`,`column_name`,`field_path`,`flex`,`hidden`,`label`,`language`,`position`,`table_name`,`xtype`) VALUES ('1','1','smtp_no_autotls','Allgemein/SMTP','1.00','0','NO-Auto-TLS','DE','7','mail_config','checkbox') ; 
INSERT IGNORE INTO `ds_column_form_label` (`active`,`allowempty`,`column_name`,`field_path`,`flex`,`hidden`,`label`,`language`,`position`,`table_name`,`xtype`) VALUES ('1','1','smtp_no_certcheck','Allgemein/SMTP','1.00','0','Cert-Check','DE','6','mail_config','checkbox') ; 
INSERT IGNORE INTO `ds_column_form_label` (`active`,`allowempty`,`column_name`,`field_path`,`flex`,`hidden`,`label`,`language`,`position`,`table_name`,`xtype`) VALUES ('1','1','smtp_pass','Allgemein/SMTP','1.00','0','Password','DE','3','mail_config','textfield') ; 
INSERT IGNORE INTO `ds_column_form_label` (`active`,`allowempty`,`column_name`,`field_path`,`flex`,`hidden`,`label`,`language`,`position`,`table_name`,`xtype`) VALUES ('1','1','smtp_secure','Allgemein/SMTP','1.00','0','Secure','DE','4','mail_config','textfield') ; 
INSERT IGNORE INTO `ds_column_form_label` (`active`,`allowempty`,`column_name`,`field_path`,`flex`,`hidden`,`label`,`language`,`position`,`table_name`,`xtype`) VALUES ('1','0','smtp_user','Allgemein/SMTP','1.00','0','User','DE','2','mail_config','textfield') ; 
INSERT IGNORE INTO `ds_dropdownfields` (`displayfield`,`filterconfig`,`idfield`,`name`,`table_name`) VALUES ('id','','id','id','mail_config') ; 
INSERT IGNORE INTO `ds_access` (`append`,`delete`,`read`,`role`,`table_name`,`write`) VALUES ('0','0','0','_default_','mail_config','0') ; 
INSERT IGNORE INTO `ds_access` (`append`,`delete`,`read`,`role`,`table_name`,`write`) VALUES ('1','1','1','administration','mail_config','1') ; 
SET FOREIGN_KEY_CHECKS=1;