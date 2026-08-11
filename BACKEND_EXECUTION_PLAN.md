# InstaDrop — Laravel Backend & Admin Panel AI Execution Plan

This master execution plan breaks down the development of the **InstaDrop Laravel Backend & Admin Panel** (`courier-backend`) into small, manageable, bite-sized steps. 

Each step is self-contained so that an AI assistant can complete one step at a time with 100% precision.

---

## 📋 Step-by-Step AI Work Plan

### Step 1: Project Setup & Structure (COMPLETED)
- [x] Create project directory `c:\Users\hp\Desktop\projects\courier-backend`
- [x] Write `FRONTEND_DOCUMENTATION.md` (Frontend-to-Backend mapping, database schema, API contracts)
- [x] Write `BACKEND_EXECUTION_PLAN.md` (Bite-sized step-by-step development plan)
- [x] Initialize core folder structure, environment files, and composer/package manifests

---

### Step 2: Database Migrations & Eloquent Models
- [ ] Create Migration & Model for `QuoteRequest` (`quote_requests` table)
- [ ] Create Migration & Model for `Order` (`orders` table)
- [ ] Create Migration & Model for `Invoice` (`invoices` table)
- [ ] Create Migration & Model for `Pod` (`pods` table)
- [ ] Create Migration & Model for `SystemSetting` (`system_settings` table)
- [ ] Create Database Seeder for initial Admin Settings & sample data

---

### Step 3: REST API Controllers for Next.js Frontend
- [ ] Create `Api/QuoteController.php`:
  - `POST /api/v1/quotes` (Handles Quote Submission from Next.js frontend)
  - Automatically triggers initial WhatsApp / Email acknowledgment
- [ ] Create `Api/OrderTrackingController.php`:
  - `GET /api/v1/tracking/{tracking_number}` (Returns status, driver info, POD link)
- [ ] Create `Api/PaymentController.php`:
  - `GET /api/v1/invoices/{token}` (Returns invoice line items for payment page)
  - `POST /api/v1/payments/process` (Processes payment & updates invoice/order status)

---

### Step 4: Messaging & Notification Services (WhatsApp & Email)
- [ ] Create `Services/WhatsAppService.php`:
  - Configurable API client (Twilio / Meta Business API / WhatsApp Gateway)
  - Method `sendQuoteAcknowledgment($quote)`
  - Method `sendPaymentLink($invoice)`
  - Method `sendStatusUpdate($order, $status)`
  - Method `sendPodNotification($order, $pod)`
- [ ] Create `Services/EmailNotificationService.php`:
  - Auto-acknowledgment email template
  - Payment link invoice email template
  - POD attachment email template

---

### Step 5: PDF Invoice & Online Payment Link Generator
- [ ] Create `Services/PdfInvoiceService.php`:
  - Generates downloadable PDF Invoice with business details, VAT, subtotal, and total
- [ ] Create Payment Link Generator:
  - Generates unique secure token URL `http://localhost:3000/pay/{token}` or `http://localhost:8000/pay/{token}`
- [ ] Stripe / Credit Card Gateway Integration Controller

---

### Step 6: Super User-Friendly Admin Panel UI
- [ ] Create `Admin/DashboardController.php`:
  - Overview cards: Today's Quotes, Pending Quotes, Unpaid Invoices, Active Deliveries, PODs
- [ ] Create `Admin/QuoteRequestController.php`:
  - View incoming quote requests with customer preferred contact method
  - **Quotation Form**: Input final selling price -> Click "Generate Invoice & Send Payment Link"
- [ ] Create `Admin/OrderController.php`:
  - Order list table & live delivery status switcher (`Dispatched`, `Collected`, `In Transit`, `Delivered`)
- [ ] Create `Admin/PodController.php`:
  - Upload POD photo/signature -> Auto-dispatch to customer via WhatsApp/Email
- [ ] Create `Admin/SettingsController.php`:
  - Configure Admin Business WhatsApp Number, Admin Email, Stripe Gateway keys, and Business details

---

### Step 7: Next.js Frontend Integration & End-to-End Testing
- [ ] Connect Next.js Quote Form (`/instant-quote`) to Laravel API (`POST /api/v1/quotes`)
- [ ] Connect Next.js Live Tracking Page (`/track-delivery`) to Laravel API (`GET /api/v1/tracking/{id}`)
- [ ] Verify End-to-End Flow: Quote Submission -> Admin Pricing -> Payment Link -> Online Payment -> Status Update -> POD Upload.
