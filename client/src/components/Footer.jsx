export default function Footer() {
  return (
    <footer className="bg-gray-800 text-white p-4 mt-8">
      <div className="container mx-auto text-center">
        <div className="mb-2">
          <b><i className="fas fa-envelope text-white-600"></i> Email:</b> <a href="mailto:binzeria@gmail.com" className="hover:text-blue-600">binzeria@gmail.com</a>
          <span className="mx-2">|</span>
          <b><i className="fas fa-phone text-white-600"></i> Call:</b> <a href="tel:+923446072989" className="hover:text-blue-600">+92-344-6072989</a>
          <span className="mx-2">|</span>
          <b><i className="fas fa-phone-alt"></i> Landline:</b> <a href="tel:9238-476937" className="hover:text-blue-600">9238-476937</a>
        </div>
        <p><b>©</b> 2023 Binz International Scholarship Portal. All rights reserved.</p>
      </div>
    </footer>
  )
}
