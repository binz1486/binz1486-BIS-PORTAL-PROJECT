import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { useState } from 'react'
import Login from './components/Login'
import About from './pages/About'
import Faqs from './pages/Faqs'
import Eligibility from './pages/Eligibility'
import './App.css'

function App() {
  const [user, setUser] = useState(null)

  const handleLoginSuccess = (userData) => {
    setUser(userData)
  }

  return (
    <Router>
      <Routes>
        <Route path="/" element={
          !user ? (
            <Login onLoginSuccess={handleLoginSuccess} />
          ) : (
            <Navigate to="/dashboard" />
          )
        } />
        <Route path="/about" element={<About />} />
        <Route path="/faqs" element={<Faqs />} />
        <Route path="/eligibility" element={<Eligibility />} />
        <Route path="/dashboard" element={
          user ? (
            <div className="min-h-screen bg-gray-50 p-8">
              <div className="max-w-4xl mx-auto bg-white shadow rounded-lg p-6 text-center">
                <h1 className="text-3xl font-bold mb-4 text-emerald-800">Welcome, {user.role}!</h1>
                <p className="text-gray-600">You are successfully logged in to your new React Dashboard.</p>
                <div className="mt-8 flex justify-center gap-4">
                  <button 
                    onClick={() => setUser(null)}
                    className="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                  >
                    Logout
                  </button>
                </div>
              </div>
            </div>
          ) : (
            <Navigate to="/" />
          )
        } />
      </Routes>
    </Router>
  )
}

export default App
