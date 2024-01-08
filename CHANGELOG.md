# Tradedata API
*Phitech Consulting, Lucas Johnston, l.johnston@phitech.consulting, +31614340331*
## Changelog
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
