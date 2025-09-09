/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_BASE: string
  readonly VITE_SITE_BASE: string
  readonly VITE_ASSETS_BASE: string
  // more env variables...
}

// ImportMeta is already defined globally by Vite
