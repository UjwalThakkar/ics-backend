<?php

declare(strict_types=1);

namespace IndianConsular\Services;

/**
 * PDF Form Service - Fills PDF forms with user data
 * 
 * Uses pdftk (PDF Toolkit) for filling PDF form fields.
 * Falls back to FPDI if pdftk is not available.
 */
class PdfFormService
{
    private string $templatePath;
    private string $outputDir;
    private bool $usePdftk;

    public function __construct()
    {
        // Path to the PDF template
        // Try both possible filenames
        $template1 = __DIR__ . '/../../public/templates/misc_application_form.pdf';
        $template2 = __DIR__ . '/../../public/templates/misc application form-new.pdf';
        
        $this->templatePath = file_exists($template1) ? $template1 : $template2;
        
        // Output directory for filled PDFs
        $this->outputDir = __DIR__ . '/../../public/uploads/filled_forms/';
        
        // Create output directory if it doesn't exist
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }

        // Check if pdftk is available
        $this->usePdftk = $this->checkPdftkAvailable();
    }

    /**
     * Check if pdftk command is available
     */
    private function checkPdftkAvailable(): bool
    {
        // Try to execute pdftk --version
        $output = [];
        $returnVar = 0;
        @exec('pdftk --version 2>&1', $output, $returnVar);
        return $returnVar === 0;
    }

    /**
     * Fill PDF form with application data
     * 
     * @param array $formData The form data to fill
     * @param string $applicationId Application ID for filename
     * @return string|null Path to filled PDF or null on failure
     */
    public function fillPdfForm(array $formData, string $applicationId): ?string
    {
        if (!file_exists($this->templatePath)) {
            error_log("PDF template not found: {$this->templatePath}");
            return null;
        }

        try {
            if ($this->usePdftk) {
                return $this->fillWithPdftk($formData, $applicationId);
            } else {
                // Fallback: Try to use FPDI or return error
                error_log("pdftk not available. Please install pdftk for PDF form filling.");
                return $this->fillWithPhpFallback($formData, $applicationId);
            }
        } catch (\Exception $e) {
            error_log("PDF filling error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fill PDF using pdftk (PDF Toolkit)
     * 
     * This is the preferred method as it properly handles PDF form fields
     */
    private function fillWithPdftk(array $formData, string $applicationId): ?string
    {
        // Prepare field data for pdftk
        $fieldData = $this->prepareFieldData($formData);
        
        // Create temporary field data file
        $fieldDataFile = sys_get_temp_dir() . '/' . uniqid('pdf_fields_', true) . '.fdf';
        $this->createFdfFile($fieldDataFile, $fieldData);
        
        // Output filename
        $outputFile = $this->outputDir . $applicationId . '_filled.pdf';
        
        // Build pdftk command with flatten option
        // The "flatten" option converts form fields to static text, making the PDF non-editable
        // This ensures users cannot modify the filled data after download
        // Note: flatten must come after output filename
        $command = sprintf(
            'pdftk "%s" fill_form "%s" output "%s" flatten',
            escapeshellarg($this->templatePath),
            escapeshellarg($fieldDataFile),
            escapeshellarg($outputFile)
        );
        
        // Execute command and capture both stdout and stderr
        $output = [];
        $returnVar = 0;
        exec($command . ' 2>&1', $output, $returnVar);
        
        // Clean up temporary file
        @unlink($fieldDataFile);
        
        if ($returnVar !== 0 || !file_exists($outputFile)) {
            error_log("pdftk failed. Command: $command. Return code: $returnVar. Output: " . implode("\n", $output));
            return null;
        }
        
        // Verify the output file exists and has content
        if (filesize($outputFile) === 0) {
            error_log("pdftk produced empty PDF file: $outputFile");
            @unlink($outputFile);
            return null;
        }
        
        // Log success for debugging
        error_log("PDF successfully generated and flattened: $outputFile (Size: " . filesize($outputFile) . " bytes)");
        
        // Return relative path from public directory
        return '/uploads/filled_forms/' . basename($outputFile);
    }

    /**
     * Fallback method using PHP (if pdftk is not available)
     * 
     * Note: This is a basic implementation. For proper form filling,
     * pdftk is recommended.
     */
    private function fillWithPhpFallback(array $formData, string $applicationId): ?string
    {
        // For now, just copy the template and log a warning
        // In production, you should install pdftk or use a PHP library
        // that supports PDF form filling (like FPDI with form field support)
        
        $outputFile = $this->outputDir . $applicationId . '_filled.pdf';
        
        // Copy template as fallback (user will need to fill manually)
        if (copy($this->templatePath, $outputFile)) {
            error_log("PDF template copied (pdftk not available). User should fill manually.");
            return '/uploads/filled_forms/' . basename($outputFile);
        }
        
        return null;
    }

    /**
     * Prepare field data mapping from form data to PDF field names
     * 
     * Mapped based on actual PDF form field names extracted using:
     * pdftk "misc_application_form.pdf" dump_data_fields
     */
    private function prepareFieldData(array $formData): array
    {
        $pdfFields = [];
        
        // Direct mappings (single field to single PDF field)
        if (!empty($formData['full_name'])) {
            $pdfFields['FullName'] = (string)$formData['full_name'];
        }
        
        if (!empty($formData['nationality'])) {
            $pdfFields['NationalityOfApplicant'] = (string)$formData['nationality'];
        }
        
        if (!empty($formData['date_of_birth'])) {
            $pdfFields['DateOfBirth'] = $this->formatDate($formData['date_of_birth']);
        }
        
        if (!empty($formData['present_address_sa'])) {
            $pdfFields['PresentAddressInSouthAfrica'] = (string)$formData['present_address_sa'];
        }
        
        if (!empty($formData['phone_number'])) {
            $pdfFields['PhoneNo'] = (string)$formData['phone_number'];
        }
        
        if (!empty($formData['email_address'])) {
            $pdfFields['EmailAddress'] = (string)$formData['email_address'];
        }
        
        if (!empty($formData['visa_immigration_status'])) {
            $pdfFields['Visa/ImmigrationStatus'] = (string)$formData['visa_immigration_status'];
        }
        
        if (!empty($formData['permanent_address_india'])) {
            $pdfFields['PermanentAddressInIndia'] = (string)$formData['permanent_address_india'];
        }
        
        // Combined fields (multiple form fields combined into one PDF field)
        
        // Father: Full Name and Nationality
        $fatherParts = [];
        if (!empty($formData['father_name'])) {
            $fatherParts[] = (string)$formData['father_name'];
        }
        if (!empty($formData['father_nationality'])) {
            $fatherParts[] = (string)$formData['father_nationality'];
        }
        if (!empty($fatherParts)) {
            $pdfFields['FullNameOfFatherAndNationality'] = implode(', ', $fatherParts);
        }
        
        // Mother: Full Name and Nationality
        $motherParts = [];
        if (!empty($formData['mother_name'])) {
            $motherParts[] = (string)$formData['mother_name'];
        }
        if (!empty($formData['mother_nationality'])) {
            $motherParts[] = (string)$formData['mother_nationality'];
        }
        if (!empty($motherParts)) {
            $pdfFields['FullNameOfMotherAndNationality'] = implode(', ', $motherParts);
        }
        
        // Place and Country of Birth
        $birthParts = [];
        if (!empty($formData['place_of_birth'])) {
            $birthParts[] = (string)$formData['place_of_birth'];
        }
        if (!empty($formData['country_of_birth'])) {
            $birthParts[] = (string)$formData['country_of_birth'];
        }
        if (!empty($birthParts)) {
            $pdfFields['PlaceAndCountryOfBirthOfApplicant'] = implode(', ', $birthParts);
        }
        
        // Spouse: Name and Nationality
        $spouseParts = [];
        if (!empty($formData['spouse_name'])) {
            $spouseParts[] = (string)$formData['spouse_name'];
        }
        if (!empty($formData['spouse_nationality'])) {
            $spouseParts[] = (string)$formData['spouse_nationality'];
        }
        if (!empty($spouseParts)) {
            $pdfFields['NameOfSpouseAndNationality'] = implode(', ', $spouseParts);
        }
        
        // Profession/Employer Details
        $professionParts = [];
        if (!empty($formData['profession'])) {
            $professionParts[] = (string)$formData['profession'];
        }
        if (!empty($formData['employer_details'])) {
            $professionParts[] = (string)$formData['employer_details'];
        }
        if (!empty($professionParts)) {
            $pdfFields['Profession/EmployersDetails'] = implode(' | ', $professionParts);
        }
        
        // Passport fields (mapped to Textbox14-17)
        // Based on PDF layout: Number, Date of Issue, Place of Issue, Validity
        if (!empty($formData['passport_number'])) {
            $pdfFields['Textbox14'] = (string)$formData['passport_number'];
        }
        
        // Textbox15: Date of Issue (formatted date)
        if (!empty($formData['passport_date_of_issue'])) {
            $pdfFields['Textbox15'] = $this->formatDate($formData['passport_date_of_issue']);
        }
        
        // Textbox16: Place of Issue (text)
        if (!empty($formData['passport_place_of_issue'])) {
            $pdfFields['Textbox16'] = (string)$formData['passport_place_of_issue'];
        }
        
        // Textbox17: Validity (formatted date)
        if (!empty($formData['passport_validity'])) {
            $pdfFields['Textbox17'] = $this->formatDate($formData['passport_validity']);
        }
        
        // Textbox18 - Likely registration number or date
        // If registered with mission, use registration number
        if (!empty($formData['is_registered_with_mission']) && $formData['is_registered_with_mission']) {
            if (!empty($formData['registration_number'])) {
                $pdfFields['Textbox18'] = (string)$formData['registration_number'];
            } elseif (!empty($formData['registration_date'])) {
                $pdfFields['Textbox18'] = $this->formatDate($formData['registration_date']);
            }
        }

        return $pdfFields;
    }

    /**
     * Format date for PDF (DD/MM/YYYY or as needed)
     */
    private function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '';
        }
        
        try {
            $dateObj = new \DateTime($date);
            return $dateObj->format('d/m/Y'); // Adjust format as needed
        } catch (\Exception $e) {
            return $date; // Return as-is if parsing fails
        }
    }

    /**
     * Create FDF (Forms Data Format) file for pdftk
     */
    private function createFdfFile(string $fdfPath, array $fields): void
    {
        $fdfContent = "%FDF-1.2\n";
        $fdfContent .= "1 0 obj\n";
        $fdfContent .= "<<\n";
        $fdfContent .= "/FDF\n";
        $fdfContent .= "<<\n";
        $fdfContent .= "/Fields [\n";
        
        foreach ($fields as $fieldName => $fieldValue) {
            $fieldValue = $this->escapeFdfValue($fieldValue);
            $fdfContent .= "<<\n";
            $fdfContent .= "/T ($fieldName)\n";
            $fdfContent .= "/V ($fieldValue)\n";
            $fdfContent .= ">>\n";
        }
        
        $fdfContent .= "]\n";
        $fdfContent .= ">>\n";
        $fdfContent .= ">>\n";
        $fdfContent .= "endobj\n";
        $fdfContent .= "trailer\n";
        $fdfContent .= "<<\n";
        $fdfContent .= "/Root 1 0 R\n";
        $fdfContent .= ">>\n";
        $fdfContent .= "%%EOF\n";
        
        file_put_contents($fdfPath, $fdfContent);
    }

    /**
     * Escape special characters for FDF format
     */
    private function escapeFdfValue(string $value): string
    {
        // First, decode any HTML entities that might have been encoded
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Escape parentheses and backslashes for FDF format
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('(', '\\(', $value);
        $value = str_replace(')', '\\)', $value);
        return $value;
    }

    /**
     * Get the PDF template path (for admin to upload/update)
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * Check if template exists
     */
    public function templateExists(): bool
    {
        return file_exists($this->templatePath);
    }
}

