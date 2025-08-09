#!/bin/bash

# Simple AFD Test Script
# This script helps test the Storage + Azure Front Door setup

set -e

echo "=========================================="
echo "Storage + Azure Front Door Test Script"
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check for required tools
if ! command_exists az; then
    echo -e "${RED}Error: Azure CLI (az) is not installed${NC}"
    exit 1
fi

if ! command_exists curl; then
    echo -e "${RED}Error: curl is not installed${NC}"
    exit 1
fi

# Get deployment information
echo -e "\n${YELLOW}Enter the following information:${NC}"
read -p "Storage Account Name: " STORAGE_ACCOUNT
read -p "Resource Group Name: " RESOURCE_GROUP
read -p "Front Door Endpoint Name: " FD_ENDPOINT
read -p "Front Door Profile Name: " FD_PROFILE

# Test 1: Check Storage Account
echo -e "\n${YELLOW}1. Checking Storage Account...${NC}"
if az storage account show --name "$STORAGE_ACCOUNT" --resource-group "$RESOURCE_GROUP" >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Storage Account exists${NC}"
    
    # Get storage account key
    STORAGE_KEY=$(az storage account keys list --account-name "$STORAGE_ACCOUNT" --resource-group "$RESOURCE_GROUP" --query '[0].value' -o tsv)
    
    # Check if documents container exists
    if az storage container show --name "documents" --account-name "$STORAGE_ACCOUNT" --account-key "$STORAGE_KEY" >/dev/null 2>&1; then
        echo -e "${GREEN}✓ Documents container exists${NC}"
    else
        echo -e "${RED}✗ Documents container not found${NC}"
    fi
else
    echo -e "${RED}✗ Storage Account not found${NC}"
    exit 1
fi

# Test 2: Upload a test file
echo -e "\n${YELLOW}2. Uploading test file...${NC}"
TEST_FILE="test-document-$(date +%s).txt"
echo "This is a test document uploaded at $(date)" > "/tmp/$TEST_FILE"

if az storage blob upload \
    --file "/tmp/$TEST_FILE" \
    --container-name "documents" \
    --name "$TEST_FILE" \
    --account-name "$STORAGE_ACCOUNT" \
    --account-key "$STORAGE_KEY" \
    --overwrite >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Test file uploaded successfully${NC}"
else
    echo -e "${RED}✗ Failed to upload test file${NC}"
    exit 1
fi

# Test 3: Check Front Door Endpoint
echo -e "\n${YELLOW}3. Checking Front Door Endpoint...${NC}"
FD_HOSTNAME=$(az afd endpoint show \
    --endpoint-name "$FD_ENDPOINT" \
    --profile-name "$FD_PROFILE" \
    --resource-group "$RESOURCE_GROUP" \
    --query "hostName" -o tsv 2>/dev/null)

if [ -n "$FD_HOSTNAME" ]; then
    echo -e "${GREEN}✓ Front Door Endpoint exists${NC}"
    echo "  Endpoint Hostname: $FD_HOSTNAME"
else
    echo -e "${RED}✗ Front Door Endpoint not found${NC}"
    exit 1
fi

# Test 4: Access file via Storage Account
echo -e "\n${YELLOW}4. Testing direct storage access...${NC}"
STORAGE_URL="https://${STORAGE_ACCOUNT}.blob.core.windows.net/documents/${TEST_FILE}"
echo "  Testing URL: $STORAGE_URL"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$STORAGE_URL")
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓ Direct storage access successful (HTTP $HTTP_CODE)${NC}"
else
    echo -e "${RED}✗ Direct storage access failed (HTTP $HTTP_CODE)${NC}"
fi

# Test 5: Access file via Front Door
echo -e "\n${YELLOW}5. Testing Front Door access...${NC}"
FD_URL="https://${FD_HOSTNAME}/documents/${TEST_FILE}"
echo "  Testing URL: $FD_URL"
echo -e "${YELLOW}  Note: Edge propagation may take a few minutes${NC}"

# Try multiple times as AFD might need time to propagate
MAX_ATTEMPTS=20
ATTEMPT=1
SUCCESS=false

while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$FD_URL")
    
    if [ "$HTTP_CODE" = "200" ]; then
        echo -e "${GREEN}✓ Front Door access successful (HTTP $HTTP_CODE)${NC}"
        SUCCESS=true
        break
    elif [ "$HTTP_CODE" = "404" ] && [ $ATTEMPT -lt $MAX_ATTEMPTS ]; then
        echo -e "  Attempt $ATTEMPT/$MAX_ATTEMPTS: HTTP $HTTP_CODE - Waiting 30 seconds..."
        sleep 30
    else
        echo -e "  Attempt $ATTEMPT/$MAX_ATTEMPTS: HTTP $HTTP_CODE"
    fi
    
    ATTEMPT=$((ATTEMPT + 1))
done

if [ "$SUCCESS" = false ]; then
    echo -e "${RED}✗ Front Door access failed after $MAX_ATTEMPTS attempts${NC}"
    echo -e "${YELLOW}  This might be due to propagation delay. Try again in a few minutes.${NC}"
fi

# Test 6: Check Caching Headers
if [ "$SUCCESS" = true ]; then
    echo -e "\n${YELLOW}6. Checking caching headers...${NC}"
    HEADERS=$(curl -s -I "$FD_URL")
    
    if echo "$HEADERS" | grep -i "cache-control" >/dev/null; then
        echo -e "${GREEN}✓ Cache-Control header present${NC}"
        echo "$HEADERS" | grep -i "cache-control"
    fi
    
    if echo "$HEADERS" | grep -i "x-cache" >/dev/null; then
        echo -e "${GREEN}✓ Edge cache header present${NC}"
        echo "$HEADERS" | grep -i "x-cache"
    fi
fi

# Cleanup
echo -e "\n${YELLOW}7. Cleaning up test file...${NC}"
if az storage blob delete \
    --container-name "documents" \
    --name "$TEST_FILE" \
    --account-name "$STORAGE_ACCOUNT" \
    --account-key "$STORAGE_KEY" >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Test file deleted${NC}"
fi

rm -f "/tmp/$TEST_FILE"

echo -e "\n=========================================="
echo -e "${GREEN}Test completed!${NC}"
echo -e "\nSummary:"
echo -e "- Storage Account: ${STORAGE_ACCOUNT}"
echo -e "- AFD Hostname: ${FD_HOSTNAME}"
echo -e "- Document URL Pattern: https://${FD_HOSTNAME}/documents/<filename>"
echo -e "\n${YELLOW}Note: It may take a few minutes for new content to be available via AFD${NC}"
echo "==========================================" 