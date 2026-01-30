# Stored Procedures for Role Permission Management

## Overview
This document describes the stored procedures used for managing role permissions in the system.

## Stored Procedures

### 1. sp_add_ms_role_permission
Creates a new role permission assignment.

**Parameters:**
- `p_role_id` (VARCHAR(50)) - Role ID
- `p_permission_id` (VARCHAR(50)) - Permission ID
- `p_user_id` (VARCHAR(50)) - User ID who creates the record
- `p_unique_id` (VARCHAR(50)) - Unique UUID for the record

**Return Codes:**
- `200` - Success
- `404` - User not found / Role not found / Permission not found
- `409` - Role permission already exists

**Example Usage:**
```sql
CALL sp_add_ms_role_permission('MRO01-00001', 'PRM00001', 'USR00001', 'uuid-here');
```

**Response:**
```json
{
    "return_code": 200,
    "return_message": "Success",
    "role_permission_id": "MRP01-00001",
    "role_id": "MRO01-00001",
    "permission_id": "PRM00001",
    "is_active": "1",
    "created_by": "USR00001",
    "created_date": "2026-01-30 17:00:00"
}
```

---

### 2. sp_update_ms_role_permission
Updates an existing role permission or creates a new one if it doesn't exist.

**Parameters:**
- `p_role_id` (VARCHAR(50)) - Role ID
- `p_permission_id` (VARCHAR(50)) - Permission ID
- `p_is_active` (ENUM('0', '1')) - Active status (1 = Active, 0 = Inactive)
- `p_user_id` (VARCHAR(50)) - User ID who updates the record
- `p_unique_id` (VARCHAR(50)) - Unique UUID for the record

**Return Codes:**
- `200` - Success
- `404` - User not found

**Example Usage:**
```sql
-- Deactivate a role permission
CALL sp_update_ms_role_permission('MRO01-00001', 'PRM00001', '0', 'USR00001', 'uuid-here');

-- Reactivate a role permission
CALL sp_update_ms_role_permission('MRO01-00001', 'PRM00001', '1', 'USR00001', 'uuid-here');
```

**Response:**
```json
{
    "return_code": 200,
    "return_message": "Success",
    "role_permission_id": "MRP01-00001",
    "role_id": "MRO01-00001",
    "permission_id": "PRM00001",
    "is_active": "0",
    "updated_by": "USR00001",
    "updated_date": "2026-01-30 17:00:00"
}
```

---

## Usage in Laravel

### Creating a Role Permission
```php
$result = DB::select('CALL sp_add_ms_role_permission(?, ?, ?, ?)', [
    $roleId,
    $permissionId,
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

### Updating a Role Permission
```php
$result = DB::select('CALL sp_update_ms_role_permission(?, ?, ?, ?, ?)', [
    $roleId,
    $permissionId,
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
- `2026_01_30_060000_create_sp_add_ms_role_permission_procedure.php` - Creates sp_add_ms_role_permission
- `2026_01_29_060030_create_sp_update_ms_role_permission_procedure.php` - Creates sp_update_ms_role_permission

---

## Notes
- Both procedures automatically generate role_permission_id using `fn_gen_number('MRP01')`
- The `sp_update_ms_role_permission` will create a new record if it doesn't exist
- All operations are logged in the audit trail via triggers
- The procedures validate user, role, and permission existence before performing operations
