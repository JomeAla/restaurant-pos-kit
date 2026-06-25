# Restaurant POS Kit — Task List

---

## Setup & Environment

- [x] **S1:** Initialize Laravel project with Composer
- [x] **S2:** Configure `.env` for SQLite database connection (MySQL inaccessible)
- [x] **S3:** Install and configure Laravel Sanctum for API authentication
- [x] **S4:** Install Laravel Filament 3.3.54 for admin panel
- [x] **S5:** Laravel Reverb v1.10.2 — installed and running via PM2 on port 8080
- [x] **S6:** Laravel Horizon replaced by PM2 (queue:work managed by PM2 auto-restart)
- [x] **S7:** Set up Vite 5 configuration with React plugin
- [x] **S8:** Initialize React SPA scaffolded in `/resources/js`
- [x] **S9:** Set up Tailwind CSS
- [ ] **S10:** Set up ESLint, Prettier, and code formatting standards — deferred
- [x] **S11:** Configure service worker for offline support
- [x] **S12:** Set up version control (Git) with initial commit
- [x] **S13:** Create database and run initial migration

---

## Phase 1 — Foundation (Auth, Menu, Tables)

### 1.1 Authentication & Staff Roles

#### Backend
- [x] **1.1.1:** Create `users` migration with fields: name, email, password, pin, phone, role_id, is_active, last_login_at
- [x] **1.1.2:** Create `roles` migration with: name, slug, description, is_default
- [x] **1.1.3:** Create `role_permissions` migration with: role_id, permission (string)
- [x] **1.1.4:** Create User model with relationships (role, permissions)
- [x] **1.1.5:** Create Role and RolePermission models
- [x] **1.1.6:** Seed default roles: Admin, Manager, Waiter, Cashier, Kitchen (with granular permissions)
- [x] **1.1.7:** Implement `POST /api/v1/auth/login` (email + password)
- [x] **1.1.8:** Implement `POST /api/v1/auth/pin` (PIN-based quick login for POS)
- [x] **1.1.9:** Implement `POST /api/v1/auth/logout`
- [x] **1.1.10:** Implement `GET /api/v1/auth/me`
- [x] **1.1.11:** Create Sanctum token-based authentication middleware `auth:sanctum`
- [x] **1.1.12:** Create permission-checking middleware `permission` (can:order.create, etc.)
- [x] **1.1.13:** Create AuthController
- [x] **1.1.14:** Create UserController with CRUD endpoints + roles list
- [x] **1.1.15:** Create RoleController with permission assignment

#### Frontend (SPA)
- [x] **1.1.16:** Build login page (email + password)
- [x] **1.1.17:** Build PIN login screen (numeric keypad for quick POS access)
- [x] **1.1.18:** Build password reset/forgot flow
- [x] **1.1.19:** Create auth context/hook for managing user session
- [x] **1.1.20:** Create protected route wrapper based on roles/permissions
- [x] **1.1.21:** Build user profile page (change password, update name)

#### Admin Panel (Filament)
- [x] **1.1.22:** Create Filament User resource (list, create, edit, delete staff accounts)
- [x] **1.1.23:** Create Filament Role resource with name/description fields
- [x] **1.1.24:** Add user login history viewer widget
- [x] **1.1.25:** Add account enable/disable toggle in user edit form

---

### 1.2 Menu Management

