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
import { Avatar } from "@/components/ui/Avatar";
import { Icon } from "@/components/ui/Icon";
import { MemberForm } from "@/components/modules/MemberForm";
import { fetchList, createItem, updateItem, deleteItem } from "@/lib/query-utils";
import { getApiErrorMessage } from "@/lib/query-client";
import type { Member } from "@/types/models";
import type { MemberFormValues } from "@/schemas/member";
import { formatDate } from "@/lib/dates";
import { removeEmptyStrings } from "@/lib/form-utils";
import { PageContainer } from "@/components/layout/PageContainer";

const endpoint = "/admin/members";
const permissionPrefix = "members";

export default function MembersPage() {
  const queryClient = useQueryClient();
  const { toast } = useToast();
  const { can } = usePermission();
  const [search, setSearch] = useState("");
  const [filterStatus, setFilterStatus] = useState("");
  const [filterGender, setFilterGender] = useState("");
  const [page, setPage] = useState(1);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<Member | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Member | null>(null);

  const canCreate = can(permissionPrefix, "create");
  const canUpdate = can(permissionPrefix, "update");
  const canDelete = can(permissionPrefix, "delete");

  const params = useMemo(
    () => ({
      search,
      filter: {
        status: filterStatus || undefined,
        gender: filterGender || undefined,
      },
      sort: "full_name",
      page,
      perPage: 10,
    }),
    [search, filterStatus, filterGender, page]
  );

  const { data, isLoading, error } = useQuery({
    queryKey: [endpoint, params],
    queryFn: () => fetchList<Member>(endpoint, params),
  });

  const rows = data?.data ?? [];
  const pagination = data?.meta?.pagination;

  const invalidate = () => queryClient.invalidateQueries({ queryKey: [endpoint] });

  const createMutation = useMutation({
    mutationFn: (values: MemberFormValues) => createItem<Member>(endpoint, values),
    onSuccess: () => {
      toast("Anggota berhasil ditambahkan", "success");
      setModalOpen(false);
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, values }: { id: string; values: MemberFormValues }) => updateItem<Member>(endpoint, id, values),
    onSuccess: () => {
      toast("Anggota berhasil diperbarui", "success");
      setModalOpen(false);
      setEditing(null);
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: string) => deleteItem(endpoint, id),
    onSuccess: () => {
      toast("Anggota berhasil dihapus", "success");
      setDeleteTarget(null);
      invalidate();
    },
    onError: (err) => toast(getApiErrorMessage(err), "error"),
  });

  const handleSave = (values: MemberFormValues) => {
    const cleaned = removeEmptyStrings(values);
    if (editing) {
      updateMutation.mutate({ id: editing.id, values: cleaned as MemberFormValues });
    } else {
      createMutation.mutate(cleaned as MemberFormValues);
    }
  };

  const openCreate = () => {
    setEditing(null);
    setModalOpen(true);
  };

  const openEdit = (row: Member) => {
    setEditing(row);
    setModalOpen(true);
  };

  const statusBadge = (status: string) => {
    const variants: Record<string, "success" | "default" | "warning" | "error"> = {
      active: "success",
      inactive: "default",
      alumni: "warning",
      moved_out: "error",
    };
    const labels: Record<string, string> = {
      active: "Aktif",
      inactive: "Nonaktif",
      alumni: "Alumni",
      moved_out: "Pindah",
    };
    return <Badge variant={variants[status] || "default"}>{labels[status] || status}</Badge>;
  };

  const columns: Column<Member>[] = [
    {
      header: "Anggota",
      cell: (row) => (
        <div className="flex items-center gap-3">
          <Avatar src={row.photo?.url} name={row.fullName} size="sm" />
          <div>
            <p className="font-medium text-on-surface">{row.fullName}</p>
            <p className="text-body-sm text-on-surface-variant">{row.phone || "-"}</p>
          </div>
        </div>
      ),
    },
    {
      header: "Dapukan / Jabatan",
      cell: (row) =>
        row.position?.title ? (
          <span className="rounded-md bg-emerald-50 px-2 py-0.5 text-label-sm font-bold text-[#004b36] border border-emerald-200">
            {row.position.title}
          </span>
        ) : (
          <span className="text-on-surface-variant/40 text-body-sm font-medium">-</span>
        ),
    },
    { header: "Jenis Kelamin", cell: (row) => (row.gender === "male" ? "Laki-laki" : row.gender === "female" ? "Perempuan" : "-") },
    { header: "Tanggal Lahir", cell: (row) => formatDate(row.birthDate) },
    { header: "Bergabung", cell: (row) => formatDate(row.joinDate) },
    { header: "Status", cell: (row) => statusBadge(row.status) },
    {
      header: "",
      cell: (row) => (
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
    },
  ];

  if (!can(permissionPrefix, "view")) {
    return <Alert variant="error">Anda tidak memiliki izin untuk mengakses modul ini.</Alert>;
  }

  return (
    <PageContainer
      title={
        <div className="flex items-center gap-2">
          <Icon name="group" />
          <span>Anggota</span>
        </div>
      }
      subtitle="Kelola data anggota komunitas."
      actions={canCreate ? <Button leftIcon="add" onClick={openCreate}>Tambah Anggota</Button> : null}
      breadcrumbs={[{ label: "Admin", href: "/admin" }, { label: "Anggota" }]}
    >
      <div className="space-y-6">
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
            value={filterStatus}
            onChange={(e) => {
              setFilterStatus(e.target.value);
              setPage(1);
            }}
            className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface"
          >
            <option value="">Semua status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
            <option value="alumni">Alumni</option>
            <option value="moved_out">Pindah</option>
          </select>
          <select
            value={filterGender}
            onChange={(e) => {
              setFilterGender(e.target.value);
              setPage(1);
            }}
            className="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-body-md text-on-surface"
          >
            <option value="">Semua gender</option>
            <option value="male">Laki-laki</option>
            <option value="female">Perempuan</option>
          </select>
        </div>

        <DataTable
          columns={columns}
          rows={rows}
          keyExtractor={(row) => row.id}
          loading={isLoading}
          onRowClick={canUpdate ? openEdit : undefined}
          emptyTitle="Belum ada anggota"
          emptyDescription="Tambahkan anggota baru untuk mulai mengelola data komunitas."
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
        title={editing ? "Ubah Anggota" : "Tambah Anggota"}
        size="md"
      >
        <MemberForm
          initial={editing}
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
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus anggota?"
        description={`"${deleteTarget?.fullName}" akan dihapus. Tindakan ini tidak dapat dibatalkan.`}
        confirmText="Hapus"
        isLoading={deleteMutation.isPending}
      />
    </div>
    </PageContainer>
  );
}

