#!/bin/bash
echo "Monitoring CDN after origin host header fix..."
echo "This usually takes 5-15 minutes to propagate"
echo ""
start_time=$(date +%s)
while true; do
    current_time=$(date +%s)
    elapsed=$((current_time - start_time))
    minutes=$((elapsed / 60))
    
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://do-git-mis-next-dev-docs.azureedge.net/documents/index.html")
    echo "$(date +"%H:%M:%S") [${minutes}m elapsed] - CDN Status: $STATUS"
    
    if [ "$STATUS" = "200" ]; then
        echo "✅ CDN is working! Origin host header fix successful."
        break
    fi
    
    if [ $elapsed -gt 900 ]; then  # 15 minutes
        echo "⚠️  Still not working after 15 minutes. May need additional troubleshooting."
        break
    fi
    
    sleep 30
done
