"use client";

import { cn } from "@/lib/cn";
import { Checkbox } from "./Checkbox";
import { Skeleton } from "./Skeleton";
import { EmptyState } from "./EmptyState";
import type { ReactNode } from "react";

export interface Column<T> {
  header: ReactNode;
  cell: (row: T) => ReactNode;
  width?: string;
  className?: string;
}

interface DataTableProps<T> {
  columns: Column<T>[];
  rows: T[];
  loading?: boolean;
  keyExtractor: (row: T) => string;
  selectable?: boolean;
  selectedIds?: string[];
  onSelectionChange?: (ids: string[]) => void;
  onRowClick?: (row: T) => void;
  emptyTitle?: string;
  emptyDescription?: string;
  skeletonRows?: number;
}

export function DataTable<T>({
  columns,
  rows,
  loading,
  keyExtractor,
  selectable,
  selectedIds = [],
  onSelectionChange,
  onRowClick,
  emptyTitle,
  emptyDescription,
  skeletonRows = 5,
}: DataTableProps<T>) {
  const allSelected = rows.length > 0 && rows.every((row) => selectedIds.includes(keyExtractor(row)));
  const someSelected = selectedIds.length > 0 && !allSelected;

  const toggleAll = () => {
    if (!onSelectionChange) return;
    if (allSelected) {
      onSelectionChange([]);
    } else {
      onSelectionChange(rows.map((row) => keyExtractor(row)));
    }
  };

  const toggleRow = (id: string) => {
    if (!onSelectionChange) return;
    if (selectedIds.includes(id)) {
      onSelectionChange(selectedIds.filter((x) => x !== id));
    } else {
      onSelectionChange([...selectedIds, id]);
    }
  };

  if (!loading && rows.length === 0) {
    return (
      <EmptyState
        title={emptyTitle || "Tidak ada data"}
        description={emptyDescription || "Belum ada data untuk ditampilkan."}
      />
    );
  }

  return (
    <div className="overflow-x-auto rounded-xl border border-outline-variant bg-surface-container-lowest">
      <table className="w-full text-left">
        <thead className="bg-surface-container-low border-b border-outline-variant">
          <tr>
            {selectable && (
              <th className="px-4 py-3 w-10">
                <Checkbox
                  checked={allSelected}
                  ref={(input) => {
                    if (input) input.indeterminate = someSelected;
                  }}
                  onChange={toggleAll}
                />
              </th>
            )}
            {columns.map((col, i) => (
              <th
                key={i}
                className={cn(
                  "px-4 py-3 text-label-sm font-label-sm text-on-surface-variant",
                  col.className
                )}
                style={{ width: col.width }}
              >
                {col.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-outline-variant">
          {loading && rows.length === 0
            ? Array.from({ length: skeletonRows }).map((_, i) => (
                <tr key={`sk-${i}`}>
                  {selectable && (
                    <td className="px-4 py-3">
                      <Skeleton className="w-4 h-4" />
                    </td>
                  )}
                  {columns.map((_, j) => (
                    <td key={j} className="px-4 py-3">
                      <Skeleton className="h-4 w-24" />
                    </td>
                  ))}
                </tr>
              ))
            : rows.map((row) => {
                const id = keyExtractor(row);
                return (
                  <tr
                    key={id}
                    onClick={() => onRowClick?.(row)}
                    className={cn(
                      "hover:bg-surface-container-low/50 transition-colors",
                      onRowClick && "cursor-pointer"
                    )}
                  >
                    {selectable && (
                      <td className="px-4 py-3" onClick={(e) => e.stopPropagation()}>
                        <Checkbox checked={selectedIds.includes(id)} onChange={() => toggleRow(id)} />
                      </td>
                    )}
                    {columns.map((col, i) => (
                      <td key={i} className={cn("px-4 py-3 text-body-sm text-on-surface", col.className)}>
                        {col.cell(row)}
                      </td>
                    ))}
                  </tr>
                );
              })}
        </tbody>
      </table>
    </div>
  );
}
