select
    suntran.dispname,
    SUM(suntran.amount) AS RevAmt,
    MAX(suncode) as SundryCode,
    subgroup.sub_code as subcode,
    subgroup.name as subname,
    subgroup.group_code as accode,
    subgroup.nature as subnature
from
    suntran
    left join revmast on suntran.revcode = revmast.rev_code
    left join subgroup on subgroup.sub_code = 458103
    left join depart on suntran.restcode = depart.dcode
where
    suncode = 10103
    and docid = '103PBPB‎ ‎ 2026‎ ‎ ‎ ‎ 15'
    and suntran.propertyid = 103
group by
    restcode,
    revcode
order by
    restcode asc
limit
    1