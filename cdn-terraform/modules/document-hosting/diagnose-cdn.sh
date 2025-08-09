#!/bin/bash

echo "CDN Diagnostic Report"
echo "===================="
echo "Generated at: $(date)"
echo ""

# Configuration
STORAGE_ACCOUNT="dogitmisnextdevdocs"
STORAGE_HOST="${STORAGE_ACCOUNT}.blob.core.windows.net"
CDN_ENDPOINT="do-git-mis-next-dev-docs.azureedge.net"
CONTAINER="documents"
TEST_FILE="index.html"

echo "1. DNS Resolution Tests"
echo "-----------------------"
echo "Storage account DNS resolution:"
nslookup $STORAGE_HOST || echo "Failed to resolve storage host"
echo ""
echo "CDN endpoint DNS resolution:"
nslookup $CDN_ENDPOINT || echo "Failed to resolve CDN endpoint"
echo ""

echo "2. Direct Storage Access Test"
echo "-----------------------------"
STORAGE_URL="https://${STORAGE_HOST}/${CONTAINER}/${TEST_FILE}"
echo "Testing: $STORAGE_URL"
curl -I -s --connect-timeout 10 "$STORAGE_URL" | head -10
echo ""

echo "3. CDN Access Tests"
echo "-------------------"
# Test with container in path
CDN_URL1="https://${CDN_ENDPOINT}/${CONTAINER}/${TEST_FILE}"
echo "Testing CDN with container path: $CDN_URL1"
curl -I -s --connect-timeout 10 "$CDN_URL1" | head -10
echo ""

# Test without container in path
CDN_URL2="https://${CDN_ENDPOINT}/${TEST_FILE}"
echo "Testing CDN root path: $CDN_URL2"
curl -I -s --connect-timeout 10 "$CDN_URL2" | head -10
echo ""

echo "4. CDN Connectivity Test"
echo "------------------------"
echo "Testing TCP connection to CDN on port 443:"
timeout 5 bash -c "echo > /dev/tcp/${CDN_ENDPOINT}/443" && echo "✓ TCP connection successful" || echo "✗ TCP connection failed"
echo ""

echo "5. SSL Certificate Check"
echo "------------------------"
echo "CDN SSL certificate:"
echo | openssl s_client -servername $CDN_ENDPOINT -connect $CDN_ENDPOINT:443 2>/dev/null | openssl x509 -noout -subject -dates 2>/dev/null || echo "Failed to retrieve certificate"
echo ""

echo "6. Traceroute to CDN"
echo "--------------------"
echo "First 10 hops to CDN endpoint:"
traceroute -m 10 -w 2 $CDN_ENDPOINT 2>/dev/null || echo "Traceroute not available"
echo ""

echo "7. Azure CDN Propagation Check"
echo "------------------------------"
echo "Note: New CDN endpoints can take up to 90 minutes to propagate globally."
echo "If the CDN was recently created, please wait for propagation to complete."
echo ""

echo "8. Recommendations"
echo "------------------"
if curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "$STORAGE_URL" | grep -q "200"; then
    echo "✓ Storage account is accessible"
else
    echo "✗ Storage account is not accessible - check network rules and container access level"
fi

if timeout 5 bash -c "echo > /dev/tcp/${CDN_ENDPOINT}/443" 2>/dev/null; then
    echo "✓ CDN endpoint is reachable"
    echo "  - If getting 404: Check origin configuration and path settings"
    echo "  - If getting timeout: CDN might still be propagating"
else
    echo "✗ CDN endpoint is not reachable"
    echo "  - CDN might still be propagating (can take up to 90 minutes)"
    echo "  - Check if CDN endpoint is properly deployed in Azure"
fi

echo ""
echo "Run this script again in 15-30 minutes if CDN is not working yet." 