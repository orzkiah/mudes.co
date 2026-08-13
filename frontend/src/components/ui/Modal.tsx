"use client";

import { useEffect, useRef } from "react";
import { cn } from "@/lib/cn";
import { Icon } from "./Icon";
import type { ReactNode } from "react";

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  title?: ReactNode;
  description?: ReactNode;
  children: ReactNode;
  className?: string;
  size?: "sm" | "md" | "lg" | "xl";
  closeOnOutsideClick?: boolean;
}

export function Modal({
  isOpen,
  onClose,
  title,
  description,
  children,
  className,
  size = "md",
  closeOnOutsideClick = false,
}: ModalProps) {
  const ref = useRef<HTMLDialogElement>(null);

  useEffect(() => {
    const dialog = ref.current;
    if (!dialog) return;
    if (isOpen && !dialog.open) {
      dialog.showModal();
      document.body.style.overflow = "hidden";
    } else if (!isOpen && dialog.open) {
      dialog.close();
      document.body.style.overflow = "";
    }
    return () => {
      document.body.style.overflow = "";
    };
  }, [isOpen]);

  useEffect(() => {
    const dialog = ref.current;
    if (!dialog) return;
    const handler = () => onClose();
    dialog.addEventListener("close", handler);
    return () => dialog.removeEventListener("close", handler);
  }, [onClose]);

  if (!isOpen) return null;

  const sizes = {
    sm: "max-w-md",
    md: "max-w-lg",
    lg: "max-w-2xl",
    xl: "max-w-4xl",
  };

  return (
    <dialog
      ref={ref}
      className={cn(
        "fixed inset-0 m-auto w-full p-0 rounded-2xl bg-surface-container-lowest shadow-premium border border-outline-variant backdrop:bg-black/40 open:animate-in open:fade-in",
        sizes[size],
        className
      )}
      onClick={(e) => {
        if (closeOnOutsideClick && e.target === ref.current) {
          onClose();
        }
      }}
    >
      <div className="flex flex-col max-h-[90vh]">
        {(title || description) && (
          <div className="flex items-start justify-between gap-4 px-6 py-4 border-b border-outline-variant">
            <div>
              {title && <h2 className="text-headline-sm font-headline-sm text-on-surface">{title}</h2>}
              {description && <p className="text-body-sm text-on-surface-variant mt-1">{description}</p>}
            </div>
            <button
              onClick={onClose}
              className="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-low"
              aria-label="Tutup"
            >
              <Icon name="close" />
            </button>
          </div>
        )}
        <div className="px-6 py-4 overflow-y-auto">{children}</div>
      </div>
    </dialog>
  );
}

interface ConfirmDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title: string;
  description?: string;
  confirmText?: string;
  confirmVariant?: "danger" | "primary";
  cancelText?: string;
  isLoading?: boolean;
}

export function ConfirmDialog({
  isOpen,
  onClose,
  onConfirm,
  title,
  description,
  confirmText = "Ya, lanjutkan",
  confirmVariant = "danger",
  cancelText = "Batal",
  isLoading,
}: ConfirmDialogProps) {
  return (
    <Modal isOpen={isOpen} onClose={onClose} title={title} size="sm">
      <div className="space-y-4">
        {description && <p className="text-body-md text-on-surface-variant">{description}</p>}
        <div className="flex justify-end gap-3">
          <button
            onClick={onClose}
            className="px-4 py-2 rounded-lg text-body-md text-on-surface hover:bg-surface-container-low"
            disabled={isLoading}
          >
            {cancelText}
          </button>
          <button
            onClick={onConfirm}
            disabled={isLoading}
            className={cn(
              "px-4 py-2 rounded-lg text-body-md text-white",
              confirmVariant === "danger" ? "bg-error hover:bg-error/90" : "bg-primary hover:bg-emerald-900"
            )}
          >
            {isLoading ? "Memproses..." : confirmText}
          </button>
        </div>
      </div>
    </Modal>
  );
}
