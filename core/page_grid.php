<?php

require_once __DIR__ . '/uploads.php';
require_once __DIR__ . '/page_theme.php';
require_once __DIR__ . '/grid_styles.php';

function gridColumnCount(int $columnType): int
{
    switch ($columnType) {
        case 2:
        case 3:
            return 2;
        case 4:
            return 3;
        default:
            return 1;
    }
}

function gridLayoutLabel(int $columnType): string
{
    switch ($columnType) {
        case 1:
            return '1 kolom';
        case 2:
            return '2 kolommen';
        case 3:
            return '2 kolommen (variant)';
        case 4:
            return '3 kolommen';
        default:
            return 'Onbekend layout';
    }
}

    function getPageGridRows(mysqli $con, int $pageId): array
    {
        $stmt = $con->prepare(
            'SELECT g.id AS row_id, g.rowPosition, g.columnType,
                    g.row_width_pct, g.row_align, g.column_gap, g.flush_columns,
                    g.border_top AS row_border_top, g.border_right AS row_border_right,
                    g.border_bottom AS row_border_bottom, g.border_left AS row_border_left,
                    g.border_width AS row_border_width, g.border_color AS row_border_color,
                    i.id AS info_id, i.colum, i.informatie, i.foto, i.backgroundColor,
                    i.backgroundKleur, i.bold, i.italic, i.opacity, i.kleur,
                    i.text_align, i.vertical_align, i.width_pct, i.padding_px,
                    i.border_top, i.border_right, i.border_bottom, i.border_left,
                    i.border_width, i.border_color
             FROM paginagrid g
             LEFT JOIN paginainfo i ON i.whichRow = g.id
             WHERE g.pageValue = ?
             ORDER BY g.rowPosition ASC, i.colum ASC'
        );

        if ($stmt === false) {
            return getPageGridRowsLegacy($con, $pageId);
        }

        $stmt->bind_param('i', $pageId);
        if (!$stmt->execute()) {
            $stmt->close();
            return getPageGridRowsLegacy($con, $pageId);
        }
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rowId = (int) $row['row_id'];
        if (!isset($rows[$rowId])) {
            $rows[$rowId] = array_merge([
                'id' => $rowId,
                'rowPosition' => (int) $row['rowPosition'],
                'columnType' => (int) $row['columnType'],
                'columns' => [],
            ], defaultRowLayout(), [
                'row_width_pct' => (int) ($row['row_width_pct'] ?? 100),
                'row_align' => $row['row_align'] ?? 'left',
                'column_gap' => (int) ($row['column_gap'] ?? 16),
                'flush_columns' => (int) ($row['flush_columns'] ?? 0),
                'border_top' => (int) ($row['row_border_top'] ?? 0),
                'border_right' => (int) ($row['row_border_right'] ?? 0),
                'border_bottom' => (int) ($row['row_border_bottom'] ?? 0),
                'border_left' => (int) ($row['row_border_left'] ?? 0),
                'border_width' => (int) ($row['row_border_width'] ?? 1),
                'border_color' => $row['row_border_color'] ?? '#d1d5db',
            ]);
        }

        if ($row['info_id'] !== null) {
            $rows[$rowId]['columns'][(int) $row['colum']] = $row;
        }
    }

    $stmt->close();

    return array_values($rows);
}

function getPageGridRowsLegacy(mysqli $con, int $pageId): array
{
    $stmt = $con->prepare(
        'SELECT g.id AS row_id, g.rowPosition, g.columnType,
                i.id AS info_id, i.colum, i.informatie, i.foto, i.backgroundColor,
                i.backgroundKleur, i.bold, i.italic, i.opacity, i.kleur
         FROM paginagrid g
         LEFT JOIN paginainfo i ON i.whichRow = g.id
         WHERE g.pageValue = ?
         ORDER BY g.rowPosition ASC, i.colum ASC'
    );
    $stmt->bind_param('i', $pageId);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rowId = (int) $row['row_id'];
        if (!isset($rows[$rowId])) {
            $rows[$rowId] = array_merge([
                'id' => $rowId,
                'rowPosition' => (int) $row['rowPosition'],
                'columnType' => (int) $row['columnType'],
                'columns' => [],
            ], defaultRowLayout());
        }

        if ($row['info_id'] !== null) {
            $rows[$rowId]['columns'][(int) $row['colum']] = array_merge($row, defaultColumnLayout());
        }
    }

    $stmt->close();

    return array_values($rows);
}

