-- FishTrackPro Database Dump for MySQL
-- Generated: 2024-09-10
-- Version: 1.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- CORE TABLES
-- =============================================

-- Users table
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `avatar` varchar(512) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(191) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `subscription_type` enum('free','pro','premium') DEFAULT 'free',
  `subscription_expires_at` timestamp NULL DEFAULT NULL,
  `bonus_balance` int(11) DEFAULT 0,
  `last_bonus_earned_at` timestamp NULL DEFAULT NULL,
  `language` varchar(5) DEFAULT 'ru',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_subscription_type_index` (`subscription_type`),
  KEY `users_language_index` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OAuth identities
CREATE TABLE `oauth_identities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(191) NOT NULL,
  `provider_id` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oauth_identities_provider_provider_id_unique` (`provider`,`provider_id`),
  KEY `oauth_identities_user_id_foreign` (`user_id`),
  CONSTRAINT `oauth_identities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Groups table
CREATE TABLE `groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `avatar` varchar(512) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groups_created_by_foreign` (`created_by`),
  CONSTRAINT `groups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group members
CREATE TABLE `group_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role` enum('member','admin','moderator') DEFAULT 'member',
  `joined_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_members_group_id_user_id_unique` (`group_id`,`user_id`),
  KEY `group_members_user_id_foreign` (`user_id`),
  CONSTRAINT `group_members_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `location_name` varchar(191) DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `status` enum('draft','published','cancelled','completed') DEFAULT 'draft',
  `organizer_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned DEFAULT NULL,
  `cover_url` varchar(512) DEFAULT NULL,
  `type` varchar(191) DEFAULT 'exhibition',
  `organizer` varchar(191) DEFAULT NULL,
  `contact_email` varchar(191) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `website` varchar(512) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(191) DEFAULT NULL,
  `region` varchar(191) DEFAULT NULL,
  `country` varchar(191) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `radius_km` int(11) DEFAULT NULL,
  `registration_start` datetime DEFAULT NULL,
  `registration_end` datetime DEFAULT NULL,
  `event_start` datetime DEFAULT NULL,
  `event_end` datetime DEFAULT NULL,
  `is_all_day` tinyint(1) DEFAULT 0,
  `current_participants` int(11) DEFAULT 0,
  `entry_fee` decimal(8,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'RUB',
  `requires_registration` tinyint(1) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 1,
  `moderation_status` enum('pending','approved','rejected','pending_review') DEFAULT 'pending',
  `moderation_result` json DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `moderated_by` bigint(20) unsigned DEFAULT NULL,
  `cover_image` varchar(512) DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `rules` text DEFAULT NULL,
  `prizes` text DEFAULT NULL,
  `schedule` text DEFAULT NULL,
  `views_count` int(11) DEFAULT 0,
  `subscribers_count` int(11) DEFAULT 0,
  `shares_count` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT NULL,
  `reviews_count` int(11) DEFAULT 0,
  `notifications_enabled` tinyint(1) DEFAULT 1,
  `reminders_enabled` tinyint(1) DEFAULT 1,
  `allow_comments` tinyint(1) DEFAULT 1,
  `allow_sharing` tinyint(1) DEFAULT 1,
  `tags` json DEFAULT NULL,
  `categories` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_organizer_id_foreign` (`organizer_id`),
  KEY `events_group_id_foreign` (`group_id`),
  KEY `events_start_at_index` (`start_at`),
  KEY `events_status_index` (`status`),
  KEY `events_type_status_index` (`type`,`status`),
  KEY `events_event_start_event_end_index` (`event_start`,`event_end`),
  KEY `events_latitude_longitude_index` (`latitude`,`longitude`),
  KEY `events_city_region_index` (`city`,`region`),
  KEY `events_moderation_status_index` (`moderation_status`),
  CONSTRAINT `events_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `events_organizer_id_foreign` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chats table (without foreign key initially)
CREATE TABLE `chats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) DEFAULT NULL,
  `type` enum('private','group','event') DEFAULT 'private',
  `group_id` bigint(20) unsigned DEFAULT NULL,
  `event_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chats_type_index` (`type`),
  KEY `chats_group_id_index` (`group_id`),
  KEY `chats_event_id_index` (`event_id`),
  CONSTRAINT `chats_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for events after events table is created
ALTER TABLE `chats` ADD CONSTRAINT `chats_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

-- Chat messages
CREATE TABLE `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_chat_id_foreign` (`chat_id`),
  KEY `chat_messages_user_id_foreign` (`user_id`),
  CONSTRAINT `chat_messages_chat_id_foreign` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event participants
