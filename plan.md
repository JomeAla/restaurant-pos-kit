# Restaurant POS Kit — Development Plan

## 1. Overview

Build a complete POS and ordering system for restaurants and food businesses. The system handles the full order lifecycle — from customer arrival through payment — with offline support and thermal printing.

**Tech Stack:** Laravel API (backend), React or Vue.js (frontend), MySQL/PostgreSQL (database), Laravel Filament (admin dashboard)

---

## 2. Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                    Frontend SPA (React/Vue)                    │
│  ┌─────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│  │   POS   │ │ Kitchen  │ │  Admin   │ │Reserva-  │         │
│  │  Module │ │ Display  │ │ Dashboard│ │  tion    │         │
│  └────┬────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘         │
│       │           │             │            │               │
│  ┌────┴───────────┴─────────────┴────────────┴────────────┐ │
│  │              Service Worker + IndexedDB                  │ │
│  │              (Offline Order Cache)                       │ │
│  └──────────────────────────┬───────────────────────────────┘ │
└─────────────────────────────┼─────────────────────────────────┘
                              │ REST API
┌─────────────────────────────┼─────────────────────────────────┐
│                   Laravel API (Backend)                        │
│  ┌─────────┐ ┌──────────┐ ┌──────────┐ ┌───────────────────┐ │
│  │  Auth   │ │  Order   │ │  Menu    │ │  Payment Gateway  │ │
│  │  Service│ │  Service │ │  Service │ │  (Paystack/Stripe)│ │
│  ├─────────┤ ├──────────┤ ├──────────┤ ├───────────────────┤ │
│  │Inventory│ │ Kitchen  │ │  Report  │ │  Coupon/Discount  │ │
│  │ Service │ │ Service  │ │  Service │ │  Service          │ │
│  ├─────────┤ ├──────────┤ ├──────────┤ ├───────────────────┤ │
│  │Reservtn │ │ Support  │ │  Notif   │ │  Offline Sync     │ │
│  │ Service │ │ Ticketing│ │  Service │ │  Service          │ │
│  └─────────┘ └──────────┘ └──────────┘ └───────────────────┘ │
└──────────────────────────┬────────────────────────────────────┘
                           │
┌──────────────────────────┴────────────────────────────────────┐
│                    Filament Admin Panel                        │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐ │
│  │  System  │ │  Users & │ │  Payment │ │  Support Tickets │ │
│  │ Settings │ │  Roles   │ │  Gateway │ │  Management      │ │
│  └──────────┘ └──────────┘ └──────────┘ └──────────────────┘ │
└──────────────────────────┬────────────────────────────────────┘
                           │
                    ┌──────┴──────┐
                    │  Database   │
                    │ MySQL/PgSQL │
                    └─────────────┘
