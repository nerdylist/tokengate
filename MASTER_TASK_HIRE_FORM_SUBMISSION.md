# Master Task: Fix Hire Form Submission

## Overview
Update the hire.php form submission to properly send and process all form fields from the frontend through the API to the database.

## Problem Statement
The hire.php form collects these fields:
- title ✓ (working)
- category ✓ (working)
- description ✓ (working)
- skills ⚠️ (collected but sent as empty array)
- budget ✓ (working)
- payment_type ✗ (collected but NOT sent)
- estimated_hours ✗ (collected but NOT sent)
- deadline ✓ (working)
- spots ✗ (collected but NOT sent)
- location ✗ (collected but NOT sent)
- remote_ok ✗ (collected but NOT sent)

Additionally:
- The database lacks columns for the new fields
- Skills are sent as empty array instead of skill names
- Backend doesn't handle skill names (expects IDs)
- Validation is incomplete

## Solution Architecture

### Layer 1: Database Schema
Add missing columns to support all form fields.

### Layer 2: Frontend (JavaScript)
Update form submission to include all collected fields in API request.

### Layer 3: API Endpoint
Accept and validate new fields, pass them to controller.

### Layer 4: Controller
Process new fields, handle skill name-to-ID conversion, save to database.

## Task Breakdown

### Task 001: Database Migration
**File**: Create `/Volumes/Crucial/SITES/redot/database/migrations/007_add_bounty_fields.sql`
**Details**: See TASK_001_DATABASE_MIGRATION.md
**Estimated Time**: 5 minutes
**Dependencies**: None
**Assigned To**: Migration Agent

### Task 002: Update Frontend JavaScript
**File**: Modify `/Volumes/Crucial/SITES/redot/assets/js/hire.js`
**Details**: See TASK_002_UPDATE_HIRE_JS.md
**Estimated Time**: 10 minutes
**Dependencies**: None (can run parallel with Task 001)
**Assigned To**: JS Agent

### Task 003: Update API Endpoint
**File**: Modify `/Volumes/Crucial/SITES/redot/api/bounties.php`
**Details**: See TASK_003_UPDATE_API_BOUNTIES.md
**Estimated Time**: 5 minutes
**Dependencies**: None (can run parallel with Tasks 001-002)
**Assigned To**: API Agent

### Task 004: Update Controller
**File**: Modify `/Volumes/Crucial/SITES/redot/controllers/BountyController.php`
**Details**: See TASK_004_UPDATE_BOUNTY_CONTROLLER.md
**Estimated Time**: 20 minutes
**Dependencies**: None (can run parallel with Tasks 001-003)
**Assigned To**: Controller Agent

### Task 005: Review and Test
**Files**: Review all changes
**Details**: See TASK_005_REVIEW_AND_TEST.md
**Estimated Time**: 30 minutes
**Dependencies**: Tasks 001-004 must be complete
**Assigned To**: QA Agent

## Execution Plan

### Phase 1: Parallel Development (Tasks 001-004)
All four tasks can be executed in parallel by different agents as they modify different files with no conflicts.

### Phase 2: Review (Task 005)
After all coding tasks are complete, review agent verifies:
1. Code quality and correctness
2. Syntax validity
3. Logic consistency
4. Error handling

### Phase 3: Integration Testing
1. Apply database migration
2. Test form submission end-to-end
3. Verify data in database
4. Check skill creation and association
5. Test error cases

### Phase 4: Final Approval
Boss reviews test results and approves deployment.

## Files Modified

1. **Created**: `/Volumes/Crucial/SITES/redot/database/migrations/007_add_bounty_fields.sql`
2. **Modified**: `/Volumes/Crucial/SITES/redot/assets/js/hire.js`
3. **Modified**: `/Volumes/Crucial/SITES/redot/api/bounties.php`
4. **Modified**: `/Volumes/Crucial/SITES/redot/controllers/BountyController.php`

## Success Criteria

1. ✓ All form fields are sent in API request
2. ✓ API accepts and validates all fields
3. ✓ Controller processes and saves all fields
4. ✓ Skills are converted from names to IDs
5. ✓ New skills are created automatically
6. ✓ Database stores all bounty information
7. ✓ Form validation works correctly
8. ✓ Error messages are clear and helpful
9. ✓ No breaking changes to existing functionality
10. ✓ Code follows project standards (PHP, JS, no 3rd party libs)

## Risk Mitigation

### Risk: Database migration fails
**Mitigation**: Test migration on backup database first

### Risk: Breaking existing bounty creation
**Mitigation**: All changes use default values for backward compatibility

### Risk: Skill creation conflicts
**Mitigation**: Case-insensitive matching and slug uniqueness checks

### Risk: Frontend validation bypass
**Mitigation**: Backend validation is comprehensive and independent

## Rollback Plan
If deployment fails:
1. Revert file changes using git
2. Keep database columns (they won't break anything)
3. Investigate and fix issues
4. Redeploy when ready

## Testing Checklist

- [ ] Syntax validation (PHP, JavaScript)
- [ ] Database migration applied successfully
- [ ] Form submits without errors
- [ ] All fields saved to database
- [ ] Skills created and associated correctly
- [ ] Validation works for required fields
- [ ] Error messages display properly
- [ ] No console errors
- [ ] No server errors in logs

## Notes

- Payment type defaults to 'fixed'
- Spots defaults to 1
- Remote OK defaults to 1 (true)
- Skills are optional
- New skills get category_id 8 (OTHER)
- Skill matching is case-insensitive
- Slug conflicts resolved with timestamp suffix

## Timeline

- **Parallel Development**: 20 minutes
- **Review**: 30 minutes
- **Testing**: 30 minutes
- **Total Estimated Time**: 80 minutes

## Agent Assignments

1. **Migration Agent** → Task 001
2. **JS Agent** → Task 002
3. **API Agent** → Task 003
4. **Controller Agent** → Task 004
5. **QA Agent** → Task 005
6. **Boss** → Final review and approval