#### Backend
- [x] **1.2.1:** Create `categories` migration with: name, slug, description, sort_order, is_active, image
- [x] **1.2.2:** Create `menu_items` migration with: category_id, name, slug, description, price, cost, image, is_active, is_available, tax_rate
- [x] **1.2.3:** Create `modifiers` migration with: name, type (single/multi), is_required, min_selection, max_selection
- [x] **1.2.4:** Create `modifier_options` migration with: modifier_id, name, price_adjustment, is_default, is_active
- [x] **1.2.5:** Create `menu_item_modifier` pivot migration
- [x] **1.2.6:** Create `combos` migration with: name, description, price, is_active, start_date, end_date
- [x] **1.2.7:** Create `combo_items` migration with: combo_id, menu_item_id, quantity
- [x] **1.2.8:** Create models: Category, MenuItem, Modifier, ModifierOption, Combo, ComboItem
- [x] **1.2.9:** Implement CategoryController (CRUD)
- [x] **1.2.10:** Implement MenuItemController (CRUD + toggleAvailability)
- [x] **1.2.11:** Implement ModifierController (CRUD + option management)
- [x] **1.2.12:** Implement ComboController (CRUD with nested items)
- [x] **1.2.13:** Create image upload endpoint with file validation
- [x] **1.2.14:** Seed sample categories, menu items, modifiers, combos, tables, and floor plan
- [x] **1.2.15:** Implement ModifierOptionController (CRUD)

#### Frontend (SPA)
- [x] **1.2.15:** Build category list page with data from API
- [x] **1.2.16:** Build category create/edit form
- [x] **1.2.17:** Build menu items list page with search and filter
- [x] **1.2.18:** Build menu item create/edit form (with image upload)
- [x] **1.2.19:** Build modifier group management page
- [x] **1.2.20:** Build modifier option management (inline within modifier)
- [x] **1.2.21:** Build combo deal list page
- [x] **1.2.22:** Build availability toggle (quick in-stock/out-of-stock switch)

#### Admin Panel (Filament)
- [x] **1.2.23:** Create Filament Category resource
- [x] **1.2.24:** Create Filament Menu Item resource (with category relationship)
- [x] **1.2.25:** Create Filament Modifier resource
- [x] **1.2.26:** Create Filament Combo resource (with items repeater)

---

### 1.3 Table Management

#### Backend
- [x] **1.3.1:** Create `restaurant_tables` migration with: table_number, capacity, section, status (free/occupied/reserved/dirty), pos_x, pos_y, width, height, shape, floor_plan_id
- [x] **1.3.2:** Create `floor_plans` migration with: name, width, height, is_active
- [x] **1.3.3:** Create RestaurantTable and FloorPlan models
- [x] **1.3.4:** Implement TableController (CRUD + status update)
- [x] **1.3.5:** Implement FloorPlanController (save/load layout)

#### Frontend (SPA)
- [x] **1.3.6:** Build visual floor plan canvas with positioned tables
- [x] **1.3.7:** Build table creation dialog (number, capacity, section)
- [x] **1.3.8:** Build table status indicator with color coding
- [x] **1.3.9:** Build table click action (open POS for that table)
- [x] **1.3.10:** Build floor plan selector (if multiple floors/sections)

#### Admin Panel (Filament)
- [x] **1.3.11:** Create Filament RestaurantTable resource
- [x] **1.3.12:** Create Filament Floor Plan resource

---

## Phase 2 — Core POS (Orders, KDS, Payments)

### 2.1 Order Management

#### Backend
- [x] **2.1.1:** Create `orders` migration with: order_number, user_id, table_id, customer_name, customer_phone, type (dine-in/takeaway/delivery), status, subtotal, tax_total, discount_total, total, notes, ordered_at
- [x] **2.1.2:** Create `order_items` migration with: order_id, menu_item_id, quantity, unit_price, total_price, modifier_summary (JSON), status, notes, course
- [x] **2.1.3:** Create `order_status_logs` migration for status change history
- [x] **2.1.4:** Create models: Order, OrderItem, OrderStatusLog
- [x] **2.1.5:** Implement order status workflow logic (Pending → Sent → Preparing → Ready → Served → Paid → Closed)
- [x] **2.1.6:** Implement OrderController:
  - `GET /orders` — list with filters (date, status, table, type)
  - `POST /orders` — create order
  - `GET /orders/{id}` — order detail with items
  - `PUT /orders/{id}` — update order (notes, customer info)
  - `DELETE /orders/{id}` — void order with reason
  - `POST /orders/{id}/items` — add item to order
  - `PUT /orders/{id}/items/{itemId}` — update item (qty, notes)
  - `DELETE /orders/{id}/items/{itemId}` — remove/void item
  - `POST /orders/{id}/hold` — hold order
  - `POST /orders/{id}/release` — release held order
  - `POST /orders/{id}/send-kitchen` — send to KDS
