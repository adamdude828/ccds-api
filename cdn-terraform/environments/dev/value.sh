#!/bin/bash
cd /Users/adamholsinger/challenger-ccds/ccds-api/cdn-terraform/environments/dev
DOMAIN="documents.example.com"

RG=$(terraform output -raw resource_group_name)
HOST=$(terraform output -raw cdn_endpoint_hostname)

PROFILE=""
for p in $(az afd profile list -g "$RG" --query "[].name" -o tsv); do
  if [ "$(az afd endpoint list -g "$RG" --profile-name "$p" --query "[?hostName=='$HOST'] | length(@)" -o tsv)" = "1" ]; then
    PROFILE="$p"; break
  fi
done

az afd custom-domain list -g "$RG" --profile-name "$PROFILE" \
  --query "[?hostName=='$DOMAIN'].validationProperties.validationToken | [0]" -o tsv
