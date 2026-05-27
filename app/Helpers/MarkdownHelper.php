<?php

namespace App\Helpers;

class MarkdownHelper
{
    /**
     * Convert limited markdown to safe HTML for AI responses.
     * Handles: **bold**, *italic*, - bullet, 1. numbered, | table, `code`, line breaks.
     */
    public static function toHtml(string $text): string
    {
        $t = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Tables: | col | col |
        $t = preg_replace_callback(
            '/(\|.+\|(?:\n\|.+\|)*)/u',
            fn($m) => self::parseTable($m[1]),
            $t
        );

        // Bold **text**
        $t = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $t);

        // Italic *text*
        $t = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/u', '<em>$1</em>', $t);

        // Inline code `code`
        $t = preg_replace('/`([^`]+)`/u', '<code>$1</code>', $t);

        // Numbered lists
        $t = preg_replace_callback(
            '/((?:^|\n)\d+\. .+(?:\n\d+\. .+)*)/mu',
            fn($m) => '<ol>' . preg_replace('/^(\d+)\. (.+)$/mu', '<li>$2</li>', $m[1]) . '</ol>',
            $t
        );

        // Unordered lists - or •
        $t = preg_replace_callback(
            '/((?:^|\n)[-•] .+(?:\n[-•] .+)*)/mu',
            fn($m) => '<ul>' . preg_replace('/^[-•] (.+)$/mu', '<li>$1</li>', $m[1]) . '</ul>',
            $t
        );

        // Double newline → paragraph break
        $t = preg_replace('/\n{2,}/u', '</p><p>', $t);
        $t = '<p>' . $t . '</p>';

        // Single newline → <br>
        $t = preg_replace('/(?<!>)\n(?!<)/u', '<br>', $t);

        // Clean up empty paragraphs
        $t = preg_replace('/<p>\s*<\/p>/u', '', $t);

        return $t;
    }

    private static function parseTable(string $raw): string
    {
        $rows = array_filter(explode("\n", trim($raw)));
        $html = '<table>';
        $isHeader = true;
        foreach ($rows as $row) {
            if (preg_match('/^\s*\|[-| :]+\|\s*$/', $row)) { $isHeader = false; continue; }
            $cells = array_slice(explode('|', $row), 1, -1);
            $tag   = $isHeader ? 'th' : 'td';
            $html .= '<tr>' . implode('', array_map(fn($c) => "<{$tag}>" . trim($c) . "</{$tag}>", $cells)) . '</tr>';
            if ($isHeader) $isHeader = false;
        }
        $html .= '</table>';
        return $html;
    }
}
