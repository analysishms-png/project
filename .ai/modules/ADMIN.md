# Admin Module

## Overview

The Admin module handles super admin operations, company management, user management, and system administration.

---

## Components

### Controllers
- `MainController` - Main admin operations
- `CompanyController` - Company management
- `PropertyController` - Property management
- `BackupController` - Backup operations
- `SuperAdminMainController` - Super admin pages
- `QRGenerate` - QR code generation

### Models
- `Companyreg` - Company registration
- `CompanyLog` - Company log
- `CompanyDiscount` - Company discounts
- `CompServiceFacilities` - Service facilities
- `User` - User accounts
- `UserPermission` - User permissions
- `UserModule` - User modules
- `UserCrudPerm` - CRUD permissions

### Services
- None (uses controllers directly)

---

## Workflows

### Company Registration Flow
1. Collect company details
2. Verify information
3. Create company account
4. Generate property ID
5. Send confirmation

### User Management Flow
1. Create user account
2. Assign role
3. Set permissions
4. Send credentials
5. Activate account

### Backup Flow
1. Select backup type
2. Create backup
3. Store backup
4. Verify backup
5. Download if needed

---

## Database Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `companyreg` | Company registration | company_id, name |
| `companylog` | Company log | company_id, action |
| `companydiscount` | Discounts | company_id, discount |
| `compservicefacilities` | Facilities | company_id, facility |
| `users` | User accounts | id, name, email, role |
| `userpermission` | Permissions | user_id, module, access |
| `usermodule` | Modules | module_id, module_name |
| `usercrudperm` | CRUD perms | user_id, crud_type |

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/superadmin` | MainController@index | superadmin |
| GET | `/superadmin/tickets` | ToolsController@allTickets | superadmin.tickets |
| GET | `/superadmin/my-pages` | SuperAdminMainController@myPages | superadmin.myPages |
| GET | `/superadmin/my-pages/create` | SuperAdminMainController@createPage | superadmin.createPage |
| POST | `/superadmin/my-pages` | SuperAdminMainController@storePage | superadmin.storePage |
| GET | `/superadmin/my-pages/{id}/edit` | SuperAdminMainController@editPage | superadmin.editPage |
| PUT | `/superadmin/my-pages/{id}` | SuperAdminMainController@updatePage | superadmin.updatePage |
| DELETE | `/superadmin/my-pages/{id}` | SuperAdminMainController@destroyPage | superadmin.destroyPage |
| GET | `/superadmin/backups` | BackupController@index | superadmin.backups |
| GET | `/superadmin/storagefdownload` | BackupController@downloadStorage | superadmin.downloadStorage |
| POST | `/superadmin/verify-database` | BackupController@verifyDatabase | superadmin.verifyDatabase |
| GET | `/superadmin/qrgenerate` | MainController@openqrgenerate | superadmin.qrgenerate |
| POST | `/superadmin/qrgenerate` | QRGenerate@generateQR | superadmin.generateQR |
| GET | `/company` | PropertyController@loadProperty | company |
| GET | `/companyreg` | MainController@companyregister | companyreg |
| POST | `/companystore` | MainController@store | companystore |
| GET | `/companylist` | MainController@loadcompanylist | companylist |
| GET | `/updatepropertyadmin` | MainController@openUpdateProperty | updatepropertyadmin |
| POST | `/updatingproperty` | MainController@companyupdate | companyupdate |

---

## Key Features

1. **Company Management** - Manage company registrations
2. **Property Management** - Manage hotel properties
3. **User Management** - Manage user accounts
4. **Permission Management** - Manage user permissions
5. **Backup Management** - Create and manage backups
6. **QR Code Generation** - Generate QR codes
7. **Page Management** - Manage dynamic pages
8. **System Configuration** - Configure system settings

---

## User Roles

| Role | Access Level |
|------|--------------|
| Super Admin | Full system access |
| Admin | Company-level access |
| Manager | Department access |
| Staff | Limited access |
| Viewer | Read-only access |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
