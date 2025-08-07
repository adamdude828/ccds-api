# Azure Storage SAS Token Generation Guide

This guide explains how to generate Shared Access Signature (SAS) tokens for Azure Storage using a shared account key. This process is consistent across programming languages.

## Core Concepts

1. **Components Required**:
   - Storage Account Name
   - Storage Account Key (base64 encoded string)
   - Container or Blob name
   - Permissions to grant
   - Expiration time

2. **Key Security Considerations**:
   - Always store account keys securely
   - Use HTTPS for all operations
   - Set appropriate expiration times
   - Use minimum required permissions
   - Consider IP restrictions when needed

## Step-by-Step Process

### 1. Initialize Credentials

The account key must be stored as a base64-decoded value:
```python
# Python example, but concept applies to all languages
account_name = "your_account_name"
account_key = base64_decode("your_account_key")
```

### 2. Create String to Sign

The string to sign must be constructed in this exact order, with newlines (\n) between each component:
```
stringToSign = [
    permissions,                    # e.g., "racwd" for Read, Add, Create, Write, Delete
    start_time,                    # ISO8601 format, e.g., "2023-01-01T00:00:00Z"
    expiry_time,                   # ISO8601 format, e.g., "2024-01-01T00:00:00Z"
    canonical_resource,            # "/blob/account/container/blob"
    identifier,                    # Empty string if not using stored access policy
    ip_range,                      # Empty string or IP range
    protocol,                      # "https" or "https,http"
    version,                       # e.g., "2020-12-06"
    resource,                      # "c" for container, "b" for blob
    snapshot_time,                 # Empty string or snapshot timestamp
    encryption_scope,              # Empty string or encryption scope
    cache_control,                 # Empty string or cache control
    content_disposition,           # Empty string or content disposition
    content_encoding,              # Empty string or content encoding
    content_language,              # Empty string or content language
    content_type                   # Empty string or content type
].join("\n")
```

### 3. Generate HMAC-SHA256 Signature

Using the account key, generate an HMAC-SHA256 signature:
```python
# Pseudocode - adapt to your language
signature = hmac_sha256(
    key=account_key,
    message=string_to_sign,
    encoding="utf-8"
).base64_encode()
```

### 4. Construct SAS Token

Combine the components into a URL-safe query string:
```
sv={version}
&ss={services}              # Optional - for account SAS
&srt={resource_types}      # Optional - for account SAS
&sp={permissions}
&st={start_time}           # Optional
&se={expiry_time}
&spr={protocol}            # Optional
&sip={ip_range}            # Optional
&skt={encryption_scope}    # Optional
&sig={signature}
```

## Example Implementation Template

Here's a template that can be adapted to any programming language:

```python
def generate_blob_sas_token(
    account_name: str,
    account_key: str,
    container_name: str,
    blob_name: str = None,
    permissions: str = "r",
    expiry_hours: int = 24
):
    # 1. Setup parameters
    start_time = current_utc_time()
    expiry_time = start_time + hours(expiry_hours)
    
    # 2. Create canonical resource string
    resource_path = f"/blob/{account_name}/{container_name}"
    if blob_name:
        resource_path += f"/{blob_name}"
        resource_type = "b"  # blob
    else:
        resource_type = "c"  # container
    
    # 3. Create string to sign
    string_to_sign = "\n".join([
        permissions,
        format_iso8601(start_time),
        format_iso8601(expiry_time),
        resource_path,
        "",  # identifier
        "",  # ip range
        "https",  # protocol
        "2020-12-06",  # version
        resource_type,
        "",  # snapshot time
        "",  # encryption scope
        "",  # cache control
        "",  # content disposition
        "",  # content encoding
        "",  # content language
        ""   # content type
    ])
    
    # 4. Generate signature
    decoded_key = base64_decode(account_key)
    signature = hmac_sha256(decoded_key, string_to_sign)
    encoded_signature = base64_encode(signature)
    
    # 5. Construct SAS token
    sas_params = {
        "sv": "2020-12-06",
        "sp": permissions,
        "st": format_iso8601(start_time),
        "se": format_iso8601(expiry_time),
        "spr": "https",
        "sr": resource_type,
        "sig": encoded_signature
    }
    
    return url_encode_parameters(sas_params)
```

## Common Permissions

- Container permissions: "racwdl" (Read, Add, Create, Write, Delete, List)
- Blob permissions: "racwd" (Read, Add, Create, Write, Delete)

## API Versions

The implementation supports multiple API versions with different features:
- 2020-12-06: Latest version, supports encryption scope
- 2020-02-10: Adds support for user delegation SAS features
- 2019-12-12: Adds support for blob tags permission
- 2019-10-10: Adds support for version IDs
- 2018-11-09: Adds support for user delegation SAS
- 2015-04-05: Base version with core functionality

## Usage Example

```python
# Generate a SAS token for a container
sas_token = generate_blob_sas_token(
    account_name="mystorageaccount",
    account_key="base64encodedkey==",
    container_name="mycontainer",
    permissions="racwd",
    expiry_hours=48
)

# Use the SAS token in a URL
container_url = f"https://{account_name}.blob.core.windows.net/{container_name}?{sas_token}"
```

## Best Practices

1. **Security**:
   - Never expose account keys in client-side code
   - Generate SAS tokens server-side only
   - Use the shortest possible expiration time
   - Use HTTPS only when possible
   - Consider using IP restrictions for additional security

2. **Performance**:
   - Cache SAS tokens when appropriate
   - Generate new tokens before expiration to ensure continuity
   - Use container-level SAS instead of individual blob SAS when possible

3. **Monitoring**:
   - Log SAS token generation
   - Monitor SAS token usage through storage analytics
   - Track token expiration to prevent access issues

4. **Error Handling**:
   - Validate all input parameters
   - Handle base64 encoding/decoding errors
   - Implement proper error handling for expired tokens
   - Include retry logic for token generation 
