select
    hsndata.sn,
    hsndata.docid,
    hsndata.restcode,
    hsndata.name,
    hsndata.hsncode,
    hsndata.taxper,
    SUM(hsndata.total) as total,
    SUM(hsndata.discamt) as discamt,
    SUM(hsndata.netamt) as netamt,
    SUM(hsndata.cgst) as cgst,
    SUM(hsndata.sgst) as sgst,
    SUM(hsndata.igst) as igst,
    hsndata.gstin,
    hsndata.party,
    hsndata.compname
from
    (
        select
            s1.restcode,
            d.name,
            im.HSNCode as hsncode,
            s2.taxper,
            s1.total / hsn_count.total_hsn as total,
            s1.discamt / hsn_count.total_hsn as discamt,
            s1.netamt / hsn_count.total_hsn as netamt,
            s1.cgst / hsn_count.total_hsn as cgst,
            s1.sgst / hsn_count.total_hsn as sgst,
            s1.igst / hsn_count.total_hsn as igst,
            SG.gstin,
            s1.party,
            SG.name as compname,
            s1.sn,
            s1.docid
        from
            sale1 as s1
            inner join stock as st on st.docid = s1.docid
            inner join depart as d on d.dcode = s1.restcode
            inner join itemmast as im on im.Code = st.item
            and im.RestCode = st.restcode
            left join sale2 as s2 on s2.docid = s1.docid
            and s2.taxper > 0
            left join subgroup as SG on SG.sub_code = s1.party
            inner join (
                SELECT
                    s.docid,
                    COUNT(DISTINCT im.HSNCode) as total_hsn
                FROM
                    stock s
                    JOIN itemmast im ON im.Code = s.item
                    AND im.RestCode = s.restcode
                GROUP BY
                    s.docid
            ) as hsn_count on hsn_count.docid = s1.docid
        where
            s1.propertyid = 169
            and s1.delflag = 'N'
            and s1.vdate between '2026-04-01'
            and '2026-04-30'
        group by
            s1.docid,
            im.HSNCode,
            s2.taxper,
            s1.restcode,
            d.name
    ) as hsndata
group by
    hsndata.restcode,
    hsndata.party,
    hsndata.hsncode,
    hsndata.taxper