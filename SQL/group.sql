SELECT 
locationState AS state,
locationCity AS city,
storeId AS store_id
FROM (
SELECT 
locationState, 
locationCity, 
storeId
FROM stores
WHERE 
locationState = 'TX'
OR locationCity = 'houston'
OR storeId = 75
GROUP BY 
locationState
) AS stores 

SELECT 
locationState, 
locationCity, 
storeId
FROM stores
WHERE 
locationState = 'TX'
OR locationCity = 'houston'
OR storeId = 75
GROUP BY 
locationState
