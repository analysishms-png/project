# Analysis HMS - Routes Documentation

## Routes Overview

This document describes all routes in the Analysis HMS project.

---

## Route Files

| File | Purpose | Middleware |
|------|---------|-----------|
| `web.php` | Main web routes | web, auth |
| `api.php` | API endpoints | api, sanctum |
| `admin.php` | Admin routes | web, auth, superadmin |
| `company.php` | Company routes | web, auth, company |
| `property.php` | Property routes | web, auth, company |
| `pointofsale.php` | POS routes | web, auth, company |
| `reporting.php` | Report routes | web, auth, company |
| `tools.php` | Tools routes | web, auth, company |
| `userparam.php` | User parameter routes | web, auth |
| `console.php` | Console routes | console |
| `channel.php` | Broadcasting channels | auth |
| `channels.php` | Channel definitions | auth |

---

## Web Routes (`web.php`)

### Public Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/` | HomeController@index | home |
| GET | `/welcome` | - | - |
| GET | `/about` | HomeController@about | about |
| GET | `/contact` | HomeController@contact | contact |
| GET | `/page/{slug}` | HomeController@dynamicPage | page.show |

### Authentication Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| POST | `/loginpy` | PythonAuth@login | - |
| POST | `/auto-login` | AutoLoginController@loginUser | auto.login |
| GET | `/logout` | LoginController@logout | logout |

### Super Admin Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/superadmin` | MainController@index | superadmin |
| GET | `/superadmin/tickets` | ToolsController@allTickets | superadmin.tickets |
| GET | `/superadmin/my-pages` | SuperAdminMainController@myPages | - |
| GET | `/superadmin/backups` | BackupController@index | superadmin.backups |
| GET | `/superadmin/qrgenerate` | MainController@openqrgenerate | - |

### Company Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/company` | PropertyController@loadProperty | company |
| GET | `/chequeclearedregister` | CheckRegister@chequeClearedRegister | chequeclearedregister |

### Reporting Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/dailyfunctionsheet` | ReportController@dailyFunctionSheet | dailyfunctionsheet |
| GET | `/bookinginquirydetail` | ReportController@bookingEnquiryDetail | bookinginquirydetail |
| GET | `/outStandingreport` | ReportController@outStandingreport | outStandingreport |
| GET | `/companywisesalereport` | ReportController@companyWiseSaleReport | companywisesalereport |
| GET | `/itemwisesalesreport` | ReportController@itemWiseSaleReport | itemwisesalesreport |

### Booking Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| POST | `/booking-followup` | BookingFollowUp@store | booking-followup.store |
| GET | `/inquiryfollup` | BookingFollowUp@index | inquiryfollup |
| GET | `/booking-followup/comments/{inqno}` | BookingFollowUp@comments | bookingfollowup.comments |

### Contact & Demo Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| POST | `/contactsubmit` | ContactController@store | contact.submit |
| POST | `/demo-request` | DemoRequestController@store | demo-request.store |

### Service Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/services/front-office` | HomeController@frontofficeservices | services.front-office |
| GET | `/services/pointofsale` | HomeController@pointofsaleservices | services.pointofsale |
| GET | `/services/banquet` | HomeController@banquetservices | services.banquet |
| GET | `/services/inventory` | HomeController@inventoryservices | services.inventory |
| GET | `/services/reservation` | HomeController@reservationservices | services.reservation |

### Utility Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/storage-link` | - | - |
| GET | `/storage/{path}` | - | - |
| GET | `/autochargepost` | CronController@autoCharge | - |
| GET | `/run-db-backup` | DatabaseSend@run | - |
| GET | `/cleanup-backups` | CleanUp@cleanup | - |

### Developer Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/developertools` | DeveloperTools@opendevelopertools | developertools |
| POST | `/generate-api-client` | DeveloperTools@generate | api.client.generate |
| GET | `/download-api-client/{propertyid}` | DeveloperTools@download | api.client.download |
| GET | `/apiusages` | HomeController@apiusages | api.usages |

### Activity Log Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/admin/activity-logs` | ActivityLogController@index | - |
| GET | `/admin/activity-logs/data` | ActivityLogController@data | - |
| GET | `/admin/activity-logs/top-routes` | ActivityLogController@getTopRoutes | - |
| GET | `/admin/activity-logs/top-users` | ActivityLogController@getTopUsers | - |

