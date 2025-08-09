#!/bin/bash

# CDN Purge Script
# This script helps purge CDN cache after updating documents

set -e

echo "=========================================="
echo "CDN Cache Purge Script"
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to show usage
show_usage() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -g, --resource-group    Resource group name (required)"
    echo "  -p, --profile          CDN profile name (required)"
    echo "  -e, --endpoint         CDN endpoint name (required)"
    echo "  -f, --file             Specific file to purge (optional, can be used multiple times)"
    echo "  -a, --all              Purge all content (use with caution)"
    echo "  -h, --help             Show this help message"
    echo ""
    echo "Examples:"
    echo "  # Purge specific files"
    echo "  $0 -g mygroup -p mycdn -e myendpoint -f /documents/doc1.pdf -f /documents/doc2.pdf"
    echo ""
    echo "  # Purge all content"
    echo "  $0 -g mygroup -p mycdn -e myendpoint --all"
    echo ""
    echo "  # Interactive mode (will prompt for values)"
    echo "  $0"
}

# Parse command line arguments
RESOURCE_GROUP=""
CDN_PROFILE=""
CDN_ENDPOINT=""
FILES_TO_PURGE=()
PURGE_ALL=false

while [[ $# -gt 0 ]]; do
    case $1 in
        -g|--resource-group)
            RESOURCE_GROUP="$2"
            shift 2
            ;;
        -p|--profile)
            CDN_PROFILE="$2"
            shift 2
            ;;
        -e|--endpoint)
            CDN_ENDPOINT="$2"
            shift 2
            ;;
        -f|--file)
            FILES_TO_PURGE+=("$2")
            shift 2
            ;;
        -a|--all)
            PURGE_ALL=true
            shift
            ;;
        -h|--help)
            show_usage
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            show_usage
            exit 1
            ;;
    esac
done

# Interactive mode if no arguments provided
if [ -z "$RESOURCE_GROUP" ] || [ -z "$CDN_PROFILE" ] || [ -z "$CDN_ENDPOINT" ]; then
    echo -e "${YELLOW}Enter CDN information:${NC}"
    
    if [ -z "$RESOURCE_GROUP" ]; then
        read -p "Resource Group Name: " RESOURCE_GROUP
    fi
    
    if [ -z "$CDN_PROFILE" ]; then
        read -p "CDN Profile Name: " CDN_PROFILE
    fi
    
    if [ -z "$CDN_ENDPOINT" ]; then
        read -p "CDN Endpoint Name: " CDN_ENDPOINT
    fi
    
    # Ask what to purge
    echo ""
    echo "What would you like to purge?"
    echo "1) Specific files"
    echo "2) All content"
    read -p "Choose (1 or 2): " PURGE_CHOICE
    
    if [ "$PURGE_CHOICE" = "1" ]; then
        echo ""
        echo "Enter file paths to purge (one per line, press Ctrl+D when done):"
        echo "Example: /documents/myfile.pdf"
        while IFS= read -r line; do
            [ -n "$line" ] && FILES_TO_PURGE+=("$line")
        done
    elif [ "$PURGE_CHOICE" = "2" ]; then
        PURGE_ALL=true
    else
        echo -e "${RED}Invalid choice${NC}"
        exit 1
    fi
fi

# Verify CDN endpoint exists
echo -e "\n${YELLOW}Verifying CDN endpoint...${NC}"
if az cdn endpoint show \
    --resource-group "$RESOURCE_GROUP" \
    --profile-name "$CDN_PROFILE" \
    --name "$CDN_ENDPOINT" >/dev/null 2>&1; then
    echo -e "${GREEN}✓ CDN endpoint found${NC}"
else
    echo -e "${RED}✗ CDN endpoint not found${NC}"
    exit 1
fi

# Prepare content paths
if [ "$PURGE_ALL" = true ]; then
    CONTENT_PATHS="/*"
    echo -e "\n${YELLOW}WARNING: Purging ALL content from CDN cache${NC}"
    read -p "Are you sure? (yes/no): " CONFIRM
    if [ "$CONFIRM" != "yes" ]; then
        echo "Purge cancelled"
        exit 0
    fi
elif [ ${#FILES_TO_PURGE[@]} -eq 0 ]; then
    echo -e "${RED}No files specified to purge${NC}"
    exit 1
else
    # Build content paths string
    CONTENT_PATHS=""
    for file in "${FILES_TO_PURGE[@]}"; do
        CONTENT_PATHS+="\"$file\" "
    done
fi

# Execute purge
echo -e "\n${YELLOW}Executing CDN purge...${NC}"
echo "Resource Group: $RESOURCE_GROUP"
echo "CDN Profile: $CDN_PROFILE"
echo "CDN Endpoint: $CDN_ENDPOINT"

if [ "$PURGE_ALL" = true ]; then
    echo "Content Paths: /* (all content)"
    
    if az cdn endpoint purge \
        --resource-group "$RESOURCE_GROUP" \
        --profile-name "$CDN_PROFILE" \
        --name "$CDN_ENDPOINT" \
        --content-paths "/*" \
        --no-wait; then
        echo -e "${GREEN}✓ Purge initiated successfully${NC}"
    else
        echo -e "${RED}✗ Purge failed${NC}"
        exit 1
    fi
else
    echo "Content Paths:"
    for file in "${FILES_TO_PURGE[@]}"; do
        echo "  - $file"
    done
    
    # Execute purge with specific files
    if az cdn endpoint purge \
        --resource-group "$RESOURCE_GROUP" \
        --profile-name "$CDN_PROFILE" \
        --name "$CDN_ENDPOINT" \
        --content-paths ${FILES_TO_PURGE[@]} \
        --no-wait; then
        echo -e "${GREEN}✓ Purge initiated successfully${NC}"
    else
        echo -e "${RED}✗ Purge failed${NC}"
        exit 1
    fi
fi

echo -e "\n${YELLOW}Note:${NC}"
echo "- Purge operations are asynchronous and may take a few minutes"
echo "- Check purge status with:"
echo "  az cdn endpoint show --resource-group $RESOURCE_GROUP --profile-name $CDN_PROFILE --name $CDN_ENDPOINT --query provisioningState"
echo ""
echo "=========================================="
echo -e "${GREEN}Purge request submitted!${NC}"
echo "==========================================" 