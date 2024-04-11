# Tradedata API
*Phitech Consulting, Lucas Johnston, l.johnston@phitech.consulting, +31614340331*
## Changelog
### Version: v1.3.1 [Ready for Acc-testing]
Date: 2024-04-11
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Minor fix and ready for acceptation testing.
#### Summary
- Minor logic fix: IexApi::get_historic_quote() date conversion only if $response is not empty.
- Added Import Plan (*/storage/files/import_plan.xlsx*) to file base.
#### Deploy instructions
- Delete *schedule_import_1000* setting.
- Delete *frequency_import_1000* setting.
- Add *schedule_import_iex_historic* setting to *settings* table with value '0' (disabled). After testing, this setting should be set to '1' (enabled) to start the import.  
- Add *frequency_import_iex_historic* setting to *settings* table with value '0 0-20,23 * * *'.
- Execute the queries in column D in */storage/files/import_plan.xlsx*. But make sure there are no rows with future dates. Also exclude current date.
### Version: v1.3.0 [Bulk Import IEX Historic Stock Quotes]
Date: 2024-04-10
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
This version primarily contains logic for bulk importing StockQuotes from IEX, for later reference, and quality check on existing daily quotes. 
#### Summary
- Added get_dates_sample() to DatesHelper class for future quality checks. Supports for instance random samples.
- Added QualityCheck class with some logic (not yet finished) to sample-wise cross-reference earlier imported StockQuotes (from the daily imports) with historic quotes. Because those (from the daily imports) seem to have some periods of bad data.
- Deleted the Srv1 Helpers. ImportFromOldServerHelper became ImportIexHistoricHelper.
- Deleted all, or most other traces of Srv1 logic and parameters.
- Some minor refactors to (what is now) ImportIexHistoricHelper class to make it suitable for importing batches of IEX historic quotes.
- #### Deploy instructions
- Run ```php artisan migrate```
### Version: v1.2.10 [Minor refactor]
Date: 2024-02-21
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
### Version: v1.2.9 [Minor bugfix in WSQ]
Date: 2024-02-04
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
### Version: v1.2.8 [Added Weekend Stock Quote (WSQ) report]
Date: 2024-02-04
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Added Weekend Stock Quotes (WSQ) report: A report that shows all stock-quotes that are for some f*** reason dated in the weekend.
### Version: v1.2.7 [Minor bugfix SQO-report]
Date: 2024-02-04
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
### Version: v1.2.6 [Date index and SQO report]
Date: 2024-02-04
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
This update should speed up the SQO-report, furthermore adds a few important fields to the SQO-report.
#### Summary
- Added index to _date_ field in _stock_quotes_ table for faster retrieval.
- Added few fields to Stock Quotes Overview report to indicate counts of sources of the quotes.
### Version: v1.2.5 [Console command that writes Stock Quote Overview CSV to storage]
Date: 2024-02-04
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
This update contains a console command that does the same as the stock-quote-overview REST endpoint, only instead of returning it over HTTP, it stores the resulting CSV on storage. This is because for a 5GB dataset like on production, generating the report takes so long that the webserver times out.
### Version: v1.2.4 [Improved efficiency for Stored Quotes Overview report]
Date: 2024-02-03
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
The fetching of the StockQuotes are now by chunking. The Report->stored_quotes_overview() is now more efficient.
### Version: v1.2.3 [Add simple exception handling to ReportingController]
Date: 2024-02-03
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Logging exception on ReportingController because on production the Stored Quotes Overview report seems to crash the program, which kind of makes sense when you try to process 5GB in one PHP session.
### Version: v1.2.2 [Minor bugfix in Stored Quotes Overview report]
Date: 2024-02-03
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Solves minor bug: Error on empty collection in Report->stored_quotes_overview().
### Version: v1.2.1 [Stored Quotes Overview report, reporting module, minor changes]
Date: 2024-02-03
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
This update contains a new, simple reporting module, including a  _Stored Quotes Overview_ report.
#### Summary
- Added Reporting route(s), controller and helper class.
- Minor refactor, fixes and improvement to various API-routes and controllers.
- Added _Stored Quotes Overview_ report that delivers a matrix of metadata about StockQuotes records.
### Version: v1.2 [Capability to import from SRV1]
Date: 2024-01-19  
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
After this update, the import from SRV1 can start.
#### Summary
- Improvement: IexApi->get_quote() now checks whether date is in the weekend.
- Implementation of functionality that imports StockQuotes from SRV1.
- Partly implementation of functionality to import other historic StockQuotes from IexApi.
#### Deploy instructions
- Add record to _operators_ table: reference=ptc, name="Phitech Consulting"  
- Add record to _http_sources_ table: reference=ptc_srv1, name="Phitech SRV1", operator_id=[id_from_previous_bullet]  
- Run ```php artisan migrate```  
- Import file '/storage/files/measurements.sql' into the database.  
- Add *schedule_import_1000* setting to *settings* table with value '1' (enabled).  
- Add *frequency_import_1000* setting to *settings* table with value '* * * * *' ().  
#### Summary
- Improved console command descriptions.
- Introduced ImportFromOldVersionHelper class.
- Added weekend-day check in _IexApi->get_quote()_.  
### Version: v1.1 [Bugfix: Including IexApi class in DownloadAllCsQuotesToday]
Date: 2024-01-08  
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
### Version: v1.0 [Frequent retrieval of StockQuotes]
Date: 2024-01-08  
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
This update contains the bulk of the work for the frequent retrieval of StockQuotes from IEX API and storing them in the database. The application is now ready for beta testing.
#### Summary
- Added migration, model, controller and handler class for StockQuotes.
- Added migration, model and seeder, for Operators.
- Added migration, model, seeder, and handler class for HttpSources.
- Added functionality that automatically deactivates ExchangeProducts not present in the latest IexSymbolSet.
- Got the retrieval and storing of StockQuotes working with new model.
- Refactored and reorganized console commands.
- get_today_quote(), get_historic_quote() and get_quote() methods now all return StockQuote objects for consistency.
- Refactored and improved get_all_by_type() method from ExchangeProduct class.
- Added rate limiter helper (IexApi->get_delay_seconds()). It returns the number of seconds to wait before making the next request to the IEX API. This can be used to set a random number of seconds to execution of the next request to the IEX API.
- Custom exception: ```RetrieveQuoteException```.
- Better error handling for IexApi->get_today_quote() and IexApi->get_historic_quote().
- Added DownloadAllCsQuotesToday console command, and added it to the schedule.
- Finished the IexApi->download_by_type() method for handling the daily StockQuote downloads.
#### Deploy instructions
- Run ```php artisan migrate```
- Run ```php artisan db:seed OperatorsSeeder```.
- Run ```php artisan db:seed HttpSourcesSeeder```.
- Add *iex_max_requests_per_second* setting to *settings* table with value 4 (4 requests per second).
- Add *iex_max_stock_quotes_if_appdebug* setting to *settings* table with value 10 (10 stock quotes per request).
- Add *schedule_download_all_cs_quotes_today* setting to *settings* table with value '1' (enabled).
- Add *frequency_download_all_cs_quotes_today* setting to *settings* table with value '0 23 * * 1-5' (every week day at 23:00h).
- Add a Daemon (supervisor) to the server that runs ```php /application/root/folder/artisan queue:work```.
### Version: v0.10 [Management of ExchangeProducts]
Date: 2023-11-27  
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Added functionality to manage set of ExchangeProducts later to use for retrieving StockQuotes.
#### Summary
- Deleted 'old' stock_quotes and iex_symbols tables. The stock_quotes table will be added again later on.
- Added migration, model, controller and handler class for ExchangeProducts (replaces iex_symbols table/model).
- Added method to store each ExchangeProduct inside a previously downloaded IexSymbolSet into exchange_products table: ```ExchangeProduct:upsert_iex_symbol_set()```;
- Added console command to trigger ```upsert_iex_symbol_set()```.
- Added DataValidationException.
#### Deploy instructions
- Do ```php artisan migrate```.
- Add *schedule_upsert_exchange_products_from_iex* setting to *settings* table with value '1' (enabled).
- Add *frequency_upsert_exchange_products_from_iex* setting to *settings* table with value '15 9 * * *' (every day at 09:15h).
### Version: v0.9 [Endpoint to test SRV1 (old) database]
Date: 2023-11-26  
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Added a method and endpoint to test the connection to SRV1 database, later on to start migrating data.
#### Summary
- Added system time to ```TdaSelf->describe()```.
- Added ```/toolbox/test-data-from-srv1``` endpoint to test connection to dwh_market_data database on SRV1.
### Version: v0.8 [Added Settings CRUD endpoints]
Date: 2023-11-26  
Collaborators: Lucas Johnston <l.johnston@phitech.consulting>
#### Description
Added get (single), get (index), edit, create and delete endpoints for settings. Settings can now be administered via API.
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
