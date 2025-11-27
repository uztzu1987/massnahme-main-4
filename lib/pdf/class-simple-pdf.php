<?php
/**
 * Simple PDF Generator
 *
 * A lightweight, dependency-free PDF generator for basic documents.
 * Based on the PDF 1.4 specification.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGC_Simple_PDF {

    private $objects = [];
    private $object_count = 0;
    private $pages = [];
    private $current_page_content = '';
    private $fonts = [];
    private $page_width = 595.28;  // A4 width in points
    private $page_height = 841.89; // A4 height in points
    private $margin = 50;
    private $font_size = 12;
    private $y_position;
    private $line_height = 1.5;

    public function __construct() {
        $this->y_position = $this->page_height - $this->margin;
        $this->addFont('Helvetica');
    }

    /**
     * Add a built-in font
     */
    private function addFont($name) {
        $this->fonts[$name] = [
            'name' => $name,
            'type' => 'Type1'
        ];
    }

    /**
     * Start a new page
     */
    public function addPage() {
        if (!empty($this->current_page_content)) {
            $this->pages[] = $this->current_page_content;
        }
        $this->current_page_content = '';
        $this->y_position = $this->page_height - $this->margin;
    }

    /**
     * Set font size
     */
    public function setFontSize($size) {
        $this->font_size = $size;
        $this->current_page_content .= "/F1 {$size} Tf\n";
    }

    /**
     * Set text color (RGB values 0-255)
     */
    public function setTextColor($r, $g, $b) {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;
        $this->current_page_content .= sprintf("%.3f %.3f %.3f rg\n", $r, $g, $b);
    }

    /**
     * Set fill color for rectangles
     */
    public function setFillColor($r, $g, $b) {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;
        $this->current_page_content .= sprintf("%.3f %.3f %.3f rg\n", $r, $g, $b);
    }

    /**
     * Set stroke color for lines/borders
     */
    public function setDrawColor($r, $g, $b) {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;
        $this->current_page_content .= sprintf("%.3f %.3f %.3f RG\n", $r, $g, $b);
    }

    /**
     * Draw a filled rectangle
     */
    public function rect($x, $y, $w, $h, $style = 'F') {
        $y = $this->page_height - $y - $h;
        $op = $style === 'F' ? 'f' : ($style === 'D' ? 'S' : 'B');
        $this->current_page_content .= sprintf("%.2f %.2f %.2f %.2f re %s\n", $x, $y, $w, $h, $op);
    }

    /**
     * Draw a line
     */
    public function line($x1, $y1, $x2, $y2) {
        $y1 = $this->page_height - $y1;
        $y2 = $this->page_height - $y2;
        $this->current_page_content .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $x1, $y1, $x2, $y2);
    }

    /**
     * Write text at current position
     */
    public function write($text, $align = 'L') {
        $text = $this->escapeText($text);
        $text_width = $this->getStringWidth($text);

        $x = $this->margin;
        if ($align === 'C') {
            $x = ($this->page_width - $text_width) / 2;
        } elseif ($align === 'R') {
            $x = $this->page_width - $this->margin - $text_width;
        }

        $this->current_page_content .= "BT\n";
        $this->current_page_content .= sprintf("/F1 %d Tf\n", $this->font_size);
        $this->current_page_content .= sprintf("%.2f %.2f Td\n", $x, $this->y_position);
        $this->current_page_content .= sprintf("(%s) Tj\n", $text);
        $this->current_page_content .= "ET\n";

        $this->y_position -= $this->font_size * $this->line_height;
    }

    /**
     * Write text at specific position
     */
    public function text($x, $y, $text) {
        $text = $this->escapeText($text);
        $y = $this->page_height - $y;

        $this->current_page_content .= "BT\n";
        $this->current_page_content .= sprintf("/F1 %d Tf\n", $this->font_size);
        $this->current_page_content .= sprintf("%.2f %.2f Td\n", $x, $y);
        $this->current_page_content .= sprintf("(%s) Tj\n", $text);
        $this->current_page_content .= "ET\n";
    }

    /**
     * Add line break
     */
    public function ln($height = null) {
        if ($height === null) {
            $height = $this->font_size * $this->line_height;
        }
        $this->y_position -= $height;
    }

    /**
     * Escape special characters in PDF text
     * Handles UTF-8 to WinAnsiEncoding (CP1252) conversion for European characters
     */
    private function escapeText($text) {
        // First decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Map common UTF-8 characters to WinAnsiEncoding (CP1252) octal codes
        // This preserves German umlauts, Euro symbol, and other common chars
        $utf8_to_winansi = [
            // Euro symbol
            '€' => '\200',
            // German characters
            'ä' => '\344',
            'ö' => '\366',
            'ü' => '\374',
            'Ä' => '\304',
            'Ö' => '\326',
            'Ü' => '\334',
            'ß' => '\337',
            // French characters
            'é' => '\351',
            'è' => '\350',
            'ê' => '\352',
            'ë' => '\353',
            'à' => '\340',
            'â' => '\342',
            'ù' => '\371',
            'û' => '\373',
            'ô' => '\364',
            'î' => '\356',
            'ï' => '\357',
            'ç' => '\347',
            'É' => '\311',
            'È' => '\310',
            'Ê' => '\312',
            'À' => '\300',
            // Spanish/Portuguese
            'ñ' => '\361',
            'Ñ' => '\321',
            'á' => '\341',
            'í' => '\355',
            'ó' => '\363',
            'ú' => '\372',
            // Other common
            '°' => '\260',
            '©' => '\251',
            '®' => '\256',
            '™' => '\231',
            '–' => '\226',  // en-dash
            '—' => '\227',  // em-dash
            ''' => '\222',  // right single quote
            ''' => '\221',  // left single quote
            '"' => '\223',  // left double quote
            '"' => '\224',  // right double quote
            '•' => '\225',  // bullet
            '…' => '\205',  // ellipsis
        ];

        // Replace UTF-8 characters with their WinAnsi octal equivalents
        $text = str_replace(
            array_keys($utf8_to_winansi),
            array_values($utf8_to_winansi),
            $text
        );

        // For any remaining non-ASCII characters, try iconv as fallback
        // This handles less common characters with transliteration
        $text = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $text);
        if ($text === false) {
            // If iconv fails, strip non-ASCII
            $text = preg_replace('/[^\x20-\x7E]/', '', $text);
        }

        // Escape PDF special characters
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);

        return $text;
    }

    /**
     * Estimate string width (approximate)
     */
    private function getStringWidth($text) {
        // Approximate width based on average character width
        return strlen($text) * $this->font_size * 0.5;
    }

    /**
     * Get current Y position
     */
    public function getY() {
        return $this->page_height - $this->y_position;
    }

    /**
     * Set Y position
     */
    public function setY($y) {
        $this->y_position = $this->page_height - $y;
    }

    /**
     * Get page width
     */
    public function getPageWidth() {
        return $this->page_width;
    }

    /**
     * Get page height
     */
    public function getPageHeight() {
        return $this->page_height;
    }

    /**
     * Set line width for drawing
     */
    public function setLineWidth($width) {
        $this->current_page_content .= sprintf("%.2f w\n", $width);
    }

    /**
     * Multi-line text cell
     */
    public function multiCell($w, $h, $text, $border = 0, $align = 'L') {
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $lines = explode("\n", wordwrap($text, intval($w / ($this->font_size * 0.5)), "\n", true));

        foreach ($lines as $line) {
            $this->write(trim($line), $align);
        }
    }

    /**
     * Generate the PDF content
     */
    public function output($filepath) {
        // Finalize current page
        if (!empty($this->current_page_content)) {
            $this->pages[] = $this->current_page_content;
        }

        $this->objects = [];
        $this->object_count = 0;

        // Build PDF structure
        $pdf = "%PDF-1.4\n";
        $pdf .= "%\xE2\xE3\xCF\xD3\n"; // Binary marker

        // Catalog
        $catalog_id = $this->addObject("<< /Type /Catalog /Pages 2 0 R >>");

        // Pages object (will be object 2)
        $page_refs = [];
        $page_start_id = 3 + count($this->fonts);
        for ($i = 0; $i < count($this->pages); $i++) {
            $page_refs[] = ($page_start_id + $i * 2) . " 0 R";
        }

        $pages_content = "<< /Type /Pages /Kids [" . implode(" ", $page_refs) . "] /Count " . count($this->pages) . " >>";
        $pages_id = $this->addObject($pages_content);

        // Font with WinAnsiEncoding for European character support
        $font_id = $this->addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>");

        // Create pages and content streams
        foreach ($this->pages as $index => $page_content) {
            // Content stream
            $stream = "q\n" . $page_content . "Q\n";
            $stream_length = strlen($stream);
            $content_id = $this->addObject("<< /Length {$stream_length} >>\nstream\n{$stream}endstream");

            // Page object
            $page_obj = "<< /Type /Page /Parent 2 0 R ";
            $page_obj .= "/MediaBox [0 0 {$this->page_width} {$this->page_height}] ";
            $page_obj .= "/Contents " . ($content_id) . " 0 R ";
            $page_obj .= "/Resources << /Font << /F1 {$font_id} 0 R >> >> >>";
            $this->addObject($page_obj);
        }

        // Build the file
        $offsets = [];
        $output = $pdf;

        foreach ($this->objects as $id => $obj) {
            $offsets[$id] = strlen($output);
            $output .= "{$id} 0 obj\n{$obj}\nendobj\n";
        }

        // Cross-reference table
        $xref_offset = strlen($output);
        $output .= "xref\n";
        $output .= "0 " . ($this->object_count + 1) . "\n";
        $output .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $this->object_count; $i++) {
            $output .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        // Trailer
        $output .= "trailer\n";
        $output .= "<< /Size " . ($this->object_count + 1) . " /Root 1 0 R >>\n";
        $output .= "startxref\n";
        $output .= $xref_offset . "\n";
        $output .= "%%EOF";

        // Write to file
        return file_put_contents($filepath, $output) !== false;
    }

    /**
     * Add an object and return its ID
     */
    private function addObject($content) {
        $this->object_count++;
        $this->objects[$this->object_count] = $content;
        return $this->object_count;
    }
}
