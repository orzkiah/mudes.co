export interface ApiError {
  type: string;
  title: string;
  status: number;
  detail: string;
  instance?: string;
  fields?: Record<string, string[]>;
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  meta: Record<string, unknown>;
  errors: ApiError | null;
}

export interface OffsetPaginationMeta {
  strategy: "offset";
  page: number;
  perPage: number;
  total: number;
  lastPage: number;
}

export interface CursorPaginationMeta {
  strategy: "cursor";
  nextCursor: string | null;
  prevCursor: string | null;
}

export interface ApiMeta {
  pagination?: OffsetPaginationMeta;
  [key: string]: unknown;
}

export interface PaginatedList<T> {
  data: T[];
  meta: ApiMeta;
}

export interface ListParams {
  search?: string;
  sort?: string;
  page?: number;
  perPage?: number;
  filter?: Record<string, string | string[] | undefined>;
}

export interface Taxonomy {
  id: string;
  name: string;
  slug: string;
  description: string | null;
  icon: string | null;
  color: string | null;
  displayOrder: number;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface MediaObject {
  id: string;
  url: string;
  /** Display name, e.g. "avatar.jpg" */
  name: string;
  /** Original file name including extension. Present in MediaResource responses;
   *  may be absent in compact entity resource embeds (ArticleResource, etc.). */
  fileName?: string;
  /** MIME type, e.g. "image/jpeg". Present in MediaResource and most entity resources;
   *  may be absent in very compact embeds (GalleryResource.coverPhoto). */
  mimeType?: string;
  /** File size in bytes. Present in MediaResource responses only. */
  size?: number;
  /** Spatie media collection name, e.g. "member-photo". Present in MediaResource only. */
  collection?: string;
  /** ISO datetime. Present in MediaResource responses only. */
  createdAt?: string;
}

export interface AuthUser {
  id: string;
  name: string;
  email: string;
  roles: string[];
  permissions: string[];
}
