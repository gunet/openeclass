<?php

require_once __DIR__ . '/../AIProviderFactory.php';

/**
 * Service for extracting course data using AI providers
 * Handles both syllabus parsing and manual course generation
 */
class AICourseExtractionService {

    private $provider;

    /**
     * Constructor
     *
     * @param AIProviderInterface|null $provider Optional AI provider, will use primary if null
     */
    public function __construct(?AIProviderInterface $provider = null) {
        $this->provider = $provider ?? AIProviderFactory::getPrimaryProvider();

        if (!$this->provider) {
            throw new Exception('No AI provider available for course extraction');
        }
    }

    /**
     * Extract course data from syllabus content
     *
     * @param string $syllabusContent Text content extracted from syllabus
     * @param array $options Options for extraction (language, format preferences, etc.)
     * @return array Structured course data
     */
    public function extractFromSyllabus(string $syllabusContent, array $options = []): array {
        if (empty($syllabusContent)) {
            throw new Exception('Syllabus content cannot be empty');
        }

        try {
            return $this->provider->extractCourseData($syllabusContent, 'syllabus', $options);
        } catch (Exception $e) {
            error_log("Course extraction from syllabus failed: " . $e->getMessage());
            throw new Exception('Failed to extract course data from syllabus: ' . $e->getMessage());
        }
    }

    /**
     * Generate course data from manual prompt
     *
     * @param string $prompt User-provided course description or requirements
     * @param array $options Options for generation (language, complexity, format, etc.)
     * @return array Generated course data
     */
    public function generateFromPrompt(string $prompt, array $options = []): array {
        if (empty($prompt)) {
            throw new Exception('Course generation prompt cannot be empty');
        }

        try {
            return $this->provider->extractCourseData($prompt, 'prompt', $options);
        } catch (Exception $e) {
            error_log("Course generation from prompt failed: " . $e->getMessage());
            throw new Exception('Failed to generate course data from prompt: ' . $e->getMessage());
        }
    }

