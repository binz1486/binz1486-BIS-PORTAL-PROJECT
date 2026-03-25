/**
 * ReusableModal - A premium split-panel component.
 * 
 * Props:
 *   show        - Boolean to control visibility
 *   onClose     - Function to call when closing (optional for inline mode)
 *   leftBg      - Tailwind bg class for the left panel (default: 'bg-emerald-900')
 *   leftContent - JSX for the left panel body
 *   leftFooter  - JSX for the left panel footer badges
 *   title       - Right panel heading
 *   subtitle    - Right panel subtitle
 *   children    - Right panel form/body content
 *   inline      - If true, renders as inline card (no overlay/backdrop)
 *   reversed    - If true, swaps panels (form left, accent right)
 *   contentKey  - Key to trigger re-mount animation on content change
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
  inline = false,
  reversed = false,
  contentKey,
}) {
  if (!show) return null

  const accentPanel = (
    <div className={`hidden md:flex md:w-2/5 ${leftBg} flex-col justify-between p-8 text-white transition-all duration-500`}>
      <div>{leftContent}</div>
      {leftFooter && (
        <div className="space-y-2 text-xs mt-6">{leftFooter}</div>
      )}
    </div>
  )

  const formPanel = (
    <div className="flex-1 p-7 md:p-8 flex flex-col" key={contentKey}>
      <div className="flex justify-between items-center mb-6">
        <div>
          <h3 className="text-2xl font-bold text-slate-800">{title}</h3>
          {subtitle && <p className="text-slate-500 text-sm mt-1">{subtitle}</p>}
        </div>
        {onClose && (
          <button
            onClick={onClose}
            className="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors"
          >
            <i className="fas fa-times text-sm"></i>
          </button>
        )}
      </div>
      <div className="flex-1">{children}</div>
    </div>
  )

  const card = (
    <div className={`bg-white rounded-xl shadow-2xl w-full ${inline ? 'max-w-4xl' : 'max-w-3xl'} min-h-[520px] overflow-hidden flex flex-col ${reversed ? 'md:flex-row-reverse' : 'md:flex-row'} ${inline ? '' : 'animate-scaleIn'} transition-all duration-500`}>
      {accentPanel}
      {formPanel}
    </div>
  )

  if (inline) return card

  return (
    <div className="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-[100] animate-fadeIn">
      {card}
    </div>
  )
}
