import type { Metadata } from "next";
import "./globals.css";
import "./custom-styles.css";

export const metadata: Metadata = {
  title: "Sistema de Reportes de Innovación",
  description: "Sistema completo para gestión de reportes",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}
