# QA Review Guide — Customer Booking Experience (Frontend)

> **Date:** 2026-09-01
> **Audience:** Non-technical reviewer — you test the website the way a customer would
> (browse, choose a session, pick a time, book, repeat a booking, and check the emails you
> receive). You do **not** need to look at code, the database, or technical admin settings.
> **Where to test:** The local/dev copy (http://welness.heba). The fixes are **not** on the
> live site yet.

---

## Before you start

Ask the tech person to:

- [ ] Set up the **three test therapists** (booking pages) and confirm they are reachable.
      They are hidden from the shop, so you reach them with the **links below**.
- [ ] Make sure **emails** are working so you can receive the test emails.
- [ ] The **test therapist logins** are available from the **WooCommerce → Test Therapist
      Fixtures** page (the admin has already run the fixtures). Use those to log in as a
      therapist in the therapist-view checks below.
- [ ] Confirm you can complete a **test payment** without a real charge.

**The three test booking pages** (open these links):

| Booking page | Currency shown | Good for testing |
|--------------|:--------------:|------------------|
| Test Therapist (EGP) | Egyptian pounds (EGP) | Session lengths, booking, repeat |
| Test Therapist (USD) | US dollars (USD) | Dollar prices, repeat in dollars |
| Test Therapist (Location) | EGP or USD based on visitor | Prices for visitors in/out of Egypt |

Details on the test pages:

- Each page offers **three session types**: **Individual 30 min**, **Couples 60 min**,
  **Family 90 min**.
- The **starting price** is EGP 1,500 / USD 60, but when you pick a session type the price
  changes to that type's price (e.g. Couples 60 = **EGP 900**, or **USD 27** in dollars).
- The therapists can only be booked on **weekdays, 09:00–17:00**.

---

## 1. Choosing a session type and picking a time

**What to check:** The time slots shown must match the session length you choose, and the
price must match the session type you pick.

- [ ] Open **Test Therapist (EGP)**.
- [ ] Pick a session type from the dropdown — you should see the three options
      (Individual 30 min / Couples 60 min / Family 90 min).
- [ ] Choose **Couples 60 min** → the available times should be **60 minutes** apart.
- [ ] Choose **Individual 30 min** → the available times should be **30 minutes** apart.
- [ ] Choose **Family 90 min** → the available times should be **90 minutes** apart.
- [ ] The **price** shown should be the price of the chosen session type (e.g. Couples 60
      = **EGP 900** on the EGP page), **not** the starting price (EGP 1,500).
- [ ] Pick a time, add it to the cart, and confirm the time and price look right.

> Note: The main thing to confirm is that a **60-minute session shows 60-minute slots**.
> Before this fix, a 60-minute session could show a shorter slot.

---

## 2. Prices and currency

**What to check:** The currency (pounds or dollars) stays the same from the moment you pick
a session through checkout and payment, and the amount is what you expected.

### EGP therapist
- [ ] Open **Test Therapist (EGP)**. Prices show **Egyptian pounds (EGP)**.
- [ ] Complete a booking through to payment. The amount is still in **EGP** and matches what
      you saw when you picked the session.

### USD therapist
- [ ] Open **Test Therapist (USD)**. Prices show **US dollars (USD)**.
- [ ] Complete a booking through to payment. The amount is still in **USD** and matches what
      you saw.

### Location therapist (optional)
- [ ] Open **Test Therapist (Location)**.
- [ ] As a visitor from Egypt, prices show **EGP**; as a visitor elsewhere, prices show
      **USD**. (If you can only test from one place, ask the tech person to confirm the
      other case.)

### General
- [ ] The price you see when you **choose a session** is the same as the price at the
      **checkout page** and on the **payment page**.

---

## 3. Repeat (recurring) bookings

**What to check:** A customer can book a series of sessions and see them listed, and the
follow-up sessions show up in their account.

- [ ] Open **Test Therapist (EGP)** and tick **"Do you want to repeat this appointment?"**.
- [ ] Choose how often (**Weekly** / **Every 2 weeks** / **Monthly**) and **how many**
      sessions (e.g. 2).
- [ ] A list of the **upcoming sessions** should appear before you pay.
- [ ] If one of your preferred times is already taken, you should see a note saying a nearby
      time was reserved instead.
- [ ] Continue and pay for the first session.
- [ ] Open **My Appointments** (in your account) and confirm the follow-up sessions are
      listed.
- [ ] A **single** booking (you did **not** tick repeat) should show no extra sessions.

---

## 4. Emails you receive

**What to check:** The booking confirmation emails show the right session, time, and the
correct currency, and repeating bookings are clearly labelled.

- [ ] After you book and pay, you get a **confirmation email** with the session details and
      the correct time.
- [ ] For a **repeat** booking, the confirmation email says it's a repeating appointment and
      lists the upcoming sessions.
- [ ] Before each repeat session, you get a **"pay for your session"** email with the amount
      due and a **link/button to pay**.
- [ ] When you pay a repeat session, you get another **confirmation email**.
- [ ] All emails show the time in **your** local time zone (not the therapist's).
- [ ] The currency in the email matches the currency you saw when booking.

- [ ] The **therapist** also gets an email for each new booking (and for each session in a
      repeat booking), showing the client, session type, time, and the full series.
- [ ] If a repeat session is cancelled (not paid), the therapist gets a **cancellation
      notice** letting them know it was an unpaid recurring session.

> **How to read the emails:** You don't need a real inbox. Open the **email log in Fluent
> SMTP** (admin → **Fluent SMTP → Email Log**) and open the relevant entry to read the full
> email. This is the easiest way to check the **customer's** and the **therapist's** emails,
> especially if they aren't arriving in a real inbox.

---

## 5. The therapist's view

**What to check:** From the therapist's side, the bookings look right — including repeat
series — and the therapist is only shown their own work.

Use the **test therapist login** available on the **WooCommerce → Test Therapist Fixtures**
page.

- [ ] Log in as a therapist and confirm you land on the **appointments list**.
- [ ] A new booking shows the **client's name**, the **session type**, the **time** (in the
      correct time zone), and the **price**.
- [ ] A repeat booking shows a **"Recurring"** marker — click it to see the **whole series**
      (all upcoming sessions), not just the first one.
- [ ] The therapist can open each session in the series from that list.
- [ ] The therapist only sees the **products/services they are assigned to**.
- [ ] The main menu is simplified (the therapist doesn't see unrelated admin menus).

---

## 6. Reporting problems

If anything looks wrong, record the **page/step**, what you expected, and what you saw, and
add a screenshot if you can. Common things to look out for:

- A session slot that doesn't match the session length.
- A price that changes between choosing a session and the payment page.
- A currency that flips (e.g. they see dollars, then pay in pounds).
- A repeat booking that doesn't show the follow-up sessions.
- A missing or confusing email, or a "pay" button/link that doesn't work.

---

## 7. Sign-off checklist

- [ ] Session times match the chosen session length.
- [ ] Price shown is the session-type price and stays the same through checkout/payment.
- [ ] Currency is correct and consistent (EGP or USD).
- [ ] Repeat booking shows the full list of upcoming sessions in My Appointments.
- [ ] Confirmation and payment-reminder emails (customer and therapist) arrive and look
      correct.
- [ ] The therapist's view shows the booking and the repeat series correctly.
- [ ] No broken buttons or confusing wording during the booking flow.

---

> **Note for the tech team:** The fixes these checks cover are on the local/dev copy and must
> be deployed to production and re-verified before customers see them.

---

*This QA document corresponds to `docs/LEADERSHIP-SUMMARY.md`.*
