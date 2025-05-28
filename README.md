# http_proxy
A simple PHP-based HTTPS-to-HTTP proxy to help securely bridge communication between HTTPS frontend environments (e.g., web apps, mobile apps) and HTTP-only backends or legacy endpoints.

# Use Case
If you have an HTTPS website or app but need to send data to an HTTP-only service (e.g., an old local server or legacy system), this proxy provides the missing HTTPS layer.

# Installation
1. Upload `http_proxy.php` to any HTTPS domain.
2. Edit the `$httpDomain` and `$httpApiPath` in the file to point to your HTTP destination, e.g.
    - $httpDomain: 'www.nocertificate.com'
    - $httpApiPath: '/module/api/request.php'
3. Call it like:

```js
fetch("https://your-secure-domain.com/http_proxy.php?type=sendMessage&param=value")
```
![image](https://github.com/user-attachments/assets/a7afa983-dce5-406d-afb8-8e3bf6e05ca3)

