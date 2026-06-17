# Bambe E-Commerce Platform — Project Report

**Student Project | Session Day & Evening | June 2026**  
**Instructor:** Eric Maniraguha  
**Business:** Bambe Fashion Store — Online Clothes & Shoes Shop (Rwanda)
**REG NUMBER** 22654/2023
**NAME** KWIZERA Angelus

---

## 1. Introduction

This report documents the design, development, deployment, and innovation features of **Bambe**, a full-stack e-commerce web application built for a local fashion business in Rwanda. Bambe enables customers to browse products online, manage a shopping cart, complete checkout, and receive order confirmations. The platform also includes an admin dashboard for product and order management.

The project was developed as the final assessment for the Web Development course, covering UI design, database integration, Docker containerization, CI/CD automation, live deployment, and two innovation features: an **AI-powered shopping chatbot** and **PayPal payment gateway integration**.

---

## 2. Problem Statement

Many small businesses in Rwanda rely on physical stores and informal sales channels (phone calls, social media DMs). This limits their reach, makes inventory tracking difficult, and provides no structured way to manage orders or customer data.

Bambe Fashion Store needed a modern online platform that would:

- Display products with categories and detailed information
- Allow customers to shop from mobile and desktop devices
- Process orders with delivery details stored in a database
- Give the business owner tools to manage products and track sales
- Support secure online payments for customers who prefer digital checkout
- Offer intelligent customer support without hiring a full-time agent

---

## 3. Objectives

| # | Objective | Status |
|---|-----------|--------|
| 1 | Build a responsive, professional customer-facing storefront | ✅ Completed |
| 2 | Implement product listing, details, and category filtering | ✅ Completed |
| 3 | Create shopping cart with add/remove/update quantity | ✅ Completed |
| 4 | Build checkout flow with order confirmation | ✅ Completed |
| 5 | Integrate MySQL database for products, customers, and orders | ✅ Completed |
| 6 | Develop admin panel for product and order management | ✅ Completed |
| 7 | Containerize with Docker and docker-compose | ✅ Completed |
| 8 | Set up CI/CD pipeline with GitHub Actions | ✅ Completed |
| 9 | Deploy application online for evaluation | ✅ Completed |
| 10 | Add AI chatbot for product recommendations and support | ✅ Completed |
| 11 | Integrate PayPal payment gateway | ✅ Completed |

---

## 4. System Features

### 4.1 Customer Storefront

- **Homepage** — Hero section, category cards, featured products, and service highlights
- **Product Listing** — Filter by category (Clothes, Shoes), search by name/description
- **Product Details** — Images, price, description, stock status, quantity selector
- **Shopping Cart** — Add/remove items, update quantities, automatic total calculation
- **Checkout** — Customer delivery form, order summary, payment method selection
- **Order Confirmation** — Order number, items, delivery info, payment status

### 4.2 Admin Panel (`/admin`)

- Secure login with password hashing (bcrypt)
- Dashboard with revenue, orders, sales charts, and low-stock alerts
- Product CRUD (create, read, update, delete) with image upload
- Order management with status updates (pending → delivered)
- Payment method and PayPal transaction ID visibility

### 4.3 Innovation Features

#### AI Shopping Chatbot
- Floating chat widget on all customer pages
- Powered by **OpenAI GPT** when API key is configured
- Intelligent **fallback mode** queries the product database for recommendations when no API key is set
- Answers questions about delivery, returns, payments, and product suggestions
- Conversation history stored in browser session

#### PayPal Payment Gateway
- PayPal JavaScript SDK on checkout page
- Server-side order creation and payment capture via PayPal REST API v2
- Automatic RWF → USD conversion (PayPal does not support Rwandan Francs)
- Orders stored with `payment_method`, `payment_status`, and transaction IDs
- Cash on Delivery remains available as an alternative

---

## 5. Technologies Used

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, CSS3, JavaScript (vanilla) |
| Backend | PHP 8.2 |
| Database | MySQL 8.0 |
| Web Server | Apache (in Docker) |
| Containerization | Docker, Docker Compose |
| CI/CD | GitHub Actions |
| AI | OpenAI Chat Completions API |
| Payments | PayPal Checkout SDK + REST API |
| Version Control | Git, GitHub |

---

