select
    SUM(amtdr - amtcr) AS Today
from
    paycharge
    left join itemcatmast on itemcatmast.Code = paycharge.paycode
    and itemcatmast.propertyid = 163
    and itemcatmast.RestCode = 'RS163'
where
    vdate = '2026-05-24'
    and paycharge.restcode = 'RS163'
    and paycode in (
        select
            RevCode
        from
            itemcatmast
        where
            CatType = 'Beverage'
    )
limit
    1