- [x] **2.1.7:** Implement order numbering strategy (sequential per day)
- [x] **2.1.8:** Implement duplicate order detection

#### Frontend (SPA)
- [x] **2.1.9:** Build POS terminal main screen (order creation)
- [x] **2.1.10:** Build menu grid with category tabs for item selection
- [x] **2.1.11:** Build modifier selection dialog (sides, extras, customizations)
- [x] **2.1.12:** Build order cart panel (items list, quantities, running total)
- [x] **2.1.13:** Build table selection overlay (visual floor plan for dine-in)
- [x] **2.1.14:** Build order type selector (dine-in/takeaway/delivery)
- [x] **2.1.15:** Build order hold/release controls (in pending state)
- [x] **2.1.16:** Build order search and filter page
- [x] **2.1.17:** Build order detail view with full item list
- [x] **2.1.18:** Build item void dialog with reason selection
- [x] **2.1.19:** Build course assignment (starter/main/dessert) on items

---

### 2.2 Kitchen Display System (KDS)

#### Backend
- [x] **2.2.1:** Create `kitchen_tickets` migration with: order_id, course, status, sent_at
- [x] **2.2.2:** Create `kitchen_ticket_items` migration with: ticket_id, order_item_id, status (pending/preparing/ready)
- [x] **2.2.3:** Create models: KitchenTicket, KitchenTicketItem
- [x] **2.2.4:** Implement KitchenController:
  - `GET /kitchen/orders` — list open tickets
  - `PUT /kitchen/orders/{id}/items/{itemId}/status` — mark item preparing/ready
  - `POST /kitchen/orders/{id}/bump` — bump (dismiss) completed order
- [~] **2.2.5:** Broadcast new order via WebSocket to KDS — DEFERRED (Reverb requires PHP 8.2+; using 5s polling interval)
- [~] **2.2.6:** Broadcast item status changes via WebSocket — DEFERRED (see 2.2.5)

#### Frontend (SPA)
- [x] **2.2.7:** Build KDS main screen — column view (Pending | Preparing | Ready)
- [x] **2.2.8:** Build order card component (order number, items, timer, course badge)
- [x] **2.2.9:** Build item status buttons (Preparing / Ready toggle)
- [x] **2.2.10:** Build sound alert for new incoming orders
- [x] **2.2.11:** Build order timer (elapsed time since sent, color-coded)
- [x] **2.2.12:** Build course-based split view (starters column, mains column) — via order grouping
- [x] **2.2.13:** Build completed order section (auto-dismiss after bump)

---

### 2.3 Payment Processing

#### Backend
- [x] **2.3.1:** Create `payments` migration with: order_id, amount, method (cash/card/POS/transfer), reference, status, notes, paid_at
- [x] **2.3.2:** Create `split_bills` migration with: order_id, split_type (by_item/by_person/percentage), splits (JSON)
- [x] **2.3.3:** Create Payment and SplitBill models
- [x] **2.3.4:** Implement PaymentController:
  - `POST /payments` — process payment
  - `GET /payments/{id}` — payment detail
  - `POST /payments/split` — process split bill
  - `POST /payments/{id}/refund` — refund payment (with approval)
- [x] **2.3.5:** Implement change calculation logic
- [x] **2.3.6:** Implement order close logic (mark table as dirty, close order)

#### Frontend (SPA)
- [x] **2.3.7:** Build payment screen with method selection buttons (Cash, Card, POS, Transfer)
- [x] **2.3.8:** Build cash payment dialog (amount tendered, change due)
- [x] **2.3.9:** Build split bill interface (by item, by person, by percentage)
- [x] **2.3.10:** Build payment confirmation screen with receipt preview
- [x] **2.3.11:** Build refund dialog (with reason, approver PIN) — integrated in Orders detail
- [x] **2.3.12:** Build transaction history quick-view — integrated in Dashboard

