delimiter //
CREATE OR REPLACE PROCEDURE `sendReportMail`( IN  request JSON )
BEGIN 
    SET @tabellenzusatz = JSON_Value( request, "$.mail_record.tabellenzusatz" );
    IF @tabellenzusatz is null THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Table name is missing';
    END IF;
    SET @id= JSON_Value( request, "$.mail_record.id" );
    IF @id is null THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'ID is missing';
    END IF;
    SET @mailto= JSON_Value( request, "$.mailto" );
    IF @mailto is null THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mail-To is missing';
    END IF;
    SET @SQL = CONCAT( "insert into  blg_mail_", @tabellenzusatz, " (id,sendtime,mailto) values (", @id ,",now(),", quote(replace(@mailto,char(59),',')) ,")  " );
    IF @SQL is null THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Statement is null';
    END IF;
    PREPARE stmt FROM @SQL;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //    