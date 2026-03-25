import { useState, useEffect, useRef } from 'react'
import { Link } from 'react-router-dom'
import logo from '../assets/logo.png'
import bgPic from '../assets/bg-pic.jpg'
import ReusableModal from './ReusableModal'

const quotes = [
  "Education is the most powerful weapon which you can use to change the world.",
  "The beautiful thing about learning is that no one can take it away from you.",
  "Investment in knowledge pays the best interest.",
  "Education is not preparation for life; education is life itself.",
  "Your education is a dress rehearsal for a life that is yours to lead."
]

export default function Login({ onLoginSuccess }) {
  // UI State
  const [isAdminMode, setIsAdminMode] = useState(false)
  const [showPassword, setShowPassword] = useState(false)
  const [showForgotModal, setShowForgotModal] = useState(false)
  const [showRegisterModal, setShowRegisterModal] = useState(false)
  const [showHelpModal, setShowHelpModal] = useState(false)
  const [showSuccessMsg, setShowSuccessMsg] = useState(false)
  const [showRegSuccessMsg, setShowRegSuccessMsg] = useState(false)
  
  // Form State
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [rememberMe, setRememberMe] = useState(false)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  // Registration State
  const [regStep, setRegStep] = useState(1)
  const [regData, setRegData] = useState({ fullName: '', applicantId: '', regEmail: '', regPassword: '', confirmPassword: '' })
  const [captcha, setCaptcha] = useState({ n1: 0, n2: 0, answer: '' })
  const [passwordStrength, setPasswordStrength] = useState({ width: '0%', color: 'bg-gray-200', text: '' })

  // Typing Effect
  const [typedQuote, setTypedQuote] = useState('')
  const quoteIndexRef = useRef(0)
  const charIndexRef = useRef(0)
  const isDeletingRef = useRef(false)

  useEffect(() => {
    const type = () => {
      const currentQuote = quotes[quoteIndexRef.current]
      if (isDeletingRef.current) {
        setTypedQuote(currentQuote.substring(0, charIndexRef.current - 1))
        charIndexRef.current--
      } else {
        setTypedQuote(currentQuote.substring(0, charIndexRef.current + 1))
        charIndexRef.current++
      }

      let speed = isDeletingRef.current ? 50 : 100

      if (!isDeletingRef.current && charIndexRef.current === currentQuote.length) {
        speed = 2000
        isDeletingRef.current = true
      } else if (isDeletingRef.current && charIndexRef.current === 0) {
        isDeletingRef.current = false
        quoteIndexRef.current = (quoteIndexRef.current + 1) % quotes.length
        speed = 500
      }

      setTimeout(type, speed)
    }

    const timer = setTimeout(type, 500)
    return () => clearTimeout(timer)
  }, [])

  // Captcha Logic
  const refreshCaptcha = () => {
    setCaptcha({ n1: Math.floor(Math.random() * 10), n2: Math.floor(Math.random() * 10), answer: '' })
  }

  useEffect(() => {
    refreshCaptcha()
  }, [])

  // Password Strength Logic
  const checkPasswordStrength = (pwd) => {
    let strength = 0
    if (pwd.length >= 8) strength++
    if (/[A-Z]/.test(pwd)) strength++
    if (/[a-z]/.test(pwd)) strength++
    if (/[0-9]/.test(pwd)) strength++
    if (/[!@#$%^&*]/.test(pwd)) strength++

    const levels = [
      { width: '0%', color: 'bg-gray-200', text: '' },
      { width: '25%', color: 'bg-red-500', text: 'Weak' },
      { width: '50%', color: 'bg-yellow-500', text: 'Medium' },
      { width: '75%', color: 'bg-blue-500', text: 'Strong' },
      { width: '100%', color: 'bg-green-500', text: 'Very Strong' }
    ]
    
    const levelIndex = Math.min(strength, 4)
    setPasswordStrength(levels[levelIndex])
  }

  const handleLogin = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')

    try {
      const response = await fetch('/server/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password, admin: isAdminMode })
      })

      const data = await response.json()
      if (data.status === 'success') {
        onLoginSuccess(data)
      } else {
        setError(data.message || 'Invalid credentials')
      }
    } catch (err) {
      setError('Connection error. Please check your backend.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-4 pt-20 bg-cover bg-center backdrop-blur-sm relative" style={{ backgroundImage: `url(${bgPic})` }}>
      {/* Admin/Applicant Icon Toggle */}
      <div className="fixed top-6 right-6 z-20">
        <button 
          onClick={() => setIsAdminMode(!isAdminMode)}
          className="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white w-12 h-12 rounded-full shadow-2xl flex items-center justify-center transition-all duration-500 hover:rotate-12 border border-white/20 group"
          title={isAdminMode ? "Switch to Applicant Login" : "Switch to Admin Login"}
        >
          <i className={`fas ${isAdminMode ? 'fa-user' : 'fa-user-shield'} text-xl transition-all duration-500 ${isAdminMode ? 'scale-110' : 'group-hover:scale-110'}`}></i>
        </button>
      </div>

      {/* Top Nav */}
      <div className="absolute top-6 left-6 flex flex-wrap gap-2 max-w-[80vw] animate-fadeIn z-10">
        <Link to="/" className="bg-white/10 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 text-xs uppercase tracking-widest rounded-full shadow-lg transition-all duration-300 backdrop-blur-sm border border-white/10 hover:-translate-y-0.5 flex items-center gap-1.5">
          <i className="fas fa-home text-[10px]"></i> Home
        </Link>
        <Link to="/eligibility" className="bg-white/10 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 text-xs uppercase tracking-widest rounded-full shadow-lg transition-all duration-300 backdrop-blur-sm border border-white/10 hover:-translate-y-0.5 flex items-center gap-1.5">
          <i className="fas fa-globe text-[10px]"></i> Eligibility
        </Link>
        <Link to="/faqs" className="bg-white/10 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 text-xs uppercase tracking-widest rounded-full shadow-lg transition-all duration-300 backdrop-blur-sm border border-white/10 hover:-translate-y-0.5 flex items-center gap-1.5">
          <i className="fas fa-question-circle text-[10px]"></i> FAQs
        </Link>
        <Link to="/about" className="bg-white/10 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 text-xs uppercase tracking-widest rounded-full shadow-lg transition-all duration-300 backdrop-blur-sm border border-white/10 hover:-translate-y-0.5 flex items-center gap-1.5">
          <i className="fas fa-info-circle text-[10px]"></i> About
        </Link>
      </div>

      {/* ====== UNIFIED LOGIN / REGISTER PANEL ====== */}
      <ReusableModal
        show={true}
        inline={true}
        reversed={showRegisterModal}
        contentKey={showRegisterModal ? 'register' : 'login'}
        leftBg={showRegisterModal ? 'bg-emerald-900' : (isAdminMode ? 'bg-slate-900' : 'flag-gradient')}
        leftContent={
          showRegisterModal ? (
            <>
              <img src={logo} alt="Logo" className="h-14 w-14 rounded-full mb-5 brightness-0 invert opacity-90" />
              <h2 className="text-3xl font-bold leading-tight mb-3">Join BIS Portal</h2>
              <p className="text-emerald-200 text-sm leading-relaxed mb-5">
                Create your applicant account to access and manage your international scholarship application.
              </p>
              <div className="border-l-2 border-emerald-400/30 pl-4 text-emerald-100 text-xs space-y-1.5">
                <p><i className="fas fa-check mr-1.5"></i> Free to apply</p>
                <p><i className="fas fa-check mr-1.5"></i> Track your application</p>
                <p><i className="fas fa-check mr-1.5"></i> Secure portal access</p>
              </div>
            </>
          ) : (
            <>
              <img src={logo} alt="Logo" className="h-16 w-16 rounded-full mb-6 brightness-0 invert opacity-90" />
              <h2 className="text-3xl font-bold leading-tight mb-3">
                {isAdminMode ? 'Admin Portal' : 'Binz International Scholarship'}
              </h2>
              <p className="text-white/70 text-sm leading-relaxed mb-6">
                {isAdminMode
                  ? 'Manage applicants, review submissions, and oversee the scholarship process.'
                  : 'Access your scholarship application and track your progress.'}
              </p>
              <div className="min-h-[50px] border-l-2 border-white/30 pl-4">
                <p className="text-white/80 text-sm italic leading-relaxed">
                  "{typedQuote}<span className="animate-blink text-white/50">|</span>"
                </p>
              </div>
            </>
          )
        }
        leftFooter={
          <div className={showRegisterModal ? 'text-emerald-300' : (isAdminMode ? 'text-slate-400' : 'text-emerald-200')}>
            <div className="flex items-center gap-2"><i className="fas fa-shield-alt"></i> 256-bit SSL Encrypted</div>
            <div className="flex items-center gap-2 mt-2"><i className="fas fa-globe"></i> 12+ Eligible Nations</div>
            <div className="flex items-center gap-2 mt-2"><i className="fas fa-award"></i> 1,050+ Scholarships Awarded</div>
          </div>
        }
        title={showRegisterModal
          ? 'Create Account'
          : (isAdminMode ? 'Admin Sign In' : 'Sign In')}
        subtitle={showRegisterModal
          ? `Step ${regStep} of 2 — ${regStep === 1 ? 'Personal Details' : 'Set Password'}`
          : (isAdminMode ? 'Authorized personnel only' : 'Welcome back! Enter your credentials')}
      >

        {/* ---- LOGIN FORM ---- */}
        {!showRegisterModal ? (
          <form className="space-y-5 flex flex-col h-full animate-slideIn" onSubmit={handleLogin} key="login-form">
            {error && (
              <div className="bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 flex items-center gap-2 text-sm text-red-700">
                <i className="fas fa-exclamation-triangle"></i> {error}
              </div>
            )}
            
            <div>
              <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
                <i className={`fas ${isAdminMode ? 'fa-user-shield' : 'fa-envelope'} text-emerald-600 w-4`}></i>
                {isAdminMode ? 'Admin Email' : 'Email Address'}
              </label>
              <input 
                className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all text-slate-800 placeholder-slate-400 text-sm" 
                type="email" 
                placeholder={isAdminMode ? 'admin@email.com' : 'your@email.com'}
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required 
              />
            </div>

            <div>
              <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
                <i className="fas fa-lock text-emerald-600 w-4"></i> Password
              </label>
              <div className="relative">
                <input 
                  className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none pr-12 transition-all text-slate-800 placeholder-slate-400 text-sm" 
                  type={showPassword ? 'text' : 'password'}
                  placeholder="••••••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required 
                />
                <button 
                  type="button" 
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600 transition-colors"
                  onClick={() => setShowPassword(!showPassword)}
                >
                  <i className={`fas ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`}></i>
                </button>
              </div>
              
              <div className="flex justify-between items-center mt-2.5">
                <div className="flex items-center gap-2">
                  <input 
                    type="checkbox" id="rememberMe"
                    className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" 
                    checked={rememberMe}
                    onChange={(e) => setRememberMe(e.target.checked)}
                  />
                  <label htmlFor="rememberMe" className="text-sm text-slate-500 cursor-pointer">Remember me</label>
                </div>
                {!isAdminMode && (
                  <button 
                    type="button" 
                    onClick={() => setShowForgotModal(true)}
                    className="text-sm text-emerald-600 hover:text-emerald-800 font-medium transition-colors"
                  >
                    Forgot password?
                  </button>
                )}
              </div>
            </div>

            <div className="flex-1"></div>

            <button 
              className={`w-full ${isAdminMode ? 'bg-slate-900 hover:bg-slate-800' : 'flag-gradient hover:opacity-90'} text-white py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center gap-2 font-bold shadow-md hover:-translate-y-0.5`}
              type="submit"
              disabled={loading}
            >
              {loading ? (
                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              ) : (
                <><i className="fas fa-sign-in-alt"></i> {isAdminMode ? 'Access Dashboard' : 'Sign In'}</>
              )}
            </button>

            {!isAdminMode && (
              <div className="text-center">
                <p className="text-slate-500 text-sm">Don't have an account? {' '}
                  <button 
                    type="button"
                    onClick={() => { setShowRegisterModal(true); setRegStep(1) }}
                    className="text-emerald-600 hover:text-emerald-800 font-semibold transition-colors"
                  >
                    Register Here
                  </button>
                </p>
              </div>
            )}
          </form>
        ) : (
          /* ---- REGISTER FORM (2-step wizard) ---- */
          <form className="space-y-5 flex flex-col h-full animate-slideIn" onSubmit={(e) => e.preventDefault()} key="register-form">
            {regStep === 1 ? (
              <>
                {/* Step 1 — Name, ID, Email */}
                <div>
                  <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
                    <i className="fas fa-address-card text-emerald-600 w-4"></i> Full Name
                  </label>
                  <input
                    className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all text-slate-800 placeholder-slate-400 text-sm"
                    placeholder="First Middle Last Name"
                    value={regData.fullName}
                    onChange={(e) => setRegData({...regData, fullName: e.target.value})}
                    required
                  />
                </div>
                <div>
                  <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
                    <i className="fas fa-passport text-emerald-600 w-4"></i> Applicant ID
                  </label>
                  <input
                    className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all text-slate-800 placeholder-slate-400 text-sm"
                    placeholder="Enter Passport Number"
                    value={regData.applicantId}
                    onChange={(e) => setRegData({...regData, applicantId: e.target.value})}
                    required
                  />
                </div>
                <div>
                  <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
                    <i className="fas fa-envelope text-emerald-600 w-4"></i> Email (Username)
                  </label>
                  <input
                    type="email"
                    className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all text-slate-800 placeholder-slate-400 text-sm"
                    placeholder="your@email.com"
                    value={regData.regEmail}
                    onChange={(e) => setRegData({...regData, regEmail: e.target.value})}
                    required
                  />
                </div>
                <div className="flex-1"></div>
                <div className="flex justify-between items-center pt-2">
                  <button type="button" onClick={() => { setShowRegisterModal(false); setRegStep(1) }} className="text-slate-500 text-sm font-semibold hover:text-slate-700 transition-colors flex items-center gap-1">
                    <i className="fas fa-arrow-left text-xs"></i> Back to Login
                  </button>
                  <button
                    type="button"
                    onClick={() => { if (regData.fullName && regData.applicantId && regData.regEmail) setRegStep(2) }}
                    className="bg-emerald-800 hover:bg-emerald-900 text-white px-8 py-3 rounded-lg font-bold transition-all shadow-md hover:-translate-y-0.5 flex items-center gap-2"
                  >
                    Next <i className="fas fa-arrow-right text-sm"></i>
                  </button>
                </div>
              </>
            ) : (
              <>
                {/* Step 2 — Password, Confirm, Captcha */}
                <div>
                  <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
                    <i className="fas fa-lock text-emerald-600 w-4"></i> Password
                  </label>
                  <div className="relative">
                    <input
                      type={showPassword ? 'text' : 'password'}
                      className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all pr-12 text-slate-800 placeholder-slate-400 text-sm"
                      placeholder="Create a strong password"
                      value={regData.regPassword}
                      onChange={(e) => { setRegData({...regData, regPassword: e.target.value}); checkPasswordStrength(e.target.value) }}
                      required
                    />
                    <button type="button" className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600 transition-colors" onClick={() => setShowPassword(!showPassword)}>
                      <i className={`fas ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`}></i>
                    </button>
                  </div>
                  <div className="mt-2 flex items-center gap-2">
                    <div className="flex-1 h-1 bg-slate-200 rounded-full overflow-hidden">
                      <div className={`h-full transition-all duration-500 ${passwordStrength.color}`} style={{ width: passwordStrength.width }}></div>
                    </div>
                    <span className="text-[10px] uppercase font-bold tracking-wider text-slate-400 min-w-[60px] text-right">{passwordStrength.text}</span>
                  </div>
                </div>

                <div>
                  <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
                    <i className="fas fa-check-double text-emerald-600 w-4"></i> Confirm Password
                  </label>
                  <input
                    type="password"
                    className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all text-slate-800 placeholder-slate-400 text-sm"
                    placeholder="Re-enter your password"
                    value={regData.confirmPassword}
                    onChange={(e) => setRegData({...regData, confirmPassword: e.target.value})}
                    required
                  />
                  {regData.confirmPassword && regData.regPassword !== regData.confirmPassword && (
                    <p className="text-red-500 text-xs mt-1.5 flex items-center gap-1"><i className="fas fa-exclamation-circle"></i> Passwords do not match</p>
                  )}
                </div>

                {/* Captcha */}
                <div className="bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 flex items-center gap-4">
                  <i className="fas fa-robot text-emerald-600"></i>
                  <span className="text-slate-700 font-semibold text-sm">{captcha.n1} + {captcha.n2} =</span>
                  <input
                    className="w-20 px-3 py-2 bg-white border border-slate-200 rounded-md focus:ring-1 focus:ring-emerald-500 outline-none font-bold text-center text-slate-700 text-sm"
                    type="number"
                    value={captcha.answer}
                    onChange={(e) => setCaptcha({...captcha, answer: e.target.value})}
                    placeholder="?"
                    required
                  />
                  <button type="button" onClick={refreshCaptcha} className="ml-auto text-slate-400 hover:text-emerald-600 hover:rotate-180 transition-all duration-500" title="Refresh">
                    <i className="fas fa-sync-alt"></i>
                  </button>
                </div>

                <div className="flex-1"></div>
                <div className="flex justify-between items-center pt-2">
                  <button type="button" onClick={() => setRegStep(1)} className="text-slate-500 text-sm font-semibold hover:text-slate-700 transition-colors flex items-center gap-1">
                    <i className="fas fa-arrow-left text-xs"></i> Back
                  </button>
                  <button type="submit" className="bg-emerald-800 hover:bg-emerald-900 text-white px-8 py-3 rounded-lg font-bold transition-all shadow-md hover:-translate-y-0.5 flex items-center gap-2">
                    <i className="fas fa-user-plus"></i> Create Account
                  </button>
                </div>
              </>
            )}
          </form>
        )}
      </ReusableModal>

      {/* Floating Help */}
      <div className="fixed bottom-6 right-6 z-20">
        <button 
          onClick={() => setShowHelpModal(true)}
          className="flag-gradient text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg transition-transform duration-200 floating-animate"
        >
          <i className="fas fa-question text-xl"></i>
        </button>
      </div>

      {/* Contact Information Modal - Compact Version */}
      {showHelpModal && (
        <div className="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-[100] animate-fadeIn backdrop-blur-md">
          <div className="bg-emerald-50 rounded-xl p-6 w-full max-w-[420px] relative border border-emerald-200 shadow-2xl transform transition-all animate-scaleIn">
            <button 
              onClick={() => setShowHelpModal(false)} 
              className="absolute top-3 right-4 text-xl text-gray-400 hover:text-gray-600 transition-colors"
            >
              <i className="fas fa-times"></i>
            </button>
            
            <div className="flex items-center gap-3 mb-6">
              <i className="fas fa-arrow-right text-xl text-emerald-900"></i>
              <h2 className="text-xl font-bold text-emerald-950 tracking-tight">Contact Information</h2>
            </div>

            <div className="space-y-4">
               <div className="flex items-center gap-3 group">
                 <div className="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                   <i className="fas fa-envelope text-red-500 text-sm"></i>
                 </div>
                 <span className="text-sm font-bold text-emerald-900 select-all">binzeria@gmail.com</span>
               </div>
               
               <div className="flex items-center gap-3 group">
                 <div className="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                   <i className="fas fa-phone text-emerald-700 text-sm"></i>
                 </div>
                 <span className="text-sm font-bold text-emerald-900 select-all">+92-344-6072989</span>
               </div>

               <div className="flex items-center gap-3 group">
                 <div className="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                   <i className="fas fa-map-marker-alt text-slate-500 text-sm"></i>
                 </div>
                 <span className="text-sm font-bold text-emerald-900">Narowal Punjab, Pakistan</span>
               </div>

               <a href="https://facebook.com/binz1486" target="_blank" rel="noopener noreferrer" className="flex items-center gap-3 group cursor-pointer">
                 <div className="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                   <i className="fab fa-facebook text-blue-600 text-md"></i>
                 </div>
                 <span className="text-sm font-bold text-emerald-900 hover:text-blue-600 transition-colors">facebook.com/binz1486</span>
               </a>

               <Link to="/about" onClick={() => setShowHelpModal(false)} className="flex items-center gap-3 group cursor-pointer">
                 <div className="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform text-amber-600">
                    <i className="fas fa-info-circle text-md"></i>
                 </div>
                 <span className="text-sm font-bold text-emerald-900 hover:text-amber-600 transition-colors">About us</span>
               </Link>
            </div>

            <div className="mt-6 pt-4 border-t border-emerald-100">
               <p className="text-emerald-900 font-bold text-[10px] uppercase tracking-widest text-center opacity-70">
                 &copy; 2025 Binz International Scholarship Portal
               </p>
            </div>
          </div>
        </div>
      )}

      {/* Forgot Password Modal */}
      <ReusableModal
        show={showForgotModal}
        onClose={() => setShowForgotModal(false)}
        leftBg="bg-emerald-800"
        leftContent={
          <>
            <div className="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center mb-8">
              <i className="fas fa-key text-white text-2xl"></i>
            </div>
            <h2 className="text-2xl font-bold leading-tight mb-3">Account Recovery</h2>
            <p className="text-emerald-200 text-sm leading-relaxed">
              Enter your registered credentials to verify your identity and reset your password.
            </p>
          </>
        }
        leftFooter={
          <div className="text-emerald-300">
            <div className="flex items-center gap-2"><i className="fas fa-lock"></i> Identity Verified Securely</div>
            <div className="flex items-center gap-2 mt-2"><i className="fas fa-headset"></i> Need help? Contact support</div>
          </div>
        }
        title="Reset Password"
        subtitle="Verify your identity to continue"
      >
        <form className="space-y-5" onSubmit={(e) => e.preventDefault()}>
          <div>
            <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
              <i className="fas fa-envelope text-emerald-600 w-4"></i> Registered Email
            </label>
            <input
              type="email"
              className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all text-slate-800 placeholder-slate-400 text-sm"
              placeholder="Enter your registered email"
              required
            />
          </div>

          <div>
            <label className="flex items-center gap-2 text-slate-600 font-semibold text-sm mb-1.5">
              <i className="fas fa-passport text-emerald-600 w-4"></i> Applicant ID
            </label>
            <input
              className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all text-slate-800 placeholder-slate-400 text-sm"
              placeholder="Enter Passport Number"
              required
            />
          </div>

          <div className="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 flex items-start gap-3 text-sm text-amber-800">
            <i className="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
            <span>If your credentials match, you will receive a password reset link at your registered email within a few minutes.</span>
          </div>

          <div className="flex justify-between items-center pt-2">
            <button type="button" onClick={() => setShowForgotModal(false)} className="text-slate-500 text-sm font-semibold hover:text-slate-700 transition-colors">
              Cancel
            </button>
            <button type="submit" className="bg-emerald-800 hover:bg-emerald-900 text-white px-8 py-3 rounded-lg font-bold transition-all shadow-md hover:-translate-y-0.5 flex items-center gap-2">
              <i className="fas fa-paper-plane"></i> Submit Request
            </button>
          </div>
        </form>
      </ReusableModal>

    </div>
  )
}
