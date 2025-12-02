# SSL Certificate Directory

This directory contains SSL certificates for HTTPS support.

## Files

- `cert.pem` - SSL certificate
- `key.pem` - Private key (keep secure!)

## Generate Certificates

Run the following command from the project root:

```bash
bash generate-ssl.sh
```

Or manually with OpenSSL:

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout ssl/key.pem \
    -out ssl/cert.pem \
    -subj "/C=ID/ST=Jakarta/L=Jakarta/O=HomEat Development/CN=localhost"
```

## Security Note

⚠️ **These are self-signed certificates for development only!**

For production, use:
- Let's Encrypt (free, automated)
- Commercial SSL certificate
- Cloudflare SSL

## Gitignore

These files are gitignored for security. Never commit private keys to version control!
