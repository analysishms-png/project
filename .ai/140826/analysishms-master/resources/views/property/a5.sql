SELECT
    hsndata.sn,
    hsndata.docid,
    hsndata.restcode,
    hsndata.name,
    hsndata.hsncode,
    hsndata.taxper,
    SUM(hsndata.total) AS total,
    SUM(hsndata.discamt) AS discamt,
    SUM(hsndata.netamt) AS netamt,
    SUM(hsndata.cgst) AS cgst,
    SUM(hsndata.sgst) AS sgst,
    SUM(hsndata.igst) AS igst,
    hsndata.gstin,
    hsndata.party,
    hsndata.compname
FROM (
    SELECT
        s1.restcode,
        d.name,
        im.HSNCode AS hsncode,
        s2.taxper,

        s1.total / hsn_count.total_hsn AS total,
        s1.discamt / hsn_count.total_hsn AS discamt,
        s1.netamt / hsn_count.total_hsn AS netamt,
        s1.cgst / hsn_count.total_hsn AS cgst,
        s1.sgst / hsn_count.total_hsn AS sgst,
        s1.igst / hsn_count.total_hsn AS igst,

        SG.gstin,
        s1.party,
        SG.name AS compname,
        s1.sn,
        s1.docid

    FROM sale1 s1

    INNER JOIN stock st
        ON st.docid = s1.docid

    INNER JOIN depart d
        ON d.dcode = s1.restcode

    INNER JOIN itemmast im
        ON im.Code = st.item
        AND im.RestCode = st.restcode

    LEFT JOIN (
        SELECT
            docid,
            MAX(taxper) AS taxper
        FROM sale2
        WHERE taxper > 0
        GROUP BY docid
    ) s2
        ON s2.docid = s1.docid

    LEFT JOIN subgroup SG
        ON SG.sub_code = s1.party

    INNER JOIN (
        SELECT
            s.docid,
            COUNT(DISTINCT im.HSNCode) AS total_hsn
        FROM stock s
        INNER JOIN itemmast im
            ON im.Code = s.item
            AND im.RestCode = s.restcode
        GROUP BY s.docid
    ) hsn_count
        ON hsn_count.docid = s1.docid

    WHERE
        s1.propertyid = 169
        AND s1.delflag = 'N'
        AND s1.vdate BETWEEN '2026-04-01' AND '2026-04-30'

    GROUP BY
        s1.docid,
        im.HSNCode,
        s2.taxper,
        s1.restcode,
        d.name

) hsndata

GROUP BY
    hsndata.restcode,
    hsndata.party,
    hsndata.hsncode,
    hsndata.taxper;