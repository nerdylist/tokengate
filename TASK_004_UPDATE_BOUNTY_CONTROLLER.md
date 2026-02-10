# Task 004: Update BountyController to Handle New Fields and Skill Names

## Objective
Update the BountyController to process the new bounty fields and handle skill names (converting them to IDs or creating new skills as needed).

## File to Modify
`/Volumes/Crucial/SITES/redot/controllers/BountyController.php`

## Changes Required

### 1. Update Validation in create() Method (line 136)

Current code:
```php
if (empty($data['title']) || empty($data['description']) || empty($data['category_id'])) {
    throw new Exception("Missing required fields: title, description, category_id");
}
```

Update to:
```php
if (empty($data['title']) || empty($data['description']) || empty($data['category_id']) || (empty($data['budget_min']) && empty($data['budget_max']))) {
    throw new Exception("Missing required fields: title, description, category_id, budget");
}
```

### 2. Add New Fields to $bountyData Array (around line 143-154)

Current code:
```php
$bountyData = [
    'user_id' => Auth::id(),
    'category_id' => $data['category_id'],
    'title' => $data['title'],
    'description' => $data['description'],
    'budget_min' => $data['budget_min'] ?? null,
    'budget_max' => $data['budget_max'] ?? null,
    'deadline' => $data['deadline'] ?? null,
    'status' => 'open',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];
```

Update to:
```php
$bountyData = [
    'user_id' => Auth::id(),
    'category_id' => $data['category_id'],
    'title' => $data['title'],
    'description' => $data['description'],
    'budget_min' => $data['budget_min'] ?? null,
    'budget_max' => $data['budget_max'] ?? null,
    'deadline' => $data['deadline'] ?? null,
    'payment_type' => $data['payment_type'] ?? 'fixed',
    'estimated_hours' => $data['estimated_hours'] ?? null,
    'spots' => $data['spots'] ?? 1,
    'location' => $data['location'] ?? null,
    'remote_ok' => $data['remote_ok'] ?? 1,
    'status' => 'open',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];
```

### 3. Update Skills Handling (around line 164-167)

Current code:
```php
// Associate skills if provided
if (!empty($data['skills']) && is_array($data['skills'])) {
    $this->associateBountySkills($bountyId, $data['skills']);
}
```

Update to:
```php
// Associate skills if provided
if (!empty($data['skills']) && is_array($data['skills'])) {
    $skillIds = $this->resolveSkillIds($data['skills']);
    if (!empty($skillIds)) {
        $this->associateBountySkills($bountyId, $skillIds);
    }
}
```

### 4. Add New Method: resolveSkillIds() (add after associateBountySkills method)

```php
/**
 * Resolve skill names to IDs (creating new skills if needed)
 * @param array $skills Array of skill names or IDs
 * @return array Array of skill IDs
 */
private function resolveSkillIds($skills)
{
    $skillIds = [];

    foreach ($skills as $skill) {
        // If it's already a numeric ID, use it
        if (is_numeric($skill)) {
            $skillIds[] = (int)$skill;
            continue;
        }

        // Otherwise, treat it as a skill name
        $skillName = trim($skill);
        if (empty($skillName)) {
            continue;
        }

        // Check if skill exists
        $existingSkill = $this->db->queryOne(
            "SELECT id FROM skills WHERE LOWER(name) = LOWER(?)",
            [$skillName]
        );

        if ($existingSkill) {
            $skillIds[] = $existingSkill['id'];
        } else {
            // Create new skill with default category (8 = OTHER)
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $skillName));
            $slug = trim($slug, '-');

            // Check if slug exists, make it unique if needed
            $slugExists = $this->db->queryOne(
                "SELECT id FROM skills WHERE slug = ?",
                [$slug]
            );

            if ($slugExists) {
                $slug = $slug . '-' . time();
            }

            $this->db->execute(
                "INSERT INTO skills (name, slug, category_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
                [$skillName, $slug, 8, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
            );

            $skillIds[] = $this->db->lastInsertId();
        }
    }

    return $skillIds;
}
```

### 5. Update $allowedFields in update() Method (line 208)

Current code:
```php
$allowedFields = ['title', 'description', 'category_id', 'budget_min', 'budget_max', 'deadline', 'status'];
```

Update to:
```php
$allowedFields = ['title', 'description', 'category_id', 'budget_min', 'budget_max', 'deadline', 'status', 'payment_type', 'estimated_hours', 'spots', 'location', 'remote_ok'];
```

## Success Criteria
- New bounty fields are saved to database
- Skill names are converted to IDs
- New skills are created automatically with category_id 8 (OTHER)
- Duplicate skill names are handled (case-insensitive matching)
- Slug conflicts are resolved by appending timestamp
- Validation includes budget check
- Update method allows editing new fields
- No breaking changes to existing functionality

## Error Handling
- Empty skill names should be skipped
- Invalid skill data should not prevent bounty creation
- Database errors should be caught and wrapped in meaningful exceptions

## Notes
- Category ID 8 corresponds to "OTHER" category
- Skills are case-insensitive (PHP vs php should match)
- Slug generation should handle special characters
