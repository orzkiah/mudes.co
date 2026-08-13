"use client";

import { useState, useMemo, type ReactNode } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { usePermission } from "@/hooks/usePermission";
import { useToast } from "@/providers/ToastProvider";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { DataTable, type Column } from "@/components/ui/DataTable";
import { Pagination } from "@/components/ui/Pagination";
import { SearchInput } from "@/components/ui/SearchInput";
import { Modal } from "@/components/ui/Modal";
import { ConfirmDialog } from "@/components/ui/Modal";
import { Alert } from "@/components/ui/Alert";
import { Icon } from "@/components/ui/Icon";
import { Select } from "@/components/ui/Select";
import { fetchList, createItem, updateItem, deleteItem } from "@/lib/query-utils";
import { getApiErrorMessage } from "@/lib/query-client";
import type { ListParams } from "@/lib/api-types";

interface FormComponentProps<T> {
  initial?: T;
  onSubmit: (values: Record<string, unknown>) => void;
  onCancel: () => void;
  loading?: boolean;
}

interface SortOption {
  value: string;
  label: string;
}

interface CrudPageProps<T> {
  endpoint: string;
  permissionPrefix: string;
  title: string;
  description: string;
  icon: string;
  columns: Column<T>[];
  keyExtractor: (row: T) => string;
  FormComponent: (props: FormComponentProps<T>) => ReactNode;
  sortOptions?: SortOption[];
  defaultSort?: string;
  filterRender?: (filter: Record<string, string>, setFilter: (filter: Record<string, string>) => void) => ReactNode;
  defaultFilter?: Record<string, string>;
  perPage?: number;
  createTitle?: string;
  editTitle?: string;
  /** Custom empty state title shown when the list returns no rows. */
  emptyTitle?: string;
  /** Custom empty state description shown when the list returns no rows. */
  emptyDescription?: string;
}

