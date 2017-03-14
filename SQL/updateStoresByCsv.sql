


CREATE TEMPORARY TABLE stores_temp LIKE stores;

LOAD DATA INFILE 'stores.csv'
INTO TABLE stores_temp
FIELDS TERMINATED BY ','
(id, lat, lng); 

UPDATE stores INNER JOIN stores_temp on stores_temp.id = stores.id SET stores.lng = stores_temp.lng;
UPDATE stores INNER JOIN stores_temp on stores_temp.id = stores.id SET stores.lat = stores_temp.lat;

-- DROP TEMPORARY TABLE stores_temp;