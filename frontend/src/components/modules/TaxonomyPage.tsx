"use client";

import { useState, useMemo } from "react";
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
import { Badge } from "@/components/ui/Badge";
import { Alert } from "@/components/ui/Alert";
import { Icon } from "@/components/ui/Icon";
import { TaxonomyForm } from "./TaxonomyForm";
import { fetchList, createItem, patchItem, deleteItem, restoreItem, bulkAction } from "@/lib/query-utils";
import { getApiErrorMessage, getValidationErrors } from "@/lib/query-client";
import type { TaxonomyResource } from "@/types/models";
import type { TaxonomyFormValues } from "@/schemas/taxonomy";
import type { PermissionAction } from "@/lib/permissions";

interface TaxonomyPageProps {
  endpoint: string;
  permissionPrefix: string;
  title: string;
  description: string;
  itemCountKey: keyof TaxonomyResource;
  icon: string;
}

export function TaxonomyPage({
  endpoint,
  permissionPrefix,
  title,
  description,
  itemCountKey,
  icon,
}: TaxonomyPageProps) {
  const queryClient = useQueryClient();
  const { toast } = useToast();
  const { can } = usePermission();
  const [search, setSearch] = useState("");
  const [filterActive, setFilterActive] = useState<string>("");
  const [page, setPage] = useState(1);
  const [selectedIds, setSelectedIds] = useState<string[]>([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<TaxonomyResource | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<TaxonomyResource | null>(null);

  const canCreate = can(permissionPrefix, "create");
  const canUpdate = can(permissionPrefix, "update");
  const canDelete = can(permissionPrefix, "delete");
  const canRestore = can(permissionPrefix, "restore");

  const params = useMemo(
    () => ({
      search,
      filter: filterActive ? { is_active: filterActive } : {},
      sort: "display_order",
      page,
      perPage: 10,
    }),
    [search, filterActive, page]
  );

  const { data, isLoading, error, refetch } = useQuery({
    queryKey: [endpoint, params],
    queryFn: () => fetchList<TaxonomyResource>(endpoint, params),
  });

  const rows = data?.data ?? [];
  const pagination = data?.meta?.pagination;

  const invalidate = () => {
    queryClient.invalidateQueries({ queryKey: [endpoint] });
  };

  const createMutation = useMutation({
    mutationFn: (values: TaxonomyFormValues) => createItem<TaxonomyResource>(endpoint, values),
    onSuccess: () => {
      toast("Data berhasil ditambahkan", "success");
      setModalOpen(false);
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, values }: { id: string; values: TaxonomyFormValues }) =>
      patchItem<TaxonomyResource>(endpoint, id, values),
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

  const restoreMutation = useMutation({
    mutationFn: (id: string) => restoreItem<TaxonomyResource>(endpoint, id),
    onSuccess: () => {
      toast("Data berhasil dipulihkan", "success");
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const bulkActionMutation = useMutation({
    mutationFn: ({ action, ids }: { action: string; ids: string[] }) => bulkAction(endpoint, action, ids),
    onSuccess: () => {
      toast("Aksi massal berhasil", "success");
      setSelectedIds([]);
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const handleSave = (values: TaxonomyFormValues) => {
    if (editing) {
      updateMutation.mutate({ id: editing.id, values });
    } else {
      createMutation.mutate(values);
    }
  };

  const openCreate = () => {
    setEditing(null);
    setModalOpen(true);
  };

  const openEdit = (row: TaxonomyResource) => {
    setEditing(row);
    setModalOpen(true);
  };

  const handleBulk = (action: string) => {
    if (selectedIds.length === 0) return;
    bulkActionMutation.mutate({ action, ids: selectedIds });
  };

  const columns: Column<TaxonomyResource>[] = [
    {
      header: "Nama",
      cell: (row) => (
        <div className="flex items-center gap-2">
          {row.icon && <Icon name={row.icon} style={row.color ? { color: row.color } : undefined} className="text-xl" />}
          <div>
            <p className="font-medium text-on-surface">{row.name}</p>
            <p className="text-body-sm text-on-surface-variant">{row.slug}</p>
          </div>
        </div>
      ),
    },
    {
      header: "Deskripsi",
      cell: (row) => <p className="text-on-surface-variant">{row.description || "-"}</p>,
      className: "max-w-xs",
    },
    {
      header: "Urutan",
      cell: (row) => row.displayOrder,
      width: "80px",
    },
    {
      header: "Status",
      cell: (row) => <Badge variant={row.isActive ? "success" : "outline"}>{row.isActive ? "Aktif" : "Nonaktif"}</Badge>,
      width: "180px",
    },
    {
      header: "Digunakan",
      cell: (row) => (row[itemCountKey] as number) ?? 0,
      width: "180px",
    },
    {
      header: "",
      cell: (row) => (
        <div className="flex items-center justify-end gap-1">
          {canUpdate && (
            <button
              onClick={() => openEdit(row)}
              className="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-label-md font-label-md text-on-surface-variant hover:bg-surface-container-low hover:text-primary"
              aria-label="Edit"
            >
              <Icon name="edit" />
              <span>Edit</span>
            </button>
          )}
          {canDelete && (
            <button
              onClick={() => setDeleteTarget(row)}
              className="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-label-md font-label-md text-on-surface-variant hover:bg-surface-container-low hover:text-error"
              aria-label="Hapus"
            >
              <Icon name="delete" />
              <span>Hapus</span>
            </button>
          )}
        </div>
      ),
      width: "190px",
    },
  ];

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
            Tambah {title}
          </Button>
        )}


      </div>

      {error && <Alert variant="error">{getApiErrorMessage(error)}</Alert>}

      <Card className="p-md space-y-4">
        <div className="flex flex-col sm:flex-row gap-3">
          <SearchInput
            value={search}
            onChange={(v) => {
              setSearch(v);
              setPage(1);
            }}
            placeholder="Cari nama..."
            className="sm:max-w-sm"
          />
          <select
            value={filterActive}
            onChange={(e) => {
              setFilterActive(e.target.value);
              setPage(1);
            }}
            className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface"
          >
            <option value="">Semua status</option>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
          {selectedIds.length > 0 && canUpdate && (
            <div className="flex gap-2 ml-auto">
              <Button
                variant="outline"
                size="sm"
                onClick={() => handleBulk("activate")}
                loading={bulkActionMutation.isPending}
                disabled={bulkActionMutation.isPending}
              >
                Aktifkan
              </Button>
              <Button
                variant="outline"
                size="sm"
                onClick={() => handleBulk("deactivate")}
                loading={bulkActionMutation.isPending}
                disabled={bulkActionMutation.isPending}
              >
                Nonaktifkan
              </Button>
              {canDelete && (
                <Button
                  variant="danger"
                  size="sm"
                  onClick={() => handleBulk("delete")}
                  loading={bulkActionMutation.isPending}
                  disabled={bulkActionMutation.isPending}
                >
                  Hapus
                </Button>
              )}
            </div>
          )}
        </div>

        <DataTable
          columns={columns}
          rows={rows}
          keyExtractor={(row) => row.id}
          loading={isLoading}
          selectable={canUpdate || canDelete}
          selectedIds={selectedIds}
          onSelectionChange={setSelectedIds}
          onRowClick={canUpdate ? openEdit : undefined}
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
        onClose={() => setModalOpen(false)}
        title={editing ? "Ubah Data" : "Tambah Data"}
        description={editing ? "Perbarui informasi di bawah ini." : "Isi data baru untuk ditambahkan."}
        size="md"
      >
        <TaxonomyForm
          initial={editing}
          onSubmit={handleSave}
          onCancel={() => setModalOpen(false)}
          loading={createMutation.isPending || updateMutation.isPending}
        />
      </Modal>

      <ConfirmDialog
        isOpen={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus data?"
        description={`Data "${deleteTarget?.name}" akan dihapus. Tindakan ini tidak dapat dibatalkan.`}
        confirmText="Hapus"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}

