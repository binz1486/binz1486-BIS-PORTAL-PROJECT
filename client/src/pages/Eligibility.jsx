import { useState, useEffect, useRef } from 'react'
import Navbar from '../components/Navbar'
import Footer from '../components/Footer'
import SecondaryNav from '../components/SecondaryNav'

const eligibleCountries = [
  "pakistan", "china", "iran", "turkey", "afghanistan", "bangladesh", 
  "sri lanka", "oman", "spain", "egypt", "panama", "palestine"
]

export default function Eligibility() {
  const [hoveredCountry, setHoveredCountry] = useState('')
  const [mapLoaded, setMapLoaded] = useState(false)
  const mapRef = useRef(null)

  useEffect(() => {
    fetch('/src/assets/map.svg')
      .then(res => res.text())
      .then(svgText => {
        const parser = new DOMParser()
        const svgDoc = parser.parseFromString(svgText, "image/svg+xml")
        const svgElement = svgDoc.documentElement
        
        // Setup SVG
        svgElement.setAttribute('width', '100%')
        svgElement.setAttribute('height', 'auto')
        svgElement.classList.add('w-full', 'h-auto')

        // Apply styles to paths
        const paths = svgElement.querySelectorAll('path')
        paths.forEach(path => {
          const id = (path.id || '').toLowerCase()
          
          if (eligibleCountries.includes(id)) {
            path.style.fill = '#047857' // Green
            path.style.cursor = 'pointer'
            path.classList.add('eligible-path')
          } else {
            path.style.fill = '#e2e8f0' // Light gray
          }

          path.style.stroke = '#ffffff'
          path.style.strokeWidth = '0.5'
          path.style.transition = 'all 0.3s ease'

          path.addEventListener('mouseenter', () => {
            if (path.id) {
              setHoveredCountry(path.id.charAt(0).toUpperCase() + path.id.slice(1))
              if (eligibleCountries.includes(id)) {
                path.style.fill = '#065f46' // Darker green
              } else {
                path.style.fill = '#cbd5e1'
              }
            }
          })

          path.addEventListener('mouseleave', () => {
            setHoveredCountry('')
            if (eligibleCountries.includes(id)) {
              path.style.fill = '#047857'
            } else {
              path.style.fill = '#e2e8f0'
            }
          })
        })

        if (mapRef.current) {
          mapRef.current.innerHTML = ''
          mapRef.current.appendChild(svgElement)
          setMapLoaded(true)
        }
      })
      .catch(err => console.error("Error loading map:", err))
  }, [])

  return (
    <div className="bg-gray-50 font-sans min-h-screen flex flex-col">
      <Navbar />
      <SecondaryNav backTo="/" nextTo="/about" nextLabel="About Us" />

      <div className="container mx-auto px-4 py-6 max-w-5xl relative z-10 flex-grow">
        <h1 className="text-2xl font-bold text-gray-700 mb-8 flex items-center gap-2">
          <i className="fas fa-check-circle"></i> Check Eligibility
        </h1>
        
        {/* World Map Section */}
        <div className="mb-10 bg-white/80 backdrop-blur rounded-2xl shadow-xl overflow-hidden border border-emerald-100 p-4 md:p-8">
          <h2 className="text-xl font-bold text-emerald-700 mb-4 flex items-center gap-2">
            <i className="fas fa-globe-asia animate-pulse"></i> Our Global Reach
          </h2>
          
          <div className="relative group">
            <div 
              ref={mapRef} 
              className={`w-full max-w-4xl mx-auto rounded-xl transition-opacity duration-700 ${mapLoaded ? 'opacity-100' : 'opacity-0'}`}
            >
              <div className="flex items-center justify-center h-[300px]">
                <i className="fas fa-spinner fa-spin text-3xl text-emerald-700"></i>
              </div>
            </div>

            {/* Floating Tooltip */}
            {hoveredCountry && (
              <div className="absolute top-0 left-1/2 -translate-x-1/2 -mt-4 bg-emerald-900 text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg pointer-events-none animate-bounce">
                {hoveredCountry}
              </div>
            )}
            
            <div className="mt-4 flex flex-wrap justify-center gap-4 text-xs font-semibold text-gray-500">
               <div className="flex items-center gap-2">
                 <span className="w-3 h-3 bg-emerald-700 rounded-full"></span> Eligible Countries
               </div>
               <div className="flex items-center gap-2">
                 <span className="w-3 h-3 bg-gray-200 rounded-full"></span> Non-Eligible Countries
               </div>
            </div>
          </div>
        </div>

        <div className="bg-white p-8 rounded-lg shadow-lg border-t-4 border-emerald-500">
          <p className="text-gray-600 mb-8 leading-relaxed text-lg text-center font-medium">
            Welcome to the Binz International Scholarship (BIS) Eligibility Checker. 
            Selected countries are marked <span className="text-emerald-700 font-bold">Green</span> on the map.
          </p>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <EligibilityCard title="Bachelor" age="23" cgpa="3.2" requirements="Intermediate" />
            <EligibilityCard title="Master" age="26" cgpa="3.4" requirements="Bachelor" />
            <EligibilityCard title="PhD" age="30" cgpa="3.5" requirements="Master" />
          </div>

          <div className="mt-10 p-6 bg-emerald-50 border-l-4 border-emerald-500 rounded-xl">
             <h4 className="font-extrabold text-emerald-900 mb-2 flex items-center gap-2">
               <i className="fas fa-info-circle"></i> Important Note:
             </h4>
             <p className="text-emerald-800 leading-relaxed">
               Eligibility is based on both Merit (70%) and Need (30%). A valid passport is mandatory for all international applications.
               Applicants with PhD as their last education are currently not eligible for further funding.
             </p>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  )
}

function EligibilityCard({ title, age, cgpa, requirements }) {
  return (
    <div className="p-6 bg-emerald-50/30 rounded-xl border border-emerald-100 hover:shadow-xl hover:-translate-y-1 transition duration-300 group">
      <h3 className="text-xl font-bold text-emerald-800 mb-4 group-hover:text-emerald-600 transition-colors">{title}</h3>
      <ul className="space-y-3 text-gray-600 text-sm">
        <li className="flex items-center gap-3"><i className="fas fa-user-clock text-amber-500"></i>Max Age: <b>{age} years</b></li>
        <li className="flex items-center gap-3"><i className="fas fa-graduation-cap text-amber-500"></i>Min CGPA: <b>{cgpa}</b></li>
        <li className="flex items-center gap-3"><i className="fas fa-scroll text-amber-500"></i>{requirements} degree</li>
      </ul>
    </div>
  )
}

