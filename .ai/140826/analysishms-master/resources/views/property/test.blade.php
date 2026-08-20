<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>jQuery Overlay Lock</title>
    <style>
        /* * {
            box-sizing: border-box
        } */

        /* body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: #f6f7fb
        } */

        /* .page {
            max-width: 920px;
            margin: 0 auto;
            padding: 16px
        } */

        /* .card {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 12px;
            padding: 16px
        } */

        /* .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 12px
        } */

        /* .col-12 {
            grid-column: span 12
        } */

        /* .col-6 {
            grid-column: span 6
        } */

        /* @media(max-width:700px) {
            .col-6 {
                grid-column: span 12
            }
        } */

        .f {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(0, 0, 0, .18);
            border-radius: 10px;
            font-size: 14px;
            background: #fff;
            outline: none
        }

        .f:focus {
            border-color: rgba(0, 0, 0, .45)
        }

        .ol-wrap {
            position: relative;
            width: 100%
        }

        .ol-cover {
            position: absolute;
            inset: 0;
            border-radius: 10px;
            background: transparent;
            z-index: 50;
            cursor: not-allowed;
            user-select: none;
        }

        .ol-badge {
            position: absolute;
            top: -10px;
            right: 10px;
            font-size: 12px;
            color: #495057;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .12);
            border-radius: 999px;
            padding: 6px 10px;
            line-height: 1;
            max-width: calc(100% - 20px);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="card">
            <div class="grid">
                <div class="col-6">
                    <input class="f" id="name" type="text" value="Sagar Rajput">
                </div>
                <div class="col-6">
                    <select class="f" id="role">
                        <option>Admin</option>
                        <option selected>Staff</option>
                        <option>Student</option>
                    </select>
                </div>
                <div class="col-12">
                    <textarea class="f" id="note" rows="3">Try typing here (will be locked)</textarea>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        function overlayLock(el, text) {
            const $el = $(el);
            if (!$el.length) return;

            if ($el.parent('.ol-wrap').length === 0) {
                $el.wrap('<div class="ol-wrap"></div>');
            }
            const $wrap = $el.parent('.ol-wrap');

            $wrap.find('.ol-cover').remove();

            const $cover = $('<div class="ol-cover" aria-hidden="true"></div>');
            const $badge = $('<div class="ol-badge"></div>').text(text || 'Locked');
            $cover.append($badge);

            $cover.on('mousedown touchstart pointerdown click keydown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });

            $wrap.append($cover);

            $el.attr({
                'aria-disabled': 'true',
                'tabindex': '-1'
            }).blur();
        }

        function overlayUnlock(el) {
            const $el = $(el);
            if (!$el.length) return;
            const $wrap = $el.parent('.ol-wrap');
            $wrap.find('.ol-cover').remove();
            $el.removeAttr('aria-disabled').removeAttr('tabindex');
        }

        $(function() {
            overlayLock('#role', 'Cant Change this');
            overlayLock('#note', 'Cant Change this');
        });
    </script>
</body>

</html>
