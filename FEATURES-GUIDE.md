# 🧘 Wellness Hub — Feature Guide (June 19, 2026 · updated September 10, 2026)

> 🌐 **Live site:** [thewellnesshub-eg.com](https://thewellnesshub-eg.com/)

---

## 1. 💱 Currency Per Therapist

**What it does:**  
Each therapist can have their own currency. The system automatically shows prices and charges clients in the therapist's chosen currency. There are three settings:

- **Location Based** (default) — the visitor's country decides: **Egypt → EGP**, everywhere else → **USD**.
- **EGP** — always Egyptian Pounds, regardless of the visitor's location.
- **USD** — always US Dollars, regardless of the visitor's location.

> 💵 **USD therapists: orders are recorded in USD, but charged in EGP.** When a USD order is paid through **Paymob** (Egypt), the order is kept in **USD** exactly as the client saw it, while Paymob is charged the **EGP equivalent** at the live USD→EGP rate (the converted amount + rate appear on the order line item). A USD order paid by a client **outside Egypt** goes to **Stripe** and is charged in **USD**. In short: EGP → Paymob; USD + Egypt → Paymob (converted to EGP); USD + abroad → Stripe.

### 🔧 How to configure (Admin only)

1. Go to **Users → All Users** in the left sidebar
2. Click **Edit** on any therapist (shop_staff) account
3. Scroll down to the **"Appointment Timezone"** section
4. Below the Timezone dropdown, you'll see the **"Your currency"** dropdown — choose **(Location Based)**, **EGP**, or **USD**
5. Click **Update Profile** at the bottom

> 💡 **Tip:** The currency field only appears for therapist (shop_staff) accounts. It's right under the timezone field — same section, next row.

### 👀 How to use / see it

| Who | What to do |
|-----|-------------|
| **Admin** | Set it once per therapist as described above. The **Users** list also shows a **Currency** column for therapist accounts (the default displays as **"(Location Based)"**). |
| **Staff/Therapist** | Nothing to do — ask your admin if your currency is wrong. |
| **Client** | Prices automatically show in the therapist's currency. If the therapist is USD and the client pays by Paymob, the charge is converted to EGP at checkout. No action needed. |

---

## 2. 📋 Session Types & Durations

**What it does:**  
Each therapy product can offer multiple session types (e.g. "Individual — 60 min", "Couples — 90 min"), each with its own duration and price in both EGP and USD. Clients pick from a dropdown on the booking page.

### 🔧 How to configure (Admin only)

1. Go to **Products → All Products**
2. Click **Edit** on any Appointment product
3. In the **Product Data** panel, stay on the **General** tab
4. Scroll down to the **"Wellness Product"** section
5. Check the box: **☑ Enable Duration Options**
6. A table appears with columns: **Label**, **Duration (min)**, **EGP Price**, **USD Price**
7. Click **+ Add Duration Option** to add a row, then fill in:
   - **Label** — what clients see in the dropdown, e.g. "Individual Session"
   - **Duration (min)** — how long the session is, e.g. `60`
   - **EGP Price** — the price in Egyptian Pounds
   - **USD Price** — the price in US Dollars
8. Click **+ Add Duration Option** again for more types (e.g. "Couples Session — 90 min")
9. Click **Update** to save the product

> 💡 **Smart fallbacks for blank fields:**
> - If you leave **Duration** blank, the product's original duration setting is used instead.
> - If you leave **EGP Price** or **USD Price** blank, the product's original price is used for that currency.
>
> This means you can set only the fields that differ from the defaults. For example: set just a longer duration without changing the price, or set just a different price for USD clients while keeping the original duration.

### 👀 How to use / see it

| Who | What to do |
|-----|-------------|
| **Admin** | Configure as above. The session type chosen by the client appears on the order detail page. |
| **Staff/Therapist** | You'll see the session type (e.g. "Couples — 90 min") in your appointment confirmation emails and on the admin order screen. |
| **Client** | On the booking page, pick from the new **Session Type** dropdown. The price updates instantly. The calendar and Add to Cart button appear only after choosing. |

**Where the session type appears:**
- 📄 **Booking page** — Session Type dropdown (before the calendar)
- 🛒 **Cart & Checkout** — shown next to the product name
- 🧾 **Order confirmation** — visible to client and admin
- ✉️ **7 appointment emails** — included whenever the client selected a session type (therapist: new appointment, cancelled; client: confirmed, payment, follow-up, reminder, rescheduled)

---

## 3. 📝 First-Time Client Intake Form

**What it does:**  
New clients complete a health & contact questionnaire. Since **September 2026 the intake form is collected on the thank-you page after booking**, not during checkout — so checkout stays short (2 steps) and clients fill the form in once the order is placed. The system checks the billing email: if that email has **never placed an order before**, the form appears; if the email already has any previous order — even one — the form is skipped.

- 🆕 **Brand-new clients** → 2-step checkout (Contact & Billing → Payment), then the intake form on the thank-you page
- 🔁 **Returning clients** (same email as a past order) → no intake form, no reminder
- ✏️ **Same person, different email** → treated as a new client (the system matches by email, not name)

The thank-you form is prefilled from billing (name + mobile) and still requires **client name** and **mobile** (editable). A one-shot reminder email is sent ~24h after booking if the client has not completed it.

**Why it works this way:**  
Therapists only need intake details once per client. After the first booking, the information is saved and accessible anytime under **Appointments → Intake Forms**. Asking again would be redundant for returning clients, and moving the form off checkout shortens the booking flow.

### 👀 How to use / see it

| Who | What to do |
|-----|-------------|
| **Admin** | Go to **Appointments → Intake Forms** to browse all submitted forms. Each is tied to the client's email address — one entry per unique email. Click any entry to see the full intake record (the thank-you form collects 27 fields). On any order page, an intake summary card appears below the billing address. |
| **Staff/Therapist** | Your **New Appointment** email includes the intake summary for first-time clients, or a **"Client Intake Form: Pending"** note when the client has not yet filled it in. You can also browse all forms under **Appointments → Intake Forms**. |
| **Client** | Checkout is 2 steps (Contact & Billing → Payment). After ordering, first-time clients complete the intake form on the thank-you page (prefilled); a reminder is emailed ~24h later if it is still missing. No login needed — it works by email. |

**Checkout — 2 steps (all clients):**

| Step | Section |
|:----:|---------|
| 1 | Contact & Billing |
| 2 | Payment |

**Intake form sections (thank-you page, new clients only):**

| # | Section |
|:-:|---------|
| 1 | Personal Details (name, birth date, address, mobile) |
| 2 | Phone & Preferences |
| 3 | Emergency Contact (name, relation, phone) |
| 4 | Health Background (medications, mental health history) |
| 5 | How You Heard About Us (friend, doctor, social media, etc.) |

**Where to see it:**
- 🛒 **Checkout page** — 2-step form at [thewellnesshub-eg.com/checkout](https://thewellnesshub-eg.com/checkout/)
- ✅ **Thank-you (order-received) page** — the intake form, right after ordering
- 📋 **Appointments → Intake Forms** — full list and detailed view (admin + staff)
- 🧾 **Order detail page** — intake summary card below billing address
- ✉️ **New Appointment email** — intake summary, or "Client Intake Form: Pending" when missing
- ⏰ **Intake reminder email** — sent ~24h after booking if the form is still incomplete

---

## 4. 🖥️ Appointment Type — Online

**What it does:**  
Every appointment is presented as an **online** session. A fixed **"Appointment type: Online"** line is shown in the cart and on every appointment email, so clients and therapists can see at a glance that the session is online.

### 👀 How to use / see it

| Who | What to do |
|-----|-------------|
| **Admin** | Nothing to configure — the label is automatic for all appointment products. |
| **Staff/Therapist** | See **Appointment type: Online** in the appointment details table of every appointment email. |
| **Client** | See **Appointment type: Online** in the cart, at checkout, on the thank-you page, and in every appointment email. |

**Where it appears:**
- 🛒 **Cart & Checkout** — added to the item details for appointment products
- 🧾 **Order item meta** — thank-you page, admin order screen, and standard WooCommerce order emails
- ✉️ **All appointment emails** — a dedicated "Appointment type" row

> ℹ️ **Note:** The value is currently a fixed label (always "Online"). If in-person sessions are introduced later, it can be wired to a per-product setting.

---

## 📊 At a Glance

| # | Feature | Configured Where | Who Benefits |
|---|---------|------------------|--------------|
| 1 | Currency per therapist (Location Based / EGP / USD) | Users → Edit → Appointment Timezone section (under timezone) | Admin, international clients |
| 2 | Session types & durations | Products → Edit → General tab → Wellness Product section | Clients (choice), Therapists (clarity) |
| 3 | Client intake form (thank-you page) | Automatic — no setup needed (appears for first-time clients by email) | Therapists (info in email), Admin (records) |
| 4 | Appointment type — Online | Automatic — no setup needed (fixed label for all appointments) | Clients & Therapists (clarity) |

All four features are live at **[thewellnesshub-eg.com](https://thewellnesshub-eg.com/)**.
