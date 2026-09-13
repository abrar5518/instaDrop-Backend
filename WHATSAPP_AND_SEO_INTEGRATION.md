# InstaDrop Courier Services — Meta WhatsApp Cloud API & SEO/Analytics Integration Guide

This guide details the complete Meta WhatsApp Cloud API (`v25.0`), Analytics (Meta Pixel, GA4, GTM, Clarity, Search Console), and Dynamic SEO (`robots.txt`, `llms.txt`, `sitemap.xml`, Schema.org) integration for **InstaDrop Courier Backend**.

---

## 1. Environment Configuration (`.env`)

Add the following environment variables to your `courier-backend/.env` file:

```env
WHATSAPP_ENABLED=true
WHATSAPP_GRAPH_VERSION=v25.0
WHATSAPP_ACCESS_TOKEN=your-meta-access-token
WHATSAPP_PHONE_NUMBER_ID=1349924141534825
WHATSAPP_BUSINESS_ACCOUNT_ID=1391593043055339
WHATSAPP_ADMIN_NUMBER=447852502775
WHATSAPP_ORDER_TEMPLATE=new_order_admin_alert
WHATSAPP_INQUIRY_TEMPLATE=new_inquiry_admin_alert
WHATSAPP_PAYMENT_TEMPLATE=payment_received_admin_alert
WHATSAPP_TEMPLATE_LANGUAGE=en
```

---

## 2. Meta WhatsApp Message Templates Setup

Create and submit the following three **Utility** templates in your Meta WhatsApp Manager ([https://business.facebook.com/wa/manage/templates/](https://business.facebook.com/wa/manage/templates/)) under Business Account ID `1391593043055339`:

### Template 1: `new_inquiry_admin_alert`
- **Category**: Utility
- **Language**: English (`en`)
- **Body**:
```text
New customer enquiry received

Name: {{1}}
Phone: {{2}}
Email: {{3}}
Subject: {{4}}
Message: {{5}}

Open the admin panel to review this enquiry.
```

### Template 2: `new_order_admin_alert`
- **Category**: Utility
- **Language**: English (`en`)
- **Body**:
```text
New order received

Order: #{{1}}
Customer: {{2}}
Phone: {{3}}
Total: {{4}}
Payment: {{5}}

Open the admin panel to review and process this order.
```

### Template 3: `payment_received_admin_alert`
- **Category**: Utility
- **Language**: English (`en`)
- **Body**:
```text
New payment received

Payment ID: #{{1}}
Customer: {{2}}
Amount: {{3}}
Payment Method: {{4}}
Status: {{5}}

Open the admin panel to view payment details.
```

---

## 3. Dynamic SEO & AI Routes

The following endpoints generate live, dynamic output driven by Admin Settings:

- **`GET /robots.txt`**: Dynamic robots file pointing to `sitemap.xml` and disallowing admin routes.
- **`GET /llms.txt`**: Dynamic Markdown file for AI models & LLMs detailing company background, contact info, operational stats, and API links.
- **`GET /sitemap.xml`**: Dynamic XML sitemap indexing all site pages.
- **`GET /api/v1/analytics-settings`**: Public JSON endpoint returning active Meta Pixel ID, GA4 Measurement ID, GTM ID, Clarity ID, Search Console meta tag, and Schema.org Organization JSON-LD arrays.

---

## 4. Manual Test Command

Run manual connectivity test to verify delivery to Meta API:

```bash
php artisan whatsapp:test-admin-alert --type=inquiry
php artisan whatsapp:test-admin-alert --type=payment
php artisan whatsapp:test-admin-alert --type=order
```