CREATE TABLE `event_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_participants_event_id_user_id_unique` (`event_id`,`user_id`),
  KEY `event_participants_user_id_foreign` (`user_id`),
  CONSTRAINT `event_participants_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event subscriptions
CREATE TABLE `event_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `event_id` bigint(20) unsigned NOT NULL,
  `status` enum('subscribed','unsubscribed','hidden') DEFAULT 'subscribed',
  `notifications_enabled` tinyint(1) DEFAULT 1,
  `reminders_enabled` tinyint(1) DEFAULT 1,
  `reminder_hours_before` int(11) DEFAULT 24,
  `email_notifications` tinyint(1) DEFAULT 0,
  `push_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `is_hidden` tinyint(1) DEFAULT 0,
  `plans_to_attend` tinyint(1) DEFAULT 0,
  `last_notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_subscriptions_user_id_event_id_unique` (`user_id`,`event_id`),
  KEY `event_subscriptions_status_index` (`status`),
  KEY `event_subscriptions_is_hidden_index` (`is_hidden`),
  KEY `event_subscriptions_plans_to_attend_index` (`plans_to_attend`),
  CONSTRAINT `event_subscriptions_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event news
CREATE TABLE `event_news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(191) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `cover_image` varchar(512) DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `type` enum('announcement','update','reminder','result') DEFAULT 'announcement',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `likes_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `moderation_status` enum('pending','approved','rejected','pending_review') DEFAULT 'pending',
  `moderation_result` json DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `moderated_by` bigint(20) unsigned DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_news_event_id_index` (`event_id`),
  KEY `event_news_user_id_index` (`user_id`),
  KEY `event_news_status_index` (`status`),
  KEY `event_news_published_at_index` (`published_at`),
  KEY `event_news_is_pinned_index` (`is_pinned`),
  KEY `event_news_moderation_status_index` (`moderation_status`),
  KEY `event_news_scheduled_at_index` (`scheduled_at`),
  CONSTRAINT `event_news_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_news_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- FISHING TABLES
-- =============================================

-- Catch records
CREATE TABLE `catch_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `fish_type` varchar(191) NOT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `length` decimal(8,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `photo_url` varchar(512) DEFAULT NULL,
  `video_url` varchar(512) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `location_name` varchar(191) DEFAULT NULL,
  `caught_at` datetime NOT NULL,
  `weather_conditions` varchar(191) DEFAULT NULL,
  `water_temperature` decimal(5,2) DEFAULT NULL,
  `air_temperature` decimal(5,2) DEFAULT NULL,
  `wind_speed` decimal(5,2) DEFAULT NULL,
  `wind_direction` varchar(10) DEFAULT NULL,
  `pressure` decimal(7,2) DEFAULT NULL,
  `humidity` decimal(5,2) DEFAULT NULL,
  `visibility` decimal(5,2) DEFAULT NULL,
  `tackle_used` varchar(191) DEFAULT NULL,
  `bait_used` varchar(191) DEFAULT NULL,
  `depth` decimal(8,2) DEFAULT NULL,
  `water_clarity` enum('clear','slightly_murky','murky','very_murky') DEFAULT NULL,
  `water_level` enum('low','normal','high','flooding') DEFAULT NULL,
  `current_speed` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `likes_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `moderation_status` enum('pending','approved','rejected','pending_review') DEFAULT 'pending',
  `moderation_result` json DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `moderated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catch_records_user_id_foreign` (`user_id`),
  KEY `catch_records_caught_at_index` (`caught_at`),
  KEY `catch_records_fish_type_index` (`fish_type`),
  KEY `catch_records_is_public_index` (`is_public`),
  KEY `catch_records_moderation_status_index` (`moderation_status`),
  CONSTRAINT `catch_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Points (fishing locations)
CREATE TABLE `points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `lat` decimal(10,7) NOT NULL,
  `lng` decimal(10,7) NOT NULL,
  `location_name` varchar(191) DEFAULT NULL,
  `water_type` enum('river','lake','pond','sea','ocean','reservoir','canal','stream') DEFAULT NULL,
  `depth_range` varchar(50) DEFAULT NULL,
  `accessibility` enum('easy','moderate','difficult','boat_only') DEFAULT NULL,
  `parking_available` tinyint(1) DEFAULT 0,
  `facilities` json DEFAULT NULL,
  `fish_species` json DEFAULT NULL,
  `best_seasons` json DEFAULT NULL,
  `best_times` json DEFAULT NULL,
  `tackle_recommendations` text DEFAULT NULL,
  `safety_notes` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_date` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `visits_count` int(11) DEFAULT 0,
  `moderation_status` enum('pending','approved','rejected','pending_review') DEFAULT 'pending',
  `moderation_result` json DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `moderated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `points_user_id_foreign` (`user_id`),
  KEY `points_lat_lng_index` (`lat`,`lng`),
  KEY `points_water_type_index` (`water_type`),
  KEY `points_is_public_index` (`is_public`),
  KEY `points_is_verified_index` (`is_verified`),
  KEY `points_moderation_status_index` (`moderation_status`),
  CONSTRAINT `points_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SOCIAL FEATURES
-- =============================================

-- Catch likes
CREATE TABLE `catch_likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `catch_record_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catch_likes_catch_record_id_user_id_unique` (`catch_record_id`,`user_id`),
  KEY `catch_likes_user_id_foreign` (`user_id`),
  CONSTRAINT `catch_likes_catch_record_id_foreign` FOREIGN KEY (`catch_record_id`) REFERENCES `catch_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `catch_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catch comments
