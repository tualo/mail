delimiter ; 
create table if not exists `mail_async_send` (
    `id` varchar(36) NOT NULL DEFAULT '',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_sending` datetime  DEFAULT NULL,
    `send_at` datetime  DEFAULT NULL,
    `maildata` json DEFAULT NULL    ,

    `error_message` text DEFAULT NULL,

    PRIMARY KEY (`id`)
);
