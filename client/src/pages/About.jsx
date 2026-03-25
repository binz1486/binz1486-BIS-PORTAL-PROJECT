import { useEffect, useState } from 'react'
import Navbar from '../components/Navbar'
import Footer from '../components/Footer'
import SecondaryNav from '../components/SecondaryNav'
import binzPic from '../assets/binz-pic.jpg'

export default function About() {
  const [counts, setCounts] = useState({ applied: 0, visits: 0, awarded: 0 })

  useEffect(() => {
    const targets = { applied: 31040, visits: 125000, awarded: 1050 }
    const duration = 2500
    const frameDuration = 1000 / 60
    const totalFrames = Math.round(duration / frameDuration)
    
    let frame = 0
    const timer = setInterval(() => {
      frame++
      const progress = 1 - Math.pow(1 - frame / totalFrames, 3) // Cubic ease-out
      setCounts({
        applied: Math.round(targets.applied * progress),
        visits: Math.round(targets.visits * progress),
        awarded: Math.round(targets.awarded * progress)
      })

      if (frame === totalFrames) clearInterval(timer)
    }, frameDuration)

    return () => clearInterval(timer)
  }, [])

  return (
    <div className="bg-white font-sans min-h-screen flex flex-col">
      <Navbar />
      <SecondaryNav backTo="/" nextTo="/faqs" nextLabel="View FAQs" />

      {/* Hero Section */}
      <section className="relative bg-emerald-900 text-white py-24 overflow-hidden">
        <div className="absolute inset-0 opacity-20">
          <div className="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/graphy.png')]"></div>
          <div className="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-transparent to-emerald-900"></div>
        </div>
        <div className="container mx-auto px-4 relative z-10 text-center">
          <h1 className="text-4xl md:text-6xl font-extrabold mb-6 tracking-tight animate-fadeInDown">
            Empowering Global Ambitions
          </h1>
          <p className="text-xl md:text-2xl text-emerald-100 max-w-3xl mx-auto leading-relaxed animate-fadeInUp">
            The Binz International Scholarship (BIS) is a beacon of excellence, fostering academic brilliance and cross-border collaboration since 2004.
          </p>
        </div>
        {/* Smooth bottom wave instead of sharp triangle */}
        <svg className="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0,60 C360,0 1080,0 1440,60 L1440,60 L0,60 Z" fill="white" />
        </svg>
      </section>

      {/* Main Content Containers */}
      <div className="flex-grow">
        
        {/* At a Glance Section */}
        <section className="container mx-auto px-4 py-16">
          <div className="flex flex-col md:flex-row items-center gap-12">
            <div className="md:w-1/2">
              <h2 className="text-3xl font-bold text-emerald-900 mb-6 flex items-center gap-3">
                <span className="w-12 h-1 bg-emerald-600"></span> BIS at a Glance
              </h2>
              <div className="space-y-6 text-gray-700 text-lg leading-relaxed">
                <p>
                  Established in September 2004, the <strong>Board of International Scholarships (BIS)</strong> has evolved into Pakistan's most prestigious merit-based funding authority. We bridge the gap between talented individuals and world-class education.
                </p>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div className="p-4 bg-emerald-50 rounded-lg border-l-4 border-emerald-600">
                    <h4 className="font-bold text-emerald-900 mb-1">Our Vision</h4>
                    <p className="text-sm">To be the global leader in educational equity and academic empowerment.</p>
                  </div>
                  <div className="p-4 bg-amber-50 rounded-lg border-l-4 border-amber-500">
                    <h4 className="font-bold text-amber-900 mb-1">Our Mission</h4>
                    <p className="text-sm">Providing transparent, merit-driven opportunities for future world leaders.</p>
                  </div>
                </div>
              </div>
            </div>
            <div className="md:w-1/2 relative">
              <div className="absolute -inset-4 bg-emerald-100 rounded-2xl rotate-3 -z-10"></div>
              <div className="bg-white p-2 rounded-2xl shadow-2xl">
                <div className="bg-emerald-900 aspect-video rounded-xl flex items-center justify-center text-emerald-200 text-4xl">
                   <i className="fas fa-university fa-3x opacity-20 absolute"></i>
                   <span className="relative z-10 font-serif italic">"Excellence Beyond Borders"</span>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Stats Section */}
        <section className="bg-emerald-900 py-16">
          <div className="container mx-auto px-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 divide-y md:divide-y-0 md:divide-x divide-emerald-700">
              <StatCard count={counts.applied} label="Global Applications" subLabel="Across 12+ Nations" />
              <StatCard count={counts.visits} label="Annual Portal Visits" subLabel="Trusted by Millions" />
              <StatCard count={counts.awarded} label="Scholarships Awarded" subLabel="Full-ride Success Stories" />
            </div>
          </div>
        </section>

        {/* Core Values Section */}
        <section className="container mx-auto px-4 py-20">
          <div className="text-center mb-16 animate-fadeIn">
            <h2 className="text-4xl font-extrabold text-emerald-900 mb-4 text-center tracking-tight">
              Our <span className="text-gradient-emerald">Core Values</span>
            </h2>
            <div className="w-24 h-1.5 bg-gradient-to-r from-emerald-600 to-emerald-400 mx-auto rounded-full"></div>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div className="animate-fadeInUp stagger-1">
              <ValueCard icon="fa-shield-alt" title="Integrity" desc="Adhering to the highest ethical standards in every selection." />
            </div>
            <div className="animate-fadeInUp stagger-2">
              <ValueCard icon="fa-chart-line" title="Transparency" desc="Open, blockchain-secured processes for complete trust." />
            </div>
            <div className="animate-fadeInUp stagger-3">
              <ValueCard icon="fa-users" title="Inclusivity" desc="Breaking barriers for students from all eligible backgrounds." />
            </div>
            <div className="animate-fadeInUp stagger-4">
              <ValueCard icon="fa-lightbulb" title="Innovation" desc="Continuous improvement of our digital portal and services." />
            </div>
          </div>
        </section>

        {/* Founder's Message */}
        <section className="bg-gray-50 py-20">
          <div className="container mx-auto px-4">
            <div className="max-w-5xl mx-auto bg-white rounded-3xl shadow-xl overflow-hidden flex flex-col md:flex-row">
              <div className="md:w-2/5 relative">
                <img src={binzPic} alt="Founder" className="w-full h-full object-cover min-h-[400px]" />
                <div className="absolute inset-0 bg-gradient-to-t from-emerald-900/80 to-transparent flex items-bottom p-8">
                   <div className="mt-auto">
                     <h3 className="text-2xl font-bold text-white">Mr. Binyameen</h3>
                     <p className="text-emerald-200">Founder & Chairman, BIS</p>
                   </div>
                </div>
              </div>
              <div className="md:w-3/5 p-12 flex flex-col justify-center">
                <i className="fas fa-quote-left text-5xl text-emerald-100 mb-6"></i>
                <p className="text-xl text-gray-700 italic leading-relaxed mb-8">
                  "Education is the only bridge that can transcend social, economic, and geographical boundaries. At BIS, we don't just grant scholarships; we build futures. Our commitment is to ensure that no brilliant mind is ever hindered by financial constraints."
                </p>
                <div className="flex items-center gap-4">
                  <div className="w-12 h-0.5 bg-emerald-600"></div>
                  <span className="font-bold text-emerald-800 tracking-widest uppercase text-sm">Visionary Perspective</span>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Timeline Section */}
        <section className="container mx-auto px-4 py-20">
           <div className="text-center mb-16">
             <h2 className="text-4xl font-extrabold text-emerald-900 mb-4 tracking-tight">Historical Milestone</h2>
             <div className="w-24 h-1.5 bg-gradient-to-r from-emerald-600 to-emerald-400 mx-auto rounded-full"></div>
           </div>
           <div className="max-w-3xl mx-auto relative border-l-2 border-emerald-200 pl-0 ml-4 md:ml-auto">
              <TimelineItem year="2004" title="Inauguration" desc="Launched in Narowal with a small fund for local brilliant students." />
              <TimelineItem year="2010" title="National Expansion" desc="Recognized as a premier body for university scholarship coordination." />
              <TimelineItem year="2018" title="Digital Transformation" desc="Introduced the online portal for seamless global applications." />
              <TimelineItem year="2025" title="International Integration" desc="Expanded eligibility to 12+ nations including Palestine and Oman." />
           </div>
        </section>

        {/* Call to Action */}
        <section className="bg-emerald-800 py-16 text-center text-white">
           <div className="container mx-auto px-4">
             <h2 className="text-3xl font-bold mb-6">Ready to Start Your Journey?</h2>
             <p className="text-emerald-100 mb-8 max-w-2xl mx-auto">Join over 31,000 applicants this year and take the first step towards your international career.</p>
             <button className="bg-white text-emerald-900 px-10 py-3 rounded-full font-bold hover:bg-emerald-50 transition shadow-lg text-lg">
               Apply Now
             </button>
           </div>
        </section>

      </div>

      <Footer />
    </div>
  )
}

