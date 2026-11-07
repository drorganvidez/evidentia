<?php

namespace App\View;

use Illuminate\Support\Str;

/**
 * Bridge between the HTML that the old rich text editor stored (descriptions, biographies) and the plain textareas
 * of the new UI. The textarea receives the text of the stored HTML with its paragraphs and line breaks; plain text
 * comes back to the page as paragraphs, and stored HTML stays as it is for x-ui.rich-text to purify.
 */
final class PlainText
{
    private const BLOCKS = '/<\s*(?:br\s*\/?|\/p|\/div|\/li|\/h[1-6]|\/blockquote|\/pre|\/tr|hr\s*\/?)\s*>/i';

    /** Whether the value carries markup of the old editor rather than plain text. */
    public static function isHtml(?string $value): bool
    {
        return $value !== null && preg_match('/<\/?[a-z][^>]*>/i', $value) === 1;
    }

    /** The text of a stored description for a textarea: one line per block, a blank line between paragraphs. */
    public static function fromHtml(?string $value): string
    {
        if ($value === null || ! self::isHtml($value)) {
            return (string) $value;
        }

        $text = preg_replace('/<\s*\/p\s*>/i', "\n\n", $value);
        $text = preg_replace(self::BLOCKS, "\n", (string) $text);
        $text = preg_replace('/<\s*li[^>]*>/i', '- ', (string) $text);
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+\n/", "\n", $text);

        return trim((string) preg_replace("/\n{3,}/", "\n\n", (string) $text));
    }

    /** Stored HTML as it is; plain text as escaped paragraphs with its line breaks. */
    public static function toHtml(?string $value): string
    {
        if ($value === null || self::isHtml($value)) {
            return (string) $value;
        }

        $paragraphs = preg_split("/\R{2,}/", trim(str_replace("\r\n", "\n", $value))) ?: [];

        return implode('', array_map(
            fn (string $paragraph): string => '<p>'.nl2br(e(trim($paragraph)), false).'</p>',
            array_filter($paragraphs, fn (string $paragraph): bool => trim($paragraph) !== ''),
        ));
    }

    /**
     * Text written in a textarea (points, agreements) as safe HTML: a blank line opens a paragraph, a line break stays,
     * lines starting with "- " or "1. " make a list and **bold**, *italics* and links work as in Markdown. Raw HTML in
     * the text is escaped; HTML stored by the old editor is purified instead.
     */
    public static function rich(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        if (self::isHtml($value)) {
            return \Html::clean($value);
        }

        $html = Str::markdown(str_replace("\r\n", "\n", $value), [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'renderer' => ['soft_break' => "<br>\n"],
        ]);

        return \Html::clean($html);
    }
}
