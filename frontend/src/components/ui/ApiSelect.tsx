"use client";

import { useQuery } from "@tanstack/react-query";
import { Select } from "./Select";
import { api } from "@/lib/api";
import type { ApiResponse } from "@/lib/api-types";
import type { SelectHTMLAttributes } from "react";

interface ApiSelectProps<T> extends Omit<SelectHTMLAttributes<HTMLSelectElement>, "options"> {
  endpoint: string;
  label?: string;
  error?: string;
  placeholder?: string;
  queryKey?: unknown[];
  params?: Record<string, string | number | boolean | undefined>;
  getOptionLabel: (item: T) => string;
  getOptionValue?: (item: T) => string;
}

export function ApiSelect<T extends { id: string }>({
  endpoint,
  label,
  error,
  placeholder = "Pilih data",
  queryKey,
  params,
  getOptionLabel,
  getOptionValue = (item) => item.id,
  disabled,
  ...props
}: ApiSelectProps<T>) {
  const { data, isLoading, isError } = useQuery({
    queryKey: queryKey ?? [endpoint, "select", params],
    queryFn: async () => {
      const response = await api.get<ApiResponse<T[]>>(endpoint, { params: { perPage: 100, ...params } });
      return response.data.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  const options = data?.map((item) => ({ value: getOptionValue(item), label: getOptionLabel(item) })) ?? [];
  const helper = isError ? "Gagal memuat pilihan." : !isLoading && options.length === 0 ? "Belum ada data pilihan." : null;

  return (
    <div>
      {label && <label className="block text-label-md font-label-md text-on-surface mb-1">{label}</label>}
      <Select
        {...props}
        disabled={disabled || isLoading || isError}
        options={[{ value: "", label: isLoading ? "Memuat pilihan..." : placeholder }, ...options]}
        error={error}
      />
      {(error || helper) && <p className={`mt-1 text-label-sm ${error ? "text-error" : "text-on-surface-variant"}`}>{error || helper}</p>}
    </div>
  );
}
