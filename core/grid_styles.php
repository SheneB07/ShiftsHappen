<?php

function defaultRowLayout(): array
{
    return [
        'row_width_pct' => 100,
        'row_align' => 'left',
        'column_gap' => 16,
        'flush_columns' => 0,
        'border_top' => 0,
        'border_right' => 0,
        'border_bottom' => 0,
        'border_left' => 0,
        'border_width' => 1,
        'border_color' => '#d1d5db',
    ];
}

function defaultColumnLayout(): array
{
    return [
        'text_align' => 'left',
        'vertical_align' => 'top',
        'width_pct' => 0,
        'padding_px' => 16,
        'border_top' => 0,
        'border_right' => 0,
        'border_bottom' => 0,
        'border_left' => 0,
        'border_width' => 1,
        'border_color' => '#d1d5db',
    ];
}

function sanitizeAlign(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function normalizeRowLayout(array $row): array
{
    return array_merge(defaultRowLayout(), [
        'row_width_pct' => max(25, min(100, (int) ($row['row_width_pct'] ?? 100))),
        'row_align' => sanitizeAlign($row['row_align'] ?? 'left', ['left', 'center', 'right'], 'left'),
        'column_gap' => max(0, min(64, (int) ($row['column_gap'] ?? 16))),
        'flush_columns' => !empty($row['flush_columns']) ? 1 : 0,
        'border_top' => !empty($row['border_top']) ? 1 : 0,
        'border_right' => !empty($row['border_right']) ? 1 : 0,
        'border_bottom' => !empty($row['border_bottom']) ? 1 : 0,
        'border_left' => !empty($row['border_left']) ? 1 : 0,
        'border_width' => max(1, min(8, (int) ($row['border_width'] ?? 1))),
        'border_color' => validateHexColor($row['border_color'] ?? '#d1d5db', '#d1d5db'),
    ]);
}

function normalizeColumnLayout(array $column): array
{
    return array_merge(defaultColumnLayout(), [
        'text_align' => sanitizeAlign($column['text_align'] ?? 'left', ['left', 'center', 'right'], 'left'),
        'vertical_align' => sanitizeAlign($column['vertical_align'] ?? 'top', ['top', 'center', 'bottom'], 'top'),
        'width_pct' => max(0, min(100, (int) ($column['width_pct'] ?? 0))),
        'padding_px' => max(0, min(64, (int) ($column['padding_px'] ?? 16))),
        'border_top' => !empty($column['border_top']) ? 1 : 0,
        'border_right' => !empty($column['border_right']) ? 1 : 0,
        'border_bottom' => !empty($column['border_bottom']) ? 1 : 0,
        'border_left' => !empty($column['border_left']) ? 1 : 0,
        'border_width' => max(1, min(8, (int) ($column['border_width'] ?? 1))),
        'border_color' => validateHexColor($column['border_color'] ?? '#d1d5db', '#d1d5db'),
    ]);
}

function buildBorderCss(array $item): string
{
    $width = max(1, min(8, (int) ($item['border_width'] ?? 1)));
    $color = validateHexColor($item['border_color'] ?? '#d1d5db', '#d1d5db');
    $styles = [];

    if (!empty($item['border_top'])) {
        $styles[] = 'border-top:' . $width . 'px solid ' . $color;
    }
    if (!empty($item['border_right'])) {
        $styles[] = 'border-right:' . $width . 'px solid ' . $color;
    }
    if (!empty($item['border_bottom'])) {
        $styles[] = 'border-bottom:' . $width . 'px solid ' . $color;
    }
    if (!empty($item['border_left'])) {
        $styles[] = 'border-left:' . $width . 'px solid ' . $color;
    }

    return implode(';', $styles);
}

function buildRowWrapperStyle(array $row): string
{
    $layout = normalizeRowLayout($row);
    $styles = [
        'max-width:' . $layout['row_width_pct'] . '%',
        'width:100%',
    ];

    if ($layout['row_align'] === 'center') {
        $styles[] = 'margin-left:auto';
        $styles[] = 'margin-right:auto';
    } elseif ($layout['row_align'] === 'right') {
        $styles[] = 'margin-left:auto';
        $styles[] = 'margin-right:0';
    } else {
        $styles[] = 'margin-left:0';
        $styles[] = 'margin-right:auto';
    }

    $border = buildBorderCss($layout);
    if ($border !== '') {
        $styles[] = $border;
    }

    return implode(';', $styles);
}

function buildRowGridStyle(array $row, array $columns, int $columnCount): string
{
    $layout = normalizeRowLayout($row);
    $gap = !empty($layout['flush_columns']) ? 0 : (int) $layout['column_gap'];
    $styles = ['gap:' . $gap . 'px'];

    $widths = [];
    for ($i = 1; $i <= $columnCount; $i++) {
        $col = $columns[$i] ?? [];
        $widths[] = (int) ($col['width_pct'] ?? 0);
    }

    $hasCustomWidths = array_filter($widths, fn($w) => $w > 0);

    if (count($hasCustomWidths) === $columnCount && array_sum($widths) > 0) {
        $styles[] = 'grid-template-columns:' . implode(' ', array_map(fn($w) => $w . '%', $widths));
    } elseif (!empty($hasCustomWidths)) {
        $parts = [];
        foreach ($widths as $width) {
            $parts[] = $width > 0 ? $width . '%' : '1fr';
        }
        $styles[] = 'grid-template-columns:' . implode(' ', $parts);
    }

    return implode(';', $styles);
}

function buildColumnStyle(array $column, bool $isImage, bool $flushRow): string
{
    $layout = normalizeColumnLayout($column);
    $styles = [
        'text-align:' . $layout['text_align'],
        'display:flex',
        'flex-direction:column',
        'justify-content:' . ($layout['vertical_align'] === 'center' ? 'center' : ($layout['vertical_align'] === 'bottom' ? 'flex-end' : 'flex-start')),
    ];

    if (!$isImage) {
        $styles[] = 'padding:' . $layout['padding_px'] . 'px';
    } elseif ($flushRow) {
        $styles[] = 'padding:0';
    }

    $border = buildBorderCss($layout);
    if ($border !== '') {
        $styles[] = $border;
    }

    return implode(';', $styles);
}

function rowGridClass(int $columnType, array $row): string
{
    $layout = normalizeRowLayout($row);
    $class = 'grid-row';

    if ($columnType === 1) {
        $class .= ' grid-row--1';
    } elseif ($columnType === 2 || $columnType === 3) {
        $class .= ' grid-row--2';
    } else {
        $class .= ' grid-row--3';
    }

    if (!empty($layout['flush_columns']) || (int) $layout['column_gap'] === 0) {
        $class .= ' grid-row--flush';
    }

    return $class;
}
