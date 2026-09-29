<?php

function builderBorderFields(string $scope, int $rowId, array $item, int $colNum = 0): string
{
    $colAttr = $colNum > 0 ? ' data-col="' . $colNum . '"' : '';
    $checked = fn(string $side) => !empty($item[$side]) ? ' checked' : '';

    return '
        <div class="layout-block">
            <p class="layout-block__label">Randen</p>
            <div class="inputField inputField--checkbox border-sides">
                <label><input type="checkbox" data-scope="' . $scope . '" data-field="border_top" data-row-id="' . $rowId . '"' . $colAttr . ' value="1"' . $checked('border_top') . '> Boven</label>
                <label><input type="checkbox" data-scope="' . $scope . '" data-field="border_right" data-row-id="' . $rowId . '"' . $colAttr . ' value="1"' . $checked('border_right') . '> Rechts</label>
                <label><input type="checkbox" data-scope="' . $scope . '" data-field="border_bottom" data-row-id="' . $rowId . '"' . $colAttr . ' value="1"' . $checked('border_bottom') . '> Onder</label>
                <label><input type="checkbox" data-scope="' . $scope . '" data-field="border_left" data-row-id="' . $rowId . '"' . $colAttr . ' value="1"' . $checked('border_left') . '> Links</label>
            </div>
            <div class="color-grid">
                <div class="inputField">
                    <label>Dikte (px)</label>
                    <input type="number" min="1" max="8" data-scope="' . $scope . '" data-field="border_width" data-row-id="' . $rowId . '"' . $colAttr . ' value="' . (int) ($item['border_width'] ?? 1) . '">
                </div>
                <div class="inputField">
                    <label>Kleur</label>
                    <input type="color" data-scope="' . $scope . '" data-field="border_color" data-row-id="' . $rowId . '"' . $colAttr . ' value="' . testInput($item['border_color'] ?? '#d1d5db') . '">
                </div>
            </div>
        </div>';
}

function builderRowSettingsHtml(array $row): string
{
    $rowId = (int) $row['id'];
    $layout = normalizeRowLayout($row);
    $gapDisabled = !empty($layout['flush_columns']) ? ' disabled' : '';
    $widths = [100, 90, 75, 60, 50];
    $widthOptions = '';
    foreach ($widths as $width) {
        $selected = (int) $layout['row_width_pct'] === $width ? ' selected' : '';
        $widthOptions .= '<option value="' . $width . '"' . $selected . '>' . $width . '%</option>';
    }

    $alignOptions = '';
    foreach (['left' => 'Links', 'center' => 'Midden', 'right' => 'Rechts'] as $value => $label) {
        $selected = $layout['row_align'] === $value ? ' selected' : '';
        $alignOptions .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }

    return '
        <div class="admin-form builder-row-settings">
            <h4>Kolomsectie instellingen</h4>
            <div class="color-grid">
                <div class="inputField">
                    <label>Breedte (%)</label>
                    <select data-scope="row" data-field="row_width_pct" data-row-id="' . $rowId . '">' . $widthOptions . '</select>
                </div>
                <div class="inputField">
                    <label>Uitlijning sectie</label>
                    <select data-scope="row" data-field="row_align" data-row-id="' . $rowId . '">' . $alignOptions . '</select>
                </div>
                <div class="inputField">
                    <label>Ruimte tussen kolommen (px)</label>
                    <input type="number" min="0" max="64" data-scope="row" data-field="column_gap" data-row-id="' . $rowId . '" value="' . (int) $layout['column_gap'] . '"' . $gapDisabled . '>
                </div>
            </div>
            <div class="inputField inputField--checkbox">
                <label>
                    <input type="checkbox" data-scope="row" data-field="flush_columns" data-row-id="' . $rowId . '" value="1"' . (!empty($layout['flush_columns']) ? ' checked' : '') . '>
                    Geen ruimte tussen kolommen (afbeeldingen flush)
                </label>
            </div>
            ' . builderBorderFields('row', $rowId, $layout) . '
        </div>';
}

