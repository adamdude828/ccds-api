# Docker Setup for Next.js Application

This document provides instructions for building and running the Next.js application using Docker.

## Prerequisites

- Docker installed on your machine
- Docker Compose (optional, but recommended)

## Building and Running with Docker

### Using Docker Directly

1. Build the Docker image:
   ```bash
   docker build -t nextjs-app .
   ```

2. Run the container:
   ```bash
   docker run -p 3000:3000 \
     -e NEXT_PUBLIC_API_URL=your_api_url \
     -e NEXT_PUBLIC_AZURE_CLIENT_ID=your_client_id \
     -e NEXT_PUBLIC_AZURE_TENANT_ID=your_tenant_id \
     nextjs-app
   ```

### Building in Azure Pipelines

When building the Docker image in Azure Pipelines, environment variables that are set in the pipeline environment will be available to the build process. Here's an example of how to build your Docker image in an Azure Pipeline:

```yaml
# Example Azure Pipeline YAML snippet
steps:
- task: Docker@2
  displayName: 'Build Docker image'
  inputs:
    command: build
    dockerfile: '$(Build.SourcesDirectory)/Dockerfile'
    repository: 'nextjs-app'
    tags: |
      $(Build.BuildId)
      latest
    buildContext: '$(Build.SourcesDirectory)'
```

If your environment variables (like NEXT_PUBLIC_API_URL, NEXT_PUBLIC_AZURE_CLIENT_ID, etc.) are already defined in your Azure Pipeline, you don't need to explicitly pass them during the build. They will be automatically available in the build environment.

However, if you need to override specific variables or use different values for the build, you can pass them as build arguments:

```yaml
# Example with explicit build arguments
steps:
- task: Docker@2
  displayName: 'Build Docker image'
  inputs:
    command: build
    dockerfile: '$(Build.SourcesDirectory)/Dockerfile'
    repository: 'nextjs-app'
    buildContext: '$(Build.SourcesDirectory)'
    arguments: >
      --build-arg NEXT_PUBLIC_API_URL=$(NEXT_PUBLIC_API_URL)
      --build-arg NEXT_PUBLIC_AZURE_CLIENT_ID=$(NEXT_PUBLIC_AZURE_CLIENT_ID)
      --build-arg NEXT_PUBLIC_AZURE_TENANT_ID=$(NEXT_PUBLIC_AZURE_TENANT_ID)
```

Note: For this approach to work with build arguments, you'll need to modify your Dockerfile to accept these arguments using the ARG instruction.

