# Task 002: Update hire.js to Send All Form Fields

## Objective
Update the JavaScript file to include all form fields in the API request when submitting a bounty.

## File to Modify
`/Volumes/Crucial/SITES/redot/assets/js/hire.js`

## Current Issue
The `handleBountySubmit` function currently only sends these fields:
- title
- description
- category_id
- budget_min
- budget_max
- deadline
- skills (as empty array)

But the form collects these additional fields that are NOT being sent:
- payment_type (radio button, line 149)
- estimated_hours (line 150)
- spots (line 152)
- location (line 153)
- remote_ok (checkbox, line 154)

## Changes Required

### 1. Update apiData object (around line 189-198)

Current code has:
```javascript
const apiData = {
    title: formData.title,
    description: formData.description,
    category_id: categoryMap[formData.category] || 8,
    budget_min: parseFloat(formData.budget),
    budget_max: parseFloat(formData.budget),
    deadline: formData.deadline || null,
    skills: [] // TODO: Convert skill names to IDs via API
};
```

Update to:
```javascript
const apiData = {
    title: formData.title,
    description: formData.description,
    category_id: categoryMap[formData.category] || 8,
    budget_min: parseFloat(formData.budget),
    budget_max: parseFloat(formData.budget),
    deadline: formData.deadline || null,
    payment_type: formData.paymentType,
    estimated_hours: formData.estimatedHours ? parseInt(formData.estimatedHours) : null,
    spots: parseInt(formData.spots),
    location: formData.location || null,
    remote_ok: formData.remoteOk ? 1 : 0,
    skills: skillNames
};
```

### 2. Validation Enhancement (Optional)

Consider adding validation for:
- At least one skill should be provided (skillNames.length > 0)

## Success Criteria
- All form fields are included in the API request
- Skills are sent as array of skill names (not empty array)
- Boolean remote_ok is converted to 1 or 0
- Numeric fields are properly parsed
- Null handling for optional fields
- No breaking changes to existing functionality

## Testing
After changes, test that:
1. Form submission includes all fields in the request payload
2. Skills are sent as string array from comma-separated input
3. Remote checkbox state is properly converted to 1/0
