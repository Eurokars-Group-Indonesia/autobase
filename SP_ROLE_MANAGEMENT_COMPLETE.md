# Complete Stored Procedures for Role Management

## Overview
This document provides a complete reference for all stored procedures used in role management, including role permissions and role menus.

---

## Table of Contents
1. [Role Permission Procedures](#role-permission-procedures)
2. [Role Menu Procedures](#role-menu-procedures)
3. [Quick Reference](#quick-reference)

---

## Role Permission Procedures

### sp_add_ms_role_permission
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

**Laravel Usage:**
```php
$result = DB::select('CALL sp_add_ms_role_permission(?, ?, ?, ?)', [
    $roleId,
    $permissionId,
    auth()->id(),
    Str::uuid()
]);
```

---

### sp_update_ms_role_permission
Updates an existing role permission or creates a new one if it doesn't exist.

**Parameters:**
- `p_role_id` (VARCHAR(50)) - Role ID
- `p_permission_id` (VARCHAR(50)) - Permission ID
- `p_is_active` (ENUM('0', '1')) - Active status
- `p_user_id` (VARCHAR(50)) - User ID who updates the record
- `p_unique_id` (VARCHAR(50)) - Unique UUID for the record

**Return Codes:**
- `200` - Success
- `404` - User not found

**Laravel Usage:**
```php
$result = DB::select('CALL sp_update_ms_role_permission(?, ?, ?, ?, ?)', [
    $roleId,
    $permissionId,
    '1', // or '0' to deactivate
    auth()->id(),
    Str::uuid()
]);
```

---

## Role Menu Procedures

### sp_add_ms_role_menu
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

**Laravel Usage:**
```php
$result = DB::select('CALL sp_add_ms_role_menu(?, ?, ?, ?)', [
    $roleId,
    $menuId,
    auth()->id(),
    Str::uuid()
]);
```

---

### sp_update_ms_role_menu
Updates an existing role menu or creates a new one if it doesn't exist.

**Parameters:**
- `p_role_id` (VARCHAR(50)) - Role ID
- `p_menu_id` (VARCHAR(50)) - Menu ID
- `p_is_active` (ENUM('0', '1')) - Active status
- `p_user_id` (VARCHAR(50)) - User ID who updates the record
- `p_unique_id` (VARCHAR(50)) - Unique UUID for the record

**Return Codes:**
- `200` - Success
- `404` - User not found

**Laravel Usage:**
```php
$result = DB::select('CALL sp_update_ms_role_menu(?, ?, ?, ?, ?)', [
    $roleId,
    $menuId,
    '1', // or '0' to deactivate
    auth()->id(),
    Str::uuid()
]);
```

---

## Quick Reference

### Migration Files
| Procedure | Migration File | Screen ID |
|-----------|---------------|-----------|
| sp_add_ms_role_permission | 2026_01_30_060000_create_sp_add_ms_role_permission_procedure.php | MRP01 |
| sp_update_ms_role_permission | 2026_01_29_060030_create_sp_update_ms_role_permission_procedure.php | MRP01 |
| sp_add_ms_role_menu | 2026_01_30_060001_create_sp_add_ms_role_menu_procedure.php | MRM01 |
| sp_update_ms_role_menu | 2026_01_29_060045_create_sp_update_ms_role_menu_procedure.php | MRM01 |

### Return Code Reference
| Code | Meaning |
|------|---------|
| 200 | Success |
| 404 | Resource not found (User/Role/Permission/Menu) |
| 409 | Conflict - Record already exists |

### Common Patterns

#### Bulk Insert Role Permissions
```php
foreach ($permissionIds as $permissionId) {
    $result = DB::select('CALL sp_add_ms_role_permission(?, ?, ?, ?)', [
        $roleId,
        $permissionId,
        auth()->id(),
        Str::uuid()
    ]);
    
    if ($result[0]->return_code != 200 && $result[0]->return_code != 409) {
        // Handle error
        throw new Exception($result[0]->return_message);
    }
}
```

#### Bulk Update Role Menus
```php
// Deactivate all existing menus
$existingMenus = DB::table('ms_role_menus')
    ->where('role_id', $roleId)
    ->get();

foreach ($existingMenus as $menu) {
    if (!in_array($menu->menu_id, $requestedMenuIds)) {
        DB::select('CALL sp_update_ms_role_menu(?, ?, ?, ?, ?)', [
            $roleId,
            $menu->menu_id,
            '0',
            auth()->id(),
            Str::uuid()
        ]);
    }
}

// Activate requested menus
foreach ($requestedMenuIds as $menuId) {
    DB::select('CALL sp_update_ms_role_menu(?, ?, ?, ?, ?)', [
        $roleId,
        $menuId,
        '1',
        auth()->id(),
        Str::uuid()
    ]);
}
```

---

## Testing

### Test sp_add_ms_role_permission
```bash
php artisan tinker --execute="print_r(DB::select('CALL sp_add_ms_role_permission(?, ?, ?, ?)', ['MRO01-00001', 'PRM00001', 'USR00001', 'test-uuid']));"
```

### Test sp_update_ms_role_permission
```bash
php artisan tinker --execute="print_r(DB::select('CALL sp_update_ms_role_permission(?, ?, ?, ?, ?)', ['MRO01-00001', 'PRM00001', '0', 'USR00001', 'test-uuid']));"
```

### Test sp_add_ms_role_menu
```bash
php artisan tinker --execute="print_r(DB::select('CALL sp_add_ms_role_menu(?, ?, ?, ?)', ['MRO01-00001', 'MNU00001', 'USR00001', 'test-uuid']));"
```

### Test sp_update_ms_role_menu
```bash
php artisan tinker --execute="print_r(DB::select('CALL sp_update_ms_role_menu(?, ?, ?, ?, ?)', ['MRO01-00001', 'MNU00001', '0', 'USR00001', 'test-uuid']));"
```

---

## Notes
- All procedures automatically generate IDs using `fn_gen_number()`
- Update procedures will create new records if they don't exist
- All operations are logged in audit trail via triggers
- Procedures validate existence of user, role, permission, and menu before operations
- Use `Str::uuid()` in Laravel to generate unique_id values
