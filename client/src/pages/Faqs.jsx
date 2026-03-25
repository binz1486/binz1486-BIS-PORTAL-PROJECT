import { useState } from 'react'
import Navbar from '../components/Navbar'
import Footer from '../components/Footer'
import SecondaryNav from '../components/SecondaryNav'

const faqData = [
  { 
    id: 1, 
    question: "What is Binz International Scholarship?", 
    answer: "BIS (Board of International Scholarships) is Pakistan's premier, internationally recognized scholarship authority, established in September 2004. Our mission is to elevate educational opportunities beyond borders by providing merit & need base financial support, fostering academic excellence, and empowering students to achieve their full potential without the burden of financial constraints.",
    color: "border-amber-400"
  },
  {
    id: 2,
    question: "Which international universities recognize BIS scholarships in Pakistan?",
    answer: "BIS scholarships are widely accepted at prestigious institutions across Pakistan, including: The University of Narowal (UON), Quaid-i-Azam University (QAU), National University of Sciences & Technology (NUST), Govt. College University (GCU), University of Karachi (UOK), University of the Punjab (PU), University of Engineering & Technology (UET), and Ghulam Ishaq Khan Institute (GIKI).",
    color: "border-amber-200"
  },
  {
    id: 3,
    question: "What is eligibility criteria for BIS scholarships?",
    answer: "Applicants must be citizens of Pakistan, Palestine, China, Iran, Turkey, Afghanistan, Bangladesh, Sri Lanka, Oman, Spain, Egypt, or Panama. Bachelor: Max age 23, Min CGPA 3.2. Master: Max age 26, Min CGPA 3.4. PhD: Max age 30, Min CGPA 3.5.",
    color: "border-amber-400",
    slug: "faq-eligibility"
  },
  {
    id: 4,
    question: "Which study disciplines are allowed under this scholarships?",
    answer: "BIS offers scholarships for Bachelor's, Master's, and PhD programs in cutting-edge disciplines, including: Computer Science, Software Engineering, AI & Machine Learning, Cybersecurity, Data Science, Information Technology, and Robotics.",
    color: "border-amber-200",
    slug: "faq-universities"
  },
  {
    id: 5,
    question: "How does BIS ensure fairness and transparency in its selection process?",
    answer: "BIS maintains a fair, transparent selection process through a structured merit-need balanced system. Applications undergo anonymized review by independent experts. committees evaluate candidates using predefined 70% merit / 30% need criteria. The blockchain-secured process includes public result declarations.",
    color: "border-amber-400"
  },
  {
    id: 6,
    question: "What will be covered after securing this scholarship?",
    answer: "The BIS scholarship provides full financial coverage including tuition fees, university charges, living expenses (accommodation and three daily meals), health insurance, and round-trip airfare. Selected international students also receive visa assistance.",
    color: "border-amber-200",
    slug: "faq-coverage"
  },
  {
    id: 7,
    question: "What is the application process for the BIS scholarship?",
    answer: "1. Online Application: Submit form through BIS website. 2. Document Submission: Provide transcripts, CV, and letters. 3. Application Fee: Pay small non-refundable fee ($10). 4. Interview: Shortlisted candidates may be invited. 5. Final Selection: Official offer letter.",
    color: "border-amber-400",
    slug: "faq-application"
  },
  {
    id: 8,
    question: "How interview conduted & requirements?",
    answer: "Verified applicants have to pay their non-refundable Applicants Fee. After this you will got email about your online interview within 30 working days. Email contain: interview date & time, Zoom link, and a basic PDF of precautions and questions.",
    color: "border-amber-200"
  },
  {
    id: 9,
    question: "What is scholarship revocation policy?",
    answer: "The BIS scholarship may be revoked for failure to maintain a minimum 70% GPA, academic dishonesty, misconduct, or unreported significant changes in financial status.",
    color: "border-amber-400"
  },
  {
    id: 10,
    question: "Is IELTS & Passport required for the BIS scholarship?",
    answer: "No, IELTS is not required. However, applicants must submit an English proficiency certificate. A valid passport is mandatory at the time of application.",
    color: "border-amber-200"
  },
  {
    id: 11,
    question: "What is the application deadline and what should I do if I missed my interview?",
    answer: "The deadline is September 01 every year. If you missed your interview, contact binzeria@gmail.com immediately with your ID and explanation, or use the portal message box. Rescheduling is subject to availability.",
    color: "border-amber-400",
    slug: "faq-contact"
  },
  {
    id: 12,
    question: "What notable achievements has BIS accomplished since 2004?",
    answer: "75,000+ Scholarships Awarded, 95% Success Rate, Global Partnerships with 50+ universities, and recognized as a top scholarship provider.",
    color: "border-amber-200"
  },
  {
    id: 13,
    question: "How can I delete my account?",
    answer: "Login to your account, click the Message icon, select Request, and state your request for account deletion. Our team will contact you within 7 working days. Once confirmed, it will be permanently deleted after a week.",
    color: "border-amber-400"
  }
]

