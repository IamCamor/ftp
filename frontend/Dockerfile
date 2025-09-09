# Use Node.js 18 Alpine for smaller image size
FROM node:18-alpine

# Install su-exec for user switching
RUN apk add --no-cache su-exec

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production

# Copy source code
COPY . .

# Build the application
RUN npm run build

# Create non-root user
RUN adduser -D -s /bin/sh appuser

# Change ownership
RUN chown -R appuser:appuser /app

# Switch to non-root user
USER appuser

# Expose port
EXPOSE 5173

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:5173 || exit 1

# Start the application
CMD ["./docker-start.sh", "npm", "run", "preview"]
