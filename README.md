
# 🔗 LinkedIn PHP Integration

This project demonstrates how to integrate LinkedIn's OAuth 2.0 and Media APIs using PHP. It allows users to log in with their LinkedIn accounts and share text, image, or video posts to their LinkedIn feed.

---

## 🚀 Features

- LinkedIn OAuth 2.0 login
- Share text-only posts
- Upload and share images
- Upload and share videos (up to 5MB)
- Token-based authentication and session management

---

## 📦 Project Structure

```
linkedin-php-integration/
│
├── api/
│   └── share.php                  # Endpoint to handle post sharing
│
├── app/
│   ├── LinkedInService.php        # Main LinkedIn API client logic
│                   
│
|-- dashboard.php              # Main UI form
│── index.php                  
│── callback.php               # OAuth redirect handler
│── logout.php                 # Logout script
│-- config.php                 # Contains LinkedIn app credentials
└── README.md                   
```

---

## 🔧 Configuration

Edit the file `config.php` with your LinkedIn App credentials:

```php
<?php
return [
    'client_id' => 'your client id',
    'client_secret' => 'your client secret',
    'redirect_uri' => 'url',
    'scopes' => 'openid profile email w_member_social'
];
```

### 📝 Config Options Explained:

| Key             | Description                                                                 |
|------------------|-----------------------------------------------------------------------------|
| `client_id`       | Your LinkedIn App’s Client ID (from [LinkedIn Developer Portal](https://www.linkedin.com/developers/apps)) |
| `client_secret`   | Your LinkedIn App’s Client Secret                                           |
| `redirect_uri`    | URL LinkedIn will redirect to after login (must match app settings)        |
| `scopes`          | Permissions you're requesting. Use `w_member_social` to allow posting      |

---

## 🔐 Authentication Flow

1. User clicks "Login with LinkedIn"
2. Redirects to LinkedIn's auth page
3. On success, user is redirected back with an access token
4. Token is stored in session for API calls

---

## 🖼️ Sharing Posts

### Form Options:

- **Text** (required)
- **Image URL** (optional) — OR —
- **Video File Upload** (optional)

> If a video is selected, it overrides the image. Only one media type is uploaded per post.

---

## ⚙️ Server Requirements

- PHP 7.4 or higher
- cURL enabled
- File upload limits set appropriately (`upload_max_filesize = 10M`, `post_max_size = 10M`)

---

## 💡 Tips

- Make sure `redirect_uri` is whitelisted in your LinkedIn app settings
- Use `$_SESSION` securely (consider HTTPS in production)
- For production use, store secrets in `.env` or server environment variables

---

## 📸 Screenshot

![LinkedIn Form](./assets/screenshot.png)

---

## 🧑‍💻 Author

Abdul Aziz — [LinkedIn](https://www.linkedin.com/)  
© 2025. MIT License.
