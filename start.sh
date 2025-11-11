#!/bin/bash
set -e

# Create storage link if it doesn't exist (idempotent)
php artisan storage:link || true

# Start queue worker in background
php artisan queue:work --tries=3 --timeout=90 &
QUEUE_PID=$!

# Start Reverb WebSocket server in background
# Use a different port for Reverb (Railway will assign main port to web server)
REVERB_PORT=${REVERB_SERVER_PORT:-8080}
php artisan reverb:start --host=0.0.0.0 --port=$REVERB_PORT &
REVERB_PID=$!

# Function to cleanup background processes on exit
cleanup() {
    echo "Shutting down..."
    kill $QUEUE_PID 2>/dev/null || true
    kill $REVERB_PID 2>/dev/null || true
    exit
}
trap cleanup SIGTERM SIGINT

# Start main web server (foreground - this keeps container alive)
# This is the main process Railway monitors
php artisan serve --host=0.0.0.0 --port=$PORT

