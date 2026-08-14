# HR Module

## Overview

The HR module handles employee management, attendance, payroll, salary, and HR operations.

---

## Components

### Controllers
- `HrpayrollsController` - HR and payroll operations

### Models
- `Employee` - Employee master
- `EmpCategory` - Employee categories
- `Attendance` - Attendance records
- `Salary` - Salary records
- `Hrpayrolls` - Payroll data
- `Overtime` - Overtime records
- `Loan` - Loan records
- `Depart` - Departments
- `Depart1` - Department details

### Services
- None (uses controllers directly)

---

## Workflows

### Employee Management Flow
1. Add new employee
2. Assign department
3. Set salary details
4. Generate employee ID
5. Activate employee

### Attendance Flow
1. Mark attendance
2. Record check-in time
3. Record check-out time
4. Calculate overtime
5. Update attendance record

### Payroll Flow
1. Select pay period
2. Calculate basic salary
3. Add allowances
4. Deduct taxes
5. Process payment
6. Generate payslip

---

## Database Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `employee` | Employee master | emp_id, name, department |
| `empcategory` | Categories | cat_code, cat_name |
| `attendance` | Attendance | emp_id, date, status |
| `salary` | Salary records | emp_id, month, amount |
| `hrpayrolls` | Payroll data | emp_id, month, gross |
| `overtime` | Overtime | emp_id, hours, date |
| `loan` | Loans | emp_id, amount, status |
| `depart` | Departments | dept_code, dept_name |
| `depart1` | Dept details | dept_code, detail |

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/hr` | HrpayrollsController@index | hr.index |
| GET | `/hr/employees` | HrpayrollsController@employees | hr.employees |
| POST | `/hr/employee/store` | HrpayrollsController@storeEmployee | hr.storeEmployee |
| GET | `/hr/attendance` | HrpayrollsController@attendance | hr.attendance |
| POST | `/hr/attendance/mark` | HrpayrollsController@markAttendance | hr.markAttendance |
| GET | `/hr/payroll` | HrpayrollsController@payroll | hr.payroll |
| POST | `/hr/payroll/process` | HrpayrollsController@processPayroll | hr.processPayroll |

---

## Key Features

1. **Employee Management** - Add, edit, and manage employees
2. **Department Management** - Manage departments and positions
3. **Attendance Tracking** - Track daily attendance
4. **Overtime Management** - Calculate and manage overtime
5. **Salary Management** - Manage salary structures
6. **Payroll Processing** - Process monthly payroll
7. **Loan Management** - Track employee loans
8. **Leave Management** - Manage employee leaves

---

## Payroll Calculation

### Basic Formula
```
Gross Salary = Basic Salary + Allowances
Deductions = PF + ESI + Tax + Loan
Net Salary = Gross Salary - Deductions
```

### Allowances
- HRA (House Rent Allowance)
- DA (Dearness Allowance)
- TA (Travel Allowance)
- Medical Allowance

### Deductions
- PF (Provident Fund)
- ESI (Employee State Insurance)
- Professional Tax
- Income Tax (TDS)

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
