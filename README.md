
# WordPress Get-JWT-Token Plugin

Adds the functionality to generate and verify JWT tokens in WordPress.

⚠️ **Attention**: Using this plugin might introduce security risks if not implemented carefully. [See class comments for details](https://github.com/fasihi01/wordpress_get-jwt-token/blob/a028048db8674b069ba38694fe1eea072428787d/get-jwt-token-api.php#L4).

---

## Installation

### Step 1: Copy the Plugin File
Copy the `get-jwt-token-api.php` file to the following directory on your WordPress server:
```
/var/www/html/wp-content/plugins/get-jwt-token-api
```

---

### Step 2: Generate a Secret Key
1. Generate a secure secret key (e.g., a 256-bit key).
2. Add the key to your `wp-config.php` file:
    ```php
    define('JWT_AUTH_SECRET_KEY', 'your-256-bit-secret');
    ```

---

### Step 3: Activate the Plugin
1. In your WordPress admin panel, navigate to **Plugins**.
2. Find the "Get JWT Token" plugin and activate it.

---

### Step 4: Install the `firebase/php-jwt` Library
1. Ensure Composer is installed on your server. If not, install it:
    ```bash
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    ```

2. Install the `firebase/php-jwt` library:
    ```bash
    cd /var/www/html
    composer require firebase/php-jwt
    ```

---

## Usage

### Fetch a JWT Token
To fetch a token, run the following code in your browser's developer console (**F12**) while logged into WordPress:

```javascript
fetch('https://example.com/wp-json/custom/v1/token', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.wpRestNonce, //todo: this may be a security issue, the value is set here https://github.com/fasihi01/wordpress_get-jwt-token/blob/ff697999b4682b4555a92426a95664954ca96be9/get-jwt-token-api.php#L40
    },
    credentials: 'include'
})
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            console.log('JWT Token:', data.token);
        } else {
            console.error('Error:', data);
        }
    })
    .catch(error => console.error('Fetch error:', error));
```

---

### Verify a JWT Token
Token verification is primarily for **server-to-server communication**. While it can be tested in the browser, it is not intended for frontend use.

Example fetch request to verify a token:

```javascript
const jwtToken = "eyJ0....HE";

fetch("https://example.com/wp-json/custom/v1/verify", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
    },
    body: JSON.stringify({
        token: jwtToken,
    }),
})
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log("Token is valid:", data.data);
        } else {
            console.error("Token verification failed:", data);
        }
    })
    .catch(error => {
        console.error("Fetch error:", error);
    });
```

---

### Notes
- The `X-WP-Nonce` header is required to ensure the request originates from a logged-in user. The wpApiSettings object "disappears" after some minutes from the browser-window scope. Be sure to have fetched the JWT by then.
- Tokens are signed using the secret key defined in `wp-config.php`. Keep this key secure.
- Avoid exposing token verification endpoints directly to untrusted environments (e.g., browsers).

---

## Security Considerations
1. Ensure the `JWT_AUTH_SECRET_KEY` is strong and never exposed publicly.
2. If roles or permissions change frequently, implement a token revocation mechanism (e.g., a blacklist).
3. Always use HTTPS to prevent token interception.

---

Let me know if additional details or adjustments are needed! 🚀
