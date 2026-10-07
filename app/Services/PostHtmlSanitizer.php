<?php

namespace App\Services;

use App\Models\PostImage;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PostHtmlSanitizer
{
    /** @var list<string> */
    private const ALLOWED_TAGS = [
        'p', 'br', 'h2', 'h3', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'blockquote', 'a', 'figure',
    ];

    /** @var list<string> */
    private const DROP_WITH_CONTENTS = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'form', 'textarea', 'xmp',
    ];

    /**
     * Sanitize editor HTML using a deliberately small allowlist. Inline images are
     * represented by ownership-checked figure markers, never arbitrary img URLs.
     *
     * @param  array<int|string>  $allowedImageIds
     * @param  array<int|string>  $allowedImageTokens
     */
    public function sanitize(string $html, array $allowedImageIds = [], array $allowedImageTokens = []): string
    {
        $allowedIds = array_fill_keys(array_map('strval', $allowedImageIds), true);
        $allowedTokens = array_fill_keys(array_map('strval', $allowedImageTokens), true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="simak-editor-root">'.$html.'</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }

        if (! $loaded) {
            return '';
        }

        $root = $document->getElementById('simak-editor-root');

        if (! $root) {
            return '';
        }

        foreach (iterator_to_array($root->childNodes) as $child) {
            $this->sanitizeNode($child, $allowedIds, $allowedTokens);
        }

        $safeHtml = '';

        foreach ($root->childNodes as $child) {
            $safeHtml .= $document->saveHTML($child);
        }

        return trim($safeHtml);
    }

    /** @return list<int> */
    public function imageIds(string $html): array
    {
        preg_match_all('/<figure data-image-id="(\d+)"><\/figure>/', $html, $matches);

        return array_map('intval', $matches[1] ?? []);
    }

    /** @return list<string> */
    public function imageTokens(string $html): array
    {
        preg_match_all('/<figure data-image-token="([A-Za-z0-9_-]{1,64})"><\/figure>/', $html, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Expand canonical image markers using metadata owned by this post.
     *
     * @param  iterable<PostImage>  $images
     */
    public function renderImages(string $html, iterable $images, bool $forEditor = false, array $allowedImageTokens = []): string
    {
        $imagesById = Collection::make($images)->keyBy(fn (PostImage $image): string => (string) $image->id);
        $safeHtml = $this->sanitize($html, $imagesById->keys()->all(), $allowedImageTokens);

        return preg_replace_callback(
            '/<figure data-image-id="(\d+)"><\/figure>/',
            function (array $matches) use ($imagesById, $forEditor): string {
                /** @var PostImage|null $image */
                $image = $imagesById->get($matches[1]);

                if (! $image) {
                    return '';
                }

                $id = (int) $image->id;
                $url = htmlspecialchars(Storage::disk('public')->url($image->file_path), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
                $alt = htmlspecialchars($image->alt_text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
                $caption = $image->caption !== null && trim($image->caption) !== ''
                    ? '<figcaption>'.htmlspecialchars($image->caption, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8').'</figcaption>'
                    : '';
                $editorAttributes = $forEditor ? ' contenteditable="false" class="editor-inline-figure"' : '';

                return '<figure data-image-id="'.$id.'"'.$editorAttributes.'><img src="'.$url.'" alt="'.$alt.'" loading="lazy" decoding="async">'.$caption.'</figure>';
            },
            $safeHtml,
        ) ?? '';
    }

    /**
     * @param  array<string, bool>  $allowedIds
     * @param  array<string, bool>  $allowedTokens
     */
    private function sanitizeNode(DOMNode $node, array $allowedIds, array $allowedTokens): void
    {
        if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE) {
            $node->parentNode?->removeChild($node);

            return;
        }

        if (! $node instanceof DOMElement) {
            if (! in_array($node->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE], true)) {
                $node->parentNode?->removeChild($node);
            }

            return;
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::DROP_WITH_CONTENTS, true)) {
            $node->parentNode?->removeChild($node);

            return;
        }

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                $this->sanitizeNode($child, $allowedIds, $allowedTokens);
            }

            $parent = $node->parentNode;

            if ($parent) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }

                $parent->removeChild($node);
            }

            return;
        }

        if ($tag === 'figure') {
            $imageId = $node->getAttribute('data-image-id');
            $imageToken = $node->getAttribute('data-image-token');
            $validImageId = $imageId !== '' && ctype_digit($imageId) && isset($allowedIds[$imageId]);
            $validToken = $imageToken !== ''
                && preg_match('/\A[A-Za-z0-9_-]{1,64}\z/', $imageToken) === 1
                && isset($allowedTokens[$imageToken]);

            if (! $validImageId && ! $validToken) {
                $node->parentNode?->removeChild($node);

                return;
            }

            $this->removeAttributes($node);
            $node->setAttribute($validImageId ? 'data-image-id' : 'data-image-token', $validImageId ? $imageId : $imageToken);

            while ($node->firstChild) {
                $node->removeChild($node->firstChild);
            }

            return;
        }

        $href = $tag === 'a' ? $this->safeHref($node->getAttribute('href')) : null;
        $this->removeAttributes($node);

        if ($tag === 'a' && $href !== null) {
            $node->setAttribute('href', $href);

            if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->sanitizeNode($child, $allowedIds, $allowedTokens);
        }
    }

    private function removeAttributes(DOMElement $element): void
    {
        while ($element->attributes->length > 0) {
            $attribute = $element->attributes->item(0);

            if ($attribute) {
                $element->removeAttributeNode($attribute);
            }
        }
    }

    private function safeHref(?string $href): ?string
    {
        if ($href === null) {
            return null;
        }

        $href = trim(preg_replace('/[\x00-\x20\x7F]+/u', '', html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        $lower = strtolower($href);

        if ($href === '' || str_starts_with($href, '//') || str_starts_with($href, '\\')) {
            return null;
        }

        if (str_starts_with($href, '/') || str_starts_with($href, '#') || str_starts_with($lower, 'mailto:')) {
            return $href;
        }

        if (filter_var($href, FILTER_VALIDATE_URL) !== false && (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://'))) {
            return $href;
        }

        return null;
    }
}
