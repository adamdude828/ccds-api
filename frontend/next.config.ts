import type { NextConfig } from "next";

// Extract domain from API URL for image configuration
const getApiDomain = () => {
  // Ensure process and process.env exist before accessing
  if (typeof process === 'undefined' || !process.env) {
    return '';
  }
  
  const apiUrl = process.env.NEXT_PUBLIC_API_URL || '';
  if (!apiUrl) {
    return '';
  }
  
  try {
    // Extract domain from URL
    const url = new URL(apiUrl);
    return url.hostname;
  } catch {
    return '';
  }
};

const nextConfig: NextConfig = {
  /* config options here */
  output: 'standalone',
  images: {
    domains: [getApiDomain()].filter(Boolean),
  },
};

export default nextConfig;