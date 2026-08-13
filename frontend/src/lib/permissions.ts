export type PermissionAction = "view" | "create" | "update" | "delete" | "restore";

export function hasPermission(permissions: string[], permission: string): boolean {
  return permissions.includes(permission);
}

export function hasAnyPermission(permissions: string[], required: string[]): boolean {
  return required.some((p) => hasPermission(permissions, p));
}
