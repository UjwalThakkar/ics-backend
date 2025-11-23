<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\VisaCountry;
use IndianConsular\Models\VisaType;
use IndianConsular\Models\VisaDownload;

class VisaController extends BaseController
{
    private VisaCountry $countryModel;
    private VisaType $visaTypeModel;
    private VisaDownload $downloadModel;

    public function __construct()
    {
        parent::__construct();
        $this->countryModel   = new VisaCountry();
        $this->visaTypeModel  = new VisaType();
        $this->downloadModel  = new VisaDownload();
    }

    // GET /api/visa/countries
    public function getCountries(array $data, array $params): array
    {
        $countries = $this->countryModel->getActiveCountries();
        return $this->success(['countries' => $countries]);
    }

    // GET /api/visa/country/south-africa
    public function getCountryDetail(array $data, array $params): array
    {
        $slug = $params['slug'] ?? 'south-africa';
        $country = $this->countryModel->findBySlug($slug);

        if (!$country) {
            return $this->error('Country not found', 404);
        }

        $visaTypes = $this->visaTypeModel->getByCountryId((int)$country['id']);

        return $this->success([
            'country'    => $country,
            'visa_types' => $visaTypes
        ]);
    }

    // GET /api/visa/type/tourist-visa?country=south-africa
    public function getVisaTypeDetail(array $data, array $params): array
    {
        // Get slug from path OR query parameter
        $slug = $params['slug'] ?? $data['slug'] ?? $data['type'] ?? '';
        $countrySlug = $data['country'] ?? 'south-africa';

        if (empty($slug)) {
            return $this->error('Visa type slug is required. Use ?slug=business-visa or /type/business-visa', 400);
        }

        $country = $this->countryModel->findBySlug($countrySlug);
        if (!$country) {
            return $this->error('Country not found', 404);
        }

        $visa = $this->visaTypeModel->findBySlugAndCountry($slug, (int)$country['id']);
        if (!$visa) {
            return $this->error('Visa type not found', 404);
        }

        // Decode JSON fields
        $feesJson  = $visa['fees_json'] ?? [];
        $documents = $visa['documents_json'] ?? [];

        // Get downloadable forms
        $downloads = $this->downloadModel->getByVisaTypeId((int)$visa['id']);

        // Build VFS-style rich response
        $richVisa = [
            "id"               => (int)$visa['id'],
            "name"             => $visa['name'],
            "slug"             => $visa['slug'],
            "country_name"     => $country['name'],
            "country_slug"     => $country['slug'],

            "overview"         => $visa['overview'] ?: "No overview available.",

            "important_alerts" => [
                "<strong style='color:red'>MANDATORY:</strong> You MUST complete the online application at indianvisaonline.gov.in <u>before</u> booking appointment.",
                "Yellow Fever vaccination certificate required if travelling from or via endemic countries."
            ],

            "fees" => [
                "table_headers" => ["Description", "Amount (INR)", "Remarks"],
                "rows" => [
                    ["Consular Fee",                    number_format($feesJson['consular_fee'] ?? 0),     "Payable to High Commission of South Africa"],
                    ["VFS Service Charge",              number_format($feesJson['vfs_fee'] ?? 2250),       "Inclusive of VAT"],
                    ["Optional SMS Service",            "100",                                              "For status updates"],
                    ["<strong>Total Payable at VFS</strong>", "<strong>" . number_format(($feesJson['consular_fee'] ?? 0) + ($feesJson['vfs_fee'] ?? 2250) + 100) . "</strong>", "<strong>Cash / Card accepted</strong>"]
                ],
                "footnotes" => [
                    "All fees are non-refundable",
                    "Urgent processing (48 hrs) available at additional cost – contact center"
                ]
            ],

            "processing_time" => [
                "standard" => $visa['processing_time'] ?: "5–7 working days",
                "urgent"   => "48 hours (additional charges apply)",
                "note"     => "Processing time starts only after biometric enrolment and complete documentation."
            ],

            "documents_required" => [
                "mandatory" => $documents,
                "additional_if_applicable" => [
                    "For minors: Birth certificate + notarised consent from both parents",
                    "If visiting family/friends: Invitation letter + host’s ID/passport copy + proof of residence"
                ]
            ],

            "photo_specifications" => [
                "image_preview_url" => "/images/visa-photo-specification.jpg", // put a real image in public/images
                "specs" => [
                    "Size: 35mm × 45mm",
                    "Colour photograph with plain WHITE background",
                    "Face must cover 70–80% of the photo area",
                    "Head centred, looking straight at camera",
                    "Neutral expression, mouth closed, eyes open & visible",
                    "No glasses, no headgear (except religious – face fully visible)",
                    "Taken within last 6 months"
                ],
                "note" => "Photo booth available at all VFS centres for ₹200"
            ],

            "download_forms" => array_map(function ($d) {
                return [
                    "title"    => $d['title'],
                    "url"      => $d['file_url'],
                    "type"     => "pdf",
                    "size"     => $d['file_size_kb'] ? round($d['file_size_kb'] / 1024, 1) . " MB" : null,
                    "is_checklist" => (bool)$d['is_checklist']
                ];
            }, $downloads),

            "online_application" => [
                "url"           => $visa['online_form_url'] ?: "https://indianvisaonline.gov.in/visa/index.html",
                "text"          => "Complete Online Application (Mandatory First Step)",
                "button_label"  => "Start Online Application →"
            ],

            "book_appointment" => [
                "text"          => "Ready to submit? Book your VFS appointment after completing online form",
                "button_label"  => "Book Appointment Now →",
                "url"           => "/booking?service=visa-{$country['slug']}-{$visa['slug']}"
            ],

            "last_updated" => date('d F Y')
        ];

        return $this->success(['visa' => $richVisa]);
    }
}
