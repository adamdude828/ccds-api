const API_BASE = process.env.NEXT_PUBLIC_API_URL;

export async function getTestToken(): Promise<string> {
  const response = await fetch(`${API_BASE}/api/test-auth-token`);
  if (!response.ok) {
    throw new Error('Failed to get test token');
  }
  const data = await response.json();
  return data.access_token;
}

export async function makeAuthenticatedRequest(
  path: string,
  options: RequestInit = {}
): Promise<Response> {
  const token = await getTestToken();
  
  return fetch(`${API_BASE}${path}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...options.headers,
    },
  });
} 