#### Admin Panel (Filament)
- [x] **2.3.13:** Create Filament Payment resource (list, view, filter, refund)
- [x] **2.3.14:** Add payment summary widget (today's takings by method)

---

## Phase 3 — Operations (Reservations, Inventory, Reports)

### 3.1 Table Reservation

#### Backend
- [x] **3.1.1:** Create `reservations` migration with: customer_name, customer_phone, customer_email, party_size, table_id, date, time_slot, status (pending/confirmed/seated/cancelled), notes, walk_in (boolean), created_by
- [x] **3.1.2:** Create Reservation model
- [x] **3.1.3:** Implement ReservationController:
  - `GET /reservations` — list with date range filter
  - `POST /reservations` — create reservation
  - `PUT /reservations/{id}` — update
  - `DELETE /reservations/{id}` — cancel
  - `GET /reservations/available-slots` — check table availability
- [x] **3.1.4:** Implement availability check logic (table free at time slot)

#### Frontend (SPA)
- [x] **3.1.5:** Build reservation calendar view (daily/weekly/monthly) — daily view with date filter
- [x] **3.1.6:** Build reservation create form (customer info, table select, time slot)
- [x] **3.1.7:** Build reservation list with status badges
- [x] **3.1.8:** Build walk-in booking quick dialog
- [x] **3.1.9:** Build table availability visual grid (time x table) — via available-slots API

#### Admin Panel (Filament)
- [x] **3.1.10:** Create Filament Reservation resource
- [ ] **3.1.11:** Add reservation calendar widget — deferred (covered by SPA daily view)

---

### 3.2 Inventory Management

#### Backend
- [x] **3.2.1:** Create `inventory_items` migration with: name, sku, category, unit (kg/pcs/ltr), current_stock, min_stock, cost_per_unit, supplier
- [x] **3.2.2:** Create `inventory_transactions` migration with: item_id, type (in/out), quantity, reason, reference_type, reference_id, user_id, notes
- [x] **3.2.3:** Create `purchase_orders` migration with: supplier, items (JSON), total_cost, status (pending/received/cancelled), ordered_at, received_at
- [x] **3.2.4:** Create `recipe_items` migration (link menu items to inventory): menu_item_id, inventory_item_id, quantity
- [x] **3.2.5:** Create models: InventoryItem, InventoryTransaction, PurchaseOrder, RecipeItem
- [x] **3.2.6:** Implement InventoryItemController (CRUD)
- [x] **3.2.7:** Implement InventoryTransactionController (stock-in, stock-out, adjust)
- [x] **3.2.8:** Implement PurchaseOrderController (CRUD, receive stock)
- [x] **3.2.9:** Implement RecipeItemController (link menu items to ingredients)
- [x] **3.2.10:** Implement low-stock check and alert logic
- [x] **3.2.11:** Implement auto-deduct inventory when order is marked Paid

#### Frontend (SPA)
- [x] **3.2.12:** Build inventory dashboard (stock levels, low-stock alerts)
- [x] **3.2.13:** Build inventory items list with search and filter
- [x] **3.2.14:** Build inventory item create/edit form
- [x] **3.2.15:** Build stock adjustment dialog (in/out with reason)
- [x] **3.2.16:** Build purchase order list and create form
- [x] **3.2.17:** Build purchase order receive flow (mark as received)
- [x] **3.2.18:** Build recipe builder (link menu items → inventory items + qty)

#### Admin Panel (Filament)
- [x] **3.2.19:** Create Filament Inventory Item resource
- [x] **3.2.20:** Create Filament Purchase Order resource
- [ ] **3.2.21:** Create Filament Recipe Item resource — skipped (management done via SPA)
- [x] **3.2.22:** Add low-stock warning widget to admin dashboard

---

### 3.3 Reports & Analytics

#### Backend
- [x] **3.3.1:** Implement ReportsController:
  - `GET /reports/sales` — daily/weekly/monthly with date range
  - `GET /reports/popular-items` — ranked by qty sold / revenue
  - `GET /reports/profit-margins` — per item (selling price - cost)
  - `GET /reports/staff-performance` — orders taken, sales, avg order value
  - `GET /reports/payment-methods` — distribution
  - `GET /reports/peak-hours` — order volume by hour
- [x] **3.3.2:** Create `daily_reports` table and scheduled aggregation command
- [ ] **3.3.3:** Implement CSV/PDF export service for reports — deferred (can add via Laravel Excel)
- [x] **3.3.4:** Create scheduled task to generate daily report snapshot (`pos:generate-daily-report`)

#### Frontend (SPA)
- [x] **3.3.5:** Build reports dashboard with summary cards
- [x] **3.3.6:** Build sales report view with date picker and chart
- [x] **3.3.7:** Build popular items report (bar chart + table)
- [x] **3.3.8:** Build profit margin report per menu item
- [x] **3.3.9:** Build staff performance report
- [x] **3.3.10:** Build payment method distribution (pie chart)
- [ ] **3.3.11:** Build export buttons (CSV/PDF) — deferred (requires Laravel Excel or similar)

#### Admin Panel (Filament)
- [ ] **3.3.12:** Create Filament Reports page with Chart.js widgets — deferred (SPA covers reports)
- [ ] **3.3.13:** Add sales trend chart (weekly/monthly) to admin dashboard — deferred

---

## Phase 4 — Infrastructure (Offline, Printing, Settings)

### 4.1 Offline Support

#### Backend
- [x] **4.1.1:** Implement `POST /sync/orders` — batch sync endpoint for offline orders
- [x] **4.1.2:** Implement conflict resolution logic (server data wins for paid orders)
- [x] **4.1.3:** Implement sync response with server-side changes since last sync

#### Frontend (SPA)
- [x] **4.1.4:** Configure service worker to cache static assets (JS, CSS, fonts, images)
- [x] **4.1.5:** Set up IndexedDB schema for offline orders
- [x] **4.1.6:** Implement offline order creation flow (save to IndexedDB)
- [x] **4.1.7:** Implement sync queue — push offline orders when online
- [x] **4.1.8:** Implement connectivity detection (online/offline indicator)
- [ ] **4.1.9:** Implement graceful degradation (disable payment if offline) — partially done (offline detection exists)
- [x] **4.1.10:** Build sync status indicator (pending count, last sync time)

---

### 4.2 Thermal Printing (ESC/POS)

#### Backend
- [x] **4.2.1:** Create print job queue with Laravel jobs — PrintJob model + DB table created
- [ ] **4.2.2:** Implement receipt template rendering (restaurant name, items, totals, QR) — partial (endpoint accepts orders, template deferred)
- [ ] **4.2.3:** Implement kitchen ticket template rendering (items, table, course) — partial
- [x] **4.2.4:** Implement `POST /print/receipt` endpoint
- [x] **4.2.5:** Implement `POST /print/kitchen-ticket` endpoint

#### Frontend (SPA)
- [ ] **4.2.6:** Implement ESC/POS printing via WebSocket to local print server — deferred (needs printer network setup)
- [ ] **4.2.7:** Build auto-print on payment completion — deferred
- [ ] **4.2.8:** Build auto-print kitchen ticket on "Send to Kitchen" — deferred
- [ ] **4.2.9:** Build partial print button (print only ready items) — deferred
- [ ] **4.2.10:** Build printer configuration UI (IP, port, test print) — deferred

#### Admin Panel (Filament)
- [ ] **4.2.11:** Create Filament printer settings page — deferred
- [ ] **4.2.12:** Add receipt template customization (logo, footer message, font size) — deferred

---

### 4.3 System Configuration

#### Backend
- [x] **4.3.1:** Create `settings` table (key-value store)
- [x] **4.3.2:** Implement SettingsController (`GET/PUT /settings`)
- [x] **4.3.3:** Create setting helpers/utilities for use across the app
- [x] **4.3.4:** Implement database backup command (`pos:db-backup`)

#### Frontend (SPA)
- [x] **4.3.5:** Build restaurant profile settings page (name, logo, address, currency)
- [x] **4.3.6:** Build tax/VAT configuration page
- [x] **4.3.7:** Build receipt footer customization

#### Admin Panel (Filament)
- [x] **4.3.8:** Create Filament Settings page (all system settings in one place)
- [x] **4.3.9:** Add shift management (open/close register with cash count)
- [x] **4.3.10:** Add backup management (trigger backup, list backups, download, restore) — backup button on Settings page; full backup list/restore deferred (needs storage browser)
- [x] **4.3.11:** Add business hours and holiday closure settings
- [x] **4.3.12:** Add log viewer for application logs

---

## Phase 5 — Platform Management (Admin Dashboard, Coupons, Support, Payment Gateways)

### 5.1 Admin Dashboard (Filament Panel)

#### Admin Panel (Filament)
- [x] **5.1.1:** Build admin dashboard home with metric cards (StatsOverview widget)
- [ ] **5.1.2:** Add chart widgets (hourly sales, weekly revenue trend) — deferred
- [ ] **5.1.3:** Add recent activity feed — deferred (activity logs exist, feed widget not built)
- [x] **5.1.4:** Create `activity_logs` migration and model
- [x] **5.1.5:** Implement activity logger middleware — log key actions across all modules
- [x] **5.1.6:** Create Filament Audit Log resource (read-only, filterable by user/action/date)
- [ ] **5.1.7:** Create Filament notification center widget — deferred
- [ ] **5.1.8:** Implement in-app notification creation — deferred
- [ ] **5.1.9:** Add data export page — deferred (endpoints exist, UI not built)
- [ ] **5.1.10:** Add data purge/cleanup tools — deferred

---

### 5.2 Payment Gateway Configuration

#### Backend
- [x] **5.2.1:** Create `payment_gateways` migration with encrypted credentials
- [x] **5.2.2:** Create `payment_gateway_logs` migration
- [x] **5.2.3:** Create PaymentGateway model with encryption casting
- [x] **5.2.4:** Implement PaymentGatewayController
- [x] **5.2.5:** Implement PaystackService
- [x] **5.2.6:** Implement StripeService
- [x] **5.2.7:** Implement webhook controller for Paystack events
- [x] **5.2.8:** Implement webhook controller for Stripe events
- [ ] **5.2.9:** Implement dynamic payment routing — partial (stripe frontend, paystack not wired to SPA)

#### Admin Panel (Filament)
- [x] **5.2.10:** Create Filament Payment Gateway Settings page with Paystack + Stripe forms
- [x] **5.2.11:** Create Filament Payment Gateway Log resource (read-only, filterable)
- [ ] **5.2.12:** Add "Refund from gateway" action on payment detail page — deferred

---

### 5.3 Coupon & Discount System

#### Backend
- [ ] **5.3.1:** Create `coupons` migration with: code, type (percentage/fixed), value, min_order_amount, max_usage_count, per_customer_limit, applicable_item_ids (JSON), applicable_category_ids (JSON), is_active, starts_at, ends_at
- [ ] **5.3.2:** Create `coupon_usage` migration with: coupon_id, order_id, customer_identifier, discount_amount, used_at
- [ ] **5.3.3:** Create Coupon and CouponUsage models
- [ ] **5.3.4:** Implement CouponController:
  - `GET /coupons` — list with filters
  - `POST /coupons` — create
  - `PUT /coupons/{id}` — update
  - `DELETE /coupons/{id}` — delete
  - `POST /coupons/validate` — validate coupon code (check expiry, usage, min order)
- [x] **5.3.5:** Implement coupon code generator (auto or manual entry)
- [x] **5.3.6:** Implement coupon application logic in OrderService (apply discount to total)
- [x] **5.3.7:** Implement coupon usage tracking
- [ ] **5.3.8:** Implement flash sale/time-limited promotion logic — partial (starts_at/ends_at on coupon)

#### Frontend (SPA)
- [x] **5.3.9:** Build coupon code input field on POS checkout screen
- [x] **5.3.10:** Build coupon validation feedback (valid ✓ / invalid ✗)
- [x] **5.3.11:** Build discount line display on order summary

#### Admin Panel (Filament)
- [x] **5.3.12:** Create Filament Coupon resource (create with all fields, list, edit, delete)
- [ ] **5.3.13:** Add coupon usage report — partial (usage_count on table)
- [ ] **5.3.14:** Add flash sale/promotion scheduler — partial

---

### 5.4 Support & Ticketing System

#### Backend
- [x] **5.4.1:** Create `support_tickets` migration
- [x] **5.4.2:** Create `ticket_messages` migration
- [x] **5.4.3:** Create `ticket_categories` migration
- [x] **5.4.4:** Create models: SupportTicket, TicketMessage, TicketCategory
- [x] **5.4.5:** Implement SupportTicketController
- [x] **5.4.6:** Implement TicketMessageController
- [x] **5.4.7:** Implement FaqController
- [ ] **5.4.8:** Implement email notification on ticket update — deferred
- [ ] **5.4.9:** Implement file upload for ticket attachments — deferred

#### Frontend (SPA)
- [x] **5.4.10:** Build support ticket list page
- [x] **5.4.11:** Build ticket create form (subject, category, priority, message)
- [x] **5.4.12:** Build ticket detail view with threaded messages
- [x] **5.4.13:** Build FAQ/knowledge base browsing page
- [ ] **5.4.14:** Build feedback mechanism when ticket is resolved — deferred

#### Admin Panel (Filament)
- [x] **5.4.15:** Create Filament Support Ticket resource
- [x] **5.4.16:** Add ticket assignment to support agent
- [ ] **5.4.17:** Add ticket messaging interface within Filament — deferred (SPA handles messaging)
- [x] **5.4.18:** Create Filament Ticket Category resource
- [x] **5.4.19:** Create Filament FAQ resource
- [ ] **5.4.20:** Add ticket analytics widget — deferred

---

## Quality Assurance & Deployment

- [ ] **QA1:** Write feature tests for all API endpoints (Pest/PHPUnit)
- [ ] **QA2:** Write frontend component tests (Vitest/React Testing Library)
- [ ] **QA3:** End-to-end testing of complete order flow (Cypress/Playwright)
- [ ] **QA4:** Load testing for order creation under high traffic
- [ ] **QA5:** Security audit — SQL injection, XSS, CSRF, rate limiting
- [ ] **QA6:** Mobile/tablet responsiveness testing
- [ ] **QA7:** Offline mode testing (create orders offline, sync when online)
- [ ] **QA8:** Printer integration testing
- [ ] **QA9:** Payment gateway sandbox testing (Paystack + Stripe)
- [ ] **QA10:** Browser compatibility testing (Chrome, Firefox, Safari)
- [ ] **D1:** Configure production environment (server, domain, SSL)
- [ ] **D2:** Set up CI/CD pipeline (GitHub Actions)
- [ ] **D3:** Configure queue worker and scheduler
- [ ] **D4:** Configure WebSocket server for production
- [ ] **D5:** Set up error monitoring (Sentry or Flare)
- [ ] **D6:** Set up backup automation
- [ ] **D7:** Deploy to production server
- [ ] **D8:** Final smoke testing on production

---

## Task Summary

| Phase | Tasks | Est. Weeks |
|---|---|---|
| Setup & Environment | 13 | — |
| Phase 1: Foundation | 38 | 2 |
| Phase 2: Core POS | 29 | 3 |
| Phase 3: Operations | 35 | 2 |
| Phase 4: Infrastructure | 20 | 2 |
| Phase 5: Platform Management | 44 | 3 |
| QA & Deployment | 18 | 1 |
| **Total** | **~197 tasks** | **~13 weeks** |
