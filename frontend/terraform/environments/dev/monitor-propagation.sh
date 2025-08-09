#!/bin/bash
while true; do
    CDN=$(curl -s -o /dev/null -w "%{http_code}" "https://do-git-mis-next-dev-docs.azureedge.net/documents/index.html")
    FD=$(curl -s -o /dev/null -w "%{http_code}" "https://do-git-mis-next-dev-docs-endpoint-ase9hmenateuc8e8.z03.azurefd.net/docs/index.html")
    echo "$(date +"%H:%M:%S") - CDN: $CDN | Front Door: $FD"
    if [ "$CDN" = "200" ]; then
        echo "✅ CDN is ready! Front Door should work soon."
        break
    fi
    sleep 30
done