## 6. System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        CUSTOMER BROWSER                      │
│  (HTML/CSS/JS — Homepage, Shop, Cart, Checkout, Chatbot)    │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP
┌──────────────────────────▼──────────────────────────────────┐
│                    PHP APPLICATION (Apache)                    │
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────────────┐ │
│  │ Store Pages │  │ Admin Panel  │  │ API Endpoints       │ │
│  │ index.php   │  │ /admin/*     │  │ api/chatbot.php     │ │
│  │ products.php│  │              │  │ api/paypal-*.php    │ │
│  └─────────────┘  └──────────────┘  └─────────────────────┘ │
└──────────────┬──────────────────────────────┬───────────────┘
               │ PDO                           │ cURL
┌──────────────▼──────────────┐   ┌────────────▼──────────────┐
│         MySQL 8.0           │   │   External Services        │
│  categories, products,      │   │  • OpenAI API (chatbot)    │
│  customers, orders, admins  │   │  • PayPal API (payments)   │
└─────────────────────────────┘   └───────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    DOCKER COMPOSE STACK                      │
│   web (PHP+Apache)  ←→  db (MySQL)  —  port 8080:80         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│              GITHUB ACTIONS CI/CD PIPELINE                   │
│  Push → Build Docker → Start → Health Check → Test Pages    │
└─────────────────────────────────────────────────────────────┘
```

### Database Schema (Key Tables)

- **categories** — Product categories (Clothes, Shoes)
- **products** — Name, price, image, stock, featured flag
- **customers** — Full name, email, phone, address, city
- **orders** — Order number, total, status, payment method/status, PayPal IDs
- **order_items** — Line items per order
- **admins** — Admin authentication

---

## 7. Screenshots

> *Insert screenshots when submitting your report (PDF or Word).*

Recommended screenshots:

1. Homepage with hero and featured products
2. Product listing with category filter
3. Product detail page
4. Shopping cart with items and totals
5. Checkout page with PayPal and Cash on Delivery options
6. Order confirmation page
7. AI chatbot widget with sample conversation
8. Admin dashboard with analytics
9. Admin order view showing PayPal payment
10. GitHub Actions CI/CD pipeline (green checkmarks)
11. Docker containers running (`docker compose ps`)

---

## 8. GitHub Repository Link

**Repository:** `[Add your GitHub URL here]`

Example: `https://github.com/yourusername/bambe-ecommerce`

The repository includes meaningful commit history covering UI development, database setup, admin panel, Docker configuration, CI/CD workflow, AI chatbot, and PayPal integration.

---

## 9. Deployment Link

**Live URL:** `[Add your deployed URL here]`

Example: `https://bambe-store.railway.app` or `http://your-server-ip:8080`

### Deployment Steps Used

1. Push source code to GitHub
2. Configure environment variables (database, PayPal, OpenAI)
3. Deploy using Docker Compose on hostinger platform 
4. Verify health endpoint: `/health.php`
5. Test full purchase flow including PayPal sandbox payment

---

## 10. CI/CD Description

The project uses **GitHub Actions** (`.github/workflows/ci-cd.yml`):

| Stage | Action |
|-------|--------|
| **Trigger** | Every push/PR to `main` or `master` |
| **Build** | `docker compose build` |
| **Start** | `docker compose up -d` |
| **Wait** | Poll MySQL until healthy |
| **Test** | Hit `/health.php`, homepage, products, cart, chatbot API |
| **Deploy** | Notification step on main branch (connect to Railway/Render/VPS) |

This ensures broken code is caught before deployment and demonstrates automated quality checks.

---

## 11. Docker Configuration

**Dockerfile** — PHP 8.2 + Apache with PDO MySQL and cURL extensions  
**docker-compose.yml** — Two services:
- `web` — Application container (port 8080)
- `db` — MySQL 8.0 with auto-import of `database/schema.sql`

```bash
docker compose up --build -d
# Access: http://localhost:8080
```

---

## 12. Challenges Encountered

1. **PayPal currency** — PayPal does not support RWF. Solved by converting totals to USD using a configurable exchange rate while displaying RWF prices to customers.

2. **AI without API costs** — Built a smart fallback chatbot that queries the product database so the feature works during demos even without an OpenAI key.

3. **Session-based cart** — Cart stored in PHP sessions works well for a single-server setup; scaling would require Redis or database-backed carts.

4. **Docker + MySQL init** — Ensured schema SQL runs on first container start via `docker-entrypoint-initdb.d` volume mount.

5. **Git ignore fix** — Dockerfile and `.github` were initially gitignored; removed so mandatory deliverables are included in the repository.

---

## 13. Future Work

- Mobile Money integration (MTN MoMo) for local Rwandan payments
- Customer accounts with order history and wishlists
- Email/SMS order notifications
- Multi-vendor marketplace for handicraft sellers
- Enhanced AI: personalized recommendations based on browsing history
- Redis session store for horizontal scaling
- Product reviews and ratings

---

## 14. Conclusion

The Bambe E-Commerce Platform successfully meets all project requirements: responsive UI, full shopping flow, MySQL database, admin management, GitHub hosting, Docker containerization, CI/CD automation, and live deployment. The addition of an AI shopping chatbot and PayPal payment gateway demonstrates innovation beyond the core requirements and prepares the business for real-world online sales in Rwanda.

The project provided practical experience in full-stack web development, API integration, containerization, and automated deployment — skills directly applicable to building and maintaining modern e-commerce systems.

---

## Appendix: Environment Variables

Copy `.env.example` to `.env` and configure:

| Variable | Purpose |
|----------|---------|
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Database connection |
| `OPENAI_API_KEY` | AI chatbot (optional) |
| `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET` | PayPal payments |
| `PAYPAL_MODE` | `sandbox` or `live` |
| `RWF_TO_USD_RATE` | Currency conversion for PayPal |

## Appendix: Admin Credentials (Development)

| Field | Value |
|-------|-------|
| URL | `/admin/login.php` |
| Username | `admin` |
| Password | `admin123` |

*Change before production deployment.*

---

**End of Report**