function createEmptyColumnInfo(mysqli $con, int $rowId, int $columnNumber): void
{
    $stmt = $con->prepare(
        'INSERT INTO paginainfo (
            whichRow, colum, informatie, foto, backgroundColor, backgroundKleur,
            bold, italic, opacity, kleur, text_align, vertical_align, width_pct, padding_px
        ) VALUES (?, ?, ?, 0, 0, ?, 0, 0, 10, ?, ?, ?, 0, 16)'
    );
    $empty = '';
    $bg = '#f9fafb';
    $text = '#111827';
    $textAlign = 'left';
    $verticalAlign = 'top';
    $stmt->bind_param('iisssss', $rowId, $columnNumber, $empty, $bg, $text, $textAlign, $verticalAlign);
    $stmt->execute();
    $stmt->close();
}

function addGridRow(mysqli $con, int $pageId, int $columnType): int
{
    $columnType = max(1, min(4, $columnType));
    $posStmt = $con->prepare('SELECT COALESCE(MAX(rowPosition), 0) + 1 AS next_pos FROM paginagrid WHERE pageValue = ?');
    $posStmt->bind_param('i', $pageId);
    $posStmt->execute();
    $nextPos = (int) $posStmt->get_result()->fetch_assoc()['next_pos'];
    $posStmt->close();

    $stmt = $con->prepare('INSERT INTO paginagrid (pageValue, rowPosition, columnType) VALUES (?, ?, ?)');
    $stmt->bind_param('iii', $pageId, $nextPos, $columnType);
    $stmt->execute();
    $rowId = (int) $con->insert_id;
    $stmt->close();

    for ($i = 1; $i <= gridColumnCount($columnType); $i++) {
        createEmptyColumnInfo($con, $rowId, $i);
    }

    return $rowId;
}

function deleteGridRow(mysqli $con, int $rowId, int $pageId): void
{
    $stmt = $con->prepare('DELETE FROM paginagrid WHERE id = ? AND pageValue = ?');
    $stmt->bind_param('ii', $rowId, $pageId);
    $stmt->execute();
    $stmt->close();
}

