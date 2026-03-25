export default function Footer() {
  return (
    <footer className="bg-emerald-950 text-emerald-100">
      <div className="container mx-auto px-6 py-10">
        <div className="flex flex-col md:flex-row justify-between items-center gap-6">
          <div className="text-center md:text-left">
            <p className="text-xl font-extrabold text-white tracking-tight">Binz International Scholarship</p>
            <p className="text-emerald-400 text-sm mt-1">Empowering Global Ambitions Since 2004</p>
          </div>
          <div className="flex flex-col sm:flex-row items-center gap-4 text-sm">
            <a href="mailto:binzeria@gmail.com" className="flex items-center gap-2 hover:text-white transition-colors group">
              <span className="w-8 h-8 rounded-full bg-emerald-800 flex items-center justify-center group-hover:bg-emerald-700 transition">
                <i className="fas fa-envelope text-emerald-300 text-sm"></i>
              </span>
              binzeria@gmail.com
            </a>
            <a href="tel:+923446072989" className="flex items-center gap-2 hover:text-white transition-colors group">
              <span className="w-8 h-8 rounded-full bg-emerald-800 flex items-center justify-center group-hover:bg-emerald-700 transition">
                <i className="fas fa-phone text-emerald-300 text-sm"></i>
              </span>
              +92-344-6072989
            </a>
          </div>
        </div>
        <div className="border-t border-emerald-800 mt-8 pt-6 text-center text-emerald-500 text-xs tracking-widest uppercase">
          &copy; 2025 Binz International Scholarship Portal. All rights reserved.
        </div>
      </div>
    </footer>
  )
}
