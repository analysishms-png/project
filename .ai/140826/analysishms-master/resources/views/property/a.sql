SELECT
    B.docid,
    B.roomno,
    B.VDate,
    B.VTime,
    B.paycode,
    B.RevenueName,
    B.hsn_code,
    B.ChargeAmt,
    IFNULL(
        (
            SELECT
                SUM(PC.amtdr - PC.amtcr)
            FROM
                paycharge PC
            WHERE
                PC.folionodocid = B.docid
                AND PC.VDate = B.VDate
                AND PC.VTime = B.VTime
                AND PC.paycode = 'CGSS135'
                AND PC.propertyid = 135
        ),
        0
    ) AS CGSTAmt,
    IFNULL(
        (
            SELECT
                SUM(PC.amtdr - PC.amtcr)
            FROM
                paycharge PC
            WHERE
                PC.folionodocid = B.docid
                AND PC.VDate = B.VDate
                AND PC.VTime = B.VTime
                AND PC.paycode = 'SGSS135'
                AND PC.propertyid = 135
        ),
        0
    ) AS SGSTAmt,
    (
        B.ChargeAmt + IFNULL(
            (
                SELECT
                    SUM(PC.amtdr - PC.amtcr)
                FROM
                    paycharge PC
                WHERE
                    PC.folionodocid = B.docid
                    AND PC.VDate = B.VDate
                    AND PC.VTime = B.VTime
                    AND PC.paycode = 'CGSS135'
                    AND PC.propertyid = 135
            ),
            0
        ) + IFNULL(
            (
                SELECT
                    SUM(PC.amtdr - PC.amtcr)
                FROM
                    paycharge PC
                WHERE
                    PC.folionodocid = B.docid
                    AND PC.VDate = B.VDate
                    AND PC.VTime = B.VTime
                    AND PC.paycode = 'SGSS135'
                    AND PC.propertyid = 135
            ),
            0
        )
    ) AS GrossAmt
FROM
    (
        SELECT
            PC.folionodocid AS docid,
            MAX(PC.roomno) AS roomno,
            PC.VDate,
            PC.VTime,
            PC.paycode,
            RM.name AS RevenueName,
            RM.hsn_code,
            SUM(PC.amtdr - PC.amtcr) AS ChargeAmt
        FROM
            paycharge PC
            INNER JOIN revmast RM ON RM.rev_code = PC.paycode
            AND RM.propertyid = PC.propertyid
        WHERE
            PC.propertyid = 135
            AND PC.folionodocid = '135CHK‎ ‎ 2025‎ ‎ ‎ ‎ 145'
            AND PC.sno1 = 1
            AND PC.FolioNo <> 0
            AND (
                PC.MODESET <> 'S'
                OR PC.MODESET IS NULL
            )
            AND (PC.amtdr - PC.amtcr) <> 0
            AND RM.field_type IN ('C', 'T')
            AND PC.paycode NOT IN (
                'DISC135',
                'ROFF135',
                'TOUT135',
                'CGSS135',
                'SGSS135'
            )
        GROUP BY
            PC.folionodocid,
            PC.VDate,
            PC.VTime,
            PC.paycode,
            RM.name,
            RM.hsn_code
    ) AS B
ORDER BY
    B.docid,
    B.VDate,
    B.VTime,
    B.RevenueName;