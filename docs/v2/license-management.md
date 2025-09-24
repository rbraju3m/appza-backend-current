# License Management User Guide

This document explains the complete **License Management Process** for users. It covers license activation, checking, expiration, and deactivation, for both **Web** and **Mobile** applications.

---

## Table of Contents

1. [Overview](#overview)
2. [License Types](#license-types)
3. [License Activation](#license-activation)
4. [Checking License Validity](#checking-license-validity)
5. [License Expiration & Grace Period](#license-expiration--grace-period)
6. [Deactivating a License](#deactivating-a-license)
7. [Understanding Status & Sub-Status](#understanding-status--sub-status)
8. [Common Scenarios](#common-scenarios)
9. [FAQs](#faqs)

---

## Overview

Licenses are required to use the plugins or apps. The license system ensures that:

* Only authorized websites or apps can use the product.
* Free trials are limited to a specific period.
* Premium licenses are validated via the external Fluent API.
* License status (active, expired, or suspended) is tracked per website or app installation.

All license-related actions communicate with **Fluent License Server** and also store details locally.

---

## License Types

1. **Free Trial License**

    * Valid for a limited period (e.g., 7 or 14 days).
    * Stored locally in the `FreeTrial` database.
    * Can have a **grace period** after expiration.

2. **Premium (Fluent) License**

    * Paid license verified via the **Fluent API**.
    * Stores license data locally in `BuildDomain` and `FluentLicenseInfo`.
    * Can be activated/deactivated remotely.

---

## License Activation

### Web Activation

1. User submits:

    * `site_url` → Website URL
    * `license_key` → License key provided
    * `email` (optional)

2. System validates:

    * Free trial or existing premium license.
    * Plugin product configuration.

3. System calls Fluent API for activation.

4. On success:

    * Local database models `BuildDomain` and `FluentLicenseInfo` are updated.
    * The license is marked as active.

**Success Response Example:**

```json
{
  "status": "activate",
  "sub_status": "activate_license",
  "message": "Your License key has been activated successfully."
}
```

**Failure Response Example:**

```json
{
  "status": "suspended",
  "sub_status": "free_trial_not_found",
  "message": "This site url is not registered for this license activation. Please contact support."
}
```

---

## Checking License Validity

### Web License Check

1. User submits:

    * `site_url`
    * `license_key`

2. System validates:

    * Local activation hash in `FluentLicenseInfo`.
    * Calls Fluent API to confirm license is valid.

3. Response contains:

    * `status`: `active`, `expired`, or `invalid`.
    * `sub_status`: Detailed state (e.g., `before_exp`, `in_grace`).
    * `message`: User, admin, and special messages.
    * `popup_message`: Any active notifications.
    * `meta`: License metadata (days until expiration, grace period, product ID).

**Example Active Response:**

```json
{
  "status": "active",
  "sub_status": "before_exp",
  "message": {
    "user": {"message": "Your license is valid for 5 more days", "message_id": 12},
    "admin": {"message": "Admin reminder", "message_id": 34},
    "special": {"message": null, "message_id": null}
  },
  "popup_message": [],
  "meta": {
    "expiration_days_diff": 5,
    "grace_days_diff": 12,
    "product_id": 7
  }
}
```

### Mobile App License Check

1. User submits:

    * `site_url` → App installation domain
    * `product` → Product slug (e.g., `my_app`)

2. System checks:

    * Local `FreeTrial` or `BuildDomain`.
    * Premium license via Fluent API if available.

3. Response is similar to Web, with `status`, `sub_status`, `message`, `popup_message`, and `meta`.

**Example Expired in Grace Period:**

```json
{
  "status": "active",
  "sub_status": "in_grace",
  "message": {
    "user": {"message": "Your trial expired 2 days ago. You are in grace period.", "message_id": 21},
    "admin": {"message": "Admin note: Grace active", "message_id": 22},
    "special": {"message": null, "message_id": null}
  },
  "popup_message": [],
  "meta": {
    "expiration_days_diff": -2,
    "grace_days_diff": 5,
    "product_id": 9
  }
}
```

---

## License Expiration & Grace Period

1. **Expiration Date**

    * License becomes inactive after this date.

2. **Grace Period**

    * Some licenses allow extra days after expiration.
    * Users are notified with `sub_status: in_grace`.

3. **Fully Expired**

    * License cannot be used after grace period.
    * `status: expired` and `sub_status: grace_expired`.

---

## Deactivating a License

1. Web or App requests:

    * `site_url`
    * `product`
    * `license_key` (required for license deactivation)
    * `action` → `license_deactivate` or `plugin_delete`

2. System updates:

    * Local database `BuildDomain` and `FreeTrial`.
    * Calls Fluent API if license deactivation is required.

3. Success Response:

```json
{
  "status": "suspended",
  "sub_status": "license_deactivated",
  "message": "License has been deactivated successfully."
}
```

---

## Understanding Status & Sub-Status

### Status

| Status    | Meaning                                       |
| --------- | --------------------------------------------- |
| invalid   | Wrong license, not found, or validation error |
| active    | License valid                                 |
| expired   | License fully expired                         |
| activate  | License activated successfully                |
| suspended | License suspended                             |

### Sub-Status

| Sub-Status                         | Meaning                            |
| ---------------------------------- | ---------------------------------- |
| before\_exp                        | Before expiration date             |
| expires\_today                     | License expires today              |
| in\_grace                          | Inside grace period                |
| grace\_expired                     | Grace period ended                 |
| free\_trial\_not\_found            | Free trial missing                 |
| product\_configuration\_not\_found | Product not configured             |
| external\_api\_error               | Failed to verify with external API |

---

## Common Scenarios

1. **New User with Free Trial**

    * Activation succeeds if site\_url matches plugin.
    * Grace period applies after expiration.

2. **Premium License Activation**

    * Activation requires Fluent license key.
    * Database is updated with `BuildDomain` and `FluentLicenseInfo`.

3. **License Expired**

    * User sees message about expiration.
    * Premium users must renew via Fluent API.

4. **License Deactivated**

    * Users cannot access plugin features.
    * Admins can reactivate with valid license.

---

## FAQs

**Q1: Can I activate the same license on multiple sites?**
A: It depends on your license activation limit. Premium licenses have a maximum activation count.

**Q2: What happens during grace period?**
A: Users can continue using the plugin but are warned that the license is about to expire.

**Q3: Can I check license from mobile app?**
A: Yes, use `app-license-check` endpoint with `site_url` and `product`.

**Q4: What if my license server is down?**
A: You may temporarily use cached free trial data, but premium verification requires the Fluent API.

---

**End of License Management User Guide**