    /**
     * Validate extracted course data
     *
     * @param array $courseData Course data to validate
     * @return bool True if data is valid
     */
    public function validateCourseData(array $courseData): bool {
        $requiredFields = ['title', 'description'];

        foreach ($requiredFields as $field) {
            if (empty($courseData[$field])) {
                return false;
            }
        }

        // Validate title length
        if (mb_strlen($courseData['title']) > 255) {
            return false;
        }

        // Validate course access levels
        $validAccessLevels = [COURSE_OPEN, COURSE_REGISTRATION, COURSE_CLOSED, COURSE_INACTIVE];
        if (isset($courseData['formvisible']) && !in_array($courseData['formvisible'], $validAccessLevels)) {
            return false;
        }

        // Validate view types
        $validViewTypes = ['simple', 'units', 'activity', 'wall', 'flippedclassroom', 'sessions'];
        if (isset($courseData['view_type']) && !in_array($courseData['view_type'], $validViewTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Extract text content from PDF file using smalot/pdfparser
     *
     * @param string $pdfFilePath Path to uploaded PDF file
     * @return string Extracted text content
     * @throws Exception If PDF parsing fails
     */
    public function extractTextFromPDF(string $pdfFilePath): string {
        if (!file_exists($pdfFilePath)) {
            throw new Exception('PDF file not found');
        }

        try {
            // Initialize PDF parser
            $parser = new \Smalot\PdfParser\Parser();

            // Parse the PDF file
            $pdf = $parser->parseFile($pdfFilePath);

            // Extract text from all pages
            $text = $pdf->getText();

            // Clean up the extracted text
            $text = $this->cleanExtractedText($text);

            if (empty(trim($text))) {
                global $langAIPDFNoText;
                throw new Exception($langAIPDFNoText ?? 'No readable text found in PDF. The PDF may contain only images or scanned content.');
            }

            return $text;

        } catch (\Smalot\PdfParser\Exception $e) {
            global $langAIPDFParsingFailed;
            error_log("PDF parsing error: " . $e->getMessage());
            throw new Exception($langAIPDFParsingFailed ?? 'Failed to read PDF file. The file may be corrupted, encrypted, or contain only images.');
        } catch (Exception $e) {
            global $langAIPDFTextExtractionError;
            error_log("PDF extraction error: " . $e->getMessage());
            throw new Exception($langAIPDFTextExtractionError ?? 'Error extracting text from PDF file.');
        }
    }

    /**
     * Clean and normalize extracted text from PDF
     *
     * @param string $text Raw extracted text
     * @return string Cleaned text
     */
    private function cleanExtractedText(string $text): string {
        // First, fix UTF-8 encoding issues
        $text = $this->fixUTF8Encoding($text);

        // Remove excessive whitespace and normalize line breaks
        $text = preg_replace('/\s+/', ' ', $text);
        $text = str_replace(['\r\n', '\r', '\n'], "\n", $text);

        // Remove multiple consecutive newlines
        $text = preg_replace('/\n\s*\n\s*\n/', "\n\n", $text);

        // Trim whitespace from each line
        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $text = implode("\n", $lines);

        // Remove empty lines at the beginning and end
        $text = trim($text);

        return $text;
    }

    /**
     * Fix UTF-8 encoding issues in extracted text
     *
     * @param string $text Raw text with potential encoding issues
     * @return string UTF-8 clean text
     */
    private function fixUTF8Encoding(string $text): string {
        // Remove or replace invalid UTF-8 sequences
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Remove null bytes and other problematic characters
        $text = str_replace(["\x00", "\xEF\xBB\xBF"], '', $text);

        // Convert common problematic characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Ensure valid UTF-8 by filtering invalid sequences
        if (!mb_check_encoding($text, 'UTF-8')) {
            // If still invalid, use iconv to clean it
            $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
        }

        return $text;
    }

    /**
     * Sanitize and prepare course data for OpenEclass
     *
     * @param array $rawData Raw extracted data from AI
     * @return array Sanitized data ready for database insertion
     */
    public function sanitizeCourseData(array $rawData): array {
        $sanitized = [];

        // Sanitize title
        if (isset($rawData['title'])) {
            $sanitized['title'] = mb_substr(trim($rawData['title']), 0, 255);
        }

        // Sanitize public code
        if (isset($rawData['public_code'])) {
            $sanitized['public_code'] = mb_substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($rawData['public_code'])), 0, 20);
        }

        // Sanitize description
        if (isset($rawData['description'])) {
            $sanitized['description'] = purify($rawData['description']);
        }

        // Sanitize professor names
        if (isset($rawData['prof_names'])) {
            $sanitized['prof_names'] = mb_substr(trim($rawData['prof_names']), 0, 255);
        }

        // Validate and set defaults for various fields
        $sanitized['language'] = $rawData['language'] ?? 'el';
        $sanitized['view_type'] = in_array($rawData['view_type'] ?? '', ['simple', 'units', 'activity', 'wall', 'flippedclassroom', 'sessions'])
            ? $rawData['view_type'] : 'units';
        $sanitized['formvisible'] = in_array($rawData['formvisible'] ?? '', [COURSE_OPEN, COURSE_REGISTRATION, COURSE_CLOSED, COURSE_INACTIVE])
            ? $rawData['formvisible'] : COURSE_REGISTRATION;
        $sanitized['course_license'] = $rawData['course_license'] ?? 0;

        // Pass through syllabus sections if present
        if (isset($rawData['syllabus_sections']) && is_array($rawData['syllabus_sections'])) {
            $sanitized['syllabus_sections'] = [];
            foreach ($rawData['syllabus_sections'] as $key => $content) {
                if (!empty($content) && is_string($content)) {
                    // Sanitize each section content
                    $sanitized['syllabus_sections'][$key] = purify(trim($content));
                }
            }
        }

        // Pass through other metadata fields
        if (isset($rawData['keywords'])) {
            $sanitized['keywords'] = mb_substr(trim($rawData['keywords']), 0, 500);
        }
        if (isset($rawData['extraction_method'])) {
            $sanitized['extraction_method'] = $rawData['extraction_method'];
        }
        if (isset($rawData['source_url'])) {
            $sanitized['source_url'] = $rawData['source_url'];
        }
        if (isset($rawData['file_name'])) {
            $sanitized['file_name'] = $rawData['file_name'];
        }
        if (isset($rawData['file_size'])) {
            $sanitized['file_size'] = $rawData['file_size'];
        }
        if (isset($rawData['text_length'])) {
            $sanitized['text_length'] = $rawData['text_length'];
        }
        if (isset($rawData['generated_at'])) {
            $sanitized['generated_at'] = $rawData['generated_at'];
        }

        return $sanitized;
    }

    /**
     * Check if AI course extraction is available
     *
     * @return bool True if service is available
     */
    public static function isEnabled(): bool {
        $q = Database::get()->querySingle("SELECT ai_modules.id, ai_module_id, name, model_name, enabled FROM ai_modules 
                    JOIN ai_providers ON ai_modules.ai_provider_id = ai_providers.id 
                        AND ai_module_id =" . AI_MODULE_CREATE_COURSE . " 
                        AND enabled = 1");
        if ($q) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get supported content types
     *
     * @return array Array of supported content types
     */
    public function getSupportedContentTypes(): array {
        return ['syllabus', 'prompt'];
    }

    /**
     * Get provider information
     *
     * @return array Provider information
     */
    public function getProviderInfo(): array {
        return [
            'type' => $this->provider->getProviderType(),
            'display_name' => $this->provider->getDisplayName(),
            'healthy' => $this->provider->isHealthy()
        ];
    }
}
