"use client";

import { Header } from "./Header";

export function AdminTopbar({ onMenuClick }: { onMenuClick?: () => void }) {
  return <Header onMenuClick={onMenuClick ?? (() => {})} />;
}
