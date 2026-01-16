<?php
/**
 * PDF Field Extractor Helper Script
 * 
 * This script helps extract PDF form field names using pdftk
 * 
 * Usage: php extract_pdf_fields.php
 */

$pdfFile = __DIR__ . '/misc_application_form.pdf';

if (!file_exists($pdfFile)) {
    echo "❌ PDF file not found: $pdfFile\n";
    echo "Please make sure 'misc application form-new.pdf' is renamed to 'misc_application_form.pdf'\n";
    exit(1);
}

echo "📄 Extracting form fields from: " . basename($pdfFile) . "\n";
echo str_repeat("=", 60) . "\n\n";

// Check if pdftk is available
$output = [];
$returnVar = 0;
exec('pdftk --version 2>&1', $output, $returnVar);

if ($returnVar !== 0) {
    echo "❌ pdftk is not installed or not in PATH\n";
    echo "Please install pdftk first:\n";
    echo "  Windows: Download from https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/\n";
    echo "  Linux: sudo apt-get install pdftk\n";
    echo "  macOS: brew install pdftk-java\n";
    exit(1);
}

echo "✅ pdftk is available\n\n";

// Extract fields
$command = sprintf('pdftk "%s" dump_data_fields 2>&1', escapeshellarg($pdfFile));
exec($command, $output, $returnVar);

if ($returnVar !== 0) {
    echo "❌ Error extracting fields:\n";
    echo implode("\n", $output) . "\n";
    exit(1);
}

if (empty($output)) {
    echo "⚠️  No form fields found in PDF. The PDF might not have form fields.\n";
    echo "You may need to create form fields in the PDF first.\n";
    exit(1);
}

// Parse output
$fields = [];
$currentField = null;

foreach ($output as $line) {
    $line = trim($line);
    
    if (empty($line)) {
        continue;
    }
    
    if (strpos($line, '---') === 0) {
        if ($currentField !== null) {
            $fields[] = $currentField;
        }
        $currentField = [];
        continue;
    }
    
    if (strpos($line, 'FieldName:') === 0) {
        $currentField['name'] = trim(str_replace('FieldName:', '', $line));
    } elseif (strpos($line, 'FieldType:') === 0) {
        $currentField['type'] = trim(str_replace('FieldType:', '', $line));
    } elseif (strpos($line, 'FieldValue:') === 0) {
        $currentField['value'] = trim(str_replace('FieldValue:', '', $line));
    }
}

if ($currentField !== null) {
    $fields[] = $currentField;
}

if (empty($fields)) {
    echo "⚠️  No form fields found in PDF.\n";
    exit(1);
}

echo "✅ Found " . count($fields) . " form field(s):\n\n";

// Display fields
foreach ($fields as $index => $field) {
    $num = $index + 1;
    echo "Field #{$num}:\n";
    echo "  Name: " . ($field['name'] ?? 'N/A') . "\n";
    echo "  Type: " . ($field['type'] ?? 'N/A') . "\n";
    if (!empty($field['value'])) {
        echo "  Default Value: " . $field['value'] . "\n";
    }
    echo "\n";
}

// Generate PHP code snippet
echo str_repeat("=", 60) . "\n";
echo "📝 PHP Field Mapping Code:\n";
echo str_repeat("=", 60) . "\n\n";

echo "// Update this in PdfFormService.php -> prepareFieldData() method\n";
echo "\$fieldMap = [\n";

// Common mappings (you'll need to adjust these)
$commonMappings = [
    'full_name' => ['full_name', 'name', 'applicant_name', 'fullname'],
    'nationality_applicant' => ['nationality', 'nationality_applicant', 'applicant_nationality'],
    'date_of_birth' => ['date_of_birth', 'dob', 'birth_date', 'date_of_birth_applicant'],
    'place_of_birth' => ['place_of_birth', 'birth_place', 'place_of_birth_applicant'],
    'country_of_birth' => ['country_of_birth', 'birth_country', 'country_of_birth_applicant'],
    'phone_number' => ['phone', 'phone_number', 'tel', 'contact_number'],
    'email_address' => ['email', 'email_address', 'email_addr'],
    'passport_number' => ['passport_number', 'passport_no', 'passport'],
    'present_address_sa' => ['present_address', 'address', 'current_address', 'address_sa'],
];

foreach ($fields as $field) {
    $pdfFieldName = $field['name'] ?? '';
    if (empty($pdfFieldName)) {
        continue;
    }
    
    // Try to find matching form field
    $formField = null;
    foreach ($commonMappings as $formKey => $possibleNames) {
        if (in_array(strtolower($pdfFieldName), array_map('strtolower', $possibleNames))) {
            $formField = $formKey;
            break;
        }
    }
    
    if ($formField) {
        echo "    '{$formField}' => '{$pdfFieldName}',\n";
    } else {
        // Show as comment for manual mapping
        echo "    // 'FORM_FIELD_NAME' => '{$pdfFieldName}', // TODO: Map this field\n";
    }
}

echo "];\n\n";

echo str_repeat("=", 60) . "\n";
echo "💡 Next Steps:\n";
echo "1. Review the field names above\n";
echo "2. Copy the PHP code snippet\n";
echo "3. Update PdfFormService.php with the correct mappings\n";
echo "4. Test by submitting a form and checking the filled PDF\n";
echo str_repeat("=", 60) . "\n";