export function CrudPage<T>({
  endpoint,
  permissionPrefix,
  title,
  description,
  icon,
  columns,
  keyExtractor,
  FormComponent,
  sortOptions,
  defaultSort = "created_at",
  filterRender,
  defaultFilter = {},
  perPage = 10,
  createTitle = "Tambah Data",
  editTitle = "Ubah Data",
  emptyTitle,
  emptyDescription,
}: CrudPageProps<T>) {
  const queryClient = useQueryClient();
  const { toast } = useToast();
  const { can } = usePermission();
  const [search, setSearch] = useState("");
  const [filter, setFilter] = useState<Record<string, string>>(defaultFilter);
  const [sort, setSort] = useState(defaultSort);
  const [page, setPage] = useState(1);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<T | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<T | null>(null);

  const canCreate = can(permissionPrefix, "create");
  const canUpdate = can(permissionPrefix, "update");
  const canDelete = can(permissionPrefix, "delete");

  const params = useMemo<ListParams>(() => {
    const output: ListParams = {
      search,
      sort,
      page,
      perPage,
    };
    if (Object.keys(filter).length > 0) {
      output.filter = filter;
    }
    return output;
  }, [search, sort, filter, page, perPage]);

  const { data, isLoading, error } = useQuery({
    queryKey: [endpoint, params],
    queryFn: () => fetchList<T>(endpoint, params),
  });

  const rows = data?.data ?? [];
  const pagination = data?.meta?.pagination;

  const invalidate = () => queryClient.invalidateQueries({ queryKey: [endpoint] });

  const createMutation = useMutation({
    mutationFn: (values: Record<string, unknown>) => createItem<T>(endpoint, values),
    onSuccess: () => {
      toast("Data berhasil ditambahkan", "success");
      setModalOpen(false);
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, values }: { id: string; values: Record<string, unknown> }) => updateItem<T>(endpoint, id, values),
    onSuccess: () => {
      toast("Data berhasil diperbarui", "success");
      setModalOpen(false);
      setEditing(null);
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: string) => deleteItem(endpoint, id),
    onSuccess: () => {
      toast("Data berhasil dihapus", "success");
      setDeleteTarget(null);
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const handleSave = (values: Record<string, unknown>) => {
    if (editing) {
      updateMutation.mutate({ id: keyExtractor(editing), values });
    } else {
      createMutation.mutate(values);
    }
  };

  const openCreate = () => {
    setEditing(null);
    setModalOpen(true);
  };

  const openEdit = (row: T) => {
    setEditing(row);
    setModalOpen(true);
  };

  const actionColumn: Column<T> = {
    header: "",
    cell: (row: T) => (
      <div className="flex items-center justify-end gap-1" onClick={(e) => e.stopPropagation()}>
        {canUpdate && (
          <button
            onClick={(e) => {
              e.stopPropagation();
              openEdit(row);
            }}
            className="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-label-md font-label-md text-on-surface-variant hover:bg-surface-container-low hover:text-primary"
            aria-label="Edit"
          >
            <Icon name="edit" />
            <span>Edit</span>
          </button>
        )}
        {canDelete && (
          <button
            onClick={(e) => {
              e.stopPropagation();
              setDeleteTarget(row);
            }}
            className="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-label-md font-label-md text-on-surface-variant hover:bg-surface-container-low hover:text-error"
            aria-label="Hapus"
          >
            <Icon name="delete" />
            <span>Hapus</span>
          </button>
        )}
      </div>
    ),
    width: "180px",
  };

  if (!can(permissionPrefix, "view")) {
    return <Alert variant="error">Anda tidak memiliki izin untuk mengakses modul ini.</Alert>;
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-headline-md font-headline-md text-on-surface flex items-center gap-2">
            <Icon name={icon} /> {title}
          </h1>
          <p className="text-body-md text-on-surface-variant mt-1">{description}</p>
        </div>
        {canCreate && (
          <Button leftIcon="add_circle" onClick={openCreate}>
            {createTitle || `Tambah ${title}`}
          </Button>
        )}

      </div>

      {error && <Alert variant="error">{getApiErrorMessage(error)}</Alert>}

      <Card className="p-md space-y-4">
        <div className="flex flex-col lg:flex-row gap-3">
          <SearchInput
            value={search}
            onChange={(v) => {
              setSearch(v);
              setPage(1);
            }}
            placeholder="Cari..."
            className="lg:max-w-sm"
          />
          {sortOptions && (
            <Select
              value={sort}
              onChange={(e) => setSort(e.target.value)}
              options={sortOptions}
              className="lg:w-48"
            />
          )}
          {filterRender && filterRender(filter, setFilter)}
        </div>

        <DataTable
          columns={[...columns, actionColumn]}
          rows={rows}
          keyExtractor={keyExtractor}
          loading={isLoading}
          onRowClick={canUpdate ? openEdit : undefined}
          emptyTitle={emptyTitle}
          emptyDescription={emptyDescription}
        />

        {pagination && pagination.lastPage > 1 && (
          <Pagination
            currentPage={pagination.page}
            lastPage={pagination.lastPage}
            total={pagination.total}
            perPage={pagination.perPage}
            from={(pagination.page - 1) * pagination.perPage + 1}
            to={Math.min(pagination.page * pagination.perPage, pagination.total)}
            onPageChange={setPage}
            className="pt-2"
          />
        )}
      </Card>

      <Modal
        isOpen={modalOpen}
        onClose={() => {
          setModalOpen(false);
          setEditing(null);
        }}
        title={editing ? editTitle : createTitle}
        size="lg"
      >
        <FormComponent
          initial={editing ?? undefined}
          onSubmit={handleSave}
          onCancel={() => {
            setModalOpen(false);
            setEditing(null);
          }}
          loading={createMutation.isPending || updateMutation.isPending}
        />
      </Modal>

      <ConfirmDialog
        isOpen={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(keyExtractor(deleteTarget))}
        title="Hapus data?"
        description="Data yang dipilih akan dihapus. Tindakan ini tidak dapat dibatalkan."
        confirmText="Hapus"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}

