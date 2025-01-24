create trigger 
`0_inventory_scanned_date_created` before insert
    on `0_inventory_scanned`
    for each row 
    set new.`scandate` = now();