function moveGridRow(mysqli $con, int $rowId, int $pageId, string $direction): void
{
    $stmt = $con->prepare('SELECT id, rowPosition FROM paginagrid WHERE pageValue = ? ORDER BY rowPosition ASC');
    $stmt->bind_param('i', $pageId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $index = null;
    foreach ($rows as $i => $row) {
        if ((int) $row['id'] === $rowId) {
            $index = $i;
            break;
        }
    }

    if ($index === null) {
        return;
    }

    $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;
    if ($swapIndex < 0 || $swapIndex >= count($rows)) {
        return;
    }

    $currentPos = (int) $rows[$index]['rowPosition'];
    $swapPos = (int) $rows[$swapIndex]['rowPosition'];
    $currentId = (int) $rows[$index]['id'];
    $swapId = (int) $rows[$swapIndex]['id'];

    $stmt = $con->prepare('UPDATE paginagrid SET rowPosition = ? WHERE id = ?');
    $stmt->bind_param('ii', $swapPos, $currentId);
    $stmt->execute();
    $stmt->close();

    $stmt = $con->prepare('UPDATE paginagrid SET rowPosition = ? WHERE id = ?');
    $stmt->bind_param('ii', $currentPos, $swapId);
    $stmt->execute();
    $stmt->close();
}

function saveGridColumn(mysqli $con, int $infoId, int $pageId, array $data, ?array $file = null, bool $allowEmpty = false): array
{
    return saveGridColumnData($con, $infoId, $pageId, $data, $file, $allowEmpty);
}

function saveGridColumnData(mysqli $con, int $infoId, int $pageId, array $data, ?array $file = null, bool $allowEmpty = false): array
{
    $check = $con->prepare(
        'SELECT i.id FROM paginainfo i
         INNER JOIN paginagrid g ON g.id = i.whichRow
         WHERE i.id = ? AND g.pageValue = ?'
    );
    $check->bind_param('ii', $infoId, $pageId);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$exists) {
        return ['success' => false, 'message' => 'Kolom niet gevonden.'];
    }

    $isImage = isset($data['foto']) && (int) $data['foto'] === 1;
    $informatie = trim($data['informatie'] ?? '');
    $kleur = validateHexColor($data['kleur'] ?? '#111827', '#111827');
    $backgroundKleur = validateHexColor($data['backgroundKleur'] ?? '#f9fafb', '#f9fafb');
    $bold = !empty($data['bold']) ? 1 : 0;
    $italic = !empty($data['italic']) ? 1 : 0;
    $opacity = max(0, min(10, (int) ($data['opacity'] ?? 10)));
    $backgroundColor = $backgroundKleur !== '#f9fafb' ? 1 : 0;

    if ($isImage && $file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = uploadPageImage($file);
        if (!$upload['success']) {
            return $upload;
        }
        $informatie = $upload['filename'];
    }

    if ($isImage && $informatie === '' && !$allowEmpty) {
        return ['success' => false, 'message' => 'Upload een afbeelding of schakel terug naar tekst.'];
    }

    if (!$isImage && $informatie === '' && !$allowEmpty) {
        return ['success' => false, 'message' => 'Vul tekst in voor deze kolom.'];
    }

    $foto = $isImage ? 1 : 0;
    $textAlign = sanitizeAlign($data['text_align'] ?? 'left', ['left', 'center', 'right'], 'left');
    $verticalAlign = sanitizeAlign($data['vertical_align'] ?? 'top', ['top', 'center', 'bottom'], 'top');
    $widthPct = max(0, min(100, (int) ($data['width_pct'] ?? 0)));
    $paddingPx = max(0, min(64, (int) ($data['padding_px'] ?? 16)));
    $colBorderTop = !empty($data['border_top']) ? 1 : 0;
    $colBorderRight = !empty($data['border_right']) ? 1 : 0;
    $colBorderBottom = !empty($data['border_bottom']) ? 1 : 0;
    $colBorderLeft = !empty($data['border_left']) ? 1 : 0;
    $colBorderWidth = max(1, min(8, (int) ($data['border_width'] ?? 1)));
    $colBorderColor = validateHexColor($data['border_color'] ?? '#d1d5db', '#d1d5db');

    $stmt = $con->prepare(
        'UPDATE paginainfo SET informatie = ?, foto = ?, backgroundColor = ?, backgroundKleur = ?,
         bold = ?, italic = ?, opacity = ?, kleur = ?, text_align = ?, vertical_align = ?,
         width_pct = ?, padding_px = ?, border_top = ?, border_right = ?, border_bottom = ?,
         border_left = ?, border_width = ?, border_color = ? WHERE id = ?'
    );
    $stmt->bind_param(
        'siisiisssiiiiiiiisi',
        $informatie,
        $foto,
        $backgroundColor,
        $backgroundKleur,
        $bold,
        $italic,
        $opacity,
        $kleur,
        $textAlign,
        $verticalAlign,
        $widthPct,
        $paddingPx,
        $colBorderTop,
        $colBorderRight,
        $colBorderBottom,
        $colBorderLeft,
        $colBorderWidth,
        $colBorderColor,
        $infoId
    );
    $stmt->execute();
    $stmt->close();

    return ['success' => true, 'message' => 'Kolom opgeslagen.', 'informatie' => $informatie, 'foto' => $foto];
}

