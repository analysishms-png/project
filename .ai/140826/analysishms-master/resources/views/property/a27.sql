select
    sum(suntran.amount) as aggregate
from
    suntran
    left join sundrytype on sundrytype.vtype = suntran.restcode
    and sundrytype.sundry_code = suntran.suncode
    and sundrytype.propertyid = 103
where
    suntran.propertyid = 103
    and not sundrytype.nature = 'Discount'
    and suntran.sno not in (1, 9)
    and suntran.revcode = ''
    and suntran.docid = '103PBPB ‎ ‎ 2026 ‎ ‎ ‎ ‎ 14'