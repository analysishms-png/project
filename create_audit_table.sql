-- Create hk_audit_log table for housekeeping audit trail
CREATE TABLE IF NOT EXISTS `hk_audit_log` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `propertyid` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `user_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `record_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `old_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `new_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `u_entdt` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_propertyid` (`propertyid`),
    KEY `idx_action` (`action`),
    KEY `idx_module` (`module`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
