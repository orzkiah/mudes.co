import axios, { type InternalAxiosRequestConfig } from "axios";

export const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1";
export const API_ORIGIN = new URL(API_URL).origin;

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
  baseURL: API_ORIGIN,
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
