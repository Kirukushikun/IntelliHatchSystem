# Form Statistics API

## Base URL
```
https://your-domain.com/api
```

## Authentication
All endpoints require authentication using Sanctum tokens.

## Endpoints

### 1. Get Form Statistics
**GET** `/form-stats`

Returns form submission statistics with flexible filtering.

#### Parameters
- `form_type_id` (optional, integer) - Filter by specific form type ID
- `date_filter` (optional, string) - Date filter type. Options:
  - `current_month` (default)
  - `last_month`
  - `current_quarter`
  - `last_quarter`
  - `last_three_months`
  - `current_year`
  - `previous_year`
  - `custom` (requires `start_date` and `end_date`)
- `start_date` (optional, date) - Start date for custom filter (YYYY-MM-DD)
- `end_date` (optional, date) - End date for custom filter (YYYY-MM-DD)

#### Examples

**Get all forms for current month:**
```
GET /form-stats
```

**Get specific form type for last quarter:**
```
GET /form-stats?form_type_id=1&date_filter=last_quarter
```

**Get custom date range:**
```
GET /form-stats?date_filter=custom&start_date=2024-01-01&end_date=2024-01-31
```

#### Response Format
```json
{
  "filters": {
    "form_type_id": null,
    "date_filter": "current_month",
    "start_date": "2024-02-01",
    "end_date": "2024-02-29"
  },
  "stats": {
    "total_forms": 45,
    "period_start": "2024-02-01",
    "period_end": "2024-02-29"
  },
  "forms_by_type": [
    {
      "name": "Incubator Routine Checklist Per Shift",
      "count": 25
    },
    {
      "name": "Another Form Type",
      "count": 20
    }
  ],
  "daily_submissions": [
    {
      "date": "2024-02-01",
      "count": 5
    },
    {
      "date": "2024-02-02",
      "count": 3
    }
  ]
}
```

### 2. Get Quick Stats
**GET** `/form-stats/quick`

Returns quick statistics for common time periods.

#### Response Format
```json
{
  "current_month": 45,
  "last_month": 38,
  "current_quarter": 120,
  "last_three_months": 95,
  "current_year": 450,
  "total_users": 25,
  "active_users": 20
}
```

## Usage Examples

### JavaScript/Fetch
```javascript
// Get current month stats
const response = await fetch('/api/form-stats', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});
const data = await response.json();

// Get custom date range for specific form type
const customResponse = await fetch('/api/form-stats?form_type_id=1&date_filter=custom&start_date=2024-01-01&end_date=2024-01-31', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});
```

### PHP/Guzzle
```php
$client = new GuzzleHttp\Client();

$response = $client->get('https://your-domain.com/api/form-stats', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json'
    ],
    'query' => [
        'form_type_id' => 1,
        'date_filter' => 'current_quarter'
    ]
]);

$data = json_decode($response->getBody(), true);
```

## Error Responses

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "start_date": ["The start date field is required when date filter is custom."],
    "end_date": ["The end date field is required when date filter is custom."]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
