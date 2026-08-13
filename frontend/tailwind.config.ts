import type { Config } from "tailwindcss";

/**
 * Design tokens mirrored 1:1 from the Stitch "Islamic Modernism" design
 * system (stitch_mudes_premium_design_system). Single source of truth —
 * do not add ad-hoc values outside this set.
 */
const config: Config = {
  darkMode: "class",
  content: ["./src/**/*.{ts,tsx}"],
  theme: {
    extend: {
      colors: {
        "on-surface-variant": "#404944",
        "on-primary-fixed-variant": "#0b513d",
        surface: "#f8f9ff",
        "on-surface": "#0b1c30",
        "inverse-surface": "#213145",
        "inverse-on-surface": "#eaf1ff",
        warning: "#F59E0B",
        "on-secondary-fixed": "#241a00",
        "primary-container": "#064e3b",
        "inverse-primary": "#95d3ba",
        "surface-tint": "#2b6954",
        "on-tertiary": "#ffffff",
        "tertiary-container": "#6b342d",
        "surface-muted": "#F8FAFC",
        "gold-light": "#F9F1D7",
        "surface-container": "#e5eeff",
        "surface-container-lowest": "#ffffff",
        "surface-dim": "#cbdbf5",
        "on-background": "#0b1c30",
        "emerald-900": "#064E3B",
        "secondary-fixed-dim": "#e9c349",
        "on-tertiary-fixed-variant": "#6e372f",
        outline: "#707974",
        "error-container": "#ffdad6",
        "surface-base": "#FFFFFF",
        "surface-bright": "#f8f9ff",
        "emerald-600": "#059669",
        "tertiary-fixed-dim": "#ffb4a9",
        "on-secondary": "#ffffff",
        "primary-fixed-dim": "#95d3ba",
        "on-primary": "#ffffff",
        "on-tertiary-container": "#ea9e93",
        "on-error-container": "#93000a",
        "on-error": "#ffffff",
        success: "#10B981",
        "outline-variant": "#bfc9c3",
        "secondary-fixed": "#ffe088",
        "surface-variant": "#d3e4fe",
        primary: "#003527",
        "on-primary-fixed": "#002117",
        "tertiary-fixed": "#ffdad5",
        "surface-container-low": "#eff4ff",
        "on-secondary-fixed-variant": "#574500",
        secondary: "#735c00",
        "secondary-container": "#fed65b",
        tertiary: "#4f1f19",
        "gold-accent": "#D4AF37",
        "on-secondary-container": "#745c00",
        "on-primary-container": "#80bea6",
        "surface-container-high": "#dce9ff",
        "surface-container-highest": "#d3e4fe",
        "primary-fixed": "#b0f0d6",
        "on-tertiary-fixed": "#380d08",
        background: "#f8f9ff",
        error: "#EF4444",
      },
      borderRadius: {
        DEFAULT: "8px",
        lg: "16px",
        xl: "24px",
        full: "9999px",
      },
      boxShadow: {
        premium: "0px 1px 2px rgba(0,0,0,0.05), 0px 10px 15px -3px rgba(0,0,0,0.03)",
        "premium-hover": "0px 4px 6px -1px rgba(0,0,0,0.1), 0px 20px 25px -5px rgba(0,0,0,0.05)",
      },
      spacing: {
        unit: "4px",
        xs: "4px",
        sm: "8px",
        md: "16px",
        lg: "24px",
        xl: "32px",
        "2xl": "48px",
        "3xl": "64px",
        gutter: "24px",
        "container-max": "1280px",
      },
      maxWidth: {
        "container-max": "1280px",
      },
      fontFamily: {
        "display-lg": ["var(--font-hanken)", "sans-serif"],
        "display-lg-mobile": ["var(--font-hanken)", "sans-serif"],
        "headline-md": ["var(--font-hanken)", "sans-serif"],
        "headline-sm": ["var(--font-hanken)", "sans-serif"],
        "body-lg": ["var(--font-inter)", "sans-serif"],
        "body-md": ["var(--font-inter)", "sans-serif"],
        "body-sm": ["var(--font-inter)", "sans-serif"],
        "label-md": ["var(--font-inter)", "sans-serif"],
        "label-sm": ["var(--font-inter)", "sans-serif"],
      },
      fontSize: {
        "display-lg": [
          "48px",
          { lineHeight: "56px", fontWeight: "700", letterSpacing: "-0.02em" },
        ],
        "display-lg-mobile": [
          "36px",
          { lineHeight: "44px", fontWeight: "700", letterSpacing: "-0.02em" },
        ],
        "headline-md": [
          "30px",
          { lineHeight: "38px", fontWeight: "600", letterSpacing: "-0.01em" },
        ],
        "headline-sm": ["24px", { lineHeight: "32px", fontWeight: "600" }],
        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
        "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }],
        "label-md": [
          "14px",
          { lineHeight: "20px", fontWeight: "600", letterSpacing: "0.01em" },
        ],
        "label-sm": [
          "12px",
          { lineHeight: "16px", fontWeight: "500", letterSpacing: "0.02em" },
        ],
      },
    },
  },
  plugins: [],
};

import tailwindcssAnimate from "tailwindcss-animate";

config.plugins = [tailwindcssAnimate];

export default config;
