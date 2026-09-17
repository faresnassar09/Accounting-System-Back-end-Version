# 📖 Accounting System — Complete REST API Documentation

Comprehensive API documentation for the **Enterprise Multi-Tenant Double-Entry Accounting & ERP Engine**.

---

## 📑 Table of Contents

1. [Global API Standards & Architecture](#1-global-api-standards--architecture)
2. [Authentication & Profile](#2-authentication--profile)
3. [Chart of Accounts](#3-chart-of-accounts)
4. [Journal Entries & Opening Balances](#4-journal-entries--opening-balances)
5. [Financial Year Closing](#5-financial-year-closing)
6. [Financial Reports (Internal)](#6-financial-reports-internal)
7. [External 3rd-Party Integration API](#7-external-3rd-party-integration-api)
8. [Error Handling & Status Codes](#8-error-handling--status-codes)

---

## 1. Global API Standards & Architecture

### Base URL & Multi-Tenancy Routing
This system uses **domain-based multi-tenancy** (`stancl/tenancy`). Every tenant possesses an isolated database. All API requests must be routed using the tenant's domain:

```http
http://{tenant_domain}:8000/api
```
*Example:* `http://tenant1.localhost:8000/api` or `http://e-wallet.localhost:8000/api`

### Mandatory Common Headers

| Header | Value | Description |
| :--- | :--- | :--- |
| `Accept` | `application/json` | **Required**. Prevents HTML 302 redirects on validation failures. |
| `Content-Type` | `application/json` | Required on all `POST` and `PUT` requests with JSON bodies. |
| `Authorization` | `Bearer <access_token>` | OAuth2 Bearer Token issued by Passport. |
| `Idempotency-Key` | `<unique-uuid-or-string>` | Required on write endpoints (`journal-entries`, `opening-balances`) to prevent duplicate transactions. |

### Standard Response Envelope
All API responses follow a uniform structure:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... },
  "code": 200
}
```

On validation or server failures:
```json
{
  "success": false,
  "message": "Validation errors",
  "data": {
    "field_name": [
      "The field_name is required."
    ]
  },
  "code": 422
}
```

---

## 2. Authentication & Profile

### 2.1 User Login
Authenticates an internal tenant user and issues a personal access token.

* **Method:** `POST`
* **Path:** `/v1/auth/login`
* **Auth:** Guest

#### Request Body
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `email` | `string` | Yes | Registered user email address. |
| `password` | `string` | Yes | User password. |

#### Example Request
```json
{
  "email": "accountant@tenant.com",
  "password": "secretpassword"
}
```

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Logged In Successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "accountant@tenant.com",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIs..."
  },
  "code": 200
}
```

---

### 2.2 User Logout
Revokes the current access token.

* **Method:** `POST`
* **Path:** `/v1/auth/logout`
* **Auth:** `Bearer <User Token>`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "User Is Loged Out",
  "data": [],
  "code": 200
}
```

---

### 2.3 User Profile
Retrieves authenticated user details, assigned role, and permissions.

* **Method:** `GET`
* **Path:** `/v1/profile`
* **Auth:** `Bearer <User Token>`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "User Profile Retrieved Successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "accountant@tenant.com",
    "role": "accountant"
  },
  "code": 200
}
```

---

### 2.4 OAuth2 Token (Client Credentials / M2M)
Issues OAuth2 Machine-to-Machine tokens for 3rd-party integrations.

* **Method:** `POST`
* **Path:** `/oauth/token`
* **Auth:** Public

#### Request Body (Client Credentials Grant)
```json
{
  "grant_type": "client_credentials",
  "client_id": "9d23ab50-...",
  "client_secret": "abcdef123456...",
  "scope": "*"
}
```

#### Example Response (200 OK)
```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi..."
}
```

---

## 3. Chart of Accounts

### 3.1 Get Hierarchical Chart Tree
Returns the entire Chart of Accounts in a nested parent-child tree structure.

* **Method:** `GET`
* **Path:** `/v1/accounting/charts`
* **Auth:** `Bearer <User Token>`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Chart Tree Reterived Successfully",
  "data": [
    {
      "id": 1,
      "name": "Assets",
      "number": 1,
      "parent_id": null,
      "descendants": [
        {
          "id": 10,
          "name": "Current Assets",
          "number": 10,
          "parent_id": 1,
          "descendants": [
            {
              "id": 1010,
              "name": "Cash / Bank",
              "number": 1010,
              "parent_id": 10
            }
          ]
        }
      ]
    }
  ],
  "code": 200
}
```

---

### 3.2 Get Flat Accounts List
Returns a flat list of all accounts for dropdown selectors.

* **Method:** `GET`
* **Path:** `/v1/accounting/accounts`
* **Auth:** `Bearer <User Token>`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Accounts Retrieved Successfully",
  "data": [
    { "id": 1010, "name": "Cash / Bank", "number": 1010 },
    { "id": 1020, "name": "Accounts Receivable", "number": 1020 },
    { "id": 2010, "name": "Accounts Payable", "number": 2010 },
    { "id": 4010, "name": "Sales Revenue", "number": 4010 }
  ],
  "code": 200
}
```

---

### 3.3 Get Closing Accounts
Returns equity and retained earnings accounts eligible to receive profit/loss during financial year closing.

* **Method:** `GET`
* **Path:** `/v1/accounting/accounts/closing`
* **Auth:** `Bearer <User Token>`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Closing Accounts Retrieved Successfully",
  "data": [
    { "id": 3020, "name": "Retained Earnings", "number": 3020 }
  ],
  "code": 200
}
```

---

## 4. Journal Entries & Opening Balances

### 4.1 Create Journal Entry
Posts a standard double-entry journal entry. 

> [!IMPORTANT]
> **Strict Balancing Rule**: $\sum \text{debit} = \sum \text{credit}$. If unbalanced, returns `422 Unprocessable Entity`.
> **Closed Year Lock**: If entry date falls in a closed fiscal year, returns `400 Bad Request`.
> **Idempotency**: Repeated calls with the same `Idempotency-Key` return the cached response without double-posting.

* **Method:** `POST`
* **Path:** `/v1/accounting/journal-entries`
* **Headers:** `Idempotency-Key: <unique-string>`
* **Auth:** `Bearer <User Token>`

#### Request Body
```json
{
  "journalHeader": {
    "reference": "JV-2026-001",
    "date": "2026-02-15",
    "description": "Customer cash purchase",
    "total_debit": 5000.00,
    "total_credit": 5000.00
  },
  "lines": [
    {
      "account_id": 1010,
      "debit": 5000.00,
      "credit": 0.00
    },
    {
      "account_id": 4010,
      "debit": 0.00,
      "credit": 5000.00
    }
  ]
}
```

#### Example Response (201 Created)
```json
{
  "success": true,
  "message": "Journal Entry Saved Successfully",
  "data": [],
  "code": 201
}
```

---

### 4.2 Create Opening Balances
Sets up initial account balances when starting the system.

* **Method:** `POST`
* **Path:** `/v1/accounting/opening-balances`
* **Headers:** `Idempotency-Key: <unique-string>`
* **Auth:** `Bearer <User Token>`

#### Request Body
```json
{
  "header": {
    "reference": "OP-2026",
    "date": "2026-01-01",
    "description": "Opening balances for year 2026"
  },
  "lines": [
    {
      "account_id": 1010,
      "debit": 50000.00,
      "credit": 0.00
    },
    {
      "account_id": 3010,
      "debit": 0.00,
      "credit": 50000.00
    }
  ]
}
```

#### Example Response (201 Created)
```json
{
  "success": true,
  "message": "Opening Balance Saved Successfully",
  "data": [],
  "code": 201
}
```

---

## 5. Financial Year Closing

### 5.1 Preview Fiscal Year Closing
Previews cumulative revenue, cumulative expenses, and computed net profit/loss for a fiscal year before closing.

* **Method:** `GET`
* **Path:** `/v1/accounting/financial-closing/preview/{year}`
* **Auth:** `Bearer <User Token>`

#### Example Request
```http
GET /v1/accounting/financial-closing/preview/2026
```

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    "total_revenues": 30000.00,
    "total_expenses": 22000.00,
    "net_profit": 8000.00
  },
  "code": 200
}
```

---

### 5.2 Apply Financial Year Closing
Executes full fiscal year closing:
1. Zeroes out all Revenue and Expense accounts (closing entries).
2. Posts the net difference to the selected **Retained Earnings** account.
3. Automatically generates the **Opening Balance Journal Entry** for the next fiscal year (`$year + 1`).
4. Locks the closed year from any future modifications.

* **Method:** `POST`
* **Path:** `/v1/accounting/financial-closing/apply`
* **Auth:** `Bearer <User Token>`

#### Request Body
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `year` | `string` | Yes | Year to close, e.g. `"2026"`. |
| `account_id` | `integer` | Yes | ID of Retained Earnings account to receive net profit/loss. |

#### Example Request
```json
{
  "year": "2026",
  "account_id": 3020
}
```

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Financial Year Closed Successfully",
  "data": [],
  "code": 200
}
```

---

## 6. Financial Reports (Internal)

All report calculations run directly in **SQL** (Zero PHP Math).

### 6.1 Trial Balance
* **Method:** `GET`
* **Path:** `/v1/accounting/reports/trial-balance`
* **Auth:** `Bearer <User Token>`
* **Query Parameters:**
  * `startDate` / `start_date` *(optional)*: `YYYY-MM-DD`
  * `endDate` / `end_date` *(optional)*: `YYYY-MM-DD` (defaults to today)

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Trial Balance Report Generated Successfully",
  "data": {
    "accounts": [
      {
        "id": 1010,
        "name": "Cash / Bank",
        "number": 1010,
        "opening_debit": 0.00,
        "opening_credit": 0.00,
        "period_debit": 54000.00,
        "period_credit": 0.00,
        "final_debit_balance": 54000.00,
        "final_credit_balance": 0.00
      }
    ],
    "totals": {
      "total_debit": 90000.00,
      "total_credit": 90000.00,
      "isBalanced": true
    }
  },
  "code": 200
}
```

---

### 6.2 General Ledger
* **Method:** `GET`
* **Path:** `/v1/accounting/reports/general-ledger`
* **Auth:** `Bearer <User Token>`
* **Query Parameters:**
  * `accountId`: **Required**. Account primary key.
  * `startDate` / `start_date` *(optional)*: `YYYY-MM-DD`
  * `endDate` / `end_date` *(optional)*: `YYYY-MM-DD`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "General Ledger Report Generated Successfully",
  "data": {
    "account_info": {
      "name": "Cash / Bank",
      "number": 1010
    },
    "opening_balance": 0.00,
    "closing_balance": 54000.00,
    "total_debit": 70000.00,
    "total_credit": 16000.00,
    "transactions": [
      {
        "date": "2026-01-01 00:00:00",
        "reference": "JV-001",
        "description": "Capital deposit",
        "debit": 50000.00,
        "credit": 0.00,
        "running_balance": 50000.00
      }
    ]
  },
  "code": 200
}
```

---

### 6.3 Income Statement (Profit & Loss)
* **Method:** `GET`
* **Path:** `/v1/accounting/reports/income-statement`
* **Auth:** `Bearer <User Token>`
* **Query Parameters:**
  * `startDate` / `start_date` *(optional)*: `YYYY-MM-DD`
  * `endDate` / `end_date` *(optional)*: `YYYY-MM-DD`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Income Statement Report Generated Successfully",
  "data": {
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
    "revenues": {
      "total_revenue": 29000.00,
      "net_sales": 29000.00,
      "operating_revenue": 0.00
    },
    "cost_of_goods_sold": {
      "total_cogs": 10000.00,
      "gross_profit": 19000.00
    },
    "operating_activities": {
      "total_expenses": 9000.00,
      "operating_income": 10000.00
    },
    "taxes": {
      "income_before_tax": 10000.00,
      "tax_expense_total": 2000.00
    },
    "final_result": {
      "net_income": 8000.00,
      "is_profit": true
    }
  },
  "code": 200
}
```

---

### 6.4 Balance Sheet
* **Method:** `GET`
* **Path:** `/v1/accounting/reports/balance-sheet`
* **Auth:** `Bearer <User Token>`
* **Query Parameters:**
  * `endDate` / `end_date` *(optional)*: `YYYY-MM-DD`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Balance Sheet Report Generated Successfully",
  "data": {
    "assets_group": {
      "group_code": "assets",
      "group_name": "Assets",
      "group_total": 68000.00,
      "sub_types": [
        {
          "type_code": "current_assets",
          "type_name": "Current Assets",
          "type_total": 68000.00,
          "accounts": [...]
        }
      ]
    },
    "liabilities_and_equity_group": {
      "group_code": "liabilities_and_equity",
      "group_name": "Liabilities and equity",
      "group_total": 68000.00,
      "sub_types": {
        "liabilities_group": {
          "type_code": "current_liabilities",
          "type_name": "Current Liabilities",
          "type_total": 10000.00
        },
        "equity_group": {
          "type_code": "equity",
          "type_name": "Owners Rights",
          "type_total": 58000.00
        }
      }
    }
  },
  "code": 200
}
```

---

## 7. External 3rd-Party Integration API

Designed for third-party machine-to-machine integrations (E-wallets, POS, Billing gateways).
* **Guards:** `EnsureClientIsResourceOwner`
* **Rate Limit:** `10 requests / minute` (by client ID or IP)
* **Auth:** `Bearer <OAuth2 Client Credentials Token>`

### 7.1 Create External Transaction
Receives external transactions and dispatches asynchronous background ledger processing (`CreateTransactionJob`).

* **Method:** `POST`
* **Path:** `/external/transaction/create`
* **Auth:** `Bearer <Client Credentials Token>`

#### Request Body
```json
{
  "timestamp": "2026-09-15 14:30:00",
  "description": "Order #5021 payment received",
  "total_amount": 1500.00,
  "parties": {
    "senders": [
      {
        "source_reference": "WALLET_USER_101",
        "amount": 1500.00
      }
    ],
    "receivers": [
      {
        "source_reference": "MERCHANT_WALLET_MAIN",
        "amount": 1500.00
      }
    ]
  }
}
```

#### Example Response (201 Created)
```json
{
  "success": true,
  "message": "Transaction Created Successfully",
  "data": [],
  "code": 201
}
```

---

### 7.2 Get External Transactions
Queries journal lines produced by external systems.

* **Method:** `GET`
* **Path:** `/external/transaction/get`
* **Auth:** `Bearer <Client Credentials Token>`
* **Query Parameters:**
  * `source_reference` *(optional)*: String
  * `start_date` *(optional)*: `YYYY-MM-DD`
  * `end_date` *(optional)*: `YYYY-MM-DD`

#### Example Response (200 OK)
```json
{
  "success": true,
  "message": "Transactions Retrieved Successfully",
  "data": [
    {
      "id": 1,
      "source_reference": "WALLET_USER_101",
      "debit": 1500.00,
      "credit": 0.00,
      "created_at": "2026-09-15 14:30:00"
    }
  ],
  "code": 200
}
```

---

### 7.3 External Reports Endpoints
Each report has its own isolated controller under `Modules\Accounting\Http\Controllers\External\Reports\`.

#### A) External Trial Balance
* **Method:** `GET`
* **Path:** `/external/reports/trial-balance`
* **Query Params:** `endDate` / `end_date` (`YYYY-MM-DD`)

#### B) External General Ledger
* **Method:** `GET`
* **Path:** `/external/reports/general-ledger`
* **Query Params:**
  * `account_number` *(e.g. `1010`)* **OR** `accountId` *(Primary Key)*
  * `startDate` / `start_date` (`YYYY-MM-DD`)
  * `endDate` / `end_date` (`YYYY-MM-DD`)

#### C) External Income Statement
* **Method:** `GET`
* **Path:** `/external/reports/income-statement`
* **Query Params:** `startDate`, `endDate` (`YYYY-MM-DD`)

#### D) External Balance Sheet
* **Method:** `GET`
* **Path:** `/external/reports/balance-sheet`
* **Query Params:** `endDate` / `end_date` (`YYYY-MM-DD`)

---

## 8. Error Handling & Status Codes

| HTTP Code | Constant | Meaning | Common Cause |
| :--- | :--- | :--- | :--- |
| **`200 OK`** | Success | Request succeeded. | Valid read / action completed. |
| **`201 Created`** | Created | Record successfully created. | Journal entry, opening balance, or transaction recorded. |
| **`400 Bad Request`** | Client Error | Business rule violation. | Attempting to post to a closed fiscal year. |
| **`401 Unauthorized`**| Unauthorized | Authentication failed. | Missing/expired Bearer token or invalid OAuth client. |
| **`403 Forbidden`** | Forbidden | Insufficient permissions. | Authenticated user lacks required Spatie role/permission. |
| **`404 Not Found`** | Not Found | Route or Tenant not found. | Invalid tenant domain or route method mismatch. |
| **`409 Conflict`** | Conflict | Idempotency lock active. | Duplicate simultaneous request for the same key. |
| **`422 Unprocessable`** | Validation Error | Form validation failed. | Unbalanced debit/credit, missing mandatory fields. |
| **`429 Too Many Requests`** | Rate Limited | Throttling limit exceeded. | More than 40 req/min (internal) or 10 req/min (external). |
| **`500 Internal Server`**| Server Error | Unexpected failure. | Database connection issue or unhandled exception. |
