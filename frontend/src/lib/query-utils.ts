import { api } from "@/lib/api";
import type { ApiResponse, ApiMeta, ListParams, MediaObject } from "@/lib/api-types";

export function buildQueryParams(params: ListParams): Record<string, string | number | undefined> {
  const output: Record<string, string | number | undefined> = {};
  if (params.search) output.search = params.search;
  if (params.sort) output.sort = params.sort;
  if (params.page) output.page = params.page;
  if (params.perPage) output.perPage = params.perPage;
  if (params.filter) {
    Object.entries(params.filter).forEach(([key, value]) => {
      if (value !== undefined && value !== "" && value !== null) {
        output[`filter[${key}]`] = Array.isArray(value) ? value.join(",") : value;
      }
    });
  }
  return output;
}


export async function fetchList<T>(endpoint: string, params: ListParams = {}) {
  const response = await api.get<ApiResponse<T[]>>(endpoint, { params: buildQueryParams(params) });
  return {
    data: response.data.data,
    meta: response.data.meta as ApiMeta,
  };
}

export async function fetchDetail<T>(endpoint: string, id: string) {
  const response = await api.get<ApiResponse<T>>(`${endpoint}/${id}`);
  return response.data.data;
}

export async function createItem<T>(endpoint: string, payload: unknown) {
  const response = await api.post<ApiResponse<T>>(endpoint, payload);
  return response.data.data;
}

export async function updateItem<T>(endpoint: string, id: string, payload: unknown) {
  const response = await api.put<ApiResponse<T>>(`${endpoint}/${id}`, payload);
  return response.data.data;
}

export async function patchItem<T>(endpoint: string, id: string, payload: unknown) {
  const response = await api.patch<ApiResponse<T>>(`${endpoint}/${id}`, payload);
  return response.data.data;
}

export async function deleteItem(endpoint: string, id: string) {
  const response = await api.delete<ApiResponse<{ id: string }>>(`${endpoint}/${id}`);
  return response.data.data;
}

export async function restoreItem<T>(endpoint: string, id: string) {
  const response = await api.post<ApiResponse<T>>(`${endpoint}/${id}/restore`);
  return response.data.data;
}

export async function bulkAction<T>(endpoint: string, action: string, ids: string[]) {
  const response = await api.post<ApiResponse<T>>(`${endpoint}/bulk-${action}`, { ids });
  return response.data.data;
}

export async function bulkReorder(endpoint: string, order: string[]) {
  const response = await api.put<ApiResponse<unknown>>(`${endpoint}/bulk-reorder`, { order });
  return response.data.data;
}

/**
 * Uploads a single file to POST /admin/media.
 *
 * Uses the existing `api` Axios instance so CSRF / Sanctum cookies are sent
 * automatically. Content-Type is set to multipart/form-data by Axios when the
 * body is a FormData object — the default "application/json" header must be
 * dropped for this request only.
 *
 * @param file       The File object selected by the user.
 * @param collection One of the five valid collection names defined in
 *                   UploadMediaRequest (backend): member-photo, article-cover,
 *                   activity-cover, gallery-photo, library-file.
 * @returns          The persisted MediaObject whose `id` should be attached to
 *                   the entity form field (e.g. photoMediaId, coverMediaId).
 */
export async function uploadMedia(file: File, collection: string): Promise<MediaObject> {
  const formData = new FormData();
  formData.append("file", file);
  formData.append("collection", collection);

  const response = await api.post<ApiResponse<MediaObject>>("/admin/media", formData);

  return response.data.data;
}
