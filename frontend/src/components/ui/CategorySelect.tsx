"use client";

import { ApiSelect } from "./ApiSelect";
import type { Taxonomy } from "@/lib/api-types";
import type { SelectHTMLAttributes } from "react";

interface CategorySelectProps extends Omit<SelectHTMLAttributes<HTMLSelectElement>, "options"> {
  endpoint: string;
  label?: string;
  error?: string;
  placeholder?: string;
}

export function CategorySelect({ endpoint, label, error, placeholder = "Pilih kategori", ...props }: CategorySelectProps) {
  return (
    <ApiSelect<Taxonomy>
      endpoint={endpoint}
      label={label}
      error={error}
      placeholder={placeholder}
      getOptionLabel={(item) => item.name}
      {...props}
    />
  );
}