function builderColumnLayoutHtml(array $row, int $colNum, array $col, bool $isImage): string
{
    $rowId = (int) $row['id'];
    $layout = normalizeColumnLayout($col);
    $textOnlyHidden = $isImage ? ' hidden' : '';

    $widthOptions = '';
    foreach ([0 => 'Automatisch (gelijk)', 25 => '25%', 33 => '33%', 50 => '50%', 66 => '66%', 75 => '75%', 100 => '100%'] as $value => $label) {
        $selected = (int) $layout['width_pct'] === $value ? ' selected' : '';
        $widthOptions .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }

    $textAlignOptions = '';
    foreach (['left', 'center', 'right'] as $value) {
        $selected = $layout['text_align'] === $value ? ' selected' : '';
        $textAlignOptions .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
    }

    $verticalAlignOptions = '';
    foreach (['top', 'center', 'bottom'] as $value) {
        $selected = $layout['vertical_align'] === $value ? ' selected' : '';
        $verticalAlignOptions .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
    }

    return '
        <div class="layout-section">
            <h5>Layout &amp; randen</h5>
            <div class="color-grid">
                <div class="inputField">
                    <label>Kolombreedte</label>
                    <select data-scope="column" data-field="width_pct" data-row-id="' . $rowId . '" data-col="' . $colNum . '">' . $widthOptions . '</select>
                </div>
                <div class="inputField">
                    <label>Tekstuitlijning</label>
                    <select data-scope="column" data-field="text_align" data-row-id="' . $rowId . '" data-col="' . $colNum . '">' . $textAlignOptions . '</select>
                </div>
                <div class="inputField content-text-only"' . $textOnlyHidden . '>
                    <label>Verticale uitlijning</label>
                    <select data-scope="column" data-field="vertical_align" data-row-id="' . $rowId . '" data-col="' . $colNum . '">' . $verticalAlignOptions . '</select>
                </div>
                <div class="inputField content-text-only"' . $textOnlyHidden . '>
                    <label>Padding (px)</label>
                    <input type="number" min="0" max="64" data-scope="column" data-field="padding_px" data-row-id="' . $rowId . '" data-col="' . $colNum . '" value="' . (int) $layout['padding_px'] . '">
                </div>
            </div>
            ' . builderBorderFields('column', $rowId, $layout, $colNum) . '
        </div>';
}

