# License System Reporting Document

This document details the critical reports required for managing software licenses, focusing on the specific needs of a system that handles both local free trials and external premium licenses.

---

## 1. Compliance & Risk Mitigation Reports 🛡️

These reports focus on the fundamental task of ensuring legal compliance and preventing unexpected costs from under-licensing or wasted spend from over-licensing.

### A. Effective License Position (ELP) Report

| Detail | Description |
| :--- | :--- |
| **Business Value** | **Core Risk Management.** Provides a definitive, real-time snapshot of the difference between licenses purchased and licenses in active deployment. |
| **Key Metrics** | **License Compliance %:** (Total Active Deployments / Total Licenses Purchased) * 100%. **Surplus/Deficit:** Number of licenses over or under the purchased quantity. |
| **Technical Solution** | Query the **External API** for total premium licenses purchased. Query the **`FreeTrial` table** for all active, deployed licenses (`is_active = 1`). Aggregate the two counts and compare against the total purchased quantity (which should be stored locally or retrieved from the external source). |

### B. License Expiration & Renewal Forecast Report

| Detail | Description |
| :--- | :--- |
| **Business Value** | **Revenue Continuity.** Critical for sales and finance teams to prevent service disruption for clients and to accurately forecast revenue streams for the next 90/180 days. |
| **Key Metrics** | License Key, **Expiration Date**, **Annual Recurring Revenue (ARR)** (if premium), Days Remaining, Current Status (`Active`, `In Grace`). |
| **Technical Solution** | Query the **External API** for all premium licenses. Query the **`FreeTrial` table** for all local licenses. Filter this combined dataset for licenses expiring within the next 180 days. Use the date logic from `LicenseService::evaluate()` to calculate `Days Remaining`. |

---

## 2. Utilization & Optimization Reports 📈

These reports provide insight into **usage efficiency**, allowing the business to identify opportunities for license reclamation and cost optimization.

### C. Inactive/Underutilized Licenses Report (Reclamation)

| Detail | Description |
| :--- | :--- |
| **Business Value** | **Cost Avoidance.** Identifies premium licenses that are assigned but not being used, allowing the business to de-allocate them and reduce future renewal costs (or reassign them to a new user immediately). |
| **Key Metrics** | License Key, Date of Last Activity Check-In, **Time Since Last Activity** (e.g., > 90 days), Product Slug. |
| **Technical Solution** | Requires a **Usage Tracking System**. The license check endpoint (`appLicenseCheck`) should update a `last_checked_in_at` timestamp on the license record (local or external). This report queries all licenses where the `last_checked_in_at` timestamp is older than the defined threshold (e.g., 90 days). |

### D. Cost Per Active User (CPAU) Report

| Detail | Description |
| :--- | :--- |
| **Business Value** | **Efficiency Measurement.** Provides the true cost of providing the software to a user who is actually deriving value from it, exposing hidden waste and justifying the purchase of more efficient licenses. |
| **Key Metrics** | **Total Annual Spend** (from premium billing), **Total Active Users** (users with activity in the last 30 days). **CPAU** (Spend / Active Users). |
| **Technical Solution** | 1. Sum the **Total ARR** for all active premium licenses. 2. Count the number of licenses with recent usage (i.e., `last_checked_in_at` is within the last 30 days). 3. Calculate the ratio. This requires robust tracking of annual spend data. |

---

## 3. Financial & Strategic Reports 💰

These reports are critical for management to understand business growth and inform long-term product and pricing decisions.

### E. License Consumption Trend (Longitudinal)

| Detail | Description |
| :--- | :--- |
| **Business Value** | **Predictive Modeling.** Helps the business forecast future license needs, anticipate growth bottlenecks, and justify investment in server infrastructure. |
| **Key Metrics** | **Monthly Trend Line** for: 1) Total New Licenses Sold, 2) Total Licenses Expired, 3) **Net License Growth**. |
| **Technical Solution** | Analyze historical data in both the **External API** (for premium sales/churn) and the **`FreeTrial` table** (for free trial adoption/conversion) over the last 12-24 months. Group all records by the month of activation/expiration. |

### F. Free Trial Conversion Funnel Report

| Detail | Description |
| :--- | :--- |
| **Business Value** | **Monetization Insight.** Measures the effectiveness of the free trial offer and identifies where users drop off before becoming paying customers. |
| **Key Metrics** | 1) Total Free Trials Started, 2) Trials with **Activity**, 3) Trials Reaching **Expiration**, 4) Trials that **Converted** (where `is_fluent_license_check` changed from 0 to 1). |
| **Technical Solution** | Use the **`FreeTrial` table** exclusively. Track the lifecycle of each record based on its initial creation date, its `last_checked_in_at` status, and the crucial transition where the **`is_fluent_license_check` column changes from `0` to `1`**. |

---

## 4. Technical Implementation Notes (for Development)

The `LicenseService` is ideally suited to execute the complex filtering and aggregation required for these reports.

### Data Aggregation Layer

Since your system relies on two data sources (Local DB and External API), all reports should operate on a **Unified Data Transfer Object (DTO)** derived from both.

1.  **Unified License DTO:** Create a common object structure that encompasses necessary fields from both sources:
    ```php
    {
        'site_url',
        'product_slug',
        'is_premium', // true/false based on source or is_fluent_license_check
        'expiration_date',
        'purchase_date',
        'renewal_value_arr',
        'last_checked_in_at', // MUST be implemented via appLicenseCheck
    }
    ```
2.  **Service Layer Data Retrieval:** A dedicated reporting method (e.g., `LicenseService::getAllActiveLicenses()`) should fetch data from both the local `FreeTrial` table and the `ExternalLicenseProvider` API, normalize it into the DTO, and return a single, unified Collection.

### Dependency on Usage Tracking

The most valuable reports (**CPAU**, **Inactive Licenses**) are impossible without a measure of activity.

| Report Need | Technical Action |
| :--- | :--- |
| **Tracking Activity** | **Action:** Modify the `appLicenseCheck` controller method to update the `last_checked_in_at` column in the **`FreeTrial` table** (for local licenses) or call the **External API** to update the usage timestamp (for premium licenses) on every successful check. |
| **Defining Activity** | **Rule:** Define an "Active User" as any user whose `last_checked_in_at` is within the last 30 days. Define "Inactive" as 90 days or more. |
