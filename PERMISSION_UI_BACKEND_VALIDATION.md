# Permission UI & Backend Validation

## Tanggal: 21 Januari 2026

## 📋 Ringkasan Perubahan

Menambahkan pengecekan permission di Frontend (View) dan Backend (Controller) untuk semua module agar:
- Button Add/Edit/Delete hanya muncul jika user memiliki permission
- Backend melakukan double-check permission sebelum eksekusi action

## ✅ Perubahan Frontend (Views)

### 1. Users Module (`resources/views/users/index.blade.php`)

#### Button "Add User"
```blade
@if(auth()->user()->hasPermission('users.create'))
    <a href="{{ route('users.create') }}" class="btn btn-light btn-sm">
        <i class="bi bi-plus-circle"></i> Add User
    </a>
@endif
```

#### Button "Edit" & "Delete"
```blade
<td>
    @if(auth()->user()->hasPermission('users.edit'))
        <a href="{{ route('users.edit', $user->unique_id) }}" class="btn btn-sm btn-warning">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    
    @if(auth()->user()->hasPermission('users.delete'))
        @if(!$user->hasRole('ADMIN') && $user->user_id !== auth()->id())
            <form action="{{ route('users.destroy', $user->unique_id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        @endif
    @endif
</td>
```

### 2. Roles Module (`resources/views/roles/index.blade.php`)

#### Button "Add Role"
```blade
@if(auth()->user()->hasPermission('roles.create'))
    <a href="{{ route('roles.create') }}" class="btn btn-light btn-sm">
        <i class="bi bi-plus-circle"></i> Add Role
    </a>
@endif
```

#### Button "Edit" & "Delete"
```blade
<td>
    @if(auth()->user()->hasPermission('roles.edit'))
        <a href="{{ route('roles.edit', $role->unique_id) }}" class="btn btn-sm btn-warning">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    
    @if(auth()->user()->hasPermission('roles.delete'))
        <form action="{{ route('roles.destroy', $role->unique_id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</td>
```

### 3. Permissions Module (`resources/views/permissions/index.blade.php`)

#### Button "Add Permission"
```blade
@if(auth()->user()->hasPermission('permissions.create'))
    <a href="{{ route('permissions.create') }}" class="btn btn-light btn-sm">
        <i class="bi bi-plus-circle"></i> Add Permission
    </a>
@endif
```

#### Button "Edit" & "Delete"
```blade
<td>
    @if(auth()->user()->hasPermission('permissions.edit'))
        <a href="{{ route('permissions.edit', $permission->unique_id) }}" class="btn btn-sm btn-warning">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    
    @if(auth()->user()->hasPermission('permissions.delete'))
        <form action="{{ route('permissions.destroy', $permission->unique_id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</td>
```

### 4. Menus Module (`resources/views/menus/index.blade.php`)

#### Button "Add Menu"
```blade
@if(auth()->user()->hasPermission('menus.create'))
    <a href="{{ route('menus.create') }}" class="btn btn-light btn-sm">
        <i class="bi bi-plus-circle"></i> Add Menu
    </a>
@endif
```

#### Button "Edit" & "Delete"
```blade
<td>
    @if(auth()->user()->hasPermission('menus.edit'))
        <a href="{{ route('menus.edit', $menu->unique_id) }}" class="btn btn-sm btn-warning">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    
    @if(auth()->user()->hasPermission('menus.delete'))
        <form action="{{ route('menus.destroy', $menu->unique_id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</td>
```

## ✅ Perubahan Backend (Controllers)

### 1. UserController (`app/Http/Controllers/UserController.php`)

```php
public function create()
{
    // Double check permission
    if (!auth()->user()->hasPermission('users.create')) {
        abort(403, 'Unauthorized action.');
    }
    // ... rest of code
}

public function store(UserRequest $request)
{
    // Double check permission
    if (!auth()->user()->hasPermission('users.create')) {
        abort(403, 'Unauthorized action.');
    }
    // ... rest of code
}

public function edit(User $user)
{
    // Double check permission
    if (!auth()->user()->hasPermission('users.edit')) {
        abort(403, 'Unauthorized action.');
    }
    // ... rest of code
}

public function update(UserRequest $request, User $user)
{
    // Double check permission
    if (!auth()->user()->hasPermission('users.edit')) {
        abort(403, 'Unauthorized action.');
    }
    // ... rest of code
}

public function destroy(User $user)
{
    // Double check permission
    if (!auth()->user()->hasPermission('users.delete')) {
        abort(403, 'Unauthorized action.');
    }
    // ... rest of code
}
```

### 2. RoleController (`app/Http/Controllers/RoleController.php`)

Sama seperti UserController, ditambahkan pengecekan permission di:
- `create()` - Check `roles.create`
- `store()` - Check `roles.create`
- `edit()` - Check `roles.edit`
- `update()` - Check `roles.edit`
- `destroy()` - Check `roles.delete`

### 3. PermissionController (`app/Http/Controllers/PermissionController.php`)

