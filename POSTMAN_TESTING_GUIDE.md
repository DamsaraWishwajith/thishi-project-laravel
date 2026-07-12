# Quick Testing Guide - Postman

## Setup

1. **Start the server:**
   ```bash
   php artisan serve --host=10.117.128.164 --port=8000
   ```

2. **Create Environment Variable in Postman:**
   - Settings → Environments → Create "Water System"
   - Add variable: `token` (initial value: empty)
   - Add variable: `base_url` (initial value: `http://10.117.128.164:8000/api`)

---

## Test Flow

### 1️⃣ REGISTER NEW USER
```
POST {{base_url}}/auth/register

{
  "nic": 123456789,
  "email": "user@example.com",
  "name": "John Doe",
  "pno": "0701234567",
  "address": "123 Main Street",
  "bill_no": "BILL001",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Tests Tab (save token):**
```javascript
pm.environment.set("token", pm.response.json().data.token);
```

---

### 2️⃣ LOGIN
```
POST {{base_url}}/auth/login

{
  "nic": 123456789,
  "password": "password123"
}
```

**Tests Tab:**
```javascript
pm.environment.set("token", pm.response.json().data.token);
```

---

### 3️⃣ GET CURRENT USER
```
GET {{base_url}}/auth/user

Headers:
Authorization: Bearer {{token}}
```

---

### 4️⃣ CREATE BILL
```
POST {{base_url}}/bill

Headers:
Authorization: Bearer {{token}}

{
  "nic": 123456789,
  "january_point": "50",
  "january_bill": "1500"
}
```

---

### 5️⃣ GET BILL
```
GET {{base_url}}/bill/123456789

Headers:
Authorization: Bearer {{token}}
```

---

### 6️⃣ UPDATE BILL
```
PUT {{base_url}}/bill/123456789

Headers:
Authorization: Bearer {{token}}

{
  "february_point": "60",
  "february_bill": "1800",
  "march_point": "70",
  "march_bill": "2100"
}
```

---

### 7️⃣ LOGOUT
```
POST {{base_url}}/auth/logout

Headers:
Authorization: Bearer {{token}}
```

---

## Error Codes

- **200:** Success
- **201:** Created successfully
- **400:** Bad request
- **401:** Unauthorized (token invalid/expired)
- **403:** Forbidden (accessing other user's bill)
- **404:** Not found
- **422:** Validation error
- **500:** Server error

---

## Common Issues

| Issue | Solution |
|-------|----------|
| Token expired | Login again to get new token |
| 403 Forbidden | You can only access your own bill (matching your NIC) |
| 404 Bill not found | Create bill first using POST |
| 422 Validation error | Check required fields and data types |
| Invalid credentials | Check nic and password |

---

## Database Reset

```bash
php artisan migrate:fresh
```

This will reset all tables to empty state.
