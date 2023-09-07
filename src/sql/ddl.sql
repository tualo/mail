create table if not exists `mail_config` (
    `id` varchar(36) NOT NULL DEFAULT '',
    `smtp_host` varchar(255) NOT NULL,
    `smtp_auth` varchar(255) NOT NULL,
    `smtp_user` varchar(255) NOT NULL,
    `smtp_secure` varchar(255) NOT NULL,
    `smtp_no_autotls` tinyint default 0,
    `smtp_no_certcheck`  tinyint default 0,
    PRIMARY KEY (`id`)
);

