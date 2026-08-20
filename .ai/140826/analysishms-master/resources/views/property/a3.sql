select
    SUM(S.RecdQty) AS QTY
from
    stock as S
    left join itemmast as I on S.Item = I.Code
    and S.restcode = I.RestCode
    left join voucher_type as VT on S.VType = VT.V_Type
where
    VT.NCAT in ('PBC', 'PBR', 'MRE', 'RQI', 'STOP', 'BKREC', 'KSREC', 'KMREC')
    and S.recdqty > 0
    and I.Code = 384169
    and VT.propertyid = 169
    and S.departcode = 'PURC169'
    and S.propertyid = 169
    and S.delflag = 'N'
limit
    1