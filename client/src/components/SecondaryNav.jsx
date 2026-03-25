import { Link } from 'react-router-dom'
import { useEffect } from 'react'

export default function SecondaryNav({ backTo, nextTo, backLabel, nextLabel }) {
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }, [])

  return (
    <div className="bg-emerald-50 border-b border-emerald-100 py-2.5">
      <div className="container mx-auto px-6">
        <div className="flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-0">
          <Link to={backTo || "/"} className="text-emerald-800 font-semibold hover:text-emerald-600 flex items-center gap-2 text-sm transition-colors group">
            <i className="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
            <span>{backLabel || "Go to Home"}</span>
          </Link>
          {nextTo && (
            <Link to={nextTo} className="text-emerald-800 font-semibold hover:text-emerald-600 flex items-center gap-2 text-sm transition-colors group">
              <span>{nextLabel}</span>
              <i className="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
            </Link>
          )}
        </div>
      </div>
    </div>
  )
}