function builderColumnHtml(array $row, int $colNum, array $col, string $assetBase): string
{
    $rowId = (int) $row['id'];
    $isImage = (int) ($col['foto'] ?? 0) === 1;
    $infoId = (int) ($col['info_id'] ?? 0);
    $imageUrl = $isImage && !empty($col['informatie'])
        ? $assetBase . 'img/fotos/' . rawurlencode($col['informatie'])
        : '';

    ob_start();
    ?>
    <div class="admin-form builder-column" data-col="<?= $colNum ?>">
        <h4>Kolom <?= $colNum ?></h4>
        <div class="inputField">
            <label>Type inhoud</label>
            <select data-scope="column" data-field="foto" data-row-id="<?= $rowId ?>" data-col="<?= $colNum ?>">
                <option value="0" <?= !$isImage ? 'selected' : '' ?>>Tekst</option>
                <option value="1" <?= $isImage ? 'selected' : '' ?>>Afbeelding</option>
            </select>
        </div>
        <div class="inputField content-text" <?= $isImage ? 'hidden' : '' ?>>
            <label>Tekst</label>
            <textarea data-scope="column" data-field="informatie" data-row-id="<?= $rowId ?>" data-col="<?= $colNum ?>" rows="4"><?= $isImage ? '' : testInput($col['informatie'] ?? '') ?></textarea>
        </div>
        <div class="inputField content-image" <?= !$isImage ? 'hidden' : '' ?>>
            <?php if ($imageUrl !== ''): ?>
                <img class="builder-thumb" src="<?= testInput($imageUrl) ?>" alt="Preview">
            <?php endif; ?>
            <label>Upload afbeelding</label>
            <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-info-id="<?= $infoId ?>" data-row-id="<?= $rowId ?>" data-col="<?= $colNum ?>">
        </div>
        <div class="color-grid">
            <div class="inputField">
                <label>Tekstkleur</label>
                <input type="color" data-scope="column" data-field="kleur" data-row-id="<?= $rowId ?>" data-col="<?= $colNum ?>" value="<?= testInput($col['kleur'] ?? '#111827') ?>">
            </div>
            <div class="inputField content-text-only" <?= $isImage ? 'hidden' : '' ?>>
                <label>Achtergrondkleur</label>
                <input type="color" data-scope="column" data-field="backgroundKleur" data-row-id="<?= $rowId ?>" data-col="<?= $colNum ?>" value="<?= testInput($col['backgroundKleur'] ?? '#f9fafb') ?>">
            </div>
        </div>
        <div class="inputField content-text-only" <?= $isImage ? 'hidden' : '' ?>>
            <label>Opacity (0–10)</label>
            <input type="range" min="0" max="10" data-scope="column" data-field="opacity" data-row-id="<?= $rowId ?>" data-col="<?= $colNum ?>" value="<?= (int) ($col['opacity'] ?? 10) ?>">
        </div>
        <div class="inputField inputField--checkbox content-text-only" <?= $isImage ? 'hidden' : '' ?>>
            <label><input type="checkbox" data-scope="column" data-field="bold" data-row-id="<?= $rowId ?>" data-col="<?= $colNum ?>" value="1" <?= !empty($col['bold']) ? 'checked' : '' ?>> Vet</label>
            <label><input type="checkbox" data-scope="column" data-field="italic" data-row-id="<?= $rowId ?>" data-col="<?= $colNum ?>" value="1" <?= !empty($col['italic']) ? 'checked' : '' ?>> Cursief</label>
        </div>
        <?= builderColumnLayoutHtml($row, $colNum, $col, $isImage) ?>
    </div>
    <?php
    return ob_get_clean();
}

function renderBuilderRows(array $gridRows, string $assetBase): string
{
    if (empty($gridRows)) {
        return '<p class="admin-empty">Nog geen kolommen. Voeg hieronder je eerste kolomsectie toe.</p>';
    }

    $html = '';
    foreach ($gridRows as $index => $row) {
        $rowId = (int) $row['id'];
        $columnType = (int) $row['columnType'];
        $columnCount = gridColumnCount($columnType);
        $columnsHtml = '';

        for ($col = 1; $col <= $columnCount; $col++) {
            if (!empty($row['columns'][$col])) {
                $columnsHtml .= builderColumnHtml($row, $col, $row['columns'][$col], $assetBase);
            }
        }

        $html .= '
            <article class="builder-row" draggable="true" data-row-id="' . $rowId . '">
                <div class="builder-row__header">
                    <span class="builder-drag-handle" title="Sleep om te ordenen">⠿</span>
                    <h3>Kolomsectie ' . ($index + 1) . ' · ' . gridLayoutLabel($columnType) . '</h3>
                    <button type="button" class="btn-danger btn-small btn-delete-row" data-row-id="' . $rowId . '">Verwijderen</button>
                </div>
                ' . builderRowSettingsHtml($row) . '
                <div class="builder-columns builder-columns--' . $columnType . '">' . $columnsHtml . '</div>
            </article>';
    }

    return $html;
}

function normalizeGridRowsForBuilder(array $gridRows): array
{
    foreach ($gridRows as &$row) {
        $row = array_merge($row, normalizeRowLayout($row));
        foreach ($row['columns'] as &$column) {
            $column = array_merge($column, normalizeColumnLayout($column));
        }
    }
    unset($row, $column);

    return $gridRows;
}
