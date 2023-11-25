# Tradedata API
*Phitech Consulting, Lucas Johnston, l.johnston@phitech.consulting, +31614340331*
## Changelog
### Version: v0.7 [Dynamic scheduling of daily IEX symbol sets]
Date: 2023-11-25  
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Added setting that determines the download frequency of IEX symbol set downloads.
#### Deploy instructions
- Add *frequency_retrieve_iex_symbol_set* setting to *settings* table with value '0 9 * * *' (every day at 09:00h).
- Better daily at 09:00h so that actually the symbol sets of *today* are downloaded and not from yesterday (6h time difference, so it must be at least 06:00h).
### Version: v0.6 [Functions for management and scheduling]
Date: 2023-11-24  
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Added various functions to be able to manage the application and to make it work by itself on a schedule.
#### Summary
- Added self description class and console command ```app\Classes\TdaSelf```.
- Added tda config file with example value.
- Temporarily reinstated *iex_symbols* table.
- Added TdaServiceProvider.
- Added migration, controller, model and seeder for settings.
- Added automatic loading of settings from settings table.
- Added custom error_log table.
- Added tasked schedule for every day at 02:00 hours: ```iex:download_symbols```
#### Deploy instructions
Add new cron job to the server:

```php /path/to/application/artisan schedule:run >> /dev/null 2>&1```

With frequency: ```* * * * *```
