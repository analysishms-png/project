select
    SUM(S.amount) AS RevAmt,
    S.revcode,
    MAX(S.vdate) AS VDate,
    MAX(R.name) AS Revenue,
    MAX(S.suncode) AS SundryCode,
    MAX(R.ac_code) AS ACode,
    MAX(R.payable_ac) AS PCode,
    MAX(R.unregistered_ac) AS UCode,
    MAX(R.field_type) AS FieldType,
    MAX(ST.calcsign) AS CSign
from
    suntran as S
    left join revmast as R on S.revcode = R.rev_code
    left join depart as D on S.restcode = D.dcode
    left join sundrytype as ST on S.sunappdate = ST.appdate
    and ST.vtype = S.restcode
    and S.suncode = ST.sundry_code
where
    S.revcode is not null
    and S.revcode <> ''
    and S.suncode != 10103
    and S.docid = '103PBPB‎ ‎ 2026‎ ‎ ‎ ‎ 15'
group by
    S.revcode
order by
    S.restcode asc