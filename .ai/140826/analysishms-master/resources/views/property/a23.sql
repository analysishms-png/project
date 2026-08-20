select
    T.*,
    0 AS Exempted,
    0 AS NonGST,
    S.BillTotal,
    CONCAT('BCNT/24-25/', T.Bill_No) AS BillNo,
    TRIM(IFNULL(SG.GSTIN, '')) AS GSTIN,
    TRIM(IFNULL(SG.Name, '')) AS CompanyName,
    IFNULL(GF.Company, '') AS Company,
    TRIM(IFNULL(TA.GSTIN, '')) AS EGSTIN,
    IFNULL(GF.TravelAgent, '') AS TravelAgent,
    CASE
        WHEN (
            T.BaseValue <> 0
            OR T.TaxAmtOther <> 0
        ) THEN 1
        ELSE 0
    END AS Cond1,
    CASE
        WHEN (T.NilRated <> 0) THEN 1
        ELSE 0
    END AS Cond2
from
    (
        SELECT
            R.FolionoDocid,
            R.Foliono,
            R.Bill_Date,
            R.Bill_No,
            R.AmtDr,
            R.BASEVALUE,
            R.TAXPER,
            0 AS TaxAmtOther,
            0 AS TaxPerOther,
            0 AS BaseValueOther,
            0 AS NilRated
        FROM
            (
                SELECT
                    MAX(Q.FolionoDocid) AS FolionoDocid,
                    MAX(Q.Foliono) AS Foliono,
                    MAX(Q.SettleDate) AS Bill_Date,
                    MAX(Q.Bill_No) AS Bill_No,
                    SUM(Q.AmtDr) AS AmtDr,
                    SUM(Q.BASEVALUE) AS BASEVALUE,
                    MAX(Q.TAXPER) AS TAXPER
                FROM
                    (
                        SELECT
                            P.FolionoDocid,
                            P.FolioNo,
                            P.SettleDate,
                            MAX(P.billno) AS Bill_No,
                            P.vtype AS VoucherType,
                            SUM(P.AmtDr - P.AmtCr) AS AmtDr,
                            MAX(
                                CASE
                                    WHEN (P.AmtDr - P.AmtCr) > 0 THEN P.OnAmt
                                    ELSE - P.OnAmt
                                END
                            ) AS BASEVALUE,
                            SUM(P.TaxPer) AS TAXPER
                        FROM
                            paycharge P
                            LEFT JOIN revmast ON P.PayCode = revmast.rev_code
                            LEFT JOIN sundrymast ON revmast.sundry = sundrymast.sundry_code
                        WHERE
                            sundrymast.nature IN('CGST', 'SGST', 'IGST')
                            AND P.roomtype = 'RO'
                            AND P.propertyid = 134
                            AND P.settledate BETWEEN '2026-01-03'
                            AND '2026-01-03'
                            AND (P.amtdr - P.amtcr) <> 0
                            AND P.foliono <> 0
                            AND P.settledate IS NOT NULL
                            AND IFNULL(P.billno, '') <> ''
                        GROUP BY
                            P.docid,
                            P.folionodocid,
                            P.foliono,
                            P.settledate,
                            P.taxper
                    ) Q
                GROUP BY
                    folionodocid,
                    taxper
            ) R
    ) as T
    inner join (
        SELECT
            FolionoDocid,
            SUM(amtdr - amtcr) AS BillTotal
        FROM
            paycharge
        WHERE
            (
                DocId IN (
                    SELECT
                        docid
                    FROM
                        paycharge
                    WHERE
                        paycode = 'DISC134'
                )
                OR (
                    AmtDr <> 0
                    AND IFNULL(Modeset, '') <> 'S'
                )
                OR (
                    Modeset = 'S'
                    AND PayCode = 'ROFF134'
                )
            )
            AND RoomType = 'RO'
            AND propertyid = 134
            AND paycode NOT IN ('TOUT134')
            AND settledate BETWEEN '2026-01-03'
            AND '2026-01-03'
            AND FolioNo <> 0
        GROUP BY
            FolioNoDocId
    ) as S on T.folionodocid = S.FolionoDocId
    inner join guestfolio as GF on T.folionodocid = GF.DocId
    left join subgroup as SG on GF.Company = SG.sub_code
    left join subgroup as TA on GF.TravelAgent = TA.sub_code
order by
    CAST(T.Bill_No AS UNSIGNED),
    T.FolionoDocid,
    T.TAXPER DESC