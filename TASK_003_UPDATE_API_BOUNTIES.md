# Task 003: Update API Endpoint to Accept New Fields

## Objective
Update the bounties API endpoint to accept and pass through the new form fields.

## File to Modify
`/Volumes/Crucial/SITES/redot/api/bounties.php`

## Current Issue
The 'create' action (line 79-98) only extracts these fields from $_POST:
- title
- description
- category_id
- budget_min
- budget_max
- deadline
- skills

It needs to also extract:
- payment_type
- estimated_hours
- spots
- location
- remote_ok

## Changes Required

### Update the $data array in 'create' action (around line 80-88)

Current code:
```php
$data = [
    'title' => $_POST['title'] ?? '',
    'description' => $_POST['description'] ?? '',
    'category_id' => $_POST['category_id'] ?? null,
    'budget_min' => $_POST['budget_min'] ?? null,
    'budget_max' => $_POST['budget_max'] ?? null,
    'deadline' => $_POST['deadline'] ?? null,
    'skills' => $_POST['skills'] ?? []
];
```

Update to:
```php
$data = [
    'title' => $_POST['title'] ?? '',
    'description' => $_POST['description'] ?? '',
    'category_id' => $_POST['category_id'] ?? null,
    'budget_min' => $_POST['budget_min'] ?? null,
    'budget_max' => $_POST['budget_max'] ?? null,
    'deadline' => $_POST['deadline'] ?? null,
    'payment_type' => $_POST['payment_type'] ?? 'fixed',
    'estimated_hours' => $_POST['estimated_hours'] ?? null,
    'spots' => $_POST['spots'] ?? 1,
    'location' => $_POST['location'] ?? null,
    'remote_ok' => $_POST['remote_ok'] ?? 1,
    'skills' => $_POST['skills'] ?? []
];
```

## Default Values
- payment_type: 'fixed' (if not provided)
- spots: 1 (if not provided)
- remote_ok: 1 (if not provided)
- estimated_hours: null (optional)
- location: null (optional)

## Success Criteria
- All new fields are extracted from $_POST
- Appropriate default values are used
- No breaking changes to existing functionality
- Fields are passed to BountyController->create() method

## Notes
- The API already handles JSON input conversion (lines 72-76)
- No changes needed to other actions (update, delete)
- Error handling remains unchanged