function StatCard({ count, label, subLabel }) {
  return (
    <div className="text-center group">
      <div className="text-5xl font-extrabold text-white mb-2 tracking-tighter group-hover:scale-105 transition duration-300">
        {count.toLocaleString()}
      </div>
      <div className="text-emerald-400 font-bold text-lg uppercase tracking-wider">{label}</div>
      <div className="text-emerald-100/60 text-sm mt-1">{subLabel}</div>
    </div>
  )
}

function ValueCard({ icon, title, desc }) {
  return (
    <div className="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover-premium transition duration-300">
      <div className="w-16 h-16 bg-emerald-50 rounded-xl flex items-center justify-center mb-6 shadow-inner">
        <i className={`fas ${icon} text-2xl text-emerald-600`}></i>
      </div>
      <h3 className="text-xl font-bold text-emerald-900 mb-3">{title}</h3>
      <p className="text-gray-600 leading-relaxed text-sm">{desc}</p>
    </div>
  )
}

function TimelineItem({ year, title, desc }) {
  return (
    <div className="mb-12 relative pl-8 md:pl-0">
      <div className="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-600 border-4 border-white"></div>
      <div className="md:ml-12 bg-white p-6 rounded-xl border border-emerald-50 shadow-sm hover:shadow-md transition">
        <span className="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 font-bold rounded text-xs mb-2">{year}</span>
        <h4 className="text-lg font-bold text-emerald-900 mb-1">{title}</h4>
        <p className="text-gray-600">{desc}</p>
      </div>
    </div>
  )
}

