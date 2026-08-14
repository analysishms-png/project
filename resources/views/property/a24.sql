select
    SUM(purch2.postval) as RevAmt,
    subgroup.sub_code,
    subgroup.name as subname,
    subgroup.nature,
    subgroup.group_code
from
    purch2
    left join subgroup on subgroup.sub_code = purch2.accode
where
    purch2.docid = '103PBPB ‎ ‎ 2026 ‎ ‎ ‎ ‎ 15'
    and purch2.propertyid = 103
group by
    purch2.accode