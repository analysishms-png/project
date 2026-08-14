<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TABLE laundryreceive (
            sn          BIGINT(20)  NOT NULL AUTO_INCREMENT,
            propertyid  INT(11)     NOT NULL,
            vno         INT(15)     NOT NULL,
            vdate       DATE        NOT NULL,
            receivedate DATE        NOT NULL,
            roomno      VARCHAR(20) DEFAULT NULL,
            itemname    VARCHAR(50) DEFAULT NULL,
            quantity    DECIMAL(10,2) DEFAULT 0.00,
            damagedqty  DECIMAL(10,2) DEFAULT 0.00,
            missingqty  DECIMAL(10,2) DEFAULT 0.00,
            receivedby  VARCHAR(50) DEFAULT NULL,
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
        DB::statement('DROP TABLE IF EXISTS laundryreceive');
    }
};
