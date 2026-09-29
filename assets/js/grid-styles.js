window.GridStyles = (function () {
    function normalizeRow(row) {
        return {
            row_width_pct: Math.max(25, Math.min(100, parseInt(row.row_width_pct, 10) || 100)),
            row_align: ['left', 'center', 'right'].indexOf(row.row_align) >= 0 ? row.row_align : 'left',
            column_gap: Math.max(0, Math.min(64, parseInt(row.column_gap, 10) || 16)),
            flush_columns: parseInt(row.flush_columns, 10) === 1 ? 1 : 0,
            border_top: parseInt(row.border_top, 10) === 1 ? 1 : 0,
            border_right: parseInt(row.border_right, 10) === 1 ? 1 : 0,
            border_bottom: parseInt(row.border_bottom, 10) === 1 ? 1 : 0,
            border_left: parseInt(row.border_left, 10) === 1 ? 1 : 0,
            border_width: Math.max(1, Math.min(8, parseInt(row.border_width, 10) || 1)),
            border_color: row.border_color || '#d1d5db',
        };
    }

    function normalizeColumn(col) {
        return {
            text_align: ['left', 'center', 'right'].indexOf(col.text_align) >= 0 ? col.text_align : 'left',
            vertical_align: ['top', 'center', 'bottom'].indexOf(col.vertical_align) >= 0 ? col.vertical_align : 'top',
            width_pct: Math.max(0, Math.min(100, parseInt(col.width_pct, 10) || 0)),
            padding_px: Math.max(0, Math.min(64, parseInt(col.padding_px, 10) || 16)),
            border_top: parseInt(col.border_top, 10) === 1 ? 1 : 0,
            border_right: parseInt(col.border_right, 10) === 1 ? 1 : 0,
            border_bottom: parseInt(col.border_bottom, 10) === 1 ? 1 : 0,
            border_left: parseInt(col.border_left, 10) === 1 ? 1 : 0,
            border_width: Math.max(1, Math.min(8, parseInt(col.border_width, 10) || 1)),
            border_color: col.border_color || '#d1d5db',
        };
    }

    function borderCss(item) {
        var width = Math.max(1, Math.min(8, parseInt(item.border_width, 10) || 1));
        var color = item.border_color || '#d1d5db';
        var styles = [];
        if (parseInt(item.border_top, 10) === 1) styles.push('border-top:' + width + 'px solid ' + color);
        if (parseInt(item.border_right, 10) === 1) styles.push('border-right:' + width + 'px solid ' + color);
        if (parseInt(item.border_bottom, 10) === 1) styles.push('border-bottom:' + width + 'px solid ' + color);
        if (parseInt(item.border_left, 10) === 1) styles.push('border-left:' + width + 'px solid ' + color);
        return styles.join(';');
    }

    function rowWrapperStyle(row) {
        var layout = normalizeRow(row);
        var styles = ['max-width:' + layout.row_width_pct + '%', 'width:100%'];
        if (layout.row_align === 'center') {
            styles.push('margin-left:auto', 'margin-right:auto');
        } else if (layout.row_align === 'right') {
            styles.push('margin-left:auto', 'margin-right:0');
        } else {
            styles.push('margin-left:0', 'margin-right:auto');
        }
        var border = borderCss(layout);
        if (border) styles.push(border);
        return styles.join(';');
    }

    function rowGridStyle(row, columns, columnCount) {
        var layout = normalizeRow(row);
        var gap = layout.flush_columns ? 0 : layout.column_gap;
        var styles = ['gap:' + gap + 'px'];
        var widths = [];
        var i;
        for (i = 1; i <= columnCount; i++) {
            widths.push(parseInt((columns[i] && columns[i].width_pct) || 0, 10));
        }
        var custom = widths.filter(function (w) { return w > 0; });
        if (custom.length === columnCount && widths.reduce(function (a, b) { return a + b; }, 0) > 0) {
            styles.push('grid-template-columns:' + widths.map(function (w) { return w + '%'; }).join(' '));
        } else if (custom.length > 0) {
            styles.push('grid-template-columns:' + widths.map(function (w) { return w > 0 ? w + '%' : '1fr'; }).join(' '));
        }
        return styles.join(';');
    }

    function columnStyle(column, isImage, flushRow) {
        var layout = normalizeColumn(column);
        var styles = [
            'text-align:' + layout.text_align,
            'display:flex',
            'flex-direction:column',
            'justify-content:' + (layout.vertical_align === 'center' ? 'center' : (layout.vertical_align === 'bottom' ? 'flex-end' : 'flex-start')),
            'height:100%',
        ];
        if (!isImage) {
            styles.push('padding:' + layout.padding_px + 'px');
        } else if (flushRow) {
            styles.push('padding:0');
        }
        var border = borderCss(layout);
        if (border) styles.push(border);
        return styles.join(';');
    }

    function rowGridClass(columnType, row) {
        var layout = normalizeRow(row);
        var cls = 'preview-row grid-row';
        if (columnType === 1) cls += ' grid-row--1';
        else if (columnType === 2 || columnType === 3) cls += ' grid-row--2';
        else cls += ' grid-row--3';
        if (layout.flush_columns || layout.column_gap === 0) cls += ' grid-row--flush';
        return cls;
    }

    function isFlushRow(row) {
        var layout = normalizeRow(row);
        return layout.flush_columns === 1 || layout.column_gap === 0;
    }

    return {
        normalizeRow: normalizeRow,
        normalizeColumn: normalizeColumn,
        rowWrapperStyle: rowWrapperStyle,
        rowGridStyle: rowGridStyle,
        columnStyle: columnStyle,
        rowGridClass: rowGridClass,
        isFlushRow: isFlushRow,
        borderCss: borderCss,
    };
})();
