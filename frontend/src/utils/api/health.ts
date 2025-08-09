import { NetworkError } from '@/types/errors';

export interface HealthCheckResponse {
  status: boolean;
  timestamp?: string;
}

export async function checkApiHealth(): Promise<HealthCheckResponse> {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL;
  
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout

    const response = await fetch(`${baseUrl}/api/health`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      signal: controller.signal,
    });

    clearTimeout(timeoutId);

    if (response.ok) {
      const data = await response.json();
      return {
        status: true,
        timestamp: data.timestamp,
      };
    }

    // If we get a response but it's not ok, API is up but unhealthy
    return {
      status: false,
    };
  } catch (error) {
    // Network error or timeout - API is down
    if (error instanceof Error) {
      if (error.name === 'AbortError') {
        throw new NetworkError('Health check timed out - server may be unavailable');
      }
      throw new NetworkError(`Health check failed: ${error.message}`);
    }
    throw new NetworkError('Health check failed - unable to connect to server');
  }
}