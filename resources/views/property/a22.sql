select
    paycharge.*,
    revmast.field_type,
    revmast.nature as revnature
from
    paycharge
    left join revmast on revmast.rev_code = paycharge.paycode
where
    paycharge.propertyid = 158
    and paycharge.folionodocid = 158CHK ‎ ‎ 2025 ‎ ‎ ‎ ‎ 64
    and paycharge.msno1 = 2
    and paycharge.modeset is null
order by
    paycharge.vdate asc,
    paycharge.vno asc,
    paycharge.sno1 asc,
    paycharge.sno asc,
    paycharge.roomno asc