```

---

## 3. Database Schema (Key Tables)

| Table | Purpose |
|---|---|
| `users` | Staff accounts (admin, cashier, waiter, kitchen) |
| `roles_permissions` | Role-based access control |
| `categories` | Menu categories (e.g., Appetizers, Mains, Drinks) |
| `menu_items` | Individual dishes/items |
| `modifiers` | Sides, extras, customizations |
| `menu_item_modifier` | Pivot: which modifiers apply to which items |
| `combos` | Combo/meal deal definitions |
| `combo_items` | Items within a combo |
| `tables` | Restaurant tables (number, capacity, section) |
| `floor_plans` | Visual layout definitions |
| `reservations` | Booking records |
| `orders` | Order headers (dine-in/takeaway/delivery) |
| `order_items` | Line items within an order |
| `order_item_modifiers` | Modifier choices per line item |
| `kitchen_tickets` | Orders relayed to kitchen |
| `payments` | Payment records (cash/card/transfer/POS) |
| `split_bills` | Split payment allocations |
| `inventory_items` | Stockable ingredients |
| `inventory_transactions` | Stock in/out logs |
| `purchase_orders` | Stock procurement |
| `daily_reports` | Aggregated sales/performance snapshots |
| `coupons` | Discount coupons (code, type, value, expiry) |
| `coupon_usage` | Tracks which orders used which coupons |
| `support_tickets` | Support request tickets |
| `ticket_messages` | Messages/replies on support tickets |
| `ticket_categories` | Support ticket categories (billing, tech, etc.) |
| `payment_gateways` | Stored gateway credentials (Paystack, Stripe) |
| `payment_gateway_logs` | Transaction logs from external gateways |
| `activity_logs` | Audit trail for all admin actions |
| `system_backups` | Database backup records |

---

## 4. Feature Modules & Implementation Phases

### Phase 1 — Foundation (Weeks 1-2)

#### 4.1 Authentication & Staff Roles
- Login/Logout (email + PIN for quick POS access)
- Role definitions: Admin, Manager, Waiter, Cashier, Kitchen
- Permission middleware enforcing access per endpoint

#### 4.2 Menu Management
- CRUD for categories and menu items
- Modifier groups (e.g., "Choose side: Fries or Salad")
- Combo/meal deal builder
- Image upload for items
- Pricing, cost tracking, tax configuration
- Availability toggles (available/out of stock)

#### 4.3 Table Management
- Add/edit/remove tables (number, capacity, section)
- Visual floor plan layout (drag-and-drop positioning)
- Table status indicators (free, occupied, reserved, dirty)

### Phase 2 — Core POS (Weeks 3-5)

#### 4.4 Order Management
- Create order → select table (or takeaway/delivery)
- Add items with modifiers to order
- Edit/void items with reason logging
- Hold/release orders
- Order status workflow:
  ```
  Pending → Sent to Kitchen → Preparing → Ready → Served → Paid → Closed
  ```
- Search orders by table, date, status, customer name

#### 4.5 Kitchen Display System (KDS)
- Real-time display of incoming orders
- Course-based splitting (starters before mains)
- Mark items as "Preparing" → "Ready"
- Sound/visual alerts for new orders
- Order priority (VIP, long wait flagged)

#### 4.6 Payment Processing
- Accept: Cash, Card, POS Terminal, Bank Transfer
- Split bill by item, by person, or by percentage
- Calculate change due
- Generate printable receipt
- Record payment method for reconciliation
- Refund/void transactions (with approval)

### Phase 3 — Operations (Weeks 6-7)

#### 4.7 Table Reservation
- Booking calendar view (daily/weekly/monthly)
- Customer name, phone, party size, time slot
- Table assignment with availability check
- Walk-in vs pre-booked
- Reservation status: Pending → Confirmed → Seated → Cancelled

#### 4.8 Inventory Management
- Stock items linked to menu items (recipes)
- Stock-in (purchase orders, supplier records)
- Stock-out (waste, usage, expired)
- Low-stock alerts (configurable threshold)
- Cost calculation per dish based on ingredient usage

#### 4.9 Reports & Analytics
- Daily/Weekly/Monthly sales summary
- Popular items ranking (by quantity & revenue)
- Profit margin breakdown per item
- Staff performance (orders taken, sales volume)
- Payment method distribution
- Peak hours analysis
- Export to CSV/PDF

### Phase 4 — Infrastructure (Weeks 8-9)

#### 4.10 Offline Support
- Service worker caches static assets
- IndexedDB stores orders created offline
- Queue syncs to server when connection restores
- Conflict resolution strategy (server wins for paid orders)

#### 4.11 Thermal Printing (ESC/POS)
- Print receipt on payment completion
- Print kitchen ticket on order send
- Configurable printer IP/port
- Receipt template customization (logo, footer message)
- Partial printing (print only ready items)

#### 4.12 System Configuration
- Restaurant profile (name, logo, address, currency)
- Tax/VAT rates
- Receipt footer text
- Shift management (open/close register)
- Backup & restore

### Phase 5 — Platform Management (Weeks 10-12)

#### 4.13 Admin Dashboard (Filament Panel)
The admin dashboard is a dedicated backend interface (built with Laravel Filament) for super admins to manage the entire platform. This is separate from the POS SPA frontend.

- **Dashboard Home:** Key metrics cards (total orders today, revenue, active tables, low-stock count), chart widgets (hourly sales, weekly trends), recent activity feed
- **User Management:** CRUD for all staff accounts, role assignment, permission toggles, account enable/disable, login history
- **Audit Trail:** Full activity log — who did what and when (created order, voided item, changed price, adjusted stock)
- **System Settings:** Restaurant profile, tax rates, currency, receipt templates, printer configuration, business hours, holiday closures
- **Data Management:** Database backup/restore, data export (CSV, Excel), purge old records, log rotation
- **Notifications:** In-app notification center, low-stock alerts, new support ticket alerts, daily summary digest

#### 4.14 Payment Gateway Configuration
- Admin interface to input and save Paystack API credentials (public key, secret key, webhook URL)
- Admin interface to input and save Stripe API credentials (publishable key, secret key, webhook secret)
- Sandbox/Live mode toggle for each gateway
- Gateway status indicator (connected/disconnected)
- Webhook endpoint registration helper
- Transaction logs per gateway for reconciliation
- Refund initiation through gateway API directly from admin panel

#### 4.15 Coupon & Discount System
- Create coupons with: code (auto or manual), discount type (percentage or fixed amount), value, minimum order amount, maximum usage count, per-customer usage limit, applicable menu items/categories, start & end dates
- Coupon validation engine (check expiry, usage limits, min order, item restrictions)
- Auto-apply coupons (e.g., "10% off orders above ₦5,000")
- Coupon usage reports — how many times used, total discount given, revenue impact
- Flash sales / time-limited promotions
- Loyalty-based discounts (e.g., "10th order free")

#### 4.16 Support & Ticketing System
- Ticket creation by restaurant staff from within the app
- Ticket categories: Billing/Payment, Technical Issue, Feature Request, Account Management
- Priority levels: Low, Medium, High, Urgent
- Internal messaging thread on each ticket (staff ↔ support agent)
- Attachment upload (screenshots, logs)
- Ticket status workflow:
  ```
  Open → Assigned → In Progress → Resolved → Closed
  ```
- Admin panel for support agents to view/manage all tickets
- Email notifications on ticket updates
- FAQ/knowledge base section for common issues
- Ticket analytics (average resolution time, volume trends)

---

## 5. Frontend Route Structure

| Route | View | Role |
|---|---|---|
| `/login` | Authentication | All |
| `/pos` | POS terminal (order creation) | Waiter, Cashier |
| `/pos/tables` | Floor plan table selector | Waiter, Cashier |
| `/orders` | Order list & management | Waiter, Cashier, Manager |
| `/orders/:id` | Order detail | Waiter, Cashier |
| `/kitchen` | Kitchen display system | Kitchen |
| `/menu` | Menu management (CRUD) | Manager, Admin |
| `/menu/categories` | Category management | Manager, Admin |
| `/menu/modifiers` | Modifier management | Manager, Admin |
| `/menu/combos` | Combo deal builder | Manager, Admin |
| `/tables` | Table & floor plan management | Manager, Admin |
| `/reservations` | Reservation calendar | All |
| `/inventory` | Inventory dashboard | Manager, Admin |
| `/inventory/items` | Stock items | Manager, Admin |
| `/inventory/purchases` | Purchase orders | Manager, Admin |
| `/reports` | Reports dashboard | Manager, Admin |
| `/reports/sales` | Sales report detail | Manager, Admin |
| `/reports/items` | Popular items | Manager, Admin |
| `/reports/staff` | Staff performance | Manager, Admin |
| `/admin` | Admin dashboard home (metrics) | Admin |
| `/admin/users` | Staff management | Admin |
| `/admin/roles` | Role & permission management | Admin |
| `/admin/audit-log` | Activity trail | Admin |
| `/admin/settings` | System configuration | Admin |
| `/admin/settings/payment-gateways` | Paystack/Stripe config | Admin |
| `/admin/settings/printers` | Thermal printer setup | Admin |
| `/admin/coupons` | Coupon management | Admin |
| `/admin/support` | Support tickets list | Admin |
| `/admin/support/:id` | Ticket detail & replies | Admin |
| `/admin/support/faq` | Knowledge base management | Admin |
| `/admin/backups` | Backup & restore | Admin |
| `/settings` | Restaurant & system config (SPA) | Manager, Admin |
| `/users` | Staff management | Admin |

---

## 6. API Endpoint Structure (Laravel)

All endpoints prefixed with `/api/v1/`.

| Group | Endpoints |
|---|---|
| **Auth** | `POST /auth/login`, `POST /auth/logout`, `GET /auth/me`, `POST /auth/pin` |
| **Users** | `GET/POST /users`, `GET/PUT/DELETE /users/{id}`, `GET /users/roles` |
| **Categories** | `GET/POST /categories`, `GET/PUT/DELETE /categories/{id}` |
| **Menu Items** | `GET/POST /menu-items`, `GET/PUT/DELETE /menu-items/{id}`, `POST /menu-items/{id}/image` |
| **Modifiers** | `GET/POST /modifiers`, `GET/PUT/DELETE /modifiers/{id}`, `GET /modifiers/groups` |
| **Combos** | `GET/POST /combos`, `GET/PUT/DELETE /combos/{id}` |
| **Tables** | `GET/POST /tables`, `GET/PUT/DELETE /tables/{id}`, `PUT /tables/{id}/status` |
| **Floor Plan** | `GET/PUT /floor-plan` |
| **Orders** | `GET/POST /orders`, `GET/PUT/DELETE /orders/{id}`, `POST /orders/{id}/items`, `PUT /orders/{id}/items/{itemId}`, `DELETE /orders/{id}/items/{itemId}`, `POST /orders/{id}/send-kitchen`, `POST /orders/{id}/pay` |
| **Kitchen** | `GET /kitchen/orders`, `PUT /kitchen/orders/{id}/items/{itemId}/status` |
| **Payments** | `POST /payments`, `GET /payments/{id}`, `POST /payments/split` |
| **Reservations** | `GET/POST /reservations`, `GET/PUT/DELETE /reservations/{id}`, `GET /reservations/available-slots` |
| **Inventory** | `GET/POST /inventory/items`, `GET/PUT/DELETE /inventory/items/{id}`, `GET/POST /inventory/transactions`, `GET/POST /purchase-orders` |
| **Reports** | `GET /reports/sales`, `GET /reports/popular-items`, `GET /reports/profit-margins`, `GET /reports/staff-performance`, `GET /reports/payment-methods` |
| **Coupons** | `GET/POST /coupons`, `GET/PUT/DELETE /coupons/{id}`, `POST /coupons/validate` |
| **Support Tickets** | `GET/POST /support/tickets`, `GET/PUT/DELETE /support/tickets/{id}`, `POST /support/tickets/{id}/messages`, `GET /support/tickets/{id}/messages`, `PUT /support/tickets/{id}/status` |
| **Support FAQ** | `GET/POST /support/faq`, `GET/PUT/DELETE /support/faq/{id}` |
| **Payment Gateways** | `GET/PUT /payment-gateways/{gateway}`, `POST /payment-gateways/{gateway}/test`, `GET /payment-gateways/{gateway}/logs` |
| **Settings** | `GET/PUT /settings`, `POST /settings/backup` |
| **Audit Log** | `GET /audit-log` |
| **Printing** | `POST /print/receipt`, `POST /print/kitchen-ticket` |
| **Sync** | `POST /sync/orders` (offline sync endpoint) |

---

## 7. Current Status

✅ Requirements gathered from spec document  
⬜ Phase 1: Foundation (Auth, Menu, Tables)  
⬜ Phase 2: Core POS (Orders, KDS, Payments)  
⬜ Phase 3: Operations (Reservations, Inventory, Reports)  
⬜ Phase 4: Infrastructure (Offline, Printing, Settings)  
⬜ Phase 5: Platform Management (Admin Dashboard, Coupons, Support, Payment Gateways)

---

## 8. Key Design Decisions

1. **Frontend choice:** React with Tailwind CSS (or Vue 3) — to be confirmed. Optimized for tablet touch interaction (large tap targets, swipe gestures).

2. **Offline strategy:** Service worker + IndexedDB. Orders created offline are queued locally and synced via a dedicated `/sync/orders` endpoint when connectivity returns.

3. **Real-time updates:** Laravel Reverb (WebSockets) for kitchen display order updates. Falls back to polling when WebSocket is unavailable.

4. **ESC/POS printing:** Print via WebSocket or direct TCP connection from the browser to a local thermal printer. A utility library handles receipt formatting.

5. **Responsive design:** Primary target is tablet (1024px+) for the POS terminal, with desktop adaptation for admin panels.

6. **Dual-panel architecture:** The POS terminal, kitchen display, and reservation UI are in a single-page app (React/Vue). The backend admin dashboard (Filament) is a separate Laravel package — super admins access it via `/admin` for full platform management.

7. **Payment gateways:** Paystack (primary for Nigerian market) and Stripe (international). Credentials stored encrypted in the database. No hardcoding — admins configure via the Filament panel. Transactions route through Laravel which forwards to the gateway API, keeping secrets server-side.

8. **Coupon system:** Server-side validation ensures no expired/overused coupons are accepted. Coupons sync to the POS frontend for auto-suggestion at checkout.

---

## 9. Next Steps

1. Confirm frontend framework (React vs Vue)
2. Set up Laravel project with database schema migrations
3. Set up Filament admin panel package
4. Implement authentication module (both SPA + Filament guard)
5. Build menu CRUD (backend + frontend)
6. Implement table management with floor plan
