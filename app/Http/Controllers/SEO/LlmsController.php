<?php

namespace App\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSetting;
use App\Models\Order;
use App\Models\QuoteRequest;
use Illuminate\Http\Response;

class LlmsController extends Controller
{
    /**
     * Generate dynamic llms.txt summary file for AI models and LLMs.
     */
    public function index(): Response
    {
        $settings = AnalyticsSetting::getSettings();
        $siteName = $settings->site_name ?: 'InstaDrop Courier Services';
        $appUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');

        $content = "# {$siteName}\n\n";
        $content .= "> InstaDrop UK Same-Day Courier Services - Urgent 60-Minute Pickup & Direct Door-to-Door Delivery.\n\n";

        $content .= "## Contact & Support Details\n\n";
        $content .= "- **Company**: {$siteName}\n";
        if ($settings->contact_email) {
            $content .= "- **Dispatch Email**: {$settings->contact_email}\n";
        }
        if ($settings->contact_phone) {
            $content .= "- **Hotline Phone**: {$settings->contact_phone}\n";
        }
        if ($settings->contact_address) {
            $content .= "- **Head Office**: {$settings->contact_address}\n";
        }
        $content .= "\n";

        $content .= "## Operational Capabilities\n\n";
        $content .= "- **Service Scope**: Same-Day UK Delivery, Medical Couriers, Legal Briefs, Pallets, Urgent Documents.\n";
        $content .= "- **Vehicle Fleet**: Bicycle, Motorbike, Small Van, Medium Van, Large Van, Luton Tail-Lift Van.\n";
        $content .= "- **Total Quotes Processed**: " . QuoteRequest::count() . "\n";
        $content .= "- **Completed Orders**: " . Order::where('status', 'delivered')->count() . "\n\n";

        $content .= "## Public API Endpoints for AI Agents\n\n";
        $content .= "- `GET {$appUrl}/api/v1/settings`: Public business contact & system settings\n";
        $content .= "- `POST {$appUrl}/api/v1/quotes`: Submit instant 60-minute pickup delivery quote request\n";
        $content .= "- `GET {$appUrl}/api/v1/tracking/{tracking_number}`: Live delivery tracking lookup\n";
        $content .= "- `GET {$appUrl}/api/v1/analytics-settings`: Analytics, Pixels & Organization JSON-LD schemas\n";
        $content .= "- `GET {$appUrl}/sitemap.xml`: XML Sitemap\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