CREATE TABLE `catch_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `catch_record_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` text NOT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  `moderation_status` enum('pending','approved','rejected','pending_review') DEFAULT 'pending',
  `moderation_result` json DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `moderated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catch_comments_catch_record_id_foreign` (`catch_record_id`),
  KEY `catch_comments_user_id_foreign` (`user_id`),
  KEY `catch_comments_is_approved_index` (`is_approved`),
  KEY `catch_comments_moderation_status_index` (`moderation_status`),
  CONSTRAINT `catch_comments_catch_record_id_foreign` FOREIGN KEY (`catch_record_id`) REFERENCES `catch_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `catch_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Follows
CREATE TABLE `follows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `follower_id` bigint(20) unsigned NOT NULL,
  `following_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `follows_follower_id_following_id_unique` (`follower_id`,`following_id`),
  KEY `follows_following_id_foreign` (`following_id`),
  CONSTRAINT `follows_follower_id_foreign` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `follows_following_id_foreign` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SUBSCRIPTION & PAYMENT TABLES
-- =============================================

-- Subscriptions
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `plan_type` enum('pro','premium') NOT NULL,
  `status` enum('active','cancelled','expired','pending') DEFAULT 'pending',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_user_id_foreign` (`user_id`),
  KEY `subscriptions_plan_type_index` (`plan_type`),
  KEY `subscriptions_status_index` (`status`),
  CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'RUB',
  `payment_method` enum('card','paypal','yandex_money','qiwi','webmoney','bank_transfer') NOT NULL,
  `payment_provider` enum('stripe','paypal','yandex_kassa','tinkoff','sberbank') NOT NULL,
  `transaction_id` varchar(191) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled','refunded') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_user_id_foreign` (`user_id`),
  KEY `payments_subscription_id_foreign` (`subscription_id`),
  KEY `payments_status_index` (`status`),
  KEY `payments_payment_method_index` (`payment_method`),
  CONSTRAINT `payments_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- BONUS SYSTEM
-- =============================================

-- Bonus transactions
CREATE TABLE `bonus_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` enum('earned','spent','expired','refunded') NOT NULL,
  `amount` int(11) NOT NULL,
  `description` varchar(191) NOT NULL,
  `reference_type` varchar(191) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonus_transactions_user_id_foreign` (`user_id`),
  KEY `bonus_transactions_type_index` (`type`),
  KEY `bonus_transactions_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  CONSTRAINT `bonus_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SMART WATCH INTEGRATION
-- =============================================

-- Biometric data
CREATE TABLE `biometric_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `session_id` bigint(20) unsigned DEFAULT NULL,
  `timestamp` timestamp NOT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `hrv` decimal(8,2) DEFAULT NULL,
  `stress_level` decimal(5,2) DEFAULT NULL,
  `mood_index` decimal(5,2) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `steps` int(11) DEFAULT NULL,
  `calories` int(11) DEFAULT NULL,
  `activity_level` enum('low','moderate','high','very_high') DEFAULT NULL,
  `accelerometer_x` decimal(10,6) DEFAULT NULL,
  `accelerometer_y` decimal(10,6) DEFAULT NULL,
  `accelerometer_z` decimal(10,6) DEFAULT NULL,
  `gyroscope_x` decimal(10,6) DEFAULT NULL,
  `gyroscope_y` decimal(10,6) DEFAULT NULL,
  `gyroscope_z` decimal(10,6) DEFAULT NULL,
  `casts` int(11) DEFAULT NULL,
  `reel_turns` int(11) DEFAULT NULL,
  `meters_retrieved` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `biometric_data_user_id_foreign` (`user_id`),
  KEY `biometric_data_session_id_foreign` (`session_id`),
  KEY `biometric_data_timestamp_index` (`timestamp`),
  CONSTRAINT `biometric_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fishing sessions
