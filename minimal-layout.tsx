"use client";

import { useState } from "react";
import { FileText, FolderOpen, Menu, Search, Bell, User } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Separator } from "@/components/ui/separator";
import { cn } from "@/lib/utils";

// Importar nuestros componentes
import Reportes from "./src/views/Reportes";
import Archivos from "./src/views/Archivos";

export default function MinimalLayout() {
  const [activeTab, setActiveTab] = useState("reportes");
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const navigationItems = [
    {
      id: "reportes",
      label: "Reportes",
      icon: FileText,
      count: 24,
    },
    {
      id: "archivos",
      label: "Archivos",
      icon: FolderOpen,
      count: 156,
    },
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header minimalista */}
      <header className="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div className="flex items-center justify-between px-6 py-4">
          <div className="flex items-center space-x-4">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setSidebarOpen(!sidebarOpen)}
              className="lg:hidden"
            >
              <Menu className="w-5 h-5" />
            </Button>
            <div className="flex items-center space-x-3">
              <div className="w-8 h-8 bg-gradient-to-br from-slate-200 to-slate-300 rounded-lg flex items-center justify-center">
                <FileText className="w-4 h-4 text-slate-600" />
              </div>
              <h1 className="text-xl font-semibold text-gray-900">
                Reportes de Innovación
              </h1>
            </div>
          </div>

          <div className="flex items-center space-x-4">
            <div className="hidden md:block">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <Input
                  placeholder="Buscar reportes..."
                  className="pl-10 w-80 bg-gray-50 border-gray-200 focus:bg-white transition-colors"
                />
              </div>
            </div>
            <Button variant="ghost" size="sm" className="relative">
              <Bell className="w-5 h-5" />
              <Badge className="absolute -top-2 -right-2 w-5 h-5 rounded-full p-0 flex items-center justify-center text-xs">
                3
              </Badge>
            </Button>
            <Avatar className="w-8 h-8">
              <AvatarImage src="/placeholder-user.jpg" />
              <AvatarFallback className="bg-gradient-to-br from-slate-200 to-slate-300 text-slate-600">
                <User className="w-4 h-4" />
              </AvatarFallback>
            </Avatar>
          </div>
        </div>
      </header>

      <div className="flex">
        {/* Sidebar minimalista */}
        <aside
          className={cn(
            "bg-white border-r border-gray-200 transition-all duration-300 ease-in-out",
            sidebarOpen ? "w-64" : "w-0 lg:w-16",
            "lg:block fixed lg:relative z-40 h-screen lg:h-auto"
          )}
        >
          <div className="p-6 space-y-6">
            {/* Navigation */}
            <nav className="space-y-2">
              {navigationItems.map((item) => (
                <button
                  key={item.id}
                  onClick={() => setActiveTab(item.id)}
                  className={cn(
                    "w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200",
                    activeTab === item.id
                      ? "bg-slate-100 text-slate-700 border border-slate-200"
                      : "text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                  )}
                >
                  <item.icon className="w-5 h-5 flex-shrink-0" />
                  {sidebarOpen && (
                    <>
                      <span className="flex-1 text-left">{item.label}</span>
                      <Badge
                        variant={
                          activeTab === item.id ? "default" : "secondary"
                        }
                        className="ml-auto"
                      >
                        {item.count}
                      </Badge>
                    </>
                  )}
                </button>
              ))}
            </nav>

            {sidebarOpen && (
              <>
                <Separator />

                {/* User stats */}
                <div className="space-y-3">
                  <h3 className="text-sm font-medium text-gray-900">
                    Estadísticas
                  </h3>
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Total archivos</span>
                      <span className="font-medium">180</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Este mes</span>
                      <span className="font-medium text-emerald-500">+12</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Almacenamiento</span>
                      <span className="font-medium">2.4 GB</span>
                    </div>
                  </div>
                </div>
              </>
            )}
          </div>
        </aside>

        {/* Main content */}
        <main className="flex-1 min-h-screen">
          <div className="p-6">
            {/* Content header */}
            <div className="mb-6">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-2xl font-bold text-gray-900">
                    {
                      navigationItems.find((item) => item.id === activeTab)
                        ?.label
                    }
                  </h2>
                  <p className="text-gray-600 mt-1">
                    Gestiona y organiza tus{" "}
                    {activeTab === "reportes" ? "reportes" : "archivos"} de
                    manera eficiente
                  </p>
                </div>
              </div>
            </div>

            {/* Content area */}
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm">
              {activeTab === "reportes" && <Reportes />}
              {activeTab === "archivos" && <Archivos />}
            </div>
          </div>
        </main>
      </div>

      {/* Overlay para mobile */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}
    </div>
  );
}
