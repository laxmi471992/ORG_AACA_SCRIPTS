-- query on Mysql side 

SELECT DISTINCT PYALORGCD
			from RMAACABHS
			WHERE RMSTRANCDE='1A'
			AND CAST(DTPRLT AS DATE) >= 20260324
			AND VENDORNUM IN ('EDAB') group by PYALORGCD ORDER BY  PYALORGCD;

-- Query on IBMi side 

SELECT DISTINCT PYALORGCD                                                 
   from AACALIB.RMAACABHS                                                 
   WHERE RMSTRANCDE='1A'                                                  
   AND DTPRLT >= '20260324'                                               
   AND VENDORNUM IN ('EDAB') group by PYALORGCD ORDER BY  PYALORGCD       
SELECT statement run complete.         

--  Both queries are giving same result.
 ORG CODE            

 AQUA            
 FFC             
 LSC             
 STB             

 -- Now taking sample from xlsx file 

 