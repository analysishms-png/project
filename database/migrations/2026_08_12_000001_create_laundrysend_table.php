<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TABLE laundrysend (
            sn          BIGINT(20)  NOT NULL AUTO_INCREMENT,
            propertyid  INT(11)     NOT NULL,
            vno         INT(15)     NOT NULL,
            vdate       DATE        NOT NULL,
            senddate    DATE        NOT NULL,
            roomno      VARCHAR(20) DEFAULT NULL,
            guestname   VARCHAR(50) DEFAULT NULL,
            itemname    VARCHAR(50) DEFAULT NULL,
            quantity    DECIMAL(10,2) DEFAULT 1.00,
            rate        DECIMAL(12,2) DEFAULT 0.00,
            amount      DECIMAL(12,2) DEFAULT 0.00,
            laundrytype VARCHAR(10) DEFAULT NULL,
            status      VARCHAR(15) DEFAULT 'Sent',
            remarks     VARCHAR(100) DEFAULT NULL,
            u_name      INT(11)     DEFAULT NULL,
            u_entdt     DATETIME    DEFAULT NULL,
            u_ae        VARCHAR(1)  DEFAULT NULL,
            UNIQUE KEY sn (sn),
            PRIMARY KEY (propertyid, vno)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS laundrysend');
    }
};
