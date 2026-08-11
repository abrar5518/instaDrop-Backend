# InstaDrop — Laravel Backend & Admin Panel Documentation

This document contains the complete technical architectural mapping between the **Next.js 16 Frontend** (`courier-website`) and the **Laravel Backend & Admin Panel** (`courier-backend`). 

Any AI agent or developer working on the backend should read this document to understand what exists on the frontend, what APIs are expected, and how the parcel delivery business workflow operates.

---

## 1. Project Overview & Business Workflow

**Brand Name**: InstaDrop (InstaDrop Same-Day Courier)  
**Frontend URL**: `http://localhost:3000` (Next.js 16, App Router, TypeScript, Tailwind v4)  
**Backend URL**: `http://localhost:8000` (Laravel 11, REST APIs, Admin Dashboard, MySQL, Blade/Livewire)

### High-Level Customer Journey (Step-by-Step):

```
┌───────────────────────────┐      ┌───────────────────────────┐      ┌───────────────────────────┐
│ 1. Customer Quote Form    │ ───> │ 2. Laravel Backend API    │ ───> │ 3. Business WhatsApp      │
│ Collection & Dropoff Code │      │ Saves Quote & Triggers    │      │ Auto-Acknowledgement      │
│ Vehicle, Size, Preferred  │      │ Notification to Admin     │      │ Sent to Customer & Admin  │
└───────────────────────────┘      └───────────────────────────┘      └───────────────────────────┘
                                                 │
                                                 ▼
┌───────────────────────────┐      ┌───────────────────────────┐      ┌───────────────────────────┐
│ 6. Customer Pays Online   │ <─── │ 5. Invoice & Payment Link │ <─── │ 4. Admin Dashboard        │
│ Secure Gateway (Stripe)   │      │ Sent via Preferred Method │      │ Admin Reviews & Enters    │
│ Instant Receipt           │      │ (WhatsApp / Email)        │      │ Final Selling Price       │
└───────────────────────────┘      └───────────────────────────┘      └───────────────────────────┘
              │
              ▼
┌───────────────────────────┐      ┌───────────────────────────┐      ┌───────────────────────────┐
│ 7. Driver Dispatched      │ ───> │ 8. Live Status Updates    │ ───> │ 9. Delivery & POD Upload  │
│ Admin Books Courier       │      │ Dispatched -> In Transit  │      │ Admin Uploads POD Photo   │
│ On Courier Exchange       │      │ Auto WhatsApp / Email     │      │ Auto Dispatched to Client │
└───────────────────────────┘      └───────────────────────────┘      └───────────────────────────┘
```

---

## 2. Next.js Frontend Routes & Payload Specifications

The frontend (`courier-website`) has **17 static/SSR routes**. Here are the primary routes interacting with the backend:

