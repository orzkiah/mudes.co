import axios, { type InternalAxiosRequestConfig } from "axios";

export const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1";

// ─── Token helpers ───────────────────────────────────────────────────────────
const TOKEN_KEY = "auth_token";

export function getAuthToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem(TOKEN_KEY);
}

export function setAuthToken(token: string): void {
  if (typeof window !== "undefined") {
    localStorage.setItem(TOKEN_KEY, token);
  }
}

export function clearAuthToken(): void {
  if (typeof window !== "undefined") {
    localStorage.removeItem(TOKEN_KEY);
  }
}

// ─── Axios instance ──────────────────────────────────────────────────────────
export const api = axios.create({
  baseURL: API_URL,
});

// Set default headers
api.defaults.headers.common["Accept"] = "application/json";
api.defaults.headers.post["Content-Type"] = "application/json";
api.defaults.headers.put["Content-Type"] = "application/json";
api.defaults.headers.patch["Content-Type"] = "application/json";

// Request interceptor: attach Bearer token + handle FormData
api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = getAuthToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  if (config.data instanceof FormData) {
    delete config.headers["Content-Type"];
    delete config.headers["content-type"];
    delete config.headers.post?.["Content-Type"];
    delete config.headers.common?.["Content-Type"];
  }
  return config;
});
