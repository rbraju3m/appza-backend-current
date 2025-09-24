# License API Guide (Frontend Integration)

This guide describes how Web & Mobile frontend should call the License Management API.

---

## 1. Base URL


---

## 2. Endpoints

### 2.1 Activate License (Web Only)

**URL:**  
`POST /api/v2/license/activate`

**Parameters (JSON body):**

| Field         | Type   | Required | Description                   |
|---------------|--------|----------|-------------------------------|
| `site_url`    | string | ✅       | Website domain                |
| `license_key` | string | ✅       | License key provided          |
| `email`       | string | ❌       | Optional user email           |

**Example Request:**

```json
{
  "site_url": "example.com",
  "license_key": "ABC-123-XYZ",
  "email": "user@example.com"
}
{
  "status": "activate",
  "sub_status": "activate_license",
  "message": "Your License key has been activated successfully."
}
{
  "status": "suspended",
  "sub_status": "free_trial_not_found",
  "message": "This site url is not registered for this license activation. Please contact support."
}
 ```

### 2.2 Web License Check

**URL:**  
`POST /api/v2/license/web-check`

**Parameters (JSON body):**


| Field         | Type   | Required | Description                   |
|---------------|--------|----------|-------------------------------|
| `site_url`    | string | ✅       | Website domain                |
| `license_key` | string | ✅       | License key provided          |

```json
{
  "site_url": "example.com",
  "license_key": "ABC-123-XYZ"
}
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

### 2.3 Mobile App License Check

**URL:**  
`POST /api/v2/license/app-check`

**Parameters (JSON body):**


| Field      | Type   | Required | Description             |
|------------|--------|----------|-------------------------|
| `site_url` | string | ✅       | App installation domain |
| `product`  | string | ✅       | Product slug (e.g., `my_app`)    |

```json
{
  "site_url": "mobile.example.com",
  "product": "my_app"
}
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
### 3 Status & Sub-Status

| Status      | Description                                          |
| ----------- | ---------------------------------------------------- |
| `invalid`   | Wrong license, not found, or validation error        |
| `active`    | License valid (before expiry or in grace period)     |
| `expired`   | License fully expired                                |
| `activate`  | License activated successfully                       |
| `suspended` | License suspended (e.g., not linked to site/product) |

| Sub-Status                        | Description                        |
| --------------------------------- | ---------------------------------- |
| `before_exp`                      | Before expiration date             |
| `expires_today`                   | Expiring today                     |
| `in_grace`                        | Inside grace period                |
| `grace_expired`                   | Grace period ended                 |
| `free_trial_not_found`            | Free trial missing                 |
| `product_configuration_not_found` | Product not configured             |
| `external_api_error`              | Failed to verify with external API |

### 4. Frontend Integration Notes

Always send site_url in both Web & Mobile requests.

For Web, also send license_key.

For Mobile, also send product.

Use status + sub_status to decide what message to show.

Show user message from response to the customer.

Optionally log admin/special messages if needed.

Handle popup messages if popup_message array is not empty.

Use meta info (like expiration_days_diff, grace_days_diff, product_id) for internal logic. 

### 5. Example Flow (Mobile)

On app install, send POST /app-check with site_url and product.

If status = active and sub_status = before_exp → normal usage.

If sub_status = in_grace → show grace message, optionally restrict features.

If status = expired → block features and notify user.

Use meta.expiration_days_diff for countdown or notifications.
