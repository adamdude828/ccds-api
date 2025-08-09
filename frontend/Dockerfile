# Stage 1: Build the application
FROM node:20-alpine AS builder

WORKDIR /app

# Define build arguments
ARG NEXT_PUBLIC_API_URL
ARG NEXT_PUBLIC_AZURE_CLIENT_ID
ARG NEXT_PUBLIC_AZURE_TENANT_ID
ARG NEXT_PUBLIC_APP_URL

# Set environment variables for the build
ENV NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL}
ENV NEXT_PUBLIC_AZURE_CLIENT_ID=${NEXT_PUBLIC_AZURE_CLIENT_ID}
ENV NEXT_PUBLIC_AZURE_TENANT_ID=${NEXT_PUBLIC_AZURE_TENANT_ID}
ENV NEXT_PUBLIC_APP_URL=${NEXT_PUBLIC_APP_URL}

# Copy the entire application at once
COPY . .

# Install dependencies
RUN npm ci

# Build the Next.js application
RUN npm run build

# Stage 2: Production image
FROM node:20-alpine AS runner

WORKDIR /app

# Set to production environment
ENV NODE_ENV production

# Set runtime environment variables
ENV NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL}
ENV NEXT_PUBLIC_AZURE_CLIENT_ID=${NEXT_PUBLIC_AZURE_CLIENT_ID}
ENV NEXT_PUBLIC_AZURE_TENANT_ID=${NEXT_PUBLIC_AZURE_TENANT_ID}
ENV NEXT_PUBLIC_APP_URL=${NEXT_PUBLIC_APP_URL}

# Add a non-root user to run the application
RUN addgroup --system --gid 1001 nodejs && \
    adduser --system --uid 1001 nextjs

# Copy necessary files from the builder stage
COPY --from=builder /app/public ./public
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static

# Set the correct permissions
RUN chown -R nextjs:nodejs /app

# Switch to non-root user
USER nextjs

# Expose the port the app will run on
EXPOSE 3000

# Set the command to start the app
CMD ["node", "server.js"] 