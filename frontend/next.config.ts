import type { NextConfig } from "next";

const BACKEND_URL = process.env.BACKEND_URL || "http://localhost:8000";

const nextConfig: NextConfig = {
  reactStrictMode: true,
  images: {
    remotePatterns: [
      { protocol: "http", hostname: "localhost", port: "8000" },
      { protocol: "http", hostname: "127.0.0.1", port: "8000" },
      { protocol: "https", hostname: "mudesco-backend.onrender.com" },
    ],
  },
  async rewrites() {
    return [
      // Proxy sanctum CSRF cookie
      {
        source: "/sanctum/:path*",
        destination: `${BACKEND_URL}/sanctum/:path*`,
      },
      // Proxy all API requests
      {
        source: "/api/:path*",
        destination: `${BACKEND_URL}/api/:path*`,
      },
    ];
  },
};

export default nextConfig;
