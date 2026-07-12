# Water System API - Authentication Endpoints

## Overview
This document provides information about the Login and Register API endpoints for the Water System application.

## Base URL
```
http://127.0.0.1:8000/api
```

---

## 1. Register User
**Create a new user account**

### Endpoint
```
POST /auth/register
```

### Request Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Validation Rules
- **name**: Required, string, max 255 characters
- **email**: Required, valid email, unique in database, max 255 characters
- **password**: Required, string, minimum 6 characters, must be confirmed

### Success Response (201 Created)
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

### Error Response (422 Unprocessable Entity)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password field must be at least 6 characters."]
    }
}
```

---

## 2. Login User
**Authenticate a user and receive an API token**

### Endpoint
```
POST /auth/login
```

### Request Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

### Validation Rules
- **email**: Required, valid email format
- **password**: Required, string

### Success Response (200 OK)
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "token": "1|abcdefghijklmnopqrstuvwxyz123456789"
    }
}
```

### Error Response (401 Unauthorized)
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

---

## 3. Get Current User
**Retrieve information about the authenticated user**

### Endpoint
```
GET /auth/user
```

### Request Headers
```
Accept: application/json
Authorization: Bearer {token}
```

### Success Response (200 OK)
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

### Error Response (401 Unauthorized)
```json
{
    "success": false,
    "message": "Unauthorized"
}
```

---

## 4. Logout User
**Revoke the user's API token**

### Endpoint
```
POST /auth/logout
```

### Request Headers
```
Accept: application/json
Authorization: Bearer {token}
```

### Success Response (200 OK)
```json
{
    "success": true,
    "message": "Logout successful"
}
```

---

## Usage Examples with cURL

### Register a new user
```bash
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get current user (using token from login)
```bash
curl -X GET http://127.0.0.1:8000/api/auth/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Logout
```bash
curl -X POST http://127.0.0.1:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Protected Routes
The following routes now require authentication with a valid Sanctum token:

- `GET /api/bill/{nic}` - Get bill by NIC
- `POST /api/bill` - Create new bill
- `PUT /api/bill/{nic}` - Update bill by NIC
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/user` - Get current user

Include the token in the `Authorization` header as `Bearer {token}`

---

## Token Storage
Tokens are stored in the `personal_access_tokens` table in the database. Each token is associated with a user and can be revoked when the user logs out.

---

## Notes
- All API responses use JSON format
- Timestamps are returned in UTC
- Passwords are hashed using bcrypt algorithm
- API tokens do not expire by default but can be revoked by the user
