import { useMemo } from "react";
import { useAuth } from "@/providers/AuthProvider";
import { hasPermission, type PermissionAction } from "@/lib/permissions";

export function usePermission() {
  const { user, isLoading } = useAuth();
  const permissions = useMemo(() => user?.permissions ?? [], [user]);

  const can = useMemo(() => {
    return (resource: string, action: PermissionAction) =>
      !isLoading && hasPermission(permissions, `${resource}.${action}`);
  }, [permissions, isLoading]);

  return { can, permissions, isLoading };
}
