select
    S1.docId as FolioNoDocId,
    S1.vdate as Bill_Date,
    S1.vtype,
    S1.VNo as BillNo,
    S1.party as GuestName,
    S1.netamt as BillTotal,
    IFNULL(Q.TAXAMT, S1.taxable) as TAXAMT,
    Q.BASEVALUE,
    Q.TAXPER,
    IFNULL(Q.TaxAmtOther, 0) as TaxAmtOther,
    IFNULL(Q.Exempted, 0) as Exempted,
    IFNULL(Q.NonGST, 0) as NonGST,
    0 as NilRated,
    TRIM(IFNULL(SG.gstin, '')) as GSTIN,
    TRIM(IFNULL(SG.name, '')) as CompanyName,
    IFNULL(HB.companycode, '') as Company,
    TRIM(IFNULL(BA.GSTIN, '')) as EGSTIN,
    IFNULL(HB.bookingagent, '') as TravelAgent,
    CASE
        WHEN (
            IFNULL(Q.BASEVALUE, 0) <> 0
            OR IFNULL(Q.TaxAmtOther, 0) <> 0
        ) THEN 1
        ELSE 0
    END as Cond1,
    CASE
        WHEN (
            IFNULL(Q.Exempted, 0) <> 0
            OR IFNULL(Q.NonGST, 0) <> 0
        ) THEN 1
        ELSE 0
    END as Cond2
from
    hallsale1 as S1
    left join (
        select
            H2.DocId,
            SUM(
                CASE
                    WHEN SM.nature IN ("CGST", "SGST", "IGST") THEN H2.TaxAmt
                    ELSE 0
                END
            ) AS TAXAMT,
            SUM(
                CASE
                    WHEN SM.nature IN ("CGST", "SGST", "IGST") THEN H2.BaseValue
                    ELSE 0
                END
            ) AS BASEVALUE,
            MAX(
                CASE
                    WHEN SM.nature IN ("CGST", "SGST", "IGST") THEN H2.TaxPer
                    ELSE 0
                END
            ) AS TAXPER,
            SUM(
                CASE
                    WHEN SM.nature IN ("Luxury Tax", "Sale Tax", "Service Tax") THEN H2.TaxAmt
                    ELSE 0
                END
            ) AS TaxAmtOther,
            SUM(
                CASE
                    WHEN SM.nature IN ("Luxury Tax", "Sale Tax", "Service Tax") THEN H2.BaseValue
                    ELSE 0
                END
            ) AS BaseValueOther,
            SUM(
                CASE
                    WHEN SM.nature IN ("Luxury Tax", "Sale Tax", "Service Tax") THEN H2.TaxPer
                    ELSE 0
                END
            ) AS TaxPerOther,
            0 AS Exempted,
            0 AS NonGST
        from
            hallsale2 as H2
            inner join revmast as R on R.rev_code = H2.taxcode
            left join sundrymast as SM on R.sundry = SM.sundry_code
        where
            H2.propertyid = 171
            and H2.vdate between '2026-04-01'
            and '2026-04-21'
            and R.field_type = 'T'
        group by
            H2.DocId
    ) as Q on Q.docId = S1.docId
    inner join hallbook as HB on HB.docid = S1.bookdocid
    left join subgroup as SG on SG.sub_code = HB.companycode
    left join subgroup as BA on BA.sub_code = HB.bookingagent
where
    S1.vdate between '2026-04-01'
    and '2026-04-21'
    and S1.propertyid = 171
order by
    S1.vtype asc,
    S1.vno asc,
    Q.taxper desc