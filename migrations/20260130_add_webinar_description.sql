-- Migration: add description column to webinars
ALTER TABLE webinars
  ADD COLUMN description TEXT NULL AFTER title;

-- Run with: mysql -u <user> -p <database> < migrations/20260130_add_webinar_description.sql
