# QA Review Guide — Customer Booking Experience (Frontend)

> **Date:** 2026-09-03
> **Audience:** Non-technical reviewer — you test the website the way a customer would
> (browse, choose a session, pick a time, book, repeat a booking, and check the emails you
> receive). You do **not** need to look at code, the database, or technical admin settings.
> **Where to test:** Open the three **test pages** listed under "Before you start" (the
> fixture-created test therapists). They are hidden, so use the direct links. Check with the
> tech person whether the changes are deployed to the site you're testing before you start.

---

## Before you start

The fixtures have been run — here are the three **test booking pages**. They are **hidden**
(not listed in the shop), so open them directly with these links:

| Booking page | Link | Currency shown | Good for testing |
|--------------|------|:--------------:|------------------|
| Test Therapist (EGP) | https://thewellnesshub-eg.com/appointment/test-therapist-egp/ | Egyptian pounds (EGP) | Session lengths, booking, repeat |
| Test Therapist (USD) | https://thewellnesshub-eg.com/appointment/test-therapist-usd/ | US dollars (USD) | Dollar prices, repeat in dollars |
| Test Therapist (Location) | https://thewellnesshub-eg.com/appointment/test-therapist-location/ | EGP or USD based on visitor | Prices for visitors in/out of Egypt |

Details on the test pages:

- Each page offers **three session types**: **Individual 30 min**, **Couples 60 min**,
  **Family 90 min**.
- The **starting price** is EGP 1,500 / USD 60, but when you pick a session type the price
  changes to that type's price (e.g. Couples 60 = **EGP 900**, or **USD 27** in dollars).
- The therapists can only be booked on **weekdays, 09:00–17:00**.

Also ask the tech person to:

- [ ] Make sure **emails** are working so you can receive the test emails.
- [ ] Confirm you can complete a **test payment** without a real charge.
- [ ] Use the **test coupon `test code`** (makes the order total **0**) — apply it at checkout
      for the booking, repeat, and email checks so nothing is actually charged. (Don't use it
      on the Paymob conversion test in Section 2.)

You'll view as a therapist using the **User Switching** plugin — no login/password needed.
See Section 5.

---

## 1. Choosing a session type and picking a time

**What to check:** The time slots shown must match the session length you choose, and the
price must match the session type you pick.

- [ ] Open **Test Therapist (EGP)** and confirm the **Session Type** dropdown loads — the
      page should have **no script/console errors** (this was a recent fix).
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
- [ ] To test a real charge through **Paymob**, pick the **30-minute session** (set to
      **$1**) — don't apply the **`test code`** coupon here, so there's a $1 charge to see
      converted.
- [ ] Complete a booking through to payment. A **payment method is always shown** — you
      should never see "no available payment methods".
- [ ] The **payment page** shows the amount you saw at booking, in dollars.

### When a dollar booking is paid through Paymob
- [ ] Use the **USD therapist** and pick the **30-minute session ($1)**. Do **not** apply the
      **`test code`** coupon here.
- [ ] Pay through **Paymob** — the **charge is in pounds (EGP)**. The booking amount stays in
      dollars, but you are charged the converted pound amount. This is normal.
- [ ] The **order / confirmation** shows a **"Paid via Paymob"** line with the converted
      pound amount and the exchange rate used.

### Location therapist (optional)
- [ ] Open **Test Therapist (Location)**.
- [ ] As a visitor from **Egypt** (or an **unknown** location), prices show **EGP**; as a
      visitor **known to be elsewhere**, prices show **USD**. (If you can only test from one
      place, ask the tech person to confirm the other case.)

### General
- [ ] The price you see when you **choose a session** is the same as the price at the
      **checkout page** and on the **payment page**.

---

## 3. Repeat (recurring) bookings

**What to check:** A customer can book a series of sessions and see them listed, and the
follow-up sessions show up in their account.

> Apply the **`test code`** coupon here so the order total is **0** — you're testing the
> repeat flow, not the payment.

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

> **How to read the emails:** You don't need a real inbox. Go to **Settings → Fluent SMTP →
> Email Logs** and open the relevant entry to read the full email. This is the easiest way to
> check the **customer's** and the **therapist's** emails, especially if they aren't arriving
> in a real inbox.

---

## 5. The therapist's view

**What to check:** From the therapist's side, the bookings look right — including repeat
series — and the therapist is only shown their own work.

Use the **User Switching** plugin: go to **Users**, find a **test therapist**, and click
**Switch to**. No login/password needed.

- [ ] After switching, confirm you land on the **appointments list**.
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
