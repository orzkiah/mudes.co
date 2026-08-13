import type { MediaObject, Taxonomy } from "@/lib/api-types";

export interface Department extends Taxonomy {
  positionCount: number;
}

export interface StudyCategory extends Taxonomy {
  scheduleCount: number;
}

export interface ActivityCategory extends Taxonomy {
  activityCount: number;
}

export interface GalleryCategory extends Taxonomy {
  galleryCount: number;
}

export interface ArticleCategory extends Taxonomy {
  articleCount: number;
}

export interface LibraryCategory extends Taxonomy {
  documentCount: number;
}

export interface TaxonomyResource extends Taxonomy {
  positionCount?: number;
  scheduleCount?: number;
  activityCount?: number;
  galleryCount?: number;
  articleCount?: number;
  documentCount?: number;
}

export interface Member {
  id: string;
  fullName: string;
  gender: "male" | "female" | null;
  birthDate: string | null;
  phone: string | null;
  photo: MediaObject | null;
  position?: { id: string; title: string; displayOrder: number } | null;
  joinDate: string | null;
  status: "active" | "inactive" | "alumni" | "moved_out";
  notes: string | null;
  userId: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface StudySchedule {
  id: string;
  studyCategoryId: string;
  category: StudyCategory;
  dayOfWeek: number;
  scheduledDate?: string | null;
  startTime: string;
  endTime: string;
  topic: string;
  ustadzName: string;
  location: string;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface StudyScheduleOccurrence {
  id: string;
  studyScheduleId: string;
  occurrenceDate: string;
  status: string;
  overrideNote: string | null;
  schedule?: StudySchedule | null;
}

export interface Activity {

  id: string;
  activityCategoryId: string;
  category: ActivityCategory;
  title: string;
  slug: string;
  description: string | null;
  startAt: string;
  endAt: string;
  location: string | null;
  status: "upcoming" | "ongoing" | "completed" | "cancelled";
  cover: MediaObject | null;
  createdAt: string;
  updatedAt: string;
}

export interface Article {
  id: string;
  articleCategoryId: string;
  category: ArticleCategory;
  title: string;
  slug: string;
  excerpt: string | null;
  body: string | null;
  cover: MediaObject | null;
  status: "draft" | "scheduled" | "published" | "archived";
  publishedAt: string | null;
  viewCount: number;
  authorId: string;
  createdAt: string;
  updatedAt: string;
}

export interface Gallery {
  id: string;
  galleryCategoryId: string;
  category: GalleryCategory;
  activityId: string | null;
  title: string;
  description: string | null;
  coverPhoto: MediaObject | null;
  photoCount: number;
  photos: GalleryPhoto[];
  createdAt: string;
  updatedAt: string;
}

export interface GalleryPhoto {
  id: string;
  mediaId: string;
  url: string;
  caption: string | null;
  displayOrder: number;
}

export interface LibraryDocument {
  id: string;
  libraryCategoryId: string;
  category: LibraryCategory;
  title: string;
  description: string | null;
  file: MediaObject | null;
  externalUrl: string | null;
  visibility: "public" | "internal";
  downloadCount: number;
  libraryType: "pdf" | "audio" | "video_link";
  createdAt: string;
  updatedAt: string;
}

export interface Announcement {
  id: string;
  title: string;
  body: string;
  priority: "normal" | "urgent";
  audience: "public" | "internal";
  pinned: boolean;
  startsAt: string;
  expiresAt: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface OrganizationPeriod {
  id: string;
  label: string;
  startDate: string;
  endDate: string;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface OrganizationPeriod {
  id: string;
  label: string;
  startDate: string;
  endDate: string;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface OrganizationPosition {
  id: string;
  organizationPeriodId: string;
  departmentId: string | null;
  department: Department | null;
  parentPositionId: string | null;
  parent?: { id: string; title: string } | null;
  positionType: "chairman" | "vice_chairman" | "secretary" | "treasurer" | "coordinator" | "member";
  level: number;
  title: string;
  member: { id: string; fullName: string; notes?: string | null; photo?: { id: string; url: string } | null } | null;
  displayOrder: number;
  children?: OrganizationPosition[];
  createdAt: string;
  updatedAt: string;
}

export interface AttendanceSession {
  id: string;
  sourceType: "schedule_occurrence" | "activity";
  sourceId: string;
  qrToken: string;
  opensAt: string;
  closesAt: string;
  attendanceCount: number;
  createdAt: string;
  updatedAt: string;
}

export interface NotificationItem {
  id: string;
  type: string;
  data: Record<string, unknown>;
  isRead: boolean;
  readAt: string | null;
  createdAt: string;
}

export interface Setting {
  id: string;
  key: string;
  value: string | number | boolean | null;
  type: "string" | "number" | "boolean" | "json" | "encrypted";
  group: string;
  description: string | null;
  isEncrypted: boolean;
  autoload: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface MosqueLocation {
  id: string;
  name: string;
  address: string;
  contactName?: string;
  phone?: string;
  mapEmbedUrl: string;
  mapDirectUrl?: string;
  notes?: string;
  isActive: boolean;
}
