import axios, { type InternalAxiosRequestConfig } from "axios";

// In production, use relative URLs so requests go through Next.js rewrites (same domain = cookies work).
// In development, hit the Laravel backend directly.
const isServer = typeof window === "undefined";
const isDev = process.env.NODE_ENV === "development";

const BASE_API_URL = isDev
  ? (process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1")
  : "/api/v1";

const BASE_ORIGIN = isDev
  ? new URL(process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000").origin
  : "";

export const API_URL = BASE_API_URL;
export const API_ORIGIN = BASE_ORIGIN;

export const api = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  xsrfCookieName: "XSRF-TOKEN",
  xsrfHeaderName: "X-XSRF-TOKEN",
  withXSRFToken: true,
});

// ─── SET DEFAULT HEADERS MANUALLY ────────────────────────────────────────────
api.defaults.headers.common["Accept"] = "application/json";
api.defaults.headers.post["Content-Type"] = "application/json";
api.defaults.headers.put["Content-Type"] = "application/json";
api.defaults.headers.patch["Content-Type"] = "application/json";

// ─── REQUEST INTERCEPTOR ─────────────────────────────────────────────────────
// FormData uploads: remove Content-Type so browser sets multipart boundary.
api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  if (config.data instanceof FormData) {
    delete config.headers["Content-Type"];
    delete config.headers["content-type"];
    delete config.headers.post?.["Content-Type"];
    delete config.headers.common?.["Content-Type"];
  }
  return config;
});

// ─── SANCTUM API (for CSRF cookie endpoint only) ─────────────────────────────
const sanctumApi = axios.create({
  baseURL: isDev ? (process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000").replace(/\/api\/v1$/, "") : "",
  withCredentials: true,
  xsrfCookieName: "XSRF-TOKEN",
  xsrfHeaderName: "X-XSRF-TOKEN",
  withXSRFToken: true,
});

export async function ensureCsrfCookie() {
  await sanctumApi.get("/sanctum/csrf-cookie");
}

/**
 * Refresh the CSRF cookie from Sanctum and return the token value.
 * Must be called before any state-mutating request (POST/PUT/PATCH/DELETE)
 * when the XSRF-TOKEN cookie may have expired or is not available.
 */
export async function refreshCsrfToken(): Promise<void> {
  await sanctumApi.get("/sanctum/csrf-cookie");
}
