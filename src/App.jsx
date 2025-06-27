"use client"

import { useState } from "react"
import Sidebar from "./components/Sidebar"
import Dashboard from "./views/Dashboard"
import Perfil from "./views/Perfil"
import Reportes from "./views/Reportes"
import Evidencias from "./views/Evidencias"
import Archivos from "./views/Archivos"
import "./styles/App.css"

function App() {
  const [activeView, setActiveView] = useState("dashboard")
  const [sidebarOpen, setSidebarOpen] = useState(false)

  const renderView = () => {
    switch (activeView) {
      case "dashboard":
        return <Dashboard />
      case "perfil":
        return <Perfil />
      case "reportes":
        return <Reportes />
      case "evidencias":
        return <Evidencias />
      case "archivos":
        return <Archivos />
      default:
        return <Dashboard />
    }
  }

  return (
    <div className="app">
      <Sidebar
        activeView={activeView}
        setActiveView={setActiveView}
        sidebarOpen={sidebarOpen}
        setSidebarOpen={setSidebarOpen}
      />

      <div className={`main-content ${sidebarOpen ? "sidebar-open" : ""}`}>
        <header className="app-header">
          <button className="menu-toggle" onClick={() => setSidebarOpen(!sidebarOpen)}>
            <span></span>
            <span></span>
            <span></span>
          </button>
          <h1>Reportes Inovación</h1>
          <div className="header-actions">
            <button className="notification-btn">
              <span className="notification-icon">🔔</span>
              <span className="notification-badge">3</span>
            </button>
            <div className="user-avatar">
              <img src="/placeholder.svg?height=40&width=40" alt="Usuario" />
            </div>
          </div>
        </header>

        <main className="app-main">{renderView()}</main>
      </div>
    </div>
  )
}

export default App
