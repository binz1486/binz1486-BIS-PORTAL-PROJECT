import { Link } from 'react-router-dom'
import { useEffect } from 'react'

export default function SecondaryNav({ backTo, nextTo, backLabel, nextLabel }) {
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }, [])

  return (
    <div className="bg-gray-400 py-2">
      <div className="container mx-auto px-4">
        <div className="flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-0">
          <Link to={backTo || "/"} className="text-gray-800 font-bold hover:text-black flex items-center w-full sm:w-auto justify-center sm:justify-start">
            <i className="fas fa-arrow-left mr-2"></i>
            <span className="animated-underline">{backLabel || "Go to Home"}</span>
          </Link>
          {nextTo && (
            <Link to={nextTo} className="text-gray-800 font-bold hover:text-black flex items-center w-full sm:w-auto justify-center sm:justify-end">
              <i className="fas fa-question-circle mr-2"></i>
              <span className="animated-underline">{nextLabel}</span>
            </Link>
          )}
        </div>
      </div>
    </div>
  )
}
