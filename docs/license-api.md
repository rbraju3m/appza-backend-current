# License API Documentation

This document outlines the **License API Controller (`LicenseController`)**, which manages the full lifecycle of plugin licenses, from activation to deactivation. It handles free trial and premium (Fluent) licenses for several products, including `appza`, `lazy_task`, and `fcom_mobile`.

---

## 🔑 Authorization

All API endpoints require a valid authorization token. This is verified by the `Lead::checkAuthorization()` method. If authorization fails, the API returns a **401 Unauthorized** response.

---

## 📡 Endpoints

### 1. Activate License

-   **Route:** `POST /api/v1/license/activate`
-   **Purpose:** Activates a new license for a specific website.
-   **Parameters:**
    -   `site_url` (string, required): The website URL.
    -   `license_key` (string, required): The license key provided by Fluent.
    -   `email` (string, optional): The user's email address.
-   **Flow:**
    1.  The `site_url` is normalized.
    2.  The API validates the Fluent plugin configuration.
    3.  A call is made to the Fluent API to activate the license.
    4.  Upon success, license details are saved/updated in the local database models (`BuildDomain`, `FluentLicenseInfo`, `FreeTrial`).
-   **Responses:**
    -   `200 OK`: License activated successfully.
    -   `422 Unprocessable Entity`: Invalid configuration or license.
    -   `500 Internal Server Error`: An issue with the API connection or database.

### 2. Check License Validity

-   **Route:** `POST /api/v1/license/check`
-   **Purpose:** Verifies if an existing license is still valid.
-   **Parameters:**
    -   `site_url` (string, required): The website URL.
    -   `license_key` (string, required): The license key.
-   **Flow:**
    1.  The `site_url` is normalized.
    2.  The API checks the local database for an existing `activation_hash`.
    3.  It then calls the Fluent API to check the license's validity.
-   **Responses:**
    -   `200 OK`: The license is valid.
    -   `404 Not Found`: No license record was found in the local database.
    -   `422 Unprocessable Entity`: The Fluent plugin is misconfigured.
    -   `500 Internal Server Error`: The license server cannot be reached.

### 3. Version Check

-   **Route:** `POST /api/v1/license/version-check`
-   **Purpose:** Retrieves the latest version information for a licensed product.
-   **Parameters:**
    -   `license_key` (string, required): The license key.
-   **Flow:**
    1.  The API fetches the plugin configuration and `activation_hash` from the local database.
    2.  It calls the Fluent API to retrieve the version data.
-   **Responses:**
    -   `200 OK`: License version retrieved successfully.
    -   `404 Not Found`: The license record was not found.
    -   `422 Unprocessable Entity`: The Fluent plugin is misconfigured.
    -   `500 Internal Server Error`: Failed to connect to the license server.

### 4. App License Check (Free Trial & Premium)

-   **Route:** `POST /api/v1/license/app-license-check`
-   **Purpose:** Checks the license status (free trial or premium) for a specific app.
-   **Parameters:**
    -   `site_url` (string, required): The website URL.
    -   `product` (string, required): The product name (`appza`, `lazy_task`, or `fcom_mobile`).
-   **Flow:**
    1.  The API loads cached popup messages and validates the request parameters.
    2.  It checks the `FreeTrial` model for a license record.
    3.  If a free trial is active, it validates the `grace_period_date`.
    4.  If a premium license is active, it validates the license via the Fluent API.
-   **Responses:**
    -   `200 OK`: Free trial or premium license is valid.
    -   `403 Forbidden`: The premium license is not configured or is invalid.
    -   `404 Not Found`: No license record was found.
    -   `422 Unprocessable Entity`: The Fluent plugin is misconfigured.
    -   `503 Service Unavailable`: The license server is unavailable.

### 5. Deactivate License / Delete Plugin

-   **Route:** `POST /api/v1/license/deactivate`
-   **Purpose:** Deactivates a license or marks a plugin as deleted.
-   **Parameters:**
    -   `site_url` (string, required): The website URL.
    -   `product` (string, required): The product name.
    -   `appza_action` (string, required): The action to perform (`license_deactivate` or `plugin_delete`).
    -   `license_key` (string, conditional): Required if `appza_action` is `license_deactivate`.
-   **Flow:**
    1.  The request is validated.
    2.  If the action is `plugin_delete`, the plugin is marked as deleted in the database.
    3.  If the action is `license_deactivate`, the API fetches the `activation_hash` and calls the Fluent API to deactivate the license.
    4.  The local database (`BuildDomain`, `FreeTrial`) is updated accordingly.
-   **Responses:**
    -   `200 OK`: License deactivated or plugin deleted successfully.
    -   `404 Not Found`: The license record was not found.
    -   `422 Unprocessable Entity`: The Fluent plugin is misconfigured.
    -   `500 Internal Server Error`: An API or database error occurred.

---

## ⚙️ Utility Methods

-   `normalizeUrl($url)`: Ensures a URL is properly formatted, always prefixed with `https://`.
-   `getSubdomainAndDomain($url)`: Extracts the subdomain and domain from a URL.
-   `getFluentErrorMessage($code, $default)`: Translates error codes from the Fluent API into human-readable messages.

---

## 🗄️ Database Models

-   `Lead`: Manages API authorization.
-   `BuildDomain`: Stores license activation details per domain.
-   `FluentInfo`: Holds Fluent product configurations (e.g., `item_id`, `api_url`).
-   `FluentLicenseInfo`: Stores Fluent license metadata like the `activation_hash`.
-   `FreeTrial`: Tracks free trial license status and grace periods.
-   `PopupMessage`: Manages cached messages displayed to users.

---

## 📑 Error Handling & Logging

All API calls are wrapped in `try/catch` blocks. Failures are logged using Laravel's `Log` facade to assist with debugging.

---
