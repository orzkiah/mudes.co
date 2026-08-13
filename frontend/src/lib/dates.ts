import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import "dayjs/locale/id";

dayjs.extend(relativeTime);
dayjs.locale("id");

export function formatDate(value: string | null | undefined, fallback = "-"): string {
  if (!value) return fallback;
  const d = dayjs(value);
  return d.isValid() ? d.format("DD MMMM YYYY") : fallback;
}

export function formatDateTime(value: string | null | undefined, fallback = "-"): string {
  if (!value) return fallback;
  const d = dayjs(value);
  return d.isValid() ? d.format("DD MMMM YYYY, HH:mm") : fallback;
}

export function formatTime(value: string | null | undefined, fallback = "-"): string {
  if (!value) return fallback;
  const parts = value.split(":");
  if (parts.length >= 2) {
    const hh = parts[0].padStart(2, "0");
    const mm = parts[1].padStart(2, "0");
    return `${hh}:${mm}`;
  }
  const d = dayjs(value);
  return d.isValid() ? d.format("HH:mm") : fallback;
}

export function formatRelative(value: string | null | undefined): string {
  if (!value) return "-";
  const d = dayjs(value);
  return d.isValid() ? d.fromNow() : "-";
}

export function toApiDate(value: string | null): string | null {
  if (!value) return null;
  const d = dayjs(value);
  return d.isValid() ? d.format("YYYY-MM-DD") : null;
}

export function toApiTime(value: string | null): string | null {
  if (!value) return null;
  const d = dayjs(value, "HH:mm");
  return d.isValid() ? d.format("HH:mm:ss") : null;
}

export { dayjs };
