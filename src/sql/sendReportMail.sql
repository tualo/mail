delimiter //
CREATE OR REPLACE PROCEDURE `sendReportMail`( IN  request JSON )
BEGIN 
    SET @tabellenzusatz = JSON_Value( request, "$.mail_record.tabellenzusatz" );

    SET @id= JSON_Value( request, "$.mail_record.id" );
    SET @mailto= JSON_Value( request, "$.mailto" );
    SET @SQL = CONCAT( "insert into  blg_mail_", @tabellenzusatz, " (id,sendtime,mailto) values (", @id ,",now(),", quote(@mailto) ,")  " );
    PREPARE stmt FROM @SQL;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //    