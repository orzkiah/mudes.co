"use client";

import { useState, useRef, useEffect } from "react";
import { Modal } from "./Modal";
import { Button } from "./Button";

interface ImageCropperModalProps {
  isOpen: boolean;
  onClose: () => void;
  imageSrc: string | null;
  fileName: string;
  isCircular?: boolean; // Circular for avatars, rectangular for covers
  aspectRatio?: number; // e.g. 16/9 for covers, 1 for square
  onCropComplete: (file: File) => void;
}

export function ImageCropperModal({
  isOpen,
  onClose,
  imageSrc,
  fileName,
  isCircular = false,
  aspectRatio = 1,
  onCropComplete,
}: ImageCropperModalProps) {
  const [zoom, setZoom] = useState(1);
  const [offsetX, setOffsetX] = useState(0);
  const [offsetY, setOffsetY] = useState(0);
  const [isDragging, setIsDragging] = useState(false);
  const dragStart = useRef({ x: 0, y: 0 });
  const [imgNaturalSize, setImgNaturalSize] = useState({ width: 0, height: 0 });

  const containerRef = useRef<HTMLDivElement>(null);
  const viewportWidth = 280;
  const viewportHeight = viewportWidth / aspectRatio;

  // Reset states when a new image is loaded
  useEffect(() => {
    if (imageSrc) {
      setZoom(1);
      setOffsetX(0);
      setOffsetY(0);
      const img = new Image();
      img.src = imageSrc;
      img.onload = () => {
        setImgNaturalSize({ width: img.naturalWidth, height: img.naturalHeight });
      };
    }
  }, [imageSrc]);

  if (!imageSrc) return null;

  // Cover image to the viewport size initially (forces image to fill viewport)
  const { width: imgW, height: imgH } = imgNaturalSize;
  const coverScale = imgW && imgH ? Math.max(viewportWidth / imgW, viewportHeight / imgH) : 1;
  const baseW = imgW * coverScale;
  const baseH = imgH * coverScale;
  
  const displayW = baseW * zoom;
  const displayH = baseH * zoom;

  const handleMouseDown = (e: React.MouseEvent) => {
    e.preventDefault();
    setIsDragging(true);
    dragStart.current = { x: e.clientX - offsetX, y: e.clientY - offsetY };
  };

  const handleMouseMove = (e: React.MouseEvent) => {
    if (!isDragging) return;
    const newX = e.clientX - dragStart.current.x;
    const newY = e.clientY - dragStart.current.y;
    
    // Constraint panning so image doesn't completely leave the viewport
    const maxPosX = Math.max(0, (displayW - viewportWidth) / 2);
    const maxPosY = Math.max(0, (displayH - viewportHeight) / 2);
    
    setOffsetX(Math.max(-maxPosX, Math.min(maxPosX, newX)));
    setOffsetY(Math.max(-maxPosY, Math.min(maxPosY, newY)));
  };

  const handleMouseUpOrLeave = () => {
    setIsDragging(false);
  };

  // Touch Support for Mobile devices
  const handleTouchStart = (e: React.TouchEvent) => {
    const touch = e.touches[0];
    if (touch) {
      setIsDragging(true);
      dragStart.current = { x: touch.clientX - offsetX, y: touch.clientY - offsetY };
    }
  };

  const handleTouchMove = (e: React.TouchEvent) => {
    if (!isDragging) return;
    const touch = e.touches[0];
    if (touch) {
      const newX = touch.clientX - dragStart.current.x;
      const newY = touch.clientY - dragStart.current.y;
      
      const maxPosX = Math.max(0, (displayW - viewportWidth) / 2);
      const maxPosY = Math.max(0, (displayH - viewportHeight) / 2);
      
      setOffsetX(Math.max(-maxPosX, Math.min(maxPosX, newX)));
      setOffsetY(Math.max(-maxPosY, Math.min(maxPosY, newY)));
    }
  };

  const handleCrop = () => {
    const img = new Image();
    img.src = imageSrc;
    img.onload = () => {
      const canvas = document.createElement("canvas");
      
      // Fixed high-quality output size (400px for avatar, 800px for covers)
      const outputW = isCircular ? 400 : 800;
      const outputH = outputW / aspectRatio;
      
      canvas.width = outputW;
      canvas.height = outputH;
      const ctx = canvas.getContext("2d");
      if (!ctx) return;

      // Fill canvas background with white to avoid transparent-to-black artifacts if any gap exists
      ctx.fillStyle = "#ffffff";
      ctx.fillRect(0, 0, outputW, outputH);

      // The scale factor from screen viewport to high-res canvas output
      const canvasScale = outputW / viewportWidth;

      // Scale the displayed dimensions up to canvas resolution
      const canvasW = displayW * canvasScale;
      const canvasH = displayH * canvasScale;

      // Translate offsets from screen viewport up to canvas resolution
      const canvasOffsetX = offsetX * canvasScale;
      const canvasOffsetY = offsetY * canvasScale;

      // Calculate top-left destination coordinates on the canvas
      const dx = (outputW - canvasW) / 2 + canvasOffsetX;
      const dy = (outputH - canvasH) / 2 + canvasOffsetY;

      // Draw the image onto the high-res canvas at the computed position and size
      ctx.drawImage(img, dx, dy, canvasW, canvasH);

      canvas.toBlob(
        (blob) => {
          if (blob) {
            const croppedFile = new File([blob], fileName, { type: "image/jpeg" });
            onCropComplete(croppedFile);
            onClose();
          }
        },
        "image/jpeg",
        0.95
      );
    };
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Sesuaikan &amp; Potong Foto" closeOnOutsideClick={false}>
      <div className="space-y-6 flex flex-col items-center">
        <p className="text-body-sm text-on-surface-variant text-center max-w-sm">
          Seret foto untuk memposisikan, gunakan slider untuk memperbesar/memperkecil agar pas dengan area cetak.
        </p>

        {/* Crop Viewport Box Container */}
        <div
          ref={containerRef}
          onMouseDown={handleMouseDown}
          onMouseMove={handleMouseMove}
          onMouseUp={handleMouseUpOrLeave}
          onMouseLeave={handleMouseUpOrLeave}
          onTouchStart={handleTouchStart}
          onTouchMove={handleTouchMove}
          onTouchEnd={handleMouseUpOrLeave}
          className="relative overflow-hidden bg-slate-900 border border-outline/25 shadow-inner cursor-move select-none"
          style={{ width: "320px", height: "320px", display: "flex", alignItems: "center", justifyContent: "center" }}
        >
          {/* Draggable image */}
          <img
            src={imageSrc}
            alt="To crop"
            draggable={false}
            className="absolute max-w-none transition-transform duration-75 origin-center pointer-events-none"
            style={{
              width: `${displayW}px`,
              height: `${displayH}px`,
              transform: `translate(${offsetX}px, ${offsetY}px)`,
            }}
          />

          {/* Transparent / Darkened Mask Overlay */}
          <div className="absolute inset-0 pointer-events-none flex items-center justify-center">
            {/* Viewport ring spacer */}
            <div
              className={`border-[2px] border-amber-400 shadow-[0_0_0_9999px_rgba(15,23,42,0.7)]`}
              style={{
                width: `${viewportWidth}px`,
                height: `${viewportHeight}px`,
                borderRadius: isCircular ? "50%" : "16px",
              }}
            />
          </div>
        </div>

        {/* Zoom Controls */}
        <div className="w-full max-w-xs space-y-2">
          <div className="flex items-center justify-between text-label-sm font-bold text-[#004b36]">
            <span>Zoom</span>
            <span>{Math.round(zoom * 100)}%</span>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={() => setZoom(Math.max(1, zoom - 0.1))}
              className="w-8 h-8 rounded-full bg-emerald-100 text-[#004b36] hover:bg-[#004b36] hover:text-white flex items-center justify-center transition-colors font-bold text-lg"
            >
              -
            </button>
            <input
              type="range"
              min="1"
              max="3"
              step="0.05"
              value={zoom}
              onChange={(e) => setZoom(parseFloat(e.target.value))}
              className="flex-1 accent-[#004b36] h-1.5 bg-outline/20 rounded-lg cursor-pointer"
            />
            <button
              onClick={() => setZoom(Math.min(3, zoom + 0.1))}
              className="w-8 h-8 rounded-full bg-emerald-100 text-[#004b36] hover:bg-[#004b36] hover:text-white flex items-center justify-center transition-colors font-bold text-lg"
            >
              +
            </button>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex justify-end gap-3 w-full border-t border-outline/10 pt-4">
          <Button type="button" variant="outline" onClick={onClose}>
            Batal
          </Button>
          <Button
            type="button"
            onClick={handleCrop}
            className="bg-[#004b36] text-white hover:bg-[#003828] font-bold"
          >
            Potong &amp; Simpan
          </Button>
        </div>
      </div>
    </Modal>
  );
}
