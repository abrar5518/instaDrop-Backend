# Meta WhatsApp Cloud API (v25.0) Integration Guide — InstaDrop Courier

This document details the Meta WhatsApp Cloud API integration for **InstaDrop Courier Services**.

---

## 1. Required Environment Variables (`.env`)

Add the following environment variables to your production `.env` file:

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

> [!CAUTION]
> Never place the real permanent `WHATSAPP_ACCESS_TOKEN` in `.env.example` or commit it to Git. Keep `.env.example` with empty token placeholder (`WHATSAPP_ACCESS_TOKEN=`).

---

## 2. Meta Message Templates Setup

Create and submit the following three **Utility** templates in your [Meta WhatsApp Manager](https://business.facebook.com/wa/manage/templates/) under Business Account ID `1391593043055339`:

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
- **Parameter Mapping**:
  1. Customer name
  2. Customer phone or `Not provided`
  3. Customer email or `Not provided`
  4. Enquiry subject/type or `General enquiry`
  5. Safely truncated & normalized enquiry text

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
- **Parameter Mapping**:
  1. Order reference / number
  2. Customer name
  3. Customer phone
  4. Formatted total including currency (£180.00)
  5. Payment status / method

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

## 3. Queue Worker & Production Deployment Setup

Notifications are dispatched asynchronously after database transactions commit (`afterCommit()`).

### Development Queue Run:
```bash
php artisan queue:work --tries=3 --backoff=10
```

### Production Deployment Commands:
```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan queue:restart
```

---

## 4. Manual Connectivity Test Command

Run the interactive Artisan command to test delivery to the admin number (`447852502775`):

```bash
php artisan whatsapp:test-admin-alert --type=inquiry
php artisan whatsapp:test-admin-alert --type=order
php artisan whatsapp:test-admin-alert --type=payment
```

---

## 5. How to Disable WhatsApp Alerts

Set `WHATSAPP_ENABLED=false` in `.env` and clear config cache (`php artisan config:cache`). No code changes required.

---

## 6. Outbound-Only Notifications & Webhooks

Webhooks are not required for outbound admin notifications. The system sends HTTP POST requests directly to `https://graph.facebook.com/v25.0/1349924141534825/messages` using Bearer authentication.