### A. `/instant-quote` & `/` (Speedy Quote Calculator)
* **Frontend Component**: [`src/components/Home/QuoteWidget.tsx`](file:///c:/Users/hp/Desktop/projects/courier-website/src/components/Home/QuoteWidget.tsx)
* **User Input Fields**:
  - `pickup_postcode` (string, required — e.g. "M1 1AE")
  - `delivery_postcode` (string, required — e.g. "SW1A 1AA")
  - `vehicle_type` (enum: `courier_car`, `small_van`, `medium_van`, `large_van`, `luton_tail_lift`)
  - `preferred_time` (enum: `asap_60min`, `today_afternoon`, `scheduled_date`)
  - `parcel_type` (string, optional — e.g. "2 Pallets", "1 Document Box", "1,000kg Machine")
  - `contact_name` (string, required)
  - `contact_email` (string, required)
  - `contact_phone` (string, required)
  - `preferred_contact_method` (enum: `whatsapp`, `email`, `phone_call`)
* **Expected API Endpoint**: `POST /api/v1/quotes`
* **Response Payload**:
  ```json
  {
    "success": true,
    "quote_id": "Q-88492",
    "message": "Quote request received! Our dispatch team will contact you shortly via your preferred method."
  }
  ```

### B. `/track-delivery` (Live Order Tracking & Digital POD)
* **Frontend Route**: `src/app/track-delivery/page.tsx`
* **User Input**: Tracking Number (e.g. `INSTA-884920`)
* **Expected API Endpoint**: `GET /api/v1/tracking/{tracking_number}`
* **Response Payload**:
  ```json
  {
    "success": true,
    "tracking_number": "INSTA-884920",
    "status": "in_transit", // pending, dispatched, in_transit, delivered
    "pickup_city": "Manchester",
    "delivery_city": "London",
    "driver_name": "Dave Miller",
    "vehicle": "Luton Tail-Lift Van",
    "estimated_arrival": "2026-08-12 14:30:00",
    "pod": {
      "recipient_name": "Sarah Mitchell",
      "signature_url": "https://backend.instadrop.co.uk/storage/pods/sig-884920.png",
      "delivered_at": "2026-08-12 14:15:00"
    }
  }
  ```

### C. Online Payment Page (`/pay/{payment_token}`)
* **Frontend / Backend Route**: Public Payment Checkout Portal
* **Expected API Endpoint**: `GET /api/v1/invoices/{token}`
* **Processing Endpoint**: `POST /api/v1/payments/process`
* **Features**: Displays invoice breakdown (Base rate, mileage, VAT, Total), Stripe Credit Card input, and Instant Payment Confirmation.

---

## 3. Database Schema Specifications (Laravel Migrations)

### 1. `quote_requests` Table
```sql
CREATE TABLE quote_requests (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    quote_number VARCHAR(30) UNIQUE NOT NULL, -- e.g. Q-88492
    pickup_postcode VARCHAR(20) NOT NULL,
    delivery_postcode VARCHAR(20) NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    preferred_time VARCHAR(50) DEFAULT 'asap_60min',
    parcel_details TEXT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(50) NOT NULL,
    preferred_contact_method ENUM('whatsapp', 'email', 'phone_call') DEFAULT 'whatsapp',
    status ENUM('pending', 'quoted', 'converted', 'cancelled') DEFAULT 'pending',
    admin_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 2. `orders` Table
```sql
CREATE TABLE orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    quote_request_id BIGINT NULL FOREIGN KEY,
    tracking_number VARCHAR(50) UNIQUE NOT NULL, -- e.g. INSTA-884920
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    preferred_contact_method ENUM('whatsapp', 'email', 'phone_call') DEFAULT 'whatsapp',
    pickup_address TEXT NOT NULL,
    delivery_address TEXT NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    carrier_name VARCHAR(100) NULL, -- e.g. Courier Exchange Driver #402
    quoted_selling_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending_payment', 'paid', 'dispatched', 'collected', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending_payment',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 3. `invoices` Table
```sql
CREATE TABLE invoices (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL FOREIGN KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL, -- e.g. INV-2026-001
    payment_token VARCHAR(100) UNIQUE NOT NULL, -- secure token for payment link
    subtotal DECIMAL(10, 2) NOT NULL,
    vat_amount DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_method VARCHAR(50) NULL, -- e.g. stripe, card, paypal
    payment_transaction_id VARCHAR(100) NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 4. `pods` (Proof of Delivery) Table
```sql
CREATE TABLE pods (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL FOREIGN KEY,
    recipient_name VARCHAR(100) NOT NULL,
    signature_path VARCHAR(255) NULL,
    photo_path VARCHAR(255) NULL,
    delivered_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 5. `system_settings` Table
```sql
CREATE TABLE system_settings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(100) DEFAULT 'InstaDrop Courier Services',
    admin_whatsapp_number VARCHAR(50) DEFAULT '+448001234455',
    admin_notification_email VARCHAR(100) DEFAULT 'dispatch@instadrop.co.uk',
    currency_code VARCHAR(10) DEFAULT 'GBP',
    vat_rate DECIMAL(5, 2) DEFAULT 20.00,
    whatsapp_api_token VARCHAR(255) NULL,
    stripe_public_key VARCHAR(255) NULL,
    stripe_secret_key VARCHAR(255) NULL,
    updated_at TIMESTAMP NULL
);
```

---

## 4. Admin Panel Modules & UX Requirements

The Admin Panel must be **extremely clean, modern, and easy to use** for non-technical administrative staff. Use simple English labels:

1. **Dashboard Home**:
   - Quick statistics cards: Total Quotes Today, Pending Quotations, Unpaid Invoices, Active Deliveries In-Transit, Completed PODs Today.
   - Quick Dispatch Feed of new customer quote requests.
2. **Quote Requests Module**:
   - View customer pickup/delivery details & preferred contact method.
   - **Enter Selling Price Form**: Input price -> Click "Generate Invoice & Send Payment Link".
3. **Orders & Live Status Manager**:
   - Single-click status updater: `Dispatched` -> `Collected` -> `In Transit` -> `Delivered`.
   - Each status update automatically triggers a WhatsApp/Email message to the customer.
4. **POD Upload Module**:
   - Drag-and-drop file uploader for signature photo / PDF.
   - Click "Save & Dispatch POD to Customer".
5. **System Settings Module**:
   - Editable fields: Admin Business WhatsApp Number, Admin Email, Stripe Gateway API keys, WhatsApp API credentials, Invoice Terms.

---

## 5. API Endpoints Contract (Laravel -> Next.js)

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/v1/quotes` | Submit new customer quote request from frontend |
| `GET` | `/api/v1/tracking/{tracking_number}` | Fetch live tracking status and POD file |
| `GET` | `/api/v1/invoices/{token}` | Fetch invoice details for online payment page |
| `POST` | `/api/v1/payments/process` | Process online credit card payment |
| `GET` | `/api/v1/settings/public` | Fetch public business contact info & WhatsApp hotline |
