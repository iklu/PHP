SELECT DISTINCT 
storeId  
FROM  stores_temp
WHERE (storeId NOT IN (SELECT storeId
FROM stores))