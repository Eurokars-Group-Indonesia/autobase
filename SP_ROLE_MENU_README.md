# Stored Procedures for Role Menu Management

## Overview
This document describes the stored procedures used for managing role menus in the system.

## Stored Procedures

### 1. sp_add_ms_role_menu
Creates a new role menu assignment.

**Parameters:**
- `p_role_id` (VARCHAR(50)) - Role ID
- `p_menu_id` (VARCHAR(50)) - Menu ID
- `p_user_id` (VARCHAR(50)) - User ID who creates the record
- `p_unique_id` (VARCHAR(50)) - Unique UUID for the record

**Return Codes:**
- `200` - Success
- `404` - User not found / Role not found / Menu not found
- `409` - Role menu already exists

**Example Usage:**
```sql
CALL sp_add_ms_role_menu('MRO01-00001', 'MNU00001', 'USR00001', 'uuid-here');
```

**Response:**
```json
{
    "return_code": 200,
    "return_message": "Success",
    "role_menu_id": "MRM01-00001",
    "role_id": "MRO01-00001",
    "menu_id": "MNU00001",
    "is_active": "1",
    "created_by": "USR00001",
    "created_date": "2026-01-30 17:00:00"
}
```

---

### 2. sp_update_ms_role_menu
Updates an existing role menu or creates a new one if it doesn't exist.

**Parameters:**
- `p_role_id` (VARCHAR(50)) - Role ID
- `p_menu_id` (VARCHAR(50)) - Menu ID
- `p_is_active` (ENUM('0', '1')) - Active status (1 = Active, 0 = Inactive)
- `p_user_id` (VARCHAR(50)) - User ID who updates the record
- `p_unique_id` (VARCHAR(50)) - Unique UUID for the record

**Return Codes:**
- `200` - Success
- `404` - User not found

**Example Usage:**
```sql
-- Deactivate a role menu
CALL sp_update_ms_role_menu('MRO01-00001', 'MNU00001', '0', 'USR00001', 'uuid-here');

-- Reactivate a role menu
CALL sp_update_ms_role_menu('MRO01-00001', 'MNU00001', '1', 'USR00001', 'uuid-here');
```

**Response:**
```json
{
    "return_code": 200,
    "return_message": "Success",
    "role_menu_id": "MRM01-00001",
    "role_id": "MRO01-00001",
    "menu_id": "MNU00001",
    "is_active": "0",
    "updated_by": "USR00001",
    "updated_date": "2026-01-30 17:00:00"
}
```

---

## Usage in Laravel

### Creating a Role Menu
```php
$result = DB::select('CALL sp_add_ms_role_menu(?, ?, ?, ?)', [
    $roleId,
    $menuId,
    auth()->id(),
    Str::uuid()
]);

if ($result[0]->return_code == 200) {
    // Success
} elseif ($result[0]->return_code == 409) {
    // Already exists
} else {
    // Error
}
```

### Updating a Role Menu
```php
$result = DB::select('CALL sp_update_ms_role_menu(?, ?, ?, ?, ?)', [
    $roleId,
    $menuId,
    '1', // or '0' to deactivate
    auth()->id(),
    Str::uuid()
]);

if ($result[0]->return_code == 200) {
    // Success
}
```

---

## Migration Files
- `2026_01_30_060001_create_sp_add_ms_role_menu_procedure.php` - Creates sp_add_ms_role_menu
- `2026_01_29_060045_create_sp_update_ms_role_menu_procedure.php` - Creates sp_update_ms_role_menu

---

## Notes
- Both procedures automatically generate role_menu_id using `fn_gen_number('MRM01')`
- The `sp_update_ms_role_menu` will create a new record if it doesn't exist
- All operations are logged in the audit trail via triggers
- The procedures validate user, role, and menu existence before performing operations