Sama seperti UserController, ditambahkan pengecekan permission di:
- `create()` - Check `permissions.create`
- `store()` - Check `permissions.create`
- `edit()` - Check `permissions.edit`
- `update()` - Check `permissions.edit`
- `destroy()` - Check `permissions.delete`

### 4. MenuController (`app/Http/Controllers/MenuController.php`)

Sama seperti UserController, ditambahkan pengecekan permission di:
- `create()` - Check `menus.create`
- `store()` - Check `menus.create`
- `edit()` - Check `menus.edit`
- `update()` - Check `menus.edit`
- `destroy()` - Check `menus.delete`

## 🔒 Lapisan Keamanan (Security Layers)

Sekarang sistem memiliki **3 lapisan keamanan**:

### Layer 1: Route Middleware
```php
Route::middleware('permission:users.create')->group(function () {
    Route::get('/users/create', [UserController::class, 'create']);
    Route::post('/users', [UserController::class, 'store']);
});
```
- Mencegah akses route jika tidak ada permission
- Return 403 Unauthorized

### Layer 2: Controller Validation
```php
public function create()
{
    if (!auth()->user()->hasPermission('users.create')) {
        abort(403, 'Unauthorized action.');
    }
    // ...
}
```
- Double-check di controller
- Backup jika middleware di-bypass

### Layer 3: Frontend UI
```blade
@if(auth()->user()->hasPermission('users.create'))
    <a href="{{ route('users.create') }}">Add User</a>
@endif
```
- Hide button jika tidak ada permission
- Improve UX (user tidak lihat button yang tidak bisa diakses)

## 🧪 Testing Checklist

### Test Users Module
- [ ] Login dengan role tanpa `users.create` → Button "Add User" tidak muncul
- [ ] Login dengan role tanpa `users.edit` → Button "Edit" tidak muncul
- [ ] Login dengan role tanpa `users.delete` → Button "Delete" tidak muncul
- [ ] Coba akses `/users/create` via URL langsung → Error 403
- [ ] Coba submit form create via Postman → Error 403

### Test Roles Module
- [ ] Login dengan role tanpa `roles.create` → Button "Add Role" tidak muncul
- [ ] Login dengan role tanpa `roles.edit` → Button "Edit" tidak muncul
- [ ] Login dengan role tanpa `roles.delete` → Button "Delete" tidak muncul
- [ ] Coba akses `/roles/create` via URL langsung → Error 403

### Test Permissions Module
- [ ] Login dengan role tanpa `permissions.create` → Button "Add Permission" tidak muncul
- [ ] Login dengan role tanpa `permissions.edit` → Button "Edit" tidak muncul
- [ ] Login dengan role tanpa `permissions.delete` → Button "Delete" tidak muncul
- [ ] Coba akses `/permissions/create` via URL langsung → Error 403

### Test Menus Module
- [ ] Login dengan role tanpa `menus.create` → Button "Add Menu" tidak muncul
- [ ] Login dengan role tanpa `menus.edit` → Button "Edit" tidak muncul
- [ ] Login dengan role tanpa `menus.delete` → Button "Delete" tidak muncul
- [ ] Coba akses `/menus/create` via URL langsung → Error 403

## 📊 Hasil Akhir

### Sebelum
- ❌ Semua button muncul untuk semua user
- ❌ User bisa akses form create/edit via URL langsung
- ❌ Hanya middleware yang protect (1 layer)

### Sesudah
- ✅ Button hanya muncul jika ada permission
- ✅ Controller double-check permission
- ✅ 3 layer security (Middleware + Controller + UI)
- ✅ Better UX (user tidak bingung lihat button yang tidak bisa diakses)

## 💡 Tips

### 1. Jika Button Masih Muncul
Clear cache:
```bash
php artisan view:clear
php artisan cache:clear
```

### 2. Jika Masih Bisa Akses via URL
Pastikan middleware sudah terdaftar di `routes/web.php`:
```php
Route::middleware('permission:users.create')->group(function () {
    // routes here
});
```

### 3. Testing Permission
Gunakan user dengan role yang berbeda-beda untuk testing:
- Admin (semua permission)
- Manager (beberapa permission)
- Staff (permission terbatas)

## 📝 File yang Diubah

### Frontend (Views)
- ✅ `resources/views/users/index.blade.php`
- ✅ `resources/views/roles/index.blade.php`
- ✅ `resources/views/permissions/index.blade.php`
- ✅ `resources/views/menus/index.blade.php`

### Backend (Controllers)
- ✅ `app/Http/Controllers/UserController.php`
- ✅ `app/Http/Controllers/RoleController.php`
- ✅ `app/Http/Controllers/PermissionController.php`
- ✅ `app/Http/Controllers/MenuController.php`

## 🎯 Kesimpulan

Sistem permission sekarang sudah lengkap dengan:
1. ✅ Route middleware protection
2. ✅ Controller validation
3. ✅ UI conditional rendering
4. ✅ Better UX (hide button yang tidak bisa diakses)
5. ✅ Better security (3 layer protection)
