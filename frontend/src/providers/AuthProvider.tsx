"use client";

import { createContext, useContext, useCallback, ReactNode } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { api, setAuthToken, clearAuthToken, getAuthToken } from "@/lib/api";
import type { ApiResponse, AuthUser } from "@/lib/api-types";

interface AuthContextValue {
  user: AuthUser | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();

  const { data: user, isLoading } = useQuery<AuthUser | null>({
    queryKey: ["auth", "me"],
    queryFn: async () => {
      const token = getAuthToken();
      if (!token) return null;
      try {
        const response = await api.get<ApiResponse<AuthUser>>("/auth/me");
        return response.data.data;
      } catch (error) {
        clearAuthToken();
        return null;
      }
    },
    refetchOnWindowFocus: false,
    retry: false,
  });

  const loginMutation = useMutation({
    mutationFn: async ({ email, password }: { email: string; password: string }) => {
      const response = await api.post<ApiResponse<{ user: AuthUser; token: string }>>("/auth/token/login", { email, password });
      const { user, token } = response.data.data;
      setAuthToken(token);
      return user;
    },
    onSuccess: (data) => {
      queryClient.setQueryData(["auth", "me"], data);
    },
  });

  const logoutMutation = useMutation({
    mutationFn: async () => {
      try {
        await api.post("/auth/token/logout");
      } catch {
        // ignore errors during logout
      } finally {
        clearAuthToken();
      }
    },
    onSuccess: () => {
      queryClient.setQueryData(["auth", "me"], null);
      queryClient.clear();
      window.location.href = "/login";
    },
  });

  const login = useCallback(
    async (email: string, password: string) => {
      await loginMutation.mutateAsync({ email, password });
    },
    [loginMutation]
  );

  const logout = useCallback(async () => {
    await logoutMutation.mutateAsync();
  }, [logoutMutation]);

  return (
    <AuthContext.Provider
      value={{
        user: user ?? null,
        isLoading: isLoading || loginMutation.isPending || logoutMutation.isPending,
        login,
        logout,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within AuthProvider");
  return context;
}
