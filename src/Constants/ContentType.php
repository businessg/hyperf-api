<?php

namespace BusinessG\HyperfApi\Constants;

enum ContentType: string
{
    // 常见文本类型
    case TEXT_PLAIN = 'text/plain';
    case TEXT_HTML = 'text/html';
    case TEXT_CSS = 'text/css';
    case TEXT_JAVASCRIPT = 'text/javascript';
    case TEXT_CSV = 'text/csv';
    case TEXT_XML = 'text/xml';

    // 应用程序类型
    case APPLICATION_JSON = 'application/json';
    case APPLICATION_XML = 'application/xml';
    case APPLICATION_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    case APPLICATION_FORM_DATA = 'multipart/form-data';
    case APPLICATION_PDF = 'application/pdf';
    case APPLICATION_MSWORD = 'application/msword';
    case APPLICATION_OCTET_STREAM = 'application/octet-stream';
    case APPLICATION_ZIP = 'application/zip';
    case APPLICATION_GZIP = 'application/gzip';

    // 图像类型
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_PNG = 'image/png';
    case IMAGE_GIF = 'image/gif';
    case IMAGE_SVG = 'image/svg+xml';
    case IMAGE_WEBP = 'image/webp';

    // 音频/视频类型
    case AUDIO_MPEG = 'audio/mpeg';
    case VIDEO_MP4 = 'video/mp4';
    case VIDEO_MPEG = 'video/mpeg';

    // 字体类型
    case FONT_WOFF = 'font/woff';
    case FONT_WOFF2 = 'font/woff2';
    case FONT_TTF = 'font/ttf';

    /**
     * 获取 Content-Type 带字符集
     */
    public function withCharset(string $charset = 'utf-8'): string
    {
        return in_array($this, [
            self::TEXT_PLAIN,
            self::TEXT_HTML,
            self::TEXT_CSS,
            self::TEXT_JAVASCRIPT,
            self::TEXT_XML,
            self::APPLICATION_JSON,
            self::APPLICATION_XML,
        ]) ? "{$this->value}; charset={$charset}" : $this->value;
    }

    /**
     * 是否为 JSON 类型
     */
    public function isJson(): bool
    {
        return $this === self::APPLICATION_JSON;
    }

    /**
     * 是否为 XML 类型
     */
    public function isXml(): bool
    {
        return $this === self::APPLICATION_XML || $this === self::TEXT_XML;
    }

    /**
     * 是否为表单类型
     */
    public function isForm(): bool
    {
        return $this === self::APPLICATION_X_WWW_FORM_URLENCODED
            || $this === self::APPLICATION_FORM_DATA;
    }

    /**
     * 是否为二进制流类型
     */
    public function isBinary(): bool
    {
        return $this === self::APPLICATION_OCTET_STREAM
            || $this === self::IMAGE_JPEG
            || $this === self::IMAGE_PNG
            || $this === self::APPLICATION_PDF
            || $this === self::APPLICATION_ZIP;
    }

    /**
     * 从文件名猜测 ContentType
     */
    public static function guessFromFilename(string $filename): ?self
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt' => self::TEXT_PLAIN,
            'html', 'htm' => self::TEXT_HTML,
            'css' => self::TEXT_CSS,
            'js' => self::TEXT_JAVASCRIPT,
            'json' => self::APPLICATION_JSON,
            'xml' => self::APPLICATION_XML,
            'csv' => self::TEXT_CSV,
            'pdf' => self::APPLICATION_PDF,
            'doc', 'docx' => self::APPLICATION_MSWORD,
            'jpg', 'jpeg' => self::IMAGE_JPEG,
            'png' => self::IMAGE_PNG,
            'gif' => self::IMAGE_GIF,
            'svg' => self::IMAGE_SVG,
            'webp' => self::IMAGE_WEBP,
            'mp3' => self::AUDIO_MPEG,
            'mp4' => self::VIDEO_MP4,
            'zip' => self::APPLICATION_ZIP,
            'gz' => self::APPLICATION_GZIP,
            'woff' => self::FONT_WOFF,
            'woff2' => self::FONT_WOFF2,
            'ttf' => self::FONT_TTF,
            default => null,
        };
    }
}