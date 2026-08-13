"use client";

import { useMemo } from "react";
import { useQuery } from "@tanstack/react-query";
import Image from "next/image";
import { api } from "@/lib/api";
import { Alert } from "@/components/ui/Alert";
import { Skeleton } from "@/components/ui/Skeleton";
import type { OrganizationPosition } from "@/types/models";

/** Helper to flatten nested tree hierarchy */
function flattenTree(nodes: OrganizationPosition[]): OrganizationPosition[] {
  const result: OrganizationPosition[] = [];
  function walk(items: OrganizationPosition[]) {
    for (const item of items) {
      result.push(item);
      if (item.children && item.children.length > 0) {
        walk(item.children);
      }
    }
  }
  walk(nodes);
  return result;
}

export function OrganizationPage() {
  // Fetch Public Active Structure from API
  const { data: tree, isLoading, error } = useQuery<OrganizationPosition[]>({
    queryKey: ["public", "organization", "structure"],
    queryFn: async () => {
      const res = await api.get("/public/organization/structure");
      return res.data.data;
    },
    staleTime: 60 * 1000,
  });

  // Flatten positions tree
  const allPositions = useMemo(() => (tree ? flattenTree(tree) : []), [tree]);

  // Group positions dynamically by displayOrder (Urutan Tampilan 1, 2, 3...)
  // Only include main structural positions (no department assigned)
  const displayOrderGroups = useMemo(() => {
    const groups = new Map<number, OrganizationPosition[]>();

    allPositions.forEach((pos) => {
      if (!pos.departmentId && !pos.department) {
        const order = Number(pos.displayOrder) || 1;
        if (!groups.has(order)) {
          groups.set(order, []);
        }
        groups.get(order)!.push(pos);
      }
    });

    return Array.from(groups.entries())
      .map(([order, positions]) => ({ order, positions }))
      .sort((a, b) => a.order - b.order);
  }, [allPositions]);

  // Group positions by department (Bidang / Tim Kerja)
  const departmentGroups = useMemo(() => {
    const groups = new Map<string, { id: string; name: string; positions: OrganizationPosition[] }>();

    allPositions.forEach((pos) => {
      if (pos.department) {
        const deptId = pos.department.id;
        if (!groups.has(deptId)) {
          groups.set(deptId, {
            id: deptId,
            name: pos.department.name,
            positions: [],
          });
        }
        groups.get(deptId)!.positions.push(pos);
      }
    });

    return Array.from(groups.values());
  }, [allPositions]);

  return (
    <div className="min-h-screen bg-[#f5f0e8] text-on-surface font-body-md selection:bg-emerald-200 selection:text-emerald-950">
      
      {/* ── 1. HEADER SECTION ── */}
      <section className="bg-white border-b border-outline/10 px-6 py-12 md:px-10">
        <div className="mx-auto max-w-container-max text-center max-w-3xl space-y-3">
          <span className="inline-flex items-center gap-2 rounded-full bg-[#004b36]/10 px-4 py-1 text-label-sm font-bold uppercase tracking-widest text-[#004b36]">
            STRUKTUR ORGANISASI
          </span>

          <h1 className="text-[34px] sm:text-[44px] font-bold leading-tight text-[#004b36] font-headline-sm tracking-tight">
            Struktur Pengurus Mudes Condet
          </h1>

          <p className="text-body-md sm:text-body-lg text-on-surface-variant max-w-2xl mx-auto leading-relaxed">
            Berikut adalah struktur kepengurusan Muda Mudi Desa Condet yang bergerak bersama untuk kebaikan dan kemajuan komunitas.
          </p>
        </div>
      </section>

      {/* ── 2. MAIN CONTENT AREA (LARGE PHOTO VERTICAL STACKED CARDS) ── */}
      <div className="mx-auto max-w-container-max px-6 py-14 md:px-10 space-y-12">

        {/* Loading State */}
        {isLoading && (
          <div className="space-y-8 animate-pulse text-center">
            <Skeleton className="h-32 w-64 mx-auto rounded-3xl" />
            <div className="flex justify-center gap-6">
              <Skeleton className="h-32 w-64 rounded-3xl" />
              <Skeleton className="h-32 w-64 rounded-3xl" />
            </div>
          </div>
        )}

        {/* Error State */}
        {!isLoading && error && (
          <Alert variant="error">
            Gagal memuat data struktur organisasi. Silakan coba muat ulang halaman.
          </Alert>
        )}

        {/* Empty State */}
        {!isLoading && !error && allPositions.length === 0 && (
          <div className="rounded-3xl bg-white p-12 text-center text-on-surface-variant border border-outline/10 max-w-md mx-auto space-y-3">
            <span className="material-symbols-outlined text-[56px] text-[#004b36]/30">account_tree</span>
            <h3 className="text-[20px] font-bold text-[#004b36] font-headline-sm">Struktur Organisasi Belum Tersedia</h3>
            <p className="text-body-sm text-on-surface-variant">
              Data kepengurusan belum diinput oleh pengurus. Silakan cek kembali secara berkala.
            </p>
          </div>
        )}

        {/* ── DYNAMIC HIERARCHY BY DISPLAY ORDER (LARGE PHOTOS + VERTICAL LAYOUT) ── */}
        {!isLoading && !error && displayOrderGroups.length > 0 && (
          <div className="space-y-6 overflow-x-auto pb-6">
            <div className="min-w-[600px] max-w-5xl mx-auto space-y-4 text-center">
              
              {displayOrderGroups.map((group, index) => (
                <div key={group.order} className="flex flex-col items-center">
                  
                  {/* Connector Line between levels */}
                  {index > 0 && (
                    <div className="w-0.5 h-10 bg-outline/30 my-4" />
                  )}

                  {/* Row of positions with the same displayOrder (sejajar) */}
                  <div className="w-full overflow-x-auto no-scrollbar py-4">
                    <div className="flex justify-center gap-3 sm:gap-6 px-4 mx-auto w-fit">
                      {group.positions.map((pos) => {
                        const name = pos.member?.fullName ?? "Posisi Belum Diisi";
                        const photoUrl = pos.member?.photo?.url;
                        const count = group.positions.length;

                        // Dynamic scaling class configurations
                        let cardClass = "w-[240px] sm:w-[280px] p-6";
                        let photoClass = "h-24 w-24 sm:h-28 sm:w-28";
                        let nameClass = "text-[18px] sm:text-[20px]";
                        let titleClass = "text-[13px] sm:text-[14px]";

                        if (count === 2) {
                          cardClass = "w-[190px] sm:w-[230px] p-5";
                          photoClass = "h-20 w-20 sm:h-24 sm:w-24";
                          nameClass = "text-[16px] sm:text-[18px]";
                          titleClass = "text-[12px] sm:text-[13px]";
                        } else if (count === 3) {
                          cardClass = "w-[160px] sm:w-[200px] p-4";
                          photoClass = "h-16 w-16 sm:h-20 sm:w-20";
                          nameClass = "text-[14px] sm:text-[16px]";
                          titleClass = "text-[11px] sm:text-[12px]";
                        } else if (count >= 4) {
                          cardClass = "w-[130px] sm:w-[170px] p-3 sm:p-4";
                          photoClass = "h-12 w-12 sm:h-16 sm:w-16";
                          nameClass = "text-[12px] sm:text-[14px]";
                          titleClass = "text-[10px] sm:text-[11px]";
                        }

                        return (
                          <div
                            key={pos.id}
                            className={`bg-white border border-outline/15 rounded-3xl shadow-2xs flex flex-col items-center text-center hover:shadow-md transition-all group shrink-0 ${cardClass}`}
                          >
                            {/* Circular Photo Avatar */}
                            <div className={`relative rounded-full overflow-hidden shrink-0 border-4 border-amber-400 bg-[#004b36]/10 shadow-xs mb-3 group-hover:scale-105 transition-transform duration-300 ${photoClass}`}>
                              {photoUrl ? (
                                <Image
                                  src={photoUrl}
                                  alt={name}
                                  fill
                                  className="object-cover"
                                />
                              ) : (
                                <div className="flex h-full w-full items-center justify-center bg-[#004b36] text-[#f9c74f] font-bold text-xl">
                                  {name[0] ?? "P"}
                                </div>
                              )}
                            </div>

                            {/* Nama Pengurus Below Photo */}
                            <p className={`font-bold text-[#004b36] font-headline-sm leading-snug line-clamp-2 ${nameClass}`}>
                              {name}
                            </p>

                            {/* Dapukan / Jabatan Below Name */}
                            <span className={`font-bold text-amber-700 mt-1 block line-clamp-2 ${titleClass}`}>
                              {pos.title}
                            </span>

                            {/* Gold Accent Underline */}
                            <div className="w-10 h-0.5 bg-amber-400 mt-3" />
                          </div>
                        );
                      })}
                    </div>
                  </div>

                </div>
              ))}

            </div>
          </div>
        )}

        {/* ── 3. TEAM / DEPARTMENT SECTION ── */}
        {!isLoading && !error && departmentGroups.length > 0 && (
          <div className="space-y-8 pt-12 border-t border-outline/10">
            <div className="text-center max-w-3xl mx-auto space-y-3">
              <span className="inline-flex items-center gap-2 rounded-full bg-[#004b36]/10 px-4 py-1 text-label-sm font-bold uppercase tracking-widest text-[#004b36]">
                BIDANG &amp; TIM KERJA
              </span>
              <h2 className="text-[28px] sm:text-[36px] font-bold text-[#004b36] font-headline-sm tracking-tight">
                Pembagian Bidang Kepengurusan
              </h2>
              <p className="text-body-md text-on-surface-variant max-w-xl mx-auto leading-relaxed">
                Daftar pengurus yang aktif berkontribusi dalam masing-masing bidang kerja kepengurusan Muda Mudi Desa Condet.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
              {departmentGroups.map((group) => (
                <div key={group.id} className="bg-white border border-outline/15 rounded-3xl p-6 shadow-2xs space-y-5 hover:shadow-xs transition-shadow">
                  {/* Department Header */}
                  <div className="flex items-center gap-3.5 pb-4 border-b border-outline/10">
                    <div className="p-3 rounded-2xl bg-[#004b36]/10 text-[#004b36] flex items-center justify-center shrink-0">
                      <span className="material-symbols-outlined text-[24px]">group</span>
                    </div>
                    <div>
                      <h3 className="text-[18px] sm:text-[20px] font-bold text-[#004b36] font-headline-sm leading-snug">
                        {group.name}
                      </h3>
                      <p className="text-[12px] sm:text-[13px] font-semibold text-on-surface-variant/80 uppercase tracking-wider mt-0.5">
                        {group.positions.length} Anggota Tim
                      </p>
                    </div>
                  </div>

                  {/* Members List */}
                  <div className="space-y-3">
                    {group.positions.map((pos) => {
                      const mName = pos.member?.fullName ?? "Posisi Belum Diisi";
                      const mPhoto = pos.member?.photo?.url;
                      const mNotes = pos.member?.notes || "—";
                      return (
                        <div key={pos.id} className="flex items-center justify-between gap-4 p-3 rounded-2xl bg-[#fbf8f1] border border-outline/5 hover:border-amber-400/30 transition-all group">
                          <div className="flex items-center gap-3.5 min-w-0">
                            {/* Member Mini Avatar */}
                            <div className="relative h-10 w-10 rounded-full overflow-hidden shrink-0 border border-amber-400/60 bg-white shadow-3xs group-hover:scale-105 transition-transform duration-200">
                              {mPhoto ? (
                                <Image
                                  src={mPhoto}
                                  alt={mName}
                                  fill
                                  className="object-cover"
                                />
                              ) : (
                                <div className="flex h-full w-full items-center justify-center bg-[#004b36] text-[#f9c74f] font-bold text-sm">
                                  {mName[0] ?? "P"}
                                </div>
                              )}
                            </div>

                            <div className="min-w-0">
                              <h4 className="font-bold text-[#004b36] text-[15px] sm:text-[16px] truncate leading-tight">{mName}</h4>
                              <p className="text-[12px] sm:text-[13px] text-amber-700 font-semibold mt-0.5">{pos.title}</p>
                            </div>
                          </div>

                          {/* Asal Kelompok Badge */}
                          <div className="shrink-0 text-right">
                            <span className="inline-block text-[11px] sm:text-[12px] font-bold text-[#004b36] bg-[#004b36]/10 px-3 py-1 rounded-full whitespace-nowrap">
                              {mNotes}
                            </span>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

      </div>
    </div>
  );
}