export default function Faqs() {
  const [searchTerm, setSearchTerm] = useState('')
  const [openId, setOpenId] = useState(null)
  const [allExpanded, setAllExpanded] = useState(false)

  const toggleFaq = (id) => {
    setOpenId(openId === id ? null : id)
  }

  const toggleAll = () => {
    setAllExpanded(!allExpanded)
  }

  const filteredFaqs = faqData.filter(faq => 
    faq.question.toLowerCase().includes(searchTerm.toLowerCase()) ||
    faq.answer.toLowerCase().includes(searchTerm.toLowerCase())
  )

  const handleSlugClick = (slug) => {
    const faq = faqData.find(f => f.slug === slug)
    if (faq) {
      setOpenId(faq.id)
      setTimeout(() => {
        document.getElementById(`faq-${faq.id}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
      }, 100)
    }
  }

  return (
    <div className="bg-gray-50 font-sans min-h-screen flex flex-col">
      <Navbar />
      <div className="bg-gray-400 py-2">
        <div className="container mx-auto px-4 flex justify-between items-center">
          <SecondaryNav backTo="/" backLabel="Go to Home" />
          <button onClick={toggleAll} className="text-gray-800 font-bold hover:text-black flex items-center gap-2 transition-colors">
            <i className={`fas ${allExpanded ? 'fa-compress' : 'fa-expand'}`}></i>
            <span className="animated-underline">{allExpanded ? 'Collapse All' : 'Expand All'}</span>
          </button>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6 max-w-4xl relative z-10 flex-grow">
        <h1 className="text-2xl font-bold text-gray-700 mb-8 flex items-center gap-2">
          <i className="fas fa-question-circle"></i> Frequently Asked Questions
        </h1>

        <div className="mb-6 flex items-center gap-2">
          <div className="relative w-full">
            <input 
              type="text" 
              placeholder="Search FAQs..." 
              className="w-full px-4 py-2 rounded border border-gray-300 focus:ring-2 focus:ring-amber-500 outline-none transition shadow-sm pr-10"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
            <i className="fas fa-search absolute right-3 top-3 text-gray-400"></i>
          </div>
        </div>

        {/* Popular Topics Quick Links */}
        <div className="mb-8 flex flex-wrap gap-3 items-center">
          <span className="text-gray-500 font-semibold mr-2">Popular:</span>
          {[
            { label: 'Eligibility', slug: 'faq-eligibility', color: 'bg-amber-100 hover:bg-amber-200' },
            { label: 'Universities', slug: 'faq-universities', color: 'bg-emerald-100 hover:bg-emerald-200' },
            { label: 'Coverage', slug: 'faq-coverage', color: 'bg-blue-100 hover:bg-blue-200' },
            { label: 'Application', slug: 'faq-application', color: 'bg-pink-100 hover:bg-pink-200' },
            { label: 'Contact', slug: 'faq-contact', color: 'bg-gray-100 hover:bg-gray-200' },
          ].map((topic) => (
            <button
              key={topic.slug}
              onClick={() => handleSlugClick(topic.slug)}
              className={`${topic.color} text-gray-700 px-4 py-1.5 rounded-full text-sm font-semibold shadow-sm transition-all hover:-translate-y-0.5 active:translate-y-0`}
            >
              {topic.label}
            </button>
          ))}
        </div>

        <div className="space-y-4">
          {filteredFaqs.map((faq) => (
            <div 
              key={faq.id} 
              id={`faq-${faq.id}`}
              className={`bg-white rounded-lg shadow-md overflow-hidden border-r-4 transition-all duration-300 ${faq.color} ${openId === faq.id ? 'shadow-lg translate-x-1' : ''}`}
            >
              <button 
                onClick={() => toggleFaq(faq.id)}
                className="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none group"
              >
                <span className={`font-medium text-lg transition-colors ${openId === faq.id ? 'text-amber-600' : 'text-gray-800'}`}>
                  {faq.id}. {faq.question}
                </span>
                <i className={`fas fa-chevron-down text-amber-600 transition-transform duration-300 ${openId === faq.id || allExpanded ? 'rotate-180' : ''}`}></i>
              </button>
              {(openId === faq.id || allExpanded) && (
                <div className="px-6 pb-5 animate-fadeIn">
                  <div className="h-px bg-gray-100 mb-4 w-full"></div>
                  <p className="text-gray-600 leading-relaxed">{faq.answer}</p>
                </div>
              )}
            </div>
          ))}
          {filteredFaqs.length === 0 && (
            <div className="text-center py-10 text-gray-500">
              No FAQs found matching your search.
            </div>
          )}
        </div>
      </div>

      <Footer />
    </div>
  )
}