CREATE TABLE `fishing_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `start_time` timestamp NOT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `hand_preference` enum('left','right') DEFAULT NULL,
  `total_casts` int(11) DEFAULT 0,
  `total_meters` decimal(8,2) DEFAULT 0.00,
  `avg_heart_rate` decimal(5,2) DEFAULT NULL,
  `max_heart_rate` int(11) DEFAULT NULL,
  `avg_hrv` decimal(8,2) DEFAULT NULL,
  `avg_stress_level` decimal(5,2) DEFAULT NULL,
  `avg_mood_index` decimal(5,2) DEFAULT NULL,
  `gps_track` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `status` enum('active','paused','completed','cancelled') DEFAULT 'active',
  `summary_phrase` text DEFAULT NULL,
  `emotion_graph` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fishing_sessions_user_id_foreign` (`user_id`),
  KEY `fishing_sessions_start_time_index` (`start_time`),
  KEY `fishing_sessions_is_active_index` (`is_active`),
  KEY `fishing_sessions_status_index` (`status`),
  CONSTRAINT `fishing_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for biometric_data after fishing_sessions is created
ALTER TABLE `biometric_data` ADD CONSTRAINT `biometric_data_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `fishing_sessions` (`id`) ON DELETE CASCADE;

-- =============================================
-- REFERENCE DATA TABLES
-- =============================================

-- Fish species
CREATE TABLE `fish_species` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `scientific_name` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `habitat` text DEFAULT NULL,
  `size_range` varchar(50) DEFAULT NULL,
  `weight_range` varchar(50) DEFAULT NULL,
  `season` json DEFAULT NULL,
  `best_time` json DEFAULT NULL,
  `tackle_recommendations` text DEFAULT NULL,
  `bait_recommendations` text DEFAULT NULL,
  `image_url` varchar(512) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fish_species_name_unique` (`name`),
  KEY `fish_species_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fishing methods
CREATE TABLE `fishing_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `equipment_needed` text DEFAULT NULL,
  `technique` text DEFAULT NULL,
  `best_for` text DEFAULT NULL,
  `difficulty_level` enum('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
  `image_url` varchar(512) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fishing_methods_name_unique` (`name`),
  KEY `fishing_methods_difficulty_level_index` (`difficulty_level`),
  KEY `fishing_methods_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SYSTEM TABLES
-- =============================================

-- Notifications
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(191) NOT NULL,
  `title` varchar(191) NOT NULL,
  `body` text DEFAULT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  KEY `notifications_type_index` (`type`),
  KEY `notifications_is_read_index` (`is_read`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personal access tokens (Laravel Sanctum)
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Insert sample fish species
INSERT INTO `fish_species` (`name`, `scientific_name`, `description`, `habitat`, `size_range`, `weight_range`, `season`, `best_time`, `tackle_recommendations`, `bait_recommendations`, `is_active`) VALUES
('Щука', 'Esox lucius', 'Хищная рыба семейства щуковых', 'Реки, озера, пруды', '50-120 см', '1-15 кг', '["spring", "summer", "autumn"]', '["morning", "evening"]', 'Спиннинг, жерлицы', 'Блесны, воблеры, живец', 1),
('Окунь', 'Perca fluviatilis', 'Хищная рыба семейства окуневых', 'Реки, озера, водохранилища', '15-50 см', '0.2-2 кг', '["spring", "summer", "autumn", "winter"]', '["morning", "evening"]', 'Спиннинг, поплавочная удочка', 'Мотыль, червь, блесны', 1),
('Карп', 'Cyprinus carpio', 'Пресноводная рыба семейства карповых', 'Пруды, озера, реки', '30-100 см', '1-20 кг', '["spring", "summer", "autumn"]', '["morning", "evening"]', 'Фидер, поплавочная удочка', 'Кукуруза, бойлы, червь', 1);

-- Insert sample fishing methods
INSERT INTO `fishing_methods` (`name`, `description`, `equipment_needed`, `technique`, `best_for`, `difficulty_level`, `is_active`) VALUES
('Спиннинг', 'Ловля на искусственные приманки', 'Спиннинг, катушка, леска, приманки', 'Заброс и проводка приманки', 'Хищные рыбы', 'intermediate', 1),
('Поплавочная удочка', 'Ловля с поплавком', 'Удочка, леска, поплавок, грузила, крючки', 'Заброс и ожидание поклевки', 'Мирные рыбы', 'beginner', 1),
('Фидер', 'Донная ловля с кормушкой', 'Фидерное удилище, катушка, кормушки', 'Заброс кормушки и ожидание', 'Крупные мирные рыбы', 'intermediate', 1);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