function saveGridRowSettings(mysqli $con, int $rowId, int $pageId, array $data): bool
{
    $layout = normalizeRowLayout($data);

    $stmt = $con->prepare(
        'UPDATE paginagrid SET row_width_pct = ?, row_align = ?, column_gap = ?, flush_columns = ?,
         border_top = ?, border_right = ?, border_bottom = ?, border_left = ?,
         border_width = ?, border_color = ?
         WHERE id = ? AND pageValue = ?'
    );
    $stmt->bind_param(
        'isiiiiiiisii',
        $layout['row_width_pct'],
        $layout['row_align'],
        $layout['column_gap'],
        $layout['flush_columns'],
        $layout['border_top'],
        $layout['border_right'],
        $layout['border_bottom'],
        $layout['border_left'],
        $layout['border_width'],
        $layout['border_color'],
        $rowId,
        $pageId
    );
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function reorderGridRowsByIds(mysqli $con, int $pageId, array $rowIds): void
{
    foreach ($rowIds as $index => $rowId) {
        $position = $index + 1;
        $rowId = (int) $rowId;
        $stmt = $con->prepare('UPDATE paginagrid SET rowPosition = ? WHERE id = ? AND pageValue = ?');
        $stmt->bind_param('iii', $position, $rowId, $pageId);
        $stmt->execute();
        $stmt->close();
    }
}

function saveEntirePageLayout(mysqli $con, int $pageId, array $payload, array $files): array
{
    if (!savePageTheme($con, $pageId, $payload['theme'] ?? [])) {
        return ['success' => false, 'message' => 'Paginakleuren opslaan mislukt.'];
    }

    $rowOrder = array_map('intval', $payload['row_order'] ?? []);
    if (!empty($rowOrder)) {
        reorderGridRowsByIds($con, $pageId, $rowOrder);
    }

    $updatedImages = [];

    foreach ($payload['columns'] ?? [] as $columnPayload) {
        $infoId = (int) ($columnPayload['info_id'] ?? 0);
        if ($infoId <= 0) {
            continue;
        }

        $fileKey = 'image_' . $infoId;
        $file = isset($files[$fileKey]) ? $files[$fileKey] : null;

        $data = [
            'foto' => (int) ($columnPayload['foto'] ?? 0),
            'informatie' => $columnPayload['informatie'] ?? '',
            'kleur' => $columnPayload['kleur'] ?? '#111827',
            'backgroundKleur' => $columnPayload['backgroundKleur'] ?? '#f9fafb',
            'opacity' => (int) ($columnPayload['opacity'] ?? 10),
            'text_align' => $columnPayload['text_align'] ?? 'left',
            'vertical_align' => $columnPayload['vertical_align'] ?? 'top',
            'width_pct' => (int) ($columnPayload['width_pct'] ?? 0),
            'padding_px' => (int) ($columnPayload['padding_px'] ?? 16),
            'border_width' => (int) ($columnPayload['border_width'] ?? 1),
            'border_color' => $columnPayload['border_color'] ?? '#d1d5db',
        ];

        if (!empty($columnPayload['bold'])) {
            $data['bold'] = 1;
        }
        if (!empty($columnPayload['italic'])) {
            $data['italic'] = 1;
        }
        if (!empty($columnPayload['border_top'])) {
            $data['border_top'] = 1;
        }
        if (!empty($columnPayload['border_right'])) {
            $data['border_right'] = 1;
        }
        if (!empty($columnPayload['border_bottom'])) {
            $data['border_bottom'] = 1;
        }
        if (!empty($columnPayload['border_left'])) {
            $data['border_left'] = 1;
        }

        $result = saveGridColumnData($con, $infoId, $pageId, $data, $file, true);
        if (!$result['success']) {
            return $result;
        }

        if ((int) ($result['foto'] ?? 0) === 1 && !empty($result['informatie'])) {
            $updatedImages[$infoId] = $result['informatie'];
        }
    }

    foreach ($payload['rows'] ?? [] as $rowPayload) {
        $rowId = (int) ($rowPayload['row_id'] ?? 0);
        if ($rowId <= 0) {
            continue;
        }

        if (!saveGridRowSettings($con, $rowId, $pageId, $rowPayload)) {
            return ['success' => false, 'message' => 'Kolomsectie-instellingen opslaan mislukt.'];
        }
    }

    return [
        'success' => true,
        'message' => 'Pagina opgeslagen.',
        'images' => $updatedImages,
    ];
}

function getPageForBuilder(mysqli $con, int $pageId): ?array
{
    $stmt = $con->prepare(
        'SELECT id, titel, slug, body_bg, page_bg, page_text_color, footer_bg, footer_text
         FROM paginas WHERE id = ?'
    );
    $stmt->bind_param('i', $pageId);
    $stmt->execute();
    $page = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $page ?: null;
}
