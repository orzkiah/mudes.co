import { QueryClient } from "@tanstack/react-query";
import { AxiosError } from "axios";
import type { ApiError, ApiResponse } from "@/lib/api-types";


function isApiError(error: unknown): error is AxiosError<ApiError> {
  return (
    error instanceof AxiosError &&
    error.response?.data !== null &&
    typeof error.response?.data === "object" &&
    "success" in error.response.data
  );
}

export function getApiError(error: unknown): ApiError | null {
  if (error instanceof AxiosError && error.response?.data) {
    const data = error.response.data as ApiResponse<unknown>;
    if (data.errors) return data.errors;
  }
  return null;
}

export function getApiErrorMessage(error: unknown): string {
  if (error instanceof AxiosError && error.response?.data) {
    const data = error.response.data as ApiResponse<unknown>;

    // Check field validation errors (e.g. 422 Unprocessable Entity)
    const rawErrors = data.errors?.fields || data.errors;
    if (rawErrors && typeof rawErrors === "object" && Object.keys(rawErrors).length > 0) {
      const fieldMessages = Object.values(rawErrors)
        .map((msgs) => (Array.isArray(msgs) ? msgs.join(", ") : typeof msgs === "string" ? msgs : ""))
        .filter(Boolean);
      if (fieldMessages.length > 0) {
        return fieldMessages.join(" | ");
      }
    }

    if (data.message) return data.message;
  }

  if (error instanceof Error) return error.message;
  return "Terjadi kesalahan pada server.";
}

export function getValidationErrors(error: unknown): Record<string, string> {
  const apiError = getApiError(error);
  if (!apiError?.fields) return {};
  return Object.fromEntries(
    Object.entries(apiError.fields).map(([key, messages]) => [
      key,
      Array.isArray(messages) ? messages.join(" ") : messages,
    ])
  );
}


export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      staleTime: 60_000,
      retry: (_failureCount, error) => {
        const apiError = getApiError(error);
        if (apiError?.status === 401 || apiError?.status === 403) return false;
        return false;
      },
    },
  },
});
