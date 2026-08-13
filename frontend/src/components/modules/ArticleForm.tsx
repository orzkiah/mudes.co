"use client";

import { useState } from "react";
import { useForm, useController } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { articleSchema, type ArticleFormValues } from "@/schemas/article";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Label } from "@/components/ui/Label";
import { MediaReferenceField } from "@/components/ui/MediaReferenceField";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";
import { CategorySelect } from "@/components/ui/CategorySelect";
import { uploadMedia } from "@/lib/query-utils";
import { Spinner } from "@/components/ui/Spinner";
import { getApiErrorMessage } from "@/lib/query-client";
import type { Article } from "@/types/models";

interface ArticleFormProps {
  initial?: Article | null;
  onSubmit: (values: ArticleFormValues) => void;
  onCancel: () => void;
  loading?: boolean;
}

interface ArticleSectionItem {
  id: string;
  url: string;
  title: string;
  description: string;
}

const statusOptions = [
  { value: "draft", label: "Draft" },
  { value: "scheduled", label: "Terjadwal" },
  { value: "published", label: "Publikasi" },
  { value: "archived", label: "Arsip" },
];

export function ArticleForm({ initial, onSubmit, onCancel, loading }: ArticleFormProps) {
  const [isUploadingCover, setIsUploadingCover] = useState(false);
  const [isUploadingSections, setIsUploadingSections] = useState(false);
  const [sectionItems, setSectionItems] = useState<ArticleSectionItem[]>([]);
  const [uploadError, setUploadError] = useState<string | null>(null);

  const {
    register,
    control,
    handleSubmit,
    watch,
    setValue,
    formState: { errors },
  } = useForm<ArticleFormValues>({
    resolver: zodResolver(articleSchema),
    defaultValues: {
      articleCategoryId: initial?.articleCategoryId ?? "",
      title: initial?.title ?? "",
      slug: initial?.slug ?? "",
      excerpt: initial?.excerpt ?? "",
      body: initial?.body ?? "",
      coverMediaId: initial?.cover?.id ?? "",
      status: initial?.status ?? "draft",
      publishedAt: initial?.publishedAt ? initial.publishedAt.slice(0, 16) : "",
    },
  });

  const { field: coverField } = useController({ name: "coverMediaId", control });
  const status = watch("status");
  const currentBody = watch("body") || "";

  // Handle uploading multiple image sections for article
  const handleSectionImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    setUploadError(null);
    setIsUploadingSections(true);

    const newItems: ArticleSectionItem[] = [];

    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      try {
        const media = await uploadMedia(file, "article-cover");
        newItems.push({
          id: media.id,
          url: media.url,
          title: "",
          description: "",
        });
      } catch (err: unknown) {
        setUploadError(getApiErrorMessage(err));
      }
    }

    setSectionItems((prev) => [...prev, ...newItems]);
    setIsUploadingSections(false);
    e.target.value = "";
  };

  // Update title or description for a section item
  const handleItemChange = (id: string, field: "title" | "description", value: string) => {
    setSectionItems((prev) =>
      prev.map((item) => (item.id === id ? { ...item, [field]: value } : item))
    );
  };

  // Insert image + title + long description into the article body
  const handleInsertSectionToBody = (item: ArticleSectionItem) => {
    const sectionTitle = item.title.trim() ? `### ${item.title.trim()}\n\n` : "";
    const imageMarkdown = `![${item.title.trim() || "Gambar Artikel"}](${item.url})\n\n`;
    const sectionDesc = item.description.trim() ? `${item.description.trim()}\n\n` : "";

    const combinedContent = `\n\n${sectionTitle}${imageMarkdown}${sectionDesc}`;
    
    // Append to article body text
    setValue("body", currentBody + combinedContent, { shouldValidate: true, shouldDirty: true });
  };

  // Remove section item card
  const handleRemoveSectionItem = (id: string) => {
    setSectionItems((prev) => prev.filter((item) => item.id !== id));
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
      <CategorySelect
        label="Kategori Artikel *"
        endpoint="/admin/article-categories"
        {...register("articleCategoryId")}
        error={errors.articleCategoryId?.message}
      />

      <div>
        <Label htmlFor="title">Judul Utama Artikel *</Label>
        <Input id="title" {...register("title")} error={errors.title?.message} />
      </div>

      <div>
        <Label htmlFor="slug">Slug URL (opsional)</Label>
        <Input id="slug" {...register("slug")} error={errors.slug?.message} />
      </div>

      <div>
        <Label htmlFor="excerpt">Kutipan Singkat (Excerpt)</Label>
        <Textarea id="excerpt" {...register("excerpt")} error={errors.excerpt?.message} />
      </div>

      {/* Main Article Body Textarea */}
      <div>
        <div className="flex items-center justify-between mb-1">
          <Label htmlFor="body">Isi Artikel Utama *</Label>
          <span className="text-label-sm text-on-surface-variant font-medium">
            Gabungan Teks &amp; Bagian Gambar Bergambar
          </span>
        </div>
        <Textarea
          id="body"
          {...register("body")}
          error={errors.body?.message}
          className="min-h-[280px] font-mono text-body-sm leading-relaxed"
          placeholder="Tuliskan teks pembuka artikel di sini. Kemudian gunakan pembuat Bagian Gambar di bawah untuk memasukkan Foto + Penjelasan Panjang di tengah artikel..."
        />
      </div>

      {/* Multiple Image Sections & Long Explanations Builder */}
      <div className="rounded-2xl border-2 border-emerald-900/15 bg-emerald-900/5 p-4 space-y-4 shadow-2xs">
        <div className="flex items-center justify-between">
          <div>
            <Label className="text-body-md font-bold text-[#004b36]">
              📸 Pembuat Bagian Gambar &amp; Penjelasan Panjang (Tengah Artikel)
            </Label>
            <p className="text-label-sm text-on-surface-variant">
              Upload foto kegiatan, tuliskan penjelasan panjang beberapa paragraf, lalu sisipkan ke tengah artikel.
            </p>
          </div>
          <span className="text-label-sm font-bold text-[#004b36] bg-emerald-100 px-3 py-1 rounded-full border border-emerald-200 shrink-0">
            {sectionItems.length} bagian disiapkan
          </span>
        </div>

        <div className="rounded-xl border-2 border-dashed border-[#004b36]/30 bg-white p-4 text-center hover:bg-emerald-50/50 transition-colors">
          <input
            type="file"
            id="article-section-image-input"
            multiple
            accept="image/jpeg,image/png,image/webp,image/gif"
            onChange={handleSectionImageUpload}
            className="hidden"
            disabled={isUploadingSections || loading}
          />
          <label htmlFor="article-section-image-input" className="cursor-pointer flex flex-col items-center justify-center space-y-1 py-1">
            <span className="material-symbols-outlined text-[36px] text-[#004b36]">add_photo_alternate</span>
            <span className="text-body-md font-bold text-[#004b36]">
              + Upload Foto &amp; Buat Bagian Penjelasan Panjang
            </span>
            <span className="text-label-sm text-on-surface-variant">
              Bisa pilih beberapa foto sekaligus (JPG, PNG, WebP, GIF)
            </span>
          </label>
        </div>

        {isUploadingSections && (
          <div className="flex items-center justify-center gap-2 py-2 text-body-sm text-[#004b36] font-semibold">
            <Spinner className="w-4 h-4" />
            Mengunggah gambar bagian artikel...
          </div>
        )}

        {uploadError && <p className="text-label-sm text-rose-600 font-semibold">{uploadError}</p>}

        {/* List of Uploaded Section Cards with Title, Long Description & Insert Button */}
        {sectionItems.length > 0 && (
          <div className="space-y-4 pt-2">
            {sectionItems.map((item, idx) => (
              <div key={item.id || idx} className="flex flex-col md:flex-row items-start gap-4 p-4 rounded-xl border border-outline/15 bg-white shadow-xs">
                <img
                  src={item.url}
                  alt={item.title || "Preview"}
                  className="w-full md:w-36 h-36 object-cover rounded-xl shrink-0 border border-outline/10 bg-slate-100 shadow-2xs"
                />
                
                <div className="flex-1 w-full space-y-3">
                  <div>
                    <Label className="text-label-sm font-bold text-[#004b36] mb-1 block">
                      Sub-Judul Bagian #{idx + 1} (Opsional)
                    </Label>
                    <Input
                      type="text"
                      value={item.title}
                      onChange={(e) => handleItemChange(item.id, "title", e.target.value)}
                      placeholder="Contoh: Sesi Penyampaian Materi Oleh Ustadz..."
                      className="text-body-sm"
                    />
                  </div>

                  <div>
                    <Label className="text-label-sm font-bold text-[#004b36] mb-1 block">
                      Penjelasan Panjang Paragraf Gambar #{idx + 1}
                    </Label>
                    <Textarea
                      value={item.description}
                      onChange={(e) => handleItemChange(item.id, "description", e.target.value)}
                      placeholder="Tuliskan penjelasan detail panjang di sini. Contoh: Pada sesi ini, pemateri menyampaikan topik penting mengenai peran generasi muda dalam merawat tradisi dan membangun ukhuwah..."
                      className="min-h-[90px] text-body-sm leading-relaxed"
                    />
                  </div>

                  <div className="flex items-center justify-between pt-1">
                    <Button
                      type="button"
                      size="sm"
                      className="bg-[#004b36] text-white hover:bg-[#003828] text-xs font-bold px-4 py-2"
                      onClick={() => handleInsertSectionToBody(item)}
                      title="Sisipkan foto + penjelasan panjang ini ke dalam teks artikel"
                    >
                      <span className="material-symbols-outlined text-[16px] mr-1">post_add</span>
                      Sisipkan Bagian Bergambar Ini ke Artikel
                    </Button>

                    <Button
                      type="button"
                      size="sm"
                      variant="ghost"
                      className="text-rose-600 hover:bg-rose-50 text-xs"
                      onClick={() => handleRemoveSectionItem(item.id)}
                    >
                      Hapus
                    </Button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="status">Status Publikasi</Label>
          <Select id="status" {...register("status")} options={statusOptions} error={errors.status?.message} />
        </div>
        <div>
          <Label htmlFor="publishedAt">Waktu Publikasi</Label>
          <Input
            id="publishedAt"
            type="datetime-local"
            {...register("publishedAt")}
            disabled={status === "draft" || status === "archived"}
            error={errors.publishedAt?.message}
          />
        </div>
      </div>

      {/* Main Cover Image Field */}
      <MediaReferenceField
        collection="article-cover"
        label="Gambar Cover Utama Artikel"
        currentMedia={initial?.cover ?? null}
        onMediaChange={(id) => coverField.onChange(id ?? "")}
        onUploadingChange={setIsUploadingCover}
        error={errors.coverMediaId?.message}
        disabled={loading}
      />

      <div className="flex justify-end gap-3 pt-4 border-t border-outline/10">
        <Button type="button" variant="outline" onClick={onCancel} disabled={loading}>
          Batal
        </Button>
        <Button type="submit" loading={loading} disabled={isUploadingCover || isUploadingSections || loading}>
          Simpan Artikel
        </Button>
      </div>
    </form>
  );
}
