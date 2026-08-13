import { cn } from "@/lib/cn";
import { Icon } from "./Icon";

interface PaginationProps {
  currentPage: number;
  lastPage: number;
  total: number;
  perPage: number;
  from: number;
  to: number;
  onPageChange: (page: number) => void;
  className?: string;
}

export function Pagination({
  currentPage,
  lastPage,
  total,
  from,
  to,
  onPageChange,
  className,
}: PaginationProps) {
  if (lastPage <= 1) return null;

  const pages = Array.from({ length: lastPage }, (_, i) => i + 1);
  const visible = pages.filter((p) => {
    if (p === 1 || p === lastPage) return true;
    return Math.abs(p - currentPage) <= 1;
  });

  const items: (number | string)[] = [];
  visible.forEach((p, idx) => {
    if (idx > 0 && (visible[idx] as number) - (visible[idx - 1] as number) > 1) {
      items.push("...");
    }
    items.push(p);
  });

  return (
    <div className={cn("flex flex-col sm:flex-row items-center justify-between gap-4", className)}>
      <p className="text-body-sm text-on-surface-variant">
        Menampilkan {from}-{to} dari {total}
      </p>
      <div className="flex items-center gap-1">
        <button
          onClick={() => onPageChange(currentPage - 1)}
          disabled={currentPage <= 1}
          className="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-low disabled:opacity-40"
          aria-label="Halaman sebelumnya"
        >
          <Icon name="chevron_left" />
        </button>
        {items.map((item, i) =>
          typeof item === "number" ? (
            <button
              key={i}
              onClick={() => onPageChange(item)}
              className={cn(
                "min-w-[36px] h-9 px-2 rounded-lg text-body-sm font-body-md",
                item === currentPage
                  ? "bg-primary text-on-primary"
                  : "text-on-surface hover:bg-surface-container-low"
              )}
            >
              {item}
            </button>
          ) : (
            <span key={i} className="px-2 text-on-surface-variant">
              {item}
            </span>
          )
        )}
        <button
          onClick={() => onPageChange(currentPage + 1)}
          disabled={currentPage >= lastPage}
          className="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-low disabled:opacity-40"
          aria-label="Halaman berikutnya"
        >
          <Icon name="chevron_right" />
        </button>
      </div>
    </div>
  );
}
