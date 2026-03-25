import { Link } from 'react-router-dom'
import logo from '../assets/logo.png'

export default function Navbar() {
  return (
    <nav className="bg-amber-gradient shadow-md py-5">
      <div className="mx-auto max-w-6xl flex items-center justify-between gap-x-8 px-4">
        <div className="flex items-center w-full md:w-auto justify-center md:justify-start">
          <img src={logo} alt="Binz Logo" className="h-12 w-12 md:h-14 md:w-14 mr-3 rounded-full shadow filter brightness-0 invert" />
          <h1 className="text-xl md:text-3xl font-bold text-white text-center md:text-left">
            Binz International Scholarship Portal
          </h1>
        </div>
      </div>
    </nav>
  )
}
