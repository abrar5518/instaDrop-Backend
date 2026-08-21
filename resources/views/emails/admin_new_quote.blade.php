<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Quote Request Received — InstaDrop Courier</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #0a192f; color: #ffffff; padding: 20px; border-radius: 12px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; color: #c6ff00; }
        .badge { background: #c6ff00; color: #0a192f; font-weight: bold; padding: 4px 12px; border-radius: 20px; display: inline-block; font-size: 12px; margin-top: 8px; }
        .section-title { font-size: 14px; font-weight: bold; color: #0a192f; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-top: 24px; }
        .field-grid { display: table; width: 100%; margin-top: 12px; }
        .field-row { display: table-row; }
        .field-cell { display: table-cell; padding: 8px; font-size: 13px; border-bottom: 1px solid #f8fafc; }
        .field-label { color: #64748b; font-weight: 600; width: 40%; }
        .field-value { color: #0f172a; font-weight: bold; width: 60%; }
        .cta-btn { display: block; width: 100%; text-align: center; background: #0a192f; color: #ffffff; text-decoration: none; padding: 14px; border-radius: 12px; font-weight: bold; font-size: 14px; margin-top: 24px; }
        .footer { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>InstaDrop Dispatch Alert</h1>
            <span class="badge">NEW QUOTE REQUEST #{{ $quote['quote_number'] ?? 'Q-88492' }}</span>
        </div>

        <p style="font-size: 14px; margin-top: 20px;">A new delivery quote request has been submitted on the website. Review details below:</p>

        <div class="section-title">Customer Information</div>
        <div class="field-grid">
            <div class="field-row">
                <div class="field-cell field-label">Customer Name:</div>
                <div class="field-cell field-value">{{ $quote['first_name'] ?? 'Sarah' }} {{ $quote['last_name'] ?? 'Mitchell' }}</div>
            </div>
            <div class="field-row">
                <div class="field-cell field-label">Email Address:</div>
                <div class="field-cell field-value">{{ $quote['email'] ?? 'sarah.m@company.co.uk' }}</div>
            </div>
            <div class="field-row">
                <div class="field-cell field-label">Phone Number:</div>
                <div class="field-cell field-value">{{ $quote['phone'] ?? '07123456789' }}</div>
            </div>
            <div class="field-row">
                <div class="field-cell field-label">Contact Preference:</div>
                <div class="field-cell field-value" style="color: #047857;">{{ strtoupper($quote['contact_preference'] ?? 'EMAIL') }}</div>
            </div>
        </div>

        <div class="section-title">Route & Vehicle Specs</div>
        <div class="field-grid">
            <div class="field-row">
                <div class="field-cell field-label">Collection Postcode:</div>
                <div class="field-cell field-value">{{ $quote['collection_postcode'] ?? 'M1 1AE' }}</div>
            </div>
            <div class="field-row">
                <div class="field-cell field-label">Delivery Postcode:</div>
                <div class="field-cell field-value">{{ $quote['delivery_postcode'] ?? 'SW1A 1AA' }}</div>
            </div>
            <div class="field-row">
                <div class="field-cell field-label">Vehicle Selected:</div>
                <div class="field-cell field-value" style="color: #0066ff;">{{ strtoupper(str_replace('_', ' ', $quote['vehicle_type'] ?? 'Luton Van')) }}</div>
            </div>
            <div class="field-row">
                <div class="field-cell field-label">Timescale:</div>
                <div class="field-cell field-value">{{ str_replace('_', ' ', $quote['timescale'] ?? 'ASAP 60-Min') }}</div>
            </div>
            <div class="field-row">
                <div class="field-cell field-label">Enquiry Type:</div>
                <div class="field-cell field-value">{{ ucfirst($quote['enquiry_type'] ?? 'Business') }}</div>
            </div>
            <div class="field-row">
                <div class="field-cell field-label">Additional Notes:</div>
                <div class="field-cell field-value" style="font-weight: normal; italic;">{{ $quote['additional_info'] ?? 'None provided.' }}</div>
            </div>
        </div>

        <a href="http://localhost:8000/admin/quotes" class="cta-btn">Open Admin Inspector & Set Price ➔</a>

        <div class="footer">
            InstaDrop Courier Services Ltd • Automated Dispatch System Notification
        </div>
    </div>
</body>
</html>
