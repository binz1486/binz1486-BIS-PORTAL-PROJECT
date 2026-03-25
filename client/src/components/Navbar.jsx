import { Link, useLocation } from 'react-router-dom'
import logo from '../assets/logo.png'

const navLinks = [
  { to: '/eligibility', label: 'Eligibility' },
  { to: '/faqs', label: 'FAQs' },
  { to: '/about', label: 'About' },
]

export default function Navbar() {
  const location = useLocation()
  return (
    <nav className="bg-emerald-900 shadow-lg py-0 sticky top-0 z-40">
      <div className="mx-auto max-w-7xl flex items-center justify-between px-6 h-16">
        {/* Logo + Brand */}
        <Link to="/" className="flex items-center gap-3 group">
          <img src={logo} alt="Binz Logo" className="h-9 w-9 rounded-full shadow filter brightness-0 invert opacity-90 group-hover:opacity-100 transition" />
          <span className="text-white font-extrabold text-lg tracking-wide hidden sm:inline leading-tight">
            Binz International <span className="text-emerald-300">Scholarship</span>
          </span>
        </Link>

        {/* Nav Links */}
        <div className="flex items-center gap-1">
          {navLinks.map(link => (
            <Link
              key={link.to}
              to={link.to}
              className={`px-4 py-2 rounded-full text-sm font-semibold uppercase tracking-widest transition-all duration-200
                ${location.pathname === link.to
                  ? 'bg-emerald-700 text-white'
                  : 'text-emerald-100 hover:bg-emerald-800 hover:text-white'
                }`}
            >
              {link.label}
            </Link>
          ))}
          <Link
            to="/login"
            className="ml-3 bg-white text-emerald-900 hover:bg-emerald-50 px-5 py-2 rounded-full text-sm font-bold uppercase tracking-widest transition-all shadow-sm hover:shadow-md"
          >
            Sign In
          </Link>
        </div>
      </div>
    </nav>
  )
}
