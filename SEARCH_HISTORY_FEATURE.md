# Search History Feature

## Overview
Fitur untuk mencatat dan memonitor history search dari user beserta execution time query. Hanya bisa diakses oleh user dengan permission **search-history.view**.

## Database Structure

### Table: `hs_search`

| Field | Type | Description |
|-------|------|-------------|
| search_id | bigint unsigned | Primary key, auto increment |
| user_id | bigint unsigned | Foreign key ke ms_users |
| search | varchar(255) | Input search query dari user |
| date_from | date | Filter date from |
| date_to | date | Filter date to |
| executed_date | datetime | Waktu eksekusi search |
| execution_time | decimal(10,2) | Waktu eksekusi dalam milliseconds |
| transaction_type | enum('H','B') | H = Header, B = Body |

**Indexes:**
- idx_user_id
- idx_executed_date
- idx_transaction_type

**Foreign Key:**
- user_id references ms_users(user_id) ON DELETE CASCADE

## Files Created/Modified

### 1. Migration
- **File**: `database/migrations/2026_01_22_063632_create_hs_search_table.php`
- **Status**: ✅ Migrated

### 2. Model
- **File**: `app/Models/SearchHistory.php`
- **Features**:
  - Relationship dengan User model
  - Method `getTransactionTypeLabel()` untuk display label
  - Cast untuk date dan decimal fields

### 3. Controller
- **File**: `app/Http/Controllers/SearchHistoryController.php`
- **Method**: `index()` - Display search history dengan filter
- **Filters**:
  - Transaction Type (H/B)
  - User
  - Date Range (executed_date)
  - Pagination

### 4. View
- **File**: `resources/views/search-history/index.blade.php`
- **Features**:
  - Datatable dengan filter
  - Color-coded execution time badges:
    - Green: < 100ms (Fast)
    - Yellow: 100-500ms (Medium)
    - Red: > 500ms (Slow)
  - Transaction type badges
  - Date range picker
  - User filter dropdown

### 5. Routes
- **File**: `routes/web.php`
- **Route**: `GET /search-history`
- **Middleware**: `permission:search-history.view`
- **Name**: `search-history.index`

### 6. Permission Seeder
- **File**: `database/seeders/SearchHistoryPermissionSeeder.php`
- **Permission Code**: `search-history.view`
- **Permission Name**: View Search History
- **Assigned to**: Administrator role

### 7. Menu Seeder
- **File**: `database/seeders/SearchHistoryMenuSeeder.php`
- **Menu Code**: `search-history`
- **Menu Name**: Search History
- **Icon**: `bi bi-clock-history`
- **Assigned to**: Administrator role

## Integration

### Transaction Header Controller
- **File**: `app/Http/Controllers/TransactionHeaderController.php`
- **Modified**: `index()` method
- **Changes**:
  - Added timing measurement using `microtime(true)`
  - Log search history when user performs search or date filter
  - Transaction type: 'H' (Header)

### Transaction Body Controller
- **File**: `app/Http/Controllers/TransactionBodyController.php`
- **Modified**: `index()` method
- **Changes**:
  - Added timing measurement using `microtime(true)`
  - Log search history when user performs search or date filter
  - Transaction type: 'B' (Body)

## How It Works

1. **User performs search** on Transaction Header or Body page
2. **System measures execution time**:
   ```php
   $startTime = microtime(true);
   // ... query execution ...
   $endTime = microtime(true);
   $executionTime = ($endTime - $startTime) * 1000; // Convert to ms
   ```
3. **System logs to hs_search table**:
   - User ID
   - Search query
   - Date filters (if any)
   - Execution time
   - Transaction type (H or B)
4. **Administrator can view** all search history with filters

## Access Control

- **Permission Required**: `search-history.view`
- **Middleware**: `permission:search-history.view`
- **Menu Visibility**: Visible to users with search-history.view permission
- **Default Assignment**: Administrator role

## Features

### Search History Page
1. **Filters**:
   - Per Page (10, 25, 50, 100)
   - Transaction Type (All, Header, Body)
   - User (All Users or specific user)
   - Date Range (executed_date)

2. **Display Columns**:
   - ID
   - User (full name)
   - Type (Header/Body badge)
   - Search Query
   - Date From
   - Date To
   - Executed Date (with time)
   - Execution Time (color-coded badge)

3. **Performance Indicators**:
   - 🟢 Green badge: < 100ms (Fast)
   - 🟡 Yellow badge: 100-500ms (Medium)
   - 🔴 Red badge: > 500ms (Slow)

## Usage Example

### For Users with Permission:
1. Login with user that has `search-history.view` permission
2. Navigate to "Search History" menu
3. View all search activities
4. Filter by:
   - Transaction type (Header/Body)
   - Specific user
   - Date range
5. Monitor query performance

### Automatic Logging:
- Every search on Transaction Header page → logged as type 'H'
- Every search on Transaction Body page → logged as type 'B'
- Includes search query, date filters, and execution time

## Benefits

1. **Performance Monitoring**: Track slow queries
2. **User Activity**: Monitor which users search most frequently
3. **Query Patterns**: Understand common search patterns
4. **Optimization**: Identify queries that need optimization
5. **Audit Trail**: Keep record of all search activities

## Notes

- Search history is logged only when user performs actual search (not on initial page load)
- Execution time includes cache lookup time
- Foreign key cascade delete: When user is deleted, their search history is also deleted
- No automatic cleanup - Administrator should manually clean old records if needed
