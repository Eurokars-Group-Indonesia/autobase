# Fix Error: Column 'pivot' not found

## Error Message
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'pivot' in 'where clause'
```

## Root Cause

Error terjadi karena penggunaan `wherePivot()` yang salah di dalam closure `whereHas()`.

### Kode yang Salah ❌
```php
public function hasPermission($permissionCode)
{
    return $this->roles()
        ->whereHas('permissions', function ($query) use ($permissionCode) {
            $query->where('ms_permissions.permission_code', $permissionCode)
                ->where('ms_permissions.is_active', '1')
                ->wherePivot('is_active', '1'); // ❌ SALAH!
        })
        ->exists();
}
```

### Mengapa Salah?

`wherePivot()` adalah method khusus yang hanya bisa digunakan pada **relasi langsung** (direct relationship query), bukan di dalam closure `whereHas()`.

Ketika kita menggunakan `whereHas()`, kita bekerja dengan query builder biasa, bukan dengan relationship query builder. Oleh karena itu, Laravel tidak mengenali method `wherePivot()` dan mencoba mencari kolom bernama `pivot` di database, yang tidak ada.

## Solusi ✅

Gunakan nama tabel pivot secara langsung:

```php
public function hasPermission($permissionCode)
{
    return $this->roles()
        ->whereHas('permissions', function ($query) use ($permissionCode) {
            $query->where('ms_permissions.permission_code', $permissionCode)
                ->where('ms_permissions.is_active', '1')
                ->where('ms_role_permissions.is_active', '1'); // ✅ BENAR!
        })
        ->exists();
}
```

## Penjelasan

### wherePivot() - Kapan Digunakan?

`wherePivot()` hanya bisa digunakan pada **relasi langsung**:

```php
// ✅ BENAR - Relasi langsung
$role->permissions()->wherePivot('is_active', '1')->get();

// ✅ BENAR - Eager loading dengan wherePivot
$role->load(['permissions' => function($query) {
    $query->wherePivot('is_active', '1');
}]);

// ❌ SALAH - Di dalam whereHas
$user->roles()->whereHas('permissions', function($query) {
    $query->wherePivot('is_active', '1'); // Error!
});
```

### Nama Tabel Pivot - Kapan Digunakan?

Gunakan nama tabel pivot langsung di dalam `whereHas()`:

```php
// ✅ BENAR - Di dalam whereHas
$user->roles()->whereHas('permissions', function($query) {
    $query->where('ms_role_permissions.is_active', '1');
});
```

## Contoh Lain

### Contoh 1: Cek User dengan Role Aktif yang Memiliki Menu Tertentu

```php
// ❌ SALAH
$users = User::whereHas('roles.menus', function($query) use ($menuId) {
    $query->where('menu_id', $menuId)
        ->wherePivot('is_active', '1'); // Error!
})->get();

// ✅ BENAR
$users = User::whereHas('roles.menus', function($query) use ($menuId) {
    $query->where('menu_id', $menuId)
        ->where('ms_role_menus.is_active', '1');
})->get();
```

### Contoh 2: Cek Role dengan Permission Tertentu

```php
// ❌ SALAH
$roles = Role::whereHas('permissions', function($query) {
    $query->where('permission_code', 'users.view')
        ->wherePivot('is_active', '1'); // Error!
})->get();

// ✅ BENAR
$roles = Role::whereHas('permissions', function($query) {
    $query->where('permission_code', 'users.view')
        ->where('ms_role_permissions.is_active', '1');
})->get();
```

## Tips & Best Practices

### 1. Gunakan wherePivot() untuk Relasi Langsung
```php
// Ambil permissions aktif dari role
$activePermissions = $role->permissions()
    ->wherePivot('is_active', '1')
    ->get();
```

### 2. Gunakan Nama Tabel untuk whereHas()
```php
// Cek apakah role memiliki permission aktif
$hasActivePermission = Role::whereHas('permissions', function($query) {
    $query->where('ms_role_permissions.is_active', '1');
})->exists();
```

### 3. Kombinasi dengan Relasi
```php
// Definisi relasi dengan wherePivot
public function permissions()
{
    return $this->belongsToMany(Permission::class, 'ms_role_permissions')
        ->wherePivot('is_active', '1'); // ✅ Ini benar
}

// Penggunaan di whereHas
public function hasPermission($code)
{
    return $this->roles()->whereHas('permissions', function($query) use ($code) {
        $query->where('permission_code', $code)
            ->where('ms_role_permissions.is_active', '1'); // ✅ Ini juga benar
    })->exists();
}
```

## Kesimpulan

- `wherePivot()` = Untuk relasi langsung
- `where('pivot_table.column')` = Untuk whereHas() closure
- Selalu gunakan nama tabel pivot yang lengkap di dalam whereHas()
- Error "Column 'pivot' not found" = Anda menggunakan wherePivot() di tempat yang salah

## File yang Diperbaiki

- ✅ `app/Models/User.php` - Method `hasPermission()`
- ✅ `PERMISSION_SYSTEM_FIX.md` - Dokumentasi diupdate
