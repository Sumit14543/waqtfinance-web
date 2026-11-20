-- Admin panel performance indexes (2026-11-19).
-- Speeds up dashboard date counts, list pages, filters and global search on tables that only had a primary key.
-- Safe to run on a live database: adds indexes only, changes no data. Run once; MySQL errors with
-- "Duplicate key name" if an index already exists, which means that line can be skipped.
-- Take a backup first, and run during low traffic (each ALTER rebuilds the table's index).

ALTER TABLE apply_loan_applications ADD INDEX idx_ala_created_at (created_at), ADD INDEX idx_ala_mobile (mobile), ADD INDEX idx_ala_loan_type (loan_type);
ALTER TABLE loan_applied_master    ADD INDEX idx_lam_created_at (created_at), ADD INDEX idx_lam_mobile (mobile_number), ADD INDEX idx_lam_pan (pancard_number), ADD INDEX idx_lam_loan_type (loan_type);
ALTER TABLE loan_leads             ADD INDEX idx_ll_created_at (created_at), ADD INDEX idx_ll_mobile (mobile);
ALTER TABLE contact_messages       ADD INDEX idx_cm_created_at (created_at);
ALTER TABLE loan_applications      ADD INDEX idx_la_created_at (created_at), ADD INDEX idx_la_status_created (status, created_at);
ALTER TABLE partner_registrations  ADD INDEX idx_pr_created_at (created_at), ADD INDEX idx_pr_status (status);
