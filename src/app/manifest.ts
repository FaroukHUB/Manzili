import type { MetadataRoute } from "next"

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: "Manzili",
    short_name: "Manzili",
    description: "Suivi de finances personnelles",
    start_url: "/dashboard",
    display: "standalone",
    orientation: "portrait",
    background_color: "#f4ede3",
    theme_color: "#8b5e3c",
    categories: ["finance", "productivity"],
    icons: [
      {
        src: "/icon-192.png",
        sizes: "192x192",
        type: "image/png",
        purpose: "any",
      },
      {
        src: "/icon-512.png",
        sizes: "512x512",
        type: "image/png",
        purpose: "maskable",
      },
    ],
  }
}
