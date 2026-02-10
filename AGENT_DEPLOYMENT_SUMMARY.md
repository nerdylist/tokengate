# Agent Deployment Summary
## Hire Form Submission Fix - Task Delegation Complete

### Status: READY FOR AGENT DEPLOYMENT

## Overview
I have analyzed the hire.php form submission issue and created comprehensive task specifications for a team of coding agents to implement the fixes.

## Problem Identified
The hire.php form collects 11 fields, but only 6 are being sent to the API and saved to the database. Additionally, the database schema is missing columns for the new fields, and skills are not being processed correctly.

## Solution Designed
A 4-layer fix addressing:
1. **Database Schema** - Add missing columns
2. **Frontend JavaScript** - Send all form fields
3. **API Endpoint** - Accept and validate new fields
4. **Backend Controller** - Process fields and handle skill name conversion

## Task Files Created

All task specification files have been created in the project root:

### 📋 MASTER_TASK_HIRE_FORM_SUBMISSION.md
Complete project overview, timeline, and coordination strategy.

### 📋 TASK_001_DATABASE_MIGRATION.md
**Agent Assignment**: Migration Agent
**File to Create**: `database/migrations/007_add_bounty_fields.sql`
**Time Estimate**: 5 minutes
**Complexity**: Low

Adds 5 columns to bounties table:
- payment_type VARCHAR(50)
- estimated_hours INTEGER
- spots INTEGER DEFAULT 1
- location VARCHAR(255)
- remote_ok INTEGER DEFAULT 1

### 📋 TASK_002_UPDATE_HIRE_JS.md
**Agent Assignment**: JavaScript Agent
**File to Modify**: `assets/js/hire.js`
**Time Estimate**: 10 minutes
**Complexity**: Low

Updates apiData object in handleBountySubmit() to include:
- All new fields from form
- Skills as array of names (not empty array)
- Proper type conversions and null handling

### 📋 TASK_003_UPDATE_API_BOUNTIES.md
**Agent Assignment**: API Agent
**File to Modify**: `api/bounties.php`
**Time Estimate**: 5 minutes
**Complexity**: Low

Updates $data array in 'create' action to extract and pass:
- All new fields from $_POST
- Appropriate default values

### 📋 TASK_004_UPDATE_BOUNTY_CONTROLLER.md
**Agent Assignment**: Controller Agent
**File to Modify**: `controllers/BountyController.php`
**Time Estimate**: 20 minutes
**Complexity**: Medium

Updates BountyController with:
- Enhanced validation (require budget)
- New fields in $bountyData array
- New method resolveSkillIds() for skill name handling
- Automatic skill creation with category_id 8 (OTHER)
- Updated $allowedFields for update() method

### 📋 TASK_005_REVIEW_AND_TEST.md
**Agent Assignment**: QA Agent
**Dependencies**: Tasks 001-004 complete
**Time Estimate**: 30 minutes
**Complexity**: Medium

Comprehensive review and testing plan:
- Code review checklist
- Syntax validation
- Database migration verification
- End-to-end testing
- Error case testing
- Browser console checks

## Agent Deployment Strategy

### Phase 1: Parallel Execution (Tasks 001-004)
Deploy 4 agents simultaneously to work on independent files:

```
┌─────────────────┐
│ Migration Agent │ → Task 001
└─────────────────┘

┌─────────────────┐
│  JS Agent       │ → Task 002
└─────────────────┘

┌─────────────────┐
│  API Agent      │ → Task 003
└─────────────────┘

┌─────────────────┐
│ Controller Agent│ → Task 004
└─────────────────┘
```

**Estimated Time**: 20 minutes (parallel execution)

### Phase 2: Review (Task 005)
Deploy QA agent after Phase 1 completion:

```
┌─────────────────┐
│   QA Agent      │ → Task 005
└─────────────────┘
```

**Estimated Time**: 30 minutes

### Phase 3: Boss Review
I will review QA agent's findings and approve deployment.

**Estimated Time**: 10 minutes

## Total Timeline
- **Phase 1**: 20 minutes (parallel)
- **Phase 2**: 30 minutes
- **Phase 3**: 10 minutes
- **Total**: 60 minutes

## Files to be Modified
1. ✏️ **Created**: `database/migrations/007_add_bounty_fields.sql`
2. ✏️ **Modified**: `assets/js/hire.js`
3. ✏️ **Modified**: `api/bounties.php`
4. ✏️ **Modified**: `controllers/BountyController.php`

## Key Implementation Details

### Default Values
- payment_type: 'fixed'
- spots: 1
- remote_ok: 1 (checked)
- estimated_hours: null (optional)
- location: null (optional)

### Skill Processing
- Skills sent as array of strings (skill names)
- Case-insensitive matching
- Automatic creation of new skills
- New skills get category_id 8 (OTHER)
- Slug conflict resolution with timestamp

### Validation Requirements
- Title required
- Description required
- Category required
- Budget required (min or max)
- Deadline required
- Spots 1-20
- Skills optional but validated if provided

## Success Criteria Checklist

- [ ] All form fields sent in API request
- [ ] API accepts all fields with validation
- [ ] Controller saves all fields to database
- [ ] Skills converted from names to IDs
- [ ] New skills auto-created
- [ ] Database columns added
- [ ] Form validation works
- [ ] Error messages clear
- [ ] No breaking changes
- [ ] All tests pass

## Risk Assessment

| Risk | Severity | Mitigation |
|------|----------|------------|
| Database migration fails | Medium | Test on backup first |
| Breaking existing bounties | High | Use default values everywhere |
| Skill creation conflicts | Low | Case-insensitive + slug checks |
| Frontend validation bypass | Low | Backend validation independent |

## Deployment Command Sequence

Once agents complete their tasks:

```bash
# 1. Apply database migration
sqlite3 /Volumes/Crucial/SITES/redot/database/redot.db < /Volumes/Crucial/SITES/redot/database/migrations/007_add_bounty_fields.sql

# 2. Verify migration
sqlite3 /Volumes/Crucial/SITES/redot/database/redot.db "PRAGMA table_info(bounties);"

# 3. Clear any caches if applicable
# (Not needed for this project - no caching layer)

# 4. Test in browser
# Navigate to: https://redot.test/hire.php
```

## Next Steps

1. **Deploy agents** to execute Tasks 001-004 in parallel
2. **Wait for completion** and collect their work
3. **Deploy QA agent** to execute Task 005
4. **Review QA results** and make final approval
5. **Apply database migration**
6. **Test in production environment**

## Notes for Agents

- ⚠️ **Do NOT add any third-party dependencies**
- ⚠️ **Follow existing code style** (PHP, vanilla JS, no frameworks)
- ⚠️ **Preserve backward compatibility** (use defaults)
- ⚠️ **Test syntax** before submitting
- ⚠️ **Handle errors gracefully**
- ⚠️ **Document assumptions** if any

## Contact for Questions

If any agent has questions or encounters blockers:
1. Refer to the detailed task specification file
2. Check existing code patterns in the file being modified
3. Consult the database schema at `database/schema.sql`
4. Escalate to Boss if still unclear

---

**Status**: ✅ Planning Complete - Ready for Agent Deployment
**Created**: 2026-02-10
**Project**: Redot (RentPeople.io)
**Task**: Fix Hire Form Submission
