<?php

namespace App\Services\Content;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Whitelist sanitizer for user-authored rich text.
 *
 * Keeps formatting produced by the editor and removes scripts, event
 * handlers, embedded frames and unsafe link protocols.
 */
class HtmlSanitizer
{
    /** @var array<int, string> */
    private const ALLOWED_TAGS = [
        'p', 'div', 'span', 'br', 'hr',
        'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup',
        'h2', 'h3', 'h4',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a',
    ];

    /** @var array<int, string> */
    private const REMOVED_TAGS = [
        'script', 'style', 'iframe', 'frame', 'object', 'embed', 'applet',
        'form', 'input', 'button', 'select', 'textarea', 'option',
        'meta', 'link', 'base', 'title', 'head', 'svg', 'math', 'img',
        'video', 'audio', 'source', 'track', 'canvas',
    ];

    public function sanitize(?string $html): ?string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="rich-root">'.$html.'</div></body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('rich-root');

        if (! $root instanceof DOMElement) {
            return null;
        }

        $this->cleanChildren($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        $output = trim($output);

        return $output === '' ? null : $output;
    }

    private function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::REMOVED_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->cleanChildren($child);
                $this->unwrap($child);

                continue;
            }

            $this->cleanAttributes($child);
            $this->cleanChildren($child);
        }
    }

    private function cleanAttributes(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);

            if ($tag === 'a' && $name === 'href') {
                if (! $this->isSafeUrl($attribute->value)) {
                    $element->removeAttribute('href');
                }

                continue;
            }

            if ($tag === 'a' && in_array($name, ['title', 'target', 'rel'], true)) {
                continue;
            }

            $element->removeAttribute($attribute->name);
        }

        if ($tag === 'a' && $element->hasAttribute('target')) {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if ($scheme === null) {
            return true;
        }

        return in_array(strtolower((string) $scheme), ['http', 'https', 'mailto'], true);
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
