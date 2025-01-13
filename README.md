# wordpress_get-jwt-token
Adds the get-jwt-token functionality

Attention: Using this may be a bad idea. [See class comments](https://github.com/fasihi01/wordpress_get-jwt-token/blob/a028048db8674b069ba38694fe1eea072428787d/get-jwt-token-api.php#L4)

Installation:

1. copy get-jwt-token-api.php to /var/www/html/wp-content/plugins/get-jwt-token-api
2. generate your-256-bit-secret somewhere and in /var/www/html/wp-config.php add at bottom:
define('JWT_AUTH_SECRET_KEY', 'your-256-bit-secret')


3. in wordpress-plugins, find and activate the plugin

4. make sure that firebase/php-jwt is installed, if not:

if no composer is installed go and get it. It may still be:
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

then to install the firebase/php-jwt:
cd /var/www/html
composer require firebase/php-jwt


5. In order to fetch the token do something like this in your logged-in-to-wordpress-browserwindow's F12:
/** fetch **/
fetch('https://example.com/wp-json/custom/v1/token', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce,
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



6. In order to verify the token, derive the server-to-server-communication from this test-fetch (it makes not much sense to do this in the browser except for testing):
/** verify **/
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