### Support Ticket Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/my-tickets` | CompanyController@myTickets | tools.myTickets |
| GET | `/my-ticket-messages` | CompanyController@getMyTicketMessages | tools.getMyTicketMessages |
| POST | `/my-ticket-messages/send` | CompanyController@sendMyTicketMessage | tools.sendMyTicketMessage |
| POST | `/my-ticket-confirm-solved` | CompanyController@confirmMyTicketSolved | tools.confirmMyTicketSolved |

---

## Tools Routes (`tools.php`)

### Dashboard

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/tools` | HomeController@tools | tools |
| GET | `/tools/dashboard` | ToolsController@toolsdashboard | toolsdashboard |

### Data Management

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/tools/tablemanagement` | ToolsController@tablemanagement | tablemanagement |
| POST | `/tools/fetch_tables` | ToolsController@fetchtables | fetchtables |
| POST | `/tools/fetch_table_data` | ToolsController@fetchtabledata | fetchtabledata |
| POST | `/tools/update_table_cell` | ToolsController@updatetablecell | updatetablecell |
| POST | `/tools/bulk_update_records` | ToolsController@bulkupdaterecords | bulk_update_records |
| POST | `/tools/insert_record` | ToolsController@insertrecord | insertrecord |
| POST | `/tools/delete_table_record` | ToolsController@deletetablerecord | deletetablerecord |

### Company Management

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/tools/changecompanydetails` | ToolsController@changecompanydetails | changecompanydetails |
| POST | `/tools/fetch_companydetails` | ToolsController@fetchcompanydetails | fetchcompanydetails |
| POST | `/tools/changecompanydetailssubmit` | ToolsController@changecompanydetailssubmit | changecompanydetailssubmit |

### Room Operations

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/tools/roomchargepost` | ToolsController@roomchargepost | roomchargepost |
| POST | `/tools/roomchargepostsubmit` | ToolsController@roomchargepostsubmit | roomchargepostsubmit |
| GET | `/tools/extrabedpost` | ToolsController@extrabedpost | extrabedpost |

### Financial Tools

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/tools/advchargetool` | ToolsController@openadvancecharge | advcharge.route |
| POST | `/tools/advancechargesubmit` | ToolsController@advancechargesubmit | advancechargesubmit |
| GET | `/tools/changebilldate` | ToolsController@changebilldate | changebilldate |
| POST | `/tools/changebilldatesubmit` | ToolsController@changebilldatesubmit | changebilldatesubmit |

### POS Tools

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/tools/posrecycle` | ToolsController@posrecycle | posrecycle |
| POST | `/tools/posrecyclesubmit` | ToolsController@posrecyclesubmit | posrecyclesubmit |

### Support & Logging

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/tools/getlogreport` | ToolsController@logreport | tools.getlogreport |
| POST | `/tools/fetchlogreport` | ToolsController@fetchlogreport | tools.fetchlogreport |
| POST | `/tools/submit-ticket` | ToolsController@submitTicket | tools.submitTicket |
| GET | `/tools/tickets` | ToolsController@viewTickets | tools.viewTickets |
| POST | `/tools/update-ticket-status` | ToolsController@updateTicketStatus | tools.updateTicketStatus |

### Meta Tags

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/meta` | MetaController@index | meta.index |
| GET | `/meta/create` | MetaController@editCreate | meta.create |
| GET | `/meta/edit/{id?}` | MetaController@editCreate | meta.edit |
| POST | `/meta/store` | MetaController@store | meta.store |
| DELETE | `/meta/destroy/{id?}` | MetaController@destroy | meta.destroy |

---

## User Parameter Routes (`userparam.php`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| POST | `/submipermusermodule` | UserParam@submipermusermodule | submipermusermodule |
| POST | `/validatecheck` | UserParam@validatecheck | validatecheck |
| GET | `/getmainmenu` | UserParam@getmainmenu | getmainmenu |
| POST | `/fetchsubmenu` | UserParam@fetchsubmenu | fetchsubmenu |
| POST | `/fetchlastmenu` | UserParam@fetchlastmenu | fetchlastmenu |
| GET | `/userpermision` | UserParam@userpermision | - |
| POST | `/getposuserdetails` | UserParam@getposuserdetails | getposuserdetails |
| POST | `/menulist` | UserParam@menulist | menulist |
| POST | `/userparamsubmit` | UserParam@userparamsubmit | userparamsubmit |
| POST | `/updateposuserxhr` | UserParam@updateposuserxhr | updateposuserxhr |

---

## Route Statistics

| Category | Count |
|----------|-------|
| Total Routes | 200+ |
| Web Routes | 150+ |
| API Routes | 50+ |
| Console Routes | 10+ |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
