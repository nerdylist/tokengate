-- Migration: Move data from pending_skills to skills table
-- Only migrate pending skills that were approved or are still pending

-- Insert pending_skills into skills table that aren't already there
-- We check for duplicates by slug to avoid conflicts
INSERT INTO skills (name, slug, category_id, submitted_by_profile_id, status, reviewed_by_admin_id, reviewed_at, created_at, updated_at)
SELECT
    ps.name,
    ps.slug,
    ps.category_id,
    ps.requested_by_profile_id,
    ps.status,
    ps.reviewed_by_admin_id,
    ps.reviewed_at,
    ps.created_at,
    ps.updated_at
FROM pending_skills ps
WHERE ps.status IN ('pending', 'rejected')
AND NOT EXISTS (
    SELECT 1 FROM skills s WHERE s.slug = ps.slug
);

-- Note: Approved skills should already be in the skills table from the old approvePendingSkill logic
-- This migration handles pending and rejected skills that need to be preserved
