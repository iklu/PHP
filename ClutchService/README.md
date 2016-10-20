Clutch module:
Account
    - higher level logic
    - CardNumber entity from Clutch
    - ClutchAccount class for search and setting services containers needed in other modules
    - BrandDemographics entity for managing the vehicles ids and vehicles details

Vehicles
    - logic between Account and Transactions modules
    - VehiclesManager class:  getting the vehicles with the BrandDemographics entity
    - Helper class: formatting "veh_" (vehicle card number)

Transactions
    DataProvider:
        - SkuCodesProvider: search the sku codes from Clutch in our database , also CACHED the results to improve speed
        - StoresProvider: search the location transaction e.g. store in our database, also CACHED the results to improve speed
    Filters:
        - filters classes to apply for transactions
    Library:
        - the logic for managing the filters

    - Filter class extended by individual filters in Filters folder : has some pre/post processing transaction data
    - FiltersToApply class has filters constants , the are used in filters for triggering
    - Target class:always last filter applied for wrapping the data
    - Transaction class: appending the filters for transaction
    - Transaction manager: preparing the transaction data for the filters and the services used