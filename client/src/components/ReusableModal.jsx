/**
 * ReusableModal - A premium split-panel modal component.
 * 
 * Props:
 *   show        - Boolean to control visibility
 *   onClose     - Function to call when closing
 *   leftBg      - Tailwind bg class for the left panel (default: 'bg-emerald-900')
 *   leftContent - JSX for the left panel body
 *   leftFooter  - JSX for the left panel footer badges
 *   title       - Right panel heading
 *   subtitle    - Right panel subtitle
 *   children    - Right panel form/body content
 */
export default function ReusableModal({
  show,
  onClose,
  leftBg = 'bg-emerald-900',
  leftContent,
  leftFooter,
  title,
  subtitle,
  children,
}) {
  if (!show) return null

  return (
    <div className="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-[100] animate-fadeIn">
      <div className="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col md:flex-row animate-scaleIn">
        
        {/* Left Accent Panel */}
        <div className={`hidden md:flex md:w-2/5 ${leftBg} flex-col justify-between p-8 text-white`}>
          <div>{leftContent}</div>
          {leftFooter && (
            <div className="space-y-2 text-xs mt-6">{leftFooter}</div>
          )}
        </div>

        {/* Right Content Panel */}
        <div className="flex-1 p-7 md:p-8">
          <div className="flex justify-between items-center mb-6">
            <div>
              <h3 className="text-2xl font-bold text-slate-800">{title}</h3>
              {subtitle && <p className="text-slate-500 text-sm mt-1">{subtitle}</p>}
            </div>
            <button
              onClick={onClose}
              className="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors"
            >
              <i className="fas fa-times text-sm"></i>
            </button>
          </div>
          {children}
        </div>
      </div>
    </div>
  )
}
