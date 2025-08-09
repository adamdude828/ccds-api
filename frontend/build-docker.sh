#!/bin/bash

# Read environment variables from .env.local
export $(grep -v '^#' .env.local | xargs)

echo "Building Docker image with environment variables from .env.local..."
echo "NEXT_PUBLIC_API_URL=$NEXT_PUBLIC_API_URL"
echo "NEXT_PUBLIC_AZURE_CLIENT_ID=$NEXT_PUBLIC_AZURE_CLIENT_ID"
echo "NEXT_PUBLIC_AZURE_TENANT_ID=$NEXT_PUBLIC_AZURE_TENANT_ID"
echo "NEXT_PUBLIC_APP_URL=$NEXT_PUBLIC_APP_URL"

# Build the Docker image
docker build \
  --build-arg NEXT_PUBLIC_API_URL=$NEXT_PUBLIC_API_URL \
  --build-arg NEXT_PUBLIC_AZURE_CLIENT_ID=$NEXT_PUBLIC_AZURE_CLIENT_ID \
  --build-arg NEXT_PUBLIC_AZURE_TENANT_ID=$NEXT_PUBLIC_AZURE_TENANT_ID \
  --build-arg NEXT_PUBLIC_APP_URL=$NEXT_PUBLIC_APP_URL \
  -t do-git-mkt-reviews-next-js .

echo "Docker image built successfully."
echo "Starting container..."

# Check if container already exists and remove it
if [ "$(docker ps -a -q -f name=do-git-mkt-reviews-next-js)" ]; then
  echo "Removing existing container..."
  docker rm -f do-git-mkt-reviews-next-js
fi

# Run the Docker container
docker run -d -p 3000:3000 \
  -e NEXT_PUBLIC_API_URL=$NEXT_PUBLIC_API_URL \
  -e NEXT_PUBLIC_AZURE_CLIENT_ID=$NEXT_PUBLIC_AZURE_CLIENT_ID \
  -e NEXT_PUBLIC_AZURE_TENANT_ID=$NEXT_PUBLIC_AZURE_TENANT_ID \
  -e NEXT_PUBLIC_APP_URL=$NEXT_PUBLIC_APP_URL \
  --name do-git-mkt-reviews-next-js \
  do-git-mkt-reviews-next-js

echo "Container started. You can access the application at http://localhost:3000" 